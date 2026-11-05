<?php
// START OF COMPLETE REPEATED LOGIC
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PembatalanPostingService
 * Service Layer Sentral untuk Pembatalan Transaksi Resmi (Jenis 9911)
 * Mencontoh pola Service CI3 (seperti FgReturnService & SuppliesReturnService)
 * 
 * Bertanggung jawab mengeksekusi:
 * 1. Validasi transaksi target
 * 2. Pembuatan dokumen transaksi master 9911 & step 9911_1
 * 3. Pembalikan Jurnal & Rekening secara balance multi-cabang
 * 4. Pembalikan Buku Pembantu terpartisi (__rek_pembantu_*)
 * 5. Penutupan sumber pembayaran (transaksi_payment_source)
 * 6. Audit signature transaksi
 */
class PembatalanPostingService
{
    private $CI;
    private $cCode = "_TR_9911";
    private $jenisTr = "9911";

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper("he_stepping");
        $this->CI->load->helper("he_access_right");
        $this->CI->load->helper("he_angka");
        $this->CI->load->helper("he_finansial");
        $this->CI->load->helper("he_accounting");
        $this->CI->load->helper("he_value_builder");
        $this->CI->load->helper("he_misc");
        $this->CI->load->config("heAccounting");
        $this->CI->load->model("CustomCounter");
        $this->CI->load->model("MdlTransaksi");
    }

    private function getReversalValue($rek, $debet, $kredit)
    {
        $normalPos = detectRekPosition($rek, 1);
        if (floatval($debet) > 0) {
            $origAmount = floatval($debet);
            if ($normalPos == "debet") {
                return -1 * $origAmount;
            } else {
                return $origAmount;
            }
        } else {
            $origAmount = floatval($kredit);
            if ($normalPos == "debet") {
                return $origAmount;
            } else {
                return -1 * $origAmount;
            }
        }
    }

    public function processCancellation($params = array())
    {
        $target_transaksi_id = isset($params["target_transaksi_id"]) ? $params["target_transaksi_id"] : (isset($params["target_id"]) ? $params["target_id"] : 0);
        $no_spk = isset($params["no_spk"]) ? $params["no_spk"] : "";
        $alasan_batal = isset($params["alasan_batal"]) ? $params["alasan_batal"] : "Pembatalan transaksi via PembatalanPostingService";
        $login_connect = isset($params["login_connect"]) ? $params["login_connect"] : array();
        $debuger = isset($params["debuger"]) ? $params["debuger"] : 0;

        if (empty($target_transaksi_id) || intval($target_transaksi_id) <= 0) {
            return array(
                "status" => 0,
                "reason" => "target_transaksi_id belum di set atau bernilai 0",
            );
        }

        if (!empty($login_connect) && is_array($login_connect)) {
            $this->CI->session->login = $login_connect;
        }

        $myId = isset($this->CI->session->login['id']) ? $this->CI->session->login['id'] : -100;
        $myName = isset($this->CI->session->login['nama']) ? $this->CI->session->login['nama'] : (isset($this->CI->session->login['nama_login']) ? $this->CI->session->login['nama_login'] : "sys");
        $cabangId = isset($this->CI->session->login['cabang_id']) ? $this->CI->session->login['cabang_id'] : 1;

        $cCode = $this->cCode;

        $targetTrx = $this->CI->db->where("id", $target_transaksi_id)->get("transaksi")->row_array();
        if (empty($targetTrx)) {
            return array(
                "status" => 0,
                "reason" => "Transaksi target (ID: " . $target_transaksi_id . ") tidak ditemukan di database",
            );
        }

        if (isset($targetTrx['trash_4']) && $targetTrx['trash_4'] == 1) {
            return array(
                "status" => 0,
                "reason" => "Transaksi target " . $targetTrx['nomer'] . " sudah berstatus dibatalkan sebelumnya",
            );
        }

        $origJurnals = $this->CI->db->where("transaksi_id", $target_transaksi_id)
            ->where("trash", 0)
            ->get("jurnal")
            ->result_array();

        // 1. Kumpulkan semua rekening dari original jurnal & registry jurnal_index
        $reks = array();
        if (!empty($origJurnals)) {
            foreach ($origJurnals as $oj) {
                if (!empty($oj['rekening'])) {
                    $reks[$oj['rekening']] = true;
                }
            }
        }
        $chkRefReg = $this->CI->db->select("jurnal_index, main")->from("transaksi_data_registry")->where("transaksi_id", $target_transaksi_id)->get()->row();
        if (!empty($chkRefReg) && !empty($chkRefReg->jurnal_index)) {
            $jiArr = @unserialize(base64_decode($chkRefReg->jurnal_index));
            if (is_array($jiArr)) {
                $sections = array('master', 'detail', 'sub_detail');
                foreach ($sections as $sec) {
                    if (isset($jiArr[$sec]) && is_array($jiArr[$sec])) {
                        foreach ($jiArr[$sec] as $spec) {
                            if (isset($spec['loop']) && is_array($spec['loop'])) {
                                foreach ($spec['loop'] as $k => $v) {
                                    if (is_numeric($k) || preg_match('/^\d+$/', $k)) {
                                        $reks[$k] = true;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $pembantuPrefixes = array(
            "RekeningPembantuAntarcabang"        => "__rek_pembantu_antarcabang__",
            "RekeningPembantuBiayaHarusDibayar" => "__rek_pembantu_biayaharusdibayar__",
            "RekeningPembantuBiayaProjectMain"  => "__rek_pembantu_subbiayaproject__",
            "RekeningPembantuEfisiensiBiayaSub" => "__rek_pembantu_subefisiensi__",
            "RekeningPembantuRawItemEfisiensi"  => "__raw_rek_pembantu__",
        );

        $foundPembantu = array();
        foreach (array_keys($reks) as $rek) {
            foreach ($pembantuPrefixes as $comName => $prefix) {
                $tbl = $prefix . $rek;
                $chk = $this->CI->db->query("SHOW TABLES LIKE '$tbl'")->num_rows();
                if ($chk > 0) {
                    $qRows = $this->CI->db->query("SELECT * FROM `$tbl` WHERE transaksi_id = ?", array($target_transaksi_id))->result_array();
                    if (!empty($qRows)) {
                        foreach ($qRows as $row) {
                            $foundPembantu[] = array(
                                "comName" => $comName,
                                "row"     => $row
                            );
                        }
                    }
                }
            }
        }

        $this->CI->db->trans_start();

        $qCnt = $this->CI->db->query("SELECT MAX(CAST(SUBSTRING_INDEX(nomer, '.', -1) AS UNSIGNED)) as max_cnt FROM transaksi WHERE jenis='9911'");
        $rCnt = $qCnt->row();
        $nextCnt = (!empty($rCnt) && $rCnt->max_cnt > 0) ? ($rCnt->max_cnt + 1) : 1001;
        $nomer9911 = "9911." . $cabangId . "." . $nextCnt;
        $nomer9911_step = $nomer9911 . "_1_" . date("YmdHis");
        $nomer2_9911 = "J-Rej." . $cabangId . "." . $nextCnt;

        $arrCounters = array(
            "stepCode|placeID"        => array("9911|$cabangId" => (int)$nextCnt, "9911|-1" => (int)$nextCnt),
            "stepCode|olehID"         => array("9911|$myId" => (int)$nextCnt),
            "stepCode|placeID|olehID" => array("9911|$cabangId|$myId" => (int)$nextCnt),
            "stepCode"                => array("9911" => (int)$nextCnt),
        );
        $countersBlob = base64_encode(serialize($arrCounters));

        $arrIdsHis = array(
            1 => array(
                "olehID"          => $myId,
                "olehName"        => $myName,
                "step"            => 1,
                "trID"            => 0,
                "nomer"           => $nomer9911,
                "nomer2"          => $nomer2_9911,
                "counters"        => $countersBlob,
                "counters_intext" => $arrCounters,
            )
        );

        $nowDtime = date("Y-m-d H:i:s");
        $nowDate = date("Y-m-d");

        $refGudangId = !empty($targetTrx['gudang_id']) ? $targetTrx['gudang_id'] : -10;
        $refGudangNama = !empty($targetTrx['gudang_nama']) ? $targetTrx['gudang_nama'] : 'gudang cabang';
        $refGudang2Id = !empty($targetTrx['gudang2_id']) ? $targetTrx['gudang2_id'] : 0;
        $refGudang2Nama = !empty($targetTrx['gudang2_nama']) ? $targetTrx['gudang2_nama'] : '';
        $refProjectId = !empty($targetTrx['project_id']) ? $targetTrx['project_id'] : 0;
        $refProjectNama = !empty($targetTrx['project_nama']) ? $targetTrx['project_nama'] : '';

        $cancelVendorName = "";
        $cancelVendorId = 0;
        if (!empty($chkRefReg) && !empty($chkRefReg->main)) {
            $refMainArr = @unserialize(base64_decode($chkRefReg->main));
            if (is_array($refMainArr)) {
                if (!empty($refMainArr['pihakProjekGudangID'])) { $refGudang2Id = $refMainArr['pihakProjekGudangID']; }
                if (!empty($refMainArr['pihakProjekGudangNama'])) { $refGudang2Nama = $refMainArr['pihakProjekGudangNama']; }
                if (!empty($refMainArr['pihakProjekName'])) { $refProjectNama = $refMainArr['pihakProjekName']; }
                if (!empty($refMainArr['pihakWoProjekEmployeeName'])) {
                    $cancelVendorName = $refMainArr['pihakWoProjekEmployeeName'];
                    $cancelVendorId = isset($refMainArr['pihakWoProjekEmployee']) ? $refMainArr['pihakWoProjekEmployee'] : 0;
                } elseif (!empty($refMainArr['vendorDetails__label'])) {
                    $cancelVendorName = $refMainArr['vendorDetails__label'];
                } elseif (!empty($refMainArr['supplierName'])) {
                    $cancelVendorName = $refMainArr['supplierName'];
                }
            }
        }

        $divId = isset($this->CI->session->login['div_id']) ? $this->CI->session->login['div_id'] : (!empty($targetTrx['div_id']) ? $targetTrx['div_id'] : 18);
        $divName = isset($this->CI->session->login['div_name']) ? $this->CI->session->login['div_name'] : (isset($this->CI->session->login['div_nama']) ? $this->CI->session->login['div_nama'] : (!empty($targetTrx['div_nama']) ? $targetTrx['div_nama'] : 'default'));

        $conData = array();
        $conData[$cCode]['main'] = array(
            "olehID"                   => $myId,
            "olehName"                 => $myName,
            "placeID"                  => $cabangId,
            "placeName"                => !empty($targetTrx['cabang_nama']) ? $targetTrx['cabang_nama'] : "",
            "cabangID"                 => $cabangId,
            "cabangName"               => !empty($targetTrx['cabang_nama']) ? $targetTrx['cabang_nama'] : "",
            "gudangID"                 => $refGudangId,
            "gudangName"               => $refGudangNama,
            "gudang2ID"                => $refGudang2Id,
            "gudang2Name"              => $refGudang2Nama,
            "jenisTr"                  => "9911",
            "jenisTrMaster"            => "9911",
            "jenisTrTop"               => "9911",
            "jenisTrName"              => "Pembatalan transaksi",
            "jenisTrTail"              => "9911",
            "nomer"                    => $nomer9911,
            "nomerNota"                => $nomer9911,
            "nomerNota2"               => $nomer2_9911,
            "tglTransaksi"             => $nowDate,
            "fulldate"                 => $nowDate,
            "dtime"                    => $nowDtime,
            "description"              => $alasan_batal,
            "nilai_cancel"             => $targetTrx['transaksi_nilai'],
            "transaksi_nilai"          => $targetTrx['transaksi_nilai'],
            "divID"                    => $divId,
            "divName"                  => $divName,
            "pihakProjekID"            => $refProjectId,
            "pihakProjekName"          => $refProjectNama,
            "pihakProjekGudangID"      => $refGudang2Id,
            "pihakProjekGudangNama"    => $refGudang2Nama,
            "pihakWoProjekSpk"         => $no_spk,
            "supplierID"               => $cancelVendorId,
            "supplierName"             => $cancelVendorName,
            "transaksiID_reference"    => $targetTrx['id'],
            "transaksiNomer_reference" => $targetTrx['nomer'],
            "target_transaksi_id"      => $targetTrx['id'],
            "target_transaksi_nomer"   => $targetTrx['nomer'],
            "target_transaksi_jenis"   => $targetTrx['jenis'],
            "alasan_batal"             => $alasan_batal,
            "keterangan"               => $alasan_batal,
            "debet"                    => $targetTrx['transaksi_nilai'],
            "kredit"                   => $targetTrx['transaksi_nilai'],
        );

        $data9911 = array(
            "jenis_master"    => "9911",
            "jenis_top"       => "9911",
            "jenis"           => "9911",
            "transaksi_jenis" => "9911",
            "jenis_label"     => "Pembatalan transaksi",
            "nomer"           => $nomer9911,
            "nomer_top"       => $nomer9911,
            "nomer2"          => $nomer2_9911,
            "dtime"           => $nowDtime,
            "fulldate"        => $nowDate,
            "oleh_id"         => $myId,
            "oleh_nama"       => $myName,
            "cabang_id"       => $cabangId,
            "cabang_nama"     => !empty($targetTrx['cabang_nama']) ? $targetTrx['cabang_nama'] : "",
            "cabang2_id"      => $cabangId,
            "cabang2_nama"    => !empty($targetTrx['cabang_nama']) ? $targetTrx['cabang_nama'] : "",
            "gudang_id"       => $refGudangId,
            "gudang_nama"     => $refGudangNama,
            "gudang2_id"      => $refGudang2Id,
            "gudang2_nama"    => $refGudang2Nama,
            "project_id"      => $refProjectId,
            "project_nama"    => $refProjectNama,
            "div_id"          => $divId,
            "div_nama"        => $divName,
            "transaksi_nilai" => $targetTrx['transaksi_nilai'],
            "keterangan"      => $alasan_batal,
            "deskripsi"       => "Pembatalan transaksi " . $targetTrx['nomer'] . (!empty($no_spk) ? " (SPK: $no_spk)" : ""),
            "referensi_id"    => $targetTrx['id'],
            "referensi_nomer" => $targetTrx['nomer'],
            "reference_id"    => $targetTrx['id'],
            "reference_nomer" => $targetTrx['nomer'],
            "reference_jenis" => $targetTrx['jenis'],
            "step_number"     => 1,
            "step_current"    => 1,
            "step_avail"      => 1,
            "tail_number"     => 1,
            "tail_code"       => "9911",
            "counters"        => $countersBlob,
            "company_id"      => 100,
            "modul"           => 290,
            "subModul"        => 291,
            "cli"             => 1,
            "status"          => 1,
            "status_4"        => 11,
            "trash"           => 0,
            "trash_4"         => 0,
        );
        $this->CI->db->insert("transaksi", $data9911);
        $id9911 = $this->CI->db->insert_id();

        $arrIdsHis[1]['trID'] = $id9911;
        $idsHisBlob = base64_encode(serialize($arrIdsHis));
        $idsHisIntext = print_r($arrIdsHis, true);
        $this->CI->db->where("id", $id9911)->update("transaksi", array(
            "id_master"      => $id9911,
            "id_top"         => $id9911,
            "ids_his"        => $idsHisBlob,
            "ids_his_intext" => $idsHisIntext,
        ));

        $dataTd9911 = array(
            "transaksi_id"     => $id9911,
            "produk_jenis"     => "account",
            "produk_id"        => 0,
            "produk_nama"      => "Pembatalan Tagihan Biaya Vendor (" . $targetTrx['nomer'] . ")",
            "valid_qty"        => 1,
            "produk_ord_jml"   => 1,
            "produk_ord_hrg"   => $targetTrx['transaksi_nilai'],
            "sub_tail_code"    => "9911",
            "sub_step_number"  => 1,
            "sub_step_current" => 1,
            "sub_step_avail"   => 1,
            "sub_tail_number"  => 1,
            "detail_tipe"      => "items",
            "last_update"      => $nowDtime,
            "trash"            => 0,
            "keterangan"       => $alasan_batal,
        );
        $this->CI->db->insert("transaksi_data", $dataTd9911);
        $idTd9911 = $this->CI->db->insert_id();
        $indexingDetails = base64_encode(serialize(array($idTd9911)));
        $this->CI->db->where("id", $id9911)->update("transaksi", array("indexing_details" => $indexingDetails));

        $data9911_step = $data9911;
        $data9911_step['jenis']          = "9911_1";
        $data9911_step['nomer']          = $nomer9911_step;
        $data9911_step['id_master']      = $id9911;
        $data9911_step['id_top']         = 0;
        $data9911_step['link_id']        = $id9911;
        $data9911_step['ids_his']        = $idsHisBlob;
        $data9911_step['ids_his_intext'] = $idsHisIntext;
        $data9911_step['subModul']       = 292;
        $this->CI->db->insert("transaksi", $data9911_step);
        $id9911_step = $this->CI->db->insert_id();

        $conData[$cCode]['main']['transaksiID'] = $id9911;
        $conData[$cCode]['main']['transaksiID_master'] = $id9911;
        $conData[$cCode]['main']['transaksiStatus'] = "1";
        $dataRegistry9911 = array(
            "transaksi_id" => $id9911,
            "main"         => base64_encode(serialize($conData[$cCode]['main'])),
        );
        $this->CI->db->insert("transaksi_data_registry", $dataRegistry9911);

        $tr = new MdlTransaksi();
        $tr->writeSignature($id9911, array(
            "nomer"        => $nomer9911,
            "step_number"  => 1,
            "step_code"    => "9911",
            "step_name"    => "Pembatalan transaksi",
            "group_code"   => "sys",
            "oleh_id"      => $myId,
            "oleh_nama"    => $myName,
            "keterangan"   => "Pembatalan transaksi " . $targetTrx['nomer'] . " oleh " . $myName,
            "transaksi_id" => $id9911,
        ));

        // Group jurnal reversal per cabang_id agar balance debet vs kredit tidak tercampur antar cabang
        $jurnalsByCabang = array();
        if (!empty($origJurnals)) {
            foreach ($origJurnals as $oj) {
                $cId = (isset($oj['cabang_id']) && $oj['cabang_id'] !== "") ? $oj['cabang_id'] : $cabangId;
                $jurnalsByCabang[$cId][] = $oj;
            }
        }

        $revertMaster = array();
        foreach ($jurnalsByCabang as $cId => $jList) {
            $cLoop = array();
            foreach ($jList as $oj) {
                $rek = $oj['rekening'];
                $val = $this->getReversalValue($rek, $oj['debet'], $oj['kredit']);
                if (isset($cLoop[$rek])) {
                    $cLoop[$rek] = $cLoop[$rek] + $val;
                } else {
                    $cLoop[$rek] = $val;
                }
            }

            // Hilangkan saldo 0 murni agar tidak memicu validasi validator komponen
            foreach ($cLoop as $rk => $rv) {
                if (abs($rv) < 0.0001) {
                    unset($cLoop[$rk]);
                }
            }

            if (!empty($cLoop)) {
                $revertMaster[] = array(
                    "comName"        => "Jurnal",
                    "loop"           => $cLoop,
                    "static"         => array(
                        "cabang_id"    => $cId,
                        "jenis"        => "jenisTr",
                        "transaksi_no" => "nomer",
                        "transaksi_id" => $id9911,
                    ),
                    "srcGateName"    => "main",
                    "srcRawGateName" => "main",
                );
                $revertMaster[] = array(
                    "comName"        => "Rekening",
                    "loop"           => $cLoop,
                    "static"         => array(
                        "cabang_id"    => $cId,
                        "jenis"        => "jenisTr",
                        "transaksi_no" => "nomer",
                        "transaksi_id" => $id9911,
                    ),
                    "srcGateName"    => "main",
                    "srcRawGateName" => "main",
                );
            }
        }

        // Tambahkan komponen pembalik buku pembantu (subsidiary ledger)
        if (!empty($foundPembantu)) {
            foreach ($foundPembantu as $fp) {
                $comName = $fp['comName'];
                $pRow = $fp['row'];
                $rek = $pRow['rekening'];
                $val = $this->getReversalValue($rek, $pRow['debet'], $pRow['kredit']);
                if (abs($val) < 0.0001) {
                    continue;
                }

                $pStatic = array(
                    "cabang_id"    => (isset($pRow['cabang_id']) && $pRow['cabang_id'] !== "") ? $pRow['cabang_id'] : $cabangId,
                    "jenis"        => "jenisTr",
                    "transaksi_no" => "nomer",
                    "transaksi_id" => $id9911,
                );
                if (isset($pRow['extern_id'])) {
                    $pStatic["extern_id"] = $pRow['extern_id'];
                }
                if (isset($pRow['extern_nama'])) {
                    $pStatic["extern_nama"] = $pRow['extern_nama'];
                }
                if (isset($pRow['cabang2_id'])) {
                    $pStatic["cabang2_id"] = $pRow['cabang2_id'];
                }
                if (isset($pRow['cabang2_nama'])) {
                    $pStatic["cabang2_nama"] = $pRow['cabang2_nama'];
                }

                $revertMaster[] = array(
                    "comName"        => $comName,
                    "loop"           => array($rek => $val),
                    "static"         => $pStatic,
                    "srcGateName"    => "main",
                    "srcRawGateName" => "main",
                );
            }
        }

        $conData[$cCode]['revert']['jurnal'][1]['master'] = $revertMaster;

        $buildTablesMaster = array();
        $addMasterTables = array(
            "rugilaba",
            "laba ditahan",
            "rugilaba lain lain",
        );
        foreach ($addMasterTables as $trek) {
            $buildTablesMaster[] = array(
                "comName" => "RugiLaba",
                "loop"    => array(
                    "$trek" => .0,
                ),
            );
        }
        foreach ($revertMaster as $rSpec) {
            $buildTablesMaster[] = $rSpec;
        }

        if (sizeof($buildTablesMaster) > 0) {
            foreach ($buildTablesMaster as $buildTablesMaster_specs) {
                $mdlName = $buildTablesMaster_specs['comName'];
                if (substr($mdlName, 0, 1) == "{") {
                    $mdlName = trim($mdlName, "{");
                    $mdlName = trim($mdlName, "}");
                    $mdlName = str_replace($mdlName, $conData[$cCode]['main'][$mdlName], $mdlName);
                }

                $mdlName = "Com" . $mdlName;
                $this->CI->load->model("Coms/" . $mdlName);
                $m = new $mdlName();
                if (isset($buildTablesMaster_specs['loop']) && sizeof($buildTablesMaster_specs['loop']) > 0) {
                    foreach ($buildTablesMaster_specs['loop'] as $key => $val) {
                        if (substr($key, 0, 1) == "{") {
                            $oldParam = $buildTablesMaster_specs['loop'][$key];
                            unset($buildTablesMaster_specs['loop'][$key]);
                            $key = trim($key, "{");
                            $key = trim($key, "}");
                            $key = str_replace($key, $conData[$cCode]['main'][$key], $key);
                            $buildTablesMaster_specs['loop'][$key] = $oldParam;
                        }
                    }
                }
                if (method_exists($m, "getTableNameMaster")) {
                    if (sizeof($m->getTableNameMaster())) {
                        $m->buildTables($buildTablesMaster_specs);
                    }
                }
            }
        }

        $iterator = $conData[$cCode]['revert']['jurnal'][1]['master'];
        if (sizeof($iterator) > 0) {
            $compValidators = ($this->CI->config->item('transaksi_value_required_components') != null) ? $this->CI->config->item('transaksi_value_required_components') : array();
            foreach ($iterator as $cCtr => $tComSpec) {
                $comName = $tComSpec['comName'];
                $srcGateName = $tComSpec['srcGateName'];

                $tmpOutParams = array();
                if (isset($tComSpec['loop'])) {
                    foreach ($tComSpec['loop'] as $key => $value) {
                        if (substr($key, 0, 1) == "{") {
                            $key = trim($key, "{");
                            $key = trim($key, "}");
                            $key = str_replace($key, $conData[$cCode][$srcGateName][$key], $key);
                        }
                        $realValue = is_numeric($value) ? $value : makeValue($value, $conData[$cCode][$srcGateName], $conData[$cCode][$srcGateName], 0);
                        if ($key != null) {
                            $tmpOutParams['loop'][$key] = $realValue;
                        }
                    }
                }

                if (isset($tComSpec['static'])) {
                    foreach ($tComSpec['static'] as $key => $value) {
                        $realValue = is_numeric($value) ? $value : makeValue($value, $conData[$cCode][$srcGateName], $conData[$cCode][$srcGateName], 0);
                        $tmpOutParams['static'][$key] = $realValue;
                    }
                    if (!isset($tmpOutParams['static']["transaksi_id"])) {
                        $tmpOutParams['static']["transaksi_id"] = $id9911;
                    }
                    if (!isset($tmpOutParams['static']["transaksi_no"])) {
                        $tmpOutParams['static']["transaksi_no"] = $nomer9911;
                    }
                    $tmpOutParams['static']["urut"] = $cCtr;
                    $tmpOutParams['static']["fulldate"] = date("Y-m-d");
                    $tmpOutParams['static']["dtime"] = date("Y-m-d H:i:s");
                    $tmpOutParams['static']["keterangan"] = "Pembatalan transaksi nomor " . $nomer9911 . " oleh " . $myName;
                }

                $mdlName = "Com" . ucfirst($comName);
                $this->CI->load->model("Coms/" . $mdlName);
                $m = new $mdlName();

                $tobeExecuted = true;
                if (in_array($mdlName, $compValidators)) {
                    $loopParams = isset($tmpOutParams['loop']) ? $tmpOutParams['loop'] : array();
                    if (sizeof($loopParams) > 0) {
                        foreach ($loopParams as $key => $val) {
                            if ($val == 0) {
                                unset($tmpOutParams['loop'][$key]);
                            }
                        }
                    }
                    if (sizeof($tmpOutParams['loop']) < 1) {
                        $tobeExecuted = false;
                    }
                }

                if ($tobeExecuted) {
                    if (!$m->pair($tmpOutParams)) {
                        $this->CI->db->trans_rollback();
                        return array(
                            "status" => 0,
                            "reason" => "Tidak berhasil memasang values pada komponen: $comName/9911/" . __FUNCTION__ . "/" . __LINE__
                        );
                    }
                    if (!$m->exec()) {
                        $this->CI->db->trans_rollback();
                        return array(
                            "status" => 0,
                            "reason" => "Gagal saat berusaha exec values pada komponen: $comName/9911/" . __FUNCTION__ . "/" . __LINE__
                        );
                    }
                }
            }
        }

        // Tutup/Batalkan sumber pembayaran (transaksi_payment_source) transaksi target jika ada
        $this->CI->db->where("transaksi_id", $target_transaksi_id);
        $this->CI->db->update("transaksi_payment_source", array(
            "sisa"         => 0,
            "lunas"        => 0,
            "dihapus"      => 1,
            "cancel_dtime" => $nowDtime,
            "cancel_id"    => $myId,
            "cancel_name"  => $myName
        ));

        $this->CI->db->trans_complete();

        if ($this->CI->db->trans_status() === FALSE) {
            return array(
                "status" => 0,
                "reason" => "Database transaction rollback saat proses PembatalanPostingService 9911",
            );
        }

        return array(
            "status"    => 1,
            "reason"    => "Berhasil membuat transaksi pembatalan 9911",
            "id9911"    => $id9911,
            "nomer9911" => $nomer9911,
        );
    }
}
// END OF COMPLETE REPEATED LOGIC

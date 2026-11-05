<?php

require_once "Modul_Controller.php";

class _processPihakMain extends Modul_Controller
{

    public function __construct()
    {
        parent::__construct();
//        $this->jenisTr = $this->uri->segment(4);
        $cCode = "_TR_" . $this->jenisTr;

    }

    public function select()
    {

        $shoppingCartSessionDeleter = isset($this->configUi[$this->jenisTr]['shoppingCartSessionDeleter']) ? $this->configUi[$this->jenisTr]['shoppingCartSessionDeleter'] : array();
        $pihakMainValueSrc = isset($this->configUi[$this->jenisTr]['pihakMainValueSrc2']) ? $this->configUi[$this->jenisTr]['pihakMainValueSrc2'] : array();
        $staticAccountComRekening = isset($this->configCore[$this->jenisTr]['staticAccountComRekening']) ? $this->configCore[$this->jenisTr]['staticAccountComRekening'] : array();
        $recomsValidate = isset($this->configUi[$this->jenisTr]['pihakMainRecoms']) ? $this->configUi[$this->jenisTr]['pihakMainRecoms'] : array();

        $cCode = "_TR_" . $this->jenisTr;
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $mdlName = $this->uri->segment(5);
        $ppnFactor = isset($_SESSION[$cCode]["main"]["ppnFactor"]) ? $_SESSION[$cCode]["main"]["ppnFactor"] : matiHEre("undefine ppn factor, please logout and login again");

        // region pembersih session items...
        if (sizeof($shoppingCartSessionDeleter) > 0) {
            foreach ($shoppingCartSessionDeleter as $gateName => $gSpec) {
                if (is_array($gSpec) && sizeof($gSpec) > 0) {
                    foreach ($gSpec as $uSpec) {
                        $_SESSION[$cCode][$gateName][$uSpec] = NULL;
                        unset($_SESSION[$cCode][$gateName][$uSpec]);
                    }
                }
                else {
                    if (isset($_SESSION[$cCode][$gateName])) {
                        $_SESSION[$cCode][$gateName] = NULL;
                        unset($_SESSION[$cCode][$gateName]);
                    }
                }
            }
        }
//        if (isset($_SESSION[$cCode]['items']) && sizeof($_SESSION[$cCode]['items']) > 0) {
//            $_SESSION[$cCode]['items'] = NULL;
//            unset($_SESSION[$cCode]['items']);
//
//            $_SESSION[$cCode]['tableIn_detail_values'] = NULL;
//            unset($_SESSION[$cCode]['tableIn_detail_values']);
//
//            $_SESSION[$cCode]['tableIn_detail'] = NULL;
//            unset($_SESSION[$cCode]['tableIn_detail']);
//
//            $arrUnsetMain = array(
//                "seluruhnya",
//                "referenceID",
//                "referenceJenis",
//                "referenceNomer",
//            );
//            foreach ($arrUnsetMain as $uSpec) {
//                $_SESSION[$cCode]['main'][$uSpec] = NULL;
//                unset($_SESSION[$cCode]['main'][$uSpec]);
//            }
//        }

        // endregion pembersih session items...

        $this->load->model("Mdls/" . $mdlName);
        $b = new $mdlName();
        $tmpB = $b->lookupByID($id)->result();
//        showLast_query("biru");
//        arrPrint($tmpB);
//
//mati_disini();
        $selectColumn = "nama";
        $arrCekKolomPihak = array(
            "pihakMainNota" => "nomer",
            "pihakMainNotaReference" => "nomer",
//            "pihakNameMainDiskon" => "per_supplier_diskon_nama",
            "pihakNameMainDiskon" => "nama",
        );
        foreach ($arrCekKolomPihak as $keyCek => $valueCek) {
            if (isset($this->configUi[$this->jenisTr][$keyCek]) && $this->configUi[$this->jenisTr][$keyCek] == true) {
                $selectColumn = $valueCek;
                break;
            }
        }
//cekhitam(":: $selectColumn ::");
//matiHere(__LINE__);
        //-----------------------------------------------------
        $dtime_now = dtimeNow();
        $dtime_now_ex = explode(" ", $dtime_now);
        $date_now = str_replace("-", "", $dtime_now_ex[0]);
        $time_now = str_replace(":", "", $dtime_now_ex[1]);
        $bookingNumber = "$date_now" . "$time_now";
        if (!isset($_SESSION[$cCode]["main"]["bookingNumber"]) || ($_SESSION[$cCode]["main"]["bookingNumber"] == null)) {
            $_SESSION[$cCode]["main"]["bookingNumber"] = $bookingNumber;
        }
        //-----------------------------------------------------

        $this->load->library("ValueGate");
        $vg = new ValueGate();
        $vg->setConfigUiJenis($this->configUiJenis);
        $vg->setConfigCoreJenis($this->configCoreJenis);
        $vg->setConfigValuesJenis($this->configValuesJenis);
        $vg->setPpnFactor($ppnFactor);


        if (sizeof($tmpB) > 0) {

            $data_id = isset($this->configUi[$this->jenisTr]["pihakNameMainDiskonIdProcessor"]) ? $this->configUi[$this->jenisTr]["pihakNameMainDiskonIdProcessor"] : "id";
//            mati_disini(":: $data_id ::");
            $_SESSION[$cCode]['main']['pihakMainID'] = $tmpB[0]->$data_id;
            $_SESSION[$cCode]['main']['pihakMainName'] = isset($tmpB[0]->$selectColumn) ? $tmpB[0]->$selectColumn : "";
            $_SESSION[$cCode]['main']['pihakMainLabel'] = isset($tmpB[0]->label) ? $tmpB[0]->label : "";
//            $_SESSION[$cCode]['main']['pihakMainLabel'] = isset($tmpB[0]->per_supplier_diskon_nama) ? $tmpB[0]->per_supplier_diskon_nama : "";

            $stat = isset($tmpB[0]->$selectColumn) ? $tmpB[0]->$selectColumn : "";
            if (sizeof($staticAccountComRekening) > 0) {
                $_SESSION[$cCode]['main']['pihakMainAkum'] = $staticAccountComRekening[$stat];
                $_SESSION[$cCode]['main']['pihakMainAkumDetails'] = "akum penyu " . $stat;
            }

            $tmpPihakName = isset($tmpB[0]->$selectColumn) ? formatNota($selectColumn, $tmpB[0]->$selectColumn) : "";

            if (isset($tmpB[0]->name)) {
                $tmpPihakName = $tmpB[0]->name;
            }

            if (sizeof($pihakMainValueSrc) > 0) {
                foreach ($pihakMainValueSrc as $key => $src) {
                    $_SESSION[$cCode]['main'][$key] = $tmpB[0]->$src;
                }
            }
//            arrPrintWebs( $_SESSION[$cCode]['main']['pihakMainName']);
//            matiHere($selectColumn.":: ".$tmpB[0]->$selectColumn);
            if (sizeof($recomsValidate) > 0) {
                arrPrint($recomsValidate);
                cekLIme($id);
                $mdlName = $recomsValidate['mdlName'];
                $pihakFilters = $recomsValidate['filters'];
                $selectMethod = isset($recomsValidate['selectMethod'][$id]) ? $recomsValidate['selectMethod'][$id] : false;
//                matiHEre($selectMethod." ".$id);
                $validateField = isset($recomsValidate['usedFields']) ? $recomsValidate['usedFields'] : "";
                $replaceTarget = $recomsValidate['targetField'];
                $this->load->model("Mdls/" . $mdlName);
                $m = new $mdlName();
                if (sizeof($pihakFilters) > 0) {
                    foreach ($pihakFilters as $f) {
                        $f_ex = explode("=", $f);
                        if (!isset($f_ex[1])) {
                            $f_ey = explode(">", $f_ex[0]);
                            if (substr($f_ey[1], 0, 1) == ".") {
                                $m->addFilter($f_ey[0] . ">'" . ltrim($f_ey[1], ".") . "'");
                            }
                            else {
                                $m->addFilter($f_ey[0] . ">'" . $this->session->login[$f_ey[1]] . "'");
                            }
                        }
                        else {
                            if (substr($f_ex[1], 0, 1) == ".") {
                                $m->addFilter($f_ex[0] . "='" . ltrim($f_ex[1], ".") . "'");
                            }
                            else {
//                                        matiHEre("ini".$f_ex[1]);
                                $m->addFilter($f_ex[0] . "='" . $_SESSION[$cCode]['main'][$f_ex[1]] . "'");
                            }
                        }
                    }
                }
                $temp2 = $m->lookUpAll()->result();
                if (sizeof($temp2) > 0) {
                    unset($_SESSION[$cCode]['main'][$replaceTarget]);
                    unset($_SESSION[$cCode]['main']["pphGate"]);
                    if (isset($_SESSION[$cCode]['items']) && sizeof($_SESSION[$cCode]['items']) > 0) {
                        foreach ($_SESSION[$cCode]['items'] as $keyID => $items) {
                            unset($_SESSION[$cCode]['items'][$keyID][$replaceTarget]);
                        }
                    }

                    if (strlen($temp2[0]->$validateField) == 0) {
                        if ($selectMethod) {
                            $_SESSION[$cCode]['main'][$replaceTarget] = 4;
                            $_SESSION[$cCode]['main']['pphGate'] = "hutang pph23";
                        }
                        else {
                            $_SESSION[$cCode]['main'][$replaceTarget] = 10;
                            $_SESSION[$cCode]['main']['pphGate'] = "hutang pph4 ayat 2";
                        }
                    }
                    else {
                        if ($selectMethod) {
//                            matiHere($selectMethod);
                            $_SESSION[$cCode]['main'][$replaceTarget] = 2;
                            $_SESSION[$cCode]['main']['pphGate'] = "hutang pph23";
                        }
                        else {
                            $_SESSION[$cCode]['main'][$replaceTarget] = 10;
                            $_SESSION[$cCode]['main']['pphGate'] = "hutang pph4 ayat 2";
                        }
                    }

                }

            }
//            mati_disini();


            $initMasterValues = array(
                "olehID" => my_id(),
                "olehName" => my_name(),
                "sellerID" => my_id(),
                "sellerName" => my_name(),
                "placeID" => my_cabang_id(),
                "placeName" => my_cabang_nama(),
                "divID" => my_div_id(),
                "divName" => my_div_nama(),
                "cabangID" => my_cabang_id(),
                "cabangName" => my_cabang_nama(),
                "gudangID" => my_gudang_id(),
                "gudangName" => my_gudang_nama(),
                "jenis_usaha" => my_jenis_usaha(),
                "tokoID" => my_toko_id(),
                "tokoNama" => my_toko_nama(),
                "jenisTr" => $this->jenisTr,
                "jenisTrMaster" => $this->jenisTr,
                "jenisTrTop" => $this->configUiJenis['steps'][1]['target'],
                "jenisTrName" => $this->configUiJenis['steps'][1]['label'],
                "stepNumber" => 1,
                "stepCode" => isset($this->configUiJenis['steps'][1]['target']) ? $this->configUiJenis['steps'][1]['target'] : 0,
                "dtime" => dtimeNow(),
                "fulldate" => dtimeNow("Y-m-d"),
                // "jenis_pajak"=>$this->session->login['jenis_usaha'],
            );
            $vg->buildValue($this->jenisTr, $id, $initMasterValues, $this->modul);
            echo "<script>";
            echo "  top.$('#shopping_cart').load('" . MODUL_PATH . "/_shoppingCart/viewCart/" . $this->jenisTr . "?selID=$id');";
//            echo "top.document.getElementById('pihakMainName').value='" . $_SESSION[$cCode]['main']['pihakMainName'] . "';";
            echo "top.document.getElementById('pihakMainName').value='" . $_SESSION[$cCode]['main']['pihakMainLabel'] . "';";
            echo "top.document.getElementById('pilihan_main').innerHTML='';";
//            echo "top.document.getElementById('pilihan_item').style.display='none';";
            echo "</script>";
        }
        else {
//            $_SESSION[$cCode]['main']['pihakMainID'] = $id;
//            $_SESSION[$cCode]['main']['pihakMainName'] = "";
//            if (sizeof($pihakMainValueSrc) > 0) {
//                foreach ($pihakMainValueSrc as $key => $src) {
//                    $_SESSION[$cCode]['main'][$key] = $tmpB[0]->$src;
//                }
//            }

            echo "<script>";
            echo "  top.$('#shopping_cart').load('" . MODUL_PATH . "/_shoppingCart/viewCart/" . $this->jenisTr . "?selID=$id');";
            echo "top.document.getElementById('pihakMain').value='" . $_SESSION[$cCode]['main']['pihakMainName'] . "';";
            echo "top.document.getElementById('pilihan_main').innerHTML='';";
            echo "</script>";
        }


    }

    public function remove()
    {
        $cCode = "_TR_" . $this->jenisTr;
        $_SESSION[$cCode]['main']['pihakMainID'] = null;
        $_SESSION[$cCode]['main']['pihakMainName'] = null;
        $_SESSION[$cCode]['main']['pihakMdlName'] = null;
        unset($_SESSION[$cCode]['main']['pihakMainID']);
        unset($_SESSION[$cCode]['main']['pihakMainName']);
        unset($_SESSION[$cCode]['main']['pihakMdlName']);
        unset($_SESSION[$cCode]['items']);

    }


    public function select_2()
    {

        $shoppingCartSessionDeleter = isset($this->configUi[$this->jenisTr]['shoppingCartSessionDeleter']) ? $this->configUi[$this->jenisTr]['shoppingCartSessionDeleter'] : array();
        $pihakMainValueSrc = isset($this->configUi[$this->jenisTr]['pihakMainValueSrc2']) ? $this->configUi[$this->jenisTr]['pihakMainValueSrc2'] : array();
        $staticAccountComRekening = isset($this->configCore[$this->jenisTr]['staticAccountComRekening']) ? $this->configCore[$this->jenisTr]['staticAccountComRekening'] : array();
        $recomsValidate = isset($this->configUi[$this->jenisTr]['pihakMainRecoms']) ? $this->configUi[$this->jenisTr]['pihakMainRecoms'] : array();
        $pihakMainProccessFilters = isset($this->configUi[$this->jenisTr]['pihakMainProccessFilters']) ? $this->configUi[$this->jenisTr]['pihakMainProccessFilters'] : array();
        $pihakMainProccessFilters = isset($this->configUi[$this->jenisTr]['pihakMainProccessFilters']) ? $this->configUi[$this->jenisTr]['pihakMainProccessFilters'] : array();


        $cCode = "_TR_" . $this->jenisTr;
        $id = isset($_GET['id']) ? $_GET['id'] : 0;
        $mdlName = $this->uri->segment(5);
        $ppnFactor = isset($_SESSION[$cCode]["main"]["ppnFactor"]) ? $_SESSION[$cCode]["main"]["ppnFactor"] : matiHEre("undefine ppn factor, please logout and login again");

        // region pembersih session items...
        if (sizeof($shoppingCartSessionDeleter) > 0) {
            foreach ($shoppingCartSessionDeleter as $gateName => $gSpec) {
                if (is_array($gSpec) && sizeof($gSpec) > 0) {
                    foreach ($gSpec as $uSpec) {
                        $_SESSION[$cCode][$gateName][$uSpec] = NULL;
                        unset($_SESSION[$cCode][$gateName][$uSpec]);
                    }
                }
                else {
                    if (isset($_SESSION[$cCode][$gateName])) {
                        $_SESSION[$cCode][$gateName] = NULL;
                        unset($_SESSION[$cCode][$gateName]);
                    }
                }
            }
        }


        // endregion pembersih session items...


        $this->load->model("MdlTransaksi");
        $tri = New MdlTransaksi();
        $tri->addFilter("id='$id'");
        $triTmp = $tri->lookupAll()->result();
        if (sizeof($triTmp) > 0) {
            $jenis_reference = $triTmp[0]->jenis;
            $trash_4 = $triTmp[0]->trash_4;
            $trash = isset($triTmp[0]->trash) ? $triTmp[0]->trash : 0;
            $status = isset($triTmp[0]->status) ? $triTmp[0]->status : 1;

            $isBatalTrx = ($trash_4 == 1 || $trash == 1 || $status == 0);
            $isRefBatal = false;
            $ref_nomer = !empty($triTmp[0]->reference_nomer) ? $triTmp[0]->reference_nomer : "";

            if (!$isBatalTrx && !empty($ref_nomer)) {
                $qRefTr = $this->db->query("SELECT trash_4, trash, cancel_transaksi_nomer, cancel_name, cancel_dtime, keterangan FROM transaksi WHERE nomer = ? LIMIT 1", array($ref_nomer));
                if ($qRefTr && $qRefTr->num_rows() > 0) {
                    $rRefTr = $qRefTr->row();
                    if ($rRefTr->trash_4 == 1 || $rRefTr->trash == 1) {
                        $isBatalTrx = true;
                        $isRefBatal = true;
                    }
                }
            }

            if ($isBatalTrx) {
                $cancel_nama = !empty($triTmp[0]->cancel_name) ? $triTmp[0]->cancel_name : "User";
                $cancel_nomer = !empty($triTmp[0]->cancel_transaksi_nomer) ? $triTmp[0]->cancel_transaksi_nomer : "";
                $cancel_dtime = !empty($triTmp[0]->cancel_dtime) ? $triTmp[0]->cancel_dtime : "-";
                $cancel_alasan = !empty($triTmp[0]->deskripsi) ? $triTmp[0]->deskripsi : (!empty($triTmp[0]->keterangan) ? $triTmp[0]->keterangan : "");

                if ($isRefBatal && isset($rRefTr)) {
                    if (!empty($rRefTr->cancel_name)) {
                        $cancel_nama = $rRefTr->cancel_name;
                    }
                    if (!empty($rRefTr->cancel_transaksi_nomer)) {
                        $cancel_nomer = $rRefTr->cancel_transaksi_nomer;
                    }
                    if (!empty($rRefTr->cancel_dtime)) {
                        $cancel_dtime = $rRefTr->cancel_dtime;
                    }
                    if (empty($cancel_alasan) && !empty($rRefTr->keterangan)) {
                        $cancel_alasan = $rRefTr->keterangan;
                    }
                }

                $nomer_trx = !empty($triTmp[0]->nomer) ? $triTmp[0]->nomer : "-";
                $jenis_trx = !empty($triTmp[0]->jenis) ? $triTmp[0]->jenis : "";
                $label_trx = !empty($triTmp[0]->jenis_label) ? $triTmp[0]->jenis_label : "Pembelian / GRN";

                // Jika cancel_transaksi_nomer kosong di baris transaksi, lookup ke dokumen pembatalan terkait
                if (empty($cancel_nomer)) {
                    $cancel_jenis = !empty($triTmp[0]->cancel_transaksi_jenis) ? $triTmp[0]->cancel_transaksi_jenis : "";
                    $cancel_id = !empty($triTmp[0]->cancel_id) ? $triTmp[0]->cancel_id : 0;

                    if (!empty($cancel_jenis) && !empty($cancel_dtime) && ($cancel_dtime != "-")) {
                        $qCancel = $this->db->query("SELECT nomer, keterangan FROM transaksi WHERE oleh_id = ? AND dtime = ? AND jenis = ? LIMIT 1", array(
                            $cancel_id,
                            $cancel_dtime,
                            $cancel_jenis
                        ));
                        if ($qCancel && ($qCancel->num_rows() > 0)) {
                            $rCancel = $qCancel->row();
                            $cancel_nomer = $rCancel->nomer;
                            if (empty($cancel_alasan) && !empty($rCancel->keterangan)) {
                                $cancel_alasan = $rCancel->keterangan;
                            }
                        }
                    }

                    if (empty($cancel_nomer)) {
                        $qCancelRef = $this->db->query("SELECT nomer, keterangan FROM transaksi WHERE reference_id = ? OR reference_nomer = ? LIMIT 1", array(
                            $triTmp[0]->id,
                            $nomer_trx
                        ));
                        if ($qCancelRef && ($qCancelRef->num_rows() > 0)) {
                            $rCancelRef = $qCancelRef->row();
                            $cancel_nomer = $rCancelRef->nomer;
                            if (empty($cancel_alasan) && !empty($rCancelRef->keterangan)) {
                                $cancel_alasan = $rCancelRef->keterangan;
                            }
                        }
                    }
                }

                // Penentuan nama menu asal untuk panduan user
                $nama_menu_asal = "Pembelian Langsung (4643)";
                if ($jenis_trx == "467") {
                    $nama_menu_asal = "Penerimaan Barang / GRN (467)";
                }
                elseif ($jenis_trx == "3344") {
                    $nama_menu_asal = "Input Klaim Tambahan (3344)";
                }

                $clean_alasan = htmlspecialchars(str_replace(array('"', "'", "\r", "\n"), array('', '', ' ', ' '), $cancel_alasan));
                $cancelNomerText = !empty($cancel_nomer) ? "<b>" . htmlspecialchars(str_replace('"', '', $cancel_nomer)) . "</b>" : "<span style='color:#e74c3c;'><i>(Tidak tercatat di sistem)</i></span>";
                $refNomerText = !empty($ref_nomer) ? "<b>" . htmlspecialchars(str_replace('"', '', $ref_nomer)) . "</b> (Purchase Order)" : "-";
                $alasanText = !empty($clean_alasan) ? "<i>&ldquo;" . $clean_alasan . "&rdquo;</i>" : "-";

                $batalHeadingDesc = $isRefBatal
                    ? "Realisasi Klaim tidak dapat dilanjutkan karena dokumen referensi utama (PO <b>{$ref_nomer}</b>) berstatus <b>DIBATALKAN (CANCELLED)</b>."
                    : "Realisasi Klaim tidak dapat dilanjutkan karena dokumen asal transaksi ini berstatus <b>DIBATALKAN (CANCELLED)</b>.";

                $msg = "<div style='text-align: left; font-size: 13px; line-height: 1.6; color: #333;'>"
                     . "  <div style='background-color: #fce4e4; border-left: 4px solid #e74c3c; padding: 10px 12px; margin-bottom: 12px; border-radius: 3px;'>"
                     . "    <b style='color: #c0392b; font-size: 14px;'><i class='fa fa-ban'></i> Transaksi Telah Dibatalkan</b><br>"
                     . "    {$batalHeadingDesc}"
                     . "  </div>"
                     . "  <table style='width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 12px;'>"
                     . "    <tr><td style='width: 140px; padding: 3px 0; color: #7f8c8d;'>Dokumen Asal</td><td style='padding: 3px 0;'>: <b>{$nomer_trx}</b> ({$label_trx})</td></tr>"
                     . (!empty($ref_nomer) ? "    <tr><td style='padding: 3px 0; color: #7f8c8d;'>Referensi PO</td><td style='padding: 3px 0;'>: {$refNomerText}</td></tr>" : "")
                     . "    <tr><td style='padding: 3px 0; color: #7f8c8d;'>Dokumen Pembatalan</td><td style='padding: 3px 0;'>: {$cancelNomerText}</td></tr>"
                     . "    <tr><td style='padding: 3px 0; color: #7f8c8d;'>Dibatalkan Oleh</td><td style='padding: 3px 0;'>: <b>{$cancel_nama}</b></td></tr>"
                     . "    <tr><td style='padding: 3px 0; color: #7f8c8d;'>Waktu Pembatalan</td><td style='padding: 3px 0;'>: {$cancel_dtime}</td></tr>"
                     . (!empty($cancel_alasan) ? "    <tr><td style='padding: 3px 0; color: #7f8c8d;'>Alasan Batal</td><td style='padding: 3px 0;'>: {$alasanText}</td></tr>" : "")
                     . "  </table>"
                     . "  <div style='background-color: #edf7ed; border-left: 4px solid #27ae60; padding: 8px 12px; font-size: 12px; border-radius: 3px;'>"
                     . "    <b><i class='fa fa-arrow-circle-right'></i> Petunjuk Pengecekan:</b><br>"
                     . "    Silahkan periksa riwayat status dokumen di menu <b>{$nama_menu_asal}</b> dengan mencari nomor <b>{$nomer_trx}</b>"
                     . (!empty($ref_nomer) ? " atau nomor PO <b>{$ref_nomer}</b>." : ".")
                     . (!empty($cancel_nomer) ? " Untuk melihat dokumen pembatalan, periksa nomor <b>{$cancel_nomer}</b> di menu Pembatalan/Retur." : "")
                     . "  </div>"
                     . "</div>";

                // Sanitasi agar tidak mengandung kutip dua atau newline yang dapat merusak string JavaScript SweetAlert
                $msg = str_replace(array('"', "\r", "\n"), array("'", ' ', ' '), $msg);

                mati_disini($msg);
            }

            $trr = new MdlTransaksi();
            $trr->setFilters(array());
            $trr->addFilter("transaksi_id='$id'");
            $trr->setJointSelectFields("transaksi_id,main");
            $tmpReg = $trr->lookupDataRegistries()->result();
            $main = blobDecode($tmpReg[0]->main);
            switch ($jenis_reference) {
                case "467":
                    $diskon_reguler = isset($main["diskon_nilai_total"]) ? $main["diskon_nilai_total"] : 0;
                    $diskon_freeproduk = isset($main["produk_rel_harga"]) ? $main["produk_rel_harga"] : 0;
                    $diskon_total = $diskon_reguler + $diskon_freeproduk;
                    $_SESSION[$cCode]["main"]["diskon_total_maksimal"] = $diskon_total;
                    break;
                case "4643":
                    $diskon_total = $main["diskon_nilai"];
                    $_SESSION[$cCode]["main"]["diskon_total_maksimal"] = $diskon_total;
                    break;
                case "3344":
                    $diskon_total = $main["nilai_piutang"];
                    $_SESSION[$cCode]["main"]["diskon_total_maksimal"] = $diskon_total;
                    break;
            }
        }


        $this->load->model("Mdls/" . $mdlName);
        $b = new $mdlName();
        if (sizeof($pihakMainProccessFilters) > 0) {
            foreach ($pihakMainProccessFilters as $f) {
                $f_ex = explode("=", $f);
                if (!isset($f_ex[1])) {
                    $f_ey = explode(">", $f_ex[0]);
                    if (substr($f_ey[1], 0, 1) == ".") {
                        $b->addFilter($f_ey[0] . ">'" . ltrim($f_ey[1], ".") . "'");
                    }
                    else {
                        $b->addFilter($f_ey[0] . ">'" . $_SESSION[$cCode]['main'][$f_ey[1]] . "'");
                    }
                }
                else {
                    if (substr($f_ex[1], 0, 1) == ".") {
                        $b->addFilter($f_ex[0] . "='" . ltrim($f_ex[1], ".") . "'");
                    }
                    else {
                        $b->addFilter($f_ex[0] . "='" . $_SESSION[$cCode]['main'][$f_ex[1]] . "'");
                    }
                }
            }
            if ($id > 0) {
                $b->addFilter("transaksi_id=$id");
            }
            $tmpB_0 = $b->lookupAll()->result();
            if (sizeof($tmpB_0) > 0) {
                $tmpB[0] = $tmpB_0[0];
            }
            cekHere(count($tmpB));
        }
        else {
            $tmpB = $b->lookupByID($id)->result();
            cekHere(count($tmpB));
        }
//        showLast_query("biru");
//        arrPrint($tmpB);
//        mati_disini(__LINE__);

        $selectColumn = "nomer";
//        $arrCekKolomPihak = array(
//            "pihakMainNota" => "nomer",
//            "pihakMainNotaReference" => "nomer",
////            "pihakNameMainDiskon" => "per_supplier_diskon_nama",
//            "pihakNameMainDiskon" => "nama",
//        );
//        foreach ($arrCekKolomPihak as $keyCek => $valueCek) {
//            if (isset($this->configUi[$this->jenisTr][$keyCek]) && $this->configUi[$this->jenisTr][$keyCek] == true) {
//                $selectColumn = $valueCek;
//                break;
//            }
//        }
//        cekhitam(":: $selectColumn ::");
//matiHere(__LINE__);
        //-----------------------------------------------------
        $dtime_now = dtimeNow();
        $dtime_now_ex = explode(" ", $dtime_now);
        $date_now = str_replace("-", "", $dtime_now_ex[0]);
        $time_now = str_replace(":", "", $dtime_now_ex[1]);
        $bookingNumber = "$date_now" . "$time_now";
        if (!isset($_SESSION[$cCode]["main"]["bookingNumber"]) || ($_SESSION[$cCode]["main"]["bookingNumber"] == null)) {
            $_SESSION[$cCode]["main"]["bookingNumber"] = $bookingNumber;
        }
        //-----------------------------------------------------

        $this->load->library("ValueGate");
        $vg = new ValueGate();
        $vg->setConfigUiJenis($this->configUiJenis);
        $vg->setConfigCoreJenis($this->configCoreJenis);
        $vg->setConfigValuesJenis($this->configValuesJenis);
        $vg->setPpnFactor($ppnFactor);


        if (sizeof($tmpB) > 0) {

            $data_id = isset($this->configUi[$this->jenisTr]["pihakNameMainDiskonIdProcessor"]) ? $this->configUi[$this->jenisTr]["pihakNameMainDiskonIdProcessor"] : "id";
            $_SESSION[$cCode]['main']['pihakMainID'] = $tmpB[0]->$data_id;
            $_SESSION[$cCode]['main']['pihakMainName'] = isset($tmpB[0]->$selectColumn) ? $tmpB[0]->$selectColumn : "";
            $_SESSION[$cCode]['main']['pihakMainLabel'] = isset($tmpB[0]->$selectColumn) ? $tmpB[0]->$selectColumn : "";
//            $_SESSION[$cCode]['main']['pihakMainLabel'] = isset($tmpB[0]->label) ? $tmpB[0]->label : "";

            $ref_nomer = isset($tmpB[0]->$selectColumn) ? $tmpB[0]->$selectColumn : "";
            $ref_nomer_ex = explode(".", $ref_nomer);
            $_SESSION[$cCode]['main']['pihakMainReferenceJenis'] = $ref_nomer_ex[0];

            $stat = isset($tmpB[0]->$selectColumn) ? $tmpB[0]->$selectColumn : "";
            $transaksi_id = $_SESSION[$cCode]['main']['pihakMainID'];


            $pihakID = isset($_SESSION[$cCode]["main"]["pihakID"]) ? $_SESSION[$cCode]["main"]["pihakID"] : 0;
            $pihakMainID = isset($_SESSION[$cCode]["main"]["pihakMainID"]) ? $_SESSION[$cCode]["main"]["pihakMainID"] : 0;
            $scriptBottom = "";
            if ($pihakID > 0) {
                if ($pihakMainID > 0) {
                    $loaderTrigger = isset($this->configUi[$this->jenisTr]['loaderTrigger']) ? $this->configUi[$this->jenisTr]['loaderTrigger'] : array();
                    if (sizeof($loaderTrigger) > 0) {
                        if (isset($loaderTrigger["enabled"]) && ($loaderTrigger["enabled"] == true)) {
                            // region reset gerbang
                            if (isset($_SESSION[$cCode]["items"])) {
//                                $_SESSION[$cCode]["items"] = NULL;
//                                unset($_SESSION[$cCode]["items"]);
//                                $mainReset = array(
//                                    "nilai_persediaan",
//                                    "nilai_piutang",
//                                    "nilai_credit_note",
//                                    "nilai_voucher",
//                                    "nilai_cash",
//                                    "nilai_pph23",
//                                    "grandtotal_netto",
//                                    "diskon_supplier_nilai",
//                                );
//                                foreach ($mainReset as $kreset){
//                                    $_SESSION[$cCode]["main"][$kreset] = NULL;
//                                    unset($_SESSION[$cCode]["main"][$kreset]);
//                                }
                            }
                            // endregion reset gerbang

                            $link = $loaderTrigger["link"];
                            $linkSelected = $loaderTrigger["linkSelected"] . "?selector&id=0&minValue=0&transaksi_id=$transaksi_id&extern_id=0";
                            $link_items = MODUL_PATH . "$link";
                            $link_items_selected = MODUL_PATH . "$linkSelected";
                            $scriptBottom .= "<script>
                                   top.$('#pilihan_item').load('$link_items');
                                   top.$('#result').load('$link_items_selected');
                         </script>
                        ";
                        }
                    }
                }
            }

            $_SESSION[$cCode]["main"]["revisi_ppn"] = 0;

            $initMasterValues = array(
                "olehID" => my_id(),
                "olehName" => my_name(),
                "sellerID" => my_id(),
                "sellerName" => my_name(),
                "placeID" => my_cabang_id(),
                "placeName" => my_cabang_nama(),
                "divID" => my_div_id(),
                "divName" => my_div_nama(),
                "cabangID" => my_cabang_id(),
                "cabangName" => my_cabang_nama(),
                "gudangID" => my_gudang_id(),
                "gudangName" => my_gudang_nama(),
                "jenis_usaha" => my_jenis_usaha(),
                "tokoID" => my_toko_id(),
                "tokoNama" => my_toko_nama(),
                "jenisTr" => $this->jenisTr,
                "jenisTrMaster" => $this->jenisTr,
                "jenisTrTop" => $this->configUiJenis['steps'][1]['target'],
                "jenisTrName" => $this->configUiJenis['steps'][1]['label'],
                "stepNumber" => 1,
                "stepCode" => isset($this->configUiJenis['steps'][1]['target']) ? $this->configUiJenis['steps'][1]['target'] : 0,
                "dtime" => dtimeNow(),
                "fulldate" => dtimeNow("Y-m-d"),
                // "jenis_pajak"=>$this->session->login['jenis_usaha'],
            );
            $vg->buildValue($this->jenisTr, $id, $initMasterValues, $this->modul);
            echo $scriptBottom;
            echo "<script>";
            echo "top.$('#shopping_cart').load('" . MODUL_PATH . "/_shoppingCart/viewCart/" . $this->jenisTr . "?selID=$id');";
            echo "top.document.getElementById('pihakMainName').value='" . $_SESSION[$cCode]['main']['pihakMainLabel'] . "';";
            echo "top.document.getElementById('pilihan_main').innerHTML='';";
            echo "</script>";
        }
        else {

            echo "<script>";
            echo "  top.$('#shopping_cart').load('" . MODUL_PATH . "/_shoppingCart/viewCart/" . $this->jenisTr . "?selID=$id');";
            echo "top.document.getElementById('pihakMain').value='" . $_SESSION[$cCode]['main']['pihakMainName'] . "';";
            echo "top.document.getElementById('pilihan_main').innerHTML='';";
            echo "</script>";
        }


    }


}


<?php


class ComProjectTasklist extends MdlMother
{

    protected $filters = array();
    protected $tableName;
    private $tableName_mutasi;
    private $tableName_fifoAvg;
    protected $tableName_master = array();
    private $inParams = array( //===inputan dari transaksi

    );
    private $outParams = array( //===output ke tabel

    );
    private $outFields = array( // dari tabel cache
        "id",
        "nama",
        "nomer",
        "cabang_id",
        "produk_paket_id",
        "produk_paket_nama",
        "no_kontrak",
        "tgl_kontrak",
        "nilai_kontrak",
        "dtime_spk",
        "no_spk",
        "no_spk2",
        "no_pre_spk",
        "persen_sub",
        "nilai_sub_fase",
        "debet",
        "kredit",
        "sisa",
        "material",
        "fase_id",
        "sub_fase_id",
        "produk_id",
        "pre_spk_validasi_img",
        "produk_nama",
        "type_pelaksana",
        "employee_id",
        "employee_nama",
        "project_workorder_id",
        "project_workorder_nama",
        "dtime",
        "nilai",
        "file",
        "status",
        "trash",
        "input_property",
        "fase_nama",
        "dtime_start",
        "dtime_end",
        "oleh_id",
        "oleh_nama",
        "progress_id",
        "progress_nama",
        "progress_percent",
        "qc_nomer",
        "qc_auth_id",
        "qc_auth_nama",
        "qc_dtime",
        "distribute_percent",
        "owner_nama",
        "dtime_diff",
        "invoiced",
        "inv_dtime",
        "inv_trx_id",
        "post_biaya_id",
        "post_biaya_no",
        "post_biaya_dtime",
        "post_return_id",
        "post_return_no",
        "post_return_dtime",
        "post_return_cli",
        "approved",
        "approval_id",
        "approval_nama",
        "approval_dtime",
        "approval_nilai",
        "reject_no",
        "approve_no",
        "gudang_wo",
        "is_group",
        "group_id",
        "group_nama",
        "catatan_pelaksana",

    );
    private $koloms = array(
        "cabang_id",
        "produk_id",
        "nama",
        "jml",
        "hpp",
        "jml_nilai",
        //        "jml_ot",
        //        "jml_nilai_ot",
    );
    private $outFieldsMutasi = array( // dari tabel rek mutasi rekening
        "transaksi_id",
        "transaksi_no",
        "transaksi_jenis",
        "cabang_id",
        "cabang_nama",
        "debet_awal",
        "debet",
        "debet_akhir",
        "kredit_awal",
        "kredit",
        "kredit_akhir",
        "qty_debet_awal",
        "qty_debet",
        "qty_debet_akhir",
        "qty_kredit_awal",
        "qty_kredit",
        "qty_kredit_akhir",
        "dtime",
        "extern_id",
        "extern_nama",
        "extern2_id",
        "extern2_nama",
        "jenis",
        "npwp",
        "fulldate",
        "gudang_id",
        "gudang_nama",
        "keterangan",
        "harga",
        "harga_avg",
        "harga_awal",
        "supplierID",
    );
    private $periode = array("harian", "bulanan", "tahunan", "forever");
    protected $jenisTr;
    protected $sortBy = array(
        "kolom" => "id",
        "mode" => "asc",
    );

    public function __construct()
    {

        $this->tableName = "project_tasklist";
        $this->load->model("Mdls/MdlTasklistProject");
    }

    //  region setter, getter
    public function getJenisTr()
    {
        return $this->jenisTr;
    }

    public function setJenisTr($jenisTr)
    {
        $this->jenisTr = $jenisTr;
    }

    public function getSortBy()
    {
        return $this->sortBy;
    }

    public function setSortBy($sortBy)
    {
        $this->sortBy = $sortBy;
    }

    public function getTableNameMaster()
    {
        return $this->tableName_master;
    }

    public function setTableNameMaster($tableName_master)
    {
        $this->tableName_master = $tableName_master;
    }

    public function getPeriode()
    {
        return $this->periode;
    }

    public function setPeriode($periode)
    {
        $this->periode = $periode;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    public function getTableNameTmp()
    {
        return $this->tableName__tmp;
    }

    public function setTableNameTmp($tableName__tmp)
    {
        $this->tableName__tmp = $tableName__tmp;
    }

    public function getFilters()
    {
        return $this->filters;
    }

    public function setFilters($filters)
    {
        $this->filters = $filters;
    }

    public function getInParams()
    {
        return $this->inParams;
    }

    public function setInParams($inParams)
    {
        $this->inParams = $inParams;
    }

    public function getOutParams()
    {
        return $this->outParams;
    }

    public function setOutParams($outParams)
    {
        $this->outParams = $outParams;
    }

    public function getOutFields()
    {
        return $this->outFields;
    }

    public function setOutFields($outFields)
    {
        $this->outFields = $outFields;
    }

    public function getOutFieldsMutasi()
    {
        return $this->outFieldsMutasi;
    }

    public function setOutFieldsMutasi($outFieldsMutasi)
    {
        $this->outFieldsMutasi = $outFieldsMutasi;
    }

    public function getTableNameMutasi()
    {
        return $this->tableName_mutasi;
    }

    //  endregion setter, getter

    public function setTableNameMutasi($tableName_mutasi)
    {
        $this->tableName_mutasi = $tableName_mutasi;
    }

    public function pair($inParams)
    {
//        $this->load->helper("he_mass_table");
//        $configBalanceProtections = $this->config->item("accountBalanceProtections");
        $this->inParams = $inParams;
        arrPrint($inParams);
        if (sizeof($this->inParams) > 0) {
            $lCounter = 0;
            foreach ($this->inParams as $array_params) {
                $arrRekening = array();
                foreach ($array_params['loop'] as $key => $x) {

                    $type_payment = isset($array_params['static']['type_payment']) ? $array_params['static']['type_payment'] : 0;
                    $rejection = isset($array_params['static']['rejection']) ? $array_params['static']['rejection'] : 0;
                    $totalBayarGlobal = isset($array_params['static']['nilai_bayar']) ? $array_params['static']['nilai_bayar'] : 0;
//                    $totalBayarGlobal = isset($array_params['static']['sisa']) ? $array_params['static']['sisa'] : 0;
                    $normalizedTypeSave = isset($array_params['static']['normalizedTypeSave']) ? $array_params['static']['normalizedTypeSave'] : 0;
                    $insertID = isset($array_params['static']['transaksi_id']) ? $array_params['static']['transaksi_id'] : 0;
                    $tmpNomorNota = isset($array_params['static']['transaksi_no']) ? $array_params['static']['transaksi_no'] : 0;




                    if ($type_payment == "termin") {
                        if ($rejection == 0) {
                            cekHitam("totalBayarGlobal: $totalBayarGlobal");
                            $aftotalBayarGlobal = $totalBayarGlobal;
                            $thisSisa = $array_params['static']['sisa'] * 1;
                            $thisDebet = $array_params['static']['debet'] * 1;
                            $sisaGlobal = $totalBayarGlobal - $thisSisa;

                            // bukan reject/pembatalan
                            $afDebet = ($sisaGlobal * 1 > 0) ? $array_params['static']['debet'] + $thisSisa : ($sisaGlobal * 1 == 0 ? $array_params['static']['debet'] + $thisSisa : $array_params['static']['debet'] + $aftotalBayarGlobal);
                            cekMerah("Debet: $thisDebet");
                            cekMerah("afDebet: $thisDebet+$thisSisa = $afDebet");

                            $afSisa = ($sisaGlobal * 1 > 0) ? $thisSisa - $thisSisa : ($sisaGlobal * 1 == 0 ? $thisSisa - $thisSisa : $thisSisa - $aftotalBayarGlobal);
                            cekMerah("sisa: $thisSisa");
                            cekMerah("afSisa: $thisSisa - $thisSisa  = $afSisa");

                            $totalBayarGlobal = $sisaGlobal;
                            cekMerah("totalBayarGlobal: $aftotalBayarGlobal - $thisSisa = $totalBayarGlobal");
                            $_preValues = $this->cekPreValue(
                                $array_params['static']['cabang_id'],
                                $array_params['static']['no_spk'],
                                $array_params['static']['produk_paket_id'],
                                $array_params['static']['progress_id']
                            );

                            if ($aftotalBayarGlobal >= 0) {

                                $invoiced = $array_params['static']['invoiced'];
                                $inv_trx_id = $array_params['static']['inv_trx_id'];
                                $inv_dtime = $array_params['static']['inv_dtime'];
                                $mode = (count($_preValues) > 0) ? "update" : "skip";
                                $this->outParams[$lCounter]["cache"][$mode]["id"] = $_preValues["id"];
                                $this->outParams[$lCounter]["cache"][$mode]["debet"] = $afDebet;
                                $this->outParams[$lCounter]["cache"][$mode]["sisa"] = $afSisa;
                                $this->outParams[$lCounter]["cache"][$mode]["invoiced"] = $invoiced != "" ? $invoiced . "," . $tmpNomorNota : $tmpNomorNota;
                                $this->outParams[$lCounter]["cache"][$mode]["inv_trx_id"] = $inv_trx_id != "" ? $inv_trx_id . "," . $insertID : $insertID;
                                $this->outParams[$lCounter]["cache"][$mode]["inv_dtime"] = $inv_dtime != "" ? $inv_dtime . "," . date('Y-m-d H:i:s') : date('Y-m-d H:i:s');
                            }


                        }
                        else {
                            cekHitam("totalBayarGlobal: $totalBayarGlobal");
                            $totalBayarGlobal = $totalBayarGlobal *-1;
                            $aftotalBayarGlobal = $totalBayarGlobal;
                            $thisSisa = $array_params['static']['sisa'] * 1;
                            $thisDebet = $array_params['static']['debet'] * 1;
                            $sisaGlobal = $totalBayarGlobal - $thisSisa;

                            $_preValues = $this->cekPreValue(
                                $array_params['static']['cabang_id'],
                                $array_params['static']['no_spk'],
                                $array_params['static']['produk_paket_id'],
                                $array_params['static']['progress_id']
                            );

                            // reject/pembatalan
//                            $afDebet = ($sisaGlobal * 1 > 0) ? ($array_params['static']['debet'] + $thisSisa) : ($sisaGlobal * 1 == 0 ? $array_params['static']['debet'] + $thisSisa : $array_params['static']['debet'] + $aftotalBayarGlobal);
                            $afDebet = $_preValues["debet"] + $totalBayarGlobal;
                            cekMerah("Debet: $thisDebet");
                            cekMerah("afDebet: $thisDebet+$thisSisa = $afDebet");

//                            $afSisa = ($sisaGlobal * 1 > 0) ? $thisSisa - $thisSisa : ($sisaGlobal * 1 == 0 ? $thisSisa - $thisSisa : $thisSisa - $aftotalBayarGlobal);
                            $afSisa = $_preValues["sisa"] - $totalBayarGlobal;
                            cekMerah("sisa: $thisSisa");
                            cekMerah("afSisa: $thisSisa - $thisSisa  = $afSisa");

                            $totalBayarGlobal = $sisaGlobal;
                            cekMerah("totalBayarGlobal: $aftotalBayarGlobal - $thisSisa = $totalBayarGlobal");

                            arrPrintHitam($_preValues);
//                            if ($aftotalBayarGlobal >= 0) {

                                $invoiced = $array_params['static']['invoiced'];
                                $inv_trx_id = $array_params['static']['inv_trx_id'];
                                $inv_dtime = $array_params['static']['inv_dtime'];
                                $mode = (count($_preValues) > 0) ? "update" : "skip";
                                $this->outParams[$lCounter]["cache"][$mode]["id"] = $_preValues["id"];
                                $this->outParams[$lCounter]["cache"][$mode]["debet"] = $afDebet;
                                $this->outParams[$lCounter]["cache"][$mode]["sisa"] = $afSisa;
                                $this->outParams[$lCounter]["cache"][$mode]["invoiced"] = ($invoiced != "") ? $invoiced . "," . $tmpNomorNota : $tmpNomorNota;
                                $this->outParams[$lCounter]["cache"][$mode]["inv_trx_id"] = ($inv_trx_id != "") ? $inv_trx_id . "," . $insertID : $insertID;
                                $this->outParams[$lCounter]["cache"][$mode]["inv_dtime"] = ($inv_dtime != "") ? $inv_dtime . "," . date('Y-m-d H:i:s') : date('Y-m-d H:i:s');
//                            }
                        }
                        arrPrintCyan($this->outParams[$lCounter]);
                        //region Exec()
                        $pakai_exec = 1;
                        if ($pakai_exec == 1) {
                            $tableName = $this->tableName;
                            $tableName_mutasi = $this->tableName_mutasi;
                            $insertIDs = array();
                            if (sizeof($this->outParams) > 0) {
                                foreach ($this->outParams as $lCounter => $pSpec) {
                                    foreach ($pSpec as $mode => $pSpec_mode) {
                                        switch ($mode) {
                                            case "cache":
                                                foreach ($pSpec_mode as $sub_mode => $pSpec_mode_data) {
                                                    $id = $pSpec_mode_data["id"];
                                                    unset($pSpec_mode_data["id"]);

                                                    switch ($sub_mode) {
//                                                    case "insert":
//                                                        $this->db->insert($tableName, $pSpec_mode_data);
//                                                        $insertIDs[] = $this->db->insert_id();
//                                                        cekUngu("$sub_mode :: " . $this->db->last_query());
//                                                        break;
                                                        case "update":
                                                            $this->db->where('id', $id);
                                                            $insertIDs[] = $this->db->update($tableName, $pSpec_mode_data);
                                                            cekOrange("$sub_mode :: " . $this->db->last_query());
                                                            break;
                                                    }
                                                }
                                                break;
//                                        case "mutasi":
//
//                                            unset($pSpec_mode["tabel"]);
//
//                                            $this->db->insert($tableName_mutasi, $pSpec_mode);
//                                            $insertIDs[] = $this->db->insert_id();
//                                            cekHijau("$mode :: " . $this->db->last_query());
//                                            break;
                                        }
                                    }
                                }
                                $this->outParams = array();

                                if (sizeof($insertIDs) == 0) {
                                    cekMerah("::: PERIODE : $periode :::");
                                    return false;
                                }
                            }
                            else {
                                cekMerah("::: PERIODE : $periode :::");
                                return false;
                            }
                        }
                        //endregion
                    }

                }
            }

        }

//        mati_disini(__LINE__);
        return true;
    }

    private function cekPreValue($cabang_id, $no_spk, $produk_paket_id, $progress_id)
    {

        $this->addFilter("cabang_id='$cabang_id'");
        $this->addFilter("no_spk='$no_spk'");
        $this->addFilter("produk_paket_id='$produk_paket_id'");
        $this->addFilter("progress_id='$progress_id'");
        $result = array();
        $localFilters = array();
        if (sizeof($this->filters) > 0) {
            foreach ($this->filters as $f) {
                $tmpArr = explode("=", $f);
                $localFilters[$tmpArr[0]] = trim($tmpArr[1], "'");
            }
        }
        $query = $this->db->select()
            ->from($this->tableName)
            ->where($localFilters)
            ->limit(1)
            ->get_compiled_select();
        $tmp = $this->db->query("{$query} FOR UPDATE")->result();
        if (sizeof($tmp) > 0) {
            // bila count($tmp) > 0, maka ambil saldo periode sendiri, dan mode update
            foreach ($tmp as $row) {
                $result = array(
                    "id" => $row->id,
                    "invoiced" => $row->invoiced,
                    "inv_trx_id" => $row->inv_trx_id,
                    "debet" => $row->debet,
                    "sisa" => $row->sisa,
                );
            }
        }
        else {
            $result = array();
        }
        //  endregion mengambil saldo dari rek_cache

        return $result;
    }

    public function addFilter($f)
    {
        $this->filters[] = $f;
    }

    public function exec()
    {

        return true;
    }

    public function getKoloms()
    {
        return $this->koloms;
    }

}

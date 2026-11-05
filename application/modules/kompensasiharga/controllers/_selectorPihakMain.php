<?php

require_once "Modul_Controller.php";

class _selectorPihakMain extends Modul_Controller
{

    public function __construct()
    {
        parent::__construct();
//        $this->jenisTr = $this->uri->segment(4);
        $cCode = "_TR_" . $this->jenisTr;

    }

    public function selectPihak()
    {

        $jenisTr = $this->uri->segment(4);

        $cCode = "_TR_" . $jenisTr;
        $mdlName = $this->uri->segment(5);
        $fields = $this->configUi[$jenisTr]['selectorFields'];
        $pihakFilters = isset($this->configUi[$jenisTr]['pihakMainFilters']) ? $this->configUi[$jenisTr]['pihakMainFilters'] : array();
        $pihakAddStaticEntry = isset($this->configUi[$jenisTr]['pihakAddStaticEntry']) ? $this->configUi[$jenisTr]['pihakAddStaticEntry'] : array();
        $selectorFields = isset($this->configUi[$jenisTr]['pihakMainViewedFields']) ? $this->configUi[$jenisTr]['pihakMainViewedFields'] : array();
        $configPair = isset($this->configUi[$jenisTr]["pihakMainPair"]) ? $this->configUi[$jenisTr]["pihakMainPair"] : array();
        $preLocker = isset($this->configUi[$jenisTr]["lockerCheck"]) ? $this->configUi[$jenisTr]["lockerCheck"] : array();
        $key = isset($_GET['search']) ? $_GET['search'] : "";

        $selectorKeySrc_id = isset($this->configUi[$jenisTr]["pihakNameMainDiskonIdSelector"]) ? $this->configUi[$jenisTr]["pihakNameMainDiskonIdSelector"] : "id";
        $pihakNameMainDiskonConfirm = isset($this->configUi[$jenisTr]["pihakNameMainDiskonConfirm"]) ? $this->configUi[$jenisTr]["pihakNameMainDiskonConfirm"] : false;

        $supplier_id = isset($_SESSION[$cCode]["main"]["pihakName"]) ? $_SESSION[$cCode]["main"]["pihakName"] : "";
        $selctorKlaimDateMin = "";
        $arrCekKolom = array(
            "nomer" => "pihakMainNota",
            "name" => "pihakNameMain",
            "nama" => "pihakNameMain",
            "per_supplier_diskon_alias" => "pihakNameMainDiskon",
        );
        $selectColumn = "nama";
        foreach ($arrCekKolom as $keyCek => $valueCek) {
            if (isset($this->configUi[$jenisTr][$valueCek]) && $this->configUi[$jenisTr][$valueCek] == true) {
                $selectColumn = $keyCek;
                break;
            }
        }


        $items = array();

        $this->load->model("Mdls/" . $mdlName);
        $o = new $mdlName();
        if (sizeof($pihakFilters) > 0) {
            foreach ($pihakFilters as $f) {
                $f_ex = explode("=", $f);
                if (!isset($f_ex[1])) {
                    $f_ey = explode(">", $f_ex[0]);
                    if (substr($f_ey[1], 0, 1) == ".") {
                        $o->addFilter($f_ey[0] . ">'" . ltrim($f_ey[1], ".") . "'");
                    }
                    else {
//                        $o->addFilter($f_ey[0] . ">'" . $this->session->login[$f_ey[1]] . "'");
                        $o->addFilter($f_ey[0] . ">'" . $_SESSION[$cCode]['main'][$f_ey[1]] . "'");
                    }
                }
                else {
                    if (substr($f_ex[1], 0, 1) == ".") {
                        $o->addFilter($f_ex[0] . "='" . ltrim($f_ex[1], ".") . "'");
                    }
                    else {
//                        $o->addFilter($f_ex[0] . "='" . $this->session->login[$f_ex[1]] . "'");
                        $o->addFilter($f_ex[0] . "='" . $_SESSION[$cCode]['main'][$f_ex[1]] . "'");
                    }
                }
            }
        }
        $this->db->limit(20);
        $tmpO = $o->lookupByKeyword($key)->result();
//        cekLime($this->db->last_query());

        $pairedStock = array();
        if (sizeof($tmpO) > 0) {
            if (sizeof($configPair) > 0) {
//                cekBiru(__LINE__);
                $tmpIds = "(";
                foreach ($tmpO as $temp2) {
                    $tmpIds .= "'" . $temp2->$selectorKeySrc_id . "',";
                }
                $tmpIds = rtrim($tmpIds, ",");
                $tmpIds .= ")";
                $mdlName = $configPair['MdlName'];
                $filters = $configPair['filter'];
                $this->load->model("Mdls/" . $mdlName);
                $p = new $mdlName();
                $p->addfilter("produk_id in $tmpIds");
//                cekHere($tmpIds);
                if (sizeof($filters) > 0) {
                    foreach ($filters as $s) {
                        $s_ex = explode("=", $s);
//                cekLime($f_ex);
                        if (!isset($s_ex[1])) {
                            $s_ey = explode(">", $s_ex[0]);
                            if (substr($s_ey[1], 0, 1) == ".") {
                                $p->addFilter($s_ey[0] . ">'" . ltrim($s_ey[1], ".") . "'");
                            }
                            else {
//                        $o->addFilter($f_ey[0] . ">'" . $this->session->login[$f_ey[1]] . "'");
                                $p->addFilter($s_ey[0] . ">'" . $_SESSION['cCode']['main'][$s_ey[1]] . "'");
                            }
                        }
                        else {
                            if (substr($s_ex[1], 0, 1) == ".") {
                                $p->addFilter($s_ex[0] . "='" . ltrim($s_ex[1], ".") . "'");
                            }
                            else {
//                        $o->addFilter($f_ex[0] . "='" . $this->session->login[$f_ex[1]] . "'");
                                $p->addFilter($s_ex[0] . "='" . $_SESSION[$cCode]['main'][$s_ex[1]] . "'");
                            }
                        }
                    }
                }
                $tmpPairData = $p->lookupAll()->result();
                cekBiru($this->db->last_query());
                if (sizeof($tmpPairData) > 0) {
                    foreach ($tmpPairData as $tmpPairData0) {
//                        $pairedStock[$tmpPairData0->produk_id] = $tmpPairData0->jumlah;
                        $pairedStock[$tmpPairData0->produk_id][] = (array)$tmpPairData0;
                    }
                }

            }
        }


        $processor = MODUL_PATH . $this->configUi[$jenisTr]['pihakMainProcessor'] . "/" . "$jenisTr/" . $this->configUi[$jenisTr]['pihakModelMain'];
        if (sizeof($tmpO) > 0) {
            if (sizeof($configPair) > 0) {
                cekHitam("MASUK ATAS...");
                foreach ($tmpO as $row) {
//                    arrPrint($row);
//                    if (isset($pairedStock[$row->$selectorKeySrc_id])) {
                    if (sizeof($selectorFields) > 0) {
//                    cekLime("masuk");
                        $nCtr = 0;
                        $tmpName = "";
                        $tmpNameOrig = "";
                        if (isset($pairedStock[$row->id])) {

                        }
                        foreach ($selectorFields as $f) {
                            $nCtr++;
                            $align = $nCtr == 1 ? "text-left" : "text-right";
                            $fSize = $nCtr == 1 ? "font-size:1em" : "font-size:0.9em";
                            $color = isset($colors[$nCtr]) ? $colors[$nCtr] : "#000000";

                            if (is_numeric($row->$f)) {
                                $tmpName .= "<span style='$fSize ;margin:0px 2px 0px 2px;color:$color;' class='no-padding no-border $align'>" . number_format($row->$f) . "*</span>";
                                $tmpNameOrig .= number_format($row->$f);
                            }
                            else {
                                $newFields = in_array($f, arrAvailFields()) ? formatNota($f, $row->$f) : $row->$f;
                                $tmpName .= "<span style='$fSize ;margin:0px 2px 0px 2px;color:$color;' class='no-padding no-border $align'>" . $newFields . "</span>";
                                $tmpNameOrig .= $newFields;
                            }
                            if (count($pairedStock[$row->$selectorKeySrc_id]) > 0) {
                                $sumAvailSaldo = 0;
                                $sumKlaim = 0;
                                foreach ($pairedStock[$row->$selectorKeySrc_id] as $txData) {
//                                        arrPrint($txData);
                                    $sumAvailSaldo += $txData["nilai"];
                                    $sumKlaim += $txData["nilai_klaim"];
                                }
                                $tmpName .= "<span style='$fSize ;margin:0px 2px 0px 2px;color:$color;' class='no-padding no-border $align'>@" . number_format($sumAvailSaldo) . "</span>";
                                $tmpNameOrig .= " @" . number_format($sumAvailSaldo) . " ";
                            }
                        }
                        $tmpName = rtrim($tmpName, "| ");


                    }
                    else {
                        $tmpName = isset($row->$selectColumn) ? $row->$selectColumn : "";

                        if (isset($row->name)) {
                            $tmpName = isset($row->$selectColumn) ? $row->$selectColumn : $row->name;
                        }
                    }
                    if (strlen($tmpName) > 1) {
                        $items[] = array(
                            "id" => $row->id,
                            "label" => $tmpName,
                            "label_orig" => $tmpNameOrig,
                            "target" => $processor,
                            "confirm" => $pihakNameMainDiskonConfirm,
                            "supplier_nama" => $supplier_id,
                        );
                    }
//                    }
                }
            }
            else {
                cekHitam("MASUK BAWAH...");
                foreach ($tmpO as $row) {
                    if (sizeof($selectorFields) > 0) {
//                        cekLime("masuk 123");
                        $nCtr = 0;
                        $tmpName = "";
                        $tmpNameOrig = "";
                        if (isset($pairedStock[$row->id])) {

                        }
                        foreach ($selectorFields as $f) {
                            $nCtr++;
                            $align = $nCtr == 1 ? "text-left" : "text-right";
                            $fSize = $nCtr == 1 ? "font-size:1em" : "font-size:0.9em";
                            $color = isset($colors[$nCtr]) ? $colors[$nCtr] : "#000000";

                            if (is_numeric($row->$f)) {
                                $tmpName .= "<span style='$fSize ;margin:0px 2px 0px 2px;color:$color;' class='no-padding no-border $align'>" . number_format($row->$f) . "</span>";
                                $tmpNameOrig .= " " . number_format($row->$f) . " ";
                            }
                            else {
                                $newFields = in_array($f, arrAvailFields()) ? formatNota($f, $row->$f) : $row->$f;
                                $tmpName .= "<span style='$fSize ;margin:0px 2px 0px 2px;color:$color;' class='no-padding no-border $align'>" . $newFields . "</span>";
                                $tmpNameOrig .= " " . $newFields . " ";
                            }
                        }
                        $tmpName = rtrim($tmpName, "| ");
                    }
                    else {
                        $tmpName = isset($row->$selectColumn) ? $row->$selectColumn : "";
                        $tmpNameOrig = isset($row->$selectColumn) ? $row->$selectColumn : "";
                        if (isset($row->label)) {
                            $tmpName = isset($row->label) ? $row->label : $row->name;
                            $tmpNameOrig = isset($row->label) ? $row->label : $row->name;
                        }
                        elseif (isset($row->name)) {
                            $tmpName = isset($row->$selectColumn) ? $row->$selectColumn : $row->name;
                            $tmpNameOrig = isset($row->$selectColumn) ? $row->$selectColumn : $row->name;
                        }
                    }
                    if (strlen($tmpName) > 1) {
                        $data_id = isset($this->configUi[$jenisTr]["pihakNameMainDiskonIdSelector"]) ? $this->configUi[$jenisTr]["pihakNameMainDiskonIdSelector"] : "id";
                        $items[] = array(
                            "id" => $row->$data_id,
                            "label" => $tmpName,
                            "label_orig" => $tmpNameOrig,
                            "target" => $processor,
                            "confirm" => $pihakNameMainDiskonConfirm,
                            "supplier_nama" => $supplier_id,
                        );
                    }
                }
            }

        }

        if (sizeof($pihakAddStaticEntry) > 0) {
            foreach ($pihakAddStaticEntry as $key => $val) {
                $addStaticEntry[$key] = getDefaultWarehouseID($this->session->login['cabang_id'])[$val];
                $addStaticEntry["target"] = $processor;

            }

            $items[] = $addStaticEntry;
            foreach ($items as $iCtr => $iSpec) {
                if ($this->session->login['gudang_id'] == $iSpec['id']) {
                    unset($items[$iCtr]);
                }
            }
        }


        $data = array(
            "mode" => "view",
            "items" => $items,
        );


        $this->load->view("_selectorPihak", $data);
    }


    public function selectPihakNota()
    {

        $jenisTr = $this->uri->segment(4);

        $cCode = "_TR_" . $jenisTr;
        $mdlName = $this->uri->segment(5);
        $fields = $this->configUi[$jenisTr]['selectorFields'];
        $pihakFilters = isset($this->configUi[$jenisTr]['pihakMainFilters']) ? $this->configUi[$jenisTr]['pihakMainFilters'] : array();
        $pihakAddStaticEntry = isset($this->configUi[$jenisTr]['pihakAddStaticEntry']) ? $this->configUi[$jenisTr]['pihakAddStaticEntry'] : array();
        $selectorFields = isset($this->configUi[$jenisTr]['pihakMainViewedFields']) ? $this->configUi[$jenisTr]['pihakMainViewedFields'] : array();
        $configPair = isset($this->configUi[$jenisTr]["pihakMainPair"]) ? $this->configUi[$jenisTr]["pihakMainPair"] : array();
        $pairRegistries = isset($this->configUi[$jenisTr]["pihakMainPairRegistries"]) ? $this->configUi[$jenisTr]["pihakMainPairRegistries"] : array();


        $key = isset($_GET['search']) ? $_GET['search'] : "";

        $arrCekKolom = array(
            "nomer" => "pihakMainNota",
            "name" => "pihakNameMain",
            "nama" => "pihakNameMain",
        );
        $selectColumn = "nama";
        foreach ($arrCekKolom as $keyCek => $valueCek) {
            if (isset($this->configUi[$jenisTr][$valueCek]) && $this->configUi[$jenisTr][$valueCek] == true) {
                $selectColumn = $keyCek;
                break;
            }
        }


        $items = array();

        $this->load->model("Mdls/" . $mdlName);
        $o = new $mdlName();
        if (sizeof($pihakFilters) > 0) {
            foreach ($pihakFilters as $f) {
                $f_ex = explode("=", $f);
                if (!isset($f_ex[1])) {
                    $f_ey = explode(">", $f_ex[0]);
                    if (substr($f_ey[1], 0, 1) == ".") {
                        $o->addFilter($f_ey[0] . ">'" . ltrim($f_ey[1], ".") . "'");
                    }
                    else {
//                        $o->addFilter($f_ey[0] . ">'" . $this->session->login[$f_ey[1]] . "'");
                        $o->addFilter($f_ey[0] . ">'" . $_SESSION['cCode']['main'][$f_ey[1]] . "'");
                    }
                }
                else {
                    if (substr($f_ex[1], 0, 1) == ".") {
                        $o->addFilter($f_ex[0] . "='" . ltrim($f_ex[1], ".") . "'");
                    }
                    else {
//                        $o->addFilter($f_ex[0] . "='" . $this->session->login[$f_ex[1]] . "'");
                        $o->addFilter($f_ex[0] . "='" . $_SESSION[$cCode]['main'][$f_ex[1]] . "'");
                    }
                }
            }
        }
        $this->db->limit(20);
        $tmpO = $o->lookupByKeyword($key)->result();
//        arrPrint($tmpO);


        $pairedStock = array();
        if (sizeof($tmpO) > 0) {
            if (isset($pairRegistries['enabled']) && ($pairRegistries['enabled'] == true)) {
                $regIDs = array();
                foreach ($tmpO as $spec) {
                    $index_reg = blobDecode($spec->indexing_registry);
                    $regIDs[] = $index_reg[$pairRegistries['param']];
                }

                $this->load->model("MdlTransaksi");
                $rg = New MdlTransaksi();
                $rg->setFilters(array());
                $rg->addFilter("id in ('" . implode("','", $regIDs) . "')");
                $rgTmp = $rg->lookupRegistries()->result();

                $regDenied = array();
                foreach ($rgTmp as $rgSpec) {
                    $regValue = blobDecode($rgSpec->values);

                    if (isset($pairRegistries['fieldsDenied'])) {
                        foreach ($pairRegistries['fieldsDenied'] as $key => $spec) {
                            if (array_key_exists($key, $regValue)) {

                                if (in_array($regValue[$key], $spec)) {
                                    // bila payment method adalah cash in advance maka masuk list untuk unset dari array...
                                    $regDenied[] = $rgSpec->transaksi_id;
                                }
                            }
                        }
                    }
                }
            }


            if (sizeof($configPair) > 0) {
                $tmpIds = "(";
                foreach ($tmpO as $temp2) {
                    $tmpIds .= "'$temp2->id',";
                }
                $tmpIds = rtrim($tmpIds, ",");
                $tmpIds .= ")";
                $mdlName = $configPair['MdlName'];
                $filters = $configPair['filter'];
                $this->load->model("Mdls/" . $mdlName);
                $p = new $mdlName();
                $p->addfilter("produk_id in $tmpIds");

                if (sizeof($filters) > 0) {
                    makeFilter($filters, $_SESSION[$cCode]['main'], $p);
                }
                $tmpPairData = $p->lookupAll()->result();

                if (sizeof($tmpPairData) > 0) {
                    foreach ($tmpPairData as $tmpPairData0) {
//                        $pairedStock[$tmpPairData0->produk_id] = $tmpPairData0->jumlah;
                        $pairedStock[$tmpPairData0->produk_id] = $tmpPairData0->nilai;
                    }
                }
            }
        }

        // unset list cash in advance milik $tmpO
        if (sizeof($tmpO) > 0) {
            foreach ($tmpO as $ctr => $spec) {
                if (in_array($spec->id, $regDenied)) {
                    unset($tmpO[$ctr]);
                }
            }
        }

        $processor = MODUL_PATH . $this->configUi[$jenisTr]['pihakMainProcessor'] . "/" . "$jenisTr/" . $this->configUi[$jenisTr]['pihakModelMain'];
        if (sizeof($tmpO) > 0) {

            if (sizeof($configPair) > 0) {
                foreach ($tmpO as $row) {

                    if (isset($pairedStock[$row->id]) && ($pairedStock[$row->id] > 0.1)) {
                        if (sizeof($selectorFields) > 0) {

                            $nCtr = 0;
                            $tmpName = "";
//                            if (isset($pairedStock[$row->id])) {
//
//                            }
//
                            foreach ($selectorFields as $f) {
                                $nCtr++;
                                $align = $nCtr == 1 ? "text-left" : "text-right";
                                $fSize = $nCtr == 1 ? "font-size:1em" : "font-size:0.9em";
                                $color = isset($colors[$nCtr]) ? $colors[$nCtr] : "#000000";

                                if (is_numeric($row->$f)) {
                                    $tmpName .= "<span style='$fSize ;margin:0px 2px 0px 2px;color:$color;' class='no-padding no-border $align'>" . number_format($row->$f) . "</span>";
                                }
                                else {
                                    $newFields = in_array($f, arrAvailFields()) ? formatNota($f, $row->$f) : $row->$f;
                                    $tmpName .= "<span style='$fSize ;margin:0px 2px 0px 2px;color:$color;' class='no-padding no-border $align'>" . $newFields . "</span>";
                                }
                            }
                            $tmpName = rtrim($tmpName, "| ");


                        }
                        else {
                            $tmpName = isset($row->$selectColumn) ? $row->$selectColumn : "";

                            if (isset($row->name)) {
                                $tmpName = isset($row->$selectColumn) ? $row->$selectColumn : $row->name;
                            }
                        }
                        if (strlen($tmpName) > 1) {
                            $items[] = array(
                                "id" => $row->id,
                                "label" => $tmpName,
//                        "label" => $newTmpName,
                                "target" => $processor,
                            );
                        }
                    }

                }
            }
            else {
                foreach ($tmpO as $row) {
                    if (sizeof($selectorFields) > 0) {
                        $nCtr = 0;
                        $tmpName = "";
                        if (isset($pairedStock[$row->id])) {

                        }
                        foreach ($selectorFields as $f) {
                            $nCtr++;
                            $align = $nCtr == 1 ? "text-left" : "text-right";
                            $fSize = $nCtr == 1 ? "font-size:1em" : "font-size:0.9em";
                            $color = isset($colors[$nCtr]) ? $colors[$nCtr] : "#000000";

                            if (is_numeric($row->$f)) {
                                $tmpName .= "<span style='$fSize ;margin:0px 2px 0px 2px;color:$color;' class='no-padding no-border $align'>" . number_format($row->$f) . "</span>";
                            }
                            else {
                                $newFields = in_array($f, arrAvailFields()) ? formatNota($f, $row->$f) : $row->$f;
                                $tmpName .= "<span style='$fSize ;margin:0px 2px 0px 2px;color:$color;' class='no-padding no-border $align'>" . $newFields . "</span>";
                            }
                        }
                        $tmpName = rtrim($tmpName, "| ");
                    }
                    else {
                        $tmpName = isset($row->$selectColumn) ? $row->$selectColumn : "";

                        if (isset($row->name)) {
                            $tmpName = isset($row->$selectColumn) ? $row->$selectColumn : $row->name;
                        }
                    }

                    if (strlen($tmpName) > 1) {
//                    if (in_array($selectColumn, arrAvailFields())) {
//
//                        $newTmpName = formatNota($selectColumn, $tmpName);
//                    }
//                    else {
//                        $newTmpName = $tmpName;
//                    }
                        $items[] = array(
                            "id" => $row->id,
                            "label" => $tmpName,
//                        "label" => $newTmpName,
                            "target" => $processor,
                        );
                    }
                }
            }

        }

        if (sizeof($pihakAddStaticEntry) > 0) {
            foreach ($pihakAddStaticEntry as $key => $val) {
                $addStaticEntry[$key] = getDefaultWarehouseID($this->session->login['cabang_id'])[$val];
                $addStaticEntry["target"] = $processor;

            }

            $items[] = $addStaticEntry;
            foreach ($items as $iCtr => $iSpec) {
                if ($this->session->login['gudang_id'] == $iSpec['id']) {
                    unset($items[$iCtr]);
                }
            }
        }

        $data = array(
            "mode" => "view",
            "items" => $items,
        );


        $this->load->view("_selectorPihak", $data);
    }

    public function selectPihak2()
    {

        $jenisTr = $this->uri->segment(4);

        $cCode = "_TR_" . $jenisTr;
        $mdlName = $this->uri->segment(5);
        $fields = $this->configUi[$jenisTr]['selectorFields'];
        $pihakFilters = isset($this->configUi[$jenisTr]['pihakMainFilters']) ? $this->configUi[$jenisTr]['pihakMainFilters'] : array();
        $pihakAddStaticEntry = isset($this->configUi[$jenisTr]['pihakAddStaticEntry']) ? $this->configUi[$jenisTr]['pihakAddStaticEntry'] : array();
        $selectorFields = isset($this->configUi[$jenisTr]['pihakMainViewedFields']) ? $this->configUi[$jenisTr]['pihakMainViewedFields'] : array();
        $configPair = isset($this->configUi[$jenisTr]["pihakMainPair"]) ? $this->configUi[$jenisTr]["pihakMainPair"] : array();
        $preLocker = isset($this->configUi[$jenisTr]["lockerCheck"]) ? $this->configUi[$jenisTr]["lockerCheck"] : array();
        $key = isset($_GET['search']) ? $_GET['search'] : "";

        $selectorKeySrc_id = isset($this->configUi[$jenisTr]["pihakNameMainDiskonIdSelector"]) ? $this->configUi[$jenisTr]["pihakNameMainDiskonIdSelector"] : "id";
        $pihakNameMainDiskonConfirm = isset($this->configUi[$jenisTr]["pihakNameMainDiskonConfirm"]) ? $this->configUi[$jenisTr]["pihakNameMainDiskonConfirm"] : false;

        $supplier_id = isset($_SESSION[$cCode]["main"]["pihakName"]) ? $_SESSION[$cCode]["main"]["pihakName"] : "";
        $selctorKlaimDateMin = "";
        $arrCekKolom = array(
            "nomer" => "pihakMainNota",
            "name" => "pihakNameMain",
            "nama" => "pihakNameMain",
            "per_supplier_diskon_alias" => "pihakNameMainDiskon",
        );
        $selectColumn = "nama";
        foreach ($arrCekKolom as $keyCek => $valueCek) {
            if (isset($this->configUi[$jenisTr][$valueCek]) && $this->configUi[$jenisTr][$valueCek] == true) {
                $selectColumn = $keyCek;
                break;
            }
        }


        $items = array();

        $isSearching = (strlen(trim($key)) > 0);
        $sessionSupplierId = isset($_SESSION[$cCode]["main"]["pihakID"]) ? $_SESSION[$cCode]["main"]["pihakID"] : "";
        $supplier_nama_sesi = isset($_SESSION[$cCode]["main"]["pihakName"]) ? $_SESSION[$cCode]["main"]["pihakName"] : "";

        $this->load->model("Mdls/" . $mdlName);
        $o = new $mdlName();
        if (sizeof($pihakFilters) > 0) {
            foreach ($pihakFilters as $f) {
                $f_ex = explode("=", $f);
                $prefix = ($mdlName == "MdlLockerStockDiskonVendor") ? "stock_locker_diskon." : "";
                if (!isset($f_ex[1])) {
                    $f_ey = explode(">", $f_ex[0]);
                    // Saat user sedang mencari (isSearching), abaikan filter nilai>.0 agar transaksi saldo 0 tetap ditemukan untuk diberi informasi penyebabnya
                    if ($mdlName == "MdlLockerStockDiskonVendor" && $f_ey[0] == "nilai" && $isSearching) {
                        continue;
                    }
                    if (substr($f_ey[1], 0, 1) == ".") {
                        $o->addFilter($prefix . $f_ey[0] . ">'" . ltrim($f_ey[1], ".") . "'");
                    }
                    else {
                        $valSess = isset($_SESSION[$cCode]['main'][$f_ey[1]]) ? $_SESSION[$cCode]['main'][$f_ey[1]] : "";
                        if (!empty($valSess)) {
                            $o->addFilter($prefix . $f_ey[0] . ">'" . $valSess . "'");
                        }
                    }
                }
                else {
                    // Saat user sedang mencari (isSearching), abaikan filter supplier_id agar nota yang dicari tetap dapat ditemukan dan dijelaskan statusnya
                    if ($mdlName == "MdlLockerStockDiskonVendor" && $f_ex[0] == "supplier_id" && $isSearching) {
                        continue;
                    }
                    if (substr($f_ex[1], 0, 1) == ".") {
                        $o->addFilter($prefix . $f_ex[0] . "='" . ltrim($f_ex[1], ".") . "'");
                    }
                    else {
                        $valSess = isset($_SESSION[$cCode]['main'][$f_ex[1]]) ? $_SESSION[$cCode]['main'][$f_ex[1]] : "";
                        if (!empty($valSess)) {
                            $o->addFilter($prefix . $f_ex[0] . "='" . $valSess . "'");
                        }
                    }
                }
            }
        }
// START OF COMPLETE REPEATED LOGIC
        if ($mdlName == "MdlLockerStockDiskonVendor") {
            $this->db->join("transaksi", "transaksi.nomer = stock_locker_diskon.nomer AND (transaksi.jenis = '467' OR transaksi.jenis LIKE '467%')", "left");
            $this->db->join("per_supplier", "per_supplier.id = stock_locker_diskon.supplier_id", "left");
            if (!$isSearching) {
                $this->db->where("(transaksi.trash_4 = 0 OR transaksi.trash_4 IS NULL)");
                $this->db->where("(transaksi.trash = 0 OR transaksi.trash IS NULL)");
                $this->db->where("transaksi.status", 1);
            }
            $this->db->select("transaksi.trash_4, transaksi.trash, transaksi.status, transaksi.link_id, transaksi.cancel_transaksi_nomer, 
                transaksi.cancel_transaksi_jenis, transaksi.cancel_id, transaksi.cancel_dtime, transaksi.reference_nomer,
                per_supplier.nama as supplier_nama,
                stock_locker_diskon.*, 
                DATE(stock_locker_diskon.fulldate) as tgl_transaksi,
                COUNT(stock_locker_diskon.id) as total_item_diskon,
                SUM(CASE WHEN stock_locker_diskon.nilai_unit > 0 THEN stock_locker_diskon.nilai_unit ELSE stock_locker_diskon.nilai END * IF(stock_locker_diskon.jumlah < 0, -1, 1)) as total_nilai_diskon,
                SUM(stock_locker_diskon.nilai_diklaim * IF(stock_locker_diskon.jumlah < 0, -1, 1)) as total_nilai_diklaim,
                SUM(stock_locker_diskon.nilai * IF(stock_locker_diskon.jumlah < 0, -1, 1)) as sisa_nilai_diskon,
                SUM(CASE WHEN stock_locker_diskon.produk_id = 7 OR stock_locker_diskon.nama = 'free_produk' THEN (stock_locker_diskon.jumlah) ELSE 0 END) as count_free,
                SUM(CASE WHEN stock_locker_diskon.produk_id != 7 AND stock_locker_diskon.nama != 'free_produk' THEN (stock_locker_diskon.jumlah) ELSE 0 END) as count_reguler,
                SUM(CASE WHEN stock_locker_diskon.produk_id = 7 OR stock_locker_diskon.nama = 'free_produk' THEN (stock_locker_diskon.nilai * IF(stock_locker_diskon.jumlah < 0, -1, 1)) ELSE 0 END) as sisa_free,
                SUM(CASE WHEN stock_locker_diskon.produk_id != 7 AND stock_locker_diskon.nama != 'free_produk' THEN (stock_locker_diskon.nilai * IF(stock_locker_diskon.jumlah < 0, -1, 1)) ELSE 0 END) as sisa_reguler", FALSE);
            if (!$isSearching) {
                $this->db->having("sisa_nilai_diskon >= 1");
            }
        }
        if ($mdlName == "MdlLockerStockDiskonVendor") {
            $this->db->order_by("transaksi.trash_4", "ASC");
            $this->db->order_by("stock_locker_diskon.id", "desc");
        } else {
            $this->db->order_by("id", "desc");
        }
        $this->db->limit(20);

        if ($mdlName == "MdlLockerStockDiskonVendor") {
            $this->db->group_by("stock_locker_diskon.nomer");
        } else {
            $this->db->group_by("nomer");
        }
// END OF COMPLETE REPEATED LOGIC
        $tmpO = $o->lookupByKeyword($key)->result();
        cekLime($this->db->last_query());

        $pairedStock = array();
        if (sizeof($tmpO) > 0) {
            if (sizeof($configPair) > 0) {
                $tmpIds = "(";
                foreach ($tmpO as $temp2) {
                    $tmpIds .= "'" . $temp2->$selectorKeySrc_id . "',";
                }
                $tmpIds = rtrim($tmpIds, ",");
                $tmpIds .= ")";
                $mdlName = $configPair['MdlName'];
                $filters = $configPair['filter'];
                $this->load->model("Mdls/" . $mdlName);
                $p = new $mdlName();
                $p->addfilter("produk_id in $tmpIds");
                if (sizeof($filters) > 0) {
                    foreach ($filters as $s) {
                        $s_ex = explode("=", $s);
//                cekLime($f_ex);
                        if (!isset($s_ex[1])) {
                            $s_ey = explode(">", $s_ex[0]);
                            if (substr($s_ey[1], 0, 1) == ".") {
                                $p->addFilter($s_ey[0] . ">'" . ltrim($s_ey[1], ".") . "'");
                            }
                            else {
//                        $o->addFilter($f_ey[0] . ">'" . $this->session->login[$f_ey[1]] . "'");
                                $p->addFilter($s_ey[0] . ">'" . $_SESSION['cCode']['main'][$s_ey[1]] . "'");
                            }
                        }
                        else {
                            if (substr($s_ex[1], 0, 1) == ".") {
                                $p->addFilter($s_ex[0] . "='" . ltrim($s_ex[1], ".") . "'");
                            }
                            else {
//                        $o->addFilter($f_ex[0] . "='" . $this->session->login[$f_ex[1]] . "'");
                                $p->addFilter($s_ex[0] . "='" . $_SESSION[$cCode]['main'][$s_ex[1]] . "'");
                            }
                        }
                    }
                }
                $tmpPairData = $p->lookupAll()->result();
                cekBiru($this->db->last_query());
                if (sizeof($tmpPairData) > 0) {
                    foreach ($tmpPairData as $tmpPairData0) {
//                        $pairedStock[$tmpPairData0->produk_id] = $tmpPairData0->jumlah;
                        $pairedStock[$tmpPairData0->produk_id][] = (array)$tmpPairData0;
                    }
                }

            }
        }


        $processor = MODUL_PATH . $this->configUi[$jenisTr]['pihakMainProcessor'] . "/" . "$jenisTr/" . $this->configUi[$jenisTr]['pihakModelMain'];
        if (sizeof($tmpO) > 0) {
            if (sizeof($configPair) > 0) {
                cekHitam("MASUK ATAS...");
                foreach ($tmpO as $row) {
                    if (!$isSearching && isset($row->sisa_nilai_diskon) && floatval($row->sisa_nilai_diskon) < 1) {
                        continue;
                    }
//                    arrPrint($row);
//                    if (isset($pairedStock[$row->$selectorKeySrc_id])) {
                    if (sizeof($selectorFields) > 0) {
//                    cekLime("masuk");
                        $nCtr = 0;
                        $tmpName = "";
                        $tmpNameOrig = "";
                        if (isset($pairedStock[$row->id])) {

                        }
                        foreach ($selectorFields as $f) {
                            $nCtr++;
                            $align = $nCtr == 1 ? "text-left" : "text-right";
                            $fSize = $nCtr == 1 ? "font-size:1em" : "font-size:0.9em";
                            $color = isset($colors[$nCtr]) ? $colors[$nCtr] : "#000000";

                            if (is_numeric($row->$f)) {
                                $tmpName .= "<span style='$fSize ;margin:0px 2px 0px 2px;color:$color;' class='no-padding no-border $align'>" . number_format($row->$f) . "*</span>";
                                $tmpNameOrig .= number_format($row->$f);
                            }
                            else {
                                $newFields = in_array($f, arrAvailFields()) ? formatNota($f, $row->$f) : $row->$f;
                                $tmpName .= "<span style='$fSize ;margin:0px 2px 0px 2px;color:$color;' class='no-padding no-border $align'>" . $newFields . "</span>";
                                $tmpNameOrig .= $newFields;
                            }
                            if (count($pairedStock[$row->$selectorKeySrc_id]) > 0) {
                                $sumAvailSaldo = 0;
                                $sumKlaim = 0;
                                foreach ($pairedStock[$row->$selectorKeySrc_id] as $txData) {
//                                        arrPrint($txData);
                                    $sumAvailSaldo += $txData["nilai"];
                                    $sumKlaim += $txData["nilai_klaim"];
                                }
                                $tmpName .= "<span style='$fSize ;margin:0px 2px 0px 2px;color:$color;' class='no-padding no-border $align'>@" . number_format($sumAvailSaldo) . "</span>";
                                $tmpNameOrig .= " @" . number_format($sumAvailSaldo) . " ";
                            }
                        }
                        $tmpName = rtrim($tmpName, "| ");


                    }
                    else {
                        $tmpName = isset($row->$selectColumn) ? $row->$selectColumn : "";

                        if (isset($row->name)) {
                            $tmpName = isset($row->$selectColumn) ? $row->$selectColumn : $row->name;
                        }
                    }
                    if (strlen($tmpName) > 1) {
                        $items[] = array(
                            "id" => $row->id,
                            "label" => $tmpName,
                            "label_orig" => $tmpNameOrig,
                            "target" => $processor,
                            "confirm" => $pihakNameMainDiskonConfirm,
                            "supplier_nama" => $supplier_id,
                        );
                    }
//                    }
                }
            }
            else {
                cekHitam("MASUK BAWAH...");
                foreach ($tmpO as $row) {
                    if ($mdlName == "MdlLockerStockDiskonVendor" && isset($row->sisa_nilai_diskon)) {
                        $sisaVal = floatval($row->sisa_nilai_diskon);
                        $isCancelled = false;
                        $isParentCancelled = false;
                        $isSaldoHabis = false;
                        $invalidReasons = array();

                        $cancel_nomer = !empty($row->cancel_transaksi_nomer) ? $row->cancel_transaksi_nomer : "";
                        $ref_nomer = !empty($row->reference_nomer) ? $row->reference_nomer : "";

                        if (isset($row->trash_4) && $row->trash_4 == 1) {
                            $isCancelled = true;
                            $invalidReasons[] = "DIBATALKAN";
                        } elseif (isset($row->trash) && $row->trash == 1) {
                            $isCancelled = true;
                            $invalidReasons[] = "DIHAPUS (TRASH)";
                        } elseif (isset($row->status) && $row->status == 0) {
                            $isCancelled = true;
                            $invalidReasons[] = "DRAFT (NOT APPROVED)";
                        } elseif (isset($row->link_id) && $row->link_id != 0) {
                            $isCancelled = true;
                            $invalidReasons[] = "ALREADY LINKED";
                        }

                        // Periksa juga status dokumen referensi/induk (seperti PO) di tabel transaksi
                        if (!$isCancelled && !empty($ref_nomer)) {
                            $qRef = $this->db->query("SELECT trash_4, trash, cancel_transaksi_nomer FROM transaksi WHERE nomer = ? LIMIT 1", array($ref_nomer));
                            if ($qRef && $qRef->num_rows() > 0) {
                                $rRef = $qRef->row();
                                if ($rRef->trash_4 == 1 || $rRef->trash == 1) {
                                    $isCancelled = true;
                                    $isParentCancelled = true;
                                    $invalidReasons[] = "PO REFERENSI DIBATALKAN";
                                    if (empty($cancel_nomer) && !empty($rRef->cancel_transaksi_nomer)) {
                                        $cancel_nomer = $rRef->cancel_transaksi_nomer;
                                    }
                                }
                            }
                        }

                        // Jika transaksi dibatalkan tapi cancel_transaksi_nomer masih kosong, cari nomor pembatalannya
                        if ($isCancelled && empty($cancel_nomer)) {
                            if (!empty($row->cancel_transaksi_jenis) && !empty($row->cancel_dtime) && !empty($row->cancel_id)) {
                                $qCancel = $this->db->query("SELECT nomer FROM transaksi WHERE oleh_id = ? AND dtime = ? AND jenis = ? LIMIT 1", array(
                                    $row->cancel_id,
                                    $row->cancel_dtime,
                                    $row->cancel_transaksi_jenis
                                ));
                                if ($qCancel && $qCancel->num_rows() > 0) {
                                    $cancel_nomer = $qCancel->row()->nomer;
                                }
                            }
                            if (empty($cancel_nomer)) {
                                $qCancelRef = $this->db->query("SELECT nomer FROM transaksi WHERE reference_id = ? OR reference_nomer = ? LIMIT 1", array(
                                    $row->transaksi_id,
                                    $row->nomer
                                ));
                                if ($qCancelRef && $qCancelRef->num_rows() > 0) {
                                    $cancel_nomer = $qCancelRef->row()->nomer;
                                }
                            }
                        }

                        if ($sisaVal < 1) {
                            $isSaldoHabis = true;
                            $invalidReasons[] = "SALDO HABIS";
                        }

                        $isDiffSupplier = false;
                        if (!empty($sessionSupplierId) && !empty($row->supplier_id) && ($row->supplier_id != $sessionSupplierId)) {
                            $isDiffSupplier = true;
                            $invalidReasons[] = "SUPPLIER BERBEDA";
                        }

                        $isDisabled = ($isCancelled || $isSaldoHabis || $isDiffSupplier);

                        // Saat bukan mode pencarian, sembunyikan transaksi yang tidak valid / saldo habis
                        if (!$isSearching && $isDisabled) {
                            continue;
                        }

                        $nomerEx = explode(".", $row->nomer);
                        $jenisCode = isset($nomerEx[0]) ? $nomerEx[0] : "";
                        $jenisBadge = "GRN";
                        $badgeClass = "label-primary";
                        if ($jenisCode === "3344") {
                            $jenisBadge = "Klaim Tambahan";
                            $badgeClass = "label-warning";
                        } elseif ($jenisCode === "4643") {
                            $jenisBadge = "Pembelian Langsung";
                            $badgeClass = "label-info";
                        }

                        // Klasifikasi Tipe Diskon vs Free Produk (Compact)
                        $countFree = isset($row->count_free) ? intval($row->count_free) : 0;
                        $countReg = isset($row->count_reguler) ? intval($row->count_reguler) : 0;

                        if ($countFree > 0 && $countReg == 0) {
                            $tipeBadge = "<span class='label label-success' style='font-size: 8.5px; padding: 1px 5px; font-weight: 500;'><i class='fa fa-gift'></i> Free Produk</span>";
                            $detailTipe = "<span class='text-success font-weight-bold'><i class='fa fa-gift'></i> {$countFree} Free</span>";
                        } elseif ($countReg > 0 && $countFree == 0) {
                            $tipeBadge = "<span class='label label-info' style='font-size: 8.5px; padding: 1px 5px; font-weight: 500;'><i class='fa fa-percent'></i> Diskon</span>";
                            $detailTipe = "<span class='text-muted'><i class='fa fa-percent'></i> {$countReg} Diskon</span>";
                        } else {
                            $tipeBadge = "<span class='label label-warning' style='font-size: 8.5px; padding: 1px 5px; font-weight: 500;'><i class='fa fa-tags'></i> Campuran</span>";
                            $detailTipe = "<span><i class='fa fa-percent text-muted'></i> {$countReg} &bull; <i class='fa fa-gift text-success'></i> {$countFree} Free</span>";
                        }

                        $statusBadge = "";
                        $causeNotice = "";
                        if ($isCancelled) {
                            $cancelRefInfo = (!empty($cancel_nomer)) ? " (No. Batal: <b>{$cancel_nomer}</b>)" : "";
                            $poRefInfo = (!empty($ref_nomer)) ? " &bull; Ref PO: <b>{$ref_nomer}</b>" : "";
                            $statusBadge .= " <span class='label label-danger' style='font-size: 8.5px; padding: 1px 4px; margin-left: 3px;'><i class='fa fa-ban'></i> BATAL</span>";
                            
                            if ($isParentCancelled) {
                                $causeNotice .= "<div style='font-size: 10.5px; line-height: 1.35; color: #c0392b; background: #fdf2f2; border: 1px solid #f5c6cb; padding: 2px 6px; border-radius: 2px;'>"
                                             . "<i class='fa fa-ban'></i> Dokumen PO referensi (<b>{$ref_nomer}</b>) telah <b>DIBATALKAN</b>{$cancelRefInfo}."
                                             . "</div>";
                            } else {
                                $causeNotice .= "<div style='font-size: 10.5px; line-height: 1.35; color: #c0392b; background: #fdf2f2; border: 1px solid #f5c6cb; padding: 2px 6px; border-radius: 2px;'>"
                                             . "<i class='fa fa-ban'></i> Dokumen berstatus <b>DIBATALKAN</b>{$cancelRefInfo}{$poRefInfo}."
                                             . "</div>";
                            }
                        } elseif ($isSaldoHabis) {
                            $statusBadge .= " <span class='label label-default' style='font-size: 8.5px; padding: 1px 4px; margin-left: 3px; background-color: #7f8c8d; color: #fff;'><i class='fa fa-info-circle'></i> HABIS</span>";
                            $causeNotice .= "<div style='font-size: 10.5px; line-height: 1.35; color: #7f8c8d; background: #f4f4f4; border: 1px solid #e0e0e0; padding: 2px 6px; border-radius: 2px;'>"
                                         . "<i class='fa fa-info-circle'></i> Saldo diskon pada transaksi ini <b>SUDAH HABIS (Rp 0)</b>."
                                         . "</div>";
                        }

                        if ($isDiffSupplier) {
                            $namaSuppTampil = !empty($row->supplier_nama) ? $row->supplier_nama : "ID " . $row->supplier_id;
                            $namaSuppSesi = !empty($supplier_nama_sesi) ? $supplier_nama_sesi : "ID " . $sessionSupplierId;
                            $statusBadge .= " <span class='label label-warning' style='font-size: 8.5px; padding: 1px 4px; margin-left: 3px;'><i class='fa fa-building-o'></i> VENDOR BEDA</span>";
                            $causeNotice .= "<div style='font-size: 10.5px; line-height: 1.35; color: #8a6d3b; background: #fcf8e3; border: 1px solid #faebcc; padding: 2px 6px; border-radius: 2px; margin-top: 2px;'>"
                                         . "<i class='fa fa-exclamation-triangle'></i> Dokumen milik <b>{$namaSuppTampil}</b> (berbeda dari pilihan sesi)."
                                         . "</div>";
                        }

                        $tglFormatted = (!empty($row->tgl_transaksi) && $row->tgl_transaksi !== "0000-00-00")
                            ? date("d/m/Y", strtotime($row->tgl_transaksi)) 
                            : "-";
                        $sisaFormatted = number_format(max(0, $sisaVal), 0, ',', '.');
                        $totalDiskonFormatted = number_format($row->total_nilai_diskon, 0, ',', '.');

                        $sisaColor = ($sisaVal >= 1 && !$isCancelled && !$isDiffSupplier) ? "#00a65a" : "#7f8c8d";
                        $sisaLabelText = ($sisaVal >= 1) ? "Rp {$sisaFormatted}" : "Rp 0 <small style='font-weight:normal;color:#e74c3c;'>(Habis)</small>";

                        $namaSupplierDoc = !empty($row->supplier_nama) ? $row->supplier_nama : "-";

                        $tmpName = "<div style='padding: 2px 0; width: 100%; font-family: -apple-system, BlinkMacSystemFont, \"Segoe UI\", Roboto, sans-serif;'>"
                              // Baris 1: Nomor Dokumen + Badge Jenis di kiri, Sisa Saldo di kanan
                              . "  <div style='display: flex; justify-content: space-between; align-items: center; margin-bottom: 2px;'>"
                              . "    <div style='font-weight: 700; font-size: 12.5px; color: " . ($isDisabled ? "#7f8c8d" : "#2c3e50") . ";'>"
                              . "      {$row->nomer}"
                              . "      <span class='label {$badgeClass}' style='font-size: 8.5px; padding: 1px 4px; margin-left: 3px; vertical-align: middle;'>{$jenisBadge}</span>"
                              . "    </div>"
                              . "    <div style='text-align: right; white-space: nowrap; margin-left: 8px;'>"
                              . "      <span style='font-weight: 700; font-size: 12px; color: {$sisaColor};'>{$sisaLabelText}</span>"
                              . "    </div>"
                              . "  </div>"
                              // Baris 2: Nama Vendor (Ellipsis agar tidak wrap) di kiri, Tipe Diskon & Status di kanan
                              . "  <div style='display: flex; justify-content: space-between; align-items: center; font-size: 11px; margin-bottom: 2px;'>"
                              . "    <div style='overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 175px; color: " . ($isDisabled ? "#777" : "#3c8dbc") . "; font-weight: 600;' title='{$namaSupplierDoc}'>"
                              . "      <i class='fa fa-building-o' style='color: #888;'></i> {$namaSupplierDoc}"
                              . "    </div>"
                              . "    <div style='white-space: nowrap; margin-left: 6px;'>"
                              . "      {$tipeBadge}"
                              . "      {$statusBadge}"
                              . "    </div>"
                              . "  </div>"
                              // Baris 3: Tanggal & Tipe Item di kiri, Total Diskon di kanan
                              . "  <div style='display: flex; justify-content: space-between; align-items: center; font-size: 10px; color: #888;'>"
                              . "    <div>"
                              . "      <i class='fa fa-calendar'></i> {$tglFormatted} &bull; {$detailTipe}"
                              . "    </div>"
                              . "    <div style='color: #999; font-size: 9.5px; white-space: nowrap; margin-left: 6px;'>"
                              . "      Tot: Rp {$totalDiskonFormatted}"
                              . "    </div>"
                              . "  </div>"
                              // Baris 4: Kotak Notice jika ada kondisi khusus (Batal / Saldo Habis / Supplier Berbeda)
                              . (!empty($causeNotice) ? "  <div style='margin-top: 3px;'>{$causeNotice}</div>" : "")
                              . "</div>";

                        $tmpNameOrig = $row->nomer . " [" . implode(", ", $invalidReasons) . "] (Sisa: Rp " . $sisaFormatted . ")";
                    }
                    elseif (sizeof($selectorFields) > 0) {
                        $nCtr = 0;
                        $tmpName = "";
                        $tmpNameOrig = "";
                        if (isset($pairedStock[$row->id])) {

                        }
                        foreach ($selectorFields as $f) {
                            $nCtr++;
                            $align = $nCtr == 1 ? "text-left" : "text-right";
                            $fSize = $nCtr == 1 ? "font-size:1em" : "font-size:0.9em";
                            $color = isset($colors[$nCtr]) ? $colors[$nCtr] : "#000000";

                            if (is_numeric($row->$f)) {
                                $tmpName .= "<span style='$fSize ;margin:0px 2px 0px 2px;color:$color;' class='no-padding no-border $align'>" . number_format($row->$f) . "</span>";
                                $tmpNameOrig .= " " . number_format($row->$f) . " ";
                            }
                            else {
                                $newFields = in_array($f, arrAvailFields()) ? formatNota($f, $row->$f) : $row->$f;
                                $tmpName .= "<span style='$fSize ;margin:0px 2px 0px 2px;color:$color;' class='no-padding no-border $align'>" . $newFields . "</span>";
                                $tmpNameOrig .= " " . $newFields . " ";
                            }
                        }
                        $tmpName = rtrim($tmpName, "| ");
                    }
                    else {
                        $tmpName = isset($row->$selectColumn) ? $row->$selectColumn : "";
                        $tmpNameOrig = isset($row->$selectColumn) ? $row->$selectColumn : "";
                        if (isset($row->label)) {
                            $tmpName = isset($row->label) ? $row->label : $row->name;
                            $tmpNameOrig = isset($row->label) ? $row->label : $row->name;
                        }
                        elseif (isset($row->name)) {
                            $tmpName = isset($row->$selectColumn) ? $row->$selectColumn : $row->name;
                            $tmpNameOrig = isset($row->$selectColumn) ? $row->$selectColumn : $row->name;
                        }
                    }
                    if (strlen($tmpName) > 1) {
                        $data_id = isset($this->configUi[$jenisTr]["pihakNameMainDiskonIdSelector"]) ? $this->configUi[$jenisTr]["pihakNameMainDiskonIdSelector"] : "id";
                        $item_arr = array(
                            "id" => $row->$data_id,
                            "label" => $tmpName,
                            "label_orig" => $tmpNameOrig,
                            "target" => $processor,
                            "confirm" => $pihakNameMainDiskonConfirm,
                            "supplier_nama" => $supplier_id,
                        );
                        if (isset($isDisabled) && $isDisabled) {
                            $item_arr["disabled"] = true;
                        }
                        $items[] = $item_arr;
                    }
                }
            }

        }

        if (sizeof($pihakAddStaticEntry) > 0) {
            foreach ($pihakAddStaticEntry as $key => $val) {
                $addStaticEntry[$key] = getDefaultWarehouseID($this->session->login['cabang_id'])[$val];
                $addStaticEntry["target"] = $processor;

            }

            $items[] = $addStaticEntry;
            foreach ($items as $iCtr => $iSpec) {
                if ($this->session->login['gudang_id'] == $iSpec['id']) {
                    unset($items[$iCtr]);
                }
            }
        }


        $data = array(
            "mode" => "view",
            "items" => $items,
            "search" => $key,
        );


        $this->load->view("_selectorPihak", $data);
    }

}
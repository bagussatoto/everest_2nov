<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "Modul_Controller.php";

class History extends Modul_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model("Mdls/MdlCurrency");
        /* ----------------------------------------------------------------------------------
          * loader cunstruk yg wajib ada
          * variabel-variabel bisa langsung dipangil, apa saja yang ada bisa dilihat didalamnya
          * ----------------------------------------------------------------------------------*/
        // require_once "_construct_file.php";
        $this->configUiModul = loadConfigUiModul_he_misc();
        $this->masterConfigUi = $this->config->item("heTransaksi_ui");
        $this->bgTransaksiColor = bgTransaksiColor();
    }

    public function index()
    {
    }

    public function viewHistory()
    {
        if (!isset($this->session->login['id'])) {
            gotoLogin();
        }
        $starttime = microtime(true);
        $limit = 100;
        $maxPageNum = 20;
        $jenisTr = $this->uri->segment(4);
        $jenisTrsub = $this->uri->segment(5);
        $cCode = $this->cCode;
        $allStep = $this->configUi[$jenisTr]['steps'];
        $availSteps = array();
        foreach ($allStep as $step => $tempStep) {
            $availSteps[$tempStep['target']] = $step;
        }

        $selectedSTep = isset($availSteps[$jenisTrsub]) ? $availSteps[$jenisTrsub] : 1;
        $historyFields = isset($this->configUi[$jenisTr]['historyFields'][$selectedSTep]) ? $this->configUi[$jenisTr]['historyFields'][$selectedSTep] : $this->configUi[$jenisTr]['shortHistoryFields'];
        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
        $historyFieldsExt = isset($this->configUi[$jenisTr]["extHistoryFields"][$selectedSTep]) ? $this->configUi[$jenisTr]["extHistoryFields"][$selectedSTep] : array();
        $extHistoryFields2 = isset($this->configUi[$jenisTr]["extHistoryFields2"][$selectedSTep]) ? $this->configUi[$jenisTr]["extHistoryFields2"][$selectedSTep] : array();
        $customButton = isset($this->configLayout[$jenisTr]["customButton"][$selectedSTep]) ? $this->configLayout[$jenisTr]["customButton"][$selectedSTep] : array();
        $printValas = isset($this->configLayout[$jenisTr]["print_nvalas"]) ? $this->configLayout[$jenisTr]["print_nvalas"] : array();
        $pairTransaksi = isset($this->configUi[$jenisTr]['pairTransaksi']) ? $this->configUi[$jenisTr]['pairTransaksi'] : array();
        $extHistoryKeterangan = isset($this->configUi[$jenisTr]['extHistoryKeterangan'][$selectedSTep]) ? $this->configUi[$jenisTr]['extHistoryKeterangan'][$selectedSTep] : array();
        //$this->debugArrPrint($extHistoryKeterangan);
        $modul = isset($this->masterConfigUi[$jenisTr]["modul"]) ? $this->masterConfigUi[$jenisTr]["modul"] : false;
        $modul_path = base_url() . $modul . "/";
        // cekHere("[$selectedSTep]");
        // arrPrint($historyFields);
        $mb = New MobileDetect();
        $isMob = $mb->isMobile();
        if ($isMob) {
            $historyFields = isset($this->configUi[$jenisTr]['compactHistoryFields']) ? $this->configUi[$jenisTr]['compactHistoryFields'] : array();
        }

        $backdate_f = formatTanggal(backDate(30), 'Y-m-d');

        $date1 = $this->getSafeDateParam('date1', $backdate_f);
        $date2 = $this->getSafeDateParam('date2', date("Y-m-d"));

        // START OF COMPLETE REPEATED LOGIC
        $allowFakturFilter = false;
        if (isset($this->configUi[$jenisTr]['filterTanggalFaktur'])) {
            $allowFakturFilter = (bool)$this->configUi[$jenisTr]['filterTanggalFaktur'];
        }
        else {
            $allowFakturFilter = ($jenisTr == "110" || (isset($this->configUi[$jenisTr]['shortHistoryFields']) && isset($this->configUi[$jenisTr]['shortHistoryFields']['dateFaktur'])));
        }

        $fakturM = isset($_GET['faktur_m']) && preg_match('/^(0[1-9]|1[0-2])$/', (string)$_GET['faktur_m']) ? (string)$_GET['faktur_m'] : '';
        $fakturY = isset($_GET['faktur_y']) && preg_match('/^\d{4}$/', (string)$_GET['faktur_y']) ? (string)$_GET['faktur_y'] : '';

        $isFakturFilterActive = false;
        $startDateFaktur = "";
        $endDateFaktur = "";
        if ($allowFakturFilter && $fakturY !== '') {
            if ($fakturM !== '') {
                $isFakturFilterActive = true;
                $startDateFaktur = $fakturY . "-" . $fakturM . "-01 00:00:00";
                $lastDay = date("t", strtotime($fakturY . "-" . $fakturM . "-01"));
                $endDateFaktur = $fakturY . "-" . $fakturM . "-" . $lastDay . " 23:59:59";
            }
            elseif (isset($_GET['faktur_m']) || isset($_GET['faktur_y'])) {
                $isFakturFilterActive = true;
                $startDateFaktur = $fakturY . "-01-01 00:00:00";
                $endDateFaktur = $fakturY . "-12-31 23:59:59";
            }
        }

        // Memaksa rentang tanggal berada dalam tahun yang sama dan menyiapkan pesan alert banner
        $year1 = substr($date1, 0, 4);
        $year2 = substr($date2, 0, 4);

        if ($year1 !== $year2) {
            $date1 = $year2 . "-01-01";
        }
        if ($isFakturFilterActive) {
            $alertMessage = "";
        }
        else {
            $alertMessage = "
            <div class='alert alert-danger alert-dismissible' style='margin-top: 10px; margin-bottom: 10px;'>
                <button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>
                <h4><i class='icon blink fa fa-warning'></i> Perhatian!</h4>
                Rentang tanggal pencarian harus berada dalam tahun yang sama. Jika data yang dicari berbeda tahun, silakan lakukan pencarian per tahun. Silahkan pilih tahun dahulu.
            </div>";
        }

        // Tambahan swal hanya jika halaman dibuka pertama kali
        $swalScript = "";
        $isFirstLoad = !isset($_GET['date1']) && !isset($_GET['search']) && !isset($_GET['faktur_y']);
        if ($isFirstLoad) {
            $swalScript = "
            <script>
            $(document).ready(function() {
                swal({
                    title: 'Perhatian!',
                    html: 'Rentang tanggal pencarian harus berada dalam tahun yang sama. <br>Jika data yang dicari berbeda tahun, silakan lakukan pencarian per tahun',
                    type: 'warning',
                    confirmButtonText: 'OK'
                });
            });
            </script>";
        }

        //region preparing ERP step labels for top link
        $steps = $this->configUi[$jenisTr]['steps'];
        $stepLabels = array();
        $stepLinks = array();
        if (sizeof($steps) > 1) {
            $subCodes = array();
            $stepCodes = array();
            $jmlStep = count($steps);

            foreach ($steps as $stepNumber => $stepSpec) {
                if ($stepNumber <= $jmlStep) {
                    $searchForm = isset($_GET['search']) ? "&search=" . $_GET['search'] : "";
                    $grParam = isset($_GET['gr']) ? "&gr=" . $_GET['gr'] : "";
                    $subCodes[$stepSpec['target']] = $stepSpec['label'];
                    $stepCodes[] = $stepSpec['target'];
                    $stepLabels[$stepNumber] = $stepSpec['label'];
                    if ($isFakturFilterActive) {
                        $stepLinks[$stepNumber] = MODUL_PATH . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->jenisTr . "/" . $stepSpec['target'] . "?faktur_m=$fakturM&faktur_y=$fakturY$searchForm$grParam";
                    }
                    else {
                        $stepLinks[$stepNumber] = MODUL_PATH . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->jenisTr . "/" . $stepSpec['target'] . "?date1=$date1&date2=$date2$searchForm$grParam";
                    }
                }
            }
// END OF COMPLETE REPEATED LOGIC
// START OF COMPLETE REPEATED LOGIC
            if (isset($_GET['stID'])) {
                $currentState = $_GET['stID'];
            }
            else {
                if ($jenisTr == "110" && isset($this->configUi[$jenisTr]['steps'][1]['target']) && $this->configUi[$jenisTr]['steps'][1]['target'] == "110r") {
                    $defaultTarget = isset($steps[3]['target']) ? $steps[3]['target'] : (isset($steps[2]['target']) ? $steps[2]['target'] : (isset($steps[1]['target']) ? $steps[1]['target'] : ""));
                }
                else {
                    $defaultTarget = isset($this->configUi[$jenisTr]['steps'][1]['target']) ? $this->configUi[$jenisTr]['steps'][1]['target'] : "";
                }
                $currentState = strlen($this->uri->segment(5)) > 0 ? $this->uri->segment(5) : $defaultTarget;
            }

            if (isset($availSteps[$currentState])) {
                $selectedSTep = $availSteps[$currentState];
                $historyFields = isset($this->configUi[$jenisTr]['historyFields'][$selectedSTep]) ? $this->configUi[$jenisTr]['historyFields'][$selectedSTep] : $this->configUi[$jenisTr]['shortHistoryFields'];
                $historyFieldsExt = isset($this->configUi[$jenisTr]["extHistoryFields"][$selectedSTep]) ? $this->configUi[$jenisTr]["extHistoryFields"][$selectedSTep] : array();
                $extHistoryFields2 = isset($this->configUi[$jenisTr]["extHistoryFields2"][$selectedSTep]) ? $this->configUi[$jenisTr]["extHistoryFields2"][$selectedSTep] : array();
                $customButton = isset($this->configLayout[$jenisTr]["customButton"][$selectedSTep]) ? $this->configLayout[$jenisTr]["customButton"][$selectedSTep] : array();
                $extHistoryKeterangan = isset($this->configUi[$jenisTr]['extHistoryKeterangan'][$selectedSTep]) ? $this->configUi[$jenisTr]['extHistoryKeterangan'][$selectedSTep] : array();
            }
        }
// END OF COMPLETE REPEATED LOGIC
        //endregion


        //region lookup histories

        $this->load->model("MdlTransaksi");

        $tr = new MdlTransaksi();

        // START OF COMPLETE REPEATED LOGIC
        $searchStr = isset($_GET['search']) ? $_GET['search'] : "";
        $filters = array(
            "link_id" => "0",
        );
        if ($this->session->login['employee_type'] == "employee_freelance") {
            $filters["seller_id"] = $this->session->login['id'];
        }
        if (isset($filters) && (sizeof($filters) > 0)) {
            foreach ($filters as $key_f => $val_f) {
                $tr->addFilter("$key_f='" . $val_f . "'");
            }
        }

        // START OF COMPLETE REPEATED LOGIC
        if (isset($currentState)) {
            $tr->addFilter("jenis='" . $currentState . "'");
        }
        // END OF COMPLETE REPEATED LOGIC

        $addParams = $this->decodeSafeBase64ArrayParam('addParams');
        if ($addParams != null && sizeof($addParams) > 0) {
            $this->addSafeFilters($tr, $addParams);
        }

        // START OF COMPLETE REPEATED LOGIC
        if (isset($_GET['search']) && $_GET['search'] !== "") {
            $tr->setKeyWord($searchStr);
            if ($isFakturFilterActive) {
                $this->db->where("efaktur_dtime >=", $startDateFaktur);
                $this->db->where("efaktur_dtime <=", $endDateFaktur);
            }
        }
        else {
            if ($isFakturFilterActive) {
                $this->db->where("efaktur_dtime >=", $startDateFaktur);
                $this->db->where("efaktur_dtime <=", $endDateFaktur);
            }
            else {
                $this->db->where("fulldate >=", $date1);
                $this->db->where("fulldate <=", $date2);
            }
        }
        // END OF COMPLETE REPEATED LOGIC

        $jmlData = $tr->lookupHistoryCount();
        $page = (isset($_GET['page']) && $_GET['page'] > 0) ? ($_GET['page']) : 1;
        $offset = ($limit * ($page - 1));

        $addParams = $this->decodeSafeBase64ArrayParam('addParams');
        if ($addParams != null && sizeof($addParams) > 0) {
            $this->addSafeFilters($tr, $addParams);
        }

        $action = array(
            "viewDetails" => base_url() . get_class($this) . "/viewDetails",
        );

        // START OF COMPLETE REPEATED LOGIC
        if (isset($_GET['search']) && $_GET['search'] !== "") {
            $tr->setKeyWord($searchStr);
            if ($isFakturFilterActive) {
                $this->db->where("efaktur_dtime >=", $startDateFaktur);
                $this->db->where("efaktur_dtime <=", $endDateFaktur);
            }
        }
        else {
            if ($isFakturFilterActive) {
                $this->db->where("efaktur_dtime >=", $startDateFaktur);
                $this->db->where("efaktur_dtime <=", $endDateFaktur);
            }
            else {
                $this->db->where("fulldate >=", $date1);
                $this->db->where("fulldate <=", $date2);
            }
        }
        if (isset($_GET['date1']) || $isFakturFilterActive) {
            $limit = 100;
        }
        if ($isFakturFilterActive) {
            $this->db->order_by("efaktur_dtime", "asc");
            $this->db->order_by("id", "asc");
        }
        // END OF COMPLETE REPEATED LOGIC
        $tmpHist = $tr->lookupHistories($jmlData, $limit, $page)->result();

        // $this->debugArrPrint($tmpHist);
        //matiHEre();
        $arrCurrency = array();
        if (sizeof($printValas) > 0) {
            $trv = new MdlCurrency();
            $tmpCurrency = $trv->lookupAll()->result();
            if (sizeof($tmpCurrency) > 0) {
                foreach ($tmpCurrency as $key => $value) {
                    $arrCurrency[$key] = $value;
                }
            }
        }

        $cabang_id = $this->placeId;

        $arrayHistory = array();
        $arrayHistory_ids = array();
        $arrayHistory_canceled = array();
        $sumValue = array();
        if (sizeof($tmpHist) > 0) {
            if (sizeof($pairRegistries) > 0) {
                $arrSalesName = array();
                $arrCustByNomerTop = array();
                $arrTransID = array();
                $arrTransTopID = array();
                $arrNomerTop = array();
                $arrIndexID = array();
                $arrIdsHist = array();
                $arrTransHist = array();
                $arrTransMainHist = array();
                $arrRefID = array();
                foreach ($tmpHist as $row) {
                    $arrTransID[] = $row->id;
                    $arrTransTopID[] = $row->id_top;
                    if (isset($row->nomer_top) && $row->nomer_top != "") {
                        $arrNomerTop[] = $row->nomer_top;
                    }
                    if (isset($row->referenceID) && $row->referenceID > 0) {
                        $arrRefID[] = $row->referenceID;
                    }

                    if ($row->ids_his != "") {
                        $hist = blobDecode($row->ids_his);
                        foreach ($hist as $hisSpec) {
                            $arrIdsHist[$row->id][$hisSpec['step']] = array(
                                "step" => $hisSpec['step'],
                                "trID" => $hisSpec['trID'],
                                "nomer" => $hisSpec['nomer'],
                            );
                            $arrTransHist[] = $hisSpec['trID'];
                        }
                    }
                }

                $arrPairTransaksiResult = array();
                if (sizeof($pairTransaksi) > 0 && sizeof($arrRefID) > 0) {
                    $trPair = new MdlTransaksi();
                    $trPair->setFilters(array());
                    $trPair->addFilter("id in ('" . implode("','", array_unique($arrRefID)) . "')");
                    $tmpPair = $trPair->lookUpMainTransaksi()->result();
                    if (sizeof($tmpPair) > 0) {
                        foreach ($tmpPair as $pRow) {
                            $arrPairTransaksiResult[$pRow->id] = isset($pRow->ids_his) ? blobDecode($pRow->ids_his) : array();
                        }
                    }
                }

                $tmpReg_result = array();
                $trReg = new MdlTransaksi();

                $trReg->setFilters(array());

                // $trReg->setFields($pairRegistries);
                $selectKolom = implode(",", $pairRegistries) . ",transaksi_id";
                $trReg->setJointSelectFields($selectKolom);
                $trReg->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
                $tmpReg = $trReg->lookupDataRegistries()->result();
                if (sizeof($tmpReg) > 0) {
                    foreach ($tmpReg as $regRow) {
                        foreach ($pairRegistries as $param) {
                            $tmpReg_result[$regRow->transaksi_id][$param] = blobDecode($regRow->$param);
                        }

                    }
                }
//                if ($_SERVER['REMOTE_ADDR'] == "202.65.117.72") {
//                    mati_disini(__LINE__);
//                }

                $tr = new MdlTransaksi();
                $tr->setFilters(array());
                $tr->addFilter("id in ('" . implode("','", $arrTransTopID) . "')");
                $tmpTrTop = $tr->lookUpMainTransaksi()->result();
                if (sizeof($tmpTrTop) > 0) {
                    foreach ($tmpTrTop as $topSpec) {
                        $arrSalesName[$topSpec->id_top] = $topSpec->oleh_nama;
                    }
                }

                if (sizeof($arrNomerTop) > 0) {
                    $trCust = new MdlTransaksi();
                    $trCust->setFilters(array());
                    $trCust->addFilter("nomer in ('" . implode("','", array_unique($arrNomerTop)) . "')");
                    $tmpCustTop = $trCust->lookUpMainTransaksi()->result();
                    if (sizeof($tmpCustTop) > 0) {
                        foreach ($tmpCustTop as $custSpec) {
                            $arrCustByNomerTop[$custSpec->nomer] = $custSpec->customers_nama;
                        }
                    }
                }

                $arrCustByTransID = array();
                if (sizeof($arrTransID) > 0) {
                    $this->db->select("transaksi_id, sub_pihak_nama");
                    $this->db->where_in("transaksi_id", $arrTransID);
                    $this->db->where("trash", "0");
                    $this->db->where("sub_pihak_nama !=", "");
                    $tmpDetails = $this->db->get("transaksi_data")->result();
                    if (sizeof($tmpDetails) > 0) {
                        foreach ($tmpDetails as $dSpec) {
                            $cName = trim($dSpec->sub_pihak_nama);
                            if ($cName !== "") {
                                $arrCustByTransID[$dSpec->transaksi_id][$cName] = $cName;
                            }
                        }
                    }
                }

                if (sizeof($arrIdsHist) > 0) {
                    $tr = new MdlTransaksi();
                    // $tr->setParam("id");
                    // $tr->setInParam($arrTransHist);
                    $tr->setFilters(array());
                    $tr->addFilter("id in ('" . implode("','", $arrTransHist) . "')");
                    $tmpTransHist = $tr->lookUpMainTransaksi()->result();
                    $tmpTransHist_result = array();
                    if (sizeof($tmpTransHist) > 0) {
                        foreach ($tmpTransHist as $histSpec) {
                            $tmpTransHist_result[$histSpec->id] = array(
                                "oleh_id" => $histSpec->oleh_id,
                                "oleh_nama" => $histSpec->oleh_nama,
                            );
                        }
                    }
                    //                    $this->debugArrPrint($tmpTransHist_result);
                    //                    matiHEre();
                    if (sizeof($arrIdsHist) > 0) {
                        foreach ($arrIdsHist as $trID => $histSpec) {
                            foreach ($histSpec as $step => $detailSpec) {
                                if (array_key_exists($detailSpec['trID'], $tmpTransHist_result)) {
                                    $detailSpec['main'] = $tmpTransHist_result[$detailSpec['trID']];
                                }
                                $arrTransMainHist[$trID][$step] = $detailSpec;
                            }
                        }
                    }

                }

            }


            $numb = 0;
            foreach ($tmpHist as $ii => $row) {
                $step_number = $row->step_number;
                $this->placeId = $cabang_id = $row->cabang_id;

                // region ids_his
                $id_hist = blobDecode($row->ids_his);
                // endregion ids_his

                //region memangil global counter
                $tNomer_top = $row->nomer_top;
                //                $tr = new MdlTransaksi();
                //                $tr->addFilter("param='main'");
                //                $tmpReg = $tr->lookupRegistriesByNumber($tNomer_top)->result();
                //
                //                $arrSalesName = "";
                //                foreach ($tmpReg as $tmpRowReg) {
                //                    $arrSalesName = $tmpRowReg->oleh_nama;
                //                }
                $salesName = isset($arrSalesName[$row->id_top]) ? $arrSalesName[$row->id_top] : "-";


                $tNomer = $row->nomer;
                $jenisTrtop = explode(".", $tNomer_top)[0];
                $jenisTrsub = explode(".", $tNomer)[0];
                $counterjenis = "$jenisTrsub|" . $this->placeId;
                // $counterjenis = my_cabang_id()=="-1"? "$jenisTrsub":"$jenisTrsub|" . $this->placeId;

                // matiHEre($jenisTrsub);
                $rawIdsHis = blobDecode($row->ids_his);
                $counterIds_his = array();
                if (is_array($rawIdsHis)) {
                    if (isset($rawIdsHis[1]['counters'])) {
                        $counterIds_his = blobDecode($rawIdsHis[1]['counters']);
                    } else {
                        foreach ($rawIdsHis as $stepItem) {
                            if (is_array($stepItem) && isset($stepItem['counters'])) {
                                $counterIds_his = blobDecode($stepItem['counters']);
                                break;
                            }
                        }
                    }
                }
                $counters = blobDecode($row->counters);
                // $this->debugArrPrint($counters);
                // matiHere();

                $counterGlobal = (is_array($counters) && isset($counters['stepCode|placeID'][$counterjenis])) ? $counters['stepCode|placeID'][$counterjenis] : "";


                $counterIds_his_global = (is_array($counterIds_his) && isset($counterIds_his['stepCode|placeID']["$jenisTrtop|$cabang_id"])) ? $counterIds_his['stepCode|placeID']["$jenisTrtop|$cabang_id"] : "";
                $cGlobals = digit_5($counterGlobal);
                $cGlobal_spo = digit_5($counterIds_his_global);

                //endregion

                if (sizeof($pairRegistries) > 0) {
                    if ((sizeof($tmpReg_result) > 0) && (isset($tmpReg_result[$row->id]))) {
                        foreach ($tmpReg_result[$row->id] as $param => $eReg) {
                            //                                                        cekLime($param);
                            if ($param == "main") {
                                foreach ($eReg as $k => $v) {
                                    if (!isset($row->$k)) {
                                        $row->$k = $v;
                                    }
                                }
                            }
                            else {
                                if (sizeof($extHistoryFields2) > 0) {
                                    foreach ($extHistoryFields2 as $k1 => $v1) {
                                        if (is_array($v1)) {
                                            $kolom = $v1['kolom'];
                                            $format = $v1['format'];
                                            //                                                cekHitam(":: $kolom :: $format ::");
                                            if (!isset($row->$k1)) {
                                                $tmpDetail = "";
                                                foreach ($eReg as $eeReg) {
                                                    $valDetail = formatField_he_format($format, $eeReg[$kolom]);
                                                    $tmpDetail .= "<span>$valDetail</span><br>";
                                                }
                                                $row->$k1 = $tmpDetail;
                                            }
                                        }
                                        else {

                                            if (!isset($row->$k1)) {
                                                $tmpDetail = "";
                                                foreach ($eReg as $eeReg) {
                                                    $valDetail = formatField_he_format("nomer", $eeReg[$v1]);
                                                    $tmpDetail .= "<span>$valDetail</span><br>";
                                                    //                                                $this->debugArrPrint($eeReg);
                                                }
                                                $row->$k1 = $tmpDetail;
                                            }
                                        }
                                        //                                        else{
                                        //                                            mati_disini("sudah ada $k1");
                                        //                                        }
                                        //
                                    }
                                }
                            }
                        }
                    }
                    if (sizeof($pairTransaksi) > 0) {
                        if ($row->referenceID > 0 && isset($arrPairTransaksiResult[$row->referenceID])) {
                            $hisTr = $arrPairTransaksiResult[$row->referenceID];
                            foreach ($hisTr as $step => $hisTrSpec) {
                                foreach ($pairTransaksi['kolom'] as $keyPair => $labelPair) {
                                    $keyPairs = $keyPair . "_" . $step;
                                    $row->$keyPairs = isset($hisTrSpec[$labelPair]) ? $hisTrSpec[$labelPair] : "--";
                                }
                            }
                        }
                    }
                    $arrItemShow = isset($this->configUi[$jenisTr]["shortItemsFields"]) ? $this->configUi[$jenisTr]["shortItemsFields"] : array();
                    $detail = viewDetailTransaksi($tmpReg_result[$row->id], $arrItemShow, $row->jenis_master);


                    $arrItemShowFaktur = isset($this->configUi[$jenisTr]["shortItemsFaktur"][$step_number]["headers"]) ? $this->configUi[$jenisTr]["shortItemsFaktur"][$step_number]["headers"] : array();
                    $gateItemShowFaktur = isset($this->configUi[$jenisTr]["shortItemsFaktur"][$step_number]["gate"]) ? $this->configUi[$jenisTr]["shortItemsFaktur"][$step_number]["gate"] : NULL;
                    $detail_faktur = viewDetailFaktur($tmpReg_result[$row->id], $arrItemShowFaktur, $row->jenis_master, true, $gateItemShowFaktur);
                }


                if (sizeof($historyFieldsExt) > 0) {
                    foreach ($historyFieldsExt as $alias => $colom) {
                        $row->$alias = $row->$colom;
                    }
                }
                $tmp = array();
                $tmp1 = array();

                $numb++;
                foreach ($historyFields as $fName => $fLabel) {

                    if (strpos($fName, '+') !== false) {//==mengandung penggabungan (+)
                        $chars = explode("+", $fName);
                        $colValue = "";
                        foreach ($chars as $key) {
                            if (is_numeric($row->$key)) {
                                if (!isset($sumValue[$key])) {
                                    $sumValue[$key] = 0;
                                }
                                $sumValue[$key] += $row->$key;
                            }
                            $colValue .= isset($row->$key) ? formatField_he_format($key, $row->$key) . "<br>" : "";
                        }
                        $colValue = rtrim($colValue, "<br>");
                    }
                    else {

                        if (is_numeric(isset($row->$fName) ? $row->$fName : "")) {
                            if (!isset($sumValue[$fName])) {
                                $sumValue[$fName] = 0;
                            }
                            $sumValue[$fName] += $row->$fName;
                        }

                        //region nomer dengan global counter
                        if ($fName == "nomer") {
                            // switch ($jenisTr) {
                            switch ($jenisTrsub) {
                                case "582s":
                                    $kolomValues = $row->$fName . "&#x2011;$cGlobals";
                                    break;
                                // case "582spd":
                                default:
                                    // $kolomValue_0s = formatField($fName, $row->$fName);
                                    $kolomValue_0s = formatField_he_format($fName, $row->$fName);
                                    $kolomValues = str_replace("</span>", "&#x2011;" . $cGlobals, $kolomValue_0s);
                                    break;
                            }
                        }
                        elseif ($fName == "nomer_top") {
                            // $kolomValue_0s = formatField($fName, $row->$fName);
                            $kolomValue_0s = formatField_he_format($fName, $row->$fName);
                            $kolomValues = str_replace("</span>", "&#x2011;" . $cGlobal_spo, $kolomValue_0s);
                        }
                        elseif ($fName == "customers_nama") {
                            $isGunggung = (
                                (isset($row->gunggunganMode) && $row->gunggunganMode == 1) ||
                                (isset($row->gunggungan_mode) && $row->gunggungan_mode == 1) ||
                                (isset($row->efaktur) && $row->efaktur == "GUNGGUNG") ||
                                (isset($row->eFaktur) && $row->eFaktur == "GUNGGUNG")
                            );
                            $custVal = (isset($row->$fName) && $row->$fName !== "" && $row->$fName !== 0 && $row->$fName !== "0") ? $row->$fName : "";
                            if ($custVal === "" && isset($arrCustByTransID[$row->id]) && sizeof($arrCustByTransID[$row->id]) > 0) {
                                $custNames = array_values($arrCustByTransID[$row->id]);
                                if (sizeof($custNames) == 1) {
                                    $custVal = $custNames[0];
                                }
                                else {
                                    $firstCust = $custNames[0];
                                    $remainingCount = sizeof($custNames) - 1;
                                    $allCustStr = htmlspecialchars(implode(", ", $custNames), ENT_QUOTES, 'UTF-8');
                                    $custVal = "<span title=\"$allCustStr\">$firstCust (+$remainingCount lainnya)</span>";
                                }
                            }
                            elseif ($custVal === "" && isset($row->nomer_top) && isset($arrCustByNomerTop[$row->nomer_top])) {
                                $custVal = $arrCustByNomerTop[$row->nomer_top];
                            }
                            if ($custVal === "") {
                                $custVal = "-";
                            }
                            if ($isGunggung) {
                                $kolomValues = "<span class=\"badge bg-yellow\" style=\"font-size:10px;margin-right:4px;\">(G)</span>" . $custVal;
                            }
                            else {
                                $kolomValues = $custVal;
                            }
                            $row->$fName = $kolomValues;
                        }
                        else {
                            //                            cekHitam(";; $fName");
                            // $kolomValues = isset($row->$fName) ? formatField($fName, $row->$fName) : "-";
                            $kolomValues = isset($row->$fName) ? formatField_he_format($fName, $row->$fName) : "-";
                        }
                        //endregion

                        // $colValue = isset($row->$fName) ? formatField($fName, $row->$fName) : "";

                        $colValue = isset($row->$fName) ? $kolomValues : "";
                        //                        cekLime("$colValue"." ".$fName);

                    }


                    if (is_array($fLabel)) {

                        $hisStep = $fLabel['step'];
                        $hisKey = $fLabel['key'];
                        //                        $tNomer = $id_hist[$hisStep][$hisKey];


                        if ($hisKey == "nomer") {
                            $colValue = isset($row->ids_his) ? showHistoriGlobalNumbers($row->ids_his, $hisStep, true, $this->jenisTr) : "";
                        }
                        else {
                            if (isset($fLabel['transaksi_jenis2'][$row->transaksi_jenis2])) {
                                $getKey = $fLabel['transaksi_jenis2'][$row->transaksi_jenis2];
                                //                                cekHere(":: $getKey ::");
                                // $colValue = isset($row->$getKey) ? formatField($getKey, $row->$getKey) : "";
                                // $colValue = isset($row->$getKey) ? formatField_he_format($getKey, $row->$getKey,$this->jenisTr,$modul_path) : "";
                                $colValue = isset($row->$getKey) ? formatField_he_format($getKey, $row->$getKey) : "";
                            }
                            else {

                                $colValue = "-";
                            }
                        }


                        //                            $tr = new MdlTransaksi();
                        //                            $tr->setFilters(array());
                        //                            $tr->addFilter("param='main'");
                        //                            $tmpReg = $tr->lookupRegistriesByNumber($tNomer)->result();
                        ////                            cekHere($this->db->last_query());
                        //
                        //                            $logistic = $tmpReg[0]->oleh_nama;
                        $logistic = "";
                        if (isset($arrTransMainHist[$row->id][$hisStep]['main'])) {
                            $main = $arrTransMainHist[$row->id][$hisStep]['main'];
                            $logistic = $main['oleh_nama'];
                        }
                    }


                    if ($fName == "no") {
                        // $colValue = formatField($fName, $numb);
                        $colValue = formatField_he_format($fName, $numb);
                    }

                    //                    cekHere($logistic);
                    $tmp['logistic'] = isset($logistic) && $logistic != null ? $logistic : 'undefined';
                    $tmp['sales_name'] = $salesName;
                    $tmp[$fName] = $colValue;
                    $tmp1["id"] = $row->id;
                }


                if (sizeof($arrCurrency) > 0) {
                    $valas = "";
                    $valas .= "<div class='btn-group'>";
                    $valas .= "<button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>";
                    $valas .= "<i class='fa fa-print'></i>";
                    $valas .= "<span class='caret'></span>";
                    $valas .= "</button>";

                    $valas .= "<ul style='background:#cde8ff;' class='dropdown-menu dropdown-menu-right'>";
                    foreach ($arrCurrency as $arrV) {
                        $nama = $arrV->nama;
                        $nomer = $row->nomer;
                        $nilai = number_format($arrV->exchange, 0);
                        // $valas .= " <li class='text-bold'><a class='dropdown-item' href='javascript:void(0);' onclick=\"top.popBig('" . base_url() . "Transaksi/viewReceipt/$nomer?type=" . blobEncode($nama) . "&f=" . blobEncode($arrV->exchange) . "')\"> <i class='fa fa-print'></i> in $nama - ($nilai) </a></li>";
                        $valas .= " <li class='text-bold'><a class='dropdown-item' href='javascript:void(0);' onclick=\"top.popBig('" . MODUL_PATH . "Printing/viewReceipt/" . $this->jenisTr . "/$nomer?type=" . blobEncode($nama) . "&f=" . blobEncode($arrV->exchange) . "')\"> <i class='fa fa-print'></i> in $nama - ($nilai) </a></li>";
                    }

                    // $valas .= " <li><a class='btn btn-xs btn-warning' href='javascript:void(0);' onclick=\"top.location.href='" . base_url() . "data/view/Currency'\"> <i class='fa fa-plus'></i> tambah currency </a></li>";
                    $valas .= " <li><a class='btn btn-xs btn-warning' href='javascript:void(0);' onclick=\"top.location.href='" . MODUL_PATH . "data/view/Currency'\"> <i class='fa fa-plus'></i> tambah currency </a></li>";
                    $valas .= "</ul>";
                    $valas .= "</div>";
                    $tmp["print_nvalas"] = $valas;
                }

                $tmp['item_fields'] = $detail;
                if ($detail_faktur != NULL) {
                    $tmp['eFaktur'] = $detail_faktur;
                }
                $tmp['next_pic'] = "-";

                // menambah background-color karena dicancel/reject/undo, atau yang dibatalkan
                //                $tmp['keterangan'] = "-";
                $addKeterangan = "";
                if (isset($row->trash_4) && ($row->trash_4 == 1)) {
                    //                    $tmp['keterangan'] = "CANCELED";
                    $addKeterangan = "CANCELED";
                    //                    $addKeterangan .= $row->cancel_dtime != NULL ? "&nl2br; date: " . $row->cancel_dtime : "";
                    //                    $addKeterangan .= $row->cancel_name != NULL ? "&nl2br; by: " . $row->cancel_name : "";
                    $addKeterangan .= $row->cancel_dtime != NULL ? nl2br(" date: " . $row->cancel_dtime) : "";
                    $addKeterangan .= $row->cancel_name != NULL ? nl2br(" by: " . $row->cancel_name) : "";
                    $arrayHistory_canceled[$ii] = array(
                        "bgcolor" => "background-color:red;color:#cccccc;",
                    );
                }
                // menambah background-color karena diedit...
                if (sizeof($extHistoryKeterangan) > 0) {
                    foreach ($extHistoryKeterangan as $mode => $modeSpec) {
                        $mode_result = "";
                        if (sizeof($modeSpec) > 0) {
                            if (isset($row->$modeSpec['kolom']) && ($row->$modeSpec['kolom'] == $modeSpec['value'])) {
                                $addBr = $addKeterangan != NULL ? "<hr>" : "";
                                $l_result = "";
                                if (is_array($modeSpec['labels'])) {
                                    foreach ($modeSpec['labels'] as $l) {
                                        if ($l_result == "") {
                                            $l_result = "$addBr $mode by: " . formatField($l, $row->$l);
                                        }
                                        else {
                                            $l_result .= ", " . formatField($l, $row->$l);
                                        }
                                    }
                                }
                                else {
                                    $l_result = $modeSpec['labels'];
                                }
                                $mode_result .= $l_result;

                                //----------------------------------------
                                if (isset($modeSpec['style'])) {
                                    $color = $modeSpec['style']['color'];
                                    $bgcolor = $modeSpec['style']['bgcolor'];
                                    $arrayHistory_keterangan[$ii] = array(
                                        "bgcolor" => "background-color:$bgcolor;color:$color;",
                                    );
                                }
                            }

                        }
                        $addKeterangan .= $mode_result;


                    }
                }


                $tmp['keterangan'] = $addKeterangan;
                $arrayHistory[$ii] = $tmp;
                $arrayHistory_ids[$ii] = $tmp1;


            }
        }
        //endregion


        //region link to add new transaction
        if (placeCanMakeTrans($this->session->login['membership'], $this->session->login['cabang_id'], $this->session->login['gudang_id'], $this->jenisTr)) {
            //        if (in_array($this->configUi[$jenisTr]["steps"][1]['userGroup'], $this->session->login['membership'])) {
            $createIndexes = (null != $this->config->item("transaksi_createIndex")) ? $this->config->item("transaksi_createIndex") : array();
            if (array_key_exists($this->jenisTr, $createIndexes)) {
                $targetUrl = base_url() . $createIndexes[$this->jenisTr] . "/" . $this->jenisTr;
            }
            else {
                $targetUrl = MODUL_PATH . "Create/index/" . $this->jenisTr;
            }

            $addLink = array(
                "link" => $targetUrl,
                "label" => "<span class='glyphicon glyphicon-plus'></span> create new " . $this->configUi[$jenisTr]["steps"][1]['label'],
            );
        }
        else {
            $addLink = null;
        }
        //endregion

        //region prepare params for viewer
        $exportQueryParams = array();
        if ($isFakturFilterActive) {
            if ($fakturM !== '') {
                $exportQueryParams['faktur_m'] = $fakturM;
            }
            if ($fakturY !== '') {
                $exportQueryParams['faktur_y'] = $fakturY;
            }
        }
        else {
            $exportQueryParams['date1'] = $date1;
            $exportQueryParams['date2'] = $date2;
        }
        if (isset($_GET['search']) && strlen((string)$_GET['search']) > 0) {
            $exportQueryParams['search'] = (string)$_GET['search'];
        }
        if (isset($_GET['gr']) && strlen((string)$_GET['gr']) > 0) {
            $exportQueryParams['gr'] = (string)$_GET['gr'];
        }
        if (isset($_GET['addParams']) && strlen((string)$_GET['addParams']) > 0) {
            $exportQueryParams['addParams'] = (string)$_GET['addParams'];
        }
        $customButtonTargetParam = http_build_query($exportQueryParams);
        $customButtonTarget = isset($currentState) ? ($currentState . (strlen($customButtonTargetParam) > 0 ? "?" . $customButtonTargetParam : "")) : "";

        $data = array(
            "mode" => $this->uri->segment(3),
            //            "mode" => "viewHistory",
            "isMobile" => $isMob,
            "jenisTr" => $jenisTr,
            "trName" => $this->configUi[$jenisTr]["label"],
            "errMsg" => $this->session->errMsg,
            "title" => isset($subCodes) && isset($currentState) ? $subCodes[$currentState] : $this->jenisTrName,
            "subTitle" => "histories",
            "swalScript" => $swalScript,
            "alertMessage" => $alertMessage,
            "customButton" => $customButton,
            "customButtonTarget" => $customButtonTarget,
            "arrayHistoryLabels" => $historyFields,
            "arrayHistory" => $arrayHistory,
            "arrayHistorySumField" => $sumValue,
            "arrayHistoryId" => $arrayHistory_ids,
            "action" => $action,
            "steps" => $steps,
            "stepLabels" => $stepLabels,
            "stepLinks" => $stepLinks,
            "addParams" => isset($_GET['addParams']) ? $_GET['addParams'] : null,
            "currentState" => isset($currentState) ? $currentState : "all states",
            "alternateLink" => MODUL_PATH . "/Transaksi/viewIncomplete/" . $this->uri->segment(3),
            "alternateLinkCaption" => "incomplete " . $this->configUi[$jenisTr]["label"] . " <span class='glyphicon glyphicon-arrow-right'></span>",
            "addLink" => $addLink,
            "filters" => array(
                "dates" => $this->dates,
                "date1" => $date1,
                "date2" => $date2,
            ),
            "faktur_m" => $fakturM,
            "faktur_y" => $fakturY,
            "is_faktur_active" => $isFakturFilterActive,
            "thisPage" => MODUL_PATH . get_class($this) . "/" . $this->uri->segment(3) . "/" . $this->uri->segment(4) . "/" . $this->uri->segment(5),
            // "thisPage"             => MODUL_PATH . get_class($this) . "/" . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->uri->segment(4) ,

            "bgTransaksiColor" => $this->bgTransaksiColor,
            "history_canceled" => isset($arrayHistory_canceled) ? $arrayHistory_canceled : array(),
            "history_keterangan" => isset($arrayHistory_keterangan) ? $arrayHistory_keterangan : array(),
            "limit" => $limit,
            "jmlData" => $jmlData,
        );
        //endregion
        $endtime = microtime(true); // Bottom of page
        $valTimeEnd = $endtime - $starttime;
        //        cekBiru("load time start $starttime ||  end $endtime =>" . "$valTimeEnd");

        $this->load->view("history", $data);
    }

    public function showData()
    {
        if (!isset($this->session->login['id'])) {
            gotoLogin();
        }
        $starttime = microtime(true);
        $limit = (isset($_GET['limit']) && is_numeric($_GET['limit']) && intval($_GET['limit']) > 0) ? intval($_GET['limit']) : 20;
        $maxPageNum = 20;
        $jenisTr = $this->jenisTr;
        $jenisTrsub = $this->uri->segment(5);
        $cCode = $this->cCode;
        $allStep = $this->configUi[$jenisTr]['steps'];
        $availSteps = array();
        foreach ($allStep as $step => $tempStep) {
            $availSteps[$tempStep['target']] = $step;
        }
        $selectedSTep = isset($availSteps[$jenisTrsub]) ? $availSteps[$jenisTrsub] : 1;
        $historyFields = isset($this->configUi[$jenisTr]['historyFields'][$selectedSTep]) ? $this->configUi[$jenisTr]['historyFields'][$selectedSTep] : $this->configUi[$jenisTr]['shortHistoryFields'];
        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
        $historyFieldsExt = isset($this->configUi[$jenisTr]["extHistoryFields"][$selectedSTep]) ? $this->configUi[$jenisTr]["extHistoryFields"][$selectedSTep] : array();
        $extHistoryFields2 = isset($this->configUi[$jenisTr]["extHistoryFields2"][$selectedSTep]) ? $this->configUi[$jenisTr]["extHistoryFields2"][$selectedSTep] : array();
        $customButton = isset($this->configLayout[$jenisTr]["customButton"][$selectedSTep]) ? $this->configLayout[$jenisTr]["customButton"][$selectedSTep] : array();
        $printValas = isset($this->configLayout[$jenisTr]["print_nvalas"]) ? $this->configLayout[$jenisTr]["print_nvalas"] : array();
        $pairTransaksi = isset($this->configUi[$jenisTr]['pairTransaksi']) ? $this->configUi[$jenisTr]['pairTransaksi'] : array();
        $extHistoryKeterangan = isset($this->configUi[$jenisTr]['extHistoryKeterangan'][$selectedSTep]) ? $this->configUi[$jenisTr]['extHistoryKeterangan'][$selectedSTep] : array();
        $modul = isset($this->masterConfigUi[$jenisTr]["modul"]) ? $this->masterConfigUi[$jenisTr]["modul"] : false;
        $modul_path = base_url() . $modul . "/";
        //$this->debugArrPrint($extHistoryKeterangan);
//        $this->debugArrPrint($historyFields);

        $mb = New MobileDetect();
        $isMob = $mb->isMobile();
        if ($isMob) {
            $historyFields = isset($this->configUi[$jenisTr]['compactHistoryFields']) ? $this->configUi[$jenisTr]['compactHistoryFields'] : array();
        }

        $backdate_f = formatTanggal(backDate(30), 'Y-m-d');

        $date1 = $this->getSafeDateParam('date1', $backdate_f);
        $date2 = $this->getSafeDateParam('date2', date("Y-m-d"));

        // START OF COMPLETE REPEATED LOGIC
        $allowFakturFilter = false;
        if (isset($this->configUi[$jenisTr]['filterTanggalFaktur'])) {
            $allowFakturFilter = (bool)$this->configUi[$jenisTr]['filterTanggalFaktur'];
        }
        elseif (isset($selectedSTep) && isset($this->configUi[$jenisTr]['steps'][$selectedSTep]['filterTanggalFaktur'])) {
            $allowFakturFilter = (bool)$this->configUi[$jenisTr]['steps'][$selectedSTep]['filterTanggalFaktur'];
        }
        else {
            $allowFakturFilter = ($jenisTr == "110" || (isset($this->configUi[$jenisTr]['shortHistoryFields']) && isset($this->configUi[$jenisTr]['shortHistoryFields']['dateFaktur'])));
        }

        $fakturM = isset($_GET['faktur_m']) && preg_match('/^(0[1-9]|1[0-2])$/', (string)$_GET['faktur_m']) ? (string)$_GET['faktur_m'] : '';
        $fakturY = isset($_GET['faktur_y']) && preg_match('/^\d{4}$/', (string)$_GET['faktur_y']) ? (string)$_GET['faktur_y'] : '';

        $isFakturFilterActive = false;
        $startDateFaktur = "";
        $endDateFaktur = "";
        if ($allowFakturFilter && $fakturY !== '') {
            if ($fakturM !== '') {
                $isFakturFilterActive = true;
                $startDateFaktur = $fakturY . "-" . $fakturM . "-01 00:00:00";
                $lastDay = date("t", strtotime($fakturY . "-" . $fakturM . "-01"));
                $endDateFaktur = $fakturY . "-" . $fakturM . "-" . $lastDay . " 23:59:59";
            }
            elseif (isset($_GET['faktur_m']) || isset($_GET['faktur_y'])) {
                $isFakturFilterActive = true;
                $startDateFaktur = $fakturY . "-01-01 00:00:00";
                $endDateFaktur = $fakturY . "-12-31 23:59:59";
            }
        }

        // cekHere($date1." ******* ".$date2);

        //region preparing ERP step labels for top link
        $steps = $this->configUi[$jenisTr]['steps'];
        $stepLabels = array();
        $stepLinks = array();
        if (sizeof($steps) > 1) {
            $subCodes = array();
            $stepCodes = array();
            $jmlStep = count($steps);

            foreach ($steps as $stepNumber => $stepSpec) {
                if ($stepNumber <= $jmlStep) {
                    $subCodes[$stepSpec['target']] = $stepSpec['label'];
                    $stepCodes[] = $stepSpec['target'];
                    $stepLabels[$stepNumber] = $stepSpec['label'];
                    $limitParam = isset($_GET['limit']) ? "&limit=" . intval($_GET['limit']) : "";
                    $searchForm = isset($_GET['search']) ? "&search=" . $_GET['search'] : "";
                    $grParam = isset($_GET['gr']) ? "&gr=" . $_GET['gr'] : "";
                    if ($isFakturFilterActive) {
                        $stepLinks[$stepNumber] = MODUL_PATH . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->uri->segment(4) . "/" . $stepSpec['target'] . "?faktur_m=$fakturM&faktur_y=$fakturY$limitParam$searchForm$grParam";
                    }
                    else {
                        $stepLinks[$stepNumber] = MODUL_PATH . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->uri->segment(4) . "/" . $stepSpec['target'] . "?date1=$date1&date2=$date2$limitParam$searchForm$grParam";
                    }
                }
            }
// END OF COMPLETE REPEATED LOGIC
            if (isset($_GET['stID'])) {
                $currentState = $_GET['stID'];
            }
            else {
                if ($jenisTr == "110" && isset($this->configUi[$jenisTr]['steps'][1]['target']) && $this->configUi[$jenisTr]['steps'][1]['target'] == "110r") {
                    $defaultTarget = isset($steps[3]['target']) ? $steps[3]['target'] : (isset($steps[2]['target']) ? $steps[2]['target'] : (isset($steps[1]['target']) ? $steps[1]['target'] : ""));
                }
                else {
                    $defaultTarget = isset($this->configUi[$jenisTr]['steps'][1]['target']) ? $this->configUi[$jenisTr]['steps'][1]['target'] : "";
                }
                $currentState = strlen($this->uri->segment(5)) > 0 ? $this->uri->segment(5) : $defaultTarget;
            }

            if (isset($availSteps[$currentState])) {
                $selectedSTep = $availSteps[$currentState];
                $historyFields = isset($this->configUi[$jenisTr]['historyFields'][$selectedSTep]) ? $this->configUi[$jenisTr]['historyFields'][$selectedSTep] : $this->configUi[$jenisTr]['shortHistoryFields'];
                $historyFieldsExt = isset($this->configUi[$jenisTr]["extHistoryFields"][$selectedSTep]) ? $this->configUi[$jenisTr]["extHistoryFields"][$selectedSTep] : array();
                $extHistoryFields2 = isset($this->configUi[$jenisTr]["extHistoryFields2"][$selectedSTep]) ? $this->configUi[$jenisTr]["extHistoryFields2"][$selectedSTep] : array();
                $customButton = isset($this->configLayout[$jenisTr]["customButton"][$selectedSTep]) ? $this->configLayout[$jenisTr]["customButton"][$selectedSTep] : array();
                $extHistoryKeterangan = isset($this->configUi[$jenisTr]['extHistoryKeterangan'][$selectedSTep]) ? $this->configUi[$jenisTr]['extHistoryKeterangan'][$selectedSTep] : array();
            }
        }
// END OF COMPLETE REPEATED LOGIC
        //endregion


        //region lookup histories

        $this->load->model("MdlTransaksi");

        $tr = new MdlTransaksi();

        // START OF COMPLETE REPEATED LOGIC
        $searchStr = isset($_GET['search']) ? $_GET['search'] : "";
        $filters = array(
            "link_id" => "0",
        );
        if ($this->session->login['employee_type'] == "employee_freelance") {
            $filters["seller_id"] = $this->session->login['id'];
        }
        if (isset($filters) && (sizeof($filters) > 0)) {
            foreach ($filters as $key_f => $val_f) {
                $tr->addFilter("$key_f='" . $val_f . "'");
            }
        }
        // START OF COMPLETE REPEATED LOGIC
        if (isset($currentState)) {
            $tr->addFilter("jenis='" . $currentState . "'");
        }
        // END OF COMPLETE REPEATED LOGIC
        // END OF COMPLETE REPEATED LOGIC

        $addParams = $this->decodeSafeBase64ArrayParam('addParams');
        if ($addParams != null && sizeof($addParams) > 0) {
            $this->addSafeFilters($tr, $addParams);
        }


        // START OF COMPLETE REPEATED LOGIC
        if (isset($_GET['search']) && $_GET['search'] !== "") {
            $tr->setKeyWord($searchStr);
            if ($isFakturFilterActive) {
                $this->db->where("efaktur_dtime >=", $startDateFaktur);
                $this->db->where("efaktur_dtime <=", $endDateFaktur);
            }
        }
        else {
            if ($isFakturFilterActive) {
                $this->db->where("efaktur_dtime >=", $startDateFaktur);
                $this->db->where("efaktur_dtime <=", $endDateFaktur);
            }
            else {
//            $this->db->where("fulldate>='" . $date1 . "'");
//            $this->db->where("fulldate<='" . $date2 . "'");
            }
        }

        $isSimpleIndexLoad = (!isset($_GET['search']) && !isset($_GET['page']) && !isset($_GET['addParams']) && isset($_GET['limit']) && !$isFakturFilterActive);
        if ($isSimpleIndexLoad) {
            $jmlData = $limit;
            $page = 1;
        }
        else {
            $jmlData = $tr->lookupHistoryCount();
            $page = (isset($_GET['page']) && $_GET['page'] > 0) ? ($_GET['page']) : 1;
        }
        $offset = ($limit * ($page - 1));

        $addParams = $this->decodeSafeBase64ArrayParam('addParams');
        if ($addParams != null && sizeof($addParams) > 0) {
            $this->addSafeFilters($tr, $addParams);
        }

        $action = array(
            "viewDetails" => base_url() . get_class($this) . "/viewDetails",
        );

        // START OF COMPLETE REPEATED LOGIC
        if (isset($_GET['search']) && $_GET['search'] !== "") {
            $tr->setKeyWord($searchStr);
            if ($isFakturFilterActive) {
                $this->db->where("efaktur_dtime >=", $startDateFaktur);
                $this->db->where("efaktur_dtime <=", $endDateFaktur);
            }
        }
        else {
            if ($isFakturFilterActive) {
                $this->db->where("efaktur_dtime >=", $startDateFaktur);
                $this->db->where("efaktur_dtime <=", $endDateFaktur);
            }
            else {
//            $this->db->where("fulldate>='" . $date1 . "'");
//            $this->db->where("fulldate<='" . $date2 . "'");
            }
        }
        if ($isFakturFilterActive) {
            $this->db->order_by("efaktur_dtime", "asc");
            $this->db->order_by("id", "asc");
        }
        // END OF COMPLETE REPEATED LOGIC
        $tmpHist = $tr->lookupHistories($jmlData, $limit, $page)->result();

        // $this->debugArrPrint($tmpHist);
        //matiHEre();
        $arrCurrency = array();
        if (sizeof($printValas) > 0) {
            $this->load->model("Mdls/MdlCurrency");
            $trv = new MdlCurrency();
            $tmpCurrency = $trv->lookupAll()->result();
            if (sizeof($tmpCurrency) > 0) {
                foreach ($tmpCurrency as $key => $value) {
                    $arrCurrency[$key] = $value;
                }
            }
        }

        $cabang_id = $this->placeId;

        $arrayHistory = array();
        $arrayHistory_ids = array();
        $arrayHistory_canceled = array();
        $sumValue = array();
        if (sizeof($tmpHist) > 0) {
            if (sizeof($pairRegistries) > 0) {
                $arrSalesName = array();
                $arrCustByNomerTop = array();
                $arrTransID = array();
                $arrTransTopID = array();
                $arrNomerTop = array();
                $arrIndexID = array();
                $arrIdsHist = array();
                $arrTransHist = array();
                $arrTransMainHist = array();
                foreach ($tmpHist as $row) {
                    $arrTransID[] = $row->id;
                    $arrTransTopID[] = $row->id_top;
                    if (isset($row->nomer_top) && $row->nomer_top != "") {
                        $arrNomerTop[] = $row->nomer_top;
                    }

                    if ($row->ids_his != "") {
                        $hist = blobDecode($row->ids_his);
                        foreach ($hist as $hisSpec) {
                            $arrIdsHist[$row->id][$hisSpec['step']] = array(
                                "step" => $hisSpec['step'],
                                "trID" => $hisSpec['trID'],
                                "nomer" => $hisSpec['nomer'],
                            );
                            $arrTransHist[] = $hisSpec['trID'];
                        }
                    }
                }

                $tmpReg_result = array();
                $trReg = new MdlTransaksi();
                $trReg->setFilters(array());

                // foreach ($pairRegistries as $param) {
                // $trReg->setParam("transaksi_id");
                // $trReg->setInParam($arrTransID);
                // $trReg->setFilters(array("param" => $param));
                // $this->debugArrPrint($arrTransID);
                // matiHere();
                if (sizeof($pairRegistries) > 0) {
                    $selectKolom = implode(",", $pairRegistries) . ",transaksi_id";
                    // matiHEre(__LINE__);
                    $trReg->setJointSelectFields($selectKolom);
                    $trReg->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
                    $tmpReg = $trReg->lookupDataRegistries()->result();
                    // ceklIme($this->db->last_query());
                    // matiHEre();

                    //                                        $this->debugArrPrint($tmpReg);
                    if (sizeof($tmpReg) > 0) {
                        foreach ($tmpReg as $regRow) {
                            foreach ($pairRegistries as $param) {
                                $tmpReg_result[$regRow->transaksi_id][$param] = blobDecode($regRow->$param);
                            }
                            // $tmpReg_result[$regRow->transaksi_id][$regRow->param] = blobDecode($regRow->values);
                        }
                    }
                }

                // }

                $tmpTrTop = array();
                if (sizeof($arrTransTopID) > 0) {
                    $tr->setFilters(array());
                    $tr = new MdlTransaksi();
                    $tr->addFilter("id in ('" . implode("','", $arrTransTopID) . "')");
                    $tmpTrTop = $tr->lookUpMainTransaksi()->result();
                }
                if (sizeof($tmpTrTop) > 0) {
                    foreach ($tmpTrTop as $topSpec) {
                        $arrSalesName[$topSpec->id_top] = $topSpec->oleh_nama;
                    }
                }

                if (sizeof($arrNomerTop) > 0) {
                    $trCust = new MdlTransaksi();
                    $trCust->setFilters(array());
                    $trCust->addFilter("nomer in ('" . implode("','", array_unique($arrNomerTop)) . "')");
                    $tmpCustTop = $trCust->lookUpMainTransaksi()->result();
                    if (sizeof($tmpCustTop) > 0) {
                        foreach ($tmpCustTop as $custSpec) {
                            $arrCustByNomerTop[$custSpec->nomer] = $custSpec->customers_nama;
                        }
                    }
                }

                $arrCustByTransID = array();
                if (sizeof($arrTransID) > 0) {
                    $this->db->select("transaksi_id, sub_pihak_nama");
                    $this->db->where_in("transaksi_id", $arrTransID);
                    $this->db->where("trash", "0");
                    $this->db->where("sub_pihak_nama !=", "");
                    $tmpDetails = $this->db->get("transaksi_data")->result();
                    if (sizeof($tmpDetails) > 0) {
                        foreach ($tmpDetails as $dSpec) {
                            $cName = trim($dSpec->sub_pihak_nama);
                            if ($cName !== "") {
                                $arrCustByTransID[$dSpec->transaksi_id][$cName] = $cName;
                            }
                        }
                    }
                }

                if (sizeof($arrIdsHist) > 0) {
                    $tmpTransHist = array();
                    if (sizeof($arrTransHist) > 0) {
                        $tr->setFilters(array());
                        $tr = new MdlTransaksi();
                        $tr->addFilter("id in ('" . implode("','", $arrTransHist) . "')");
                        $tmpTransHist = $tr->lookUpMainTransaksi()->result();
                    }

                    //                    $tr = new MdlTransaksi();
                    //                    $tr->setFilters(array());
                    //                    $tr->addFilter("id in ('" . implode("','", $arrTransHist) . "')");
                    //                    $tmpTransHist = $tr->lookupAll()->result();

                    $tmpTransHist_result = array();
                    if (sizeof($tmpTransHist) > 0) {
                        foreach ($tmpTransHist as $histSpec) {
                            $tmpTransHist_result[$histSpec->id] = array(
                                "oleh_id" => $histSpec->oleh_id,
                                "oleh_nama" => $histSpec->oleh_nama,
                            );
                        }
                    }
                    //                    $this->debugArrPrint($tmpTransHist_result);
                    //                    matiHEre();
                    if (sizeof($arrIdsHist) > 0) {
                        foreach ($arrIdsHist as $trID => $histSpec) {
                            foreach ($histSpec as $step => $detailSpec) {
                                if (array_key_exists($detailSpec['trID'], $tmpTransHist_result)) {
                                    $detailSpec['main'] = $tmpTransHist_result[$detailSpec['trID']];
                                }
                                $arrTransMainHist[$trID][$step] = $detailSpec;
                            }
                        }
                    }

                }

            }


            $numb = 0;
            foreach ($tmpHist as $ii => $row) {
                $step_number = $row->step_number;
                $this->placeId = $cabang_id = $row->cabang_id;
                // region ids_his
                $id_hist = blobDecode($row->ids_his);
                // endregion ids_his

                //region memangil global counter
                $tNomer_top = $row->nomer_top;
                //                $tr = new MdlTransaksi();
                //                $tr->addFilter("param='main'");
                //                $tmpReg = $tr->lookupRegistriesByNumber($tNomer_top)->result();
                //
                //                $arrSalesName = "";
                //                foreach ($tmpReg as $tmpRowReg) {
                //                    $arrSalesName = $tmpRowReg->oleh_nama;
                //                }
                $salesName = isset($arrSalesName[$row->id_top]) ? $arrSalesName[$row->id_top] : "-";


                $tNomer = $row->nomer;
                $jenisTrtop = explode(".", $tNomer_top)[0];
                $jenisTrsub = explode(".", $tNomer)[0];
                $counterjenis = "$jenisTrsub|" . $this->placeId;
                // $counterjenis = my_cabang_id()=="-1"? "$jenisTrsub":"$jenisTrsub|" . $this->placeId;

                // matiHEre($jenisTrsub);
                $rawIdsHis = blobDecode($row->ids_his);
                $counterIds_his = array();
                if (is_array($rawIdsHis)) {
                    if (isset($rawIdsHis[1]['counters'])) {
                        $counterIds_his = blobDecode($rawIdsHis[1]['counters']);
                    } else {
                        foreach ($rawIdsHis as $stepItem) {
                            if (is_array($stepItem) && isset($stepItem['counters'])) {
                                $counterIds_his = blobDecode($stepItem['counters']);
                                break;
                            }
                        }
                    }
                }
                $counters = blobDecode($row->counters);
                // $this->debugArrPrint($counters);
                // matiHere();

                $counterGlobal = (is_array($counters) && isset($counters['stepCode|placeID'][$counterjenis])) ? $counters['stepCode|placeID'][$counterjenis] : "";


                $counterIds_his_global = (is_array($counterIds_his) && isset($counterIds_his['stepCode|placeID']["$jenisTrtop|$cabang_id"])) ? $counterIds_his['stepCode|placeID']["$jenisTrtop|$cabang_id"] : "";
                $cGlobals = digit_5($counterGlobal);
                $cGlobal_spo = digit_5($counterIds_his_global);

                //endregion

                if (sizeof($pairRegistries) > 0) {
                    if ((sizeof($tmpReg_result) > 0) && (isset($tmpReg_result[$row->id]))) {
                        foreach ($tmpReg_result[$row->id] as $param => $eReg) {
                            if ($param == "main") {
                                foreach ($eReg as $k => $v) {
                                    if (!isset($row->$k)) {
//                                    if (!isset($row->$k) || ($row->$k == 0)) {
                                        $row->$k = $v;
                                    }
                                }
                            }
                            else {
                                if (sizeof($extHistoryFields2) > 0) {
                                    foreach ($extHistoryFields2 as $k1 => $v1) {
                                        if (is_array($v1)) {
                                            $kolom = $v1['kolom'];
                                            $format = $v1['format'];
                                            //                                                cekHitam(":: $kolom :: $format ::");
                                            if (!isset($row->$k1)) {
                                                $tmpDetail = "";
                                                foreach ($eReg as $eeReg) {
                                                    $valDetail = formatField_he_format($format, $eeReg[$kolom]);
                                                    $tmpDetail .= "<span>$valDetail</span><br>";
                                                }
                                                $row->$k1 = $tmpDetail;
                                            }
                                        }
                                        else {

                                            if (!isset($row->$k1)) {
                                                $tmpDetail = "";
                                                foreach ($eReg as $eeReg) {
                                                    $valDetail = formatField_he_format("nomer", $eeReg[$v1]);
                                                    $tmpDetail .= "<span>$valDetail</span><br>";
                                                    //                                                $this->debugArrPrint($eeReg);
                                                }
                                                $row->$k1 = $tmpDetail;
                                            }
                                        }
                                        //                                        else{
                                        //                                            mati_disini("sudah ada $k1");
                                        //                                        }
                                        //
                                    }
                                }
                            }
                        }
                    }
                    if (sizeof($pairTransaksi) > 0) {
                        if ($row->referenceID > 0) {
                            $trPair = new MdlTransaksi();
                            $trPair->addFilter("id='" . $row->referenceID . "'");
                            $trPairTmp = $trPair->lookupMainTransaksi()->result();
                            if (sizeof($trPairTmp) > 0) {
                                $hisTr = isset($trPairTmp[0]->ids_his) ? blobDecode($trPairTmp[0]->ids_his) : array();
                                foreach ($hisTr as $step => $hisTrSpec) {
                                    foreach ($pairTransaksi['kolom'] as $keyPair => $labelPair) {
                                        $keyPairs = $keyPair . "_" . $step;
                                        $row->$keyPairs = isset($hisTrSpec[$labelPair]) ? $hisTrSpec[$labelPair] : "--";
                                    }
                                }
                            }
                        }
                    }
                    $arrItemShow = isset($this->configUi[$jenisTr]["shortItemsFields"]) ? $this->configUi[$jenisTr]["shortItemsFields"] : array();
                    $detail = viewDetailTransaksi($tmpReg_result[$row->id], $arrItemShow, $row->jenis_master);

                    $arrItemShowFaktur = isset($this->configUi[$jenisTr]["shortItemsFaktur"][$step_number]["headers"]) ? $this->configUi[$jenisTr]["shortItemsFaktur"][$step_number]["headers"] : array();
                    $gateItemShowFaktur = isset($this->configUi[$jenisTr]["shortItemsFaktur"][$step_number]["gate"]) ? $this->configUi[$jenisTr]["shortItemsFaktur"][$step_number]["gate"] : NULL;
                    $detail_faktur = viewDetailFaktur($tmpReg_result[$row->id], $arrItemShowFaktur, $row->jenis_master, true, $gateItemShowFaktur);
                }

                //$this->debugArrPrint($row);
                if (sizeof($historyFieldsExt) > 0) {
                    foreach ($historyFieldsExt as $alias => $colom) {
                        $row->$alias = $row->$colom;
                    }
                }
                $tmp = array();
                $tmp1 = array();
                //$this->debugArrPrint($row);
                //                 break;
                $numb++;
                foreach ($historyFields as $fName => $fLabel) {
                    //                    cekHitam($fName);
                    if (strpos($fName, '+') !== false) {//==mengandung penggabungan (+)
                        $chars = explode("+", $fName);
                        $colValue = "";
                        foreach ($chars as $key) {
                            if (is_numeric($row->$key)) {
                                if (!isset($sumValue[$key])) {
                                    $sumValue[$key] = 0;
                                }
                                $sumValue[$key] += $row->$key;
                            }
                            $colValue .= isset($row->$key) ? formatField_he_format($key, $row->$key) . "<br>" : "";
                        }
                        $colValue = rtrim($colValue, "<br>");
                    }
                    else {

                        if (is_numeric(isset($row->$fName) ? $row->$fName : "")) {
                            if (!isset($sumValue[$fName])) {
                                $sumValue[$fName] = 0;
                            }
                            $sumValue[$fName] += $row->$fName;
                        }

                        //region nomer dengan global counter
                        if ($fName == "nomer") {
                            // switch ($jenisTr) {
                            switch ($jenisTrsub) {
                                case "582s":
                                    $kolomValues = $row->$fName . "&#x2011;$cGlobals";
                                    break;
                                // case "582spd":
                                default:
                                    $kolomValue_0s = formatField_he_format($fName, $row->$fName);
                                    $kolomValues = str_replace("</span>", "&#x2011;" . $cGlobals, $kolomValue_0s);
                                    break;
                            }
                        }
                        elseif ($fName == "nomer_top") {
                            $kolomValue_0s = formatField_he_format($fName, $row->$fName);
                            $kolomValues = str_replace("</span>", "&#x2011;" . $cGlobal_spo, $kolomValue_0s);
                        }
                        elseif ($fName == "customers_nama") {
                            $isGunggung = (
                                (isset($row->gunggunganMode) && $row->gunggunganMode == 1) ||
                                (isset($row->gunggungan_mode) && $row->gunggungan_mode == 1) ||
                                (isset($row->efaktur) && $row->efaktur == "GUNGGUNG") ||
                                (isset($row->eFaktur) && $row->eFaktur == "GUNGGUNG")
                            );
                            $custVal = (isset($row->$fName) && $row->$fName !== "" && $row->$fName !== 0 && $row->$fName !== "0") ? $row->$fName : "";
                            if ($custVal === "" && isset($arrCustByTransID[$row->id]) && sizeof($arrCustByTransID[$row->id]) > 0) {
                                $custNames = array_values($arrCustByTransID[$row->id]);
                                if (sizeof($custNames) == 1) {
                                    $custVal = $custNames[0];
                                }
                                else {
                                    $firstCust = $custNames[0];
                                    $remainingCount = sizeof($custNames) - 1;
                                    $allCustStr = htmlspecialchars(implode(", ", $custNames), ENT_QUOTES, 'UTF-8');
                                    $custVal = "<span title=\"$allCustStr\">$firstCust (+$remainingCount lainnya)</span>";
                                }
                            }
                            elseif ($custVal === "" && isset($row->nomer_top) && isset($arrCustByNomerTop[$row->nomer_top])) {
                                $custVal = $arrCustByNomerTop[$row->nomer_top];
                            }
                            if ($custVal === "") {
                                $custVal = "-";
                            }
                            if ($isGunggung) {
                                $kolomValues = "<span class=\"badge bg-yellow\" style=\"font-size:10px;margin-right:4px;\">(G)</span>" . $custVal;
                            }
                            else {
                                $kolomValues = $custVal;
                            }
                            $row->$fName = $kolomValues;
                        }
                        else {
                            //                            cekHitam(";; $fName");
                            $kolomValues = isset($row->$fName) ? formatField_he_format($fName, $row->$fName) : "-";
                        }
                        //endregion

                        // $colValue = isset($row->$fName) ? formatField($fName, $row->$fName) : "";

                        $colValue = isset($row->$fName) ? $kolomValues : "";
                        //                        cekLime("$colValue"." ".$fName);

                    }

                    //                    if ($fName == "ids_his") {
                    if (is_array($fLabel)) {

                        $hisStep = $fLabel['step'];
                        $hisKey = $fLabel['key'];
                        //                        $tNomer = $id_hist[$hisStep][$hisKey];


                        if ($hisKey == "nomer") {
                            $colValue = isset($row->ids_his) ? showHistoriGlobalNumbers($row->ids_his, $hisStep, true, $this->jenisTr) : "";
                        }
                        else {
                            if (isset($fLabel['transaksi_jenis2'][$row->transaksi_jenis2])) {
                                $getKey = $fLabel['transaksi_jenis2'][$row->transaksi_jenis2];
                                //                                cekHere(":: $getKey ::");
                                $colValue = isset($row->$getKey) ? formatField_he_format($getKey, $row->$getKey) : "";
                            }
                            else {

                                $colValue = "-";
                            }
                        }


                        //                            $tr = new MdlTransaksi();
                        //                            $tr->setFilters(array());
                        //                            $tr->addFilter("param='main'");
                        //                            $tmpReg = $tr->lookupRegistriesByNumber($tNomer)->result();
                        ////                            cekHere($this->db->last_query());
                        //
                        //                            $logistic = $tmpReg[0]->oleh_nama;
                        $logistic = "";
                        if (isset($arrTransMainHist[$row->id][$hisStep]['main'])) {
                            $main = $arrTransMainHist[$row->id][$hisStep]['main'];
                            $logistic = $main['oleh_nama'];
                        }
                    }
                    //                    }

                    if ($fName == "no") {
                        $colValue = formatField_he_format($fName, $numb);
                    }

                    //                    cekHere($logistic);
                    $tmp['logistic'] = isset($logistic) && $logistic != null ? $logistic : 'undefined';
                    $tmp['sales_name'] = $salesName;
                    $tmp[$fName] = $colValue;
                    $tmp1["id"] = $row->id;
                }


                if (sizeof($arrCurrency) > 0) {
                    $valas = "";
                    $valas .= "<div class='btn-group'>";
                    $valas .= "<button type='button' class='btn btn-primary dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>";
                    $valas .= "<i class='fa fa-print'></i>";
                    $valas .= "<span class='caret'></span>";
                    $valas .= "</button>";

                    $valas .= "<ul style='background:#cde8ff;' class='dropdown-menu dropdown-menu-right'>";
                    foreach ($arrCurrency as $arrV) {
                        $nama = $arrV->nama;
                        $nomer = $row->nomer;
                        $nilai = number_format($arrV->exchange, 0);
                        // $valas .= " <li class='text-bold'><a class='dropdown-item' href='javascript:void(0);' onclick=\"top.popBig('" . MODUL_PATH . "Printing/viewReceipt/$nomer?type=" . blobEncode($nama) . "&f=" . blobEncode($arrV->exchange) . "')\"> <i class='fa fa-print'></i> in $nama - ($nilai) </a></li>";
                        $valas .= " <li class='text-bold'><a class='dropdown-item' href='javascript:void(0);' onclick=\"top.popBig('" . MODUL_PATH . "Printing/viewReceipt/" . $this->jenisTr . "/$nomer?type=" . blobEncode($nama) . "&f=" . blobEncode($arrV->exchange) . "')\"> <i class='fa fa-print'></i> in $nama - ($nilai) </a></li>";
                    }

                    // $valas .= " <li><a class='btn btn-xs btn-warning' href='javascript:void(0);' onclick=\"top.location.href='" . base_url() . "data/view/Currency'\"> <i class='fa fa-plus'></i> tambah currency </a></li>";
                    $valas .= "</ul>";
                    $valas .= "</div>";
                    $tmp["print_nvalas"] = $valas;
                }

                $tmp['item_fields'] = $detail;
                if ($detail_faktur != NULL) {
                    $tmp['eFaktur'] = $detail_faktur;
                }
                $tmp['next_pic'] = "-";

                // menambah background-color karena dicancel/reject/undo, atau yang dibatalkan
                //                $tmp['keterangan'] = "-";
                $addKeterangan = "";
                if (isset($row->trash_4) && ($row->trash_4 == 1)) {
                    //                    $tmp['keterangan'] = "CANCELED";
                    $addKeterangan = "CANCELED";
                    //                    $addKeterangan .= $row->cancel_dtime != NULL ? "&nl2br; date: " . $row->cancel_dtime : "";
                    //                    $addKeterangan .= $row->cancel_name != NULL ? "&nl2br; by: " . $row->cancel_name : "";
                    $addKeterangan .= $row->cancel_dtime != NULL ? nl2br(" date: " . $row->cancel_dtime) : "";
                    $addKeterangan .= $row->cancel_name != NULL ? nl2br(" by: " . $row->cancel_name) : "";
                    $arrayHistory_canceled[$ii] = array(
                        "bgcolor" => "background-color:red;color:#cccccc;",
                    );
                }
                // menambah background-color karena diedit...
                if (sizeof($extHistoryKeterangan) > 0) {
                    foreach ($extHistoryKeterangan as $mode => $modeSpec) {
                        $mode_result = "";
                        if (sizeof($modeSpec) > 0) {
                            if (isset($row->$modeSpec['kolom']) && ($row->$modeSpec['kolom'] == $modeSpec['value'])) {
                                $addBr = $addKeterangan != NULL ? "<hr>" : "";
                                $l_result = "";
                                if (is_array($modeSpec['labels'])) {
                                    foreach ($modeSpec['labels'] as $l) {
                                        if ($l_result == "") {
                                            $l_result = "$addBr $mode by: " . formatField_he_format($l, $row->$l);
                                        }
                                        else {
                                            $l_result .= ", " . formatField_he_format($l, $row->$l);
                                        }
                                    }
                                }
                                else {
                                    $l_result = $modeSpec['labels'];
                                }
                                $mode_result .= $l_result;

                                //----------------------------------------
                                if (isset($modeSpec['style'])) {
                                    $color = $modeSpec['style']['color'];
                                    $bgcolor = $modeSpec['style']['bgcolor'];
                                    $arrayHistory_keterangan[$ii] = array(
                                        "bgcolor" => "background-color:$bgcolor;color:$color;",
                                    );
                                }
                            }

                        }
                        $addKeterangan .= $mode_result;


                    }
                }


                $tmp['keterangan'] = $addKeterangan;
                $arrayHistory[$ii] = $tmp;
                $arrayHistory_ids[$ii] = $tmp1;


            }
        }
        //endregion


        //region link to add new transaction
        if (placeCanMakeTrans_he_menu($this->session->login['membership'], $this->session->login['cabang_id'], $this->session->login['gudang_id'], $this->jenisTr, $this->configUiJenis)) {
            //        if (in_array($this->configUi[$jenisTr]["steps"][1]['userGroup'], $this->session->login['membership'])) {
            $createIndexes = (null != $this->config->item("transaksi_createIndex")) ? $this->config->item("transaksi_createIndex") : array();
            if (array_key_exists($this->jenisTr, $createIndexes)) {
                $targetUrl = MODUL_PATH . $createIndexes[$this->jenisTr] . "/" . $this->jenisTr;
            }
            else {
                $targetUrl = MODUL_PATH . "Create/index/" . $this->jenisTr;
            }
            $addLink = array(
                "link" => $targetUrl,
                "label" => "<span class='glyphicon glyphicon-plus'></span> create new " . $this->configUi[$jenisTr]["steps"][1]['label'],
            );
        }
        else {
            $addLink = null;
        }
        //endregion

//        $this->debugArrPrint($arrayHistory);
        //region prepare params for viewer
        $exportQueryParams = array();
        if ($isFakturFilterActive) {
            if ($fakturM !== '') {
                $exportQueryParams['faktur_m'] = $fakturM;
            }
            if ($fakturY !== '') {
                $exportQueryParams['faktur_y'] = $fakturY;
            }
        }
        else {
            $exportQueryParams['date1'] = $date1;
            $exportQueryParams['date2'] = $date2;
        }
        if (isset($_GET['search']) && strlen((string)$_GET['search']) > 0) {
            $exportQueryParams['search'] = (string)$_GET['search'];
        }
        if (isset($_GET['gr']) && strlen((string)$_GET['gr']) > 0) {
            $exportQueryParams['gr'] = (string)$_GET['gr'];
        }
        if (isset($_GET['addParams']) && strlen((string)$_GET['addParams']) > 0) {
            $exportQueryParams['addParams'] = (string)$_GET['addParams'];
        }
        $customButtonTargetParam = http_build_query($exportQueryParams);
        $customButtonTarget = isset($currentState) ? ($currentState . (strlen($customButtonTargetParam) > 0 ? "?" . $customButtonTargetParam : "")) : "";

        $data = array(
            "mode" => "showData",
            //            "mode" => "viewHistory",
            "isMobile" => $isMob,
            "jenisTr" => $jenisTr,
            "trName" => $this->configUi[$jenisTr]["label"],
            "errMsg" => $this->session->errMsg,
            "title" => isset($subCodes) && isset($currentState) ? $subCodes[$currentState] : $this->jenisTrName,
            "subTitle" => "histories",
            "customButton" => $customButton,
            "customButtonTarget" => $customButtonTarget,
            "arrayHistoryLabels" => $historyFields,
            "arrayHistory" => $arrayHistory,
            "arrayHistorySumField" => $sumValue,
            "arrayHistoryId" => $arrayHistory_ids,
            "action" => $action,
            "steps" => $steps,
            "stepLabels" => $stepLabels,
            "stepLinks" => $stepLinks,
            "addParams" => isset($_GET['addParams']) ? $_GET['addParams'] : null,
            "currentState" => isset($currentState) ? $currentState : "all states",
            //            "alternateLink" => base_url() . $this->uri->segment(1) . "/viewIncomplete/" . $this->uri->segment(3),
            //            "alternateLinkCaption" => "incomplete " . $this->configUi[$jenisTr]["label"] . " <span class='glyphicon glyphicon-arrow-right'></span>",
            "alternateLink" => "",
            "alternateLinkCaption" => "",
            "addLink" => $addLink,
            "filters" => array(
                "dates" => $this->dates,
                "date1" => $date1,
                "date2" => $date2,
            ),
            "faktur_m" => $fakturM,
            "faktur_y" => $fakturY,
            "is_faktur_active" => $isFakturFilterActive,
            "thisPage" => MODUL_PATH . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->uri->segment(4) . "/" . $this->uri->segment(5) . "/" . $this->uri->segment(6),

            "history_canceled" => isset($arrayHistory_canceled) ? $arrayHistory_canceled : array(),
            "history_keterangan" => isset($arrayHistory_keterangan) ? $arrayHistory_keterangan : array(),
            "limit" => $limit,
            "jmlData" => $jmlData,
        );
        //endregion

        $endtime = microtime(true); // Bottom of page
        $valTimeEnd = $endtime - $starttime;
        //        cekBiru("load time start $starttime ||  end $endtime =>" . "$valTimeEnd");

        $this->load->view("history", $data);
    }

    // START OF COMPLETE REPEATED LOGIC
    public function exportExcel()
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        if (!isset($this->session->login['id'])) {
            gotoLogin();
        }

        $targetParam = $this->uri->segment(4);
        $subParam = $this->uri->segment(5);
        if (strlen((string)$subParam) > 0) {
            $targetParam = $subParam;
        }
        if (isset($_GET['stID']) && strlen((string)$_GET['stID']) > 0) {
            $targetParam = $_GET['stID'];
        }

        $jenisTr = "110";
        $currentState = $targetParam;

        if (isset($this->configUi[$targetParam])) {
            $jenisTr = $targetParam;
            $currentState = $targetParam;
        }
        else {
            foreach ($this->configUi as $kTr => $specTr) {
                if (isset($specTr['steps'])) {
                    foreach ($specTr['steps'] as $sNum => $sSpec) {
                        if (isset($sSpec['target']) && $sSpec['target'] == $targetParam) {
                            $jenisTr = $kTr;
                            $currentState = $targetParam;
                            break 2;
                        }
                    }
                }
            }
        }

        $allStep = isset($this->configUi[$jenisTr]['steps']) ? $this->configUi[$jenisTr]['steps'] : array();
        $availSteps = array();
        foreach ($allStep as $stepNum => $tempStep) {
            $availSteps[$tempStep['target']] = $stepNum;
        }

        if ($jenisTr == "110" && (!isset($currentState) || strlen((string)$currentState) == 0 || $currentState == "110")) {
            $selectedSTep = 3;
            $currentState = "110";
        }
        else {
            $selectedSTep = isset($availSteps[$currentState]) ? $availSteps[$currentState] : 1;
        }

        $historyFields = isset($this->configUi[$jenisTr]['historyFields'][$selectedSTep]) ? $this->configUi[$jenisTr]['historyFields'][$selectedSTep] : $this->configUi[$jenisTr]['shortHistoryFields'];
        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
        $historyFieldsExt = isset($this->configUi[$jenisTr]["extHistoryFields"][$selectedSTep]) ? $this->configUi[$jenisTr]["extHistoryFields"][$selectedSTep] : array();
        $extHistoryFields2 = isset($this->configUi[$jenisTr]["extHistoryFields2"][$selectedSTep]) ? $this->configUi[$jenisTr]["extHistoryFields2"][$selectedSTep] : array();
        $pairTransaksi = isset($this->configUi[$jenisTr]['pairTransaksi']) ? $this->configUi[$jenisTr]['pairTransaksi'] : array();

        $backdate_f = formatTanggal(backDate(30), 'Y-m-d');
        $date1 = $this->getSafeDateParam('date1', $backdate_f);
        $date2 = $this->getSafeDateParam('date2', date("Y-m-d"));

        $allowFakturFilter = false;
        if (isset($this->configUi[$jenisTr]['filterTanggalFaktur'])) {
            $allowFakturFilter = (bool)$this->configUi[$jenisTr]['filterTanggalFaktur'];
        }
        else {
            $allowFakturFilter = ($jenisTr == "110" || (isset($this->configUi[$jenisTr]['shortHistoryFields']) && isset($this->configUi[$jenisTr]['shortHistoryFields']['dateFaktur'])));
        }

        $fakturM = isset($_GET['faktur_m']) && preg_match('/^(0[1-9]|1[0-2])$/', (string)$_GET['faktur_m']) ? (string)$_GET['faktur_m'] : '';
        $fakturY = isset($_GET['faktur_y']) && preg_match('/^\d{4}$/', (string)$_GET['faktur_y']) ? (string)$_GET['faktur_y'] : '';

        $isFakturFilterActive = false;
        $startDateFaktur = "";
        $endDateFaktur = "";
        if ($allowFakturFilter && $fakturY !== '') {
            if ($fakturM !== '') {
                $isFakturFilterActive = true;
                $startDateFaktur = $fakturY . "-" . $fakturM . "-01 00:00:00";
                $lastDay = date("t", strtotime($fakturY . "-" . $fakturM . "-01"));
                $endDateFaktur = $fakturY . "-" . $fakturM . "-" . $lastDay . " 23:59:59";
            }
            elseif (isset($_GET['faktur_m']) || isset($_GET['faktur_y'])) {
                $isFakturFilterActive = true;
                $startDateFaktur = $fakturY . "-01-01 00:00:00";
                $endDateFaktur = $fakturY . "-12-31 23:59:59";
            }
        }

        $this->load->model("MdlTransaksi");
        $tr = new MdlTransaksi();

        $searchStr = isset($_GET['search']) ? $_GET['search'] : "";
        $filters = array(
            "link_id" => "0",
        );
        if ($this->session->login['employee_type'] == "employee_freelance") {
            $filters["seller_id"] = $this->session->login['id'];
        }
        if (isset($filters) && (sizeof($filters) > 0)) {
            foreach ($filters as $key_f => $val_f) {
                $tr->addFilter("$key_f='" . $val_f . "'");
            }
        }

        if (isset($currentState)) {
            $tr->addFilter("jenis='" . $currentState . "'");
        }

        $addParams = $this->decodeSafeBase64ArrayParam('addParams');
        if ($addParams != null && sizeof($addParams) > 0) {
            $this->addSafeFilters($tr, $addParams);
        }

        if (isset($_GET['search']) && $_GET['search'] !== "") {
            $tr->setKeyWord($searchStr);
            if ($isFakturFilterActive) {
                $this->db->where("efaktur_dtime >=", $startDateFaktur);
                $this->db->where("efaktur_dtime <=", $endDateFaktur);
            }
        }
        else {
            if ($isFakturFilterActive) {
                $this->db->where("efaktur_dtime >=", $startDateFaktur);
                $this->db->where("efaktur_dtime <=", $endDateFaktur);
            }
            else {
                $this->db->where("fulldate >=", $date1);
                $this->db->where("fulldate <=", $date2);
            }
        }

        $jmlData = $tr->lookupHistoryCount();
        $maxExportLimit = 10000;
        $exportLimit = ($jmlData > 0 && $jmlData < $maxExportLimit) ? $jmlData : $maxExportLimit;

        if ($addParams != null && sizeof($addParams) > 0) {
            $this->addSafeFilters($tr, $addParams);
        }

        if (isset($_GET['search']) && $_GET['search'] !== "") {
            $tr->setKeyWord($searchStr);
            if ($isFakturFilterActive) {
                $this->db->where("efaktur_dtime >=", $startDateFaktur);
                $this->db->where("efaktur_dtime <=", $endDateFaktur);
            }
        }
        else {
            if ($isFakturFilterActive) {
                $this->db->where("efaktur_dtime >=", $startDateFaktur);
                $this->db->where("efaktur_dtime <=", $endDateFaktur);
            }
            else {
                $this->db->where("fulldate >=", $date1);
                $this->db->where("fulldate <=", $date2);
            }
        }

        if ($isFakturFilterActive) {
            $this->db->order_by("efaktur_dtime", "asc");
            $this->db->order_by("id", "asc");
        }

        $tmpHist = $tr->lookupHistories($exportLimit, $exportLimit, 1)->result();

        $arrSalesName = array();
        $arrCustByNomerTop = array();
        $tmpReg_result = array();
        $arrTransMainHist = array();
        $arrPairTransaksiResult = array();

        if (sizeof($tmpHist) > 0) {
            if (sizeof($pairRegistries) > 0) {
                $arrTransID = array();
                $arrTransTopID = array();
                $arrNomerTop = array();
                $arrIdsHist = array();
                $arrTransHist = array();
                $arrRefID = array();

                foreach ($tmpHist as $row) {
                    $arrTransID[] = $row->id;
                    $arrTransTopID[] = $row->id_top;
                    if (isset($row->nomer_top) && $row->nomer_top != "") {
                        $arrNomerTop[] = $row->nomer_top;
                    }
                    if (isset($row->referenceID) && $row->referenceID > 0) {
                        $arrRefID[] = $row->referenceID;
                    }

                    if ($row->ids_his != "") {
                        $hist = blobDecode($row->ids_his);
                        if (is_array($hist)) {
                            foreach ($hist as $hisSpec) {
                                $arrIdsHist[$row->id][$hisSpec['step']] = array(
                                    "step" => $hisSpec['step'],
                                    "trID" => $hisSpec['trID'],
                                    "nomer" => $hisSpec['nomer'],
                                );
                                $arrTransHist[] = $hisSpec['trID'];
                            }
                        }
                    }
                }

                if (sizeof($pairTransaksi) > 0 && sizeof($arrRefID) > 0) {
                    $trPair = new MdlTransaksi();
                    $trPair->setFilters(array());
                    $trPair->addFilter("id in ('" . implode("','", array_unique($arrRefID)) . "')");
                    $tmpPair = $trPair->lookUpMainTransaksi()->result();
                    if (sizeof($tmpPair) > 0) {
                        foreach ($tmpPair as $pRow) {
                            $arrPairTransaksiResult[$pRow->id] = isset($pRow->ids_his) ? blobDecode($pRow->ids_his) : array();
                        }
                    }
                }

                $trReg = new MdlTransaksi();
                $trReg->setFilters(array());
                $selectKolom = implode(",", $pairRegistries) . ",transaksi_id";
                $trReg->setJointSelectFields($selectKolom);
                $trReg->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
                $tmpReg = $trReg->lookupDataRegistries()->result();
                if (sizeof($tmpReg) > 0) {
                    foreach ($tmpReg as $regRow) {
                        foreach ($pairRegistries as $param) {
                            $tmpReg_result[$regRow->transaksi_id][$param] = blobDecode($regRow->$param);
                        }
                    }
                }

                $trTopMdl = new MdlTransaksi();
                $trTopMdl->setFilters(array());
                $trTopMdl->addFilter("id in ('" . implode("','", $arrTransTopID) . "')");
                $tmpTrTop = $trTopMdl->lookUpMainTransaksi()->result();
                if (sizeof($tmpTrTop) > 0) {
                    foreach ($tmpTrTop as $topSpec) {
                        $arrSalesName[$topSpec->id_top] = $topSpec->oleh_nama;
                    }
                }

                if (sizeof($arrNomerTop) > 0) {
                    $trCust = new MdlTransaksi();
                    $trCust->setFilters(array());
                    $trCust->addFilter("nomer in ('" . implode("','", array_unique($arrNomerTop)) . "')");
                    $tmpCustTop = $trCust->lookUpMainTransaksi()->result();
                    if (sizeof($tmpCustTop) > 0) {
                        foreach ($tmpCustTop as $custSpec) {
                            $arrCustByNomerTop[$custSpec->nomer] = $custSpec->customers_nama;
                        }
                    }
                }

                $arrCustByTransID = array();
                if (sizeof($arrTransID) > 0) {
                    $this->db->select("transaksi_id, sub_pihak_nama");
                    $this->db->where_in("transaksi_id", $arrTransID);
                    $this->db->where("trash", "0");
                    $this->db->where("sub_pihak_nama !=", "");
                    $tmpDetails = $this->db->get("transaksi_data")->result();
                    if (sizeof($tmpDetails) > 0) {
                        foreach ($tmpDetails as $dSpec) {
                            $cName = trim($dSpec->sub_pihak_nama);
                            if ($cName !== "") {
                                $arrCustByTransID[$dSpec->transaksi_id][$cName] = $cName;
                            }
                        }
                    }
                }

                if (sizeof($arrIdsHist) > 0) {
                    $trHistMdl = new MdlTransaksi();
                    $trHistMdl->setFilters(array());
                    $trHistMdl->addFilter("id in ('" . implode("','", $arrTransHist) . "')");
                    $tmpTransHist = $trHistMdl->lookUpMainTransaksi()->result();
                    $tmpTransHist_result = array();
                    if (sizeof($tmpTransHist) > 0) {
                        foreach ($tmpTransHist as $histSpec) {
                            $tmpTransHist_result[$histSpec->id] = array(
                                "oleh_id" => $histSpec->oleh_id,
                                "oleh_nama" => $histSpec->oleh_nama,
                            );
                        }
                    }
                    foreach ($arrIdsHist as $trID => $histSpec) {
                        foreach ($histSpec as $step => $detailSpec) {
                            if (array_key_exists($detailSpec['trID'], $tmpTransHist_result)) {
                                $detailSpec['main'] = $tmpTransHist_result[$detailSpec['trID']];
                            }
                            $arrTransMainHist[$trID][$step] = $detailSpec;
                        }
                    }
                }
            }
        }

        $excelHeaders = array();
        foreach ($historyFields as $fName => $fLabel) {
            if ($fName == "no" || $fName == "review_details" || $fName == "print_label") {
                continue;
            }
            $label = is_array($fLabel) ? (isset($fLabel['label']) ? $fLabel['label'] : $fName) : $fLabel;
            $label = trim(strip_tags((string)$label));

            $type = "string";
            if (in_array($fName, array("harga", "disc", "nett1", "dpp_ppn", "grand_ppn", "new_net3", "tagihan", "projectNilai"))) {
                $type = "integer";
            }

            $excelHeaders[$fName] = array(
                "label" => $label,
                "type" => $type,
            );
        }
        $excelHeaders["status_transaksi"] = array(
            "label" => "Status",
            "type" => "string",
        );

        $datas = array();
        if (sizeof($tmpHist) > 0) {
            foreach ($tmpHist as $row) {
                $cabang_id = $row->cabang_id;
                $salesName = isset($arrSalesName[$row->id_top]) ? $arrSalesName[$row->id_top] : "-";

                $tNomer_top = $row->nomer_top;
                $tNomer = $row->nomer;
                $jenisTrtop = explode(".", $tNomer_top)[0];
                $jenisTrsub = explode(".", $tNomer)[0];
                $counterjenis = "$jenisTrsub|" . $cabang_id;

                $rawIdsHis = blobDecode($row->ids_his);
                $counterIds_his = array();
                if (is_array($rawIdsHis)) {
                    if (isset($rawIdsHis[1]['counters'])) {
                        $counterIds_his = blobDecode($rawIdsHis[1]['counters']);
                    }
                    else {
                        foreach ($rawIdsHis as $stepItem) {
                            if (is_array($stepItem) && isset($stepItem['counters'])) {
                                $counterIds_his = blobDecode($stepItem['counters']);
                                break;
                            }
                        }
                    }
                }
                $counters = blobDecode($row->counters);
                $counterGlobal = (is_array($counters) && isset($counters['stepCode|placeID'][$counterjenis])) ? $counters['stepCode|placeID'][$counterjenis] : "";
                $counterIds_his_global = (is_array($counterIds_his) && isset($counterIds_his['stepCode|placeID']["$jenisTrtop|$cabang_id"])) ? $counterIds_his['stepCode|placeID']["$jenisTrtop|$cabang_id"] : "";
                $cGlobals = digit_5($counterGlobal);
                $cGlobal_spo = digit_5($counterIds_his_global);

                if (sizeof($pairRegistries) > 0) {
                    if ((sizeof($tmpReg_result) > 0) && (isset($tmpReg_result[$row->id]))) {
                        foreach ($tmpReg_result[$row->id] as $param => $eReg) {
                            if ($param == "main") {
                                foreach ($eReg as $k => $v) {
                                    if (!isset($row->$k)) {
                                        $row->$k = $v;
                                    }
                                }
                            }
                            else {
                                if (sizeof($extHistoryFields2) > 0) {
                                    foreach ($extHistoryFields2 as $k1 => $v1) {
                                        if (is_array($v1)) {
                                            $kolom = $v1['kolom'];
                                            $format = $v1['format'];
                                            if (!isset($row->$k1)) {
                                                $tmpDetail = "";
                                                foreach ($eReg as $eeReg) {
                                                    $valDetail = formatField_he_format($format, $eeReg[$kolom]);
                                                    $tmpDetail .= "<span>$valDetail</span><br>";
                                                }
                                                $row->$k1 = $tmpDetail;
                                            }
                                        }
                                        else {
                                            if (!isset($row->$k1)) {
                                                $tmpDetail = "";
                                                foreach ($eReg as $eeReg) {
                                                    $valDetail = formatField_he_format("nomer", $eeReg[$v1]);
                                                    $tmpDetail .= "<span>$valDetail</span><br>";
                                                }
                                                $row->$k1 = $tmpDetail;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if (sizeof($pairTransaksi) > 0) {
                        if ($row->referenceID > 0 && isset($arrPairTransaksiResult[$row->referenceID])) {
                            $hisTr = $arrPairTransaksiResult[$row->referenceID];
                            foreach ($hisTr as $step => $hisTrSpec) {
                                foreach ($pairTransaksi['kolom'] as $keyPair => $labelPair) {
                                    $keyPairs = $keyPair . "_" . $step;
                                    $row->$keyPairs = isset($hisTrSpec[$labelPair]) ? $hisTrSpec[$labelPair] : "--";
                                }
                            }
                        }
                    }
                }

                if (sizeof($historyFieldsExt) > 0) {
                    foreach ($historyFieldsExt as $alias => $colom) {
                        $row->$alias = $row->$colom;
                    }
                }

                $rowObj = array();
                foreach ($excelHeaders as $fName => $hdrSpec) {
                    if ($fName == "status_transaksi") {
                        $rowObj[$fName] = (isset($row->trash_4) && $row->trash_4 == 1) ? "CANCELED" : "ACTIVE";
                        continue;
                    }

                    if ($fName == "nomer") {
                        $kolomValue_0s = formatField_he_format($fName, $row->$fName);
                        $colVal = str_replace("</span>", "&#x2011;" . $cGlobals, $kolomValue_0s);
                    }
                    elseif ($fName == "nomer_top") {
                        $kolomValue_0s = formatField_he_format($fName, $row->$fName);
                        $colVal = str_replace("</span>", "&#x2011;" . $cGlobal_spo, $kolomValue_0s);
                    }
                    elseif ($fName == "customers_nama") {
                        $isGunggung = (
                            (isset($row->gunggunganMode) && $row->gunggunganMode == 1) ||
                            (isset($row->gunggungan_mode) && $row->gunggungan_mode == 1) ||
                            (isset($row->efaktur) && $row->efaktur == "GUNGGUNG") ||
                            (isset($row->eFaktur) && $row->eFaktur == "GUNGGUNG")
                        );
                        $custVal = (isset($row->$fName) && $row->$fName !== "" && $row->$fName !== 0 && $row->$fName !== "0") ? $row->$fName : "";
                        if ($custVal === "" && isset($arrCustByTransID[$row->id]) && sizeof($arrCustByTransID[$row->id]) > 0) {
                            $custNames = array_values($arrCustByTransID[$row->id]);
                            $custVal = implode(", ", $custNames);
                        }
                        elseif ($custVal === "" && isset($row->nomer_top) && isset($arrCustByNomerTop[$row->nomer_top])) {
                            $custVal = $arrCustByNomerTop[$row->nomer_top];
                        }
                        if ($custVal === "") {
                            $custVal = "-";
                        }
                        if ($isGunggung) {
                            $colVal = "(G) " . $custVal;
                        }
                        else {
                            $colVal = $custVal;
                        }
                    }
                    elseif (is_array(isset($historyFields[$fName]) ? $historyFields[$fName] : null)) {
                        $fLabel = $historyFields[$fName];
                        $hisStep = $fLabel['step'];
                        $hisKey = $fLabel['key'];
                        if ($hisKey == "nomer") {
                            $colVal = isset($row->ids_his) ? showHistoriGlobalNumbers($row->ids_his, $hisStep, true, $this->jenisTr) : "";
                        }
                        else {
                            $colVal = isset($row->$fName) ? $row->$fName : "-";
                        }
                    }
                    else {
                        $colVal = isset($row->$fName) ? $row->$fName : "-";
                    }

                    $cleanVal = strip_tags(str_replace(array("<br>", "<br/>", "<br />"), ", ", (string)$colVal));
                    $cleanVal = html_entity_decode($cleanVal, ENT_QUOTES, 'UTF-8');
                    $cleanVal = trim(str_replace(array("\xc2\xa0", "&#x2011;"), array(' ', '-'), $cleanVal));

                    if ($hdrSpec['type'] == "integer") {
                        $numericVal = floatval(preg_replace('/[^0-9.-]/', '', $cleanVal));
                        $rowObj[$fName] = $numericVal;
                    }
                    else {
                        $rowObj[$fName] = $cleanVal;
                    }
                }

                $datas[] = (object)$rowObj;
            }
        }

        $this->load->library('Excel');
        $ex = new Excel();

        $periodSuffix = $isFakturFilterActive ? ("Faktur_" . ($fakturM ? $fakturM . "_" : "") . $fakturY) : ("Periode_" . str_replace("-", "", $date1) . "_" . str_replace("-", "", $date2));
        $titleFile = "E-Faktur_" . $currentState . "_" . $periodSuffix;

        $ex->setTitleFile($titleFile);
        $ex->setHeaders($excelHeaders);
        $ex->setDatas($datas);

        return $ex->writer();
    }
    // END OF COMPLETE REPEATED LOGIC
}


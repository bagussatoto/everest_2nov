<?php


class ToolRepairSusulanPreFakturPpnKeluaran extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->masterConfigUi = $this->config->item("heTransaksi_ui");
        $this->load->helper("he_angka");
    }

    function index()
    {
        $arrTools = array(
            "kas" => "viewUnsyncedKas",
            "produk" => "viewUnsyncedProduk",
            "produk rakitan" => "viewUnsyncedProdukRakitan",
            "supplies" => "viewUnsyncedSupplies",
            "valas" => "viewUnsyncedValas",
        );

//        foreach ($arrTools as $key => $value) {
//            echo "<div>";
//            echo "<h3>";
//            echo "<a href='" . base_url() . get_class($this) . "/$value' target='_blank'>:: $key ::</a>";
//            echo "</h3>";
//            echo "</div>";
//        }
    }

    public function run_susulanPerFakturPpnKeluaran()
    {

        $this->load->model("MdlTransaksi");
        $this->load->model("CustomCounter");
        $this->load->helper("he_mass_table");
        $startDate = dtimeNow();


        $getTrID = (isset($_GET['tr_id']) && ($_GET['tr_id'] > 0)) ? $_GET['tr_id'] : 0;
        $addJudul = "";

        $getTrID = 1027122;

        $tr = New MdlTransaksi();
        $tr->setSortBy(
            array(
                "kolom" => "id",
                "mode" => "ASC",
            )
        );
        $this->db->limit(1);

        // bila ada trID dari URL, maka ini adalah cek manual, tidak boleh close commit !!!
        if ($getTrID > 0) {
            $tr->addFilter("id='$getTrID'");
            $addJudul = "<br>cek manual";
        }
        else {
            $tr->addFilter("cli='0'");
            mati_disini(__LINE__ . " WAJIB tentukan transaksi_id");
        }

        $trTmp = $tr->lookupAll()->result();
        cekHere($this->db->last_query() . "<br>" . sizeof($trTmp));

        if (sizeof($trTmp) > 0) {
            $trID_cli = $trTmp[0]->id;
            $trTmpCabangID = $trTmp[0]->cabang_id;
            $kolom = array(
                "trID" => "id",
                "trID_master" => "id_master",
                "jenisTr" => "jenis",
                "jenisTrMaster" => "jenis_master",
                "jenisTrTop" => "jenis_top",
                "nomer" => "nomer",
                "nomerTop" => "nomer_top",
                "dtime" => "dtime",
                "fulldate" => "fulldate",
                "stepNumber" => "step_number",
                "indexRegistry" => "indexing_registry",
                "olehID" => "oleh_id",
                "olehNama" => "oleh_nama",
            );

            $arrKolomTrans = array();
            foreach ($kolom as $key => $val) {
                $arrKolomTrans[$key] = isset($trTmp[0]->$val) ? $trTmp[0]->$val : NULL;
            }

            $reg = New MdlTransaksi();
            $reg->setFilters(array());
            $reg->addFilter("transaksi_id='" . $trTmp[0]->id . "'");
            $regTmp = $reg->lookupDataRegistries()->result();
            $registryGates = array();
            foreach ($regTmp as $regSpec) {
                foreach ($regSpec as $key_reg => $val_reg) {
                    if ($key_reg != "transaksi_id") {
                        $registryGates[$key_reg] = blobDecode($val_reg);
                    }
                }
            }


            $this->jenisTr = $jenisTrTarget = $jenisTr = $jenis = $arrKolomTrans['jenisTr'];
            $jenisTrMaster = $arrKolomTrans['jenisTrMaster'];
            $fulldate = $arrKolomTrans['fulldate'];
            $dtime = $arrKolomTrans['dtime'];
            $stepNumber = $arrKolomTrans['stepNumber'];
            $insertNum = $tmpNomorNota = $transaksi_no = $arrKolomTrans['nomer'];
            $pelaku_transaksi_id = $olehID = $arrKolomTrans['olehID'];
            $pelaku_transaksi_nama = $olehNama = $arrKolomTrans['olehNama'];
            $insertID = $transaksiID = $transaksi_id = $arrKolomTrans['trID'];
            $masterID = $arrKolomTrans['trID_master'];
            $olehID = $arrKolomTrans['olehID'];
            $olehNama = $arrKolomTrans['olehNama'];
            $stepNumCurrent = 1;
            $stepNum = 2;
            $ppnFactor = $registryGates["main"]["ppnFactor"];
            //---------------------
            $cCode = "_TR_" . $this->jenisTr;
            $_SESSION[$cCode] = array();
            $_SESSION[$cCode] = $registryGates;


            $configCoreMasterModulJenis = $configCoreMasterModulJenisBuilder = $configCore = loadConfigModulJenis_he_misc($jenisTrMaster, "coTransaksiCore");
            $configUiMasterModulJenis = $configUi = loadConfigModulJenis_he_misc($jenisTrMaster, "coTransaksiUi");
            $configLayoutMasterModulJenis = $configLayout = loadConfigModulJenis_he_misc($jenisTrMaster, "coTransaksiLayout");
            $configValuesMasterModulJenis = $configValues = loadConfigModulJenis_he_misc($jenisTrMaster, "coTransaksiValues");


            cekHitam(":: jenisTrMaster-> $jenisTrMaster :: jenisTr-> $jenisTr :: [trID_cli: $trID_cli]");

            $midmaster = $trID_cli;
            $mNumMaster = $insertNum;
            $mJenisMaster = $jenisTrMaster;

            $this->db->trans_start();


            $registryGates = $_SESSION[$cCode];
            $rsltItems_valas = blobEncode($_SESSION[$cCode]["rsltItems"]);


            cekHijau("MULAI BAGIAN KONEK 110 KE DC/PUSAT");

            //region connecting antar cabang
            $stepNum = 4;
            $origJenis = $jenisTrMaster;
//            $steps = isset($this->configUi[$origJenis]['steps']) ? $this->configUi[$origJenis]['steps'] : array();
//            $connector = isset($this->configUi[$origJenis]['connectTo']) ? $this->configUi[$origJenis]['connectTo'] : "";
//            $preReplacer = isset($this->configUi[$jenisTrMaster]['replacerConnectTo']) ? $this->configUi[$jenisTrMaster]['replacerConnectTo'] : array();
//            $replacerConnectToItems = isset($this->configUi[$jenisTrMaster]['replacerConnectToItems']) ? $this->configUi[$jenisTrMaster]['replacerConnectToItems'] : array();
//            $validateValueConnector = isset($this->configUi[$jenisTrMaster]['connectoValidate'][$stepNum]) ? $this->configUi[$jenisTrMaster]['connectoValidate'][$stepNum] : array();
            $steps = isset($configUiMasterModulJenis['steps']) ? $configUiMasterModulJenis['steps'] : array();
            $connector = isset($configUiMasterModulJenis['connectTo']) ? $configUiMasterModulJenis['connectTo'] : "";
            $preReplacer = isset($configUiMasterModulJenis['replacerConnectTo']) ? $configUiMasterModulJenis['replacerConnectTo'] : array();
            $replacerConnectToItems = isset($configUiMasterModulJenis['replacerConnectToItems']) ? $configUiMasterModulJenis['replacerConnectToItems'] : array();
            $validateValueConnector = isset($configUiMasterModulJenis['connectoValidate'][$stepNum]) ? $configUiMasterModulJenis['connectoValidate'][$stepNum] : array();

            $mongoListConnect = array();
            $mongRegIDConnect = array();

            if (strlen($connector) > 0) {
                cekMerah("CONNEC TO $connector |$stepNum|" . sizeof($steps));
//                $masterID = $getTrID;
                $configUiMasterModulJenis = loadConfigModulJenis_he_misc($connector, "coTransaksiUi");
                $configCoreMasterModulJenis = loadConfigModulJenis_he_misc($connector, "coTransaksiCore");
                $configLayoutMasterModulJenis = loadConfigModulJenis_he_misc($connector, "coTransaksiLayout");
                $tCodeTargetJenisTransaksi = $configUiMasterModulJenis['steps'][1]['target'];
                $modul_transaksi = $this->config->item("heTransaksi_ui")[$connector]["modul"];
                if (isset($this->configUi[$jenisTrMaster]['connectoValidate'][$stepNum])) {
                    $validateValueConnector = $this->configUi[$jenisTrMaster]['connectoValidate'][$stepNum];
                    $preVal = $_SESSION[$cCode]['main'][$validateValueConnector];
                    $stepNum = $preVal > 0 ? $stepNum : "1000";//1000 untuk nglewatin step biar gak jalan connectingnya karena nilai yang dicari 0 kasusnya cash in advance ppn sudah masuk pusat tidak perlu diterbitkan auto dorong ppn ke pusat
                }

                if ($stepNum == sizeof($steps)) {
                    cekMerah("NOW CONNECTING to $connector");

                    if (sizeof($configCoreMasterModulJenis) < 1) {
                        die("konfigurasi connector harus memiliki step lebih dari satu!");
                    }

                    $oldCode = $cCode;
                    $cCode = "_TR_" . $connector;

                    he_clone_transaction_session($oldCode, $cCode);
                    //==replace pertama
                    $masterReplacersO = array(
                        "jenisTr" => $connector,
                        "jenisTrMaster" => $connector,
                        "jenisTrTop" => $configUiMasterModulJenis['steps'][1]['target'],
                        "jenis" => $configUiMasterModulJenis['steps'][1]['target'],
                        "jenis_label" => $configUiMasterModulJenis['steps'][1]['label'],
                        "transaksi_jenis" => $configUiMasterModulJenis['steps'][1]['target'],
                        "stepCode" => $configUiMasterModulJenis['steps'][1]['target'],
                        "placeID" => isset($preReplacer['place2ID']) ? $preReplacer['place2ID'] : $_SESSION[$cCode]['main']['place2ID'],
                        "placeName" => isset($preReplacer['place2Name']) ? $preReplacer['place2Name'] : $_SESSION[$cCode]['main']['place2Name'],
                        "place2ID" => $_SESSION[$cCode]['main']['placeID'],
                        "place2Name" => $_SESSION[$cCode]['main']['placeName'],
                        "cabangID" => isset($preReplacer['cabang2ID']) ? $preReplacer['cabang2ID'] : $_SESSION[$cCode]['main']['place2ID'],
                        "cabangName" => isset($preReplacer['place2Name']) ? $preReplacer['place2Name'] : $_SESSION[$cCode]['main']['place2Name'],
                        "cabang2ID" => $_SESSION[$cCode]['main']['placeID'],
                        "cabang2Name" => $_SESSION[$cCode]['main']['placeName'],
                        //
                        "gudang2ID" => $_SESSION[$cCode]['main']['gudangID'],
                        "gudang2Name" => $_SESSION[$cCode]['main']['gudangName'],
                        "gudangID" => isset($preReplacer['gudang2ID']) ? $preReplacer['gudang2ID'] : $_SESSION[$cCode]['main']['gudang2ID'],
                        "gudangName" => isset($preReplacer['gudang2Name']) ? $preReplacer['gudang2Name'] : $_SESSION[$cCode]['main']['gudang2Name'],
                        "pihakID" => isset($_SESSION[$cCode]['main']['placeID']) ? $_SESSION[$cCode]['main']['placeID'] : "",
                        "pihakName" => isset($_SESSION[$cCode]['main']['placeName']) ? $_SESSION[$cCode]['main']['placeName'] : "",
                        "pihakName2" => $_SESSION[$cCode]['main']['placeName'],
                        "gudang" => $_SESSION[$cCode]['main']['gudangID'],
                        "gudang__name" => $_SESSION[$cCode]['main']['gudangName'],
                        "gudang__label" => $_SESSION[$cCode]['main']['gudangName'],
                        "efaktur_source" => isset($preReplacer['efaktur_source']) ? $_SESSION[$cCode]['main']['nomer'] : "",
                        "referensi_id" => $midmaster,
                        "referensi_nomer" => $mNumMaster,
                        "referensi_jenis" => $mJenisMaster,
                        "reference_id" => $midmaster,
                        "reference_nomer" => $mNumMaster,
                        "reference_jenis" => $mJenisMaster,

                    );
                    foreach ($masterReplacersO as $key => $val) {
                        $_SESSION[$cCode]['main'][$key] = $val;
                        //                    $_SESSION[$cCode]['main'][$key] = $val;
                    }
                    $masterReplacers = array(
                        //                    "referensi_id" => $masterID, (dimatikan)
                        "inv" => $tmpNomorNota,
                        "jenis_master" => $connector,
                        "jenis_top" => $configUiMasterModulJenis['steps'][1]['target'],
                        "jenis" => $configUiMasterModulJenis['steps'][1]['target'],
                        "jenis_label" => $configUiMasterModulJenis['steps'][1]['label'],
                        "transaksi_jenis" => $configUiMasterModulJenis['steps'][1]['target'],
                        "cabang_id" => isset($preReplacer['cabang2ID']) ? $preReplacer['cabang2ID'] : $_SESSION[$cCode]['tableIn_master']['cabang2_id'],
                        "cabang_nama" => isset($preReplacer['cabang2Name']) ? $preReplacer['cabang2Name'] : $_SESSION[$cCode]['tableIn_master']['cabang2_nama'],
                        "cabang2_id" => $_SESSION[$cCode]['tableIn_master']['cabang_id'],
                        "cabang2_nama" => $_SESSION[$cCode]['tableIn_master']['cabang_nama'],
                        "gudang_id" => isset($preReplacer['gudang2ID']) ? $preReplacer['gudang2ID'] : $_SESSION[$cCode]['tableIn_master']['gudang2_id'],
                        "gudang_nama" => isset($preReplacer['gudang2Name']) ? $preReplacer['gudang2Name'] : $_SESSION[$cCode]['tableIn_master']['gudang2_nama'],
                        "gudang2_id" => $_SESSION[$cCode]['tableIn_master']['gudang_id'],
                        "gudang2_nama" => $_SESSION[$cCode]['tableIn_master']['gudang_nama'],
                        "gudang" => $_SESSION[$cCode]['tableIn_master']['gudang_id'],
                        "gudang__name" => $_SESSION[$cCode]['tableIn_master']['gudang_nama'],
                        "gudang__label" => $_SESSION[$cCode]['tableIn_master']['gudang_nama'],

                        "step_avail" => sizeof($configUiMasterModulJenis['steps']),
                        "step_current" => 1,
                        "step_number" => 1,
                        "next_step_code" => isset($configUiMasterModulJenis['steps'][2]) ? $configUiMasterModulJenis['steps'][2]['target'] : "",
                        "next_step_label" => isset($configUiMasterModulJenis['steps'][2]) ? $configUiMasterModulJenis['steps'][2]['label'] : "",
                        "next_group_code" => isset($configUiMasterModulJenis['steps'][2]) ? $configUiMasterModulJenis['steps'][2]['userGroup'] : "",
                        "next_step_num" => isset($configUiMasterModulJenis['steps'][2]) ? 2 : "0",
                        "efaktur_source" => isset($preReplacer['efaktur_source']) ? $_SESSION[$cCode]['main']['nomer'] : "",
                        "referensi_id" => $midmaster,
                        "referensi_nomer" => $mNumMaster,
                        "referensi_jenis" => $mJenisMaster,
                        "reference_id" => $midmaster,
                        "reference_nomer" => $mNumMaster,
                        "reference_jenis" => $mJenisMaster,

                    );
                    foreach ($masterReplacers as $key => $val) {
                        $_SESSION[$cCode]['tableIn_master'][$key] = $val;
                    }
                    arrPrintCyan($_SESSION[$cCode]['tableIn_master']);


                    //replacer items
                    if (count($replacerConnectToItems) > 0) {
                        foreach ($_SESSION[$cCode]["items"] as $iiItems => $datas) {
                            foreach ($replacerConnectToItems as $nKey => $nValue_key) {
                                $_SESSION[$cCode]["items"][$iiItems][$nKey] = $datas[$nValue_key];
                            }
                        }
                    }


                    //region penomoran receipt #2
                    //<editor-fold desc="==========penomoran">
                    $this->load->model("CustomCounter");
                    $cn = new CustomCounter("transaksi");
                    $cn->setType("transaksi");
                    $cn->setModul($modul_transaksi);
                    $cn->setStepCode($tCodeTargetJenisTransaksi);
                    $counterForNumber = array($configCoreMasterModulJenis['formatNota']);
                    if (!in_array($counterForNumber[0], $configCoreMasterModulJenis['counters'])) {
                        die(__LINE__ . " Used number should be registered in 'counters' config as well");
                    }

                    foreach ($counterForNumber as $i => $cRawParams) {
                        $cParams = explode("|", $cRawParams);
                        $cValues = array();
                        foreach ($cParams as $param) {
                            //                    $cValues[$i][$param] = $_SESSION[$cCode]['main'][$param];
                            //                    echo "filling $param with " . $_SESSION[$cCode]['main'][$param] . "<br>";
                            $cValues[$i][$param] = $_SESSION[$cCode]['main'][$param];
                            //                    echo "filling $param with " . $_SESSION[$cCode]['main'][$param] . "<br>";
                        }
                        $cRawValues = implode("|", $cValues[$i]);
                        $paramSpec = $cn->getNewCount($cParams, $cValues[$i]);

                    }

                    $tmpNomorNota2 = $paramSpec['paramString'];
                    $tmpNomorNota2Alias = formatNota("nomer_nolink", $tmpNomorNota2);
                    cekMerah("[tmpNomorNota2: $tmpNomorNota2] [tmpNomorNota2Alias: $tmpNomorNota2Alias]");

                    //</editor-fold>
                    //endregion

                    //region dynamic counters #2
                    // <editor-fold defaultstate="collapsed" desc="==========__init+update dynamic-counters ">
                    $cn = new CustomCounter("transaksi");
                    $cn->setType("transaksi");
                    $cn->setModul($modul_transaksi);
                    $cn->setStepCode($tCodeTargetJenisTransaksi);
                    $configCustomParams = $configCoreMasterModulJenis['counters'];
                    $configCustomParams[] = "stepCode";
                    if (sizeof($configCustomParams) > 0) {
                        $cContent = array();
                        foreach ($configCustomParams as $i => $cRawParams) {
                            $cParams = explode("|", $cRawParams);
                            $cValues = array();
                            foreach ($cParams as $param) {
                                $cValues[$i][$param] = $_SESSION[$cCode]['main'][$param];
                            }
                            $cRawValues = implode("|", $cValues[$i]);
                            $paramSpec = $cn->getNewCount($cParams, $cValues[$i]);

                            $cContent[$cRawParams][$cRawValues] = $paramSpec['value'];
                            switch ($paramSpec['id']) {
                                case 0: //===counter type is new
                                    $paramKeyRaw = print_r($cParams, true);
                                    $paramValuesRaw = print_r($cValues[$i], true);
                                    $cn->writeNewCount($cParams, $cValues[$i], $paramKeyRaw, $paramValuesRaw);
                                    break;
                                default: //===counter to be updated
                                    $cn->updateCount($paramSpec['id'], $paramSpec['value']);
                                    break;
                            }
                            //echo "<hr>";
                        }
                    }
                    $appliedCounters2 = base64_encode(serialize($cContent));
                    $appliedCounters_inText2 = print_r($cContent, true);
                    // </editor-fold>
                    //endregion

                    //region tambahan counter
                    $this->load->library("CounterNumber");
                    $ccn = new CounterNumber();
                    $ccn->setCCode($cCode);
                    $ccn->setJenisTr($connector);
                    $ccn->setTransaksiGate($_SESSION[$cCode]['tableIn_master']);
                    $ccn->setMainGate($_SESSION[$cCode]['main']);
                    $ccn->setItemsGate($_SESSION[$cCode]['items']);
                    $ccn->setItems2SumGate($_SESSION[$cCode]['items2_sum']);
                    $new_counter = $ccn->getCounterNumber();


                    if (isset($new_counter['main']) && sizeof($new_counter['main']) > 0) {
                        foreach ($new_counter['main'] as $ckey => $cval) {
                            $_SESSION[$cCode]['tableIn_master'][$ckey] = $cval;
                            $_SESSION[$cCode]['main'][$ckey] = $cval;
                        }
                    }
                    if (isset($new_counter['items']) && sizeof($new_counter['items']) > 0) {
                        foreach ($new_counter['items'] as $ikey => $iSpec) {
                            foreach ($iSpec as $iikey => $iival) {
                                $_SESSION[$cCode]['items'][$ikey][$iikey] = $iival;
                            }
                        }
                    }
                    if (isset($new_counter['items2_sum']) && sizeof($new_counter['items2_sum']) > 0) {
                        foreach ($new_counter['items2_sum'] as $ikey => $iSpec) {
                            foreach ($iSpec as $iikey => $iival) {
                                $_SESSION[$cCode]['items2_sum'][$ikey][$iikey] = $iival;
                            }
                        }
                    }
                    //endregion

                    $addValues = array(
                        'counters' => $appliedCounters2,
                        'counters_intext' => $appliedCounters_inText2,
                        'nomer' => $tmpNomorNota2,
                        'nomer2' => $tmpNomorNota2Alias,
                        'dtime' => $dtime,
                        'fulldate' => $fulldate,
                    );
                    foreach ($addValues as $key => $val) {
                        $_SESSION[$cCode]['tableIn_master'][$key] = $val;
                    }

                    //==replace kedua
                    $masterReplacers = array(
                        "nomer" => $tmpNomorNota2,
                        "nomer2" => $tmpNomorNota2Alias,
                        "counters" => $appliedCounters2,
                        "counters_intext" => $appliedCounters_inText2,
                    );
                    foreach ($masterReplacers as $key => $val) {
                        $_SESSION[$cCode]['tableIn_master'][$key] = $val;
                    }

                    //===cloning detail/items cabang1 ke cabang2
                    //===yang direplace: sub_step_number, sub_step_current, sub_step_avail, next_substep_num, next_substep_code, next_substep_label, next_subgroup_code
                    $detailReplacers = array(
                        "sub_step_avail" => sizeof($configUiMasterModulJenis['steps']),
                        "sub_step_current" => 1,
                        "sub_step_number" => 1,
                        "next_substep_num" => $_SESSION[$cCode]['tableIn_master']['next_step_num'],
                        "next_substep_code" => $_SESSION[$cCode]['tableIn_master']['next_step_code'],
                        "next_substep_label" => $_SESSION[$cCode]['tableIn_master']['next_step_label'],
                        "next_subgroup_code" => $_SESSION[$cCode]['tableIn_master']['next_group_code'],
                    );
                    if (isset($_SESSION[$cCode]['tableIn_detail']) && sizeof($_SESSION[$cCode]['tableIn_detail']) > 0) {
                        //                    cekmerah("tulis rincian transaksi kedua");
                        foreach ($_SESSION[$cCode]['tableIn_detail'] as $k => $dSpec) {
                            foreach ($dSpec as $key => $val) {
                                $_SESSION[$cCode]['tableIn_detail'][$k][$key] = isset($detailReplacers[$key]) ? $detailReplacers[$key] : $val;
                            }
                        }
                    }
                    else {
                        //                    cekmerah("GAGAL tulis rincian transaksi kedua");
                    }


                    //region ----------write transaksi & transaksi_data #2
                    if (isset($_SESSION[$cCode]['tableIn_master']) && sizeof($_SESSION[$cCode]['tableIn_master']) > 0) {
                        $tr = new MdlTransaksi();
                        $insertID = $tr->writeMainEntries($_SESSION[$cCode]['tableIn_master']);
                        cekUngu($this->db->last_query());
                        $epID = $tr->writeMainEntries_entryPoint($insertID, $masterID, $_SESSION[$cCode]['tableIn_master']);
                        $insertNum = $_SESSION[$cCode]['tableIn_master']['nomer'];
                        $_SESSION[$cCode]['main']['nomer'] = $insertNum;
                        $mongoListConnect['main'] = array($insertID, $epID);
                        cekmerah("tulis transaksi kedua :: trID $insertID");
                        cekmerah($this->db->last_query());
                        if ($insertID < 1) {
                            die("Gagal saat berusaha  write transaction entry pada " . __FILE__ . " baris " . __LINE__);
                        }
                    }
                    else {
                        cekmerah("GAGAL tulis transaksi kedua");
                    }
                    if (isset($_SESSION[$cCode]['tableIn_master_values']) && sizeof($_SESSION[$cCode]['tableIn_master_values']) > 0) {
                        $inserMainValues = array();
                        foreach ($_SESSION[$cCode]['tableIn_master_values'] as $key => $val) {
                            $dd = $tr->writeMainValues($insertID, array("key" => $key, "value" => $val));
                            $inserMainValues[] = $dd;
                            $mongoListConnect['mainValues'][] = $dd;
                        }
                        if (sizeof($inserMainValues) > 0) {
                            $arrBlob = blobEncode($inserMainValues);
                            $this->db->query("UPDATE transaksi SET indexing_main_values = '$arrBlob' WHERE id=$insertID");
                        }
                    }
                    if (isset($_SESSION[$cCode]['main_add_values']) && sizeof($_SESSION[$cCode]['main_add_values']) > 0) {
                        foreach ($_SESSION[$cCode]['main_add_values'] as $key => $val) {
                            $dd = $tr->writeMainValues($insertID, array("key" => $key, "value" => $val));
                            $mongoListConnect['mainValues'][] = $dd;
                        }
                    }
                    if (isset($_SESSION[$cCode]['main_inputs']) && sizeof($_SESSION[$cCode]['main_inputs']) > 0) {
                        foreach ($_SESSION[$cCode]['main_inputs'] as $key => $val) {
                            $dd = $tr->writeMainValues($insertID, array("key" => $key, "value" => $val));
                            $inserMainValues[] = $dd;
                            $mongoListConnect['mainValues'][] = $dd;
                        }
                    }
                    if (isset($_SESSION[$cCode]['main_elements']) && sizeof($_SESSION[$cCode]['main_elements']) > 0) {
                        //                    cekMerah("ada mainElements");
                        foreach ($_SESSION[$cCode]['main_elements'] as $elName => $aSpec) {
                            $tr->writeMainElements($insertID, array(
                                "mdl_name" => isset($aSpec['mdl_name']) ? $aSpec['mdl_name'] : "",
                                "key" => isset($aSpec['key']) ? $aSpec['key'] : 0,
                                "value" => isset($aSpec['value']) ? $aSpec['value'] : "",
                                "name" => $aSpec['name'],
                                "label" => $aSpec['label'],
                                "contents" => isset($aSpec['contents']) ? $aSpec['contents'] : "",
                                "contents_intext" => isset($aSpec['contents_intext']) ? $aSpec['contents_intext'] : "",

                            ));
                        }
                    }
                    if (isset($_SESSION[$cCode]['tableIn_detail']) && sizeof($_SESSION[$cCode]['tableIn_detail']) > 0) {
                        $insertIDs = array();
                        $insertDeIDs = array();
                        foreach ($_SESSION[$cCode]['tableIn_detail'] as $dSpec) {
                            $insertDetailID = $tr->writeDetailEntries($insertID, $dSpec);
                            if ($insertDetailID < 1) {
                                die("Gagal saat berusaha write transaction detail entry pada " . __FILE__ . " baris " . __LINE__);
                            }
                            else {
                                $insertIDs[] = $insertDetailID;
                                $insertDeIDs[$insertID][] = $insertDetailID;
                                $mongoListConnect['detail'][] = $insertDetailID;
                            }
                            if ($epID != 999) {
                                $insertEpID = $tr->writeDetailEntries($epID, $dSpec);
                                if ($insertEpID < 1) {
                                    die("Gagal saat berusaha write transaction detail entry point pada " . __FILE__ . " baris " . __LINE__);
                                }
                                else {
                                    $insertIDs[] = $insertEpID;
                                    $insertDeIDs[$epID][] = $insertEpID;
                                    $mongoListConnect['detail'][] = $insertEpID;
                                }
                            }
                        }
                        if (sizeof($insertIDs) == 0) {
                            die(lgShowAlert("Transaksi gagal disimpan karena rincian transaksi kosong."));
                        }
                        else {
                            $indexing_details = array();
                            foreach ($insertDeIDs as $key => $numb) {
                                $indexing_details[$key] = $numb;
                            }

                            foreach ($indexing_details as $k => $arrID) {
                                $arrBlob = blobEncode($arrID);
                                $this->db->query("UPDATE transaksi SET indexing_details = '$arrBlob' WHERE id=$k");
                                cekOrange($this->db->last_query());
                            }
                        }
                    }
                    if (isset($_SESSION[$cCode]['tableIn_detail2_sum']) && sizeof($_SESSION[$cCode]['tableIn_detail2_sum']) > 0) {
                        $insertIDs = array();
                        foreach ($_SESSION[$cCode]['tableIn_detail2_sum'] as $dSpec) {
                            $insertIDs[] = $tr->writeDetailEntries($insertID, $dSpec);
                            $mongoListConnect['detail'] = $insertIDs;
                            if ($epID != 999) {
                                $insertIDs[] = $tr->writeDetailEntries($epID, $dSpec);
                                $mongoListConnect['detail'] = $mongoListConnect['detail'] = $insertIDs;;
                            }
                        }
                    }
                    if (isset($_SESSION[$cCode]['tableIn_detail_values']) && sizeof($_SESSION[$cCode]['tableIn_detail_values']) > 0) {
                        $insertIDs = array();
                        foreach ($_SESSION[$cCode]['tableIn_detail_values'] as $pID => $dSpec) {
                            if (isset($configCoreMasterModulJenis['tableIn']['detailValues'])) {
                                foreach ($configCoreMasterModulJenis['tableIn']['detailValues'] as $key => $src) {
                                    $dd = $tr->writeDetailValues($insertID, array(
                                        "produk_jenis" => $_SESSION[$cCode]['tableIn_detail'][$pID]['produk_jenis'],
                                        "produk_id" => $pID,
                                        "key" => $key,
                                        "value" => isset($dSpec[$src]) ? $dSpec[$src] : 0,
                                    ));
                                    $insertIDs[] = $dd;
                                    $mongoListConnect['detailValues'][] = $dd;
                                }
                            }
                        }
                        if (sizeof($insertIDs) > 0) {
                            $arrBlob = blobEncode($insertIDs);
                            $this->db->query("UPDATE transaksi SET indexing_detail_values = '$arrBlob' WHERE id=$insertID");
                        }
                    }
                    if (isset($_SESSION[$cCode]['tableIn_detail_values2_sum']) && sizeof($_SESSION[$cCode]['tableIn_detail_values2_sum']) > 0) {
                        foreach ($_SESSION[$cCode]['tableIn_detail_values2_sum'] as $pID => $dSpec) {
                            if (isset($configCoreMasterModulJenis['tableIn']['detailValues2_sum'])) {
                                foreach ($configCoreMasterModulJenis['tableIn']['detailValues2_sum'] as $key => $src) {
                                    $insertIDs[] = $tr->writeDetailValues($insertID, array(
                                        "produk_jenis" => $_SESSION[$cCode]['tableIn_detail2_sum'][$pID]['produk_jenis'],
                                        "produk_id" => $pID,
                                        "key" => $key,
                                        "value" => $dSpec[$src],
                                    ));
                                }
                            }
                        }
                    }

                    //region nulis paymentSource
                    $stepCode = $configUiMasterModulJenis['steps'][1]['target'];
                    $paymentSources = $this->config->item("payment_source");
                    if (array_key_exists($stepCode, $paymentSources)) {
                        $payConfigs = $paymentSources[$stepCode];
                        if (sizeof($payConfigs) > 0) {
                            foreach ($payConfigs as $paymentSrcConfig) {
                                //					$paymentSrcConfig = $paymentSources[$stepCode];
                                $valueSrc = $paymentSrcConfig['valueSrc'];
                                $externSrc = $paymentSrcConfig['externSrc'];
                                $tr->writePaymentSrc($insertID, array(
                                    "jenis" => $stepCode,
                                    "target_jenis" => $paymentSrcConfig['jenisTarget'],
                                    "reference_jenis" => $paymentSrcConfig['jenisSrc'],
                                    "extern_id" => $_SESSION[$cCode]['main'][$externSrc['id']],
                                    "extern_nama" => $_SESSION[$cCode]['main'][$externSrc['nama']],
                                    "nomer" => $tmpNomorNota2,
                                    "label" => $paymentSrcConfig['label'],
                                    "tagihan" => $_SESSION[$cCode]['main'][$valueSrc],
                                    "terbayar" => 0,
                                    "sisa" => $_SESSION[$cCode]['main'][$valueSrc],
                                    "cabang_id" => $_SESSION[$cCode]['main']['placeID'],
                                    "cabang_nama" => $_SESSION[$cCode]['main']['placeName'],
                                    "oleh_id" => $olehID,
                                    "oleh_nama" => $olehNama,
                                    "dtime" => $dtime,
                                    "fulldate" => $fulldate,
                                    "valas_id" => isset($_SESSION[$cCode]['main'][$externSrc['valasId']]) ? $_SESSION[$cCode]['main'][$externSrc['valasId']] : '',
                                    "valas_nama" => isset($_SESSION[$cCode]['main'][$externSrc['valasLabel']]) ? $_SESSION[$cCode]['main'][$externSrc['valasLabel']] : '',
                                    "valas_nilai" => isset($_SESSION[$cCode]['main'][$externSrc['valasValue']]) ? $_SESSION[$cCode]['main'][$externSrc['valasValue']] : '',
                                    "tagihan_valas" => isset($_SESSION[$cCode]['main'][$externSrc['valasTagihan']]) ? $_SESSION[$cCode]['main'][$externSrc['valasTagihan']] : '',
                                    "terbayar_valas" => 0,
                                    "sisa_valas" => isset($_SESSION[$cCode]['main'][$externSrc['valasSisa']]) ? $_SESSION[$cCode]['main'][$externSrc['valasSisa']] : '',
                                ));
                            }
                        }
                    }
                    else {
                        //cekMerah("TIDAK nulis paymentSrc");
                    }
                    //endregion

                    //region nulis paymentAntiSource
                    $stepCode = $configUiMasterModulJenis['steps'][1]['target'];
                    $paymentSources = $this->config->item("payment_antiSource");
                    if (array_key_exists($stepCode, $paymentSources)) {
                        $payConfigs = $paymentSources[$stepCode];
                        if (sizeof($payConfigs) > 0) {
                            foreach ($payConfigs as $paymentSrcConfig) {
                                //					$paymentSrcConfig = $paymentSources[$stepCode];
                                $valueSrc = $paymentSrcConfig['valueSrc'];
                                $externSrc = $paymentSrcConfig['externSrc'];
                                $tr->writePaymentAntiSrc($insertID, array(
                                    "jenis" => $stepCode,
                                    "target_jenis" => $paymentSrcConfig['jenisTarget'],
                                    "reference_jenis" => $paymentSrcConfig['jenisSrc'],
                                    "extern_id" => $_SESSION[$cCode]['main'][$externSrc['id']],
                                    "extern_nama" => $_SESSION[$cCode]['main'][$externSrc['nama']],
                                    "nomer" => $tmpNomorNota2,
                                    "label" => $paymentSrcConfig['label'],
                                    "tagihan" => $_SESSION[$cCode]['main'][$valueSrc],
                                    "terbayar" => 0,
                                    "sisa" => $_SESSION[$cCode]['main'][$valueSrc],
                                    "cabang_id" => $_SESSION[$cCode]['main']['placeID'],
                                    "cabang_nama" => $_SESSION[$cCode]['main']['placeName'],
                                    "oleh_id" => $olehID,
                                    "oleh_nama" => $olehNama,
                                    "dtime" => $dtime,
                                    "fulldate" => $fulldate,
                                ));
                            }
                        }


                        //cekMerah($this->db->last_query());

                    }
                    else {
                        //cekMerah("TIDAK nulis paymentSrc");
                    }
                    //endregion

                    $idHis_decode[$stepNum] = array(
                        "dtime" => $dtime,
                        "fulldate" => $fulldate,
                        "olehID" => $_SESSION[$cCode]['main']['olehID'],
                        "olehName" => $_SESSION[$cCode]['main']['olehName'],
                        "step" => $stepNum,
                        "trID" => $insertID,
                        "nomer" => $tmpNomorNota2,
                        "nomer2" => $tmpNomorNota2Alias,
                        "counters" => $appliedCounters2,
                        "counters_intext" => $appliedCounters_inText2,
                    );
                    $idHis_blob = blobEncode($idHis_decode);
                    $idHis_intext = print_r($idHis_decode, true);

                    $_SESSION[$cCode]['tableIn_master']['ids_his'] = $idHis_blob;
                    $_SESSION[$cCode]['tableIn_master']['ids_his_intext'] = $idHis_intext;

                    $tr = new MdlTransaksi();
                    $dupState = $tr->updateData(array("id" => $insertID), array(
                        "id_master" => $masterID,
                        "id_top" => $insertID,
                        "ids_his" => $idHis_blob,
                        "ids_his_intext" => $idHis_intext,

                    )) or die("Failed to update tr next-state!");

                    $baseRegistries = array(
                        'main' => isset($_SESSION[$cCode]['main']) ? $_SESSION[$cCode]['main'] : array(),
                        'items' => isset($_SESSION[$cCode]['items']) ? $_SESSION[$cCode]['items'] : array(),
                        'items2' => isset($_SESSION[$cCode]['items2']) ? $_SESSION[$cCode]['items2'] : array(),
                        'items2_sum' => isset($_SESSION[$cCode]['items2_sum']) ? $_SESSION[$cCode]['items2_sum'] : array(),
                        'itemSrc' => isset($_SESSION[$cCode]['itemSrc']) ? $_SESSION[$cCode]['itemSrc'] : array(),
                        'itemSrc_sum' => isset($_SESSION[$cCode]['itemSrc_sum']) ? $_SESSION[$cCode]['itemSrc_sum'] : array(),
                        'items3' => isset($_SESSION[$cCode]['items3']) ? $_SESSION[$cCode]['items3'] : array(),
                        'items3_sum' => isset($_SESSION[$cCode]['items3_sum']) ? $_SESSION[$cCode]['items3_sum'] : array(),
                        'items4' => isset($_SESSION[$cCode]['items4']) ? $_SESSION[$cCode]['items4'] : array(),
                        'items4_sum' => isset($_SESSION[$cCode]['items4_sum']) ? $_SESSION[$cCode]['items4_sum'] : array(),
                        'items5_sum' => isset($_SESSION[$cCode]['items5_sum']) ? $_SESSION[$cCode]['items5_sum'] : array(),
                        'items6_sum' => isset($_SESSION[$cCode]['items6_sum']) ? $_SESSION[$cCode]['items6_sum'] : array(),
                        'items7_sum' => isset($_SESSION[$cCode]['items7_sum']) ? $_SESSION[$cCode]['items7_sum'] : array(),
                        'items8_sum' => isset($_SESSION[$cCode]['items8_sum']) ? $_SESSION[$cCode]['items8_sum'] : array(),
                        'items9_sum' => isset($_SESSION[$cCode]['items9_sum']) ? $_SESSION[$cCode]['items9_sum'] : array(),
                        'items10_sum' => isset($_SESSION[$cCode]['items10_sum']) ? $_SESSION[$cCode]['items10_sum'] : array(),
                        'items_noapprove' => isset($_SESSION[$cCode]['items_noapprove']) ? $_SESSION[$cCode]['items_noapprove'] : array(),

                        'rsltItems' => isset($_SESSION[$cCode]['rsltItems']) ? $_SESSION[$cCode]['rsltItems'] : array(),
                        'rsltItems2' => isset($_SESSION[$cCode]['rsltItems2']) ? $_SESSION[$cCode]['rsltItems2'] : array(),
                        'rsltItems3' => isset($_SESSION[$cCode]['rsltItems3']) ? $_SESSION[$cCode]['rsltItems3'] : array(),

                        'tableIn_master' => isset($_SESSION[$cCode]['tableIn_master']) ? $_SESSION[$cCode]['tableIn_master'] : array(),
                        'tableIn_detail' => isset($_SESSION[$cCode]['tableIn_detail']) ? $_SESSION[$cCode]['tableIn_detail'] : array(),
                        'tableIn_detail2_sum' => isset($_SESSION[$cCode]['tableIn_detail2_sum']) ? $_SESSION[$cCode]['tableIn_detail2_sum'] : array(),
                        'tableIn_detail_rsltItems' => isset($_SESSION[$cCode]['tableIn_detail_rsltItems']) ? $_SESSION[$cCode]['tableIn_detail_rsltItems'] : array(),
                        'tableIn_detail_rsltItems2' => isset($_SESSION[$cCode]['tableIn_detail_rsltItems2']) ? $_SESSION[$cCode]['tableIn_detail_rsltItems2'] : array(),
                        'tableIn_master_values' => isset($_SESSION[$cCode]['tableIn_master_values']) ? $_SESSION[$cCode]['tableIn_master_values'] : array(),
                        'tableIn_detail_values' => isset($_SESSION[$cCode]['tableIn_detail_values']) ? $_SESSION[$cCode]['tableIn_detail_values'] : array(),
                        'tableIn_detail_values_rsltItems' => isset($_SESSION[$cCode]['tableIn_detail_values_rsltItems']) ? $_SESSION[$cCode]['tableIn_detail_values_rsltItems'] : array(),
                        'tableIn_detail_values_rsltItems2' => isset($_SESSION[$cCode]['tableIn_detail_values_rsltItems2']) ? $_SESSION[$cCode]['tableIn_detail_values_rsltItems2'] : array(),
                        'tableIn_detail_values2_sum' => isset($_SESSION[$cCode]['tableIn_detail_values2_sum']) ? $_SESSION[$cCode]['tableIn_detail_values2_sum'] : array(),
                        'main_add_values' => isset($_SESSION[$cCode]['main_add_values']) ? $_SESSION[$cCode]['main_add_values'] : array(),
                        'main_add_fields' => isset($_SESSION[$cCode]['main_add_fields']) ? $_SESSION[$cCode]['main_add_fields'] : array(),
                        'main_elements' => isset($_SESSION[$cCode]['main_elements']) ? $_SESSION[$cCode]['main_elements'] : array(),
                        'main_inputs' => isset($_SESSION[$cCode]['main_inputs']) ? $_SESSION[$cCode]['main_inputs'] : array(),
                        'main_inputs_orig' => isset($_SESSION[$cCode]['main_inputs']) ? $_SESSION[$cCode]['main_inputs'] : array(),
                        "receiptDetailFields" => isset($configLayoutMasterModulJenis['receiptDetailFields'][1]) ? $configLayoutMasterModulJenis['receiptDetailFields'][1] : array(),
                        "receiptSumFields" => isset($configLayoutMasterModulJenis['receiptSumFields'][1]) ? $configLayoutMasterModulJenis['receiptSumFields'][1] : array(),
                        "receiptDetailFields2" => isset($configLayoutMasterModulJenis['receiptDetailFields2'][1]) ? $configLayoutMasterModulJenis['receiptDetailFields2'][1] : array(),
                        "receiptSumFields2" => isset($configLayoutMasterModulJenis['receiptSumFields2'][1]) ? $configLayoutMasterModulJenis['receiptSumFields2'][1] : array(),
                        "receiptDetailSrcFields" => isset($configLayoutMasterModulJenis['receiptDetailSrcFields'][1]) ? $configLayoutMasterModulJenis['receiptDetailSrcFields'][1] : array(),
                        "items_komposisi" => isset($_SESSION[$cCode]['items_komposisi']) ? $_SESSION[$cCode]['items_komposisi'] : array(),
                        "componentsBuilder" => isset($_SESSION[$cCode]['componentsBuilder']) ? $_SESSION[$cCode]['componentsBuilder'] : array(),
                        "jurnalItems" => isset($_SESSION[$cCode]['jurnalItems']) ? $_SESSION[$cCode]['jurnalItems'] : array(),
                        "jurnal_index" => isset($_SESSION[$cCode]['jurnal_index']) ? $_SESSION[$cCode]['jurnal_index'] : array(),
                        "postProcessor" => isset($_SESSION[$cCode]['postProcessor']) ? $_SESSION[$cCode]['postProcessor'] : array(),
                        "preProcessor" => isset($_SESSION[$cCode]['preProcessor']) ? $_SESSION[$cCode]['preProcessor'] : array(),
                        "revert" => isset($_SESSION[$cCode]['revert']) ? $_SESSION[$cCode]['revert'] : array(),

                    );
                    $doWriteReg = $tr->writeDataRegistries($insertID, $baseRegistries) or mati_disini("Ada kesalahan", "Gagal saat berusaha  write base params into registries");
                    showLast_query("kuning");
                    $mongRegIDConnect = $doWriteReg;
                    //endregion

                }
                else {
                    cekMerah("to be delayed to connect to $connector");
                }
            }
            else {
                cekKuning("TIDAK ADA KONEKTOR DARI KODE $jenisTrMaster");
            }
            //endregion

            cekHijau("BAGIAN KONEK 110 KE DC/PUSAT SELESAI");
//            mati_disini(__LINE__);


            $stopDate = dtimeNow();
//            mati_disini(__LINE__ . " || STOP CEK DULU...");

            cekHitam("--- MULAI VALIDATOR ---");
            $this->load->library("Validator");
            $vdt = New Validator();

            // validasi lajur DC/PUSAT
            validateAllBalances("-1");

            // validasi lajur CABANG
            validateAllBalances("1");


            cekHijau("...tes cli transaksi... rekening pembantu masuk disini (component detail)<br>start: $startDate<br>stop: $stopDate<br>butuh waktu: " . timeDiff($startDate, $stopDate));
            mati_disini("...tes cli transaksi... rekening pembantu masuk disini (component detail)<br>start: $startDate<br>stop: $stopDate<br>butuh waktu: " . timeDiff($startDate, $stopDate));


            $this->db->trans_complete() or mati_disini("Gagal saat berusaha  commit transaction!");

            cekHijau("<h3> [$getTrID] SELESAI... </h3>");
        }
        else {
            $stopDate = dtimeNow();
            cekMerah(":: TIDAK ADA yang perlu di-CLI-kan ::
                    <br>start: $startDate<br>stop: $stopDate<br>butuh waktu: " . timeDiff($startDate, $stopDate));
        }

    }

    //------------
    public function cekMarketplace()
    {
        $this->load->model("MdlTransaksi");
        $startDate = dtimeNow();

        $jenis = "5822spd";
        $date_start = "2026-10-01";
        $date_stop = "2026-10-29";


        $this->db->trans_start();


        $arrTrIDs = array();
        $tr = New MdlTransaksi();
        $tr->addFilter("jenis='$jenis'");
        $tr->addFilter("fulldate>='$date_start'");
        $tr->addFilter("fulldate<='$date_stop'");
        $trTmp = $tr->lookupAll()->result();
        foreach ($trTmp as $spec){
            $arrTrIDs[$spec->id] = $spec->id;
        }

        $reg = New MdlTransaksi();
        $reg->setFilters(array());
        $reg->setJointSelectFields("transaksi_id,main");
        $reg->addFilter("transaksi_id in ('".implode("','", $arrTrIDs)."')");
        $regTmp = $reg->lookupDataRegistries()->result();
        $registryGates = array();
        $marketplace__ = 0;
        $marketplaceData = array();
        $arrTrIDs_marketplace = array();
        foreach ($regTmp as $regSpec) {
            $regID = $regSpec->transaksi_id;
            $main = blobDecode($regSpec->main);
            $registryGates[$regID] = array(
                "tipe_penjualan_id" => $main["tipe_penjualan"],
                "tipe_penjualan_nama" => $main["tipe_penjualan_nama"],
                "marketplace_id" => $main["pihakMarketplaceID"],
                "marketplace_nama" => $main["pihakMarketplaceName"],
            );
            if($main["tipe_penjualan"] == 1){
                $marketplace__ += 1;
                $marketplaceData[$regID] = array(
                    "tipe_penjualan_id" => $main["tipe_penjualan"],
                    "tipe_penjualan_nama" => $main["tipe_penjualan_nama"],
                    "marketplace_id" => $main["pihakMarketplaceID"],
                    "marketplace_nama" => $main["pihakMarketplaceName"],
                    "customers_id" => $main["pihakID"],
                    "customers_nama" => $main["pihakName"],
                );
                $arrTrIDs_marketplace[$regID] = $regID;
            }
        }
        cekHitam("[marketplace__: $marketplace__]");
//        arrPrint($registryGates);
//        arrPrintCyan($marketplaceData);

        $pakai_ini = 0;
        if($pakai_ini == 1){
            foreach ($registryGates as $trid => $data){
                $tr = New MdlTransaksi();
                $tr->setFilters(array());
                $where = array(
                    "id" => $trid,
                );
                $tr->updateData($where, $data);
                showLast_query("orange");
            }
        }

        $pakai_ini = 1;
        if($pakai_ini == 1){
//            arrPrintWebs($arrTrIDs_marketplace);
            $jenisCek = "110r";
            $arrTrIDs = array();
            $arrTrIDs_110r = array();
            $tr = New MdlTransaksi();
            $tr->addFilter("jenis='$jenisCek'");
            $tr->addFilter("reference_id in ('".implode("','", $arrTrIDs_marketplace)."')");
            $trTmp = $tr->lookupAll()->result();
            showLast_query("biru");
            foreach ($trTmp as $spec){
                $arrReferenceIDs_110r[$spec->reference_id] = $spec->reference_id;
            }
            arrPrintPink($arrReferenceIDs_110r);
            $arrTidakPunya_110r = array_diff($arrTrIDs_marketplace, $arrReferenceIDs_110r);
            arrPrintHitam($arrTidakPunya_110r);
            cekHitam(count($arrTidakPunya_110r));
            cekHitam(implode(",", $arrTidakPunya_110r));
        }





        $stopDate = dtimeNow();
        mati_disini("...tes cli transaksi... rekening pembantu masuk disini (component detail)<br>start: $startDate<br>stop: $stopDate<br>butuh waktu: " . timeDiff($startDate, $stopDate));

        $this->db->trans_complete() or mati_disini("Gagal saat berusaha  commit transaction!");

        cekHijau("<h3> [$getTrID] SELESAI... </h3>");
    }

    public function cekPOS()
    {
        $this->load->model("MdlTransaksi");
        $this->load->model("Coms/ComJurnal");
        $startDate = dtimeNow();

        $jenis = array("5822spd", "5823spd");
        $pembayaran_sys = "cash";
        $date_start = "2026-10-01";
        $date_stop = "2026-11-03";


        $this->db->trans_start();


        $arrTrIDs = array();
        $arrTrIDs_marketplace = array();
        $tr = New MdlTransaksi();
        $tr->addFilter("jenis in ('".implode("','", $jenis)."')");
        $tr->addFilter("fulldate>='$date_start'");
        $tr->addFilter("fulldate<='$date_stop'");
//        $tr->addFilter("pembayaran_sys='$pembayaran_sys'");
        $trTmp = $tr->lookupAll()->result();
        showLast_query("biru");
        foreach ($trTmp as $spec){
            $arrTrIDs[$spec->id] = $spec->id;
            $arrTrIDs_marketplace[$spec->id] = $spec->id;
        }

        $arrTrIDs_jurnal = array();
        $cj = New ComJurnal();
        $cj->addFilter("transaksi_id in ('".implode("','", $arrTrIDs)."')");
        $cj->addFilter("rekening='2030060'");
        $cj->addFilter("kredit>0");
        $cjTmp = $cj->lookupAll()->result();
        showLast_query("kuning");
        if(sizeof($cjTmp)>0){
            foreach ($cjTmp as $cjSpec){
                $arrTrIDs_jurnal[$cjSpec->transaksi_id] = $cjSpec->transaksi_id;
            }
            $arrTrIDs_marketplace = array();
            $arrTrIDs_marketplace = array_diff($arrTrIDs_jurnal, $arrTrIDs);
        }

        $pakai_ini = 0;
        if($pakai_ini == 1){
            foreach ($registryGates as $trid => $data){
                $tr = New MdlTransaksi();
                $tr->setFilters(array());
                $where = array(
                    "id" => $trid,
                );
                $tr->updateData($where, $data);
                showLast_query("orange");
            }
        }
        arrPrintPink($arrTrIDs_jurnal);
        arrPrintCyan($arrTrIDs_marketplace);
//mati_disini(__LINE__);
        $pakai_ini = 1;
        if($pakai_ini == 1){
            $jenisCek = "110r";
            $arrTrIDs = array();
            $arrTrIDs_110r = array();
            $tr = New MdlTransaksi();
            $tr->addFilter("jenis='$jenisCek'");
            $tr->addFilter("reference_id in ('".implode("','", $arrTrIDs_marketplace)."')");
            $trTmp = $tr->lookupAll()->result();
            showLast_query("biru");
            foreach ($trTmp as $spec){
                $arrReferenceIDs_110r[$spec->reference_id] = $spec->reference_id;
            }
            arrPrintPink($arrReferenceIDs_110r);
            $arrTidakPunya_110r = array_diff($arrTrIDs_marketplace, $arrReferenceIDs_110r);
            arrPrintHitam($arrTidakPunya_110r);
            cekHitam(count($arrTidakPunya_110r));
            cekHitam(implode(",", $arrTidakPunya_110r));
        }





        $stopDate = dtimeNow();
        mati_disini("...tes cli transaksi... rekening pembantu masuk disini (component detail)<br>start: $startDate<br>stop: $stopDate<br>butuh waktu: " . timeDiff($startDate, $stopDate));

        $this->db->trans_complete() or mati_disini("Gagal saat berusaha  commit transaction!");

        cekHijau("<h3> [$getTrID] SELESAI... </h3>");
    }


}



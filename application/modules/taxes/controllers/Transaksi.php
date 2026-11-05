<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once "Modul_Controller.php";

class Transaksi extends Modul_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper("he_stepping");
        $this->load->helper("he_access_right");
        $this->load->library("MobileDetect");
        $this->load->helper("he_session_replacer");
        $this->load->model("Mdls/MdlCurrency");
        $this->load->model("MdlTransaksi");
        $this->load->helper('he_angka');
        $tmpJenis = $this->jenisTr;
        $this->cabang_code = getCabangCode();
        $this->allSteps = isset($this->configUi[$tmpJenis]['steps']) ? $this->configUi[$tmpJenis]['steps'] : array();
        /* ----------------------------------------------------------------------------------
          * loader cunstruk yg wajib ada
          * variabel-variabel bisa langsung dipangil, apa saja yang ada bisa dilihat didalamnya
          * ----------------------------------------------------------------------------------*/
        // require_once "_construct_file.php";

        $this->reject = true;
//        $this->reject_all = true;
    }

    public function validate()
    {
        $fieldOpnameEntryValidatorRules = isset($this->configUi[$this->jenisTr]["shoppingCartFieldOpnameEntryValidators"]) ? $this->configUi[$this->jenisTr]["shoppingCartFieldOpnameEntryValidators"] : array();
        $fieldOpnameValidatorRules = isset($this->configUi[$this->jenisTr]["shoppingCartFieldOpnameValidators"]) ? $this->configUi[$this->jenisTr]["shoppingCartFieldOpnameValidators"] : array();

        $fieldValidatorRules = isset($this->configUi[$this->jenisTr]["shoppingCartFieldValidators"]) ? $this->configUi[$this->jenisTr]["shoppingCartFieldValidators"] : array();
        $rowValidatorRules = isset($this->configUi[$this->jenisTr]["shoppingCartRowValidators"]) ? $this->configUi[$this->jenisTr]["shoppingCartRowValidators"] : array();
        $rowNumValidatorRules = isset($this->configUi[$this->jenisTr]["shoppingCartRowNumValidators"]) ? $this->configUi[$this->jenisTr]["shoppingCartRowNumValidators"] : array();
        $rowOptValidatorRules = isset($this->configUi[$this->jenisTr]["shoppingCartRowOptionalValidators"]) ? $this->configUi[$this->jenisTr]["shoppingCartRowOptionalValidators"] : array();
        $validateReceiveElement = isset($this->configUi[$this->jenisTr]["validateReceiveElement"][1]) ? $this->configUi[$this->jenisTr]["validateReceiveElement"][1] : array();
        $appletConfigs = isset($this->configUi[$this->jenisTr]['applets']) ? $this->configUi[$this->jenisTr]['applets'] : array();
        $elementConfigs = isset($this->configUi[$this->jenisTr]['receiptElements']) ? $this->configUi[$this->jenisTr]['receiptElements'] : array();
        $relElementConfigs = isset($this->configUi[$this->jenisTr]['relativeElements']) ? $this->configUi[$this->jenisTr]['relativeElements'] : array();
        $availPayments = isset($this->configUi[$this->jenisTr]['availPayments']) ? $this->configUi[$this->jenisTr]['availPayments'] : array();
        $validateAdditional = isset($this->configUi[$this->jenisTr]['shopingCartAddValidator']['additional']) ? $this->configUi[$this->jenisTr]['shopingCartAddValidator']['additional'] : array();
        $fieldValidatorsComparison = isset($this->configUi[$this->jenisTr]['shoppingCartFieldValidatorsComparison']) ? $this->configUi[$this->jenisTr]['shoppingCartFieldValidatorsComparison'] : array();

        $fieldValidatorPairedItem = isset($this->configUi[$this->jenisTr]["shoppingCartValidatorsPairedItem"]) ? $this->configUi[$this->jenisTr]["shoppingCartValidatorsPairedItem"] : array();
        $unionValidateComparison = isset($this->configUi[$this->jenisTr]["shopingCartUnionComparison"]) ? $this->configUi[$this->jenisTr]["shopingCartUnionComparison"] : array();
        $unionValidate = isset($this->configUi[$this->jenisTr]["shoppingCartUnionValidators"]) ? $this->configUi[$this->jenisTr]["shoppingCartUnionValidators"] : array();
        $balanceValidate = isset($this->configUi[$this->jenisTr]["shoppingCartBalanceValidate"]) ? $this->configUi[$this->jenisTr]["shoppingCartBalanceValidate"] : array();
        $previewCtr = isset($this->configUi[$this->jenisTr]["previewCtr"]) ? $this->configUi[$this->jenisTr]["previewCtr"] : matiHere('controller preview belum diset');


        $cCode = $this->cCode;

        $rawPrevURL = isset($_GET['rawPrev']) ? $_GET['rawPrev'] : "";
        // mati_disini(__CLASS__ . " $cCode");
        //==iterasi untuk memasukkan element relatif
        if (isset($_SESSION[$cCode]['main_elements']) && sizeof($_SESSION[$cCode]['main_elements']) > 0) {
            //            //cekBiru("hendak memeriksa relative impacts");
            foreach ($_SESSION[$cCode]['main_elements'] as $eName => $eSpec) {
                //                //cekBiru("memeriksa $eName:");
                if (array_key_exists($eName, $relElementConfigs)) {
                    //                    //cekHijau("$eName memiliki relative impacts");
                    $currentValue = "";
                    switch ($eSpec['elementType']) {
                        case "dataModel":
                            $currentValue = $eSpec['key'];
                            break;
                        case "dataField":
                            $currentValue = $eSpec['value'];
                            break;
                    }
                    if (array_key_exists($currentValue, $relElementConfigs[$eName])) {
                        //                        //cekHijau("memenuhi syarat");
                        //===daftarkan ke elementConfig
                        if (sizeof($relElementConfigs[$eName][$currentValue]) > 0) {
                            //                            //cekMerah("memeriksa $eName, $currentValue");
                            //                            $rcCtr = 0;
                            foreach ($relElementConfigs[$eName][$currentValue] as $rcID => $rcSpec) {
                                //                                $elKey = $eName . "_" . $currentValue . "_" . $rcID;
                                $elKey = $rcID;
                                $elementConfigs[$elKey] = $relElementConfigs[$eName][$currentValue][$rcID];
                                //                                $rcCtr++;
                            }
                        }

                    }
                    else {
                        //                        //cekMerah("TIDAK memenuhi syarat");
                    }
                }
            }
        }
        $arrySegment = $this->uri->segment_array();
        if (isset($_SESSION[$cCode]['mode']['edit']) && sizeof($_SESSION[$cCode]['mode']['edit']) > 0) {
            $prevAction = $previewCtr . "/editPreview";
            $main_id = "/" . $_SESSION[$cCode]['mode']['edit'];
        }
        else {
            if (isset($_SESSION[$cCode]['mode']['cancel']) && sizeof($_SESSION[$cCode]['mode']['cancel']) > 0) {
                $prevAction = "$previewCtr/preCancelPackingPreview";
                $main_id = "/" . $_SESSION[$cCode]['mode']['cancel'];
            }
            else {
                $prevAction = $previewCtr . "/preview";
                $main_id = "";
            }
        }

        $errMsgs = array();
        $errLines = array();
        $errFields = array();
        $errRows = array();
        $errErrorException = array();
        if (sizeof($rowValidatorRules) > 0) {

            foreach ($rowValidatorRules as $field => $label) {
                if (!isset($_SESSION[$cCode]['main'][$field])) {
                    $errMsgs[] = "$label wajib diisi";

                    $errRows[] = $field;
                }

            }

        }
        if (sizeof($rowNumValidatorRules) > 0) {

            foreach ($rowNumValidatorRules as $field => $label) {
                if (!isset($_SESSION[$cCode]['main'][$field])) {
                    $errMsgs[] = "$label wajib dilengkapi";

                    $errRows[] = $field;
                }
                else {
                    if (0 + ($_SESSION[$cCode]['main'][$field]) < 0.1) {
                        $errMsgs[] = "$label should be a number";

                        $errRows[] = $field;
                    }
                }

            }

        }
        if (sizeof($rowOptValidatorRules) > 0) {
            foreach ($rowOptValidatorRules as $srcName => $srcSpec) {
                foreach ($srcSpec as $value => $pair) {
                    if (isset($_SESSION[$cCode]['main'][$srcName]) && $_SESSION[$cCode]['main'][$srcName] == $value) {
                        foreach ($pair as $k => $v) {
                            if (!isset($_SESSION[$cCode]['main'][$k])) {
                                $errMsgs[] = "$k is required";

                                $errRows[] = $k;
                            }
                        }
                    }
                }
            }
        }
        if (sizeof($fieldValidatorRules) > 0) {

            if (isset($_SESSION[$cCode]['items']) && sizeof($_SESSION[$cCode]['items']) > 0) {
                foreach ($_SESSION[$cCode]['items'] as $xid => $iSpec) {
                    $id = $iSpec['id'];
                    $nama = htmlspecialchars($iSpec['name']);
                    if ((isset($iSpec['disabled']) && $iSpec['disabled'] == "0") || !isset($iSpec['disabled'])) {
                        if (!isset($errFields[$id])) {
                            $errFields[$id] = array();
                        }
                        foreach ($fieldValidatorRules as $field => $label) {
                            if (!isset($iSpec[$field])) {
                                $errMsgs[] = "$label wajib diisi";
                                $errLines[] = $id;
                                $errFields[$id][] = $field;
                            }
                            if (!is_numeric($iSpec[$field])) {
                                $errMsgs[] = "$label must be a valid number";
                                $errLines[] = $id;
                                $errFields[$id][] = $field;
                            }
                            if ((int)$iSpec[$field] < 0.5) {
//                                $errMsgs[] = "$label must be > 0";
                                $errMsgs[] = "$label harus lebih dari 0";
                                $errLines[] = $id;
                                $errFields[$id][] = $field;
                            }
                        }
                    }

                }
            }

        }
        if (sizeof($elementConfigs) > 0) {
            foreach ($elementConfigs as $eName => $aSpec) {
                if (!isset($_SESSION[$cCode]['main_elements'][$eName])) {
                    if ((isset($aSpec['noValidate'])) && ($aSpec['noValidate'] == true)) {
                    }
                    else {
                        if (isset($aSpec['labelValidate'])) {
                            $elementMsg = $aSpec['labelValidate'];
                        }
                        else {
                            $elementMsg = ($aSpec['label'] . " wajib dipilih!");
                        }
                        $errMsgs[] = $elementMsg;
                        echo "<script>";
                        echo "if(top.document.getElementById('elTitle_$eName')){top.document.getElementById('elTitle_$eName').className='box-headers text-red text-left'};";
                        echo "</script>\n";
                    }
                }
                else {
                    switch ($aSpec['elementType']) {
                        case "dataModel":
                            if (isset($validateReceiveElement[$eName]) && sizeof($validateReceiveElement[$eName])) {
                                $valid = 0;
                                $check = array();
                                foreach ($validateReceiveElement[$eName] as $tLabel => $textWarning) {
                                    if (strlen($_SESSION[$cCode]['main'][$eName . '__' . $tLabel]) > 10) {
                                    }
                                    else {
                                        $check[] = $tLabel;
                                    }
                                }
                                if (sizeof($check) > 1) {
                                    $errMsgs[] = ("Harap lengkapi salah satu NPWP/KTP diBillingDetails customer");
                                    foreach ($check as $nLabel) {
                                        $errMsgs[] = ("lengkapi $nLabel!");
                                    }
                                }
                            }

                            if (strlen($_SESSION[$cCode]['main_elements'][$eName]['key']) < 0.5) {
                                $errMsgs[] = ("element " . $aSpec['label'] . " must be filled with one entry!");
                                echo "<script>";
                                echo "if(top.document.getElementById('elTitle_$eName')){top.document.getElementById('elTitle_$eName').className='box-headers text-red text-left'};";
                                echo "</script>\n";
                            }
                            else {
                                echo "<script>";
                                echo "if(top.document.getElementById('elTitle_$eName')){top.document.getElementById('elTitle_$eName').className='box-headers bg-grey text-left'};";
                                echo "</script>\n";
                            }
                            break;
                        case "dataField":
                            if (strlen($_SESSION[$cCode]['main_elements'][$eName]['value']) < 0.5) {
                                if ((isset($aSpec['noValidate'])) && ($aSpec['noValidate'] == true)) {
                                }
                                else {
                                    $errMsgs[] = ($aSpec['label'] . " must be filled with one entry!***");
                                    echo "<script>";
                                    echo "if(top.document.getElementById('elTitle_$eName')){top.document.getElementById('elTitle_$eName').className='box-headers text-red text-left'};";
                                    echo "</script>\n";
                                }

                            }
                            else {
                                echo "<script>";
                                echo "if(top.document.getElementById('elTitle_$eName')){top.document.getElementById('elTitle_$eName').className='box-headers bg-grey text-left'};";
                                echo "</script>\n";
                            }
                            break;
                    }

                }
            }
        }
        if (sizeof($fieldValidatorsComparison) > 0) {
            $result = array();
            $labels = array();
            foreach ($fieldValidatorsComparison as $field => $label) {
                $labels[$label] = $field;
                $result[$label] = isset($_SESSION[$cCode]['main'][$field]) ? $_SESSION[$cCode]['main'][$field] : 0;
            }

            if (round($result["sumber"], 2) != round($result["target"], 2)) {
                $labelSrc = isset($labels["sumber"]) ? $labels["sumber"] : "";
                $labelTarget = isset($labels["target"]) ? $labels["target"] : "";
                $errMsgs[] = "Nilai total tidak sama dari nilai akumulasi nota yang dipilih.";
                $errRows[] = "test";
            }


        }
        if (sizeof($unionValidateComparison) > 0) {
            $result = array();
            $labels = array();
            $validate = 0;
            $val = array();
            $valsValidate = array();

            foreach ($unionValidateComparison as $fieldData) {

                if (isset($_SESSION[$cCode]['main']["nilai_entry"])) {
                    if ($_SESSION[$cCode]['main']["nilai_entry"] > 0) {

                    }
                    else {
                        if ($_SESSION[$cCode]['main']["nilai_entry"] == 0) {
                            unset($_SESSION[$cCode]['main']["nilai_entry"]);
                        }
                        else {

                            if (sizeof($unionValidate) > 0) {

                                foreach ($unionValidate as $fieldUnion) {
                                    $validateUnion = 0;
                                    foreach ($fieldUnion as $field => $fieldLabel) {
                                        if (isset($_SESSION[$cCode]['main'][$field]) && $_SESSION[$cCode]['main'][$field] > 0) {

                                        }
                                        else {
                                            $validateUnion++;

                                        }
                                    }
                                    if (sizeof($fieldUnion) == $validateUnion) {
                                        $errMsgs[] = $fieldData["nilai_entry"];
                                    }
                                }
                            }
                            else {
                                $errMsgs[] = $fieldData["nilai_entry"];
                            }
                        }

                    }
                }
                else {
                    if (sizeof($unionValidate) > 0) {

                        foreach ($unionValidate as $fieldUnion) {
                            $validateUnion = 0;
                            $err = array();
                            foreach ($fieldUnion as $field => $fieldLabel) {
                                if (isset($_SESSION[$cCode]['main'][$field]) && $_SESSION[$cCode]['main'][$field] > 0) {

                                }
                                else {
                                    $validateUnion++;
                                    $err[] = $fieldLabel;

                                }
                            }
                            if (sizeof($err) == sizeof($fieldUnion)) {
                                $errMsgs[] = "metode pembayaran tidak ada yang dipilih, silahkan periksa kembali";
                            }
                        }
                    }
                    else {
                        $errMsgs[] = $fieldData["nilai_entry"];
                    }
                }

                foreach ($fieldData as $field => $label) {

                    //                    if (isset($_SESSION[$cCode]['main'][$field]) && ($_SESSION[$cCode]['main'][$field] > 0)) {
                    if (isset($_SESSION[$cCode]['main'][$field])) {
                        //cekHere("ada $field dan nilai $field tidak sama dengan nol ");
                        $validate++;
                        $val["source"] = $field;
                        $valsValidate["source"][] = $field;
                    }
                    else {
                        //cekHere("TIDAK ada $field  ");
                        $val["target"] = $field;
                        $valsValidate["target"][] = $field;
                    }
                }

            }
            //$this->debugArrPrint($unionValidateComparison);
            //$this->debugArrPrint($valsValidate);


            if (array_key_exists("source", $val)) {
                $cekval = $val["target"];
                if (!isset($_SESSION[$cCode]['main'][$cekval])) {
                    foreach ($unionValidateComparison as $data) {
                        //                        //cekHere(":: sampai sini $cekval :: " . $data[$cekval]);
                        if (isset($data[$cekval])) {
                            $errMsgs[] = $data[$cekval];
                            $errErrorException[] = $data[$cekval];
                        }
                    }
                }
                //$this->debugArrPrint($errMsgs);
            }
            // $this->debugArrPrint($valsValidate);
            if (!isset($valsValidate["source"]) || (sizeof($valsValidate["source"]) == 0)) {
                // mati_disini("tidak memilih cash account, credit note, uang muka");
                //  $errMsgs[] = "cash account, credit note, uang muka tidak ada yang dipilih.";
            }

        }
        if (sizeof($fieldValidatorPairedItem) > 0) {
            $label = $this->configUi[$this->jenisTr]["label"];
            $source = isset($_SESSION[$cCode][$fieldValidatorPairedItem['sumber']]) ? $_SESSION[$cCode][$fieldValidatorPairedItem['sumber']] : "";
            $target = isset($_SESSION[$cCode][$fieldValidatorPairedItem['target']]) ? $_SESSION[$cCode][$fieldValidatorPairedItem['target']] : "";
            if (sizeof($source) > 0) {
                foreach ($source as $pID => $pSpec) {
                    $pTarget = isset($target[$pID]) ? $target[$pID] : array();

                    if (!array_key_exists($pID, $target)) {
                        $errMsgs[] = "$label " . $pSpec['name'] . " is required.";
                    }
                    if (isset($pTarget['jml']) && isset($pSpec['jml'])) {
                        if ($pTarget['jml'] < $pSpec['jml']) {
                            $errMsgs[] = "konversi " . $pSpec['name'] . " salah. silahkan cek ulang jumlah konversinya.";
                        }
                        $jml_satuan = $pTarget['jml'] / $pSpec['jml'];
                        $jml_satuan_ex = explode(".", $jml_satuan);
                        if (sizeof($jml_satuan_ex) > 1) {
                            $errMsgs[] = "konversi " . $pSpec['name'] . " salah. silahkan cek ulang jumlah konversinya.";
                        }
                    }
                }
            }
        }
        if (sizeof($fieldOpnameValidatorRules) > 0) {
            if (isset($_SESSION[$cCode]['items']) && sizeof($_SESSION[$cCode]['items']) > 0) {
                foreach ($_SESSION[$cCode]['items'] as $xid => $iSpec) {
                    $id = $iSpec['id'];
                    $name = htmlspecialchars($iSpec['nama']);
                    if ((isset($iSpec['disabled']) && $iSpec['disabled'] == "0") || !isset($iSpec['disabled'])) {
                        foreach ($fieldOpnameValidatorRules as $field => $label) {
                            if (isset($iSpec['qty_selisih']) && ($iSpec['qty_selisih'] > 0)) {
                                if ((int)$iSpec[$field] <= 0) {
//                                    $errMsgs[] = "$label $name must be > 0";
                                    $errMsgs[] = "$label $name harus lebih dari 0";
                                    $errLines[] = $id;
                                    $errFields[$id][] = $field;
                                }
                            }
                        }
                    }
                }
            }
        }
        if (sizeof($fieldOpnameEntryValidatorRules) > 0) {
            if (isset($_SESSION[$cCode]['items']) && sizeof($_SESSION[$cCode]['items']) > 0) {
                $no = 0;
                foreach ($_SESSION[$cCode]['items'] as $xid => $iSpec) {
                    $no++;
                    $id = $iSpec['id'];
                    $kode = $iSpec['produk_kode'];
                    $label = $iSpec['label'];

                    $name = htmlspecialchars($iSpec['nama']);
                    if ((isset($iSpec['disabled']) && $iSpec['disabled'] == "0") || !isset($iSpec['disabled'])) {
                        foreach ($fieldOpnameEntryValidatorRules as $field => $label) {
                            if (!isset($iSpec[$field]) && ($iSpec['stok'] > 0)) {
                                $errMsgs[] = "$label $name harus diketik ulang, baris nomer $no.";
                                $errLines[] = $id;
                                $errFields[$id][] = $field;

                            }
                        }
                    }

                    $stokBuku = isset($iSpec->stok) ? $iSpec->stok : 0;
                    $stokOpname = $iSpec->qty_opname;
                    $selisih = $stokOpname - $stokBuku;
                    $stokBukuHitung = $iSpec->qty_opname - $iSpec->qty_debet + $iSpec->qty_kredit;
                    if ($selisih < 0) {
                        if ($selisih != $iSpec->qty_kredit) {
                            $msg = "Produk $label $kode SALAH. Silahkan diperiksa baris nomer $no.";
                            $errMsgs[] = $msg;
                        }
                    }
                    elseif ($selisih > 0) {
                        if ($selisih != $iSpec->qty_debet) {
                            $msg = "Produk $label $kode SALAH. Silahkan diperiksa baris nomer $no.";
                            $errMsgs[] = $msg;
                        }
                    }
                    elseif ($stokBukuHitung != $stokBuku) {
                        $msg = "Produk $label $kode SALAH. Silahkan diperiksa baris nomer $no.";
                        $errMsgs[] = $msg;
                    }
                    //                    else{
                    //                        mati_disini("Produk $label $kode SALAH. Silahkan diperiksa baris nomer $no.");
                    //                    }
                }
            }
        }

        //region validate additional
        if (sizeof($validateAdditional) > 0) {
            $selectedAdd = $_SESSION[$cCode]["main"]["additional"];
            if (isset($validateAdditional[$selectedAdd])) {
                $valKey = $validateAdditional[$selectedAdd];
                if ($_SESSION[$cCode]["main"][$valKey] == 0) {
                    $errMsgs[] = "additional kurs must be filled or select none on additional for additional kurs 0 ";
                }
            }

        }
        //        matiHEre();

        //endregion

        if (sizeof($balanceValidate) > 0) {
            foreach ($balanceValidate as $kk => $kSpec) {
                if ($kk == "key") {
                    $debet = $kSpec[0];
                    $kredit = $kSpec[1];
                    $debetNilai = isset($_SESSION[$cCode]['main'][$debet]) ? $_SESSION[$cCode]['main'][$debet] : 0;
                    $kreditNilai = isset($_SESSION[$cCode]['main'][$kredit]) ? $_SESSION[$cCode]['main'][$kredit] : 0;
                    if ($debetNilai != $kreditNilai) {
                        $errMsgs[] = $balanceValidate['label'];
                    }
                }
            }
        }
        // arrPrintBlue($_SESSION[$cCode]['tableIn_detail']);
        // mati_disini(__LINE__);
        // always validation active
        $gateNameDetail = "tableIn_detail";
        if (!isset($_SESSION[$cCode][$gateNameDetail]) || sizeof($_SESSION[$cCode][$gateNameDetail]) == 0) {

            $msg = "Transaksi gagal dilanjutkan karena rincian transaksi kosong.<br>Silahkan merubah qty/unit price dahulu dan masukkan data yang benar supaya transaksi bisa dilanjutkan. @" . __LINE__ . __METHOD__;
            mati_disini($msg);
        }
        else {
            foreach ($_SESSION[$cCode][$gateNameDetail] as $spec) {
                if (!isset($spec['valid_qty'])) {
                    $hidden_msg = "<br><span style='color:#ffffff;'>gerbang valid_qty tidak ada</span>";
                    $msg = "Transaksi gagal dilanjutkan karena rincian transaksi kosong.<br>Silahkan merubah qty/unit price dahulu dan masukkan data yang benar supaya transaksi bisa dilanjutkan. $hidden_msg";
                    mati_disini($msg);
                }
                else {
                    if ($spec['valid_qty'] == 0) {
                        $hidden_msg = "<br><span style='color:#ffffff;'>gerbang valid_qty bernilai 0</span>";
                        $msg = "Transaksi gagal dilanjutkan karena rincian transaksi kosong.<br>Silahkan merubah qty/unit price dahulu dan masukkan data yang benar supaya transaksi bisa dilanjutkan. $hidden_msg";
                        mati_disini($msg);
                    }
                }
            }
        }


        /* ------------------------------
         * validasi stok saat mau produksi
         * ------------------------------*/
        $mains = $_SESSION[$cCode];

        $items2_sum = isset($mains['items2_sum']) ? $mains['items2_sum'] : "";
        switch ($this->jenisTr) {
            case "776":

                $kurangStoks = array();
                $hasil = "";
                $no = 0;
                foreach ($items2_sum as $pId => $item) {

                    $nama = $item['nama'];
                    $sisa = $item['stok'] - $item['jml'];

                    if ($sisa < 0) {
                        $no++;
                        $kurangStoks[$pId] = $sisa;
                        //
                        $sisa_f = $sisa * -1;
                        $nama_f = htmlspecialchars($nama);
                        $var = "<span class='font-size-0-7'>$no. $nama_f: <span class='text-bold'>$sisa_f</span></span>";
                        if ($hasil == "") {
                            $hasil = "$var";
                        }
                        else {
                            $hasil = "$hasil<br>$var";
                        }
                    }
                }


                if (sizeof($kurangStoks) > 0) {
                    // //cekhitam(sizeof($kurangStoks));
                    $alerts = array("type" => "warning",
                        "title" => "Persediaan tidak cukup",
                        "html" => $hasil,
                    );
                    echo swalAlert($alerts);
                    die();
                }

                break;
            case "1982":
                break;
            case "961":
            case "967":
                $items = $mains['items'];
                $kurangStoks = array();
                $hasil = "";
                $no = 0;
                foreach ($items as $pId => $item) {
                    if (isset($item['stok'])) {
                        $nama = $item['nama'];
                        $sisa = $item['stok'] - $item['jml'];

                        if ($sisa < 0) {
                            $no++;
                            $kurangStoks[$pId] = $sisa;

                            $sisa_f = $sisa * -1;
                            $stok_f = $item['stok'];
                            $jml_f = $item['jml'];
                            $nama_f = htmlspecialchars($nama);
                            $var = "<span class='font-size-0-7'>$no. <span style='font-weight:bold;'>$nama_f</span>, stok tersedia $stok_f, jumlah input <span style='font-weight:bold;'>$jml_f</span> melebihi stok yang tersedia. Silahkan dikoreksi lagi.</span>";
                            if ($hasil == "") {
                                $hasil = "$var";
                            }
                            else {
                                $hasil = "$hasil<br>$var";
                            }
                        }
                    }
                }
                if (sizeof($kurangStoks) > 0) {

                    $alerts = array("type" => "warning",
                        "title" => "Insufficient stock",
                        "html" => $hasil,
                    );
                    echo swalAlert($alerts);
                    die();
                }

                break;
            case "749":
                if ($_SESSION[$cCode]['main']['lebih_bayar'] <= 0) {
                    //                    matiHEre("iki opoo ".$_SESSION[$cCode]['main']['lebih_bayar']);
                    $_SESSION[$cCode]['main']['nilai_cash'] = $_SESSION[$cCode]['main']['nilai_entry'];
                    //$this->debugArrPrint($errErrorException);
                    if (sizeof($errErrorException) > 0) {
                        foreach ($errErrorException as $errKey => $errVal) {
                            if (array_key_exists($errKey, $errMsgs)) {
                                unset($errMsgs[$errKey]);
                            }
                        }
                    }

                }

                break;
        }

        if (sizeof($errMsgs) > 0) {
            $_SESSION['errMsg'] = implode("<br>", $errMsgs);

            if (sizeof($errLines) > 0) {
                $_SESSION['errLines'] = $errLines;
            }

            if (sizeof($errFields) > 0) {
                $_SESSION['errFields'] = $errFields;
            }


            echo lgShowAlert($_SESSION['errMsg']);
            die();
        }
        else {

//             mati_disini("LOLOS, tidak kena VALIDATE :: $prevAction");
            // $shoppingCart =  base_url() . $this->modul . "/" . $prevAction . "/" . $this->jenisTr . "?rawPrev=$rawPrevURL";

            $actionTarget = "top.BootstrapDialog.show({
                                title:'preview',
                                message: " . 'top.$' . "('<div></div>').load('" . base_url() . $this->modul . "/" . $prevAction . "/" . $this->jenisTr . "?rawPrev=$rawPrevURL'),
                                draggable:false,
                                size:top.BootstrapDialog.SIZE_WIDE,
                                type:top.BootstrapDialog.TYPE_DEFAULT,
                                closable:true,
                                }
                             );";

            echo "<script>top.close_holdon();$actionTarget</script>";

        }
    }

    public function viewUndoneItems()
    {
        $jenisTr = $this->jenisTr;
        $cCode = $this->cCode;

        $historyFields = $this->configUi[$jenisTr]['shortHistoryFields'];
        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
        $dueDateAllow = isset($this->configUi[$jenisTr]['updateDueDate']) ? $this->configUi[$jenisTr]['updateDueDate'] : array();
        $mb = New MobileDetect();
        $isMob = $mb->isMobile();
        if ($isMob) {
            $historyFields = isset($this->configUi[$jenisTr]['compactHistoryFields']) ? $this->configUi[$jenisTr]['compactHistoryFields'] : array();
        }
        $arrExtHistoryFields2 = isset($this->configUi[$jenisTr]["extHistoryFields2"]) ? $this->configUi[$jenisTr]["extHistoryFields2"] : array();
        $glanceHistoryFields = isset($this->configUi[$jenisTr]['glanceHistoryFields']) ? $this->configUi[$jenisTr]['glanceHistoryFields'] : array("nomer" => "receipt no");
        $glanceHistoryFields2 = array("nomer" => "receipt no"); //init;
        $selectorProcessor = base_url() . $this->configUi[$jenisTr]['selectorProcessor'] . "/$jenisTr";

        //region lookup on-going transactions
        $arrayOnprogress = array();
        $progressFields = $historyFields;

        // $progressFields['jenis_label'] = "activity";
        $progressFields['oleh_nama'] = "started by";
        $progressFields['state'] = "status";
        $progressFields['action'] = "action";

        $steps = $this->configUi[$jenisTr]['steps'];

        $this->load->model("MdlTransaksi");
        $tr = new MdlTransaksi();

        if (sizeof($steps) > 1) {

            $stepCodes = array();
            $jmlStep = count($steps);
            if (isset($this->accessList[$jenisTr]) && sizeof($this->accessList) > 0) {
                $arrFilters = array();
                $indsteps = "(";
                foreach ($this->accessList[$jenisTr] as $stepNumber => $stepSpec) {
                    if ($stepNumber <= $jmlStep) {
                        foreach ($stepSpec as $targetCode => $filters) {
                            $indsteps .= "'$targetCode',";
                            $stepCodes[] = $targetCode;
                            if ($filters['allowFollowUp'] == "true") {
                                $arrFilters["allowFollowUp"][] = $targetCode;
                            }

                        }
                    }
                }
                $indsteps = rtrim($indsteps, ",");
                $indsteps .= ")";
                if (sizeof($arrFilters) > 0) {
                    $tr->addFilter("next_step_code in $indsteps");
                }
                else {
                    $tr->addFilter("transaksi.oleh_id='" . $this->session->login['id'] . "'");
                }
            }
            else {
                foreach ($steps as $stepNumber => $stepSpec) {
                    if ($stepNumber < $jmlStep) {
                        $stepCodes[] = $stepSpec['target'];
                    }

                }
            }

        }


        $tr->addFilter("div_id='" . $this->session->login['div_id'] . "'");
        $tr->addFilter("jenis_top='" . $steps[1]['target'] . "'");
        $tr->addFilter("next_substep_code<>''");
        $tr->addFilter("sub_step_number>0");
        $tr->addFilter("transaksi_data.trash=0");
        $tr->addFilter("transaksi_data.valid_qty>0");
        if (isset($this->accessList[$jenisTr]) && sizeof($this->accessList) > 0) {
        }
        else {
            if (sizeof($this->session->login['membership']) > 0) {
                $this->db->group_start();
                $mCtr = 0;
                foreach ($this->session->login['membership'] as $gID) {
                    if ($mCtr == 0) {
                        $this->db->where(array("next_subgroup_code" => $gID));

                    }
                    else {
                        $this->db->or_where(array("next_subgroup_code" => $gID));
                    }
                    $mCtr++;
                }
                $this->db->group_end();
            }
        }

        $tmpHist = $tr->lookupUndoneEntries_joined(replaceSession())->result();
        //cekLime($this->db->last_query());

        //region due date
        $tr->setFilters(array());
        $listedDue = $tr->lookupAllDueDate()->result();
        $arrDue = array();
        $overDue = array();
        foreach ($listedDue as $dueData) {

            $due_date = strtotime($dueData->due_date);
            $date_now = strtotime(date("Y-m-d"));
            if ($due_date < $date_now) {
                $arrDue[$dueData->transaksi_id] = array(
                    "due_date" => $dueData->due_date,
                    "aging" => umurDay($dueData->due_date),
                );
            }

        }
        //$this->debugArrPrint($arrDue);
        //$this->debugArrPrint($tmpHist);
        //endregion
        $arrOver_due = array();


        if (sizeof($tmpHist) > 0) {
            $arrTransID = array();
            $arrTransTopID = array();
            $arrIdsHist = array();
            $arrTransHist = array();
            foreach ($tmpHist as $row) {
                $arrTransID[] = $row->transaksi_id;
                $arrTransTopID[] = $row->id_top;

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
            $selectKolom = implode(",", $pairRegistries) . ", transaksi_id";
            // cekHitam($selectKolom);
            $trReg = new MdlTransaksi();
            $trReg->setFilters(array());
            //            $trReg->addFilter("param='main'");
            $trReg->setJointSelectFields($selectKolom);
            $trReg->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
            $tmpReg = $trReg->lookupDataRegistries()->result();


            if (sizeof($tmpReg) > 0) {
                foreach ($tmpReg as $regRow) {
                    //                    $this->debugArrPrint($regRow);
                    foreach ($regRow as $key_reg => $val_reg) {
                        if ($key_reg != "transaksi_id") {
                            $tmpReg_result[$regRow->transaksi_id][$key_reg] = blobDecode($val_reg);
                        }
                    }

                }
            }
            //            $this->debugArrPrint($tmpReg_result);
            //            mati_disini();
            if (sizeof($arrIdsHist) > 0) {
                $tr = new MdlTransaksi();
                $tr->setFilters(array());
                $tr->addFilter("id in ('" . implode("','", $arrTransHist) . "')");
                $tmpTransHist = $tr->lookupAll()->result();


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
            $numb = 0;
            foreach ($tmpHist as $row) {
                $step_current = $row->step_current;
                $extHistoryFields2 = isset($arrExtHistoryFields2[$row->step_number]) ? $arrExtHistoryFields2[$row->step_number] : array();

                if (sizeof($pairRegistries) > 0) {
                    if ((sizeof($tmpReg_result) > 0) && (isset($tmpReg_result[$row->transaksi_id]))) {
                        foreach ($tmpReg_result[$row->transaksi_id] as $param => $eReg) {
                            switch ($param) {
                                case "main":
                                    foreach ($eReg as $k => $v) {
                                        if (!isset($row->$k)) {
                                            $row->$k = $v;
                                        }
                                    }
                                    break;
                                case "items":
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
                                                    }
                                                    $row->$k1 = $tmpDetail;
                                                }
                                            }
                                        }
                                    }
                                    break;
                            }
                        }
                    }
                }

                $tmp = array();
                $numb++;

                foreach ($historyFields as $fName => $fLabel) {

                    if (isset($row->$fName)) {
                        if (is_numeric($row->$fName)) {
                            if (!isset($sumFooter[$fName])) {
                                $sumFooter[$fName] = 0;
                            }
                            $sumFooter[$fName] += $row->$fName;
                        }
                    }

                    if (is_array($fLabel)) {
                        $hisStep = $fLabel['step'];
                        $hisKey = $fLabel['key'];
                        //                                $tNomer = $id_hist[$hisStep][$hisKey];

                        if (isset($row->ids_his)) {
                            $returnVal = showHistoriGlobalNumbers($row->ids_his, $hisStep, true, $this->jenisTr);
                            if ($returnVal == "") {
                                $tmp[$fName] = "-";
                            }
                            else {
                                $tmp[$fName] = $returnVal;
                            }
                        }
                        else {
                            $tmp[$fName] = "-";
                        }
                    }
                    else {
                        $tmp[$fName] = isset($row->$fName) ? formatField_he_format($fName, $row->$fName) : formatField_he_format($fName, 0);
                    }

                    if ($fName == "no") {
                        $tmp[$fName] = formatField_he_format($fName, $numb);
                    }
                }

                if ($row->sub_step_number > 0) {
                    $tmp['state'] = createStateHorizontal($row->sub_step_number, $row->step_avail, $jenisTr);
                }
                else {
                    $tmp['state'] = "<span style='color:#777777'>canceled</span>";
                }


                $tmp['action'] = "";
                $nextStepNum = ($row->next_substep_num);
                $currentStepNum = ($row->sub_step_number);
                $nextStepCode = ($row->next_step_code);
                $allowJoin = isset($this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin']) && $this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin'] == true ? $this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin'] : false;


                $allowFollowup = false;
                if ($row->sub_step_number > 0) {
                    $allowFollowup = true;
                    $actionLabel = "review " . $this->configUi[$jenisTr]['steps'][$currentStepNum]['label'];
                }

                if (isset($this->configUi[$jenisTr]['steps'][$nextStepNum])) {
                    if (isset($this->accessList[$jenisTr])) {
                        if (isset($this->accessList[$jenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"])) {
                            $allowFollowup = $this->accessList[$jenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"];
                            $actionLabel = $this->configUi[$jenisTr]['steps'][$nextStepNum]['actionLabel'];
                        }
                    }
                    else {
                        if (placeCanFollowupTrans_he_menu($this->session->login['membership'], $this->session->login['cabang_id'], $this->session->login['gudang_id'], $this->jenisTr, $nextStepNum, "", $this->configUiJenis)) {
                            $allowFollowup = true;
                            $actionLabel = $this->configUi[$jenisTr]['steps'][$nextStepNum]['actionLabel'];

                        }
                    }

                }

                if ($allowFollowup) {
                    $stepLabel = $this->configUi[$jenisTr]['steps'][$nextStepNum]['label'];
                    $isCancelPacking = isset($this->configUi[$jenisTr]['steps'][$nextStepNum]['isCancelPacking']) ? $this->configUi[$jenisTr]['steps'][$nextStepNum]['isCancelPacking'] : false;
                    $allowCancel = isset($this->configUi[$jenisTr]['steps'][$nextStepNum]['allowCancel']) ? $this->configUi[$jenisTr]['steps'][$nextStepNum]['allowCancel'] : false;

                    $targetFollowupLink = $isCancelPacking == true ? "followupCancelPackingPrePreview" : "followupPrePreview";
                    $followupLink = "disableShopCart();top.$('#result').load('" . MODUL_PATH . "FollowUp/$targetFollowupLink/$jenisTr/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "');";

                    $tmp['action'] = "<div class='input-group'>";
                    $tmp['action'] .= "<a class='btn btn-primary btn-block' title='turn this entry into $stepLabel' data-toggle='tooltip' href='javascript:void(0)' onClick =\"top.open_holdon();$followupLink\"><span class='glyphicon glyphicon-ok'></span> " . $actionLabel . "</a>";
                    if ($allowJoin) {
                        $tmp['action'] .= "<span class='input-group-addon'>";
                        $tmp['action'] .= "<a title='process many items at once' href='" . MODUL_PATH . "Transaksi/viewIncomplete/" . $this->jenisTr . "/$currentStepNum'><span class='fa fa-dedent'></span></a>";
                        $tmp['action'] .= "</span class='input-group-addon'>";
                    }
                }

                $arrayOnprogress[] = $tmp;

            }
        }
        //endregion


        $sumFooterResult = array();
        if (isset($sumFooter) && sizeof($sumFooter) > 0) {
            foreach ($sumFooter as $key => $val) {
                $sumFooterResult[$key] = formatField_he_format($key, $val);
            }
        }

        $data = array(
            "mode" => "viewUndoneItems",
            "isMobile" => $isMob,
            "arrayProgressLabels" => $progressFields,
            "arrayProgress2Labels" => $glanceHistoryFields2,
            "arrayOnProgress" => $arrayOnprogress,
            "arrayOnProgress2" => isset($arrayOnprogress2) ? $arrayOnprogress2 : array(),
            "needToClear" => isset($needToClear) ? $needToClear : "",
            "reqFormTarget" => isset($reqFormTarget) ? $reqFormTarget : "",
            "allowMultiSelect" => isset($allowMultiSelect) ? $allowMultiSelect : "",
            "overDue" => $arrOver_due,
            "clearCartTarget" => MODUL_PATH . "/_shoppingCart/reset/" . $this->jenisTr,

            "sumFooter" => $sumFooterResult,
            "jenisTr" => $jenisTr,
        );

        $this->load->view("transaksi", $data);

    }

    public function viewUndoneItemsIndex_ori()
    {
        $starttime = microtime(true);
        if (!isset($this->session->login['id'])) {
            redirect(base_url() . "Login");
        }
        $scriptBottom = "";
        $sesionReplacer = replaceSession();
        //        $jenisTr = $this->uri->segment(3);
        $jenisTr = $this->jenisTr;
        $cCode = "_TR_" . $this->jenisTr;
        $paymentConfig = isset($this->configUi[$jenisTr]['paymentConfig']) ? $this->configUi[$jenisTr]['paymentConfig'] : false;
        $myPaymentConfig = isset($this->configUi[$jenisTr]['myPaymentConfig']) ? $this->configUi[$jenisTr]['myPaymentConfig'] : false;
        $historyFields = isset($this->configUi[$jenisTr]['shortHistoryFields']) ? $this->configUi[$jenisTr]['shortHistoryFields'] : array();
        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
        $connectTo = isset($this->configUi[$jenisTr]['connectTo']) ? $this->configUi[$jenisTr]['connectTo'] : "";
        $stepHistoryFields = isset($this->configUi[$jenisTr]['shortStepHistoryFields']) ? $this->configUi[$jenisTr]['shortStepHistoryFields'] : array();
        $arrExtHistoryFields2 = isset($this->configUi[$jenisTr]["extHistoryFields2"]) ? $this->configUi[$jenisTr]["extHistoryFields2"] : array();

        $mb = New MobileDetect();
        $isMob = $mb->isMobile();
        if ($isMob) {
            $historyFields = isset($this->configUi[$jenisTr]['compactHistoryFields']) ? $this->configUi[$jenisTr]['compactHistoryFields'] : array();
        }
        $prePreFields = $historyFields;
        $prePreFields['state'] = "status";
        $prePreFields['action'] = "action";
        $swapJenisTr = isset($this->configUi[$jenisTr]['requestCode']['masterCode']) ? $this->configUi[$jenisTr]['requestCode']['masterCode'] : array();
        $arrayOnprePre = array();
        $arrayOnprePreGroup = array();
        if (sizeof($swapJenisTr) > 0) {
            $steps = $this->configUi[$swapJenisTr]['steps'];
            $arrFilters = array();
            if (sizeof($steps) > 1) {
                $this->load->model("MdlTransaksi");
                $tr = new MdlTransaksi();
                $stepCodes = array();
                $jmlStep = count($steps);
                if (isset($this->accessList[$swapJenisTr]) && sizeof($this->accessList) > 0) {
                    $indsteps = "(";
                    foreach ($this->accessList[$swapJenisTr] as $stepNumber => $stepSpec) {
                        if ($stepNumber <= $jmlStep) {
                            foreach ($stepSpec as $targetCode => $filters) {
                                $indsteps .= "'$targetCode',";
                                $stepCodes[] = $targetCode;
                                if ($filters['allowFollowUp'] == "true") {
                                    $arrFilters["allowFollowUp"][] = $targetCode;
                                }
                            }
                        }
                    }
                    $indsteps = rtrim($indsteps, ",");
                    $indsteps .= ")";
                    if (sizeof($arrFilters) > 0) {
                        $tr->addFilter("next_step_code in $indsteps");
                    }
                    else {
                        $tr->addFilter("transaksi.oleh_id='" . $this->session->login['id'] . "'");
                    }
                }
                else {

                }
                $tr->addFilter("div_id='" . $this->session->login['div_id'] . "'");
                $tr->addFilter("jenis_top='" . $steps[1]['target'] . "'");
                $tr->addFilter("next_substep_code<>''");
                $tr->addFilter("sub_step_number>0");
                $tr->addFilter("valid_qty>0");
                $tmpHist = $tr->lookupRecentUndoneEntries_joined($sesionReplacer)->result();
                if (sizeof($tmpHist) > 0) {
                    $arrTransID = array();
                    $arrTransTopID = array();
                    $arrIdsHist = array();
                    $arrTransHist = array();
                    foreach ($tmpHist as $row) {
                        $arrTransID[] = $row->id;
                        $arrTransTopID[] = $row->id_top;

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
                    $selectKolom = implode(",", $pairRegistries) . ",transaksi_id";
                    // matiHEre(__LINE__);
                    $trReg->setJointSelectFields($selectKolom);
                    $trReg->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
                    $tmpReg = $trReg->lookupDataRegistries()->result();

                    if (sizeof($tmpReg) > 0) {
                        foreach ($tmpReg as $regRow) {
                            $param = $regRow->param;
                            $tmpReg_result[$regRow->transaksi_id][$param] = blobDecode($regRow->values);
                        }
                    }

                    foreach ($tmpHist as $row) {
                        if (sizeof($pairRegistries) > 0) {
                            //                            $trReg = new MdlTransaksi();
                            //                            $trReg->setFilters(array());
                            //                            $trReg->addFilter("param in ('" . implode("','", $pairRegistries) . "')");
                            //                            $trReg->addFilter("transaksi_id='" . $row->transaksi_id . "'");
                            //                            $tmpReg = $trReg->lookupRegistries()->result();
                            //                            if (sizeof($tmpReg) > 0) {
                            //                                foreach ($tmpReg as $regRow) {
                            //                                    $param = $regRow->param;
                            //                                    $$param = blobDecode($regRow->values);
                            //                                }
                            //                                foreach ($pairRegistries as $eReg) {
                            //                                    foreach ($$eReg as $k => $v) {
                            //                                        if (!isset($row->$k)) {
                            //                                            $row->$k = $v;
                            //                                        }
                            //                                    }
                            //                                }
                            //                            }
                            if ((sizeof($tmpReg_result) > 0) && (isset($tmpReg_result[$row->id]))) {
                                foreach ($tmpReg_result[$row->id] as $param => $eReg) {
                                    foreach ($eReg as $k => $v) {
                                        if (!isset($row->$k)) {
                                            $row->$k = $v;
                                        }
                                    }
                                }
                            }
                        }

                        $tmp = array();
                        foreach ($historyFields as $fName => $fLabel) {
                            $tmp[$fName] = isset($row->$fName) ? formatField($fName, $row->$fName) : formatField($fName, 0);
                        }

                        //                        cekHijau('$swapJenisTr: ' . $swapJenisTr);

                        $showPoStatus = isset($this->configUi[$swapJenisTr]['showPoStatus']) ? $this->configUi[$swapJenisTr]['showPoStatus'] : array();
                        $cekStateLocation = ($this->session->login['cabang_id'] > 0) ? "cabang" : "pusat";

                        if ($row->sub_step_number > 0) {

                            $tmp['state'] = "<div class='panel panel-warning' style='padding: 3px;margin-bottom: 5px;' ><span style='color:" . $this->configUi[$swapJenisTr]['steps'][$row->sub_step_number]['stateColor'] . "'>" . $this->configUi[$swapJenisTr]['steps'][$row->sub_step_number]['stateLabel'] . "</span>";
                            $tmp['state'] .= "<br>" . createStateSign($row->sub_step_number, $row->step_avail, $swapJenisTr) . "</div>";

                            // cek PO
                            $arrTransactionSource = array();
                            $id_master = $row->id_master;
                            $id_top1 = $row->id_top;
                            $this->load->model("MdlTransaksi");
                            $l = new MdlTransaksi();
                            $l->setFilters(array());
                            $l->addFilterJoin("transaksi_data.valid_qty>0");
                            $l->addFilter("id_master='" . $id_master . "'");
                            $l->addFilter("jenis_master='" . $swapJenisTr . "'");
                            $l->addFilter("link_id=0");
                            $tmpTS = $l->lookupJoined();

                            $arrsub_step_number = array();
                            $arrstep_avail = array();
                            $arrext_blob = array();
                            $arrjenis_master = array();
                            $arrtransaksi_no = array();
                            $arrketerangan = array();

                            if (sizeof($tmpTS) > 0) {
                                foreach ($tmpTS as $kk => $arVL) {
                                    $id = $arVL->id;
                                    $id_master = $arVL->id_master;
                                    $id_top2 = $arVL->id_top;
                                    $produk_id = $arVL->produk_id;
                                    $arrTransactionSource[$id_top1] = $produk_id;
                                    $arrsub_step_number[$id_top1] = $arVL->sub_step_number;
                                    $arrstep_avail[$id_top1] = $arVL->step_avail;
                                    $arrext_blob[$id_top1][$kk] = isset($arVL->ext_blob) ? ($arVL->ext_blob != "" ? blobDecode($arVL->ext_blob) : "") : "";
                                }

                                if (sizeof($arrext_blob[$id_top1]) > 0) {
                                    foreach ($arrext_blob[$id_top1] as $ky => $aVal) {
                                        if ($aVal != "") {
                                            foreach ($arrext_blob[$id_top1][$ky]['static'] as $numb => $numData) {
                                                $arrtransaksi_no[$id_top1][$numb] = formatField("nomer", $numb);
                                                $arrjenis_master[$id_top1][$numb] = $numData['jenis'];
                                                $arrketerangan[$id_top1][$numb] = $numData['keterangan'];
                                            }
                                        }
                                    }
                                }
                            }

                            if ($arrext_blob[$id_top1][$ky] != "") {
                                $tmp['state'] .= "<div class='panel panel-danger bg-green' style='padding: 3px;margin-bottom: 5px;'>";
                                if ($cekStateLocation == "cabang") {
                                    $tmp['state'] .= "<div><b>diproses oleh PUSAT</b></div>";
                                }
                                if (sizeof($arrtransaksi_no[$id_top1]) > 0) {
                                    foreach ($arrtransaksi_no[$id_top1] as $numb_) {
                                        $tmp['state'] .= "<div><span class='fa fa-check-circle text-warning'></span> " . $numb_ . "</div>";
                                    }
                                }
                                $tmp['state'] .= "</div>";
                            }

                        }
                        else {
                            $tmp['state'] = "<span style='color:#777777'>canceled</span>";
                        }

                        $tmp['action'] = "";
                        $nextStepNum = ($row->next_substep_num);
                        $currentStepNum = ($row->sub_step_number);
                        $currentStepCode = ($row->jenis);
                        $nextStepCode = ($row->next_step_code);

                        if ($row->sub_step_number > 0) {
                            $allowFollowup = true;
                            $actionLabel = "review " . $this->configUi[$swapJenisTr]['steps'][$currentStepNum]['label'];
                        }

                        if (isset($this->configUi[$swapJenisTr]['steps'][$nextStepNum])) {
                            if (isset($this->accessList[$jenisTr])) {
                                if (isset($this->accessList[$swapJenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"])) {
                                    $allowFollowup = $this->accessList[$swapJenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"];
                                    $actionLabel = $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['actionLabel'];
                                }
                            }
                            else {
                                if (in_array($this->configUi[$swapJenisTr]['steps'][$nextStepNum]['userGroup'], $this->session->login['membership'])) {
                                    $allowFollowup = true;
                                    $actionLabel = $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['actionLabel'];
                                }
                                else {
                                    $allowFollowup = true;
                                    $actionLabel = "review " . $this->configUi[$swapJenisTr]['steps'][$currentStepNum]['label'];
                                }
                            }
                        }

                        if ($allowFollowup) {
                            $allowJoin = isset($this->configUi[$swapJenisTr]["steps"][$nextStepNum]['allowJoin']) && $this->configUi[$swapJenisTr]["steps"][$nextStepNum]['allowJoin'] == true ? $this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin'] : false;
                            $stepLabel = isset($this->configUi[$swapJenisTr]['steps'][$nextStepNum]['label']) ? $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['label'] : "";
                            $isCancelPacking = isset($this->configUi[$swapJenisTr]['steps'][$nextStepNum]['isCancelPacking']) ? $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['isCancelPacking'] : false;
                            $allowCancel = isset($this->configUi[$swapJenisTr]['steps'][$nextStepNum]['allowCancel']) ? $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['allowCancel'] : false;

                            $targetFollowupLink = $isCancelPacking == true ? "followupCancelPackingPrePreview" : "followupPrePreview";
                            $followupLink = "top.$('#result').load('" . base_url() . "Transaksi/$targetFollowupLink/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "');";

                            $tmp['action'] = "<div class='input-group'>";
                            $tmp['action'] .= "<a class='btn btn-primary btn-block' title='turn this entry into $stepLabel' href='javascript:void(0)' onClick =\"top.open_holdon();$followupLink\">" . $actionLabel . "</a>";
                            if ($allowJoin) {
                                $tmp['action'] .= "<span class='input-group-addon'>";
                                $tmp['action'] .= "<a title='process many items at once' href='" . base_url() . "Transaksi/viewIncomplete/" . $this->jenisTr . "/$currentStepNum'><span class='fa fa-dedent'></span></a>";
                                $tmp['action'] .= "</span class='input-group-addon'>";
                            }
                            $tmp['action'] .= "</div class='input-group'>";

                            if ($allowCancel) {
                                $tmp['action'] .= "<div class='input-group'>";
                                $tmp['action'] .= "<a class='btn btn-primary btn-block' title='turn this entry into $stepLabel' href='javascript:void(0)' onClick =\"top.open_holdon();$followupLink\">" . $actionLabel . "</a>";
                                if ($allowJoin) {
                                    $tmp['action'] .= "<span class='input-group-addon'>";
                                    $tmp['action'] .= "<a title='process many items at once' href='" . base_url() . "Transaksi/viewIncomplete/" . $this->jenisTr . "/$currentStepNum'><span class='fa fa-dedent'></span></a>";
                                    $tmp['action'] .= "</span class='input-group-addon'>";
                                }
                                $tmp['action'] .= "</div class='input-group'>";
                            }
                        }
                        else {

                        }
                        $arrayOnprePre[] = $tmp;
                        $arrayOnprePreGroup[$currentStepNum][] = $tmp;
                    }
                }
            }
            else {
                $arrayOnprePre = array();
            }
        }
        else {
            $arrayOnprePre = array();
        }
        //endregion prePre


        //region lookup on-going transactions
        $progressFields = $historyFields;
        $progressFields['state'] = "status";
        $progressFields['action'] = "action";
        $steps = $this->configUi[$jenisTr]['steps'];

        $arrFilters = array();

        $this->load->model("MdlTransaksi");
        $tr = new MdlTransaksi();
        $stepCodes = array();
        $jmlStep = count($steps);

        if (isset($this->accessList[$jenisTr]) && sizeof($this->accessList) > 0) {
            $indsteps = "(";
            foreach ($this->accessList[$jenisTr] as $stepNumber => $stepSpec) {
                if ($stepNumber <= $jmlStep) {
                    foreach ($stepSpec as $targetCode => $filters) {

                        $stepCodes[] = $targetCode;
                        if ($filters['allowCreate'] == "true") {
                            //berarti step 1 boleh edit
                            $next = $stepNumber + 1;
                            $target2 = $steps[$next]["target"];
                            $indsteps .= "'$target2',";
                            $arrFilters["allowFollowUp"][] = $target2;
                        }
                        if ($filters['allowFollowUp'] == "true") {
                            $indsteps .= "'$targetCode',";
                            $arrFilters["allowFollowUp"][] = $targetCode;
                        }

                    }
//                    foreach ($stepSpec as $targetCode => $filters) {
//                        $indsteps .= "'$targetCode',";
//                        $stepCodes[] = $targetCode;
//                        if ($filters['allowFollowUp'] == "true") {
//                            $arrFilters["allowFollowUp"][] = $targetCode;
//                        }
//
//                    }
                }
            }
            $indsteps = rtrim($indsteps, ",");
            $indsteps .= ")";
            if (sizeof($arrFilters) > 0) {
                $tr->addFilter("next_step_code in $indsteps");
            }
            else {
                $tr->addFilter("transaksi.oleh_id='" . $this->session->login['id'] . "'");
                $tr->addFilter("next_step_code!=''");
            }
        }
        else {

        }

        $tr->addFilter("div_id='" . $this->session->login['div_id'] . "'");
        $tr->addFilter("jenis_top='" . $steps[1]['target'] . "'");
        $tr->addFilter("next_substep_code<>''");
        $tr->addFilter("sub_step_number>0");
        $tr->addFilter("valid_qty>0");
        if ($this->session->login['employee_type'] == "employee_freelance") {
            $tr->addFilter("seller_id='" . $this->session->login['id'] . "'");
        }

        $tmpHist = $tr->lookupRecentUndoneEntries_joined($sesionReplacer)->result();

        $arrayOnprogress = array();
        $arrayOnprogressGroup = array();
        $arrayOnprogressPartialMark = array();
        $arrayOnprogressGroupPartialMark = array();

        $extData = "";
        $extact = "";
        $selectProcessor = "";

        if (sizeof($tmpHist) > 0) {
            $arrTransID = array();
            $arrTransTopID = array();
            $arrIdsHist = array();
            $arrTransHist = array();
            $arrNextAction = array();
            foreach ($tmpHist as $row) {
                $arrTransID[] = $row->transaksi_id;
                $arrTransTopID[] = $row->id_top;
                $arrNextAction[$row->transaksi_id] = array(
                    "next_step_num" => $row->next_substep_num,
                    "next_step_code" => $row->next_substep_code,
                );
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
                //                    $this->debugArrPrint($row);
            }

            $tmpReg_result = array();
            if (sizeof($pairRegistries) > 0) {
                $selectKolom = implode(",", $pairRegistries) . ", transaksi_id";
                $trReg = new MdlTransaksi();
                $trReg->setFilters(array());
                //            $trReg->addFilter("param='main'");
                $trReg->setJointSelectFields($selectKolom);
                $trReg->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
                $tmpReg = $trReg->lookupDataRegistries()->result();
                if (sizeof($tmpReg) > 0) {
                    foreach ($tmpReg as $regRow) {
                        //                    $this->debugArrPrint($regRow);
                        foreach ($regRow as $key_reg => $val_reg) {
                            if ($key_reg != "transaksi_id") {
                                $tmpReg_result[$regRow->transaksi_id][$key_reg] = blobDecode($val_reg);
                            }
                        }

                    }
                }
            }


            $arrNextPIC = callNextPIC($arrNextAction);

            if (sizeof($arrIdsHist) > 0) {
                $tr = new MdlTransaksi();
                $tr->setFilters(array());
                $tr->addFilter("id in ('" . implode("','", $arrTransHist) . "')");
                $tmpTransHist = $tr->lookupAll()->result();


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

            $numb = 0;
            foreach ($tmpHist as $row) {
                //$this->debugArrPrint($row);
                $extHistoryFields2 = isset($arrExtHistoryFields2[$row->step_number]) ? $arrExtHistoryFields2[$row->step_number] : array();

                if (sizeof($pairRegistries) > 0) {
                    if ((sizeof($tmpReg_result) > 0) && (isset($tmpReg_result[$row->transaksi_id]))) {
                        foreach ($tmpReg_result[$row->transaksi_id] as $param => $eReg) {
                            switch ($param) {
                                case "main":
                                    foreach ($eReg as $k => $v) {
                                        if (!isset($row->$k)) {
                                            $row->$k = $v;
                                        }
                                    }
                                    break;
                                case "items":
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
                                                    }
                                                    $row->$k1 = $tmpDetail;
                                                }
                                            }
                                        }
                                    }
                                    break;
                            }
                        }
                    }
                }
                //$this->debugArrPrint($row);


                $numb++;

                $tmp = array();
                foreach ($historyFields as $fName => $fLabel) {
                    if (isset($row->$fName)) {
                        if (is_numeric($row->$fName)) {
                            if (!isset($sumFooter[$fName])) {
                                $sumFooter[$fName] = 0;
                            }
                            $sumFooter[$fName] += $row->$fName;
                        }
                    }

                    if (is_array($fLabel)) {
                        $hisStep = $fLabel['step'];
                        $hisKey = $fLabel['key'];

                        if (isset($row->ids_his)) {
                            if ($hisKey == "nomer") {
                                $returnVal = showHistoriGlobalNumbers($row->ids_his, $hisStep, true, $this->jenisTr);
                                if ($returnVal == "") {
                                    $tmp[$fName] = "-";
                                }
                                else {
                                    $tmp[$fName] = $returnVal;
                                }
                            }
                            else {
                                $ids_his_decode = blobDecode($row->ids_his);
                                if (isset($ids_his_decode[$hisStep][$hisKey])) {
                                    $tmp[$fName] = $ids_his_decode[$hisStep][$hisKey];
                                }
                                else {
                                    $tmp[$fName] = "-";
                                }
                            }
                        }
                        else {
                            $tmp[$fName] = "-";
                        }
                    }
                    else {
                        $tmp[$fName] = isset($row->$fName) ? formatField_he_format($fName, $row->$fName) : formatField_he_format($fName, 0);
                    }

                    if ($fName == "no") {
                        $tmp[$fName] = formatField_he_format($fName, $numb);
                    }

                }

                if (sizeof($row->cancel_packing_source_id) > 0) {
                    $this->debugArrPrint($row->cancel_packing_source_id);
                    $trx = new MdlTransaksi();
                    $trx->addFilter("id='" . $row->cancel_packing_source_id . "'");
                    $tmpTrx = $trx->lookupAll()->result();

                    //                        $tmp['nomer_top'] = $tmpTrx[0]->nomer;
                    $tmp['nomer_top'] = formatField_he_format("nomer_top", $tmpTrx[0]->nomer);
                    //                        $this->debugArrPrint($tmpTrx[0]->nomer);
                }


                if ($row->sub_step_number > 0) {
                    $tmp['state'] = "<span style='color:" . $this->configUi[$jenisTr]['steps'][$row->sub_step_number]['stateColor'] . "'>" . $this->configUi[$jenisTr]['steps'][$row->sub_step_number]['stateLabel'] . "</span>";
                    $tmp['state'] .= "<br>" . createStateSign($row->sub_step_number, $row->step_avail, $jenisTr);
                }
                else {
                    $tmp['state'] = "<span style='color:#777777'>canceled</span>";
                }


                $tmp['next_pic'] = "-";
                if (sizeof($arrNextPIC) > 0) {
                    if (isset($arrNextPIC[$row->next_substep_code][$row->next_substep_num])) {
                        $next_pic = "";
                        $nob = 1;
                        foreach ($arrNextPIC[$row->next_substep_code][$row->next_substep_num] as $spec) {
                            if ($row->cabang_id == $spec['cabang_id']) {

                                if ($next_pic == "") {
                                    $next_pic = "$nob. " . $spec['nama'];
                                }
                                else {
                                    $nob++;
                                    $next_pic = $next_pic . "<br>" . "$nob. " . $spec['nama'];
                                }

                            }

                        }
                        $tmp['next_pic'] = $next_pic;
                        //                            cekHitam($next_pic);
                    }
                }


                $tmp['action'] = "";
                $nextStepNum = ($row->next_substep_num);
                $currentStepNum = ($row->sub_step_number);
                $currentStepCode = ($row->jenis);
                $nextStepCode = ($row->next_step_code);


                if ($row->sub_step_number > 0) {
                    //                        cekHere("koq");
                    $allowFollowup = true;
                    $actionLabel = "review " . $this->configUi[$jenisTr]['steps'][$currentStepNum]['label'];
                }

                if (isset($this->configUi[$jenisTr]['steps'][$nextStepNum])) {
                    if (isset($this->accessList[$jenisTr])) {
                        if (isset($this->accessList[$jenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"])) {
                            $allowFollowup = $this->accessList[$jenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"];
                            $actionLabel = $this->configUi[$jenisTr]['steps'][$nextStepNum]['actionLabel'];
                        }


                    }
                    else {
                        if (in_array($this->configUi[$jenisTr]['steps'][$nextStepNum]['userGroup'], $this->session->login['membership'])) {
                            $allowFollowup = true;
                            $actionLabel = $this->configUi[$jenisTr]['steps'][$nextStepNum]['actionLabel'];
                        }
                        else {
                            $allowFollowup = true;
                            $actionLabel = "review " . $this->configUi[$jenisTr]['steps'][$currentStepNum]['label'];
                        }
                    }
                }
                $req_cancel_qty = $row->req_cancel_qty != '' ? $row->req_cancel_qty : 0;
                $valid_qty = $row->valid_qty != '' ? $row->valid_qty : 0;


                if ($allowFollowup) {
                    $allowJoin = isset($this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin']) && $this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin'] == true ? $this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin'] : false;
                    $stepLabel = isset($this->configUi[$jenisTr]['steps'][$nextStepNum]['label']) ? $this->configUi[$jenisTr]['steps'][$nextStepNum]['label'] : "";
                    $isCancelPacking = isset($this->configUi[$jenisTr]['steps'][$nextStepNum]['isCancelPacking']) ? $this->configUi[$jenisTr]['steps'][$nextStepNum]['isCancelPacking'] : false;
                    $allowCancel = isset($this->configUi[$jenisTr]['steps'][$nextStepNum]['allowCancel']) ? $this->configUi[$jenisTr]['steps'][$nextStepNum]['allowCancel'] : false;

                    $targetFollowupLink = $isCancelPacking == true ? "followupCancelPackingPrePreview" : "followupPrePreview";

                    $followupLink = "top.$('#result').load('" . MODUL_PATH . "FollowUp/$targetFollowupLink/$jenisTr/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "');";

                    $tmp['action'] = "<div class='input-group'>";
                    $tmp['action'] .= "<a class='btn btn-primary btn-block' title='turn this entry into $stepLabel' href='javascript:void(0)' 
                                onClick =\"top.open_holdon();$followupLink\">" . $actionLabel . "</a>";
                    //                        if ($allowJoin) {
                    //                            $tmp['action'] .= "<span class='input-group-addon'>";
                    //                            $tmp['action'] .= "<a title='process many items at once' href='" . base_url() . "Transaksi/viewIncomplete/" . $this->jenisTr . "/$currentStepNum'><span class='fa fa-dedent'></span></a>";
                    //                            $tmp['action'] .= "</span class='input-group-addon'>";
                    //                        }
                    $tmp['action'] .= "</div class='input-group'>";

                    if ($req_cancel_qty > 0 && $valid_qty == 0) {
                        $tmp['action'] = "<div class='btn-group' role='group' aria-label='cancel packing on progress'>";
                        $tmp['action'] .= "<button type='button' disabled class='btn btn-warning' title='sedang dalam process cancel packing' href='javascript:void(0)'>menuggu approve cancel</button>";
                        //                            $tmp['action'] .= "<button type='button' class='btn btn-warning' title='sedang dalam process cancel packing' href='javascript:void(0)'>x</button>";
                        $tmp['action'] .= "</div>";
                    }

                }
                else {

                }

                $tmpMark = array();
                $tmp['keterangan'] = "-";
                if ($row->partial == 1) {
                    $tmpMark['style'] = "background-color:yellow;";
                    $tmp['keterangan'] = "<span style='color:red;'>transaksi diproses sebagian</span>";
                }


                $arrayOnprogress[] = $tmp;
                $arrayOnprogressGroup[$currentStepNum][] = $tmp;

                $arrayOnprogressPartialMark[] = $tmpMark;
                $arrayOnprogressGroupPartialMark[$currentStepNum][] = $tmpMark;

                //                    break;
            }
        }


        //endregion


        //region link to add new transaction
        if (placeCanMakeTrans_he_menu($this->session->login['membership'], $this->session->login['cabang_id'], $this->session->login['gudang_id'], $this->jenisTr, $this->configUiJenis)) {
            $createIndexes = (null != $this->config->item("transaksi_createIndex")) ? $this->config->item("transaksi_createIndex") : array();
            $isDisableMakeTrans = isset($this->configUi[$this->jenisTr]['isDisableMakeTrans']) ? $this->configUi[$this->jenisTr]['isDisableMakeTrans'] : false;
            if (array_key_exists($this->jenisTr, $createIndexes)) {
                $targetUrl = MODUL_PATH . $createIndexes[$this->jenisTr] . "/" . $this->jenisTr;
            }
            else {
                $targetUrl = MODUL_PATH . "Create/index/" . $this->jenisTr;
            }

            if ($isDisableMakeTrans) {
                $addLink = null;
            }
            else {
                $addLink = array(
                    "link" => $targetUrl,
                    "label" => "<span class='glyphicon glyphicon-plus'></span> create new " . $this->configUi[$jenisTr]["steps"][1]['label'],
                );
            }

        }
        else {
            $addLink = null;
        }
        //endregion

        $jenisTrsub = isset($_GET['step']) ? $_GET['step'] : 1;
        $historyFieldsDt = isset($this->configUi[$jenisTr]['historyFields'][$jenisTrsub]) ? $this->configUi[$jenisTr]['historyFields'][$jenisTrsub] : $this->configUi[$jenisTr]['shortHistoryFields'];
        $availDbTable = $tr->getAvailTable($historyFieldsDt);

        //-----------------------------------------------------
        $arrJenisTrCek = array("587", "687", "1587", "1687");
        if (in_array($jenisTr, $arrJenisTrCek)) {
            $transaksiName = isset($this->configUi[$jenisTr]['label']) ? $this->configUi[$jenisTr]['label'] : NULL;
            $subplace = isset($this->configUi[$jenisTr]['steps'][1]['subplace']) ? $this->configUi[$jenisTr]['steps'][1]['subplace'] : NULL;
            if ($subplace != NULL) {
                if (($subplace == "warehouse") && ($this->session->login['gudang_id'] < 0)) {
                    //                    cekPink("SESUAI");
                    $msg = NULL;
                }
                elseif (($subplace == "warehouse_ng") && ($this->session->login['gudang_id'] > 0)) {
                    //                    cekPink("SESUAI NOT GOOD");
                    $msg = NULL;
                }
                else {
                    $msg = "Anda tidak memiliki kewenangan untuk membuat request $transaksiName";
                    $addLink = array();
                }
            }
        }

        //-----------------------------------------------------
        $endtime = microtime(true); // Bottom of page
        $val = $endtime - $starttime;
        //        cekMerah("Grabing data " . $val . " Seconds");

        //region prepare params to viewer
        $data = array(
            "mode" => isset($this->configUi[$jenisTr]["mode"]) ? $this->configUi[$jenisTr]["mode"] : $this->uri->segment(3),
            "isMobile" => $isMob,
            "errMsg" => $this->session->errMsg,
            "template" => $this->configUi[$jenisTr]["template"],
            "title" => $this->configUi[$jenisTr]["label"],
            "subTitle" => $this->configUi[$jenisTr]["steps"][1]['label'],
            "jenisTr" => $jenisTr,
            "trName" => $this->configUi[$jenisTr]["label"],
            'addLink' => $addLink,
            "allSteps" => $this->allSteps,
            "historyTitle" => "<span class='glyphicon glyphicon-time'></span> recent " . $this->configUi[$jenisTr]["label"] . " histories",
            "arrayHistoryLabels" => array("dtime" => "time") + $historyFields,
            "arrayHistoryLabelsDt" => $historyFieldsDt,
            "availDbTable" => $availDbTable,
            "arrayHistory" => isset($arrayHistory) ? $arrayHistory : array(),
            "onprogressTitle" => "<span class='glyphicon glyphicon-alert'></span> TRANSAKSI YANG PERLU ACTION ",
            "arrayProgressLabels" => $progressFields,
            "arrayOnProgressToPay" => $paymentConfig,
            "itemLabels" => isset($itemLabels) ? $itemLabels : "",
            "srcLabel" => isset($srcLabel) ? $srcLabel : "",
            //            "arrayOnProgress" => array_merge($arrayOnprogress, $arrayOnprePre, $arrayOnpreDistribution),
            "steps" => $steps,
            "arrayOnProgress" => (isset($arrayOnprogress) && sizeof($arrayOnprogress) > 0) ? $arrayOnprogress : array(),
            "arrayOnprogressGroup" => (isset($arrayOnprogressGroup) && (sizeof($arrayOnprogressGroup) > 0)) ? $arrayOnprogressGroup : array(),

            "arrayOnprePre" => $arrayOnprePre,
            "arrayOnprePreGroup" => $arrayOnprePreGroup,

            "arrayOnpreDistribution" => isset($arrayOnpreDistribution) ? $arrayOnpreDistribution : array(),
            "arrayOnpreDistributionGroup" => isset($arrayOnpreDistributionGroup) ? $arrayOnpreDistributionGroup : array(),

            "entities" => isset($entities) ? $entities : array(),
            "recapTitle" => "<span class='fa fa-newspaper-o'></span> today " . $this->configUi[$jenisTr]["label"] . " reports",
            "arrayRecapLabels" => isset($recapLabels) ? $recapLabels : array(),
            "arrayRecap" => isset($arrayRecap) ? $arrayRecap : array(),

            "onprogressViewTitle" => "<span class='fa fa-eye'></span> show incomplete step " . $this->configUi[$jenisTr]["label"],
            "onprogressViewSubTitle" => "<span class='text-black'>(daftar transaksi yang masih stanby di cabang tujuan)</span>",
            "arrayOnProgressView" => isset($arrayOnProgressView) ? $arrayOnProgressView : array(),
            "stepHistoryFields" => isset($stepHistoryFields) ? $stepHistoryFields : array(),

            "selectProcessor" => $selectProcessor,
            "sumFooter" => (isset($sumFooterResult) && sizeof($sumFooterResult) > 0) ? $sumFooter : array(),
            "scriptBottom" => $scriptBottom,
            "btnLabel" => $extData,
            "actionTarget" => $extact,
            "sumFooter" => isset($sumFooter) ? $sumFooter : "",

            "arrayOnprogressPartialMark" => (isset($arrayOnprogressPartialMark) && sizeof($arrayOnprogressPartialMark) > 0) ? $arrayOnprogressPartialMark : array(),
            "arrayOnprogressGroupPartialMark" => (isset($arrayOnprogressGroupPartialMark) && sizeof($arrayOnprogressGroupPartialMark) > 0) ? $arrayOnprogressGroupPartialMark : array(),

            "defaultItemTrgEditable" => isset($defaultItemTrgEditable) ? $defaultItemTrgEditable : array(),
            "editItemTrg" => MODUL_PATH . "_followupLiveEdit/editEfaktur/" . $this->jenisTr . "/",
        );
        //endregion
        $this->load->view("transaksi", $data);
    }

    /*---------dgn paging php-------*/
    public function viewUndoneItemsIndex()
    {
        $stepper = isset($_GET['step']) ? $_GET['step'] : 1;
        $getDate = isset($_GET['date']) ? $_GET['date'] : NULL;
        if ($getDate != NULL) {
            $getDate_ex = explode("-", $getDate);
            $date_tahun = $getDate_ex[0];
            $date_bulan = $getDate_ex[1];
        }

        $starttime = microtime(true);
        if (!isset($this->session->login['id'])) {
            redirect(base_url() . "Login");
        }
        $scriptBottom = "";
        $sesionReplacer = replaceSession();
        $jenisTr = $this->jenisTr;
        $cCode = "_TR_" . $this->jenisTr;
        $paymentConfig = isset($this->configUi[$jenisTr]['paymentConfig']) ? $this->configUi[$jenisTr]['paymentConfig'] : false;
        $myPaymentConfig = isset($this->configUi[$jenisTr]['myPaymentConfig']) ? $this->configUi[$jenisTr]['myPaymentConfig'] : false;
        $historyFields = isset($this->configUi[$jenisTr]['shortHistoryFields']) ? $this->configUi[$jenisTr]['shortHistoryFields'] : array();

        $historyFields2 = isset($this->configUi[$jenisTr]['shortHistoryFields2']) ? $this->configUi[$jenisTr]['shortHistoryFields2'] : array();

        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
        $connectTo = isset($this->configUi[$jenisTr]['connectTo']) ? $this->configUi[$jenisTr]['connectTo'] : "";
        $stepHistoryFields = isset($this->configUi[$jenisTr]['shortStepHistoryFields']) ? $this->configUi[$jenisTr]['shortStepHistoryFields'] : array();
        $arrExtHistoryFields2 = isset($this->configUi[$jenisTr]["extHistoryFields2"]) ? $this->configUi[$jenisTr]["extHistoryFields2"] : array();
        $filterCustomerNpwp = isset($this->configUi[$jenisTr]["filterCustomerNpwp"]) ? $this->configUi[$jenisTr]["filterCustomerNpwp"] : array();
        $addMainSource = isset($this->configUi[$jenisTr]["addMainSource"]) ? $this->configUi[$jenisTr]["addMainSource"] : array();
        $inputFakturTransaksi = isset($this->configUi[$jenisTr]["inputFakturTransaksi"]) ? $this->configUi[$jenisTr]["inputFakturTransaksi"] : array();
        $arrItemShow = isset($this->configUi[$jenisTr]["shortItemsFields"]) ? $this->configUi[$jenisTr]["shortItemsFields"] : array();
        $gunggungFieldsView = (isset($this->configUi[$jenisTr]["gunggungFields"]) && (count($this->configUi[$jenisTr]["gunggungFields"]) > 0)) ? true : false;

        $mb = New MobileDetect();
        $isMob = $mb->isMobile();
        if ($isMob) {
            $historyFields = isset($this->configUi[$jenisTr]['compactHistoryFields']) ? $this->configUi[$jenisTr]['compactHistoryFields'] : array();
        }
        $prePreFields = $historyFields;
        $prePreFields['state'] = "status";
        $prePreFields['action'] = "action";
        $swapJenisTr = isset($this->configUi[$jenisTr]['requestCode']['masterCode']) ? $this->configUi[$jenisTr]['requestCode']['masterCode'] : array();
        $arrayOnprePre = array();
        $arrayOnprePreGroup = array();
        if (sizeof($swapJenisTr) > 0) {
            $steps = $this->configUi[$swapJenisTr]['steps'];
            $arrFilters = array();
            if (sizeof($steps) > 1) {
                $this->load->model("MdlTransaksi");
                $tr = new MdlTransaksi();
                $stepCodes = array();
                $jmlStep = count($steps);
                if (isset($this->accessList[$swapJenisTr]) && sizeof($this->accessList) > 0) {
                    $indsteps = "(";
                    foreach ($this->accessList[$swapJenisTr] as $stepNumber => $stepSpec) {
                        if ($stepNumber <= $jmlStep) {
                            foreach ($stepSpec as $targetCode => $filters) {
                                $indsteps .= "'$targetCode',";
                                $stepCodes[] = $targetCode;
                                if ($filters['allowFollowUp'] == "true") {
                                    $arrFilters["allowFollowUp"][] = $targetCode;
                                }
                            }
                        }
                    }
                    $indsteps = rtrim($indsteps, ",");
                    $indsteps .= ")";
                    if (sizeof($arrFilters) > 0) {
                        $tr->addFilter("next_step_code in $indsteps");
                    }
                    else {
                        $tr->addFilter("transaksi.oleh_id='" . $this->session->login['id'] . "'");
                    }
                }
                else {

                }
                $tr->addFilter("div_id='" . $this->session->login['div_id'] . "'");
                $tr->addFilter("jenis_top='" . $steps[1]['target'] . "'");
                $tr->addFilter("next_substep_code<>''");
                $tr->addFilter("sub_step_number>0");
                $tr->addFilter("valid_qty>0");
                $tmpHist = $tr->lookupRecentUndoneEntries_joined($sesionReplacer)->result();
                $this->debugShowLastQuery("biru");
                matiDisini(__LINE__);
                if (sizeof($tmpHist) > 0) {
                    $arrTransID = array();
                    $arrTransTopID = array();
                    $arrIdsHist = array();
                    $arrTransHist = array();
                    foreach ($tmpHist as $row) {
                        $arrTransID[] = $row->id;
                        $arrTransTopID[] = $row->id_top;

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
                    $selectKolom = implode(",", $pairRegistries) . ",transaksi_id";
                    // matiHEre(__LINE__);
                    $trReg->setJointSelectFields($selectKolom);
                    $trReg->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
                    $tmpReg = $trReg->lookupDataRegistries()->result();

                    if (sizeof($tmpReg) > 0) {
                        foreach ($tmpReg as $regRow) {
                            $param = $regRow->param;
                            $tmpReg_result[$regRow->transaksi_id][$param] = blobDecode($regRow->values);
                        }
                    }

                    foreach ($tmpHist as $row) {
                        if (sizeof($pairRegistries) > 0) {
                            //                            $trReg = new MdlTransaksi();
                            //                            $trReg->setFilters(array());
                            //                            $trReg->addFilter("param in ('" . implode("','", $pairRegistries) . "')");
                            //                            $trReg->addFilter("transaksi_id='" . $row->transaksi_id . "'");
                            //                            $tmpReg = $trReg->lookupRegistries()->result();
                            //                            if (sizeof($tmpReg) > 0) {
                            //                                foreach ($tmpReg as $regRow) {
                            //                                    $param = $regRow->param;
                            //                                    $$param = blobDecode($regRow->values);
                            //                                }
                            //                                foreach ($pairRegistries as $eReg) {
                            //                                    foreach ($$eReg as $k => $v) {
                            //                                        if (!isset($row->$k)) {
                            //                                            $row->$k = $v;
                            //                                        }
                            //                                    }
                            //                                }
                            //                            }
                            if ((sizeof($tmpReg_result) > 0) && (isset($tmpReg_result[$row->id]))) {
                                foreach ($tmpReg_result[$row->id] as $param => $eReg) {
                                    foreach ($eReg as $k => $v) {
                                        if (!isset($row->$k)) {
                                            $row->$k = $v;
                                        }
                                    }
                                }
                            }
                        }

                        $tmp = array();
                        foreach ($historyFields as $fName => $fLabel) {
                            $tmp[$fName] = isset($row->$fName) ? formatField($fName, $row->$fName) : formatField($fName, 0);
                        }

                        //                        cekHijau('$swapJenisTr: ' . $swapJenisTr);

                        $showPoStatus = isset($this->configUi[$swapJenisTr]['showPoStatus']) ? $this->configUi[$swapJenisTr]['showPoStatus'] : array();
                        $cekStateLocation = ($this->session->login['cabang_id'] > 0) ? "cabang" : "pusat";

                        if ($row->sub_step_number > 0) {

                            $tmp['state'] = "<div class='panel panel-warning' style='padding: 3px;margin-bottom: 5px;' ><span style='color:" . $this->configUi[$swapJenisTr]['steps'][$row->sub_step_number]['stateColor'] . "'>" . $this->configUi[$swapJenisTr]['steps'][$row->sub_step_number]['stateLabel'] . "</span>";
                            $tmp['state'] .= "<br>" . createStateSign($row->sub_step_number, $row->step_avail, $swapJenisTr) . "</div>";

                            // cek PO
                            $arrTransactionSource = array();
                            $id_master = $row->id_master;
                            $id_top1 = $row->id_top;
                            $this->load->model("MdlTransaksi");
                            $l = new MdlTransaksi();
                            $l->setFilters(array());
                            $l->addFilterJoin("transaksi_data.valid_qty>0");
                            $l->addFilter("id_master='" . $id_master . "'");
                            $l->addFilter("jenis_master='" . $swapJenisTr . "'");
                            $l->addFilter("link_id=0");
                            $tmpTS = $l->lookupJoined();

                            $arrsub_step_number = array();
                            $arrstep_avail = array();
                            $arrext_blob = array();
                            $arrjenis_master = array();
                            $arrtransaksi_no = array();
                            $arrketerangan = array();

                            if (sizeof($tmpTS) > 0) {
                                foreach ($tmpTS as $kk => $arVL) {
                                    $id = $arVL->id;
                                    $id_master = $arVL->id_master;
                                    $id_top2 = $arVL->id_top;
                                    $produk_id = $arVL->produk_id;
                                    $arrTransactionSource[$id_top1] = $produk_id;
                                    $arrsub_step_number[$id_top1] = $arVL->sub_step_number;
                                    $arrstep_avail[$id_top1] = $arVL->step_avail;
                                    $arrext_blob[$id_top1][$kk] = isset($arVL->ext_blob) ? ($arVL->ext_blob != "" ? blobDecode($arVL->ext_blob) : "") : "";
                                }

                                if (sizeof($arrext_blob[$id_top1]) > 0) {
                                    foreach ($arrext_blob[$id_top1] as $ky => $aVal) {
                                        if ($aVal != "") {
                                            foreach ($arrext_blob[$id_top1][$ky]['static'] as $numb => $numData) {
                                                $arrtransaksi_no[$id_top1][$numb] = formatField("nomer", $numb);
                                                $arrjenis_master[$id_top1][$numb] = $numData['jenis'];
                                                $arrketerangan[$id_top1][$numb] = $numData['keterangan'];
                                            }
                                        }
                                    }
                                }
                            }

                            if ($arrext_blob[$id_top1][$ky] != "") {
                                $tmp['state'] .= "<div class='panel panel-danger bg-green' style='padding: 3px;margin-bottom: 5px;'>";
                                if ($cekStateLocation == "cabang") {
                                    $tmp['state'] .= "<div><b>diproses oleh PUSAT</b></div>";
                                }
                                if (sizeof($arrtransaksi_no[$id_top1]) > 0) {
                                    foreach ($arrtransaksi_no[$id_top1] as $numb_) {
                                        $tmp['state'] .= "<div><span class='fa fa-check-circle text-warning'></span> " . $numb_ . "</div>";
                                    }
                                }
                                $tmp['state'] .= "</div>";
                            }

                        }
                        else {
                            $tmp['state'] = "<span style='color:#777777'>canceled</span>";
                        }

                        $tmp['action'] = "";
                        $nextStepNum = ($row->next_substep_num);
                        $currentStepNum = ($row->sub_step_number);
                        $currentStepCode = ($row->jenis);
                        $nextStepCode = ($row->next_step_code);

                        if ($row->sub_step_number > 0) {
                            $allowFollowup = true;
                            $actionLabel = "review " . $this->configUi[$swapJenisTr]['steps'][$currentStepNum]['label'];
                        }

                        if (isset($this->configUi[$swapJenisTr]['steps'][$nextStepNum])) {
                            if (isset($this->accessList[$jenisTr])) {
                                if (isset($this->accessList[$swapJenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"])) {
                                    $allowFollowup = $this->accessList[$swapJenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"];
                                    $actionLabel = $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['actionLabel'];
                                }
                            }
                            else {
                                if (in_array($this->configUi[$swapJenisTr]['steps'][$nextStepNum]['userGroup'], $this->session->login['membership'])) {
                                    $allowFollowup = true;
                                    $actionLabel = $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['actionLabel'];
                                }
                                else {
                                    $allowFollowup = true;
                                    $actionLabel = "review " . $this->configUi[$swapJenisTr]['steps'][$currentStepNum]['label'];
                                }
                            }
                        }

                        if ($allowFollowup) {
                            $allowJoin = isset($this->configUi[$swapJenisTr]["steps"][$nextStepNum]['allowJoin']) && $this->configUi[$swapJenisTr]["steps"][$nextStepNum]['allowJoin'] == true ? $this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin'] : false;
                            $stepLabel = isset($this->configUi[$swapJenisTr]['steps'][$nextStepNum]['label']) ? $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['label'] : "";
                            $isCancelPacking = isset($this->configUi[$swapJenisTr]['steps'][$nextStepNum]['isCancelPacking']) ? $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['isCancelPacking'] : false;
                            $allowCancel = isset($this->configUi[$swapJenisTr]['steps'][$nextStepNum]['allowCancel']) ? $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['allowCancel'] : false;

                            $targetFollowupLink = $isCancelPacking == true ? "followupCancelPackingPrePreview" : "followupPrePreview";
                            $followupLink = "top.$('#result').load('" . base_url() . "Transaksi/$targetFollowupLink/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "');";

                            $tmp['action'] = "<div class='input-group'>";
                            $tmp['action'] .= "<a class='btn btn-primary btn-block' title='turn this entry into $stepLabel' href='javascript:void(0)' onClick =\"top.open_holdon();$followupLink\">" . $actionLabel . "</a>";
                            if ($allowJoin) {
                                $tmp['action'] .= "<span class='input-group-addon'>";
                                $tmp['action'] .= "<a title='process many items at once' href='" . base_url() . "Transaksi/viewIncomplete/" . $this->jenisTr . "/$currentStepNum'><span class='fa fa-dedent'></span></a>";
                                $tmp['action'] .= "</span class='input-group-addon'>";
                            }
                            $tmp['action'] .= "</div class='input-group'>";

                            if ($allowCancel) {
                                $tmp['action'] .= "<div class='input-group'>";
                                $tmp['action'] .= "<a class='btn btn-primary btn-block' title='turn this entry into $stepLabel' href='javascript:void(0)' onClick =\"top.open_holdon();$followupLink\">" . $actionLabel . "</a>";
                                if ($allowJoin) {
                                    $tmp['action'] .= "<span class='input-group-addon'>";
                                    $tmp['action'] .= "<a title='process many items at once' href='" . base_url() . "Transaksi/viewIncomplete/" . $this->jenisTr . "/$currentStepNum'><span class='fa fa-dedent'></span></a>";
                                    $tmp['action'] .= "</span class='input-group-addon'>";
                                }
                                $tmp['action'] .= "</div class='input-group'>";
                            }
                        }
                        else {

                        }
                        $arrayOnprePre[] = $tmp;
                        $arrayOnprePreGroup[$currentStepNum][] = $tmp;
                    }
                }
            }
            else {
                $arrayOnprePre = array();
            }
        }
        else {
            $arrayOnprePre = array();
        }

        $addData = array();
        if (sizeof($filterCustomerNpwp) > 0) {
            if (isset($filterCustomerNpwp["enabled"]) && ($filterCustomerNpwp["enabled"] == true)) {
                $mdlName = $filterCustomerNpwp["mdlName"];
                $this->load->model("Mdls/$mdlName");
                $mm = New $mdlName();
                $mm->setFilters();
                $mmTmp = $mm->lookupAll()->result();
                if (sizeof($mmTmp) > 0) {
                    foreach ($mmTmp as $mmSpec) {
                        $addData[$mmSpec->id] = (array)$mmSpec;
                    }
                }
            }
        }

        //region lookup on-going transactions
        $progressFields = $stepper == "pre_gunggungan" ? $historyFields2 : $historyFields;
        $progressFields['state'] = "status";
        $progressFields['action'] = "action";

        $steps = $this->configUi[$jenisTr]['steps'];

        $arrFilters = array();
        $this->load->model("MdlTransaksi");
        $tr = new MdlTransaksi();
        $stepCodes = array();
        $jmlStep = count($steps);
arrPrint($this->accessList[$jenisTr]);
        if (isset($this->accessList[$jenisTr]) && sizeof($this->accessList) > 0) {
            $indsteps = "(";
            foreach ($this->accessList[$jenisTr] as $stepNumber => $stepSpec) {
                if ($stepNumber <= $jmlStep) {
                    foreach ($stepSpec as $targetCode => $filters) {

                        $stepCodes[] = $targetCode;
                        if ($filters['allowCreate'] == "true") {
                            //berarti step 1 boleh edit
                            $next = $stepNumber + 1;
                            $target2 = $steps[$next]["target"];
                            $indsteps .= "'$target2',";
                            $arrFilters["allowFollowUp"][] = $target2;
                        }
                        if ($filters['allowFollowUp'] == "true") {
                            $indsteps .= "'$targetCode',";
                            $arrFilters["allowFollowUp"][] = $targetCode;
                        }

                    }
                    //                    foreach ($stepSpec as $targetCode => $filters) {
                    //                        $indsteps .= "'$targetCode',";
                    //                        $stepCodes[] = $targetCode;
                    //                        if ($filters['allowFollowUp'] == "true") {
                    //                            $arrFilters["allowFollowUp"][] = $targetCode;
                    //                        }
                    //
                    //                    }
                }
            }
            $indsteps = rtrim($indsteps, ",");
            $indsteps .= ")";
            if (sizeof($arrFilters) > 0) {
                $tr->addFilter("next_step_code in $indsteps");
            }
            else {
                $tr->addFilter("transaksi.oleh_id='" . $this->session->login['id'] . "'");
                $tr->addFilter("next_step_code!=''");
            }
        }
        else {

        }

        $tr->addFilter("div_id='" . $this->session->login['div_id'] . "'");
        $tr->addFilter("jenis_top='" . $steps[1]['target'] . "'");
        $tr->addFilter("next_substep_code<>''");
        $tr->addFilter("sub_step_number>0");
        $tr->addFilter("valid_qty>0");
        if ($this->session->login['employee_type'] == "employee_freelance") {
            $tr->addFilter("seller_id='" . $this->session->login['id'] . "'");
        }

        if ($getDate != NULL) {
            $date_tahun = $getDate_ex[0];
            $date_bulan = $getDate_ex[1];
            $tr->addFilter("year(transaksi.dtime)='$date_tahun'");
            $tr->addFilter("month(transaksi.dtime)='$date_bulan'");
        }


        /* -----------------------------------------------------------------------------------
         * filtering data
         * -----------------------------------------------------------------------------------*/
        $gets = $_GET;
        unset($gets['page']);
        $xtraHref = http_build_query($gets);
        $limitDb = url_segment(5);
        $urutanDb = url_segment(6);
        /* --------------------------------------- ------------------------------------------
         * urutan dan limit
         * -----------------------------------------------------------------------------------*/
        if (isset($limitDb)) {
            $this->db->limit($limitDb);
        }
        if (isset($urutanDb)) {
            $this->db->order_by("transaksi.id", "$urutanDb");
        }
        // ------------------------------------------------------------------------------
        if (isset($_GET['kw']) && trim($_GET['kw']) !== '') {
            $_GET['kw'] = str_replace(array('INVPOS.', 'INVPOS'), array('5822r.', '5822r'), $_GET['kw']);
            $kw = trim($_GET['kw']);
            $tr->setKeyWord($kw);
            $words = explode(" ", $kw);
            $arrKolomSearch = array(
                "transaksi.customers_nama",
                "transaksi.oleh_nama",
                "transaksi.suppliers_nama",
                "transaksi.nomer",
                "transaksi.nomer_top",
                "transaksi.nomer2",
                "per_customers.npwp"
            );
            foreach ($words as $word) {
                $word = trim($word);
                if ($word !== '') {
                    $likeClauses = array();
                    foreach ($arrKolomSearch as $col) {
                        $likeClauses[] = "$col LIKE '%" . $this->db->escape_like_str($word) . "%'";
                    }
                    $tr->addFilter("(" . implode(" OR ", $likeClauses) . ")");
                }
            }
        }
        else if (isset($this->keyWord)) {
            $key = isset($this->keyWord) ? $this->keyWord : "";
            $this->createSmartSearch($key, array("transaksi.customers_nama", "transaksi.oleh_nama", "transaksi.suppliers_nama"));
        }
        // ------------------------------------------------------------------------------


        //ini cuma tricky yakk
        if (isset($_GET['step']) && $_GET['step'] == "pre_gunggungan") {
//            $tr->addFilter("valid_qty=pre_gunggungan");
            $tmpHist_0 = $tr->lookupRecentUndoneEntries_joined($sesionReplacer, 0)->result();
        }
        else {
            $tmpHist_0 = $tr->lookupRecentUndoneEntries_joined($sesionReplacer)->result();
        }
//        // $this->debugShowLastQuery("kuning");

        $arrayOnprogress = array();
        $arrayOnprogressGroup = array();
        $arrayOnprogressGroupNonNpwp = array();
        $arrayOnprogressPartialMark = array();
        $arrayOnprogressGroupPartialMark = array();
        $arrayOnprogressGunggungMark = array();

        foreach ($tmpHist_0 as $item) {
//            $transaksi_id = $item->transaksi_id;
//            cekHere("[$transaksi_id]");

            $currentStepNum = $item->sub_step_number;
            $npwp = $item->npwp;
            if ($item->step_number == 1) {
                if (sizeof($filterCustomerNpwp) > 0) {
                    if (isset($filterCustomerNpwp["enabled"]) && ($filterCustomerNpwp["enabled"] == true)) {
                        if (isset($addData[$item->customers_id]["npwp"]) && ($addData[$item->customers_id]["npwp"] != NULL)) {
                            $arrayOnprogressGroup[$currentStepNum][] = $item;
                        }
                        else {
                            $arrayOnprogressGroupNonNpwp[$currentStepNum][] = $item;
                            $arrayOnprogressGroup[$currentStepNum][] = $item;
                        }
                    }
                    else {
                        $arrayOnprogressGroup[$currentStepNum][] = $item;
                    }
                }
                else {
                    $arrayOnprogressGroup[$currentStepNum][] = $item;
                }
            }
            else {
                $arrayOnprogressGroup[$currentStepNum][] = $item;
            }
        }

        $extData = "";
        $extact = "";
        $selectProcessor = "";


        // -------------------------------------------------------paging
        $segmen = url_segment();
        $segmenUrl = implode("/", $segmen);
        $step = isset($_GET['step']) ? $_GET['step'] : "1";
        $gr = isset($_GET['gr']) ? $_GET['gr'] : "";

        $items = $arrayOnprogressGroup[$step];
        $itemJml = count($items);
        $jmlPerhalaman = 200;

        $this->load->library("pagination");
        $pg = new Pagination();

        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        $pg->setTotalPages($itemJml);
        $pg->setPages($page);
        $pg->setLimit($jmlPerhalaman); // perhalaman
        $pg->setExtraHref("&$xtraHref"); // perhalaman

        $link_halaman = base_url() . $segmenUrl . "?page=";
        $pg->setWebUrlPage($link_halaman); // perhalaman
        $loaderPages = array(
            "id" => "undoneList",
            "link" => $link_halaman
        );
        $pg->setLoaderPages($loaderPages);

        $paginationData = $pg->build();
        $btnPage = $paginationData['pagination'];
        $jmlHalaman = $paginationData['lastpage'];
        $keyPage = $page - 1;
        // -------------------------------------------------------paging
        $srrchunks = array_chunk($items, $jmlPerhalaman, true);
        $tmpHist = isset($srrchunks[$keyPage]) ? $srrchunks[$keyPage] : array();
        $arrNextPIC = array();
        if (sizeof($tmpHist_0) > 0) {
            $arrTransID = array();
            $arrTransTopID = array();
            $arrTransMasterID = array();
            $arrIdsHist = array();
            $arrTransHist = array();
            $arrNextAction = array();
            $arrAddReference = array();

            foreach ($tmpHist as $row) {
                $arrTransID[] = $row->transaksi_id;
                $arrTransTopID[] = $row->id_top;
                $arrTransMasterID[] = $row->id_master;
                $arrNextAction[$row->transaksi_id] = array(
                    "next_step_num" => $row->next_substep_num,
                    "next_step_code" => $row->next_substep_code,
                );
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
//            arrPrintKuning($arrTransID);
//            arrPrintCyan($arrTransMasterID);
            //----
            $tr = New MdlTransaksi();
            $tr->setFilters(array());
            $tr->addFilter("id_master in ('" . implode("','", $arrTransMasterID) . "')");
            $trTmp = $tr->lookUpAll()->result();
            $this->debugShowLastQuery("biru");
            if (sizeof($trTmp) > 0) {
                foreach ($trTmp as $trTmpSpec) {
                    switch ($trTmpSpec->jenis) {
                        case "588so":// penjualan project
                        case "5822so":// penjualan reguler
                        case "5823so":// penjualan pos
                            $arrAddReference[$trTmpSpec->id_master] = array(
                                "reference_so_id" => $trTmpSpec->id,
                                "reference_so_nomer" => $trTmpSpec->nomer,
                            );
                            break;
                    }
                }
            }
            //----


            $tmpReg_result = array();
            if (sizeof($pairRegistries) > 0) {
                $selectKolom = implode(",", $pairRegistries) . ", transaksi_id";
                $trReg = new MdlTransaksi();
                $trReg->setFilters(array());
                //            $trReg->addFilter("param='main'");
                $trReg->setJointSelectFields($selectKolom);
                $trReg->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
                $tmpReg = $trReg->lookupDataRegistries()->result();
                if (sizeof($tmpReg) > 0) {
                    foreach ($tmpReg as $regRow) {
                        //                    $this->debugArrPrint($regRow);
                        foreach ($regRow as $key_reg => $val_reg) {
                            if ($key_reg != "transaksi_id") {
                                $tmpReg_result[$regRow->transaksi_id][$key_reg] = blobDecode($val_reg);
                            }
                        }

                    }
                }
            }

            $arrNextPIC = callNextPIC($arrNextAction);

            $numb = 0;
            foreach ($tmpHist as $row) {
                $transaksi_id = $row->transaksi_id;
                $gunggungan_mode = $row->gunggungan_mode;
                $extHistoryFields2 = isset($arrExtHistoryFields2[$row->step_number]) ? $arrExtHistoryFields2[$row->step_number] : array();
                $addMainSourceEditable = isset($addMainSource[$row->next_step_num]["editableFields"]) ? $addMainSource[$row->next_step_num]["editableFields"] : array();

                if (sizeof($pairRegistries) > 0) {
                    if ((sizeof($tmpReg_result) > 0) && (isset($tmpReg_result[$row->transaksi_id]))) {
                        foreach ($tmpReg_result[$row->transaksi_id] as $param => $eReg) {
                            switch ($param) {
                                case "main":
                                    foreach ($eReg as $k => $v) {
                                        if (!isset($row->$k)) {
                                            $row->$k = $v;
                                        }
                                    }
                                    break;
                                case "items":
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
                                                    }
                                                    $row->$k1 = $tmpDetail;
                                                }
                                            }
                                        }
                                    }
                                    break;
                            }
                        }
                    }
                    $arrItemShow = isset($this->configUi[$jenisTr]["shortItemsFields"]) ? $this->configUi[$jenisTr]["shortItemsFields"] : array();
                    $detail = viewDetailTransaksi($tmpReg_result[$row->transaksi_id], $arrItemShow, $row->jenis_master);
                }
                if (isset($arrAddReference[$row->id_master]) && (sizeof($arrAddReference[$row->id_master]) > 0)) {
                    foreach ($arrAddReference[$row->id_master] as $akey => $aval) {
                        $row->$akey = $aval;
                    }
                }


                $numb++;
                $tmp = array();
                foreach ($historyFields as $fName => $fLabel) {
                    if (isset($row->$fName)) {
                        if (is_numeric($row->$fName)) {
                            if (!isset($sumFooter[$fName])) {
                                $sumFooter[$fName] = 0;
                            }
                            $sumFooter[$fName] += $row->$fName;
                        }
                    }

                    if (is_array($fLabel)) {
                        $hisStep = $fLabel['step'];
                        $hisKey = $fLabel['key'];

                        if (isset($row->ids_his)) {
                            if ($hisKey == "nomer") {
                                $returnVal = showHistoriGlobalNumbers($row->ids_his, $hisStep, true, $this->jenisTr);
                                if ($returnVal == "") {
                                    $tmp[$fName] = "-";
                                }
                                else {
                                    $tmp[$fName] = $returnVal;
                                }
                            }
                            else {
                                $ids_his_decode = blobDecode($row->ids_his);
                                if (isset($ids_his_decode[$hisStep][$hisKey])) {
                                    $tmp[$fName] = $ids_his_decode[$hisStep][$hisKey];
                                }
                                else {
                                    $tmp[$fName] = "-";
                                }
                            }
                        }
                        else {
                            $tmp[$fName] = "-";
                        }
                    }
                    else {
                        $tmp[$fName] = isset($row->$fName) ? formatField_he_format($fName, $row->$fName) : formatField_he_format($fName, 0);
                    }

                    if ($fName == "no") {
                        $tmp[$fName] = formatField_he_format($fName, $numb);
                    }
                    //-----
                    if (sizeof($inputFakturTransaksi) > 0) {
                        if (isset($inputFakturTransaksi[$row->next_substep_num]["enabled"]) && ($inputFakturTransaksi[$row->next_substep_num]["enabled"] == true)) {
                            if (isset($addMainSourceEditable[$fName])) {
                                $defaultValue = isset($row->$fName) ? $row->$fName : "";
                                $type_input = $addMainSourceEditable[$fName];
                                $idd = $fName . "_" . $transaksi_id;
                                $disabled_input = "";
                                $bg_class = "bg-lime";
                                if (isset($filterCustomerNpwp["enabled"]) && ($filterCustomerNpwp["enabled"] == true)) {
                                    if (!isset($addData[$row->customers_id]["npwp"]) || ($addData[$row->customers_id]["npwp"] == NULL)) {
                                        $disabled_input = "disabled='disabled'";
                                        $bg_class = "";
                                    }
                                }
                                $tmp[$fName] = "<input id='$idd' type='$type_input' $disabled_input
                                    name='$fname' value='$defaultValue' 
                                    class='form-control text-right text-red text-bold $bg_class'>";
                            }
                        }
                    }

                }
                if (sizeof($row->cancel_packing_source_id) > 0) {
                    $this->debugArrPrint($row->cancel_packing_source_id);
                    $trx = new MdlTransaksi();
                    $trx->addFilter("id='" . $row->cancel_packing_source_id . "'");
                    $tmpTrx = $trx->lookupAll()->result();

                    //                        $tmp['nomer_top'] = $tmpTrx[0]->nomer;
                    $tmp['nomer_top'] = formatField_he_format("nomer_top", $tmpTrx[0]->nomer);
                    //                        $this->debugArrPrint($tmpTrx[0]->nomer);
                }

                if ($row->sub_step_number > 0) {
                    $tmp['state'] = "<span style='color:" . $this->configUi[$jenisTr]['steps'][$row->sub_step_number]['stateColor'] . "'>" . $this->configUi[$jenisTr]['steps'][$row->sub_step_number]['stateLabel'] . "</span>";
                    $tmp['state'] .= "<br>" . createStateSignNext($row->sub_step_number, $row->step_avail, $jenisTr);
                }
                else {
                    $tmp['state'] = "<span style='color:#777777'>canceled</span>";
                }


                $tmp["transaksi_id"] = $transaksi_id;
                $tmp["gunggungan_mode"] = $gunggungan_mode;

                //$detail = viewDetailTransaksi($tmpReg_result[$row->transaksi_id], $arrItemShow, $row->jenis_master);
                //$tblItems = $detail;
                //$tmp['item_fields'] = $tblItems;
                // $tmp['pilihan'] = "<td align='center'><input type='checkbox' class='tic_tax'></td>";
                $tmp['pilihan'] = "<input type='checkbox' value='$transaksi_id' class='tic_tax'>";
                $tmp['item_fields'] = $detail;
                $tmp['next_pic'] = "-";
                if (sizeof($arrNextPIC) > 0) {
                    if (isset($arrNextPIC[$row->next_substep_code][$row->next_substep_num])) {
                        $next_pic = "";
                        $nob = 1;
                        foreach ($arrNextPIC[$row->next_substep_code][$row->next_substep_num] as $spec) {
                            if ($row->cabang_id == $spec['cabang_id']) {

                                if ($next_pic == "") {
                                    $next_pic = "$nob. " . $spec['nama'];
                                }
                                else {
                                    $nob++;
                                    $next_pic = $next_pic . "<br>" . "$nob. " . $spec['nama'];
                                }

                            }

                        }
                        $tmp['next_pic'] = $next_pic;
                        //                            cekHitam($next_pic);
                    }
                }
                $tmp['item_fields'] = $detail;
                $tmp['action'] = "";
                $nextStepNum = ($row->next_substep_num);
                $currentStepNum = ($row->sub_step_number);
                $currentStepCode = ($row->jenis);
                $nextStepCode = ($row->next_step_code);

                if ($row->sub_step_number > 0) {
                    //                        cekHere("koq");
                    $allowFollowup = true;
                    $actionLabel = "review " . $this->configUi[$jenisTr]['steps'][$currentStepNum]['label'];
                }

                if (isset($this->configUi[$jenisTr]['steps'][$nextStepNum])) {
                    if (isset($this->accessList[$jenisTr])) {
                        if (isset($this->accessList[$jenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"])) {
                            $allowFollowup = $this->accessList[$jenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"];
                            $actionLabel = $this->configUi[$jenisTr]['steps'][$nextStepNum]['actionLabel'];
                        }


                    }
                    else {
                        if (in_array($this->configUi[$jenisTr]['steps'][$nextStepNum]['userGroup'], $this->session->login['membership'])) {
                            $allowFollowup = true;
                            $actionLabel = $this->configUi[$jenisTr]['steps'][$nextStepNum]['actionLabel'];
                        }
                        else {
                            $allowFollowup = true;
                            $actionLabel = "review " . $this->configUi[$jenisTr]['steps'][$currentStepNum]['label'];
                        }
                    }
                }
                $req_cancel_qty = $row->req_cancel_qty != '' ? $row->req_cancel_qty : 0;
                $valid_qty = $row->valid_qty != '' ? $row->valid_qty : 0;

                if ($allowFollowup) {
                    $allowJoin = isset($this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin']) && $this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin'] == true ? $this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin'] : false;
                    $stepLabel = isset($this->configUi[$jenisTr]['steps'][$nextStepNum]['label']) ? $this->configUi[$jenisTr]['steps'][$nextStepNum]['label'] : "";
                    $isCancelPacking = isset($this->configUi[$jenisTr]['steps'][$nextStepNum]['isCancelPacking']) ? $this->configUi[$jenisTr]['steps'][$nextStepNum]['isCancelPacking'] : false;
                    $allowCancel = isset($this->configUi[$jenisTr]['steps'][$nextStepNum]['allowCancel']) ? $this->configUi[$jenisTr]['steps'][$nextStepNum]['allowCancel'] : false;

                    $targetFollowupLink = $isCancelPacking == true ? "followupCancelPackingPrePreview" : "followupPrePreview";

                    $followupLink = "top.$('#result').load('" . MODUL_PATH . "FollowUp/$targetFollowupLink/$jenisTr/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "');";
                    //--------
                    $followup_id = "followup_" . $transaksi_id;
                    $tmp['action'] = "<div class='input-group'>";
                    $tmp['action'] .= "<button id='$followup_id' class='btn btn-primary btn-block' title='turn this entry into $stepLabel' href='javascript:void(0)' 
                                onClick =\"top.open_holdon();$followupLink\">" . $actionLabel . "</button>";
                    $tmp['action'] .= "</div class='input-group'>";
                    //--------
                    if ($req_cancel_qty > 0 && $valid_qty == 0) {
                        $tmp['action'] = "<div class='btn-group btn-block' role='group' aria-label='cancel packing on progress'>";
                        $tmp['action'] .= "<button type='button' disabled class='btn btn-warning' title='sedang dalam process cancel packing' href='javascript:void(0)'>menuggu approve cancel</button>";
                        $tmp['action'] .= "</div>";
                    }
                    //--------
                    if (sizeof($inputFakturTransaksi) > 0) {
                        if (isset($inputFakturTransaksi[$nextStepNum]["enabled"]) && ($inputFakturTransaksi[$nextStepNum]["enabled"] == true)) {
                            $dateFaktur_id = "dateFaktur_" . $transaksi_id;
                            $eFaktur_id = "eFaktur_" . $transaksi_id;
                            $simpan_id = "simpan_" . $transaksi_id;
                            $targetFollowupLinkFaktur = $inputFakturTransaksi[$nextStepNum]["prePreviewLink"];
                            $action_label_button = $inputFakturTransaksi[$nextStepNum]["action_label_button"];
//                            $followupLinkFaktur = "top.$('#result').load('" . MODUL_PATH . "$targetFollowupLinkFaktur/$jenisTr/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "');";
//                            $followupLinkFaktur = "top.open_holdon();
//top.$('#result').load('https://demo.mayagrahakencana.com/everest_30sep/taxes/FollowUp/followupPrePreviewFaktur/110/213072/2/1?dateFaktur=' + document.getElementById('dateFaktur_213072').value);
//";
//
                            $followupLinkFaktur = "top.$('#result').load('" . MODUL_PATH . "$targetFollowupLinkFaktur/$jenisTr/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "?dateFaktur='+document.getElementById('$dateFaktur_id').value+'&eFaktur='+document.getElementById('$eFaktur_id').value);";
                            $actionTarget = MODUL_PATH . "$targetFollowupLinkFaktur/$jenisTr/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "?dateFaktur='+document.getElementById('$dateFaktur_id').value+'&eFaktur='+document.getElementById('$eFaktur_id').value";
                            $tmp['action'] .= "<button id='$simpan_id' type='button' class='btn btn-success btn-block' title='simpan Faktur PPN'
                                        style='margin-top:5px;'
                                        href='javascript:void(0)' 
                                        onClick=\"if(confirm('Tanggal dan Nomer Faktur akan disimpan. Klik OK untuk melanjutkan.')==1){top.open_holdon();$followupLinkFaktur}\"          
                                        >$action_label_button</button>";
//                            onClick=\"top.open_holdon();$followupLinkFaktur\"
//                            onClick=\"confirm_alert_result('Peringatan','Transaksi akan disimpan setelah mengeklik OK','$actionTarget');this.disabled=true;\"
                        }
                    }
                }
                else {

                }
                //REJECT------------------------------
                $pakai_ini = 1;
                if ($pakai_ini == 1) {
                    $arrStepAllow = array(1);
                    if (in_array($row->sub_step_number, $arrStepAllow)) {
                        $evPre = evaluatePreProcessors_he_menu($this->jenisTr, $currentStepNum, $this->configCoreJenis, $this->configUiJenis);
                        $evPost = evaluatePostProcessors_he_menu($this->jenisTr, $currentStepNum, $this->configCoreJenis, $this->configUiJenis);
                        $evCom = evaluateComponents_he_menu($this->jenisTr, $currentStepNum, $this->configCoreJenis, $this->configUiJenis);
//                        cekHitam($row->transaksi_id . " || evPreProcc: $evPre, evPostProcc: $evPost, evCom: $evCom");
                        if ($evPre == null && $evPost == null && $evCom == null) {
                            $transaksiID_reject = $row->transaksi_id;
                            $masterRevertStep = ($currentStepNum - 1);
                            $childRevertStep = -($currentStepNum);

                            $disabled_hold = isset($disabled_hold) ? $disabled_hold : "";
                            $disabled_button_reject = isset($disabled_button_reject) ? $disabled_button_reject : "";
                            if (isset($this->reject) && $this->reject == true) {
                                $link_reject = MODUL_PATH . "FollowUp/followupPrePreview/" . $this->jenisTr . "/$transaksiID_reject/$nextStepNum/" . $row->sub_step_number . "?getmode=reject";
                                $actionLabel_edit = "Reject 1 step";
                                $tmp['action'] .= "<button class='btn btn-danger btn-block btn-xs' title='turn this entry into $stepLabel' href='javascript:void(0)' 
                                $disabled_hold $disabled_button_reject
                                onclick =\"document.getElementById('result').src='$link_reject'\">" . $actionLabel_edit . " </button>";
                            }

                            if (isset($this->reject_all) && $this->reject_all == true) {
                                $link_reject_all = MODUL_PATH . "FollowUp/followupPrePreview/" . $this->jenisTr . "/$transaksiID_reject/$nextStepNum/" . $row->sub_step_number . "?getmode=rejectall";
                                $actionLabel_edit = "Reject all step";
                                $tmp['action'] .= "<button class='btn btn-danger btn-block btn-xs' title='turn this entry into $stepLabel' href='javascript:void(0)' 
                                style='background-color:#000000;color:#ffffff;'
                                $disabled_hold $disabled_button_reject
                                onclick =\"document.getElementById('result').src='$link_reject_all'\">" . $actionLabel_edit . " </button>";
                            }
                        }

                    }
                }
                //------------------------------

                if ($gunggungan_mode == 1) {
                    $arrayOnprogressGunggungMark[]["style"] = "background-color:yellow;";
                    $tmp['jenis_pajak'] = "PPN GUNGGUNGAN";
                }
                else {
                    $arrayOnprogressGunggungMark[] = array();
                    $tmp['jenis_pajak'] = "REGULER";
                }

                $tmpMark = array();
                $tmp['keterangan'] = "-";
                if ($row->partial == 1) {
                    $tmpMark['style'] = "background-color:yellow;";
                    $tmp['keterangan'] = "<span style='color:red;'>transaksi diproses sebagian</span>";
                }

                if ($row->step_number == 1) {
                    if (sizeof($filterCustomerNpwp) > 0) {
                        if (isset($filterCustomerNpwp["enabled"]) && ($filterCustomerNpwp["enabled"] == true)) {
                            if (isset($addData[$row->customers_id]["npwp"]) && ($addData[$row->customers_id]["npwp"] != NULL)) {
                                $arrayOnprogress[$currentStepNum][] = $tmp;
                                $arrayOnprogressPartialMark[] = $tmpMark;
                                $arrayOnprogressGroupPartialMark[$currentStepNum][] = $tmpMark;
                            } else {
                                $tmp['action'] = "<span style='color:red; font-size:11px;'><b>Hanya bisa diproses di<br>TAB Prepare Gunggungan</b></span>";
                                $tmp['row_style'] = "background-color:#dcdcdc;";
                                $arrayOnprogress[$currentStepNum][] = $tmp;
                                $arrayOnprogressPartialMark[] = $tmpMark;
                                $arrayOnprogressGroupPartialMark[$currentStepNum][] = $tmpMark;
                            }
                        }
                        else {
                            $arrayOnprogress[$currentStepNum][] = $tmp;
                            $arrayOnprogressPartialMark[] = $tmpMark;
                            $arrayOnprogressGroupPartialMark[$currentStepNum][] = $tmpMark;
                        }
                    }
                    else {
                        $arrayOnprogress[$currentStepNum][] = $tmp;
                        $arrayOnprogressPartialMark[] = $tmpMark;
                        $arrayOnprogressGroupPartialMark[$currentStepNum][] = $tmpMark;
                    }
                }
                else {
                    $arrayOnprogress[$currentStepNum][] = $tmp;
                    $arrayOnprogressPartialMark[] = $tmpMark;
                    $arrayOnprogressGroupPartialMark[$currentStepNum][] = $tmpMark;
                }

            }

        }
        //endregion

        //region link to add new transaction
        if (placeCanMakeTrans_he_menu($this->session->login['membership'], $this->session->login['cabang_id'], $this->session->login['gudang_id'], $this->jenisTr, $this->configUiJenis)) {
            $createIndexes = (null != $this->config->item("transaksi_createIndex")) ? $this->config->item("transaksi_createIndex") : array();
            $isDisableMakeTrans = isset($this->configUi[$this->jenisTr]['isDisableMakeTrans']) ? $this->configUi[$this->jenisTr]['isDisableMakeTrans'] : false;
            if (array_key_exists($this->jenisTr, $createIndexes)) {
                $targetUrl = MODUL_PATH . $createIndexes[$this->jenisTr] . "/" . $this->jenisTr;
            }
            else {
                $targetUrl = MODUL_PATH . "Create/index/" . $this->jenisTr;
            }

            if ($isDisableMakeTrans) {
                $addLink = null;
            }
            else {
                $addLink = array(
                    "link" => $targetUrl,
                    "label" => "<span class='glyphicon glyphicon-plus'></span> create new " . $this->configUi[$jenisTr]["steps"][1]['label'],
                );
            }

        }
        else {
            $addLink = null;
        }
        //endregion

        $jenisTrsub = isset($_GET['step']) ? $_GET['step'] : 1;
        $historyFieldsDt = isset($this->configUi[$jenisTr]['historyFields'][$jenisTrsub]) ? $this->configUi[$jenisTr]['historyFields'][$jenisTrsub] : $this->configUi[$jenisTr]['shortHistoryFields'];
        $availDbTable = $tr->getAvailTable($historyFieldsDt);

        //-----------------------------------------------------
        $arrJenisTrCek = array("587", "687", "1587", "1687");
        if (in_array($jenisTr, $arrJenisTrCek)) {
            $transaksiName = isset($this->configUi[$jenisTr]['label']) ? $this->configUi[$jenisTr]['label'] : NULL;
            $subplace = isset($this->configUi[$jenisTr]['steps'][1]['subplace']) ? $this->configUi[$jenisTr]['steps'][1]['subplace'] : NULL;
            if ($subplace != NULL) {
                if (($subplace == "warehouse") && ($this->session->login['gudang_id'] < 0)) {
                    //                    cekPink("SESUAI");
                    $msg = NULL;
                }
                elseif (($subplace == "warehouse_ng") && ($this->session->login['gudang_id'] > 0)) {
                    //                    cekPink("SESUAI NOT GOOD");
                    $msg = NULL;
                }
                else {
                    $msg = "Anda tidak memiliki kewenangan untuk membuat request $transaksiName";
                    $addLink = array();
                }
            }
        }

        //-----------------------------------------------------
        $endtime = microtime(true); // Bottom of page
        $val = $endtime - $starttime;
        $mode = isset($this->configUi[$jenisTr]["mode"]) ? $this->configUi[$jenisTr]["mode"] : $this->uri->segment(3);

        $data = array(
//            "mode" => isset($this->configUi[$jenisTr]["mode"]) ? $this->configUi[$jenisTr]["mode"] : $this->uri->segment(3),
            "mode" => $mode,
            "isMobile" => $isMob,
            "errMsg" => $this->session->errMsg,
            "template" => $this->configUi[$jenisTr]["template"],
            "title" => $this->configUi[$jenisTr]["label"],
            "subTitle" => $this->configUi[$jenisTr]["steps"][1]['label'],
            "jenisTr" => $jenisTr,
            "trName" => $this->configUi[$jenisTr]["label"],
            'addLink' => $addLink,
            "allSteps" => $this->allSteps,
            "historyTitle" => "<span class='glyphicon glyphicon-time'></span> recent " . $this->configUi[$jenisTr]["label"] . " histories",
            "arrayHistoryLabels" => array("dtime" => "time") + $historyFields,
            "arrayHistoryLabelsDt" => $historyFieldsDt,
            "availDbTable" => $availDbTable,
            "arrayHistory" => isset($arrayHistory) ? $arrayHistory : array(),
            "onprogressTitle" => "<span class='glyphicon glyphicon-alert'></span> TRANSAKSI YANG PERLU ACTION ",
            "arrayProgressLabels" => $progressFields,
            "arrayOnProgressToPay" => $paymentConfig,
            "itemLabels" => isset($itemLabels) ? $itemLabels : "",
            "srcLabel" => isset($srcLabel) ? $srcLabel : "",
            //            "arrayOnProgress" => array_merge($arrayOnprogress, $arrayOnprePre, $arrayOnpreDistribution),
            "steps" => $steps,
            "arrayOnProgress" => (isset($arrayOnprogress) && sizeof($arrayOnprogress) > 0) ? $arrayOnprogress : array(),
            "arrayOnprogressGroup" => (isset($arrayOnprogressGroup) && (sizeof($arrayOnprogressGroup) > 0)) ? $arrayOnprogressGroup : array(),
            "arrayOnprogressGroupNonNpwp" => (isset($arrayOnprogressGroupNonNpwp) && (sizeof($arrayOnprogressGroupNonNpwp) > 0)) ? $arrayOnprogressGroupNonNpwp : array(),
            "stepCounts" => isset($stepCounts) ? $stepCounts : array(),
            "nonNpwpCount" => isset($nonNpwpCount) ? $nonNpwpCount : 0,
            "arrayOnprePre" => $arrayOnprePre,
            "arrayOnprePreGroup" => $arrayOnprePreGroup,

            "arrayOnpreDistribution" => isset($arrayOnpreDistribution) ? $arrayOnpreDistribution : array(),
            "arrayOnpreDistributionGroup" => isset($arrayOnpreDistributionGroup) ? $arrayOnpreDistributionGroup : array(),

            "entities" => isset($entities) ? $entities : array(),
            "recapTitle" => "<span class='fa fa-newspaper-o'></span> today " . $this->configUi[$jenisTr]["label"] . " reports",
            "arrayRecapLabels" => isset($recapLabels) ? $recapLabels : array(),
            "arrayRecap" => isset($arrayRecap) ? $arrayRecap : array(),

            "onprogressViewTitle" => "<span class='fa fa-eye'></span> show incomplete step " . $this->configUi[$jenisTr]["label"],
            "onprogressViewSubTitle" => "<span class='text-black'>(daftar transaksi yang masih stanby di cabang tujuan)</span>",
            "arrayOnProgressView" => isset($arrayOnProgressView) ? $arrayOnProgressView : array(),
            "stepHistoryFields" => isset($stepHistoryFields) ? $stepHistoryFields : array(),

            "selectProcessor" => $selectProcessor,
            "sumFooter" => (isset($sumFooterResult) && sizeof($sumFooterResult) > 0) ? $sumFooter : array(),
            "scriptBottom" => $scriptBottom,
            "btnLabel" => $extData,
            "actionTarget" => $extact,
            "sumFooter" => isset($sumFooter) ? $sumFooter : "",

            "arrayOnprogressPartialMark" => (isset($arrayOnprogressPartialMark) && sizeof($arrayOnprogressPartialMark) > 0) ? $arrayOnprogressPartialMark : array(),
            "arrayOnprogressGroupPartialMark" => (isset($arrayOnprogressGroupPartialMark) && sizeof($arrayOnprogressGroupPartialMark) > 0) ? $arrayOnprogressGroupPartialMark : array(),

            "defaultItemTrgEditable" => isset($defaultItemTrgEditable) ? $defaultItemTrgEditable : array(),
            "editItemTrg" => MODUL_PATH . "_followupLiveEdit/editEfaktur/" . $this->jenisTr . "/",
            "arrNextPIC" => isset($arrNextPIC) ? $arrNextPIC : array(),
            "btnPage" => isset($btnPage) ? $btnPage : "",
            "gPage" => isset($page) ? $page : 1,
            "jmlPerhalaman" => isset($jmlPerhalaman) ? $jmlPerhalaman : 10,
            "urutanDb" => isset($urutanDb) ? $urutanDb : "",

            "gunggungFieldsView" => $gunggungFieldsView,
            "getDate" => $getDate,

            "arrayOnprogressGunggungMark" => $arrayOnprogressGunggungMark,
            "entry_faktur_masal" => isset($this->configUi[$this->jenisTr]["entry_faktur_masal"]) ? $this->configUi[$this->jenisTr]["entry_faktur_masal"] : false,
        );

        $this->load->view("transaksi", $data);
    }


    /*---------dgn paging php-------*/
    public function viewUndoneItemsIndex2()
    {
        $stepper = isset($_GET['step']) ? $_GET['step'] : 1;
        $isPreGunggungan = ($stepper == "pre_gunggungan");
        $getDate = isset($_GET['date']) ? $_GET['date'] : NULL;
        if ($getDate != NULL) {
            $getDate_ex = explode("-", $getDate);
            $date_tahun = $getDate_ex[0];
            $date_bulan = $getDate_ex[1];
        }

        $starttime = microtime(true);
        if (!isset($this->session->login['id'])) {
            redirect(base_url() . "Login");
        }
        $scriptBottom = "";
        $sesionReplacer = replaceSession();
        $jenisTr = $this->jenisTr;
        $cCode = "_TR_" . $this->jenisTr;
        $paymentConfig = isset($this->configUi[$jenisTr]['paymentConfig']) ? $this->configUi[$jenisTr]['paymentConfig'] : false;
        $myPaymentConfig = isset($this->configUi[$jenisTr]['myPaymentConfig']) ? $this->configUi[$jenisTr]['myPaymentConfig'] : false;
        $historyFields = isset($this->configUi[$jenisTr]['shortHistoryFields']) ? $this->configUi[$jenisTr]['shortHistoryFields'] : array();

        $historyFields2 = isset($this->configUi[$jenisTr]['shortHistoryFields2']) ? $this->configUi[$jenisTr]['shortHistoryFields2'] : array();

        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
        $connectTo = isset($this->configUi[$jenisTr]['connectTo']) ? $this->configUi[$jenisTr]['connectTo'] : "";
        $stepHistoryFields = isset($this->configUi[$jenisTr]['shortStepHistoryFields']) ? $this->configUi[$jenisTr]['shortStepHistoryFields'] : array();
        $arrExtHistoryFields2 = isset($this->configUi[$jenisTr]["extHistoryFields2"]) ? $this->configUi[$jenisTr]["extHistoryFields2"] : array();
        $filterCustomerNpwp = isset($this->configUi[$jenisTr]["filterCustomerNpwp"]) ? $this->configUi[$jenisTr]["filterCustomerNpwp"] : array();
        $addMainSource = isset($this->configUi[$jenisTr]["addMainSource"]) ? $this->configUi[$jenisTr]["addMainSource"] : array();
        $inputFakturTransaksi = isset($this->configUi[$jenisTr]["inputFakturTransaksi"]) ? $this->configUi[$jenisTr]["inputFakturTransaksi"] : array();
        $inputFakturTransaksiApprove = isset($this->configUi[$jenisTr]["inputFakturTransaksiApprove"]) ? $this->configUi[$jenisTr]["inputFakturTransaksiApprove"] : array();
        $arrItemShow = isset($this->configUi[$jenisTr]["shortItemsFields"]) ? $this->configUi[$jenisTr]["shortItemsFields"] : array();
        $gunggungFieldsView = (isset($this->configUi[$jenisTr]["gunggungFields"]) && (count($this->configUi[$jenisTr]["gunggungFields"]) > 0)) ? true : false;

        $steps_akses = isset($this->configUi[$jenisTr]["steps"]) ? $this->configUi[$jenisTr]["steps"] : array();


        $mb = New MobileDetect();
        $isMob = $mb->isMobile();
        if ($isMob) {
            $historyFields = isset($this->configUi[$jenisTr]['compactHistoryFields']) ? $this->configUi[$jenisTr]['compactHistoryFields'] : array();
        }
        $prePreFields = $historyFields;
        $prePreFields['state'] = "status";
        $prePreFields['action'] = "action";
        $swapJenisTr = isset($this->configUi[$jenisTr]['requestCode']['masterCode']) ? $this->configUi[$jenisTr]['requestCode']['masterCode'] : array();
        $arrayOnprePre = array();
        $arrayOnprePreGroup = array();
        if (sizeof($swapJenisTr) > 0) {
            $steps = $this->configUi[$swapJenisTr]['steps'];
            $arrFilters = array();
            if (sizeof($steps) > 1) {
                $this->load->model("MdlTransaksi");
                $tr = new MdlTransaksi();
                $stepCodes = array();
                $jmlStep = count($steps);
                if (isset($this->accessList[$swapJenisTr]) && sizeof($this->accessList) > 0) {
                    $indsteps = "(";
                    foreach ($this->accessList[$swapJenisTr] as $stepNumber => $stepSpec) {
                        if ($stepNumber <= $jmlStep) {
                            foreach ($stepSpec as $targetCode => $filters) {
                                $indsteps .= "'$targetCode',";
                                $stepCodes[] = $targetCode;
                                if ($filters['allowFollowUp'] == "true") {
                                    $arrFilters["allowFollowUp"][] = $targetCode;
                                }
                            }
                        }
                    }
                    $indsteps = rtrim($indsteps, ",");
                    $indsteps .= ")";
                    if (sizeof($arrFilters) > 0) {
                        $tr->addFilter("next_step_code in $indsteps");
                    }
                    else {
                        $tr->addFilter("transaksi.oleh_id='" . $this->session->login['id'] . "'");
                    }
                }
                else {

                }
                $tr->addFilter("div_id='" . $this->session->login['div_id'] . "'");
                $tr->addFilter("jenis_top='" . $steps[1]['target'] . "'");
                $tr->addFilter("next_substep_code<>''");
                $tr->addFilter("sub_step_number>0");
                $tr->addFilter("valid_qty>0");
                $tmpHist = $tr->lookupRecentUndoneEntries_joined($sesionReplacer)->result();
                $this->debugShowLastQuery("biru");
                matiDisini(__LINE__);
                if (sizeof($tmpHist) > 0) {
                    $arrTransID = array();
                    $arrTransTopID = array();
                    $arrIdsHist = array();
                    $arrTransHist = array();
                    foreach ($tmpHist as $row) {
                        $arrTransID[] = $row->id;
                        $arrTransTopID[] = $row->id_top;

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
                    $selectKolom = implode(",", $pairRegistries) . ",transaksi_id";
                    // matiHEre(__LINE__);
                    $trReg->setJointSelectFields($selectKolom);
                    $trReg->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
                    $tmpReg = $trReg->lookupDataRegistries()->result();

                    if (sizeof($tmpReg) > 0) {
                        foreach ($tmpReg as $regRow) {
                            $param = $regRow->param;
                            $tmpReg_result[$regRow->transaksi_id][$param] = blobDecode($regRow->values);
                        }
                    }

                    foreach ($tmpHist as $row) {
                        if (sizeof($pairRegistries) > 0) {
                            //                            $trReg = new MdlTransaksi();
                            //                            $trReg->setFilters(array());
                            //                            $trReg->addFilter("param in ('" . implode("','", $pairRegistries) . "')");
                            //                            $trReg->addFilter("transaksi_id='" . $row->transaksi_id . "'");
                            //                            $tmpReg = $trReg->lookupRegistries()->result();
                            //                            if (sizeof($tmpReg) > 0) {
                            //                                foreach ($tmpReg as $regRow) {
                            //                                    $param = $regRow->param;
                            //                                    $$param = blobDecode($regRow->values);
                            //                                }
                            //                                foreach ($pairRegistries as $eReg) {
                            //                                    foreach ($$eReg as $k => $v) {
                            //                                        if (!isset($row->$k)) {
                            //                                            $row->$k = $v;
                            //                                        }
                            //                                    }
                            //                                }
                            //                            }
                            if ((sizeof($tmpReg_result) > 0) && (isset($tmpReg_result[$row->id]))) {
                                foreach ($tmpReg_result[$row->id] as $param => $eReg) {
                                    foreach ($eReg as $k => $v) {
                                        if (!isset($row->$k)) {
                                            $row->$k = $v;
                                        }
                                    }
                                }
                            }
                        }

                        $tmp = array();
                        foreach ($historyFields as $fName => $fLabel) {
                            $tmp[$fName] = isset($row->$fName) ? formatField($fName, $row->$fName) : formatField($fName, 0);
                        }

                        //                        cekHijau('$swapJenisTr: ' . $swapJenisTr);

                        $showPoStatus = isset($this->configUi[$swapJenisTr]['showPoStatus']) ? $this->configUi[$swapJenisTr]['showPoStatus'] : array();
                        $cekStateLocation = ($this->session->login['cabang_id'] > 0) ? "cabang" : "pusat";

                        if ($row->sub_step_number > 0) {

                            $tmp['state'] = "<div class='panel panel-warning' style='padding: 3px;margin-bottom: 5px;' ><span style='color:" . $this->configUi[$swapJenisTr]['steps'][$row->sub_step_number]['stateColor'] . "'>" . $this->configUi[$swapJenisTr]['steps'][$row->sub_step_number]['stateLabel'] . "</span>";
                            $tmp['state'] .= "<br>" . createStateSign($row->sub_step_number, $row->step_avail, $swapJenisTr) . "</div>";

                            // cek PO
                            $arrTransactionSource = array();
                            $id_master = $row->id_master;
                            $id_top1 = $row->id_top;
                            $this->load->model("MdlTransaksi");
                            $l = new MdlTransaksi();
                            $l->setFilters(array());
                            $l->addFilterJoin("transaksi_data.valid_qty>0");
                            $l->addFilter("id_master='" . $id_master . "'");
                            $l->addFilter("jenis_master='" . $swapJenisTr . "'");
                            $l->addFilter("link_id=0");
                            $tmpTS = $l->lookupJoined();

                            $arrsub_step_number = array();
                            $arrstep_avail = array();
                            $arrext_blob = array();
                            $arrjenis_master = array();
                            $arrtransaksi_no = array();
                            $arrketerangan = array();

                            if (sizeof($tmpTS) > 0) {
                                foreach ($tmpTS as $kk => $arVL) {
                                    $id = $arVL->id;
                                    $id_master = $arVL->id_master;
                                    $id_top2 = $arVL->id_top;
                                    $produk_id = $arVL->produk_id;
                                    $arrTransactionSource[$id_top1] = $produk_id;
                                    $arrsub_step_number[$id_top1] = $arVL->sub_step_number;
                                    $arrstep_avail[$id_top1] = $arVL->step_avail;
                                    $arrext_blob[$id_top1][$kk] = isset($arVL->ext_blob) ? ($arVL->ext_blob != "" ? blobDecode($arVL->ext_blob) : "") : "";
                                }

                                if (sizeof($arrext_blob[$id_top1]) > 0) {
                                    foreach ($arrext_blob[$id_top1] as $ky => $aVal) {
                                        if ($aVal != "") {
                                            foreach ($arrext_blob[$id_top1][$ky]['static'] as $numb => $numData) {
                                                $arrtransaksi_no[$id_top1][$numb] = formatField("nomer", $numb);
                                                $arrjenis_master[$id_top1][$numb] = $numData['jenis'];
                                                $arrketerangan[$id_top1][$numb] = $numData['keterangan'];
                                            }
                                        }
                                    }
                                }
                            }

                            if ($arrext_blob[$id_top1][$ky] != "") {
                                $tmp['state'] .= "<div class='panel panel-danger bg-green' style='padding: 3px;margin-bottom: 5px;'>";
                                if ($cekStateLocation == "cabang") {
                                    $tmp['state'] .= "<div><b>diproses oleh PUSAT</b></div>";
                                }
                                if (sizeof($arrtransaksi_no[$id_top1]) > 0) {
                                    foreach ($arrtransaksi_no[$id_top1] as $numb_) {
                                        $tmp['state'] .= "<div><span class='fa fa-check-circle text-warning'></span> " . $numb_ . "</div>";
                                    }
                                }
                                $tmp['state'] .= "</div>";
                            }

                        }
                        else {
                            $tmp['state'] = "<span style='color:#777777'>canceled</span>";
                        }

                        $tmp['action'] = "";
                        $nextStepNum = ($row->next_substep_num);
                        $currentStepNum = ($row->sub_step_number);
                        $currentStepCode = ($row->jenis);
                        $nextStepCode = ($row->next_step_code);

                        if ($row->sub_step_number > 0) {
                            $allowFollowup = true;
                            $actionLabel = "review " . $this->configUi[$swapJenisTr]['steps'][$currentStepNum]['label'];
                        }

                        if (isset($this->configUi[$swapJenisTr]['steps'][$nextStepNum])) {
                            if (isset($this->accessList[$jenisTr])) {
                                if (isset($this->accessList[$swapJenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"])) {
                                    $allowFollowup = $this->accessList[$swapJenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"];
                                    $actionLabel = $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['actionLabel'];
                                }
                            }
                            else {
                                if (in_array($this->configUi[$swapJenisTr]['steps'][$nextStepNum]['userGroup'], $this->session->login['membership'])) {
                                    $allowFollowup = true;
                                    $actionLabel = $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['actionLabel'];
                                }
                                else {
                                    $allowFollowup = true;
                                    $actionLabel = "review " . $this->configUi[$swapJenisTr]['steps'][$currentStepNum]['label'];
                                }
                            }
                        }

                        if ($allowFollowup) {
                            $allowJoin = isset($this->configUi[$swapJenisTr]["steps"][$nextStepNum]['allowJoin']) && $this->configUi[$swapJenisTr]["steps"][$nextStepNum]['allowJoin'] == true ? $this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin'] : false;
                            $stepLabel = isset($this->configUi[$swapJenisTr]['steps'][$nextStepNum]['label']) ? $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['label'] : "";
                            $isCancelPacking = isset($this->configUi[$swapJenisTr]['steps'][$nextStepNum]['isCancelPacking']) ? $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['isCancelPacking'] : false;
                            $allowCancel = isset($this->configUi[$swapJenisTr]['steps'][$nextStepNum]['allowCancel']) ? $this->configUi[$swapJenisTr]['steps'][$nextStepNum]['allowCancel'] : false;

                            $targetFollowupLink = $isCancelPacking == true ? "followupCancelPackingPrePreview" : "followupPrePreview";
                            $followupLink = "top.$('#result').load('" . base_url() . "Transaksi/$targetFollowupLink/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "');";

                            $tmp['action'] = "<div class='input-group'>";
                            $tmp['action'] .= "<a class='btn btn-primary btn-block' title='turn this entry into $stepLabel' href='javascript:void(0)' onClick =\"top.open_holdon();$followupLink\">" . $actionLabel . "</a>";
                            if ($allowJoin) {
                                $tmp['action'] .= "<span class='input-group-addon'>";
                                $tmp['action'] .= "<a title='process many items at once' href='" . base_url() . "Transaksi/viewIncomplete/" . $this->jenisTr . "/$currentStepNum'><span class='fa fa-dedent'></span></a>";
                                $tmp['action'] .= "</span class='input-group-addon'>";
                            }
                            $tmp['action'] .= "</div class='input-group'>";

                            if ($allowCancel) {
                                $tmp['action'] .= "<div class='input-group'>";
                                $tmp['action'] .= "<a class='btn btn-primary btn-block' title='turn this entry into $stepLabel' href='javascript:void(0)' onClick =\"top.open_holdon();$followupLink\">" . $actionLabel . "</a>";
                                if ($allowJoin) {
                                    $tmp['action'] .= "<span class='input-group-addon'>";
                                    $tmp['action'] .= "<a title='process many items at once' href='" . base_url() . "Transaksi/viewIncomplete/" . $this->jenisTr . "/$currentStepNum'><span class='fa fa-dedent'></span></a>";
                                    $tmp['action'] .= "</span class='input-group-addon'>";
                                }
                                $tmp['action'] .= "</div class='input-group'>";
                            }
                        }
                        else {

                        }
                        $arrayOnprePre[] = $tmp;
                        $arrayOnprePreGroup[$currentStepNum][] = $tmp;
                    }
                }
            }
            else {
                $arrayOnprePre = array();
            }
        }
        else {
            $arrayOnprePre = array();
        }

        $addData = array();
        if (sizeof($filterCustomerNpwp) > 0) {
            if (isset($filterCustomerNpwp["enabled"]) && ($filterCustomerNpwp["enabled"] == true)) {
                $mdlName = $filterCustomerNpwp["mdlName"];
                $this->load->model("Mdls/$mdlName");
                $mm = New $mdlName();
                $mm->setFilters(array());
                $mmTmp = $mm->lookupAll()->result();
                if (sizeof($mmTmp) > 0) {
                    foreach ($mmTmp as $mmSpec) {
                        $addData[$mmSpec->id] = (array)$mmSpec;
                    }
                }
            }
        }
        //region lookup on-going transactions
        $isStepOne110 = ($stepper == 1 && $jenisTr == "110");
        $progressFields = ($stepper == "pre_gunggungan" || $isStepOne110) ? $historyFields2 : $historyFields;
        $progressFields['state'] = "status";
        $progressFields['action'] = "action";

        //debug #reject
        if ($stepper == "pre_gunggungan" || $isStepOne110) {
            $progressFields["reject"] = "reject";
        }

//        if(isset($inputFakturTransaksiApprove[$stepper]["enabled"]) && ($inputFakturTransaksiApprove[$stepper]["enabled"] == true)){
//            if($stepper == 1){
//                $progressFields['action_approve'] = "approve";
//            }
//        }

        $steps = $this->configUi[$jenisTr]['steps'];

        $arrFilters = array();
        $this->load->model("MdlTransaksi");
        $tr = new MdlTransaksi();

        $stepCodes = array();
        $jmlStep = count($steps);

//        $pakai_ini = 0;//
        $pakai_ini = 1;// mengikuti hak aksesnya
        if ($pakai_ini == 1) {
            if (isset($this->accessList[$jenisTr]) && sizeof($this->accessList) > 0) {
                $indsteps = "(";
                foreach ($this->accessList[$jenisTr] as $stepNumber => $stepSpec) {
                    if ($stepNumber <= $jmlStep) {
                        foreach ($stepSpec as $targetCode => $filters) {

                            $stepCodes[] = $targetCode;
                            if ($filters['allowCreate'] == "true") {
                                //berarti step 1 boleh edit
                                $next = $stepNumber + 1;
                                $target2 = $steps[$next]["target"];
                                $indsteps .= "'$target2',";
                                $arrFilters["allowFollowUp"][] = $target2;
                            }
                            if ($filters['allowFollowUp'] == "true") {
                                $indsteps .= "'$targetCode',";
                                $arrFilters["allowFollowUp"][] = $targetCode;
                            }

                        }
                        //                    foreach ($stepSpec as $targetCode => $filters) {
                        //                        $indsteps .= "'$targetCode',";
                        //                        $stepCodes[] = $targetCode;
                        //                        if ($filters['allowFollowUp'] == "true") {
                        //                            $arrFilters["allowFollowUp"][] = $targetCode;
                        //                        }
                        //
                        //                    }
                    }
                }
                $indsteps = rtrim($indsteps, ",");
                $indsteps .= ")";
                if (sizeof($arrFilters) > 0) {
                    $tr->addFilter("next_step_code in $indsteps");
                }
                else {
                    $tr->addFilter("transaksi.oleh_id='" . $this->session->login['id'] . "'");
                    $tr->addFilter("next_step_code!=''");
                }
            }
            else {

            }
            $tr->addFilter("next_substep_code<>''");
            $tr->addFilter("sub_step_number>0");
            $tr->addFilter("valid_qty>0");
        }
        else {
            if (sizeof($steps_akses) > 0) {
                $akses_target = array();
                foreach ($steps_akses as $xx => $xxSpec) {
                    $akses_target[$xx] = $xxSpec["target"];
                }
                $tr->addFilter("next_step_code in ('" . implode("','", $akses_target) . "')");

//                $tr->addFilter("next_substep_code<>''");
                $tr->addFilter("sub_step_number>0");
//                $tr->addFilter("valid_qty>0");

//                $tr->addFilter("id_master in ('1092','336621')");// tembak dulu untuk cek...
            }
        }

        $tr->addFilter("div_id='" . $this->session->login['div_id'] . "'");
        $tr->addFilter("jenis_top='" . $steps[1]['target'] . "'");

        if ($this->session->login['employee_type'] == "employee_freelance") {
            $tr->addFilter("seller_id='" . $this->session->login['id'] . "'");
        }

        if ($getDate != NULL) {
            $date_tahun = $getDate_ex[0];
            $date_bulan = $getDate_ex[1];
            $tr->addFilter("year(transaksi.dtime)='$date_tahun'");
            $tr->addFilter("month(transaksi.dtime)='$date_bulan'");
        }


        /* -----------------------------------------------------------------------------------
         * filtering data
         * -----------------------------------------------------------------------------------*/
        $gets = $_GET;
        unset($gets['page']);
        $xtraHref = http_build_query($gets);
        $limitDb = url_segment(5);
        $urutanDb = url_segment(6);
        $step = isset($_GET['step']) ? $_GET['step'] : "1";
        $isPreGunggungan = ($step == "pre_gunggungan");
        $targetStep = $isPreGunggungan ? 1 : (is_numeric($step) ? intval($step) : 1);
        $jmlPerhalaman = 200;
        $page = (isset($_GET['page']) && is_numeric($_GET['page']) && intval($_GET['page']) > 0) ? intval($_GET['page']) : 1;
        $keyPage = $page - 1;
        $offset = $jmlPerhalaman * $keyPage;
        $queryLimit = $jmlPerhalaman;
        if (isset($limitDb) && is_numeric($limitDb) && intval($limitDb) > 0) {
            $queryLimit = intval($limitDb);
            $page = 1;
            $keyPage = 0;
            $offset = 0;
        }
        $orderMode = (isset($urutanDb) && in_array(strtolower($urutanDb), array("asc", "desc"))) ? strtolower($urutanDb) : "desc";
        /* --------------------------------------- ------------------------------------------
         * urutan dan limit
         * -----------------------------------------------------------------------------------*/
        // ------------------------------------------------------------------------------
        if (isset($_GET['kw']) && trim($_GET['kw']) !== '') {
            $_GET['kw'] = str_replace(array('INVPOS.', 'INVPOS'), array('5822r.', '5822r'), $_GET['kw']);
            $kw = trim($_GET['kw']);
            $tr->setKeyWord($kw);
            $words = explode(" ", $kw);
            $arrKolomSearch = array(
                "transaksi.customers_nama",
                "transaksi.oleh_nama",
                "transaksi.suppliers_nama",
                "transaksi.nomer",
                //-----
                "transaksi.nomer_top",
                "transaksi.nomer_new",
                "transaksi.nomer2",
                "transaksi.seller_nama",
                "transaksi.salesman_nama",
                "transaksi.cabang_nama",
                "transaksi.cabang2_nama",
                "transaksi.no_ktp",
                "transaksi.npwp",
            );
            foreach ($words as $word) {
                $word = trim($word);
                if ($word !== '') {
                    $likeClauses = array();
                    foreach ($arrKolomSearch as $col) {
                        $likeClauses[] = "$col LIKE '%" . $this->db->escape_like_str($word) . "%'";
                    }
                    $tr->addFilter("(" . implode(" OR ", $likeClauses) . ")");
                }
            }
        }
        else if (isset($this->keyWord)) {
            $key = isset($this->keyWord) ? $this->keyWord : "";
            $arrKolomSearch = array(
                "transaksi.customers_nama",
                "transaksi.oleh_nama",
                "transaksi.suppliers_nama",
                "transaksi.nomer"
            );
            $this->createSmartSearch($key, $arrKolomSearch);
        }
        // ------------------------------------------------------------------------------

        if (ipadd() == "202.65.117.72") {
            cekHere("ip terdeteksi 202.65.117.72");
            $tr->addFilter("valid_qty>0");
        }
        $tr->addFilter("valid_qty>0");
        $isFilterNpwpEnabled = (sizeof($filterCustomerNpwp) > 0) && isset($filterCustomerNpwp["enabled"]) && ($filterCustomerNpwp["enabled"] == true);
        $stepCounts = array();
        $stepCountRows = $tr->lookupRecentUndoneEntries_joinedCountByStep($sesionReplacer);
//        cekHitam($this->db->last_query());
//        matiHere();
        if (sizeof($stepCountRows) > 0) {
            foreach ($stepCountRows as $stepCountSpec) {
                $stepNum = intval($stepCountSpec->sub_step_number);
                if ($stepNum > 0) {
                    $stepCounts[$stepNum] = intval($stepCountSpec->totalnum);
                }
            }
        }
        $allStepOneCount = isset($stepCounts[1]) ? intval($stepCounts[1]) : 0;
        $stepCounts[1] = intval($allStepOneCount);

        // Optimasi: Jika Step 1 Unified (menggunakan AJAX DataTable server-side), lewati query data berat yang tidak dipakai view
        $isStepOneUnified = (($stepper == 1 && $jenisTr == "110") || $stepper == "pre_gunggungan");

        $nonNpwpCount = 0;
        $npwpStepOneCount = 0;
        $itemJml = 0;
        $tmpHist_0 = array();
        $arrayOnprogress = array();
        $arrayOnprogressGroup = array();
        $arrayOnprogressGroupNonNpwp = array();
        $arrayOnprogressPartialMark = array();
        $arrayOnprogressGroupPartialMark = array();
        $arrayOnprogressGunggungMark = array();
        $extData = "";
        $extact = "";
        $selectProcessor = "";
        $items = array();
        $btnPage = "";
        $jmlHalaman = 0;
        $tmpHist = array();

        if (!$isStepOneUnified) {
            $trNonNpwp = clone $tr;
            $trNonNpwp->addFilter("sub_step_number='1'");
            $trNonNpwp->addFilter("next_substep_code in ('110e','110')");
            $trNonNpwp->addFilter("customers_id in (select id from per_customers where ifnull(npwp,'')='')");
            $nonNpwpCount = $trNonNpwp->lookupRecentUndoneEntries_joinedCount($sesionReplacer);

            if ($allStepOneCount > 0) {
                $trNpwp = clone $tr;
                $trNpwp->addFilter("sub_step_number='1'");
                $trNpwp->addFilter("customers_id in (select id from per_customers where ifnull(npwp,'')<>'')");
                $npwpStepOneCount = $trNpwp->lookupRecentUndoneEntries_joinedCount($sesionReplacer);
            }

            if ($targetStep > 0) {
                $tr->addFilter("sub_step_number='" . $targetStep . "'");
            }

            $itemJml = $isPreGunggungan ? $nonNpwpCount : 0;
            if (!$isPreGunggungan) {
                $itemJml = $tr->lookupRecentUndoneEntries_joinedCount($sesionReplacer);
                if (isset($limitDb) && is_numeric($limitDb) && intval($limitDb) > 0 && $itemJml > $queryLimit) {
                    $itemJml = $queryLimit;
                }
                $tmpHist_0 = $tr->lookupRecentUndoneEntries_joinedPaged($sesionReplacer, $queryLimit, $offset, $orderMode)->result();
            }

            foreach ($tmpHist_0 as $item) {
                $currentStepNum = $item->sub_step_number;
                $npwp = isset($item->npwp) ? $item->npwp : null;
                if ($item->step_number == 1) {
                    if (sizeof($filterCustomerNpwp) > 0) {
                        if (isset($filterCustomerNpwp["enabled"]) && ($filterCustomerNpwp["enabled"] == true)) {
                            if (isset($addData[$item->customers_id]["npwp"]) && ($addData[$item->customers_id]["npwp"] != NULL)) {
                                $arrayOnprogressGroup[$currentStepNum][] = $item;
                            }
                            else {
                                $arrayOnprogressGroupNonNpwp[$currentStepNum][] = $item;
                                $arrayOnprogressGroup[$currentStepNum][] = $item;
                            }
                        }
                        else {
                            $arrayOnprogressGroup[$currentStepNum][] = $item;
                        }
                    }
                    else {
                        $arrayOnprogressGroup[$currentStepNum][] = $item;
                    }
                }
                else {
                    $arrayOnprogressGroup[$currentStepNum][] = $item;
                }
            }

            $segmen = url_segment();
            $segmenUrl = implode("/", $segmen);
            $gr = isset($_GET['gr']) ? $_GET['gr'] : "";
            $items = isset($arrayOnprogressGroup[$step]) ? $arrayOnprogressGroup[$step] : array();

            $this->load->library("pagination");
            $pg = new Pagination();
            $pg->setTotalPages($itemJml);
            $pg->setPages($page);
            $pg->setLimit($jmlPerhalaman);
            $pg->setExtraHref("&$xtraHref");

            $link_halaman = base_url() . $segmenUrl . "?page=";
            $pg->setWebUrlPage($link_halaman);
            $loaderPages = array(
                "id" => "undoneList",
                "link" => $link_halaman
            );
            $pg->setLoaderPages($loaderPages);

            $paginationData = $pg->build();
            $btnPage = $paginationData['pagination'];
            $jmlHalaman = $paginationData['lastpage'];
            $tmpHist = $items;
        }
        $arrNextPIC = array();
        if (sizeof($tmpHist_0) > 0) {
            $arrTransID = array();
            $arrTransTopID = array();
            $arrTransMasterID = array();
            $arrIdsHist = array();
            $arrTransHist = array();
            $arrNextAction = array();
            $arrAddReference = array();
            $arrTopData = array();

            foreach ($tmpHist as $row) {
                $arrTransID[] = $row->transaksi_id;
                $arrTransTopID[] = $row->id_top;
                $arrTransMasterID[] = $row->id_master;
                $arrNextAction[$row->transaksi_id] = array(
                    "next_step_num" => $row->next_substep_num,
                    "next_step_code" => $row->next_substep_code,
                );
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
//            arrPrintKuning($arrTransID);
//            arrPrintCyan($arrTransMasterID);
            //----
            $tr = New MdlTransaksi();
            $tr->setFilters(array());
            $tr->addFilter("id_master in ('" . implode("','", $arrTransMasterID) . "')");
            $trTmp = $tr->lookUpAll()->result();
//            $this->debugShowLastQuery("biru");
            if (sizeof($trTmp) > 0) {
                foreach ($trTmp as $trTmpSpec) {
                    switch ($trTmpSpec->jenis) {
                        case "588so":// penjualan project
                        case "5822so":// penjualan reguler
                        case "5823so":// penjualan pos
                            $arrAddReference[$trTmpSpec->id_master] = array(
                                "reference_so_id" => $trTmpSpec->id,
                                "reference_so_nomer" => $trTmpSpec->nomer,
                            );
                            break;

                        case "110e":// input tgl dan nomer faktur (pairing)
                            if ($trTmpSpec->trash_4 == 0) {
                                $arrTopData[$trTmpSpec->id_top] = array(
                                    "transaksi_id" => $trTmpSpec->id,
                                    "efaktur" => $trTmpSpec->efaktur,
                                    "efaktur_dtime" => $trTmpSpec->efaktur_dtime,
                                    "eFaktur" => $trTmpSpec->efaktur,
                                    "dateFaktur" => $trTmpSpec->efaktur_dtime,
                                );
                            }
                            break;
                    }
                }
            }
            //----


            $tmpReg_result = array();
            if (sizeof($pairRegistries) > 0) {
                $selectKolom = implode(",", $pairRegistries) . ", transaksi_id";
                $trReg = new MdlTransaksi();
                $trReg->setFilters(array());
                //            $trReg->addFilter("param='main'");
                $trReg->setJointSelectFields($selectKolom);
                $trReg->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
                $tmpReg = $trReg->lookupDataRegistries()->result();
                if (sizeof($tmpReg) > 0) {
                    foreach ($tmpReg as $regRow) {
                        //                    $this->debugArrPrint($regRow);
                        foreach ($regRow as $key_reg => $val_reg) {
                            if ($key_reg != "transaksi_id") {
                                $tmpReg_result[$regRow->transaksi_id][$key_reg] = blobDecode($val_reg);
                            }
                        }

                    }
                }
            }

            $arrNextPIC = callNextPIC($arrNextAction);

            $numb = 0;
            foreach ($tmpHist as $row) {

                $transaksi_id = $row->transaksi_id;
                $gunggungan_mode = $row->gunggungan_mode;
                $extHistoryFields2 = isset($arrExtHistoryFields2[$row->step_number]) ? $arrExtHistoryFields2[$row->step_number] : array();
                $addMainSourceEditable = isset($addMainSource[$row->next_step_num]["editableFields"]) ? $addMainSource[$row->next_step_num]["editableFields"] : array();

                if (sizeof($pairRegistries) > 0) {
                    if ((sizeof($tmpReg_result) > 0) && (isset($tmpReg_result[$row->transaksi_id]))) {
                        foreach ($tmpReg_result[$row->transaksi_id] as $param => $eReg) {
                            switch ($param) {
                                case "main":
//                                    $this->debugArrPrint($eReg);
                                    foreach ($eReg as $k => $v) {
                                        if (!isset($row->$k)) {
                                            $row->$k = $v;
                                        }
                                    }
                                    break;
                                case "items":
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
                                                    }
                                                    $row->$k1 = $tmpDetail;
                                                }
                                            }
                                        }
                                    }
                                    break;
                            }
                        }
                    }
                    $arrItemShow = isset($this->configUi[$jenisTr]["shortItemsFields"]) ? $this->configUi[$jenisTr]["shortItemsFields"] : array();
                    $detail = viewDetailTransaksi($tmpReg_result[$row->transaksi_id], $arrItemShow, $row->jenis_master);
                }
                if (isset($arrAddReference[$row->id_master]) && (sizeof($arrAddReference[$row->id_master]) > 0)) {
                    foreach ($arrAddReference[$row->id_master] as $akey => $aval) {
                        $row->$akey = $aval;
                    }
                }


                $numb++;
                $tmp = array();
                foreach ($historyFields as $fName => $fLabel) {
                    if (isset($row->$fName)) {
                        if (is_numeric($row->$fName)) {
                            if (!isset($sumFooter[$fName])) {
                                $sumFooter[$fName] = 0;
                            }
                            $sumFooter[$fName] += $row->$fName;
                        }
                    }

                    if (is_array($fLabel)) {
                        $hisStep = $fLabel['step'];
                        $hisKey = $fLabel['key'];

                        if (isset($row->ids_his)) {
                            if ($hisKey == "nomer") {
                                $returnVal = showHistoriGlobalNumbers($row->ids_his, $hisStep, true, $this->jenisTr);
                                if ($returnVal == "") {
                                    $tmp[$fName] = "-";
                                }
                                else {
                                    $tmp[$fName] = $returnVal;
                                }
                            }
                            else {
                                $ids_his_decode = blobDecode($row->ids_his);
                                if (isset($ids_his_decode[$hisStep][$hisKey])) {
                                    $tmp[$fName] = $ids_his_decode[$hisStep][$hisKey];
                                }
                                else {
                                    $tmp[$fName] = "-";
                                }
                            }
                        }
                        else {
                            $tmp[$fName] = "-";
                        }
                    }
                    else {
                        $tmp[$fName] = isset($row->$fName) ? formatField_he_format($fName, $row->$fName) : formatField_he_format($fName, 0);
                    }

                    if ($fName == "no") {
                        $tmp[$fName] = formatField_he_format($fName, $numb);
                    }
                    //-----
                    if (sizeof($inputFakturTransaksi) > 0) {
                        if (isset($inputFakturTransaksi[$row->next_substep_num]["enabled"]) && ($inputFakturTransaksi[$row->next_substep_num]["enabled"] == true)) {
                            if (isset($addMainSourceEditable[$fName])) {
                                $defaultValue = isset($row->$fName) ? $row->$fName : "";
                                $type_input = $addMainSourceEditable[$fName];
                                $idd = $fName . "_" . $transaksi_id;
                                if (isset($arrTopData[$row->id_top])) {
//                                    $defaultValue = date($arrTopData[$row->id_top][$fName]);
//                                    cekHere("[$defaultValue] [$fName] [$type_input]");
                                    $idd = $fName . "_" . $arrTopData[$row->id_top]["transaksi_id"];
                                }

                                $disabled_input = "";
                                $bg_class = "bg-lime";
                                if (isset($filterCustomerNpwp["enabled"]) && ($filterCustomerNpwp["enabled"] == true)) {
                                    if (!isset($addData[$row->customers_id]["npwp"]) || ($addData[$row->customers_id]["npwp"] == NULL)) {
//                                        $disabled_input = "disabled='disabled'";
                                        $bg_class = "";
                                    }
                                }
                                $tmp[$fName] = "<input id='$idd' type='$type_input' $disabled_input
                                    name='$fName' value='$defaultValue'
                                    class='form-control text-right text-red text-bold $bg_class'>";


                            }
                        }
                    }

                }
                if (sizeof($row->cancel_packing_source_id) > 0) {
                    $this->debugArrPrint($row->cancel_packing_source_id);
                    $trx = new MdlTransaksi();
                    $trx->addFilter("id='" . $row->cancel_packing_source_id . "'");
                    $tmpTrx = $trx->lookupAll()->result();

                    //                        $tmp['nomer_top'] = $tmpTrx[0]->nomer;
                    $tmp['nomer_top'] = formatField_he_format("nomer_top", $tmpTrx[0]->nomer);
                    //                        $this->debugArrPrint($tmpTrx[0]->nomer);
                }

                if ($row->sub_step_number > 0) {
                    $tmp['state'] = "<span style='color:" . $this->configUi[$jenisTr]['steps'][$row->sub_step_number]['stateColor'] . "'>" . $this->configUi[$jenisTr]['steps'][$row->sub_step_number]['stateLabel'] . "</span>";
                    $tmp['state'] .= "<br>" . createStateSignNext($row->sub_step_number, $row->step_avail, $jenisTr);
                }
                else {
                    $tmp['state'] = "<span style='color:#777777'>canceled</span>";
                }


                $tmp['cabang2_kode'] = $this->cabang_code[$row->cabang2_id];
                $tmp['transaksi_id'] = $transaksi_id;
                $tmp['gunggungan_mode'] = $gunggungan_mode;


                //$detail = viewDetailTransaksi($tmpReg_result[$row->transaksi_id], $arrItemShow, $row->jenis_master);
                //$tblItems = $detail;
                //$tmp['item_fields'] = $tblItems;
                $tmp['item_fields'] = $detail;
                $tmp['next_pic'] = "-";
                if (sizeof($arrNextPIC) > 0) {
                    if (isset($arrNextPIC[$row->next_substep_code][$row->next_substep_num])) {
                        $next_pic = "";
                        $nob = 1;
                        foreach ($arrNextPIC[$row->next_substep_code][$row->next_substep_num] as $spec) {
                            if ($row->cabang_id == $spec['cabang_id']) {

                                if ($next_pic == "") {
                                    $next_pic = "$nob. " . $spec['nama'];
                                }
                                else {
                                    $nob++;
                                    $next_pic = $next_pic . "<br>" . "$nob. " . $spec['nama'];
                                }

                            }

                        }
                        $tmp['next_pic'] = $next_pic;
                        //                            cekHitam($next_pic);
                    }
                }
                $tmp['item_fields'] = $detail;
                $tmp['action'] = "";
                $tmp['action_approve'] = "";
                $nextStepNum = ($row->next_substep_num);
                $currentStepNum = ($row->sub_step_number);
                $currentStepCode = ($row->jenis);
                $nextStepCode = ($row->next_step_code);

                if ($row->sub_step_number > 0) {
                    //                        cekHere("koq");
                    $allowFollowup = true;
                    $actionLabel = "review " . $this->configUi[$jenisTr]['steps'][$currentStepNum]['label'];
                }

                if (isset($this->configUi[$jenisTr]['steps'][$nextStepNum])) {
                    if (isset($this->accessList[$jenisTr])) {
                        if (isset($this->accessList[$jenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"])) {
                            $allowFollowup = $this->accessList[$jenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"];
                            $actionLabel = $this->configUi[$jenisTr]['steps'][$nextStepNum]['actionLabel'];
                        }


                    }
                    else {
                        if (in_array($this->configUi[$jenisTr]['steps'][$nextStepNum]['userGroup'], $this->session->login['membership'])) {
                            $allowFollowup = true;
                            $actionLabel = $this->configUi[$jenisTr]['steps'][$nextStepNum]['actionLabel'];
                        }
                        else {
                            $allowFollowup = true;
                            $actionLabel = "review " . $this->configUi[$jenisTr]['steps'][$currentStepNum]['label'];
                        }
                    }
                }
                $req_cancel_qty = $row->req_cancel_qty != '' ? $row->req_cancel_qty : 0;
                $valid_qty = $row->valid_qty != '' ? $row->valid_qty : 0;

                if ($allowFollowup) {
                    $allowJoin = isset($this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin']) && $this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin'] == true ? $this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin'] : false;
                    $stepLabel = isset($this->configUi[$jenisTr]['steps'][$nextStepNum]['label']) ? $this->configUi[$jenisTr]['steps'][$nextStepNum]['label'] : "";
                    $isCancelPacking = isset($this->configUi[$jenisTr]['steps'][$nextStepNum]['isCancelPacking']) ? $this->configUi[$jenisTr]['steps'][$nextStepNum]['isCancelPacking'] : false;
                    $allowCancel = isset($this->configUi[$jenisTr]['steps'][$nextStepNum]['allowCancel']) ? $this->configUi[$jenisTr]['steps'][$nextStepNum]['allowCancel'] : false;

                    $targetFollowupLink = $isCancelPacking == true ? "followupCancelPackingPrePreview" : "followupPrePreview";


                    if (isset($inputFakturTransaksiApprove[$stepper]["enabled"]) && ($inputFakturTransaksiApprove[$stepper]["enabled"] == true)) {
                        if (isset($arrTopData[$row->id_top])) {
                            arrPrintHitam($arrTopData[$row->id_top]);
                            $disabled_request = "disabled";
                        }
                        else {
                            $disabled_request = "";
                        }
                    }
                    else {
                        $disabled_request = "";
                    }


                    $followupLink = "top.$('#result').load('" . MODUL_PATH . "FollowUp/$targetFollowupLink/$jenisTr/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "');";
                    //--------
                    $followup_id = "followup_" . $transaksi_id;
                    $tmp['action'] = "<div class='input-group'>";
                    $tmp['action'] .= "<button id='$followup_id' $disabled_request
                                class='btn btn-primary btn-block btn-sm' title='turn this entry into $stepLabel' href='javascript:void(0)' 
                                onClick =\"top.open_holdon();$followupLink\">" . $actionLabel . "</button>";
                    $tmp['action'] .= "</div class='input-group'>";
                    //--------
                    if ($req_cancel_qty > 0 && $valid_qty == 0) {
                        $tmp['action'] = "<div class='btn-group btn-block' role='group' aria-label='cancel packing on progress'>";
                        $tmp['action'] .= "<button type='button' disabled class='btn btn-warning' title='sedang dalam process cancel packing' href='javascript:void(0)'>menuggu approve cancel</button>";
                        $tmp['action'] .= "</div>";
                    }
                    //--------
                    if (sizeof($inputFakturTransaksi) > 0) {
                        if (isset($inputFakturTransaksi[$nextStepNum]["enabled"]) && ($inputFakturTransaksi[$nextStepNum]["enabled"] == true)) {
                            $dateFaktur_id = "dateFaktur_" . $transaksi_id;
                            $eFaktur_id = "eFaktur_" . $transaksi_id;
                            $simpan_id = "simpan_" . $transaksi_id;
                            $targetFollowupLinkFaktur = $inputFakturTransaksi[$nextStepNum]["prePreviewLink"];
                            $action_label_button = $inputFakturTransaksi[$nextStepNum]["action_label_button"];


                            $followupLinkFaktur = "top.$('#result').load('" . MODUL_PATH . "$targetFollowupLinkFaktur/$jenisTr/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "?dateFaktur='+document.getElementById('$dateFaktur_id').value+'&eFaktur='+document.getElementById('$eFaktur_id').value);";
                            $actionTarget = MODUL_PATH . "$targetFollowupLinkFaktur/$jenisTr/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "?dateFaktur='+document.getElementById('$dateFaktur_id').value+'&eFaktur='+document.getElementById('$eFaktur_id').value";
                            $tmp['action'] .= "<button id='$simpan_id' type='button' class='btn btn-success btn-block btn-sm' title='simpan Faktur PPN' $disabled_request
                                        style='margin-top:5px;'
                                        href='javascript:void(0)' 
                                        onClick=\"if(confirm('Tanggal dan Nomer Faktur akan disimpan. Klik OK untuk melanjutkan.')==1){top.open_holdon();$followupLinkFaktur}\"          
                                        >$action_label_button</button>";


                        }
                    }
                }
                else {

                }
                //REJECT------------------------------
                $pakai_ini = 1;
                if ($pakai_ini == 1) {
                    $arrStepAllow = array(1);
                    if (in_array($row->sub_step_number, $arrStepAllow)) {
                        $evPre = evaluatePreProcessors_he_menu($this->jenisTr, $currentStepNum, $this->configCoreJenis, $this->configUiJenis);
                        $evPost = evaluatePostProcessors_he_menu($this->jenisTr, $currentStepNum, $this->configCoreJenis, $this->configUiJenis);
                        $evCom = evaluateComponents_he_menu($this->jenisTr, $currentStepNum, $this->configCoreJenis, $this->configUiJenis);
//                        cekHitam($row->transaksi_id . " || evPreProcc: $evPre, evPostProcc: $evPost, evCom: $evCom");
                        if ($evPre == null && $evPost == null && $evCom == null) {
                            $transaksiID_reject = $row->transaksi_id;
                            $masterRevertStep = ($currentStepNum - 1);
                            $childRevertStep = -($currentStepNum);

                            $disabled_hold = isset($disabled_hold) ? $disabled_hold : "";
                            $disabled_button_reject = isset($disabled_button_reject) ? $disabled_button_reject : "";
                            $disabled_request = isset($disabled_request) ? $disabled_request : "";
                            if (isset($this->reject) && $this->reject == true) {
                                $link_reject = MODUL_PATH . "FollowUp/followupPrePreview/" . $this->jenisTr . "/$transaksiID_reject/$nextStepNum/" . $row->sub_step_number . "?getmode=reject";
                                $actionLabel_edit = "Reject 1 step";
                                $tmp['action'] .= "<button class='btn btn-danger btn-block btn-xs' title='turn this entry into $stepLabel' href='javascript:void(0)' 
                                $disabled_hold $disabled_button_reject $disabled_request
                                onclick =\"document.getElementById('result').src='$link_reject'\">" . $actionLabel_edit . " </button>";
                            }

                            if (isset($this->reject_all) && $this->reject_all == true) {
                                $link_reject_all = MODUL_PATH . "FollowUp/followupPrePreview/" . $this->jenisTr . "/$transaksiID_reject/$nextStepNum/" . $row->sub_step_number . "?getmode=rejectall";
                                $actionLabel_edit = "Reject all step";
                                $tmp['action'] .= "<button class='btn btn-danger btn-block btn-xs' title='turn this entry into $stepLabel' href='javascript:void(0)' 
                                style='background-color:#000000;color:#ffffff;'
                                $disabled_hold $disabled_button_reject $disabled_request
                                onclick =\"document.getElementById('result').src='$link_reject_all'\">" . $actionLabel_edit . " </button>";
                            }
                        }

                    }
                }
                //------------------------------

                if ($allowFollowup) {
                    if (isset($inputFakturTransaksiApprove[$stepper]["enabled"]) && ($inputFakturTransaksiApprove[$stepper]["enabled"] == true)) {
                        if (isset($arrTopData[$row->id_top])) {
                            $disabled_request = "disabled";
                        }
                        else {
                            $disabled_request = "";
                        }
                    }
                    else {
                        $disabled_request = "";
                    }

                    //---------------------------------------
                    if (isset($inputFakturTransaksiApprove[$stepper]["enabled"]) && ($inputFakturTransaksiApprove[$stepper]["enabled"] == true)) {
                        if (isset($arrTopData[$row->id_top])) {
                            $transaksi_id_approve = $arrTopData[$row->id_top]["transaksi_id"];
                            $dateFaktur_id = "dateFaktur_" . $transaksi_id_approve;
                            $eFaktur_id = "eFaktur_" . $transaksi_id_approve;
                            $simpan_id = "simpan_" . $transaksi_id_approve;
                            $num_asal = 2;
                            $num_target = 3;
                            $disabled_approve = "";
                            $btn_approve = "btn-success";
                        }
                        else {
                            $disabled_approve = "disabled";
                            $btn_approve = "btn-default";
                            $transaksi_id_approve = "";
                            $dateFaktur_id = "";
                            $eFaktur_id = "";
                            $simpan_id = "";
                            $num_asal = 2;
                            $num_target = 3;
                        }
                        $targetFollowupLinkFaktur = isset($targetFollowupLinkFaktur) ? $targetFollowupLinkFaktur : "";
                        $followupLinkFakturApprove = "top.$('#result').load('" . MODUL_PATH . "$targetFollowupLinkFaktur/$jenisTr/" . $transaksi_id_approve . "/$num_target/" . $num_asal . "?dateFaktur='+document.getElementById('$dateFaktur_id').value+'&eFaktur='+document.getElementById('$eFaktur_id').value);";
//                                $followupLinkFakturApprove = "";
                        $action_label_button_approve = "Simpan dan Approve";
//                                $tmp['action_approve'] .= "<button id='$simpan_id' type='button' class='btn btn-success btn-block btn-sm' title='simpan Faktur PPN' $disabled_approve
                        $tmp['action'] .= "<br><button id='$simpan_id' type='button' class='btn $btn_approve btn-block btn-sm' title='simpan Faktur PPN' $disabled_approve
                                        style='margin-top:5px;'
                                        href='javascript:void(0)' 
                                        onClick=\"if(confirm('Tanggal dan Nomer Faktur akan disimpan. Klik OK untuk melanjutkan.')==1){top.open_holdon();$followupLinkFakturApprove}\"          
                                        >$action_label_button_approve</button>";
                    }
                    //---------------------------------------

                }


                if ($gunggungan_mode == 1) {
                    $arrayOnprogressGunggungMark[]["style"] = "background-color:yellow;";
                    $tmp['jenis_pajak'] = "PPN GUNGGUNGAN";
                }
                else {
                    $arrayOnprogressGunggungMark[] = array();
                    $tmp['jenis_pajak'] = "REGULER";
                }
                if(isset($row->projectID) && $row->projectID > 0){
//                    $tmp['tagihan'] = $row->new_net3;
                    $tmp['tagihan'] = formatField_he_format("tagihan", $row->new_net3);
                }



                $tmpMark = array();
                $tmp['keterangan'] = "-";
                if ($row->partial == 1) {
                    $tmpMark['style'] = "background-color:yellow;";
                    $tmp['keterangan'] = "<span style='color:red;'>transaksi diproses sebagian</span>";
                }

                if ($row->step_number == 1) {
                    if (sizeof($filterCustomerNpwp) > 0) {
                        if (isset($filterCustomerNpwp["enabled"]) && ($filterCustomerNpwp["enabled"] == true)) {
                            if (isset($addData[$row->customers_id]["npwp"]) && ($addData[$row->customers_id]["npwp"] != NULL)) {
                                $arrayOnprogress[$currentStepNum][] = $tmp;
                                $arrayOnprogressPartialMark[] = $tmpMark;
                                $arrayOnprogressGroupPartialMark[$currentStepNum][] = $tmpMark;
                            } else {
                                $tmp['action'] = "<span style='color:red; font-size:11px;'><b>Hanya bisa diproses di<br>TAB Prepare Gunggungan</b></span>";
                                $tmp['row_style'] = "background-color:#dcdcdc;";
                                $arrayOnprogress[$currentStepNum][] = $tmp;
                                $arrayOnprogressPartialMark[] = $tmpMark;
                                $arrayOnprogressGroupPartialMark[$currentStepNum][] = $tmpMark;
                            }
                        }
                        else {
                            $arrayOnprogress[$currentStepNum][] = $tmp;
                            $arrayOnprogressPartialMark[] = $tmpMark;
                            $arrayOnprogressGroupPartialMark[$currentStepNum][] = $tmpMark;
                        }
                    }
                    else {
                        $arrayOnprogress[$currentStepNum][] = $tmp;
                        $arrayOnprogressPartialMark[] = $tmpMark;
                        $arrayOnprogressGroupPartialMark[$currentStepNum][] = $tmpMark;
                    }
                }
                else {
                    $arrayOnprogress[$currentStepNum][] = $tmp;
                    $arrayOnprogressPartialMark[] = $tmpMark;
                    $arrayOnprogressGroupPartialMark[$currentStepNum][] = $tmpMark;
                }

            }

        }
        //endregion

        //region link to add new transaction
        if (placeCanMakeTrans_he_menu($this->session->login['membership'], $this->session->login['cabang_id'], $this->session->login['gudang_id'], $this->jenisTr, $this->configUiJenis)) {
            $createIndexes = (null != $this->config->item("transaksi_createIndex")) ? $this->config->item("transaksi_createIndex") : array();
            $isDisableMakeTrans = isset($this->configUi[$this->jenisTr]['isDisableMakeTrans']) ? $this->configUi[$this->jenisTr]['isDisableMakeTrans'] : false;
            if (array_key_exists($this->jenisTr, $createIndexes)) {
                $targetUrl = MODUL_PATH . $createIndexes[$this->jenisTr] . "/" . $this->jenisTr;
            }
            else {
                $targetUrl = MODUL_PATH . "Create/index/" . $this->jenisTr;
            }

            if ($isDisableMakeTrans) {
                $addLink = null;
            }
            else {
                $addLink = array(
                    "link" => $targetUrl,
                    "label" => "<span class='glyphicon glyphicon-plus'></span> create new " . $this->configUi[$jenisTr]["steps"][1]['label'],
                );
            }

        }
        else {
            $addLink = null;
        }
        //endregion

        $jenisTrsub = isset($_GET['step']) ? $_GET['step'] : 1;
        $historyFieldsDt = isset($this->configUi[$jenisTr]['historyFields'][$jenisTrsub]) ? $this->configUi[$jenisTr]['historyFields'][$jenisTrsub] : $this->configUi[$jenisTr]['shortHistoryFields'];
        $availDbTable = $tr->getAvailTable($historyFieldsDt);

        //-----------------------------------------------------
        $arrJenisTrCek = array("587", "687", "1587", "1687");
        if (in_array($jenisTr, $arrJenisTrCek)) {
            $transaksiName = isset($this->configUi[$jenisTr]['label']) ? $this->configUi[$jenisTr]['label'] : NULL;
            $subplace = isset($this->configUi[$jenisTr]['steps'][1]['subplace']) ? $this->configUi[$jenisTr]['steps'][1]['subplace'] : NULL;
            if ($subplace != NULL) {
                if (($subplace == "warehouse") && ($this->session->login['gudang_id'] < 0)) {
                    //                    cekPink("SESUAI");
                    $msg = NULL;
                }
                elseif (($subplace == "warehouse_ng") && ($this->session->login['gudang_id'] > 0)) {
                    //                    cekPink("SESUAI NOT GOOD");
                    $msg = NULL;
                }
                else {
                    $msg = "Anda tidak memiliki kewenangan untuk membuat request $transaksiName";
                    $addLink = array();
                }
            }
        }

        //-----------------------------------------------------
        $endtime = microtime(true); // Bottom of page
        $val = $endtime - $starttime;
        $mode = isset($this->configUi[$jenisTr]["mode"]) ? $this->configUi[$jenisTr]["mode"] : $this->uri->segment(3);
//        cekHitam("[$mode]");
//        cekHitam("[$getDate] [$mode]");
//        arrPrintKuning($progressFields);
//
        $mode = "viewUndoneItemsIndex";
        $data = array(
//            "mode" => isset($this->configUi[$jenisTr]["mode"]) ? $this->configUi[$jenisTr]["mode"] : $this->uri->segment(3),
            "mode" => $mode,
            "isMobile" => $isMob,
            "errMsg" => $this->session->errMsg,
            "template" => $this->configUi[$jenisTr]["template"],
            "title" => $this->configUi[$jenisTr]["label"],
            "subTitle" => $this->configUi[$jenisTr]["steps"][1]['label'],
            "jenisTr" => $jenisTr,
            "trName" => $this->configUi[$jenisTr]["label"],
            'addLink' => $addLink,
            "allSteps" => $this->allSteps,
            "historyTitle" => "<span class='glyphicon glyphicon-time'></span> recent " . $this->configUi[$jenisTr]["label"] . " histories",
            "arrayHistoryLabels" => array("dtime" => "time") + $historyFields,
            "arrayHistoryLabelsDt" => $historyFieldsDt,
            "availDbTable" => $availDbTable,
            "arrayHistory" => isset($arrayHistory) ? $arrayHistory : array(),
            "onprogressTitle" => "<span class='glyphicon glyphicon-alert'></span> TRANSAKSI YANG PERLU ACTION ",
            "arrayProgressLabels" => $progressFields,
            "arrayOnProgressToPay" => $paymentConfig,
            "itemLabels" => isset($itemLabels) ? $itemLabels : "",
            "srcLabel" => isset($srcLabel) ? $srcLabel : "",
            //            "arrayOnProgress" => array_merge($arrayOnprogress, $arrayOnprePre, $arrayOnpreDistribution),
            "steps" => $steps,
            "arrayOnProgress" => (isset($arrayOnprogress) && sizeof($arrayOnprogress) > 0) ? $arrayOnprogress : array(),
            "arrayOnprogressGroup" => (isset($arrayOnprogressGroup) && (sizeof($arrayOnprogressGroup) > 0)) ? $arrayOnprogressGroup : array(),
            "arrayOnprogressGroupNonNpwp" => (isset($arrayOnprogressGroupNonNpwp) && (sizeof($arrayOnprogressGroupNonNpwp) > 0)) ? $arrayOnprogressGroupNonNpwp : array(),
            "stepCounts" => isset($stepCounts) ? $stepCounts : array(),
            "allStepOneCount" => isset($allStepOneCount) ? $allStepOneCount : 0,
            "npwpStepOneCount" => isset($npwpStepOneCount) ? $npwpStepOneCount : 0,
            "nonNpwpCount" => isset($nonNpwpCount) ? $nonNpwpCount : 0,
            "arrayOnprePre" => $arrayOnprePre,
            "arrayOnprePreGroup" => $arrayOnprePreGroup,

            "arrayOnpreDistribution" => isset($arrayOnpreDistribution) ? $arrayOnpreDistribution : array(),
            "arrayOnpreDistributionGroup" => isset($arrayOnpreDistributionGroup) ? $arrayOnpreDistributionGroup : array(),

            "entities" => isset($entities) ? $entities : array(),
            "recapTitle" => "<span class='fa fa-newspaper-o'></span> today " . $this->configUi[$jenisTr]["label"] . " reports",
            "arrayRecapLabels" => isset($recapLabels) ? $recapLabels : array(),
            "arrayRecap" => isset($arrayRecap) ? $arrayRecap : array(),

            "onprogressViewTitle" => "<span class='fa fa-eye'></span> show incomplete step " . $this->configUi[$jenisTr]["label"],
            "onprogressViewSubTitle" => "<span class='text-black'>(daftar transaksi yang masih stanby di cabang tujuan)</span>",
            "arrayOnProgressView" => isset($arrayOnProgressView) ? $arrayOnProgressView : array(),
            "stepHistoryFields" => isset($stepHistoryFields) ? $stepHistoryFields : array(),

            "selectProcessor" => $selectProcessor,
            "sumFooter" => (isset($sumFooterResult) && sizeof($sumFooterResult) > 0) ? $sumFooter : array(),
            "scriptBottom" => $scriptBottom,
            "btnLabel" => $extData,
            "actionTarget" => $extact,
            "sumFooter" => isset($sumFooter) ? $sumFooter : "",

            "arrayOnprogressPartialMark" => (isset($arrayOnprogressPartialMark) && sizeof($arrayOnprogressPartialMark) > 0) ? $arrayOnprogressPartialMark : array(),
            "arrayOnprogressGroupPartialMark" => (isset($arrayOnprogressGroupPartialMark) && sizeof($arrayOnprogressGroupPartialMark) > 0) ? $arrayOnprogressGroupPartialMark : array(),

            "defaultItemTrgEditable" => isset($defaultItemTrgEditable) ? $defaultItemTrgEditable : array(),
            "editItemTrg" => MODUL_PATH . "_followupLiveEdit/editEfaktur/" . $this->jenisTr . "/",
            "arrNextPIC" => isset($arrNextPIC) ? $arrNextPIC : array(),
            "btnPage" => isset($btnPage) ? $btnPage : "",
            "gPage" => isset($page) ? $page : 1,
            "jmlPerhalaman" => isset($jmlPerhalaman) ? $jmlPerhalaman : 10,
            "urutanDb" => isset($urutanDb) ? $urutanDb : "",

            "gunggungFieldsView" => $gunggungFieldsView,
            "getDate" => $getDate,

            "arrayOnprogressGunggungMark" => $arrayOnprogressGunggungMark,
            "reloadLink" => MODUL_PATH . get_class($this) . "/" . __FUNCTION__ . "/" . $this->jenisTr . "?step=$stepper",
            "entry_faktur_masal" => isset($this->configUi[$this->jenisTr]["entry_faktur_masal"]) ? $this->configUi[$this->jenisTr]["entry_faktur_masal"] : false,
        );

        $this->load->view("transaksi", $data);
    }


    public function viewRequestItems()
    {

        $origJenisTr = $this->uri->segment(4);
        $jenisTr = isset($this->configUi[$origJenisTr]['aliasMainTrans']) ? $this->configUi[$origJenisTr]['aliasMainTrans'] : $origJenisTr;

        $this->jenisTr = $jenisTr;
        // $cCode = $this->cCode;
        $cCode = "_TR_" . $this->jenisTr;
        $cCodeOrig = "_TR_" . $origJenisTr;

        $historyFields = isset($this->configUi[$jenisTr]['shortHistoryFields']) ? $this->configUi[$jenisTr]['shortHistoryFields'] : array();
        $tabHistoryFields = isset($this->configUi[$jenisTr]['tabHistoryFields']) ? $this->configUi[$jenisTr]['tabHistoryFields'] : array();
        $tabFieldsItems = isset($this->configUi[$jenisTr]['tabFieldsItems']) ? $this->configUi[$jenisTr]['tabFieldsItems'] : array();
        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
        $dueDateAllow = isset($this->configUi[$jenisTr]['updateDueDate']) ? $this->configUi[$jenisTr]['updateDueDate'] : array();


        $mb = New MobileDetect();
        $isMob = $mb->isMobile();

        if ($isMob) {
            $historyFields = isset($this->configUi[$jenisTr]['compactHistoryFields']) ? $this->configUi[$jenisTr]['compactHistoryFields'] : array();
        }
        $glanceHistoryFields = isset($this->configUi[$jenisTr]['glanceHistoryFields']) ? $this->configUi[$jenisTr]['glanceHistoryFields'] : array("nomer" => "receipt no");
        $glanceHistoryFields2 = array("nomer" => "receipt no"); //init;

        //region lookup on-going transactions
        $arrayOnprogress = array();
        $arrOver_due = array();
        $progressFields = $historyFields;


        //region lookup on-going from connected requests
        if (!isset($_SESSION['undoneQty'])) {
            $_SESSION['undoneQty'] = array();
        }
        if (!isset($_SESSION['undoneQty'][$this->jenisTr])) {
            $_SESSION['undoneQty'][$this->jenisTr] = 0;
        }

        $progress2Fields = array();
        $arrayOnprogress2 = array();
        $tmpTabData = array();
        $reqFormTarget = "";
        $allowMultiSelect = false;
        $needToClear = false;
        $allowMultiSelect = isset($this->configUi[$origJenisTr]['tabRequestCode']['allowMultiSelect']) ? $this->configUi[$origJenisTr]['tabRequestCode']['allowMultiSelect'] : false;


        if (isset($this->configUi[$this->uri->segment(4)]['tabRequestCode'])) {
            $masterRefCode = $this->configUi[$this->uri->segment(4)]['tabRequestCode']['masterCode'];
            $stateRefCode = $this->configUi[$this->uri->segment(4)]['tabRequestCode']['stateCode'];
            $stateRefNum = $this->configUi[$this->uri->segment(4)]['tabRequestCode']['stepNumber'];
            cekHere("$masterRefCode :: $stateRefCode :: $stateRefNum");
            $progress2Fields = isset($this->configUi[$masterRefCode]['shortHistoryFields']) ? $this->configUi[$masterRefCode]['shortHistoryFields'] : "";
            $glanceHistoryFields2 = isset($this->configUi[$masterRefCode]['glanceHistoryFields']) ? $this->configUi[$masterRefCode]['glanceHistoryFields'] : array("nomer" => "receipt no");

            $reqFormTarget = MODUL_PATH . get_class($this) . "/swapFrom/" . $this->uri->segment(4);

            $this->load->model("MdlTransaksi");
            $tr = new MdlTransaksi();
            $tr->addFilter("transaksi.cabang_id='" . $this->session->login['cabang_id'] . "'");
            $tr->addFilter("transaksi.jenis_master='" . $masterRefCode . "'");
            $tr->addFilter("transaksi.jenis='" . $stateRefCode . "'");
            $tr->addFilter("transaksi.step_current='" . $stateRefNum . "'");
            if (isset($_GET['test']) && ($_GET['test'] == 1)) {
//                $tmpByReq = $tr->lookupRecentHistories()->result();

                $tr->addFilterJoin("transaksi_data.next_substep_code='" . $masterRefCode . "'");
                $tr->addFilterJoin("transaksi_data.produk_jenis='supplies'");
                $tr->addFilterJoin("transaksi_data.valid_qty>0");
                $tmpByReq = $tr->lookupJoined();
            }
            else {
                $tmpByReq = array();
            }
            cekHijau(__LINE__ . "<br>" . $this->db->last_query());
            matiHere(sizeof($tmpByReq));

            $needToClear = false;
            if (isset($_SESSION[$cCode]['items']) && sizeof($_SESSION[$cCode]['items']) > 0) {
                $needToClear = true;
            }

            $_SESSION['undoneQty'][$this->jenisTr] = sizeof($tmpByReq);

            //$this->debugArrPrint($tmpByReq);
            if (sizeof($tmpByReq) > 0) {
                $arrIds = array();
                foreach ($tmpByReq as $row) {
                    $arrIds[] = $row->id;
                }

                $tr->setFilters(array());
                $tr->addFilterJoin("transaksi_data.transaksi_id IN ('" . implode("','", $arrIds) . "')");
                $tr->addFilterJoin("transaksi_data.next_substep_code='" . $masterRefCode . "'");
                $tr->addFilter("transaksi.link_id=0");
                $tr->addFilterJoin("transaksi_data.produk_jenis='supplies'");
                $tr->addFilterJoin("transaksi_data.valid_qty>0");
                $trTr = $tr->lookupJoined();
                cekMerah(__LINE__ . " -/- " . $this->db->last_query());
                //$this->debugArrPrint($trTr);
                $arrayTabHistory = array();
                if (sizeof($trTr) > 0) {

                    $tmpItems = array();
                    $sumValidQty = array();
                    $sumPrdOrdJml = array();
                    $listTrxByProd = array();
                    $reqCtr = 0;

                    foreach ($trTr as $ky => $rows) {

                        $reqCtr++;
                        $totalSumPrdOrdJmlTR = 0;
                        if (!isset($sumValidQtyID[$rows->produk_id])) {
                            $sumValidQtyID[$rows->produk_id] = 0;
                        }
                        if (!isset($sumPrdOrdJmlID[$rows->produk_id])) {
                            $sumPrdOrdJmlID[$rows->produk_id] = 0;
                        }
                        if (!isset($sumValidQtyTR[$rows->transaksi_id][$rows->produk_id])) {
                            $sumValidQtyTR[$rows->transaksi_id][$rows->produk_id] = 0;
                        }
                        if (!isset($sumPrdOrdJmlTR[$rows->transaksi_id][$rows->produk_id])) {
                            $sumPrdOrdJmlTR[$rows->transaksi_id][$rows->produk_id] = 0;
                        }
                        $sumValidQtyID[$rows->produk_id] += $rows->valid_qty;
                        $sumValidQtyTR[$rows->transaksi_id][$rows->produk_id] += $rows->valid_qty;
                        $listTrxByProd[$rows->produk_id][] = $rows->nomer_top;
                        $sumPrdOrdJmlID[$rows->produk_id] += $rows->valid_qty;
                        $sumPrdOrdJmlTR[$rows->transaksi_id][$rows->produk_id] += $rows->valid_qty;
                        $arrProduk[$rows->transaksi_id][$rows->produk_id] = "<div>" . $rows->produk_nama . "</div>";
                        $needToClear = false;
                        if (isset($_SESSION[$cCode]['items']) && sizeof($_SESSION[$cCode]['items']) > 0) {
                            $needToClear = true;
                        }

                        foreach ($tabHistoryFields as $key => $row) {
                            //cekHere($key);
                            switch ($key) {
                                case "vendor_id":
                                    break;
                                case "transaksi_id":
                                    $totalSumPrdOrdJmlTR = 0;
                                    $inputType = $allowMultiSelect == true ? "checkbox" : "radio";
                                    foreach ($sumPrdOrdJmlTR[$rows->transaksi_id] as $pid => $val) {
                                        $totalSumPrdOrdJmlTR += $val;
                                    };
                                    $produk_btn = "<span href='" . base_url() . "Addons/ViewDetails/index/" . $rows->transaksi_id . "' name='qtips' class='btn btn-sm'> berisi " . sizeof($sumPrdOrdJmlTR[$rows->transaksi_id]) . " item, Total " . $totalSumPrdOrdJmlTR . " unit  </span>";
                                    $clickEvent = "";
                                    if ($inputType == "radio" && !$needToClear) {
                                        $clickEvent = "onclick=\"document.getElementById('$key').submit()\"";
                                    }

                                    $transaksi_id = array(
                                        "select" => "<input type=$inputType name='trID[]' value='" . $rows->transaksi_id . "' id='select_" . $key . "_" . $reqCtr . "' $clickEvent>",
                                        "nomer" => formatField('nomer', $rows->nomer),
                                        "nomer_top" => formatField('nomer_top', $rows->nomer_top),
                                        "produk_id" => $rows->transaksi_id,
                                        "dtime" => formatField('dtime', $rows->dtime) . "<br><span class='meta'>" . timeSince(strtotime($rows->dtime)) . "</span>",
                                        "oleh_nama" => $rows->oleh_nama,
                                        "cabang2_nama" => $rows->cabang2_nama,
                                        "purchased" => 0,
                                        "arrProduk" => $produk_btn,
                                        "produk_nama" => $rows->produk_nama,
                                        "valid_qty" => $sumValidQtyTR[$rows->transaksi_id][$rows->produk_id],
                                        "produk_ord_jml" => $sumPrdOrdJmlTR[$rows->transaksi_id][$rows->produk_id],
                                    );
                                    $nextStepNum = $rows->next_step_num;
                                    $followupLink = "top.$('#result').load('" . MODUL_PATH . "FollowUp/followupPrePreview/" . $this->jenisTr . "/" . $rows->transaksi_id . "/$nextStepNum/" . $rows->sub_step_number . "');";

                                    $actionLabel = "reject";
                                    $tmp = "<div class='input-group'>";
                                    $tmp .= "<a class='btn btn-danger btn-block' title='' href='javascript:void(0)' 
                                        onClick =\"$followupLink\">" . $actionLabel . "</a>";

                                    $tmp .= "</div class='input-group'>";
                                    $transaksi_id['action'] = $tmp;

                                    $tmpTabData[$key][$rows->transaksi_id] = $transaksi_id;
                                    break;
                                case "produk_id":
                                    $inputType = $allowMultiSelect == true ? "checkbox" : "radio";
                                    $arrTrxId = $listTrxByProd[$rows->produk_id];
                                    $listingTrx = array();
                                    foreach ($arrTrxId as $pid => $nomer) {
                                        $listingTrx[] = formatField('nomer_top', $nomer);
                                    }
                                    $clickEvent = "";
                                    if ($inputType == "radio" && !$needToClear) {
                                        $clickEvent = "onclick=\"document.getElementById('$key').submit()\"";
                                    }

                                    $produk_id = array(
                                        "select" => "<input type=$inputType name='prdID[]' value='" . $rows->produk_id . "' id='select_" . $key . "_" . $reqCtr . "' $clickEvent>",
                                        "produk_id" => $rows->produk_id,
                                        "dtime" => formatField('dtime', $rows->dtime) . "<br><span class='meta'>" . timeSince(strtotime($rows->dtime)) . "</span>",
                                        "produk_nama" => $rows->produk_nama,
                                        "nomer_top" => implode("<br>", $listingTrx),
                                        "produk_ord_jml" => $sumPrdOrdJmlID[$rows->produk_id],
                                    );

                                    $tmpTabData[$key][$rows->produk_id] = $produk_id;
                                    break;
                            }

                        }

                    }

                    $arrayOnprogress2 = $tmpTabData;
                }
            }
            if (isset($_SESSION[$cCode]['items']) && sizeof($_SESSION[$cCode]['items']) > 0) {
                $needToClear = true;
            }
        }
        //endregion


        $data = array(
            "mode" => $this->uri->segment(3),
            "isMobile" => $isMob,
            "arrayProgressLabels" => $progressFields,
            "arrayProgress2Labels" => $glanceHistoryFields2,
            "arrayOnProgress" => $arrayOnprogress,
            "arrayOnProgress2" => $arrayOnprogress2,
            "needToClear" => $needToClear,
            "reqFormTarget" => $reqFormTarget,
            "allowMultiSelect" => $allowMultiSelect,
            "overDue" => $arrOver_due,
            "tabFieldsItems" => $tabFieldsItems,
            "tabHistoryFields" => $tabHistoryFields,
            "clearCartTarget" => MODUL_PATH . "_shoppingCart/reset/" . $this->jenisTr,
            "scriptBottom" => "",
        );

        $this->load->view("transaksi", $data);

    }

    //done update modul
    public function viewResume()
    {
        $globalVars = array();
        $no = $this->uri->segment(5);
        // $this->jenisTr = explode(".", $no)[0];
        $cCode = $this->cCode;
        $this->load->config("heAccounting");
        $accountChildsLinks = $this->config->item("accountChildsLinks");

        $this->load->model("MdlTransaksi");
        // $this->load->model("Mdls/MdlMongoMother");
        $tr = new MdlTransaksi();
        $tr->addFilter("nomer='$no'");
        $tmp1 = $tr->lookupAll()->result();
        if (sizeof($tmp1) == 0) {
            $tr->removeFilter("nomer='$no'");
            $tr->addFilter("nomer2='$no'");
            $tmp1 = $tr->lookupAll()->result();
        }
//        $this->debugShowLastQuery("biru");
        if (sizeof($tmp1[0]) > 0) {
            foreach ($tmp1[0] as $tmpKey => $tmpVal) {
                $rkey_field = $tmpKey;
                if ($rkey_field == "nomer") {
                    $rkey_field = "nomer_nolink";
                }
//                $globalDatas[$rkey] = formatField_he_format($rkey_field, $rval, "", "");
                $globalVars[$tmpKey] = formatField_he_format($rkey_field, $tmpVal, "", "");
            }
        }

        $jenisMasterTrans = $tmp1[0]->jenis_master;
        $transaksiID = $tmp1[0]->id;
        $transaksiTopID = $tmp1[0]->id_top;
        $gunggunganMode = $tmp1[0]->gunggungan_mode;
        $step_number = $tmp1[0]->step_number < 1 ? 1 : $tmp1[0]->step_number;
        $configUiJenis = loadConfigModulJenis_he_misc($jenisMasterTrans, "coTransaksiUi");
        $globalVars['transaksi_nama'] = $configUiJenis['label'];
        $globalVars["transaksi_nama_" . $step_number] = $configUiJenis['steps'][$step_number]["label"];
        $globalVars["transaksi_actionLabel"] = $configUiJenis['steps'][$step_number]["actionLabel"];
        $pairRegistry = isset($configUiJenis["pairRegistries"]) ? $configUiJenis["pairRegistries"] : array("main", "items");
        $configNotes = isset($configUiJenis["canceledLabel"][$step_number]) ? $configUiJenis["canceledLabel"][$step_number] : NULL;

        // membaca status edit di TOPID
        $tr = new MdlTransaksi();
        $tr->addFilter("id='$transaksiTopID'");
        $tmpTop = $tr->lookupAll()->result();
        $arrTopEdit = array();
        if (sizeof($tmpTop) > 0) {
            if ((isset($tmpTop[0]->status_edit)) && ($tmpTop[0]->status_edit == 1)) {
                $arrTopEdit[$transaksiTopID] = array(
                    "status_edit" => $tmpTop[0]->status_edit,
                    "edit_dtime" => $tmpTop[0]->edit_dtime,
                    "edit_id" => $tmpTop[0]->edit_id,
                    "edit_name" => $tmpTop[0]->edit_name,
                );
            }
        }


        //region swap from registry

        $tr = new MdlTransaksi();
        $tr->setFilters(array());
        $tr->setJointSelectFields($pairRegistry);//select fields jika ada, atau biarkana array untuk reset
        $tr->addFilter("transaksi_id='$transaksiID'");
        $tmpReg = $tr->lookupDataRegistries()->result();

        if (sizeof($tmpReg) > 0) {
            if (sizeof($tmpReg) > 0) {
                foreach ($tmpReg as $eParams) {
                    foreach ($pairRegistry as $param) {
                        $$param = blobDecode($eParams->$param);
                    }
                }
            }
        }
        //endregion

        //region signatures
        $signNumbers = array();
        $signValues = array();
        $signExtValues = array();
        $trs = new MdlTransaksi();
        $trs->setFilters(array());
        $masterID = isset($tmp1[0]->id_master) ? $tmp1[0]->id_master : 0;
        $masterIDs = array();
        if ($masterID > 0) {
            $masterIDs[] = $masterID;
        }

        // Resolusi rantai transaksi timbal balik (Bidirectional Chain Resolution)
        // Bedakan faktur pajak gabungan (Step 2: 110e & Step 3: 110) dari prepare faktur (Step 1: 110r)
        $isTaxInvoiceMaster = false;
        if ($jenisMasterTrans == '110' && ($step_number >= 2 || strpos($tmp1[0]->jenis, '110e') !== false || $tmp1[0]->jenis == '110')) {
            $isTaxInvoiceMaster = true;
        }

        if ($isTaxInvoiceMaster) {
            // Faktur pajak gabungan (110e / 110)
            $taxMasterID = (!empty($tmp1[0]->id_master) && $tmp1[0]->id_master > 0) ? $tmp1[0]->id_master : $tmp1[0]->id;
            if (!in_array($taxMasterID, $masterIDs)) {
                $masterIDs[] = $taxMasterID;
            }

            // Baca record master faktur pajak
            $this->db->select("reference_id");
            $this->db->from("transaksi");
            $this->db->where("id", $taxMasterID);
            $qTaxRef = $this->db->get()->result();
            if (sizeof($qTaxRef) > 0 && !empty($qTaxRef[0]->reference_id) && $qTaxRef[0]->reference_id > 0) {
                $this->db->select("id_master");
                $this->db->from("transaksi");
                $this->db->where("id", $qTaxRef[0]->reference_id);
                $qRef = $this->db->get()->result();
                if (sizeof($qRef) > 0 && !empty($qRef[0]->id_master) && !in_array($qRef[0]->id_master, $masterIDs)) {
                    $masterIDs[] = $qRef[0]->id_master;
                }
            }

            // Ambil transaksi_data dari master pajak (hanya pada 110e yang berisi transaksi 110r hulu)
            $this->db->select("produk_id, sub_referensi_id_2, sub_referensi_id_4");
            $this->db->from("transaksi_data");
            $this->db->where("transaksi_id", $taxMasterID);
            $qTd = $this->db->get()->result();
            if (sizeof($qTd) > 0) {
                $refProdIDs = array();
                foreach ($qTd as $tdRow) {
                    if (!empty($tdRow->produk_id) && $tdRow->produk_id > 0) {
                        $refProdIDs[] = $tdRow->produk_id;
                    }
                    if (!empty($tdRow->sub_referensi_id_2) && $tdRow->sub_referensi_id_2 > 0) {
                        $refProdIDs[] = $tdRow->sub_referensi_id_2;
                    }
                    if (!empty($tdRow->sub_referensi_id_4) && $tdRow->sub_referensi_id_4 > 0) {
                        $refProdIDs[] = $tdRow->sub_referensi_id_4;
                    }
                }
                if (sizeof($refProdIDs) > 0) {
                    $this->db->select("id_master");
                    $this->db->from("transaksi");
                    $this->db->where_in("id", array_unique($refProdIDs));
                    $qPm = $this->db->get()->result();
                    if (sizeof($qPm) > 0) {
                        foreach ($qPm as $pmRow) {
                            if (!empty($pmRow->id_master) && !in_array($pmRow->id_master, $masterIDs)) {
                                $masterIDs[] = $pmRow->id_master;
                            }
                        }
                    }
                }
            }
        } else {
            // Dokumen non-faktur-gabungan (Sales Order ATAU Prepare E-Faktur 110r Step 1)
            // Kumpulkan ID prepare faktur (110r)
            $taxChildIDs = array();
            if ($jenisMasterTrans == '110') {
                // Jika dokumen saat ini adalah 110r, dokumen ini sendiri adalah prepare faktur
                $taxChildIDs[] = $transaksiID;
            } else {
                // Jika dokumen saat ini adalah SO, cari 110r di bawahnya
                $this->db->select("id");
                $this->db->from("transaksi");
                $this->db->where("id_master", $masterID);
                $this->db->where("jenis_master", "110");
                $qTaxChilds = $this->db->get()->result();
                if (sizeof($qTaxChilds) > 0) {
                    foreach ($qTaxChilds as $tc) {
                        $taxChildIDs[] = $tc->id;
                    }
                }
            }

            $taxMastersFound = array();
            if (sizeof($taxChildIDs) > 0) {
                $this->db->select("t.id, t.id_master");
                $this->db->from("transaksi_data td");
                $this->db->join("transaksi t", "t.id = td.transaksi_id", "inner");
                $this->db->where_in("td.produk_id", $taxChildIDs);
                $this->db->where("t.jenis_master", "110");
                $this->db->where("t.id_master !=", $masterID);
                $this->db->where("t.trash", "0");
                $qDownTd = $this->db->get()->result();
                if (sizeof($qDownTd) > 0) {
                    foreach ($qDownTd as $dt) {
                        $tmid = (!empty($dt->id_master) && $dt->id_master > 0) ? $dt->id_master : $dt->id;
                        if (!in_array($tmid, $taxMastersFound)) {
                            $taxMastersFound[] = $tmid;
                        }
                    }
                }
            }

            // Cek juga via reference_id untuk faktur pajak di luar SO ini
            $this->db->select("id, id_master");
            $this->db->from("transaksi");
            $this->db->where("reference_id IN (SELECT id FROM transaksi WHERE id_master = '$masterID')", NULL, FALSE);
            $this->db->where("jenis_master", "110");
            $this->db->where("id_master !=", $masterID);
            $this->db->where("trash", "0");
            $qDownRef = $this->db->get()->result();
            if (sizeof($qDownRef) > 0) {
                foreach ($qDownRef as $dr) {
                    $tmid = (!empty($dr->id_master) && $dr->id_master > 0) ? $dr->id_master : $dr->id;
                    if (!in_array($tmid, $taxMastersFound)) {
                        $taxMastersFound[] = $tmid;
                    }
                }
            }

            // Daftarkan master faktur pajak dan cari seluruh SO rekanan yang ikut digabung ke dalamnya
            foreach ($taxMastersFound as $tmid) {
                if (!in_array($tmid, $masterIDs)) {
                    $masterIDs[] = $tmid;
                }
                $this->db->select("t2.id_master");
                $this->db->from("transaksi_data td");
                $this->db->join("transaksi t2", "t2.id = td.produk_id", "inner");
                $this->db->where("td.transaksi_id", $tmid);
                $this->db->where("t2.id_master >", 0);
                $qSibTd = $this->db->get()->result();
                if (sizeof($qSibTd) > 0) {
                    foreach ($qSibTd as $sib) {
                        if (!in_array($sib->id_master, $masterIDs)) {
                            $masterIDs[] = $sib->id_master;
                        }
                    }
                }
            }
        }
        $masterIDs = array_values(array_unique(array_filter($masterIDs)));

        $tmpSign = array();
        $seenSignIds = array();
        foreach ($masterIDs as $mID) {
            $trs = new MdlTransaksi();
            $trs->setFilters(array());
            $sRows = $trs->lookupSignaturesByMasterID($mID)->result();
            if (sizeof($sRows) > 0) {
                foreach ($sRows as $sRow) {
                    if (!isset($seenSignIds[$sRow->id])) {
                        $seenSignIds[$sRow->id] = true;
                        $tmpSign[] = $sRow;
                    }
                }
            }
        }
        //         cekLime($this->db->last_query());
        // $this->debugArrPrint($tmpSign);
        $arrCounters = blobDecode($tmp1[0]->counters);
        $urutCounters = NULL;
        if (sizeof($arrCounters) > 0) {
            $key2 = $tmp1[0]->jenis . "|" . $tmp1[0]->cabang_id;
            $urutCounters = digit_5($arrCounters["stepCode|placeID"][$key2]);
        }
        if ($urutCounters != NULL) {
            $urutCounters = "-$urutCounters";
        }

        if (isset($this->configUi[$tmp1[0]->jenis_master]['resumeFieldNames'])) {
            $resumeFields = $this->configUi[$tmp1[0]->jenis_master]['resumeFieldNames']['selectFields'];
            $resumeTitle = $this->configUi[$tmp1[0]->jenis_master]['resumeFieldNames']['title'];
            $resumeName = isset($tmp1[0]->$resumeFields) ? $tmp1[0]->$resumeFields : "";
        }
        else {
            $resumeTitle = "";
            $resumeName = "";
        }

        $arrSignName = array();
        if (sizeof($tmpSign) > 0) {
            foreach ($tmpSign as $ii => $row) {
                $signValues['sign_' . $row->id . '_' . $row->step_number] = array(
                    "title" => isset($this->configUi[$tmp1[0]->jenis_master]['steps'][$row->step_number]['label']) ? $this->configUi[$tmp1[0]->jenis_master]['steps'][$row->step_number]['label'] : "",
                    "label" => isset($this->configUi[$tmp1[0]->jenis_master]['steps'][$row->step_number]['stateCaption']) ? $this->configUi[$tmp1[0]->jenis_master]['steps'][$row->step_number]['stateCaption'] : "",
                    "dtime" => $row->dtime,

                    "nomer" => formatField_he_format("nomer_nolink", $row->nomer),
                    "nomer_orig" => $row->nomer,

                    "oleh" => isset($row->oleh_nama) ? $row->oleh_nama : "",
                    "caption_department" => isset($this->config->item("userGroup")[$row->group_code]) ? $this->config->item("userGroup")[$row->group_code] : "",
                    "prev_id" => isset($row->prev_id) ? $row->prev_id : 0,
                    "link_id" => isset($row->transaksi_id) ? $row->transaksi_id : 0,
                );
                $arrSignName[$ii] = array(
                    "nama" => $row->oleh_nama,
                    "dtime" => $row->dtime,
                );
            }
        }
        $signStr = "";
        //endregion

        //entry points
        $jenisTr_110r = array("110r_1");
        $jenisTr_spd = "5822spd";
        $jenisTr_spd_link = array("5822spd_4");
        $terimaAr = array();
        $inputFaktur = array();
        $cancelledIds = array();
        $cancelledNomers = array();
        $trOrigIDs = array();
        $rowStepNumber = array();
        $epDetail = array();

        $tmpEP = array();
        $seenEpIds = array();
        foreach ($masterIDs as $mID) {
            $trep = new MdlTransaksi();
            $epRows = $trep->lookupEntryPoints($mID)->result();
            if (sizeof($epRows) > 0) {
                foreach ($epRows as $epRow) {
                    if (!isset($seenEpIds[$epRow->id])) {
                        $seenEpIds[$epRow->id] = true;
                        $tmpEP[] = $epRow;
                    }
                }
            }
        }
        $epValues = array();
        if (sizeof($tmpEP) > 0) {
            foreach ($tmpEP as $ii => $row) {
                $trOrigIDs[] = $row->link_id;
                $jenisEx = explode("_", $row->jenis);
                $jenis = $jenisEx[0];
                $counterDecode = blobDecode($row->counters);
                $counterjenis = $jenis . "|" . $row->cabang_id;
                $counterjenismasterID = $jenis . "|" . $row->id_master;
                $counterGlobal = isset($counterDecode['stepCode|placeID'][$counterjenis]) ? $counterDecode['stepCode|placeID'][$counterjenis] : 0;
                $counterMasterIDGlobal = isset($counterDecode['stepCode|masterID'][$counterjenismasterID]) ? $counterDecode['stepCode|masterID'][$counterjenismasterID] : 0;
                $cGlobals = digit_5($counterGlobal);
                $cMasterIDGlobals = $counterMasterIDGlobal > 0 ? digit_5($counterMasterIDGlobal) : 0;

                if (empty($row->deskripsi)) {
                    if (!isset($rowStepNumber["referenceID__" . $row->step_number])) {
                        $rowStepNumber["referenceID__" . $row->step_number] = array(
                            "id"       => $row->id,
                            "link_id"  => $row->link_id,
                            "step_num" => $row->step_number,
                        );
                    }
                    $kkey = 'sign_' . $row->id . '_' . $row->step_number;
                    $rawNote = !empty($row->deskripsi) ? $row->deskripsi : (isset($row->keterangan) ? $row->keterangan : "");
                    $epValues[$kkey] = array(
                        "title"                    => isset($this->configUi[$row->jenis_master]['steps'][$row->step_number]['label']) ? $this->configUi[$row->jenis_master]['steps'][$row->step_number]['label'] : "",
                        "cabang_nama"              => $row->cabang_nama,
                        "label"                    => $row->jenis_label,
                        "dtime"                    => $row->dtime,
                        "nomer"                    => formatField_he_format("nomer_nolink", $row->nomer),
                        "nomer_orig"               => $row->nomer,
                        "oleh"                     => $row->oleh_nama,
                        "caption_department"       => "text",
                        "deskripsi"                => $rawNote,
                        "keterangan"               => isset($row->keterangan) ? $row->keterangan : "",
                        "link_id"                  => $row->link_id,
                        "global_counter"           => $cGlobals,
                        "master_id_global_counter" => $cMasterIDGlobals,
                        "jenis"                    => $jenis,
                        "kkey"                     => $kkey,
                    );
                    if ($row->efaktur != 0) {
                        $epValues[$kkey]["date_faktur"] = isset($row->efaktur_dtime) ? $row->efaktur_dtime : NULL;
                        $epValues[$kkey]["nomer_faktur"] = isset($row->efaktur) ? $row->efaktur : NULL;
                        $epValues[$kkey]["gunggungan_mode"] = isset($row->gunggungan_mode) ? $row->gunggungan_mode : 0;
                    }
                } else {
                    $tmpReg = $tr->lookupDataRegistriesByMasterID($row->link_id)->result();
                    $main = isset($tmpReg[0]->main) ? blobDecode($tmpReg[0]->main) : array();
                    if ($jenis == "5822spd") {
                        $main["referenceID__4"] = isset($main["referenceID"]) ? $main["referenceID"] : 0;
                    }
                    if (isset($row->nomer2) && $row->nomer2 != "") {
                        $cancelledNomers[] = $row->nomer2;
                        $nomerCleanEx = explode("-", $row->nomer2);
                        $cancelledNomers[] = $nomerCleanEx[0];
                    }
                    if (isset($main["referenceID"]) && $main["referenceID"] > 0) {
                        $cancelledIds[$main["referenceID"]] = $main["referenceID"];
                    }
                    if (isset($main["referenceID__4"]) && $main["referenceID__4"] > 0) {
                        $cancelledIds[$main["referenceID__4"]] = $main["referenceID__4"];
                    }
                    if (isset($main["transaksi"]) && $main["transaksi"] > 0) {
                        $cancelledIds[$main["transaksi"]] = $main["transaksi"];
                    }
                    if (isset($main["currentID"]) && $main["currentID"] > 0) {
                        $cancelledIds[$main["currentID"]] = $main["currentID"];
                    }
                    if (isset($row->link_id) && $row->link_id > 0) {
                        $cancelledIds[$row->link_id] = $row->link_id;
                    }

                    $kkey = 'sign_' . $row->id . '_' . $row->step_number . '_r';
                    $epValues[$kkey] = array(
                        "title"                    => isset($this->configUi[$row->jenis_master]['steps'][$row->step_number]['label']) ? "Pembatalan/Reject " . $this->configUi[$row->jenis_master]['steps'][$row->step_number]['label'] : "",
                        "cabang_nama"              => $row->cabang_nama,
                        "label"                    => $row->jenis_label,
                        "dtime"                    => $row->dtime,
                        "nomer"                    => formatField_he_format("nomer_nolink", $row->nomer),
                        "nomer_orig"               => $row->nomer,
                        "oleh"                     => $row->oleh_nama,
                        "caption_department"       => "text",
                        "deskripsi"                => $row->deskripsi,
                        "link_id"                  => $row->link_id,
                        "global_counter"           => $cGlobals,
                        "master_id_global_counter" => $cMasterIDGlobals,
                        "jenis"                    => $jenis,
                        "kkey"                     => $kkey,
                    );
                }

                if (empty($row->deskripsi)) {
                    if (in_array($row->jenis, $jenisTr_spd_link)) {
                        $terimaAr[$row->link_id] = $row->link_id;
                    }
                    if (in_array($row->jenis, $jenisTr_110r)) {
                        $inputFaktur[$row->link_id] = $row->link_id;
                    }
                }
            }
        }

        if (sizeof($cancelledNomers) > 0) {
            $this->db->select("id");
            $this->db->from("transaksi");
            $this->db->group_start();
            $this->db->where_in("nomer", $cancelledNomers);
            $this->db->or_where_in("nomer2", $cancelledNomers);
            $this->db->group_end();
            $qCancel = $this->db->get()->result();
            if (sizeof($qCancel) > 0) {
                foreach ($qCancel as $qc) {
                    $cancelledIds[$qc->id] = $qc->id;
                }
            }
        }

        if (sizeof($cancelledIds) > 0) {
            foreach ($cancelledIds as $cId) {
                if (isset($terimaAr[$cId])) {
                    unset($terimaAr[$cId]);
                }
                if (isset($inputFaktur[$cId])) {
                    unset($inputFaktur[$cId]);
                }
            }
        }

        //------LINK KE AR RECEIPT (749)
        $totalArBayar = 0;
        if (sizeof($terimaAr) > 0) {
            $trep = new MdlTransaksi();
            $trep->addFilter("transaksi.jenis='749'");
            $trep->addFilter("transaksi_data.produk_id in ('" . implode("','", $terimaAr) . "')");
            $trepTmp = $trep->lookupJoined_OLD()->result();
            foreach ($trepTmp as $row) {
                $subBayar = isset($row->produk_ord_hrg) ? (float)$row->produk_ord_hrg * (isset($row->produk_ord_jml) && (float)$row->produk_ord_jml > 0 ? (float)$row->produk_ord_jml : 1) : 0;
                $totalArBayar += $subBayar;
                $jenis = $row->jenis;
                $counterDecode = blobDecode($row->counters);
                $counterjenis = $jenis . "|" . $row->cabang_id;
                $counterjenismasterID = $jenis . "|" . $row->id_master;
                $counterGlobal = isset($counterDecode['stepCode|placeID'][$counterjenis]) ? $counterDecode['stepCode|placeID'][$counterjenis] : 0;
                $counterMasterIDGlobal = isset($counterDecode['stepCode|masterID'][$counterjenismasterID]) ? $counterDecode['stepCode|masterID'][$counterjenismasterID] : 0;
                $cGlobals = digit_5($counterGlobal);
                $cMasterIDGlobals = $counterMasterIDGlobal > 0 ? digit_5($counterMasterIDGlobal) : 0;
                $kkey = 'sign_' . $row->transaksi_id . '_' . $row->step_number;
                $epValues[$kkey] = array(
                    "title"                    => isset(loadConfigModulJenis_he_misc($row->jenis_master, "coTransaksiUi")['steps'][$row->step_number]['label']) ? loadConfigModulJenis_he_misc($row->jenis_master, "coTransaksiUi")['steps'][$row->step_number]['label'] : "",
                    "cabang_nama"              => $row->cabang_nama,
                    "label"                    => $row->jenis_label,
                    "dtime"                    => $row->dtime,
                    "nomer"                    => formatField_he_format("nomer_nolink", $row->nomer),
                    "nomer_orig"               => $row->nomer,
                    "oleh"                     => $row->oleh_nama,
                    "caption_department"       => "text",
                    "deskripsi"                => $row->deskripsi,
                    "link_id"                  => $row->link_id,
                    "global_counter"           => $cGlobals,
                    "master_id_global_counter" => $cMasterIDGlobals,
                    "jenis"                    => $jenis,
                    "kkey"                     => $kkey,
                );
            }
        }

        //------LINK KE E-FAKTUR (110)
        if (sizeof($inputFaktur) > 0) {
            $trep = new MdlTransaksi();
            $trep->addFilter("transaksi.jenis_master='110'");
            $trep->addFilter("transaksi_data.produk_id in ('" . implode("','", $inputFaktur) . "')");
            $trepTmp = $trep->lookupJoined_OLD()->result();
            foreach ($trepTmp as $row) {
                $jenis = $row->jenis;
                $cleanRowNomer = explode('_', $row->nomer);
                $cleanRowNomerNota = $cleanRowNomer[0];

                // Cek apakah dokumen ini sudah ada di epValues (misal sudah ditarik via lookupEntryPoints)
                $isAlreadyExists = false;
                foreach ($epValues as $exKey => $exItem) {
                    $exClean = explode('_', $exItem['nomer_orig']);
                    $exCleanNota = $exClean[0];
                    if ($exCleanNota == $cleanRowNomerNota && $exItem['jenis'] == $jenis) {
                        $isAlreadyExists = true;
                        if (empty($epValues[$exKey]['nomer_faktur']) && !empty($row->efaktur)) {
                            $epValues[$exKey]['nomer_faktur'] = $row->efaktur;
                        }
                        if (empty($epValues[$exKey]['date_faktur']) && !empty($row->efaktur_dtime)) {
                            $epValues[$exKey]['date_faktur'] = $row->efaktur_dtime;
                        }
                        break;
                    }
                    if (isset($exItem['link_id']) && $exItem['link_id'] == $row->transaksi_id) {
                        $isAlreadyExists = true;
                        if (empty($epValues[$exKey]['nomer_faktur']) && !empty($row->efaktur)) {
                            $epValues[$exKey]['nomer_faktur'] = $row->efaktur;
                        }
                        if (empty($epValues[$exKey]['date_faktur']) && !empty($row->efaktur_dtime)) {
                            $epValues[$exKey]['date_faktur'] = $row->efaktur_dtime;
                        }
                        break;
                    }
                }
                if ($isAlreadyExists) {
                    continue;
                }
                $counterDecode = blobDecode($row->counters);
                $counterjenis = $jenis . "|" . $row->cabang_id;
                $counterjenismasterID = $jenis . "|" . $row->id_master;
                $counterGlobal = isset($counterDecode['stepCode|placeID'][$counterjenis]) ? $counterDecode['stepCode|placeID'][$counterjenis] : 0;
                $counterMasterIDGlobal = isset($counterDecode['stepCode|masterID'][$counterjenismasterID]) ? $counterDecode['stepCode|masterID'][$counterjenismasterID] : 0;
                $cGlobals = digit_5($counterGlobal);
                $cMasterIDGlobals = $counterMasterIDGlobal > 0 ? digit_5($counterMasterIDGlobal) : 0;
                $kkey = 'sign_' . $row->transaksi_id . '_' . $row->step_number;
                $epValues[$kkey] = array(
                    "title"                    => isset(loadConfigModulJenis_he_misc($row->jenis_master, "coTransaksiUi")['steps'][$row->step_number]['label']) ? loadConfigModulJenis_he_misc($row->jenis_master, "coTransaksiUi")['steps'][$row->step_number]['label'] : "",
                    "cabang_nama"              => $row->cabang_nama,
                    "label"                    => $row->jenis_label,
                    "dtime"                    => $row->dtime,
                    "nomer"                    => formatField_he_format("nomer_nolink", $row->nomer),
                    "nomer_orig"               => $row->nomer,
                    "oleh"                     => $row->oleh_nama,
                    "caption_department"       => "text",
                    "deskripsi"                => $row->deskripsi,
                    "link_id"                  => $row->link_id,
                    "global_counter"           => $cGlobals,
                    "master_id_global_counter" => $cMasterIDGlobals,
                    "jenis"                    => $jenis,
                    "date_faktur"              => $row->efaktur_dtime,
                    "nomer_faktur"             => $row->efaktur,
                    "gunggungan_mode"          => $row->gunggungan_mode,
                    "kkey"                     => $kkey,
                );
                if ($row->trash_4 == 1) {
                    $kkey = 'sign_' . $row->transaksi_id . '_' . $row->step_number . '_r';
                    $epValues[$kkey] = array(
                        "title"              => isset(loadConfigModulJenis_he_misc($row->jenis_master, "coTransaksiUi")['steps'][$row->step_number]['label']) ? "Pembatalan/Reject " . loadConfigModulJenis_he_misc($row->jenis_master, "coTransaksiUi")['steps'][$row->step_number]['label'] : "",
                        "cabang_nama"        => $row->cabang_nama,
                        "label"              => "Pembatalan/Reject " . $row->jenis_label,
                        "dtime"              => $row->cancel_dtime,
                        "nomer"              => formatField_he_format("nomer_nolink", $row->nomer),
                        "nomer_reject"       => formatField_he_format("nomer_nolink", isset($row->cancel_transaksi_nomer) ? $row->cancel_transaksi_nomer : NULL),
                        "nomer_orig"         => $row->nomer,
                        "oleh"               => $row->cancel_name,
                        "caption_department" => "text",
                        "deskripsi"          => $row->deskripsi,
                        "link_id"            => $row->link_id,
                        "jenis"              => $jenis,
                        "date_faktur"        => $row->efaktur_dtime,
                        "nomer_faktur"       => $row->efaktur,
                        "gunggungan_mode"    => $row->gunggungan_mode,
                        "kkey"               => $kkey,
                    );
                }
            }
        }

        //------LINK KE KASIR PENJUALAN TUNAI (4464)
        $totalCashBayar = 0;
        $searchIDs = $trOrigIDs;
        if ($transaksiID > 0 && !in_array($transaksiID, $searchIDs)) {
            $searchIDs[] = $transaksiID;
        }
        if ($transaksiTopID > 0 && !in_array($transaksiTopID, $searchIDs)) {
            $searchIDs[] = $transaksiTopID;
        }

        if (count($searchIDs) > 0) {
            $this->db->select("t.id, t.nomer, t.dtime, t.oleh_nama, t.cabang_nama, t.jenis, t.jenis_label, t.counters, t.deskripsi, t.jenis_master, td.produk_ord_hrg, td.produk_ord_jml");
            $this->db->from("transaksi t");
            $this->db->join("transaksi_data td", "td.transaksi_id = t.id", "inner");
            $this->db->group_start();
            $this->db->where_in("td.produk_id", $searchIDs);
            $this->db->or_where_in("t.id_top", $searchIDs);
            $this->db->or_where_in("t.id_master", $searchIDs);
            $this->db->group_end();
            $this->db->group_start();
            $this->db->where("t.jenis", "4464");
            $this->db->or_like("t.jenis", "4464_");
            $this->db->or_where("t.jenis_master", "4464");
            $this->db->group_end();
            $this->db->where("t.trash", "0");
            $this->db->where("t.link_id", "0");
            $this->db->group_by("t.id");

            $queryPayment = $this->db->get();
            if ($queryPayment && $queryPayment->num_rows() > 0) {
                foreach ($queryPayment->result() as $pRow) {
                    $cPay = isset($pRow->produk_ord_hrg) ? (float)$pRow->produk_ord_hrg * (isset($pRow->produk_ord_jml) && (float)$pRow->produk_ord_jml > 0 ? (float)$pRow->produk_ord_jml : 1) : 0;
                    $totalCashBayar += $cPay;
                    $kkeyPay = 'sign_pay_' . $pRow->id;
                    $counterDecode = blobDecode($pRow->counters);
                    $cGlobals = "";
                    if (is_array($counterDecode) && isset($counterDecode['stepCode|placeID'])) {
                        $firstCounter = reset($counterDecode['stepCode|placeID']);
                        $cGlobals = digit_5($firstCounter);
                    }
                    $epValues[$kkeyPay] = array(
                        "title"                    => "Penerimaan Penjualan Tunai",
                        "cabang_nama"              => $pRow->cabang_nama,
                        "label"                    => "Penerimaan Penjualan Tunai",
                        "dtime"                    => $pRow->dtime,
                        "nomer"                    => formatField_he_format("nomer_nolink", $pRow->nomer),
                        "nomer_orig"               => $pRow->nomer,
                        "oleh"                     => $pRow->oleh_nama,
                        "caption_department"       => "Finance / Kasir",
                        "deskripsi"                => $pRow->deskripsi,
                        "link_id"                  => $pRow->id,
                        "global_counter"           => $cGlobals,
                        "master_id_global_counter" => $cGlobals,
                        "jenis"                    => "4464",
                        "kkey"                     => $kkeyPay,
                    );
                }
            }
        }

        //------LINK KE PENERIMAAN UANG MUKA KONSUMEN (4467)
        $totalUmBayar = 0;
        $searchNomers = array();
        foreach ($epValues as $epItem) {
            if (!empty($epItem['nomer_orig'])) {
                $searchNomers[] = $epItem['nomer_orig'];
                $cleanEx = explode('_', $epItem['nomer_orig']);
                if (!empty($cleanEx[0])) {
                    $searchNomers[] = $cleanEx[0];
                }
            }
            if (!empty($epItem['nomer'])) {
                $searchNomers[] = $epItem['nomer'];
            }
        }
        if (!empty($no)) {
            $searchNomers[] = $no;
            $cleanEx = explode('_', $no);
            if (!empty($cleanEx[0])) {
                $searchNomers[] = $cleanEx[0];
            }
        }
        $searchNomers = array_unique(array_filter($searchNomers));

        $cleanSearchIDs = array();
        if (isset($searchIDs) && is_array($searchIDs)) {
            foreach ($searchIDs as $sIdVal) {
                $sIdInt = intval($sIdVal);
                if ($sIdInt > 0 && !in_array($sIdInt, $cleanSearchIDs)) {
                    $cleanSearchIDs[] = $sIdInt;
                }
            }
        }

        if (count($cleanSearchIDs) > 0 || count($searchNomers) > 0) {
            $this->db->select("t.id, t.nomer, t.nomer2, t.dtime, t.oleh_nama, t.cabang_nama, t.jenis, t.jenis_label, t.counters, t.deskripsi, t.keterangan, t.transaksi_nilai, t.reference_nomer, t.reference_id, t.trash, t.trash_4");
            $this->db->from("transaksi t");
            $this->db->group_start();
            if (count($searchNomers) > 0) {
                $this->db->where_in("t.reference_nomer", $searchNomers);
            }
            if (count($cleanSearchIDs) > 0) {
                $cleanIdList = implode(",", $cleanSearchIDs);
                $this->db->or_where_in("t.reference_id", $cleanSearchIDs);
                $this->db->or_where("t.id IN (SELECT transaksi_id FROM transaksi_payment_source WHERE extern2_id IN ($cleanIdList) AND jenis='4467')", NULL, FALSE);
                $this->db->or_where("t.nomer IN (SELECT nomer FROM transaksi_payment_source WHERE extern2_id IN ($cleanIdList) AND jenis='4467')", NULL, FALSE);
            }
            $this->db->group_end();
            $this->db->group_start();
            $this->db->where("t.jenis", "4467");
            $this->db->or_like("t.jenis", "4467_");
            $this->db->or_where("t.jenis_master", "4467");
            $this->db->group_end();
            $this->db->where("t.trash", "0");
            $this->db->where("t.link_id", "0");
            $this->db->group_by("t.id");

            $umMasterIDs = array();
            $queryUm = $this->db->get();
            if ($queryUm && $queryUm->num_rows() > 0) {
                foreach ($queryUm->result() as $uRow) {
                    $umMasterIDs[] = $uRow->id;
                    $uNilai = isset($uRow->transaksi_nilai) ? (float)$uRow->transaksi_nilai : 0;
                    if ($uRow->trash == 0 && $uRow->trash_4 == 0) {
                        $totalUmBayar += $uNilai;
                    }
                    $kkeyUm = 'sign_um_' . $uRow->id;
                    $counterDecode = blobDecode($uRow->counters);
                    $cGlobals = "";
                    if (is_array($counterDecode) && isset($counterDecode['stepCode|placeID'])) {
                        $firstCounter = reset($counterDecode['stepCode|placeID']);
                        $cGlobals = digit_5($firstCounter);
                    }
                    $umStatus = "";
                    if ($uRow->trash != 0 || $uRow->trash_4 != 0) {
                        $umStatus = "BATAL / VOID";
                    }
                    $umDeskripsi = "Penerimaan Uang Muka Rp " . number_format($uNilai, 0, ',', '.');
                    if (!empty($uRow->keterangan)) {
                        $umDeskripsi .= "<br><small class='text-muted'>" . htmlspecialchars($uRow->keterangan, ENT_QUOTES) . "</small>";
                    }
                    $epValues[$kkeyUm] = array(
                        "title"                    => "UANG MUKA KONSUMEN",
                        "cabang_nama"              => $uRow->cabang_nama,
                        "label"                    => "UANG MUKA KONSUMEN",
                        "dtime"                    => $uRow->dtime,
                        "nomer"                    => formatField_he_format("nomer_nolink", $uRow->nomer),
                        "nomer_orig"               => $uRow->nomer,
                        "oleh"                     => $uRow->oleh_nama,
                        "caption_department"       => "Finance / Kasir",
                        "deskripsi"                => $umDeskripsi,
                        "keterangan"               => isset($uRow->keterangan) ? $uRow->keterangan : "",
                        "link_id"                  => $uRow->id,
                        "global_counter"           => $cGlobals,
                        "master_id_global_counter" => $cGlobals,
                        "jenis"                    => "4467",
                        "status"                   => $umStatus,
                        "kkey"                     => $kkeyUm,
                    );
                }
            }

            //------LINK KE FAKTUR PAJAK UANG MUKA (110r & 110rrj)
            if (count($umMasterIDs) > 0) {
                $this->db->select("t.id, t.nomer, t.nomer2, t.dtime, t.oleh_nama, t.cabang_nama, t.cabang2_nama, t.jenis, t.jenis_label, t.counters, t.deskripsi, t.trash, t.trash_4, t.efaktur, t.efaktur_dtime, t.gunggungan_mode, t.cancel_name, t.cancel_dtime, t.cancel_transaksi_nomer, t.id_master, t.jenis_master, t.step_number");
                $this->db->from("transaksi t");
                $this->db->where_in("t.id_master", $umMasterIDs);
                $this->db->group_start();
                $this->db->where_in("t.jenis", array("110r", "110r_1", "110rrj", "110rrj_1"));
                $this->db->or_where_in("t.jenis_master", array("110r", "110rrj", "110"));
                $this->db->group_end();
                $this->db->where("t.trash", "0");
                $this->db->where("t.link_id", "0");
                $this->db->group_by("t.id");
                $this->db->order_by("t.dtime", "ASC");

                $queryUmEf = $this->db->get();
                if ($queryUmEf && $queryUmEf->num_rows() > 0) {
                    $umEfRows = $queryUmEf->result();
                    $umEfRejectDocs = array();
                    foreach ($umEfRows as $rEfRow) {
                        if ($rEfRow->jenis == "110rrj" || $rEfRow->jenis == "110rrj_1") {
                            $umEfRejectDocs[$rEfRow->id_master][] = $rEfRow;
                        }
                    }

                    foreach ($umEfRows as $rEfRow) {
                        if ($rEfRow->jenis == "110rrj" || $rEfRow->jenis == "110rrj_1") {
                            continue;
                        }

                        $counterDecode = blobDecode($rEfRow->counters);
                        $cGlobals = "";
                        if (is_array($counterDecode) && isset($counterDecode['stepCode|placeID'])) {
                            $firstCounter = reset($counterDecode['stepCode|placeID']);
                            $cGlobals = digit_5($firstCounter);
                        }

                        $kkeyEf = 'sign_um_efaktur_' . $rEfRow->id;
                        $efFakturNo = !empty($rEfRow->efaktur) ? $rEfRow->efaktur : (!empty($rEfRow->nomer2) ? $rEfRow->nomer2 : "");
                        $efDate = ($rEfRow->efaktur_dtime != '0000-00-00' && !empty($rEfRow->efaktur_dtime)) ? $rEfRow->efaktur_dtime : "";

                        $epValues[$kkeyEf] = array(
                            "title"                    => "PREPARE E-FAKTUR (Uang Muka)",
                            "cabang_nama"              => !empty($rEfRow->cabang_nama) ? $rEfRow->cabang_nama : (!empty($rEfRow->cabang2_nama) ? $rEfRow->cabang2_nama : "-"),
                            "label"                    => "PREPARE E-FAKTUR (Uang Muka)",
                            "dtime"                    => $rEfRow->dtime,
                            "nomer"                    => formatField_he_format("nomer_nolink", $rEfRow->nomer),
                            "nomer_orig"               => $rEfRow->nomer,
                            "nomer_faktur"             => $efFakturNo,
                            "date_faktur"              => $efDate,
                            "oleh"                     => $rEfRow->oleh_nama,
                            "caption_department"       => "Pajak / Finance",
                            "deskripsi"                => $rEfRow->deskripsi,
                            "link_id"                  => $rEfRow->id,
                            "global_counter"           => $cGlobals,
                            "master_id_global_counter" => $cGlobals,
                            "jenis"                    => $rEfRow->jenis,
                            "gunggungan_mode"          => $rEfRow->gunggungan_mode,
                            "kkey"                     => $kkeyEf,
                        );

                        if ($rEfRow->trash_4 == 1 || !empty($rEfRow->cancel_dtime) || isset($umEfRejectDocs[$rEfRow->id_master])) {
                            $cancelDtime = (!empty($rEfRow->cancel_dtime) && $rEfRow->cancel_dtime != '0000-00-00 00:00:00') ? $rEfRow->cancel_dtime : $rEfRow->dtime;
                            $cancelName = !empty($rEfRow->cancel_name) ? $rEfRow->cancel_name : $rEfRow->oleh_nama;
                            $cancelNomer = "";

                            if (isset($umEfRejectDocs[$rEfRow->id_master])) {
                                $rjItem = $umEfRejectDocs[$rEfRow->id_master][0];
                                $cancelNomer = $rjItem->nomer;
                                if (!empty($rjItem->cancel_dtime) && $rjItem->cancel_dtime != '0000-00-00 00:00:00') {
                                    $cancelDtime = $rjItem->cancel_dtime;
                                } elseif (!empty($rjItem->dtime)) {
                                    $cancelDtime = $rjItem->dtime;
                                }
                                if (!empty($rjItem->oleh_nama)) {
                                    $cancelName = $rjItem->oleh_nama;
                                }
                            }

                            $kkeyEfR = 'sign_um_efaktur_' . $rEfRow->id . '_r';
                            $epValues[$kkeyEfR] = array(
                                "title"              => "Pembatalan/Reject PREPARE E-FAKTUR (Uang Muka)",
                                "cabang_nama"        => !empty($rEfRow->cabang_nama) ? $rEfRow->cabang_nama : (!empty($rEfRow->cabang2_nama) ? $rEfRow->cabang2_nama : "-"),
                                "label"              => "Pembatalan/Reject PREPARE E-FAKTUR (Uang Muka)",
                                "dtime"              => $cancelDtime,
                                "nomer"              => formatField_he_format("nomer_nolink", $rEfRow->nomer),
                                "nomer_reject"       => formatField_he_format("nomer_nolink", $cancelNomer),
                                "nomer_orig"         => $rEfRow->nomer,
                                "oleh"               => $cancelName,
                                "caption_department" => "Pajak / Finance",
                                "deskripsi"          => "Rejection Faktur Uang Muka",
                                "link_id"            => $rEfRow->id,
                                "jenis"              => "110rrj",
                                "kkey"               => $kkeyEfR,
                            );
                        }
                    }
                }
            }
        }

        $signArray = sizeof($epValues) > 0 ? $epValues : $signValues;

        if (sizeof($signArray) > 0) {
            $hasCashPayment = false;
            $hasArPayment = false;
            $hasUmPayment = false;
            $hasInvoice = false;
            $hasPackingList = false;

            foreach ($signArray as $sItem) {
                $itemJenis = isset($sItem['jenis']) ? $sItem['jenis'] : "";
                $itemNomer = isset($sItem['nomer_orig']) ? $sItem['nomer_orig'] : (isset($sItem['nomer']) ? $sItem['nomer'] : "");

                if ($itemJenis == '4464' || strpos($itemJenis, '4464') === 0) {
                    $hasCashPayment = true;
                }
                if ($itemJenis == '4467' || strpos($itemJenis, '4467') === 0) {
                    $hasUmPayment = true;
                }
                if ($itemJenis == '749') {
                    $hasArPayment = true;
                }

                $isItemRejected = false;
                if (isset($sItem['status']) && stripos($sItem['status'], 'reject') !== false) {
                    $isItemRejected = true;
                }
                if (isset($sItem['deskripsi']) && stripos($sItem['deskripsi'], 'pembatalan') !== false) {
                    $isItemRejected = true;
                }

                if (!$isItemRejected) {
                    if (strpos($itemNomer, 'INV.') !== false || (isset($sItem['title']) && stripos($sItem['title'], 'INVOICE') !== false)) {
                        $hasInvoice = true;
                    }
                    if (strpos($itemJenis, 'spd') !== false || (isset($sItem['title']) && stripos($sItem['title'], 'PACKING LIST') !== false)) {
                        $hasPackingList = true;
                    }
                }
            }

            // SOT Status Pembayaran via he_finansial_helper
            $this->load->helper("he_finansial");
            foreach ($masterIDs as $mID) {
                if ($mID > 0 && !in_array($mID, $searchIDs)) {
                    $searchIDs[] = $mID;
                }
            }
            $payStatus = fin_get_payment_status($searchIDs, 1000.0, true);

            if ($payStatus['has_record'] && $payStatus['tagihan'] > 0) {
                if ($payStatus['is_lunas']) {
                    if ($hasUmPayment && $hasArPayment) {
                        $paymentBadge = "<span class='label label-success' style='font-size:12px; margin-left:10px;'><i class='fa fa-check-circle'></i> LUNAS (Uang Muka + A/R Receipt)</span>";
                    } elseif ($hasUmPayment && $hasCashPayment) {
                        $paymentBadge = "<span class='label label-success' style='font-size:12px; margin-left:10px;'><i class='fa fa-check-circle'></i> LUNAS (Uang Muka + Tunai Kasir)</span>";
                    } elseif ($hasUmPayment) {
                        $paymentBadge = "<span class='label label-success' style='font-size:12px; margin-left:10px;'><i class='fa fa-check-circle'></i> LUNAS (Uang Muka)</span>";
                    } elseif (isset($payStatus['has_um']) && $payStatus['has_um']) {
                        $paymentBadge = "<span class='label label-success' style='font-size:12px; margin-left:10px;'><i class='fa fa-check-circle'></i> LUNAS (Uang Muka / DP)</span>";
                    } elseif ($hasCashPayment) {
                        $paymentBadge = "<span class='label label-success' style='font-size:12px; margin-left:10px;'><i class='fa fa-check-circle'></i> LUNAS (Tunai Kasir)</span>";
                    } elseif ($hasArPayment) {
                        $paymentBadge = "<span class='label label-success' style='font-size:12px; margin-left:10px;'><i class='fa fa-check-circle'></i> LUNAS (Melalui A/R Receipt)</span>";
                    } else {
                        $paymentBadge = "<span class='label label-success' style='font-size:12px; margin-left:10px;'><i class='fa fa-check-circle'></i> LUNAS</span>";
                    }
                } elseif ($payStatus['is_cicilan']) {
                    $terbayarFmt = number_format($payStatus['terbayar'], 0, ',', '.');
                    $sisaFmt = number_format($payStatus['sisa'], 0, ',', '.');
                    if (isset($payStatus['has_um']) && $payStatus['has_um']) {
                        $paymentBadge = "<span class='label label-warning' style='font-size:12px; margin-left:10px;'><i class='fa fa-clock-o'></i> CICILAN (UM Rp $terbayarFmt / Sisa Rp $sisaFmt)</span>";
                    } else {
                        $paymentBadge = "<span class='label label-warning' style='font-size:12px; margin-left:10px;'><i class='fa fa-clock-o'></i> CICILAN (Terbayar Rp $terbayarFmt / Sisa Rp $sisaFmt)</span>";
                    }
                } else {
                    if ($hasInvoice || $hasPackingList) {
                        $tagihanFmt = number_format($payStatus['tagihan'], 0, ',', '.');
                        $paymentBadge = "<span class='label label-danger' style='font-size:12px; margin-left:10px;'><i class='fa fa-exclamation-circle'></i> BELUM LUNAS (Rp $tagihanFmt)</span>";
                    } else {
                        $paymentBadge = "<span class='label label-default' style='font-size:12px; margin-left:10px;'><i class='fa fa-hourglass-start'></i> MENUNGGU INVOICE</span>";
                    }
                }
            } else {
                if ($hasInvoice || $hasPackingList) {
                    $paymentBadge = "<span class='label label-danger' style='font-size:12px; margin-left:10px;'><i class='fa fa-exclamation-circle'></i> BELUM LUNAS</span>";
                } else {
                    $paymentBadge = "<span class='label label-default' style='font-size:12px; margin-left:10px;'><i class='fa fa-hourglass-start'></i> MENUNGGU INVOICE</span>";
                }
            }

            // Sanitasi akhir: pastikan tidak ada baris duplikat di signArray
            $signArrayClean = array();
            $seenCleanKeys = array();
            foreach ($signArray as $kKey => $itemSpec) {
                $itemNomerOrig = isset($itemSpec['nomer_orig']) ? $itemSpec['nomer_orig'] : (isset($itemSpec['nomer']) ? $itemSpec['nomer'] : "");
                $itemNomerEx = explode('_', $itemNomerOrig);
                $itemNomerBase = $itemNomerEx[0];
                $itemJenis = isset($itemSpec['jenis']) ? $itemSpec['jenis'] : "";
                $isRej = (isset($itemSpec['status']) && stripos($itemSpec['status'], 'reject') !== false) || (isset($itemSpec['deskripsi']) && stripos($itemSpec['deskripsi'], 'pembatalan') !== false);
                $dedupKey = $itemNomerBase . '|' . $itemJenis . '|' . ($isRej ? '1' : '0');

                if (isset($seenCleanKeys[$dedupKey])) {
                    $prevKey = $seenCleanKeys[$dedupKey];
                    if (empty($signArrayClean[$prevKey]['nomer_faktur']) && !empty($itemSpec['nomer_faktur'])) {
                        $signArrayClean[$prevKey]['nomer_faktur'] = $itemSpec['nomer_faktur'];
                    }
                    if (empty($signArrayClean[$prevKey]['date_faktur']) && !empty($itemSpec['date_faktur'])) {
                        $signArrayClean[$prevKey]['date_faktur'] = $itemSpec['date_faktur'];
                    }
                    continue;
                }

                $seenCleanKeys[$dedupKey] = $kKey;
                $signArrayClean[$kKey] = $itemSpec;
            }
            $signArray = $signArrayClean;

            $signArraySorted = array();
            foreach ($signArray as $kKey => $itemSpec) {
                $sortKey = $itemSpec['dtime'] . '_' . $kKey;
                $signArraySorted[$sortKey] = $itemSpec;
            }
            ksort($signArraySorted);

            $signStr .= "<div class='panel table-responsive'>";
            $signStr .= "<h4 class='text-blue'><span class='fa fa-sign-in'></span> History Entry Points </h4>";
            $signStr .= "<h5 class='text-blue'><span class='fa fa-tasks text-uppercase'></span> $resumeTitle  $resumeName $paymentBadge</h5>";
            $signStr .= "<table class='table table-condensed table-striped table-hover-color-red dataTable'>";
            $signStr .= "<tr bgcolor='#f0f0f0'>";
            $signStr .= "<td class='text-muted' style='width:35px; text-align:center;'>no.</td>";
            $signStr .= "<td class='text-muted'>date</td>";
            $signStr .= "<td class='text-muted'>title</td>";
            $signStr .= "<td class='text-muted'>branch</td>";
            $signStr .= "<td class='text-muted'>transaction number</td>";
            $signStr .= "<td class='text-muted'>person</td>";
            $signStr .= "<td class='text-muted'>notes</td>";
            $signStr .= "<td class='text-muted'>tool</td>";
            $signStr .= "</tr>";
            $nom = 0;
            foreach ($signArraySorted as $k => $sSpec) {
                $nom++;
                $jenis = $sSpec['jenis'];
                $nomerOrig = $sSpec['nomer_orig'];
                $nomerOrigEx = explode("_", $nomerOrig);
                $nomerOrigNota = $nomerOrigEx[0];
                $nomerView = $nomerOrigNota . "-" . $sSpec['global_counter'];
                $nomerViewEdit = $nomerOrigNota . "-" . $sSpec['master_id_global_counter'];
                $nomerViewFinal = $jenis == "582spoe" ? $nomerViewEdit : $nomerView;

                $oleh_nama = isset($arrSignName[$k]) ? $arrSignName[$k] : $sSpec['oleh'];

                $edit_description = "";
                if (isset($arrTopEdit[$sSpec['link_id']])) {
                    $edit_dtime = formatField_he_format("date_time", $arrTopEdit[$sSpec['link_id']]['edit_dtime']);
                    $edit_nama = $arrTopEdit[$sSpec['link_id']]['edit_name'];
                    $edit_description = "<span style='color:red;'>Edited by $edit_nama, $edit_dtime</span>";
                }

                $signStr .= "<tr>";
                $signStr .= "<td align='center'>" . $nom . "</td>";
                $signStr .= "<td>" . formatField_he_format("date_time", $sSpec['dtime']) . "</td>";
                $signStr .= "<td>" . ($sSpec['label']) . "</td>";
                $signStr .= "<td>" . (isset($sSpec['cabang_nama']) ? $sSpec['cabang_nama'] : "-") . "</td>";
                
                $signStr .= "<td>" . (formatField_he_format("nomer_nolink", $nomerViewFinal));
                if (!empty($sSpec['date_faktur'])) {
                    $signStr .= "<br><small class='text-muted'>Tgl Faktur: " . $sSpec['date_faktur'] . "</small>";
                }
                if (!empty($sSpec['nomer_faktur'])) {
                    $signStr .= "<br><small class='text-muted'>No. Faktur: " . $sSpec['nomer_faktur'] . "</small>";
                }
                if (!empty($sSpec['nomer_reject'])) {
                    $signStr .= "<br><small class='text-danger'>No. Reject: " . $sSpec['nomer_reject'] . "</small>";
                }
                $signStr .= "</td>";

                $signStr .= "<td><span class='fa fa-user'></span> " . $oleh_nama . "</td>";

                $notesList = array();
                $rawNote = !empty($sSpec['deskripsi']) ? $sSpec['deskripsi'] : (!empty($sSpec['keterangan']) ? $sSpec['keterangan'] : "");
                if (!empty($rawNote)) {
                    $notesList[] = $rawNote;
                }
                if (!empty($edit_description)) {
                    $notesList[] = $edit_description;
                }
                $signStr .= "<td>" . (count($notesList) > 0 ? implode("<br>", $notesList) : "-") . "</td>";

                $isRejectedRow = false;
                if (strpos($jenis, 'rj') !== false || strpos($jenis, 'cancel') !== false) {
                    $isRejectedRow = true;
                }
                if (!empty($sSpec['deskripsi']) && (stripos($sSpec['deskripsi'], 'reject') !== false || stripos($sSpec['deskripsi'], 'pembatalan') !== false)) {
                    $isRejectedRow = true;
                }
                if (!empty($sSpec['title']) && (stripos($sSpec['title'], 'reject') !== false || stripos($sSpec['title'], 'pembatalan') !== false)) {
                    $isRejectedRow = true;
                }

                $signStr .= "<td align='center'>";
                if ($isRejectedRow) {
                    $signStr .= "-";
                } else {
                    $targetModul = "taxes";
                    $targetController = "Printing";
                    $targetMethod = "viewReceiptReg";
                    $targetJenisTr = "110";

                    if (strpos($nomerOrigNota, '582') === 0 || strpos($jenis, '582') === 0 || strpos($jenis, '1582') === 0) {
                        $targetModul = "penjualan";
                        $targetJenisTr = "5822";
                        $targetMethod = "viewReceiptReg";
                    } elseif (strpos($nomerOrigNota, 'INV.') === 0 || strpos($jenis, '4822') === 0) {
                        $targetModul = "invoicing";
                        $targetJenisTr = "4822";
                        $targetMethod = "viewReceiptReg";
                    } elseif (strpos($jenis, '4467') === 0 || strpos($nomerOrigNota, '4467') === 0) {
                        $targetModul = "kas";
                        $targetJenisTr = "4467";
                        $targetMethod = "viewReceiptReg";
                    } elseif (strpos($jenis, '4464') === 0 || strpos($nomerOrigNota, '4464') === 0) {
                        $targetModul = "kas";
                        $targetJenisTr = "4464";
                        $targetMethod = "viewReceiptCashIn";
                    } elseif (strpos($jenis, '749') === 0 || strpos($nomerOrigNota, '749') === 0) {
                        $targetModul = "penerimaan";
                        $targetJenisTr = "749";
                        $targetMethod = "viewReceiptReg";
                    } elseif (strpos($jenis, '110') === 0 || strpos($nomerOrigNota, '110') === 0) {
                        $targetModul = "taxes";
                        $targetJenisTr = "110";
                        $targetMethod = "viewReceiptReg";
                    }

                    $printUrl = base_url() . $targetModul . "/" . $targetController . "/" . $targetMethod . "/" . $targetJenisTr . "/" . $nomerOrigNota;
                    $signStr .= "<a href=\"javascript:void(0);\" style='color:#0525f7;text-align:center;display:block;' titles='#open $nomerOrigNota' onClick=\"top.popBig('$printUrl');\"><span class='fa fa-print' style='font-size:20px'></span></a>";
                }
                $signStr .= "</td>";

                $signStr .= "</tr>";
            }
            $signStr .= "</table class='table table-condensed'>";
            $signStr .= "</div class='panel'>";
        }

        $mems = isset($this->session->login['membership']) ? $this->session->login['membership'] : array();
        $allowedView = $this->config->item('userGroup_jurnal') != null ? $this->config->item('userGroup_jurnal') : array();
        $rekeningAlias = $this->config->item('accountAlias') != null ? $this->config->item('accountAlias') : array();

        $lItems = array();
        $headers = array();
        $arrCabang = array();
        if (array_intersect($allowedView, $mems)) {
            $this->load->model("Coms/ComJurnal");
            $this->load->model("Mdls/MdlCabang");

            $cb = New MdlCabang();
            $tmpCb = $cb->lookupAll()->result();
            $arrCabang = array();
            if (sizeof($tmpCb) > 0) {
                foreach ($tmpCb as $spec) {
                    $arrCabang[$spec->id] = $spec->nama;
                }
            }

            $j = new ComJurnal();
            $j->addFilter("transaksi_no='$no'");
            if ($this->session->login['cabang_id'] == CB_ID_PUSAT) {

            }
            else {
                // dimatikan dulu untuk checking jurnal lengkapnya...........
                //                $j->addFilter("cabang_id='" . $this->session->login['cabang_id'] . "'");
            }
            //            $j->setSortBy(array("kolom" => "urut", "mode" => "ASC"));
            $tmp = $j->lookupAll()->result();
            $headers = array(
                "dtime" => "date",
                "rekening" => "account",
                "debet" => "debet",
                "kredit" => "kredit",
            );
            $lItems = array();
            if (sizeof($tmp) > 0) {
                /* ---------------------------------------
                 * array link untuk mengarahkan ke halaman mutasi yg dikendaki
                 * -----------------------------------*/

                //region Description
                $mutasiLinks = array();
                foreach ($accountChildsLinks as $rekNama => $rekLink) {
                    $mutasiLinks[$rekNama] = $rekLink . "/" . $main['pihakID'] . "?o=" . $main['cabangID'] . "&trID=$transaksiID";
                }
                //endregion

                foreach ($tmp as $row) {
                    $d_start = backCustomDate("30", $row->dtime);
                    $d_stop = $row->dtime;
                    if ($row->j_jenis == "items") {
                        $blob_ext = blobEncode($row->rekening);
                        //                        Ledger/viewMoveDetails/RekeningPembantuProduk/persediaan%20produk/1076?o=-1&ext2_id=&main_ext2_id=
                        $defLink = "Ledger/viewMoveDetails/RekeningPembantuProduk/" . $row->rekening_main . "/" . $row->extern_id . "?o=" . $row->cabang_id . "&trID=$transaksiID&date1=$d_start&date2=$d_stop&blob_ext=$blob_ext";
                    }
                    else {
                        $defLink = "Ledger/viewMoveDetails_1/Rekening/" . $row->rekening . "?o=" . $row->cabang_id . "&trID=$transaksiID&date1=$d_start&date2=$d_stop";
                        //                    $defLink = "Ledger/viewMoves_l1/Rekening/" . $row->rekening . "?o=" . $row->cabang_id . "&trID=$transaksiID&date1=$d_start&date2=$d_stop";
                    }

//                    $rekening_alias = isset($rekeningAlias[$row->rekening]) ? $rekeningAlias[$row->rekening] : $row->rekening;
                    $rekening_alias = isset(fetchAccountStructureAlias()[$row->rekening]) ? fetchAccountStructureAlias()[$row->rekening] : $row->rekening;
                    if ($row->debet > 0 || $row->kredit > 0) {
                        //$strLink = key_exists($row->rekening, $mutasiLinks) ? $mutasiLinks[$row->rekening] : $defLink;
                        $strLink = $defLink;
                        $lItems[$row->cabang_id][$row->urut][] = array(
                            "dtime" => $row->dtime,
                            "rekening" => $row->debet > 0 ? $rekening_alias : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $rekening_alias,
                            "debet" => $row->debet > 0 ? $row->debet : "",
                            "kredit" => $row->kredit > 0 ? $row->kredit : "",
                            "link" => base_url() . $strLink,
                        );
                    }
                }
                //                $this->debugArrPrint($lItems);
                ksort($lItems);
            }


        }

        $mb = New MobileDetect();
        $isMob = $mb->isMobile();
        $mobMode = $isMob == true ? "1" : "0";

        $printSettings = isset($this->configLayout[$jenisMasterTrans]['allowPrint']['1']) ? $this->configLayout[$jenisMasterTrans]['allowPrint']['1'] : array();
        $printLocation = isset($this->configLayout[$jenisMasterTrans]['printLocation']) ? $this->configLayout[$jenisMasterTrans]['printLocation'] : "Transaksi/viewReceipt/";

        $valasType = (isset($main['currencyDetails__label'])) ? "&type=" . blobEncode($main['currencyDetails__label']) : "";
        $valasFKali = "&f=" . blobEncode(1);

        if (sizeof($tmpSign) > 0) {
            if (isset($printSettings['size'])) {
                switch ($printSettings['size']) {
                    case "normal":
                        $receiptLink = "top.popBig('" . MODUL_PATH . $printLocation . $this->jenisTr . "/" . "$no?mobMode=$mobMode&st=1$valasType$valasFKali');";
                        break;
                    case "small":
                        $receiptLink = "top.popSmall('" . MODUL_PATH . $printLocation . $this->jenisTr . "/" . "$no?mobMode=$mobMode&st=2');";
                        break;
                    default:
                        //                                cekkuning("size: UNKNOWN");
                        break;
                }
            }
            else {
                $receiptLink = "top.popBig('" . MODUL_PATH . $printLocation . $this->jenisTr . "/" . "$no?mobMode=$mobMode&st=3$valasType$valasFKali');";
            }
        }
        else {
            $receiptLink = "top.popBig('" . MODUL_PATH . $printLocation . $this->jenisTr . "/" . "$no?st=4');";
        }


        // ============== TAMBAHAN ============== ============== ==============
        $itemLabels = isset($this->configUi[$jenisMasterTrans]['shoppingCartFields'][1]) ? $this->configUi[$jenisMasterTrans]['shoppingCartFields'][1] : array();
        $itemLabels2 = isset($this->configUi[$jenisMasterTrans]['shoppingCartFields2'][1]) ? $this->configUi[$jenisMasterTrans]['shoppingCartFields2'][1] : array();
        $itemLabels3 = isset($this->configUi[$jenisMasterTrans]['shoppingCartFields3'][1]) ? $this->configUi[$jenisMasterTrans]['shoppingCartFields3'][1] : array();
        $itemNumLabels = isset($this->configUi[$jenisMasterTrans]['shoppingCartNumFields'][1]) ? $this->configUi[$jenisMasterTrans]['shoppingCartNumFields'][1] : array();
        $itemNumLabels2 = isset($this->configUi[$jenisMasterTrans]['shoppingCartNumFields2'][1]) ? $this->configUi[$jenisMasterTrans]['shoppingCartNumFields2'][1] : array();
        $itemNumLabels3 = isset($this->configUi[$jenisMasterTrans]['shoppingCartNumFields3'][1]) ? $this->configUi[$jenisMasterTrans]['shoppingCartNumFields3'][1] : array();
        $sumRows = isset($this->configUi[$jenisMasterTrans]['shoppingCartSumFields'][1]) ? $this->configUi[$jenisMasterTrans]['shoppingCartSumFields'][1] : $this->configLayout[$jenisMasterTrans]['receiptSumFields'][1];
        $noteEnabled = isset($this->configUi[$this->jenisTr]['shoppingCartNoteEnabled']) && $this->configUi[$this->jenisTr]['shoppingCartNoteEnabled'] == true ? true : false;

        $showScheme = isset($this->configUi[$this->jenisTr]['shoppingCartShowScheme']) ? $this->configUi[$this->jenisTr]['shoppingCartShowScheme'] : false;


        $arrCons = array();
        $headerScheme = array();

        if ($showScheme) {

            $awal_pinjaman = isset($main['awal_pinjaman']) ? $main['awal_pinjaman'] : date('Y-m-d');
            $jatuh_tempo = isset($main['jatuh_tempo']) ? $main['jatuh_tempo'] : date('Y-m-d');
            $nilai_pinjaman = isset($main['harga']) ? $main['harga'] : 0;
            $rate_bunga = isset($main['persen_bunga']) ? $main['persen_bunga'] : 0;

            $npwp = "";
            $pph_nilai = strlen($npwp) > 10 && $pph_nilai == 15 ? 15 : 15; //dipaksa 15% untuk pemegang saham
            $valid_bunga = ($nilai_pinjaman / 12);
            $nilai_bunga = ($valid_bunga * $rate_bunga) / 100;
            $nilai_pph23 = ($nilai_bunga * $pph_nilai) / 100;

            $period = new DatePeriod(
                new DateTime($awal_pinjaman),
                new DateInterval('P1D'),
                new DateTime($jatuh_tempo)
            );

            $periodNow = new DatePeriod(
                new DateTime($awal_pinjaman),
                new DateInterval('P1D'),
                new DateTime(date('Y-m-d'))
            );

            $arrBulan = array();
            $arrBulanNow = array();
            $arrHarian = array();
            $arrWaktu = array();

            foreach ($period as $key => $value) {
                if (!isset($arrBulan[$value->format('Y-m')])) {
                    $arrBulan[$value->format('Y-m')] = array();
                }
                $arrBulan[$value->format('Y-m')][] = $value->format('Y-m-d');
            }

            foreach ($periodNow as $key => $value) {
                if (!isset($arrBulanNow[$value->format('Y-m')])) {
                    $arrBulanNow[$value->format('Y-m')] = array();
                }
                $arrBulanNow[$value->format('Y-m')][] = $value->format('Y-m-d');
            }

            $hariPadaBulanJatuhTempo = isset($arrBulan[date('Y-m', strtotime($jatuh_tempo))]) ? count($arrBulan[date('Y-m', strtotime($jatuh_tempo))]) : 0;
            $arrBulan[date('Y-m', strtotime($jatuh_tempo))][$hariPadaBulanJatuhTempo] = date('Y-m-d', strtotime($jatuh_tempo));


            $total_hari = 0;
            $total_bulan = 0;
            foreach ($arrBulan as $thnbln => $thblntgl) {
                $tmp = array(
                    "thnbln" => $thnbln,
                    "jml_hari_dbln" => count($arrBulan[$thnbln]),
                    "nilai_pinjaman" => $nilai_pinjaman,
                    "rate_bunga" => $rate_bunga,
                    "valid_bunga" => $valid_bunga * (count($arrBulan[$thnbln]) / 30),
                    "nilai_bunga" => $nilai_bunga * (count($arrBulan[$thnbln]) / 30),
                    "nilai_pph23" => $nilai_pph23 * (count($arrBulan[$thnbln]) / 30),
                    "nett_bunga" => $nilai_bunga * (count($arrBulan[$thnbln]) / 30) - ($nilai_pph23 * (count($arrBulan[$thnbln]) / 30)),
                    "silangan" => isset($arrBulanNow[$thnbln]) ? ($thnbln != date('Y-m') ? "hijau" : "berjalan") : "merah",
                );
                if (!isset($arrCons[$thnbln])) {
                    $arrCons[$thnbln] = array();
                }
                $arrCons[$thnbln] = $tmp;
                $total_bulan++;
                $total_hari += count($arrBulan[$thnbln]);
            }

            $nmPemengangSaham = "belum memilih kreditur";
            foreach ($items as $ids => $data) {
                $nmPemengangSaham = isset($data['nama']) ? $data['nama'] : "<span class='text-bol text-red'>belum ditentunkan</span>";
            }

            $headerScheme = array(
                "nama" => "$nmPemengangSaham",
                "jml_pinjaman" => "$nilai_pinjaman",
                "bunga_tahunan" => "$rate_bunga",
                "awal_meminjam" => date('d F Y', strtotime($awal_pinjaman)),
                "pelunasan_pinjaman" => date('d F Y', strtotime($jatuh_tempo)),
                "lama_pinjaman" => "$total_hari hari ($total_bulan bln)",
            );

        }

        $itemLabelsTop = $itemLabels + $itemNumLabels + array("subtotal" => "sub-amount");
        $item2LabelsTop = $itemLabels2 + $itemNumLabels2 + array("subtotal" => "sub-amount");
        $item3LabelsTop = $itemLabels3 + $itemNumLabels3 + array("subtotal" => "sub-amount");
        $itemLabelBlacklist = array(
            "stok_center",
            "stok",
        );

        foreach ($itemLabelsTop as $key => $val) {
            if (in_array($key, $itemLabelBlacklist)) {
                unset($itemLabelsTop[$key]);
            }
        }
        if (isset($this->configUi[$jenisMasterTrans]['shoppingCartHideSubamount']) && $this->configUi[$jenisMasterTrans]['shoppingCartHideSubamount'][1] == true) {
            unset($itemLabelsTop['subtotal']);
            unset($item2LabelsTop['subtotal']);
            unset($item3LabelsTop['subtotal']);
        }
        $blacklistView = $this->config->item('userGroup_blacklist') != null ? $this->config->item('userGroup_blacklist') : array();
        $addFildRowFields = array();


        $allowedViewTop = false;
        foreach ($mems as $memsVal) {
            if (!in_array($memsVal, $blacklistView)) {
                $allowedViewTop = true;
            }
        }

        if ($allowedViewTop == true) {


            $m = new MdlTransaksi();
            $m->setFilters(array());
            $tmpReg = $m->lookupDataRegistriesByMasterID($transaksiTopID)->result();
            $regfields = $m->getFields()["dataRegistry"];
            $addRow = isset($this->configUi[$jenisMasterTrans]['additionalRows']['dummyElement']['yes']) ? $this->configUi[$jenisMasterTrans]['additionalRows']['dummyElement']['yes'] : array();
            $itemNotApprove = isset($this->configUi[$jenisMasterTrans]['closedRequest'][1]) ? $this->configUi[$jenisMasterTrans]['closedRequest'][1] : true;
            $detailCore = isset($this->configCore[$jenisMasterTrans]['valueGates']['detail']) ? $this->configCore[$jenisMasterTrans]['valueGates']['detail'] : array();

            if (sizeof($tmpReg) > 0) {
                foreach ($tmpReg as $row) {
                    foreach ($regfields as $params) {
//                        cekHitam($params);
                        if ($params != "transaksi_id") {
                            if (isset($row->$params)) {
                                $var = $params . "_reg";
                                $$var = unserialize(base64_decode($row->$params));
                            }
                        }

                    }
                    // $$var = unserialize(base64_decode($row->values));
                }
                if (sizeof($addRow) > 0) {
                    foreach ($addRow as $gateKey => $mpFields) {

                        if (isset($mpFields['hideRow']) && $mpFields['hideRow'] == true) {

                        }
                        else {
                            $addFildRowFields[$gateKey] = $mpFields['label'];
                        }
                    }
                }

                //----------------------------------
                if ($itemNotApprove == true) {
                    // $dTmp = $m->lookupDetailTransaksi($transaksiTopID)->result();
                    // dari model sudah di result breooo
                    $dTmp = $m->lookupDetailTransaksi($transaksiTopID);

                    $notApprove = array();
                    foreach ($dTmp as $spec) {
                        if ((isset($spec->valid_qty_no_approve)) && ($spec->valid_qty_no_approve > 0)) {
                            $notApprove[$spec->produk_id] = $spec->valid_qty_no_approve;
                        }
                    }
                    $mainNotApprove = array();
                    $itemsNotApprove = array();
                    if (sizeof($notApprove) > 0) {
                        $itemsNotApprove = $items_reg;
                        foreach ($notApprove as $pid => $pqty_not_approve) {
                            $itemsNotApprove[$pid]['qty'] = $pqty_not_approve;
                            $itemsNotApprove[$pid]['jml'] = $pqty_not_approve;
                            $itemsNotApprove[$pid]['subtotal'] = $pqty_not_approve * $itemsNotApprove[$pid]['harga'];
                            if (sizeof($detailCore) > 0) {
                                foreach ($detailCore as $key_c => $val_c) {
                                    $itemsNotApprove[$pid]['sub_' . $key_c] = $pqty_not_approve * $itemsNotApprove[$pid][$key_c];
                                    if (!isset($mainNotApprove[$key_c])) {
                                        $mainNotApprove[$key_c] = 0;
                                    }
                                    $mainNotApprove[$key_c] += ($pqty_not_approve * $itemsNotApprove[$pid][$key_c]);
                                }
                            }
                            if (!isset($mainNotApprove['harga'])) {
                                $mainNotApprove['harga'] = 0;
                            }
                            $mainNotApprove['harga'] += ($pqty_not_approve * $itemsNotApprove[$pid]['harga']);
                        }
                    }
                }
            }

            //tambahan jika ada result item
            // $this->debugArrPrint($main_reg);
            if (sizeof($rsltItems_reg) > 0) {
                //region reference detil
                $m->setFilters(array());
                $tmpRegRef = $m->lookupDataRegistriesByMasterID($main_reg['referenceID']);
                foreach ($tmpRegRef as $refRow) {
                    foreach ($regfields as $param) {
                        if (isset($refRow->$param)) {
                            if ($param != "transaksi_id") {
                                $varR = $param . "_ref";
                                $$varR = unserialize(base64_decode($refRow->$param));
                            }

                        }
                    }
                }

                $tmpRowRslts_ref = array();
                $totalNilaiRef = 0;
                if (sizeof($rsltItems_ref) > 0) {
                    foreach ($rsltItems_ref as $rowRefID => $rowref_data) {
                        // $hpp = number_format($rowref_data['hpp'],0);
                        $hpp = $rowref_data['hpp'];
                        if (!isset($tmpRowRslts_ref[$rowref_data['id']][$main_reg['pihakExternID']][$hpp]['qty'])) {
                            $tmpRowRslts_ref[$rowref_data['id']][$main_reg['pihakExternID']][$hpp]['qty'] = 0;

                        }
                        if (!isset($tmpRowRslts_ref[$rowref_data['id']][$main_reg['pihakExternID']][$hpp]['subtotal'])) {
                            $tmpRowRslts_ref[$rowref_data['id']][$main_reg['pihakExternID']][$hpp]['subtotal'] = 0;
                        }

                        $tmpRowRslts_ref[$rowref_data['id']][$main_reg['pihakExternID']][$hpp]['qty'] += $rowref_data['qty'];
                        $tmpRowRslts_ref[$rowref_data['id']][$main_reg['pihakExternID']][$hpp]['subtotal'] += $rowref_data['sub_hpp'];
                        $tmpRowRslts_ref[$rowref_data['id']][$main_reg['pihakExternID']][$hpp]['harga'] = $rowref_data['hpp'];
                        $totalNilaiRef += $rowref_data['sub_hpp'];
                    }
                    $rowDetilRslt_label[$main_reg['pihakExternID']] = array(
                        "harga" => "harga",
                        "qty" => "qty",
                        "subtotal" => "subtotal",
                    );
                }
                //endregion

                //region current detil
                $tmpRowRslts = array();
                $tmpRowRsltsnilai = 0;
                foreach ($rsltItems_reg as $rowFifo => $fifoData) {
                    // $hpp_0 = number_format($fifoData['hpp'],0);
                    $hpp_0 = $fifoData['hpp'];
                    if (!isset($tmpRowRslts_ref[$fifoData['id']][$main_reg['jenisTr']][$hpp_0]['qty'])) {
                        $tmpRowRslts_ref[$fifoData['id']][$main_reg['jenisTr']][$hpp_0]['qty'] = 0;
                    }
                    if (!isset($tmpRowRslts_ref[$fifoData['id']][$main_reg['jenisTr']][$hpp_0]['subtotal'])) {
                        $tmpRowRslts_ref[$fifoData['id']][$main_reg['jenisTr']][$hpp_0]['subtotal'] = 0;
                    }
                    $tmpRowRslts_ref[$fifoData['id']][$main_reg['jenisTr']][$hpp_0]['qty'] += $fifoData['qty'];
                    $tmpRowRslts_ref[$fifoData['id']][$main_reg['jenisTr']][$hpp_0]['subtotal'] += $fifoData['sub_hpp'];
                    $tmpRowRslts_ref[$fifoData['id']][$main_reg['jenisTr']][$hpp_0]['harga'] = $fifoData['hpp'];
                    $tmpRowRsltsnilai += $fifoData['sub_hpp'];
                }
                $tmpRowRslts_label = array(
                    "nama" => "produk",
                    "qty" => "qty",
                    $main_reg['pihakExternID'] => $main_reg['pihakExternName'],
                    $main_reg['jenisTr'] => $main_reg['jenisTrName'],
                );
                $rowDetilRslt_label[$main_reg['jenisTr']] = array(
                    "harga" => "harga",
                    "qty" => "qty",
                    "subtotal" => "subtotal",
                );

                //endregion
            }
        }

        $transaksiJenisTopLabel = isset($main_reg['jenisTrName']) ? $main_reg['jenisTrName'] : "";
        //-------------------------------------
        if (($configNotes != NULL) && ($globalVars['trash_4'] == 1)) {
            foreach ($globalVars as $key => $val) {
                if (!is_array($val)) {
                    $configNotes = str_replace("{" . $key . "}", $val, $configNotes);
                }
            }
            $notes_final = $configNotes;
        }
        else {
            $notes_final = NULL;
        }
        //-------------------------------------
        if ($gunggunganMode == 1) {
            $itemLabels = isset($this->configUi[$jenisMasterTrans]['shoppingCartFieldsGunggungTop'][1]) ? $this->configUi[$jenisMasterTrans]['shoppingCartFieldsGunggungTop'][1] : array();
            $itemLabelsTop = $itemLabels + $itemNumLabels + array("subtotal" => "sub-amount");
        }
        //-------------------------------------

        $data = array(
            "mode" => $this->uri->segment(3),
            "title" => $no,
            "showScheme" => $arrCons,
            "headerScheme" => $headerScheme,
            "headers" => $headers,
            "main" => isset($main) ? $main : array(),
            "items" => $lItems,
            "receiptLink" => $receiptLink,
            "signStr" => $signStr,
            "cabangData" => $arrCabang,

            "urutCounter" => $urutCounters,
            "underMaintenance" => underConstruction(),

            // ===== ======
            "detail_title" => "Pengajuan $transaksiJenisTopLabel",

            "itemLabels" => isset($itemLabelsTop) ? $itemLabelsTop : array(),
            "item2Labels" => isset($item2LabelsTop) ? $item2LabelsTop : array(),
            "item3Labels" => isset($item3LabelsTop) ? $item3LabelsTop : array(),

            "detail_main" => isset($main_reg) ? $main_reg : array(),
            "detail_items" => isset($items_reg) ? $items_reg : array(),
            "detil_item_rslt" => isset($tmpRowRslts_ref) ? $tmpRowRslts_ref : array(),
            // "detil_item_rslt_ref" => isset($tmpRowRslts_ref) ? $tmpRowRslts_ref : array(),
            "detil_item_rslt_label" => isset($tmpRowRslts_label) ? $tmpRowRslts_label : array(),
            "detil_item_rslt_label2" => isset($rowDetilRslt_label) ? $rowDetilRslt_label : array(),
            "detail2_items" => isset($items2_sum_reg) ? $items2_sum_reg : array(),
            "detail3_items" => isset($items3_sum_reg) ? $items3_sum_reg : array(),

            "detail_sumRows" => $sumRows,
            "detail2_sumRows" => isset($sumRows2) ? $sumRows2 : array(),
            "detail3_sumRows" => isset($sumRows3) ? $sumRows3 : array(),
            "noteEnabled" => $noteEnabled,
            "addRows" => $addFildRowFields,
            //--------------------
            "mainNotApprove" => isset($mainNotApprove) ? $mainNotApprove : array(),
            "itemsNotApprove" => isset($itemsNotApprove) ? $itemsNotApprove : array(),
            "detail_not_approve_title" => "Pengajuan $transaksiJenisTopLabel (tidak diapprove)",
            //--------------------
            "notes_final" => $notes_final,
        );
        $this->load->view("transaksi", $data);
    }

    public function viewMyActs()
    {

        if (!isset($this->session->login['id'])) {
            redirect(base_url() . "Login");
        }

        $uID = isset($_GET['u']) && $_GET['u'] > 0 ? $_GET['u'] : $this->session->login['id'];
        $mems = isset($this->session->login['membership']) ? $this->session->login['membership'] : array();

        $limit = 18;
        $maxPageNum = 20;
        $jenisTr = $this->jenisTr;
        $cCode = $this->cCode;
        //        $historyFields = $this->configUi[$jenisTr]['shortHistoryFields'];
        $historyFields = isset($this->config->item("heTransaksi_report")[$jenisTr]['longHistoryFields']) ? $this->config->item("heTransaksi_report")[$jenisTr]['longHistoryFields'] : array(
            "produk_nama" => "item name",
            "produk_ord_jml" => "qty",
            "produk_ord_hrg" => "@price",

            "nomer_top+nomer" => "receipt number",
            "oleh_nama+dtime" => "person",
        );
        $mb = New MobileDetect();
        $isMob = $mb->isMobile();
        if ($isMob) {
            $historyFields = isset($this->configUi[$jenisTr]['compactHistoryFields']) ? $this->configUi[$jenisTr]['compactHistoryFields'] : array();
        }

        //        arrprint($historyFields);
        //
        //region preparing ERP step labels for top link
        $steps = $this->configUi[$jenisTr]['steps'];
        $stepLabels = array(//            "0" => "all"
        );
        $stepLinks = array(//            "0" => base_url() . $this->uri->segment(1) . "/" . $this->uri->segment(2) . "/" . $this->uri->segment(3)
        );
        if (sizeof($steps) > 1) {
            $subCodes = array();
            $stepCodes = array();
            $jmlStep = count($steps);

            foreach ($steps as $stepNumber => $stepSpec) {
                if ($stepNumber <= $jmlStep) {
                    $subCodes[$stepSpec['target']] = $stepSpec['label'];
                    $stepCodes[] = $stepSpec['target'];
                    //                    $stepLabels[$stepNumber] = $stepSpec['stateLabel'];
                    $stepLabels[$stepNumber] = $stepSpec['label'];
                    $stepLinks[$stepNumber] = MODUL_PATH . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->jenisTr . "/" . $stepSpec['target'];
                }

            }


            //            $currentState = strlen($this->uri->segment(4)) > 0 ? $this->uri->segment(4) : $this->jenisTr;
            $currentState = strlen($this->uri->segment(5)) > 0 ? $this->uri->segment(5) : $this->configUi[$jenisTr]['steps'][1]['target'];
        }
        //endregion

        //
        //region lookup histories


        $this->load->model("MdlTransaksi");
        $tr = new MdlTransaksi();

        $identifiers = $tr->fetchIdentifiers();
        $recaps = array();
        $names = array();

        $tr->addFilter("transaksi.cabang_id='" . $this->session->login['cabang_id'] . "'");
        $tr->addFilter("gudang_id='" . $this->session->login['gudang_id'] . "'");
        $tr->addFilter("jenis_master='" . $this->jenisTr . "'");
        $tr->addFilter("transaksi.oleh_id='" . $uID . "'");//dimatiin buat test

        //region date filter
        $date1 = isset($_GET['date1']) ? $_GET['date1'] : date("Y-m-d");
        $date2 = isset($_GET['date2']) ? $_GET['date2'] : date("Y-m-d");
        $this->db->where("fulldate>='" . $date1 . "'");
        $this->db->where("fulldate<='" . $date2 . "'");
        //endregion

        if (isset($currentState)) {
            $tr->addFilter("jenis='" . $currentState . "'");
        }

        //        $tr->addFilter("next_substep_code=''");


        $addParams = $this->decodeSafeBase64ArrayParam('addParams');
        if ($addParams != null && sizeof($addParams) > 0) {
            //            arrprint($addParams);
            $this->addSafeFilters($tr, $addParams);
        }

        $action = array(
            // "viewDetails" => MODUL_PATH . get_class($this) . "/viewDetails",
            "viewDetails" => MODUL_PATH . "/views/viewDetails",
        );


        //region date filter
        $date1 = $this->getSafeDateParam('date1', date("Y-m-d"));
        $date2 = $this->getSafeDateParam('date2', date("Y-m-d"));
        $this->db->where("fulldate >=", $date1);
        $this->db->where("fulldate <=", $date2);
        //endregion


        $arrayHistory = array();
        $arrayHistory_ids = array();

        //region ngumpulin ID transaksi
        $trIDs = array();
        $tr0 = $tr;
        //        $tmpHist0 = $tr0->lookupHistories_joined_all($jmlData, $limit, $page)->result();
        $tmpHist0 = $tr0->lookupAll()->result();
// cekLime($this->db->last_query());
// $this->debugArrPrint($tmpHist0);
// matiHEre(__LINE__);
        if (sizeof($tmpHist0) > 0) {
            foreach ($tmpHist0 as $row) {
                if (!in_array($row->id, $trIDs)) {
                    $trIDs[] = $row->id;
                }
            }
        }
        //endregion


        //pairingan dari elements
        //region pairing dari elements
        if (isset($this->config->item("heTransaksi_elementPairs")[$this->jenisTr])) {
            // matiHEre(__LINE__);
            $pairConfig = $this->config->item("heTransaksi_elementPairs")[$this->jenisTr];

        }
        if (sizeof($trIDs) > 0) {
            // matiHEre(__LINE__);
            $trReg = new MdlTransaksi();
            $trReg->setFilters(array());
            // $trReg->addFilter("param='main_elements'");
            $trReg->setJointSelectFields("main_elements,transaksi_id");//string
            $trReg->addFilter("transaksi_id in (" . implode(",", $trIDs) . ")");
            $tmpReg = $trReg->lookupDataRegistries()->result();
            // cekkuning($this->db->last_query());
            // $this->debugArrPrint($tmpReg);
            // matiHere();
            if (sizeof($tmpReg) > 0) {
                foreach ($tmpReg as $row) {
                    // $this->debugArrPrint($row);
                    //     matiHEre();
                    $els = blobDecode($row->main_elements);

                    $trID = $row->transaksi_id;
                    if (!isset($identPairs[$trID])) {
                        $identPairs[$trID] = array();
                        $namePairs[$trID] = array();
                        $fieldPairs[$trID] = array();
                    }
                    if (sizeof($els) > 0) {
                        foreach ($els as $elName => $elSpec) {
                            if (isset($pairConfig[$elName])) {

                                //                            arrprint($elSpec);
                                $elPairs = hePairFromElement($this->jenisTr, $elName, $elSpec);
                                //                            arrprint($elPairs['content']);
                                $identifiers = $identifiers + $elPairs['identifiers'];
                                $identPairs[$trID] = $identPairs[$trID] + $elPairs['identifiers'];
                                $names[$elName . "_id"][$elPairs['content'][$elName . "_id"]] = $elName . "_" . $elPairs['content'][$elName . "_nama"];
                                //                                $namePairs[$trID][$elName."_id"][$elPairs['content'][$elName."_id"]]=$elPairs['content'][$elName."_nama"];
                                $namePairs[$trID][$elName . "_id"]["id"] = $elPairs['content'][$elName . "_id"];
                                $namePairs[$trID][$elName . "_id"]["name"] = $elPairs['content'][$elName . "_nama"];
                                if (!isset($fieldPairs[$trID][$elName . "_id"])) {
                                    $fieldPairs[$trID][$elName . "_id"] = "";
                                    $fieldPairs[$trID][$elName . "_nama"] = "";
                                }
                                $fieldPairs[$trID][$elName . "_id"] = $elPairs['content'][$elName . "_id"];
                                $fieldPairs[$trID][$elName . "_nama"] = $elPairs['content'][$elName . "_nama"];
                                //                                cekhijau("pairResult");
                                //                                arrprint($elPairs);
                            }
                        }
                    }
                }
            }
        }
        //endregion
// matiHEre("hoop".__LINE__);
        //region date filter
        $date1 = isset($_GET['date1']) ? $_GET['date1'] : date("Y-m-d");
        $date2 = isset($_GET['date2']) ? $_GET['date2'] : date("Y-m-d");
        $this->db->where("fulldate>='" . $date1 . "'");
        $this->db->where("fulldate<='" . $date2 . "'");
        //endregion
        $tmpHist = $tr->lookupHistories_joined_all()->result();
        //        cekbiru($this->db->last_query());

        //        cekMerah($this->db->last_query());


        if (sizeof($tmpHist) > 0) {
            foreach ($tmpHist as $row) {
                $trID = $row->transaksi_id;
                $tmp = array();
                $tmp1 = array();
                foreach ($historyFields as $fName => $fLabel) {
                    if (strpos($fName, '+') !== false) {//==mengandung penggabungan (+)
                        $chars = explode("+", $fName);
                        $colValue = "";
                        foreach ($chars as $key) {
                            $colValue .= isset($row->$key) ? formatField($key, $row->$key) . "<br>" : "";
                        }
                        $colValue = rtrim($colValue, "<br>");
                    }
                    else {
                        $colValue = isset($row->$fName) ? formatField($fName, $row->$fName) : "";
                    }

                    //                    $tmp[$fName] = formatField($fName, $row->$fName);
                    $tmp[$fName] = $colValue;
                    $tmp1["id"] = $row->id;
                    //                    $arrayHistory[$row->transaksi_id][$fName] = $colValue;
                }
                if (isset($fieldPairs[$trID]) && sizeof($fieldPairs[$trID]) > 0) {
                    foreach ($fieldPairs[$trID] as $k => $v) {
                        $tmp[$k] = $v;
                    }
                }
                $arrayHistory[] = $tmp;
                $arrayHistory_ids[] = $tmp1;

                $jenis = $row->jenis;
                foreach ($identifiers as $iID => $iName) {
                    //                    cekbiru("checking $iID");
                    if ((isset($row->$iID) > 0 && strlen($row->$iID) > 0 && isset($row->$iName) > 0 && strlen($row->$iName) > 0) || (isset($namePairs[$trID][$iID]["id"]) && strlen($namePairs[$trID][$iID]["id"]) > 0)) {
                        //                            cekkuning("$iID/$iName");

                        //                        cekmerah("$iID being processed");
                        if (!isset($names[$iID])) {
                            $names[$iID] = array();
                        }
                        //                        $srcName = "";
                        if (isset($identPairs[$trID]) && array_key_exists($iID, $identPairs[$trID])) {
                            $srcID = $namePairs[$trID][$iID]["id"];
                            $srcName = $namePairs[$trID][$iID]["name"];
                            //                            cekbiru("$trID $srcID reading from element value: $srcID/$srcName");

                        }
                        else {
                            $srcID = $row->$iID;
                            $srcName = $row->$iName;
                            //                            cekbiru("$trID $srcID reading from table row: $srcID/$srcName");
                        }
                        //                        cekkuning("$iID/$iName/$srcID");
                        $names[$iID][$srcID] = $srcName;
                        if (!isset($recaps[$jenis][$iID][$srcID])) {
                            $recaps[$jenis][$iID][$srcID] = array(
                                "qty" => 0,
                                "value" => 0,
                            );
                        }

                        $recaps[$jenis][$iID][$srcID]['qty'] += $row->produk_ord_jml;
                        $recaps[$jenis][$iID][$srcID]['value'] += ($row->produk_ord_jml * $row->produk_ord_hrg);
                    }
                    else {
                        //                        cekmerah("$iID NOT being processed");
                    }
                }

            }
        }
        //endregion

        //region link to add new transaction
        if (placeCanMakeTrans($this->session->login['membership'], $this->session->login['cabang_id'], $this->session->login['gudang_id'], $this->jenisTr)) {
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

        //
        //region prepare params for viewer
        $subTitle = lgTranslateTime($date1) . " to " . lgTranslateTime($date2);
        if ($date1 == $date2) {
            $subTitle = lgTranslateTime($date1);
        }

        $availFilters = array();
        if (sizeof($mems) > 0) {
            foreach ($mems as $gID) {
                if (isset($this->config->item("heTransaksi_identifierGroups")[$gID]) && sizeof($this->config->item("heTransaksi_identifierGroups")[$gID]) > 0) {
                    foreach ($this->config->item("heTransaksi_identifierGroups")[$gID] as $fID => $fLabel) {
                        if (!array_key_exists($fID, $availFilters)) {
                            $availFilters[$fID] = $fLabel;
                        }
                    }

                }
            }
        }

        //        arrprint($availFilters);die();


        //arrprint($names);die();

        //arrprint($arrayHistory);
        $thisTr = strlen($this->uri->segment(5)) > 0 ? $this->uri->segment(5) : $this->jenisTr;
        $thisTrName = isset($row->jenis_label) ? $row->jenis_label : $this->configUi[$jenisTr]["label"];
        $data = array(
            "mode" => $this->uri->segment(3),
            "isMobile" => $isMob,
            "jenisTr" => $jenisTr,
            //            "trName"               => $this->configUi[$jenisTr]["label"],
            "trName" => $thisTrName,
            "errMsg" => $this->session->errMsg,
            "title" => (isset($subCodes) && isset($currentState) ? $subCodes[$currentState] : $this->jenisTrName) . " (" . $this->session->login['nama'] . ")",
            "subTitle" => $subTitle,
            //            "pageCount"            => $numPages,
            //            "page"                 => $page,
            //            "pages"                => $pages,
            "arrayHistoryLabels" => $historyFields,
            "arrayHistory" => $arrayHistory,
            "arrayHistoryId" => $arrayHistory_ids,
            "action" => $action,
            "steps" => $steps,
            "stepLabels" => $stepLabels,
            "stepLinks" => $stepLinks,
            "addParams" => isset($_GET['addParams']) ? $_GET['addParams'] : null,
            "currentState" => isset($currentState) ? $currentState : "all states",
            "alternateLink" => MODUL_PATH . $this->uri->segment(2) . "/viewIncomplete/" . $this->uri->segment(3) . "/" . $this->jenisTr,
            "alternateLinkCaption" => "incomplete " . $this->configUi[$jenisTr]["label"] . " <span class='glyphicon glyphicon-arrow-right'></span>",
            "addLink" => $addLink,
            "filters" => array(
                "dates" => $this->dates,
                "date1" => $date1,
                "date2" => $date2,
            ),
            "thisPage" => MODUL_PATH . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->jenisTr,
            //
            //            "availFilters"=>isset($this->config->item("heTransaksi_report")[$jenisTr]["availFilters"])?$this->config->item("heTransaksi_report")[$jenisTr]["availFilters"]:array(),"availFilters"=>isset($this->config->item("heTransaksi_report")[$jenisTr]["availFilters"])?$this->config->item("heTransaksi_report")[$jenisTr]["availFilters"]:array(),
            "availFilters" => isset($availFilters) ? $availFilters : array("oleh_id" => "person"),

            "names" => $names,
            "recaps" => isset($recaps[$thisTr]) ? $recaps[$thisTr] : array(),

        );
        //endregion


        $this->load->view("history", $data);
    }

    public function viewOutstanding()
    {
        if (!isset($this->session->login['id'])) {
            gotoLogin();
            // redirect(base_url() . "Login");
        }
        $this->debugArrPrint($this->uri->segment_array());

        $limit = 1000;
        //        $limit = 18;
        $maxPageNum = 20;
        $jenisTr = $this->jenisTr;
        $jenisTrsub = $this->uri->segment(6);
        $cCode = "_TR_" . $this->jenisTr;
        $currentState = strlen($this->uri->segment(5)) > 0 ? $this->uri->segment(5) : "transaksi";
        $selecetedCode = isset($this->configUi[$jenisTr]['steps']['2']['target']) ? $this->configUi[$jenisTr]['steps']['2']['target'] : "";
        $historyFields = isset($this->configLayout[$jenisTr]['fixedFieldHold']) ? $this->configLayout[$jenisTr]['fixedFieldHold'] : array();
        $detailsFields = isset($this->configLayout[$jenisTr]['fixedFieldHold'][$currentState]['loop']) ? $this->configLayout[$jenisTr]['fixedFieldHold'][$currentState]['loop'] : array();
        $arrayValid = isset($this->configLayout[$jenisTr]['fixedFieldHold'][$currentState]['array_flip']) ? $this->configLayout[$jenisTr]['fixedFieldHold'][$currentState]['array_flip'] : array();
        $lockerStock = isset($this->configLayout[$jenisTr]['lockerStock']) ? $this->configLayout[$jenisTr]['lockerStock'] : "MdlLockerStock";

        $menuLabel = isset($this->configUi[$jenisTr]['label']) ? $this->configUi[$jenisTr]['label'] : "";

        if (isset($_SESSION[$cCode])) {
            $_SESSION[$cCode] = null;
            unset($_SESSION[$cCode]);
        }


        //            $stepNumber = isset($_SESSION[$cCode]['tableIn_master']['step_number']) ? $_SESSION[$cCode]['tableIn_master']['step_number'] : 1;
        //region session init
        if (!isset($_SESSION[$cCode])) {
            $_SESSION[$cCode] = array(
                "items" => array(),
                "main" => array(),
            );
        }
        if (!isset($_SESSION[$cCode]['main'])) {
            $_SESSION[$cCode]['main'] = array();
        }
        if (!isset($_SESSION[$cCode]['items'])) {
            $_SESSION[$cCode]['items'] = array();
        }
        //endregion

        //       $this->debugArrPrint($arrayFlip);
        $mb = New MobileDetect();
        $this->load->model("Mdls/$lockerStock");
        $st = new $lockerStock();
        //        $st->addFilter("jenis='produk'");
        //        $st->addFilter("jenis_locker='stock'");
        $st->addFilter("state='active'");

        $tmpStock = $st->lookupAll()->result();

        $cabangID = (isset($_GET['o']) && $_GET['o'] <> 0) ? $_GET['o'] : $this->session->login['cabang_id'];
        $gudangID = (isset($_GET['o']) && $_GET['o'] <> 0) ? $_GET['o'] : $this->session->login['gudang_id'];

        $marking_style = array();
        if (isset($_GET['trID']) && ($_GET['trID'] > 0)) {
            $marking_style[$_GET['trID']] = "background-color:yellow;font-size:20px;";
        }


        //        $this->debugArrPrint($tmpStock);

        $isMob = $mb->isMobile();
        if ($isMob) {
            $historyFields = isset($this->configUi[$jenisTr]['compactHistoryFields']) ? $this->configUi[$jenisTr]['compactHistoryFields'] : array();
        }

        $backdate_f = formatTanggal(backDate(30), 'Y-m-d');

        $date1 = isset($_GET['date1']) ? $_GET['date1'] : $backdate_f;
        $date2 = isset($_GET['date2']) ? $_GET['date2'] : date("Y-m-d");

        //region prepare ERP
        $stepLabels = array(//            "0" => "all"
        );
        $stepLinks = array(//            "0" => base_url() . $this->uri->segment(1) . "/" . $this->uri->segment(2) . "/" . $this->uri->segment(3)
        );
        if (sizeof($historyFields) > 1) {
            $subCodes = array();
            $stepCodes = array();
            $jmlStep = count($historyFields);

            foreach ($historyFields as $stepNumber => $stepSpec) {
                $subCodes[$stepNumber] = $stepNumber;
                $stepCodes[] = $stepNumber;
                $stepLabels[$stepNumber] = $stepSpec['label'];
                $stepLinks[$stepNumber] = MODUL_PATH . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->jenisTr . "/" . $stepNumber . "/" . "?date1=$date1&date2=$date2";
            }
        }
        //endregion


        //region prepare data outstanding
        $this->load->model("MdlTransaksi");
        $tr = new MdlTransaksi();
        $tr1 = new MdlTransaksi();

        $tr->addFilter("jenis_master='" . $this->jenisTr . "'");
        $tr->addFilter("div_id='" . $this->session->login['div_id'] . "'");
        $tr->addFilter("jenis='$selecetedCode'");
        $tr->addFilter("transaksi.cabang_id='$cabangID'");

        //        $tr->addFilter("id_master='14005'");
        $searchStr = isset($_GET['search']) ? $_GET['search'] : "";

        //region date filter
        //         $this->db->where("fulldate>='" . $date1 . "'");
        //         $this->db->where("fulldate<='" . $date2 . "'");
        //endregion


        $addParams = $this->decodeSafeBase64ArrayParam('addParams');
        if ($addParams != null && sizeof($addParams) > 0) {
            //            arrprint($addParams);
            $this->addSafeFilters($tr, $addParams);
        }

        if (isset($_GET['search'])) {
            $tr->setKeyWord($searchStr);
        }
        else {
        }
        $jmlData = $tr->lookupDataCount();
        //        $limit=10;
        $page = (isset($_GET['page']) && $_GET['page'] > 0) ? ($_GET['page']) : 1;
        $offset = ($limit * ($page - 1));

        $addParams = $this->decodeSafeBase64ArrayParam('addParams');
        if ($addParams != null && sizeof($addParams) > 0) {
            //            arrprint($addParams);
            $this->addSafeFilters($tr, $addParams);
        }


        if (isset($_GET['search'])) {
            $tr->setKeyWord($searchStr);
        }
        else {
        }

        // $tr->addFilter("sub_step_number>0");
        // $tr->addFilter("valid_qty>0");
        $tr->addFilterJoin("sub_step_number>0");
        $tr->addFilterJoin("valid_qty>0");

        $tmpTr = $tr->lookupJoined();
        //        endregion

        $extractedItems = array();//==untuk urusan update transaksi referer
        $validItems = array();
        $validItemSends = array();
        $mainData = array();
        $itemsVal = array();
        $itemData = array();
        $mainDataOutstandingItems = array();

        $kolom_2s = array(
            "cabang_id",
            "produk_id",
            "jumlah",
            "gudang_id",
        );

        $stocks = array();
        foreach ($tmpStock as $temps) {
            $tempDatas = array();
            foreach ($kolom_2s as $kolom) {
                $$kolom = $temps->$kolom;
                $tempDatas[$kolom] = $temps->$kolom;
            }
            $stocks[$cabang_id][$produk_id] = $tempDatas;
        }

        $summary = array();
        $arrayAction = array();
        $arrayItemsMasterToID = array();
        $arrayItemsReg = array();
        $arrayItemsRegPair = array();
        $arrayItemsOutstandingPair = array();
        // $this->debugArrPrint($tmpTr);
        if (sizeof($tmpTr) > 0) {
            //            arrprint($historyFields[$currentState]);
            if (isset($historyFields[$currentState]['items'])) {
                $listTrID = array();
                foreach ($tmpTr as $row) {
                    $listTrID[] = $row->id;
                    $ind_reg = $row->indexing_registry != NULL ? blobDecode($row->indexing_registry) : array();
                    $arrayItemsReg[$row->id_master] = isset($ind_reg['items']) ? $ind_reg['items'] : 0;
                    $arrayItemsMasterToID[$row->id_master] = $row->transaksi_id;
                }
                //                $this->debugArrPrint($arrayItemsReg);
                // membaca registry items
                $tr->setFilters(array());
                // $tr->addFilter("id in ('" . implode("','", $arrayItemsReg) . "')");
                $tr->setJointSelectFields("items,transaksi_id");
                $tr->addFilter("transaksi_id in ('" . implode("','", $listTrID) . "')");
                $tmpReg = $tr->lookupDataRegistries()->result();
                // cekLime($this->db->last_query());
                // matiHEre();
                foreach ($tmpReg as $rSpec) {
                    $arrayItemsRegPair[$rSpec->transaksi_id] = blobDecode($rSpec->items);
                }
                foreach ($arrayItemsRegPair as $trID => $tmp) {
                    foreach ($tmp as $pID => $etmp) {
                        if (isset($historyFields[$currentState]['items']['outstanding_items'])) {
                            foreach ($historyFields[$currentState]['items']['outstanding_items'] as $val) {
                                $arrayItemsOutstandingPair[$trID][$pID] = $etmp[$val];
                            }
                        }
                    }
                }
            }
            //            $this->debugArrPrint($arrayItemsMasterToID);
            //            $this->debugArrPrint($arrayItemsOutstandingPair);
            foreach ($tmpTr as $row) {
                if (!isset($validItems[$row->produk_id])) {
                    $validItems[$row->id_master][$row->produk_id] = 0;
                }
                if (!isset($validItemSends[$row->produk_id])) {
                    $validItemSends[$row->id_master][$row->produk_id] = 0;
                }

                $validItems[$row->id_master][$row->produk_id] += $row->valid_qty;
                $validItemSends[$row->id_master][$row->produk_id] += $row->produk_ord_jml - $row->valid_qty;

                if (!isset($extractedItems[$row->produk_id])) {
                    $extractedItems[$row->id_master][$row->produk_id] = array();
                }

                $no = 0;
                $srcKey = isset($historyFields[$currentState]['srcKey']) ? $historyFields[$currentState]['srcKey'] : array();

                if (isset($historyFields[$currentState]['fields'])) {
                    foreach ($historyFields[$currentState]['fields'] as $srcKey_0 => $srcAlias) {
                        $no++;
                        $val_main = isset($row->$srcKey_0) ? makeValue($srcKey_0, json_decode(json_encode($row), true), json_decode(json_encode($row), true)) : "";
                        //                        $mainData[$row->$srcKey][$srcKey_0] = formatField($srcKey_0, $val_main);

                        if (is_array($srcAlias)) {
                            $hisStep = $srcAlias['step'];
                            $hisKey = $srcAlias['key'];
                            if (isset($row->ids_his)) {
                                if ($hisKey == "nomer") {
                                    $returnVal = showHistoriGlobalNumbers($row->ids_his, $hisStep, true, $this->jenisTr);
                                    if ($returnVal == "") {
                                        $mainData[$row->$srcKey][$srcKey_0] = "-";
                                    }
                                    else {
                                        $mainData[$row->$srcKey][$srcKey_0] = $returnVal;
                                    }
                                }
                                else {
                                    $ids_his_decode = blobDecode($row->ids_his);
                                    if (isset($ids_his_decode[$hisStep][$hisKey])) {
                                        $mainData[$row->$srcKey][$srcKey_0] = $ids_his_decode[$hisStep][$hisKey];
                                    }
                                    else {
                                        $mainData[$row->$srcKey][$srcKey_0] = "-";
                                    }
                                }
                            }
                            else {
                                $mainData[$row->$srcKey][$srcKey_0] = "-";
                            }
                        }
                        else {
                            $mainData[$row->$srcKey][$srcKey_0] = formatField_he_format($srcKey_0, $val_main);
                        }
                    }

                    // mereplace manual aoutstanding items...
                    $mainDataOutstandingItems[$row->$srcKey][] = array(
                        "valid_qty" => $row->valid_qty,
                        "label" => $row->produk_label,
                        "kode" => $row->produk_kode,
                        "produk_id" => $row->produk_id,
                    );

                }

                $arrAsem[$row->produk_id][] = $row->produk_kode;

                if (sizeof($detailsFields) > 0) {
                    foreach ($detailsFields as $fieldKey => $key) {
                        $val = makeValue($key, json_decode(json_encode($row), true), json_decode(json_encode($row), true));
                        $itemData[$row->$srcKey][$fieldKey][] = formatField_he_format($fieldKey, $val);
                        //                        foreach ($tmpStock as $temps) {
                        //                            if($row->produk_id==$temps->produk_id && $temps->cabang_id==$cabangID){
                        //                                $stocks[$cabangID][$row->produk_id] = $temps->jumlah;
                        //                            }
                        //                        }
                        //                        $itemData[$row->$srcKey]['stok'][] = $row->produk_id;
                        $itemData[$row->$srcKey]['stok'][] = isset($stocks[$cabangID][$row->produk_id]['jumlah']) ? $stocks[$cabangID][$row->produk_id]['jumlah'] : 0;
                    }
                }

            }
            //            $this->debugArrPrint($arrayItemsOutstandingPair);
            // ======================================================================
            // memairingkan qty outstanding dengan masterID masing-masing.
            if (sizeof($mainData) > 0) {
                if (sizeof($mainDataOutstandingItems) > 0) {
                    foreach ($mainDataOutstandingItems as $masterID => $spec) {
                        if (array_key_exists($masterID, $mainData)) {

                            $hasil = "";
                            $sub_tmpOutstandingPair = 0;
                            foreach ($spec as $dSpec) {
                                $addSpec = "";
                                if (isset($arrayItemsMasterToID[$masterID])) {
                                    $pairTrID = $arrayItemsMasterToID[$masterID];
                                    $tmpOutstandingPair = isset($arrayItemsOutstandingPair[$pairTrID][$dSpec['produk_id']]) ? $arrayItemsOutstandingPair[$pairTrID][$dSpec['produk_id']] : "0";
                                    $sub_tmpOutstandingPair += ($tmpOutstandingPair * $dSpec['valid_qty']);

                                    $price = number_format($tmpOutstandingPair);
                                    $sum_price = number_format($tmpOutstandingPair * $dSpec['valid_qty']);
                                    $addSpec = "<span style='width:200px;' class='pull-right text-right'>@ $price  |  $sum_price</span>";
                                }

                                if ($hasil == "") {
                                    $hasil = $dSpec['valid_qty'] . "x " . $dSpec['label'] . " " . $dSpec['kode'] . $addSpec;
                                }
                                else {
                                    $hasil .= "<br>" . $dSpec['valid_qty'] . "x " . $dSpec['label'] . " " . $dSpec['kode'] . $addSpec;
                                }
                            }
                            //                            cekHere($hasil);
                            $mainData[$masterID]['outstanding_items'] = $hasil;
                            $mainData[$masterID]['sub_outstanding_items'] = formatField("debet", $sub_tmpOutstandingPair);
                            if (!isset($summary['sub_outstanding_items'])) {
                                $summary['sub_outstanding_items'] = 0;
                            }
                            $summary['sub_outstanding_items'] += $sub_tmpOutstandingPair;
                        }
                    }
                }
            }
        }
        else {
            cekmerah("TIDAK ada yang mau diekstrak");
        }
        //endregion


        //region header label
        $headerFieldLabel = array();
        if (isset($historyFields[$currentState]['fields'])) {
            foreach ($historyFields[$currentState]['fields'] as $srcKey => $alias) {
                if (is_array($alias)) {
                    $headerFieldLabel[$srcKey] = isset($alias['label']) ? $alias['label'] : "-";
                }
                else {
                    $headerFieldLabel[$srcKey] = $alias;
                }
            }
        }

        //endregion
        $addLink = null;
        $data = array(
            "mode" => $this->uri->segment(3),
            "isMobile" => $isMob,
            "jenisTr" => $jenisTr,
            "trName" => $this->configUi[$jenisTr]["label"],
            "errMsg" => $this->session->errMsg,
            "title" => isset($subCodes) && isset($currentState) ? $subCodes[$currentState] : $this->jenisTrName,
            //            "subTitle" => "<b> Out Standing $menuLabel <r>( periode " . (isset($date1) && isset($date2) ? date("d-M-Y", strtotime($date1)) . " s/d " . date("d-M-Y", strtotime($date2)) : "HINGGA HARI INI") . " )</r></b> ",
            "subTitle" => "<b> Out Standing $menuLabel",
            "arrayHistoryLabels" => $headerFieldLabel,
            "arrayHistory" => $mainData,
            "arrayAction" => $arrayAction,
            "arrayHistorySumField" => array(),
            "detailsFields" => $itemData,
            "arrayHistoryId" => array(),
            "action" => array(),
            "steps" => $historyFields,
            "arrayValid" => $arrayValid,
            "stepLabels" => $stepLabels,
            "stepLinks" => $stepLinks,
            "addParams" => isset($_GET['addParams']) ? $_GET['addParams'] : null,
            "currentState" => isset($currentState) ? $currentState : "all states",
            "alternateLink" => MODUL_PATH . $this->uri->segment(2) . $this->uri->segment(3) . "/" . $this->jenisTr . "/viewIncomplete/",
            "alternateLinkCaption" => "incomplete " . $this->configUi[$jenisTr]["label"] . " <span class='glyphicon glyphicon-arrow-right'></span>",
            "addLink" => $addLink,
            "filters" => array(
                "dates" => $this->dates,
                "date1" => $date1,
                "date2" => $date2,
            ),
            "thisPage" => MODUL_PATH . "/" . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->jenisTr . "/" . $this->uri->segment(5),
            "marking_style" => $marking_style,
            "summary" => $summary,
        );

        $this->load->view("history", $data);
    }

    public function viewStatus()
    {
        if (!isset($this->session->login['id'])) {
            redirect(base_url() . "Login");
        }
        $limit = 20;
        $maxPageNum = 20;
        $jenisTr = $this->jenisTr;
        $cCode = $this->cCode;
        $historyFields = isset($this->configUi[$jenisTr]['shortStatusFields']) ? $this->configUi[$jenisTr]['shortStatusFields'] : array();
        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
        $pairStepCode = isset($this->configUi[$jenisTr]['steps']) ? $this->configUi[$jenisTr]['steps'] : array();
        $addMainStep = isset($this->configUi[$jenisTr]['addMainStep']) ? $this->configUi[$jenisTr]['addMainStep'] : array();
        $regulerRoutesConfig = $this->config->item("heTransaksi_regulerRoutes") != NULL ? $this->config->item("heTransaksi_regulerRoutes") : array();
        $sourceInternalConnectConfig = $this->config->item("heTransaksi_source_internal_connect") != NULL ? $this->config->item("heTransaksi_source_internal_connect") : array();
        $sourceCenterConnectConfig = $this->config->item("heTransaksi_center_connect") != NULL ? $this->config->item("heTransaksi_center_connect") : array();


        $jenisTrConnect = heGetOriginConnectTCode($jenisTr);
        $jenisTrOriginConnect = heGetOriginTCode($jenisTr);
        $arrJenisTrMain = array($jenisTr);
        // ini mencari target koneknya
        if ($jenisTrConnect != NULL) {
            $arrJenisTrMain[] = $jenisTrConnect;
        }
        // ini mencari sumber koneknya
        if ($jenisTrOriginConnect != NULL) {
            if (!in_array($jenisTrOriginConnect, $sourceInternalConnectConfig)) {
                $arrJenisTrMain[] = $jenisTrOriginConnect;
            }
        }


        $mb = New MobileDetect();
        $isMob = $mb->isMobile();
        if ($isMob) {
            $historyFields = isset($this->configUi[$jenisTr]['shortStatusFields']) ? $this->configUi[$jenisTr]['compactHistoryFields'] : array();
        }


        $backdate_f = formatTanggal(backDate(30), 'Y-m-d');
        $date1 = isset($_GET['date1']) ? $_GET['date1'] : $backdate_f;
        $date2 = isset($_GET['date2']) ? $_GET['date2'] : date("Y-m-d");
        $tmpCode = array();
        if (sizeof($pairStepCode) > 0) {
            foreach ($pairStepCode as $t => $tSpec) {
                $tmpCode[$t] = $tSpec['target'];
            }
        }
        if (sizeof($addMainStep) > 0) {
            foreach ($addMainStep as $t => $tSpec) {
                $tmpCode[$t] = $tSpec['target'];
            }
        }

        $this->load->model("MdlTransaksi");
        $this->load->model("Coms/ComJurnal_activityMain");


        //region lookup histories
        $tr = new MdlTransaksi();
        $searchStr = isset($_GET['search']) ? $_GET['search'] : "";


        // kolom yang diambil dari tabel transaksi...
        $arrKolomTr = array(
            "id",
            "dtime",
            "nomer_top",
            "nomer",
            "jenis",
            "jenis_master",
            "jenis_top",
            "jenis_label",
            "oleh_id",
            "oleh_nama",
            "customers_id",
            "customers_nama",
            "suppliers_id",
            "suppliers_nama",
            "cabang_id",
            "cabang_nama",
            "cabang2_id",
            "cabang2_nama",
        );
        $arrJurnalStep = array();
        if (sizeof($regulerRoutesConfig) > 0) {
            foreach ($regulerRoutesConfig as $jenisMaster => $jSpec) {
                foreach ($jSpec as $step => $sSpec) {
                    $arrJurnalStep[$jenisMaster][$step] = $sSpec["debet"];
                }
            }
        }


        $cj = New ComJurnal_activityMain();
        $cj->setFilters(array());
        if ($this->session->login['cabang_id'] == CB_ID_PUSAT) {
        }
        else {
            $cabang_id = $this->session->login['cabang_id'];
            $cabang_pusat = CB_ID_PUSAT;
            //            cekPink("origin konek $jenisTrOriginConnect");
            //            $this->debugArrPrint($sourceCenterConnectConfig);
            if (in_array($jenisTrOriginConnect, $sourceCenterConnectConfig)) {
                //                cekPink("transaksi yang ada koneksi dengan PUSAT");
                $where = "((cabang_id='$cabang_id' AND cabang2_id='$cabang_pusat') OR (cabang_id='$cabang_pusat' AND cabang2_id='$cabang_id'))";
            }
            elseif (in_array($jenisTr, $sourceCenterConnectConfig)) {
                //                cekPink("transaksi yang ada koneksi dengan PUSAT");
                $where = "((cabang_id='$cabang_id' AND cabang2_id='$cabang_pusat') OR (cabang_id='$cabang_pusat' AND cabang2_id='$cabang_id'))";
            }
            else {
                //                cekPink("transaksi yang TIDAK ada koneksi dengan PUSAT");
                $where = "((cabang_id='$cabang_id' AND cabang2_id='$cabang_id') OR (cabang_id='$cabang_id' AND cabang2_id='$cabang_id'))";
            }

            $this->db->where($where);
        }

        if (!isset($_GET['date1']) && !isset($_GET['date2'])) {

            $subTitle_date = "";
            $subSubTitle_date = "";
        }
        else {
            $date1 = isset($_GET['date1']) ? $_GET['date1'] : date("Y-m-01");
            $date2 = isset($_GET['date2']) ? $_GET['date2'] : date("Y-m-d");

            $this->db->where("fulldate>='" . $date1 . "'");
            $this->db->where("fulldate<='" . $date2 . "'");

            $subTitle_date = lgTranslateTime($date1) . " - " . lgTranslateTime($date2);
            $subSubTitle_date = "";
        }

        //        arrprint($arrJenisTrMain);
        $cj->addFilter("jenis_master in ('" . implode("','", $arrJenisTrMain) . "')");
        $tmp = $cj->lookupAll()->result();
        //        $this->debugShowLastQuery("biru");
        //        cekHitam(sizeof($tmp));

        $tmpHistory = array();
        $tmpHistory_0 = array();
        $tmpHistory_waiting = array();
        $tmpHistory_activity = array();
        $tmpHistory_activityData = array();
        $trIDs = array();
        if (sizeof($tmp) > 0) {
            foreach ($tmp as $tmpSpec) {


                $tmpHistory_0[$tmpSpec->master_id][] = $tmpSpec;
                $tmpHistory_activity[$tmpSpec->master_id][] = $tmpSpec->activity;
                $tmpHistory_activityData[$tmpSpec->master_id][$tmpSpec->activity] = array(
                    "activity" => $tmpSpec->activity,
                    "master_id" => $tmpSpec->master_id,
                    "transaksi_no" => isset($tmpSpec->transaksi_no) ? $tmpSpec->transaksi_no : "",
                    "dtime" => $tmpSpec->dtime,
                    "fulldate" => $tmpSpec->fulldate,
                    "oleh_nama" => isset($tmpSpec->oleh_nama) ? $tmpSpec->oleh_nama : "",
                );

                $trIDs[$tmpSpec->master_id] = $tmpSpec->master_id;
                if ($tmpSpec->kredit > 0) {
                    if ($tmpSpec->activity != "request") {

                        $tmpHistory_waiting[$tmpSpec->master_id][] = $tmpSpec->activity;
                    }
                }
            }


            // pair dengan transaksi
            $tr = New MdlTransaksi();
            $tr->addFilter("id in ('" . implode("','", $trIDs) . "')");
            $tmpTr = $tr->lookupAll()->result();
            //            // $this->debugShowLastQuery("kuning");
            foreach ($tmpTr as $trSpec) {
                foreach ($arrKolomTr as $key) {
                    $subTr[$key] = isset($trSpec->$key) ? $trSpec->$key : "";
                }
                $tmpHistory[$trSpec->id] = (object)$subTr;
            }

            // pair dengan registry main
            $treg = New MdlTransaksi();
            $treg->setFilters(array());
            $treg->addFilter("transaksi_id in ('" . implode("','", $trIDs) . "')");
            // $treg->addFilter("param='main'");
            $treg->setJointSelectFields("main,transaksi_id");
            $tmpReg = $treg->lookupDataRegistries()->result();
            $pairRegistriesResult = array();
            if (sizeof($tmpReg) > 0) {
                foreach ($tmpReg as $regRow) {
                    $pairRegistriesResult[$regRow->transaksi_id] = blobDecode($regRow->main);
                }
            }
        }


        $tmpHist = $tmpHistory;
        $arrayHistory = array();
        $arrayHistory_ids = array();
        $arrayHistory_topID = array();
        $arrayHistoryCanceled = array();
        $arrayHistoryDone = array();
        $arrayHistoryStatus = array();
        if (sizeof($tmpHist) > 0) {
            krsort($tmpHist);
            foreach ($tmpHist as $row) {
                if (isset($pairRegistriesResult[$row->id])) {
                    foreach ($pairRegistriesResult[$row->id] as $k => $v) {
                        if (!isset($row->$k)) {
                            $row->$k = $v;
                        }
                    }
                }

                $tmp = array();
                foreach ($historyFields as $fName => $fLabel) {
                    if (strpos($fName, '+') !== false) {
                        //==mengandung penggabungan (+)
                        $chars = explode("+", $fName);
                        $colValue = "";
                        foreach ($chars as $key) {
                            $colValue .= isset($row->$key) ? formatField_he_format($key, $row->$key) . "<br>" : "";
                        }
                        $colValue = rtrim($colValue, "<br>");
                    }
                    else {
                        $colValue = isset($row->$fName) ? formatField_he_format($fName, $row->$fName) : "";
                    }
                    $tmp[$fName] = $colValue;
                    //                    $tmp1["id"] = $row->id;
                }

                $disabled = "";
                $arrActivity = array();
                $arrWaiting = array();

                if (isset($tmpHistory_activity[$row->id])) {
                    $arrActivity = $tmpHistory_activity[$row->id];
                }
                if (isset($tmpHistory_waiting[$row->id])) {
                    $arrWaiting = $tmpHistory_waiting[$row->id];
                }


                $status_next = "";
                if (isset($arrJurnalStep[$row->jenis_master])) {
                    foreach ($arrJurnalStep[$row->jenis_master] as $number => $value) {
                        $value_add = "";
                        $onclick = "";

                        if (in_array($value, $arrActivity)) {
                            if (in_array($value, $arrWaiting)) {
                                $disabled = "";
                                $class = "btn-warning";
                            }
                            else {

                                $dtime = $tmpHistory_activityData[$row->id][$value]['dtime'];
                                $oleh_nama = $tmpHistory_activityData[$row->id][$value]['oleh_nama'];
                                $transaksi_no = $tmpHistory_activityData[$row->id][$value]['transaksi_no'];
                                $transaksi_no_f = formatNota("nomer", $transaksi_no);

                                $disabled = "";
                                $class = "btn-success";
                                $value_add = " (by $oleh_nama, $transaksi_no_f, $dtime)";
                                $onclick = "onclick=\"showModal('" . base_url() . "Transaksi/viewResume/$transaksi_no','view resume for $transaksi_no_f')\"";
                            }
                        }
                        else {
                            $disabled = "";
                            $class = "btn-default";
                        }

                        $value_add = "";
                        if ($status_next == "") {
                            $status_next = "<input type='button' class='btn btn-sm $class' $disabled value='$value $value_add'
                                    $onclick
                                    >";
                            $status_next .= "<br>";
                        }
                        else {
                            $status_next = $status_next;
                            $status_next .= "<input type='button' class='btn btn-sm $class' $disabled value='$value $value_add'
                                    $onclick
                                    >";
                            $status_next .= "<br>";
                        }
                    }
                }
                $tmp['status_next'] = $status_next;


                $arrayHistory[$row->id] = $tmp;
                $arrayHistoryStatus[$row->id] = str_replace("<br>", "&nbsp;", $status_next);


            }

        }
        //endregion


        //region prepare params for viewer
        $trName = isset($this->configUi[$jenisTr]["label"]) ? $this->configUi[$jenisTr]["label"] : "";
        $data = array(
            "mode" => $this->uri->segment(3),
            "isMobile" => $isMob,
            "jenisTr" => $jenisTr,
            "trName" => $this->configUi[$jenisTr]["label"],
            "errMsg" => $this->session->errMsg,
            "title" => isset($subCodes) && isset($currentState) ? $subCodes[$currentState] : $this->jenisTrName,
            "subTitle" => "transaction status ",
            "arrayHistoryLabels" => $historyFields,
            "arrayHistory" => $arrayHistory,
            "arrayHistoryId" => $arrayHistory_ids,
            "arrayHistoryCanceled" => $arrayHistoryCanceled,
            "action" => isset($action) && sizeof($action) > 0 ? $action : array(),
            "steps" => isset($steps) && sizeof($steps) > 0 ? $steps : array(),
            "stepLabels" => isset($stepLabels) && sizeof($stepLabels) > 0 ? $stepLabels : array(),
            "stepLinks" => isset($stepLinks) && sizeof($stepLinks) > 0 ? $stepLinks : array(),
            "addParams" => isset($_GET['addParams']) ? $_GET['addParams'] : null,
            "currentState" => isset($currentState) ? $currentState : "all states",
            "alternateLink" => MODUL_PATH . $this->uri->segment(2) . "/viewIncomplete/" . $this->uri->segment(3) . "/" . $this->jenisTr,
            "alternateLinkCaption" => "incomplete " . $this->configUi[$jenisTr]["label"] . " <span class='glyphicon glyphicon-arrow-right'></span>",
            "addLink" => isset($addLink) ? $addLink : "",
            "filters" => array(
                "dates" => $this->dates,
                "date1" => $date1,
                "date2" => $date2,
            ),
            "thisPage" => MODUL_PATH . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->uri->segment(4) . "/" . $this->uri->segment(5),
            "arrayHistoryStatus" => $arrayHistoryStatus,
            "customStatusLayout" => true,
            //            "customStatusLayout" => false,
        );
        //endregion


        $this->load->view("history", $data);
    }

    public function index()
    {
        $starttime = microtime(true);
        if (!isset($this->session->login['id'])) {
            redirect(base_url() . "Login");
        }

        $sesionReplacer = replaceSession();
        $jenisTr = $this->jenisTr;
        $cCode = "_TR_" . $this->jenisTr;
        $paymentConfig = isset($this->configUi[$jenisTr]['paymentConfig']) ? $this->configUi[$jenisTr]['paymentConfig'] : false;
        $historyFields = isset($this->configUi[$jenisTr]['shortHistoryFields']) ? $this->configUi[$jenisTr]['shortHistoryFields'] : array();
        $kepoinFields = isset($this->configUi[$jenisTr]['shortKepoinFields']) ? $this->configUi[$jenisTr]['shortKepoinFields'] : array();
        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
        $shoppingCartPerTransaksi = isset($this->configUi[$jenisTr]['shoppingCartPerTransaksi']) ? $this->configUi[$jenisTr]['shoppingCartPerTransaksi'] : 0;
        $shoppingCartPerTransaksiBtn = isset($this->configUi[$jenisTr]['shoppingCartPerTransaksiBtn']) ? $this->configUi[$jenisTr]['shoppingCartPerTransaksiBtn'] : 0;
        $connectTo = isset($this->configUi[$jenisTr]['connectTo']) ? $this->configUi[$jenisTr]['connectTo'] : "";
        $steps = $this->configUi[$jenisTr]['steps'];
        //region link to add new transaction

        //tambahin hak akses custom

        if (placeCanMakeTrans_he_menu($this->session->login['membership'], $this->session->login['cabang_id'], $this->session->login['gudang_id'], $this->jenisTr, $this->configUiJenis)) {
            //        if (in_array($this->configUi[$jenisTr]["steps"][1]['userGroup'], $this->session->login['membership'])) {
            $createIndexes = (null != $this->config->item("transaksi_createIndex")) ? $this->config->item("transaksi_createIndex") : array();
            $isDisableMakeTrans = isset($this->configUi[$this->jenisTr]['isDisableMakeTrans']) ? $this->configUi[$this->jenisTr]['isDisableMakeTrans'] : false;
            if (array_key_exists($this->jenisTr, $createIndexes)) {
                $targetUrl = MODUL_PATH . $createIndexes[$this->jenisTr] . "/" . $this->jenisTr;
            }
            else {
                $targetUrl = MODUL_PATH . "Create/index/" . $this->jenisTr;
            }

            if ($isDisableMakeTrans) {
                $addLink = null;
                //                $_SESSION['errMsg'] = "test error dari 1982";
                //                $this->debugArrPrint( $this->session->errMsg );
            }
            else {
                $addLink = array(
                    "link" => $targetUrl,
                    "label" => "<span class='glyphicon glyphicon-plus'></span> create new " . $this->configUi[$jenisTr]["steps"][1]['label'],
                );
            }

        }
        else {
            $addLink = null;
        }
        //endregion

        $getStep = isset($_GET['step']) ? $_GET['step'] : 1;
        $baseUrl = base_url();
        if ($jenisTr == "110") {
            $link_undoneList = MODUL_PATH . "Transaksi/viewUndoneItemsIndex2/$jenisTr/?gr=cGVtYmVsaWFu&ohyes=ohno&step=$getStep";
        }
        else {
            $link_undoneList = MODUL_PATH . "Transaksi/viewUndoneItemsIndex/$jenisTr/?gr=cGVtYmVsaWFu&ohyes=ohno&step=$getStep";
        }
        $link_historyList = MODUL_PATH . "History/showData/$jenisTr/?limit=10";

        $scriptBottom = "";
        $scriptBottom .= "<script>
             var viewundoneList = function () {
                         top.$('#undoneList').html(\"<div class='row' style='margin-top: 14px; padding-bottom: 14px;'> <span class='col-lg-12 text-center'><img width='5%' src='$baseUrl/assets/images/loading_16_p.gif'></span> </div>\");
                     setTimeout(function () {
                                      if (top.$('#undoneList').find('img').length > 0) {
                                          top.$('#undoneList').load('$link_undoneList', function(){viewundoneList})
                                          }
                                      },1000);
                     }\n
                     viewundoneList()
\n\n
                    var viewhistoryList = function () {
                        top.$('#historyList').html(\"<div class='row' style='margin-top: 14px; padding-bottom: 14px;'> <span class='col-lg-12 text-center'><img width='5%' src='$baseUrl/assets/images/loading_16_p.gif'></span> </div>\");
                        setTimeout(function () {
                              if (top.$('#historyList').find('img').length > 0) {
                                  top.$('#historyList').load('$link_historyList', function(){viewhistoryList})
                         }
                        },1000);
                     }\n
                     viewhistoryList()\n
                     
         </script>
        ";
        //region prepare params to viewer
        $data = array(
            "mode" => "index",
            // "isMobile"                       => $isMob,
            "errMsg" => $this->session->errMsg,
            "template" => $this->configUi[$jenisTr]["template"],
            "title" => $this->configUi[$jenisTr]["label"],
            "subTitle" => $this->configUi[$jenisTr]["steps"][1]['label'],
            "jenisTr" => $jenisTr,
            "trName" => $this->configUi[$jenisTr]["label"],
            'addLink' => $addLink,

            "scriptBotto" => isset($scriptBottom) ? $scriptBottom : "",
            "steps" => $steps,
            "allSteps" => $this->allSteps,
        );
        //endregion


        $this->load->view("transaksi", $data);
    }

    public function selectPaymentExternSrc()
    {
        $targetJenis = $this->uri->segment(3);

        heInitCart($targetJenis);


        //==dapatkan srcJenis
        $paymentSources = null != ($this->config->item("payment_source")) ? $this->config->item("payment_source") : array();
        $readerDueDate = isset($this->configUi[$targetJenis]['dueDateReader']) ? $this->configUi[$targetJenis]['dueDateReader'] : false;

        if (sizeof($paymentSources) > 0) {
            foreach ($paymentSources as $src => $mainSpecs) {
                //                cekHere("$src");;
                if (sizeof($mainSpecs) > 0) {

                    foreach ($mainSpecs as $step => $sSpecTemp) {
                        //                        cekHere("$step");
                        //                        arrprint($sSpecTemp);
                        $sCtr = 0;
                        foreach ($sSpecTemp as $sSpec) {

                            //                            $this->debugArrPrint($sSpec);
                            //                            cekHitam($src);
                            if (isset($sSpec['jenisTarget']) && $sSpec['jenisTarget'] == $targetJenis) {
                                $srcJenis = $sSpec['jenisSrc'];
                                $rawSrcJenis = $src;
                                $srcIndex = $sCtr;
                                $srcStep = $step;
                                //                            cekhijau($srcJenis . " memenuhi syarat");
                            }
                            else {
                                $srcJenis = isset($sSpec['jenisSrc']) ? $sSpec['jenisSrc'] : "";
                                $rawSrcJenis = "";
                                $step = "";
                                $srcIndex = "";
                                $srcStep = "";
                                //                            cekmerah($srcJenis . " TIDAK memenuhi syarat");
                            }
                        }
                        $sCtr++;

                    }
                }
            }
        }

        //        $this->debugArrPrint($paymentSources[$rawSrcJenis][$srcStep][$srcIndex]);
        //cekHitam("$rawSrcJenis |$srcStep| $srcIndex");
        //        die();
        $defItemLabels = array(
            "extern_nama" => isset($paymentSources[$rawSrcJenis][$srcStep][$srcIndex]['externSrc']['extLabel']) ? $paymentSources[$rawSrcJenis][$srcStep][$srcIndex]['externSrc']['extLabel'] : "",
            //                "nomer"=>"receipt number",
            //                "fulldate"=>"date",
            "tagihan" => "due amount",
            "terbayar" => "paid",
            "diskon" => "discount",
            "sisa" => "due remain",
            "tagihan_valas" => "due amount",
            "terbayar_valas" => "paid",
            "diskon_valas" => "discount",
            "sisa_valas" => "due remain",
        );
        $itemLabels = (isset($this->configUi[$targetJenis]["shoppingCartReferenceExternFields"])) ? $this->configUi[$targetJenis]["shoppingCartReferenceExternFields"] : $defItemLabels;
        $itemLabels["due_date"] = "due date";
        $itemLabels["aging"] = "aging(days)";
        $itemLabels["over_due"] = "over due";

        //		die();
        //==dapatkan daftar kolom dari srcJenis
        //        $historyFields = $this->configUi[$srcJenis]['shortHistoryFields'];

        $tr = new MdlTransaksi();
        $tr->setFilters(array());
        $tr->addFilter("transaksi_payment_source.cabang_id='" . $this->session->login['cabang_id'] . "'");
        $tr->addFilter("sisa>0");
        $tmpSrc = $tr->lookupPaymentSrcByJenis($targetJenis)->result();
        //        cekHere($this->db->last_query());
        $tmpSrcDue = array();
        $dueEmployee = array();
        if ($readerDueDate) {
            $tr->setFilters(array());
            $tr->addFilter("status='1'");
            $tmpSrcDue = $tr->lookupAllDueDate()->result();
            $tempDataDues = array();
            $validDate = array();
            foreach ($tmpSrcDue as $tmpSrcDue_tmp) {
                $tempDataDues[$tmpSrcDue_tmp->customers_id][] = array(
                    "due_date" => $tmpSrcDue_tmp->due_date,
                    "aging_dtime" => $tmpSrcDue_tmp->dtime,
                );

            }
            //            $this->debugArrPrint($tempDataDues);
            $dtime_now = strtotime(date("Y-m-d"));
            foreach ($tempDataDues as $cus_id => $tempDataDues_0) {
                $dueVal = array();
                $dtimeVal = array();
                foreach ($tempDataDues_0 as $dtime_val) {

                    $keyIndex = strtotime($dtime_val['due_date']);
                    $dueVal[] = $keyIndex;
                    $dtimeVal[$keyIndex] = array(
                        "due_date" => $dtime_val['due_date'],
                        "aging" => $dtime_val['aging_dtime'],
                    );

                }
                asort($dueVal);
                $key_index = $dueVal['0'];
                $date_due = $dtimeVal[$key_index]['due_date'];
                $aging = $dtimeVal[$key_index]['aging'];

                //cekHere("$date_due || $aging");
                if ($dtime_now > $key_index) {
                    $dueEmployee[$cus_id] = array(
                        "due_date" => formatField_he_format("dtime", $date_due),
                        "over_due" => umurDay($date_due) > 0 ? umurDay($date_due) : "0",
                        "aging" => umurDay($aging) > 0 ? umurDay($aging) : "0",
                    );
                }
            }


        }
        //matiHere();
        // cekHere($targetJenis);
        //        $this->debugArrPrint($dueEmployee);
        $items = array();
        $externs = array();
        $tagihans = array();
        $terbayars = array();
        $sisas = array();
        $diskons = array();
        $tagihans_valas = array();
        $terbayars_valas = array();
        $sisas_valas = array();
        $diskons_valas = array();
        $srcLabel = "";
        if (sizeof($tmpSrc) > 0) {
            foreach ($tmpSrc as $row) {
                $tmp = array();
                //                foreach($historyFields as $fName=>$label){
                //                    $tmp[$fName]=$row->$fName;
                //                }
                $classMarking = "";
                if (isset($dueEmployee[$row->extern_id])) {
                    $classMarking = "bg-danger";
                    //                    cekHere($row->extern_nama);
                }

                if (!in_array($row->extern_id, $externs)) {
                    if (!isset($tagihans[$row->extern_id])) {
                        $tagihans[$row->extern_id] = 0;
                        $terbayars[$row->extern_id] = 0;
                        $diskons[$row->extern_id] = 0;
                        $sisas[$row->extern_id] = 0;
                        $tagihans_valas[$row->extern_id] = 0;
                        $terbayars_valas[$row->extern_id] = 0;
                        $sisas_valas[$row->extern_id] = 0;
                        $diskons_valas[$row->extern_id] = 0;

                    }

                    $tmp = (array)$row;
                    $tmp["link"] = base_url() . get_class($this) . "/selectPaymentSrc/$targetJenis/" . $row->extern_id;
                    $tmp["due_date"] = isset($dueEmployee[$row->extern_id]['due_date']) ? formatField_he_format("dtime", $dueEmployee[$row->extern_id]['due_date']) : "-";
                    $tmp["aging"] = isset($dueEmployee[$row->extern_id]['aging']) ? $dueEmployee[$row->extern_id]['aging'] : "-";
                    $tmp["over_due"] = isset($dueEmployee[$row->extern_id]['over_due']) ? $dueEmployee[$row->extern_id]['over_due'] : "-";
                    $tmp["class_marking"] = $classMarking;
                    //                    $tmp["class_marking"] = "bg-danger";
                    //                    $tmp["tagihan"] = $tagihans[$row->extern_id];
                    $items[$row->extern_id] = $tmp;
                    $externs[] = $row->extern_id;
                    $externName = $row->extern_nama;
                }
                $tagihans[$row->extern_id] += isset($row->tagihan) ? $row->tagihan : 0;
                $terbayars[$row->extern_id] += isset($row->terbayar) ? $row->terbayar : 0;
                $sisas[$row->extern_id] += isset($row->sisa) ? $row->sisa : 0;
                $diskons[$row->extern_id] += isset($row->diskon) ? $row->diskon : 0;
                $tagihans_valas[$row->extern_id] += isset($row->tagihan_valas) ? $row->tagihan_valas : 0;
                $terbayars_valas[$row->extern_id] += isset($row->terbayar_valas) ? $row->terbayar_valas : 0;
                $sisas_valas[$row->extern_id] += isset($row->sisa_valas) ? $row->sisa_valas : 0;
                $diskons_valas[$row->extern_id] += isset($row->diskon_valas) ? $row->diskon_valas : 0;
                $srcLabel = $row->label;
            }
            //            $this->debugArrPrint($items);
            foreach ($items as $externID => $iSpec) {
                $items[$externID]['tagihan'] = $tagihans[$externID];
                $items[$externID]['terbayar'] = $terbayars[$externID];
                $items[$externID]['diskon'] = $diskons[$externID];
                $items[$externID]['sisa'] = $sisas[$externID];
                $items[$externID]['tagihan_valas'] = $tagihans_valas[$externID];
                $items[$externID]['terbayar_valas'] = $terbayars_valas[$externID];
                $items[$externID]['diskon_valas'] = $diskons_valas[$externID];
                $items[$externID]['sisa_valas'] = $sisas_valas[$externID];
            }
        }

        //        cekHitam("$srcStep||$srcIndex");
        //		cekkuning("rawSrcJenis: ".$rawSrcJenis);
        //        $this->debugArrPrint($this->uri->segment_array());
        $data = array(
            "mode" => $this->uri->segment(2),
            //            "template" => $this->configUi[$jenisTr]["template"],
            "title" => $this->configUi[$targetJenis]["label"],
            "subTitle" => isset($paymentSources[$rawSrcJenis][$srcStep][$srcIndex]['externSrc']['extLabel']) ? "select " . $paymentSources[$rawSrcJenis][$srcStep][$srcIndex]['externSrc']['extLabel'] . "(s) listed below" : "",
            "items" => $items,
            "srcLabel" => $srcLabel,
            "itemLabels" => $itemLabels,
            "jenisTr" => $this->jenisTr,
            //            "dueDate"    =>$dueEmployee,
        );
        //        arrprint($data);

        if (!isset($_GET['json'])) {
            $this->load->view("transaksi", $data);
        }
        else {
            //            echo json_encode($data);

            $this->debugArrPrint($data);
        }


        //        die("selecting payment src...");
    }

    public function selectPaymentSrc()
    {
        if (!isset($this->session->login['id'])) {
            gotoLogin();
        }
        $externID = $this->uri->segment(5);
        $selectedTrID = $this->uri->segment(6);

        $this->load->model("MdlTransaksi");

        // arrPrintPink($this->uri->segment_array());
        $scriptBottom = "";
        $scriptTrigger = "";
        // if(isset($selectedTrID)){
        $scriptTrigger .= "$('.chRadio').trigger('click')";
        // }

        $jenisTr = $targetJenis = $this->jenisTr;
        $cCode = $this->jenisTr;

        // cekHere("cCode:: $cCode " . __METHOD__ . " line " . __LINE__);
        heInitCart($targetJenis);
        $this->load->helper("he_versi_history_old");

        $detailResetList = array(
            "items",
            //            "out_detail",
            //            "out_detail2",
            "tableIn_detail",
            "tableIn_detail2",
            "tableIn_detail_values",
            "tableIn_detail2_sum",
            "tableIn_detail_values2_sum",
        );
        foreach ($detailResetList as $sSName) {
            $_SESSION[$cCode][$sSName] = null;
            unset($_SESSION[$cCode][$sSName]);
        }


        if (!isset($_SESSION[$cCode])) {
            $_SESSION[$cCode] = array(
                "items" => array(),
                "main" => array(),

            );
        }
        if (!isset($_SESSION[$cCode]['main'])) {
            $_SESSION[$cCode]['main'] = array();
        }
        if (!isset($_SESSION[$cCode]['items'])) {
            $_SESSION[$cCode]['items'] = array();
        }


        //==dapatkan srcJenis
        $stepCode = $this->configUi[$targetJenis]['steps'][1]['target'];
        $steps = $this->configUi[$targetJenis]['steps'];
        $tagihanSrc = isset($this->configUi[$targetJenis]['tagihanSrc']) ? $this->configUi[$targetJenis]['tagihanSrc'] : "sisa";
        $paymentSources = null != ($this->config->item("payment_source")) ? $this->config->item("payment_source") : array();
        $returnRoutes = null != ($this->config->item("transaksi_returnRoutes")) ? $this->config->item("transaksi_returnRoutes") : array();
        $readerDueDate = isset($this->configUi[$targetJenis]['dueDateReader']) ? $this->configUi[$targetJenis]['dueDateReader'] : false;
        $rawPrevURL = isset($_GET['rawPrev']) ? $_GET['rawPrev'] : "";
        $prePrint = isset($steps[1]['prePrint']) ? $steps[1]['prePrint'] : "";

        $ppnDisabledConfig = isset($this->configUi[$targetJenis]['ppnDisabled']) ? $this->configUi[$targetJenis]['ppnDisabled'] : array();

        $jenisSrc = null;

        //		arrprint($paymentSources);die();
        if (sizeof($paymentSources) > 0) {
            foreach ($paymentSources as $src => $sSpec) {
                $payConfigs = $paymentSources[$src];
                if (sizeof($payConfigs) > 0) {
                    //                    $sCtr = 0;
                    foreach ($payConfigs as $paymentSrcConfigTmp) {
                        $sCtr = 0;
                        foreach ($paymentSrcConfigTmp as $paymentSrcConfig) {
                            if (isset($paymentSrcConfig['jenisTarget']) && $paymentSrcConfig['jenisTarget'] == $targetJenis) {
                                $srcJenis = $paymentSrcConfig['jenisSrc'];
                                $rawSrcJenis = $src;
                                $srcIndex = $sCtr;
                                $jenisSrc = $srcJenis;
                            }
                            $sCtr++;
                        }

                    }
                }
            }
        }

        $references = array();
        if ($jenisSrc != null) {
            if (isset($returnRoutes[$jenisSrc])) {
                $retCode = $returnRoutes[$jenisSrc];
                $trr = new MdlTransaksi();
                $trr->setFilters(array());
                $trr->addFilter("param='main'");
                $trr->addFilter("jenis='$retCode'");
                $tmpR = $trr->lookupRegistries_joined()->result();
                //cekHere($this->db->last_query());

                if (sizeof($tmpR) > 0) {
                    foreach ($tmpR as $row) {
                        $main = blobDecode($row->values);
                        //                        arrprint($main);
                        $references[$main['referenceID']] = array(
                            "id" => $row->transaksi_id,
                            "nomer" => $row->nomer,
                            "refID" => $main['referenceID'],
                            "refJenis" => $main['referenceJenis'],
                            "refNum" => $main['referenceNomer'],
                            "harga" => isset($main['harga']) ? $main['harga'] : 0,
                            "nett" => isset($main['nett']) ? $main['nett'] : 0,


                        );
                    }
                }
            }
        }

        //        arrprint($references);
        $elementConfigs = isset($this->configUi[$this->jenisTr]['receiptElements']) ? $this->configUi[$this->jenisTr]['receiptElements'] : array();


        //==dapatkan daftar kolom dari srcJenis
        //        $historyFields = $this->configUi[$srcJenis]['shortHistoryFields'];

        $tr = new MdlTransaksi();
        $tr->setFilters(array());
        $tr->addFilter("transaksi_payment_source.cabang_id='" . $this->session->login['cabang_id'] . "'");
        $tr->addFilter("extern_id='$externID'");
        $tr->addFilter("sisa>0");

        if ($selectedTrID > 0) {
            $tr->addFilter("transaksi_id='$selectedTrID'");
        }
        $tmpSrc = $tr->lookupPaymentSrcByJenis_joined($targetJenis)->result();
        //        $tmpSrc = $tr->lookupPaymentSrcByJenis($targetJenis)->result();
        // cekKuning(__LINE__. " | " .$this->db->last_query());
        //        $this->debugArrPrint($tmpSrc);
        if ($readerDueDate) {
            $tr->setFilters(array());
            $tmpSrcDue = $tr->lookupAllDueDate()->result();
            //        $this->debugArrPrint($tmpSrcDue);
            if (sizeof($tmpSrcDue) > 0) {
                foreach ($tmpSrcDue as $tmpSrccDue_0) {
                    $mainDueDate[$tmpSrccDue_0->transaksi_id] = $tmpSrccDue_0->due_date;
                }
            }
        }

        $externName = "";
        $items = array();
        $tempDueDate = array();
        $ppnDisabled = array();
        if (sizeof($tmpSrc) > 0) {
            foreach ($tmpSrc as $row) {
                $tmp = array();
                //                foreach($historyFields as $fName=>$label){
                //                    $tmp[$fName]=$row->$fName;
                //                }
                $jenis = $row->jenis;
                $transaksi_id = $row->id;
                if ($readerDueDate) {
                    $tempDueDate = blobDecode($row->ids_prev);
                    $dueTime = array();
                    if (sizeof($tempDueDate) > 0) {
                        $time_due = array();
                        foreach ($tempDueDate as $k => $spdID) {
                            $time_due[] = strtotime($mainDueDate[$spdID]);
                            $dueTime[strtotime($mainDueDate[$spdID])] = $mainDueDate[$spdID];
                        }
                        asort($time_due);
                        $key_due = $time_due[0];
                    }
                    if ($key_due > 0) {
                        $date_now = strtotime(date("Y-m-d"));
                        $dateValid = strtotime($dueTime[$key_due]);
                        //                        cekHijau("$dateValid >$date_now");
                        if ($dateValid < $date_now) {
                            //                            cekHere("$dateValid >$date_now");
                            $tmp['class_bg'] = "bg-danger";
                        }
                        else {
                            $tmp['class_bg'] = "";
                        }
                    }
                    else {
                        $tmp['class_bg'] = "";
                    }

                    $tmp['due_date'] = isset($dueTime[$key_due]) ? formatField_he_format("dtime", $dueTime[$key_due]) : "-";
                    $tmp['aging'] = isset($dueTime[$key_due]) && umurDay($dueTime[$key_due]) > 0 ? umurDay($dueTime[$key_due]) : "0";
                }
                else {
                    $tmp['class_bg'] = "";
                }


                $tmp['refID'] = array_key_exists($row->transaksi_id, $references) ? $references[$row->transaksi_id]['refID'] : "";
                $tmp['refNum'] = array_key_exists($row->transaksi_id, $references) ? $references[$row->transaksi_id]['refNum'] : "";
                $tmp['refValue'] = array_key_exists($row->transaksi_id, $references) ? $references[$row->transaksi_id]['nett'] : "";

                if (strlen($prePrint) > 10) {
                    $linkPrint = base_url() . "$prePrint/$jenis/$transaksi_id";
                    $tmp['prePrint'] = "<button type='button' class='btn btn-link' onclick=\"window.open('$linkPrint')\"><i class='fa fa-print'></i></button>";
                }

                if (isset($ppnDisabledConfig['enabled']) && $ppnDisabledConfig['enabled'] == true) {
                    //                    if (($row->ppn_sisa > 0) && ($row->ppn_status == 1)) {
                    if (($row->ppn_status == 1)) {
                        $ppnDisabled[] = $row->transaksi_id;
                        $tmp['notes'] = isset($ppnDisabledConfig['notes']) ? "<span style='color:red;font-size:10px;'>" . $ppnDisabledConfig['notes'] . "</span>" : "-";
                    }
                }

                $items[] = (array)$row + $tmp;
                $externName = $row->extern_nama;
            }
        }
        //$this->debugArrPrint($items);
        $cCode = "_TR_" . $targetJenis;
        //$this->debugArrPrint($ppnDisabled);
        $defaultItemLabels = array(
            "extern_label2" => "jenis",
            "nomer" => "receipt number",
            "nomer_top" => "receipt ref.",
            "refNum" => "return ref.",
            "fulldate" => "date",
            "tagihan" => "due amount",
            "refValue" => "returned",
            "terbayar" => "paid",
            "diskon" => "discount",
            "sisa" => "due remain",
        );

        $jQueryCustom = isset($this->configUi[$this->jenisTr]['jQueryCustom']) ? $this->configUi[$this->jenisTr]['jQueryCustom'] : "";

        if (sizeof($jQueryCustom) > 0) {
            $scriptBottom .= $jQueryCustom;
        }


        $defSisa = isset($_SESSION[$cCode]['main']['sisa']) ? $_SESSION[$cCode]['main']['sisa'] : 0;
        $data = array(
            "mode" => $this->uri->segment(3),
            "title" => $this->configUi[$targetJenis]["label"],
            "subTitle" => $externName,
            "jenisTr" => $targetJenis,
            "items" => $items,
            "prePrint" => $prePrint,
            "itemLabels" => isset($this->configUi[$targetJenis]["shoppingCartReferenceFields"]) ? $this->configUi[$targetJenis]["shoppingCartReferenceFields"] : $defaultItemLabels,
            "selectProcessor" => $this->configUi[$targetJenis]["selectorProcessor"],
            "paymentSubtitle" => "details for " . $this->configUi[$this->jenisTr]['steps'][1]['label'],
            "btnLabel" => "continue " . $this->configUi[$this->jenisTr]['steps'][1]['label'],
            "ses_outMaster" => isset($_SESSION[$cCode]['main']) ? $_SESSION[$cCode]['main'] : array(),
            "ses_items" => isset($_SESSION[$cCode]['items']) ? $_SESSION[$cCode]['items'] : array(),
            // -----------
            "actionTarget" => "top.$('#result').load('" . MODUL_PATH . "Transaksi/validate/" . $targetJenis . "?rawPrev=$rawPrevURL');",
            "columnRecorderTarget" => base_url() . "ValueGate/recordColumn/" . $this->jenisTr . "/nilai_entry",
            "bankColumnRecorderTarget" => base_url() . "ValueGate/recordColumn/" . $this->jenisTr . "/paymentMethod_cash",
            // -------------------
            "selectedBankID" => isset($_SESSION[$cCode]['main']['cash_account']) ? $_SESSION[$cCode]['main']['cash_account'] : 0,
            "dueDateReader" => $readerDueDate,
            "tagihanSrc" => $tagihanSrc,
            "scriptBottom" => $scriptBottom,
            "tagihanValue" => isset($_SESSION[$cCode]['main'][$tagihanSrc]) ? $_SESSION[$cCode]['main'][$tagihanSrc] : $defSisa,
            "isPaymentRadioSelect" => isset($this->configUi[$this->jenisTr]['isPaymentRadioSelect']) ? $this->configUi[$this->jenisTr]['isPaymentRadioSelect'] : false,
            "ppnDisabled" => $ppnDisabled,
            "scriptTrigger" => $scriptTrigger,
        );

        if (!isset($_GET['json'])) {
            $this->load->view("transaksi", $data);
        }
        else {
            return json_encode($data);
        }

        //        die("selecting payment src...");
    }

    public function viewIncomplete()
    {


        if (!isset($this->session->login['id'])) {
            redirect(base_url() . "Login");
        }
        $jenisTr = $this->jenisTr;
        $cCode = $this->cCode;

        $historyFields = $this->configUi[$jenisTr]['shortHistoryFields'];
        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
        $mb = New MobileDetect();
        $isMob = $mb->isMobile();
        if ($isMob) {
            $historyFields = isset($this->configUi[$jenisTr]['compactHistoryFields']) ? $this->configUi[$jenisTr]['compactHistoryFields'] : array();
        }

        //
        //region lookup on-going transactions
        $currentState = 99;
        $progressFields = $historyFields;
        $progressFields['items'] = "detail items";
        $progressFields['state'] = "status";
        $progressFields['action'] = "action";
        $steps = $this->configUi[$jenisTr]['steps'];
        $stepLabels = array(
            "99" => "all",
            //            "0" => "canceled",
        );
        $date1 = isset($_GET['date1']) ? $_GET['date1'] : date("Y-m-01");
        $date2 = isset($_GET['date2']) ? $_GET['date2'] : date("Y-m-d");
        $searchStr = isset($_GET['search']) ? $_GET['search'] : "";
        $stepLinks = array(
            "99" => MODUL_PATH . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->jenisTr . "/99?date1=$date1&date2=$date2",
            //            "0"  => base_url() . $this->uri->segment(1) . "/" . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/0?date1=$date1&date2=$date2",
            "0" => MODUL_PATH . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->jenisTr . "/0",
        );


        $arrayOnprogress = array();
        $arrayOnprogressbyState = array();


        if (sizeof($steps) > 1) {
            $stepCodes = array();
            //$this->debugArrPrint($this->accessList);
            $jmlStep = count($steps);
            $indsteps = "(";
            $arrFilters = array();
            if (isset($this->accessList[$jenisTr]) && sizeof($this->accessList[$jenisTr]) > 0) {
                foreach ($this->accessList[$jenisTr] as $stepNumber => $stepSpec) {
                    if ($stepNumber <= $jmlStep) {
                        foreach ($stepSpec as $targetCode => $filters) {
                            $indsteps .= "'$targetCode',";
                            $stepCodes[] = $targetCode;
                            if ($filters['allowFollowUp'] == "true") {
                                $arrFilters["allowFollowUp"][] = $targetCode;
                            }

                        }
                    }
                }
            }

            $indsteps = rtrim($indsteps, ",");
            $indsteps .= ")";
            $stepLabels ["0"] = "canceled";

            if ($steps[1]['userGroup'] == $this->session->login['jenis']) {
                $stepLabels[-1] = "<span class='glyphicon glyphicon-plus'></span>";
                // $stepLinks[-1] = MODUL_PATH . $this->uri->segment(2) . "/createForm/" . $this->uri->segment(3) . "?date1=$date1&date2=$date2";
                $stepLinks[-1] = MODUL_PATH . "Create/index/" . $this->jenisTr . "?date1=$date1&date2=$date2";
            }


            //            $currentState = $this->uri->segment(4) > 0 ? $this->uri->segment(4) : 0;
            if (strlen($this->uri->segment(5)) > 0) {
                $currentState = $this->uri->segment(5);
            }


            $this->load->model("MdlTransaksi");
            $tr = new MdlTransaksi();
            $tr->addFilter("transaksi.oleh_id='" . $this->session->login['id'] . "'");
            //            $tr->addFilter("transaksi.cabang_id='" . $this->session->login['cabang_id'] . "'");
            //            $tr->addFilter("gudang_id='" . $this->session->login['gudang_id'] . "'");
            //            $tr->addFilter("jenis='" . $steps[1]['target'] . "'");
            $tr->addFilter("div_id='" . $this->session->login['div_id'] . "'");
            $tr->addFilter("jenis_top='" . $steps[1]['target'] . "'");

            //region date filter
            //            $date1 = isset($_GET['date1']) ? $_GET['date1'] : date("Y-m-01");
            //            $date2 = isset($_GET['date2']) ? $_GET['date2'] : date("Y-m-d");
            if (isset($_GET['date1'])) {
                $date1 = $this->getSafeDateParam('date1', date("Y-m-01"));
                $date2 = $this->getSafeDateParam('date2', date("Y-m-d"));
                $this->db->where("fulldate >=", $date1);
                $this->db->where("fulldate <=", $date2);
            }
            if (isset($_GET['search'])) {
                $tr->setKeyWord($searchStr);
            }

            //endregion

            //cekBiru($currentState);
            $tr->addFilter("next_substep_code<>''");
            if ($currentState < 99) {
                $tr->addFilter("sub_step_number='$currentState'");
                $tr->addFilter("valid_qty>0");
            }
            else {
                $tr->addFilter("sub_step_number>0");
                $tr->addFilter("valid_qty>0");
            }
            if (sizeof($arrFilters) > 0) {
                //                $tr->addFilter("next_step_code in $indsteps");
            }
            else {
                //                $tr->addFilter("transaksi.oleh_id='" . $this->session->login['id'] . "'");
            }

            //            $tmpHist = $tr->lookupUndoneEntries_joined($this->session->login['cabang_id'], $this->session->login['gudang_id'])->result();

            $tmpHist = $tr->lookupUndoneEntries_joined(replaceSession())->result();
            //            cekHere($this->db->last_query());
            //            $this->debugArrPrint($tmpHist);

            $selectedTopID = isset($_GET['topID']) ? $_GET['topID'] : 0;


            $rowCtr = 0;
            if (sizeof($tmpHist) > 0) {
                $arrTransID = array();
                $arrTransTopID = array();
                $arrIdsHist = array();
                $arrTransHist = array();
                $arrProduk = array();
                foreach ($tmpHist as $row) {
                    $arrTransID[] = $row->transaksi_id;
                    $arrTransTopID[] = $row->id_top;
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

                    $arrProduk[$row->transaksi_id] = array(
                        "id" => $row->produk_id,
                        "nama" => $row->produk_nama,
                        "label" => $row->produk_label,
                        "kode" => $row->produk_kode,
                        "valid_qty" => $row->valid_qty,
                        "master_id" => $row->id_master,
                    );
                }
                $tmpReg_result = array();
                $trReg = new MdlTransaksi();
                $trReg->setFilters(array());
                // $trReg->addFilter("param='main'");


                $trReg->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
                $trReg->setJointSelectFields("main,transaksi_id");
                // $tmpReg = $trReg->lookupRegistries()->result();
                $tmpReg = $trReg->lookupDataRegistries()->result();
                if (sizeof($tmpReg) > 0) {
                    foreach ($tmpReg as $regRow) {
                        $tmpReg_result[$regRow->transaksi_id]["main"] = blobDecode($regRow->main);
                        // $this->debugArrPrint($regRow);
                        // $tmpReg_result[$regRow->transaksi_id][$param] = blobDecode($regRow->values);
                    }
                }

// matiHEre();
                foreach ($tmpHist as $row) {
                    if (sizeof($pairRegistries) > 0) {
                        if ((sizeof($tmpReg_result) > 0) && (isset($tmpReg_result[$row->transaksi_id]))) {
                            foreach ($tmpReg_result[$row->transaksi_id] as $param => $eReg) {
                                foreach ($eReg as $k => $v) {
                                    if (!isset($row->$k)) {
                                        $row->$k = $v;
                                    }
                                }
                            }
                        }
                    }

                    $rowCtr++;
                    $tmp = array();
                    foreach ($historyFields as $fName => $fLabel) {

                        $tmp[$fName] = isset($row->$fName) ? formatField_he_format($fName, $row->$fName) : formatField_he_format($fName, 0);

                        if ($fName == "no") {
                            $tmp[$fName] = formatField_he_format($fName, $rowCtr);
                        }
                    }
                    // menambah manual detail/items barang
                    if (isset($arrProduk[$row->transaksi_id])) {
                        $tmp['items'] = $arrProduk[$row->transaksi_id]["valid_qty"] . "x ";
                        $tmp['items'] .= $arrProduk[$row->transaksi_id]["label"] . " ";
                        $tmp['items'] .= $arrProduk[$row->transaksi_id]["kode"];

                        $linkOutstanding = MODUL_PATH . "Transaksi/viewOutstanding/$jenisTr/transaksi/$jenisTr?trID=" . $arrProduk[$row->transaksi_id]["master_id"];
                        $tmp['items'] .= "<br><br> <a class='btn btn-primary btn-block' title='lihat selengkapnya' target='_blank'
                            href='$linkOutstanding'>
                            <span class=''>click to view complete...</span></a>";
                    }
                    else {
                        $tmp['items'] = "-";
                    }


                    if ($row->sub_step_number > 0) {
                        $tmp['state'] = "<span style='color:" . $this->configUi[$jenisTr]['steps'][$row->sub_step_number]['stateColor'] . "'>" . $this->configUi[$jenisTr]['steps'][$row->sub_step_number]['stateLabel'] . "</span>";
                        $tmp['state'] .= "<br>" . createStateSign($row->sub_step_number, $row->step_avail, $jenisTr);
                    }
                    else {
                        $tmp['state'] = "<span style='color:#777777'>canceled</span>";
                    }

                    $tmp['action'] = "";
                    $nextStepNum = ($row->next_substep_num);
                    $currentStepNum = ($row->sub_step_number);
                    $nextStepCode = $row->next_step_code;
                    $allowJoin = isset($this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin']) && $this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin'] == true ? $this->configUi[$jenisTr]["steps"][$nextStepNum]['allowJoin'] : false;
                    $allowFollowup = false;
                    if ($row->sub_step_number > 0) {

                        if (placeCanFollowupTrans($this->session->login['membership'], $this->session->login['cabang_id'], $this->session->login['gudang_id'], $this->jenisTr, $currentStepNum)) {
                            //                        if (in_array($this->configUi[$jenisTr]['steps'][$currentStepNum]['userGroup'], $this->session->login['membership'])) {
                            $allowFollowup = true;
                            $actionLabel = "review " . $this->configUi[$jenisTr]['steps'][$currentStepNum]['label'];

                        }
                    }

                    if (isset($this->configUi[$jenisTr]['steps'][$nextStepNum])) {
                        //                        cekHitam("masooox $nextStepNum ".$row->sub_step_number);
                        if (isset($this->accessList[$jenisTr])) {
                            if (isset($this->accessList[$jenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"])) {
                                $allowFollowup = $this->accessList[$jenisTr][$nextStepNum][$nextStepCode]["allowFollowUp"];
                                $actionLabel = $this->configUi[$jenisTr]['steps'][$nextStepNum]['actionLabel'];
                            }


                        }
                        else {
                            if (placeCanFollowupTrans($this->session->login['membership'], $this->session->login['cabang_id'], $this->session->login['gudang_id'], $this->jenisTr, $nextStepNum)) {
                                //                        if (in_array($this->configUi[$jenisTr]['steps'][$nextStepNum]['userGroup'], $this->session->login['membership'])) {
                                $allowFollowup = true;
                                $actionLabel = $this->configUi[$jenisTr]['steps'][$nextStepNum]['actionLabel'];

                            }
                        }

                    }

                    if ($allowFollowup) {
                        $stepLabel = $this->configUi[$jenisTr]['steps'][$nextStepNum]['label'];

                        $followupLink = "top.$('#result').load('" . MODUL_PATH . "FollowUp/followupPrePreview/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "');";
                        $tmp['action'] = "<a class='btn btn-primary btn-block' title='turn this entry into $stepLabel' href='javascript:void(0)' onClick =\"top.open_holdon();$followupLink\">" . $actionLabel . "</a>";
                    }


                    if ($currentState > 0) {
                        if (isset($tmp['jenis_label'])) {
                            $clickEvent = "";
                            if ($row->id_top == $selectedTopID) {
                                $checked = "checked";
                            }
                            else {
                                $clickEvent = "location.href='" . MODUL_PATH . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->uri->segment(4) . "?topID=" . $row->id_top . "';";
                                $checked = "";
                            }

                            $tmp['jenis_label'] = "<label><input type='checkbox' name='oID[]' $checked onclick=\"$clickEvent\" value='" . $row->transaksi_id . "'>" . $tmp['jenis_label'] . "</label>";
                            //                                echo "topID:".$row->id_top."<br>";
                        }
                    }

                    $arrayOnprogress[] = $tmp;
                    $arrayOnprogressbyState[$row->sub_step_number][] = $tmp;


                }
            }
        }
        else {
            $arrayOnprogress = array();
        }

        //$this->debugArrPrint($arrayOnprogress);

        //endregion

        //region link to add new transaction
        if (placeCanMakeTrans($this->session->login['membership'], $this->session->login['cabang_id'], $this->session->login['gudang_id'], $this->jenisTr)) {
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
        //$this->debugArrPrint($addLink);
        //
        //region prepare params for viewer
        $data = array(
            "mode" => $this->uri->segment(3),
            "isMobile" => $isMob,
            "errMsg" => $this->session->errMsg,
            "template" => $this->configUi[$jenisTr]["template"],
            "title" => "incomplete " . $this->configUi[$jenisTr]["label"],
            //            "subTitle"          => $this->configUi[$jenisTr]["label"] . " with status '" . $stepLabels[$currentState] . "'",
            "subTitle" => $stepLabels[$currentState] . "'",
            "jenisTr" => $jenisTr,
            "jenisTransaksi" => $jenisTr,
            "trName" => $this->configUi[$jenisTr]["label"],
            //            "selectorCaller" => base_url() . "_selectorItem/" . $this->configUi[$jenisTr]["selectorCaller"] . "/$jenisTr/" . $this->configUi[$jenisTr]["selectorModel"],
            "selectorCaller" => MODUL_PATH . $this->configUi[$jenisTr]["selectorCaller"] . "/$jenisTr/" . $this->configUi[$jenisTr]["selectorModel"],
            "selectorLabel" => $this->configUi[$jenisTr]["selectorLabel"],
            //            "pihakCaller" => base_url() . "_selectorPihak/" . $this->configUi[$jenisTr]["pihakCaller"] . "/$jenisTr/" . $this->configUi[$jenisTr]["pihakModel"],
            "pihakCaller" => MODUL_PATH . $this->configUi[$jenisTr]["pihakCaller"] . "/$jenisTr/" . $this->configUi[$jenisTr]["pihakModel"],
            "pihakCallerDelete" => MODUL_PATH . "_processPihak/remove/$jenisTr",
            // "pihakLabel"=>$this->configUi[$jenisTr]["pihakLabel"],
            "pihakLabel" => isset($_SESSION[$cCode]['main']['pihakName']) ? $_SESSION[$cCode]['main']['pihakName'] : $this->configUi[$jenisTr]["pihakLabel"],

            "arrayProgressLabels" => $progressFields,
            "arrayOnProgress" => $arrayOnprogress,
            "arrayOnProgressByState" => $arrayOnprogressbyState,
            "stepLabels" => $stepLabels,
            "stepLinks" => $stepLinks,
            "currentState" => $currentState,
            "alternateLink" => MODUL_PATH . "/History" . "/viewHistory/" . $this->jenisTr,
            "alternateLinkCaption" => $this->configUi[$jenisTr]["label"] . " histories " . "<span class='glyphicon glyphicon-arrow-right'></span>",
            //==untuk keperluan followup many-to-one
            "allowJoin" => isset($allowJoin) ? $allowJoin : false,
            "actionLabel" => isset($actionLabel) ? $actionLabel : "",
            "_nextStepNum" => isset($nextStepNum) ? $nextStepNum : "",
            "_currentStepNum" => $currentState,
            "followupBase" => MODUL_PATH . "FollowUp/followupPrePreview/",
            "addLink" => $addLink,
            "filters" => array(
                "dates" => $this->dates,
                "date1" => $date1,
                "date2" => $date2,
            ),
            "thisPage" => MODUL_PATH . $this->uri->segment(2) . "/" . $this->uri->segment(3) . "/" . $this->uri->segment(4) . "/" . $this->uri->segment(5),
        );
        //        $data= array();
        //endregion


        $this->load->view("history", $data);
    }

    public function viewIncompleteStepAntarCabang()
    {

        $jenisTr = $this->uri->segment(3);
        $cCode = "_TR_" . $this->jenisTr;
        $historyFields = isset($this->configUi[$jenisTr]['shortHistoryFields']) ? $this->configUi[$jenisTr]['shortHistoryFields'] : array();
        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
        $connectTo = isset($this->configUi[$jenisTr]['connectTo']) ? $this->configUi[$jenisTr]['connectTo'] : "";
        $stepHistoryFields = isset($this->configUi[$jenisTr]['shortStepHistoryFields']) ? $this->configUi[$jenisTr]['shortStepHistoryFields'] : array();
        $arrExtHistoryFields2 = isset($this->configUi[$jenisTr]["extHistoryFields2"]) ? $this->configUi[$jenisTr]["extHistoryFields2"] : array();


        $tmpMasterIDExt = array();
        $arrJenisStepRslt = array();
        $arrayOnProgressView = array();
        if (sizeof($stepHistoryFields) > 0) {
            if (strlen($connectTo) > 0) {
                $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
                $transaksiConfig = isset($this->configUi[$jenisTr]['steps']) ? $this->configUi[$jenisTr]['steps'] : array();
                $transaksiConnectConfig = isset($this->configUi[$connectTo]['steps']) ? $this->configUi[$connectTo]['steps'] : array();
                $transaksiStepConfig = isset($this->configUi[$jenisTr]['steps'][1]) ? $this->configUi[$jenisTr]['steps'][1] : array();
                $jenisTrTop = isset($transaksiStepConfig['target']) ? $transaksiStepConfig['target'] : "";


                $transaksiExtendConnect = isset($this->configUi[$jenisTr]['extConnectTo']) ? $this->configUi[$jenisTr]['extConnectTo'] : array();
                $transaksiExtendConnectMain = isset($this->configUi[$jenisTr]['extConnectToMain']) ? $this->configUi[$jenisTr]['extConnectToMain'] : array();
                $transaksiExtendConnectPair = isset($this->configUi[$jenisTr]['extConnectToPair']) ? $this->configUi[$jenisTr]['extConnectToPair'] : array();


                if (sizeof($transaksiExtendConnect) > 0) {
                    foreach ($transaksiExtendConnect as $key_jenis) {
                        $arrJenisStep = array(
                            "next_step_num" => 1,
                            "next_step_code" => $key_jenis,
                        );

                        $arrJenisStepRslt[$key_jenis] = callNextPICDetail($arrJenisStep);

                    }
                }


                $tr2 = new MdlTransaksi();
                $tr2->setFilters(array());

                if (isset($connectTo)) {
                    $tr2->addFilter("jenis_master='$connectTo'");
                    $tr2->addFilter("link_id='0'");
                    $tr2->addFilter("next_step_code>'0'");
                    $tr2->addFilter("sub_step_number>'0'");
                    $tr2->addFilter("gudang_id<>'0'");
                    if ($this->session->login['cabang_id'] == CB_ID_PUSAT) {
                    }
                    else {
                        $tr2->addFilter("transaksi.cabang2_id='" . $this->session->login['cabang_id'] . "'");
                    }
                    $tmpSrc2 = $tr2->lookupJoined_OLD()->result();
                    //                    cekHere($this->db->last_query() . " --- " . count($tmpSrc2));

                    $tmp2 = array();
                    $tmpMasterIDResult = array();
                    $tmpMasterID = array();
                    $tmpResult = array();
                    $arrNextAction = array();
                    if (sizeof($tmpSrc2) > 0) {

                        foreach ($tmpSrc2 as $src2sSpec) {
                            $tmpMasterID[$src2sSpec->id_master] = $src2sSpec->id_master;
                            $arrNextAction[$src2sSpec->transaksi_id] = array(
                                "next_step_num" => $src2sSpec->next_substep_num,
                                "next_step_code" => $src2sSpec->next_substep_code,
                            );

                            $arrTransID[] = $src2sSpec->transaksi_id;
                        }
                        //$this->debugArrPrint($tmpMasterID);
                        //$this->debugArrPrint($transaksiExtendConnectMain);
                        //$this->debugArrPrint($transaksiExtendConnect);

                        //--------------------------------
                        if (sizeof($transaksiExtendConnect) > 0) {
                            $tr12 = new MdlTransaksi();
                            $tr12->setFilters(array());
                            $tr12->addFilter("link_id=0");
                            $this->db->where_in("jenis", $transaksiExtendConnectMain);
                            $tr12->addFilter("id_master in ('" . implode("','", $tmpMasterID) . "')");
                            $tr12->addFilter("valid_qty>'0'");
                            //                            $trTmpExt = $tr12->lookupAll()->result();
                            $trTmpExt = $tr12->lookupJoined()->result();
                            //                            $this->debugShowLastQuery("biru");
                            //                            cekHere(sizeof($trTmpExt));
                            //                            $this->debugArrPrint($trTmpExt);
                            if (sizeof($trTmpExt) > 0) {
                                foreach ($trTmpExt as $trTmpExtSpec) {
                                    //arrprint($trTmpExtSpec);
                                    $pairCode = $transaksiExtendConnectPair[$trTmpExtSpec->jenis];
                                    //                                    cekhere($pairCode);
                                    $tmpMasterIDExt[$trTmpExtSpec->id_master][$trTmpExtSpec->jenis] = $arrJenisStepRslt[$pairCode];
                                }
                            }
                        }
                        //                        $this->debugArrPrint($tmpMasterIDExt);
                        //--------------------------------


                        $tr3 = new MdlTransaksi();
                        $tr3->setFilters(array());
                        $tr3->addFilter("link_id='0'");
                        $tr3->addFilter("id_master in ('" . implode("','", $tmpMasterID) . "')");
                        $tmpSrc3 = $tr3->lookupAll()->result();
                        //                    cekHijau($this->db->last_query() . " --- " . count($tmpSrc2));

                        //region membaca pairingan registries MAIN
                        $tmpReg_result = array();
                        if (sizeof($pairRegistries) > 0) {
                            foreach ($pairRegistries as $param) {

                                $trReg = new MdlTransaksi();
                                $trReg->setFilters(array());
                                $trReg->addFilter("param='$param'");
                                $trReg->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
                                $tmpReg = $trReg->lookupRegistries()->result();
                                if (sizeof($tmpReg) > 0) {
                                    foreach ($tmpReg as $regRow) {
                                        $param = $regRow->param;
                                        $tmpReg_result[$regRow->transaksi_id][$param] = blobDecode($regRow->values);
                                    }
                                }
                            }
                        }
                        //endregion

                        foreach ($tmpSrc3 as $tmpSrc3Spec) {
                            $tmpMasterIDResult[$tmpSrc3Spec->id_master][$tmpSrc3Spec->jenis] = $tmpSrc3Spec->nomer;
                        }

                        foreach ($tmpSrc2 as $src2s) {
                            if (sizeof($tmpMasterIDResult[$src2s->id_master]) > 0) {
                                foreach ($tmpMasterIDResult[$src2s->id_master] as $key => $val) {

                                    $src2s->$key = $val;
                                }
                            }
                            $tmpResult[$src2s->id_master] = $src2s;
                        }

                        $arrNextPIC = callNextPIC($arrNextAction);


                        foreach ($tmpResult as $tmpSpec) {
                            //                            $this->debugArrPrint($tmpSpec);
                            $extHistoryFields2 = isset($arrExtHistoryFields2[$tmpSpec->step_number]) ? $arrExtHistoryFields2[$tmpSpec->step_number] : array();

                            if (sizeof($pairRegistries) > 0) {
                                if ((sizeof($tmpReg_result) > 0) && (isset($tmpReg_result[$tmpSpec->transaksi_id]))) {
                                    foreach ($tmpReg_result[$tmpSpec->transaksi_id] as $param => $eReg) {
                                        if ($param == "main") {
                                            foreach ($eReg as $k => $v) {
                                                if (!isset($tmpSpec->$k)) {
                                                    $tmpSpec->$k = $v;
                                                }
                                            }
                                        }
                                        elseif ($param == "items") {
                                            if (sizeof($extHistoryFields2) > 0) {
                                                foreach ($extHistoryFields2 as $k1 => $v1) {
                                                    if (is_array($v1)) {
                                                        $kolom = $v1['kolom'];
                                                        $format = $v1['format'];
                                                        //                                                cekHitam(":: $kolom :: $format ::");
                                                        if (!isset($tmpSpec->$k1)) {
                                                            $tmpDetail = "";
                                                            foreach ($eReg as $eeReg) {
                                                                $valDetail = formatField($format, $eeReg[$kolom]);
                                                                $tmpDetail .= "<span>$valDetail</span><br>";
                                                            }
                                                            $tmpSpec->$k1 = $tmpDetail;
                                                        }
                                                    }
                                                    else {
                                                        if (!isset($tmpSpec->$k1)) {
                                                            $tmpDetail = "";
                                                            foreach ($eReg as $eeReg) {
                                                                $valDetail = formatField("nomer", $eeReg[$v1]);
                                                                $tmpDetail .= "<span>$valDetail</span><br>";
                                                            }
                                                            $tmpSpec->$k1 = $tmpDetail;
                                                        }
                                                    }

                                                }
                                            }
                                        }
                                    }
                                }
                            }


                            foreach ($stepHistoryFields as $fName => $fLabel) {

                                $keyFormat = array_key_exists($fName, getTransaksiJenis()) ? "nomer" : $fName;
                                $tmp2Datas[$fName] = isset($tmpSpec->$fName) ? formatField($keyFormat, $tmpSpec->$fName) : "-";
                            }

                            if ($tmpSpec->sub_step_number > 0) {
                                $tmp2Datas['state'] = "<span style='color:" . $this->configUi[$jenisTr]['steps'][$tmpSpec->sub_step_number]['stateColor'] . "'>" . $this->configUi[$jenisTr]['steps'][$tmpSpec->sub_step_number]['stateLabel'] . "</span>";
                                $tmp2Datas['state'] .= "<br>" . createStateSign($tmpSpec->sub_step_number, $tmpSpec->step_avail, $connectTo);
                            }
                            else {
                                $tmp2Datas['state'] = "<span style='color:#777777'>canceled</span>";
                            }


                            $tmp2Datas['next_pic'] = "-";
                            if (sizeof($arrNextPIC) > 0) {
                                if (isset($arrNextPIC[$tmpSpec->next_substep_code][$tmpSpec->next_substep_num])) {
                                    $next_pic = "";
                                    $nob = 1;
                                    foreach ($arrNextPIC[$tmpSpec->next_substep_code][$tmpSpec->next_substep_num] as $spec) {
                                        if ($tmpSpec->cabang_id == $spec['cabang_id']) {

                                            if ($next_pic == "") {
                                                $next_pic = "$nob. " . $spec['nama'];
                                            }
                                            else {
                                                $nob++;
                                                $next_pic = $next_pic . "<br>" . "$nob. " . $spec['nama'];
                                            }

                                        }
                                    }
                                    $tmp2Datas['next_pic'] = $next_pic;

                                }
                            }
                            //$this->debugArrPrint($tmpMasterIDExt[$tmpSpec->id_master]);
                            //---------------------------------------------
                            //                            if(isset($tmpMasterIDExt[$tmpSpec->id_master])){
                            //                            if(isset($tmpMasterIDExt[$tmpSpec->id_master])){
                            if (sizeof($transaksiExtendConnectPair) > 0) {
                                foreach ($transaksiExtendConnectPair as $kk => $vv) {
                                    $next_picx = "";
                                    $nobx = 1;
                                    if (isset($tmpMasterIDExt[$tmpSpec->id_master][$kk])) {
                                        foreach ($tmpMasterIDExt[$tmpSpec->id_master][$kk] as $spec) {
                                            if ($next_picx == "") {
                                                $next_picx = "$nobx. " . $spec['nama'];
                                            }
                                            else {
                                                $nobx++;
                                                $next_picx .= "<br>" . "$nobx. " . $spec['nama'];
                                            }
                                        }
                                    }
                                    else {
                                        $next_picx = "done";
                                    }

                                    $tmp2Datas[$kk] = $next_picx;
                                }
                            }
                            //                            }
                            //---------------------------------------------


                            $tmp2[] = $tmp2Datas;

                            //                            break;
                        }
                    }
                }


                $arrayOnProgressView = $tmp2; // $tmp2
            }
        }

        return $arrayOnProgressView;
    }

    public function viewResumeDetails()
    {

        $selectedID = $this->uri->segment(5);

        $this->load->model("MdlTransaksi");
        $tr = new MdlTransaksi();
        $tr->setFilters(array());
        $tr->addFilter("transaksi_data.trash='0'");
        $joinedTrans = $tr->lookupJoinedByID($selectedID)->result()[0];

        $jenisTr = $joinedTrans->jenis_master;
        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
        $receiptElemets = isset($this->configUi[$jenisTr]["receiptElements"]) ? $this->configUi[$jenisTr]["receiptElements"] : array();
        $receiptDetailCompactListlabel = isset($this->configLayout[$jenisTr]["reviewDetailCompactListsLabel"]) ? $this->configLayout[$jenisTr]["reviewDetailCompactListsLabel"] : array();
        $receiptDetailCompactListlabel2 = isset($this->configLayout[$jenisTr]["reviewDetailCompactListsLabel2"]) ? $this->configLayout[$jenisTr]["reviewDetailCompactListsLabel2"] : array();
        $receiptCompactDetailsSum = isset($this->configLayout[$jenisTr]["reviewCompactListDetailSum"]) ? $this->configLayout[$jenisTr]["reviewCompactListDetailSum"] : array();
        $receiptCompactDetailsSum2 = isset($this->configLayout[$jenisTr]["reviewCompactListDetailSum2"]) ? $this->configLayout[$jenisTr]["reviewCompactListDetailSum2"] : array();
        $receiptMainLabel = isset($this->configLayout[$jenisTr]["reviewMainCompactListsLabel"]) ? $this->configLayout[$jenisTr]["reviewMainCompactListsLabel"] : array();
        $reviewCompactListSum = isset($this->configLayout[$jenisTr]["reviewCompactListSum"]) ? $this->configLayout[$jenisTr]["reviewCompactListSum"] : array();
        $reviewAddRows = isset($this->configLayout[$jenisTr]["reviewAddRows"]) ? $this->configLayout[$jenisTr]["reviewAddRows"] : array();
        $reviewSign = isset($this->configLayout[$jenisTr]["reviewSign"][$joinedTrans->step_number]) ? $this->configLayout[$jenisTr]["reviewSign"][$joinedTrans->step_number] : array();

        $d_start = backCustomDate(30, $joinedTrans->fulldate);
        $d_stop = $joinedTrans->fulldate;

        $tr->setFilters(array());
        $tr->addFilter("transaksi_id='$selectedID'");
        $tmpReg = $tr->lookupDataRegistries()->result();

        $mainElements = array();

        $mainData = array();
        $receiptDetailFields = array();
        $receiptSumFields = array();
        $masterItems = array();
        $masterItems2_sum = array();
        foreach ($tmpReg as $regData) {
            foreach ($regData as $key_reg => $val_reg) {
                switch ($key_reg) {
                    case "main":
                        $mainData = blobDecode($val_reg);
                        break;
                    case "items":
                        $masterItems_0 = blobDecode($val_reg);
                        foreach ($masterItems_0 as $pId => $masterItem) {
                            if (isset($masterItem['nett1']) && ($masterItem['nett1'] > 0)) {
                                $ppn_key = $masterItem['ppn'] / $masterItem['nett1'];
                            }
                            else {
                                $ppn_key = 0;
                            }
                            $addItem['harganppn'] = $masterItem['harga'] + ($masterItem['harga'] * $ppn_key);
                            $masterItems[$pId] = $masterItem + $addItem;
                        }
                        break;
                    case "items2_sum":
                        $masterItems2_sum = blobDecode($val_reg);
                        //                    foreach ($masterItems_0 as $pId => $masterItem) {
                        //                        $ppn_key = $masterItem['ppn'] / $masterItem['nett1'];
                        //                        $addItem['harganppn'] = $masterItem['harga'] + ($masterItem['harga'] * $ppn_key);
                        //                        $masterItems[$pId] = $masterItem + $addItem;
                        //                    }
                        break;
                    case "main_elements"://
                        $mainElements = blobDecode($val_reg);
                        break;
                    case "receiptDetailFields"://
                        $receiptDetailFields = blobDecode($val_reg);
                        break;
                    case "receiptSumFields"://
                        $receiptSumFields = blobDecode($val_reg);
                        break;
                }
            }

        }
        switch ($joinedTrans->produk_jenis) {
            case "produk":
                $link = base_url() . "Ledger/viewMoveDetails/RekeningPembantuProduk/persediaan produk/";
                break;
            case "supplies":
                $link = base_url() . "Ledger/viewMoveDetails/RekeningPembantuSupplies/persediaan supplies/";
                break;
            default:
                $link = NULL;
                break;
        }


        $sumQty = array();
        $arrItemsLink = array();
        if (sizeof($masterItems) > 0) {
            $qty_sum = 0;
            foreach ($masterItems as $data) {
                $qty_sum += $data["qty"];

                if ($link != NULL) {
                    $arrItemsLink[$data['id']] = $link . $data['id'] . "?o=" . $data['cabangID'] . "&trID=" . $joinedTrans->transaksi_id . "&date1=$d_start&date2=$d_stop";
                }
            }
            $sumQty["qty"] = $qty_sum;
        }
        $mainData = $mainData + $sumQty + (array)$joinedTrans;

        //region signatures
        $signNumbers = array();
        $signValues = array();
        $signExtValues = array();
        $trs = new MdlTransaksi();
        $trs->setFilters(array());
        $tmpSign = $trs->lookupSignaturesByMasterID($joinedTrans->id_master)->result();

        $signHeader = array();
        if (sizeof($tmpSign) > 0) {
            foreach ($tmpSign as $row) {
                $signValues['sign_' . $row->step_number] = array(
                    "label" => isset($this->configUi[$joinedTrans->jenis_master]['steps'][$row->step_number]['stateCaption']) ? $this->configUi[$joinedTrans->jenis_master]['steps'][$row->step_number]['stateCaption'] : "",
                    "contents" => isset($row->oleh_nama) ? $row->oleh_nama : "",
                    "caption_department" => isset($this->config->item("userGroup")[$row->group_code]) ? $this->config->item("userGroup")[$row->group_code] : "",
                );
            }
        }
        //endregion


        $data = array(
            "mode" => $this->uri->segment(3),
            "mainFields" => $mainData,
            "items" => $masterItems,
            "items2_sum" => $masterItems2_sum,
            "itemsLabel" => $receiptDetailCompactListlabel,
            "itemsLabel2" => $receiptDetailCompactListlabel2,
            "mainSumDetailsFieldsLabel" => $receiptCompactDetailsSum,
            "mainSumDetailsFieldsLabel2" => $receiptCompactDetailsSum2,
            "mainSumFieldsLabel" => $reviewCompactListSum,
            "mainFieldsLabel" => $receiptMainLabel,
            "mainElements" => $mainElements,
            "receiptSumFields" => $receiptSumFields,
            "reviewAddRows" => $reviewAddRows,
            "sign" => $signValues,
            "reviewSign" => $reviewSign,

            "itemsLink" => $arrItemsLink,
            "itemsKolomLink" => array("nama", "produk_kode"),
            "underMaintenance" => underConstruction(),
        );

        $this->load->view("transaksi", $data);

    }

    // START OF COMPLETE REPEATED LOGIC
    public function viewGunggunganIndex()
    {
        $starttime = microtime(true);
        if (!isset($this->session->login['id'])) {
            redirect(base_url() . "Login");
        }
        $scriptBottom = "";
        $sesionReplacer = replaceSession();
        $arrTransaksiLabel = loadConfigUiMasterLabel();

        $jenisTr = $this->jenisTr;
        $cCode = "_TR_" . $this->jenisTr;
        $this->load->model("MdlTransaksi");
        $paymentConfig = isset($this->configUi[$jenisTr]['paymentConfig']) ? $this->configUi[$jenisTr]['paymentConfig'] : false;
        $myPaymentConfig = isset($this->configUi[$jenisTr]['myPaymentConfig']) ? $this->configUi[$jenisTr]['myPaymentConfig'] : false;
        $historyFields = isset($this->configUi[$jenisTr]['shortHistoryFields2']) ? $this->configUi[$jenisTr]['shortHistoryFields2'] : array();
        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();
        $connectTo = isset($this->configUi[$jenisTr]['connectTo']) ? $this->configUi[$jenisTr]['connectTo'] : "";
        $stepHistoryFields = isset($this->configUi[$jenisTr]['shortStepHistoryFields']) ? $this->configUi[$jenisTr]['shortStepHistoryFields'] : array();
        $arrExtHistoryFields2 = isset($this->configUi[$jenisTr]["extHistoryFields2"]) ? $this->configUi[$jenisTr]["extHistoryFields2"] : array();

        $mb = new MobileDetect();
        $isMob = $mb->isMobile();
        if ($isMob) {
            $historyFields = isset($this->configUi[$jenisTr]['compactHistoryFields']) ? $this->configUi[$jenisTr]['compactHistoryFields'] : array();
        }

        //region lookup on-going transactions
        $progressFields = $historyFields;
        $progressFields['state'] = "status";
        $progressFields['action'] = "action";
        $progressFields['reject'] = "reject";

        $steps = $this->configUi[$jenisTr]['steps'];

        // Filter Pajak: 'all', 'npwp', 'gunggungan'
        $filterPajak = isset($_POST['filter_pajak']) ? trim($_POST['filter_pajak']) : (isset($_GET['filter_pajak']) ? trim($_GET['filter_pajak']) : 'all');
        if (empty($filterPajak)) {
            if (isset($_POST['hideNpwp']) && $_POST['hideNpwp'] == 1) {
                $filterPajak = 'gunggungan';
            }
            else {
                $filterPajak = 'all';
            }
        }

        // Filter Cabang: 'all', '-1', '1', '33', '34'
        $filterCabang = isset($_POST['filter_cabang']) ? trim($_POST['filter_cabang']) : (isset($_GET['filter_cabang']) ? trim($_GET['filter_cabang']) : 'all');
        if (empty($filterCabang)) {
            $filterCabang = 'all';
        }

        // Tanggal Filter (Mendukung baik POST dari DataTable AJAX maupun GET dari URL)
        $dateInput = (isset($_POST['date']) && trim($_POST['date']) != '') ? trim($_POST['date']) : ((isset($_GET['date']) && trim($_GET['date']) != '') ? trim($_GET['date']) : '');
        $dateTahun = null;
        $dateBulan = null;
        if ($dateInput != '') {
            $getDate_ex = explode("-", $dateInput);
            if (count($getDate_ex) >= 2) {
                $dateTahun = intval($getDate_ex[0]);
                $dateBulan = intval($getDate_ex[1]);
            }
        }

        $divId = isset($this->session->login['div_id']) ? $this->session->login['div_id'] : '18';

        // 1. QUERY HITUNG BADGE (Khusus Step 1: sub_step_number = '1', GROUP BY cabang2_id)
        $this->db->select("
            transaksi.cabang2_id,
            COUNT(DISTINCT transaksi.id) AS count_all,
            COUNT(DISTINCT CASE WHEN per_customers.npwp IS NOT NULL AND TRIM(per_customers.npwp) != '' AND TRIM(per_customers.npwp) != '-' THEN transaksi.id END) AS count_npwp,
            COUNT(DISTINCT CASE WHEN per_customers.npwp IS NULL OR TRIM(per_customers.npwp) = '' OR TRIM(per_customers.npwp) = '-' THEN transaksi.id END) AS count_gunggungan
        ", false);
        $this->db->from('transaksi');
        $this->db->join('transaksi_data', 'transaksi_data.transaksi_id = transaksi.id AND transaksi_data.trash = "0" AND transaksi_data.valid_qty > 0 AND transaksi_data.sub_step_number = "1" AND transaksi_data.next_substep_code IN ("110e", "110")');
        $this->db->join('per_customers', 'transaksi.customers_id = per_customers.id', 'left');
        $this->db->where('transaksi.status', '1');
        $this->db->where('transaksi.trash', '0');
        $this->db->where('transaksi.link_id', '0');
        $this->db->where('transaksi.div_id', $divId);
        $this->db->where('transaksi.jenis_top', '110r');
        if ($this->session->login['employee_type'] == "employee_freelance") {
            $this->db->where("transaksi.seller_id", $this->session->login['id']);
        }
        if ($dateTahun !== null && $dateBulan !== null) {
            $this->db->where("YEAR(transaksi.dtime) = '$dateTahun' AND MONTH(transaksi.dtime) = '$dateBulan'");
        }
        $this->db->group_by('transaksi.cabang2_id');

        $badgeQuery = $this->db->get();
        $badgeRows = ($badgeQuery->num_rows() > 0) ? $badgeQuery->result() : array();
        $countAll = 0;
        $countNpwp = 0;
        $countGunggungan = 0;
        $countCabang = array();

        if (sizeof($badgeRows) > 0) {
            foreach ($badgeRows as $bRow) {
                $cId = (string)$bRow->cabang2_id;
                $cAll = intval($bRow->count_all);
                $cNpwp = intval($bRow->count_npwp);
                $cGung = intval($bRow->count_gunggungan);

                $countAll += $cAll;
                $countNpwp += $cNpwp;
                $countGunggungan += $cGung;

                $countCabang[$cId] = array(
                    "all" => $cAll,
                    "npwp" => $cNpwp,
                    "gunggungan" => $cGung,
                );
            }
        }

        // Total records berdasarkan filter aktif (Lv1 Pajak + Lv2 Cabang)
        if ($filterCabang != 'all' && isset($countCabang[$filterCabang])) {
            if ($filterPajak == 'npwp') {
                $recordsTotal = $countCabang[$filterCabang]['npwp'];
            }
            elseif ($filterPajak == 'gunggungan') {
                $recordsTotal = $countCabang[$filterCabang]['gunggungan'];
            }
            else {
                $recordsTotal = $countCabang[$filterCabang]['all'];
            }
        }
        else {
            if ($filterPajak == 'npwp') {
                $recordsTotal = $countNpwp;
            }
            elseif ($filterPajak == 'gunggungan') {
                $recordsTotal = $countGunggungan;
            }
            else {
                $recordsTotal = $countAll;
            }
        }

        // 2. QUERY HITUNG FILTERED DATA (Jika ada Search Keyword)
        $hasSearch = (isset($_POST['search']['value']) && trim($_POST['search']['value']) != '');
        if ($hasSearch) {
            $this->db->select("COUNT(DISTINCT transaksi.id) AS total_filtered", false);
            $this->db->from('transaksi');
            $this->db->join('transaksi_data', 'transaksi_data.transaksi_id = transaksi.id AND transaksi_data.trash = "0" AND transaksi_data.valid_qty > 0 AND transaksi_data.sub_step_number = "1" AND transaksi_data.next_substep_code IN ("110e", "110")');
            $this->db->join('per_customers', 'transaksi.customers_id = per_customers.id', 'left');
            $this->db->where('transaksi.status', '1');
            $this->db->where('transaksi.trash', '0');
            $this->db->where('transaksi.link_id', '0');
            $this->db->where('transaksi.div_id', $divId);
            $this->db->where('transaksi.jenis_top', '110r');
            if ($this->session->login['employee_type'] == "employee_freelance") {
                $this->db->where("transaksi.seller_id", $this->session->login['id']);
            }
            if ($dateTahun !== null && $dateBulan !== null) {
                $this->db->where("YEAR(transaksi.dtime) = '$dateTahun' AND MONTH(transaksi.dtime) = '$dateBulan'");
            }
            if ($filterPajak == 'npwp') {
                $this->db->where("per_customers.npwp IS NOT NULL AND TRIM(per_customers.npwp) != '' AND TRIM(per_customers.npwp) != '-'");
            }
            elseif ($filterPajak == 'gunggungan') {
                $this->db->where("(per_customers.npwp IS NULL OR TRIM(per_customers.npwp) = '' OR TRIM(per_customers.npwp) = '-')");
            }
            if ($filterCabang != 'all') {
                $this->db->where('transaksi.cabang2_id', $filterCabang);
            }

            $key = trim($_POST['search']['value']);
            $key = preg_replace('/\s+/', ' ', $key);
            $arrKey = explode(" ", $key);

            $this->db->group_start();
            foreach ($arrKey as $k) {
                if (trim($k) != '') {
                    $this->db->group_start();
                    $this->db->like('per_customers.npwp', $k);
                    $this->db->or_like('transaksi.customers_nama', $k);
                    $this->db->or_like('transaksi.dtime', $k);
                    $this->db->or_like('transaksi.nomer', $k);
                    $this->db->or_like('transaksi.nomer_top', $k);
                    $this->db->or_like('transaksi.nomer2', $k);
                    $this->db->group_end();
                }
            }
            $this->db->group_end();

            $filterQuery = $this->db->get();
            $filterRow = ($filterQuery->num_rows() > 0) ? $filterQuery->row() : null;
            $recordsFiltered = ($filterRow !== null && isset($filterRow->total_filtered)) ? intval($filterRow->total_filtered) : 0;
        }
        else {
            $recordsFiltered = $recordsTotal;
        }

        // START OF COMPLETE REPEATED LOGIC
        // Cross-Category & Cross-Cabang Search Match Detection (UX Pintar: Rekomendasi Pindah Tab / Cabang)
        $crossSearchMatch = null;
        if ($hasSearch && $recordsFiltered == 0) {
            $cabangNamesMap = array(
                'all' => 'SEMUA CABANG'
            );
            $this->db->select('id, nama, kode_cabang');
            $this->db->from('per_cabang');
            $this->db->where('trash', '0');
            $this->db->where('status', '1');
            $this->db->where('jenis', 'cabang');
            $cabQuery = $this->db->get();
            if ($cabQuery && $cabQuery->num_rows() > 0) {
                foreach ($cabQuery->result() as $cRow) {
                    $cabangNamesMap[(string)$cRow->id] = (!empty($cRow->kode_cabang) ? $cRow->kode_cabang . " - " : "") . $cRow->nama;
                }
            }

            $this->db->select("transaksi.cabang2_id, 
                COUNT(DISTINCT transaksi.id) AS total_count,
                COUNT(DISTINCT CASE WHEN per_customers.npwp IS NOT NULL AND TRIM(per_customers.npwp) != '' AND TRIM(per_customers.npwp) != '-' THEN transaksi.id END) AS npwp_count,
                COUNT(DISTINCT CASE WHEN per_customers.npwp IS NULL OR TRIM(per_customers.npwp) = '' OR TRIM(per_customers.npwp) = '-' THEN transaksi.id END) AS gunggungan_count", false);
            $this->db->from('transaksi');
            $this->db->join('transaksi_data', 'transaksi_data.transaksi_id = transaksi.id AND transaksi_data.trash = "0" AND transaksi_data.valid_qty > 0 AND transaksi_data.sub_step_number = "1" AND transaksi_data.next_substep_code IN ("110e", "110")');
            $this->db->join('per_customers', 'transaksi.customers_id = per_customers.id', 'left');
            $this->db->where('transaksi.status', '1');
            $this->db->where('transaksi.trash', '0');
            $this->db->where('transaksi.link_id', '0');
            $this->db->where('transaksi.div_id', $divId);
            $this->db->where('transaksi.jenis_top', '110r');
            if ($this->session->login['employee_type'] == "employee_freelance") {
                $this->db->where("transaksi.seller_id", $this->session->login['id']);
            }
            if ($dateTahun !== null && $dateBulan !== null) {
                $this->db->where("YEAR(transaksi.dtime) = '$dateTahun' AND MONTH(transaksi.dtime) = '$dateBulan'");
            }

            $this->db->group_start();
            foreach ($arrKey as $k) {
                if (trim($k) != '') {
                    $this->db->group_start();
                    $this->db->like('per_customers.npwp', $k);
                    $this->db->or_like('transaksi.customers_nama', $k);
                    $this->db->or_like('transaksi.dtime', $k);
                    $this->db->or_like('transaksi.nomer', $k);
                    $this->db->or_like('transaksi.nomer_top', $k);
                    $this->db->or_like('transaksi.nomer2', $k);
                    $this->db->group_end();
                }
            }
            $this->db->group_end();
            $this->db->group_by('transaksi.cabang2_id');

            $crossQuery = $this->db->get();
            if ($crossQuery && $crossQuery->num_rows() > 0) {
                $cabangRows = array();
                $totAll = 0;
                $totNpwp = 0;
                $totGung = 0;
                foreach ($crossQuery->result_array() as $r) {
                    $cid = (string)$r['cabang2_id'];
                    $cabangRows[$cid] = array(
                        'all' => intval($r['total_count']),
                        'npwp' => intval($r['npwp_count']),
                        'gunggungan' => intval($r['gunggungan_count'])
                    );
                    $totAll += intval($r['total_count']);
                    $totNpwp += intval($r['npwp_count']);
                    $totGung += intval($r['gunggungan_count']);
                }

                $currCabKey = (string)$filterCabang;
                $currCabNama = isset($cabangNamesMap[$currCabKey]) ? $cabangNamesMap[$currCabKey] : ($currCabKey == 'all' ? 'SEMUA CABANG' : 'Cabang ' . $currCabKey);
                $currPajakLabel = ($filterPajak == 'npwp' ? 'NPWP' : ($filterPajak == 'gunggungan' ? 'Non-NPWP / Gunggungan' : 'SEMUA'));

                // Skenario 1: Cek apakah ada di kategori pajak lain pada cabang yang SAMA
                if ($filterPajak != 'all') {
                    $otherPajak = ($filterPajak == 'npwp') ? 'gunggungan' : 'npwp';
                    $otherPajakLabel = ($otherPajak == 'npwp') ? 'NPWP' : 'Non-NPWP / Gunggungan';

                    if ($filterCabang == 'all') {
                        $otherCount = ($otherPajak == 'npwp') ? $totNpwp : $totGung;
                        if ($otherCount > 0) {
                            $crossSearchMatch = array(
                                'type' => 'pajak',
                                'count' => $otherCount,
                                'currentFilter' => $filterPajak,
                                'currentFilterLabel' => $currPajakLabel,
                                'currentCabang' => $filterCabang,
                                'currentCabangNama' => $currCabNama,
                                'targetFilter' => $otherPajak,
                                'targetFilterLabel' => $otherPajakLabel,
                                'targetCabang' => $filterCabang,
                                'targetCabangNama' => $currCabNama
                            );
                        }
                    } elseif (isset($cabangRows[$currCabKey]) && $cabangRows[$currCabKey][$otherPajak] > 0) {
                        $crossSearchMatch = array(
                            'type' => 'pajak',
                            'count' => $cabangRows[$currCabKey][$otherPajak],
                            'currentFilter' => $filterPajak,
                            'currentFilterLabel' => $currPajakLabel,
                            'currentCabang' => $filterCabang,
                            'currentCabangNama' => $currCabNama,
                            'targetFilter' => $otherPajak,
                            'targetFilterLabel' => $otherPajakLabel,
                            'targetCabang' => $filterCabang,
                            'targetCabangNama' => $currCabNama
                        );
                    }
                }

                // Skenario 2: Cek apakah ada di cabang LAIN pada kategori pajak saat ini
                if ($crossSearchMatch === null && $filterCabang != 'all') {
                    $otherCabangMatches = array();
                    $otherCabangCount = 0;
                    foreach ($cabangRows as $cid => $counts) {
                        if ($cid != $currCabKey) {
                            $cVal = ($filterPajak == 'all') ? $counts['all'] : $counts[$filterPajak];
                            if ($cVal > 0) {
                                $otherCabangMatches[$cid] = $cVal;
                                $otherCabangCount += $cVal;
                            }
                        }
                    }

                    if ($otherCabangCount > 0) {
                        if (count($otherCabangMatches) == 1) {
                            reset($otherCabangMatches);
                            $targetCid = key($otherCabangMatches);
                            $targetCabNama = isset($cabangNamesMap[$targetCid]) ? $cabangNamesMap[$targetCid] : 'Cabang ' . $targetCid;
                        } else {
                            $targetCid = 'all';
                            $targetCabNama = 'SEMUA CABANG';
                        }

                        $crossSearchMatch = array(
                            'type' => 'cabang',
                            'count' => $otherCabangCount,
                            'currentFilter' => $filterPajak,
                            'currentFilterLabel' => $currPajakLabel,
                            'currentCabang' => $filterCabang,
                            'currentCabangNama' => $currCabNama,
                            'targetFilter' => $filterPajak,
                            'targetFilterLabel' => $currPajakLabel,
                            'targetCabang' => $targetCid,
                            'targetCabangNama' => $targetCabNama
                        );
                    }
                }

                // Skenario 3: Cek apakah ada di cabang LAIN dan kategori pajak LAIN
                if ($crossSearchMatch === null && $filterCabang != 'all' && $filterPajak != 'all') {
                    $otherPajak = ($filterPajak == 'npwp') ? 'gunggungan' : 'npwp';
                    $otherPajakLabel = ($otherPajak == 'npwp') ? 'NPWP' : 'Non-NPWP / Gunggungan';
                    $bothMatches = array();
                    $bothCount = 0;
                    foreach ($cabangRows as $cid => $counts) {
                        if ($cid != $currCabKey && $counts[$otherPajak] > 0) {
                            $bothMatches[$cid] = $counts[$otherPajak];
                            $bothCount += $counts[$otherPajak];
                        }
                    }

                    if ($bothCount > 0) {
                        if (count($bothMatches) == 1) {
                            reset($bothMatches);
                            $targetCid = key($bothMatches);
                            $targetCabNama = isset($cabangNamesMap[$targetCid]) ? $cabangNamesMap[$targetCid] : 'Cabang ' . $targetCid;
                        } else {
                            $targetCid = 'all';
                            $targetCabNama = 'SEMUA CABANG';
                        }

                        $crossSearchMatch = array(
                            'type' => 'both',
                            'count' => $bothCount,
                            'currentFilter' => $filterPajak,
                            'currentFilterLabel' => $currPajakLabel,
                            'currentCabang' => $filterCabang,
                            'currentCabangNama' => $currCabNama,
                            'targetFilter' => $otherPajak,
                            'targetFilterLabel' => $otherPajakLabel,
                            'targetCabang' => $targetCid,
                            'targetCabangNama' => $targetCabNama
                        );
                    }
                }
            }
        }
        // END OF COMPLETE REPEATED LOGIC

        // ORDER BY
        $keyConvert = array();
        $ur = 1;
        foreach ($progressFields as $key => $lab) {
            $keyConvert[$ur] = $key;
            $ur++;
        }

        $orderMap = array(
            "date" => "transaksi.dtime",
            "dtime" => "transaksi.dtime",
            "cabang2_kode" => "transaksi.cabang2_id",
            "cabang2_nama" => "transaksi.cabang2_nama",
            "customers_nama" => "transaksi.customers_nama",
            "customer" => "transaksi.customers_nama",
            "npwp" => "per_customers.npwp",
            "jenis_pajak" => "per_customers.npwp",
            "projectName" => "transaksi.project_nama",
            "projectNilai" => "transaksi.project_nilai",
            "nomer" => "transaksi.nomer",
            "nomer2" => "transaksi.nomer2",
            "nomer_top" => "transaksi.nomer_top",
            "reference_so_nomer" => "transaksi.nomer_top",
            "reference_dp_nomer" => "transaksi.nomer_top",
            "so_number" => "transaksi.nomer_top",
            "receipt_number" => "transaksi.nomer",
            "oleh_nama" => "transaksi.oleh_nama",
            "harga" => "transaksi.harga",
            "disc" => "transaksi.disc",
            "dpp_ppn" => "transaksi.dpp_ppn",
            "new_grand_ppn" => "transaksi.new_grand_ppn",
            "tagihan" => "transaksi.tagihan",
            "id" => "transaksi.id",
        );

        if (isset($_POST["order"][0]["column"]) && $_POST["order"][0]["column"] > 0) {
            $columnIndex = intval($_POST["order"][0]["column"]);
            $columnKey = isset($keyConvert[$columnIndex]) ? $keyConvert[$columnIndex] : "id";
            $directional = (isset($_POST["order"][0]["dir"]) && strtolower($_POST["order"][0]["dir"]) == "asc") ? "asc" : "desc";
            $orderColumn = isset($orderMap[$columnKey]) ? $orderMap[$columnKey] : "transaksi.id";
        }
        else {
            $orderColumn = "transaksi.id";
            $directional = "desc";
        }

        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 20;

        // 3. QUERY DATA UTAMA - Tahap 1: Ambil hanya ID via pagination (sub_step_number = '1')
        $tmpHist = array();
        if ($recordsFiltered > 0) {
            $this->db->select("transaksi.id AS tr_id");
            $this->db->from('transaksi');
            $this->db->join('transaksi_data', 'transaksi_data.transaksi_id = transaksi.id AND transaksi_data.trash = "0" AND transaksi_data.valid_qty > 0 AND transaksi_data.sub_step_number = "1" AND transaksi_data.next_substep_code IN ("110e", "110")');
            if ($filterPajak != 'all' || $hasSearch) {
                $this->db->join('per_customers', 'transaksi.customers_id = per_customers.id', 'left');
            }
            $this->db->where('transaksi.status', '1');
            $this->db->where('transaksi.trash', '0');
            $this->db->where('transaksi.link_id', '0');
            $this->db->where('transaksi.div_id', $divId);
            $this->db->where('transaksi.jenis_top', '110r');
            if ($this->session->login['employee_type'] == "employee_freelance") {
                $this->db->where("transaksi.seller_id", $this->session->login['id']);
            }
            if ($dateTahun !== null && $dateBulan !== null) {
                $this->db->where("YEAR(transaksi.dtime) = '$dateTahun' AND MONTH(transaksi.dtime) = '$dateBulan'");
            }
            if ($filterPajak == 'npwp') {
                $this->db->where("per_customers.npwp IS NOT NULL AND TRIM(per_customers.npwp) != '' AND TRIM(per_customers.npwp) != '-'");
            }
            elseif ($filterPajak == 'gunggungan') {
                $this->db->where("(per_customers.npwp IS NULL OR TRIM(per_customers.npwp) = '' OR TRIM(per_customers.npwp) = '-')");
            }
            if ($filterCabang != 'all') {
                $this->db->where('transaksi.cabang2_id', $filterCabang);
            }

            if ($hasSearch) {
                $key = trim($_POST['search']['value']);
                $key = preg_replace('/\s+/', ' ', $key);
                $arrKey = explode(" ", $key);

                $this->db->group_start();
                foreach ($arrKey as $k) {
                    if (trim($k) != '') {
                        $this->db->group_start();
                        $this->db->like('per_customers.npwp', $k);
                        $this->db->or_like('transaksi.customers_nama', $k);
                        $this->db->or_like('transaksi.dtime', $k);
                        $this->db->or_like('transaksi.nomer', $k);
                        $this->db->or_like('transaksi.nomer_top', $k);
                        $this->db->or_like('transaksi.nomer2', $k);
                        $this->db->group_end();
                    }
                }
                $this->db->group_end();
            }

            $this->db->group_by(array('transaksi_data.transaksi_id', 'transaksi_data.next_substep_code'));
            $this->db->order_by($orderColumn, $directional);

            if ($length > 0) {
                $this->db->limit($length, $start);
            }

            $idQuery = $this->db->get();
            $idRows = ($idQuery->num_rows() > 0) ? $idQuery->result() : array();
            // showLast_query("biru");
            // matiHere(__LINE__);
            // Tahap 2: Ambil kolom lengkap hanya untuk ID terpilih (sub_step_number = '1')
            if (sizeof($idRows) > 0) {
                $targetTrxIds = array();
                foreach ($idRows as $ir) {
                    $targetTrxIds[] = $ir->tr_id;
                }

                $this->db->select("transaksi.*, per_customers.npwp AS npwp, transaksi_data.next_substep_num, transaksi_data.next_substep_code, transaksi_data.sub_step_number, transaksi_data.transaksi_id, transaksi.oleh_id AS oleh_id, transaksi.oleh_nama AS oleh_nama, transaksi.cabang_id AS cabang_id, transaksi.dtime AS dtime");
                $this->db->from('transaksi');
                $this->db->join('transaksi_data', 'transaksi_data.transaksi_id = transaksi.id');
                $this->db->join('per_customers', 'transaksi.customers_id = per_customers.id', 'left');
                $this->db->where_in('transaksi.id', $targetTrxIds);
                $this->db->where('transaksi_data.trash', '0');
                $this->db->where('transaksi_data.valid_qty >', '0');
                $this->db->where('transaksi_data.sub_step_number', '1');
                $this->db->where_in('transaksi_data.next_substep_code', array('110e', '110'));
                $this->db->group_by(array('transaksi_data.transaksi_id', 'transaksi_data.next_substep_code'));
                $this->db->order_by($orderColumn, $directional);
                $query = $this->db->get();
                $tmpHist = ($query->num_rows() > 0) ? $query->result() : array();
            }
        }

        $arrayOnprogress = array();
        $arrayOnprogress[1] = array();

        // Load config untuk tombol aksi NPWP
        $inputFakturTransaksi = isset($this->configUi[$jenisTr]["inputFakturTransaksi"]) ? $this->configUi[$jenisTr]["inputFakturTransaksi"] : array();
        $inputFakturTransaksiApprove = isset($this->configUi[$jenisTr]["inputFakturTransaksiApprove"]) ? $this->configUi[$jenisTr]["inputFakturTransaksiApprove"] : array();

        if (sizeof($tmpHist) > 0) {
            $arrTransID = array();
            $arrTransTopID = array();
            $arrTransMasterID = array();
            $arrCust = array();
            $arrNextAction = array();
            $arrAddReference = array();
            $arrTopData = array();

            foreach ($tmpHist as $row) {
                $arrTransID[] = $row->transaksi_id;
                if (isset($row->id_top) && $row->id_top > 0) {
                    $arrTransTopID[] = $row->id_top;
                }
                $arrTransMasterID[] = $row->id_master;
                $arrCust[$row->customers_id] = $row->customers_id;
                $arrNextAction[$row->transaksi_id] = array(
                    "next_step_num" => $row->next_substep_num,
                    "next_step_code" => $row->next_substep_code,
                );
            }

            // Lookup Reference SO + arrTopData (efaktur yang sudah tersimpan)
            if (sizeof($arrTransMasterID) > 0) {
                $trRef = new MdlTransaksi();
                $trRef->setFilters(array());
                $trRef->addFilter("id_master in ('" . implode("','", $arrTransMasterID) . "')");
                $trTmp = $trRef->lookUpAll()->result();
                if (sizeof($trTmp) > 0) {
                    foreach ($trTmp as $trTmpSpec) {
                        switch ($trTmpSpec->jenis) {
                            case "588so":
                            case "5822so":
                            case "5823so":
                                $arrAddReference[$trTmpSpec->id_master] = array(
                                    "reference_so_id" => $trTmpSpec->id,
                                    "reference_so_nomer" => $trTmpSpec->nomer,
                                );
                                break;

                            case "110e":
                                // Data efaktur yang sudah pernah di-entry (untuk pre-populate input)
                                if ($trTmpSpec->trash_4 == 0) {
                                    $arrTopData[$trTmpSpec->id_top] = array(
                                        "transaksi_id" => $trTmpSpec->id,
                                        "efaktur" => $trTmpSpec->efaktur,
                                        "efaktur_dtime" => $trTmpSpec->efaktur_dtime,
                                        "eFaktur" => $trTmpSpec->efaktur,
                                        "dateFaktur" => $trTmpSpec->efaktur_dtime,
                                    );
                                }
                                break;
                        }
                    }
                }
            }

            // Lookup Pair Registries hanya untuk baris aktif (termasuk id_master dan id_top sumber transaksi)
            if (!in_array("main", $pairRegistries)) {
                $pairRegistries[] = "main";
            }
            $arrAllRegLookupIDs = array_values(array_unique(array_merge($arrTransID, $arrTransMasterID, $arrTransTopID)));
            $tmpReg_result = array();
            if (sizeof($pairRegistries) > 0 && sizeof($arrAllRegLookupIDs) > 0) {
                $selectKolom = implode(",", $pairRegistries) . ", transaksi_id";
                $trReg = new MdlTransaksi();
                $trReg->setFilters(array());
                $trReg->setJointSelectFields($selectKolom);
                $trReg->addFilter("transaksi_id in ('" . implode("','", $arrAllRegLookupIDs) . "')");
                $tmpReg = $trReg->lookupDataRegistries()->result();
                if (sizeof($tmpReg) > 0) {
                    foreach ($tmpReg as $regRow) {
                        foreach ($regRow as $key_reg => $val_reg) {
                            if ($key_reg != "transaksi_id") {
                                $tmpReg_result[$regRow->transaksi_id][$key_reg] = blobDecode($val_reg);
                            }
                        }
                    }
                }
            }

            $numb = $start;
            foreach ($tmpHist as $row) {
//                arrPrint($row);
//                matiHEre();
                $is_npwp = (!empty($row->npwp) && trim($row->npwp) != '' && trim($row->npwp) != '-') ? 1 : 0;
                $step_number_val = isset($row->step_number) ? $row->step_number : (isset($row->sub_step_number) ? $row->sub_step_number : 1);
                $extHistoryFields2 = isset($arrExtHistoryFields2[$step_number_val]) ? $arrExtHistoryFields2[$step_number_val] : array();
                $jenis_master = $row->jenis_master;
                $modul = MODUL_PATH;
                if (sizeof($pairRegistries) > 0 && isset($tmpReg_result[$row->transaksi_id])) {
                    foreach ($tmpReg_result[$row->transaksi_id] as $param => $eReg) {
                        switch ($param) {
                            case "main":
                                foreach ($eReg as $k => $v) {
                                    if (!isset($row->$k)) {
                                        $row->$k = $v;
                                    }
                                }
                                break;
                            case "items":
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
                                break;
                        }
                    }
                }

                // Pencarian nomor SO dari registri (mendukung referensi_so__nomer, referensi_so_reguler__nomer, referenceNumberSO)
                $soCandidate = null;
                if (!empty($row->referenceNumberSO)) {
                    $soCandidate = $row->referenceNumberSO;
                }
                elseif (!empty($row->referensi_so__nomer)) {
                    $soCandidate = $row->referensi_so__nomer;
                }
                elseif (!empty($row->referensi_so_reguler__nomer)) {
                    $soCandidate = $row->referensi_so_reguler__nomer;
                }
                elseif (isset($row->id_master) && isset($tmpReg_result[$row->id_master]['main'])) {
                    $regMainM = $tmpReg_result[$row->id_master]['main'];
                    if (!empty($regMainM['referensi_so__nomer'])) {
                        $soCandidate = $regMainM['referensi_so__nomer'];
                    }
                    elseif (!empty($regMainM['referensi_so_reguler__nomer'])) {
                        $soCandidate = $regMainM['referensi_so_reguler__nomer'];
                    }
                    elseif (!empty($regMainM['referenceNumberSO'])) {
                        $soCandidate = $regMainM['referenceNumberSO'];
                    }
                }
                elseif (isset($row->id_top) && isset($tmpReg_result[$row->id_top]['main'])) {
                    $regMainT = $tmpReg_result[$row->id_top]['main'];
                    if (!empty($regMainT['referensi_so__nomer'])) {
                        $soCandidate = $regMainT['referensi_so__nomer'];
                    }
                    elseif (!empty($regMainT['referensi_so_reguler__nomer'])) {
                        $soCandidate = $regMainT['referensi_so_reguler__nomer'];
                    }
                    elseif (!empty($regMainT['referenceNumberSO'])) {
                        $soCandidate = $regMainT['referenceNumberSO'];
                    }
                }

                if (isset($arrAddReference[$row->id_master]) && sizeof($arrAddReference[$row->id_master]) > 0) {
                    foreach ($arrAddReference[$row->id_master] as $akey => $aval) {
                        $row->$akey = $aval;
                    }
                }

                // Alternatif A: Format kolom SO number & Referensi (DP / SPD)
                $nomer_sumber_dp = isset($row->nomer_top) ? $row->nomer_top : "-";

                // Deteksi nomor referensi dokumen asal (SPD atau DP)
                $refCandidate = null;
                if (!empty($row->efaktur_source) && $row->efaktur_source !== "-" && $row->efaktur_source !== "0") {
                    $refCandidate = $row->efaktur_source;
                }
                elseif (isset($tmpReg_result[$row->transaksi_id]['main']['efaktur_source']) && !empty($tmpReg_result[$row->transaksi_id]['main']['efaktur_source']) && $tmpReg_result[$row->transaksi_id]['main']['efaktur_source'] !== "-" && $tmpReg_result[$row->transaksi_id]['main']['efaktur_source'] !== "0") {
                    $refCandidate = $tmpReg_result[$row->transaksi_id]['main']['efaktur_source'];
                }
                elseif (!empty($row->referensi_spd_nomer) && $row->referensi_spd_nomer !== "-" && $row->referensi_spd_nomer !== "0") {
                    $refCandidate = $row->referensi_spd_nomer;
                }
                elseif (isset($row->id_top) && isset($tmpReg_result[$row->id_top]['main']['efaktur_source']) && !empty($tmpReg_result[$row->id_top]['main']['efaktur_source']) && $tmpReg_result[$row->id_top]['main']['efaktur_source'] !== "-" && $tmpReg_result[$row->id_top]['main']['efaktur_source'] !== "0") {
                    $refCandidate = $tmpReg_result[$row->id_top]['main']['efaktur_source'];
                }
                elseif (isset($row->id_master) && isset($tmpReg_result[$row->id_master]['main']['efaktur_source']) && !empty($tmpReg_result[$row->id_master]['main']['efaktur_source']) && $tmpReg_result[$row->id_master]['main']['efaktur_source'] !== "-" && $tmpReg_result[$row->id_master]['main']['efaktur_source'] !== "0") {
                    $refCandidate = $tmpReg_result[$row->id_master]['main']['efaktur_source'];
                }

                // Cek apakah transaksi ini merupakan transaksi Down Payment (DP)
                $is_dp_trx = (strpos($nomer_sumber_dp, '4467') === 0 || (isset($row->jenis_top) && $row->jenis_top == '4467') || ($refCandidate !== null && strpos($refCandidate, '4467') === 0));

                if ($is_dp_trx) {
                    // Kasus 1: Transaksi Down Payment (DP)
                    $nomor_dp = (strpos($nomer_sumber_dp, '4467') === 0) ? $nomer_sumber_dp : $refCandidate;
                    $row->reference_dp_nomer = "<div class='text-left'>" . $nomor_dp . "</div>";

                    // Kolom SO Number: Tampilkan nomor SO jika ada, atau '-' jika tidak ada
                    if (!empty($soCandidate) && strpos($soCandidate, '4467') !== 0) {
                        $row->reference_so_nomer = $soCandidate;
                    }
                    elseif (isset($row->reference_so_nomer) && !empty($row->reference_so_nomer) && $row->reference_so_nomer != 0 && strpos($row->reference_so_nomer, '4467') !== 0) {
                        // pertahankan reference_so_nomer yang valid
                    }
                    else {
                        $row->reference_so_nomer = "<div class='text-center'>-</div>";
                    }
                }
                else {
                    // Kasus 2: Transaksi Penjualan Reguler / Pengiriman
                    // Kolom Referensi: Tampilkan nomor SPD jika ada, atau '-' jika tidak ada
                    if ($refCandidate !== null) {
                        $row->reference_dp_nomer = "<div class='text-left'>" . $refCandidate . "</div>";
                    }
                    else {
                        $row->reference_dp_nomer = "<div class='text-center'>-</div>";
                }

                    // Kolom SO Number: Tampilkan nomor SO jika ada
                    if (!empty($row->reference_so_nomer) && $row->reference_so_nomer != 0 && strpos($row->reference_so_nomer, '4467') !== 0) {
                        // sudah terisi dari $arrAddReference
                    }
                    elseif (!empty($soCandidate) && strpos($soCandidate, '4467') !== 0) {
                        $row->reference_so_nomer = $soCandidate;
                    }
                    else {
                        $row->reference_so_nomer = "<div class='text-center'>-</div>";
                    }
                }

                // Penentuan label sumber transaksi berdasarkan nomer_top asli (misal: 4467)
                $nomer_top_ex = explode(".", $nomer_sumber_dp);
                $transaksi_label = isset($arrTransaksiLabel[$nomer_top_ex[0]]) ? $arrTransaksiLabel[$nomer_top_ex[0]] : "-";

                $numb++;
                $tmp = array();
                $tmp[] = array(
                    "key" => 'no',
                    "dataType" => 0,
                    "value" => $numb,
                );

                $transaksi_id = $row->transaksi_id;
                $nextStepNum = $row->next_substep_num;
                $currentStepNum = $row->sub_step_number;

                foreach ($historyFields as $fName => $fLabel) {
                    // Kolom jenis_pajak: REGULER atau PPN GUNGGUNGAN
                    if ($fName == "jenis_pajak") {
                        if ($is_npwp) {
                            $tmp[] = array(
                                "key" => $fName,
                                "dataType" => 0,
                                "value" => "<span style='color:#337ab7; font-weight:bold;'>REGULER</span>",
                            );
                        }
                        else {
                            $tmp[] = array(
                                "key" => $fName,
                                "dataType" => 0,
                                "value" => "<span style='color:#d58512; font-weight:bold;'>PPN GUNGGUNGAN</span>",
                            );
                        }
                    }
                    // Kolom dateFaktur: input date untuk NPWP, "-" untuk gunggungan
                    elseif ($fName == "dateFaktur") {
                        if ($is_npwp) {
                            $dateFaktur_id = "dateFaktur_" . $transaksi_id;
                            $defaultDateVal = "";
                            if (isset($arrTopData[$row->id_top])) {
                                $defaultDateVal = $arrTopData[$row->id_top]["dateFaktur"];
                                $dateFaktur_id = "dateFaktur_" . $arrTopData[$row->id_top]["transaksi_id"];
                            }
                            $tmp[] = array(
                                "key" => $fName,
                                "dataType" => 0,
                                "value" => "<input id='" . $dateFaktur_id . "' type='date' name='dateFaktur' value='" . $defaultDateVal . "' class='form-control text-right text-red text-bold bg-lime' style='min-width:130px;'>",
                            );
                        }
                        else {
                            $tmp[] = array(
                                "key" => $fName,
                                "dataType" => 0,
                                "value" => "-",
                            );
                        }
                    }
                    // Kolom eFaktur: input text untuk NPWP, "-" untuk gunggungan
                    elseif ($fName == "eFaktur") {
                        if ($is_npwp) {
                            $eFaktur_id = "eFaktur_" . $transaksi_id;
                            $defaultEFakturVal = "";
                            if (isset($arrTopData[$row->id_top])) {
                                $defaultEFakturVal = $arrTopData[$row->id_top]["eFaktur"];
                                $eFaktur_id = "eFaktur_" . $arrTopData[$row->id_top]["transaksi_id"];
                            }
                            $tmp[] = array(
                                "key" => $fName,
                                "dataType" => 0,
                                "value" => "<input id='" . $eFaktur_id . "' type='text' name='eFaktur' value='" . $defaultEFakturVal . "' class='form-control text-right text-red text-bold bg-lime' style='min-width:140px;'>",
                            );
                        }
                        else {
                            $tmp[] = array(
                                "key" => $fName,
                                "dataType" => 0,
                                "value" => "-",
                            );
                        }
                    }
                    // Kolom transaksi_label
                    elseif ($fName == "transaksi_label") {
                        $tmp[] = array(
                            "key" => $fName,
                            "dataType" => 0,
                            "value" => $transaksi_label,
                        );
                    }
                    // Kolom no
                    elseif ($fName == "no") {
                        $tmp[] = array(
                            "key" => $fName,
                            "dataType" => 0,
                            "value" => $numb,
                        );
                    }
                    // Kolom npwp
                    elseif ($fName == "npwp") {
                        $npwpVal = (!empty($row->npwp) && trim($row->npwp) != '' && trim($row->npwp) != '-') ? $row->npwp : "-";
                        $tmp[] = array(
                            "key" => $fName,
                            "dataType" => 0,
                            "value" => $npwpVal,
                        );
                    }
                    // Kolom nomer_top
                    elseif ($fName == "nomer_top") {
                        $nomerTopVal = isset($row->nomer_top) ? $row->nomer_top : "-";
                        $tmp[] = array(
                            "key" => $fName,
                            "dataType" => 0,
                            "value" => formatField_he_format_json($fName, $nomerTopVal,$jenis_master,$modul),
                        );
                    }
                    // Kolom cabang2_kode
                    elseif ($fName == "cabang2_kode") {
                        $cabKode = (isset($row->cabang2_id) && isset($this->cabang_code[$row->cabang2_id])) ? $this->cabang_code[$row->cabang2_id] : (isset($row->cabang2_kode) ? $row->cabang2_kode : "-");
                        $tmp[] = array(
                            "key" => $fName,
                            "dataType" => 0,
                            "value" => $cabKode,
                        );
                    }
                    // Kolom reference_so_nomer
                    elseif ($fName == "reference_so_nomer") {
                        $soVal = (isset($row->reference_so_nomer) && !empty($row->reference_so_nomer) && $row->reference_so_nomer !== "-") ? $row->reference_so_nomer : "<div class='text-center'>-</div>";
                        $tmp[] = array(
                            "key" => $fName,
                            "dataType" => 0,
                            "value" => $soVal,
                        );
                    }
                    // Kolom reference_dp_nomer
                    elseif ($fName == "reference_dp_nomer") {
                        $dpVal = isset($row->reference_dp_nomer) ? $row->reference_dp_nomer : "<div class='text-center'>-</div>";
                        $tmp[] = array(
                            "key" => $fName,
                            "dataType" => 0,
                            "value" => $dpVal,
                        );
                    }
                    elseif ($fName == "nomer") {
//                        matiHEre($modul."::".$jenis_master."::".$row->$fName);
                        $thisVal = isset($row->$fName) ? formatField_he_format($fName, $row->$fName,$jenis_master,$modul) : formatField_he_format_json($fName, 0);
                        $tmp[] = array(
                            "key" => $fName,
                            "dataType" => is_array($thisVal),
                            "value" => $thisVal,
                        );
                    }
                    // Kolom lainnya (default)
                    else {
                        $thisVal = isset($row->$fName) ? formatField_he_format_json($fName, $row->$fName) : formatField_he_format_json($fName, 0);
                        $tmp[] = array(
                            "key" => $fName,
                            "dataType" => is_array($thisVal),
                            "value" => $thisVal,
                        );
                    }
                }

                // STATUS STATE
                if ($is_npwp) {
                    // NPWP: tampilkan step indicator lengkap
                    if (isset($row->sub_step_number) && $row->sub_step_number > 0 && isset($this->configUi[$jenisTr]['steps'][$row->sub_step_number])) {
                        $stateColor = isset($this->configUi[$jenisTr]['steps'][$row->sub_step_number]['stateColor']) ? $this->configUi[$jenisTr]['steps'][$row->sub_step_number]['stateColor'] : '#dd3300';
                        $stateLabel = isset($this->configUi[$jenisTr]['steps'][$row->sub_step_number]['stateLabel']) ? $this->configUi[$jenisTr]['steps'][$row->sub_step_number]['stateLabel'] : 'create faktur pajak';
                        $stateHtml = "<span style='color:" . $stateColor . "'>" . $stateLabel . "</span>";
                    }
                    else {
                        $stateHtml = "<span style='color:#777777'>canceled</span>";
                    }
                }
                else {
                    // Gunggungan: teks sederhana berwarna kuning
                    $stateHtml = "<span style='color:#d58512; font-weight:bold;'>gunggungan</span>";
                }
                $tmp[] = array(
                    "key" => "state",
                    "dataType" => 0,
                    "value" => $stateHtml,
                );

                // ACTION — berbeda antara NPWP dan Gunggungan
                $actionLabel = "review " . (isset($this->configUi[$jenisTr]['steps'][$currentStepNum]['label']) ? $this->configUi[$jenisTr]['steps'][$currentStepNum]['label'] : "PREPARE");
                if (isset($this->configUi[$jenisTr]['steps'][$nextStepNum]['actionLabel'])) {
                    $actionLabel = $this->configUi[$jenisTr]['steps'][$nextStepNum]['actionLabel'];
                }
                $stepLabel = isset($this->configUi[$jenisTr]['steps'][$nextStepNum]['label']) ? $this->configUi[$jenisTr]['steps'][$nextStepNum]['label'] : "";

                $followupLink = "top.$('#result').load('" . MODUL_PATH . "FollowUp/followupPrePreview/$jenisTr/" . $transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "');";

                if ($is_npwp) {
                    // NPWP: tombol aksi lengkap (entry efaktur + Simpan Efaktur + Reject + Simpan dan Approve)
                    $followup_id = "followup_" . $transaksi_id;

                    // Cek apakah sudah ada data arrTopData (efaktur sudah pernah di-entry)
                    $disabled_request = "";
                    if (isset($inputFakturTransaksiApprove[1]["enabled"]) && ($inputFakturTransaksiApprove[1]["enabled"] == true)) {
                        if (isset($arrTopData[$row->id_top])) {
                            $disabled_request = "disabled";
                        }
                    }

                    $actionHtml = "<div class='input-group'>";
                    // Tombol 1: entry efaktur (biru)
                    $actionHtml .= "<button id='" . $followup_id . "' " . $disabled_request . " class='btn btn-primary btn-block btn-sm' title='turn this entry into " . $stepLabel . "' href='javascript:void(0)' onClick=\"top.open_holdon();" . $followupLink . "\">" . $actionLabel . "</button>";
                    $actionHtml .= "</div>";

                    // Tombol 2: Simpan Efaktur (hijau) — jika inputFakturTransaksi aktif
                    if (isset($inputFakturTransaksi[$nextStepNum]["enabled"]) && ($inputFakturTransaksi[$nextStepNum]["enabled"] == true)) {
                        $dateFaktur_id_action = "dateFaktur_" . $transaksi_id;
                        $eFaktur_id_action = "eFaktur_" . $transaksi_id;
                        $simpan_id = "simpan_" . $transaksi_id;
                        if (isset($arrTopData[$row->id_top])) {
                            $dateFaktur_id_action = "dateFaktur_" . $arrTopData[$row->id_top]["transaksi_id"];
                            $eFaktur_id_action = "eFaktur_" . $arrTopData[$row->id_top]["transaksi_id"];
                            $simpan_id = "simpan_" . $arrTopData[$row->id_top]["transaksi_id"];
                        }
                        $targetFollowupLinkFaktur = $inputFakturTransaksi[$nextStepNum]["prePreviewLink"];
                        $action_label_button = $inputFakturTransaksi[$nextStepNum]["action_label_button"];
                        $followupLinkFaktur = "top.$('#result').load('" . MODUL_PATH . "$targetFollowupLinkFaktur/$jenisTr/" . $transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "?dateFaktur='+document.getElementById('" . $dateFaktur_id_action . "').value+'&eFaktur='+document.getElementById('" . $eFaktur_id_action . "').value);";
                        $actionHtml .= "<button id='" . $simpan_id . "' type='button' class='btn btn-success btn-block btn-sm' title='simpan Faktur PPN' " . $disabled_request . " style='margin-top:5px;' href='javascript:void(0)' onClick=\"if(confirm('Tanggal dan Nomer Faktur akan disimpan. Klik OK untuk melanjutkan.')==1){top.open_holdon();" . $followupLinkFaktur . "}\">" . $action_label_button . "</button>";
                    }

                    // Tombol 3: Reject 1 step (merah)
                    $link_reject_npwp = MODUL_PATH . "FollowUp/followupPrePreview/$jenisTr/" . $transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "/?getmode=reject";
                    $actionHtml .= "<button class='btn btn-danger btn-block btn-xs' style='margin-top:5px;' href='javascript:void(0)' onclick=\"document.getElementById('result').src='" . $link_reject_npwp . "'\">Reject 1 step</button>";

                    // Tombol 4: Simpan dan Approve (hijau) — jika inputFakturTransaksiApprove aktif
                    if (isset($inputFakturTransaksiApprove[1]["enabled"]) && ($inputFakturTransaksiApprove[1]["enabled"] == true)) {
                        if (isset($arrTopData[$row->id_top])) {
                            $transaksi_id_approve = $arrTopData[$row->id_top]["transaksi_id"];
                            $dateFaktur_id_approve = "dateFaktur_" . $transaksi_id_approve;
                            $eFaktur_id_approve = "eFaktur_" . $transaksi_id_approve;
                            $simpan_id_approve = "simpan_approve_" . $transaksi_id_approve;
                            $num_asal = 2;
                            $num_target = 3;
                            $disabled_approve = "";
                            $btn_approve = "btn-success";
                        }
                        else {
                            $disabled_approve = "disabled";
                            $btn_approve = "btn-default";
                            $transaksi_id_approve = "";
                            $dateFaktur_id_approve = "";
                            $eFaktur_id_approve = "";
                            $simpan_id_approve = "simpan_approve_" . $transaksi_id;
                            $num_asal = 2;
                            $num_target = 3;
                        }
                        $targetFollowupLinkFakturApprove = isset($inputFakturTransaksi[$nextStepNum]["prePreviewLink"]) ? $inputFakturTransaksi[$nextStepNum]["prePreviewLink"] : "FollowUp/followupPrePreviewFaktur";
                        $followupLinkFakturApprove = "top.$('#result').load('" . MODUL_PATH . "$targetFollowupLinkFakturApprove/$jenisTr/" . $transaksi_id_approve . "/$num_target/" . $num_asal . "?dateFaktur='+document.getElementById('" . $dateFaktur_id_approve . "').value+'&eFaktur='+document.getElementById('" . $eFaktur_id_approve . "').value);";
                        $actionHtml .= "<br><button id='" . $simpan_id_approve . "' type='button' class='btn " . $btn_approve . " btn-block btn-sm' title='simpan Faktur PPN' " . $disabled_approve . " style='margin-top:5px;' href='javascript:void(0)' onClick=\"if(confirm('Tanggal dan Nomer Faktur akan disimpan. Klik OK untuk melanjutkan.')==1){top.open_holdon();" . $followupLinkFakturApprove . "}\">Simpan dan Approve</button>";
                    }

                    $tmp[] = array(
                        'key' => "action",
                        'dataType' => 0,
                        'value' => $actionHtml,
                        'is_npwp' => 1,
                        'transaksi_id' => $transaksi_id,
                        'render_mode' => 'html',
                    );
                }
                else {
                    // Gunggungan: checkbox saja (tanpa label)
                    $tmp[] = array(
                        'key' => "action",
                        'dataType' => 0,
                        'value' => $actionLabel,
                        'checked' => isset($_SESSION[$cCode]['items'][$row->transaksi_id]) ? 1 : 0,
                        'label' => $actionLabel,
                        'onclick' => $followupLink,
                        'link' => MODUL_PATH . "FollowUp/followupMultiPrePreview/$jenisTr/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "/" . $row->jenis,
                        'stepLabel' => $stepLabel,
                        'is_npwp' => 0,
                        'transaksi_id' => $row->transaksi_id,
                        'render_mode' => 'checkbox',
                    );
                }

                // REJECT — hanya untuk Gunggungan (NPWP reject sudah di dalam action)
                $link_reject = MODUL_PATH . "FollowUp/followupPrePreview/$jenisTr/" . $row->transaksi_id . "/$nextStepNum/" . $row->sub_step_number . "/?getmode=reject";
                if ($is_npwp) {
                    // NPWP: reject sudah include di action column, kolom ini kosong
                    $tmp[] = array(
                        'key' => "reject",
                        'dataType' => 0,
                        'value' => "-",
                        'is_npwp' => 1,
                        'transaksi_id' => $row->transaksi_id,
                    );
                }
                else {
                    // Gunggungan: tombol reject di kolom terpisah
                    $tmp[] = array(
                        'key' => "reject",
                        'dataType' => 0,
                        'value' => "<button class='btn btn-danger btn-block btn-xs' href=\"javascript:void(0)\" onclick=\"document.getElementById('result').src='" . $link_reject . "'\">Reject</button>",
                        'is_npwp' => 0,
                        'transaksi_id' => $row->transaksi_id,
                    );
                }

                $arrayOnprogress[1][] = $tmp;
            }
        }

        $json_data = array(
            "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => !empty($arrayOnprogress[1]) ? $arrayOnprogress[1] : array(),
            "countBadge" => array(
                "all" => $countAll,
                "npwp" => $countNpwp,
                "gunggungan" => $countGunggungan,
            ),
            "countCabang" => $countCabang,
            "crossSearchMatch" => $crossSearchMatch,
        );

        echo json_encode($json_data);
    }
    // END OF COMPLETE REPEATED LOGIC

    public function tabGunggungan()
    {

        $data = array(
            "mode" => "tabGunggungan",
        );
        $this->load->view("transaksi", $data);
    }

    // START OF COMPLETE REPEATED LOGIC
    public function exportGunggunganStep1()
    {
        if (!isset($this->session->login['id'])) {
            redirect(base_url() . "Login");
        }

        $jenisTr = $this->jenisTr;
        $this->load->model("MdlTransaksi");
        $arrTransaksiLabel = loadConfigUiMasterLabel();

        // Parameter Filter dari GET (atau POST)
        $filterPajak = isset($_GET['filter_pajak']) ? trim($_GET['filter_pajak']) : (isset($_POST['filter_pajak']) ? trim($_POST['filter_pajak']) : 'all');
        if (empty($filterPajak)) {
            $filterPajak = 'all';
        }

        $filterCabang = isset($_GET['filter_cabang']) ? trim($_GET['filter_cabang']) : (isset($_POST['filter_cabang']) ? trim($_POST['filter_cabang']) : 'all');
        if (empty($filterCabang)) {
            $filterCabang = 'all';
        }

        $dateInput = isset($_GET['date']) ? trim($_GET['date']) : (isset($_POST['date']) ? trim($_POST['date']) : '');
        $searchVal = isset($_GET['search']) ? trim($_GET['search']) : (isset($_POST['search']) ? trim($_POST['search']) : '');

        // 1. QUERY TAHAP 1: Ambil semua transaksi ID yang sesuai filter aktif via private method
        $filtersStep1 = array(
            'date' => $dateInput,
            'search' => $searchVal,
            'filter_pajak' => $filterPajak,
            'filter_cabang' => $filterCabang,
        );
        $targetTrxIds = $this->_getQueueTransaksiIds(1, array('110e', '110'), $filtersStep1);

        // 2. QUERY TAHAP 2: Ambil kolom lengkap untuk transaksi terpilih
        $tmpHist = array();
        if (sizeof($targetTrxIds) > 0) {

            $chunks = array_chunk($targetTrxIds, 500);
            foreach ($chunks as $chunkIds) {
                $this->db->select("transaksi.*, per_customers.npwp AS npwp, transaksi_data.next_substep_num, transaksi_data.next_substep_code, transaksi_data.sub_step_number, transaksi_data.transaksi_id, transaksi.oleh_id AS oleh_id, transaksi.oleh_nama AS oleh_nama, transaksi.cabang_id AS cabang_id, transaksi.dtime AS dtime");
                $this->db->from('transaksi');
                $this->db->join('transaksi_data', 'transaksi_data.transaksi_id = transaksi.id');
                $this->db->join('per_customers', 'transaksi.customers_id = per_customers.id', 'left');
                $this->db->where_in('transaksi.id', $chunkIds);
                $this->db->where('transaksi_data.trash', '0');
                $this->db->where('transaksi_data.valid_qty >', '0');
                $this->db->where('transaksi_data.sub_step_number', '1');
                $this->db->where_in('transaksi_data.next_substep_code', array('110e', '110'));
                $this->db->group_by(array('transaksi_data.transaksi_id', 'transaksi_data.next_substep_code'));
                $this->db->order_by('transaksi.id', 'desc');
                $chunkQuery = $this->db->get();
                if ($chunkQuery->num_rows() > 0) {
                    foreach ($chunkQuery->result() as $cRow) {
                        $tmpHist[] = $cRow;
                    }
                }
            }
        }

        // 3. Lookup Reference SO dan Registri Dokumen
        $arrTransID = array();
        $arrTransTopID = array();
        $arrTransMasterID = array();
        $arrAddReference = array();

        if (sizeof($tmpHist) > 0) {
            foreach ($tmpHist as $row) {
                $arrTransID[] = $row->transaksi_id;
                if (isset($row->id_top) && $row->id_top > 0) {
                    $arrTransTopID[] = $row->id_top;
                }
                if (isset($row->id_master) && $row->id_master > 0) {
                    $arrTransMasterID[] = $row->id_master;
                }
            }

            if (sizeof($arrTransMasterID) > 0) {
                $masterChunks = array_chunk(array_values(array_unique($arrTransMasterID)), 500);
                foreach ($masterChunks as $mChunk) {
                    $this->db->select("id, id_master, jenis, nomer");
                    $this->db->from("transaksi");
                    $this->db->where_in("id_master", $mChunk);
                    $this->db->where_in("jenis", array("588so", "5822so", "5823so"));
                    $trTmpQuery = $this->db->get();
                    if ($trTmpQuery->num_rows() > 0) {
                        foreach ($trTmpQuery->result() as $trTmpSpec) {
                            $arrAddReference[$trTmpSpec->id_master] = array(
                                "reference_so_id" => $trTmpSpec->id,
                                "reference_so_nomer" => $trTmpSpec->nomer,
                            );
                        }
                    }
                }
            }

            $arrAllRegLookupIDs = array_values(array_unique(array_merge($arrTransID, $arrTransMasterID, $arrTransTopID)));
            $tmpReg_result = array();
            if (sizeof($arrAllRegLookupIDs) > 0) {
                $regChunks = array_chunk($arrAllRegLookupIDs, 500);
                foreach ($regChunks as $rChunk) {
                    $this->db->select("transaksi_id, main");
                    $this->db->from("transaksi_data_registry");
                    $this->db->where_in("transaksi_id", $rChunk);
                    $regQuery = $this->db->get();
                    if ($regQuery->num_rows() > 0) {
                        foreach ($regQuery->result() as $regRow) {
                            $tmpReg_result[$regRow->transaksi_id]['main'] = blobDecode($regRow->main);
                        }
                    }
                }
            }
        }

        // 4. Bangun Data Baris untuk Spreadsheet
        $rowsExport = array();
        $numb = 0;
        $totalDpp = 0;
        $totalDppPengganti = 0;
        $totalPpn = 0;
        $totalInvoice = 0;

        foreach ($tmpHist as $row) {
            if (isset($tmpReg_result[$row->transaksi_id]['main']) && is_array($tmpReg_result[$row->transaksi_id]['main'])) {
                foreach ($tmpReg_result[$row->transaksi_id]['main'] as $k => $v) {
                    if (!isset($row->$k)) {
                        $row->$k = $v;
                    }
                }
            }

            if (isset($arrAddReference[$row->id_master]) && sizeof($arrAddReference[$row->id_master]) > 0) {
                foreach ($arrAddReference[$row->id_master] as $akey => $aval) {
                    $row->$akey = $aval;
                }
            }

            $soCandidate = null;
            if (!empty($row->referenceNumberSO)) {
                $soCandidate = $row->referenceNumberSO;
            }
            elseif (!empty($row->referensi_so__nomer)) {
                $soCandidate = $row->referensi_so__nomer;
            }
            elseif (!empty($row->referensi_so_reguler__nomer)) {
                $soCandidate = $row->referensi_so_reguler__nomer;
            }
            elseif (isset($row->id_master) && isset($tmpReg_result[$row->id_master]['main'])) {
                $regMainM = $tmpReg_result[$row->id_master]['main'];
                if (!empty($regMainM['referensi_so__nomer'])) {
                    $soCandidate = $regMainM['referensi_so__nomer'];
                }
                elseif (!empty($regMainM['referensi_so_reguler__nomer'])) {
                    $soCandidate = $regMainM['referensi_so_reguler__nomer'];
                }
                elseif (!empty($regMainM['referenceNumberSO'])) {
                    $soCandidate = $regMainM['referenceNumberSO'];
                }
            }
            elseif (isset($row->id_top) && isset($tmpReg_result[$row->id_top]['main'])) {
                $regMainT = $tmpReg_result[$row->id_top]['main'];
                if (!empty($regMainT['referensi_so__nomer'])) {
                    $soCandidate = $regMainT['referensi_so__nomer'];
                }
                elseif (!empty($regMainT['referensi_so_reguler__nomer'])) {
                    $soCandidate = $regMainT['referensi_so_reguler__nomer'];
                }
                elseif (!empty($regMainT['referenceNumberSO'])) {
                    $soCandidate = $regMainT['referenceNumberSO'];
                }
            }

            $nomer_sumber_dp = isset($row->nomer_top) ? $row->nomer_top : "-";
            $refCandidate = null;
            if (!empty($row->efaktur_source) && $row->efaktur_source !== "-" && $row->efaktur_source !== "0") {
                $refCandidate = $row->efaktur_source;
            }
            elseif (isset($tmpReg_result[$row->transaksi_id]['main']['efaktur_source']) && !empty($tmpReg_result[$row->transaksi_id]['main']['efaktur_source']) && $tmpReg_result[$row->transaksi_id]['main']['efaktur_source'] !== "-" && $tmpReg_result[$row->transaksi_id]['main']['efaktur_source'] !== "0") {
                $refCandidate = $tmpReg_result[$row->transaksi_id]['main']['efaktur_source'];
            }
            elseif (!empty($row->referensi_spd_nomer) && $row->referensi_spd_nomer !== "-" && $row->referensi_spd_nomer !== "0") {
                $refCandidate = $row->referensi_spd_nomer;
            }
            elseif (isset($row->id_top) && isset($tmpReg_result[$row->id_top]['main']['efaktur_source']) && !empty($tmpReg_result[$row->id_top]['main']['efaktur_source']) && $tmpReg_result[$row->id_top]['main']['efaktur_source'] !== "-" && $tmpReg_result[$row->id_top]['main']['efaktur_source'] !== "0") {
                $refCandidate = $tmpReg_result[$row->id_top]['main']['efaktur_source'];
            }
            elseif (isset($row->id_master) && isset($tmpReg_result[$row->id_master]['main']['efaktur_source']) && !empty($tmpReg_result[$row->id_master]['main']['efaktur_source']) && $tmpReg_result[$row->id_master]['main']['efaktur_source'] !== "-" && $tmpReg_result[$row->id_master]['main']['efaktur_source'] !== "0") {
                $refCandidate = $tmpReg_result[$row->id_master]['main']['efaktur_source'];
            }

            $is_dp_trx = (strpos($nomer_sumber_dp, '4467') === 0 || (isset($row->jenis_top) && $row->jenis_top == '4467') || ($refCandidate !== null && strpos($refCandidate, '4467') === 0));

            if ($is_dp_trx) {
                $nomor_dp = (strpos($nomer_sumber_dp, '4467') === 0) ? $nomer_sumber_dp : $refCandidate;
                $refFinal = $nomor_dp;
                if (!empty($soCandidate) && strpos($soCandidate, '4467') !== 0) {
                    $soFinal = $soCandidate;
                }
                elseif (isset($row->reference_so_nomer) && !empty($row->reference_so_nomer) && $row->reference_so_nomer != 0 && strpos($row->reference_so_nomer, '4467') !== 0) {
                    $soFinal = $row->reference_so_nomer;
                }
                else {
                    $soFinal = "-";
                }
            }
            else {
                if ($refCandidate !== null) {
                    $refFinal = $refCandidate;
                }
                else {
                    $refFinal = "-";
                }
                if (!empty($row->reference_so_nomer) && $row->reference_so_nomer != 0 && strpos($row->reference_so_nomer, '4467') !== 0) {
                    $soFinal = $row->reference_so_nomer;
                }
                elseif (!empty($soCandidate) && strpos($soCandidate, '4467') !== 0) {
                    $soFinal = $soCandidate;
                }
                else {
                    $soFinal = "-";
                }
            }

            $nomer_top_ex = explode(".", $nomer_sumber_dp);
            $transaksi_label = isset($arrTransaksiLabel[$nomer_top_ex[0]]) ? $arrTransaksiLabel[$nomer_top_ex[0]] : "-";

            $is_npwp = (!empty($row->npwp) && trim($row->npwp) != '' && trim($row->npwp) != '-') ? 1 : 0;
            $jenisPajakStr = $is_npwp ? "NPWP" : "GUNGGUNGAN";

            $valDpp = isset($row->dpp_ppn) ? floatval($row->dpp_ppn) : 0;
            $valDppPengganti = isset($row->dpp_pengganti) ? floatval($row->dpp_pengganti) : 0;
            $valPpn = isset($row->new_grand_ppn) ? floatval($row->new_grand_ppn) : 0;
            $valTagihan = floatval(isset($row->tagihan) && $row->tagihan > 0 ? $row->tagihan : (isset($row->harga) ? $row->harga : 0));

            $totalDpp += $valDpp;
            $totalDppPengganti += $valDppPengganti;
            $totalPpn += $valPpn;
            $totalInvoice += $valTagihan;

            $numb++;
            $rowsExport[] = array(
                "no" => $numb,
                "dtime" => isset($row->dtime) ? $row->dtime : "-",
                "cabang_kode" => isset($row->cabang2_id) ? $row->cabang2_id : "-",
                "cabang_nama" => isset($row->cabang2_nama) ? $row->cabang2_nama : "-",
                "customers_nama" => isset($row->customers_nama) ? $row->customers_nama : "-",
                "npwp" => isset($row->npwp) ? $row->npwp : "-",
                "jenis_pajak" => $jenisPajakStr,
                "nomer_top" => isset($row->nomer_top) ? $row->nomer_top : "-",
                "so_nomer" => $soFinal,
                "ref_nomer" => $refFinal,
                "nomer" => isset($row->nomer) ? $row->nomer : "-",
                "oleh_nama" => isset($row->oleh_nama) ? $row->oleh_nama : "-",
                "dpp" => $valDpp,
                "dpp_pengganti" => $valDppPengganti,
                "ppn" => $valPpn,
                "tagihan" => $valTagihan,
                "transaksi_label" => $transaksi_label
            );
        }

        // 5. Buat File OpenXML .xlsx murni
        $tempFile = tempnam(sys_get_temp_dir(), 'efaktur_rekap_') . '.xlsx';
        if (file_exists($tempFile)) {
            @unlink($tempFile);
        }

        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            die("Gagal membuat file export XLSX");
        }

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';

        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';

        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Rekap E-Faktur Step 1" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';

        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <numFmts count="1">
    <numFmt numFmtId="164" formatCode="#,##0"/>
  </numFmts>
  <fonts count="3">
    <font><sz val="10"/><name val="Segoe UI"/></font>
    <font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Segoe UI"/></font>
    <font><b/><sz val="10"/><color rgb="FF000000"/><name val="Segoe UI"/></font>
  </fonts>
  <fills count="4">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF337AB7"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF2F4F7"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/></border>
    <border>
      <left style="thin"><color rgb="FFD3D3D3"/></left>
      <right style="thin"><color rgb="FFD3D3D3"/></right>
      <top style="thin"><color rgb="FFD3D3D3"/></top>
      <bottom style="thin"><color rgb="FFD3D3D3"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="7">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"><alignment horizontal="left" vertical="center"/></xf>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"><alignment horizontal="right" vertical="center"/></xf>
    <xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>
    <xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>
    <xf numFmtId="164" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyNumberFormat="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>
  </cellXfs>
</styleSheet>';

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <cols>
    <col min="1" max="1" width="6" customWidth="1"/>
    <col min="2" max="2" width="18" customWidth="1"/>
    <col min="3" max="3" width="12" customWidth="1"/>
    <col min="4" max="4" width="22" customWidth="1"/>
    <col min="5" max="5" width="28" customWidth="1"/>
    <col min="6" max="6" width="18" customWidth="1"/>
    <col min="7" max="7" width="14" customWidth="1"/>
    <col min="8" max="8" width="20" customWidth="1"/>
    <col min="9" max="9" width="20" customWidth="1"/>
    <col min="10" max="10" width="22" customWidth="1"/>
    <col min="11" max="11" width="18" customWidth="1"/>
    <col min="12" max="12" width="16" customWidth="1"/>
    <col min="13" max="13" width="16" customWidth="1"/>
    <col min="14" max="14" width="16" customWidth="1"/>
    <col min="15" max="15" width="16" customWidth="1"/>
    <col min="16" max="16" width="18" customWidth="1"/>
    <col min="17" max="17" width="18" customWidth="1"/>
  </cols>
  <sheetData>';

        $headers = array('No', 'Tanggal', 'Kode Cabang', 'Cabang', 'Customer', 'NPWP', 'Jenis Pajak', 'PRE-SO Number', 'SO Number', 'Referensi (DP/SPD)', 'Receipt Number', 'Person', 'DPP', 'DPP Pengganti', 'PPN', 'Total Invoice', 'Sumber Transaksi');
        $colLetters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q');

        // Header Row
        $sheetXml .= '<row r="1" ht="26" customHeight="1">';
        foreach ($headers as $idx => $h) {
            $sheetXml .= '<c r="' . $colLetters[$idx] . '1" s="1" t="inlineStr"><is><t>' . htmlspecialchars($h, ENT_QUOTES, 'UTF-8') . '</t></is></c>';
        }
        $sheetXml .= '</row>';

        // Data Rows
        $rowIdx = 2;
        foreach ($rowsExport as $r) {
            $c_dtime = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$r['dtime']), ENT_QUOTES, 'UTF-8');
            $c_cabKode = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$r['cabang_kode']), ENT_QUOTES, 'UTF-8');
            $c_cabNama = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$r['cabang_nama']), ENT_QUOTES, 'UTF-8');
            $c_custNama = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$r['customers_nama']), ENT_QUOTES, 'UTF-8');
            $c_npwp = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$r['npwp']), ENT_QUOTES, 'UTF-8');
            $c_jp = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$r['jenis_pajak']), ENT_QUOTES, 'UTF-8');
            $c_nomTop = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$r['nomer_top']), ENT_QUOTES, 'UTF-8');
            $c_soNom = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$r['so_nomer']), ENT_QUOTES, 'UTF-8');
            $c_refNom = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$r['ref_nomer']), ENT_QUOTES, 'UTF-8');
            $c_nom = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$r['nomer']), ENT_QUOTES, 'UTF-8');
            $c_oleh = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$r['oleh_nama']), ENT_QUOTES, 'UTF-8');
            $c_trxLabel = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$r['transaksi_label']), ENT_QUOTES, 'UTF-8');

            $sheetXml .= '<row r="' . $rowIdx . '" ht="20" customHeight="1">';
            $sheetXml .= '<c r="A' . $rowIdx . '" s="2" t="inlineStr"><is><t>' . $r['no'] . '</t></is></c>';
            $sheetXml .= '<c r="B' . $rowIdx . '" s="2" t="inlineStr"><is><t>' . $c_dtime . '</t></is></c>';
            $sheetXml .= '<c r="C' . $rowIdx . '" s="2" t="inlineStr"><is><t>' . $c_cabKode . '</t></is></c>';
            $sheetXml .= '<c r="D' . $rowIdx . '" s="0" t="inlineStr"><is><t>' . $c_cabNama . '</t></is></c>';
            $sheetXml .= '<c r="E' . $rowIdx . '" s="0" t="inlineStr"><is><t>' . $c_custNama . '</t></is></c>';
            $sheetXml .= '<c r="F' . $rowIdx . '" s="2" t="inlineStr"><is><t>' . $c_npwp . '</t></is></c>';
            $sheetXml .= '<c r="G' . $rowIdx . '" s="2" t="inlineStr"><is><t>' . $c_jp . '</t></is></c>';
            $sheetXml .= '<c r="H' . $rowIdx . '" s="0" t="inlineStr"><is><t>' . $c_nomTop . '</t></is></c>';
            $sheetXml .= '<c r="I' . $rowIdx . '" s="0" t="inlineStr"><is><t>' . $c_soNom . '</t></is></c>';
            $sheetXml .= '<c r="J' . $rowIdx . '" s="0" t="inlineStr"><is><t>' . $c_refNom . '</t></is></c>';
            $sheetXml .= '<c r="K' . $rowIdx . '" s="2" t="inlineStr"><is><t>' . $c_nom . '</t></is></c>';
            $sheetXml .= '<c r="L' . $rowIdx . '" s="0" t="inlineStr"><is><t>' . $c_oleh . '</t></is></c>';
            $sheetXml .= '<c r="M' . $rowIdx . '" s="4"><v>' . $r['dpp'] . '</v></c>';
            $sheetXml .= '<c r="N' . $rowIdx . '" s="4"><v>' . $r['dpp_pengganti'] . '</v></c>';
            $sheetXml .= '<c r="O' . $rowIdx . '" s="4"><v>' . $r['ppn'] . '</v></c>';
            $sheetXml .= '<c r="P' . $rowIdx . '" s="4"><v>' . $r['tagihan'] . '</v></c>';
            $sheetXml .= '<c r="Q' . $rowIdx . '" s="0" t="inlineStr"><is><t>' . $c_trxLabel . '</t></is></c>';
            $sheetXml .= '</row>';
            $rowIdx++;
        }

        // Summary / Total Row
        if ($numb > 0) {
            $sheetXml .= '<row r="' . $rowIdx . '" ht="24" customHeight="1">';
            $sheetXml .= '<c r="A' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
            $sheetXml .= '<c r="B' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
            $sheetXml .= '<c r="C' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
            $sheetXml .= '<c r="D' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
            $sheetXml .= '<c r="E' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
            $sheetXml .= '<c r="F' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
            $sheetXml .= '<c r="G' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
            $sheetXml .= '<c r="H' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
            $sheetXml .= '<c r="I' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
            $sheetXml .= '<c r="J' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
            $sheetXml .= '<c r="K' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
            $sheetXml .= '<c r="L' . $rowIdx . '" s="5" t="inlineStr"><is><t>TOTAL</t></is></c>';
            $sheetXml .= '<c r="M' . $rowIdx . '" s="6"><v>' . $totalDpp . '</v></c>';
            $sheetXml .= '<c r="N' . $rowIdx . '" s="6"><v>' . $totalDppPengganti . '</v></c>';
            $sheetXml .= '<c r="O' . $rowIdx . '" s="6"><v>' . $totalPpn . '</v></c>';
            $sheetXml .= '<c r="P' . $rowIdx . '" s="6"><v>' . $totalInvoice . '</v></c>';
            $sheetXml .= '<c r="Q' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
            $sheetXml .= '</row>';
        }

        $sheetXml .= '</sheetData></worksheet>';

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
        $zip->addFromString('xl/workbook.xml', $workbook);
        $zip->addFromString('xl/styles.xml', $styles);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        // 6. Send Download Headers
        $periodePart = ($dateInput != '') ? str_replace('-', '_', $dateInput) : date('Y_m_d');
        $filterPart = ($filterPajak != 'all') ? '_' . strtoupper($filterPajak) : '';

        $cabangPart = '';
        if ($filterCabang != 'all') {
            $this->db->select('id, nama, kode_cabang');
            $this->db->from('per_cabang');
            $this->db->where('id', $filterCabang);
            $cabQuery = $this->db->get();
            if ($cabQuery && $cabQuery->num_rows() > 0) {
                $cabRow = $cabQuery->row();
                $cleanKode = preg_replace('/[^A-Za-z0-9]/', '', $cabRow->kode_cabang);
                $cleanNama = preg_replace('/[^A-Za-z0-9]+/', '_', trim(str_ireplace('CABANG', '', $cabRow->nama)));
                $cleanNama = trim($cleanNama, '_');
                $cabangPart = '_' . (!empty($cleanKode) ? $cleanKode . '_' . $cleanNama : $cleanNama);
            } else {
                $cabangPart = '_CAB_' . $filterCabang;
            }
        }
        $filename = 'Rekap_EFaktur_Step1_' . $periodePart . $filterPart . $cabangPart . '.xlsx';

        // Bersihkan seluruh output buffer bertingkat agar binary stream bersih 100%
        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate');
        header('Pragma: public');
        readfile($tempFile);
        @unlink($tempFile);
        exit();
    }
    // END OF COMPLETE REPEATED LOGIC

    // START OF COMPLETE REPEATED LOGIC
    public function exportEntryEfakturStep2()
    {
        if (!isset($this->session->login['id'])) {
            redirect(base_url() . "Login");
        }

        $jenisTr = $this->jenisTr;
        $divId = isset($this->session->login['div_id']) ? $this->session->login['div_id'] : '18';

        // Parameter Filter dari GET atau POST
        $dateInput = isset($_GET['date']) ? trim($_GET['date']) : (isset($_POST['date']) ? trim($_POST['date']) : '');
        $searchVal = isset($_GET['kw']) ? trim($_GET['kw']) : (isset($_GET['search']) ? trim($_GET['search']) : (isset($_POST['kw']) ? trim($_POST['kw']) : ''));

        // 1. Ambil ID Transaksi Step 2 via private method
        $filtersStep2 = array(
            'date' => $dateInput,
            'search' => $searchVal,
        );
        $allTrIds = $this->_getQueueTransaksiIds(2, '110', $filtersStep2);

        // 2. Query Detail Transaksi
        $tmpHist = array();
        if (sizeof($allTrIds) > 0) {
            $chunks = array_chunk($allTrIds, 500);
            foreach ($chunks as $chunk) {
                $this->db->select("transaksi.*, per_customers.npwp AS npwp, 
                    transaksi_data.next_substep_num, transaksi_data.next_substep_code, transaksi_data.sub_step_number, transaksi_data.transaksi_id,
                    transaksi.oleh_id AS oleh_id, transaksi.oleh_nama AS oleh_nama, transaksi.cabang_id AS cabang_id, transaksi.dtime AS dtime");
                $this->db->from('transaksi');
                $this->db->join('transaksi_data', 'transaksi_data.transaksi_id = transaksi.id');
                $this->db->join('per_customers', 'transaksi.customers_id = per_customers.id', 'left');
                $this->db->where_in('transaksi.id', $chunk);
                $this->db->where('transaksi_data.trash', '0');
                $this->db->where('transaksi_data.valid_qty >', 0);
                $this->db->where('transaksi_data.sub_step_number', '2');
                $this->db->where('transaksi_data.next_substep_code', '110');
                $this->db->group_by(array('transaksi_data.transaksi_id', 'transaksi_data.next_substep_code'));
                $this->db->order_by('transaksi.id', 'desc');

                $qDetail = $this->db->get();
                if ($qDetail->num_rows() > 0) {
                    foreach ($qDetail->result() as $rowItem) {
                        $tmpHist[] = $rowItem;
                    }
                }
            }
        }

        // 3. Lookup Reference SO, Registri Dokumen, dan Top Data (Faktur/Tanggal Pairing)
        $arrTransID = array();
        $arrTransTopID = array();
        $arrTransMasterID = array();
        $arrAddReference = array();
        $arrTopData = array();

        if (sizeof($tmpHist) > 0) {
            foreach ($tmpHist as $row) {
                $arrTransID[] = $row->transaksi_id;
                if (isset($row->id_top) && $row->id_top > 0) {
                    $arrTransTopID[] = $row->id_top;
                }
                if (isset($row->id_master) && $row->id_master > 0) {
                    $arrTransMasterID[] = $row->id_master;
                }
            }

            if (sizeof($arrTransMasterID) > 0) {
                $masterChunks = array_chunk(array_values(array_unique($arrTransMasterID)), 500);
                foreach ($masterChunks as $mChunk) {
                    $this->db->select("id, id_master, id_top, jenis, nomer, efaktur, efaktur_dtime, trash_4");
                    $this->db->from("transaksi");
                    $this->db->where_in("id_master", $mChunk);
                    $this->db->where_in("jenis", array("588so", "5822so", "5823so", "110e"));
                    $trTmpQuery = $this->db->get();
                    if ($trTmpQuery->num_rows() > 0) {
                        foreach ($trTmpQuery->result() as $trTmpSpec) {
                            if (in_array($trTmpSpec->jenis, array("588so", "5822so", "5823so"))) {
                                $arrAddReference[$trTmpSpec->id_master] = array(
                                    "reference_so_id" => $trTmpSpec->id,
                                    "reference_so_nomer" => $trTmpSpec->nomer,
                                );
                            }
                            elseif ($trTmpSpec->jenis == "110e" && $trTmpSpec->trash_4 == 0) {
                                $arrTopData[$trTmpSpec->id_top] = array(
                                    "transaksi_id" => $trTmpSpec->id,
                                    "efaktur" => $trTmpSpec->efaktur,
                                    "efaktur_dtime" => $trTmpSpec->efaktur_dtime,
                                    "eFaktur" => $trTmpSpec->efaktur,
                                    "dateFaktur" => $trTmpSpec->efaktur_dtime,
                                );
                            }
                        }
                    }
                }
            }

            $arrAllRegLookupIDs = array_values(array_unique(array_merge($arrTransID, $arrTransMasterID, $arrTransTopID)));
            $tmpReg_result = array();
            if (sizeof($arrAllRegLookupIDs) > 0) {
                $regChunks = array_chunk($arrAllRegLookupIDs, 500);
                foreach ($regChunks as $rChunk) {
                    $this->db->select("transaksi_id, main");
                    $this->db->from("transaksi_data_registry");
                    $this->db->where_in("transaksi_id", $rChunk);
                    $regQuery = $this->db->get();
                    if ($regQuery->num_rows() > 0) {
                        foreach ($regQuery->result() as $regRow) {
                            $tmpReg_result[$regRow->transaksi_id]['main'] = blobDecode($regRow->main);
                        }
                    }
}
            }
        }

        // 4. Inisialisasi Berkas Spreadsheet (ZipArchive Native XML)
        $tempFile = tempnam(sys_get_temp_dir(), 'efaktur_step2_');
        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            die("Gagal membuat arsip spreadsheet sementara.");
        }

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';

        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';

        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>';

        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Rekap Entry e-Faktur Step 2" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>';

        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <numFmts count="1">
    <numFmt numFmtId="164" formatCode="#,##0"/>
  </numFmts>
  <fonts count="3">
    <font><sz val="10"/><name val="Segoe UI"/></font>
    <font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Segoe UI"/></font>
    <font><b/><sz val="10"/><color rgb="FF000000"/><name val="Segoe UI"/></font>
  </fonts>
  <fills count="4">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF2E7D32"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF2F4F7"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/></border>
    <border>
      <left style="thin"><color rgb="FFD3D3D3"/></left>
      <right style="thin"><color rgb="FFD3D3D3"/></right>
      <top style="thin"><color rgb="FFD3D3D3"/></top>
      <bottom style="thin"><color rgb="FFD3D3D3"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>
  </cellStyleXfs>
  <cellXfs count="7">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"><alignment horizontal="left" vertical="center"/></xf>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"><alignment horizontal="right" vertical="center"/></xf>
    <xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1"><alignment horizontal="right" vertical="center"/></xf>
    <xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>
    <xf numFmtId="164" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyNumberFormat="1" applyBorder="1"><alignment horizontal="right" vertical="center"/></xf>
  </cellXfs>
</styleSheet>';

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <cols>
    <col min="1" max="1" width="6" customWidth="1"/>
    <col min="2" max="2" width="18" customWidth="1"/>
    <col min="3" max="3" width="12" customWidth="1"/>
    <col min="4" max="4" width="20" customWidth="1"/>
    <col min="5" max="5" width="28" customWidth="1"/>
    <col min="6" max="6" width="18" customWidth="1"/>
    <col min="7" max="7" width="20" customWidth="1"/>
    <col min="8" max="8" width="20" customWidth="1"/>
    <col min="9" max="9" width="18" customWidth="1"/>
    <col min="10" max="10" width="16" customWidth="1"/>
    <col min="11" max="11" width="16" customWidth="1"/>
    <col min="12" max="12" width="16" customWidth="1"/>
    <col min="13" max="13" width="16" customWidth="1"/>
    <col min="14" max="14" width="18" customWidth="1"/>
    <col min="15" max="15" width="16" customWidth="1"/>
    <col min="16" max="16" width="22" customWidth="1"/>
  </cols>
  <sheetData>';

        $headers = array('No', 'Tanggal', 'Kode Cabang', 'Cabang', 'Customer', 'NPWP', 'PRE-SO Number', 'SO Number', 'Receipt / Entry Number', 'Person', 'DPP', 'DPP Pengganti', 'PPN', 'Total Amount', 'Tgl Faktur', 'Nomer Faktur');
        $colLetters = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P');

        $sheetXml .= '<row r="1" ht="26" customHeight="1">';
        foreach ($headers as $idx => $h) {
            $sheetXml .= '<c r="' . $colLetters[$idx] . '1" s="1" t="inlineStr"><is><t>' . htmlspecialchars($h, ENT_QUOTES, 'UTF-8') . '</t></is></c>';
        }
        $sheetXml .= '</row>';

        $rowIdx = 2;
        $totalDpp = 0;
        $totalDppPengganti = 0;
        $totalPpn = 0;
        $totalAmount = 0;

        foreach ($tmpHist as $row) {
            if (isset($tmpReg_result[$row->transaksi_id]['main']) && is_array($tmpReg_result[$row->transaksi_id]['main'])) {
                foreach ($tmpReg_result[$row->transaksi_id]['main'] as $k => $v) {
                    if (!isset($row->$k)) {
                        $row->$k = $v;
                    }
                }
            }

            if (isset($arrAddReference[$row->id_master]) && sizeof($arrAddReference[$row->id_master]) > 0) {
                foreach ($arrAddReference[$row->id_master] as $akey => $aval) {
                    $row->$akey = $aval;
                }
            }

            // SO Candidate
            $soCandidate = "-";
            if (!empty($row->reference_so_nomer)) {
                $soCandidate = $row->reference_so_nomer;
            }
            elseif (!empty($row->referenceNumberSO)) {
                $soCandidate = $row->referenceNumberSO;
            }
            elseif (!empty($row->referensi_so__nomer)) {
                $soCandidate = $row->referensi_so__nomer;
            }
            elseif (!empty($row->referensi_so_reguler__nomer)) {
                $soCandidate = $row->referensi_so_reguler__nomer;
            }
            elseif (isset($row->id_master) && isset($tmpReg_result[$row->id_master]['main'])) {
                $regMainM = $tmpReg_result[$row->id_master]['main'];
                if (!empty($regMainM['referensi_so__nomer'])) {
                    $soCandidate = $regMainM['referensi_so__nomer'];
                }
                elseif (!empty($regMainM['referensi_so_reguler__nomer'])) {
                    $soCandidate = $regMainM['referensi_so_reguler__nomer'];
                }
                elseif (!empty($regMainM['referenceNumberSO'])) {
                    $soCandidate = $regMainM['referenceNumberSO'];
                }
            }

            // Customer Name & NPWP
            $customerName = "-";
            if (!empty($row->customers_nama)) {
                $customerName = $row->customers_nama;
            }
            elseif (!empty($row->customer_nama) && $row->customer_nama !== "0") {
                $customerName = $row->customer_nama;
            }
            elseif (!empty($row->customerName) && $row->customerName !== "0") {
                $customerName = $row->customerName;
            }

            $npwpVal = "-";
            if (!empty($row->npwp) && $row->npwp !== "0") {
                $npwpVal = $row->npwp;
            }

            // Nilai Finansial
            $valDpp = isset($row->dpp_ppn) ? floatval($row->dpp_ppn) : 0;
            $valDppPengganti = isset($row->dpp_pengganti) ? floatval($row->dpp_pengganti) : 0;
            $valPpn = isset($row->new_grand_ppn) ? floatval($row->new_grand_ppn) : 0;
            $valAmount = floatval(isset($row->tagihan) && $row->tagihan > 0 ? $row->tagihan : (isset($row->harga) ? $row->harga : 0));

            $totalDpp += $valDpp;
            $totalDppPengganti += $valDppPengganti;
            $totalPpn += $valPpn;
            $totalAmount += $valAmount;

            // Nomer & Tanggal Faktur (jika draft ada)
            $fakturNo = "-";
            $fakturDate = "-";
            if (isset($arrTopData[$row->id_top])) {
                if (!empty($arrTopData[$row->id_top]['efaktur'])) {
                    $fakturNo = $arrTopData[$row->id_top]['efaktur'];
                }
                if (!empty($arrTopData[$row->id_top]['efaktur_dtime'])) {
                    $fakturDate = $arrTopData[$row->id_top]['efaktur_dtime'];
                }
            }
            elseif (!empty($row->efaktur) && $row->efaktur !== "-") {
                $fakturNo = $row->efaktur;
            }
            elseif (!empty($row->eFaktur) && $row->eFaktur !== "-") {
                $fakturNo = $row->eFaktur;
            }

            if ($fakturDate === "-" && !empty($row->efaktur_dtime)) {
                $fakturDate = $row->efaktur_dtime;
            }
            elseif ($fakturDate === "-" && !empty($row->dateFaktur)) {
                $fakturDate = $row->dateFaktur;
            }

            $c_dtime = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$row->dtime), ENT_QUOTES, 'UTF-8');
            $c_cabKode = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$row->cabang2_id), ENT_QUOTES, 'UTF-8');
            $c_cabNama = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)(isset($row->cabang2_nama) ? $row->cabang2_nama : "-")), ENT_QUOTES, 'UTF-8');
            $c_custNama = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$customerName), ENT_QUOTES, 'UTF-8');
            $c_npwp = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$npwpVal), ENT_QUOTES, 'UTF-8');
            $c_nomTop = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)(isset($row->nomer_top) ? $row->nomer_top : "-")), ENT_QUOTES, 'UTF-8');
            $c_soNom = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$soCandidate), ENT_QUOTES, 'UTF-8');
            $c_nom = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)(isset($row->nomer) ? $row->nomer : "-")), ENT_QUOTES, 'UTF-8');
            $c_oleh = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)(isset($row->oleh_nama) ? $row->oleh_nama : "-")), ENT_QUOTES, 'UTF-8');
            $c_fakturDate = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$fakturDate), ENT_QUOTES, 'UTF-8');
            $c_fakturNo = htmlspecialchars(preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string)$fakturNo), ENT_QUOTES, 'UTF-8');

            $sheetXml .= '<row r="' . $rowIdx . '" ht="20" customHeight="1">';
            $sheetXml .= '<c r="A' . $rowIdx . '" s="2" t="inlineStr"><is><t>' . ($rowIdx - 1) . '</t></is></c>';
            $sheetXml .= '<c r="B' . $rowIdx . '" s="2" t="inlineStr"><is><t>' . $c_dtime . '</t></is></c>';
            $sheetXml .= '<c r="C' . $rowIdx . '" s="2" t="inlineStr"><is><t>' . $c_cabKode . '</t></is></c>';
            $sheetXml .= '<c r="D' . $rowIdx . '" s="0" t="inlineStr"><is><t>' . $c_cabNama . '</t></is></c>';
            $sheetXml .= '<c r="E' . $rowIdx . '" s="0" t="inlineStr"><is><t>' . $c_custNama . '</t></is></c>';
            $sheetXml .= '<c r="F' . $rowIdx . '" s="2" t="inlineStr"><is><t>' . $c_npwp . '</t></is></c>';
            $sheetXml .= '<c r="G' . $rowIdx . '" s="0" t="inlineStr"><is><t>' . $c_nomTop . '</t></is></c>';
            $sheetXml .= '<c r="H' . $rowIdx . '" s="0" t="inlineStr"><is><t>' . $c_soNom . '</t></is></c>';
            $sheetXml .= '<c r="I' . $rowIdx . '" s="2" t="inlineStr"><is><t>' . $c_nom . '</t></is></c>';
            $sheetXml .= '<c r="J' . $rowIdx . '" s="0" t="inlineStr"><is><t>' . $c_oleh . '</t></is></c>';
            $sheetXml .= '<c r="K' . $rowIdx . '" s="4"><v>' . $valDpp . '</v></c>';
            $sheetXml .= '<c r="L' . $rowIdx . '" s="4"><v>' . $valDppPengganti . '</v></c>';
            $sheetXml .= '<c r="M' . $rowIdx . '" s="4"><v>' . $valPpn . '</v></c>';
            $sheetXml .= '<c r="N' . $rowIdx . '" s="4"><v>' . $valAmount . '</v></c>';
            $sheetXml .= '<c r="O' . $rowIdx . '" s="2" t="inlineStr"><is><t>' . $c_fakturDate . '</t></is></c>';
            $sheetXml .= '<c r="P' . $rowIdx . '" s="0" t="inlineStr"><is><t>' . $c_fakturNo . '</t></is></c>';
            $sheetXml .= '</row>';
            $rowIdx++;
        }

        // Summary Row
        $sheetXml .= '<row r="' . $rowIdx . '" ht="24" customHeight="1">';
        $sheetXml .= '<c r="A' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
        $sheetXml .= '<c r="B' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
        $sheetXml .= '<c r="C' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
        $sheetXml .= '<c r="D' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
        $sheetXml .= '<c r="E' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
        $sheetXml .= '<c r="F' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
        $sheetXml .= '<c r="G' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
        $sheetXml .= '<c r="H' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
        $sheetXml .= '<c r="I' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
        $sheetXml .= '<c r="J' . $rowIdx . '" s="5" t="inlineStr"><is><t>TOTAL</t></is></c>';
        $sheetXml .= '<c r="K' . $rowIdx . '" s="6"><v>' . $totalDpp . '</v></c>';
        $sheetXml .= '<c r="L' . $rowIdx . '" s="6"><v>' . $totalDppPengganti . '</v></c>';
        $sheetXml .= '<c r="M' . $rowIdx . '" s="6"><v>' . $totalPpn . '</v></c>';
        $sheetXml .= '<c r="N' . $rowIdx . '" s="6"><v>' . $totalAmount . '</v></c>';
        $sheetXml .= '<c r="O' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
        $sheetXml .= '<c r="P' . $rowIdx . '" s="5" t="inlineStr"><is><t></t></is></c>';
        $sheetXml .= '</row>';

        $sheetXml .= '</sheetData></worksheet>';

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);
        $zip->addFromString('xl/workbook.xml', $workbook);
        $zip->addFromString('xl/styles.xml', $styles);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        // Send Download Headers
        $periodePart = ($dateInput != '') ? str_replace('-', '_', $dateInput) : date('Y_m_d');
        $filename = 'Rekap_Entry_EFaktur_Step2_' . $periodePart . '.xlsx';

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0, no-cache, no-store, must-revalidate');
        header('Pragma: public');
        readfile($tempFile);
        @unlink($tempFile);
        exit();
    }
    // END OF COMPLETE REPEATED LOGIC

    // START OF COMPLETE REPEATED LOGIC
    /**
     * Helper sentralisasi query ID transaksi antrean pipeline pajak
     * Mengeliminasi duplikasi query di export Step 1 dan export Step 2
     *
     * @param int|string $stepNumber Nomor step (1 atau 2)
     * @param string|array $substepCode Next substep code ('110' atau array('110e', '110'))
     * @param array $filters Filter tambahan: date/tahun/bulan, search, filter_pajak, filter_cabang
     * @return array List of integer transaksi_id
     */
    private function _getQueueTransaksiIds($stepNumber, $substepCode, $filters = array())
    {
        $divId = isset($this->session->login['div_id']) ? $this->session->login['div_id'] : '18';
        $hasSearch = !empty($filters['search']);
        $filterPajak = isset($filters['filter_pajak']) ? $filters['filter_pajak'] : 'all';
        $filterCabang = isset($filters['filter_cabang']) ? $filters['filter_cabang'] : 'all';

        $this->db->select("transaksi.id AS tr_id");
        $this->db->from('transaksi');

        if (is_array($substepCode)) {
            $this->db->join('transaksi_data', 'transaksi_data.transaksi_id = transaksi.id AND transaksi_data.trash = "0" AND transaksi_data.valid_qty > 0 AND transaksi_data.sub_step_number = "' . $stepNumber . '" AND transaksi_data.next_substep_code IN ("' . implode('","', $substepCode) . '")');
        } else {
            $this->db->join('transaksi_data', 'transaksi_data.transaksi_id = transaksi.id AND transaksi_data.trash = "0" AND transaksi_data.valid_qty > 0 AND transaksi_data.sub_step_number = "' . $stepNumber . '" AND transaksi_data.next_substep_code = "' . $substepCode . '"');
        }

        if ($hasSearch || $filterPajak != 'all') {
            $this->db->join('per_customers', 'transaksi.customers_id = per_customers.id', 'left');
        }

        $this->db->where('transaksi.status', '1');
        $this->db->where('transaksi.trash', '0');
        $this->db->where('transaksi.link_id', '0');
        $this->db->where('transaksi.div_id', $divId);
        $this->db->where('transaksi.jenis_top', '110r');

        if (isset($this->session->login['employee_type']) && $this->session->login['employee_type'] == "employee_freelance") {
            $this->db->where("transaksi.seller_id", $this->session->login['id']);
        }

        // Parsing filter tanggal jika dikirim berupa string YYYY-MM
        $dateTahun = null;
        $dateBulan = null;
        if (!empty($filters['date'])) {
            $dateEx = explode("-", trim($filters['date']));
            if (count($dateEx) >= 2) {
                $dateTahun = intval($dateEx[0]);
                $dateBulan = intval($dateEx[1]);
            }
        } elseif (isset($filters['tahun']) && isset($filters['bulan'])) {
            $dateTahun = intval($filters['tahun']);
            $dateBulan = intval($filters['bulan']);
        }

        if ($dateTahun !== null && $dateBulan !== null) {
            $this->db->where("YEAR(transaksi.dtime) = '$dateTahun' AND MONTH(transaksi.dtime) = '$dateBulan'");
        }

        if ($filterPajak == 'npwp') {
            $this->db->where("per_customers.npwp IS NOT NULL AND TRIM(per_customers.npwp) != '' AND TRIM(per_customers.npwp) != '-'");
        } elseif ($filterPajak == 'gunggungan') {
            $this->db->where("(per_customers.npwp IS NULL OR TRIM(per_customers.npwp) = '' OR TRIM(per_customers.npwp) = '-')");
        }

        if ($filterCabang != 'all') {
            $this->db->where('transaksi.cabang2_id', $filterCabang);
        }

        if ($hasSearch) {
            $key = trim($filters['search']);
            $key = str_replace(array('INVPOS.', 'INVPOS'), array('5822r.', '5822r'), $key);
            $key = preg_replace('/\s+/', ' ', $key);
            $arrKey = explode(" ", $key);

            $this->db->group_start();
            foreach ($arrKey as $k) {
                if (trim($k) != '') {
                    $this->db->group_start();
                    $this->db->like('per_customers.npwp', $k);
                    $this->db->or_like('transaksi.customers_nama', $k);
                    $this->db->or_like('transaksi.dtime', $k);
                    $this->db->or_like('transaksi.nomer', $k);
                    $this->db->or_like('transaksi.nomer_top', $k);
                    $this->db->or_like('transaksi.nomer2', $k);
                    $this->db->or_like('transaksi.cabang2_nama', $k);
                    $this->db->or_like('transaksi.oleh_nama', $k);
                    $this->db->group_end();
                }
            }
            $this->db->group_end();
        }

        $this->db->group_by(array('transaksi_data.transaksi_id', 'transaksi_data.next_substep_code'));
        $this->db->order_by('transaksi.id', 'desc');

        $query = $this->db->get();
        $targetIds = array();
        if ($query && $query->num_rows() > 0) {
            foreach ($query->result() as $row) {
                $targetIds[] = $row->tr_id;
            }
        }
        return $targetIds;
    }
    // END OF COMPLETE REPEATED LOGIC

}



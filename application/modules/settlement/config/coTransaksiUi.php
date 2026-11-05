<?php
//region urusan tanggal-menanggal
// date_default_timezone_set('asia/jakarta');
// $date = new DateTime(date("Y-m-d")); // Y-m-d
// $date->add(new DateInterval('P30D'));
//$date->format('Y-m-d') . "\n";
//endregion

//tambahin filter "461ro untuk selectornota taxes 681
$config["coTransaksiUi"] = array(
    //  config penyetoran
    "759" => array(
        "icon" => "fa fa-money",
        "label" => "Settle Harian",
        "place" => "branch",
        "paymentConfig" => true,
        "isDisableMakeTrans" => true,
        "steps" => array(
            1 => array(
                "label" => "Settle Harian",
                "actionLabel" => "penyetoran",
                "source" => "",
                "target" => "759r",
                "userGroup" => "o_finance",
                "stateLabel" => "prepare by",
                "stateColor" => "#dd3300",
            ),
        ),
        "template" => "template/transaksi_payment.html",
        "selectorModel" => "MdlNota",
        "selectorFilters" => array(
            "cabang_id=placeID",
            "jenis=.582",
            "transaksi_nilai_sisa>.0",
        ),
        "selectorCaller" => "_selectorItem/selectItem",// bikin shopping cart background
        "selectorLabel" => "item",
        "selectorParamFields" => array(
            "id" => "id",
            "nama" => "nomer",
        ),
        "selectorViewedFields" => array(
            "nomer",
            "dtime",
        ),

        "selectorProcessor" => "_processSelectNota/select",
        "editHandlerMethod" => "select",
        "pihakModel" => "MdlCustomer",
        "pihakCaller" => "_selectorPihak/selectPihak",
        "pihakLabel" => "customer",
        "pihakProcessor" => "_processPihak/select",
        "shortHistoryFields" => array(
            //            "jenis_label"                => "activity",
            "dtime" => "date",
            //            "customers_nama"             => "customer",
            "nomer" => "request number",
            "details" => "detail",
            "oleh_nama" => "person",
            "nilai_bayar" => "amount",
            "cash_account_source__label" => "bank account source",
            "cash_account_target__label" => "bank account target",
            "cashMethode__label" => "target method account",
        ),
        "shortStepHistoryFields" => array(
            "jenis_label" => "activity",
            "dtime" => "date",
            "cabang2_nama" => "sender",
            "cabang_nama" => "recipient",
            "759r" => "request number",
            //            "759" => "approval number",
            //            "758r" => "request number",

            "details" => "invoice",
            "customerSetor" => "customer",
            "nilaiSetor" => "nilai",

            //            "758" => "receipt number",

            "oleh_nama" => "person",
            "nilai_bayar" => "amount",
            "cash_account_source__label" => "bank account source",
            "cash_account_target__label" => "bank account target",
            "cashMethode__label" => "target method account",
            "next_pic" => "next step otorisator",
        ),
        "shortStatusFields" => array(
            "jenis_label" => "activity",
            "dtime" => "date",
            "cabang2_nama" => "sender",
            "cabang_nama" => "recipient",
            "759r" => "request number",
            //            "759" => "approval number",
            //            "758r" => "request number",
            "758" => "receipt number",

            "oleh_nama" => "person",
            "nilai_bayar" => "amount",
            "item_fields" => "isi",
//            "nilai_bayar" => "amount",
//            "cash_account_source__label" => "bank account source",
//            "cash_account_target__label" => "bank account target",
            //            "next_pic" => "next step otorisator",
        ),
        "historyFields" => array(
            1 => array(
                "no" => "no",
                "dtime" => "tanggal",
                "cabang2_nama" => "cabang",
                "nomer" => "nomor setoran",
//                "details" => "invoice",
//                "customerSetor" => "customer",
//                "nilaiSetor" => "nilai",
                "item_fields" => "isi",
                "nilai_bayar" => "nilai setoran",
                "oleh_nama" => "penyetor",
//                "cash_account_source__label" => "bank account source",
//                "cashMethode__label" => "target method account",
//                "cash_account_target__label" => "bank account target",
                "keterangan" => "keterangan",
                "print_label" => "tool",
            ),
            2 => array(
                "no" => "no",
                "dtime" => "date",
                //            "customers_nama"             => "customer",
                "nomer" => "request number",
                "details" => "detail",
                "oleh_nama" => "person",
                "nilai_bayar" => "amount",
                "cash_account_source__label" => "bank account source",
                "cash_account_target__label" => "bank account target",
                "cashMethode__label" => "target method account",
                "keterangan" => "keterangan",
                "print_label" => "tool",
            ),
        ),
        "compactHistoryFields" => array(
//            "jenis_label" => "activity",
            "dtime" => "date",
//            "customers_nama" => "customer",
            "nomer" => "receipt number",
            "oleh_nama" => "person",
            "nilai_bayar" => "amount",
        ),
        "extHistoryFields" => array(
            1 => array(
                //                "review_details" => "id",
                "print_label" => "nomer",
            ),
        ),
        "extHistoryFields2" => array(
            1 => array(
                //                "details" => "nama",
                "details" => array(
                    "kolom" => "nama",
                    "format" => "nomer",
                ),
                "customerSetor" => array(
                    "kolom" => "extern2_nama",
                    "format" => "nama",
                ),
                "nilaiSetor" => array(
                    "kolom" => "nilai_bayar",
                    "format" => "debet",
                ),
            ),
        ),

        "selectorFields" => array("id", "nama", "satuan"),
        "pihakFields" => array("id", "nama"),
        "shoppingCart" => array(
            "initPrices" => "beli",
        ),

        "shoppingCartFields" => array(
            1 => array(
                "extern2_nama" => "Pelanggan/Penyetor",
                "nama" => "No. Nota",
                "cash_account_nama" => "Akun Kas / Bank",
                "jml" => "Qty",
            ),

        ),
        "shoppingCartFieldSrc" => array(
            "extern2_id" => "extern2_id",
            "extern2_nama" => "extern2_nama",
            "nama" => "nomer",
            "tagihan" => "tagihan",
            "terbayar" => "terbayar",
            "sisa" => "sisa",
            "cash_account" => "cash_account",
            "cash_account_nama" => "cash_account_nama",
            "dtime2"=>"dtime",
            "fulldate"=>"dtime",
        ),
        "shoppingCartNumFields" => array(
            1 => array(
                "sisa" => "Sisa Tagihan",
            ),
        ),
        "shoppingCartEditableFields" => array(),
        "shoppingCartAmountValue" => array(
            1 => "sisa",
        ),
        "shoppingCartSumFields" => array(
            1 => array(
                //                "sisa" => "debt amount",
                //                "creditAmount" => "paid using credit",
                //                "nilai_entry" => "paid using cash account",
                //                "nilai_bayar" => "total amount of payment",
                //                "new_sisa" => "remain debt (from list)",
            ),
        ),
        "shoppingCartAvoidRemove" => true,
//        "shopingCartReload" => true,
        "tagihanSrc" => "harus_bayar",
        "receiptElements" => array(
            "settlementOption" => array(
                "elementType" => "dataModel",
                "inputType" => "radio",
                "label" => "settlement tunai",
                "mdlName" => "MdlCashSetorOption",
                "mdlFilter" => array(
                    "id=option_tunai",
                ),
                "key" => "id",
                "labelSrc" => "name",
                "usedFields" => array(
                    "name" => "name",
                ),
                "editPoints" => array(1, 2, 3),
                "hideRow" => true,
                "hiddenBox" => false,
            ),
            "dummyElement" => array(
                "elementType" => "dataModel",
                "inputType" => "radio",
                "label" => "auto-validation",
                "mdlName" => "MdlDummyElement",
                //                "mdlFilter"   => array("id=pihakID"),
                "key" => "id",
                "labelSrc" => "name",
                "usedFields" => array(
                    "name" => "name",

                ),
                "editPoints" => array(1, 2, 3),
                "hideRow" => false,
                "hiddenBox" => true,
            ),
        ),
        "relativeElements" => array(
            "settlementOption" => array(
                1 => array(
                    "cash_account_target" => array(

                        "elementType" => "dataModel",
                        "inputType" => "radio",
                        "label" => "rekening tujuan",
//                        "mdlName" => "MdlBankAccount_cash_and_in",
                        "mdlName" => "MdlBankAccount_cash",
                        "mdlFilter" => array(
//                            "cabang_id=placeID",
//                            "jenis2=.1",
                        ),
                        "key" => "id",
                        "labelSrc" => "nama",
                        "labelSrcFields" => array(
                            "folders_nama", "nama", "alias",
                        ),
                        "usedFields" => array(
                            "nama" => "account number",
                            "alias" => "holder alias",

                        ),
                        "editPoints" => array(1,),
                        "noValidate" => true,
                        "hiddenBox" => true,
                        "hideRow" => true,
                    ),
                ),
            ),
        ),
        "pairMakers" => array(
            1 => array(
                "stock" => array(
                    "helperName" => "he_cek_saldo_kas",
                    "functionName" => "cekStockSaldoKas",
                    "params" => array(
                        "cabang_id" => "placeID",
                        //                        "gudang_id" => "gudangID",
                    ),
                ),
            ),
        ),
        "mainValueInjectors" => array(
            "amount" => "sisa",
            "creditAmount" => "creditAmount",
            "harus_bayar" => "harus_bayar",
        ),
        "shoppingCartRowValidators" => array(
            "pihakID" => "vendor ID",
            "pihakName" => "vendor name",
        ),
        "shoppingCartRowNumValidators" => array(
            "nilai_entry" => "amount of payment",
        ),
        "shoppingCartReferenceFields" => array(
            "fulldate" => "Tanggal",
            "jenis_label" => "Jenis penerimaan",
            "nomer" => "No. Nota",
            "nomer_top" => "No. Referensi",
            //            "refNum" => "return ref.",
            "extern2_nama" => "Pelanggan/Penyetor",

//            "tagihan" => "Jumlah Tagihan",
            //            "refValue" => "returned",
//            "terbayar" => "Sudah Dibayar",
            //            "diskon" => "discount",
            "sisa" => "Nominal Penerimaan",
            "cash_account_nama" => "Akun Kas / Bank",
            "notes" => "Keterangan",
        ),
        "shoppingCartReferenceFieldsModal" => array(
            "fulldate" => "Tanggal",
            "jenis_label" => "Jenis penerimaan",
            "nomer" => "No. Nota",
//            "nomer_top" => "No. Referensi",
            //            "refNum" => "return ref.",
            "extern2_nama" => "Pelanggan",

//            "tagihan" => "Jumlah Tagihan",
            //            "refValue" => "returned",
//            "terbayar" => "Sudah Dibayar",
            //            "diskon" => "discount",
            "sisa" => "Nominal",
//            "cash_account_nama" => "Akun Kas / Bank",
            "notes" => "Keterangan",
        ),
        "shoppingCartReferenceExternFields" => array(
            "extern_nama" => "person",
            "tagihan" => "due amount",
            "terbayar" => "paid",
            //            "diskon" => "discount",
            "sisa" => "due remain",
        ),
        "additionalRows" => array(
            "dummyElement" => array(
                "yes" => array(
                    "harus_bayar" => array(
                        "label" => "total harus disetor",
                        "defaultValue" => "(sisa-creditAmount-creditValue)",
                        "maxValue" => "(sisa-creditAmount-creditValue)",
                        "minValue" => "(sisa-creditAmount-creditValue)",
                        "keyPressAction" => "",
                        'disabled' => "disabled",
                        "addPoints" => array(1,),

                    ),
                    "nilai_entry" => array(
                        "label" => "jumlah setoran",
                        "defaultValue" => ".0",
                        "keyupAction" => "",
                        'disabled' => "disabled",
                        "addPoints" => array(1,),
                    ),
                ),
            ),
        ),
        "shoppingCartFieldValidatorsComparison" => array(
            "nilai_entry" => "sumber",
            "nilai_bayar" => "target",
        ),
        "pairRegistries" => array(
            "main", "items", "items8_sum"
        ),
        "connectTo" => "758",
        "connectoValidate" => array(
            1 => "nilai_entry",
        ),
        "replacerConnectTo" => array(
            "cabang2ID" => "-1",
            "cabang2Name" => "pusat",
            "place2ID" => "-1",
            "place2Name" => "pusat",
            "gudang2ID" => "-1",
            "gudang2Name" => "default center warehouse",
            "efaktur_source" => "nomer",//untuk ambil jika lintas cabang
            "pihakID" => "placeID",
            "pihakName" => "placeName",
        ),
        "paymentSrcLocked" => array(
            "enabled" => false,
            "notes" => "penerimaan tunai<br>belum dilakukan setoran ke bank",
        ),
        "viewDescriptionNote" => true,
        "previewCtr" => "Create",
        "canceledLabel" => array(
            1 => "Transaksi Penyetoran Kas ke Pusat nomer {nomer} telah dibatalkan oleh {cancel_name} pada {cancel_dtime}. 
                            <br>Silahkan melakukan penyetoran ulang di cabang {cabang_nama}",
        ),
        "shortItemsFields" => array(
            "extern2_nama" => array(
                "label" => "konsumen",
                "addKey" => "keterangan",
            ),
            "nama" => "nomor transaksi",
            "cash_account_nama" => "akun kas/bank",
            "nilai_bayar" => "nilai setor",
        ),
        "shortItemsFields_sub" => array(
//            "extern2_nama" => array(
//                "label" => "akun kas/bank",
//                "addKey" => "keterangan",
//            ),
//            "nama" => "nomor transaksi",
            "cash_account_nama" => "akun kas/bank",
            "nilai_setor" => "nilai setor",
        ),
        //----
        "connectToEdit" => array(
            1 => array(
                "enabled" => true,
                "connectTo" => "759re",
                "label" => "EDIT setoran kas",
            ),
        ),
        "connectToReject" => array(
            1 => array(
                "enabled" => true,
                "connectTo" => "759rrj",
                "label" => "REJECT setoran kas",
            ),
        ),
    ),
    "758" => array(
        "icon" => "fa fa-money",
        "label" => "Penerimaan Setoran Kas",
        "place" => "center",//=> "center",
        "steps" => array(
            1 => array(
                "label" => "request setoran cabang",
                "actionLabel" => "setoran kas",
                "source" => "",
// START OF COMPLETE REPEATED LOGIC
                "target" => "758r",
                "userGroup" => "o_finance",
                "stateLabel" => "pending acceptance",
// END OF COMPLETE REPEATED LOGIC
                "stateColor" => "#dd3300",
                "stateCaption" => "initiated by",
            ),
            2 => array(
                "label" => "Penerimaan Setoran Kas",
                "actionLabel" => "receive",
                "source" => "758r",
                "target" => "758",
                "userGroup" => "c_finance",
                "stateLabel" => "completed",
                "stateColor" => "#009900",
                "stateCaption" => "received by",
            ),
        ),
// START OF COMPLETE REPEATED LOGIC
        "showPreJournal" => array(
            1 => false,
            2 => false,
        ),
        "preJournalCollapsed" => array(
            2 => false,
        ),
// END OF COMPLETE REPEATED LOGIC
        "template" => "template/transaksi_payment.html",
        "selectorModel" => "MdlNota",
        "selectorFilters" => array(
            "cabang_id=placeID",
            "jenis=.582",
            "transaksi_nilai_sisa>.0",
        ),
        "selectorCaller" => "_selectorItem/selectItem",// bikin shopping cart background
        "selectorLabel" => "item",
        "selectorParamFields" => array(
            "id" => "id",
            "nama" => "nomer",
        ),
        "selectorViewedFields" => array(
            "nomer",
            "dtime",
        ),

        "selectorProcessor" => "_processSelectNota/select",
        "editHandlerMethod" => "select",
        "pihakModel" => "MdlCustomer",
        "pihakCaller" => "_selectorPihak/selectPihak",
        "pihakLabel" => "customer",
        "pihakProcessor" => "_processPihak/select",
        "shortHistoryFields" => array(
            "dtime" => "tanggal",
            "cabang2_nama" => "cabang",
            "nomer" => "nomor setoran",
//            "details" => "invoice",
//            "customerSetor" => "customer",
//            "nilaiSetor" => "nilai",
            "item_fields" => "isi",
            "nilai_bayar" => "nilai setoran",
//            "cash_account_source__label" => "bank account source",
//            "cashMethode__label" => "target method account",
//            "cash_account_target__label" => "bank account target",
            "oleh_nama" => "penyetor",
        ),
        "shortStatusFields" => array(
            "jenis_label" => "activity",
            "dtime" => "date",
            "cabang2_nama" => "sender",
            "cabang_nama" => "recipient",
            "759r" => "request number",
            //            "759" => "approval number",
            //            "758r" => "request number",
            "758" => "receipt number",

//            "oleh_nama" => "person",
            "nilai_bayar" => "amount",
            "item_fields" => "isi",
            "oleh_nama" => "penyetor",
//            "cash_account_source__label" => "bank account source",
//            "cash_account_target__label" => "bank account target",
            //            "next_pic" => "next step otorisator",
        ),
        "compactHistoryFields" => array(
            "jenis_label" => "activity",
            "dtime" => "date",
            "cabang2_nama" => "branch",
            "nomer" => "receipt number",
            "oleh_nama" => "person",
            "nilai_bayar" => "amount",
        ),
        "historyFields" => array(
            1 => array(
                "no" => "no",
                "dtime" => "tanggal",
                "cabang2_nama" => "cabang",
                "nomer" => "nomor setoran",
//                "details" => "invoice",
//                "customerSetor" => "customer",
//                "nilaiSetor" => "nilai",
                "item_fields" => "isi",
                "nilai_bayar" => "nilai setoran",
                "oleh_nama" => "penyetor",
//                "cash_account_source__label" => "bank account source",
//                "cashMethode__label" => "target method account",
//                "cash_account_target__label" => "bank account target",
                "keterangan" => "keterangan",
                "print_label" => "tool",
            ),
            2 => array(
                "no" => "no",
                "dtime" => "tanggal",
                "cabang2_nama" => "cabang",
                "nomer" => "nomor setoran",
//                "details" => "invoice",
//                "customerSetor" => "customer",
//                "nilaiSetor" => "nilai",
                "item_fields" => "isi",
                "nilai_bayar" => "nilai setoran",
                "oleh_nama" => "penyetor",
//                "cash_account_source__label" => "bank account source",
//                "cashMethode__label" => "target method account",
//                "cash_account_target__label" => "bank account target",
                "keterangan" => "keterangan",
                "print_label" => "tool",
            ),
        ),
        "extHistoryFields" => array(
            1 => array(
                //                "review_details" => "id",
                "print_label" => "nomer",
            ),
        ),
        "extHistoryFields2" => array(
            1 => array(
                //                "details" => "nama",
                "details" => array(
                    "kolom" => "nama",
                    "format" => "nomer",
                ),
                "customerSetor" => array(
                    "kolom" => "extern2_nama",
                    "format" => "nama",
                ),
                "nilaiSetor" => array(
                    "kolom" => "nilai_bayar",
                    "format" => "debet",
                ),
            ),
            2 => array(
                //                "details" => "nama",
                "details" => array(
                    "kolom" => "nama",
                    "format" => "nomer",
                ),
                "customerSetor" => array(
                    "kolom" => "extern2_nama",
                    "format" => "nama",
                ),
                "nilaiSetor" => array(
                    "kolom" => "nilai_bayar",
                    "format" => "debet",
                ),
            ),
        ),
        "selectorFields" => array("id", "nama", "satuan"),
        "pihakFields" => array("id", "nama"),
        "shoppingCart" => array(
            "initPrices" => "beli",
        ),

        "shoppingCartFields" => array(
            1 => array(
                "extern2_nama" => "customer",
                "nama" => "item name",
                "cash_account_nama" => "akun kas/bank",
            ),

        ),
        "shoppingCartFieldSrc" => array(
            "extern2_nama" => "customer",
            "nama" => "nomer",
            "tagihan" => "tagihan",
            "terbayar" => "terbayar",
            "sisa" => "sisa",
            "nilai_bayar" => "nilai_bayar",
            "dtime2" => "dtime",

        ),
        "shoppingCartNumFields" => array(
            1 => array(
                "sisa" => "disetor",
            ),
            2 => array(
                "sisa" => "disetor",
            ),
        ),
        "shoppingCartNumFields3" => array(
            1 => array(
                "nilai_setor" => "disetor",
            ),
            2 => array(
                "nilai_setor" => "disetor",
            ),
        ),
        "shoppingCartEditableFields" => array(),
        "shoppingCartAmountValue" => array(
            2 => "nilai_entry",
        ),
        "shoppingCartSumFields" => array(
            2 => array(
                "subtotal" => "total",
            ),
        ),
        "shoppingCartSumFields3" => array(
            2 => array(
                "subtotal" => "total",
            ),
        ),
        "shoppingCartHideSubamount" => array(
            1 => true,
            2 => true,
        ),
        "shoppingCartAvoidRemove" => true,
        "tagihanSrc" => "harus_bayar",
        "receiptElements" => array(
            //            "cash_account" => array(
            //                "elementType" => "dataModel",
            //                "inputType" => "radio",
            //                "label" => "branch cash account",
            //                "mdlName" => "MdlBankAccount_out",
            //                "mdlFilter" => array(
            //                    "bank.cabang_id=placeID",
            //                ),
            //                "key" => "id",
            //                "labelSrc" => "nama",
            //                "usedFields" => array(
            //                    "nama" => "account",
            //                ),
            //                "editPoints" => array(1),
            //                "noValidatate" =>true,
            //            ),
            //            "cash_account_tujuan" => array(
            //                "elementType" => "dataModel",
            //                "inputType" => "radio",
            //                "label" => "center cash account",
            //                "mdlName" => "MdlBankAccount_in",
            //                "mdlFilter" => array(
            //                    "cabang_id=place2ID",
            //                ),
            //                "key" => "id",
            //                "labelSrc" => "nama",
            //                "usedFields" => array(
            //                    "nama" => "account",
            //                ),
            //                "editPoints" => array(1, 2),
            //                "noValidatate" =>true,
            //            ),
            //            "dummyElement" => array(
            //                "elementType" => "dataModel",
            //                "inputType" => "radio",
            //                "label" => "auto-validation",
            //                "mdlName" => "MdlDummyElement",
            //                //                "mdlFilter"   => array("id=pihakID"),
            //                "key" => "id",
            //                "labelSrc" => "name",
            //                "usedFields" => array(
            //                    "name" => "name",
            //
            //                ),
            //                "editPoints" => array(1, 2, 3),
            //                "noValidatate" =>true,
            //            ),
        ),
        "pairMakers" => array(
            1 => array(
                "stock" => array(
                    "helperName" => "he_cek_saldo_kas",
                    "functionName" => "cekStockSaldoKas",
                    "params" => array(
                        "cabang_id" => "placeID",
                        //                        "gudang_id" => "gudangID",
                    ),
                ),
            ),
        ),
        "mainValueInjectors" => array(
            "amount" => "sisa",
            "creditAmount" => "creditAmount",
            "harus_bayar" => "harus_bayar",
        ),
        "shoppingCartRowValidators" => array(
            "pihakID" => "vendor ID",
            "pihakName" => "vendor name",
        ),
        "shoppingCartRowNumValidators" => array(
            "nilai_entry" => "amount of payment",
        ),

        "pairRegistries" => array(
            "main", "items", "items8_sum"
        ),
        "revertException" => true,
        "canceledLabel" => array(
            1 => "Transaksi Penyetoran Kas ke Pusat nomer {nomer} telah dibatalkan oleh {cancel_name} pada {cancel_dtime}. Silahkan melakukan penyetoran ulang di cabang {cabang2_nama}",
        ),
        "shortItemsFields" => array(
            "extern2_nama" => array(
                "label" => "penyetor",
                "addKey" => "keterangan",
            ),
            "nama" => "nomor transaksi",
            "cash_account_nama" => "akun kas/bank",
            "nilai_bayar" => "nilai setor",
        ),
        "shortItemsFields_sub" => array(
//            "extern2_nama" => array(
//                "label" => "akun kas/bank",
//                "addKey" => "keterangan",
//            ),
//            "nama" => "nomor transaksi",
            "cash_account_nama" => "akun kas/bank",
            "nilai_setor" => "nilai setor",
        ),

    ),
);




<?php
/**
 * Created by PhpStorm.
 * User: aziz
 * Date: 11/22/2018
 * Time: 8:38 PM
 */
$config["coTransaksiValues"] = array(
    //  config penyetoran
    "759" => array(
        "counters" => array(
            "stepCode|placeID",
            "stepCode|olehID",
            "stepCode|placeID|olehID",
            //            "stepCode|customerID",
            //            "stepCode|placeID|customerID",
        ),
        "formatNota" => "stepCode|placeID|olehID",
        "valueGates" => array(//==sumber nilai yang dikirim kemana2
            "master" => array(//==sumber nilai utama
                //                "customerID" => "pihakID",
                //                "customerName" => "pihakName",
                //                "refs" => "refs",
                //                "refs_intext" => "refs_intext",
                "place2ID" => "centerDetails",
                "place2Name" => "centerDetails__label",
                "gudang2Name" => "gudang2ID__label",
            ),
            "detail" => array(//===sumber nilai berupa rincian

            ),
            "master_dependent" => array(
                "cashMethode" => array(
                    "rekening_koran" => array(
                        "rekening_koran_value" => "nilai_entry",
                        "kas_value" => "0",
                    ),
                    "reguler" => array(
                        "rekening_koran_value" => "0",
                        "kas_value" => "nilai_entry",
                    ),
                ),
            ),
        ),
        "valueBuilders" => array(
            "totalCredit" => "creditAmount+creditValue",
            //            "harus_bayar" => "sisa-totalCredit",
            "nilai_bayar" => "nilai_entry+totalCredit",
        ),
        "valuePopulator" => array(
            //            array(
            "valueSrc" => "nilai_bayar",
            "acuanSrc" => ".sisa",
            //            ),
        ),

        "additionalSource" => true,
        "additionalItemSourceKey" => array(
            "top" => "nilai_bayar",
            "bottom" => "tagihan",//harga_nett2
        ),
        "additionalItemSource" => array(
            "harga_nett2" => "tagihan",//harga_nett2
            "hpp" => "hpp",
            "ppn" => "ppn",
            "laba_kotor" => "tagihan-hpp",//harga_nett2
        ),
        "additionalItemResult" => array(
            "harga_nett2" => "__harga_nett2",
            "hpp" => "__hpp",
            "ppn" => "__ppn",
            "laba_kotor" => "__laba_kotor",
        ),

        "populators" => array(
            "nilai_bayar" => array(
                "mainSrc" => array(
                    "key" => "nilai_bayar",
                ),
                "itemTarget" => array(
                    "key" => "nilai_bayar",
                    "maxAmountSrc" => "sisa",
                ),
            ),
        ),
        "additionalBuilders" => array(//==per-item
            "new_sisa" => "sisa-nilai_bayar",
        ),
        "additionalMainBuilders" => array(//==per-item
            "harus_bayar" => "sisa-totalCredit",
            //            "nilai_bayar" => "nilai_entry+totalCredit",
        ),

        "preProcessor" => array(),
        "tableIn" => array(
            "master" => array(
                "jenis_master" => "jenisTrMaster",
                "jenis_top" => "jenisTrTop",
                "jenis" => "jenisTr",
                "jenis_label" => "jenisTrName",
                "div_id" => "divID",
                "div_nama" => "divName",
                "dtime" => "dtime",
                "fulldate" => "fulldate",
                "oleh_id" => "olehID",
                "oleh_nama" => "olehName",

                "customers_id" => "pihakID",
                "customers_nama" => "pihakName",

                "cabang_id" => "placeID",
                "cabang_nama" => "placeName",
                "cabang2_id" => "place2ID",
                "cabang2_nama" => "place2Name",
                "gudang_id" => "gudangID",
                "gudang_nama" => "gudangName",
                "gudang2_id" => "gudang2ID",
                "gudang2_nama" => "gudang2Name",

                "transaksi_nilai" => "nilai_bayar",
                "transaksi_jenis" => "jenisTr",
                "keterangan" => "description",

                "bank_rekening_id" => "cash_id",
                "bank_rekening_nama" => "bank_rekening_nama",

                "ids_ref" => "refs",
                "ids_ref_intext" => "refs_intext",
            ),
            "mainValues" => array(
                "tagihan" => "tagihan",
                "terbayar" => "terbayar",
                "sisa" => "sisa",
                "nilai_bayar" => "nilai_bayar",

                "harus_bayar" => "harus_bayar",
                "creditAmount" => "creditAmount",
                "nilai_entry" => "nilai_entry",
            ),
            "detail" => array(
                "dtime" => "dtime",
                "produk_id" => "id",
                "produk_kode" => "code",
                "produk_label" => "label",
                "produk_nama" => "name",
                "produk_ord_jml" => ".1",
                "produk_ord_hrg" => "nilai_bayar",
                //                "hpp" => "hpp",
                //                "ppn" => "ppn",
                // "produk_ord_diskon",
                // "produk_hrg_ori",
                // "produk_hrg_gap",
                //                "satuan" => "satuan","note" => "note",
            ),
            "detailValues" => array(
                "tagihan" => "tagihan",
                "terbayar" => "terbayar",
                "sisa" => "sisa",
                "nilai_bayar" => "nilai_bayar",
                "new_sisa" => "new_sisa",
            ),
        ),
        "tableIn_static" => array(
            "master" => array(
                "trash" => 0,
            ),
            "detail" => array(
                "trash" => 0,
                "produk_jenis" => "invoice",
            ),
        ),
        "components" => array(),
        "postProcessor" => array(
            "759r" => array(
                "master" => array(
                    array(
                        "comName" => "LockerValue",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "gudang_id" => ".0",
                            "state" => ".active",
                            "jenis" => ".kas",
                            "produk_id" => "cash_account_source",
                            "nama" => "cash_account_source__label",
                            "nilai" => "-nilai_entry",
                            "transaksi_id" => ".0",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "LockerValue",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "gudang_id" => ".0",
                            //                            "state" => ".payment",
                            "state" => ".hold",
                            "jenis" => ".kas",
                            "produk_id" => "cash_account_source",
                            "nama" => "cash_account_source__label",
                            "nilai" => "nilai_entry",
                            //                            "transaksi_id" => ".0",
                            "transaksi_id" => "transaksi_id",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),

                    array(
                        "comName" => "Jurnal_activity",
                        "loop" => array(
                            "activity" => ".1",
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "cabang_nama" => "placeName",
                            "cabang2_id" => "place2ID",
                            "cabang2_nama" => "place2Name",
                            "oleh_id" => "olehID",
                            "oleh_nama" => "olehName",
                            "jenis" => "jenisTr",
                            "jenis_master" => "jenisTrMaster",
                            "jenis_top" => "jenisTrTop",
                            "master_id" => "transaksi_id",
                            "step_number" => ".1",
                            "nilai" => ".1",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "Jurnal_activityMain",
                        "loop" => array(
                            "activity" => ".1",
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "cabang_nama" => "placeName",
                            "cabang2_id" => "place2ID",
                            "cabang2_nama" => "place2Name",
                            "oleh_id" => "olehID",
                            "oleh_nama" => "olehName",
                            "jenis" => "jenisTr",
                            "jenis_master" => "jenisTrMaster",
                            "jenis_top" => "jenisTrTop",
                            "master_id" => "transaksi_id",
                            "step_number" => ".1",
                            "nilai" => ".1",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                ),
                "detail" => array(
                    array(
                        "comName" => "PaymentSrcItem",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => "id",
                            "extern_nama" => "name",
                            "label" => ".hutang setoran",
                            "target_jenis" => "jenisTr",
                            "transaksi_id" => "refID",
                            "terbayar" => "nilai_bayar",
                            "sisa" => "new_sisa",
                        ),
                        "reversable" => true,
                        "srcGateName" => "items",
                        "srcRawGateName" => "items",
                    ),
                ),
            ),

        ),
    ),
    "758" => array(
        "counters" => array(
            "stepCode|placeID",
            "stepCode|olehID",
            "stepCode|placeID|olehID",
            //            "stepCode|customerID",
            //            "stepCode|placeID|customerID",
        ),
        "formatNota" => "stepCode|placeID|olehID",
        "valueGates" => array(//==sumber nilai yang dikirim kemana2
            "master" => array(//==sumber nilai utama
                //                "customerID" => "pihakID",
                //                "customerName" => "pihakName",
                //                "refs" => "refs",
                //                "refs_intext" => "refs_intext",

            ),
            "detail" => array(//===sumber nilai berupa rincian

            ),
        ),
        "valueBuilders" => array(
            //            "totalCredit"=>"creditAmount+creditValue",
            //            "harus_bayar" => "sisa-totalCredit",
            //            "nilai_bayar" => "nilai_entry+totalCredit",
            "nilai_bayar" => "nilai_entry",
        ),
        "valuePopulator" => array(
            //            array(
            "valueSrc" => "nilai_bayar",
            "acuanSrc" => ".sisa",
            //            ),
        ),
        "populators" => array(
            "nilai_bayar" => array(
                "mainSrc" => array(
                    "key" => "nilai_bayar",
                ),
                "itemTarget" => array(
                    "key" => "nilai_bayar",
                    "maxAmountSrc" => "sisa",
                ),
            ),
        ),
        "additionalBuilders" => array(//==per-item
            "new_sisa" => "sisa-nilai_bayar",
        ),
        "additionalMainBuilders" => array(//==per-item
            "harus_bayar" => "sisa-totalCredit",
            //            "nilai_bayar" => "nilai_entry+totalCredit",
        ),

        "preProcessor" => array(
            "758" => array(
                "master" => array(
                    array(
                        "comName" => "LockerDebtValue",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "place2ID",
                            "jenis" => ".hutang biaya ke pusat",
                            "nilai" => "nilai_entry",
                        ),
                        "resultParams" => array(
                            "main" => array(
                                "nilai_dipakai" => "nilai_dipakai",
                                "nilai_sisa" => "nilai_sisa",
                            ),
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "LockerDebtValue",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "place2ID",
                            //                            "gudang_id" => "gudangID",
                            //                            "state" => ".active",
                            "jenis" => ".hutang ke pusat",
                            //                            "produk_id" => "pihakID",
                            //                            "nama" => "pihakName",
                            //                            "nilai" => "ppn",
                            "nilai" => "nilai_sisa_hutang_biaya_ke_pusat",
                            //                            "transaksi_id" => "masterID",
                            //                            "oleh_id" => ".0",
                            //                            "paymentMethod" => "paymentMethod",
                        ),
                        "resultParams" => array(
                            "main" => array(
                                "nilai_dipakai" => "nilai_dipakai",
                                "nilai_sisa" => "nilai_sisa",
                                //                                "nilai_tambah" => "nilai_tambah",
                            ),
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),

                    // rekening koran
                    array(
                        "comName" => "RekeningKoran",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "state" => ".active",
                            "extern_id" => "cash_account_target",
                            "extern_nama" => "cash_account_target__label",
                            "nilai" => "nilai_entry",
                            "method" => "cashMethode", // cash method yang dipilih saat setor
                            "jenis" => ".hutang bank",
                        ),
                        "resultParams" => array(
                            "main" => array(
                                "nilai_cash" => "nilai_cash",
                                "nilai_koran" => "nilai_koran",
                                "nilai_cash_full" => "nilai_cash_full",
                                "nilai_koran_full" => "nilai_koran_full",
                            ),
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                ),
            ),

        ),
        "tableIn" => array(
            "master" => array(
                "jenis_master" => "jenisTrMaster",
                "jenis_top" => "jenisTrTop",
                "jenis" => "jenisTr",
                "jenis_label" => "jenisTrName",
                "div_id" => "divID",
                "div_nama" => "divName",
                "dtime" => "dtime",
                "fulldate" => "fulldate",
                "oleh_id" => "olehID",
                "oleh_nama" => "olehName",

                "customers_id" => "pihakID",
                "customers_nama" => "pihakName",

                "cabang_id" => "placeID",
                "cabang_nama" => "placeName",
                "cabang2_id" => "place2ID",
                "cabang2_nama" => "place2Name",
                "gudang_id" => "gudangID",
                "gudang_nama" => "gudangName",
                "gudang2_id" => "gudang2ID",
                "gudang2_nama" => "gudang2Name",

                "transaksi_nilai" => "nilai_bayar",
                "transaksi_jenis" => "jenisTr",
                "keterangan" => "description",

                "bank_rekening_id" => "cash_id",
                "bank_rekening_nama" => "bank_rekening_nama",

                "ids_ref" => "refs",
                "ids_ref_intext" => "refs_intext",
            ),
            "mainValues" => array(
                "tagihan" => "tagihan",
                "terbayar" => "terbayar",
                "sisa" => "sisa",
                "nilai_bayar" => "nilai_bayar",

                "harus_bayar" => "harus_bayar",
                "creditAmount" => "creditAmount",
                "nilai_entry" => "nilai_entry",
            ),
            "detail" => array(
                "dtime" => "dtime",
                "produk_id" => "id",
                "produk_kode" => "code",
                "produk_label" => "label",
                "produk_nama" => "name",
                "produk_ord_jml" => ".1",
                "produk_ord_hrg" => "nilai_bayar",
                //                "hpp" => "hpp",
                //                "ppn" => "ppn",
                // "produk_ord_diskon",
                // "produk_hrg_ori",
                // "produk_hrg_gap",
                //                "satuan" => "satuan","note" => "note",
            ),
            "detailValues" => array(
                "tagihan" => "tagihan",
                "terbayar" => "terbayar",
                "sisa" => "sisa",
                "nilai_bayar" => "nilai_bayar",
                "new_sisa" => "new_sisa",
            ),
        ),
        "tableIn_static" => array(
            "master" => array(
                "trash" => 0,
            ),
            "detail" => array(
                "trash" => 0,
                "produk_jenis" => "invoice",
            ),
        ),
        "components" => array(
            "758" => array(
                "master" => array(
                    //<editor-fold desc="bagian cabang">
                    array(
                        "comName" => "Jurnal",
                        "loop" => array(
                            "030202" => "-nilai_sisa_hutang_ke_pusat",// laba ditempatkan pusat
                            "020402" => "-nilai_dipakai_hutang_biaya_ke_pusat",// hutang biaya ke pusat
                            "020401" => "-nilai_dipakai_hutang_ke_pusat",// hutang ke pusat
                            "010101" => "-nilai_entry",// kas
                        ),
                        "static" => array(
                            "cabang_id" => "place2ID",
                            "jenis" => "jenisTr",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "Rekening",
                        "loop" => array(
                            "030202" => "-nilai_sisa_hutang_ke_pusat",// laba ditempatkan pusat
                            "020402" => "-nilai_dipakai_hutang_biaya_ke_pusat",// hutang biaya ke pusat
                            "020401" => "-nilai_dipakai_hutang_ke_pusat",// hutang ke pusat
                            "010101" => "-nilai_entry",// kas
                        ),
                        "static" => array(
                            "cabang_id" => "place2ID",
                            "jenis" => "jenisTr",
                            // "transaksi_no" => "nomer",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "RekeningPembantuKas",
                        "loop" => array(
                            "010101" => "-nilai_entry",// kas
                        ),
                        "static" => array(
                            "cabang_id" => "place2ID",
                            "extern_id" => "cash_account_source",// diisi id bank
                            "extern_nama" => "cash_account_source__label",// diisi nama bank
                            "jenis" => "jenisTr",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "RekeningPembantuAntarcabang",
                        "loop" => array(
                            "020401" => "-nilai_dipakai_hutang_ke_pusat",// hutang ke pusat
                        ),
                        "static" => array(
                            "cabang_id" => "place2ID",
                            "cabang2_id" => "placeID",
                            "cabang2_nama" => "placeName",
                            "extern_id" => "placeID",
                            "extern_nama" => "placeName",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "RekeningPembantuAntarcabang",
                        "loop" => array(
                            "020402" => "-nilai_dipakai_hutang_biaya_ke_pusat",// hutang biaya ke pusat
                        ),
                        "static" => array(
                            "cabang_id" => "place2ID",
                            "cabang2_id" => "placeID",
                            "cabang2_nama" => "placeName",
                            "extern_id" => "placeID",
                            "extern_nama" => "placeName",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),

                    //</editor-fold>

                    //<editor-fold desc="bagian pusat">
                    array(
                        "comName" => "Jurnal",
                        "loop" => array(
                            "030202" => "nilai_sisa_hutang_ke_pusat",// laba ditempatkan pusat
                            "010804" => "-nilai_dipakai_hutang_biaya_ke_pusat",// piutang biaya cabang
                            "010801" => "-nilai_dipakai_hutang_ke_pusat",// piutang cabang
                            "010101" => "nilai_cash_full",// kas
                            "020202" => "-nilai_koran_full",// hutang bank
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "jenis" => "jenisTr",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "Rekening",
                        "loop" => array(
                            "030202" => "nilai_sisa_hutang_ke_pusat",// laba ditempatkan pusat
                            "010804" => "-nilai_dipakai_hutang_biaya_ke_pusat",// piutang biaya cabang
                            "010801" => "-nilai_dipakai_hutang_ke_pusat",// piutang cabang
                            "010101" => "nilai_cash_full",// kas
                            "020202" => "-nilai_koran_full",// hutang bank
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "jenis" => "jenisTr",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "RekeningPembantuKas",
                        "loop" => array(
                            "010101" => "nilai_cash_full",// kas
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => "cash_account_target",// diisi id bank
                            "extern_nama" => "cash_account_target__label",// diisi nama bank
                            "jenis" => "jenisTr",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "RekeningPembantuAntarcabang",
                        "loop" => array(
                            "010801" => "-nilai_dipakai_hutang_ke_pusat",// piutang cabang
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "cabang2_id" => "place2ID",
                            "cabang2_nama" => "place2Name",
                            "extern_id" => "place2ID",
                            "extern_nama" => "place2Name",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "RekeningPembantuAntarcabang",
                        "loop" => array(
                            "010804" => "-nilai_dipakai_hutang_biaya_ke_pusat",// piutang biaya cabang
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "cabang2_id" => "place2ID",
                            "cabang2_nama" => "place2Name",
                            "extern_id" => "place2ID",
                            "extern_nama" => "place2Name",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),

                    //rekening koran
                    array(
                        "comName" => "RekeningPembantuBank",
                        "loop" => array(
                            "020202" => "-nilai_koran_full",// hutang bank
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => "cash_account_target__folders",//id bank
                            "extern_nama" => "cash_account_target__folders_nama",//lbel bank
                            "jenis" => "jenisTr",
                            "transaksi_no" => "nomer",
                            "extern2_id" => "cash_account_target__folders",
                            "extern2_nama" => "cash_account_target__folders_nama",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "RekeningPembantuRelasiRekeningKoran",//rekening pembantu level 2
                        "loop" => array(
                            "020202" => "-nilai_koran_full",// hutang bank
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => ".1",//id relasi rekening koran
                            "extern_nama" => ".02020200001",//lbel relasi rekening koran // rekening koran
                            "extern2_id" => "cash_account_target__folders",//id folder rekening koran
                            "extern2_nama" => "cash_account_target__folders_nama",//label folder rekening koran
                            "jenis" => "jenisTr",
                            "transaksi_no" => "nomer",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "RekeningPembantuRekeningKoranMain",//rekening pembantu level 3
                        "loop" => array(
                            "02020200001" => "-nilai_koran_full",// rekening koran
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => "cash_account_target",//id rekening koran
                            "extern_nama" => "cash_account_target__label",//label rekening koran
                            "extern2_id" => "cash_account_target__folders",//folder rekening koran
                            "extern2_nama" => "cash_account_target__folders_nama",//folder rekening koran

                            "jenis" => "jenisTr",
                            "transaksi_no" => "nomer",
                            "produk_nilai" => "nilai_koran_full",
                            "produk_qty" => ".-1",

                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    //endregkening koran


                    array(
                        "comName" => "Jurnal",
                        "loop" => array(
                            "010101" => "nilai_cash",// kas
                            "020202" => "nilai_koran",// hutang bank
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "jenis" => "jenisTr",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "Rekening",
                        "loop" => array(
                            "010101" => "nilai_cash",// kas
                            "020202" => "nilai_koran",// hutang bank
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "jenis" => "jenisTr",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "RekeningPembantuKas",
                        "loop" => array(
                            "010101" => "nilai_cash",// kas
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => "cash_account_target",// diisi id bank
                            "extern_nama" => "cash_account_target__label",// diisi nama bank
                            "jenis" => "jenisTr",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),

                    //rekening koran
                    array(
                        "comName" => "RekeningPembantuBank",
                        "loop" => array(
                            "020202" => "nilai_koran",// hutang bank
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => "cash_account_target__folders",//id bank
                            "extern_nama" => "cash_account_target__folders_nama",//lbel bank
                            "jenis" => "jenisTr",
                            "transaksi_no" => "nomer",
                            "extern2_id" => "cash_account_target__folders",
                            "extern2_nama" => "cash_account_target__folders_nama",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "RekeningPembantuRelasiRekeningKoran",//rekening pembantu level 2
                        "loop" => array(
                            "020202" => "nilai_koran",// hutang bank
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => ".1",//id relasi rekening koran
                            "extern_nama" => ".02020200001",//lbel relasi rekening koran // rekening koran
                            "extern2_id" => "cash_account_target__folders",//id folder rekening koran
                            "extern2_nama" => "cash_account_target__folders_nama",//label folder rekening koran
                            "jenis" => "jenisTr",
                            "transaksi_no" => "nomer",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "RekeningPembantuRekeningKoranMain",//rekening pembantu level 3
                        "loop" => array(
                            "02020200001" => "nilai_koran",//
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => "cash_account_target",//id rekening koran
                            "extern_nama" => "cash_account_target__label",//label rekening koran
                            "extern2_id" => "cash_account_target__folders",//folder rekening koran
                            "extern2_nama" => "cash_account_target__folders_nama",//folder rekening koran

                            "jenis" => "jenisTr",
                            "transaksi_no" => "nomer",
                            "produk_nilai" => "nilai_koran_full",
                            "produk_qty" => ".1",

                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    //endregkening koran

                    //</editor-fold>
                ),
                "detail" => array(),
            ),
        ),
        "postProcessor" => array(
            "758" => array(
                "master" => array(
                    array(
                        "comName" => "LockerValue",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "cabang2ID",
                            "gudang_id" => ".0",
                            //                            "state" => ".payment",
                            "state" => ".hold",
                            "jenis" => ".kas",
                            "produk_id" => "cash_account_source",
                            "nama" => "cash_account_source__label",
                            "nilai" => "-nilai_entry",
                            //                            "transaksi_id" => ".0",
                            "transaksi_id" => "masterID",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "LockerValue",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "cabang2ID",
                            "gudang_id" => ".0",
                            "state" => ".sold",
                            "jenis" => ".kas",
                            "produk_id" => "cash_account_source",
                            "nama" => "cash_account_source__label",
                            "nilai" => "nilai_entry",
                            "transaksi_id" => ".0",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    // locker kas reguler pusat
                    array(
                        "comName" => "LockerValue",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "gudang_id" => ".0",
                            "state" => ".active",
                            "jenis" => ".kas",
                            "produk_id" => "cash_account_target",
                            "nama" => "cash_account_target__label",
                            "nilai" => "nilai_cash_full",
                            "transaksi_id" => ".0",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    // locker kas rekening koran pusat
                    array(
                        "comName" => "LockerValue",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "gudang_id" => ".0",
                            "state" => ".active",
                            "jenis" => ".kas",
                            "produk_id" => "cash_account_target",
                            "nama" => "cash_account_target__label",
                            "nilai" => "nilai_cash",
                            "transaksi_id" => ".0",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),

                    // menambah available rekening koran
                    array(
                        "comName" => "LockerValue",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "cabangID",
                            "gudang_id" => ".0",
                            "state" => ".active",
                            "jenis" => ".plafon hutang bank",
                            "produk_id" => "cash_account_target",
                            "nama" => "cash_account_target__label",
                            "nilai" => "nilai_koran_full",
                            "transaksi_id" => ".0",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "LockerStockPlafonBankMutasiMain",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => "cash_account_target",
                            "extern_nama" => "cash_account_target__label",
                            "debet" => "nilai_koran_full",
                            "produk_nilai" => "nilai_koran_full",
                            "gudang_id" => ".0",
                            "jenis" => "jenisTr",
                            "transaksi_jenis" => "jenisTr",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "LockerValue",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "cabangID",
                            "gudang_id" => ".0",
                            "state" => ".active",
                            "jenis" => ".plafon hutang bank",
                            "produk_id" => "cash_account_target",
                            "nama" => "cash_account_target__label",
                            "nilai" => "-nilai_koran",
                            "transaksi_id" => ".0",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "LockerStockPlafonBankMutasiMain",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => "cash_account_target",
                            "extern_nama" => "cash_account_target__label",
                            "debet" => "-nilai_koran",
                            "produk_nilai" => "-nilai_koran",
                            "gudang_id" => ".0",
                            "jenis" => "jenisTr",
                            "transaksi_jenis" => "jenisTr",
                            //                            "transaksi_id"        => "jenisTr",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),


                    array(
                        "comName" => "Jurnal_activity",
                        "loop" => array(
                            "activity" => ".1",
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "cabang_nama" => "placeName",
                            "cabang2_id" => "place2ID",
                            "cabang2_nama" => "place2Name",
                            "oleh_id" => "olehID",
                            "oleh_nama" => "olehName",
                            "jenis" => "jenisTr",
                            "jenis_master" => "jenisTrMaster",
                            "jenis_top" => "jenisTrTop",
                            "master_id" => "transaksi_id",
                            "step_number" => ".2",
                            "nilai" => ".1",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "Jurnal_activityMain",
                        "loop" => array(
                            "activity" => ".1",
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "cabang_nama" => "placeName",
                            "cabang2_id" => "place2ID",
                            "cabang2_nama" => "place2Name",
                            "oleh_id" => "olehID",
                            "oleh_nama" => "olehName",
                            "jenis" => "jenisTr",
                            "jenis_master" => "jenisTrMaster",
                            "jenis_top" => "jenisTrTop",
                            "master_id" => "transaksi_id",
                            "step_number" => ".2",
                            "nilai" => ".1",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                ),
                "detail" => array(),
            ),
        ),
    ),

);
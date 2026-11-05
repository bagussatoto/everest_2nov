<?php
/**
 * Created by PhpStorm.
 * User: aziz
 * Date: 11/22/2018
 * Time: 8:38 PM
 */


$config["coTransaksiCore"] = array(
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
                    array(
                        "comName" => "LockerValueItem",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "gudang_id" => ".0",
                            "state" => ".active",
                            "jenis" => ".kas",
                            "produk_id" => "cash_account",
                            "nama" => "cash_account_nama",
                            "nilai" => "-nilai_bayar",
                            "transaksi_id" => ".0",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "items",
                        "srcRawGateName" => "items",
                    ),
                    array(
                        "comName" => "LockerValueItem",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "gudang_id" => ".0",
                            "state" => ".hold",
                            "jenis" => ".kas",
                            "produk_id" => "cash_account",
                            "nama" => "cash_account_nama",
                            "nilai" => "nilai_bayar",
                            "transaksi_id" => "transaksi_id",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "items",
                        "srcRawGateName" => "items",
                    ),
                ),
            ),

        ),
        //-----
        "countersEdit" => array(
            "stepCode|placeID",
            "stepCode|olehID",
            "stepCode|placeID|olehID",

            "stepCode|masterID",
            "stepCode|masterID|placeID",
            "stepCode|masterID|olehID",
            "stepCode|masterID|placeID|olehID",
        ),
        "formatNotaEdit" => "stepCode|placeID|olehID",
        "countersReject" => array(
            "stepCode|placeID",
            "stepCode|olehID",
            "stepCode|placeID|olehID",

            "stepCode|masterID",
            "stepCode|masterID|placeID",
            "stepCode|masterID|olehID",
            "stepCode|masterID|placeID|olehID",
        ),
        "formatNotaReject" => "stepCode|placeID|olehID",
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
            "master" => array(
                "place2ID" => "cabang2ID",
                "place2Name" => "cabang2Name",
            ),
            "detail" => array(

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

        "preProcessor" => array(
            "758" => array(
                "master" => array(
                    array(
                        "comName" => "LockerDebtValue",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "place2ID",
                            "jenis" => ".2040020",//hutang biaya ke pusat
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
                            "jenis" => ".2040010",//hutang ke pusat
                            "nilai" => "nilai_sisa_2040020",
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
//                    array(
//                        "comName" => "RekeningKoran",
//                        "loop" => array(),
//                        "static" => array(
//                            "cabang_id" => "placeID",
//                            "state" => ".active",
//                            "extern_id" => "cash_account_target",
//                            "extern_nama" => "cash_account_target__label",
//                            "nilai" => "nilai_entry",
//                            "method" => "cashMethode", // cash method yang dipilih saat setor
//                            "jenis" => ".hutang bank",
//                        ),
//                        "resultParams" => array(
//                            "main" => array(
//                                "nilai_cash" => "nilai_cash",
//                                "nilai_koran" => "nilai_koran",
//                                "nilai_cash_full" => "nilai_cash_full",
//                                "nilai_koran_full" => "nilai_koran_full",
//                            ),
//                        ),
//                        "srcGateName" => "main",
//                        "srcRawGateName" => "main",
//                    ),

                ),
            ),

        ),
        "components" => array(
            "758" => array(
                "master" => array(
                    //<editor-fold desc="bagian cabang">
                    array(
                        "comName" => "Jurnal",
                        "loop" => array(
                            "3020050" => "-nilai_sisa_2040010",// laba ditempatkan pusat
                            "2040020" => "-nilai_dipakai_2040020",// hutang biaya ke pusat
                            "2040010" => "-nilai_dipakai_2040010",// hutang ke pusat
                            "1010010010" => "-nilai_entry",// kas
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
                            "3020050" => "-nilai_sisa_2040010",// laba ditempatkan pusat
                            "2040020" => "-nilai_dipakai_2040020",// hutang biaya ke pusat
                            "2040010" => "-nilai_dipakai_2040010",// hutang ke pusat
                            "1010010010" => "-nilai_entry",// kas
                        ),
                        "static" => array(
                            "cabang_id" => "place2ID",
                            "jenis" => "jenisTr",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "RekeningPembantuAntarcabang",
                        "loop" => array(
                            "2040010" => "-nilai_dipakai_2040010",// hutang ke pusat
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
                            "2040020" => "-nilai_dipakai_2040020",// hutang biaya ke pusat
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
                            "3020050" => "nilai_sisa_2040010",// laba ditempatkan pusat
                            "1010060040" => "-nilai_dipakai_2040020",// piutang biaya cabang
                            "1010060010" => "-nilai_dipakai_2040010",// piutang cabang
                            "1010010010" => "nilai_entry",// kas
//                            "2020020" => "-nilai_koran_full",// hutang bank
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
                            "3020050" => "nilai_sisa_2040010",// laba ditempatkan pusat
                            "1010060040" => "-nilai_dipakai_2040020",// piutang biaya cabang
                            "1010060010" => "-nilai_dipakai_2040010",// piutang cabang
                            "1010010010" => "nilai_entry",// kas
//                            "2020020" => "-nilai_koran_full",// hutang bank
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "jenis" => "jenisTr",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "RekeningPembantuAntarcabang",
                        "loop" => array(
                            "1010060010" => "-nilai_dipakai_2040010",// piutang cabang
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
                            "1010060040" => "-nilai_dipakai_2040020",// piutang biaya cabang
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

                    //</editor-fold>
                ),
                "detail" => array(
                    // region CABANG KAS KELUAR
                    array(
                        "comName" => "RekeningPembantuKasItem",
                        "loop" => array(
//                            "1010010010" => "-nilai_setor",// kas
                            "1010010010" => "-sisa",// kas
                        ),
                        "static" => array(
                            "cabang_id" => "place2ID",
                            "extern_id" => "cash_account",// diisi id bank
                            "extern_nama" => "cash_account_nama",// diisi nama bank
                            "jenis" => "jenisTr",
                        ),
                        "srcGateName" => "items5_sum",
                        "srcRawGateName" => "items5_sum",
                    ),
                    // endregion CABANG

                    // region DC/PUSAT NON TUNAI
                    array(
                        "comName" => "RekeningPembantuKasItem",
                        "loop" => array(
//                            "1010010010" => "nilai_setor",// kas
                            "1010010010" => "sisa",// kas
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => "cash_account",// diisi id bank
                            "extern_nama" => "cash_account_nama",// diisi nama bank
                            "jenis" => "jenisTr",
                        ),
                        "srcGateName" => "items6_sum",
                        "srcRawGateName" => "items6_sum",
                    ),
                    // endregion DC/PUSAT

                    // region DC/PUSAT TUNAI
                    array(
                        "comName" => "RekeningPembantuKasItem",
                        "loop" => array(
                            "1010010010" => "nilai_setor",// kas
//                            "1010010010" => "sisa",// kas
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => "cash_account",// diisi id bank
                            "extern_nama" => "cash_account_nama",// diisi nama bank
                            "jenis" => "jenisTr",
                        ),
                        "srcGateName" => "items7_sum",
                        "srcRawGateName" => "items7_sum",
                    ),
                    // endregion DC/PUSAT

                ),
            ),
        ),
        "postProcessor" => array(
            "758" => array(
                "master" => array(),
                "detail" => array(
                    // region CABANG
                    array(
                        "comName" => "LockerValueItem",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "cabang2ID",
                            "gudang_id" => ".0",
                            "state" => ".hold",
                            "jenis" => ".kas",
                            "produk_id" => "cash_account",
                            "nama" => "cash_account_nama",
                            "nilai" => "-sisa",
                            "transaksi_id" => "masterID",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "items5_sum",
                        "srcRawGateName" => "items5_sum",
                    ),
                    array(
                        "comName" => "LockerValueItem",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "cabang2ID",
                            "gudang_id" => ".0",
                            "state" => ".sold",
                            "jenis" => ".kas",
                            "produk_id" => "cash_account",
                            "nama" => "cash_account_nama",
                            "nilai" => "sisa",
                            "transaksi_id" => ".0",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "items5_sum",
                        "srcRawGateName" => "items5_sum",
                    ),
                    // endregion CABANG

                    // locker kas reguler pusat
                    array(
                        "comName" => "LockerValueItem",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "gudang_id" => ".0",
                            "state" => ".active",
                            "jenis" => ".kas",
                            "produk_id" => "cash_account",
                            "nama" => "cash_account_nama",
                            "nilai" => "sisa",
                            "transaksi_id" => ".0",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "items6_sum",
                        "srcRawGateName" => "items6_sum",
                    ),
                    // locker kas rekening koran pusat
                    array(
                        "comName" => "LockerValueItem",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "gudang_id" => ".0",
                            "state" => ".active",
                            "jenis" => ".kas",
                            "produk_id" => "cash_account",
                            "nama" => "cash_account_nama",
                            "nilai" => "nilai_setor",
                            "transaksi_id" => ".0",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "items7_sum",
                        "srcRawGateName" => "items7_sum",
                    ),


                ),
            ),
        ),


    ),

);


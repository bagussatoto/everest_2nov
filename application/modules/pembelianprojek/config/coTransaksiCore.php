<?php
/**
 * Created by PhpStorm.
 * User: aziz
 * Date: 11/22/2018
 * Time: 8:38 PM
 */
$config["coTransaksiCore"] = array(


    /*
     * ganti metode karena kadang project belum start
     * akan diterbitkan conecting distribusi jasa, tinggal approval
     */
    // tidak termasuk PPN MASUKAN
    "3463" => array(
        "counters" => array(
            "stepCode|placeID",
            "stepCode|olehID",
            "stepCode|placeID|olehID",
            "stepCode|supplierID",
        ),
        "formatNota" => "stepCode|placeID",
        "valueGates" => array(//==sumber nilai yang dikirim kemana2
            "master" => array(//==sumber nilai utama
                "supplierID" => "pihakID",
                "supplierName" => "pihakName",
                "place2ID" => "branch",
                "place2Name" => "branch__label",
                "customerID" => "customerProjek",
                "customerName" => "customerProjek__label",
//                "transaksi_id_target" => "transaksiData",
//                "transaksi_nomer_target" => "transaksiData__label",
                "gudang2ID" => "pihakProjekGudangID",
                "gudang2Name" => "pihakProjekGudangNama",
                "projectID" => "produkProjek",
                "projectName" => "produkProjek__label",
            ),
            "detail" => array(
                //===sumber nilai berupa rincian
//                "disc" => "(discPersen*harga)/100",
//                "harga_disc" => "harga-disc",
//                "dppPPn" => "harga_disc*(dpp_persen/100)",
//                "dppPPh" => "harga_disc*pph",
                "ppn_persen" => "ppnFactor",
//                "ppn" => "(ppn_persen/100)*dppPPn",
//                "hpp_nppn" => "harga_disc+ppn",
                "nett" => "hpp_nppn",
                "subtotal" => "hpp_nppn",
                "max_dpp_persen" => ".100",
            ),
            "detail2" => array(//===sumber nilai berupa rincian
                "disc" => "(discPersen*harga)/100",
                "harga_disc" => "harga-disc",
                //--------
                "ppn_persen" => "ppnFactor",
                "dppPPn" => "harga_disc*(dpp_ppn_persen/100)",
                "ppn" => "(ppn_persen/100)*dppPPn",
                //--------
                "dppPPh" => "harga_disc*(dpp_pph_persen/100)",
                "pph_nilai" => "(pph_persen/100)*dppPPh",
                //--------
                "hpp_nppn" => "harga_disc+ppn",
                "nett" => "hpp_nppn",
                "max_dpp_persen" => ".100",
                "subtotal" => "jml*hpp_nppn",
            ),
            "master_dependent" => array(
                "paymentMethod" => array(
                    "credit" => array(
                        "nilai_credit" => "tagihan",
                        "nilai_cash" => "0",
                    ),
                    "cbd" => array(
                        "nilai_credit" => "tagihan",
                        "nilai_cash" => "0",
                    ),
                    "cia" => array(
                        "nilai_credit" => "tagihan",
                        "nilai_cash" => "0",
                    ),
                    "tt_adv" => array(
                        "nilai_credit" => "tagihan",
                        "nilai_cash" => "0",
                    ),
                ),
            ),
        ),
        "valueSubDetail" => true,// build value dari detail2 dan direcap ke items
        "valueSubDetailRecap" => array(
            "harga",
            "harga_disc",
            "dppPPn",
            "dppPPh",
            "hpp_nppn",
            "nett",
            "pph_nilai",
            "ppn",
//            "subtotal",
        ),
        "valueBuilders" => array(
            "grand_total" => "nett",
            "tagihan" => "grand_total-discount-dp",
            "ppn_value" => "nilai_dpp_ppn*ppnFactor/100",
            "payment_out" => "nett",
            "dppPph_dipakai" => "valid_pph_key*dppPPh",

            "dpp_pengganti" => "dppPPn*(ppnFactor/12)",
        ),
        "preProcessor" => array(
            "3463" => array(
                "master" => array(
                    array(
                        "comName" => "LockerValue",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "gudang_id" => "gudangID",
                            "state" => ".active",
                            "jenis" => ".ppn in",
                            "produk_id" => "pihakID",
                            "nama" => "pihakName",
                            "nilai" => "ppn",
//                            "transaksi_id" => "masterID",
                            "transaksi_id" => "currentID",
                            "oleh_id" => ".0",
                            "paymentMethod" => "paymentMethod",
                        ),
                        "resultParams" => array(
                            "main" => array(
                                "nilai_dipakai" => "nilai_dipakai",
                                "nilai_tambah" => "nilai_tambah",
                            ),
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "LockerValue",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "gudang_id" => "gudangID",
                            "state" => ".active",
                            "jenis" => ".piutang pembelian",
                            "produk_id" => "pihakID",
                            "nama" => "pihakName",
                            "nilai" => "tagihan-nilai_dipakai_ppn_in",
//                            "transaksi_id" => "masterID",
                            "transaksi_id" => "currentID",
                            "oleh_id" => ".0",
                            "paymentMethod" => "paymentMethod",
                        ),
                        "resultParams" => array(
                            "main" => array(
                                "nilai_dipakai" => "nilai_dipakai",
                                "nilai_tambah" => "nilai_tambah",
                            ),
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                ),
                "detail" => array(),
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

                "suppliers_id" => "supplierID",
                "suppliers_nama" => "supplierName",
                "customers_id" => "customerID",
                "customers_nama" => "customerName",

                "cabang_id" => "placeID",
                "cabang_nama" => "placeName",
                "cabang2_id" => "place2ID",
                "cabang2_nama" => "place2Name",
                "transaksi_nilai" => "nett",
                "transaksi_jenis" => "jenisTr",
                "keterangan" => "description",

                "gudang_id" => "gudangID",
                "gudang_nama" => "gudangName",
                "gudang2_id" => "gudang2ID",
                "gudang2_nama" => "gudang2Name",

                "project_id" => "pihakProjekID",
                "project_nama" => "pihakProjekName",
            ),
            "detail" => array(
                "dtime" => "dtime",
                "produk_id" => "id",
                "produk_kode" => "code",
                "produk_label" => "label",
                "produk_nama" => "name",
                "produk_ord_jml" => "qty",
                "produk_ord_hrg" => "harga",
                "satuan" => "satuan",
                "keterangan" => "note",
            ),

        ),
        "tableIn_static" => array(
            "master" => array(
                "trash" => 0,
            ),
            "detail" => array(
                "trash" => 0,
                "produk_jenis" => "service",
            ),
        ),
        "components" => array(
            "3463" => array(
                "master" => array(
                    //region PO PUSAT
                    array(
                        "comName" => "Jurnal",
                        "loop" => array(
                            "1010030060" => "harga_disc",// projek cost
                            "2010010" => "harga_disc",// hutang dagang
//                            "1010040070" => "nilai_tambah_ppn_in",// ppn in jasa belum ada faktur
//                            "1010020030" => "-nilai_dipakai_piutang_pembelian",// piutang pembelian
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "jenis" => "jenisTr",
                            "transaksi_no" => "nomer",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    array(
                        "comName" => "Rekening",
                        "loop" => array(
                            "1010030060" => "harga_disc",// projek cost
                            "2010010" => "harga_disc",// hutang dagang
//                            "1010040070" => "nilai_tambah_ppn_in",// ppn in jasa
//                            "1010020030" => "-nilai_dipakai_piutang_pembelian",// piutang pembelian
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "jenis" => "jenisTr",
                            "transaksi_no" => "nomer",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),

//                    array(
//                        "comName" => "RekeningPembantuSupplier",
//                        "loop" => array(
//                            "1010020030" => "-nilai_dipakai_piutang_pembelian",// piutang pembelian
//                        ),
//                        "static" => array(
//                            "cabang_id" => "placeID",
//                            "extern_id" => "pihakID",
//                            "extern_nama" => "pihakName",
//                            "jenis" => "jenisTr",
//                            "transaksi_no" => "nomer",
//                        ),
//                        "srcGateName" => "main",
//                        "srcRawGateName" => "main",
//                    ),
                    array(
                        "comName" => "RekeningPembantuSupplier",
                        "loop" => array(
                            "2010010" => "harga_disc",// hutang dagang
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => "pihakID",
                            "extern_nama" => "pihakName",
                            "jenis" => "jenisTr",
                            "transaksi_no" => "nomer",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
//                    array(
//                        "comName" => "RekeningPembantuSupplier",
//                        "loop" => array(
//                            "1010040070" => "nilai_tambah_ppn_in",// ppn in jasa
//                        ),
//                        "static" => array(
//                            "cabang_id" => "placeID",
//                            "extern_id" => "pihakID",
//                            "extern_nama" => "pihakName",
//                            "jenis" => "jenisTr",
//                            "transaksi_no" => "nomer",
//                        ),
//                        "srcGateName" => "main",
//                        "srcRawGateName" => "main",
//                    ),
                    array(
                        "comName" => "RekeningPembantuProjek",
                        "loop" => array(
                            "1010030060" => "harga_disc",// projek cost
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
//                            "extern_id" => "customerID",//
//                            "extern_nama" => "customerName",//
                            "extern_id" => "pihakProjekID",//
                            "extern_nama" => "pihakProjekName",//
//                            "extern2_id" => "transaksi_id_target",// so
//                            "extern2_nama" => "transaksi_nomer_target",// so
                            "jenis" => "jenisTr",
                            "transaksi_no" => "nomer",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    //endregion

                    // region pembantu hutang per-supplier, per-grn
                    array(
                        "comName" => "RekeningPembantuSubSupplier",
                        "loop" => array(
                            "2010010" => "harga_disc",// hutang dagang
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "extern_id" => "pihakID",
                            "extern_nama" => "pihakName",
                            "extern2_id" => "pihakID",
                            "extern2_nama" => "pihakName",
                            "jenis" => "jenisTr",
                            "transaksi_no" => "nomer",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),
                    // endregion pembantu hutang per-supplier, per-grn
                ),
                "detail" => array(),
            ),
//            "3113" => array(
//                "master" => array(
//                    //region seleish ppn 10 vs 11 %
//                    array(
//                        "comName" => "Jurnal",
//                        "loop" => array(
//                            "1010040070" => "selisih_ppn_realisasi*-1",//ppn in jasa
//                            "2010010" => "selisih_ppn_realisasi*-1",//hutang dagang
//
//                        ),
//                        "static" => array(
//                            "cabang_id" => "placeID",
//                            "jenis" => "jenisTr",
//                            "transaksi_no" => "nomer",
//                        ),
//                        "srcGateName" => "main",
//                        "srcRawGateName" => "main",
//                    ),
//                    array(
//                        "comName" => "Rekening",
//                        "loop" => array(
//                            "1010040070" => "selisih_ppn_realisasi*-1",//ppn in jasa
//                            "2010010" => "selisih_ppn_realisasi*-1",//hutang dagang
//
//                        ),
//                        "static" => array(
//                            "cabang_id" => "placeID",
//                            "jenis" => "jenisTr",
//                            "transaksi_no" => "nomer",
//                        ),
//                        "srcGateName" => "main",
//                        "srcRawGateName" => "main",
//                    ),
//                    array(
//                        "comName" => "RekeningPembantuSupplier",
//                        "loop" => array(
//                            "2010010" => "selisih_ppn_realisasi*-1",//hutang dagang
//                        ),
//                        "static" => array(
//                            "cabang_id" => "placeID",
//                            "extern_id" => "pihakID",
//                            "extern_nama" => "pihakName",
//                            "jenis" => "jenisTr",
//                            "transaksi_no" => "nomer",
//                        ),
//                        "srcGateName" => "main",
//                        "srcRawGateName" => "main",
//                    ),
//                    array(
//                        "comName" => "RekeningPembantuSupplier",
//                        "loop" => array(
//                            "1010040070" => "selisih_ppn_realisasi*-1",//ppn in jasa
//                        ),
//                        "static" => array(
//                            "cabang_id" => "placeID",
//                            "extern_id" => "pihakID",
//                            "extern_nama" => "pihakName",
//                            "jenis" => "jenisTr",
//                            "transaksi_no" => "nomer",
//                        ),
//                        "srcGateName" => "main",
//                        "srcRawGateName" => "main",
//                    ),
//                    //endregion
//
//                    array(
//                        "comName" => "Jurnal",
//                        "loop" => array(
//                            "1010040070" => "-ppn_realisasi",//ppn in jasa
//                            "1010040060" => "ppn_realisasi",//ppn in realisasi
//                        ),
//                        "static" => array(
//                            "cabang_id" => "placeID",
//                            "jenis" => "jenisTr",
//                            "transaksi_no" => "nomer",
//                        ),
//                        "srcGateName" => "main",
//                        "srcRawGateName" => "main",
//                    ),
//                    array(
//                        "comName" => "Rekening",
//                        "loop" => array(
//                            "1010040070" => "-ppn_realisasi",//ppn in jasa
//                            "1010040060" => "ppn_realisasi",//ppn in realisasi
//                        ),
//                        "static" => array(
//                            "cabang_id" => "placeID",
//                            "jenis" => "jenisTr",
//                            "transaksi_no" => "nomer",
//                        ),
//                        "srcGateName" => "main",
//                        "srcRawGateName" => "main",
//                    ),
//                    array(
//                        "comName" => "RekeningPembantuSupplier",
//                        "loop" => array(
//                            "1010040070" => "-ppn_realisasi",//ppn in jasa
//                        ),
//                        "static" => array(
//                            "cabang_id" => "placeID",
//                            "extern_id" => "pihakID",
//                            "extern_nama" => "pihakName",
//                            "jenis" => "jenisTr",
//                            "transaksi_no" => "nomer",
//                        ),
//                        "srcGateName" => "main",
//                        "srcRawGateName" => "main",
//                    ),
//                ),
//                "detail" => array(),
//            ),
        ),
        "postProcessor" => array(
            "3463ro" => array(),
            "3463o" => array(
                "master" => array(
                    array(
                        "comName" => "TransaksiStatus",
                        "loop" => array(
                            "3463o" => "grand_total",
                        ),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "cabang_nama" => "placeName",
                            "jenis" => "stepCode",
                            "jenis_master" => "jenisTrMaster",
                            "oleh_id" => "olehID",
                            "oleh_nama" => "olehName",
                            "suppliers_id" => "pihakID",
                            "suppliers_nama" => "pihakName",
                            "id_master" => "masterID",
                            "transaksi_nilai" => "harga",
                            "diskon_nilai" => ".0",
                            "ppn_nilai" => "ppn",
                            "transaksi_net" => "grand_total",
                            "transaksi_dibayar" => ".0",
                            "transaksi_reject" => ".0",
                            "transaksi_fullfillment" => ".0",
                            "transaksi_dikirim" => "grand_total",
                            "transaksi_nett" => "grand_total",
                            "transaksi_saldo" => "grand_total",
//                            "transaksi_id" => "referenceID__2",
//                            "transaksi_no" => "referenceNomer__2",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                        "reversable" => true,
                    ),
                ),
                "detail" => array(
                    array(
                        "comName" => "PriceSupplies",
                        "loop" => array(),
                        "static" => array(
                            "produk_id" => "id",
                            "produk_nama" => "name",
                            "nilai" => "harga",
                            "cabang_id" => "placeID",
                            "oleh_id" => "olehID",
                            "oleh_nama" => "olehName",
                            "jenis" => ".jasa",
                            "jenis_value" => ".hpp",
                        ),
                        "srcGateName" => "items",
                        "srcRawGateName" => "items",
                    ),
                ),
            ),
            "3463" => array(
                "master" => array(
                    array(
                        "comName" => "LockerValue",
                        "loop" => array(),
                        "static" => array(
                            "cabang_id" => "placeID",
                            "gudang_id" => "gudangID",
                            "state" => ".hold",
                            "jenis" => ".ppn in",
                            "produk_id" => "pihakID",
                            "nama" => "pihakName",
                            "nilai" => "-nilai_dipakai_ppn_in",
                            "transaksi_id" => "currentID",
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
                            "gudang_id" => "gudangID",
                            "state" => ".hold",
                            "jenis" => ".piutang pembelian",
                            "produk_id" => "pihakID",
                            "nama" => "pihakName",
                            "nilai" => "-nilai_dipakai_piutang_pembelian",
                            "transaksi_id" => "currentID",
                            "oleh_id" => ".0",
                        ),
                        "srcGateName" => "main",
                        "srcRawGateName" => "main",
                    ),


                    // masuk transaksi_data_items, nomer ponya

//                    array(
//                        "comName" => "TransaksiItems3_sum",
//                        "loop" => array(),
//                        "static" => array(
//                            "produk_id" => "insertID",// transaksi id SRN
//                            "produk_nama" => "nomer",// nomer transaksi SRN
//                            "produk_jenis" => ".po_projek",// nomer transaksi SRN
//                            "produk_ord_jml" => ".1",
//                            "produk_ord_hrg" => "harga",//
//                            "produk_ord_diskon" => "disc",
//                            "valid_qty" => ".1",
//                            "satuan" => ".0",
//                            "transaksi_id" => "produkProjek__transaksi_id_app",
//                            "jenisTr" => "jenisTr",
//                            "referenceID" => "referenceID",
//                        ),
//                        "srcGateName" => "main",
//                        "srcRawGateName" => "main",
//                    ),
//                    array(
//                        "comName" => "TransaksiDataRegistriUpdate",
//                        "loop" => array(),
//                        "static" => array(
//                            "transaksi_id" => "produkProjek__transaksi_id_app",// transaksi id dari so yang sudah diapprove
//                            "jenisTrMaster" => "jenisTrMaster",
//                            "insertID" => "insertID",
//                            "jenisTr" => "jenisTr",
//                        ),
//                        "srcGateName" => "main",
//                        "srcRawGateName" => "main",
//                    ),

                    // locker projek

//                    array(
//                        "comName" => "LockerStockProjekMain",
//                        "loop" => array(),
//                        "static" => array(
//                            "cabang_id" => "place2ID",
//                            "jenis" => ".po_projek",
//                            "state" => ".active",
//                            "jumlah" => ".1",
//                            "produk_id" => "insertID",// transaksi id SRN
//                            "nama" => "nomer",// nomer transaksi SRN
//                            "satuan" => "satuan",
//                            "transaksi_id" => "produkProjek__transaksi_id_app",// id SO
//                            "nomer" => "produkProjek__transaksi_no_app",// nomer SO
//                            "oleh_id" => ".0",
//                            "gudang_id" => "gudang2ID",
//                        ),
//                        "srcGateName" => "main",
//                        "srcRawGateName" => "main",
//                    ),

                ),
                "detail" => array(),
            ),

        ),
        //-----
        "countersEdit" => array(
            "stepCode|placeID",
            "stepCode|olehID",
            "stepCode|placeID|olehID",
            "stepCode|supplierID",

            "stepCode|masterID",
            "stepCode|masterID|placeID",
            "stepCode|masterID|olehID",
            "stepCode|masterID|placeID|olehID",
            "stepCode|masterID|supplierID",
        ),
        "formatNotaEdit" => "stepCode|placeID",
        "countersReject" => array(
            "stepCode|placeID",
            "stepCode|olehID",
            "stepCode|placeID|olehID",
            "stepCode|supplierID",

            "stepCode|masterID",
            "stepCode|masterID|placeID",
            "stepCode|masterID|olehID",
            "stepCode|masterID|placeID|olehID",
            "stepCode|masterID|supplierID",
        ),
        "formatNotaReject" => "stepCode|placeID",
    ),


);
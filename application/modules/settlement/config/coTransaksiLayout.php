<?php

$config["coTransaksiLayout"] = array(
    //  config penyetoran
//    "759" => array(
//        "receiptTemplate" => array(
//            1 => "template/759.html",
//        ),
//        "headerNota" => array(
//            //            "pusat" => array(
//            //                "centerDetails__nama" => "name",
//            //                "cash_account_source__label" => "sumber",
//            //                "cash_account_target__label" => "bank pusat",
//            //            ),
//
//            // "dtime" => "date",
//            // "placeName" => "Branch",
//            // "tlp_1" => "phone",
//            // "alamat_1" => "address",
//            // "dtime_jatuh_tempo" => "jatuh tempo",
//            // "pembayaran" => "payment method",
//        ),
//        "fixedElements" => array(
//            1 => array(
//                "nomer" => "No.",
//                //                "nomer_top" => "SO No.",
//                "dtime" => "Date",
//                "cash_account_source__label" => "sumber dana",
//                "cash_account_target__label" => "bank pusat",
//                //                "top_nama" => "Term of Payment",
//                //                "tos_nama" => "Term of Shipment",
//                //                "capacity_nama" => "Capacity",
//                //                "dueDate_value" => "Due Date",
//            ),
//        ),
//        "headerTables" => array(
//            "extern2_nama" => "customer",
//            "produk_nama" => "product name",
//            "produk_ord_hrg" => "price",
//            "produk_ord_jml" => "jumlah",
//            "sub_total" => "sub total",
//        ),
//        "receiptMainFields" => array(
//            "jenis_label" => "activity",
//            "nomer" => "reference no.",
//            "result_nomer" => "receipt no.",
//            "customers_nama" => "customer",
//            "dtime" => "date",
//            "bank_rekening_nama" => "cash account",
//        ),
//        "receiptDetailFields" => array(
//            1 => array(
//                "extern2_nama" => "customer",
//                "produk_nama" => "product name",
//                "produk_ord_jml" => "qty",
//                "satuan" => "uom",
//                //            "hpp" => "price",
//                //            "ppn" => "ppn",
//            ),
//        ),
//        "receiptSumFields" => array(
//            1 => array(
//                "sisa" => "receivable amount",
//                "creditAmount" => "paid using credit",
//                "nilai_entry" => "paid using cash account",
//                "nilai_bayar" => "total amount of payment",
//                "new_sisa" => "remain receivable (from list)",
//            ),
//        ),
//        "receiptNumFields" => array(
//            1 => array(
//                "sisa" => "due remain",
//            ),
//        ),
//        "receipNumFields" => array(
//            1 => array(
//                "sisa" => "due remain",
//            ),
//        ),
//        "reportSumFields" => array(
//            "customers_id" => "customers_nama",
//
//        ),
//        "printLocation" => "Printing/viewReceiptReg/",
//        "receiptInword" => array(),
//    ),
    "759" => array(
        "receiptTemplate" => array(
            1 => "template/758r.html",
            2 => "template/758.html",
        ),
        "headerNota" => array(
            "dtime" => "date",
            "customers_nama" => "Customer",
            "tlp_1" => "phone",
            "alamat_1" => "address",
            "dtime_jatuh_tempo" => "jatuh tempo",
            "pembayaran" => "payment method",
        ),
        "fixedElements" => array(
            1 => array(
                "nomer" => "No.",
                //                "nomer_top" => "SO No.",
                "dtime" => "Date",
                "cash_account_source__label" => "sumber dana",
                "cash_account_target__label" => "bank pusat",

                //                "top_nama" => "Term of Payment",
                //                "tos_nama" => "Term of Shipment",
                //                "capacity_nama" => "Capacity",
                //                "dueDate_value" => "Due Date",
            ),
            2 => array(
                "nomer" => "No.",
                //                "nomer_top" => "SO No.",
                "dtime" => "Date",
                "cash_account_source__label" => "sumber dana",
                "cash_account_target__label" => "bank pusat",

                //                "top_nama" => "Term of Payment",
                //                "tos_nama" => "Term of Shipment",
                //                "capacity_nama" => "Capacity",
                //                "dueDate_value" => "Due Date",
            ),
        ),
        "headerTables" => array(
            "extern2_nama" => "customer",
            "produk_nama" => "product name",
            "produk_ord_hrg" => "price",
            "produk_ord_jml" => "jumlah",
            "sub_total" => "sub total",
        ),
        "receiptMainFields" => array(
            "jenis_label" => "activity",
            "nomer" => "receipt no.",
            "dtime" => "date",
            "sellerName" => "penyetor",
            "settlementMethod__label" => "metode setoran",
            "cash_account_target__label" => "pos akun penerima",
        ),
        "receiptDetailFields" => array(
            1 => array(
                "extern2_nama" => "customer",
                "produk_nama" => "product name",
                "produk_ord_jml" => "qty",
                "satuan" => "uom",
            ),
            2 => array(
                "extern2_nama" => "konsumen",
                "produk_nama" => "nomer transaksi",
                "cash_account_nama" => "akun kas/bank",
            ),
        ),
        "receiptDetailFields3" => array(
            1 => array(
                "cash_account_nama" => "akun kas/bank",
//                "produk_nama" => "nomer transaksi",
            ),
            2 => array(
                "cash_account_nama" => "akun kas/bank",
//                "produk_nama" => "nomer transaksi",
            ),
        ),
        "receiptNumFields" => array(
            1 => array(
                "nilai_bayar" => "disetor",
            ),
            2 => array(
                "nilai_bayar" => "disetor",
            ),
        ),
        "receipNumFields" => array(
            1 => array(
                "nilai_bayar" => "disetor",
            ),
            2 => array(
                "nilai_bayar" => "disetor",
            ),
        ),
        "receiptSumFields" => array(
            1 => array(
                "sisa" => "receivable amount",
                "creditAmount" => "paid using credit",
                "nilai_entry" => "paid using cash account",
                "nilai_bayar" => "total amount of payment",
                "new_sisa" => "remain receivable (from list)",
            ),
            2 => array(
                "nilai_bayar" => "receivable amount",

            ),
        ),
        "receiptSumFields3" => array(
            2 => array(
                "subtotal" => "total",
            ),
        ),
        "reportSumFields" => array(
            "customers_id" => "customers_nama",

        ),
        "printLocation" => "Printing/viewReceiptReg/",
        "receiptInword" => array(),

        "reviewDetailCompactListsLabel" => array(
            "nama" => "product name",
            "sisa" => "due remain",
            "tagihan" => "total price",
        ),
        "reviewMainCompactListsLabel" => array(
            "nomer" => "Nomer",
            "dtime" => "Date",
            "cabang_nama" => "Branch",
        ),
        "reviewCompactListDetailSum" => array(
            //            "." => "-",
            //            "*" => "-",
            //            "-" => "-",
            "tagihan" => "grand total",
        ),
        "reviewCompactListSum" => array(
            //            "harga" => "Amount",
            //            "ppn" => "VAT",
            //            "nett" => "Grand Total",
        ),
        "reviewAddRows" => array(
            //            "top__nama" => "pembayaran",
            //            "dp" => "downpayment",
            //            "paymentMethod" => "paymentMethod",
        ),
        "reviewSign" => array(
            1 => array(
                "sign_1",
            ),
            2 => array(
                "sign_1",
                "sign_2",
            ),
        ),

    ),
    "758" => array(
        "receiptTemplate" => array(
            1 => "template/758r.html",
            2 => "template/758.html",
        ),
        "headerNota" => array(
            "dtime" => "date",
            "customers_nama" => "Customer",
            "tlp_1" => "phone",
            "alamat_1" => "address",
            "dtime_jatuh_tempo" => "jatuh tempo",
            "pembayaran" => "payment method",
        ),
        "fixedElements" => array(
            1 => array(
                "nomer" => "No.",
                //                "nomer_top" => "SO No.",
                "dtime" => "Date",
                "cash_account_source__label" => "sumber dana",
                "cash_account_target__label" => "bank pusat",

                //                "top_nama" => "Term of Payment",
                //                "tos_nama" => "Term of Shipment",
                //                "capacity_nama" => "Capacity",
                //                "dueDate_value" => "Due Date",
            ),
            2 => array(
                "nomer" => "No.",
                //                "nomer_top" => "SO No.",
                "dtime" => "Date",
                "cash_account_source__label" => "sumber dana",
                "cash_account_target__label" => "bank pusat",

                //                "top_nama" => "Term of Payment",
                //                "tos_nama" => "Term of Shipment",
                //                "capacity_nama" => "Capacity",
                //                "dueDate_value" => "Due Date",
            ),
        ),
        "headerTables" => array(
            "extern2_nama" => "customer",
            "produk_nama" => "product name",
            "produk_ord_hrg" => "price",
            "produk_ord_jml" => "jumlah",
            "sub_total" => "sub total",
        ),
// START OF COMPLETE REPEATED LOGIC
        "receiptMainFields" => array(
            "jenis_label" => "activity",
            "nomer" => "receipt no.",
            "dtime" => "date",
            "sellerName" => "penyetor",
            "settlementMethod__label" => "metode setoran",
            "cash_account_target__label" => "pos akun penerima",
        ),
// END OF COMPLETE REPEATED LOGIC
        "receiptDetailFields" => array(
            1 => array(
                "extern2_nama" => "customer",
                "produk_nama" => "product name",
                "produk_ord_jml" => "qty",
                "satuan" => "uom",
            ),
            2 => array(
                "extern2_nama" => "konsumen",
                "produk_nama" => "nomer transaksi",
                "cash_account_nama" => "akun kas/bank",
            ),
        ),
        "receiptDetailFields3" => array(
            1 => array(
                "cash_account_nama" => "akun kas/bank",
//                "produk_nama" => "nomer transaksi",
            ),
            2 => array(
                "cash_account_nama" => "akun kas/bank",
//                "produk_nama" => "nomer transaksi",
            ),
        ),
        "receiptNumFields" => array(
            1 => array(
                "nilai_bayar" => "disetor",
            ),
            2 => array(
                "nilai_bayar" => "disetor",
            ),
        ),
        "receipNumFields" => array(
            1 => array(
                "nilai_bayar" => "disetor",
            ),
            2 => array(
                "nilai_bayar" => "disetor",
            ),
        ),
        "receiptSumFields" => array(
            1 => array(
                "sisa" => "receivable amount",
                "creditAmount" => "paid using credit",
                "nilai_entry" => "paid using cash account",
                "nilai_bayar" => "total amount of payment",
                "new_sisa" => "remain receivable (from list)",
            ),
            2 => array(
                "nilai_bayar" => "receivable amount",

            ),
        ),
        "receiptSumFields3" => array(
            2 => array(
                "subtotal" => "total",
            ),
        ),
        "reportSumFields" => array(
            "customers_id" => "customers_nama",

        ),
        "printLocation" => "Printing/viewReceiptReg/",
        "receiptInword" => array(),

        "reviewDetailCompactListsLabel" => array(
            "nama" => "product name",
            "sisa" => "due remain",
            "tagihan" => "total price",
        ),
        "reviewMainCompactListsLabel" => array(
            "nomer" => "Nomer",
            "dtime" => "Date",
            "cabang_nama" => "Branch",
        ),
        "reviewCompactListDetailSum" => array(
            //            "." => "-",
            //            "*" => "-",
            //            "-" => "-",
            "tagihan" => "grand total",
        ),
        "reviewCompactListSum" => array(
            //            "harga" => "Amount",
            //            "ppn" => "VAT",
            //            "nett" => "Grand Total",
        ),
        "reviewAddRows" => array(
            //            "top__nama" => "pembayaran",
            //            "dp" => "downpayment",
            //            "paymentMethod" => "paymentMethod",
        ),
        "reviewSign" => array(
            1 => array(
                "sign_1",
            ),
            2 => array(
                "sign_1",
                "sign_2",
            ),
        ),

    ),



);
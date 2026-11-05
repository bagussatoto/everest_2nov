<?php
// START OF COMPLETE REPEATED LOGIC
$title = isset($title) ? $title : "Tracer Stok Invoice";
$subTitle = isset($subTitle) ? $subTitle : "Pelacak Mutasi Stok Persediaan per Invoice";
$searchMode = isset($searchMode) ? $searchMode : "";
$isFinancial = isset($isFinancial) ? (bool)$isFinancial : false;
$hasFinancialAccess = isset($hasFinancialAccess) ? (bool)$hasFinancialAccess : false;
$financialInvoices = isset($financialInvoices) ? $financialInvoices : array();

$pageTemplate = "application/template/default.html";
$p = new Layout($title, $subTitle, $pageTemplate);

$content = "";

// Header Card & Kotak Pencarian
$content .= "<div class='box box-solid box-primary' style='border-radius:4px; box-shadow:0 2px 5px rgba(0,0,0,0.1);'>";
$content .= "<div class='box-header with-border' style='background-color:#3c8dbc; color:#fff;'>";
$content .= "<h3 class='box-title' style='font-size:16px; font-weight:600;'><i class='fa fa-search'></i> Pencarian Dokumen Invoice / Nota</h3>";
$content .= "</div>";
$content .= "<div class='box-body' style='padding:20px;'>";
$content .= "<form method='GET' action='" . $thisPage . "' class='form-inline'>";
$content .= "<div class='form-group' style='margin-right:15px;'>";
$content .= "<label for='q' style='margin-right:12px; font-size:14px;'>Kata Kunci / Nomor Dokumen:</label>";
$content .= "<input type='text' class='form-control input-lg' style='min-width:380px; font-weight:bold; letter-spacing:0.8px; border-radius:3px;' id='q' name='q' value='" . htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') . "' placeholder='Nomor nota (5822-***, spo-***, INV.1.***) atau nama konsumen...' autofocus required>";
$content .= "</div>";
$content .= "<button type='submit' class='btn btn-primary btn-lg' style='border-radius:3px; font-weight:bold;'><i class='fa fa-search'></i> Lacak Produk & Mutasi</button> ";
if (!empty($keyword)) {
    $content .= "<a href='" . $thisPage . "' class='btn btn-default btn-lg' style='border-radius:3px;'><i class='fa fa-refresh'></i> Reset</a>";
}
$content .= "</form>";
$content .= "<p class='text-muted' style='margin-top:10px; margin-bottom:0; font-size:12px;'><i class='fa fa-info-circle'></i> Mendukung format dengan pemisah strip (-) maupun titik (.), pencarian awalan nota, kemiripan nomor, serta pencarian nama rekanan/konsumen.</p>";
$content .= "</div>";
$content .= "</div>";

// Alert Not Found / Error
if (!empty($errMsg)) {
    $content .= "<div class='alert alert-warning alert-dismissible' style='border-radius:4px; font-size:14px; margin-top:15px;'>";
    $content .= "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
    $content .= "<h4><i class='icon fa fa-warning'></i> Pemberitahuan</h4>";
    $content .= htmlspecialchars($errMsg, ENT_QUOTES, 'UTF-8');
    $content .= "</div>";
}

// Multi-Matches Table (Hasil Pencarian Kemiripan, Awalan, atau Nama Konsumen)
if (isset($matches) && count($matches) > 0) {
    $headerColor = "#00c0ef";
    $headerTitle = "<i class='fa fa-list'></i> Ditemukan " . count($matches) . " Dokumen yang Cocok";
    if ($searchMode === 'customer') {
        $headerColor = "#00a65a";
        $headerTitle = "<i class='fa fa-users'></i> Ditemukan " . count($matches) . " Dokumen untuk Pihak / Rekanan: &ldquo;" . htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') . "&rdquo;";
    } elseif ($searchMode === 'similarity') {
        $headerColor = "#f39c12";
        $headerTitle = "<i class='fa fa-lightbulb-o'></i> Ditemukan " . count($matches) . " Transaksi dengan Kemiripan Nomor";
    } elseif ($searchMode === 'prefix') {
        $headerTitle = "<i class='fa fa-list'></i> Ditemukan " . count($matches) . " Transaksi dengan Awalan Serupa";
    }

    $content .= "<div class='box box-solid box-info' style='border-radius:4px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-top:15px;'>";
    $content .= "<div class='box-header with-border' style='background-color:" . $headerColor . "; color:#fff;'>";
    $content .= "<h3 class='box-title' style='font-size:15px; font-weight:600;'><i class='fa fa-list'></i> " . $headerTitle . "</h3>";
    $content .= "</div>";
    
    if ($searchMode === 'customer') {
        $content .= "<div style='padding:10px 15px; background-color:#f0fdf4; border-bottom:1px solid #dcfce7; color:#166534; font-size:13px;'>";
        $content .= "<i class='fa fa-info-circle'></i> Menampilkan riwayat transaksi terbaru yang berhubungan dengan rekanan/konsumen <b>" . htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') . "</b>. Klik pada nomor dokumen untuk memilih dan melacak mutasi stok:";
        $content .= "</div>";
    } elseif ($searchMode === 'similarity') {
        $content .= "<div style='padding:10px 15px; background-color:#fffdf5; border-bottom:1px solid #faebcc; color:#8a6d3b; font-size:13px;'>";
        $content .= "<i class='fa fa-info-circle'></i> Nomor yang persis sama tidak ditemukan. Sistem menampilkan transaksi terbaru dengan kemiripan nomor/keyword. Silakan klik nomor dokumen untuk membukanya:";
        $content .= "</div>";
    }

    $content .= "<div class='box-body table-responsive no-padding'>";
    $content .= "<table class='table table-hover table-striped table-bordered' style='margin-bottom:0;'>";
    $content .= "<thead style='background-color:#eaf6fa;'>";
    $content .= "<tr>";
    $content .= "<th class='text-center' style='width:45px;'>No</th>";
    $content .= "<th>Nomor Dokumen & Referensi (Klik untuk Memilih)</th>";
    $content .= "<th style='width:160px;'>Jenis & Tahap</th>";
    $content .= "<th style='width:120px;'>Tanggal Transaksi</th>";
    $content .= "<th>Konsumen / Pihak</th>";
    $content .= "<th>Lokasi Cabang & Gudang</th>";
    $content .= "</tr>";
    $content .= "</thead>";
    $content .= "<tbody>";
    $mIdx = 1;
    foreach ($matches as $m) {
        $mitra = !empty($m->konsumen_label) ? $m->konsumen_label : (!empty($m->customers_nama) ? $m->customers_nama : (!empty($m->suppliers_nama) ? $m->suppliers_nama : "-"));
        $subMitra = !empty($m->sub_pihak) ? "<br><small class='text-muted'><i class='fa fa-truck'></i> " . htmlspecialchars($m->sub_pihak, ENT_QUOTES, 'UTF-8') . "</small>" : "";

        $pickUrl = $thisPage . "?q=" . urlencode($keyword) . "&id=" . $m->id . "&exact=1";
        
        $nomerText = "<a href='" . $pickUrl . "' class='text-primary' style='font-size:15px; font-weight:bold; text-decoration:underline;' title='Klik untuk memilih dan melacak dokumen ini'>";
        $nomerText .= "<i class='fa fa-file-text-o'></i> " . htmlspecialchars($m->nomer, ENT_QUOTES, 'UTF-8');
        $nomerText .= " <i class='fa fa-arrow-circle-right' style='font-size:12px; margin-left:3px;'></i>";
        $nomerText .= "</a>";
        if (!empty($m->nomer2)) {
            $nomerText .= "<br><small class='text-muted'><i class='fa fa-tag'></i> No. 2: <b>" . htmlspecialchars($m->nomer2, ENT_QUOTES, 'UTF-8') . "</b></small>";
        }
        if (!empty($m->ref_doc)) {
            $nomerText .= "<br><small class='text-success'><i class='fa fa-link'></i> Ref: <b>" . htmlspecialchars($m->ref_doc, ENT_QUOTES, 'UTF-8') . "</b></small>";
        }

        $jenisBadge = "<span class='label label-default text-uppercase' style='font-size:12px;'>" . htmlspecialchars($m->jenis, ENT_QUOTES, 'UTF-8') . "</span>";
        if (isset($m->is_financial) && $m->is_financial) {
            $jenisBadge .= " <span class='badge bg-orange' style='font-size:10px;' title='Dokumen Pembayaran / Keuangan'><i class='fa fa-credit-card'></i> BAYAR</span>";
        }
        if (!empty($m->step_label)) {
            $jenisBadge .= " <span class='badge bg-light-blue' style='font-size:10px;'>" . htmlspecialchars($m->step_label, ENT_QUOTES, 'UTF-8') . "</span>";
        }
        if (!empty($m->jenis_deskripsi)) {
            $jenisBadge .= "<br><small class='text-muted' style='font-weight:500;'>" . htmlspecialchars($m->jenis_deskripsi, ENT_QUOTES, 'UTF-8') . "</small>";
        }

        $lokasiText = !empty($m->lokasi_label) ? $m->lokasi_label : "-";

        if ($mitra !== '-' && !empty($mitra)) {
            $custSearchUrl = $thisPage . "?q=" . urlencode($mitra);
            $mitraHtml = "<a href='" . $custSearchUrl . "' class='text-navy' style='font-weight:bold; text-decoration:none; border-bottom:1px dashed #3c8dbc;' title='Klik untuk mencari transaksi lain dari pihak/konsumen ini: " . htmlspecialchars($mitra, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($mitra, ENT_QUOTES, 'UTF-8') . " <i class='fa fa-search text-muted' style='font-size:10px; margin-left:3px;'></i></a>";
        } else {
            $mitraHtml = "-";
        }

        $content .= "<tr>";
        $content .= "<td class='text-center' style='vertical-align:middle;'>" . $mIdx++ . "</td>";
        $content .= "<td style='vertical-align:middle;'>" . $nomerText . "</td>";
        $content .= "<td style='vertical-align:middle;'>" . $jenisBadge . "</td>";
        $content .= "<td style='vertical-align:middle;'><i class='fa fa-calendar text-muted'></i> " . htmlspecialchars(substr($m->fulldate, 0, 10), ENT_QUOTES, 'UTF-8') . "</td>";
        $content .= "<td style='vertical-align:middle;'>" . $mitraHtml . $subMitra . "</td>";
        $content .= "<td style='vertical-align:middle; font-size:13px;'>" . $lokasiText . "</td>";
        $content .= "</tr>";
    }
    $content .= "</tbody>";
    $content .= "</table>";
    $content .= "</div>";
    $content .= "</div>";
}

// Single Document Result (Dokumen Terpilih)
if (isset($dataNota) && $dataNota !== null) {
    $mitra = !empty($dataNota->konsumen_nama) ? $dataNota->konsumen_nama : (!empty($dataNota->customers_nama) ? $dataNota->customers_nama : (!empty($dataNota->suppliers_nama) ? $dataNota->suppliers_nama : "-"));
    $subMitra = !empty($dataNota->sub_pihak) ? "<br><small class='text-muted'><i class='fa fa-truck'></i> " . htmlspecialchars($dataNota->sub_pihak, ENT_QUOTES, 'UTF-8') . "</small>" : "";

    $docHeaderTitle = htmlspecialchars($dataNota->nomer, ENT_QUOTES, 'UTF-8');
    if (!empty($dataNota->nomer2)) {
        $docHeaderTitle .= " <span style='font-size:13px; font-weight:normal;'>(No. 2: " . htmlspecialchars($dataNota->nomer2, ENT_QUOTES, 'UTF-8') . ")</span>";
    }

    $boxColor = $isFinancial ? "box-warning" : "box-success";
    $headerBg = $isFinancial ? "#f39c12" : "#00a65a";
    $headerIcon = $isFinancial ? "fa-credit-card" : "fa-file-text-o";
    $borderLeftColor = $isFinancial ? "#f39c12" : "#00a65a";

    $jenisBadge = "<span class='label label-info text-uppercase' style='font-size:12px; padding:3px 8px;'>" . htmlspecialchars($dataNota->jenis, ENT_QUOTES, 'UTF-8') . "</span>";
    if (!empty($dataNota->jenis_deskripsi)) {
        $jenisBadge .= "<br><span class='text-muted' style='font-size:12px; font-weight:500;'><i class='fa fa-tag'></i> " . htmlspecialchars($dataNota->jenis_deskripsi, ENT_QUOTES, 'UTF-8') . "</span>";
    }

    $content .= "<div class='box box-solid " . $boxColor . "' style='border-radius:4px; box-shadow:0 2px 5px rgba(0,0,0,0.1); margin-top:15px;'>";
    $content .= "<div class='box-header with-border' style='background-color:" . $headerBg . "; color:#fff;'>";
    $content .= "<h3 class='box-title' style='font-size:16px; font-weight:600;'><i class='fa " . $headerIcon . "'></i> Dokumen Terpilih: <b>" . $docHeaderTitle . "</b>";
    if ($isFinancial) {
        $content .= " <span class='badge' style='background-color:#fff; color:#f39c12; margin-left:8px; font-size:11px;'><i class='fa fa-money'></i> DOKUMEN KEUANGAN / PEMBAYARAN</span>";
    }
    $content .= "</h3>";
    $content .= "</div>";
    $content .= "<div class='box-body' style='padding:20px;'>";
    
    // Header Info Grid (Enriched)
    $content .= "<div class='well well-sm' style='background-color:#f9fbf9; border-left:4px solid " . $borderLeftColor . "; margin-bottom:20px;'>";
    $content .= "<div class='row'>";
    
    // Kolom 1: Nomor Dokumen & Referensi
    $content .= "<div class='col-md-3 col-sm-6'><b>Nomor Dokumen:</b><br><span class='text-primary' style='font-size:16px; font-weight:bold;'>" . htmlspecialchars($dataNota->nomer, ENT_QUOTES, 'UTF-8') . "</span>";
    if (!empty($dataNota->nomer2)) {
        $content .= "<br><small class='text-muted'><i class='fa fa-tag'></i> No. 2: <b>" . htmlspecialchars($dataNota->nomer2, ENT_QUOTES, 'UTF-8') . "</b></small>";
    }
    if (!empty($dataNota->ref_doc)) {
        $content .= "<br><small class='text-success'><i class='fa fa-link'></i> Ref: <b>" . htmlspecialchars($dataNota->ref_doc, ENT_QUOTES, 'UTF-8') . "</b></small>";
    }
    $content .= "</div>";

    // Kolom 2: Tanggal & Jenis Dokumen
    $content .= "<div class='col-md-3 col-sm-6'><b>Tanggal & Jenis:</b><br><span style='font-size:14px; font-weight:500;'><i class='fa fa-calendar'></i> " . htmlspecialchars($dataNota->fulldate, ENT_QUOTES, 'UTF-8') . "</span><br style='margin-bottom:4px;'>" . $jenisBadge . "</div>";

    // Kolom 3: Konsumen / Pihak
    if ($mitra !== '-' && !empty($mitra)) {
        $custSearchUrl = $thisPage . "?q=" . urlencode($mitra);
        $mitraDetailHtml = "<a href='" . $custSearchUrl . "' style='color:#2c3e50; text-decoration:none; border-bottom:1px dashed #3c8dbc;' title='Klik untuk mencari transaksi lain dari pihak ini: " . htmlspecialchars($mitra, ENT_QUOTES, 'UTF-8') . "'>" . htmlspecialchars($mitra, ENT_QUOTES, 'UTF-8') . " <i class='fa fa-search text-muted' style='font-size:11px; margin-left:3px;'></i></a>";
    } else {
        $mitraDetailHtml = "-";
    }
    $content .= "<div class='col-md-3 col-sm-6'><b>Konsumen / Pihak:</b><br><span style='font-size:14px; font-weight:bold;'><i class='fa fa-user'></i> " . $mitraDetailHtml . "</span>" . $subMitra . "</div>";

    // Kolom 4: Lokasi Cabang & Gudang
    $lokasiInfo = !empty($dataNota->lokasi_info) ? $dataNota->lokasi_info : "-";
    $content .= "<div class='col-md-3 col-sm-6'><b>Cabang / Gudang:</b><br><span style='font-size:13px; color:#333;'><i class='fa fa-building-o'></i> " . $lokasiInfo . "</span></div>";

    $content .= "</div>";
    $content .= "</div>";

    // =========================================================================
    // KONDISI 1: DOKUMEN KEUANGAN TAPI HAK AKSES DIBATASI (BUKAN ADMIN/FINANCE)
    // =========================================================================
    if ($isFinancial && !$hasFinancialAccess) {
        $content .= "<div class='alert alert-warning' style='border-radius:4px; padding:20px; background-color:#fffdf5; border:1px solid #faebcc; color:#8a6d3b;'>";
        $content .= "<h4 style='font-weight:700; color:#c05621; margin-top:0;'><i class='fa fa-lock'></i> Akses Dokumen Keuangan Dibatasi</h4>";
        $content .= "<p style='font-size:14px; line-height:1.6; margin-bottom:12px;'>";
        $content .= "Dokumen <b>" . htmlspecialchars($dataNota->nomer, ENT_QUOTES, 'UTF-8') . "</b> teridentifikasi sebagai dokumen <b>Pembayaran Keuangan / Pelunasan Hutang-Piutang (Financial Settlement)</b>.<br>";
        $content .= "Sesuai kebijakan hak akses sistem, informasi mutasi kas/bank dan rincian pelunasan keuangan dibatasi dan hanya dapat dibuka oleh divisi <b>Finance</b> atau <b>Administrator / Manajemen</b>.";
        $content .= "</p>";
        $content .= "<hr style='border-top:1px solid #faebcc; margin:12px 0;'>";
        $content .= "<p style='font-size:13px; color:#666; margin-bottom:0;'>";
        $content .= "<i class='fa fa-lightbulb-o text-yellow'></i> <b>Petunjuk untuk Bagian Gudang / Kasir:</b><br>";
        $content .= "Jika Anda bermaksud melacak mutasi fisik stok barang di gudang, silakan gunakan nomor <b>Surat Jalan (DO)</b>, <b>Penerimaan Barang (SJ / LPB)</b>, <b>Faktur Penjualan (INV)</b>, atau <b>Purchase Order (PO)</b> terkait.";
        $content .= "</p>";
        $content .= "</div>";
    }

    // =========================================================================
    // KONDISI 2: DOKUMEN KEUANGAN DAN MEMILIKI HAK AKSES (ADMIN / FINANCE)
    // =========================================================================
    elseif ($isFinancial && $hasFinancialAccess) {
        $content .= "<div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;'>";
        $content .= "<h4 style='font-weight:600; margin:0; color:#2c3e50;'><i class='fa fa-list-alt text-green'></i> Rincian Faktur / Tagihan yang Dilunasi</h4>";
        $content .= "<span class='text-muted' style='font-size:12px;'><i class='fa fa-info-circle'></i> Klik pada <b>Nomor Faktur</b> untuk menelusuri mutasi fisik barang dan nomor serial</span>";
        $content .= "</div>";

        $content .= "<div class='alert alert-info' style='border-radius:3px; padding:10px 15px; margin-bottom:15px; font-size:13px;'>";
        $content .= "<i class='fa fa-info-circle'></i> Dokumen ini adalah bukti pembayaran kas/bank (bukan pergerakan fisik barang). Di bawah ini adalah daftar faktur pembelian/penjualan yang dilunasi oleh dokumen ini. Silakan klik tautan pada <b>Nomor Faktur</b> untuk melihat rincian barang fisik dan kartu stoknya:";
        $content .= "</div>";

        if (isset($financialInvoices) && count($financialInvoices) > 0) {
            $content .= "<div class='table-responsive'>";
            $content .= "<table class='table table-bordered table-striped table-hover'>";
            $content .= "<thead style='background-color:#3c8dbc; color:#fff;'>";
            $content .= "<tr>";
            $content .= "<th class='text-center' style='width:45px;'>No</th>";
            $content .= "<th class='text-center' style='width:95px;'>ID Faktur</th>";
            $content .= "<th>Nomor Faktur Pembelian / Tagihan</th>";
            $content .= "<th style='width:125px;'>Tanggal Faktur</th>";
            $content .= "<th>Konsumen / Pihak Rekanan</th>";
            $content .= "<th class='text-right' style='width:160px;'>Nilai Pelunasan</th>";
            $content .= "<th style='width:170px;'>No. Seri Faktur Pajak</th>";
            $content .= "</tr>";
            $content .= "</thead>";
            $content .= "<tbody>";
            $fIdx = 1;
            foreach ($financialInvoices as $fin) {
                $drillUrl = $thisPage . "?id=" . $fin['transaksi_id'] . "&exact=1";
                $fNomerHtml = "<a href='" . $drillUrl . "' style='font-size:14px; font-weight:bold; color:#0d6efd; text-decoration:underline;' title='Klik untuk membuka faktur ini dan melacak stok produk'>";
                $fNomerHtml .= "<i class='fa fa-file-text-o'></i> " . htmlspecialchars($fin['nomer'], ENT_QUOTES, 'UTF-8');
                $fNomerHtml .= "</a>";
                if (!empty($fin['nomer2'])) {
                    $fNomerHtml .= "<br><small class='text-muted'><i class='fa fa-tag'></i> No. 2: <b>" . htmlspecialchars($fin['nomer2'], ENT_QUOTES, 'UTF-8') . "</b></small>";
                }
                if (!empty($fin['jenis'])) {
                    $fNomerHtml .= " <span class='label label-default text-uppercase' style='font-size:10px; margin-left:4px;'>" . htmlspecialchars($fin['jenis'], ENT_QUOTES, 'UTF-8') . "</span>";
                }
                if (!empty($fin['jenis_deskripsi'])) {
                    $fNomerHtml .= "<br><small class='text-muted' style='font-weight:500;'><i class='fa fa-tag'></i> " . htmlspecialchars($fin['jenis_deskripsi'], ENT_QUOTES, 'UTF-8') . "</small>";
                }

                $fMitra = !empty($fin['mitra']) ? $fin['mitra'] : "-";
                if ($fMitra !== '-' && !empty($fMitra)) {
                    $custSearchUrl = $thisPage . "?q=" . urlencode($fMitra);
                    $fMitraHtml = "<a href='" . $custSearchUrl . "' class='text-navy' style='font-weight:bold; text-decoration:none; border-bottom:1px dashed #3c8dbc;' title='Klik untuk mencari transaksi lain dari pihak ini: " . htmlspecialchars($fMitra, ENT_QUOTES, 'UTF-8') . "'>";
                    $fMitraHtml .= "<i class='fa fa-user text-muted' style='font-size:11px; margin-right:3px;'></i>" . htmlspecialchars($fMitra, ENT_QUOTES, 'UTF-8');
                    $fMitraHtml .= " <i class='fa fa-search text-muted' style='font-size:10px; margin-left:2px;'></i>";
                    $fMitraHtml .= "</a>";
                } else {
                    $fMitraHtml = "<span class='text-muted'>-</span>";
                }
                if (!empty($fin['lokasi'])) {
                    $fMitraHtml .= "<br><small class='text-muted' style='font-size:11px;'><i class='fa fa-building-o'></i> " . htmlspecialchars($fin['lokasi'], ENT_QUOTES, 'UTF-8') . "</small>";
                }

                $eFakturHtml = !empty($fin['e_faktur']) ? "<span class='label label-default' style='font-size:11px;'><i class='fa fa-qrcode'></i> " . htmlspecialchars($fin['e_faktur'], ENT_QUOTES, 'UTF-8') . "</span>" : "<span class='text-muted'>-</span>";

                $content .= "<tr>";
                $content .= "<td class='text-center' style='vertical-align:middle;'>" . $fIdx++ . "</td>";
                $content .= "<td class='text-center' style='vertical-align:middle;'><span class='badge bg-gray' style='color:#333; font-weight:bold;'>#" . (int)$fin['transaksi_id'] . "</span></td>";
                $content .= "<td style='vertical-align:middle;'>" . $fNomerHtml . "</td>";
                $content .= "<td style='vertical-align:middle;'><i class='fa fa-calendar text-muted'></i> " . htmlspecialchars($fin['tgl_faktur'], ENT_QUOTES, 'UTF-8') . "</td>";
                $content .= "<td style='vertical-align:middle;'>" . $fMitraHtml . "</td>";
                $content .= "<td class='text-right' style='vertical-align:middle; font-size:14px; font-weight:bold; color:#198754;'>Rp " . number_format($fin['nominal'], 2, ',', '.') . "</td>";
                $content .= "<td style='vertical-align:middle;'>" . $eFakturHtml . "</td>";
                $content .= "</tr>";
            }
            $content .= "</tbody>";
            $content .= "</table>";
            $content .= "</div>";
        } else {
            $content .= "<div class='alert alert-warning' style='border-radius:4px;'><i class='fa fa-warning'></i> Rincian nota atau faktur yang dilunasi tidak tercatat pada dokumen ini.</div>";
        }
    }

    // =========================================================================
    // KONDISI 3: DOKUMEN LOGISTIK / STOK FISIK REGULER
    // =========================================================================
    else {
        // Subtitle Tabel Produk
        $content .= "<div style='display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;'>";
        $content .= "<h4 style='font-weight:600; margin:0;'><i class='fa fa-cubes text-yellow'></i> Rincian Produk & Akses Mutasi Stok</h4>";
        $content .= "<span class='text-muted' style='font-size:12px;'><i class='fa fa-info-circle'></i> Klik pada Kode atau Nama Produk untuk membuka kartu mutasi stok fisik (Ledger) di tab baru</span>";
        $content .= "</div>";

        if (isset($items) && count($items) > 0) {
            $content .= "<div class='table-responsive'>";
            $content .= "<table class='table table-bordered table-striped table-hover'>";
            $content .= "<thead style='background-color:#3c8dbc; color:#fff;'>";
            $content .= "<tr>";
            $content .= "<th class='text-center' style='width:45px;'>No</th>";
            $content .= "<th class='text-center' style='width:85px;'>ID Produk</th>";
            $content .= "<th style='width:170px;'>Kode Produk</th>";
            $content .= "<th>Nama Produk / Deskripsi Item</th>";
            $content .= "<th class='text-right' style='width:110px;'>Qty Faktur</th>";
            $content .= "<th class='text-center' style='width:85px;'>Satuan</th>";
            $content .= "<th class='text-center' style='width:170px;'>Tipe / Kategori</th>";
            $content .= "</tr>";
            $content .= "</thead>";
            $content .= "<tbody>";
            $pIdx = 1;
            foreach ($items as $item) {
                $hasMutasi = ($item['is_stok'] && !empty($item['link_mutasi']));

                // Kode link / text
                if ($hasMutasi) {
                    $kodeHtml = "<a href='" . $item['link_mutasi'] . "' target='_blank' class='text-navy' style='font-size:13px; font-weight:bold; text-decoration:underline;' title='Klik untuk membuka kartu mutasi stok (Ledger) di tab baru'>";
                    $kodeHtml .= "<i class='fa fa-line-chart text-primary' style='font-size:11px; margin-right:4px;'></i>" . htmlspecialchars($item['kode'], ENT_QUOTES, 'UTF-8');
                    $kodeHtml .= "</a>";
                } else {
                    $kodeHtml = "<b class='text-navy' style='font-size:13px;'>" . htmlspecialchars($item['kode'], ENT_QUOTES, 'UTF-8') . "</b>";
                }

                // Nama link / text + Serial Button
                if ($hasMutasi) {
                    $namaHtml = "<a href='" . $item['link_mutasi'] . "' target='_blank' style='color:#2c3e50; font-weight:600; text-decoration:none;' onmouseover='this.style.textDecoration=\"underline\"' onmouseout='this.style.textDecoration=\"none\"' title='Klik untuk membuka kartu mutasi stok (Ledger) di tab baru'>";
                    $namaHtml .= htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8');
                    $namaHtml .= "</a>";
                } else {
                    $namaHtml = "<span style='font-weight:500; color:#333;'>" . htmlspecialchars($item['nama'], ENT_QUOTES, 'UTF-8') . "</span>";
                }

                // Sematkan tombol Serial di samping Nama Produk jika unit/memiliki serial
                if (!empty($item['link_serial'])) {
                    $namaHtml .= " <a href='" . $item['link_serial'] . "' target='_blank' class='btn btn-xs btn-warning' style='margin-left:6px; border-radius:3px; font-weight:bold; padding:2px 7px;' title='Lacak Nomor Serial Item Ini'>";
                    $namaHtml .= "<i class='fa fa-qrcode'></i> Serial";
                    $namaHtml .= "</a>";
                }

                // Non-stok label jika bukan stok
                if (!$item['is_stok']) {
                    $namaHtml .= " <span class='label label-default' style='font-size:10px; margin-left:5px;'><i class='fa fa-ban'></i> Non-Stok</span>";
                }

                $content .= "<tr>";
                $content .= "<td class='text-center' style='vertical-align:middle;'>" . $pIdx++ . "</td>";
                $content .= "<td class='text-center' style='vertical-align:middle;'><span class='badge bg-gray' style='color:#333; font-weight:bold; font-size:12px; padding:3px 7px;'>#" . (int)$item['produk_id'] . "</span></td>";
                $content .= "<td style='vertical-align:middle;'>" . $kodeHtml . "</td>";
                $content .= "<td style='vertical-align:middle;'>" . $namaHtml . "</td>";
                $content .= "<td class='text-right' style='vertical-align:middle; font-size:14px;'><b>" . number_format($item['qty'], 2, ',', '.') . "</b></td>";
                $content .= "<td class='text-center' style='vertical-align:middle;'><span class='badge bg-gray' style='color:#333; font-weight:600; padding:4px 8px;'>" . htmlspecialchars($item['satuan'], ENT_QUOTES, 'UTF-8') . "</span></td>";
                $content .= "<td class='text-center' style='vertical-align:middle;'>";
                if (!empty($item['tipe']) && $item['tipe'] !== "-") {
                    $content .= "<span class='badge bg-purple'>" . htmlspecialchars($item['tipe'], ENT_QUOTES, 'UTF-8') . "</span>";
                } else {
                    $content .= "<span class='text-muted'>-</span>";
                }
                $content .= "</td>";
                $content .= "</tr>";
            }
            $content .= "</tbody>";
            $content .= "</table>";
            $content .= "</div>";
        } else {
            $content .= "<div class='alert alert-info' style='border-radius:4px;'><i class='fa fa-info-circle'></i> Dokumen ini tidak memuat rincian item produk atau belum terindeks di registri.</div>";
        }
    }

    $content .= "</div>";
    $content .= "</div>";
}

$content .= "<script>
$(document).ready(function() {
    if (typeof top.close_holdon === 'function') top.close_holdon();
    if (typeof close_holdon === 'function') close_holdon();
    if (typeof top.HoldOn !== 'undefined' && typeof top.HoldOn.close === 'function') {
        top.HoldOn.close();
    }
    if (typeof HoldOn !== 'undefined' && typeof HoldOn.close === 'function') {
        HoldOn.close();
    }
});
</script>";

$p->addTags(array(
    "menu_left"        => callMenuLeft(),
    "float_menu_atas"  => callFloatMenu('atas'),
    "float_menu_bawah" => callFloatMenu(),
    "menu_taskbar"     => callMenuTaskbar(),
    "btn_back"         => callBackNav(),
    "content"          => $content,
    "title"            => $title,
    "sub_title"        => $subTitle,
    "profile_name"     => $this->session->login['nama'],
));

$p->render();
// END OF COMPLETE REPEATED LOGIC

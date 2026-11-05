<?php
// START OF COMPLETE REPEATED LOGIC
if (!defined('BASEPATH')) exit('No direct script access allowed');
?>

<style>
    .report-wrapper {
        width: 100%;
        background: #ffffff;
        border-radius: 4px;
        border: 1px solid #d2d6de;
        overflow: hidden;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 13px;
        color: #2d3748;
        line-height: 1.5;
        margin-bottom: 20px;
    }

    .report-wrapper * {
        box-sizing: border-box;
    }

    .report-body {
        padding: 16px 20px;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 22px 0 10px 0;
        padding-bottom: 6px;
        border-bottom: 2px solid #edf2f7;
    }

    .section-header h3 {
        font-size: 13px;
        font-weight: 700;
        color: #2d3748;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }

    .badge-iso {
        font-size: 10px;
        background: #ebf8ff;
        color: #2b6cb0;
        padding: 2px 6px;
        border-radius: 3px;
        font-weight: 600;
        border: 1px solid #bee3f8;
    }

    /* KPI Cards */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 15px;
    }

    .kpi-card {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 14px 16px;
        border-left: 4px solid #cbd5e0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .kpi-card.info { border-left-color: #3182ce; }
    .kpi-card.success { border-left-color: #38a169; }
    .kpi-card.warning { border-left-color: #dd6b20; }

    .kpi-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #718096;
        font-weight: 600;
    }

    .kpi-value {
        font-size: 18px;
        font-weight: 800;
        margin: 6px 0;
        font-family: 'SFMono-Regular', Consolas, "Liberation Mono", Menlo, monospace;
    }

    .kpi-sub {
        font-size: 11px;
        color: #a0aec0;
    }

    .text-blue { color: #2b6cb0; }
    .text-green { color: #276749; }
    .text-red { color: #c53030; }
    .text-purple { color: #6b46c1; }

    /* Aging Grid */
    .aging-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 15px;
    }

    .aging-box {
        padding: 12px 14px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
    }

    .aging-current { background: #f0fff4; border-color: #c6f6d5; }
    .aging-warning { background: #fffff0; border-color: #fefcbf; }
    .aging-due { background: #fffaf0; border-color: #feebc8; }
    .aging-overdue { background: #fff5f5; border-color: #fed7d7; }

    .aging-title {
        display: flex;
        justify-content: space-between;
        font-size: 11px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .aging-amount {
        font-size: 15px;
        font-weight: 800;
        font-family: 'SFMono-Regular', Consolas, monospace;
    }

    /* Filter Toolbar */
    .filter-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f7fafc;
        padding: 10px 14px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        margin-bottom: 12px;
    }

    .filter-tabs {
        display: flex;
        gap: 6px;
    }

    .tab-btn {
        padding: 6px 12px;
        font-size: 12px;
        font-weight: 600;
        border-radius: 4px;
        border: 1px solid #cbd5e0;
        background: #fff;
        color: #4a5568;
        cursor: pointer;
        transition: all 0.2s;
    }

    .tab-btn.active {
        background: #1a365d;
        color: #fff;
        border-color: #1a365d;
    }

    .search-box {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #fff;
        border: 1px solid #cbd5e0;
        border-radius: 4px;
        padding: 5px 10px;
    }

    .search-box input {
        border: none;
        outline: none;
        font-size: 12px;
        width: 180px;
    }

    /* Data Table */
    .data-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border: 1px solid #e2e8f0;
        font-size: 12px;
    }

    .data-table th {
        background: #f8fafc;
        color: #4a5568;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        padding: 10px 12px;
        border-bottom: 2px solid #cbd5e0;
        border-top: 1px solid #e2e8f0;
        text-align: left;
    }

    .data-table td {
        padding: 9px 12px;
        border-bottom: 1px solid #edf2f7;
        color: #2d3748;
    }

    .data-table tr:hover {
        background-color: #f7fafc;
    }

    .data-table tr.row-zero {
        background-color: #fafafa;
        opacity: 0.85;
    }

    .data-table tfoot td {
        background: #edf2f7;
        font-weight: 800;
        border-top: 2px solid #cbd5e0;
        padding: 10px 12px;
    }

    .btn-drill {
        padding: 4px 8px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 3px;
        border: 1px solid #cbd5e0;
        background: #ebf8ff;
        color: #2b6cb0;
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-drill:hover {
        background: #bee3f8;
    }

    /* Drilldown Accordion */
    .drilldown-row {
        background: #f7fafc;
    }

    .drilldown-container {
        padding: 14px 18px;
        background: #ffffff;
        border: 1px solid #cbd5e0;
        border-radius: 4px;
        margin: 6px 0;
    }

    .sub-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        margin-top: 8px;
    }

    .sub-table th {
        background: #edf2f7;
        padding: 6px 8px;
        font-weight: 700;
        color: #4a5568;
        border-bottom: 1px solid #cbd5e0;
    }

    .sub-table td {
        padding: 6px 8px;
        border-bottom: 1px solid #edf2f7;
    }

    .badge-status {
        display: inline-block;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        font-weight: 700;
    }

    .badge-active { background: #feebc8; color: #c05621; }
    .badge-zero { background: #edf2f7; color: #718096; }

    @media print {
        .no-print { display: none !important; }
        .report-wrapper { border: none !important; box-shadow: none !important; }
        body { padding: 0 !important; background: #fff !important; }
    }
</style>

<div class="report-wrapper">

    <div class="report-body" style="padding-top:10px;">

        <!-- 1. Executive Summary KPI Cards -->
        <div class="section-header" style="margin-top:0;">
            <h3><span class="badge-iso">ISO 9001: 7.5.3</span> 1. Ringkasan Eksekutif &amp; Posisi Piutang Klaim</h3>
            <span style="font-size:11px; color:#718096;">Model: <code>ComRekeningPembantuPiutangSupplierMain</code></span>
        </div>

        <div class="kpi-grid">
            <div class="kpi-card info">
                <div class="kpi-label">Total Klaim Masuk (Debet)</div>
                <div class="kpi-value text-blue">Rp <?php echo number_format($totalDebet, 0, ',', '.'); ?></div>
                <div class="kpi-sub">Total pengakuan hak diskon nota</div>
            </div>
            <div class="kpi-card success">
                <div class="kpi-label">Realisasi Pemakaian (Kredit)</div>
                <div class="kpi-value text-green">Rp <?php echo number_format($totalKredit, 0, ',', '.'); ?></div>
                <div class="kpi-sub">Dicairkan via dokumen klaim #3333</div>
            </div>
            <div class="kpi-card warning">
                <div class="kpi-label">Saldo Aktif Berjalan (Net)</div>
                <div class="kpi-value text-red">Rp <?php echo number_format($totalNet, 0, ',', '.'); ?></div>
                <div class="kpi-sub"><?php echo $totalActiveVendors; ?> vendor memiliki hak tagih aktif</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-label">Rasio Realisasi Klaim</div>
                <div class="kpi-value text-purple"><?php echo number_format($realizationRatio, 1, ',', '.'); ?>%</div>
                <div class="kpi-sub">Dari total hak piutang terbentuk</div>
            </div>
        </div>

        <!-- 2. Aging Schedule Schedule -->
        <div class="section-header">
            <h3><span class="badge-iso">IFRS 15 / PSAK 72</span> 2. Distribusi Umur Piutang Klaim (Aging Schedule)</h3>
            <span style="font-size:11px; color:#718096;">Dihitung dari sisa saldo nota riil</span>
        </div>

        <div class="aging-grid">
            <div class="aging-box aging-current">
                <div class="aging-title">
                    <span>0 &ndash; 30 Hari</span>
                    <span style="color:#2f855a;">Lancar</span>
                </div>
                <div class="aging-amount text-green">Rp <?php echo number_format($agingData['aging_0_30'], 0, ',', '.'); ?></div>
            </div>
            <div class="aging-box aging-warning">
                <div class="aging-title">
                    <span>31 &ndash; 60 Hari</span>
                    <span style="color:#b7791f;">Perhatian</span>
                </div>
                <div class="aging-amount" style="color:#b7791f;">Rp <?php echo number_format($agingData['aging_31_60'], 0, ',', '.'); ?></div>
            </div>
            <div class="aging-box aging-due">
                <div class="aging-title">
                    <span>61 &ndash; 90 Hari</span>
                    <span style="color:#c05621;">Jatuh Tempo</span>
                </div>
                <div class="aging-amount" style="color:#c05621;">Rp <?php echo number_format($agingData['aging_61_90'], 0, ',', '.'); ?></div>
            </div>
            <div class="aging-box aging-overdue">
                <div class="aging-title">
                    <span>&gt; 90 Hari</span>
                    <span style="color:#c53030;">Tertunggak / Audit</span>
                </div>
                <div class="aging-amount text-red">Rp <?php echo number_format($agingData['aging_over_90'], 0, ',', '.'); ?></div>
            </div>
        </div>

        <!-- 3. Rekapitulasi Sub-Ledger per Mitra Supplier -->
        <div class="section-header">
            <h3><span class="badge-iso">Level 1 Sub-Ledger</span> 3. Rekapitulasi Saldo per Mitra Supplier</h3>
            <span style="font-size:11px; color:#718096;">Total: <?php echo $totalVendors; ?> Supplier Terdaftar</span>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="filter-bar no-print">
            <div class="filter-tabs">
                <button type="button" class="tab-btn active" data-filter="all">
                    Semua Supplier (<?php echo $totalVendors; ?>)
                </button>
                <button type="button" class="tab-btn" data-filter="active">
                    Hanya Saldo Aktif (<?php echo $totalActiveVendors; ?>)
                </button>
                <button type="button" class="tab-btn" data-filter="zero">
                    Hanya Saldo Rp 0 (<?php echo $totalZeroVendors; ?>)
                </button>
            </div>
            <div class="search-box">
                <i class="fa fa-search" style="color:#a0aec0;"></i>
                <input type="text" id="vendorSearchInput" placeholder="Cari nama atau ID supplier...">
            </div>
        </div>

        <table class="data-table" id="vendorTable">
            <thead>
                <tr>
                    <th style="width:40px;" class="text-center">No</th>
                    <th style="width:80px;" class="text-center">ID Supp</th>
                    <th>Nama Mitra Supplier</th>
                    <th style="width:130px;">Rek ID</th>
                    <th style="width:150px;" class="text-right">Debet Masuk (Rp)</th>
                    <th style="width:150px;" class="text-right">Kredit Keluar (Rp)</th>
                    <th style="width:160px;" class="text-right">Saldo Akhir (Rp)</th>
                    <th style="width:80px;" class="text-center">Status</th>
                    <th style="width:100px;" class="text-center no-print">Aksi Drilldown</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $no = 1;
                // Gabungkan vendor aktif terlebih dahulu, baru vendor saldo 0
                $allVendors = array_merge($vendorsActive, $vendorsZero);
                foreach ($allVendors as $v): 
                    $isZero = ($v['saldo'] == 0);
                    $rowClass = $isZero ? 'row-zero' : 'row-active';
                ?>
                <tr class="vendor-row <?php echo $rowClass; ?>" data-supplier-id="<?php echo $v['id']; ?>" data-name="<?php echo strtolower(htmlspecialchars($v['nama'])); ?>" data-zero="<?php echo $isZero ? '1' : '0'; ?>">
                    <td class="text-center" style="color:#718096;"><?php echo $no++; ?></td>
                    <td class="text-center font-mono"><strong><?php echo $v['id']; ?></strong></td>
                    <td>
                        <span style="font-weight:600; color:#1a365d;"><?php echo htmlspecialchars($v['nama']); ?></span>
                    </td>
                    <td class="font-mono text-muted" style="font-size:11px;"><?php echo htmlspecialchars($v['rek_id']); ?></td>
                    <td class="text-right font-mono text-blue">
                        <?php echo number_format($v['debet'], 2, ',', '.'); ?>
                    </td>
                    <td class="text-right font-mono text-muted">
                        <?php echo number_format($v['kredit'], 2, ',', '.'); ?>
                    </td>
                    <td class="text-right font-mono" style="font-weight:700; color:<?php echo ($v['saldo'] > 0 ? '#c53030' : '#718096'); ?>;">
                        <?php echo number_format($v['saldo'], 2, ',', '.'); ?>
                    </td>
                    <td class="text-center">
                        <?php if ($v['saldo'] > 0): ?>
                            <span class="badge-status badge-active">Aktif</span>
                        <?php else: ?>
                            <span class="badge-status badge-zero">Nol (Lunas)</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center no-print">
                        <button type="button" class="btn-drill toggle-drilldown" data-id="<?php echo $v['id']; ?>" data-name="<?php echo htmlspecialchars($v['nama']); ?>">
                            <i class="fa fa-list-ul"></i> Mutasi <i class="fa fa-caret-down"></i>
                        </button>
                    </td>
                </tr>
                <!-- Accordion Row untuk Level 2 Mutasi Detail -->
                <tr id="drilldown-<?php echo $v['id']; ?>" class="drilldown-row" style="display:none;">
                    <td colspan="9" style="padding:0;">
                        <div class="drilldown-container">
                            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                                <strong style="color:#1a365d; font-size:12px;">
                                    <i class="fa fa-history"></i> Buku Pembantu Transaksi Dokumen: <?php echo htmlspecialchars($v['nama']); ?> (ID: <?php echo $v['id']; ?>)
                                </strong>
                                <span style="font-size:11px; color:#718096;">
                                    Sumber Model: <code>ComRekeningPembantuPiutangSupplierMain::fetchMoves()</code>
                                </span>
                            </div>
                            <div class="drilldown-content" id="drilldown-content-<?php echo $v['id']; ?>">
                                <div style="text-align:center; padding:15px; color:#718096;">
                                    <i class="fa fa-spinner fa-spin"></i> Mengambil jejak mutasi dokumen dari server...
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="text-right">TOTAL KONSOLIDASI (IDR):</td>
                    <td class="text-right font-mono text-blue"><?php echo number_format($totalDebet, 2, ',', '.'); ?></td>
                    <td class="text-right font-mono text-muted"><?php echo number_format($totalKredit, 2, ',', '.'); ?></td>
                    <td class="text-right font-mono text-red" style="font-size:14px;"><?php echo number_format($totalNet, 2, ',', '.'); ?></td>
                    <td colspan="2" class="text-center" style="font-size:11px; color:#718096;">34 Rekanan Mitra</td>
                </tr>
            </tfoot>
        </table>



    </div>
</div>

<script>
$(document).ready(function() {
    var drilldownCache = {};

    // 1. Tab Filtering (All / Active / Zero)
    $('.tab-btn').on('click', function() {
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        var filter = $(this).data('filter');

        applyFilters();
    });

    // 2. Search Live Input
    $('#vendorSearchInput').on('keyup', function() {
        applyFilters();
    });

    function applyFilters() {
        var activeFilter = $('.tab-btn.active').data('filter');
        var query = $('#vendorSearchInput').val().toLowerCase().trim();

        $('.vendor-row').each(function() {
            var row = $(this);
            var isZero = row.data('zero') == '1';
            var name = row.data('name');
            var sId = String(row.data('supplier-id'));

            var matchesFilter = true;
            if (activeFilter === 'active' && isZero) {
                matchesFilter = false;
            } else if (activeFilter === 'zero' && !isZero) {
                matchesFilter = false;
            }

            var matchesSearch = true;
            if (query.length > 0) {
                if (name.indexOf(query) === -1 && sId.indexOf(query) === -1) {
                    matchesSearch = false;
                }
            }

            if (matchesFilter && matchesSearch) {
                row.show();
            } else {
                row.hide();
                // Sembunyikan juga drilldown yang terbuka jika row induk disembunyikan
                var sIdVal = row.data('supplier-id');
                $('#drilldown-' + sIdVal).hide();
            }
        });
    }

    // 3. Drill-down Mutasi Vendor (Level 2 via AJAX)
    $('.toggle-drilldown').on('click', function(e) {
        e.preventDefault();
        var btn = $(this);
        var sId = btn.data('id');
        var sName = btn.data('name');
        var rowTarget = $('#drilldown-' + sId);
        var tableContainer = $('#drilldown-content-' + sId);

        if (rowTarget.is(':visible')) {
            rowTarget.hide();
            btn.html('<i class="fa fa-list-ul"></i> Mutasi <i class="fa fa-caret-down"></i>');
            return;
        }

        // Tampilkan row
        rowTarget.show();
        btn.html('<i class="fa fa-list-ul"></i> Tutup <i class="fa fa-caret-up"></i>');

        // Jika sudah pernah dimuat (cached), tidak perlu AJAX ulang
        if (drilldownCache[sId]) {
            return;
        }

        tableContainer.html('<div style="text-align:center; padding:15px; color:#718096;"><i class="fa fa-spinner fa-spin"></i> Mengambil jejak mutasi dokumen dari server...</div>');

        $.ajax({
            url: '<?php echo $drilldownUrl; ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                supplier_id: sId,
                rekening: '<?php echo htmlspecialchars($rekening); ?>'
            },
            success: function(res) {
                if (res.status === 'success') {
                    var tableHtml = '<table class="sub-table">' +
                        '<thead>' +
                            '<tr>' +
                                '<th style="width:40px;" class="text-center">No</th>' +
                                '<th style="width:120px;">Waktu Posting</th>' +
                                '<th style="width:90px;" class="text-center">Jenis</th>' +
                                '<th style="width:140px;">No. Dokumen</th>' +
                                '<th>Keterangan / Audit Trail</th>' +
                                '<th style="width:130px;" class="text-right">Masuk / Debet (Rp)</th>' +
                                '<th style="width:130px;" class="text-right">Keluar / Kredit (Rp)</th>' +
                                '<th style="width:140px;" class="text-right">Running Balance (Rp)</th>' +
                                '<th style="width:70px;" class="text-center">Status</th>' +
                            '</tr>' +
                        '</thead>' +
                        '<tbody>' +
                            res.html +
                        '</tbody>' +
                    '</table>';
                    tableContainer.html(tableHtml);
                    drilldownCache[sId] = true;
                } else {
                    tableContainer.html('<div style="text-align:center; color:#c53030; padding:15px;"><i class="fa fa-exclamation-triangle"></i> ' + (res.message || 'Gagal memuat mutasi.') + '</div>');
                }
            },
            error: function() {
                tableContainer.html('<div style="text-align:center; color:#c53030; padding:15px;"><i class="fa fa-exclamation-triangle"></i> Gagal menghubungi server.</div>');
            }
        });
    });
});
</script>
<!-- // END OF COMPLETE REPEATED LOGIC -->

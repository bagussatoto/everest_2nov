<?php
// START OF COMPLETE REPEATED LOGIC
$global = isset($kpiData['global']) ? $kpiData['global'] : array(
    'total_sisa' => 0,
    'total_cabang' => 0,
    'total_penyetor' => 0,
    'total_nota' => 0,
    'total_overdue' => 0
);
$cabangList = isset($kpiData['cabang']) ? $kpiData['cabang'] : array();
$context = isset($context) ? $context : 'settlement'; // 'welcome' atau 'settlement'
?>

<!-- KPI SUMMARY CARDS WIDGET (REKOMENDASI OPSI A: BRANCH-FIRST HIERARCHY) -->
<style>
.kpi-settlement-container {
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    padding: 16px;
    margin-bottom: 20px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.08);
}
.kpi-title-header {
    font-size: 15px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.kpi-title-header span {
    color: #0f172a;
    font-weight: 800;
}
.kpi-collapse-btn {
    background: #f0f9ff;
    border: 1.5px solid #0284c7;
    color: #0284c7;
    padding: 5px 14px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s ease;
}
.kpi-collapse-btn:hover {
    background: #0284c7;
    color: #ffffff;
}
#kpi-content-collapsible {
    margin-top: 14px;
}

/* Global Macro Cards */
.kpi-global-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-bottom: 16px;
}
.kpi-global-card {
    flex: 1;
    min-width: 220px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 6px;
    padding: 12px 16px;
}
.kpi-card-danger {
    background: #fff5f5;
    border-color: #f87171;
}
.kpi-card-warning {
    background: #fffdf5;
    border-color: #fbbf24;
}
.kpi-card-label {
    font-size: 12px;
    font-weight: 800;
    color: #0f172a;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.kpi-card-value {
    font-size: 22px;
    font-weight: 800;
    color: #0f172a;
    margin-top: 4px;
}
.kpi-card-subtext {
    font-size: 12px;
    font-weight: 700;
    color: #1e293b;
    margin-top: 2px;
}

/* Quick Filter Pills */
.kpi-pills-bar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    padding: 10px 14px;
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    margin-bottom: 16px;
}
.kpi-pills-label {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
    margin-right: 4px;
}
.kpi-pill-btn {
    border: 1.5px solid #0284c7;
    background: #ffffff;
    color: #0f172a;
    padding: 5px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s ease;
}
.kpi-pill-btn:hover {
    background: #e0f2fe;
    border-color: #0369a1;
}
.kpi-pill-btn.active {
    background: #0284c7;
    border-color: #0284c7;
    color: #ffffff;
}

/* Branch Cards Grid */
.kpi-branch-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 14px;
}
.kpi-branch-card {
    flex: 1;
    min-width: 320px;
    background: #ffffff;
    border: 1.5px solid #cbd5e1;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transition: all 0.2s ease;
}
.kpi-branch-card:hover {
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    border-color: #94a3b8;
}
.kpi-branch-header {
    background: #f8fafc;
    border-bottom: 1.5px solid #cbd5e1;
    padding: 12px 16px;
}
.kpi-branch-title-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.kpi-branch-name {
    font-size: 14px;
    font-weight: 800;
    color: #0f172a;
    text-transform: uppercase;
}
.kpi-branch-summary {
    margin-top: 5px;
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
}
.kpi-branch-summary strong {
    color: #0284c7;
    font-weight: 800;
}
.kpi-branch-body {
    padding: 12px 16px;
    flex: 1;
}
.kpi-subledger-title {
    font-size: 12px;
    font-weight: 800;
    color: #0f172a;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}
.kpi-cashier-row {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 8px 12px;
    margin-bottom: 8px;
    transition: background 0.15s ease;
}
.kpi-cashier-row:hover {
    background: #f8fafc;
    border-color: #94a3b8;
}
.kpi-cashier-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}
.kpi-cashier-name {
    font-size: 13px;
    font-weight: 800;
    color: #0f172a;
}
.kpi-cashier-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
}
.kpi-cashier-amount {
    font-size: 15px;
    font-weight: 800;
    color: #0284c7;
}
.kpi-cashier-info {
    font-size: 12px;
    font-weight: 700;
    color: #0f172a;
    margin-left: 4px;
}

/* Status Aging Badges */
.kpi-badge-aging {
    display: inline-block;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: 800;
    border-radius: 12px;
    text-transform: uppercase;
}
.badge-aging-normal {
    background: #dcfce7;
    color: #14532d;
    border: 1px solid #86efac;
}
.badge-aging-warning {
    background: #fef9c3;
    color: #713f12;
    border: 1px solid #fde047;
}
.badge-aging-overdue {
    background: #fee2e2;
    color: #7f1d1d;
    border: 1px solid #fca5a5;
}

/* Action Buttons */
.kpi-branch-footer {
    background: #f8fafc;
    border-top: 1.5px solid #cbd5e1;
    padding: 10px 16px;
    display: flex;
    justify-content: space-between;
    gap: 8px;
}
.kpi-action-btn {
    padding: 8px 14px;
    font-size: 12px;
    font-weight: 800;
    border-radius: 4px;
    text-decoration: none;
    cursor: pointer;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.kpi-btn-mini {
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 800;
    border-radius: 4px;
    border: 1.5px solid #0284c7;
    background: #f0f9ff;
    color: #0284c7;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s ease;
}
.kpi-btn-mini:hover {
    background: #0284c7;
    color: #ffffff;
}
.btn-kpi-welcome {
    background: #0284c7;
    color: #ffffff !important;
}
.btn-kpi-welcome:hover {
    background: #0369a1;
}
.btn-kpi-settlement {
    background: #0284c7;
    color: #ffffff !important;
    border: 1px solid #0284c7;
}
.btn-kpi-settlement:hover {
    background: #0369a1;
}
</style>

<div class="kpi-settlement-container">
    <div class="kpi-title-header">
        <span><i class="fa fa-calculator"></i> REKAPITULASI KAS CABANG BELUM DISETOR</span>
        <button type="button" class="kpi-collapse-btn" id="btn-kpi-collapse" onclick="kpiToggleCollapse()">
            <i class="fa fa-chevron-up" id="kpi-collapse-icon"></i> <span id="kpi-collapse-text">Ciutkan Ringkasan</span>
        </button>
    </div>

    <div id="kpi-content-collapsible">
        <!-- BARIS 1: KARTU TOTAL GLOBAL (MAKRO) -->
        <div class="kpi-global-grid">
            <div class="kpi-global-card">
                <div class="kpi-card-label"><i class="fa fa-money"></i> SALDO KAS BELUM DISETOR</div>
                <div class="kpi-card-value">Rp <?php echo number_format($global['total_sisa'], 0, ',', '.'); ?></div>
                <div class="kpi-card-subtext"><?php echo intval($global['total_nota']); ?> Transaksi Penerimaan</div>
            </div>

            <div class="kpi-global-card kpi-card-warning">
                <div class="kpi-card-label"><i class="fa fa-building"></i> SEBARAN KANTOR CABANG</div>
                <div class="kpi-card-value"><?php echo intval($global['total_cabang']); ?> <span style="font-size:14px; font-weight:800;">Cabang</span></div>
                <div class="kpi-card-subtext"><?php echo intval($global['total_penyetor']); ?> Kasir Pengelola Kas</div>
            </div>

            <div class="kpi-global-card kpi-card-danger">
                <div class="kpi-card-label"><i class="fa fa-exclamation-triangle"></i> SETORAN TERLAMBAT (> 24 JAM)</div>
                <div class="kpi-card-value" style="color:#b91c1c;"><?php echo intval($global['total_overdue']); ?> <span style="font-size:14px; font-weight:800;">Kasir</span></div>
                <?php if (intval($global['total_overdue']) == 0): ?>
                    <div class="kpi-card-subtext" style="color:#16a34a;"><i class="fa fa-check-circle"></i> Kepatuhan SOP Terjaga</div>
                <?php else: ?>
                    <div class="kpi-card-subtext" style="color:#991b1b;"><i class="fa fa-clock-o"></i> Membutuhkan Konfirmasi Penyetoran</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- BARIS 2: QUICK FILTER PILLS (NAVIGASI CEPAT CABANG) -->
        <?php if (sizeof($cabangList) > 0): ?>
            <div class="kpi-pills-bar">
                <span class="kpi-pills-label"><i class="fa fa-filter"></i> Filter Cabang:</span>
                <button type="button" class="kpi-pill-btn active" onclick="kpiFilterBranchCards('all', this)">
                    🔘 Semua (Rp <?php echo number_format($global['total_sisa'], 0, ',', '.'); ?>)
                </button>
                <?php foreach ($cabangList as $c): ?>
                    <?php 
                        $namaCabangClean = preg_replace('/^cabang\s+/i', '', trim($c['cabang_nama']));
                    ?>
                    <button type="button" class="kpi-pill-btn" onclick="kpiFilterBranchCards('<?php echo $c['cabang_id']; ?>', this)">
                        🏢 <?php echo htmlspecialchars($namaCabangClean); ?> (Rp <?php echo number_format($c['total_sisa'], 0, ',', '.'); ?>)
                    </button>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- BARIS 3: GRID KARTU PER-CABANG (OPSI A: BRANCH-FIRST HIERARCHY) -->
        <?php if (sizeof($cabangList) > 0): ?>
            <div class="kpi-branch-grid">
                <?php foreach ($cabangList as $c): ?>
                    <?php
                        $branchBadgeClass = 'badge-aging-normal';
                        $branchStatusText = 'Lancar (< 24 Jam)';
                        if ($c['worst_status_aging'] == 'overdue') {
                            $branchBadgeClass = 'badge-aging-overdue';
                            $branchStatusText = 'Terlambat (> 48 Jam)';
                        } elseif ($c['worst_status_aging'] == 'warning') {
                            $branchBadgeClass = 'badge-aging-warning';
                            $branchStatusText = 'Perhatian (24-48 Jam)';
                        }
                        $cashiers = isset($c['penyetor_list']) ? $c['penyetor_list'] : array();
                        $cashierCount = sizeof($cashiers);
                    ?>
                    <div class="kpi-branch-card" data-cabang-id="<?php echo $c['cabang_id']; ?>" data-cabang-nama="<?php echo htmlspecialchars($c['cabang_nama']); ?>">
                        <!-- Header Cabang -->
                        <div class="kpi-branch-header">
                            <div class="kpi-branch-title-row">
                                <span class="kpi-branch-name"><i class="fa fa-building"></i> <?php echo htmlspecialchars($c['cabang_nama']); ?></span>
                                <span class="kpi-badge-aging <?php echo $branchBadgeClass; ?>"><?php echo $branchStatusText; ?></span>
                            </div>
                            <div class="kpi-branch-summary">
                                Saldo Kas: <strong>Rp <?php echo number_format($c['total_sisa'], 0, ',', '.'); ?></strong> &bull; <?php echo intval($c['total_nota']); ?> Transaksi
                            </div>
                        </div>

                        <!-- Body Cabang: Sub-ledger Daftar Kasir -->
                        <div class="kpi-branch-body">
                            <div class="kpi-subledger-title">
                                <i class="fa fa-users"></i> Pengelola Kas (<?php echo $cashierCount; ?> Kasir):
                            </div>
                            <?php foreach ($cashiers as $p): ?>
                                <?php
                                    $pBadgeClass = 'badge-aging-normal';
                                    $pStatusText = 'Lancar (< 24 Jam)';
                                    if ($p['status_aging'] == 'overdue') {
                                        $pBadgeClass = 'badge-aging-overdue';
                                        $pStatusText = 'Terlambat (> 48 Jam)';
                                    } elseif ($p['status_aging'] == 'warning') {
                                        $pBadgeClass = 'badge-aging-warning';
                                        $pStatusText = 'Perhatian (24-48 Jam)';
                                    }
                                ?>
                                <div class="kpi-cashier-row">
                                    <div class="kpi-cashier-header">
                                        <span class="kpi-cashier-name"><i class="fa fa-user-circle"></i> <?php echo htmlspecialchars($p['extern_nama']); ?></span>
                                        <span class="kpi-badge-aging <?php echo $pBadgeClass; ?>" style="font-size:10px; padding:2px 7px;"><?php echo $pStatusText; ?></span>
                                    </div>
                                    <div class="kpi-cashier-meta">
                                        <div>
                                            <span class="kpi-cashier-amount">Rp <?php echo number_format($p['total_sisa'], 0, ',', '.'); ?></span>
                                            <span class="kpi-cashier-info">(<?php echo intval($p['total_nota']); ?> Transaksi | <?php echo $p['aging_hours']; ?> Jam)</span>
                                        </div>
                                        <div>
                                            <?php if ($context == 'welcome'): ?>
                                                <a href="<?php echo base_url(); ?>settlement/Transaksi/index/758?extern_id=<?php echo $p['extern_id']; ?>" class="kpi-btn-mini btn-kpi-welcome" title="Buka transaksi kasir ini">
                                                    <i class="fa fa-external-link"></i> Buka Setoran
                                                </a>
                                            <?php else: ?>
                                                <button type="button" class="kpi-btn-mini" onclick="kpiFilterTablePenyetor('<?php echo addslashes($p['extern_nama']); ?>', '<?php echo $p['extern_id']; ?>')" title="Filter antrean khusus kasir ini">
                                                    <i class="fa fa-filter"></i> Filter Kasir
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Footer Cabang: Aksi Kolektif -->
                        <div class="kpi-branch-footer">
                            <?php if ($context == 'welcome'): ?>
                                <a href="<?php echo base_url(); ?>settlement/Transaksi/index/758?cabang_id=<?php echo $c['cabang_id']; ?>" class="kpi-action-btn btn-kpi-welcome" style="width:100%; justify-content:center;">
                                    <i class="fa fa-external-link"></i> Buka Transaksi <?php echo htmlspecialchars($c['cabang_nama']); ?>
                                </a>
                            <?php else: ?>
                                <button type="button" class="kpi-action-btn btn-kpi-settlement" onclick="kpiFilterTableCabang('<?php echo addslashes($c['cabang_nama']); ?>', '<?php echo $c['cabang_id']; ?>')" style="width:100%; justify-content:center;">
                                    <i class="fa fa-search"></i> Tampilkan <?php echo intval($c['total_nota']); ?> Transaksi
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="padding:14px; background:#f1f5f9; border-radius:6px; font-size:13px; font-weight:700; color:#0f172a; text-align:center;">
                <i class="fa fa-check-circle" style="color:#16a34a; font-size:16px;"></i> Tidak ada saldo kas cabang yang tertahan saat ini (Seluruh penerimaan kas telah disetorkan ke Kantor Pusat).
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Fungsi toggle collapsible widget ringkasan
function kpiToggleCollapse() {
    var content = $('#kpi-content-collapsible');
    var icon = $('#kpi-collapse-icon');
    var text = $('#kpi-collapse-text');
    if (content.is(':visible')) {
        content.slideUp(200);
        icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
        text.text('Tampilkan Ringkasan');
    } else {
        content.slideDown(200);
        icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
        text.text('Ciutkan Ringkasan');
    }
}

// Filter visibilitas kartu cabang berdasarkan quick filter pill
function kpiFilterBranchCards(cabangId, btnEl) {
    $('.kpi-pill-btn').removeClass('active');
    if (btnEl) {
        $(btnEl).addClass('active');
    }

    if (cabangId === 'all') {
        $('.kpi-branch-card').show();
        // Reset filter tabel transaksi jika di modul settlement
        kpiResetTableFilter();
    } else {
        $('.kpi-branch-card').hide();
        var targetCard = $('.kpi-branch-card[data-cabang-id="' + cabangId + '"]');
        targetCard.show();
        var cabangNama = targetCard.attr('data-cabang-nama');
        if (cabangNama) {
            kpiFilterTableCabang(cabangNama, cabangId);
        }
    }
}

// Filter tabel transaksi settlement berdasarkan nama cabang
function kpiFilterTableCabang(cabangNama, cabangId) {
    if (typeof $ !== 'undefined') {
        var tables = $($.fn.dataTable.tables(true)).DataTable();
        if (tables && typeof tables.search === 'function') {
            tables.search(cabangNama).draw();
        } else {
            // Fallback input search biasa
            var searchInput = $('input[type="search"]');
            if (searchInput.length > 0) {
                searchInput.val(cabangNama).trigger('keyup');
            }
        }
        kpiScrollToUndoneList();
    }
}

// Filter tabel transaksi settlement berdasarkan nama penyetor/kasir
function kpiFilterTablePenyetor(externNama, externId) {
    if (typeof $ !== 'undefined') {
        var tables = $($.fn.dataTable.tables(true)).DataTable();
        if (tables && typeof tables.search === 'function') {
            tables.search(externNama).draw();
        } else {
            // Fallback input search biasa
            var searchInput = $('input[type="search"]');
            if (searchInput.length > 0) {
                searchInput.val(externNama).trigger('keyup');
            }
        }
        kpiScrollToUndoneList();
    }
}

// Reset filter pencarian tabel
function kpiResetTableFilter() {
    if (typeof $ !== 'undefined') {
        var tables = $($.fn.dataTable.tables(true)).DataTable();
        if (tables && typeof tables.search === 'function') {
            tables.search('').draw();
        } else {
            var searchInput = $('input[type="search"]');
            if (searchInput.length > 0) {
                searchInput.val('').trigger('keyup');
            }
        }
    }
}

// Scroll halus ke area antrean transaksi (#undoneList)
function kpiScrollToUndoneList() {
    if (typeof $ !== 'undefined') {
        var target = $('#undoneList');
        if (target.length > 0) {
            $('html, body').animate({
                scrollTop: target.offset().top - 70
            }, 300);
        }
    }
}
</script>
<!-- END OF COMPLETE REPEATED LOGIC -->

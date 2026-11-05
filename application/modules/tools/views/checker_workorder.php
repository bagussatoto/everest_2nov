<?php
// START OF COMPLETE REPEATED LOGIC
$base = base_url();
$filterParams = http_build_query($filters);
$urlCsv   = $base . "tools/CheckerWorkorder?format=csv&" . $filterParams;
$urlJson  = $base . "tools/CheckerWorkorder?format=json&" . $filterParams;
$urlReset = $base . "tools/CheckerWorkorder";
?>
<!-- SweetAlert & Moment.js jika belum dimuat layout -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/id.min.js"></script>

<style>
    .wo-monitoring-container {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        color: #333;
        font-size: 13px;
        padding: 5px 0 30px 0;
    }

    /* Header Gradient Banner */
    .page-header-box {
        background: linear-gradient(135deg, #134e5e 0%, #71b280 100%);
        color: #fff;
        padding: 20px 24px;
        border-radius: 4px;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.15);
    }
    .page-header-box h2 {
        margin: 0 0 6px 0;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .page-header-box p {
        margin: 0;
        font-size: 13px;
        opacity: 0.92;
    }

    /* KPI Summary Cards */
    .kpi-row { margin-bottom: 20px; }
    .kpi-card {
        background: #fff;
        border-radius: 4px;
        padding: 14px 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        border-left: 4px solid #ccc;
        margin-bottom: 12px;
        transition: transform 0.15s ease-in-out;
    }
    .kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(0,0,0,0.12);
    }
    .kpi-card.danger  { border-left-color: #dd4b39; }
    .kpi-card.warning { border-left-color: #f39c12; }
    .kpi-card.primary { border-left-color: #3c8dbc; }
    .kpi-card.success { border-left-color: #00a65a; }
    .kpi-card.info    { border-left-color: #00c0ef; }
    .kpi-card .number { font-size: 22px; font-weight: 700; margin: 2px 0; color: #222; }
    .kpi-card .title  { font-size: 11px; text-transform: uppercase; color: #777; font-weight: 600; letter-spacing: 0.5px; }

    /* Panel Filter */
    .filter-box {
        background: #fff;
        border: 1px solid #d2d6de;
        border-radius: 4px;
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    /* Main Table Container */
    .main-card {
        background: #fff;
        border-radius: 4px;
        border: 1px solid #d2d6de;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }
    .main-card-header {
        padding: 14px 20px;
        border-bottom: 1px solid #f4f4f4;
        background-color: #f9fafc;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .main-card-header h3 {
        margin: 0;
        font-size: 16px;
        font-weight: 700;
        color: #333;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Table Styling */
    #tabel_checker_wo {
        table-layout: fixed;
        width: 100% !important;
        margin-bottom: 0;
    }
    #tabel_checker_wo thead th {
        background-color: #eef2f5;
        color: #495057;
        font-weight: 700;
        font-size: 12px;
        border-bottom: 2px solid #d2d6de;
        padding: 10px 8px;
        vertical-align: middle;
        text-align: center;
    }
    #tabel_checker_wo tbody td {
        padding: 9px 8px;
        vertical-align: middle;
        font-size: 12px;
        border-top: 1px solid #f0f0f0;
    }
    #tabel_checker_wo tbody tr:hover {
        background-color: #fdfaf2;
    }
    #tabel_checker_wo tbody tr.row-anomaly {
        background-color: #fff8f8;
    }
    #tabel_checker_wo tbody tr.row-anomaly:hover {
        background-color: #ffeef0;
    }

    /* Badges */
    .badge-flag {
        display: inline-block;
        padding: 4px 8px;
        font-size: 10px;
        font-weight: 700;
        border-radius: 3px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .badge-biaya-only {
        background-color: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    .badge-material-only {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    .badge-standar {
        background-color: #dcfce7;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }

    .badge-parent {
        display: inline-block;
        padding: 2px 6px;
        font-size: 9px;
        font-weight: 600;
        border-radius: 3px;
        margin-top: 3px;
    }
    .badge-parent-jasa { background: #fee2e2; color: #991b1b; }
    .badge-parent-unit { background: #fef3c7; color: #92400e; }
    .badge-parent-std  { background: #f3f4f6; color: #374151; }

    .badge-alert-anomaly {
        background-color: #dc2626;
        color: #fff;
        padding: 2px 6px;
        font-size: 10px;
        font-weight: 700;
        border-radius: 3px;
        display: inline-block;
        margin-bottom: 2px;
    }

    .btn-action-tool {
        padding: 3px 8px;
        font-size: 11px;
        font-weight: 600;
        border-radius: 3px;
        margin: 2px;
    }
</style>

<div class="spk-monitoring-container wo-monitoring-container">
    <!-- Header Banner -->
    <div class="page-header-box">
        <div class="row" style="display: flex; align-items: center; justify-content: space-between;">
            <div class="col-md-8">
                <h2><i class="fa fa-tasks"></i> MONITORING & CHECKER WORK ORDER</h2>
                <p>Pusat Audit Kesehatan Work Order (WO), Verifikasi Sinkronisasi Flag Proyek vs SPK, dan Deteksi Anomali Mode Kerja</p>
            </div>
            <div class="col-md-4 text-right">
                <a href="<?php echo $urlCsv; ?>" class="btn btn-sm btn-default" style="color: #333; font-weight: 600; margin-right: 5px;">
                    <i class="fa fa-file-excel-o text-green"></i> Unduh CSV
                </a>
                <a href="<?php echo $urlJson; ?>" target="_blank" class="btn btn-sm btn-default" style="color: #333; font-weight: 600;">
                    <i class="fa fa-code text-blue"></i> JSON API
                </a>
            </div>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row kpi-row">
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="kpi-card primary">
                <div class="title">Total Work Order</div>
                <div class="number"><?php echo number_format($summary['total_wo']); ?></div>
                <div style="font-size: 10px; color: #888;">Dari <?php echo $summary['total_project']; ?> Proyek Aktif</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="kpi-card danger">
                <div class="title">Anomali Terdeteksi</div>
                <div class="number text-red"><?php echo number_format($summary['total_anomali']); ?></div>
                <div style="font-size: 10px; color: #888;">Perlu Sinkronisasi / Cek</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="kpi-card warning">
                <div class="title">SPK Pending</div>
                <div class="number text-yellow"><?php echo number_format($summary['spk_pending']); ?></div>
                <div style="font-size: 10px; color: #888;">Sedang Berjalan / Belum QC</div>
            </div>
        </div>
        <div class="col-md-2 col-sm-4 col-xs-6">
            <div class="kpi-card success">
                <div class="title">SPK Selesai QC</div>
                <div class="number text-green"><?php echo number_format($summary['spk_selesai']); ?></div>
                <div style="font-size: 10px; color: #888;">QC Disetujui / Selesai</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 col-xs-6">
            <div class="kpi-card info">
                <div class="title">Distribusi Mode WO</div>
                <div style="margin-top: 5px; font-size: 11px;">
                    <span class="badge-flag badge-biaya-only" style="margin-right: 4px;">Biaya: <?php echo $summary['biaya_only']; ?></span>
                    <span class="badge-flag badge-material-only" style="margin-right: 4px;">Unit: <?php echo $summary['material_only']; ?></span>
                    <span class="badge-flag badge-standar">Std: <?php echo $summary['standar']; ?></span>
                </div>
                <div style="font-size: 10px; color: #888; margin-top: 4px;">Total <?php echo number_format($summary['total_spk']); ?> SPK Terdaftar</div>
            </div>
        </div>
        <div class="col-md-1 col-sm-2 col-xs-6 text-center" style="padding-top: 10px;">
            <a href="<?php echo $base; ?>tools/CheckerWorkorder?type=anomali" class="btn btn-block btn-danger btn-sm text-bold" style="padding: 12px 5px;" title="Tampilkan Hanya WO yang Memiliki Anomali">
                <i class="fa fa-exclamation-triangle"></i><br>FILTER ANOMALI
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="filter-box">
        <form method="GET" action="<?php echo $base; ?>tools/CheckerWorkorder" class="form-horizontal">
            <div class="row">
                <div class="col-md-5">
                    <label style="font-size: 12px; margin-bottom: 4px;">Filter Berdasarkan Proyek:</label>
                    <select name="project_id" class="form-control input-sm select2" style="width: 100%;">
                        <option value="all">-- Semua Proyek --</option>
                        <?php foreach ($projectList as $pId => $pNama): ?>
                            <option value="<?php echo $pId; ?>" <?php echo ($filters['project_id'] == $pId) ? 'selected' : ''; ?>>
                                [ID #<?php echo $pId; ?>] <?php echo htmlspecialchars($pNama); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label style="font-size: 12px; margin-bottom: 4px;">Filter Kategori / Status:</label>
                    <select name="type" class="form-control input-sm">
                        <option value="all" <?php echo ($filters['type'] === 'all') ? 'selected' : ''; ?>>-- Semua Kategori --</option>
                        <option value="anomali" <?php echo ($filters['type'] === 'anomali') ? 'selected' : ''; ?>>⚠️ Hanya Anomali / Mismatch (Prioritas)</option>
                        <option value="pending_spk" <?php echo ($filters['type'] === 'pending_spk') ? 'selected' : ''; ?>>⏳ WO dengan SPK Masih Berjalan (Pending)</option>
                        <option value="biaya_only" <?php echo ($filters['type'] === 'biaya_only') ? 'selected' : ''; ?>>🔵 Biaya Only (Jasa Murni)</option>
                        <option value="material_only" <?php echo ($filters['type'] === 'material_only') ? 'selected' : ''; ?>>🟡 Material Only (Unit Murni)</option>
                        <option value="standar" <?php echo ($filters['type'] === 'standar') ? 'selected' : ''; ?>>🟢 Standar (Biaya + Material)</option>
                    </select>
                </div>
                <div class="col-md-3" style="padding-top: 24px;">
                    <button type="submit" class="btn btn-sm btn-primary text-bold">
                        <i class="fa fa-filter"></i> Terapkan Filter
                    </button>
                    <a href="<?php echo $urlReset; ?>" class="btn btn-sm btn-default">
                        <i class="fa fa-refresh"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Main Data Card -->
    <div class="main-card">
        <div class="main-card-header">
            <h3><i class="fa fa-list"></i> DAFTAR AUDIT WORK ORDER (<?php echo count($list); ?> DATA DITAMPILKAN)</h3>
            <div>
                <span class="text-muted" style="font-size: 11px; margin-right: 10px;">
                    <i class="fa fa-info-circle text-blue"></i> Klik tombol <b>Sinkronisasi</b> untuk auto-fix mismatch flag secara aman
                </span>
            </div>
        </div>

        <div class="table-responsive" style="padding: 10px 15px;">
            <table id="tabel_checker_wo" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width: 35px;">No</th>
                        <th style="width: 200px;">Proyek Induk</th>
                        <th style="width: 220px;">Work Order</th>
                        <th style="width: 110px;">Mode WO</th>
                        <th style="width: 110px;">Komposisi (RAB)</th>
                        <th style="width: 130px;">Rekap SPK</th>
                        <th style="width: 210px;">Status Integritas / Anomali</th>
                        <th style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted" style="padding: 40px;">
                                <i class="fa fa-check-circle text-green" style="font-size: 32px;"></i><br><br>
                                <b>Tidak ada data Work Order yang cocok dengan filter yang dipilih.</b>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php $no = 1; foreach ($list as $row): 
                            $rowClass = $row['is_anomaly'] ? 'row-anomaly' : '';
                        ?>
                        <tr class="<?php echo $rowClass; ?>" id="row_wo_<?php echo $row['paket_id']; ?>">
                            <td class="text-center font-weight-bold"><?php echo $no++; ?></td>
                            
                            <!-- Proyek Induk -->
                            <td>
                                <div style="font-weight: 700; color: #1e3a8a;">
                                    <?php echo htmlspecialchars($row['project_nama']); ?>
                                </div>
                                <div style="font-size: 11px; color: #666;">
                                    ID Proyek: #<?php echo $row['project_id']; ?>
                                    <?php if (!empty($row['quot_id'])): ?>
                                        | Quot: #<?php echo $row['quot_id']; ?>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <?php if ($row['parent_type'] === 'JASA MURNI'): ?>
                                        <span class="badge-parent badge-parent-jasa"><i class="fa fa-wrench"></i> PROYEK JASA MURNI</span>
                                    <?php elseif ($row['parent_type'] === 'UNIT MURNI'): ?>
                                        <span class="badge-parent badge-parent-unit"><i class="fa fa-cube"></i> PROYEK UNIT MURNI</span>
                                    <?php else: ?>
                                        <span class="badge-parent badge-parent-std"><i class="fa fa-sliders"></i> PROYEK STANDAR</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Work Order -->
                            <td>
                                <div style="font-weight: 700; color: #111;">
                                    <?php echo htmlspecialchars($row['paket_nama']); ?>
                                </div>
                                <div style="font-size: 11px; color: #666;">
                                    ID WO: #<?php echo $row['paket_id']; ?> 
                                    <?php if (!empty($row['dtime'])): ?>
                                        | <i class="fa fa-calendar"></i> <?php echo substr($row['dtime'], 0, 10); ?>
                                    <?php endif; ?>
                                </div>
                                <div style="margin-top: 3px;">
                                    <a href="<?php echo $base; ?>master_project/MasterData/viewProdukPaket/<?php echo $row['project_id']; ?>/<?php echo $row['paket_id']; ?>" target="_blank" class="text-blue" style="font-size: 11px; text-decoration: underline;" title="Buka Detail WO di Tab Baru">
                                        <i class="fa fa-external-link"></i> Buka Editor WO
                                    </a>
                                </div>
                            </td>

                            <!-- Mode WO Flag -->
                            <td class="text-center">
                                <?php if ($row['wo_type'] === 'BIAYA ONLY'): ?>
                                    <span class="badge-flag badge-biaya-only"><i class="fa fa-wrench"></i> BIAYA ONLY</span>
                                    <div style="font-size: 10px; color: #777; margin-top: 2px;">non_material: 1</div>
                                <?php elseif ($row['wo_type'] === 'MATERIAL ONLY'): ?>
                                    <span class="badge-flag badge-material-only"><i class="fa fa-cube"></i> MATERIAL ONLY</span>
                                    <div style="font-size: 10px; color: #777; margin-top: 2px;">non_biaya: 1</div>
                                <?php else: ?>
                                    <span class="badge-flag badge-standar"><i class="fa fa-check"></i> STANDAR</span>
                                    <div style="font-size: 10px; color: #777; margin-top: 2px;">mat + biaya</div>
                                <?php endif; ?>
                            </td>

                            <!-- Komposisi RAB -->
                            <td class="text-center">
                                <div style="font-size: 11px;">
                                    <span style="display: block; margin-bottom: 2px;">
                                        <i class="fa fa-cubes text-muted"></i> Material: <b><?php echo $row['mat_count']; ?></b> item
                                    </span>
                                    <span>
                                        <i class="fa fa-money text-muted"></i> Biaya: <b><?php echo $row['biaya_count']; ?></b> item
                                    </span>
                                </div>
                            </td>

                            <!-- Rekap SPK -->
                            <td class="text-center">
                                <div style="font-size: 11px; font-weight: 600;">
                                    Total: <b><?php echo $row['total_spk']; ?> SPK</b>
                                </div>
                                <div style="margin-top: 3px; font-size: 11px;">
                                    <?php if (intval($row['spk_pending']) > 0): ?>
                                        <span class="label label-warning" title="SPK Belum Selesai QC" style="margin-right: 2px;">
                                            <i class="fa fa-clock-o"></i> <?php echo $row['spk_pending']; ?> Pending
                                        </span>
                                    <?php endif; ?>
                                    <?php if (intval($row['spk_selesai']) > 0): ?>
                                        <span class="label label-success" title="SPK Sudah Selesai QC">
                                            <i class="fa fa-check"></i> <?php echo $row['spk_selesai']; ?> QC
                                        </span>
                                    <?php endif; ?>
                                    <?php if (intval($row['total_spk']) == 0): ?>
                                        <span class="label label-default">Belum Ada SPK</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <!-- Status Integritas / Anomali -->
                            <td>
                                <?php if (!$row['is_anomaly']): ?>
                                    <span class="label label-success" style="font-size: 11px;"><i class="fa fa-check-circle"></i> KESEHATAN OK</span>
                                    <div style="font-size: 10px; color: #888; margin-top: 3px;">Flag selaras dengan tipe proyek</div>
                                <?php else: ?>
                                    <?php foreach ($row['anomalies'] as $an): ?>
                                        <div style="margin-bottom: 4px;">
                                            <span class="badge-alert-anomaly bg-<?php echo $an['color']; ?>">
                                                <i class="fa fa-warning"></i> <?php echo $an['label']; ?>
                                            </span>
                                            <div style="font-size: 11px; color: #555; line-height: 1.2;">
                                                <?php echo htmlspecialchars($an['desc']); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>

                            <!-- Tombol Aksi -->
                            <td class="text-center">
                                <button type="button" class="btn btn-xs btn-info btn-action-tool" onclick="viewSpkModal(<?php echo $row['paket_id']; ?>, '<?php echo addslashes(htmlspecialchars($row['paket_nama'])); ?>')" title="Lihat Daftar SPK di Bawah WO Ini">
                                    <i class="fa fa-list-alt"></i> SPK (<?php echo $row['total_spk']; ?>)
                                </button>
                                
                                <?php if ($row['is_anomaly']): ?>
                                    <?php 
                                    $primaryAction = $row['anomalies'][0]['action'];
                                    $actionLabel   = "Sinkronkan Flag";
                                    $btnColor      = "btn-danger";
                                    if ($primaryAction === 'set_biaya_only') {
                                        $actionLabel = "Set Biaya Only";
                                        $btnColor    = "btn-danger";
                                    } elseif ($primaryAction === 'set_material_only') {
                                        $actionLabel = "Set Unit Only";
                                        $btnColor    = "btn-warning";
                                    } elseif ($primaryAction === 'sync_spk_flags') {
                                        $actionLabel = "Sinkronkan SPK";
                                        $btnColor    = "btn-warning";
                                    }
                                    ?>
                                    <button type="button" class="btn btn-xs <?php echo $btnColor; ?> btn-action-tool" onclick="executeSyncFix(<?php echo $row['paket_id']; ?>, '<?php echo $primaryAction; ?>', '<?php echo addslashes(htmlspecialchars($row['paket_nama'])); ?>')" title="Jalankan Self-Healing untuk Sinkronisasi Flag">
                                        <i class="fa fa-magic"></i> <?php echo $actionLabel; ?>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Quick-View SPK Tasklist -->
<div class="modal fade" id="modalSpkList" tabindex="-1" role="dialog" aria-labelledby="modalSpkListLabel">
    <div class="modal-dialog modal-lg" role="document" style="width: 85%;">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #134e5e; color: #fff;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalSpkListLabel">
                    <i class="fa fa-tasks"></i> Daftar SPK di Bawah Work Order: <span id="modalWoNamaTitle" style="font-weight: 700;"></span>
                </h4>
            </div>
            <div class="modal-body" style="padding: 15px;">
                <div id="spkLoadingIndicator" class="text-center" style="padding: 30px;">
                    <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                    <p style="margin-top: 10px; color: #777;">Memuat rincian SPK...</p>
                </div>
                <div id="spkTableContainer" class="table-responsive" style="display: none;">
                    <table class="table table-bordered table-striped" style="font-size: 12px; margin-bottom: 0;">
                        <thead>
                            <tr style="background-color: #f4f4f4;">
                                <th style="width: 35px;" class="text-center">No</th>
                                <th style="width: 140px;">No SPK</th>
                                <th>Nama Tugas / SPK</th>
                                <th style="width: 120px;">Pelaksana</th>
                                <th style="width: 95px;" class="text-center">Progress</th>
                                <th style="width: 110px;">Status QC</th>
                                <th style="width: 95px;" class="text-center">Flag Mode</th>
                                <th style="width: 105px;" class="text-right">Nilai Kontrak</th>
                                <th style="width: 165px;" class="text-center">Aksi / Dokumen</th>
                            </tr>
                        </thead>
                        <tbody id="spkTableBody"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Detail Isi Lengkap SPK -->
<div class="modal fade" id="modalSpkDetail" tabindex="-1" role="dialog" aria-labelledby="modalSpkDetailLabel" style="z-index: 1060;">
    <div class="modal-dialog modal-lg" role="document" style="width: 85%;">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: #fff;">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: #fff; opacity: 0.85;">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modalSpkDetailLabel">
                    <i class="fa fa-file-text-o"></i> RINCIAN ISI LENGKAP SPK: <span id="detSpkNoTitle" style="font-weight: 700;"></span>
                </h4>
            </div>
            <div class="modal-body" style="padding: 16px; background-color: #f8fafc;">
                <div id="spkDetailLoading" class="text-center" style="padding: 40px;">
                    <i class="fa fa-spinner fa-spin fa-2x text-muted"></i>
                    <p style="margin-top: 10px; color: #777;">Memuat isi lengkap SPK...</p>
                </div>
                <div id="spkDetailContent" style="display: none;">
                    <!-- Metadata Header Box -->
                    <div class="box box-solid box-default" style="margin-bottom: 15px; border: 1px solid #e2e8f0; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="box-body" style="padding: 14px;">
                            <div class="row">
                                <div class="col-md-4" style="border-right: 1px solid #edf2f7;">
                                    <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Nomor SPK & Pekerjaan</div>
                                    <div style="font-size: 14px; font-weight: 700; color: #1e293b; margin-top: 2px;" id="detSpkNo"></div>
                                    <div style="font-size: 12px; color: #334155;" id="detSpkNama"></div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;">
                                        Proyek: <b id="detSpkProyek" style="color:#1e3a8a;"></b>
                                    </div>
                                </div>
                                <div class="col-md-4" style="border-right: 1px solid #edf2f7;">
                                    <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Pelaksana & Jadwal</div>
                                    <div style="font-size: 13px; font-weight: 700; color: #0284c7; margin-top: 2px;" id="detSpkPelaksana"></div>
                                    <div style="font-size: 11px; color: #475569;" id="detSpkJadwal"></div>
                                    <div style="font-size: 11px; color: #475569; margin-top: 4px;">
                                        Nilai Kontrak: <b style="color: #15803d;" id="detSpkNilai"></b>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div style="font-size: 11px; text-transform: uppercase; color: #64748b; font-weight: 600;">Status QC & Posting Biaya</div>
                                    <div id="detSpkQcBadge" style="margin-top: 4px;"></div>
                                    <div style="font-size: 11px; color: #475569; margin-top: 4px;" id="detSpkPostBiaya"></div>
                                    <div style="font-size: 11px; color: #64748b; margin-top: 4px;" id="detSpkModeFlag"></div>
                                </div>
                            </div>
                            <div class="row" style="margin-top: 10px; padding-top: 10px; border-top: 1px dashed #e2e8f0;" id="detSpkKeteranganRow">
                                <div class="col-md-12">
                                    <span style="font-size: 11px; color: #64748b;"><b>Catatan / Uraian Tugas:</b> </span>
                                    <span style="font-size: 12px; color: #1e293b;" id="detSpkKeterangan"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Nav Tabs untuk Komposisi Material, Biaya, dan Sub-Task -->
                    <div class="nav-tabs-custom" style="box-shadow: none; border: 1px solid #e2e8f0; border-radius: 4px;">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab_spk_material" data-toggle="tab"><i class="fa fa-cubes text-blue"></i> Komposisi Material & Supplies (<span id="detMatCount" class="badge bg-blue" style="font-size:10px;">0</span>)</a></li>
                            <li><a href="#tab_spk_biaya" data-toggle="tab"><i class="fa fa-money text-green"></i> Komponen Biaya / Jasa (<span id="detBiayaCount" class="badge bg-green" style="font-size:10px;">0</span>)</a></li>
                            <li><a href="#tab_spk_subtasks" data-toggle="tab"><i class="fa fa-check-square-o text-orange"></i> Tahapan Pengerjaan Sub-Task (<span id="detSubTaskCount" class="badge bg-orange" style="font-size:10px;">0</span>)</a></li>
                        </ul>
                        <div class="tab-content" style="padding: 15px; background: #fff;">
                            <!-- Tab 1: Material -->
                            <div class="tab-pane active" id="tab_spk_material">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" style="font-size: 12px; margin-bottom: 0;">
                                        <thead>
                                            <tr style="background-color: #f1f5f9;">
                                                <th style="width: 35px;" class="text-center">No</th>
                                                <th style="width: 80px;" class="text-center">Jenis</th>
                                                <th>Nama Material / Supplies</th>
                                                <th style="width: 80px;" class="text-right">Jumlah</th>
                                                <th style="width: 70px;" class="text-center">Satuan</th>
                                                <th style="width: 130px;" class="text-right">Harga Anggaran</th>
                                                <th style="width: 140px;" class="text-right">Total Anggaran</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detTableMaterialBody"></tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Tab 2: Biaya -->
                            <div class="tab-pane" id="tab_spk_biaya">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" style="font-size: 12px; margin-bottom: 0;">
                                        <thead>
                                            <tr style="background-color: #f1f5f9;">
                                                <th style="width: 35px;" class="text-center">No</th>
                                                <th>Nama Biaya / Komponen Jasa</th>
                                                <th style="width: 120px;">Kategori</th>
                                                <th style="width: 80px;" class="text-right">Jumlah</th>
                                                <th style="width: 70px;" class="text-center">Satuan</th>
                                                <th style="width: 140px;" class="text-right">Nilai / Biaya</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detTableBiayaBody"></tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Tab 3: Sub Tasks -->
                            <div class="tab-pane" id="tab_spk_subtasks">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" style="font-size: 12px; margin-bottom: 0;">
                                        <thead>
                                            <tr style="background-color: #f1f5f9;">
                                                <th style="width: 35px;" class="text-center">No</th>
                                                <th style="width: 130px;">Kode Sub-Task</th>
                                                <th>Nama Tahapan Pengerjaan</th>
                                                <th style="width: 90px;" class="text-center">Bobot (%)</th>
                                                <th style="width: 100px;" class="text-center">Realisasi (%)</th>
                                                <th style="width: 150px;" class="text-center">Jadwal</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="detTableSubTaskBody"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" style="background-color: #f8fafc;">
                <a href="#" id="btnModalPrintSpk" target="_blank" class="btn btn-sm btn-primary">
                    <i class="fa fa-print"></i> Cetak / Print Lembar SPK
                </a>
                <a href="#" id="btnModalOpenProject" target="_blank" class="btn btn-sm btn-default">
                    <i class="fa fa-external-link"></i> Buka di Master Project
                </a>
                <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inisialisasi DataTable jika library tersedia
    if ($.fn.DataTable) {
        $('#tabel_checker_wo').DataTable({
            "pageLength": 25,
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
            "language": {
                "search": "Cari Cepat (WO / Proyek):",
                "lengthMenu": "Tampilkan _MENU_ data per halaman",
                "zeroRecords": "Tidak ada data yang sesuai",
                "info": "Menampilkan _START_ s/d _END_ dari _TOTAL_ Work Order",
                "infoEmpty": "Menampilkan 0 data",
                "infoFiltered": "(disaring dari _MAX_ total data)",
                "paginate": {
                    "first": "Awal",
                    "last": "Akhir",
                    "next": "Berikutnya",
                    "previous": "Sebelumnya"
                }
            },
            "order": [[0, "asc"]],
            "columnDefs": [
                { "orderable": false, "targets": [7] }
            ]
        });
    }

    // Inisialisasi Select2 jika tersedia
    if ($.fn.select2) {
        $('.select2').select2();
    }
});

/**
 * Tampilkan Modal Quick View SPK
 */
function viewSpkModal(paketId, paketNama) {
    $('#modalWoNamaTitle').text(paketNama + ' (#' + paketId + ')');
    $('#spkLoadingIndicator').show();
    $('#spkTableContainer').hide();
    $('#spkTableBody').empty();
    $('#modalSpkList').modal('show');

    $.ajax({
        url: '<?php echo $base; ?>tools/CheckerWorkorder/getSpkListAjax',
        type: 'GET',
        dataType: 'json',
        data: { paket_id: paketId },
        success: function(res) {
            $('#spkLoadingIndicator').hide();
            if (res && res.status === 1) {
                var html = '';
                if (res.data.length === 0) {
                    html = '<tr><td colspan="9" class="text-center text-muted" style="padding: 20px;">Belum ada SPK terbit di bawah Work Order ini.</td></tr>';
                } else {
                    $.each(res.data, function(idx, spk) {
                        var isSelesai = parseInt(spk.progress_id) === 3;
                        var badgeProgress = isSelesai 
                            ? '<span class="label label-success"><i class="fa fa-check"></i> Selesai (100%)</span>' 
                            : '<span class="label label-warning"><i class="fa fa-clock-o"></i> Berjalan (' + (spk.progress_percent || '0') + '%)</span>';
                        
                        var qcStatus = spk.qc_nomer 
                            ? '<span class="text-green text-bold"><i class="fa fa-check-circle"></i> ' + spk.qc_nomer + '</span>' 
                            : '<span class="text-muted"><i>Belum Terbit QC</i></span>';

                        var flagBadge = '';
                        if (parseInt(spk.non_material) === 1) {
                            flagBadge += '<span class="label label-info" style="font-size:9px;">Non-Mat (Jasa)</span> ';
                        }
                        if (parseInt(spk.non_biaya) === 1) {
                            flagBadge += '<span class="label label-warning" style="font-size:9px;">Non-Biaya (Unit)</span>';
                        }
                        if (parseInt(spk.non_material) === 0 && parseInt(spk.non_biaya) === 0) {
                            flagBadge = '<span class="label label-default" style="font-size:9px;">Standar</span>';
                        }

                        var nilaiKontrak = spk.nilai_kontrak ? 'Rp ' + Number(spk.nilai_kontrak).toLocaleString('id-ID') : '-';
                        var printUrl = '<?php echo $base; ?>master_project/MasterData/print_tasklist/main/' + spk.id + '/' + spk.produk_id;
                        var projectUrl = '<?php echo $base; ?>master_project/MasterData/index/' + spk.produk_id;

                        html += '<tr>' +
                            '<td class="text-center font-weight-bold">' + (idx + 1) + '</td>' +
                            '<td><b>' + (spk.no_spk || '-') + '</b><br><small class="text-muted">ID: #' + spk.id + '</small></td>' +
                            '<td><b>' + (spk.tugas_nama || '-') + '</b></td>' +
                            '<td>' + (spk.employee_nama || '-') + '</td>' +
                            '<td class="text-center">' + badgeProgress + '</td>' +
                            '<td>' + qcStatus + '</td>' +
                            '<td class="text-center">' + flagBadge + '</td>' +
                            '<td class="text-right font-weight-bold">' + nilaiKontrak + '</td>' +
                            '<td class="text-center" style="white-space: nowrap;">' +
                                '<button type="button" class="btn btn-xs btn-primary" onclick="showSpkDetail(' + spk.id + ')" title="Lihat Isi Lengkap SPK (Material, Biaya, Jadwal, Sub-Task)">' +
                                    '<i class="fa fa-eye"></i> Detail Isi' +
                                '</button> ' +
                                '<a href="' + printUrl + '" target="_blank" class="btn btn-xs btn-default" title="Cetak / Print Dokumen SPK">' +
                                    '<i class="fa fa-print"></i> Print' +
                                '</a> ' +
                                '<a href="' + projectUrl + '" target="_blank" class="btn btn-xs btn-default" title="Buka Halaman Proyek di Master Project">' +
                                    '<i class="fa fa-external-link"></i>' +
                                '</a>' +
                            '</td>' +
                        '</tr>';
                    });
                }
                $('#spkTableBody').html(html);
                $('#spkTableContainer').show();
            } else {
                $('#spkTableBody').html('<tr><td colspan="9" class="text-center text-red">Gagal memuat data SPK: ' + (res.message || 'Error server') + '</td></tr>');
                $('#spkTableContainer').show();
            }
        },
        error: function(xhr, status, err) {
            $('#spkLoadingIndicator').hide();
            $('#spkTableBody').html('<tr><td colspan="9" class="text-center text-red">Gangguan koneksi ke server saat mengambil data SPK.</td></tr>');
            $('#spkTableContainer').show();
        }
    });
}

/**
 * Tampilkan Rincian Lengkap Sebuah SPK (BOM Material, Biaya, Sub-Tasklist)
 */
function showSpkDetail(spkId) {
    $('#spkDetailLoading').show();
    $('#spkDetailContent').hide();
    $('#modalSpkDetail').modal('show');

    $.ajax({
        url: '<?php echo $base; ?>tools/CheckerWorkorder/getSpkDetailAjax',
        type: 'GET',
        dataType: 'json',
        data: { spk_id: spkId },
        success: function(res) {
            $('#spkDetailLoading').hide();
            if (res && res.status === 1) {
                var s = res.spk;
                $('#detSpkNoTitle').text(s.no_spk || ('SPK #' + s.id));
                $('#detSpkNo').text(s.no_spk || '-');
                $('#detSpkNama').text(s.nama || '-');
                $('#detSpkProyek').text(s.project_nama ? (s.project_nama + ' (#' + s.produk_id + ')') : ('ID Proyek: #' + s.produk_id));
                $('#detSpkPelaksana').text(s.employee_nama || '-');
                
                var startTgl = s.dtime_start ? s.dtime_start : '-';
                var endTgl = s.dtime_end ? s.dtime_end : '-';
                $('#detSpkJadwal').html('<i class="fa fa-calendar"></i> ' + startTgl + ' s/d ' + endTgl);

                var nilaiKontrak = s.nilai_kontrak ? 'Rp ' + Number(s.nilai_kontrak).toLocaleString('id-ID') : '-';
                $('#detSpkNilai').text(nilaiKontrak);

                // QC Badge
                if (s.qc_nomer) {
                    var qcHtml = '<span class="label label-success"><i class="fa fa-check-circle"></i> ' + s.qc_nomer + '</span>';
                    if (s.qc_auth_nama) {
                        qcHtml += ' <small class="text-muted">(' + s.qc_auth_nama + ' - ' + (s.qc_dtime ? s.qc_dtime : '') + ')</small>';
                    }
                    $('#detSpkQcBadge').html(qcHtml);
                } else if (parseInt(s.progress_id) === 3) {
                    $('#detSpkQcBadge').html('<span class="label label-success"><i class="fa fa-check-circle"></i> QC Selesai</span>');
                } else {
                    $('#detSpkQcBadge').html('<span class="label label-warning"><i class="fa fa-clock-o"></i> Belum QC</span>');
                }

                // Post Biaya Info
                if (s.post_biaya_no) {
                    $('#detSpkPostBiaya').html('<i class="fa fa-check text-green"></i> Posting Biaya: <b>' + s.post_biaya_no + '</b>');
                } else {
                    $('#detSpkPostBiaya').html('<span class="text-muted"><i class="fa fa-info-circle"></i> Biaya Belum Diterbitkan</span>');
                }

                // Mode Flag
                var modeStr = 'Standar (Material + Biaya)';
                if (parseInt(s.non_material) === 1 && parseInt(s.non_biaya) === 0) {
                    modeStr = '<span class="badge-flag badge-biaya-only" style="padding:2px 6px;">BIAYA ONLY (NON-MAT)</span>';
                } else if (parseInt(s.non_material) === 0 && parseInt(s.non_biaya) === 1) {
                    modeStr = '<span class="badge-flag badge-material-only" style="padding:2px 6px;">MATERIAL ONLY (NON-BIAYA)</span>';
                }
                $('#detSpkModeFlag').html('Mode: ' + modeStr);

                // Keterangan
                if (s.keterangan && String(s.keterangan).trim() !== '') {
                    $('#detSpkKeterangan').text(s.keterangan);
                    $('#detSpkKeteranganRow').show();
                } else {
                    $('#detSpkKeteranganRow').hide();
                }

                // Tombol Footer Cetak & Proyek
                $('#btnModalPrintSpk').attr('href', '<?php echo $base; ?>master_project/MasterData/print_tasklist/main/' + s.id + '/' + s.produk_id);
                $('#btnModalOpenProject').attr('href', '<?php echo $base; ?>master_project/MasterData/index/' + s.produk_id);

                // Isi Tabel Material
                var matHtml = '';
                var matItems = res.materialItems || [];
                $('#detMatCount').text(matItems.length);
                if (matItems.length === 0) {
                    matHtml = '<tr><td colspan="8" class="text-center text-muted" style="padding: 20px;">Tidak ada komponen material / supplies pada SPK ini.</td></tr>';
                } else {
                    $.each(matItems, function(mIdx, mRow) {
                        var hrgStr = mRow.harga ? 'Rp ' + Number(mRow.harga).toLocaleString('id-ID') : '-';
                        var totStr = mRow.total_anggaran ? 'Rp ' + Number(mRow.total_anggaran).toLocaleString('id-ID') : '-';
                        matHtml += '<tr>' +
                            '<td class="text-center">' + (mIdx + 1) + '</td>' +
                            '<td class="text-center"><span class="label label-info" style="font-size:9px;">' + (mRow.jenis || 'material') + '</span></td>' +
                            '<td><b>' + (mRow.nama_item || '-') + '</b></td>' +
                            '<td class="text-right">' + (mRow.jml || '0') + '</td>' +
                            '<td class="text-center">' + (mRow.satuan || 'unit') + '</td>' +
                            '<td class="text-right">' + hrgStr + '</td>' +
                            '<td class="text-right font-weight-bold">' + totStr + '</td>' +
                            '<td>' + (mRow.keterangan || '-') + '</td>' +
                        '</tr>';
                    });
                }
                $('#detTableMaterialBody').html(matHtml);

                // Isi Tabel Biaya
                var byHtml = '';
                var byItems = res.biayaItems || [];
                $('#detBiayaCount').text(byItems.length);
                if (byItems.length === 0) {
                    byHtml = '<tr><td colspan="7" class="text-center text-muted" style="padding: 20px;">Tidak ada rincian komponen biaya / jasa teralokasi pada SPK ini.</td></tr>';
                } else {
                    $.each(byItems, function(bIdx, bRow) {
                        var valStr = bRow.nilai ? 'Rp ' + Number(bRow.nilai).toLocaleString('id-ID') : (bRow.harga ? 'Rp ' + Number(bRow.harga).toLocaleString('id-ID') : '-');
                        byHtml += '<tr>' +
                            '<td class="text-center">' + (bIdx + 1) + '</td>' +
                            '<td><b>' + (bRow.nama_item || '-') + '</b></td>' +
                            '<td><span class="label label-default">' + (bRow.kategori || 'quality/jasa') + '</span></td>' +
                            '<td class="text-right">' + (bRow.jml || '1') + '</td>' +
                            '<td class="text-center">' + (bRow.satuan || 'unit') + '</td>' +
                            '<td class="text-right font-weight-bold text-green">' + valStr + '</td>' +
                            '<td>' + (bRow.keterangan || '-') + '</td>' +
                        '</tr>';
                    });
                }
                $('#detTableBiayaBody').html(byHtml);

                // Isi Tabel Sub-Tasks
                var stHtml = '';
                var subTasks = res.subTasks || [];
                $('#detSubTaskCount').text(subTasks.length);
                if (subTasks.length === 0) {
                    stHtml = '<tr><td colspan="7" class="text-center text-muted" style="padding: 20px;">Tidak ada tahapan sub-tasklist terdaftar.</td></tr>';
                } else {
                    $.each(subTasks, function(sIdx, stRow) {
                        var jadwStr = (stRow.dtime_start || '-') + ' s/d ' + (stRow.dtime_end || '-');
                        stHtml += '<tr>' +
                            '<td class="text-center">' + (sIdx + 1) + '</td>' +
                            '<td><code>' + (stRow.no_sub || '-') + '</code></td>' +
                            '<td><b>' + (stRow.nama || '-') + '</b></td>' +
                            '<td class="text-center">' + (stRow.bobot || '0') + '%</td>' +
                            '<td class="text-center font-weight-bold text-green">' + (stRow.realisasi || '0') + '%</td>' +
                            '<td class="text-center">' + jadwStr + '</td>' +
                            '<td>' + (stRow.keterangan || '-') + '</td>' +
                        '</tr>';
                    });
                }
                $('#detTableSubTaskBody').html(stHtml);

                $('#spkDetailContent').show();
            } else {
                alert('Gagal memuat rincian SPK: ' + (res.message || 'Error server'));
                $('#modalSpkDetail').modal('hide');
            }
        },
        error: function() {
            $('#spkDetailLoading').hide();
            alert('Gangguan koneksi ke server saat memuat rincian SPK.');
            $('#modalSpkDetail').modal('hide');
        }
    });
}

/**
 * Eksekusi Sinkronisasi Flag (Self-Healing)
 */
function executeSyncFix(paketId, actionType, paketNama) {
    var confirmTitle = 'KONFIRMASI SINKRONISASI';
    var confirmText  = 'Apakah Anda yakin ingin melakukan sinkronisasi flag pada Work Order #' + paketId + ' (' + paketNama + ')?';

    if (actionType === 'set_biaya_only') {
        confirmText = 'WO ini akan diatur ke mode BIAYA ONLY (non_material=1) dan seluruh SPK anak akan disinkronkan. Lanjutkan?';
    } else if (actionType === 'set_material_only') {
        confirmText = 'WO ini akan diatur ke mode MATERIAL ONLY (non_biaya=1) dan seluruh SPK anak akan disinkronkan. Lanjutkan?';
    } else if (actionType === 'sync_spk_flags') {
        confirmText = 'Seluruh SPK anak akan disinkronkan mengikuti flag Work Order induk #' + paketId + '. Lanjutkan?';
    }

    var doSync = function() {
        $.ajax({
            url: '<?php echo $base; ?>tools/CheckerWorkorder/syncFix',
            type: 'POST',
            dataType: 'json',
            data: {
                paket_id: paketId,
                action: actionType
            },
            success: function(res) {
                if (res && res.status === 1) {
                    if (typeof swal !== 'undefined') {
                        swal('Berhasil!', res.message, 'success');
                    } else {
                        alert('Berhasil: ' + res.message);
                    }
                    setTimeout(function() {
                        window.location.reload();
                    }, 1200);
                } else {
                    var errMsg = res ? res.message : 'Terjadi kesalahan sistem.';
                    if (typeof swal !== 'undefined') {
                        swal('Gagal!', errMsg, 'error');
                    } else {
                        alert('Gagal: ' + errMsg);
                    }
                }
            },
            error: function(xhr) {
                if (typeof swal !== 'undefined') {
                    swal('Error!', 'Gangguan komunikasi dengan server (' + (xhr.status || 0) + ').', 'error');
                } else {
                    alert('Gangguan komunikasi dengan server.');
                }
            }
        });
    };

    if (typeof swal !== 'undefined') {
        swal({
            title: confirmTitle,
            text: confirmText,
            type: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Jalankan Sinkronisasi!',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (result && result.value !== false) {
                doSync();
            }
        });
    } else {
        if (confirm(confirmText)) {
            doSync();
        }
    }
}
</script>
<!-- END OF COMPLETE REPEATED LOGIC -->

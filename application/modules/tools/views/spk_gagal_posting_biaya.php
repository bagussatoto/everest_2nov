<?php
// START OF COMPLETE REPEATED LOGIC
$base = base_url();
$filterParams = http_build_query($filters);
$urlCsv   = $base . "tools/SpkGagalPostingBiaya?format=csv&" . $filterParams;
$urlJson  = $base . "tools/SpkGagalPostingBiaya?format=json&" . $filterParams;
$urlReset = $base . "tools/SpkGagalPostingBiaya";
?>
<!-- Moment.js untuk format waktu relatif -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/id.min.js"></script>

<style>
    .spk-monitoring-container {
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        color: #333;
        font-size: 13px;
        padding: 5px 0;
    }

    /* Header Gradient Banner */
    .page-header-box {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: #fff;
        padding: 18px 24px;
        border-radius: 4px;
        margin-bottom: 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .page-header-box h2 {
        margin: 0 0 6px 0;
        font-size: 21px;
        font-weight: 700;
        letter-spacing: 0.5px;
    }
    .page-header-box p {
        margin: 0;
        font-size: 13px;
        opacity: 0.9;
    }

    /* KPI Summary Cards */
    .kpi-row { margin-bottom: 18px; }
    .kpi-card {
        background: #fff;
        border-radius: 4px;
        padding: 14px 16px;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        border-left: 4px solid #ccc;
        margin-bottom: 10px;
    }
    .kpi-card.danger  { border-left-color: #dd4b39; }
    .kpi-card.warning { border-left-color: #f39c12; }
    .kpi-card.primary { border-left-color: #3c8dbc; }
    .kpi-card.success { border-left-color: #00a65a; }
    .kpi-card .number { font-size: 22px; font-weight: 700; margin: 2px 0; }
    .kpi-card .title  { font-size: 11px; text-transform: uppercase; color: #777; font-weight: 600; letter-spacing: 0.5px; }

    /* Panel Filter */
    .filter-box {
        background: #fff;
        border: 1px solid #d2d6de;
        border-radius: 4px;
        padding: 14px 18px;
        margin-bottom: 18px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    /* Tabel Presisi Sesuai Master Project */
    .main-card {
        background: #fff;
        border-radius: 4px;
        border: 1px solid #d2d6de;
        box-shadow: 0 1px 4px rgba(0,0,0,0.08);
        margin-bottom: 30px;
    }
    .main-card-header {
        padding: 12px 18px;
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
    }

    /* Style Tabel Identik dengan #daftar_tugas_ di Master Project */
    #daftar_tugas_ {
        table-layout: fixed;
        width: 100% !important;
        margin-bottom: 0;
    }
    #daftar_tugas_ thead th {
        background-color: #f4f4f4;
        color: #444;
        font-size: 12px;
        font-weight: 700;
        border-bottom: 2px solid #d2d6de !important;
        padding: 9px 8px;
        vertical-align: middle;
    }
    #daftar_tugas_ tbody td {
        font-size: 11.5px;
        padding: 8px 6px;
        vertical-align: top;
        word-wrap: break-word;
        overflow: hidden;
        border-color: #f4f4f4;
    }
    #daftar_tugas_ tbody tr:hover {
        background-color: #f5f8fd;
    }

    .td-ellipsis {
        max-width: 100%;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: block;
        cursor: default;
    }
    r {
        color: #dd4b39;
        font-weight: 600;
    }
    .text-navi {
        color: #001f3f;
    }

    /* Lebar Kolom Presisi */
    .col-w-no       { width: 35px !important; text-align: center; }
    .col-w-waktu    { width: 110px !important; }
    .col-w-spk      { width: 155px !important; }
    .col-w-jadwal   { width: 110px !important; text-align: center; }
    .col-w-konsumen { width: 140px !important; }
    .col-w-pekerjaan{ width: 185px !important; }
    .col-w-pelaksana{ width: 175px !important; }
    .col-w-ket      { width: 150px !important; }
    .col-w-status   { width: 135px !important; }
    .col-w-tindakan { width: 125px !important; }

    /* Tombol Aksi Vertikal */
    .btn-action-group .btn {
        display: block;
        width: 100%;
        text-align: left;
        margin-bottom: 2px;
        font-size: 10.5px;
        padding: 3px 6px;
        border-radius: 2px;
    }
    .btn-action-group .btn i {
        width: 14px;
        text-align: center;
        margin-right: 4px;
    }
</style>

<div class="spk-monitoring-container">

    <!-- Header Banner -->
    <div class="page-header-box">
        <div class="row">
            <div class="col-md-9">
                <h2><i class="fa fa-exclamation-circle text-yellow"></i> MONITORING SPK SUDAH QC BELUM TERBIT POSTING BIAYA (3674)</h2>
                <p>Daftar seluruh nomor SPK Subkon/Vendor yang telah selesai QC dengan komponen biaya namun belum memiliki record posting biaya <b>3674 / 3674r</b> di sistem.</p>
            </div>
            <div class="col-md-3 text-right" style="padding-top:8px;">
                <a href="<?= $urlCsv ?>" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Unduh CSV / Excel</a>
                <a href="<?= $base ?>master_project/MasterData" class="btn btn-default btn-sm" target="_blank"><i class="fa fa-folder-open"></i> Master Project</a>
            </div>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="row kpi-row">
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card danger">
                <div class="title">Total SPK Gagal / Belum Terbit</div>
                <div class="number text-danger"><?= number_format($summary['total_spk'], 0, ',', '.') ?> <small style="font-size:13px;font-weight:normal;">SPK</small></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card warning">
                <div class="title">Total Nilai Biaya Menggantung</div>
                <div class="number text-warning" style="color:#d58512;">Rp <?= number_format($summary['total_nilai'], 0, ',', '.') ?></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card primary">
                <div class="title">Project Terdampak</div>
                <div class="number text-primary"><?= number_format($summary['total_project'], 0, ',', '.') ?> <small style="font-size:13px;font-weight:normal;">Project</small></div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-card success">
                <div class="title">Vendor / Subkon Terdampak</div>
                <div class="number text-success"><?= number_format($summary['total_vendor'], 0, ',', '.') ?> <small style="font-size:13px;font-weight:normal;">Vendor</small></div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-box">
        <form method="GET" action="<?= $base ?>tools/SpkGagalPostingBiaya" class="form-inline">
            <div class="form-group" style="margin-right:12px;">
                <label for="project_id" style="font-size:12px; margin-right:4px;">Filter Project:</label>
                <select name="project_id" id="project_id" class="form-control input-sm" style="min-width:240px;">
                    <option value="all">-- Semua Project (<?= count($projectList) ?> Project) --</option>
                    <?php foreach ($projectList as $pId => $pName): ?>
                        <option value="<?= $pId ?>" <?= ($filters['project_id'] == strval($pId)) ? 'selected' : '' ?>>
                            [ID: <?= $pId ?>] <?= htmlspecialchars($pName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group" style="margin-right:12px;">
                <label for="vendor" style="font-size:12px; margin-right:4px;">Filter Vendor / Subkon:</label>
                <select name="vendor" id="vendor" class="form-control input-sm" style="min-width:220px;">
                    <option value="all">-- Semua Vendor / Subkon (<?= count($vendorList) ?>) --</option>
                    <?php foreach ($vendorList as $vName): ?>
                        <option value="<?= htmlspecialchars($vName) ?>" <?= ($filters['vendor'] == $vName) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($vName) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-filter"></i> Terapkan Filter</button>
            <a href="<?= $urlReset ?>" class="btn btn-default btn-sm"><i class="fa fa-undo"></i> Reset</a>
        </form>
    </div>

    <!-- Main Table Container -->
    <div class="main-card">
        <div class="main-card-header">
            <h3><i class="fa fa-list-alt text-blue"></i> DAFTAR PELAKSANA DAN PEKERJAAN (BIAYA BELUM DITERBITKAN)</h3>
            <span class="text-muted" style="font-size:12px;">Menampilkan <b><?= count($list) ?></b> SPK yang membutuhkan perhatian</span>
        </div>

        <div class="table-responsive">
            <table id="daftar_tugas_" class="table dataTable compact display table-bordered table-condensed">
                <thead>
                    <tr>
                        <th class="col-w-no">No</th>
                        <th class="col-w-waktu">Waktu Dibuat</th>
                        <th class="col-w-spk">No SPK</th>
                        <th class="col-w-jadwal">Waktu / Jadwal</th>
                        <th class="col-w-konsumen">Konsumen</th>
                        <th class="col-w-pekerjaan">Pekerjaan & WO</th>
                        <th class="col-w-pelaksana">Pelaksana</th>
                        <th class="col-w-ket">Keterangan</th>
                        <th class="col-w-status">Status & Progress</th>
                        <th class="col-w-tindakan">Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($list)): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted" style="padding:30px;">
                                <i class="fa fa-check-circle text-green" style="font-size:32px;"></i><br>
                                <b>Luar biasa! Tidak ditemukan SPK yang mengalami kegagalan posting biaya 3674 pada filter ini.</b>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php 
                        $no = 1;
                        foreach ($list as $row): 
                            $spkNo = $row['no_spk'];
                            $taskType = $row['task_type'];
                            $isMain = ($taskType === 'main');
                            $pUrl = $base . "master_project/MasterData/index/" . $row['produk_id'];
                            $printUrl = $base . "master_project/MasterData/print_tasklist/" . $row['id'] . "/" . $row['produk_id'];
                            
                            // Format Waktu Dibuat
                            $dtimeRaw = !empty($row['dtime']) ? $row['dtime'] : '';
                            $dtimeFormatted = '-';
                            if (!empty($dtimeRaw)) {
                                $dtObj = new DateTime($dtimeRaw);
                                $dtimeFormatted = $dtObj->format('d M Y H:i');
                            }

                            // Format Tanggal Jadwal
                            $startRaw = !empty($row['dtime_start']) ? $row['dtime_start'] : '';
                            $endRaw   = !empty($row['dtime_end']) ? $row['dtime_end'] : '';
                            $startFormatted = !empty($startRaw) ? (new DateTime($startRaw))->format('d M Y') : '-';
                            $endFormatted   = !empty($endRaw) ? (new DateTime($endRaw))->format('d M Y') : '-';
                        ?>
                        <tr>
                            <!-- 0: No -->
                            <td class="col-w-no text-center"><b><?= $no++ ?></b></td>

                            <!-- 1: Waktu Dibuat -->
                            <td class="col-w-waktu" data-order="<?= $dtimeRaw ?>">
                                <?= $dtimeFormatted ?><br>
                                <small class="text-muted"><i class="moment-relative" data-time="<?= $dtimeRaw ?>">-</i></small>
                            </td>

                            <!-- 2: No SPK -->
                            <td class="col-w-spk">
                                <?php if ($isMain): ?>
                                    <span class="label label-primary" style="font-size:9px;margin-bottom:2px;display:inline-block;"><i class="fa fa-file-text-o"></i> SPK UTAMA</span><br>
                                <?php else: ?>
                                    <span class="label bg-purple" style="font-size:9px;margin-bottom:2px;display:inline-block;background-color:#605ca8;color:#fff;"><i class="fa fa-plus-circle"></i> SPK TAMBAHAN</span><br>
                                <?php endif; ?>
                                <div class="td-ellipsis" title="<?= htmlspecialchars($spkNo) ?>">
                                    <b><?= htmlspecialchars($spkNo) ?></b>
                                </div>
                            </td>

                            <!-- 3: Waktu / Jadwal -->
                            <td class="col-w-jadwal text-center">
                                <?= $startFormatted ?><br>
                                <r class="moment-relative-fromnow" data-time="<?= $startRaw ?>">-</r><br>
                                <b>s/d</b><br>
                                <?= $endFormatted ?><br>
                                <r class="moment-relative-fromnow" data-time="<?= $endRaw ?>">-</r>
                            </td>

                            <!-- 4: Konsumen -->
                            <td class="col-w-konsumen">
                                <div class="td-ellipsis" title="<?= htmlspecialchars($row['owner_nama']) ?>">
                                    <?= !empty($row['owner_nama']) ? htmlspecialchars($row['owner_nama']) : '-' ?>
                                </div>
                            </td>

                            <!-- 5: Pekerjaan & WO -->
                            <td class="col-w-pekerjaan">
                                <div class="td-ellipsis" style="margin-bottom:3px;">
                                    <a href="<?= $pUrl ?>" target="_blank" class="text-bold text-primary" title="Buka Project: <?= htmlspecialchars($row['project_nama']) ?>" style="font-size:11px; text-decoration:none;">
                                        <i class="fa fa-folder-open text-yellow"></i> <?= htmlspecialchars($row['project_nama']) ?> <i class="fa fa-external-link" style="font-size:9px;"></i>
                                    </a>
                                </div>
                                <div class="td-ellipsis" title="<?= htmlspecialchars($row['tugas_nama']) ?>">
                                    <b><?= htmlspecialchars($row['tugas_nama']) ?></b>
                                </div>
                                <small>
                                    <i class="fa fa-dropbox text-navi"></i> 
                                    <span class="text-bold text-uppercase text-navi td-ellipsis" title="<?= htmlspecialchars($row['produk_paket_nama']) ?>" style="font-size:11px;">
                                        <?= !empty($row['produk_paket_nama']) ? htmlspecialchars($row['produk_paket_nama']) : 'NON-PAKET' ?>
                                    </span>
                                </small>
                            </td>

                            <!-- 6: Pelaksana -->
                            <td class="col-w-pelaksana">
                                <div class="td-ellipsis" title="<?= htmlspecialchars($row['employee_nama']) ?>">
                                    <i class="fa fa-user-circle text-blue"></i> 
                                    <span class="text-bold text-uppercase text-blue">
                                        <?= !empty($row['employee_nama']) ? htmlspecialchars($row['employee_nama']) : "<span class='text-red text-bold'>belum ada pelaksana</span>" ?>
                                    </span>
                                </div>
                                <div style="margin-top:2px;">
                                    <span class="badge" style="background-color:#ff851b;color:#fff;font-size:9px;padding:3px 6px;font-weight:bold;">
                                        <i class="fa fa-pencil" style="font-size:8px;margin-right:2px;"></i> PIHAK KE-3 / VENDOR / SUBKON
                                    </span>
                                </div>
                                <div style="font-size:10.5px;margin-top:3px;line-height:1.3;color:#dd4b39;font-weight:bold;" title="Status Biaya: Harap Lapor Admin Untuk Melakukan Cek Kode EPB-3674">
                                    <i class="fa fa-exclamation-triangle"></i> Harap Lapor Admin Untuk Melakukan Cek Kode EPB-3674
                                </div>
                                <?php if ($row['nilai_biaya_riil'] > 0): ?>
                                    <div style="margin-top:4px;">
                                        <span class="label label-danger" style="font-size:10px; font-weight:normal;" title="Nilai Biaya Subkon yang menggantung">
                                            Rp <?= number_format($row['nilai_biaya_riil'], 0, ',', '.') ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- 7: Keterangan -->
                            <td class="col-w-ket">
                                <div class="td-ellipsis" title="<?= htmlspecialchars($row['keterangan']) ?>">
                                    <?= !empty($row['keterangan']) ? htmlspecialchars($row['keterangan']) : '-' ?>
                                </div>
                            </td>

                            <!-- 8: Status & Progress -->
                            <td class="col-w-status">
                                <div class="btn btn-xs btn-success" style="margin-bottom:5px; padding:2px 8px;" disabled title="pelaksanaan telah selesai">
                                    <i class="fa fa-check-circle"></i> Selesai
                                </div>
                                <div class="progress active" style="margin-bottom:2px; height:18px; border-radius:2px;">
                                    <div class="progress-bar progress-bar-success progress-bar-striped" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%; line-height:18px;">
                                        <span class="text-bold text-auto">100% Selesai</span>
                                    </div>
                                </div>
                                <?php if (!empty($row['qc_dtime'])): ?>
                                    <div class="text-muted" style="font-size:10px; margin-top:2px;" title="Waktu QC Selesai: <?= $row['qc_dtime'] ?>">
                                        <i class="fa fa-clock-o"></i> QC: <?= (new DateTime($row['qc_dtime']))->format('d/m/Y') ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- 9: Tindakan -->
                            <td class="col-w-tindakan">
                                <div class="btn-group-vertical btn-action-group" style="width:100%;">
                                    <button class="btn btn-xs bg-gray text-left" disabled><i class="fa fa-eraser"></i> PEMBATALAN</button>
                                    <button class="btn btn-xs bg-gray text-left" disabled><i class="fa fa-plus"></i> MONITORING</button>
                                    <a href="<?= $pUrl ?>" target="_blank" class="btn btn-xs bg-gray text-bold text-left" title="Buka Detail Project untuk Riwayat QC">
                                        <i class="fa fa-eye text-yellow"></i> Lihat Riwayat QC
                                    </a>
                                    <a href="<?= $printUrl ?>" target="_blank" class="btn btn-xs bg-gray text-left" title="Cetak SPK">
                                        <i class="fa fa-print"></i> PRINT SPK
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
$(document).ready(function() {
    moment.locale('id');

    // Format waktu relatif Moment.js
    $('.moment-relative').each(function() {
        var t = $(this).attr('data-time');
        if (t && t !== '') {
            $(this).text(moment(t, 'YYYY-MM-DD HH:mm:ss').fromNow());
        }
    });

    $('.moment-relative-fromnow').each(function() {
        var t = $(this).attr('data-time');
        if (t && t !== '') {
            $(this).text(moment(t, 'YYYY-MM-DD').fromNow());
        }
    });

    // Inisialisasi DataTable
    var table = $('#daftar_tugas_').DataTable({
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        ordering: true,
        order: [[1, 'desc']], // Urutkan berdasarkan Waktu Dibuat terkini
        columnDefs: [
            { orderable: false, targets: [0, 9] } // Kolom No dan Tindakan tidak bisa di-sort
        ],
        language: {
            search: "Cari SPK / Project / Vendor:",
            searchPlaceholder: "Ketik No SPK atau nama...",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            zeroRecords: "Tidak ada data SPK yang cocok",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data SPK",
            infoEmpty: "Menampilkan 0 data",
            infoFiltered: "(disaring dari _MAX_ total data)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Berikutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // Update nomor urut otomatis saat pagination / sorting
    table.on('order.dt search.dt', function () {
        table.column(0, {search:'applied', order:'applied'}).nodes().each(function (cell, i) {
            cell.innerHTML = '<b>' + (i+1) + '</b>';
        });
    }).draw();
});
</script>


<?php
// END OF COMPLETE REPEATED LOGIC

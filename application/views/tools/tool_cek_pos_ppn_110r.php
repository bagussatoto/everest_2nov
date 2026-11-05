<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- START OF COMPLETE REPEATED LOGIC -->
<style>
    .audit-card {
        border-radius: 6px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        background: #fff;
        margin-bottom: 20px;
        border-top: 3px solid #3c8dbc;
    }
    .audit-card-warning {
        border-top-color: #f39c12 !important;
    }
    .audit-card-danger {
        border-top-color: #dd4b39 !important;
    }
    .audit-card-success {
        border-top-color: #00a65a !important;
    }
    .audit-header {
        padding: 12px 18px;
        border-bottom: 1px solid #f4f4f4;
        font-size: 16px;
        font-weight: 600;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .audit-body {
        padding: 18px;
    }
    .kpi-box {
        border-radius: 6px;
        padding: 15px;
        color: #fff;
        margin-bottom: 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        position: relative;
        overflow: hidden;
    }
    .kpi-box .kpi-icon {
        position: absolute;
        right: 15px;
        top: 15px;
        font-size: 38px;
        opacity: 0.25;
    }
    .kpi-box .kpi-num {
        font-size: 24px;
        font-weight: 700;
        line-height: 1.1;
    }
    .kpi-box .kpi-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 5px;
        opacity: 0.9;
    }
    .kpi-box .kpi-sub {
        font-size: 12px;
        margin-top: 4px;
        opacity: 0.9;
        font-weight: 600;
    }
    .kpi-blue   { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); }
    .kpi-orange { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); }
    .kpi-green  { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .kpi-red    { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }

    /* Actionable Guidance Notification (Rule 3.12) */
    .actionable-guidance-box {
        padding: 15px 20px;
        margin-bottom: 20px;
        border-radius: 6px;
        border-left: 5px solid;
        box-shadow: 0 2px 4px rgba(0,0,0,0.06);
    }
    .guidance-warning {
        background-color: #fff8e6;
        border-left-color: #f39c12;
        color: #8a6d3b;
    }
    .guidance-danger {
        background-color: #fdf2f2;
        border-left-color: #dd4b39;
        color: #a94442;
    }
    .guidance-success {
        background-color: #f0fdf4;
        border-left-color: #00a65a;
        color: #3c763d;
    }
    .guidance-title {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .guidance-body {
        font-size: 13px;
        line-height: 1.5;
    }
    .guidance-actions {
        margin-top: 12px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .table-audit th {
        background-color: #f4f6f9;
        font-weight: 600;
        text-align: center;
        vertical-align: middle !important;
        font-size: 13px;
    }
    .table-audit td {
        vertical-align: middle !important;
        font-size: 13px;
    }
    .badge-status {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
    }
    .badge-danger-custom {
        background-color: #dd4b39;
        color: #fff;
    }
    .badge-success-custom {
        background-color: #00a65a;
        color: #fff;
    }
    .badge-warning-custom {
        background-color: #f39c12;
        color: #fff;
    }
    .badge-default-custom {
        background-color: #e0e0e0;
        color: #555;
    }
    .quick-filter-btn {
        margin-right: 5px;
        margin-bottom: 5px;
        border-radius: 20px;
        font-size: 12px;
        padding: 5px 14px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    .quick-filter-btn.active {
        background-color: #3c8dbc;
        color: #fff;
        border-color: #3c8dbc;
    }
</style>

<div class="content-wrapper-tool" style="padding: 15px;">
    <!-- HEADER BAR -->
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <div>
            <h3 style="margin: 0; font-weight: 700; color: #333;">
                <i class="fa fa-balance-scale text-primary"></i> Checker POS PPN (110r)
            </h3>
            <p style="margin: 5px 0 0 0; color: #777; font-size: 13px;">
                Pemeriksaan Transaksi Packing List / Kasir POS yang Menjurnal PPN Keluaran namun Belum Menerbitkan Kode 110r
            </p>
        </div>
        <div style="margin-top: 10px;">
            <a href="<?php echo base_url(); ?>ToolRepairSusulanPreFakturPpnKeluaran" class="btn btn-default btn-sm" target="_blank">
                <i class="fa fa-wrench"></i> Buka Tool Repair Susulan PPN
            </a>
        </div>
    </div>

    <!-- FILTER PANEL -->
    <div class="audit-card">
        <div class="audit-header">
            <span><i class="fa fa-filter text-primary"></i> Filter Audit Transaksi</span>
            <span class="text-muted" style="font-size: 12px; font-weight: normal;">Jenis POS: <code>5822spd</code>, <code>5823spd</code> | Rek PPN: <code>2030060</code></span>
        </div>
        <div class="audit-body">
            <form id="form_filter" onsubmit="return false;">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label><i class="fa fa-calendar"></i> Tanggal Mulai:</label>
                            <input type="date" id="input_date_start" class="form-control" value="<?php echo htmlspecialchars($default_date_start); ?>">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label><i class="fa fa-calendar"></i> Tanggal Selesai:</label>
                            <input type="date" id="input_date_stop" class="form-control" value="<?php echo htmlspecialchars($default_date_stop); ?>">
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label><i class="fa fa-building"></i> Cabang:</label>
                            <select id="select_cabang" class="form-control">
                                <option value="-1">-- Semua Cabang --</option>
                                <?php foreach ($cabangs as $cb): ?>
                                    <option value="<?php echo $cb['id']; ?>" <?php echo ($cb['id'] == $default_cabang_id) ? "selected" : ""; ?>>
                                        [<?php echo $cb['id']; ?>] <?php echo htmlspecialchars($cb['nama']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label><i class="fa fa-sliders"></i> Tampilan Filter:</label>
                            <select id="select_status_filter" class="form-control">
                                <option value="anomali_only" <?php echo ($default_status_filter === "anomali_only") ? "selected" : ""; ?>>Hanya Anomali (Belum Terbit 110r)</option>
                                <option value="ppn_only" <?php echo ($default_status_filter === "ppn_only") ? "selected" : ""; ?>>Semua POS Menjurnal PPN</option>
                                <option value="sudah_110r" <?php echo ($default_status_filter === "sudah_110r") ? "selected" : ""; ?>>Hanya yang Sudah Terbit 110r</option>
                                <option value="all" <?php echo ($default_status_filter === "all") ? "selected" : ""; ?>>Semua Transaksi POS</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div style="margin-top: 10px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                    <div>
                        <button type="button" id="btn_audit" class="btn btn-primary btn-sm" style="min-width: 140px;">
                            <i class="fa fa-search"></i> Jalankan Audit
                        </button>
                        <button type="button" id="btn_reset" class="btn btn-default btn-sm">
                            <i class="fa fa-undo"></i> Reset Filter
                        </button>
                    </div>
                    <div id="loading_indicator" style="display: none; color: #3c8dbc; font-weight: 600; font-size: 13px;">
                        <i class="fa fa-spinner fa-spin"></i> Memeriksa data transaksi dan jurnal PPN...
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ACTIONABLE GUIDANCE NOTIFICATION (Rule 3.12) -->
    <div id="guidance_container"></div>

    <!-- KPI SUMMARY CARDS -->
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <div class="kpi-box kpi-blue">
                <i class="fa fa-shopping-cart kpi-icon"></i>
                <div class="kpi-num" id="kpi_total_pos">0</div>
                <div class="kpi-label">Total Transaksi POS</div>
                <div class="kpi-sub">5822spd & 5823spd</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-box kpi-orange">
                <i class="fa fa-calculator kpi-icon"></i>
                <div class="kpi-num" id="kpi_total_ppn">0</div>
                <div class="kpi-label">Menjurnal PPN</div>
                <div class="kpi-sub" id="kpi_nominal_ppn_total">Rp 0</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-box kpi-green">
                <i class="fa fa-check-circle kpi-icon"></i>
                <div class="kpi-num" id="kpi_total_sudah_110r">0</div>
                <div class="kpi-label">Sudah Terbit 110r</div>
                <div class="kpi-sub">Status Klop</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="kpi-box kpi-red">
                <i class="fa fa-exclamation-triangle kpi-icon"></i>
                <div class="kpi-num" id="kpi_total_anomali">0</div>
                <div class="kpi-label">Belum Terbit 110r</div>
                <div class="kpi-sub" id="kpi_nominal_ppn_anomali">Rp 0</div>
            </div>
        </div>
    </div>

    <!-- DATA TABLE PANEL -->
    <div class="audit-card">
        <div class="audit-header">
            <div>
                <i class="fa fa-list text-primary"></i> Rincian Hasil Audit Transaksi
                <span id="badge_count_row" class="badge bg-blue" style="margin-left: 8px;">0 Data</span>
            </div>
            <div>
                <input type="text" id="table_search" class="form-control input-sm" placeholder="Cari Nomer Nota / ID / Cabang..." style="width: 250px; display: inline-block;">
                <button type="button" id="btn_export_csv" class="btn btn-default btn-sm" title="Unduh CSV">
                    <i class="fa fa-download"></i> Ekspor CSV
                </button>
            </div>
        </div>
        <div class="audit-body" style="padding-top: 10px;">
            <!-- QUICK FILTER PILLS -->
            <div style="margin-bottom: 15px;">
                <button type="button" class="btn btn-default quick-filter-btn" data-filter="anomali_only">
                    <i class="fa fa-exclamation-circle text-danger"></i> Anomali Belum 110r (<span id="pill_count_anomali">0</span>)
                </button>
                <button type="button" class="btn btn-default quick-filter-btn" data-filter="sudah_110r">
                    <i class="fa fa-check-circle text-success"></i> Sudah Terbit 110r (<span id="pill_count_sudah">0</span>)
                </button>
                <button type="button" class="btn btn-default quick-filter-btn" data-filter="ppn_only">
                    <i class="fa fa-calculator text-warning"></i> Semua Kena PPN (<span id="pill_count_ppn">0</span>)
                </button>
                <button type="button" class="btn btn-default quick-filter-btn" data-filter="all">
                    <i class="fa fa-bars"></i> Semua Transaksi POS (<span id="pill_count_all">0</span>)
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped table-audit" id="table_audit_data">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th style="width: 80px;">ID Tr</th>
                            <th style="width: 150px;">Nomer Nota</th>
                            <th style="width: 130px;">Tanggal / Jam</th>
                            <th>Cabang / Gudang</th>
                            <th style="width: 90px;">Jenis</th>
                            <th style="width: 140px;">Nominal PPN (2030060)</th>
                            <th style="width: 170px;">Status 110r</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="table_audit_tbody">
                        <tr>
                            <td colspan="9" class="text-center text-muted" style="padding: 30px;">
                                <i class="fa fa-info-circle fa-2x"></i><br>
                                Silakan klik tombol <b>Jalankan Audit</b> untuk memuat data.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    var globalBaseUrl = "<?php echo base_url(); ?>";
    var auditRawData = [];

    function formatRupiah(num) {
        num = parseFloat(num) || 0;
        return "Rp " + num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    }

    function renderGuidanceBox(anomaliCount, nominalPpnAnomali) {
        var container = $('#guidance_container');
        if (anomaliCount > 0) {
            var html = '<div class="actionable-guidance-box guidance-warning">' +
                '<div class="guidance-title">' +
                    '<i class="fa fa-exclamation-triangle"></i> ' +
                    'Terdeteksi ' + anomaliCount + ' Transaksi POS Ber-PPN Belum Menerbitkan Kode 110r' +
                '</div>' +
                '<div class="guidance-body">' +
                    'Terdapat total PPN Keluaran senilai <b>' + formatRupiah(nominalPpnAnomali) + '</b> yang sudah terjurnal pada akun <code>2030060</code>, ' +
                    'namun dokumen relasi <code>110r</code> belum diterbitkan ke Pusat/DC. ' +
                    'Hal ini berpotensi menyebabkan ketidaksesuaian laporan faktur pajak keluaran.' +
                '</div>' +
                '<div class="guidance-actions">' +
                    '<button type="button" class="btn btn-warning btn-xs" onclick="filterToAnomali()">' +
                        '<i class="fa fa-filter"></i> Tampilkan ' + anomaliCount + ' Nota Anomali' +
                    '</button>' +
                    '<a href="' + globalBaseUrl + 'ToolRepairSusulanPreFakturPpnKeluaran" class="btn btn-default btn-xs" target="_blank">' +
                        '<i class="fa fa-wrench"></i> Buka Antarmuka Perbaikan Susulan' +
                    '</a>' +
                '</div>' +
            '</div>';
            container.html(html);
        } else {
            var html = '<div class="actionable-guidance-box guidance-success">' +
                '<div class="guidance-title">' +
                    '<i class="fa fa-check-circle"></i> ' +
                    'Kondisi Data Selaras (100% Klop)' +
                '</div>' +
                '<div class="guidance-body">' +
                    'Seluruh transaksi POS yang menjurnal PPN Keluaran pada periode ini telah memiliki dokumen penerbitan <code>110r</code> dengan benar. Tidak ditemukan transaksi anomali.' +
                '</div>' +
            '</div>';
            container.html(html);
        }
    }

    function filterToAnomali() {
        $('#select_status_filter').val('anomali_only');
        updateQuickFilterButtons('anomali_only');
        renderTableRows();
    }

    function updateQuickFilterButtons(selectedFilter) {
        $('.quick-filter-btn').removeClass('active');
        $('.quick-filter-btn[data-filter="' + selectedFilter + '"]').addClass('active');
    }

    function fetchAuditData() {
        var dateStart = $('#input_date_start').val();
        var dateStop = $('#input_date_stop').val();
        var cabangId = $('#select_cabang').val();
        var statusFilter = $('#select_status_filter').val();

        $('#loading_indicator').show();
        $('#btn_audit').prop('disabled', true);

        $.ajax({
            url: globalBaseUrl + 'ToolCekPosPpn110r/ajaxGetData',
            type: 'GET',
            dataType: 'json',
            data: {
                date_start: dateStart,
                date_stop: dateStop,
                cabang_id: cabangId,
                status_filter: 'all' // Ambil seluruh data agar KPI dan pill count lengkap di sisi klien
            },
            success: function(res) {
                $('#loading_indicator').hide();
                $('#btn_audit').prop('disabled', false);

                if (res.status === 'success') {
                    auditRawData = res.data || [];
                    var kpi = res.kpi || {};

                    // Update KPI Cards
                    $('#kpi_total_pos').text((kpi.total_pos || 0).toLocaleString('id-ID'));
                    $('#kpi_total_ppn').text((kpi.total_ppn || 0).toLocaleString('id-ID'));
                    $('#kpi_nominal_ppn_total').text(formatRupiah(kpi.nominal_ppn_total));
                    $('#kpi_total_sudah_110r').text((kpi.total_sudah_110r || 0).toLocaleString('id-ID'));
                    $('#kpi_total_anomali').text((kpi.total_anomali || 0).toLocaleString('id-ID'));
                    $('#kpi_nominal_ppn_anomali').text(formatRupiah(kpi.nominal_ppn_anomali));

                    // Update Quick Filter Pills
                    $('#pill_count_anomali').text(kpi.total_anomali || 0);
                    $('#pill_count_sudah').text(kpi.total_sudah_110r || 0);
                    $('#pill_count_ppn').text(kpi.total_ppn || 0);
                    $('#pill_count_all').text(kpi.total_pos || 0);

                    // Render Guidance Notification
                    renderGuidanceBox(kpi.total_anomali || 0, kpi.nominal_ppn_anomali || 0);

                    // Update Filter State & Render Table
                    updateQuickFilterButtons(statusFilter);
                    renderTableRows();
                } else {
                    alert('Gagal memuat data: ' + (res.message || 'Terjadi kesalahan sistem'));
                }
            },
            error: function(xhr, status, error) {
                $('#loading_indicator').hide();
                $('#btn_audit').prop('disabled', false);
                alert('Terjadi kesalahan jaringan atau server saat memuat data audit.');
            }
        });
    }

    function renderTableRows() {
        var statusFilter = $('#select_status_filter').val();
        var searchKeyword = ($('#table_search').val() || '').toLowerCase().trim();
        var tbody = $('#table_audit_tbody');
        tbody.empty();

        var filtered = auditRawData.filter(function(row) {
            // Filter status
            if (statusFilter === 'anomali_only' && row.is_anomali != 1) return false;
            if (statusFilter === 'sudah_110r' && (row.has_ppn != 1 || row.has_110r != 1)) return false;
            if (statusFilter === 'ppn_only' && row.has_ppn != 1) return false;

            // Filter search keyword
            if (searchKeyword !== '') {
                var match = (row.id + '').toLowerCase().indexOf(searchKeyword) !== -1 ||
                            (row.nomer || '').toLowerCase().indexOf(searchKeyword) !== -1 ||
                            (row.nomer2 || '').toLowerCase().indexOf(searchKeyword) !== -1 ||
                            (row.cabang_nama || '').toLowerCase().indexOf(searchKeyword) !== -1 ||
                            (row.gudang_nama || '').toLowerCase().indexOf(searchKeyword) !== -1 ||
                            (row.oleh_nama || '').toLowerCase().indexOf(searchKeyword) !== -1;
                if (!match) return false;
            }
            return true;
        });

        $('#badge_count_row').text(filtered.length + ' Data');

        if (filtered.length === 0) {
            tbody.append('<tr><td colspan="9" class="text-center text-muted" style="padding: 25px;"><i class="fa fa-search"></i> Tidak ada transaksi yang sesuai kriteria filter.</td></tr>');
            return;
        }

        var html = '';
        for (var i = 0; i < filtered.length; i++) {
            var item = filtered[i];
            var rowClass = item.is_anomali == 1 ? 'warning' : '';

            var statusHtml = '';
            if (item.has_ppn == 1) {
                if (item.has_110r == 1) {
                    var nota110 = '';
                    if (item.list_110r && item.list_110r.length > 0) {
                        nota110 = '<br><small class="text-muted">' + item.list_110r[0].nomer + '</small>';
                    }
                    statusHtml = '<span class="badge-status badge-success-custom"><i class="fa fa-check"></i> Sudah 110r</span>' + nota110;
                } else {
                    statusHtml = '<span class="badge-status badge-danger-custom"><i class="fa fa-times"></i> Belum Terbit 110r</span>';
                }
            } else {
                statusHtml = '<span class="badge-status badge-default-custom">Non-PPN</span>';
            }

            var actionHtml = '<div class="btn-group">' +
                '<a href="' + globalBaseUrl + 'transaksi/viewDetail/' + item.id + '" target="_blank" class="btn btn-default btn-xs" title="Lihat Detail Transaksi">' +
                    '<i class="fa fa-eye"></i>' +
                '</a>' +
                '<a href="' + globalBaseUrl + 'ToolRepairSusulanPreFakturPpnKeluaran/run_susulanPerFakturPpnKeluaran?tr_id=' + item.id + '" target="_blank" class="btn btn-primary btn-xs" title="Proses Susulan Faktur 110r">' +
                    '<i class="fa fa-external-link"></i> Susulkan' +
                '</a>' +
            '</div>';

            html += '<tr class="' + rowClass + '">' +
                '<td class="text-center">' + (i + 1) + '</td>' +
                '<td class="text-center"><b>' + item.id + '</b></td>' +
                '<td><b>' + item.nomer + '</b>' + (item.nomer2 ? '<br><small class="text-muted">' + item.nomer2 + '</small>' : '') + '</td>' +
                '<td>' + item.fulldate + '<br><small class="text-muted">' + (item.dtime || '') + '</small></td>' +
                '<td>' + (item.cabang_nama || '-') + '<br><small class="text-muted">' + (item.gudang_nama || '') + '</small></td>' +
                '<td class="text-center"><code>' + item.jenis + '</code></td>' +
                '<td class="text-right"><b>' + (item.has_ppn == 1 ? formatRupiah(item.ppn_amount) : '-') + '</b></td>' +
                '<td class="text-center">' + statusHtml + '</td>' +
                '<td class="text-center">' + actionHtml + '</td>' +
            '</tr>';
        }

        tbody.html(html);
    }

    function exportToCsv() {
        if (!auditRawData || auditRawData.length === 0) {
            alert('Tidak ada data yang dapat diekspor. Silakan jalankan audit terlebih dahulu.');
            return;
        }

        var csv = [];
        var headers = ["No", "ID Transaksi", "Nomer Nota", "Nomer Nota 2", "Tanggal", "Waktu", "Cabang", "Gudang", "Jenis", "Nominal PPN", "Status 110r", "Nomer 110r"];
        csv.push(headers.join(","));

        for (var i = 0; i < auditRawData.length; i++) {
            var item = auditRawData[i];
            var nomer110r = (item.list_110r && item.list_110r.length > 0) ? item.list_110r[0].nomer : "";
            var status110r = item.has_ppn == 1 ? (item.has_110r == 1 ? "Sudah Terbit" : "Belum Terbit (Anomali)") : "Non-PPN";

            var row = [
                i + 1,
                item.id,
                '"' + (item.nomer || '') + '"',
                '"' + (item.nomer2 || '') + '"',
                item.fulldate,
                item.dtime || '',
                '"' + (item.cabang_nama || '') + '"',
                '"' + (item.gudang_nama || '') + '"',
                item.jenis,
                item.ppn_amount || 0,
                '"' + status110r + '"',
                '"' + nomer110r + '"'
            ];
            csv.push(row.join(","));
        }

        var csvString = csv.join("\r\n");
        var blob = new Blob([csvString], { type: "text/csv;charset=utf-8;" });
        var url = URL.createObjectURL(blob);
        var a = document.createElement("a");
        a.href = url;
        a.download = "Audit_POS_PPN_110r_" + ($('#input_date_start').val()) + "_sd_" + ($('#input_date_stop').val()) + ".csv";
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }

    $(document).ready(function() {
        // Event listeners
        $('#btn_audit').on('click', function() {
            fetchAuditData();
        });

        $('#btn_reset').on('click', function() {
            $('#input_date_start').val('<?php echo htmlspecialchars($default_date_start); ?>');
            $('#input_date_stop').val('<?php echo htmlspecialchars($default_date_stop); ?>');
            $('#select_cabang').val('-1');
            $('#select_status_filter').val('anomali_only');
            $('#table_search').val('');
            updateQuickFilterButtons('anomali_only');
            fetchAuditData();
        });

        $('#select_status_filter').on('change', function() {
            var val = $(this).val();
            updateQuickFilterButtons(val);
            renderTableRows();
        });

        $('.quick-filter-btn').on('click', function() {
            var filter = $(this).data('filter');
            $('#select_status_filter').val(filter);
            updateQuickFilterButtons(filter);
            renderTableRows();
        });

        $('#table_search').on('keyup', function() {
            renderTableRows();
        });

        $('#btn_export_csv').on('click', function() {
            exportToCsv();
        });

        // Auto load on open
        fetchAuditData();
    });
</script>
<!-- END OF COMPLETE REPEATED LOGIC -->


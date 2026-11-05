<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!-- START OF COMPLETE REPEATED LOGIC -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" />
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
        font-size: 40px;
        opacity: 0.25;
    }
    .kpi-box .kpi-num {
        font-size: 26px;
        font-weight: 700;
        line-height: 1.1;
    }
    .kpi-box .kpi-label {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 5px;
        opacity: 0.9;
    }
    .kpi-box .kpi-sub {
        font-size: 12px;
        margin-top: 4px;
        opacity: 0.85;
    }
    .kpi-blue   { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); }
    .kpi-green  { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
    .kpi-orange { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); }
    .kpi-red    { background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%); }
    .kpi-purple { background: linear-gradient(135deg, #654ea3 0%, #eaafc8 100%); }

    .badge-diff-neg {
        background-color: #dd4b39;
        color: #fff;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: bold;
    }
    .badge-diff-pos {
        background-color: #f39c12;
        color: #fff;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: bold;
    }
    .badge-diff-zero {
        background-color: #00a65a;
        color: #fff;
        padding: 4px 8px;
        border-radius: 4px;
        font-weight: bold;
    }
    .table-audit th {
        background-color: #f4f6f9;
        font-weight: 600;
        text-align: center;
        vertical-align: middle !important;
    }
    .select2-container .select2-selection--single {
        height: 34px !important;
        border-color: #d2d6de !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 32px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 32px !important;
    }
    .nav-tabs-custom > .nav-tabs > li.active {
        border-top-color: #3c8dbc;
    }
</style>

<div class="content-wrapper-tool" style="padding: 15px;">
    <!-- HEADER BAR -->
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <div>
            <h3 style="margin: 0; font-weight: 700; color: #333;">
                <i class="fa fa-balance-scale text-primary"></i> Audit Stok vs Serial Produk
            </h3>
            <p style="margin: 5px 0 0 0; color: #777; font-size: 13px;">
                Pemeriksaan & Rekonsiliasi Otomatis antara Saldo Kartu Stok Fisik dengan Mutasi Nomor Seri
            </p>
        </div>
        <div style="margin-top: 10px;">
            <a href="<?php echo base_url(); ?>LedgerV2/viewBalances_l1/RekeningPembantuAktivaBerwujud/1010030030" class="btn btn-default btn-sm" target="_blank">
                <i class="fa fa-folder-open"></i> Buka Product Inventory (1010030030)
            </a>
        </div>
    </div>

    <!-- FILTER PANEL -->
    <div class="audit-card">
        <div class="audit-header">
            <i class="fa fa-filter text-primary"></i> Parameter Audit Produk
        </div>
        <div class="audit-body">
            <form id="form_filter" onsubmit="return false;">
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label><i class="fa fa-building"></i> Cabang:</label>
                            <select id="select_cabang" class="form-control">
                                <?php foreach ($cabangs as $cb): ?>
                                    <option value="<?php echo $cb['id']; ?>" <?php echo ($cb['id'] == $default_cabang_id) ? "selected" : ""; ?>>
                                        [<?php echo $cb['id']; ?>] <?php echo $cb['nama']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <label><i class="fa fa-cubes"></i> Gudang:</label>
                            <select id="select_gudang" class="form-control">
                                <?php foreach ($gudangs as $gd): ?>
                                    <option value="<?php echo $gd['id']; ?>" data-cabang="<?php echo $gd['cabang_id']; ?>" <?php echo ($gd['id'] == $default_gudang_id) ? "selected" : ""; ?>>
                                        [<?php echo $gd['id']; ?>] <?php echo $gd['nama']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label><i class="fa fa-search"></i> Cari Produk Berserial:</label>
                            <select id="select_produk" class="form-control select2" style="width: 100%;">
                                <?php if ($default_produk): ?>
                                    <option value="<?php echo $default_produk['id']; ?>" selected>
                                        [<?php echo $default_produk['kode']; ?>] <?php echo $default_produk['nama']; ?> (<?php echo $default_produk['jml_serial']; ?> komponen/set)
                                    </option>
                                <?php else: ?>
                                    <option value="">-- Ketik Nama atau Kode Produk --</option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row" style="margin-top: 10px;">
                    <div class="col-md-12">
                        <button type="button" id="btn_audit" class="btn btn-primary">
                            <i class="fa fa-search"></i> <b>Periksa Produk Ini</b>
                        </button>
                        <button type="button" id="btn_radar" class="btn btn-warning" style="margin-left: 8px;">
                            <i class="fa fa-crosshairs"></i> <b>Deteksi Semua Produk Selisih (Radar)</b>
                        </button>
                        <span id="loading_spinner" style="display: none; margin-left: 15px; color: #3c8dbc; font-weight: bold;">
                            <i class="fa fa-spinner fa-spin fa-2x" style="vertical-align: middle;"></i> Memproses data...
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- RADAR SCANNER PANEL (DYNAMIC) -->
    <div id="radar_section" class="audit-card audit-card-warning" style="display: none;">
        <div class="audit-header" style="display: flex; justify-content: space-between; align-items: center;">
            <span><i class="fa fa-crosshairs text-yellow"></i> Hasil Deteksi Radar: Produk Mengalami Selisih Stok vs Serial</span>
            <button type="button" class="btn btn-xs btn-default" onclick="$('#radar_section').slideUp();">
                <i class="fa fa-times"></i> Tutup Radar
            </button>
        </div>
        <div class="audit-body">
            <div id="radar_summary_text" style="margin-bottom: 12px; font-size: 14px;"></div>
            <div class="table-responsive">
                <table id="tbl_radar" class="table table-bordered table-striped table-hover table-audit" style="width: 100%;">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th style="width: 120px;">Kode Produk</th>
                            <th>Nama Produk</th>
                            <th style="width: 100px;">Komponen/Set</th>
                            <th style="width: 100px;">Stok Buku</th>
                            <th style="width: 100px;">Pasang Serial</th>
                            <th style="width: 100px;">Total Serial Pcs</th>
                            <th style="width: 110px;">Selisih</th>
                            <th style="width: 150px;">Status Komponen</th>
                            <th style="width: 90px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tbl_radar_body">
                        <!-- Loaded dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- PRODUCT DETAIL AUDIT SECTION -->
    <div id="audit_detail_section" style="display: none;">
        <!-- KPI SUMMARY ROW -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="kpi-box kpi-blue">
                    <i class="fa fa-book kpi-icon"></i>
                    <div class="kpi-num" id="kpi_stok">0</div>
                    <div class="kpi-label">Saldo Stok Buku</div>
                    <div class="kpi-sub" id="kpi_stok_rp">Rp 0</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="kpi-box kpi-purple">
                    <i class="fa fa-barcode kpi-icon"></i>
                    <div class="kpi-num" id="kpi_serial">0</div>
                    <div class="kpi-label">Serial Fisik Aktif</div>
                    <div class="kpi-sub" id="kpi_serial_pcs">0 pcs total serial</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="kpi-box" id="kpi_status_box">
                    <i class="fa fa-balance-scale kpi-icon"></i>
                    <div class="kpi-num" id="kpi_diff">0</div>
                    <div class="kpi-label" id="kpi_status_label">Status Sinkronisasi</div>
                    <div class="kpi-sub" id="kpi_status_sub">-</div>
                </div>
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="kpi-box" id="kpi_parts_box">
                    <i class="fa fa-cubes kpi-icon"></i>
                    <div class="kpi-num" id="kpi_parts_status">OK</div>
                    <div class="kpi-label">Keseimbangan Part</div>
                    <div class="kpi-sub" id="kpi_parts_detail">-</div>
                </div>
            </div>
        </div>

        <!-- TABS CONTAINER -->
        <div class="nav-tabs-custom" style="box-shadow: 0 2px 6px rgba(0,0,0,0.08); border-radius: 6px;">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#tab_discrepancy" data-toggle="tab">
                        <i class="fa fa-exclamation-triangle text-red"></i> <b>Temuan Transaksi Selisih</b> 
                        <span id="badge_discrepancy_count" class="label label-danger" style="margin-left: 5px;">0</span>
                    </a>
                </li>
                <li>
                    <a href="#tab_active_serials" data-toggle="tab">
                        <i class="fa fa-list-ol text-green"></i> <b>Nomor Seri Fisik Aktif</b> 
                        <span id="badge_active_serials_count" class="label label-success" style="margin-left: 5px;">0</span>
                    </a>
                </li>
                <li>
                    <a href="#tab_all_reconciled" data-toggle="tab">
                        <i class="fa fa-history text-blue"></i> <b>Rekonsiliasi Lengkap Seluruh Transaksi</b>
                        <span id="badge_all_tr_count" class="label label-primary" style="margin-left: 5px;">0</span>
                    </a>
                </li>
            </ul>

            <div class="tab-content" style="padding: 15px;">
                <!-- TAB 1: DISCREPANCY LOG -->
                <div class="tab-pane active" id="tab_discrepancy">
                    <div id="discrepancy_banner" style="margin-bottom: 15px;"></div>
                    <div class="table-responsive">
                        <table id="tbl_discrepancy" class="table table-bordered table-hover table-audit" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 35px;">No</th>
                                    <th style="width: 90px;">ID Master</th>
                                    <th style="width: 130px;">Kategori</th>
                                    <th style="width: 130px;">Tgl & Jam</th>
                                    <th>No. Transaksi</th>
                                    <th>No. Referensi</th>
                                    <th>Pelaku & Pihak</th>
                                    <th style="width: 90px;">Mutasi Stok</th>
                                    <th style="width: 100px;">Mutasi Serial</th>
                                    <th style="width: 90px;">Selisih</th>
                                    <th>Rincian Part</th>
                                    <th style="width: 110px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbl_discrepancy_body">
                                <!-- Loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: ACTIVE SERIALS -->
                <div class="tab-pane" id="tab_active_serials">
                    <p class="text-muted" style="margin-bottom: 12px;">
                        <i class="fa fa-info-circle text-info"></i> Daftar seluruh nomor seri fisik yang saat ini berstatus aktif (tersedia di gudang terpilih):
                    </p>
                    <div class="table-responsive">
                        <table id="tbl_active_serials" class="table table-bordered table-striped table-hover table-audit" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th>Nomor Seri Fisik</th>
                                    <th>Komponen / SKU</th>
                                    <th>Tanggal Masuk</th>
                                    <th>No. Transaksi Penerimaan</th>
                                </tr>
                            </thead>
                            <tbody id="tbl_active_serials_body">
                                <!-- Loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: ALL RECONCILED -->
                <div class="tab-pane" id="tab_all_reconciled">
                    <p class="text-muted" style="margin-bottom: 12px;">
                        <i class="fa fa-info-circle text-info"></i> Seluruh riwayat transaksi produk ini beserta status kesesuaian antara mutasi stok dan mutasi serial:
                    </p>
                    <div class="table-responsive">
                        <table id="tbl_all_reconciled" class="table table-bordered table-hover table-audit" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th style="width: 35px;">No</th>
                                    <th style="width: 80px;">ID Master</th>
                                    <th style="width: 120px;">Kategori</th>
                                    <th style="width: 130px;">Tgl & Jam</th>
                                    <th>No. Transaksi</th>
                                    <th>Referensi</th>
                                    <th>Pelaku</th>
                                    <th style="width: 80px;">Stok In</th>
                                    <th style="width: 80px;">Stok Out</th>
                                    <th style="width: 85px;">Serial In</th>
                                    <th style="width: 85px;">Serial Out</th>
                                    <th style="width: 80px;">Status</th>
                                    <th style="width: 110px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tbl_all_reconciled_body">
                                <!-- Loaded dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- RINCIAN TRANSAKSI (PRODUK & SERIAL) DITAMPILKAN SECARA INLINE COLLAPSIBLE PADA SETIAP BARIS TABEL -->
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    var globalBaseUrl = "<?php echo base_url(); ?>";

    $(document).ready(function() {
        // Init Select2 for Product
        $('#select_produk').select2({
            placeholder: '-- Ketik Nama atau Kode Produk --',
            allowClear: true,
            ajax: {
                url: globalBaseUrl + 'ToolCekStokSerial/ajaxSearchProduct',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return { results: data.results };
                },
                cache: true
            },
            minimumInputLength: 2
        });

        // Filter gudang dropdown according to cabang
        $('#select_cabang').on('change', function() {
            var cid = $(this).val();
            $('#select_gudang option').each(function() {
                var g_cid = $(this).data('cabang');
                var val = $(this).val();
                if (val == -1 || g_cid == cid) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }).trigger('change');

        // Button Action: Periksa Produk
        $('#btn_audit').on('click', function() {
            runCheckProduct();
        });

        // Button Action: Deteksi Radar Semua Produk Selisih
        $('#btn_radar').on('click', function() {
            runRadarScan();
        });

        // Auto trigger default product check if default exists
        <?php if ($default_produk_id > 0): ?>
            runCheckProduct();
        <?php endif; ?>
    });

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }

    function runCheckProduct(overridePid) {
        var pid = overridePid || $('#select_produk').val();
        var cid = $('#select_cabang').val();
        var gid = $('#select_gudang').val();

        if (!pid || pid <= 0) {
            swal({ type: 'warning', title: 'Peringatan', text: 'Silakan pilih produk yang akan diperiksa terlebih dahulu.' });
            return;
        }

        $('#loading_spinner').show();
        $('#btn_audit').prop('disabled', true);

        $.ajax({
            url: globalBaseUrl + 'ToolCekStokSerial/ajaxCheckProduct',
            type: 'POST',
            dataType: 'json',
            data: {
                produk_id: pid,
                cabang_id: cid,
                gudang_id: gid
            },
            success: function(res) {
                $('#loading_spinner').hide();
                $('#btn_audit').prop('disabled', false);

                if (res.status !== 'success') {
                    swal({ type: 'error', title: 'Gagal', text: res.message || 'Terjadi kesalahan sistem.' });
                    return;
                }

                renderProductAudit(res);
            },
            error: function(xhr, status, err) {
                $('#loading_spinner').hide();
                $('#btn_audit').prop('disabled', false);
                swal({ type: 'error', title: 'Error', text: 'Gagal memproses audit: ' + err });
            }
        });
    }

    function renderProductAudit(data) {
        var s = data.summary;
        var p = data.produk;

        $('#audit_detail_section').slideDown();

        // 1. KPI Stok
        $('#kpi_stok').text(s.stok_qty.toLocaleString('id-ID') + ' unit');
        $('#kpi_stok_rp').text(formatRupiah(s.stok_rp));

        // 2. KPI Serial
        $('#kpi_serial').text(s.serial_pairs.toLocaleString('id-ID') + ' pasang');
        $('#kpi_serial_pcs').text(s.total_physical_serials.toLocaleString('id-ID') + ' pcs nomor seri aktif');

        // 3. KPI Status Sinkronisasi
        var diff = s.diff;
        var boxStatus = $('#kpi_status_box');
        boxStatus.removeClass('kpi-green kpi-red kpi-orange');

        if (diff === 0) {
            boxStatus.addClass('kpi-green');
            $('#kpi_diff').html('<i class="fa fa-check-circle"></i> SINKRON');
            $('#kpi_status_sub').text('Jumlah stok fisik & nomor seri 100% cocok');
        } else {
            boxStatus.addClass('kpi-red');
            var diffText = (diff > 0) ? ('+' + diff.toLocaleString('id-ID')) : diff.toLocaleString('id-ID');
            $('#kpi_diff').html(diffText + ' Pasang');
            var subText = (diff > 0) ? 'Stok buku melebihi serial aktif' : 'Serial aktif melebihi stok buku';
            $('#kpi_status_sub').text(subText);
        }

        // 4. KPI Part Balance
        var boxParts = $('#kpi_parts_box');
        boxParts.removeClass('kpi-green kpi-orange kpi-blue');
        var partsArr = [];
        for (var sku in s.parts_breakdown) {
            partsArr.push(sku + ': ' + s.parts_breakdown[sku]);
        }
        var partsStr = partsArr.length > 0 ? partsArr.join(', ') : 'Tidak ada serial';

        if (s.is_unbalanced) {
            boxParts.addClass('kpi-orange');
            $('#kpi_parts_status').html('<i class="fa fa-exclamation-triangle"></i> JOMPLANG');
            $('#kpi_parts_detail').text(partsStr);
        } else {
            boxParts.addClass('kpi-green');
            $('#kpi_parts_status').html('<i class="fa fa-check"></i> SEIMBANG');
            $('#kpi_parts_detail').text(partsStr);
        }

        // 5. Tab Discrepancy
        var discList = data.discrepancies;
        $('#badge_discrepancy_count').text(discList.length);

        if (discList.length === 0) {
            $('#discrepancy_banner').html(
                '<div class="alert alert-success" style="margin-bottom: 0;">' +
                '<i class="fa fa-check-circle fa-lg"></i> <b>Luar Biasa!</b> Tidak ditemukan ketidakcocokan historis transaksi untuk produk ini. Seluruh mutasi kartu stok dan mutasi nomor seri tercatat selaras.' +
                '</div>'
            );
            $('#tbl_discrepancy_body').html('<tr><td colspan="12" class="text-center text-muted" style="padding: 20px;">Tidak ada transaksi selisih ditemukan.</td></tr>');
        } else {
            $('#discrepancy_banner').html(
                '<div class="alert alert-danger" style="margin-bottom: 0;">' +
                '<i class="fa fa-exclamation-circle fa-lg"></i> <b>Ditemukan ' + discList.length + ' Transaksi Bermasalah!</b> ' +
                'Ada mutasi transaksi yang memotong/menambah stok namun nomor serinya tidak sesuai.' +
                '</div>'
            );

            var rowsHtml = '';
            for (var i = 0; i < discList.length; i++) {
                var d = discList[i];
                var partsDetail = [];
                for (var pName in d.parts_out) {
                    partsDetail.push('Out ' + pName + ': ' + d.parts_out[pName]);
                }
                for (var pName in d.parts_in) {
                    partsDetail.push('In ' + pName + ': ' + d.parts_in[pName]);
                }
                var partStr = partsDetail.length > 0 ? partsDetail.join('<br>') : '-';

                var netBadge = '';
                if (d.net_diff < 0) {
                    netBadge = '<span class="badge-diff-neg">' + d.net_diff + ' pasang</span>';
                } else if (d.net_diff > 0) {
                    netBadge = '<span class="badge-diff-pos">+' + d.net_diff + ' pasang</span>';
                } else {
                    netBadge = '<span class="badge-diff-zero">0 (imbang)</span>';
                }

                rowsHtml += '<tr id="row_disc_' + i + '">' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td class="text-center"><span class="label label-default" style="font-size: 11px;">' + d.id_master + '</span></td>' +
                    '<td><span class="label label-info">' + d.category + '</span></td>' +
                    '<td style="font-size: 12px;">' + d.dtime + '</td>' +
                    '<td><b>' + d.sample_no + '</b><br><small class="text-muted">ID: ' + d.sample_id + '</small></td>' +
                    '<td>' + (d.reference_nomer ? ('<b class="text-primary">' + d.reference_nomer + '</b>') : '-') + '</td>' +
                    '<td>' + (d.pelaku ? ('<i class="fa fa-user"></i> ' + d.pelaku) : '-') + (d.pihak ? ('<br><small class="text-muted">' + d.pihak + '</small>') : '') + '</td>' +
                    '<td class="text-center">In: ' + d.stok_in + '<br>Out: <b>' + d.stok_out + '</b></td>' +
                    '<td class="text-center">In: ' + d.serial_in_pairs + '<br>Out: <b>' + d.serial_out_pairs + '</b></td>' +
                    '<td class="text-center">' + netBadge + '</td>' +
                    '<td style="font-size: 11px;">' + partStr + '</td>' +
                    '<td class="text-center">' +
                    '<button type="button" class="btn btn-info btn-xs btn-toggle-detail" id="btn_toggle_disc_' + i + '" onclick="toggleRowDetail(\'disc\', ' + i + ', \'' + d.id_master + '\', \'' + d.sample_no + '\', ' + d.sample_id + ')">' +
                    '<i class="fa fa-chevron-down"></i> Rincian' +
                    '</button>' +
                    '</td>' +
                    '</tr>' +
                    '<tr id="collapse_disc_' + i + '" class="tr-detail-collapse" style="display: none; background: #fafbfc;">' +
                    '<td colspan="12" style="padding: 12px 15px; border-top: 1px dashed #3c8dbc; border-bottom: 2px solid #3c8dbc;">' +
                    '<div id="collapse_disc_content_' + i + '"></div>' +
                    '</td>' +
                    '</tr>';
            }
            $('#tbl_discrepancy_body').html(rowsHtml);
        }

        // 6. Tab Active Serials
        var activeList = data.active_serials;
        $('#badge_active_serials_count').text(activeList.length);
        if (activeList.length === 0) {
            $('#tbl_active_serials_body').html('<tr><td colspan="5" class="text-center text-muted" style="padding: 20px;">Tidak ada nomor seri aktif di gudang ini.</td></tr>');
        } else {
            var activeHtml = '';
            for (var j = 0; j < activeList.length; j++) {
                var as = activeList[j];
                activeHtml += '<tr>' +
                    '<td class="text-center">' + (j + 1) + '</td>' +
                    '<td><code>' + as.serial_no + '</code></td>' +
                    '<td><span class="label label-primary">' + (as.sku || '-') + '</span></td>' +
                    '<td style="font-size: 12px;">' + as.tgl_masuk + '</td>' +
                    '<td>' + (as.transaksi_no || '-') + ' <small class="text-muted">(ID: ' + as.transaksi_id + ')</small></td>' +
                    '</tr>';
            }
            $('#tbl_active_serials_body').html(activeHtml);
        }

        // 7. Tab All Reconciled
        var allList = data.all_reconciled;
        $('#badge_all_tr_count').text(allList.length);
        var allHtml = '';
        for (var k = 0; k < allList.length; k++) {
            var item = allList[k];
            var isOk = (item.net_diff === 0 && item.diff_in === 0 && item.diff_out === 0);
            var statusBadge = isOk 
                ? '<span class="label label-success"><i class="fa fa-check"></i> Cocok</span>' 
                : '<span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Selisih ' + item.net_diff + '</span>';

            allHtml += '<tr id="row_all_' + k + '" ' + (isOk ? '' : 'style="background-color: #fff0f0;"') + '>' +
                '<td class="text-center">' + (k + 1) + '</td>' +
                '<td class="text-center"><span class="label label-default">' + item.id_master + '</span></td>' +
                '<td>' + item.category + '</td>' +
                '<td style="font-size: 12px;">' + item.dtime + '</td>' +
                '<td><b>' + item.sample_no + '</b></td>' +
                '<td>' + (item.reference_nomer || '-') + '</td>' +
                '<td>' + (item.pelaku || '-') + '</td>' +
                '<td class="text-center">' + item.stok_in + '</td>' +
                '<td class="text-center">' + item.stok_out + '</td>' +
                '<td class="text-center">' + item.serial_in_pairs + '</td>' +
                '<td class="text-center">' + item.serial_out_pairs + '</td>' +
                '<td class="text-center">' + statusBadge + '</td>' +
                '<td class="text-center">' +
                '<button type="button" class="btn btn-info btn-xs btn-toggle-detail" id="btn_toggle_all_' + k + '" onclick="toggleRowDetail(\'all\', ' + k + ', \'' + item.id_master + '\', \'' + item.sample_no + '\', ' + item.sample_id + ')">' +
                '<i class="fa fa-chevron-down"></i> Rincian' +
                '</button>' +
                '</td>' +
                '</tr>' +
                '<tr id="collapse_all_' + k + '" class="tr-detail-collapse" style="display: none; background: #fafbfc;">' +
                '<td colspan="13" style="padding: 12px 15px; border-top: 1px dashed #3c8dbc; border-bottom: 2px solid #3c8dbc;">' +
                '<div id="collapse_all_content_' + k + '"></div>' +
                '</td>' +
                '</tr>';
        }
        $('#tbl_all_reconciled_body').html(allHtml);
    }

    function runRadarScan() {
        var cid = $('#select_cabang').val();
        var gid = $('#select_gudang').val();

        $('#loading_spinner').show();
        $('#btn_radar').prop('disabled', true);

        $.ajax({
            url: globalBaseUrl + 'ToolCekStokSerial/ajaxScanAllMismatches',
            type: 'POST',
            dataType: 'json',
            data: {
                cabang_id: cid,
                gudang_id: gid
            },
            success: function(res) {
                $('#loading_spinner').hide();
                $('#btn_radar').prop('disabled', false);

                if (res.status !== 'success') {
                    swal({ type: 'error', title: 'Gagal', text: 'Gagal menjalankan pemindaian radar.' });
                    return;
                }

                $('#radar_section').slideDown();
                $('#radar_summary_text').html(
                    '<b>Pemindaian Selesai dalam ' + res.duration + ' detik:</b> Ditemukan <b class="text-red">' + res.total + ' produk</b> yang mengalami selisih stok atau ketidakseimbangan nomor seri pada gudang terpilih.'
                );

                if (res.total === 0) {
                    $('#tbl_radar_body').html('<tr><td colspan="10" class="text-center text-success" style="padding: 20px;"><b>Mantap!</b> Semua produk berserial pada gudang ini memiliki saldo stok dan nomor seri yang 100% sinkron.</td></tr>');
                } else {
                    var rHtml = '';
                    for (var i = 0; i < res.mismatches.length; i++) {
                        var m = res.mismatches[i];
                        var diffBadge = '';
                        if (m.diff < 0) {
                            diffBadge = '<span class="badge-diff-neg">' + m.diff + ' pasang</span>';
                        } else if (m.diff > 0) {
                            diffBadge = '<span class="badge-diff-pos">+' + m.diff + ' pasang</span>';
                        } else {
                            diffBadge = '<span class="badge-diff-zero">0</span>';
                        }

                        var partsDetail = [];
                        for (var sku in m.parts) {
                            partsDetail.push(sku + ': ' + m.parts[sku]);
                        }
                        var pStr = partsDetail.length > 0 ? partsDetail.join(', ') : 'Kosong';
                        var balBadge = m.is_unbalanced 
                            ? ('<span class="label label-warning">Jomplang</span><br><small>' + pStr + '</small>') 
                            : ('<span class="label label-success">Rata</span><br><small>' + pStr + '</small>');

                        rHtml += '<tr>' +
                            '<td class="text-center">' + (i + 1) + '</td>' +
                            '<td><code>' + m.kode + '</code></td>' +
                            '<td><b>' + m.nama + '</b></td>' +
                            '<td class="text-center">' + m.jml_serial + ' part/set</td>' +
                            '<td class="text-center"><b>' + m.stok + '</b></td>' +
                            '<td class="text-center"><b>' + m.pairs + '</b></td>' +
                            '<td class="text-center">' + m.total_serial + '</td>' +
                            '<td class="text-center">' + diffBadge + '</td>' +
                            '<td style="font-size: 11px;">' + balBadge + '</td>' +
                            '<td class="text-center">' +
                            '<button type="button" class="btn btn-primary btn-xs" onclick="inspectProductFromRadar(' + m.produk_id + ', \'' + m.kode + '\', \'' + escape(m.nama) + '\', ' + m.jml_serial + ')">' +
                            '<i class="fa fa-search"></i> Periksa' +
                            '</button>' +
                            '</td>' +
                            '</tr>';
                    }
                    $('#tbl_radar_body').html(rHtml);
                }

                // Scroll to radar section
                $('html, body').animate({
                    scrollTop: $("#radar_section").offset().top - 60
                }, 500);
            },
            error: function(xhr, status, err) {
                $('#loading_spinner').hide();
                $('#btn_radar').prop('disabled', false);
                swal({ type: 'error', title: 'Error', text: 'Gagal menjalankan radar scan: ' + err });
            }
        });
    }

    function inspectProductFromRadar(pid, kode, namaEscaped, parts) {
        var nama = unescape(namaEscaped);
        // Update Select2
        var option = new Option('[' + kode + '] ' + nama + ' (' + parts + ' komponen/set)', pid, true, true);
        $('#select_produk').append(option).trigger('change');

        // Scroll to audit section
        $('html, body').animate({
            scrollTop: $("#form_filter").offset().top - 60
        }, 300);

        // Run audit
        runCheckProduct(pid);
    }

    function toggleRowDetail(prefix, idx, idMaster, sampleNo, sampleId) {
        var rowCollapse = $('#collapse_' + prefix + '_' + idx);
        var btnToggle = $('#btn_toggle_' + prefix + '_' + idx);
        var contentDiv = $('#collapse_' + prefix + '_content_' + idx);

        if (rowCollapse.is(':visible')) {
            rowCollapse.hide();
            btnToggle.removeClass('btn-warning').addClass('btn-info');
            btnToggle.html('<i class="fa fa-chevron-down"></i> Rincian');
            return;
        }

        // Tampilkan baris
        rowCollapse.show();
        btnToggle.removeClass('btn-info').addClass('btn-warning');
        btnToggle.html('<i class="fa fa-chevron-up"></i> Tutup');

        // Jika sudah dimuat sebelumnya, tidak perlu fetch ulang (instant)
        if (contentDiv.data('loaded') == '1') {
            return;
        }

        // Tampilkan loading spinner kompak
        contentDiv.html(
            '<div class="text-center" style="padding: 25px;">' +
            '<i class="fa fa-spinner fa-spin fa-2x text-primary"></i>' +
            '<p style="margin-top: 10px; font-weight: bold; color: #3c8dbc; font-size: 13px;">Memuat rincian alur dokumen, mutasi produk, dan nomor seri...</p>' +
            '</div>'
        );

        var cid = $('#select_cabang').val();
        var gid = $('#select_gudang').val();
        var activePid = $('#select_produk').val();

        $.ajax({
            url: globalBaseUrl + 'ToolCekStokSerial/ajaxGetTransactionDetail',
            type: 'POST',
            dataType: 'json',
            data: {
                id_master: idMaster,
                transaksi_id: sampleId,
                cabang_id: cid,
                gudang_id: gid,
                active_produk_id: activePid
            },
            success: function(res) {
                if (res.status !== 'success') {
                    contentDiv.html('<div class="alert alert-danger" style="margin-bottom: 0;">Gagal memuat rincian: ' + (res.message || 'Kesalahan sistem.') + '</div>');
                    return;
                }
                contentDiv.data('loaded', '1');
                contentDiv.html(buildCollapsibleDetailHtml(res));
            },
            error: function(xhr, status, err) {
                contentDiv.html('<div class="alert alert-danger" style="margin-bottom: 0;">Terjadi kesalahan saat memuat data: ' + err + '</div>');
            }
        });
    }

    function buildCollapsibleDetailHtml(res) {
        var mainTr = res.main_tr;

        // Workflow badges
        var wfBadges = '';
        for (var w = 0; w < res.all_transactions.length; w++) {
            var tr = res.all_transactions[w];
            var isMain = (tr.id == mainTr.id);
            var badgeClass = isMain ? 'label-warning' : 'label-primary';
            wfBadges += '<span class="label ' + badgeClass + '" style="font-size: 11px; margin-right: 6px; padding: 4px 8px; display: inline-block; margin-bottom: 4px;">' +
                        '<i class="fa fa-file-text-o"></i> ' + tr.nomer + ' (ID: ' + tr.id + ')' +
                        (tr.oleh_nama ? (' - ' + tr.oleh_nama) : '') +
                        '</span>';
        }

        // Discrepancy Insight
        var hasDiscrepancy = false;
        var discMessages = [];
        for (var p = 0; p < res.products.length; p++) {
            var prod = res.products[p];
            if (prod.jml_serial > 0 && (prod.diff_in !== 0 || prod.diff_out !== 0)) {
                hasDiscrepancy = true;
                var pDisc = '<b>' + prod.nama + '</b>: ';
                if (prod.diff_out !== 0) {
                    pDisc += (prod.diff_out > 0)
                        ? ('Stok keluar <b>' + prod.stok_out + ' unit</b>, namun serial fisik keluar hanya <b>' + prod.serial_out_pairs + ' pasang</b> (kurang ' + prod.diff_out + ' pasang di-scan).')
                        : ('Serial fisik keluar <b>' + prod.serial_out_pairs + ' pasang</b> melebihi stok keluar ' + prod.stok_out + ' unit.');
                }
                if (prod.diff_in !== 0) {
                    pDisc += (prod.diff_in > 0)
                        ? ('Stok masuk <b>' + prod.stok_in + ' unit</b>, namun serial masuk ' + prod.serial_in_pairs + ' pasang.')
                        : ('Serial fisik masuk <b>' + prod.serial_in_pairs + ' pasang</b> melebihi stok masuk ' + prod.stok_in + ' unit.');
                }
                discMessages.push(pDisc);
            }
        }

        var insightHtml = '';
        if (hasDiscrepancy) {
            var listItems = '';
            for (var m = 0; m < discMessages.length; m++) {
                listItems += '<li>' + discMessages[m] + '</li>';
            }
            insightHtml = '<div class="alert alert-danger" style="margin-bottom: 12px; padding: 10px 15px;">' +
                          '<h5 style="margin-top: 0; margin-bottom: 5px; font-weight: 700;"><i class="fa fa-exclamation-triangle"></i> Indikasi Selisih Mutasi Serial:</h5>' +
                          '<ul style="margin-bottom: 0; padding-left: 20px;">' + listItems + '</ul>' +
                          '</div>';
        } else {
            insightHtml = '<div class="alert alert-success" style="margin-bottom: 12px; padding: 10px 15px;">' +
                          '<i class="fa fa-check-circle"></i> <b>Mutasi Selaras:</b> Semua produk berserial pada transaksi ini memiliki mutasi stok dan pemotongan serial yang cocok 100%.' +
                          '</div>';
        }

        // Table 1: Products
        var pRows = '';
        if (res.products.length === 0) {
            pRows = '<tr><td colspan="10" class="text-center text-muted" style="padding: 15px;">Tidak ada mutasi produk pada gudang ini.</td></tr>';
        } else {
            for (var i = 0; i < res.products.length; i++) {
                var pr = res.products[i];
                var isCurrent = (pr.produk_id == res.active_produk_id);
                var rowBg = isCurrent ? 'style="background-color: #fffde7;"' : '';
                var currentBadge = isCurrent ? ' <span class="label label-warning" style="font-size: 9px;"><i class="fa fa-dot-circle-o"></i> Audit</span>' : '';

                var diffBadge = '';
                var statusBadge = '';
                if (pr.jml_serial == 0) {
                    diffBadge = '<span class="text-muted">- (Non-Serial)</span>';
                    statusBadge = '<span class="label label-default">Non-Serial</span>';
                } else if (pr.diff_out > 0) {
                    diffBadge = '<span class="badge-diff-neg">Kurang ' + pr.diff_out + ' psg</span>';
                    statusBadge = '<span class="label label-danger"><i class="fa fa-times"></i> Kurang Scan</span>';
                } else if (pr.diff_out < 0) {
                    diffBadge = '<span class="badge-diff-pos">Lebih ' + Math.abs(pr.diff_out) + ' psg</span>';
                    statusBadge = '<span class="label label-warning"><i class="fa fa-exclamation"></i> Serial Lebih</span>';
                } else if (pr.diff_in !== 0) {
                    diffBadge = '<span class="badge-diff-neg">Selisih In ' + pr.diff_in + ' psg</span>';
                    statusBadge = '<span class="label label-danger"><i class="fa fa-times"></i> Selisih Masuk</span>';
                } else {
                    diffBadge = '<span class="badge-diff-zero">0 (Cocok)</span>';
                    statusBadge = '<span class="label label-success"><i class="fa fa-check"></i> Sinkron</span>';
                }

                pRows += '<tr ' + rowBg + '>' +
                    '<td class="text-center">' + (i + 1) + '</td>' +
                    '<td><code>' + pr.kode + '</code></td>' +
                    '<td><b>' + pr.nama + '</b>' + currentBadge + '</td>' +
                    '<td class="text-center">' + pr.jml_serial + ' part</td>' +
                    '<td class="text-center">' + pr.stok_in + '</td>' +
                    '<td class="text-center"><b>' + pr.stok_out + '</b></td>' +
                    '<td class="text-center">' + pr.serial_in_pairs + '</td>' +
                    '<td class="text-center"><b>' + pr.serial_out_pairs + '</b></td>' +
                    '<td class="text-center">' + diffBadge + '</td>' +
                    '<td class="text-center">' + statusBadge + '</td>' +
                    '</tr>';
            }
        }

        // Table 2: Serials
        var sRows = '';
        if (res.serials.length === 0) {
            sRows = '<tr><td colspan="7" class="text-center text-muted" style="padding: 15px;">Tidak ada nomor seri fisik yang dipindai / termutasi pada gudang ini dalam transaksi ini.</td></tr>';
        } else {
            for (var j = 0; j < res.serials.length; j++) {
                var sr = res.serials[j];
                var directionBadge = (sr.qty_out > 0) 
                    ? '<span class="label label-danger" style="font-size: 10px;"><i class="fa fa-arrow-circle-up"></i> Out</span>' 
                    : '<span class="label label-success" style="font-size: 10px;"><i class="fa fa-arrow-circle-down"></i> In</span>';

                sRows += '<tr>' +
                    '<td class="text-center">' + (j + 1) + '</td>' +
                    '<td><code style="font-size: 12px; font-weight: bold;">' + sr.serial_no + '</code></td>' +
                    '<td><span class="label label-primary">' + sr.sku + '</span></td>' +
                    '<td style="font-size: 11px;">' + sr.produk_nama + '</td>' +
                    '<td class="text-center">' + directionBadge + '</td>' +
                    '<td><small><b>' + sr.transaksi_no + '</b></small></td>' +
                    '<td><small>' + (sr.oleh_nama || '-') + '</small></td>' +
                    '</tr>';
            }
        }

        // Assemble final Collapsible Card (with max-height: 500px as requested)
        var html = '' +
            '<div style="background: #ffffff; border: 1px solid #d2d6de; border-radius: 4px; padding: 15px; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">' +
                // Header Info Bar
                '<div class="row" style="margin-bottom: 12px; border-bottom: 1px solid #eee; padding-bottom: 10px;">' +
                    '<div class="col-md-7">' +
                        '<h5 style="margin-top: 0; margin-bottom: 4px; font-weight: 700; color: #3c8dbc;">' +
                            '<i class="fa fa-file-text-o"></i> Rincian Transaksi: <b>' + (mainTr.nomer || '-') + '</b> ' +
                            '<span class="label label-default" style="font-size: 11px;">ID: ' + (mainTr.id || '-') + ' | Master: ' + (mainTr.id_master || '-') + '</span>' +
                        '</h5>' +
                        '<div style="font-size: 12px; color: #555;">' +
                            '<span><i class="fa fa-calendar"></i> ' + (mainTr.dtime || '-') + '</span> | ' +
                            '<span><i class="fa fa-link"></i> Ref: <b class="text-danger">' + (mainTr.reference_nomer || '-') + '</b></span> | ' +
                            '<span><i class="fa fa-user"></i> Oleh: ' + (mainTr.oleh_nama || '-') + '</span>' +
                            (mainTr.customers_nama ? (' | <span><i class="fa fa-building"></i> ' + mainTr.customers_nama + '</span>') : '') +
                            (mainTr.suppliers_nama ? (' | <span><i class="fa fa-truck"></i> ' + mainTr.suppliers_nama + '</span>') : '') +
                        '</div>' +
                    '</div>' +
                    '<div class="col-md-5 text-right">' +
                        '<div style="font-size: 11px; font-weight: bold; color: #666; margin-bottom: 3px;"><i class="fa fa-random text-blue"></i> Rangkaian Dokumen Alur:</div>' +
                        '<div>' + wfBadges + '</div>' +
                    '</div>' +
                '</div>' +

                // Discrepancy Insight
                insightHtml +

                // Two Columns Side-by-Side with max-height: 500px !
                '<div class="row">' +
                    // Column 1: Products (Col-md-6)
                    '<div class="col-md-6">' +
                        '<div class="box box-primary" style="margin-bottom: 0; box-shadow: none; border: 1px solid #e1e4e8;">' +
                            '<div class="box-header with-border" style="background: #fbfbfb; padding: 8px 12px;">' +
                                '<h5 class="box-title" style="font-size: 13px; font-weight: 700; color: #3c8dbc;">' +
                                    '<i class="fa fa-cubes"></i> 1. Produk Terlibat & Mutasi Stok (' + res.products.length + ' Produk)' +
                                '</h5>' +
                            '</div>' +
                            '<div class="box-body no-padding table-responsive" style="max-height: 500px; overflow-y: auto;">' +
                                '<table class="table table-bordered table-striped table-hover table-audit" style="margin-bottom: 0; font-size: 12px;">' +
                                    '<thead>' +
                                        '<tr>' +
                                            '<th style="width: 30px;">No</th>' +
                                            '<th style="width: 90px;">Kode</th>' +
                                            '<th>Nama Produk</th>' +
                                            '<th style="width: 60px;">Part</th>' +
                                            '<th style="width: 50px;">In</th>' +
                                            '<th style="width: 50px;">Out</th>' +
                                            '<th style="width: 55px;">Ser.In</th>' +
                                            '<th style="width: 55px;">Ser.Out</th>' +
                                            '<th style="width: 85px;">Selisih</th>' +
                                            '<th style="width: 80px;">Status</th>' +
                                        '</tr>' +
                                    '</thead>' +
                                    '<tbody>' + pRows + '</tbody>' +
                                '</table>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +

                    // Column 2: Serials (Col-md-6)
                    '<div class="col-md-6">' +
                        '<div class="box box-success" style="margin-bottom: 0; box-shadow: none; border: 1px solid #e1e4e8;">' +
                            '<div class="box-header with-border" style="background: #fbfbfb; padding: 8px 12px;">' +
                                '<h5 class="box-title" style="font-size: 13px; font-weight: 700; color: #00a65a;">' +
                                    '<i class="fa fa-barcode"></i> 2. Nomor Seri Fisik yang Di-Scan (' + res.serials.length + ' Serial)' +
                                '</h5>' +
                            '</div>' +
                            '<div class="box-body no-padding table-responsive" style="max-height: 500px; overflow-y: auto;">' +
                                '<table class="table table-bordered table-striped table-hover table-audit" style="margin-bottom: 0; font-size: 12px;">' +
                                    '<thead>' +
                                        '<tr>' +
                                            '<th style="width: 30px;">No</th>' +
                                            '<th>Nomor Seri Fisik</th>' +
                                            '<th style="width: 90px;">Part/SKU</th>' +
                                            '<th>Nama Produk</th>' +
                                            '<th style="width: 55px;">Arah</th>' +
                                            '<th style="width: 120px;">Tr. Scanner</th>' +
                                            '<th style="width: 90px;">Petugas</th>' +
                                        '</tr>' +
                                    '</thead>' +
                                    '<tbody>' + sRows + '</tbody>' +
                                '</table>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

        return html;
    }
</script>
<!-- END OF COMPLETE REPEATED LOGIC -->

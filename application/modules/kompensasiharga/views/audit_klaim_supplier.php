<?php
// START OF COMPLETE REPEATED LOGIC
if (!defined('BASEPATH')) exit('No direct script access allowed');

$mode = isset($mode) ? $mode : "index";

switch ($mode) {

    case "index":
        $title = isset($title) ? $title : "AUDIT & VERIFIKASI KLAIM DISKON KE SUPPLIER";
        $subTitle = isset($subTitle) ? $subTitle : "Tool Validasi & Rekonsiliasi Saldo Piutang Diskon";
        $template = isset($template) ? $template : (MODUL_TEMPLATE_PATH . "template/transaksi_index.html");
        $jenisTr = isset($jenisTr) ? $jenisTr : "3333";
        $allSteps = isset($allSteps) ? $allSteps : array();
        $linkMenu = isset($linkMenu) ? $linkMenu : array();
        $scriptBottom = isset($scriptBottom) ? $scriptBottom : "";
        $errMsg = isset($errMsg) ? $errMsg : "";

        $p = New Layout("$title", "$subTitle", MODUL_TEMPLATE_PATH . "template/transaksi_index.html");

        $time_line = "";
        if (sizeof($allSteps) > 0) {
            $time_line = createStateHorizontal('-1', sizeof($allSteps), $jenisTr);
        }
        $keterangan_notif = notifTransaksi();

        $arrLinkMenuAdd = array();
        if (sizeof($linkMenu) > 0) {
            $lCtr = 0;
            foreach ($linkMenu as $lSpec) {
                $lCtr++;
                $arrLinkMenuAdd["linkMenu_" . $lCtr . "_link"] = MODUL_PATH . $lSpec["link"];
                $arrLinkMenuAdd["linkMenu_" . $lCtr . "_label"] = $lSpec["label"];
            }
        }

        $arrTags = array(
            "title" => $title,
            "sub_title" => $subTitle,
            "add_link" => "<a class='btn btn-success' href='" . MODUL_PATH . "AuditKlaimSupplier/index/$jenisTr'><i class='fa fa-refresh'></i> Refresh Audit Tool</a>",
            "history_title" => "",
            "history_content" => "",
            "history_footer" => "",
            "recap_title" => "",
            "recap_content" => "",
            "recap_footer" => "",
            "profile_name" => isset($this->session->login['nama']) ? $this->session->login['nama'] : "",
            "newTrTarget" => MODUL_PATH . "AuditKlaimSupplier/index/$jenisTr",
            "newTrDisp" => "inline-table",
            "scriptBottom" => $scriptBottom,
            "index_active" => "class='active'",
            "time_line" => $time_line,
            "keterangan_notif" => $keterangan_notif,
        );

        if (sizeof($arrLinkMenuAdd) > 0) {
            $arrTags = $arrTags + $arrLinkMenuAdd;
        }

        $p->addTags($arrTags);
        $p->render();
        break;

    case "viewKlaimSupplierIndex":
        $str = "";
        $stepper = isset($_GET['step']) ? $_GET['step'] : 1;
        $title = isset($title) ? $title : "";
        $subTitle = isset($subTitle) ? $subTitle : "";
        $arrayProgressLabels = isset($arrayProgressLabels) ? $arrayProgressLabels : array();
        $pihakDataSelected = isset($pihakDataSelected) ? intval($pihakDataSelected) : 0;
        $linkDataSelected = isset($linkDataSelected) ? $linkDataSelected : "";
        $ajaxDataUrl = isset($ajaxDataUrl) ? $ajaxDataUrl : (MODUL_PATH . "AuditKlaimSupplier/dataAjax/" . $jenisTr . "/");
        $addTabHeaderLimit = isset($addTabHeaderLimit) ? $addTabHeaderLimit : 0;
        $arrPihakData = isset($arrPihakData) ? $arrPihakData : array();
        $onprogressTitle = isset($onprogressTitle) ? $onprogressTitle : "<span class='fa fa-shield text-green'></span> AUDIT & VERIFIKASI KLAIM DISKON KE SUPPLIER";

        $strOnprog = "";
        $strOnprog .= "<div class=\"box box-primary\" style='border-top: 3px solid #3c8dbc;'>";
        $strOnprog .= "<div class=\"box-header with-border\">";
        $strOnprog .= "<h4 class=\"box-title text-uppercase\"><i class='fa fa-shield text-primary'></i> $onprogressTitle</h4>";
        $strOnprog .= "<div class='box-tools pull-right'>";
        if (sizeof($arrPihakData) > 0) {
            asort($arrPihakData);
            $link = $linkDataSelected . "?sid=";
            $strOnprog .= "<span style='vertical-align: middle; margin-right: 5px;'>Pilih Supplier: </span>";
            $strOnprog .= "<select id='supplier_klaim_select' class='selectpicker show-tick' data-live-search='true' data-size='10' data-style='btn-primary' data-width='auto' onchange=\"var v = this.value; window.selectedKlaimGrnMap = {}; if($('#klaimList .box').length){ $('#klaimList .box').append('<div class=\\'overlay\\'><i class=\\'fa fa-refresh fa-spin text-primary\\'></i></div>'); }else{ if(typeof top.open_holdon === 'function'){ top.open_holdon(); } } $('#klaimList').load('$link' + v, function(){ if(typeof top.close_holdon === 'function'){ top.close_holdon(); } });\">";
            $strOnprog .= "<option value='0'>--- pilih supplier ---</option>";
            foreach ($arrPihakData as $sid => $snama) {
                $selected = ($sid == $pihakDataSelected) ? "selected" : "";
                $strOnprog .= "<option value='$sid' $selected> $snama </option>";
            }
            $strOnprog .= "</select>";
            $strOnprog .= "<script>$('#supplier_klaim_select').selectpicker();</script>";
        }
        $strOnprog .= "</div>";
        $strOnprog .= "</div>";

        if (isset($arrPihakData) && (count($arrPihakData) > 0) && (count($arrPihakData) <= $addTabHeaderLimit)) {
            $arrPihakData[0] = "ALL";
            ksort($arrPihakData);
            $content = "<ul class='nav nav-tabs'>";
            foreach ($arrPihakData as $step => $label) {
                $color = $step == $pihakDataSelected ? "#454549" : "#999999";
                $borderColor = $step == $pihakDataSelected ? "#cccccc" : "#ffffff";
                $bgColor = $step == $pihakDataSelected ? "#ffffff" : "#f0f0f0";
                $linkStep = $linkDataSelected . "?sid=$step";
                $content .= "<li class='nav-item'>";
                $varUrl = $step == $pihakDataSelected ? "javascript:void(0);" : "if($('#klaimList>div.box-body')){ window.selectedKlaimGrnMap = {}; $('#klaimList').load('$linkStep'); }else{ console.log('tidak ada container klaimList'); }";
                $content .= "<a class='nav-link btn' style='background:$bgColor;color:$color;border-width:1px 1px 0px 1px; border-color:$borderColor; border-style: solid;' onclick =\"$varUrl\">";
                if ($step == $pihakDataSelected) {
                    $content .= "<span class='fa fa-check-circle text-primary'></span> ";
                }
                $content .= "$label</a>";
                $content .= "</li>";
            }
            $content .= "</ul>";
            $strOnprog .= $content;
        }

        $strOnprog .= "<div class=\"box-body table-responsive\">";

        // Summary Information Box
        $strOnprog .= "<div class='callout callout-info' style='margin-bottom: 12px; padding: 8px 15px; border-left: 4px solid #00c0ef; background-color: #f4f8fa; color: #333;'>";
        $strOnprog .= "  <h5 style='margin:0 0 5px 0; font-weight:bold;'><i class='fa fa-info-circle text-info'></i> Petunjuk Verifikasi Data (In-Page Audit):</h5>";
        $strOnprog .= "  <ul style='margin-bottom:0; padding-left:20px; font-size:12px;'>";
        $strOnprog .= "    <li>Klik pada <strong>Nomer GRN</strong> atau <strong>Nomer PO</strong> untuk membuka <em>modal drill-down rincian faktur dokumen asli</em>.</li>";
        $strOnprog .= "    <li>Klik pada nominal <strong>Nilai Klaim</strong> (yang bergaris bawah merah) untuk membuka <em>riwayat mutasi klaim pemotong sebelumnya</em>.</li>";
        $strOnprog .= "    <li>Kolom <strong>Status Validasi</strong> memeriksa keseimbangan matematis: <code>(Sisa Diskon + Nilai Klaim) == Diskon Supplier</code>.</li>";
        $strOnprog .= "  </ul>";
        $strOnprog .= "</div>";

        // Action Toolbar
        $strOnprog .= "<div id='batch_klaim_action_bar' style='margin-bottom: 12px; padding: 10px 15px; background: #ffffff; border: 1px solid #d2d6de; border-left: 4px solid #3c8dbc; border-radius: 3px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;'>";
        $strOnprog .= "  <div style='display: flex; align-items: center; gap: 15px; flex-wrap: wrap;'>";
        $strOnprog .= "    <span style='font-weight: bold; color: #333;'><i class='fa fa-check-square-o text-primary'></i> Terpilih: <span id='batch_selected_count' class='badge bg-blue' style='font-size: 13px; font-weight: bold;'>0</span> Nota</span>";
        $strOnprog .= "    <span style='color: #555;'>Total Sisa Diskon: <strong id='batch_selected_sisa_label' class='text-primary' style='font-size: 14px;'>Rp 0</strong></span>";
        $strOnprog .= "    <span id='batch_category_badge' style='display:none;'></span>";
        $strOnprog .= "  </div>";
        $strOnprog .= "  <div style='display: flex; gap: 8px;'>";
        $strOnprog .= "    <button type='button' id='btn_audit_selisih_all' class='btn btn-sm btn-danger' style='font-weight:bold; box-shadow: 0 1px 3px rgba(221,75,57,0.3);' onclick='lihatSemuaSelisih()'><i class='fa fa-exclamation-triangle'></i> Tampilkan Semua Selisih Validasi (Semua Supplier: <span id='btn_count_selisih_all'>" . (isset($countSelisihAll) ? $countSelisihAll : "...") . "</span>)</button>";
        $strOnprog .= "    <button type='button' id='btn_clear_batch_klaim' class='btn btn-sm btn-default' style='display:none;' onclick='clearAllKlaimSelection()'><i class='fa fa-times'></i> Batalkan Pilihan</button>";
        $strOnprog .= "  </div>";
        $strOnprog .= "</div>";

        // Filter Toolbar
        $strOnprog .= "<div id='klaim_diskon_filter_bar' class='klaim-filter-container'>";
        $strOnprog .= "  <div class='klaim-filter-row'>";
        $strOnprog .= "    <span class='klaim-filter-label'><i class='fa fa-filter text-muted'></i> Kategori:</span>";
        $strOnprog .= "    <button type='button' class='btn btn-xs btn-diskon-pill active' data-filter='all'>Semua <span class='badge' id='badge_cnt_all'>...</span></button>";
        $strOnprog .= "    <button type='button' class='btn btn-xs btn-diskon-pill' data-filter='reguler'><i class='fa fa-tags text-blue'></i> Hanya Diskon Reguler <span class='badge' id='badge_cnt_reguler'>...</span></button>";
        $strOnprog .= "    <button type='button' class='btn btn-xs btn-diskon-pill' data-filter='freeproduk'><i class='fa fa-gift text-yellow'></i> Hanya Free Produk <span class='badge' id='badge_cnt_freeproduk'>...</span></button>";
        $strOnprog .= "    <button type='button' class='btn btn-xs btn-diskon-pill' data-filter='kombinasi'><i class='fa fa-th-list text-purple'></i> Kombinasi <span class='badge' id='badge_cnt_kombinasi'>...</span></button>";
        $strOnprog .= "    <button type='button' class='btn btn-xs btn-diskon-pill btn-diskon-pill-selisih' data-filter='selisih' style='border-color:#dd4b39; color:#c9302c;'><i class='fa fa-exclamation-triangle text-red'></i> Status Selisih / Merah <span class='badge' style='background-color:#dd4b39; color:#fff;' id='badge_cnt_selisih'>" . (isset($countSelisihAll) ? $countSelisihAll : "...") . "</span></button>";
        $strOnprog .= "  </div>";
        $strOnprog .= "  <div class='klaim-filter-row'>";
        $strOnprog .= "    <span class='klaim-filter-label'><i class='fa fa-columns text-muted'></i> Kolom Diskon:</span>";
        $strOnprog .= "    <button type='button' class='btn btn-xs btn-diskon-pill' data-filter='d1'>Diskon 1 <span class='badge' id='badge_cnt_d1'>...</span></button>";
        $strOnprog .= "    <button type='button' class='btn btn-xs btn-diskon-pill' data-filter='d2'>Diskon 2 <span class='badge' id='badge_cnt_d2'>...</span></button>";
        $strOnprog .= "    <button type='button' class='btn btn-xs btn-diskon-pill' data-filter='d3'>Diskon 3 <span class='badge' id='badge_cnt_d3'>...</span></button>";
        $strOnprog .= "    <button type='button' class='btn btn-xs btn-diskon-pill' data-filter='d4'>Diskon 4 <span class='badge' id='badge_cnt_d4'>...</span></button>";
        $strOnprog .= "    <button type='button' class='btn btn-xs btn-diskon-pill' data-filter='d5'>Diskon 5 <span class='badge' id='badge_cnt_d5'>...</span></button>";
        $strOnprog .= "    <button type='button' class='btn btn-xs btn-diskon-pill' data-filter='d6'>Diskon 6 <span class='badge' id='badge_cnt_d6'>...</span></button>";
        $strOnprog .= "    <button type='button' class='btn btn-xs btn-diskon-pill' data-filter='d7'><i class='fa fa-gift text-yellow'></i> Free Produk (D7) <span class='badge' id='badge_cnt_d7'>...</span></button>";
        $strOnprog .= "  </div>";
        $strOnprog .= "</div>";

        // Table
        $strOnprog .= "<div class='table-responsive step_$stepper'>";
        $strOnprog .= "<table id='arrayOnProgress_step_$stepper' class='table datatables stripe compact nowarp order-column table-condensed table-bordered no-padding' style='border:solid #3c8dbc 0px; width: 100%;'>";
        $strOnprog .= "<thead>";
        $strOnprog .= "<tr class='text-uppercase'>";
        $strOnprog .= "<th style='width: 35px; text-align: center;' class='no-sort'><input type='checkbox' id='check_all_grn_klaim' onclick='event.stopPropagation();' title='Pilih Semua' style='cursor:pointer; transform: scale(1.2);'></th>";
        if (sizeof($arrayProgressLabels) > 0) {
            $strOnprog .= "<th class='no-sort' style='width: 35px; text-align: center;'>No.</th>";
            foreach ($arrayProgressLabels as $key => $label) {
                $sortClass = in_array($key, array('dtime', 'suppliers_nama', 'nomer', 'oleh_nama', 'keterangan')) ? "" : "no-sort";
                $strOnprog .= "<th class='$sortClass'>";
                if (is_array($label)) {
                    $strOnprog .= isset($label['label']) ? $label['label'] : "-";
                } else {
                    $strOnprog .= $label;
                }
                $strOnprog .= "</th>";
            }
        }
        $strOnprog .= "</tr>";
        $strOnprog .= "</thead>";

        $strOnprog .= "<tbody id='arrayOnProgress_tbody_$stepper'>";
        $strOnprog .= "</tbody>";

        $strOnprog .= "<tfoot>";
        $strOnprog .= "<tr>";
        $strOnprog .= "<th>-</th>";
        $strOnprog .= "<th class='text-center text-bold'>TOTAL</th>";
        if (sizeof($arrayProgressLabels) > 0) {
            foreach ($arrayProgressLabels as $key => $label) {
                $strOnprog .= "<th id='footer_col_" . $key . "' data-key='" . $key . "' class='text-right'>-</th>";
            }
        }
        $strOnprog .= "</tr>";
        $strOnprog .= "</tfoot>";
        $strOnprog .= "</table>";
        $strOnprog .= "</div>";

        // Modal 1: Audit Dokumen GRN/PO
        $strOnprog .= "
        <div class='modal fade' id='modalAuditGrn' tabindex='-1' role='dialog' aria-labelledby='modalAuditGrnLabel'>
          <div class='modal-dialog modal-lg' role='document' style='width:96%; max-width:1450px;'>
            <div class='modal-content'>
              <div class='modal-header bg-primary' style='padding:12px 18px;'>
                <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true' style='color:#fff;'>&times;</span></button>
                <h4 class='modal-title' id='modalAuditGrnLabel' style='color:#fff;'><i class='fa fa-file-text-o'></i> Audit Rincian Faktur Dokumen Asli (GRN & PO)</h4>
              </div>
              <div class='modal-body' id='modalAuditGrnBody' style='padding:18px;'>
                <div class='text-center' style='padding:30px;'><i class='fa fa-spinner fa-spin fa-2x text-primary'></i><br>Memuat data dokumen...</div>
              </div>
              <div class='modal-footer' style='padding:10px 18px;'>
                <button type='button' class='btn btn-default' data-dismiss='modal'><i class='fa fa-times'></i> Tutup</button>
              </div>
            </div>
          </div>
        </div>
        ";

        // Modal 2: Audit Mutasi Klaim
        $strOnprog .= "
        <div class='modal fade' id='modalAuditMutasi' tabindex='-1' role='dialog' aria-labelledby='modalAuditMutasiLabel'>
          <div class='modal-dialog modal-lg' role='document' style='width:80%; max-width:1000px;'>
            <div class='modal-content'>
              <div class='modal-header bg-navy' style='padding:12px 18px; color:#fff;'>
                <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true' style='color:#fff;'>&times;</span></button>
                <h4 class='modal-title' id='modalAuditMutasiLabel'><i class='fa fa-history text-yellow'></i> Audit Jejak Mutasi Rekening Piutang Supplier (Akun 1010020030)</h4>
              </div>
              <div class='modal-body' id='modalAuditMutasiBody' style='padding:18px;'>
                <div class='text-center' style='padding:30px;'><i class='fa fa-spinner fa-spin fa-2x text-primary'></i><br>Memuat riwayat mutasi...</div>
              </div>
              <div class='modal-footer' style='padding:10px 18px;'>
                <button type='button' class='btn btn-default' data-dismiss='modal'><i class='fa fa-times'></i> Tutup</button>
              </div>
            </div>
          </div>
        </div>
        ";

        // Styling
        $strOnprog .= "<style>
            #arrayOnProgress_step_" . $stepper . "_wrapper {
                position: relative;
                min-height: 220px;
            }
            #arrayOnProgress_step_" . $stepper . "_wrapper .dataTables_processing {
                position: absolute !important;
                top: 36px !important;
                left: 0 !important;
                width: 100% !important;
                height: calc(100% - 36px) !important;
                min-height: 180px !important;
                margin: 0 !important;
                padding: 0 !important;
                background: rgba(255, 255, 255, 0.72) !important;
                backdrop-filter: blur(2px) !important;
                -webkit-backdrop-filter: blur(2px) !important;
                border: none !important;
                box-shadow: none !important;
                z-index: 1050 !important;
                text-align: left !important;
            }
            .dt-modern-loader-card {
                position: absolute;
                top: 75px;
                left: 50%;
                transform: translateX(-50%);
                display: inline-flex;
                align-items: center;
                gap: 14px;
                padding: 12px 24px;
                background: #ffffff;
                border-radius: 50px;
                box-shadow: 0 10px 28px rgba(0, 0, 0, 0.12), 0 2px 6px rgba(0, 0, 0, 0.04);
                border: 1px solid rgba(0, 0, 0, 0.08);
                white-space: nowrap;
            }
            .dt-spinner-circle {
                width: 34px;
                height: 34px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #e8f5e9;
                border-radius: 50%;
                flex-shrink: 0;
            }
            .dt-spinner-circle i {
                font-size: 18px;
                color: #3c8dbc;
            }
            .dt-loader-text {
                display: flex;
                flex-direction: column;
                text-align: left;
            }
            .dt-loader-title {
                font-size: 13px;
                font-weight: 700;
                color: #2c3e50;
                line-height: 1.2;
            }
            .dt-loader-sub {
                font-size: 11px;
                color: #8c9ba5;
                margin-top: 2px;
                line-height: 1.2;
            }
            #arrayOnProgress_step_" . $stepper . " tfoot th,
            #arrayOnProgress_step_" . $stepper . " tfoot td,
            #arrayOnProgress_step_" . $stepper . "_wrapper .dataTables_scrollFoot tfoot th {
                background-color: #d2d6de !important;
                color: #333333 !important;
                border-top: 2px solid #b5bbc8 !important;
                border-bottom: 2px solid #b5bbc8 !important;
                font-weight: bold !important;
                vertical-align: middle !important;
            }
            #arrayOnProgress_step_" . $stepper . "_wrapper .dt-buttons .btn {
                background-image: none !important;
                box-shadow: none !important;
            }
            #arrayOnProgress_step_" . $stepper . "_wrapper .dt-buttons .btn-filter-sisa.active {
                background-color: #00a65a !important;
                border-color: #008d4c !important;
                color: #ffffff !important;
                font-weight: bold !important;
            }
            #arrayOnProgress_step_" . $stepper . "_wrapper .dt-buttons .btn-filter-all.active {
                background-color: #3c8dbc !important;
                border-color: #367fa9 !important;
                color: #ffffff !important;
                font-weight: bold !important;
            }
            .klaim-filter-container {
                margin-bottom: 12px;
                padding: 10px 14px;
                background: #ffffff;
                border: 1px solid #d2d6de;
                border-radius: 4px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            }
            .klaim-filter-row {
                display: flex;
                align-items: center;
                gap: 6px;
                flex-wrap: wrap;
            }
            .klaim-filter-row + .klaim-filter-row {
                margin-top: 8px;
                padding-top: 8px;
                border-top: 1px dashed #e4e7eb;
            }
            .klaim-filter-label {
                font-size: 11px;
                font-weight: bold;
                color: #555;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-right: 4px;
            }
            .btn-diskon-pill {
                border-radius: 14px !important;
                padding: 3px 10px !important;
                font-size: 11.5px !important;
                font-weight: 600 !important;
                transition: all 0.2s ease !important;
                border: 1px solid #d2d6de !important;
                background: #fdfdfd !important;
                color: #444 !important;
            }
            .btn-diskon-pill:hover {
                background: #f4f6f9 !important;
                border-color: #3c8dbc !important;
                color: #111 !important;
            }
            .btn-diskon-pill.active {
                background: #3c8dbc !important;
                border-color: #2e6da4 !important;
                color: #ffffff !important;
            }
            .btn-diskon-pill.active i {
                color: #ffffff !important;
            }
            .btn-diskon-pill .badge {
                font-size: 10.5px !important;
                font-weight: bold !important;
                padding: 2px 6px !important;
                border-radius: 10px !important;
                margin-left: 4px !important;
                background-color: #777;
                color: #fff;
            }
            .btn-diskon-pill.active .badge {
                background-color: #ffffff !important;
                color: #3c8dbc !important;
            }
        </style>";

        // JavaScript
        $strOnprog .= "<script>
            window.selectedKlaimGrnMap = window.selectedKlaimGrnMap || {};
            window.klaimFilterMode = '" . (isset($initialFilterMode) ? $initialFilterMode : 'sisa') . "';
            window.klaimDiskonType = window.klaimDiskonType || 'all';
            window.latestGlobalSum = {};
            window.latestPageSum = {};

            window.lihatSemuaSelisih = function() {
                window.selectedKlaimGrnMap = {};
                window.klaimFilterMode = 'selisih';
                window.klaimDiskonType = 'all';

                var currentSid = " . intval($pihakDataSelected) . ";
                if (currentSid > 0) {
                    var linkAll = '" . $linkDataSelected . "?sid=0&filterMode=selisih';
                    if ($('#klaimList .box').length) {
                        $('#klaimList .box').append('<div class=\"overlay\"><i class=\"fa fa-refresh fa-spin text-danger\"></i></div>');
                    }
                    $('#klaimList').load(linkAll);
                } else {
                    $('.btn-filter-selisih').addClass('btn-danger active').removeClass('btn-default');
                    $('.btn-filter-sisa').addClass('btn-default').removeClass('btn-success active');
                    $('.btn-filter-all').addClass('btn-default').removeClass('btn-primary active');
                    $('.btn-diskon-pill').removeClass('active');
                    $('.btn-diskon-pill-selisih').addClass('active');
                    if (window.tableKlaim) {
                        window.tableKlaim.ajax.reload();
                    }
                }
            };

            window.bukaAuditGrn = function(grnId) {
                $('#modalAuditGrnBody').html('<div class=\"text-center\" style=\"padding:30px;\"><i class=\"fa fa-refresh fa-spin fa-2x text-primary\"></i><br>Mengambil rincian faktur dokumen GRN #' + grnId + '...</div>');
                $('#modalAuditGrn').modal('show');
                $('#modalAuditGrnBody').load('" . MODUL_PATH . "AuditKlaimSupplier/previewGrnDetailAjax/" . $jenisTr . "/' + grnId);
            };

            window.bukaAuditMutasi = function(grnId) {
                $('#modalAuditMutasiBody').html('<div class=\"text-center\" style=\"padding:30px;\"><i class=\"fa fa-refresh fa-spin fa-2x text-navy\"></i><br>Mengambil riwayat mutasi klaim pemotong #' + grnId + '...</div>');
                $('#modalAuditMutasi').modal('show');
                $('#modalAuditMutasiBody').load('" . MODUL_PATH . "AuditKlaimSupplier/previewMutasiKlaimAjax/" . $jenisTr . "/' + grnId);
            };

            function updateDiskonBadges(b) {
                if (!b) return;
                $('#badge_cnt_all').text(b.all !== undefined ? b.all : 0);
                $('#badge_cnt_reguler').text(b.reguler !== undefined ? b.reguler : 0);
                $('#badge_cnt_freeproduk').text(b.freeproduk !== undefined ? b.freeproduk : 0);
                $('#badge_cnt_kombinasi').text(b.kombinasi !== undefined ? b.kombinasi : 0);
                if (b.selisih !== undefined) {
                    $('#btn_count_selisih').text(b.selisih);
                }
                if (b.selisih_all !== undefined) {
                    $('#btn_count_selisih_all').text(b.selisih_all);
                    $('#badge_cnt_selisih').text(b.selisih_all);
                }

                $('#badge_cnt_d1').text(b.d1 !== undefined ? b.d1 : 0);
                $('#badge_cnt_d2').text(b.d2 !== undefined ? b.d2 : 0);
                $('#badge_cnt_d3').text(b.d3 !== undefined ? b.d3 : 0);
                $('#badge_cnt_d4').text(b.d4 !== undefined ? b.d4 : 0);
                $('#badge_cnt_d5').text(b.d5 !== undefined ? b.d5 : 0);
                $('#badge_cnt_d6').text(b.d6 !== undefined ? b.d6 : 0);
                $('#badge_cnt_d7').text(b.d7 !== undefined ? b.d7 : 0);

                $('.btn-diskon-pill').each(function() {
                    var f = $(this).attr('data-filter');
                    var cnt = 0;
                    if (f === 'all') cnt = b.all || 0;
                    else if (f === 'reguler') cnt = b.reguler || 0;
                    else if (f === 'freeproduk') cnt = b.freeproduk || 0;
                    else if (f === 'kombinasi') cnt = b.kombinasi || 0;
                    else if (f === 'selisih') cnt = b.selisih_all || b.selisih || 0;
                    else if (b[f] !== undefined) cnt = b[f] || 0;

                    if (cnt === 0 && f !== 'all') {
                        $(this).css('opacity', '0.45');
                    } else {
                        $(this).css('opacity', '1');
                    }
                });
            }

            function getActiveKlaimStats() {
                var count = 0;
                var totalSisa = 0;
                var countFP = 0;
                var countReg = 0;
                var suppliers = {};
                for (var gid in window.selectedKlaimGrnMap) {
                    if (window.selectedKlaimGrnMap.hasOwnProperty(gid) && window.selectedKlaimGrnMap[gid].selected) {
                        count++;
                        var item = window.selectedKlaimGrnMap[gid];
                        totalSisa += (parseFloat(item.sisa) || 0);
                        if (item.category === 'freeproduk' || (item.hasFreeProduk > 0 && item.hasReguler == 0)) {
                            countFP++;
                        } else {
                            countReg++;
                        }
                        if (item.supplierId) {
                            suppliers[item.supplierId] = item.supplierNama || item.supplierId;
                        }
                    }
                }
                var suppKeys = Object.keys(suppliers);
                return {
                    count: count,
                    totalSisa: Math.round(totalSisa),
                    countFP: countFP,
                    countReg: countReg,
                    supplierCount: suppKeys.length,
                    firstSupplierNama: (suppKeys.length > 0 ? suppliers[suppKeys[0]] : '')
                };
            }

            function updateBatchActionBar() {
                var stats = getActiveKlaimStats();
                $('#batch_selected_count').text(stats.count);
                $('#batch_selected_sisa_label').text('Rp ' + addCommas(Math.round(stats.totalSisa)));

                if (stats.count > 0) {
                    var badgeHtml = '';
                    if (stats.countFP > 0 && stats.countReg > 0) {
                        badgeHtml = '<span class=\"badge bg-purple\" style=\"font-size:12px; padding:4px 8px;\"><i class=\"fa fa-th-list\"></i> Campur (' + stats.countReg + ' Reguler, ' + stats.countFP + ' Free Produk)</span>';
                    } else if (stats.countFP > 0) {
                        badgeHtml = '<span class=\"badge bg-yellow\" style=\"font-size:12px; padding:4px 8px;\"><i class=\"fa fa-gift\"></i> ' + stats.countFP + ' Free Produk (Diskon 7)</span>';
                    } else if (stats.countReg > 0) {
                        badgeHtml = '<span class=\"badge bg-blue\" style=\"font-size:12px; padding:4px 8px;\"><i class=\"fa fa-tags\"></i> ' + stats.countReg + ' Diskon Reguler</span>';
                    }
                    if (stats.supplierCount > 1) {
                        badgeHtml += ' <span class=\"badge bg-gray\" style=\"font-size:11px; padding:4px 8px; color:#333;\"><i class=\"fa fa-truck\"></i> ' + stats.supplierCount + ' Supplier</span>';
                    }
                    $('#batch_category_badge').html(badgeHtml).show();
                    $('#btn_clear_batch_klaim').show();
                } else {
                    $('#batch_category_badge').hide();
                    $('#btn_clear_batch_klaim').hide();
                }
            }

            function syncCheckboxUI() {
                $('.check_grn_klaim').each(function() {
                    var el = $(this);
                    var gid = el.val();
                    var isSelected = (window.selectedKlaimGrnMap[gid] && window.selectedKlaimGrnMap[gid].selected);
                    var sisa = parseFloat(el.attr('data-sisa')) || 0;

                    el.prop('checked', isSelected ? true : false);

                    if (sisa <= 0) {
                        el.prop('disabled', true).css('opacity', '0.35').attr('title', 'Sisa diskon 0 (sudah diklaim penuh)');
                    } else {
                        el.prop('disabled', false).css('opacity', '1').attr('title', 'Pilih nota ini');
                    }
                });

                var enabledCbs = $('.check_grn_klaim:not(:disabled)');
                if (enabledCbs.length > 0) {
                    var allChecked = true;
                    enabledCbs.each(function() {
                        if (!$(this).prop('checked')) {
                            allChecked = false;
                            return false;
                        }
                    });
                    $('#check_all_grn_klaim').prop('checked', allChecked);
                } else {
                    $('#check_all_grn_klaim').prop('checked', false);
                }
            }

            function clearAllKlaimSelection() {
                window.selectedKlaimGrnMap = {};
                $('.check_grn_klaim').prop('checked', false);
                $('#check_all_grn_klaim').prop('checked', false);
                syncCheckboxUI();
                updateBatchActionBar();
            }

            function updateFooterSums(pageSum, globalSum) {
                pageSum = pageSum || {};
                globalSum = globalSum || {};
                $('#arrayOnProgress_step_" . $stepper . " tfoot th[data-key]').each(function() {
                    var key = $(this).attr('data-key');
                    var pVal = (pageSum[key] !== undefined && pageSum[key] !== null) ? parseFloat(pageSum[key]) : null;
                    var gVal = (globalSum[key] !== undefined && globalSum[key] !== null) ? parseFloat(globalSum[key]) : null;

                    if ((pVal !== null && pVal > 0) || (gVal !== null && gVal > 0)) {
                        var html = '';
                        if (pVal !== null && pVal > 0) {
                            html += '<div class=\"text-right text-primary text-bold\" style=\"font-size:11px;\" title=\"Total Halaman Ini\">Hal: ' + addCommas(Math.round(pVal)) + '</div>';
                        }
                        if (gVal !== null && gVal > 0) {
                            html += '<div class=\"text-right text-success text-bold\" style=\"font-size:11px;\" title=\"Total Seluruh Data\">Global: ' + addCommas(Math.round(gVal)) + '</div>';
                        }
                        if (html === '') {
                            html = '-';
                        }
                        $(this).html(html);
                    } else {
                        $(this).html('-');
                    }
                });
            }

            $(document).ready(function() {
                $.fn.dataTable.ext.errMode = 'none';
                $('#arrayOnProgress_step_$stepper').on('error.dt', function(e, settings, techNote, message) {
                    console.warn('DataTables warning suppressed:', message);
                });

                var table = $('#arrayOnProgress_step_$stepper').DataTable({
                    dom: 'lBfrtip',
                    searchDelay: 600,
                    fixedHeader: true,
                    ordering: true,
                    order: [[ 2, 'desc' ]],
                    columnDefs: [
                        { orderable: false, targets: 'no-sort' }
                    ],
                    searching: true,
                    serverSide: true,
                    processing: true,
                    language: {
                        processing: '<div class=\"dt-modern-loader-card\">' +
                                    '  <div class=\"dt-spinner-circle\">' +
                                    '    <i class=\"fa fa-circle-o-notch fa-spin\"></i>' +
                                    '  </div>' +
                                    '  <div class=\"dt-loader-text\">' +
                                    '    <span class=\"dt-loader-title\">Memuat Data Audit...</span>' +
                                    '    <span class=\"dt-loader-sub\"><i class=\"fa fa-database text-muted\"></i> Mengambil data dari server</span>' +
                                    '  </div>' +
                                    '</div>'
                    },
                    lengthMenu: [ [10, 20, 50, 100], [10, 20, 50, 100] ],
                    pageLength: 10,
                    ajax: {
                        url: '$ajaxDataUrl',
                        type: 'GET',
                        data: function(d) {
                            d.sid = '$pihakDataSelected';
                            d.filterMode = window.klaimFilterMode || 'sisa';
                            d.diskonFilter = window.klaimDiskonType || 'all';
                            if (d.search && d.search.value) {
                                try {
                                    d.search_b64 = btoa(unescape(encodeURIComponent(d.search.value)));
                                } catch(e) {
                                    d.search_b64 = '';
                                }
                            }
                        },
                        dataSrc: function(json) {
                            if (json.countSisa !== undefined) {
                                $('#btn_count_sisa').text(json.countSisa);
                            }
                            if (json.countTotal !== undefined) {
                                $('#btn_count_total').text(json.countTotal);
                            }
                            if (json.diskonBadges) {
                                updateDiskonBadges(json.diskonBadges);
                            }
                            window.latestGlobalSum = json.sumFooterGlobal || {};
                            window.latestPageSum = json.sumFooterPage || {};
                            return json.data;
                        }
                    },
                    buttons: [
                        {
                            text: '<i class=\"fa fa-filter\"></i> Siap Diklaim (<span id=\"btn_count_sisa\">...</span>)',
                            className: 'btn btn-xs ' + (window.klaimFilterMode === 'sisa' ? 'btn-success active' : 'btn-default') + ' btn-filter-sisa',
                            action: function ( e, dt, node, config ) {
                                window.klaimFilterMode = 'sisa';
                                $('.btn-filter-sisa').addClass('btn-success active').removeClass('btn-default');
                                $('.btn-filter-all').addClass('btn-default').removeClass('btn-primary active');
                                $('.btn-filter-selisih').addClass('btn-default').removeClass('btn-danger active');
                                dt.ajax.reload();
                            }
                        },
                        {
                            text: '<i class=\"fa fa-list\"></i> Tampilkan Semua Data (<span id=\"btn_count_total\">...</span>)',
                            className: 'btn btn-xs ' + (window.klaimFilterMode === 'all' ? 'btn-primary active' : 'btn-default') + ' btn-filter-all',
                            action: function ( e, dt, node, config ) {
                                window.klaimFilterMode = 'all';
                                $('.btn-filter-all').addClass('btn-primary active').removeClass('btn-default');
                                $('.btn-filter-sisa').addClass('btn-default').removeClass('btn-success active');
                                $('.btn-filter-selisih').addClass('btn-default').removeClass('btn-danger active');
                                dt.ajax.reload();
                            }
                        },
                        {
                            text: '<i class=\"fa fa-exclamation-triangle\"></i> Hanya Data Selisih (<span id=\"btn_count_selisih\">...</span>)',
                            className: 'btn btn-xs ' + (window.klaimFilterMode === 'selisih' ? 'btn-danger active' : 'btn-default') + ' btn-filter-selisih',
                            action: function ( e, dt, node, config ) {
                                window.klaimFilterMode = 'selisih';
                                $('.btn-filter-selisih').addClass('btn-danger active').removeClass('btn-default');
                                $('.btn-filter-sisa').addClass('btn-default').removeClass('btn-success active');
                                $('.btn-filter-all').addClass('btn-default').removeClass('btn-primary active');
                                dt.ajax.reload();
                            }
                        }
                    ],
                    drawCallback: function(settings) {
                        syncCheckboxUI();
                        updateFooterSums(window.latestPageSum, window.latestGlobalSum);
                    }
                });

                window.tableKlaim = table;

                $('.btn-diskon-pill').on('click', function() {
                    var filterType = $(this).attr('data-filter');
                    $('.btn-diskon-pill').removeClass('active');
                    $(this).addClass('active');
                    if (filterType === 'selisih') {
                        window.klaimFilterMode = 'selisih';
                        window.klaimDiskonType = 'all';
                        $('.btn-filter-selisih').addClass('btn-danger active').removeClass('btn-default');
                        $('.btn-filter-sisa').addClass('btn-default').removeClass('btn-success active');
                        $('.btn-filter-all').addClass('btn-default').removeClass('btn-primary active');
                    } else {
                        if (window.klaimFilterMode === 'selisih') {
                            window.klaimFilterMode = 'sisa';
                            $('.btn-filter-sisa').addClass('btn-success active').removeClass('btn-default');
                            $('.btn-filter-selisih').addClass('btn-default').removeClass('btn-danger active');
                        }
                        window.klaimDiskonType = filterType;
                    }
                    table.ajax.reload();
                });

                $(document).on('change', '.check_grn_klaim', function() {
                    var el = $(this);
                    var gid = el.val();
                    var isChecked = el.is(':checked');
                    var sisa = parseFloat(el.attr('data-sisa')) || 0;

                    if (!window.selectedKlaimGrnMap[gid]) {
                        window.selectedKlaimGrnMap[gid] = {};
                    }
                    window.selectedKlaimGrnMap[gid].selected = isChecked;
                    window.selectedKlaimGrnMap[gid].sisa = sisa;
                    window.selectedKlaimGrnMap[gid].nomer = el.attr('data-nomer');
                    window.selectedKlaimGrnMap[gid].supplierId = el.attr('data-supplier-id');
                    window.selectedKlaimGrnMap[gid].supplierNama = el.attr('data-supplier-nama');
                    window.selectedKlaimGrnMap[gid].hasFreeProduk = el.attr('data-freeproduk');
                    window.selectedKlaimGrnMap[gid].hasReguler = el.attr('data-reguler');

                    syncCheckboxUI();
                    updateBatchActionBar();
                });

                $('#check_all_grn_klaim').on('change', function() {
                    var isChecked = $(this).is(':checked');
                    $('.check_grn_klaim:not(:disabled)').each(function() {
                        var el = $(this);
                        var gid = el.val();
                        var sisa = parseFloat(el.attr('data-sisa')) || 0;

                        if (!window.selectedKlaimGrnMap[gid]) {
                            window.selectedKlaimGrnMap[gid] = {};
                        }
                        window.selectedKlaimGrnMap[gid].selected = isChecked;
                        window.selectedKlaimGrnMap[gid].sisa = sisa;
                        window.selectedKlaimGrnMap[gid].nomer = el.attr('data-nomer');
                        window.selectedKlaimGrnMap[gid].supplierId = el.attr('data-supplier-id');
                        window.selectedKlaimGrnMap[gid].supplierNama = el.attr('data-supplier-nama');
                        window.selectedKlaimGrnMap[gid].hasFreeProduk = el.attr('data-freeproduk');
                        window.selectedKlaimGrnMap[gid].hasReguler = el.attr('data-reguler');
                    });
                    syncCheckboxUI();
                    updateBatchActionBar();
                });
            });
            $('.selectpicker').selectpicker();
        </script>";

        $strOnprog .= "</div>";
        $strOnprog .= "</div>";

        $str .= $strOnprog;
        echo $str;
        break;
}
// END OF COMPLETE REPEATED LOGIC

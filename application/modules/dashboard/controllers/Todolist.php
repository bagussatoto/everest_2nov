<?php
// START OF COMPLETE REPEATED LOGIC
/**
 * Controller Todolist
 * Modul Dashboard - Everest ERP
 *
 * Dioptimalkan untuk menampilkan Widget "To Do List Transaksi" modern
 * dengan kartu tugas responsif, tab kategori divisi, indikator umur dokumen (aging),
 * dan alert kepatuhan regulasi (Batas Lapor Pajak DJP).
 *
 * Kompatibilitas: PHP 5.6 Native, CodeIgniter 3.1.8, AdminLTE 2.3.11
 */

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class Todolist extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!isset($this->session->login['id'])) {
            gotoLogin();
        }
        validateUserSession($this->session->login['id']);
        session_write_close();
    }

    /**
     * Memformat teks label menjadi Title Case yang rapi dan mempertahankan akronim bisnis penting
     *
     * @param string $rawLabel
     * @return string
     */
    private function formatTodoTitle($rawLabel)
    {
        $clean = trim(str_replace('::', '', $rawLabel));
        $words = explode(' ', strtolower($clean));
        $acronyms = array('fg', 'ppn', 'po', 'grn', 'djp', 'so', 'pbis', 'pph21', 'pph', 'spv', 'id', 'e-faktur', 'pos');
        $formatted = array();
        foreach ($words as $w) {
            $w = trim($w);
            if ($w === '') {
                continue;
            }
            if (in_array($w, $acronyms)) {
                $formatted[] = ($w === 'e-faktur') ? 'E-Faktur' : strtoupper($w);
            } else {
                $formatted[] = ucfirst($w);
            }
        }
        return implode(' ', $formatted);
    }

    /**
     * Menampilkan widget To Do List Transaksi dalam tampilan Card Grid modern dan Tab Kategori
     */
    public function viewTodolistTransaksi()
    {
        $myId = my_id();

        $this->load->helper("he_access_right");
        $this->load->config("heTransaksi_ui");
        $configUi = $this->config->item("heTransaksi_ui");
        if (!is_array($configUi)) {
            $configUi = array();
        }

        // Ambil hak akses transaksi yang diizinkan untuk pengguna saat ini
        $src_access = alowedAccess_he_access_right($myId, $configUi);

        $aliasing_master = array();
        $modul_master = array();
        $icon_master = array();
        $alias_steps = array();
        $modul_steps = array();

        foreach ($configUi as $jmaster => $item) {
            $modul = isset($item['modul']) ? $item['modul'] : '';
            $label_parent = isset($item['label']) ? $item['label'] : $jmaster;
            $aliasing_master[$jmaster] = $label_parent;
            $modul_master[$jmaster] = $modul;
            $icon_master[$jmaster] = isset($item['icon']) ? $item['icon'] : 'fa-folder-open-o';

            $steps = isset($item['steps']) ? $item['steps'] : array();
            foreach ($steps as $step => $step_params) {
                $source = isset($step_params['source']) ? $step_params['source'] : '';
                $label_step = isset($step_params['label']) ? $step_params['label'] : '';
                if (!empty($source)) {
                    $alias_steps[$source] = $label_step;
                    $modul_steps[$source] = $modul;
                }
            }
        }

        $hak_master_jenis = array();
        $hak_jenis = array();
        $hak_jenis_master = array();

        if (is_array($src_access) && sizeof($src_access) > 0) {
            foreach ($src_access as $mJenis => $src_accesies) {
                foreach ($src_accesies as $src_accesy) {
                    foreach ($src_accesy as $ac_jenis => $item) {
                        $hak_jenis_master[$mJenis][] = $ac_jenis;
                        $hak_jenis[] = $ac_jenis;
                        $hak_master_jenis[$ac_jenis] = $mJenis;
                    }
                }
            }
        }
        $hak_master_jenis["111r"] = 111;

        // Panggil transaksi gantung dari model MdlTransaksi
        $this->load->model("MdlTransaksi");
        $tr = new MdlTransaksi();
        $jenis_gantungs = $tr->callGantunganTransaksi(false);

        // Pemetaan kategori departemen standar
        $categoryMap = array(
            // Pajak & Keuangan
            '110'   => 'tax',
            '111'   => 'tax',
            '115'   => 'tax',
            '117'   => 'tax',
            '1155'  => 'tax',
            '1674'  => 'tax',
            '11674' => 'tax',
            '21674' => 'tax',
            '7674'  => 'tax',
            '1757'  => 'tax',
            '2675'  => 'tax',
            '672'   => 'tax',
            '3674'  => 'tax',
            'pbis'  => 'tax',
            'kas'   => 'tax',
            'bank'  => 'tax',
            'rekening' => 'tax',

            // Purchasing & Pengadaan
            '461'   => 'purchasing',
            '462'   => 'purchasing',
            '463'   => 'purchasing',
            '464'   => 'purchasing',
            '465'   => 'purchasing',
            '466'   => 'purchasing',
            '467'   => 'purchasing',
            '1466'  => 'purchasing',
            '3463'  => 'purchasing',
            '3465'  => 'purchasing',
            '4464'  => 'purchasing',
            '4467'  => 'purchasing',
            '9467'  => 'purchasing',
            '19467' => 'purchasing',
            '7467'  => 'purchasing',
            '7762'  => 'purchasing',

            // Logistik & Distribusi
            '583'   => 'logistik',
            '585'   => 'logistik',
            '588'   => 'logistik',
            '5822'  => 'logistik',
            '5823'  => 'logistik',
            '5833'  => 'logistik',
            '5855'  => 'logistik',
            '9833'  => 'logistik',
            '9855'  => 'logistik',
            '9834'  => 'logistik',
            '1985'  => 'logistik',
            '2985'  => 'logistik',
            '382'   => 'logistik',
            '582'   => 'logistik',
            '982'   => 'logistik',

            // Gudang & Inventori
            '1118'  => 'inventory',
            '1119'  => 'inventory',
            '2228'  => 'inventory',
            '2229'  => 'inventory',
            '3344'  => 'inventory',
            '8786'  => 'inventory',
            '8788'  => 'inventory',
            '8789'  => 'inventory',
            '2677'  => 'inventory',
            '1677'  => 'inventory',
            '675'   => 'inventory',
            '677'   => 'inventory',
            '757'   => 'inventory',
            '758'   => 'inventory',
            '759'   => 'inventory',
            '7761'  => 'inventory',
            '1961'  => 'inventory',
        );

        // Metadata tab kategori
        $categoryMeta = array(
            'all' => array(
                'label' => 'Semua Tugas',
                'icon'  => 'fa-th-large',
                'color' => 'bg-purple',
                'textColor' => 'text-purple',
            ),
            'tax' => array(
                'label' => 'Pajak & Keuangan',
                'icon'  => 'fa-calculator',
                'color' => 'bg-red',
                'textColor' => 'text-red',
            ),
            'purchasing' => array(
                'label' => 'Purchasing',
                'icon'  => 'fa-shopping-cart',
                'color' => 'bg-orange',
                'textColor' => 'text-orange',
            ),
            'logistik' => array(
                'label' => 'Logistik & Distribusi',
                'icon'  => 'fa-truck',
                'color' => 'bg-blue',
                'textColor' => 'text-blue',
            ),
            'inventory' => array(
                'label' => 'Gudang & Inventori',
                'icon'  => 'fa-cubes',
                'color' => 'bg-green',
                'textColor' => 'text-green',
            ),
            'lainnya' => array(
                'label' => 'Operasional Lainnya',
                'icon'  => 'fa-tasks',
                'color' => 'bg-navy',
                'textColor' => 'text-navy',
            ),
        );

        // Agregasi data transaksi gantung berdasarkan master jenis
        $masterData = array();
        $nowTime = time();

        foreach ($hak_master_jenis as $jenis_tr => $jenis_master) {
            if (isset($jenis_gantungs[$jenis_tr]) && is_array($jenis_gantungs[$jenis_tr])) {
                $items = $jenis_gantungs[$jenis_tr];
                $jml = sizeof($items);
                if ($jml > 0) {
                    if (!isset($masterData[$jenis_master])) {
                        $masterData[$jenis_master] = array(
                            'total_qty'     => 0,
                            'oldest_dtime'  => '',
                            'substeps'      => array(),
                        );
                    }
                    $masterData[$jenis_master]['total_qty'] += $jml;
                    $masterData[$jenis_master]['substeps'][$jenis_tr] = $jml;

                    foreach ($items as $itemRow) {
                        if (isset($itemRow->dtime) && !empty($itemRow->dtime)) {
                            $dtimeVal = $itemRow->dtime;
                            if (empty($masterData[$jenis_master]['oldest_dtime']) || $dtimeVal < $masterData[$jenis_master]['oldest_dtime']) {
                                $masterData[$jenis_master]['oldest_dtime'] = $dtimeVal;
                            }
                        }
                    }
                }
            }
        }

        // Susun daftar kartu tugas
        $cardsList = array();
        $categoryCounts = array(
            'all'        => 0,
            'tax'        => 0,
            'purchasing' => 0,
            'logistik'   => 0,
            'inventory'  => 0,
            'lainnya'    => 0,
        );
        $urgencyCounts = array(
            'danger'  => 0,
            'warning' => 0,
            'info'    => 0,
        );

        foreach ($masterData as $jenis_master => $itemData) {
            $qty = $itemData['total_qty'];
            if ($qty <= 0) {
                continue;
            }

            // Hitung umur dokumen tertua (aging dalam hari)
            $days_pending = 0;
            $oldestFormatted = '-';
            if (!empty($itemData['oldest_dtime'])) {
                $oldestTs = strtotime($itemData['oldest_dtime']);
                if ($oldestTs > 0 && $nowTime > $oldestTs) {
                    $days_pending = (int)floor(($nowTime - $oldestTs) / 86400);
                    $oldestFormatted = date('d M Y H:i', $oldestTs);
                }
            }

            // Tentukan tingkat urgensi berdasarkan Threshold SOP
            // Kritis (Danger): Dokumen >= 50 ATAU Umur >= 14 hari
            // Perhatian (Warning): Dokumen >= 10 ATAU Umur >= 7 hari
            // Normal (Info): Dokumen < 10 DAN Umur < 7 hari
            if ($qty >= 50 || $days_pending >= 14) {
                $urgencyLevel = 'danger';
                $urgencyRank = 3;
                $badgeClass = 'label-danger';
                $btnClass = 'btn-danger';
                $agingClass = 'text-danger text-bold';
            } elseif ($qty >= 10 || $days_pending >= 7) {
                $urgencyLevel = 'warning';
                $urgencyRank = 2;
                $badgeClass = 'label-warning';
                $btnClass = 'btn-warning';
                $agingClass = 'text-yellow text-bold';
            } else {
                $urgencyLevel = 'info';
                $urgencyRank = 1;
                $badgeClass = 'label-info';
                $btnClass = 'btn-info';
                $agingClass = 'text-muted';
            }

            $urgencyCounts[$urgencyLevel] += $qty;

            // Tentukan kategori
            $cat = isset($categoryMap[$jenis_master]) ? $categoryMap[$jenis_master] : '';
            if (empty($cat)) {
                $modulCheck = isset($modul_master[$jenis_master]) ? strtolower($modul_master[$jenis_master]) : '';
                $labelCheck = isset($aliasing_master[$jenis_master]) ? strtolower($aliasing_master[$jenis_master]) : '';
                if (strpos($modulCheck, 'tax') !== false || strpos($modulCheck, 'faktur') !== false || strpos($modulCheck, 'keuangan') !== false || strpos($labelCheck, 'pajak') !== false || strpos($labelCheck, 'ppn') !== false) {
                    $cat = 'tax';
                } elseif (strpos($modulCheck, 'purchas') !== false || strpos($modulCheck, 'beli') !== false || strpos($labelCheck, 'purchas') !== false || strpos($labelCheck, 'pengadaan') !== false) {
                    $cat = 'purchasing';
                } elseif (strpos($modulCheck, 'distribusi') !== false || strpos($modulCheck, 'logistik') !== false || strpos($modulCheck, 'kirim') !== false || strpos($labelCheck, 'distribusi') !== false) {
                    $cat = 'logistik';
                } elseif (strpos($modulCheck, 'opname') !== false || strpos($modulCheck, 'stok') !== false || strpos($modulCheck, 'gudang') !== false || strpos($labelCheck, 'opname') !== false || strpos($labelCheck, 'stok') !== false) {
                    $cat = 'inventory';
                } else {
                    $cat = 'lainnya';
                }
            }

            $categoryCounts['all'] += $qty;
            if (isset($categoryCounts[$cat])) {
                $categoryCounts[$cat] += $qty;
            }

            // Format judul dan modul
            $rawLabel = isset($aliasing_master[$jenis_master]) ? $aliasing_master[$jenis_master] : $jenis_master;
            $cleanTitle = $this->formatTodoTitle($rawLabel);
            $modul = isset($modul_master[$jenis_master]) ? $modul_master[$jenis_master] : '';
            $link_transaksi = base_url() . "$modul/Transaksi/index/$jenis_master";

            // Ikon tugas
            if (isset($icon_master[$jenis_master]) && !empty($icon_master[$jenis_master]) && $icon_master[$jenis_master] !== 'fa-star' && $icon_master[$jenis_master] !== 'fa-circle') {
                $taskIcon = $icon_master[$jenis_master];
            } else {
                if ($cat === 'tax') {
                    $taskIcon = ($jenis_master === '110') ? 'fa-file-text-o' : 'fa-balance-scale';
                } elseif ($cat === 'purchasing') {
                    $taskIcon = 'fa-shopping-cart';
                } elseif ($cat === 'logistik') {
                    $taskIcon = 'fa-truck';
                } elseif ($cat === 'inventory') {
                    $taskIcon = 'fa-cubes';
                } else {
                    $taskIcon = 'fa-folder-open-o';
                }
            }

            // Teks penuaan
            $agingText = ($days_pending === 0) ? 'Hari ini' : "Tertua: $days_pending hr lalu";

            // Indikator khusus batas pelaporan pajak DJP
            $deadlineBadge = '';
            if ($jenis_master === '110') {
                $deadlineBadge = "<span class='label label-danger pull-right' style='font-size:10px; font-weight:600; padding:2px 6px; border-radius:3px;' title='Batas pelaporan e-Faktur Pajak ke DJP tgl 15'><i class='fa fa-bell'></i> Batas DJP: Tgl 15</span>";
            }

            $cardsList[] = array(
                'jenis_master'    => $jenis_master,
                'category'        => $cat,
                'title'           => $cleanTitle,
                'qty'             => $qty,
                'days_pending'    => $days_pending,
                'oldest_dtime'    => $oldestFormatted,
                'urgencyLevel'    => $urgencyLevel,
                'urgencyRank'     => $urgencyRank,
                'badgeClass'      => $badgeClass,
                'btnClass'        => $btnClass,
                'agingClass'      => $agingClass,
                'agingText'       => $agingText,
                'deadlineBadge'   => $deadlineBadge,
                'taskIcon'        => $taskIcon,
                'link'            => $link_transaksi,
            );
        }

        // Urutkan kartu: Urgensi Kritis duluan (Rank DESC), kemudian Umur Tertua DESC, kemudian Jumlah Dokumen DESC
        usort($cardsList, function ($a, $b) {
            if ($a['urgencyRank'] != $b['urgencyRank']) {
                return ($a['urgencyRank'] > $b['urgencyRank']) ? -1 : 1;
            }
            if ($a['days_pending'] != $b['days_pending']) {
                return ($a['days_pending'] > $b['days_pending']) ? -1 : 1;
            }
            return ($a['qty'] > $b['qty']) ? -1 : 1;
        });

        // Pisahkan kartu ke dalam grup tab
        $cardsByTab = array(
            'all'        => array(),
            'tax'        => array(),
            'purchasing' => array(),
            'logistik'   => array(),
            'inventory'  => array(),
            'lainnya'    => array(),
        );

        foreach ($cardsList as $c) {
            $cardsByTab['all'][] = $c;
            $catKey = $c['category'];
            if (isset($cardsByTab[$catKey])) {
                $cardsByTab[$catKey][] = $c;
            } else {
                $cardsByTab['lainnya'][] = $c;
            }
        }

        // Tentukan tab aktif default berdasarkan peran pengguna
        $defaultActiveTab = 'all';
        $userMemberships = my_memberships();
        if (is_array($userMemberships)) {
            if (in_array('c_finance', $userMemberships) || in_array('o_finance', $userMemberships)) {
                if (count($cardsByTab['tax']) > 0) {
                    $defaultActiveTab = 'tax';
                }
            } elseif (in_array('c_purchasing', $userMemberships) || in_array('o_purchasing', $userMemberships)) {
                if (count($cardsByTab['purchasing']) > 0) {
                    $defaultActiveTab = 'purchasing';
                }
            } elseif (in_array('c_logistik', $userMemberships) || in_array('o_logistik', $userMemberships)) {
                if (count($cardsByTab['logistik']) > 0) {
                    $defaultActiveTab = 'logistik';
                }
            }
        }

        // Bangun output HTML To Do List
        $refreshUrl = base_url("dashboard/Todolist/viewTodolistTransaksi");
        $totalAllDocs = number_format($categoryCounts['all'], 0, ',', '.');

        $html = "<div id='todolist-container' class='todolist-box-content'>";

        // CSS tersemat untuk mempercantik kartu dan antarmuka
        $html .= "<style>
            .todolist-box-content { font-family: 'Source Sans Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; }
            .todo-header-panel { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; background: #ffffff; padding: 10px 14px; border-radius: 6px; border: 1px solid #e5e9ec; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
            .todo-summary-info { display: flex; align-items: center; flex-wrap: wrap; gap: 8px; font-size: 13px; }
            .todo-summary-info .main-count { font-size: 15px; font-weight: 700; color: #2c3e50; margin-right: 6px; }
            .todo-tools-bar { display: flex; align-items: center; gap: 8px; margin-top: 4px; }
            .todo-search-input { width: 190px !important; height: 30px !important; border-radius: 4px !important; font-size: 12px !important; }
            
            .nav-tabs.todo-tabs { border-bottom: 2px solid #e1e8ed; margin-bottom: 14px; }
            .nav-tabs.todo-tabs > li > a { border-radius: 6px 6px 0 0; padding: 8px 14px; font-weight: 600; color: #5a6b7c; font-size: 13px; background: #f8fafc; border: 1px solid #e1e8ed; border-bottom: none; margin-right: 4px; transition: all 0.2s ease; }
            .nav-tabs.todo-tabs > li > a:hover { background: #ffffff; color: #1e293b; }
            .nav-tabs.todo-tabs > li.active > a, .nav-tabs.todo-tabs > li.active > a:focus, .nav-tabs.todo-tabs > li.active > a:hover { background: #ffffff; color: #0284c7; font-weight: 700; border: 1px solid #e1e8ed; border-bottom-color: transparent; border-top: 3px solid #0284c7; }
            .nav-tabs.todo-tabs .badge { margin-left: 5px; font-size: 11px; padding: 3px 6px; font-weight: 600; border-radius: 10px; }

            .todo-card { position: relative; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; margin-bottom: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.04); transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease; height: calc(100% - 12px); display: flex; flex-direction: column; justify-content: space-between; }
            .todo-card:hover { transform: translateY(-3px); box-shadow: 0 6px 14px rgba(0,0,0,0.08); border-color: #cbd5e1; }
            
            .todo-card-danger { border-left: 4px solid #ef4444 !important; }
            .todo-card-warning { border-left: 4px solid #f59e0b !important; }
            .todo-card-info { border-left: 4px solid #3b82f6 !important; }

            .todo-card-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 11px; }
            .todo-category-tag { font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; }
            
            .todo-card-main { display: flex; align-items: flex-start; margin-bottom: 10px; gap: 10px; }
            .todo-icon-box { flex-shrink: 0; width: 38px; height: 38px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
            .todo-card-danger .todo-icon-box { background: #fee2e2; color: #dc2626; }
            .todo-card-warning .todo-icon-box { background: #fef3c7; color: #d97706; }
            .todo-card-info .todo-icon-box { background: #e0f2fe; color: #0284c7; }

            .todo-card-details { flex-grow: 1; min-width: 0; }
            .todo-card-title { margin: 0 0 5px 0; font-size: 14px; font-weight: 700; color: #1e293b; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .todo-meta-row { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 4px; font-size: 12px; }
            .todo-qty-chip { display: inline-block; padding: 2px 7px; border-radius: 4px; font-weight: 700; font-size: 12px; }
            .todo-aging-chip { font-size: 11px; display: inline-flex; align-items: center; gap: 3px; }

            .todo-card-bottom { margin-top: auto; padding-top: 8px; border-top: 1px solid #f1f5f9; }
            .todo-btn-action { font-weight: 600; border-radius: 5px; font-size: 12px; padding: 5px 10px; text-align: center; display: block; text-decoration: none !important; transition: opacity 0.2s; }
            .todo-btn-action:hover { opacity: 0.9; }
            
            .todo-empty-state { background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 30px 20px; text-align: center; color: #64748b; margin: 10px 0; }
            .todo-empty-state i { font-size: 32px; color: #10b981; margin-bottom: 8px; }
        </style>";

        // Summary Bar Header
        $html .= "<div class='todo-header-panel'>";
        $html .= "<div class='todo-summary-info'>";
        $html .= "<span class='main-count'><i class='fa fa-tasks text-primary'></i> Total: $totalAllDocs Dokumen</span>";
        $html .= "<span class='text-muted'>menunggu penyelesaian</span>";
        $html .= "<span class='label label-danger' style='font-size:11px;'><i class='fa fa-exclamation-circle'></i> " . number_format($urgencyCounts['danger'], 0, ',', '.') . " Kritis</span>";
        $html .= "<span class='label label-warning' style='font-size:11px;'><i class='fa fa-clock-o'></i> " . number_format($urgencyCounts['warning'], 0, ',', '.') . " Perhatian</span>";
        $html .= "<span class='label label-info' style='font-size:11px;'><i class='fa fa-check-circle-o'></i> " . number_format($urgencyCounts['info'], 0, ',', '.') . " Normal</span>";
        $html .= "</div>";

        $html .= "<div class='todo-tools-bar'>";
        $html .= "<input type='text' id='todo-filter-input' class='form-control input-sm todo-search-input' placeholder='Cari transaksi...' onkeyup='filterTodoCards(this.value)'>";
        $html .= "<button type='button' class='btn btn-sm btn-default' title='Muat Ulang Data' onclick=\"$('#todolist').load('$refreshUrl');\"><i class='fa fa-refresh text-primary'></i> Muat Ulang</button>";
        $html .= "</div>";
        $html .= "</div>";

        // Tab Navigasi Kategori
        $html .= "<ul class='nav nav-tabs todo-tabs'>";
        foreach ($categoryMeta as $cKey => $cMeta) {
            $catDocCount = isset($categoryCounts[$cKey]) ? $categoryCounts[$cKey] : 0;
            // Jika tab bukan 'all' dan tidak memiliki tugas, sembunyikan jika kategori 'lainnya'
            if ($cKey === 'lainnya' && $catDocCount === 0) {
                continue;
            }
            $isActive = ($cKey === $defaultActiveTab) ? 'active' : '';
            $badgeColor = $cMeta['color'];
            $icon = $cMeta['icon'];
            $label = $cMeta['label'];

            $html .= "<li class='$isActive'>";
            $html .= "<a href='#tab-todo-$cKey' data-toggle='tab'>";
            $html .= "<i class='fa $icon " . $cMeta['textColor'] . "'></i> $label ";
            $html .= "<span class='badge $badgeColor'>" . number_format($catDocCount, 0, ',', '.') . "</span>";
            $html .= "</a>";
            $html .= "</li>";
        }
        $html .= "</ul>";

        // Tab Content Grid
        $html .= "<div class='tab-content'>";
        foreach ($categoryMeta as $cKey => $cMeta) {
            if ($cKey === 'lainnya' && (!isset($categoryCounts['lainnya']) || $categoryCounts['lainnya'] === 0)) {
                continue;
            }
            $isActive = ($cKey === $defaultActiveTab) ? 'active in' : '';
            $tabCards = isset($cardsByTab[$cKey]) ? $cardsByTab[$cKey] : array();

            $html .= "<div class='tab-pane fade $isActive' id='tab-todo-$cKey'>";
            if (count($tabCards) > 0) {
                $html .= "<div class='row' style='margin-left:-6px; margin-right:-6px;'>";
                foreach ($tabCards as $card) {
                    $catId = $card['category'];
                    $catTitle = isset($categoryMeta[$catId]['label']) ? $categoryMeta[$catId]['label'] : 'Umum';

                    $html .= "<div class='col-lg-3 col-md-4 col-sm-6 col-xs-12 todo-card-col' data-keyword='" . strtolower($card['title'] . " " . $card['jenis_master']) . "' style='padding:0 6px;'>";
                    $html .= "<div class='todo-card todo-card-" . $card['urgencyLevel'] . "'>";

                    // Header Kartu
                    $html .= "<div class='todo-card-top'>";
                    $html .= "<span class='todo-category-tag'><i class='fa " . (isset($categoryMeta[$catId]['icon']) ? $categoryMeta[$catId]['icon'] : 'fa-folder-o') . "'></i> $catTitle</span>";
                    $html .= $card['deadlineBadge'];
                    $html .= "</div>";

                    // Badan Kartu
                    $html .= "<div class='todo-card-main'>";
                    $html .= "<div class='todo-icon-box'><i class='fa " . $card['taskIcon'] . "'></i></div>";
                    $html .= "<div class='todo-card-details'>";
                    $html .= "<h4 class='todo-card-title' title='" . htmlspecialchars($card['title'], ENT_QUOTES) . "'>" . $card['title'] . "</h4>";
                    $html .= "<div class='todo-meta-row'>";
                    $html .= "<span class='todo-qty-chip " . $card['badgeClass'] . "'><i class='fa fa-clone'></i> <strong>" . number_format($card['qty'], 0, ',', '.') . "</strong> Dok</span>";
                    $html .= "<span class='todo-aging-chip " . $card['agingClass'] . "' title='Dokumen tertua sejak: " . $card['oldest_dtime'] . "'><i class='fa fa-clock-o'></i> " . $card['agingText'] . "</span>";
                    $html .= "</div>";
                    $html .= "</div>";
                    $html .= "</div>";

                    // Footer Kartu (Tombol Tindakan)
                    $html .= "<div class='todo-card-bottom'>";
                    $html .= "<a href='" . $card['link'] . "' class='btn btn-xs " . $card['btnClass'] . " todo-btn-action' title='Buka antrean " . htmlspecialchars($card['title'], ENT_QUOTES) . "'>";
                    $html .= "<span>Buka Antrean</span> <i class='fa fa-arrow-circle-right'></i>";
                    $html .= "</a>";
                    $html .= "</div>";

                    $html .= "</div>";
                    $html .= "</div>";
                }
                $html .= "</div>";
            } else {
                $html .= "<div class='todo-empty-state'>";
                $html .= "<i class='fa fa-check-circle'></i>";
                $html .= "<h4 style='margin:0 0 5px 0; color:#1e293b; font-weight:700;'>Tidak Ada Antrean Tugas</h4>";
                $html .= "<p style='margin:0; font-size:13px;'>Semua transaksi pada kategori ini telah selesai diproses.</p>";
                $html .= "</div>";
            }
            $html .= "</div>";
        }
        $html .= "</div>";

        // JavaScript Filter Pencarian Cepat
        $html .= "<script>
            function filterTodoCards(query) {
                var term = query.toLowerCase().trim();
                $('.tab-pane.active .todo-card-col').each(function() {
                    var text = $(this).attr('data-keyword') || '';
                    if (term === '' || text.indexOf(term) > -1) {
                        $(this).fadeIn(150);
                    } else {
                        $(this).fadeOut(150);
                    }
                });
            }
        </script>";

        $html .= "</div>";

        $data = array(
            "mode"                  => "viewTodolistTransaksi",
            "title"                 => "To Do List Transaksi",
            "subTitle"              => "<a href=\"javascript:void(0);\" class=\"btn btn-xs btn-default\" onclick=\"$('#todolist').load('$refreshUrl');\"><i class=\"fa fa-refresh\"></i></a>",
            "content"               => $html,
            "label_series"          => '',
            "tipe_series"           => '',
            "subRekening"           => '',
            "keyBiaya"              => '',
            "periode"               => '',
            "indeks"                => '',
            "buffer"                => '',
            "leadTime"              => '',
            "navigasi"              => '',
            "navigasiAttr"          => '',
            "biayaBulanan"          => '',
            "hppBulanan"            => '',
            "pembelianBulanan"      => '',
            "penjualanBulanan"      => '',
            "penjualanFireBulanan"  => '',
            "penjualanNonFireBulanan" => '',
            "dataQuarter"           => '',
            "penjualanQuarter"      => '',
            "add_link"              => "",
            "link_cart"             => '',
        );

        $this->load->view("graph", $data);
    }
}
// END OF COMPLETE REPEATED LOGIC
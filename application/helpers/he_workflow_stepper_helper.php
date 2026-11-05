<?php
// START OF COMPLETE REPEATED LOGIC
/**
 * Helper Visualisasi Alur Kerja Transaksi (Workflow Stepper & Context Guidance Card)
 * Digunakan untuk menyediakan UI transparan yang memandu pengguna:
 * 1. Mengetahui posisi alur ("Dari Mana - Ke Mana").
 * 2. Mengetahui tujuan pembukaan layar ("Saya melakukan ini untuk apa").
 * 3. Menghindari kebingungan tombol/menu yang belum aktif.
 */

if (!function_exists('renderWorkflowStepper')) {
    /**
     * Render komponen visual Workflow Stepper horizontal.
     *
     * @param int $activeStep Nomor langkah yang sedang aktif (1-5)
     * @param string $jenisTr Jenis transaksi (opsional)
     * @param array $customSteps Definisi langkah kustom (opsional)
     * @return string HTML rendered stepper
     */
    function renderWorkflowStepper($activeStep = 2, $jenisTr = null, $customSteps = array())
    {
        $ci =& get_instance();

        // Definisi standar alur siklus proyek jika customSteps tidak diberikan
        $defaultSteps = array(
            1 => array(
                "title" => "Perencanaan & Kontrak",
                "role" => "Sales / PM",
                "desc" => "Penerbitan Kontrak & RAB Proyek",
                "icon" => "fa-file-text-o"
            ),
            2 => array(
                "title" => "Alokasi BOM & Tim",
                "role" => "Admin Proyek",
                "desc" => "Penetapan Material & Teknisi Lapangan",
                "icon" => "fa-cogs"
            ),
            3 => array(
                "title" => "Pengeluaran Bahan",
                "role" => "Bagian Gudang",
                "desc" => "Distribusi Material Fisik Proyek",
                "icon" => "fa-truck"
            ),
            4 => array(
                "title" => "Pengerjaan & QC",
                "role" => "Teknisi / QC",
                "desc" => "Eksekusi SPK & Uji Kualitas Lapangan",
                "icon" => "fa-wrench"
            ),
            5 => array(
                "title" => "BAST & Penagihan",
                "role" => "Finance",
                "desc" => "Serah Terima & Penagihan Termin",
                "icon" => "fa-money"
            )
        );

        $steps = (!empty($customSteps) && is_array($customSteps)) ? $customSteps : $defaultSteps;
        $totalSteps = count($steps);

        $html = "";
        $html .= "<style>
            .wf-stepper-container {
                background: #ffffff;
                border: 1px solid #e1e6eb;
                border-radius: 6px;
                padding: 14px 18px;
                margin-bottom: 16px;
                box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            }
            .wf-stepper-title {
                font-size: 11px;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-weight: bold;
                color: #8c9ba5;
                margin-bottom: 10px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .wf-stepper-track {
                display: flex;
                align-items: flex-start;
                justify-content: space-between;
                position: relative;
                margin: 0;
                padding: 0;
                list-style: none;
            }
            .wf-stepper-item {
                flex: 1;
                text-align: center;
                position: relative;
                padding: 0 6px;
            }
            .wf-stepper-item:not(:last-child)::after {
                content: '';
                position: absolute;
                top: 18px;
                left: 55%;
                width: 90%;
                height: 3px;
                background-color: #e5e9ec;
                z-index: 1;
            }
            .wf-stepper-item.completed:not(:last-child)::after {
                background-color: #00a65a;
            }
            .wf-stepper-item.active:not(:last-child)::after {
                background-color: #e5e9ec;
            }
            .wf-stepper-circle {
                width: 36px;
                height: 36px;
                border-radius: 50%;
                background: #f0f3f5;
                color: #7f8c8d;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-size: 14px;
                font-weight: bold;
                margin-bottom: 6px;
                position: relative;
                z-index: 2;
                border: 2px solid #e1e6eb;
                transition: all 0.2s ease;
            }
            .wf-stepper-item.completed .wf-stepper-circle {
                background: #00a65a;
                color: #ffffff;
                border-color: #008d4c;
            }
            .wf-stepper-item.active .wf-stepper-circle {
                background: #0073b7;
                color: #ffffff;
                border-color: #005a91;
                box-shadow: 0 0 0 4px rgba(0, 115, 183, 0.2);
            }
            .wf-stepper-item.locked .wf-stepper-circle {
                background: #f4f6f7;
                color: #bdc3c7;
                border-color: #e5e9ec;
            }
            .wf-stepper-name {
                font-size: 12px;
                font-weight: bold;
                color: #333333;
                margin-bottom: 2px;
                line-height: 1.2;
            }
            .wf-stepper-item.completed .wf-stepper-name {
                color: #00a65a;
            }
            .wf-stepper-item.active .wf-stepper-name {
                color: #0073b7;
            }
            .wf-stepper-item.locked .wf-stepper-name {
                color: #95a5a6;
            }
            .wf-stepper-role {
                font-size: 10px;
                color: #7f8c8d;
                text-transform: uppercase;
                font-weight: 600;
                display: block;
            }
            .wf-stepper-badge {
                display: inline-block;
                padding: 1px 6px;
                border-radius: 3px;
                font-size: 9px;
                margin-top: 3px;
                font-weight: 600;
            }
            .badge-done { background: #e8f8f0; color: #00a65a; border: 1px solid #b7e4cf; }
            .badge-now  { background: #e6f2f8; color: #0073b7; border: 1px solid #b3d7ed; }
            .badge-lock { background: #f5f5f5; color: #999999; border: 1px solid #e0e0e0; }
        </style>";

        $html .= "<div class='wf-stepper-container hidden'>";
        $html .= "<div class='wf-stepper-title'>";
        $html .= "<span><i class='fa fa-sitemap'></i> Alur Rantai Bisnis Proyek (Workflow Pipeline)</span>";
        $html .= "<span class='text-muted' style='font-size:10px;'>Posisi: Langkah " . (int)$activeStep . " dari " . (int)$totalSteps . "</span>";
        $html .= "</div>";

        $html .= "<ul class='wf-stepper-track'>";
        foreach ($steps as $num => $stepData) {
            $isCompleted = ($num < $activeStep);
            $isActive = ($num == $activeStep);
            $isLocked = ($num > $activeStep);

            $statusClass = $isCompleted ? "completed" : ($isActive ? "active" : "locked");
            $icon = isset($stepData['icon']) ? $stepData['icon'] : "fa-circle";
            $title = isset($stepData['title']) ? $stepData['title'] : "Langkah $num";
            $role = isset($stepData['role']) ? $stepData['role'] : "Operasional";

            $badgeLabel = "";
            if ($isCompleted) {
                $circleContent = "<i class='fa fa-check'></i>";
                $badgeLabel = "<span class='wf-stepper-badge badge-done'><i class='fa fa-check'></i> Selesai</span>";
            } elseif ($isActive) {
                $circleContent = "<i class='fa $icon'></i>";
                $badgeLabel = "<span class='wf-stepper-badge badge-now'><i class='fa fa-pencil'></i> Aktif Disini</span>";
            } else {
                $circleContent = "<i class='fa fa-lock'></i>";
                $badgeLabel = "<span class='wf-stepper-badge badge-lock'><i class='fa fa-clock-o'></i> Menunggu</span>";
            }

            $html .= "<li class='wf-stepper-item $statusClass'>";
            $html .= "<div class='wf-stepper-circle' title='" . htmlspecialchars($title) . "'>$circleContent</div>";
            $html .= "<div class='wf-stepper-name'>$title</div>";
            $html .= "<span class='wf-stepper-role'>$role</span>";
            $html .= $badgeLabel;
            $html .= "</li>";
        }
        $html .= "</ul>";
        $html .= "</div>";

        return $html;
    }
}

if (!function_exists('renderContextGuidanceCard')) {
    /**
     * Render kartu petunjuk kontekstual ("Saya melakukan ini untuk apa?").
     *
     * @param array $params Parameter panduan:
     *                      - role: Nama peran aktif
     *                      - purpose: Tujuan langkah ini
     *                      - origin: Dokumen asal (dari mana)
     *                      - nextImpact: Dampak lanjutan (ke mana)
     *                      - auditUrl: Link audit (opsional)
     * @return string HTML rendered guidance card
     */
    function renderContextGuidanceCard($params = array())
    {
        $role = isset($params['role']) ? $params['role'] : "Admin Proyek";
        $purpose = isset($params['purpose']) ? $params['purpose'] : "Menetapkan alokasi material BOM dan tim kerja teknisi sebelum pengeluaran gudang.";
        $origin = isset($params['origin']) ? $params['origin'] : "Kontrak Kerja / SPK Penjualan";
        $nextImpact = isset($params['nextImpact']) ? $params['nextImpact'] : "Bagian Gudang akan menerima kuota pengeluaran bahan & Teknisi menerima Tasklist QC.";
        $auditUrl = isset($params['auditUrl']) ? $params['auditUrl'] : "";

        $html = "";
        $html .= "<style>
            .wf-guidance-card {
                background: #fdfefe;
                border-left: 4px solid #0073b7;
                border-top: 1px solid #e5edf2;
                border-right: 1px solid #e5edf2;
                border-bottom: 1px solid #e5edf2;
                border-radius: 4px;
                padding: 12px 16px;
                margin-bottom: 16px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            }
            .wf-guidance-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
                gap: 12px;
                margin-top: 8px;
            }
            .wf-guidance-block {
                background: #f8fafc;
                border: 1px solid #edf2f7;
                border-radius: 4px;
                padding: 8px 10px;
            }
            .wf-guidance-label {
                font-size: 10px;
                font-weight: bold;
                text-transform: uppercase;
                color: #718096;
                margin-bottom: 3px;
                display: flex;
                align-items: center;
                gap: 4px;
            }
            .wf-guidance-desc {
                font-size: 12px;
                color: #2d3748;
                line-height: 1.35;
                font-weight: 500;
            }
        </style>";

        $html .= "<div class='wf-guidance-card hidden'>";
        $html .= "<div class='clearfix'>";
        $html .= "<div class='pull-left'>";
        $html .= "<strong style='color:#0073b7; font-size:13px;'><i class='fa fa-info-circle'></i> Panduan Konteks & Tujuan Langkah Ini</strong>";
        $html .= "</div>";

        if (!empty($auditUrl)) {
            $html .= "<div class='pull-right'>";
            $html .= "<a href='$auditUrl' target='_blank' class='btn btn-xs btn-primary' style='border-radius:3px;'>";
            $html .= "<i class='fa fa-stethoscope'></i> UAT Audit Triangulasi Proyek";
            $html .= "</a>";
            $html .= "</div>";
        }
        $html .= "</div>";

        $html .= "<div class='wf-guidance-grid'>";

        // Block 1: Peran Anda
        $html .= "<div class='wf-guidance-block'>";
        $html .= "<div class='wf-guidance-label'><i class='fa fa-user-circle text-primary'></i> Peran Anda</div>";
        $html .= "<div class='wf-guidance-desc'>" . htmlspecialchars($role) . "</div>";
        $html .= "</div>";

        // Block 2: Dari Mana (Asal Dokumen)
        $html .= "<div class='wf-guidance-block'>";
        $html .= "<div class='wf-guidance-label'><i class='fa fa-arrow-circle-right text-success'></i> Dari Mana (Prasyarat)</div>";
        $html .= "<div class='wf-guidance-desc'>" . htmlspecialchars($origin) . "</div>";
        $html .= "</div>";

        // Block 3: Tujuan Form Ini
        $html .= "<div class='wf-guidance-block'>";
        $html .= "<div class='wf-guidance-label'><i class='fa fa-bullseye text-warning'></i> Tujuan Input Ini</div>";
        $html .= "<div class='wf-guidance-desc'>" . htmlspecialchars($purpose) . "</div>";
        $html .= "</div>";

        // Block 4: Ke Mana (Dampak Selanjutnya)
        $html .= "<div class='wf-guidance-block'>";
        $html .= "<div class='wf-guidance-label'><i class='fa fa-paper-plane text-info'></i> Ke Mana (Dampak Lanjutan)</div>";
        $html .= "<div class='wf-guidance-desc'>" . htmlspecialchars($nextImpact) . "</div>";
        $html .= "</div>";

        $html .= "</div>";
        $html .= "</div>";

        return $html;
    }
}
// END OF COMPLETE REPEATED LOGIC

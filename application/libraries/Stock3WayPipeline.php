<?php

// START OF COMPLETE REPEATED LOGIC
/**
 * Core Pipeline Engine: 3-Way Matching Persediaan & Audit Locker
 * 
 * Mematuhi Doktrin ERP: "ELSE ADALAH NYAWA ERP"
 * Mengimplementasikan 5 Tahap Pipeline (Pipes & Filters) dengan penegakan FAIL-CLOSED
 * Kompatibel 100% dengan PHP 5.6 & CodeIgniter 3.1.8 HMVC
 */
class Stock3WayPipeline
{
    protected $CI;
    protected $rekeningFisik = "1010030030";
    protected $rekeningIntransit = "1010030040";

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper("he_route");
    }

    /**
     * Jalur Cepat (Fast-Lane Pipeline) untuk Gatekeeper Checkout UI
     * Target latensi: < 10 ms (Memverifikasi Buku Besar vs Meja Locker)
     *
     * @param int $cabangId
     * @param int $gudangId
     * @param int $produkId
     * @param float $qtyRequested
     * @return array Status keputusan: status, code, msg, data
     */
    public function runFastLane($cabangId, $gudangId, $produkId, $qtyRequested = 0)
    {
        // Tahap 1: Context & Scope Resolver
        $context = $this->resolveContext($cabangId, $gudangId, $produkId);
        if (!$context['is_valid']) {
            $response = array(
                "status" => "BLOCK",
                "code" => "ELSE-4",
                "msg" => "Konfigurasi gudang atau akun akuntansi persediaan produk ini belum terdaftar sah.",
                "data" => $context
            );
            $this->dispatchAlert("ELSE-4", $context, array(), $response['msg']);
            return $response;
        }

        // Tahap 2: Multi-Pillar Extractor (Fast Mode: Ledger & Locker)
        $extraction = $this->extractPillars($context, "fast");
        if (!$extraction['success']) {
            $response = array(
                "status" => "BLOCK",
                "code" => "ELSE-3",
                "msg" => "Koneksi audit integritas stok terputus. Demi keamanan data transaksi tidak dapat dilanjutkan.",
                "data" => $extraction
            );
            $this->dispatchAlert("ELSE-3", $context, $extraction, $response['msg']);
            return $response;
        }

        // Tahap 3: State & Intransit Normalizer
        $normalized = $this->normalizeStates($extraction);

        // Tahap 4: Triangulation & Comparator
        $comparison = $this->compareTriangulation($normalized, "fast");

        // Tahap 5: Decision Gatekeeper & Action Dispatcher
        $decision = $this->evaluateDecision($context, $normalized, $comparison, $qtyRequested, "fast");

        return $decision;
    }

    /**
     * Jalur Audit Penuh (Deep-Audit Pipeline) untuk Background Worker / CLI Cron
     * Memvalidasi 3 Pilar: Mutasi Primer (Checkpoint) == Ledger GL == Locker
     *
     * @param int $cabangId
     * @param int $gudangId
     * @param int $produkId
     * @param string $startDate (Format Y-m-d)
     * @return array Hasil audit mendalam beserta matriks varians
     */
    public function runDeepAudit($cabangId, $gudangId, $produkId, $startDate = null)
    {
        $context = $this->resolveContext($cabangId, $gudangId, $produkId);
        if (!$context['is_valid']) {
            return array(
                "status" => "BLOCK",
                "code" => "ELSE-4",
                "msg" => "Context tidak valid untuk audit.",
                "data" => $context
            );
        }

        $context['start_date'] = !empty($startDate) ? $startDate : date("Y-m-01 00:00:00");

        $extraction = $this->extractPillars($context, "deep");
        if (!$extraction['success']) {
            return array(
                "status" => "BLOCK",
                "code" => "ELSE-3",
                "msg" => "Gagal mengekstraksi data pada deep audit.",
                "data" => $extraction
            );
        }

        $normalized = $this->normalizeStates($extraction);
        $comparison = $this->compareTriangulation($normalized, "deep");
        $decision = $this->evaluateDecision($context, $normalized, $comparison, 0, "deep");

        return $decision;
    }

    // =========================================================================
    // PIPELINE STAGES (TAHAP 1 s/d TAHAP 5)
    // =========================================================================

    /**
     * TAHAP 1: Context & Scope Resolver
     * Memvalidasi integritas parameter, memfilter ID dummy (-555, 0), dan membaca nama produk.
     */
    protected function resolveContext($cabangId, $gudangId, $produkId)
    {
        $cId = intval($cabangId);
        $gId = intval($gudangId);
        $pId = intval($produkId);

        $isValid = true;
        $errorReason = "";

        if ($pId <= 0) {
            $isValid = false;
            $errorReason = "ID Produk tidak valid ($pId)";
        } elseif ($cId <= 0) {
            $isValid = false;
            $errorReason = "ID Cabang tidak valid ($cId)";
        } elseif ($gId <= 0 || $gId == -555) {
            $isValid = false;
            $errorReason = "ID Gudang tidak valid ($gId)";
        }

        // Ambil nama produk untuk payload notifikasi
        $produkNama = "Unknown Product";
        if ($pId > 0) {
            $this->CI->db->select("nama, kode, barcode");
            $this->CI->db->where("id", $pId);
            $queryProd = $this->CI->db->get("produk");
            if ($queryProd && $queryProd->num_rows() > 0) {
                $pRow = $queryProd->row();
                $produkNama = $pRow->nama;
            }
        }

        return array(
            "is_valid" => $isValid,
            "error_reason" => $errorReason,
            "cabang_id" => $cId,
            "gudang_id" => $gId,
            "produk_id" => $pId,
            "produk_nama" => $produkNama,
            "timestamp" => date("Y-m-d H:i:s")
        );
    }

    /**
     * TAHAP 2: Multi-Pillar Extractor (Parallel Data Fetching)
     * Mengambil data dari Pilar 2 (GL), Pilar 3 (Locker), dan Pilar 1 (Primer jika deep)
     */
    protected function extractPillars($context, $mode)
    {
        try {
            $pId = $context['produk_id'];
            $cId = $context['cabang_id'];
            $gId = $context['gudang_id'];

            // --- Pipe 2B: Buku Besar Akuntansi (Inventory Ledger) ---
            $sqlLedger = "SELECT 
                            COALESCE(SUM(CASE WHEN rekening = ? THEN qty_debet ELSE 0 END), 0) AS qty_fisik,
                            COALESCE(SUM(CASE WHEN rekening = ? THEN qty_debet ELSE 0 END), 0) AS qty_intransit
                          FROM _rek_pembantu_produk_cache
                          WHERE periode = 'forever'
                            AND rekening IN (?, ?)
                            AND extern_id = ?
                            AND cabang_id = ?
                            AND gudang_id = ?";
            $bindsLedger = array(
                $this->rekeningFisik,
                $this->rekeningIntransit,
                $this->rekeningFisik,
                $this->rekeningIntransit,
                $pId,
                $cId,
                $gId
            );
            $qLedger = $this->CI->db->query($sqlLedger, $bindsLedger);
            if (!$qLedger) {
                return array("success" => false, "stage" => "Pipe 2B Ledger", "error" => "Query Ledger Failed");
            }
            $rowLedger = $qLedger->row();
            $ledgerFisik = floatval($rowLedger->qty_fisik);
            $ledgerIntransit = floatval($rowLedger->qty_intransit);

            // --- Pipe 2C: Meja Operasional Stock Locker ---
            $sqlLocker = "SELECT 
                            COALESCE(SUM(CASE WHEN state = 'active' THEN jumlah ELSE 0 END), 0) AS qty_active,
                            COALESCE(SUM(CASE WHEN state = 'hold' AND transaksi_id = '0' THEN jumlah ELSE 0 END), 0) AS qty_hold_draft,
                            COALESCE(SUM(CASE WHEN state = 'hold' AND transaksi_id > '0' THEN jumlah ELSE 0 END), 0) AS qty_hold_intransit
                          FROM stock_locker
                          WHERE jenis = 'produk'
                            AND produk_id = ?
                            AND cabang_id = ?
                            AND gudang_id = ?";
            $bindsLocker = array($pId, $cId, $gId);
            $qLocker = $this->CI->db->query($sqlLocker, $bindsLocker);
            if (!$qLocker) {
                return array("success" => false, "stage" => "Pipe 2C Locker", "error" => "Query Locker Failed");
            }
            $rowLocker = $qLocker->row();
            $lockerActive = floatval($rowLocker->qty_active);
            $lockerHoldDraft = floatval($rowLocker->qty_hold_draft);
            $lockerHoldIntransit = floatval($rowLocker->qty_hold_intransit);

            // --- Pipe 2A: Mutasi Transaksi Primer (Hanya dieksekusi pada Deep Audit) ---
            $primaryQty = 0.0;
            if ($mode === "deep") {
                $startDate = isset($context['start_date']) ? $context['start_date'] : date("Y-m-01 00:00:00");
                $sqlPrimary = "SELECT 
                                 COALESCE(SUM(CASE 
                                     WHEN t.arah_mutasi = 'masuk' THEN td.valid_qty 
                                     WHEN t.arah_mutasi = 'keluar' THEN -(td.valid_qty)
                                     ELSE 0 
                                 END), 0) AS qty_mutasi
                               FROM transaksi_data td
                               INNER JOIN transaksi t ON t.id = td.transaksi_id
                               WHERE t.trash_4 = '0'
                                 AND td.produk_id = ?
                                 AND t.cabang_id = ?
                                 AND t.gudang_id = ?
                                 AND t.dtime >= ?";
                $bindsPrimary = array($pId, $cId, $gId, $startDate);
                $qPrimary = $this->CI->db->query($sqlPrimary, $bindsPrimary);
                if ($qPrimary && $qPrimary->num_rows() > 0) {
                    $rowPrimary = $qPrimary->row();
                    $primaryQty = floatval($rowPrimary->qty_mutasi);
                }
            }

            return array(
                "success" => true,
                "ledger_fisik" => $ledgerFisik,
                "ledger_intransit" => $ledgerIntransit,
                "locker_active" => $lockerActive,
                "locker_hold_draft" => $lockerHoldDraft,
                "locker_hold_intransit" => $lockerHoldIntransit,
                "primary_qty" => $primaryQty
            );
        } catch (Exception $e) {
            return array("success" => false, "stage" => "Extractor Exception", "error" => $e->getMessage());
        }
    }

    /**
     * TAHAP 3: State & Intransit Normalizer
     * Menghitung total komprehensif dan mendeteksi kondisi saldo negatif.
     */
    protected function normalizeStates($extraction)
    {
        $ledgerFisik = round($extraction['ledger_fisik'], 4);
        $ledgerIntransit = round($extraction['ledger_intransit'], 4);
        $lockerActive = round($extraction['locker_active'], 4);
        $lockerHoldDraft = round($extraction['locker_hold_draft'], 4);
        $lockerHoldIntransit = round($extraction['locker_hold_intransit'], 4);
        $primaryQty = round($extraction['primary_qty'], 4);

        $totalLockerPhysical = round($lockerActive + $lockerHoldDraft + $lockerHoldIntransit, 4);

        // Deteksi Saldo Negatif (Indikator Bug Koding Masa Lalu)
        $hasNegative = false;
        $negativeDetails = array();
        if ($ledgerFisik < 0) {
            $hasNegative = true;
            $negativeDetails[] = "Ledger Fisik ($ledgerFisik)";
        }
        if ($lockerActive < 0) {
            $hasNegative = true;
            $negativeDetails[] = "Locker Active ($lockerActive)";
        }
        if ($lockerHoldDraft < 0) {
            $hasNegative = true;
            $negativeDetails[] = "Locker Hold Draft ($lockerHoldDraft)";
        }
        if ($lockerHoldIntransit < 0) {
            $hasNegative = true;
            $negativeDetails[] = "Locker Hold Intransit ($lockerHoldIntransit)";
        }

        return array(
            "ledger_fisik" => $ledgerFisik,
            "ledger_intransit" => $ledgerIntransit,
            "locker_active" => $lockerActive,
            "locker_hold_draft" => $lockerHoldDraft,
            "locker_hold_intransit" => $lockerHoldIntransit,
            "total_locker_physical" => $totalLockerPhysical,
            "primary_qty" => $primaryQty,
            "has_negative" => $hasNegative,
            "negative_details" => $negativeDetails
        );
    }

    /**
     * TAHAP 4: Triangulation & Drift Comparator
     * Membandingkan pilar data dan menghitung delta varians.
     */
    protected function compareTriangulation($normalized, $mode)
    {
        $diffLedgerLocker = round(abs($normalized['ledger_fisik'] - $normalized['total_locker_physical']), 4);
        $isLockerSynced = ($diffLedgerLocker <= 0.0001);

        $isPrimarySynced = true;
        $diffPrimaryLedger = 0.0;
        if ($mode === "deep") {
            $diffPrimaryLedger = round(abs($normalized['primary_qty'] - $normalized['ledger_fisik']), 4);
            $isPrimarySynced = ($diffPrimaryLedger <= 0.0001);
        }

        return array(
            "is_locker_synced" => $isLockerSynced,
            "diff_ledger_locker" => $diffLedgerLocker,
            "is_primary_synced" => $isPrimarySynced,
            "diff_primary_ledger" => $diffPrimaryLedger
        );
    }

    /**
     * TAHAP 5: Decision Gatekeeper & Action Dispatcher
     * Menerapkan Doktrin ERP: YES, NO, ELSE Matrix dengan penegakan FAIL-CLOSED mutlak.
     */
    protected function evaluateDecision($context, $normalized, $comparison, $qtyRequested, $mode)
    {
        $qtyReq = round(floatval($qtyRequested), 4);
        $payloadData = array(
            "context" => $context,
            "normalized" => $normalized,
            "comparison" => $comparison,
            "qty_requested" => $qtyReq
        );

        // --- CABANG ELSE-2: SALDO NEGATIF (Bug Koding / Integritas Terkorupsi) ---
        if ($normalized['has_negative']) {
            $negStr = implode(", ", $normalized['negative_details']);
            $msg = "Terdeteksi nilai saldo persediaan anomali negatif pada: $negStr. Transaksi dibekukan demi keamanan data. Hubungi Tim Teknis.";
            $response = array(
                "status" => "BLOCK",
                "code" => "ELSE-2",
                "msg" => $msg,
                "data" => $payloadData
            );
            $this->dispatchAlert("ELSE-2", $context, $payloadData, $msg);
            return $response;
        }

        // --- CABANG ELSE-1: LOCKER DRIFT (Buku Besar != Total Locker) ---
        if (!$comparison['is_locker_synced']) {
            $diff = $comparison['diff_ledger_locker'];
            $msg = "Terjadi ketidaksesuaian integritas data persediaan (Selisih Buku vs Locker: $diff). Transaksi dibatalkan otomatis. Harap hubungi Tim Dukungan Teknis.";
            $response = array(
                "status" => "BLOCK",
                "code" => "ELSE-1",
                "msg" => $msg,
                "data" => $payloadData
            );
            $this->dispatchAlert("ELSE-1", $context, $payloadData, $msg);
            return $response;
        }

        // --- CABANG ELSE-5: GL POSTING DRIFT (Khusus Mode Deep Audit) ---
        if ($mode === "deep" && !$comparison['is_primary_synced']) {
            $diffGl = $comparison['diff_primary_ledger'];
            $msg = "Terdeteksi ketidaksesuaian Mutasi Transaksi Primer vs Buku Besar Akuntansi (Selisih: $diffGl).";
            $response = array(
                "status" => "BLOCK",
                "code" => "ELSE-5",
                "msg" => $msg,
                "data" => $payloadData
            );
            $this->dispatchAlert("ELSE-5", $context, $payloadData, $msg);
            return $response;
        }

        // --- CABANG NO: REJEKSI BISNIS NORMAL (Stok Bebas Tidak Cukup) ---
        if ($qtyReq > 0 && $qtyReq > $normalized['locker_active']) {
            $avail = $normalized['locker_active'];
            $hold = $normalized['locker_hold_draft'];
            $msg = "Stok bebas tidak mencukupi untuk transaksi ini. Kuantitas diminta: $qtyReq, Stok bebas tersedia: $avail" . ($hold > 0 ? " (Sebagian stok sebesar $hold unit sedang diproses di draf transaksi lain)" : "") . ".";
            return array(
                "status" => "REJECT",
                "code" => "NO",
                "msg" => $msg,
                "data" => $payloadData
            );
        }

        // --- CABANG YES: TRANSAKSI SAH & SINKRON (ALLOW) ---
        return array(
            "status" => "ALLOW",
            "code" => "YES",
            "msg" => "Stok persediaan tervalidasi sinkron dan mencukupi.",
            "data" => $payloadData
        );
    }

    /**
     * Dispatcher Notifikasi Teknis Darurat (Dual Notification)
     * Mengirim payload ke Telegram Teknis via kirim_tele_route() dan menyimpan log audit.
     */
    public function dispatchAlert($anomalyCode, $context, $data, $userMessage)
    {
        try {
            $domain = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : "ERP-EVEREST";
            $pId = isset($context['produk_id']) ? $context['produk_id'] : 0;
            $pNama = isset($context['produk_nama']) ? $context['produk_nama'] : "-";
            $cId = isset($context['cabang_id']) ? $context['cabang_id'] : 0;
            $gId = isset($context['gudang_id']) ? $context['gudang_id'] : 0;
            $waktu = date("Y-m-d H:i:s");

            $teleMsg = "*🚨 EMERGENCY AUDIT ALERT: STOCK PIPELINE ANOMALY*" . PHP_EOL;
            $teleMsg .= "*Domain:* " . $domain . PHP_EOL;
            $teleMsg .= "*Kode Anomali:* " . $anomalyCode . PHP_EOL;
            $teleMsg .= "*Waktu Kejadian:* " . $waktu . PHP_EOL;
            $teleMsg .= "*Cabang ID:* " . $cId . " | *Gudang ID:* " . $gId . PHP_EOL;
            $teleMsg .= "*Produk:* [" . $pId . "] " . $pNama . PHP_EOL . PHP_EOL;

            if (isset($data['normalized'])) {
                $norm = $data['normalized'];
                $teleMsg .= "*📊 RINCIAN SALDO:* " . PHP_EOL;
                $teleMsg .= "- Buku Besar Fisik (GL): " . $norm['ledger_fisik'] . PHP_EOL;
                $teleMsg .= "- Locker Active: " . $norm['locker_active'] . PHP_EOL;
                $teleMsg .= "- Locker Hold Draft (tr=0): " . $norm['locker_hold_draft'] . PHP_EOL;
                $teleMsg .= "- Locker Hold Intransit (tr>0): " . $norm['locker_hold_intransit'] . PHP_EOL;
                $teleMsg .= "- Total Meja Locker: " . $norm['total_locker_physical'] . PHP_EOL;
            }

            if (isset($data['comparison'])) {
                $comp = $data['comparison'];
                $teleMsg .= "*⚖️ SELISIH (VARIANCE):* " . $comp['diff_ledger_locker'] . PHP_EOL;
            }

            $teleMsg .= PHP_EOL . "*Status Eksekusi:* FAIL-CLOSED (Transaksi Diblokir Sektika)." . PHP_EOL;
            $teleMsg .= "*Pesan Staf:* " . $userMessage . PHP_EOL;

            // Eksekusi pengiriman Telegram via he_route
            $chatIdTeknis = "-1001457771609";
            $tokenTeknis = "2006804072:AAF1qUtWoF88THjnMdDkXmPAhY0XnRYaGPs";

            if (function_exists('kirim_tele_route')) {
                @kirim_tele_route($teleMsg, $chatIdTeknis, $tokenTeknis);
            }

            // Catat log ke tabel notif_locker jika tersedia
            $tableNotif = "notif_locker";
            if ($this->CI->db->table_exists($tableNotif)) {
                $logData = array(
                    "transaksi_id" => 0,
                    "msg" => $teleMsg,
                    "status" => 1,
                );
                @$this->CI->db->insert($tableNotif, $logData);
            }
        } catch (Exception $e) {
            // Silently log exception to avoid blocking the fail-closed response
            log_message('error', 'Stock3WayPipeline dispatchAlert failed: ' . $e->getMessage());
        }
    }
}
// END OF COMPLETE REPEATED LOGIC

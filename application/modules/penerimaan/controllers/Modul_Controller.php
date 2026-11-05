<?php
// START OF COMPLETE REPEATED LOGIC
// defined('BASEPATH') OR exit('No direct script access allowed');

class Modul_Controller extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();

        /* ----------------------------------------------------------------------------------
         * validasi session bila tidak ada dipaksa ke halaman login
         * ----------------------------------------------------------------------------------*/
        if (!isset($this->session->login['id'])) {
            gotoLogin();
        }
        validateUserSession($this->session->login['id']);

        /* ----------------------------------------------------------------------------------
         * loader dari masing-masing modul
         * ----------------------------------------------------------------------------------*/
        $this->jenisTr = $tmpJenis = $this->uri->segment(4);
        $this->cCode = $cCode = "_TR_" . $this->jenisTr;
        $this->modul = $modul = $this->uri->segment(1);
        $this->cabangId = $this->placeId = my_cabang_id();
        $this->dates = dtimeNow('y-m-d');

        /* ----------------------------------------------------------------------------------
         * MODUL_### dibentuk di he_url_helper
         * ---------------------------------------------------------------------------------*/
        $this->modulPath = $modulPath = MODUL_PATH;
        /* ---------------------------------------------------------------------------------
         * untuk ngeload config pada modul
         * ---------------------------------------------------------------------------------*/
        $this->configPath = $configPath = MODUL_CONFIG_PATH;
        $this->load->config($configPath . "coTransaksiUi");
        $this->configUi = $this->config->item("coTransaksiUi");

        $this->load->config($configPath . "coTransaksiCore");
        $this->configCore = $this->config->item("coTransaksiCore");

        $this->load->config($configPath . "coTransaksiLayout");
        $this->configLayout = $this->config->item("coTransaksiLayout");
        $this->load->config($configPath . "coTransaksiValues");
        $this->configValues = $this->config->item("coTransaksiValues");

        if (isset($this->jenisTr)) {
            $this->configUiJenis = isset($this->configUi[$this->jenisTr]) ? $this->configUi[$this->jenisTr] : cekOrange($this->jenisTr . " belum ada di coTransaksiUi di modul " . $this->modul);
            $this->configCoreJenis = isset($this->configCore[$this->jenisTr]) ? $this->configCore[$this->jenisTr] : cekBiru($this->jenisTr . " belum ada di coTransaksiCore di modul " . $this->modul);
            $this->configLayoutJenis = isset($this->configLayout[$this->jenisTr]) ? $this->configLayout[$this->jenisTr] : cekMerah($this->jenisTr . " belum ada di coTransaksiLayout di modul " . $this->modul);
            $this->configValuesJenis = isset($this->configValues[$this->jenisTr]) ? $this->configValues[$this->jenisTr] : cekMerah($this->jenisTr . " belum ada di coTransaksiLayout di modul " . $this->modul);
            $this->jenisTrName = isset($this->configUi[$this->jenisTr]['steps'][1]['label']) ? $this->configUi[$this->jenisTr]['steps'][1]['label'] : "unnamed";
        }

        $this->load->helper("he_access_right");
        $this->load->helper("he_session_replacer");
        $this->accessList = alowedAccess(my_id());

        $this->transaksiMaintenance = $this->config->item("maintenanceTransaksi") != null && $this->config->item("maintenanceTransaksi") == true ? true : false;
        $this->transaksiMaintenanceMsg = isset($this->config->item("maintenanceOptions")[1]) ? $this->config->item("maintenanceOptions")[1] : array();
        $this->mongoTableList = array(
            "main" => "transaksi",
            "mainValues" => "transaksi_values",
            "detail" => "transaksi_data",
            "detailValues" => "transaksi_data_values",
            "sign" => "transaksi_sign",
            "extras" => "transaksi_extstep",
            "registry" => "transaksi_registry",
        );
        $this->allSteps = isset($this->configUi[$this->jenisTr]['steps']) ? $this->configUi[$this->jenisTr]['steps'] : array();

        $this->ppnFactor = isset($_SESSION['login']['ppnFactor']) ? $this->session->login["ppnFactor"] : cekOrange("cek " . __FILE__);
    }

    public function checkLockOrDie($jenisTr, $bookingNumber, $backUrl = null)
    {
        if (empty($bookingNumber) || empty($jenisTr)) {
            return;
        }

        $this->load->model("Mdls/MdlLockerTransaksi");
        $cabangId = my_cabang_id();
        $myId = my_id();
        $myName = isset($_SESSION['login']['nama_login']) ? $_SESSION['login']['nama_login'] : 'Petugas';

        // Cek jika ada request retake lock
        $forceRetake = $this->input->get('forceRetakeLock');
        if ($forceRetake == '1') {
            $existingLock = $this->MdlLockerTransaksi->checkLockFinance($cabangId, $jenisTr, $bookingNumber);
            if (sizeof($existingLock) > 0) {
                $lastActive = strtotime($existingLock['dtime']);
                if ((time() - $lastActive) >= 300) { // Idle >= 5 menit
                    $this->MdlLockerTransaksi->retakeLockFinance($cabangId, $jenisTr, $bookingNumber, $myId, $myName);
                    return;
                }
            }
        }

        $lockData = $this->MdlLockerTransaksi->checkLockFinance($cabangId, $jenisTr, $bookingNumber);
        if (sizeof($lockData) > 0) {
            if (!$lockData['is_my_lock']) {
                // Lock dipegang petugas lain di cabang yang sama
                $dataView = array(
                    'booking_number' => $bookingNumber,
                    'lock_data' => array(
                        'nama_login' => $lockData['oleh_nama'],
                        'oleh_id' => $lockData['oleh_id'],
                        'ip_address' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1',
                        'device_info' => isset($lockData['satuan']) ? $lockData['satuan'] : 'PC / Browser',
                        'dtime_last_active' => $lockData['dtime']
                    ),
                    'back_url' => $backUrl ? $backUrl : base_url($this->modul . "/Transaksi/index/" . $jenisTr),
                    'retake_url' => current_url() . "?forceRetakeLock=1"
                );
                $this->load->view('modal_transaksi_terkunci', $dataView);
                die();
            }
        }

        // Terapkan/perbarui lock aktif milik user
        $this->MdlLockerTransaksi->holdLockFinance($cabangId, $jenisTr, $bookingNumber, $myId, $myName);
    }

    public function releaseTransactionLock($jenisTr, $bookingNumber)
    {
        if (empty($bookingNumber) || empty($jenisTr)) {
            return;
        }
        $this->load->model("Mdls/MdlLockerTransaksi");
        $cabangId = my_cabang_id();
        $this->MdlLockerTransaksi->releaseLockFinance($cabangId, $jenisTr, $bookingNumber);
    }

    public function index()
    {
        cekOrange($this->modul);
        die(__FILE__ . " gondes");
    }

    protected function decodeSafeSerializedBase64($raw, $default = null)
    {
        if (!is_string($raw) || $raw === "") {
            return $default;
        }
        $decoded = base64_decode($raw, true);
        if ($decoded === false || $decoded === "") {
            return $default;
        }
        // Block serialized object/reference payloads from request.
        if (preg_match('/(^|;|{|})[OCR]:[0-9]+:/', $decoded) === 1) {
            return $default;
        }

        $value = @unserialize($decoded);
        if ($value === false && $decoded !== serialize(false)) {
            return $default;
        }
        if (is_object($value)) {
            return $default;
        }

        return $value;
    }

    protected function decodeSafeSerializedBase64Array($raw)
    {
        $value = $this->decodeSafeSerializedBase64($raw, array());
        return is_array($value) ? $value : array();
    }

    protected function getSafeArrayQueryParam($key)
    {
        if (!isset($_GET[$key]) || is_array($_GET[$key])) {
            return array();
        }
        return $this->decodeSafeSerializedBase64Array($_GET[$key]);
    }

    protected function getSafeIntQueryParam($key, $default = 0)
    {
        if (!isset($_GET[$key]) || is_array($_GET[$key])) {
            return (int)$default;
        }
        return (int)$_GET[$key];
    }

    protected function sanitizeFilterExpression($expression)
    {
        if (!is_string($expression)) {
            return null;
        }
        $expression = trim($expression);
        if ($expression === "") {
            return null;
        }
        if (preg_match('/(--|\/\*|\*\/|;|#)/', $expression) === 1) {
            return null;
        }
        if (preg_match('/^[a-zA-Z0-9_().,\s\'"`><=%-]+$/', $expression) !== 1) {
            return null;
        }

        return $expression;
    }

    protected function getSafeFilterParamsFromQuery($key)
    {
        $rawFilters = $this->getSafeArrayQueryParam($key);
        $safeFilters = array();
        if (sizeof($rawFilters) > 0) {
            foreach ($rawFilters as $filter) {
                $safeFilter = $this->sanitizeFilterExpression($filter);
                if ($safeFilter !== null) {
                    $safeFilters[] = $safeFilter;
                }
            }
        }
        return $safeFilters;
    }

    protected function isProjectNoteItem($itemSpec)
    {
        if (!is_array($itemSpec) || sizeof($itemSpec) == 0) {
            return false;
        }

        $projectId = 0;
        if (isset($itemSpec['project_id'])) {
            $projectId = (int)$itemSpec['project_id'];
        }
        elseif (isset($itemSpec['projectID'])) {
            $projectId = (int)$itemSpec['projectID'];
        }
        if ($projectId > 0) {
            return true;
        }

        $projectName = "";
        if (isset($itemSpec['project_nama'])) {
            $projectName = trim((string)$itemSpec['project_nama']);
        }
        elseif (isset($itemSpec['projectName'])) {
            $projectName = trim((string)$itemSpec['projectName']);
        }

        return $projectName !== "" && $projectName !== "0";
    }

    protected function assertNoMixedProjectNotes($cCode, $pendingItem = array(), $pendingId = null)
    {
        $selectorProcessor = isset($this->configUi[$this->jenisTr]['selectorProcessor']) ? $this->configUi[$this->jenisTr]['selectorProcessor'] : "";
        if (strpos((string)$selectorProcessor, "_processSelectNota") === false) {
            return;
        }

        $selectedItems = isset($_SESSION[$cCode]['items']) && is_array($_SESSION[$cCode]['items']) ? $_SESSION[$cCode]['items'] : array();
        $projectCount = 0;
        $regularCount = 0;

        foreach ($selectedItems as $selectedId => $itemSpec) {
            if ($pendingId !== null && (string)$selectedId === (string)$pendingId) {
                continue;
            }

            if ($this->isProjectNoteItem((array)$itemSpec)) {
                $projectCount++;
            }
            else {
                $regularCount++;
            }
        }

        if (is_array($pendingItem) && sizeof($pendingItem) > 0) {
            if ($this->isProjectNoteItem($pendingItem)) {
                $projectCount++;
            }
            else {
                $regularCount++;
            }
        }

        if ($projectCount > 1) {
            matiHere("Project hanya boleh 1 nota per transaksi.");
        }
        if ($projectCount > 0 && $regularCount > 0) {
            matiHere("Nota project tidak bisa digabung dengan nota reguler.");
        }
    }

    // START OF COMPLETE REPEATED LOGIC
    protected function assertSequentialProjectTermins($pendingItem = array())
    {
        // Dukungan pemanggilan berbasis kode sesi (misal dari Create.php)
        if (is_string($pendingItem)) {
            $cCode = $pendingItem;
            if (isset($_SESSION[$cCode]['items']) && is_array($_SESSION[$cCode]['items'])) {
                foreach ($_SESSION[$cCode]['items'] as $it) {
                    $this->assertSequentialProjectTermins((array)$it);
                }
            }
            return;
        }

        if (!$this->isProjectNoteItem($pendingItem)) {
            return;
        }

        $projectId = 0;
        if (isset($pendingItem['project_id']) && is_numeric($pendingItem['project_id'])) {
            $projectId = (int)$pendingItem['project_id'];
        }
        elseif (isset($pendingItem['projectID']) && is_numeric($pendingItem['projectID'])) {
            $projectId = (int)$pendingItem['projectID'];
        }
        if ($projectId <= 0) {
            return;
        }

        $currentTpsId = isset($pendingItem['tabel_id']) ? (int)$pendingItem['tabel_id'] : (isset($pendingItem['id']) ? (int)$pendingItem['id'] : 0);
        $currentFulldate = isset($pendingItem['fulldate']) ? (string)$pendingItem['fulldate'] : '';

        // Query ke transaksi_payment_source untuk memeriksa apakah ada nota termin lebih tua yang belum lunas
        $this->db->select("id, nomer, tagihan, terbayar, sisa, fulldate");
        $this->db->from("transaksi_payment_source");
        $this->db->where("project_id", $projectId);
        $this->db->where("sisa >", 1000);
        $this->db->where("target_jenis", "749");
        $this->db->where("(cancel_dtime IS NULL OR cancel_dtime = '0000-00-00 00:00:00')", null, false);
        $this->db->order_by("fulldate", "ASC");
        $this->db->order_by("id", "ASC");
        $olderRows = $this->db->get()->result_array();

        if (!empty($olderRows)) {
            foreach ($olderRows as $or) {
                $orId = (int)$or['id'];
                $orFulldate = (string)$or['fulldate'];

                // Jika baris ini adalah baris yang sedang dipilih sendiri, lewati
                if ($currentTpsId > 0 && $orId === $currentTpsId) {
                    continue;
                }

                // Cek apakah baris ini lebih tua dari yang sedang dipilih
                $isOlder = false;
                if (!empty($orFulldate) && !empty($currentFulldate) && $orFulldate !== $currentFulldate) {
                    $isOlder = ($orFulldate < $currentFulldate);
                }
                elseif ($currentTpsId > 0) {
                    $isOlder = ($orId < $currentTpsId);
                }

                if ($isOlder) {
                    $nom = $or['nomer'];
                    $sisaF = number_format($or['sisa']);
                    matiHere("Pembayaran termin proyek wajib berurutan. Nota $nom dengan sisa tagihan Rp $sisaF harus dilunasi terlebih dahulu.");
                }
            }
        }
    }

    protected function assertValidAdvancePaymentBalance($cCode)
    {
        if (!isset($_SESSION[$cCode]['main'])) {
            return;
        }

        $main = $_SESSION[$cCode]['main'];

        // 1. Cek Uang Muka Reguler
        if (isset($main['uang_muka_dipakai']) && ((float)$main['uang_muka_dipakai'] > 0)) {
            $val = (float)$main['uang_muka_dipakai'];
            $saldo = isset($main['uangMuka__saldoUangMukaround']) ? (float)$main['uangMuka__saldoUangMukaround'] : (isset($main['uangMuka__saldoUangMuka']) ? (float)$main['uangMuka__saldoUangMuka'] : 0);
            if ($val > $saldo) {
                matiHere("Pelanggaran Integritas: Pemakaian Uang Muka (" . number_format($val, 0, ',', '.') . ") melebihi saldo tersedia (" . number_format($saldo, 0, ',', '.') . ").");
            }
        }

        // 2. Cek Uang Muka Project
        if (isset($main['uang_muka_dipakai_ppn']) && ((float)$main['uang_muka_dipakai_ppn'] > 0)) {
            $valPrj = (float)$main['uang_muka_dipakai_ppn'];
            $saldoPrj = isset($main['uangMukaProject__saldoUangMukaRound']) ? (float)$main['uangMukaProject__saldoUangMukaRound'] : (isset($main['uangMukaProject__saldoPakai']) ? (float)$main['uangMukaProject__saldoPakai'] : 0);
            if ($valPrj > $saldoPrj) {
                matiHere("Pelanggaran Integritas: Pemakaian Uang Muka Project (" . number_format($valPrj, 0, ',', '.') . ") melebihi saldo tersedia (" . number_format($saldoPrj, 0, ',', '.') . ").");
            }
        }
    }
}
// END OF COMPLETE REPEATED LOGIC

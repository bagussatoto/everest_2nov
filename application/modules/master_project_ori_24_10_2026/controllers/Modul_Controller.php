<?php
// START OF COMPLETE REPEATED LOGIC
// defined('BASEPATH') OR exit('No direct script access allowed');

class Modul_Controller extends MX_Controller
{
    public $configUi;
    public $configUiJenis = array();
    public $configCore;
    public $configCoreJenis = array();
    public $configLayout;
    public $configLayoutJenis = array();
    public $configValues;
    public $configValuesJenis = array();
    public $jenisTr;
    public $cCode;
    public $modul;
    public $cabangId;
    public $placeId;
    public $dates;
    public $modulPath;
    public $configPath;
    public $jenisTrName = "unnamed";
    public $accessList;
    public $transaksiMaintenance;
    public $transaksiMaintenanceMsg;
    public $mongoTableList;
    public $allSteps = array();
    public $ppnFactor;

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
        // cekHitam($this->modulPath);
        /* ---------------------------------------------------------------------------------
         * untuk ngeload config pada modul
         * ---------------------------------------------------------------------------------*/
        $this->configPath = $configPath = MODUL_CONFIG_PATH;
        // cekHitam($this->configPath);
        $this->load->config($configPath . "coTransaksiUi");
        $this->configUi = $this->config->item("coTransaksiUi");


        $this->load->config($configPath . "coTransaksiCore");
        $this->configCore = $this->config->item("coTransaksiCore");

        $this->load->config($configPath . "coTransaksiLayout");
        $this->configLayout = $this->config->item("coTransaksiLayout");
        $this->load->config($configPath . "coTransaksiValues");
        $this->configValues = $this->config->item("coTransaksiValues");

        $this->configUiJenis = array();
        $this->configCoreJenis = array();
        $this->configLayoutJenis = array();
        $this->configValuesJenis = array();
        $this->jenisTrName = "unnamed";

        if (isset($this->jenisTr) && !empty($this->jenisTr)) {
            $heTransaksi_ui = $this->config->item("heTransaksi_ui");
            if (isset($this->configUi[$this->jenisTr])) {
                $this->configUiJenis = $this->configUi[$this->jenisTr];
            } else if (isset($heTransaksi_ui[$this->jenisTr])) {
                $this->configUiJenis = $heTransaksi_ui[$this->jenisTr];
            } else {
                $this->configUiJenis = cekOrange($this->jenisTr . " belum ada di coTransaksiUi di modul " . $this->modul);
            }

            $this->configCoreJenis = isset($this->configCore[$this->jenisTr]) ? $this->configCore[$this->jenisTr] : cekBiru($this->jenisTr . " belum ada di coTransaksiCore di modul " . $this->modul);
            $this->configLayoutJenis = isset($this->configLayout[$this->jenisTr]) ? $this->configLayout[$this->jenisTr] : cekMerah($this->jenisTr . " belum ada di coTransaksiLayout di modul " . $this->modul);
            $this->configValuesJenis = isset($this->configValues[$this->jenisTr]) ? $this->configValues[$this->jenisTr] : cekMerah($this->jenisTr . " belum ada di coTransaksiLayout di modul " . $this->modul);
            $this->jenisTrName = (is_array($this->configUiJenis) && isset($this->configUiJenis['steps'][1]['label'])) ? $this->configUiJenis['steps'][1]['label'] : "unnamed";
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
        $this->allSteps = (is_array($this->configUiJenis) && isset($this->configUiJenis['steps'])) ? $this->configUiJenis['steps'] : array();

        $this->ppnFactor = isset($_SESSION['login']['ppnFactor']) ? $this->session->login["ppnFactor"] : cekOrange("cek " . __FILE__);

    }

    public function index()
    {
        cekOrange($this->modul);
        die(__FILE__ . " gondes");

    }

}

// END OF COMPLETE REPEATED LOGIC

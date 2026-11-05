<?php
// START OF COMPLETE REPEATED LOGIC
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Class ProjectPostingBiayaService
 * Service Layer Sentral untuk Posting Biaya SPK ke Keuangan (3674 & 3675)
 * Mematuhi Standar Arsitektur ERP Dunia (Single ACID Transaction)
 * 100% Kompatibel dengan CodeIgniter 3 HMVC dan PHP 5.6
 *
 * Mengeliminasi pemanggilan HTTP cURL internal antar-modul, memungkinkan:
 * 1. Pemanggilan in-memory langsung dari Master Project (QC).
 * 2. Unifikasi transaksi database MariaDB atomik (trans_start / trans_complete).
 * 3. Native rollback otomatis jika terjadi kegagalan data keuangan/vendor.
 */
class ProjectPostingBiayaService
{
    /**
     * @var CI_Controller
     */
    protected $CI;

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->helper("he_angka");
        $this->CI->load->helper("he_finansial");
        $this->CI->load->helper("he_stepping");
        $this->CI->load->helper("he_access_right");
        $this->CI->load->config("heAccounting");
        $this->CI->load->model("CustomCounter");
        $this->CI->load->model("MdlTransaksi");
    }

    /**
     * Magic getter untuk mengakses super-object CI3
     *
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        return get_instance()->$key;
    }

    /**
     * Eksekusi posting biaya SPK ke modul biaya (3674) dan payment source (3675)
     *
     * @param string $no_spk Nomor SPK yang akan diposting
     * @param array $userLogin Data sesi login pelaksana transaksi
     * @param bool|int $forceRepost True jika dipaksa reposting ulang (misal melalui tool reparasi)
     * @param bool $autoCommit True jika dijalankan standalone (mengelola trans_start sendiri),
     *                         False jika dijalankan di dalam transaksi induk (misal MasterData QC)
     * @return array [
     *     'status' => int (1=sukses, 0=gagal),
     *     'post_biaya_id' => int,
     *     'post_biaya_no' => string,
     *     'payment_source_id' => int,
     *     'reason' => string,
     *     'line' => int
     * ]
     */
    public function eksekusiPostingBiayaSpk($no_spk, $userLogin = array(), $forceRepost = false, $autoCommit = false)
    {
        if (empty($no_spk)) {
            return array(
                "status" => 0,
                "reason" => "Nomor SPK tidak valid atau belum di-set.",
                "line" => __LINE__
            );
        }

        if (empty($userLogin) && isset($this->CI->session->login)) {
            $userLogin = $this->CI->session->login;
        }

        $controllerPath = APPPATH . "modules/biaya/controllers/AutoPostingBiaya.php";
        if (!file_exists($controllerPath)) {
            return array(
                "status" => 0,
                "reason" => "File controller AutoPostingBiaya tidak ditemukan pada path: " . $controllerPath,
                "line" => __LINE__
            );
        }

        require_once $controllerPath;

        if (!class_exists("AutoPostingBiaya")) {
            return array(
                "status" => 0,
                "reason" => "Class AutoPostingBiaya tidak ditemukan setelah require_once.",
                "line" => __LINE__
            );
        }

        $controller = new AutoPostingBiaya();

        if (method_exists($controller, "executePostingInternal")) {
            return $controller->executePostingInternal($no_spk, $userLogin, $forceRepost ? 1 : 0, $autoCommit);
        }

        return array(
            "status" => 0,
            "reason" => "Method executePostingInternal belum didefinisikan pada AutoPostingBiaya.",
            "line" => __LINE__
        );
    }
}
// END OF COMPLETE REPEATED LOGIC

<?php
// START OF COMPLETE REPEATED LOGIC
// defined('BASEPATH') OR exit('No direct script access allowed');

require_once "Modul_Controller.php";

/**
 * Controller HTTP Gateway untuk Auto Posting Pembatalan (Jenis 9911)
 * Berfungsi sebagai wrapper HTTP/AJAX yang mendelegasikan eksekusi bisnis
 * secara penuh ke library PembatalanPostingService.
 */
class AutoPostingPembatalan extends Modul_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library("PembatalanPostingService");
    }

    public function index()
    {
        header("Content-Type: application/json");
        $params = !empty($_POST) ? $_POST : (!empty($_GET) ? $_GET : $_REQUEST);
        $result = $this->executeCancellation($params);
        echo json_encode($result);
        exit;
    }

    public function executeCancellation($params = array())
    {
        if (empty($params)) {
            $params = !empty($_POST) ? $_POST : (!empty($_GET) ? $_GET : $_REQUEST);
        }
        return $this->pembatalanpostingservice->processCancellation($params);
    }
}
// END OF COMPLETE REPEATED LOGIC

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "Modul_Controller.php";

// START OF COMPLETE REPEATED LOGIC
class _processSelectPaymentSource extends Modul_Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function select()
    {
        $cCode = $this->cCode;
        if (!isset($_SESSION[$cCode])) {
            $_SESSION[$cCode] = array(
                "main" => array(),
                "items" => array()
            );
        }

        // Ambil input ID baik via POST maupun GET (mendukung array, JSON string, atau list berkoma)
        $rawIds = $this->input->post('ids');
        if (empty($rawIds)) {
            $rawIds = $this->input->post('payment_source_ids');
        }
        if (empty($rawIds)) {
            $rawIds = $this->input->post('transaksi_ids');
        }
        if (empty($rawIds) && isset($_GET['ids'])) {
            $rawIds = $_GET['ids'];
        }

        $cleanIds = array();
        if (is_array($rawIds)) {
            foreach ($rawIds as $v) {
                $v = trim($v);
                if (is_numeric($v) && $v > 0) {
                    $cleanIds[] = intval($v);
                }
            }
        } elseif (is_string($rawIds) && strlen(trim($rawIds)) > 0) {
            $decoded = json_decode($rawIds, true);
            if (is_array($decoded)) {
                foreach ($decoded as $v) {
                    $v = trim($v);
                    if (is_numeric($v) && $v > 0) {
                        $cleanIds[] = intval($v);
                    }
                }
            } else {
                $exp = explode(",", $rawIds);
                foreach ($exp as $v) {
                    $v = trim($v);
                    if (is_numeric($v) && $v > 0) {
                        $cleanIds[] = intval($v);
                    }
                }
            }
        }

        $cleanIds = array_unique($cleanIds);

        // Reset items3_sum untuk transaksi 756
        $_SESSION[$cCode]['items3_sum'] = array();

        $loadedCount = 0;
        if (sizeof($cleanIds) > 0) {
            $this->load->model("Mdls/MdlPaymentSource");
            $mps = new MdlPaymentSource();
            $mps->setFilters(array());
            $mps->addFilter("id in ('" . implode("','", $cleanIds) . "')");
            $res = $mps->lookupAll();
            if ($res && $res->num_rows() > 0) {
                $rows = $res->result_array();
                foreach ($rows as $row) {
                    $rowId = $row['id'];
                    $row['refID'] = isset($row['transaksi_id']) ? $row['transaksi_id'] : 0;
                    unset($row['transaksi_id']);
                    $row['refNum'] = isset($row['nomer']) ? $row['nomer'] : '';
                    unset($row['nomer']);
                    $_SESSION[$cCode]['items3_sum'][$rowId] = $row;
                    $loadedCount++;
                }
            }
        }

        if ($this->input->is_ajax_request() || isset($_GET['is_ajax'])) {
            echo json_encode(array(
                "status" => 1,
                "count" => $loadedCount,
                "message" => "Berhasil memuat $loadedCount data nota dari transaksi_payment_source ke items3_sum"
            ));
            return;
        }

        echo "Berhasil memuat $loadedCount data nota ke items3_sum.";
    }
}
// END OF COMPLETE REPEATED LOGIC

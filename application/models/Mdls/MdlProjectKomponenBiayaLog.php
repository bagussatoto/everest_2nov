<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// START OF COMPLETE REPEATED LOGIC
class MdlProjectKomponenBiayaLog extends CI_Model
{
    protected $tableName = "project_komponen_biaya_log";

    public function __construct()
    {
        parent::__construct();
    }

    public function addLog($logData)
    {
        if (!is_array($logData) || count($logData) == 0) {
            return false;
        }

        $insertData = array(
            "biaya_id"      => isset($logData['biaya_id']) ? intval($logData['biaya_id']) : 0,
            "biaya_nama"    => isset($logData['biaya_nama']) ? $logData['biaya_nama'] : "",
            "user_id"       => isset($logData['user_id']) ? intval($logData['user_id']) : 0,
            "user_nama"     => isset($logData['user_nama']) ? $logData['user_nama'] : "",
            "ip_address"    => isset($logData['ip_address']) ? $logData['ip_address'] : "",
            "alasan"        => isset($logData['alasan']) ? trim($logData['alasan']) : "",
            "total_sebelum" => isset($logData['total_sebelum']) ? floatval($logData['total_sebelum']) : 0,
            "total_sesudah" => isset($logData['total_sesudah']) ? floatval($logData['total_sesudah']) : 0,
            "detail_delta"  => isset($logData['detail_delta']) ? (is_array($logData['detail_delta']) ? json_encode($logData['detail_delta']) : $logData['detail_delta']) : "{}",
            "created_at"    => isset($logData['created_at']) ? $logData['created_at'] : date("Y-m-d H:i:s"),
        );

        $result = $this->db->insert($this->tableName, $insertData);
        if ($result) {
            return $this->db->insert_id();
        }
        return false;
    }

    public function getHistoryByBiayaID($biayaID, $limit = 50)
    {
        $biayaID = intval($biayaID);
        $limit = intval($limit);

        $this->db->where('biaya_id', $biayaID);
        $this->db->order_by('created_at', 'DESC');
        $this->db->order_by('id', 'DESC');
        if ($limit > 0) {
            $this->db->limit($limit);
        }

        return $this->db->get($this->tableName)->result();
    }
}
// END OF COMPLETE REPEATED LOGIC

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// START OF COMPLETE REPEATED LOGIC
class MdlCashUncheckRequest extends MdlMother
{
    protected $tableName = "transaksi_cash_uncheck_request";
    protected $indexFields = "id";

    protected $listedFieldsForm = array();
    protected $listedFieldsHidden = array();
    protected $search;
    protected $filters = array();

    public function __construct()
    {
        parent::__construct();
        $this->ensureTableExists();
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    public function getIndexFields()
    {
        return $this->indexFields;
    }

    public function addData($data, $table = null)
    {
        $targetTable = !empty($table) ? $table : $this->tableName;
        $this->db->insert($targetTable, $data);
        return $this->db->insert_id();
    }

    /**
     * Update data pada tabel transaksi_cash_uncheck_request
     */
    public function updateData($where, $data, $table = null)
    {
        $targetTable = !empty($table) ? $table : $this->tableName;
        if (is_array($where)) {
            $this->db->where($where);
        } else {
            $this->db->where($this->indexFields, $where);
        }
        return $this->db->update($targetTable, $data);
    }

    /**
     * Memperbarui status persetujuan atau penolakan permohonan pengecualian kas
     */
    public function updateApprovalStatus($requestId, $status, $spvId, $spvNama, $catatanSpv, $actionMethod = 'remote')
    {
        $updateData = array(
            "status"        => $status,
            "spv_id"        => intval($spvId),
            "spv_nama"      => $spvNama,
            "catatan_spv"   => $catatanSpv,
            "action_method" => $actionMethod,
            "dtime_action"  => date('Y-m-d H:i:s')
        );

        $this->db->where('id', intval($requestId));
        return $this->db->update($this->tableName, $updateData);
    }

    /**
     * Memastikan tabel transaksi_cash_uncheck_request sudah terbentuk di database
     */
    private function ensureTableExists()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `" . $this->tableName . "` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `nomer_request` varchar(50) NOT NULL,
            `transaksi_id` int(11) NOT NULL,
            `transaksi_no` varchar(50) DEFAULT NULL,
            `transaksi_nilai` decimal(15,2) DEFAULT '0.00',
            `customer_nama` varchar(150) DEFAULT NULL,
            `cabang_id` int(11) DEFAULT '0',
            `cabang_nama` varchar(100) DEFAULT NULL,
            `kasir_id` int(11) DEFAULT '0',
            `kasir_nama` varchar(100) DEFAULT NULL,
            `alasan_kasir` text,
            `status` varchar(20) NOT NULL DEFAULT 'pending',
            `spv_id` int(11) DEFAULT NULL,
            `spv_nama` varchar(100) DEFAULT NULL,
            `catatan_spv` text,
            `action_method` varchar(20) DEFAULT NULL,
            `dtime_request` datetime DEFAULT NULL,
            `dtime_action` datetime DEFAULT NULL,
            `trash` tinyint(1) NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`),
            KEY `idx_transaksi_id` (`transaksi_id`),
            KEY `idx_cabang_status` (`cabang_id`, `status`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        $this->db->query($sql);
    }

    /**
     * Generate Nomor Request Unik: REQ-EXC.YYYYMMDD.XXXX
     */
    public function generateNomerRequest()
    {
        $prefix = "REQ-EXC." . date("Ymd") . ".";
        $sql = "SELECT nomer_request FROM " . $this->tableName . " 
                WHERE nomer_request LIKE ? 
                ORDER BY id DESC LIMIT 1";
        $query = $this->db->query($sql, array($prefix . "%"));
        $lastNo = 0;
        if ($query && $query->num_rows() > 0) {
            $row = $query->row();
            $exp = explode(".", $row->nomer_request);
            if (isset($exp[2]) && is_numeric($exp[2])) {
                $lastNo = intval($exp[2]);
            }
        }
        $nextNo = str_pad($lastNo + 1, 4, "0", STR_PAD_LEFT);
        return $prefix . $nextNo;
    }

    /**
     * Mengambil data permohonan aktif berdasarkan transaksi_id nota kas
     */
    public function getLatestRequestByTransaksiId($transaksiId)
    {
        $sql = "SELECT * FROM " . $this->tableName . " 
                WHERE transaksi_id = ? AND trash = 0 
                ORDER BY id DESC LIMIT 1";
        $query = $this->db->query($sql, array(intval($transaksiId)));
        if ($query && $query->num_rows() > 0) {
            return $query->row_array();
        }
        return null;
    }

    /**
     * Mengambil daftar seluruh request untuk atasan (khususnya status pending & riwayat hari ini)
     */
    public function getRequestsForSpv($cabangId = 0)
    {
        $this->db->from($this->tableName);
        $this->db->where('trash', 0);
        if ($cabangId > 0) {
            $this->db->where('cabang_id', intval($cabangId));
        }
        $this->db->order_by("FIELD(status, 'pending', 'approved', 'rejected')", "", false);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(100);
        $query = $this->db->get();
        if ($query && $query->num_rows() > 0) {
            return $query->result_array();
        }
        return array();
    }
}
// END OF COMPLETE REPEATED LOGIC

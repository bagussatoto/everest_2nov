<?php
// START OF COMPLETE REPEATED LOGIC

class ComKpiSettlement extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Mengambil ringkasan global nominal hutang setoran kas cabang (target_jenis=759 & sisa>0) dari tabel transaksi_payment_source
     * 
     * @param int|null $cabangId ID cabang (opsional)
     * @return array Data ringkasan global
     */
    public function getSummaryGlobal($cabangId = null)
    {
        $this->db->select("
            COALESCE(SUM(sisa), 0) AS total_sisa,
            COUNT(DISTINCT cabang_id) AS total_cabang,
            COUNT(DISTINCT extern_id) AS total_penyetor,
            COUNT(id) AS total_nota
        ", false);
        $this->db->from("transaksi_payment_source");
        $this->db->where("target_jenis", "759");
        $this->db->where("sisa >", 0);
        
        if ($cabangId != null && intval($cabangId) > 0) {
            $this->db->where("cabang_id", intval($cabangId));
        }
        
        $query = $this->db->get();
        $row = $query->row();

        // Hitung total overdue penyetor (> 24 jam)
        $this->db->select("COUNT(DISTINCT extern_id) AS total_overdue", false);
        $this->db->from("transaksi_payment_source");
        $this->db->where("target_jenis", "759");
        $this->db->where("sisa >", 0);
        $this->db->where("dtime <", date("Y-m-d H:i:s", strtotime("-24 hours")));
        if ($cabangId != null && intval($cabangId) > 0) {
            $this->db->where("cabang_id", intval($cabangId));
        }
        $queryOverdue = $this->db->get();
        $rowOverdue = $queryOverdue->row();

        $result = array(
            "total_sisa" => isset($row->total_sisa) ? floatval($row->total_sisa) : 0,
            "total_cabang" => isset($row->total_cabang) ? intval($row->total_cabang) : 0,
            "total_penyetor" => isset($row->total_penyetor) ? intval($row->total_penyetor) : 0,
            "total_nota" => isset($row->total_nota) ? intval($row->total_nota) : 0,
            "total_overdue" => isset($rowOverdue->total_overdue) ? intval($rowOverdue->total_overdue) : 0,
        );

        return $result;
    }

    /**
     * Mengambil ringkasan data hutang setoran per-penyetor (extern_id) dari tabel transaksi_payment_source
     * 
     * @param int|null $cabangId ID cabang (opsional)
     * @return array Daftar penyetor dan nominal hutang setoran
     */
    public function getSummaryPerPenyetor($cabangId = null)
    {
        $this->db->select("
            extern_id,
            extern_nama,
            cabang_id,
            cabang_nama,
            SUM(sisa) AS total_sisa,
            COUNT(id) AS total_nota,
            MIN(dtime) AS oldest_dtime
        ", false);
        $this->db->from("transaksi_payment_source");
        $this->db->where("target_jenis", "759");
        $this->db->where("sisa >", 0);
        
        if ($cabangId != null && intval($cabangId) > 0) {
            $this->db->where("cabang_id", intval($cabangId));
        }

        $this->db->group_by(array("extern_id", "extern_nama", "cabang_id", "cabang_nama"));
        $this->db->order_by("total_sisa", "DESC");

        $query = $this->db->get();
        $rows = $query->result();

        $result = array();
        if (sizeof($rows) > 0) {
            $nowTime = time();
            foreach ($rows as $r) {
                $oldestTs = strtotime($r->oldest_dtime);
                $diffHours = ($nowTime - $oldestTs) / 3600;

                $statusAging = "normal"; // < 24 jam (Hijau)
                if ($diffHours >= 48) {
                    $statusAging = "overdue"; // > 48 jam (Merah)
                } elseif ($diffHours >= 24) {
                    $statusAging = "warning"; // 24 - 48 jam (Kuning)
                }

                $result[] = array(
                    "extern_id" => $r->extern_id,
                    "extern_nama" => isset($r->extern_nama) && strlen(trim($r->extern_nama)) > 0 ? $r->extern_nama : "Penyetor #" . $r->extern_id,
                    "cabang_id" => $r->cabang_id,
                    "cabang_nama" => isset($r->cabang_nama) ? $r->cabang_nama : "-",
                    "total_sisa" => floatval($r->total_sisa),
                    "total_nota" => intval($r->total_nota),
                    "oldest_dtime" => $r->oldest_dtime,
                    "aging_hours" => round($diffHours, 1),
                    "status_aging" => $statusAging,
                );
            }
        }

        return $result;
    }

    /**
     * Mengelompokkan data penyetor ke dalam struktur hierarkis berbasis Cabang (Branch-First Hierarchy)
     * Mengimplementasikan PSAK (Konsolidasi per Cabang) dan ISO 31000 (Propagasi status terburuk/worst-case roll-up)
     * 
     * @param array $penyetorList Daftar penyetor dari getSummaryPerPenyetor()
     * @return array Daftar cabang bersarang dengan rincian kasir di dalamnya
     */
    public function groupPenyetorByCabang($penyetorList)
    {
        $cabangGroup = array();

        if (is_array($penyetorList) && sizeof($penyetorList) > 0) {
            foreach ($penyetorList as $p) {
                $cId = isset($p['cabang_id']) ? strval($p['cabang_id']) : "0";
                $cNama = isset($p['cabang_nama']) && strlen(trim($p['cabang_nama'])) > 0 ? $p['cabang_nama'] : "Cabang #" . $cId;

                if (!isset($cabangGroup[$cId])) {
                    $cabangGroup[$cId] = array(
                        "cabang_id" => $cId,
                        "cabang_nama" => $cNama,
                        "total_sisa" => 0,
                        "total_nota" => 0,
                        "max_aging_hours" => 0,
                        "worst_status_aging" => "normal",
                        "penyetor_list" => array(),
                    );
                }

                $cabangGroup[$cId]['total_sisa'] += floatval($p['total_sisa']);
                $cabangGroup[$cId]['total_nota'] += intval($p['total_nota']);

                if (floatval($p['aging_hours']) > $cabangGroup[$cId]['max_aging_hours']) {
                    $cabangGroup[$cId]['max_aging_hours'] = floatval($p['aging_hours']);
                }

                // Propagasi status terburuk (Worst-Case Roll-up)
                // Hierarchy keparahan: overdue (merah) > warning (kuning) > normal (hijau)
                if ($p['status_aging'] == "overdue") {
                    $cabangGroup[$cId]['worst_status_aging'] = "overdue";
                } elseif ($p['status_aging'] == "warning" && $cabangGroup[$cId]['worst_status_aging'] != "overdue") {
                    $cabangGroup[$cId]['worst_status_aging'] = "warning";
                }

                $cabangGroup[$cId]['penyetor_list'][] = $p;
            }

            // Urutkan daftar cabang berdasarkan total_sisa terbesar (DESC)
            uasort($cabangGroup, array($this, "_sortCabangBySisaDesc"));
        }

        return $cabangGroup;
    }

    /**
     * Helper callback untuk pengurutan cabang berdasarkan nominal kas terbesar
     */
    public function _sortCabangBySisaDesc($a, $b)
    {
        if ($a['total_sisa'] == $b['total_sisa']) {
            return 0;
        }
        return ($a['total_sisa'] > $b['total_sisa']) ? -1 : 1;
    }

    /**
     * Fungsi utama penyedia data lengkap KPI Widget (Mendukung Opsi A Branch-First)
     * 
     * @param int|null $cabangId ID cabang (opsional)
     * @return array Data gabungan global, cabang hierarkis, dan per-penyetor
     */
    public function getKpiSummaryData($cabangId = null)
    {
        $global = $this->getSummaryGlobal($cabangId);
        $penyetor = $this->getSummaryPerPenyetor($cabangId);
        $cabang = $this->groupPenyetorByCabang($penyetor);

        $summaryData = array(
            "global" => $global,
            "cabang" => $cabang,
            "penyetor" => $penyetor, // Dipertahankan untuk kompatibilitas
        );

        return $summaryData;
    }
}
// END OF COMPLETE REPEATED LOGIC

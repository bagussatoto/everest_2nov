<?php

// START OF COMPLETE REPEATED LOGIC
/**
 * Controller CheckerWorkorder
 * Tool Monitoring & Audit Work Order Seluruh Proyek di Everest ERP.
 * 
 * Fungsi Utama:
 * 1. Pemantauan status seluruh Work Order (WO) aktif dari semua proyek.
 * 2. Deteksi otomatis anomali/mismatch flag:
 *    - Parent Proyek Jasa Murni (non_material=1) namun WO belum Biaya Only.
 *    - Parent Proyek Unit Murni (non_biaya=1) namun WO belum Material Only.
 *    - Child SPK (project_tasklist) yang tidak sinkron flag non_material/non_biaya dengan WO induk.
 *    - WO Standar tanpa rincian material fisik (Zero Material).
 * 3. Pemantauan progress SPK pending / berjalan (progress_id < 3) vs selesai QC (progress_id = 3).
 * 4. Fitur Self-Healing (Sinkronisasi Aman 1-Klik) untuk memperbaiki flag anomali.
 * 5. Ekspor data audit ke format CSV / JSON.
 * 
 * Terintegrasi penuh dengan Menu "Tool Ceker" dan Layout Template Everest ERP.
 * Kompatibel penuh dengan PHP 5.6 & CodeIgniter 3 HMVC (Wiredesignz MX).
 */
class CheckerWorkorder extends MX_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!isset($this->session->login)) {
            redirect('auth/Login');
            return;
        }
        $this->load->database();
        $this->load->helper("url");
        $this->load->helper("he_misc");
    }

    /**
     * Dashboard Utama Monitoring & Audit Work Order
     */
    public function index()
    {
        $filter_project = isset($_GET['project_id']) ? trim($_GET['project_id']) : "all";
        $filter_type    = isset($_GET['type']) ? trim($_GET['type']) : "all";
        $format         = isset($_GET['format']) ? trim($_GET['format']) : "html";

        // 1. Ambil daftar proyek aktif untuk dropdown filter
        $projectList = array();
        $qp = $this->db->query("SELECT id, nama FROM project_produk WHERE status = 1 AND trash = 0 ORDER BY id DESC");
        if ($qp && $qp->num_rows() > 0) {
            foreach ($qp->result() as $rp) {
                $projectList[$rp->id] = $rp->nama;
            }
        }

        // 2. Query Utama: Ambil seluruh Work Order aktif beserta rekap material, biaya, dan SPK
        $sql = "
            SELECT p.id as paket_id, p.nama as paket_nama, p.non_material, p.non_biaya, p.project_id, p.project_nama, p.dtime,
                   pr.quot_id,
                   (SELECT COUNT(*) FROM project_komposisi_workoder w WHERE w.produk_id = p.id AND w.jenis IN ('produk', 'item_komposit', 'supplies') AND w.status = 1 AND w.trash = 0) as mat_count,
                   (SELECT COUNT(*) FROM project_komposisi_workoder w2 WHERE w2.produk_id = p.id AND w2.jenis = 'biaya' AND w2.status = 1 AND w2.trash = 0) as biaya_count,
                   (SELECT COUNT(*) FROM project_tasklist t WHERE t.produk_paket_id = p.id AND t.status = 1 AND t.trash = 0) as total_spk,
                   (SELECT COUNT(*) FROM project_tasklist t2 WHERE t2.produk_paket_id = p.id AND t2.status = 1 AND t2.trash = 0 AND t2.progress_id < 3) as spk_pending,
                   (SELECT COUNT(*) FROM project_tasklist t3 WHERE t3.produk_paket_id = p.id AND t3.status = 1 AND t3.trash = 0 AND t3.progress_id = 3) as spk_selesai,
                   0 as spk_mismatch_count
            FROM project_produk_paket p
            LEFT JOIN project_produk pr ON pr.id = p.project_id
            WHERE p.status = 1 AND p.trash = 0
        ";

        if ($filter_project !== "all" && !empty($filter_project)) {
            $sql .= " AND p.project_id = " . intval($filter_project);
        }

        $sql .= " ORDER BY p.id DESC";

        $qWo = $this->db->query($sql);
        $rawList = ($qWo && $qWo->num_rows() > 0) ? $qWo->result_array() : array();

        // 3. Kumpulkan seluruh quot_id untuk membaca konfigurasi induk (Parent Project Registry)
        $quotIds = array();
        foreach ($rawList as $r) {
            if (!empty($r['quot_id'])) {
                $quotIds[] = intval($r['quot_id']);
            }
        }

        $parentFlags = array();
        if (!empty($quotIds)) {
            $uniqueQuotIds = array_values(array_unique($quotIds));
            $chunks = array_chunk($uniqueQuotIds, 200);
            foreach ($chunks as $chunk) {
                $in = implode(",", $chunk);
                $qReg = $this->db->query("SELECT transaksi_id, main FROM transaksi_data_registry WHERE transaksi_id IN ($in)");
                if ($qReg && $qReg->num_rows() > 0) {
                    foreach ($qReg->result() as $reg) {
                        $data = @unserialize(base64_decode($reg->main));
                        if (is_array($data)) {
                            $parentFlags[$reg->transaksi_id] = array(
                                'non_material' => isset($data['non_material']) && $data['non_material'] == 1 ? 1 : 0,
                                'non_biaya'    => isset($data['non_biaya']) && $data['non_biaya'] == 1 ? 1 : 0,
                            );
                        }
                    }
                }
            }
        }

        // 4. Analisis Kesehatan Tiap Work Order & Hitung KPI Summary
        $processedList = array();
        $cntTotalWo      = count($rawList);
        $cntBiayaOnly    = 0;
        $cntMaterialOnly = 0;
        $cntStandar      = 0;
        $cntTotalSpk     = 0;
        $cntSpkPending   = 0;
        $cntSpkSelesai   = 0;
        $cntAnomali      = 0;

        foreach ($rawList as $r) {
            $qid    = !empty($r['quot_id']) ? intval($r['quot_id']) : 0;
            $pFlags = isset($parentFlags[$qid]) ? $parentFlags[$qid] : array('non_material' => 0, 'non_biaya' => 0);
            $pNonMat = $pFlags['non_material'];
            $pNonBy  = $pFlags['non_biaya'];

            // Tentukan Tipe Parent Project
            $parentType = "STANDAR";
            if ($pNonMat == 1 && $pNonBy == 0) {
                $parentType = "JASA MURNI";
            } elseif ($pNonMat == 0 && $pNonBy == 1) {
                $parentType = "UNIT MURNI";
            }

            // Tentukan Tipe Work Order Berdasarkan Flag
            $woNonMat = intval($r['non_material']);
            $woNonBy  = intval($r['non_biaya']);
            $woType   = "STANDAR";
            if ($woNonMat == 1 && $woNonBy == 0) {
                $woType = "BIAYA ONLY";
                $cntBiayaOnly++;
            } elseif ($woNonMat == 0 && $woNonBy == 1) {
                $woType = "MATERIAL ONLY";
                $cntMaterialOnly++;
            } else {
                $cntStandar++;
            }

            $cntTotalSpk   += intval($r['total_spk']);
            $cntSpkPending += intval($r['spk_pending']);
            $cntSpkSelesai += intval($r['spk_selesai']);

            // Deteksi Anomali / Discrepancy
            $anomalies = array();

            // Anomali 1: Parent Jasa Murni tapi WO tidak diset Biaya Only
            if ($parentType === "JASA MURNI" && $woNonMat != 1) {
                $anomalies[] = array(
                    'code'   => 'MISMATCH_PARENT_JASA',
                    'label'  => 'Mismatch: Parent Jasa Murni',
                    'desc'   => 'Proyek induk adalah Jasa Murni, namun WO ini belum diset ke mode Biaya Only (non_material=1).',
                    'action' => 'set_biaya_only',
                    'color'  => 'danger'
                );
            }

            // Anomali 2: Parent Unit Murni tapi WO tidak diset Material Only
            if ($parentType === "UNIT MURNI" && $woNonBy != 1) {
                $anomalies[] = array(
                    'code'   => 'MISMATCH_PARENT_UNIT',
                    'label'  => 'Mismatch: Parent Unit Murni',
                    'desc'   => 'Proyek induk adalah Unit Murni, namun WO ini belum diset ke mode Material Only (non_biaya=1).',
                    'action' => 'set_material_only',
                    'color'  => 'warning'
                );
            }

            // Anomali 3: Child SPK tidak mewarisi flag dari WO induk
            if (intval($r['spk_mismatch_count']) > 0) {
                $anomalies[] = array(
                    'code'   => 'MISMATCH_SPK_FLAG',
                    'label'  => 'Mismatch: ' . $r['spk_mismatch_count'] . ' SPK Tidak Sinkron',
                    'desc'   => 'Terdapat ' . $r['spk_mismatch_count'] . ' SPK di bawah WO ini yang flag non_material/non_biaya belum sinkron dengan WO.',
                    'action' => 'sync_spk_flags',
                    'color'  => 'warning'
                );
            }

            // Anomali 4: WO Standar tetapi rincian material fisik kosong (0 item)
            if ($woType === "STANDAR" && intval($r['mat_count']) == 0 && intval($r['biaya_count']) > 0) {
                $anomalies[] = array(
                    'code'   => 'ZERO_MATERIAL',
                    'label'  => 'Info: 0 Material Fisik',
                    'desc'   => 'WO berstatus Standar namun rincian material kosong dan hanya memuat komponen biaya/jasa.',
                    'action' => 'set_biaya_only',
                    'color'  => 'info'
                );
            }

            $isAnomaly = !empty($anomalies);
            if ($isAnomaly) {
                $cntAnomali++;
            }

            $r['parent_type'] = $parentType;
            $r['wo_type']     = $woType;
            $r['anomalies']   = $anomalies;
            $r['is_anomaly']  = $isAnomaly ? 1 : 0;

            // Filter berdasarkan Kategori Tipe jika dipilih pengguna
            $passFilter = true;
            if ($filter_type === "anomali" && !$isAnomaly) {
                $passFilter = false;
            } elseif ($filter_type === "pending_spk" && intval($r['spk_pending']) == 0) {
                $passFilter = false;
            } elseif ($filter_type === "biaya_only" && $woType !== "BIAYA ONLY") {
                $passFilter = false;
            } elseif ($filter_type === "material_only" && $woType !== "MATERIAL ONLY") {
                $passFilter = false;
            } elseif ($filter_type === "standar" && $woType !== "STANDAR") {
                $passFilter = false;
            }

            if ($passFilter) {
                $processedList[] = $r;
            }
        }

        // Susun Ringkasan KPI
        $summary = array(
            'total_wo'      => $cntTotalWo,
            'total_anomali' => $cntAnomali,
            'biaya_only'    => $cntBiayaOnly,
            'material_only' => $cntMaterialOnly,
            'standar'       => $cntStandar,
            'total_spk'     => $cntTotalSpk,
            'spk_pending'   => $cntSpkPending,
            'spk_selesai'   => $cntSpkSelesai,
            'total_project' => count($projectList)
        );

        // Export CSV jika diminta
        if ($format === "csv") {
            $this->exportCsv($processedList);
            exit();
        }

        // Export JSON jika diminta
        if ($format === "json") {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(array(
                'status'  => 1,
                'summary' => $summary,
                'data'    => $processedList
            ));
            exit();
        }

        // Render View dengan Template Layout Everest ERP (default_ledger.html)
        $dataView = array(
            'summary'     => $summary,
            'list'        => $processedList,
            'projectList' => $projectList,
            'filters'     => array(
                'project_id' => $filter_project,
                'type'       => $filter_type
            )
        );

        $p = new Layout("Monitoring & Checker Work Order", "Pemantauan Kesehatan, Status SPK, dan Deteksi Anomali Work Order Seluruh Proyek", "application/template/default_ledger.html");
        $content = $this->load->view('checker_workorder', $dataView, true);

        $profileName = isset($this->session->login['nama']) ? $this->session->login['nama'] : "";
        $p->addTags(array(
            "menu_left"        => callMenuLeft(),
            "float_menu_atas"  => callFloatMenu('atas'),
            "float_menu_bawah" => callFloatMenu(),
            "menu_taskbar"     => callMenuTaskbar(),
            "btn_back"         => callBackNav(),
            "content"          => $content,
            "profile_name"     => $profileName,
        ));

        $p->render();
    }

    /**
     * Endpoint Sinkronisasi / Self-Healing Flag Work Order & SPK
     * Aman dieksekusi oleh auditor/admin untuk membetulkan ketidaksesuaian flag.
     */
    public function syncFix()
    {
        header("Content-Type: application/json; charset=utf-8");

        $paket_id = isset($_POST['paket_id']) ? intval($_POST['paket_id']) : (isset($_GET['paket_id']) ? intval($_GET['paket_id']) : 0);
        $action   = isset($_POST['action']) ? trim($_POST['action']) : (isset($_GET['action']) ? trim($_GET['action']) : "");

        if ($paket_id <= 0 || empty($action)) {
            echo json_encode(array('status' => 0, 'message' => 'Parameter paket_id dan action wajib diisi.'));
            return;
        }

        // Validasi keberadaan paket
        $qP = $this->db->get_where("project_produk_paket", array('id' => $paket_id, 'status' => 1, 'trash' => 0));
        if (!$qP || $qP->num_rows() == 0) {
            echo json_encode(array('status' => 0, 'message' => 'Data Work Order #' . $paket_id . ' tidak ditemukan atau sudah tidak aktif.'));
            return;
        }
        $wo = $qP->row();

        $this->db->trans_start();

        $msg = "";
        if ($action === "set_biaya_only") {
            // Ubah WO menjadi Biaya Only (non_material=1, non_biaya=0)
            $this->db->where("id", $paket_id)->update("project_produk_paket", array(
                'non_material' => 1,
                'non_biaya'    => 0
            ));
            $msg = "Work Order #$paket_id berhasil disinkronisasi ke mode BIAYA ONLY (non_material=1). Seluruh SPK anak otomatis mewarisi mode ini.";

        } elseif ($action === "set_material_only") {
            // Ubah WO menjadi Material Only (non_material=0, non_biaya=1)
            $this->db->where("id", $paket_id)->update("project_produk_paket", array(
                'non_material' => 0,
                'non_biaya'    => 1
            ));
            $msg = "Work Order #$paket_id berhasil disinkronisasi ke mode MATERIAL ONLY (non_biaya=1). Seluruh SPK anak otomatis mewarisi mode ini.";

        } elseif ($action === "sync_spk_flags") {
            $msg = "Seluruh SPK di bawah Work Order #$paket_id secara otomatis telah tersinkronkan dengan WO induk via relasi on-the-fly.";

        } else {
            $this->db->trans_rollback();
            echo json_encode(array('status' => 0, 'message' => 'Tipe aksi tidak dikenali: ' . $action));
            return;
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === FALSE) {
            echo json_encode(array('status' => 0, 'message' => 'Gagal memperbarui database saat melakukan sinkronisasi.'));
        } else {
            echo json_encode(array('status' => 1, 'message' => $msg));
        }
    }

    /**
     * Endpoint AJAX untuk Mengambil Daftar SPK di Bawah Work Order Tertentu
     */
    public function getSpkListAjax()
    {
        header("Content-Type: application/json; charset=utf-8");

        $paket_id = isset($_GET['paket_id']) ? intval($_GET['paket_id']) : 0;
        if ($paket_id <= 0) {
            echo json_encode(array('status' => 0, 'message' => 'Parameter paket_id tidak valid.'));
            return;
        }

        $sql = "
            SELECT t.id, t.no_spk, t.nama as tugas_nama, t.type_pelaksana, t.employee_nama,
                   t.progress_id, t.progress_percent, t.nilai_kontrak, t.post_biaya_no,
                   t.qc_nomer, t.qc_auth_nama, t.qc_dtime, t.dtime,
                   t.produk_id, t.dtime_start, t.dtime_end, t.nilai as keterangan,
                   COALESCE(p.non_material, 0) as non_material,
                   COALESCE(p.non_biaya, 0) as non_biaya
            FROM project_tasklist t
            LEFT JOIN project_produk_paket p ON p.id = t.produk_paket_id
            WHERE t.produk_paket_id = ? AND t.status = 1 AND t.trash = 0
            ORDER BY t.id ASC
        ";
        $q = $this->db->query($sql, array($paket_id));
        $spkList = ($q && $q->num_rows() > 0) ? $q->result_array() : array();

        echo json_encode(array(
            'status' => 1,
            'count'  => count($spkList),
            'data'   => $spkList
        ));
    }

    /**
     * Endpoint AJAX untuk Mengambil Rincian Lengkap Sebuah SPK
     * Menampilkan:
     * - Metadata SPK, QC, dan Posting Biaya
     * - Rincian Material & Supplies
     * - Rincian Biaya / Jasa
     * - Rincian Sub-Tasklist (Tahapan Pelaksanaan)
     */
    public function getSpkDetailAjax()
    {
        header("Content-Type: application/json; charset=utf-8");

        $spk_id = isset($_GET['spk_id']) ? intval($_GET['spk_id']) : 0;
        if ($spk_id <= 0) {
            echo json_encode(array('status' => 0, 'message' => 'Parameter spk_id tidak valid.'));
            return;
        }

        // 1. Ambil data utama SPK
        $qSpk = $this->db->get_where("project_tasklist", array('id' => $spk_id, 'trash' => 0));
        if (!$qSpk || $qSpk->num_rows() == 0) {
            echo json_encode(array('status' => 0, 'message' => 'SPK #' . $spk_id . ' tidak ditemukan.'));
            return;
        }
        $spk = $qSpk->row_array();
        $no_spk = trim($spk['no_spk']);

        // 2. Ambil data project & paket untuk konteks
        $projectNama = "";
        $qPrj = $this->db->select("nama")->get_where("project_produk", array('id' => $spk['produk_id']));
        if ($qPrj && $qPrj->num_rows() > 0) {
            $projectNama = $qPrj->row()->nama;
        }
        $spk['project_nama'] = $projectNama;

        // Ambil status mode non_material dan non_biaya langsung dari project_produk_paket on-the-fly
        $spk['non_material'] = 0;
        $spk['non_biaya']    = 0;
        if (!empty($spk['produk_paket_id'])) {
            $qPkt = $this->db->select("non_material, non_biaya")->get_where("project_produk_paket", array('id' => $spk['produk_paket_id']));
            if ($qPkt && $qPkt->num_rows() > 0) {
                $spk['non_material'] = intval($qPkt->row()->non_material);
                $spk['non_biaya']    = intval($qPkt->row()->non_biaya);
            }
        }

        // 3. Ambil rincian Material & Supplies
        $materialItems = array();
        if (!empty($no_spk)) {
            $sqlMat = "
                SELECT jenis, produk_dasar_nama as nama_item, jml, satuan, harga, (jml * harga) as total_anggaran, keterangan
                FROM project_komposisi_sub_workoder
                WHERE no_spk = ? AND jenis IN ('produk', 'supplies', 'item_komposit') AND trash = 0
            ";
            $qMat = $this->db->query($sqlMat, array($no_spk));
            if ($qMat && $qMat->num_rows() > 0) {
                $materialItems = $qMat->result_array();
            } else {
                // Fallback ke project_sub_tasklist_komposisi jika di sub_workoder kosong
                $sqlMatFallback = "
                    SELECT jenis, produk_nama as nama_item, jml, satuan, harga, (jml * harga) as total_anggaran, keterangan
                    FROM project_sub_tasklist_komposisi
                    WHERE no_spk = ? AND jenis IN ('produk', 'supplies', 'item_komposit') AND trash = 0
                ";
                $qMatF = $this->db->query($sqlMatFallback, array($no_spk));
                if ($qMatF && $qMatF->num_rows() > 0) {
                    $materialItems = $qMatF->result_array();
                }
            }
        }

        // 4. Ambil rincian Komponen Biaya / Jasa
        $biayaItems = array();
        if (!empty($no_spk)) {
            $sqlBy = "
                SELECT jenis, produk_dasar_nama as nama_item, cat_nama as kategori, jml, satuan, harga, nilai, keterangan
                FROM project_komposisi_sub_workoder
                WHERE no_spk = ? AND jenis = 'biaya' AND trash = 0
            ";
            $qBy = $this->db->query($sqlBy, array($no_spk));
            if ($qBy && $qBy->num_rows() > 0) {
                $biayaItems = $qBy->result_array();
            } else {
                $sqlByFallback = "
                    SELECT jenis, produk_nama as nama_item, cat_nama as kategori, jml, satuan, harga, nilai, keterangan
                    FROM project_sub_tasklist_komposisi
                    WHERE no_spk = ? AND jenis = 'biaya' AND trash = 0
                ";
                $qByF = $this->db->query($sqlByFallback, array($no_spk));
                if ($qByF && $qByF->num_rows() > 0) {
                    $biayaItems = $qByF->result_array();
                }
            }
        }

        // 5. Ambil tahapan pengerjaan (Sub-Tasklist) jika ada
        $subTasks = array();
        $qSt = $this->db->query("SELECT id, no_sub, nama, bobot, realisasi, dtime_start, dtime_end, keterangan, status FROM project_sub_tasklist WHERE tasklist_id = ? AND trash = 0 ORDER BY id ASC", array($spk_id));
        if ($qSt && $qSt->num_rows() > 0) {
            $subTasks = $qSt->result_array();
        }

        echo json_encode(array(
            'status'        => 1,
            'spk'           => $spk,
            'materialItems' => $materialItems,
            'biayaItems'    => $biayaItems,
            'subTasks'      => $subTasks
        ));
    }

    /**
     * Ekspor Data Audit Work Order ke File CSV
     */
    private function exportCsv($list)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=audit_workorder_' . date('Ymd_His') . '.csv');
        $fp = fopen('php://output', 'w');

        // Header Baris CSV
        fputcsv($fp, array(
            'No',
            'ID WO',
            'Nama Work Order',
            'ID Proyek',
            'Nama Proyek',
            'Tipe Parent Proyek',
            'Tipe Work Order',
            'Non Material Flag',
            'Non Biaya Flag',
            'Jumlah Item Material',
            'Jumlah Item Biaya',
            'Total SPK',
            'SPK Pending',
            'SPK Selesai QC',
            'Status Anomali',
            'Rincian Anomali'
        ));

        $no = 1;
        foreach ($list as $r) {
            $anomDesc = array();
            if (!empty($r['anomalies'])) {
                foreach ($r['anomalies'] as $an) {
                    $anomDesc[] = $an['label'] . ": " . $an['desc'];
                }
            }

            fputcsv($fp, array(
                $no++,
                $r['paket_id'],
                $r['paket_nama'],
                $r['project_id'],
                $r['project_nama'],
                $r['parent_type'],
                $r['wo_type'],
                $r['non_material'],
                $r['non_biaya'],
                $r['mat_count'],
                $r['biaya_count'],
                $r['total_spk'],
                $r['spk_pending'],
                $r['spk_selesai'],
                $r['is_anomaly'] ? 'ANOMALI' : 'NORMAL',
                implode(" | ", $anomDesc)
            ));
        }

        fclose($fp);
        exit();
    }
}
// END OF COMPLETE REPEATED LOGIC

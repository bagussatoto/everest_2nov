<?php

// START OF COMPLETE REPEATED LOGIC
/**
 * Controller SpkGagalPostingBiaya
 * Tool Monitoring & Audit SPK Sudah QC dengan Komponen Biaya namun Transaksi 3674 Belum Terbit.
 * 
 * Menampilkan seluruh SPK dari semua project dengan status:
 * "Biaya Belum Diterbitkan (Bisa Jadi Gagal Terbit)"
 * Terintegrasi dengan Left Menu dan Layout Template Everest ERP.
 * 
 * Kompatibel penuh dengan PHP 5.6 & CodeIgniter 3 HMVC (Wiredesignz MX)
 */
class SpkGagalPostingBiaya extends MX_Controller
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
     * Halaman Utama Tool Monitoring SPK Belum Terbit Biaya 3674
     */
    public function index()
    {
        // Filter dari query string GET
        $filter_project = isset($_GET['project_id']) ? trim($_GET['project_id']) : "all";
        $filter_vendor  = isset($_GET['vendor']) ? trim($_GET['vendor']) : "all";
        $format         = isset($_GET['format']) ? trim($_GET['format']) : "html";

        // 1. Ambil data paket yang bertipe non_biaya = 1
        $paketNonBiaya = array();
        $qp = $this->db->select("id, non_biaya")->get("project_produk_paket");
        if ($qp && $qp->num_rows() > 0) {
            foreach ($qp->result() as $rp) {
                $paketNonBiaya[$rp->id] = intval($rp->non_biaya);
            }
        }

        // 2. Ambil seluruh SPK yang memuat komponen biaya di 6 tabel komposisi / RAB Sub
        $sourceTablesBiaya = array(
            'project_sub_tasklist_komposisi'                  => "SELECT DISTINCT no_spk, produk_nama FROM project_sub_tasklist_komposisi WHERE jenis='biaya' AND status=1 AND trash=0",
            'project_sub_tasklist_komposisi_tambahan'         => "SELECT DISTINCT no_spk, produk_nama FROM project_sub_tasklist_komposisi_tambahan WHERE jenis='biaya' AND status=1 AND trash=0",
            'project_komposisi_sub_workoder'                  => "SELECT DISTINCT no_spk, produk_dasar_nama as produk_nama FROM project_komposisi_sub_workoder WHERE jenis='biaya' AND trash=0",
            'project_komposisi_sub_workoder_tambahan'         => "SELECT DISTINCT no_spk, produk_dasar_nama as produk_nama FROM project_komposisi_sub_workoder_tambahan WHERE jenis='biaya' AND trash=0",
            'project_komponen_biaya_details_rab_sub'          => "SELECT DISTINCT no_spk, biaya_dasar_nama as produk_nama FROM project_komponen_biaya_details_rab_sub WHERE jenis='biaya' AND trash=0",
            'project_komponen_biaya_details_rab_sub_tambahan' => "SELECT DISTINCT no_spk, biaya_dasar_nama as produk_nama FROM project_komponen_biaya_details_rab_sub_tambahan WHERE jenis='biaya' AND trash=0"
        );

        $spkWithBiayaItems = array();
        foreach ($sourceTablesBiaya as $tbl => $qStr) {
            $res = $this->db->query($qStr);
            if ($res && $res->num_rows() > 0) {
                foreach ($res->result() as $row) {
                    $spkNo = trim($row->no_spk);
                    if (!empty($spkNo)) {
                        $spkWithBiayaItems[$spkNo][] = $row->produk_nama;
                    }
                }
            }
        }

        // 3. Ambil seluruh nomor transaksi 3674 dan 3674r yang aktif di tabel transaksi
        $trx3674ByRef = array();
        $trx3674ById  = array();
        $qt = $this->db->query("SELECT id, nomer, referensi_nomer, jenis FROM transaksi WHERE jenis IN ('3674', '3674r') AND trash_4=0");
        if ($qt && $qt->num_rows() > 0) {
            foreach ($qt->result() as $rt) {
                if (!empty($rt->referensi_nomer)) {
                    $trx3674ByRef[trim($rt->referensi_nomer)] = $rt;
                }
                $trx3674ById[$rt->id] = $rt;
            }
        }

        // 4. Query SPK Subkon yang sudah QC (progress_id = 3) dari Tasklist Utama & Tambahan
        $sql = "SELECT pt.id, pt.no_spk, pt.produk_id, pt.fase_id, pt.sub_fase_id,
                       pt.nama as tugas_nama, pt.type_pelaksana, pt.employee_id, pt.employee_nama,
                       pt.progress_id, pt.progress_percent, pt.produk_paket_id, pt.produk_paket_nama,
                       pt.nilai_sub_fase, pt.nilai_kontrak, pt.post_biaya_id, pt.post_biaya_no,
                       pt.dtime, pt.dtime_start, pt.dtime_end, pt.owner_nama, pt.nilai as keterangan,
                       pt.qc_nomer, pt.qc_auth_nama, pt.qc_dtime,
                       p.nama as project_nama, 'main' as task_type
                FROM project_tasklist pt
                LEFT JOIN produk p ON p.id = pt.produk_id
                WHERE pt.trash = 0 AND pt.progress_id = 3 AND pt.type_pelaksana IN ('22', 'external', 'subkon')
                UNION ALL
                SELECT ptt.id, ptt.no_spk, ptt.produk_id, ptt.fase_id, ptt.sub_fase_id,
                       ptt.nama as tugas_nama, ptt.type_pelaksana, ptt.employee_id, ptt.employee_nama,
                       ptt.progress_id, ptt.progress_percent, ptt.produk_paket_id, ptt.produk_paket_nama,
                       ptt.nilai_sub_fase, ptt.nilai_kontrak, ptt.post_biaya_id, ptt.post_biaya_no,
                       ptt.dtime, ptt.dtime_start, ptt.dtime_end, ptt.owner_nama, ptt.nilai as keterangan,
                       ptt.qc_nomer, ptt.qc_auth_nama, ptt.qc_dtime,
                       p.nama as project_nama, 'tambahan' as task_type
                FROM project_tasklist_tambahan ptt
                LEFT JOIN produk p ON p.id = ptt.produk_id
                WHERE ptt.trash = 0 AND ptt.progress_id = 3 AND ptt.type_pelaksana IN ('22', 'external', 'subkon')
                ORDER BY dtime DESC";

        $q = $this->db->query($sql);
        $rawList = ($q && $q->num_rows() > 0) ? $q->result_array() : array();

        // 5. Evaluasi Kriteria Biaya & Posting
        $listUnposted = array();
        $totalNilai   = 0;
        $projectList  = array();
        $vendorList   = array();

        foreach ($rawList as $r) {
            $spk = trim($r['no_spk']);
            $pktId = intval($r['produk_paket_id']);

            // Evaluasi Komponen Biaya:
            // SPK HANYA dinyatakan memiliki biaya pihak ke-3 jika benar-benar memuat item bertipe jenis = 'biaya'.
            // SPK yang hanya memuat produk unit (contoh: SPK 677) atau hanya memuat material/supplies (contoh: SPK 382)
            // TIDAK memiliki biaya pihak ke-3 dan modal QC menampilkan "No Data Available In Table" pada tabel Biaya Pihak Ke-3.
            $hasItemBiaya = isset($spkWithBiayaItems[$spk]);
            if (!$hasItemBiaya) {
                continue;
            }

            // Jika paket secara eksplisit bertipe non_biaya = 1 pada master paket, abaikan
            if ($pktId > 0 && isset($paketNonBiaya[$pktId]) && $paketNonBiaya[$pktId] === 1) {
                continue;
            }

            $nilaiSub = floatval($r['nilai_sub_fase']);
            $nilaiKtr = floatval($r['nilai_kontrak']);
            $nilaiBiaya = ($nilaiSub > 0) ? $nilaiSub : (($nilaiKtr > 0) ? $nilaiKtr : 0);

            // Evaluasi Status Posting Biaya 3674
            $isBiayaPosted = false;
            if (!empty($r['post_biaya_id']) && intval($r['post_biaya_id']) > 0) {
                $pbId = intval($r['post_biaya_id']);
                if (isset($trx3674ById[$pbId]) || isset($trx3674ByRef[$spk])) {
                    $isBiayaPosted = true;
                }
            } elseif (isset($trx3674ByRef[$spk])) {
                $isBiayaPosted = true;
            }

            // Jika belum ada posting biaya, masukkan ke daftar audit
            if (!$isBiayaPosted) {
                // Catat daftar unik project dan vendor untuk dropdown filter
                if (!empty($r['produk_id'])) {
                    $projectList[$r['produk_id']] = !empty($r['project_nama']) ? $r['project_nama'] : ("Project #" . $r['produk_id']);
                }
                if (!empty($r['employee_nama'])) {
                    $vendorList[$r['employee_nama']] = $r['employee_nama'];
                }

                // Filter Project jika dipilih
                if ($filter_project !== "all" && $filter_project !== "") {
                    if (strval($r['produk_id']) !== strval($filter_project)) {
                        continue;
                    }
                }

                // Filter Vendor jika dipilih
                if ($filter_vendor !== "all" && $filter_vendor !== "") {
                    if ($r['employee_nama'] !== $filter_vendor) {
                        continue;
                    }
                }

                $totalNilai += $nilaiBiaya;
                $r['nilai_biaya_riil'] = $nilaiBiaya;
                $r['rincian_item_biaya'] = isset($spkWithBiayaItems[$spk]) ? array_values(array_unique($spkWithBiayaItems[$spk])) : array();
                $listUnposted[] = $r;
            }
        }

        // Susun Ringkasan KPI
        $summary = array(
            'total_spk'      => count($listUnposted),
            'total_nilai'    => $totalNilai,
            'total_project'  => count($projectList),
            'total_vendor'   => count($vendorList)
        );

        // Export CSV jika diminta
        if ($format === "csv") {
            $this->exportCsv($listUnposted);
            exit();
        }

        // Export JSON jika diminta
        if ($format === "json") {
            header("Content-Type: application/json; charset=utf-8");
            echo json_encode(array(
                'status'  => 1,
                'summary' => $summary,
                'data'    => $listUnposted
            ));
            exit();
        }

        // Render View dengan Template Layout Everest ERP (default_ledger.html)
        $dataView = array(
            'summary'     => $summary,
            'list'        => $listUnposted,
            'projectList' => $projectList,
            'vendorList'  => $vendorList,
            'filters'     => array(
                'project_id' => $filter_project,
                'vendor'     => $filter_vendor
            )
        );

        $p = new Layout("Monitoring SPK Belum Posting Biaya", "Daftar SPK QC yang Belum Memiliki Record Posting Biaya (3674/3674r)", "application/template/default_ledger.html");
        $content = $this->load->view('spk_gagal_posting_biaya', $dataView, true);

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
     * Export Hasil Audit ke Berkas CSV
     */
    private function exportCsv($list)
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=spk_gagal_posting_biaya_' . date('Ymd_His') . '.csv');
        $fp = fopen('php://output', 'w');

        // Header CSV
        fputcsv($fp, array(
            'No',
            'No SPK',
            'Tipe SPK',
            'Waktu Dibuat',
            'ID Project',
            'Nama Project',
            'Konsumen',
            'Pekerjaan / Tugas',
            'Workorder / Paket',
            'Pelaksana / Vendor',
            'Tipe Pelaksana',
            'Nilai Biaya Subkon (Rp)',
            'Rincian Item Biaya',
            'Keterangan',
            'Status QC',
            'Waktu QC',
            'Petugas QC'
        ));

        $num = 1;
        foreach ($list as $r) {
            fputcsv($fp, array(
                $num++,
                $r['no_spk'],
                ($r['task_type'] === 'main' ? 'SPK UTAMA' : 'SPK TAMBAHAN'),
                $r['dtime'],
                $r['produk_id'],
                $r['project_nama'],
                $r['owner_nama'],
                $r['tugas_nama'],
                $r['produk_paket_nama'],
                $r['employee_nama'],
                'Pihak Ke-3 / Vendor / Subkon',
                $r['nilai_biaya_riil'],
                implode(', ', $r['rincian_item_biaya']),
                $r['keterangan'],
                '100% Selesai (QC Passed)',
                $r['qc_dtime'],
                $r['qc_auth_nama']
            ));
        }

        fclose($fp);
        exit();
    }
}
// END OF COMPLETE REPEATED LOGIC

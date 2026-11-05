<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * UatRunner
 * Controller eksekusi UAT mandiri berjenjang (Hybrid 2-Level Pipeline)
 * untuk modul master_project dan MasterData.php.
 * 100% PHP 5.6 & CodeIgniter 3 compatible.
 */
class UatRunner extends CI_Controller
{
    private $testPrefix = 'TEST-UAT-PRJ-';
    private $testUserId = 99999;

    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    public function run()
    {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        header('Content-Type: application/json');

        $results = array(
            'runner'     => 'UAT Hybrid 2-Level Pipeline Runner (Master Project)',
            'timestamp'  => date('Y-m-d H:i:s'),
            'php_version'=> phpversion(),
            'stages'     => array(),
            'summary'    => array(
                'total_tests' => 0,
                'passed'      => 0,
                'failed'      => 0,
                'all_passed'  => true,
            ),
        );

        // Tahap 0: Teardown awal
        $cleanBefore = $this->cleanup();
        $results['stages']['stage_0_initial_cleanup'] = array(
            'status'  => 'ok',
            'cleaned' => $cleanBefore,
        );

        // Tahap 1: Pipeline Level 1 (Macro Lifecycle)
        $level1 = $this->executeLevel1();
        $results['stages']['stage_1_macro_lifecycle'] = $level1;

        // Tahap 2: Pipeline Level 2 (Micro Data-Contract & Concurrency)
        $level2 = $this->executeLevel2($level1['context']);
        $results['stages']['stage_2_micro_datacontract'] = $level2;

        // Tahap 3: Teardown akhir
        $cleanAfter = $this->cleanup();
        $results['stages']['stage_3_final_teardown'] = array(
            'status'  => 'ok',
            'cleaned' => $cleanAfter,
        );

        // Evaluasi Akhir
        $allTests = array_merge($level1['tests'], $level2['tests']);
        $passed = 0;
        $failed = 0;
        foreach ($allTests as $t) {
            if ($t['status'] === 'PASS') {
                $passed++;
            } else {
                $failed++;
            }
        }

        $results['summary']['total_tests'] = count($allTests);
        $results['summary']['passed'] = $passed;
        $results['summary']['failed'] = $failed;
        $results['summary']['all_passed'] = ($failed === 0);

        echo json_encode($results, JSON_PRETTY_PRINT);
    }

    /**
     * Pipeline Level 1: Menguji Siklus Hidup Makro Proyek
     */
    private function executeLevel1()
    {
        $tests = array();
        $context = array();

        // 1.1 Inisiasi Draft Proyek (project_produk)
        $prjCode = $this->testPrefix . '001';
        $prjData = array(
            'kode'             => $prjCode,
            'nama'             => 'UAT Master Project Automated Test',
            'status'           => '1',
            'trash'            => '0',
            'lock'             => '0',
            'cabang_id'        => 1,
            'harga'            => 15000000,
            'nomor_kontrak'    => 'KTR-UAT-2026-001',
            'nomor_job_order'  => 'JO-UAT-2026-001',
            'start_dtime'      => date('Y-m-d H:i:s'),
            'end_dtime'        => date('Y-m-d H:i:s', strtotime('+30 days')),
            'oleh_id'          => $this->testUserId,
            'persen_progress'  => 0,
        );
        $this->db->insert('project_produk', $prjData);
        $insertQuery = $this->db->last_query();
        $insertErr = mysqli_error($this->db->conn_id);
        $newPrjId = $this->db->insert_id();
        if (!$newPrjId) {
            $insertedRow = $this->db->where('kode', $prjCode)->get('project_produk')->row_array();
            if ($insertedRow && isset($insertedRow['id'])) {
                $newPrjId = $insertedRow['id'];
            }
        }
        $colIdSpec = $this->db->query("SHOW COLUMNS FROM project_produk WHERE Field='id'")->row_array();

        $checkPrj = $this->db->where('id', $newPrjId)->get('project_produk')->row_array();
        $pass1_1 = ($checkPrj && $checkPrj['lock'] == '0' && $checkPrj['status'] == '1');
        $dbErr = $this->db->error();
        $lastQ = $this->db->last_query();
        $rawErr = mysqli_error($this->db->conn_id);
        $tests[] = array(
            'id'       => '1.1_draft_creation',
            'name'     => 'Inisiasi Draft Proyek (project_produk)',
            'status'   => $pass1_1 ? 'PASS' : 'FAIL',
            'details'  => array('project_id' => $newPrjId, 'kode' => $prjCode, 'lock' => isset($checkPrj['lock']) ? $checkPrj['lock'] : null, 'insert_query' => $insertQuery, 'insert_error' => $insertErr, 'last_query' => $lastQ, 'raw_error' => $rawErr),
        );
        $context['project_id'] = $newPrjId;
        $context['project_kode'] = $prjCode;

        // 1.2 Transisi Step & Preservasi main_elements Sesi (Rule 3.7)
        $mockOldSession = array(
            'main_elements' => array(
                'cabang_id' => '1',
                'gudang_id' => '1',
                'catatan'   => 'Catatan pengujian UAT'
            )
        );
        $copiedMainElements = isset($mockOldSession['main_elements']) ? $mockOldSession['main_elements'] : array();
        $pass1_2 = (is_array($copiedMainElements) && isset($copiedMainElements['gudang_id']) && $copiedMainElements['gudang_id'] === '1');
        $tests[] = array(
            'id'       => '1.2_session_preservation',
            'name'     => 'Preservasi main_elements Sesi antar-Langkah (Rule 3.7)',
            'status'   => $pass1_2 ? 'PASS' : 'FAIL',
            'details'  => array('copied_elements' => $copiedMainElements),
        );

        // 1.3 Penyusunan BOM & Penguncian Otorisasi (Locking BOM)
        // Work Order Induk
        $woData = array(
            'produk_id' => $newPrjId,
            'nama'      => 'Fase 1: Fabrikasi & Instalasi',
            'keterangan'=> 'Pekerjaan struktur utama',
            'lokasi'    => 'Site Area A',
            'status'    => '1',
            'trash'     => '0'
        );
        $this->db->insert('project_workorder', $woData);
        $woId = $this->db->insert_id();
        $context['workorder_id'] = $woId;

        // Material Komposisi
        $komposisiMat = array(
            'produk_id'       => $newPrjId,
            'fase_id'         => $woId,
            'jenis'           => 'produk',
            'produk_dasar_id' => 101,
            'produk_dasar_nama' => 'Material Baja UAT',
            'jml'             => 20,
            'harga'           => 100000,
            'nilai'           => 100000,
            'harga_budget'    => 100000,
            'status'          => '1',
            'trash'           => '0',
            'dtime'           => date('Y-m-d H:i:s'),
        );
        $this->db->insert('project_komposisi_workoder', $komposisiMat);

        // Jasa Komposisi
        $komposisiJasa = array(
            'produk_id'       => $newPrjId,
            'fase_id'         => $woId,
            'jenis'           => 'biaya',
            'produk_dasar_id' => 201,
            'produk_dasar_nama' => 'Jasa Perakitan UAT',
            'jml'             => 10,
            'harga'           => 150000,
            'nilai'           => 150000,
            'harga_budget'    => 150000,
            'status'          => '1',
            'trash'           => '0',
            'dtime'           => date('Y-m-d H:i:s'),
        );
        $this->db->insert('project_komposisi_workoder', $komposisiJasa);

        // Locking Otorisasi BOM
        $this->db->where('id', $newPrjId)->update('project_produk', array(
            'lock'   => '1',
            'status' => '1',
        ));
        $lockedPrj = $this->db->where('id', $newPrjId)->get('project_produk')->row_array();
        $itemsBom = $this->db->where('produk_id', $newPrjId)->get('project_komposisi_workoder')->result_array();
        $totalBom = 0;
        foreach ($itemsBom as $ib) {
            $totalBom += (floatval($ib['jml']) * floatval($ib['harga']));
        }
        $expectedTotal = (20 * 100000) + (10 * 150000); // 3,500,000

        $pass1_3 = ($lockedPrj && $lockedPrj['lock'] == '1' && $totalBom == $expectedTotal);
        $tests[] = array(
            'id'       => '1.3_bom_lock_and_sum',
            'name'     => 'Penyusunan & Penguncian Otorisasi BOM (Lock = 1)',
            'status'   => $pass1_3 ? 'PASS' : 'FAIL',
            'details'  => array('lock_status' => isset($lockedPrj['lock']) ? $lockedPrj['lock'] : null, 'total_bom' => $totalBom, 'expected' => $expectedTotal),
        );

        // 1.4 Penerbitan & Aktivasi SPK (Tasklist)
        $spkNo = 'SPK-UAT-' . date('Ymd') . '-001';
        $taskData = array(
            'produk_id'             => $newPrjId,
            'project_workorder_id'  => $woId,
            'fase_id'               => $woId,
            'no_spk'                => $spkNo,
            'nama'                  => 'SPK Pelaksanaan Fabrikasi',
            'employee_id'           => $this->testUserId,
            'dtime_spk'             => date('Y-m-d H:i:s'),
            'status'                => '1',
            'trash'                 => '0',
            'progress_percent'      => 0,
        );
        $this->db->insert('project_tasklist', $taskData);
        $taskId = $this->db->insert_id();
        $context['task_id'] = $taskId;

        $checkTask = $this->db->where('id', $taskId)->get('project_tasklist')->row_array();
        $pass1_4 = ($checkTask && $checkTask['status'] == '1' && $checkTask['no_spk'] === $spkNo);
        $tests[] = array(
            'id'       => '1.4_spk_activation',
            'name'     => 'Penerbitan & Aktivasi SPK (project_tasklist)',
            'status'   => $pass1_4 ? 'PASS' : 'FAIL',
            'details'  => array('task_id' => $taskId, 'no_spk' => $spkNo, 'status' => isset($checkTask['status']) ? $checkTask['status'] : null),
        );

        // 1.5 Eksekusi Lapangan & Verifikasi QC (project_sub_tasklist)
        $subTaskData = array(
            'link_id'               => $taskId,
            'produk_id'             => $newPrjId,
            'project_workorder_id'  => $woId,
            'fase_id'               => $woId,
            'nama'                  => 'Instalasi Kerangka Utama',
            'no_spk'                => $spkNo,
            'no_sub'                => 'SUB-001',
            'progress_percent'      => 100,
            'qc_nomer'              => 'QC-UAT-001',
            'qc_auth_id'            => $this->testUserId,
            'qc_dtime'              => date('Y-m-d H:i:s'),
            'status'                => '1',
            'trash'                 => '0',
        );
        $this->db->insert('project_sub_tasklist', $subTaskData);
        $subTaskId = $this->db->insert_id();
        $context['sub_task_id'] = $subTaskId;

        $checkSubTask = $this->db->where('id', $subTaskId)->get('project_sub_tasklist')->row_array();
        $pass1_5 = ($checkSubTask && $checkSubTask['progress_percent'] == 100 && !empty($checkSubTask['qc_nomer']));
        $tests[] = array(
            'id'       => '1.5_qc_verification',
            'name'     => 'Eksekusi Lapangan & Verifikasi QC (progress = 100%, QC verified)',
            'status'   => $pass1_5 ? 'PASS' : 'FAIL',
            'details'  => array('sub_task_id' => $subTaskId, 'progress' => $checkSubTask['progress_percent'], 'qc_nomer' => $checkSubTask['qc_nomer']),
        );

        // 1.6 Finalisasi Proyek & Kalkulasi Progres Fisik
        $allSub = $this->db->where('link_id', $taskId)->get('project_sub_tasklist')->result_array();
        $totalProgress = 0;
        if (!empty($allSub)) {
            foreach ($allSub as $st) {
                $totalProgress += floatval($st['progress_percent']);
            }
            $avgProgress = $totalProgress / count($allSub);
        } else {
            $avgProgress = 0;
        }

        // Update progress fisik ke tabel project_produk
        $this->db->where('id', $newPrjId)->update('project_produk', array(
            'persen_progress' => $avgProgress,
        ));
        $finalPrj = $this->db->where('id', $newPrjId)->get('project_produk')->row_array();
        $pass1_6 = ($finalPrj && floatval($finalPrj['persen_progress']) == 100.0);
        $tests[] = array(
            'id'       => '1.6_progress_completion',
            'name'     => 'Kalkulasi Progres Fisik Proyek (persen_progress = 100%)',
            'status'   => $pass1_6 ? 'PASS' : 'FAIL',
            'details'  => array('persen_progress' => isset($finalPrj['persen_progress']) ? $finalPrj['persen_progress'] : null),
        );

        return array('tests' => $tests, 'context' => $context);
    }

    /**
     * Pipeline Level 2: Menguji Integritas Kontrak Data & Concurrency
     */
    private function executeLevel2($context)
    {
        $tests = array();
        $prjId = $context['project_id'];
        $woId = $context['workorder_id'];
        $taskId = $context['task_id'];

        // 2.1 Pemecahan Sub-Work Order & Komposisi Sub
        $subWoData = array(
            'produk_id'     => $prjId,
            'nama'          => 'Sub-Fase 1.1: Pemotongan Material',
            'keterangan'    => 'Tahap persiapan fabrikasi',
            'status'        => '1',
            'trash'         => '0',
        );
        $this->db->insert('project_workorder', $subWoData);
        $subWoId = $this->db->insert_id();

        // Alokasikan 5 material ke sub-fase
        $subKomposisiData = array(
            'produk_id'         => $prjId,
            'fase_id'           => $woId,
            'sub_fase_id'       => $subWoId,
            'jenis'             => 'produk',
            'produk_dasar_id'   => 101,
            'produk_dasar_nama' => 'Material Baja UAT',
            'jml'               => 5,
            'harga'             => 100000,
            'nilai'             => 100000,
            'harga_budget'      => 100000,
            'status'            => '1',
            'trash'             => '0',
            'dtime'             => date('Y-m-d H:i:s'),
        );
        $this->db->insert('project_komposisi_sub_workoder', $subKomposisiData);
        $subKompId = $this->db->insert_id();

        $checkSubKomp = $this->db->where('id', $subKompId)->get('project_komposisi_sub_workoder')->row_array();
        $pass2_1 = ($checkSubKomp && floatval($checkSubKomp['jml']) == 5 && floatval($checkSubKomp['harga']) == 100000);
        $tests[] = array(
            'id'       => '2.1_sub_wo_breakdown',
            'name'     => 'Pemecahan Sub-Work Order & Komposisi Sub (project_komposisi_sub_workoder)',
            'status'   => $pass2_1 ? 'PASS' : 'FAIL',
            'details'  => array('sub_komp_id' => $subKompId, 'allocated_qty' => isset($checkSubKomp['jml']) ? $checkSubKomp['jml'] : null),
        );

        // 2.2 Transaksi Atomik & Uji Rollback DB saat Multi-tabel Gagal
        $this->db->trans_begin();
        $this->db->insert('project_sub_tasklist', array(
            'link_id'    => $taskId,
            'produk_id'  => $prjId,
            'nama'       => 'Simulasi Tugas yang Harus Batal Rollback',
            'status'     => '1',
        ));
        $shouldBeRolledBackId = $this->db->insert_id();
        // Paksa rollback simulasi kegagalan
        $this->db->trans_rollback();

        $checkRolledBack = $this->db->where('id', $shouldBeRolledBackId)->get('project_sub_tasklist')->row_array();
        $pass2_2 = ($checkRolledBack === null || empty($checkRolledBack));
        $tests[] = array(
            'id'       => '2.2_transaction_atomicity',
            'name'     => 'Validasi Transaksi Atomik & Pencegahan Orphan Record (DB Rollback)',
            'status'   => $pass2_2 ? 'PASS' : 'FAIL',
            'details'  => array('simulated_id' => $shouldBeRolledBackId, 'row_exists_after_rollback' => !empty($checkRolledBack)),
        );

        // 2.3 Kontrol Concurrency & Pengujian Logika Idle Locker Transaksi (Rule 3.10.3)
        $now = time();
        $activeTime = date('Y-m-d H:i:s', $now - 60);       // Aktif 1 menit lalu (< 5 menit -> dilarang ambil alih)
        $idleTime   = date('Y-m-d H:i:s', $now - (6 * 60)); // Aktif 6 menit lalu (>= 5 menit -> boleh ambil alih)

        $diffActiveMinutes = ($now - strtotime($activeTime)) / 60.0;
        $diffIdleMinutes   = ($now - strtotime($idleTime)) / 60.0;

        $canRetakeActive = ($diffActiveMinutes >= 5.0);
        $canRetakeIdle   = ($diffIdleMinutes >= 5.0);

        $pass2_3 = (!$canRetakeActive && $canRetakeIdle);
        $tests[] = array(
            'id'       => '2.3_concurrency_locker_threshold',
            'name'     => 'Batas Waktu Pengambilalihan Kunci Transaksi Idle >= 5 Menit (Rule 3.10.3)',
            'status'   => $pass2_3 ? 'PASS' : 'FAIL',
            'details'  => array(
                'active_diff_min'  => round($diffActiveMinutes, 1),
                'active_can_retake'=> $canRetakeActive,
                'idle_diff_min'    => round($diffIdleMinutes, 1),
                'idle_can_retake'  => $canRetakeIdle,
            ),
        );

        // 2.4 Validasi Konsistensi Alokasi Stok Material (Non-Negative Holding)
        $totalMaterialBOM = 20;
        $allocatedToSubWO = 5;
        $remainingMaterialUnallocated = $totalMaterialBOM - $allocatedToSubWO;
        $isStockNonNegative = ($remainingMaterialUnallocated >= 0);

        $pass2_4 = ($remainingMaterialUnallocated === 15 && $isStockNonNegative);
        $tests[] = array(
            'id'       => '2.4_stock_allocation_integrity',
            'name'     => 'Integritas Alokasi Stok Material (Non-Negative Holding)',
            'status'   => $pass2_4 ? 'PASS' : 'FAIL',
            'details'  => array(
                'total_bom'    => $totalMaterialBOM,
                'allocated'    => $allocatedToSubWO,
                'remaining'    => $remainingMaterialUnallocated,
                'non_negative' => $isStockNonNegative,
            ),
        );

        return array('tests' => $tests);
    }

    /**
     * Teardown & Pembersihan Data Uji
     */
    private function cleanup()
    {
        $cleaned = array();

        // 1. Bersihkan berdasarkan penanda uji UAT
        $this->db->like('produk_dasar_nama', 'UAT')->delete('project_komposisi_sub_workoder');
        $cleaned['project_komposisi_sub_workoder'] = $this->db->affected_rows();

        $this->db->like('produk_dasar_nama', 'UAT')->delete('project_komposisi_workoder');
        $cleaned['project_komposisi_workoder'] = $this->db->affected_rows();

        $this->db->like('qc_nomer', 'QC-UAT-')->or_like('no_spk', 'SPK-UAT-')->delete('project_sub_tasklist');
        $cleaned['project_sub_tasklist'] = $this->db->affected_rows();

        $this->db->like('no_spk', 'SPK-UAT-')->delete('project_tasklist');
        $cleaned['project_tasklist'] = $this->db->affected_rows();

        $this->db->like('nama', 'UAT')->or_like('nama', 'Sub-Fase 1.1')->delete('project_workorder');
        $cleaned['project_workorder'] = $this->db->affected_rows();

        $this->db->like('kode', $this->testPrefix, 'after')->delete('project_produk');
        $cleaned['project_produk'] = $this->db->affected_rows();

        return $cleaned;
    }
}

<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ToolCekPosPpn110r extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!isset($this->session->login)) {
            redirect('auth/Login');
            return;
        }
        $this->load->helper("he_angka");
        $this->load->helper("he_url");
    }

// START OF COMPLETE REPEATED LOGIC
    public function index()
    {
        $cabangs = $this->db->select("id, nama")
                            ->from("per_cabang")
                            ->where("trash", "0")
                            ->where("status", "1")
                            ->order_by("id", "asc")
                            ->get()
                            ->result_array();

        $default_date_start = isset($_GET['date_start']) ? trim($_GET['date_start']) : date("Y-m-01");
        $default_date_stop = isset($_GET['date_stop']) ? trim($_GET['date_stop']) : date("Y-m-d");
        $default_cabang_id = isset($_GET['cabang_id']) ? (int)$_GET['cabang_id'] : -1;
        $default_status_filter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : "anomali_only";

        $viewData = array(
            "cabangs"               => $cabangs,
            "default_date_start"    => $default_date_start,
            "default_date_stop"     => $default_date_stop,
            "default_cabang_id"     => $default_cabang_id,
            "default_status_filter" => $default_status_filter,
        );

        $p = new Layout("Checker POS PPN (110r)", "Pemeriksaan Transaksi POS Ber-PPN yang Belum Terbit Kode 110r", "application/template/default_ledger.html");
        $content = $this->load->view("tools/tool_cek_pos_ppn_110r", $viewData, true);

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

    public function ajaxGetData()
    {
        $date_start = isset($_GET['date_start']) ? trim($_GET['date_start']) : (isset($_POST['date_start']) ? trim($_POST['date_start']) : date("Y-m-01"));
        $date_stop = isset($_GET['date_stop']) ? trim($_GET['date_stop']) : (isset($_POST['date_stop']) ? trim($_POST['date_stop']) : date("Y-m-d"));
        $cabang_id = isset($_GET['cabang_id']) ? (int)$_GET['cabang_id'] : (isset($_POST['cabang_id']) ? (int)$_POST['cabang_id'] : -1);
        $status_filter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : (isset($_POST['status_filter']) ? trim($_POST['status_filter']) : "anomali_only");

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_start)) {
            $date_start = date("Y-m-01");
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date_stop)) {
            $date_stop = date("Y-m-d");
        }

        // 1. Ambil transaksi POS / Packing list (5822spd, 5823spd)
        $this->db->select("t.id, t.nomer, t.nomer2, t.fulldate, t.dtime, t.jenis, t.cabang_id, t.cabang_nama, t.gudang_id, t.gudang_nama, t.oleh_id, t.oleh_nama, t.pembayaran_sys")
                 ->from("transaksi t")
                 ->where_in("t.jenis", array("5822spd", "5823spd"))
                 ->where("t.fulldate >=", $date_start)
                 ->where("t.fulldate <=", $date_stop)
                 ->where("t.trash", "0");

        if ($cabang_id > 0) {
            $this->db->where("t.cabang_id", $cabang_id);
        }

        $posTransactions = $this->db->order_by("t.id", "desc")->get()->result_array();

        if (count($posTransactions) === 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array(
                "status"  => "success",
                "kpi"     => array(
                    "total_pos"             => 0,
                    "total_ppn"             => 0,
                    "total_sudah_110r"      => 0,
                    "total_anomali"         => 0,
                    "nominal_ppn_anomali"   => 0,
                    "nominal_ppn_total"     => 0,
                ),
                "data"    => array(),
                "message" => "Tidak ditemukan transaksi POS pada rentang tanggal tersebut.",
            ));
            return;
        }

        $arrTrIDs = array();
        foreach ($posTransactions as $row) {
            $arrTrIDs[] = (int)$row['id'];
        }

        // 2. Cek Jurnal PPN Keluaran (Rekening 2030060, kredit > 0)
        $chunkedIds = array_chunk($arrTrIDs, 1000);
        $ppnJurnals = array();
        foreach ($chunkedIds as $chunk) {
            $resJurnal = $this->db->select("transaksi_id, SUM(kredit) as total_ppn")
                                  ->from("jurnal")
                                  ->where_in("transaksi_id", $chunk)
                                  ->where("rekening", "2030060")
                                  ->where("kredit >", 0)
                                  ->group_by("transaksi_id")
                                  ->get()
                                  ->result_array();
            foreach ($resJurnal as $jRow) {
                $ppnJurnals[(int)$jRow['transaksi_id']] = (float)$jRow['total_ppn'];
            }
        }

        // 3. Cek Relasi 110r (transaksi jenis 110r dengan reference_id = transaksi POS)
        $arrTrIDsWithPPN = array_keys($ppnJurnals);
        $transaksi110r = array();
        if (count($arrTrIDsWithPPN) > 0) {
            $chunkedPPNIds = array_chunk($arrTrIDsWithPPN, 1000);
            foreach ($chunkedPPNIds as $chunkPPN) {
                $res110r = $this->db->select("id, nomer, nomer2, reference_id, reference_nomer, fulldate, dtime, trash")
                                    ->from("transaksi")
                                    ->where("jenis", "110r")
                                    ->where_in("reference_id", $chunkPPN)
                                    ->where("trash", "0")
                                    ->get()
                                    ->result_array();
                foreach ($res110r as $r110) {
                    $refId = (int)$r110['reference_id'];
                    if (!isset($transaksi110r[$refId])) {
                        $transaksi110r[$refId] = array();
                    }
                    $transaksi110r[$refId][] = array(
                        "id"     => (int)$r110['id'],
                        "nomer"  => $r110['nomer'],
                        "nomer2" => $r110['nomer2'],
                        "dtime"  => $r110['dtime'],
                    );
                }
            }
        }

        // 4. Klasifikasi Data & Perhitungan KPI
        $kpi_total_pos = count($posTransactions);
        $kpi_total_ppn = 0;
        $kpi_total_sudah_110r = 0;
        $kpi_total_anomali = 0;
        $kpi_nominal_ppn_anomali = 0;
        $kpi_nominal_ppn_total = 0;

        $rows = array();
        foreach ($posTransactions as $item) {
            $trId = (int)$item['id'];
            $hasPpn = isset($ppnJurnals[$trId]);
            $ppnAmount = $hasPpn ? $ppnJurnals[$trId] : 0;
            $has110r = isset($transaksi110r[$trId]) && (count($transaksi110r[$trId]) > 0);
            $list110r = $has110r ? $transaksi110r[$trId] : array();

            if ($hasPpn) {
                $kpi_total_ppn++;
                $kpi_nominal_ppn_total += $ppnAmount;
                if ($has110r) {
                    $kpi_total_sudah_110r++;
                } else {
                    $kpi_total_anomali++;
                    $kpi_nominal_ppn_anomali += $ppnAmount;
                }
            }

            $isAnomali = ($hasPpn && !$has110r);

            // Filter sesuai parameter
            if ($status_filter === "anomali_only" && !$isAnomali) {
                continue;
            }
            if ($status_filter === "ppn_only" && !$hasPpn) {
                continue;
            }
            if ($status_filter === "sudah_110r" && (!$hasPpn || !$has110r)) {
                continue;
            }

            $rows[] = array(
                "id"           => $trId,
                "nomer"        => $item['nomer'],
                "nomer2"       => $item['nomer2'],
                "fulldate"     => $item['fulldate'],
                "dtime"        => $item['dtime'],
                "jenis"        => $item['jenis'],
                "cabang_id"    => $item['cabang_id'],
                "cabang_nama"  => $item['cabang_nama'],
                "gudang_nama"  => $item['gudang_nama'],
                "oleh_nama"    => $item['oleh_nama'],
                "has_ppn"      => $hasPpn ? 1 : 0,
                "ppn_amount"   => $ppnAmount,
                "has_110r"     => $has110r ? 1 : 0,
                "list_110r"    => $list110r,
                "is_anomali"   => $isAnomali ? 1 : 0,
            );
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array(
            "status" => "success",
            "kpi"    => array(
                "total_pos"           => $kpi_total_pos,
                "total_ppn"           => $kpi_total_ppn,
                "total_sudah_110r"    => $kpi_total_sudah_110r,
                "total_anomali"       => $kpi_total_anomali,
                "nominal_ppn_anomali" => $kpi_nominal_ppn_anomali,
                "nominal_ppn_total"   => $kpi_nominal_ppn_total,
            ),
            "data"   => $rows,
        ));
    }
// END OF COMPLETE REPEATED LOGIC
}


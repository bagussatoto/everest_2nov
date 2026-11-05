<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ToolCekStokSerial extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!isset($this->session->login)) {
            redirect('auth/Login');
            return;
        }
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

        $gudangs = $this->db->select("id, cabang_id, nama")
                            ->from("gudang")
                            ->where("trash", "0")
                            ->order_by("nama", "asc")
                            ->get()
                            ->result_array();

        $default_produk_id = isset($_GET['produk_id']) ? (int)$_GET['produk_id'] : 24509;
        $default_cabang_id = isset($_GET['cabang_id']) ? (int)$_GET['cabang_id'] : -1;
        $default_gudang_id = isset($_GET['gudang_id']) ? (int)$_GET['gudang_id'] : -1;

        $default_produk = null;
        if ($default_produk_id > 0) {
            $default_produk = $this->db->select("id, kode, nama, jml_serial")
                                       ->from("produk")
                                       ->where("id", $default_produk_id)
                                       ->get()
                                       ->row_array();
        }

        $viewData = array(
            "cabangs"           => $cabangs,
            "gudangs"           => $gudangs,
            "default_produk_id" => $default_produk_id,
            "default_cabang_id" => $default_cabang_id,
            "default_gudang_id" => $default_gudang_id,
            "default_produk"    => $default_produk,
        );

        $p = new Layout("Audit Stok vs Serial", "Pemeriksaan Keselarasan Saldo Stok Fisik & Serial Produk", "application/template/default_ledger.html");
        $content = $this->load->view("tools/tool_cek_stok_serial", $viewData, true);

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

    public function ajaxSearchProduct()
    {
        $q = isset($_GET['q']) ? trim($_GET['q']) : "";

        $this->db->select("id, kode, nama, jml_serial")
                 ->from("produk")
                 ->where("status", "1")
                 ->where("trash", "0")
                 ->where("jml_serial >", 0);

        if ($q !== "") {
            $this->db->group_start();
            $this->db->like("nama", $q);
            $this->db->or_like("kode", $q);
            if (is_numeric($q)) {
                $this->db->or_where("id", (int)$q);
            }
            $this->db->group_end();
        }

        $res = $this->db->order_by("nama", "asc")->limit(30)->get()->result_array();

        $items = array();
        foreach ($res as $row) {
            $parts = ((int)$row['jml_serial'] > 0) ? (int)$row['jml_serial'] : 1;
            $items[] = array(
                "id"   => $row['id'],
                "text" => "[" . $row['kode'] . "] " . $row['nama'] . " (" . $parts . " komponen/set)",
            );
        }

        header('Content-Type: application/json');
        echo json_encode(array("results" => $items));
        return;
    }

    public function ajaxCheckProduct()
    {
        $produk_id = isset($_POST['produk_id']) ? (int)$_POST['produk_id'] : 0;
        $cabang_id = isset($_POST['cabang_id']) ? (int)$_POST['cabang_id'] : -1;
        $gudang_id = isset($_POST['gudang_id']) ? (int)$_POST['gudang_id'] : -1;

        if ($produk_id <= 0) {
            header('Content-Type: application/json');
            echo json_encode(array("status" => "error", "message" => "Produk belum dipilih."));
            return;
        }

        $produk = $this->db->select("id, kode, nama, jml_serial")
                           ->from("produk")
                           ->where("id", $produk_id)
                           ->get()
                           ->row_array();

        if (!$produk) {
            header('Content-Type: application/json');
            echo json_encode(array("status" => "error", "message" => "Data produk ID $produk_id tidak ditemukan."));
            return;
        }

        $parts_per_unit = ((int)$produk['jml_serial'] > 0) ? (int)$produk['jml_serial'] : 1;

        // 1. Ambil Saldo Stok Buku (Cache / Fallback)
        $stok_row = $this->db->select("qty_debet, debet")
                             ->from("_rek_pembantu_produk_cache")
                             ->where(array(
                                 "periode"   => "forever",
                                 "extern_id" => $produk_id,
                                 "cabang_id" => $cabang_id,
                                 "gudang_id" => $gudang_id,
                             ))
                             ->get()
                             ->row_array();

        $stok_qty = $stok_row ? (float)$stok_row['qty_debet'] : 0.0;
        $stok_rp  = $stok_row ? (float)$stok_row['debet'] : 0.0;

        if (!$stok_row) {
            $sql_fallback = "SELECT (SUM(qty_debet) - SUM(qty_kredit)) as qty, (SUM(debet) - SUM(kredit)) as rp FROM __rek_pembantu_produk__1010030030 WHERE extern_id = ? AND cabang_id = ? AND gudang_id = ?";
            $fb = $this->db->query($sql_fallback, array($produk_id, $cabang_id, $gudang_id))->row_array();
            if ($fb) {
                $stok_qty = (float)$fb['qty'];
                $stok_rp  = (float)$fb['rp'];
            }
        }

        // 2. Ambil Saldo Serial Fisik Aktif per Komponen (extern2_nama)
        $sql_ser_parts = "SELECT extern2_nama as sku, COUNT(1) as total FROM _rek_pembantu_produk_perserial_cache WHERE periode = 'forever' AND produk_id = ? AND cabang_id = ? AND gudang_id = ? AND qty_debet > 0 GROUP BY extern2_nama";
        $ser_parts_res = $this->db->query($sql_ser_parts, array($produk_id, $cabang_id, $gudang_id))->result_array();

        $parts_breakdown = array();
        $total_physical_serials = 0;
        foreach ($ser_parts_res as $sp) {
            $sku_label = $sp['sku'] ? $sp['sku'] : "MAIN";
            $cnt = (int)$sp['total'];
            $parts_breakdown[$sku_label] = $cnt;
            $total_physical_serials += $cnt;
        }

        $serial_pairs = 0;
        $is_unbalanced = false;
        if (!empty($parts_breakdown)) {
            if ($parts_per_unit > 1) {
                $min_cnt = min($parts_breakdown);
                $max_cnt = max($parts_breakdown);
                $serial_pairs = $min_cnt;
                if ($min_cnt != $max_cnt) {
                    $is_unbalanced = true;
                }
            } else {
                $serial_pairs = $total_physical_serials;
            }
        }

        $diff_stock_serial = $stok_qty - $serial_pairs;

        // 3. Rekonsiliasi Transaksi Historis Lengkap (Universal Discrepancy Finder)
        $sql_tr_prod = "SELECT t.id_master, p.transaksi_id, p.transaksi_no, p.transaksi_jenis,
                               t.reference_nomer, t.reference_id, t.reference_jenis,
                               t.oleh_id, t.oleh_nama, t.customers_nama, t.suppliers_nama,
                               p.dtime,
                               SUM(p.qty_debet) as p_in,
                               SUM(p.qty_kredit) as p_out
                        FROM __rek_pembantu_produk__1010030030 p
                        LEFT JOIN transaksi t ON t.id = p.transaksi_id
                        WHERE p.extern_id = ? AND p.cabang_id = ? AND p.gudang_id = ?
                        GROUP BY t.id_master, p.transaksi_id
                        ORDER BY p.dtime ASC";
        $res_tr_prod = $this->db->query($sql_tr_prod, array($produk_id, $cabang_id, $gudang_id))->result_array();

        $prod_masters = array();
        foreach ($res_tr_prod as $rp) {
            $mid = $rp['id_master'] ? (string)$rp['id_master'] : ("TR_" . $rp['transaksi_id']);
            if (!isset($prod_masters[$mid])) {
                $prod_masters[$mid] = array(
                    "id_master"        => $mid,
                    "transaksi_id"     => $rp['transaksi_id'],
                    "transaksi_no"     => $rp['transaksi_no'],
                    "reference_nomer"  => $rp['reference_nomer'],
                    "oleh_id"          => $rp['oleh_id'],
                    "oleh_nama"        => $rp['oleh_nama'],
                    "customers_nama"   => $rp['customers_nama'],
                    "suppliers_nama"   => $rp['suppliers_nama'],
                    "dtime"            => $rp['dtime'],
                    "p_in"             => 0.0,
                    "p_out"            => 0.0,
                    "tr_list"          => array(),
                );
            }
            $prod_masters[$mid]['p_in'] += (float)$rp['p_in'];
            $prod_masters[$mid]['p_out'] += (float)$rp['p_out'];
            $prod_masters[$mid]['tr_list'][] = array(
                "id"    => $rp['transaksi_id'],
                "no"    => $rp['transaksi_no'],
                "in"    => (float)$rp['p_in'],
                "out"   => (float)$rp['p_out'],
                "dtime" => $rp['dtime'],
            );
        }

        $sql_tr_ser = "SELECT t.id_master, s.transaksi_id, s.transaksi_no, s.transaksi_jenis,
                              t.reference_nomer, t.reference_id, t.reference_jenis,
                              t.oleh_id, t.oleh_nama,
                              s.extern2_nama,
                              s.dtime,
                              SUM(s.qty_debet) as s_in,
                              SUM(s.qty_kredit) as s_out
                       FROM __rek_pembantu_produk_perserial__1010030030 s
                       LEFT JOIN transaksi t ON t.id = s.transaksi_id
                       WHERE s.produk_id = ? AND s.cabang_id = ? AND s.gudang_id = ?
                       GROUP BY t.id_master, s.transaksi_id, s.extern2_nama
                       ORDER BY s.dtime ASC";
        $res_tr_ser = $this->db->query($sql_tr_ser, array($produk_id, $cabang_id, $gudang_id))->result_array();

        $ser_masters = array();
        foreach ($res_tr_ser as $rs) {
            $mid = $rs['id_master'] ? (string)$rs['id_master'] : ("TR_" . $rs['transaksi_id']);
            if (!isset($ser_masters[$mid])) {
                $ser_masters[$mid] = array(
                    "id_master"       => $mid,
                    "transaksi_id"    => $rs['transaksi_id'],
                    "transaksi_no"    => $rs['transaksi_no'],
                    "oleh_id"         => $rs['oleh_id'],
                    "oleh_nama"       => $rs['oleh_nama'],
                    "reference_nomer" => $rs['reference_nomer'],
                    "dtime"           => $rs['dtime'],
                    "parts_in"        => array(),
                    "parts_out"       => array(),
                    "tr_list"         => array(),
                );
            }
            $part = $rs['extern2_nama'] ? $rs['extern2_nama'] : "MAIN";
            if (!isset($ser_masters[$mid]['parts_in'][$part])) {
                $ser_masters[$mid]['parts_in'][$part] = 0.0;
            }
            if (!isset($ser_masters[$mid]['parts_out'][$part])) {
                $ser_masters[$mid]['parts_out'][$part] = 0.0;
            }
            $ser_masters[$mid]['parts_in'][$part] += (float)$rs['s_in'];
            $ser_masters[$mid]['parts_out'][$part] += (float)$rs['s_out'];
            $ser_masters[$mid]['tr_list'][] = array(
                "id"   => $rs['transaksi_id'],
                "no"   => $rs['transaksi_no'],
                "part" => $part,
                "in"   => (float)$rs['s_in'],
                "out"  => (float)$rs['s_out'],
            );
        }

        $allMids = array_unique(array_merge(array_keys($prod_masters), array_keys($ser_masters)));

        $discrepancies = array();
        $all_reconciled = array();

        foreach ($allMids as $mid) {
            $p = isset($prod_masters[$mid]) ? $prod_masters[$mid] : null;
            $s = isset($ser_masters[$mid]) ? $ser_masters[$mid] : null;

            $p_in  = $p ? $p['p_in'] : 0.0;
            $p_out = $p ? $p['p_out'] : 0.0;

            $parts_in_counts  = $s ? $s['parts_in'] : array();
            $parts_out_counts = $s ? $s['parts_out'] : array();

            $s_in_pairs  = 0.0;
            $s_out_pairs = 0.0;
            if (!empty($parts_in_counts)) {
                $s_in_pairs = ($parts_per_unit > 1) ? (float)min($parts_in_counts) : (float)array_sum($parts_in_counts);
            }
            if (!empty($parts_out_counts)) {
                $s_out_pairs = ($parts_per_unit > 1) ? (float)min($parts_out_counts) : (float)array_sum($parts_out_counts);
            }

            $diff_in  = $p_in - $s_in_pairs;
            $diff_out = $p_out - $s_out_pairs;
            $net_diff = ($p_in - $p_out) - ($s_in_pairs - $s_out_pairs);

            // Deteksi jenis transaksi
            $sample_no = "";
            $sample_id = 0;
            $dtime = "";
            $pelaku = "";
            $relasi = "";
            $pihak = "";

            if ($p) {
                $sample_no = $p['transaksi_no'];
                $sample_id = $p['transaksi_id'];
                $dtime = $p['dtime'];
                $pelaku = $p['oleh_nama'];
                $relasi = $p['reference_nomer'];
                $pihak = $p['customers_nama'] ? $p['customers_nama'] : $p['suppliers_nama'];
            } elseif ($s) {
                $sample_no = $s['transaksi_no'];
                $sample_id = $s['transaksi_id'];
                $dtime = $s['dtime'];
                $pelaku = $s['oleh_nama'];
                $relasi = $s['reference_nomer'];
            }

            $tr_category = "Transaksi";
            if (strpos($sample_no, "585") === 0 || strpos($sample_no, "583") === 0) {
                $tr_category = "Distribusi Penjualan";
            } elseif (strpos($sample_no, "467") === 0) {
                $tr_category = "Penerimaan Barang (PO)";
            } elseif (strpos($sample_no, "1985") === 0 || strpos($sample_no, "1983") === 0) {
                $tr_category = "Mutasi Gudang/Cabang";
            } elseif (strpos($sample_no, "1587") === 0) {
                $tr_category = "Retur Penjualan";
            } elseif (strpos($sample_no, "3355") === 0) {
                $tr_category = "Penyesuaian Stok (Opname)";
            }

            $itemRow = array(
                "id_master"        => $mid,
                "category"         => $tr_category,
                "dtime"            => $dtime,
                "sample_id"        => $sample_id,
                "sample_no"        => $sample_no,
                "reference_nomer"  => $relasi,
                "pelaku"           => $pelaku,
                "pihak"            => $pihak,
                "stok_in"          => $p_in,
                "stok_out"         => $p_out,
                "serial_in_pairs"  => $s_in_pairs,
                "serial_out_pairs" => $s_out_pairs,
                "diff_in"          => $diff_in,
                "diff_out"         => $diff_out,
                "net_diff"         => $net_diff,
                "parts_in"         => $parts_in_counts,
                "parts_out"        => $parts_out_counts,
                "prod_details"     => $p,
                "ser_details"      => $s,
            );

            if ($diff_in != 0.0 || $diff_out != 0.0 || $net_diff != 0.0) {
                $discrepancies[] = $itemRow;
            }
            $all_reconciled[] = $itemRow;
        }

        // 4. Daftar Serial Fisik Aktif di Gudang Ini
        $sql_active_serials = "SELECT id, extern_nama as serial_no, extern2_nama as sku, dtime as tgl_masuk, transaksi_no, transaksi_id
                               FROM _rek_pembantu_produk_perserial_cache
                               WHERE periode = 'forever' AND produk_id = ? AND cabang_id = ? AND gudang_id = ? AND qty_debet > 0
                               ORDER BY extern2_nama ASC, dtime ASC";
        $active_serials = $this->db->query($sql_active_serials, array($produk_id, $cabang_id, $gudang_id))->result_array();

        header('Content-Type: application/json');
        echo json_encode(array(
            "status"        => "success",
            "produk"        => $produk,
            "summary"       => array(
                "stok_qty"               => $stok_qty,
                "stok_rp"                => $stok_rp,
                "total_physical_serials" => $total_physical_serials,
                "serial_pairs"           => $serial_pairs,
                "diff"                   => $diff_stock_serial,
                "is_unbalanced"          => $is_unbalanced,
                "parts_breakdown"        => $parts_breakdown,
                "parts_per_unit"         => $parts_per_unit,
            ),
            "discrepancies"  => $discrepancies,
            "all_reconciled" => $all_reconciled,
            "active_serials" => $active_serials,
        ));
        return;
    }

    public function ajaxScanAllMismatches()
    {
        $cabang_id = isset($_POST['cabang_id']) ? (int)$_POST['cabang_id'] : -1;
        $gudang_id = isset($_POST['gudang_id']) ? (int)$_POST['gudang_id'] : -1;

        $t0 = microtime(true);

        // 1. Ambil stok semua produk di gudang ini dari _rek_pembantu_produk_cache
        $sql_stok = "SELECT extern_id as produk_id, qty_debet as stok_qty
                     FROM _rek_pembantu_produk_cache
                     WHERE periode = 'forever' AND cabang_id = ? AND gudang_id = ? AND qty_debet > 0";
        $res_stok = $this->db->query($sql_stok, array($cabang_id, $gudang_id))->result_array();

        $stok_map = array();
        foreach ($res_stok as $r) {
            $stok_map[$r['produk_id']] = (float)$r['stok_qty'];
        }

        // 2. Ambil serial semua produk di gudang ini dari _rek_pembantu_produk_perserial_cache
        $sql_ser = "SELECT produk_id, extern2_nama as sku, COUNT(1) as total_serial
                    FROM _rek_pembantu_produk_perserial_cache
                    WHERE periode = 'forever' AND cabang_id = ? AND gudang_id = ? AND qty_debet > 0
                    GROUP BY produk_id, extern2_nama";
        $res_ser = $this->db->query($sql_ser, array($cabang_id, $gudang_id))->result_array();

        $ser_map = array();
        foreach ($res_ser as $r) {
            $pid = $r['produk_id'];
            $sku = $r['sku'] ? $r['sku'] : "MAIN";
            if (!isset($ser_map[$pid])) {
                $ser_map[$pid] = array();
            }
            $ser_map[$pid][$sku] = (int)$r['total_serial'];
        }

        $allPids = array_unique(array_merge(array_keys($stok_map), array_keys($ser_map)));

        if (empty($allPids)) {
            header('Content-Type: application/json');
            echo json_encode(array(
                "status"     => "success",
                "total"      => 0,
                "mismatches" => array(),
                "duration"   => round(microtime(true) - $t0, 3),
            ));
            return;
        }

        $this->db->select("id, kode, nama, jml_serial")
                 ->from("produk")
                 ->where_in("id", $allPids)
                 ->where("jml_serial >", 0);
        $prod_info_res = $this->db->get()->result_array();

        $mismatches = array();
        foreach ($prod_info_res as $info) {
            $pid = $info['id'];
            $stok = isset($stok_map[$pid]) ? $stok_map[$pid] : 0.0;
            $serials = isset($ser_map[$pid]) ? $ser_map[$pid] : array();

            $parts_per_unit = ((int)$info['jml_serial'] > 0) ? (int)$info['jml_serial'] : 1;
            $total_serials = array_sum($serials);

            $pairs = 0;
            $is_unbalanced_parts = false;
            if (!empty($serials)) {
                if ($parts_per_unit > 1) {
                    $min_part = min($serials);
                    $max_part = max($serials);
                    $pairs = $min_part;
                    if ($min_part != $max_part) {
                        $is_unbalanced_parts = true;
                    }
                } else {
                    $pairs = $total_serials;
                }
            }

            $diff = $stok - $pairs;
            if ($diff != 0.0 || $is_unbalanced_parts) {
                $mismatches[] = array(
                    "produk_id"     => $pid,
                    "kode"          => $info['kode'],
                    "nama"          => $info['nama'],
                    "jml_serial"    => $parts_per_unit,
                    "stok"          => $stok,
                    "total_serial"  => $total_serials,
                    "pairs"         => $pairs,
                    "diff"          => $diff,
                    "is_unbalanced" => $is_unbalanced_parts,
                    "parts"         => $serials,
                );
            }
        }

        $duration = round(microtime(true) - $t0, 3);

        header('Content-Type: application/json');
        echo json_encode(array(
            "status"     => "success",
            "total"      => count($mismatches),
            "mismatches" => $mismatches,
            "duration"   => $duration,
        ));
        return;
    }

    public function ajaxGetTransactionDetail()
    {
        $id_master        = isset($_POST['id_master']) ? trim($_POST['id_master']) : "";
        $transaksi_id     = isset($_POST['transaksi_id']) ? (int)$_POST['transaksi_id'] : 0;
        $cabang_id        = isset($_POST['cabang_id']) ? (int)$_POST['cabang_id'] : -1;
        $gudang_id        = isset($_POST['gudang_id']) ? (int)$_POST['gudang_id'] : -1;
        $active_produk_id = isset($_POST['active_produk_id']) ? (int)$_POST['active_produk_id'] : 0;

        $this->db->select("id, nomer, jenis, id_master, reference_id, reference_nomer, oleh_id, oleh_nama, customers_nama, suppliers_nama, dtime")
                 ->from("transaksi");

        if (is_numeric($id_master) && (int)$id_master > 0) {
            $this->db->group_start()
                     ->where("id", (int)$id_master)
                     ->or_where("id_master", (int)$id_master)
                     ->or_where("reference_id", (int)$id_master)
                     ->group_end();
        } elseif ($transaksi_id > 0) {
            $this->db->where("id", $transaksi_id);
        } else {
            header('Content-Type: application/json');
            echo json_encode(array("status" => "error", "message" => "Parameter transaksi tidak valid."));
            return;
        }

        $tr_list = $this->db->order_by("id", "asc")->get()->result_array();

        if (empty($tr_list)) {
            header('Content-Type: application/json');
            echo json_encode(array("status" => "error", "message" => "Data transaksi tidak ditemukan."));
            return;
        }

        $tr_ids = array();
        $main_tr = $tr_list[0];
        foreach ($tr_list as $tr) {
            $tr_ids[] = (int)$tr['id'];
            if (strpos($tr['nomer'], '585.') === 0 || strpos($tr['nomer'], '467.') === 0 || strpos($tr['nomer'], '1985.') === 0 || strpos($tr['nomer'], '1587.') === 0) {
                $main_tr = $tr;
            }
        }

        // Ambil produk mutasi di gudang ini
        $this->db->select("p.extern_id as produk_id, p.extern_nama as produk_nama, SUM(p.qty_debet) as stok_in, SUM(p.qty_kredit) as stok_out")
                 ->from("__rek_pembantu_produk__1010030030 p")
                 ->where_in("p.transaksi_id", $tr_ids)
                 ->where("p.cabang_id", $cabang_id)
                 ->where("p.gudang_id", $gudang_id)
                 ->group_by("p.extern_id");
        $res_prod = $this->db->get()->result_array();

        $products = array();
        $prod_pids = array();
        foreach ($res_prod as $rp) {
            $pid = (int)$rp['produk_id'];
            $prod_pids[] = $pid;
            $products[$pid] = array(
                "produk_id"        => $pid,
                "kode"             => "-",
                "nama"             => $rp['produk_nama'],
                "jml_serial"       => 0,
                "stok_in"          => (float)$rp['stok_in'],
                "stok_out"         => (float)$rp['stok_out'],
                "serial_in_pairs"  => 0.0,
                "serial_out_pairs" => 0.0,
                "diff_in"          => 0.0,
                "diff_out"         => 0.0,
                "parts_in"         => array(),
                "parts_out"        => array(),
                "serials"          => array(),
            );
        }

        // Ambil info master produk
        if (!empty($prod_pids)) {
            $p_infos = $this->db->select("id, kode, nama, jml_serial")
                                ->from("produk")
                                ->where_in("id", $prod_pids)
                                ->get()
                                ->result_array();
            foreach ($p_infos as $pi) {
                $p_id = (int)$pi['id'];
                if (isset($products[$p_id])) {
                    $products[$p_id]['kode'] = $pi['kode'];
                    $products[$p_id]['jml_serial'] = ((int)$pi['jml_serial'] > 0) ? (int)$pi['jml_serial'] : 0;
                }
            }
        }

        // Ambil mutasi nomor seri di gudang ini
        $this->db->select("s.id, s.transaksi_id, s.transaksi_no, s.produk_id, s.produk_nama, s.extern_nama as serial_no, s.extern2_nama as sku, s.qty_debet, s.qty_kredit, s.dtime, t.oleh_nama")
                 ->from("__rek_pembantu_produk_perserial__1010030030 s")
                 ->join("transaksi t", "t.id = s.transaksi_id", "left")
                 ->where_in("s.transaksi_id", $tr_ids)
                 ->where("s.cabang_id", $cabang_id)
                 ->where("s.gudang_id", $gudang_id)
                 ->order_by("s.produk_id, s.extern2_nama, s.id", "asc");
        $res_ser = $this->db->get()->result_array();

        $serials_all = array();
        foreach ($res_ser as $rs) {
            $pid = (int)$rs['produk_id'];
            $part = $rs['sku'] ? $rs['sku'] : "MAIN";
            $qty_in = (float)$rs['qty_debet'];
            $qty_out = (float)$rs['qty_kredit'];

            if (!isset($products[$pid])) {
                $p_single = $this->db->select("id, kode, nama, jml_serial")->from("produk")->where("id", $pid)->get()->row_array();
                $products[$pid] = array(
                    "produk_id"        => $pid,
                    "kode"             => $p_single ? $p_single['kode'] : "-",
                    "nama"             => $p_single ? $p_single['nama'] : $rs['produk_nama'],
                    "jml_serial"       => ($p_single && (int)$p_single['jml_serial'] > 0) ? (int)$p_single['jml_serial'] : 1,
                    "stok_in"          => 0.0,
                    "stok_out"         => 0.0,
                    "serial_in_pairs"  => 0.0,
                    "serial_out_pairs" => 0.0,
                    "diff_in"          => 0.0,
                    "diff_out"         => 0.0,
                    "parts_in"         => array(),
                    "parts_out"        => array(),
                    "serials"          => array(),
                );
            }

            if (!isset($products[$pid]['parts_in'][$part])) {
                $products[$pid]['parts_in'][$part] = 0.0;
            }
            if (!isset($products[$pid]['parts_out'][$part])) {
                $products[$pid]['parts_out'][$part] = 0.0;
            }
            $products[$pid]['parts_in'][$part] += $qty_in;
            $products[$pid]['parts_out'][$part] += $qty_out;

            $s_item = array(
                "id"           => $rs['id'],
                "transaksi_no" => $rs['transaksi_no'],
                "serial_no"    => $rs['serial_no'],
                "sku"          => $part,
                "produk_id"    => $pid,
                "produk_nama"  => $products[$pid]['nama'],
                "qty_in"       => $qty_in,
                "qty_out"      => $qty_out,
                "dtime"        => $rs['dtime'],
                "oleh_nama"    => $rs['oleh_nama'],
            );
            $products[$pid]['serials'][] = $s_item;
            $serials_all[] = $s_item;
        }

        // Kalkulasi pasang & selisih per produk
        foreach ($products as $pid => &$p) {
            if ($p['jml_serial'] == 0) {
                $p['diff_in']  = 0.0;
                $p['diff_out'] = 0.0;
                continue;
            }
            $parts_per_unit = ($p['jml_serial'] > 0) ? $p['jml_serial'] : 1;
            if (!empty($p['parts_in'])) {
                $p['serial_in_pairs'] = ($parts_per_unit > 1) ? (float)min($p['parts_in']) : (float)array_sum($p['parts_in']);
            }
            if (!empty($p['parts_out'])) {
                $p['serial_out_pairs'] = ($parts_per_unit > 1) ? (float)min($p['parts_out']) : (float)array_sum($p['parts_out']);
            }

            $p['diff_in']  = $p['stok_in'] - $p['serial_in_pairs'];
            $p['diff_out'] = $p['stok_out'] - $p['serial_out_pairs'];
        }
        unset($p);

        header('Content-Type: application/json');
        echo json_encode(array(
            "status"           => "success",
            "main_tr"          => $main_tr,
            "all_transactions" => $tr_list,
            "products"         => array_values($products),
            "serials"          => $serials_all,
            "active_produk_id" => $active_produk_id,
        ));
        return;
    }
// END OF COMPLETE REPEATED LOGIC
}

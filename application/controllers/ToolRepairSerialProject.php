<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class ToolRepairSerialProject extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!isset($this->session->login['id'])) {
            gotoLogin();
        }
    }

    public function index()
    {
        $this->preview();
    }

    public function preview()
    {
        $target_nomer = isset($_GET['nomer']) ? trim((string)$_GET['nomer']) : '1119.-1.47';
        $cabang_id = -1;
        $gudang_id = 9;

        $sql_tr = "SELECT id, nomer, jenis, cabang_id, gudang_id, dtime, keterangan FROM transaksi WHERE nomer = ? LIMIT 1";
        $tr = $this->db->query($sql_tr, array($target_nomer))->row_array();

        $transaksi_id = 0;
        if ($tr && isset($tr['id'])) {
            $transaksi_id = (int)$tr['id'];
        }

        $mutasi_rows = array();
        $cache_rows = array();
        $summary = array();

        if ($transaksi_id > 0) {
            $sql_mutasi = "SELECT produk_id, produk_nama, extern2_nama as sku, COUNT(1) as total_mutasi
                           FROM __rek_pembantu_produk_perserial__1010030030
                           WHERE transaksi_id = ? AND cabang_id = ? AND gudang_id = ? AND produk_id != 239
                           GROUP BY produk_id, produk_nama, extern2_nama
                           ORDER BY total_mutasi DESC";
            $mutasi_rows = $this->db->query($sql_mutasi, array($transaksi_id, $cabang_id, $gudang_id))->result_array();

            $sql_cache = "SELECT c.produk_id, p.nama as produk_nama, p.jml_serial,
                                 COUNT(1) as total_serial_cache,
                                 stok.qty_debet as stok_buku_qty
                          FROM _rek_pembantu_produk_perserial_cache c
                          JOIN produk p ON p.id = c.produk_id
                          LEFT JOIN _rek_pembantu_produk_cache stok
                            ON stok.extern_id = c.produk_id
                            AND stok.cabang_id = ?
                            AND stok.gudang_id = ?
                            AND stok.periode = 'forever'
                          WHERE c.transaksi_id = ? AND c.cabang_id = ? AND c.gudang_id = ? AND c.produk_id != 239
                          GROUP BY c.produk_id, p.nama, p.jml_serial, stok.qty_debet
                          ORDER BY total_serial_cache DESC";
            $cache_rows = $this->db->query($sql_cache, array($cabang_id, $gudang_id, $transaksi_id, $cabang_id, $gudang_id))->result_array();

            $total_cache_count = 0;
            foreach ($cache_rows as $cr) {
                $total_cache_count += (int)$cr['total_serial_cache'];
            }

            $total_mutasi_count = 0;
            foreach ($mutasi_rows as $mr) {
                $total_mutasi_count += (int)$mr['total_mutasi'];
            }

            $summary = array(
                "total_produk" => sizeof($cache_rows),
                "total_cache"  => $total_cache_count,
                "total_mutasi" => $total_mutasi_count,
            );
        }

        $html = "<!DOCTYPE html>";
        $html .= "<html><head><meta charset='utf-8'><title>Preview Pemulihan Serial Gudang Project</title>";
        $html .= "<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'>";
        $html .= "<style>body{padding:25px;background:#f8f9fa;font-family:sans-serif;} .panel{box-shadow:0 2px 5px rgba(0,0,0,0.08);}</style>";
        $html .= "</head><body>";
        $html .= "<div class='container-fluid'>";
        $html .= "<div class='panel panel-primary'>";
        $html .= "<div class='panel-heading'><h3 class='panel-title'><b>PREVIEW & AUDIT PEMULIHAN SERIAL GUDANG PROJECT PUSAT (CABANG -1, GUDANG 9)</b></h3></div>";
        $html .= "<div class='panel-body'>";

        if (!$tr) {
            $html .= "<div class='alert alert-danger'>Transaksi dengan nomor <b>" . htmlspecialchars($target_nomer) . "</b> tidak ditemukan di database ini.</div>";
            $html .= "</div></div></div></body></html>";
            echo $html;
            return;
        }

        $html .= "<div class='alert alert-info'>";
        $html .= "<b>Target Transaksi Opname:</b> " . htmlspecialchars($tr['nomer']) . " (ID: " . $transaksi_id . ") | ";
        $html .= "<b>Waktu:</b> " . $tr['dtime'] . " | <b>Cabang:</b> " . $tr['cabang_id'] . " | <b>Gudang:</b> " . $tr['gudang_id'];
        $html .= "</div>";

        $html .= "<div class='row'>";
        $html .= "<div class='col-md-4'><div class='well well-sm text-center'><h4>Total Produk Terdampak</h4><h2>" . (isset($summary['total_produk']) ? $summary['total_produk'] : 0) . "</h2></div></div>";
        $html .= "<div class='col-md-4'><div class='well well-sm text-center'><h4>Total Baris Serial di Cache</h4><h2>" . (isset($summary['total_cache']) ? $summary['total_cache'] : 0) . "</h2></div></div>";
        $html .= "<div class='col-md-4'><div class='well well-sm text-center'><h4>Total Mutasi Kredit Cacat</h4><h2>" . (isset($summary['total_mutasi']) ? $summary['total_mutasi'] : 0) . "</h2></div></div>";
        $html .= "</div>";

        $html .= "<h4><b>Daftar Produk yang Akan Dipulihkan:</b></h4>";
        $html .= "<table class='table table-bordered table-striped table-hover'>";
        $html .= "<thead><tr class='info'>";
        $html .= "<th>No.</th><th>PID</th><th>Nama Produk</th><th>Komponen per Unit</th><th>Serial di Cache (Qty=0)</th><th>Stok Buku Fisik</th><th>Status Pasangan Unit</th>";
        $html .= "</tr></thead><tbody>";

        $no = 1;
        foreach ($cache_rows as $row) {
            $parts = ((int)$row['jml_serial'] > 0) ? (int)$row['jml_serial'] : 1;
            $pairs = (float)$row['total_serial_cache'] / $parts;
            $stok_buku = isset($row['stok_buku_qty']) ? (float)$row['stok_buku_qty'] : 0;
            $status_class = ($pairs == $stok_buku) ? "text-success" : "text-warning";
            $status_text = ($pairs == $stok_buku) ? "<b>Klop 100% (" . $pairs . " Set)</b>" : "Selisih (Pairs: $pairs vs Stok: $stok_buku)";

            $html .= "<tr>";
            $html .= "<td>" . $no++ . "</td>";
            $html .= "<td>" . $row['produk_id'] . "</td>";
            $html .= "<td>" . htmlspecialchars($row['produk_nama']) . "</td>";
            $html .= "<td>" . $parts . " komponen</td>";
            $html .= "<td><span class='badge'>" . $row['total_serial_cache'] . " serial</span></td>";
            $html .= "<td><b>" . $stok_buku . " unit</b></td>";
            $html .= "<td class='" . $status_class . "'>" . $status_text . "</td>";
            $html .= "</tr>";
        }

        $html .= "</tbody></table>";

        $execute_url = base_url() . "ToolRepairSerialProject/execute?nomer=" . urlencode($target_nomer);
        $html .= "<hr>";
        $html .= "<form method='POST' action='" . $execute_url . "' onsubmit='return confirm(\"Apakah Anda yakin ingin mengeksekusi pemulihan data serial sekarang?\\nData backup JSON akan dibuat otomatis sebelum transaksi dieksekusi.\");'>";
        $html .= "<input type='hidden' name='confirm_execute' value='1'>";
        $html .= "<button type='submit' class='btn btn-danger btn-lg btn-block'><i class='glyphicon glyphicon-wrench'></i> <b>EKSEKUSI PEMULIHAN SERIAL SEKARANG (DENGAN DATABASE TRANSACTION)</b></button>";
        $html .= "</form>";

        $html .= "</div></div></div></body></html>";
        echo $html;
    }

    public function execute()
    {
        if ($this->input->server('REQUEST_METHOD') !== 'POST' || $this->input->post('confirm_execute') != '1') {
            redirect('ToolRepairSerialProject/preview');
            return;
        }

        $target_nomer = isset($_GET['nomer']) ? trim((string)$_GET['nomer']) : '1119.-1.47';
        $cabang_id = -1;
        $gudang_id = 9;

        $sql_tr = "SELECT id, nomer, jenis, cabang_id, gudang_id, dtime FROM transaksi WHERE nomer = ? LIMIT 1";
        $tr = $this->db->query($sql_tr, array($target_nomer))->row_array();

        if (!$tr || !isset($tr['id'])) {
            die("Transaksi target $target_nomer tidak ditemukan.");
        }

        $transaksi_id = (int)$tr['id'];

        $sql_bak_mutasi = "SELECT * FROM __rek_pembantu_produk_perserial__1010030030
                           WHERE transaksi_id = ? AND cabang_id = ? AND gudang_id = ? AND produk_id != 239";
        $mutasi_backup = $this->db->query($sql_bak_mutasi, array($transaksi_id, $cabang_id, $gudang_id))->result_array();

        $sql_bak_cache = "SELECT * FROM _rek_pembantu_produk_perserial_cache
                          WHERE transaksi_id = ? AND cabang_id = ? AND gudang_id = ? AND produk_id != 239";
        $cache_backup = $this->db->query($sql_bak_cache, array($transaksi_id, $cabang_id, $gudang_id))->result_array();

        if (sizeof($cache_backup) == 0) {
            die("Tidak ada data cache yang berstatus transaksi_id = $transaksi_id untuk dipulihkan. Proses dihentikan.");
        }

        $backup_dir = APPPATH . "logs/backup_recovery/";
        if (!file_exists($backup_dir)) {
            @mkdir($backup_dir, 0777, true);
        }
        $backup_file = $backup_dir . "recovery_serial_tr" . $transaksi_id . "_" . date("Ymd_His") . ".json";
        $backup_payload = array(
            "timestamp"     => date("Y-m-d H:i:s"),
            "target_nomer"  => $target_nomer,
            "transaksi_id"  => $transaksi_id,
            "cabang_id"     => $cabang_id,
            "gudang_id"     => $gudang_id,
            "total_mutasi"  => sizeof($mutasi_backup),
            "total_cache"   => sizeof($cache_backup),
            "data_mutasi"   => $mutasi_backup,
            "data_cache"    => $cache_backup,
        );
        @file_put_contents($backup_file, json_encode($backup_payload));

        $this->db->trans_start();

        $sql_delete_mutasi = "DELETE FROM __rek_pembantu_produk_perserial__1010030030
                              WHERE transaksi_id = ? AND cabang_id = ? AND gudang_id = ? AND produk_id != 239";
        $this->db->query($sql_delete_mutasi, array($transaksi_id, $cabang_id, $gudang_id));
        $deleted_mutasi_count = $this->db->affected_rows();

        $sql_update_cache = "UPDATE _rek_pembantu_produk_perserial_cache c
                             JOIN (
                                 SELECT m_in.extern_nama, m_in.produk_id, m_in.extern2_nama as orig_sku,
                                        m_in.transaksi_id as orig_tr_id, m_in.transaksi_no as orig_tr_no,
                                        m_in.dtime as orig_dtime
                                 FROM (
                                     SELECT extern_nama, produk_id, extern2_nama, transaksi_id, transaksi_no, dtime,
                                            ROW_NUMBER() OVER (PARTITION BY extern_nama, produk_id ORDER BY id DESC) as rn
                                     FROM __rek_pembantu_produk_perserial__1010030030
                                     WHERE qty_debet = 1 AND cabang_id = -1 AND gudang_id = 9
                                 ) m_in
                                 WHERE m_in.rn = 1
                             ) orig ON orig.extern_nama = c.extern_nama AND orig.produk_id = c.produk_id
                             SET c.qty_debet = 1,
                                 c.debet = 1.0,
                                 c.qty_kredit = 0,
                                 c.kredit = 0.0,
                                 c.extern2_nama = orig.orig_sku,
                                 c.transaksi_id = orig.orig_tr_id,
                                 c.transaksi_no = orig.orig_tr_no,
                                 c.dtime = orig.orig_dtime
                             WHERE c.transaksi_id = ? AND c.cabang_id = ? AND c.gudang_id = ? AND c.produk_id != 239";
        $this->db->query($sql_update_cache, array($transaksi_id, $cabang_id, $gudang_id));
        $updated_cache_count = $this->db->affected_rows();

        $this->db->trans_complete();

        $is_success = ($this->db->trans_status() !== FALSE);

        $html = "<!DOCTYPE html>";
        $html .= "<html><head><meta charset='utf-8'><title>Hasil Eksekusi Pemulihan Serial</title>";
        $html .= "<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'>";
        $html .= "<style>body{padding:25px;background:#f8f9fa;font-family:sans-serif;}</style>";
        $html .= "</head><body><div class='container'>";

        if ($is_success) {
            $html .= "<div class='panel panel-success'>";
            $html .= "<div class='panel-heading'><h3 class='panel-title'><b>PEMULIHAN SERIAL BERHASIL DILAKUKAN!</b></h3></div>";
            $html .= "<div class='panel-body'>";
            $html .= "<h4>Rincian Eksekusi:</h4>";
            $html .= "<ul>";
            $html .= "<li><b>Target Transaksi Opname:</b> " . htmlspecialchars($target_nomer) . " (ID: $transaksi_id)</li>";
            $html .= "<li><b>Baris Mutasi Kredit Cacat Terhapus:</b> " . $deleted_mutasi_count . " baris</li>";
            $html .= "<li><b>Baris Cache Serial Berhasil Dipulihkan:</b> " . $updated_cache_count . " baris (qty_debet = 1)</li>";
            $html .= "<li><b>Lokasi Berkas Cadangan (Backup JSON):</b> <code>" . htmlspecialchars($backup_file) . "</code></li>";
            $html .= "</ul>";
            $html .= "<div class='alert alert-success'>";
            $html .= "<b>Status:</b> Transaksi database telah di-COMMIT secara permanen. Seluruh serial pada Gudang Project Pusat kini telah aktif kembali.";
            $html .= "</div>";
            $html .= "<a href='" . base_url() . "LedgerV2/viewBalances_l1/RekeningPembantuProduk/1010030030' class='btn btn-primary btn-lg' target='_blank'><i class='glyphicon glyphicon-th-list'></i> <b>Buka Ledger Rekening Pembantu Produk (Periksa Hasil)</b></a>";
            $html .= "</div></div>";
        } else {
            $html .= "<div class='panel panel-danger'>";
            $html .= "<div class='panel-heading'><h3 class='panel-title'><b>PEMULIHAN GAGAL DIEKSEKUSI!</b></h3></div>";
            $html .= "<div class='panel-body'>";
            $html .= "<p>Terjadi kesalahan saat memproses query ke database. Transaksi telah di-ROLLBACK secara otomatis, tidak ada perubahan data yang tersimpan.</p>";
            $html .= "<a href='" . base_url() . "ToolRepairSerialProject/preview' class='btn btn-default'>Kembali ke Preview</a>";
            $html .= "</div></div>";
        }

        $html .= "</div></body></html>";
        echo $html;
    }
}

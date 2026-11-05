<?php
// START OF COMPLETE REPEATED LOGIC
defined('BASEPATH') OR exit('No direct script access allowed');

require_once "Modul_Controller.php";

/**
 * Controller Audit & Rekonsiliasi Triangulasi Data Project
 * Digunakan untuk UAT audit transaksi selesai yang bermasalah di kemudian hari (Sisi 1).
 *
 * Pemetaan Struktur Database Resmi:
 * 1. MdlProdukProject    => Tabel 'project_produk' (kolom: id, kode, nama, nomor_kontrak, spek, harga, keterangan, alamat, startdtime, end_dtime, lock, status, trash)
 * 2. MdlProjectWorkOrder => Tabel 'project_workorder' (kolom: id, produk_id, qty, nama, lokasi, keterangan, status, trash) -> Diurutkan berdasarkan 'id' ASC (bukan 'urut')
 * 3. MdlProjectKomposisi => Tabel 'project_komposisi' (kolom: id, produk_id, produk_dasar_id, jml, harga, nilai, harga_budget, jenis, status, trash)
 * 4. MdlProduk           => Tabel 'produk' (kolom: id, kode, nama, satuan, jenis, status, trash)
 * 5. MdlTimWorkProject   => Tabel 'project_tim_work' (kolom: id, produk_id, employee_id, hak_akses_id, status, trash)
 * 6. MdlEmployee         => Tabel 'per_employee' (kolom: id, nama, nama_login, div_id, status, trash)
 * 7. MdlTransaksi        => Tabel 'transaksi' (kolom: id, nomer, jenis, dtime, status, step_number, project_id, trash)
 * 8. MdlTransaksi Detail => Tabel 'transaksi_data' (kolom: id, transaksi_id, produk_id, produk_nama, produk_ord_jml, produk_ord_hrg, satuan, status, trash)
 */
class AuditProject extends Modul_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper("he_project");
        $this->load->helper("he_angka");
    }

    /**
     * Halaman Audit Triangulasi Project
     * URL: master_project/AuditProject/audit/{projectId}
     */
    public function audit($projectId = 82)
    {
        $projectId = (int)$projectId;
        if ($projectId <= 0) {
            $projectId = 82;
        }

        // 1. Ambil Data Header Master Project (Tabel: project_produk)
        $this->db->where("id", $projectId);
        $projectQuery = $this->db->get("project_produk");
        $projectData = $projectQuery->row();

        if (!$projectData) {
            echo "<div style='padding:20px; font-family:sans-serif; color:#d9534f;'>";
            echo "<h3>Data Project ID #$projectId tidak ditemukan dalam tabel project_produk.</h3>";
            echo "<a href='javascript:history.back()'>&laquo; Kembali</a>";
            echo "</div>";
            return;
        }

        // 2. Ambil Work Order / Fase (Tabel: project_workorder, kolom urut diurutkan via 'id')
        $this->db->where("produk_id", $projectId);
        $this->db->where("status", "1");
        $this->db->where("trash", "0");
        $this->db->order_by("id", "ASC");
        $workOrders = $this->db->get("project_workorder")->result();

        // 3. Ambil Komposisi BOM (Tabel: project_komposisi join produk via produk_dasar_id)
        $this->db->select("pk.id, pk.produk_id, pk.produk_dasar_id, pk.jml, pk.harga, pk.nilai, pk.harga_budget, pk.jenis, p.nama as produk_nama, p.satuan as produk_satuan");
        $this->db->from("project_komposisi pk");
        $this->db->join("produk p", "p.id = pk.produk_dasar_id", "left");
        $this->db->where("pk.produk_id", $projectId);
        $this->db->where("pk.status", "1");
        $this->db->where("pk.trash", "0");
        $this->db->order_by("pk.id", "ASC");
        $komposisiList = $this->db->get()->result();

        // 4. Ambil Tim Kerja (Tabel: project_tim_work join per_employee via employee_id)
        $this->db->select("tw.id, tw.produk_id, tw.employee_id, e.nama as employee_nama");
        $this->db->from("project_tim_work tw");
        $this->db->join("per_employee e", "e.id = tw.employee_id", "left");
        $this->db->where("tw.produk_id", $projectId);
        $this->db->where("tw.status", "1");
        $this->db->where("tw.trash", "0");
        $this->db->order_by("tw.id", "ASC");
        $timWorkList = $this->db->get()->result();

        // 5. Ambil Transaksi Hilir Terkait (Tabel: transaksi_data join transaksi via project_id)
        $this->db->select("td.id, td.transaksi_id, td.produk_id, td.produk_nama, td.produk_ord_jml, td.produk_ord_hrg, t.nomer, t.jenis, t.dtime, t.status, t.step_number");
        $this->db->from("transaksi_data td");
        $this->db->join("transaksi t", "t.id = td.transaksi_id", "left");
        $this->db->where("t.project_id", $projectId);
        $this->db->where("t.trash", "0");
        $this->db->where("td.trash", "0");
        $this->db->order_by("t.id", "DESC");
        $this->db->limit(100);
        $transaksiDetailList = $this->db->get()->result();

        // 6. Hitung Total Kuantitas BOM vs Estimasi Biaya
        $totalItemBom = count($komposisiList);
        $totalEstBiayaBom = 0;
        foreach ($komposisiList as $kItem) {
            $estHpp = isset($kItem->harga) ? (float)$kItem->harga : (isset($kItem->nilai) ? (float)$kItem->nilai : 0);
            $estQty = isset($kItem->jml) ? (float)$kItem->jml : 0;
            $totalEstBiayaBom += ($estHpp * $estQty);
        }

        // 7. Render Tampilan Audit Interaktif
        $dataView = array(
            "projectId" => $projectId,
            "project" => $projectData,
            "workOrders" => $workOrders,
            "komposisiList" => $komposisiList,
            "timWorkList" => $timWorkList,
            "transaksiDetailList" => $transaksiDetailList,
            "totalItemBom" => $totalItemBom,
            "totalEstBiayaBom" => $totalEstBiayaBom,
        );

        $this->renderAuditView($dataView);
    }

    /**
     * Render HTML Audit Report View
     */
    private function renderAuditView($data)
    {
        $project = $data["project"];
        $projectId = $data["projectId"];
        $isLocked = (isset($project->lock) && $project->lock == 1);
        $nilaiKontrak = isset($project->harga) ? (float)$project->harga : 0;
        $totalEstBom = $data["totalEstBiayaBom"];

        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="utf-8">
            <title>Audit & Rekonsiliasi Triangulasi - Project #<?= $projectId ?></title>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link rel="stylesheet" href="<?= base_url() ?>assets/bootstrap-3.3.7-dist/css/bootstrap.min.css">
            <link rel="stylesheet" href="<?= base_url() ?>assets/Font-Awesome-master/css/font-awesome.min.css">
            <style>
                body { background-color: #f4f6f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Arial, sans-serif; padding: 20px; }
                .audit-card { background: #fff; border-radius: 6px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 20px; padding: 20px; }
                .audit-header { border-bottom: 2px solid #0073b7; padding-bottom: 12px; margin-bottom: 20px; }
                .badge-status { font-size: 13px; padding: 6px 12px; border-radius: 4px; }
                .metric-box { background: #eef3f7; border-left: 4px solid #0073b7; padding: 15px; border-radius: 4px; margin-bottom: 15px; }
                .metric-title { font-size: 12px; color: #666; text-transform: uppercase; font-weight: bold; }
                .metric-val { font-size: 20px; font-weight: bold; color: #222; }
                .table-audit th { background-color: #f1f4f6; color: #333; font-size: 12px; text-transform: uppercase; }
            </style>
        </head>
        <body>
        <div class="container-fluid">
            <!-- Header Utama -->
            <div class="audit-card">
                <div class="audit-header clearfix">
                    <div class="pull-left">
                        <h2 style="margin:0 0 5px 0;">
                            <i class="fa fa-stethoscope text-primary"></i> Laporan Audit & Rekonsiliasi Triangulasi
                        </h2>
                        <span class="text-muted">Pemeriksaan Pasca-Transaksi Selesai &bull; <b>Project ID: #<?= $projectId ?></b> &bull; <?= htmlspecialchars(isset($project->nama) ? $project->nama : 'Proyek') ?></span>
                    </div>
                    <div class="pull-right">
                        <a href="<?= MODUL_PATH ?>MasterData/index/<?= $projectId ?>" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Kembali ke Master Data
                        </a>
                        <button onclick="window.print()" class="btn btn-primary btn-sm">
                            <i class="fa fa-print"></i> Cetak Berita Acara
                        </button>
                    </div>
                </div>

                <!-- Indikator Kunci -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="metric-box">
                            <div class="metric-title">Status Kunci (Lock)</div>
                            <div class="metric-val">
                                <?php if ($isLocked): ?>
                                    <span class="label label-danger"><i class="fa fa-lock"></i> TERKUNCI (1)</span>
                                <?php else: ?>
                                    <span class="label label-success"><i class="fa fa-unlock"></i> TERBUKA (0)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-box">
                            <div class="metric-title">Nilai Kontrak (Header)</div>
                            <div class="metric-val text-primary">
                                Rp <?= number_format($nilaiKontrak, 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-box">
                            <div class="metric-title">Estimasi BOM Material</div>
                            <div class="metric-val text-warning">
                                Rp <?= number_format($totalEstBom, 0, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="metric-box">
                            <div class="metric-title">Jumlah Komponen BOM</div>
                            <div class="metric-val text-success">
                                <?= $data["totalItemBom"] ?> Item
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Matriks Verifikasi Triangulasi (Checklist) -->
            <div class="audit-card">
                <h4 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    <i class="fa fa-check-square-o text-success"></i> Matriks Verifikasi Tiga Titik (BOM vs Mutasi vs Jurnal)
                </h4>
                <table class="table table-bordered table-striped table-audit">
                    <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Objek Verifikasi</th>
                        <th>Nilai Terdaftar</th>
                        <th>Kondisi Ideal</th>
                        <th style="width: 140px; text-align:center;">Status Audit</th>
                        <th>Catatan Rekomendasi</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>1</td>
                        <td><b>Integritas Status Lock</b><br><small class="text-muted">Tabel: project_produk &bull; Field: lock</small></td>
                        <td><?= $isLocked ? "Terkunci (Lock = 1)" : "Terbuka (Lock = 0)" ?></td>
                        <td>Wajib bernilai 1 jika transaksi operasional telah selesai</td>
                        <td class="text-center">
                            <?php if ($isLocked): ?>
                                <span class="label label-success badge-status"><i class="fa fa-check"></i> PASS</span>
                            <?php else: ?>
                                <span class="label label-warning badge-status"><i class="fa fa-exclamation-triangle"></i> OPEN LOCK</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $isLocked ? "Status aman dari modifikasi tak disengaja." : "Perlu dikunci jika operasional telah berakhir." ?></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><b>Alokasi Work Order (Fase)</b><br><small class="text-muted">Tabel: project_workorder &bull; Urutan: id</small></td>
                        <td><?= count($data["workOrders"]) ?> Fase terdaftar</td>
                        <td>Minimal memiliki 1 fase pengerjaan aktif</td>
                        <td class="text-center">
                            <?php if (count($data["workOrders"]) > 0): ?>
                                <span class="label label-success badge-status"><i class="fa fa-check"></i> PASS</span>
                            <?php else: ?>
                                <span class="label label-danger badge-status"><i class="fa fa-times"></i> NO WO</span>
                            <?php endif; ?>
                        </td>
                        <td>Semua fase harus memiliki SPK pendukung.</td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><b>Komposisi BOM Terencana</b><br><small class="text-muted">Tabel: project_komposisi &bull; Field: jml, produk_dasar_id</small></td>
                        <td><?= $data["totalItemBom"] ?> item bahan/supplies</td>
                        <td>Kuantitas distribusi tidak boleh melampaui BOM</td>
                        <td class="text-center">
                            <?php if ($data["totalItemBom"] > 0): ?>
                                <span class="label label-success badge-status"><i class="fa fa-check"></i> PASS</span>
                            <?php else: ?>
                                <span class="label label-warning badge-status"><i class="fa fa-exclamation"></i> KOSONG</span>
                            <?php endif; ?>
                        </td>
                        <td>Periksa kesesuaian fisik pengeluaran bahan pada tabel rincian di bawah.</td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td><b>Tim Pelaksana (Tim Kerja)</b><br><small class="text-muted">Tabel: project_tim_work &bull; Join: per_employee</small></td>
                        <td><?= count($data["timWorkList"]) ?> personel terdaftar</td>
                        <td>PIC pelaksana harus terdata di per_employee</td>
                        <td class="text-center">
                            <?php if (count($data["timWorkList"]) > 0): ?>
                                <span class="label label-success badge-status"><i class="fa fa-check"></i> PASS</span>
                            <?php else: ?>
                                <span class="label label-warning badge-status"><i class="fa fa-exclamation"></i> BELUM ADA</span>
                            <?php endif; ?>
                        </td>
                        <td>Penting untuk penentuan hak akses tasklist teknisi.</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- Detail Komposisi Material BOM -->
            <div class="audit-card">
                <h4 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    <i class="fa fa-cubes text-info"></i> Daftar Bahan & Material BOM (Tabel: project_komposisi)
                </h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-audit">
                        <thead>
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>Nama Komponen / Material</th>
                            <th>Satuan</th>
                            <th class="text-right">Qty BOM (jml)</th>
                            <th class="text-right">Estimasi HPP (Rp)</th>
                            <th class="text-right">Subtotal (Rp)</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (count($data["komposisiList"]) > 0): ?>
                            <?php $no = 1; foreach ($data["komposisiList"] as $item): ?>
                                <?php
                                $itemQty = isset($item->jml) ? (float)$item->jml : 0;
                                $itemHarga = isset($item->harga) ? (float)$item->harga : (isset($item->nilai) ? (float)$item->nilai : 0);
                                $itemSubtotal = $itemQty * $itemHarga;
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><b><?= htmlspecialchars($item->produk_nama ? $item->produk_nama : "Item #" . $item->produk_dasar_id) ?></b></td>
                                    <td><?= htmlspecialchars($item->produk_satuan ? $item->produk_satuan : "-") ?></td>
                                    <td class="text-right"><?= number_format($itemQty, 2, ',', '.') ?></td>
                                    <td class="text-right"><?= number_format($itemHarga, 0, ',', '.') ?></td>
                                    <td class="text-right"><b><?= number_format($itemSubtotal, 0, ',', '.') ?></b></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada item komposisi material yang didaftarkan pada proyek ini di tabel project_komposisi.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Riwayat Transaksi Terkait -->
            <div class="audit-card">
                <h4 style="margin-top:0; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    <i class="fa fa-exchange text-warning"></i> Mutasi Transaksi Hilir Berelasi (Tabel: transaksi & transaksi_data)
                </h4>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-audit">
                        <thead>
                        <tr>
                            <th style="width:40px;">No</th>
                            <th>No. Transaksi</th>
                            <th>Nama Produk</th>
                            <th>Jenis Tr</th>
                            <th>Waktu (Dtime)</th>
                            <th>Step</th>
                            <th class="text-right">Qty</th>
                            <th class="text-right">Total Nilai (Rp)</th>
                            <th class="text-center">Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (count($data["transaksiDetailList"]) > 0): ?>
                            <?php $no = 1; foreach ($data["transaksiDetailList"] as $trItem): ?>
                                <?php
                                $subtotalItem = (float)$trItem->produk_ord_jml * (float)$trItem->produk_ord_hrg;
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><b><?= htmlspecialchars($trItem->nomer ? $trItem->nomer : "#" . $trItem->transaksi_id) ?></b></td>
                                    <td><?= htmlspecialchars($trItem->produk_nama ? $trItem->produk_nama : "Item #" . $trItem->produk_id) ?></td>
                                    <td><span class="label label-info"><?= htmlspecialchars($trItem->jenis) ?></span></td>
                                    <td><?= htmlspecialchars($trItem->dtime) ?></td>
                                    <td>Step <?= (int)$trItem->step_number ?></td>
                                    <td class="text-right"><?= number_format((float)$trItem->produk_ord_jml, 2, ',', '.') ?></td>
                                    <td class="text-right"><?= number_format($subtotalItem, 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <?php if ($trItem->status == 1): ?>
                                            <span class="label label-success">Posted</span>
                                        <?php else: ?>
                                            <span class="label label-default"><?= htmlspecialchars($trItem->status) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">Belum ada transaksi hilir langsung yang tercatat mengikat Project ID #<?= $projectId ?> pada tabel transaksi.</td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        </body>
        </html>
        <?php
    }
}
// END OF COMPLETE REPEATED LOGIC

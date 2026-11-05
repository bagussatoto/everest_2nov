<?php
// START OF COMPLETE REPEATED LOGIC
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once "Modul_Controller.php";

class LaporanKlaimSupplier extends Modul_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!isset($this->session->login['id'])) {
            gotoLogin();
        }
        validateUserSession($this->session->login['id']);

        $this->load->helper("he_stepping");
        $this->load->helper("he_access_right");
        $this->load->library("MobileDetect");
        $this->load->helper("he_session_replacer");
        $this->load->helper("he_angka");
        $this->load->model("Mdls/MdlSupplier");

        if (empty($this->jenisTr)) {
            $this->jenisTr = "3333";
        }
    }

    public function index($rekName = "1010020030")
    {
        if (!isset($this->session->login['id'])) {
            redirect(base_url() . "Login");
        }

        $rekening = urldecode($rekName);
        if (empty($rekening) || $rekening == "3333") {
            $rekening = "1010020030";
        }

        // =========================================================================
        // LEVEL 1: SALDO PER SUPPLIER MENGGUNAKAN MODEL RESMI
        // Model: Coms/ComRekeningPembantuPiutangSupplierMain -> method: fetchBalances
        // =========================================================================
        $this->load->model("Coms/ComRekeningPembantuPiutangSupplierMain");
        $comSupplier = new ComRekeningPembantuPiutangSupplierMain();

        $cabangId = isset($this->session->login['cabang_id']) ? $this->session->login['cabang_id'] : -1;
        if ($cabangId != -1 && $cabangId != 0 && $cabangId != "") {
            $comSupplier->addFilter("cabang_id = '" . $cabangId . "'");
        }

        $rawBalances = $comSupplier->fetchBalances($rekening, "", "rek_id", "ASC");

        // Lookup nama master supplier untuk fallback jika extern_nama kosong
        $mdlSupplier = new MdlSupplier();
        $supplierLookup = $mdlSupplier->lookupAll();

        $vendorsActive = array();
        $vendorsZero = array();

        $totalDebet = 0;
        $totalKredit = 0;
        $totalNet = 0;

        if (is_array($rawBalances) && sizeof($rawBalances) > 0) {
            foreach ($rawBalances as $row) {
                $sId = intval($row->extern_id);
                $sNama = isset($row->extern_nama) && strlen(trim($row->extern_nama)) > 0 ? trim($row->extern_nama) : "";
                if ($sNama == "" && isset($supplierLookup[$sId]['nama'])) {
                    $sNama = $supplierLookup[$sId]['nama'];
                }
                if ($sNama == "") {
                    $sNama = "Supplier #" . $sId;
                }

                $nDebet = floatval($row->debet);
                $nKredit = floatval($row->kredit);
                $nNet = $nDebet - $nKredit;

                $totalDebet += $nDebet;
                $totalKredit += $nKredit;
                $totalNet += $nNet;

                $vendorItem = array(
                    "id" => $sId,
                    "rek_id" => isset($row->rek_id) ? $row->rek_id : "",
                    "nama" => $sNama,
                    "debet" => $nDebet,
                    "kredit" => $nKredit,
                    "saldo" => $nNet,
                    "cabang_id" => isset($row->cabang_id) ? $row->cabang_id : -1,
                );

                if ($nNet >= 1) {
                    $vendorsActive[] = $vendorItem;
                } else {
                    $vendorsZero[] = $vendorItem;
                }
            }
        }

        // Urutkan vendor aktif dari saldo terbesar
        usort($vendorsActive, function ($a, $b) {
            if ($b['saldo'] == $a['saldo']) return 0;
            return ($b['saldo'] > $a['saldo']) ? 1 : -1;
        });

        // Urutkan vendor saldo 0 berdasarkan nama alfabetis
        usort($vendorsZero, function ($a, $b) {
            return strcmp(strtolower($a['nama']), strtolower($b['nama']));
        });

        // Hitung Aging Schedule Piutang Riil berdasarkan sisa nota aktif
        $agingData = $this->calculateAgingSchedule($rekening);

        $realizationRatio = ($totalDebet > 0) ? (($totalKredit / $totalDebet) * 100) : 0;

        $data = array(
            "rekening" => $rekening,
            "rekeningNama" => "Klaim Kepada Supplier (Piutang Pembelian)",
            "vendorsActive" => $vendorsActive,
            "vendorsZero" => $vendorsZero,
            "totalVendors" => sizeof($vendorsActive) + sizeof($vendorsZero),
            "totalActiveVendors" => sizeof($vendorsActive),
            "totalZeroVendors" => sizeof($vendorsZero),
            "totalDebet" => $totalDebet,
            "totalKredit" => $totalKredit,
            "totalNet" => $totalNet,
            "realizationRatio" => $realizationRatio,
            "agingData" => $agingData,
            "printUrl" => MODUL_PATH . "LaporanKlaimSupplier/cetak/" . urlencode($rekening),
            "drilldownUrl" => MODUL_PATH . "LaporanKlaimSupplier/getMutasiVendorAjax",
        );

        $content = $this->load->view("laporan_klaim_supplier", $data, true);

        if (!class_exists('Layout', false)) {
            $this->load->library("Layout");
        }

        $title = "LAPORAN KLAIM SUPPLIER";
        $subTitle = "Sub-Buku Besar COA " . $rekening;

        $p = new Layout($title, $subTitle, MODUL_TEMPLATE_PATH . "template/abReport.html");
        $str_group = isset($_GET['gr']) ? "?gr=" . $_GET['gr'] : "";
        $p->addTags(array(
            "jenisTr" => $this->jenisTr . $str_group,
            "trName" => $this->jenisTrName,
            "menu_left" => callMenuLeft(),
            "float_menu_atas" => callFloatMenu('atas'),
            "float_menu_bawah" => callFloatMenu(),
            "menu_taskbar" => callMenuTaskbar(),
            "btn_back" => callBackNav(),
            "content" => $content,
            "content2" => "",
            "profile_name" => $this->session->login['nama'],
            "add_link" => "",
            "newTrTarget" => base_url() . "kompensasiharga/Create/index/" . $this->jenisTr,
            "newTrDisp" => "inline-table",
            "nav_top" => "",
        ));
        $p->render();
    }

    // =========================================================================
    // LEVEL 2: MUTASI RINCI PER SUPPLIER MENGGUNAKAN MODEL RESMI
    // Model: Coms/ComRekeningPembantuPiutangSupplierMain -> method: fetchMoves
    // =========================================================================
    public function getMutasiVendorAjax()
    {
        $supplierId = isset($_POST['supplier_id']) ? intval($_POST['supplier_id']) : (isset($_GET['supplier_id']) ? intval($_GET['supplier_id']) : 0);
        $rekening = isset($_POST['rekening']) ? trim($_POST['rekening']) : (isset($_GET['rekening']) ? trim($_GET['rekening']) : "1010020030");
        if (empty($rekening) || $rekening == "3333") {
            $rekening = "1010020030";
        }

        if ($supplierId <= 0) {
            echo json_encode(array("status" => "error", "message" => "ID Supplier tidak valid."));
            exit;
        }

        $this->load->model("Coms/ComRekeningPembantuPiutangSupplierMain");
        $comSupplier = new ComRekeningPembantuPiutangSupplierMain();

        // Level 2: Ambil seluruh jejak mutasi dokumen untuk vendor tersebut via model resmi
        $moves = $comSupplier->fetchMoves($rekening, $supplierId);

        $html = "";
        if (!is_array($moves) || sizeof($moves) == 0) {
            $html .= "<tr><td colspan='9' class='text-center text-muted' style='padding:20px;'><i class='fa fa-info-circle'></i> Belum ada riwayat mutasi untuk supplier ini.</td></tr>";
        } else {
            $runningBalance = 0;
            $no = 1;
            foreach ($moves as $m) {
                $nDebet = floatval($m->debet);
                $nKredit = floatval($m->kredit);
                $runningBalance += ($nDebet - $nKredit);

                $dtime = isset($m->dtime) ? $m->dtime : (isset($m->fulldate) ? $m->fulldate : "-");
                $tglFormatted = (strlen($dtime) >= 10) ? date("d/m/Y H:i", strtotime($dtime)) : $dtime;

                $trJenis = isset($m->transaksi_jenis) ? $m->transaksi_jenis : (isset($m->jenis) ? $m->jenis : "-");
                $trNo = isset($m->transaksi_no) && strlen(trim($m->transaksi_no)) > 0 ? trim($m->transaksi_no) : "-";
                $trId = isset($m->transaksi_id) ? intval($m->transaksi_id) : 0;
                $keterangan = isset($m->keterangan) ? $m->keterangan : "-";

                $badgeJenis = "";
                if ($trJenis == "467") {
                    $badgeJenis = "<span class='badge' style='background:#2b6cb0;'>GRN 467</span>";
                } elseif ($trJenis == "3333") {
                    $badgeJenis = "<span class='badge' style='background:#22543d;'>Klaim 3333</span>";
                } elseif ($trJenis == "9911") {
                    $badgeJenis = "<span class='badge' style='background:#744210;'>Retur 9911</span>";
                } else {
                    $badgeJenis = "<span class='badge' style='background:#718096;'>Trx $trJenis</span>";
                }

                $html .= "<tr>";
                $html .= "<td class='text-center' style='font-size:11px;'>" . $no++ . "</td>";
                $html .= "<td style='font-size:11px;white-space:nowrap;'>" . $tglFormatted . "</td>";
                $html .= "<td class='text-center' style='font-size:11px;'>" . $badgeJenis . "</td>";
                $html .= "<td style='font-size:11px;font-family:monospace;font-weight:600;'>";
                if ($trId > 0) {
                    $html .= "<a href='" . base_url() . "kompensasiharga/ViewDetails/index/$trJenis/$trId' target='_blank' style='color:#2b6cb0;text-decoration:none;'>$trNo <i class='fa fa-external-link'></i></a>";
                } else {
                    $html .= $trNo;
                }
                $html .= "</td>";
                $html .= "<td style='font-size:11px;color:#4a5568;max-width:280px;'>" . htmlspecialchars($keterangan) . "</td>";
                $html .= "<td class='text-right' style='font-size:11px;font-family:monospace;color:#22543d;'>" . ($nDebet > 0 ? number_format($nDebet, 2, ',', '.') : "-") . "</td>";
                $html .= "<td class='text-right' style='font-size:11px;font-family:monospace;color:#742a2a;'>" . ($nKredit > 0 ? number_format($nKredit, 2, ',', '.') : "-") . "</td>";
                $html .= "<td class='text-right' style='font-size:11px;font-family:monospace;font-weight:bold;color:#1a365d;'>" . number_format($runningBalance, 2, ',', '.') . "</td>";
                $html .= "<td class='text-center' style='font-size:11px;'>";
                if ($runningBalance <= 0) {
                    $html .= "<span class='label label-success' style='font-size:10px;'>Lunas</span>";
                } else {
                    $html .= "<span class='label label-warning' style='font-size:10px;'>Aktif</span>";
                }
                $html .= "</td>";
                $html .= "</tr>";
            }
        }

        echo json_encode(array(
            "status" => "success",
            "html" => $html,
            "total_records" => is_array($moves) ? sizeof($moves) : 0
        ));
        exit;
    }

    public function cetak($rekName = "1010020030")
    {
        $this->index($rekName);
    }

    private function calculateAgingSchedule($rekening)
    {
        $sql = "SELECT 
            SUM(CASE WHEN days <= 30 THEN sisa_piutang ELSE 0 END) as aging_0_30,
            SUM(CASE WHEN days BETWEEN 31 AND 60 THEN sisa_piutang ELSE 0 END) as aging_31_60,
            SUM(CASE WHEN days BETWEEN 61 AND 90 THEN sisa_piutang ELSE 0 END) as aging_61_90,
            SUM(CASE WHEN days > 90 THEN sisa_piutang ELSE 0 END) as aging_over_90,
            SUM(sisa_piutang) as total_saldo
        FROM (
            SELECT 
                extern_id,
                MIN(fulldate) as tgl_grn,
                DATEDIFF(NOW(), MIN(fulldate)) as days,
                SUM(debet - kredit) as sisa_piutang
            FROM __rek_pembantu_subpiutangsuppliertrans__1010020030
            GROUP BY extern_id
            HAVING sisa_piutang > 0
        ) sub";

        $q = $this->db->query($sql);
        if ($q && $q->num_rows() > 0) {
            $r = $q->row_array();
            return array(
                "aging_0_30" => floatval($r['aging_0_30']),
                "aging_31_60" => floatval($r['aging_31_60']),
                "aging_61_90" => floatval($r['aging_61_90']),
                "aging_over_90" => floatval($r['aging_over_90']),
                "total_saldo" => floatval($r['total_saldo']),
            );
        }

        return array(
            "aging_0_30" => 0,
            "aging_31_60" => 0,
            "aging_61_90" => 0,
            "aging_over_90" => 0,
            "total_saldo" => 0,
        );
    }
}
// END OF COMPLETE REPEATED LOGIC


<?php
// START OF COMPLETE REPEATED LOGIC
if (!defined('BASEPATH')) exit('No direct script access allowed');
require_once "Modul_Controller.php";

class AuditKlaimSupplier extends Modul_Controller
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
        $this->load->model("Mdls/MdlCurrency");
        $this->load->helper('he_angka');
        $this->masterConfigUi = $this->config->item("heTransaksi_ui");

        if (empty($this->jenisTr)) {
            $this->jenisTr = "3333";
        }
    }

    public function index($jenisTr = "3333")
    {
        if (!isset($this->session->login['id'])) {
            redirect(base_url() . "Login");
        }

        if (empty($jenisTr)) {
            $jenisTr = $this->jenisTr;
        }

        $isMob = isMobile_he_misc();
        $getStep = isset($_GET['step']) ? $_GET['step'] : ($isMob == true ? 2 : 1);
        $baseUrl = base_url();
        $link_klaimList = MODUL_PATH . "AuditKlaimSupplier/viewAuditKlaimIndex/$jenisTr/?gr=cGVtYmVsaWFu&ohyes=ohno&step=$getStep";

        $scriptBottom = "";
        $scriptBottom .= "<script>";
        $scriptBottom .= "
            if (top.$('#undoneList').length) {
                top.$('#undoneList').hide().empty();
            }
            var viewklaimList = function () {
                top.$('#klaimList').html(\" <div class='row' style='margin-top:14px;padding-bottom:14px;'><span class='col-lg-12 text-center'><img width='5%' src='$baseUrl/assets/images/loading_16_p.gif'></span></div> \");
                setTimeout(function () {
                    if (top.$('#klaimList').find('img').length > 0) {
                        top.$('#klaimList').load('$link_klaimList', function(){viewklaimList});
                    }
                }, 1000);
            };
            viewklaimList();
        ";
        $scriptBottom .= "</script>";

        $steps = isset($this->configUi[$jenisTr]['steps']) ? $this->configUi[$jenisTr]['steps'] : array();
        $linkMenu = isset($this->configUi[$jenisTr]['linkMenu']) ? $this->configUi[$jenisTr]['linkMenu'] : array();
        $addLink = array(
            "link" => MODUL_PATH . "Create/index/$jenisTr",
            "label" => "REALISASI KLAIM DISKON KE SUPPLIER",
        );

        $data = array(
            "mode" => "index",
            "errMsg" => $this->session->errMsg,
            "template" => isset($this->configUi[$jenisTr]["template"]) ? $this->configUi[$jenisTr]["template"] : "",
            "title" => "AUDIT & VERIFIKASI KLAIM DISKON KE SUPPLIER",
            "subTitle" => "Tool Validasi & Rekonsiliasi Saldo Piutang Diskon",
            "jenisTr" => $jenisTr,
            "trName" => "AUDIT & VERIFIKASI KLAIM DISKON KE SUPPLIER",
            "addLink" => $addLink,
            "scriptBottom" => $scriptBottom,
            "steps" => $steps,
            "allSteps" => isset($this->allSteps) ? $this->allSteps : array(),
            "linkMenu" => $linkMenu,
        );

        $this->load->view("audit_klaim_supplier", $data);
    }

    public function viewAuditKlaimIndex($jenisTr = "3333")
    {
        $jenisTrsub = isset($_GET['step']) ? $_GET['step'] : 1;
        $starttime = microtime(true);
        if (!isset($this->session->login['id'])) {
            redirect(base_url() . "Login");
        }

        if (empty($jenisTr)) {
            $jenisTr = $this->jenisTr;
        }

        $viewKlaimSupplier = isset($this->configUi[$jenisTr]["viewKlaimSupplier"]) ? $this->configUi[$jenisTr]["viewKlaimSupplier"] : array();
        $historyFields = array();
        $historySumFields = array();
        $labelAlias = array();

        if (sizeof($viewKlaimSupplier) > 0) {
            $historyFields = isset($viewKlaimSupplier["historyFields"][1]) ? $viewKlaimSupplier["historyFields"][1] : array();
            $historySumFields = isset($viewKlaimSupplier["historySumFields"]) ? $viewKlaimSupplier["historySumFields"] : array();
            $labelAlias = isset($viewKlaimSupplier["labelAlias"]) ? $viewKlaimSupplier["labelAlias"] : array();
        }

        // Tambahkan kolom Status Audit ke label progres
        $historyFields['status_audit'] = "Status Validasi";

        $isMob = isMobile_he_misc();
        $steps = isset($this->configUi[$jenisTr]['steps']) ? $this->configUi[$jenisTr]['steps'] : array();

        $arrPihakData = array();
        if (isset($viewKlaimSupplier["addPairedModel"])) {
            $mdlName = $viewKlaimSupplier["addPairedModel"]["mdlName"];
            $this->load->model("Mdls/$mdlName");
            $mnm = new $mdlName();
            $mnmTmp = $mnm->lookupAll()->result();
            if (sizeof($mnmTmp) > 0) {
                foreach ($mnmTmp as $mnmSpec) {
                    $arrPihakData[$mnmSpec->id] = $mnmSpec->nama;
                }
            }
        }

        $getSupplierNama = "";
        if (isset($_GET["sid"]) && ($_GET["sid"] > 0)) {
            $getSupplierNama = (isset($arrPihakData[$_GET["sid"]])) ? "(" . $arrPihakData[$_GET["sid"]] . ")" : "";
            unset($historyFields["suppliers_nama"]);
        }

        $selisihAllIds = $this->getSelisihTransIds(0);
        $countSelisihAll = count($selisihAllIds);
        $initialFilterMode = isset($_GET['filterMode']) ? $_GET['filterMode'] : 'sisa';

        $data = array(
            "mode" => "viewKlaimSupplierIndex",
            "isMobile" => $isMob,
            "errMsg" => $this->session->errMsg,
            "template" => isset($this->configUi[$jenisTr]["template"]) ? $this->configUi[$jenisTr]["template"] : "",
            "title" => "AUDIT & VERIFIKASI KLAIM DISKON KE SUPPLIER",
            "subTitle" => "Rekonsiliasi Saldo Piutang Diskon Supplier",
            "jenisTr" => $jenisTr,
            "trName" => "AUDIT & VERIFIKASI KLAIM DISKON KE SUPPLIER",
            "allSteps" => isset($this->allSteps) ? $this->allSteps : array(),
            "onprogressTitle" => "<span class='fa fa-shield text-green'></span> AUDIT & VERIFIKASI KLAIM DISKON KE SUPPLIER &nbsp; $getSupplierNama",
            "arrayProgressLabels" => $historyFields,
            "steps" => $isMob == true && isset($steps[2]) ? array(2 => $steps[2]) : $steps,
            "arrPihakData" => $arrPihakData,
            "pihakDataSelected" => isset($_GET["sid"]) ? intval($_GET["sid"]) : 0,
            "linkDataSelected" => MODUL_PATH . "AuditKlaimSupplier/viewAuditKlaimIndex/" . $this->jenisTr . "/",
            "ajaxDataUrl" => MODUL_PATH . "AuditKlaimSupplier/dataAjax/" . $this->jenisTr . "/",
            "addTabHeaderLimit" => isset($viewKlaimSupplier["addTabHeaderLimit"]) ? $viewKlaimSupplier["addTabHeaderLimit"] : 0,
            "historySumFields" => isset($historySumFields) ? $historySumFields : array(),
            "labelAlias" => isset($labelAlias) ? $labelAlias : array(),
            "countSelisihAll" => $countSelisihAll,
            "initialFilterMode" => $initialFilterMode,
        );

        $this->load->view("audit_klaim_supplier", $data);
    }

    public function dataAjax($jenisTr = null)
    {
        if (!isset($this->session->login['id'])) {
            header('Content-Type: application/json');
            echo json_encode(array(
                "draw" => 0,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => array()
            ));
            exit;
        }

        if (empty($jenisTr)) {
            $jenisTr = $this->jenisTr;
        }

        $viewKlaimSupplier = isset($this->configUi[$jenisTr]["viewKlaimSupplier"]) ? $this->configUi[$jenisTr]["viewKlaimSupplier"] : array();
        $historyFields = array();
        $historySumFields = array();
        $jenisSource = array("467");
        $historyKlaimFields = array();
        $labelAlias = array();
        $kolomRegistry = array();
        $pairRegistries = isset($this->configUi[$jenisTr]['pairRegistries']) ? $this->configUi[$jenisTr]['pairRegistries'] : array();

        if (sizeof($viewKlaimSupplier) > 0) {
            $historyFields = isset($viewKlaimSupplier["historyFields"][1]) ? $viewKlaimSupplier["historyFields"][1] : array();
            $historySumFields = isset($viewKlaimSupplier["historySumFields"]) ? $viewKlaimSupplier["historySumFields"] : array();
            $jenisSource = isset($viewKlaimSupplier["jenisSource"]) ? $viewKlaimSupplier["jenisSource"] : array("467");
            $historyKlaimFields = isset($viewKlaimSupplier["historyKlaimFields"]) ? $viewKlaimSupplier["historyKlaimFields"] : array();
            $labelAlias = isset($viewKlaimSupplier["labelAlias"]) ? $viewKlaimSupplier["labelAlias"] : array();
            $kolomRegistry = isset($viewKlaimSupplier["kolom_registry"]) ? $viewKlaimSupplier["kolom_registry"] : array();
        }

        // Tambah kolom status_audit
        $historyFields['status_audit'] = "Status Validasi";

        $draw = isset($_GET['draw']) ? intval($_GET['draw']) : (isset($_POST['draw']) ? intval($_POST['draw']) : 1);
        $start = isset($_GET['start']) ? intval($_GET['start']) : (isset($_POST['start']) ? intval($_POST['start']) : 0);
        $length = isset($_GET['length']) ? intval($_GET['length']) : (isset($_POST['length']) ? intval($_POST['length']) : 10);
        if ($length <= 0 || $length > 100) {
            $length = 10;
        }

        $searchValue = '';
        if (isset($_GET['search_b64']) && trim($_GET['search_b64']) !== '') {
            $decoded = base64_decode($_GET['search_b64']);
            if ($decoded !== false) {
                $searchValue = trim($decoded);
            }
        } elseif (isset($_POST['search_b64']) && trim($_POST['search_b64']) !== '') {
            $decoded = base64_decode($_POST['search_b64']);
            if ($decoded !== false) {
                $searchValue = trim($decoded);
            }
        } elseif (isset($_GET['search']['value'])) {
            $searchValue = trim($_GET['search']['value']);
        } elseif (isset($_POST['search']['value'])) {
            $searchValue = trim($_POST['search']['value']);
        }

        $sid = isset($_GET['sid']) ? intval($_GET['sid']) : (isset($_POST['sid']) ? intval($_POST['sid']) : 0);
        $filterMode = isset($_GET['filterMode']) ? trim($_GET['filterMode']) : (isset($_POST['filterMode']) ? trim($_POST['filterMode']) : 'sisa');
        $diskonFilter = isset($_GET['diskonFilter']) ? trim($_GET['diskonFilter']) : (isset($_POST['diskonFilter']) ? trim($_POST['diskonFilter']) : 'all');

        if ($sid > 0) {
            unset($historyFields["suppliers_nama"]);
        }

        $columnMap = array();
        $colIdx = 0;
        $colIdx++; // Checkbox
        $colIdx++; // No.

        foreach ($historyFields as $fKey => $fVal) {
            if ($fKey === 'dtime') {
                $columnMap[$colIdx] = 'transaksi.dtime';
            } elseif ($fKey === 'suppliers_nama') {
                $columnMap[$colIdx] = 'transaksi.suppliers_nama';
            } elseif ($fKey === 'nomer') {
                $columnMap[$colIdx] = 'transaksi.nomer';
            } elseif ($fKey === 'oleh_nama') {
                $columnMap[$colIdx] = 'transaksi.oleh_nama';
            } elseif ($fKey === 'keterangan') {
                $columnMap[$colIdx] = 'transaksi.keterangan';
            }
            $colIdx++;
        }

        $orderColIdx = isset($_GET['order'][0]['column']) ? intval($_GET['order'][0]['column']) : (isset($_POST['order'][0]['column']) ? intval($_POST['order'][0]['column']) : 2);
        $orderDir = isset($_GET['order'][0]['dir']) ? strtolower(trim($_GET['order'][0]['dir'])) : (isset($_POST['order'][0]['dir']) ? strtolower(trim($_POST['order'][0]['dir'])) : 'desc');
        if ($orderDir !== 'asc' && $orderDir !== 'desc') {
            $orderDir = 'desc';
        }

        $orderBySql = "transaksi.dtime DESC";
        if (isset($columnMap[$orderColIdx])) {
            $orderBySql = $columnMap[$orderColIdx] . " " . strtoupper($orderDir);
        }

        $whereBase = "transaksi.jenis IN ('" . implode("','", $jenisSource) . "') AND transaksi.status = '1' AND transaksi.trash = '0' AND transaksi.link_id = '0'";
        $bindsBase = array();
        if ($sid > 0) {
            $whereBase .= " AND transaksi.suppliers_id = ?";
            $bindsBase[] = $sid;
        }

        $sqlCountTotal = "SELECT COUNT(*) as cnt FROM transaksi WHERE " . $whereBase;
        $qTotal = $this->db->query($sqlCountTotal, $bindsBase);
        $rowTotal = $qTotal->row();
        $countTotal = isset($rowTotal->cnt) ? intval($rowTotal->cnt) : 0;

        $sqlBadges = "SELECT 
            COUNT(DISTINCT c.extern_id) as total_sisa,
            COUNT(DISTINCT CASE WHEN c.extern2_id = 1 THEN c.extern_id END) as cnt_d1,
            COUNT(DISTINCT CASE WHEN c.extern2_id = 2 THEN c.extern_id END) as cnt_d2,
            COUNT(DISTINCT CASE WHEN c.extern2_id = 3 THEN c.extern_id END) as cnt_d3,
            COUNT(DISTINCT CASE WHEN c.extern2_id = 4 THEN c.extern_id END) as cnt_d4,
            COUNT(DISTINCT CASE WHEN c.extern2_id = 5 THEN c.extern_id END) as cnt_d5,
            COUNT(DISTINCT CASE WHEN c.extern2_id = 6 THEN c.extern_id END) as cnt_d6,
            COUNT(DISTINCT CASE WHEN c.extern2_id = 7 THEN c.extern_id END) as cnt_d7,
            COUNT(DISTINCT CASE WHEN c.extern2_id = 8 THEN c.extern_id END) as cnt_d8
        FROM _rek_pembantu_subpiutangsuppliertrans_cache c
        INNER JOIN transaksi ON transaksi.id = c.extern_id
        WHERE c.periode = 'forever' AND c.rekening = '1010020030' AND c.debet >= 1
          AND " . $whereBase;
        $qBadges = $this->db->query($sqlBadges, $bindsBase);
        $rowBadges = $qBadges->row_array();

        $sqlKatBadges = "SELECT 
            SUM(CASE WHEN has_fp = 0 AND has_reg = 1 THEN 1 ELSE 0 END) as cnt_reguler,
            SUM(CASE WHEN has_fp = 1 AND has_reg = 0 THEN 1 ELSE 0 END) as cnt_freeproduk,
            SUM(CASE WHEN has_fp = 1 AND has_reg = 1 THEN 1 ELSE 0 END) as cnt_kombinasi
        FROM (
            SELECT 
                c.extern_id,
                MAX(CASE WHEN c.extern2_id = 7 THEN 1 ELSE 0 END) as has_fp,
                MAX(CASE WHEN c.extern2_id != 7 THEN 1 ELSE 0 END) as has_reg
            FROM _rek_pembantu_subpiutangsuppliertrans_cache c
            INNER JOIN transaksi ON transaksi.id = c.extern_id
            WHERE c.periode = 'forever' AND c.rekening = '1010020030' AND c.debet >= 1
              AND " . $whereBase . "
            GROUP BY c.extern_id
        ) sub";
        $qKatBadges = $this->db->query($sqlKatBadges, $bindsBase);
        $rowKatBadges = $qKatBadges->row_array();

        $selisihAllIds = $this->getSelisihTransIds(0);
        $countSelisihAll = count($selisihAllIds);
        $countSelisihSid = ($sid > 0) ? count($this->getSelisihTransIds($sid)) : $countSelisihAll;

        $diskonBadges = array(
            "all" => isset($rowBadges['total_sisa']) ? intval($rowBadges['total_sisa']) : 0,
            "reguler" => isset($rowKatBadges['cnt_reguler']) ? intval($rowKatBadges['cnt_reguler']) : 0,
            "freeproduk" => isset($rowKatBadges['cnt_freeproduk']) ? intval($rowKatBadges['cnt_freeproduk']) : 0,
            "kombinasi" => isset($rowKatBadges['cnt_kombinasi']) ? intval($rowKatBadges['cnt_kombinasi']) : 0,
            "selisih" => $countSelisihSid,
            "selisih_all" => $countSelisihAll,
            "d1" => isset($rowBadges['cnt_d1']) ? intval($rowBadges['cnt_d1']) : 0,
            "d2" => isset($rowBadges['cnt_d2']) ? intval($rowBadges['cnt_d2']) : 0,
            "d3" => isset($rowBadges['cnt_d3']) ? intval($rowBadges['cnt_d3']) : 0,
            "d4" => isset($rowBadges['cnt_d4']) ? intval($rowBadges['cnt_d4']) : 0,
            "d5" => isset($rowBadges['cnt_d5']) ? intval($rowBadges['cnt_d5']) : 0,
            "d6" => isset($rowBadges['cnt_d6']) ? intval($rowBadges['cnt_d6']) : 0,
            "d7" => isset($rowBadges['cnt_d7']) ? intval($rowBadges['cnt_d7']) : 0,
        );

        $joinSisa = "";
        if ($diskonFilter === 'reguler') {
            $joinSisa = "INNER JOIN (
                SELECT extern_id 
                FROM _rek_pembantu_subpiutangsuppliertrans_cache 
                WHERE periode = 'forever' AND rekening = '1010020030' AND debet >= 1
                GROUP BY extern_id
                HAVING MAX(CASE WHEN extern2_id = 7 THEN 1 ELSE 0 END) = 0
                   AND MAX(CASE WHEN extern2_id != 7 THEN 1 ELSE 0 END) = 1
            ) c ON c.extern_id = transaksi.id";
        } elseif ($diskonFilter === 'freeproduk') {
            $joinSisa = "INNER JOIN (
                SELECT extern_id 
                FROM _rek_pembantu_subpiutangsuppliertrans_cache 
                WHERE periode = 'forever' AND rekening = '1010020030' AND debet >= 1
                GROUP BY extern_id
                HAVING MAX(CASE WHEN extern2_id = 7 THEN 1 ELSE 0 END) = 1
                   AND MAX(CASE WHEN extern2_id != 7 THEN 1 ELSE 0 END) = 0
            ) c ON c.extern_id = transaksi.id";
        } elseif ($diskonFilter === 'kombinasi') {
            $joinSisa = "INNER JOIN (
                SELECT extern_id 
                FROM _rek_pembantu_subpiutangsuppliertrans_cache 
                WHERE periode = 'forever' AND rekening = '1010020030' AND debet >= 1
                GROUP BY extern_id
                HAVING MAX(CASE WHEN extern2_id = 7 THEN 1 ELSE 0 END) = 1
                   AND MAX(CASE WHEN extern2_id != 7 THEN 1 ELSE 0 END) = 1
            ) c ON c.extern_id = transaksi.id";
        } elseif (substr($diskonFilter, 0, 1) === 'd') {
            $dNum = intval(substr($diskonFilter, 1));
            if ($dNum > 0) {
                $joinSisa = "INNER JOIN (
                    SELECT DISTINCT extern_id 
                    FROM _rek_pembantu_subpiutangsuppliertrans_cache 
                    WHERE periode = 'forever' AND rekening = '1010020030' AND debet >= 1 AND extern2_id = " . $dNum . "
                ) c ON c.extern_id = transaksi.id";
            }
        } elseif ($filterMode === 'selisih') {
            $selisihIds = ($sid > 0) ? $this->getSelisihTransIds($sid) : $selisihAllIds;
            if (empty($selisihIds)) {
                $selisihIds = array(-1);
            }
            $joinSisa = "INNER JOIN (
                SELECT id as extern_id FROM transaksi WHERE id IN (" . implode(",", $selisihIds) . ")
            ) c ON c.extern_id = transaksi.id";
        } elseif ($filterMode === 'sisa') {
            $joinSisa = "INNER JOIN (
                SELECT DISTINCT extern_id 
                FROM _rek_pembantu_subpiutangsuppliertrans_cache 
                WHERE periode = 'forever' AND rekening = '1010020030' AND debet >= 1
            ) c ON c.extern_id = transaksi.id";
        }

        if ($joinSisa !== "") {
            $sqlCountFTotal = "SELECT COUNT(DISTINCT transaksi.id) as cnt FROM transaksi $joinSisa WHERE " . $whereBase;
            $qFTotal = $this->db->query($sqlCountFTotal, $bindsBase);
            $rowFTotal = $qFTotal->row();
            $recordsTotal = isset($rowFTotal->cnt) ? intval($rowFTotal->cnt) : 0;
        } else {
            $recordsTotal = $countTotal;
        }

        $whereFiltered = $whereBase;
        $bindsFiltered = $bindsBase;

        if ($searchValue !== '') {
            $searchTerms = array();
            $searchTerms[] = $searchValue;

            $stripped = preg_replace('/-[0-9]{4,6}$/', '', $searchValue);
            if ($stripped !== $searchValue && trim($stripped) !== '') {
                $searchTerms[] = trim($stripped);
            }

            $whereSearchParts = array();
            foreach ($searchTerms as $term) {
                $whereSearchParts[] = "transaksi.nomer LIKE " . $this->db->escape('%' . $term . '%');
                $whereSearchParts[] = "transaksi.suppliers_nama LIKE " . $this->db->escape('%' . $term . '%');
                $whereSearchParts[] = "transaksi.oleh_nama LIKE " . $this->db->escape('%' . $term . '%');
                $whereSearchParts[] = "transaksi.keterangan LIKE " . $this->db->escape('%' . $term . '%');
                $whereSearchParts[] = "transaksi.nomer_top LIKE " . $this->db->escape('%' . $term . '%');
                $whereSearchParts[] = "transaksi.ids_his LIKE " . $this->db->escape('%' . $term . '%');
            }
            $whereFiltered .= " AND (" . implode(" OR ", $whereSearchParts) . ")";

            $sqlFiltered = "SELECT COUNT(DISTINCT transaksi.id) as cnt FROM transaksi $joinSisa WHERE " . $whereFiltered;
            $qF = $this->db->query($sqlFiltered, $bindsFiltered);
            $rowF = $qF->row();
            $recordsFiltered = isset($rowF->cnt) ? intval($rowF->cnt) : 0;
        } else {
            $recordsFiltered = $recordsTotal;
        }

        $sqlData = "SELECT transaksi.* FROM transaksi $joinSisa WHERE " . $whereFiltered . " ORDER BY " . $orderBySql . " LIMIT " . intval($length) . " OFFSET " . intval($start);
        $resData = $this->db->query($sqlData, $bindsFiltered);
        $tmpHist = $resData->result();

        $arrTransID = array();
        if (sizeof($tmpHist) > 0) {
            foreach ($tmpHist as $row) {
                $arrTransID[] = $row->id;
            }
        }

        // 1. Paired Model: Rekening Pembantu Cache Diskon
        $arrDiskonData = array();
        $arrDiskonDataByTransaksi = array();
        if (sizeof($arrTransID) > 0 && isset($viewKlaimSupplier["pairedModel"])) {
            $mdlName = $viewKlaimSupplier["pairedModel"]["comName"];
            $filter = $viewKlaimSupplier["pairedModel"]["filter"];
            $this->load->model("Coms/$mdlName");
            $mn = new $mdlName();
            foreach ($filter as $ff) {
                $mn->addFilter($ff);
            }
            $mn->addFilter("extern_id in ('" . implode("','", $arrTransID) . "')");
            $mnTmp = $mn->lookupAll()->result();
            if (sizeof($mnTmp) > 0) {
                foreach ($mnTmp as $mnSpec) {
                    $arrDiskonData[$mnSpec->extern_id][$mnSpec->extern2_id] = array(
                        "transaksi_id" => $mnSpec->extern_id,
                        "qty_debet" => $mnSpec->qty_debet,
                        "debet" => $mnSpec->debet,
                        "suppliers_id" => $mnSpec->extern3_id,
                        "suppliers_nama" => $mnSpec->extern3_nama,
                        "extern2_id" => $mnSpec->extern2_id,
                        "extern2_nama" => $mnSpec->extern2_nama,
                    );
                    if (!isset($arrDiskonDataByTransaksi[$mnSpec->extern_id]["sisa_diskon"])) {
                        $arrDiskonDataByTransaksi[$mnSpec->extern_id]["sisa_diskon"] = 0;
                    }
                    $arrDiskonDataByTransaksi[$mnSpec->extern_id]["sisa_diskon"] += $mnSpec->debet;
                }
            }
        }

        // 2. Locker Stock Diskon
        $arrDiskonLockerData = array();
        $arrDiskonLockerDataDiklaim = array();
        if (sizeof($arrTransID) > 0 && isset($viewKlaimSupplier["addPairedModelLocker"])) {
            $mdlName = $viewKlaimSupplier["addPairedModelLocker"]["mdlName"];
            $filter = $viewKlaimSupplier["addPairedModelLocker"]["filter"];
            $this->load->model("Mdls/$mdlName");
            $mnl = new $mdlName();
            foreach ($filter as $ff) {
                $mnl->addFilter($ff);
            }
            $mnl->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
            $mnlTmp = $mnl->lookupAll()->result();
            if (sizeof($mnlTmp) > 0) {
                foreach ($mnlTmp as $mnSpec) {
                    if (!isset($arrDiskonLockerData[$mnSpec->transaksi_id][$mnSpec->produk_id]["nilai_klaim"])) {
                        $arrDiskonLockerData[$mnSpec->transaksi_id][$mnSpec->produk_id]["nilai_klaim"] = 0;
                    }
                    $arrDiskonLockerData[$mnSpec->transaksi_id][$mnSpec->produk_id]["nilai_klaim"] += $mnSpec->nilai;
                    if (!isset($arrDiskonLockerData[$mnSpec->transaksi_id][$mnSpec->produk_id]["nilai_diklaim"])) {
                        $arrDiskonLockerData[$mnSpec->transaksi_id][$mnSpec->produk_id]["nilai_diklaim"] = 0;
                    }
                    $arrDiskonLockerData[$mnSpec->transaksi_id][$mnSpec->produk_id]["nilai_diklaim"] += $mnSpec->nilai_diklaim;
                    if (!isset($arrDiskonLockerDataDiklaim[$mnSpec->transaksi_id]["nilai_total_diklaim"])) {
                        $arrDiskonLockerDataDiklaim[$mnSpec->transaksi_id]["nilai_total_diklaim"] = 0;
                    }
                    $arrDiskonLockerDataDiklaim[$mnSpec->transaksi_id]["nilai_total_diklaim"] += $mnSpec->nilai_diklaim;
                }
            }
        }

        // 3. Klaim yang sudah pernah diproses (transaksi 3333)
        $arrTransaksiDataKlaim = array();
        if (sizeof($arrTransID) > 0 && isset($viewKlaimSupplier["addPairedModelKlaim"])) {
            $mdlName = $viewKlaimSupplier["addPairedModelKlaim"]["mdlName"];
            $filter = $viewKlaimSupplier["addPairedModelKlaim"]["filter"];
            if (!class_exists($mdlName)) {
                if (file_exists(APPPATH . "models/$mdlName.php")) {
                    $this->load->model($mdlName);
                } elseif (file_exists(APPPATH . "models/Mdls/$mdlName.php")) {
                    $this->load->model("Mdls/$mdlName");
                }
            }
            $mnl = new $mdlName();
            foreach ($filter as $ff) {
                $mnl->addFilter($ff);
            }
            $mnl->addFilter("reference_id in ('" . implode("','", $arrTransID) . "')");
            $mnlTmp = $mnl->lookupAll()->result();
            $tridKlaim = array();
            if (sizeof($mnlTmp) > 0) {
                foreach ($mnlTmp as $ii => $mnSpec) {
                    $arrTransaksiDataKlaim[$mnSpec->reference_id][$ii] = array(
                        "id" => $mnSpec->id,
                        "nomer" => $mnSpec->nomer,
                        "jenis" => $mnSpec->jenis,
                        "jenis_master" => $mnSpec->jenis_master,
                        "oleh_nama" => $mnSpec->oleh_nama,
                        "dtime" => $mnSpec->dtime,
                        "fulldate" => $mnSpec->fulldate,
                    );
                    $tridKlaim[$mnSpec->id] = $mnSpec->id;
                }
                if (sizeof($tridKlaim) > 0) {
                    $tbl = "__rek_pembantu_subpiutangsuppliertrans__1010020030";
                    $this->db->where_in("transaksi_id", $tridKlaim);
                    $queryMutasi = $this->db->get($tbl)->result();
                    if (sizeof($queryMutasi) > 0) {
                        foreach ($queryMutasi as $spec) {
                            if (!isset($arrDiskonLockerDataDiklaim[$spec->extern_id]["nilai_total_diklaim_mutasi"])) {
                                $arrDiskonLockerDataDiklaim[$spec->extern_id]["nilai_total_diklaim_mutasi"] = 0;
                            }
                            $arrDiskonLockerDataDiklaim[$spec->extern_id]["nilai_total_diklaim_mutasi"] += $spec->kredit;
                        }
                    }
                }
            }
        }

        // 4. Registry values
        $tmpReg_result = array();
        if (sizeof($arrTransID) > 0 && sizeof($pairRegistries) > 0) {
            $selectKolom = implode(",", $pairRegistries) . ", transaksi_id";
            $this->load->model("MdlTransaksi");
            $trReg = new MdlTransaksi();
            $trReg->setFilters(array());
            $trReg->setJointSelectFields($selectKolom);
            $trReg->addFilter("transaksi_id in ('" . implode("','", $arrTransID) . "')");
            $tmpReg = $trReg->lookupDataRegistries()->result();
            if (sizeof($tmpReg) > 0) {
                foreach ($tmpReg as $regRow) {
                    foreach ($regRow as $key_reg => $val_reg) {
                        if ($key_reg != "transaksi_id") {
                            $tmpReg_result[$regRow->transaksi_id][$key_reg] = blobDecode($val_reg);
                        }
                    }
                }
            }
        }

        // Global sum query
        $sqlSumGlobal = "SELECT 
            SUM(CASE WHEN c.extern2_id = 1 THEN c.debet ELSE 0 END) as diskon_1_nilai,
            SUM(CASE WHEN c.extern2_id = 2 THEN c.debet ELSE 0 END) as diskon_2_nilai,
            SUM(CASE WHEN c.extern2_id = 3 THEN c.debet ELSE 0 END) as diskon_3_nilai,
            SUM(CASE WHEN c.extern2_id = 4 THEN c.debet ELSE 0 END) as diskon_4_nilai,
            SUM(CASE WHEN c.extern2_id = 5 THEN c.debet ELSE 0 END) as diskon_5_nilai,
            SUM(CASE WHEN c.extern2_id = 6 THEN c.debet ELSE 0 END) as diskon_6_nilai,
            SUM(CASE WHEN c.extern2_id = 7 THEN c.debet ELSE 0 END) as diskon_7_nilai,
            SUM(CASE WHEN c.extern2_id = 8 THEN c.debet ELSE 0 END) as diskon_8_nilai,
            SUM(c.debet) as sisa_diskon
        FROM _rek_pembantu_subpiutangsuppliertrans_cache c
        INNER JOIN transaksi ON transaksi.id = c.extern_id
        WHERE c.periode = 'forever' AND c.rekening = '1010020030' AND c.debet >= 1
          AND " . $whereFiltered;
        $qSumG = $this->db->query($sqlSumGlobal, $bindsFiltered);
        $rowSumG = $qSumG->row_array();
        $sumGlobal = is_array($rowSumG) ? $rowSumG : array();

        $dataRows = array();
        $sumPage = array();
        $no = $start;

        foreach ($tmpHist as $row) {
            $no++;

            if (sizeof($pairRegistries) > 0 && sizeof($tmpReg_result) > 0 && isset($tmpReg_result[$row->id])) {
                foreach ($tmpReg_result[$row->id] as $param => $eReg) {
                    if ($param == "main" && is_array($eReg)) {
                        foreach ($eReg as $k => $v) {
                            if (in_array($k, $kolomRegistry) && !isset($row->$k)) {
                                $row->$k = $v;
                            }
                        }
                    }
                }
                $relHarga = isset($row->produk_rel_harga) ? floatval($row->produk_rel_harga) : 0;
                $totDiskon = isset($row->diskon_nilai_total) ? floatval($row->diskon_nilai_total) : 0;
                $row->diskon_nilai_total_freeproduk = $totDiskon + $relHarga;
            } else {
                if (!isset($row->diskon_nilai_total_freeproduk)) {
                    $row->diskon_nilai_total_freeproduk = 0;
                }
            }

            if (isset($arrDiskonData[$row->id])) {
                foreach ($arrDiskonData[$row->id] as $diskon_id => $diskonSpec) {
                    $new_key = "diskon_" . $diskon_id . "_nilai";
                    $row->$new_key = $diskonSpec["debet"];
                    foreach ($diskonSpec as $kk => $vv) {
                        $new_kk = "rek_" . $diskon_id . "_" . $kk;
                        $row->$new_kk = $vv;
                    }
                }
            }

            if (isset($arrDiskonLockerData[$row->id])) {
                foreach ($arrDiskonLockerData[$row->id] as $diskon_id => $klaimSpec) {
                    foreach ($klaimSpec as $kk => $vv) {
                        $new_kk = $kk . "_" . $diskon_id;
                        $row->$new_kk = $vv;
                    }
                }
            }

            if (isset($arrDiskonLockerDataDiklaim[$row->id])) {
                foreach ($arrDiskonLockerDataDiklaim[$row->id] as $kky => $vval) {
                    $row->$kky = $vval;
                }
                if (isset($row->nilai_total_diklaim) && $row->nilai_total_diklaim == 0 && isset($row->nilai_total_diklaim_mutasi)) {
                    $row->nilai_total_diklaim = $row->nilai_total_diklaim_mutasi;
                }
            }

            if (isset($arrDiskonDataByTransaksi[$row->id])) {
                foreach ($arrDiskonDataByTransaksi[$row->id] as $kky => $vval) {
                    $row->$kky = $vval;
                }
            }

            $jenis_trans = $row->jenis_master;
            $modules = isset($this->masterConfigUi[$jenis_trans]["modul"]) ? $this->masterConfigUi[$jenis_trans]["modul"] : false;
            $modules_path = base_url() . $modules . "/";

            $hasFreeProduk = 0;
            $hasReguler = 0;
            if (isset($arrDiskonData[$row->id])) {
                foreach ($arrDiskonData[$row->id] as $dId => $dSpec) {
                    if (isset($dSpec["debet"]) && floatval($dSpec["debet"]) >= 1) {
                        if ($dId == 7) {
                            $hasFreeProduk = 1;
                        } else {
                            $hasReguler = 1;
                        }
                    }
                }
            }

            $raw_sisa = isset($row->sisa_diskon) ? round(floatval($row->sisa_diskon)) : 0;
            $row_has_sisa = ($raw_sisa > 0) ? 1 : 0;
            $grn_id = $row->id;
            $grn_nomer = $row->nomer;
            $row_supplier_id = isset($row->suppliers_id) ? $row->suppliers_id : 0;
            $row_supplier_nama = isset($row->suppliers_nama) ? htmlspecialchars($row->suppliers_nama, ENT_QUOTES) : "";
            $cb_disabled = ($row_has_sisa == 0) ? "disabled" : "";
            $cb_title = ($row_has_sisa == 0) ? "Sisa diskon 0 (sudah diklaim penuh)" : "Pilih nota ini";

            $cbHtml = "-";
            if ($grn_id > 0) {
                $cbHtml = "<input type='checkbox' class='check_grn_klaim' value='$grn_id' data-sisa='$raw_sisa' data-nomer='$grn_nomer' data-supplier-id='$row_supplier_id' data-supplier-nama='$row_supplier_nama' data-freeproduk='$hasFreeProduk' data-reguler='$hasReguler' $cb_disabled title='$cb_title' style='cursor:" . ($cb_disabled ? "not-allowed" : "pointer") . "; transform: scale(1.15);'>";
            }

            // Hitung Rekonsiliasi Balance
            $valPlafon = isset($row->diskon_nilai_total_freeproduk) ? floatval($row->diskon_nilai_total_freeproduk) : 0;
            $valKlaim = isset($row->nilai_total_diklaim) ? floatval($row->nilai_total_diklaim) : 0;
            $valSisa = isset($row->sisa_diskon) ? floatval($row->sisa_diskon) : 0;
            $selisih = abs(($valSisa + $valKlaim) - $valPlafon);
            $isBalance = ($selisih <= 5) ? true : false;

            if ($isBalance) {
                $statusAuditBadge = "<span class='badge bg-green' style='font-size:11px;' title='Data Balance: Sisa + Klaim = Plafon'><i class='fa fa-check-circle'></i> Valid</span>";
            } else {
                $statusAuditBadge = "<span class='badge bg-red' style='font-size:11px;' title='Selisih Rp " . number_format($selisih, 0, ',', '.') . "'><i class='fa fa-exclamation-triangle'></i> Selisih Rp " . number_format($selisih, 0, ',', '.') . "</span>";
            }

            $rowCols = array();
            $rowCols[] = $cbHtml;
            $rowCols[] = $no;

            foreach ($historyFields as $fName => $fLabel) {
                if ($fName === 'status_audit') {
                    $rowCols[] = $statusAuditBadge;
                    continue;
                }

                $cellHtml = "-";
                if (is_array($fLabel)) {
                    $hisStep = $fLabel['step'];
                    $hisKey = $fLabel['key'];
                    if (isset($row->ids_his) && $row->ids_his != "") {
                        if ($hisKey == "nomer") {
                            $returnVal = showHistoriGlobalNumbers($row->ids_his, $hisStep, true, $jenis_trans);
                            $cellHtml = ($returnVal != "") ? $returnVal : (!empty($row->nomer_top) ? $row->nomer_top : "-");
                        } else {
                            $ids_his_decode = blobDecode($row->ids_his);
                            $cellHtml = isset($ids_his_decode[$hisStep][$hisKey]) ? $ids_his_decode[$hisStep][$hisKey] : "-";
                        }
                    } else {
                        $cellHtml = !empty($row->nomer_top) ? $row->nomer_top : "-";
                    }
                } else {
                    $valRaw = isset($row->$fName) ? $row->$fName : "0";

                    if ($fName === 'nomer') {
                        // Interactive drill-down link for GRN
                        $cellHtml = "<a href='javascript:void(0)' onclick='bukaAuditGrn(" . intval($row->id) . ")' title='Audit Rincian GRN' class='text-bold text-navy'><i class='fa fa-search-plus text-blue'></i> " . htmlspecialchars($valRaw, ENT_QUOTES) . "</a>";
                    } elseif ($fName === 'nilai_total_diklaim') {
                        $numericKlaim = is_numeric($valRaw) ? floatval($valRaw) : 0;
                        $formattedKlaim = number_format($numericKlaim, 0, ',', '.');
                        if ($numericKlaim > 0) {
                            $cellHtml = "<a href='javascript:void(0)' onclick='bukaAuditMutasi(" . intval($row->id) . ")' title='Lihat Riwayat Pencairan Klaim' class='text-bold text-red' style='text-decoration: underline;'><i class='fa fa-history'></i> Rp " . $formattedKlaim . "</a>";
                        } else {
                            $cellHtml = "Rp 0";
                        }
                    } else {
                        $cellHtml = formatField_he_format($fName, $valRaw, $jenis_trans, $modules_path);
                    }

                    if (array_key_exists($fName, $historySumFields)) {
                        $numericVal = is_numeric($valRaw) ? floatval($valRaw) : 0;
                        if (!isset($sumPage[$fName])) {
                            $sumPage[$fName] = 0;
                        }
                        $sumPage[$fName] += $numericVal;

                        $sisa_klaim = floor($numericVal);
                        $key_diklaim = isset($historyKlaimFields[$fName]) ? $historyKlaimFields[$fName] : 0;
                        $nilai_diklaim = (isset($row->$key_diklaim) && is_numeric($row->$key_diklaim)) ? floor(floatval($row->$key_diklaim)) : 0;

                        if ($sisa_klaim > 0 && $nilai_diklaim == 0) {
                            $key_alias_val = 3;
                        } elseif ($sisa_klaim > 0 && $nilai_diklaim > 0) {
                            $key_alias_val = 4;
                        } elseif ($sisa_klaim == 0 && $nilai_diklaim > 0) {
                            $key_alias_val = 5;
                        } else {
                            $key_alias_val = 2;
                        }

                        $alias = isset($labelAlias[$key_alias_val]) ? $labelAlias[$key_alias_val] : "";
                        if ($key_alias_val == 3) {
                            $cellHtml .= "<div style='color: #f39c12; font-size: 10px; font-weight: bold;'>$alias</div>";
                        } elseif ($key_alias_val == 4) {
                            $cellHtml .= "<div style='color: #3c8dbc; font-size: 10px; font-weight: bold;'>$alias</div>";
                        } elseif ($key_alias_val == 5) {
                            $cellHtml .= "<div style='color: #00a65a; font-size: 10px; font-weight: bold;'>$alias</div>";
                        }
                    }
                }
                $rowCols[] = $cellHtml;
            }
            $dataRows[] = $rowCols;
        }

        if (ob_get_length()) {
            ob_clean();
        }
        header('Content-Type: application/json');
        echo json_encode(array(
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "countSisa" => isset($diskonBadges["all"]) ? $diskonBadges["all"] : 0,
            "countTotal" => $countTotal,
            "countSelisih" => $countSelisihSid,
            "countSelisihAll" => $countSelisihAll,
            "diskonBadges" => $diskonBadges,
            "sumFooterPage" => $sumPage,
            "sumFooterGlobal" => $sumGlobal,
            "data" => $dataRows
        ));
        exit;
    }

    private function getSelisihTransIds($sid = 0)
    {
        $whereSupp = ($sid > 0) ? " AND t.suppliers_id = " . intval($sid) : "";
        $sqlSisa = "SELECT c.extern_id, SUM(c.debet) as sisa 
                    FROM _rek_pembantu_subpiutangsuppliertrans_cache c
                    INNER JOIN transaksi t ON t.id = c.extern_id
                    WHERE c.periode = 'forever' AND c.rekening = '1010020030'
                      AND t.jenis = '467' AND t.status = '1' AND t.trash = '0' AND t.link_id = '0'
                      $whereSupp
                    GROUP BY c.extern_id HAVING sisa >= 1";
        $qS = $this->db->query($sqlSisa)->result();
        if (empty($qS)) {
            return array();
        }

        $sisaMap = array();
        $ids = array();
        foreach ($qS as $r) {
            $sisaMap[$r->extern_id] = floatval($r->sisa);
            $ids[] = intval($r->extern_id);
        }

        if (empty($ids)) {
            return array();
        }

        $idList = implode(',', $ids);

        $sqlKlaim = "SELECT extern_id, SUM(kredit) as klaim 
                     FROM __rek_pembantu_subpiutangsuppliertrans__1010020030 
                     WHERE extern_id IN ($idList) 
                     GROUP BY extern_id";
        $qK = $this->db->query($sqlKlaim)->result();
        $klaimMap = array();
        if (sizeof($qK) > 0) {
            foreach ($qK as $r) {
                $klaimMap[$r->extern_id] = floatval($r->klaim);
            }
        }

        $sqlR = "SELECT transaksi_id, main 
                 FROM transaksi_data_registry 
                 WHERE transaksi_id IN ($idList)";
        $qR = $this->db->query($sqlR)->result();
        $regMap = array();
        if (sizeof($qR) > 0) {
            foreach ($qR as $r) {
                $main = blobDecode($r->main);
                $d = isset($main['diskon_nilai_total']) ? floatval($main['diskon_nilai_total']) : 0;
                $p = isset($main['produk_rel_harga']) ? floatval($main['produk_rel_harga']) : 0;
                $regMap[$r->transaksi_id] = $d + $p;
            }
        }

        $selisihIds = array();
        foreach ($ids as $id) {
            $valSisa = isset($sisaMap[$id]) ? $sisaMap[$id] : 0;
            $valKlaim = isset($klaimMap[$id]) ? $klaimMap[$id] : 0;
            $valPlafon = isset($regMap[$id]) ? $regMap[$id] : 0;
            if (abs(($valSisa + $valKlaim) - $valPlafon) > 5) {
                $selisihIds[] = $id;
            }
        }

        return $selisihIds;
    }

    public function previewGrnDetailAjax($param1 = 0, $param2 = 0)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        ob_start();

        $grnId = ($param2 > 0) ? intval($param2) : intval($param1);
        if ($grnId <= 0) {
            echo "<div class='alert alert-danger'>ID GRN tidak valid.</div>";
            exit;
        }

        $sql = "SELECT 
                    t.id, t.nomer, t.dtime, t.suppliers_id, t.suppliers_nama, t.nomer_top, t.oleh_nama, t.keterangan,
                    r.main, r.items
                FROM transaksi t
                LEFT JOIN transaksi_data_registry r ON r.transaksi_id = t.id
                WHERE t.id = ?";
        $q = $this->db->query($sql, array($grnId))->row();

        if (!$q) {
            echo "<div class='alert alert-warning'>Data GRN #$grnId tidak ditemukan di sistem.</div>";
            exit;
        }

        $mainData = isset($q->main) ? blobDecode($q->main) : array();
        if (empty($mainData) && isset($q->main) && is_string($q->main) && $q->main !== "") {
            $rawDec = base64_decode($q->main, true);
            if ($rawDec !== false) {
                $alt = @unserialize($rawDec);
                if (is_array($alt)) {
                    $mainData = $alt;
                }
            }
        }

        $itemsData = isset($q->items) ? blobDecode($q->items) : array();
        if (empty($itemsData) && isset($q->items) && is_string($q->items) && $q->items !== "") {
            $rawDec = base64_decode($q->items, true);
            if ($rawDec !== false) {
                $alt = @unserialize($rawDec);
                if (is_array($alt)) {
                    $itemsData = $alt;
                }
            }
        }

        $nomerGrn = isset($q->nomer) ? $q->nomer : "-";
        $nomerPo = isset($q->nomer_top) ? $q->nomer_top : "-";
        $tglGrn = isset($q->dtime) ? $q->dtime : "-";
        $supplierNama = isset($q->suppliers_nama) ? $q->suppliers_nama : "-";
        $petugas = isset($q->oleh_nama) ? $q->oleh_nama : "-";
        $plafonDiskon = isset($mainData['diskon_nilai_total']) ? floatval($mainData['diskon_nilai_total']) : 0;
        $relHarga = isset($mainData['produk_rel_harga']) ? floatval($mainData['produk_rel_harga']) : 0;
        $totalPlafon = $plafonDiskon + $relHarga;

        $processedItems = array();
        $grandQty = 0;
        $grandBruto = 0;
        $grandNetto = 0;
        $grandD1Sub = 0;
        $grandD2Sub = 0;
        $grandD3Sub = 0;
        $grandD4Sub = 0;
        $grandD5Sub = 0;
        $grandD6Sub = 0;
        $grandD7Sub = 0;
        $grandFpSub = 0;
        $grandDiskonTotalSub = 0;

        if (is_array($itemsData) && sizeof($itemsData) > 0) {
            foreach ($itemsData as $it) {
                $nama = isset($it['nama']) ? $it['nama'] : (isset($it['name']) ? $it['name'] : '-');
                $qty = isset($it['qty']) ? floatval($it['qty']) : (isset($it['jml']) ? floatval($it['jml']) : 0);
                $harga = isset($it['harga']) ? floatval($it['harga']) : (isset($it['nilai']) ? floatval($it['nilai']) : 0);
                $bruto = $qty * $harga;

                // 8 Jenis Diskon Per 1 Unit (Statis Penuh: Diskon 1-7 + Free Produk)
                $d1_u = isset($it['diskon_1_nilai']) ? floatval($it['diskon_1_nilai']) : 0;
                $d2_u = isset($it['diskon_2_nilai']) ? floatval($it['diskon_2_nilai']) : 0;
                $d3_u = isset($it['diskon_3_nilai']) ? floatval($it['diskon_3_nilai']) : 0;
                $d4_u = isset($it['diskon_4_nilai']) ? floatval($it['diskon_4_nilai']) : 0;
                $d5_u = isset($it['diskon_5_nilai']) ? floatval($it['diskon_5_nilai']) : 0;
                $d6_u = isset($it['diskon_6_nilai']) ? floatval($it['diskon_6_nilai']) : 0;
                $d7_u = isset($it['diskon_8_nilai']) ? floatval($it['diskon_8_nilai']) : 0;
                $fp_u = isset($it['produk_rel_harga']) ? floatval($it['produk_rel_harga']) : (isset($it['diskon_7_nilai']) ? floatval($it['diskon_7_nilai']) : 0);

                $tot_diskon_u = isset($it['diskon_nilai_total']) ? floatval($it['diskon_nilai_total']) : ($d1_u + $d2_u + $d3_u + $d4_u + $d5_u + $d6_u + $d7_u + $fp_u);

                // 8 Jenis Diskon Subtotal Baris (Qty x Unit)
                $d1_s = isset($it['sub_diskon_1_nilai']) && floatval($it['sub_diskon_1_nilai']) > 0 ? floatval($it['sub_diskon_1_nilai']) : ($d1_u * $qty);
                $d2_s = isset($it['sub_diskon_2_nilai']) && floatval($it['sub_diskon_2_nilai']) > 0 ? floatval($it['sub_diskon_2_nilai']) : ($d2_u * $qty);
                $d3_s = isset($it['sub_diskon_3_nilai']) && floatval($it['sub_diskon_3_nilai']) > 0 ? floatval($it['sub_diskon_3_nilai']) : ($d3_u * $qty);
                $d4_s = isset($it['sub_diskon_4_nilai']) && floatval($it['sub_diskon_4_nilai']) > 0 ? floatval($it['sub_diskon_4_nilai']) : ($d4_u * $qty);
                $d5_s = isset($it['sub_diskon_5_nilai']) && floatval($it['sub_diskon_5_nilai']) > 0 ? floatval($it['sub_diskon_5_nilai']) : ($d5_u * $qty);
                $d6_s = isset($it['sub_diskon_6_nilai']) && floatval($it['sub_diskon_6_nilai']) > 0 ? floatval($it['sub_diskon_6_nilai']) : ($d6_u * $qty);
                $d7_s = isset($it['sub_diskon_8_nilai']) && floatval($it['sub_diskon_8_nilai']) > 0 ? floatval($it['sub_diskon_8_nilai']) : ($d7_u * $qty);
                $fp_s = isset($it['sub_produk_rel_harga']) && floatval($it['sub_produk_rel_harga']) > 0 ? floatval($it['sub_produk_rel_harga']) : (isset($it['sub_diskon_7_nilai']) && floatval($it['sub_diskon_7_nilai']) > 0 ? floatval($it['sub_diskon_7_nilai']) : ($fp_u * $qty));

                $tot_diskon_s = isset($it['sub_diskon_nilai_total']) && floatval($it['sub_diskon_nilai_total']) > 0 ? floatval($it['sub_diskon_nilai_total']) : ($d1_s + $d2_s + $d3_s + $d4_s + $d5_s + $d6_s + $d7_s + $fp_s);

                $netto = isset($it['sub_total']) ? floatval($it['sub_total']) : ($bruto - $tot_diskon_s);

                $grandQty += $qty;
                $grandBruto += $bruto;
                $grandNetto += $netto;
                $grandD1Sub += $d1_s;
                $grandD2Sub += $d2_s;
                $grandD3Sub += $d3_s;
                $grandD4Sub += $d4_s;
                $grandD5Sub += $d5_s;
                $grandD6Sub += $d6_s;
                $grandD7Sub += $d7_s;
                $grandFpSub += $fp_s;
                $grandDiskonTotalSub += $tot_diskon_s;

                $processedItems[] = array(
                    'nama' => $nama,
                    'qty' => $qty,
                    'harga' => $harga,
                    'bruto' => $bruto,
                    'netto' => $netto,
                    'd1_u' => $d1_u,
                    'd2_u' => $d2_u,
                    'd3_u' => $d3_u,
                    'd4_u' => $d4_u,
                    'd5_u' => $d5_u,
                    'd6_u' => $d6_u,
                    'd7_u' => $d7_u,
                    'fp_u' => $fp_u,
                    'tot_u' => $tot_diskon_u,
                    'd1_s' => $d1_s,
                    'd2_s' => $d2_s,
                    'd3_s' => $d3_s,
                    'd4_s' => $d4_s,
                    'd5_s' => $d5_s,
                    'd6_s' => $d6_s,
                    'd7_s' => $d7_s,
                    'fp_s' => $fp_s,
                    'tot_s' => $tot_diskon_s,
                );
            }
        }

        if ($totalPlafon <= 0 && $grandDiskonTotalSub > 0) {
            $totalPlafon = $grandDiskonTotalSub;
        }

        $html = "";
        $html .= "<div class='well well-sm' style='background:#f9f9f9; margin-bottom:15px; border-left:4px solid #3c8dbc;'>";
        $html .= "  <div class='row'>";
        $html .= "    <div class='col-md-6'>";
        $html .= "      <table class='table table-condensed' style='margin-bottom:0;'>";
        $html .= "        <tr><td style='width:120px;'><strong>Nomer GRN</strong></td><td>: <span class='badge bg-navy'>$nomerGrn</span></td></tr>";
        $html .= "        <tr><td><strong>Nomer PO</strong></td><td>: $nomerPo</td></tr>";
        $html .= "        <tr><td><strong>Supplier</strong></td><td>: <strong>$supplierNama</strong></td></tr>";
        $html .= "      </table>";
        $html .= "    </div>";
        $html .= "    <div class='col-md-6'>";
        $html .= "      <table class='table table-condensed' style='margin-bottom:0;'>";
        $html .= "        <tr><td style='width:130px;'><strong>Tanggal GRN</strong></td><td>: $tglGrn</td></tr>";
        $html .= "        <tr><td><strong>Petugas Input</strong></td><td>: $petugas</td></tr>";
        $html .= "        <tr><td><strong>Total Diskon Tertera</strong></td><td>: <strong class='text-primary' style='font-size:15px;'>Rp " . number_format($totalPlafon, 0, ',', '.') . "</strong></td></tr>";
        $html .= "      </table>";
        $html .= "    </div>";
        $html .= "  </div>";
        $html .= "</div>";

        // Summary Cards: 8 Jenis Diskon + Grand Total (Statis Penuh)
        $html .= "<div class='row' style='margin-bottom:6px;'>";
        $html .= "  <div class='col-md-3 col-sm-6 col-xs-6'>";
        $html .= "    <div style='background:#f8f9fb; border-top:3px solid #00c0ef; border-radius:3px; padding:6px 10px; margin-bottom:8px;'>";
        $html .= "      <div style='font-size:10px; color:#777; text-transform:uppercase; font-weight:bold;'>Total Diskon 1</div>";
        $html .= "      <div style='font-size:13px; font-weight:bold; color:#0073b7;'>Rp " . number_format($grandD1Sub, 0, ',', '.') . "</div>";
        $html .= "    </div>";
        $html .= "  </div>";
        $html .= "  <div class='col-md-3 col-sm-6 col-xs-6'>";
        $html .= "    <div style='background:#f8f9fb; border-top:3px solid #3c8dbc; border-radius:3px; padding:6px 10px; margin-bottom:8px;'>";
        $html .= "      <div style='font-size:10px; color:#777; text-transform:uppercase; font-weight:bold;'>Total Diskon 2</div>";
        $html .= "      <div style='font-size:13px; font-weight:bold; color:#222d32;'>Rp " . number_format($grandD2Sub, 0, ',', '.') . "</div>";
        $html .= "    </div>";
        $html .= "  </div>";
        $html .= "  <div class='col-md-3 col-sm-6 col-xs-6'>";
        $html .= "    <div style='background:#f8f9fb; border-top:3px solid #f39c12; border-radius:3px; padding:6px 10px; margin-bottom:8px;'>";
        $html .= "      <div style='font-size:10px; color:#777; text-transform:uppercase; font-weight:bold;'>Total Diskon 3</div>";
        $html .= "      <div style='font-size:13px; font-weight:bold; color:#d58512;'>Rp " . number_format($grandD3Sub, 0, ',', '.') . "</div>";
        $html .= "    </div>";
        $html .= "  </div>";
        $html .= "  <div class='col-md-3 col-sm-6 col-xs-6'>";
        $html .= "    <div style='background:#f8f9fb; border-top:3px solid #dd4b39; border-radius:3px; padding:6px 10px; margin-bottom:8px;'>";
        $html .= "      <div style='font-size:10px; color:#777; text-transform:uppercase; font-weight:bold;'>Total Diskon 4</div>";
        $html .= "      <div style='font-size:13px; font-weight:bold; color:#c9302c;'>Rp " . number_format($grandD4Sub, 0, ',', '.') . "</div>";
        $html .= "    </div>";
        $html .= "  </div>";
        $html .= "</div>";

        $html .= "<div class='row' style='margin-bottom:12px;'>";
        $html .= "  <div class='col-md-3 col-sm-6 col-xs-6'>";
        $html .= "    <div style='background:#f8f9fb; border-top:3px solid #605ca8; border-radius:3px; padding:6px 10px; margin-bottom:8px;'>";
        $html .= "      <div style='font-size:10px; color:#777; text-transform:uppercase; font-weight:bold;'>Total Diskon 5</div>";
        $html .= "      <div style='font-size:13px; font-weight:bold; color:#605ca8;'>Rp " . number_format($grandD5Sub, 0, ',', '.') . "</div>";
        $html .= "    </div>";
        $html .= "  </div>";
        $html .= "  <div class='col-md-3 col-sm-6 col-xs-6'>";
        $html .= "    <div style='background:#f8f9fb; border-top:3px solid #001f3f; border-radius:3px; padding:6px 10px; margin-bottom:8px;'>";
        $html .= "      <div style='font-size:10px; color:#777; text-transform:uppercase; font-weight:bold;'>Total Diskon 6</div>";
        $html .= "      <div style='font-size:13px; font-weight:bold; color:#001f3f;'>Rp " . number_format($grandD6Sub, 0, ',', '.') . "</div>";
        $html .= "    </div>";
        $html .= "  </div>";
        $html .= "  <div class='col-md-2 col-sm-4 col-xs-6'>";
        $html .= "    <div style='background:#f8f9fb; border-top:3px solid #39cccc; border-radius:3px; padding:6px 10px; margin-bottom:8px;'>";
        $html .= "      <div style='font-size:10px; color:#777; text-transform:uppercase; font-weight:bold;'>Total Diskon 7</div>";
        $html .= "      <div style='font-size:13px; font-weight:bold; color:#39cccc;'>Rp " . number_format($grandD7Sub, 0, ',', '.') . "</div>";
        $html .= "    </div>";
        $html .= "  </div>";
        $html .= "  <div class='col-md-2 col-sm-4 col-xs-6'>";
        $html .= "    <div style='background:#fdfaf2; border-top:3px solid #e08e0b; border-radius:3px; padding:6px 10px; margin-bottom:8px;'>";
        $html .= "      <div style='font-size:10px; color:#e08e0b; text-transform:uppercase; font-weight:bold;'>Total Free Produk</div>";
        $html .= "      <div style='font-size:13px; font-weight:bold; color:#d58512;'>Rp " . number_format($grandFpSub, 0, ',', '.') . "</div>";
        $html .= "    </div>";
        $html .= "  </div>";
        $html .= "  <div class='col-md-2 col-sm-4 col-xs-12'>";
        $html .= "    <div style='background:#edfbf1; border-top:3px solid #00a65a; border-radius:3px; padding:6px 10px; margin-bottom:8px;'>";
        $html .= "      <div style='font-size:10px; color:#008d4c; text-transform:uppercase; font-weight:bold;'>Total Seluruh Diskon</div>";
        $html .= "      <div style='font-size:13px; font-weight:bold; color:#00a65a;'>Rp " . number_format($grandDiskonTotalSub, 0, ',', '.') . "</div>";
        $html .= "    </div>";
        $html .= "  </div>";
        $html .= "</div>";

        $html .= "<div class='alert alert-info' style='padding:6px 12px; margin-bottom:12px; font-size:11px; border-left:3px solid #0097bc;'>";
        $html .= "  <i class='fa fa-info-circle'></i> <strong>Keterangan:</strong> Seluruh kolom <strong>Diskon 1 s/d 7 dan Free Produk</strong> ditampilkan statis penuh persis seperti tabel induk. Kolom <span class='badge bg-blue' style='font-size:10px;'>Diskon / Unit</span> menampilkan potongan untuk 1 unit barang. Kolom <span class='badge bg-green' style='font-size:10px;'>Total Diskon Baris</span> adalah hasil perkalian <code>Qty × Diskon Unit</code>.";
        $html .= "</div>";

        $html .= "<div class='table-responsive' style='overflow-x:auto;'>";
        $html .= "<table class='table table-bordered table-striped table-condensed' style='font-size:11px; white-space:nowrap;'>";
        $html .= "  <thead>";
        $html .= "    <tr class='bg-gray'>";
        $html .= "      <th rowspan='2' class='text-center' style='vertical-align:middle; width:25px;'>No.</th>";
        $html .= "      <th rowspan='2' style='vertical-align:middle; min-width:180px;'>Nama Produk / Barang</th>";
        $html .= "      <th rowspan='2' class='text-center' style='vertical-align:middle; width:45px;'>Qty</th>";
        $html .= "      <th rowspan='2' class='text-right' style='vertical-align:middle; width:90px;'>Harga / Unit</th>";
        $html .= "      <th rowspan='2' class='text-right' style='vertical-align:middle; width:100px;'>Subtotal Bruto</th>";
        $html .= "      <th colspan='9' class='text-center' style='background:#3c8dbc; color:#fff; border-bottom:1px solid #fff;'>Diskon Per 1 Unit Barang</th>";
        $html .= "      <th colspan='9' class='text-center' style='background:#00a65a; color:#fff; border-bottom:1px solid #fff;'>Total Diskon Baris (Qty × Unit)</th>";
        $html .= "      <th rowspan='2' class='text-right' style='vertical-align:middle; width:105px;'>Subtotal Netto</th>";
        $html .= "    </tr>";
        $html .= "    <tr>";
        $html .= "      <th class='text-right' style='background:#e8f1f8;'>Diskon 1</th>";
        $html .= "      <th class='text-right' style='background:#e8f1f8;'>Diskon 2</th>";
        $html .= "      <th class='text-right' style='background:#e8f1f8;'>Diskon 3</th>";
        $html .= "      <th class='text-right' style='background:#e8f1f8;'>Diskon 4</th>";
        $html .= "      <th class='text-right' style='background:#e8f1f8;'>Diskon 5</th>";
        $html .= "      <th class='text-right' style='background:#e8f1f8;'>Diskon 6</th>";
        $html .= "      <th class='text-right' style='background:#e8f1f8;'>Diskon 7</th>";
        $html .= "      <th class='text-right' style='background:#fff5e6;'>Free Produk</th>";
        $html .= "      <th class='text-right' style='background:#d7e9f7; font-weight:bold;'>Tot. / Unit</th>";
        $html .= "      <th class='text-right' style='background:#e8f8ee;'>Tot. Diskon 1</th>";
        $html .= "      <th class='text-right' style='background:#e8f8ee;'>Tot. Diskon 2</th>";
        $html .= "      <th class='text-right' style='background:#e8f8ee;'>Tot. Diskon 3</th>";
        $html .= "      <th class='text-right' style='background:#e8f8ee;'>Tot. Diskon 4</th>";
        $html .= "      <th class='text-right' style='background:#e8f8ee;'>Tot. Diskon 5</th>";
        $html .= "      <th class='text-right' style='background:#e8f8ee;'>Tot. Diskon 6</th>";
        $html .= "      <th class='text-right' style='background:#e8f8ee;'>Tot. Diskon 7</th>";
        $html .= "      <th class='text-right' style='background:#fff5e6;'>Tot. Free Prod</th>";
        $html .= "      <th class='text-right' style='background:#d2f4dd; font-weight:bold;'>Tot. Diskon</th>";
        $html .= "    </tr>";
        $html .= "  </thead>";
        $html .= "  <tbody>";

        if (sizeof($processedItems) > 0) {
            $itemNo = 0;
            foreach ($processedItems as $it) {
                $itemNo++;
                $html .= "    <tr>";
                $html .= "      <td class='text-center'>$itemNo</td>";
                $html .= "      <td><strong>" . htmlspecialchars($it['nama'], ENT_QUOTES) . "</strong></td>";
                $html .= "      <td class='text-center text-bold'>" . number_format($it['qty'], 0, ',', '.') . "</td>";
                $html .= "      <td class='text-right'>Rp " . number_format($it['harga'], 0, ',', '.') . "</td>";
                $html .= "      <td class='text-right text-muted'>Rp " . number_format($it['bruto'], 0, ',', '.') . "</td>";
                $html .= "      <td class='text-right'>" . ($it['d1_u'] > 0 ? "Rp " . number_format($it['d1_u'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right'>" . ($it['d2_u'] > 0 ? "Rp " . number_format($it['d2_u'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right'>" . ($it['d3_u'] > 0 ? "Rp " . number_format($it['d3_u'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right'>" . ($it['d4_u'] > 0 ? "Rp " . number_format($it['d4_u'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right'>" . ($it['d5_u'] > 0 ? "Rp " . number_format($it['d5_u'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right'>" . ($it['d6_u'] > 0 ? "Rp " . number_format($it['d6_u'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right'>" . ($it['d7_u'] > 0 ? "Rp " . number_format($it['d7_u'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right' style='background:#fdfaf2;'>" . ($it['fp_u'] > 0 ? "Rp " . number_format($it['fp_u'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right text-bold text-navy' style='background:#f4f9fd;'>Rp " . number_format($it['tot_u'], 0, ',', '.') . "</td>";
                $html .= "      <td class='text-right'>" . ($it['d1_s'] > 0 ? "Rp " . number_format($it['d1_s'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right'>" . ($it['d2_s'] > 0 ? "Rp " . number_format($it['d2_s'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right'>" . ($it['d3_s'] > 0 ? "Rp " . number_format($it['d3_s'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right'>" . ($it['d4_s'] > 0 ? "Rp " . number_format($it['d4_s'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right'>" . ($it['d5_s'] > 0 ? "Rp " . number_format($it['d5_s'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right'>" . ($it['d6_s'] > 0 ? "Rp " . number_format($it['d6_s'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right'>" . ($it['d7_s'] > 0 ? "Rp " . number_format($it['d7_s'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right' style='background:#fdfaf2;'>" . ($it['fp_s'] > 0 ? "Rp " . number_format($it['fp_s'], 0, ',', '.') : "-") . "</td>";
                $html .= "      <td class='text-right text-bold text-green' style='background:#f1fbf4;'>Rp " . number_format($it['tot_s'], 0, ',', '.') . "</td>";
                $html .= "      <td class='text-right font-weight-bold text-primary'>Rp " . number_format($it['netto'], 0, ',', '.') . "</td>";
                $html .= "    </tr>";
            }
        } else {
            $html .= "    <tr><td colspan='24' class='text-center text-muted'>Tidak ada rincian baris item.</td></tr>";
        }
        $html .= "  </tbody>";
        $html .= "  <tfoot class='bg-gray'>";
        $html .= "    <tr style='font-weight:bold;'>";
        $html .= "      <td colspan='2' class='text-center'>TOTAL AKUMULASI FAKTUR</td>";
        $html .= "      <td class='text-center'>" . number_format($grandQty, 0, ',', '.') . "</td>";
        $html .= "      <td class='text-right'>-</td>";
        $html .= "      <td class='text-right'>Rp " . number_format($grandBruto, 0, ',', '.') . "</td>";
        $html .= "      <td colspan='7' class='text-center text-muted'>-</td>";
        $html .= "      <td class='text-center text-muted'>-</td>";
        $html .= "      <td class='text-right text-navy'>-</td>";
        $html .= "      <td class='text-right'>Rp " . number_format($grandD1Sub, 0, ',', '.') . "</td>";
        $html .= "      <td class='text-right'>Rp " . number_format($grandD2Sub, 0, ',', '.') . "</td>";
        $html .= "      <td class='text-right'>Rp " . number_format($grandD3Sub, 0, ',', '.') . "</td>";
        $html .= "      <td class='text-right'>Rp " . number_format($grandD4Sub, 0, ',', '.') . "</td>";
        $html .= "      <td class='text-right'>Rp " . number_format($grandD5Sub, 0, ',', '.') . "</td>";
        $html .= "      <td class='text-right'>Rp " . number_format($grandD6Sub, 0, ',', '.') . "</td>";
        $html .= "      <td class='text-right'>Rp " . number_format($grandD7Sub, 0, ',', '.') . "</td>";
        $html .= "      <td class='text-right' style='background:#fdfaf2;'>Rp " . number_format($grandFpSub, 0, ',', '.') . "</td>";
        $html .= "      <td class='text-right text-green'>Rp " . number_format($grandDiskonTotalSub, 0, ',', '.') . "</td>";
        $html .= "      <td class='text-right text-primary'>Rp " . number_format($grandNetto, 0, ',', '.') . "</td>";
        $html .= "    </tr>";
        $html .= "  </tfoot>";
        $html .= "</table>";
        $html .= "</div>";

        echo $html;
        exit;
    }

    public function previewMutasiKlaimAjax($param1 = 0, $param2 = 0)
    {
        $grnId = ($param2 > 0) ? intval($param2) : intval($param1);
        if ($grnId <= 0) {
            echo "<div class='alert alert-danger'>ID GRN tidak valid.</div>";
            exit;
        }

        $sql = "SELECT 
                    m.id as mutasi_id,
                    m.dtime as tgl_mutasi,
                    m.kredit as nominal_klaim,
                    m.extern2_nama as jenis_diskon,
                    m.keterangan as ket_mutasi,
                    t.id as klaim_id,
                    t.nomer as klaim_nomer,
                    t.oleh_nama as klaim_oleh,
                    t.dtime as klaim_dtime
                FROM __rek_pembantu_subpiutangsuppliertrans__1010020030 m
                LEFT JOIN transaksi t ON t.id = m.transaksi_id
                WHERE m.extern_id = ? AND m.kredit > 0
                ORDER BY m.dtime ASC";
        $q = $this->db->query($sql, array($grnId));
        $res = $q->result();

        $html = "";
        $html .= "<div class='alert alert-info' style='margin-bottom:15px;'>";
        $html .= "  <i class='fa fa-info-circle'></i> Riwayat transaksi klaim pemotong piutang diskon untuk nota GRN ID <strong>#$grnId</strong>.";
        $html .= "</div>";

        $html .= "<div class='table-responsive'>";
        $html .= "<table class='table table-bordered table-striped table-condensed' style='font-size:12px;'>";
        $html .= "  <thead class='bg-navy text-white'>";
        $html .= "    <tr>";
        $html .= "      <th style='width:35px;' class='text-center'>No.</th>";
        $html .= "      <th>Nomer Transaksi Klaim</th>";
        $html .= "      <th>Tanggal & Waktu Klaim</th>";
        $html .= "      <th>Alokasi Diskon</th>";
        $html .= "      <th class='text-right'>Nominal Dicairkan</th>";
        $html .= "      <th>Petugas Pemroses</th>";
        $html .= "      <th>Keterangan</th>";
        $html .= "    </tr>";
        $html .= "  </thead>";
        $html .= "  <tbody>";

        if (sizeof($res) > 0) {
            $mNo = 0;
            $totKredit = 0;
            foreach ($res as $m) {
                $mNo++;
                $totKredit += floatval($m->nominal_klaim);
                $nomKlaim = !empty($m->klaim_nomer) ? $m->klaim_nomer : ("TRX #" . $m->klaim_id);
                $tgl = !empty($m->tgl_mutasi) ? $m->tgl_mutasi : $m->klaim_dtime;
                $petugas = !empty($m->klaim_oleh) ? $m->klaim_oleh : "-";
                $diskonNama = !empty($m->jenis_diskon) ? $m->jenis_diskon : "Diskon Reguler";
                $ket = !empty($m->ket_mutasi) ? $m->ket_mutasi : "-";

                $html .= "    <tr>";
                $html .= "      <td class='text-center'>$mNo</td>";
                $html .= "      <td><strong class='text-primary'><i class='fa fa-file-text-o'></i> $nomKlaim</strong></td>";
                $html .= "      <td>$tgl</td>";
                $html .= "      <td><span class='label label-default'>$diskonNama</span></td>";
                $html .= "      <td class='text-right text-bold text-red'>Rp " . number_format($m->nominal_klaim, 0, ',', '.') . "</td>";
                $html .= "      <td>$petugas</td>";
                $html .= "      <td>$ket</td>";
                $html .= "    </tr>";
            }
            $html .= "  </tbody>";
            $html .= "  <tfoot class='bg-gray'>";
            $html .= "    <tr style='font-weight:bold;'>";
            $html .= "      <td colspan='4' class='text-center'>TOTAL KLAIM SELESAI</td>";
            $html .= "      <td class='text-right text-red'>Rp " . number_format($totKredit, 0, ',', '.') . "</td>";
            $html .= "      <td colspan='2'></td>";
            $html .= "    </tr>";
            $html .= "  </tfoot>";
        } else {
            $html .= "    <tr><td colspan='7' class='text-center text-muted'>Belum ada mutasi klaim pemotong pada nota ini (Diskon masih utuh).</td></tr>";
            $html .= "  </tbody>";
        }
        $html .= "</table>";
        $html .= "</div>";

        echo $html;
        exit;
    }

    // START OF COMPLETE REPEATED LOGIC
    public function traceSelisih()
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        // 1. Ambil seluruh nota GRN aktif yang tercatat memiliki saldo piutang di rekening pembantu cache (252 Nota)
        $sqlCache = "SELECT 
            c.extern_id as grn_id,
            t.nomer as grn_nomer,
            t.fulldate as grn_fulldate,
            t.dtime as grn_dtime,
            t.suppliers_id,
            COALESCE(NULLIF(t.suppliers_nama, ''), CONCAT('Supplier #', t.suppliers_id)) as suppliers_nama,
            SUM(c.debet) as cache_debet
        FROM _rek_pembantu_subpiutangsuppliertrans_cache c
        INNER JOIN transaksi t ON t.id = c.extern_id
        WHERE c.periode = 'forever' 
          AND c.rekening = '1010020030'
          AND t.jenis IN ('467')
          AND t.status = '1'
          AND t.trash = '0'
          AND t.link_id = '0'
          AND (t.trash_4 = '0' OR t.trash_4 IS NULL)
        GROUP BY c.extern_id, t.nomer, t.fulldate, t.dtime, t.suppliers_id, t.suppliers_nama
        HAVING cache_debet >= 1
        ORDER BY t.suppliers_nama ASC, c.extern_id ASC";

        $qCache = $this->db->query($sqlCache);
        $resCache = $qCache ? $qCache->result_array() : array();

        if (empty($resCache)) {
            echo "Tidak ditemukan data rekening cache aktif.";
            exit;
        }

        $allGrnIds = array();
        $allGrnNomers = array();
        $cacheMap = array();
        foreach ($resCache as $row) {
            $gId = intval($row['grn_id']);
            $allGrnIds[] = $gId;
            $allGrnNomers[] = $this->db->escape($row['grn_nomer']);
            $cacheMap[$gId] = $row;
        }

        // 2. Ambil data locker untuk seluruh GRN tersebut
        $lockerMap = array();
        $lockerDetails = array();
        if (sizeof($allGrnIds) > 0) {
            $idListStr = implode(",", $allGrnIds);
            $sqlLocker = "SELECT 
                transaksi_id as grn_id,
                nomer as grn_nomer,
                produk_id,
                nilai,
                nilai_diklaim,
                nilai_unit,
                jumlah,
                state
            FROM stock_locker_diskon
            WHERE jenis_locker = 'stock' AND jenis = 'diskon'
              AND (transaksi_id IN ($idListStr) OR nomer IN (" . implode(",", $allGrnNomers) . "))";
            $qLocker = $this->db->query($sqlLocker);
            if ($qLocker) {
                foreach ($qLocker->result_array() as $lRow) {
                    $gId = intval($lRow['grn_id']);
                    if (!isset($lockerMap[$gId])) {
                        $lockerMap[$gId] = array(
                            "sisa" => 0,
                            "diklaim" => 0,
                            "rows" => 0,
                            "has_active" => false
                        );
                    }
                    $nSisa = floatval($lRow['nilai']);
                    $nKlaim = floatval($lRow['nilai_diklaim']);
                    $lockerMap[$gId]['sisa'] += $nSisa;
                    $lockerMap[$gId]['diklaim'] += $nKlaim;
                    $lockerMap[$gId]['rows']++;
                    if ($nSisa >= 1) {
                        $lockerMap[$gId]['has_active'] = true;
                    }
                    $lockerDetails[$gId][] = $lRow;
                }
            }
        }

        // 3. Filter 78 nota selisih: Ada di rekening cache (debet >= 1), tetapi TIDAK ADA di locker aktif (has_active == false atau nilai < 1)
        $selisihGrns = array();
        $selisihGrnIds = array();
        $selisihGrnNomers = array();
        foreach ($cacheMap as $gId => $gData) {
            $lData = isset($lockerMap[$gId]) ? $lockerMap[$gId] : null;
            $isLockerActive = ($lData && $lData['has_active'] && $lData['sisa'] >= 1);
            if (!$isLockerActive) {
                $selisihGrns[$gId] = $gData;
                $selisihGrnIds[] = $gId;
                $selisihGrnNomers[] = $this->db->escape($gData['grn_nomer']);
            }
        }

        $totalSelisih = count($selisihGrns);

        // 4. Untuk seluruh nota selisih, cek histori transaksi klaim (jenis 3333/3344) dan retur (9911)
        $claimsMap = array();
        $returMap = array();
        $mutasiMap = array();

        if ($totalSelisih > 0) {
            $sIdListStr = implode(",", $selisihGrnIds);
            $sNomListStr = implode(",", $selisihGrnNomers);

            // Cek dokumen klaim 3333
            $sqlClaims = "SELECT 
                id, nomer, fulldate, dtime, reference_id, reference_nomer, transaksi_nilai, oleh_nama, status, trash
            FROM transaksi 
            WHERE jenis IN ('3333', '3344')
              AND (reference_id IN ($sIdListStr) OR reference_nomer IN ($sNomListStr))";
            $qClaims = $this->db->query($sqlClaims);
            if ($qClaims) {
                foreach ($qClaims->result_array() as $cRow) {
                    $refId = intval($cRow['reference_id']);
                    $refNom = $cRow['reference_nomer'];
                    foreach ($selisihGrns as $gId => $gSpec) {
                        if ($gId == $refId || $gSpec['grn_nomer'] == $refNom) {
                            $claimsMap[$gId][] = $cRow;
                        }
                    }
                }
            }

            // Cek dokumen retur 9911
            $sqlRetur = "SELECT 
                id, nomer, fulldate, dtime, reference_id, reference_nomer, transaksi_nilai, oleh_nama, status, trash
            FROM transaksi 
            WHERE jenis LIKE '9911%'
              AND (reference_id IN ($sIdListStr) OR reference_nomer IN ($sNomListStr))";
            $qRetur = $this->db->query($sqlRetur);
            if ($qRetur) {
                foreach ($qRetur->result_array() as $rRow) {
                    $refId = intval($rRow['reference_id']);
                    $refNom = $rRow['reference_nomer'];
                    foreach ($selisihGrns as $gId => $gSpec) {
                        if ($gId == $refId || $gSpec['grn_nomer'] == $refNom) {
                            $returMap[$gId][] = $rRow;
                        }
                    }
                }
            }

            // Cek mutasi buku pembantu partisi __rek_pembantu_subpiutangsuppliertrans__1010020030
            $tblMutasi = "__rek_pembantu_subpiutangsuppliertrans__1010020030";
            if ($this->db->table_exists($tblMutasi)) {
                $sqlMut = "SELECT 
                    extern_id as grn_id,
                    SUM(debet) as debet,
                    SUM(kredit) as kredit,
                    COUNT(*) as jml_mutasi
                FROM $tblMutasi
                WHERE extern_id IN ($sIdListStr)
                GROUP BY extern_id";
                $qMut = $this->db->query($sqlMut);
                if ($qMut) {
                    foreach ($qMut->result_array() as $mRow) {
                        $mutasiMap[intval($mRow['grn_id'])] = array(
                            "debet" => floatval($mRow['debet']),
                            "kredit" => floatval($mRow['kredit']),
                            "jml_mutasi" => intval($mRow['jml_mutasi'])
                        );
                    }
                }
            }
        }

        // 5. Klasifikasi tiap nota
        $diagnosaList = array();
        $cntKlaimTanpaKredit = 0;
        $cntLockerZero = 0;
        $cntRetur = 0;
        $cntLainnya = 0;
        $cntSharp = 0;

        $totNilaiKlaimTanpaKredit = 0;
        $totNilaiLockerZero = 0;
        $totNilaiRetur = 0;
        $totNilaiSelisih = 0;
        $totNilaiSharp = 0;
        $totLockerSisaSelisih = 0;
        $totLockerDiklaimSelisih = 0;
        $totMutasiKreditSelisih = 0;

        foreach ($selisihGrns as $gId => $gData) {
            $lData = isset($lockerMap[$gId]) ? $lockerMap[$gId] : null;
            $cDocs = isset($claimsMap[$gId]) ? $claimsMap[$gId] : array();
            $rDocs = isset($returMap[$gId]) ? $returMap[$gId] : array();
            $mRow = isset($mutasiMap[$gId]) ? $mutasiMap[$gId] : array("debet" => 0, "kredit" => 0);

            $isSharp = (stripos($gData['suppliers_nama'], 'SHARP') !== false);
            $cacheDebet = floatval($gData['cache_debet']);
            $totNilaiSelisih += $cacheDebet;

            if ($isSharp) {
                $cntSharp++;
                $totNilaiSharp += $cacheDebet;
            }

            $mutasiKredit = floatval($mRow['kredit']);
            $totMutasiKreditSelisih += $mutasiKredit;
            $lockerSisa = $lData ? floatval($lData['sisa']) : 0;
            $totLockerSisaSelisih += $lockerSisa;
            $lockerDiklaim = $lData ? floatval($lData['diklaim']) : 0;
            $totLockerDiklaimSelisih += $lockerDiklaim;
            $lockerRows = $lData ? intval($lData['rows']) : 0;

            $kategoriKey = "";
            $kategoriLabel = "";
            $diagnosaText = "";
            $badgeColor = "";

            if (sizeof($cDocs) > 0 || $lockerDiklaim > 0) {
                $kategoriKey = "klaim_tanpa_kredit";
                $kategoriLabel = "Sudah Diklaim (Rekening Belum Ter-kredit)";
                $badgeColor = "label-danger";
                $cntKlaimTanpaKredit++;
                $totNilaiKlaimTanpaKredit += $cacheDebet;

                $cDocNomers = array();
                foreach ($cDocs as $cd) {
                    $cDocNomers[] = $cd['nomer'] . " (Rp " . number_format($cd['transaksi_nilai'], 0, ',', '.') . ")";
                }
                $diagnosaText = "Dokumen klaim terbit: " . (!empty($cDocNomers) ? implode(", ", $cDocNomers) : ("Locker diklaim Rp " . number_format($lockerDiklaim, 0, ',', '.'))) . ". Saldo locker telah terpotong, tetapi mutasi kredit akuntansi 1010020030 tercatat Rp " . number_format($mutasiKredit, 0, ',', '.') . " sehingga piutang di rekening cache masih menggantung aktif.";
            } elseif (sizeof($rDocs) > 0) {
                $kategoriKey = "retur";
                $kategoriLabel = "Terkait Retur / Pembatalan (9911)";
                $badgeColor = "label-warning";
                $cntRetur++;
                $totNilaiRetur += $cacheDebet;

                $rDocNomers = array();
                foreach ($rDocs as $rd) {
                    $rDocNomers[] = $rd['nomer'];
                }
                $diagnosaText = "Terdapat dokumen retur: " . implode(", ", $rDocNomers) . ". Piutang akuntansi menggantung atau locker dinolkan via retur.";
            } elseif ($lockerRows > 0 && $lockerSisa == 0 && $lockerDiklaim == 0) {
                $kategoriKey = "locker_zero";
                $kategoriLabel = "Locker Kosong / Ter-wipe Rp 0 sejak GRN";
                $badgeColor = "label-warning";
                $cntLockerZero++;
                $totNilaiLockerZero += $cacheDebet;
                $diagnosaText = "Locker terdaftar ($lockerRows baris) namun bernilai Rp 0,00 di kolom nilai. Tidak pernah ada transaksi klaim 3333. Hak diskon ter-wipe saat penyimpanan GRN.";
            } elseif ($lockerRows == 0) {
                $kategoriKey = "locker_zero";
                $kategoriLabel = "Locker Belum Terbit (0 Baris)";
                $badgeColor = "label-warning";
                $cntLockerZero++;
                $totNilaiLockerZero += $cacheDebet;
                $diagnosaText = "Tabel stock_locker_diskon tidak memiliki baris untuk GRN ini sama sekali, namun rekening 1010020030 mendebet Rp " . number_format($cacheDebet, 0, ',', '.') . ".";
            } else {
                $kategoriKey = "lainnya";
                $kategoriLabel = "Diskrepansi Lainnya";
                $badgeColor = "label-default";
                $cntLainnya++;
                $diagnosaText = "Locker sisa Rp " . number_format($lockerSisa, 0, ',', '.') . " vs Rekening Rp " . number_format($cacheDebet, 0, ',', '.') . ".";
            }

            $diagnosaList[] = array(
                "grn_id" => $gId,
                "grn_nomer" => $gData['grn_nomer'],
                "grn_fulldate" => $gData['grn_fulldate'],
                "suppliers_id" => $gData['suppliers_id'],
                "suppliers_nama" => $gData['suppliers_nama'],
                "cache_debet" => $cacheDebet,
                "mutasi_kredit" => $mutasiKredit,
                "locker_sisa" => $lockerSisa,
                "locker_diklaim" => $lockerDiklaim,
                "locker_rows" => $lockerRows,
                "claims_docs" => $cDocs,
                "retur_docs" => $rDocs,
                "kategori_key" => $kategoriKey,
                "kategori_label" => $kategoriLabel,
                "badge_color" => $badgeColor,
                "diagnosa_text" => $diagnosaText,
                "is_sharp" => $isSharp
            );
        }

        // Render HTML Output
        $baseUrl = base_url();
        $linkBack = MODUL_PATH . "Transaksi/index/3333";

        echo "<!DOCTYPE html>";
        echo "<html lang='id'>";
        echo "<head>";
        echo "<meta charset='UTF-8'>";
        echo "<title>Investigasi Forensik 78 Nota Selisih (Locker vs Rekening COA 1010020030)</title>";
        echo "<link rel='stylesheet' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css'>";
        echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>";
        echo "<style>";
        echo "body { background: #f4f6f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; font-size: 12.5px; color: #2d3748; padding: 20px; }";
        echo ".kpi-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px 18px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); margin-bottom: 20px; min-height: 110px; }";
        echo ".kpi-title { font-size: 11px; text-transform: uppercase; font-weight: 700; color: #64748b; letter-spacing: 0.5px; }";
        echo ".kpi-value { font-size: 21px; font-weight: 800; margin: 4px 0; font-family: Consolas, monospace; }";
        echo ".kpi-desc { font-size: 11.5px; color: #64748b; }";
        echo ".filter-pill { display: inline-block; padding: 6px 14px; margin-right: 6px; margin-bottom: 8px; border-radius: 20px; font-weight: 700; font-size: 11.5px; cursor: pointer; border: 1px solid #cbd5e1; background: #fff; color: #475569; transition: all 0.15s; }";
        echo ".filter-pill.active, .filter-pill:hover { background: #2563eb; color: #fff; border-color: #2563eb; }";
        echo ".table-audit th { background: #0f172a; color: #f8fafc; font-size: 11px; text-transform: uppercase; padding: 10px 8px; border: none; }";
        echo ".table-audit td { padding: 9px 8px; vertical-align: middle; border-bottom: 1px solid #e2e8f0; }";
        echo ".table-audit tr:hover td { background: #f8fafc; }";
        echo ".table-audit tfoot td { border-top: 2px solid #0f172a; font-weight: bold; }";
        echo ".font-mono { font-family: Consolas, monospace; }";
        echo "</style>";
        echo "</head>";
        echo "<body>";

        echo "<div class='container-fluid' style='max-width: 1440px; margin: 0 auto;'>";

        // Topbar
        echo "<div style='display:flex; justify-content:space-between; align-items:center; background:#0f172a; color:#fff; padding:16px 20px; border-radius:8px; margin-bottom:20px; box-shadow:0 2px 5px rgba(0,0,0,0.1);'>";
        echo "  <div>";
        echo "    <h3 style='margin:0; font-size:18px; font-weight:800;'><i class='fa fa-search text-warning'></i> INVESTIGASI FORENSIK 78 NOTA SELISIH</h3>";
        echo "    <div style='font-size:12px; color:#94a3b8; margin-top:3px;'>Pencocokan 3-Arah: Rekening Pembantu 1010020030 &harr; stock_locker_diskon &harr; Transaksi Klaim 3333</div>";
        echo "  </div>";
        echo "  <div>";
        echo "    <a href='$linkBack' class='btn btn-danger btn-sm' style='font-weight:bold;'><i class='fa fa-arrow-left'></i> Kembali ke Antrean Operasional</a>";
        echo "  </div>";
        echo "</div>";

        // Summary KPI Grid (5 Kolom dengan Nilai Akumulasi Besar)
        echo "<div class='row'>";
        echo "  <div class='col-md-3'>";
        echo "    <div class='kpi-box' style='border-top:4px solid #ef4444; background:#fffafa;'>";
        echo "      <div class='kpi-title text-danger'><i class='fa fa-exclamation-triangle'></i> Total Saldo Tergantung / Selisih</div>";
        echo "      <div class='kpi-value text-danger'>Rp " . number_format($totNilaiSelisih, 0, ',', '.') . "</div>";
        echo "      <div class='kpi-desc'>Akumulasi dari <strong>$totalSelisih Nota</strong> menggantung</div>";
        echo "    </div>";
        echo "  </div>";
        echo "  <div class='col-md-3'>";
        echo "    <div class='kpi-box' style='border-top:4px solid #b91c1c;'>";
        echo "      <div class='kpi-title'>1. Sudah Diklaim (Rekening Gantung)</div>";
        echo "      <div class='kpi-value text-danger'>Rp " . number_format($totNilaiKlaimTanpaKredit, 0, ',', '.') . "</div>";
        echo "      <div class='kpi-desc'><strong>$cntKlaimTanpaKredit Nota</strong> (Locker 0 tapi Jurnal Kredit 0)</div>";
        echo "    </div>";
        echo "  </div>";
        echo "  <div class='col-md-3'>";
        echo "    <div class='kpi-box' style='border-top:4px solid #3b82f6; background:#f0f7ff;'>";
        echo "      <div class='kpi-title text-primary'>2. Khusus PT. Sharp Electronics</div>";
        echo "      <div class='kpi-value text-primary'>Rp " . number_format($totNilaiSharp, 0, ',', '.') . "</div>";
        echo "      <div class='kpi-desc'><strong>$cntSharp Nota</strong> dari total 48 nota Sharp</div>";
        echo "    </div>";
        echo "  </div>";
        echo "  <div class='col-md-3'>";
        echo "    <div class='kpi-box' style='border-top:4px solid #f59e0b;'>";
        echo "      <div class='kpi-title'>3. Locker Ter-wipe Rp 0 / Retur</div>";
        echo "      <div class='kpi-value text-warning'>Rp " . number_format($totNilaiLockerZero + $totNilaiRetur, 0, ',', '.') . "</div>";
        echo "      <div class='kpi-desc'><strong>" . ($cntLockerZero + $cntRetur) . " Nota</strong> (Terbit 0 atau terkait retur)</div>";
        echo "    </div>";
        echo "  </div>";
        echo "</div>";

        // Filter Controls
        echo "<div style='background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:12px 18px; margin-bottom:15px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;'>";
        echo "  <div>";
        echo "    <span style='font-size:11px; font-weight:bold; color:#64748b; text-transform:uppercase; margin-right:6px;'><i class='fa fa-filter'></i> Filter Tampilan:</span>";
        echo "    <span class='filter-pill active' data-filter='all'>Semua Selisih ($totalSelisih)</span>";
        echo "    <span class='filter-pill' data-filter='sharp'>Khusus PT. Sharp ($cntSharp)</span>";
        echo "    <span class='filter-pill' data-filter='klaim_tanpa_kredit'>Sudah Diklaim tapi Rekening Gantung ($cntKlaimTanpaKredit)</span>";
        echo "    <span class='filter-pill' data-filter='locker_zero'>Locker Ter-wipe Rp 0 ($cntLockerZero)</span>";
        echo "    <span class='filter-pill' data-filter='retur'>Terkait Retur ($cntRetur)</span>";
        echo "  </div>";
        echo "  <div>";
        echo "    <input type='text' id='input_search_audit' class='form-control input-sm' placeholder='Cari No. GRN / Vendor...' style='width:240px; display:inline-block;'>";
        echo "  </div>";
        echo "</div>";

        // Detail Table
        echo "<div class='table-responsive' style='background:#fff; border:1px solid #e2e8f0; border-radius:8px; overflow:hidden;'>";
        echo "  <table class='table table-bordered table-audit' id='table_trace_selisih' style='margin-bottom:0;'>";
        echo "    <thead>";
        echo "      <tr>";
        echo "        <th style='width:35px;' class='text-center'>No.</th>";
        echo "        <th style='width:115px;'>No. GRN</th>";
        echo "        <th style='width:85px;' class='text-center'>Tgl GRN</th>";
        echo "        <th>Nama Mitra Supplier</th>";
        echo "        <th style='width:130px;' class='text-right'>Saldo Rekening</th>";
        echo "        <th style='width:100px;' class='text-center'>Sisa Locker</th>";
        echo "        <th style='width:100px;' class='text-center'>Klaim Locker</th>";
        echo "        <th style='width:100px;' class='text-center'>Kredit Jurnal</th>";
        echo "        <th style='width:220px;'>Dokumen Klaim (3333)</th>";
        echo "        <th style='width:200px;'>Akar Kausalitas &amp; Diagnosa</th>";
        echo "      </tr>";
        echo "    </thead>";
        echo "    <tbody>";

        $no = 0;
        foreach ($diagnosaList as $item) {
            $no++;
            $sharpClass = $item['is_sharp'] ? "is-sharp" : "";
            $katClass = "kat-" . $item['kategori_key'];

            $claimDocStr = "-";
            if (!empty($item['claims_docs'])) {
                $cList = array();
                foreach ($item['claims_docs'] as $cd) {
                    $cList[] = "<strong class='text-danger'>" . htmlspecialchars($cd['nomer']) . "</strong><br><small class='text-muted'>Rp " . number_format($cd['transaksi_nilai'], 0, ',', '.') . " (" . $cd['fulldate'] . " by " . $cd['oleh_nama'] . ")</small>";
                }
                $claimDocStr = implode("<br>", $cList);
            }

            echo "    <tr class='row-trace $sharpClass $katClass' data-debet='" . $item['cache_debet'] . "' data-sisa='" . $item['locker_sisa'] . "' data-klaim='" . $item['locker_diklaim'] . "' data-kredit='" . $item['mutasi_kredit'] . "'>";
            echo "      <td class='text-center text-muted'>$no</td>";
            echo "      <td><strong class='text-primary'>" . htmlspecialchars($item['grn_nomer']) . "</strong><br><small class='text-muted'>#" . $item['grn_id'] . "</small></td>";
            echo "      <td class='text-center font-mono'>" . $item['grn_fulldate'] . "</td>";
            echo "      <td><strong>" . htmlspecialchars($item['suppliers_nama']) . "</strong></td>";
            echo "      <td class='text-right font-mono text-danger font-bold'>Rp " . number_format($item['cache_debet'], 0, ',', '.') . "</td>";
            echo "      <td class='text-center font-mono'>" . ($item['locker_sisa'] > 0 ? "Rp " . number_format($item['locker_sisa'], 0, ',', '.') : "<span class='label label-default'>Rp 0</span>") . "</td>";
            echo "      <td class='text-center font-mono'>" . ($item['locker_diklaim'] > 0 ? "<span class='text-success font-bold'>Rp " . number_format($item['locker_diklaim'], 0, ',', '.') . "</span>" : "-") . "</td>";
            echo "      <td class='text-center font-mono'>" . ($item['mutasi_kredit'] > 0 ? "<span class='text-success font-bold'>Rp " . number_format($item['mutasi_kredit'], 0, ',', '.') . "</span>" : "<span class='label label-danger'>Rp 0</span>") . "</td>";
            echo "      <td>$claimDocStr</td>";
            echo "      <td>";
            echo "        <span class='label " . $item['badge_color'] . "' style='font-size:10.5px;'>" . $item['kategori_label'] . "</span>";
            echo "        <div style='font-size:11px; color:#64748b; margin-top:4px; line-height:1.35;'>" . $item['diagnosa_text'] . "</div>";
            echo "      </td>";
            echo "    </tr>";
        }

        echo "    </tbody>";

        // FOOTER REKAPITULASI TOTAL AKUMULASI NOMINAL
        echo "    <tfoot style='background:#0f172a; color:#fff;'>";
        echo "      <tr>";
        echo "        <td colspan='4' class='text-center' style='padding:12px 10px; font-size:12px; letter-spacing:0.5px;'>";
        echo "          <i class='fa fa-calculator text-warning'></i> <strong>TOTAL AKUMULASI SELISIH / TERGANTUNG (<span id='foot_count_nota'>$totalSelisih</span> NOTA)</strong>";
        echo "        </td>";
        echo "        <td class='text-right font-mono' style='color:#fca5a5; font-size:13px; font-weight:800;' id='foot_total_rekening'>";
        echo "          Rp " . number_format($totNilaiSelisih, 0, ',', '.');
        echo "        </td>";
        echo "        <td class='text-center font-mono' id='foot_total_sisa_locker'>";
        echo "          Rp " . number_format($totLockerSisaSelisih, 0, ',', '.');
        echo "        </td>";
        echo "        <td class='text-center font-mono' style='color:#86efac;' id='foot_total_klaim_locker'>";
        echo "          Rp " . number_format($totLockerDiklaimSelisih, 0, ',', '.');
        echo "        </td>";
        echo "        <td class='text-center font-mono' style='color:#86efac;' id='foot_total_mutasi_kredit'>";
        echo "          Rp " . number_format($totMutasiKreditSelisih, 0, ',', '.');
        echo "        </td>";
        echo "        <td colspan='2' class='text-center' style='font-size:11px; color:#94a3b8; font-weight:normal;'>";
        echo "          <i class='fa fa-info-circle'></i> Nilai total menyesuaikan filter aktif secara otomatis";
        echo "        </td>";
        echo "      </tr>";
        echo "    </tfoot>";

        echo "  </table>";
        echo "</div>";

        echo "</div>"; // container

        // Interactive Script dengan Auto-Recalculate Akumulasi
        echo "<script src='https://code.jquery.com/jquery-2.2.4.min.js'></script>";
        echo "<script>";
        echo "  function formatRupiah(num) {";
        echo "    var nStr = Math.round(num) + '';";
        echo "    var rgx = /(\\d+)(\\d{3})/;";
        echo "    while (rgx.test(nStr)) {";
        echo "      nStr = nStr.replace(rgx, '$1' + '.' + '$2');";
        echo "    }";
        echo "    return 'Rp ' + nStr;";
        echo "  }";
        echo "  function recalculateTotals() {";
        echo "    var totDebet = 0;";
        echo "    var totSisa = 0;";
        echo "    var totKlaim = 0;";
        echo "    var totKredit = 0;";
        echo "    var count = 0;";
        echo "    $('.row-trace:visible').each(function() {";
        echo "      count++;";
        echo "      totDebet += parseFloat($(this).attr('data-debet')) || 0;";
        echo "      totSisa += parseFloat($(this).attr('data-sisa')) || 0;";
        echo "      totKlaim += parseFloat($(this).attr('data-klaim')) || 0;";
        echo "      totKredit += parseFloat($(this).attr('data-kredit')) || 0;";
        echo "    });";
        echo "    $('#foot_count_nota').text(count);";
        echo "    $('#foot_total_rekening').text(formatRupiah(totDebet));";
        echo "    $('#foot_total_sisa_locker').text(formatRupiah(totSisa));";
        echo "    $('#foot_total_klaim_locker').text(formatRupiah(totKlaim));";
        echo "    $('#foot_total_mutasi_kredit').text(formatRupiah(totKredit));";
        echo "  }";
        echo "  $('.filter-pill').on('click', function() {";
        echo "    $('.filter-pill').removeClass('active');";
        echo "    $(this).addClass('active');";
        echo "    var f = $(this).data('filter');";
        echo "    if (f === 'all') {";
        echo "      $('.row-trace').show();";
        echo "    } else if (f === 'sharp') {";
        echo "      $('.row-trace').hide();";
        echo "      $('.row-trace.is-sharp').show();";
        echo "    } else {";
        echo "      $('.row-trace').hide();";
        echo "      $('.row-trace.kat-' + f).show();";
        echo "    }";
        echo "    recalculateTotals();";
        echo "  });";
        echo "  $('#input_search_audit').on('keyup', function() {";
        echo "    var val = $(this).val().toLowerCase();";
        echo "    $('.row-trace').each(function() {";
        echo "      var text = $(this).text().toLowerCase();";
        echo "      $(this).toggle(text.indexOf(val) > -1);";
        echo "    });";
        echo "    recalculateTotals();";
        echo "  });";
        echo "</script>";

        echo "</body></html>";
        exit;
    }
    // END OF COMPLETE REPEATED LOGIC
}
// END OF COMPLETE REPEATED LOGIC

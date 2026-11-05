<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// START OF COMPLETE REPEATED LOGIC
class TracerStokInvoice extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!isset($this->session->login['id'])) {
            gotoLogin();
        }
        $this->load->model("MdlTransaksi");
    }

    public function index()
    {
        $keyword = isset($_GET['q']) ? trim($_GET['q']) : "";
        $targetId = isset($_GET['id']) && is_numeric($_GET['id']) ? intval($_GET['id']) : 0;
        $isExactParam = isset($_GET['exact']) && $_GET['exact'] == 1;

        $dataNota = null;
        $items = array();
        $matches = array();
        $errMsg = "";
        $searchMode = ""; // 'direct', 'exact', 'prefix', 'similarity', 'customer'

        $selectFields = "id, nomer, nomer2, jenis, fulldate, cabang_id, gudang_id, customers_nama, customers_id, suppliers_nama, suppliers_id, status, trash, ids_his, reference_id, reference_nomer, salesman_nama, gudang_status_nama";

        if ($targetId > 0 && $isExactParam) {
            // Kasus 0: User memilih langsung transaksi dari tabel kandidat / multi-match
            $this->db->select($selectFields);
            $this->db->from("transaksi");
            $this->db->where("trash", 0);
            $this->db->where("id", $targetId);
            $this->db->limit(1);
            $directMatches = $this->db->get()->result();

            if (count($directMatches) > 0) {
                $targetTr = $directMatches[0];
                $dataNota = $targetTr;
                $items = $this->_extractItems($targetTr);
                $searchMode = "direct";
            } else {
                $errMsg = "Dokumen transaksi ID #" . $targetId . " tidak ditemukan atau dalam status dibatalkan (trash).";
            }
        }
        elseif ($keyword !== "") {
            $hasSpace = (strpos($keyword, ' ') !== false);
            $isSubStepQuery = (strpos($keyword, '_') !== false);

            // Batasi jangkauan pada 200.000 transaksi terbaru untuk kueri tanpa indeks B-Tree mandiri
            $maxRow = $this->db->select_max("id")->get("transaksi")->row();
            $maxId = isset($maxRow->id) ? intval($maxRow->id) : 0;
            $minId = ($maxId > 200000) ? ($maxId - 200000) : 0;

            // Dokumen invoice di ERP Everest tidak pernah mengandung spasi.
            // Jika kata kunci mengandung spasi, langsung alihkan ke pencarian Rekanan / Konsumen (Tier 4).
            if (!$hasSpace) {
                // Preprocessing & Normalisasi Input: Siapkan varian karakter (- dan .)
                $kwOriginal = $keyword;
                $kwDots = preg_replace('/\.+/', '.', str_replace('-', '.', $keyword));
                $kwDashes = preg_replace('/-+/', '-', str_replace('.', '-', $keyword));
                
                // Array varian unik untuk mengakomodasi entri 5822-*** maupun 5822.***
                $variants = array_values(array_unique(array($kwOriginal, $kwDots, $kwDashes)));

                // =========================================================================
                // TIER 1: Exact Match pada nomer dan nomer2 (Sangat cepat via B-Tree Index)
                // Menggunakan grouped OR equality untuk mencegah MySQL Error 1271 (Collation Mismatch)
                // =========================================================================
                $this->db->select($selectFields);
                $this->db->from("transaksi");
                $this->db->where("trash", 0);
                if (!$isSubStepQuery) {
                    $this->db->where("link_id", 0);
                }
                $this->db->group_start();
                foreach ($variants as $v) {
                    $this->db->or_where("nomer", $v);
                }
                $this->db->group_end();
                $this->db->limit(15);
                $exactMatches = $this->db->get()->result();

                if (count($exactMatches) === 0) {
                    $this->db->select($selectFields);
                    $this->db->from("transaksi");
                    $this->db->where("trash", 0);
                    if (!$isSubStepQuery) {
                        $this->db->where("link_id", 0);
                    }
                    $this->db->group_start();
                    foreach ($variants as $v) {
                        $this->db->or_where("nomer2", $v);
                    }
                    $this->db->group_end();
                    $this->db->limit(15);
                    $exactMatches = $this->db->get()->result();
                }

                if (count($exactMatches) === 1 && !isset($_GET['list'])) {
                    $targetTr = $exactMatches[0];
                    $dataNota = $targetTr;
                    $items = $this->_extractItems($targetTr);
                    $searchMode = "exact";
                } elseif (count($exactMatches) > 1) {
                    $this->_enrichMatches($exactMatches);
                    $matches = $exactMatches;
                    $searchMode = "exact";
                } else {
                    // =========================================================================
                    // TIER 2: Prefix Match (LIKE 'keyword%') pada nomer & nomer2
                    // =========================================================================
                    $prefixMatches = array();
                    $foundIds = array();

                    foreach ($variants as $v) {
                        if (strlen($v) < 2) continue;

                        // Prefix pada nomer (memakai B-Tree index no_transaksi)
                        $this->db->select($selectFields);
                        $this->db->from("transaksi");
                        $this->db->where("trash", 0);
                        if (!$isSubStepQuery) {
                            $this->db->where("link_id", 0);
                        }
                        $this->db->like("nomer", $v, 'after');
                        $this->db->order_by("id", "DESC");
                        $this->db->limit(15);
                        $res = $this->db->get()->result();
                        foreach ($res as $row) {
                            if (!in_array($row->id, $foundIds)) {
                                $foundIds[] = $row->id;
                                $prefixMatches[] = $row;
                            }
                        }
                        if (count($prefixMatches) >= 15) break;

                        // Prefix pada nomer2 (dibatasi minId agar tidak full table scan)
                        if (count($prefixMatches) < 15) {
                            $this->db->select($selectFields);
                            $this->db->from("transaksi");
                            $this->db->where("trash", 0);
                            if (!$isSubStepQuery) {
                                $this->db->where("link_id", 0);
                            }
                            if ($minId > 0) {
                                $this->db->where("id >=", $minId);
                            }
                            $this->db->like("nomer2", $v, 'after');
                            $this->db->order_by("id", "DESC");
                            $this->db->limit(15);
                            $res2 = $this->db->get()->result();
                            foreach ($res2 as $row) {
                                if (!in_array($row->id, $foundIds)) {
                                    $foundIds[] = $row->id;
                                    $prefixMatches[] = $row;
                                }
                            }
                        }
                        if (count($prefixMatches) >= 15) break;
                    }

                    if (count($prefixMatches) === 1 && !isset($_GET['list'])) {
                        $targetTr = $prefixMatches[0];
                        $dataNota = $targetTr;
                        $items = $this->_extractItems($targetTr);
                        $searchMode = "prefix";
                    } elseif (count($prefixMatches) > 1) {
                        $this->_enrichMatches($prefixMatches);
                        $matches = $prefixMatches;
                        $searchMode = "prefix";
                    } else {
                        // =========================================================================
                        // TIER 3: Bounded Similarity Match (LIKE '%keyword%') pada recent rows
                        // =========================================================================
                        if (strlen($keyword) >= 3) {
                            $simMatches = array();
                            $simIds = array();

                            foreach ($variants as $v) {
                                if (strlen($v) < 3) continue;

                                // 1. Partial match pada nomer
                                $this->db->select($selectFields);
                                $this->db->from("transaksi");
                                $this->db->where("trash", 0);
                                if (!$isSubStepQuery) {
                                    $this->db->where("link_id", 0);
                                }
                                if ($minId > 0) {
                                    $this->db->where("id >=", $minId);
                                }
                                $this->db->like("nomer", $v, 'both');
                                $this->db->order_by("id", "DESC");
                                $this->db->limit(15);
                                $simRes1 = $this->db->get()->result();

                                foreach ($simRes1 as $sr) {
                                    if (!in_array($sr->id, $simIds)) {
                                        $simIds[] = $sr->id;
                                        $simMatches[] = $sr;
                                    }
                                }
                                if (count($simMatches) >= 15) break;

                                // 2. Partial match pada nomer2 jika hasil masih kurang dari 5
                                if (count($simMatches) < 5) {
                                    $this->db->select($selectFields);
                                    $this->db->from("transaksi");
                                    $this->db->where("trash", 0);
                                    if (!$isSubStepQuery) {
                                        $this->db->where("link_id", 0);
                                    }
                                    if ($minId > 0) {
                                        $this->db->where("id >=", $minId);
                                    }
                                    $this->db->like("nomer2", $v, 'both');
                                    $this->db->order_by("id", "DESC");
                                    $this->db->limit(15);
                                    $simRes2 = $this->db->get()->result();

                                    foreach ($simRes2 as $sr2) {
                                        if (!in_array($sr2->id, $simIds)) {
                                            $simIds[] = $sr2->id;
                                            $simMatches[] = $sr2;
                                        }
                                    }
                                }
                                if (count($simMatches) >= 15) break;
                            }

                            if (count($simMatches) === 1 && !isset($_GET['list'])) {
                                $targetTr = $simMatches[0];
                                $dataNota = $targetTr;
                                $items = $this->_extractItems($targetTr);
                                $searchMode = "similarity";
                            } elseif (count($simMatches) > 1) {
                                $this->_enrichMatches($simMatches);
                                $matches = $simMatches;
                                $searchMode = "similarity";
                            }
                        }
                    }
                }
            }

            // =========================================================================
            // TIER 4: Pencarian berdasarkan Nama Konsumen / Pihak / Rekanan (Wajib link_id = 0)
            // Dijalankan jika keyword berspasi ATAU jika Tier 1-3 tidak menemukan hasil
            // =========================================================================
            if (!$dataNota && empty($matches)) {
                $this->db->select($selectFields);
                $this->db->from("transaksi");
                $this->db->where("trash", 0);
                $this->db->where("link_id", 0);
                if ($minId > 0) {
                    $this->db->where("id >=", $minId);
                }
                $this->db->group_start();
                $this->db->like("customers_nama", $keyword, 'both');
                $this->db->or_like("suppliers_nama", $keyword, 'both');
                $this->db->group_end();
                $this->db->order_by("id", "DESC");
                $this->db->limit(15);
                $custMatches = $this->db->get()->result();

                if (count($custMatches) === 1 && !isset($_GET['list'])) {
                    $targetTr = $custMatches[0];
                    $dataNota = $targetTr;
                    $items = $this->_extractItems($targetTr);
                    $searchMode = "customer";
                } elseif (count($custMatches) > 1) {
                    $this->_enrichMatches($custMatches);
                    $matches = $custMatches;
                    $searchMode = "customer";
                } else {
                    $errMsg = "Dokumen transaksi atau rekanan dengan kata kunci '" . htmlspecialchars($keyword, ENT_QUOTES, 'UTF-8') . "' tidak ditemukan atau dalam status dibatalkan (trash).";
                }
            }
        }

        // =========================================================================
        // KLASIFIKASI KATEGORI DOKUMEN & PENGECEKAN HAK AKSES FINANSIAL
        // =========================================================================
        $isFinancial = false;
        $hasFinancialAccess = false;
        $financialInvoices = array();

        if ($dataNota !== null) {
            $isFinancial = $this->_isFinancialTransaction($dataNota);
            $hasFinancialAccess = $this->_canAccessFinancial();

            if ($isFinancial) {
                if ($hasFinancialAccess) {
                    $financialInvoices = $this->_extractFinancialInvoices($dataNota);
                }
                $items = array();
            }
        }

        $title = "Tracer Stok Invoice";
        $subTitle = "Pelacak Mutasi Stok Persediaan per Invoice";

        $data = array(
            "title" => $title,
            "subTitle" => $subTitle,
            "keyword" => $keyword,
            "dataNota" => $dataNota,
            "items" => $items,
            "matches" => $matches,
            "errMsg" => $errMsg,
            "searchMode" => $searchMode,
            "isFinancial" => $isFinancial,
            "hasFinancialAccess" => $hasFinancialAccess,
            "financialInvoices" => $financialInvoices,
            "thisPage" => base_url() . "addons/TracerStokInvoice/index",
        );

        $this->load->view("tracer_stok_invoice", $data);
    }

    private function _enrichMatches(&$matches)
    {
        if (!is_array($matches) || count($matches) === 0) {
            return;
        }

        $allIds = array();
        foreach ($matches as $m) {
            $allIds[] = (int)$m->id;
            if (isset($m->reference_id) && is_numeric($m->reference_id) && $m->reference_id > 0) {
                $allIds[] = (int)$m->reference_id;
            }
        }
        $allIds = array_values(array_unique($allIds));

        $regMap = array();
        if (count($allIds) > 0) {
            $this->db->select("transaksi_id, main");
            $this->db->from("transaksi_data_registry");
            $this->db->where_in("transaksi_id", $allIds);
            $regRows = $this->db->get()->result_array();
            if (is_array($regRows)) {
                foreach ($regRows as $rr) {
                    if (!empty($rr['main'])) {
                        $decoded = blobDecode($rr['main']);
                        if (is_array($decoded)) {
                            $regMap[$rr['transaksi_id']] = $decoded;
                        }
                    }
                }
            }
        }

        foreach ($matches as &$m) {
            $main = isset($regMap[$m->id]) ? $regMap[$m->id] : array();
            
            // Fallback 1: Registry dari reference_id (SO induk / transaksi hulu)
            if (empty($main) && isset($m->reference_id) && isset($regMap[$m->reference_id])) {
                $main = $regMap[$m->reference_id];
            }

            // Fallback 2: Registry dari transaksi lain yang satu rumpun nomor (prefix sebelum _)
            if (empty($main)) {
                $parts = explode('_', $m->nomer);
                $baseNomer = $parts[0];
                foreach ($matches as $otherM) {
                    if ($otherM->id !== $m->id && strpos($otherM->nomer, $baseNomer) === 0 && isset($regMap[$otherM->id])) {
                        $main = $regMap[$otherM->id];
                        break;
                    }
                }
            }

            // A. Konsumen / Pihak
            $konsumen = !empty($m->customers_nama) ? $m->customers_nama : (!empty($m->suppliers_nama) ? $m->suppliers_nama : "");
            if (empty($konsumen) && !empty($main['customerName'])) {
                $konsumen = $main['customerName'];
            }
            if (empty($konsumen) && !empty($main['pihakName'])) {
                $konsumen = $main['pihakName'];
            }
            if (empty($konsumen) && !empty($main['pihakMainName'])) {
                $konsumen = $main['pihakMainName'];
            }
            if (empty($konsumen)) {
                $konsumen = "-";
            }
            $m->konsumen_label = $konsumen;

            // Sub Pihak (misal asal kirim atau nama salesman)
            $subPihak = "";
            if (!empty($main['pihakName']) && $main['pihakName'] !== $konsumen) {
                $subPihak = "Asal: " . $main['pihakName'];
            } elseif (!empty($m->gudang_status_nama) && $m->gudang_status_nama !== $konsumen) {
                $subPihak = "Pihak: " . $m->gudang_status_nama;
            } elseif (!empty($m->salesman_nama)) {
                $subPihak = "Sales: " . $m->salesman_nama;
            }
            $m->sub_pihak = $subPihak;

            // B. Lokasi Cabang & Gudang
            $asalCab = !empty($main['cabang2Name']) ? $main['cabang2Name'] : "";
            $asalGud = !empty($main['gudang2Name']) ? $main['gudang2Name'] : "";
            $tujuanCab = !empty($main['cabangName']) ? $main['cabangName'] : "";
            $tujuanGud = !empty($main['gudangName']) ? $main['gudangName'] : "";

            $asalStr = trim($asalCab . ($asalGud ? " (" . $asalGud . ")" : ""));
            $tujuanStr = trim($tujuanCab . ($tujuanGud ? " (" . $tujuanGud . ")" : ""));

            if (!empty($asalStr) && !empty($tujuanStr) && $asalStr !== $tujuanStr) {
                $lokasi = "<b>Dari:</b> " . $asalStr . "<br><b>Ke:</b> " . $tujuanStr;
            } elseif (!empty($tujuanStr)) {
                $lokasi = $tujuanStr;
            } elseif (!empty($asalStr)) {
                $lokasi = $asalStr;
            } elseif (!empty($m->cabang_id)) {
                $lokasi = "Cabang #" . $m->cabang_id . (!empty($m->gudang_id) ? " (Gudang #" . $m->gudang_id . ")" : "");
            } else {
                $lokasi = "-";
            }
            $m->lokasi_label = $lokasi;

            // C. Jenis & Tahapan
            $jenisDesc = !empty($main['jenisTrName']) ? ucwords(str_replace('_', ' ', $main['jenisTrName'])) : (!empty($main['jenis_label']) ? ucwords($main['jenis_label']) : "");
            $stepDesc = "";
            if (strpos($m->jenis, '_') !== false) {
                $parts = explode('_', $m->jenis);
                $stepDesc = "Tahap " . $parts[1];
            } elseif (isset($main['stepNumber']) && !empty($main['stepNumber'])) {
                $stepDesc = "Langkah " . $main['stepNumber'];
            }
            $m->jenis_deskripsi = $jenisDesc;
            $m->step_label = $stepDesc;

            // D. Referensi Dokumen
            $refDoc = !empty($m->reference_nomer) ? $m->reference_nomer : (!empty($main['requestReferenceNomer']) ? $main['requestReferenceNomer'] : (!empty($main['requestReferenceNomerTop']) ? $main['requestReferenceNomerTop'] : ""));
            $m->ref_doc = $refDoc;

            // E. Cek apakah transaksi keuangan / payment
            $m->is_financial = $this->_isFinancialTransaction($m);
        }
    }

    private function _canAccessFinancial()
    {
        $membership = isset($this->session->login['membership']) ? $this->session->login['membership'] : array();
        if (!is_array($membership)) {
            $membership = array($membership);
        }

        $this->load->config('usergroup');
        $roleDivision = $this->config->item('roleDivision');
        $roleHierarchy = $this->config->item('roleHierarchy');

        foreach ($membership as $r) {
            if ($r === 'root' || $r === 'superman') {
                return true;
            }
            if (isset($roleDivision[$r]) && in_array($roleDivision[$r], array('system', 'management', 'finance'))) {
                return true;
            }
            if (isset($roleHierarchy[$r]) && $roleHierarchy[$r] <= 6) {
                return true;
            }
        }

        if (isset($this->session->login['jenis'])) {
            $j = strtolower($this->session->login['jenis']);
            if (in_array($j, array('admin', 'finance', 'superman', 'manager'))) {
                return true;
            }
        }

        return false;
    }

    private function _isFinancialTransaction($tr)
    {
        if (!$tr) return false;

        $financialTypes = array(
            '489', '487', '483', '462', '4821', '749', '1424', '1485', '1487', 
            '1483', '1488', '3675', '4811', '1475', '1477', '475', '477', 
            '4448', '4411', '758', '759', '481', '482', '484', '485'
        );

        $j = (string)$tr->jenis;
        if (in_array($j, $financialTypes)) {
            return true;
        }

        if (isset($tr->id) && $tr->id > 0) {
            $this->db->select("items, main");
            $this->db->from("transaksi_data_registry");
            $this->db->where("transaksi_id", $tr->id);
            $reg = $this->db->get()->row();
            if ($reg) {
                if (!empty($reg->main)) {
                    $m = blobDecode($reg->main);
                    if (is_array($m)) {
                        if (isset($m['jenisTr']) && in_array((string)$m['jenisTr'], $financialTypes)) {
                            return true;
                        }
                        if (isset($m['jenis_transaksi']) && in_array((string)$m['jenis_transaksi'], $financialTypes)) {
                            return true;
                        }
                    }
                }
                if (!empty($reg->items)) {
                    $rawItems = blobDecode($reg->items);
                    if (is_array($rawItems)) {
                        foreach ($rawItems as $it) {
                            if (isset($it['handler']) && strpos($it['handler'], 'pembayaran/') !== false) {
                                return true;
                            }
                            if (isset($it['sub_tagihan']) || isset($it['tagihan']) || isset($it['nilai_bayar'])) {
                                if (!isset($it['produk_id']) && !isset($it['kode'])) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    private function _extractFinancialInvoices(&$targetTr)
    {
        $financialInvoices = array();
        $main = array();
        $rawItems = array();

        $this->db->select("items, main");
        $this->db->from("transaksi_data_registry");
        $this->db->where("transaksi_id", $targetTr->id);
        $rowReg = $this->db->get()->row();

        if ($rowReg) {
            if (!empty($rowReg->items)) {
                $decodedItems = blobDecode($rowReg->items);
                if (is_array($decodedItems)) {
                    $rawItems = $decodedItems;
                }
            }
            if (!empty($rowReg->main)) {
                $decodedMain = blobDecode($rowReg->main);
                if (is_array($decodedMain)) {
                    $main = $decodedMain;
                }
            }
        }

        $konsumen = !empty($targetTr->customers_nama) ? $targetTr->customers_nama : (!empty($targetTr->suppliers_nama) ? $targetTr->suppliers_nama : "");
        if (empty($konsumen) && !empty($main['customerName'])) {
            $konsumen = $main['customerName'];
        }
        if (empty($konsumen) && !empty($main['pihakName'])) {
            $konsumen = $main['pihakName'];
        }
        if (empty($konsumen) && !empty($main['supplierName'])) {
            $konsumen = $main['supplierName'];
        }
        if (empty($konsumen)) {
            $konsumen = "-";
        }
        $targetTr->konsumen_nama = $konsumen;

        $targetTr->sub_pihak = !empty($main['sellerName']) ? "Oleh: " . $main['sellerName'] : "";
        $targetTr->lokasi_info = !empty($main['cabangName']) ? $main['cabangName'] . (!empty($main['gudangName']) ? " (" . $main['gudangName'] . ")" : "") : "-";
        $targetTr->jenis_deskripsi = !empty($main['jenisTrName']) ? ucwords(str_replace('_', ' ', $main['jenisTrName'])) : "Account Payable / Receivable Payment";
        $targetTr->ref_doc = !empty($targetTr->reference_nomer) ? $targetTr->reference_nomer : "";

        $invoiceIds = array();
        foreach ($rawItems as $k => $it) {
            $invId = 0;
            if (isset($it['refID']) && is_numeric($it['refID']) && $it['refID'] > 0) {
                $invId = (int)$it['refID'];
            } elseif (isset($it['id']) && is_numeric($it['id']) && $it['id'] > 0) {
                $invId = (int)$it['id'];
            } elseif (isset($it['sub_refID']) && is_numeric($it['sub_refID']) && $it['sub_refID'] > 0) {
                $invId = (int)$it['sub_refID'];
            }
            if ($invId > 0) {
                $invoiceIds[] = $invId;
            }
        }
        $invoiceIds = array_values(array_unique($invoiceIds));

        $invMap = array();
        $invRegMap = array();
        if (count($invoiceIds) > 0) {
            $this->db->select("id, nomer, nomer2, jenis, fulldate, customers_nama, suppliers_nama, cabang_id, gudang_id, status, trash");
            $this->db->from("transaksi");
            $this->db->where_in("id", $invoiceIds);
            $res = $this->db->get()->result_array();
            if (is_array($res)) {
                foreach ($res as $row) {
                    $invMap[$row['id']] = $row;
                }
            }

            $this->db->select("transaksi_id, main");
            $this->db->from("transaksi_data_registry");
            $this->db->where_in("transaksi_id", $invoiceIds);
            $regRes = $this->db->get()->result_array();
            if (is_array($regRes)) {
                foreach ($regRes as $rrow) {
                    if (!empty($rrow['main'])) {
                        $decMain = blobDecode($rrow['main']);
                        if (is_array($decMain)) {
                            $invRegMap[$rrow['transaksi_id']] = $decMain;
                        }
                    }
                }
            }
        }

        foreach ($rawItems as $k => $it) {
            $invId = 0;
            if (isset($it['refID']) && is_numeric($it['refID']) && $it['refID'] > 0) {
                $invId = (int)$it['refID'];
            } elseif (isset($it['id']) && is_numeric($it['id']) && $it['id'] > 0) {
                $invId = (int)$it['id'];
            } elseif (isset($it['sub_refID']) && is_numeric($it['sub_refID']) && $it['sub_refID'] > 0) {
                $invId = (int)$it['sub_refID'];
            }

            $dbInv = isset($invMap[$invId]) ? $invMap[$invId] : null;
            $regMain = isset($invRegMap[$invId]) ? $invRegMap[$invId] : array();

            $nomerDoc = !empty($dbInv['nomer']) ? $dbInv['nomer'] : (isset($it['name']) ? $it['name'] : (isset($it['nama']) ? $it['nama'] : "-"));
            $nomer2Doc = !empty($dbInv['nomer2']) ? $dbInv['nomer2'] : (isset($it['nomer2']) ? $it['nomer2'] : "");
            $tglFaktur = !empty($dbInv['fulldate']) ? substr($dbInv['fulldate'], 0, 10) : (isset($it['dateFaktur']) ? $it['dateFaktur'] : "-");
            $nominal = isset($it['nilai_bayar']) && $it['nilai_bayar'] > 0 ? (float)$it['nilai_bayar'] : (isset($it['sub_tagihan']) ? (float)$it['sub_tagihan'] : (isset($it['subtotal']) ? (float)$it['subtotal'] : 0));
            $jenisDoc = !empty($dbInv['jenis']) ? $dbInv['jenis'] : (isset($it['sub_jenis']) ? $it['sub_jenis'] : "");

            // Pihak Rekanan / Konsumen
            $mitra = !empty($dbInv['customers_nama']) ? $dbInv['customers_nama'] : (!empty($dbInv['suppliers_nama']) ? $dbInv['suppliers_nama'] : (!empty($regMain['customerName']) ? $regMain['customerName'] : (!empty($regMain['supplierName']) ? $regMain['supplierName'] : (!empty($regMain['pihakName']) ? $regMain['pihakName'] : "-"))));

            // Lokasi Cabang & Gudang
            $lokasi = !empty($regMain['cabangName']) ? $regMain['cabangName'] . (!empty($regMain['gudangName']) ? " (" . $regMain['gudangName'] . ")" : "") : (!empty($dbInv['cabang_id']) ? "Cabang #" . $dbInv['cabang_id'] . (!empty($dbInv['gudang_id']) ? " (Gudang #" . $dbInv['gudang_id'] . ")" : "") : "");

            // Deskripsi Jenis Transaksi
            $jenisDeskripsi = !empty($regMain['jenisTrName']) ? ucwords(str_replace('_', ' ', $regMain['jenisTrName'])) : (!empty($regMain['jenis_label']) ? ucwords($regMain['jenis_label']) : "");

            $financialInvoices[] = array(
                'transaksi_id' => $invId,
                'nomer' => $nomerDoc,
                'nomer2' => $nomer2Doc,
                'tgl_faktur' => $tglFaktur,
                'nominal' => $nominal,
                'jenis' => $jenisDoc,
                'jenis_deskripsi' => $jenisDeskripsi,
                'mitra' => $mitra,
                'lokasi' => $lokasi,
                'e_faktur' => isset($it['eFaktur']) ? $it['eFaktur'] : "",
            );
        }

        return $financialInvoices;
    }

    private function _extractItems(&$targetTr)
    {
        if ($this->_isFinancialTransaction($targetTr)) {
            return array();
        }

        $rawItems = array();
        $main = array();

        // 1. Cek dari transaksi_data_registry (items & main)
        $this->db->select("items, main");
        $this->db->from("transaksi_data_registry");
        $this->db->where("transaksi_id", $targetTr->id);
        $rowReg = $this->db->get()->row();

        if ($rowReg) {
            if (!empty($rowReg->items)) {
                $decodedItems = blobDecode($rowReg->items);
                if (is_array($decodedItems) && count($decodedItems) > 0) {
                    $rawItems = $decodedItems;
                }
            }
            if (!empty($rowReg->main)) {
                $decodedMain = blobDecode($rowReg->main);
                if (is_array($decodedMain)) {
                    $main = $decodedMain;
                }
            }
        }

        // =========================================================================
        // ENRICHMENT HEADER METADATA (Konsumen, Asal-Tujuan, Lokasi, Deskripsi Jenis)
        // =========================================================================
        // A. Konsumen / Pihak
        $konsumen = !empty($targetTr->customers_nama) ? $targetTr->customers_nama : (!empty($targetTr->suppliers_nama) ? $targetTr->suppliers_nama : "");
        if (empty($konsumen) && !empty($main['customerName'])) {
            $konsumen = $main['customerName'];
        }
        if (empty($konsumen) && !empty($main['pihakName'])) {
            $konsumen = $main['pihakName'];
        }
        if (empty($konsumen) && !empty($main['pihakMainName'])) {
            $konsumen = $main['pihakMainName'];
        }
        if (empty($konsumen)) {
            $konsumen = "-";
        }
        $targetTr->konsumen_nama = $konsumen;

        // Sub Pihak (misal pihak asal pengirim barang atau rekanan SO)
        $subPihak = "";
        if (!empty($main['pihakName']) && $main['pihakName'] !== $konsumen) {
            $subPihak = "Asal / Pihak: " . $main['pihakName'];
        } elseif (!empty($main['suppliers_nama']) && $main['suppliers_nama'] !== $konsumen && $main['suppliers_nama'] !== '0') {
            $subPihak = "Supplier: " . $main['suppliers_nama'];
        } elseif (!empty($targetTr->gudang_status_nama) && $targetTr->gudang_status_nama !== $konsumen) {
            $subPihak = "Pihak: " . $targetTr->gudang_status_nama;
        }
        $targetTr->sub_pihak = $subPihak;

        // B. Lokasi Cabang / Gudang Asal & Tujuan
        $asalCab = !empty($main['cabang2Name']) ? $main['cabang2Name'] : "";
        $asalGud = !empty($main['gudang2Name']) ? $main['gudang2Name'] : "";
        $tujuanCab = !empty($main['cabangName']) ? $main['cabangName'] : "";
        $tujuanGud = !empty($main['gudangName']) ? $main['gudangName'] : "";

        $asalStr = trim($asalCab . ($asalGud ? " (" . $asalGud . ")" : ""));
        $tujuanStr = trim($tujuanCab . ($tujuanGud ? " (" . $tujuanGud . ")" : ""));

        if (!empty($asalStr) && !empty($tujuanStr) && $asalStr !== $tujuanStr) {
            $lokasi = "<b>Dari:</b> " . $asalStr . "<br><b>Ke:</b> " . $tujuanStr;
        } elseif (!empty($tujuanStr)) {
            $lokasi = $tujuanStr;
        } elseif (!empty($asalStr)) {
            $lokasi = $asalStr;
        } elseif (!empty($targetTr->cabang_id)) {
            $lokasi = "Cabang #" . $targetTr->cabang_id . (!empty($targetTr->gudang_id) ? " (Gudang #" . $targetTr->gudang_id . ")" : "");
        } else {
            $lokasi = "-";
        }
        $targetTr->lokasi_info = $lokasi;

        // C. Deskripsi Jenis Transaksi
        $jenisDesc = !empty($main['jenisTrName']) ? ucwords(str_replace('_', ' ', $main['jenisTrName'])) : (!empty($main['jenis_label']) ? ucwords($main['jenis_label']) : "");
        $targetTr->jenis_deskripsi = $jenisDesc;

        // D. Nomor Referensi (SO / PO / Request)
        $refDoc = !empty($targetTr->reference_nomer) ? $targetTr->reference_nomer : (!empty($main['requestReferenceNomer']) ? $main['requestReferenceNomer'] : (!empty($main['requestReferenceNomerTop']) ? $main['requestReferenceNomerTop'] : ""));
        $targetTr->ref_doc = $refDoc;

        // =========================================================================
        // CANDIDATE TRANSACTIONS RESOLUTION (Penelusuran Mutasi Fisik Riil)
        // =========================================================================
        $candidateTrIds = array((int)$targetTr->id);
        
        // 1. Ambil transaksi step pengiriman dari ids_his jika ada
        if (!empty($targetTr->ids_his)) {
            $idshis = blobDecode($targetTr->ids_his);
            if (is_array($idshis)) {
                foreach ($idshis as $stepNum => $stepSpec) {
                    if (isset($stepSpec['trID']) && !empty($stepSpec['trID'])) {
                        $candidateTrIds[] = (int)$stepSpec['trID'];
                    }
                }
            }
        }

        // 2. Telusuri transaksi turunan (DO / Surat Jalan / Penerimaan) yang merujuk dokumen ini
        // Menggunakan filter id >= targetTr->id untuk memanfaatkan indeks PRIMARY sehingga pemindaian langsung fokus pada dokumen hilir (optimasi 53x lebih cepat)
        $this->db->select("id");
        $this->db->from("transaksi");
        if (isset($targetTr->id) && $targetTr->id > 0) {
            $this->db->where("id >=", (int)$targetTr->id);
        }
        $this->db->group_start();
        $this->db->where("reference_nomer", $targetTr->nomer);
        $this->db->or_where("reference_id", $targetTr->id);
        $this->db->group_end();
        $this->db->where("trash", 0);
        $this->db->order_by("id", "DESC");
        $this->db->limit(20);
        $downstreamRows = $this->db->get()->result_array();

        if (is_array($downstreamRows)) {
            foreach ($downstreamRows as $ds) {
                $candidateTrIds[] = (int)$ds['id'];
            }
        }

        $candidateTrIds = array_values(array_unique(array_filter($candidateTrIds)));
        $cabangId = !empty($targetTr->cabang_id) ? $targetTr->cabang_id : my_cabang_id();

        // =========================================================================
        // BATCH ENRICHMENT KE MASTER PRODUK & SATUAN (Single Batch Query)
        // =========================================================================
        $pids = array();
        foreach ($rawItems as $it) {
            $pId = 0;
            if (isset($it['produk_id']) && is_numeric($it['produk_id'])) {
                $pId = (int)$it['produk_id'];
            } elseif (isset($it['id']) && is_numeric($it['id'])) {
                $pId = (int)$it['id'];
            }
            if ($pId > 0) {
                $pids[] = $pId;
            }
        }

        $prodMap = array();
        if (count($pids) > 0) {
            $pids = array_values(array_unique($pids));
            $this->db->select("p.id, p.kode, p.barcode, p.nama, p.satuan, p.satuan_id, p.kategori_nama, p.tipe_nama, p.jml_serial, s.kode as satuan_kode, s.nama as satuan_nama");
            $this->db->from("produk p");
            $this->db->join("satuan s", "(p.satuan_id = s.id OR p.satuan = s.id OR p.satuan = s.kode)", "left");
            $this->db->where_in("p.id", $pids);
            $prodRows = $this->db->get()->result_array();
            if (is_array($prodRows)) {
                foreach ($prodRows as $pr) {
                    $prodMap[$pr['id']] = $pr;
                }
            }
        }

        // =========================================================================
        // BATCH PENCARIAN MUTASI STOK RIIL PER PRODUK (Untuk Highlight Bold)
        // =========================================================================
        $mutationMap = array();
        if (count($pids) > 0 && count($candidateTrIds) > 0) {
            // A. Cek di tabel mutasi persediaan produk jadi
            $this->db->select("id, transaksi_id, transaksi_no, extern_id, fulldate, cabang_id, gudang_id");
            $this->db->from("__rek_pembantu_produk__1010030030");
            $this->db->where_in("extern_id", $pids);
            $this->db->where_in("transaksi_id", $candidateTrIds);
            $this->db->order_by("id", "DESC");
            $mutRows = $this->db->get()->result_array();
            if (is_array($mutRows)) {
                foreach ($mutRows as $mr) {
                    $pidKey = (int)$mr['extern_id'];
                    if (!isset($mutationMap[$pidKey])) {
                        $mutationMap[$pidKey] = $mr;
                    }
                }
            }

            // B. Cek di tabel mutasi supplies untuk item yang belum cocok
            $missingPids = array();
            foreach ($pids as $p) {
                if (!isset($mutationMap[$p])) {
                    $missingPids[] = $p;
                }
            }
            if (count($missingPids) > 0) {
                $this->db->select("id, transaksi_id, transaksi_no, extern_id, fulldate, cabang_id, gudang_id");
                $this->db->from("__rek_pembantu_supplies__1010030010");
                $this->db->where_in("extern_id", $missingPids);
                $this->db->where_in("transaksi_id", $candidateTrIds);
                $this->db->order_by("id", "DESC");
                $mutSupplies = $this->db->get()->result_array();
                if (is_array($mutSupplies)) {
                    foreach ($mutSupplies as $ms) {
                        $pidKey = (int)$ms['extern_id'];
                        if (!isset($mutationMap[$pidKey])) {
                            $mutationMap[$pidKey] = $ms;
                        }
                    }
                }
            }
        }

        $processedItems = array();
        foreach ($rawItems as $idx => $it) {
            $pId = 0;
            if (isset($it['produk_id']) && is_numeric($it['produk_id'])) {
                $pId = (int)$it['produk_id'];
            } elseif (isset($it['id']) && is_numeric($it['id'])) {
                $pId = (int)$it['id'];
            }

            $pInfo = isset($prodMap[$pId]) ? $prodMap[$pId] : array();

            // A. Kode Produk (Utamakan dari item, fallback ke master produk.kode atau barcode)
            $kode = !empty($it['kode']) ? $it['kode'] : (!empty($it['produk_kode']) ? $it['produk_kode'] : "");
            if (empty($kode) && !empty($pInfo['kode'])) {
                $kode = $pInfo['kode'];
            }
            if (empty($kode) && !empty($pInfo['barcode']) && $pInfo['barcode'] !== '0') {
                $kode = $pInfo['barcode'];
            }
            if (empty($kode)) {
                $kode = "-";
            }

            // B. Nama Produk
            $nama = !empty($it['nama']) ? $it['nama'] : (!empty($it['produk_nama']) ? $it['produk_nama'] : (!empty($pInfo['nama']) ? $pInfo['nama'] : "-"));

            // C. Qty Faktur
            $qty = isset($it['jml']) ? (float)$it['jml'] : (isset($it['qty']) ? (float)$it['qty'] : 0);

            // D. Satuan (Utamakan item, fallback ke relasi satuan master, fallback ke Unit / Pcs)
            $satuan = !empty($it['satuan']) ? $it['satuan'] : (!empty($it['uom']) ? $it['uom'] : "");
            if (empty($satuan) && !empty($pInfo['satuan_kode'])) {
                $satuan = $pInfo['satuan_kode'];
            }
            if (empty($satuan) && !empty($pInfo['satuan']) && !is_numeric($pInfo['satuan'])) {
                $satuan = $pInfo['satuan'];
            }
            if (empty($satuan)) {
                $katCheck = !empty($pInfo['kategori_nama']) ? strtolower($pInfo['kategori_nama']) : (!empty($it['kategori_nama']) ? strtolower($it['kategori_nama']) : "");
                $satuan = ($katCheck === 'unit') ? 'Unit' : 'Pcs';
            }

            // E. Tipe / Kategori
            $katNama = !empty($pInfo['kategori_nama']) ? $pInfo['kategori_nama'] : (!empty($it['kategori_nama']) ? $it['kategori_nama'] : (!empty($it['tipe']) ? $it['tipe'] : ""));
            $tipeSpec = !empty($pInfo['tipe_nama']) ? $pInfo['tipe_nama'] : (!empty($it['tipe_nama']) ? $it['tipe_nama'] : "");

            $displayTipe = "";
            if (!empty($katNama)) {
                $displayTipe = strtoupper($katNama);
            }
            if (!empty($tipeSpec)) {
                if (!empty($displayTipe)) {
                    $displayTipe .= " (" . strtoupper($tipeSpec) . ")";
                } else {
                    $displayTipe = strtoupper($tipeSpec);
                }
            }
            if (empty($displayTipe)) {
                $displayTipe = "-";
            }

            // F. Gudang Item
            $gudangId = isset($it['gudang_id']) && !empty($it['gudang_id']) ? $it['gudang_id'] : $targetTr->gudang_id;

            // G. Cek apakah item fisik / inventaris
            $isStok = ($pId > 0 && !preg_match('/^(ongkir|biaya|jasa|diskon)/i', $nama));

            // H. Cek apakah Unit / memiliki Serial Number
            $isUnit = (strtolower($katNama) === 'unit' || (isset($pInfo['jml_serial']) && $pInfo['jml_serial'] > 0) || (isset($it['jml_serial']) && $it['jml_serial'] > 0) || (isset($it['serial']) && !empty($it['serial'])));

            // Rekening: default produk jadi 1010030030
            $relName = "RekeningPembantuProduk";
            $rekCode = "1010030030";
            if (isset($it['jenis_rekening']) && $it['jenis_rekening'] === 'supplies') {
                $relName = "RekeningPembantuSupplies";
                $rekCode = "1010030010";
            }

            // Cek apakah ada mutasi riil yang ditemukan untuk produk ini
            $hasRealMutasi = isset($mutationMap[$pId]);
            $mutInfo = $hasRealMutasi ? $mutationMap[$pId] : null;

            $linkMutasi = "";
            $linkSerial = "";
            if ($isStok) {
                if ($hasRealMutasi && $mutInfo) {
                    // KONDISI A: Ditemukan mutasi riil -> Kunci rentang tanggal +/- 7 hari & pasang trID agar baris BOLD
                    $tglMutasi = substr($mutInfo['fulldate'], 0, 10);
                    $date1 = date('Y-m-d', strtotime('-7 days', strtotime($tglMutasi)));
                    $date2 = date('Y-m-d', strtotime('+7 days', strtotime($tglMutasi)));
                    $matchedTrId = $mutInfo['transaksi_id'];

                    $linkMutasi = base_url() . "LedgerV2/viewMoveDetails/" . $relName . "/" . $rekCode . "/" . $pId 
                        . "?o=-1" 
                        . "&w=-1" 
                        . "&date1=" . $date1 
                        . "&date2=" . $date2 
                        . "&trID=" . $matchedTrId;
                } else {
                    // KONDISI B: Belum ada mutasi fisik -> JANGAN kunci tanggal, biarkan LedgerV2 tampilkan 20 mutasi terakhir + saldo terkini
                    $linkMutasi = base_url() . "LedgerV2/viewMoveDetails/" . $relName . "/" . $rekCode . "/" . $pId 
                        . "?o=-1" 
                        . "&w=-1";
                }

                if ($isUnit) {
                    $linkSerial = base_url() . "addons/Qr/viewSerial/?produk_id=" . $pId . "&cabang_id=" . $cabangId . "&gudang_id=" . $gudangId;
                }
            }

            $processedItems[] = array(
                "produk_id" => $pId,
                "kode" => $kode,
                "nama" => $nama,
                "qty" => $qty,
                "satuan" => $satuan,
                "gudang_id" => $gudangId,
                "tipe" => $displayTipe,
                "is_unit" => $isUnit,
                "is_stok" => $isStok,
                "link_mutasi" => $linkMutasi,
                "link_serial" => $linkSerial,
                "do_transaksi_id" => ($hasRealMutasi && $mutInfo) ? $mutInfo['transaksi_id'] : $targetTr->id,
                "do_nomer" => ($hasRealMutasi && $mutInfo) ? $mutInfo['transaksi_no'] : $targetTr->nomer,
                "has_mutasi" => $hasRealMutasi,
            );
        }

        return $processedItems;
    }
}
// END OF COMPLETE REPEATED LOGIC

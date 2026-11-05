<?php

//by: chepy
//date: 10 Sept 2025

function generateShowButton($fase_urut, $prodID, $enabled) {
    $btnJs = "
        var tmpIdForm=top.$('#komposisi_fase_biaya_tambahan_{$fase_urut}{$prodID}');
        var idform=$(tmpIdForm).attr('idform');
        $(tmpIdForm).attr('onclick','document.getElementById(\"'+idform+'\").submit();');
        $(tmpIdForm).prop('disabled', " . ($enabled ? "false" : "true") . ");
        $(tmpIdForm)." . ($enabled ? "addClass('btn-success');" : "removeClass('btn-success');");

    return $btnJs;
}

// Helper untuk hitung subtotal
function getSubtotal($fase_urut, $prodID, $jml, $harga, $value = null) {
    if (isset($_SESSION["NEW_TAMBAHAN"]["komposisi_fase_biaya_tambahan"][$fase_urut][$prodID]["subtotal"])) {
        return round($_SESSION["NEW_TAMBAHAN"]["komposisi_fase_biaya_tambahan"][$fase_urut][$prodID]["subtotal"]);
    }
    if ($value !== null) {
        return round($_SESSION["NEW_TAMBAHAN"]["komposisi_fase_biaya_tambahan"][$fase_urut][$prodID]["jml"] * $value);
    }
    return round($jml * $harga);
}

// Helper untuk cetak output
function printOutputJs($fase_urut, $extraJs, $subTotal, $showButton) {
    $outputJs = base64_encode("
        top.$('#komposisi_fase_biaya_tambahan_{$fase_urut}subtotal').html(addCommas($subTotal));
        $extraJs
        $showButton
    ");
    echo $outputJs;
}

/**
 * Mengambil HPP riil produk project secara dinamis dari MdlHargaProduk2 (tabel price).
 *
 * 1. Prioritas Utama: master table price (MdlHargaProduk2) cabang_id = -1 (hpp_supplier / hpp)
 * 2. Fallback: fifo_avg gudang project pusat: cabang_id = -1, gudang_id = 9
 */
if (!function_exists('getHppProdukProject')) {
    function getHppProdukProject($produk_id) {
        $CI = &get_instance();
        if (empty($produk_id)) {
            return 0;
        }

        // 1. Sumber Utama: master table price (MdlHargaProduk2)
        if ($CI->db->table_exists('price')) {
            $qPrice = $CI->db->select("nilai, jenis_value")
                ->from("price")
                ->where("produk_id", $produk_id)
                ->where("cabang_id", -1)
                ->where("status", "1")
                ->where("trash", "0")
                ->where_in("jenis_value", array("hpp_supplier", "hpp"))
                ->order_by("FIELD(jenis_value, 'hpp_supplier', 'hpp')", "ASC", false)
                ->get();

            if ($qPrice->num_rows() > 0) {
                foreach ($qPrice->result() as $rowPrice) {
                    if ($rowPrice->nilai * 1 > 0) {
                        return (float)$rowPrice->nilai;
                    }
                }
            }
        }

        // 2. Fallback: fifo_avg gudang project pusat (cabang -1, gudang 9)
        if ($CI->db->table_exists('fifo_avg')) {
            $q1 = $CI->db->select("hpp")
                ->from("fifo_avg")
                ->where("produk_id", $produk_id)
                ->where("cabang_id", -1)
                ->where("gudang_id", 9)
                ->where("jenis", "produk")
                ->get();

            if ($q1->num_rows() > 0) {
                $row1 = $q1->row();
                if ($row1->hpp * 1 > 0) {
                    return (float)$row1->hpp;
                }
            }
        }

        return 0;
    }
}

/**
 * Mengambil HPP riil batch untuk banyak produk project dari MdlHargaProduk2 (tabel price).
 *
 * 1. Prioritas Utama: master table price (MdlHargaProduk2) cabang_id = -1 (hpp_supplier / hpp)
 * 2. Fallback: fifo_avg gudang project pusat: gudang_id = 9 (cabang_id = -1)
 */
if (!function_exists('getBatchHppProdukProject')) {
    function getBatchHppProdukProject($arr_produk_ids = array()) {
        $CI = &get_instance();
        $arrResult = array();

        if (empty($arr_produk_ids)) {
            return $arrResult;
        }

        // 1. Sumber Utama: master table price (MdlHargaProduk2)
        if ($CI->db->table_exists('price')) {
            $CI->db->select("produk_id, nilai, jenis_value");
            $CI->db->from("price");
            $CI->db->where("cabang_id", -1);
            $CI->db->where("status", "1");
            $CI->db->where("trash", "0");
            $CI->db->where_in("jenis_value", array("hpp_supplier", "hpp"));
            $CI->db->where_in("produk_id", $arr_produk_ids);
            $CI->db->order_by("FIELD(jenis_value, 'hpp_supplier', 'hpp')", "ASC", false);
            $qPrice = $CI->db->get();

            if ($qPrice->num_rows() > 0) {
                foreach ($qPrice->result() as $rowPrice) {
                    if ($rowPrice->nilai * 1 > 0 && !isset($arrResult[$rowPrice->produk_id])) {
                        $arrResult[$rowPrice->produk_id] = (float)$rowPrice->nilai;
                    }
                }
            }
        }

        // 2. Fallback: fifo_avg gudang project pusat (cabang -1, gudang 9) jika belum ada di master price
        $missing_ids = array();
        foreach ($arr_produk_ids as $pId) {
            if (!isset($arrResult[$pId]) || $arrResult[$pId] <= 0) {
                $missing_ids[] = $pId;
            }
        }

        if (!empty($missing_ids) && $CI->db->table_exists('fifo_avg')) {
            $CI->db->select("produk_id, gudang_id, hpp");
            $CI->db->from("fifo_avg");
            $CI->db->where("cabang_id", -1);
            $CI->db->where("gudang_id", 9);
            $CI->db->where("jenis", "produk");
            $CI->db->where_in("produk_id", $missing_ids);
            $qFifo = $CI->db->get();
            if ($qFifo->num_rows() > 0) {
                foreach ($qFifo->result() as $fRow) {
                    if ($fRow->hpp * 1 > 0 && (!isset($arrResult[$fRow->produk_id]) || $arrResult[$fRow->produk_id] <= 0)) {
                        $arrResult[$fRow->produk_id] = (float)$fRow->hpp;
                    }
                }
            }
        }

        return $arrResult;
    }
}

/**
 * 3-Way Matching Verification Helper untuk Modul Master Project
 *
 * Memverifikasi integritas 3 pilar:
 * 1. Kontrak Header Project ($headerKontrak / harga_jual_project)
 * 2. Total Rincian Jual Barang ($totalRincian / sum(harga_non_ppn * jml))
 * 3. HPP Realisasi & Laba Kotor ($totalHpp / sum(material + biaya), expected profit = $headerKontrak - $totalHpp)
 *
 * @param int|string $produkProjectPid ID Produk Project di tabel project_produk / project_komposisi_workoder
 * @param float $headerKontrak Nilai Kontrak Header Excl. PPN
 * @param float|null $reportedProfit Nilai laba kotor yang dilaporkan sistem (opsional)
 * @param float $tolerance Batas toleransi selisih pembulatan (default 1)
 * @return array
 */
if (!function_exists('verify_project_3way_matching')) {
    function verify_project_3way_matching($produkProjectPid, $headerKontrak = 0, $reportedProfit = null, $tolerance = 1) {
        $CI = &get_instance();
        $result = array(
            "is_valid" => true,
            "mode" => "PASS",
            "errors" => array(),
            "warnings" => array(),
            "data" => array(
                "header_kontrak" => (float)$headerKontrak,
                "total_rincian_jual" => 0,
                "total_budget" => 0,
                "total_hpp_realisasi" => 0,
                "calculated_profit" => 0,
                "reported_profit" => $reportedProfit !== null ? (float)$reportedProfit : null,
                "selisih_kontrak_vs_rincian" => 0,
                "selisih_profit" => 0,
            ),
        );

        if (empty($produkProjectPid)) {
            $result["is_valid"] = false;
            $result["errors"][] = "ID Produk Project tidak valid atau kosong.";
            return $result;
        }

        $calculatedJual = 0;
        $calculatedBudget = 0;
        $calculatedHpp = 0;

        // 1. Ambil data komposisi sub workorder dari database
        $CI->load->model("Mdls/MdlProjectKomposisiWorkorderSub");
        $kfs = new MdlProjectKomposisiWorkorderSub();
        $kfs->setFilters(array());
        $kfs->addFilter("produk_id='$produkProjectPid'");
        $kfs->addFilter("trash='0'");
        $kfs->addFilter("status='1'");
        $kfs->addFilter("jenis_transaksi='5582'");
        $CI->db->order_by("id", "ASC");
        $tempSub = $kfs->lookUpAll()->result();

        // 2. Ambil data rincian biaya RAB jika ada
        $CI->load->model("Mdls/MdlProjectKomponenBiayaDetailsRab");
        $byrab = new MdlProjectKomponenBiayaDetailsRab();
        $byrab->addFilter("project_id='$produkProjectPid'");
        $arrTmpByRab = $byrab->lookUpAll()->result();
        $arrByDetailsRab = array();
        if (!empty($arrTmpByRab)) {
            foreach ($arrTmpByRab as $byData) {
                $by_id = $byData->biaya_id;
                $by_dsr_id = $byData->biaya_dasar_id;
                $arrByDetailsRab[$by_id][$by_dsr_id] = (array)$byData;
                $arrByDetailsRab[$by_id][$by_dsr_id]["subtotal"] = $byData->jml * $byData->harga;
            }
        }

        // 3. Batch lookup HPP riil untuk item material
        $batchProdukIds = array();
        if (!empty($tempSub)) {
            foreach ($tempSub as $subItem) {
                if ($subItem->jenis == "produk" && isset($subItem->produk_dasar_id) && $subItem->produk_dasar_id > 0) {
                    $batchProdukIds[] = $subItem->produk_dasar_id;
                }
            }
        }
        $batchHppMap = array();
        if (!empty($batchProdukIds)) {
            $batchProdukIds = array_unique($batchProdukIds);
            if (function_exists('getBatchHppProdukProject')) {
                $batchHppMap = getBatchHppProdukProject($batchProdukIds);
            }
        }

        if (!empty($tempSub)) {
            foreach ($tempSub as $subItem) {
                $jml = isset($subItem->jml) ? (float)$subItem->jml : 0;
                $harga = isset($subItem->harga) ? (float)$subItem->harga : 0;

                if ($subItem->jenis == "produk") {
                    $liveHppSub = isset($batchHppMap[$subItem->produk_dasar_id]) ? $batchHppMap[$subItem->produk_dasar_id] : 0;
                    $hppItem = $liveHppSub > 0 ? $liveHppSub : ((isset($subItem->hrg_hpp) && $subItem->hrg_hpp * 1 > 0) ? (float)$subItem->hrg_hpp : ((isset($subItem->nilai) && $subItem->nilai * 1 > 0) ? (float)$subItem->nilai : 0));
                    $budgetSingle = (isset($subItem->harga_budget) && (float)$subItem->harga_budget > 0) ? (float)$subItem->harga_budget : $hppItem;

                    $calculatedJual += ($jml * $harga);
                    $calculatedBudget += ($jml * $budgetSingle);
                    $calculatedHpp += ($jml * $hppItem);
                }
                elseif ($subItem->jenis == "biaya") {
                    $hppBiayaSingle = 0;
                    if (isset($arrByDetailsRab[$subItem->produk_dasar_id]) && count($arrByDetailsRab[$subItem->produk_dasar_id]) > 0) {
                        $subHppRab = 0;
                        foreach ($arrByDetailsRab[$subItem->produk_dasar_id] as $rVal) {
                            $subHppRab += $rVal['subtotal'];
                        }
                        $hppBiayaSingle = ($jml > 0) ? ($subHppRab / $jml) : 0;
                    }
                    else {
                        $hppBiayaSingle = isset($subItem->hrg_hpp) ? (float)$subItem->hrg_hpp : 0;
                    }

                    $budgetBiayaSingle = (isset($subItem->harga_budget) && (float)$subItem->harga_budget > 0) ? (float)$subItem->harga_budget : $hppBiayaSingle;

                    $calculatedJual += ($jml * $harga);
                    $calculatedBudget += ($jml * $budgetBiayaSingle);
                    $calculatedHpp += ($jml * $hppBiayaSingle);
                }
            }
        }

        $calculatedProfit = $calculatedJual - $calculatedHpp;

        $result["data"]["total_rincian_jual"] = $calculatedJual;
        $result["data"]["total_budget"] = $calculatedBudget;
        $result["data"]["total_hpp_realisasi"] = $calculatedHpp;
        $result["data"]["calculated_profit"] = $calculatedProfit;

        // Pilar 1 vs Pilar 2: Kontrak Header vs Total Rincian Jual
        if ($headerKontrak > 0) {
            $selisihKontrak = abs($headerKontrak - $calculatedJual);
            $result["data"]["selisih_kontrak_vs_rincian"] = $selisihKontrak;
            if ($selisihKontrak > $tolerance) {
                $result["is_valid"] = false;
                $result["errors"][] = "MISMATCH Pilar 1 & 2: Nilai Kontrak Header (" . number_format($headerKontrak) . ") berbeda dengan Total Rincian Jual (" . number_format($calculatedJual) . "), selisih: " . number_format($selisihKontrak) . ".";
            }
        }

        // Pilar 3: Verifikasi Laba Kotor (Profit Integrity Gate)
        if ($reportedProfit !== null) {
            $selisihProfit = abs($reportedProfit - $calculatedProfit);
            $result["data"]["selisih_profit"] = $selisihProfit;
            if ($selisihProfit > $tolerance) {
                $result["is_valid"] = false;
                $result["errors"][] = "MISMATCH Pilar 3: Nilai Estimasi Profit (" . number_format($reportedProfit) . ") berbeda dengan hasil kalkulasi independen (" . number_format($calculatedProfit) . "), selisih: " . number_format($selisihProfit) . ".";
            }
        }

        return $result;
    }
}

// END OF COMPLETE REPEATED LOGIC
?>
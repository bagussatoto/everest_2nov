<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper Finansial Global & Pembulatan Presisi (ISO 20022 / ISO 80000-1)
 * SOT Status Pembayaran Transaksi (transaksi_payment_source)
 * Kompatibel 100% dengan PHP 5.6 dan CodeIgniter 3.1.8
 */

// START OF COMPLETE REPEATED LOGIC
if (!function_exists('fin_sub')) {
    /**
     * Pengurangan string desimal presisi tinggi (BC Math)
     * Mengeliminasi floating-point precision bug pada PHP
     */
    function fin_sub($val1, $val2, $precision = 4)
    {
        $v1 = isset($val1) && strlen(trim($val1)) > 0 ? strval($val1) : "0";
        $v2 = isset($val2) && strlen(trim($val2)) > 0 ? strval($val2) : "0";
        return bcsub($v1, $v2, intval($precision));
    }
}

if (!function_exists('fin_add')) {
    /**
     * Penambahan string desimal presisi tinggi (BC Math)
     */
    function fin_add($val1, $val2, $precision = 4)
    {
        $v1 = isset($val1) && strlen(trim($val1)) > 0 ? strval($val1) : "0";
        $v2 = isset($val2) && strlen(trim($val2)) > 0 ? strval($val2) : "0";
        return bcadd($v1, $v2, intval($precision));
    }
}

if (!function_exists('fin_mul')) {
    /**
     * Perkalian string desimal presisi tinggi (BC Math)
     */
    function fin_mul($val1, $val2, $precision = 4)
    {
        $v1 = isset($val1) && strlen(trim($val1)) > 0 ? strval($val1) : "0";
        $v2 = isset($val2) && strlen(trim($val2)) > 0 ? strval($val2) : "0";
        return bcmul($v1, $v2, intval($precision));
    }
}

if (!function_exists('fin_div')) {
    /**
     * Pembagian string desimal presisi tinggi (BC Math)
     */
    function fin_div($val1, $val2, $precision = 4)
    {
        $v1 = isset($val1) && strlen(trim($val1)) > 0 ? strval($val1) : "0";
        $v2 = isset($val2) && strlen(trim($val2)) > 0 ? strval($val2) : "0";
        if (bccomp($v2, "0", intval($precision)) == 0) {
            return "0";
        }
        return bcdiv($v1, $v2, intval($precision));
    }
}

if (!function_exists('fin_is_zero')) {
    /**
     * Pengecekan sisa nol mutlak (Epsilon Comparison)
     * Mengembalikan true jika nilai bernilai 0 (bebas dari sisa desimal mikro 0.000000000001)
     */
    function fin_is_zero($val, $precision = 4)
    {
        $v = isset($val) && strlen(trim($val)) > 0 ? strval($val) : "0";
        $clean = bcsub($v, "0", intval($precision));
        return bccomp($clean, "0", intval($precision)) == 0;
    }
}

if (!function_exists('fin_round')) {
    /**
     * Pembulatan Akuntansi Perbankan (Bankers Rounding ISO 80000-1)
     */
    function fin_round($val, $precision = 0)
    {
        $v = isset($val) ? floatval($val) : 0.0;
        return round($v, intval($precision), PHP_ROUND_HALF_EVEN);
    }
}

// START OF COMPLETE REPEATED LOGIC
if (!function_exists('fin_evaluate_payment_status')) {
    /**
     * Evaluasi Status Pembayaran Berdasarkan Single Source of Truth (transaksi_payment_source)
     * Kompatibel PHP 5.6
     *
     * @param array $paymentData Data pembayaran (hasil query transaksi_payment_source atau summary array)
     * @param float $tolerance Toleransi selisih rupiah pelunasan (default 1000.0)
     * @return array
     */
    function fin_evaluate_payment_status($paymentData, $tolerance = 1000.0)
    {
        $totTagihan = 0.0;
        $totTerbayar = 0.0;
        $totReturned = 0.0;
        $totSisa = 0.0;
        $hasRecord = false;
        $hasUm = false;
        $details = array();

        if (is_array($paymentData) && sizeof($paymentData) > 0) {
            if (isset($paymentData['tagihan']) || isset($paymentData['terbayar']) || isset($paymentData['sisa'])) {
                $tag = isset($paymentData['tagihan']) ? floatval($paymentData['tagihan']) : 0.0;
                $ter = isset($paymentData['terbayar']) ? floatval($paymentData['terbayar']) : 0.0;
                $ret = isset($paymentData['returned']) ? floatval($paymentData['returned']) : 0.0;
                $sis = isset($paymentData['sisa']) ? floatval($paymentData['sisa']) : ($tag - $ter - $ret);
                $jns = isset($paymentData['jenis']) ? strval($paymentData['jenis']) : "";
                if ($tag > 0 || $ter > 0 || $ret > 0) {
                    $hasRecord = true;
                    $totTagihan = $tag;
                    $totTerbayar = $ter;
                    $totReturned = $ret;
                    $totSisa = $sis;
                    if ($jns === '4467') {
                        $hasUm = true;
                    }
                }
            } else {
                foreach ($paymentData as $item) {
                    $tag = 0.0;
                    $ter = 0.0;
                    $ret = 0.0;
                    $sis = 0.0;
                    $trID = 0;
                    $itemJenis = "";
                    if (is_object($item)) {
                        $trID = isset($item->transaksi_id) ? intval($item->transaksi_id) : 0;
                        $tag = isset($item->tagihan) ? floatval($item->tagihan) : 0.0;
                        $ter = isset($item->terbayar) ? floatval($item->terbayar) : 0.0;
                        $ret = isset($item->returned) ? floatval($item->returned) : 0.0;
                        $sis = isset($item->sisa) ? floatval($item->sisa) : ($tag - $ter - $ret);
                        $itemJenis = isset($item->jenis) ? strval($item->jenis) : "";
                    } elseif (is_array($item)) {
                        $trID = isset($item['transaksi_id']) ? intval($item['transaksi_id']) : 0;
                        $tag = isset($item['tagihan']) ? floatval($item['tagihan']) : 0.0;
                        $ter = isset($item['terbayar']) ? floatval($item['terbayar']) : 0.0;
                        $ret = isset($item['returned']) ? floatval($item['returned']) : 0.0;
                        $sis = isset($item['sisa']) ? floatval($item['sisa']) : ($tag - $ter - $ret);
                        $itemJenis = isset($item['jenis']) ? strval($item['jenis']) : "";
                    }

                    if ($tag > 0 || $ter > 0 || $ret > 0) {
                        $hasRecord = true;
                        $totTagihan += $tag;
                        $totTerbayar += $ter;
                        $totReturned += $ret;
                        $totSisa += $sis;
                        if ($itemJenis === '4467') {
                            $hasUm = true;
                        }
                        if ($trID > 0) {
                            $details[$trID] = array(
                                "tagihan"  => $tag,
                                "terbayar" => $ter,
                                "returned" => $ret,
                                "sisa"     => $sis,
                            );
                        }
                    }
                }
            }
        }

        $status = "BELUM_LUNAS";
        $badgeSm = "<span class='label label-default' style='font-size:11px; color:#555;'>🟡 BELUM LUNAS</span>";
        $badgeLg = "<span class='label label-warning' style='font-size:12px; margin-left:10px; color:#8a6d3b; background-color:#fcf8e3; border:1px solid #faebcc;'><i class='fa fa-clock-o'></i> BELUM MENERIMA PELUNASAN</span>";

        if ($hasRecord && ($totTagihan > 0 || $totTerbayar > 0)) {
            $tol = floatval($tolerance);
            if ($totTerbayar > 0.0 && $totTagihan > 0.0 && ($totSisa <= 0.0 || $totSisa <= $tol)) {
                $status = "LUNAS";
                $badgeSm = "<span class='label label-success' style='font-size:11px;'>🟢 LUNAS</span>";
                $badgeLg = "<span class='label label-success' style='font-size:12px; margin-left:10px;'><i class='fa fa-check-circle'></i> LUNAS</span>";
            } elseif ($totTerbayar > 0.0 && $totTagihan > 0.0) {
                $status = "CICILAN";
                $terbayarFmt = number_format($totTerbayar, 0, ',', '.');
                $sisaFmt = number_format($totSisa, 0, ',', '.');
                $badgeSm = "<span class='label label-warning' style='font-size:11px;'>🟠 CICILAN</span>";
                $badgeLg = "<span class='label label-warning' style='font-size:12px; margin-left:10px; color:#ffffff; background-color:#f0ad4e; border:1px solid #eea236;'><i class='fa fa-hourglass-half'></i> PELUNASAN SEBAGIAN (Terbayar: Rp " . $terbayarFmt . " / Sisa: Rp " . $sisaFmt . ")</span>";
            } elseif ($totTerbayar > 0.0 && $totTagihan == 0.0) {
                $status = "DP";
                $terbayarFmt = number_format($totTerbayar, 0, ',', '.');
                $badgeSm = "<span class='label label-warning' style='font-size:11px; background-color:#f0ad4e;'>🟠 DP DITERIMA</span>";
                $badgeLg = "<span class='label label-warning' style='font-size:12px; margin-left:10px; color:#ffffff; background-color:#f0ad4e; border:1px solid #eea236;'><i class='fa fa-check-circle-o'></i> UANG MUKA / DP DITERIMA (Rp " . $terbayarFmt . ")</span>";
            } elseif ($totReturned >= $totTagihan || ($totReturned > 0.0 && $totSisa <= 0.0)) {
                $status = "BATAL";
                $badgeSm = "<span class='label label-default' style='font-size:11px; color:#777; background-color:#e6e6e6;'>⚪ BATAL/RETUR</span>";
                $badgeLg = "<span class='label label-default' style='font-size:12px; margin-left:10px; color:#555; background-color:#eee; border:1px solid #ccc;'><i class='fa fa-ban'></i> FAKTUR DIBATALKAN / DIRETUR PENUH</span>";
            }
        }

        return array(
            "status"      => $status,
            "is_lunas"    => ($status === "LUNAS"),
            "is_cicilan"  => ($status === "CICILAN"),
            "is_dp"       => ($status === "DP"),
            "is_batal"    => ($status === "BATAL"),
            "tagihan"     => $totTagihan,
            "terbayar"    => $totTerbayar,
            "returned"    => $totReturned,
            "sisa"        => $totSisa,
            "has_record"  => $hasRecord,
            "has_um"      => $hasUm,
            "badge"       => $badgeSm,
            "badge_sm"    => $badgeSm,
            "badge_lg"    => $badgeLg,
            "details"     => $details,
        );
    }
}

if (!function_exists('fin_get_payment_status')) {
    /**
     * Ambil dan hitung status pembayaran transaksi dari tabel transaksi_payment_source
     * Kompatibel PHP 5.6 dan CodeIgniter 3.1.8
     *
     * @param int|string|array $transaksiIDs ID transaksi tunggal atau array ID
     * @param float $tolerance Batas toleransi rupiah pembulatan pelunasan (default 1000.0)
     * @param bool $autoResolveChain Otomatis lacak ID transaksi satu rantai (id_top/id_master) jika true
     * @return array
     */
    function fin_get_payment_status($transaksiIDs, $tolerance = 1000.0, $autoResolveChain = true)
    {
        $cleanIDs = array();
        if (is_array($transaksiIDs)) {
            foreach ($transaksiIDs as $idVal) {
                $idInt = intval($idVal);
                if ($idInt > 0 && !in_array($idInt, $cleanIDs)) {
                    $cleanIDs[] = $idInt;
                }
            }
        } elseif (is_string($transaksiIDs) && strpos($transaksiIDs, ',') !== false) {
            $ex = explode(',', $transaksiIDs);
            foreach ($ex as $idVal) {
                $idInt = intval(trim($idVal));
                if ($idInt > 0 && !in_array($idInt, $cleanIDs)) {
                    $cleanIDs[] = $idInt;
                }
            }
        } else {
            $idInt = intval($transaksiIDs);
            if ($idInt > 0) {
                $cleanIDs[] = $idInt;
            }
        }

        if (sizeof($cleanIDs) == 0) {
            return fin_evaluate_payment_status(array(), $tolerance);
        }

        $CI =& get_instance();

        // Lacak seluruh rantai transaksi jika diminta
        if ($autoResolveChain) {
            $CI->db->select("id, id_top, id_master");
            $CI->db->group_start();
            $CI->db->where_in("id", $cleanIDs);
            $CI->db->or_where_in("id_top", $cleanIDs);
            $CI->db->group_end();
            $CI->db->where("trash", 0);
            $CI->db->where("trash_4", 0);
            $qChain = $CI->db->get("transaksi")->result();
            $topIDs = array();
            if (sizeof($qChain) > 0) {
                foreach ($qChain as $cRow) {
                    $cID = intval($cRow->id);
                    $cTop = intval($cRow->id_top);
                    if ($cID > 0 && !in_array($cID, $cleanIDs)) {
                        $cleanIDs[] = $cID;
                    }
                    if ($cTop > 0 && !in_array($cTop, $topIDs)) {
                        $topIDs[] = $cTop;
                    }
                }
            }

            if (sizeof($topIDs) > 0) {
                $CI->db->select("id");
                $CI->db->where_in("id_top", $topIDs);
                $CI->db->where("trash", 0);
                $CI->db->where("trash_4", 0);
                $qSiblings = $CI->db->get("transaksi")->result();
                if (sizeof($qSiblings) > 0) {
                    foreach ($qSiblings as $sRow) {
                        $sID = intval($sRow->id);
                        if ($sID > 0 && !in_array($sID, $cleanIDs)) {
                            $cleanIDs[] = $sID;
                        }
                    }
                }
            }

            // Jika rantai transaksi memiliki dokumen aktif (trash = 0 AND trash_4 = 0),
            // pastikan ID transaksi yang berstatus reject/batal (trash_4 = 1) disaring keluar
            // agar data retur dari faktur lama yang mati tidak mencemari kartu piutang transaksi aktif
            if (sizeof($cleanIDs) > 0) {
                $CI->db->select("id");
                $CI->db->where_in("id", $cleanIDs);
                $CI->db->where("trash", 0);
                $CI->db->where("trash_4", 0);
                $qActiveCheck = $CI->db->get("transaksi")->result();
                if (sizeof($qActiveCheck) > 0) {
                    $CI->db->select("id");
                    $CI->db->where_in("id", $cleanIDs);
                    $CI->db->group_start();
                    $CI->db->where("trash !=", 0);
                    $CI->db->or_where("trash_4 !=", 0);
                    $CI->db->group_end();
                    $qDeadTrans = $CI->db->get("transaksi")->result();
                    if (sizeof($qDeadTrans) > 0) {
                        $deadIDs = array();
                        foreach ($qDeadTrans as $dRow) {
                            $deadIDs[] = intval($dRow->id);
                        }
                        $filteredCleanIDs = array();
                        foreach ($cleanIDs as $cID) {
                            if (!in_array($cID, $deadIDs)) {
                                $filteredCleanIDs[] = $cID;
                            }
                        }
                        $cleanIDs = $filteredCleanIDs;
                    }
                }
            }
        }

        // Ambil data transaksi_payment_source
        if (sizeof($cleanIDs) == 0) {
            return fin_evaluate_payment_status(array(), $tolerance);
        }

        $CI->db->select("transaksi_id, tagihan, terbayar, returned, sisa, jenis, extern2_id");
        $CI->db->where_in("transaksi_id", $cleanIDs);
        $CI->db->where("dihapus", 0);
        $tmpPay = $CI->db->get("transaksi_payment_source")->result();

        // Lacak Uang Muka Konsumen (jenis 4467) yang terikat pada nomor SO/rantai transaksi (extern2_id)
        $CI->db->select("transaksi_id, tagihan, terbayar, returned, sisa, jenis, extern2_id");
        $CI->db->where_in("extern2_id", $cleanIDs);
        $CI->db->where("jenis", "4467");
        $CI->db->where("dihapus", 0);
        $tmpUm = $CI->db->get("transaksi_payment_source")->result();

        if (sizeof($tmpPay) == 0 && sizeof($tmpUm) > 0) {
            $tmpPay = $tmpUm;
        }

        return fin_evaluate_payment_status($tmpPay, $tolerance);
    }
}

if (!function_exists('fin_get_batch_payment_summary')) {
    /**
     * Ambil summary data pembayaran secara batch untuk banyak ID transaksi sekaligus
     * Digunakan untuk optimasi performa pada halaman tabel/history agar terhindar dari N+1 query
     * Mendukung data invoice normal (transaksi_id) dan Uang Muka Konsumen 4467 (extern2_id)
     * Kompatibel PHP 5.6
     *
     * @param array $transaksiIDs Kumpulan seluruh ID transaksi (SO, SPD, Inv, dll)
     * @return array Array berindeks ID transaksi: [$id => array('tagihan'=>..., 'terbayar'=>..., 'returned'=>..., 'sisa'=>..., 'jenis'=>...)]
     */
    function fin_get_batch_payment_summary($transaksiIDs)
    {
        $summary = array();
        if (!is_array($transaksiIDs) || sizeof($transaksiIDs) == 0) {
            return $summary;
        }

        $cleanIDs = array();
        foreach ($transaksiIDs as $idVal) {
            $idInt = intval($idVal);
            if ($idInt > 0 && !in_array($idInt, $cleanIDs)) {
                $cleanIDs[] = $idInt;
            }
        }

        if (sizeof($cleanIDs) == 0) {
            return $summary;
        }

        $CI =& get_instance();

        // 1. Ambil data transaksi_payment_source normal berbasis transaksi_id
        $CI->db->select("transaksi_id, tagihan, terbayar, returned, sisa, jenis, extern2_id");
        $CI->db->where_in("transaksi_id", $cleanIDs);
        $CI->db->where("dihapus", 0);
        $tmpPay = $CI->db->get("transaksi_payment_source")->result();

        if (sizeof($tmpPay) > 0) {
            foreach ($tmpPay as $pRow) {
                $trID = intval($pRow->transaksi_id);
                if ($trID > 0) {
                    $tag = floatval($pRow->tagihan);
                    $ter = floatval($pRow->terbayar);
                    $ret = floatval($pRow->returned);
                    $sis = floatval($pRow->sisa);
                    $jns = isset($pRow->jenis) ? strval($pRow->jenis) : "";

                    if (!isset($summary[$trID])) {
                        $summary[$trID] = array(
                            "tagihan"  => $tag,
                            "terbayar" => $ter,
                            "returned" => $ret,
                            "sisa"     => $sis,
                            "jenis"    => $jns,
                        );
                    } else {
                        // Akumulasi jika satu transaksi_id memiliki beberapa baris pencatatan
                        $summary[$trID]['tagihan']  += $tag;
                        $summary[$trID]['terbayar'] += $ter;
                        $summary[$trID]['returned'] += $ret;
                        $summary[$trID]['sisa']     += $sis;
                    }
                }
            }
        }

        // 2. Lacak Uang Muka Konsumen (jenis 4467) yang terhubung via extern2_id (misal: ID Sales Order)
        $CI->db->select("transaksi_id, tagihan, terbayar, returned, sisa, jenis, extern2_id");
        $CI->db->where_in("extern2_id", $cleanIDs);
        $CI->db->where("jenis", "4467");
        $CI->db->where("dihapus", 0);
        $tmpUm = $CI->db->get("transaksi_payment_source")->result();

        if (sizeof($tmpUm) > 0) {
            foreach ($tmpUm as $uRow) {
                $targetID = intval($uRow->extern2_id);
                if ($targetID > 0) {
                    $tag = floatval($uRow->tagihan);
                    $ter = floatval($uRow->terbayar);
                    $ret = floatval($uRow->returned);
                    $sis = floatval($uRow->sisa);
                    $jns = isset($uRow->jenis) ? strval($uRow->jenis) : "4467";

                    // Prioritaskan rekaman invoice normal jika sudah ada tagihan aktif.
                    // Jika belum ada atau jika tagihan normal bernilai 0, gunakan rekaman Uang Muka.
                    if (!isset($summary[$targetID]) || $summary[$targetID]['tagihan'] == 0) {
                        if (!isset($summary[$targetID])) {
                            $summary[$targetID] = array(
                                "tagihan"  => $tag,
                                "terbayar" => $ter,
                                "returned" => $ret,
                                "sisa"     => $sis,
                                "jenis"    => $jns,
                            );
                        } else {
                            $summary[$targetID]['tagihan']  = $tag;
                            $summary[$targetID]['terbayar'] = $ter;
                            $summary[$targetID]['returned'] = $ret;
                            $summary[$targetID]['sisa']     = $sis;
                            $summary[$targetID]['jenis']    = $jns;
                        }
                    } else {
                        // Jika sudah ada rekaman Uang Muka lain pada targetID yang sama, akumulasikan
                        if (isset($summary[$targetID]['jenis']) && $summary[$targetID]['jenis'] === '4467') {
                            $summary[$targetID]['tagihan']  += $tag;
                            $summary[$targetID]['terbayar'] += $ter;
                            $summary[$targetID]['returned'] += $ret;
                            $summary[$targetID]['sisa']     += $sis;
                        }
                    }
                }
            }
        }

        return $summary;
    }
}
// END OF COMPLETE REPEATED LOGIC

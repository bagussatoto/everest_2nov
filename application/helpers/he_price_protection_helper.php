<?php
// START OF COMPLETE REPEATED LOGIC
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper Proteksi & Notifikasi Margin Jual vs Harga Beli (HPP)
 * Versi 6 - 3-Tier Fallback Engine + Dukungan Produk Paket (Roll-Up Komposisi)
 * Tier 1: Master price (MdlHargaProduk2 / jenis_value = 'hpp')
 * Tier 2: Persediaan fifo_avg (MdlFifoAverage / persediaan cabang & barang konversi)
 * Tier 3: Buku Pembantu Persediaan (_rek_pembantu_produk_cache / periode='forever')
 * Kompatibel PHP 5.6 & CodeIgniter 3 HMVC Everest
 */

if (!function_exists('pp_get_item_hpp')) {
    /**
     * Mengambil nilai HPP produk dengan 3-Tier Fallback Engine serta dukungan Produk Paket:
     * - Langkah 0: Deteksi Paket / Komposisi (produk_komposisi -> roll-up komponen fisik + jasa hpp=0)
     * - Tier 1: Master price (MdlHargaProduk2 / jenis_value = 'hpp')
     * - Tier 2: Persediaan fifo_avg (MdlFifoAverage / barang konversi/persediaan cabang)
     * - Tier 3: Buku Pembantu persediaan (_rek_pembantu_produk_cache / periode='forever')
     *
     * @param int|string $produk_id
     * @param int|string $cabang_id
     * @param int|string|null $gudang_id
     * @param string $sourceMode 'auto'|'master_only'|'fifo_only'|'ledger_only'|'single_item'
     * @return array array('hpp' => float, 'source' => string, 'is_package' => bool, 'components' => array)
     */
    function pp_get_item_hpp($produk_id, $cabang_id, $gudang_id = null, $sourceMode = 'auto')
    {
        static $cachedHpp = array();

        // Penanganan kompatibilitas jika parameter ke-3 dilewati sebagai mode string (misal: 'auto')
        if (is_string($gudang_id) && !is_numeric($gudang_id)) {
            $sourceMode = $gudang_id;
            $gudang_id = null;
        }

        $cabang_id = !empty($cabang_id) ? $cabang_id : (function_exists('my_cabang_id') ? my_cabang_id() : 1);
        $gudang_id_key = !empty($gudang_id) ? $gudang_id : '0';
        $cacheKey = $cabang_id . '_' . $gudang_id_key . '_' . $produk_id . '_' . $sourceMode;

        if (isset($cachedHpp[$cacheKey])) {
            return $cachedHpp[$cacheKey];
        }

        $ci =& get_instance();
        $result = array(
            'hpp'        => 0.0,
            'source'     => 'none',
            'is_package' => false,
            'components' => array(),
        );

        if (empty($produk_id)) {
            $cachedHpp[$cacheKey] = $result;
            return $result;
        }

        // ----------------------------------------------------------------------------------
        // LANGKAH 0: Deteksi Produk Paket (Roll-Up Komposisi Barang & Jasa)
        // Dijalankan jika sourceMode bukan 'single_item' (mencegah rekursi tanpa batas)
        // ----------------------------------------------------------------------------------
        if ($sourceMode !== 'single_item' && $sourceMode !== 'master_only' && $sourceMode !== 'fifo_only' && $sourceMode !== 'ledger_only') {
            $ci->load->model('Mdls/MdlProdukPaketKomposisi');
            $mdlKomposisi = new MdlProdukPaketKomposisi();
            $queryKomposisi = $mdlKomposisi->lookupByPID($produk_id);

            if ($queryKomposisi && $queryKomposisi->num_rows() > 0) {
                $komponenRows = $queryKomposisi->result();
                $totalHppPaket = 0.0;
                $arrComponents = array();
                $ci->load->model('Mdls/MdlProduk');
                $mdlProd = new MdlProduk();

                foreach ($komponenRows as $kRow) {
                    $childId = isset($kRow->produk_dasar_id) ? $kRow->produk_dasar_id : 0;
                    $ratioQty = isset($kRow->jml) ? (float)$kRow->jml : 1.0;

                    // Periksa identitas master produk komponen untuk membedakan barang fisik vs jasa
                    $childJenis = 'produk';
                    $childNama = 'Komponen #' . $childId;
                    if ($childId > 0) {
                        $qChildProd = $mdlProd->lookupByID($childId);
                        if ($qChildProd && $qChildProd->num_rows() > 0) {
                            $rChild = $qChildProd->row();
                            $childJenis = isset($rChild->jenis) ? $rChild->jenis : 'produk';
                            $childNama = isset($rChild->nama) ? $rChild->nama : $childNama;
                        }
                    }

                    // Komponen bertipe 'jasa' modal HPP = 0.0
                    $childHpp = 0.0;
                    $childSource = 'service_zero_cost';
                    if (strtolower(trim($childJenis)) === 'jasa') {
                        $childHpp = 0.0;
                        $childSource = 'service_zero_cost';
                    } else {
                        // Komponen barang fisik: cari HPP satuan melalui engine 3-tier
                        $childHppData = pp_get_item_hpp($childId, $cabang_id, $gudang_id, 'single_item');
                        $childHpp = (float)$childHppData['hpp'];
                        $childSource = $childHppData['source'];
                    }

                    $subtotalHppKomponen = $childHpp * $ratioQty;
                    $totalHppPaket += $subtotalHppKomponen;

                    $arrComponents[] = array(
                        'produk_id'    => $childId,
                        'nama'         => $childNama,
                        'jenis'        => $childJenis,
                        'qty'          => $ratioQty,
                        'hpp_satuan'   => $childHpp,
                        'hpp_subtotal' => $subtotalHppKomponen,
                        'source'       => $childSource,
                    );
                }

                $result['hpp'] = $totalHppPaket;
                $result['source'] = 'package_composition';
                $result['is_package'] = true;
                $result['components'] = $arrComponents;

                $cachedHpp[$cacheKey] = $result;
                return $result;
            }
        }

        // ----------------------------------------------------------------------------------
        // TIER 1: Master Table `price` (Purchase Price dari Supplier)
        // ----------------------------------------------------------------------------------
        if ($sourceMode === 'auto' || $sourceMode === 'single_item' || $sourceMode === 'master_only') {
            $ci->load->model('Mdls/MdlHargaProduk2');
            $mdlPrice = new MdlHargaProduk2();
            $mdlPrice->addFilter("produk_id='" . $ci->db->escape_str($produk_id) . "'");
            $mdlPrice->addFilter("status='1'");
            $mdlPrice->addFilter("trash='0'");
            $mdlPrice->addFilter("jenis_value='hpp'");

            $cabangPusat = defined('CB_ID_PUSAT') ? CB_ID_PUSAT : 1;
            $mdlPrice->addFilter("cabang_id in ('" . $ci->db->escape_str($cabang_id) . "', '" . $ci->db->escape_str($cabangPusat) . "')");

            $queryPrice = $mdlPrice->lookupAll();
            if ($queryPrice && $queryPrice->num_rows() > 0) {
                $rowsPrice = $queryPrice->result();
                $foundPrice = 0.0;
                foreach ($rowsPrice as $rp) {
                    if (isset($rp->nilai) && (float)$rp->nilai > 0) {
                        $foundPrice = (float)$rp->nilai;
                        if ((int)$rp->cabang_id === (int)$cabang_id) {
                            break;
                        }
                    }
                }
                if ($foundPrice > 0) {
                    $result['hpp'] = $foundPrice;
                    $result['source'] = 'master_purchase';
                    $cachedHpp[$cacheKey] = $result;
                    return $result;
                }
            }
        }

        // ----------------------------------------------------------------------------------
        // TIER 2: Tabel Persediaan `fifo_avg` (HPP Riil Barang Hasil Konversi / Persediaan Cabang)
        // ----------------------------------------------------------------------------------
        if ($sourceMode === 'auto' || $sourceMode === 'single_item' || $sourceMode === 'fifo_only') {
            $ci->load->model('Mdls/MdlFifoAverage');
            $mdlFifo = new MdlFifoAverage();
            $mdlFifo->addFilter("produk_id='" . $ci->db->escape_str($produk_id) . "'");
            $mdlFifo->addFilter("cabang_id='" . $ci->db->escape_str($cabang_id) . "'");

            $queryFifo = $mdlFifo->lookupAll();
            if ($queryFifo && $queryFifo->num_rows() > 0) {
                $rowFifo = $queryFifo->row();
                $hppFifo = isset($rowFifo->hpp) ? (float)$rowFifo->hpp : 0.0;
                if ($hppFifo <= 0 && isset($rowFifo->jml_nilai) && isset($rowFifo->jml) && (float)$rowFifo->jml > 0) {
                    $hppFifo = (float)$rowFifo->jml_nilai / (float)$rowFifo->jml;
                }
                if ($hppFifo > 0) {
                    $result['hpp'] = $hppFifo;
                    $result['source'] = 'conversion_fifo';
                    $cachedHpp[$cacheKey] = $result;
                    return $result;
                }
            }
        }

        // ----------------------------------------------------------------------------------
        // TIER 3: Buku Pembantu Persediaan `_rek_pembantu_produk_cache` (Periode='forever')
        // Parameter: periode='forever', extern_id, cabang_id, gudang_id
        // ----------------------------------------------------------------------------------
        if ($sourceMode === 'auto' || $sourceMode === 'single_item' || $sourceMode === 'ledger_only') {
            if ($ci->db->table_exists('_rek_pembantu_produk_cache')) {
                // Percobaan 1: Menggunakan gudang_id spesifik jika tersedia
                $ci->db->select('c1.cabang_id, c1.gudang_id, c1.harga, c1.harga_avg, c1.saldo_qty_debet, c1.saldo_qty_kredit, c1.saldo_debet, c1.saldo_kredit');
                $ci->db->from('_rek_pembantu_produk_cache c1');
                $ci->db->where('c1.extern_id', $ci->db->escape_str($produk_id));
                $ci->db->where('c1.periode', 'forever');
                $ci->db->where('c1.cabang_id', $ci->db->escape_str($cabang_id));

                if (!empty($gudang_id)) {
                    $ci->db->where('c1.gudang_id', $ci->db->escape_str($gudang_id));
                }

                $ci->db->order_by('c1.id', 'DESC');
                $ci->db->limit(1);
                $queryLedger = $ci->db->get();

                // Percobaan 2: Fallback ke level cabang jika pencarian dengan gudang_id spesifik tidak menemukan hasil
                if ((!$queryLedger || $queryLedger->num_rows() === 0) && !empty($gudang_id)) {
                    $ci->db->select('c1.cabang_id, c1.gudang_id, c1.harga, c1.harga_avg, c1.saldo_qty_debet, c1.saldo_qty_kredit, c1.saldo_debet, c1.saldo_kredit');
                    $ci->db->from('_rek_pembantu_produk_cache c1');
                    $ci->db->where('c1.extern_id', $ci->db->escape_str($produk_id));
                    $ci->db->where('c1.periode', 'forever');
                    $ci->db->where('c1.cabang_id', $ci->db->escape_str($cabang_id));
                    $ci->db->order_by('c1.id', 'DESC');
                    $ci->db->limit(1);
                    $queryLedger = $ci->db->get();
                }

                if ($queryLedger && $queryLedger->num_rows() > 0) {
                    $rowLedger = $queryLedger->row();
                    $hppLedger = 0.0;

                    // 1. Prioritas utama: harga_avg (Moving Average Cost)
                    if (isset($rowLedger->harga_avg) && (float)$rowLedger->harga_avg > 0) {
                        $hppLedger = (float)$rowLedger->harga_avg;
                    }
                    // 2. Prioritas kedua: harga transaksi terakhir
                    elseif (isset($rowLedger->harga) && (float)$rowLedger->harga > 0) {
                        $hppLedger = (float)$rowLedger->harga;
                    }
                    // 3. Prioritas ketiga: kalkulasi rasio saldo debet / saldo qty debet
                    elseif (isset($rowLedger->saldo_debet) && isset($rowLedger->saldo_qty_debet) && (float)$rowLedger->saldo_qty_debet > 0 && (float)$rowLedger->saldo_debet > 0) {
                        $hppLedger = (float)$rowLedger->saldo_debet / (float)$rowLedger->saldo_qty_debet;
                    }

                    if ($hppLedger > 0) {
                        $result['hpp'] = $hppLedger;
                        $result['source'] = 'ledger_cache_forever';
                        $cachedHpp[$cacheKey] = $result;
                        return $result;
                    }
                }
            }
        }

        $cachedHpp[$cacheKey] = $result;
        return $result;
    }
}

if (!function_exists('pp_check_item_margin')) {
    /**
     * Memeriksa margin harga satuan bersih produk vs HPP
     *
     * @param int|string $produk_id
     * @param float $harga_jual
     * @param float $diskon_nominal
     * @param float $qty
     * @param int|string $cabang_id
     * @param int|string|null $gudang_id
     * @param string $sourceMode
     * @return array
     */
    function pp_check_item_margin($produk_id, $harga_jual, $diskon_nominal, $qty, $cabang_id, $gudang_id = null, $sourceMode = 'auto')
    {
        // Penanganan kompatibilitas jika parameter ke-6 dilewati sebagai string mode (misal: 'auto')
        if (is_string($gudang_id) && !is_numeric($gudang_id)) {
            $sourceMode = $gudang_id;
            $gudang_id = null;
        }

        $harga_jual = is_string($harga_jual) ? (float)str_replace(',', '', $harga_jual) : (float)$harga_jual;
        $diskon_nominal = is_string($diskon_nominal) ? (float)str_replace(',', '', $diskon_nominal) : (float)$diskon_nominal;
        $qty = (float)$qty > 0 ? (float)$qty : 1.0;

        $hppData = pp_get_item_hpp($produk_id, $cabang_id, $gudang_id, $sourceMode);
        $hpp = (float)$hppData['hpp'];

        $nettSatuan = $harga_jual - $diskon_nominal;
        $marginUnit = $nettSatuan - $hpp;

        $isLoss = false;
        $totalLoss = 0.0;
        $lossPercent = 0.0;

        // Hanya dianggap rugi jika HPP > 0 dan Nett Satuan < HPP
        if ($hpp > 0 && $marginUnit < 0) {
            $isLoss = true;
            $totalLoss = abs($marginUnit) * $qty;
            $lossPercent = round((abs($marginUnit) / $hpp) * 100, 2);
        }

        return array(
            'is_loss'       => $isLoss,
            'hpp'           => $hpp,
            'hpp_source'    => $hppData['source'],
            'is_package'    => isset($hppData['is_package']) ? $hppData['is_package'] : false,
            'components'    => isset($hppData['components']) ? $hppData['components'] : array(),
            'harga_kotor'   => $harga_jual,
            'diskon'        => $diskon_nominal,
            'nett'          => $nettSatuan,
            'dpp_per_unit'  => $nettSatuan,
            'margin_unit'   => $marginUnit,
            'loss_per_unit' => $isLoss ? abs($marginUnit) : 0.0,
            'total_loss'    => $totalLoss,
            'loss_percent'  => $lossPercent,
        );
    }
}

if (!function_exists('pp_check_nota_margin')) {
    /**
     * Memeriksa margin total transaksi nota vs total akumulasi HPP
     * Mendukung evaluasi defisit total nota maupun subsidi silang (cross-subsidy)
     * Serta menangani produk paket dan pencegahan pencatatan ganda
     *
     * @param array $items Array items transaksi (mengandung produk_id, qty, harga, disc)
     * @param float $diskon_faktur_global
     * @param int|string $cabang_id
     * @param int|string|null $gudang_id
     * @param string $sourceMode
     * @return array
     */
    function pp_check_nota_margin($items, $diskon_faktur_global = 0.0, $cabang_id = null, $gudang_id = null, $sourceMode = 'auto')
    {
        // Penanganan kompatibilitas jika parameter ke-4 dilewati sebagai string mode (misal: 'auto')
        if (is_string($gudang_id) && !is_numeric($gudang_id)) {
            $sourceMode = $gudang_id;
            $gudang_id = null;
        }

        if ($cabang_id === null && function_exists('my_cabang_id')) {
            $cabang_id = my_cabang_id();
        }
        if ($gudang_id === null && function_exists('my_gudang_id')) {
            $gudang_id = my_gudang_id();
        }

        $diskon_faktur_global = (float)$diskon_faktur_global;
        $totalNettItem = 0.0;
        $totalHppNota = 0.0;
        $lossItems = array();

        if (is_array($items) && sizeof($items) > 0) {
            foreach ($items as $pId => $item) {
                if (empty($item)) {
                    continue;
                }
                $id = isset($item['id']) ? $item['id'] : (isset($item['produk_id']) ? $item['produk_id'] : $pId);
                $qty = isset($item['qty']) ? (float)$item['qty'] : (isset($item['jml']) ? (float)$item['jml'] : (isset($item['valid_qty']) ? (float)$item['valid_qty'] : 1.0));
                $harga = isset($item['harga']) ? (float)$item['harga'] : (isset($item['produk_ord_hrg']) ? (float)$item['produk_ord_hrg'] : 0.0);
                $disc = isset($item['disc']) ? (float)$item['disc'] : 0.0;

                $itemCheck = pp_check_item_margin($id, $harga, $disc, $qty, $cabang_id, $gudang_id, $sourceMode);
                $lineNett = $itemCheck['nett'] * $qty;
                $lineHpp = $itemCheck['hpp'] * $qty;

                $totalNettItem += $lineNett;
                $totalHppNota += $lineHpp;

                if ($itemCheck['is_loss']) {
                    $lossItems[] = array(
                        'produk_id'        => $id,
                        'produk_nama'      => isset($item['name']) ? $item['name'] : (isset($item['nama']) ? $item['nama'] : (isset($item['produk_nama']) ? $item['produk_nama'] : 'Item #' . $id)),
                        'produk_kode'      => isset($item['code']) ? $item['code'] : (isset($item['kode']) ? $item['kode'] : (isset($item['produk_kode']) ? $item['produk_kode'] : '')),
                        'qty'              => $qty,
                        'harga_kotor'      => $harga,
                        'diskon'           => $disc,
                        'nett'             => $itemCheck['nett'],
                        'harga_jual_netto' => $itemCheck['nett'],
                        'hpp'              => $itemCheck['hpp'],
                        'hpp_source'       => $itemCheck['hpp_source'],
                        'source'           => $itemCheck['hpp_source'],
                        'is_package'       => $itemCheck['is_package'],
                        'components'       => $itemCheck['components'],
                        'margin_unit'      => $itemCheck['margin_unit'],
                        'selisih'          => abs($itemCheck['margin_unit']),
                        'total_loss'       => $itemCheck['total_loss'],
                        'loss_percent'     => $itemCheck['loss_percent'],
                    );
                }
            }
        }

        // Total penjualan bersih nota setelah diskon faktur global
        $totalNettNota = $totalNettItem - $diskon_faktur_global;
        $marginNota = $totalNettNota - $totalHppNota;
        $deficitNota = 0.0;
        $totalProfitNota = 0.0;
        $profitPercent = 0.0;
        $isLossNota = false;

        // Evaluasi margin nota
        if ($totalHppNota > 0) {
            if ($totalNettNota < $totalHppNota) {
                $isLossNota = true;
                $deficitNota = $totalHppNota - $totalNettNota;
            } else {
                $totalProfitNota = $marginNota;
                $profitPercent = round(($marginNota / $totalHppNota) * 100, 2);
            }
        }

        return array(
            'is_loss_nota'         => $isLossNota,
            'total_nett_item'      => $totalNettItem,
            'diskon_faktur_global' => $diskon_faktur_global,
            'total_nett_nota'      => $totalNettNota,
            'total_hpp_nota'       => $totalHppNota,
            'margin_nota'          => $marginNota,
            'deficit_nota'         => $deficitNota,
            'total_profit_nota'    => $totalProfitNota,
            'profit_percent'       => $profitPercent,
            'loss_items_count'     => sizeof($lossItems),
            'loss_items_detail'    => $lossItems,
        );
    }
}
// END OF COMPLETE REPEATED LOGIC

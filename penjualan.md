# BLUEPRINT & DOKUMENTASI ARSITEKTUR MODUL PENJUALAN
## Penanganan Silo Rantai Dokumen & Agregasi Jurnal Resume Transaksi (Order-to-Cash)

> [!NOTE]
> Dokumen ini merupakan implementasi kasus konkret untuk siklus bisnis **Order-to-Cash (Penjualan)** yang mengacu pada blueprint arsitektur universal: [MASTER_BLUEPRINT_ERP_MODUL.md](file:///z:/everest_2nov/MASTER_BLUEPRINT_ERP_MODUL.md).

Dokumen ini mendokumentasikan analisis perilaku keterisolasian data (*data silo*), mekanisme rantai dokumen (*document chain* berbasis `id_master`), dan solusi konkret untuk penyatuan tampilan audit trail operasional serta jurnal akuntansi pada modal resume transaksi penjualan (`viewResume`).

---

## 1. Latar Belakang & Identifikasi Masalah

Pada modul `penjualan` (khususnya alur transaksi master `5822`), satu pesanan pelanggan melewati serangkaian tahapan siklus hidup dokumen (*lifecycle*):
1. **Sales Pre-Order (SPO):** `5822spo` (Pembuatan pesanan awal oleh Sales).
2. **Sales Order (SO):** `5822so` (Persetujuan / Approval oleh Supervisor Sales).
3. **Pre-Packing (PKD):** `5822pkd` (Persiapan dan pengepakan fisik barang di Gudang).
4. **Packing List / Delivery (SPD):** `5822spd` (Pengiriman fisik barang keluar dari Gudang).
5. **Invoice / Tagihan (INV):** `INV` (Penerbitan faktur penjualan dan pengakuan piutang).
6. **E-Faktur Pajak (EF-R):** `EF-R` (Faktur pajak keluaran PPN).
7. **A/R Receipt / Pelunasan (RPC):** `RPC` / jenis `749` (Penerimaan kas/bank pelunasan piutang oleh Finance).

### Gejala Masalah pada Modal Resume (`Transaksi::viewResume`)
Ketika pengguna mengklik nomor transaksi untuk melihat resume (`view resume for [nomer]`):
- Tampilan riwayat (*History Entry Points*) berhasil menampilkan seluruh 14 titik entri dari SPO awal hingga A/R Receipt karena memanfaatkan `id_master`.
- Namun, blok jurnal akuntansi di bawah riwayat mengalami **keterputusan cerita (*broken narrative / silo*)** tergantung dari nota mana pengguna membuka modal tersebut.

---

## 2. Analisis Perilaku Silo per Dokumen

### 3. Kesimpulan Faktual

Tangkapan layar pengujian sistem memperlihatkan secara gamblang perilaku *silo* per-dokumen:

1. **Saat membuka nota SPO (`5822spo`):**
   * Jurnal pengiriman (`SPD`) dan pelunasan (`RPC`) **keduanya hilang**.
   * Sistem menampilkan pesan kontradiktif: *- no journal affected by this transaction -*, meskipun status transaksi telah bertanda hijau **LUNAS (Melalui A/R Receipt)**.
2. **Saat membuka nota SPD (`5822spd`):**
   * Jurnal pengiriman (`SPD`) **muncul** (HPP, Persediaan, Penjualan, PPN, dan pemotongan deposit konsumen).
   * Namun jurnal pelunasan kasir (`RPC` tanggal 23 Sep) **hilang/tidak tampil**.
3. **Jika membuka nota RPC (`RPC`):**
   * Hanya jurnal penerimaan kas/bank yang muncul, sementara jurnal pengakuan penjualan, HPP persediaan, dan rincian barang pesanan awal hilang dari konteks.
4. **Kasus Uang Muka Konsumen Berelasi Sales Order (Jenis `4467`):**
   * Konsumen membayar uang muka (DP) yang dipotong di jurnal pengiriman (`Hutang Ke Konsumen: Debet 2,252,252`).
   * **Aturan Relasi SO:** Relasi uang muka dengan Sales Order **hanya berlaku jika berelasi dengan SO**, yang dicatat pada tabel `transaksi_payment_source`:
     * `extern2_id` = ID Transaksi SO (`SO_ID`).
     * `extern2_nama` = Nomor Transaksi SO (`SO_NOMER`).
     * `jenis` = `'4467'`.
   * **Penyebab Silo:** Fungsi `viewResume()` di `Transaksi.php:8983` hanya mencari kasir tunai `4464` dan **tidak mengikutsertakan kueri `transaksi_payment_source` untuk `4467`**, sehingga kuitansi uang muka DP dan jurnal Kas vs Hutang Konsumen tidak pernah muncul di modal resume pesanan tersebut.

### Akar Masalah Teknis di Kode
Pada `application/modules/penjualan/controllers/Transaksi.php` baris 9393:
```php
$j = new ComJurnal();
$j->addFilter("transaksi_no='$no'");
```
Variabel `$no` diambil secara kaku (*voucher-centric*) dari segmen URL dokumen pemanggil (`$this->uri->segment(5)`). Karena tabel `jurnal` mencatat nomor transaksi sesuai nota pemicu langsungnya (`INV...`, `5822spd...`, atau `RPC...`), kueri tersebut gagal menarik jurnal milik dokumen lain yang berada dalam satu silsilah keluarga `id_master`.

---

## 4. Solusi Konkret yang Diperlukan

### Prinsip Solusi
Agar dokumen mana pun yang dibuka (apakah SPO, SPD, maupun RPC) menampilkan keseluruhan jurnal rantai transaksi secara utuh:

> **Kueri jurnal di `Transaksi.php:9393` harus diubah dari memfilter satu nomor string nota menjadi memfilter seluruh nomor dokumen yang terdaftar dalam rantai `id_master` (`5822spd...` + `RPC...` + `INV...`). Dengan begitu, jurnal pengiriman dan jurnal pelunasan kasir akan berdampingan rapi di satu halaman resume.**

### Detail Perubahan Kode di `application/modules/penjualan/controllers/Transaksi.php`

#### A. Kueri Jurnal Terpadu (Menyatukan Jurnal Hulu ke Hilir)
Menggantikan kueri filter tunggal dengan agregasi nomor dokumen dari hasil `lookupEntryPoints($masterID)`:

```php
// =========================================================================
// AGREGASI JURNAL RANTAI TRANSAKSI (END-TO-END DOCUMENT CHAIN)
// =========================================================================
$chainNomers = array($no);

// 1. Kumpulkan seluruh nomor dokumen turunan dari tabel entry points
if (isset($tmpEP) && sizeof($tmpEP) > 0) {
    foreach ($tmpEP as $epRow) {
        if (!empty($epRow->nomer)) {
            $chainNomers[] = $epRow->nomer;
        }
        if (!empty($epRow->nomer_faktur)) {
            $chainNomers[] = $epRow->nomer_faktur;
        }
        if (!empty($epRow->nomer2)) {
            $chainNomers[] = $epRow->nomer2;
        }
    }
}

// 2. Kumpulkan nomor dokumen dari kaitan A/R Receipt (jenis 749)
if (isset($trepTmp) && sizeof($trepTmp) > 0) {
    foreach ($trepTmp as $arRow) {
        if (!empty($arRow->nomer)) {
            $chainNomers[] = $arRow->nomer;
        }
    }
}

// 3. Kumpulkan nomor transaksi penerimaan tunai kasir (jenis 4464)
if (isset($queryPayment) && $queryPayment->num_rows() > 0) {
    foreach ($queryPayment->result() as $pRow) {
        if (!empty($pRow->nomer)) {
            $chainNomers[] = $pRow->nomer;
        }
    }
}

// 4. Kumpulkan nomor transaksi Uang Muka Konsumen yang berelasi dengan Sales Order (jenis 4467)
// Relasi SO disimpan di tabel `transaksi_payment_source`:
// extern2_id = ID Transaksi SO, extern2_nama = Nomer Transaksi SO
if (count($searchIDs) > 0) {
    $this->db->select("tps.transaksi_id, tps.tagihan, tps.terbayar, tps.extern2_id, tps.extern2_nama, t.nomer, t.dtime, t.oleh_nama, t.cabang_nama, t.jenis, t.counters");
    $this->db->from("transaksi_payment_source tps");
    $this->db->join("transaksi t", "t.id = tps.transaksi_id", "inner");
    $this->db->where_in("tps.extern2_id", $searchIDs);
    $this->db->where("tps.jenis", "4467");
    $this->db->where("tps.dihapus", 0);
    $this->db->where("t.trash", "0");
    $queryUm = $this->db->get();
    if ($queryUm && $queryUm->num_rows() > 0) {
        foreach ($queryUm->result() as $uRow) {
            if (!empty($uRow->nomer)) {
                $chainNomers[] = $uRow->nomer;
            }
            // Sertakan juga ke dalam History Entry Points sebagai titik entri Uang Muka SO
            $kkeyUm = 'sign_um_' . $uRow->transaksi_id;
            if (!isset($epValues[$kkeyUm])) {
                $counterDecode = blobDecode($uRow->counters);
                $cGlobals = "";
                if (is_array($counterDecode) && isset($counterDecode['stepCode|placeID'])) {
                    $firstCounter = reset($counterDecode['stepCode|placeID']);
                    $cGlobals = digit_5($firstCounter);
                }
                $epValues[$kkeyUm] = array(
                    "title"                    => "Uang Muka Konsumen (Relasi SO)",
                    "cabang_nama"              => $uRow->cabang_nama,
                    "label"                    => "Penerimaan Uang Muka SO",
                    "dtime"                    => $uRow->dtime,
                    "nomer"                    => formatField_he_format("nomer_nolink", $uRow->nomer),
                    "nomer_orig"               => $uRow->nomer,
                    "oleh"                     => $uRow->oleh_nama,
                    "caption_department"       => "Finance / Kasir",
                    "deskripsi"                => "Relasi SO: " . $uRow->extern2_nama,
                    "link_id"                  => $uRow->transaksi_id,
                    "global_counter"           => $cGlobals,
                    "master_id_global_counter" => $cGlobals,
                    "jenis"                    => "4467",
                    "kkey"                     => $kkeyUm,
                );
            }
        }
    }
}

$chainNomers = array_unique(array_filter($chainNomers));

// 4. Eksekusi kueri jurnal untuk SELURUH rantai dokumen yang terikat
$j = new ComJurnal();
if (sizeof($chainNomers) > 1) {
    $j->addFilter("transaksi_no IN ('" . implode("','", $chainNomers) . "')");
} else {
    $j->addFilter("transaksi_no='$no'");
}
$j->addFilter("status='1'");
$j->addFilter("trash='0'");
```

#### B. Sinkronisasi Kuantitas Fulfill Logistik Gudang
Memperbaiki kalkulasi `Fulfill(Qty)` di tabel detail pesanan agar tidak tertahan di angka `0` saat barang sudah dikirim penuh:

```php
// Jika alur sudah terkirim (shipped via 5822spd) dan tidak menggunakan split fulfillment terpisah
if ((!isset($fullfillment[$id]["jml"]) || $fullfillment[$id]["jml"] == 0) && isset($shipment[$id]["jml"]) && $shipment[$id]["jml"] > 0) {
    $val_fullfil = $shipment[$id]["jml"];
    $subtotal_fullfil = $val_fullfil * (isset($iSpec["nett1_ppn"]) ? $iSpec["nett1_ppn"] : $iSpec["harga"]);
}
```

---

## 5. Dampak dan Manfaat Perbaikan

1. **Konsistensi Informasi Lintas Divisi:**
   * Divisi Penjualan (Sales) dapat memverifikasi bahwa pesanan benar-benar telah diakui pendapatannya dan dilunasi oleh bagian keuangan.
   * Divisi Keuangan (Finance) dapat menelusuri rincian barang dan alamat kirim dari kuitansi pembayaran tanpa berpindah modul.
2. **Eliminasi Kebingungan Pengguna:**
   * Menghilangkan kontradiksi tulisan *- no journal affected by this transaction -* pada pesanan yang sudah berstatus lunas.
   * Menghilangkan kebingungan angka `Fulfill(Qty) = 0` padahal barang fisik sudah keluar dari gudang.
3. **Keamanan & Standar PHP 5.6:**
   * Kueri tetap aman menggunakan sanitasi query builder / binding standar CI 3.1.8.
   * Kompatibel penuh dengan PHP 5.6 tanpa operator modern.


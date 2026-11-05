# MASTER BLUEPRINT ARSITEKTUR & PROTOKOL REVIEW MODUL EVEREST ERP
## Standar Universal Rekonsiliasi Rantai Dokumen, Buku Besar Terpadu, dan Aliran Material (Graph-Centric Paradigm)

Dokumen ini merupakan panduan arsitektur induk (*universal master blueprint*) untuk seluruh modul transaksi di lingkungan sistem Everest ERP (CodeIgniter 3.1.8 HMVC, PHP 5.6). Blueprint ini menetapkan standar arsitektur, pola relasi data, mekanisme agregasi jurnal, dan protokol audit teknis guna menjamin tidak adanya keterisolasian data (*data silo*) di modul mana pun.

---

## 1. Perbedaan Fundamental Paradigma: Voucher-Centric vs ERP Graph-Centric

| Parameter | Sistem Non-ERP / Silo (*Voucher-Centric*) | Everest ERP Terpadu (*Graph-Centric*) |
| :--- | :--- | :--- |
| **Pusat Identitas (Anchor)** | Terikat pada nomor string nota individual (`transaksi_no = '$no'`). | Terikat pada akar rantai dokumen yang universal: **`id_master`**. |
| **Visibilitas Antar-Divisi** | Setiap divisi (Sales, Gudang, Finance, Tax) hanya melihat dokumen miliknya sendiri. | Seluruh divisi dapat melihat pohon silsilah transaksi (*transaction tree*) dari hulu hingga hilir. |
| **Audit Jurnal Akuntansi** | Jurnal terpecah per voucher. Membuka pesanan menampilkan: *- no journal affected -*. | Seluruh jurnal akuntansi dari komitmen awal, mutasi persediaan, pengakuan pendapatan/beban, hingga kas pelunasan diagregasikan secara utuh. |
| **Relasi Pembayaran & Pajak** | Uang muka (DP) dan e-Faktur terpisah di tabel tersendiri tanpa tautan balik. | Menggunakan tabel alokasi finansial (`transaksi_payment_source`) dan jembatan modul otomatis (`connectTo`). |

---

## 2. Tiga Pilar Universal Integritas Modul ERP

Setiap modul transaksi wajib dibangun dan diaudit berdasarkan 3 pilar integritas:

```
                          [ id_master ]
                   UNIVERSAL ANCHOR TRANSAKSI
                                │
        ┌───────────────────────┼───────────────────────┐
        ▼                       ▼                       ▼
   [ PILAR 1 ]             [ PILAR 2 ]             [ PILAR 3 ]
 Intelektual Rantai      Buku Besar Terpadu     Rekonsiliasi Kuantitas
 Dokumen Operasional    (Unified Accounting)      & Aliran Material
(Audit Trail Narrative)   (Multi-Doc Journals)   (Fulfillment Balance)
```

### PILAR 1: Integritas Rantai Dokumen Operasional (*Audit Trail Narrative*)
Satu siklus transaksi bisnis tidak boleh terputus, dari pembuatan draf pertama hingga rekonsiliasi akhir.

1. **Propagasi `id_master`:**
   * Dokumen pertama dalam rantai transaksi (Step 1, misal: *Pre-Order* atau *Purchase Request*) menginisiasi `id_master = id`.
   * Seluruh dokumen turunan di step-step berikutnya **WAJIB** menyalin `id_master` dokumen akar tersebut.
2. **Penanganan Percabangan Finansial Luar Alur (Uang Muka / DP):**
   * Transaksi Uang Muka (misal DP Penjualan `4467` atau DP Pembelian `4460`) yang dibayar sebelum atau bersamaan dengan pesanan harus dicatat pada tabel `transaksi_payment_source`:
     * `extern2_id` = ID transaksi dokumen induk (misal: ID Sales Order / Purchase Order).
     * `extern2_nama` = Nomor transaksi dokumen induk.
     * `jenis` = Kode jenis uang muka (misal: `'4467'`, `'4460'`).
3. **Penanganan Jembatan Antar-Modul (*Cross-Module Bridges*):**
   * Integrasi otomatis ke modul lain (seperti pembuatan prapajak ke modul `taxes` atau pengeluaran logistik) dikonfigurasi via atribut `connectTo` pada `coTransaksiUi.php`.
   * Field pelacak seperti `ids_his` (blob), `id_top`, dan `nomer_top` wajib dijaga konsistensinya.

---

### PILAR 2: Integritas Buku Besar Terpadu (*Unified Accounting & General Ledger*)
Audit trail keuangan harus memperlihatkan seluruh jurnal yang dipicu oleh rantai transaksi, tanpa memandang dokumen mana yang sedang dibuka oleh pengguna di modal resume (`Transaksi::viewResume`).

#### Pola Anti-Pattern (Silo / Rusak):
```php
// JANGAN DIGUNAKAN: Hanya menarik jurnal milik voucher yang diklik
$j = new ComJurnal();
$j->addFilter("transaksi_no='$no'");
```
*Dampak Buruk:* Saat pengguna membuka nota PO atau SO, sistem menampilkan pesan keliru: *- no journal affected by this transaction -*, padahal barang sudah dikirim dan pembayaran sudah lunas di bagian kasir.

#### Pola Universal ERP (Wajib Diimplementasikan):
Kueri jurnal wajib mengumpulkan seluruh nomor nota sah di dalam rantai `id_master` sebelum memanggil `ComJurnal`:
```php
// START OF COMPLETE REPEATED LOGIC
// Kumpulkan nomor dokumen dari seluruh titik entri rantai id_master
$chainNomers = array($no);

// 1. Dari tabel titik entri (entry points / riwayat tahapan)
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

// 2. Dari relasi pelunasan / A/R / A/P Receipt
if (isset($arrSettlement) && sizeof($arrSettlement) > 0) {
    foreach ($arrSettlement as $sRow) {
        if (!empty($sRow->nomer)) {
            $chainNomers[] = $sRow->nomer;
        }
    }
}

// 3. Dari relasi Uang Muka (transaksi_payment_source)
if (count($searchIDs) > 0) {
    $this->db->select("t.nomer");
    $this->db->from("transaksi_payment_source tps");
    $this->db->join("transaksi t", "t.id = tps.transaksi_id", "inner");
    $this->db->where_in("tps.extern2_id", $searchIDs);
    $this->db->where("tps.dihapus", 0);
    $this->db->where("t.trash", "0");
    $queryPaymentSource = $this->db->get();
    if ($queryPaymentSource && $queryPaymentSource->num_rows() > 0) {
        foreach ($queryPaymentSource->result() as $psRow) {
            if (!empty($psRow->nomer)) {
                $chainNomers[] = $psRow->nomer;
            }
        }
    }
}

$chainNomers = array_unique(array_filter($chainNomers));

// 4. Eksekusi Kueri Jurnal Agregasi
$j = new ComJurnal();
if (sizeof($chainNomers) > 1) {
    $j->addFilter("transaksi_no IN ('" . implode("','", $chainNomers) . "')");
} else {
    $j->addFilter("transaksi_no='$no'");
}
$j->addFilter("status='1'");
$j->addFilter("trash='0'");
// END OF COMPLETE REPEATED LOGIC
```

---

### PILAR 3: Rekonsiliasi Kuantitas & Aliran Fisik (*Material & Fulfillment Reconciliation*)
Pada modul-modul yang melibatkan pergerakan fisik barang (Inventory, Sales, Purchase, Distribution, Production), kuantitas barang wajib memenuhi persamaan matematis rekonsiliasi:

$$\text{Ordered Qty} \ge \text{Approved Qty} \ge \text{Fulfilled / Received Qty} \ge \text{Invoiced Qty}$$

* **Aturan Tampilan UI Fulfillment:**
  Jika dokumen pengiriman fisik (`SPD` / `Delivery Note`) atau penerimaan fisik (`GRN` / `Penerimaan`) telah sah dan kuantitas valid terbit, status kuantitas `Fulfill(Qty)` atau `Received(Qty)` pada tampilan resume **DILARANG** tertahan di angka `0`.
* **Formula Penyesuaian:**
  ```php
  if ((!isset($fullfillment[$id]["jml"]) || $fullfillment[$id]["jml"] == 0) && isset($shipment[$id]["jml"]) && $shipment[$id]["jml"] > 0) {
      $val_fullfil = $shipment[$id]["jml"];
      $subtotal_fullfil = $val_fullfil * (isset($iSpec["nett1_ppn"]) ? $iSpec["nett1_ppn"] : $iSpec["harga"]);
  }
  ```

---

## 3. Matriks Arsitektur Lintas Modul Everest ERP

Tabel referensi pemetaan parameter rantai dokumen untuk modul-modul di Everest:

| Siklus Bisnis | Modul HMVC | Kode Master | Urutan Dokumen Hulu $\rightarrow$ Hilir | Titik Rekonsiliasi Finansial |
| :--- | :--- | :--- | :--- | :--- |
| **Order-to-Cash (Penjualan)** | `penjualan` | `5822`, `5823`, `588` | SPO $\rightarrow$ SO $\rightarrow$ PKD $\rightarrow$ SPD $\rightarrow$ INV $\rightarrow$ RPC | DP SO via `transaksi_payment_source` (`jenis=4467`), Kasir Tunai `4464`, A/R `749`, Pajak `connectTo=110` |
| **Procure-to-Pay (Pembelian)** | `pembelian` | `466`, `460`, `1466` | PR $\rightarrow$ PO $\rightarrow$ Pre-GRN $\rightarrow$ GRN $\rightarrow$ Bill $\rightarrow$ Payment | DP PO via `transaksi_payment_source` (`jenis=4460`), A/P Payment `759` / `4447`, Realisasi PPN `112`/`113` |
| **Inter-Branch Transfer (Mutasi)** | `distribusifg` | `5844`, `5855`, `5833` | Request $\rightarrow$ Approval $\rightarrow$ Scan/Pack $\rightarrow$ In-Transit $\rightarrow$ Receipt | Persediaan Asal vs Persediaan In-Transit vs Persediaan Tujuan (via `ComLockerStockDualWrite`) |
| **Plan-to-Produce (Produksi)** | `produksi` | `3675`, `3676` | SPK $\rightarrow$ Permintaan Bahan $\rightarrow$ Pengeluaran BOM $\rightarrow$ FG Receipt | Persediaan Bahan Baku $\rightarrow$ WIP (Barang Dalam Proses) $\rightarrow$ Persediaan Barang Jadi (FG) |
| **Tax Management (Pajak)** | `taxes` | `110`, `110r`, `110e` | Prepare Faktur (`110r`) $\rightarrow$ Entry e-Faktur (`110e`) $\rightarrow$ Otorisasi (`110`) | Menerima inject data otomatis via `connectTo=110` dari modul Penjualan & Pembelian |
| **Expense & Kasir (Biaya Operasional)** | `pembayaran` / kasir | `671`, `2677`, `477` | Pengajuan Biaya $\rightarrow$ Approval $\rightarrow$ Kas Keluar $\rightarrow$ Settlement | Rekening Pembantu Kas & Buku Besar Biaya |
| **Fixed Asset (Aset & Depresiasi)** | `aset` | `8787`, `8788`, `8789` | Registrasi Aset $\rightarrow$ Request Depresiasi $\rightarrow$ Otorisasi Jurnal Depresiasi | Biaya Penyusutan vs Akumulasi Penyusutan Aset |

---

## 4. Protokol & Checklist Audit Review Modul (10 Poin Standar)

Gunakan daftar periksa berikut setiap kali melakukan code review, refactoring, atau pengujian modul baru di Everest ERP:

| No | Parameter Pemeriksaan | Target Verifikasi Teknis | Status |
| :---: | :--- | :--- | :---: |
| **1** | **Preservasi `id_master`** | Seluruh baris transaksi turunan menyimpan `id_master` transaksi root pertama secara konsisten. | [ ] |
| **2** | **Kueri Jurnal Terpadu (`viewResume`)** | Kueri jurnal menggunakan agregasi array `$chainNomers` (`transaksi_no IN (...)`), bukan single string (`transaksi_no='$no'`). | [ ] |
| **3** | **Alokasi Uang Muka / DP** | Transaksi DP yang terikat di `transaksi_payment_source` ditarik dan ditampilkan di Entry Points & Jurnal Resume. | [ ] |
| **4** | **Rekonsiliasi Kuantitas Fisik** | Kuantitas `Fulfill(Qty)` / `Received(Qty)` selaras dengan nota fisik pengiriman/penerimaan riil. | [ ] |
| **5** | **Early Validation Pattern** | Validasi URL segment, session, locking transaksi, dan validitas barang (`valid_qty > 0`) di awal fungsi sebelum transaksi DB. | [ ] |
| **6** | **Dual-Write Stock Locker** | Modifikasi persediaan gudang wajib melewati model `ComLockerStockDualWrite`, bukan update langsung ke 1 tabel. | [ ] |
| **7** | **Locker Transaksi Ganda** | Pengecekan `MdlLockerTransaksi` (`state='hold'`) diterapkan untuk mencegah race condition antar-petugas. | [ ] |
| **8** | **Preservasi Sesi `main_elements`** | Penyalinan elemen form (`main_elements`) dijaga saat transisi step di `FollowUp.php`. | [ ] |
| **9** | **Target Modul Formatter** | Pemanggilan formatter link (`formatField_he_format`) mengoper `$modul` dan `$jenisTr` yang tepat agar modal resume tidak salah sasaran. | [ ] |
| **10** | **Kompatibilitas PHP 5.6 & Query Binding** | Kode bebas dari operator PHP 7+ (`??`, `[]`, arrow function) dan semua query menggunakan query builder/binding parameter. | [ ] |

---

## 5. Implementasi Helper Universal (Standard Reusable Snippets)

### A. Helper Pengumpul Dokumen Rantai Transaksi
Dapat ditempatkan pada `application/helpers/he_transaksi_helper.php` untuk digunakan oleh seluruh controller modul:

```php
// START OF COMPLETE REPEATED LOGIC
if (!function_exists('he_collect_chain_documents')) {
    /**
     * Mengumpulkan seluruh nomor transaksi yang berada dalam satu silsilah id_master,
     * termasuk dokumen pembayaran kasir, AR/AP receipt, dan uang muka terkait.
     *
     * @param string $directNomer Nomor nota aktif saat ini
     * @param array $tmpEP Hasil kueri MdlHistory / Entry Points
     * @param array $searchIDs Kumpulan ID transaksi dalam rantai (termasuk SO/PO/INV)
     * @param array $extraNomers Nomor dokumen tambahan dari relasi khusus
     * @return array Daftar unik nomor transaksi yang sah
     */
    function he_collect_chain_documents($directNomer, $tmpEP = array(), $searchIDs = array(), $extraNomers = array())
    {
        $ci =& get_instance();
        $chainNomers = array($directNomer);

        // 1. Ekstrak dari Entry Points
        if (is_array($tmpEP) && sizeof($tmpEP) > 0) {
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

        // 2. Ekstrak dari nomor ekstra yang di-pass
        if (is_array($extraNomers) && sizeof($extraNomers) > 0) {
            foreach ($extraNomers as $exNom) {
                if (!empty($exNom)) {
                    $chainNomers[] = $exNom;
                }
            }
        }

        // 3. Ekstrak dari transaksi_payment_source (Uang Muka SO/PO, dsb)
        if (is_array($searchIDs) && count($searchIDs) > 0) {
            $ci->db->select("t.nomer");
            $ci->db->from("transaksi_payment_source tps");
            $ci->db->join("transaksi t", "t.id = tps.transaksi_id", "inner");
            $ci->db->where_in("tps.extern2_id", $searchIDs);
            $ci->db->where("tps.dihapus", 0);
            $ci->db->where("t.trash", "0");
            $queryPs = $ci->db->get();
            if ($queryPs && $queryPs->num_rows() > 0) {
                foreach ($queryPs->result() as $psRow) {
                    if (!empty($psRow->nomer)) {
                        $chainNomers[] = $psRow->nomer;
                    }
                }
            }
        }

        return array_values(array_unique(array_filter($chainNomers)));
    }
}
// END OF COMPLETE REPEATED LOGIC
```

### B. Helper Resolusi Modul Berdasarkan Jenis Transaksi
Menentukan modul target yang sah saat membangun tautan link antar-modul:

```php
// START OF COMPLETE REPEATED LOGIC
if (!function_exists('he_resolve_modul_by_jenis')) {
    /**
     * Menentukan path modul HMVC yang tepat berdasarkan kode jenis transaksi
     *
     * @param string $jenisTr Kode transaksi (misal: '5822', '466', '110', '5844')
     * @return string Path URL modul (misal: 'penjualan/', 'pembelian/', 'taxes/')
     */
    function he_resolve_modul_by_jenis($jenisTr)
    {
        $ci =& get_instance();
        $ci->load->config("heTransaksi_ui");
        $allUi = $ci->config->item("heTransaksi_ui");

        // Ekstrak prefix jika berupa kode turunan (misal '5822spo' -> '5822')
        $cleanJenis = $jenisTr;
        if (isset($allUi[$cleanJenis]["modul"])) {
            return base_url() . $allUi[$cleanJenis]["modul"] . "/";
        }

        // Cari berdasarkan awalan numerik
        preg_match('/^\d+/', $jenisTr, $matches);
        if (!empty($matches[0]) && isset($allUi[$matches[0]]["modul"])) {
            return base_url() . $allUi[$matches[0]]["modul"] . "/";
        }

        return defined('MODUL_PATH') ? MODUL_PATH : base_url();
    }
}
// END OF COMPLETE REPEATED LOGIC
```

---

## 6. Referensi Terkait & Implementasi Konkret
* **Implementasi Modul Penjualan (Order-to-Cash):** [`penjualan.md`](file:///z:/everest_2nov/penjualan.md)
* **Aturan & Protokol Coding Workspace:** [`AGENTS.md`](file:///z:/everest_2nov/AGENTS.md)


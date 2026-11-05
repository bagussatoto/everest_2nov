# STANDAR OPERASIONAL PROSEDUR (SOP) PERBAIKAN & REFACTORING MODUL TRANSAKSI
## Universal End-to-End Troubleshooting, Dynamic Drill-Down, dan Audit Integritas Modul Everest ERP

> [!IMPORTANT]
> Dokumen ini adalah panduan operasional standar (**Universal & Adaptive Runbook**) yang wajib dipatuhi oleh seluruh developer dan AI Agent ketika melakukan audit, perbaikan bug (*troubleshooting*), atau *refactoring* pada modul transaksi apa pun di lingkungan Everest ERP (CodeIgniter 3.1.8 HMVC, PHP 5.6).
> 
> Dokumen ini melengkapi prinsip arsitektur konseptual pada [MASTER_BLUEPRINT_ERP_MODUL.md](file:///z:/everest_2nov/MASTER_BLUEPRINT_ERP_MODUL.md) dan aturan mutlak pada [AGENTS.md](file:///z:/everest_2nov/AGENTS.md).

---

## 1. Prinsip Dasar & Batasan Arsitektur yang Tak Boleh Ditawar (Immutable Invariants)

Meskipun logika bisnis, alur proses, dan regulasi transaksi bersifat dinamis, **pondasi arsitektur teknis berikut berstatus mutlak dan TIDAK BOLEH dikompromikan**:

1. **Kompatibilitas Sintaks PHP 5.6:**
   * DILARANG menggunakan operator modern PHP 7+: *null coalescing* (`??`), *short array syntax* (`[]`), *spaceship operator* (`<=>`), *spread operator* (`...`), *anonymous classes*, *return type declarations*, *scalar type hints*, atau *arrow functions* (`fn() => ...`).
   * Gunakan selalu: `isset($x) ? $x : $default`, `array()`, dan deklarasi fungsi standar `function($x) { ... }`.
2. **Keamanan Kueri (Zero Raw SQL Concatenation):**
   * Semua kueri wajib menggunakan **Query Builder CI3** (`$this->db->where_in()`, `$this->db->join()`) atau **Query Binding** (`$this->db->query($sql, $binds)`).
   * DILARANG menggunakan `implode("','", $array)` secara mentah di dalam string kueri tanpa sanitasi atau binding parameter.
3. **Integritas Pembukuan Berpasangan (Double-Entry Balance):**
   * Jurnal akuntansi yang dibentuk atau diagregasikan wajib selalu seimbang antara total **Debet** dan **Kredit**.
4. **Mutasi Fisik & Persediaan Gudang Terkunci:**
   * Perubahan stok fisik atau penahanan kuantitas (*hold*) WAJIB melalui model bisnis `ComLockerStockDualWrite`, bukan manipulasi manual langsung ke tabel stok.
5. **Kontrol Akses Bersamaan (Concurrency Control):**
   * Pengecekan dan penguncian transaksi aktif wajib melalui model `MdlLockerTransaksi` (`state='hold'`, `jumlah='1'`) guna mencegah *race condition* antar-petugas.
6. **Preservasi Identitas Silsilah (`id_master`) & Sesi Form (`main_elements`):**
   * Seluruh dokumen turunan wajib mewarisi `id_master` dari dokumen akar pertama.
   * Transisi antar-langkah di `FollowUp.php` wajib menyalin `main_elements` dari `$oldCode` ke `$cCode` agar konfigurasi bawaan (seperti gudang default) tidak lenyap.
7. **Sentralisasi Penyimpanan Draf Sementara:**
   * Penyimpanan draf transaksi multi-step disimpan ke database NoSQL (MongoDB), bukan tabel transaksional MySQL.

---

## 2. Alur Kerja Perbaikan 5 Fase (Universal Refactoring Workflow)

Setiap proses perbaikan modul transaksi wajib melalui 5 fase berurutan tanpa melompati tahapan:

```
┌─────────────────────────────────────────────────────────────────────────────┐
│ FASE 1: RECONNAISSANCE OTOMATIS (Code-Recon Mandiri Sebelum Bertanya)      │
│ Agen/Developer memindai berkas modul, 4 config HMVC, gateway, & skema DB    │
└──────────────────────────────────────┬──────────────────────────────────────┘
                                       │
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ FASE 2: INTERACTIVE DRILL-DOWN GATE (Wawancara Terarah Wajib "Drill Me")    │
│ Tanya Developer: Masalah, regulasi baru, topologi relasi, & dampak data lama│
└──────────────────────────────────────┬──────────────────────────────────────┘
                                       │
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ FASE 3: AUDIT & PERBAIKAN WRITE-PATH (Alur Eksekusi & Mutasi Database)     │
│ Fail-Fast, Transaksi Atomik DB, Lock Transaksi, Stock Locker, & Sesi Form   │
└──────────────────────────────────────┬──────────────────────────────────────┘
                                       │
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ FASE 4: AUDIT & PERBAIKAN READ-PATH (Resume Transaksi Terpadu `viewResume`) │
│ Agregasi Dokumen id_master, Uang Muka TPS, Kueri Jurnal Aman, & Kuantitas   │
└──────────────────────────────────────┬──────────────────────────────────────┘
                                       │
                                       ▼
┌─────────────────────────────────────────────────────────────────────────────┐
│ FASE 5: MATRIKS VERIFIKASI & UJI ASIMETRI (Multi-Perspective Smoke Test)    │
│ Pengujian resume dari hulu, tengah, hilir, data lama, dan skenario batal    │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## 3. Fase 1: Reconnaissance Otomatis (Code-Recon Mandiri)

Sebelum mengajukan pertanyaan kepada pengguna/developer, eksekutor wajib membedah anatomi modul terkait secara mandiri guna membangun konteks teknis awal.

### Checklist Pemindaian Mandiri:
1. **Periksa 4 Berkas Konfigurasi Modul (`application/modules/[modul]/config/`):**
   * `coTransaksiCore.php`: Identifikasi komponen bisnis (`Com*`), aturan FIFO persediaan, dan pemetaan gateway.
   * `coTransaksiUi.php`: Identifikasi seluruh tahapan langkah (`steps`), label UI, dan integrasi modul lain via atribut `connectTo`.
   * `coTransaksiLayout.php`: Identifikasi kolom tabel dan pemetaan rendering form.
   * `coTransaksiValues.php`: Identifikasi aturan kalkulasi nilai, diskon, dan perpajakan.
2. **Periksa Hirarki Controller (`application/modules/[modul]/controllers/`):**
   * `Modul_Controller.php`: Basis controller pengelola sesi dan MongoDB draft.
   * `_shoppingCart.php` / `_processSelect*.php`: Handler pemilihan barang dan penahanan stok (`hold`).
   * `Create.php`: Pembuatan dokumen awal (Step 1) dan inisiasi `id_master`.
   * `FollowUp.php`: Otorisasi, transisi antar-langkah, dan penyalinan sesi.
   * `Transaksi.php`: Penyajian daftar transaksi dan modal ringkasan (`viewResume`).
3. **Petakan Parameter Teknis Awal:**
   * Kode jenis master (`jenis_master`, misal: `5822`, `466`, `5844`).
   * Identifikasi tabel relasi eksternal (`transaksi_payment_source`, `transaksi_data`, `transaksi_histori`).

---

## 4. Fase 2: Interactive Drill-Down Gate (Protokol Wawancara Terarah)

> [!CAUTION]
> **DILARANG MENGUBAH KODE SEBELUM TAHAP INI DISELESAIKAN.**
> Eksekutor wajib memaparkan temuan dari Fase 1 dan mengajukan pertanyaan terarah berbasis hipotesis konkret (bukan pertanyaan terbuka tanpa dasar).

### 4 Pertanyaan Kunci Wajib (Interactive Questionnaire):

1. **Identifikasi Masalah & Latar Belakang Regulasi/Bisnis:**
   * *"Apa anomali spesifik yang terjadi pada modul ini? Apakah perbaikan ini dipicu oleh regulasi baru (misal perubahan tarif PPN, aturan e-Faktur, PPh), perubahan SOP fisik gudang, atau bug keterputusan data?"*
2. **Topologi Silsilah Rantai Dokumen:**
   * *"Apakah rantai transaksi modul ini bersifat linear 1-to-1 (misal 1 PO $\rightarrow$ 1 GRN $\rightarrow$ 1 Tagihan), atau mendukung konsolidasi N-to-N (misal 1 Tagihan untuk banyak PO, atau 1 PO dicicil banyak pengiriman/penerimaan)?"*
3. **Mekanisme Uang Muka & Pengakuan Finansial:**
   * *"Apakah modul ini melibatkan Uang Muka (DP)? Jika ya, apa kode jenis transaksinya (misal `4467` untuk konsumen, `4460` untuk supplier), bagaimana pemotongan saldo hutang/piutang dicatat di `transaksi_payment_source`, dan apakah terbit faktur pajak uang muka (`110r`/`110e`)?"*
4. **Aturan Rekonsiliasi Kuantitas Fisik vs Finansial:**
   * *"Bagaimana penanganan kuantitas fisik jika terjadi pengiriman bertahap (partial), kelebihan pemenuhan (over-delivery), atau retur barang? Apakah kuantitas fulfill/received dihitung dari dokumen logistik fisik atau dokumen penagihan?"*

### Validasi Dampak Data Historis (*Legacy Data Sanity Check*):
Sebelum melangkah ke Fase 3, pastikan perubahan yang dirancang tidak merusak pembacaan data transaksi masa lampau yang dibuat sebelum aturan baru berlaku.

---

## 5. Fase 3: Prosedur Perbaikan Write-Path (Alur Eksekusi & Mutasi Database)

Fase ini mengamankan seluruh jalur eksekusi penulisan data pada controller `Create.php`, `FollowUp.php`, dan `_processSelect*.php`.

### Checklist Langkah Write-Path:

#### 3.1 Pola Validasi Awal (*Early Validation Pattern - Fail Fast*)
Tempatkan validasi integritas di baris teratas fungsi sebelum menjalankan transaksi database:
```php
// START OF COMPLETE REPEATED LOGIC
// 1. Validasi parameter sesi & URL
if (empty($transaksiID) || empty($stepNumCurrent)) {
    redirect(MODUL_PATH . "Transaksi");
    exit();
}

// 2. Validasi status dokumen referensi (tidak boleh reject/batal)
$qParent = $this->db->select("id, trash, trash_4")
    ->from("transaksi")
    ->where("id", $transaksiID_reference)
    ->get()->row();

if (!$qParent || $qParent->trash != 0 || $qParent->trash_4 != 0) {
    $this->session->set_flashdata("error", "Transaksi referensi telah dibatalkan atau tidak valid.");
    redirect(MODUL_PATH . "Transaksi");
    exit();
}

// 3. Validasi Concurrency Lock (MdlLockerTransaksi)
$this->load->model("Mdls/MdlLockerTransaksi");
$locker = new MdlLockerTransaksi();
$isLockedByOther = $locker->isLockedByOtherUser($transaksiID, my_id());
if ($isLockedByOther) {
    showLockerBlockedPage($transaksiID);
    exit();
}
// END OF COMPLETE REPEATED LOGIC
```

#### 3.2 Transaksi Database Atomik
Seluruh operasi penulisan multi-tabel wajib dibungkus transaksi database CodeIgniter:
```php
// START OF COMPLETE REPEATED LOGIC
$this->db->trans_start();

// Eksekusi operasi penulisan transaksi, transaksi_data, dan relasi

$this->db->trans_complete();
if ($this->db->trans_status() === FALSE) {
    log_message('error', 'Gagal memproses transaksi pada modul ' . $this->modul);
    return FALSE;
}
// END OF COMPLETE REPEATED LOGIC
```

#### 3.3 Preservasi Identitas Silsilah (`id_master`)
Pastikan dokumen baru selalu menyalin `id_master` dari transaksi hulu:
```php
// START OF COMPLETE REPEATED LOGIC
$id_master_final = (!empty($parentRow->id_master) && (int)$parentRow->id_master > 0) 
    ? (int)$parentRow->id_master 
    : (int)$transaksiID_reference;
$dataInsertTransaksi['id_master'] = $id_master_final;
// END OF COMPLETE REPEATED LOGIC
```

#### 3.4 Preservasi Elemen Sesi Formulir (`main_elements`) saat Transisi Modul
Saat melakukan swap step atau transisi dokumen di `FollowUp.php`:
```php
// START OF COMPLETE REPEATED LOGIC
$_SESSION[$cCode]['main_elements'] = isset($_SESSION[$oldCode]['main_elements']) 
    ? $_SESSION[$oldCode]['main_elements'] 
    : array();
// END OF COMPLETE REPEATED LOGIC
```

#### 3.5 Mutasi Persediaan Melalui `ComLockerStockDualWrite`
Dilarang memanipulasi stok fisik secara langsung. Gunakan dual-write locker:
```php
// START OF COMPLETE REPEATED LOGIC
$this->load->model("Coms/ComLockerStockDualWrite");
$dw = new ComLockerStockDualWrite();
$dw->setModul($this->modul);
$dw->setTransaksiID($transaksiID);
$dw->commitHoldToActiveStock($gudangID, $itemsStockArray);
// END OF COMPLETE REPEATED LOGIC
```

---

## 6. Fase 4: Prosedur Perbaikan Read-Path & Audit Trail (Resume Terpadu `viewResume`)

Fase ini memastikan modal resume transaksi (`Transaksi::viewResume`) menyajikan riwayat operasional lengkap dan agregasi buku besar menyeluruh dari hulu ke hilir.

### Checklist Langkah Read-Path:

#### 4.1 Agregasi Nomor Dokumen Silsilah Rantai Transaksi
Kumpulkan seluruh nomor nota sah dari rantai `id_master`, titik entri (*entry points*), penerimaan kas, dan uang muka terkait:
```php
// START OF COMPLETE REPEATED LOGIC
$chainNomers = array($no);

// 1. Ekstrak dari tabel Entry Points / Riwayat Tahapan
if (isset($tmpEP) && is_array($tmpEP) && sizeof($tmpEP) > 0) {
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

// 2. Ekstrak dari Dokumen Pelunasan Finansial (A/R atau A/P Settlement)
if (isset($arrSettlement) && is_array($arrSettlement) && sizeof($arrSettlement) > 0) {
    foreach ($arrSettlement as $sRow) {
        if (!empty($sRow->nomer)) {
            $chainNomers[] = $sRow->nomer;
        }
    }
}

// 3. Ekstrak dari Uang Muka Terikat (transaksi_payment_source)
if (isset($searchIDs) && is_array($searchIDs) && count($searchIDs) > 0) {
    $this->db->select("t.nomer, t.id as um_id");
    $this->db->from("transaksi_payment_source tps");
    $this->db->join("transaksi t", "t.id = tps.transaksi_id", "inner");
    $this->db->where_in("tps.extern2_id", $searchIDs);
    $this->db->where("tps.dihapus", 0);
    $this->db->where("t.trash", "0");
    $queryPs = $this->db->get();
    if ($queryPs && $queryPs->num_rows() > 0) {
        foreach ($queryPs->result() as $psRow) {
            if (!empty($psRow->nomer)) {
                $chainNomers[] = $psRow->nomer;
            }
        }
    }
}

$chainNomers = array_values(array_unique(array_filter($chainNomers)));
// END OF COMPLETE REPEATED LOGIC
```

#### 4.2 Kueri Jurnal Akuntansi Terpadu (Aman & Bebas Celah)
Panggil `ComJurnal` dengan kueri aman (*query builder / parameter binding*):
```php
// START OF COMPLETE REPEATED LOGIC
$this->load->model("Coms/ComJurnal");
$j = new ComJurnal();

if (sizeof($chainNomers) > 1) {
    // Sanitasi parameter string untuk klausa IN
    $sanitizedNomers = array();
    foreach ($chainNomers as $cNom) {
        $sanitizedNomers[] = $this->db->escape_str($cNom);
    }
    $j->addFilter("transaksi_no IN ('" . implode("','", $sanitizedNomers) . "')");
} else {
    $cleanNo = $this->db->escape_str($no);
    $j->addFilter("transaksi_no='$cleanNo'");
}

$j->addFilter("status='1'");
$j->addFilter("trash='0'");
$jurnalRows = $j->lookupAll()->result();
// END OF COMPLETE REPEATED LOGIC
```

#### 4.3 Rekonsiliasi Kuantitas Fisik vs Tagihan
Cegah angka `Fulfill(Qty)` atau `Received(Qty)` tertahan di `0` jika nota fisik barang telah terbit:
```php
// START OF COMPLETE REPEATED LOGIC
if ((!isset($fullfillment[$id]["jml"]) || (float)$fullfillment[$id]["jml"] == 0) 
    && isset($shipment[$id]["jml"]) && (float)$shipment[$id]["jml"] > 0) {
    $val_fullfil = (float)$shipment[$id]["jml"];
    $subtotal_fullfil = $val_fullfil * (isset($iSpec["nett1_ppn"]) ? (float)$iSpec["nett1_ppn"] : (float)$iSpec["harga"]);
}
// END OF COMPLETE REPEATED LOGIC
```

#### 4.4 Badge Status Finansial Berbasis Helper Sentral
Gunakan helper standar `he_finansial_helper` (`fin_get_payment_status`) untuk menentukan badge pelunasan (Lunas / Pelunasan Sebagian / Belum Lunas) secara objektif dari data riil tabel finansial.

---

## 7. Fase 5: Matriks Verifikasi & Uji Asimetri (Regression & Smoke Testing)

Sebelum pekerjaan dinyatakan selesai, lakukan pengujian verifikasi multi-perspektif:

| Perspektif Pengujian | Titik Akses (URL Modal Resume) | Parameter yang Wajib Terlihat Benar | Status Verifikasi |
| :--- | :--- | :--- | :---: |
| **1. Sudut Pandang Hulu** | Buka resume dari nota dokumen awal (misal SPO / PR / Permintaan Mutasi). | Seluruh titik riwayat s/d kasir/pembayaran muncul; Jurnal pengiriman/penerimaan dan jurnal kasir tampil utuh; badge LUNAS valid. | [ ] |
| **2. Sudut Pandang Tengah** | Buka resume dari nota logistik fisik (misal SPD / GRN / In-Transit). | Kuantitas pemenuhan fisik tidak 0; Jurnal persediaan dan jurnal tagihan berdampingan; rincian barang akurat. | [ ] |
| **3. Sudut Pandang Hilir** | Buka resume dari nota tagihan / kuitansi kas (misal INV / Bill / RPC / Kasir). | Tautan balik ke dokumen pesanan asal aktif; Uang muka tercatat memotong tagihan; jurnal kas dan pendapatan/hutang lengkap. | [ ] |
| **4. Uji Data Masa Lalu (*Legacy*)** | Buka resume dari 3 transaksi historis yang terbit sebelum tanggal perbaikan. | Tampilan tidak *fatal error* / *blank page*; angka saldo tetap sinkron dengan laporan keuangan periode berjalan. | [ ] |
| **5. Uji Pembatalan (*Void/Reject*)** | Buka resume transaksi yang telah direject (`trash_4 = 1`). | Tanda batal/reject muncul berwarna merah pada entry points; jurnal terkait berstatus tereliminasi/void. | [ ] |

---

## 8. Panduan Komparasi Alur Modul (Reference Patterns)

Gunakan matriks ini sebagai referensi saat menerapkan Fase 2 (*Drill-Down*) pada berbagai siklus bisnis di Everest ERP:

| Parameter | Siklus Penjualan (Order-to-Cash) | Siklus Pembelian (Procure-to-Pay) | Siklus Mutasi Stok (Inter-Branch) |
| :--- | :--- | :--- | :--- |
| **Modul Utama** | `penjualan` | `pembelian` | `distribusifg` / `distribusisupplies` |
| **Dokumen Hulu** | Sales Pre-Order (`5822spo`), SO (`5822so`) | Purchase Request (`466pr`), PO (`466po`) | Permintaan Distribusi (`5844req`) |
| **Dokumen Fisik** | Packing List / Delivery (`5822spd`) | Penerimaan Barang / GRN (`466grn`) | Pengiriman (`5844out`) $\rightarrow$ Penerimaan (`5844in`) |
| **Dokumen Tagihan**| Invoice (`INV` / `4822`) | Tagihan Supplier / Bill (`466bill`) | - (Non-tagihan internal) |
| **Kode Uang Muka** | `4467` (Uang Muka Konsumen) | `4460` (Uang Muka Supplier) | - |
| **A/R vs A/P** | Pelunasan Piutang via A/R `749` / Kas `4464` | Pembayaran Hutang via A/P `759` / Kas `4447` | Jurnal Mutasi Persediaan Asal vs In-Transit vs Tujuan |
| **Integrasi Pajak**| Keluaran via `connectTo=110` (`110r`/`110e`) | Masukan via Faktur Masukan PPN `112`/`113` | - |

---

## 9. Kesimpulan & Penutup

Dengan mengikuti prosedur 5 fase ini:
1. Setiap perbaikan modul diawali dengan pemindaian kode yang cermat sehingga pertanyaan wawancara (*drill-down*) fokus dan terarah.
2. Tidak ada risiko *overfitting* atau merusak data masa lalu karena batas arsitektur (*invariants*) dan uji asimetri dikunci secara ketat.
3. Seluruh modul di Everest ERP akan memiliki standar integritas data dan transparansi audit trail yang seragam.


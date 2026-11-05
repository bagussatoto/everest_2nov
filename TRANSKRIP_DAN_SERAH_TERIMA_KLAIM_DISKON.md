# DOKUMEN SERAH TERIMA & TRANSKRIP LENGKAP INVESTIGASI
## SINKRONISASI KLAIM DISKON SUPPLIER & REKONSILIASI COA 1010020030
**Tanggal Penyusunan:** 03 November 2026  
**Aplikasi / Stack:** Everest ERP &mdash; PHP 5.6, CodeIgniter 3.1.8 HMVC, MariaDB 10  
**Modul Terdampak:** `kompensasiharga` (Transaksi 3333), `reporting`, Akun Sub-Ledger `1010020030`  
**Status Sesi:** Operasional Stabil (100% Sinkron), Tool Forensik Siap Pakai, Siap Masuk Tahap Menu Laporan Terpisah  

---

## 1. Ringkasan Eksekutif (Executive Summary)

Sesi kerja ini berfokus pada penyelesaian dua kebutuhan krusial:
1. **Kebutuhan Operasional Kasir/Operator (Selesai):** Menghilangkan selisih dan kebingungan data antara badge filter antrean klaim (`Semua (174)`), panel rekap saldo supplier, dan jumlah nota per vendor (khususnya PT. SHARP ELECTRONICS INDONESIA yang sebelumnya tertera 48 nota namun hanya tampil 1 nota saat diklik).
2. **Kebutuhan Akuntansi & Investigasi Forensik (Selesai):** Menjawab pertanyaan fundamental manajemen: *"Mengapa ada perbedaan 78 nota antara fisik locker (174 Nota) vs rekening pembantu akuntansi (252 Nota)? Apakah sudah diklaim namun rekening tidak diperbaharui?"* &rarr; **Terbukti secara empiris bahwa dokumen klaim telah terbit memotong fisik locker, namun mutasi kredit akuntansi gagal ter-posting ke buku pembantu.**

---

## 2. Kronologi Diskusi & Permintaan User (Conversation Log)

| No | Permintaan User | Analisis Teknis & Masalah yang Ditemukan | Solusi yang Diambil |
|:---:|:---|:---|:---|
| 1 | *Operator butuh mengetahui supplier mana saja yang masih memiliki saldo.* | Tampilan antrean klaim (`jenis=3333`) sebelumnya mencampuradukkan ribuan GRN tanpa ringkasan vendor mana yang masih punya hak tagih aktif. | Dibuatkan mockup perbandingan 3 opsi UI (`review_operator_toggle_view.html`), disepakati opsi tombol toggle persisten dengan panel rekapitulasi vendor. |
| 2 | *Tidak ada navigasi kembali saat memilih rekap saldo supplier.* | Panel rekap menyembunyikan wrapper DataTables sehingga operator kehilangan tombol kembali ke antrean nota GRN. | Ditambahkan persistent mode toolbar di atas panel (`[Siap Diklaim]` / `[Semua Data]` / `[Saldo Klaim Supplier]`) serta tombol merah `[⬅ Kembali ke Daftar Nota GRN]` di atas, header, dan footer panel. |
| 3 | *PT Sharp diklik tidak sampai 48 nota, hanya ada satu?* | Data rekap membaca dari tabel cache akuntansi (`_rek_pembantu_subpiutangsuppliertrans_cache` = 252 nota, Sharp = 48), sedangkan antrean klaim membaca fisik `stock_locker_diskon` (174 nota, Sharp = 1). | User menginstruksikan: *"kita geser ke sini dulu 'stock_locker_diskon' biar sinkron supaya mudah user-nya"*. Sumber query `getSupplierBalanceSummaryAjax` dialihkan ke `stock_locker_diskon`. |
| 4 | *Keanehan: Badge 174 Nota vs Total Konsolidasi Rekap 252 Nota.* | Efek turunan dari perbedaan sumber data di atas. | Setelah query dialihkan ke `stock_locker_diskon`, total konsolidasi rekap menjadi tepat **174 Nota** dan PT Sharp tepat **1 Nota** (sinkron 100%). |
| 5 | *Saldo lunas tidak tampil nama suppliernya (muncul `Supplier #8`, dsb).* | Query vendor lunas merujuk ke tabel non-existent `suppliers` (`SELECT id, nama FROM suppliers`). Di Everest, tabel master adalah `per_supplier`. | Query diperbaiki menggunakan model resmi Everest [`MdlSupplier`](file:///z:/everest_2nov/application/models/Mdls/MdlSupplier.php) (`$mdlSupplier->lookupAll()`) dengan fallback bertingkat ke `per_supplier` dan `transaksi.suppliers_nama`. |
| 6 | *Bantu perbaiki supaya kalimat lebih pendek dan bebas pusing, gunakan yang saya tandai saja.* | User menandai kotak pada tombol dan garis merah pada teks: **`SALDO KLAIM SUPPLIER`** untuk membuang jargon akuntansi (*"SUB-LEDGER"*, *"COA 1010020030"*). | Seluruh teks UI diseragamkan menjadi **`SALDO KLAIM SUPPLIER`** (judul box, tombol toolbar, tombol DataTables, loading, dan footer). |
| 7 | *Tracing mengapa ada ketidaksinkronan antara locker dengan rekening, apakah sudah diklaim namun rekening tidak diperbaharui.* | Hipotesis user diuji. Dibuatkan skrip investigasi forensik 3-way matching pada controller [`AuditKlaimSupplier.php`](file:///z:/everest_2nov/application/modules/kompensasiharga/controllers/AuditKlaimSupplier.php#L1282). | Dibangun dashboard audit di URL: `kompensasiharga/AuditKlaimSupplier/traceSelisih`. |
| 8 | *Akumulasi nominal selisih/tergantung belum ada.* | Tabel audit belum memiliki footer rekapitulasi nominal rupiah dan kartu KPI belum menonjolkan total rupiah tergantung. | Ditambahkan kartu KPI nominal besar, baris `<tfoot>` total konsolidasi rupiah, dan JavaScript auto-recalculate secara *real-time* saat filter/pencarian aktif. |

---

## 3. Berkas yang Telah Dimodifikasi (Modified Files)

### 3.1. `application/modules/kompensasiharga/controllers/Transaksi.php`
* **Fungsi `getSupplierBalanceSummaryAjax()` (Baris 6636 s/d 6800):**
  1. Mengalihkan query supplier aktif dari `_rek_pembantu_subpiutangsuppliertrans_cache` ke `stock_locker_diskon` (`nilai >= 1`, `jenis_locker = 'stock'`, `transaksi.jenis IN ('467')`, `status = '1'`, `trash = '0'`).
  2. Mengimplementasikan model [`MdlSupplier`](file:///z:/everest_2nov/application/models/Mdls/MdlSupplier.php) untuk membaca nama-nama mitra supplier yang berstatus lunas (saldo Rp 0), menggantikan query raw SQL yang gagal sebelumnya.

### 3.2. `application/modules/kompensasiharga/views/transaksi.php`
* **Bagian Toolbar & Tabel Saldo Klaim Supplier (Baris 14586, 14805, 14835–14955):**
  1. Menambahkan persistent mode switcher toolbar di atas tabel.
  2. Mengganti seluruh teks panjang/jargon akuntansi menjadi **`SALDO KLAIM SUPPLIER`**.
  3. Mengamankan event click listener menggunakan delegated jQuery (`.btn-switch-mode`, `.btn-close-supp-summary`, `.btn-fast-track-supp`) berbasis HTML5 data attributes (`data-id`, `data-name`) untuk mencegah error *SyntaxError: Unexpected string*.

### 3.3. `application/modules/kompensasiharga/controllers/AuditKlaimSupplier.php`
* **Fungsi Baru `traceSelisih()` (Baris 1282 s/d 1795):**
  1. Melakukan komparasi 3-arah: Rekening Cache `1010020030` vs `stock_locker_diskon` vs Transaksi Klaim `3333` / Retur `9911`.
  2. Mengklasifikasikan 78 nota selisih ke dalam kategori:
     - 🔴 **Sudah Diklaim (Rekening Belum Ter-kredit):** Terbukti ada dokumen `3333` dan locker telah 0, namun kredit akuntansi = 0.
     - 🟠 **Locker Kosong / Ter-wipe Rp 0:** Locker bernilai 0 sejak awal simpan GRN.
     - 🔵 **Terkait Retur (9911):** GRN dibatalkan/diretur.
  3. Menyajikan kartu KPI akumulasi nominal besar, baris `<tfoot>` rekapitulasi, dan kalkulasi dinamis real-time saat filter diklik (seperti filter `[Khusus PT. Sharp]`).

---

## 4. Temuan Forensik Inti (Root Cause Analysis 78 Nota Selisih)

Berdasarkan hasil eksekusi diagnosa pada `AuditKlaimSupplier/traceSelisih`:

```
========================================================================================
STATUS PERBANDINGAN DATA GRN DISKON PEMBELIAN
========================================================================================
Total GRN dengan Saldo Piutang di Rekening Cache (1010020030) : 252 Nota
Total GRN dengan Saldo Aktif Fisik di stock_locker_diskon      : 174 Nota
----------------------------------------------------------------------------------------
SELISIH NOTA YANG MENGGANTUNG                                  :  78 Nota
  └─ Dari 78 Nota Selisih ini, milik PT. SHARP                :  47 Nota
========================================================================================
```

### Mengapa Ada Selisih Ini?
1. **Fakta Terbukti: Transaksi Klaim 3333 Sudah Pernah Dieksekusi**
   - Sebagian besar dari 78 nota tersebut (khususnya 47 nota PT. Sharp) **telah diproses klaimnya** oleh kasir/operator melalui dokumen klaim resmi (`jenis = '3333'`).
   - Modul klaim memotong nilai saldo di tabel fisik `stock_locker_diskon` sehingga saldonya menjadi `0` (atau berstatus diklaim).
2. **Titik Kerusakan Sistemik: Gagal Posting Kredit ke Buku Pembantu Akuntansi**
   - Transaksi klaim `3333` tersebut mengalami kendala saat eksekusi posting ke komponen `ComRekeningPembantuPiutangSupplierDetailTransMain` (diduga akibat *mismatch selector ID* di `coTransaksiUi.php` pada versi lama).
   - Akibatnya:
     - Mutasi **Kredit** pada partisi `__rek_pembantu_subpiutangsuppliertrans__1010020030` **TIDAK PERNAH TERCATAT (Kredit = 0)**.
     - Kolom `debet` pada tabel snapshot `_rek_pembantu_subpiutangsuppliertrans_cache` **TIDAK PERNAH DINOLKAN**.
     - Rekening akuntansi menganggap hak piutang tersebut masih utuh berjalan (*forever*).

---

## 5. Rencana Kerja Selanjutnya untuk Tim Penerus (Next Steps Roadmap)

### Langkah 1: Pembuatan Menu Laporan Akuntansi Terpisah
* **Tujuan:** Membuat halaman laporan akuntansi sub-ledger COA 1010020030 yang terpisah dari transaksi operasional, agar operasional harian kasir tetap bersih dan cepat.
* **Referensi Siap Pakai:**
  - Prototype Desain HTML: [`z:\everest_2nov\laporan_rekening_1010020030.html`](file:///z:/everest_2nov/laporan_rekening_1010020030.html)
  - Blueprint & Spesifikasi Query SQL: [`z:\everest_2nov\BLUEPRINT_LAPORAN_AKUNTANSI_COA_1010020030.md`](file:///z:/everest_2nov/BLUEPRINT_LAPORAN_AKUNTANSI_COA_1010020030.md)
* **Tempat Implementasi yang Disarankan:**
  Buat controller baru di bawah modul pelaporan, misalnya `application/modules/reporting/controllers/LaporanSubpiutangSupplier.php` atau submenu di modul `kompensasiharga/LaporanKlaimSupplier.php`.

### Langkah 2: Rekonsiliasi & Pembersihan 78 Nota Selisih (Sinkronisasi Kredit)
* **Tujuan:** Membersihkan 78 nota gantung dari rekening cache agar neraca akuntansi mencerminkan fakta operasional bahwa nota-nota tersebut memang telah lunas dicairkan.
* **Metode Eksekusi Aman:**
  Gunakan atau panggil fungsi rekonsiliasi yang sudah ada di [`Transaksi.php`](file:///z:/everest_2nov/application/modules/kompensasiharga/controllers/Transaksi.php#L6915) (`cekSaldoDiskonGrnCache` atau `cekSaldoDiskonGrn`) untuk:
  1. Memasukkan mutasi kredit penyesuaian pada partisi `__rek_pembantu_subpiutangsuppliertrans__1010020030`.
  2. Mengupdate kolom `debet = 0` pada tabel cache `_rek_pembantu_subpiutangsuppliertrans_cache` untuk 78 ID GRN tersebut.

---

## 6. Daftar Tautan Cepat untuk Tim

* **Layar Operasional Transaksi Klaim:**
  `https://demo.mayagrahakencana.com/everest_2nov/kompensasiharga/Transaksi/index/3333`
* **Dashboard Investigasi Forensik 78 Nota Selisih:**
  `https://demo.mayagrahakencana.com/everest_2nov/kompensasiharga/AuditKlaimSupplier/traceSelisih`
* **Dashboard Audit Hulu-Hilir Diskon Pembelian:**
  `https://demo.mayagrahakencana.com/everest_2nov/Tool/traceDiskonPembelian`
* **Prototype Laporan Akuntansi:**
  [`z:\everest_2nov\laporan_rekening_1010020030.html`](file:///z:/everest_2nov/laporan_rekening_1010020030.html)
* **Dokumen Blueprint Akuntansi:**
  [`z:\everest_2nov\BLUEPRINT_LAPORAN_AKUNTANSI_COA_1010020030.md`](file:///z:/everest_2nov/BLUEPRINT_LAPORAN_AKUNTANSI_COA_1010020030.md)


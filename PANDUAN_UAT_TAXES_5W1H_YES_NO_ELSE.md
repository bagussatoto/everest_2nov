# 📘 BUKU PANDUAN LENGKAP UAT (USER ACCEPTANCE TESTING)
## MODUL PERPAJAKAN (`taxes`) — PPN, E-FAKTUR, PPH & BEA MASUK
### Metode Analisis: **5W + 1H** & Pipeline Logika Percabangan **YES / NO / ELSE**

> **Dokumen ini adalah panduan operasional komprehensif** untuk menguji seluruh siklus transaksi perpajakan korporasi pada modul [`taxes`](file:///z:/everest_19okt/application/modules/taxes). Seluruh alur langkah (`steps`), hak akses (`userGroup`), lokasi (`place = center`), validasi `cabangValidator`, pembentukan jurnal buku besar (Pusat & Cabang), pembantu pajak, serta mekanisme `reject` telah diselaraskan 100% dengan kode sumber asli [`coTransaksiUi.php`](file:///z:/everest_19okt/application/modules/taxes/config/coTransaksiUi.php) dan [`coTransaksiCore.php`](file:///z:/everest_19okt/application/modules/taxes/config/coTransaksiCore.php).

---

## 🧭 1. PENDAHULUAN & KONTEKS BISNIS (5W + 1H OVERVIEW)

| Dimensi | Pertanyaan Kunci | Jawaban & Konteks Operasional Modul `taxes` |
| :--- | :--- | :--- |
| **WHAT** *(Apa)* | Apa objek utama yang diuji dalam modul ini? | Siklus manajemen perpajakan terpadu yang mencakup **9 Kode Transaksi Aktif UI** (**`681`**, **`5681`**, **`110`**, **`111`**, **`5683`**, **`117`**, **`118`**, **`116`**, **`1155`**) serta **1 Kode Core Backend** (**`1483`**), mulai dari pencatatan Faktur Pajak (e-Faktur), Bukti Potong PPh, hingga Setoran Pajak Kas/Bank. |
| **WHY** *(Mengapa)* | Mengapa pengujian UAT ini bersifat kritis bagi perusahaan? | Karena kesalahan pencatatan pajak berdampak langsung pada **ketidakpatuhan fiskal (DJP)**, selisih neraca pajak (**PPN Belum Ada Faktur vs PPN Realisasi**), salah alokasi buku **Pusat vs Cabang**, serta potensi duplikasi nomor seri faktur pajak. |
| **WHO** *(Siapa)* | Siapa saja aktor/pengguna yang terlibat dalam alur ini? | Sesuai [`coTransaksiUi.php`](file:///z:/everest_19okt/application/modules/taxes/config/coTransaksiUi.php), hanya ada 3 grup akses: **System/Otomatis Hulu** (`sys`), **Staf Finance Pajak** (`c_finance`), dan **Supervisor Finance** (`c_finance_spv`). |
| **WHERE** *(Di mana)* | Di mana proses ini dijalankan di dalam arsitektur sistem? | **Wajib di Kantor Pusat (`place = center`, `cabang_id = -1`)** pada modul `application/modules/taxes`. Terdapat fitur proteksi `cabangValidator` pada kode `110` dan `111` yang otomatis menolak transaksi bila pengguna login di Cabang. |
| **WHEN** *(Kapan)* | Kapan masing-masing tahapan transaksi dieksekusi? | Saat kiriman antrean pajak (`111r`, `116r`, `1155r`) terbentuk otomatis oleh sistem (`sys`) dari transaksi penjualan/pembelian/pembayaran, saat diterbitkan e-Faktur PPN Keluaran (`110`), atau saat penyetoran PPh masa/tahunan (`117`, `118`, `5683`). |
| **HOW** *(Bagaimana)* | Bagaimana mekanisme kerja & validasi sistemnya? | Melalui pendekatan **Multi-Step Approval Workflow**, penguncian **Locker Transaksi (`ComLockerStockDualWrite`)**, validasi **Token Mutasi CSRF (`TaxesMutationService`)**, pengecekan **Format Faktur (`checkerKodePajak`) & NPWP (`checkerNomerNpwp`)**, serta penjurnalan otomatis **Buku Pusat (`placeID`) & Buku Cabang (`place2ID`)**. |

---

### 1.1 Peta Kode Transaksi Modul `taxes` (Sesuai `coTransaksiUi.php` & `coTransaksiCore.php`)

| Kode | Nama Transaksi di Sistem | Pihak (`pihakModel`) | Step 1 (`userGroup`) | Step 2 (`userGroup`) | Step 3 (`userGroup`) | Dampak Jurnal / Komponen di `coTransaksiCore.php` |
| :---: | :--- | :--- | :---: | :---: | :---: | :--- |
| **`110`** | **E-Faktur PPN Keluaran (Sales)** | `MdlCustomer` | `110r` (`c_finance`) | `110e` (`c_finance`) | `110` (`c_finance_spv`) | **2 Buku (Pusat & Cabang)** + PostProc `SyncPpnBendahara` |
| **`111`** | **Faktur Pajak PPN Masukan (Purchase)** | `MdlSupplier` | `111r` (**`sys`**) | `111` (`c_finance`) | — | **Jurnal Pusat:** Rekonsiliasi `1010040050` $\rightarrow$ `1010040060` |
| **`116`** | **Bukti Bayar PPh 23 (Customer / SO)** | `MdlCustomer_and_pre` | `116r` (**`sys`**) | `116` (`c_finance`) | — | **2 Buku (Pusat & Cabang):** `1010040030` vs `1010060010` & `2040010` |
| **`1155`** | **Input Nomor Faktur PPh 23 (Supplier)** | `MdlSupplier` | `1155r` (**`sys`**) | `1155` (`c_finance`) | — | Tanpa Jurnal GL (`components` kosong); PostProc `PaymentSrcItem` (`.hutang pph23`) |
| **`117`** | **Pembayaran PPh Pasal 25** | `MdlNota` (`463o`) | `117r` (`c_finance`) | `117` (`c_finance_spv`) | — | **Jurnal Pusat:** DEBET `1010040040` (PPh 25) / KREDIT `1010010010` (Kas/Bank) |
| **`118`** | **Pembayaran PPh Pasal 4 Ayat (2)** | `MdlNota` (`463o`) | `118r` (`c_finance`) | `118` (`c_finance_spv`) | — | **Jurnal Pusat:** DEBET `1010040110` (PPh 4(2)) / KREDIT `1010010010` (Kas/Bank) |
| **`5683`** | **PPh 29 (Kurang Bayar Tahunan)** | `MdlCustomer_and_pre` | `5683r` (`c_finance`) | `5683` (`c_finance_spv`) | — | **Jurnal Pusat:** `1010040120` (PPh 29) & `2030050` (Hutang PPh 29)* |
| **`681`** | **Pengajuan PPN Impor (PIB)** | `MdlNota` (`466r/460r/461ro`) | `681r` (`c_finance`) | `681` (`c_finance`) | — | Tanpa Jurnal GL di modul ini (`components => array()`)* |
| **`5681`** | **Pengajuan PPh 22 (Impor/Bendahara)** | `MdlNota` (`466r/460r/461ro`) | `5681r` (`c_finance`) | `5681` (`c_finance`) | — | Tanpa Jurnal GL di modul ini (`components => array()`) |
| **`1483`** | **Penyesuaian / Setoran Hutang PPh 21** | *(Belum ada di UI)* | — | — | — | Hanya ada di `coTransaksiCore.php` (`2030010` vs `1010010010` / `2020020`)* |

> [!WARNING]
> **Catatan Penting Konfigurasi Koding Saat Ini:**
> 1. **Kode `1483`:** Terdefinisi lengkap di [`coTransaksiCore.php` (L2149-L2457)](file:///z:/everest_19okt/application/modules/taxes/config/coTransaksiCore.php#L2149-L2457), namun **belum didaftarkan di `coTransaksiUi.php`**. Pemanggilan URL `1483` secara langsung di browser akan dihentikan oleh `Modul_Controller.php` dengan pesan: *"1483 belum ada di coTransaksiUi di modul taxes"*.
> 2. **Kode `681` Step 2:** Pada [`coTransaksiUi.php` (L30)](file:///z:/everest_19okt/application/modules/taxes/config/coTransaksiUi.php#L30), konfigurasi Step 2 tertulis `"source" => "651r"` (sedangkan hasil Step 1 adalah `"681r"`). Perlu diperhatikan saat menguji transisi Step 1 ke Step 2 pada kode `681`.
> 3. **Kode `5683` (PPh 29):** Pada [`coTransaksiCore.php` (L990)](file:///z:/everest_19okt/application/modules/taxes/config/coTransaksiCore.php#L990) terdapat catatan teknis developer bahwa logika pajak PPh 29 masih dalam tahap penyesuaian.

---

### 1.2 Prasyarat Data Master & Lingkungan Sebelum UAT Dimulai
Pastikan seluruh komponen data berikut sudah tersedia dan aktif di database:
1. **Lokasi Login Wajib (Kantor Pusat):**
   * Seluruh pengujian modul `taxes` **WAJIB** menggunakan sesi login yang berada di **Kantor Pusat (`place = center`, `cabang_id = -1`)**.
   * Pada kode **`110`** ([`coTransaksiUi.php` L1532](file:///z:/everest_19okt/application/modules/taxes/config/coTransaksiUi.php#L1532)) dan **`111`** ([`coTransaksiUi.php` L2007](file:///z:/everest_19okt/application/modules/taxes/config/coTransaksiUi.php#L2007)), fitur `cabangValidator` aktif menolak penyimpanan apabila terdeteksi berada di Cabang.
2. **Akun Pengguna (Role Matrix Sesuai `coTransaksiUi.php`):**
   * **User 1 (`c_finance`):** Staf Finance/Pajak Pusat (Pembuat Step 1 pada `110`, `117`, `118`, `5683`, `681`, `5681` serta Eksekutor Step 2 pada `110`, `111`, `116`, `1155`, `681`, `5681`).
   * **User 2 (`c_finance_spv`):** Supervisor Finance Pusat (Approver Final pada `110` Step 3, `117` Step 2, `118` Step 2, dan `5683` Step 2).
   * **User 3 (`c_kasir` / Akun Cabang `cabang_id > 0`):** Untuk pengujian negatif (RBAC & `cabangValidator`).
3. **Data Master Pihak & Referensi Nota Hulu:**
   * **Customer (`MdlCustomer` / `MdlCustomer_and_pre`):** Memiliki NPWP valid 15/16 digit (`npwp`) untuk pengujian `110`, `116`, dan `5683`.
   * **Supplier (`MdlSupplier`):** Memiliki NPWP valid 15/16 digit untuk pengujian `111` dan `1155`.
   * **Antrean Otomatis `sys` (`111r`, `116r`, `1155r`):** Telah terbentuk minimal 1 transaksi antrean Step 1 dari modul Pembelian/Penjualan/Pembayaran untuk di-followup di Step 2.
   * **Nota Referensi Biaya/Jasa (`jenis = 463o`):** Tersedia di `MdlNota` untuk pengujian kode `117` (PPh 25) dan `118` (PPh Pasal 4(2)).
   * **Nota Referensi Impor/PO (`jenis IN ('466r','460r','461ro')`):** Tersedia di `MdlNota` untuk pengujian kode `681` dan `5681`.
4. **Rekening Akuntansi (COA) yang Terlibat:**
   * `1010010010` — Kas / Bank (`cash_account`)
   * `1010040030` — Uang Muka PPh Pasal 23 (`pph 23 dibayar dimuka`)
   * `1010040040` — Uang Muka PPh Pasal 25 (`pph 25`)
   * `1010040050` — PPN Masukan Belum Ada Faktur (`ppn in belum ada faktur`)
   * `1010040060` — PPN Masukan Realisasi / Sudah Faktur (`ppn in realisasi`)
   * `1010040110` — PPh Pasal 4 Ayat (2) Final (`pph pasal 4(2)`)
   * `1010040120` — PPh Pasal 29 (`pph 29`)
   * `1010060010` — Piutang Cabang (Buku Pusat)
   * `2040010` — Hutang ke Pusat (Buku Cabang)
   * `2030060` — PPN Keluaran Belum Faktur (`ppn out belum faktur` - Buku Cabang)
   * `2030070` — PPN Keluaran Sudah Ada Faktur (`ppn out sudah ada faktur` - Buku Pusat)
   * `2030050` — Hutang PPh Pasal 29

---

## 🌳 2. KLASTER TRANSAKSI & PIPELINE LOGIKA (YES / NO / ELSE)

### 2.1 KLASTER 1: E-FAKTUR PPN KELUARAN (`110`) — 3 STEPS (`110r` $\rightarrow$ `110e` $\rightarrow$ `110`)

```
[MULAI: Invoice Penjualan Customer Terbit]
       │
       ▼
┌────────────────────────────────────────────────────────────────────────────┐
│ STEP 1: Pengajuan E-Faktur PPN Keluaran (Kode: 110r)                       │
│ Aktor: Staf Finance Pusat (c_finance) | Lokasi: Center (cabang_id = -1)    │
│ Endpoint: taxes/Create/index/110                                           │
└──────┬─────────────────────────────────────────────────────────────────────┘
       │
       ├─► Apakah User Login di Pusat (center) & Invoice + NPWP Valid?
       │     ├── [YES]  ──► Simpan Step 1: Terbentuk "110r" (Menunggu Input Faktur)
       │     ├── [NO]   ──► Login di Cabang -> Ditolak cabangValidator!
       │     └── [ELSE] ──► Koneksi terputus -> Tersimpan di Draft MongoDB
       │
       ▼
┌────────────────────────────────────────────────────────────────────────────┐
│ STEP 2: Input Nomor E-Faktur & Tanggal Faktur (Kode: 110e)                 │
│ Aktor: Staf Finance Pusat (c_finance)                                      │
│ Endpoint: taxes/FollowUp/followupPrePreviewFaktur/110/...                  │
└──────┬─────────────────────────────────────────────────────────────────────┘
       │
       ├─► Validasi eFaktur (checkerKodePajak), dateFaktur & NPWP (15/16 Digit)?
       │     ├── [YES]  ──► Naik ke Step 2: Terbentuk "110e" (Siap Otorisasi SPV)
       │     ├── [NO]   ──► Format eFaktur/NPWP salah atau duplikat -> Blokir Submit!
       │     └── [ELSE] ──► Salah pilih invoice -> Klik Reject (doRevert / doRevertAll)
       │
       ▼
┌────────────────────────────────────────────────────────────────────────────┐
│ STEP 3: Approval E-Faktur Final (Kode: 110)                                │
│ Aktor: Supervisor Finance Pusat (c_finance_spv)                            │
│ Endpoint: taxes/FollowUp/followupPrePreviewFaktur/110/...                  │
└──────┬─────────────────────────────────────────────────────────────────────┘
       │
       ├─► Keputusan Otorisasi Supervisor Finance (c_finance_spv)?
       │     ├── [YES / APPROVE] ──► 1. Status Final "110"
       │     │                       2. Jurnal Buku Pusat (placeID):
       │     │                          [D] 1010060010 Piutang Cabang
       │     │                          [K] 2030070    PPN Out Sudah Ada Faktur
       │     │                       3. Jurnal Buku Cabang (place2ID):
       │     │                          [D] 2030060    PPN Out Belum Faktur
       │     │                          [K] 2040010    Hutang ke Pusat
       │     │                       4. Eksekusi PostProcessor: SyncPpnBendahara
       │     ├── [NO / REJECT 1 STEP]  ──► doRevert: Mundur dari Step 3 ke Step 2
       │     └── [ELSE / REJECT ALL]   ──► doRevertAll: Batal penuh (trash_4 = 1)
       ▼
[SELESAI]
```

---

### 2.2 KLASTER 2: FAKTUR PPN MASUKAN (`111`) — 2 STEPS (`111r` Otomatis `sys` $\rightarrow$ `111`)

```
[MULAI: Transaksi Pembelian / Penerimaan Barang Kena Pajak di Hulu Selesai]
       │
       ▼
┌────────────────────────────────────────────────────────────────────────────┐
│ STEP 1 (OTOMATIS SISTEM): Antrean Faktur PPN Masukan (Kode: 111r)          │
│ Aktor: Sistem Otomatis (userGroup = sys)                                   │
│ Catatan: Tester TIDAK membuat manual lewat Create/index/111!               │
└──────┬─────────────────────────────────────────────────────────────────────┘
       │
       ▼
┌────────────────────────────────────────────────────────────────────────────┐
│ STEP 2: Input & Realisasi Faktur Pajak Masukan (Kode: 111)                 │
│ Aktor: Staf Finance Pusat (c_finance) | Lokasi: Center (cabang_id = -1)    │
│ Endpoint: taxes/FollowUp/followupPrePreview/111/{ID}/2/1                   │
└──────┬─────────────────────────────────────────────────────────────────────┘
       │
       ├─► Validasi cabangValidator, eFaktur, dateFaktur & NPWP Supplier (15/16 Digit)?
       │     ├── [YES / SUBMIT]  ──► 1. Status Final "111" (PPN Masukan Sah)
       │     │                       2. Jurnal Buku Pusat (placeID):
       │     │                          [D] 1010040060 PPN In Realisasi (Sudah Faktur)
       │     │                          [K] 1010040050 PPN In Belum Ada Faktur
       │     │                       3. Update Rekening Pembantu Supplier & Pajak
       │     ├── [NO / INVALID]  ──► Login di Cabang / Nomor Faktur kosong / Duplikat /
       │     │                       NPWP bukan 15-16 digit -> Diblokir dengan Alert!
       │     └── [ELSE / REJECT] ──► Klik Reject (doRevertAll) -> Antrean dibatalkan
       ▼
[SELESAI]
```

---

### 2.3 KLASTER 3: BUKTI POTONG PPH 23 CUSTOMER (`116`) VS SUPPLIER (`1155`)

> [!IMPORTANT]
> **Perbedaan Mendasar `116` dan `1155` di Koding:**
> - **Kode `116` (Bukti Bayar PPh 23 Customer):** Berasal dari transaksi penjualan/SO (`MdlCustomer_and_pre`). Step 1 (`116r`) dibuat otomatis oleh `sys`. Saat Step 2 (`116`) diselesaikan oleh `c_finance`, sistem **membentuk Jurnal di Buku Pusat & Buku Cabang**.
> - **Kode `1155` (Input Nomor Faktur PPh 23 Supplier):** Berasal dari transaksi pembelian/hutang ke vendor (`MdlSupplier`). Step 1 (`1155r`) dibuat otomatis oleh `sys`. Saat Step 2 (`1155`) diselesaikan oleh `c_finance`, sistem **tidak membentuk jurnal GL** (`components` kosong), melainkan mengeksekusi postProcessor `PaymentSrcItem` untuk meng-update nilai `.hutang pph23`.

```
[ANTREAN OTOMATIS DARI HULU (userGroup = sys): 116r (Customer) atau 1155r (Supplier)]
       │
       ▼
┌────────────────────────────────────────────────────────────────────────────┐
│ STEP 2: FollowUp Bukti Potong / Nomor Faktur PPh 23 (Kode: 116 / 1155)     │
│ Aktor: Staf Finance Pusat (c_finance)                                      │
│ Endpoint: taxes/FollowUp/followupPrePreview/{116 atau 1155}/{ID}/2/1       │
└──────┬─────────────────────────────────────────────────────────────────────┘
       │
       ├─► Apakah Input eFaktur, dateFaktur & NPWP Valid?
       │     ├── [YES — KODE 116 (Customer)]  ──► 1. Status Final "116"
       │     │                                    2. Jurnal Buku Pusat (placeID):
       │     │                                       [D] 1010040030 PPh 23 Dibayar Dimuka
       │     │                                       [K] 1010060010 Piutang Cabang
       │     │                                    3. Jurnal Buku Cabang (place2ID):
       │     │                                       [D] 2040010    Hutang ke Pusat
       │     │                                       [K] 1010040030 PPh 23 Dibayar Dimuka
       │     ├── [YES — KODE 1155 (Supplier)] ──► 1. Status Final "1155"
       │     │                                    2. Tanpa Jurnal GL (components kosong)
       │     │                                    3. PostProcessor PaymentSrcItem (.hutang pph23)
       │     ├── [NO / INVALID]               ──► eFaktur/dateFaktur kosong -> Diblokir!
       │     └── [ELSE / REJECT]              ──► doRevertAll -> Membatalkan antrean
       ▼
[SELESAI]
```

---

### 2.4 KLASTER 4: PEMBAYARAN PPH 25 (`117`), PPH PASAL 4(2) (`118`), & PPH 29 (`5683`)

```
[MULAI: Penyetoran Pajak PPh ke Kas Negara]
       │
       ▼
┌────────────────────────────────────────────────────────────────────────────┐
│ STEP 1: Request Pembayaran PPh (Kode: 117r / 118r / 5683r)                 │
│ Aktor: Staf Finance Pusat (c_finance) | Lokasi: Center (cabang_id = -1)    │
│ Khusus 117 & 118:                                                          │
│ - Pilih Referensi Nota (MdlNota filter jenis = .463o)                      │
│ - Pilih Objek Pajak (_processSelectBiaya: MdlPph25Static / MdlPphPasal)    │
│ - Pilih Rekening Kas/Bank (cash_account di Step 1 / editPoints = array(1)) │
└──────┬─────────────────────────────────────────────────────────────────────┘
       │
       ├─► Apakah Nota 463o, Objek Pajak & Akun Kas/Bank (cash_account) Lengkap?
       │     ├── [YES]  ──► Terbentuk Request Step 1 (117r / 118r / 5683r)
       │     ├── [NO]   ──► Akun Kas/Bank atau Objek Pajak belum dipilih -> Gagal Submit!
       │     └── [ELSE] ──► Tersimpan di Draft MongoDB
       │
       ▼
┌────────────────────────────────────────────────────────────────────────────┐
│ STEP 2: Approval Pembayaran PPh (Kode: 117 / 118 / 5683)                   │
│ Aktor: Supervisor Finance Pusat (c_finance_spv)                            │
│ Endpoint: taxes/FollowUp/followupPrePreview/{117|118|5683}/{ID}/2/1        │
└──────┬─────────────────────────────────────────────────────────────────────┘
       │
       ├─► Keputusan Otorisasi Supervisor Finance (c_finance_spv)?
       │     ├── [YES — KODE 117 (PPh 25)]    ──► [D] 1010040040 PPh 25
       │     │                                    [K] 1010010010 Kas/Bank
       │     ├── [YES — KODE 118 (PPh 4(2))]  ──► [D] 1010040110 PPh Pasal 4(2)
       │     │                                    [K] 1010010010 Kas/Bank
       │     ├── [YES — KODE 5683 (PPh 29)]   ──► Jurnal ke 1010040120 & 2030050
       │     ├── [NO / REJECT 1 STEP]         ──► doRevert: Kembali ke Step 1
       │     └── [ELSE / REJECT ALL]          ──► doRevertAll: Void (trash_4 = 1)
       ▼
[SELESAI]
```

---

### 2.5 KLASTER 5: PENGAJUAN PPN IMPOR / PIB (`681`) & PPH 22 (`5681`)
* **Karakteristik di [`coTransaksiUi.php`](file:///z:/everest_19okt/application/modules/taxes/config/coTransaksiUi.php) & [`coTransaksiCore.php`](file:///z:/everest_19okt/application/modules/taxes/config/coTransaksiCore.php):**
  * **Step 1 (`681r` / `5681r`)** dan **Step 2 (`681` / `5681`)** sama-sama dijalankan oleh **`c_finance`** di **Pusat (`center`)**.
  * Memilih referensi PO/Impor dari `MdlNota` dengan filter `jenis IN ('466r','460r','461ro')` dan item objek pajak (`MdlObjekPajak1` untuk `681`, `MdlObjekPajak` untuk `5681`).
  * Memiliki `"components" => array()` di `coTransaksiCore.php` (berfungsi sebagai pencatatan pengajuan/klaim pajak impor untuk diteruskan ke modul pembayaran/keuangan, tanpa menjurnal buku besar langsung di modul `taxes`).

---

## 🔄 3. MEKANISME REJECT 1 STEP (`doRevert`) VS REJECT ALL STEPS (`doRevertAll`)

Di dalam controller [`FollowUp.php`](file:///z:/everest_19okt/application/modules/taxes/controllers/FollowUp.php), sistem menyediakan dua metode pembatalan/penolakan dengan dampak database yang berbeda:

| Aspek Perbandingan | **Reject 1 Step (`FollowUp/doRevert`)** | **Reject All Steps (`FollowUp/doRevertAll`)** |
| :--- | :--- | :--- |
| **Lokasi Fungsi di Koding** | [`FollowUp.php` (Baris 2860)](file:///z:/everest_19okt/application/modules/taxes/controllers/FollowUp.php#L2860) | [`FollowUp.php` (Baris 3155)](file:///z:/everest_19okt/application/modules/taxes/controllers/FollowUp.php#L3155) |
| **Tujuan Operasional** | Mengembalikan dokumen **mundur 1 langkah** ke tahapan sebelumnya agar dapat diperbaiki (revisi nomor/tanggal faktur). | Membatalkan (**Void / Reject Permanen**) seluruh rangkaian transaksi dari awal sampai akhir. |
| **Kapan Digunakan** | Saat dokumen berada di **Step $\ge$ 2** (contoh: `110` di Step 3 dikembalikan ke Step 2 `110e` karena salah ketik nomor seri faktur). | Saat transaksi masih di **Step 1**, atau saat Supervisor memutuskan dokumen salah total (salah pilih Customer/Nota). |
| **Perubahan Kolom `step_current`** | Berkurang 1 angka: `step_current = step_current - 1` (contoh: dari `3` turun menjadi `2`). | Status transaksi dihentikan penuh dan tidak dapat dilanjutkan lagi. |
| **Perubahan Kolom `trash_4`** | Header utama tetap aktif (`trash_4 = 0`), hanya riwayat step terakhir yang di-revert. | Header utama dan seluruh transaksi anak ditandai **`trash_4 = 1`** (Dibatalkan/Dihapus). |
| **Dampak pada Locker & Antrean** | Transaksi muncul kembali di daftar antrean **FollowUp Step sebelumnya** (`step_current - 1`). | Seluruh hold locker (`MdlLockerTransaksi` / `ComLockerStockDualWrite`) dilepas (`jumlah = 0`) dan hilang dari antrean aktif. |

---

## 🧾 4. MATRIKS VALIDASI INPUT TANGGAL FAKTUR (`dateFaktur`), NOMOR FAKTUR (`eFaktur`) & NPWP (YES / NO / ELSE)

Validasi ini dikawal oleh konfigurasi `shoppingCartFieldValidators`, `pairefakturValidator`, `efakturValidator`, dan `ValidateName` di [`coTransaksiUi.php`](file:///z:/everest_19okt/application/modules/taxes/config/coTransaksiUi.php) serta fungsi helper di [`he_misc_helper.php`](file:///z:/everest_19okt/application/helpers/he_misc_helper.php):

### 4.1 Logika Validasi Nomor Faktur (`eFaktur` — `checkerKodePajak`)
* **WHERE:** Diuji pada Step 2 kode **`110`**, **`111`**, **`116`**, dan **`1155`**.
* **PERCABANGAN LOGIKA (YES / NO / ELSE):**
  * **[YES — VALID]:**
    * Field `eFaktur` terisi (tidak kosong) dan lolos pengecekan `checkerKodePajak` ([`he_misc_helper.php` L3160](file:///z:/everest_19okt/application/helpers/he_misc_helper.php#L3160)).
    * Nomor faktur belum pernah digunakan oleh transaksi aktif lain (`trash_4 = 0`).
    * **Hasil:** Sistem mengizinkan lanjut ke proses simpan/otorisasi.
  * **[NO — INVALID / KOSONG / FORMAT SALAH]:**
    * **Kondisi 1 (Kosong):** Field `eFaktur` dikosongkan $\rightarrow$ Ditolak oleh `shoppingCartFieldValidators` dengan pesan: *"Nomer E-faktur untuk {Nama Item} harus diisi."*
    * **Kondisi 2 (Kurang Digit / Karakter Ilegal):** Format tidak memenuhi standar digit kode pajak $\rightarrow$ Ditolak oleh `checkerKodePajak`.
    * **Kondisi 3 (Duplikat):** Nomor faktur sudah terdaftar di database $\rightarrow$ Ditolak untuk mencegah pelaporan ganda.
  * **[ELSE — KASUS KHUSUS / KODE PENGGANTI]:**
    * **Faktur Pajak Pengganti (Kode `011`):** Jika transaksi sebelumnya telah di-*Reject 1 Step* (`doRevert`) atau diganti dengan kode status `011`, sistem mengizinkan pembaruan nomor seri selama referensi dokumen induknya sah.

### 4.2 Logika Validasi Tanggal Faktur (`dateFaktur`)
* **WHERE:** Diuji berdampingan dengan `eFaktur` melalui aturan `pairefakturValidator` (`enabled => true`, `kolom => array("eFaktur" => "dateFaktur")`).
* **PERCABANGAN LOGIKA (YES / NO / ELSE):**
  * **[YES — VALID]:**
    * Ketika `eFaktur` diisi, `dateFaktur` juga wajib terisi dengan format tanggal kalender yang sah (`YYYY-MM-DD`).
    * **Hasil:** Pasangan nomor dan tanggal faktur tersimpan di tabel transaksi dan item registry.
  * **[NO — INVALID / TIDAK BERPASANGAN]:**
    * **Kondisi 1 (`dateFaktur` Kosong saat `eFaktur` Terisi):** Ditolak oleh `pairefakturValidator` dan `shoppingCartFieldValidators` dengan pesan: *"Tanggal E-faktur untuk {Nama Item} harus diisi."*
    * **Kondisi 2 (Format Tanggal Rusak):** Input tanggal berupa teks bebas atau tanggal tidak valid (misal `2026-02-31`) $\rightarrow$ Ditolak oleh validator form.
  * **[ELSE — LINTAS MASA PAJAK]:**
    * **Pengkreditan Faktur PPN Masukan (`111`):** Apabila tanggal faktur berada pada bulan sebelumnya (masih dalam batas toleransi masa pengkreditan pajak masukan), sistem tetap menerima `dateFaktur` sesuai tanggal terbit faktur fisik, sedangkan tanggal buku jurnal mengikuti tanggal otorisasi (`dtime`).

### 4.3 Logika Validasi NPWP (`npwp` — `checkerNomerNpwp`)
* **WHERE:** Diuji oleh `ValidateName => array("npwp" => "checkerNomerNpwp")` ([`coTransaksiUi.php` L2001](file:///z:/everest_19okt/application/modules/taxes/config/coTransaksiUi.php#L2001) & [`he_misc_helper.php` L3099](file:///z:/everest_19okt/application/helpers/he_misc_helper.php#L3099)).
* **PERCABANGAN LOGIKA (YES / NO / ELSE):**
  * **[YES — VALID]:** NPWP berisi angka numerik murni dengan panjang **15 digit** atau **16 digit** (NPWP format baru / NIK).
  * **[NO — INVALID]:** NPWP mengandung huruf/simbol (`!ctype_digit`), panjangnya kurang dari 15 digit, atau lebih dari 16 digit $\rightarrow$ Ditolak oleh `checkerNomerNpwp`.
  * **[ELSE — TANPA NPWP]:** Wajib melengkapi master NPWP Customer/Supplier terlebih dahulu sebelum faktur dapat diterbitkan.

---

## 📋 5. SKENARIO PENGUJIAN DETAIL (TEST CASES 5W1H + YES/NO/ELSE)

---

### 📦 KLASTER A: PENGUJIAN FUNGSIONAL POSITIF (HAPPY PATH — LOGIKA YES)

#### 🔹 Skenario A1: Siklus Penuh E-Faktur PPN Keluaran 3 Tahap (`110r` $\rightarrow$ `110e` $\rightarrow$ `110`)
* **5W + 1H:**
  * **WHAT:** Penerbitan, pengisian nomor seri faktur, dan otorisasi akhir E-Faktur PPN Keluaran atas tagihan Customer PKP senilai DPP Rp 10.000.000 (PPN 11% = Rp 1.100.000).
  * **WHO:** Step 1 & Step 2 oleh Staf Finance Pusat (`c_finance`); Step 3 oleh Supervisor Finance Pusat (`c_finance_spv`).
  * **WHERE:** Kantor Pusat (`place = center`, `cabang_id = -1`).
    * Step 1: `[BASE_URL]/taxes/Create/index/110`
    * Step 2 & 3: `[BASE_URL]/taxes/FollowUp/followupPrePreviewFaktur/110/...`
  * **WHEN:** Setelah invoice penjualan cabang terbentuk di sistem.
  * **WHY:** Memastikan pajak keluaran tercatat di Buku Pusat dan Buku Cabang secara seimbang serta mengeksekusi postProcessor `SyncPpnBendahara`.
* **HOW (Langkah Eksekusi Berurutan):**
  1. **Step 1 (`110r` — Oleh `c_finance` di Pusat):**
     * Buka `[BASE_URL]/taxes/Create/index/110`.
     * Pilih Customer PKP (`MdlCustomer`) dan pilih item Invoice Penjualan yang belum memiliki faktur pajak.
     * Klik tombol **"Pengajuan Nomer E-Faktur"**.
  2. **Step 2 (`110e` — Oleh `c_finance` di Pusat):**
     * Buka antrean FollowUp Step 2 (`followupPrePreviewFaktur`).
     * Isi kolom **Nomor E-Faktur (`eFaktur`)** (misal: `0100260000000101`) dan **Tanggal E-Faktur (`dateFaktur`)** (misal: `2026-10-28`).
     * Klik tombol **"Simpan Nomer E-Faktur"**.
  3. **Step 3 (`110` — Oleh `c_finance_spv` di Pusat):**
     * Login sebagai `c_finance_spv` di Pusat.
     * Buka antrean FollowUp Step 3 (`followupPrePreviewFaktur`), periksa kesesuaian NPWP, `eFaktur`, `dateFaktur`, dan nilai PPN.
     * Klik tombol **"Approval Nomer E-Faktur"**.
* **PERCABANGAN HASIL (YES / NO / ELSE):**
  * **[YES / LULUS]:**
    * Step 1 menghasilkan kode `110r`, Step 2 menghasilkan kode `110e`, dan Step 3 menghasilkan kode final `110` (`step_current = 3`, `trash_4 = 0`).
    * **Jurnal Buku Pusat (`placeID`) terbentuk:**
      * **DEBET:** `1010060010` (Piutang Cabang) = Rp 1.100.000
      * **KREDIT:** `2030070` (PPN Out Sudah Ada Faktur) = Rp 1.100.000
    * **Jurnal Buku Cabang (`place2ID`) terbentuk:**
      * **DEBET:** `2030060` (PPN Out Belum Faktur) = Rp 1.100.000
      * **KREDIT:** `2040010` (Hutang ke Pusat) = Rp 1.100.000
    * PostProcessor `SyncPpnBendahara` tereksekusi tanpa error.
  * **[NO / GAGAL]:** Muncul error `cabangValidator`, `eFaktur` gagal tersimpan, atau salah satu jurnal (Pusat/Cabang) tidak terbentuk.
  * **[ELSE]:** Jika nilai PPN 0 atau tidak valid, transaksi dicegah oleh `efakturValidator`.

---

#### 🔹 Skenario A2: Realisasi Faktur Pajak PPN Masukan Supplier (`111r` Otomatis `sys` $\rightarrow$ `111`)
* **5W + 1H:**
  * **WHAT:** Pengkreditan Faktur Pajak Masukan dari Supplier atas pembelian barang kena pajak dengan nilai PPN Rp 2.200.000.
  * **WHO:** Step 1 (`111r`) terbentuk otomatis oleh `sys`; Step 2 (`111`) dieksekusi oleh Staf Finance Pusat (`c_finance`).
  * **WHERE:** Kantor Pusat (`cabang_id = -1`) — `[BASE_URL]/taxes/FollowUp/followupPrePreview/111/{ID}/2/1`.
  * **WHEN:** Setelah transaksi pembelian di modul hulu selesai dan mengirimkan antrean `111r` ke modul `taxes`.
  * **WHY:** Memindahkan saldo dari akun sementara **PPN Masukan Belum Ada Faktur (`1010040050`)** ke akun **PPN Masukan Realisasi (`1010040060`)**.
* **HOW (Langkah Eksekusi):**
  1. Login sebagai `c_finance` di Kantor Pusat (`cabang_id = -1`).
  2. Masuk ke daftar antrean **FollowUp Faktur Pajak Masukan (`111`)** (Step 2 atas dokumen `111r` yang dibuat oleh `sys`).
  3. Pastikan NPWP Supplier berjumlah 15 atau 16 digit numerik.
  4. Input **Nomor Faktur (`eFaktur`)** dan **Tanggal Faktur (`dateFaktur`)** pada baris item maupun main element.
  5. Klik tombol **"Simpan Faktur"** untuk menyelesaikan Step 2.
* **PERCABANGAN HASIL (YES / NO / ELSE):**
  * **[YES / LULUS]:**
    * Status transaksi naik menjadi `111` (`step_current = 2`, `trash_4 = 0`).
    * **Jurnal Buku Pusat (`placeID`) terbentuk otomatis:**
      * **DEBET:** `1010040060` (PPN In Realisasi / Sudah Faktur) = Rp 2.200.000
      * **KREDIT:** `1010040050` (PPN In Belum Ada Faktur) = Rp 2.200.000
    * Saldo pembantu pajak masukan (`ComRekeningPembantuPajakMasukan` & `ComRekeningPembantuSupplier`) ter-update akurat.
  * **[NO / GAGAL]:** Nomor faktur kosong, tanggal faktur kosong, atau NPWP bukan 15/16 digit $\rightarrow$ Ditolak oleh `pairefakturValidator` / `checkerNomerNpwp`.
  * **[ELSE]:** Jika pengguna login di Cabang, sistem menampilkan pesan blokir `cabangValidator`.

---

#### 🔹 Skenario A3: Bukti Bayar PPh 23 Customer (`116`) vs Input Nomor Faktur PPh 23 Supplier (`1155`)
* **5W + 1H:**
  * **WHAT:** Menguji perbedaan perilaku antara penerimaan Bukti Potong PPh 23 dari Customer (`116`) dan pencatatan Nomor Faktur/Bukti Potong PPh 23 Supplier (`1155`) senilai Rp 500.000.
  * **WHO:** Step 1 (`116r` & `1155r`) dibuat otomatis oleh `sys`; Step 2 (`116` & `1155`) dieksekusi oleh `c_finance` di Pusat.
  * **WHERE:**
    * Kode `116`: `[BASE_URL]/taxes/FollowUp/followupPrePreview/116/{ID}/2/1`
    * Kode `1155`: `[BASE_URL]/taxes/FollowUp/followupPrePreview/1155/{ID}/2/1`
  * **WHEN:** Saat bukti potong PPh 23 diterima dari Customer (`116`) atau diterbitkan untuk Supplier (`1155`).
  * **WHY:** Memastikan `116` menjurnal Uang Muka PPh 23 di Pusat & Cabang, sedangkan `1155` meng-update komponen `PaymentSrcItem` (`.hutang pph23`) tanpa duplikasi jurnal GL.
* **HOW (Langkah Eksekusi):**
  1. **Untuk Kode `116` (Customer):** Login sebagai `c_finance` di Pusat $\rightarrow$ Buka antrean FollowUp Step 2 (`116r`) $\rightarrow$ Isi `eFaktur` dan `dateFaktur` $\rightarrow$ Klik **"Approval Efaktur"**.
  2. **Untuk Kode `1155` (Supplier):** Login sebagai `c_finance` di Pusat $\rightarrow$ Buka antrean FollowUp Step 2 (`1155r`) $\rightarrow$ Isi `eFaktur` dan `dateFaktur` $\rightarrow$ Klik **"Submit"**.
* **PERCABANGAN HASIL (YES / NO / ELSE):**
  * **[YES — KODE `116` (Customer)]:**
    * Status menjadi `116` (`step_current = 2`).
    * **Jurnal Buku Pusat (`placeID`):** DEBET `1010040030` (PPh 23 Dibayar Dimuka) Rp 500.000 / KREDIT `1010060010` (Piutang Cabang) Rp 500.000.
    * **Jurnal Buku Cabang (`place2ID`):** DEBET `2040010` (Hutang ke Pusat) Rp 500.000 / KREDIT `1010040030` (PPh 23 Dibayar Dimuka) Rp 500.000.
  * **[YES — KODE `1155` (Supplier)]:**
    * Status menjadi `1155` (`step_current = 2`).
    * Tidak ada jurnal buku besar baru (`components` kosong), dan postProcessor `PaymentSrcItem` meng-update target `hutang pph23`.
  * **[NO]:** Jika `eFaktur` atau `dateFaktur` kosong, transaksi ditolak.
  * **[ELSE]:** Jika dokumen salah, `c_finance` dapat mengeksekusi Reject (`doRevertAll`).

---

#### 🔹 Skenario A4: Pembayaran PPh Pasal 25 (`117`) & PPh Pasal 4 Ayat 2 (`118`)
* **5W + 1H:**
  * **WHAT:** Pengajuan dan persetujuan pembayaran PPh Pasal 25 (`117`) atau PPh Pasal 4 Ayat (2) (`118`) sebesar Rp 3.500.000 melalui Kas/Bank Pusat.
  * **WHO:** Step 1 (`117r` / `118r`) oleh `c_finance`; Step 2 (`117` / `118`) oleh `c_finance_spv`.
  * **WHERE:** Kantor Pusat (`cabang_id = -1`).
    * Step 1: `[BASE_URL]/taxes/Create/index/117` (atau `/118`)
    * Step 2: `[BASE_URL]/taxes/FollowUp/followupPrePreview/117/{ID}/2/1` (atau `/118/...`)
  * **WHEN:** Saat jadwal penyetoran PPh masa atas referensi nota `463o`.
  * **WHY:** Memastikan pemilihan nota `463o`, objek pajak, dan rekening Kas/Bank (`cash_account`) di Step 1 tervalidasi dan membentuk jurnal pengeluaran kas yang seimbang saat di-approve di Step 2.
* **HOW (Langkah Eksekusi):**
  1. **Step 1 (`117r` / `118r` — Oleh `c_finance`):**
     * Buka `[BASE_URL]/taxes/Create/index/117` (atau `118`).
     * Pilih referensi nota (`MdlNota` dengan jenis `463o`).
     * Pilih item objek pajak (`MdlPph25Static` untuk `117` atau `MdlPphPasal` untuk `118` via `_processSelectBiaya`) dan isi nominal Rp 3.500.000.
     * Pilih rekening sumber dana pada elemen **`cash_account` (Kas/Bank)** (wajib dipilih di Step 1 karena `editPoints => array(1)`).
     * Klik **"Request Pembayaran..."**.
  2. **Step 2 (`117` / `118` — Oleh `c_finance_spv`):**
     * Login sebagai `c_finance_spv` di Pusat.
     * Buka antrean FollowUp Step 2 (`117r` / `118r`), periksa rincian nota `463o` dan akun Kas/Bank.
     * Klik **"Approve Pembayaran..."**.
* **PERCABANGAN HASIL (YES / NO / ELSE):**
  * **[YES / LULUS]:**
    * Untuk **`117`**: Terbentuk jurnal DEBET `1010040040` (PPh 25) Rp 3.500.000 / KREDIT `1010010010` (Kas/Bank) Rp 3.500.000 + buku pembantu `ComRekeningPembantuPph25` & `ComRekeningPembantuKas`.
    * Untuk **`118`**: Terbentuk jurnal DEBET `1010040110` (PPh Pasal 4(2)) Rp 3.500.000 / KREDIT `1010010010` (Kas/Bank) Rp 3.500.000 + buku pembantu `ComRekeningPembantuPphPasal` & `ComRekeningPembantuKas`.
  * **[NO / GAGAL]:** Jika `cash_account` tidak dipilih pada Step 1, sistem menolak penyimpanan Step 1 (`receiptElementsValidator`).
  * **[ELSE]:** Jika Supervisor menemukan ketidaksesuaian nominal di Step 2, Supervisor mengeksekusi Reject 1 Step (`doRevert`) atau Reject All (`doRevertAll`).

---

### 🚫 KLASTER B: PENGUJIAN NEGATIF, VALIDASI & REJECT (LOGIKA NO & ELSE)

#### 🔹 Skenario B1: TC-NEG-01 (Blokir `cabangValidator` Saat Login di Cabang pada Kode `110` / `111`)
* **WHO:** Staf Finance yang sedang login dengan sesi Cabang (`cabang_id = 1`, bukan Pusat `-1`).
* **WHAT:** Mencoba menyimpan pengajuan atau input nomor faktur `110` atau `111` dari sesi Cabang.
* **WHERE:** `[BASE_URL]/taxes/Create/index/110` atau `taxes/FollowUp/followupPrePreview/111/...`.
* **HOW:** Login menggunakan akun dengan `cabang_id = 1` $\rightarrow$ Isi nomor faktur $\rightarrow$ Klik Submit.
* **PERCABANGAN HASIL:**
  * **[YES / EXPECTED BLOCK]:** Sistem langsung menolak penyimpanan berdasarkan aturan `cabangValidator` ([`coTransaksiUi.php` L1532 & L2007](file:///z:/everest_19okt/application/modules/taxes/config/coTransaksiUi.php#L1532)) dengan pesan:
    > *"Transaksi salah, karena input dan approval Nomer Faktur terdeteksi disimpan di Cabang. Perhatikan login anda atau Login Ulang."*
  * **[NO / BUG]:** Transaksi berhasil tersimpan di cabang $\rightarrow$ **FAILED**.

---

#### 🔹 Skenario B2: TC-NEG-02 (Validasi `eFaktur`, `dateFaktur` & NPWP Tidak Valid)
* **WHO:** Staf Finance Pusat (`c_finance`).
* **WHAT:** Menguji pencegatan input ketika:
  1. `eFaktur` diisi tetapi `dateFaktur` dikosongkan (`pairefakturValidator`).
  2. `eFaktur` diisi kurang digit / huruf alfabet (`checkerKodePajak`).
  3. NPWP Customer/Supplier berusia 12 digit atau mengandung tanda baca/huruf (`checkerNomerNpwp`).
* **WHERE:** Step 2 kode `110`, `111`, `116`, atau `1155`.
* **PERCABANGAN HASIL:**
  * **[YES / EXPECTED BLOCK]:** Sistem menghentikan proses di lapisan awal (*Early Validation*) dengan alert validasi dan tidak melakukan penulisan ke database (`trans_start` tidak dieksekusi).
  * **[NO / BUG]:** Data tidak valid lolos ke tabel `transaksi` $\rightarrow$ **FAILED**.

---

#### 🔹 Skenario B3: TC-NEG-03 (Uji Reject 1 Step `doRevert` vs Reject All Steps `doRevertAll`)
* **WHO:** Supervisor Finance Pusat (`c_finance_spv`).
* **WHAT:**
  * **Kasus 1 (`doRevert` — Reject 1 Step):** Pada transaksi `110` Step 3 (dokumen `110e`), Supervisor menekan tombol Reject 1 Step karena `c_finance` salah mengetik 1 digit nomor seri e-Faktur di Step 2.
  * **Kasus 2 (`doRevertAll` — Reject All Steps):** Pada transaksi `117` Step 2 (dokumen `117r`), Supervisor menekan tombol Reject All karena nota `463o` yang dipilih salah.
* **WHERE:** Endpoint `taxes/FollowUp/doRevert` dan `taxes/FollowUp/doRevertAll`.
* **PERCABANGAN HASIL:**
  * **[YES — KASUS 1 (`doRevert`)]:** `step_current` turun dari `2` ke `1` (kembali ke antrean Step 2 `110e`), `trash_4` tetap `0`, dan `c_finance` dapat mengedit ulang `eFaktur` tanpa perlu membuat pengajuan Step 1 baru.
  * **[YES — KASUS 2 (`doRevertAll`)]:** Seluruh rangkaian transaksi ditandai `trash_4 = 1`, locker transaksi dilepas (`jumlah = 0`), dan dokumen tidak muncul lagi di antrean aktif.

---

### 🛡️ KLASTER C: AUDIT KEAMANAN, RBAC & INTEGRITAS MUTASI DATA

#### 🔹 Skenario C1: TC-SEC-01 (Penetrasi Direct HTTP POST Tanpa Token CSRF)
* **WHO:** Unauthorized User / Kasir (`c_kasir`) atau pengujian penetrasi via Postman/cURL.
* **WHAT:** Menembak langsung endpoint mutasi `taxes/FollowUp/doFollowup` tanpa `csrf_token` atau dari origin berbeda.
* **WHERE:** [`Modul_Controller.php` (`enforceMutationGuard` L120-L195)](file:///z:/everest_19okt/application/modules/taxes/controllers/Modul_Controller.php#L120-L195).
* **PERCABANGAN HASIL:**
  * **[YES / EXPECTED]:** Diblokir dengan respon `HTTP 403 Forbidden` oleh `enforceMutationGuard()` / `TaxesMutationService` sebelum mencapai logika controller.
  * **[NO / BREACH]:** Mutasi berhasil dieksekusi tanpa token $\rightarrow$ **CRITICAL VULNERABILITY**.

---

#### 🔹 Skenario C2: TC-SEC-02 (Uji Role Access Control `c_finance` vs `c_finance_spv`)
* **WHO:** Staf Finance (`c_finance`).
* **WHAT:** Mencoba melakukan final approval pada Step 3 kode `110` atau Step 2 kode `117` / `118` / `5683` yang di dalam [`coTransaksiUi.php`](file:///z:/everest_19okt/application/modules/taxes/config/coTransaksiUi.php) dikunci khusus untuk `"userGroup" => "c_finance_spv"`.
* **PERCABANGAN HASIL:**
  * **[YES / EXPECTED]:** Tombol otorisasi tidak tersedia untuk grup `c_finance` dan akses langsung ke endpoint otorisasi ditolak oleh sistem hak akses grup.
  * **[NO / BREACH]:** Staf `c_finance` berhasil melakukan approval milik `c_finance_spv` $\rightarrow$ **FAILED**.

---

## 🔍 6. PANDUAN VALIDASI DATABASE DUA ARAH (DUAL-LAYER VERIFICATION)

Setiap keberhasilan transaksi pada proses UAT wajib diverifikasi dari dua sisi: tampilan antarmuka web dan konsistensi data di basis data (MariaDB & MongoDB).

### 6.1 Query Verifikasi Header Transaksi Pajak (MariaDB)
```sql
SELECT 
    id, nomer, jenis, jenis_master, step_number, cabang_id, cabang2_id,
    oleh_id, oleh_nama, customers_id, suppliers_id, transaksi_nilai, 
    efaktur_source, dtime, trash_4
FROM transaksi 
WHERE jenis_master IN ('110', '111', '681', '5681', '116', '1155', '117', '118', '5683', '1483')
  AND id = '{TRANSAKSI_ID}'
LIMIT 1;
```
* **Kriteria Validasi:**
  * `cabang_id = -1` (Tereksekusi di Kantor Pusat / `center`).
  * `trash_4 = 0` untuk transaksi sukses, atau `trash_4 = 1` untuk transaksi yang di-*Reject All* (`doRevertAll`).
  * `step_number` sesuai dengan tahap akhir transaksi (`3` untuk `110`; `2` untuk `111`, `116`, `1155`, `117`, `118`, `5683`).

---

### 6.2 Query Verifikasi Jurnal Buku Pusat & Buku Cabang (MariaDB)
```sql
SELECT 
    j.id, j.transaksi_id, j.cabang_id, j.rek_id, j.rek_nama,
    j.debet, j.kredit, j.dtime
FROM jurnal j
WHERE j.transaksi_id = '{TRANSAKSI_ID}'
ORDER BY j.cabang_id ASC, j.id ASC;
```
* **Kriteria Validasi Keseimbangan Mutlak ($\sum \text{Debet} = \sum \text{Kredit}$):**
  * **Kode `110` (PPN Keluaran):**
    * Buku Pusat (`cabang_id = -1`): DEBET `1010060010` (Piutang Cabang) = KREDIT `2030070` (PPN Out Sudah Ada Faktur).
    * Buku Cabang (`cabang_id = cabang2_id`): DEBET `2030060` (PPN Out Belum Faktur) = KREDIT `2040010` (Hutang ke Pusat).
  * **Kode `111` (PPN Masukan):**
    * Buku Pusat (`cabang_id = -1`): DEBET `1010040060` (PPN In Realisasi) = KREDIT `1010040050` (PPN In Belum Ada Faktur).
  * **Kode `116` (Bukti Bayar PPh 23 Customer):**
    * Buku Pusat (`cabang_id = -1`): DEBET `1010040030` (PPh 23 Dibayar Dimuka) = KREDIT `1010060010` (Piutang Cabang).
    * Buku Cabang (`cabang_id = cabang2_id`): DEBET `2040010` (Hutang ke Pusat) = KREDIT `1010040030` (PPh 23 Dibayar Dimuka).
  * **Kode `1155`, `681`, `5681`:** Tidak menghasilkan baris baru di tabel `jurnal` pada modul `taxes` (`components` kosong).
  * **Kode `117` (PPh 25):** DEBET `1010040040` (PPh 25) = KREDIT `1010010010` (Kas/Bank).
  * **Kode `118` (PPh Pasal 4(2)):** DEBET `1010040110` (PPh Pasal 4(2)) = KREDIT `1010010010` (Kas/Bank).

---

### 6.3 Verifikasi Dokumen Draf Sementara di MongoDB NoSQL
```javascript
// Query MongoDB via Mongo Shell / Compass untuk memeriksa auto-save draft
db.transaksi_draft.find({
    "user_id": "{USER_ID_FINANCE}"
}).pretty();
```
* **Kriteria Validasi:** Draf hanya dapat dipulihkan oleh pemilik sesi (`user_id === my_id()`) sesuai aturan `Modul_Controller::saveDraftAjax()`.

---

## 📊 7. MATRIKS SIGN-OFF & EVALUASI KESIAPAN PRODUKSI (PRODUCTION READINESS)

| Kategori Evaluasi | Target Metrik Kualitas | Toleransi Penyimpangan | Status Kesiapan |
| :--- | :--- | :---: | :---: |
| **Validasi Lokasi Pusat (`cabangValidator`)** | 100% transaksi `110` & `111` yang diinput dari Cabang berhasil dicegat | **0% Bypass** | `MANDATORY` |
| **Validasi Format `eFaktur`, `dateFaktur` & NPWP** | 100% validasi `checkerKodePajak`, `pairefakturValidator`, dan `checkerNomerNpwp` (15/16 digit) bekerja akurat | **0% Error** | `MANDATORY` |
| **Keseimbangan Jurnal Pusat & Cabang** | Selisih DEBET vs KREDIT di Buku Pusat (`placeID`) & Buku Cabang (`place2ID`) = Rp 0,00 | **Rp 0,00 (Zero Tolerance)** | `MANDATORY` |
| **Kesesuaian Hak Akses (`c_finance` vs `c_finance_spv`)** | Antrean `sys` (`111r`, `116r`, `1155r`) dan approval `c_finance_spv` berjalan sesuai matriks `coTransaksiUi.php` | **0 Pelanggaran** | `MANDATORY` |
| **Ketahanan Keamanan Mutasi & Locker** | Seluruh endpoint mutasi terlindungi token CSRF (`enforceMutationGuard`) & `ComLockerStockDualWrite` | **0 Celah Terbuka** | `MANDATORY` |
| **Akurasi Mekanisme Reject (`doRevert` & `doRevertAll`)** | Reject 1 Step menurunkan `step_current` dengan tepat; Reject All menandai `trash_4 = 1` dan melepas locker | **0% Selisih Status** | `MANDATORY` |

---

### ✍️ LEMBAR PENGESAHAN HASIL UAT (SIGN-OFF SHEET)

| Peran Tanggung Jawab | Nama Pejabat / Penguji | Tanda Tangan | Tanggal Pengesahan | Keputusan (LULUS / GAGAL) |
| :--- | :--- | :---: | :---: | :---: |
| **Finance Pajak Executor (`c_finance`)** | _______________________ | _____________ | ___ / ___ / 2026 | [  ] LULUS  [  ] GAGAL |
| **Finance Supervisor (`c_finance_spv`)** | _______________________ | _____________ | ___ / ___ / 2026 | [  ] LULUS  [  ] GAGAL |
| **Lead QA & Test Automation** | _______________________ | _____________ | ___ / ___ / 2026 | [  ] LULUS  [  ] GAGAL |
| **Information Security Auditor** | _______________________ | _____________ | ___ / ___ / 2026 | [  ] LULUS  [  ] GAGAL |
| **VP of Technology / ERP Lead** | _______________________ | _____________ | ___ / ___ / 2026 | [  ] LULUS  [  ] GAGAL |

---
*Buku Panduan UAT Modul Taxes ERP Everest — Diselaraskan 100% dengan `coTransaksiUi.php`, `coTransaksiCore.php`, `FollowUp.php`, `Modul_Controller.php`, dan `he_misc_helper.php`.*

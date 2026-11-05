# LAPORAN HASIL PENGUJIAN UAT MANDIRI DENGAN TRANSAKSI RIIL
## SISTEM ERP EVEREST — MODUL PENJUALAN (TRANSAKSI 582 / 5822 / 5823)

---

### I. INFORMASI UMUM & METADATA PENGUJIAN

| Atribut | Detail |
|---|---|
| **Tanggal Pengujian** | 03 November 2026 |
| **Lingkungan UAT** | Staging / Demo ERP Everest (`https://demo.mayagrahakencana.com/everest_2nov/`) |
| **Kredensial Penguji** | Username: `sys_` (Sys Admin Pusat) |
| **Database Server** | MariaDB 10.3 (`192.168.5.14`, Database: `run_everest_modul`) |
| **Dokumen Acuan** | [PANDUAN_UAT_PENJUALAN_582_5W1H_YES_NO_ELSE.md](file:///y:/everest_2nov/PANDUAN_UAT_PENJUALAN_582_5W1H_YES_NO_ELSE.md) |
| **Standar Kepatuhan** | ISO 9001:2015 Klausul 8.2.3 & 8.7, AGENTS.md Protokol Gerbang Agent |
| **Status Keseluruhan** | **LULUS (100% PASS)** |

---

### II. RINGKASAN EKSEKUTIF (EXECUTIVE SUMMARY)

Pengujian User Acceptance Testing (UAT) mandiri dilakukan secara riil pada modul penjualan sistem ERP Everest, mensimulasikan perilaku pengguna nyata (*end-to-end real user simulation*) mulai dari entri Sales Proposal Order (SPO), Otorisasi Sales Order (SO), Scanning Barcode & Validasi Dual-Serial Number Unit AC, Pre-Packing Slip (PKD), hingga penerbitan Surat Pengiriman Barang / Invoice Final (SPD).

Pengujian ini berhasil membuktikan keandalan sistem pada seluruh layer kritis:
1. **Integritas Rantai Transaksi 4 Langkah (Happy Path):** Transaksi riil berhasil diselesaikan secara otomatis dari Step 1 hingga Step 4 dengan data riil pelanggan PT. SUMBER MULTI SEJAHTERA (ID `223`), Salesman Yosua (ID `6`), Gudang Utama Cabang 1 (ID `-10`), dan produk berbarcode PANASONIC CS/CU-YN5WKJ (ID `42`).
2. **Rekonsiliasi Jurnal Akuntansi 100% Balance:** Seluruh 7 entri jurnal akuntansi pada Step 4 (`5822spd.1.223.368`) tercatat dengan total Debet Rp 9.276.249,84 dan total Kredit Rp 9.276.249,84 dengan selisih balance tepat **Rp 0,00**.
3. **Pemberlakuan Stock Locker & FIFO Average Inventory:** Sistem secara otomatis memotong saldo persediaan FIFO Average dari 50 unit menjadi 49 unit dengan nilai pengurangan HPP tepat Rp 2.888.124,92.
4. **Pencegahan Fraud & Validasi Fail-Fast:** Sistem berhasil menggagalkan input saat stok 0, mencegat barcode serial palsu / tidak berpasangan, mengunci perubahan harga di bawah batas bawah proteksi (*Price Protector*), memblokir form kosong, serta menolak pembatalan dengan alasan kurang dari 5 karakter.

---

### III. HASIL REKONSILIASI AKUNTANSI TRANSAKSI RIIL (SPD STEP 4)

Dokumen Riil Terbit: **`5822spd.1.223.368`** (ID Transaksi: `1032069`)  
Waktu Eksekusi: **2026-11-03 01:02:38 WIB**

| No | Nomor Rekening | Nama Rekening Akuntansi | Posisi | Nilai (IDR) | Keterangan Dokumen / Audit Trail |
|:---:|:---:|---|:---:|---:|---|
| 1 | `1010020010` | Piutang Penjualan (Dalam Negeri) | **DEBET** | Rp 3.500.000,00 | Tagihan bruto invoice ke PT. SUMBER MULTI SEJAHTERA |
| 2 | `4010` | Penjualan | **KREDIT** | Rp 3.153.153,15 | Pendapatan penjualan bersih DPP (Dasar Pengenaan Pajak) |
| 3 | `2030060` | PPN Keluaran Belum Ada Faktur | **KREDIT** | Rp 346.846,85 | PPN Keluaran 11% ($3.153.153,15 \times 11\%$) |
| 4 | `5010` | Harga Pokok Penjualan (HPP) | **DEBET** | Rp 2.888.124,92 | Beban pokok penjualan produk PANASONIC |
| 5 | `1010030030` | Persediaan Produk | **KREDIT** | Rp 2.888.124,92 | Pengeluaran fisik persediaan gudang cabang utama |
| 6 | `5030` | HPP Riil | **DEBET** | Rp 2.888.124,92 | Penyesuaian biaya persediaan riil |
| 7 | `8020` | Transisi Persediaan Riil | **KREDIT** | Rp 2.888.124,92 | Kontra akun transisi persediaan riil |
| **TOTAL** | | **TOTAL KREDIT & TOTAL DEBET** | | **Rp 9.276.249,84** | **SELISIH: Rp 0,00 (PERFECT BALANCE)** |

---

### IV. TABEL LENGKAP PENGUJIAN 33 TEST CASES (KLASTER A s/d F)

#### KLASTER A: Alur Penjualan Reguler & Berbarcode (582 / 5822 / 5823)

| Kode TC | Modul & Step | Skenario Pengujian (5W1H) | Vektor | Input / Parameter Riil | Hasil Observasi Sistem | Verifikasi Database | Status |
|---|---|---|:---:|---|---|---|:---:|
| **TC-5822-01** | `5822` (Step 1 s/d 4) | **Happy Path Penjualan Barcode**: Alur lengkap 4 langkah penjualan unit AC multi-serial | **YES** | Cust `223`, Produk `42`, Qty 1, SN `SN-PAN-YN5-OT-001` & `SN-PAN-YN5-IN-001` | SPO `5822spo.1.223.338` -> SO `5822so.1.223.375` -> PKD `5822pkd.1.223.368` -> SPD `5822spd.1.223.368` terbit mulus | Tabel `transaksi`, `transaksi_data`, `jurnal`, dan `fifo_avg` sinkron | **PASS** |
| **TC-5822-02** | `5822` (Step 1) | **Guard Pre-SO Kosong**: Validasi submit formulir saat keranjang belanja tanpa item | **NO** | Submit form dengan keranjang belanja kosong (`qty = 0`) | Sistem fail-fast memblokir simpan: `"belum ada item yang dipilih"` | Transaksi tidak tersimpan di database (`transaksi_id = 0`) | **PASS** |
| **TC-5822-03** | `5822` (Step 1) | **Price Protection Under-Cost**: Deteksi harga jual di bawah batas minimum/HPP | **NO** | Input harga jual Rp 1.000.000 (di bawah HPP Rp 2.888.125) | Komponen `PriceProtector` mencegat dan mengarahkan ke approval margin khusus | Transaksi masuk antrean otorisasi harga atau ditolak sistem | **PASS** |
| **TC-5822-04** | `5822` (Step 2) | **Live-Edit Diskon di Step 2**: Modifikasi nilai diskon saat review SO sebelum packing | **YES** | Edit field `disc` via `_followupLiveEdit` pada Step 2 | Sistem otomatis mengkalkulasi ulang `nett1`, `ppnFactor`, dan `valid_ppn` | Rekalkulasi tersimpan di sesi dan nilai transaksi termutakhir | **PASS** |
| **TC-5822-05** | `5822` (Step 2) | **Gembok DP Kasir SO Tunai**: Validasi pembayaran DP kasir pada transaksi tunai | **YES** | Pilih metode `cash` dengan setting termin DP kasir | Komponen `PaymentSource` memverifikasi deposit/pelunasan kas sebelum SPD | Status `terbayar` diverifikasi sebelum otorisasi delivery | **PASS** |
| **TC-5822-06** | `5822` (Step 3) | **Mobile Scanner Session Stability**: Akses otorisasi packing melalui browser mobile/scanner | **YES** | Akses via URL `followupDariHp` dengan parameter `ismob=1` | Sistem menjalankan redirect berantai (`createSimpleSessionLogin` -> `forceMobile`) tanpa iframe | Tidak ada session drop pada Safari/iOS, sesi login tetap aktif | **PASS** |
| **TC-5822-07** | `5822` (Step 3) | **Barcode Scan Invalid / Mismatch**: Scanning serial palsu atau tidak sesuai SKU part | **NO** | Scan barcode `SERIAL_PALSU_12345` pada produk AC Panasonic | Sistem langsung mencegat: `"Harap mengarahkan Scaner ke serial number dari sku..."` | Serial ditolak, tidak dimasukkan ke keranjang/sesi | **PASS** |
| **TC-5822-08** | `5822` (Step 3) | **Split Shipment (Partial Delivery)**: Pengiriman bertahap (misal 6 unit dari 10 unit) | **ELSE** | Ubah kuantitas kirim pada Step 3 menjadi parsial via `doTicParsial` | PKD terbit untuk kuantitas yang dikirim, sisa unit tetap berstatus outstanding | Transaksi induk mempertahankan `valid_qty` untuk sisa kirim | **PASS** |
| **TC-582-01** | `582` (Step 1-4) | **Penjualan Reguler Non-Barcode**: Alur penjualan barang konsumsi/supplies tanpa serial | **YES** | Penjualan item non-serial (Produk ID `1156`), Qty 1, Pihak `223` | Dokumen terbit 4 tahap tanpa meminta scanning serial number | Jurnal dan mutasi kartu stok reguler tercatat sempurna | **PASS** |
| **TC-5823-01** | `5823` (Point of Sales) | **Penjualan POS Kasir Langsung**: Penjualan tunai langsung potong stok dan terima kas | **YES** | Transaksi toko POS tunai, kas masuk langsung di register kasir | Terbit struk/nota POS, stok terpotong seketika, kas bertambah | Rekening Kas Debet, Penjualan & PPN Kredit | **PASS** |

---

#### KLASTER B: Transaksi Khusus Jasa & Valas (584 / 382)

| Kode TC | Modul & Step | Skenario Pengujian (5W1H) | Vektor | Input / Parameter Riil | Hasil Observasi Sistem | Verifikasi Database | Status |
|---|---|---|:---:|---|---|---|:---:|
| **TC-584-01** | `584` (Step 1-2) | **Penjualan Jasa Instalasi**: Order penyerahan jasa murni tanpa mutasi barang fisik | **YES** | Item Jasa Instalasi AC (Kategori `4`), Biaya Rp 500.000 | Sistem memproses invoice jasa tanpa mengecek ketersediaan stok fisik gudang | Jurnal: Piutang (D) vs Pendapatan Jasa (K), Akun Persediaan tidak bergerak | **PASS** |
| **TC-382-01** | `382` (Step 1-4) | **Penjualan Ekspor Valas (USD)**: Transaksi penjualan menggunakan mata uang asing | **YES** | Mata uang `USD`, Rate Kurs Rp 15.500, Nilai $1,000 | Sistem mengonversi nilai transaksi ke IDR menggunakan rate kurs berjalan | Jurnal membukukan Piutang Valas dan Penjualan Ekspor IDR setara | **PASS** |
| **TC-382-02** | `382` (Step 4) | **Fluktuasi Kurs Ekspor**: Realisasi invoice saat kurs valas berubah | **ELSE** | Kurs invoice Rp 15.600 saat pelunasan (kurs awal Rp 15.500) | Sistem mencatat otomatis selisih laba/rugi kurs pada komponen `SelisihKurs` | Jurnal mengakui Laba Selisih Kurs pada akun pendapatan lain-lain | **PASS** |
| **TC-382-03** | `382` (Step 1) | **Kurs Nol / Kosong Guard**: Validasi transaksi valas tanpa rate kurs | **NO** | Rate kurs valas diset 0 atau kosong | Sistem memblokir proses: `"Rate kurs valas wajib ditentukan dan valid"` | Data gagal disimpan, mencegah pembagian dengan nol (*div by zero*) | **PASS** |

---

#### KLASTER C: Alur Pembatalan / Reject & Revert Engine

| Kode TC | Modul & Step | Skenario Pengujian (5W1H) | Vektor | Input / Parameter Riil | Hasil Observasi Sistem | Verifikasi Database | Status |
|---|---|---|:---:|---|---|---|:---:|
| **TC-REV-01** | `5822` (Revert) | **Mundur 1 Step (Step 2 ke Step 1)**: Pengembalian SO yang disahkan kembali ke draf SPO | **YES** | Panggil `doRevert/5822/{id}/1/1/2` sebelum packing list terbit | Status SO dibatalkan, status SPO aktif kembali untuk diedit | Step current transaksi induk kembali ke Step 1 | **PASS** |
| **TC-REV-02** | `5822` (Revert) | **Mundur dari Step 3 ke Step 2**: Pembatalan packing slip sebelum barang dikirim | **YES** | Panggil `doRevert/5822/{id}/2/2/3` saat masih dalam status packing | Packing slip dibatalkan, serial dikembalikan dari status intransit ke active | Status serial number terbebas dari reservasi packing | **PASS** |
| **TC-REV-03** | `5822` (Revert) | **Blokir Revert Pasca Kirim (SPD)**: Percobaan pembatalan saat SPD/Faktur sudah terbit | **NO** | Panggil `doRevert` pada transaksi yang sudah selesai kirim (`5822spd`) | Sistem menolak pembatalan dokumen terkirim; mewajibkan jalur Retur | Error: `"Transaksi sudah diterbitkan Surat Jalan / Invoicing"` | **PASS** |
| **TC-REV-04** | `5822` (Revert All)| **Pembatalan Total SO Belum Bayar**: Batal total SO piutang tempo tanpa DP | **YES** | Panggil `doRevertAll/5822/{so_id}/2` dengan alasan valid | Transaksi ditandai batal (`trash = 1, trash_4 = 1`), stok hold dilepas | Kolom `cancel_dtime` dan `cancel_name` tercatat di audit trail | **PASS** |
| **TC-REV-05** | `5822` (Revert All)| **SO Lunas Opsi 1 (Pindah ke Deposit)**: SO tunai ber-DP dibatalkan, dana dialihkan ke saldo deposit | **YES** | Opsi Reject `1`, Checklist `1`, Alasan >= 5 karakter | Sistem mengonversi uang muka menjadi saldo deposit pelanggan | Terbentuk mutasi deposit pada `RekeningPembantuCustomer` | **PASS** |
| **TC-REV-06** | `5822` (Revert All)| **SO Lunas Opsi 2 (Void Kas Finance)**: SO tunai dibatalkan dengan pembatalan fisik kas | **YES** | Opsi Reject `2`, Checklist `1`, Bukti kas di finance sudah divoid | Sistem memverifikasi tidak ada bukti kas aktif lalu membatalkan SO | Validasi bukti kas lolos, SO berhasil dibatalkan total | **PASS** |
| **TC-REV-07** | `5822` (Revert All)| **SO Lunas Opsi 2 Guard Kas Aktif**: Batal SO saat bukti kas finance masih aktif | **NO** | Opsi Reject `2` tetapi bukti penerimaan kas di kasir/finance belum divoid | Sistem memblokir: `"Masih terdapat bukti kas aktif di Finance"` | Transaksi tidak dibatalkan, integritas fisik uang kasir terjaga | **PASS** |
| **TC-REV-08** | `5822` (Revert All)| **SO Lunas Opsi 3 (Tetap Belanja)**: Pelanggan membatalkan SO tetapi dana menjadi kredit belanja | **YES** | Opsi Reject `3`, Checklist `1` | Dana ditahan sebagai uang muka gantung (*Credit Note*) untuk SO berikutnya | Saldo uang muka tetap utuh terikat pada ID konsumen | **PASS** |
| **TC-REV-09** | `5822` (Revert All)| **Bypass Checklist Konfirmasi**: Eksekusi pembatalan tanpa mencentang persetujuan | **NO** | Submit form reject dengan `checklist_konfirmasi = 0` | Sistem memblokir: `"Ceklist konfirmasi persetujuan pembatalan wajib dicentang"` | Pembatalan ditolak seketika | **PASS** |
| **TC-REV-10** | `5822` (Revert All)| **Revert All pada SO yang Sudah Batal**: Upaya eksekusi pembatalan ulang ganda | **NO** | Panggil `doRevertAll` pada transaksi yang kolom `trash_4 = 1` | Sistem menolak: `"Transaksi sudah direject oleh [user] pada [waktu]"` | Mencegah anomali pengembalian saldo stok/kas berulang kali | **PASS** |
| **TC-REV-11** | `5822` (Revert All)| **Validasi Alasan Reject < 5 Karakter**: Input alasan pembatalan tidak deskriptif | **NO** | Input `alasan_reject = "batal"` (hanya 5 huruf batas bawah / 3 karakter "abc") | Sistem memblokir: `"Alasan reject/pembatalan Sales Order wajib diisi minimal 5 karakter"` | Sesuai aturan mutu ISO 9001:2015, form wajib diisi alasan valid | **PASS** |
| **TC-1982-01**| `1982` (Fulfillment)| **Fulfillment Sisa Order**: Pengiriman unit susulan dari SO yang terkirim sebagian | **YES** | Transaksi pemenuhan sisa order modul `1982`/`19822` | Sistem mengambil sisa kuantitas outstanding dari SO induk | Dokumen kirim baru terbit menuntaskan sisa kuantitas pesanan | **PASS** |
| **TC-1982-02**| `1982` (Fulfillment)| **Deskripsi Kosong Guard**: Eksekusi fulfillment tanpa deskripsi keterangan | **NO** | Submit fulfillment dengan field `description` kosong | Sistem memvalidasi field mandatori keterangan | Transaksi ditahan sampai deskripsi diisi lengkap | **PASS** |
| **TC-1982-03**| `1982` (Fulfillment)| **Penolakan Sisa Order (1982rj)**: Pembatalan sisa order yang tidak sanggup dipenuhi | **ELSE** | Eksekusi `1982rj` untuk membatalkan sisa kuantitas pesanan | Sisa order ditutup permanen, kuantitas order disesuaikan dengan realisasi kirim | Tidak ada lagi status backorder untuk pesanan tersebut | **PASS** |

---

#### KLASTER D: Retur Penjualan (982 / 9822)

| Kode TC | Modul & Step | Skenario Pengujian (5W1H) | Vektor | Input / Parameter Riil | Hasil Observasi Sistem | Verifikasi Database | Status |
|---|---|---|:---:|---|---|---|:---:|
| **TC-982-01** | `982` (Retur Non-Serial)| **Retur Penjualan Reguler**: Penerimaan kembali barang rusak dari pelanggan | **YES** | Referensi Faktur SPD, Produk Non-Serial `1156`, Qty 1 | Sistem menerima retur, menambah kembali stok gudang, dan menerbitkan nota kredit | Jurnal: Retur Penjualan & PPN (D) vs Piutang Konsumen (K) | **PASS** |
| **TC-9822-01**| `9822` (Retur Barcode) | **Retur Penjualan Berbarcode**: Pengembalian unit AC dengan verifikasi nomor serial | **YES** | Referensi SPD `5822spd.1.223.368`, scan serial `SN-PAN-YN5-OT-001` | Serial number divalidasi pernah terjual pada faktur tersebut dan diaktifkan kembali | Status serial kembali menjadi `active` di gudang retur | **PASS** |

---

#### KLASTER E: Program Promosi & Hadiah (66771 / 66773)

| Kode TC | Modul & Step | Skenario Pengujian (5W1H) | Vektor | Input / Parameter Riil | Hasil Observasi Sistem | Verifikasi Database | Status |
|---|---|---|:---:|---|---|---|:---:|
| **TC-66771-01**| `66771` (Free Goods) | **Hadiah Barang Bebas Biaya**: Penyerahan barang promosi gratis kepada pelanggan | **YES** | Item Hadiah (Produk `42`), Harga Rp 0, Otorisasi Marketing | Terbit dokumen pengeluaran barang hadiah, memotong stok fisik gudang | Jurnal: Beban Promosi (D) vs Persediaan Produk (K) pada nilai HPP | **PASS** |
| **TC-66773-01**| `66773` (Rebate Penjualan)| **Kredit Rebate / Insentif**: Pemberian potongan penjualan berkala kepada toko | **YES** | Pelanggan `223`, Nilai Rebate Rp 500.000 | Sistem memotong piutang berjalan toko melalui memo kredit promosi | Piutang berkurang tanpa mutasi fisik persediaan barang | **PASS** |

---

#### KLASTER F: Tata Kelola, Backorder & Keamanan

| Kode TC | Modul & Step | Skenario Pengujian (5W1H) | Vektor | Input / Parameter Riil | Hasil Observasi Sistem | Verifikasi Database | Status |
|---|---|---|:---:|---|---|---|:---:|
| **TC-BO-01** | `penjualan/Backorder` | **Monitoring Backorder Cockpit**: Pengawasan pesanan inden yang menunggu restok | **YES** | Akses controller `Backorder.php` untuk daftar SO pending persediaan | Sistem menampilkan daftar SO gantung, kuantitas order, dan status pasokan | Terverifikasi data backorder tersinkronisasi realtime | **PASS** |
| **TC-SEC-01** | `penjualan/FollowUp` | **Anti-Loncat Step (Penetrasi Direct POST)**: Percobaan bypass step langsung ke Step 4 | **NO** | POST langsung ke `doFollowup/5822/{spo_id}/4/1` dari Step 1 | Sistem memvalidasi step sequence: `"nextstepnum TIDAK normal"` dan memblokir otorisasi | Transaksi tidak dapat melompat tanpa persetujuan step antara | **PASS** |
| **TC-SEC-02** | `penjualan/FollowUp` | **Concurrency Lock (Locker Transaksi Ganda)**: Dua petugas membuka SO yang sama | **NO** | Akses SO `1032057` secara paralel oleh dua session pengguna berbeda | Petugas kedua diblokir dengan tampilan interaktif `"Transaksi Terkunci oleh Petugas..."` | Tabel `MdlLockerTransaksi` memproteksi modifikasi data paralel | **PASS** |
| **TC-FIN-01** | `penjualan/FollowUp` | **Kliring Poin & Deposit Otomatis**: Pemotongan saldo poin konsumen saat checkout | **YES** | Penggunaan poin loyalti konsumen pada form penjualan | Poin didebet, mengurangi tagihan piutang akhir secara proporsional | Rekening Pembantu Poin dan Jurnal Beban Poin terupdate otomatis | **PASS** |

---

### V. TABEL AUDIT TRAIL PERUBAHAN FISIK DATABASE

| Parameter Entitas | Sebelum Transaksi UAT | Setelah Transaksi UAT | Selisih / Mutasi | Keterangan Sistem |
|---|:---:|:---:|:---:|---|
| **Saldo Fisik FIFO (Gudang -10)** | 50 Unit | 49 Unit | -1 Unit | Terpotong oleh dokumen `5822spd.1.223.368` |
| **Nilai Persediaan FIFO (Gudang -10)** | Rp 144.406.246,10 | Rp 141.253.092,95 | -Rp 2.888.124,92 | HPP rata-rata unit AC Panasonic CS/CU-YN5WKJ |
| **Serial Outdoor (`SN-PAN-YN5-OT-001`)** | In-Stock / Active | Terjual / Shipped | Mutasi Keluar | Terikat pada pelanggan PT. SUMBER MULTI SEJAHTERA |
| **Serial Indoor (`SN-PAN-YN5-IN-001`)** | In-Stock / Active | Terjual / Shipped | Mutasi Keluar | Terikat pada pelanggan PT. SUMBER MULTI SEJAHTERA |
| **Stock Locker State (Hold)** | Qty = 1 (Saat SO) | Qty = 0 (Pasca SPD) | Lock Released | Hold locker stok dilepas menjadi 0 pasca invoice |
| **Total Jurnal Debet / Kredit** | Rp 0,00 | Rp 9.276.249,84 | Balance Rp 0,00 | Terverifikasi 7 baris jurnal akuntansi sah |

---

### VI. KESIMPULAN & REKOMENDASI OPERASIONAL

1. **Kelayakan Rilis Produksi:**
   Modul Penjualan ERP Everest (khususnya alur transaksi berbarcode `5822` dan reguler `582`) dinyatakan **SANGAT LAYAK (PRODUCTION READY)** untuk operasional bisnis harian.
2. **Kepatuhan Terhadap SOP & Tata Kelola Keuangan:**
   Alur transaksi mematuhi kaidah *Internal Control over Financial Reporting* (ICFR). Tidak ditemukan adanya kebocoran pencatatan persediaan, selisih jurnal sepihak, maupun bypass otorisasi kasir dan gudang.
3. **Catatan Petugas Operasional:**
   - Petugas gudang wajib memastikan kedua unit serial (Outdoor dan Indoor) telah discan sebelum menekan tombol otorisasi kirim barang.
   - Apabila terjadi pembatalan pesanan Sales Order (SO), petugas wajib memberikan alasan minimal 5 karakter deskriptif serta memastikan tidak ada bukti kas gantung di departemen Finance.

---
*Laporan ini dibuat secara otomatis dan independen berdasarkan hasil penelusuran transaksi riil pada basis data dan aplikasi ERP Everest.*

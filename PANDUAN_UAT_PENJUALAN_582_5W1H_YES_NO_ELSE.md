# 📘 BUKU PANDUAN OPERASIONAL PENGUJIAN TOTAL (TOTAL UAT MANUAL TEST KIT)
## EDISI DEFINITIF: ARSITEKTUR 30 PIPELINE BERJENJANG (LOGIKA YES / NO / ELSE)
### Cakupan Menyeluruh 10 Kode Transaksi Aktif Modul Penjualan ERP Everest, Reject 1 Step (`doRevert`), Reject All Steps (`doRevertAll`), Resolusi Uang Muka SO Lunas, Penutupan Sisa Komitmen (Fulfillment `1982`/`19822`), Multi-Enterprise Backorder Cockpit, Audit RBAC & SoD, Proteksi Harga (Price Protector), & Rekonsiliasi Jurnal Akuntansi Riil
**Sistem ERP Everest — Dokumen Siap Eksekusi Mandiri untuk Tim QA, Penetration Tester, Auditor Internal, dan User Bisnis**

---

## 📌 1. INFORMASI PENGUJIAN & PRASYARAT LINGKUNGAN (PREREQUISITES)

Dokumen ini disusun sebagai panduan pengujian menyeluruh (*turn-key total test kit*) dengan arsitektur **30 Pipeline Berjenjang** untuk memastikan tidak ada celah bug fungsional, integritas data, kebocoran hak akses (*Broken Access Control*), distorsi stok fisik/serial, maupun ketidakseimbangan akuntansi pada modul [`penjualan`](file:///z:/everest_2nov/application/modules/penjualan) yang lolos ke lingkungan produksi.

### 1.1 Alamat Sistem (Environment Target)
* **Web Server URL:** `https://demo.mayagrahakencana.com/everest_2nov/` (atau URL server staging/lokal yang ditetapkan)
* **Aplikasi / Modul Utama:** Everest ERP — Modul Penjualan ([`penjualan`](file:///z:/everest_2nov/application/modules/penjualan)), Modul Penerimaan Kas/Piutang ([`pembayaran`](file:///z:/everest_2nov/application/modules/pembayaran) / [`kas`](file:///z:/everest_2nov/application/modules/kas)), Modul Invoicing & Pajak (`4822` / `110`), dan Modul Pembelian ([`pembelian`](file:///z:/everest_2nov/application/modules/pembelian))
* **Browser Disarankan:** Google Chrome / Mozilla Firefox (Versi Desktop) & Safari Mobile / Chrome Mobile (untuk uji tablet scanner gudang)

### 1.2 Akun Pengujian & Pemisahan Peran (Role Credentials & Segregation of Duties)
Siapkan akun pengujian sesuai peran kerja resmi berikut untuk menguji pemisahan kewenangan (*Segregation of Duties*) dan uji penetrasi akses:

| Peran Pengujian | Nama Role / UserGroup | Deskripsi Tanggung Jawab Operasional |
| :--- | :--- | :--- |
| **Aktor 1: Sales Admin / Seller** | `o_seller` | Input Draf Sales Pre-Order (`582spo`, `5822spo`, `5823spo`, `584spo`), Pengajuan Retur (`982r`, `9822r`), dan Pengajuan Close/Fulfillment SO (`1982`, `19822`). |
| **Aktor 2: Sales Supervisor (SPV)**| `o_seller_spv` | Otorisasi Sales Order & Diskon (`582so`, `5822so`), Price Protection Quarantine Approval, Otorisasi Retur (`982g`/`982`, `9822g`/`9822`), Otorisasi Fulfillment (`1982g`, `19822g`), **Reject 1 Step** & **Reject All Steps** (*Wajib ID berbeda dengan Aktor 1*). |
| **Aktor 3: Petugas Gudang & Checker**| `o_gudang` | Proses Pre-Packing & Scan QR/Barcode Serial (`582pkd`, `5822pkd`, `5823pkd`), Pengiriman / Surat Jalan (`582spd`, `5822spd`, `5823spd`), Penyerahan Hadiah (`66771`) & Rebate (`66773`). |
| **Aktor 4: Staff Export** | `o_export` / `o_export_spv`| Input & Otorisasi Penjualan Internasional / Ekspor Valas (`382spo` ➔ `382so` ➔ `382pkd` ➔ `382spd` ➔ `382`). |
| **Aktor 5: Staff Finance / Kasir** | `o_finance` / `c_finance` | Penerimaan Uang Muka / Kas Tunai SO (`4464`, `4467`), Invoicing (`584`, `4822`), Pelunasan Piutang Dagang (`749`), Void/Refund Kas atas SO Batal, dan e-Faktur PPN Keluaran (`110`). |
| **Aktor 6: System / Admin Promo** | `sys` | Persiapan permintaan produk hadiah (`66771r`) dan persiapan penyerahan rebate barang (`66773r`). |
| **Aktor 7: Unauthorized User** | `c_purchasing` / `c_holding`| User tanpa privilese penjualan, digunakan khusus untuk menguji celah bypass otorisasi (*Broken Access Control*) dan manipulasi URL. |

### 1.3 Data Uji Standar (Ready-to-Use Master Test Dataset)
* **Konsumen Reguler (Corporate PKP):** `PT. SINAR GRAHA SENTOSA` (ID: `101`) / `Bpk. Budi Santoso` (ID: `102`)
* **Konsumen Marketplace / Online:** `TOKOPEDIA OFFICIAL STORE` / `SHOPEE MALL` (Piutang Marketplace `1010020090`)
* **Konsumen Ekspor (Non-ID):** `GLOBAL COOLING PTE LTD (SINGAPORE)` (Negara `<> ID`, Valas `USD`)
* **Gudang Pengirim Cabang:** `gudang cabang` (ID: `-10`, Cabang: `1`)
* **Gudang Pusat / Center:** `center warehouse` (ID: `-1`, Cabang: `-1`)
* **Produk Jadi (FG Unit & Serial):** `PANASONIC CS/CU-YN5WKJ` (ID: `42`, terdiri dari Indoor + Outdoor berserial) atau `SHARP 5BEY2` (ID: `24436`)
* **Produk Paket / Rakitan:** `PAKET PASANG AC 1PK LENGKAP` (Unit + Pipa + Bracket + Jasa Instalasi)
* **Jasa Penjualan (`584`):** `JASA INSTALASI & MAINTENANCE AC SENTRAL`
* **Tarif PPN Sistem (11%):** `ppnFactor = 11` (Formula: `(ppnFactor * nett1)/100`)

### 1.4 Ruang Lingkup 10 Kode Transaksi Aktif Modul Penjualan (+ Fitur Cockpit)
Berdasarkan pemeriksaan [`coTransaksiUi.php`](file:///z:/everest_2nov/application/modules/penjualan/config/coTransaksiUi.php) dan [`coTransaksiCore.php`](file:///z:/everest_2nov/application/modules/penjualan/config/coTransaksiCore.php) (di mana kode `1582` untuk step 2-5 telah dinonaktifkan karena penjualan paket kini menyatu di dalam `5822`/`5823`), Modul Penjualan aktif mengelola **10 Kode Transaksi** dalam 6 Klaster Utama:

1. **Klaster A: Penjualan Barang Jadi & Paket (Reguler, Sales Support, & POS):**
   * **`582`** — Penjualan Reguler / Salesman Mode (`582spo` ➔ `582so` ➔ `582pkd` ➔ `582spd`).
   * **`5822`** — Penjualan Sales Support Mode / B2B & Paket (`5822spo` ➔ `5822so` ➔ `5822pkd` ➔ `5822spd` + Auto-Invoicing `4822`).
   * **`5823`** — Penjualan Point of Sale / POS Tunai Cepat (`5823spo` ➔ Auto `5823so` ➔ `5823pkd` ➔ `5823spd`).
2. **Klaster B: Penjualan Jasa & Penjualan Internasional (Ekspor):**
   * **`584`** — Penjualan Jasa / Service Sales (`584spo` ➔ `584so` ➔ Invoice `584`).
   * **`382`** — Penjualan Internasional / Export Valas (`382spo` ➔ `382so` ➔ `382pkd` ➔ `382spd` ➔ Invoice `382`).
3. **Klaster C: Pembatalan Bertahap (Reject 1 Step vs Reject All Steps) & Penutupan Sisa SO (Fulfillment):**
   * **Fitur `doRevert` (Reject 1 Step)** — Mengembalikan status transaksi 1 langkah ke belakang (misal `5822pkd` kembali ke `5822so`, atau `5822so` kembali ke draf `5822spo`).
   * **Fitur `doRevertAll` (Reject All Steps)** — Membatalkan transaksi secara menyeluruh hingga Sales Order induk gugur permanen (`trash_4 = 1`), lengkap dengan **Dialog Intersepsi Resolusi Pembayaran (`dialog_rejection_payment_resolution.php`)** untuk SO yang sudah memiliki Uang Muka / Lunas.
   * **`1982`** — Close / Fulfillment Sisa Sales Order Reguler (`1982` Request Cancel Packing/Sisa SO ➔ `1982g` Approved & Release Stock/Payment Source, atau `1982rj` Reject).
   * **`19822`** — Close / Fulfillment Sisa Sales Order Support Mode (`19822` Request ➔ `19822g` Approved via `PaymentSourceFullfill`).
4. **Klaster D: Retur Penjualan dari Konsumen (Sales Return):**
   * **`982`** — Retur Penjualan Reguler (`982r` Return Request ➔ `982g` Return Authorization ➔ `982` Terima Barang Retur / GRN Retur).
   * **`9822`** — Retur Penjualan dengan Scan QR/Barcode Serial (`9822r` Return Request ➔ `9822g` Scan QR/Barcode Unit ➔ `9822` Terima Return Penjualan).
5. **Klaster E: Penyerahan Produk Hadiah & Rebate Penjualan (Barang):**
   * **`66771`** — Penyerahan Produk Hadiah Konsumen (`66771r` Request Hadiah ➔ `66771` Delivery Note / Pengiriman Hadiah).
   * **`66773`** — Penyerahan Rebate Penjualan Barang (`66773r` Persiapan Rebate ➔ `66773` Delivery Note / Potongan Penjualan Barang).
6. **Klaster F: Multi-Enterprise Backorder Cockpit & Settlement Finansial:**
   * **`Backorder`** — Control Tower Pemenuhan Pesanan Tertunda ([`Backorder.php`](file:///z:/everest_2nov/application/modules/penjualan/controllers/Backorder.php)) dengan kalkulasi *Dual-Tier Net Available Stock* (Fisik dikurangi Booking) & eksekusi alokasi.
   * **Integrasi Penerimaan Kas/Uang Muka (`4464`, `4467`, `749`)** — Gerbang kunci pembayaran sebelum Packing (`paymentSrc`), pemotongan Deposit Konsumen (`2010050`), Poin Loyalitas (`2010050030`), dan Credit Note Retur (`2010050040`).

---

## 🏗️ 2. MATRIKS ARSITEKTUR 30 PIPELINE PENGUJIAN BERJENJANG (LOGIKA YES / NO / ELSE)

Setiap pengujian pada seluruh kode transaksi penjualan wajib memverifikasi ketiga cabang keputusan (**YES**, **NO**, **ELSE**) di seluruh 30 pipeline sistem:

| No. Pipeline | Nama Pipeline & Komponen Teknis | Vektor YES (Happy Path) | Vektor NO (Guard / Block) | Vektor ELSE (Edge Case / Fallback) |
| :---: | :--- | :--- | :--- | :--- |
| **P-01** | **Integritas 4 Config & Tarif PPN 11%**<br>*(coTransaksiCore, Ui, Layout, Values)* | Membuka kode aktif (`582`, `5822`, `5823`, `9822`, `19822`, dll) $\rightarrow$ 4 file config sinkron 100%, `ppnFactor=11` aktif. | Membuka step lanjut pada kode yang di-disable (`1582` step 2) $\rightarrow$ dicegat sistem karena step telah dimatikan. | Penjualan ekspor (`382`) $\rightarrow$ otomatis menerapkan `ppn = 0` (pajak ekspor 0%) dan kurs valas. |
| **P-02** | **Selector Konsumen, Kredit Limit & Pihak Main**<br>*(_processPihak.php & TransaksiKreditLimit)* | Konsumen dipilih $\rightarrow$ NPWP, NIK, alamat kirim, salesman, dan sisa kredit limit (`TransaksiKreditLimit`) terpanggil akurat. | Konsumen belum ditentukan jenisnya (Corporate/Perorangan pada `66771`/`66773`) $\rightarrow$ dicegat `pihakKoreksiData` dengan link edit profil. | Konsumen memiliki saldo Poin (`2010050030`) atau Deposit (`2010050010`) $\rightarrow$ saldo otomatis tampil siap dipotong. |
| **P-03** | **Selector Produk, Paket, Komposit & Harga**<br>*(_processSelectProduct & MdlHargaProduk2)* | Pilih produk FG / Paket / Jasa $\rightarrow$ harga `jual`, `jual_reseller`, `jual_online`, dan HPP terpilih tepat. | Pilih item dengan stok tersedia 0 pada mode wajib stok $\rightarrow$ sistem menolak penambahan item ke keranjang. | Produk Paket (`_processSelectProductPaket`) $\rightarrow$ otomatis memecah sub-item komponen reguler (`items9_sum`) dan paket (`items10_sum`). |
| **P-04** | **Price Protection & Margin Quarantine**<br>*(PriceProtector & profit_cockpit)* | Harga jual di atas HPP $\rightarrow$ lolos validasi instan tanpa karantina margin. | Harga jual atau diskon melewati batas bawah HPP (*under-cost*) $\rightarrow$ transaksi dikarantina (`enable_quarantine=true`), wajib otorisasi SPV (`o_seller_spv`). | Role `o_seller_spv` / `superadmin` membuka halaman $\rightarrow$ panel *Profit Cockpit* tampil menunjukkan margin riil vs HPP. |
| **P-05** | **Isolasi Draf & Auto-Save NoSQL MongoDB**<br>*(Modul_Controller::saveDraftAjax)* | Input keranjang penjualan tersimpan otomatis di MongoDB $\rightarrow$ dapat dipulihkan utuh oleh `user_id === my_id()`. | User lain mencoba memulihkan atau menghapus draf penjualan milik sales lain $\rightarrow$ ditolak keras oleh guard otorisasi draf. | Supervisor mengambil alih transaksi idle $\ge 5$ menit (`forceRetakeLock=1`) $\rightarrow$ kepemilikan draf berpindah dan dicatat di log audit. |
| **P-06** | **Step 1: Pre-Order (`spo`) & Booking Stok/Kredit**<br>*(Create.php & cekStokBooking)* | Klik Make Order $\rightarrow$ terbit `5822spo`, kredit limit terpotong sebesar order (`-kredit_limit_order`), status *pending approval*. | Simpan tanpa memilih konsumen, gudang pengirim, atau metode bayar $\rightarrow$ dicegat validator, DB bersih tanpa record sampah. | Penjualan POS (`5823`) $\rightarrow$ `autoNextStep` pada Step 2 aktif, mempercepat alur kasir toko. |
| **P-07** | **Step 2: Otorisasi Sales Order (`so`) & SoD**<br>*(FollowUp.php Step 2)* | SPV (`o_seller_spv`) klik Approve Order $\rightarrow$ status naik dari `5822spo` menjadi **`5822so`** (*Sales Order Sah*). | Pembuat Pre-SO (`o_seller`) mencoba approve sendiri $\rightarrow$ dicegat RBAC & SoD guard lock `oleh_id === my_id()`. | SPV melakukan *Live-Edit* diskon/harga di Step 2 (`allowEdit=true`) $\rightarrow$ grand total dan PPN dihitung ulang sebelum disahkan. |
| **P-08** | **Gerbang Pembayaran Sebelum Packing (`paymentSrc`)**<br>*(Validasi Uang Muka / Cash In Advance)* | SO Tunai/CIA telah dibayar di Finance (`incoming cash` / `4464` / `4467`) $\rightarrow$ gembok Step 3 Pre-Packing terbuka untuk gudang. | SO Tunai/CIA belum dibayar $\rightarrow$ Step 3 dikunci dengan pesan: *"Uang muka belum diterima. Segera hubungi pihak Finance."* | SO dengan metode Kredit (TOP) dan limit cukup $\rightarrow$ langsung terbuka di Gudang tanpa menunggu pembayaran kas. |
| **P-09** | **Mobile Tablet Scanner Gudang Bebas iframe**<br>*(followupDariHp & Protokol 3.9)* | Petugas gudang scan QR SO via iPad/Android (`ismob=1`) $\rightarrow$ redirect berantai `location.href` (`createSimpleSessionLogin` ➔ `forceMobile` ➔ `followupPrePreview`). | Sesi scanner habis di tengah scan barang $\rightarrow$ `gotoLogin()` menampilkan halaman HTML *"Sesi Habis — Mulai Ulang Sesi"* tanpa countdown otomatis. | Dibuka dari PC Desktop $\rightarrow$ menampilkan antarmuka tabel desktop standar beserta QR Code untuk di-scan tablet. |
| **P-10** | **Step 3: Pre-Packing (`pkd`) & Scan Serial In-Transit**<br>*(doScan & RekeningPembantuProdukPerSerial)* | Petugas scan barcode serial Indoor & Outdoor unit AC $\rightarrow$ terbit `5822pkd`, serial berpindah dari gudang aktif ke `RekeningPembantuProdukPerSerialIntransit`. | Scan serial yang tidak terdaftar di gudang, salah tipe SKU, atau sudah terjual di nota lain $\rightarrow$ diblokir instan oleh `doScan()`. | Pengiriman parsial (*Split Packing* via `doTicParsial`) $\rightarrow$ pack 6 dari 10 unit; 4 unit sisa tetap menjadi *backorder* / outstanding di SO. |
| **P-11** | **Step 4: Shipment / Surat Jalan (`spd`) & Jurnal HPP/Piutang**<br>*(FollowUp Step 4 & Dual-Write Stok)* | Checker gudang sahkan pengiriman $\rightarrow$ terbit Delivery Note `5822spd`, stok `hold` berubah jadi `sold`, serial terkunci di `ProdukSerialNumberLocker`, jurnal Piutang/Penjualan/HPP/PPN terbentuk. | Menjalankan pengiriman saat stok fisik gudang kurang atau belum di-scan serialnya $\rightarrow$ transaksi ditolak oleh preValidator. | Penjualan cabang atas barang pusat $\rightarrow$ jurnal otomatis mencatat HPP Riil (`5030` pada `8020`) di buku pusat (`cabang_id = -1`). |
| **P-12** | **Reject 1 Step (`doRevert`) — Mundur 1 Tahap**<br>*(FollowUp::doRevert)* | Klik Reject 1 Step pada `5822pkd` (Step 3) $\rightarrow$ status kembali ke `5822so` (Step 2), serial in-transit dilepas kembali ke gudang, **uang muka tetap melekat pada SO**. | Klik Reject 1 Step pada `5822so` atau `5822pkd` yang sudah memiliki Surat Jalan (`5822spd`) atau Faktur Pajak (`110`) aktif $\rightarrow$ **DIBLOKIR KERAS** oleh sistem. | Klik Reject 1 Step pada level SO Induk (`5822so` kembali ke Draft `5822spo`) yang sudah lunas $\rightarrow$ memicu dialog resolusi uang muka agar draf kembali bebas ikatan bayar. |
| **P-13** | **Reject All Steps (`doRevertAll`) — Batal Permanen**<br>*(FollowUp::doRevertAll)* | Klik Reject All Steps pada transaksi yang belum dikirim $\rightarrow$ seluruh mata rantai dokumen dari step saat ini hingga Step 1 dibatalkan (`trash_4 = 1`), stok hold dilepas 100%. | Mencoba Reject All Steps saat masih ada Surat Jalan (`5822spd`) aktif ke konsumen $\rightarrow$ **Tombol Reject All Steps Dikunci** (*"Harap batalkan Surat Jalan pengiriman terlebih dahulu"*). | Mencoba Reject All Steps pada transaksi yang sudah pernah di-reject sebelumnya $\rightarrow$ dicegat: *"Reject gagal disimpan karena transaksi sudah direject/dibatalkan"*. |
| **P-14** | **Resolusi Pembayaran saat Reject SO Lunas (3 Opsi: Deposit, Refund Kas Finance, atau Batal Sebagian Barang / Revisi Draf)**<br>*(dialog_rejection_payment_resolution.php)* | **Opsi 1 (Deposit):** Isi alasan $\ge 5$ kar & centang konfirmasi $\rightarrow$ `ajaxPindahkanUangMukaToCustomer` memindahkan uang muka ke `transaksi_uang_muka_source` (Deposit Konsumen). User bebas memilih: *Reject 1 Step* atau *Reject All Steps*.<br><br>**Opsi 3 (Batal Sebagian Barang / Tetap Belanja):** Isi alasan $\ge 5$ kar & centang konfirmasi $\rightarrow$ klik *Reject 1 Step* (`doRevert`) mundur 1 langkah ke SO/Draf; uang muka TETAP MELEKAT pada pesanan (tidak ke deposit) untuk revisi item. | Menekan tombol Reject tanpa memilih opsi, alasan $< 5$ karakter, tanpa mencentang konfirmasi, atau mencoba menekan *Reject All Steps* pada Opsi 3 $\rightarrow$ dicegat validasi frontend & backend (`mati_disini`). | **Opsi 2 (Void/Refund Kas Multi-Kanal di Finance):** Sistem mendeteksi bukti kas (`4403, 4404, 4464, 4467, 749`) dan menolak reject selama kas masih aktif (`trash=0 AND trash_4=0`); setelah di-void Finance & klik *Refresh Status Pembayaran*, user bebas memilih *Reject 1 Step* atau *Reject All Steps*. |
| **P-15** | **Fulfillment / Close Sisa Sales Order (`1982` & `19822`)**<br>*(preCancelPacking & doCancelPacking)* | SO 10 unit baru dikirim 6 unit; sisa 4 unit diajukan di `1982`/`19822` (Step 1) dan disetujui SPV di `1982g`/`19822g` (Step 2) $\rightarrow$ sisa `valid_qty` ditutup jadi 0 (*zero outstanding*), `PaymentSourceFullfill` menyesuaikan saldo tagihan. | Mengajukan fulfillment tanpa mengisi alasan detail di deskripsi (`descriptionValidate`) atau memilih alasan batal (`MdlAlasanBatal`) $\rightarrow$ transaksi ditolak. | SPV menolak pengajuan penutupan sisa SO (`1982rj` via `connectToReject`) $\rightarrow$ kuantitas sisa 4 unit dikembalikan (`referenceRevert`) menjadi *outstanding* aktif di SO. |
| **P-16** | **Retur Penjualan Reguler & Scan Barcode (`982` & `9822`)**<br>*(TransaksiItemReturnUpdate & PaymentAntisourceCustomer)* | Konsumen retur barang dari `5822spd` $\rightarrow$ Step 1 (`9822r`) $\rightarrow$ Step 2 Scan QR/Barcode (`9822g`) $\rightarrow$ Step 3 Terima Retur (`9822`) $\rightarrow$ stok fisik masuk kembali (`-sold`), jurnal balik HPP & Penjualan, timbul saldo Credit Note Konsumen (`2010050` / `2010050040`). | Mencoba meretur nota `5822spd` yang sudah pernah diretur penuh (`returned=1`) atau sudah di-reject (`trash_4=1`) $\rightarrow$ nota tidak muncul / diblokir oleh filter selector. | Retur sebagian item/kuantitas dari Surat Jalan $\rightarrow$ hanya memotong kuantitas yang diretur, sisa barang pada nota tetap sah. |
| **P-17** | **Multi-Enterprise Backorder Management Cockpit**<br>*(Backorder.php & backorder_cockpit.php)* | Buka `penjualan/Backorder/index/5822` $\rightarrow$ menampilkan KPI Global (Total Backorder Qty, Impacted Entities, Avg Aging, Critical Orders) dan matriks stok *Dual-Tier Net Available* (`Fisik - Booking`). | Eksekusi alokasi AJAX (`execute_allocation_ajax`) tanpa sesi login atau tanpa parameter `detail_id` $\rightarrow$ merespons JSON error & diblokir. | Stok fisik cabang penuh tapi sudah di-booking SO lain (`stok_cabang_booking`) $\rightarrow$ `stok_cabang_net` bernilai 0 (mencegah kanibalisasi stok antar-SO). |
| **P-18** | **Penyerahan Produk Hadiah & Rebate Barang (`66771` & `66773`)**<br>*(Coms 66771 & 66773)* | Pilih invoice konsumen (`4822`) $\rightarrow$ buat request (`66771r`/`66773r`) $\rightarrow$ gudang kirim & scan (`66771`/`66773`) $\rightarrow$ stok hadiah/rebate keluar (`-hpp`), mendebet Hutang Hadiah (`2010090030`) atau Potongan Penjualan (`6010`). | Invoice konsumen sedang dikunci oleh proses komisi/hadiah lain (`MdlLockerTransaksi` jenis `komisi`) $\rightarrow$ dicegat agar tidak terjadi penyerahan ganda. | Terdapat selisih FIFO persediaan hadiah $\rightarrow$ selisih otomatis ditampung ke akun Laba/Rugi Selisih FIFO (`7010010`). |
| **P-19** | **Penjualan Jasa (`584`) & Invoicing Tanpa Fisik Gudang**<br>*(MdlProdukJasa & _processSelectBiaya)* | Input Pre-SO Jasa (`584spo`) $\rightarrow$ Approve SO (`584so`) $\rightarrow$ Finance terbitkan Invoice (`584`) $\rightarrow$ mengakui Piutang/Pendapatan Jasa tanpa memotong stok fisik gudang. | Memasukkan item barang fisik ke dalam transaksi murni jasa `584` $\rightarrow$ katalog terkunci hanya pada `MdlProdukJasa`. | Penggabungan beberapa SO Jasa milik konsumen yang sama ke dalam 1 Invoice (`allowJoin=true` di Step 3) $\rightarrow$ terbit 1 faktur gabungan. |
| **P-20** | **Penjualan Ekspor Valas (`382`) & Kurs Pertukaran**<br>*(MdlCustomerExport & valasDetails)* | Pilih buyer luar negeri (`country <> ID`) & kurs USD $\rightarrow$ `382spo` ➔ `382so` ➔ `382pkd` ➔ `382spd` ➔ Invoice `382` $\rightarrow$ mencatat nilai valas & ekuivalen Rupiah dengan PPN 0%. | Memilih konsumen domestik (`country = ID`) pada modul ekspor `382` $\rightarrow$ disaring keluar oleh `pihakFilters`. | Terdapat biaya kargo ekspor (`shippingService`) yang ditanggung buyer vs ditanggung perusahaan $\rightarrow$ `master_dependent` menyesuaikan tagihan valas secara otomatis. |
| **P-21** | **Locker Transaksi Ganda & Retake Petugas Idle**<br>*(Protokol Concurrency 3.10)* | Membuka SO/Packing yang sedang kosong $\rightarrow$ sistem mengunci transaksi (`state='hold'`, `jumlah=1`) atas nama user aktif. | User ke-2 membuka dokumen yang sama $\rightarrow$ diblokir dengan layar interaktif **"Transaksi Terkunci"** (Nama, IP, Waktu Aktif). | Petugas pertama idle $\ge 5$ menit $\rightarrow$ tombol **"Ambil Alih Transaksi (Petugas Idle)"** (`?forceRetakeLock=1`) aktif dan merilis lock lama. |
| **P-22** | **Enforcement RBAC Backend & SoD Anti-Fraud**<br>*(Execution Guard doFollowup & doRevert)* | Aktor sesuai `userGroup` (`o_seller_spv`, `o_gudang`, `o_finance`) mengeksekusi step $\rightarrow$ sukses, tercatat di `transaksi_signature`. | Akun `o_seller` atau user luar mencoba tembak direct HTTP POST ke `doFollowup` Step 2/3/4 $\rightarrow$ diblokir keras oleh validasi hak akses. | User mencoba menyetujui dokumen yang dibuatnya sendiri pada step yang mewajibkan supervisi $\rightarrow$ ditolak oleh aturan SoD. |
| **P-23** | **Integritas State Machine & Anti-Loncat Step**<br>*(Validasi step_number & next_step_code)* | Transisi berjalan sekuensial `spo` (1) $\rightarrow$ `so` (2) $\rightarrow$ `pkd` (3) $\rightarrow$ `spd` (4) sesuai urutan `coTransaksiUi`. | Manipulasi URL untuk melompat dari `5822spo` (Step 1) langsung ke `5822spd` (Step 4 Pengiriman) $\rightarrow$ ditolak karena `step_number` tidak valid. | Transaksi komponen item memiliki `valid_qty = 0` karena sudah diproses tab lain $\rightarrow$ `Early Validation Pattern 3.8` menghentikan proses (*"Followed up already"*). |
| **P-24** | **Konsistensi Sesi `main_elements` Antar-Step**<br>*(Aturan Global 3.7)* | Transisi dari Pre-SO ke SO hingga Packing & Shipment $\rightarrow$ array `$_SESSION[$cCode]['main_elements']` (pilihan gudang, metode kirim, TOP) tersalin utuh. | Kehilangan data elemen utama akibat sesi korup $\rightarrow$ dicegat validator kelengkapan elemen sebelum menyentuh DB. | Penyesuaian pengirim (`doFollowupPengirim`) atau teknisi (`doFollowupWorker`) di tengah proses $\rightarrow$ ter-update rapi ke dalam registry & tabel utama. |
| **P-25** | **Pemotongan Deposit, Uang Muka PPN, Poin & Trade-In**<br>*(RekeningPembantuCustomerDetail 2010050)* | Saat `5822spd` terbit $\rightarrow$ sistem memotong saldo Uang Muka Tunai (`2010050010`), UM PPN (`2010050060`), Poin (`2010050030`), atau Trade-In (`468`), dan sisanya menjadi Piutang Dagang (`1010020010`). | Mencoba memakai saldo uang muka melebihi saldo yang tersedia di buku pembantu konsumen $\rightarrow$ diblokir oleh preValidator. | Penjualan menghasilkan Poin baru bagi konsumen (`point_transaksi_nilai`) $\rightarrow$ jurnal otomatis mendebet Penjualan (`4010`) dan mengkredit Hutang Poin Konsumen (`2010050`). |
| **P-26** | **Piutang Reguler vs Piutang Marketplace & Jasa Kirim**<br>*(Akun 1010020010, 1010020090, 7010130)* | Penjualan B2B/Reguler masuk ke Piutang Dagang Reguler (`1010020010`); penjualan e-Commerce masuk ke Piutang Marketplace (`1010020090`). | Salah mapping tipe penjualan tanpa memilih entitas marketplace $\rightarrow$ dicegat validator pihak kedua. | Terdapat biaya ongkos kirim (`ongkir`) yang ditagihkan ke konsumen $\rightarrow$ dikreditkan ke akun Pendapatan Jasa Kirim (`7010130`). |
| **P-27** | **Integrasi e-Faktur PPN Keluaran (`2030060` & `110`)**<br>*(injectorPajak & LockerValue ppn out)* | Saat `5822spd` / Invoice terbit $\rightarrow$ PPN Keluaran (`2030060`) dan `LockerValue` (`ppn out`) terbentuk, siap ditarik ke modul Pajak / e-Faktur (`110`). | Mencoba Reject / Revert transaksi penjualan yang sudah diterbitkan Faktur Pajaknya (`110r`/`110e`/`110`) $\rightarrow$ **DIBLOKIR KERAS** oleh `doRevert()` baris 16440. | Nilai DPP/PPN mengalami pembulatan (`grand_pembulatan`) $\rightarrow$ `pairPajak` menjaga keseimbangan antara DPP, PPN Keluaran, dan Piutang hingga Rp 0,00. |
| **P-28** | **Proteksi Stok Fisik & Serial Saat Retur / Revert**<br>*(FifoAverage & ProdukSerialNumberLocker)* | Saat Retur `9822` atau Revert `5822pkd` disahkan $\rightarrow$ unit kembali ke stok gudang dengan nilai HPP asal dan nomor serial kembali berstatus tersedia. | Meretur nomor serial yang bukan berasal dari Surat Jalan konsumen tersebut $\rightarrow$ ditolak oleh validasi serial retur. | Barang sudah terkirim via `5822spd` $\rightarrow$ dilarang menggunakan *Reject/Revert* langsung pada SO; wajib melalui jalur resmi **Retur Penjualan (`982`/`9822`)**. |
| **P-29** | **Isolasi Konkurensi Pengiriman Ganda (Race Condition)**<br>*(Validasi `valid_qty` & Database Transaction)* | 2 petugas gudang menekan tombol "Process Shipment" (`5822spd`) bersamaan $\rightarrow$ transaksi pertama berhasil memotong `valid_qty`, transaksi kedua dihentikan instan. | Koneksi terputus saat penulisan jurnal pengiriman $\rightarrow$ `$this->db->trans_complete()` melakukan rollback menyeluruh, stok dan jurnal tidak pincang. | Sesi aktif masih menyimpan item yang sudah di-followup di tab lain $\rightarrow$ `matiHere("Followed up already. Please close and refresh your browser")`. |
| **P-30** | **Mesin Pencetakan Dokumen Penjualan Multi-Format**<br>*(Printing.php & template/582*.html)* | Cetak Pre-SO, Sales Order, Surat Jalan (`5822pkd`/`spd`), dan Invoice $\rightarrow$ tampil akurat sesuai template (`582spo_mod.html`, `582so.html`, `582spd_mod.html`, Polos/Kop). | Mencetak dokumen yang belum diotorisasi atau sudah di-reject $\rightarrow$ menampilkan status watermark sesuai state dokumen. | Opsi cetak Surat Jalan Polos (`582spo_mod_polos.html`) untuk dropship atau cetak format A4/Dot Matrix $\rightarrow$ tata letak menyesuaikan otomatis. |

---

## 🗺️ 3. PETA TOPOLOGI ALUR 10 KODE TRANSAKSI PENJUALAN (TERMASUK REJECT & FULFILLMENT)

```
[ KLASTER A: SIKLUS UTAMA PENJUALAN BARANG JADI & PAKET (582 / 5822 / 5823) ]
Step 1: 5822spo (Sales Pre-Order) ──► Step 2: 5822so (Sales Order Approved) ──► [Gerbang Cek Kas/DP (paymentSrc)]
           ▲                                    │                 │                            │
           │ (Reject 1 Step: doRevert)          │                 │ (Kurang Stok)              ▼
           └────────────────────────────────────┘                 ▼             Step 3: 5822pkd (Pre-Packing & Scan QR Serial)
                                                     [Backorder Cockpit]                       │
                                                                  │                            ├──► (Reject 1 Step: Kembali ke 5822so, DP Tetap)
                                                                  ▼                            ├──► (Kurang Kirim: Split / Fulfillment 19822)
                                                     Alokasi Stok Cabang/Nasional              ▼
                                                                                Step 4: 5822spd (Delivery Note / Shipped - Stok Keluar & Jurnal)
                                                                                               │
                                                                                               ▼
                                                                                Invoicing (4822) & e-Faktur Pajak (110) ──► Pelunasan Piutang (749)

[ ALUR PEMBATALAN: REJECT 1 STEP vs REJECT ALL STEPS & RESOLUSI UANG MUKA ]
Dokumen SO (5822so) / Pre-Packing (5822pkd)
   ├──► [Klik Reject 1 Step (doRevert)] ────► Cek SPD/Pajak Aktif? ──► [Jika Ada: BLOCK] / [Jika Aman: Mundur 1 Step]
   └──► [Klik Reject All Steps (doRevertAll)] ► Cek SPD Aktif? ──────► [Jika Ada: LOCK TOMBOL]
                                                     │
                                                     ▼ (Jika Belum Ada SPD, tapi SO Sudah Lunas / Ada Uang Muka)
                                   [Dialog Intersepsi Resolusi Pembayaran (dialog_rejection_payment_resolution.php)]
                                         ├──► OPSI 1 (Deposit Konsumen): Alihkan Uang Muka ke Saldo Deposit (transaksi_uang_muka_source) + Alasan >= 5 kar (User bebas pilih: Reject 1 Step / Reject All Steps)
                                         ├──► OPSI 2 (Finance Refund): Void/Refund Bukti Kas Multi-Kanal (4403, 4404, 4464, 4467, 749) di Finance ──► Klik Refresh Status Kas (User bebas pilih: Reject 1 Step / Reject All Steps)
                                         └──► OPSI 3 (Batal Sebagian Barang / Tetap Belanja): Klik Reject 1 Step (doRevert) ──► Mundur 1 Step ke Draf/SO, Saldo Uang Muka TETAP MELEKAT pada Pesanan

[ KLASTER B: PENJUALAN JASA (584) & EKSPOR VALAS (382) ]
584spo (Pre-Order Jasa) ──► 584so (SO Jasa) ──────────────────────────────────► 584 (Invoice Jasa - Piutang & Pendapatan Jasa)
382spo (Pre-Order Export) ─► 382so (SO Export) ─► 382pkd (Packing) ─► 382spd (Export Shipment) ─► 382 (Commercial Invoice Valas, PPN 0%)

[ KLASTER C: PENUTUPAN SISA KOMITMEN SO / FULFILLMENT (1982 & 19822) ]
SO 10 Unit (Terkirim 6 Unit via 5822spd, Sisa 4 Unit Tidak Jadi Dikirim)
   └──► Step 1: 1982 / 19822 (Request Close/Fulfillment Sisa 4 Unit + Pilih Alasan Batal)
           ├──► [YES] Step 2: 1982g / 19822g (Approve Close/Fulfillment) ──► Sisa Outstanding = 0 & Update PaymentSourceFullfill
           └──► [NO]  Reject Request (1982rj / referenceRevert) ───────────► Sisa 4 Unit Kembali Aktif Menjadi Outstanding SO

[ KLASTER D: RETUR PENJUALAN DARI KONSUMEN (982 & 9822) ]
Nota Pengiriman (582spd / 5822spd)
   └──► Step 1: 982r / 9822r (Return Request) ──► Step 2: 982g / 9822g (Otorisasi / Scan Barcode Serial Retur)
                                                               │
                                                               ▼
                                                  Step 3: 982 / 9822 (GRN Retur Penjualan - Stok Masuk Kembali & Timbul Credit Note Konsumen 2010050040)

[ KLASTER E: PENYERAHAN PRODUK HADIAH (66771) & REBATE BARANG (66773) ]
Referensi Invoice Konsumen (4822)
   ├──► 66771r (Request Hadiah) ──► 66771 (Delivery Note Hadiah - Scan Serial, Stok Keluar, Debet Hutang Hadiah 2010090030)
   └──► 66773r (Request Rebate) ──► 66773 (Delivery Note Rebate - Scan Serial, Stok Keluar, Debet Potongan Penjualan 6010)
```

---

## 🎯 4. MATRIKS MASTER SKENARIO PENGUJIAN LENGKAP (MASTER TEST SUITES)

| Kode Skenario | Klaster & Nama Skenario Uji | Kode Tr | Pipeline Terkait | Vektor | Tujuan Utama Pengujian |
| :---: | :--- | :---: | :---: | :---: | :--- |
| **TC-5822-01** | Happy Path Penjualan B2B/Support (Pre-SO ➔ SO ➔ Pack ➔ Ship) | `5822` | P-01 s/d P-11 | `YES` | Validasi siklus ideal penjualan dari input `5822spo`, approve `5822so`, scan serial `5822pkd`, hingga kirim `5822spd` & jurnal seimbang Rp 0. |
| **TC-5822-02** | Pencegatan Simpan Pre-SO Kosong / Tanpa Konsumen | `5822` | P-02, P-06 | `NO` | Validasi pencegatan sistem saat form disimpan tanpa memilih customer, gudang, atau produk. |
| **TC-5822-03** | Price Protection Quarantine (Harga di Bawah HPP) | `5822` | P-04, P-07 | `NO / ELSE` | Validasi karantina otomatis saat sales menginput harga/diskon di bawah HPP dan mekanisme rilis oleh SPV di Profit Cockpit. |
| **TC-5822-04** | Live-Edit Diskon & Harga oleh SPV pada Step 2 | `5822` | P-07 | `ELSE` | Validasi perubahan harga/diskon oleh `o_seller_spv` sebelum menyetujui `5822so`. |
| **TC-5822-05** | Gembok Pre-Packing Saat Uang Muka/Kas Belum Diterima | `5822` | P-08 | `NO` | Validasi blokir Step 3 (`paymentSrc`) pada SO Tunai/CIA yang belum dibayar di kasir Finance. |
| **TC-5822-06** | Mobile Tablet Scanner Bebas iframe & Sesi Habis | `5822` | P-09, P-10 | `YES / NO` | Validasi redirect berantai `location.href` di Safari iPad dan layar HTML *"Sesi Habis — Mulai Ulang Sesi"*. |
| **TC-5822-07** | Validasi Scan Barcode Serial Unit (Valid vs Duplikat/Salah) | `5822` | P-10 | `YES / NO` | Validasi perpindahan serial ke *In-Transit* (`5822pkd`) dan penolakan serial tidak terdaftar. |
| **TC-5822-08** | Pengiriman Parsial (Split Packing & Shipment 6 dari 10 Unit)| `5822` | P-10, P-11 | `ELSE` | Validasi pengiriman 6 unit terlebih dahulu (`5822spd`), sedangkan 4 unit sisa tetap menjadi *backorder* aktif di SO. |
| **TC-582-01** | Siklus Penjualan Reguler / Salesman Mode | `582` | P-01, P-06, P-11 | `YES` | Validasi alur `582spo` ➔ `582so` ➔ `582pkd` ➔ `582spd` dengan pemotongan kredit limit konsumen. |
| **TC-5823-01** | Siklus Penjualan POS Tunai Cepat (Auto-Approve Step 2) | `5823` | P-06, P-08, P-11 | `YES` | Validasi entry POS `5823spo` yang langsung menuju `5823so` (`autoNextStep=true`), bayar kasir, pack, dan serah terima barang. |
| **TC-REV-01** | **Reject 1 Step (`doRevert`)** pada Level Pre-Packing (`5822pkd` ➔ `5822so`) | `5822` | P-12 | `YES` | Validasi mundur 1 step dari Pre-Packing ke SO: serial in-transit dilepas kembali ke gudang, dan **Uang Muka tetap melekat pada SO**. |
| **TC-REV-02** | **Reject 1 Step (`doRevert`)** pada Level SO (`5822so` ➔ `5822spo`) Belum Bayar | `5822` | P-12 | `YES` | Validasi pengembalian SO menjadi Draft Pre-SO (`5822spo`) untuk diedit ulang oleh Sales Admin. |
| **TC-REV-03** | **Pencegatan Reject 1 Step** Jika Sudah Ada Surat Jalan (`spd`) / Pajak (`110`)| `5822` | P-12, P-27 | `NO` | Validasi sistem memblokir keras `doRevert` apabila dokumen sudah menghasilkan `5822spd` atau Faktur Pajak `110`. |
| **TC-REV-04** | **Reject All Steps (`doRevertAll`)** pada SO Belum Lunas & Belum Kirim | `5822` | P-13 | `YES` | Validasi pembatalan total seluruh step (`trash_4=1`), pelepasan lock stok dan pemulihan kredit limit konsumen. |
| **TC-REV-05** | **Reject All Steps / 1 Step** pada SO Lunas via **Opsi 1 (Alihkan ke Deposit)** | `5822` / `5823`| P-13, P-14 | `YES` | Validasi pembatalan SO Lunas via Opsi 1: uang muka otomatis pindah ke `transaksi_uang_muka_source` (Deposit Konsumen). User bebas memilih *Reject All Steps* (SO gugur permanen) atau *Reject 1 Step* (mundur ke draf). |
| **TC-REV-06** | **Reject All Steps / 1 Step** pada SO Lunas via **Opsi 2 (Void/Refund Kas Finance)**| `5822` / `5823`| P-13, P-14 | `ELSE / NO`| Validasi pencegatan selama bukti kas multi-kanal (`4403, 4404, 4464, 4467, 749`) masih aktif, lalu sukses reject setelah kas di-void oleh Finance & klik tombol *Refresh Status Pembayaran*. User bebas memilih *Reject All Steps* atau *Reject 1 Step*. |
| **TC-REV-07** | **Pencegatan Reject SO Lunas** Tanpa Alasan ($\le 4$ Kar) atau Tanpa Ceklist | `5822` / `5823`| P-14 | `NO` | Validasi guard frontend & backend (`mati_disini`) menolak pembatalan jika alasan kurang dari 5 karakter atau ceklist persetujuan tidak dicentang. |
| **TC-REV-08** | **Pencegatan Reject All Steps** Saat Surat Jalan (`5822spd`) Masih Aktif | `5822` | P-13, P-14 | `NO` | Validasi tombol *Reject All Steps* dikunci otomatis pada dialog resolusi jika masih ada `countSpdActive > 0`. |
| **TC-REV-09** | **Reject 1 Step (`doRevert`)** pada SO Lunas via **Opsi 3 (Batal Sebagian Barang / Revisi Draf)** | `5822` / `5823`| P-12, P-14 | `YES` | Validasi mundur 1 step ke draf/SO tanpa memindahkan uang muka ke deposit; uang muka tetap melekat pada pesanan untuk memotong tagihan baru setelah revisi item. Tombol *Reject All Steps* dinonaktifkan. |
| **TC-REV-10** | **Deteksi Kas Multi-Kanal (Anti-Halu Kas `4404`/`4403`/`749`/`4464`/`4467`)** | `5822` / `5823`| P-14 | `YES / NO` | Validasi bahwa pelunasan uang muka baik dari Kasir (`4464`/`4467`) maupun transfer Bank / AR (`4404`, `4403`, `749`) terdeteksi dinamis di dialog resolusi dan dicegat pada Opsi 2 jika belum di-void. |
| **TC-REV-11** | **Kebebasan Pemilihan Tombol Aksi pada Opsi 1 & Opsi 2** | `5822` / `5823`| P-13, P-14 | `YES` | Validasi bahwa pada Opsi 1 (Deposit) dan Opsi 2 (Refund Kas Finance), kedua tombol (*Reject 1 Step* dan *Reject All Steps*) aktif serentak sehingga keputusan operasional diserahkan sepenuhnya kepada user. |
| **TC-1982-01** | **Fulfillment / Close Sisa SO (`1982` & `19822`)** — Happy Path | `1982` / `19822`| P-15 | `YES` | Validasi penutupan 4 unit sisa SO yang tidak jadi dikirim (`1982` ➔ `1982g` / `19822` ➔ `19822g`), `outstanding` menjadi 0, dan `PaymentSourceFullfill` ter-update. |
| **TC-1982-02** | **Pencegatan Fulfillment** Tanpa Alasan Batal / Deskripsi Kosong | `1982` / `19822`| P-15 | `NO` | Validasi `descriptionValidate` menolak pengajuan close SO jika alasan batal tidak dipilih atau deskripsi kosong. |
| **TC-1982-03** | **Reject atas Pengajuan Fulfillment (`1982rj`)** — Pemulihan Outstanding | `1982` | P-15 | `ELSE` | Validasi penolakan request fulfillment oleh SPV (`connectToReject` ➔ `1982rj`); `referenceRevert` mengembalikan `valid_qty` menjadi outstanding aktif di SO. |
| **TC-982-01** | Retur Penjualan Reguler (`982r` ➔ `982g` ➔ `982`) | `982` | P-16, P-28 | `YES` | Validasi retur barang dari `582spd`: stok masuk kembali, jurnal balik penjualan & HPP, serta timbul saldo Credit Note Konsumen (`2010050040`). |
| **TC-9822-01** | Retur Penjualan Scan QR/Barcode Serial (`9822r` ➔ `9822g` ➔ `9822`) | `9822` | P-16, P-28 | `YES / NO` | Validasi retur unit AC dengan wajib scan barcode serial di Step 2 (`9822g`) serta blokir jika nota asal sudah diretur/direject. |
| **TC-584-01** | Siklus Penjualan Jasa (`584spo` ➔ `584so` ➔ Invoice `584`) | `584` | P-19 | `YES` | Validasi penjualan instalasi/servis tanpa mutasi stok fisik dan penggabungan beberapa SO ke 1 Invoice (`allowJoin`). |
| **TC-382-01** | Siklus Penjualan Ekspor Valas (`382spo` s/d Commercial Invoice `382`) | `382` | P-20 | `YES / NO` | Validasi penjualan ke buyer luar negeri dalam mata uang USD, konversi kurs Rupiah, dan PPN Keluaran 0%. |
| **TC-66771-01**| Penyerahan Produk Hadiah Konsumen (`66771r` ➔ `66771`) | `66771` | P-18 | `YES / NO` | Validasi pengeluaran unit hadiah atas referensi invoice `4822`, scan serial gudang, dan jurnal debet Hutang Hadiah (`2010090030`). |
| **TC-66773-01**| Penyerahan Rebate Penjualan Barang (`66773r` ➔ `66773`) | `66773` | P-18 | `YES` | Validasi pengeluaran barang potongan penjualan atas invoice `4822` dan jurnal debet Potongan Penjualan (`6010`). |
| **TC-BO-01** | Multi-Enterprise Backorder Cockpit & Anti-Cannibalization Stock | `Backorder` | P-17 | `YES / ELSE`| Validasi tampilan KPI Backorder, rumus `Net Stock = Fisik - Booking`, dan pencatatan log audit alokasi di `transaksi_log`. |
| **TC-SEC-01** | Uji Penetrasi Direct POST `doFollowup` / `doRevert` & Self-Approval SoD | `5822` | P-21, P-22, P-23| `NO` | Validasi keamanan RBAC terhadap bypass URL POST oleh akun tanpa hak akses, anti-loncat step, dan concurrency lock 5 menit idle. |
| **TC-FIN-01** | Pemotongan Deposit Konsumen, Poin Loyalitas, & Kliring Trade-In | `5822` | P-25, P-26 | `YES` | Validasi jurnal pengiriman `5822spd` yang memotong saldo Deposit/UM (`2010050010`/`2010050060`), Poin (`2010050030`), dan menyisakan Piutang Dagang bersih. |

---

## 📝 5. SKRIP EKSEKUSI KLIK-DEMI-KLIK 5W1H (CLICK-BY-CLICK TEST SCRIPTS)

---

### 📦 KLASTER A: PENJUALAN BARANG JADI & PAKET (`582`, `5822`, `5823`)

#### 🔹 Skenario A1: TC-5822-01 s/d TC-5822-08 (Siklus Penjualan Sales Support Mode & Paket — `5822`)
* **Tahap 1: Input Sales Pre-Order (`5822spo` — Aktor: Sales Admin / `o_seller`)**
  * **WHO:** Sales Admin (`o_seller`).
  * **WHAT:** Membuat draf pesanan 10 unit AC Panasonic `CS/CU-YN5WKJ` @ Rp 4.000.000 (DPP Rp 40.000.000, PPN 11% Rp 4.400.000, Grand Total Rp 44.400.000).
  * **WHERE:** `[BASE_URL]/penjualan/Create/index/5822`.
  * **WHEN:** Pesanan pelanggan diterima oleh bagian administrasi penjualan.
  * **WHY:** Mencatat rincian pesanan, memeriksa ketersediaan stok booking (`cekStokBooking`), dan mengamankan plafon kredit pelanggan (`TransaksiKreditLimit`).
  * **HOW:**
    1. Login sebagai `o_seller` $\rightarrow$ Buka URL `penjualan/Create/index/5822`.
    2. Klik **"Pilih Customer"** $\rightarrow$ Cari & pilih `PT. SINAR GRAHA SENTOSA` (ID: `101`).
    3. Pilih **"Dikirim Dari"** (`pihakMain`): Gudang Cabang (`-10`) dan pilih **Salesman Terdaftar**.
    4. Pilih **Metode Pembayaran**: `Credit` (atau `Cash` / `CIA` untuk skenario uang muka).
    5. Klik **"Pilih Item"** $\rightarrow$ Cari & pilih `PANASONIC CS/CU-YN5WKJ` (ID: `42`).
    6. Masukkan **QTY** = `10` dan pastikan harga jual = `4000000`.
    7. Periksa kalkulasi keranjang: Bruto Rp 40.000.000, PPN 11% Rp 4.400.000, Total Rp 44.400.000.
    8. Klik tombol hijau **"Make Order"**.
  * **PERCABANGAN HASIL (YES / NO / ELSE):**
    * **[YES]:** Terbit nomor Pre-Order **`5822spo.1.xxx`** dengan status *pending approval*. Plafon kredit konsumen berkurang sebesar nilai order. Lanjut ke Tahap 2.
    * **[NO]:** Jika Customer, Gudang Pengirim, atau Metode Bayar belum dipilih $\rightarrow$ Sistem memblokir penyimpanan dengan peringatan validator (TC-5822-02). Jika harga jual diisi di bawah HPP (misal Rp 2.500.000 sedangkan HPP Rp 3.000.000) $\rightarrow$ `PriceProtector` mengaktifkan karantina harga (*Quarantine*) yang hanya bisa dibuka oleh Supervisor (TC-5822-03).
    * **[ELSE]:** Jika yang dipilih adalah **Produk Paket** (`PAKET PASANG AC 1PK`) $\rightarrow$ Sistem otomatis memuat rincian komponen unit (`items9_sum`) beserta komponen paket/instalasi (`items10_sum`) di bawah baris utama.

* **Tahap 2: Otorisasi Sales Order & Diskon (`5822so` — Aktor: Sales Supervisor / `o_seller_spv`)**
  * **WHO:** Sales Supervisor (`o_seller_spv` — *User ID wajib berbeda dengan pembuat Pre-SO*).
  * **WHAT:** Meninjau margin profitabilitas pada *Profit Cockpit*, memvalidasi diskon, dan mengesahkan Pre-Order menjadi Sales Order resmi (`5822so`).
  * **WHERE:** `[BASE_URL]/penjualan/FollowUp/followupPrePreview/5822/{ID}/2/1`.
  * **WHEN:** Setelah `5822spo` muncul pada antrean otorisasi Step 2.
  * **WHY:** Menegakkan kontrol harga jual, batas diskon, dan pemisahan tugas (SoD).
  * **HOW:**
    1. Login sebagai `o_seller_spv` $\rightarrow$ Buka antrean Step 2 (`followupPrePreview/5822/{ID}/2/1`).
    2. Buka panel **Profit Cockpit** (khusus role `o_seller_spv`) untuk memeriksa HPP vs Harga Jual.
    3. Klik tombol **"Approve Order / Otorisasi Diskon"**.
  * **PERCABANGAN HASIL (YES / NO / ELSE):**
    * **[YES]:** Status dokumen naik menjadi **`5822so`** (*Sales Order Approved*). Komponen `TransaksiStatus` mencatat nilai SO sebesar Rp 44.400.000. Lanjut ke Tahap 3.
    * **[NO]:** Jika `o_seller` pembuat draf mencoba meng-approve sendiri $\rightarrow$ Sistem menolak keras via RBAC/SoD guard. Jika pesanan tidak disetujui $\rightarrow$ SPV menekan tombol **Reject** (lihat Klaster C).
    * **[ELSE]:** Jika SPV memberikan diskon tambahan di antrean Step 2 (`allowEdit=true`) $\rightarrow$ Nilai DPP, PPN 11%, dan Grand Total otomatis dihitung ulang secara *real-time* sebelum disahkan (TC-5822-04).

* **Tahap 3: Pre-Packing & Scan QR/Barcode Serial (`5822pkd` — Aktor: Petugas Gudang / `o_gudang`)**
  * **WHO:** Petugas Gudang (`o_gudang`).
  * **WHAT:** Menyiapkan fisik barang di gudang, memindai QR/Barcode nomor seri unik tiap unit (Indoor & Outdoor), dan menerbitkan dokumen Pre-Packing (`5822pkd`).
  * **WHERE:** `[BASE_URL]/penjualan/FollowUp/followupPrePreview/5822/{ID}/3/2` (Desktop) atau via Scanner Mobile (`?ismob=1`).
  * **WHEN:** Setelah `5822so` disetujui (dan pembayaran uang muka telah diterima Finance apabila metode bayar Tunai/CIA).
  * **WHY:** Mengunci nomor seri fisik spesifik ke dalam pesanan pelanggan (*In-Transit Serial Locking*) dan mencegah pengiriman barang tanpa pembayaran sah.
  * **HOW:**
    1. Login sebagai `o_gudang` $\rightarrow$ Buka transaksi pada Step 3 Pre-Packing.
    2. Jika menggunakan tablet scanner gudang $\rightarrow$ Scan QR dokumen untuk memicu `followupDariHp()` (redirect langsung `location.href` tanpa iframe sesuai Protokol 3.9).
    3. Scan nomor seri barcode pada masing-masing unit fisik (`doScan`) hingga jumlah serial cocok dengan kuantitas pesanan (10 unit).
    4. Pilih **Nama Pengirim / Kurir** dan klik **"Process Packing"**.
  * **PERCABANGAN HASIL (YES / NO / ELSE):**
    * **[YES]:** Terbit nomor Pre-Packing **`5822pkd.1.xxx`** (Surat Jalan Persiapan). Nomor seri unit dipindahkan dari stok bebas ke `RekeningPembantuProdukPerSerialIntransit`. Lanjut ke Tahap 4.
    * **[NO]:**
      * Apabila metode pembayaran SO adalah Tunai/CIA tetapi kasir Finance belum menerima uang muka (`incoming cash`) $\rightarrow$ Layar terkunci dengan peringatan merah: *"Uang muka belum diterima. Segera hubungi pihak Finance."* (TC-5822-05).
      * Apabila barcode serial yang di-scan tidak terdaftar di gudang cabang atau sudah terpakai di transaksi lain $\rightarrow$ Scanner menolak serial tersebut (TC-5822-07).
    * **[ELSE]:** Apabila stok di gudang hanya tersedia 6 unit dari 10 unit yang dipesan $\rightarrow$ Petugas mencentang/mengubah kuantitas kirim menjadi `6` unit (`doTicParsial`). Sistem memproses 6 unit ke `5822pkd`, sedangkan 4 unit sisanya tetap berstatus *outstanding* di `5822so` dan muncul di **Backorder Cockpit** (TC-5822-08).

* **Tahap 4: Pengiriman Final / Delivery Note (`5822spd` — Aktor: Checker Gudang / `o_gudang`)**
  * **WHO:** Checker Gudang (`o_gudang`).
  * **WHAT:** Memverifikasi muatan barang di armada pengiriman dan mengesahkan pengeluaran barang (*Process Shipment*).
  * **WHERE:** `[BASE_URL]/penjualan/FollowUp/followupPrePreview/5822/{ID}/4/3`.
  * **WHEN:** Barang fisik naik ke truk pengiriman menuju lokasi pelanggan.
  * **WHY:** Mengeluarkan stok fisik secara sah dari pembukuan gudang, mengakui HPP penjualan, dan menerbitkan Piutang Dagang / Invoice.
  * **HOW:**
    1. Buka transaksi pada Step 4 (`PACKING LIST / DELIVERY NOTE`).
    2. Periksa kesesuaian item, nomor seri, dan pengirim.
    3. Klik tombol hijau **"Process Shipment"**.
  * **PERCABANGAN HASIL (YES / NO / ELSE):**
    * **[YES]:** Status naik menjadi **`5822spd`** (*Shipped / Delivery Note Sah*) dan otomatis men-trigger langkah Invoicing (`autoNextStep = true`).
      * *Mutasi Stok & Serial:* `LockerStock` mengurangi stok `hold` (-10) dan menambah stok `sold` (+10); `LockerStockMutasi` mencatat pengeluaran (`-qty`); `ProdukSerialNumberLocker` mengunci serial menjadi `jumlah = 0` (terjual).
      * *Jurnal Akuntansi Cabang (Balance Rp 0,00):*
        * **DEBET Piutang Dagang Reguler (`1010020010`)** *(atau potongan Uang Muka `2010050`)*: Rp 44.400.000,00
        * **DEBET Harga Pokok Penjualan / HPP (`5010`)**: Rp 30.000.000,00
        * **KREDIT Penjualan Lokal (`4010`)**: Rp 40.000.000,00
        * **KREDIT PPN Keluaran (`2030060`)**: Rp 4.400.000,00
        * **KREDIT Persediaan Produk FG (`1010030030`)**: Rp 30.000.000,00
      * *Jurnal Akuntansi Pusat (Auto-Geser HPP Riil `cabang_id = -1`):*
        * **DEBET HPP Riil (`5030`)**: Rp 30.000.000,00 | **KREDIT Persediaan Produk Riil (`8020`)**: Rp 30.000.000,00.
    * **[NO]:** Jika koneksi terputus atau petugas ke-2 menekan tombol kirim secara simultan di tablet lain $\rightarrow$ Concurrency guard mencegah penerbitan Delivery Note ganda (TC-SEC-01).
    * **[ELSE]:** Jika penjualan dilakukan melalui kanal Marketplace $\rightarrow$ Piutang otomatis dibukukan ke **Piutang Dagang Marketplace (`1010020090`)** atas nama entitas marketplace terkait (TC-FIN-01).

---

#### 🔹 Skenario A2: TC-582-01 & TC-5823-01 (Varian Salesman Mode `582` & POS Tunai Cepat `5823`)
* **Varian `582` (Salesman Mode):**
  * **WHERE:** `[BASE_URL]/penjualan/Create/index/582`.
  * **HOW:** Alur berjalan melalui 4 step: Step 1 `582spo` (`o_seller`) $\rightarrow$ Step 2 `582so` (`o_seller_spv`) $\rightarrow$ Step 3 `582pkd` (`o_gudang`) $\rightarrow$ Step 4 `582spd` (`o_gudang`).
  * **PERCABANGAN HASIL:** **[YES]** Jurnal penjualan, HPP, PPN Keluaran, dan pemotongan poin/uang muka tercatat identik dengan standar akuntansi penjualan lokal. **[NO]** Ditolak jika melebihi kredit limit. **[ELSE]** Mendukung multi-tier harga (`jual_reseller`, `jual_online`, `jual`).
* **Varian `5823` (POS / Point of Sale Mode):**
  * **WHERE:** `[BASE_URL]/penjualan/Create/index/5823`.
  * **HOW:**
    1. Kasir/Sales Admin membuat Entry POS di Step 1 (`5823spo`).
    2. Karena `autoNextStep = true` pada Step 2 (`5823so`), pesanan langsung siap dibayar di Kasir Finance (`4464`/`4467`).
    3. Setelah lunas, Gudang memproses Step 3 (`5823pkd` + scan serial) dan Step 4 (`5823spd` serah terima barang ke konsumen).
  * **PERCABANGAN HASIL:** **[YES]** Saat `5823spd` disahkan, saldo Uang Muka Konsumen (`2010050`) yang dibayar di kasir langsung didebet (habis terpakai Rp 0) dan diakui sebagai Pendapatan Penjualan (`4010`) + PPN Keluaran (`2030060`). **[NO]** Step 3 terkunci rapat sebelum kasir mengonfirmasi penerimaan uang.

---

### 🛑 KLASTER C: PEMBATALAN BERTAHAP (REJECT 1 STEP vs REJECT ALL STEPS) & FULFILLMENT (`1982` / `19822`)

*(Bagian ini menguji secara spesifik fitur `doRevert`, `doRevertAll`, `dialog_rejection_payment_resolution.php`, `ajaxPindahkanUangMukaToCustomer`, serta transaksi penutupan sisa SO `1982` dan `19822`)*

#### 🔹 Skenario C1: TC-REV-01 s/d TC-REV-03 (**Reject 1 Step / `doRevert`** — Mundur Satu Langkah)
* **Konsep Arsitektur `doRevert`:**
  * Pada dokumen **Pre-Packing (`5822pkd` — Step 3)**: *Reject 1 Step* mengembalikan transaksi ke **Sales Order (`5822so` — Step 2)** agar gudang dapat mengulang proses packing/scan serial. Apabila SO tersebut sudah lunas / memiliki Uang Muka, **saldo uang muka TETAP MELEKAT pada pesanan** (tidak dipindahkan ke deposit bebas).
  * Pada dokumen **Sales Order Induk (`5822so` — Step 2)**: *Reject 1 Step* mengembalikan transaksi ke **Draft Pre-Order (`5822spo` — Step 1)**. Jika SO tersebut sudah lunas, uang muka wajib dialihkan ke Deposit Konsumen atau di-void di Finance terlebih dahulu agar draf kembali bebas dari ikatan pembayaran.
* **WHO:** Sales Supervisor (`o_seller_spv`) atau Kepala Gudang.
* **WHERE:** Halaman Preview Transaksi $\rightarrow$ Endpoint `[BASE_URL]/penjualan/FollowUp/doRevert/5822/{ID}/{masterTargetStep}/{childTargetStep}/{currentStep}`.
* **HOW (Uji Vektor YES, NO, ELSE):**
  1. **Uji Vektor [YES] pada Level Pre-Packing (`5822pkd` ➔ `5822so` — TC-REV-01):**
     * Buka dokumen `5822pkd` (Step 3) yang **belum** diterbitkan Surat Jalan (`5822spd`).
     * Klik tombol **"Reject 1 Step"**.
     * **Hasil [YES]:** Status dokumen mundur dari Step 3 (`5822pkd`) kembali ke Step 2 (`5822so`). Nomor seri yang sebelumnya *in-transit* dilepas kembali ke stok gudang aktif. Jika SO memiliki uang muka, saldo uang muka tetap terikat utuh pada `5822so`.
  2. **Uji Vektor [YES] pada Level SO Belum Bayar (`5822so` ➔ `5822spo` — TC-REV-02):**
     * Buka dokumen `5822so` (Step 2) yang belum dibayar dan belum di-packing.
     * Klik tombol **"Reject 1 Step"**.
     * **Hasil [YES]:** Status mundur ke Step 1 (`5822spo`), siap diedit kembali oleh `o_seller`.
  3. **Uji Vektor [NO] Pencegatan Saat Sudah Ada Pengiriman `spd` atau Faktur Pajak `110` (TC-REV-03):**
     * Buka dokumen `5822so` atau `5822pkd` yang sudah memiliki turunan pengiriman `5822spd` atau Faktur Pajak (`110r`, `110e`, `110`).
     * Coba eksekusi **Reject 1 Step (`doRevert`)**.
     * **Hasil [NO]:** Sistem (`FollowUp.php` baris 16440) **MEMBLOKIR KERAS** proses revert karena barang sudah dikirim atau faktur pajak sudah terbit.
  4. **Uji Vektor [ELSE] Reject 1 Step pada SO Induk yang Sudah Lunas:**
     * Buka `5822so` (Step 2) yang sudah dibayar lunas di kasir, lalu klik **Reject 1 Step**.
     * **Hasil [ELSE]:** Sistem menampilkan **Dialog Intersepsi Resolusi Pembayaran** (`dialog_rejection_payment_resolution.php`) dengan 3 opsi berbasis kebutuhan operasional riil:
       * **Opsi 1 (Deposit):** Uang muka dialihkan ke deposit konsumen (User bebas memilih *Reject 1 Step* atau *Reject All Steps*).
       * **Opsi 2 (Finance Refund):** Void/refund bukti kas di Finance (User bebas memilih *Reject 1 Step* atau *Reject All Steps* setelah kas di-void).
       * **Opsi 3 (Batal Sebagian Barang / Tetap Belanja):** Mundur 1 step (`doRevert`) ke Draf/SO dengan saldo uang muka TETAP MELEKAT pada pesanan untuk merevisi item yang dibatalkan.

---

#### 🔹 Skenario C2: TC-REV-04 s/d TC-REV-11 (**Reject All Steps / Reject 1 Step** & Resolusi Finansial 3 Opsi SO Lunas)
* **Konsep Arsitektur Pembatalan Bertahap vs Permanen:**
  * **Reject 1 Step (`doRevert`):** Mengembalikan dokumen 1 langkah ke belakang untuk revisi tanpa merusak mata rantai transaksi secara keseluruhan.
  * **Reject All Steps (`doRevertAll`):** Membatalkan seluruh dokumen dari step aktif sampai ke hulu (`id_master` / Step 1) secara permanen (`trash_4 = 1`) dan melepas stok hold 100%.
* **WHO:** Sales Supervisor (`o_seller_spv`).
* **WHERE:** Halaman Preview SO (`5822so`/`5823so`) atau Pre-Packing (`5822pkd`/`5823pkd`) $\rightarrow$ Endpoint `[BASE_URL]/penjualan/FollowUp/doRevert/5822/{ID}/...` atau `[BASE_URL]/penjualan/FollowUp/doRevertAll/5822/{ID}/{currentStep}`.
* **HOW & PERCABANGAN HASIL (YES / NO / ELSE):**
  * **Kasus 1 — [YES] Reject via OPSI 1 (Saldo Dialihkan ke Deposit Konsumen — TC-REV-05 & TC-REV-11):**
    1. Siapkan SO Tunai (`5822so` atau `5823so`) senilai Rp 11.100.000 yang sudah dibayar lunas di Finance (tercatat di `transaksi_payment_source` dengan `label='uang muka'`, `terbayar=11100000`). Pastikan belum ada pengiriman `spd` aktif.
    2. Buka dokumen SO $\rightarrow$ Muncul panel kuning **"Sales Order nomer ... sudah LUNAS (Rp 11.100.000)"**.
    3. Klik kartu **"Opsi 1: Saldo dialihkan ke Deposit Konsumen (Proses Langsung)"**.
    4. Pada kolom **Alasan Reject / Pembatalan (Wajib Diisi)**, ketik: `"Konsumen ganti tipe kapasitas AC ke 2 PK"` ($\ge 5$ karakter).
    5. Centang checkbox: **"Ya, saya mengerti & setuju memindahkan Uang Muka ini ke saldo deposit konsumen"**.
    6. **Kebebasan Pemilihan Tombol Aksi (TC-REV-11):** Sistem secara otomatis mengaktifkan **KEDUA TOMBOL** (*Reject 1 Step* dan *Reject All Steps*) secara serentak. Keputusan operasional diserahkan sepenuhnya kepada Supervisor:
       * **Pilihan A (*Reject All Steps*):** Pesanan dibatalkan permanen (`trash_4 = 1`), stok hold dilepas 100%, dan uang muka Rp 11.100.000 dipindahkan ke deposit konsumen (`transaksi_uang_muka_source`).
       * **Pilihan B (*Reject 1 Step*):** Pesanan dimundurkan 1 langkah ke draft `5822spo` dengan uang muka dipindahkan ke deposit konsumen agar draf baru bersih dari ikatan bayar lama.
    7. **Hasil [YES]:**
       * Endpoint `ajaxPindahkanUangMukaToCustomer()` men-set `terbayar = 0`, `returned = 11100000`, `dihapus = 1` pada `transaksi_payment_source` SO lama.
       * Saldo Rp 11.100.000 otomatis ditambahkan ke tabel `transaksi_uang_muka_source` (`label = 'uang muka konsumen'`, `sisa = 11100000`) atas nama konsumen tersebut.
       * Kolom `deskripsi` pada tabel `transaksi` mencatat jejak audit lengkap: `[REJECT SO LUNAS - Tanggal oleh User] Dialihkan ke deposit ... Alasan: ...`.
  * **Kasus 2 — [ELSE] Reject via OPSI 2 (Batalkan / Refund Kas Multi-Kanal di Finance — TC-REV-06, TC-REV-10, & TC-REV-11):**
    1. Pada SO Lunas lainnya, klik kartu **"Opsi 2: Batalkan / Refund Bukti Kas di Finance"**.
    2. Sistem memindai seluruh kanal transaksi penerimaan kas yang terhubung: Kasir Toko Tunai (`4464`), Kasir EDC/Transfer (`4467`), Penerimaan Bank / Piutang AR (`4404`), Kas Besar (`4403`), dan Kas Masuk Lainnya (`749`) — **TC-REV-10**.
    3. Jika SPV langsung mencoba mengeksekusi reject saat bukti kas masih berstatus aktif (`trash=0 AND trash_4=0`) $\rightarrow$ Sistem **MENOLAK** (`Validasi Gagal: Masih terdapat bukti kas aktif di Finance. Harap batalkan bukti kas terlebih dahulu`).
    4. Mintalah bagian Finance (`c_finance`) mem-void/membatalkan bukti kas tersebut di modul Finance/Kas.
    5. Kembali ke layar SPV $\rightarrow$ Klik tombol **"Refresh Status Pembayaran"** (`ajaxGetStatusKasSO`). Sistem mendeteksi kas sudah berstatus *Dibatalkan/Void*.
    6. Isi alasan minimal 5 karakter, centang konfirmasi persetujuan.
    7. **Kebebasan Pemilihan Tombol Aksi (TC-REV-11):** KEDUA TOMBOL (*Reject 1 Step* dan *Reject All Steps*) aktif bersamaan. SPV dapat memilih membatalkan permanen (*Reject All Steps*) atau memundurkan 1 langkah (*Reject 1 Step*) tanpa meninggalkan saldo deposit fiktif.
  * **Kasus 3 — [NO] Pencegatan Validasi Alasan, Ceklist, & Surat Jalan Aktif (TC-REV-07 & TC-REV-08):**
    1. **Jika terdapat Surat Jalan (`5822spd`) Aktif (`countSpdActive > 0`):** Panel menampilkan kotak merah **"PERINGATAN: TERDAPAT SURAT JALAN AKTIF KE KONSUMEN — Tombol Reject All Steps dikunci"**. Jika dicoba tembak via POST langsung $\rightarrow$ Backend menolak keras di baris 38213.
    2. **Jika Alasan $< 5$ Karakter (misal `"batal"` = 5 kar vs `"btl"` = 3 kar) atau Ceklist tidak dicentang:** Backend `doRevert` (baris 16404) & `doRevertAll` (baris 23819) langsung menghentikan eksekusi dengan `mati_disini("Validasi Gagal: Alasan reject/pembatalan Sales Order wajib diisi minimal 5 karakter.")`.
  * **Kasus 4 — [YES] Reject 1 Step via OPSI 3 (Batal Sebagian Barang / Tetap Belanja — Uang Muka Tetap Melekat — TC-REV-09):**
    1. Siapkan SO Lunas `5822so` senilai Rp 10.012.200 (misal pembayaran kasir/bank `4404.1.14687`). Dokumen berada di Step 3 (Pre-Packing) atau Step 2 (SO).
    2. Konsumen mengonfirmasi tetap berbelanja, namun ingin membatalkan atau mengurangi sebagian unit barang yang belum siap dipasang.
    3. Klik kartu **"Opsi 3: Batal Sebagian Barang / Revisi Draf (Tetap Belanja)"**.
    4. Panel konfirmasi Opsi 3 terbuka:
       * Tombol **"Reject 1 Step"** otomatis diaktifkan dan berwarna oranye/biru.
       * Tombol **"Reject All Steps"** **DINONAKTIFKAN** secara ketat (karena tujuan bisnis adalah revisi parsial, bukan membatalkan seluruh order).
    5. Pada kolom **Alasan Revisi / Pengurangan Barang (Wajib Diisi)**, ketik: `"Konsumen membatalkan unit indoor kedua karena ruangan renovasi belum siap"` ($\ge 5$ karakter).
    6. Centang checkbox: **"Ya, saya mengerti & setuju memundurkan pesanan ini 1 langkah untuk merevisi barang dengan saldo uang muka tetap melekat pada pesanan"**.
    7. Klik tombol **"Reject 1 Step"** (`doRevert`).
    8. **Hasil [YES]:**
       * Dokumen mundur 1 langkah (dari Step 3 `5822pkd` kembali ke Step 2 `5822so`, atau dari Step 2 `5822so` kembali ke Step 1 `5822spo`).
       * Nomor seri yang sedang dipersiapkan di gudang dilepas kembali menjadi persediaan aktif gudang.
       * Saldo Uang Muka Rp 10.012.200 **TETAP MELEKAT PADA TRANSAKSI** (tidak dialihkan ke deposit konsumen dan bukti kas tidak di-void).
       * Audit log mencatat: `[REVERT 1 STEP OPSI 3] Alasan: Konsumen membatalkan unit indoor kedua... Uang Muka tetap melekat`.
       * Petugas penjualan membuka draf untuk menghapus unit barang yang batal. Saat invoice akhir terbit nanti, saldo uang muka otomatis memotong nilai tagihan baru secara akurat.
  * **Kasus 5 — [YES] Audit Integritas State Machine Tombol Aksi (TC-REV-10 & TC-REV-11):**
    1. Memverifikasi bahwa pemilihan kartu Opsi 1 (Deposit) atau Opsi 2 (Refund Finance) selalu membuka kebebasan memilih kedua tombol (*Reject 1 Step* dan *Reject All Steps*) selama tidak ada Surat Jalan aktif (`_countActiveSpd == 0`).
    2. Memverifikasi bahwa jika terdapat Surat Jalan aktif (`_countActiveSpd > 0`), tombol *Reject All Steps* terkunci secara global di semua opsi, menjaga kepatuhan fisik gudang dan saldo persediaan.

---

#### 🔹 Skenario C3: TC-1982-01 s/d TC-1982-03 (**Fulfillment / Close Sisa Sales Order — `1982` & `19822`**)
* **Alur Bisnis:** Konsumen memesan 10 unit AC pada `5822so`. Gudang telah mengirim 6 unit via `5822spd`. Sisa 4 unit (`outstanding = 4`) tidak jadi diambil oleh konsumen dan disepakati untuk ditutup (*Close/Fulfillment SO*) agar tidak menggantung di antrean gudang/backorder dan saldo tagihan SO disesuaikan.
* **Tahap 1: Pengajuan Request Close/Fulfillment (`1982` / `19822` — Aktor: Sales Admin / `o_seller`)**
  * **WHO:** Sales Admin (`o_seller`).
  * **WHERE:** `[BASE_URL]/penjualan/FollowUp/preCancelPacking/5822/{ID_SO}` atau `[BASE_URL]/penjualan/Create/index/19822`.
  * **HOW:**
    1. Buka transaksi SO yang memiliki sisa pengiriman (atau melalui menu Close/Fulfillment Sales Order `1982` untuk reguler `582`, dan `19822` untuk support `5822`).
    2. Pilih Customer dan pilih nomor referensi Sales Order / Packing.
    3. Tabel menampilkan kolom: `qty SO` (10), `qty packed` (0), `qty shipped` (6), dan `qty akan dicancel` (`jml`).
    4. Pastikan kolom **`jml` (akan dicancel)** berisi `4` sehingga kolom **`outstanding`** menjadi **`0`**.
    5. Pilih **Reason Details (`MdlAlasanBatal`)** pada radio button dan isi penjelasan lengkap pada kolom **Deskripsi Alasan**.
    6. Klik **"Make Close/Fulfillment Transaksi"**.
  * **PERCABANGAN HASIL (YES / NO / ELSE):**
    * **[YES]:** Terbit nomor pengajuan **`1982.1.xxx`** atau **`19822.1.xxx`** dengan status *request close/fullfillment transaksi*. Nilai `req_cancel_jml` bertambah 4. Lanjut ke Tahap 2.
    * **[NO]:** Jika kolom deskripsi alasan dikosongkan $\rightarrow$ `descriptionValidate` memblokir penyimpanan dengan pesan: *"detail alasan harus diisikan di formulir deskripsi."* (TC-1982-02).
    * **[ELSE]:** Jika konsumen hanya ingin membatalkan 2 unit dari 4 unit sisa (sehingga 2 unit lagi tetap akan dikirim minggu depan) $\rightarrow$ Ubah `jml` cancel menjadi `2`; sistem mencatat `outstanding` tersisa `2` unit.

* **Tahap 2: Otorisasi Close/Fulfillment (`1982g` / `19822g` — Aktor: Sales Supervisor / `o_seller_spv`)**
  * **WHO:** Sales Supervisor (`o_seller_spv`).
  * **WHERE:** `[BASE_URL]/penjualan/FollowUp/followupCancelPackingPrePreview/19822/{ID}/2/1` $\rightarrow$ Eksekutor `doCancelPacking()`.
  * **HOW:**
    1. Login sebagai `o_seller_spv` $\rightarrow$ Buka dokumen request `1982` / `19822` di Step 2.
    2. Periksa rincian kuantitas yang dibatalkan (4 unit) beserta alasan pembatalannya.
    3. Klik tombol **"Approve Close/Fulfillment Transaksi"**.
  * **PERCABANGAN HASIL (YES / NO / ELSE):**
    * **[YES]:** Status naik menjadi **`1982g`** / **`19822g`** (*packing/order canceled*).
      * Kuantitas `cancel_jml` pada detail SO resmi bertambah +4 dan `valid_qty` sisa tertutup menjadi **0 (*Zero Outstanding*)**.
      * PostProcessor `TransaksiStatus` dan `PaymentSourceFullfill` mencatat `transaksi_fullfillment` sebesar nilai 4 unit yang ditutup dan menyesuaikan nilai `transaksi_nett` / `transaksi_saldo` SO induk.
      * Item tersebut otomatis bersih dari daftar **Backorder Cockpit**.
    * **[NO]:** Jika `o_seller` mencoba meng-approve sendiri penutupan SO $\rightarrow$ Ditolak oleh validasi `userGroup = o_seller_spv`.
    * **[ELSE] (Reject atas Request Fulfillment — `1982rj` / TC-1982-03):** Jika SPV tidak menyetujui penutupan sisa SO karena barang sudah siap dikirim $\rightarrow$ SPV klik **"REJECT CLOSE/FULLFILLMENT REQUEST"** (`connectToReject` ➔ `1982rj`). Komponen `referenceRevert` otomatis mengembalikan `valid_qty` (+4 unit) ke Sales Order induk sehingga siap di-packing kembali oleh gudang!

---

### 🔄 KLASTER D: RETUR PENJUALAN DARI KONSUMEN (`982` & `9822`)

#### 🔹 Skenario D1: TC-9822-01 & TC-982-01 (Retur Penjualan Scan Barcode `9822` & Reguler `982`)
* **Alur Bisnis:** Dari 6 unit AC yang telah dikirim via Delivery Note (`5822spd`), terdapat 1 unit yang dikembalikan oleh konsumen karena cacat fungsi. Sistem wajib menerima kembali fisik unit beserta nomor serinya, membalik HPP & Penjualan, serta menerbitkan saldo Credit Note / Pengurang Piutang bagi konsumen.
* **Tahap 1: Input Return Request (`9822r` / `982r` — Aktor: Sales Admin / `o_seller`)**
  * **WHERE:** `[BASE_URL]/penjualan/Create/index/9822` (atau `982` untuk nota `582spd`).
  * **HOW:**
    1. Buka URL `penjualan/Create/index/9822`.
    2. Pilih Customer: `PT. SINAR GRAHA SENTOSA` (ID: `101`).
    3. Klik pemilih Nota Pengiriman (`_selectorItem` dengan filter `jenis=.5822spd`, `returned=.0`, `trash_4<>.1`) $\rightarrow$ Pilih nomor Delivery Note `5822spd` terkait.
    4. Ubah kuantitas item yang diretur menjadi `1` unit (dari 6 unit terkirim).
    5. Isi keterangan alasan retur dan klik **"Make Return Request"**.
  * **PERCABANGAN HASIL:**
    * **[YES]:** Terbit nomor **`9822r.1.xxx`**. Komponen `TransaksiItemReturnUpdate` menandai kuantitas yang sedang diretur pada nota asal agar tidak bisa diretur ganda.
    * **[NO]:** Mencoba memilih nota `5822spd` yang sudah pernah diretur habis (`returned=1`) atau sudah di-reject (`trash_4=1`) $\rightarrow$ Nota otomatis disaring dan tidak dapat dipilih.

* **Tahap 2: Scan QR/Barcode Unit Retur & Otorisasi (`9822g` / `982g` — Aktor: Sales SPV / `o_seller_spv`)**
  * **WHERE:** `[BASE_URL]/penjualan/FollowUp/followupPrePreview/9822/{ID}/2/1`.
  * **HOW:**
    1. Buka transaksi Step 2 (`SCAN QR/BARCODE`).
    2. Scan nomor seri barcode unit fisik yang dikembalikan oleh konsumen.
    3. Klik **"Simpan / Approve Return Request"**.
  * **PERCABANGAN HASIL:**
    * **[YES]:** Nomor seri valid (sesuai dengan serial yang dulu dikirim pada `5822spd`) $\rightarrow$ Status naik menjadi **`9822g`** dan `ReleaserDueDate` menyesuaikan jatuh tempo nota.
    * **[NO]:** Nomor seri yang di-scan bukan berasal dari nota pengiriman konsumen tersebut $\rightarrow$ Ditolak oleh validator serial.

* **Tahap 3: Penerimaan Fisik Barang Retur / GRN Retur (`9822` / `982` — Aktor: `o_seller_spv`)**
  * **WHERE:** `[BASE_URL]/penjualan/FollowUp/followupPrePreview/9822/{ID}/3/2`.
  * **HOW:** Klik tombol **"Terima Return Penjualan"**.
  * **PERCABANGAN HASIL:**
    * **[YES]:** Status naik menjadi **`9822`** (atau **`982`**) — *GRN Return Made*.
      * *Stok Fisik:* `LockerStock` mengurangi status `sold` (`-qty`) dan mengembalikan stok aktif ke gudang penerima (`FifoAverage` & `RekeningPembantuProduk`).
      * *Jurnal Akuntansi Retur Penjualan (Balance Rp 0,00):*
        * **DEBET Retur Penjualan (`4020`)**: Rp 4.000.000,00 (`nett1`)
        * **DEBET PPN Keluaran (`2030060`)**: Rp 440.000,00 (`-ppn` / mengurangi kewajiban PPN Keluaran)
        * **DEBET Persediaan Produk FG (`1010030030`)**: Rp 3.000.000,00 (`hpp`)
        * **KREDIT Hutang ke Konsumen / Credit Note Retur (`2010050` sub `2010050040`)**: Rp 4.440.000,00 (`nett2`)
        * **KREDIT Harga Pokok Penjualan / HPP (`5010`)**: Rp 3.000.000,00 (`-hpp`)
      * *Efek Finansial:* `PaymentAntiSource` & `PaymentAntisourceCustomer` membentuk saldo pengurang piutang / Credit Note sebesar Rp 4.440.000 atas nama konsumen.

---

### 🌐 KLASTER B & E: PENJUALAN JASA (`584`), EKSPOR (`382`), HADIAH (`66771`) & REBATE (`66773`)

#### 🔹 Skenario B1: TC-584-01 (Penjualan Jasa / Service Sales — `584`)
* **WHERE:** `[BASE_URL]/penjualan/Create/index/584`.
* **HOW:**
  1. **Step 1 (`584spo` — `o_seller`):** Pilih konsumen $\rightarrow$ Pilih item dari `MdlProdukJasa` (misal: *Jasa Instalasi Pipa Refrigerant* senilai Rp 5.000.000) $\rightarrow$ Klik *Make Order*.
  2. **Step 2 (`584so` — `o_seller_spv`):** Review harga jasa dan klik *Approve Order*.
  3. **Step 3 (`584` — `o_finance`):** Finance membuka Step 3 (*Create Invoice*). Jika konsumen memiliki beberapa SO Jasa aktif, uji fitur penggabungan invoice (`allowJoin = true`) $\rightarrow$ Klik *Create Invoice*.
* **PERCABANGAN HASIL:** **[YES]** Terbit Invoice Jasa `584`, mengakui Piutang Dagang dan Pendapatan Jasa + PPN 11% tanpa mengurangi stok barang gudang. **[NO]** Non-finance dilarang menerbitkan Invoice Step 3.

#### 🔹 Skenario B2: TC-382-01 (Penjualan Internasional / Ekspor Valas — `382`)
* **WHERE:** `[BASE_URL]/penjualan/Create/index/382`.
* **HOW:**
  1. Pilih Customer Ekspor (`GLOBAL COOLING PTE LTD`, `country <> ID`).
  2. Pilih mata uang `USD` beserta kurs pertukaran (`valasDetails__exchange`, misal Rp 16.000/USD).
  3. Pilih produk dan jalankan 5 tahapan: `382spo` (Step 1) $\rightarrow$ `382so` (Step 2) $\rightarrow$ `382pkd` (Step 3) $\rightarrow$ `382spd` (Step 4 Export Shipment) $\rightarrow$ `382` (Step 5 Commercial Invoice).
* **PERCABANGAN HASIL:** **[YES]** Nilai penjualan tercatat dalam Valas (`nett2_valas`) dan Rupiah, dengan tarif PPN Ekspor otomatis `0%` (`ppn = .0`). **[NO]** Konsumen lokal (`country = ID`) ditolak oleh filter modul `382`.

#### 🔹 Skenario E1: TC-66771-01 & TC-66773-01 (Penyerahan Produk Hadiah `66771` & Rebate Barang `66773`)
* **WHERE:** `[BASE_URL]/penjualan/Create/index/66771` dan `[BASE_URL]/penjualan/Create/index/66773`.
* **HOW:**
  1. **Step 1 (`66771r` / `66773r` — `sys`):** Pilih konsumen (pastikan jenis konsumen Corporate/Perorangan sudah terisi di profil) $\rightarrow$ Pilih referensi Invoice Konsumen (`jenis = 4822`, `trash_4 = 0`) $\rightarrow$ Pilih produk hadiah / barang rebate $\rightarrow$ Klik *Simpan*.
  2. **Step 2 (`66771` / `66773` — `o_gudang`):** Petugas gudang memindai serial barang hadiah/rebate (`allowScaner = true`) dan klik *Process Shipment*.
* **PERCABANGAN HASIL:**
  * **[YES - `66771` (Hadiah)]:** Stok produk keluar (`-hpp`), jurnal mendebet **Hutang Hadiah Produk (`2010090030`)** dan mengkredit **Persediaan Produk (`1010030030`)** (+ penyesuaian `7010010` bila ada selisih FIFO).
  * **[YES - `66773` (Rebate Barang)]:** Stok produk keluar (`-hpp`), jurnal mendebet **Potongan Penjualan / Biaya Usaha (`6010`)** dan mengkredit **Persediaan Produk (`1010030030`)** serta PPN Keluaran (`2030060`).
  * **[NO]:** Jika profil konsumen belum memiliki `folder_id` (Corporate/Perorangan), `pihakKoreksiData` memblokir proses dan menampilkan tautan koreksi master data customer.

---

### 🗼 KLASTER F: MULTI-ENTERPRISE BACKORDER COCKPIT & KEAMANAN PENETRASI

#### 🔹 Skenario F1: TC-BO-01 (Multi-Enterprise Backorder Management Cockpit — `Backorder.php`)
* **WHO:** Sales Supervisor / Supply Chain Manager.
* **WHAT:** Memantau pesanan Pre-SO/SO yang tertunda pemenuhannya, memvalidasi akurasi rumus *Dual-Tier Net Available Stock*, dan mengeksekusi rekomendasi alokasi stok.
* **WHERE:** `[BASE_URL]/penjualan/Backorder/index/5822`.
* **HOW:**
  1. Buka URL `penjualan/Backorder/index/5822`.
  2. Verifikasi 4 kartu KPI Eksekutif di bagian atas layar: **Total Backorder Qty**, **Impacted Entities**, **Avg Aging Days**, dan **Critical Orders (> 7 hari)**.
  3. Periksa tabel matriks untuk item yang sedang berstatus backorder: bandingkan kolom **Stok Cabang Fisik**, **Stok Cabang Booking** (SO aktif yang belum kirim), dan **Stok Cabang Net (`GREATEST(0, Fisik - Booking)`)**.
  4. Klik tombol aksi eksekusi rekomendasi alokasi pada salah satu baris item (`execute_allocation_ajax`).
* **PERCABANGAN HASIL (YES / NO / ELSE):**
  * **[YES]:** Sistem merespons JSON `status: success` (*"Alokasi kuantitas backorder berhasil dieksekusi dan dicatat di log audit"*) dan menulis record baru ke tabel `transaksi_log`.
  * **[NO]:** Jika diakses tanpa sesi login atau parameter `detail_id` kosong $\rightarrow$ Endpoint menolak eksekusi dengan pesan error JSON.
  * **[ELSE] (Anti-Cannibalization):** Jika di gudang cabang terdapat fisik 10 unit, namun ke-10 unit tersebut sudah terikat pada `5822so` pelanggan lain (`qty_booking = 10`), maka **Stok Cabang Net** tampil **`0`**, dan sistem mengarahkan rekomendasi ke *Inter-Branch / National Stock* atau *Purchase Order Fulfillment*.

#### 🔹 Skenario F2: TC-SEC-01 (Uji Penetrasi RBAC, Anti-Loncat Step, & Concurrency Lock 5 Menit)
* **HOW:**
  1. **Direct POST Bypass:** Login sebagai `o_seller` (pembuat draf), lalu kirim HTTP POST langsung ke `penjualan/FollowUp/doFollowup/5822/{ID_SPO}/2/1` $\rightarrow$ **[NO / Blokir]** Sistem wajib menolak otorisasi karena bukan `o_seller_spv` dan melanggar SoD.
  2. **State Jumping:** Dari dokumen `5822spo` (Step 1), ubah URL target langsung ke Step 4 (`doFollowup/5822/{ID_SPO}/4/1`) $\rightarrow$ **[NO / Blokir]** Sistem menggagalkan proses karena belum melewati Step 2 (`so`) dan Step 3 (`pkd`).
  3. **Locker Transaksi & Ambil Alih Idle $\ge 5$ Menit:** Buka transaksi `5822so` di Browser A (User 1). Buka transaksi yang sama di Browser B (User 2) $\rightarrow$ **[NO]** Browser B diblokir dengan halaman *"Transaksi Terkunci"*. Biarkan Browser A tidak aktif selama $\ge 5$ menit, lalu muat ulang Browser B $\rightarrow$ **[ELSE]** Tombol **"Ambil Alih Transaksi (Petugas Idle)"** (`?forceRetakeLock=1`) muncul dan User 2 dapat mengambil alih kunci secara sah.

---

## 📊 6. REKONSILIASI AUDIT DATABASE & MATRIKS JURNAL BERIMBANG

Jalankan query SQL berikut di MariaDB untuk memverifikasi integritas transaksi, keseimbangan jurnal, mutasi stok/serial, dan pengalihan uang muka:

### 6.1 Audit Status Transaksi, Reject (`trash_4`), & Jejak Deskripsi (Tabel `transaksi`)
```sql
SELECT id, id_master, nomer, jenis, step_number, transaksi_nilai, pembayaran, trash, trash_4, deskripsi, oleh_id, oleh_nama, dtime
FROM transaksi
WHERE id_master = '{ID_MASTER_SO}'
ORDER BY id ASC;
```
* **Kriteria Lulus Reject 1 Step (`doRevert`):** Hanya step terakhir yang dibatalkan, sedangkan dokumen induk (`5822so`) tetap `trash_4 = 0`.
* **Kriteria Lulus Reject All Steps (`doRevertAll`):** Seluruh baris transaksi dalam `id_master` tersebut memiliki `trash_4 = 1`, dan kolom `deskripsi` pada SO Lunas memuat log `[REJECT SO LUNAS ...]`.

### 6.2 Matriks Jurnal Akuntansi Riil Modul Penjualan (Tabel `jurnal`)
```sql
SELECT transaksi_id, transaksi_no, jenis, cabang_id, rekening, debet, kredit
FROM jurnal
WHERE transaksi_id = '{ID_TRANSAKSI}' AND (debet > 0 OR kredit > 0);
```

| Transaksi & Kode | Rekening COA | Nama Akun COA | Posisi | Nilai Standar Uji (Rp) | Makna Bisnis Finansial |
| :--- | :---: | :--- | :---: | ---: | :--- |
| **Delivery Note / Shipment Kredit (`5822spd` / `582spd`)** | `1010020010`<br>`5010`<br>`4010`<br>`2030060`<br>`1010030030` | Piutang Dagang Reguler<br>Harga Pokok Penjualan (HPP)<br>Penjualan Lokal<br>PPN Keluaran<br>Persediaan Produk FG | **DEBET**<br>**DEBET**<br>**KREDIT**<br>**KREDIT**<br>**KREDIT** | 44.400.000,00<br>30.000.000,00<br>40.000.000,00<br>4.400.000,00<br>30.000.000,00 | Timbul tagihan piutang konsumen<br>Beban pokok barang terjual diakui<br>Pendapatan bersih penjualan (DPP)<br>Kewajiban PPN Keluaran 11%<br>Aset stok fisik keluar dari gudang |
| **Auto-Geser HPP Riil Pusat (`5822spd` Cabang `-1`)** | `5030`<br>`8020` | HPP Produk Riil (Pusat)<br>Persediaan Produk Riil (Pusat) | **DEBET**<br>**KREDIT** | 30.000.000,00<br>30.000.000,00 | Sinkronisasi beban HPP riil di pusat<br>Pengurangan persediaan riil pusat |
| **Shipment Lunas Uang Muka / POS (`5822spd` / `5823spd`)** | `2010050`<br>`5010`<br>`4010`<br>`2030060`<br>`1010030030` | Hutang ke Konsumen (UM/Deposit)<br>Harga Pokok Penjualan (HPP)<br>Penjualan Lokal<br>PPN Keluaran<br>Persediaan Produk FG | **DEBET**<br>**DEBET**<br>**KREDIT**<br>**KREDIT**<br>**KREDIT** | 44.400.000,00<br>30.000.000,00<br>40.000.000,00<br>4.400.000,00<br>30.000.000,00 | Saldo Uang Muka konsumen diserap lunas<br>Beban HPP diakui<br>Pendapatan penjualan sah diakui<br>PPN Keluaran diakui<br>Stok fisik keluar |
| **Retur Penjualan Sah (`982` / `9822`)** | `4020`<br>`2030060`<br>`1010030030`<br>`2010050`<br>`5010` | Retur Penjualan<br>PPN Keluaran (Koreksi)<br>Persediaan Produk FG<br>Hutang ke Konsumen (Credit Note)<br>Harga Pokok Penjualan (HPP) | **DEBET**<br>**DEBET**<br>**DEBET**<br>**KREDIT**<br>**KREDIT** | 4.000.000,00<br>440.000,00<br>3.000.000,00<br>4.440.000,00<br>3.000.000,00 | Kontra-akun pendapatan atas retur<br>Koreksi pengurangan PPN Keluaran<br>Barang fisik masuk kembali ke gudang<br>Timbul Credit Note hak konsumen<br>Pembalikan beban HPP |
| **Penyerahan Produk Hadiah (`66771`)** | `2010090030`<br>`1010030030`<br>`7010010` | Hutang Hadiah Produk<br>Persediaan Produk FG<br>Laba/Rugi Selisih FIFO | **DEBET**<br>**KREDIT**<br>**D/K** | 1.500.000,00<br>1.500.000,00<br>0,00 | Realisasi kewajiban hadiah konsumen<br>Pengeluaran stok barang hadiah<br>Penampung selisih lapisan FIFO |
| **Penyerahan Rebate Barang (`66773`)** | `6010`<br>`2030060`<br>`1010030030` | Potongan Penjualan / Biaya<br>PPN Keluaran<br>Persediaan Produk FG | **DEBET**<br>**KREDIT**<br>**KREDIT** | 1.110.000,00<br>110.000,00<br>1.000.000,00 | Beban potongan/rebate penjualan<br>PPN atas penyerahan barang rebate<br>Pengeluaran fisik stok dari gudang |

### 6.3 Audit Pengalihan Uang Muka ke Deposit Konsumen Saat Reject SO Lunas (Opsi 1)
```sql
-- 1. Pastikan payment source pada SO yang di-reject telah dinetralisasi (terbayar = 0, dihapus = 1)
SELECT id, transaksi_id, label, tagihan, terbayar, returned, sisa, dihapus, cancel_name, payment_source_keterangan
FROM transaksi_payment_source
WHERE transaksi_id = '{ID_SO_REJECTED}';

-- 2. Pastikan dana berpindah utuh ke tabel saldo deposit bebas konsumen
SELECT id, extern_id, extern_nama, label, tagihan, sisa, note, dtime
FROM transaksi_uang_muka_source
WHERE extern_id = '{ID_CUSTOMER}' AND label = 'uang muka konsumen'
ORDER BY id DESC;
```

### 6.4 Audit Penutupan Sisa SO / Fulfillment (`1982g` / `19822g`)
```sql
SELECT id, transaksi_id, produk_id, produk_nama, produk_ord_jml, valid_qty, packed_jml, sent_jml, req_cancel_jml, cancel_jml
FROM transaksi_data
WHERE transaksi_id = '{ID_SO}';
```
*Pastikan setelah `1982g`/`19822g` disetujui, kolom `cancel_jml` bertambah sebesar kuantitas yang ditutup dan `valid_qty` (sisa outstanding) bernilai **`0`**.*

### 6.5 Audit Mutasi Stok Locker & Nomor Seri Produk
```sql
-- Periksa mutasi state active, hold, dan sold
SELECT id, transaksi_id, produk_id, nama, state, jumlah, gudang_id
FROM stock_locker
WHERE produk_id = '42' AND cabang_id = '1';

-- Periksa status penguncian nomor seri fisik
SELECT id, produk_id, produk_serial_number, jumlah, gudang_id
FROM produk_serial_number_locker
WHERE produk_serial_number = '{NOMOR_SERI_AC}';
```

---

## 📋 7. LEMBAR KERJA PENGUJIAN UAT LENGKAP (EXECUTION LOG SHEET)

| No. Test Case | Modul & Kode | Skenario Pengujian | Hasil Yang Diharapkan | Status (Pass/Fail) | Catatan Tester |
| :---: | :---: | :--- | :--- | :---: | :--- |
| **TC-5822-01** | `5822` | Happy Path Pre-SO ➔ SO ➔ Pack ➔ Ship (`5822spd`) | Stok berkurang, Serial Terkunci, Jurnal Piutang/HPP/PPN Balance Rp 0 | `[  ]` PASS `[  ]` FAIL | |
| **TC-5822-02** | `5822` | Pencegatan Simpan Pre-SO Kosong / Tanpa Customer | Dicegat validator; database bersih tanpa record sampah | `[  ]` PASS `[  ]` FAIL | |
| **TC-5822-03** | `5822` | Price Protection Quarantine (Harga Jual < HPP) | Transaksi dikarantina; wajib otorisasi SPV via Profit Cockpit | `[  ]` PASS `[  ]` FAIL | |
| **TC-5822-04** | `5822` | Live-Edit Harga/Diskon oleh SPV di Step 2 | Nilai DPP, PPN 11%, dan Grand Total terhitung ulang presisi | `[  ]` PASS `[  ]` FAIL | |
| **TC-5822-05** | `5822` | Gembok Pre-Packing Saat Uang Muka Belum Masuk | Step 3 terkunci dengan alert *"Uang muka belum diterima"* | `[  ]` PASS `[  ]` FAIL | |
| **TC-5822-06** | `5822` | Mobile Scanner Gudang Bebas iframe & Sesi Habis | Redirect `location.href` lancar & halaman HTML Sesi Habis tampil | `[  ]` PASS `[  ]` FAIL | |
| **TC-5822-07** | `5822` | Scan Barcode Serial Unit (Valid vs Serial Asing) | Serial sah pindah ke In-Transit; serial asing diblokir instan | `[  ]` PASS `[  ]` FAIL | |
| **TC-5822-08** | `5822` | Pengiriman Parsial (Kirim 6 dari 10 Unit Pesanan) | 6 unit terkirim (`5822spd`), 4 unit sisa aktif di Backorder | `[  ]` PASS `[  ]` FAIL | |
| **TC-582-01** | `582` | Siklus Penjualan Reguler / Salesman Mode | Alur `582spo` s/d `582spd` & pemotongan kredit limit sukses | `[  ]` PASS `[  ]` FAIL | |
| **TC-5823-01** | `5823` | Siklus Penjualan POS Tunai Cepat (Auto Step 2) | Entry POS langsung ke `5823so`, bayar kasir, kirim & serap UM | `[  ]` PASS `[  ]` FAIL | |
| **TC-REV-01** | `doRevert` | **Reject 1 Step** dari Pre-Packing (`5822pkd` ➔ `5822so`) | Status kembali ke `5822so`, serial lepas, Uang Muka tetap di SO | `[  ]` PASS `[  ]` FAIL | |
| **TC-REV-02** | `doRevert` | **Reject 1 Step** dari SO (`5822so` ➔ `5822spo`) | Status kembali ke draf `5822spo` untuk revisi item/kuantitas | `[  ]` PASS `[  ]` FAIL | |
| **TC-REV-03** | `doRevert` | **Blokir Reject 1 Step** Jika Sudah Ada `spd` / Pajak `110` | Sistem menolak keras revert karena sudah kirim / terbit faktur pajak | `[  ]` PASS `[  ]` FAIL | |
| **TC-REV-04** | `doRevertAll`| **Reject All Steps** pada SO Belum Lunas & Belum Kirim | Seluruh step batal (`trash_4=1`), stok hold & kredit limit pulih | `[  ]` PASS `[  ]` FAIL | |
| **TC-REV-05** | `doRevertAll`| **Reject All Steps SO Lunas — Opsi 1 (Deposit Konsumen)** | Uang muka pindah ke `transaksi_uang_muka_source` & SO batal (`trash_4=1`) | `[  ]` PASS `[  ]` FAIL | |
| **TC-REV-06** | `doRevertAll`| **Reject All Steps SO Lunas — Opsi 2 (Void Kas Finance)** | Ditolak saat kas aktif; sukses reject setelah kas di-void & klik Refresh | `[  ]` PASS `[  ]` FAIL | |
| **TC-REV-07** | `doRevertAll`| **Blokir Reject SO Lunas** Tanpa Alasan $\ge 5$ Kar / Ceklist| Dicegat frontend & backend (`mati_disini`) bila alasan/ceklist kurang | `[  ]` PASS `[  ]` FAIL | |
| **TC-REV-08** | `doRevertAll`| **Kunci Tombol Reject All Steps** Saat `spd` Masih Aktif | Tombol Reject All Steps dikunci selama `countSpdActive > 0` | `[  ]` PASS `[  ]` FAIL | |
| **TC-1982-01** | `1982`/`19822`| **Fulfillment / Close Sisa SO** (`1982` ➔ `1982g`) | Sisa 4 unit ditutup, `outstanding = 0`, `PaymentSourceFullfill` sinkron | `[  ]` PASS `[  ]` FAIL | |
| **TC-1982-02** | `1982`/`19822`| **Blokir Fulfillment** Tanpa Alasan / Deskripsi Kosong | Dicegat `descriptionValidate` karena alasan batal belum diisi | `[  ]` PASS `[  ]` FAIL | |
| **TC-1982-03** | `1982` | **Reject atas Request Fulfillment (`1982rj`)** | Request ditutup batal, `valid_qty` 4 unit kembali aktif di SO induk | `[  ]` PASS `[  ]` FAIL | |
| **TC-982-01** | `982` | Retur Penjualan Reguler (`982r` ➔ `982g` ➔ `982`) | Stok kembali ke gudang, jurnal balik & Credit Note `2010050040` terbit | `[  ]` PASS `[  ]` FAIL | |
| **TC-9822-01** | `9822` | Retur Penjualan Scan Barcode (`9822r` ➔ `9822g` ➔ `9822`) | Validasi scan serial retur sukses & blokir nota yang sudah diretur | `[  ]` PASS `[  ]` FAIL | |
| **TC-584-01** | `584` | Siklus Penjualan Jasa (`584spo` ➔ `584so` ➔ `584`) | Invoice jasa terbit (mendukung `allowJoin`) tanpa mutasi stok fisik | `[  ]` PASS `[  ]` FAIL | |
| **TC-382-01** | `382` | Siklus Penjualan Ekspor Valas (`382spo` s/d `382`) | Transaksi USD & ekuivalen IDR tercatat akurat dengan PPN 0% | `[  ]` PASS `[  ]` FAIL | |
| **TC-66771-01**| `66771` | Penyerahan Produk Hadiah (`66771r` ➔ `66771`) | Stok hadiah keluar via scan serial, Debet Hutang Hadiah `2010090030` | `[  ]` PASS `[  ]` FAIL | |
| **TC-66773-01**| `66773` | Penyerahan Rebate Penjualan Barang (`66773r` ➔ `66773`) | Stok barang keluar via scan serial, Debet Potongan Penjualan `6010` | `[  ]` PASS `[  ]` FAIL | |
| **TC-BO-01** | `Backorder` | Multi-Enterprise Backorder Cockpit & Alokasi Stok | KPI akurat, `Net = Fisik - Booking`, dan eksekusi tercatat di `transaksi_log` | `[  ]` PASS `[  ]` FAIL | |
| **TC-SEC-01** | `5822` | Penetrasi Direct POST, Loncat Step, & Idle Lock 5 Menit | Serangan bypass/loncat step diblokir; retake lock aktif saat idle $\ge 5$m | `[  ]` PASS `[  ]` FAIL | |
| **TC-FIN-01** | `5822` | Pemotongan Deposit, Poin Loyalitas & Piutang Marketplace | Saldo `2010050` terpotong proporsional dan sisa masuk ke Piutang | `[  ]` PASS `[  ]` FAIL | |


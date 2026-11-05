# 📑 LAPORAN UAT PIPELINE BERJENJANG
## Fitur: Dinamisasi SPK Berbasis Komposisi Nyata (*Data-Driven Dynamic Scope*)
**Modul:** `master_project`  
**Host Server:** `https://192.168.5.14/everest_19okt/`  
**Database:** `192.168.5.14` (`run_everest_modul`)  
**Akun UAT:** `sysproject` (Role: `o_project_spv` / Operator Project) & `sys_`  
**Waktu Eksekusi:** 22 Oktober 2026

---

## 🎯 1. Ringkasan Eksekutif UAT
Pengujian User Acceptance Testing (UAT) telah selesai dilakukan secara menyeluruh menggunakan metode **Pipeline Berjenjang 2 Layer (Layer 1: Logic & HTTP API Integration Test; Layer 2: Visual & Functional Browser Walkthrough)**. Seluruh skenario pengujian yang mencakup kondisi **YES**, **NO**, dan **ELSE** dinyatakan **100% LULUS (PASSED)** tanpa error fatal.

---

## 📊 2. Matriks Pengujian Pipeline Berjenjang

| Kategori | ID Uji | Skenario Pengujian | Target Komposisi Data | Ekspektasi Sistem | Hasil Aktual | Status |
| :---: | :---: | :--- | :--- | :--- | :--- | :---: |
| **YES** | `UAT-YES-01` | SPK Murni Jasa / Biaya (Contoh: Task 8 / SPK 002/SPK-INT) | Hanya ada item Biaya di RAB Sub; Tidak ada barang fisik. | `non_material: 1`<br>`non_biaya: 0`<br>Tabel bahan sembunyi, alert non-material aktif, tabel biaya tampil. | Sesuai. Alert Non-Material aktif, tabel fisik sembunyi, tabel Equivalen Biaya menampilkan 4 item biaya (Rp 3.850.000). | **PASSED** ✅ |
| **YES** | `UAT-YES-02` | SPK Perlengkapan / Supplies (Contoh: Task 45 / SPK 043/SPK-INT) | Ada item Supplies di RAB Sub / Tambahan (Kabel, Pipa, Steker, Fleksible). | `non_material: 0`<br>`non_biaya: 0`<br>Supplies masuk ke `#list_bahan_baku`, caption dinamis berubah. | Sesuai. Item supplies masuk ke `#list_bahan_baku`, caption berubah menjadi `DAFTAR PERLENGKAPAN / SUPPLIES`. | **PASSED** ✅ |
| **YES** | `UAT-YES-03` | SPK Murni Material Unit (Contoh: Task 1059 / SPK 906/SPK-INT) | Hanya ada Unit AC di komposisi fisik; Tanpa rincian biaya. | `non_material: 0`<br>`non_biaya: 1`<br>Tabel material tampil, tabel biaya sembunyi. | Sesuai. `list_bahan: 1`, `list_biaya: 0`, `non_material: 0`, `non_biaya: 1`. | **PASSED** ✅ |
| **NO** | `UAT-NO-01` | Blokir Penyelesaian Fisik Tanpa Distribusi Stok Gudang | SPK Fisik (`non_material: 0`), stok belum didistribusikan dari gudang (`sdh_diambil: 0`). | Tombol disabled / input disabled `belum distribusi*`, `stockCheck()` memblokir penyelesaian. | Sesuai. Form input disabled dengan teks merah `belum distribusi`, sistem menampilkan peringatan stok gudang. | **PASSED** ✅ |
| **NO** | `UAT-NO-02` | Validasi Kewajiban Dokumentasi / Foto Pelaksana | SPK dengan pekerjaan fisik tanpa upload bukti foto pekerjaan. | Tombol Selesai disabled `Silahkan Pilih/Upload Photo Dulu`. | Sesuai. Tombol pengerjaan terkunci disabled hingga ada foto di Dropzone. | **PASSED** ✅ |
| **ELSE** | `UAT-ELSE-01` | Fallback Graceful SPK Tanpa Item (Zero Items / Task Baru) | Komposisi kosong ($KomposisiWorkorder = [] dan $KomposisiWorkorderBiaya = []). | Tidak terjadi PHP Notice / Broken Layout. Graceful default ke `non_material: 1`, `non_biaya: 1`. | Sesuai. HTTP 200, output JSON valid, fallback aman tanpa PHP fatal error. | **PASSED** ✅ |
| **ELSE** | `UAT-ELSE-02` | Proteksi Duplikasi Pengajuan Kasir (Hanya Supplies Lapangan) | SPK Tambahan hanya memuat rincian supplies (material bantu dari gudang). | Pagu kasir/reimburse yang diteruskan ke Finance = Rp 0. | Sesuai. Tidak ada voucher kasbon diterbitkan karena supplies disalurkan via mutasi stok gudang. | **PASSED** ✅ |

---

## 📸 3. Bukti Visual UAT (Browser Walkthrough)

### 🔹 Bukti 1: Skenario YES - SPK Non-Material (Murni Biaya / Jasa)
- **Data Uji:** Task ID 8 / Produk ID 21 (SPK: `002/SPK-INT/988/002/III/2024`)
- **Tampilan Modal:**
  - Header: `TAHAP PENGERJAAN JASA & BIAYA`
  - Alert Biru: `SPK NON-MATERIAL (MURNI BIAYA / JASA): SPK ini tidak memerlukan pengambilan barang di gudang. Silakan unggah foto bukti kerja dan klik tombol Selesai Dikerjakan.`
  - Tabel Fisik `#list_bahan_baku`: Tersembunyi rapi.
  - Tabel Biaya: Menampilkan 4 rincian biaya: Freon, Jasa Split Duct 6PK, Cassette 6PK, dan Drain Pump (Total: Rp 3.850.000).

![Modal SPK Murni Biaya](file:///C:/Users/thomas/.gemini/antigravity-ide/brain/03322825-382f-4eed-8a51-e2abdeebf062/spk_murni_biaya_modal_1792687931295.png)

---

### 🔹 Bukti 2: Skenario YES & NO - SPK Perlengkapan (Supplies) & Validasi Stok
- **Data Uji:** Task ID 45 / Produk ID 22 (SPK: `043/SPK-INT/979/043/IX/2024`)
- **Tampilan Modal:**
  - Header: `TAHAP PEMASANGAN MATERIAL & PENGGUNAAN BAHAN`
  - Caption Tabel Dinamis: `DAFTAR PERLENGKAPAN / SUPPLIES`
  - Rincian Barang Fisik: `KABEL NYM 3X1,5`, `PIPA HODA *2330`, `STEKER`, `FLEKSIBILE`
  - Proteksi Validasi Negatif (Kondisi NO): Kolom aksi berstatus `belum distribusi` warna merah (input disabled), dan tombol selesai terkunci `Silahkan Pilih/Upload Photo Dulu`.

![Modal SPK Supplies](file:///C:/Users/thomas/.gemini/antigravity-ide/brain/03322825-382f-4eed-8a51-e2abdeebf062/spk_supplies_modal_1792687948442.png)

---

## 🔍 4. Rekaman Interaksi Browser
Sesi interaksi browser otomatis tersimpan pada berkas rekaman:
- [uat_complete_modals_1792687920734.webp](file:///C:/Users/thomas/.gemini/antigravity-ide/brain/03322825-382f-4eed-8a51-e2abdeebf062/uat_complete_modals_1792687920734.webp)

---

## 🏁 5. Kesimpulan
Implementasi **Dinamisasi Berbasis Komposisi Nyata (*Data-Driven Dynamic Scope*)** pada `MasterData.php` telah terbukti:
1. Menghilangkan hardcode kaku `SPK-TMB = non_material 1`.
2. Menempatkan item `supplies` pada habitat aslinya (barang fisik ber-locker stock), bukan dipaksa menjadi biaya COA.
3. Menjaga integritas validasi keamanan (stok gudang & foto pekerjaan tetap wajib jika ada unsur fisik).
4. Menyesuaikan teks antarmuka (caption & alert) secara otomatis dan dinamis berdasarkan isi aktual pekerjaan.

# BLUEPRINT & DOKUMENTASI TEKNIS: LAPORAN AKUNTANSI REKENING COA 1010020030
**Akun:** 1010020030 &mdash; "Klaim Kepada Supplier" / "Piutang Pembelian / Klaim Supplier"  
**Modul Utama:** `kompensasiharga` (Trx 3333 / 3344), `Ledger`, `penerimaan` (GRN 467)  
**Dokumen Referensi:** ISO 9001:2015 (Clause 7.5 & 8.4), ISO/IEC 27001 (A.12.4), IFRS 15 / PSAK 72  
**Status File Implementasi HTML:** [`laporan_rekening_1010020030.html`](file:///z:/everest_2nov/laporan_rekening_1010020030.html) (Live & Interaktif)  
**Tanggal Penyusunan:** 03 November 2026  

---

## 1. Latar Belakang & Karakteristik Akun

Rekening COA `1010020030` merupakan akun kelompok **Aset Lancar** (*Current Assets &mdash; Piutang Lain-Lain / Piutang Pembelian*). Akun ini berfungsi menampung hak piutang perusahaan kepada supplier/vendor yang timbul dari:
1. **Hak Diskon Pembelian (Diskon 1 s/d 8):** Promo seasonal, diskon kuantitas, atau kompensasi harga dari PO/GRN.
2. **Kompensasi Free Produk (Diskon 7):** Pengakuan nilai moneter dari barang bonus/hadiah yang dijanjikan supplier namun belum diterima/dipotong.
3. **Realisasi Pencairan:** Pelunasan piutang klaim melalui transfer/giro bank atau pemotongan langsung invoice hutang dagang (*set-off Accounts Payable*).

* **Posisi Normal Akun:** **Debet** (Penambahan hak piutang = Debet; Realisasi/pencairan klaim = Kredit).

---

## 2. Peta Arsitektur Basis Data di Everest ERP

Di dalam sistem Everest ERP, pencatatan akun `1010020030` dikelola melalui struktur database berikut:

### 2.1. Tabel-Tabel Terkait
| Nama Tabel | Peran / Fungsi | Kolom Kunci |
| :--- | :--- | :--- |
| `_rek_pembantu_subpiutangsuppliertrans_cache` | Tabel snapshot/cache saldo aktif berjalan periode `forever` | `rekening='1010020030'`, `periode='forever'`, `extern_id` (GRN ID), `extern2_id` (Slot Diskon), `extern3_id` (Supplier ID), `debet` (Sisa Saldo) |
| `__rek_pembantu_subpiutangsuppliertrans__1010020030` | Tabel partisi fisik mutasi jurnal transaksi akun 1010020030 | `transaksi_id` (ID Trx Klaim/GRN), `extern_id` (GRN ID), `extern2_id` (Slot Diskon), `extern3_id` (Supplier ID), `debet` (Klaim Masuk), `kredit` (Klaim Terpakai), `dtime`, `keterangan` |
| `suppliers` | Tabel master vendor / mitra supplier | `id`, `nama`, `kode`, `alamat` |
| `transaksi` | Tabel induk dokumen transaksi | `id`, `nomer`, `jenis` (467=GRN, 3333=Klaim), `suppliers_id`, `oleh_nama`, `dtime` |
| `stock_locker_diskon` | Layer operasional pengunci stok diskon | `transaksi_id`, `produk_id` (slot diskon), `nilai` (saldo locker aktif), `state='active'` |

### 2.2. Model & Helper Bisnis Terkait
* **Model Komponen:** [`ComRekeningPembantuPiutangSupplierDetailTransMain`](file:///z:/everest_2nov/application/models/Coms/ComRekeningPembantuPiutangSupplierDetailTransMain.php)
* **Helper Pengecekan:** [`he_cek_diskon_supplier_transaksi_helper.php`](file:///z:/everest_2nov/application/helpers/Pairs/he_cek_diskon_supplier_transaksi_helper.php) (`cekDiskonSupplierTransaksi`)
* **Konfigurasi Gateway:** [`coTransaksiCore.php`](file:///z:/everest_2nov/application/modules/kompensasiharga/config/coTransaksiCore.php) (Mapping gateway `nilai_piutang` / `-sub_diskon_supplier_nilai`)
* **Registrasi Subledger:** [`heAccounting.php`](file:///z:/everest_2nov/application/config/heAccounting.php#L1929) (`RekeningPembantuPiutangSupplierMain`)

---

## 3. Evaluasi & Kritik Arsitektur Basis Data Eksisting

Berdasarkan tinjauan teori basis data akuntansi (*Double-Entry Bookkeeping*):
1. **Dynamic Table Partitioning Anti-Pattern:** Penamaan tabel menyematkan nomor rekening fisik (`__rek_...__1010020030`) memutus kemampuan relasi SQL terpadu (*native cross-account queries*).
2. **Kolom Polimorfik Tanpa Semantik:** Penggunaan kolom `extern_id`, `extern2_id`, `extern3_id` menghilangkan integritas relasi *Foreign Key* dan memaksa PHP melakukan mapping manual yang rentan salah konteks.
3. **Desinkronisasi Antara Cache & Mutasi:** Saldo disimpan pada tabel cache terpisah (`_cache`) bukan hasil agregasi murni (`SUM(debet) - SUM(kredit)`), sehingga memicu perlunya audit komparasi rutin.
4. **Coupling Operasional vs Finansial:** Penggabungan pembacaan antara `stock_locker_diskon` (status fisik penguncian) dengan saldo buku pembantu sub-piutang.

---

## 4. Spesifikasi Laporan Standar ISO & Best Practice

Laporan yang telah diimplementasikan pada file [`laporan_rekening_1010020030.html`](file:///z:/everest_2nov/laporan_rekening_1010020030.html) mencakup 4 pilar audit:

### 4.1. Pilar 1: Ringkasan Eksekutif & Posisi Akun
* **Total Klaim Masuk (Debet):** Akumulasi seluruh hak klaim yang diakui dari nota GRN pembelian.
* **Total Pemakaian (Kredit):** Akumulasi realisasi klaim yang telah dicairkan (transaksi 3333).
* **Saldo Aktif Berjalan (Net):** Nilai sisa hak piutang aktif yang belum ditagihkan/dicairkan.
* **Claim Realization Ratio (%):** Persentase penagihan piutang klaim terhadap hak yang timbul.

### 4.2. Pilar 2: Distribusi Umur Piutang Klaim (*Aging Schedule*)
* **0 – 30 Hari:** Status Lancar (*Current*).
* **31 – 60 Hari:** Perhatian Khusus (*Early Warning*).
* **61 – 90 Hari:** Jatuh Tempo (*Due*).
* **> 90 Hari:** Risiko Kedaluwarsa / Macet (*Overdue / Impairment Risk*).

### 4.3. Pilar 3: Rekapitulasi Sub-Ledger per Mitra Supplier
* Menampilkan seluruh supplier (aktif dan lunas/nihil).
* **Collapsible Row untuk Saldo Rp 0:** Seluruh supplier bersaldo Rp 0 diringkas dalam 1 baris yang jika diklik akan membuka rincian lengkap 10 vendor.
* **Filter Tabs:** *Semua Vendor*, *Hanya Saldo Aktif*, *Hanya Saldo Rp 0*, dan tombol *Cetak*.

### 4.4. Pilar 4: Jejak Audit Mutasi Rinci per Supplier (*Drill-Down Accordion*)
Setiap baris rekening pembantu dapat diklik untuk membuka sub-tabel mutasi dengan kolom:
1. No. Urut Mutasi
2. Tanggal & Waktu Posting
3. No. Dokumen GRN Sumber
4. No. Referensi PO
5. Alokasi / Uraian Diskon (Diskon 1, 2, Free Produk, dll.)
6. Masuk (Debet)
7. Keluar (Kredit)
8. Saldo Berjalan (*Running Balance*)
9. No. Dokumen Realisasi Klaim (TRX 3333 / Tanda Pending)
10. Petugas Pemroses (Oleh)
11. Keterangan Lengkap Audit Trail

---

## 5. Kumpulan Query SQL Produksi (Production Ready)

Query berikut dirancang untuk membaca data riil dari skema MariaDB Everest secara efisien:

### 5.1. Query Rekapitulasi Lengkap Semua Supplier (Termasuk Saldo 0 & Collapsible)
```sql
SELECT 
    s.id AS supplier_id,
    s.nama AS supplier_nama,
    COUNT(DISTINCT c.extern_id) AS jumlah_nota_grn,
    IFNULL(SUM(c.debet), 0) AS sisa_saldo_aktif,
    IFNULL(k.total_terpakai, 0) AS total_pemakaian_klaim,
    (IFNULL(SUM(c.debet), 0) + IFNULL(k.total_terpakai, 0)) AS total_hak_klaim_masuk,
    CASE 
        WHEN IFNULL(SUM(c.debet), 0) > 0 THEN 1 
        ELSE 0 
    END AS is_saldo_aktif
FROM suppliers s
LEFT JOIN _rek_pembantu_subpiutangsuppliertrans_cache c 
    ON c.extern3_id = s.id 
   AND c.rekening = '1010020030' 
   AND c.periode = 'forever'
LEFT JOIN (
    SELECT 
        extern3_id, 
        SUM(kredit) AS total_terpakai
    FROM __rek_pembantu_subpiutangsuppliertrans__1010020030
    WHERE kredit > 0
    GROUP BY extern3_id
) k ON k.extern3_id = s.id
GROUP BY s.id, s.nama
ORDER BY is_saldo_aktif DESC, sisa_saldo_aktif DESC, s.nama ASC;
```

### 5.2. Query Kartu Mutasi Rinci per Supplier Tertentu (Drill-Down Audit Trail)
Ganti `:supplier_id` dengan ID supplier yang dipilih:
```sql
SELECT 
    m.id AS mutasi_id,
    m.dtime AS tgl_posting,
    m.extern_id AS grn_id,
    m.extern_nama AS grn_nomer,
    t_grn.reference_id AS po_id,
    t_grn.indexing_po AS po_nomer,
    m.extern2_id AS slot_diskon_id,
    m.extern2_nama AS jenis_diskon,
    m.debet AS nominal_masuk,
    m.kredit AS nominal_keluar,
    m.transaksi_id AS klaim_trx_id,
    t_klaim.nomer AS klaim_trx_nomer,
    IFNULL(t_klaim.oleh_nama, m.oleh_nama) AS petugas,
    m.keterangan AS catatan_audit
FROM __rek_pembantu_subpiutangsuppliertrans__1010020030 m
LEFT JOIN transaksi t_grn ON t_grn.id = m.extern_id
LEFT JOIN transaksi t_klaim ON t_klaim.id = m.transaksi_id AND m.transaksi_jenis = '3333'
WHERE m.extern3_id = :supplier_id
ORDER BY m.dtime ASC, m.id ASC;
```

### 5.3. Query Aging Schedule Akun 1010020030
```sql
SELECT 
    SUM(CASE WHEN DATEDIFF(NOW(), t.dtime) <= 30 THEN c.debet ELSE 0 END) AS aging_0_30,
    SUM(CASE WHEN DATEDIFF(NOW(), t.dtime) BETWEEN 31 AND 60 THEN c.debet ELSE 0 END) AS aging_31_60,
    SUM(CASE WHEN DATEDIFF(NOW(), t.dtime) BETWEEN 61 AND 90 THEN c.debet ELSE 0 END) AS aging_61_90,
    SUM(CASE WHEN DATEDIFF(NOW(), t.dtime) > 90 THEN c.debet ELSE 0 END) AS aging_over_90,
    SUM(c.debet) AS total_saldo_aktif
FROM _rek_pembantu_subpiutangsuppliertrans_cache c
INNER JOIN transaksi t ON t.id = c.extern_id
WHERE c.rekening = '1010020030' 
  AND c.periode = 'forever' 
  AND c.debet > 0;
```

---

## 6. Struktur File yang Sudah Terbentuk

1. **File Antarmuka HTML (Siap Pakai & Presentasi):**
   * Path: [`z:\everest_2nov\laporan_rekening_1010020030.html`](file:///z:/everest_2nov/laporan_rekening_1010020030.html)
   * Fitur: 
     * Responsive CSS & Print-ready A4 formatting.
     * Baris collapsible saldo 0 dengan penghitungan total otomatis.
     * Inline drill-down accordion mutasi per supplier dengan 11 kolom audit trail.
     * Filter navigasi: Semua vendor, Hanya aktif, Hanya saldo 0, dan Buka semua mutasi.
2. **File Dokumentasi & Blueprint Ini:**
   * Path: [`z:\everest_2nov\BLUEPRINT_LAPORAN_AKUNTANSI_COA_1010020030.md`](file:///z:/everest_2nov/BLUEPRINT_LAPORAN_AKUNTANSI_COA_1010020030.md)

---

## 7. Rencana Tindak Lanjut (*Next Steps* untuk Dilanjutkan Nanti)

Saat Anda siap melanjutkan pengerjaan, opsi integrasi yang dapat dipilih meliputi:
1. **Opsi A: Integrasi Modul `kompensasiharga`**
   * Menjadikan laporan ini sebagai tab menu baru di `Transaksi.php` atau controller khusus di bawah `kompensasiharga/controllers/LaporanKlaimSupplier.php`.
   * Menghubungkan data AJAX dinamis ke query di Bab 5.
2. **Opsi B: Integrasi Modul General Ledger (`Ledger.php`)**
   * Menyematkan tampilan drill-down ini pada menu `Ledger/viewBalances_l1/RekeningPembantuPiutangSupplierMain/1010020030`.
3. **Opsi C: Pembuatan Database VIEW Terstandar di MariaDB**
   * Membuat *Database View* `v_subledger_klaim_supplier_1010020030` agar modul pelaporan apapun di masa depan bisa memanggil laporan ini dengan `SELECT *` sederhana tanpa koding rumit.


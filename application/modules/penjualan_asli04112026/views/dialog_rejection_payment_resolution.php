<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// START OF COMPLETE REPEATED LOGIC
/* ----------------------------------------------------------------------------------
 * Dialog Intersepsi & Resolusi Finansial Pembatalan (Reject) Sales Order Lunas / Uang Muka
 * Standar: ISO/IEC 25010 & Clean HMVC CI3 (PHP 5.6)
 * ----------------------------------------------------------------------------------*/

$allowDeposit = isset($options['allowDepositConversion']) ? $options['allowDepositConversion'] : true;
$allowRefundKas = isset($options['allowFinanceRefund']) ? $options['allowFinanceRefund'] : true;
?>
<div id="container_reject_so_lunas" style="text-align: left; font-size: 13px; line-height: 1.5; color: #333; background: #ffffff; border: 1px solid #dcdcdc; border-radius: 8px; padding: 16px; margin: 10px 0; box-shadow: 0 4px 10px rgba(0,0,0,0.08);">
  <input type="hidden" id="hidden_opsi_reject_selected" name="opsi_reject_selected" value="" />
  <input type="hidden" id="hidden_alasan_reject" name="alasan_reject" value="" />
  <input type="hidden" id="hidden_checklist_konfirmasi" name="checklist_konfirmasi" value="0" />

  <!-- STATE 0: PEMILIHAN OPSI PENYELESAIAN -->
  <div id="panel_reject_state_0">
    <div style="background: #fffbeb; border: 1px solid #fef3c7; border-left: 5px solid #f59e0b; padding: 12px 16px; margin-bottom: 16px; border-radius: 6px;">
      <div style="display: flex; align-items: center; justify-content: space-between;">
        <h4 style="margin: 0; color: #92400e; font-weight: bold; font-size: 15px;"><i class="fa fa-exclamation-circle"></i> Sales Order nomer <?php echo $nomer_dibayar_f; ?> sudah LUNAS</h4>
        <span style="background: #d97706; color: #fff; font-size: 12px; font-weight: bold; padding: 3px 8px; border-radius: 4px;">Rp <?php echo $nilai_bayar_f; ?></span>
      </div>
      <div style="margin-top: 8px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <span style="font-size: 12px; color: #6b7280; font-weight: bold;">Status Pemenuhan:</span>
        <span style="background: #dcfce7; color: #166534; font-size: 11px; font-weight: bold; padding: 2px 8px; border-radius: 4px; border: 1px solid #bbf7d0;">🟢 LUNAS (Kas <?php echo $primaryKasNomerF; ?>)</span>
        <?php echo $logistikBadge; ?>
      </div>
      <div style="color: #78350f; font-size: 13px; margin-top: 8px;">Pesanan ini telah memiliki penerimaan pembayaran lunas. Untuk membatalkan (reject) Sales Order ini, silakan periksa status pengiriman dan pilih opsi penyelesaian:</div>
    </div>

    <?php if ($countSpdActive > 0): ?>
      <div style="background: #fef2f2; border: 1px solid #fecaca; border-left: 5px solid #dc2626; padding: 12px 16px; margin-bottom: 16px; border-radius: 6px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <h4 style="margin: 0; color: #991b1b; font-weight: bold; font-size: 14px;"><i class="fa fa-ban"></i> PERINGATAN: TERDAPAT SURAT JALAN AKTIF KE KONSUMEN</h4>
          <span class="label label-danger" style="font-size: 11px;"><?php echo $countSpdActive; ?> Dokumen Aktif</span>
        </div>
        <div style="color: #7f1d1d; font-size: 13px; margin-top: 6px; line-height: 1.4;">
          Barang fisik secara administratif masih tercatat keluar/dikirim ke konsumen. <b>Tombol Reject All Steps dikunci</b> untuk mencegah selisih stok persediaan. Harap batalkan Surat Jalan pengiriman terlebih dahulu di bagian gudang/pengiriman sebelum membatalkan pesanan.
        </div>
      </div>
    <?php elseif ($countSpdCanceled > 0): ?>
      <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-left: 5px solid #16a34a; padding: 10px 14px; margin-bottom: 14px; border-radius: 6px;">
        <div style="color: #166534; font-size: 13px; line-height: 1.4;">
          <i class="fa fa-check-circle"></i> <b>Pengiriman Telah Dibatalkan:</b> Dokumen Surat Jalan (<?php echo $countSpdCanceled; ?> pengiriman) telah dibatalkan. Fisik barang telah kembali ke stok persediaan gudang. Silakan selesaikan pengalihan dana di bawah ini.
        </div>
      </div>
    <?php endif; ?>

    <!-- Kotak Penjelasan Dampak Finansial (Impact Summary) -->
    <?php if ($isDokumenPkd): ?>
      <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-left: 5px solid #2563eb; padding: 12px 16px; margin-bottom: 16px; border-radius: 6px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <h4 style="margin: 0; color: #1e40af; font-weight: bold; font-size: 14px;"><i class="fa fa-info-circle"></i> RINGKASAN DAMPAK FINANSIAL UANG MUKA (DOKUMEN PACKING)</h4>
          <span style="background: #dbeafe; color: #1e40af; font-size: 11px; font-weight: bold; padding: 2px 8px; border-radius: 4px;">Level: Pre-Packing / Packing</span>
        </div>
        <div style="color: #1e3a8a; font-size: 13px; margin-top: 8px; line-height: 1.5;">
          <div style="margin-bottom: 4px;">• <b style="color: #1d4ed8;">Reject 1 Step :</b> Mengulang tahapan pengerjaan packing ke step sebelumnya. Saldo uang muka <b>Rp <?php echo $nilai_bayar_f; ?></b> <span style="background: #fef08a; color: #854d0e; padding: 1px 6px; border-radius: 3px; font-weight: bold;">TETAP MELEKAT PADA PESANAN</span> (tidak dipindahkan ke deposit).</div>
          <div>• <b style="color: #b91c1c;">Reject All Steps :</b> Membatalkan pesanan ini secara menyeluruh sampai SO gugur. Saldo uang muka <b>Rp <?php echo $nilai_bayar_f; ?></b> <span style="background: #bbf7d0; color: #166534; padding: 1px 6px; border-radius: 3px; font-weight: bold;">DIALIHKAN KE SALDO DEPOSIT KONSUMEN</span> atau di-void di Finance.</div>
        </div>
      </div>
    <?php else: ?>
      <div style="background: #fefce8; border: 1px solid #fef08a; border-left: 5px solid #ca8a04; padding: 12px 16px; margin-bottom: 16px; border-radius: 6px;">
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <h4 style="margin: 0; color: #854d0e; font-weight: bold; font-size: 14px;"><i class="fa fa-info-circle"></i> RINGKASAN DAMPAK FINANSIAL UANG MUKA (SALES ORDER INDUK)</h4>
          <span style="background: #fef9c3; color: #854d0e; font-size: 11px; font-weight: bold; padding: 2px 8px; border-radius: 4px;">Level: Sales Order Induk</span>
        </div>
        <div style="color: #713f12; font-size: 13px; margin-top: 8px; line-height: 1.5;">
          <div style="margin-bottom: 4px;">• <b style="color: #a16207;">Reject 1 Step :</b> Mengembalikan pesanan ke Draft (Step 1). Saldo uang muka <b>Rp <?php echo $nilai_bayar_f; ?></b> <span style="background: #bbf7d0; color: #166534; padding: 1px 6px; border-radius: 3px; font-weight: bold;">DIALIHKAN KE SALDO DEPOSIT KONSUMEN</span> atau di-void di Finance agar draft bebas dari keterikatan pembayaran.</div>
          <div>• <b style="color: #b91c1c;">Reject All Steps :</b> Membatalkan pesanan secara permanen. Saldo uang muka <b>Rp <?php echo $nilai_bayar_f; ?></b> <span style="background: #bbf7d0; color: #166534; padding: 1px 6px; border-radius: 3px; font-weight: bold;">DIALIHKAN KE SALDO DEPOSIT KONSUMEN</span> atau di-void di Finance.</div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Kartu Opsi 1 (Saldo Deposit Konsumen) -->
    <?php if ($allowDeposit): ?>
      <div id="card_reject_opsi_1" onclick="selectAndShowRejectState(1)" style="cursor: pointer; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 15px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
          <div style="display: flex; align-items: flex-start;">
            <div id="radio_circle_1" style="margin-top: 2px; margin-right: 12px; min-width: 22px; width: 22px; height: 22px; border-radius: 50%; border: 2px solid #9ca3af; display: flex; align-items: center; justify-content: center; background: #ffffff; transition: all 0.2s;">
              <div id="radio_dot_1" style="display: none; width: 10px; height: 10px; border-radius: 50%; background: #2563eb;"></div>
            </div>
            <div>
              <div style="display: flex; align-items: center; flex-wrap: wrap;">
                <h4 style="margin: 0 8px 0 0; color: #1e40af; font-weight: bold; font-size: 15px;">Opsi 1: Saldo dialihkan ke Deposit Konsumen</h4>
                <span style="background: #dcfce7; color: #166534; font-size: 11px; font-weight: bold; padding: 2px 7px; border-radius: 10px; border: 1px solid #bbf7d0; margin-right: 5px;">⚡ Proses Langsung</span>
                <span style="background: #e0f2fe; color: #075985; font-size: 11px; font-weight: bold; padding: 2px 7px; border-radius: 10px; border: 1px solid #bae6fd;">Dana Mengendap di Sistem</span>
              </div>
              <div style="color: #374151; font-size: 13px; margin-top: 6px; line-height: 1.4;">Uang muka <b>Rp <?php echo $nilai_bayar_f; ?></b> dialihkan menjadi saldo deposit atas nama konsumen <b><?php echo $custNama; ?></b>.</div>
              <div style="color: #166534; font-size: 12px; margin-top: 4px; font-weight: bold;"><i class="fa fa-arrow-circle-right"></i> Rekomendasi: Pilih Opsi 1 jika konsumen ingin ganti tipe barang atau belanja lagi nanti.</div>
              <div style="color: #6b7280; font-size: 12px; margin-top: 2px;"><i class="fa fa-info-circle"></i> Sales Order langsung dibatalkan secara menyeluruh setelah mengisi konfirmasi dan alasan tertulis.</div>
            </div>
          </div>
          <div style="margin-left: 15px; white-space: nowrap;">
            <span class="btn btn-primary" style="font-weight: bold; padding: 8px 14px; font-size: 13px; border-radius: 6px;">Pilih & Lanjutkan &rarr;</span>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Kartu Opsi 2 (Batalkan / Refund Kas di Finance) -->
    <?php if ($allowRefundKas): ?>
      <div id="card_reject_opsi_2" onclick="selectAndShowRejectState(2)" style="cursor: pointer; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 15px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start;">
          <div style="display: flex; align-items: flex-start;">
            <div id="radio_circle_2" style="margin-top: 2px; margin-right: 12px; min-width: 22px; width: 22px; height: 22px; border-radius: 50%; border: 2px solid #9ca3af; display: flex; align-items: center; justify-content: center; background: #ffffff; transition: all 0.2s;">
              <div id="radio_dot_2" style="display: none; width: 10px; height: 10px; border-radius: 50%; background: #d97706;"></div>
            </div>
            <div>
              <div style="display: flex; align-items: center; flex-wrap: wrap;">
                <h4 style="margin: 0 8px 0 0; color: #92400e; font-weight: bold; font-size: 15px;">Opsi 2: Batalkan / Refund Bukti Kas di Finance</h4>
                <span style="background: #fef3c7; color: #92400e; font-size: 11px; font-weight: bold; padding: 2px 7px; border-radius: 10px; border: 1px solid #fde68a; margin-right: 5px;">⏳ Perlu Tindakan Finance</span>
                <span style="background: #fee2e2; color: #991b1b; font-size: 11px; font-weight: bold; padding: 2px 7px; border-radius: 10px; border: 1px solid #fecaca;">Bukti Kas: <?php echo $primaryKasNomerF; ?></span>
              </div>
              <div style="color: #374151; font-size: 13px; margin-top: 6px; line-height: 1.4;">Lihat rincian bukti kas pelunas dan minta bagian <b>Finance / Kasir</b> melakukan pembatalan (void/refund).</div>
              <div style="color: #92400e; font-size: 12px; margin-top: 4px; font-weight: bold;"><i class="fa fa-arrow-circle-right"></i> Rekomendasi: Pilih Opsi 2 jika konsumen membatalkan pembelian dan meminta uangnya kembali via Finance.</div>
              <div style="color: #92400e; font-size: 12px; margin-top: 2px; font-weight: 600;"><i class="fa fa-book"></i> Jurnal dibalik sehingga seolah transaksi tidak pernah dilakukan (tidak timbul Deposit).</div>
              <div style="color: #6b7280; font-size: 12px; margin-top: 4px;"><i class="fa fa-info-circle"></i> Setelah bukti kas dibatalkan oleh Finance, klik tombol refresh untuk memproses pembatalan SO.</div>
            </div>
          </div>
          <div style="margin-left: 15px; white-space: nowrap;">
            <span class="btn btn-warning" style="font-weight: bold; padding: 8px 14px; font-size: 13px; border-radius: 6px; color: #fff; background-color: #d97706; border-color: #b45309;">Lihat Rincian Kas &rarr;</span>
          </div>
        </div>
      </div>
    <?php endif; ?>

    <!-- Kartu Opsi 3 (Batal Sebagian Barang / Revisi Draf) -->
    <div id="card_reject_opsi_3" onclick="selectAndShowRejectState(3)" style="cursor: pointer; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 15px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: all 0.2s;">
      <div style="display: flex; justify-content: space-between; align-items: flex-start;">
        <div style="display: flex; align-items: flex-start;">
          <div id="radio_circle_3" style="margin-top: 2px; margin-right: 12px; min-width: 22px; width: 22px; height: 22px; border-radius: 50%; border: 2px solid #9ca3af; display: flex; align-items: center; justify-content: center; background: #ffffff; transition: all 0.2s;">
            <div id="radio_dot_3" style="display: none; width: 10px; height: 10px; border-radius: 50%; background: #0284c7;"></div>
          </div>
          <div>
            <div style="display: flex; align-items: center; flex-wrap: wrap;">
              <h4 style="margin: 0 8px 0 0; color: #0369a1; font-weight: bold; font-size: 15px;">Opsi 3: Batal Sebagian Barang / Revisi Draf (Tetap Belanja)</h4>
              <span style="background: #e0f2fe; color: #0369a1; font-size: 11px; font-weight: bold; padding: 2px 7px; border-radius: 10px; border: 1px solid #bae6fd; margin-right: 5px;">⚡ Proses Langsung</span>
              <span style="background: #fef08a; color: #854d0e; font-size: 11px; font-weight: bold; padding: 2px 7px; border-radius: 10px; border: 1px solid #fde047;">Uang Muka Tetap Melekat</span>
            </div>
            <div style="color: #374151; font-size: 13px; margin-top: 6px; line-height: 1.4;">Konsumen tetap jadi membeli, hanya membatalkan/mengurangi sebagian barang. Pesanan akan dimundurkan 1 langkah ke draf untuk menghapus barang yang batal.</div>
            <div style="color: #0369a1; font-size: 12px; margin-top: 4px; font-weight: bold;"><i class="fa fa-arrow-circle-right"></i> Rekomendasi: Pilih Opsi 3 jika konsumen tetap belanja tetapi ingin membatalkan sebagian barang.</div>
            <div style="color: #6b7280; font-size: 12px; margin-top: 2px;"><i class="fa fa-info-circle"></i> Saldo uang muka <b>Rp <?php echo $nilai_bayar_f; ?></b> tetap melekat pada pesanan ini dan otomatis memotong tagihan baru saat invoice akhir.</div>
          </div>
        </div>
        <div style="margin-left: 15px; white-space: nowrap;">
          <span class="btn btn-info" style="font-weight: bold; padding: 8px 14px; font-size: 13px; border-radius: 6px; background-color: #0284c7; border-color: #0369a1; color: #ffffff;">Pilih & Lanjutkan &rarr;</span>
        </div>
      </div>
    </div>

    <!-- Tabel Rincian Riwayat Pengiriman Konsumen (SPD) -->
    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 14px;">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <b style="font-size: 13px; color: #334155;"><i class="fa fa-truck"></i> Riwayat Dokumen Pengiriman Konsumen (Surat Jalan / SPD):</b>
        <span style="font-size: 11px; color: #64748b;">Total Dokumen: <?php echo sizeof($linkedSpd); ?></span>
      </div>
      <div class="table-responsive" style="margin-bottom: 0;">
        <table class="table table-bordered table-striped" style="font-size: 12px; margin-bottom: 0; background: #fff;">
          <thead>
            <tr style="background: #f1f5f9; color: #475569;">
              <th style="width: 35px; text-align: center;">No</th>
              <th>No. Surat Jalan</th>
              <th>Tanggal & Jam</th>
              <th>Keterangan</th>
              <th style="text-align: right;">Nilai Barang</th>
              <th>Petugas</th>
              <th style="width: 100px; text-align: center;">Status</th>
            </tr>
          </thead>
          <tbody>
            <?php echo $spdTableRows; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Petunjuk kausalitas tombol aksi bawah -->
    <div style="text-align: center; font-size: 12px; color: #6b7280; padding: 6px 0 2px 0; font-style: italic;">
      <i class="fa fa-hand-o-up"></i> Klik salah satu pilihan di atas untuk membuka formulir alasan & mengaktifkan tombol Tolak/Batalkan di bawah.
    </div>
  </div>

  <!-- STATE 1: KONFIRMASI OPSI 1 & FORM ALASAN WAJIB -->
  <div id="panel_reject_state_1" style="display: none;">
    <div style="background: #eaf2f8; border-left: 4px solid #337ab7; padding: 12px 14px; margin-bottom: 14px; border-radius: 4px;">
      <h4 style="margin: 0 0 6px 0; color: #1b3d5a; font-weight: bold;"><i class="fa fa-question-circle"></i> KONFIRMASI PENGALIHAN UANG MUKA KE KONSUMEN</h4>
      <div style="font-size: 14px; color: #1b3d5a; line-height: 1.5;">
        <b>Apakah anda mampu mengingat bahwa Uang Muka sales order nomer <span style="text-decoration: underline; color: #204d74;"><?php echo $nomer_dibayar; ?></span> dialihkan ke Uang Muka atas nama <span style="text-decoration: underline; color: #204d74;"><?php echo $custNama; ?></span> ?</b>
      </div>
    </div>

    <!-- Detail box Opsi 1 -->
    <div style="background: #fafafa; border: 1px solid #e0e0e0; border-radius: 4px; padding: 12px; margin-bottom: 14px;">
      <table style="width: 100%; font-size: 13px; line-height: 1.8;">
        <tr><td style="width: 160px; color: #666;">Dari Dokumen</td><td>: <b><?php echo $nomer_dibayar; ?></b> (<?php echo $jenisTr; ?>so)</td></tr>
        <tr><td style="color: #666;">Ke Rekening / Saldo</td><td>: <b>Deposit Uang Muka Konsumen</b></td></tr>
        <tr><td style="color: #666;">Nama Konsumen</td><td>: <b><?php echo $custNama; ?></b> (ID: <?php echo $custID; ?>)</td></tr>
        <tr><td style="color: #666;">Nilai Uang Muka</td><td>: <b style="color: #d9534f; font-size: 15px;">Rp <?php echo $nilai_bayar_f; ?></b></td></tr>
      </table>
    </div>

    <!-- Form Alasan Reject Wajib Opsi 1 -->
    <div style="margin-bottom: 14px;">
      <label for="txt_alasan_reject_so_1" style="font-weight: bold; color: #333; display: block; margin-bottom: 6px;">📝 ALASAN REJECT / PEMBATALAN (WAJIB DIISI) <span style="color: red;">*</span> :</label>
      <textarea id="txt_alasan_reject_so_1" class="form-control" rows="3" onkeyup="syncAndValidateOpsi1()" placeholder="Tuliskan alasan pembatalan pesanan secara detail (minimal 5 karakter)..." style="width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 8px; font-size: 13px;"></textarea>
      <div style="display: flex; justify-content: space-between; margin-top: 4px;">
        <span id="txt_alasan_counter_1" style="font-size: 12px; color: #888;">0 / min 5 karakter</span>
        <span id="txt_alasan_error_1" style="font-size: 12px; color: #d9534f; display: none; font-weight: bold;"><i class="fa fa-times-circle"></i> Alasan reject wajib diisi minimal 5 karakter sebelum melanjutkan.</span>
      </div>
    </div>

    <!-- Checklist konfirmasi persetujuan Opsi 1 -->
    <div id="box_checklist_opsi1" style="background: #f0fdf4; border: 2px solid #86efac; border-radius: 6px; padding: 12px 14px; margin-bottom: 14px;">
      <label style="font-size: 13px; font-weight: bold; color: #166534; cursor: pointer; display: flex; align-items: center; margin: 0;">
        <input type="checkbox" id="chk_konfirmasi_opsi_1" value="1" onchange="syncAndValidateOpsi1()" style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
        <span>Ya, saya mengerti & setuju memindahkan Uang Muka ini ke saldo deposit konsumen</span>
      </label>
    </div>

    <!-- Action buttons Opsi 1 -->
    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 12px;">
      <button type="button" class="btn btn-default" onclick="showRejectState(0)"><i class="fa fa-arrow-left"></i> Kembali Pilih Opsi Lain</button>
      <span id="status_opsi1_reject_hint" style="font-size: 12px; color: #888; font-style: italic;">Isi alasan min. 5 karakter & centang persetujuan di atas untuk mengaktifkan tombol Reject di bawah.</span>
    </div>
  </div>

  <!-- STATE 2: RINCIAN KAS (OPSI 2) -->
  <div id="panel_reject_state_2" style="display: none;">
    <div style="background: #fcf8e3; border-left: 4px solid #f0ad4e; padding: 12px 14px; margin-bottom: 14px; border-radius: 4px;">
      <h4 style="margin: 0 0 4px 0; color: #8a6d3b; font-weight: bold;"><i class="fa fa-list-alt"></i> DAFTAR STATUS PENERIMAAN KAS (<?php echo $primaryKasLabel; ?>)</h4>
      <div style="font-size: 13px; color: #66512c;">Referensi Sales Order: <b><?php echo $nomer_dibayar; ?></b> | Konsumen: <b><?php echo $custNama; ?></b></div>
    </div>

    <!-- Tabel Daftar Bukti Kas -->
    <div class="table-responsive" style="margin-bottom: 12px;">
      <table class="table table-bordered table-striped table-hover" style="margin-bottom: 0; background: #fff; font-size: 12px;">
        <thead>
          <tr style="background: #f5f5f5;">
            <th style="width: 35px; text-align: center;">No</th>
            <th>No. Bukti Kas</th>
            <th>Tanggal & Jam</th>
            <th>Akun Kas / Bank</th>
            <th style="text-align: right;">Nominal</th>
            <th>Petugas</th>
            <th style="text-align: center;">Status</th>
          </tr>
        </thead>
        <tbody id="tbody_status_kas_notif">
          <?php echo $kasTableRows; ?>
        </tbody>
      </table>
    </div>

    <!-- Petunjuk Tindak Lanjut -->
    <div style="background: #e8f4f8; border: 1px solid #bce8f1; border-radius: 4px; padding: 12px; margin-bottom: 14px; font-size: 12px; color: #31708f;">
      <b>ℹ️ Petunjuk Tindak Lanjut:</b><br>
      1. Laporkan dan koordinasikan dengan bagian <b>Finance / Kasir</b> untuk membatalkan/void bukti kas di atas.<br>
      2. Setelah bukti kas dibatalkan oleh Finance/Kasir, silakan klik tombol <b>Refresh Status Pembayaran</b> di bawah.<br>
      3. Sistem akan mendeteksi pembatalan kas, dan setelah Anda mengisi alasan serta mencentang konfirmasi, tombol Reject di bawah akan aktif.<br>
      4. Jika akan melakukan pembatalan, lakukan pembatalan penerimaan kas <b><?php echo $strAllKasNomers; ?></b> terlebih dulu.
    </div>

    <!-- Feedback Pesan Refresh AJAX -->
    <div id="box_status_kas_refresh_msg" style="margin-bottom: 14px; display: none;"></div>

    <!-- Form Alasan Reject Wajib Opsi 2 -->
    <div style="margin-bottom: 14px;">
      <label for="txt_alasan_reject_so_2" style="font-weight: bold; color: #333; display: block; margin-bottom: 6px;">📝 ALASAN REJECT / PEMBATALAN (WAJIB DIISI) <span style="color: red;">*</span> :</label>
      <textarea id="txt_alasan_reject_so_2" class="form-control" rows="3" onkeyup="syncAndValidateOpsi2()" placeholder="Tuliskan alasan pembatalan pesanan secara detail (minimal 5 karakter)..." style="width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 8px; font-size: 13px;"></textarea>
      <div style="display: flex; justify-content: space-between; margin-top: 4px;">
        <span id="txt_alasan_counter_2" style="font-size: 12px; color: #888;">0 / min 5 karakter</span>
        <span id="txt_alasan_error_2" style="font-size: 12px; color: #d9534f; display: none; font-weight: bold;"><i class="fa fa-times-circle"></i> Alasan reject wajib diisi minimal 5 karakter sebelum melanjutkan.</span>
      </div>
    </div>

    <!-- Checklist konfirmasi pembatalan kas Opsi 2 -->
    <div id="box_checklist_opsi2" style="background: #fffdf5; border: 2px solid #f0ad4e; border-radius: 6px; padding: 12px 14px; margin-bottom: 14px;">
      <label style="font-size: 13px; font-weight: bold; color: #8a6d3b; cursor: pointer; display: flex; align-items: center; margin: 0;">
        <input type="checkbox" id="chk_konfirmasi_opsi_2" value="1" onchange="syncAndValidateOpsi2()" style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
        <span>Ya, saya mengonfirmasi bahwa bukti penerimaan kas di atas telah dibatalkan (VOID) / dana telah direfund oleh Finance, dan menyetujui pembatalan Sales Order ini.</span>
      </label>
    </div>

    <!-- Action buttons Opsi 2 -->
    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 12px;">
      <button type="button" class="btn btn-default" onclick="showRejectState(0)"><i class="fa fa-arrow-left"></i> Kembali ke Pilihan Opsi</button>
      <div style="display: flex; align-items: center;">
        <span id="status_opsi2_reject_hint" style="font-size: 12px; color: #888; font-style: italic; margin-right: 15px;">Pastikan kas VOID, isi alasan min. 5 karakter & centang konfirmasi untuk mengaktifkan tombol Reject di bawah.</span>
        <button type="button" id="btn_refresh_status_kas" class="btn btn-info" onclick="refreshTabelStatusKas(event); return false;"><i class="fa fa-refresh"></i> Refresh Status Pembayaran</button>
      </div>
    </div>
  </div>

  <!-- STATE 3: KONFIRMASI OPSI 3 & FORM REVISI BARANG -->
  <div id="panel_reject_state_3" style="display: none;">
    <div style="background: #f0f9ff; border-left: 4px solid #0284c7; padding: 12px 14px; margin-bottom: 14px; border-radius: 4px;">
      <h4 style="margin: 0 0 6px 0; color: #075985; font-weight: bold;"><i class="fa fa-refresh"></i> KONFIRMASI REVISI ITEM / BATAL SEBAGIAN BARANG (MUNDUR 1 STEP)</h4>
      <div style="font-size: 14px; color: #0c4a6e; line-height: 1.5;">
        <b>Pesanan Sales Order nomer <span style="text-decoration: underline; color: #0284c7;"><?php echo $nomer_dibayar; ?></span> akan dimundurkan 1 langkah ke tahap sebelumnya untuk merevisi/mengurangi barang.</b>
      </div>
    </div>

    <!-- Detail box Opsi 3 -->
    <div style="background: #fafafa; border: 1px solid #e0e0e0; border-radius: 4px; padding: 12px; margin-bottom: 14px;">
      <table style="width: 100%; font-size: 13px; line-height: 1.8;">
        <tr><td style="width: 160px; color: #666;">Dokumen Aktif</td><td>: <b><?php echo $nomer_dibayar; ?></b> (Tahap: <?php echo $isDokumenPkd ? "Packing List / Pre-Packing" : "Sales Order"; ?>)</td></tr>
        <tr><td style="color: #666;">Status Uang Muka</td><td>: <span style="background: #fef08a; color: #854d0e; padding: 2px 8px; border-radius: 3px; font-weight: bold;">TETAP MELEKAT PADA PESANAN</span> (Tidak Dialihkan ke Deposit)</td></tr>
        <tr><td style="color: #666;">Nama Konsumen</td><td>: <b><?php echo $custNama; ?></b> (ID: <?php echo $custID; ?>)</td></tr>
        <tr><td style="color: #666;">Nilai Uang Muka</td><td>: <b style="color: #0369a1; font-size: 15px;">Rp <?php echo $nilai_bayar_f; ?></b></td></tr>
        <tr><td style="color: #666;">Tindakan Setelah Ini</td><td>: Setelah mundur 1 step, silakan edit draf pesanan untuk membuang/mengurangi barang yang batal dibeli.</td></tr>
      </table>
    </div>

    <!-- Form Alasan Reject Wajib Opsi 3 -->
    <div style="margin-bottom: 14px;">
      <label for="txt_alasan_reject_so_3" style="font-weight: bold; color: #333; display: block; margin-bottom: 6px;">📝 ALASAN REVISI / BATAL SEBAGIAN BARANG (WAJIB DIISI) <span style="color: red;">*</span> :</label>
      <textarea id="txt_alasan_reject_so_3" class="form-control" rows="3" onkeyup="syncAndValidateOpsi3()" placeholder="Tuliskan alasan mengapa barang dikurangi / barang mana yang dibatalkan (minimal 5 karakter)..." style="width: 100%; border: 1px solid #ccc; border-radius: 4px; padding: 8px; font-size: 13px;"></textarea>
      <div style="display: flex; justify-content: space-between; margin-top: 4px;">
        <span id="txt_alasan_counter_3" style="font-size: 12px; color: #888;">0 / min 5 karakter</span>
        <span id="txt_alasan_error_3" style="font-size: 12px; color: #d9534f; display: none; font-weight: bold;"><i class="fa fa-times-circle"></i> Alasan revisi wajib diisi minimal 5 karakter sebelum melanjutkan.</span>
      </div>
    </div>

    <!-- Checklist konfirmasi persetujuan Opsi 3 -->
    <div id="box_checklist_opsi3" style="background: #f0f9ff; border: 2px solid #bae6fd; border-radius: 6px; padding: 12px 14px; margin-bottom: 14px;">
      <label style="font-size: 13px; font-weight: bold; color: #0369a1; cursor: pointer; display: flex; align-items: center; margin: 0;">
        <input type="checkbox" id="chk_konfirmasi_opsi_3" value="1" onchange="syncAndValidateOpsi3()" style="width: 18px; height: 18px; margin-right: 10px; cursor: pointer;">
        <span>Ya, saya mengerti & setuju memundurkan pesanan ini 1 langkah untuk merevisi barang dengan saldo uang muka tetap melekat pada pesanan.</span>
      </label>
    </div>

    <!-- Action buttons Opsi 3 -->
    <div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #eee; padding-top: 12px;">
      <button type="button" class="btn btn-default" onclick="showRejectState(0)"><i class="fa fa-arrow-left"></i> Kembali Pilih Opsi Lain</button>
      <span id="status_opsi3_reject_hint" style="font-size: 12px; color: #888; font-style: italic;">Isi alasan min. 5 karakter & centang persetujuan di atas untuk mengaktifkan tombol Reject 1 Step di bawah.</span>
    </div>
  </div>
</div>

<!-- JAVASCRIPT INLINE UNTUK KONTROL STATE & AJAX -->
<script type="text/javascript">
  window._countActiveKas = <?php echo intval($countActiveKasInitial); ?>;
  window._countActiveSpd = <?php echo intval($countSpdActive); ?>;
  function setRadioActive(stateNum) {
    var c1 = document.getElementById('card_reject_opsi_1');
    var c2 = document.getElementById('card_reject_opsi_2');
    var c3 = document.getElementById('card_reject_opsi_3');
    var rc1 = document.getElementById('radio_circle_1');
    var rc2 = document.getElementById('radio_circle_2');
    var rc3 = document.getElementById('radio_circle_3');
    var rd1 = document.getElementById('radio_dot_1');
    var rd2 = document.getElementById('radio_dot_2');
    var rd3 = document.getElementById('radio_dot_3');
    if (stateNum === 1) {
      if (rd1) rd1.style.display = 'block';
      if (rc1) { rc1.style.borderColor = '#2563eb'; rc1.style.backgroundColor = '#eff6ff'; }
      if (c1) { c1.style.borderColor = '#2563eb'; c1.style.backgroundColor = '#f8fbfe'; c1.style.boxShadow = '0 2px 8px rgba(37,99,235,0.12)'; }
      if (rd2) rd2.style.display = 'none';
      if (rc2) { rc2.style.borderColor = '#9ca3af'; rc2.style.backgroundColor = '#ffffff'; }
      if (c2) { c2.style.borderColor = '#cbd5e1'; c2.style.backgroundColor = '#ffffff'; c2.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)'; }
      if (rd3) rd3.style.display = 'none';
      if (rc3) { rc3.style.borderColor = '#9ca3af'; rc3.style.backgroundColor = '#ffffff'; }
      if (c3) { c3.style.borderColor = '#cbd5e1'; c3.style.backgroundColor = '#ffffff'; c3.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)'; }
    } else if (stateNum === 2) {
      if (rd2) rd2.style.display = 'block';
      if (rc2) { rc2.style.borderColor = '#d97706'; rc2.style.backgroundColor = '#fffbeb'; }
      if (c2) { c2.style.borderColor = '#d97706'; c2.style.backgroundColor = '#fffdf8'; c2.style.boxShadow = '0 2px 8px rgba(217,119,6,0.12)'; }
      if (rd1) rd1.style.display = 'none';
      if (rc1) { rc1.style.borderColor = '#9ca3af'; rc1.style.backgroundColor = '#ffffff'; }
      if (c1) { c1.style.borderColor = '#cbd5e1'; c1.style.backgroundColor = '#ffffff'; c1.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)'; }
      if (rd3) rd3.style.display = 'none';
      if (rc3) { rc3.style.borderColor = '#9ca3af'; rc3.style.backgroundColor = '#ffffff'; }
      if (c3) { c3.style.borderColor = '#cbd5e1'; c3.style.backgroundColor = '#ffffff'; c3.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)'; }
    } else if (stateNum === 3) {
      if (rd3) rd3.style.display = 'block';
      if (rc3) { rc3.style.borderColor = '#0284c7'; rc3.style.backgroundColor = '#e0f2fe'; }
      if (c3) { c3.style.borderColor = '#0284c7'; c3.style.backgroundColor = '#f0f9ff'; c3.style.boxShadow = '0 2px 8px rgba(2,132,199,0.15)'; }
      if (rd1) rd1.style.display = 'none';
      if (rc1) { rc1.style.borderColor = '#9ca3af'; rc1.style.backgroundColor = '#ffffff'; }
      if (c1) { c1.style.borderColor = '#cbd5e1'; c1.style.backgroundColor = '#ffffff'; c1.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)'; }
      if (rd2) rd2.style.display = 'none';
      if (rc2) { rc2.style.borderColor = '#9ca3af'; rc2.style.backgroundColor = '#ffffff'; }
      if (c2) { c2.style.borderColor = '#cbd5e1'; c2.style.backgroundColor = '#ffffff'; c2.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)'; }
    } else {
      if (rd1) rd1.style.display = 'none';
      if (rc1) { rc1.style.borderColor = '#9ca3af'; rc1.style.backgroundColor = '#ffffff'; }
      if (c1) { c1.style.borderColor = '#cbd5e1'; c1.style.backgroundColor = '#ffffff'; c1.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)'; }
      if (rd2) rd2.style.display = 'none';
      if (rc2) { rc2.style.borderColor = '#9ca3af'; rc2.style.backgroundColor = '#ffffff'; }
      if (c2) { c2.style.borderColor = '#cbd5e1'; c2.style.backgroundColor = '#ffffff'; c2.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)'; }
      if (rd3) rd3.style.display = 'none';
      if (rc3) { rc3.style.borderColor = '#9ca3af'; rc3.style.backgroundColor = '#ffffff'; }
      if (c3) { c3.style.borderColor = '#cbd5e1'; c3.style.backgroundColor = '#ffffff'; c3.style.boxShadow = '0 1px 3px rgba(0,0,0,0.05)'; }
    }
  }
  function selectAndShowRejectState(stateNum) {
    setRadioActive(stateNum);
    setTimeout(function() { showRejectState(stateNum); }, 180);
  }
  function showRejectState(stateNum) {
    var p0 = document.getElementById('panel_reject_state_0');
    var p1 = document.getElementById('panel_reject_state_1');
    var p2 = document.getElementById('panel_reject_state_2');
    var p3 = document.getElementById('panel_reject_state_3');
    var hidOpsi = document.getElementById('hidden_opsi_reject_selected');
    var hidAlasan = document.getElementById('hidden_alasan_reject');
    var hidChk = document.getElementById('hidden_checklist_konfirmasi');
    if (p0) p0.style.display = (stateNum === 0) ? 'block' : 'none';
    if (p1) p1.style.display = (stateNum === 1) ? 'block' : 'none';
    if (p2) p2.style.display = (stateNum === 2) ? 'block' : 'none';
    if (p3) p3.style.display = (stateNum === 3) ? 'block' : 'none';
    if (stateNum === 0) {
      if (hidOpsi) hidOpsi.value = '';
      if (hidAlasan) hidAlasan.value = '';
      if (hidChk) hidChk.value = '0';
      setRadioActive(0);
      setMainRejectButtonsState(false, false, false);
    } else if (stateNum === 1) {
      if (hidOpsi) hidOpsi.value = '1';
      setRadioActive(1);
      setTimeout(function() { var el = document.getElementById('txt_alasan_reject_so_1'); if (el) el.focus(); }, 100);
      syncAndValidateOpsi1();
    } else if (stateNum === 2) {
      if (hidOpsi) hidOpsi.value = '2';
      setRadioActive(2);
      setTimeout(function() { var el = document.getElementById('txt_alasan_reject_so_2'); if (el) el.focus(); }, 100);
      syncAndValidateOpsi2();
    } else if (stateNum === 3) {
      if (hidOpsi) hidOpsi.value = '3';
      setRadioActive(3);
      setTimeout(function() { var el = document.getElementById('txt_alasan_reject_so_3'); if (el) el.focus(); }, 100);
      syncAndValidateOpsi3();
    }
  }
  function getMainRejectButtons() {
    var btn1 = document.getElementById('btn_main_reject_1_step');
    var btnAll = document.getElementById('btn_main_reject_all_steps');
    if (!btn1 || !btnAll) {
      var allButtons = document.querySelectorAll('button');
      for (var i = 0; i < allButtons.length; i++) {
        var txt = (allButtons[i].textContent || allButtons[i].innerText || '').toLowerCase().trim();
        if (!btn1 && txt.indexOf('reject 1 step') !== -1) {
          btn1 = allButtons[i];
          btn1.id = 'btn_main_reject_1_step';
        }
        if (!btnAll && txt.indexOf('reject all steps') !== -1) {
          btnAll = allButtons[i];
          btnAll.id = 'btn_main_reject_all_steps';
        }
      }
    }
    return { btn1: btn1, btnAll: btnAll };
  }
  window._isDokumenPkd = <?php echo ($isDokumenPkd ? "true" : "false"); ?>;
  function updateHintMandatoryRejectButtons(show) {
    var hint = document.getElementById('hint_mandatory_reject_buttons');
    if (!hint) {
      var btns = getMainRejectButtons();
      var targetBtn = btns.btn1 || btns.btnAll;
      if (targetBtn && targetBtn.parentNode) {
        hint = document.createElement('div');
        hint.id = 'hint_mandatory_reject_buttons';
        hint.style.cssText = 'font-size: 12px; color: #b91c1c; font-weight: 600; margin-bottom: 6px; text-align: right; width: 100%;';
        hint.innerHTML = '<i class="fa fa-info-circle"></i> tombol abu-abu (tidak bisa diklik) karena ada formulir mandatory yang belum diisi/diklik/dipilih.';
        targetBtn.parentNode.insertBefore(hint, targetBtn.parentNode.firstChild);
      }
    }
    if (hint) {
      hint.style.display = show ? 'block' : 'none';
    }
  }
  function setMainRejectButtonsState(enabled, enableBtn1, enableBtnAll) {
    var btns = getMainRejectButtons();
    var btn1 = btns.btn1;
    var btnAll = btns.btnAll;
    if (typeof enableBtn1 === 'undefined') {
      enableBtn1 = enabled;
    }
    if (typeof enableBtnAll === 'undefined') {
      enableBtnAll = enabled;
    }
    if (btn1) {
      btn1.disabled = !enableBtn1;
      btn1.style.opacity = enableBtn1 ? '1' : '0.4';
      if (!enableBtn1 && enabled) {
        btn1.title = 'Tombol Reject 1 Step tidak aktif untuk opsi ini.';
      } else {
        btn1.title = '';
      }
    }
    if (btnAll) {
      if (window._countActiveSpd > 0) {
        btnAll.disabled = true;
        btnAll.style.opacity = '0.4';
        btnAll.title = 'Terdapat Surat Jalan aktif ke konsumen. Tidak dapat Reject All Steps.';
      } else {
        btnAll.disabled = !enableBtnAll;
        btnAll.style.opacity = enableBtnAll ? '1' : '0.4';
        if (!enableBtnAll && enabled) {
          btnAll.title = 'Tombol Reject All Steps tidak aktif untuk opsi ini.';
        } else {
          btnAll.title = '';
        }
      }
    }
    var isAnyEnabled = (btn1 && !btn1.disabled) || (btnAll && !btnAll.disabled);
    updateHintMandatoryRejectButtons(!isAnyEnabled);
  }
  function triggerReject1StepFromBox() {
    if (window._countActiveSpd > 0) {
      alert("Terdapat Surat Jalan aktif ke konsumen. Harap batalkan Surat Jalan pengiriman terlebih dahulu di bagian gudang/pengiriman.");
      return;
    }
    var btns = getMainRejectButtons();
    if (btns.btn1 && !btns.btn1.disabled) {
      btns.btn1.click();
    } else {
      var f1 = document.getElementById('f1');
      var targetUrl = '<?php echo isset($doRevertTargetUrl) ? $doRevertTargetUrl : ""; ?>';
      if (f1 && targetUrl) {
        if (confirm("Mundur 1 step ke tahap sebelumnya untuk merevisi/mengurangi barang?\nSaldo pembayaran TETAP MELEKAT pada pesanan ini.")) {
          f1.action = targetUrl;
          f1.submit();
        }
      } else if (btns.btn1) {
        btns.btn1.disabled = false;
        btns.btn1.style.opacity = '1';
        btns.btn1.click();
      }
    }
  }
  function syncAndValidateOpsi1() {
    var txt = document.getElementById('txt_alasan_reject_so_1');
    var val = txt ? txt.value.trim() : '';
    var chk = document.getElementById('chk_konfirmasi_opsi_1');
    var isChecked = chk ? chk.checked : false;
    var counter = document.getElementById('txt_alasan_counter_1');
    var err = document.getElementById('txt_alasan_error_1');
    var hint = document.getElementById('status_opsi1_reject_hint');
    var hidAlasan = document.getElementById('hidden_alasan_reject');
    var hidChk = document.getElementById('hidden_checklist_konfirmasi');
    var hidOpsi = document.getElementById('hidden_opsi_reject_selected');
    if (counter) { counter.innerText = val.length + ' / min 5 karakter'; }
    if (hidOpsi) hidOpsi.value = '1';
    if (hidAlasan) hidAlasan.value = val;
    if (hidChk) hidChk.value = isChecked ? '1' : '0';
    if (val.length >= 5 && isChecked) {
      if (err) { err.style.display = 'none'; }
      setMainRejectButtonsState(true, true, true);
      if (hint) { hint.innerHTML = '<b style="color: #2e7d32;"><i class="fa fa-check-circle"></i> Data lengkap. Tombol Reject 1 Step & Reject All Steps di bawah sudah AKTIF (Opsi 1). Silakan pilih tombol sesuai keputusan operasional Anda.</b>'; }
    } else {
      setMainRejectButtonsState(false, false, false);
      if (hint) {
        if (val.length < 5) {
          hint.innerHTML = '<span style="color: #c62828;"><i class="fa fa-exclamation-triangle"></i> Alasan reject wajib diisi minimal 5 karakter.</span>';
        } else if (!isChecked) {
          hint.innerHTML = '<span style="color: #e65100;"><i class="fa fa-info-circle"></i> Silakan centang kotak persetujuan pengalihan uang muka di atas.</span>';
        }
      }
    }
  }
  function syncAndValidateOpsi2() {
    var txt = document.getElementById('txt_alasan_reject_so_2');
    var val = txt ? txt.value.trim() : '';
    var chk = document.getElementById('chk_konfirmasi_opsi_2');
    var isChecked = chk ? chk.checked : false;
    var counter = document.getElementById('txt_alasan_counter_2');
    var err = document.getElementById('txt_alasan_error_2');
    var hint = document.getElementById('status_opsi2_reject_hint');
    var hidAlasan = document.getElementById('hidden_alasan_reject');
    var hidChk = document.getElementById('hidden_checklist_konfirmasi');
    var hidOpsi = document.getElementById('hidden_opsi_reject_selected');
    if (counter) { counter.innerText = val.length + ' / min 5 karakter'; }
    if (hidOpsi) hidOpsi.value = '2';
    if (hidAlasan) hidAlasan.value = val;
    if (hidChk) hidChk.value = isChecked ? '1' : '0';
    var hasActiveKas = (window._countActiveKas > 0);
    if (hasActiveKas) {
      setMainRejectButtonsState(false, false, false);
      if (hint) { hint.innerHTML = '<span style="color: #c62828; font-weight: bold;"><i class="fa fa-ban"></i> Masih ada ' + window._countActiveKas + ' bukti kas AKTIF di Finance. Harap batalkan bukti kas terlebih dahulu & klik Refresh.</span>'; }
    } else if (val.length >= 5 && isChecked) {
      if (err) { err.style.display = 'none'; }
      setMainRejectButtonsState(true, true, true);
      if (hint) { hint.innerHTML = '<b style="color: #2e7d32;"><i class="fa fa-check-circle"></i> Bukti kas telah VOID & data lengkap. Tombol Reject 1 Step & Reject All Steps di bawah sudah AKTIF (Opsi 2). Silakan pilih tombol sesuai keputusan operasional Anda.</b>'; }
    } else {
      setMainRejectButtonsState(false, false, false);
      if (hint) {
        if (val.length < 5) {
          hint.innerHTML = '<span style="color: #c62828;"><i class="fa fa-exclamation-triangle"></i> Alasan reject wajib diisi minimal 5 karakter.</span>';
        } else if (!isChecked) {
          hint.innerHTML = '<span style="color: #e65100;"><i class="fa fa-info-circle"></i> Silakan centang kotak konfirmasi pembatalan kas di atas.</span>';
        }
      }
    }
  }
  function syncAndValidateOpsi3() {
    var txt = document.getElementById('txt_alasan_reject_so_3');
    var val = txt ? txt.value.trim() : '';
    var chk = document.getElementById('chk_konfirmasi_opsi_3');
    var isChecked = chk ? chk.checked : false;
    var counter = document.getElementById('txt_alasan_counter_3');
    var err = document.getElementById('txt_alasan_error_3');
    var hint = document.getElementById('status_opsi3_reject_hint');
    var hidAlasan = document.getElementById('hidden_alasan_reject');
    var hidChk = document.getElementById('hidden_checklist_konfirmasi');
    var hidOpsi = document.getElementById('hidden_opsi_reject_selected');
    if (counter) { counter.innerText = val.length + ' / min 5 karakter'; }
    if (hidOpsi) hidOpsi.value = '3';
    if (hidAlasan) hidAlasan.value = val;
    if (hidChk) hidChk.value = isChecked ? '1' : '0';
    if (val.length >= 5 && isChecked) {
      if (err) { err.style.display = 'none'; }
      setMainRejectButtonsState(true, true, false);
      if (hint) { hint.innerHTML = '<b style="color: #0369a1;"><i class="fa fa-check-circle"></i> Data lengkap. Tombol Reject 1 Step di bawah sudah AKTIF (Opsi 3). Saldo uang muka tetap melekat pada pesanan.</b>'; }
    } else {
      setMainRejectButtonsState(false, false, false);
      if (hint) {
        if (val.length < 5) {
          hint.innerHTML = '<span style="color: #c62828;"><i class="fa fa-exclamation-triangle"></i> Alasan revisi wajib diisi minimal 5 karakter.</span>';
        } else if (!isChecked) {
          hint.innerHTML = '<span style="color: #0369a1;"><i class="fa fa-info-circle"></i> Silakan centang kotak persetujuan revisi draf di atas.</span>';
        }
      }
    }
  }
  setTimeout(function() {
    getMainRejectButtons();
    setMainRejectButtonsState(false, false, false);
  }, 250);
  function refreshTabelStatusKas(e) {
    if (e && e.preventDefault) { e.preventDefault(); }
    var btn = document.getElementById('btn_refresh_status_kas');
    var tbody = document.getElementById('tbody_status_kas_notif');
    var msgBox = document.getElementById('box_status_kas_refresh_msg');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Memperbarui Status...'; }
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo $ajaxStatusKasUrl; ?>', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4) {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-refresh"></i> Refresh Status Pembayaran'; }
        if (xhr.status === 200) {
          try {
            var res = JSON.parse(xhr.responseText);
            if (res.status === 'success') {
              if (tbody) { tbody.innerHTML = res.rows_html; }
              window._countActiveKas = parseInt(res.active_count);
              if (msgBox) {
                msgBox.style.display = 'block';
                if (res.all_voided) {
                  msgBox.innerHTML = '<div class="alert alert-success" style="margin-bottom: 0; padding: 10px;"><i class="fa fa-check-circle"></i> <b>Semua Bukti Kas Telah Dibatalkan (VOID)!</b><br>Bukti penerimaan kas pelunas sudah tidak aktif. Silakan isi alasan reject dan centang konfirmasi di bawah untuk mengaktifkan tombol Reject SO.</div>';
                } else {
                  var nowStr = new Date().toLocaleTimeString();
                  msgBox.innerHTML = '<div class="alert alert-info" style="margin-bottom: 0; padding: 10px;"><i class="fa fa-info-circle"></i> Status kas diperbarui pukul <b>' + nowStr + '</b>. Terdapat <b>' + res.active_count + '</b> bukti kas yang masih berstatus <b>AKTIF</b>.<br>Silakan hubungi Finance/Kasir jika bukti kas seharusnya sudah dibatalkan.</div>';
                }
              }
              syncAndValidateOpsi2();
            } else {
              alert('❌ Gagal memperbarui status: ' + res.message);
            }
          } catch (e) {
            alert('❌ Terjadi kesalahan format data dari server.');
          }
        } else {
          alert('❌ Kesalahan jaringan/server: Status ' + xhr.status);
        }
      }
    };
    var params = 'so_id=<?php echo $rootSOId; ?>&nomer_dibayar=' + encodeURIComponent('<?php echo $nomer_dibayar; ?>');
    xhr.send(params);
  }
</script>
<?php
// END OF COMPLETE REPEATED LOGIC
?>

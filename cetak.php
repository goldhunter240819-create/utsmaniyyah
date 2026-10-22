<?php
/**
 * Cetak Syahadah & Transkrip Nilai Page
 */
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Cetak Syahadah & Transkrip Nilai';
$currentPage = 'cetak';

require_once __DIR__ . '/includes/header.php';
?>

<section id="tab-cetak" class="tab-content active">
  <!-- Control Bar for Print Preview -->
  <div class="card no-print" style="margin-bottom: 20px;">
    <div class="card-header" style="flex-direction: column; align-items: stretch; gap: 14px;">
      <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div class="card-title">
          <i>📜</i> <span>Format Resmi Syahadah & Transkrip Nilai Munaqosyah</span>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
          <button class="btn btn-gold" onclick="window.print()">
            🖨️ Cetak / Simpan PDF
          </button>
          <button class="btn btn-outline" onclick="printAllSyahadah('both')">
            📑 Cetak Semua Santri (Batch)
          </button>
        </div>
      </div>

      <!-- Options & Filter Row -->
      <div style="display: flex; gap: 14px; align-items: center; flex-wrap: wrap; background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid var(--gray-200);">
        <div style="display: flex; align-items: center; gap: 8px;">
          <label style="font-size: 0.82rem; font-weight: 700; color: var(--dark-700); white-space: nowrap;">Pilih Santri:</label>
          <select id="cetakSantriSelect" class="form-control" style="width: 250px; font-weight: 600;" onchange="generateCetakPreview()"></select>
        </div>

        <div style="display: flex; align-items: center; gap: 8px;">
          <label style="font-size: 0.82rem; font-weight: 700; color: var(--dark-700); white-space: nowrap;">Halaman:</label>
          <select id="cetakDocTypeSelect" class="form-control" style="width: 210px;" onchange="generateCetakPreview()">
            <option value="both">Halaman Depan & Belakang</option>
            <option value="depan">Hanya Depan (Syahadah)</option>
            <option value="belakang">Hanya Belakang (Transkrip)</option>
            <option value="formulir">Formulir Pendaftaran Ujian</option>
          </select>
        </div>

        <!-- Toggles for Certificate Authenticity -->
        <div style="display: flex; align-items: center; gap: 18px; margin-left: auto; flex-wrap: wrap;">
          <label style="display: flex; align-items: center; gap: 6px; font-size: 0.82rem; font-weight: 700; cursor: pointer; color: var(--dark-700);" title="Centang untuk cetak pada kertas HVS putih atau export PDF. Hilangkan centang jika mencetak di atas kertas blangko bersertifikat resmi.">
            <input type="checkbox" id="chkShowFrame" checked onchange="generateCetakPreview()">
            <span>Bingkai Emas Hijau Vektor (Depan-Belakang)</span>
          </label>
          <label style="display: flex; align-items: center; gap: 6px; font-size: 0.82rem; font-weight: 700; cursor: pointer; color: var(--dark-700);" title="Sertakan stempel ungu & tanda tangan resmi digital">
            <input type="checkbox" id="chkShowStamp" checked onchange="generateCetakPreview()">
            <span>Stempel & TTD Resmi</span>
          </label>
        </div>
      </div>
    </div>
  </div>

  <!-- Document Render Sheet Area -->
  <div class="print-preview-container" id="cetakPreviewArea"></div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

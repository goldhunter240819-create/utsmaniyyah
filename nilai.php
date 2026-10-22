<?php
/**
 * Penilaian Ujian Page
 */
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Penilaian Ujian (9 Mapel)';
$currentPage = 'nilai';

require_once __DIR__ . '/includes/header.php';
?>

<section id="tab-penilaian" class="tab-content active">
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <i>📝</i> <span>Input & Evaluasi Nilai 9 Mata Ujian (Skala Lembar Ujian: 0 - 10 / Desimal)</span>
      </div>
      <div style="font-size: 0.82rem; color: var(--dark-500);">
        💡 Nilai otomatis tersimpan langsung ke database MySQL saat diketik.
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th class="text-center">No</th>
              <th>Nama Santri</th>
              <th class="text-center" title="Ghoroibul Qur'an">Ghorib</th>
              <th class="text-center" title="Ilmu Tajwid">Tajwid</th>
              <th class="text-center" title="Tartil & Fashohah">Tartil</th>
              <th class="text-center" title="Hafalan Surat Pendek">Surat</th>
              <th class="text-center" title="Hafalan Do'a Harian">Do'a</th>
              <th class="text-center" title="Tauhid Dasar">Tauhid</th>
              <th class="text-center" title="Praktik Wudlu">Wudlu</th>
              <th class="text-center" title="Praktik Sholat / Fasolatan">Sholat</th>
              <th class="text-center" title="Kitabah">Kitabah</th>
              <th class="text-center">Total</th>
              <th class="text-center">Rata</th>
              <th class="text-center">Status</th>
            </tr>
          </thead>
          <tbody id="penilaianTableBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

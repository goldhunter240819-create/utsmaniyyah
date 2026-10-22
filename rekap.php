<?php
/**
 * Rekapitulasi & Ranking Page
 */
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Rekap & Ranking Nilai Munaqosyah';
$currentPage = 'rekap';

require_once __DIR__ . '/includes/header.php';
?>

<section id="tab-rekap" class="tab-content active">
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <i>🏆</i> <span>Rekapitulasi Nilai & Peringkat Kelulusan</span>
      </div>
      <div style="display: flex; gap: 8px;">
        <button class="btn btn-outline btn-sm" onclick="printRekapitulasiLandscape()">
          🖨️ Cetak Lembar Rekap (Landscape)
        </button>
        <button class="btn btn-success btn-sm" onclick="exportRekapExcel()">
          📊 Export ke Excel (.xlsx)
        </button>
      </div>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th class="text-center">No</th>
              <th>Nama Santri</th>
              <th>Asal RTQ</th>
              <th class="text-center">Ghorib</th>
              <th class="text-center">Tajwid</th>
              <th class="text-center">Tartil</th>
              <th class="text-center">Surat</th>
              <th class="text-center">Do'a</th>
              <th class="text-center">Tauhid</th>
              <th class="text-center">Wudlu</th>
              <th class="text-center">Sholat</th>
              <th class="text-center">Kitabah</th>
              <th class="text-center">Total</th>
              <th class="text-center">Rata</th>
              <th class="text-center">Peringkat</th>
            </tr>
          </thead>
          <tbody id="rekapTableBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

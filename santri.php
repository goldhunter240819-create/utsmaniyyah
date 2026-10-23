<?php
/**
 * Data Santri Page
 */
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Data Santri Munaqosyah';
$currentPage = 'santri';

require_once __DIR__ . '/includes/header.php';
?>

<section id="tab-santri" class="tab-content active">
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <i>👥</i> <span>Daftar Santri Peserta Munaqosyah</span>
      </div>
      <button class="btn btn-gold btn-sm" onclick="openAddSantriModal()">
        ➕ Tambah Santri
      </button>
    </div>
    <div class="card-body">
      <div class="filter-toolbar">
        <div class="search-box">
          <i>🔍</i>
          <input type="text" id="searchSantriInput" class="search-input" placeholder="Cari nama, no. induk, atau ayah...">
        </div>
        <div>
          <button class="btn btn-outline btn-sm" onclick="exportRekapExcel()">📥 Ekspor Data ke Excel</button>
        </div>
      </div>

      <div class="table-responsive" style="overflow-x: auto; border-radius: 8px; border: 1px solid var(--gray-300);">
        <table class="table-custom table-excel" style="min-width: 1350px;">
          <thead>
            <tr>
              <th class="text-center" style="width: 45px;">No</th>
              <th style="min-width: 180px;">Nama Lengkap Santri</th>
              <th class="text-center" style="width: 80px;">L / P</th>
              <th style="min-width: 130px;">Unit RTQ</th>
              <th style="min-width: 160px;">Tempat, Tanggal Lahir</th>
              <th style="min-width: 130px;">Nama Ayah</th>
              <th style="min-width: 130px;">Nama Ibu</th>
              <th style="min-width: 220px;">Alamat Lengkap</th>
              <th style="min-width: 90px;">No. Induk</th>
              <th style="min-width: 90px;">No. Ujian</th>
              <th style="min-width: 160px;">No. Syahadah</th>
              <th class="text-center" style="width: 140px;">Aksi</th>
            </tr>
          </thead>
          <tbody id="santriTableBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

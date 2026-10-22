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

      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th class="text-center" style="width: 5%;">No</th>
              <th style="width: 25%;">Nama & TTL</th>
              <th style="width: 15%;">Unit RTQ</th>
              <th style="width: 10%;">Gender</th>
              <th style="width: 15%;">No Induk & Ujian</th>
              <th style="width: 15%;">No. Syahadah</th>
              <th class="text-center" style="width: 15%;">Aksi</th>
            </tr>
          </thead>
          <tbody id="santriTableBody"></tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

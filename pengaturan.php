<?php
/**
 * Pengaturan Sistem & Multi-RTQ Page
 */
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Pengaturan Sistem & Multi-RTQ';
$currentPage = 'pengaturan';

require_once __DIR__ . '/includes/header.php';
?>

<section id="tab-pengaturan" class="tab-content active">
  <!-- Lembaga Induk -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <i>🏛️</i> <span>Pengaturan Lembaga Induk & Parameter Kelulusan</span>
      </div>
    </div>
    <div class="card-body">
      <div id="cfgRoleNotice" style="margin-bottom: 18px;"></div>
      <form id="configForm">
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Nama Lembaga Pusat / Cabang</label>
            <input type="text" id="cfgLembaga" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Metode Pembelajaran</label>
            <input type="text" id="cfgMetode" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Wilayah Lembaga</label>
            <input type="text" id="cfgWilayah" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Tingkat Pendidikan</label>
            <input type="text" id="cfgTingkat" class="form-control" placeholder="RTQ TINGKAT DASAR" required>
          </div>
          <div class="form-group">
            <label class="form-label">Nama Pimpinan Lajnah Muroqobah</label>
            <input type="text" id="cfgPimpinanLajnah" class="form-control" required>
          </div>
          <div class="form-group">
            <label class="form-label">Nama Ketua Panitia Ujian</label>
            <input type="text" id="cfgKetuaPanitia" class="form-control">
          </div>
          <div class="form-group">
            <label class="form-label">Batas Nilai Kelulusan Total (KKM)</label>
            <input type="number" id="cfgKkm" class="form-control" min="0" max="100" required>
          </div>
        </div>

        <div style="margin-top: 18px; text-align: right;" id="btnSaveLembagaWrap">
          <button type="submit" class="btn btn-primary">💾 Simpan Data Lembaga</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Manajemen Unit RTQ -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <i>🕌</i> <span>Manajemen Unit RTQ</span>
      </div>
      <button class="btn btn-gold btn-sm" id="btnAddRtqBtn" onclick="openAddRtqModal()">➕ Tambah Unit RTQ</button>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Kode</th>
              <th>Nama Unit RTQ</th>
              <th>Alamat Lengkap</th>
              <th>Kepala RTQ</th>
              <th>Ketua Tim Penguji</th>
              <th>Kontak WA</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody id="rtqListTableBody"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Manajemen Akun Pengurus RTQ & Hak Akses (Hanya Developer yang dapat mengelola) -->
  <div class="card" id="userManagementCard">
    <div class="card-header">
      <div class="card-title">
        <i>👥</i> <span>Manajemen Akun Pengguna & Hak Akses Login</span>
      </div>
      <button class="btn btn-gold btn-sm" id="btnAddUserBtn" onclick="openAddUserModal()">➕ Tambah Akun Pengurus</button>
    </div>
    <div class="card-body">
      <p style="color: var(--dark-600); font-size: 0.88rem; margin-bottom: 16px;">
        Akun-akun di bawah ini digunakan oleh pengurus masing-masing unit RTQ untuk login ke sistem di halaman <strong>login.php</strong>. Setiap pengurus unit RTQ hanya dapat mengelola data santri dari unit RTQ yang ditetapkan.
      </p>
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Username Login</th>
              <th>Nama Pengurus / Akun</th>
              <th>Unit RTQ yang Dipegang</th>
              <th>Hak Akses (Role)</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody id="userListTableBody"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Manajemen Periode / Tahun Ujian -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <i>📅</i> <span>Manajemen Periode / Tahun Pelajaran Ujian</span>
      </div>
      <button class="btn btn-gold btn-sm" id="btnAddPeriodeBtn" onclick="openAddPeriodeModal()">➕ Tambah Tahun / Periode</button>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table-custom">
          <thead>
            <tr>
              <th>Tahun Pelajaran</th>
              <th>Tanggal Hijriyah</th>
              <th>Tanggal Masehi</th>
              <th>Tanggal Terbit Syahadah</th>
              <th>Titimangsa</th>
              <th class="text-center">Aksi</th>
            </tr>
          </thead>
          <tbody id="periodeListTableBody"></tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Backup & Restore Data -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">
        <i>💾</i> <span>Cadangkan Data MySQL (Export / Backup)</span>
      </div>
    </div>
    <div class="card-body">
      <p style="color: var(--dark-600); margin-bottom: 16px;">
        Anda dapat mengekspor seluruh data santri, nilai, dan konfigurasi dari database MySQL ke dalam file JSON atau Excel untuk cadangan data.
      </p>
      <div style="display: flex; gap: 12px; flex-wrap: wrap;">
        <button class="btn btn-outline" onclick="backupDataJson()">⬇️ Unduh Cadangan Data (JSON)</button>
        <button class="btn btn-outline" onclick="exportRekapExcel()">📊 Unduh Seluruh Nilai (Excel)</button>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

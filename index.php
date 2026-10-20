<?php
session_start();
require_once __DIR__ . '/config/database.php';
$currentUser = $_SESSION['current_user'] ?? [
    'id' => 1,
    'username' => 'admin',
    'nama' => 'Super Admin Lajnah Pusat',
    'role' => 'SUPER_ADMIN',
    'rtq_id' => null
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Penilaian Munaqosyah & Cetak Syahadah - RTQ Utsmaniyyah</title>
  <meta name="description" content="Aplikasi Web Sistem Informasi Penilaian Munaqosyah dan Cetak Syahadah RTQ Utsmaniyyah Metode Yanbu'a Lampung Barat.">
  
  <!-- Favicon Logo Yanbua PNG Asli -->
  <link rel="icon" type="image/png" href="assets/images/logo-yanbua.png">

  <!-- Google Fonts: Plus Jakarta Sans & Amiri -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Cinzel:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- SheetJS for Excel (.xlsx) Export -->
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

  <!-- Main Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <div class="app-wrapper">
    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <img src="assets/images/logo-yanbua.png" alt="Logo Yanbu'a">
        </div>
        <div class="sidebar-brand">
          <h2>MUNAQOSYAH</h2>
          <p id="sidebarActiveRtqLabel">Yanbu'a Lampung Barat</p>
          <button class="btn btn-sm" onclick="openRoleModal()" style="padding: 2px 8px; font-size: 0.72rem; margin-top: 5px; background: rgba(255,255,255,0.15); color: #fff; border: 1px solid rgba(255,255,255,0.25); border-radius: 20px;">
            🔄 Ganti Pengurus
          </button>
        </div>
      </div>

      <div style="padding: 12px 16px; background: rgba(0,0,0,0.15); font-size: 0.8rem; border-bottom: 1px solid rgba(255,255,255,0.08);">
        <div style="opacity: 0.7; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Login Pengurus:</div>
        <div style="font-weight: 700; color: var(--gold-400);" id="currentUserBadge">
          <?= htmlspecialchars($currentUser['nama']) ?>
        </div>
      </div>

      <ul class="sidebar-menu">
        <li class="menu-category">Utama</li>
        <li class="nav-item">
          <a class="nav-link active" data-tab="tab-dashboard">
            <i>📊</i> <span>Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-tab="tab-santri">
            <i>👥</i> <span>Data Santri</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-tab="tab-penilaian">
            <i>📝</i> <span>Penilaian Ujian</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-tab="tab-rekap">
            <i>🏆</i> <span>Rekap & Ranking</span>
          </a>
        </li>

        <li class="menu-category">Dokumen & Cetak</li>
        <li class="nav-item">
          <a class="nav-link" data-tab="tab-cetak">
            <i>📜</i> <span>Cetak Syahadah</span>
          </a>
        </li>

        <li class="menu-category">Konfigurasi</li>
        <li class="nav-item">
          <a class="nav-link" data-tab="tab-pengaturan">
            <i>⚙️</i> <span>Pengaturan Multi-RTQ</span>
          </a>
        </li>
      </ul>

      <div class="sidebar-footer">
        <div>Metode Yanbu'a Lampung Barat</div>
        <div style="font-size: 0.7rem; opacity: 0.7; margin-top: 2px;">Database MySQL Active (XAMPP)</div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Topbar Header -->
      <header class="topbar">
        <div class="topbar-title">
          <button class="menu-toggle" id="menuToggle" aria-label="Buka Menu">☰</button>
          <h1 id="topbarPageTitle">Dashboard Munaqosyah</h1>
        </div>
        <div class="topbar-actions">
          <div class="topbar-filters">
            <select id="topbarRtqFilter" class="topbar-select" title="Pilih Unit RTQ">
              <option value="ALL">Semua RTQ (Gabungan)</option>
            </select>
            <select id="topbarPeriodeFilter" class="topbar-select" title="Pilih Periode / Tahun Ujian">
            </select>
          </div>
          <button class="btn btn-outline btn-sm" onclick="exportRekapExcel()">
            📥 <span>Export Excel</span>
          </button>
          <button class="btn btn-gold btn-sm" onclick="openAddSantriModal()">
            ➕ <span>Tambah Santri</span>
          </button>
        </div>
      </header>

      <!-- Page Container -->
      <main class="page-container">
        
        <!-- ============================================== -->
        <!-- TAB 1: DASHBOARD -->
        <!-- ============================================== -->
        <section id="tab-dashboard" class="tab-content active">
          <!-- Hero Banner -->
          <div class="hero-banner">
            <div class="hero-text">
              <h2>Lajnah Muroqobah Yanbu'a Lampung Barat</h2>
              <p id="heroSubtitle">Sistem Terintegrasi Multi-RTQ: Pengolahan Nilai Munaqosyah, Evaluasi Ujian Akhir, dan Pencetakan Syahadah Resmi.</p>
              <div class="hero-badge">
                <span id="heroRtqBadge">📍 Unit: RTQ Utsmaniyyah I, II, & III</span>
              </div>
            </div>
            <div class="hero-img">
              <img src="assets/images/logo-yanbua.png" alt="Logo Yanbu'a">
            </div>
          </div>

          <!-- Stats Grid -->
          <div class="stats-grid">
            <div class="stat-card primary">
              <div class="stat-icon">👥</div>
              <div class="stat-details">
                <h3>Total Santri</h3>
                <div class="stat-number" id="dashTotalSantri">0</div>
                <div class="stat-desc">Peserta Munaqosyah</div>
              </div>
            </div>
            <div class="stat-card success">
              <div class="stat-icon">✅</div>
              <div class="stat-details">
                <h3>Santri Lulus</h3>
                <div class="stat-number" id="dashLulus">0</div>
                <div class="stat-desc">Memenuhi KKM</div>
              </div>
            </div>
            <div class="stat-card danger">
              <div class="stat-icon">⚠️</div>
              <div class="stat-details">
                <h3>Belum Lulus</h3>
                <div class="stat-number" id="dashTidakLulus">0</div>
                <div class="stat-desc">Perlu Perbaikan / Remidi</div>
              </div>
            </div>
            <div class="stat-card gold">
              <div class="stat-icon">⭐</div>
              <div class="stat-details">
                <h3>Rata-Rata Nilai</h3>
                <div class="stat-number" id="dashRataRata">0</div>
                <div class="stat-desc">Seluruh Mata Ujian</div>
              </div>
            </div>
          </div>

          <!-- Top 3 Ranking (Podium) -->
          <div class="card">
            <div class="card-header">
              <div class="card-title">
                <i>🏆</i> <span>Peringkat 3 Besar Santri Terbaik (Top Ranking)</span>
              </div>
              <button class="btn btn-outline btn-sm" onclick="switchTab('tab-rekap')">Lihat Semua Rangking →</button>
            </div>
            <div class="card-body">
              <div class="podium-grid" id="dashPodium"></div>
            </div>
          </div>

          <!-- Subject Averages Breakdown -->
          <div class="card">
            <div class="card-header">
              <div class="card-title">
                <i>📈</i> <span>Distribusi Rata-Rata 9 Mata Ujian Munaqosyah</span>
              </div>
            </div>
            <div class="card-body">
              <div id="dashSubjectBars"></div>
            </div>
          </div>
        </section>

        <!-- ============================================== -->
        <!-- TAB 2: DATA SANTRI -->
        <!-- ============================================== -->
        <section id="tab-santri" class="tab-content">
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

        <!-- ============================================== -->
        <!-- TAB 3: PENILAIAN MUNAQOSYAH -->
        <!-- ============================================== -->
        <section id="tab-penilaian" class="tab-content">
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

        <!-- ============================================== -->
        <!-- TAB 4: REKAPITULASI & RANKING -->
        <!-- ============================================== -->
        <section id="tab-rekap" class="tab-content">
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

        <!-- ============================================== -->
        <!-- TAB 5: CETAK DOKUMEN RESMI (SYAHADAH & TRANSKRIP) -->
        <!-- ============================================== -->
        <section id="tab-cetak" class="tab-content">
          <!-- Control Bar for Print Preview -->
          <div class="card no-print">
            <div class="card-header">
              <div class="card-title">
                <i>📜</i> <span>Pratinjau & Cetak Syahadah / Transkrip Nilai</span>
              </div>
              <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <select id="cetakSantriSelect" class="form-control" style="width: 260px; font-weight: 600;"></select>
                <select id="cetakDocTypeSelect" class="form-control" style="width: 220px;">
                  <option value="both">Halaman Depan & Belakang</option>
                  <option value="depan">Hanya Depan (Syahadah)</option>
                  <option value="belakang">Hanya Belakang (Transkrip)</option>
                  <option value="formulir">Formulir Pendaftaran Ujian</option>
                </select>
                <button class="btn btn-gold" onclick="window.print()">
                  🖨️ Cetak Dokumen Ini
                </button>
                <button class="btn btn-outline" onclick="printAllSyahadah('both')">
                  📑 Cetak Semua Santri Sekaligus
                </button>
              </div>
            </div>
          </div>

          <!-- Document Render Sheet Area -->
          <div class="print-preview-container" id="cetakPreviewArea"></div>
        </section>

        <!-- ============================================== -->
        <!-- TAB 6: PENGATURAN MULTI-RTQ & PERIODE -->
        <!-- ============================================== -->
        <section id="tab-pengaturan" class="tab-content">
          <!-- Lembaga Induk -->
          <div class="card">
            <div class="card-header">
              <div class="card-title">
                <i>🏛️</i> <span>Pengaturan Lembaga Induk & Parameter Kelulusan</span>
              </div>
            </div>
            <div class="card-body">
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

                <div style="margin-top: 18px; text-align: right;">
                  <button type="submit" class="btn btn-primary">💾 Simpan Data Lembaga</button>
                </div>
              </form>
            </div>
          </div>

          <!-- Manajemen 3 Unit RTQ -->
          <div class="card">
            <div class="card-header">
              <div class="card-title">
                <i>🕌</i> <span>Manajemen Unit RTQ (3 RTQ Utsmaniyyah)</span>
              </div>
              <button class="btn btn-gold btn-sm" onclick="openAddRtqModal()">➕ Tambah Unit RTQ</button>
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

          <!-- Manajemen Periode / Tahun Ujian -->
          <div class="card">
            <div class="card-header">
              <div class="card-title">
                <i>📅</i> <span>Manajemen Periode / Tahun Pelajaran Ujian</span>
              </div>
              <button class="btn btn-gold btn-sm" onclick="openAddPeriodeModal()">➕ Tambah Tahun / Periode</button>
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

      </main>
    </div>
  </div>

  <!-- MODAL: TAMBAH / EDIT SANTRI -->
  <div class="modal-backdrop" id="santriModal">
    <div class="modal-container">
      <div class="modal-header">
        <h3 class="modal-title" id="santriModalTitle">Tambah Santri Baru</h3>
        <button class="modal-close" onclick="closeModal('santriModal')">✕</button>
      </div>
      <form id="santriForm">
        <div class="modal-body">
          <input type="hidden" id="santriIdInput">
          <div class="form-grid">
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">Nama Lengkap Santri *</label>
              <input type="text" id="inputNama" class="form-control" placeholder="Contoh: Ahmad Alhabib Ramadhan" required>
            </div>
            <div class="form-group">
              <label class="form-label">Jenis Kelamin *</label>
              <select id="inputGender" class="form-control" required>
                <option value="L">Laki-laki (Bin)</option>
                <option value="P">Perempuan (Binti)</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Asal Unit RTQ *</label>
              <select id="inputRtq" class="form-control" required></select>
            </div>
            <div class="form-group">
              <label class="form-label">Tahun Pelajaran *</label>
              <select id="inputPeriode" class="form-control" required></select>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">Tempat, Tanggal Lahir</label>
              <input type="text" id="inputTtl" class="form-control" placeholder="Contoh: Tambak Jaya, 12 Agustus 2012">
            </div>
            <div class="form-group">
              <label class="form-label">Nama Ayah Kandung</label>
              <input type="text" id="inputAyah" class="form-control" placeholder="Nama ayah">
            </div>
            <div class="form-group">
              <label class="form-label">Nama Ibu Kandung</label>
              <input type="text" id="inputIbu" class="form-control" placeholder="Nama ibu">
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">Alamat Lengkap</label>
              <input type="text" id="inputAlamat" class="form-control" placeholder="Dusun, Desa, Kecamatan, Kabupaten">
            </div>
            <div class="form-group">
              <label class="form-label">Nomor Induk Santri</label>
              <input type="text" id="inputNoInduk" class="form-control" placeholder="0001">
            </div>
            <div class="form-group">
              <label class="form-label">Nomor Ujian</label>
              <input type="text" id="inputNoUjian" class="form-control" placeholder="0001">
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">Nomor Syahadah</label>
              <input type="text" id="inputNoSyahadah" class="form-control" placeholder="LMY-LB/RTQ-U3/001/09/10/2025">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeModal('santriModal')">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan ke Database</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: TAMBAH / EDIT UNIT RTQ -->
  <div class="modal-backdrop" id="rtqModal">
    <div class="modal-container">
      <div class="modal-header">
        <h3 class="modal-title" id="rtqModalTitle">Tambah Unit RTQ</h3>
        <button class="modal-close" onclick="closeModal('rtqModal')">✕</button>
      </div>
      <form id="rtqForm">
        <div class="modal-body">
          <input type="hidden" id="rtqIdInput">
          <div class="form-grid">
            <div class="form-group">
              <label class="form-label">Kode Singkat RTQ *</label>
              <input type="text" id="inputRtqKode" class="form-control" placeholder="Contoh: RTQ-U1" required>
            </div>
            <div class="form-group">
              <label class="form-label">Nama Lengkap Unit RTQ *</label>
              <input type="text" id="inputRtqNamaModal" class="form-control" placeholder="Contoh: RTQ Utsmaniyyah I" required>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">Alamat Lengkap Unit RTQ *</label>
              <input type="text" id="inputRtqAlamatModal" class="form-control" placeholder="Alamat dusun/desa, kecamatan" required>
            </div>
            <div class="form-group">
              <label class="form-label">Nama Kepala RTQ *</label>
              <input type="text" id="inputRtqKepalaModal" class="form-control" placeholder="Nama Kepala RTQ" required>
            </div>
            <div class="form-group">
              <label class="form-label">Nama Ketua Tim Penguji *</label>
              <input type="text" id="inputRtqPengujiModal" class="form-control" placeholder="Nama Ketua Tim Penguji" required>
            </div>
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">No. WhatsApp RTQ</label>
              <input type="text" id="inputRtqWaModal" class="form-control" placeholder="08xxxxxxxxxx">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeModal('rtqModal')">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Unit RTQ</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: TAMBAH / EDIT PERIODE UJIAN -->
  <div class="modal-backdrop" id="periodeModal">
    <div class="modal-container">
      <div class="modal-header">
        <h3 class="modal-title" id="periodeModalTitle">Tambah Tahun / Periode Ujian</h3>
        <button class="modal-close" onclick="closeModal('periodeModal')">✕</button>
      </div>
      <form id="periodeForm">
        <div class="modal-body">
          <input type="hidden" id="periodeIdInput">
          <div class="form-grid">
            <div class="form-group" style="grid-column: 1 / -1;">
              <label class="form-label">Tahun Pelajaran *</label>
              <input type="text" id="inputPeriodeTahun" class="form-control" placeholder="Contoh: 2025 M / 1447 H" required>
            </div>
            <div class="form-group">
              <label class="form-label">Tanggal Ujian (Hijriyah)</label>
              <input type="text" id="inputPeriodeH" class="form-control" placeholder="Contoh: 14 Rabi'ul Awwal 1447 H">
            </div>
            <div class="form-group">
              <label class="form-label">Tanggal Ujian (Masehi)</label>
              <input type="text" id="inputPeriodeM" class="form-control" placeholder="Contoh: 07 September 2025 M">
            </div>
            <div class="form-group">
              <label class="form-label">Tanggal Terbit Syahadah</label>
              <input type="text" id="inputPeriodeSyahadah" class="form-control" placeholder="Contoh: 21 September 2025">
            </div>
            <div class="form-group">
              <label class="form-label">Titimangsa Transkrip Nilai</label>
              <input type="text" id="inputPeriodeTitimangsa" class="form-control" placeholder="Contoh: Tambak Jaya, 07 September 2025">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline" onclick="closeModal('periodeModal')">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Periode</button>
        </div>
      </form>
    </div>
  </div>

  <!-- MODAL: GANTI PENGURUS / ROLE LOGIN -->
  <div class="modal-backdrop" id="roleModal">
    <div class="modal-container" style="max-width: 520px;">
      <div class="modal-header">
        <h3 class="modal-title">Pilih Profil Pengurus / Hak Akses</h3>
        <button class="modal-close" onclick="closeModal('roleModal')">✕</button>
      </div>
      <div class="modal-body">
        <p style="color: var(--dark-600); font-size: 0.9rem; margin-bottom: 16px;">
          Pilih unit RTQ yang sedang Anda kelola. Data santri, penilaian munaqosyah, dan pejabat penandatangan syahadah akan otomatis terkunci sesuai RTQ yang Anda pilih:
        </p>
        <div style="display: flex; flex-direction: column; gap: 10px;" id="roleOptionList"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('roleModal')">Tutup</button>
      </div>
    </div>
  </div>

  <!-- Main JavaScript Logic -->
  <script src="assets/js/app.js"></script>
</body>
</html>

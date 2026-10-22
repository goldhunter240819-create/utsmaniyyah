<?php
session_start();
require_once __DIR__ . '/config/database.php';
$isLoggedIn = isset($_SESSION['current_user']);
$currentUser = $_SESSION['current_user'] ?? null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistem Penilaian Munaqosyah & Cetak Syahadah - RTQ Utsmaniyyah</title>
  <meta name="description" content="Aplikasi Web Sistem Informasi Penilaian Munaqosyah dan Cetak Syahadah RTQ Utsmaniyyah Metode Yanbu'a Lampung Barat.">
  
  <!-- Favicon Logo Yanbua PNG Asli Transparan -->
  <link rel="icon" type="image/png" href="assets/images/logo-yanbua.png">

  <!-- Google Fonts: Plus Jakarta Sans & Amiri -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Cinzel:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <!-- SheetJS for Excel (.xlsx) Export -->
  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

  <!-- SweetAlert2 Luxury Alert System -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Main Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* Styling Khusus Layar Login */
    .login-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #072a20 0%, #0d4432 50%, #146c43 100%);
      padding: 20px;
    }
    .login-card {
      background: #ffffff;
      border-radius: var(--radius-xl);
      box-shadow: 0 20px 40px rgba(0,0,0,0.3);
      width: 100%;
      max-width: 460px;
      padding: 36px 32px;
      text-align: center;
      position: relative;
    }
    .login-logo {
      width: 85px;
      height: 85px;
      margin: 0 auto 14px;
      background: #f0f7f4;
      border-radius: 50%;
      padding: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 6px 15px rgba(13,68,50,0.15);
    }
    .login-logo img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    .login-title {
      font-size: 1.35rem;
      font-weight: 800;
      color: var(--primary-900);
      margin-bottom: 4px;
    }
    .login-sub {
      font-size: 0.85rem;
      color: var(--dark-500);
      margin-bottom: 24px;
    }
    .quick-account-box {
      margin-top: 24px;
      padding: 16px;
      background: #f8faf9;
      border-radius: var(--radius-md);
      border: 1px dashed var(--gray-300);
      text-align: left;
      font-size: 0.8rem;
    }
    .quick-account-title {
      font-weight: 700;
      color: var(--primary-800);
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .account-badge-btn {
      display: inline-block;
      margin: 3px 2px;
      padding: 4px 8px;
      background: #fff;
      border: 1px solid var(--gray-300);
      border-radius: var(--radius-sm);
      cursor: pointer;
      font-weight: 600;
      font-size: 0.75rem;
      color: var(--dark-700);
      transition: all 0.2s;
    }
    .account-badge-btn:hover {
      background: var(--primary-100);
      border-color: var(--primary-600);
      color: var(--primary-900);
    }
  </style>
</head>
<body>

  <!-- ============================================== -->
  <!-- LAYAR LOGIN (Tampil jika belum login) -->
  <!-- ============================================== -->
  <div id="loginScreen" class="login-wrapper" style="<?= $isLoggedIn ? 'display: none;' : '' ?>">
    <div class="login-card">
      <div class="login-logo">
        <img src="assets/images/logo-yanbua.png" alt="Logo Yanbu'a">
      </div>
      <div class="login-brand-tag">PORTAL RESMI PENGURUS</div>
      <h2 class="login-title">SISTEM MUNAQOSYAH</h2>
      <p class="login-sub">Lajnah Muroqobah Yanbu'a Kabupaten Lampung Barat</p>

      <form id="loginForm">
        <div class="form-group" style="text-align: left;">
          <label class="form-label">Username Pengurus</label>
          <div class="input-with-icon">
            <input type="text" id="loginUsername" class="form-control" placeholder="Masukkan username pengurus" required autocomplete="username">
          </div>
        </div>
        <div class="form-group" style="text-align: left;">
          <label class="form-label">Password</label>
          <div class="input-with-icon">
            <input type="password" id="loginPassword" class="form-control" placeholder="Masukkan kata sandi" required autocomplete="current-password">
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-login-submit" style="width: 100%; padding: 13px; margin-top: 10px; font-weight: 700;">
          <span>Masuk ke Sistem RTQ</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </button>
      </form>

      <div class="login-footer-security">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        <span>Sistem Akses Terisolasi & Terenkripsi Aman</span>
      </div>
    </div>
  </div>

  <!-- ============================================== -->
  <!-- APLIKASI UTAMA (Tampil setelah login) -->
  <!-- ============================================== -->
  <div class="app-wrapper" id="mainApp" style="<?= $isLoggedIn ? '' : 'display: none;' ?>">
    <!-- Sidebar Navigation -->
    <aside class="sidebar" id="sidebar">
      <div class="sidebar-header">
        <div class="sidebar-logo">
          <img src="assets/images/logo-yanbua.png" alt="Logo Yanbu'a">
        </div>
        <div class="sidebar-brand">
          <h2>MUNAQOSYAH</h2>
          <div class="sidebar-brand-badge" id="sidebarActiveRtqLabel">Yanbu'a Lampung Barat</div>
        </div>
      </div>

      <!-- Info Login Pengurus (Luxury Glass Card) -->
      <div class="sidebar-user-card">
        <div class="user-card-avatar">
          <span id="userAvatarChar">M</span>
        </div>
        <div class="user-card-info">
          <div class="user-card-name" id="currentUserBadge"><?= htmlspecialchars($currentUser['nama'] ?? 'Pengurus RTQ') ?></div>
          <div class="user-card-status">
            <span class="status-dot"></span>
            <span class="status-text">Terhubung Aman</span>
          </div>
        </div>
        <button class="user-logout-btn" onclick="handleLogout()" title="Keluar dari Sistem">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
        </button>
      </div>

      <ul class="sidebar-menu">
        <li class="menu-category">Menu Utama</li>
        <li class="nav-item">
          <a class="nav-link active" data-tab="tab-dashboard">
            <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
            <span>Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-tab="tab-santri">
            <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            <span>Data Santri</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-tab="tab-penilaian">
            <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <span>Penilaian Ujian</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" data-tab="tab-rekap">
            <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
            <span>Rekap & Ranking</span>
          </a>
        </li>

        <li class="menu-category">Dokumen & Cetak</li>
        <li class="nav-item">
          <a class="nav-link" data-tab="tab-cetak">
            <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
            <span>Cetak Syahadah</span>
          </a>
        </li>

        <li class="menu-category">Konfigurasi</li>
        <li class="nav-item">
          <a class="nav-link" data-tab="tab-pengaturan">
            <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            <span>Pengaturan Sistem</span>
          </a>
        </li>
      </ul>

      <div class="sidebar-footer">
        <div class="footer-title">Metode Yanbu'a Lampung Barat</div>
        <div class="footer-sub">Multi-RTQ Multi-Pengurus Terisolasi</div>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">
      <!-- Topbar Header -->
      <header class="topbar">
        <div class="topbar-title">
          <button class="menu-toggle" id="menuToggle" aria-label="Buka Menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
          </button>
          <div>
            <h1 id="topbarPageTitle">Dashboard Munaqosyah</h1>
          </div>
        </div>
        <div class="topbar-actions">
          <div class="topbar-filters">
            <!-- Dropdown RTQ: Hanya aktif untuk Developer Maul, terkunci untuk Admin RTQ -->
            <select id="topbarRtqFilter" class="topbar-select" title="Pilih Unit RTQ">
              <option value="ALL">Semua Unit RTQ (Gabungan)</option>
            </select>
            <select id="topbarPeriodeFilter" class="topbar-select" title="Pilih Periode / Tahun Ujian">
            </select>
          </div>
          <button class="btn btn-outline btn-sm" onclick="exportRekapExcel()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <span>Ekspor Excel</span>
          </button>
          <button class="btn btn-primary btn-sm" onclick="openAddSantriModal()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>Tambah Santri</span>
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
              <p id="heroSubtitle">Sistem Pengolahan Nilai Munaqosyah, Evaluasi Ujian Akhir, dan Pencetakan Syahadah Resmi.</p>
              <div class="hero-badge">
                <span id="heroRtqBadge">📍 Unit: RTQ Utsmaniyyah</span>
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
                    <span>Bingkai Ornamen Hijau</span>
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

          <!-- Manajemen 3 Unit RTQ -->
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

  <!-- Main JavaScript Logic -->
  <script src="assets/js/app.js"></script>
</body>
</html>

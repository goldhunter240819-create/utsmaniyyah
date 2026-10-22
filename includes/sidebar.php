<?php
/**
 * Shared Sidebar Navigation
 */
$currentUser = get_current_user_data();
$userInitial = !empty($currentUser['nama']) ? strtoupper(substr($currentUser['nama'], 0, 1)) : 'U';
$rtqName = !empty($currentUser['rtq_nama']) ? $currentUser['rtq_nama'] : 'Pusat / Semua RTQ';
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo">
      <img src="assets/images/logo-yanbua.png" alt="Logo Yanbu'a">
    </div>
    <div class="sidebar-brand">
      <h2>MUNAQOSYAH</h2>
      <div class="sidebar-brand-badge" id="sidebarActiveRtqLabel"><?= htmlspecialchars($rtqName) ?></div>
    </div>
  </div>

  <div class="sidebar-user-card user-card" id="userCard">
    <div class="user-card-avatar">
      <span id="userAvatarChar"><?= htmlspecialchars($userInitial) ?></span>
    </div>
    <div class="user-card-info">
      <div class="user-card-name" id="currentUserBadge" title="<?= htmlspecialchars($currentUser['nama'] ?? 'Pengurus RTQ') ?>">
        <?= htmlspecialchars($currentUser['nama'] ?? 'Pengurus RTQ') ?>
      </div>
      <div class="user-card-status">
        <span class="status-dot"></span>
        <span class="status-text"><?= htmlspecialchars($currentUser['role'] ?? 'PENGURUS') ?></span>
      </div>
    </div>
    <a href="logout.php" class="user-logout-btn" title="Keluar dari Sistem">
      <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
    </a>
  </div>

  <ul class="sidebar-menu">
    <li class="menu-category">Menu Utama</li>
    <li class="nav-item">
      <a href="dashboard.php" class="nav-link <?= ($currentPage === 'dashboard') ? 'active' : '' ?>">
        <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
        <span>Dashboard</span>
      </a>
    </li>
    <li class="nav-item">
      <a href="santri.php" class="nav-link <?= ($currentPage === 'santri') ? 'active' : '' ?>">
        <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        <span>Data Santri</span>
      </a>
    </li>
    <li class="nav-item">
      <a href="nilai.php" class="nav-link <?= ($currentPage === 'nilai') ? 'active' : '' ?>">
        <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        <span>Penilaian Ujian</span>
      </a>
    </li>
    <li class="nav-item">
      <a href="rekap.php" class="nav-link <?= ($currentPage === 'rekap') ? 'active' : '' ?>">
        <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="7"></circle><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"></polyline></svg>
        <span>Rekap & Ranking</span>
      </a>
    </li>

    <li class="menu-category">Dokumen & Cetak</li>
    <li class="nav-item">
      <a href="cetak.php" class="nav-link <?= ($currentPage === 'cetak') ? 'active' : '' ?>">
        <svg class="nav-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        <span>Cetak Syahadah</span>
      </a>
    </li>

    <li class="menu-category">Konfigurasi</li>
    <li class="nav-item">
      <a href="pengaturan.php" class="nav-link <?= ($currentPage === 'pengaturan') ? 'active' : '' ?>">
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

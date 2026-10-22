<?php
/**
 * Shared Application Header
 */
require_once __DIR__ . '/auth.php';
require_login();

$currentUser = get_current_user_data();
$pageTitle = $pageTitle ?? 'Sistem Munaqosyah RTQ';
$currentPage = $currentPage ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> - RTQ Utsmaniyyah</title>
  <meta name="description" content="Sistem Informasi Penilaian Munaqosyah dan Cetak Syahadah RTQ Utsmaniyyah Metode Yanbu'a Lampung Barat.">
  
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  
  <link rel="icon" type="image/png" href="assets/images/logo-yanbua.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Katibeh&family=Aref+Ruqaa:wght@400;700&family=Amiri+Quran&family=Amiri:ital,wght@0,400;0,700;1,400;1,700&family=Cinzel:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
</head>
<body data-page="<?= htmlspecialchars($currentPage) ?>">

  <div class="app-wrapper" id="mainApp">
    <!-- Sidebar Navigation -->
    <?php require_once __DIR__ . '/sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="main-content">
      <!-- Topbar Header -->
      <header class="topbar">
        <div class="topbar-title">
          <button class="menu-toggle" id="menuToggle" aria-label="Buka Menu">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
          </button>
          <div>
            <h1 id="topbarPageTitle"><?= htmlspecialchars($pageTitle) ?></h1>
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

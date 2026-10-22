<?php
/**
 * Dashboard Page
 */
require_once __DIR__ . '/includes/auth.php';
require_login();

$pageTitle = 'Dashboard Munaqosyah';
$currentPage = 'dashboard';

require_once __DIR__ . '/includes/header.php';
?>

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
      <a href="rekap.php" class="btn btn-outline btn-sm">Lihat Semua Rangking →</a>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>

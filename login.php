<?php
/**
 * Login Page
 * Sistem Penilaian Munaqosyah & Cetak Syahadah RTQ Utsmaniyyah
 */

require_once __DIR__ . '/includes/auth.php';

// Jika sudah login, langsung lempar ke dashboard.php
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

$errorMsg = '';

// Proses form login jika dikirim secara direct POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $errorMsg = 'Username dan password wajib diisi.';
    } else {
        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.password, u.nama, u.role, u.rtq_id, r.nama AS rtq_nama, r.kode AS rtq_kode
            FROM `users` u
            LEFT JOIN `rtq` r ON u.rtq_id = r.id
            WHERE u.username = ?
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && ($user['password'] === $password || password_verify($password, $user['password']))) {
            unset($user['password']);
            $_SESSION['current_user'] = $user;
            header('Location: dashboard.php');
            exit;
        } else {
            $errorMsg = 'Username atau password salah. Silakan coba lagi.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Pengurus - Sistem Munaqosyah RTQ Utsmaniyyah</title>
  
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  
  <link rel="icon" type="image/png" href="assets/images/logo-yanbua.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
  
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  
  <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
  <style>
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
      max-width: 440px;
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
    .error-alert {
      background: #fee2e2;
      border: 1px solid #f87171;
      color: #991b1b;
      padding: 10px 14px;
      border-radius: var(--radius-sm);
      font-size: 0.85rem;
      margin-bottom: 18px;
      text-align: left;
    }
    .quick-account-box {
      margin-top: 24px;
      padding: 14px;
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
      padding: 5px 9px;
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

  <div class="login-wrapper">
    <div class="login-card">
      <div class="login-logo">
        <img src="assets/images/logo-yanbua.png" alt="Logo Yanbu'a">
      </div>
      <div class="login-brand-tag">PORTAL RESMI PENGURUS</div>
      <h2 class="login-title">SISTEM MUNAQOSYAH</h2>
      <p class="login-sub">Lajnah Muroqobah Yanbu'a Kabupaten Lampung Barat</p>

      <?php if (!empty($errorMsg)): ?>
        <div class="error-alert">
          ⚠️ <?= htmlspecialchars($errorMsg) ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php" id="loginForm">
        <div class="form-group" style="text-align: left;">
          <label class="form-label" for="loginUsername">Username Pengurus</label>
          <div class="input-with-icon">
            <input type="text" id="loginUsername" name="username" class="form-control" placeholder="Masukkan username pengurus" required autocomplete="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>
        </div>
        <div class="form-group" style="text-align: left;">
          <label class="form-label" for="loginPassword">Password</label>
          <div class="input-with-icon">
            <input type="password" id="loginPassword" name="password" class="form-control" placeholder="Masukkan kata sandi" required autocomplete="current-password">
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-login-submit" style="width: 100%; padding: 13px; margin-top: 10px; font-weight: 700;">
          <span>Masuk ke Sistem RTQ</span>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
        </button>
      </form>

      <div class="quick-account-box">
        <div class="quick-account-title">
          <span>🔑 Klik untuk Isi Cepat Akun:</span>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 4px;">
          <button type="button" class="account-badge-btn" onclick="fillLogin('Maul', 'Basenglah123.')">👑 Developer (Maul)</button>
          <button type="button" class="account-badge-btn" onclick="fillLogin('Utsmaniyyah1', 'Utsmaniyyah1')">🏫 Admin RTQ 1</button>
          <button type="button" class="account-badge-btn" onclick="fillLogin('Utsmaniyyah2', 'Utsmaniyyah2')">🏫 Admin RTQ 2</button>
          <button type="button" class="account-badge-btn" onclick="fillLogin('Utsmaniyyah3', 'Utsmaniyyah3')">🏫 Admin RTQ 3</button>
        </div>
      </div>

      <div class="login-footer-security">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
        <span>Sistem Akses Terisolasi & Terenkripsi Aman</span>
      </div>
    </div>
  </div>

  <script>
    function fillLogin(user, pass) {
      document.getElementById('loginUsername').value = user;
      document.getElementById('loginPassword').value = pass;
    }
  </script>
</body>
</html>

<?php
require_once __DIR__ . '/../config.php';

if (isLoggedIn()) {
    header('Location: /ulinkaciamis/admin/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE username = ? AND aktif = 1 LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['admin_id']      = $user['id'];
            $_SESSION['admin_nama']    = $user['nama'];
            $_SESSION['admin_role']    = $user['role'];
            $_SESSION['admin_wilayah'] = $user['wilayah'];
            header('Location: /ulinkaciamis/admin/dashboard.php');
            exit;
        } else {
            $error = 'Username atau password tidak sesuai. Silakan periksa kembali.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk - Portal Wilayah UlinKa Ciamis</title>
  <link rel="icon" type="image/png" href="/ulinkaciamis/assets/images/lambang_ciamis.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>

  <style>
    *, *::before, *::after {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      min-height: 100vh;
      background: radial-gradient(circle at 20% 20%, #2e0327 0%, #150212 50%, #0a0109 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px 16px;
      color: #0f172a;
      position: relative;
      overflow-x: hidden;
    }

    /* Ambient Glow Accents */
    .glow-orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(140px);
      pointer-events: none;
      opacity: 0.28;
    }
    .glow-1 {
      width: 500px;
      height: 500px;
      background: #9e0b84;
      top: -100px;
      left: -100px;
    }
    .glow-2 {
      width: 450px;
      height: 450px;
      background: #6b21a8;
      bottom: -80px;
      right: -80px;
    }

    /* Main Container (Split Screen) */
    .auth-container {
      position: relative;
      z-index: 10;
      display: flex;
      align-items: stretch;
      width: 100%;
      max-width: 980px;
      min-height: 580px;
      background: #ffffff;
      border-radius: 24px;
      box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.08);
      overflow: hidden;
    }

    /* LEFT: Brand Panel with Photo & Purple Gradient */
    .brand-panel {
      width: 440px;
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 55px 44px;
      text-align: center;
      color: #ffffff;
      flex-shrink: 0;
      background-image: 
        linear-gradient(145deg, rgba(74, 5, 63, 0.94) 0%, rgba(133, 10, 112, 0.90) 50%, rgba(158, 11, 132, 0.88) 100%),
        url('/ulinkaciamis/assets/images/alun_alun_ciamis.jpg');
      background-size: cover;
      background-position: center;
      overflow: hidden;
    }

    .brand-panel::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 50% 30%, rgba(208, 0, 255, 0.25) 0%, transparent 70%);
      pointer-events: none;
    }

    .brand-content {
      position: relative;
      z-index: 2;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .shield-badge {
      width: 96px;
      height: 96px;
      border-radius: 50%;
      background: #ffffff;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 12px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
      margin-bottom: 22px;
      border: 3px solid rgba(250, 204, 21, 0.85); /* gold border */
      transition: transform 0.3s ease;
    }
    .shield-badge:hover {
      transform: scale(1.06) rotate(2deg);
    }
    .shield-badge img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }

    .brand-title {
      font-size: 24px;
      font-weight: 800;
      color: #ffffff;
      letter-spacing: -0.5px;
      line-height: 1.25;
      margin-bottom: 6px;
    }

    .brand-tagline {
      font-size: 13px;
      font-weight: 700;
      color: #fde047; /* gold */
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-bottom: 12px;
    }

    .gold-divider {
      width: 54px;
      height: 3px;
      background: #facc15;
      border-radius: 2px;
      margin: 10px 0 16px;
    }

    .brand-desc {
      font-size: 13px;
      color: rgba(255, 255, 255, 0.88);
      line-height: 1.6;
      max-width: 320px;
      margin-bottom: 24px;
    }

    .feature-pills {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      gap: 8px;
    }

    .pill-item {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 11px;
      font-weight: 600;
      background: rgba(255, 255, 255, 0.16);
      backdrop-filter: blur(8px);
      padding: 6px 12px;
      border-radius: 9999px;
      border: 1px solid rgba(255, 255, 255, 0.22);
      color: #ffffff;
    }

    /* RIGHT: Form Panel */
    .form-panel {
      flex: 1;
      padding: 50px 48px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      background: #ffffff;
    }

    .form-header {
      margin-bottom: 26px;
    }

    .logo-container {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 16px;
    }
    .logo-container img {
      height: 28px;
      width: auto;
      object-fit: contain;
    }
    .header-badge {
      font-size: 10px;
      font-weight: 800;
      color: #9e0b84;
      background: #fdf2fa;
      border: 1px solid rgba(158, 11, 132, 0.2);
      padding: 3px 8px;
      border-radius: 6px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .form-title {
      font-size: 22px;
      font-weight: 800;
      color: #0f172a;
      letter-spacing: -0.4px;
      margin-bottom: 4px;
    }
    .form-subtitle {
      font-size: 13px;
      color: #64748b;
    }

    /* Alert */
    .alert-box {
      display: flex;
      align-items: center;
      gap: 10px;
      background: #fef2f2;
      border: 1px solid #fecaca;
      color: #dc2626;
      padding: 12px 14px;
      border-radius: 12px;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 20px;
    }

    /* Form Fields */
    .form-group {
      margin-bottom: 18px;
    }
    .form-label {
      display: block;
      font-size: 12px;
      font-weight: 700;
      color: #334155;
      margin-bottom: 6px;
      text-transform: uppercase;
      letter-spacing: 0.4px;
    }
    .form-label .req {
      color: #ef4444;
    }

    .input-wrapper {
      position: relative;
      display: flex;
      align-items: center;
    }
    .input-icon {
      position: absolute;
      left: 14px;
      color: #94a3b8;
      font-size: 14px;
      pointer-events: none;
      transition: color 0.2s ease;
    }
    .form-input {
      width: 100%;
      height: 46px;
      padding: 10px 14px 10px 42px;
      background: #f8fafc;
      border: 1.5px solid #e2e8f0;
      border-radius: 12px;
      font-family: inherit;
      font-size: 14px;
      color: #0f172a;
      outline: none;
      transition: all 0.2s ease;
    }
    .form-input:focus {
      background: #ffffff;
      border-color: #9e0b84;
      box-shadow: 0 0 0 4px rgba(158, 11, 132, 0.12);
    }
    .form-input:focus + .input-icon,
    .input-wrapper:focus-within .input-icon {
      color: #9e0b84;
    }

    .toggle-pwd-btn {
      position: absolute;
      right: 12px;
      background: none;
      border: none;
      color: #94a3b8;
      cursor: pointer;
      font-size: 14px;
      padding: 6px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      transition: color 0.2s ease;
    }
    .toggle-pwd-btn:hover {
      color: #9e0b84;
    }

    /* Submit Button */
    .btn-submit {
      width: 100%;
      height: 48px;
      background: linear-gradient(135deg, #9e0b84 0%, #850a70 100%);
      color: #ffffff;
      border: none;
      border-radius: 12px;
      font-family: inherit;
      font-size: 14px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      cursor: pointer;
      box-shadow: 0 4px 15px rgba(158, 11, 132, 0.35);
      transition: all 0.25s ease;
      margin-top: 6px;
    }
    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(158, 11, 132, 0.45);
      background: linear-gradient(135deg, #b21497 0%, #9e0b84 100%);
    }
    .btn-submit:active {
      transform: translateY(0);
    }

    /* Quick Fill Demo Bar */
    .demo-section {
      margin-top: 22px;
      padding-top: 18px;
      border-top: 1px solid #f1f5f9;
    }
    .demo-header {
      font-size: 11px;
      font-weight: 700;
      color: #64748b;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .demo-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
    }
    .demo-chip {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      color: #334155;
      font-size: 11px;
      font-weight: 600;
      padding: 5px 10px;
      border-radius: 8px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      transition: all 0.2s ease;
    }
    .demo-chip:hover {
      background: #fdf2fa;
      border-color: #9e0b84;
      color: #9e0b84;
      transform: translateY(-1px);
    }
    .demo-chip.primary {
      background: #fdf2fa;
      border-color: rgba(158, 11, 132, 0.3);
      color: #9e0b84;
    }

    /* Back Link */
    .back-nav {
      margin-top: 16px;
      text-align: center;
    }
    .back-nav a {
      font-size: 12px;
      font-weight: 600;
      color: #64748b;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: color 0.2s ease;
    }
    .back-nav a:hover {
      color: #9e0b84;
    }

    /* Responsive */
    @media (max-width: 880px) {
      .auth-container {
        flex-direction: column;
        max-width: 460px;
      }
      .brand-panel {
        width: 100%;
        padding: 40px 24px;
      }
      .brand-desc {
        display: none;
      }
      .form-panel {
        padding: 36px 28px;
      }
    }
  </style>
</head>
<body>

  <!-- Background Orbs -->
  <div class="glow-orb glow-1"></div>
  <div class="glow-orb glow-2"></div>

  <div class="auth-container">
    
    <!-- ================= LEFT: BRAND HERO PANEL ================= -->
    <div class="brand-panel">
      <div class="brand-content">
        
        <!-- Official Ciamis Shield Logo -->
        <div class="shield-badge">
          <img src="/ulinkaciamis/assets/images/lambang_ciamis.png" alt="Lambang Kabupaten Ciamis">
        </div>

        <h1 class="brand-title">Pemerintah Kabupaten Ciamis</h1>
        <div class="brand-tagline">Mahayunan Ayuna Kadatuan</div>

        <div class="gold-divider"></div>

        <p class="brand-desc">
          Portal Administrasi Terpadu Direktori Pemetaan & Fasilitas Publik Tatar Galuh Ciamis. Dikelola oleh Superadmin & Perangkat Wilayah Desa/Kecamatan.
        </p>

        <div class="feature-pills">
          <span class="pill-item"><i class="fa-solid fa-map-location-dot"></i> 27 Kecamatan</span>
          <span class="pill-item"><i class="fa-solid fa-layer-group"></i> Pemetaan Mandiri</span>
          <span class="pill-item"><i class="fa-solid fa-shield-halved"></i> Akses Perangkat</span>
        </div>
      </div>
    </div>

    <!-- ================= RIGHT: LOGIN FORM PANEL ================= -->
    <div class="form-panel">
      
      <div class="form-header">
        <div class="logo-container">
          <img src="/ulinkaciamis/public/images/ulinkaCiamis_cropped.webp" alt="UlinKa Ciamis">
          <span class="header-badge">Admin Panel</span>
        </div>
        <h2 class="form-title">Masuk ke Sistem</h2>
        <p class="form-subtitle">Silakan masukkan username dan password pengurus Anda</p>
      </div>

      <?php if (!empty($error)): ?>
        <div class="alert-box">
          <i class="fa-solid fa-circle-exclamation"></i>
          <span><?= htmlspecialchars($error) ?></span>
        </div>
      <?php endif; ?>

      <form method="POST" action="" id="loginForm">
        
        <!-- Username -->
        <div class="form-group">
          <label class="form-label" for="username">Username <span class="req">*</span></label>
          <div class="input-wrapper">
            <i class="fa-solid fa-user input-icon"></i>
            <input 
              type="text" 
              id="username" 
              name="username" 
              class="form-input" 
              placeholder="Contoh: superadmin atau pengurus_panjalu" 
              value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" 
              required 
              autofocus 
              autocomplete="username"
            >
          </div>
        </div>

        <!-- Password -->
        <div class="form-group">
          <label class="form-label" for="password">Password <span class="req">*</span></label>
          <div class="input-wrapper">
            <i class="fa-solid fa-lock input-icon"></i>
            <input 
              type="password" 
              id="password" 
              name="password" 
              class="form-input" 
              placeholder="Masukkan password akun" 
              required 
              autocomplete="current-password"
            >
            <button type="button" class="toggle-pwd-btn" onclick="togglePasswordVisibility()" title="Lihat password">
              <i class="fa-regular fa-eye" id="eyeIcon"></i>
            </button>
          </div>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn-submit">
          <i class="fa-solid fa-arrow-right-to-bracket"></i>
          <span>Masuk ke Dashboard</span>
        </button>
      </form>

      <!-- Demo Accounts Quick Fill -->
      <div class="demo-section">
        <div class="demo-header">
          <span><i class="fa-solid fa-wand-magic-sparkles text-amber-500"></i> Klik untuk Isi Cepat Akun Demo:</span>
        </div>
        <div class="demo-grid">
          <button type="button" class="demo-chip primary" onclick="fillDemo('superadmin', 'password')">
            ⭐ Superadmin (Kabupaten)
          </button>
          <button type="button" class="demo-chip" onclick="fillDemo('pengurus_panjalu', 'password')">
            🏞️ Kec. Panjalu
          </button>
          <button type="button" class="demo-chip" onclick="fillDemo('pengurus_kawali', 'password')">
            📜 Kec. Kawali
          </button>
          <button type="button" class="demo-chip" onclick="fillDemo('pengurus_sadananya', 'password')">
            🌊 Kec. Sadananya
          </button>
          <button type="button" class="demo-chip" onclick="fillDemo('pengurus_ciamis', 'password')">
            🏛️ Kec. Ciamis
          </button>
        </div>
      </div>

      <!-- Back to Public Map -->
      <div class="back-nav">
        <a href="/ulinkaciamis/">
          <i class="fa-solid fa-arrow-left"></i> Kembali ke Peta Publik UlinKa Ciamis
        </a>
      </div>

    </div>
  </div>

  <script>
    function togglePasswordVisibility() {
      const pwdInput = document.getElementById('password');
      const eyeIcon = document.getElementById('eyeIcon');
      if (pwdInput.type === 'password') {
        pwdInput.type = 'text';
        eyeIcon.className = 'fa-regular fa-eye-slash';
      } else {
        pwdInput.type = 'password';
        eyeIcon.className = 'fa-regular fa-eye';
      }
    }

    function fillDemo(username, password) {
      const u = document.getElementById('username');
      const p = document.getElementById('password');
      u.value = username;
      p.value = password;
      u.focus();

      // Visual pulse feedback
      [u, p].forEach(el => {
        el.style.borderColor = '#9e0b84';
        el.style.backgroundColor = '#fdf2fa';
        setTimeout(() => {
          el.style.borderColor = '';
          el.style.backgroundColor = '';
        }, 500);
      });
    }
  </script>
</body>
</html>

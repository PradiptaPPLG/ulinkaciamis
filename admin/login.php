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
            $_SESSION['admin_id']   = $user['id'];
            $_SESSION['admin_nama'] = $user['nama'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['admin_wilayah'] = $user['wilayah'];
            header('Location: /ulinkaciamis/admin/dashboard.php');
            exit;
        } else {
            $error = 'Username atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Perangkat Desa - UlinKa Ciamis</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Plus Jakarta Sans',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#0a0f1e;position:relative;overflow:hidden}
.bg-glow{position:absolute;width:600px;height:600px;border-radius:50%;filter:blur(120px);opacity:.15;pointer-events:none}
.bg-glow-1{background:#10B981;top:-100px;left:-100px}
.bg-glow-2{background:#3B82F6;bottom:-100px;right:-100px}
.bg-glow-3{background:#8B5CF6;top:50%;left:50%;transform:translate(-50%,-50%)}
.card{background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);border-radius:24px;padding:48px 40px;width:100%;max-width:420px;position:relative;z-index:1;backdrop-filter:blur(20px)}
.logo{text-align:center;margin-bottom:32px}
.logo-icon{width:64px;height:64px;background:linear-gradient(135deg,#10B981,#3B82F6);border-radius:16px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;font-size:28px}
.logo h1{font-size:24px;font-weight:800;color:#fff;letter-spacing:-.5px}
.logo p{font-size:13px;color:rgba(255,255,255,.5);margin-top:4px}
.badge{display:inline-block;background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);color:#10B981;font-size:11px;font-weight:600;padding:4px 12px;border-radius:100px;margin-top:8px;letter-spacing:.5px;text-transform:uppercase}
.form-group{margin-bottom:20px}
label{display:block;font-size:13px;font-weight:600;color:rgba(255,255,255,.7);margin-bottom:8px}
input[type=text],input[type=password]{width:100%;padding:14px 16px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.12);border-radius:12px;color:#fff;font-family:inherit;font-size:14px;transition:.3s}
input[type=text]:focus,input[type=password]:focus{outline:none;border-color:#10B981;background:rgba(16,185,129,.08)}
input::placeholder{color:rgba(255,255,255,.25)}
.btn{width:100%;padding:14px;background:linear-gradient(135deg,#10B981,#0d9668);border:none;border-radius:12px;color:#fff;font-family:inherit;font-size:15px;font-weight:700;cursor:pointer;transition:.3s;letter-spacing:.3px}
.btn:hover{transform:translateY(-2px);box-shadow:0 8px 30px rgba(16,185,129,.4)}
.btn:active{transform:translateY(0)}
.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#FCA5A5;padding:12px 16px;border-radius:10px;font-size:13px;margin-bottom:20px}
.back-link{text-align:center;margin-top:24px}
.back-link a{color:rgba(255,255,255,.4);font-size:13px;text-decoration:none;transition:.3s}
.back-link a:hover{color:#10B981}
.divider{text-align:center;margin:20px 0;color:rgba(255,255,255,.2);font-size:12px}
.hint{text-align:center;font-size:12px;color:rgba(255,255,255,.3);margin-top:16px;line-height:1.6}
</style>
</head>
<body>
<div class="bg-glow bg-glow-1"></div>
<div class="bg-glow bg-glow-2"></div>
<div class="bg-glow bg-glow-3"></div>
<div class="card">
  <div class="logo">
    <div class="logo-icon">🗺️</div>
    <h1>UlinKa Ciamis</h1>
    <p>Portal Informasi Wilayah Ciamis</p>
    <span class="badge">Perangkat Desa</span>
  </div>
  <?php if($error): ?><div class="error">⚠️ <?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="POST" action="">
    <div class="form-group">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autocomplete="username">
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
    </div>
    <button type="submit" class="btn">🔐 Masuk ke Dashboard</button>
  </form>
  <div class="back-link">
    <a href="/ulinkaciamis/">← Kembali ke Peta Publik</a>
  </div>
  <div class="hint">
    <strong>Demo Akun:</strong><br>
    ⭐ Superadmin: <code>superadmin</code> / <code>password</code><br>
    🏘️ Pengurus Panjalu: <code>pengurus_panjalu</code> / <code>password</code><br>
    🏘️ Pengurus Kawali: <code>pengurus_kawali</code> / <code>password</code><br>
    🏘️ Pengurus Sadananya: <code>pengurus_sadananya</code> / <code>password</code>
  </div>
</div>
</body>
</html>

<?php
require_once __DIR__ . '/../config.php';
requireLogin();

$db = getDB();

// Identifikasi Peran & Wilayah
$user_role    = $_SESSION['admin_role'] ?? 'pengurus_wilayah';
$user_wilayah = $_SESSION['admin_wilayah'] ?? '';
$is_regional  = ($user_role !== 'superadmin' && !empty($user_wilayah) && $user_wilayah !== 'Seluruh Kabupaten Ciamis');

$safe_wilayah = $db->real_escape_string($user_wilayah);

// Query Statistik Sesuai Wewenang
if ($is_regional) {
    $total_aktif    = $db->query("SELECT COUNT(*) as c FROM destinasi WHERE status='aktif' AND kecamatan='$safe_wilayah'")->fetch_assoc()['c'];
    $total_nonaktif = $db->query("SELECT COUNT(*) as c FROM destinasi WHERE status='nonaktif' AND kecamatan='$safe_wilayah'")->fetch_assoc()['c'];
    $total_all      = $db->query("SELECT COUNT(*) as c FROM destinasi WHERE kecamatan='$safe_wilayah'")->fetch_assoc()['c'];
    $total_kategori = $db->query("SELECT COUNT(DISTINCT kategori_id) as c FROM destinasi WHERE kecamatan='$safe_wilayah'")->fetch_assoc()['c'];

    $recent = $db->query("
        SELECT d.*, k.nama AS kat 
        FROM destinasi d 
        JOIN kategori k ON d.kategori_id=k.id 
        WHERE d.kecamatan='$safe_wilayah'
        ORDER BY d.id DESC 
        LIMIT 8
    ")->fetch_all(MYSQLI_ASSOC);

    $by_kat = $db->query("
        SELECT k.nama, k.warna, COUNT(d.id) as total 
        FROM kategori k 
        LEFT JOIN destinasi d ON k.id=d.kategori_id AND d.status='aktif' AND d.kecamatan='$safe_wilayah'
        GROUP BY k.id 
        HAVING total > 0
        ORDER BY total DESC 
        LIMIT 8
    ")->fetch_all(MYSQLI_ASSOC);
} else {
    $total_aktif    = $db->query("SELECT COUNT(*) as c FROM destinasi WHERE status='aktif'")->fetch_assoc()['c'];
    $total_nonaktif = $db->query("SELECT COUNT(*) as c FROM destinasi WHERE status='nonaktif'")->fetch_assoc()['c'];
    $total_all      = $db->query("SELECT COUNT(*) as c FROM destinasi")->fetch_assoc()['c'];
    $total_kategori = $db->query("SELECT COUNT(*) as c FROM kategori")->fetch_assoc()['c'];

    $recent = $db->query("
        SELECT d.*, k.nama AS kat 
        FROM destinasi d 
        JOIN kategori k ON d.kategori_id=k.id 
        ORDER BY d.id DESC 
        LIMIT 8
    ")->fetch_all(MYSQLI_ASSOC);

    $by_kat = $db->query("
        SELECT k.nama, k.warna, COUNT(d.id) as total 
        FROM kategori k 
        LEFT JOIN destinasi d ON k.id=d.kategori_id AND d.status='aktif' 
        GROUP BY k.id 
        ORDER BY total DESC 
        LIMIT 8
    ")->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - UlinKa Ciamis</title>
<link rel="icon" type="image/png" href="/ulinkaciamis/assets/images/lambang_ciamis.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
<link rel="stylesheet" href="/ulinkaciamis/assets/css/admin.css">
</head>
<body>
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
  <?php include 'partials/topbar.php'; ?>
  <div class="admin-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Dashboard Administrator</h1>
        <p class="page-sub">
          Selamat datang kembali, <strong><?= htmlspecialchars($_SESSION['admin_user']['nama_lengkap'] ?? $_SESSION['admin_nama'] ?? 'Pengurus') ?></strong>.
          <?php if($is_regional): ?>
            Anda mengelola wilayah <strong>Kecamatan <?= htmlspecialchars($user_wilayah) ?></strong>.
          <?php else: ?>
            Anda mengelola seluruh <strong>27 Kecamatan</strong> di Kabupaten Ciamis.
          <?php endif; ?>
        </p>
      </div>
      <div style="display:flex;align-items:center;gap:10px;">
        <a href="/ulinkaciamis/admin/kelola.php" class="btn-secondary"><i class="fas fa-list-check"></i> Kelola Data</a>
        <a href="/ulinkaciamis/admin/tambah.php" class="btn-primary"><i class="fas fa-plus"></i> Tambah Destinasi</a>
      </div>
    </div>

    <!-- STATS CARDS -->
    <div class="stats-grid">
      <div class="stat-card stat-purple">
        <div class="stat-icon"><i class="fas fa-map-location-dot"></i></div>
        <div class="stat-info">
          <div class="stat-value"><?= $total_aktif ?></div>
          <div class="stat-label">Destinasi Aktif <?= $is_regional ? "(Kec. $user_wilayah)" : "Ciamis" ?></div>
        </div>
      </div>
      <div class="stat-card stat-emerald">
        <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
        <div class="stat-info">
          <div class="stat-value"><?= $total_all ?></div>
          <div class="stat-label">Total Terdaftar <?= $is_regional ? "(Wilayah Anda)" : "Se-Kabupaten" ?></div>
        </div>
      </div>
      <div class="stat-card stat-amber">
        <div class="stat-icon"><i class="fas fa-tags"></i></div>
        <div class="stat-info">
          <div class="stat-value"><?= $total_kategori ?></div>
          <div class="stat-label"><?= $is_regional ? "Kategori Aktif di Wilayah" : "Kategori Direktori" ?></div>
        </div>
      </div>
      <div class="stat-card stat-blue">
        <div class="stat-icon"><i class="fas fa-shield-halved"></i></div>
        <div class="stat-info">
          <div class="stat-value" style="font-size:18px;line-height:28px;">
            <?= $is_regional ? "Kec. " . htmlspecialchars($user_wilayah) : "27 Kec." ?>
          </div>
          <div class="stat-label">Cakupan Wilayah Tugas</div>
        </div>
      </div>
    </div>

    <!-- MAIN TWO-COLUMN DASHBOARD CONTENT -->
    <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;margin-top:24px;">

      <!-- RECENT TABLE -->
      <div class="table-card">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
          <h3 style="font-size:15px;font-weight:700;color:var(--text-main);margin:0;">
            <i class="fas fa-clock-rotate-left text-muted" style="margin-right:6px;"></i> 
            Destinasi &amp; Fasilitas Terbaru <?= $is_regional ? "(Kec. $user_wilayah)" : "" ?>
          </h3>
          <a href="/ulinkaciamis/admin/kelola.php" style="font-size:12.5px;color:var(--primary);font-weight:600;text-decoration:none;">Lihat Semua &rarr;</a>
        </div>

        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width:40px;">Foto</th>
                <th>Nama Tempat</th>
                <th>Kategori</th>
                <th>Status</th>
                <th style="text-align:right;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($recent)): ?>
                <tr><td colspan="5" style="text-align:center;padding:32px;color:var(--text-muted);">Belum ada data destinasi di wilayah ini.</td></tr>
              <?php endif; ?>
              <?php foreach($recent as $r): ?>
              <tr>
                <td>
                  <img src="<?= htmlspecialchars($r['foto'] ?: '/ulinkaciamis/assets/images/alun_alun_ciamis.jpg') ?>" 
                       alt="<?= htmlspecialchars($r['nama']) ?>" 
                       class="dest-preview-thumb" 
                       onerror="this.src='/ulinkaciamis/assets/images/alun_alun_ciamis.jpg'" />
                </td>
                <td>
                  <div style="font-weight:700;color:var(--text-main);font-size:13.5px;"><?= htmlspecialchars($r['nama']) ?></div>
                  <div style="font-size:11.5px;color:var(--text-muted);">Kec. <?= htmlspecialchars($r['kecamatan']) ?></div>
                </td>
                <td><span class="badge-cat"><?= htmlspecialchars($r['kat']) ?></span></td>
                <td>
                  <?php if($r['status']==='aktif'): ?>
                    <span class="badge-status active"><i class="fas fa-circle" style="font-size:7px;"></i> Aktif</span>
                  <?php else: ?>
                    <span class="badge-status inactive"><i class="fas fa-circle" style="font-size:7px;"></i> Nonaktif</span>
                  <?php endif; ?>
                </td>
                <td style="text-align:right;">
                  <a href="/ulinkaciamis/admin/edit.php?id=<?= $r['id'] ?>" class="tbl-btn edit" title="Edit"><i class="fas fa-pen-to-square"></i></a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- SIDE SUMMARY -->
      <div style="display:flex;flex-direction:column;gap:18px;">
        <div class="form-card" style="padding:20px;">
          <h3 style="font-size:14.5px;font-weight:700;color:var(--text-main);margin-bottom:14px;">
            <i class="fas fa-chart-pie" style="color:var(--primary);margin-right:6px;"></i> Distribusi Kategori
          </h3>
          <div style="display:flex;flex-direction:column;gap:10px;">
            <?php foreach($by_kat as $bk): ?>
            <div>
              <div style="display:flex;justify-content:space-between;font-size:12.5px;font-weight:600;margin-bottom:4px;">
                <span style="color:var(--text-body);"><?= htmlspecialchars($bk['nama']) ?></span>
                <span style="color:var(--text-muted);font-weight:700;"><?= $bk['total'] ?></span>
              </div>
              <div style="height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden;">
                <div style="height:100%;width:<?= min(100, ($bk['total'] / max(1, $total_aktif)) * 100) ?>%;background:<?= $bk['warna'] ?: 'var(--primary)' ?>;border-radius:3px;"></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- QUICK ACTION CARD -->
        <div class="form-card" style="padding:20px;background:#fdf2fa;border-color:rgba(158,11,132,0.2);">
          <div style="font-weight:800;font-size:14px;color:#9e0b84;margin-bottom:6px;">
            <i class="fas fa-map-pin"></i> Petunjuk Petugas Wilayah
          </div>
          <p style="font-size:12px;color:#475569;line-height:1.5;margin-bottom:12px;">
            Untuk menambah titik baru di wilayah Anda, buka formulir <strong>Tambah Destinasi</strong> lalu geser pin langsung pada peta untuk mendapatkan titik koordinat presisi.
          </p>
          <a href="/ulinkaciamis/admin/tambah.php" class="btn-primary" style="width:100%;text-align:center;justify-content:center;display:flex;">
            <i class="fas fa-plus"></i> Tambah Tempat Sekarang
          </a>
        </div>
      </div>

    </div>

  </div>
</div>
</body>
</html>

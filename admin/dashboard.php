<?php
require_once __DIR__ . '/../config.php';
requireLogin();

$db = getDB();
$total_destinasi = $db->query("SELECT COUNT(*) as c FROM destinasi WHERE status='aktif'")->fetch_assoc()['c'];
$total_kategori  = $db->query("SELECT COUNT(*) as c FROM kategori")->fetch_assoc()['c'];
$total_nonaktif  = $db->query("SELECT COUNT(*) as c FROM destinasi WHERE status='nonaktif'")->fetch_assoc()['c'];

$recent = $db->query("SELECT d.*, k.nama AS kat FROM destinasi d JOIN kategori k ON d.kategori_id=k.id ORDER BY d.created_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

$by_kat = $db->query("SELECT k.nama, COUNT(d.id) as total FROM kategori k LEFT JOIN destinasi d ON k.id=d.kategori_id AND d.status='aktif' GROUP BY k.id ORDER BY total DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - UlinKa Ciamis</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
        <h1 class="page-title">Dashboard</h1>
        <p class="page-sub">Selamat datang, <?= htmlspecialchars($_SESSION['admin_nama']) ?> 👋</p>
      </div>
      <a href="/ulinkaciamis/admin/tambah.php" class="btn-primary"><i class="fas fa-plus"></i> Tambah Destinasi</a>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card stat-green">
        <div class="stat-icon"><i class="fas fa-map-pin"></i></div>
        <div class="stat-info">
          <div class="stat-num"><?= $total_destinasi ?></div>
          <div class="stat-label">Destinasi Aktif</div>
        </div>
      </div>
      <div class="stat-card stat-blue">
        <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
        <div class="stat-info">
          <div class="stat-num"><?= $total_kategori ?></div>
          <div class="stat-label">Kategori</div>
        </div>
      </div>
      <div class="stat-card stat-orange">
        <div class="stat-icon"><i class="fas fa-eye-slash"></i></div>
        <div class="stat-info">
          <div class="stat-num"><?= $total_nonaktif ?></div>
          <div class="stat-label">Nonaktif</div>
        </div>
      </div>
      <div class="stat-card stat-purple">
        <div class="stat-icon"><i class="fas fa-map"></i></div>
        <div class="stat-info">
          <div class="stat-num">1</div>
          <div class="stat-label">Wilayah (Ciamis)</div>
        </div>
      </div>
    </div>

    <div class="two-col">
      <!-- RECENT -->
      <div class="card">
        <div class="card-header">
          <h3><i class="fas fa-clock-rotate-left"></i> Destinasi Terbaru</h3>
          <a href="/ulinkaciamis/admin/kelola.php" class="card-link">Lihat Semua →</a>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead><tr><th>Nama</th><th>Kategori</th><th>Status</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php foreach($recent as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r['nama']) ?></td>
              <td><span class="badge-cat"><?= htmlspecialchars($r['kat']) ?></span></td>
              <td>
                <?php if($r['status']==='aktif'): ?>
                  <span class="badge-status active">Aktif</span>
                <?php else: ?>
                  <span class="badge-status inactive">Nonaktif</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="/ulinkaciamis/admin/edit.php?id=<?= $r['id'] ?>" class="tbl-btn edit"><i class="fas fa-pen"></i></a>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- BY KATEGORI -->
      <div class="card">
        <div class="card-header">
          <h3><i class="fas fa-chart-bar"></i> Per Kategori</h3>
        </div>
        <div class="kat-list">
          <?php
          $maxTotal = max(array_column($by_kat,'total') ?: [1]);
          foreach($by_kat as $bk):
            $pct = $maxTotal > 0 ? round($bk['total']/$maxTotal*100) : 0;
          ?>
          <div class="kat-row">
            <div class="kat-name"><?= htmlspecialchars($bk['nama']) ?></div>
            <div class="kat-bar-wrap">
              <div class="kat-bar" style="width:<?= $pct ?>%"></div>
            </div>
            <div class="kat-num"><?= $bk['total'] ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>

  </div>
</div>
</body>
</html>

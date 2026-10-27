<?php
require_once __DIR__ . '/../config.php';
requireLogin();

$db = getDB();
$total_destinasi = $db->query("SELECT COUNT(*) as c FROM destinasi WHERE status='aktif'")->fetch_assoc()['c'];
$total_kategori  = $db->query("SELECT COUNT(*) as c FROM kategori")->fetch_assoc()['c'];
$total_nonaktif  = $db->query("SELECT COUNT(*) as c FROM destinasi WHERE status='nonaktif'")->fetch_assoc()['c'];
$total_all       = $db->query("SELECT COUNT(*) as c FROM destinasi")->fetch_assoc()['c'];

$recent = $db->query("
    SELECT d.*, k.nama AS kat 
    FROM destinasi d 
    JOIN kategori k ON d.kategori_id=k.id 
    ORDER BY d.id DESC 
    LIMIT 7
")->fetch_all(MYSQLI_ASSOC);

$by_kat = $db->query("
    SELECT k.nama, k.warna, COUNT(d.id) as total 
    FROM kategori k 
    LEFT JOIN destinasi d ON k.id=d.kategori_id AND d.status='aktif' 
    GROUP BY k.id 
    ORDER BY total DESC 
    LIMIT 8
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Ringkasan - UlinKa Ciamis</title>
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
        <p class="page-sub">Selamat datang kembali, <strong><?= htmlspecialchars($_SESSION['admin_nama'] ?? 'Pengurus') ?></strong>. Berikut ringkasan data Tatar Galuh Ciamis.</p>
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
          <div class="stat-num"><?= $total_all ?></div>
          <div class="stat-label">Total Destinasi Terdata</div>
        </div>
      </div>
      <div class="stat-card stat-green">
        <div class="stat-icon"><i class="fas fa-circle-check"></i></div>
        <div class="stat-info">
          <div class="stat-num"><?= $total_destinasi ?></div>
          <div class="stat-label">Destinasi Aktif di Peta</div>
        </div>
      </div>
      <div class="stat-card stat-blue">
        <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
        <div class="stat-info">
          <div class="stat-num"><?= $total_kategori ?></div>
          <div class="stat-label">Kategori Wisata &amp; Fasilitas</div>
        </div>
      </div>
      <div class="stat-card stat-orange">
        <div class="stat-icon"><i class="fas fa-landmark"></i></div>
        <div class="stat-info">
          <div class="stat-num">27</div>
          <div class="stat-label">Kecamatan Tatar Galuh</div>
        </div>
      </div>
    </div>

    <!-- MAIN TWO COLUMN GRID -->
    <div class="two-col">
      <!-- RECENT DESTINATIONS -->
      <div class="card">
        <div class="card-header">
          <h3><i class="fas fa-clock-rotate-left"></i> Destinasi Baru Ditambahkan</h3>
          <a href="/ulinkaciamis/admin/kelola.php" class="card-link">Lihat Semua Destinasi &rarr;</a>
        </div>
        <div class="table-wrap">
          <table class="data-table">
            <thead>
              <tr>
                <th style="width:48px;">Foto</th>
                <th>Nama Tempat &amp; Alamat</th>
                <th>Kategori</th>
                <th>Status</th>
                <th style="text-align:right;">Aksi</th>
              </tr>
            </thead>
            <tbody>
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
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                  <i class="fas fa-location-dot" style="font-size:11px;color:var(--text-light);margin-right:3px;"></i>
                  <?= htmlspecialchars($r['kecamatan'] ? 'Kec. ' . $r['kecamatan'] : 'Kabupaten Ciamis') ?>
                </div>
              </td>
              <td>
                <span class="badge-cat"><?= htmlspecialchars($r['kat']) ?></span>
              </td>
              <td>
                <?php if($r['status']==='aktif'): ?>
                  <span class="badge-status active"><i class="fas fa-circle" style="font-size:7px;"></i> Aktif</span>
                <?php else: ?>
                  <span class="badge-status inactive"><i class="fas fa-circle" style="font-size:7px;"></i> Nonaktif</span>
                <?php endif; ?>
              </td>
              <td style="text-align:right;">
                <div class="tbl-actions" style="justify-content:flex-end;">
                  <a href="/ulinkaciamis/admin/edit.php?id=<?= $r['id'] ?>" class="tbl-btn edit" title="Edit Destinasi"><i class="fas fa-pen-to-square"></i></a>
                  <a href="/ulinkaciamis/?q=<?= urlencode($r['nama']) ?>" target="_blank" class="tbl-btn view" title="Lihat di Peta"><i class="fas fa-map-pin"></i></a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- CATEGORY BREAKDOWN -->
      <div class="card">
        <div class="card-header">
          <h3><i class="fas fa-chart-column"></i> Sebaran Kategori</h3>
          <a href="/ulinkaciamis/admin/kategori.php" class="card-link">Kelola Kategori &rarr;</a>
        </div>
        <div class="kat-list">
          <?php
          $maxTotal = max(array_column($by_kat, 'total') ?: [1]);
          foreach($by_kat as $bk):
            $pct = $maxTotal > 0 ? round($bk['total'] / $maxTotal * 100) : 0;
            $barColor = !empty($bk['warna']) ? htmlspecialchars($bk['warna']) : 'var(--primary)';
          ?>
          <div class="kat-row">
            <div class="kat-name" title="<?= htmlspecialchars($bk['nama']) ?>">
              <?= htmlspecialchars($bk['nama']) ?>
            </div>
            <div class="kat-bar-wrap">
              <div class="kat-bar" style="width:<?= $pct ?>%;background:<?= $barColor ?>;"></div>
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

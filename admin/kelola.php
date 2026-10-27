<?php
require_once __DIR__ . '/../config.php';
requireLogin();

$db = getDB();
$kat_filter = (int)($_GET['kat'] ?? 0);
$kec_filter = trim($_GET['kec'] ?? '');
$search     = trim($_GET['q'] ?? '');
$page       = max(1, (int)($_GET['p'] ?? 1));
$per_page   = 15;
$offset     = ($page - 1) * $per_page;

$where = "WHERE 1=1";
$params = []; $types = '';
if ($kat_filter > 0) { $where .= " AND d.kategori_id=?"; $types .= 'i'; $params[] = $kat_filter; }
if (!empty($kec_filter)) { $where .= " AND d.kecamatan=?"; $types .= 's'; $params[] = $kec_filter; }
if (!empty($search)) {
    $where .= " AND (d.nama LIKE ? OR d.alamat LIKE ? OR d.kecamatan LIKE ?)";
    $types .= 'sss'; $like = "%$search%"; $params[] = $like; $params[] = $like; $params[] = $like;
}

$count_stmt = $db->prepare("SELECT COUNT(*) as c FROM destinasi d $where");
if ($params) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['c'];
$pages = ceil($total / $per_page);

$sql = "SELECT d.*, k.nama AS kat_nama FROM destinasi d JOIN kategori k ON d.kategori_id=k.id $where ORDER BY d.id DESC LIMIT ? OFFSET ?";
$all_params = array_merge($params, [$per_page, $offset]);
$all_types  = $types . 'ii';
$stmt = $db->prepare($sql);
$stmt->bind_param($all_types, ...$all_params);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$kategori_list = $db->query("SELECT id, nama FROM kategori ORDER BY urutan ASC")->fetch_all(MYSQLI_ASSOC);
$kecamatan_list = ['Ciamis', 'Panjalu', 'Kawali', 'Sadananya', 'Cikoneng', 'Sindangkasih', 'Rancah', 'Cijeungjing', 'Panumbangan', 'Baregbeg', 'Rajadesa', 'Banjaranyar', 'Pamarican', 'Banjarsari', 'Cipaku', 'Lumbung', 'Jatinagara', 'Sukadana', 'Cisaga', 'Tambaksari', 'Sukamantri', 'Cidolog', 'Cimaragas', 'Lakbok', 'Purwadadi', 'Mangunjaya'];

// Handle delete
if (isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $del = $db->prepare("DELETE FROM destinasi WHERE id=?");
    $del->bind_param('i', $_GET['hapus']);
    $del->execute();
    header('Location: /ulinkaciamis/admin/kelola.php?deleted=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Data Destinasi - UlinKa Ciamis</title>
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
        <h1 class="page-title">Kelola Destinasi &amp; Fasilitas</h1>
        <p class="page-sub">Menampilkan total <strong><?= $total ?> destinasi</strong> terdaftar di Tatar Galuh Ciamis</p>
      </div>
      <a href="/ulinkaciamis/admin/tambah.php" class="btn-primary"><i class="fas fa-plus"></i> Tambah Destinasi Baru</a>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> Data destinasi berhasil dihapus dari sistem.</div>
    <?php endif; ?>

    <?php if(!empty($_SESSION['admin_wilayah'])): ?>
    <div class="alert alert-success" style="background:#f0fdf4;border-color:#bbf7d0;color:#166534;">
      <i class="fas fa-shield-halved"></i>
      <span>Wilayah Penugasan: <strong>Kecamatan <?= htmlspecialchars($_SESSION['admin_wilayah']) ?></strong></span>
    </div>
    <?php endif; ?>

    <!-- FILTER TOOLBAR -->
    <div class="filter-bar">
      <form method="GET" class="filter-form">
        <div class="search-filter">
          <i class="fas fa-search"></i>
          <input type="text" name="q" placeholder="Cari nama tempat, alamat, atau kecamatan..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <select name="kat">
          <option value="">Semua Kategori</option>
          <?php foreach($kategori_list as $k): ?>
          <option value="<?= $k['id'] ?>" <?= $kat_filter == $k['id'] ? 'selected' : '' ?>><?= htmlspecialchars($k['nama']) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="kec">
          <option value="">Semua Kecamatan</option>
          <?php foreach($kecamatan_list as $kc): ?>
          <option value="<?= $kc ?>" <?= $kec_filter === $kc ? 'selected' : '' ?>>Kec. <?= $kc ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Terapkan Filter</button>
        <?php if($search || $kat_filter || $kec_filter): ?>
        <a href="/ulinkaciamis/admin/kelola.php" class="btn-ghost-sm"><i class="fas fa-rotate-left"></i> Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- TABLE CARD -->
    <div class="table-card">
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:40px;">No</th>
              <th style="width:50px;">Foto</th>
              <th>Nama Tempat &amp; Alamat</th>
              <th>Kecamatan</th>
              <th>Kategori</th>
              <th>Fasilitas</th>
              <th>Status</th>
              <th>Penginput</th>
              <th style="text-align:right;">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php if(empty($rows)): ?>
            <tr>
              <td colspan="9" style="text-align:center;padding:48px 20px;color:var(--text-muted);">
                <i class="fas fa-folder-open" style="font-size:32px;color:var(--text-light);margin-bottom:8px;display:block;"></i>
                Tidak ada data destinasi yang sesuai kriteria pencarian.
              </td>
            </tr>
          <?php endif; ?>
          <?php foreach($rows as $i => $r): ?>
          <tr>
            <td style="color:var(--text-muted);font-weight:600;font-size:12px;"><?= $offset + $i + 1 ?></td>
            <td>
              <img src="<?= htmlspecialchars($r['foto'] ?: '/ulinkaciamis/assets/images/alun_alun_ciamis.jpg') ?>" 
                   alt="<?= htmlspecialchars($r['nama']) ?>" 
                   class="dest-preview-thumb" 
                   onerror="this.src='/ulinkaciamis/assets/images/alun_alun_ciamis.jpg'" />
            </td>
            <td>
              <div style="font-weight:700;color:var(--text-main);font-size:13.5px;"><?= htmlspecialchars($r['nama']) ?></div>
              <?php if($r['alamat']): ?>
                <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">
                  <?= htmlspecialchars(mb_strimwidth($r['alamat'], 0, 48, '...')) ?>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <span class="badge" style="background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe;">
                Kec. <?= htmlspecialchars($r['kecamatan'] ?? 'Ciamis') ?>
              </span>
            </td>
            <td><span class="badge-cat"><?= htmlspecialchars($r['kat_nama']) ?></span></td>
            <td>
              <?= $r['wifi'] ? '<span class="badge-wifi"><i class="fas fa-wifi"></i> WiFi</span>' : '<span style="color:var(--text-light);font-size:12px;">—</span>' ?>
            </td>
            <td>
              <?php if($r['status']==='aktif'): ?>
                <span class="badge-status active"><i class="fas fa-circle" style="font-size:7px;"></i> Aktif</span>
              <?php else: ?>
                <span class="badge-status inactive"><i class="fas fa-circle" style="font-size:7px;"></i> Nonaktif</span>
              <?php endif; ?>
            </td>
            <td>
              <span style="font-size:12px;color:var(--text-muted);">
                <?= htmlspecialchars($r['ditambahkan_oleh'] ?? 'Admin') ?>
              </span>
            </td>
            <td style="text-align:right;">
              <div class="tbl-actions" style="justify-content:flex-end;">
                <a href="/ulinkaciamis/admin/edit.php?id=<?= $r['id'] ?>" class="tbl-btn edit" title="Edit Data"><i class="fas fa-pen-to-square"></i></a>
                <a href="/ulinkaciamis/?q=<?= urlencode($r['nama']) ?>" target="_blank" class="tbl-btn view" title="Lihat di Peta"><i class="fas fa-map-pin"></i></a>
                <a href="/ulinkaciamis/admin/kelola.php?hapus=<?= $r['id'] ?>" class="tbl-btn del" title="Hapus Destinasi" onclick="return confirm('Apakah Anda yakin ingin menghapus destinasi: <?= addslashes(htmlspecialchars($r['nama'])) ?>?')"><i class="fas fa-trash-can"></i></a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- PAGINATION -->
      <?php if($pages > 1): ?>
      <div class="pagination">
        <span style="margin-right:auto;font-size:12.5px;color:var(--text-muted);">
          Halaman <strong><?= $page ?></strong> dari <strong><?= $pages ?></strong> (Total <?= $total ?> data)
        </span>
        <?php for($i=1;$i<=$pages;$i++): ?>
        <a href="?p=<?= $i ?>&q=<?= urlencode($search) ?>&kat=<?= $kat_filter ?>&kec=<?= urlencode($kec_filter) ?>" class="page-btn <?= $i==$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>
</body>
</html>

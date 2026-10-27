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
<title>Kelola Destinasi - UlinKa Ciamis</title>
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
        <h1 class="page-title">Kelola Destinasi</h1>
        <p class="page-sub">Total <?= $total ?> destinasi ditemukan</p>
      </div>
      <a href="/ulinkaciamis/admin/tambah.php" class="btn-primary"><i class="fas fa-plus"></i> Tambah Baru</a>
    </div>

    <?php if(isset($_GET['deleted'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> Destinasi berhasil dihapus.</div>
    <?php endif; ?>

    <?php if(!empty($_SESSION['admin_wilayah'])): ?>
    <div style="background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);border-radius:12px;padding:10px 16px;margin-bottom:16px;color:#10b981;font-size:13px;display:flex;align-items:center;gap:8px">
      <i class="fas fa-map-pin"></i> <span>Wilayah Tugas Anda: <strong>Kecamatan <?= htmlspecialchars($_SESSION['admin_wilayah']) ?></strong></span>
    </div>
    <?php endif; ?>

    <!-- FILTER -->
    <div class="filter-bar">
      <form method="GET" class="filter-form">
        <div class="search-filter">
          <i class="fas fa-search"></i>
          <input type="text" name="q" placeholder="Cari nama, alamat, atau kecamatan..." value="<?= htmlspecialchars($search) ?>">
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
        <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Filter</button>
        <a href="/ulinkaciamis/admin/kelola.php" class="btn-ghost-sm">Reset</a>
      </form>
    </div>

    <!-- TABLE -->
    <div class="card">
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama Tempat</th>
              <th>Kecamatan</th>
              <th>Kategori</th>
              <th>WiFi</th>
              <th>Status</th>
              <th>Ditambahkan</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php if(empty($rows)): ?>
            <tr><td colspan="8" class="text-center" style="padding:40px;color:rgba(255,255,255,.3)">Tidak ada data ditemukan</td></tr>
          <?php endif; ?>
          <?php foreach($rows as $i => $r): ?>
          <tr>
            <td><?= $offset + $i + 1 ?></td>
            <td>
              <strong><?= htmlspecialchars($r['nama']) ?></strong>
              <?php if($r['alamat']): ?><br><small style="color:rgba(255,255,255,.4)"><?= htmlspecialchars(mb_strimwidth($r['alamat'],0,40,'...')) ?></small><?php endif; ?>
            </td>
            <td><span class="badge" style="background:rgba(59,130,246,.15);color:#60a5fa;border:1px solid rgba(59,130,246,.3)">Kec. <?= htmlspecialchars($r['kecamatan'] ?? 'Ciamis') ?></span></td>
            <td><span class="badge-cat"><?= htmlspecialchars($r['kat_nama']) ?></span></td>
            <td><?= $r['wifi'] ? '<span class="badge-wifi">📶 Ada</span>' : '<span style="color:rgba(255,255,255,.25)">—</span>' ?></td>
            <td>
              <?php if($r['status']==='aktif'): ?>
                <span class="badge-status active">Aktif</span>
              <?php else: ?>
                <span class="badge-status inactive">Nonaktif</span>
              <?php endif; ?>
            </td>
            <td><small style="color:rgba(255,255,255,.4)"><?= htmlspecialchars($r['ditambahkan_oleh'] ?? '—') ?></small></td>
            <td>
              <div class="tbl-actions">
                <a href="/ulinkaciamis/admin/edit.php?id=<?= $r['id'] ?>" class="tbl-btn edit" title="Edit"><i class="fas fa-pen"></i></a>
                <a href="/ulinkaciamis/admin/kelola.php?hapus=<?= $r['id'] ?>" class="tbl-btn del" title="Hapus" onclick="return confirm('Hapus <?= addslashes(htmlspecialchars($r['nama'])) ?>?')"><i class="fas fa-trash"></i></a>
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
        <?php for($i=1;$i<=$pages;$i++): ?>
        <a href="?p=<?= $i ?>&q=<?= urlencode($search) ?>&kat=<?= $kat_filter ?>" class="page-btn <?= $i==$page?'active':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>

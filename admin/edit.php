<?php
require_once __DIR__ . '/../config.php';
requireLogin();

$db  = getDB();
$id  = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: /ulinkaciamis/admin/kelola.php'); exit; }

$dest = $db->prepare("SELECT * FROM destinasi WHERE id=?");
$dest->bind_param('i', $id);
$dest->execute();
$d = $dest->get_result()->fetch_assoc();
if (!$d) { header('Location: /ulinkaciamis/admin/kelola.php'); exit; }

$kategori_list = $db->query("SELECT id, nama FROM kategori ORDER BY urutan ASC")->fetch_all(MYSQLI_ASSOC);
$msg   = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama'] ?? '');
    $kat_id    = (int)($_POST['kategori_id'] ?? 0);
    $kecamatan = trim($_POST['kecamatan'] ?? 'Ciamis');
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $alamat    = trim($_POST['alamat'] ?? '');
    $lat       = trim($_POST['latitude'] ?? '');
    $lng       = trim($_POST['longitude'] ?? '');
    $harga     = trim($_POST['harga'] ?? '');
    $wifi      = isset($_POST['wifi']) ? 1 : 0;
    $jam_buka  = trim($_POST['jam_buka'] ?? '');
    $jam_tutup = trim($_POST['jam_tutup'] ?? '');
    $no_telp   = trim($_POST['no_telepon'] ?? '');
    $status    = $_POST['status'] === 'nonaktif' ? 'nonaktif' : 'aktif';

    // Upload foto baru jika ada
    $foto = $d['foto'] ?? null;
    if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            $filename = 'dest_' . time() . '_' . rand(100, 999) . '.' . $ext;
            $uploadDir = __DIR__ . '/../assets/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $filename)) {
                $foto = '/ulinkaciamis/assets/uploads/' . $filename;
            }
        }
    }

    if (empty($nama) || $kat_id < 1 || empty($lat) || empty($lng)) {
        $error = 'Nama, kategori, latitude, dan longitude wajib diisi.';
    } else {
        $stmt = $db->prepare("UPDATE destinasi SET nama=?,kategori_id=?,kecamatan=?,deskripsi=?,alamat=?,latitude=?,longitude=?,harga=?,wifi=?,jam_buka=?,jam_tutup=?,no_telepon=?,status=?,foto=? WHERE id=?");
        $stmt->bind_param('sissssssisssssi', $nama, $kat_id, $kecamatan, $deskripsi, $alamat, $lat, $lng, $harga, $wifi, $jam_buka, $jam_tutup, $no_telp, $status, $foto, $id);
        if ($stmt->execute()) {
            $msg = 'Destinasi berhasil diperbarui!';
            // Refresh data
            $dest->execute();
            $d = $dest->get_result()->fetch_assoc();
        } else {
            $error = 'Gagal menyimpan: ' . $db->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Destinasi - UlinKa Ciamis</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="/ulinkaciamis/assets/css/admin.css">
</head>
<body>
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
  <?php include 'partials/topbar.php'; ?>
  <div class="admin-content">
    <div class="page-header">
      <div>
        <h1 class="page-title">Edit Destinasi</h1>
        <p class="page-sub"><?= htmlspecialchars($d['nama']) ?></p>
      </div>
      <a href="/ulinkaciamis/admin/kelola.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <?php if($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="form-layout">
      <form method="POST" enctype="multipart/form-data" class="form-card">

        <div class="form-section">
          <h3 class="form-section-title"><i class="fas fa-info-circle"></i> Informasi Dasar</h3>
          <div class="form-grid">
            <div class="form-group full">
              <label>Nama Tempat <span class="req">*</span></label>
              <input type="text" name="nama" value="<?= htmlspecialchars($d['nama']) ?>" required>
            </div>
            <div class="form-group">
              <label>Kategori <span class="req">*</span></label>
              <select name="kategori_id" required>
                <?php foreach($kategori_list as $k): ?>
                <option value="<?= $k['id'] ?>" <?= $d['kategori_id']==$k['id']?'selected':'' ?>><?= htmlspecialchars($k['nama']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Kecamatan / Wilayah <span class="req">*</span></label>
              <select name="kecamatan" required>
                <?php
                $all_kec = ['Ciamis', 'Panjalu', 'Kawali', 'Sadananya', 'Cikoneng', 'Sindangkasih', 'Rancah', 'Cijeungjing', 'Panumbangan', 'Baregbeg', 'Rajadesa', 'Banjaranyar', 'Pamarican', 'Banjarsari', 'Cipaku', 'Lumbung', 'Jatinagara', 'Sukadana', 'Cisaga', 'Tambaksari', 'Sukamantri', 'Cidolog', 'Cimaragas', 'Lakbok', 'Purwadadi', 'Mangunjaya'];
                $cur_kec = $d['kecamatan'] ?? 'Ciamis';
                foreach($all_kec as $kc): ?>
                <option value="<?= $kc ?>" <?= $cur_kec === $kc ? 'selected' : '' ?>>Kec. <?= $kc ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Status</label>
              <select name="status">
                <option value="aktif" <?= $d['status']==='aktif'?'selected':'' ?>>✅ Aktif</option>
                <option value="nonaktif" <?= $d['status']==='nonaktif'?'selected':'' ?>>❌ Nonaktif</option>
              </select>
            </div>
            <div class="form-group full">
              <label>Alamat Lengkap</label>
              <input type="text" name="alamat" value="<?= htmlspecialchars($d['alamat'] ?? '') ?>">
            </div>
            <div class="form-group full">
              <label>Deskripsi</label>
              <textarea name="deskripsi" rows="3"><?= htmlspecialchars($d['deskripsi'] ?? '') ?></textarea>
            </div>
            <div class="form-group full">
              <label>Foto Tempat / Fasilitas</label>
              <?php if(!empty($d['foto'])): ?>
              <div style="margin-bottom:10px;display:flex;align-items:center;gap:12px">
                <img src="<?= htmlspecialchars($d['foto']) ?>" alt="Foto" style="width:72px;height:72px;object-fit:cover;border-radius:8px;border:1px solid rgba(255,255,255,.1)">
                <span style="font-size:12px;color:#94a3b8">Foto saat ini aktif. Pilih file baru jika ingin mengganti.</span>
              </div>
              <?php endif; ?>
              <input type="file" name="foto" accept="image/*" style="border:1px dashed rgba(255,255,255,.2);padding:10px;border-radius:10px;background:rgba(255,255,255,.02);color:#94a3b8">
              <small style="color:#94a3b8;font-size:12px;margin-top:4px;display:block">Format: JPG, PNG, WEBP (Maks 5MB)</small>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3 class="form-section-title"><i class="fas fa-map-pin"></i> Koordinat — Klik peta untuk pindah pin</h3>
          <div class="form-grid">
            <div class="form-group">
              <label>Latitude <span class="req">*</span></label>
              <input type="text" name="latitude" id="inputLat" value="<?= htmlspecialchars($d['latitude']) ?>" required>
            </div>
            <div class="form-group">
              <label>Longitude <span class="req">*</span></label>
              <input type="text" name="longitude" id="inputLng" value="<?= htmlspecialchars($d['longitude']) ?>" required>
            </div>
          </div>
          <div id="pickMap" style="height:300px;border-radius:14px;overflow:hidden;border:1px solid rgba(255,255,255,.1);margin-top:4px"></div>
        </div>

        <div class="form-section">
          <h3 class="form-section-title"><i class="fas fa-circle-info"></i> Detail Fasilitas</h3>
          <div class="form-grid">
            <div class="form-group">
              <label>Harga / Tarif</label>
              <input type="text" name="harga" value="<?= htmlspecialchars($d['harga'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>No. Telepon</label>
              <input type="text" name="no_telepon" value="<?= htmlspecialchars($d['no_telepon'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Jam Buka</label>
              <input type="time" name="jam_buka" value="<?= htmlspecialchars(substr($d['jam_buka']??'',0,5)) ?>">
            </div>
            <div class="form-group">
              <label>Jam Tutup</label>
              <input type="time" name="jam_tutup" value="<?= htmlspecialchars(substr($d['jam_tutup']??'',0,5)) ?>">
            </div>
            <div class="form-group full">
              <label class="switch-label">
                <div class="switch-wrap">
                  <input type="checkbox" name="wifi" id="wifiSwitch" <?= $d['wifi']?'checked':'' ?>>
                  <span class="switch-slider"></span>
                </div>
                <span>Tersedia WiFi</span>
              </label>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary btn-lg"><i class="fas fa-save"></i> Perbarui Destinasi</button>
          <a href="/ulinkaciamis/admin/kelola.php?hapus=<?= $id ?>" class="btn-danger" onclick="return confirm('Hapus destinasi ini?')"><i class="fas fa-trash"></i> Hapus</a>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const lat0 = <?= (float)$d['latitude'] ?>, lng0 = <?= (float)$d['longitude'] ?>;
const pickMap = L.map('pickMap', { center: [lat0, lng0], zoom: 15 });
// Esri World Street Map: Clean, reliable, no API key required
L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
  attribution: '&copy; Esri',
  maxZoom: 19
}).addTo(pickMap);

const inputLat = document.getElementById('inputLat');
const inputLng = document.getElementById('inputLng');
let pickMarker = L.marker([lat0, lng0]).addTo(pickMap);

pickMap.on('click', e => {
  const { lat, lng } = e.latlng;
  inputLat.value = lat.toFixed(7);
  inputLng.value = lng.toFixed(7);
  pickMarker.setLatLng(e.latlng);
});
</script>
</body>
</html>

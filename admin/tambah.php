<?php
require_once __DIR__ . '/../config.php';
requireLogin();

$db = getDB();
$kategori_list = $db->query("SELECT id, nama FROM kategori ORDER BY urutan ASC")->fetch_all(MYSQLI_ASSOC);

$msg   = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama       = trim($_POST['nama'] ?? '');
    $kat_id     = (int)($_POST['kategori_id'] ?? 0);
    $kecamatan  = trim($_POST['kecamatan'] ?? 'Ciamis');
    $deskripsi  = trim($_POST['deskripsi'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');
    $lat        = trim($_POST['latitude'] ?? '');
    $lng        = trim($_POST['longitude'] ?? '');
    $harga      = trim($_POST['harga'] ?? '');
    $wifi       = isset($_POST['wifi']) ? 1 : 0;
    $jam_buka   = trim($_POST['jam_buka'] ?? '');
    $jam_tutup  = trim($_POST['jam_tutup'] ?? '');
    $no_telp    = trim($_POST['no_telepon'] ?? '');
    $status     = $_POST['status'] === 'nonaktif' ? 'nonaktif' : 'aktif';

    // Upload foto jika ada
    $foto = null;
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
        $by = $_SESSION['admin_nama'];
        $stmt = $db->prepare("INSERT INTO destinasi (nama, kategori_id, kecamatan, deskripsi, alamat, latitude, longitude, harga, wifi, jam_buka, jam_tutup, no_telepon, status, ditambahkan_oleh, foto) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sissssssisissss', $nama, $kat_id, $kecamatan, $deskripsi, $alamat, $lat, $lng, $harga, $wifi, $jam_buka, $jam_tutup, $no_telp, $status, $by, $foto);
        if ($stmt->execute()) {
            $msg = 'Destinasi berhasil ditambahkan!';
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
<title>Tambah Destinasi - UlinKa Ciamis</title>
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
        <h1 class="page-title">Tambah Destinasi</h1>
        <p class="page-sub">Tambahkan tempat/fasilitas baru ke peta</p>
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
              <input type="text" name="nama" placeholder="cth: Alfamart Jl. Sudirman" value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label>Kategori <span class="req">*</span></label>
              <select name="kategori_id" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach($kategori_list as $k): ?>
                <option value="<?= $k['id'] ?>" <?= (($_POST['kategori_id']??'') == $k['id']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($k['nama']) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Kecamatan / Wilayah <span class="req">*</span></label>
              <select name="kecamatan" required>
                <?php
                $all_kec = ['Ciamis', 'Panjalu', 'Kawali', 'Sadananya', 'Cikoneng', 'Sindangkasih', 'Rancah', 'Cijeungjing', 'Panumbangan', 'Baregbeg', 'Rajadesa', 'Banjaranyar', 'Pamarican', 'Banjarsari', 'Cipaku', 'Lumbung', 'Jatinagara', 'Sukadana', 'Cisaga', 'Tambaksari', 'Sukamantri', 'Cidolog', 'Cimaragas', 'Lakbok', 'Purwadadi', 'Mangunjaya'];
                $user_wilayah = $_SESSION['admin_wilayah'] ?? '';
                $sel_kec = $_POST['kecamatan'] ?? ($user_wilayah && in_array($user_wilayah, $all_kec) ? $user_wilayah : 'Ciamis');
                foreach($all_kec as $kc): ?>
                <option value="<?= $kc ?>" <?= $sel_kec === $kc ? 'selected' : '' ?>>Kec. <?= $kc ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Status</label>
              <select name="status">
                <option value="aktif">✅ Aktif (tampil di peta)</option>
                <option value="nonaktif">❌ Nonaktif</option>
              </select>
            </div>
            <div class="form-group full">
              <label>Alamat Lengkap</label>
              <input type="text" name="alamat" placeholder="cth: Jl. Jend. Sudirman No.1, Ciamis" value="<?= htmlspecialchars($_POST['alamat'] ?? '') ?>">
            </div>
            <div class="form-group full">
              <label>Deskripsi Tempat</label>
              <textarea name="deskripsi" rows="3" placeholder="Deskripsi daya tarik, fasilitas, atau keunggulan tempat ini..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
            </div>
            <div class="form-group full">
              <label>Foto Tempat / Fasilitas (Opsional)</label>
              <input type="file" name="foto" accept="image/*" style="border:1px dashed rgba(255,255,255,.2);padding:10px;border-radius:10px;background:rgba(255,255,255,.02);color:#94a3b8">
              <small style="color:#94a3b8;font-size:12px;margin-top:4px;display:block">Format: JPG, PNG, WEBP (Maks 5MB)</small>
            </div>
          </div>
        </div>

        <div class="form-section">
          <h3 class="form-section-title"><i class="fas fa-map-pin"></i> Koordinat — Klik peta untuk mengisi otomatis</h3>
          <div class="form-grid">
            <div class="form-group">
              <label>Latitude <span class="req">*</span></label>
              <input type="text" name="latitude" id="inputLat" placeholder="-7.3276" value="<?= htmlspecialchars($_POST['latitude'] ?? '') ?>" required>
            </div>
            <div class="form-group">
              <label>Longitude <span class="req">*</span></label>
              <input type="text" name="longitude" id="inputLng" placeholder="108.3625" value="<?= htmlspecialchars($_POST['longitude'] ?? '') ?>" required>
            </div>
          </div>
          <div id="pickMap" style="height:320px;border-radius:14px;overflow:hidden;border:1px solid rgba(255,255,255,.1);margin-top:4px"></div>
          <p class="form-hint"><i class="fas fa-hand-pointer"></i> Klik lokasi di peta Ciamis untuk menandai koordinat presisi</p>
        </div>

        <div class="form-section">
          <h3 class="form-section-title"><i class="fas fa-circle-info"></i> Detail Fasilitas & Tiket</h3>
          <div class="form-grid">
            <div class="form-group">
              <label>Harga / Tarif Tiket</label>
              <input type="text" name="harga" placeholder="cth: Rp 10.000 / Gratis" value="<?= htmlspecialchars($_POST['harga'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>No. Telepon / WhatsApp</label>
              <input type="text" name="no_telepon" placeholder="cth: (0265) 771000" value="<?= htmlspecialchars($_POST['no_telepon'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Jam Buka</label>
              <input type="time" name="jam_buka" value="<?= htmlspecialchars($_POST['jam_buka'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Jam Tutup</label>
              <input type="time" name="jam_tutup" value="<?= htmlspecialchars($_POST['jam_tutup'] ?? '') ?>">
            </div>
            <div class="form-group full">
              <label class="switch-label">
                <div class="switch-wrap">
                  <input type="checkbox" name="wifi" id="wifiSwitch" <?= isset($_POST['wifi']) ? 'checked' : '' ?>>
                  <span class="switch-slider"></span>
                </div>
                <span>Tersedia WiFi</span>
              </label>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary btn-lg"><i class="fas fa-save"></i> Simpan Destinasi</button>
          <a href="/ulinkaciamis/admin/kelola.php" class="btn-ghost">Batal</a>
        </div>

      </form>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const pickMap = L.map('pickMap', { center: [-7.3276, 108.3625], zoom: 13 });
// Esri World Street Map: Clean, full color, no API key required
L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
  attribution: '&copy; Esri',
  maxZoom: 19
}).addTo(pickMap);

let pickMarker = null;
const inputLat = document.getElementById('inputLat');
const inputLng = document.getElementById('inputLng');

// Init marker if values exist
const initLat = parseFloat(inputLat.value);
const initLng = parseFloat(inputLng.value);
if (!isNaN(initLat) && !isNaN(initLng)) {
  pickMarker = L.marker([initLat, initLng]).addTo(pickMap);
  pickMap.setView([initLat, initLng], 15);
}

pickMap.on('click', e => {
  const { lat, lng } = e.latlng;
  inputLat.value = lat.toFixed(7);
  inputLng.value = lng.toFixed(7);
  if (pickMarker) pickMarker.setLatLng(e.latlng);
  else pickMarker = L.marker(e.latlng).addTo(pickMap);
});

// Manual input sync
[inputLat, inputLng].forEach(el => {
  el.addEventListener('change', () => {
    const lat = parseFloat(inputLat.value);
    const lng = parseFloat(inputLng.value);
    if (!isNaN(lat) && !isNaN(lng)) {
      if (pickMarker) pickMarker.setLatLng([lat, lng]);
      else pickMarker = L.marker([lat, lng]).addTo(pickMap);
      pickMap.setView([lat, lng], 15);
    }
  });
});
</script>
</body>
</html>

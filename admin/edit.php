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
        $error = 'Nama, kategori, latitude, dan longitude wajib diisi secara lengkap.';
    } else {
        $stmt = $db->prepare("UPDATE destinasi SET nama=?,kategori_id=?,kecamatan=?,deskripsi=?,alamat=?,latitude=?,longitude=?,harga=?,wifi=?,jam_buka=?,jam_tutup=?,no_telepon=?,status=?,foto=? WHERE id=?");
        $stmt->bind_param('sissssssisssssi', $nama, $kat_id, $kecamatan, $deskripsi, $alamat, $lat, $lng, $harga, $wifi, $jam_buka, $jam_tutup, $no_telp, $status, $foto, $id);
        if ($stmt->execute()) {
            $msg = 'Data destinasi berhasil diperbarui!';
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
<title>Edit Destinasi: <?= htmlspecialchars($d['nama']) ?> - UlinKa Ciamis</title>
<link rel="icon" type="image/png" href="/ulinkaciamis/assets/images/lambang_ciamis.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        <h1 class="page-title">Edit Data Destinasi</h1>
        <p class="page-sub">Memperbarui informasi untuk <strong><?= htmlspecialchars($d['nama']) ?></strong> (ID: #<?= $d['id'] ?>)</p>
      </div>
      <div style="display:flex;align-items:center;gap:10px;">
        <a href="/ulinkaciamis/?q=<?= urlencode($d['nama']) ?>" target="_blank" class="btn-secondary"><i class="fas fa-eye"></i> Pratinjau di Peta</a>
        <a href="/ulinkaciamis/admin/kelola.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
      </div>
    </div>

    <?php if($msg): ?><div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-error"><i class="fas fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="form-layout">
      <form method="POST" enctype="multipart/form-data" class="form-card">

        <!-- SECTION 1 -->
        <div class="form-section">
          <h3 class="form-section-title"><i class="fas fa-circle-info"></i> Informasi Dasar Tempat</h3>
          <div class="form-grid">
            <div class="form-group full">
              <label>Nama Destinasi / Tempat / Fasilitas <span class="req">*</span></label>
              <input type="text" name="nama" value="<?= htmlspecialchars($d['nama']) ?>" required>
            </div>
            <div class="form-group">
              <label>Kategori <span class="req">*</span></label>
              <select name="kategori_id" required>
                <?php foreach($kategori_list as $k): ?>
                <option value="<?= $k['id'] ?>" <?= $d['kategori_id'] == $k['id'] ? 'selected' : '' ?>>
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
                foreach($all_kec as $kc): ?>
                <option value="<?= $kc ?>" <?= ($d['kecamatan'] ?? 'Ciamis') === $kc ? 'selected' : '' ?>>Kec. <?= $kc ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Status Penayangan</label>
              <select name="status">
                <option value="aktif" <?= $d['status'] === 'aktif' ? 'selected' : '' ?>>Aktif (Ditampilkan di peta publik)</option>
                <option value="nonaktif" <?= $d['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif (Disembunyikan)</option>
              </select>
            </div>
            <div class="form-group">
              <label>Foto Tempat / Fasilitas</label>
              <?php if(!empty($d['foto'])): ?>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                  <img src="<?= htmlspecialchars($d['foto']) ?>" alt="Foto Sekarang" style="width:50px;height:50px;object-fit:cover;border-radius:6px;border:1px solid var(--border);">
                  <span style="font-size:12px;color:var(--text-muted);">Foto aktif terpasang. Unggah file baru untuk mengganti:</span>
                </div>
              <?php endif; ?>
              <input type="file" name="foto" accept="image/*">
              <small class="form-hint">Format yang didukung: JPG, PNG, WEBP (Maks 5MB)</small>
            </div>
            <div class="form-group full">
              <label>Alamat Lengkap</label>
              <input type="text" name="alamat" value="<?= htmlspecialchars($d['alamat'] ?? '') ?>" placeholder="Contoh: Jl. Jend. Sudirman No. 1, Ciamis Kota">
            </div>
            <div class="form-group full">
              <label>Deskripsi Ringkas</label>
              <textarea name="deskripsi" rows="3"><?= htmlspecialchars($d['deskripsi'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <!-- SECTION 2 -->
        <div class="form-section">
          <h3 class="form-section-title"><i class="fas fa-location-crosshairs"></i> Posisi Koordinat Peta</h3>
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
          <div style="display:flex;align-items:center;gap:8px;margin:12px 0 8px;flex-wrap:wrap;">
            <span style="font-size:12px;color:var(--text-muted);font-weight:600;"><i class="fas fa-crosshairs"></i> Fokus Titik Cepat:</span>
            <button type="button" onclick="jumpTo(-7.326694, 108.353192, 'Alun-Alun Ciamis')" class="btn-ghost-sm">Alun-Alun Ciamis</button>
            <button type="button" onclick="jumpTo(-7.135200, 108.281100, 'Panjalu')" class="btn-ghost-sm">Situ Panjalu</button>
            <button type="button" onclick="jumpTo(-7.195000, 108.368000, 'Kawali')" class="btn-ghost-sm">Astana Gede Kawali</button>
            <button type="button" onclick="jumpTo(-7.278500, 108.318000, 'Sadananya')" class="btn-ghost-sm">Cadas Ngampar</button>
            <button type="button" onclick="jumpTo(-7.334750, 108.359400, 'RSUD Ciamis')" class="btn-ghost-sm">RSUD Ciamis</button>
          </div>
          <div id="pickMap" style="height:350px;border-radius:var(--radius);overflow:hidden;border:1px solid var(--border-strong);margin-top:6px;"></div>
          <p class="form-hint"><i class="fas fa-circle-info"></i> Klik langsung di atas peta untuk memperbarui titik koordinat presisi.</p>
        </div>

        <!-- SECTION 3 -->
        <div class="form-section">
          <h3 class="form-section-title"><i class="fas fa-clock"></i> Fasilitas, Tiket &amp; Jam Operasional</h3>
          <div class="form-grid">
            <div class="form-group">
              <label>Harga Tiket / Biaya</label>
              <input type="text" name="harga" value="<?= htmlspecialchars($d['harga'] ?? '') ?>" placeholder="Contoh: Gratis / Rp 10.000">
            </div>
            <div class="form-group">
              <label>Nomor Telepon / Kontak</label>
              <input type="text" name="no_telepon" value="<?= htmlspecialchars($d['no_telepon'] ?? '') ?>" placeholder="Contoh: (0265) 771000">
            </div>
            <div class="form-group">
              <label>Jam Buka</label>
              <input type="time" name="jam_buka" value="<?= htmlspecialchars($d['jam_buka'] ?? '') ?>">
            </div>
            <div class="form-group">
              <label>Jam Tutup</label>
              <input type="time" name="jam_tutup" value="<?= htmlspecialchars($d['jam_tutup'] ?? '') ?>">
            </div>
            <div class="form-group full">
              <label class="switch-label">
                <div class="switch-wrap">
                  <input type="checkbox" name="wifi" id="wifiSwitch" <?= !empty($d['wifi']) ? 'checked' : '' ?>>
                  <span class="switch-slider"></span>
                </div>
                <span style="font-size:13px;font-weight:600;color:var(--text-main);">Tersedia Fasilitas WiFi Publik / Gratis</span>
              </label>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary btn-lg"><i class="fas fa-floppy-disk"></i> Simpan Perubahan</button>
          <a href="/ulinkaciamis/admin/kelola.php" class="btn-secondary">Batal</a>
        </div>

      </form>
    </div>

  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const curLat = parseFloat("<?= (float)$d['latitude'] ?>") || -7.326694;
const curLng = parseFloat("<?= (float)$d['longitude'] ?>") || 108.353192;

const pickMap = L.map('pickMap', { center: [curLat, curLng], zoom: 15 });
L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
  maxZoom: 20,
  subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
  attribution: '&copy; Google Maps'
}).addTo(pickMap);

const inputLat = document.getElementById('inputLat');
const inputLng = document.getElementById('inputLng');
let pickMarker = L.marker([curLat, curLng]).addTo(pickMap);

function jumpTo(lat, lng, name) {
  pickMap.setView([lat, lng], 16);
  inputLat.value = lat.toFixed(7);
  inputLng.value = lng.toFixed(7);
  if (pickMarker) pickMarker.setLatLng([lat, lng]);
  else pickMarker = L.marker([lat, lng]).addTo(pickMap);
}

pickMap.on('click', e => {
  const { lat, lng } = e.latlng;
  inputLat.value = lat.toFixed(7);
  inputLng.value = lng.toFixed(7);
  if (pickMarker) pickMarker.setLatLng(e.latlng);
  else pickMarker = L.marker(e.latlng).addTo(pickMap);
});

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

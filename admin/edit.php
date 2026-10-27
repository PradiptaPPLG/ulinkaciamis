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

// 1. Dapatkan Informasi Peran & Wilayah Pengguna
$user_role    = $_SESSION['admin_role'] ?? 'pengurus_wilayah';
$user_wilayah = $_SESSION['admin_wilayah'] ?? '';
$is_regional  = ($user_role !== 'superadmin' && !empty($user_wilayah) && $user_wilayah !== 'Seluruh Kabupaten Ciamis');

// 2. Proteksi Kewenangan: Staf Daerah HANYA BISA MENGEDIT DESTINASI DI WILAYAHNYA SENDIRI
if ($is_regional && $d['kecamatan'] !== $user_wilayah) {
    die("
    <div style='font-family:sans-serif;padding:50px 20px;text-align:center;max-width:550px;margin:50px auto;background:#fff;border-radius:16px;box-shadow:0 10px 30px rgba(0,0,0,0.1);border:1px solid #fee2e2;'>
        <div style='font-size:48px;color:#ef4444;margin-bottom:15px;'><i class='fas fa-shield-halved'></i></div>
        <h2 style='color:#1e293b;margin-bottom:8px;'>Akses Ditolak (Otoritas Wilayah)</h2>
        <p style='color:#64748b;line-height:1.6;font-size:14px;margin-bottom:24px;'>
            Destinasi <strong>" . htmlspecialchars($d['nama']) . "</strong> berada di <strong>Kecamatan " . htmlspecialchars($d['kecamatan']) . "</strong>. Akun Anda memiliki wewenang khusus untuk wilayah <strong>Kecamatan " . htmlspecialchars($user_wilayah) . "</strong>.
        </p>
        <a href='/ulinkaciamis/admin/kelola.php' style='display:inline-block;padding:10px 24px;background:#9e0b84;color:#fff;text-decoration:none;border-radius:8px;font-weight:700;font-size:13px;'>Kembali ke Kelola Destinasi</a>
    </div>
    ");
}

// Konfigurasi Wilayah & Koordinat Default
$wilayah_configs = [
    'Panjalu' => [
        'lat' => -7.135200, 'lng' => 108.281100, 'zoom' => 14,
        'bounds' => ['min_lat' => -7.220, 'max_lat' => -7.050, 'min_lng' => 108.210, 'max_lng' => 108.350],
        'shortcuts' => [
            ['name' => 'Situ Lengkong', 'lat' => -7.135200, 'lng' => 108.281100],
            ['name' => 'Alun-Alun / Pasar Panjalu', 'lat' => -7.138200, 'lng' => 108.281500],
            ['name' => 'Curug 7 Cibolang', 'lat' => -7.120500, 'lng' => 108.267800],
            ['name' => 'Kantor Kec. Panjalu', 'lat' => -7.136800, 'lng' => 108.280500],
            ['name' => 'Puskesmas Panjalu', 'lat' => -7.137800, 'lng' => 108.280100],
            ['name' => 'AHASS Honda Panjalu', 'lat' => -7.139500, 'lng' => 108.282500],
        ]
    ],
    'Kawali' => [
        'lat' => -7.195000, 'lng' => 108.368000, 'zoom' => 14,
        'bounds' => ['min_lat' => -7.260, 'max_lat' => -7.130, 'min_lng' => 108.310, 'max_lng' => 108.430],
        'shortcuts' => [
            ['name' => 'Astana Gede Kawali', 'lat' => -7.195000, 'lng' => 108.368000],
            ['name' => 'Simpang Tiga Kawali', 'lat' => -7.196800, 'lng' => 108.368200],
            ['name' => 'Kantor Kec. Kawali', 'lat' => -7.196500, 'lng' => 108.367500],
            ['name' => 'Puskesmas DTP Kawali', 'lat' => -7.198100, 'lng' => 108.367800],
            ['name' => 'AHASS Kawali', 'lat' => -7.200500, 'lng' => 108.370200],
        ]
    ],
    'Ciamis' => [
        'lat' => -7.326694, 'lng' => 108.353192, 'zoom' => 15,
        'bounds' => ['min_lat' => -7.380, 'max_lat' => -7.260, 'min_lng' => 108.270, 'max_lng' => 108.430],
        'shortcuts' => [
            ['name' => 'Alun-Alun Ciamis', 'lat' => -7.326694, 'lng' => 108.353192],
            ['name' => 'Masjid Agung Ciamis', 'lat' => -7.327150, 'lng' => 108.352480],
            ['name' => 'Stasiun Ciamis', 'lat' => -7.324883, 'lng' => 108.349947],
            ['name' => 'RSUD Ciamis', 'lat' => -7.334750, 'lng' => 108.359400],
            ['name' => 'Stadion Galuh', 'lat' => -7.328900, 'lng' => 108.342100],
        ]
    ],
    'Sadananya' => [
        'lat' => -7.278500, 'lng' => 108.318000, 'zoom' => 14,
        'bounds' => ['min_lat' => -7.320, 'max_lat' => -7.190, 'min_lng' => 108.240, 'max_lng' => 108.360],
        'shortcuts' => [
            ['name' => 'Cadas Ngampar', 'lat' => -7.247997, 'lng' => 108.309501],
            ['name' => 'Curug Panganten', 'lat' => -7.235100, 'lng' => 108.298500],
            ['name' => 'Puskesmas Sadananya', 'lat' => -7.278800, 'lng' => 108.318200],
        ]
    ]
];

$active_cfg = $wilayah_configs[$d['kecamatan']] ?? [
    'lat' => (float)$d['latitude'], 'lng' => (float)$d['longitude'], 'zoom' => 15,
    'bounds' => ['min_lat' => -7.55, 'max_lat' => -7.00, 'min_lng' => 108.15, 'max_lng' => 108.65],
    'shortcuts' => [
        ['name' => 'Posisi Awal Destinasi', 'lat' => (float)$d['latitude'], 'lng' => (float)$d['longitude']],
    ]
];

// Daftar Kategori
$kategori_list = $db->query("SELECT id, nama, slug, warna FROM kategori ORDER BY urutan ASC")->fetch_all(MYSQLI_ASSOC);

$kategori_icons = [
    'wisata-alam'       => 'fa-solid fa-mountain-sun',
    'wisata-buatan'     => 'fa-solid fa-landmark-flag',
    'curug'             => 'fa-solid fa-water',
    'sejarah'           => 'fa-solid fa-monument',
    'cafe-kuliner'      => 'fa-solid fa-utensils',
    'minimarket'        => 'fa-solid fa-cart-shopping',
    'spbu'              => 'fa-solid fa-gas-pump',
    'bengkel'           => 'fa-solid fa-wrench',
    'pendidikan'        => 'fa-solid fa-graduation-cap',
    'kesehatan'         => 'fa-solid fa-heart-pulse',
    'umkm-toko'         => 'fa-solid fa-store',
    'pasar'             => 'fa-solid fa-basket-shopping',
    'keamanan'          => 'fa-solid fa-shield-halved',
    'bank-atm'          => 'fa-solid fa-building-columns',
    'transportasi'      => 'fa-solid fa-bus',
    'olahraga-gor'      => 'fa-solid fa-futbol',
    'hotel-penginapan'  => 'fa-solid fa-hotel',
    'ibadah'            => 'fa-solid fa-mosque',
    'pemerintahan'      => 'fa-solid fa-landmark',
];

$all_kec = ['Ciamis', 'Panjalu', 'Kawali', 'Sadananya', 'Cikoneng', 'Sindangkasih', 'Rancah', 'Cijeungjing', 'Panumbangan', 'Baregbeg', 'Rajadesa', 'Banjaranyar', 'Pamarican', 'Banjarsari', 'Cipaku', 'Lumbung', 'Jatinagara', 'Sukadana', 'Cisaga', 'Tambaksari', 'Sukamantri', 'Cidolog', 'Cimaragas', 'Lakbok', 'Purwadadi', 'Mangunjaya'];

$msg   = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama'] ?? '');
    $kat_id    = (int)($_POST['kategori_id'] ?? 0);
    $deskripsi = trim($_POST['deskripsi'] ?? '');
    $alamat    = trim($_POST['alamat'] ?? '');
    $lat       = (float)($_POST['latitude'] ?? 0);
    $lng       = (float)($_POST['longitude'] ?? 0);
    $harga     = trim($_POST['harga'] ?? '');
    $wifi      = isset($_POST['wifi']) ? 1 : 0;
    $jam_buka  = trim($_POST['jam_buka'] ?? '');
    $jam_tutup = trim($_POST['jam_tutup'] ?? '');
    $no_telp   = trim($_POST['no_telepon'] ?? '');
    $status    = ($_POST['status'] ?? 'aktif') === 'nonaktif' ? 'nonaktif' : 'aktif';

    // 1. Kunci Kecamatan
    if ($is_regional) {
        $kecamatan = $user_wilayah;
    } else {
        $kecamatan = trim($_POST['kecamatan'] ?? $d['kecamatan']);
    }

    // 2. Validasi Batas Wilayah
    $out_of_bounds = false;
    if ($is_regional && isset($wilayah_configs[$user_wilayah])) {
        $b = $wilayah_configs[$user_wilayah]['bounds'];
        if ($lat < $b['min_lat'] || $lat > $b['max_lat'] || $lng < $b['min_lng'] || $lng > $b['max_lng']) {
            $out_of_bounds = true;
        }
    }

    if (empty($nama) || $kat_id < 1 || empty($lat) || empty($lng)) {
        $error = 'Nama, kategori, latitude, dan longitude wajib diisi secara lengkap.';
    } elseif ($out_of_bounds) {
        $error = "Peringatan: Posisi koordinat ($lat, $lng) berada di luar batas wilayah Kecamatan $user_wilayah. Akun Anda hanya berwenang di dalam wilayah $user_wilayah.";
    } else {
        // Upload foto baru jika ada
        $foto = $d['foto'] ?? null;
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
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

        $stmt = $db->prepare("UPDATE destinasi SET nama=?,kategori_id=?,kecamatan=?,deskripsi=?,alamat=?,latitude=?,longitude=?,harga=?,wifi=?,jam_buka=?,jam_tutup=?,no_telepon=?,status=?,foto=? WHERE id=?");
        $stmt->bind_param('sissssssisssssi', $nama, $kat_id, $kecamatan, $deskripsi, $alamat, $lat, $lng, $harga, $wifi, $jam_buka, $jam_tutup, $no_telp, $status, $foto, $id);
        if ($stmt->execute()) {
            $msg = 'Perubahan data destinasi berhasil disimpan ke sistem!';
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
<title>Edit: <?= htmlspecialchars($d['nama']) ?> - UlinKa Ciamis</title>
<link rel="icon" type="image/png" href="/ulinkaciamis/assets/images/lambang_ciamis.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="/ulinkaciamis/assets/css/admin.css">

<style>
/* Category Picker Grid */
.category-picker-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
  gap: 10px;
  margin-top: 8px;
}
.category-card-btn {
  background: #ffffff;
  border: 1.5px solid var(--border);
  border-radius: 12px;
  padding: 10px 8px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 6px;
  cursor: pointer;
  transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
  text-align: center;
  position: relative;
  user-select: none;
}
.category-card-btn:hover {
  border-color: #9e0b84;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(158, 11, 132, 0.08);
}
.category-card-btn.active {
  border-color: #9e0b84;
  background: #fdf2fa;
  box-shadow: 0 0 0 3px rgba(158, 11, 132, 0.15);
}
.category-card-icon {
  width: 34px;
  height: 34px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 15px;
  color: #ffffff;
  transition: transform 0.2s;
}
.category-card-btn.active .category-card-icon {
  transform: scale(1.1);
}
.category-card-label {
  font-size: 11.5px;
  font-weight: 700;
  color: var(--text-main);
  line-height: 1.25;
}
.category-card-btn.active .category-card-label {
  color: #9e0b84;
}
.category-check-badge {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background: #9e0b84;
  color: #ffffff;
  font-size: 9px;
  display: none;
  align-items: center;
  justify-content: center;
}
.category-card-btn.active .category-check-badge {
  display: flex;
}

/* Regional Lock Banner */
.regional-lock-banner {
  background: #f8fafc;
  border: 1.5px dashed #cbd5e1;
  border-radius: 12px;
  padding: 12px 16px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
}
.regional-lock-badge {
  background: #ecfdf5;
  border: 1px solid #a7f3d0;
  color: #065f46;
  font-size: 12px;
  font-weight: 800;
  padding: 4px 10px;
  border-radius: 8px;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

/* Map Interactive HUD */
.map-hud-container {
  position: relative;
  border-radius: 14px;
  overflow: hidden;
  border: 1.5px solid var(--border-strong);
  margin-top: 8px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.04);
}
.map-floating-hud {
  position: absolute;
  top: 12px;
  right: 12px;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.94);
  backdrop-filter: blur(8px);
  border: 1px solid var(--border);
  padding: 7px 12px;
  border-radius: 10px;
  font-size: 12px;
  font-weight: 700;
  color: #1e293b;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
  display: flex;
  align-items: center;
  gap: 8px;
  pointer-events: none;
}
.map-boundary-warning {
  position: absolute;
  bottom: 12px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 1000;
  background: #ef4444;
  color: #ffffff;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 800;
  box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
  display: none;
  align-items: center;
  gap: 8px;
  animation: pulseWarning 1.5s infinite;
}
@keyframes pulseWarning {
  0% { transform: translateX(-50%) scale(1); }
  50% { transform: translateX(-50%) scale(1.03); }
  100% { transform: translateX(-50%) scale(1); }
}
</style>
</head>
<body>
<?php include 'partials/sidebar.php'; ?>
<div class="admin-main">
  <?php include 'partials/topbar.php'; ?>
  <div class="admin-content">

    <div class="page-header">
      <div>
        <h1 class="page-title">Edit Destinasi &amp; Fasilitas</h1>
        <p class="page-sub">
          Memperbarui data: <strong><?= htmlspecialchars($d['nama']) ?></strong> (Kec. <?= htmlspecialchars($d['kecamatan']) ?>)
        </p>
      </div>
      <a href="/ulinkaciamis/admin/kelola.php" class="btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>

    <?php if($msg): ?><div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-error"><i class="fas fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="form-layout">
      <form method="POST" enctype="multipart/form-data" class="form-card" id="editForm">

        <!-- SECTION 1 -->
        <div class="form-section">
          <h3 class="form-section-title"><i class="fas fa-circle-info"></i> Informasi Dasar Tempat &amp; Logo Kategori</h3>
          
          <div class="form-grid">
            <div class="form-group full">
              <label>Nama Destinasi / Tempat / Fasilitas <span class="req">*</span></label>
              <input type="text" name="nama" value="<?= htmlspecialchars($d['nama']) ?>" required>
            </div>

            <!-- WILAYAH KECAMATAN -->
            <div class="form-group full">
              <label>Wilayah Kecamatan <span class="req">*</span></label>
              <?php if ($is_regional): ?>
                <div class="regional-lock-banner">
                  <div style="display:flex;align-items:center;gap:10px;">
                    <i class="fas fa-shield-halved" style="font-size:22px;color:#10b981;"></i>
                    <div>
                      <div style="font-size:13px;font-weight:700;color:var(--text-main);">Kecamatan <?= htmlspecialchars($user_wilayah) ?></div>
                      <div style="font-size:11px;color:var(--text-muted);">Akun Anda hanya berwenang mengelola data di wilayah penugasan ini.</div>
                    </div>
                  </div>
                  <div class="regional-lock-badge">
                    <i class="fas fa-lock"></i> Terkunci (Wilayah Anda)
                  </div>
                </div>
                <input type="hidden" name="kecamatan" value="<?= htmlspecialchars($user_wilayah) ?>">
              <?php else: ?>
                <select name="kecamatan" id="selectKecamatan" required>
                  <?php foreach($all_kec as $kc): ?>
                    <option value="<?= $kc ?>" <?= $d['kecamatan'] === $kc ? 'selected' : '' ?>>Kec. <?= $kc ?></option>
                  <?php endforeach; ?>
                </select>
                <small class="form-hint">Sebagai Super Admin, Anda bebas memindahkan kecamatan jika diperlukan.</small>
              <?php endif; ?>
            </div>

            <!-- PILIH LOGO & KATEGORI SECARA VISUAL -->
            <div class="form-group full">
              <label>Kategori &amp; Logo Pin <span class="req">*</span></label>
              <input type="hidden" name="kategori_id" id="selectedKategoriId" value="<?= htmlspecialchars($d['kategori_id']) ?>" required>
              
              <div class="category-picker-grid">
                <?php foreach($kategori_list as $k): 
                  $slug = $k['slug'];
                  $icon = $kategori_icons[$slug] ?? 'fa-solid fa-map-pin';
                  $warna = $k['warna'] ?: '#3B82F6';
                  $isActive = ($d['kategori_id'] == $k['id']);
                ?>
                  <div class="category-card-btn <?= $isActive ? 'active' : '' ?>" data-id="<?= $k['id'] ?>" data-slug="<?= $slug ?>" data-color="<?= $warna ?>" onclick="selectCategory(<?= $k['id'] ?>, '<?= $slug ?>', '<?= $warna ?>')">
                    <div class="category-check-badge"><i class="fas fa-check"></i></div>
                    <div class="category-card-icon" style="background-color: <?= $warna ?>;">
                      <i class="<?= $icon ?>"></i>
                    </div>
                    <span class="category-card-label"><?= htmlspecialchars($k['nama']) ?></span>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="form-group">
              <label>Status Penayangan di Peta</label>
              <select name="status">
                <option value="aktif" <?= $d['status'] === 'aktif' ? 'selected' : '' ?>>Aktif (Ditampilkan di peta publik)</option>
                <option value="nonaktif" <?= $d['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif (Disembunyikan)</option>
              </select>
            </div>

            <div class="form-group">
              <label>Foto Tempat Saat Ini</label>
              <?php if($d['foto']): ?>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
                  <img src="<?= htmlspecialchars($d['foto']) ?>" style="width:60px;height:45px;border-radius:6px;object-fit:cover;border:1px solid var(--border);">
                  <span style="font-size:11.5px;color:var(--text-muted);word-break:break-all;"><?= htmlspecialchars(basename($d['foto'])) ?></span>
                </div>
              <?php endif; ?>
              <input type="file" name="foto" accept="image/*">
              <small class="form-hint">Pilih gambar baru hanya jika ingin mengganti foto di atas.</small>
            </div>

            <div class="form-group full">
              <label>Alamat Lengkap</label>
              <input type="text" name="alamat" value="<?= htmlspecialchars($d['alamat'] ?? '') ?>" placeholder="Alamat detail...">
            </div>

            <div class="form-group full">
              <label>Deskripsi Ringkas</label>
              <textarea name="deskripsi" rows="3"><?= htmlspecialchars($d['deskripsi'] ?? '') ?></textarea>
            </div>
          </div>
        </div>

        <!-- SECTION 2: MAP & DRAGGABLE PIN REAL-TIME -->
        <div class="form-section">
          <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <h3 class="form-section-title" style="margin-bottom:0;"><i class="fas fa-location-crosshairs"></i> Posisi Koordinat Peta (Pin Interaktif)</h3>
            <span style="font-size:12px;background:#fdf2fa;color:#9e0b84;padding:3px 10px;border-radius:20px;font-weight:700;">
              <i class="fas fa-hand-pointer"></i> Drag &amp; Drop Pin Didukung
            </span>
          </div>

          <p class="form-hint" style="margin-top:6px;">
            <i class="fas fa-circle-info"></i> <strong>Instruksi Petugas:</strong> Geser pin di atas peta untuk memperbarui titik koordinat secara langsung. Nilai Latitude &amp; Longitude akan terisi otomatis.
          </p>

          <div class="form-grid" style="margin-top:12px;">
            <div class="form-group">
              <label>Latitude <span class="req">*</span></label>
              <input type="text" name="latitude" id="inputLat" value="<?= htmlspecialchars($d['latitude']) ?>" required>
            </div>
            <div class="form-group">
              <label>Longitude <span class="req">*</span></label>
              <input type="text" name="longitude" id="inputLng" value="<?= htmlspecialchars($d['longitude']) ?>" required>
            </div>
          </div>

          <!-- Shortcuts -->
          <div style="display:flex;align-items:center;gap:6px;margin:12px 0 6px;flex-wrap:wrap;">
            <span style="font-size:11.5px;color:var(--text-muted);font-weight:700;"><i class="fas fa-bolt text-amber-500"></i> Fokus Titik Cepat:</span>
            <?php foreach($active_cfg['shortcuts'] as $sc): ?>
              <button type="button" onclick="jumpTo(<?= $sc['lat'] ?>, <?= $sc['lng'] ?>)" class="btn-ghost-sm">
                <?= htmlspecialchars($sc['name']) ?>
              </button>
            <?php endforeach; ?>
          </div>

          <!-- Peta Leaflet dengan Floating HUD -->
          <div class="map-hud-container">
            <div class="map-floating-hud">
              <i class="fas fa-arrows-up-down-left-right text-emerald-600"></i>
              <span>Pin Aktif: <strong id="hudCoords" style="font-family:monospace;"><?= $d['latitude'] ?>, <?= $d['longitude'] ?></strong></span>
            </div>

            <div class="map-boundary-warning" id="boundaryWarning">
              <i class="fas fa-triangle-exclamation"></i>
              <span>Pin berada di luar batas wilayah <?= htmlspecialchars($user_wilayah ?: 'Ciamis') ?>! Geser kembali ke dalam area.</span>
            </div>

            <div id="pickMap" style="height:380px;width:100%;"></div>
          </div>
        </div>

        <!-- SECTION 3 -->
        <div class="form-section">
          <h3 class="form-section-title"><i class="fas fa-clock"></i> Biaya &amp; Jam Operasional</h3>
          <div class="form-grid">
            <div class="form-group">
              <label>Biaya Masuk / Tarif / Harga</label>
              <input type="text" name="harga" value="<?= htmlspecialchars($d['harga'] ?? '') ?>" placeholder="Contoh: Gratis / Rp 10.000">
            </div>
            <div class="form-group">
              <label>Nomor Telepon / WhatsApp</label>
              <input type="text" name="no_telepon" value="<?= htmlspecialchars($d['no_telepon'] ?? '') ?>" placeholder="Nomor kontak...">
            </div>
            <div class="form-group">
              <label>Jam Buka</label>
              <input type="time" name="jam_buka" value="<?= htmlspecialchars($d['jam_buka'] ? substr($d['jam_buka'],0,5) : '') ?>">
            </div>
            <div class="form-group">
              <label>Jam Tutup</label>
              <input type="time" name="jam_tutup" value="<?= htmlspecialchars($d['jam_tutup'] ? substr($d['jam_tutup'],0,5) : '') ?>">
            </div>
            <div class="form-group full">
              <label class="switch-label">
                <div class="switch-wrap">
                  <input type="checkbox" name="wifi" id="wifiSwitch" <?= $d['wifi'] ? 'checked' : '' ?>>
                  <span class="switch-slider"></span>
                </div>
                <span style="font-size:13px;font-weight:600;color:var(--text-main);">Tersedia Fasilitas Akses Internet / WiFi</span>
              </label>
            </div>
          </div>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn-primary btn-lg" id="submitBtn">
            <i class="fas fa-floppy-disk"></i> Simpan Perubahan
          </button>
          <a href="/ulinkaciamis/admin/kelola.php" class="btn-secondary">Batal</a>
        </div>

      </form>
    </div>

  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const isRegional = <?= json_encode($is_regional) ?>;
const userWilayah = <?= json_encode($user_wilayah) ?>;
const wilayahBounds = <?= json_encode($active_cfg['bounds']) ?>;
const initialLat = <?= (float)$d['latitude'] ?>;
const initialLng = <?= (float)$d['longitude'] ?>;

const pickMap = L.map('pickMap', {
  center: [initialLat, initialLng],
  zoom: 16
});

L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
  maxZoom: 20,
  subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
  attribution: '&copy; Google Maps'
}).addTo(pickMap);

const inputLat = document.getElementById('inputLat');
const inputLng = document.getElementById('inputLng');
const hudCoords = document.getElementById('hudCoords');
const boundaryWarning = document.getElementById('boundaryWarning');
const submitBtn = document.getElementById('submitBtn');

function createCustomPin(color = '#9e0b84') {
  return L.divIcon({
    className: 'custom-admin-drag-pin',
    html: `
      <div style="
        width: 38px;
        height: 38px;
        background: ${color};
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.35);
        border: 2.5px solid #ffffff;
        cursor: grab;
      ">
        <i class="fa-solid fa-location-dot" style="transform: rotate(45deg); color: #ffffff; font-size: 17px;"></i>
      </div>
    `,
    iconSize: [38, 38],
    iconAnchor: [19, 38]
  });
}

function checkBoundary(lat, lng) {
  if (!isRegional || !wilayahBounds) return true;
  const isInside = (
    lat >= wilayahBounds.min_lat &&
    lat <= wilayahBounds.max_lat &&
    lng >= wilayahBounds.min_lng &&
    lng <= wilayahBounds.max_lng
  );

  if (!isInside) {
    boundaryWarning.style.display = 'flex';
    submitBtn.style.opacity = '0.5';
    submitBtn.style.pointerEvents = 'none';
    hudCoords.style.color = '#ef4444';
  } else {
    boundaryWarning.style.display = 'none';
    submitBtn.style.opacity = '1';
    submitBtn.style.pointerEvents = 'auto';
    hudCoords.style.color = '#059669';
  }
  return isInside;
}

function updateCoords(lat, lng) {
  inputLat.value = lat.toFixed(7);
  inputLng.value = lng.toFixed(7);
  hudCoords.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
  checkBoundary(lat, lng);
}

// Inisialisasi Marker Draggable
let pickMarker = L.marker([initialLat, initialLng], {
  draggable: true,
  autoPan: true,
  icon: createCustomPin('#9e0b84')
}).addTo(pickMap);

updateCoords(initialLat, initialLng);

// LISTENER: DRAGGING REAL TIME
pickMarker.on('drag', function(e) {
  const pos = e.target.getLatLng();
  updateCoords(pos.lat, pos.lng);
});

// LISTENER: DRAG END
pickMarker.on('dragend', function(e) {
  const pos = e.target.getLatLng();
  updateCoords(pos.lat, pos.lng);
});

// LISTENER: CLICK
pickMap.on('click', function(e) {
  pickMarker.setLatLng(e.latlng);
  updateCoords(e.latlng.lat, e.latlng.lng);
});

function jumpTo(lat, lng) {
  pickMap.setView([lat, lng], 16);
  pickMarker.setLatLng([lat, lng]);
  updateCoords(lat, lng);
}

// MANUAL INPUT
[inputLat, inputLng].forEach(el => {
  el.addEventListener('input', () => {
    const lat = parseFloat(inputLat.value);
    const lng = parseFloat(inputLng.value);
    if (!isNaN(lat) && !isNaN(lng)) {
      pickMarker.setLatLng([lat, lng]);
      pickMap.panTo([lat, lng]);
      updateCoords(lat, lng);
    }
  });
});

function selectCategory(id, slug, color) {
  document.getElementById('selectedKategoriId').value = id;
  document.querySelectorAll('.category-card-btn').forEach(btn => {
    btn.classList.toggle('active', btn.dataset.id == id);
  });
  if (pickMarker) {
    pickMarker.setIcon(createCustomPin(color));
  }
}
</script>
</body>
</html>

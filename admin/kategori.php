<?php
require_once __DIR__ . '/../config.php';
requireLogin();

$db = getDB();
$msg = '';
$error = '';

// Handle Delete
if (isset($_GET['hapus'])) {
    $del_id = (int)$_GET['hapus'];
    // Check if category has destinations
    $check = $db->query("SELECT COUNT(*) AS total FROM destinasi WHERE kategori_id = $del_id")->fetch_assoc();
    if ($check && $check['total'] > 0) {
        $error = "Kategori tidak dapat dihapus karena masih digunakan oleh {$check['total']} destinasi.";
    } else {
        $stmt = $db->prepare("DELETE FROM kategori WHERE id = ?");
        $stmt->bind_param('i', $del_id);
        if ($stmt->execute()) {
            $msg = "Kategori berhasil dihapus.";
        } else {
            $error = "Gagal menghapus: " . $db->error;
        }
    }
}

// Handle Add / Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'tambah';
    $nama   = trim($_POST['nama'] ?? '');
    $slug   = trim($_POST['slug'] ?? '');
    $warna  = trim($_POST['warna'] ?? '#7a0d66');
    $urutan = (int)($_POST['urutan'] ?? 0);

    if (empty($slug) && !empty($nama)) {
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $nama));
    }

    if (empty($nama) || empty($slug)) {
        $error = "Nama kategori dan slug wajib diisi.";
    } else {
        if ($action === 'tambah') {
            $stmt = $db->prepare("INSERT INTO kategori (nama, slug, warna, urutan) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('sssi', $nama, $slug, $warna, $urutan);
            if ($stmt->execute()) {
                $msg = "Kategori '{$nama}' berhasil ditambahkan!";
            } else {
                $error = "Gagal menambah: " . $db->error;
            }
        } elseif ($action === 'edit') {
            $id = (int)($_POST['id'] ?? 0);
            $stmt = $db->prepare("UPDATE kategori SET nama = ?, slug = ?, warna = ?, urutan = ? WHERE id = ?");
            $stmt->bind_param('sssii', $nama, $slug, $warna, $urutan, $id);
            if ($stmt->execute()) {
                $msg = "Kategori '{$nama}' berhasil diperbarui!";
            } else {
                $error = "Gagal memperbarui: " . $db->error;
            }
        }
    }
}

$categories = $db->query("
    SELECT k.*, COUNT(d.id) AS total_destinasi 
    FROM kategori k 
    LEFT JOIN destinasi d ON k.id = d.kategori_id 
    GROUP BY k.id 
    ORDER BY k.urutan ASC, k.nama ASC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kelola Kategori - UlinKa Ciamis</title>
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
        <h1 class="page-title">Kelola Kategori Wisata &amp; Fasilitas</h1>
        <p class="page-sub">Kelola klasifikasi destinasi untuk penyaringan di direktori dan peta publik Tatar Galuh</p>
      </div>
      <button class="btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Tambah Kategori Baru</button>
    </div>

    <?php if($msg): ?><div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-error"><i class="fas fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="table-card">
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th style="width:40px;">No</th>
              <th>Nama Kategori</th>
              <th>Slug Sistem</th>
              <th>Warna Label</th>
              <th>Urutan</th>
              <th>Total Destinasi</th>
              <th style="text-align:right;">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($categories as $index => $cat): ?>
          <tr>
            <td style="color:var(--text-muted);font-weight:600;font-size:12px;"><?= $index + 1 ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:10px;">
                <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:<?= htmlspecialchars($cat['warna']) ?>;box-shadow:0 0 0 2px rgba(0,0,0,0.06);"></span>
                <strong style="color:var(--text-main);font-size:13.5px;"><?= htmlspecialchars($cat['nama']) ?></strong>
              </div>
            </td>
            <td>
              <code style="background:#f1f5f9;padding:3px 7px;border-radius:4px;font-size:12px;color:var(--text-body);border:1px solid var(--border);">
                <?= htmlspecialchars($cat['slug']) ?>
              </code>
            </td>
            <td>
              <span class="badge" style="background:<?= htmlspecialchars($cat['warna']) ?>18;color:<?= htmlspecialchars($cat['warna']) ?>;border:1px solid <?= htmlspecialchars($cat['warna']) ?>40;">
                <?= htmlspecialchars($cat['warna']) ?>
              </span>
            </td>
            <td><span style="font-weight:600;color:var(--text-body);"><?= (int)$cat['urutan'] ?></span></td>
            <td>
              <a href="/ulinkaciamis/admin/kelola.php?kat=<?= $cat['id'] ?>" class="badge" style="background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;text-decoration:none;">
                <i class="fas fa-map-pin" style="font-size:10px;"></i> <?= $cat['total_destinasi'] ?> Tempat
              </a>
            </td>
            <td style="text-align:right;">
              <div class="tbl-actions" style="justify-content:flex-end;">
                <button class="tbl-btn edit" title="Edit Kategori" onclick='openEditModal(<?= json_encode($cat) ?>)'><i class="fas fa-pen-to-square"></i></button>
                <a href="/ulinkaciamis/admin/kategori.php?hapus=<?= $cat['id'] ?>" class="tbl-btn del" title="Hapus Kategori" onclick="return confirm('Hapus kategori <?= addslashes(htmlspecialchars($cat['nama'])) ?>?')"><i class="fas fa-trash-can"></i></a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- MODAL FORM -->
<div class="modal-overlay" id="catModal">
  <div class="modal-card">
    <div class="modal-header">
      <h3 class="modal-title" id="modalTitle">Tambah Kategori</h3>
      <button type="button" class="modal-close" onclick="closeModal()"><i class="fas fa-xmark"></i></button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="action" id="formAction" value="tambah">
        <input type="hidden" name="id" id="catId" value="">

        <div class="form-group" style="margin-bottom:16px;">
          <label>Nama Kategori <span class="req">*</span></label>
          <input type="text" name="nama" id="catNama" placeholder="Contoh: Wisata Religi & Budaya" required oninput="autoSlug(this.value)">
        </div>

        <div class="form-group" style="margin-bottom:16px;">
          <label>Slug URL <span class="req">*</span></label>
          <input type="text" name="slug" id="catSlug" placeholder="cth: wisata-religi-budaya" required>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
          <div class="form-group">
            <label>Warna Aksen</label>
            <div style="display:flex;align-items:center;gap:8px;">
              <input type="color" name="warna" id="catWarna" value="#7a0d66" style="width:42px;height:38px;padding:2px;cursor:pointer;border-radius:6px;border:1px solid var(--border-strong);">
              <input type="text" id="catWarnaText" value="#7a0d66" style="flex:1;" oninput="document.getElementById('catWarna').value=this.value">
            </div>
          </div>
          <div class="form-group">
            <label>Urutan Tampil</label>
            <input type="number" name="urutan" id="catUrutan" value="0" min="0" style="padding:9px 13px;border:1px solid var(--border-strong);border-radius:var(--radius-sm);outline:none;">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-secondary" onclick="closeModal()">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmit"><i class="fas fa-floppy-disk"></i> Simpan Kategori</button>
      </div>
    </form>
  </div>
</div>

<script>
const modal = document.getElementById('catModal');
const catWarna = document.getElementById('catWarna');
const catWarnaText = document.getElementById('catWarnaText');

catWarna.addEventListener('input', e => {
  catWarnaText.value = e.target.value;
});

function openAddModal() {
  document.getElementById('modalTitle').textContent = 'Tambah Kategori Baru';
  document.getElementById('formAction').value = 'tambah';
  document.getElementById('catId').value = '';
  document.getElementById('catNama').value = '';
  document.getElementById('catSlug').value = '';
  document.getElementById('catWarna').value = '#7a0d66';
  document.getElementById('catWarnaText').value = '#7a0d66';
  document.getElementById('catUrutan').value = '0';
  document.getElementById('btnSubmit').innerHTML = '<i class="fas fa-floppy-disk"></i> Simpan Kategori';
  modal.classList.add('open');
}

function openEditModal(cat) {
  document.getElementById('modalTitle').textContent = 'Edit Kategori: ' + cat.nama;
  document.getElementById('formAction').value = 'edit';
  document.getElementById('catId').value = cat.id;
  document.getElementById('catNama').value = cat.nama;
  document.getElementById('catSlug').value = cat.slug;
  document.getElementById('catWarna').value = cat.warna || '#7a0d66';
  document.getElementById('catWarnaText').value = cat.warna || '#7a0d66';
  document.getElementById('catUrutan').value = cat.urutan || '0';
  document.getElementById('btnSubmit').innerHTML = '<i class="fas fa-floppy-disk"></i> Simpan Perubahan';
  modal.classList.add('open');
}

function closeModal() {
  modal.classList.remove('open');
}

function autoSlug(text) {
  if (document.getElementById('formAction').value === 'tambah') {
    document.getElementById('catSlug').value = text
      .toLowerCase()
      .trim()
      .replace(/[^\w\s-]/g, '')
      .replace(/[\s_-]+/g, '-')
      .replace(/^-+|-+$/g, '');
  }
}

modal.addEventListener('click', e => {
  if (e.target === modal) closeModal();
});
</script>
</body>
</html>

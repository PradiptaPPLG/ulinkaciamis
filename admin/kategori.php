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
    $warna  = trim($_POST['warna'] ?? '#3B82F6');
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
        <h1 class="page-title">Kelola Kategori Fasilitas & Wisata</h1>
        <p class="page-sub">Atur kategori destinasi dan fasilitas di wilayah Ciamis</p>
      </div>
      <button class="btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Tambah Kategori</button>
    </div>

    <?php if($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($msg) ?></div><?php endif; ?>
    <?php if($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?></div><?php endif; ?>

    <div class="table-card">
      <div class="table-wrap">
        <table class="data-table">
          <thead>
            <tr>
              <th>Urutan</th>
              <th>Nama Kategori</th>
              <th>Slug / Kode</th>
              <th>Warna Badge</th>
              <th>Jumlah Tempat</th>
              <th style="text-align:right">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($categories as $c): ?>
            <tr>
              <td><span class="badge" style="background:rgba(255,255,255,.05);color:#94a3b8">#<?= $c['urutan'] ?></span></td>
              <td><strong><?= htmlspecialchars($c['nama']) ?></strong></td>
              <td><code><?= htmlspecialchars($c['slug']) ?></code></td>
              <td>
                <div style="display:flex;align-items:center;gap:8px">
                  <span style="display:inline-block;width:16px;height:16px;border-radius:4px;background:<?= htmlspecialchars($c['warna']?:'#3b82f6') ?>;border:1px solid rgba(255,255,255,.2)"></span>
                  <span style="font-size:12px;color:#94a3b8"><?= htmlspecialchars($c['warna']?:'#3b82f6') ?></span>
                </div>
              </td>
              <td><span class="badge badge-active"><?= $c['total_destinasi'] ?> tempat</span></td>
              <td style="text-align:right">
                <button class="btn-sm btn-edit" onclick="editKat(<?= htmlspecialchars(json_encode($c)) ?>)"><i class="fas fa-pencil"></i></button>
                <?php if($c['total_destinasi'] == 0): ?>
                <a href="?hapus=<?= $c['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Hapus kategori <?= htmlspecialchars($c['nama']) ?>?')"><i class="fas fa-trash"></i></a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Form -->
<div id="katModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);backdrop-filter:blur(4px);z-index:999;align-items:center;justify-content:center;">
  <div style="background:#131B2E;border:1px solid rgba(255,255,255,.1);border-radius:16px;width:90%;max-width:480px;padding:24px;">
    <h3 id="modalTitle" style="margin-top:0;margin-bottom:16px;font-size:18px">Tambah Kategori</h3>
    <form method="POST" id="katForm">
      <input type="hidden" name="action" id="formAction" value="tambah">
      <input type="hidden" name="id" id="formId" value="">
      
      <div class="form-group" style="margin-bottom:14px">
        <label>Nama Kategori <span class="req">*</span></label>
        <input type="text" name="nama" id="formNama" placeholder="cth: Minimarket" required style="width:100%;box-sizing:border-box">
      </div>
      <div class="form-group" style="margin-bottom:14px">
        <label>Slug URL / Kode <span class="req">*</span></label>
        <input type="text" name="slug" id="formSlug" placeholder="cth: minimarket" required style="width:100%;box-sizing:border-box">
      </div>
      <div class="form-group" style="margin-bottom:14px">
        <label>Warna Tema</label>
        <input type="color" name="warna" id="formWarna" value="#3B82F6" style="width:100%;height:40px;background:transparent;border:1px solid rgba(255,255,255,.1);border-radius:8px;cursor:pointer">
      </div>
      <div class="form-group" style="margin-bottom:20px">
        <label>Nomor Urutan</label>
        <input type="number" name="urutan" id="formUrutan" value="1" style="width:100%;box-sizing:border-box">
      </div>

      <div style="display:flex;justify-content:flex-end;gap:10px">
        <button type="button" class="btn-ghost" onclick="closeModal()">Batal</button>
        <button type="submit" class="btn-primary" id="btnSubmitModal">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddModal() {
  document.getElementById('modalTitle').textContent = 'Tambah Kategori';
  document.getElementById('formAction').value = 'tambah';
  document.getElementById('formId').value = '';
  document.getElementById('formNama').value = '';
  document.getElementById('formSlug').value = '';
  document.getElementById('formWarna').value = '#3B82F6';
  document.getElementById('formUrutan').value = '1';
  document.getElementById('katModal').style.display = 'flex';
}

function editKat(item) {
  document.getElementById('modalTitle').textContent = 'Edit Kategori';
  document.getElementById('formAction').value = 'edit';
  document.getElementById('formId').value = item.id;
  document.getElementById('formNama').value = item.nama;
  document.getElementById('formSlug').value = item.slug;
  document.getElementById('formWarna').value = item.warna || '#3B82F6';
  document.getElementById('formUrutan').value = item.urutan;
  document.getElementById('katModal').style.display = 'flex';
}

function closeModal() {
  document.getElementById('katModal').style.display = 'none';
}

document.getElementById('formNama').addEventListener('input', function(e) {
  if (document.getElementById('formAction').value === 'tambah') {
    document.getElementById('formSlug').value = e.target.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
  }
});
</script>
</body>
</html>

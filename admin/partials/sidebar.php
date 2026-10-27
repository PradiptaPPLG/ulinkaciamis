<?php
$current_page = basename($_SERVER['PHP_SELF']);
function navActive($pages, $current) {
    return in_array($current, (array)$pages) ? 'active' : '';
}
?>
<aside class="admin-sidebar">
    <div class="sidebar-brand" style="display:flex;align-items:center;gap:10px;padding:16px 20px;">
    <img src="/ulinkaciamis/public/images/ulinkaCiamis_cropped.webp" alt="UlinKa Ciamis" style="height:34px;width:auto;object-fit:contain;" />
  </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-group-label">Utama</div>
    <a href="/ulinkaciamis/admin/dashboard.php" class="nav-item <?= navActive('dashboard.php', $current_page) ?>">
      <i class="fas fa-gauge-high"></i> Dashboard
    </a>
    <a href="/ulinkaciamis/admin/kelola.php" class="nav-item <?= navActive(['kelola.php','edit.php'], $current_page) ?>">
      <i class="fas fa-list-ul"></i> Kelola Destinasi
    </a>
    <a href="/ulinkaciamis/admin/tambah.php" class="nav-item <?= navActive('tambah.php', $current_page) ?>">
      <i class="fas fa-plus-circle"></i> Tambah Destinasi
    </a>
    <a href="/ulinkaciamis/admin/kategori.php" class="nav-item <?= navActive('kategori.php', $current_page) ?>">
      <i class="fas fa-tags"></i> Kelola Kategori
    </a>

    <div class="nav-group-label" style="margin-top:16px">Lainnya</div>
    <a href="/ulinkaciamis/" class="nav-item" target="_blank">
      <i class="fas fa-map"></i> Lihat Peta Publik
    </a>
    <a href="/ulinkaciamis/admin/logout.php" class="nav-item nav-logout">
      <i class="fas fa-right-from-bracket"></i> Keluar
    </a>
  </nav>

  <div class="sidebar-user">
    <div class="user-avatar"><?= strtoupper(substr($_SESSION['admin_nama']??'A',0,1)) ?></div>
    <div class="user-info">
      <div class="user-name"><?= htmlspecialchars($_SESSION['admin_nama']??'Admin') ?></div>
      <div class="user-role"><?= ($_SESSION['admin_role']??'')==='superadmin' ? '⭐ Super Admin Kabupaten' : '🏘️ Pengurus ' . htmlspecialchars($_SESSION['admin_wilayah'] ?? 'Wilayah') ?></div>
    </div>
  </div>
</aside>

<div class="admin-topbar">
  <button class="topbar-menu-btn" id="mobileSidebarBtn"><i class="fas fa-bars"></i></button>
  <div class="topbar-right">
    <a href="/ulinkaciamis/" target="_blank" class="topbar-link">
      <i class="fas fa-external-link"></i> Lihat Peta
    </a>
    <span class="topbar-user"><?= htmlspecialchars($_SESSION['admin_nama']??'') ?></span>
  </div>
</div>
<script>
document.getElementById('mobileSidebarBtn')?.addEventListener('click', () => {
  document.querySelector('.admin-sidebar')?.classList.toggle('open');
});
</script>

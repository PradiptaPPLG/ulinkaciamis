<?php
require_once __DIR__ . '/../config.php';
session_destroy();
header('Location: /ulinkaciamis/admin/login.php?logout=1');
exit;
?>

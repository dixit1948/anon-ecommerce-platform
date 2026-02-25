<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Destroy admin session only (keep user session)
unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email']);
header('Location: ' . SITE_URL . '/admin/login.php');
exit;

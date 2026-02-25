<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

session_destroy();
setcookie(session_name(), '', time() - 3600, '/');
header('Location: ' . SITE_URL . '/user/login.php');
exit;

<?php
// Definisikan konstanta untuk direktori saat ini
define('BASE_PATH', dirname(__DIR__));
define('ADMIN_PATH', __DIR__);

// Mulai session
session_start();

// Load konfigurasi dan fungsi
require_once BASE_PATH . '/includes/config.php';
require_once ADMIN_PATH . '/includes/auth.php';
require_once ADMIN_PATH . '/includes/functions.php';

// Cek login admin
requireLogin();

// Set judul halaman
$page_title = 'Dashboard';

// Redirect ke index.php di modules/dashboard
header('Location: ' . $site_config['admin_url'] . '/modules/dashboard/index.php');
exit;
?>
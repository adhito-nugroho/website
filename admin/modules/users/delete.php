<?php
// Definisikan konstanta untuk direktori
define('BASE_PATH', dirname(dirname(dirname(__DIR__))));
define('ADMIN_PATH', dirname(dirname(__DIR__)));

// Mulai session
session_start();

// Load konfigurasi dan fungsi
require_once BASE_PATH . '/includes/config.php';
require_once ADMIN_PATH . '/includes/auth.php';
require_once ADMIN_PATH . '/includes/functions.php';

// Cek login admin
requireLogin();

// Cek role admin
requireRole('admin');

// Validasi parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = 'ID pengguna tidak valid';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];

// Cek apakah ID yang dihapus adalah admin yang sedang login
if ($id == $_SESSION['admin_id']) {
    $_SESSION['message'] = 'Anda tidak dapat menghapus akun yang sedang digunakan';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// Cek apakah data pengguna ada
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['message'] = 'Pengguna tidak ditemukan';
        $_SESSION['message_type'] = 'danger';
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['message'] = 'Error: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// Proses penghapusan
try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['message'] = 'Pengguna berhasil dihapus';
    $_SESSION['message_type'] = 'success';
} catch (PDOException $e) {
    $_SESSION['message'] = 'Error saat menghapus pengguna: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

// Redirect kembali ke halaman index
header('Location: index.php');
exit;
?>
 
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

// Validasi parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = 'ID pesan tidak valid';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];

// Cek apakah data pesan ada
try {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    $message = $stmt->fetch();

    if (!$message) {
        $_SESSION['message'] = 'Pesan tidak ditemukan';
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
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->execute([$id]);

    // Log aktivitas
    if (isset($_SESSION['admin_id'])) {
        logActivity($_SESSION['admin_id'], 'menghapus pesan', [
            'message_id' => $id,
            'from' => $message['name'],
            'email' => $message['email']
        ]);
    }

    $_SESSION['message'] = 'Pesan berhasil dihapus';
    $_SESSION['message_type'] = 'success';
} catch (PDOException $e) {
    $_SESSION['message'] = 'Error saat menghapus pesan: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

// Redirect kembali ke halaman index
header('Location: index.php');
exit;
?>
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
    $_SESSION['message'] = 'ID foto tidak valid';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];

// Cek apakah data galeri ada
try {
    $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
    $stmt->execute([$id]);
    $gallery = $stmt->fetch();

    if (!$gallery) {
        $_SESSION['message'] = 'Foto tidak ditemukan';
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
    // Hapus file gambar
    if (!empty($gallery['image'])) {
        deleteImage($gallery['image'], 'uploads/galeri');
    }
    
    // Hapus data dari database
    $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->execute([$id]);

    // Log aktivitas
    if (isset($_SESSION['admin_id'])) {
        logActivity($_SESSION['admin_id'], 'menghapus foto dari galeri', [
            'gallery_id' => $id,
            'title' => $gallery['title']
        ]);
    }

    $_SESSION['message'] = 'Foto berhasil dihapus dari galeri';
    $_SESSION['message_type'] = 'success';
} catch (PDOException $e) {
    $_SESSION['message'] = 'Error saat menghapus foto: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
}

// Redirect kembali ke halaman index
header('Location: index.php');
exit;
?>
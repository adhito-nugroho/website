<?php
// Hanya aktifkan tampilan error saat development
if (getenv('APP_ENV') === 'development' || $_SERVER['SERVER_NAME'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    // Untuk lingkungan produksi, matikan tampilan error
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

// Definisikan konstanta BASE_PATH
define('BASE_PATH', dirname(__FILE__));

// Mulai session
session_start();

// Load konfigurasi dan functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Definisikan error handler global
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    // Log error tapi jangan tampilkan ke pengguna
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    // Return false untuk penanganan error default PHP
    // Return true untuk menekan error
    return true;
}

// Set error handler kustom
set_error_handler('custom_error_handler');

// PENTING: Pastikan variabel $pdo sudah tersedia dari config.php
if (!isset($pdo) || !($pdo instanceof PDO)) {
    die("Error fatal: Koneksi database tidak tersedia. Periksa file config.php");
}

// Dapatkan halaman yang diminta
$page = isset($_GET['page']) ? sanitizeInput($_GET['page']) : 'beranda';

// Validasi halaman yang diizinkan
$allowed_pages = ['beranda', 'profil', 'layanan', 'program', 'statistik', 'monitoring', 'publikasi', 'galeri', 'kontak'];
if (!in_array($page, $allowed_pages)) {
    $page = 'beranda';
}

// Cek apakah halaman memiliki parameter view atau id
$has_specific_view = (isset($_GET['view']) || isset($_GET['id']));

// Load header
include_once 'includes/header.php';

// PENTING: Pastikan variabel $pdo masih tersedia setelah header
if (!isset($pdo) || !($pdo instanceof PDO)) {
    // Coba reload koneksi database jika hilang
    try {
        $pdo = new PDO(
            "mysql:host={$db_config['host']};dbname={$db_config['database']};charset={$db_config['charset']}",
            $db_config['username'],
            $db_config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    } catch (PDOException $e) {
        die("Koneksi database gagal: " . $e->getMessage());
    }
}

// Mulai output buffering untuk menangkap error
ob_start();

if ($page === 'beranda' || !$has_specific_view) {
    // Jika halaman beranda atau tidak ada parameter view/id,
    // muat semua section seperti biasa
    include_once 'modules/beranda.php';        // Hero section & floating stats
    include_once 'modules/profil.php';         // Profil section
    include_once 'modules/layanan.php';        // Layanan section
    include_once 'modules/program.php';        // Program section
    include_once 'modules/statistik.php';      // Statistik section
    include_once 'modules/monitoring.php';     // Monitoring section
    include_once 'modules/publikasi.php';      // Publikasi section
    include_once 'modules/galeri.php';         // Galeri section
    include_once 'modules/kontak.php';         // Kontak section
} else {
    // Jika halaman spesifik dengan parameter view/id,
    // muat hanya modul yang diminta
    switch ($page) {
        case 'publikasi':
            try {
                // Validasi parameter view terlebih dahulu
                if (isset($_GET['view']) && !in_array($_GET['view'], ['all', 'documents'])) {
                    // Jika parameter tidak valid, reset
                    $_GET['view'] = null;
                }
                include_once 'modules/publikasi.php';
            } catch (Throwable $e) {
                error_log('Error in publikasi module: ' . $e->getMessage());
                include_once 'includes/fallback.php';
            }
            break;
        case 'galeri':
            include_once 'modules/galeri.php';
            break;
        case 'profil':
            include_once 'modules/profil.php';
            break;
        case 'layanan':
            include_once 'modules/layanan.php';
            break;
        case 'program':
            include_once 'modules/program.php';
            break;
        case 'statistik':
            include_once 'modules/statistik.php';
            break;
        case 'monitoring':
            include_once 'modules/monitoring.php';
            break;
        case 'kontak':
            include_once 'modules/kontak.php';
            break;
        default:
            // Jika halaman tidak dikenali, redirect ke beranda
            header('Location: index.php');
            exit;
    }
}

// Ambil konten yang dibuffer dan filter error
$content = ob_get_clean();

// Hapus pesan error yang mungkin muncul
$content = preg_replace('/<div class="alert alert-danger[^>]*>.*?<\/div>/s', '', $content);
$content = preg_replace('/<div[^>]*>Terjadi kesalahan.*?<\/div>/s', '', $content);

// Tampilkan konten yang sudah difilter
echo $content;

// Load footer
include_once 'includes/footer.php';

// Restore default error handler
restore_error_handler();
?>
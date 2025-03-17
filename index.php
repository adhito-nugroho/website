<?php
// Tampilkan semua error PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definisikan konstanta BASE_PATH
define('BASE_PATH', dirname(__FILE__));

// Mulai session
session_start();

// Load konfigurasi dan functions
require_once 'includes/config.php';
require_once 'includes/functions.php';

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

// Selalu muat semua section di halaman beranda
include_once 'modules/beranda.php';        // Hero section & floating stats
include_once 'modules/profil.php';         // Profil section
include_once 'modules/layanan.php';        // Layanan section
include_once 'modules/program.php';        // Program section
include_once 'modules/statistik.php';      // Statistik section
include_once 'modules/monitoring.php';     // Monitoring section
include_once 'modules/publikasi.php';      // Publikasi section
include_once 'modules/galeri.php';         // Galeri section
include_once 'modules/kontak.php';         // Kontak section

// Load footer
include_once 'includes/footer.php';
?>
<?php
// Mencegah akses langsung ke file
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Zona waktu default
date_default_timezone_set('Asia/Jakarta');

// Konfigurasi database
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'cdk_bojonegoro',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci'
];

// Informasi website
$site_config = [
    'base_url' => 'http://localhost:8000/website-cdk-3',
    'admin_url' => 'http://localhost:8000/website-cdk-3/admin',
    'upload_dir' => 'uploads',
    'debug_mode' => true
];

// Pengaturan keamanan
$security_config = [
    'csrf_token_name' => 'csrf_token',
    'csrf_token_expire' => 7200, // dalam detik (2 jam)
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']
];

// Buat koneksi database
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
    // Tangani error koneksi database
    if ($site_config['debug_mode']) {
        die("Koneksi database gagal: " . $e->getMessage());
    } else {
        die("Terjadi kesalahan pada sistem. Silakan coba lagi nanti.");
    }
}

// Fungsi untuk mendapatkan pengaturan website dari database
function getSiteSettings($pdo)
{
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $settings = [];
        while ($row = $stmt->fetch()) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (PDOException $e) {
        if ($GLOBALS['site_config']['debug_mode']) {
            error_log("Error loading settings: " . $e->getMessage());
        }
        return [];
    }
}

// Memuat pengaturan dari database
$site_settings = getSiteSettings($pdo);

// Fungsi untuk memverifikasi CSRF token
function verifyCsrfToken($token)
{
    // Use the form-specific token verification if it exists
    if (function_exists('verifyFormToken')) {
        return verifyFormToken('global', $token);
    }
    
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }

    $stored_token = $_SESSION['csrf_token'];
    $token_time = $_SESSION['csrf_token_time'];
    $current_time = time();

    // Periksa apakah token sudah kadaluwarsa
    if ($current_time - $token_time > $GLOBALS['security_config']['csrf_token_expire']) {
        return false;
    }

    // Verifikasi token
    return hash_equals($stored_token, $token);
}

// Fungsi untuk membuat CSRF token baru
function generateCsrfToken()
{
    // Use the form-specific token generation if it exists
    if (function_exists('generateFormToken')) {
        return generateFormToken('global');
    }
    
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    $_SESSION['csrf_token_time'] = time();
    return $token;
}

// Fungsi sanitasi input
function sanitizeInput($input)
{
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitizeInput($value);
        }
    } else {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

// Fungsi untuk membuat slug dari string
function createSlug($string)
{
    $string = strtolower(trim($string));
    $string = preg_replace('/[^a-z0-9-]/', '-', $string);
    $string = preg_replace('/-+/', '-', $string);
    return trim($string, '-');
}

// Fungsi untuk mengecek file type yang diizinkan
function isAllowedFileType($filename)
{
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($ext, $GLOBALS['security_config']['allowed_file_types']);
}

// Fungsi untuk mengamankan upload file
function secureUploadFile($file, $directory, $allowedTypes = null)
{
    if (!$allowedTypes) {
        $allowedTypes = $GLOBALS['security_config']['allowed_file_types'];
    }

    // Validasi file
    $filename = basename($file['name']);
    $filesize = $file['size'];
    $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    // Cek ukuran file (max 10MB)
    if ($filesize > 10485760) {
        return ['success' => false, 'message' => 'Ukuran file melebihi batas maksimum (10MB)'];
    }

    // Cek tipe file
    if (!in_array($filetype, $allowedTypes)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan'];
    }

    // Buat nama file unik
    $newFilename = time() . '_' . uniqid() . '.' . $filetype;
    $targetPath = $GLOBALS['site_config']['upload_dir'] . '/' . $directory . '/' . $newFilename;

    // Pindahkan file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return [
            'success' => true,
            'filename' => $newFilename,
            'path' => $targetPath
        ];
    } else {
        return ['success' => false, 'message' => 'Gagal mengupload file'];
    }
}

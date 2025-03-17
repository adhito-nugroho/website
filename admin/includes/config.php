<?php
// Prevent direct access
if (!defined('ADMIN_PATH') && !defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diizinkan');
}

// Database configuration
$db_host = 'localhost';
$db_name = 'cdk_bojonegoro';
$db_user = 'root';
$db_pass = '';
$db_charset = 'utf8mb4';

// PDO connection options
$db_options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

// Create PDO connection
try {
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=$db_charset";
    $pdo = new PDO($dsn, $db_user, $db_pass, $db_options);
} catch (PDOException $e) {
    error_log('Database Connection Error: ' . $e->getMessage());
    die('Koneksi database gagal: ' . $e->getMessage());
}

// Site configuration
define('SITE_URL', 'http://localhost/website-cdk3');
define('ADMIN_URL', SITE_URL . '/admin');
define('UPLOAD_PATH', BASE_PATH . '/uploads');
define('UPLOAD_URL', SITE_URL . '/uploads');

// Timezone setting
date_default_timezone_set('Asia/Jakarta');

// Session lifetime (30 minutes)
ini_set('session.gc_maxlifetime', 1800);
session_set_cookie_params(1800);

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Maximum file upload size (2MB)
define('MAX_FILE_SIZE', 2 * 1024 * 1024);

// Allowed file types
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);
define('ALLOWED_DOCUMENT_TYPES', ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);

// Create uploads directory if it doesn't exist
$upload_dirs = [
    UPLOAD_PATH,
    UPLOAD_PATH . '/posts',
    UPLOAD_PATH . '/services',
    UPLOAD_PATH . '/documents',
    UPLOAD_PATH . '/galleries',
    UPLOAD_PATH . '/settings'
];

foreach ($upload_dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

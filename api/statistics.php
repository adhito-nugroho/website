<?php
// Definisikan konstanta BASE_PATH
define('BASE_PATH', dirname(__DIR__));

// Mulai session
session_start();

// Load konfigurasi
require_once BASE_PATH . '/includes/config.php';
require_once BASE_PATH . '/includes/functions.php';

// Set header untuk JSON
header('Content-Type: application/json');

// Cek method request
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

// Sanitasi parameter
$category = sanitizeInput($_GET['category'] ?? '');
$year = (int) sanitizeInput($_GET['year'] ?? date('Y'));

// Validasi parameter
if (empty($category) || !in_array($category, ['forest-area', 'forest-production'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid category parameter'
    ]);
    exit;
}

try {
    // Query database
    $stmt = $pdo->prepare("SELECT * FROM statistics WHERE category = :category AND year = :year");
    $stmt->execute(['category' => $category, 'year' => $year]);
    $statistics = $stmt->fetch();
    
    if ($statistics) {
        echo json_encode([
            'success' => true,
            'statistics' => $statistics
        ]);
    } else {
        // Jika data tidak ditemukan, coba cari tahun terdekat
        $stmt = $pdo->prepare("
            SELECT * FROM statistics 
            WHERE category = :category 
            ORDER BY ABS(year - :year) LIMIT 1
        ");
        $stmt->execute(['category' => $category, 'year' => $year]);
        $statistics = $stmt->fetch();
        
        if ($statistics) {
            echo json_encode([
                'success' => true,
                'statistics' => $statistics,
                'message' => 'Data untuk tahun ' . $year . ' tidak tersedia. Menampilkan data tahun ' . $statistics['year']
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
    }
} catch (PDOException $e) {
    // Log error
    error_log('API Statistics Error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
?>

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
$year = (int) sanitizeInput($_GET['year'] ?? date('Y'));

try {
    // Query database
    $stmt = $pdo->prepare("
        SELECT * FROM achievements 
        WHERE year = :year AND is_active = 1 
        ORDER BY percentage DESC
    ");
    $stmt->execute(['year' => $year]);
    $achievements = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'achievements' => $achievements
    ]);
} catch (PDOException $e) {
    // Log error
    error_log('API Achievements Error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
?>
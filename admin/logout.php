<?php
// Definisikan konstanta untuk direktori saat ini
define('BASE_PATH', dirname(__DIR__));
define('ADMIN_PATH', __DIR__);

// Mulai session
session_start();

// Load konfigurasi
require_once BASE_PATH . '/includes/config.php';

// Log aktivitas logout jika user sedang login
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true && isset($_SESSION['admin_id'])) {
    try {
        // Log aktivitas logout
        $user_id = $_SESSION['admin_id'];
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $stmt = $pdo->prepare("
            INSERT INTO admin_logs (user_id, activity, ip_address, user_agent, created_at) 
            VALUES (:user_id, :activity, :ip_address, :user_agent, NOW())
        ");
        $stmt->execute([
            'user_id' => $user_id,
            'activity' => 'logout',
            'ip_address' => $ip_address,
            'user_agent' => $agent
        ]);
    } catch (PDOException $e) {
        // Log error
        error_log('Logout Error: ' . $e->getMessage());
    }
}

// Hapus semua data session
$_SESSION = [];

// Hancurkan cookie session jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Mulai session baru untuk pesan flash
session_start();

// Tambahkan pesan sukses untuk halaman login
$_SESSION['message'] = 'Anda berhasil logout.';
$_SESSION['message_type'] = 'success';

// Redirect ke halaman login dengan path absolut
header('Location: ' . $site_config['admin_url'] . '/login.php');
exit;
?>
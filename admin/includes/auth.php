<?php
// Memastikan file ini tidak diakses langsung
if (!defined('ADMIN_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

/**
 * Memeriksa apakah admin sudah login
 * Jika belum, redirect ke halaman login
 */
function requireLogin()
{
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Memeriksa apakah admin memiliki role tertentu
 * 
 * @param string|array $roles Role yang diizinkan
 * @return bool True jika admin memiliki role yang diizinkan
 */
function checkRole($roles)
{
    if (!isset($_SESSION['admin_role'])) {
        return false;
    }

    if (is_array($roles)) {
        return in_array($_SESSION['admin_role'], $roles);
    } else {
        return $_SESSION['admin_role'] === $roles;
    }
}

/**
 * Memeriksa apakah admin memiliki role tertentu
 * Jika tidak, tampilkan pesan error dan redirect
 * 
 * @param string|array $roles Role yang diizinkan
 */
function requireRole($roles)
{
    if (!checkRole($roles)) {
        $_SESSION['error_message'] = 'Anda tidak memiliki izin untuk mengakses halaman ini.';
        header('Location: dashboard.php');
        exit;
    }
}

/**
 * Mendapatkan data admin yang sedang login
 * 
 * @return array|null Data admin atau null jika tidak ditemukan
 */
function getCurrentAdmin()
{
    global $pdo;

    if (!isset($_SESSION['admin_id'])) {
        return null;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute(['id' => $_SESSION['admin_id']]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Error getting current admin: ' . $e->getMessage());
        return null;
    }
}

/**
 * Mencatat aktivitas admin
 * 
 * @param string $action Tindakan yang dilakukan
 * @param string $module Modul yang terkait
 * @param int $item_id ID item yang terkait (opsional)
 * @param string $details Detail tambahan (opsional)
 */
function logAdminActivity($action, $module, $item_id = null, $details = null)
{
    global $pdo;

    if (!isset($_SESSION['admin_id'])) {
        return;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO admin_logs (admin_id, action, module, item_id, details, ip_address)
            VALUES (:admin_id, :action, :module, :item_id, :details, :ip_address)
        ");

        $stmt->execute([
            'admin_id' => $_SESSION['admin_id'],
            'action' => $action,
            'module' => $module,
            'item_id' => $item_id,
            'details' => $details,
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);
    } catch (PDOException $e) {
        error_log('Error logging admin activity: ' . $e->getMessage());
    }
}



/**
 * Membuat tabel admin_logs jika belum ada
 * Tabel ini digunakan untuk mencatat aktivitas admin
 */
function createAdminLogsTable()
{
    global $pdo;

    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS admin_logs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                admin_id INT NOT NULL,
                action VARCHAR(50) NOT NULL,
                module VARCHAR(50) NOT NULL,
                item_id INT,
                details TEXT,
                ip_address VARCHAR(45),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");
    } catch (PDOException $e) {
        error_log('Error creating admin_logs table: ' . $e->getMessage());
    }
}

// Buat tabel admin_logs jika belum ada
createAdminLogsTable();

/**
 * Check if admin user is logged in
 * 
 * @return bool True if admin is logged in, false otherwise
 */
function isLoggedIn()
{
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Login admin user
 * 
 * @param string $username Username
 * @param string $password Password
 * @param bool $remember Remember login
 * @return array Result with success status and message
 */
function loginAdmin($username, $password, $remember = false)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_role'] = $user['role'];

            // Update last login time
            $update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update->execute([$user['id']]);

            // Set remember cookie if requested
            if ($remember) {
                setcookie('admin_remember', $user['username'], time() + (86400 * 30), '/');
            }

            // Log activity
            logAdminActivity('login', 'auth', $user['id'], 'Login berhasil');

            return ['success' => true, 'message' => 'Login berhasil'];
        } else {
            return ['success' => false, 'message' => 'Username atau password salah'];
        }
    } catch (PDOException $e) {
        error_log('Login Error: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Terjadi kesalahan saat login'];
    }
}

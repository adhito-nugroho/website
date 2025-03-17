<?php
// Definisikan konstanta untuk direktori saat ini
define('BASE_PATH', dirname(__DIR__));
define('ADMIN_PATH', __DIR__);

// Mulai session
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: modules/dashboard/index.php');
    exit;
}

// Load konfigurasi
require_once BASE_PATH . '/includes/config.php';

// Inisialisasi variabel error
$error = '';

// Proses login jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi dan validasi input
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validasi input
    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi';
    } else {
        // Modifikasi bagian try-catch saat verifikasi login
        try {
            // Cek username di database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->execute(['username' => $username]);
            $user = $stmt->fetch();

            // Tambahkan log untuk debugging
            error_log('Login attempt for user: ' . $username);

            // Verifikasi user dan password
            if ($user && password_verify($password, $user['password'])) {
                // Set session login
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_name'] = $user['name'];
                $_SESSION['admin_role'] = $user['role'];

                try {
                    // Update last login dalam try-catch terpisah
                    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
                    $stmt->execute(['id' => $user['id']]);
                } catch (PDOException $e) {
                    // Log error tapi jangan hentikan proses login
                    error_log('Error updating last_login: ' . $e->getMessage());
                }

                try {
                    // Log aktivitas login dalam try-catch terpisah
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

                    $stmt = $pdo->prepare("
                INSERT INTO admin_logs (user_id, activity, ip_address, user_agent, created_at) 
                VALUES (:user_id, :activity, :ip_address, :user_agent, NOW())
            ");
                    $stmt->execute([
                        'user_id' => $user['id'],
                        'activity' => 'login',
                        'ip_address' => $ip_address,
                        'user_agent' => $agent
                    ]);
                } catch (PDOException $e) {
                    // Log error tapi jangan hentikan proses login
                    error_log('Error logging login activity: ' . $e->getMessage());
                }

                // Redirect ke dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Username atau password tidak valid';

                try {
                    // Log percobaan login gagal dalam try-catch terpisah
                    $ip_address = $_SERVER['REMOTE_ADDR'];
                    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

                    $stmt = $pdo->prepare("
                INSERT INTO login_attempts (username, ip_address, user_agent, status, created_at) 
                VALUES (:username, :ip_address, :user_agent, 'failed', NOW())
            ");
                    $stmt->execute([
                        'username' => $username,
                        'ip_address' => $ip_address,
                        'user_agent' => $agent
                    ]);
                } catch (PDOException $e) {
                    // Log error tapi jangan hentikan proses
                    error_log('Error logging failed login attempt: ' . $e->getMessage());
                }
            }
        } catch (PDOException $e) {
            $error = 'Terjadi kesalahan sistem. Silakan coba lagi nanti.';
            // Log error dengan detail lebih lengkap
            error_log('Login Error (PDO Exception): ' . $e->getMessage() . ' - SQL State: ' . $e->getCode());
        }
    }
}

// Generate CSRF token
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;
$_SESSION['csrf_token_time'] = time();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - CDK Wilayah Bojonegoro</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/images/favicon.ico" type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #2e7d32;
            --primary-dark: #1b4332;
            --secondary-color: #edf7f3;
        }

        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        .login-container {
            max-width: 420px;
            width: 100%;
            padding: 2rem;
        }

        .login-card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header img {
            height: 70px;
            margin-bottom: 1.5rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.05);
            }

            100% {
                transform: scale(1);
            }
        }

        .login-header h4 {
            color: var(--primary-dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .form-control {
            height: 50px;
            border-radius: 8px;
            padding-left: 45px;
            font-size: 16px;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(46, 125, 50, 0.15);
        }

        .input-group-text {
            position: absolute;
            height: 50px;
            width: 50px;
            border: none;
            background: transparent;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            height: 50px;
            font-size: 16px;
            font-weight: 500;
            border-radius: 8px;
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .login-footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.875rem;
            color: #6c757d;
        }

        .back-to-site {
            display: inline-block;
            margin-top: 1rem;
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .back-to-site:hover {
            color: var(--primary-dark);
        }

        .alert {
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="../assets/images/logo.png" alt="Logo CDK Bojonegoro">
                <h4>Admin Dashboard</h4>
                <p class="text-muted">CDK Wilayah Bojonegoro</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="mb-4 position-relative">
                    <span class="input-group-text">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username"
                        required>
                </div>

                <div class="mb-4 position-relative">
                    <span class="input-group-text">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password"
                        required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i> Login
                    </button>
                </div>
            </form>

            <div class="login-footer">
                <div>&copy; <?php echo date('Y'); ?> Dinas Kehutanan Provinsi Jawa Timur</div>
                <a href="../index.php" class="back-to-site">
                    <i class="fas fa-arrow-left me-1"></i> Kembali ke Website
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Login Form Validation -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const loginForm = document.getElementById('loginForm');

            loginForm.addEventListener('submit', function (e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value;

                if (!username || !password) {
                    e.preventDefault();
                    alert('Username dan password wajib diisi');
                }
            });
        });
    </script>
</body>

</html>
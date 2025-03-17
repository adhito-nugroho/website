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

// Cek role admin
requireRole('admin');

// Variabel untuk form
$name = '';
$username = '';
$email = '';
$role = 'editor';
$errors = [];

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi dan validasi input
    $name = sanitizeInput($_POST['name'] ?? '');
    $username = sanitizeInput($_POST['username'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = sanitizeInput($_POST['role'] ?? 'editor');

    // Validasi
    if (empty($name)) {
        $errors[] = 'Nama lengkap wajib diisi';
    }

    if (empty($username)) {
        $errors[] = 'Username wajib diisi';
    } else if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username hanya boleh berisi huruf, angka, dan underscore';
    } else {
        // Cek apakah username sudah digunakan
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Username sudah digunakan';
        }
    }

    if (empty($email)) {
        $errors[] = 'Email wajib diisi';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    } else {
        // Cek apakah email sudah digunakan
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Email sudah digunakan';
        }
    }

    if (empty($password)) {
        $errors[] = 'Password wajib diisi';
    } else if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Konfirmasi password tidak sesuai';
    }

    if (!in_array($role, ['admin', 'editor'])) {
        $errors[] = 'Role tidak valid';
    }

    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        try {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (name, username, email, password, role, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $stmt->execute([$name, $username, $email, $hashed_password, $role]);

            // Redirect ke halaman pengguna dengan pesan sukses
            $_SESSION['message'] = 'Pengguna berhasil ditambahkan';
            $_SESSION['message_type'] = 'success';

            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error database: ' . $e->getMessage();
        }
    }
}

// Set page title
$page_title = 'Tambah Pengguna';

// Load header
include_once ADMIN_PATH . '/includes/header.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">Tambah Pengguna</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Pengguna</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Form Tambah Pengguna</h4>
                        <form action="" method="post">
                            <div class="mb-3 row">
                                <label for="name" class="col-md-2 col-form-label">Nama Lengkap <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="name" name="name"
                                        value="<?php echo htmlspecialchars($name); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="username" class="col-md-2 col-form-label">Username <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="username" name="username"
                                        value="<?php echo htmlspecialchars($username); ?>" required>
                                    <small class="form-text text-muted">Username hanya boleh berisi huruf, angka, dan
                                        underscore</small>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="email" class="col-md-2 col-form-label">Email <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="email" class="form-control" id="email" name="email"
                                        value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="password" class="col-md-2 col-form-label">Password <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <small class="form-text text-muted">Password minimal 6 karakter</small>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="confirm_password" class="col-md-2 col-form-label">Konfirmasi Password <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="role" class="col-md-2 col-form-label">Role <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <select class="form-select" id="role" name="role" required>
                                        <option value="editor" <?php echo ($role === 'editor') ? 'selected' : ''; ?>>
                                            Editor</option>
                                        <option value="admin" <?php echo ($role === 'admin') ? 'selected' : ''; ?>>
                                            Administrator</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <div class="col-md-10 offset-md-2">
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                    <a href="index.php" class="btn btn-secondary">Batal</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
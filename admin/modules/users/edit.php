<div class="mb-3 row">
    <div class="col-md-10 offset-md-2">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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

<!-- Form Validation Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('form');
        const passwordField = document.getElementById('password');
        const confirmPasswordField = document.getElementById('confirm_password');

        form.addEventListener('submit', function (e) {
            // Reset previous error highlights
            const formControls = form.querySelectorAll('.form-control');
            formControls.forEach(control => {
                control.classList.remove('is-invalid');
            });

            // Check if password fields match if either is filled
            if (passwordField.value || confirmPasswordField.value) {
                if (passwordField.value !== confirmPasswordField.value) {
                    e.preventDefault();
                    confirmPasswordField.classList.add('is-invalid');
                    alert('Konfirmasi password tidak sesuai dengan password');
                    return;
                }

                // Validate password length
                if (passwordField.value.length > 0 && passwordField.value.length < 6) {
                    e.preventDefault();
                    passwordField.classList.add('is-invalid');
                    alert('Password minimal 6 karakter');
                    return;
                }
            }
        });
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
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

// Validasi parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = 'ID pengguna tidak valid';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];

// Ambil data pengguna dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['message'] = 'Pengguna tidak ditemukan';
        $_SESSION['message_type'] = 'danger';
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['message'] = 'Error: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// Variabel untuk form
$name = $user['name'];
$username = $user['username'];
$email = $user['email'];
$role = $user['role'];
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
        // Cek apakah username sudah digunakan oleh pengguna lain
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$username, $id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Username sudah digunakan';
        }
    }

    if (empty($email)) {
        $errors[] = 'Email wajib diisi';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid';
    } else {
        // Cek apakah email sudah digunakan oleh pengguna lain
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetchColumn() > 0) {
            $errors[] = 'Email sudah digunakan';
        }
    }

    // Password optional, hanya divalidasi jika diisi
    if (!empty($password)) {
        if (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter';
        }

        if ($password !== $confirm_password) {
            $errors[] = 'Konfirmasi password tidak sesuai';
        }
    }

    if (!in_array($role, ['admin', 'editor'])) {
        $errors[] = 'Role tidak valid';
    }

    // Jika tidak ada error, update database
    if (empty($errors)) {
        try {
            // Siapkan query berdasarkan apakah password diubah atau tidak
            if (!empty($password)) {
                // Hash password baru
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, 
                        username = ?, 
                        email = ?, 
                        password = ?, 
                        role = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");

                $stmt->execute([$name, $username, $email, $hashed_password, $role, $id]);
            } else {
                // Tidak mengubah password
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, 
                        username = ?, 
                        email = ?, 
                        role = ?,
                        updated_at = NOW()
                    WHERE id = ?
                ");

                $stmt->execute([$name, $username, $email, $role, $id]);
            }

            // Redirect ke halaman pengguna dengan pesan sukses
            $_SESSION['message'] = 'Pengguna berhasil diperbarui';
            $_SESSION['message_type'] = 'success';

            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error database: ' . $e->getMessage();
        }
    }
}

// Set page title
$page_title = 'Edit Pengguna';

// Load header
include_once ADMIN_PATH . '/includes/header.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">Edit Pengguna</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Pengguna</a></li>
                        <li class="breadcrumb-item active">Edit</li>
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
                        <h4 class="card-title">Form Edit Pengguna</h4>
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
                                <label for="password" class="col-md-2 col-form-label">Password</label>
                                <div class="col-md-10">
                                    <input type="password" class="form-control" id="password" name="password">
                                    <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.
                                        Password minimal 6 karakter.</small>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="confirm_password" class="col-md-2 col-form-label">Konfirmasi
                                    Password</label>
                                <div class="col-md-10">
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password">
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
<?php
// Definisikan konstanta untuk direktori
define('BASE_PATH', dirname(__DIR__));
define('ADMIN_PATH', __DIR__);

// Mulai session
session_start();

// Load konfigurasi dan fungsi
require_once BASE_PATH . '/includes/config.php';
require_once ADMIN_PATH . '/includes/auth.php';
require_once ADMIN_PATH . '/includes/functions.php';

// Cek login admin
requireLogin();

// Ambil data admin yang sedang login
$user = getCurrentAdmin();

if (!$user) {
    // Redirect jika data pengguna tidak ditemukan
    $_SESSION['error_message'] = 'Data pengguna tidak ditemukan.';
    header('Location: dashboard.php');
    exit;
}

// Inisialisasi variabel
$name = $user['name'];
$username = $user['username'];
$email = $user['email'];
$profile_updated = false;
$password_updated = false;
$errors = [];

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Sanitasi input
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');

        // Validasi input
        if (empty($name)) {
            $errors[] = 'Nama lengkap wajib diisi';
        }

        if (empty($email)) {
            $errors[] = 'Email wajib diisi';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid';
        } else {
            // Cek duplikasi email (kecuali email user saat ini)
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user['id']]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Email sudah digunakan oleh pengguna lain';
            }
        }

        // Jika tidak ada error, update data profil
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$name, $email, $user['id']]);

                // Update data session
                $_SESSION['admin_name'] = $name;

                $profile_updated = true;

                // Log aktivitas
                logAdminActivity('update', 'profile', $user['id'], 'Updated profile information');
            } catch (PDOException $e) {
                $errors[] = 'Error database: ' . $e->getMessage();
            }
        }
    } elseif (isset($_POST['update_password'])) {
        // Sanitasi & validasi input
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validasi password saat ini
        if (empty($current_password)) {
            $errors[] = 'Password saat ini wajib diisi';
        } else if (!password_verify($current_password, $user['password'])) {
            $errors[] = 'Password saat ini tidak sesuai';
        }

        // Validasi password baru
        if (empty($new_password)) {
            $errors[] = 'Password baru wajib diisi';
        } else if (strlen($new_password) < 6) {
            $errors[] = 'Password baru minimal 6 karakter';
        }

        // Validasi konfirmasi password
        if ($new_password !== $confirm_password) {
            $errors[] = 'Konfirmasi password tidak sesuai';
        }

        // Jika tidak ada error, update password
        if (empty($errors)) {
            try {
                // Hash password baru
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$hashed_password, $user['id']]);

                $password_updated = true;

                // Log aktivitas
                logAdminActivity('update', 'profile', $user['id'], 'Updated password');
            } catch (PDOException $e) {
                $errors[] = 'Error database: ' . $e->getMessage();
            }
        }
    }
}

// Set page title
$page_title = 'Profil Pengguna';

// Load header
include_once ADMIN_PATH . '/includes/header.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">Profil Pengguna</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Profil</li>
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

        <?php if ($profile_updated): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Informasi profil berhasil diperbarui.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($password_updated): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Password berhasil diperbarui.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="row">
            <!-- Profil Information -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Informasi Profil</h4>
                        <hr>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username"
                                    value="<?php echo htmlspecialchars($username); ?>" readonly>
                                <small class="form-text text-muted">Username tidak dapat diubah.</small>
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Lengkap <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="<?php echo htmlspecialchars($name); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="<?php echo htmlspecialchars($email); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <input type="text" class="form-control" id="role"
                                    value="<?php echo ucfirst(htmlspecialchars($user['role'])); ?>" readonly>
                                <small class="form-text text-muted">Role tidak dapat diubah.</small>
                            </div>

                            <div class="mb-3">
                                <label for="last_login" class="form-label">Login Terakhir</label>
                                <input type="text" class="form-control" id="last_login"
                                    value="<?php echo $user['last_login'] ? date('d/m/Y H:i:s', strtotime($user['last_login'])) : 'Belum ada aktivitas login'; ?>"
                                    readonly>
                            </div>

                            <div class="mb-3">
                                <input type="hidden" name="update_profile" value="1">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Change Password -->
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Ubah Password</h4>
                        <hr>
                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Password Saat Ini <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="current_password"
                                        name="current_password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button"
                                        data-target="current_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">Password Baru <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password" name="new_password"
                                        required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button"
                                        data-target="new_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <small class="form-text text-muted">Password minimal 6 karakter.</small>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Konfirmasi Password Baru <span
                                        class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password"
                                        name="confirm_password" required>
                                    <button class="btn btn-outline-secondary toggle-password" type="button"
                                        data-target="confirm_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="mb-3">
                                <input type="hidden" name="update_password" value="1">
                                <button type="submit" class="btn btn-primary">Ubah Password</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h4 class="card-title">Informasi Akun</h4>
                        <hr>
                        <p>
                            <strong>Terdaftar pada:</strong><br>
                            <?php echo date('d/m/Y H:i:s', strtotime($user['created_at'])); ?>
                        </p>
                        <p>
                            <strong>Terakhir diperbarui:</strong><br>
                            <?php echo date('d/m/Y H:i:s', strtotime($user['updated_at'])); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toggle Password Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggleButtons = document.querySelectorAll('.toggle-password');

        toggleButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const passwordInput = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
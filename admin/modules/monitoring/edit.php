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

// Set judul halaman
$page_title = 'Edit Monitoring';

// Cek apakah ada ID yang diberikan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'ID monitoring tidak valid';
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];

// Ambil data monitoring dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM monitoring WHERE id = ?");
    $stmt->execute([$id]);
    $monitoring = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$monitoring) {
        $_SESSION['error_message'] = 'Data monitoring tidak ditemukan';
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
    header('Location: index.php');
    exit;
}

// Inisialisasi variabel
$date = $monitoring['date'];
$location = $monitoring['location'];
$activity = $monitoring['activity'];
$status = $monitoring['status'];
$description = $monitoring['description'];
$result = $monitoring['result'];
$notes = $monitoring['notes'];
$errors = [];

// Proses form jika ada request POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Ambil dan validasi data dari form
        $date = sanitizeInput($_POST['date'] ?? '');
        $location = sanitizeInput($_POST['location'] ?? '');
        $activity = sanitizeInput($_POST['activity'] ?? '');
        $status = sanitizeInput($_POST['status'] ?? '');
        $description = sanitizeInput($_POST['description'] ?? '');
        $result = sanitizeInput($_POST['result'] ?? '');
        $notes = sanitizeInput($_POST['notes'] ?? '');

        // Validasi required fields
        if (empty($date)) {
            $errors[] = "Tanggal harus diisi";
        }

        if (empty($location)) {
            $errors[] = "Lokasi harus diisi";
        }

        if (empty($activity)) {
            $errors[] = "Kegiatan harus diisi";
        }

        if (empty($status)) {
            $errors[] = "Status harus dipilih";
        }

        // Jika tidak ada error, update database
        if (empty($errors)) {
            // Update data di database
            $stmt = $pdo->prepare("
                UPDATE monitoring 
                SET date = ?, location = ?, activity = ?, status = ?, 
                    description = ?, result = ?, notes = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $stmt->execute([
                $date,
                $location,
                $activity,
                $status,
                $description,
                $result,
                $notes,
                $id
            ]);

            // Catat aktivitas admin
            logAdminActivity($_SESSION['user_id'], 'update', $id, 'Mengubah data monitoring');

            // Simpan pesan sukses ke session
            $_SESSION['success_message'] = 'Data monitoring berhasil diperbarui';

            // Redirect ke halaman index
            header('Location: index.php');
            exit;
        }
    } catch (Exception $e) {
        $errors[] = 'Error: ' . $e->getMessage();
    }
}

// Load header
include_once ADMIN_PATH . '/includes/header.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">Edit Monitoring</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Monitoring</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Form Edit Monitoring</h4>
                        <form method="post" action="" id="monitoringForm">
                            <div class="mb-3 row">
                                <label for="date" class="col-md-2 col-form-label">Tanggal <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="date" class="form-control" id="date" name="date"
                                        value="<?php echo htmlspecialchars($date); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="location" class="col-md-2 col-form-label">Lokasi <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="location" name="location"
                                        value="<?php echo htmlspecialchars($location); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="activity" class="col-md-2 col-form-label">Kegiatan <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="activity" name="activity"
                                        value="<?php echo htmlspecialchars($activity); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="status" class="col-md-2 col-form-label">Status <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">Pilih Status</option>
                                        <option value="pending" <?php echo ($status == 'pending') ? 'selected' : ''; ?>>
                                            Menunggu</option>
                                        <option value="in_progress" <?php echo ($status == 'in_progress') ? 'selected' : ''; ?>>Sedang Berjalan</option>
                                        <option value="completed" <?php echo ($status == 'completed') ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="cancelled" <?php echo ($status == 'cancelled') ? 'selected' : ''; ?>>Dibatalkan</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="description" class="col-md-2 col-form-label">Deskripsi</label>
                                <div class="col-md-10">
                                    <textarea class="form-control" id="description" name="description"
                                        rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="result" class="col-md-2 col-form-label">Hasil</label>
                                <div class="col-md-10">
                                    <textarea class="form-control" id="result" name="result"
                                        rows="4"><?php echo htmlspecialchars($result); ?></textarea>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="notes" class="col-md-2 col-form-label">Catatan</label>
                                <div class="col-md-10">
                                    <textarea class="form-control" id="notes" name="notes"
                                        rows="3"><?php echo htmlspecialchars($notes); ?></textarea>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <div class="col-md-10 offset-md-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Perubahan
                                    </button>
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Custom Script for Form Validation -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Form validation
        const form = document.getElementById('monitoringForm');

        form.addEventListener('submit', function (event) {
            let isValid = true;

            // Check all required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(function (field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('is-invalid');
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            if (!isValid) {
                event.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi');
            }
        });

        // Remove validation styling on input
        const formInputs = form.querySelectorAll('input, select, textarea');
        formInputs.forEach(function (input) {
            input.addEventListener('input', function () {
                if (this.value.trim()) {
                    this.classList.remove('is-invalid');
                }
            });
        });
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
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
$page_title = 'Tambah Dokumen';

// Variabel untuk form
$title = '';
$category = '';
$description = '';
$upload_date = date('Y-m-d');
$is_active = 1;
$errors = [];

// Generate CSRF token
$csrf_token = generateCsrfToken();

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi dan validasi input
    $title = sanitizeInput($_POST['title'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $upload_date = sanitizeInput($_POST['upload_date'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validasi
    if (empty($title)) {
        $errors[] = 'Judul dokumen wajib diisi';
    }

    if (empty($category)) {
        $errors[] = 'Kategori dokumen wajib diisi';
    }

    if (empty($upload_date)) {
        $errors[] = 'Tanggal upload wajib diisi';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $upload_date)) {
        $errors[] = 'Format tanggal upload tidak valid (YYYY-MM-DD)';
    }

    // Cek file dokumen
    if (!isset($_FILES['document']) || $_FILES['document']['error'] != 0) {
        $errors[] = 'File dokumen wajib diupload';
    } else {
        // Validasi tipe file
        $allowed_types = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
        $file_ext = strtolower(pathinfo($_FILES['document']['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            $errors[] = 'Tipe file tidak diizinkan. Tipe yang diizinkan: ' . implode(', ', $allowed_types);
        }

        // Validasi ukuran file (max 10MB)
        if ($_FILES['document']['size'] > 10 * 1024 * 1024) {
            $errors[] = 'Ukuran file terlalu besar. Maksimal 10MB';
        }
    }

    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        try {
            // Upload file dokumen
            $upload_dir = BASE_PATH . '/uploads/dokumen';

            // Buat direktori jika belum ada
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            // Buat nama file yang unik
            $filename = time() . '_' . uniqid() . '.' . $file_ext;
            $upload_path = $upload_dir . '/' . $filename;

            // Pindahkan file
            if (move_uploaded_file($_FILES['document']['tmp_name'], $upload_path)) {
                // Simpan data ke database
                $stmt = $pdo->prepare("
                    INSERT INTO documents (
                        title, filename, file_type, file_size, category, description, 
                        upload_date, is_active, created_by, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?, 
                        ?, ?, ?, NOW(), NOW()
                    )
                ");

                $stmt->execute([
                    $title,
                    $filename,
                    $file_ext,
                    $_FILES['document']['size'],
                    $category,
                    $description,
                    $upload_date,
                    $is_active,
                    $_SESSION['admin_id']
                ]);

                // Log aktivitas
                logActivity($_SESSION['admin_id'], 'menambah dokumen baru', [
                    'title' => $title,
                    'category' => $category
                ]);

                // Redirect ke halaman dokumen dengan pesan sukses
                $_SESSION['success_message'] = 'Dokumen berhasil ditambahkan';

                header('Location: index.php?tab=documents');
                exit;
            } else {
                $errors[] = 'Gagal mengupload file dokumen';
            }
        } catch (PDOException $e) {
            $errors[] = 'Error database: ' . $e->getMessage();
        }
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
                <h4 class="text-themecolor"><?php echo $page_title; ?></h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php?tab=documents">Dokumen</a></li>
                        <li class="breadcrumb-item active"><?php echo $page_title; ?></li>
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
                        <h4 class="card-title">Form Tambah Dokumen</h4>
                        <form action="" method="post" enctype="multipart/form-data">
                            <!-- Add CSRF token hidden field back -->
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                            <div class="mb-3 row">
                                <label for="title" class="col-md-2 col-form-label">Judul Dokumen <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="title" name="title"
                                        value="<?php echo htmlspecialchars($title); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="category" class="col-md-2 col-form-label">Kategori <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="Laporan" <?php echo ($category == 'Laporan') ? 'selected' : ''; ?>>
                                            Laporan</option>
                                        <option value="Pedoman" <?php echo ($category == 'Pedoman') ? 'selected' : ''; ?>>
                                            Pedoman</option>
                                        <option value="Statistik" <?php echo ($category == 'Statistik') ? 'selected' : ''; ?>>Statistik</option>
                                        <option value="Rencana" <?php echo ($category == 'Rencana') ? 'selected' : ''; ?>>
                                            Rencana</option>
                                        <option value="Peraturan" <?php echo ($category == 'Peraturan') ? 'selected' : ''; ?>>Peraturan</option>
                                        <option value="Formulir" <?php echo ($category == 'Formulir') ? 'selected' : ''; ?>>Formulir</option>
                                        <option value="Lainnya" <?php echo ($category == 'Lainnya') ? 'selected' : ''; ?>>
                                            Lainnya</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="document" class="col-md-2 col-form-label">File Dokumen <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="file" class="form-control" id="document" name="document" required>
                                    <small class="form-text text-muted">Format: pdf, doc, docx, xls, xlsx, ppt, pptx,
                                        txt. Ukuran maksimal: 10MB</small>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="upload_date" class="col-md-2 col-form-label">Tanggal Upload <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="date" class="form-control" id="upload_date" name="upload_date"
                                        value="<?php echo htmlspecialchars($upload_date); ?>" required>
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
                                <div class="col-md-2 col-form-label">Status</div>
                                <div class="col-md-10">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                            <?php echo $is_active ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">Aktif</label>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <div class="col-md-10 offset-md-2">
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                    <a href="index.php?tab=documents" class="btn btn-secondary">Batal</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Script for File Validation -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Validasi file saat dipilih
        const documentInput = document.getElementById('document');

        documentInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                // Validasi ukuran file
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    alert('Ukuran file terlalu besar. Maksimal 10MB');
                    this.value = ''; // Reset input
                    return;
                }

                // Validasi tipe file
                const allowedTypes = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt'];
                const fileName = file.name;
                const fileExt = fileName.split('.').pop().toLowerCase();

                if (!allowedTypes.includes(fileExt)) {
                    alert('Tipe file tidak diizinkan. Tipe yang diizinkan: ' + allowedTypes.join(', '));
                    this.value = ''; // Reset input
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
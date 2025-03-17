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
$page_title = 'Tambah Foto Galeri';

// Variabel untuk form
$title = '';
$description = '';
$category = '';
$event_date = date('Y-m-d');
$is_active = 1;
$errors = [];

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi dan validasi input
    $title = sanitizeInput($_POST['title'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? '');
    $event_date = sanitizeInput($_POST['event_date'] ?? '');
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validasi
    if (empty($title)) {
        $errors[] = 'Judul foto wajib diisi';
    }

    if (empty($category)) {
        $errors[] = 'Kategori foto wajib diisi';
    }

    if (empty($event_date)) {
        $errors[] = 'Tanggal kegiatan wajib diisi';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {
        $errors[] = 'Format tanggal kegiatan tidak valid (YYYY-MM-DD)';
    }

    // Cek file gambar
    if (!isset($_FILES['image']) || $_FILES['image']['error'] != 0) {
        $errors[] = 'Gambar wajib diupload';
    } else {
        // Validasi gambar
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) {
            $errors[] = 'Tipe file tidak diizinkan. Tipe yang diizinkan: ' . implode(', ', $allowed_types);
        }

        // Validasi ukuran gambar (max 5MB)
        if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
            $errors[] = 'Ukuran gambar terlalu besar. Maksimal 5MB';
        }
    }

    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        try {
            // Upload gambar
            $upload_result = uploadImage($_FILES['image'], 'uploads/galeri');

            if (!$upload_result['status']) {
                throw new Exception('Error upload gambar: ' . $upload_result['message']);
            }

            $image_filename = $upload_result['filename'];

            // Simpan data ke database
            $stmt = $pdo->prepare("
                INSERT INTO gallery (title, description, image, category, event_date, is_active, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $stmt->execute([
                $title,
                $description,
                $image_filename,
                $category,
                $event_date,
                $is_active
            ]);

            // Log aktivitas
            if (isset($_SESSION['admin_id'])) {
                logActivity($_SESSION['admin_id'], 'menambah foto ke galeri', [
                    'title' => $title,
                    'category' => $category
                ]);
            }

            // Redirect ke halaman galeri dengan pesan sukses
            $_SESSION['message'] = 'Foto berhasil ditambahkan ke galeri';
            $_SESSION['message_type'] = 'success';

            header('Location: index.php');
            exit;
        } catch (Exception $e) {
            // Jika error, hapus gambar yang sudah diupload (jika ada)
            if (isset($image_filename)) {
                deleteImage($image_filename, 'uploads/galeri');
            }

            $errors[] = 'Error: ' . $e->getMessage();
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
                        <li class="breadcrumb-item"><a href="index.php">Galeri</a></li>
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
                        <h4 class="card-title">Form Tambah Foto</h4>
                        <form action="" method="post" enctype="multipart/form-data">
                            <div class="mb-3 row">
                                <label for="title" class="col-md-2 col-form-label">Judul Foto <span
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
                                        <option value="penanaman" <?php echo ($category == 'penanaman') ? 'selected' : ''; ?>>Penanaman</option>
                                        <option value="penyuluhan" <?php echo ($category == 'penyuluhan') ? 'selected' : ''; ?>>Penyuluhan</option>
                                        <option value="pembibitan" <?php echo ($category == 'pembibitan') ? 'selected' : ''; ?>>Pembibitan</option>
                                        <option value="pertemuan" <?php echo ($category == 'pertemuan') ? 'selected' : ''; ?>>Pertemuan</option>
                                        <option value="pelatihan" <?php echo ($category == 'pelatihan') ? 'selected' : ''; ?>>Pelatihan</option>
                                        <option value="lainnya" <?php echo ($category == 'lainnya') ? 'selected' : ''; ?>>
                                            Lainnya</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="image" class="col-md-2 col-form-label">Gambar <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*"
                                        required>
                                    <small class="form-text text-muted">Format: jpg, jpeg, png, gif. Ukuran maksimal:
                                        5MB</small>
                                    <div id="imagePreview" class="mt-2" style="display:none;">
                                        <img src="" alt="Preview" class="img-thumbnail" style="max-height: 200px;">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="event_date" class="col-md-2 col-form-label">Tanggal Kegiatan <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="date" class="form-control" id="event_date" name="event_date"
                                        value="<?php echo htmlspecialchars($event_date); ?>" required>
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

<!-- Custom Script for Image Preview -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Image preview
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('imagePreview');
        const previewImg = imagePreview.querySelector('img');

        imageInput.addEventListener('change', function () {
            const file = this.files[0];
            if (file) {
                // Validasi ukuran gambar
                const maxSize = 5 * 1024 * 1024; // 5MB
                if (file.size > maxSize) {
                    alert('Ukuran gambar terlalu besar. Maksimal 5MB');
                    this.value = ''; // Reset input
                    imagePreview.style.display = 'none';
                    return;
                }

                // Validasi tipe gambar
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Tipe file tidak diizinkan. Tipe yang diizinkan: jpg, jpeg, png, gif');
                    this.value = ''; // Reset input
                    imagePreview.style.display = 'none';
                    return;
                }

                // Preview gambar
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    imagePreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
            }
        });
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
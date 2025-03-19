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
$page_title = 'Tambah Berita/Artikel';

// Variabel untuk form
$title = '';
$slug = '';
$category = '';
$content = '';
$publish_date = date('Y-m-d');
$is_featured = 0;
$is_active = 1;
$errors = [];

// Generate CSRF token
$csrf_token = generateCsrfToken();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi dan validasi input
    $title = sanitizeInput($_POST['title'] ?? '');
    $slug = sanitizeInput($_POST['slug'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? '');
    $content = $_POST['content'] ?? ''; // Tidak di-sanitize karena berisi HTML
    $publish_date = sanitizeInput($_POST['publish_date'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validasi
    if (empty($title)) {
        $errors[] = 'Judul berita/artikel wajib diisi';
    }

    if (empty($category)) {
        $errors[] = 'Kategori berita/artikel wajib diisi';
    }

    if (empty($content)) {
        $errors[] = 'Konten berita/artikel wajib diisi';
    }

    if (empty($publish_date)) {
        $errors[] = 'Tanggal publikasi wajib diisi';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $publish_date)) {
        $errors[] = 'Format tanggal publikasi tidak valid (YYYY-MM-DD)';
    }

    // Buat slug dari judul jika slug kosong
    if (empty($slug)) {
        $slug = createSlug($title);
    } else {
        $slug = createSlug($slug);
    }

    // Cek apakah slug sudah digunakan
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM posts WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetch()['count'] > 0) {
        $errors[] = 'Slug sudah digunakan, silakan gunakan slug lain';
    }

    // Proses upload gambar jika ada
    $image_filename = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $upload_result = uploadImage($_FILES['image'], 'uploads/publikasi');

        if (!$upload_result['status']) {
            $errors[] = 'Error upload gambar: ' . $upload_result['message'];
        } else {
            $image_filename = $upload_result['filename'];
        }
    }

    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO posts (
                    title, slug, category, content, image, 
                    publish_date, is_featured, is_active, created_by, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, 
                    ?, ?, ?, ?, NOW(), NOW()
                )
            ");

            $stmt->execute([
                $title,
                $slug,
                $category,
                $content,
                $image_filename,
                $publish_date,
                $is_featured,
                $is_active,
                $_SESSION['admin_id']
            ]);

            // Log aktivitas
            logActivity($_SESSION['admin_id'], 'menambah berita/artikel baru', [
                'title' => $title,
                'category' => $category
            ]);

            // Redirect ke halaman publikasi dengan pesan sukses
            $_SESSION['success_message'] = 'Berita/artikel berhasil ditambahkan';

            header('Location: index.php?tab=posts');
            exit;
        } catch (PDOException $e) {
            // Jika error, hapus gambar yang sudah diupload (jika ada)
            if (!empty($image_filename)) {
                deleteImage($image_filename, 'uploads/publikasi');
            }

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
                        <li class="breadcrumb-item"><a href="index.php?tab=posts">Publikasi</a></li>
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
                        <h4 class="card-title">Form Tambah Berita/Artikel</h4>
                        <form action="" method="post" enctype="multipart/form-data">
                            <!-- Add CSRF token hidden field back -->
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                            <div class="mb-3 row">
                                <label for="title" class="col-md-2 col-form-label">Judul <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="title" name="title"
                                        value="<?php echo htmlspecialchars($title); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="slug" class="col-md-2 col-form-label">Slug</label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="slug" name="slug"
                                        value="<?php echo htmlspecialchars($slug); ?>">
                                    <small class="form-text text-muted">Akan dibuat otomatis dari judul jika
                                        dikosongkan</small>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="category" class="col-md-2 col-form-label">Kategori <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="Program" <?php echo ($category == 'Program') ? 'selected' : ''; ?>>
                                            Program</option>
                                        <option value="Pemberdayaan" <?php echo ($category == 'Pemberdayaan') ? 'selected' : ''; ?>>Pemberdayaan</option>
                                        <option value="Perlindungan" <?php echo ($category == 'Perlindungan') ? 'selected' : ''; ?>>Perlindungan</option>
                                        <option value="Rehabilitasi" <?php echo ($category == 'Rehabilitasi') ? 'selected' : ''; ?>>Rehabilitasi</option>
                                        <option value="Pengumuman" <?php echo ($category == 'Pengumuman') ? 'selected' : ''; ?>>Pengumuman</option>
                                        <option value="Berita" <?php echo ($category == 'Berita') ? 'selected' : ''; ?>>
                                            Berita</option>
                                        <option value="Artikel" <?php echo ($category == 'Artikel') ? 'selected' : ''; ?>>
                                            Artikel</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="image" class="col-md-2 col-form-label">Gambar</label>
                                <div class="col-md-10">
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <small class="form-text text-muted">Format: jpg, jpeg, png. Ukuran maksimal:
                                        2MB</small>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="publish_date" class="col-md-2 col-form-label">Tanggal Publikasi <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="date" class="form-control" id="publish_date" name="publish_date"
                                        value="<?php echo htmlspecialchars($publish_date); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="content" class="col-md-2 col-form-label">Konten <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <textarea class="form-control summernote" id="content" name="content"
                                        rows="10"><?php echo htmlspecialchars($content); ?></textarea>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <div class="col-md-2 col-form-label">Status</div>
                                <div class="col-md-10">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-input" type="checkbox" id="is_featured"
                                            name="is_featured" <?php echo $is_featured ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_featured">Tampilkan di Halaman
                                            Utama</label>
                                    </div>
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
                                    <a href="index.php?tab=posts" class="btn btn-secondary">Batal</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Script for Auto Slug -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Auto generate slug from title
        const titleInput = document.getElementById('title');
        const slugInput = document.getElementById('slug');

        titleInput.addEventListener('keyup', function () {
            if (!slugInput.value) {
                slugInput.value = createSlug(this.value);
            }
        });

        titleInput.addEventListener('blur', function () {
            if (!slugInput.value) {
                slugInput.value = createSlug(this.value);
            }
        });

        // Function to create slug
        function createSlug(text) {
            return text.toString().toLowerCase()
                .replace(/\s+/g, '-')           // Replace spaces with -
                .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
                .replace(/\-\-+/g, '-')         // Replace multiple - with single -
                .replace(/^-+/, '')             // Trim - from start of text
                .replace(/-+$/, '');            // Trim - from end of text
        }

        // Initialize Summernote
        $('.summernote').summernote({
            height: 350,
            minHeight: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            callbacks: {
                onImageUpload: function (files) {
                    // Custom image upload handler can be added here
                    // This will need additional server-side code to handle the upload
                    alert('Fitur upload gambar langsung belum tersedia. Silakan upload gambar terlebih dahulu, kemudian masukkan URL gambar.');
                }
            }
        });
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
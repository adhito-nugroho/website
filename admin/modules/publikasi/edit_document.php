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
$page_title = 'Edit Dokumen';

// Validasi parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'ID dokumen tidak valid';
    header('Location: index.php?tab=documents');
    exit;
}

$id = (int) $_GET['id'];

// Ambil data dokumen dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
    $stmt->execute([$id]);
    $document = $stmt->fetch();

    if (!$document) {
        $_SESSION['error_message'] = 'Dokumen tidak ditemukan';
        header('Location: index.php?tab=documents');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
    header('Location: index.php?tab=documents');
    exit;
}

// Variabel untuk form
$title = $document['title'];
$category = $document['category'];
$description = $document['description'];
$upload_date = $document['upload_date'];
$is_active = $document['is_active'];
$current_filename = $document['filename'];
$current_filetype = $document['file_type'];
$current_filesize = $document['file_size'];
$errors = [];

// Remove CSRF token generation
// $csrf_token = generateCsrfToken();

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Remove CSRF token validation
    // if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    //     $errors[] = 'Token keamanan tidak valid';
    // }
    
    // Continue with form processing
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = 'Token keamanan tidak valid';
    } else {
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

        // Variabel untuk file baru
        $filename = $current_filename;
        $filetype = $current_filetype;
        $filesize = $current_filesize;

        // Cek jika ada file dokumen baru
        if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
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

            // Jika validasi oke, update file
            if (empty($errors)) {
                // Upload file dokumen baru
                $upload_dir = BASE_PATH . '/uploads/dokumen';

                // Buat nama file yang unik
                $new_filename = time() . '_' . uniqid() . '.' . $file_ext;
                $upload_path = $upload_dir . '/' . $new_filename;

                // Pindahkan file
                if (move_uploaded_file($_FILES['document']['tmp_name'], $upload_path)) {
                    // Hapus file lama
                    $old_file_path = $upload_dir . '/' . $current_filename;
                    if (file_exists($old_file_path)) {
                        unlink($old_file_path);
                    }

                    // Update data file
                    $filename = $new_filename;
                    $filetype = $file_ext;
                    $filesize = $_FILES['document']['size'];
                } else {
                    $errors[] = 'Gagal mengupload file dokumen baru';
                }
            }
        }

        // Jika tidak ada error, update database
        if (empty($errors)) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE documents SET 
                        title = ?, 
                        filename = ?, 
                        file_type = ?, 
                        file_size = ?, 
                        category = ?, 
                        description = ?, 
                        upload_date = ?, 
                        is_active = ?, 
                        updated_at = NOW()
                    WHERE id = ?
                ");

                $stmt->execute([
                    $title,
                    $filename,
                    $filetype,
                    $filesize,
                    $category,
                    $description,
                    $upload_date,
                    $is_active,
                    $id
                ]);

                // Log aktivitas
                logActivity($_SESSION['admin_id'], 'memperbarui dokumen', [
                    'document_id' => $id,
                    'title' => $title
                ]);

                // Redirect ke halaman dokumen dengan pesan sukses
                $_SESSION['success_message'] = 'Dokumen berhasil diperbarui';

                header('Location: index.php?tab=documents');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Error database: ' . $e->getMessage();
            }
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
                        <h4 class="card-title">Form Edit Dokumen</h4>
                        <form action="" method="post" enctype="multipart/form-data">
                            <!-- Remove CSRF token hidden field -->
                            <!-- <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"> -->
                            
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
                                <label class="col-md-2 col-form-label">File Saat Ini</label>
                                <div class="col-md-10">
                                    <?php
                                    $icon_class = 'fas fa-file-alt';
                                    switch (strtolower($current_filetype)) {
                                        case 'pdf':
                                            $icon_class = 'fas fa-file-pdf text-danger';
                                            break;
                                        case 'doc':
                                        case 'docx':
                                            $icon_class = 'fas fa-file-word text-primary';
                                            break;
                                        case 'xls':
                                        case 'xlsx':
                                            $icon_class = 'fas fa-file-excel text-success';
                                            break;
                                        case 'ppt':
                                        case 'pptx':
                                            $icon_class = 'fas fa-file-powerpoint text-warning';
                                            break;
                                        case 'zip':
                                        case 'rar':
                                            $icon_class = 'fas fa-file-archive text-secondary';
                                            break;
                                    }
                                    ?>
                                    <div class="d-flex align-items-center">
                                        <i class="<?php echo $icon_class; ?> fa-2x me-2"></i>
                                        <div>
                                            <div><?php echo htmlspecialchars($current_filename); ?></div>
                                            <small class="text-muted">
                                                <?php echo strtoupper($current_filetype); ?> •
                                                <?php echo formatFilesize($current_filesize); ?>
                                            </small>
                                        </div>
                                        <a href="<?php echo $site_config['base_url']; ?>/uploads/dokumen/<?php echo $current_filename; ?>"
                                            class="btn btn-sm btn-success ms-3" target="_blank">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="document" class="col-md-2 col-form-label">Ganti File</label>
                                <div class="col-md-10">
                                    <input type="file" class="form-control" id="document" name="document">
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
                                    <button type="submit" class="btn btn-primary">Update</button>
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
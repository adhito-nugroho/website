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
$page_title = 'Manajemen Galeri';

// Tambahkan, edit, atau hapus data jika ada request
$message = '';
$message_type = '';

// Proses hapus data
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        // Verifikasi CSRF token
        if (!isset($_GET['token']) || !verifyCsrfToken($_GET['token'])) {
            throw new Exception('Token keamanan tidak valid');
        }

        // Periksa apakah galeri ada
        $stmt = $pdo->prepare("SELECT * FROM gallery WHERE id = ?");
        $stmt->execute([$id]);
        $gallery = $stmt->fetch();

        if (!$gallery) {
            throw new Exception('Item galeri tidak ditemukan');
        }

        // Hapus gambar
        if (!empty($gallery['image'])) {
            deleteImage($gallery['image'], 'uploads/galeri');
        }

        // Hapus data dari database
        $stmt = $pdo->prepare("DELETE FROM gallery WHERE id = ?");
        $stmt->execute([$id]);

        // Log aktivitas
        logActivity($_SESSION['admin_id'], 'menghapus item galeri', ['gallery_id' => $id, 'title' => $gallery['title']]);

        $message = 'Item galeri berhasil dihapus';
        $message_type = 'success';
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Filter kategori
$category_filter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';

// Query untuk mendapatkan kategori unik
try {
    $stmt = $pdo->query("SELECT DISTINCT category FROM gallery ORDER BY category");
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $message = 'Error: ' . $e->getMessage();
    $message_type = 'danger';
    $categories = [];
}

// Ambil data galeri dari database
try {
    $query = "SELECT * FROM gallery";
    $params = [];

    if (!empty($category_filter)) {
        $query .= " WHERE category = ?";
        $params[] = $category_filter;
    }

    $query .= " ORDER BY event_date DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $galleries = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = 'Error: ' . $e->getMessage();
    $message_type = 'danger';
    $galleries = [];
}

// Generate CSRF token
$csrf_token = generateCsrfToken();

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
                        <li class="breadcrumb-item active"><?php echo $page_title; ?></li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title">Daftar Galeri</h4>
                            <a href="add.php" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Tambah Foto
                            </a>
                        </div>

                        <!-- Filter -->
                        <div class="mb-4">
                            <form action="" method="get" class="row g-3">
                                <div class="col-md-4">
                                    <label for="category" class="form-label">Filter Kategori</label>
                                    <select name="category" id="category" class="form-select"
                                        onchange="this.form.submit()">
                                        <option value="">Semua Kategori</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($category_filter == $category) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </form>
                        </div>

                        <!-- Galeri Grid -->
                        <div class="row">
                            <?php if (count($galleries) > 0): ?>
                                <?php foreach ($galleries as $gallery): ?>
                                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                                        <div class="card">
                                            <div class="position-relative">
                                                <img src="<?php echo $site_config['base_url']; ?>/uploads/galeri/<?php echo htmlspecialchars($gallery['image']); ?>"
                                                    class="card-img-top"
                                                    alt="<?php echo htmlspecialchars($gallery['title']); ?>"
                                                    style="height: 200px; object-fit: cover;">
                                                <div class="position-absolute top-0 end-0 p-2">
                                                    <?php if ($gallery['is_active']): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Nonaktif</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <h5 class="card-title"><?php echo htmlspecialchars($gallery['title']); ?></h5>
                                                <p class="card-text small text-muted">
                                                    <span
                                                        class="badge bg-secondary"><?php echo htmlspecialchars($gallery['category']); ?></span>
                                                    <i class="fas fa-calendar-alt ms-2 me-1"></i>
                                                    <?php echo date('d/m/Y', strtotime($gallery['event_date'])); ?>
                                                </p>
                                                <div class="btn-group w-100">
                                                    <a href="edit.php?id=<?php echo $gallery['id']; ?>"
                                                        class="btn btn-sm btn-info text-white" title="Edit">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-danger btn-delete"
                                                        data-id="<?php echo $gallery['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($gallery['title']); ?>"
                                                        data-token="<?php echo $csrf_token; ?>" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i> Hapus
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <?php if (!empty($category_filter)): ?>
                                            Tidak ada foto dalam kategori "<?php echo htmlspecialchars($category_filter); ?>".
                                        <?php else: ?>
                                            Belum ada foto dalam galeri.
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus foto "<span id="delete-name"></span>" dari galeri?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="delete-link" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
</div>

<!-- Custom Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Konfigurasi modal konfirmasi hapus
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const deleteButtons = document.querySelectorAll('.btn-delete');

        deleteButtons.forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const token = this.getAttribute('data-token');

                document.getElementById('delete-name').textContent = name;
                document.getElementById('delete-link').href = `index.php?action=delete&id=${id}&token=${token}`;

                deleteModal.show();
            });
        });
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
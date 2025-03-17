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

        // Periksa apakah program ada
        $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = ?");
        $stmt->execute([$id]);
        $program = $stmt->fetch();

        if (!$program) {
            throw new Exception('Program tidak ditemukan');
        }

        // Hapus program
        $stmt = $pdo->prepare("DELETE FROM programs WHERE id = ?");
        $stmt->execute([$id]);

        $message = 'Program berhasil dihapus';
        $message_type = 'success';
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Ambil data program dari database
try {
    $stmt = $pdo->query("SELECT * FROM programs ORDER BY order_number");
    $programs = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = 'Error: ' . $e->getMessage();
    $message_type = 'danger';
    $programs = [];
}

// Generate CSRF token
$csrf_token = generateCsrfToken();

// Set page title for header
$page_title = 'Manajemen Program';

// Load header
include_once ADMIN_PATH . '/includes/header.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">Manajemen Program</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Program</li>
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
                            <h4 class="card-title">Daftar Program</h4>
                            <a href="add.php" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Tambah Program
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered datatable">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="10%">Icon</th>
                                        <th width="25%">Judul</th>
                                        <th>Deskripsi</th>
                                        <th width="8%">Urutan</th>
                                        <th width="8%">Status</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($programs) > 0): ?>
                                        <?php foreach ($programs as $index => $program): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td class="text-center">
                                                    <i class="<?php echo htmlspecialchars($program['icon']); ?> fa-2x"></i>
                                                </td>
                                                <td><?php echo htmlspecialchars($program['title']); ?></td>
                                                <td><?php echo htmlspecialchars($program['description']); ?></td>
                                                <td class="text-center"><?php echo $program['order_number']; ?></td>
                                                <td class="text-center">
                                                    <?php if ($program['is_active']): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Nonaktif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="edit.php?id=<?php echo $program['id']; ?>"
                                                        class="btn btn-sm btn-info" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-danger btn-delete"
                                                        data-id="<?php echo $program['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($program['title']); ?>"
                                                        data-token="<?php echo $csrf_token; ?>" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada data program</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
                <p>Apakah Anda yakin ingin menghapus program "<span id="delete-name"></span>"?</p>
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
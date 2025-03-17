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

        // Cek apakah user yang akan dihapus adalah admin yang sedang login
        if ($id == $_SESSION['admin_id']) {
            throw new Exception('Anda tidak dapat menghapus akun yang sedang digunakan');
        }

        // Periksa apakah user ada
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            throw new Exception('Pengguna tidak ditemukan');
        }

        // Hapus user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        $message = 'Pengguna berhasil dihapus';
        $message_type = 'success';
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Ambil data pengguna dari database
try {
    $stmt = $pdo->query("SELECT * FROM users ORDER BY name");
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = 'Error: ' . $e->getMessage();
    $message_type = 'danger';
    $users = [];
}

// Generate CSRF token
$csrf_token = generateCsrfToken();

// Set page title
$page_title = 'Manajemen Pengguna';

// Load header
include_once ADMIN_PATH . '/includes/header.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">Manajemen Pengguna</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Pengguna</li>
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
                            <h4 class="card-title">Daftar Pengguna</h4>
                            <a href="add.php" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Tambah Pengguna
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered datatable">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="20%">Nama</th>
                                        <th width="15%">Username</th>
                                        <th width="20%">Email</th>
                                        <th width="10%">Role</th>
                                        <th width="15%">Login Terakhir</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($users) > 0): ?>
                                        <?php foreach ($users as $index => $user): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <?php if ($user['role'] == 'admin'): ?>
                                                        <span class="badge bg-primary">Administrator</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-info">Editor</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Belum login'; ?>
                                                </td>
                                                <td>
                                                    <a href="edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-info"
                                                        title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                                        <a href="#" class="btn btn-sm btn-danger btn-delete"
                                                            data-id="<?php echo $user['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($user['name']); ?>"
                                                            data-token="<?php echo $csrf_token; ?>" title="Hapus">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-secondary" disabled
                                                            title="Tidak dapat menghapus akun yang sedang digunakan">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Tidak ada data pengguna</td>
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
                <p>Apakah Anda yakin ingin menghapus pengguna "<span id="delete-name"></span>"?</p>
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

        // Fix untuk dropdown user information
        const dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        dropdownElementList.map(function (dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
        });
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
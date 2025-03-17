 
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
$page_title = 'Manajemen Pesan';

// Filter status
$status_filter = isset($_GET['status']) ? sanitizeInput($_GET['status']) : '';
$search_query = isset($_GET['search']) ? sanitizeInput($_GET['search']) : '';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Hapus pesan jika ada request
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        // Verifikasi CSRF token
        if (!isset($_GET['token']) || !verifyCsrfToken($_GET['token'])) {
            throw new Exception('Token keamanan tidak valid');
        }

        // Periksa apakah pesan ada
        $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
        $stmt->execute([$id]);
        $message = $stmt->fetch();

        if (!$message) {
            throw new Exception('Pesan tidak ditemukan');
        }

        // Hapus pesan
        $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->execute([$id]);

        // Log aktivitas
        if (isset($_SESSION['admin_id'])) {
            logActivity($_SESSION['admin_id'], 'menghapus pesan', [
                'message_id' => $id,
                'from' => $message['name']
            ]);
        }

        $_SESSION['message'] = 'Pesan berhasil dihapus';
        $_SESSION['message_type'] = 'success';
    } catch (Exception $e) {
        $_SESSION['message'] = 'Error: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }

    // Redirect ke halaman pesan
    header('Location: index.php' . ($status_filter ? "?status=$status_filter" : ""));
    exit;
}

// Ambil data pesan dari database
try {
    // Build query
    $query = "SELECT * FROM messages";
    $count_query = "SELECT COUNT(*) FROM messages";
    $params = [];
    $conditions = [];

    if (!empty($status_filter)) {
        $conditions[] = "status = ?";
        $params[] = $status_filter;
    }

    if (!empty($search_query)) {
        $conditions[] = "(name LIKE ? OR email LIKE ? OR message LIKE ?)";
        $params[] = "%$search_query%";
        $params[] = "%$search_query%";
        $params[] = "%$search_query%";
    }

    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
        $count_query .= " WHERE " . implode(" AND ", $conditions);
    }

    $query .= " ORDER BY created_at DESC LIMIT ?, ?";
    $params[] = $offset;
    $params[] = $per_page;

    // Get total count
    $stmt = $pdo->prepare($count_query);
    $stmt->execute(array_slice($params, 0, count($params) - 2));
    $total_items = $stmt->fetchColumn();
    $total_pages = ceil($total_items / $per_page);

    // Get messages for current page
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $messages = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['message'] = 'Error: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    $messages = [];
    $total_pages = 1;
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
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
                <?php echo $_SESSION['message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php
            // Clear message after displaying
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        <?php endif; ?>

        <!-- Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Daftar Pesan</h4>

                        <!-- Filter and Search -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="btn-group" role="group">
                                    <a href="index.php" class="btn <?php echo empty($status_filter) ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        Semua
                                    </a>
                                    <a href="index.php?status=unread" class="btn <?php echo $status_filter === 'unread' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        Belum Dibaca
                                    </a>
                                    <a href="index.php?status=read" class="btn <?php echo $status_filter === 'read' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        Sudah Dibaca
                                    </a>
                                    <a href="index.php?status=replied" class="btn <?php echo $status_filter === 'replied' ? 'btn-primary' : 'btn-outline-primary'; ?>">
                                        Sudah Dibalas
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <form action="" method="get" class="d-flex">
                                    <?php if (!empty($status_filter)): ?>
                                        <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
                                    <?php endif; ?>
                                    <input type="text" class="form-control me-2" name="search" placeholder="Cari pesan..." value="<?php echo htmlspecialchars($search_query); ?>">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Messages Table -->
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Pengirim</th>
                                        <th>Kontak</th>
                                        <th>Kategori</th>
                                        <th>Pesan</th>
                                        <th>Tanggal</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($messages) > 0): ?>
                                        <?php foreach ($messages as $msg): ?>
                                            <tr class="<?php echo $msg['status'] === 'unread' ? 'table-active fw-bold' : ''; ?>">
                                                <td><?php echo htmlspecialchars($msg['name']); ?></td>
                                                <td>
                                                    <?php echo htmlspecialchars($msg['email']); ?>
                                                    <?php if (!empty($msg['phone'])): ?>
                                                        <br><small><?php echo htmlspecialchars($msg['phone']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($msg['category']); ?></td>
                                                <td><?php echo htmlspecialchars(truncateText($msg['message'], 50)); ?></td>
                                                <td><?php echo formatDate($msg['created_at'], true); ?></td>
                                                <td>
                                                    <?php if ($msg['status'] === 'unread'): ?>
                                                        <span class="badge bg-danger">Belum Dibaca</span>
                                                    <?php elseif ($msg['status'] === 'read'): ?>
                                                        <span class="badge bg-warning text-dark">Sudah Dibaca</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Sudah Dibalas</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <a href="view.php?id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-info text-white" title="Lihat">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="reply.php?id=<?php echo $msg['id']; ?>" class="btn btn-sm btn-primary" title="Balas">
                                                        <i class="fas fa-reply"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-danger btn-delete" 
                                                       data-id="<?php echo $msg['id']; ?>" 
                                                       data-name="<?php echo htmlspecialchars($msg['name']); ?>"
                                                       data-token="<?php echo $csrf_token; ?>" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                Tidak ada pesan<?php echo !empty($status_filter) ? ' dengan status "' . $status_filter . '"' : ''; ?><?php echo !empty($search_query) ? ' yang sesuai dengan pencarian' : ''; ?>.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo ($page <= 1) ? '#' : 'index.php?page=' . ($page - 1) . ($status_filter ? '&status=' . $status_filter : '') . ($search_query ? '&search=' . urlencode($search_query) : ''); ?>">
                                            &laquo;
                                        </a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="index.php?page=<?php echo $i; ?><?php echo $status_filter ? '&status=' . $status_filter : ''; ?><?php echo $search_query ? '&search=' . urlencode($search_query) : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?php echo ($page >= $total_pages) ? '#' : 'index.php?page=' . ($page + 1) . ($status_filter ? '&status=' . $status_filter : '') . ($search_query ? '&search=' . urlencode($search_query) : ''); ?>">
                                            &raquo;
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
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
                <p>Apakah Anda yakin ingin menghapus pesan dari "<span id="delete-name"></span>"?</p>
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
                document.getElementById('delete-link').href = `index.php?action=delete&id=${id}&token=${token}${window.location.search.includes('status=') ? '&status=<?php echo $status_filter; ?>' : ''}`;

                deleteModal.show();
            });
        });
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
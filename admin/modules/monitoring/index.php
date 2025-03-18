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
$page_title = 'Manajemen Monitoring';

// Inisialisasi variabel pesan
$message = '';
$message_type = '';

// Cek pesan dari session
if (isset($_SESSION['success_message'])) {
    $message = $_SESSION['success_message'];
    $message_type = 'success';
    // Hapus pesan dari session setelah digunakan
    unset($_SESSION['success_message']);
} elseif (isset($_SESSION['error_message'])) {
    $message = $_SESSION['error_message'];
    $message_type = 'danger';
    // Hapus pesan dari session setelah digunakan
    unset($_SESSION['error_message']);
}

// Cek pesan dari URL (redirect)
if (isset($_GET['message']) && !empty($_GET['message'])) {
    $message = $_GET['message'];
    $message_type = $_GET['message_type'] ?? 'success';
}

// Proses hapus data
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        // Verifikasi CSRF token
        if (!isset($_GET['token']) || !verifyCsrfToken($_GET['token'])) {
            throw new Exception('Token keamanan tidak valid');
        }

        // Periksa apakah data monitoring ada
        $stmt = $pdo->prepare("SELECT * FROM monitoring WHERE id = ?");
        $stmt->execute([$id]);
        $monitoring = $stmt->fetch();

        if (!$monitoring) {
            throw new Exception('Data monitoring tidak ditemukan');
        }

        // Hapus data monitoring
        $stmt = $pdo->prepare("DELETE FROM monitoring WHERE id = ?");
        $stmt->execute([$id]);

        // Simpan pesan sukses ke session
        $_SESSION['success_message'] = 'Data monitoring berhasil dihapus';

        // Redirect untuk mencegah resubmit saat refresh
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Error: ' . $e->getMessage();
        header('Location: index.php');
        exit;
    }
}

// Ambil data monitoring dari database
try {
    $stmt = $pdo->query("SELECT * FROM monitoring ORDER BY date DESC");
    $monitorings = $stmt->fetchAll();
} catch (PDOException $e) {
    $message = 'Error: ' . $e->getMessage();
    $message_type = 'danger';
    $monitorings = [];
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
                <h4 class="text-themecolor">Manajemen Monitoring</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Monitoring</li>
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
                            <h4 class="card-title">Daftar Monitoring</h4>
                            <a href="add.php" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Tambah Monitoring
                            </a>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-bordered datatable">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th width="15%">Tanggal</th>
                                        <th width="20%">Lokasi</th>
                                        <th width="20%">Kegiatan</th>
                                        <th width="20%">Status</th>
                                        <th width="20%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($monitorings) > 0): ?>
                                        <?php foreach ($monitorings as $index => $monitoring): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($monitoring['date'])); ?></td>
                                                <td><?php echo htmlspecialchars($monitoring['location']); ?></td>
                                                <td><?php echo htmlspecialchars($monitoring['activity']); ?></td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    switch ($monitoring['status']) {
                                                        case 'pending':
                                                            $status_class = 'bg-warning';
                                                            $status_text = 'Menunggu';
                                                            break;
                                                        case 'in_progress':
                                                            $status_class = 'bg-info';
                                                            $status_text = 'Sedang Berjalan';
                                                            break;
                                                        case 'completed':
                                                            $status_class = 'bg-success';
                                                            $status_text = 'Selesai';
                                                            break;
                                                        case 'cancelled':
                                                            $status_class = 'bg-danger';
                                                            $status_text = 'Dibatalkan';
                                                            break;
                                                        default:
                                                            $status_text = ucfirst($monitoring['status']);
                                                    }
                                                    ?>
                                                    <span
                                                        class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                </td>
                                                <td>
                                                    <a href="view.php?id=<?php echo $monitoring['id']; ?>"
                                                        class="btn btn-sm btn-info" title="Lihat">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit.php?id=<?php echo $monitoring['id']; ?>"
                                                        class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-danger btn-delete"
                                                        data-id="<?php echo $monitoring['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($monitoring['activity']); ?>"
                                                        data-token="<?php echo $csrf_token; ?>" title="Hapus">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">Tidak ada data monitoring</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Widget Monitoring Dashboard -->
        <div class="row mt-4">
            <div class="col-12">
                <?php
                // Ambil ringkasan data monitoring
                try {
                    // Total monitoring
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM monitoring");
                    $total_monitoring = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                    
                    // Monitoring bulan ini
                    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM monitoring WHERE MONTH(date) = ? AND YEAR(date) = ?");
                    $stmt->execute([date('n'), date('Y')]);
                    $monthly_monitoring = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
                    
                    // Monitoring berdasarkan status
                    $stmt = $pdo->query("
                        SELECT status, COUNT(*) as count 
                        FROM monitoring 
                        GROUP BY status
                    ");
                    $status_counts = [];
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $status_counts[$row['status']] = $row['count'];
                    }
                    
                    // Monitoring terbaru
                    $stmt = $pdo->query("
                        SELECT m.*, u.name as created_by_name 
                        FROM monitoring m 
                        LEFT JOIN users u ON m.created_by = u.id 
                        ORDER BY m.date DESC 
                        LIMIT 5
                    ");
                    $recent_monitorings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                } catch (PDOException $e) {
                    error_log('Error getting monitoring data for dashboard: ' . $e->getMessage());
                    $total_monitoring = 0;
                    $monthly_monitoring = 0;
                    $status_counts = [];
                    $recent_monitorings = [];
                }

                // Status yang tersedia
                $status_data = [
                    'pending' => [
                        'label' => 'Menunggu',
                        'count' => $status_counts['pending'] ?? 0,
                        'class' => 'bg-warning text-dark'
                    ],
                    'in_progress' => [
                        'label' => 'Sedang Berjalan',
                        'count' => $status_counts['in_progress'] ?? 0,
                        'class' => 'bg-info text-white'
                    ],
                    'completed' => [
                        'label' => 'Selesai',
                        'count' => $status_counts['completed'] ?? 0,
                        'class' => 'bg-success text-white'
                    ],
                    'cancelled' => [
                        'label' => 'Dibatalkan',
                        'count' => $status_counts['cancelled'] ?? 0,
                        'class' => 'bg-danger text-white'
                    ]
                ];
                ?>

                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title mb-0">Ringkasan Monitoring</h4>
                        </div>
                        
                        <!-- Quick Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><?php echo $total_monitoring; ?></h5>
                                                <p class="mb-0 small">Total Monitoring</p>
                                            </div>
                                            <div class="dashboard-icon">
                                                <i class="fas fa-clipboard-list fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-info text-white">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><?php echo $monthly_monitoring; ?></h5>
                                                <p class="mb-0 small">Bulan Ini</p>
                                            </div>
                                            <div class="dashboard-icon">
                                                <i class="fas fa-calendar-alt fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><?php echo $status_data['completed']['count']; ?></h5>
                                                <p class="mb-0 small">Selesai</p>
                                            </div>
                                            <div class="dashboard-icon">
                                                <i class="fas fa-check-circle fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0"><?php echo $status_data['in_progress']['count']; ?></h5>
                                                <p class="mb-0 small">Sedang Berjalan</p>
                                            </div>
                                            <div class="dashboard-icon">
                                                <i class="fas fa-cog fa-spin fa-2x"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Status Overview -->
                        <div class="row mb-4">
                            <div class="col-lg-12">
                                <h5 class="card-subtitle mb-3">Status Monitoring</h5>
                                <div class="progress-container">
                                    <?php foreach ($status_data as $status): ?>
                                        <?php
                                        $percentage = ($total_monitoring > 0) ? round(($status['count'] / $total_monitoring) * 100) : 0;
                                        ?>
                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span><?php echo $status['label']; ?></span>
                                                <span><?php echo $status['count']; ?> (<?php echo $percentage; ?>%)</span>
                                            </div>
                                            <div class="progress" style="height: 10px;">
                                                <div class="progress-bar <?php echo $status['class']; ?>" role="progressbar" 
                                                     style="width: <?php echo $percentage; ?>%;" 
                                                     aria-valuenow="<?php echo $percentage; ?>" 
                                                     aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Recent Monitoring if not empty -->
                        <?php if (!empty($recent_monitorings)): ?>
                        <div class="row">
                            <div class="col-lg-12">
                                <h5 class="card-subtitle mb-3">Monitoring Terbaru</h5>
                                <div class="table-responsive">
                                    <table class="table table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th>Tanggal</th>
                                                <th>Lokasi</th>
                                                <th>Kegiatan</th>
                                                <th>Status</th>
                                                <th>Dibuat Oleh</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($recent_monitorings as $monitoring): ?>
                                                <tr>
                                                    <td><?php echo date('d/m/Y', strtotime($monitoring['date'])); ?></td>
                                                    <td><?php echo htmlspecialchars($monitoring['location']); ?></td>
                                                    <td><?php echo htmlspecialchars($monitoring['activity']); ?></td>
                                                    <td>
                                                        <?php 
                                                        $status_class = '';
                                                        switch($monitoring['status']) {
                                                            case 'pending':
                                                                $status_class = 'bg-warning text-dark';
                                                                $status_text = 'Menunggu';
                                                                break;
                                                            case 'in_progress':
                                                                $status_class = 'bg-info text-white';
                                                                $status_text = 'Sedang Berjalan';
                                                                break;
                                                            case 'completed':
                                                                $status_class = 'bg-success text-white';
                                                                $status_text = 'Selesai';
                                                                break;
                                                            case 'cancelled':
                                                                $status_class = 'bg-danger text-white';
                                                                $status_text = 'Dibatalkan';
                                                                break;
                                                            default:
                                                                $status_text = ucfirst($monitoring['status']);
                                                        }
                                                        ?>
                                                        <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($monitoring['created_by_name'] ?? 'Sistem'); ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
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
                <p>Apakah Anda yakin ingin menghapus monitoring "<span id="delete-name"></span>"?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="delete-link" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-icon {
    opacity: 0.8;
}
.progress {
    background-color: rgba(0,0,0,0.05);
}
</style>

<!-- Custom Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Konfigurasi modal konfirmasi hapus
        const deleteModalElement = document.getElementById('deleteModal');
        if (deleteModalElement) {
            const deleteModal = new bootstrap.Modal(deleteModalElement);
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
        }

        // Auto-hide alert after 5 seconds
        const alertElement = document.querySelector('.alert');
        if (alertElement) {
            setTimeout(function () {
                const bsAlert = bootstrap.Alert.getInstance(alertElement);
                if (bsAlert) {
                    bsAlert.close();
                } else {
                    alertElement.classList.remove('show');
                    setTimeout(function () {
                        alertElement.remove();
                    }, 150);
                }
            }, 5000);
        }
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
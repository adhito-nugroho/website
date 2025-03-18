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
$page_title = 'Detail Monitoring';

// Cek apakah ada ID yang diberikan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'ID monitoring tidak valid';
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];

// Ambil data monitoring dari database
try {
    $stmt = $pdo->prepare("SELECT m.*, u.name as created_by_name 
                          FROM monitoring m 
                          LEFT JOIN users u ON m.created_by = u.id 
                          WHERE m.id = ?");
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

// Load header
include_once ADMIN_PATH . '/includes/header.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">Detail Monitoring</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Monitoring</a></li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="card-title">Informasi Monitoring</h4>
                            <div>
                                <a href="edit.php?id=<?php echo $monitoring['id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th width="20%">ID</th>
                                        <td><?php echo $monitoring['id']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal</th>
                                        <td><?php echo date('d/m/Y', strtotime($monitoring['date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Lokasi</th>
                                        <td><?php echo htmlspecialchars($monitoring['location']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Kegiatan</th>
                                        <td><?php echo htmlspecialchars($monitoring['activity']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
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
                                    </tr>
                                    <tr>
                                        <th>Deskripsi</th>
                                        <td><?php echo nl2br(htmlspecialchars($monitoring['description'] ?? '-')); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Hasil</th>
                                        <td><?php echo nl2br(htmlspecialchars($monitoring['result'] ?? '-')); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Catatan</th>
                                        <td><?php echo nl2br(htmlspecialchars($monitoring['notes'] ?? '-')); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Dibuat Oleh</th>
                                        <td><?php echo htmlspecialchars($monitoring['created_by_name'] ?? 'Sistem'); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Dibuat Pada</th>
                                        <td><?php echo date('d/m/Y H:i:s', strtotime($monitoring['created_at'])); ?>
                                        </td>
                                    </tr>
                                    <?php if (!empty($monitoring['updated_at']) && $monitoring['updated_at'] != $monitoring['created_at']): ?>
                                        <tr>
                                            <th>Diperbarui Pada</th>
                                            <td><?php echo date('d/m/Y H:i:s', strtotime($monitoring['updated_at'])); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4">
                            <h5>Timeline Status</h5>
                            <div class="timeline-container mt-3">
                                <div class="timeline">
                                    <div
                                        class="timeline-item <?php echo ($monitoring['status'] == 'pending' || $monitoring['status'] == 'in_progress' || $monitoring['status'] == 'completed') ? 'active' : ''; ?>">
                                        <div class="timeline-dot bg-warning"></div>
                                        <div class="timeline-content">
                                            <h6>Menunggu</h6>
                                            <p>Kegiatan monitoring dalam tahap persiapan</p>
                                        </div>
                                    </div>
                                    <div
                                        class="timeline-item <?php echo ($monitoring['status'] == 'in_progress' || $monitoring['status'] == 'completed') ? 'active' : ''; ?>">
                                        <div class="timeline-dot bg-info"></div>
                                        <div class="timeline-content">
                                            <h6>Sedang Berjalan</h6>
                                            <p>Kegiatan monitoring dalam tahap pelaksanaan</p>
                                        </div>
                                    </div>
                                    <div
                                        class="timeline-item <?php echo ($monitoring['status'] == 'completed') ? 'active' : ''; ?>">
                                        <div class="timeline-dot bg-success"></div>
                                        <div class="timeline-content">
                                            <h6>Selesai</h6>
                                            <p>Kegiatan monitoring telah selesai dilaksanakan</p>
                                        </div>
                                    </div>
                                    <?php if ($monitoring['status'] == 'cancelled'): ?>
                                        <div class="timeline-item active">
                                            <div class="timeline-dot bg-danger"></div>
                                            <div class="timeline-content">
                                                <h6>Dibatalkan</h6>
                                                <p>Kegiatan monitoring dibatalkan</p>
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
    </div>
</div>

<!-- Timeline CSS -->
<style>
    .timeline-container {
        padding: 20px 0;
    }

    .timeline {
        position: relative;
        max-width: 800px;
        margin: 0 auto;
    }

    .timeline::after {
        content: '';
        position: absolute;
        width: 2px;
        background-color: #e0e0e0;
        top: 0;
        bottom: 0;
        left: 20px;
        margin-left: -1px;
    }

    .timeline-item {
        padding: 10px 40px;
        position: relative;
        margin-bottom: 20px;
        opacity: 0.6;
    }

    .timeline-item.active {
        opacity: 1;
    }

    .timeline-dot {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        position: absolute;
        left: 18px;
        top: 15px;
        z-index: 1;
        border: 3px solid white;
    }

    .timeline-content {
        padding: 15px;
        background-color: white;
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        position: relative;
    }

    .timeline-content h6 {
        margin-top: 0;
        font-weight: bold;
    }

    .timeline-content p {
        margin-bottom: 0;
        color: #666;
    }
</style>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
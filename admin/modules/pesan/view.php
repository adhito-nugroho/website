 
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
$page_title = 'Lihat Pesan';

// Validasi parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = 'ID pesan tidak valid';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];

// Ambil data pesan dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    $message = $stmt->fetch();

    if (!$message) {
        $_SESSION['message'] = 'Pesan tidak ditemukan';
        $_SESSION['message_type'] = 'danger';
        header('Location: index.php');
        exit;
    }

    // Update status pesan menjadi 'read' jika sebelumnya 'unread'
    if ($message['status'] === 'unread') {
        $stmt = $pdo->prepare("UPDATE messages SET status = 'read' WHERE id = ?");
        $stmt->execute([$id]);
        
        // Log aktivitas
        if (isset($_SESSION['admin_id'])) {
            logActivity($_SESSION['admin_id'], 'membaca pesan', [
                'message_id' => $id,
                'from' => $message['name']
            ]);
        }
        
        // Update status di data message
        $message['status'] = 'read';
    }
} catch (PDOException $e) {
    $_SESSION['message'] = 'Error: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
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
                <h4 class="text-themecolor"><?php echo $page_title; ?></h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Pesan</a></li>
                        <li class="breadcrumb-item active"><?php echo $page_title; ?></li>
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
                            <h4 class="card-title mb-0">Detail Pesan</h4>
                            <div>
                                <a href="reply.php?id=<?php echo $message['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-reply"></i> Balas Pesan
                                </a>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <!-- Message Details -->
                                <div class="card message-card">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 class="mb-0">
                                                    <?php echo htmlspecialchars($message['name']); ?>
                                                    <small class="ms-2 text-muted">
                                                        (<?php echo htmlspecialchars($message['email']); ?>)
                                                    </small>
                                                </h5>
                                                <?php if (!empty($message['phone'])): ?>
                                                    <div class="small text-muted">
                                                        <i class="fas fa-phone-alt me-1"></i> <?php echo htmlspecialchars($message['phone']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-end">
                                                <span class="badge bg-<?php 
                                                    echo $message['status'] === 'unread' ? 'danger' : 
                                                        ($message['status'] === 'read' ? 'warning' : 'success'); 
                                                ?>">
                                                    <?php 
                                                        echo $message['status'] === 'unread' ? 'Belum Dibaca' : 
                                                            ($message['status'] === 'read' ? 'Sudah Dibaca' : 'Sudah Dibalas'); 
                                                    ?>
                                                </span>
                                                <div class="small text-muted mt-1">
                                                    <i class="fas fa-calendar-alt me-1"></i> <?php echo formatDate($message['created_at'], true); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <strong>Kategori:</strong> 
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($message['category']); ?></span>
                                        </div>
                                        <div class="message-content p-3 border rounded">
                                            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($message['ip_address'])): ?>
                                        <div class="card-footer bg-light">
                                            <div class="small text-muted">
                                                <i class="fas fa-info-circle me-1"></i> Dikirim dari IP: <?php echo htmlspecialchars($message['ip_address']); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <!-- Sender Info -->
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Informasi Pengirim</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <i class="fas fa-user me-2"></i> <strong>Nama:</strong> <?php echo htmlspecialchars($message['name']); ?>
                                            </li>
                                            <li class="list-group-item">
                                                <i class="fas fa-envelope me-2"></i> <strong>Email:</strong> 
                                                <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>"><?php echo htmlspecialchars($message['email']); ?></a>
                                            </li>
                                            <?php if (!empty($message['phone'])): ?>
                                                <li class="list-group-item">
                                                    <i class="fas fa-phone-alt me-2"></i> <strong>Telepon:</strong> 
                                                    <a href="tel:<?php echo htmlspecialchars($message['phone']); ?>"><?php echo htmlspecialchars($message['phone']); ?></a>
                                                </li>
                                            <?php endif; ?>
                                            <li class="list-group-item">
                                                <i class="fas fa-tag me-2"></i> <strong>Kategori:</strong> <?php echo htmlspecialchars($message['category']); ?>
                                            </li>
                                            <li class="list-group-item">
                                                <i class="fas fa-calendar-alt me-2"></i> <strong>Tanggal:</strong> <?php echo formatDate($message['created_at'], true); ?>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="card mt-3">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0">Aksi</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <a href="reply.php?id=<?php echo $message['id']; ?>" class="btn btn-primary">
                                                <i class="fas fa-reply me-1"></i> Balas Pesan
                                            </a>
                                            <a href="delete.php?id=<?php echo $message['id']; ?>" class="btn btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus pesan ini?');">
                                                <i class="fas fa-trash-alt me-1"></i> Hapus Pesan
                                            </a>
                                            <a href="index.php" class="btn btn-secondary">
                                                <i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
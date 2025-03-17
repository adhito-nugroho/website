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
$page_title = 'Dashboard';

// Ambil data admin yang sedang login
$current_admin = getCurrentAdmin();

// Dapatkan statistik untuk dashboard
try {
    // Jumlah layanan
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM services");
    $services_count = $stmt->fetch()['total'];

    // Jumlah program
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM programs");
    $programs_count = $stmt->fetch()['total'];

    // Jumlah publikasi
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM posts");
    $posts_count = $stmt->fetch()['total'];

    // Jumlah galeri
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM gallery");
    $gallery_count = $stmt->fetch()['total'];

    // Jumlah dokumen
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM documents");
    $documents_count = $stmt->fetch()['total'];

    // Jumlah pesan belum dibaca
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM messages WHERE status = 'unread'");
    $unread_messages = $stmt->fetch()['total'];

    // Total pengunjung (dummy data)
    $visitors_count = 15420;

    // Pesan terbaru
    $stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5");
    $latest_messages = $stmt->fetchAll();

    // Publikasi terbaru
    $stmt = $pdo->query("
        SELECT p.*, u.name as author_name 
        FROM posts p 
        LEFT JOIN users u ON p.created_by = u.id 
        ORDER BY p.created_at DESC 
        LIMIT 5
    ");
    $latest_posts = $stmt->fetchAll();

    // Aktivitas terbaru (dummy data)
    $recent_activities = [
        ['user' => 'Administrator', 'action' => 'menambahkan layanan baru', 'time' => date('Y-m-d H:i:s', strtotime('-1 hour'))],
        ['user' => 'Administrator', 'action' => 'memperbarui program', 'time' => date('Y-m-d H:i:s', strtotime('-3 hour'))],
        ['user' => 'Editor', 'action' => 'mengunggah dokumen baru', 'time' => date('Y-m-d H:i:s', strtotime('-5 hour'))],
        ['user' => 'Administrator', 'action' => 'menambahkan foto ke galeri', 'time' => date('Y-m-d H:i:s', strtotime('-1 day'))],
        ['user' => 'Editor', 'action' => 'menjawab pesan dari pengunjung', 'time' => date('Y-m-d H:i:s', strtotime('-2 day'))]
    ];
} catch (PDOException $e) {
    // Log error
    error_log('Dashboard Error: ' . $e->getMessage());
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
                <h4 class="text-themecolor">Dashboard</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Welcome Message -->
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Selamat Datang, <?php echo htmlspecialchars($current_admin['name']); ?>!</h4>
            <p>Anda login sebagai <strong><?php echo ucfirst(htmlspecialchars($current_admin['role'])); ?></strong>.
                Terakhir login pada
                <?php echo $current_admin['last_login'] ? date('d M Y H:i', strtotime($current_admin['last_login'])) : 'belum pernah'; ?>.
            </p>
        </div>

        <!-- Dashboard Stats -->
        <div class="row">
            <!-- Layanan -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex">
                            <div>
                                <h5 class="card-title text-muted">Layanan</h5>
                                <h2 class="font-weight-bold"><?php echo $services_count; ?></h2>
                            </div>
                            <div class="ms-auto">
                                <div class="stats-icon bg-primary text-white rounded-circle">
                                    <i class="fas fa-concierge-bell"></i>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $site_config['admin_url']; ?>/modules/layanan/index.php"
                            class="btn btn-sm btn-outline-primary mt-3">Kelola Layanan</a>
                    </div>
                </div>
            </div>

            <!-- Program -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex">
                            <div>
                                <h5 class="card-title text-muted">Program</h5>
                                <h2 class="font-weight-bold"><?php echo $programs_count; ?></h2>
                            </div>
                            <div class="ms-auto">
                                <div class="stats-icon bg-success text-white rounded-circle">
                                    <i class="fas fa-project-diagram"></i>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $site_config['admin_url']; ?>/modules/program/index.php"
                            class="btn btn-sm btn-outline-success mt-3">Kelola Program</a>
                    </div>
                </div>
            </div>

            <!-- Publikasi -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex">
                            <div>
                                <h5 class="card-title text-muted">Publikasi</h5>
                                <h2 class="font-weight-bold"><?php echo $posts_count; ?></h2>
                            </div>
                            <div class="ms-auto">
                                <div class="stats-icon bg-info text-white rounded-circle">
                                    <i class="fas fa-newspaper"></i>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $site_config['admin_url']; ?>/modules/publikasi/index.php"
                            class="btn btn-sm btn-outline-info mt-3">Kelola Publikasi</a>
                    </div>
                </div>
            </div>

            <!-- Pesan -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex">
                            <div>
                                <h5 class="card-title text-muted">Pesan Belum Dibaca</h5>
                                <h2 class="font-weight-bold"><?php echo $unread_messages; ?></h2>
                            </div>
                            <div class="ms-auto">
                                <div class="stats-icon bg-danger text-white rounded-circle">
                                    <i class="fas fa-envelope"></i>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $site_config['admin_url']; ?>/modules/pesan/index.php"
                            class="btn btn-sm btn-outline-danger mt-3">Kelola Pesan</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Row Stats -->
        <div class="row">
            <!-- Dokumen -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex">
                            <div>
                                <h5 class="card-title text-muted">Dokumen</h5>
                                <h2 class="font-weight-bold"><?php echo $documents_count; ?></h2>
                            </div>
                            <div class="ms-auto">
                                <div class="stats-icon bg-warning text-white rounded-circle">
                                    <i class="fas fa-file-alt"></i>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $site_config['admin_url']; ?>/modules/publikasi/index.php?tab=documents"
                            class="btn btn-sm btn-outline-warning mt-3">Kelola Dokumen</a>
                    </div>
                </div>
            </div>

            <!-- Galeri -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex">
                            <div>
                                <h5 class="card-title text-muted">Galeri</h5>
                                <h2 class="font-weight-bold"><?php echo $gallery_count; ?></h2>
                            </div>
                            <div class="ms-auto">
                                <div class="stats-icon bg-purple text-white rounded-circle">
                                    <i class="fas fa-images"></i>
                                </div>
                            </div>
                        </div>
                        <a href="<?php echo $site_config['admin_url']; ?>/modules/galeri/index.php"
                            class="btn btn-sm btn-outline-secondary mt-3">Kelola Galeri</a>
                    </div>
                </div>
            </div>

            <!-- Pengunjung -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex">
                            <div>
                                <h5 class="card-title text-muted">Pengunjung</h5>
                                <h2 class="font-weight-bold"><?php echo number_format($visitors_count); ?></h2>
                            </div>
                            <div class="ms-auto">
                                <div class="stats-icon bg-teal text-white rounded-circle">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                        <button class="btn btn-sm btn-outline-teal mt-3" disabled>Statistik Pengunjung</button>
                    </div>
                </div>
            </div>

            <!-- Waktu Server -->
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex">
                            <div>
                                <h5 class="card-title text-muted">Waktu Server</h5>
                                <h2 class="font-weight-bold" id="serverTime"><?php echo date('H:i:s'); ?></h2>
                                <p class="text-muted"><?php echo date('d F Y'); ?></p>
                            </div>
                            <div class="ms-auto">
                                <div class="stats-icon bg-dark text-white rounded-circle">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts & Recent Data -->
        <div class="row">
            <!-- Chart Pengunjung -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Statistik Pengunjung</h5>
                        <div class="chart-container" style="position: relative; height:300px;">
                            <canvas id="visitorChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Aktivitas Terbaru -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Aktivitas Terbaru</h5>
                        <div class="activity-feed">
                            <?php foreach ($recent_activities as $activity): ?>
                                <div class="activity-item d-flex align-items-start mb-3">
                                    <div class="activity-icon me-3">
                                        <span class="avatar avatar-xs bg-primary text-white rounded-circle">
                                            <?php echo substr($activity['user'], 0, 1); ?>
                                        </span>
                                    </div>
                                    <div class="activity-content">
                                        <strong><?php echo htmlspecialchars($activity['user']); ?></strong>
                                        <?php echo htmlspecialchars($activity['action']); ?>
                                        <div class="text-muted small">
                                            <?php echo timeAgo($activity['time']); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Data -->
        <div class="row">
            <!-- Pesan Terbaru -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Pesan Terbaru</h5>

                        <?php if (count($latest_messages) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nama</th>
                                            <th>Kategori</th>
                                            <th>Tanggal</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($latest_messages as $message): ?>
                                            <tr>
                                                <td>
                                                    <a
                                                        href="<?php echo $site_config['admin_url']; ?>/modules/pesan/view.php?id=<?php echo $message['id']; ?>">
                                                        <?php echo htmlspecialchars($message['name']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo ucfirst(htmlspecialchars($message['category'])); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($message['created_at'])); ?></td>
                                                <td>
                                                    <?php if ($message['status'] === 'unread'): ?>
                                                        <span class="badge bg-danger">Belum Dibaca</span>
                                                    <?php elseif ($message['status'] === 'read'): ?>
                                                        <span class="badge bg-warning">Sudah Dibaca</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success">Dijawab</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mt-3">
                                Belum ada pesan masuk.
                            </div>
                        <?php endif; ?>

                        <a href="<?php echo $site_config['admin_url']; ?>/modules/pesan/index.php"
                            class="btn btn-sm btn-outline-primary mt-3">Lihat Semua Pesan</a>
                    </div>
                </div>
            </div>

            <!-- Publikasi Terbaru -->
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Publikasi Terbaru</h5>

                        <?php if (count($latest_posts) > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Judul</th>
                                            <th>Kategori</th>
                                            <th>Penulis</th>
                                            <th>Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($latest_posts as $post): ?>
                                            <tr>
                                                <td>
                                                    <a
                                                        href="<?php echo $site_config['admin_url']; ?>/modules/publikasi/edit.php?id=<?php echo $post['id']; ?>">
                                                        <?php echo htmlspecialchars($post['title']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo ucfirst(htmlspecialchars($post['category'])); ?></td>
                                                <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($post['publish_date'])); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info mt-3">
                                Belum ada publikasi.
                            </div>
                        <?php endif; ?>

                        <a href="<?php echo $site_config['admin_url']; ?>/modules/publikasi/index.php"
                            class="btn btn-sm btn-outline-primary mt-3">Lihat Semua Publikasi</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Server Time Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Server Time
        function updateServerTime() {
            const serverTimeElement = document.getElementById('serverTime');
            let time = new Date();

            const hours = String(time.getHours()).padStart(2, '0');
            const minutes = String(time.getMinutes()).padStart(2, '0');
            const seconds = String(time.getSeconds()).padStart(2, '0');

            serverTimeElement.textContent = hours + ':' + minutes + ':' + seconds;
        }

        setInterval(updateServerTime, 1000);

        // Visitor Chart
        const ctx = document.getElementById('visitorChart').getContext('2d');
        const visitorChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                datasets: [{
                    label: 'Pengunjung Website',
                    data: [1200, 1900, 1500, 2200, 1800, 2400, 2800, 3100, 2900, 3300, 3500, 4000],
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 2,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
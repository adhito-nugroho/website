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
$page_title = 'Detail Statistik';

// Cek apakah ada ID yang diberikan
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = 'ID statistik tidak valid';
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];

// Ambil data statistik dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM statistics WHERE id = ?");
    $stmt->execute([$id]);
    $statistic = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$statistic) {
        $_SESSION['error_message'] = 'Data statistik tidak ditemukan';
        header('Location: index.php');
        exit;
    }

    // Decode data JSON
    $data_json = json_decode($statistic['data_json'], true);
    $labels = $data_json['labels'] ?? [];
    $data = $data_json['data'] ?? [];

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
                <h4 class="text-themecolor">Detail Statistik</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Statistik</a></li>
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
                        <h4 class="card-title"><?php echo htmlspecialchars($statistic['title']); ?></h4>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <th width="30%">Kategori</th>
                                        <td>: <?php echo htmlspecialchars($statistic['category']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Tahun</th>
                                        <td>: <?php echo $statistic['year']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Satuan</th>
                                        <td>: <?php echo htmlspecialchars($statistic['unit'] ?? 'Tidak ada'); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Dibuat</th>
                                        <td>: <?php echo date('d/m/Y H:i', strtotime($statistic['created_at'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Terakhir Diupdate</th>
                                        <td>:
                                            <?php echo date('d/m/Y H:i', strtotime($statistic['updated_at'] ?? $statistic['created_at'])); ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <h5>Data Statistik</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th width="5%">No</th>
                                                <th width="45%">Label</th>
                                                <th width="50%">Nilai
                                                    (<?php echo htmlspecialchars($statistic['unit'] ?? ''); ?>)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (!empty($labels) && !empty($data)): ?>
                                                <?php foreach ($labels as $index => $label): ?>
                                                    <tr>
                                                        <td><?php echo $index + 1; ?></td>
                                                        <td><?php echo htmlspecialchars($label); ?></td>
                                                        <td><?php echo number_format($data[$index], 0, ',', '.'); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3" class="text-center">Tidak ada data statistik</td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="2" class="text-end">Total</th>
                                                <th><?php echo number_format(array_sum($data), 0, ',', '.'); ?></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5>Visualisasi Data</h5>
                                <div class="chart-container" style="position: relative; height:400px;">
                                    <canvas id="statisticChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <a href="edit.php?id=<?php echo $statistic['id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Chart.js script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('statisticChart').getContext('2d');

        // Get data from PHP
        const labels = <?php echo json_encode($labels); ?>;
        const data = <?php echo json_encode($data); ?>;
        const unit = <?php echo json_encode($statistic['unit'] ?? ''); ?>;

        // Create chart
        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Nilai (' + unit + ')',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: '<?php echo htmlspecialchars($statistic['title']); ?> (<?php echo $statistic['year']; ?>)'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Nilai (' + unit + ')'
                        }
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
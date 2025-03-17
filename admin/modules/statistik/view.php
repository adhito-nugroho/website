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

// Validasi parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = 'ID statistik tidak valid';
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

$id = (int) $_GET['id'];

// Ambil data statistik dari database
try {
    $stmt = $pdo->prepare("SELECT * FROM statistics WHERE id = ?");
    $stmt->execute([$id]);
    $statistic = $stmt->fetch();

    if (!$statistic) {
        $_SESSION['message'] = 'Data statistik tidak ditemukan';
        $_SESSION['message_type'] = 'danger';
        header('Location: index.php');
        exit;
    }
} catch (PDOException $e) {
    $_SESSION['message'] = 'Error: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// Parse JSON data
try {
    $data_json = json_decode($statistic['data_json'], true);
    if (!isset($data_json['labels']) || !isset($data_json['data'])) {
        throw new Exception('Format data JSON tidak valid');
    }
    $labels = $data_json['labels'];
    $values = $data_json['data'];
} catch (Exception $e) {
    $labels = [];
    $values = [];
    $_SESSION['message'] = 'Error parsing data JSON: ' . $e->getMessage();
    $_SESSION['message_type'] = 'warning';
}

// Format label kategori
$category_labels = [
    'forest-area' => 'Luas Kawasan Hutan',
    'forest-production' => 'Produksi Hasil Hutan',
    'rehabilitation' => 'Rehabilitasi Hutan',
    'social-forestry' => 'Perhutanan Sosial',
    'forest-fire' => 'Kebakaran Hutan',
    'other' => 'Lainnya'
];

// Set page title
$page_title = 'Detail Statistik';

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
            <!-- Info Statistik -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Informasi Statistik</h4>
                        <hr>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Judul</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($statistic['title']); ?></dd>

                            <dt class="col-sm-4">Kategori</dt>
                            <dd class="col-sm-8">
                                <?php echo htmlspecialchars($category_labels[$statistic['category']] ?? $statistic['category']); ?>
                            </dd>

                            <dt class="col-sm-4">Tahun</dt>
                            <dd class="col-sm-8"><?php echo $statistic['year']; ?></dd>
                            
                            <dt class="col-sm-4">Satuan</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($statistic['unit'] ?? '-'); ?></dd>

                            <dt class="col-sm-4">Dibuat</dt>
                            <dd class="col-sm-8"><?php echo date('d/m/Y H:i', strtotime($statistic['created_at'])); ?>
                            </dd>

                            <dt class="col-sm-4">Diperbarui</dt>
                            <dd class="col-sm-8"><?php echo date('d/m/Y H:i', strtotime($statistic['updated_at'])); ?>
                            </dd>
                        </dl>
                        <hr>
                        <div class="d-flex">
                            <a href="edit.php?id=<?php echo $statistic['id']; ?>" class="btn btn-warning me-2">Edit</a>
                            <a href="index.php" class="btn btn-secondary">Kembali</a>
                        </div>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-body">
                        <h4 class="card-title">Data Mentah</h4>
                        <hr>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Label</th>
                                        <th>Nilai<?php echo !empty($statistic['unit']) ? ' (' . htmlspecialchars($statistic['unit']) . ')' : ''; ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($labels)): ?>
                                        <?php for ($i = 0; $i < count($labels); $i++): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($labels[$i]); ?></td>
                                                <td class="text-end"><?php echo number_format($values[$i], 2); ?></td>
                                            </tr>
                                        <?php endfor; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="text-center">Tidak ada data</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                                <?php if (!empty($values)): ?>
                                    <tfoot>
                                        <tr>
                                            <th>Total</th>
                                            <th class="text-end"><?php echo number_format(array_sum($values), 2); ?></th>
                                        </tr>
                                    </tfoot>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">
                            Visualisasi Grafik
                            <div class="float-end">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary" id="chartTypeBar">
                                        <i class="fas fa-chart-bar"></i> Bar
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="chartTypeLine">
                                        <i class="fas fa-chart-line"></i> Line
                                    </button>
                                    <button type="button" class="btn btn-outline-primary" id="chartTypePie">
                                        <i class="fas fa-chart-pie"></i> Pie
                                    </button>
                                </div>
                            </div>
                        </h4>
                        <hr>
                        <div style="height: 400px;">
                            <canvas id="dataChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="card mt-4">
                    <div class="card-body">
                        <h4 class="card-title">Statistik Dasar</h4>
                        <hr>
                        <?php if (!empty($values)): ?>
                            <div class="row">
                                <div class="col-md-3 col-sm-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Total</h5>
                                            <h3 class="mb-0"><?php echo number_format(array_sum($values), 2); ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Rata-rata</h5>
                                            <h3 class="mb-0">
                                                <?php echo number_format(array_sum($values) / count($values), 2); ?>
                                            </h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Min</h5>
                                            <h3 class="mb-0"><?php echo number_format(min($values), 2); ?></h3>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="card bg-light">
                                        <div class="card-body text-center">
                                            <h5 class="card-title">Max</h5>
                                            <h3 class="mb-0"><?php echo number_format(max($values), 2); ?></h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button class="btn btn-primary" id="downloadPDF">
                                    <i class="fas fa-file-pdf"></i> Unduh PDF
                                </button>
                                <button class="btn btn-success" id="downloadExcel">
                                    <i class="fas fa-file-excel"></i> Unduh Excel
                                </button>
                                <button class="btn btn-info" id="downloadImage">
                                    <i class="fas fa-file-image"></i> Unduh Gambar
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info">
                                Tidak ada data untuk dihitung statistik dasar.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- Load html2canvas & jsPDF for export functionality -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>

<!-- Custom Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Chart data
        const labels = <?php echo json_encode($labels); ?>;
        const values = <?php echo json_encode($values); ?>;

        // Generate random colors for chart
        function generateColors(count) {
            const colors = [];
            const backgroundColors = [];

            for (let i = 0; i < count; i++) {
                const r = Math.floor(Math.random() * 255);
                const g = Math.floor(Math.random() * 255);
                const b = Math.floor(Math.random() * 255);

                colors.push(`rgba(${r}, ${g}, ${b}, 1)`);
                backgroundColors.push(`rgba(${r}, ${g}, ${b}, 0.2)`);
            }

            return {
                borderColors: colors,
                backgroundColors: backgroundColors
            };
        }

        // Initialize colors
        const colorSet = generateColors(labels.length);

        // Initialize chart
        let chartType = 'bar';
        let dataChart;

        function createChart() {
            const ctx = document.getElementById('dataChart').getContext('2d');

            let chartConfig = {
                type: chartType,
                data: {
                    labels: labels,
                    datasets: [{
                        label: '<?php echo addslashes($statistic['title']) . (!empty($statistic['unit']) ? ' (' . addslashes($statistic['unit']) . ')' : ''); ?>',
                        data: values,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            };

            // Chart type specific configurations
            if (chartType === 'bar' || chartType === 'line') {
                chartConfig.data.datasets[0].backgroundColor = colorSet.backgroundColors;
                chartConfig.data.datasets[0].borderColor = colorSet.borderColors;
                chartConfig.data.datasets[0].borderWidth = 1;

                chartConfig.options.scales = {
                    y: {
                        beginAtZero: true
                    }
                };
            } else if (chartType === 'pie') {
                chartConfig.data.datasets[0].backgroundColor = colorSet.borderColors;
                chartConfig.data.datasets[0].borderColor = '#fff';
                chartConfig.data.datasets[0].borderWidth = 1;
            }

            // Destroy previous chart if exists
            if (dataChart) {
                dataChart.destroy();
            }

            // Create new chart
            dataChart = new Chart(ctx, chartConfig);
        }

        // Create initial chart
        createChart();

        // Chart type change buttons
        document.getElementById('chartTypeBar').addEventListener('click', function () {
            chartType = 'bar';
            createChart();
            updateActiveButton(this);
        });

        document.getElementById('chartTypeLine').addEventListener('click', function () {
            chartType = 'line';
            createChart();
            updateActiveButton(this);
        });

        document.getElementById('chartTypePie').addEventListener('click', function () {
            chartType = 'pie';
            createChart();
            updateActiveButton(this);
        });

        // Update active button
        function updateActiveButton(activeButton) {
            document.querySelectorAll('.btn-group .btn').forEach(btn => {
                btn.classList.remove('active');
                btn.classList.add('btn-outline-primary');
                btn.classList.remove('btn-primary');
            });

            activeButton.classList.add('active');
            activeButton.classList.remove('btn-outline-primary');
            activeButton.classList.add('btn-primary');
        }

        // Set initial active button
        updateActiveButton(document.getElementById('chartTypeBar'));

        // Export functionality
        // Export to PDF
        document.getElementById('downloadPDF').addEventListener('click', function () {
            // Create a PDF object
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF('landscape');

            // Add title
            pdf.setFontSize(18);
            pdf.text('<?php echo addslashes($statistic['title']); ?>', 14, 22);

            // Add metadata
            pdf.setFontSize(12);
            pdf.text('Kategori: <?php echo addslashes($category_labels[$statistic['category']] ?? $statistic['category']); ?>', 14, 30);
            pdf.text('Tahun: <?php echo $statistic['year']; ?>', 14, 36);

            // Convert chart to image
            const canvas = document.getElementById('dataChart');

            // Add chart image to PDF
            const imgData = canvas.toDataURL('image/png');
            pdf.addImage(imgData, 'PNG', 14, 45, 270, 130);

            // Add table data
            pdf.setFontSize(14);
            pdf.text('Data Statistik', 14, 190);

            let yPos = 200;
            const cellWidth = 70;

            // Table header
            pdf.setFillColor(240, 240, 240);
            pdf.rect(14, yPos - 6, cellWidth, 8, 'F');
            pdf.rect(14 + cellWidth, yPos - 6, cellWidth, 8, 'F');

            pdf.setFontSize(12);
            pdf.text('Label', 14 + 5, yPos);
            pdf.text('Nilai', 14 + cellWidth + 5, yPos);

            yPos += 8;

            // Table rows
            for (let i = 0; i < labels.length; i++) {
                pdf.text(labels[i], 14 + 5, yPos);
                pdf.text(values[i].toString(), 14 + cellWidth + 5, yPos);
                yPos += 8;

                // Add a new page if we run out of space
                if (yPos > 270) {
                    pdf.addPage();
                    yPos = 20;
                }
            }

            // Save PDF
            pdf.save('<?php echo addslashes($statistic['title']); ?>_<?php echo $statistic['year']; ?>.pdf');
        });

        // Export to Excel
        document.getElementById('downloadExcel').addEventListener('click', function () {
            // Create Excel-like CSV content
            let csvContent = 'Label,Nilai\n';

            // Add data rows
            for (let i = 0; i < labels.length; i++) {
                csvContent += `"${labels[i]}",${values[i]}\n`;
            }

            // Add total row
            csvContent += `"Total",${values.reduce((a, b) => a + b, 0)}\n`;

            // Create download link
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.setAttribute('href', url);
            link.setAttribute('download', '<?php echo addslashes($statistic['title']); ?>_<?php echo $statistic['year']; ?>.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // Export to Image
        document.getElementById('downloadImage').addEventListener('click', function () {
            const canvas = document.getElementById('dataChart');
            const link = document.createElement('a');
            link.download = '<?php echo addslashes($statistic['title']); ?>_<?php echo $statistic['year']; ?>.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
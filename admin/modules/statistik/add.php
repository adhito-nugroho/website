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

// Inisialisasi variabel
$title = '';
$category = '';
$year = date('Y');
$errors = [];

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi dan validasi input
    $title = sanitizeInput($_POST['title'] ?? '');
    $category = sanitizeInput($_POST['category'] ?? '');
    $year = (int) ($_POST['year'] ?? date('Y'));
    $unit = sanitizeInput($_POST['unit'] ?? '');
    
    // Validasi input
    if (empty($title)) {
        $errors[] = 'Judul statistik wajib diisi';
    }

    if (empty($category)) {
        $errors[] = 'Kategori statistik wajib diisi';
    }

    if ($year < 2000 || $year > 2100) {
        $errors[] = 'Tahun tidak valid (harus antara 2000-2100)';
    }

    // Ambil data item dari form
    $item_labels = $_POST['item_label'] ?? [];
    $item_values = $_POST['item_value'] ?? [];

    // Validasi item
    if (empty($item_labels) || empty($item_values)) {
        $errors[] = 'Minimal harus ada 1 item data';
    } else {
        $labels = [];
        $values = [];

        // Filter item kosong dan validasi
        for ($i = 0; $i < count($item_labels); $i++) {
            $label = trim($item_labels[$i]);
            $value = trim($item_values[$i]);

            if (!empty($label) && $value !== '') {
                // Validasi nilai numerik
                if (!is_numeric($value)) {
                    $errors[] = 'Nilai "' . $label . '" harus berupa angka';
                    continue;
                }

                $labels[] = $label;
                $values[] = (float) $value;
            }
        }

        if (empty($labels)) {
            $errors[] = 'Minimal harus ada 1 item data yang valid';
        }
    }

    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        try {
            // Buat JSON data
            $data_json = json_encode([
                'labels' => $labels,
                'data' => $values
            ]);

            // Simpan data statistik
            $stmt = $pdo->prepare("
                INSERT INTO statistics (title, category, year, data_json, unit, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ");
            
            $stmt->execute([$title, $category, $year, $data_json, $unit]);

            // Redirect ke halaman statistik dengan pesan sukses
            $_SESSION['message'] = 'Data statistik berhasil ditambahkan';
            $_SESSION['message_type'] = 'success';

            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error database: ' . $e->getMessage();
        }
    }
}

// Set page title
$page_title = 'Tambah Statistik';

// Load header
include_once ADMIN_PATH . '/includes/header.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">Tambah Statistik</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Statistik</a></li>
                        <li class="breadcrumb-item active">Tambah</li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Form Tambah Statistik</h4>
                        <form action="" method="post" id="statisticForm">
                            <div class="mb-3 row">
                                <label for="title" class="col-md-2 col-form-label">Judul Statistik <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="title" name="title"
                                        value="<?php echo htmlspecialchars($title); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="category" class="col-md-2 col-form-label">Kategori <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Pilih Kategori</option>
                                        <option value="forest-area" <?php echo ($category === 'forest-area') ? 'selected' : ''; ?>>Luas Kawasan Hutan</option>
                                        <option value="forest-production" <?php echo ($category === 'forest-production') ? 'selected' : ''; ?>>Produksi Hasil Hutan</option>
                                        <option value="rehabilitation" <?php echo ($category === 'rehabilitation') ? 'selected' : ''; ?>>Rehabilitasi Hutan</option>
                                        <option value="social-forestry" <?php echo ($category === 'social-forestry') ? 'selected' : ''; ?>>Perhutanan Sosial</option>
                                        <option value="forest-fire" <?php echo ($category === 'forest-fire') ? 'selected' : ''; ?>>Kebakaran Hutan</option>
                                        <option value="other" <?php echo ($category === 'other') ? 'selected' : ''; ?>>
                                            Lainnya</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="year" class="col-md-2 col-form-label">Tahun <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <select class="form-select" id="year" name="year" required>
                                        <?php for ($y = date('Y'); $y >= 2010; $y--): ?>
                                            <option value="<?php echo $y; ?>" <?php echo ($year == $y) ? 'selected' : ''; ?>>
                                                <?php echo $y; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="unit" class="col-md-2 col-form-label">Satuan</label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="unit" name="unit" 
                                        value="<?php echo htmlspecialchars($unit ?? ''); ?>" placeholder="Contoh: Ha, Ton, Orang, dll">
                                    <small class="form-text text-muted">Satuan pengukuran data (opsional)</small>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-md-2 col-form-label">Data Statistik <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTable">
                                            <thead>
                                                <tr>
                                                    <th width="40%">Label</th>
                                                    <th width="40%">Nilai</th>
                                                    <th width="20%">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control" name="item_label[]"
                                                            required>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="0.01" class="form-control"
                                                            name="item_value[]" required>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-danger btn-sm remove-row">
                                                            <i class="fas fa-trash"></i> Hapus
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3">
                                                        <button type="button" class="btn btn-success btn-sm"
                                                            id="addRow">
                                                            <i class="fas fa-plus"></i> Tambah Item
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <div class="col-md-10 offset-md-2">
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                    <a href="index.php" class="btn btn-secondary">Batal</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Chart Preview -->
            <div class="col-12 mt-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Preview Grafik</h4>
                        <div style="height: 400px;">
                            <canvas id="previewChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Custom Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Dynamic add/remove rows
        const addRowBtn = document.getElementById('addRow');
        const dataTable = document.getElementById('dataTable').getElementsByTagName('tbody')[0];

        // Add new row
        addRowBtn.addEventListener('click', function () {
            const newRow = document.createElement('tr');

            newRow.innerHTML = `
                <td>
                    <input type="text" class="form-control" name="item_label[]" required>
                </td>
                <td>
                    <input type="number" step="0.01" class="form-control" name="item_value[]" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </td>
            `;

            dataTable.appendChild(newRow);
            updateChartPreview();
        });

        // Remove row
        dataTable.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-row') || e.target.parentElement.classList.contains('remove-row')) {
                const button = e.target.closest('.remove-row');
                const row = button.closest('tr');

                // Ensure we always have at least one row
                if (dataTable.rows.length > 1) {
                    row.remove();
                    updateChartPreview();
                } else {
                    alert('Minimal harus ada 1 item data!');
                }
            }
        });

        // Initialize chart
        let previewChart;

        function initChart() {
            const ctx = document.getElementById('previewChart').getContext('2d');

            previewChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: [],
                    datasets: [{
                        label: 'Data Statistik',
                        data: [],
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
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
        }

        // Update chart preview
        function updateChartPreview() {
            if (!previewChart) {
                initChart();
            }

            const labels = [];
            const values = [];
            const unit = document.getElementById('unit').value;

            // Get all rows
            const rows = dataTable.rows;

            for (let i = 0; i < rows.length; i++) {
                const labelInput = rows[i].querySelector('input[name="item_label[]"]');
                const valueInput = rows[i].querySelector('input[name="item_value[]"]');

                if (labelInput.value && valueInput.value) {
                    labels.push(labelInput.value);
                    values.push(parseFloat(valueInput.value));
                }
            }

            // Update chart data
            previewChart.data.labels = labels;
            previewChart.data.datasets[0].data = values;
            
            // Update chart title with unit if available
            const title = document.getElementById('title').value || 'Data Statistik';
            previewChart.data.datasets[0].label = unit ? `${title} (${unit})` : title;
            
            previewChart.update();
        }

        // Initialize preview chart
        initChart();

        // Listen for changes in form inputs to update chart
        document.getElementById('statisticForm').addEventListener('input', updateChartPreview);
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
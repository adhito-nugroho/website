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
$page_title = 'Tambah Statistik';

// Inisialisasi variabel
$title = '';
$category = '';
$year = date('Y');
$unit = '';
$labels = [''];
$data = [''];
$errors = [];

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validasi input
        $title = sanitizeInput($_POST['title'] ?? '');
        $category = sanitizeInput($_POST['category'] ?? '');
        $year = (int) sanitizeInput($_POST['year'] ?? date('Y'));
        $unit = sanitizeInput($_POST['unit'] ?? '');

        // Validasi required fields
        if (empty($title)) {
            $errors[] = "Judul statistik harus diisi";
        }

        if (empty($category)) {
            $errors[] = "Kategori statistik harus diisi";
        }

        if (empty($unit)) {
            $errors[] = "Satuan statistik harus diisi";
        }

        // Process JSON data
        $labels = $_POST['labels'] ?? [];
        $data_values = $_POST['data'] ?? [];

        // Remove empty entries
        $labels = array_filter($labels);
        $data_values = array_filter($data_values, function ($value) {
            return $value !== ''; });

        if (empty($labels) || empty($data_values) || count($labels) !== count($data_values)) {
            $errors[] = "Data statistik tidak valid. Pastikan jumlah label dan data sama";
        }

        // Jika tidak ada error, simpan ke database
        if (empty($errors)) {
            // Create JSON structure
            $data_json = json_encode([
                'labels' => array_values($labels),
                'data' => array_map('intval', array_values($data_values))
            ]);

            // Insert into database
            $stmt = $pdo->prepare("
                INSERT INTO statistics (title, category, year, unit, data_json, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$title, $category, $year, $unit, $data_json]);

            // Log admin activity
            logAdminActivity($_SESSION['user_id'], 'create', $pdo->lastInsertId(), 'Menambahkan data statistik baru');

            // Simpan pesan sukses ke dalam session
            $_SESSION['success_message'] = 'Data statistik berhasil ditambahkan';

            // Redirect ke halaman index
            header('Location: index.php');
            exit;
        }
    } catch (Exception $e) {
        $errors[] = 'Error: ' . $e->getMessage();
    }
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
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Form Tambah Statistik</h4>
                        <form method="post" action="" id="statisticForm">
                            <div class="mb-3 row">
                                <label for="title" class="col-md-2 col-form-label">Judul <span
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
                                    <input type="text" class="form-control" id="category" name="category"
                                        value="<?php echo htmlspecialchars($category); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="year" class="col-md-2 col-form-label">Tahun <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="number" class="form-control" id="year" name="year" min="2000"
                                        max="2100" value="<?php echo $year; ?>" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="unit" class="col-md-2 col-form-label">Satuan <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="unit" name="unit"
                                        value="<?php echo htmlspecialchars($unit); ?>"
                                        placeholder="contoh: orang, ton, hektar" required>
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
                                                    <th width="45%">Label</th>
                                                    <th width="45%">Nilai</th>
                                                    <th width="10%">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($labels) && !empty($data) && count($labels) === count($data)): ?>
                                                    <?php foreach ($labels as $index => $label): ?>
                                                        <tr>
                                                            <td>
                                                                <input type="text" class="form-control" name="labels[]"
                                                                    value="<?php echo htmlspecialchars($label); ?>" required>
                                                            </td>
                                                            <td>
                                                                <input type="number" class="form-control" name="data[]"
                                                                    value="<?php echo htmlspecialchars($data[$index]); ?>"
                                                                    required>
                                                            </td>
                                                            <td>
                                                                <button type="button"
                                                                    class="btn btn-danger btn-sm btn-remove-row">
                                                                    <i class="fas fa-trash-alt"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <tr>
                                                        <td>
                                                            <input type="text" class="form-control" name="labels[]"
                                                                required>
                                                        </td>
                                                        <td>
                                                            <input type="number" class="form-control" name="data[]"
                                                                required>
                                                        </td>
                                                        <td>
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm btn-remove-row">
                                                                <i class="fas fa-trash-alt"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3">
                                                        <button type="button" class="btn btn-success btn-sm"
                                                            id="btnAddRow">
                                                            <i class="fas fa-plus-circle"></i> Tambah Baris
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
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan
                                    </button>
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Tambah baris baru
        document.getElementById('btnAddRow').addEventListener('click', function () {
            const tbody = document.querySelector('#dataTable tbody');
            const newRow = document.createElement('tr');

            newRow.innerHTML = `
                <td>
                    <input type="text" class="form-control" name="labels[]" required>
                </td>
                <td>
                    <input type="number" class="form-control" name="data[]" required>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm btn-remove-row">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </td>
            `;

            tbody.appendChild(newRow);

            // Tambahkan event listener untuk tombol hapus pada baris baru
            newRow.querySelector('.btn-remove-row').addEventListener('click', removeRow);
        });

        // Hapus baris
        const removeRowButtons = document.querySelectorAll('.btn-remove-row');
        removeRowButtons.forEach(button => {
            button.addEventListener('click', removeRow);
        });

        function removeRow(e) {
            const tbody = document.querySelector('#dataTable tbody');
            const rows = tbody.querySelectorAll('tr');

            // Pastikan minimal ada satu baris
            if (rows.length > 1) {
                const row = e.target.closest('tr');
                row.remove();
            } else {
                alert('Minimal harus ada satu baris data');
            }
        }
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
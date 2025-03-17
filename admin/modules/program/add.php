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

// Variabel untuk form
$title = '';
$icon = '';
$description = '';
$content = '';
$order_number = 0;
$is_active = 1;
$errors = [];

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi dan validasi input
    $title = sanitizeInput($_POST['title'] ?? '');
    $icon = sanitizeInput($_POST['icon'] ?? '');
    $description = sanitizeInput($_POST['description'] ?? '');
    $content = $_POST['content'] ?? ''; // Tidak di-sanitize karena berisi HTML
    $order_number = (int) ($_POST['order_number'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validasi
    if (empty($title)) {
        $errors[] = 'Judul program wajib diisi';
    }

    if (empty($icon)) {
        $errors[] = 'Icon program wajib diisi';
    }

    if (empty($description)) {
        $errors[] = 'Deskripsi program wajib diisi';
    }

    if (empty($content)) {
        $errors[] = 'Konten program wajib diisi';
    }

    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO programs (title, icon, description, content, order_number, is_active, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $stmt->execute([$title, $icon, $description, $content, $order_number, $is_active]);

            // Redirect ke halaman program dengan pesan sukses
            $_SESSION['message'] = 'Program berhasil ditambahkan';
            $_SESSION['message_type'] = 'success';

            header('Location: index.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Error database: ' . $e->getMessage();
        }
    }
}

// Set page title for header
$page_title = 'Tambah Program';

// Load header
include_once ADMIN_PATH . '/includes/header.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">Tambah Program</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="index.php">Program</a></li>
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
                        <h4 class="card-title">Form Tambah Program</h4>
                        <form action="" method="post">
                            <div class="mb-3 row">
                                <label for="title" class="col-md-2 col-form-label">Judul Program <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <input type="text" class="form-control" id="title" name="title"
                                        value="<?php echo htmlspecialchars($title); ?>" required>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="icon" class="col-md-2 col-form-label">Icon <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="icon" name="icon"
                                            value="<?php echo htmlspecialchars($icon); ?>"
                                            placeholder="ri-draft-line" required>
                                        <button class="btn btn-outline-secondary" type="button" id="iconPicker">Pilih
                                            Icon</button>
                                    </div>
                                    <small class="form-text text-muted">Gunakan kelas icon dari Font Awesome atau Remix Icon, contoh:
                                        ri-draft-line, fas fa-tree</small>
                                    <div class="mt-2">
                                        <span>Preview: <i id="iconPreview"
                                                class="<?php echo htmlspecialchars($icon); ?> fa-2x"></i></span>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="description" class="col-md-2 col-form-label">Deskripsi Singkat <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <textarea class="form-control" id="description" name="description" rows="3"
                                        required><?php echo htmlspecialchars($description); ?></textarea>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="content" class="col-md-2 col-form-label">Konten <span
                                        class="text-danger">*</span></label>
                                <div class="col-md-10">
                                    <textarea class="form-control summernote" id="content"
                                        name="content"><?php echo htmlspecialchars($content); ?></textarea>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label for="order_number" class="col-md-2 col-form-label">Urutan</label>
                                <div class="col-md-10">
                                    <input type="number" class="form-control" id="order_number" name="order_number"
                                        value="<?php echo (int) $order_number; ?>" min="0">
                                    <small class="form-text text-muted">Menentukan urutan tampilan program (terkecil
                                        ditampilkan pertama)</small>
                                </div>
                            </div>

                            <div class="mb-3 row">
                                <label class="col-md-2 col-form-label">Status</label>
                                <div class="col-md-10">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                            <?php echo $is_active ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">Aktif</label>
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
        </div>
    </div>
</div>

<!-- Modal Icon Picker -->
<div class="modal fade" id="iconPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pilih Icon</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <input type="text" class="form-control" id="iconSearch" placeholder="Cari icon...">
                </div>
                <div class="row icon-list">
                    <!-- Icon list akan diisi oleh JavaScript -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Icon Preview
        const iconInput = document.getElementById('icon');
        const iconPreview = document.getElementById('iconPreview');

        iconInput.addEventListener('input', function () {
            iconPreview.className = this.value + ' fa-2x';
        });

        // Icon Picker
        const iconPicker = document.getElementById('iconPicker');
        const iconPickerModal = new bootstrap.Modal(document.getElementById('iconPickerModal'));
        const iconSearch = document.getElementById('iconSearch');
        const iconList = document.querySelector('.icon-list');

        // Daftar icon Font Awesome dan Remix Icon yang umum digunakan
        const commonIcons = [
            // Remix Icons
            'ri-draft-line', 'ri-plant-line', 'ri-seedling-line', 'ri-shield-check-line',
            'ri-file-list-line', 'ri-folder-line', 'ri-user-line', 'ri-team-line',
            'ri-government-line', 'ri-building-line', 'ri-home-line', 'ri-map-pin-line',
            'ri-tree-line', 'ri-leaf-line', 'ri-recycle-line', 'ri-water-flash-line',
            'ri-cloud-line', 'ri-sun-line', 'ri-moon-line', 'ri-earth-line',
            
            // Font Awesome
            'fas fa-tree', 'fas fa-leaf', 'fas fa-seedling', 'fas fa-shield-alt',
            'fas fa-file-signature', 'fas fa-users', 'fas fa-user-shield', 'fas fa-user-tie',
            'fas fa-building', 'fas fa-home', 'fas fa-map-marker-alt', 'fas fa-chart-bar',
            'fas fa-chart-line', 'fas fa-chart-pie', 'fas fa-chart-area', 'fas fa-recycle',
            'fas fa-water', 'fas fa-cloud', 'fas fa-sun', 'fas fa-moon',
            'fas fa-tractor', 'fas fa-hands-helping', 'fas fa-university', 'fas fa-landmark'
        ];

        // Render icon list
        function renderIcons(icons) {
            iconList.innerHTML = '';

            icons.forEach(icon => {
                const iconDiv = document.createElement('div');
                iconDiv.className = 'col-md-3 col-sm-4 col-6 mb-3 text-center icon-item';
                iconDiv.innerHTML = `
                <div class="border rounded p-3 icon-container" data-icon="${icon}">
                    <i class="${icon} fa-2x mb-2"></i>
                    <div><small>${icon}</small></div>
                </div>
            `;
                iconList.appendChild(iconDiv);
            });

            // Add click event to icons
            document.querySelectorAll('.icon-container').forEach(container => {
                container.addEventListener('click', function () {
                    const selectedIcon = this.getAttribute('data-icon');
                    iconInput.value = selectedIcon;
                    iconPreview.className = selectedIcon + ' fa-2x';
                    iconPickerModal.hide();
                });
            });
        }

        // Show icon picker modal
        iconPicker.addEventListener('click', function () {
            renderIcons(commonIcons);
            iconPickerModal.show();
        });

        // Filter icons
        iconSearch.addEventListener('input', function () {
            const searchTerm = this.value.toLowerCase();

            if (searchTerm === '') {
                renderIcons(commonIcons);
            } else {
                const filteredIcons = commonIcons.filter(icon =>
                    icon.toLowerCase().includes(searchTerm)
                );
                renderIcons(filteredIcons);
            }
        });

        // Initialize Summernote
        $(document).ready(function () {
            $('.summernote').summernote({
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
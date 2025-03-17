<?php
// Definisikan konstanta untuk direktori
define('BASE_PATH', dirname(__DIR__));
define('ADMIN_PATH', __DIR__);

// Mulai session
session_start();

// Load konfigurasi dan fungsi
require_once BASE_PATH . '/includes/config.php';
require_once ADMIN_PATH . '/includes/auth.php';
require_once ADMIN_PATH . '/includes/functions.php';

// Cek login admin
requireLogin();

// Cek role admin
requireRole('admin');

// Inisialisasi variabel
$settings = [];
$errors = [];
$success = false;

// Ambil data pengaturan dari database
try {
    $stmt = $pdo->query("SELECT * FROM settings ORDER BY setting_key");
    $result = $stmt->fetchAll();

    // Konversi ke array dengan key => value
    foreach ($result as $row) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    $errors[] = 'Error mengambil data pengaturan: ' . $e->getMessage();
}

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi input
    $updated_settings = $_POST['settings'] ?? [];

    if (empty($updated_settings)) {
        $errors[] = 'Tidak ada pengaturan yang disubmit';
    } else {
        try {
            // Begin transaction
            $pdo->beginTransaction();

            foreach ($updated_settings as $key => $value) {
                // Sanitasi input
                $key = sanitizeInput($key);
                $value = sanitizeInput($value);

                // Update pengaturan
                $stmt = $pdo->prepare("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?");
                $stmt->execute([$value, $key]);

                // Update array settings untuk tampilan
                $settings[$key] = $value;
            }

            // Upload logo jika ada
            if (!empty($_FILES['site_logo']['name'])) {
                $logo = $_FILES['site_logo'];
                $validation = validateImage($logo, ['jpg', 'jpeg', 'png', 'gif', 'svg'], 1024);

                if ($validation['status']) {
                    // Upload gambar
                    $upload = uploadImage($logo, 'uploads/settings', 'logo.' . pathinfo($logo['name'], PATHINFO_EXTENSION));

                    if ($upload['status']) {
                        // Update pengaturan logo
                        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'site_logo'");
                        $stmt->execute([$upload['path']]);

                        // Update array settings untuk tampilan
                        $settings['site_logo'] = $upload['path'];
                    } else {
                        throw new Exception($upload['message']);
                    }
                } else {
                    throw new Exception($validation['message']);
                }
            }

            // Upload favicon jika ada
            if (!empty($_FILES['site_favicon']['name'])) {
                $favicon = $_FILES['site_favicon'];
                $validation = validateImage($favicon, ['ico', 'png'], 256);

                if ($validation['status']) {
                    // Upload gambar
                    $upload = uploadImage($favicon, 'uploads/settings', 'favicon.' . pathinfo($favicon['name'], PATHINFO_EXTENSION));

                    if ($upload['status']) {
                        // Update pengaturan favicon
                        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'site_favicon'");
                        $stmt->execute([$upload['path']]);

                        // Update array settings untuk tampilan
                        $settings['site_favicon'] = $upload['path'];
                    } else {
                        throw new Exception($upload['message']);
                    }
                } else {
                    throw new Exception($validation['message']);
                }
            }

            // Commit transaction
            $pdo->commit();

            // Set success message
            $success = true;

            // Log aktivitas
            logAdminActivity('update', 'settings', null, 'Updated website settings');
        } catch (Exception $e) {
            // Rollback transaction
            $pdo->rollBack();
            $errors[] = 'Error update pengaturan: ' . $e->getMessage();
        }
    }
}

// Set page title
$page_title = 'Pengaturan Website';

// Load header
include_once ADMIN_PATH . '/includes/header.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor">Pengaturan Website</h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Pengaturan</li>
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

        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                Pengaturan website berhasil diperbarui.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Pengaturan Umum</h4>
                        <form method="post" action="" enctype="multipart/form-data">
                            <ul class="nav nav-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#general" role="tab">Umum</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#contact" role="tab">Kontak</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#social" role="tab">Media Sosial</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#seo" role="tab">SEO</a>
                                </li>
                            </ul>

                            <div class="tab-content p-3">
                                <!-- Tab Umum -->
                                <div class="tab-pane active" id="general" role="tabpanel">
                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Logo Website</label>
                                        <div class="col-md-9">
                                            <?php if (!empty($settings['site_logo'])): ?>
                                                <div class="mb-2">
                                                    <img src="<?php echo $site_config['base_url'] . '/' . $settings['site_logo']; ?>"
                                                        alt="Logo" style="max-height: 60px;">
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" name="site_logo"
                                                accept="image/png, image/jpeg, image/gif, image/svg+xml">
                                            <small class="form-text text-muted">Format: JPG, PNG, GIF, SVG. Ukuran
                                                maksimal: 1MB.</small>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Favicon</label>
                                        <div class="col-md-9">
                                            <?php if (!empty($settings['site_favicon'])): ?>
                                                <div class="mb-2">
                                                    <img src="<?php echo $site_config['base_url'] . '/' . $settings['site_favicon']; ?>"
                                                        alt="Favicon" style="max-height: 32px;">
                                                </div>
                                            <?php endif; ?>
                                            <input type="file" class="form-control" name="site_favicon"
                                                accept="image/x-icon, image/png">
                                            <small class="form-text text-muted">Format: ICO, PNG. Ukuran maksimal:
                                                256KB.</small>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Judul Website</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="settings[site_title]"
                                                value="<?php echo htmlspecialchars($settings['site_title'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Deskripsi Website</label>
                                        <div class="col-md-9">
                                            <textarea class="form-control" name="settings[site_description]"
                                                rows="3"><?php echo htmlspecialchars($settings['site_description'] ?? ''); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Jam Operasional</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="settings[office_hours]"
                                                value="<?php echo htmlspecialchars($settings['office_hours'] ?? ''); ?>">
                                            <small class="form-text text-muted">Contoh: Senin - Jumat: 08:00 - 16:00
                                                WIB</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab Kontak -->
                                <div class="tab-pane" id="contact" role="tabpanel">
                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Alamat Kantor</label>
                                        <div class="col-md-9">
                                            <textarea class="form-control" name="settings[site_address]"
                                                rows="3"><?php echo htmlspecialchars($settings['site_address'] ?? ''); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Nomor Telepon</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="settings[site_phone]"
                                                value="<?php echo htmlspecialchars($settings['site_phone'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Email Kontak</label>
                                        <div class="col-md-9">
                                            <input type="email" class="form-control" name="settings[site_email]"
                                                value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Google Maps Embed</label>
                                        <div class="col-md-9">
                                            <textarea class="form-control" name="settings[google_maps]"
                                                rows="4"><?php echo htmlspecialchars($settings['google_maps'] ?? ''); ?></textarea>
                                            <small class="form-text text-muted">Masukkan kode embed Google Maps</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab Media Sosial -->
                                <div class="tab-pane" id="social" role="tabpanel">
                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Facebook</label>
                                        <div class="col-md-9">
                                            <input type="url" class="form-control" name="settings[social_facebook]"
                                                value="<?php echo htmlspecialchars($settings['social_facebook'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Twitter</label>
                                        <div class="col-md-9">
                                            <input type="url" class="form-control" name="settings[social_twitter]"
                                                value="<?php echo htmlspecialchars($settings['social_twitter'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Instagram</label>
                                        <div class="col-md-9">
                                            <input type="url" class="form-control" name="settings[social_instagram]"
                                                value="<?php echo htmlspecialchars($settings['social_instagram'] ?? ''); ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">YouTube</label>
                                        <div class="col-md-9">
                                            <input type="url" class="form-control" name="settings[social_youtube]"
                                                value="<?php echo htmlspecialchars($settings['social_youtube'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>

                                <!-- Tab SEO -->
                                <div class="tab-pane" id="seo" role="tabpanel">
                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Meta Keywords</label>
                                        <div class="col-md-9">
                                            <textarea class="form-control" name="settings[meta_keywords]"
                                                rows="3"><?php echo htmlspecialchars($settings['meta_keywords'] ?? ''); ?></textarea>
                                            <small class="form-text text-muted">Pisahkan dengan koma. Contoh: kehutanan,
                                                lingkungan, bojonegoro</small>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Meta Description</label>
                                        <div class="col-md-9">
                                            <textarea class="form-control" name="settings[meta_description]"
                                                rows="3"><?php echo htmlspecialchars($settings['meta_description'] ?? ''); ?></textarea>
                                            <small class="form-text text-muted">Deskripsi singkat tentang website
                                                (maksimal 160 karakter)</small>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label class="col-md-3 col-form-label">Google Analytics ID</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" name="settings[google_analytics]"
                                                value="<?php echo htmlspecialchars($settings['google_analytics'] ?? ''); ?>">
                                            <small class="form-text text-muted">Contoh: UA-XXXXX-Y atau
                                                G-XXXXXXXX</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary">Simpan Pengaturan</button>
                            </div>
                        </form>
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
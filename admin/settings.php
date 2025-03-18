<?php
// Definisikan konstanta untuk direktori
define('BASE_PATH', dirname(__DIR__));

/**
 * Sanitizes a filename by removing invalid characters.
 *
 * @param string $filename The original filename.
 * @return string The sanitized filename.
 */
function sanitizeFilename($filename)
{
    return preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $filename);
}
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

    // Ambil data pengaturan beranda dari site_settings
    $stmt = $pdo->query("SELECT * FROM site_settings WHERE setting_key = 'hero_content'");
    $hero_content = $stmt->fetch();
    if ($hero_content) {
        $hero_data = json_decode($hero_content['setting_value'], true);
    } else {
        $hero_data = [
            'title' => 'Cabang Dinas Kehutanan Wilayah Bojonegoro',
            'subtitle' => 'Unit Pelaksana Teknis Dinas Kehutanan Provinsi Jawa Timur yang melaksanakan kebijakan teknis operasional di bidang kehutanan',
            'button1_text' => 'Layanan Kami',
            'button1_link' => '#layanan',
            'button2_text' => 'Hubungi Kami',
            'button2_link' => '#kontak',
            'background_video' => 'forest-bg.mp4'
        ];
    }

    // Ambil data wilayah kerja
    $stmt = $pdo->query("SELECT * FROM work_areas WHERE is_active = 1 ORDER BY name");
    $work_areas = $stmt->fetchAll();

    // Ambil data statistik hutan
    $stmt = $pdo->query("SELECT * FROM statistics WHERE category = 'forest-area' ORDER BY year DESC LIMIT 1");
    $forest_stats = $stmt->fetch();

    if ($forest_stats && isset($forest_stats['data_json'])) {
        $forest_data = json_decode($forest_stats['data_json'], true);
    } else {
        $forest_data = [];
    }

} catch (PDOException $e) {
    $errors[] = 'Error mengambil data pengaturan: ' . $e->getMessage();
}

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Cek aksi yang diminta
    $action = $_POST['action'] ?? 'general';

    if ($action === 'general') {
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
                    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) 
                                         ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
                    $stmt->execute([$key, $value, $value]);

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
                            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) VALUES ('site_logo', ?, NOW())
                                                 ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
                            $stmt->execute([$upload['path'], $upload['path']]);

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
                            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value, updated_at) VALUES ('site_favicon', ?, NOW())
                                                 ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()");
                            $stmt->execute([$upload['path'], $upload['path']]);

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
    } elseif ($action === 'hero') {
        // Update pengaturan hero beranda
        try {
            $hero_data = [
                'title' => $_POST['hero_title'] ?? '',
                'subtitle' => $_POST['hero_subtitle'] ?? '',
                'button1_text' => $_POST['button1_text'] ?? '',
                'button1_link' => $_POST['button1_link'] ?? '',
                'button2_text' => $_POST['button2_text'] ?? '',
                'button2_link' => $_POST['button2_link'] ?? '',
                'background_video' => $_POST['background_video'] ?? 'forest-bg.mp4'
            ];

            $hero_json = json_encode($hero_data);

            // Update atau insert ke database
            $stmt = $pdo->prepare("
                INSERT INTO site_settings (setting_key, setting_value, created_at, updated_at) 
                VALUES ('hero_content', ?, NOW(), NOW())
                ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()
            ");
            $stmt->execute([$hero_json, $hero_json]);

            // Upload background video jika ada
            if (!empty($_FILES['hero_video']['name']) && $_FILES['hero_video']['error'] == 0) {
                $allowed_types = ['video/mp4'];
                $max_size = 10 * 1024 * 1024; // 10MB

                if (!in_array($_FILES['hero_video']['type'], $allowed_types)) {
                    throw new Exception('Format video tidak didukung. Hanya format MP4 yang diperbolehkan.');
                }

                if ($_FILES['hero_video']['size'] > $max_size) {
                    throw new Exception('Ukuran video terlalu besar. Maksimal 10MB.');
                }

                $video_name = sanitizeFilename($_FILES['hero_video']['name']);
                $upload_dir = BASE_PATH . '/assets/videos/';
                $upload_path = $upload_dir . $video_name;

                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (move_uploaded_file($_FILES['hero_video']['tmp_name'], $upload_path)) {
                    // Update data hero dengan nama video baru
                    $hero_data['background_video'] = $video_name;
                    $hero_json = json_encode($hero_data);

                    $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = 'hero_content'");
                    $stmt->execute([$hero_json]);
                } else {
                    throw new Exception('Gagal mengupload video background.');
                }
            }

            $success = true;

            // Log aktivitas
            logAdminActivity('update', 'hero_content', null, 'Updated hero content settings');
        } catch (Exception $e) {
            $errors[] = 'Error update pengaturan hero: ' . $e->getMessage();
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
                        <li class="breadcrumb-item">
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
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#beranda" role="tab">Beranda</a>
                            </li>
                        </ul>

                        <div class="tab-content p-3">
                            <!-- Tab Umum -->
                            <div class="tab-pane active" id="general" role="tabpanel">
                                <form method="post" action="" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="general">
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

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary">Simpan Pengaturan Umum</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Tab Kontak -->
                            <div class="tab-pane" id="contact" role="tabpanel">
                                <form method="post" action="">
                                    <input type="hidden" name="action" value="general">
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

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary">Simpan Pengaturan Kontak</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Tab Media Sosial -->
                            <div class="tab-pane" id="social" role="tabpanel">
                                <form method="post" action="">
                                    <input type="hidden" name="action" value="general">
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

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary">Simpan Pengaturan Media
                                            Sosial</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Tab SEO -->
                            <div class="tab-pane" id="seo" role="tabpanel">
                                <form method="post" action="">
                                    <input type="hidden" name="action" value="general">
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

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary">Simpan Pengaturan SEO</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Tab Beranda -->
                            <div class="tab-pane" id="beranda" role="tabpanel">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Pengaturan ini digunakan untuk mengatur tampilan
                                    hero section pada halaman beranda.
                                </div>

                                <!-- Hero Section Preview -->
                                <div class="mb-4 border p-3 rounded">
                                    <h5 class="mb-3">Preview Hero Section</h5>
                                    <div class="hero-preview bg-dark text-white p-4 rounded position-relative"
                                        style="min-height: 250px;">
                                        <div class="position-absolute w-100 h-100 top-0 start-0 rounded"
                                            style="opacity: 0.3; background: url('<?php echo $site_config['base_url']; ?>/assets/images/forest-preview.jpg') center/cover no-repeat;">
                                        </div>
                                        <div class="position-relative z-1 text-center p-4">
                                            <h2><?php echo htmlspecialchars($hero_data['title']); ?></h2>
                                            <p class="my-3"><?php echo htmlspecialchars($hero_data['subtitle']); ?></p>
                                            <div class="mt-3">
                                                <button
                                                    class="btn btn-light me-2"><?php echo htmlspecialchars($hero_data['button1_text']); ?></button>
                                                <button
                                                    class="btn btn-outline-light"><?php echo htmlspecialchars($hero_data['button2_text']); ?></button>
                                            </div>
                                            <small class="d-block mt-3">Background video:
                                                <?php echo htmlspecialchars($hero_data['background_video']); ?></small>
                                        </div>
                                    </div>
                                </div>

                                <form method="post" action="" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="hero">

                                    <div class="mb-3 row">
                                        <label for="hero_title" class="col-md-3 col-form-label">Judul Hero</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="hero_title" name="hero_title"
                                                value="<?php echo htmlspecialchars($hero_data['title']); ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="hero_subtitle" class="col-md-3 col-form-label">Subtitle Hero</label>
                                        <div class="col-md-9">
                                            <textarea class="form-control" id="hero_subtitle" name="hero_subtitle"
                                                rows="3"><?php echo htmlspecialchars($hero_data['subtitle']); ?></textarea>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="button1_text" class="col-md-3 col-form-label">Tombol 1 -
                                            Teks</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="button1_text"
                                                name="button1_text"
                                                value="<?php echo htmlspecialchars($hero_data['button1_text']); ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="button1_link" class="col-md-3 col-form-label">Tombol 1 -
                                            Link</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="button1_link"
                                                name="button1_link"
                                                value="<?php echo htmlspecialchars($hero_data['button1_link']); ?>">
                                            <small class="form-text text-muted">Contoh: #layanan (untuk scroll ke
                                                section) atau http://example.com (untuk link eksternal)</small>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="button2_text" class="col-md-3 col-form-label">Tombol 2 -
                                            Teks</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="button2_text"
                                                name="button2_text"
                                                value="<?php echo htmlspecialchars($hero_data['button2_text']); ?>">
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="button2_link" class="col-md-3 col-form-label">Tombol 2 -
                                            Link</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="button2_link"
                                                name="button2_link"
                                                value="<?php echo htmlspecialchars($hero_data['button2_link']); ?>">
                                            <small class="form-text text-muted">Contoh: #kontak (untuk scroll ke
                                                section) atau http://example.com (untuk link eksternal)</small>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="background_video" class="col-md-3 col-form-label">Nama Video
                                            Latar</label>
                                        <div class="col-md-9">
                                            <input type="text" class="form-control" id="background_video"
                                                name="background_video"
                                                value="<?php echo htmlspecialchars($hero_data['background_video']); ?>">
                                            <small class="form-text text-muted">Nama file video yang sudah diupload ke
                                                folder assets/videos/</small>
                                        </div>
                                    </div>

                                    <div class="mb-3 row">
                                        <label for="hero_video" class="col-md-3 col-form-label">Upload Video
                                            Baru</label>
                                        <div class="col-md-9">
                                            <input type="file" class="form-control" id="hero_video" name="hero_video"
                                                accept="video/mp4">
                                            <small class="form-text text-muted">Format: MP4. Ukuran maksimal: 10MB.
                                                Video yang diupload akan menggantikan video latar saat ini.</small>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <h5>Pengaturan Wilayah Kerja</h5>
                                        <p>Wilayah kerja dapat dikelola melalui menu <a
                                                href="<?php echo $site_config['admin_url']; ?>/modules/work_areas/index.php">Wilayah
                                                Kerja</a>.</p>
                                    </div>

                                    <div class="mt-4">
                                        <h5>Pengaturan Statistik</h5>
                                        <p>Data statistik yang ditampilkan di beranda dapat dikelola melalui menu</p>
                                        <p>Data statistik yang ditampilkan di beranda dapat dikelola melalui menu <a
                                                href="<?php echo $site_config['admin_url']; ?>/modules/statistik/index.php">Statistik</a>.
                                        </p>
                                        <p>Pastikan terdapat data statistik dengan kategori 'forest-area' untuk
                                            menampilkan data di floating stats pada beranda.</p>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary">Simpan Pengaturan Beranda</button>
                                        <a href="<?php echo $site_config['base_url']; ?>" target="_blank"
                                            class="btn btn-secondary ms-2">Lihat Beranda</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Script untuk tab dan form handler -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Aktivasi tab sesuai dengan action yang dilakukan
        const urlParams = new URLSearchParams(window.location.search);
        const tab = urlParams.get('tab');

        if (tab) {
            const tabEl = document.querySelector(`a[href="#${tab}"]`);
            if (tabEl) {
                new bootstrap.Tab(tabEl).show();
            }
        }

        // Tambahkan parameter tab pada form submission
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            form.addEventListener('submit', function () {
                const activeTab = document.querySelector('.tab-pane.active').id;
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'tab';
                input.value = activeTab;
                form.appendChild(input);
            });
        });
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
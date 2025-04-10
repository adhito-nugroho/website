<?php
// Form kontak standalone - letakkan di root website
define('BASE_PATH', dirname(__FILE__));

// Mulai session
session_start();


require_once BASE_PATH . '/includes/config.php';

// Buat koneksi database
try {
    $pdo = new PDO(
        "mysql:host={$db_config['host']};dbname={$db_config['database']};charset={$db_config['charset']}",
        $db_config['username'],
        $db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    $db_connected = true;
} catch (PDOException $e) {
    $error_message = "Koneksi database gagal: " . $e->getMessage();
    $db_connected = false;
}

// Fungsi sanitasi input
// function sanitizeInput($input)
// {
//     if (is_array($input)) {
//         foreach ($input as $key => $value) {
//             $input[$key] = sanitizeInput($value);
//         }
//     } else {
//         $input = trim($input);
//         $input = stripslashes($input);
//         $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
//     }
//     return $input;
// }

// Inisialisasi variabel
$form_submitted = false;
$form_success = false;
$form_message = '';

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_submitted = true;

    // Cek koneksi database
    if (!$db_connected) {
        $form_success = false;
        $form_message = "Tidak dapat mengirim pesan: Koneksi database tidak tersedia.";
    } else {
        // Ambil dan sanitasi input
        $name = sanitizeInput($_POST['name'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $category = sanitizeInput($_POST['category'] ?? '');
        $message = sanitizeInput($_POST['message'] ?? '');

        // Validasi input
        if (empty($name) || empty($email) || empty($category) || empty($message)) {
            $form_success = false;
            $form_message = "Semua field wajib diisi!";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $form_success = false;
            $form_message = "Format email tidak valid!";
        } else {
            try {
                // Insert data ke database
                $stmt = $pdo->prepare("
                    INSERT INTO messages (name, email, phone, category, message, ip_address, status) 
                    VALUES (:name, :email, :phone, :category, :message, :ip_address, :status)
                ");

                $result = $stmt->execute([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'category' => $category,
                    'message' => $message,
                    'ip_address' => $_SERVER['REMOTE_ADDR'],
                    'status' => 'unread'
                ]);

                if ($result) {
                    $form_success = true;
                    $form_message = "Pesan Anda berhasil dikirim. Terima kasih telah menghubungi kami.";
                } else {
                    $form_success = false;
                    $form_message = "Gagal menyimpan pesan. Silakan coba lagi nanti.";
                }
            } catch (PDOException $e) {
                $form_success = false;
                $form_message = "Error database: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="theme-color" content="#2e7d32" />
    <title>Form Kontak - CDK Wilayah Bojonegoro</title>
    <meta name="description" content="Kirim pesan ke CDK Wilayah Bojonegoro">

    <!-- CSS Libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">

    <!-- Inline styles untuk form standalone -->
    <style>
        :root {
            --primary-color: #2e7d32;
            --primary-hover: #24662a;
            --secondary-color: #1b4332;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --success-color: #28a745;
            --error-color: #dc3545;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            background-color: #f5f7f9;
            color: #333;
            padding-top: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .form-header {
            background: linear-gradient(135deg, #2e7d32, #1b4332);
            padding: 2rem 0;
            color: white;
            text-align: center;
            margin-bottom: 3rem;
        }

        .form-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            font-size: 1.1rem;
            opacity: 0.9;
            max-width: 700px;
            margin: 0 auto;
        }

        .form-container {
            max-width: 800px;
            margin: 0 auto 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 30px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #2e7d32, #52b788);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1.25rem;
            border: 1px solid #ddd;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(46, 125, 50, 0.25);
        }

        .form-label {
            font-weight: 600;
            color: #555;
            margin-bottom: 0.5rem;
        }

        .form-select {
            border-radius: 8px;
            padding: 0.75rem 1.25rem;
            border: 1px solid #ddd;
        }

        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(46, 125, 50, 0.25);
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
        }

        .btn-primary:focus {
            box-shadow: 0 0 0 0.25rem rgba(46, 125, 50, 0.25);
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            padding: 0.75rem 2rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        .alert {
            border-radius: 10px;
            padding: 1.25rem;
            margin-bottom: 2rem;
            border: none;
        }

        .alert-success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
        }

        .alert-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--error-color);
        }

        .footer {
            background-color: #f0f2f5;
            text-align: center;
            padding: 1.5rem;
            margin-top: auto;
        }

        .back-link {
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.3s;
        }

        .back-link i {
            margin-right: 0.5rem;
        }

        .back-link:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        .form-icon {
            color: var(--primary-color);
            margin-right: 10px;
        }

        .form-buttons {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 2rem 1.5rem;
                margin: 0 1rem 2rem;
            }

            .form-header {
                padding: 1.5rem 1rem;
                margin-bottom: 2rem;
            }

            .form-buttons {
                flex-direction: column;
                gap: 1rem;
            }

            .form-buttons .btn {
                width: 100%;
            }

            .back-link {
                margin-bottom: 1rem;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-container {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>

<body>
    <div class="form-header">
        <div class="container">
            <a href="index.php" class="back-link">
                <i class="ri-arrow-left-line"></i> Kembali ke Beranda
            </a>
            <h1>Hubungi Kami</h1>
            <p>Silakan isi form di bawah ini dan kami akan segera menghubungi Anda kembali</p>
        </div>
    </div>

    <div class="container">
        <div class="form-container">
            <?php if ($form_submitted): ?>
                <div class="alert alert-<?php echo $form_success ? 'success' : 'danger'; ?>">
                    <i class="ri-<?php echo $form_success ? 'checkbox-circle-line' : 'error-warning-line'; ?> me-2"></i>
                    <?php echo $form_message; ?>
                </div>

                <?php if ($form_success): ?>
                    <div class="text-center py-4">
                        <i class="ri-mail-check-line" style="font-size: 5rem; color: var(--success-color);"></i>
                        <h3 class="mt-3">Pesan Terkirim!</h3>
                        <p class="mb-4">Kami akan segera memproses pesan Anda.</p>
                        <div>
                            <a href="index.php" class="btn btn-primary">
                                <i class="ri-home-line me-2"></i> Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Form akan ditampilkan kembali dengan data yang sudah diisi -->
                <?php endif; ?>
            <?php endif; ?>

            <?php if (!$form_submitted || !$form_success): ?>
                <form method="post" action="">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="nama" class="form-label">
                                    <i class="ri-user-line form-icon"></i>Nama Lengkap
                                </label>
                                <input type="text" name="name" class="form-control" id="nama"
                                    placeholder="Masukkan nama lengkap Anda"
                                    value="<?php echo $form_submitted ? htmlspecialchars($name) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label">
                                    <i class="ri-mail-line form-icon"></i>Email
                                </label>
                                <input type="email" name="email" class="form-control" id="email"
                                    placeholder="Masukkan alamat email Anda"
                                    value="<?php echo $form_submitted ? htmlspecialchars($email) : ''; ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="telepon" class="form-label">
                                    <i class="ri-phone-line form-icon"></i>Telepon
                                </label>
                                <input type="text" name="phone" class="form-control" id="telepon"
                                    placeholder="Masukkan nomor telepon Anda"
                                    value="<?php echo $form_submitted ? htmlspecialchars($phone) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="kategori" class="form-label">
                                    <i class="ri-file-list-3-line form-icon"></i>Kategori
                                </label>
                                <select name="category" class="form-select" id="kategori" required>
                                    <option value="" selected disabled>-- Pilih Kategori --</option>
                                    <option value="Informasi" <?php echo ($form_submitted && $category === 'Informasi') ? 'selected' : ''; ?>>Informasi</option>
                                    <option value="Layanan" <?php echo ($form_submitted && $category === 'Layanan') ? 'selected' : ''; ?>>Layanan</option>
                                    <option value="Pengaduan" <?php echo ($form_submitted && $category === 'Pengaduan') ? 'selected' : ''; ?>>Pengaduan</option>
                                    <option value="Kerjasama" <?php echo ($form_submitted && $category === 'Kerjasama') ? 'selected' : ''; ?>>Kerjasama</option>
                                    <option value="Lainnya" <?php echo ($form_submitted && $category === 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="pesan" class="form-label">
                            <i class="ri-message-3-line form-icon"></i>Pesan
                        </label>
                        <textarea name="message" class="form-control" id="pesan" rows="6"
                            placeholder="Tulis pesan Anda di sini..."
                            required><?php echo $form_submitted ? htmlspecialchars($message) : ''; ?></textarea>
                    </div>

                    <div class="form-buttons">
                        <a href="index.php" class="back-link">
                            <i class="ri-arrow-left-line"></i> Kembali ke Beranda
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="ri-send-plane-line me-2"></i> Kirim Pesan
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> CDK Wilayah Bojonegoro. Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Form validation script
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.querySelector('form');

            if (form) {
                form.addEventListener('submit', function (e) {
                    let hasError = false;
                    const requiredFields = form.querySelectorAll('[required]');

                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            hasError = true;
                            field.classList.add('is-invalid');
                        } else {
                            field.classList.remove('is-invalid');
                        }

                        // Email validation
                        if (field.type === 'email' && field.value) {
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!emailRegex.test(field.value)) {
                                hasError = true;
                                field.classList.add('is-invalid');
                            }
                        }
                    });

                    if (hasError) {
                        e.preventDefault();
                        alert('Mohon lengkapi form dengan benar.');
                    }
                });

                // Live validation on input
                const inputs = form.querySelectorAll('input, textarea, select');
                inputs.forEach(input => {
                    input.addEventListener('input', function () {
                        if (this.hasAttribute('required') && !this.value.trim()) {
                            this.classList.add('is-invalid');
                        } else {
                            this.classList.remove('is-invalid');
                        }

                        // Email validation
                        if (this.type === 'email' && this.value) {
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!emailRegex.test(this.value)) {
                                this.classList.add('is-invalid');
                            }
                        }
                    });
                });
            }
        });
    </script>
</body>

</html>
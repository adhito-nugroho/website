 
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
$page_title = 'Balas Pesan';

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
        $message['status'] = 'read';
    }
} catch (PDOException $e) {
    $_SESSION['message'] = 'Error: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    header('Location: index.php');
    exit;
}

// Ambil data admin
$admin_name = $_SESSION['admin_name'] ?? 'Administrator';
$admin_email = $_SESSION['admin_email'] ?? $site_config['site_email'] ?? 'admin@cdk-bojonegoro.jatimprov.go.id';

// Variabel untuk form
$subject = 'Re: ' . $message['category'];
$reply_content = '';
$errors = [];

// Ambil data konfigurasi untuk footer email
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'site_title'");
    $stmt->execute();
    $site_title = $stmt->fetchColumn() ?: 'CDK Wilayah Bojonegoro';
} catch (PDOException $e) {
    $site_title = 'CDK Wilayah Bojonegoro';
}

// Proses form jika disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitasi dan validasi input
    $subject = sanitizeInput($_POST['subject'] ?? '');
    $reply_content = $_POST['reply_content'] ?? '';

    // Validasi
    if (empty($subject)) {
        $errors[] = 'Subjek email wajib diisi';
    }

    if (empty($reply_content)) {
        $errors[] = 'Isi balasan wajib diisi';
    }

    // Jika tidak ada error, kirim email dan update status
    if (empty($errors)) {
        // Siapkan konten email
        $recipient_email = $message['email'];
        $recipient_name = $message['name'];
        
        // Headers untuk email
        $headers = "From: $admin_name <$admin_email>" . "\r\n";
        $headers .= "Reply-To: $admin_email" . "\r\n";
        $headers .= "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8" . "\r\n";
        
        // Isi email dengan format HTML
        $email_body = "
        <html>
        <head>
            <title>$subject</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
                <div style='border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 20px;'>
                    <h2 style='color: #007bff; margin: 0;'>$subject</h2>
                </div>
                
                <p>Kepada Yth. $recipient_name,</p>
                
                <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 15px 0;'>
                    " . nl2br(htmlspecialchars($reply_content)) . "
                </div>
                
                <p>Berikut adalah pesan Anda sebelumnya:</p>
                
                <div style='background-color: #f0f0f0; padding: 15px; border-left: 3px solid #6c757d; margin: 15px 0;'>
                    <strong>Kategori:</strong> " . htmlspecialchars($message['category']) . "<br>
                    <strong>Tanggal:</strong> " . formatDate($message['created_at']) . "<br>
                    <strong>Pesan:</strong><br>
                    " . nl2br(htmlspecialchars($message['message'])) . "
                </div>
                
                <div style='margin-top: 30px; padding-top: 15px; border-top: 1px solid #ddd; font-size: 0.9em; color: #666;'>
                    <p>Terima kasih telah menghubungi kami.<br>
                    Salam hormat,</p>
                    <p><strong>$admin_name</strong><br>
                    $site_title</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        // Kirim email
        $mail_sent = mail($recipient_email, $subject, $email_body, $headers);
        
        if ($mail_sent) {
            // Update status pesan menjadi 'replied'
            try {
                $stmt = $pdo->prepare("UPDATE messages SET status = 'replied' WHERE id = ?");
                $stmt->execute([$id]);
                
                // Log aktivitas
                if (isset($_SESSION['admin_id'])) {
                    logActivity($_SESSION['admin_id'], 'membalas pesan', [
                        'message_id' => $id,
                        'to' => $recipient_name,
                        'subject' => $subject
                    ]);
                }
                
                $_SESSION['message'] = 'Balasan berhasil dikirim ke ' . $recipient_email;
                $_SESSION['message_type'] = 'success';
                
                header('Location: index.php');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Error update status: ' . $e->getMessage();
            }
        } else {
            $errors[] = 'Gagal mengirim email. Periksa konfigurasi email server.';
        }
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
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Balas Pesan dari <?php echo htmlspecialchars($message['name']); ?></h4>
                        
                        <form action="" method="post">
                            <div class="mb-3">
                                <label for="recipient" class="form-label">Penerima</label>
                                <input type="text" class="form-control" id="recipient" value="<?php echo htmlspecialchars($message['name']); ?> <<?php echo htmlspecialchars($message['email']); ?>>" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label for="subject" class="form-label">Subjek <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="subject" name="subject" value="<?php echo htmlspecialchars($subject); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reply_content" class="form-label">Isi Balasan <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="reply_content" name="reply_content" rows="10" required><?php echo htmlspecialchars($reply_content); ?></textarea>
                                <small class="form-text text-muted">Gunakan format teks biasa. HTML tidak diperbolehkan.</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Email akan dikirim sebagai</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($admin_name); ?> <<?php echo htmlspecialchars($admin_email); ?>>" readonly>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="view.php?id=<?php echo $id; ?>" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-1"></i> Kirim Balasan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <!-- Original Message -->
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Pesan Asli</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Pengirim</label>
                            <div class="p-2 bg-light rounded">
                                <strong><?php echo htmlspecialchars($message['name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($message['email']); ?></small>
                                <?php if (!empty($message['phone'])): ?>
                                    <br><small><?php echo htmlspecialchars($message['phone']); ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <div class="p-2 bg-light rounded">
                                <?php echo htmlspecialchars($message['category']); ?>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <div class="p-2 bg-light rounded">
                                <?php echo formatDate($message['created_at'], true); ?>
                            </div>
                        </div>
                        
                        <div>
                            <label class="form-label">Pesan</label>
                            <div class="p-2 bg-light rounded" style="max-height: 300px; overflow-y: auto;">
                                <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Email Preview -->
                <div class="card mt-3">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Informasi Email</h5>
                    </div>
                    <div class="card-body">
                        <p>Email akan dikirim dengan format HTML yang berisi:</p>
                        <ul class="list-group list-group-flush">
                            <li class="list-group-item">
                                <i class="fas fa-check-circle text-success me-2"></i> Subjek email
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-check-circle text-success me-2"></i> Salam pembuka
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-check-circle text-success me-2"></i> Isi balasan Anda
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-check-circle text-success me-2"></i> Kutipan pesan asli
                            </li>
                            <li class="list-group-item">
                                <i class="fas fa-check-circle text-success me-2"></i> Tanda tangan dengan nama dan instansi
                            </li>
                        </ul>
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
<?php
// Memastikan file ini tidak diakses langsung
if (!defined('ADMIN_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

/**
 * Dapatkan dan hapus pesan flash
 * 
 * @param string $name Nama pesan flash
 * @return string|null Pesan flash atau null jika tidak ada
 */
if (!function_exists('getFlashMessage')) {
    function getFlashMessage($name)
    {
        $message = $_SESSION[$name] ?? '';
        unset($_SESSION[$name]);
        return $message;
    }
}

/**
 * Set pesan flash
 * 
 * @param string $name Nama pesan flash
 * @param string $message Isi pesan flash
 * @return void
 */
if (!function_exists('setFlashMessage')) {
    function setFlashMessage($name, $message)
    {
        $_SESSION[$name] = $message;
    }
}

/**
 * Validasi input upload gambar
 * 
 * @param array $file File yang diupload ($_FILES[])
 * @param array $allowed_types Tipe file yang diizinkan (ext)
 * @param int $max_size Ukuran maksimum file dalam KB
 * @return array Status validasi dan pesan error
 */
if (!function_exists('validateImage')) {
    function validateImage($file, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 2048)
    {
        // Cek apakah ada file yang diupload
        if ($file['error'] == 4) {
            return ['status' => false, 'message' => 'Tidak ada file yang dipilih'];
        }

        // Cek error upload
        if ($file['error'] != 0) {
            return ['status' => false, 'message' => 'Error saat mengupload file: ' . $file['error']];
        }

        // Cek ukuran file (KB)
        if ($file['size'] > $max_size * 1024) {
            return ['status' => false, 'message' => 'Ukuran file terlalu besar (maksimum ' . $max_size . 'KB)'];
        }

        // Cek ekstensi file
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_types)) {
            return ['status' => false, 'message' => 'Tipe file tidak diizinkan (hanya ' . implode(', ', $allowed_types) . ')'];
        }

        return ['status' => true, 'message' => 'File valid'];
    }
}

/**
 * Upload gambar ke direktori
 * 
 * @param array $file File yang diupload ($_FILES[])
 * @param string $destination Direktori tujuan upload (relatif terhadap BASE_PATH)
 * @param string $new_filename Nama file baru (opsional)
 * @return array Status upload dan informasi file
 */
if (!function_exists('uploadImage')) {
    function uploadImage($file, $destination, $new_filename = '')
    {
        global $site_config;

        // Validasi gambar
        $validation = validateImage($file);
        if (!$validation['status']) {
            return $validation;
        }

        // Buat direktori jika belum ada
        $upload_dir = BASE_PATH . '/' . trim($destination, '/');
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        // Buat nama file unik jika tidak ada nama baru
        if (empty($new_filename)) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $new_filename = time() . '_' . uniqid() . '.' . $ext;
        }

        // Upload file
        $upload_path = $upload_dir . '/' . $new_filename;
        if (move_uploaded_file($file['tmp_name'], $upload_path)) {
            return [
                'status' => true,
                'message' => 'File berhasil diupload',
                'filename' => $new_filename,
                'path' => $destination . '/' . $new_filename
            ];
        } else {
            return ['status' => false, 'message' => 'Gagal mengupload file'];
        }
    }
}

/**
 * Hapus file gambar
 * 
 * @param string $filename Nama file yang akan dihapus
 * @param string $directory Direktori file (relatif terhadap BASE_PATH)
 * @return bool Status penghapusan file
 */
if (!function_exists('deleteImage')) {
    function deleteImage($filename, $directory)
    {
        $file_path = BASE_PATH . '/' . trim($directory, '/') . '/' . $filename;
        if (file_exists($file_path)) {
            return unlink($file_path);
        }
        return false;
    }
}

/**
 * Format tanggal ke format Indonesia
 * 
 * @param string $date Tanggal dalam format database (YYYY-MM-DD)
 * @param bool $with_time Sertakan waktu
 * @return string Tanggal yang diformat
 */
if (!function_exists('formatDate')) {
    function formatDate($date, $with_time = false)
    {
        if (empty($date))
            return '-';

        $timestamp = strtotime($date);
        $day = date('d', $timestamp);
        $month_names = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];
        $month = $month_names[date('m', $timestamp)];
        $year = date('Y', $timestamp);

        $formatted_date = $day . ' ' . $month . ' ' . $year;

        if ($with_time) {
            $formatted_date .= ' ' . date('H:i', $timestamp);
        }

        return $formatted_date;
    }
}

/**
 * Format ukuran file ke format yang mudah dibaca
 * 
 * @param int $size Ukuran file dalam byte
 * @return string Ukuran file yang diformat
 */
if (!function_exists('formatFilesize')) {
    function formatFilesize($size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($size >= 1024 && $i < count($units) - 1) {
            $size /= 1024;
            $i++;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
}

/**
 * Format waktu yang lalu
 * 
 * @param string $datetime Waktu dalam format database
 * @return string Waktu yang lalu
 */
if (!function_exists('timeAgo')) {
    function timeAgo($datetime)
    {
        $time = strtotime($datetime);
        $now = time();
        $diff = $now - $time;

        if ($diff < 60) {
            return $diff . ' detik yang lalu';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' menit yang lalu';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' jam yang lalu';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . ' hari yang lalu';
        } elseif ($diff < 2592000) {
            return floor($diff / 604800) . ' minggu yang lalu';
        } elseif ($diff < 31536000) {
            return floor($diff / 2592000) . ' bulan yang lalu';
        } else {
            return floor($diff / 31536000) . ' tahun yang lalu';
        }
    }
}

/**
 * Potong teks menjadi panjang tertentu
 * 
 * @param string $text Teks yang akan dipotong
 * @param int $length Panjang maksimum
 * @param string $append Teks yang ditambahkan di akhir jika dipotong
 * @return string Teks yang sudah dipotong
 */
if (!function_exists('truncateText')) {
    function truncateText($text, $length = 100, $append = '...')
    {
        $text = strip_tags($text);
        if (strlen($text) <= $length) {
            return $text;
        }

        $text = substr($text, 0, $length);
        $text = substr($text, 0, strrpos($text, ' '));

        return $text . $append;
    }
}

/**
 * Bersihkan string untuk digunakan sebagai slug
 * 
 * @param string $string String yang akan dibersihkan
 * @return string Slug yang sudah dibersihkan
 */
// Tidak mendeklarasikan ulang fungsi createSlug karena sudah ada di includes/config.php

/**
 * Dapatkan opsi status untuk select
 * 
 * @param string $selected Status yang dipilih
 * @return string HTML options
 */
if (!function_exists('getStatusOptions')) {
    function getStatusOptions($selected = '')
    {
        $statuses = [
            'active' => 'Aktif',
            'inactive' => 'Tidak Aktif'
        ];

        $html = '';
        foreach ($statuses as $value => $label) {
            $selected_attr = ($selected == $value) ? ' selected' : '';
            $html .= '<option value="' . $value . '"' . $selected_attr . '>' . $label . '</option>';
        }

        return $html;
    }
}
/**
 * Generate a form-specific CSRF token
 * 
 * @param string $form_name Name of the form
 * @return string Generated token
 */
function generateFormToken($form_name)
{
    if (!isset($_SESSION['form_tokens'])) {
        $_SESSION['form_tokens'] = [];
    }

    $token = bin2hex(random_bytes(32));
    $_SESSION['form_tokens'][$form_name] = [
        'token' => $token,
        'time' => time()
    ];

    return $token;
}

/**
 * Verify a form-specific CSRF token
 * 
 * @param string $form_name Name of the form
 * @param string $token Token to verify
 * @return bool True if token is valid
 */
function verifyFormToken($form_name, $token)
{
    if (!isset($_SESSION['form_tokens'][$form_name])) {
        return false;
    }

    $stored = $_SESSION['form_tokens'][$form_name];

    // Check if token is expired (2 hours)
    if (time() - $stored['time'] > 7200) {
        unset($_SESSION['form_tokens'][$form_name]);
        return false;
    }

    return hash_equals($stored['token'], $token);
}

/**
 * Log aktivitas admin
 * 
 * @param int $user_id ID pengguna
 * @param string $activity Aktivitas yang dilakukan
 * @param array $details Detail aktivitas (opsional)
 * @return bool Status penyimpanan log
 */
if (!function_exists('logActivity')) {
    function logActivity($user_id, $activity, $details = [])
    {
        global $pdo;

        try {
            $stmt = $pdo->prepare("
                INSERT INTO admin_logs (user_id, activity, details, ip_address, user_agent, created_at) 
                VALUES (:user_id, :activity, :details, :ip_address, :user_agent, NOW())
            ");

            $stmt->execute([
                'user_id' => $user_id,
                'activity' => $activity,
                'details' => !empty($details) ? json_encode($details) : NULL,
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
            ]);

            return true;
        } catch (PDOException $e) {
            error_log('Error logging activity: ' . $e->getMessage());
            return false;
        }
    }
}
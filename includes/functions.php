<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Tambahkan log untuk memastikan file dimuat
error_log('Functions.php loaded successfully');

/**
 * Memformat tanggal ke format Indonesia
 * 
 * @param string $date Tanggal dalam format Y-m-d
 * @param bool $with_day Tampilkan nama hari
 * @return string Tanggal yang sudah diformat
 */
function formatDateIndo($date, $with_day = false)
{
    if (empty($date))
        return '';

    $timestamp = strtotime($date);
    $day_names = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $month_names = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    $day = date('w', $timestamp);
    $date = date('j', $timestamp);
    $month = date('n', $timestamp);
    $year = date('Y', $timestamp);

    if ($with_day) {
        return $day_names[$day] . ', ' . $date . ' ' . $month_names[$month] . ' ' . $year;
    } else {
        return $date . ' ' . $month_names[$month] . ' ' . $year;
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

/**
 * Buat URL yang aman
 * 
 * @param string $url URL yang akan diamankan
 * @return string URL yang sudah diamankan
 */
function safeUrl($url)
{
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}

/**
 * Mendeteksi perangkat mobile
 * 
 * @return bool True jika perangkat mobile
 */
function isMobile()
{
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

/**
 * Kirim pesan kontak ke database
 * 
 * @param array $data Data pesan kontak
 * @return array Status dan pesan
 */
function sendContactMessage($data)
{
    global $pdo;

    try {
        $stmt = $pdo->prepare("
            INSERT INTO messages (name, email, phone, category, message, ip_address) 
            VALUES (:name, :email, :phone, :category, :message, :ip_address)
        ");

        $stmt->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'category' => $data['category'],
            'message' => $data['message'],
            'ip_address' => $_SERVER['REMOTE_ADDR']
        ]);

        return ['success' => true, 'message' => 'Pesan berhasil dikirim'];
    } catch (PDOException $e) {
        error_log('Error sending contact message: ' . $e->getMessage());
        return ['success' => false, 'message' => 'Terjadi kesalahan saat mengirim pesan'];
    }
}

/**
 * Ambil data program dari database
 * 
 * @param int $id ID program (opsional)
 * @return array Data program
 */
function getPrograms($id = null)
{
    global $pdo;

    try {
        if ($id) {
            // Ambil satu program berdasarkan ID
            $stmt = $pdo->prepare("SELECT * FROM programs WHERE id = :id AND is_active = 1");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } else {
            // Ambil semua program aktif
            $stmt = $pdo->query("SELECT * FROM programs WHERE is_active = 1 ORDER BY order_number");
            return $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log('Error getting programs: ' . $e->getMessage());
        return [];
    }
}

/**
 * Ambil data statistik dari database
 * 
 * @param string $category Kategori statistik
 * @param int $year Tahun (opsional)
 * @return array Data statistik
 */
function getStatistics($category, $year = null)
{
    global $pdo;

    try {
        if ($year) {
            $stmt = $pdo->prepare("SELECT * FROM statistics WHERE category = :category AND year = :year");
            $stmt->execute(['category' => $category, 'year' => $year]);
        } else {
            // Ambil tahun terbaru
            $stmt = $pdo->prepare("
                SELECT * FROM statistics 
                WHERE category = :category 
                ORDER BY year DESC LIMIT 1
            ");
            $stmt->execute(['category' => $category]);
        }

        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Error getting statistics: ' . $e->getMessage());
        return [];
    }
}

/**
 * Ambil data capaian program dari database
 * 
 * @param int $year Tahun (opsional)
 * @return array Data capaian program
 */
function getAchievements($year = null)
{
    global $pdo;

    try {
        if ($year) {
            $stmt = $pdo->prepare("
                SELECT * FROM achievements 
                WHERE year = :year AND is_active = 1 
                ORDER BY percentage DESC
            ");
            $stmt->execute(['year' => $year]);
        } else {
            // Ambil tahun terbaru
            $latest_year = $pdo->query("
                SELECT MAX(year) as max_year FROM achievements WHERE is_active = 1
            ")->fetch();

            $year = $latest_year['max_year'] ?? date('Y');

            $stmt = $pdo->prepare("
                SELECT * FROM achievements 
                WHERE year = :year AND is_active = 1 
                ORDER BY percentage DESC
            ");
            $stmt->execute(['year' => $year]);
        }

        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error getting achievements: ' . $e->getMessage());
        return [];
    }
}

/**
 * Ambil data publikasi/berita dari database
 * 
 * @param int $limit Jumlah publikasi yang diambil
 * @param bool $featured Hanya ambil yang featured
 * @param string $category Kategori publikasi (opsional)
 * @return array Data publikasi
 */
function getPosts($limit = 4, $featured = false, $category = null)
{
    global $pdo;

    try {
        $sql = "
            SELECT p.*, u.name as author_name 
            FROM posts p 
            LEFT JOIN users u ON p.created_by = u.id 
            WHERE p.is_active = 1
        ";

        $params = [];

        if ($featured) {
            $sql .= " AND p.is_featured = 1";
        }

        if ($category) {
            $sql .= " AND p.category = :category";
            $params['category'] = $category;
        }

        $sql .= " ORDER BY p.publish_date DESC LIMIT :limit";
        $params['limit'] = $limit;

        $stmt = $pdo->prepare($sql);

        // Bind params
        foreach ($params as $key => $value) {
            if ($key == 'limit') {
                $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':' . $key, $value);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error getting posts: ' . $e->getMessage());
        return [];
    }
}

/**
 * Ambil data galeri dari database
 * 
 * @param int $limit Jumlah galeri yang diambil
 * @param string $category Kategori galeri (opsional)
 * @return array Data galeri
 */
function getGallery($limit = 6, $category = null)
{
    global $pdo;

    try {
        $sql = "SELECT * FROM gallery WHERE is_active = 1";
        $params = [];

        if ($category) {
            $sql .= " AND category = :category";
            $params['category'] = $category;
        }

        $sql .= " ORDER BY event_date DESC LIMIT :limit";
        $params['limit'] = $limit;

        $stmt = $pdo->prepare($sql);

        // Bind params
        foreach ($params as $key => $value) {
            if ($key == 'limit') {
                $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':' . $key, $value);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error getting gallery: ' . $e->getMessage());
        return [];
    }
}

/**
 * Ambil data dokumen dari database
 * 
 * @param int $limit Jumlah dokumen yang diambil
 * @param string $category Kategori dokumen (opsional)
 * @return array Data dokumen
 */
function getDocuments($limit = 4, $category = null)
{
    global $pdo;

    try {
        $sql = "SELECT * FROM documents WHERE is_active = 1";
        $params = [];

        if ($category) {
            $sql .= " AND category = :category";
            $params['category'] = $category;
        }

        $sql .= " ORDER BY upload_date DESC LIMIT :limit";
        $params['limit'] = $limit;

        $stmt = $pdo->prepare($sql);

        // Bind params
        foreach ($params as $key => $value) {
            if ($key == 'limit') {
                $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':' . $key, $value);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error getting documents: ' . $e->getMessage());
        return [];
    }
}

/**
 * Ambil data wilayah kerja dari database
 * 
 * @return array Data wilayah kerja
 */
function getWorkAreas()
{
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT * FROM work_areas WHERE is_active = 1 ORDER BY name");
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error getting work areas: ' . $e->getMessage());
        return [];
    }
}

/**
 * Ambil data layanan dari database
 * 
 * @param int $id ID layanan (opsional)
 * @return array Data layanan
 */
function getServices($id = null)
{
    global $pdo;

    try {
        if ($id) {
            // Ambil satu layanan berdasarkan ID
            $stmt = $pdo->prepare("SELECT * FROM services WHERE id = :id AND is_active = 1");
            $stmt->execute(['id' => $id]);
            return $stmt->fetch();
        } else {
            // Ambil semua layanan aktif
            $stmt = $pdo->query("SELECT * FROM services WHERE is_active = 1 ORDER BY order_number");
            return $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log('Error getting services: ' . $e->getMessage());
        return [];
    }
}

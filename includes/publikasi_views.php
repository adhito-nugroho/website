<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Memastikan fungsi-fungsi utama telah dimuat
if (!function_exists('formatDateIndo')) {
    require_once BASE_PATH . '/includes/functions.php';
}

/**
 * Menampilkan halaman daftar semua publikasi/berita
 */
function renderAllPublications() {
    global $pdo;
    
    // Pastikan koneksi database tersedia
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        echo '<div class="container py-5"><div class="alert alert-danger">Koneksi database tidak tersedia</div></div>';
        return;
    }
    
    // Pagination setup
    $page_number = isset($_GET['pn']) ? (int)$_GET['pn'] : 1;
    $items_per_page = 8;
    $offset = ($page_number - 1) * $items_per_page;
    
    // Filter kategori
    $category_filter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
    
    // Ambil total publikasi untuk pagination
    try {
        $count_sql = "SELECT COUNT(*) as total FROM posts WHERE is_active = 1";
        $params = [];
        
        if (!empty($category_filter)) {
            $count_sql .= " AND category = :category";
            $params['category'] = $category_filter;
        }
        
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_items = $count_stmt->fetch()['total'];
        
        $total_pages = ceil($total_items / $items_per_page);
    } catch (PDOException $e) {
        error_log('Error counting posts: ' . $e->getMessage());
        $total_items = 0;
        $total_pages = 1;
    }
    
    // Ambil data publikasi dengan pagination
    $posts = [];
    try {
        $sql = "
            SELECT p.*, u.name as author_name 
            FROM posts p 
            LEFT JOIN users u ON p.created_by = u.id 
            WHERE p.is_active = 1
        ";
        
        $params = [];
        
        if (!empty($category_filter)) {
            $sql .= " AND p.category = :category";
            $params['category'] = $category_filter;
        }
        
        $sql .= " ORDER BY p.publish_date DESC 
                 LIMIT :offset, :limit";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
        
        $stmt->execute();
        $posts = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error getting posts: ' . $e->getMessage());
    }
    
    // Ambil semua kategori untuk filter
    $categories = [];
    try {
        $stmt = $pdo->query("SELECT DISTINCT category FROM posts WHERE is_active = 1 ORDER BY category");
        while ($row = $stmt->fetch()) {
            $categories[] = $row['category'];
        }
    } catch (PDOException $e) {
        error_log('Error getting categories: ' . $e->getMessage());
    }
    
    // Render template
    ?>
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                        <li class="breadcrumb-item active">Publikasi & Berita</li>
                    </ol>
                </nav>
                <h2>Publikasi & Berita</h2>
                <p class="text-muted">Informasi terkini seputar kehutanan dan kegiatan CDK Wilayah Bojonegoro</p>
            </div>
            <div class="col-md-4">
                <div class="d-flex justify-content-end mb-3">
                    <a href="index.php?page=publikasi&view=documents" class="btn btn-outline-success me-2">
                        <i class="ri-file-list-line"></i> Lihat Dokumen
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="ri-home-line"></i> Beranda
                    </a>
                </div>
                <form action="index.php" method="get" class="d-flex">
                    <input type="hidden" name="page" value="publikasi">
                    <input type="hidden" name="view" value="all">
                    <select name="category" class="form-select me-2" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($category_filter === $category) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-success">Filter</button>
                </form>
            </div>
        </div>
        
        <?php if (count($posts) > 0): ?>
            <div class="row g-4">
                <?php foreach ($posts as $post): ?>
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="news-card h-100 animate-hover">
                            <img src="uploads/publikasi/<?php echo htmlspecialchars($post['image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="news-image">
                            <div class="news-content">
                                <span class="news-tag"><?php echo htmlspecialchars($post['category']); ?></span>
                                <h5><?php echo htmlspecialchars($post['title']); ?></h5>
                                <p><?php echo truncateText(strip_tags($post['content']), 100); ?></p>
                                <div class="news-meta">
                                    <span><i class="ri-calendar-line"></i> <?php echo formatDateIndo($post['publish_date']); ?></span>
                                    <a href="index.php?page=publikasi&id=<?php echo $post['id']; ?>" class="read-more">Baca Selengkapnya <i class="ri-arrow-right-line"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-container mt-5">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page_number > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=publikasi&view=all<?php echo !empty($category_filter) ? '&category='.urlencode($category_filter) : ''; ?>&pn=<?php echo $page_number - 1; ?>">
                                        <i class="ri-arrow-left-s-line"></i> Sebelumnya
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            // Tampilkan maksimal 5 nomor halaman
                            $start_page = max(1, min($page_number - 2, $total_pages - 4));
                            $end_page = min($start_page + 4, $total_pages);
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?php echo ($i === $page_number) ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=publikasi&view=all<?php echo !empty($category_filter) ? '&category='.urlencode($category_filter) : ''; ?>&pn=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page_number < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=publikasi&view=all<?php echo !empty($category_filter) ? '&category='.urlencode($category_filter) : ''; ?>&pn=<?php echo $page_number + 1; ?>">
                                        Selanjutnya <i class="ri-arrow-right-s-line"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-5">
                <a href="index.php?page=publikasi&view=documents" class="btn btn-outline-success me-3">
                    <i class="ri-file-list-line"></i> Lihat Dokumen
                </a>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="ri-home-line"></i> Kembali ke Beranda
                </a>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="ri-information-line me-2"></i> Belum ada publikasi pada kategori ini.
            </div>
            
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="ri-home-line"></i> Kembali ke Beranda
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Menampilkan halaman detail publikasi/berita
 * 
 * @param int $id ID publikasi
 */
function renderPublicationDetail($id) {
    global $pdo;
    
    // Pastikan koneksi database tersedia
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        echo '<div class="container py-5"><div class="alert alert-danger">Koneksi database tidak tersedia</div></div>';
        return;
    }
    
    // Ambil data publikasi berdasarkan ID
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, u.name as author_name 
            FROM posts p 
            LEFT JOIN users u ON p.created_by = u.id 
            WHERE p.id = :id AND p.is_active = 1
        ");
        $stmt->execute(['id' => $id]);
        $post = $stmt->fetch();
        
        if (!$post) {
            echo '<div class="container py-5"><div class="alert alert-danger">Publikasi tidak ditemukan</div></div>';
            return;
        }
        
        // Ambil publikasi terkait (dengan kategori yang sama)
        $stmt = $pdo->prepare("
            SELECT id, title, image, publish_date 
            FROM posts 
            WHERE category = :category AND id != :id AND is_active = 1 
            ORDER BY publish_date DESC 
            LIMIT 3
        ");
        $stmt->execute([
            'category' => $post['category'],
            'id' => $id
        ]);
        $related_posts = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        error_log('Error getting post detail: ' . $e->getMessage());
        echo '<div class="container py-5"><div class="alert alert-danger">Terjadi kesalahan saat memuat data</div></div>';
        return;
    }
    
    // Render template
    ?>
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="index.php?page=publikasi&view=all">Publikasi</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($post['title']); ?></li>
                    </ol>
                </nav>
                
                <div class="card post-detail-card">
                    <img src="uploads/publikasi/<?php echo htmlspecialchars($post['image']); ?>" class="post-detail-image" alt="<?php echo htmlspecialchars($post['title']); ?>">
                    
                    <div class="card-body">
                        <span class="post-category"><?php echo htmlspecialchars($post['category']); ?></span>
                        <h1 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h1>
                        
                        <div class="post-meta">
                            <span class="me-3"><i class="ri-calendar-line"></i> <?php echo formatDateIndo($post['publish_date'], true); ?></span>
                            <span class="me-3"><i class="ri-user-line"></i> <?php echo htmlspecialchars($post['author_name'] ?? 'Admin'); ?></span>
                        </div>
                        
                        <div class="post-content mt-4">
                            <?php echo $post['content']; ?>
                        </div>
                        
                        <div class="post-tags mt-4">
                            <i class="ri-price-tag-3-line"></i>
                            <a href="index.php?page=publikasi&view=all&category=<?php echo urlencode($post['category']); ?>" class="post-tag">
                                <?php echo htmlspecialchars($post['category']); ?>
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="post-navigation mt-4">
                    <a href="index.php?page=publikasi&view=all" class="btn btn-outline-success">
                        <i class="ri-arrow-left-line"></i> Kembali ke Daftar Publikasi
                    </a>
                </div>
            </div>
            
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="sidebar-box">
                    <h4 class="sidebar-title">Publikasi Terkait</h4>
                    
                    <?php if (count($related_posts) > 0): ?>
                        <div class="related-posts">
                            <?php foreach ($related_posts as $related): ?>
                                <a href="index.php?page=publikasi&id=<?php echo $related['id']; ?>" class="related-post-item">
                                    <img src="uploads/publikasi/<?php echo htmlspecialchars($related['image']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>">
                                    <div class="related-post-content">
                                        <h6><?php echo htmlspecialchars($related['title']); ?></h6>
                                        <span><i class="ri-calendar-line"></i> <?php echo formatDateIndo($related['publish_date']); ?></span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">Tidak ada publikasi terkait.</p>
                    <?php endif; ?>
                </div>
                
                <div class="sidebar-box mt-4">
                    <h4 class="sidebar-title">Kategori</h4>
                    <ul class="category-list">
                        <?php
                        try {
                            // Ambil semua kategori yang ada di publikasi
                            $stmt = $pdo->query("
                                SELECT category, COUNT(*) as post_count 
                                FROM posts 
                                WHERE is_active = 1 
                                GROUP BY category 
                                ORDER BY category
                            ");
                            while ($category = $stmt->fetch()):
                        ?>
                            <li>
                                <a href="index.php?page=publikasi&view=all&category=<?php echo urlencode($category['category']); ?>">
                                    <?php echo htmlspecialchars($category['category']); ?>
                                    <span class="badge bg-light text-dark"><?php echo $category['post_count']; ?></span>
                                </a>
                            </li>
                        <?php endwhile;
                        } catch (PDOException $e) {
                            error_log('Error getting post categories: ' . $e->getMessage());
                        }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Menampilkan daftar semua dokumen
 */
function renderAllDocuments() {
    global $pdo;
    
    // Pastikan koneksi database tersedia
    if (!isset($pdo) || !($pdo instanceof PDO)) {
        echo '<div class="container py-5"><div class="alert alert-danger">Koneksi database tidak tersedia</div></div>';
        return;
    }
    
    // Pagination setup
    $page_number = isset($_GET['pn']) ? (int)$_GET['pn'] : 1;
    $items_per_page = 12;
    $offset = ($page_number - 1) * $items_per_page;
    
    // Filter kategori
    $category_filter = isset($_GET['category']) ? sanitizeInput($_GET['category']) : '';
    
    // Ambil total dokumen untuk pagination
    try {
        $count_sql = "SELECT COUNT(*) as total FROM documents WHERE is_active = 1";
        $params = [];
        
        if (!empty($category_filter)) {
            $count_sql .= " AND category = :category";
            $params['category'] = $category_filter;
        }
        
        $count_stmt = $pdo->prepare($count_sql);
        $count_stmt->execute($params);
        $total_items = $count_stmt->fetch()['total'];
        
        $total_pages = ceil($total_items / $items_per_page);
    } catch (PDOException $e) {
        error_log('Error counting documents: ' . $e->getMessage());
        $total_items = 0;
        $total_pages = 1;
    }
    
    // Ambil data dokumen dengan pagination
    $documents = [];
    try {
        $sql = "
            SELECT d.*, u.name as uploader_name 
            FROM documents d
            LEFT JOIN users u ON d.created_by = u.id 
            WHERE d.is_active = 1
        ";
        
        $params = [];
        
        if (!empty($category_filter)) {
            $sql .= " AND d.category = :category";
            $params['category'] = $category_filter;
        }
        
        $sql .= " ORDER BY d.upload_date DESC 
                 LIMIT :offset, :limit";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
        
        $stmt->execute();
        $documents = $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error getting documents: ' . $e->getMessage());
    }
    
    // Ambil semua kategori untuk filter
    $categories = [];
    try {
        $stmt = $pdo->query("SELECT DISTINCT category FROM documents WHERE is_active = 1 ORDER BY category");
        while ($row = $stmt->fetch()) {
            $categories[] = $row['category'];
        }
    } catch (PDOException $e) {
        error_log('Error getting document categories: ' . $e->getMessage());
    }
    
    // Render template
    ?>
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-8">
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Beranda</a></li>
                        <li class="breadcrumb-item"><a href="index.php?page=publikasi">Publikasi</a></li>
                        <li class="breadcrumb-item active">Dokumen Penting</li>
                    </ol>
                </nav>
                <h2>Dokumen Penting</h2>
                <p class="text-muted">Akses dokumen penting terkait kehutanan dan kegiatan CDK Wilayah Bojonegoro</p>
            </div>
            <div class="col-md-4">
                <div class="d-flex justify-content-end mb-3">
                    <a href="index.php?page=publikasi&view=all" class="btn btn-outline-success me-2">
                        <i class="ri-file-list-line"></i> Lihat Publikasi
                    </a>
                    <a href="index.php" class="btn btn-outline-secondary">
                        <i class="ri-home-line"></i> Beranda
                    </a>
                </div>
                <form action="index.php" method="get" class="d-flex">
                    <input type="hidden" name="page" value="publikasi">
                    <input type="hidden" name="view" value="documents">
                    <select name="category" class="form-select me-2" onchange="this.form.submit()">
                        <option value="">Semua Kategori</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($category_filter === $category) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-success">Filter</button>
                </form>
            </div>
        </div>
        
        <?php if (count($documents) > 0): ?>
            <div class="row g-4">
                <?php foreach ($documents as $document): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="document-card h-100">
                            <?php
                            // Set icon berdasarkan file type
                            $icon_class = 'ri-file-text-line';
                            switch (strtolower($document['file_type'])) {
                                case 'pdf':
                                    $icon_class = 'ri-file-pdf-line';
                                    break;
                                case 'doc':
                                case 'docx':
                                    $icon_class = 'ri-file-word-line';
                                    break;
                                case 'xls':
                                case 'xlsx':
                                    $icon_class = 'ri-file-excel-line';
                                    break;
                                case 'ppt':
                                case 'pptx':
                                    $icon_class = 'ri-file-ppt-line';
                                    break;
                            }
                            ?>
                            
                            <div class="document-icon">
                                <i class="<?php echo $icon_class; ?>"></i>
                            </div>
                            
                            <div class="document-content">
                                <h5><?php echo htmlspecialchars($document['title']); ?></h5>
                                <?php if (!empty($document['description'])): ?>
                                    <p><?php echo truncateText($document['description'], 80); ?></p>
                                <?php endif; ?>
                                
                                <div class="document-meta">
                                    <span class="me-2"><i class="ri-calendar-line"></i> <?php echo formatDateIndo($document['upload_date']); ?></span>
                                    <span class="me-2"><i class="ri-file-list-line"></i> <?php echo strtoupper($document['file_type']); ?></span>
                                    <span><i class="ri-download-line"></i> <?php echo number_format($document['download_count']); ?></span>
                                </div>
                                
                                <a href="uploads/dokumen/<?php echo htmlspecialchars($document['filename']); ?>" target="_blank" class="btn btn-sm btn-outline-success mt-3">
                                    <i class="ri-download-line"></i> Unduh Dokumen
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination-container mt-5">
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($page_number > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=publikasi&view=documents<?php echo !empty($category_filter) ? '&category='.urlencode($category_filter) : ''; ?>&pn=<?php echo $page_number - 1; ?>">
                                        <i class="ri-arrow-left-s-line"></i> Sebelumnya
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            // Tampilkan maksimal 5 nomor halaman
                            $start_page = max(1, min($page_number - 2, $total_pages - 4));
                            $end_page = min($start_page + 4, $total_pages);
                            
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?php echo ($i === $page_number) ? 'active' : ''; ?>">
                                    <a class="page-link" href="index.php?page=publikasi&view=documents<?php echo !empty($category_filter) ? '&category='.urlencode($category_filter) : ''; ?>&pn=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page_number < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="index.php?page=publikasi&view=documents<?php echo !empty($category_filter) ? '&category='.urlencode($category_filter) : ''; ?>&pn=<?php echo $page_number + 1; ?>">
                                        Selanjutnya <i class="ri-arrow-right-s-line"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
            
            <div class="text-center mt-5">
                <a href="index.php?page=publikasi&view=all" class="btn btn-outline-success me-3">
                    <i class="ri-newspaper-line"></i> Lihat Publikasi
                </a>
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="ri-home-line"></i> Kembali ke Beranda
                </a>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="ri-information-line me-2"></i> Belum ada dokumen pada kategori ini.
            </div>
            
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-outline-secondary">
                    <i class="ri-home-line"></i> Kembali ke Beranda
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
} 
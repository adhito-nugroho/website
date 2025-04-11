<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Memastikan variabel $pdo tersedia
if (!isset($pdo) || !($pdo instanceof PDO)) {
    echo '<div class="alert alert-danger">Koneksi database tidak tersedia</div>';
    return;
}

// Memastikan fungsi-fungsi yang diperlukan sudah tersedia
if (!function_exists('formatDateIndo') || !function_exists('sanitizeInput') || !function_exists('truncateText')) {
    require_once BASE_PATH . '/includes/functions.php';
}

// Include publikasi_views.php untuk halaman khusus publikasi
require_once BASE_PATH . '/includes/publikasi_views.php';

// Periksa apakah ada parameter view atau id
if (isset($_GET['view']) || isset($_GET['id'])) {
    try {
        // Jika ada parameter id, tampilkan detail publikasi
        if (isset($_GET['id'])) {
            renderPublicationDetail((int)$_GET['id']);
            return; // Hentikan eksekusi file ini
        }
        
        // Jika ada parameter view, tampilkan halaman sesuai parameter
        if (isset($_GET['view'])) {
            switch ($_GET['view']) {
                case 'all':
                    renderAllPublications();
                    return; // Hentikan eksekusi file ini
                    
                case 'documents':
                    renderAllDocuments();
                    return; // Hentikan eksekusi file ini
                    
                default:
                    // Jika parameter view tidak valid, lanjutkan dengan tampilan default
                    break;
            }
        }
    } catch (Exception $e) {
        error_log('Error rendering publication view: ' . $e->getMessage());
        echo '<div class="container py-5"><div class="alert alert-danger">Terjadi kesalahan saat memuat halaman publikasi. Silakan coba lagi nanti.</div></div>';
        return;
    }
}

// Jika tidak ada parameter view atau id, atau parameter tidak valid,
// tampilkan section publikasi pada halaman beranda (default)

// Ambil data publikasi/berita dan dokumen dari database
try {
    $posts = getPosts(4);
    $documents = getDocuments(4);
    
    // Pastikan $posts dan $documents adalah array
    if (!is_array($posts)) {
        $posts = [];
    }
    
    if (!is_array($documents)) {
        $documents = [];
    }
} catch (Exception $e) {
    error_log('Error getting posts/documents: ' . $e->getMessage());
    $posts = [];
    $documents = [];
}
?>

<!-- Publikasi Section -->
<section id="publikasi" class="publication-section">
  <div class="container py-5">
    <div class="section-header text-center mb-5" data-aos="fade-up">
      <h2>Publikasi & Informasi</h2>
      <p class="section-subheading">Berita terkini dan dokumen penting terkait kehutanan</p>
    </div>

    <div class="row g-4">
      <!-- Berita Terkini -->
      <div class="col-lg-8" data-aos="fade-up">
        <div class="row g-4">
          <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $index => $post): ?>
              <!-- Berita <?php echo $index + 1; ?> -->
              <div class="col-md-6">
                <div class="news-card animate-hover">
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
          <?php else: ?>
            <div class="col-12 text-center">
              <p>Belum ada data publikasi.</p>
            </div>
          <?php endif; ?>
        </div>

        <div class="text-center mt-4">
          <a href="index.php?page=publikasi&view=all" class="btn btn-outline-success">
            Lihat Semua Berita <i class="ri-arrow-right-line"></i>
          </a>
        </div>
      </div>

      <!-- Sidebar Dokumen -->
      <div class="col-lg-4" data-aos="fade-up">
        <div class="sidebar-box">
          <h4 class="sidebar-title">Dokumen Penting</h4>
          <div class="doc-list">
            <?php if (count($documents) > 0): ?>
              <?php foreach ($documents as $document): ?>
                <a href="uploads/dokumen/<?php echo htmlspecialchars($document['filename']); ?>" class="doc-item" target="_blank">
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
                  <i class="<?php echo $icon_class; ?> doc-icon"></i>
                  <div class="doc-info">
                    <h6><?php echo htmlspecialchars($document['title']); ?></h6>
                    <span class="doc-meta">
                      <?php echo formatDateIndo($document['upload_date']); ?> • 
                      <?php echo strtoupper($document['file_type']); ?> • 
                      <?php echo round($document['file_size'] / 1024, 1); ?> KB
                    </span>
                  </div>
                </a>
              <?php endforeach; ?>
            <?php else: ?>
              <p class="text-center">Belum ada dokumen.</p>
            <?php endif; ?>
          </div>
          <div class="text-center mt-4">
            <a href="index.php?page=publikasi&view=documents" class="btn btn-outline-success btn-sm">
              Lihat Semua Dokumen <i class="ri-arrow-right-line"></i>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

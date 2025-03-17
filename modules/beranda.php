<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Ambil data statistik terbaru
$forest_stats = getStatistics('forest-area');
$production_stats = getStatistics('forest-production');

// Decode data JSON jika ada
$forest_data = [];
if ($forest_stats && isset($forest_stats['data_json'])) {
    $forest_data = json_decode($forest_stats['data_json'], true);
}

// Ambil berita terbaru untuk highlight
$featured_posts = getPosts(2, true);
?>

<!-- Hero Section -->
<section class="hero-section" id="hero">
  <div class="hero-video-bg">
    <video autoplay muted loop>
      <source src="assets/videos/forest-bg.mp4" type="video/mp4">
    </video>
  </div>
  <div class="hero-overlay"></div>
  <div id="particles-js"></div>
  
  <div class="hero-content-wrapper">
    <div class="hero-content" data-aos="fade-up">
      <h1 class="text-white mb-4">Cabang Dinas Kehutanan Wilayah Bojonegoro</h1>
      <p class="text-white mb-4">
        Unit Pelaksana Teknis Dinas Kehutanan Provinsi Jawa Timur yang melaksanakan
        kebijakan teknis operasional di bidang kehutanan
      </p>
      <div class="hero-buttons">
        <a href="#layanan" class="btn btn-light btn-lg me-3">Layanan Kami</a>
        <a href="#kontak" class="btn btn-outline-light btn-lg">Hubungi Kami</a>
      </div>
    </div>
  </div>
</section>

<!-- Floating Stats -->
<section class="floating-stats">
  <div class="container">
    <div class="stats-container" data-aos="fade-up" data-aos-delay="200">
      <div class="row g-0">
        <div class="col-md-3">
          <div class="stat-item">
            <div class="stat-icon">
              <i class="ri-plant-line"></i>
            </div>
            <div class="stat-content">
              <h3 class="counter"><?php echo number_format($forest_data['data'][0] ?? 0); ?></h3>
              <p>Hektar Hutan Produksi</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-item">
            <div class="stat-icon">
              <i class="ri-shield-line"></i>
            </div>
            <div class="stat-content">
              <h3 class="counter"><?php echo number_format($forest_data['data'][1] ?? 0); ?></h3>
              <p>Hektar Hutan Lindung</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-item">
            <div class="stat-icon">
              <i class="ri-community-line"></i>
            </div>
            <div class="stat-content">
              <h3 class="counter"><?php echo number_format($forest_data['data'][3] ?? 0); ?></h3>
              <p>Hektar Hutan Rakyat</p>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="stat-item">
            <div class="stat-icon">
              <i class="ri-government-line"></i>
            </div>
            <div class="stat-content">
              <h3 class="counter">6</h3>
              <p>Wilayah Kerja</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section> 

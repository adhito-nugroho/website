<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
  http_response_code(403);
  exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Load functions for homepage if not already loaded
if (!function_exists('getHeroContent')) {
  require_once BASE_PATH . '/includes/homepage_functions.php';
}

// Ambil data hero section
$hero_content = getHeroContent();

// Ambil data statistik terbaru
$forest_stats = getStatistics('forest-area');
$production_stats = getStatistics('forest-production');

// Decode data JSON jika ada
$forest_data = [];
if ($forest_stats && isset($forest_stats['data_json'])) {
  $forest_data = json_decode($forest_stats['data_json'], true);
}

// Ambil data wilayah kerja
$work_areas = getWorkAreas();
$work_areas_count = count($work_areas);

// Ambil berita terbaru untuk highlight
$featured_posts = getPosts(2, true);
?>
<style>
  /* Modern Floating Stats Section */
  .floating-stats {
    margin-top: -70px;
    position: relative;
    z-index: 10;
  }

  .stats-container {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-lg);
    border: 1px solid rgba(255, 255, 255, 0.3);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
  }
  
  .stats-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
  }

  .stat-item {
    padding: 25px 20px;
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
    border-right: 1px solid rgba(0, 0, 0, 0.05);
    position: relative;
    overflow: hidden;
  }
  
  .stat-item::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 3px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    transform: scaleX(0);
    transform-origin: left;
    transition: transform 0.5s ease;
  }
  
  .stat-item:hover::before {
    transform: scaleX(1);
  }

  .stat-icon {
    margin-bottom: 15px;
    display: inline-block;
    position: relative;
    z-index: 1;
  }

  .stat-icon i {
    font-size: 2rem;
    color: var(--primary-color);
    background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    width: 65px;
    height: 65px;
    line-height: 65px;
    border-radius: 50%;
    display: inline-block;
    transition: all 0.5s ease;
    position: relative;
  }
  
  .stat-icon::after {
    content: '';
    position: absolute;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: rgba(71, 187, 137, 0.1);
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: -1;
    transition: all 0.3s ease;
  }
  
  .stat-item:hover .stat-icon::after {
    width: 60px;
    height: 60px;
    background: rgba(71, 187, 137, 0.2);
  }

  .stat-content h3 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 5px;
    color: var(--primary-dark);
    transition: all 0.3s ease;
  }
  
  .stat-item:hover .stat-content h3 {
    transform: translateY(-5px);
  }

  .stat-content p {
    font-size: 15px;
    color: var(--text-medium);
    margin-bottom: 0;
    transition: all 0.3s ease;
  }
  
  .stat-item:hover .stat-content p {
    color: var(--primary-color);
  }

  /* Dark mode support for floating stats */
  .dark-mode .stats-container {
    background: rgba(30, 35, 40, 0.9);
    border-color: rgba(50, 60, 70, 0.3);
  }
  
  .dark-mode .stat-item {
    border-color: rgba(255, 255, 255, 0.05);
  }
  
  .dark-mode .stat-content h3 {
    color: var(--dark-text);
  }
  
  .dark-mode .stat-content p {
    color: var(--dark-text-muted);
  }
  
  .dark-mode .stat-item:hover .stat-content p {
    color: var(--secondary-light);
  }

  /* Responsif untuk layar kecil */
  @media (max-width: 767px) {
    .floating-stats {
      margin-top: 20px;
    }

    .stat-item {
      border-right: none;
      border-bottom: 1px solid rgba(0, 0, 0, 0.05);
      padding: 20px;
    }
    
    .dark-mode .stat-item {
      border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .stat-icon i {
      width: 60px;
      height: 60px;
      line-height: 60px;
      font-size: 1.75rem;
    }

    .stat-content h3 {
      font-size: 24px;
    }
  }
</style>
<!-- Hero Section -->
<section class="hero-section" id="hero">
  <div class="hero-video-bg">
    <video autoplay muted loop playsinline preload="auto" poster="assets/images/hero-bg.jpg">
      <source src="assets/videos/<?php echo htmlspecialchars($hero_content['background_video']); ?>" type="video/mp4">
    </video>
  </div>
  <div class="hero-overlay"></div>
  <div id="particles-js"></div>

  <div class="hero-content-wrapper">
    <div class="hero-content glass-effect" data-aos="fade-up">
      <h1 class="text-white mb-4 gradient-text"><?php echo htmlspecialchars($hero_content['title']); ?></h1>
      <p class="text-white mb-4">
        <?php echo htmlspecialchars($hero_content['subtitle']); ?>
      </p>
      <div class="hero-buttons">
        <a href="<?php echo htmlspecialchars($hero_content['button1_link']); ?>"
          class="btn btn-primary btn-lg me-3"><i class="ri-leaf-line me-2"></i><?php echo htmlspecialchars($hero_content['button1_text']); ?></a>
        <a href="<?php echo htmlspecialchars($hero_content['button2_link']); ?>"
          class="btn btn-outline-light btn-lg"><i class="ri-information-line me-2"></i><?php echo htmlspecialchars($hero_content['button2_text']); ?></a>
      </div>
    </div>
  </div>
</section>

<style>
  /* Enhanced Hero Section */
  .hero-section {
    position: relative;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    margin-top: calc(-1 * var(--navbar-height));
    overflow: hidden;
  }
  
  .hero-video-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    overflow: hidden;
  }
  
  .hero-video-bg video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    filter: brightness(1.1) contrast(1.1);
    transform: scale(1.02); /* Slightly scale the video to avoid white edges */
  }
  
  .hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(20, 102, 68, 0.6), rgba(29, 94, 94, 0.6));
    z-index: 2;
    mix-blend-mode: multiply; /* This enhances the video visibility while keeping text readable */
  }
  
  .hero-content-wrapper {
    position: relative;
    z-index: 4;
    width: 100%;
    padding: 0 1.5rem;
    display: flex;
    justify-content: center;
    align-items: center;
  }
  
  .glass-effect {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: var(--radius-lg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
  }
  
  .hero-content {
    max-width: 800px;
    text-align: center;
    padding: 3.5rem;
    transform: translateY(0);
    transition: transform 0.5s ease;
    background: rgba(255, 255, 255, 0.08); /* More transparent to see the video */
    backdrop-filter: blur(5px); /* Less blur to see the video better */
    -webkit-backdrop-filter: blur(5px);
  }
  
  .hero-content:hover {
    transform: translateY(-10px);
  }
  
  .hero-content h1 {
    font-size: 3.5rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 1.5rem;
    text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
  }
  
  .gradient-text {
    background: linear-gradient(90deg, #ffffff, #e0f2f1);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    display: inline-block;
  }
  
  .hero-content p {
    font-size: 1.2rem;
    line-height: 1.6;
    margin-bottom: 2rem;
    text-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
    color: rgba(255, 255, 255, 0.95);
  }
  
  .hero-buttons {
    display: flex;
    justify-content: center;
    gap: 1rem;
  }
  
  .hero-buttons .btn {
    padding: 0.8rem 1.8rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-radius: var(--radius-md);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    z-index: 1;
    border-width: 2px;
  }
  
  .hero-buttons .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 0%;
    height: 100%;
    background: rgba(255, 255, 255, 0.1);
    transition: width 0.4s ease;
    z-index: -1;
  }
  
  .hero-buttons .btn:hover::before {
    width: 100%;
  }
  
  .hero-buttons .btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
  }
  
  .hero-buttons .btn i {
    font-size: 1.2rem;
    vertical-align: middle;
    margin-right: 0.5rem;
    transition: transform 0.3s ease;
  }
  
  .hero-buttons .btn:hover i {
    transform: translateY(-2px);
  }
  
  .hero-buttons .btn-primary {
    background: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
  }
  
  .hero-buttons .btn-primary:hover {
    background: var(--primary-dark);
    border-color: var(--primary-dark);
  }
  
  .hero-buttons .btn-outline-light {
    border-color: rgba(255, 255, 255, 0.7);
    color: white;
  }
  
  .hero-buttons .btn-outline-light:hover {
    background: rgba(255, 255, 255, 0.1);
    color: white;
  }
  
  /* Dark mode enhancements */
  .dark-mode .hero-overlay {
    background: linear-gradient(135deg, rgba(13, 60, 40, 0.65), rgba(10, 50, 55, 0.65));
  }
  
  /* Responsive styles */
  @media (max-width: 991px) {
    .hero-content h1 {
      font-size: 2.8rem;
    }
    
    .hero-content p {
      font-size: 1.1rem;
    }
    
    .hero-buttons {
      flex-direction: column;
      gap: 0.75rem;
    }
    
    .hero-buttons .btn {
      width: 100%;
    }
  }
  
  @media (max-width: 768px) {
    .hero-content {
      padding: 2.5rem;
    }
    
    .hero-content h1 {
      font-size: 2.2rem;
    }
  }
  
  @media (max-width: 576px) {
    .hero-content {
      padding: 2rem 1.5rem;
    }
    
    .hero-content h1 {
      font-size: 1.8rem;
    }
    
    .hero-content p {
      font-size: 1rem;
      margin-bottom: 1.5rem;
    }
  }
</style>

<!-- Floating Stats -->
<section class="floating-stats">
  <div class="container">
    <div class="stats-container" data-aos="fade-up" data-aos-delay="200">
      <div class="row g-0">
        <!-- Hutan Produksi -->
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

        <!-- Hutan Lindung -->
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

        <!-- Hutan Konservasi -->
        <div class="col-md-3">
          <div class="stat-item">
            <div class="stat-icon">
              <i class="ri-leaf-line"></i>
            </div>
            <div class="stat-content">
              <h3 class="counter"><?php echo number_format($forest_data['data'][2] ?? 0); ?></h3>
              <p>Hektar Hutan Konservasi</p>
            </div>
          </div>
        </div>

        <!-- Wilayah Kerja -->
        <div class="col-md-3">
          <div class="stat-item">
            <div class="stat-icon">
              <i class="ri-government-line"></i>
            </div>
            <div class="stat-content">
              <h3 class="counter"><?php echo $work_areas_count; ?></h3>
              <p>Wilayah Kerja</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Wilayah Kerja Section -->
<section class="work-areas-section py-5">
  <div class="container">
    <div class="section-header text-center mb-5" data-aos="fade-up">
      <h2>Wilayah Kerja</h2>
      <p>Cabang Dinas Kehutanan Wilayah Bojonegoro mencakup beberapa kabupaten di Jawa Timur</p>
    </div>

    <div class="row mb-5">
      <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-up">
        <div class="map-container">
          <div id="east-java-map"
            style="height: 400px; width: 100%; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1);"></div>
        </div>
      </div>
      <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
        <div class="card h-100">
          <div class="card-body">
            <h4 class="card-title mb-4">Area Pengelolaan Hutan</h4>
            <p>Cabang Dinas Kehutanan Wilayah Bojonegoro bertanggung jawab untuk mengelola kawasan hutan di 6 wilayah
              kabupaten/kota di Jawa Timur, meliputi:</p>
            <ul class="list-group list-group-flush">
              <?php
              // Ambil data wilayah kerja menggunakan fungsi yang sudah ada
              $work_areas = getWorkAreas();
              foreach ($work_areas as $area):
                ?>
                <li class="list-group-item d-flex align-items-center">
                  <i class="ri-map-pin-line me-3 text-success"></i>
                  <span><?php echo htmlspecialchars($area['name']); ?></span>
                </li>
              <?php endforeach; ?>
            </ul>
            <p class="mt-4">
              Total luas kawasan hutan yang dikelola mencapai ribuan hektar, terdiri dari hutan produksi,
              hutan lindung, dan hutan rakyat yang tersebar di berbagai kabupaten.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Script untuk peta -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Check if Leaflet is available
    if (typeof L === 'undefined') {
        mapContainer.innerHTML = '<div class="alert alert-warning">Peta tidak dapat dimuat. Leaflet.js tidak tersedia.</div>';
        return;
    }

    // Inisialisasi peta menggunakan Leaflet.js (pastikan sudah include library Leaflet di header)
    if (typeof L !== 'undefined') {
      // Koordinat pusat Jawa Timur
      var map = L.map('east-java-map').setView([-7.5360639, 112.2384017], 8);

      // Tambahkan tile layer OpenStreetMap
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
      }).addTo(map);

      // Data koordinat kabupaten/kota
      var areas = [
        { name: 'Kabupaten Bojonegoro', lat: -7.1526263, lng: 111.8799083 },
        { name: 'Kabupaten Tuban', lat: -6.8949442, lng: 112.0464015 },
        { name: 'Kabupaten Lamongan', lat: -7.1191689, lng: 112.4160563 },
        { name: 'Kabupaten Gresik', lat: -7.1545329, lng: 112.6524563 },
        { name: 'Kabupaten Sidoarjo', lat: -7.4459197, lng: 112.6680983 },
        { name: 'Kota Surabaya', lat: -7.2574719, lng: 112.7520883 }
      ];

      // Tambahkan marker untuk setiap kabupaten/kota
      areas.forEach(function (area) {
        var marker = L.marker([area.lat, area.lng]).addTo(map);
        marker.bindPopup("<b>" + area.name + "</b><br>Area pengelolaan CDK Wilayah Bojonegoro");
      });

      // Tambahkan polygon untuk area kerja (contoh: area Bojonegoro)
      var workAreaPolygon = L.polygon([
        [-7.0, 111.7],
        [-7.3, 111.7],
        [-7.3, 112.0],
        [-7.0, 112.0]
      ], {
        color: 'green',
        fillColor: '#76b852',
        fillOpacity: 0.3
      }).addTo(map);
      workAreaPolygon.bindPopup("Kawasan Hutan CDK Wilayah Bojonegoro");
    }
  });
</script>

<!-- Script untuk mengaktifkan counter -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Animasi counter
    const counters = document.querySelectorAll('.counter');
    counters.forEach(counter => {
      const target = parseInt(counter.textContent.replace(/,/g, ''));
      let count = 0;
      const increment = Math.ceil(target / 100);

      const updateCount = () => {
        if (count < target) {
          count += increment;
          if (count > target) count = target;
          counter.innerText = count.toLocaleString();
          setTimeout(updateCount, 10);
        }
      };

      updateCount();
    });
  });
</script>
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
  /* Floating Stats Section */
  .floating-stats {
    margin-top: -70px;
    position: relative;
    z-index: 10;
  }

  .stats-container {
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    overflow: hidden;
  }

  .stat-item {
    padding: 30px 20px;
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
    border-right: 1px solid rgba(0, 0, 0, 0.05);
  }

  .stat-item:hover {
    background-color: #f9f9f9;
    transform: translateY(-5px);
  }

  .stat-icon {
    margin-bottom: 15px;
    display: inline-block;
  }

  .stat-icon i {
    font-size: 2.5rem;
    color: #2E8B57;
    /* Warna hijau hutan */
    background: rgba(46, 139, 87, 0.1);
    width: 70px;
    height: 70px;
    line-height: 70px;
    border-radius: 50%;
    display: inline-block;
    transition: all 0.3s ease;
  }

  .stat-item:hover .stat-icon i {
    background: #2E8B57;
    color: white;
    transform: scale(1.1);
  }

  .stat-content h3 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 5px;
    color: #333;
  }

  .stat-content p {
    font-size: 16px;
    color: #666;
    margin-bottom: 0;
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

    .stat-icon i {
      width: 60px;
      height: 60px;
      line-height: 60px;
      font-size: 2rem;
    }

    .stat-content h3 {
      font-size: 24px;
    }
  }
</style>
<!-- Hero Section -->
<section class="hero-section" id="hero">
  <div class="hero-video-bg">
    <video autoplay muted loop>
      <source src="assets/videos/<?php echo htmlspecialchars($hero_content['background_video']); ?>" type="video/mp4">
    </video>
  </div>
  <div class="hero-overlay"></div>
  <div id="particles-js"></div>

  <div class="hero-content-wrapper">
    <div class="hero-content" data-aos="fade-up">
      <h1 class="text-white mb-4"><?php echo htmlspecialchars($hero_content['title']); ?></h1>
      <p class="text-white mb-4">
        <?php echo htmlspecialchars($hero_content['subtitle']); ?>
      </p>
      <div class="hero-buttons">
        <a href="<?php echo htmlspecialchars($hero_content['button1_link']); ?>"
          class="btn btn-light btn-lg me-3"><?php echo htmlspecialchars($hero_content['button1_text']); ?></a>
        <a href="<?php echo htmlspecialchars($hero_content['button2_link']); ?>"
          class="btn btn-outline-light btn-lg"><?php echo htmlspecialchars($hero_content['button2_text']); ?></a>
      </div>
    </div>
  </div>
</section>

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
    } else {
      console.error('Leaflet library not found. Please include Leaflet.js');
      document.getElementById('east-java-map').innerHTML = '<div class="alert alert-warning">Peta tidak dapat dimuat. Pastikan koneksi internet Anda aktif.</div>';
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
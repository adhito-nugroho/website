<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
  http_response_code(403);
  exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Ambil data wilayah kerja
$work_areas = [];
try {
  $stmt = $pdo->query("SELECT * FROM work_areas WHERE is_active = 1 ORDER BY name");
  $work_areas = $stmt->fetchAll();
} catch (PDOException $e) {
  error_log('Error getting work areas: ' . $e->getMessage());
}

// Pastikan $work_areas adalah array
if (!is_array($work_areas)) {
  $work_areas = [];
}
?>

<!-- Profil Section -->
<section id="profil" class="profile-section">
  <div class="container">
    <div class="section-header" data-aos="fade-up">
      <h2>Profil Kami</h2>
      <p class="section-subheading">
        Mengenal lebih dekat Cabang Dinas Kehutanan Wilayah Bojonegoro
      </p>
    </div>

    <div class="row align-items-center">
      <div class="col-lg-6" data-aos="fade-right">
        <div class="profile-image">
          <img src="assets/images/kantor-cdk.jpg" alt="Kantor CDK Bojonegoro" class="img-fluid rounded-lg shadow-lg">
        </div>
      </div>
      <div class="col-lg-6" data-aos="fade-left">
        <div class="profile-content">
          <h3>Cabang Dinas Kehutanan Wilayah Bojonegoro</h3>
          <p class="lead">
            Unit Pelaksana Teknis Dinas Kehutanan Provinsi Jawa Timur yang melaksanakan kebijakan teknis operasional di
            bidang kehutanan.
          </p>
          <p>
            CDK Wilayah Bojonegoro memiliki tugas melaksanakan sebagian tugas Dinas Kehutanan Provinsi Jawa Timur di
            bidang kehutanan meliputi perencanaan, pemanfaatan, rehabilitasi, perlindungan, konservasi, dan pemberdayaan
            masyarakat di wilayah kerjanya.
          </p>

          <div class="profile-features">
            <div class="feature-item">
              <i class="ri-check-double-line"></i>
              <span>Perencanaan dan tata hutan</span>
            </div>
            <div class="feature-item">
              <i class="ri-check-double-line"></i>
              <span>Pemanfaatan dan penggunaan kawasan hutan</span>
            </div>
            <div class="feature-item">
              <i class="ri-check-double-line"></i>
              <span>Rehabilitasi hutan dan lahan</span>
            </div>
            <div class="feature-item">
              <i class="ri-check-double-line"></i>
              <span>Perlindungan dan pengamanan hutan</span>
            </div>
            <div class="feature-item">
              <i class="ri-check-double-line"></i>
              <span>Pemberdayaan masyarakat sekitar hutan</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
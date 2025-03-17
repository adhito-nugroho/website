<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Ambil data layanan dari database
$services = getServices();

// Pastikan $services adalah array
if (!is_array($services)) {
    $services = [];
}
?>

<!-- Layanan Section -->
<section id="layanan" class="services-section">
  <div class="container">
    <div class="section-header text-center" data-aos="fade-up">
      <h2>Layanan Kehutanan</h2>
      <p class="section-subheading">
        Pelayanan teknis bidang kehutanan sesuai wilayah kerja
      </p>
    </div>

    <div class="row g-4">
      <?php if (count($services) > 0): ?>
        <?php foreach ($services as $service): ?>
          <!-- Layanan: <?php echo htmlspecialchars($service['title']); ?> -->
          <div class="col-lg-4" data-aos="fade-up">
            <div class="service-card glass-card" id="layanan-<?php echo $service['id']; ?>">
              <div class="service-icon-wrapper">
                <i class="<?php echo htmlspecialchars($service['icon']); ?> service-icon"></i>
              </div>
              <div class="service-content">
                <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                <?php echo $service['content']; ?>
                <div class="service-action">
                  <a href="#" class="btn btn-success mt-3">Ajukan Permohonan</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center">
          <p>Belum ada data layanan.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Ambil data program dari database
$programs = getPrograms();

// Pastikan $programs adalah array
if (!is_array($programs)) {
    $programs = [];
}
?>

<section id="program" class="program-section">
  <div class="container">
    <div class="section-header text-center" data-aos="fade-up">
      <h2>Program & Kegiatan</h2>
      <p class="section-subheading">Program dan kegiatan teknis bidang kehutanan sesuai Pergub No 48 Tahun 2018</p>
    </div>
    
    <div class="program-grid">
      <?php if (count($programs) > 0): ?>
        <?php foreach ($programs as $index => $program): ?>
          <!-- Program: <?php echo htmlspecialchars($program['title']); ?> -->
          <div class="program-card" data-aos="fade-up" <?php echo ($index > 0) ? 'data-aos-delay="' . ($index * 100) . '"' : ''; ?>>
            <div class="program-header">
              <div class="program-icon">
                <i class="<?php echo htmlspecialchars($program['icon']); ?>"></i>
              </div>
              <div class="program-title">
                <h4><?php echo htmlspecialchars($program['title']); ?></h4>
                <p><?php echo htmlspecialchars($program['description']); ?></p>
              </div>
            </div>
            <div class="program-content">
              <?php echo $program['content']; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="text-center">
          <p>Belum ada data program.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

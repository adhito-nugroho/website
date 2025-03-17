<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Ambil data capaian program
$achievements = getAchievements();

// Pastikan $achievements adalah array
if (!is_array($achievements)) {
    $achievements = [];
}

// Jika tidak ada data, buat data contoh
if (empty($achievements)) {
    // Data contoh untuk ditampilkan
    $achievements = [
        [
            'id' => 1,
            'title' => 'Rehabilitasi Hutan',
            'description' => 'Program rehabilitasi hutan dan lahan kritis',
            'percentage' => 85,
            'year' => date('Y'),
            'icon' => 'ri-plant-line'
        ],
        [
            'id' => 2,
            'title' => 'Perhutanan Sosial',
            'description' => 'Program pemberdayaan masyarakat sekitar hutan',
            'percentage' => 70,
            'year' => date('Y'),
            'icon' => 'ri-team-line'
        ],
        [
            'id' => 3,
            'title' => 'Perlindungan Hutan',
            'description' => 'Program perlindungan dan pengamanan kawasan hutan',
            'percentage' => 90,
            'year' => date('Y'),
            'icon' => 'ri-shield-line'
        ],
        [
            'id' => 4,
            'title' => 'Produksi Hasil Hutan',
            'description' => 'Program peningkatan produksi hasil hutan',
            'percentage' => 65,
            'year' => date('Y'),
            'icon' => 'ri-leaf-line'
        ]
    ];
    
    // Simpan data contoh ke database jika memungkinkan
    try {
        // Buat tabel jika belum ada
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS achievements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                percentage INT NOT NULL,
                year INT NOT NULL,
                icon VARCHAR(100) DEFAULT 'ri-award-line',
                is_active TINYINT(1) DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");
        
        // Tambahkan data contoh
        $stmt = $pdo->prepare("
            INSERT INTO achievements (title, description, percentage, year, icon, is_active) 
            VALUES (?, ?, ?, ?, ?, 1)
        ");
        
        foreach ($achievements as $data) {
            $stmt->execute([
                $data['title'],
                $data['description'],
                $data['percentage'],
                $data['year'],
                $data['icon']
            ]);
        }
    } catch (PDOException $e) {
        error_log('Error creating sample achievements: ' . $e->getMessage());
    }
}

// Siapkan data untuk chart
$achievement_labels = [];
$achievement_data = [];
foreach ($achievements as $achievement) {
    $achievement_labels[] = $achievement['title'] ?? 'Program';
    $achievement_data[] = $achievement['percentage'] ?? 0;
}

// Ambil tahun capaian
$current_year = isset($_GET['year']) ? (int)$_GET['year'] : (isset($achievements[0]['year']) ? $achievements[0]['year'] : date('Y'));

// Ambil tahun-tahun yang tersedia untuk dropdown
$available_years = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT year FROM achievements ORDER BY year DESC");
    while ($row = $stmt->fetch()) {
        $available_years[] = $row['year'];
    }
} catch (PDOException $e) {
    error_log('Error getting achievement years: ' . $e->getMessage());
}

// Jika tidak ada tahun yang tersedia, tambahkan tahun saat ini
if (empty($available_years)) {
    $available_years[] = date('Y');
}
?>

<!-- Monitoring Section -->
<section id="monitoring" class="monitoring-section">
  <div class="container">
    <div class="section-header" data-aos="fade-up">
      <h2>Monitoring & Evaluasi</h2>
      <p class="section-subheading">
        Capaian program dan kegiatan CDK Wilayah Bojonegoro
      </p>
    </div>

    <!-- Filter Tahun -->
    <div class="monitoring-filter mb-5" data-aos="fade-up">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
          <div class="input-group">
            <label class="input-group-text" for="achievementYear">Tahun</label>
            <select class="form-select" id="achievementYear">
              <?php foreach ($available_years as $year): ?>
                <option value="<?php echo $year; ?>" <?php echo ($year == $current_year) ? 'selected' : ''; ?>>
                  <?php echo $year; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="button" id="loadAchievements">Tampilkan</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Achievement Cards -->
    <div class="row">
      <?php if (count($achievements) > 0): ?>
        <?php foreach ($achievements as $achievement): ?>
          <div class="col-md-6 col-lg-3 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $loop * 100; ?>">
            <div class="achievement-card">
              <div class="achievement-icon">
                <i class="<?php echo htmlspecialchars($achievement['icon']); ?>"></i>
              </div>
              <h4><?php echo htmlspecialchars($achievement['title']); ?></h4>
              <div class="progress-container">
                <div class="progress">
                  <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $achievement['percentage']; ?>%;" 
                       aria-valuenow="<?php echo $achievement['percentage']; ?>" aria-valuemin="0" aria-valuemax="100">
                    <?php echo $achievement['percentage']; ?>%
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center">
          <p>Belum ada data capaian program untuk tahun <?php echo $current_year; ?>.</p>
        </div>
      <?php endif; ?>
    </div>

    <!-- Achievement Chart -->
    <div class="row mt-5">
      <div class="col-12" data-aos="fade-up">
        <div class="monitoring-card">
          <div class="card-header">
            <h4>Grafik Capaian Program Tahun <?php echo $current_year; ?></h4>
          </div>
          <div class="card-body">
            <canvas id="achievementChart" height="300" width="100%"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Monitoring Description -->
    <div class="row mt-5">
      <div class="col-12" data-aos="fade-up">
        <div class="monitoring-description">
          <h4>Tentang Monitoring & Evaluasi</h4>
          <p>
            Monitoring dan evaluasi merupakan bagian penting dalam pengelolaan program kehutanan untuk memastikan 
            pencapaian target dan tujuan yang telah ditetapkan. CDK Wilayah Bojonegoro secara berkala melakukan 
            monitoring dan evaluasi terhadap program-program yang dilaksanakan.
          </p>
          <p>
            Capaian program diukur berdasarkan indikator kinerja yang telah ditetapkan dalam rencana strategis 
            dan rencana kerja tahunan. Hasil monitoring dan evaluasi digunakan sebagai bahan perbaikan dan 
            peningkatan kinerja program di masa mendatang.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Script untuk inisialisasi chart -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  try {
    // Memastikan Chart.js tersedia
    if (typeof Chart === 'undefined') {
      console.error('Chart.js is not loaded!');
      return;
    }
    
    // Data untuk chart capaian program
    const achievementData = {
      labels: <?php echo json_encode($achievement_labels); ?>,
      datasets: [{
        label: 'Persentase Capaian (%)',
        data: <?php echo json_encode($achievement_data); ?>,
        backgroundColor: [
          '#2d6a4f', '#40916c', '#52b788', '#74c69d'
        ],
        borderWidth: 0
      }]
    };
    
    console.log('Achievement data prepared:', achievementData);
    
    // Menyimpan data ke variabel global untuk digunakan oleh main.js
    window.achievementChartData = achievementData;
    
    // Memicu event untuk memberitahu main.js bahwa data chart sudah siap
    console.log('Dispatching monitoringChartDataReady event');
    document.dispatchEvent(new CustomEvent('monitoringChartDataReady'));
  
    // Event listener untuk filter tahun
    const loadAchievementsBtn = document.getElementById('loadAchievements');
    if (loadAchievementsBtn) {
      loadAchievementsBtn.addEventListener('click', function() {
        const year = document.getElementById('achievementYear').value;
        window.location.href = `?page=monitoring&year=${year}`;
      });
    }
  } catch (error) {
    console.error('Error in monitoring.php script:', error);
  }
});
</script>

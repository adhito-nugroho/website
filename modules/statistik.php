<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Ambil data statistik
$forest_area = getStatistics('forest-area');
$forest_production = getStatistics('forest-production');

// Decode JSON data
$forest_area_data = [];
$forest_production_data = [];

if ($forest_area && isset($forest_area['data_json'])) {
    $forest_area_data = json_decode($forest_area['data_json'], true);
}

if ($forest_production && isset($forest_production['data_json'])) {
    $forest_production_data = json_decode($forest_production['data_json'], true);
}

// Ambil tahun statistik
$current_year = $forest_area['year'] ?? date('Y');

// Ambil tahun-tahun yang tersedia untuk dropdown
$available_years = [];
try {
    $stmt = $pdo->query("SELECT DISTINCT year FROM statistics ORDER BY year DESC");
    while ($row = $stmt->fetch()) {
        $available_years[] = $row['year'];
    }
} catch (PDOException $e) {
    error_log('Error getting statistics years: ' . $e->getMessage());
}
?>

<!-- Statistik Section -->
<section id="statistik" class="statistics-section">
  <div class="container">
    <div class="section-header" data-aos="fade-up">
      <h2>Data & Statistik</h2>
      <p class="section-subheading">
        Informasi statistik kehutanan di wilayah kerja CDK Bojonegoro
      </p>
    </div>

    <!-- Filter Tahun -->
    <div class="statistics-filter mb-5" data-aos="fade-up">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
          <div class="input-group">
            <label class="input-group-text" for="statisticYear">Tahun</label>
            <select class="form-select" id="statisticYear">
              <?php foreach ($available_years as $year): ?>
                <option value="<?php echo $year; ?>" <?php echo ($year == $current_year) ? 'selected' : ''; ?>>
                  <?php echo $year; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="button" id="loadStatistics">Tampilkan</button>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Luas Kawasan Hutan -->
      <div class="col-lg-6" data-aos="fade-up">
        <div class="statistic-card">
          <div class="card-header">
            <h4>Luas Kawasan Hutan (Ha)</h4>
            <div class="card-tools">
              <button class="btn btn-sm btn-outline-secondary" data-chart-type="bar" data-target="forestAreaChart">
                <i class="ri-bar-chart-horizontal-line"></i>
              </button>
              <button class="btn btn-sm btn-outline-secondary active" data-chart-type="pie" data-target="forestAreaChart">
                <i class="ri-pie-chart-line"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <canvas id="forestAreaChart" height="300"></canvas>
          </div>
        </div>
      </div>

      <!-- Produksi Hasil Hutan -->
      <div class="col-lg-6" data-aos="fade-up" data-aos-delay="100">
        <div class="statistic-card">
          <div class="card-header">
            <h4>Produksi Hasil Hutan</h4>
            <div class="card-tools">
              <button class="btn btn-sm btn-outline-secondary active" data-chart-type="bar" data-target="forestProductionChart">
                <i class="ri-bar-chart-horizontal-line"></i>
              </button>
              <button class="btn btn-sm btn-outline-secondary" data-chart-type="pie" data-target="forestProductionChart">
                <i class="ri-pie-chart-line"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <canvas id="forestProductionChart" height="300"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabel Data -->
    <div class="row mt-5">
      <div class="col-12" data-aos="fade-up">
        <div class="statistic-card">
          <div class="card-header">
            <h4>Tabel Data Statistik</h4>
            <div class="card-tools">
              <button class="btn btn-sm btn-success" id="exportExcel">
                <i class="ri-file-excel-line me-1"></i> Export Excel
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>Kategori</th>
                    <th>Jenis</th>
                    <th>Nilai</th>
                    <th>Satuan</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (isset($forest_area_data['labels']) && isset($forest_area_data['data'])): ?>
                    <?php foreach ($forest_area_data['labels'] as $index => $label): ?>
                      <tr>
                        <td>Luas Kawasan Hutan</td>
                        <td><?php echo htmlspecialchars($label); ?></td>
                        <td><?php echo number_format($forest_area_data['data'][$index] ?? 0); ?></td>
                        <td>Hektar</td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                  
                  <?php if (isset($forest_production_data['labels']) && isset($forest_production_data['data'])): ?>
                    <?php foreach ($forest_production_data['labels'] as $index => $label): ?>
                      <tr>
                        <td>Produksi Hasil Hutan</td>
                        <td><?php echo htmlspecialchars($label); ?></td>
                        <td><?php echo number_format($forest_production_data['data'][$index] ?? 0); ?></td>
                        <td><?php echo ($index <= 1) ? 'Meter Kubik' : 'Ton'; ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Script untuk inisialisasi chart -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Data untuk chart
  const forestAreaData = <?php echo $forest_area['data_json'] ?? '{}'; ?>;
  const forestProductionData = <?php echo $forest_production['data_json'] ?? '{}'; ?>;
  
  // Menyimpan data ke variabel global untuk digunakan oleh main.js
  window.forestAreaChartData = {
    labels: forestAreaData.labels || ['Hutan Produksi', 'Hutan Lindung', 'Hutan Rakyat', 'Hutan Kota'],
    data: forestAreaData.data || [45000, 25000, 15000, 5000]
  };
  
  window.forestProductionChartData = {
    labels: forestProductionData.labels || ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
    data: forestProductionData.data || [1200, 1900, 1500, 1800, 2200, 1600]
  };
  
  // Memicu event untuk memberitahu main.js bahwa data chart sudah siap
  document.dispatchEvent(new CustomEvent('chartDataReady'));
});
</script>

<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Ambil tahun dari query string atau gunakan tahun saat ini
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Ambil data statistik berdasarkan kategori dan tahun
function getStatisticsByCategory($category, $year) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM statistics WHERE category = ? AND year = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$category, $year]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log('Error getting statistics: ' . $e->getMessage());
        return null;
    }
}

// Ambil data statistik untuk kategori yang berbeda
$forest_area = getStatisticsByCategory('forest-area', $selected_year);
$forest_production = getStatisticsByCategory('forest-production', $selected_year);
$rehabilitation = getStatisticsByCategory('rehabilitation', $selected_year);
$social_forestry = getStatisticsByCategory('social-forestry', $selected_year);

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

// Jika tidak ada tahun yang tersedia, tambahkan tahun saat ini
if (empty($available_years)) {
    $available_years[] = date('Y');
}

// Jika tahun yang dipilih tidak ada dalam daftar, gunakan tahun pertama dari daftar
if (!in_array($selected_year, $available_years)) {
    $selected_year = $available_years[0];
}

// Fungsi untuk mendapatkan semua statistik berdasarkan tahun
function getAllStatisticsByYear($year) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT * FROM statistics WHERE year = ? ORDER BY category");
        $stmt->execute([$year]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error getting all statistics: ' . $e->getMessage());
        return [];
    }
}

// Ambil semua statistik untuk tahun yang dipilih
$all_statistics = getAllStatisticsByYear($selected_year);

// Format label kategori
$category_labels = [
    'forest-area' => 'Luas Kawasan Hutan',
    'forest-production' => 'Produksi Hasil Hutan',
    'rehabilitation' => 'Rehabilitasi Hutan',
    'social-forestry' => 'Perhutanan Sosial',
    'forest-fire' => 'Kebakaran Hutan',
    'other' => 'Lainnya'
];
?>

<!-- Statistik Section -->
<section id="statistik" class="statistics-section py-5">
  <div class="container">
    <div class="section-header text-center mb-5" data-aos="fade-up">
      <h2>Data & Statistik</h2>
      <p class="section-subheading">
        Informasi statistik kehutanan di wilayah kerja CDK Bojonegoro
      </p>
    </div>

    <!-- Filter Tahun -->
    <div class="statistics-filter mb-5" data-aos="fade-up">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
          <form action="index.php" method="get" class="input-group">
            <input type="hidden" name="page" value="statistik">
            <label class="input-group-text" for="statisticYear">Tahun</label>
            <select class="form-select" id="statisticYear" name="year" onchange="this.form.submit()">
              <?php foreach ($available_years as $year): ?>
                <option value="<?php echo $year; ?>" <?php echo ($year == $selected_year) ? 'selected' : ''; ?>>
                  <?php echo $year; ?>
                </option>
              <?php endforeach; ?>
            </select>
            <noscript>
              <button class="btn btn-primary" type="submit">Tampilkan</button>
            </noscript>
          </form>
        </div>
      </div>
    </div>

    <div class="row">
      <?php
      // Tampilkan grafik untuk setiap kategori statistik yang tersedia
      $chart_count = 0;
      foreach ($all_statistics as $index => $statistic):
        // Decode JSON data
        $chart_data = json_decode($statistic['data_json'], true);
        if (!$chart_data || !isset($chart_data['labels']) || !isset($chart_data['data'])) {
          continue;
        }
        
        $chart_id = 'chart_' . $statistic['id'];
        $chart_count++;
        $delay = ($chart_count - 1) * 100;
      ?>
      <div class="col-lg-6 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $delay; ?>">
        <div class="statistic-card h-100">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4><?php echo htmlspecialchars($statistic['title']); ?></h4>
            <div class="card-tools">
              <button class="btn btn-sm btn-outline-secondary active" data-chart-type="bar" data-target="<?php echo $chart_id; ?>">
                <i class="ri-bar-chart-horizontal-line"></i>
              </button>
              <button class="btn btn-sm btn-outline-secondary" data-chart-type="pie" data-target="<?php echo $chart_id; ?>">
                <i class="ri-pie-chart-line"></i>
              </button>
            </div>
          </div>
          <div class="card-body">
            <canvas id="<?php echo $chart_id; ?>" height="300"></canvas>
          </div>
          <div class="card-footer text-muted">
            <small>Kategori: <?php echo htmlspecialchars($category_labels[$statistic['category']] ?? $statistic['category']); ?></small>
            <?php if (!empty($statistic['unit'])): ?>
              <small class="ms-2">Satuan: <?php echo htmlspecialchars($statistic['unit']); ?></small>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      
      <?php if ($chart_count === 0): ?>
      <div class="col-12 text-center">
        <div class="alert alert-info">
          <i class="ri-information-line me-2"></i> Belum ada data statistik untuk tahun <?php echo $selected_year; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Tabel Data -->
    <?php if ($chart_count > 0): ?>
    <div class="row mt-5">
      <div class="col-12" data-aos="fade-up">
        <div class="statistic-card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4>Tabel Data Statistik Tahun <?php echo $selected_year; ?></h4>
            <div class="card-tools">
              <button class="btn btn-sm btn-success" id="exportExcel">
                <i class="ri-file-excel-line me-1"></i> Export Excel
              </button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover" id="statisticsTable">
                <thead>
                  <tr>
                    <th>Kategori</th>
                    <th>Judul</th>
                    <th>Jenis</th>
                    <th>Nilai</th>
                    <th>Satuan</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($all_statistics as $statistic): 
                    $data = json_decode($statistic['data_json'], true);
                    if (!$data || !isset($data['labels']) || !isset($data['data'])) {
                      continue;
                    }
                    
                    $category_name = $category_labels[$statistic['category']] ?? $statistic['category'];
                    $unit = htmlspecialchars($statistic['unit'] ?? '-');
                    
                    foreach ($data['labels'] as $i => $label):
                      if (!isset($data['data'][$i])) continue;
                  ?>
                  <tr>
                    <td><?php echo htmlspecialchars($category_name); ?></td>
                    <td><?php echo htmlspecialchars($statistic['title']); ?></td>
                    <td><?php echo htmlspecialchars($label); ?></td>
                    <td><?php echo number_format($data['data'][$i], 0, ',', '.'); ?></td>
                    <td><?php echo $unit; ?></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- Script untuk inisialisasi chart -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Data untuk chart
  const chartData = {
    <?php 
    foreach ($all_statistics as $statistic): 
      $data = json_decode($statistic['data_json'], true);
      if (!$data || !isset($data['labels']) || !isset($data['data'])) {
        continue;
      }
      $chart_id = 'chart_' . $statistic['id'];
      echo "'" . $chart_id . "': {";
      echo "labels: " . json_encode($data['labels']) . ",";
      echo "data: " . json_encode($data['data']) . ",";
      echo "title: '" . addslashes($statistic['title']) . "',";
      echo "unit: '" . addslashes($statistic['unit'] ?? '') . "'";
      echo "},";
    endforeach;
    ?>
  };
  
  // Inisialisasi chart
  const charts = {};
  
  for (const [chartId, data] of Object.entries(chartData)) {
    const ctx = document.getElementById(chartId);
    if (!ctx) continue;
    
    const chartTitle = data.title + (data.unit ? ` (${data.unit})` : '');
    
    charts[chartId] = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: data.labels,
        datasets: [{
          label: chartTitle,
          data: data.data,
          backgroundColor: [
            'rgba(75, 192, 192, 0.2)',
            'rgba(54, 162, 235, 0.2)',
            'rgba(255, 206, 86, 0.2)',
            'rgba(255, 99, 132, 0.2)',
            'rgba(153, 102, 255, 0.2)',
            'rgba(255, 159, 64, 0.2)'
          ],
          borderColor: [
            'rgba(75, 192, 192, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true
          }
        }
      }
    });
  }
  
  // Toggle chart type
  document.querySelectorAll('[data-chart-type]').forEach(button => {
    button.addEventListener('click', function() {
      const chartType = this.getAttribute('data-chart-type');
      const targetChart = this.getAttribute('data-target');
      
      // Update active button
      this.closest('.card-tools').querySelectorAll('button').forEach(btn => {
        btn.classList.remove('active');
      });
      this.classList.add('active');
      
      // Update chart type
      if (charts[targetChart]) {
        charts[targetChart].config.type = chartType;
        charts[targetChart].update();
      }
    });
  });
  
  // Export to Excel
  document.getElementById('exportExcel')?.addEventListener('click', function() {
    const table = document.getElementById('statisticsTable');
    if (!table) return;
    
    // Create workbook
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.table_to_sheet(table);
    
    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, 'Statistik <?php echo $selected_year; ?>');
    
    // Save file
    XLSX.writeFile(wb, 'Statistik_CDK_Bojonegoro_<?php echo $selected_year; ?>.xlsx');
  });
});
</script>

<!-- Load SheetJS for Excel export -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

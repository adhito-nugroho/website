<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
  http_response_code(403);
  exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Ambil filter tahun
$current_year = isset($_GET['year']) ? (int) $_GET['year'] : date('Y');
$current_month = isset($_GET['month']) ? (int) $_GET['month'] : date('n');

// Ambil data monitoring dari database
try {
  // Query untuk mengambil monitoring berdasarkan tahun
  $stmt = $pdo->prepare("
        SELECT m.*, u.name as created_by_name 
        FROM monitoring m 
        LEFT JOIN users u ON m.created_by = u.id 
        WHERE YEAR(m.date) = ? 
        ORDER BY m.date DESC
    ");
  $stmt->execute([$current_year]);
  $monitorings = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Jika tidak ada data, berikan array kosong
  if (!$monitorings) {
    $monitorings = [];
  }

  // Query untuk mengambil tahun-tahun yang tersedia
  $stmt = $pdo->query("SELECT DISTINCT YEAR(date) as year FROM monitoring ORDER BY year DESC");
  $available_years = $stmt->fetchAll(PDO::FETCH_COLUMN);

  // Jika tidak ada tahun, tampilkan tahun saat ini
  if (empty($available_years)) {
    $available_years = [date('Y')];
  }

  // Data untuk statistik
  $total_monitoring = count($monitorings);
  $monitoring_by_status = [
    'pending' => 0,
    'in_progress' => 0,
    'completed' => 0,
    'cancelled' => 0
  ];

  // Hitung jumlah monitoring berdasarkan status
  foreach ($monitorings as $monitoring) {
    if (isset($monitoring_by_status[$monitoring['status']])) {
      $monitoring_by_status[$monitoring['status']]++;
    }
  }

  // Data untuk chart (monitoring per bulan)
  $monitoring_months = [];
  $monitoring_counts = [];

  // Query untuk menghitung monitoring per bulan pada tahun tersebut
  $stmt = $pdo->prepare("
        SELECT MONTH(date) as month, COUNT(*) as count 
        FROM monitoring 
        WHERE YEAR(date) = ? 
        GROUP BY MONTH(date) 
        ORDER BY month ASC
    ");
  $stmt->execute([$current_year]);
  $monthly_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // Siapkan data untuk 12 bulan
  $month_names = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
  for ($i = 1; $i <= 12; $i++) {
    $monitoring_months[] = $month_names[$i - 1];
    $monitoring_counts[] = 0;
  }

  // Isi data actual
  foreach ($monthly_data as $data) {
    $month_index = $data['month'] - 1;
    $monitoring_counts[$month_index] = (int) $data['count'];
  }

  // Data untuk chart (status monitoring)
  $status_labels = ['Menunggu', 'Sedang Berjalan', 'Selesai', 'Dibatalkan'];
  $status_counts = [
    $monitoring_by_status['pending'],
    $monitoring_by_status['in_progress'],
    $monitoring_by_status['completed'],
    $monitoring_by_status['cancelled']
  ];
  $status_colors = ['#FFC107', '#17A2B8', '#28A745', '#DC3545'];

} catch (PDOException $e) {
  error_log('Error getting monitoring data: ' . $e->getMessage());
  $monitorings = [];
  $available_years = [date('Y')];
  $monitoring_months = $month_names;
  $monitoring_counts = array_fill(0, 12, 0);
  $status_labels = ['Menunggu', 'Sedang Berjalan', 'Selesai', 'Dibatalkan'];
  $status_counts = [0, 0, 0, 0];
  $status_colors = ['#FFC107', '#17A2B8', '#28A745', '#DC3545'];
}
?>

<!-- Monitoring Section -->
<section id="monitoring" class="monitoring-section">
  <div class="container">
    <div class="section-header" data-aos="fade-up">
      <h2>Monitoring & Evaluasi</h2>
      <p class="section-subheading">
        Data kegiatan monitoring dan evaluasi CDK Wilayah Bojonegoro
      </p>
    </div>

    <!-- Filter Tahun -->
    <div class="monitoring-filter mb-4" data-aos="fade-up">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
          <div class="input-group">
            <label class="input-group-text" for="monitoringYear">Tahun</label>
            <select class="form-select" id="monitoringYear">
              <?php foreach ($available_years as $year): ?>
                  <option value="<?php echo $year; ?>" <?php echo ($year == $current_year) ? 'selected' : ''; ?>>
                    <?php echo $year; ?>
                  </option>
              <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" type="button" id="loadMonitoring">Tampilkan</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Monitoring Stats Cards -->
    <div class="row mb-4">
      <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="100">
        <div class="stat-card bg-primary text-white">
          <div class="stat-icon">
            <i class="ri-file-list-3-line"></i>
          </div>
          <div class="stat-content">
            <h3><?php echo $total_monitoring; ?></h3>
            <p>Total Monitoring</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="200">
        <div class="stat-card bg-info text-white">
          <div class="stat-icon">
            <i class="ri-loader-line"></i>
          </div>
          <div class="stat-content">
            <h3><?php echo $monitoring_by_status['in_progress']; ?></h3>
            <p>Sedang Berjalan</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="300">
        <div class="stat-card bg-success text-white">
          <div class="stat-icon">
            <i class="ri-check-double-line"></i>
          </div>
          <div class="stat-content">
            <h3><?php echo $monitoring_by_status['completed']; ?></h3>
            <p>Selesai</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 mb-3" data-aos="fade-up" data-aos-delay="400">
        <div class="stat-card bg-warning text-dark">
          <div class="stat-icon">
            <i class="ri-time-line"></i>
          </div>
          <div class="stat-content">
            <h3><?php echo $monitoring_by_status['pending']; ?></h3>
            <p>Menunggu</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Monitoring Charts -->
    <div class="row mb-4">
      <div class="col-md-8 mb-4" data-aos="fade-up">
        <div class="monitoring-card">
          <div class="card-header">
            <h4>Jumlah Monitoring Per Bulan (<?php echo $current_year; ?>)</h4>
          </div>
          <div class="card-body">
            <canvas id="monthlyMonitoringChart" height="300"></canvas>
          </div>
        </div>
      </div>
      <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="200">
        <div class="monitoring-card">
          <div class="card-header">
            <h4>Status Monitoring</h4>
          </div>
          <div class="card-body">
            <canvas id="statusMonitoringChart" height="300"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Monitoring Table -->
    <div class="row" data-aos="fade-up">
      <div class="col-12">
        <div class="monitoring-card">
          <div class="card-header">
            <h4>Daftar Kegiatan Monitoring Tahun <?php echo $current_year; ?></h4>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Lokasi</th>
                    <th>Kegiatan</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($monitorings) > 0): ?>
                      <?php foreach ($monitorings as $index => $monitoring): ?>
                          <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($monitoring['date'])); ?></td>
                            <td><?php echo htmlspecialchars($monitoring['location']); ?></td>
                            <td><?php echo htmlspecialchars($monitoring['activity']); ?></td>
                            <td>
                              <?php
                              $status_class = '';
                              switch ($monitoring['status']) {
                                case 'pending':
                                  $status_class = 'bg-warning text-dark';
                                  $status_text = 'Menunggu';
                                  break;
                                case 'in_progress':
                                  $status_class = 'bg-info text-white';
                                  $status_text = 'Sedang Berjalan';
                                  break;
                                case 'completed':
                                  $status_class = 'bg-success text-white';
                                  $status_text = 'Selesai';
                                  break;
                                case 'cancelled':
                                  $status_class = 'bg-danger text-white';
                                  $status_text = 'Dibatalkan';
                                  break;
                                default:
                                  $status_text = ucfirst($monitoring['status']);
                              }
                              ?>
                              <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                            </td>
                          </tr>
                      <?php endforeach; ?>
                  <?php else: ?>
                      <tr>
                        <td colspan="5" class="text-center">Belum ada data monitoring untuk tahun <?php echo $current_year; ?>.</td>
                      </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
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
            Kegiatan monitoring meliputi pemantauan langsung ke lapangan, pengumpulan data dan informasi, serta 
            evaluasi pelaksanaan program. Hasil monitoring digunakan sebagai dasar untuk perbaikan program 
            dan pengambilan keputusan di masa mendatang.
          </p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CSS Tambahan -->
<style>
  .stat-card {
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    transition: transform 0.3s ease;
  }
  
  .stat-card:hover {
    transform: translateY(-5px);
  }
  
  .stat-icon {
    font-size: 2.5rem;
    margin-right: 15px;
  }
  
  .stat-content h3 {
    font-size: 1.8rem;
    margin-bottom: 0;
    font-weight: 700;
  }
  
  .stat-content p {
    margin-bottom: 0;
    opacity: 0.9;
  }
  
  .monitoring-card {
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    overflow: hidden;
  }
  
  .monitoring-card .card-header {
    background-color: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
  }
  
  .monitoring-card .card-header h4 {
    margin-bottom: 0;
    font-weight: 600;
  }
  
  .monitoring-card .card-body {
    padding: 20px;
  }
  
  .monitoring-description {
    background-color: #f8f9fa;
    padding: 25px;
    border-radius: 8px;
    margin-top: 20px;
  }
  
  .monitoring-description h4 {
    font-weight: 600;
    margin-bottom: 15px;
  }
  
  .monitoring-description p {
    margin-bottom: 15px;
    line-height: 1.6;
  }
  
  .table th {
    font-weight: 600;
  }
</style>

<!-- Script untuk chart -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  try {
    // Memastikan Chart.js tersedia
    if (typeof Chart === 'undefined') {
      console.error('Chart.js is not loaded!');
      loadChartJsScript();
      return;
    }
    
    // Buat chart bulanan
    createMonthlyChart();
    
    // Buat chart status
    createStatusChart();
    
    // Event listener untuk filter tahun
    const loadMonitoringBtn = document.getElementById('loadMonitoring');
    if (loadMonitoringBtn) {
      loadMonitoringBtn.addEventListener('click', function() {
        const year = document.getElementById('monitoringYear').value;
        window.location.href = `?page=monitoring&year=${year}`;
      });
    }
  } catch (error) {
    console.error('Error in monitoring charts script:', error);
  }
  
  // Fungsi untuk memuat Chart.js jika belum tersedia
  function loadChartJsScript() {
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
    script.onload = function() {
      createMonthlyChart();
      createStatusChart();
    };
    document.head.appendChild(script);
  }
  
  // Fungsi untuk membuat chart bulanan
  function createMonthlyChart() {
    const ctx = document.getElementById('monthlyMonitoringChart').getContext('2d');
    
    // Data untuk chart bulanan
    const monthlyData = {
      labels: <?php echo json_encode($monitoring_months); ?>,
      datasets: [{
        label: 'Jumlah Kegiatan',
        data: <?php echo json_encode($monitoring_counts); ?>,
        backgroundColor: 'rgba(54, 162, 235, 0.7)',
        borderColor: 'rgba(54, 162, 235, 1)',
        borderWidth: 1
      }]
    };
    
    // Buat chart
    new Chart(ctx, {
      type: 'bar',
      data: monthlyData,
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: true,
            position: 'top'
          },
          tooltip: {
            mode: 'index',
            intersect: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
              precision: 0
            }
          }
        }
      }
    });
  }
  
  // Fungsi untuk membuat chart status
  function createStatusChart() {
    const ctx = document.getElementById('statusMonitoringChart').getContext('2d');
    
    // Data untuk chart status
    const statusData = {
      labels: <?php echo json_encode($status_labels); ?>,
      datasets: [{
        data: <?php echo json_encode($status_counts); ?>,
        backgroundColor: <?php echo json_encode($status_colors); ?>,
        borderWidth: 0
      }]
    };
    
    // Buat chart
    new Chart(ctx, {
      type: 'doughnut',
      data: statusData,
      options: {
        responsive: true,
        plugins: {
          legend: {
            position: 'bottom'
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                const label = context.label || '';
                const value = context.raw || 0;
                const total = context.chart.data.datasets[0].data.reduce((a, b) => a + b, 0);
                const percentage = Math.round((value / total) * 100);
                return `${label}: ${value} (${percentage}%)`;
              }
            }
          }
        },
        cutout: '65%'
      }
    });
  }
});
</script>
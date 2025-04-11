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

  // Data untuk chart (status monitoring) - Gunakan warna yang lebih lembut
  $status_labels = ['Menunggu', 'Sedang Berjalan', 'Selesai', 'Dibatalkan'];
  $status_counts = [
    $monitoring_by_status['pending'],
    $monitoring_by_status['in_progress'],
    $monitoring_by_status['completed'],
    $monitoring_by_status['cancelled']
  ];
  $status_colors = [
    'rgba(255, 193, 7, 0.8)',   // Kuning (Menunggu)
    'rgba(23, 162, 184, 0.8)',   // Biru (Sedang Berjalan)
    'rgba(40, 167, 69, 0.8)',    // Hijau (Selesai)
    'rgba(220, 53, 69, 0.8)'     // Merah (Dibatalkan)
  ];
  
  // Warna dengan transparansi rendah untuk bar chart
  $monthly_colors = [
    'rgba(75, 192, 192, 0.7)',  // Hijau Tosca
    'rgba(54, 162, 235, 0.7)',  // Biru
    'rgba(153, 102, 255, 0.7)',  // Ungu
    'rgba(255, 159, 64, 0.7)',  // Oranye
    'rgba(255, 99, 132, 0.7)',  // Merah Muda
    'rgba(255, 206, 86, 0.7)',  // Kuning
    'rgba(75, 192, 192, 0.7)',  // Hijau Tosca
    'rgba(54, 162, 235, 0.7)',  // Biru
    'rgba(153, 102, 255, 0.7)',  // Ungu
    'rgba(255, 159, 64, 0.7)',  // Oranye
    'rgba(255, 99, 132, 0.7)',  // Merah Muda
    'rgba(255, 206, 86, 0.7)'   // Kuning
  ];

} catch (PDOException $e) {
  error_log('Error getting monitoring data: ' . $e->getMessage());
  $monitorings = [];
  $available_years = [date('Y')];
  $monitoring_months = $month_names;
  $monitoring_counts = array_fill(0, 12, 0);
  $status_labels = ['Menunggu', 'Sedang Berjalan', 'Selesai', 'Dibatalkan'];
  $status_counts = [0, 0, 0, 0];
  $status_colors = [
    'rgba(255, 193, 7, 0.8)',   // Kuning (Menunggu)
    'rgba(23, 162, 184, 0.8)',   // Biru (Sedang Berjalan)
    'rgba(40, 167, 69, 0.8)',    // Hijau (Selesai)
    'rgba(220, 53, 69, 0.8)'     // Merah (Dibatalkan)
  ];
  $monthly_colors = [
    'rgba(75, 192, 192, 0.7)',  // Hijau Tosca (repeat for 12 months)
    'rgba(54, 162, 235, 0.7)',
    'rgba(153, 102, 255, 0.7)',
    'rgba(255, 159, 64, 0.7)',
    'rgba(255, 99, 132, 0.7)',
    'rgba(255, 206, 86, 0.7)',
    'rgba(75, 192, 192, 0.7)',
    'rgba(54, 162, 235, 0.7)',
    'rgba(153, 102, 255, 0.7)',
    'rgba(255, 159, 64, 0.7)',
    'rgba(255, 99, 132, 0.7)',
    'rgba(255, 206, 86, 0.7)'
  ];
}
?>

<!-- Monitoring Section -->
<section id="monitoring" class="monitoring-section py-5">
  <div class="container">
    <div class="section-header text-center mb-5">
      <h2>Monitoring & Evaluasi</h2>
      <p class="section-subheading">
        Data kegiatan monitoring dan evaluasi CDK Wilayah Bojonegoro
      </p>
    </div>

    <!-- Filter Tahun -->
    <div class="monitoring-filter mb-4">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
          <form action="index.php" method="get" class="input-group">
            <input type="hidden" name="page" value="monitoring">
            <label class="input-group-text" for="monitoringYear">Tahun</label>
            <select class="form-select" id="monitoringYear" name="year" onchange="this.form.submit()">
              <?php foreach ($available_years as $year): ?>
                <option value="<?php echo $year; ?>" <?php echo ($year == $current_year) ? 'selected' : ''; ?>>
                  <?php echo $year; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
      </div>
    </div>

    <!-- Monitoring Stats Cards -->
    <div class="row mb-4">
      <div class="col-md-3 col-6 mb-3">
        <div class="stat-card bg-white text-dark border">
          <div class="stat-icon text-primary">
            <i class="ri-file-list-3-line"></i>
          </div>
          <div class="stat-content">
            <h3><?php echo $total_monitoring; ?></h3>
            <p>Total Monitoring</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="stat-card bg-white text-dark border">
          <div class="stat-icon text-info">
            <i class="ri-loader-line"></i>
          </div>
          <div class="stat-content">
            <h3><?php echo $monitoring_by_status['in_progress']; ?></h3>
            <p>Sedang Berjalan</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="stat-card bg-white text-dark border">
          <div class="stat-icon text-success">
            <i class="ri-check-double-line"></i>
          </div>
          <div class="stat-content">
            <h3><?php echo $monitoring_by_status['completed']; ?></h3>
            <p>Selesai</p>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-6 mb-3">
        <div class="stat-card bg-white text-dark border">
          <div class="stat-icon text-warning">
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
      <div class="col-lg-8 mb-4">
        <div class="card h-100 shadow-sm">
          <div class="card-header bg-white">
            <h5 class="card-title mb-0">Jumlah Monitoring Per Bulan (<?php echo $current_year; ?>)</h5>
          </div>
          <div class="card-body">
            <canvas id="monthlyMonitoringChart"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg-4 mb-4">
        <div class="card h-100 shadow-sm">
          <div class="card-header bg-white">
            <h5 class="card-title mb-0">Status Monitoring</h5>
          </div>
          <div class="card-body">
            <canvas id="statusMonitoringChart"></canvas>
          </div>
        </div>
      </div>
    </div>

    <!-- Monitoring Table -->
    <div class="row">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header bg-white">
            <h5 class="card-title mb-0">Daftar Kegiatan Monitoring Tahun <?php echo $current_year; ?></h5>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="px-3">No</th>
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
                        <td class="px-3"><?php echo $index + 1; ?></td>
                        <td><?php echo date('d/m/Y', strtotime($monitoring['date'])); ?></td>
                        <td><?php echo htmlspecialchars($monitoring['location']); ?></td>
                        <td><?php echo htmlspecialchars($monitoring['title']); ?></td>
                        <td>
                          <?php
                          $status_text = '';
                          $status_class = '';
                          switch ($monitoring['status']) {
                            case 'pending':
                              $status_text = 'Menunggu';
                              $status_class = 'badge bg-warning text-dark';
                              break;
                            case 'in_progress':
                              $status_text = 'Sedang Berjalan';
                              $status_class = 'badge bg-info';
                              break;
                            case 'completed':
                              $status_text = 'Selesai';
                              $status_class = 'badge bg-success';
                              break;
                            case 'cancelled':
                              $status_text = 'Dibatalkan';
                              $status_class = 'badge bg-danger';
                              break;
                            default:
                              $status_text = 'Tidak Diketahui';
                              $status_class = 'badge bg-secondary';
                          }
                          ?>
                          <span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="5" class="text-center py-4">Tidak ada data monitoring untuk tahun <?php echo $current_year; ?></td>
                    </tr>
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

<!-- Script untuk Chart Monitoring -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Cek apakah Chart.js tersedia
  if (typeof Chart === 'undefined') {
    document.querySelectorAll('.chart-container').forEach(container => {
      container.innerHTML = '<div class="alert alert-warning">Chart tidak dapat dimuat. Chart.js tidak tersedia.</div>';
    });
    return;
  }
  
  // Data untuk chart monitoring per bulan
  const monthlyMonitoringChart = new Chart(
    document.getElementById('monthlyMonitoringChart'),
    {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($monitoring_months); ?>,
        datasets: [{
          label: 'Jumlah Monitoring',
          data: <?php echo json_encode($monitoring_counts); ?>,
          backgroundColor: <?php echo json_encode($monthly_colors); ?>,
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            bodyFont: {
              size: 14
            },
            callbacks: {
              label: function(context) {
                return `Jumlah: ${context.parsed.y} monitoring`;
              }
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              precision: 0,
              font: {
                size: 12
              }
            },
            grid: {
              color: 'rgba(0, 0, 0, 0.05)'
            }
          },
          x: {
            ticks: {
              font: {
                size: 12
              }
            },
            grid: {
              display: false
            }
          }
        }
      }
    }
  );

  // Data untuk chart status monitoring
  const statusMonitoringChart = new Chart(
    document.getElementById('statusMonitoringChart'),
    {
      type: 'pie',
      data: {
        labels: <?php echo json_encode($status_labels); ?>,
        datasets: [{
          data: <?php echo json_encode($status_counts); ?>,
          backgroundColor: <?php echo json_encode($status_colors); ?>,
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 15,
              padding: 15,
              font: {
                size: 13
              }
            }
          },
          tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            bodyFont: {
              size: 14
            },
            callbacks: {
              label: function(context) {
                const value = context.parsed;
                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                const percentage = total ? Math.round((value / total) * 100) : 0;
                return `${context.label}: ${value} (${percentage}%)`;
              }
            }
          }
        }
      }
    }
  );
});
</script>

<style>
/* Styles untuk Monitoring Section */
.monitoring-section {
  background-color: #f8f9fa;
  padding: 80px 0;
}

.stat-card {
  background-color: #ffffff;
  border-radius: 10px;
  box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
  padding: 20px;
  height: 100%;
  display: flex;
  align-items: center;
  transition: transform 0.2s;
}

.stat-card:hover {
  transform: translateY(-5px);
}

.stat-icon {
  font-size: 2.5rem;
  margin-right: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-content h3 {
  font-size: 1.8rem;
  font-weight: 700;
  margin-bottom: 5px;
}

.stat-content p {
  margin-bottom: 0;
  color: #6c757d;
  font-size: 0.9rem;
}

.card {
  border-radius: 10px;
  border: none;
  transition: box-shadow 0.3s;
}

.card:hover {
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1) !important;
}

.card-header {
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  padding: 15px 20px;
}

.card-title {
  font-weight: 600;
  color: #343a40;
}

.table th {
  font-weight: 600;
  color: #495057;
}

.table td {
  vertical-align: middle;
}

.badge {
  font-weight: 500;
  padding: 0.4em 0.7em;
}
</style>
</script>
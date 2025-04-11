<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Ambil tahun dari query string atau gunakan tahun sekarang
$selected_year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Fungsi untuk mendapatkan statistik berdasarkan kategori
function getStatisticsByCategory($category = null, $year = null) {
    global $pdo;
    
    $sql = "SELECT * FROM statistics";
    $params = [];
    
    if ($category) {
        $sql .= " WHERE category = ?";
        $params[] = $category;
        
        if ($year) {
            $sql .= " AND year = ?";
            $params[] = $year;
        }
    } elseif ($year) {
        $sql .= " WHERE year = ?";
        $params[] = $year;
    }
    
    $sql .= " ORDER BY id ASC";
    
    $stmt = $pdo->prepare($sql);
    
    if (count($params) > 0) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

try {
    // Ambil data statistik untuk tahun yang dipilih
    $all_statistics = getStatisticsByCategory(null, $selected_year);

    // Cari tahun terkecil dan terbesar untuk pilihan filter
    $stmt = $pdo->query("SELECT MIN(year) as min_year, MAX(year) as max_year FROM statistics");
    $year_range = $stmt->fetch(PDO::FETCH_ASSOC);
    $min_year = $year_range['min_year'] ?? date('Y');
    $max_year = $year_range['max_year'] ?? date('Y');
} catch (PDOException $e) {
    error_log('Database error in statistik.php: ' . $e->getMessage());
    $all_statistics = [];
    $min_year = $max_year = date('Y');
}
?>

<!-- Statistik Section -->
<section id="statistik" class="statistics-section py-5">
    <div class="container">
        <div class="section-header text-center mb-5">
            <h2>Data & Statistik</h2>
            <p class="section-subheading">
                Informasi statistik kehutanan di wilayah kerja CDK Bojonegoro
            </p>
        </div>
        
        <!-- Filter Tahun -->
        <div class="statistics-filter mb-5">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <form action="index.php" method="get" class="input-group">
                        <input type="hidden" name="page" value="statistik">
                        <label class="input-group-text" for="statisticYear">Tahun</label>
                        <select class="form-select" id="statisticYear" name="year" onchange="this.form.submit()">
                            <?php for ($year = $max_year; $year >= $min_year; $year--): ?>
                            <option value="<?php echo $year; ?>" <?php echo ($year == $selected_year) ? 'selected' : ''; ?>>
                                <?php echo $year; ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <?php if (empty($all_statistics)): ?>
        <div class="row">
            <div class="col-12">
                <div class="alert alert-info text-center">
                    Tidak ada data statistik yang tersedia untuk tahun <?php echo $selected_year; ?>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="row">
            <?php foreach ($all_statistics as $index => $statistic): ?>
                <?php
                // Decode JSON data
                $chart_data = json_decode($statistic['data_json'], true);
                if (!$chart_data || !isset($chart_data['labels']) || !isset($chart_data['data'])) {
                    continue;
                }
                
                $chart_id = 'chart_' . $statistic['id'];
                $chart_count = $index + 1;
                $delay = $index * 100;
                ?>
                <div class="col-lg-6 mb-4">
                    <div class="statistic-card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><?php echo htmlspecialchars($statistic['title']); ?></h4>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="<?php echo $chart_id; ?>"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Script untuk inisialisasi chart -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cek apakah Chart.js tersedia
    if (typeof Chart === 'undefined') {
        document.querySelectorAll('.chart-container').forEach(container => {
            container.innerHTML = '<div class="alert alert-warning">Chart tidak dapat dimuat. Chart.js tidak tersedia.</div>';
        });
        return;
    }
    
    // Data untuk chart
    const chartData = {
        <?php 
        foreach ($all_statistics as $statistic): 
            $data = json_decode($statistic['data_json'], true);
            if (!$data || !isset($data['labels']) || !isset($data['data'])) {
                continue;
            }
            $chart_id = 'chart_' . $statistic['id'];
            
            // Siapkan array warna
            $colors = [
                'rgba(75, 192, 192, 0.6)',
                'rgba(54, 162, 235, 0.6)',
                'rgba(255, 206, 86, 0.6)',
                'rgba(255, 99, 132, 0.6)',
                'rgba(153, 102, 255, 0.6)',
                'rgba(255, 159, 64, 0.6)'
            ];
            
            // Pastikan ada cukup warna untuk semua data
            while (count($colors) < count($data['data'])) {
                $colors = array_merge($colors, $colors);
            }
            
            // Buat string JSON untuk data chart
            echo "'". $chart_id ."': {\n";
            echo "    labels: ". json_encode($data['labels']) .",\n";
            echo "    data: ". json_encode($data['data']) .",\n";
            echo "    colors: ". json_encode(array_slice($colors, 0, count($data['data']))) .",\n";
            echo "    title: '". addslashes($statistic['title']) ."',\n";
            echo "    unit: '". addslashes($statistic['unit'] ?? '') ."'\n";
            echo "},\n";
        endforeach;
        ?>
    };
    
    // Buat semua chart
    Object.keys(chartData).forEach(chartId => {
        const canvas = document.getElementById(chartId);
        if (!canvas) {
            return;
        }
        
        const data = chartData[chartId];
        const ctx = canvas.getContext('2d');
        
        // Buat chart
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.labels,
                datasets: [{
                    label: data.title,
                    data: data.data,
                    backgroundColor: data.colors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: data.title + (data.unit ? ` (${data.unit})` : ''),
                        font: {
                            size: 16
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y.toLocaleString('id-ID');
                                    if (data.unit) {
                                        label += ' ' + data.unit;
                                    }
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    });
});
</script>

<style>
.statistic-card {
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    overflow: hidden;
}

.statistic-card .card-header {
    background-color: #f8f9fa;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
}

.statistic-card .card-header h4 {
    margin-bottom: 0;
    font-weight: 600;
}

.statistic-card .card-body {
    padding: 20px;
    height: 340px;
}

.chart-container {
    width: 100%;
    height: 100%;
}
</style>

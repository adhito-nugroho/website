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
    
    // Modern color palettes
    const modernColorSets = [
        // Set 1: Teal to Green
        [
            'rgba(30, 138, 92, 0.8)',
            'rgba(64, 168, 120, 0.8)',
            'rgba(96, 188, 145, 0.8)',
            'rgba(129, 209, 170, 0.8)',
            'rgba(162, 229, 196, 0.8)'
        ],
        // Set 2: Blue to Teal
        [
            'rgba(5, 150, 176, 0.8)',
            'rgba(61, 182, 233, 0.8)',
            'rgba(110, 197, 233, 0.8)',
            'rgba(159, 213, 233, 0.8)',
            'rgba(208, 228, 233, 0.8)'
        ],
        // Set 3: Yellow to Orange
        [
            'rgba(255, 209, 102, 0.8)',
            'rgba(255, 183, 77, 0.8)',
            'rgba(255, 157, 51, 0.8)',
            'rgba(255, 130, 25, 0.8)',
            'rgba(250, 95, 0, 0.8)'
        ]
    ];
    
    // Dark mode palettes
    const darkModeColorSets = [
        // Set 1: Turquoise
        [
            'rgba(20, 184, 166, 0.8)',
            'rgba(45, 212, 191, 0.8)',
            'rgba(94, 234, 212, 0.8)',
            'rgba(153, 246, 228, 0.8)',
            'rgba(204, 251, 241, 0.8)'
        ],
        // Set 2: Blue
        [
            'rgba(14, 165, 233, 0.8)',
            'rgba(56, 189, 248, 0.8)',
            'rgba(125, 211, 252, 0.8)',
            'rgba(186, 230, 253, 0.8)',
            'rgba(224, 242, 254, 0.8)'
        ],
        // Set 3: Amber
        [
            'rgba(245, 158, 11, 0.8)',
            'rgba(251, 191, 36, 0.8)',
            'rgba(253, 230, 138, 0.8)',
            'rgba(252, 211, 77, 0.8)',
            'rgba(254, 243, 199, 0.8)'
        ]
    ];
    
    // Handle color scheme changes when dark mode is toggled
    function getColorScheme(colorSetIndex) {
        const isDarkMode = document.body.classList.contains('dark-mode');
        return isDarkMode ? darkModeColorSets[colorSetIndex % darkModeColorSets.length] : modernColorSets[colorSetIndex % modernColorSets.length];
    }
    
    // Data untuk chart
    const chartData = {
        <?php 
        $colorSetIndex = 0;
        foreach ($all_statistics as $statistic): 
            $data = json_decode($statistic['data_json'], true);
            if (!$data || !isset($data['labels']) || !isset($data['data'])) {
                continue;
            }
            $chart_id = 'chart_' . $statistic['id'];
            
            // Rotasi warna untuk setiap chart
            $colorSetIndex = ($colorSetIndex + 1) % 3;
            
            // Buat string JSON untuk data chart
            echo "'". $chart_id ."': {\n";
            echo "    labels: ". json_encode($data['labels']) .",\n";
            echo "    data: ". json_encode($data['data']) .",\n";
            echo "    colorSetIndex: ". $colorSetIndex .",\n";
            echo "    title: '". addslashes($statistic['title']) ."',\n";
            echo "    unit: '". addslashes($statistic['unit'] ?? '') ."'\n";
            echo "},\n";
        endforeach;
        ?>
    };
    
    // Chart instances by ID for reference
    const charts = {};
    
    // Function to create or update charts
    function createOrUpdateCharts() {
        Object.keys(chartData).forEach(chartId => {
            const canvas = document.getElementById(chartId);
            if (!canvas) {
                return;
            }
            
            const data = chartData[chartId];
            const ctx = canvas.getContext('2d');
            
            // Get colors based on current theme
            const colors = getColorScheme(data.colorSetIndex);
            // Ensure we have enough colors for all data points
            while (colors.length < data.data.length) {
                colors.push(...colors);
            }
            
            // Use only the colors we need
            const finalColors = colors.slice(0, data.data.length);
            
            // If chart already exists, update it
            if (charts[chartId]) {
                const chart = charts[chartId];
                chart.data.datasets[0].backgroundColor = finalColors;
                chart.update();
                return;
            }
            
            // Otherwise create a new chart
            const chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: data.title,
                        data: data.data,
                        backgroundColor: finalColors,
                        borderWidth: 0,
                        borderRadius: 6,
                        hoverBorderWidth: 1,
                        hoverBorderColor: 'rgba(255, 255, 255, 0.5)',
                        barPercentage: 0.7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        duration: 1000,
                        easing: 'easeOutQuart'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        title: {
                            display: true,
                            text: data.title + (data.unit ? ` (${data.unit})` : ''),
                            font: {
                                size: 16,
                                family: "'Poppins', sans-serif",
                                weight: 'bold'
                            },
                            color: document.body.classList.contains('dark-mode') ? '#e5e7eb' : '#1a2c2f',
                            padding: 20
                        },
                        tooltip: {
                            backgroundColor: document.body.classList.contains('dark-mode') ? 'rgba(30, 35, 40, 0.8)' : 'rgba(255, 255, 255, 0.9)',
                            titleColor: document.body.classList.contains('dark-mode') ? '#fff' : '#1a2c2f',
                            bodyColor: document.body.classList.contains('dark-mode') ? '#e5e7eb' : '#4d6369',
                            bodyFont: {
                                family: "'Poppins', sans-serif"
                            },
                            padding: 12,
                            borderWidth: 1,
                            borderColor: document.body.classList.contains('dark-mode') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)',
                            cornerRadius: 8,
                            boxShadow: '0px 4px 10px rgba(0, 0, 0, 0.1)',
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
                            grid: {
                                color: document.body.classList.contains('dark-mode') ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                },
                                color: document.body.classList.contains('dark-mode') ? '#9ca3af' : '#4d6369',
                                padding: 10,
                                callback: function(value) {
                                    return value.toLocaleString('id-ID');
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif"
                                },
                                color: document.body.classList.contains('dark-mode') ? '#9ca3af' : '#4d6369',
                                padding: 10
                            }
                        }
                    }
                }
            });
            
            // Store chart instance for future updates
            charts[chartId] = chart;
        });
    }
    
    // Create initial charts
    createOrUpdateCharts();
    
    // Update charts when theme changes
    document.getElementById('theme-toggle')?.addEventListener('change', function() {
        // Give time for the dark-mode class to be applied
        setTimeout(function() {
            Object.values(charts).forEach(chart => {
                // Update title color
                chart.options.plugins.title.color = document.body.classList.contains('dark-mode') ? '#e5e7eb' : '#1a2c2f';
                
                // Update tooltip style
                chart.options.plugins.tooltip.backgroundColor = document.body.classList.contains('dark-mode') ? 'rgba(30, 35, 40, 0.8)' : 'rgba(255, 255, 255, 0.9)';
                chart.options.plugins.tooltip.titleColor = document.body.classList.contains('dark-mode') ? '#fff' : '#1a2c2f';
                chart.options.plugins.tooltip.bodyColor = document.body.classList.contains('dark-mode') ? '#e5e7eb' : '#4d6369';
                chart.options.plugins.tooltip.borderColor = document.body.classList.contains('dark-mode') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                
                // Update scales
                chart.options.scales.y.grid.color = document.body.classList.contains('dark-mode') ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';
                chart.options.scales.y.ticks.color = document.body.classList.contains('dark-mode') ? '#9ca3af' : '#4d6369';
                chart.options.scales.x.ticks.color = document.body.classList.contains('dark-mode') ? '#9ca3af' : '#4d6369';
                
                // Update chart colors
                const chartId = chart.canvas.id;
                const data = chartData[chartId];
                const colors = getColorScheme(data.colorSetIndex);
                
                // Ensure we have enough colors
                while (colors.length < data.data.length) {
                    colors.push(...colors);
                }
                
                chart.data.datasets[0].backgroundColor = colors.slice(0, data.data.length);
                chart.update();
            });
        }, 50);
    });
});
</script>

<style>
.statistic-card {
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    margin-bottom: 24px;
    overflow: hidden;
    border: none;
    background-color: var(--bg-white);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.statistic-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.statistic-card .card-header {
    background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
    padding: 18px 24px;
    border: none;
    color: white;
    position: relative;
    overflow: hidden;
}

.statistic-card .card-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    height: 100%;
    width: 35%;
    background: rgba(255, 255, 255, 0.1);
    clip-path: polygon(100% 0, 0 0, 100% 100%);
}

.statistic-card .card-header h4 {
    margin-bottom: 0;
    font-weight: 600;
    position: relative;
}

.statistic-card .card-body {
    padding: 24px;
    height: 340px;
    background-color: var(--bg-white);
    display: flex;
    align-items: center;
    justify-content: center;
}

.chart-container {
    width: 100%;
    height: 100%;
    padding: 10px;
}

/* Dark mode styling */
.dark-mode .statistic-card {
    background-color: var(--dark-card-bg);
    border-color: var(--dark-border);
}

.dark-mode .statistic-card .card-body {
    background-color: var(--dark-card-bg);
}

.dark-mode .statistic-card .card-header {
    background: linear-gradient(90deg, var(--primary-dark), var(--primary-color));
}
</style>

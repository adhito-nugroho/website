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
                <div class="col-lg-6">
                    <div class="statistic-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h4><?php echo htmlspecialchars($statistic['title']); ?></h4>
                            <?php if (!empty($statistic['unit'])): ?>
                            <span class="badge bg-light text-dark"><?php echo htmlspecialchars($statistic['unit']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="card-body">
                            <div class="chart-container" data-chart-id="<?php echo $chart_id; ?>">
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
    console.log('DOM Content Loaded - Initializing Statistics Charts');
    
    // Cek apakah Chart.js tersedia
    if (typeof Chart === 'undefined') {
        console.error('Chart.js is not available');
        document.querySelectorAll('.chart-container').forEach(container => {
            container.innerHTML = '<div class="alert alert-warning">Chart tidak dapat dimuat. Chart.js tidak tersedia.</div>';
        });
        return;
    }
    
    console.log('Chart.js is available');
    
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
    
    console.log('Chart data prepared:', Object.keys(chartData).length, 'charts');
    
    // Chart instances by ID for reference
    const charts = {};
    
    // Function to add a temporary border to debug element sizing
    function addDebugBorder(element, color = 'red') {
        const originalBorder = element.style.border;
        element.style.border = `2px solid ${color}`;
        setTimeout(() => {
            element.style.border = originalBorder;
        }, 5000);
    }
    
    // Wait a short moment to ensure DOM is fully ready
    setTimeout(function() {
        try {
            console.log('Starting chart initialization');
            
            // Function to create or update charts
            function createOrUpdateCharts() {
                Object.keys(chartData).forEach(chartId => {
                    try {
                        const canvas = document.getElementById(chartId);
                        if (!canvas) {
                            console.warn(`Canvas element with ID ${chartId} not found`);
                            return;
                        }
                        
                        console.log(`Initializing chart: ${chartId}`);
                        
                        // Debug element sizes
                        const container = canvas.closest('.chart-container');
                        const cardBody = canvas.closest('.card-body');
                        console.log(`Canvas dimensions: ${canvas.clientWidth}x${canvas.clientHeight}`);
                        if (container) console.log(`Container dimensions: ${container.clientWidth}x${container.clientHeight}`);
                        if (cardBody) console.log(`Card body dimensions: ${cardBody.clientWidth}x${cardBody.clientHeight}`);
                        
                        // Add temporary debug borders
                        addDebugBorder(canvas, 'blue');
                        if (container) addDebugBorder(container, 'green');
                        if (cardBody) addDebugBorder(cardBody, 'orange');
                        
                        const data = chartData[chartId];
                        console.log(`Chart data for ${chartId}:`, data);
                        
                        const ctx = canvas.getContext('2d');
                        
                        // Get colors based on current theme
                        const colors = getColorScheme(data.colorSetIndex);
                        // Ensure we have enough colors for all data points
                        while (colors.length < data.data.length) {
                            colors.push(...colors);
                        }
                        
                        // Use only the colors we need
                        const finalColors = colors.slice(0, data.data.length);
                        
                        // If chart already exists, destroy it first to prevent conflicts
                        if (charts[chartId]) {
                            console.log(`Destroying existing chart: ${chartId}`);
                            charts[chartId].destroy();
                            delete charts[chartId];
                        }
                        
                        // Ensure the canvas is properly reset
                        canvas.width = canvas.clientWidth * window.devicePixelRatio;
                        canvas.height = canvas.clientHeight * window.devicePixelRatio;
                        canvas.style.width = canvas.clientWidth + 'px';
                        canvas.style.height = canvas.clientHeight + 'px';
                        
                        // Create a new chart
                        console.log(`Creating new chart: ${chartId}`);
                        const chart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: data.labels,
                                datasets: [{
                                    label: data.title,
                                    data: data.data,
                                    backgroundColor: finalColors,
                                    borderColor: finalColors.map(color => color.replace('0.8', '1')),
                                    borderWidth: 1,
                                    borderRadius: 6,
                                    hoverBorderWidth: 2,
                                    hoverBorderColor: 'rgba(255, 255, 255, 0.7)',
                                    barPercentage: 0.6,
                                    maxBarThickness: 50,
                                    borderSkipped: false, // Rounded corners on all sides
                                    hoverBackgroundColor: finalColors.map(color => color.replace('0.8', '0.9')),
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                animation: {
                                    delay: function(context) {
                                        // Add sequential animation to each bar
                                        return context.dataIndex * 100;
                                    },
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
                                        enabled: true,
                                        backgroundColor: document.body.classList.contains('dark-mode') ? 'rgba(42, 42, 42, 0.9)' : 'rgba(255, 255, 255, 0.95)',
                                        titleColor: document.body.classList.contains('dark-mode') ? '#fff' : '#1a2c2f',
                                        bodyColor: document.body.classList.contains('dark-mode') ? '#e5e7eb' : '#4d6369',
                                        titleFont: {
                                            family: "'Poppins', sans-serif",
                                            weight: 'bold',
                                            size: 14
                                        },
                                        bodyFont: {
                                            family: "'Poppins', sans-serif",
                                            size: 13
                                        },
                                        padding: 14,
                                        borderWidth: 1,
                                        borderColor: document.body.classList.contains('dark-mode') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.05)',
                                        cornerRadius: 12,
                                        displayColors: true,
                                        boxWidth: 10,
                                        boxHeight: 10,
                                        boxPadding: 3,
                                        usePointStyle: true,
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
                                interaction: {
                                    mode: 'index',
                                    intersect: false,
                                },
                                layout: {
                                    padding: {
                                        left: 10,
                                        right: 10,
                                        top: 0,
                                        bottom: 10
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        grid: {
                                            color: document.body.classList.contains('dark-mode') ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.03)',
                                            drawBorder: false,
                                            lineWidth: 1
                                        },
                                        border: {
                                            display: false
                                        },
                                        ticks: {
                                            font: {
                                                family: "'Poppins', sans-serif",
                                                size: 12
                                            },
                                            color: document.body.classList.contains('dark-mode') ? 'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.6)',
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
                                        border: {
                                            display: false
                                        },
                                        ticks: {
                                            font: {
                                                family: "'Poppins', sans-serif",
                                                size: 12
                                            },
                                            color: document.body.classList.contains('dark-mode') ? 'rgba(255, 255, 255, 0.7)' : 'rgba(0, 0, 0, 0.6)',
                                            padding: 5,
                                            maxRotation: 45,
                                            minRotation: 0
                                        }
                                    }
                                }
                            }
                        });
                        
                        // Store chart instance for future updates
                        charts[chartId] = chart;
                        console.log(`Chart created successfully: ${chartId}`);
                    } catch (chartError) {
                        console.error(`Error creating chart ${chartId}:`, chartError);
                        const canvas = document.getElementById(chartId);
                        if (canvas) {
                            const container = canvas.closest('.chart-container');
                            if (container) {
                                container.innerHTML = `<div class="alert alert-danger">Error loading chart: ${chartError.message}</div>`;
                            }
                        }
                    }
                });
            }
            
            // Create initial charts
            createOrUpdateCharts();
            
            // Make charts globally available for potential updates
            window.statisticsCharts = charts;
            
            // Update charts when theme changes - from navbar toggle
            document.getElementById('theme-toggle')?.addEventListener('change', function() {
                // Give time for the dark-mode class to be applied
                setTimeout(function() {
                    updateChartsForTheme();
                }, 50);
            });
            
            // Also listen for theme change from floating toggle button
            document.getElementById('theme-toggle-float')?.addEventListener('click', function() {
                // Give time for the dark-mode class to be applied
                setTimeout(function() {
                    updateChartsForTheme();
                }, 50);
            });
            
            function updateChartsForTheme() {
                try {
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
                } catch (e) {
                    console.error("Error updating charts for theme change:", e);
                }
            }
            
            // Redraw charts when window is resized
            let resizeTimer;
            window.addEventListener('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    Object.values(charts).forEach(chart => {
                        try {
                            chart.resize();
                        } catch (e) {
                            console.warn("Error resizing chart:", e);
                        }
                    });
                }, 250);
            });
        } catch (error) {
            console.error("Error initializing statistics charts:", error);
            document.querySelectorAll('.chart-container').forEach(container => {
                container.innerHTML = `<div class="alert alert-danger">Error initializing charts: ${error.message}</div>`;
            });
        }
    }, 100); // Short delay to ensure DOM is ready
});
</script>

<style>
.statistic-card {
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    margin-bottom: 24px;
    overflow: hidden;
    border: none;
    background-color: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s ease;
    border: 1px solid rgba(255, 255, 255, 0.2);
    height: 100%;
    display: flex;
    flex-direction: column;
}

.statistic-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(45, 106, 79, 0.15);
}

.statistic-card .card-header {
    background: linear-gradient(120deg, var(--primary-color), var(--primary-light));
    padding: 20px 24px;
    border: none;
    color: white;
    position: relative;
    overflow: hidden;
    flex-shrink: 0;
}

.statistic-card .card-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    height: 100%;
    width: 35%;
    background: rgba(255, 255, 255, 0.15);
    clip-path: polygon(100% 0, 0 0, 100% 100%);
    transition: transform 0.5s ease;
}

.statistic-card:hover .card-header::before {
    transform: translateX(-10px);
}

.statistic-card .card-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, rgba(255, 255, 255, 0.5), transparent);
    opacity: 0;
    transition: opacity 0.5s ease;
}

.statistic-card:hover .card-header::after {
    opacity: 1;
}

.statistic-card .card-header h4 {
    margin-bottom: 0;
    font-weight: 600;
    position: relative;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.2);
    font-size: 1.25rem;
}

.statistic-card .card-body {
    padding: 10px;
    height: 340px;
    background-color: rgba(255, 255, 255, 0);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-grow: 1;
    min-height: 340px;
    overflow: hidden;
    position: relative;
}

.chart-container {
    width: 100%;
    height: 100%;
    position: relative;
    transition: all 0.3s ease;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 300px;
}

/* Make sure the Canvas is block-level and takes full space */
.chart-container canvas {
    display: block !important;
    box-sizing: border-box !important;
    height: 100% !important;
    width: 100% !important;
    min-height: 300px;
}

.statistic-card:hover .chart-container {
    transform: scale(1.02);
}

/* Fixed height to ensure consistent display */
@media (min-width: 992px) {
    .statistic-card .card-body,
    .chart-container,
    .chart-container canvas {
        height: 360px !important;
        min-height: 360px !important;
    }
}

/* Dark mode styling */
.dark-mode .statistic-card {
    background-color: rgba(42, 42, 42, 0.85);
    border-color: var(--dark-border);
    border: 1px solid rgba(255, 255, 255, 0.05);
}

.dark-mode .statistic-card .card-body {
    background-color: transparent;
}

.dark-mode .statistic-card .card-header {
    background: linear-gradient(120deg, var(--primary-dark), var(--primary-color));
}

/* Alert message styling */
.chart-container .alert {
    margin: 0;
    width: 100%;
    text-align: center;
}

/* Make sure we have equal height rows */
.statistics-section .row {
    display: flex;
    flex-wrap: wrap;
}

.statistics-section .row > [class*="col-"] {
    display: flex;
    flex-direction: column;
    margin-bottom: 24px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .statistic-card .card-body {
        height: 300px;
        min-height: 300px;
        padding: 10px;
    }
    
    .statistic-card .card-header {
        padding: 15px;
    }
    
    .statistic-card .card-header h4 {
        font-size: 1.1rem;
    }
    
    .chart-container,
    .chart-container canvas {
        min-height: 260px;
        height: 260px !important;
    }
}

/* Loading state for charts */
.chart-container.loading {
    position: relative;
}

.chart-container.loading::after {
    content: "";
    position: absolute;
    top: 50%;
    left: 50%;
    width: 40px;
    height: 40px;
    margin: -20px 0 0 -20px;
    border: 5px solid var(--primary-light);
    border-top: 5px solid var(--primary-color);
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Fix for Chart.js specific issues */
.chart-container {
    position: relative;
}

.chart-container::before {
    content: "";
    display: block;
    padding-top: 60%;
}

.chart-container > canvas {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    width: 100%;
    height: 100%;
}
</style>

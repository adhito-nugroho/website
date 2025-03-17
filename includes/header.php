<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Ambil pengaturan website dari database
$stmt = $pdo->query("SELECT * FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Pastikan $settings adalah array
if (!is_array($settings)) {
    $settings = [];
}

// Set variabel untuk title dan meta description
$page_title = isset($settings['site_title']) ? $settings['site_title'] : 'CDK Wilayah Bojonegoro';
$page_description = isset($settings['site_description']) ? $settings['site_description'] : 'Unit Pelaksana Teknis Dinas Kehutanan Provinsi Jawa Timur';

// Set title berdasarkan halaman yang sedang aktif
if ($page != 'beranda') {
    $page_titles = [
        'profil' => 'Profil',
        'layanan' => 'Layanan Kehutanan',
        'program' => 'Program & Kegiatan',
        'statistik' => 'Data & Statistik',
        'monitoring' => 'Monitoring & Evaluasi',
        'publikasi' => 'Publikasi & Informasi',
        'galeri' => 'Galeri Kegiatan',
        'kontak' => 'Hubungi Kami'
    ];
    
    if (isset($page_titles[$page])) {
        $page_title = $page_titles[$page] . ' - ' . $page_title;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="light dark" />
    <meta name="theme-color" content="#2e7d32" />
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    
    <!-- SEO Meta Tags -->
    <meta name="author" content="CDK Wilayah Bojonegoro">
    <meta name="keywords" content="kehutanan, bojonegoro, dinas kehutanan, jawa timur, hutan, lingkungan, pelestarian, cdk">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:image" content="<?php echo isset($settings['site_logo']) ? htmlspecialchars($settings['site_logo']) : 'assets/images/logo.png'; ?>">
    <meta property="og:url" content="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    
    <!-- Accessibility Meta Tags -->
    <!-- Replace the deprecated Apple-specific meta tag -->
    <!-- Instead of just: -->
    <!-- <meta name="apple-mobile-web-app-capable" content="yes"> -->
    
    <!-- Use both tags: -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="canonical" href="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
    
    <!-- Preload critical assets -->
    <link rel="preload" href="assets/css/styles.css" as="style">
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">
    <link rel="preload" href="assets/images/logo.png" as="image">
    
    <!-- Preconnect to external domains -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com" crossorigin>
    
    <!-- Font display optimization -->
    <link rel="preload" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css"></noscript>

    <!-- CSS Libraries -->
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    />
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.css"
    />
    <link
      rel="stylesheet"
      href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css"
    />
    <link
      rel="stylesheet"
      href="https://unpkg.com/swiper/swiper-bundle.min.css"
    />

    <link rel="stylesheet" href="assets/css/styles.css" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Dashboard Styles -->
    <style>
      /* Global Spacing */
      :root {
        --section-spacing: 6rem;
        --section-spacing-sm: 4rem;
        --content-spacing: 2rem;
        --navbar-height: 80px;
      }

      /* Base Layout */
      html {
        scroll-padding-top: var(--navbar-height);
        scroll-behavior: smooth;
      }

      body {
        padding-top: var(--navbar-height); /* Kompensasi untuk fixed navbar */
      }

      /* Navbar */
      .navbar {
        height: var(--navbar-height);
        background: rgba(27, 67, 50, 0.95);
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
        z-index: 1030;
      }

      .navbar.scrolled {
        background: rgba(27, 67, 50, 0.98);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      }

      .nav-link {
        color: rgba(255, 255, 255, 0.9) !important;
        font-weight: 500;
        padding: 0.5rem 1rem !important;
        transition: all 0.3s ease;
      }
      
      .nav-link:hover,
      .nav-link.active {
        color: #ffffff !important;
      }

      .navbar-brand {
        color: #ffffff !important;
        font-weight: 700;
        font-size: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        letter-spacing: 0.5px;
      }

      .navbar-brand img {
        height: 45px;
        width: auto;
        filter: drop-shadow(1px 1px 2px rgba(0, 0, 0, 0.3));
      }

      /* Section Base */
      section {
        padding: var(--section-spacing) 0;
        position: relative;
        overflow: visible;
      }

      /* Hero Section */
      .hero-section {
        padding: 0;
        height: 100vh;
        min-height: 600px;
        display: flex;
        align-items: center;
        margin-top: calc(-1 * var(--navbar-height));
      }

      .hero-video-bg {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
      }

      .hero-video-bg video {
        width: 100%;
        height: 100%;
        object-fit: cover;
      }

      .hero-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, rgba(45, 106, 79, 0.6), rgba(27, 67, 50, 0.65));
        z-index: 2;
      }

      #particles-js {
        position: absolute;
        width: 100%;
        height: 100%;
        z-index: 3;
        opacity: 0.3;
      }

      .hero-content-wrapper {
        position: relative;
        z-index: 4;
        width: 100%;
        padding: 0 1.5rem;
      }

      .hero-content {
        max-width: 800px;
        margin: 0 auto;
        text-align: center;
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 24px;
        padding: 3.5rem;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 8px 32px rgba(31, 38, 135, 0.15);
      }

      /* Section Headers */
      .section-header {
        margin-bottom: var(--section-spacing-sm);
        text-align: center;
        max-width: 800px;
        margin-left: auto;
        margin-right: auto;
        position: relative;
        z-index: 10;
        padding: 1rem 0;
        background: linear-gradient(to bottom, var(--bg-white) 50%, transparent);
      }

      .section-header h2 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: var(--text-dark);
        font-weight: 700;
      }

      .section-header .section-subheading {
        font-size: 1.1rem;
        color: var(--text-medium);
        line-height: 1.6;
      }

      /* Content z-index */
      .section-content {
        position: relative;
        z-index: 1;
        margin-top: -50px;
        padding-top: 50px;
      }

      /* Dashboard Cards */
      .dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        margin-top: var(--content-spacing);
        position: relative;
        z-index: 1;
        padding-top: 2rem;
      }

      .dashboard-card {
        background: var(--bg-white);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
        transform: translateY(0); /* Reset transform */
        opacity: 1; /* Reset opacity */
      }

      .card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
      }

      .card-header h4 {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin: 0;
        font-size: 1.2rem;
      }

      .chart-container {
        height: 300px;
        position: relative;
      }

      /* Responsive */
      @media (max-width: 991px) {
        :root {
          --section-spacing: 5rem;
          --section-spacing-sm: 3rem;
          --navbar-height: 70px;
        }

        .section-header h2 {
          font-size: 2rem;
        }
      }

      @media (max-width: 768px) {
        :root {
          --section-spacing: 4rem;
          --section-spacing-sm: 2.5rem;
          --content-spacing: 1.5rem;
          --navbar-height: 60px;
        }

        .hero-content {
          padding: 2rem;
        }

        .dashboard-grid {
          grid-template-columns: 1fr;
          gap: 1.5rem;
        }

        .section-header h2 {
          font-size: 1.75rem;
        }
      }

      @media (max-width: 576px) {
        .hero-content {
          padding: 1.5rem;
          margin: 0 1rem;
        }

        .container {
          padding: 0 1rem;
        }
      }

      /* Update ScrollReveal animations */
      [data-aos] {
        pointer-events: all !important;
      }

      /* Update specific sections background */
      .profile-section {
        background: var(--bg-light);
      }

      .stats-dashboard {
        background: var(--bg-white);
        position: relative;
      }

      .monitoring-section {
        background: var(--bg-light);
      }

      /* Remove background from section header */
      .section-header {
        opacity: 1 !important;
        transform: none !important;
        transition: none !important;
      }

      /* Update dashboard cards */
      .dashboard-grid {
        position: relative;
        z-index: 1;
        margin-top: 2rem;
      }

      .dashboard-card {
        background: var(--bg-white);
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: var(--shadow-md);
        transition: all 0.3s ease;
      }
    </style>

    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>
    
    <!-- Loading Overlay Style -->
    <style>
      .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        transition: opacity 0.3s, visibility 0.3s;
      }
      
      .loading-animation {
        width: 50px;
        height: 50px;
        border: 5px solid #f3f3f3;
        border-top: 5px solid #2e7d32;
        border-radius: 50%;
        animation: spin 1s linear infinite;
      }
      
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
    </style>
    
    <!-- Inline script to hide loading overlay after 5 seconds -->
    <script>
      // Fallback to hide loading overlay after 5 seconds
      window.addEventListener('load', function() {
        const loadingOverlay = document.querySelector('.loading-overlay');
        if (loadingOverlay) {
          loadingOverlay.style.display = 'none';
        }
      });
      
      // Fallback timeout
      setTimeout(function() {
        const loadingOverlay = document.querySelector('.loading-overlay');
        if (loadingOverlay) {
          loadingOverlay.style.display = 'none';
        }
      }, 5000);
    </script>
  </head>
  <body>
    <!-- Loading Overlay -->
    <div class="loading-overlay">
      <div class="loading-animation"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
      <div class="container">
        <a class="navbar-brand" href="index.php">
          <img
            src="assets/images/logo.png"
            alt="Logo CDK Bojonegoro"
            height="45"
            width="45"
            style="filter: drop-shadow(1px 1px 2px rgba(0, 0, 0, 0.3));"
          />
          <span style="color: #ffffff !important; font-weight: 700; text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.5);">CDK Wilayah Bojonegoro</span>
        </a>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarNav"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item">
              <a class="nav-link <?php echo ($page == 'beranda') ? 'active' : ''; ?>" href="index.php">Beranda</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo ($page == 'beranda') ? '#profil' : 'index.php#profil'; ?>">Profil</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo ($page == 'beranda') ? '#layanan' : 'index.php#layanan'; ?>">Layanan</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo ($page == 'beranda') ? '#program' : 'index.php#program'; ?>">Program</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo ($page == 'beranda') ? '#monitoring' : 'index.php#monitoring'; ?>">Monitoring</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo ($page == 'beranda') ? '#publikasi' : 'index.php#publikasi'; ?>">Publikasi</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo ($page == 'beranda') ? '#kontak' : 'index.php#kontak'; ?>">Kontak</a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main id="main-content">
    <!-- Content akan dimuat di sini -->

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
    
    <!-- Error handling for resource loading -->
    <script>
    // Pantau loading resource untuk debugging
    document.addEventListener('error', function(e) {
      const target = e.target;
      if (target.tagName === 'IMG' || target.tagName === 'SCRIPT' || target.tagName === 'LINK') {
        // Log resource loading error to analytics if available
        if (typeof gtag === 'function') {
          gtag('event', 'resource_error', {
            resource_type: target.tagName.toLowerCase(),
            resource_url: target.src || target.href,
            page_url: window.location.href
          });
        }
      }
    }, true);
    </script>
    
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
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""
    />
    <link
      rel="stylesheet"
      href="https://unpkg.com/swiper/swiper-bundle.min.css"
    />

    <link rel="stylesheet" href="assets/css/styles.css" />
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <?php 
    // Muat CSS publikasi jika halaman publikasi atau terdapat parameter view/id
    $load_publikasi_css = ($page === 'publikasi');
    $load_publikasi_css = $load_publikasi_css || (isset($_GET['view']) && in_array($_GET['view'], ['all', 'documents']));
    $load_publikasi_css = $load_publikasi_css || isset($_GET['id']);
    
    if ($load_publikasi_css): 
    ?>
    <link rel="stylesheet" href="assets/css/publikasi.css" />
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <!-- Kondisional loading script -->
    <script>
    // Flag untuk menentukan apakah ini halaman penuh atau hanya bagian
    var isFullPage = <?php echo ($page === 'beranda' || !$has_specific_view) ? 'true' : 'false'; ?>;
    </script>

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

    <!-- Module-specific CSS -->
    <?php if ($page == 'publikasi-detail' && isset($_GET['view']) && $_GET['view'] == 'documents'): ?>
    <link rel="stylesheet" href="assets/css/modules/documents.css" />
    <?php endif; ?>
    
    <?php if ($page == 'publikasi-list'): ?>
    <link rel="stylesheet" href="assets/css/modules/publikasi.css" />
    <?php endif; ?>
    
    <!-- Leaflet CSS and JS -->
    <script 
      src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
      integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
      crossorigin="">
    </script>
    <link
      rel="stylesheet"
      href="https://unpkg.com/swiper/swiper-bundle.min.css"
    />
    
    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Modern UI Theme CSS -->
    <style>
      :root {
        /* Modern Color Palette */
        --primary-color: #1e8a5c;     /* Lebih vibrant green */
        --primary-dark: #146644;      /* Darker green */
        --primary-light: #47bb89;     /* Lighter green */
        
        /* Secondary Colors - Lebih modern */
        --secondary-color: #3db6e9;   /* Modern blue */
        --secondary-light: #98e5ff;   /* Light blue */
        --secondary-dark: #0179a8;    /* Dark blue */
        
        /* Accent Colors */
        --accent-color: #ffd166;      /* Modern yellow */
        --accent-light: #ffe7a9;      /* Light yellow */
        --accent-dark: #e6b800;       /* Dark yellow */
        
        /* Background Colors - Modern & Clean */
        --bg-light: #f8faf9;          /* Clean off-white */
        --bg-white: #ffffff;
        --bg-gray: #eef2f3;
        --bg-gradient: linear-gradient(135deg, #f8fcfa, #eef8f3);
        
        /* Text Colors - Enhanced Readability */
        --text-dark: #1a2c2f;          /* Dark teal for better contrast */
        --text-medium: #4d6369;        /* Medium teal */
        --text-light: #6e8b94;         /* Light teal */
        
        /* Status Colors - More Vibrant */
        --success-color: #04a777;      /* Vibrant green */
        --info-color: #0ea5e9;         /* Vibrant blue */
        --warning-color: #f59e0b;      /* Vibrant orange */
        --error-color: #f43f5e;        /* Modern pink/red */
        
        /* Shadow & Border - More Subtle */
        --border-color: #e9f0f2;
        --shadow-sm: 0 4px 6px rgba(47, 121, 138, 0.07);
        --shadow-md: 0 8px 15px rgba(47, 121, 138, 0.1);
        --shadow-lg: 0 15px 25px rgba(47, 121, 138, 0.12);
        
        /* Radius Values */
        --radius-sm: 6px;
        --radius-md: 12px;
        --radius-lg: 18px;
        --radius-xl: 24px;
        
        /* Animation */
        --transition-fast: 0.2s;
        --transition-normal: 0.3s;
        --transition-slow: 0.5s;
        
        /* Dark Mode Colors */
        --dark-bg: #121619;
        --dark-card-bg: #1e2328;
        --dark-border: #2a3139;
        --dark-text: #e5e7eb;
        --dark-text-muted: #9ca3af;
      }

      /* Dark Mode Toggle */
      .theme-switch-wrapper {
        display: flex;
        align-items: center;
        margin-left: 15px;
      }

      .theme-switch {
        display: inline-block;
        position: relative;
        width: 45px;
        height: 24px;
        margin: 0;
      }

      .theme-switch input {
        opacity: 0;
        width: 0;
        height: 0;
      }

      .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255,255,255,0.3);
        transition: var(--transition-normal);
        border-radius: 34px;
      }

      .slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: var(--transition-normal);
        border-radius: 50%;
      }

      input:checked + .slider {
        background-color: var(--accent-color);
      }

      input:checked + .slider:before {
        transform: translateX(21px);
      }

      /* Modern UI Enhancements */
      body {
        font-family: 'Poppins', sans-serif;
        background: var(--bg-light);
        transition: background-color var(--transition-normal);
      }

      .card, .dashboard-card, .statistic-card {
        border: none;
        border-radius: var(--radius-md);
        box-shadow: var(--shadow-sm);
        transition: transform var(--transition-normal), box-shadow var(--transition-normal);
        overflow: hidden;
      }

      .card:hover, .dashboard-card:hover, .statistic-card:hover {
        transform: translateY(-5px);
        box-shadow: var(--shadow-md);
      }

      .btn {
        font-weight: 500;
        border-radius: var(--radius-sm);
        padding: 0.6rem 1.2rem;
        transition: transform var(--transition-fast), box-shadow var(--transition-fast);
      }

      .btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-sm);
      }

      .navbar {
        background: linear-gradient(90deg, var(--primary-dark), var(--primary-color));
        box-shadow: var(--shadow-md);
        height: var(--navbar-height);
      }

      .navbar.scrolled {
        background: linear-gradient(90deg, var(--primary-dark), var(--primary-color));
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      }

      .section-header h2 {
        font-weight: 700;
        color: var(--primary-dark);
        margin-bottom: 1rem;
      }

      .section-header::after {
        content: '';
        display: block;
        width: 70px;
        height: 3px;
        background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
        margin: 1rem auto 0;
        border-radius: 3px;
      }

      /* Dark Mode Styles */
      .dark-mode {
        background-color: var(--dark-bg);
        color: var(--dark-text);
      }

      .dark-mode .card, 
      .dark-mode .dashboard-card,
      .dark-mode .statistic-card {
        background-color: var(--dark-card-bg);
        border-color: var(--dark-border);
        color: var(--dark-text);
      }

      .dark-mode .text-dark {
        color: var(--dark-text) !important;
      }

      .dark-mode .bg-light {
        background-color: var(--dark-card-bg) !important;
      }

      .dark-mode .navbar {
        background: linear-gradient(90deg, #0c3725, #1a5e41);
      }

      .dark-mode .section-header h2 {
        color: var(--dark-text);
      }

      .dark-mode .section-subheading {
        color: var(--dark-text-muted);
      }
      
      /* Modern table styles */
      table.table {
        border-radius: var(--radius-md);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
      }
      
      .table thead th {
        background-color: var(--primary-light);
        color: white;
        font-weight: 500;
        border: none;
      }
      
      .dark-mode .table thead th {
        background-color: var(--primary-dark);
      }
      
      .table tbody tr:nth-of-type(odd) {
        background-color: rgba(0, 0, 0, 0.02);
      }
      
      .dark-mode .table tbody tr:nth-of-type(odd) {
        background-color: rgba(255, 255, 255, 0.05);
      }
      
      /* Badge styles */
      .badge {
        font-weight: 500;
        padding: 0.4em 0.8em;
        border-radius: var(--radius-sm);
      }
      
      /* Chart styling */
      .chart-container {
        border-radius: var(--radius-md);
        padding: 15px;
        background-color: var(--bg-white);
        transition: background-color var(--transition-normal);
      }
      
      .dark-mode .chart-container {
        background-color: var(--dark-card-bg);
      }
      
      @media (max-width: 768px) {
        .navbar-brand {
          font-size: 1.1rem;
        }
        
        .navbar-brand img {
          height: 35px;
          width: 35px;
        }
        
        .theme-switch-wrapper {
          margin-top: 10px;
        }
      }
    </style>
    
    <!-- Dark Mode Toggle Script -->
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        // Check for saved theme preference or default to light
        const savedTheme = localStorage.getItem('theme') || 'light';
        const themeToggle = document.getElementById('theme-toggle');
        
        // Apply the saved theme on page load
        if (savedTheme === 'dark') {
          document.body.classList.add('dark-mode');
          themeToggle.checked = true;
        }
        
        // Theme toggle functionality
        themeToggle.addEventListener('change', function(e) {
          if (e.target.checked) {
            document.body.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
          } else {
            document.body.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light');
          }
        });
      });
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
              <a class="nav-link" href="<?php echo ($page == 'beranda') ? '#publikasi' : 'index.php#publikasi'; ?>">Publikasi</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="<?php echo ($page == 'beranda') ? '#kontak' : 'index.php#kontak'; ?>">Kontak</a>
            </li>
            <li class="nav-item theme-switch-wrapper">
              <label class="theme-switch" for="theme-toggle">
                <input type="checkbox" id="theme-toggle" />
                <span class="slider"></span>
              </label>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <main id="main-content">
    <!-- Content akan dimuat di sini -->

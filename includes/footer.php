<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Pastikan $settings adalah array
if (!isset($settings) || !is_array($settings)) {
    $settings = [];
}
?>

    </main>

    <!-- Footer -->
    <footer class="footer bg-dark text-white">
      <div class="container">
        <div class="row g-4">
          <div class="col-lg-4">
            <div class="footer-info">
              <img
                src="assets/images/logo-white.png"
                alt="Logo"
                height="60"
                class="mb-3"
              />
              <p>Cabang Dinas Kehutanan Wilayah Bojonegoro</p>
              <div class="social-links mt-3">
                <a href="<?php echo htmlspecialchars($settings['social_facebook'] ?? '#'); ?>" class="social-icon"
                  ><i class="fab fa-facebook-f"></i
                ></a>
                <a href="<?php echo htmlspecialchars($settings['social_twitter'] ?? '#'); ?>" class="social-icon"
                  ><i class="fab fa-twitter"></i
                ></a>
                <a href="<?php echo htmlspecialchars($settings['social_instagram'] ?? '#'); ?>" class="social-icon"
                  ><i class="fab fa-instagram"></i
                ></a>
                <a href="<?php echo htmlspecialchars($settings['social_youtube'] ?? '#'); ?>" class="social-icon"
                  ><i class="fab fa-youtube"></i
                ></a>
              </div>
            </div>
          </div>
          <div class="col-lg-2">
            <h5>Link Cepat</h5>
            <ul class="footer-links">
              <li><a href="index.php">Beranda</a></li>
              <li><a href="index.php#profil">Profil</a></li>
              <li><a href="index.php#layanan">Layanan</a></li>
              <li><a href="index.php#program">Program</a></li>
            </ul>
          </div>
          <div class="col-lg-3">
            <h5>Layanan Utama</h5>
            <ul class="footer-links">
              <?php
              // Ambil layanan dari database
              $stmt = $pdo->query("SELECT id, title FROM services WHERE is_active = 1 ORDER BY order_number LIMIT 5");
              while ($layanan = $stmt->fetch()) {
                  echo '<li><a href="index.php#layanan-' . $layanan['id'] . '">' . htmlspecialchars($layanan['title']) . '</a></li>';
              }
              ?>
            </ul>
          </div>
          <div class="col-lg-3">
            <h5>Informasi Penting</h5>
            <ul class="footer-links">
              <li><a href="#">Prosedur Pelayanan</a></li>
              <li><a href="#">Standar Pelayanan</a></li>
              <li><a href="#">Maklumat Pelayanan</a></li>
              <li><a href="#">Peraturan Terkait</a></li>
            </ul>
          </div>
        </div>
      </div>
      <div class="footer-bottom mt-4">
        <div class="container">
          <div class="row">
            <div class="col-md-6">
              <p class="mb-0">
                &copy; <?php echo date('Y'); ?> CDK Wilayah Bojonegoro. Hak Cipta Dilindungi.
              </p>
            </div>
            <div class="col-md-6 text-md-end">
              <a href="#" class="text-white me-3">Kebijakan Privasi</a>
              <a href="#" class="text-white">Syarat & Ketentuan</a>
            </div>
          </div>
        </div>
      </div>
    </footer>

    <!-- Back to Top Button -->
    <button id="backToTop" class="back-to-top">
      <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Modal Preview Image -->
    <div class="modal-preview" id="imageModal" style="display: none;">
      <span class="modal-close">&times;</span>
      <img id="modalImage" src="" alt="Preview Image">
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/scrollreveal@4.0.9/dist/scrollreveal.min.js"></script>
    <script src="assets/js/main.js"></script>
    
    <!-- Particles and Animation Scripts -->
    <script>
    // Particles Config
    particlesJS("particles-js", {
      particles: {
        number: {
          value: 40,
          density: {
            enable: true,
            value_area: 800
          }
        },
        color: {
          value: "#ffffff"
        },
        opacity: {
          value: 0.3,
          random: false
        },
        size: {
          value: 2,
          random: true
        },
        line_linked: {
          enable: true,
          distance: 150,
          color: "#ffffff",
          opacity: 0.2,
          width: 1
        },
        move: {
          enable: true,
          speed: 3,
          direction: "none",
          random: false,
          straight: false,
          out_mode: "out",
          bounce: false
        }
      },
      interactivity: {
        detect_on: "canvas",
        events: {
          onhover: {
            enable: true,
            mode: "repulse"
          },
          resize: true
        }
      },
      retina_detect: true
    });

    // Pastikan ScrollReveal sudah dimuat
    document.addEventListener('DOMContentLoaded', function() {
      if (typeof ScrollReveal !== 'undefined') {
        // Update ScrollReveal configuration
        const scrollReveal = ScrollReveal({
          distance: '20px',
          duration: 800,
          delay: 0,
          easing: 'ease-out',
          reset: false,
          useDelay: 'once',
          viewFactor: 0.1
        });

        // Hapus animasi dari section headers
        scrollReveal.reveal('.section-header', {
          distance: '0px',
          opacity: 1,
          scale: 1,
          viewFactor: 0,
          beforeReveal: function(domEl) {
            domEl.style.opacity = '1';
            domEl.style.transform = 'none';
          }
        });

        // Animasi hanya untuk content
        scrollReveal.reveal('.section-content, .dashboard-card', {
          delay: 200,
          distance: '30px',
          origin: 'bottom',
          interval: 100,
          viewFactor: 0.2,
          beforeReveal: function(domEl) {
            // Pastikan section header tetap di atas
            const header = domEl.closest('section').querySelector('.section-header');
            if (header) {
              header.style.zIndex = '10';
            }
          }
        });
      } else {
        console.warn('ScrollReveal library not loaded');
      }
    });
    </script>
    
    <!-- Performance Monitoring -->
    <script>
      // Simple performance monitoring
      window.addEventListener('load', function() {
        setTimeout(function() {
          if (window.performance) {
            const perfData = window.performance.timing;
            const pageLoadTime = perfData.loadEventEnd - perfData.navigationStart;
            const domReadyTime = perfData.domComplete - perfData.domLoading;
            
            console.log('Page load time: ' + pageLoadTime + 'ms');
            console.log('DOM ready time: ' + domReadyTime + 'ms');
            
            // Send to analytics if needed
            if (typeof gtag === 'function') {
              gtag('event', 'timing_complete', {
                'name': 'page_load',
                'value': pageLoadTime,
                'event_category': 'Performance'
              });
            }
          }
        }, 0);
      });
      
      // Report JS errors
      window.addEventListener('error', function(e) {
        if (typeof gtag === 'function') {
          gtag('event', 'exception', {
            'description': e.message,
            'fatal': false
          });
        }
      });
      
      // Track user engagement
      document.addEventListener('DOMContentLoaded', function() {
        // Track scroll depth
        let scrollDepth = 0;
        window.addEventListener('scroll', debounce(function() {
          const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
          const scrollHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
          const currentScrollDepth = Math.round((scrollTop / scrollHeight) * 100);
          
          if (currentScrollDepth > scrollDepth && currentScrollDepth % 25 === 0) {
            scrollDepth = currentScrollDepth;
            console.log('Scroll depth: ' + scrollDepth + '%');
            
            // Send to analytics if needed
            if (typeof gtag === 'function') {
              gtag('event', 'scroll_depth', {
                'depth': scrollDepth,
                'page': window.location.pathname
              });
            }
          }
        }, 100));
      });
    </script>
  </body>
</html>

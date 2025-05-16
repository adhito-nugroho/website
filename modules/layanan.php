<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Ambil data layanan dari database
$services = getServices();

// Pastikan $services adalah array
if (!is_array($services)) {
    $services = [];
}
?>

<!-- Layanan Section -->
<section id="layanan" class="services-section">
  <div class="container">
    <div class="section-header text-center" data-aos="fade-up">
      <h2>Layanan Kehutanan</h2>
      <p class="section-subheading">
        Pelayanan teknis bidang kehutanan sesuai wilayah kerja
      </p>
    </div>

    <div class="row g-4">
      <?php if (count($services) > 0): ?>
        <?php foreach ($services as $service): ?>
          <!-- Layanan: <?php echo htmlspecialchars($service['title']); ?> -->
          <div class="col-lg-4" data-aos="fade-up">
            <div class="service-card glass-card" id="layanan-<?php echo $service['id']; ?>">
              <div class="service-icon-wrapper">
                <i class="<?php echo htmlspecialchars($service['icon']); ?> service-icon"></i>
              </div>
              <div class="service-content">
                <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                <?php echo $service['content']; ?>
                <div class="service-action">
                  <a href="form-kontak.php?layanan=<?php echo $service['id']; ?>&title=<?php echo urlencode($service['title']); ?>" class="btn btn-primary mt-3">
                    <i class="ri-chat-3-line me-2"></i>Konsultasi
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center">
          <p>Belum ada data layanan.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<style>
  /* Modern Service Card Styles */
  .services-section {
    padding: 80px 0;
    background-color: var(--bg-light);
    position: relative;
  }
  
  .service-card {
    background-color: rgba(255, 255, 255, 0.85);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: var(--radius-lg);
    padding: 30px;
    height: 100%;
    transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1), box-shadow 0.4s ease;
    position: relative;
    z-index: 1;
    overflow: hidden;
    box-shadow: var(--shadow-sm);
    border: 1px solid rgba(255, 255, 255, 0.3);
  }
  
  .service-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    z-index: 1;
    transform: scaleX(0);
    transform-origin: right;
    transition: transform 0.5s ease;
  }
  
  .service-card:hover {
    transform: translateY(-15px);
    box-shadow: 0 15px 35px rgba(45, 106, 79, 0.15);
  }
  
  .service-card:hover::before {
    transform: scaleX(1);
    transform-origin: left;
  }
  
  .service-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(to top, rgba(255,255,255,0.08), transparent);
    z-index: -1;
    opacity: 0;
    transition: opacity 0.5s ease;
  }
  
  .service-card:hover::after {
    opacity: 1;
  }
  
  .service-icon-wrapper {
    margin-bottom: 25px;
    display: flex;
    justify-content: center;
  }
  
  .service-icon {
    font-size: 2.5rem;
    color: var(--primary-color);
    width: 80px;
    height: 80px;
    line-height: 80px;
    text-align: center;
    background: linear-gradient(135deg, rgba(46, 139, 87, 0.1), rgba(46, 139, 87, 0.05));
    border-radius: 50%;
    transition: all 0.5s ease;
    position: relative;
    z-index: 1;
  }
  
  .service-icon::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    padding: 3px;
    background: linear-gradient(135deg, var(--primary-light), transparent);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    opacity: 0;
    transition: opacity 0.5s ease;
  }
  
  .service-card:hover .service-icon::before {
    opacity: 1;
  }
  
  .service-card:hover .service-icon {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
    color: white;
    transform: scale(1.1) rotate(10deg);
    box-shadow: 0 10px 20px rgba(45, 106, 79, 0.2);
  }
  
  .service-content {
    text-align: center;
    transition: transform 0.3s ease;
  }
  
  .service-card:hover .service-content {
    transform: translateY(-5px);
  }
  
  .service-content h4 {
    font-size: 1.4rem;
    font-weight: 700;
    margin-bottom: 15px;
    color: var(--primary-dark);
    position: relative;
    display: inline-block;
  }
  
  .service-content h4::after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: -5px;
    width: 0;
    height: 2px;
    background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
    transform: translateX(-50%);
    transition: width 0.4s ease;
    opacity: 0;
  }
  
  .service-card:hover .service-content h4::after {
    width: 80%;
    opacity: 1;
  }
  
  .service-content p {
    color: var(--text-medium);
    margin-bottom: 20px;
    font-size: 0.95rem;
    transition: color 0.3s ease;
  }
  
  .service-card:hover .service-content p {
    color: var(--primary-dark);
  }
  
  .service-action {
    margin-top: 25px;
    position: relative;
    z-index: 2;
  }
  
  .service-action .btn {
    padding: 0.7rem 1.8rem;
    font-weight: 500;
    border-radius: var(--radius-md);
    transition: all 0.4s ease;
    position: relative;
    overflow: hidden;
    border: none;
    background: var(--primary-color);
    color: white;
  }
  
  .service-action .btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0));
    z-index: -1;
    transform: translateX(-100%);
    transition: transform 0.6s ease;
  }
  
  .service-action .btn:hover::before {
    transform: translateX(0);
  }
  
  .service-action .btn:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(45, 106, 79, 0.25);
    background: var(--primary-dark);
  }
  
  .service-action .btn i {
    transition: transform 0.3s ease;
    display: inline-block;
  }
  
  .service-action .btn:hover i {
    transform: translateX(3px);
  }

  /* Dark mode support */
  .dark-mode .service-card {
    background-color: rgba(42, 42, 42, 0.8);
    border: 1px solid var(--dark-border);
  }
  
  .dark-mode .service-content h4 {
    color: var(--dark-text);
  }
  
  .dark-mode .service-content p {
    color: var(--dark-text-muted);
  }
  
  .dark-mode .service-card:hover .service-content p {
    color: var(--secondary-light);
  }
  
  /* Animation */
  @keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(45, 106, 79, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(45, 106, 79, 0); }
    100% { box-shadow: 0 0 0 0 rgba(45, 106, 79, 0); }
  }
  
  .service-action .btn {
    animation: pulse 2s infinite;
  }
  
  .service-action .btn:hover {
    animation: none;
  }
  
  /* Responsive Design */
  @media (max-width: 768px) {
    .service-card {
      padding: 25px;
    }
    
    .service-icon {
      width: 70px;
      height: 70px;
      line-height: 70px;
      font-size: 2.2rem;
    }
    
    .service-content h4 {
      font-size: 1.3rem;
    }
  }
</style>

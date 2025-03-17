<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Ambil pengaturan kontak dari database
$contact_settings = [];
$contact_keys = ['site_address', 'site_phone', 'site_email', 'office_hours', 
                'social_facebook', 'social_twitter', 'social_instagram', 'social_youtube'];

try {
    $placeholders = implode(',', array_fill(0, count($contact_keys), '?'));
    $stmt = $pdo->prepare("SELECT setting_key, setting_value FROM settings WHERE setting_key IN ($placeholders)");
    $stmt->execute($contact_keys);
    
    while ($row = $stmt->fetch()) {
        $contact_settings[$row['setting_key']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    // Gagal mengambil data, gunakan nilai default
}

// Pastikan $contact_settings adalah array
if (!is_array($contact_settings)) {
    $contact_settings = [];
}
?>

<!-- Kontak Section -->
<section id="kontak" class="contact-section">
  <div class="container">
    <div class="section-header" data-aos="fade-up">
      <h2>Hubungi Kami</h2>
      <p class="section-subheading">
        Silakan hubungi kami untuk informasi lebih lanjut
      </p>
    </div>

    <div class="row gy-4">
      <!-- Informasi Kontak -->
      <div class="col-lg-4" data-aos="fade-up">
        <div class="contact-card">
          <div class="card-icon">
            <i class="ri-map-pin-line"></i>
          </div>
          <h3>Lokasi Kantor</h3>
          <p><?php echo htmlspecialchars($contact_settings['site_address'] ?? 'Jl. Hayam Wuruk No. 9, Bojonegoro, Jawa Timur'); ?></p>
        </div>
      </div>

      <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
        <div class="contact-card">
          <div class="card-icon">
            <i class="ri-phone-line"></i>
          </div>
          <h3>Hubungi Kami</h3>
          <p>Telepon: <?php echo htmlspecialchars($contact_settings['site_phone'] ?? '(0353) 123456'); ?></p>
          <p>Email: <?php echo htmlspecialchars($contact_settings['site_email'] ?? 'info@cdk-bojonegoro.jatimprov.go.id'); ?></p>
        </div>
      </div>

      <div class="col-lg-4" data-aos="fade-up" data-aos-delay="200">
        <div class="contact-card">
          <div class="card-icon">
            <i class="ri-time-line"></i>
          </div>
          <h3>Jam Operasional</h3>
          <p><?php echo htmlspecialchars($contact_settings['office_hours'] ?? 'Senin - Jumat: 08:00 - 16:00 WIB'); ?></p>
        </div>
      </div>
    </div>

    <!-- Social Media Links -->
    <div class="row mt-5" data-aos="fade-up">
      <div class="col-12">
        <div class="social-links-card">
          <h3>Ikuti Kami</h3>
          <div class="social-icons">
            <?php if (!empty($contact_settings['social_facebook'])): ?>
              <a href="<?php echo htmlspecialchars($contact_settings['social_facebook']); ?>" target="_blank" class="facebook">
                <i class="ri-facebook-fill"></i>
              </a>
            <?php endif; ?>
            
            <?php if (!empty($contact_settings['social_twitter'])): ?>
              <a href="<?php echo htmlspecialchars($contact_settings['social_twitter']); ?>" target="_blank" class="twitter">
                <i class="ri-twitter-fill"></i>
              </a>
            <?php endif; ?>
            
            <?php if (!empty($contact_settings['social_instagram'])): ?>
              <a href="<?php echo htmlspecialchars($contact_settings['social_instagram']); ?>" target="_blank" class="instagram">
                <i class="ri-instagram-fill"></i>
              </a>
            <?php endif; ?>
            
            <?php if (!empty($contact_settings['social_youtube'])): ?>
              <a href="<?php echo htmlspecialchars($contact_settings['social_youtube']); ?>" target="_blank" class="youtube">
                <i class="ri-youtube-fill"></i>
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <!-- CTA untuk Form Kontak -->
    <div class="row mt-5" data-aos="fade-up">
      <div class="col-12 text-center">
        <div class="cta-card">
          <h3>Punya pertanyaan atau pesan?</h3>
          <p style="color: white;">Kirimkan pesan Anda melalui form kontak kami dan kami akan segera merespons.</p>
          <a href="form-kontak.php" class="btn btn-primary btn-lg">
            <i class="ri-mail-send-line me-2"></i> Kirim Pesan
          </a>
        </div>
      </div>
    </div>
    
    <!-- Peta Lokasi -->
    <div class="row mt-5">
      <div class="col-12" data-aos="fade-up">
        <div class="location-map-card">
          <h3>Lokasi Kami</h3>
          <div id="contactMap" style="height: 450px;"></div>
        </div>
      </div>
    </div>
  </div>
</section>

<style>
/* Custom styles for contact section */
.contact-section {
  padding: 80px 0;
  background-color: #f9f9f9;
}

.contact-card {
  background: #fff;
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
  height: 100%;
  transition: all 0.3s ease;
  text-align: center;
}

.contact-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
}

.card-icon {
  margin-bottom: 20px;
}

.card-icon i {
  color: #2e7d32;
  font-size: 48px;
}

.contact-card h3 {
  font-size: 22px;
  font-weight: 700;
  color: #1b4332;
  margin-bottom: 15px;
}

.contact-card p {
  color: #555;
  margin-bottom: 8px;
}

.social-links-card {
  background: #fff;
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
  text-align: center;
}

.social-links-card h3 {
  font-size: 22px;
  font-weight: 700;
  color: #1b4332;
  margin-bottom: 20px;
}

.social-icons {
  display: flex;
  justify-content: center;
  gap: 15px;
}

.social-icons a {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 50px;
  height: 50px;
  background-color: #2e7d32;
  color: white;
  border-radius: 50%;
  font-size: 22px;
  transition: all 0.3s ease;
  text-decoration: none;
}

.social-icons a:hover {
  transform: translateY(-5px);
  background-color: #1b4332;
}

.cta-card {
  background: linear-gradient(135deg, #3a8f3f, #1b5e20);
  padding: 40px;
  border-radius: 15px;
  box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
  color: white;
  text-align: center;
}

.cta-card h3 {
  font-size: 28px;
  font-weight: 700;
  margin-bottom: 15px;
}

.cta-card p {
  font-size: 18px;
  margin-bottom: 25px;
  opacity: 0.9;
}

.cta-card .btn-primary {
  background-color: white;
  color: #2e7d32;
  font-weight: 600;
  padding: 12px 30px;
  border: none;
  transition: all 0.3s ease;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.cta-card .btn-primary:hover {
  background-color: #f0f0f0;
  transform: translateY(-3px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.location-map-card {
  background: #fff;
  padding: 30px;
  border-radius: 15px;
  box-shadow: 0 5px 25px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.location-map-card h3 {
  font-size: 22px;
  font-weight: 700;
  color: #1b4332;
  margin-bottom: 20px;
  text-align: center;
}

#contactMap {
  border-radius: 10px;
  overflow: hidden;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Inisialisasi peta lokasi
  const contactMap = L.map('contactMap').setView([-7.1507, 111.8871], 15);
  
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors'
  }).addTo(contactMap);
  
  // Tambahkan marker lokasi kantor dengan popup yang lebih informatif
  const marker = L.marker([-7.1507, 111.8871]).addTo(contactMap);
  
  // Buat konten popup yang lebih informatif
  const popupContent = `
    <div style="text-align: center; width: 200px;">
      <h5 style="font-weight: bold; margin-bottom: 5px;">CDK Wilayah Bojonegoro</h5>
      <p style="margin-bottom: 5px;">${contactMap.getZoom() >= 14 ? '<?php echo addslashes(htmlspecialchars($contact_settings['site_address'] ?? 'Jl. Hayam Wuruk No. 9, Bojonegoro')); ?>' : 'Kantor Cabang Dinas Kehutanan'}</p>
      <p style="margin-bottom: 5px;"><i class="ri-phone-line"></i> <?php echo addslashes(htmlspecialchars($contact_settings['site_phone'] ?? '(0353) 123456')); ?></p>
      <a href="#" onclick="window.open('https://maps.google.com/?q=-7.1507,111.8871', '_blank')">Buka di Google Maps</a>
    </div>
  `;
  
  marker.bindPopup(popupContent).openPopup();
  
  // Tambahkan fitur zoom dan batas area
  contactMap.on('zoomend', function() {
    if (contactMap.getZoom() < 14) {
      marker.closePopup();
    } else {
      marker.openPopup();
    }
  });
});
</script>
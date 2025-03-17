<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Ambil data galeri dari database
$gallery_items = getGallery(6);

// Pastikan $gallery_items adalah array
if (!is_array($gallery_items)) {
    $gallery_items = [];
}

// Dapatkan kategori unik
$categories = [];
if (!empty($gallery_items)) {
    foreach ($gallery_items as $item) {
        if (!in_array($item['category'], $categories)) {
            $categories[] = $item['category'];
        }
    }
}
?>

<!-- Gallery Section -->
<section id="galeri" class="gallery-section glass-card">
  <div class="container">
    <div class="section-header text-center" data-aos="fade-up">
      <h2>Galeri Kegiatan</h2>
      <p class="section-subheading">Dokumentasi program dan kegiatan kehutanan</p>
    </div>

    <!-- Gallery Filter -->
    <div class="gallery-filter d-flex justify-content-center" data-aos="fade-up">
      <button class="btn btn-outline-success active" data-filter="all">Semua</button>
      <?php foreach ($categories as $category): ?>
        <button class="btn btn-outline-success" data-filter="<?php echo htmlspecialchars($category); ?>">
          <?php echo ucfirst(htmlspecialchars($category)); ?>
        </button>
      <?php endforeach; ?>
    </div>

    <!-- Gallery Items -->
    <div class="row g-4 gallery-container">
      <?php if (count($gallery_items) > 0): ?>
        <?php foreach ($gallery_items as $index => $item): ?>
          <!-- Item <?php echo htmlspecialchars($item['category']); ?> -->
          <div class="col-lg-4 col-md-6 gallery-item <?php echo htmlspecialchars($item['category']); ?>" data-aos="fade-up" <?php echo ($index > 0) ? 'data-aos-delay="' . (($index % 3) * 100) . '"' : ''; ?>>
            <div class="gallery-card">
              <div class="gallery-image">
                <img src="uploads/galeri/<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                <div class="gallery-overlay">
                  <a href="uploads/galeri/<?php echo htmlspecialchars($item['image']); ?>" class="gallery-popup">
                    <i class="ri-zoom-in-line"></i>
                  </a>
                </div>
              </div>
              <div class="gallery-content">
                <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                <p><?php echo htmlspecialchars($item['description']); ?></p>
                <div class="gallery-meta">
                  <span><i class="ri-calendar-line"></i> <?php echo formatDateIndo($item['event_date']); ?></span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-center">
          <p>Belum ada data galeri.</p>
        </div>
      <?php endif; ?>
    </div>
    
    <?php if (count($gallery_items) > 0): ?>
    <div class="text-center mt-4">
      <a href="index.php?page=galeri&view=all" class="btn btn-outline-success">
        Lihat Semua Foto <i class="ri-arrow-right-line"></i>
      </a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- Script untuk gallery filter dan popup -->
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Gallery Filter
  const filterButtons = document.querySelectorAll('.gallery-filter button');
  const galleryItems = document.querySelectorAll('.gallery-item');
  
  filterButtons.forEach(button => {
    button.addEventListener('click', function() {
      // Remove active class from all buttons
      filterButtons.forEach(btn => btn.classList.remove('active'));
      
      // Add active class to clicked button
      this.classList.add('active');
      
      // Get filter value
      const filterValue = this.getAttribute('data-filter');
      
      // Show/hide gallery items
      galleryItems.forEach(item => {
        if (filterValue === 'all' || item.classList.contains(filterValue)) {
          item.style.display = 'block';
        } else {
          item.style.display = 'none';
        }
      });
    });
  });
  
  // Gallery Popup
  const galleryPopups = document.querySelectorAll('.gallery-popup');
  const modal = document.getElementById('imageModal');
  const modalImage = document.getElementById('modalImage');
  const closeModal = document.querySelector('.modal-close');
  
  galleryPopups.forEach(popup => {
    popup.addEventListener('click', function(e) {
      e.preventDefault();
      const imageSrc = this.getAttribute('href');
      modalImage.src = imageSrc;
      modal.style.display = 'flex';
    });
  });
  
  // Close modal
  if (closeModal) {
    closeModal.addEventListener('click', function() {
      modal.style.display = 'none';
    });
  }
  
  // Close modal when clicking outside the image
  window.addEventListener('click', function(e) {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });
});
</script>

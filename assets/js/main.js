// Utility Functions
function debounce(func, wait) {
  let timeout;
  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout);
      func(...args);
    };
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
  };
}

// Lazy load scripts
function loadScriptAsync(src, callback) {
  const script = document.createElement("script");
  script.src = src;
  script.async = true;

  if (callback) {
    script.onload = callback;
  }

  document.head.appendChild(script);
  return script;
}

// Global error handler
window.addEventListener("error", function (event) {
  console.error("Global error caught:", event.error || event.message);
  hideLoadingOverlay();

  // Show error message to user
  const errorMessage = document.createElement("div");
  errorMessage.className = "alert alert-danger fixed-top m-3";
  errorMessage.style.zIndex = "9999";
  errorMessage.innerHTML = `
    <strong>Terjadi kesalahan:</strong> 
    <p>${event.error ? event.error.message : event.message}</p>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  document.body.appendChild(errorMessage);

  // Auto-remove after 10 seconds
  setTimeout(() => {
    if (document.body.contains(errorMessage)) {
      document.body.removeChild(errorMessage);
    }
  }, 10000);

  return false;
});

// Error Handler
const handleError = (error) => {
  console.error("An error occurred:", error);
  const errorMessage = document.createElement("div");
  errorMessage.className = "alert alert-danger";
  errorMessage.textContent = "Terjadi kesalahan. Silakan coba lagi nanti.";
  document.body.appendChild(errorMessage);
  setTimeout(() => errorMessage.remove(), 5000);
};

// Set a timeout to hide loading overlay after 5 seconds even if page doesn't fully load
window.addEventListener("load", function () {
  hideLoadingOverlay();
});

// Fallback: Hide loading overlay after 5 seconds even if 'load' event doesn't fire
setTimeout(hideLoadingOverlay, 5000);

function hideLoadingOverlay() {
  const loadingOverlay = document.querySelector(".loading-overlay");
  if (loadingOverlay) {
    loadingOverlay.style.display = "none";
  }
}

// Initialize AOS
AOS.init({
  duration: 800,
  easing: "ease-in-out",
  once: true,
  mirror: false,
});

// Navbar Scroll Effect
const navbar = document.querySelector(".navbar");
const handleScroll = debounce(() => {
  if (window.scrollY > 50) {
    navbar.classList.add("navbar-scrolled");
  } else {
    navbar.classList.remove("navbar-scrolled");
  }
}, 100);

window.addEventListener("scroll", handleScroll);

// Smooth Scrolling for Anchor Links
document.addEventListener("DOMContentLoaded", function () {
  // Get all links that have hash (#) in them
  const anchorLinks = document.querySelectorAll('a[href*="#"]:not([href="#"])');

  anchorLinks.forEach((link) => {
    link.addEventListener("click", function (e) {
      // Only prevent default if the link is to a section on this page
      const href = this.getAttribute("href");
      const isInternalLink =
        href.startsWith("#") || href.includes(window.location.pathname + "#");

      if (isInternalLink) {
        e.preventDefault();

        // Get the target section id
        let targetId;
        if (href.startsWith("#")) {
          targetId = href;
        } else {
          targetId = "#" + href.split("#")[1];
        }

        // Find the target element
        const targetElement = document.querySelector(targetId);

        if (targetElement) {
          // Calculate position to scroll to (accounting for navbar height)
          const navbarHeight = document.querySelector(".navbar").offsetHeight;
          const targetPosition =
            targetElement.getBoundingClientRect().top +
            window.pageYOffset -
            navbarHeight;

          // Smooth scroll to target
          window.scrollTo({
            top: targetPosition,
            behavior: "smooth",
          });

          // Update URL hash without scrolling
          history.pushState(null, null, targetId);
        }
      }
    });
  });

  // Handle initial hash in URL
  if (window.location.hash) {
    setTimeout(() => {
      const targetElement = document.querySelector(window.location.hash);
      if (targetElement) {
        const navbarHeight = document.querySelector(".navbar").offsetHeight;
        const targetPosition =
          targetElement.getBoundingClientRect().top +
          window.pageYOffset -
          navbarHeight;

        window.scrollTo({
          top: targetPosition,
          behavior: "smooth",
        });
      }
    }, 300);
  }
});

// Initialize Map
document.addEventListener("DOMContentLoaded", function () {
  try {
    // Pemeriksaan elemen map sebelum inisialisasi
    const mapElement = document.getElementById("map");
    if (!mapElement) {
      console.log("Map container not found. Skipping map initialization.");
      return; // Keluar dari fungsi jika elemen map tidak ditemukan
    }

    var map = L.map("map").setView([-7.1507, 111.8871], 8);
    // ...rest of code
  } catch (error) {
    handleError(error);
  }
});

// Contact Form Handler
const contactForm = document.querySelector(".contact-form");
if (contactForm) {
  contactForm.addEventListener("submit", async function (e) {
    e.preventDefault();

    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    try {
      // Show loading state
      submitBtn.innerHTML =
        '<span class="spinner-border spinner-border-sm"></span> Mengirim...';
      submitBtn.disabled = true;

      // Get form data
      const formData = {
        nama: document.getElementById("nama").value,
        email: document.getElementById("email").value,
        telepon: document.getElementById("telepon").value,
        kategori: document.getElementById("kategori").value,
        pesan: document.getElementById("pesan").value,
      };

      // Simulate API call
      await new Promise((resolve) => setTimeout(resolve, 1500));

      // Reset form and show success
      contactForm.reset();
      const alert = document.createElement("div");
      alert.className = "alert alert-success mt-3";
      alert.innerHTML =
        "Pesan Anda telah terkirim. Kami akan segera menghubungi Anda.";
      contactForm.appendChild(alert);
      setTimeout(() => alert.remove(), 5000);
    } catch (error) {
      handleError(error);
    } finally {
      submitBtn.innerHTML = originalText;
      submitBtn.disabled = false;
    }
  });
}

// Forest Statistics Chart
const forestStats = document.getElementById("forestStats");
if (forestStats) {
  try {
    new Chart(forestStats, {
      type: "bar",
      data: {
        labels: [
          "Hutan Produksi",
          "Hutan Rakyat",
          "Area Rehabilitasi",
          "Perhutanan Sosial",
        ],
        datasets: [
          {
            label: "Luas Area (Ha)",
            data: [45000, 15000, 5000, 10000],
            backgroundColor: [
              "rgba(46, 125, 50, 0.8)",
              "rgba(76, 175, 80, 0.8)",
              "rgba(129, 199, 132, 0.8)",
              "rgba(27, 94, 32, 0.8)",
            ],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: function (value) {
                return value.toLocaleString() + " Ha";
              },
            },
          },
        },
      },
    });
  } catch (error) {
    handleError(error);
  }
}

// Progress Bar Animation with Intersection Observer
const animateProgress = () => {
  document.querySelectorAll(".progress-bar").forEach((bar) => {
    const width = bar.style.width;
    bar.style.width = "0";
    requestAnimationFrame(() => {
      bar.style.width = width;
    });
  });
};

const progressObserver = new IntersectionObserver(
  (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        animateProgress();
        progressObserver.unobserve(entry.target);
      }
    });
  },
  { threshold: 0.5 }
);

document
  .querySelectorAll(".progress-list")
  .forEach((el) => progressObserver.observe(el));

// Back to Top Button
const backToTopButton = document.getElementById("backToTop");
window.addEventListener(
  "scroll",
  debounce(() => {
    if (window.scrollY > 300) {
      backToTopButton.classList.add("show");
    } else {
      backToTopButton.classList.remove("show");
    }
  }, 100)
);

backToTopButton.addEventListener("click", () => {
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });
});

// Swiper Initialization
document.addEventListener("DOMContentLoaded", function () {
  try {
    const swipers = document.querySelectorAll(".swiper-container");
    swipers.forEach((swiperElement) => {
      new Swiper(swiperElement, {
        loop: true,
        autoplay: {
          delay: 5000,
          disableOnInteraction: false,
        },
        pagination: {
          el: ".swiper-pagination",
          clickable: true,
        },
        navigation: {
          nextEl: ".swiper-button-next",
          prevEl: ".swiper-button-prev",
        },
      });
    });
  } catch (error) {
    handleError(error);
  }
});

// Image Preview Modal
function initializeImagePreviews() {
  const galleryThumbnails = document.querySelectorAll(".gallery-thumbnail");

  galleryThumbnails.forEach((thumbnail) => {
    thumbnail.addEventListener("click", function () {
      try {
        const imageSrc = this.getAttribute("data-image");
        const modal = document.createElement("div");
        modal.className = "modal-preview";
        modal.innerHTML = `
                  <span class="modal-close">&times;</span>
                  <img src="${imageSrc}" alt="Preview">
              `;

        document.body.appendChild(modal);
        document.body.style.overflow = "hidden";

        const closeModal = () => {
          modal.remove();
          document.body.style.overflow = "auto";
        };

        modal
          .querySelector(".modal-close")
          .addEventListener("click", closeModal);
        modal.addEventListener("click", (e) => {
          if (e.target === modal) closeModal();
        });

        const handleKeyDown = (e) => {
          if (e.key === "Escape") {
            closeModal();
            document.removeEventListener("keydown", handleKeyDown);
          }
        };

        document.addEventListener("keydown", handleKeyDown);
      } catch (error) {
        handleError(error);
      }
    });
  });
}

// Lazy Loading Images
const lazyLoadImages = () => {
  const imageObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const img = entry.target;
        img.src = img.dataset.src;
        img.classList.remove("lazy");
        observer.unobserve(img);
      }
    });
  });

  document.querySelectorAll("img[data-src]").forEach((img) => {
    imageObserver.observe(img);
  });
};

// Initialize all functions when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  try {
    // Hide loading overlay
    const loadingOverlay = document.querySelector(".loading-overlay");
    if (loadingOverlay) {
      loadingOverlay.style.display = "none";
    }

    // Existing initializations
    initializeImagePreviews();
    lazyLoadImages();
    initCounters();

    console.log("DOM loaded, initializing charts");

    // Initialize charts
    initializeCharts();

    // Check if monitoring chart element exists and initialize it
    if (document.getElementById("achievementChart")) {
      console.log("Achievement chart element found, initializing");
      initializeMonitoringCharts();
    } else {
      console.log("Achievement chart element not found on initial load");
    }

    // Listen for chart data ready event from statistik.php
    document.addEventListener("chartDataReady", function () {
      console.log("chartDataReady event received");
      // Reinitialize charts with new data
      initializeCharts(true);
    });

    // Listen for chart data ready event from monitoring.php
    document.addEventListener("monitoringChartDataReady", function () {
      console.log("monitoringChartDataReady event received");
      // Reinitialize monitoring charts with new data
      initializeMonitoringCharts(true);
    });
  } catch (error) {
    // Hide loading overlay even if there's an error
    const loadingOverlay = document.querySelector(".loading-overlay");
    if (loadingOverlay) {
      loadingOverlay.style.display = "none";
    }
    console.error("Initialization error:", error);
  }
});

// Function to initialize charts
function initializeCharts(useCustomData = false) {
  const forestAreaChart = document.getElementById("forestAreaChart");
  const forestProductionChart = document.getElementById(
    "forestProductionChart"
  );

  // Destroy existing charts if they exist
  if (window.forestAreaChartInstance) {
    window.forestAreaChartInstance.destroy();
  }
  if (window.forestProductionChartInstance) {
    window.forestProductionChartInstance.destroy();
  }

  // Only initialize if elements exist
  if (forestAreaChart) {
    // Get data from global variable if available
    const labels =
      useCustomData && window.forestAreaChartData
        ? window.forestAreaChartData.labels
        : ["Hutan Produksi", "Hutan Lindung", "Hutan Rakyat", "Hutan Kota"];

    const data =
      useCustomData && window.forestAreaChartData
        ? window.forestAreaChartData.data
        : [45000, 25000, 15000, 5000];

    // Create new chart
    window.forestAreaChartInstance = new Chart(forestAreaChart, {
      type: "doughnut",
      data: {
        labels: labels,
        datasets: [
          {
            data: data,
            backgroundColor: [
              "rgba(46, 125, 50, 0.8)",
              "rgba(56, 142, 60, 0.8)",
              "rgba(76, 175, 80, 0.8)",
              "rgba(129, 199, 132, 0.8)",
            ],
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: "bottom",
          },
        },
      },
    });
  }

  if (forestProductionChart) {
    // Get data from global variable if available
    const labels =
      useCustomData && window.forestProductionChartData
        ? window.forestProductionChartData.labels
        : ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun"];

    const data =
      useCustomData && window.forestProductionChartData
        ? window.forestProductionChartData.data
        : [1200, 1900, 1500, 1800, 2200, 1600];

    // Create new chart
    window.forestProductionChartInstance = new Chart(forestProductionChart, {
      type: "bar",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Produksi (Ton)",
            data: data,
            backgroundColor: "rgba(46, 125, 50, 0.8)",
            borderColor: "rgba(46, 125, 50, 1)",
            borderWidth: 1,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
          },
        },
      },
    });
  }

  // Add event listeners for chart type buttons
  document.querySelectorAll("[data-chart-type]").forEach((button) => {
    // Remove existing event listeners to prevent duplicates
    const newButton = button.cloneNode(true);
    button.parentNode.replaceChild(newButton, button);

    newButton.addEventListener("click", function () {
      const chartType = this.getAttribute("data-chart-type");
      const targetChart = this.getAttribute("data-target");

      // Update active state on buttons
      this.closest(".card-tools")
        .querySelectorAll("button")
        .forEach((btn) => {
          btn.classList.remove("active");
        });
      this.classList.add("active");

      // Update chart type
      if (targetChart === "forestAreaChart" && window.forestAreaChartInstance) {
        window.forestAreaChartInstance.config.type = chartType;
        window.forestAreaChartInstance.update();
      } else if (
        targetChart === "forestProductionChart" &&
        window.forestProductionChartInstance
      ) {
        window.forestProductionChartInstance.config.type = chartType;
        window.forestProductionChartInstance.update();
      }
    });
  });

  // Event listener for year filter
  const loadStatisticsBtn = document.getElementById("loadStatistics");
  if (loadStatisticsBtn) {
    // Remove existing event listeners to prevent duplicates
    const newBtn = loadStatisticsBtn.cloneNode(true);
    loadStatisticsBtn.parentNode.replaceChild(newBtn, loadStatisticsBtn);

    newBtn.addEventListener("click", function () {
      const year = document.getElementById("statisticYear").value;
      window.location.href = `?page=statistik&year=${year}`;
    });
  }
}

// Function to initialize monitoring charts
function initializeMonitoringCharts(useCustomData = false) {
  const achievementChart = document.getElementById("achievementChart");

  // Debug: Log jika elemen chart tidak ditemukan
  if (!achievementChart) {
    console.log("Achievement chart element not found");
    return;
  }

  console.log("Initializing achievement chart", achievementChart);

  // Destroy existing chart if it exists
  if (window.achievementChartInstance) {
    console.log("Destroying existing achievement chart instance");
    window.achievementChartInstance.destroy();
  }

  // Default data
  const defaultData = {
    labels: [
      "Rehabilitasi Hutan",
      "Perhutanan Sosial",
      "Perlindungan Hutan",
      "Produksi Hasil Hutan",
    ],
    datasets: [
      {
        label: "Persentase Capaian (%)",
        data: [85, 70, 90, 65],
        backgroundColor: ["#2d6a4f", "#40916c", "#52b788", "#74c69d"],
        borderWidth: 0,
      },
    ],
  };

  // Use custom data if available
  let chartData = defaultData;
  if (useCustomData && window.achievementChartData) {
    console.log(
      "Using custom achievement chart data:",
      window.achievementChartData
    );

    // Pastikan data tidak kosong
    if (
      window.achievementChartData.labels &&
      window.achievementChartData.labels.length > 0 &&
      window.achievementChartData.datasets &&
      window.achievementChartData.datasets[0].data &&
      window.achievementChartData.datasets[0].data.length > 0
    ) {
      chartData = window.achievementChartData;
    } else {
      console.warn(
        "Custom achievement data is empty or invalid, using default data"
      );
    }
  } else {
    console.log("Using default achievement chart data");
  }

  try {
    // Create new chart
    window.achievementChartInstance = new Chart(achievementChart, {
      type: "bar",
      data: chartData,
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            max: 100,
            ticks: {
              callback: function (value) {
                return value + "%";
              },
            },
          },
        },
        plugins: {
          legend: {
            display: false,
          },
        },
      },
    });
    console.log("Achievement chart initialized successfully");
  } catch (error) {
    console.error("Error initializing achievement chart:", error);
  }
}

// Cleanup function
window.addEventListener("beforeunload", () => {
  // Cleanup event listeners to prevent memory leaks
  window.removeEventListener("scroll", handleScroll);
});

// Gallery Functionality
const initGallery = () => {
  const galleryContainer = document.querySelector(".gallery-container");
  const filterBtns = document.querySelectorAll(".gallery-filter .btn");

  if (!galleryContainer || filterBtns.length === 0) return;

  filterBtns.forEach((btn) => {
    btn.addEventListener("click", function () {
      // Remove active class from all buttons
      filterBtns.forEach((btn) => btn.classList.remove("active"));
      // Add active class to clicked button
      this.classList.add("active");

      const filterValue = this.getAttribute("data-filter");
      const items = galleryContainer.querySelectorAll(".gallery-item");

      items.forEach((item) => {
        if (filterValue === "all" || item.classList.contains(filterValue)) {
          item.style.display = "block";
          // Optional: Add fade-in animation
          item.style.opacity = "0";
          setTimeout(() => {
            item.style.opacity = "1";
          }, 100);
        } else {
          item.style.display = "none";
        }
      });
    });
  });
};

// Gallery Popup
const initGalleryPopup = () => {
  const galleryPopups = document.querySelectorAll(".gallery-popup");
  if (!galleryPopups.length) return;

  galleryPopups.forEach((popup) => {
    popup.addEventListener("click", function (e) {
      e.preventDefault();
      const imageUrl = this.getAttribute("href");

      // Create modal
      const modal = document.createElement("div");
      modal.className = "modal-preview";
      modal.innerHTML = `
        <span class="modal-close">&times;</span>
        <img src="${imageUrl}" alt="Preview">
      `;

      // Add to body
      document.body.appendChild(modal);
      document.body.style.overflow = "hidden";

      // Close modal events
      modal.addEventListener("click", function () {
        this.remove();
        document.body.style.overflow = "auto";
      });
    });
  });
};

// Initialize gallery when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  try {
    initGallery();
    initGalleryPopup();
  } catch (error) {
    console.log("Gallery initialization error:", error);
  }
});

// Gallery Modal
document.addEventListener("DOMContentLoaded", function () {
  const modal = document.getElementById("imageModal");
  const modalImg = document.getElementById("modalImage");
  const closeBtn = document.querySelector(".modal-close");

  // Menambahkan event listener ke semua gallery popup links
  document.querySelectorAll(".gallery-popup").forEach((item) => {
    item.addEventListener("click", function (e) {
      e.preventDefault();
      modal.style.display = "flex";
      modalImg.src = this.getAttribute("href");
    });
  });

  // Menutup modal ketika tombol close diklik
  closeBtn.addEventListener("click", function () {
    modal.style.display = "none";
  });

  // Menutup modal ketika mengklik di luar gambar
  modal.addEventListener("click", function (e) {
    if (e.target === modal) {
      modal.style.display = "none";
    }
  });

  // Menutup modal dengan tombol ESC
  document.addEventListener("keydown", function (e) {
    if (e.key === "Escape" && modal.style.display === "flex") {
      modal.style.display = "none";
    }
  });
});

// Counter Animation
const initCounters = () => {
  const counters = document.querySelectorAll("[data-counter]");

  const animateCounter = (counter) => {
    const target = parseInt(counter.getAttribute("data-counter"));
    let current = 0;
    const increment = target / 50; // Adjust speed here
    const duration = 2000; // 2 seconds
    const step = duration / 50;

    const updateCounter = () => {
      current += increment;
      if (current > target) {
        counter.textContent = target.toLocaleString() + "+";
      } else {
        counter.textContent = Math.floor(current).toLocaleString() + "+";
        requestAnimationFrame(updateCounter);
      }
    };

    updateCounter();
  };

  const observerCallback = (entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        animateCounter(entry.target);
        observer.unobserve(entry.target);
      }
    });
  };

  const observer = new IntersectionObserver(observerCallback, {
    threshold: 0.5,
  });

  counters.forEach((counter) => observer.observe(counter));
};

// Initialize when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  try {
    initCounters();
  } catch (error) {
    console.log("Counter initialization error:", error);
  }
});

// Defer non-critical scripts
document.addEventListener("DOMContentLoaded", function () {
  // Load non-critical scripts after page load
  setTimeout(() => {
    // Load AOS library if not already loaded
    if (typeof AOS === "undefined") {
      loadScriptAsync(
        "https://cdnjs.cloudflare.com/ajax/libs/aos/2.3.4/aos.js",
        function () {
          // Initialize AOS after loading
          AOS.init({
            duration: 800,
            easing: "ease-in-out",
            once: true,
            mirror: false,
          });
        }
      );
    }

    // Load other non-critical scripts
    if (
      typeof ScrollReveal === "undefined" &&
      document.querySelector("[data-scroll]")
    ) {
      loadScriptAsync("https://unpkg.com/scrollreveal");
    }
  }, 1000);
});

// Tambahkan kode ini di main.js
document.addEventListener("DOMContentLoaded", function () {
  // Pastikan elemen peta ada sebelum inisialisasi
  if (document.getElementById("map")) {
    // Inisialisasi peta Leaflet
    var map = L.map("map").setView([-7.150975, 111.8813844], 13);

    // Tambahkan tile layer (OpenStreetMap)
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution:
        '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    }).addTo(map);

    // Tambahkan marker atau elemen lain yang dibutuhkan
    // L.marker([-7.150975, 111.8813844]).addTo(map);
  } else {
    console.log("Map container not found. Skipping map initialization.");
  }

  // Alternatif: Jika elemen peta hanya ada di halaman tertentu
  // Periksa apakah kita berada di halaman yang seharusnya memiliki peta
  if (
    window.location.href.includes("page=peta") ||
    document.querySelector(".peta-section")
  ) {
    console.error(
      "Halaman seharusnya memiliki peta, tapi container #map tidak ditemukan!"
    );
  }
});

// ATAU gunakan pendekatan lazy-loading jika peta hanya dimuat pada kondisi tertentu
function initializeMap() {
  if (!document.getElementById("map")) {
    console.error("Map container not found!");
    return;
  }

  var map = L.map("map").setView([-7.150975, 111.8813844], 13);

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
  }).addTo(map);
}

// Fungsi ini dapat dipanggil ketika tab/section yang berisi peta ditampilkan
// Contoh: document.getElementById('petaTab').addEventListener('click', initializeMap);

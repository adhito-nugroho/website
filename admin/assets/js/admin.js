// Admin Panel JavaScript

(function ($) {
  "use strict"; // Start of use strict

  // Toggle the side navigation
  $("#sidebarToggle, #sidebarToggleTop").on("click", function (e) {
    $("body").toggleClass("sidebar-toggled");
    $(".sidebar").toggleClass("toggled");
    if ($(".sidebar").hasClass("toggled")) {
      $(".sidebar .collapse").collapse("hide");
    }
  });

  // Close any open menu accordions when window is resized below 768px
  $(window).resize(function () {
    if ($(window).width() < 768) {
      $(".sidebar .collapse").collapse("hide");
    }
  });

  // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
  $("body.fixed-nav .sidebar").on(
    "mousewheel DOMMouseScroll wheel",
    function (e) {
      if ($(window).width() > 768) {
        var e0 = e.originalEvent,
          delta = e0.wheelDelta || -e0.detail;
        this.scrollTop += (delta < 0 ? 1 : -1) * 30;
        e.preventDefault();
      }
    }
  );

  // Scroll to top button appear
  $(document).on("scroll", function () {
    var scrollDistance = $(this).scrollTop();
    if (scrollDistance > 100) {
      $(".scroll-to-top").fadeIn();
    } else {
      $(".scroll-to-top").fadeOut();
    }
  });

  // Smooth scrolling using jQuery easing
  $(document).on("click", "a.scroll-to-top", function (e) {
    var $anchor = $(this);
    $("html, body")
      .stop()
      .animate(
        {
          scrollTop: $($anchor.attr("href")).offset().top,
        },
        1000,
        "easeInOutExpo"
      );
    e.preventDefault();
  });

  // Toggle password visibility
  $("#togglePassword").on("click", function () {
    const passwordField = $("#password");
    const passwordFieldType = passwordField.attr("type");

    if (passwordFieldType === "password") {
      passwordField.attr("type", "text");
      $(this).find("i").removeClass("fa-eye").addClass("fa-eye-slash");
    } else {
      passwordField.attr("type", "password");
      $(this).find("i").removeClass("fa-eye-slash").addClass("fa-eye");
    }
  });

  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Initialize popovers
  var popoverTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="popover"]')
  );
  var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl);
  });

  // Auto-dismiss alerts
  window.setTimeout(function () {
    $(".alert-dismissible.fade").alert("close");
  }, 5000);

  // Confirm delete
  $(".btn-delete").on("click", function (e) {
    if (!confirm("Apakah Anda yakin ingin menghapus item ini?")) {
      e.preventDefault();
    }
  });

  // Fix for Bootstrap 5 dropdowns
  const dropdownElementList = document.querySelectorAll(".dropdown-toggle");
  dropdownElementList.forEach((dropdownToggleEl) => {
    new bootstrap.Dropdown(dropdownToggleEl);
  });
  // Fix for Bootstrap 5 modal backdrop
  $(".modal").on("shown.bs.modal", function () {
    if ($(".modal-backdrop").length === 0) {
      $("body").append('<div class="modal-backdrop fade show"></div>');
    }
  });

  $(".modal").on("hidden.bs.modal", function () {
    $(".modal-backdrop").remove();
  });

  // DataTables initialization
  if ($.fn.DataTable) {
    $(".dataTable").DataTable({
      language: {
        url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json",
      },
      responsive: true,
    });
  }

  // Initialize date pickers
  if ($.fn.datepicker) {
    $(".datepicker").datepicker({
      format: "yyyy-mm-dd",
      autoclose: true,
      todayHighlight: true,
      language: "id",
    });
  }

  // Initialize select2
  if ($.fn.select2) {
    $(".select2").select2({
      theme: "bootstrap4",
    });
  }

  // Initialize summernote
  if ($.fn.summernote) {
    $(".summernote").summernote({
      height: 300,
      minHeight: 200,
      maxHeight: 500,
      toolbar: [
        ["style", ["style"]],
        ["font", ["bold", "underline", "clear"]],
        ["color", ["color"]],
        ["para", ["ul", "ol", "paragraph"]],
        ["table", ["table"]],
        ["insert", ["link", "picture"]],
        ["view", ["fullscreen", "codeview", "help"]],
      ],
      callbacks: {
        onImageUpload: function (files) {
          // Custom image upload handler
          for (let i = 0; i < files.length; i++) {
            uploadSummernoteImage(files[i], this);
          }
        },
      },
    });
  }

  // Function to upload images from summernote
  function uploadSummernoteImage(file, editor) {
    const formData = new FormData();
    formData.append("file", file);
    formData.append("csrf_token", $('input[name="csrf_token"]').val());

    $.ajax({
      url: "ajax/upload_image.php",
      method: "POST",
      data: formData,
      processData: false,
      contentType: false,
      success: function (data) {
        if (data.success) {
          $(editor).summernote("insertImage", data.url);
        } else {
          alert("Error uploading image: " + data.message);
        }
      },
      error: function () {
        alert("Error uploading image. Please try again.");
      },
    });
  }
})(jQuery); // End of use strict

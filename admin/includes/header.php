<?php
// Memastikan file ini tidak diakses langsung
if (!defined('ADMIN_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}

// Ambil data admin yang sedang login
$current_admin = getCurrentAdmin();

// Tambahkan definisi fungsi getFlashMessage jika belum ada di functions.php
if (!function_exists('getFlashMessage')) {
    function getFlashMessage($name)
    {
        $message = $_SESSION[$name] ?? '';
        unset($_SESSION[$name]);
        return $message;
    }
}

// Ambil pesan flash
$success_message = getFlashMessage('success_message');
$error_message = getFlashMessage('error_message');
$warning_message = getFlashMessage('warning_message');
$info_message = getFlashMessage('info_message');

// Tentukan halaman aktif
$current_page = basename($_SERVER['PHP_SELF'], '.php');
$current_module = isset($_GET['module']) ? $_GET['module'] : '';
$current_action = isset($_GET['action']) ? $_GET['action'] : '';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Admin Panel'; ?> - CDK Bojonegoro</title>

    <!-- Favicon -->
    <link rel="shortcut icon" href="<?php echo $site_config['base_url']; ?>/assets/images/favicon.ico"
        type="image/x-icon">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom styles -->
    <link href="<?php echo $site_config['admin_url']; ?>/assets/css/admin.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Summernote JS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs5.min.js"></script>
</head>

<body id="page-top">

    <!-- Page Wrapper -->
    <div id="wrapper">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center"
                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">
                <div class="sidebar-brand-icon">
                    <i class="fas fa-tree"></i>
                </div>
                <div class="sidebar-brand-text mx-3">CDK Admin</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li
                class="nav-item <?php echo ($current_page === 'dashboard' || $current_page === 'index') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $site_config['admin_url']; ?>/dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Konten
            </div>

            <!-- Nav Item - Layanan -->
            <li class="nav-item <?php echo (strpos($current_page, 'layanan') !== false) ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $site_config['admin_url']; ?>/modules/layanan/index.php">
                    <i class="fas fa-fw fa-hand-holding-heart"></i>
                    <span>Layanan</span>
                </a>
            </li>

            <!-- Nav Item - Program -->
            <li class="nav-item <?php echo (strpos($current_page, 'program') !== false) ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $site_config['admin_url']; ?>/modules/program/index.php">
                    <i class="fas fa-fw fa-project-diagram"></i>
                    <span>Program</span>
                </a>
            </li>

            <!-- Nav Item - Statistik -->
            <li class="nav-item <?php echo (strpos($current_page, 'statistik') !== false) ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $site_config['admin_url']; ?>/modules/statistik/index.php">
                    <i class="fas fa-fw fa-chart-bar"></i>
                    <span>Statistik</span>
                </a>
            </li>

            <!-- Nav Item - Monitoring -->
            <li class="nav-item <?php echo (strpos($current_page, 'monitoring') !== false) ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $site_config['admin_url']; ?>/modules/monitoring/index.php">
                    <i class="fas fa-fw fa-tasks"></i>
                    <span>Monitoring</span>
                </a>
            </li>

            <!-- Nav Item - Publikasi -->
            <li class="nav-item <?php echo (strpos($current_page, 'publikasi') !== false) ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $site_config['admin_url']; ?>/modules/publikasi/index.php">
                    <i class="fas fa-fw fa-newspaper"></i>
                    <span>Publikasi</span>
                </a>
            </li>

            <!-- Nav Item - Galeri -->
            <li class="nav-item <?php echo (strpos($current_page, 'galeri') !== false) ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $site_config['admin_url']; ?>/modules/galeri/index.php">
                    <i class="fas fa-fw fa-images"></i>
                    <span>Galeri</span>
                </a>
            </li>

            <!-- Nav Item - Pesan -->
            <li class="nav-item <?php echo (strpos($current_page, 'pesan') !== false) ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $site_config['admin_url']; ?>/modules/pesan/index.php">
                    <i class="fas fa-fw fa-envelope"></i>
                    <span>Pesan</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Pengaturan
            </div>

            <!-- Nav Item - Users (hanya untuk administrator) -->
            <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'admin'): ?>
                <li class="nav-item <?php echo (strpos($current_page, 'users') !== false) ? 'active' : ''; ?>">
                    <a class="nav-link" href="<?php echo $site_config['admin_url']; ?>/modules/users/index.php">
                        <i class="fas fa-fw fa-users"></i>
                        <span>Pengguna</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Nav Item - Settings -->
            <li class="nav-item <?php echo ($current_page === 'settings') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $site_config['admin_url']; ?>/settings.php">
                    <i class="fas fa-fw fa-cog"></i>
                    <span>Pengaturan</span>
                </a>
            </li>

            <!-- Nav Item - Profile -->
            <li class="nav-item <?php echo ($current_page === 'profile') ? 'active' : ''; ?>">
                <a class="nav-link" href="<?php echo $site_config['admin_url']; ?>/profile.php">
                    <i class="fas fa-fw fa-user"></i>
                    <span>Profil</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle">
                    <i class="fas fa-angle-left"></i>
                </button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ms-auto">

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="me-2 d-none d-lg-inline text-gray-600 small">
                                    <?php echo htmlspecialchars($current_admin['name'] ?? 'Admin'); ?>
                                </span>
                                <i class="fas fa-user-circle fa-fw"></i>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-end shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="<?php echo $site_config['admin_url']; ?>/profile.php">
                                    <i class="fas fa-user fa-sm fa-fw me-2 text-gray-400"></i>
                                    Profil
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw me-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">

                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo $page_title ?? 'Dashboard'; ?></h1>
                    </div>

                    <!-- Flash Messages -->
                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo $success_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $error_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($warning_message): ?>
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <?php echo $warning_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($info_message): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <?php echo $info_message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
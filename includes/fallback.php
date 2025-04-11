<?php
// Memastikan file ini tidak diakses langsung
if (!defined('BASE_PATH')) {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diperbolehkan');
}
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h2 class="mb-4">Halaman Sedang Dalam Perbaikan</h2>
            <p class="mb-4">Mohon maaf, halaman yang Anda minta sedang dalam perbaikan. Silakan kembali ke halaman utama atau coba lagi nanti.</p>
            <a href="index.php" class="btn btn-success">Kembali ke Beranda</a>
        </div>
    </div>
</div> 
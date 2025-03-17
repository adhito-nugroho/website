<?php
// Definisikan konstanta untuk direktori
define('BASE_PATH', dirname(dirname(dirname(__DIR__))));
define('ADMIN_PATH', dirname(dirname(__DIR__)));

// Mulai session
session_start();

// Load konfigurasi dan fungsi
require_once BASE_PATH . '/includes/config.php';
require_once ADMIN_PATH . '/includes/auth.php';
require_once ADMIN_PATH . '/includes/functions.php';

// Cek login admin
requireLogin();

// Set judul halaman
$page_title = 'Manajemen Publikasi';

// Ambil parameter tab
$tab = isset($_GET['tab']) ? sanitizeInput($_GET['tab']) : 'posts';

// Tambahkan, edit, atau hapus data jika ada request
$message = '';
$message_type = '';

// Proses hapus data
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    try {
        // Remove CSRF token verification
        if ($tab == 'posts') {
            // Periksa apakah publikasi ada
            $stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
            $stmt->execute([$id]);
            $post = $stmt->fetch();

            if (!$post) {
                throw new Exception('Publikasi tidak ditemukan');
            }

            // Hapus gambar jika ada
            if (!empty($post['image'])) {
                deleteImage($post['image'], 'uploads/publikasi');
            }

            // Hapus publikasi
            $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
            $stmt->execute([$id]);

            // Log aktivitas
            logActivity($_SESSION['admin_id'], 'menghapus publikasi', ['post_id' => $id, 'title' => $post['title']]);

            $message = 'Publikasi berhasil dihapus';
            $message_type = 'success';
        } else {
            // Periksa apakah dokumen ada
            $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ?");
            $stmt->execute([$id]);
            $document = $stmt->fetch();

            if (!$document) {
                throw new Exception('Dokumen tidak ditemukan');
            }

            // Hapus file dokumen
            deleteImage($document['filename'], 'uploads/dokumen');

            // Hapus dokumen dari database
            $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ?");
            $stmt->execute([$id]);

            // Log aktivitas
            logActivity($_SESSION['admin_id'], 'menghapus dokumen', ['document_id' => $id, 'title' => $document['title']]);

            $message = 'Dokumen berhasil dihapus';
            $message_type = 'success';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Ambil data dari database
try {
    if ($tab == 'posts') {
        // Ambil data publikasi
        $stmt = $pdo->query("
            SELECT p.*, u.name as author_name 
            FROM posts p 
            LEFT JOIN users u ON p.created_by = u.id 
            ORDER BY p.created_at DESC
        ");
        $posts = $stmt->fetchAll();
    } else {
        // Ambil data dokumen
        $stmt = $pdo->query("
            SELECT d.*, u.name as uploader_name 
            FROM documents d 
            LEFT JOIN users u ON d.created_by = u.id 
            ORDER BY d.upload_date DESC
        ");
        $documents = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    $message = 'Error: ' . $e->getMessage();
    $message_type = 'danger';
    $posts = [];
    $documents = [];
}

// Remove CSRF token generation
// $csrf_token = generateCsrfToken();

// Load header
include_once ADMIN_PATH . '/includes/header.php';
?>

<!-- Content Wrapper -->
<div class="content-wrapper">
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row page-titles">
            <div class="col-md-5 align-self-center">
                <h4 class="text-themecolor"><?php echo $page_title; ?></h4>
            </div>
            <div class="col-md-7 align-self-center text-end">
                <div class="d-flex justify-content-end align-items-center">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a
                                href="<?php echo $site_config['admin_url']; ?>/dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active"><?php echo $page_title; ?></li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo ($tab == 'posts') ? 'active' : ''; ?>"
                    href="index.php?tab=posts">Berita/Artikel</a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($tab == 'documents') ? 'active' : ''; ?>"
                    href="index.php?tab=documents">Dokumen</a>
            </li>
        </ul>

        <!-- Content -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <?php if ($tab == 'posts'): ?>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title">Daftar Berita/Artikel</h4>
                                <a href="add.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Tambah Berita/Artikel
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered datatable">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="10%">Gambar</th>
                                            <th width="30%">Judul</th>
                                            <th width="10%">Kategori</th>
                                            <th width="15%">Penulis</th>
                                            <th width="10%">Tanggal</th>
                                            <th width="10%">Status</th>
                                            <th width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($posts) > 0): ?>
                                            <?php foreach ($posts as $index => $post): ?>
                                                <tr>
                                                    <td><?php echo $index + 1; ?></td>
                                                    <td class="text-center">
                                                        <?php if (!empty($post['image'])): ?>
                                                            <img src="<?php echo $site_config['base_url']; ?>/uploads/publikasi/<?php echo $post['image']; ?>"
                                                                alt="<?php echo htmlspecialchars($post['title']); ?>"
                                                                class="img-thumbnail" style="max-height: 50px;">
                                                        <?php else: ?>
                                                            <span class="badge bg-secondary">No Image</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($post['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($post['category']); ?></td>
                                                    <td><?php echo htmlspecialchars($post['author_name'] ?? 'N/A'); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($post['publish_date'])); ?></td>
                                                    <td class="text-center">
                                                        <?php if ($post['is_active']): ?>
                                                            <span class="badge bg-success">Aktif</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Nonaktif</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="edit.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-info"
                                                            title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="<?php echo $site_config['base_url']; ?>/index.php?page=publikasi&id=<?php echo $post['id']; ?>"
                                                            class="btn btn-sm btn-success" title="Lihat" target="_blank">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="#" class="btn btn-sm btn-danger btn-delete"
                                                            data-id="<?php echo $post['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($post['title']); ?>"
                                                            data-token="<?php echo $csrf_token; ?>" title="Hapus">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center">Tidak ada data berita/artikel</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title">Daftar Dokumen</h4>
                                <a href="add_document.php" class="btn btn-primary">
                                    <i class="fas fa-plus-circle"></i> Tambah Dokumen
                                </a>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-bordered datatable">
                                    <thead>
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="5%">Tipe</th>
                                            <th width="30%">Judul</th>
                                            <th width="10%">Kategori</th>
                                            <th width="10%">Ukuran</th>
                                            <th width="15%">Uploader</th>
                                            <th width="10%">Tanggal</th>
                                            <th width="5%">Status</th>
                                            <th width="10%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($documents) > 0): ?>
                                            <?php foreach ($documents as $index => $document): ?>
                                                <tr>
                                                    <td><?php echo $index + 1; ?></td>
                                                    <td class="text-center">
                                                        <?php
                                                        $icon_class = 'fas fa-file-alt';
                                                        switch (strtolower($document['file_type'])) {
                                                            case 'pdf':
                                                                $icon_class = 'fas fa-file-pdf text-danger';
                                                                break;
                                                            case 'doc':
                                                            case 'docx':
                                                                $icon_class = 'fas fa-file-word text-primary';
                                                                break;
                                                            case 'xls':
                                                            case 'xlsx':
                                                                $icon_class = 'fas fa-file-excel text-success';
                                                                break;
                                                            case 'ppt':
                                                            case 'pptx':
                                                                $icon_class = 'fas fa-file-powerpoint text-warning';
                                                                break;
                                                            case 'zip':
                                                            case 'rar':
                                                                $icon_class = 'fas fa-file-archive text-secondary';
                                                                break;
                                                        }
                                                        ?>
                                                        <i class="<?php echo $icon_class; ?> fa-2x"></i>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($document['title']); ?></td>
                                                    <td><?php echo htmlspecialchars($document['category']); ?></td>
                                                    <td><?php echo formatFilesize($document['file_size']); ?></td>
                                                    <td><?php echo htmlspecialchars($document['uploader_name'] ?? 'N/A'); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($document['upload_date'])); ?></td>
                                                    <td class="text-center">
                                                        <?php if ($document['is_active']): ?>
                                                            <span class="badge bg-success">Aktif</span>
                                                        <?php else: ?>
                                                            <span class="badge bg-danger">Nonaktif</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="edit_document.php?id=<?php echo $document['id']; ?>"
                                                            class="btn btn-sm btn-info" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="<?php echo $site_config['base_url']; ?>/uploads/dokumen/<?php echo $document['filename']; ?>"
                                                            class="btn btn-sm btn-success" title="Download" target="_blank">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <a href="#" class="btn btn-sm btn-danger btn-delete"
                                                            data-id="<?php echo $document['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($document['title']); ?>"
                                                            data-token="<?php echo $csrf_token; ?>" title="Hapus">
                                                            <i class="fas fa-trash-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center">Tidak ada data dokumen</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus <span
                        id="delete-type"><?php echo ($tab == 'posts') ? 'berita/artikel' : 'dokumen'; ?></span> "<span
                        id="delete-name"></span>"?</p>
                <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <a href="#" id="delete-link" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
</div>

<!-- Custom Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Konfigurasi modal konfirmasi hapus
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        const deleteButtons = document.querySelectorAll('.btn-delete');

        deleteButtons.forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();

                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const token = this.getAttribute('data-token');
                const tab = '<?php echo $tab; ?>';

                document.getElementById('delete-name').textContent = name;
                document.getElementById('delete-type').textContent = (tab === 'posts') ? 'berita/artikel' : 'dokumen';
                document.getElementById('delete-link').href = `index.php?tab=${tab}&action=delete&id=${id}&token=${token}`;

                deleteModal.show();
            });
        });
    });
</script>

<?php
// Load footer
include_once ADMIN_PATH . '/includes/footer.php';
?>
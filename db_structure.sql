 -- Buat database
CREATE DATABASE IF NOT EXISTS cdk_bojonegoro;
USE cdk_bojonegoro;

-- Tabel untuk pengguna admin
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'editor') NOT NULL,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk layanan
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    icon VARCHAR(50) NOT NULL,
    description TEXT,
    content TEXT,
    order_number INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk program
CREATE TABLE programs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    icon VARCHAR(50) NOT NULL,
    description TEXT,
    content TEXT,
    order_number INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk statistik
CREATE TABLE statistics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    year INT NOT NULL,
    data_json JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk monitoring/capaian program
CREATE TABLE achievements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    icon VARCHAR(50) NOT NULL,
    percentage INT NOT NULL,
    year INT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk publikasi/berita
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    category VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    image VARCHAR(255),
    publish_date DATE NOT NULL,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    view_count INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel untuk dokumen
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    filename VARCHAR(255) NOT NULL,
    file_type VARCHAR(10) NOT NULL,
    file_size INT NOT NULL,
    category VARCHAR(50),
    description TEXT,
    upload_date DATE NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    download_count INT DEFAULT 0,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Tabel untuk galeri
CREATE TABLE gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,
    event_date DATE NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk pesan dari pengunjung
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    category VARCHAR(50) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel untuk wilayah kerja
CREATE TABLE work_areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk konfigurasi website
CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    setting_description VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert data pengguna admin default
INSERT INTO users (username, password, name, email, role) VALUES 
('admin', '$2y$10$qJfMJQdgEkE9y4OeUOAZ2.3jJTIuLJ0y0QnPL/CgWUQhJJ5j.n6c.', 'Administrator', 'admin@cdk-bojonegoro.jatimprov.go.id', 'admin');

-- Insert data layanan
INSERT INTO services (title, icon, description, content, order_number) VALUES 
('Perizinan & Sertifikasi', 'fas fa-file-signature', 'Pendampingan perizinan industri primer hasil hutan', '<ul class="service-list">
                  <li>
                    <i class="fas fa-check-circle"></i> Pendampingan perizinan
                    industri primer hasil hutan
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i> Sertifikasi hutan hak
                    dan industri primer kayu
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i> Verifikasi dokumen hasil
                    hutan
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i> Monitoring dan evaluasi
                    perizinan
                  </li>
                </ul>', 1),
('Rehabilitasi & Konservasi', 'fas fa-seedling', 'Rehabilitasi lahan kritis dan konservasi tanah dan air', '<ul class="service-list">
                  <li>
                    <i class="fas fa-check-circle"></i> Rehabilitasi lahan
                    kritis
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i> Konservasi tanah dan air
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i> Pengelolaan DAS terpadu
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i> Pembinaan kawasan
                    ekosistem esensial
                  </li>
                </ul>', 2),
('Pemberdayaan Masyarakat', 'fas fa-users', 'Program perhutanan sosial dan pendampingan kelompok tani hutan', '<ul class="service-list">
                  <li>
                    <i class="fas fa-check-circle"></i> Program perhutanan
                    sosial
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i> Pendampingan kelompok
                    tani hutan
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i> Penyuluhan kehutanan
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i> Pembinaan usaha
                    kehutanan masyarakat
                  </li>
                </ul>', 3),
('Pengawasan & Pengendalian', 'fas fa-shield-alt', 'Pengendalian pemanfaatan tumbuhan/satwa liar non-CITES', '<ul class="service-list">
                  <li>
                    <i class="fas fa-check-circle"></i> Pengendalian pemanfaatan
                    tumbuhan/satwa liar non-CITES
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i> Monitoring potensi hutan
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i> Evaluasi kinerja
                    industri kehutanan
                  </li>
                  <li>
                    <i class="fas fa-check-circle"></i> Perlindungan hutan
                  </li>
                </ul>', 4);

-- Insert data program
INSERT INTO programs (title, icon, description, content, order_number) VALUES 
('Perencanaan & Tata Hutan', 'ri-draft-line', 'Perencanaan, pengukuhan dan penatagunaan kawasan hutan', '<ul class="program-list">
                <li>
                  <i class="ri-check-line"></i>
                  <span>Inventarisasi dan pemetaan kawasan hutan</span>
                </li>
                <li>
                  <i class="ri-check-line"></i>
                  <span>Pengukuhan dan penatagunaan kawasan hutan</span>
                </li>
                <li>
                  <i class="ri-check-line"></i>
                  <span>Penyusunan rencana pengelolaan hutan</span>
                </li>
              </ul>', 1),
('Pemanfaatan Hutan', 'ri-plant-line', 'Pemanfaatan dan penggunaan kawasan hutan', '<ul class="program-list">
                <li>
                  <i class="ri-check-line"></i>
                  <span>Pemanfaatan kawasan, jasa lingkungan dan hasil hutan</span>
                </li>
                <li>
                  <i class="ri-check-line"></i>
                  <span>Penggunaan dan perubahan peruntukan kawasan hutan</span>
                </li>
                <li>
                  <i class="ri-check-line"></i>
                  <span>Pengelolaan iuran dan peredaran hasil hutan</span>
                </li>
              </ul>', 2),
('Rehabilitasi Hutan', 'ri-seedling-line', 'Rehabilitasi hutan dan lahan kritis', '<ul class="program-list">
                <li>
                  <i class="ri-check-line"></i>
                  <span>Rehabilitasi hutan dan lahan kritis</span>
                </li>
                <li>
                  <i class="ri-check-line"></i>
                  <span>Perbenihan tanaman hutan</span>
                </li>
                <li>
                  <i class="ri-check-line"></i>
                  <span>Pengelolaan DAS dan perhutanan sosial</span>
                </li>
              </ul>', 3),
('Perlindungan Hutan', 'ri-shield-check-line', 'Perlindungan dan pengamanan hutan', '<ul class="program-list">
                <li>
                  <i class="ri-check-line"></i>
                  <span>Pengamanan dan penegakan hukum kehutanan</span>
                </li>
                <li>
                  <i class="ri-check-line"></i>
                  <span>Pengendalian kebakaran hutan dan lahan</span>
                </li>
                <li>
                  <i class="ri-check-line"></i>
                  <span>Pengendalian kerusakan ekosistem hutan</span>
                </li>
              </ul>', 4);

-- Insert data statistik
INSERT INTO statistics (title, category, year, data_json) VALUES 
('Luas Kawasan Hutan', 'forest-area', 2024, '{"labels": ["Hutan Produksi", "Hutan Lindung", "Hutan Konservasi", "Hutan Rakyat", "APL"], "data": [45000, 8500, 2500, 15000, 20000]}'),
('Produksi Hasil Hutan', 'forest-production', 2024, '{"labels": ["Kayu Jati", "Kayu Rimba", "Getah Pinus", "Madu Hutan", "HHBK Lainnya"], "data": [1200, 850, 450, 300, 200]}');

-- Insert data monitoring/capaian program
INSERT INTO achievements (title, icon, percentage, year) VALUES 
('Rehabilitasi Lahan', 'fas fa-seedling', 75, 2024),
('Perhutanan Sosial', 'fas fa-hands-helping', 80, 2024),
('Pembibitan', 'fas fa-leaf', 90, 2024),
('Penyuluhan', 'fas fa-chalkboard-teacher', 85, 2024);

-- Insert data publikasi/berita
INSERT INTO posts (title, slug, category, content, image, publish_date, created_by, is_featured) VALUES 
('Penanaman 10.000 Bibit Pohon di Kawasan Hutan Lindung', 'penanaman-10000-bibit-pohon', 'Program', '<p>Program penanaman pohon sebagai upaya rehabilitasi hutan dan lahan kritis telah dilaksanakan dengan melibatkan masyarakat sekitar hutan dan stakeholder terkait.</p><p>Kegiatan ini bertujuan untuk meningkatkan tutupan lahan dan memperbaiki fungsi hutan sebagai penyangga sumber daya air. Sebanyak 10.000 bibit pohon jenis endemik dan bernilai ekonomi tinggi telah ditanam pada areal seluas 50 hektar.</p>', 'penanaman.jpg', '2025-02-12', 1, 1),
('Penguatan Kelembagaan Kelompok Tani Hutan', 'penguatan-kelembagaan-kelompok-tani', 'Pemberdayaan', '<p>Kegiatan penguatan kelembagaan kelompok tani hutan melalui pelatihan manajemen organisasi dan pengembangan usaha produktif berbasis hasil hutan.</p><p>Program ini melibatkan 50 kelompok tani dari 5 kabupaten/kota dengan total peserta 150 orang. Materi yang disampaikan meliputi pengembangan usaha, akses permodalan, dan pemasaran hasil hutan.</p>', 'perhutanan-sosial.jpg', '2025-02-08', 1, 1),
('Intensifikasi Patroli Pengamanan Hutan', 'intensifikasi-patroli-pengamanan-hutan', 'Perlindungan', '<p>Peningkatan kegiatan patroli pengamanan hutan bersama masyarakat untuk mencegah kegiatan illegal logging dan perambahan kawasan hutan.</p><p>Patroli gabungan dilakukan secara rutin 2 kali seminggu dengan melibatkan petugas kehutanan, polisi hutan, dan masyarakat pengawas hutan (MPA).</p>', 'patroli.jpg', '2025-02-05', 1, 0),
('Pengembangan Bibit Unggul Tanaman Hutan', 'pengembangan-bibit-unggul', 'Rehabilitasi', '<p>Inovasi pengembangan bibit unggul melalui teknik pembibitan modern untuk mendukung program rehabilitasi hutan dan lahan.</p><p>Program ini menghasilkan bibit unggul dengan tingkat pertumbuhan 30% lebih cepat dan daya tahan terhadap hama penyakit yang lebih baik.</p>', 'pembibitan.jpg', '2025-02-01', 1, 0);

-- Insert data dokumen
INSERT INTO documents (title, filename, file_type, file_size, category, description, upload_date, created_by) VALUES 
('Laporan Kinerja Tahun 2024', 'laporan-kinerja-2024.pdf', 'PDF', 2500, 'Laporan', 'Laporan kinerja tahunan CDK Wilayah Bojonegoro tahun 2024', '2025-01-15', 1),
('Pedoman Teknis Rehabilitasi Hutan', 'pedoman-rehabilitasi-hutan.doc', 'DOC', 1800, 'Pedoman', 'Pedoman teknis pelaksanaan kegiatan rehabilitasi hutan dan lahan', '2025-01-10', 1),
('Data Statistik Kehutanan 2024', 'statistik-kehutanan-2024.xls', 'XLS', 1200, 'Statistik', 'Data statistik kehutanan wilayah Bojonegoro tahun 2024', '2025-01-05', 1),
('Rencana Kerja Tahunan 2025', 'rkt-2025.pdf', 'PDF', 3100, 'Rencana', 'Rencana kerja tahunan CDK Wilayah Bojonegoro tahun 2025', '2025-01-01', 1);

-- Insert data galeri
INSERT INTO gallery (title, description, image, category, event_date) VALUES 
('Program Penanaman Pohon', 'Penanaman 10.000 bibit pohon bersama masyarakat', 'penanaman.jpg', 'penanaman', '2024-01-15'),
('Penyuluhan Kehutanan', 'Sosialisasi program perhutanan sosial', 'penyuluhan.jpeg', 'penyuluhan', '2024-01-20'),
('Pembibitan Tanaman', 'Pengembangan bibit unggul di persemaian', 'pembibitan.jpeg', 'pembibitan', '2024-01-25');

-- Insert data wilayah kerja
INSERT INTO work_areas (name) VALUES 
('Kabupaten Bojonegoro'),
('Kabupaten Tuban'),
('Kabupaten Lamongan'),
('Kabupaten Gresik'),
('Kabupaten Sidoarjo'),
('Kota Surabaya');

-- Insert data konfigurasi website
INSERT INTO settings (setting_key, setting_value, setting_description) VALUES 
('site_title', 'CDK Wilayah Bojonegoro - Dinas Kehutanan Provinsi Jawa Timur', 'Judul website'),
('site_description', 'Unit Pelaksana Teknis Dinas Kehutanan Provinsi Jawa Timur', 'Deskripsi website'),
('site_address', 'Jl. Hayam Wuruk No. 9, Bojonegoro, Jawa Timur', 'Alamat kantor'),
('site_phone', '(0353) 123456', 'Nomor telepon'),
('site_email', 'info@cdk-bojonegoro.jatimprov.go.id', 'Email kontak'),
('office_hours', 'Senin - Jumat: 08:00 - 16:00 WIB', 'Jam operasional'),
('social_facebook', 'https://facebook.com/cdkbojonegoro', 'Link Facebook'),
('social_twitter', 'https://twitter.com/cdkbojonegoro', 'Link Twitter'),
('social_instagram', 'https://instagram.com/cdkbojonegoro', 'Link Instagram'),
('social_youtube', 'https://youtube.com/cdkbojonegoro', 'Link YouTube');

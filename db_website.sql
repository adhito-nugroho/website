/*
 Navicat Premium Data Transfer

 Source Server         : koneksi
 Source Server Type    : MySQL
 Source Server Version : 80030
 Source Host           : localhost:3306
 Source Schema         : cdk_bojonegoro

 Target Server Type    : MySQL
 Target Server Version : 80030
 File Encoding         : 65001

 Date: 19/03/2025 13:51:38
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for achievements
-- ----------------------------
DROP TABLE IF EXISTS `achievements`;
CREATE TABLE `achievements`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `percentage` int(0) NOT NULL,
  `year` int(0) NOT NULL,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of achievements
-- ----------------------------
INSERT INTO `achievements` VALUES (1, 'Rehabilitasi Lahan', 'fas fa-seedling', 75, 2024, 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `achievements` VALUES (2, 'Perhutanan Sosial', 'fas fa-hands-helping', 80, 2024, 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `achievements` VALUES (3, 'Pembibitan', 'fas fa-leaf', 90, 2024, 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `achievements` VALUES (4, 'Penyuluhan', 'fas fa-chalkboard-teacher', 85, 2024, 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');

-- ----------------------------
-- Table structure for activity_logs
-- ----------------------------
DROP TABLE IF EXISTS `activity_logs`;
CREATE TABLE `activity_logs`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `user_id` int(0) NULL DEFAULT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `module` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `created_at` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `user_id`(`user_id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for admin_logs
-- ----------------------------
DROP TABLE IF EXISTS `admin_logs`;
CREATE TABLE `admin_logs`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `admin_id` int(0) NOT NULL,
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `module` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `item_id` int(0) NULL DEFAULT NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `admin_id`(`admin_id`) USING BTREE,
  CONSTRAINT `admin_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin_logs
-- ----------------------------
INSERT INTO `admin_logs` VALUES (1, 2, 'logout', 'auth', NULL, 'Admin logout', '::1', '2025-03-16 05:31:19');
INSERT INTO `admin_logs` VALUES (2, 2, 'update', 'profile', 2, 'Updated profile information', '::1', '2025-03-17 20:30:52');
INSERT INTO `admin_logs` VALUES (3, 2, 'update', 'settings', NULL, 'Updated website settings', '::1', '2025-03-17 20:34:34');
INSERT INTO `admin_logs` VALUES (4, 2, 'update', 'settings', NULL, 'Updated website settings', '::1', '2025-03-17 20:35:25');

-- ----------------------------
-- Table structure for documents
-- ----------------------------
DROP TABLE IF EXISTS `documents`;
CREATE TABLE `documents`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `file_type` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `file_size` int(0) NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `upload_date` date NOT NULL,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `download_count` int(0) NULL DEFAULT 0,
  `created_by` int(0) NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `created_by`(`created_by`) USING BTREE,
  CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of documents
-- ----------------------------
INSERT INTO `documents` VALUES (2, 'Pedoman Teknis Rehabilitasi Hutan', 'pedoman-rehabilitasi-hutan.doc', 'DOC', 1800, 'Pedoman', 'Pedoman teknis pelaksanaan kegiatan rehabilitasi hutan dan lahan', '2025-01-10', 1, 0, NULL, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `documents` VALUES (3, 'Data Statistik Kehutanan 2024', 'statistik-kehutanan-2024.xls', 'XLS', 1200, 'Statistik', 'Data statistik kehutanan wilayah Bojonegoro tahun 2024', '2025-01-05', 1, 0, NULL, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `documents` VALUES (4, 'Rencana Kerja Tahunan 2025', 'rkt-2025.pdf', 'PDF', 3100, 'Rencana', 'Rencana kerja tahunan CDK Wilayah Bojonegoro tahun 2025', '2025-01-01', 1, 0, NULL, '2025-03-15 21:31:58', '2025-03-15 21:31:58');

-- ----------------------------
-- Table structure for gallery
-- ----------------------------
DROP TABLE IF EXISTS `gallery`;
CREATE TABLE `gallery`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `event_date` date NOT NULL,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of gallery
-- ----------------------------
INSERT INTO `gallery` VALUES (1, 'Program Penanaman Pohon', 'Penanaman 10.000 bibit pohon bersama masyarakat', '1742161819_67d7479b42bb9.jpg', 'penanaman', '2024-01-15', 1, '2025-03-15 21:31:58', '2025-03-17 04:50:19');
INSERT INTO `gallery` VALUES (2, 'Penyuluhan Kehutanan', 'Sosialisasi program perhutanan sosial', '1742161811_67d747936cf68.jpeg', 'penyuluhan', '2024-01-20', 1, '2025-03-15 21:31:58', '2025-03-17 04:50:11');
INSERT INTO `gallery` VALUES (3, 'Pembibitan Tanaman', 'Pengembangan bibit unggul di persemaian', '1742161799_67d74787ed0a4.jpeg', 'pembibitan', '2024-01-25', 1, '2025-03-15 21:31:58', '2025-03-17 04:49:59');

-- ----------------------------
-- Table structure for messages
-- ----------------------------
DROP TABLE IF EXISTS `messages`;
CREATE TABLE `messages`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status` enum('unread','read','replied') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT 'unread',
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of messages
-- ----------------------------
INSERT INTO `messages` VALUES (1, 'Test User', 'test@example.com', '123456789', 'Informasi', 'Ini adalah pesan test untuk memastikan tabel messages berfungsi dengan baik.', 'read', '::1', '2025-03-17 09:31:51');
INSERT INTO `messages` VALUES (3, 'Test User 2025-03-17 09:42:12', 'test@example.com', '123456789', 'Test', 'Ini adalah pesan test insert langsung pada 2025-03-17 09:42:12', 'unread', '::1', '2025-03-17 09:42:12');
INSERT INTO `messages` VALUES (4, 'Manual Test User', 'manual@test.com', '987654321', 'Test', 'Pesan test manual', 'unread', '::1', '2025-03-17 09:42:12');
INSERT INTO `messages` VALUES (5, 'adhito nugroho', 'dtfans2@gmail.com', '1234565', 'Pengaduan', '12345654654654654654654654', 'unread', '::1', '2025-03-17 10:04:46');

-- ----------------------------
-- Table structure for monitoring
-- ----------------------------
DROP TABLE IF EXISTS `monitoring`;
CREATE TABLE `monitoring`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `activity` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `status` enum('pending','in_progress','completed','cancelled') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'pending',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `result` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `created_by` int(0) NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `created_by`(`created_by`) USING BTREE,
  CONSTRAINT `monitoring_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of monitoring
-- ----------------------------
INSERT INTO `monitoring` VALUES (1, '2025-03-15', 'Hutan Lindung Bojonegoro', 'Patroli Pengamanan Hutan', 'completed', 'Patroli rutin untuk mengawasi kegiatan di kawasan hutan lindung', 'Tidak ditemukan aktivitas ilegal', 'Perlu meningkatkan frekuensi patroli pada malam hari', 2, '2025-03-18 10:00:00', '2025-03-18 10:00:00');
INSERT INTO `monitoring` VALUES (2, '2025-03-16', 'Kawasan Rehabilitasi Tuban', 'Monitoring Penanaman', 'in_progress', 'Pemantauan perkembangan bibit yang ditanam pada program rehabilitasi', 'Tingkat pertumbuhan bibit mencapai 85%', 'Beberapa area memerlukan penyiraman tambahan', 2, '2025-03-18 10:05:00', '2025-03-18 10:05:00');
INSERT INTO `monitoring` VALUES (3, '2025-03-17', 'Persemaian Permanen Lamongan', 'Evaluasi Pembibitan', 'completed', 'Evaluasi kualitas bibit di persemaian permanen', 'pada saat kunjungan ulang', 'Jadwalkan kunjungan ulang minggu depan', 2, '2025-03-18 10:10:00', '2025-03-18 09:10:18');

-- ----------------------------
-- Table structure for pengaturan
-- ----------------------------
DROP TABLE IF EXISTS `pengaturan`;
CREATE TABLE `pengaturan`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `created_at` timestamp(0) NOT NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `key`(`key`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pengaturan
-- ----------------------------
INSERT INTO `pengaturan` VALUES (1, 'nama_instansi', 'Cabang Dinas Kehutanan Wilayah Bojonegoro', '2025-03-18 04:20:36', NULL);
INSERT INTO `pengaturan` VALUES (2, 'alamat', 'Jl. Hayam Wuruk No. 9, Bojonegoro, Jawa Timur', '2025-03-18 04:20:36', NULL);
INSERT INTO `pengaturan` VALUES (3, 'telepon', '(0353) 123456', '2025-03-18 04:20:36', NULL);
INSERT INTO `pengaturan` VALUES (4, 'email', 'info@cdk-bojonegoro.jatimprov.go.id', '2025-03-18 04:20:36', NULL);
INSERT INTO `pengaturan` VALUES (5, 'jam_layanan', 'Senin - Jumat: 08:00 - 16:00 WIB', '2025-03-18 04:20:36', NULL);
INSERT INTO `pengaturan` VALUES (6, 'logo', 'logo.png', '2025-03-18 04:20:36', NULL);

-- ----------------------------
-- Table structure for posts
-- ----------------------------
DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `publish_date` date NOT NULL,
  `is_featured` tinyint(1) NULL DEFAULT 0,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `view_count` int(0) NULL DEFAULT 0,
  `created_by` int(0) NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `slug`(`slug`) USING BTREE,
  INDEX `created_by`(`created_by`) USING BTREE,
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE RESTRICT
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of posts
-- ----------------------------
INSERT INTO `posts` VALUES (1, 'Penanaman 10.000 Bibit Pohon di Kawasan Hutan Lindung', 'penanaman-10000-bibit-pohon', 'Pemberdayaan', '<p>Program penanaman pohon sebagai upaya rehabilitasi hutan dan lahan kritis telah dilaksanakan dengan melibatkan masyarakat sekitar hutan dan stakeholder terkait.</p><p>Kegiatan ini bertujuan untuk meningkatkan tutupan lahan dan memperbaiki fungsi hutan sebagai penyangga sumber daya air. Sebanyak 10.000 bibit pohon jenis endemik dan bernilai ekonomi tinggi telah ditanam pada areal seluas 50 hektar.</p>', '1742161244_67d7455ce6d29.jpeg', '2025-02-12', 1, 1, 0, 2, '2025-03-15 21:31:58', '2025-03-18 14:34:58');
INSERT INTO `posts` VALUES (2, 'Penguatan Kelembagaan Kelompok Tani Hutan', 'penguatan-kelembagaan-kelompok-tani', 'Pemberdayaan', '<p>Kegiatan penguatan kelembagaan kelompok tani hutan melalui pelatihan manajemen organisasi dan pengembangan usaha produktif berbasis hasil hutan.</p><p>Program ini melibatkan 50 kelompok tani dari 5 kabupaten/kota dengan total peserta 150 orang. Materi yang disampaikan meliputi pengembangan usaha, akses permodalan, dan pemasaran hasil hutan.</p>', '1742161261_67d7456d92665.jpg', '2025-02-08', 1, 1, 0, 3, '2025-03-15 21:31:58', '2025-03-18 14:35:04');
INSERT INTO `posts` VALUES (3, 'Intensifikasi Patroli Pengamanan Hutan', 'intensifikasi-patroli-pengamanan-hutan', 'Perlindungan', '<p>Peningkatan kegiatan patroli pengamanan hutan bersama masyarakat untuk mencegah kegiatan illegal logging dan perambahan kawasan hutan.</p><p>Patroli gabungan dilakukan secara rutin 2 kali seminggu dengan melibatkan petugas kehutanan, polisi hutan, dan masyarakat pengawas hutan (MPA).</p>', 'patroli.jpg', '2025-02-05', 1, 1, 0, 2, '2025-03-15 21:31:58', '2025-03-18 14:35:47');
INSERT INTO `posts` VALUES (4, 'Pengembangan Bibit Unggul Tanaman Hutan', 'pengembangan-bibit-unggul', 'Rehabilitasi', '<p>Inovasi pengembangan bibit unggul melalui teknik pembibitan modern untuk mendukung program rehabilitasi hutan dan lahan.</p><p>Program ini menghasilkan bibit unggul dengan tingkat pertumbuhan 30% lebih cepat dan daya tahan terhadap hama penyakit yang lebih baik.</p>', '1742161275_67d7457b73ab9.jpg', '2025-02-01', 0, 1, 0, 3, '2025-03-15 21:31:58', '2025-03-18 14:35:14');

-- ----------------------------
-- Table structure for programs
-- ----------------------------
DROP TABLE IF EXISTS `programs`;
CREATE TABLE `programs`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `order_number` int(0) NULL DEFAULT 0,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of programs
-- ----------------------------
INSERT INTO `programs` VALUES (1, 'Perencanaan dan Tata Hutan', 'fas fa-water', 'Perencanaan, pengukuhan dan penatagunaan kawasan hutan', '<ul class=\"program-list\">\r\n                <li>\r\n                  <i class=\"ri-check-line\"></i>\r\n                  <span>Inventarisasi dan pemetaan kawasan hutan</span>\r\n                </li>\r\n                <li>\r\n                  <i class=\"ri-check-line\"></i>\r\n                  <span>Pengukuhan dan penatagunaan kawasan hutan</span>\r\n                </li>\r\n                <li>\r\n                  <i class=\"ri-check-line\"></i>\r\n                  <span>Penyusunan rencana pengelolaan hutan</span>\r\n                </li>\r\n              </ul>', 1, 1, '2025-03-15 21:31:58', '2025-03-18 05:06:52');
INSERT INTO `programs` VALUES (2, 'Pemanfaatan Hutan', 'ri-plant-line', 'Pemanfaatan dan penggunaan kawasan hutan', '<ul class=\"program-list\">\r\n                <li>\r\n                  <i class=\"ri-check-line\"></i>\r\n                  <span>Pemanfaatan kawasan, jasa lingkungan dan hasil hutan</span>\r\n                </li>\r\n                <li>\r\n                  <i class=\"ri-check-line\"></i>\r\n                  <span>Penggunaan dan perubahan peruntukan kawasan hutan</span>\r\n                </li>\r\n                <li>\r\n                  <i class=\"ri-check-line\"></i>\r\n                  <span>Pengelolaan iuran dan peredaran hasil hutan</span>\r\n                </li>\r\n              </ul>', 2, 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `programs` VALUES (3, 'Rehabilitasi Hutan', 'ri-seedling-line', 'Rehabilitasi hutan dan lahan kritis', '<ul class=\"program-list\">\r\n                <li>\r\n                  <i class=\"ri-check-line\"></i>\r\n                  <span>Rehabilitasi hutan dan lahan kritis</span>\r\n                </li>\r\n                <li>\r\n                  <i class=\"ri-check-line\"></i>\r\n                  <span>Perbenihan tanaman hutan</span>\r\n                </li>\r\n                <li>\r\n                  <i class=\"ri-check-line\"></i>\r\n                  <span>Pengelolaan DAS dan perhutanan sosial</span>\r\n                </li>\r\n              </ul>', 3, 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `programs` VALUES (4, 'Perlindungan Hutan', 'ri-shield-check-line', 'Perlindungan dan pengamanan hutan', '<ul class=\"program-list\">\r\n                <li>\r\n                  <i class=\"ri-check-line\"></i>\r\n                  <span>Pengamanan dan penegakan hukum kehutanan</span>\r\n                </li>\r\n                <li>\r\n                  <i class=\"ri-check-line\"></i>\r\n                  <span>Pengendalian kebakaran hutan dan lahan</span>\r\n                </li>\r\n                <li>\r\n                  <i class=\"ri-check-line\"></i>\r\n                  <span>Pengendalian kerusakan ekosistem hutan</span>\r\n                </li>\r\n              </ul>', 4, 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');

-- ----------------------------
-- Table structure for services
-- ----------------------------
DROP TABLE IF EXISTS `services`;
CREATE TABLE `services`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL,
  `order_number` int(0) NULL DEFAULT 0,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of services
-- ----------------------------
INSERT INTO `services` VALUES (1, 'Perizinan dan Sertifikasi', 'fas fa-file-signature', 'Pendampingan perizinan industri primer hasil hutan', '<ul class=\"service-list\">\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Pendampingan perizinan\r\n                    industri primer hasil hutan\r\n                  </li>\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Sertifikasi hutan hak\r\n                    dan industri primer kayu\r\n                  </li>\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Verifikasi dokumen hasil\r\n                    hutan\r\n                  </li>\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Monitoring dan evaluasi\r\n                    perizinan\r\n                  </li>\r\n                </ul>', 1, 1, '2025-03-15 21:31:58', '2025-03-18 09:03:58');
INSERT INTO `services` VALUES (2, 'Rehabilitasi & Konservasi', 'fas fa-seedling', 'Rehabilitasi lahan kritis dan konservasi tanah dan air', '<ul class=\"service-list\">\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Rehabilitasi lahan\r\n                    kritis\r\n                  </li>\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Konservasi tanah dan air\r\n                  </li>\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Pengelolaan DAS terpadu\r\n                  </li>\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Pembinaan kawasan\r\n                    ekosistem esensial\r\n                  </li>\r\n                </ul>', 2, 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `services` VALUES (3, 'Pemberdayaan Masyarakat', 'fas fa-users', 'Program perhutanan sosial dan pendampingan kelompok tani hutan', '<ul class=\"service-list\">\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Program perhutanan\r\n                    sosial\r\n                  </li>\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Pendampingan kelompok\r\n                    tani hutan\r\n                  </li>\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Penyuluhan kehutanan\r\n                  </li>\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Pembinaan usaha\r\n                    kehutanan masyarakat\r\n                  </li>\r\n                </ul>', 3, 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `services` VALUES (4, 'Pengawasan & Pengendalian', 'fas fa-shield-alt', 'Pengendalian pemanfaatan tumbuhan/satwa liar non-CITES', '<ul class=\"service-list\">\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Pengendalian pemanfaatan\r\n                    tumbuhan/satwa liar non-CITES\r\n                  </li>\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Monitoring potensi hutan\r\n                  </li>\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Evaluasi kinerja\r\n                    industri kehutanan\r\n                  </li>\r\n                  <li>\r\n                    <i class=\"fas fa-check-circle\"></i> Perlindungan hutan\r\n                  </li>\r\n                </ul>', 4, 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `services` VALUES (6, 'Pendaftaran PBPHH', 'fas fa-university', 'pendaftaran melalu jatim bejo', 'pendaftaran oss', 5, 1, '2025-03-18 09:04:48', '2025-03-18 09:04:48');

-- ----------------------------
-- Table structure for settings
-- ----------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `setting_description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `setting_key`(`setting_key`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of settings
-- ----------------------------
INSERT INTO `settings` VALUES (1, 'site_title', 'CDK Wilayah Bojonegoro - Dinas Kehutanan Provinsi Jawa Timur', 'Judul website', '2025-03-15 21:31:58', '2025-03-17 20:35:25');
INSERT INTO `settings` VALUES (2, 'site_description', 'Unit Pelaksana Teknis Dinas Kehutanan Provinsi Jawa Timur', 'Deskripsi website', '2025-03-15 21:31:58', '2025-03-17 20:35:25');
INSERT INTO `settings` VALUES (3, 'site_address', 'Jl. Hayam Wuruk No. 9, Bojonegoro, Jawa Timur', 'Alamat kantor', '2025-03-15 21:31:58', '2025-03-17 20:35:25');
INSERT INTO `settings` VALUES (4, 'site_phone', '(0353) 123456', 'Nomor telepon', '2025-03-15 21:31:58', '2025-03-17 20:35:25');
INSERT INTO `settings` VALUES (5, 'site_email', 'info@cdk-bojonegoro.jatimprov.go.id', 'Email kontak', '2025-03-15 21:31:58', '2025-03-17 20:35:25');
INSERT INTO `settings` VALUES (6, 'office_hours', 'Senin - Jumat: 08:00 - 16:00 WIB', 'Jam operasional', '2025-03-15 21:31:58', '2025-03-17 20:35:25');
INSERT INTO `settings` VALUES (7, 'social_facebook', 'https://facebook.com/cdkbojonegoro', 'Link Facebook', '2025-03-15 21:31:58', '2025-03-17 20:35:25');
INSERT INTO `settings` VALUES (8, 'social_twitter', 'https://twitter.com/cdkbojonegoro', 'Link Twitter', '2025-03-15 21:31:58', '2025-03-17 20:35:25');
INSERT INTO `settings` VALUES (9, 'social_instagram', 'https://www.instagram.com/cabdinkehutananbojonegoro/', 'Link Instagram', '2025-03-15 21:31:58', '2025-03-17 20:35:25');
INSERT INTO `settings` VALUES (10, 'social_youtube', 'https://www.youtube.com/@officialcdkbojonegoro', 'Link YouTube', '2025-03-15 21:31:58', '2025-03-17 20:35:25');

-- ----------------------------
-- Table structure for site_settings
-- ----------------------------
DROP TABLE IF EXISTS `site_settings`;
CREATE TABLE `site_settings`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL COMMENT 'Kunci pengaturan',
  `setting_value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL COMMENT 'Nilai pengaturan (JSON)',
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `setting_key`(`setting_key`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of site_settings
-- ----------------------------
INSERT INTO `site_settings` VALUES (1, 'hero_content', '{\"title\":\"Cabang Dinas Kehutanan Wilayah Bojonegoro\",\"subtitle\":\"Unit Pelaksana Teknis Dinas Kehutanan Provinsi Jawa Timur yang melaksanakan kebijakan teknis operasional di bidang kehutanan\",\"button1_text\":\"Layanan Kami\",\"button1_link\":\"#layanan\",\"button2_text\":\"Hubungi Kami\",\"button2_link\":\"#kontak\",\"background_video\":\"forest-bg.mp4\"}', '2025-03-18 09:33:34', '2025-03-18 09:33:34');
INSERT INTO `site_settings` VALUES (2, 'work_areas', '[{\"name\":\"Kabupaten Bojonegoro\",\"description\":\"Wilayah kerja meliputi kawasan hutan di Kabupaten Bojonegoro\"},{\"name\":\"Kabupaten Tuban\",\"description\":\"Wilayah kerja meliputi kawasan hutan di Kabupaten Tuban\"},{\"name\":\"Kabupaten Lamongan\",\"description\":\"Wilayah kerja meliputi kawasan hutan di Kabupaten Lamongan\"},{\"name\":\"Kabupaten Ngawi\",\"description\":\"Wilayah kerja meliputi kawasan hutan di Kabupaten Ngawi\"},{\"name\":\"Kabupaten Blora\",\"description\":\"Wilayah kerja meliputi kawasan hutan di Kabupaten Blora\"},{\"name\":\"Kabupaten Madiun\",\"description\":\"Wilayah kerja meliputi kawasan hutan di Kabupaten Madiun\"}]', '2025-03-18 09:33:34', '2025-03-18 09:33:34');

-- ----------------------------
-- Table structure for statistics
-- ----------------------------
DROP TABLE IF EXISTS `statistics`;
CREATE TABLE `statistics`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `category` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `year` int(0) NOT NULL,
  `unit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NULL DEFAULT NULL,
  `data_json` json NOT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of statistics
-- ----------------------------
INSERT INTO `statistics` VALUES (3, 'Hasil Hutan Bukan Kayu', 'forest-production', 2025, 'ton', '{\"data\": [1000, 500, 6050], \"labels\": [\"Madu\", \"Getah Pinus\", \"Minyak Cengkeh\"]}', '2025-03-18 04:39:35', '2025-03-18 09:07:43');
INSERT INTO `statistics` VALUES (4, 'Hasil Hutan Kayu', 'forest-production', 2025, 'm3', '{\"data\": [1000, 1500, 2000], \"labels\": [\"Jati\", \"Sengon\", \"Pinus\"]}', '2025-03-18 04:42:33', '2025-03-18 05:22:43');
INSERT INTO `statistics` VALUES (5, 'Luas Kawasan Hutan Negara', 'forest-area', 2025, 'hektar', '{\"data\": [75000, 45000, 30000, 25000], \"labels\": [\"Hutan Produksi\", \"Hutan Lindung\", \"Hutan Konservasi\", \"Hutan Rakyat\"]}', '2025-03-18 10:13:30', '2025-03-18 14:31:38');
INSERT INTO `statistics` VALUES (6, 'Luas Kawasan Hutan Rakyat', 'forest-area', 2025, 'hektar', '{\"data\": [75000, 45000, 30000, 25000], \"labels\": [\"Hutan Produksi\", \"Hutan Lindung\", \"Hutan Konservasi\", \"Hutan Rakyat\"]}', '2025-03-18 10:16:20', '2025-03-18 14:31:53');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `role` enum('admin','editor') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `last_login` datetime(0) NULL DEFAULT NULL,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (2, 'admin', '$2y$10$jVfT4QKO/WSCLDgSbSYCYu8mSyS.foFpYBFyHclmXJ4ewIEz6tEYS', 'Administrator utama', 'admin@cdk-bojonegoro.jatimprov.go.id', 'admin', '2025-03-18 14:30:59', '2025-03-16 05:02:58', '2025-03-18 14:30:59');
INSERT INTO `users` VALUES (3, 'joni', '$2y$10$fmcDUtrbQ7rs/34Iha9J6OZ2WuOgkpHs3UkyHecP4tR1PzZiruDnS', 'joni', 'joni@gmail.com', 'editor', NULL, '2025-03-17 19:51:25', '2025-03-17 19:51:25');

-- ----------------------------
-- Table structure for work_areas
-- ----------------------------
DROP TABLE IF EXISTS `work_areas`;
CREATE TABLE `work_areas`  (
  `id` int(0) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `is_active` tinyint(1) NULL DEFAULT 1,
  `created_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0),
  `updated_at` timestamp(0) NULL DEFAULT CURRENT_TIMESTAMP(0) ON UPDATE CURRENT_TIMESTAMP(0),
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of work_areas
-- ----------------------------
INSERT INTO `work_areas` VALUES (1, 'Kabupaten Bojonegoro', 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `work_areas` VALUES (2, 'Kabupaten Tuban', 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `work_areas` VALUES (3, 'Kabupaten Lamongan', 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `work_areas` VALUES (4, 'Kabupaten Gresik', 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `work_areas` VALUES (5, 'Kabupaten Sidoarjo', 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');
INSERT INTO `work_areas` VALUES (6, 'Kota Surabaya', 1, '2025-03-15 21:31:58', '2025-03-15 21:31:58');

SET FOREIGN_KEY_CHECKS = 1;

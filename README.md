# Website CDK Bojonegoro

Sistem website resmi untuk Cabang Dinas Kehutanan (CDK) Bojonegoro dengan fitur publikasi, galeri, statistik, monitoring, dan manajemen konten.

## Daftar Isi
- [Gambaran Umum](#gambaran-umum)
- [Teknologi](#teknologi)
- [Struktur Proyek](#struktur-proyek)
- [Fitur Utama](#fitur-utama)
- [Instalasi](#instalasi)
- [Penggunaan](#penggunaan)
- [Manajemen Admin](#manajemen-admin)
- [Troubleshooting](#troubleshooting)

## Gambaran Umum

Website CDK Bojonegoro adalah platform resmi yang menyediakan informasi komprehensif tentang aktivitas, program, dan layanan dari Cabang Dinas Kehutanan Bojonegoro. Website ini memungkinkan masyarakat untuk mengakses berita terbaru, dokumen publik, statistik kehutanan, dan data monitoring kehutanan di wilayah Bojonegoro.

## Teknologi

- **Front-end**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Back-end**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Framework CSS**: Bootstrap 5, AOS (Animate On Scroll)
- **Library JavaScript**: Chart.js, Remixicon, Swiper
- **Server**: Apache/Nginx

## Struktur Proyek

```
website-cdk/
├── admin/                  # Panel Admin
│   ├── assets/             # File statis admin
│   ├── includes/           # File include admin
│   └── modules/            # Modul admin
├── assets/                 # File statis website utama
│   ├── css/                # File CSS
│   │   └── modules/        # CSS khusus per modul
│   ├── img/                # Gambar dan resource
│   ├── js/                 # File JavaScript
│   └── vendor/             # Library pihak ketiga
├── includes/               # File include utama
├── modules/                # Modul halaman
├── templates/              # Template halaman
├── uploads/                # File upload (publikasi, galeri, dll.)
├── index.php               # Halaman utama
├── .htaccess               # Konfigurasi Apache
└── README.md               # Dokumentasi (file ini)
```

## Fitur Utama

1. **Halaman Beranda**
   - Hero banner dengan statistik
   - Informasi profil singkat
   - Daftar layanan dan program
   - Statistik dan monitoring
   - Publikasi terbaru
   - Galeri foto kegiatan
   - Formulir kontak

2. **Publikasi**
   - Daftar berita dan artikel
   - Filter berdasarkan kategori
   - Pencarian berdasarkan kata kunci
   - Tampilan detail artikel
   - Dokumen publik yang dapat diunduh

3. **Galeri**
   - Galeri foto kegiatan
   - Filter berdasarkan kategori
   - Tampilan lightbox untuk melihat detail

4. **Statistik dan Monitoring**
   - Data statistik kehutanan
   - Visualisasi grafik dan chart
   - Data monitoring program kehutanan

5. **Panel Admin**
   - Manajemen pengguna
   - Manajemen konten (publikasi, galeri, dll.)
   - Upload dan manajemen dokumen
   - Pengelolaan statistik dan monitoring
   - Manajemen pesan dari pengunjung

## Instalasi

### Persyaratan Sistem
- PHP 7.4 atau lebih tinggi
- MySQL 5.7 atau MariaDB 10.3+
- Apache/Nginx dengan mod_rewrite
- Ekstensi PHP: PDO, GD, JSON, mbstring, zip

### Langkah Instalasi

1. **Clone Repository**
   ```bash
   git clone https://github.com/adhito-nugroho/website-cdk.git
   cd website-cdk
   ```

2. **Setup Database**
   - Buat database MySQL baru
   - Import struktur database dari `database/cdk_structure.sql`
   - (Opsional) Import data sampel dari `database/cdk_sample_data.sql`

3. **Konfigurasi**
   - Salin `includes/config-sample.php` ke `includes/config.php`
   - Edit `config.php` dan sesuaikan dengan pengaturan database Anda

4. **Pengaturan Server**
   - Pastikan direktori `uploads/` dapat ditulis oleh server web
   - Konfigurasikan server web untuk arahkan semua request ke `index.php`

5. **Akses Admin**
   - Buka `http://your-domain.com/admin/`
   - Login dengan kredensial default (admin/admin123)
   - **SEGERA UBAH PASSWORD DEFAULT!**

## Penggunaan

### Halaman Frontend

Setelah instalasi, pengunjung dapat mengakses berbagai halaman:

- **Beranda**: `index.php` atau `index.php?page=beranda`
- **Profil**: `index.php?page=profil`
- **Layanan**: `index.php?page=layanan`
- **Program**: `index.php?page=program`
- **Statistik**: `index.php?page=statistik`
- **Monitoring**: `index.php?page=monitoring`
- **Publikasi**: `index.php?page=publikasi`
- **Galeri**: `index.php?page=galeri`
- **Kontak**: `index.php?page=kontak`

### Halaman Detail

- **Detail Publikasi**: `index.php?page=publikasi&id={id_publikasi}`
- **Daftar Semua Publikasi**: `index.php?page=publikasi&view=all`
- **Daftar Dokumen**: `index.php?page=publikasi&view=documents`

## Manajemen Admin

Panel admin (`/admin/`) memberikan kontrol penuh atas konten website:

1. **Dashboard**
   - Ringkasan aktivitas
   - Statistik pengunjung
   - Notifikasi pesan baru

2. **Manajemen Publikasi**
   - Tambah/edit/hapus berita dan artikel
   - Atur kategori dan tag
   - Upload gambar untuk artikel
   - Kelola dokumen publik

3. **Manajemen Galeri**
   - Upload foto kegiatan
   - Kelola kategori galeri
   - Atur tampilan galeri

4. **Manajemen Statistik & Monitoring**
   - Input data statistik
   - Buat dan edit grafik
   - Update data monitoring

5. **Pengaturan Website**
   - Info dasar website
   - Kontak dan media sosial
   - SEO dan metadata

## Troubleshooting

### Masalah Umum

1. **Halaman Error 500**
   - Periksa file log PHP dan server
   - Pastikan file `config.php` sudah diatur dengan benar
   - Periksa izin file dan direktori

2. **Gambar Tidak Tampil**
   - Pastikan direktori `uploads/` memiliki izin tulis
   - Periksa apakah path gambar sudah benar

3. **Tidak Bisa Login Admin**
   - Pastikan cookie browser diaktifkan
   - Reset password melalui database jika perlu

### Dukungan

Jika Anda mengalami masalah dalam instalasi atau penggunaan, hubungi:
- Email: support@example.com
- Telepon: 021-12345678

---

&copy; 2023 CDK Bojonegoro. Semua hak dilindungi.

 
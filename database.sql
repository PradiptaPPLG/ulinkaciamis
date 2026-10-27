-- UlinKaCiamis Database Schema
-- Import file ini ke phpMyAdmin

CREATE DATABASE IF NOT EXISTS ulinkaciamis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ulinkaciamis;

CREATE TABLE IF NOT EXISTS kategori (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon_url VARCHAR(255),
    warna VARCHAR(10) DEFAULT '#3B82F6',
    urutan INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS destinasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(255) NOT NULL,
    kategori_id INT NOT NULL,
    deskripsi TEXT,
    alamat VARCHAR(500),
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    harga VARCHAR(100) DEFAULT NULL,
    harga_min DECIMAL(15,2) DEFAULT NULL,
    harga_max DECIMAL(15,2) DEFAULT NULL,
    wifi TINYINT(1) DEFAULT 0,
    jam_buka TIME DEFAULT NULL,
    jam_tutup TIME DEFAULT NULL,
    no_telepon VARCHAR(30) DEFAULT NULL,
    website VARCHAR(255) DEFAULT NULL,
    foto VARCHAR(500) DEFAULT NULL,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif',
    ditambahkan_oleh VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(150) NOT NULL,
    username VARCHAR(80) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('superadmin', 'perangkat_desa') DEFAULT 'perangkat_desa',
    wilayah VARCHAR(150) DEFAULT NULL,
    aktif TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO kategori (nama, slug, warna, urutan) VALUES
('Wisata Alam', 'wisata-alam', '#10B981', 1),
('Wisata Buatan', 'wisata-buatan', '#6366F1', 2),
('Cafe & Kuliner', 'cafe-kuliner', '#F59E0B', 3),
('Minimarket', 'minimarket', '#EF4444', 4),
('SPBU / Pom Bensin', 'spbu', '#F97316', 5),
('UMKM & Toko', 'umkm-toko', '#8B5CF6', 6),
('Pasar', 'pasar', '#EC4899', 7),
('Kesehatan', 'kesehatan', '#06B6D4', 8),
('Pendidikan', 'pendidikan', '#3B82F6', 9),
('Keamanan', 'keamanan', '#64748B', 10),
('Bank & ATM', 'bank-atm', '#0EA5E9', 11),
('Transportasi', 'transportasi', '#84CC16', 12),
('Olahraga & GOR', 'olahraga-gor', '#F43F5E', 13),
('Hotel & Penginapan', 'hotel-penginapan', '#A855F7', 14),
('Ibadah', 'ibadah', '#059669', 15),
('Pemerintahan', 'pemerintahan', '#475569', 16);

-- Password default: password (bcrypt)
INSERT INTO admin_users (nama, username, password, role, wilayah) VALUES
('Super Administrator', 'superadmin', '$2y$10$TKh8H1.PfTVPpUBgQM/5meQi2.tSipuAhiR9pTp8bTPOqCb8P7Auu', 'superadmin', 'Ciamis'),
('Perangkat Desa Demo', 'perangkat', '$2y$10$TKh8H1.PfTVPpUBgQM/5meQi2.tSipuAhiR9pTp8bTPOqCb8P7Auu', 'perangkat_desa', 'Ciamis Kota');

INSERT INTO destinasi (nama, kategori_id, deskripsi, alamat, latitude, longitude, harga, wifi, jam_buka, jam_tutup, no_telepon, ditambahkan_oleh) VALUES
('Situ Lengkong Panjalu', 1, 'Danau alami yang indah dengan pulau di tengahnya, wisata alam dan religi di Ciamis', 'Panjalu, Ciamis, Jawa Barat', -7.1872, 108.2321, 'Rp 10.000', 0, '07:00:00', '17:00:00', NULL, 'superadmin'),
('Curug Tujuh Cibolang', 1, 'Air terjun bertingkat tujuh yang memesona di tengah hutan alami', 'Cikupa, Ciamis, Jawa Barat', -7.2950, 108.2810, 'Rp 15.000', 0, '07:00:00', '17:00:00', NULL, 'superadmin'),
('Pantai Pangandaran', 1, 'Pantai indah dengan pasir putih dan keindahan bawah laut', 'Pangandaran, Ciamis, Jawa Barat', -7.6884, 108.6530, 'Rp 25.000', 1, '00:00:00', '23:59:00', '(0265) 639125', 'superadmin'),
('Kolam Renang Tirta Yudha', 2, 'Kolam renang umum dengan fasilitas lengkap di pusat kota Ciamis', 'Jl. Ir. H. Juanda, Ciamis', -7.3310, 108.3600, 'Rp 15.000', 0, '07:00:00', '17:00:00', NULL, 'superadmin'),
('Stadion Galuh Ciamis', 13, 'Stadion sepak bola kebanggaan warga Ciamis', 'Jl. Stadion, Ciamis', -7.3295, 108.3580, 'Gratis', 0, '06:00:00', '18:00:00', NULL, 'superadmin'),
('RSUD Ciamis', 8, 'Rumah Sakit Umum Daerah Ciamis - pelayanan kesehatan 24 jam', 'Jl. Rumah Sakit No.76, Ciamis', -7.3280, 108.3642, NULL, 1, '00:00:00', '23:59:00', '(0265) 771281', 'superadmin'),
('Alfamart Jl. Sudirman', 4, 'Minimarket Alfamart cabang Jl. Sudirman Ciamis', 'Jl. Jend. Sudirman, Ciamis', -7.3265, 108.3630, NULL, 1, '07:00:00', '22:00:00', NULL, 'superadmin'),
('Indomaret Ciamis Kota', 4, 'Minimarket Indomaret di pusat kota Ciamis', 'Jl. Ir. H. Juanda No.12, Ciamis', -7.3300, 108.3620, NULL, 1, '07:00:00', '22:00:00', NULL, 'superadmin'),
('SPBU Pertamina Ciamis', 5, 'SPBU Pertamina di Ciamis Kota, layanan 24 jam', 'Jl. RE. Martadinata, Ciamis', -7.3315, 108.3650, NULL, 0, '00:00:00', '23:59:00', NULL, 'superadmin'),
('Pasar Manis Ciamis', 7, 'Pasar tradisional terbesar di Ciamis', 'Jl. Pasar Manis, Ciamis', -7.3290, 108.3605, NULL, 0, '04:00:00', '12:00:00', NULL, 'superadmin'),
('SMAN 1 Ciamis', 9, 'Sekolah Menengah Atas Negeri 1 Ciamis', 'Jl. Jend. Sudirman No.69, Ciamis', -7.3280, 108.3670, NULL, 1, '07:00:00', '16:00:00', '(0265) 771066', 'superadmin'),
('SDN 1 Ciamis', 9, 'Sekolah Dasar Negeri 1 Ciamis', 'Jl. Diponegoro, Ciamis', -7.3270, 108.3640, NULL, 0, '07:00:00', '13:00:00', NULL, 'superadmin'),
('Polsek Ciamis Kota', 10, 'Kepolisian Sektor Ciamis Kota, siap melayani 24 jam', 'Jl. RE Martadinata, Ciamis', -7.3255, 108.3635, NULL, 1, '00:00:00', '23:59:00', '(0265) 771110', 'superadmin'),
('Bank BRI Ciamis', 11, 'Bank Rakyat Indonesia Cabang Ciamis', 'Jl. Jend. Sudirman No.1, Ciamis', -7.3275, 108.3645, NULL, 1, '08:00:00', '15:00:00', '(0265) 771001', 'superadmin'),
('Terminal Ciamis', 12, 'Terminal bus Tipe B Ciamis', 'Jl. Terminal, Ciamis', -7.3330, 108.3590, NULL, 0, '05:00:00', '22:00:00', NULL, 'superadmin'),
('GOR Galuh Ciamis', 13, 'Gedung Olahraga serbaguna Galuh Ciamis', 'Jl. Stadion, Ciamis', -7.3288, 108.3575, 'Rp 20.000/jam', 0, '07:00:00', '21:00:00', NULL, 'superadmin'),
('Perpustakaan Daerah Ciamis', 16, 'Perpustakaan umum daerah Kabupaten Ciamis', 'Jl. Jend. Sudirman, Ciamis', -7.3260, 108.3655, 'Gratis', 1, '08:00:00', '16:00:00', NULL, 'superadmin'),
('Cafe Galuh Rasa', 3, 'Cafe cozy dengan nuansa Sunda, menu kopi dan kuliner lokal Ciamis', 'Jl. Diponegoro No.7, Ciamis', -7.3295, 108.3628, 'Rp 15.000 - Rp 50.000', 1, '09:00:00', '22:00:00', NULL, 'superadmin'),
('Mixue Ciamis', 3, 'Gerai es krim dan teh Mixue di Ciamis', 'Jl. Jend. Sudirman, Ciamis', -7.3268, 108.3638, 'Rp 10.000 - Rp 35.000', 0, '10:00:00', '22:00:00', NULL, 'superadmin'),
('Puskesmas Ciamis Kota', 8, 'Pusat Kesehatan Masyarakat Kecamatan Ciamis', 'Jl. Kesehatan, Ciamis', -7.3302, 108.3618, 'Gratis (BPJS)', 0, '07:30:00', '14:00:00', '(0265) 771200', 'superadmin');

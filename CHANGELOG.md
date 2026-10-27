# Changelog - UlinKa Ciamis

Dokumentasi seluruh riwayat perubahan, pembaruan fitur, perbaikan bug, dan peningkatan performa pada portal direktori dan pemetaan geografis **UlinKa Ciamis (Eksplorasi Wisata, Budaya, Kuliner & Fasilitas Publik Tatar Galuh Ciamis)**.

---

## [v1.4.0] - 2026-10-27
### 🚀 Fitur Baru & Peningkatan Besar

#### 1. Pengayaan Data Fasilitas Publik & Foto Asli Otentik
- **Total Destinasi & Fasilitas Meningkat:** Dari 46 titik menjadi **68 titik terverifikasi** di seluruh Kabupaten Ciamis.
- **Sekolah & Pendidikan:**
  - `SD Negeri 1 Panjalu` (foto asli gerbang, seragam merah putih, dan plang sekolah di pusat Panjalu).
  - `SD Negeri 1 Ciamis` & `SD Negeri 1 Kawali`.
  - `TK Negeri Pembina Ciamis` & `TK Pertiwi Panjalu`.
  - `SMP Negeri 1 Panjalu`, `SMP Negeri 1 Kawali`, `SMP Negeri 1 Ciamis`.
- **Bengkel & Layanan Otomotif (Kategori Baru `#EA580C`):**
  - `Bengkel Resmi AHASS Honda Panjalu` (foto asli authorized workshop, mekanik berseragam resmi, lift hidrolik).
  - `Bengkel Resmi AHASS Honda Motor Ciamis` (Jl. Ir. H. Juanda No. 88).
  - `Bengkel Resmi AHASS Kawali Motor` (Jl. Siliwangi No. 78).
  - `Bengkel Mobil & Ban Berkah Jaya Ciamis` (Spooring 3D, balancing, servis mesin).
  - `Bengkel Motor Sumber Rezeki Panjalu` (Jl. Raya Simpar No. 15).
- **Minimarket Modern:**
  - `Alfamart Panjalu Situ Lengkong` (foto asli tampak depan jalan Panjalu dengan latar perbukitan).
  - `Alfamart Kawali Siliwangi` & `Alfamart Ciamis Kota`.
  - `Indomaret Panjalu Situ Lengkong` & `Indomaret Simpang Tiga Kawali`.
- **Fasilitas Pelayanan Publik & Kesehatan:**
  - `Puskesmas Panjalu (Layanan IGD 24 Jam)` & `Puskesmas DTP Kawali`.
  - `Kantor Kecamatan Panjalu` & `Kantor Kecamatan Kawali`.
  - `Toko Bahan Bangunan Panjalu Jaya`.
  - `Apotek Kimia Farma Ciamis Juanda` & `Apotek K-24 Samuji (Buka 24 Jam)`.

#### 2. Pipeline Optimasi Gambar WebP (Bebas Lag 60 FPS)
- Mengganti seluruh foto placeholder/dummy browser dengan foto asli berkualitas tinggi.
- Mengimplementasikan pipeline otomatis [`optimize_images.py`](file:///assets/images/optimize_images.py):
  - Menghasilkan thumbnail WebP presisi 100x100px di folder `assets/images/thumbs/` dengan ukuran rata-rata **2.8 KB per pin** (reduksi ukuran sebesar **99.47%**).
  - Menghasilkan gambar optimal WebP 800px di folder `assets/images/optimized/` untuk tampilan modal/detail.
  - Mengeliminasi masalah lag saat zoom in dan zoom out pada peta Leaflet.

#### 3. Sistem Otoritas Wilayah Petugas ("Staff per Daerah")
- Penambahan akun resmi pengurus kewilayahan pada database:
  - `staff_panjalu` (Kewenangan khusus Kecamatan Panjalu).
  - `staff_kawali` (Kewenangan khusus Kecamatan Kawali).
  - `staff_sadananya` (Kewenangan khusus Kecamatan Sadananya).
  - `staff_ciamis` (Kewenangan khusus Kecamatan Ciamis Kota).
  - `superadmin` (Akses penuh seluruh 27 Kecamatan di Kabupaten Ciamis).
- **Pembatasan Teritorial Berlapis (Client & Server-Side):**
  - **Form Tambah (`admin/tambah.php`):** Kolom kecamatan otomatis terkunci ke wilayah staf dengan lencana `[Terkunci (Wilayah Anda)]`. Sistem memvalidasi koordinat; titik di luar batas kecamatan ditolak otomatis.
  - **Form Edit (`admin/edit.php`):** Staf daerah diblokir (*Access Denied*) jika mencoba mengedit data di luar kecamatannya.
  - **Manajemen Data (`admin/kelola.php`):** Staf hanya dapat melihat, memfilter, dan menghapus data pada wilayah tugasnya.
  - **Dashboard (`admin/dashboard.php`):** Metrik, ringkasan statistik, dan grafik kategori diisolasi sesuai wilayah staf.

#### 4. Pin Peta Draggable & Real-Time Auto-Generate Koordinat
- Marker pin pada formulir tambah & edit kini dapat di-drag langsung di atas peta (`draggable: true`).
- Nilai input **Latitude** dan **Longitude** terisi secara otomatis dan real-time saat marker digeser (`drag` dan `dragend`).
- Dilengkapi **Floating Coordinate HUD** (`Pin Aktif: lat, lng`) serta boundary warning yang memberi peringatan visual jika pin keluar dari batas wilayah kecamatan staf.
- Tombol **Fokus Titik Cepat** yang disesuaikan secara dinamis per wilayah (misal untuk Panjalu: Situ Lengkong, Alun-Alun Panjalu, Curug 7 Cibolang, Puskesmas Panjalu, dsb.).

#### 5. Pemilih Logo & Kategori Visual (Category Picker Grid)
- Menggantikan dropdown kategori monoton dengan **Category Picker Grid** interaktif.
- Setiap kategori dilengkapi ikon FontAwesome / Phosphor dan badge warna khusus.
- Pemilihan kategori secara langsung mengubah warna pin preview pada peta.

---

## [v1.3.0] - 2026-10-27
### ⚡ Perbaikan Interaksi Peta & Audit Geocoding Koordinat
- **Eliminasi Pin Hover Overlap:** Menghapus animasi `scale(1.24)` dan label popover melayang pada marker Leaflet yang sebelumnya saling menutupi dan menyebabkan lag.
- **Koreksi Geocoding Koordinat:** Memperbaiki alamat dan titik koordinat presisi untuk 46 destinasi ikonik di Ciamis (Alun-Alun Ciamis, Mie Gacoan Jl. Ahmad Yani No. 43, Jembatan Cirahong perbatasan Manonjaya-Ciamis, Cadas Ngampar Sadananya, Stadion Galuh, dsb.).

---

## [v1.2.0] - 2026-10-27
### 🎨 Redesign Antarmuka Admin & Hero Carousel
- **Executive Light Theme:** Menata ulang seluruh antarmuka admin panel dari darkmode/ai-slop menjadi tema profesional bernuansa ungu khas Galuh (`#7A0D66`) dan putih bersih.
- **Hero Carousel Beranda:** Menggantikan banner statis dengan slider visual interaktif menampilkan ikon wisata unggulan (Alun-Alun Ciamis, Jembatan Cirahong, Sungai Cireong, Situ Lengkong Panjalu).

---

## [v1.1.0] - 2026-10-26
### 🏛️ Fondasi Awal Direktori GIS
- Inisialisasi peta Leaflet dengan dukungan ubin Google Maps Hybrid dan OpenStreetMap.
- Struktur database MySQL untuk destinasi wisata, kategori, dan autentikasi administrator.
- Manajemen CRUD destinasi wisata dan fasilitas publik Kabupaten Ciamis.

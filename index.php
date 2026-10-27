<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ciamisku - Eksplorasi Wisata & Fasilitas Tatar Galuh Ciamis</title>
  <meta name="description" content="Direktori resmi dan peta panduan wisata, sejarah Galuh, kuliner, cafe, UMKM, dan fasilitas umum di Kabupaten Ciamis.">
  
  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Phosphor Icons -->
  <script src="https://unpkg.com/@phosphor-icons/web"></script>
  <!-- Google Font -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Leaflet.js untuk Peta Interaktif -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    .leaflet-pane { z-index: 10 !important; }
    .leaflet-top, .leaflet-bottom { z-index: 11 !important; }
    .custom-pin {
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 9999px;
      border: 2.5px solid #ffffff;
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.35);
      color: #ffffff;
      font-size: 14px;
      transition: transform 0.2s ease;
      cursor: pointer;
    }
    .custom-pin:hover {
      transform: scale(1.22);
    }
    .leaflet-popup-content-wrapper {
      border-radius: 16px;
      padding: 4px;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2);
    }
    .leaflet-popup-content {
      margin: 8px;
    }
  </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased pb-24 md:pb-12">

  <!-- ================= TOP NAVIGATION ================= -->
  <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-100 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
      <div class="flex items-center gap-3 cursor-pointer" onclick="switchTab('beranda')">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white shadow-md shadow-emerald-500/20">
          <i class="ph-bold ph-compass text-2xl"></i>
        </div>
        <div>
          <h1 class="text-xl font-extrabold bg-gradient-to-r from-emerald-700 to-teal-500 bg-clip-text text-transparent leading-none">Ciamisku</h1>
          <span class="text-[10px] text-slate-400 font-semibold tracking-wider uppercase">Jelajah Tatar Galuh</span>
        </div>
      </div>

      <!-- Desktop Nav Links -->
      <nav class="hidden md:flex items-center gap-1 bg-slate-100/80 p-1.5 rounded-2xl border border-slate-200/60">
        <button onclick="switchTab('beranda')" class="desktop-tab-btn bg-white text-emerald-700 shadow-sm px-4 py-2 rounded-xl text-xs font-bold transition duration-200" data-target="beranda">
          <i class="ph-bold ph-house mr-1 text-sm"></i> Beranda
        </button>
        <button onclick="switchTab('eksplor')" class="desktop-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-emerald-600 transition duration-200" data-target="eksplor">
          <i class="ph-bold ph-magnifying-glass mr-1 text-sm"></i> Eksplor (<span id="navTotalCount">0</span>)
        </button>
        <button onclick="switchTab('favorit')" class="desktop-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-emerald-600 transition duration-200" data-target="favorit">
          <i class="ph-bold ph-heart mr-1 text-sm"></i> Tersimpan (<span class="fav-count">0</span>)
        </button>
        <button onclick="switchTab('info')" class="desktop-tab-btn px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:text-emerald-600 transition duration-200" data-target="info">
          <i class="ph-bold ph-info mr-1 text-sm"></i> Info Wilayah
        </button>
      </nav>

      <!-- Portal Perangkat Desa / Admin Login Button -->
      <div class="flex items-center gap-2">
        <a href="/ulinkaciamis/admin/login.php" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition shadow-sm">
          <i class="ph-bold ph-shield-check text-sm text-emerald-400"></i>
          <span>Portal Pengurus</span>
        </a>
        <button onclick="switchTab('favorit')" class="md:hidden relative w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-600">
          <i class="ph-bold ph-heart text-lg"></i>
          <span class="fav-badge absolute -top-1 -right-1 w-4 h-4 bg-rose-500 text-white rounded-full text-[10px] flex items-center justify-center font-bold hidden">0</span>
        </button>
      </div>
    </div>
  </header>

  <!-- ================= TAB 1: BERANDA ================= -->
  <div id="tab-beranda" class="tab-content block">
    <!-- Hero Section -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 pt-5 pb-3">
      <div class="relative bg-gradient-to-br from-emerald-800 via-teal-800 to-slate-900 rounded-3xl p-6 sm:p-10 text-white overflow-hidden shadow-xl shadow-emerald-950/15">
        <div class="absolute -right-8 -bottom-8 w-64 h-64 bg-emerald-400/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute right-12 top-6 hidden lg:flex flex-col gap-2 bg-white/10 backdrop-blur-md border border-white/15 p-4 rounded-2xl w-64">
          <span class="text-[11px] font-bold text-emerald-200 uppercase tracking-wider">Info Singgah Jalur Selatan</span>
          <p class="text-xs text-slate-100 leading-relaxed">Melintas dari arah Bandung/Tasik menuju Pangandaran? Mampir 30–60 menit ke kuliner, situs Galuh, & pusat oleh-oleh Ciamis!</p>
          <button onclick="quickJumpMood('transit')" class="mt-1 py-1.5 px-3 bg-amber-400 hover:bg-amber-300 text-slate-900 font-bold text-[11px] rounded-xl transition flex items-center justify-center gap-1">
            <i class="ph-bold ph-car"></i> Lihat Spot Singgah Cepat
          </button>
        </div>

        <div class="max-w-xl relative z-10">
          <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/15 backdrop-blur-md text-emerald-200 text-xs font-semibold rounded-full mb-3">
            <i class="ph-fill ph-sparkle text-amber-400"></i> Direktori Resmi & Panduan Tatar Galuh
          </span>
          <h2 class="text-2xl sm:text-4xl font-extrabold leading-tight mb-2">
            Eksplorasi Wisata, Sejarah Galuh, Kuliner & Fasilitas di Ciamis
          </h2>
          <p class="text-xs sm:text-sm text-slate-200 mb-5 leading-relaxed">
            Dari segarnya aliran sungai Gunung Sawal, jejak Kerajaan Sunda Galuh, kuliner legendaris, spot WFC tenang, hingga fasilitas publik lengkap di pusat kota.
          </p>

          <div class="flex flex-wrap items-center gap-2.5">
            <button onclick="switchTab('eksplor')" class="px-5 py-3 rounded-2xl bg-white text-emerald-900 font-bold text-xs sm:text-sm shadow-md hover:bg-emerald-50 transition flex items-center gap-2">
              <i class="ph-bold ph-magnifying-glass"></i> Mulai Eksplorasi
            </button>
            <button onclick="quickJumpMood('gratis-murah')" class="px-4 py-3 rounded-2xl bg-white/15 hover:bg-white/25 backdrop-blur text-white font-semibold text-xs sm:text-sm transition flex items-center gap-1.5">
              <i class="ph-bold ph-ticket"></i> Wisata Gratis & Hemat
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- AKSES CEPAT KATEGORI BERANDA -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 py-2">
      <div class="grid grid-cols-3 sm:grid-cols-6 gap-2.5">
        <button onclick="quickJumpCategory('curug')" class="bg-white hover:border-blue-400 border border-slate-200/80 p-3 rounded-2xl flex flex-col items-center gap-1.5 shadow-sm transition group">
          <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl group-hover:scale-110 transition">
            <i class="ph-fill ph-drop"></i>
          </div>
          <span class="text-[11px] font-bold text-slate-700 text-center">Curug & Sungai</span>
        </button>
        <button onclick="quickJumpCategory('wisata-alam')" class="bg-white hover:border-teal-400 border border-slate-200/80 p-3 rounded-2xl flex flex-col items-center gap-1.5 shadow-sm transition group">
          <div class="w-10 h-10 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-xl group-hover:scale-110 transition">
            <i class="ph-fill ph-mountains"></i>
          </div>
          <span class="text-[11px] font-bold text-slate-700 text-center">Wisata Alam</span>
        </button>
        <button onclick="quickJumpCategory('sejarah')" class="bg-white hover:border-violet-400 border border-slate-200/80 p-3 rounded-2xl flex flex-col items-center gap-1.5 shadow-sm transition group">
          <div class="w-10 h-10 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center text-xl group-hover:scale-110 transition">
            <i class="ph-fill ph-bank"></i>
          </div>
          <span class="text-[11px] font-bold text-slate-700 text-center">Sejarah Galuh</span>
        </button>
        <button onclick="quickJumpCategory('cafe-kuliner')" class="bg-white hover:border-orange-400 border border-slate-200/80 p-3 rounded-2xl flex flex-col items-center gap-1.5 shadow-sm transition group">
          <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center text-xl group-hover:scale-110 transition">
            <i class="ph-fill ph-bowl-food"></i>
          </div>
          <span class="text-[11px] font-bold text-slate-700 text-center">Kuliner & UMKM</span>
        </button>
        <button onclick="quickJumpCategory('cafe-kuliner')" class="bg-white hover:border-amber-400 border border-slate-200/80 p-3 rounded-2xl flex flex-col items-center gap-1.5 shadow-sm transition group">
          <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl group-hover:scale-110 transition">
            <i class="ph-fill ph-coffee"></i>
          </div>
          <span class="text-[11px] font-bold text-slate-700 text-center">Cafe & WFC</span>
        </button>
        <button onclick="quickJumpCategory('fasilitas')" class="bg-white hover:border-sky-400 border border-slate-200/80 p-3 rounded-2xl flex flex-col items-center gap-1.5 shadow-sm transition group">
          <div class="w-10 h-10 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-xl group-hover:scale-110 transition">
            <i class="ph-fill ph-gas-pump"></i>
          </div>
          <span class="text-[11px] font-bold text-slate-700 text-center">Fasilitas Publik</span>
        </button>
      </div>
    </section>

    <!-- PETA DAERAH CIAMIS (INTERAKTIF DENGAN FILTER PIN) -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 py-4">
      <div class="bg-white p-4 sm:p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3 mb-4">
          <div>
            <h3 class="text-base sm:text-lg font-bold text-slate-800 flex items-center gap-2">
              <i class="ph-fill ph-map-pin text-emerald-600 text-xl"></i> Peta Interaktif Sebaran Destinasi Ciamis
            </h3>
            <p class="text-xs text-slate-500">Klik filter pin untuk menyaring kategori, atau klik pin di peta untuk info detail & rute.</p>
          </div>
          
          <!-- Filter Pin Peta Langsung -->
          <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar pb-1">
            <button onclick="filterMapMarkers('all')" class="map-filter-btn px-2.5 py-1.5 rounded-xl text-[11px] font-bold bg-slate-900 text-white whitespace-nowrap transition" data-mapcat="all">
              Semua Pin
            </button>
            <button onclick="filterMapMarkers('curug')" class="map-filter-btn px-2.5 py-1.5 rounded-xl text-[11px] font-semibold bg-slate-100 text-slate-600 hover:bg-blue-50 whitespace-nowrap flex items-center gap-1 transition" data-mapcat="curug">
              <span class="w-2.5 h-2.5 rounded-full bg-blue-500 inline-block"></span> Curug
            </button>
            <button onclick="filterMapMarkers('wisata-alam')" class="map-filter-btn px-2.5 py-1.5 rounded-xl text-[11px] font-semibold bg-slate-100 text-slate-600 hover:bg-teal-50 whitespace-nowrap flex items-center gap-1 transition" data-mapcat="wisata-alam">
              <span class="w-2.5 h-2.5 rounded-full bg-teal-500 inline-block"></span> Alam
            </button>
            <button onclick="filterMapMarkers('sejarah')" class="map-filter-btn px-2.5 py-1.5 rounded-xl text-[11px] font-semibold bg-slate-100 text-slate-600 hover:bg-violet-50 whitespace-nowrap flex items-center gap-1 transition" data-mapcat="sejarah">
              <span class="w-2.5 h-2.5 rounded-full bg-violet-600 inline-block"></span> Sejarah
            </button>
            <button onclick="filterMapMarkers('cafe-kuliner')" class="map-filter-btn px-2.5 py-1.5 rounded-xl text-[11px] font-semibold bg-slate-100 text-slate-600 hover:bg-orange-50 whitespace-nowrap flex items-center gap-1 transition" data-mapcat="cafe-kuliner">
              <span class="w-2.5 h-2.5 rounded-full bg-orange-500 inline-block"></span> Kuliner/Cafe
            </button>
            <button onclick="filterMapMarkers('spbu')" class="map-filter-btn px-2.5 py-1.5 rounded-xl text-[11px] font-semibold bg-slate-100 text-slate-600 hover:bg-amber-50 whitespace-nowrap flex items-center gap-1 transition" data-mapcat="spbu">
              <span class="w-2.5 h-2.5 rounded-full bg-amber-500 inline-block"></span> SPBU
            </button>
            <button onclick="filterMapMarkers('minimarket')" class="map-filter-btn px-2.5 py-1.5 rounded-xl text-[11px] font-semibold bg-slate-100 text-slate-600 hover:bg-red-50 whitespace-nowrap flex items-center gap-1 transition" data-mapcat="minimarket">
              <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block"></span> Minimarket
            </button>
            <button onclick="filterMapMarkers('fasilitas')" class="map-filter-btn px-2.5 py-1.5 rounded-xl text-[11px] font-semibold bg-slate-100 text-slate-600 hover:bg-sky-50 whitespace-nowrap flex items-center gap-1 transition" data-mapcat="fasilitas">
              <span class="w-2.5 h-2.5 rounded-full bg-sky-500 inline-block"></span> Fasilitas
            </button>
          </div>
        </div>

        <!-- Wadah Map Leaflet -->
        <div id="ciamisMap" class="w-full h-80 sm:h-[440px] rounded-2xl border border-slate-200 shadow-inner z-10"></div>
      </div>
    </section>

    <!-- SPOT REKOMENDASI POPULER -->
    <section class="max-w-6xl mx-auto px-4 sm:px-6 py-4">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-base sm:text-lg font-bold text-slate-800">Ikonik & Paling Banyak Dicari di Ciamis</h3>
          <p class="text-xs text-slate-500">Rekomendasi unggulan lintas kategori minggu ini</p>
        </div>
        <button onclick="switchTab('eksplor')" class="text-xs font-bold text-emerald-600 hover:text-emerald-700 flex items-center gap-1">
          Lihat Semua Destinasi <i class="ph-bold ph-arrow-right"></i>
        </button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5" id="homeHighlightGrid">
        <!-- Rendered via JS -->
      </div>
    </section>
  </div>

  <!-- ================= TAB 2: EKSPLOR TEMPAT ================= -->
  <div id="tab-eksplor" class="tab-content hidden">
    <section class="max-w-6xl mx-auto px-4 sm:px-6 pt-6 pb-2">
      <div class="bg-white p-4 sm:p-6 rounded-3xl border border-slate-200 shadow-sm mb-4">
        <!-- Search & Filter Kecamatan -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
          <div class="relative md:col-span-2">
            <i class="ph-bold ph-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
            <input 
              type="text" 
              id="searchInput" 
              placeholder="Cari 'Cadas Ngampar', 'Alfamart', 'SPBU', 'Astana Gede', 'WiFi'..." 
              class="w-full pl-12 pr-4 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-800 placeholder-slate-400 text-sm focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition"
              oninput="handleSearch()"
            >
          </div>
          <div class="relative">
            <i class="ph-bold ph-map-trifold absolute left-4 top-1/2 -translate-y-1/2 text-emerald-600 text-lg"></i>
            <select 
              id="kecamatanSelect" 
              onchange="handleKecamatanFilter()"
              class="w-full pl-11 pr-8 py-3 rounded-2xl bg-slate-50 border border-slate-200 text-slate-700 font-semibold text-sm focus:outline-none focus:ring-4 focus:ring-emerald-500/20 focus:border-emerald-500 transition appearance-none cursor-pointer"
            >
              <option value="all">Semua Kecamatan</option>
              <!-- Diisi dinamis via JS -->
            </select>
          </div>
        </div>

        <!-- Filter Kebutuhan / Mood -->
        <div class="mb-4">
          <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
            <i class="ph-fill ph-sparkle text-amber-500"></i> Kebutuhan Khusus / Suasana (Mood):
          </label>
          <div class="flex items-center gap-2 overflow-x-auto no-scrollbar pb-1">
            <button onclick="setMoodFilter('all')" class="mood-btn px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap border bg-slate-900 text-white border-slate-900 transition" data-mood="all">
              Semua Mood
            </button>
            <button onclick="setMoodFilter('transit')" class="mood-btn px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap border bg-slate-100 text-slate-600 border-slate-200 hover:border-emerald-500 transition" data-mood="transit">
              🚗 Singgah Jalur Nasional / SPBU
            </button>
            <button onclick="setMoodFilter('belajar')" class="mood-btn px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap border bg-slate-100 text-slate-600 border-slate-200 hover:border-emerald-500 transition" data-mood="belajar">
              💻 Nugas & WFC Tenang
            </button>
            <button onclick="setMoodFilter('air-jernih')" class="mood-btn px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap border bg-slate-100 text-slate-600 border-slate-200 hover:border-emerald-500 transition" data-mood="air-jernih">
              🌊 Main Air & Sungai Jernih
            </button>
            <button onclick="setMoodFilter('sejarah-budaya')" class="mood-btn px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap border bg-slate-100 text-slate-600 border-slate-200 hover:border-emerald-500 transition" data-mood="sejarah-budaya">
              🏛️ Napak Tilas & Ziarah Galuh
            </button>
            <button onclick="setMoodFilter('kuliner-malam')" class="mood-btn px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap border bg-slate-100 text-slate-600 border-slate-200 hover:border-emerald-500 transition" data-mood="kuliner-malam">
              🍜 Kuliner & Oleh-Oleh Khas
            </button>
            <button onclick="setMoodFilter('sunset')" class="mood-btn px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap border bg-slate-100 text-slate-600 border-slate-200 hover:border-emerald-500 transition" data-mood="sunset">
              🌅 Kabut Pagi & View Tinggi
            </button>
            <button onclick="setMoodFilter('keluarga')" class="mood-btn px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap border bg-slate-100 text-slate-600 border-slate-200 hover:border-emerald-500 transition" data-mood="keluarga">
              👨‍👩‍👧‍👦 Ramah Anak & Botram
            </button>
            <button onclick="setMoodFilter('gratis-murah')" class="mood-btn px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap border bg-slate-100 text-slate-600 border-slate-200 hover:border-emerald-500 transition" data-mood="gratis-murah">
              💸 Gratis / Ramah Kantong
            </button>
          </div>
        </div>

        <!-- Kategori Tempat (Filter Cepat) -->
        <div>
          <div class="flex items-center justify-between mb-2">
            <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Kategori Destinasi & Fasilitas:</label>
            <button onclick="resetAllFilters()" class="text-[11px] font-bold text-emerald-600 hover:underline">Reset Filter</button>
          </div>
          <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-2">
            <button onclick="setCategoryFilter('all')" class="cat-card p-2.5 rounded-2xl bg-emerald-50/70 border border-emerald-500 text-emerald-800 font-bold flex flex-col items-center justify-center gap-1 shadow-sm transition" data-cat="all">
              <i class="ph-bold ph-squares-four text-lg"></i>
              <span class="text-[11px]">Semua</span>
            </button>
            <button onclick="setCategoryFilter('curug')" class="cat-card p-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 font-semibold flex flex-col items-center justify-center gap-1 shadow-sm transition" data-cat="curug">
              <i class="ph-bold ph-drop text-lg text-blue-500"></i>
              <span class="text-[11px] truncate w-full text-center">Curug/Sungai</span>
            </button>
            <button onclick="setCategoryFilter('wisata-alam')" class="cat-card p-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 font-semibold flex flex-col items-center justify-center gap-1 shadow-sm transition" data-cat="wisata-alam">
              <i class="ph-bold ph-mountains text-lg text-teal-500"></i>
              <span class="text-[11px] truncate w-full text-center">Wisata Alam</span>
            </button>
            <button onclick="setCategoryFilter('sejarah')" class="cat-card p-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 font-semibold flex flex-col items-center justify-center gap-1 shadow-sm transition" data-cat="sejarah">
              <i class="ph-bold ph-bank text-lg text-violet-600"></i>
              <span class="text-[11px] truncate w-full text-center">Sejarah Galuh</span>
            </button>
            <button onclick="setCategoryFilter('cafe-kuliner')" class="cat-card p-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 font-semibold flex flex-col items-center justify-center gap-1 shadow-sm transition" data-cat="cafe-kuliner">
              <i class="ph-bold ph-coffee text-lg text-amber-500"></i>
              <span class="text-[11px] truncate w-full text-center">Cafe & Kuliner</span>
            </button>
            <button onclick="setCategoryFilter('spbu')" class="cat-card p-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 font-semibold flex flex-col items-center justify-center gap-1 shadow-sm transition" data-cat="spbu">
              <i class="ph-bold ph-gas-pump text-lg text-orange-500"></i>
              <span class="text-[11px] truncate w-full text-center">SPBU & Pom</span>
            </button>
            <button onclick="setCategoryFilter('minimarket')" class="cat-card p-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 font-semibold flex flex-col items-center justify-center gap-1 shadow-sm transition" data-cat="minimarket">
              <i class="ph-bold ph-storefront text-lg text-red-500"></i>
              <span class="text-[11px] truncate w-full text-center">Minimarket</span>
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Hasil Grid Destinasi -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 py-2">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h3 class="text-base sm:text-lg font-bold text-slate-800" id="resultTitle">Daftar Destinasi & Fasilitas Ciamis</h3>
          <p class="text-xs text-slate-500" id="resultCount">Memuat data destinasi dari database...</p>
        </div>
      </div>

      <div id="destinationsGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <!-- Render via JS -->
      </div>

      <!-- State Kosong -->
      <div id="emptyState" class="hidden text-center py-14 bg-white rounded-3xl border border-slate-200">
        <div class="w-16 h-16 bg-slate-100 text-slate-400 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
          <i class="ph-bold ph-magnifying-glass"></i>
        </div>
        <h4 class="text-base font-bold text-slate-700">Tidak ada tempat yang cocok</h4>
        <p class="text-xs text-slate-400 mt-1 mb-4">Coba ganti kata kunci pencarian, pilih kecamatan lain, atau reset filter.</p>
        <button onclick="resetAllFilters()" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition">
          Tampilkan Semua Destinasi
        </button>
      </div>
    </main>
  </div>

  <!-- ================= TAB 3: FAVORIT ================= -->
  <div id="tab-favorit" class="tab-content hidden max-w-6xl mx-auto px-4 sm:px-6 py-6">
    <div class="mb-5">
      <h2 class="text-2xl font-extrabold text-slate-800">Rencana Kunjungan Tersimpan</h2>
      <p class="text-xs text-slate-500">Daftar destinasi, kuliner, atau tempat favorit pilihanmu di Ciamis.</p>
    </div>

    <div id="favoritesGrid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"></div>

    <div id="favoriteEmptyState" class="hidden text-center py-16 bg-white rounded-3xl border border-slate-200">
      <div class="w-16 h-16 bg-rose-50 text-rose-400 rounded-full flex items-center justify-center mx-auto mb-3 text-2xl">
        <i class="ph-bold ph-heart"></i>
      </div>
      <h4 class="text-base font-bold text-slate-700">Belum ada tempat disimpan</h4>
      <p class="text-xs text-slate-400 mt-1 mb-4">Tekan ikon hati pada kartu destinasi untuk menyusun daftar kunjunganmu.</p>
      <button onclick="switchTab('eksplor')" class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-xs font-bold hover:bg-emerald-700 transition">
        Eksplor Ciamis Sekarang
      </button>
    </div>
  </div>

  <!-- ================= TAB 4: INFO WILAYAH ================= -->
  <div id="tab-info" class="tab-content hidden max-w-3xl mx-auto px-4 sm:px-6 py-6">
    <div class="bg-white rounded-3xl border border-slate-200 p-6 shadow-sm mb-4">
      <div class="flex items-center gap-4 mb-6">
        <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 text-white flex items-center justify-center text-2xl font-extrabold shadow-md shadow-emerald-500/20">
          TG
        </div>
        <div>
          <h3 class="text-lg font-bold text-slate-800">Direktori Inovasi Wilayah Tatar Galuh</h3>
          <p class="text-xs text-slate-400">Peta Resmi Informasi Wisata, UMKM & Fasilitas Publik</p>
          <span class="inline-block mt-1.5 px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px]">
            Kabupaten Ciamis, Jawa Barat
          </span>
        </div>
      </div>

      <!-- Statistik Ringkas Database -->
      <div class="grid grid-cols-3 gap-3 mb-6">
        <div class="bg-slate-50 border border-slate-100 p-3 rounded-2xl text-center">
          <span class="block text-xl font-extrabold text-emerald-700" id="statTotalPlaces">0</span>
          <span class="text-[11px] text-slate-500 font-medium">Total Spot Aktif</span>
        </div>
        <div class="bg-slate-50 border border-slate-100 p-3 rounded-2xl text-center">
          <span class="block text-xl font-extrabold text-teal-600" id="statTotalKec">0</span>
          <span class="text-[11px] text-slate-500 font-medium">Kecamatan</span>
        </div>
        <div class="bg-slate-50 border border-slate-100 p-3 rounded-2xl text-center">
          <span class="block text-xl font-extrabold text-rose-500 fav-count">0</span>
          <span class="text-[11px] text-slate-500 font-medium">Disimpan Anda</span>
        </div>
      </div>

      <div class="border-t border-slate-100 pt-4 space-y-3">
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
          <h4 class="text-xs font-bold text-slate-700 mb-1 flex items-center gap-1.5">
            <i class="ph-bold ph-shield-check text-emerald-600"></i> Untuk Pengurus Desa & Kecamatan
          </h4>
          <p class="text-xs text-slate-500 mb-3 leading-relaxed">
            Masing-masing pengurus desa atau kecamatan dapat login ke Portal Pengurus Wilayah untuk mendaftarkan dan memperbarui fasilitas, UMKM, atau destinasi wisata di daerahnya.
          </p>
          <a href="/ulinkaciamis/admin/login.php" class="inline-flex items-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-sm transition">
            <i class="ph-bold ph-key"></i> Masuk ke Portal Pengurus
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= MODAL DETAIL DESTINASI ================= -->
  <div id="detailModal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white w-full max-w-lg rounded-3xl overflow-hidden shadow-2xl flex flex-col max-h-[90vh]">
      <div class="relative h-56 w-full bg-slate-200">
        <img id="modalImg" src="" alt="" class="w-full h-full object-cover">
        <button onclick="closeModal()" class="absolute top-4 right-4 w-9 h-9 rounded-full bg-white/90 backdrop-blur flex items-center justify-center text-slate-700 hover:bg-white shadow">
          <i class="ph-bold ph-x text-lg"></i>
        </button>
        <span id="modalCategory" class="absolute bottom-4 left-4 px-3 py-1 bg-emerald-600/95 backdrop-blur text-white text-xs font-bold rounded-full shadow"></span>
      </div>
      <div class="p-5 overflow-y-auto">
        <div class="flex justify-between items-start gap-2 mb-1.5">
          <h3 id="modalTitle" class="text-xl font-extrabold text-slate-800"></h3>
          <div class="flex items-center gap-1 text-amber-500 font-bold text-sm bg-amber-50 px-2.5 py-1 rounded-xl shrink-0">
            <i class="ph-fill ph-star"></i>
            <span id="modalRating"></span>
          </div>
        </div>
        <p id="modalLocation" class="text-xs text-slate-500 font-medium flex items-center gap-1.5 mb-4">
          <i class="ph-fill ph-map-pin text-emerald-600 text-sm"></i> <span></span>
        </p>

        <!-- Info Jam Buka & Estimasi Biaya & WiFi -->
        <div class="grid grid-cols-2 gap-2.5 mb-4">
          <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-2xl flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
              <i class="ph-bold ph-ticket"></i>
            </div>
            <div>
              <span class="block text-[10px] text-slate-400 font-bold uppercase">Tiket / Biaya</span>
              <span id="modalPrice" class="text-xs font-bold text-slate-700"></span>
            </div>
          </div>
          <div class="bg-slate-50 border border-slate-100 p-2.5 rounded-2xl flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center shrink-0">
              <i class="ph-bold ph-clock"></i>
            </div>
            <div>
              <span class="block text-[10px] text-slate-400 font-bold uppercase">Jam Operasional</span>
              <span id="modalHours" class="text-xs font-bold text-slate-700"></span>
            </div>
          </div>
        </div>

        <p id="modalDesc" class="text-sm text-slate-600 mb-4 leading-relaxed"></p>

        <!-- Fasilitas Highlight -->
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Fasilitas & Keunggulan</h4>
        <div id="modalFeatures" class="flex flex-wrap gap-1.5 mb-6"></div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
          <button id="modalFocusMapBtn" class="w-full py-3 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-2xl flex items-center justify-center gap-2 text-xs transition">
            <i class="ph-bold ph-map-trifold text-emerald-600 text-base"></i> Sorot di Peta Ciamis
          </button>
          <a id="modalMapsLink" href="#" target="_blank" rel="noopener noreferrer" class="w-full py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-2xl flex items-center justify-center gap-2 text-xs shadow-lg shadow-emerald-600/25 transition">
            <i class="ph-bold ph-navigation-arrow text-base"></i> Rute Google Maps
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- ================= MOBILE BOTTOM NAVIGATION ================= -->
  <nav class="md:hidden fixed bottom-0 left-0 right-0 z-30 bg-white/95 backdrop-blur-md border-t border-slate-200 px-4 py-2 flex justify-around items-center">
    <button onclick="switchTab('beranda')" class="mobile-nav-btn flex flex-col items-center gap-1 text-emerald-600 font-bold text-[11px]" data-target="beranda">
      <i class="ph-fill ph-house text-2xl"></i>
      <span>Beranda</span>
    </button>
    <button onclick="switchTab('eksplor')" class="mobile-nav-btn flex flex-col items-center gap-1 text-slate-400 font-medium text-[11px]" data-target="eksplor">
      <i class="ph-bold ph-compass text-2xl"></i>
      <span>Eksplor</span>
    </button>
    <button onclick="switchTab('favorit')" class="mobile-nav-btn flex flex-col items-center gap-1 text-slate-400 font-medium text-[11px] relative" data-target="favorit">
      <i class="ph-bold ph-heart text-2xl"></i>
      <span class="fav-badge absolute top-0 right-3 w-4 h-4 bg-rose-500 text-white rounded-full text-[10px] flex items-center justify-center font-bold hidden">0</span>
      <span>Favorit</span>
    </button>
    <button onclick="switchTab('info')" class="mobile-nav-btn flex flex-col items-center gap-1 text-slate-400 font-medium text-[11px]" data-target="info">
      <i class="ph-bold ph-info text-2xl"></i>
      <span>Info</span>
    </button>
  </nav>

  <!-- ================= LOGIKA JAVASCRIPT & DATA DARI MYSQL ================= -->
  <script>
    // Konfigurasi Visual Kategori (Warna Pin & Icon Phosphor)
    const categoryMeta = {
      curug:           { label: "Curug & Sungai", color: "#2563eb", icon: "ph-drop", badgeBg: "bg-blue-50 text-blue-700" },
      'wisata-alam':   { label: "Wisata Alam", color: "#0d9488", icon: "ph-mountains", badgeBg: "bg-teal-50 text-teal-700" },
      sejarah:         { label: "Sejarah Galuh", color: "#7c3aed", icon: "ph-bank", badgeBg: "bg-violet-50 text-violet-700" },
      'cafe-kuliner':  { label: "Cafe & Kuliner", color: "#f97316", icon: "ph-coffee", badgeBg: "bg-orange-50 text-orange-700" },
      'umkm-toko':     { label: "UMKM & Oleh-Oleh", color: "#8b5cf6", icon: "ph-shopping-bag", badgeBg: "bg-purple-50 text-purple-700" },
      spbu:            { label: "SPBU / Pom Bensin", color: "#ea580c", icon: "ph-gas-pump", badgeBg: "bg-orange-50 text-orange-700" },
      minimarket:      { label: "Minimarket", color: "#dc2626", icon: "ph-storefront", badgeBg: "bg-red-50 text-red-700" },
      kesehatan:       { label: "Kesehatan & RS", color: "#0891b2", icon: "ph-first-aid", badgeBg: "bg-cyan-50 text-cyan-700" },
      keamanan:        { label: "Kepolisian", color: "#475569", icon: "ph-shield", badgeBg: "bg-slate-100 text-slate-700" },
      transportasi:    { label: "Transportasi", color: "#65a30d", icon: "ph-bus", badgeBg: "bg-lime-50 text-lime-700" },
      'olahraga-gor':  { label: "Olahraga & GOR", color: "#e11d48", icon: "ph-trophy", badgeBg: "bg-rose-50 text-rose-700" },
      'wisata-buatan': { label: "Taman & Kota", color: "#0284c7", icon: "ph-park", badgeBg: "bg-sky-50 text-sky-700" },
      default:         { label: "Fasilitas Publik", color: "#10b981", icon: "ph-map-pin", badgeBg: "bg-emerald-50 text-emerald-700" }
    };

    function getMetaForPlace(place) {
      const name = (place.nama || place.name || '').toLowerCase();
      if (name.includes('alfamart')) return { label: 'Minimarket Alfamart', color: '#dc2626', icon: 'ph-storefront', badgeBg: 'bg-red-50 text-red-700' };
      if (name.includes('indomaret')) return { label: 'Minimarket Indomaret', color: '#0284c7', icon: 'ph-storefront', badgeBg: 'bg-sky-50 text-sky-700' };
      if (name.includes('spbu') || name.includes('pertamina')) return { label: 'SPBU Pertamina', color: '#ea580c', icon: 'ph-gas-pump', badgeBg: 'bg-orange-50 text-orange-700' };
      if (name.includes('mixue') || name.includes('momoyo')) return { label: 'Es Krim & Teh', color: '#e11d48', icon: 'ph-ice-cream', badgeBg: 'bg-rose-50 text-rose-700' };
      if (name.includes('curug') || name.includes('sungai') || name.includes('cadas')) return categoryMeta.curug;
      if (name.includes('situ') || name.includes('hutan') || name.includes('puncak')) return categoryMeta['wisata-alam'];
      if (name.includes('situs') || name.includes('astana') || name.includes('karangkamulyan') || name.includes('museum')) return categoryMeta.sejarah;
      
      const slug = place.category || place.kategori_slug;
      return categoryMeta[slug] || categoryMeta.default;
    }

    const fallbackImage = "https://images.unsplash.com/photo-1470770841072-f978cf4d019e?w=700&auto=format&fit=crop";

    // Global State
    let destinations = [];
    let mapInstance = null;
    let markerLayerGroup = null;
    let currentMapFilter = 'all';
    let currentCategory = 'all';
    let currentMood = 'all';
    let currentKecamatan = 'all';
    let currentSearch = '';
    let favoriteIds = JSON.parse(localStorage.getItem('ciamisku_favorites') || '[]');

    // 1. Fetch Real Data from MySQL API
    async function loadDataFromAPI() {
      try {
        const response = await fetch('/ulinkaciamis/api/destinasi.php');
        const json = await response.json();
        if (json.status === 'ok' && Array.isArray(json.data)) {
          destinations = json.data;
        }
      } catch (err) {
        console.warn('API error, using fallback data:', err);
      }
      
      populateKecamatanOptions();
      renderDestinations();
      renderMapMarkers();
      updateFavoriteUI();
    }

    // 2. Inisialisasi Peta Leaflet dengan Esri World_Street_Map (Terbukti tidak 403 / No API Key!)
    function initCiamisMap() {
      // Batas wilayah Kabupaten Ciamis
      const ciamisCenter = [-7.3276, 108.3550];
      const southWest = L.latLng(-7.75, 108.05);
      const northEast = L.latLng(-7.00, 108.68);
      const bounds = L.latLngBounds(southWest, northEast);

      mapInstance = L.map('ciamisMap', {
        center: ciamisCenter,
        zoom: 12,
        minZoom: 10,
        maxZoom: 18,
        maxBounds: bounds,
        maxBoundsViscosity: 0.85
      });

      // Esri World Street Map: 100% Free, Reliable, Crystal Clear, No API Key, No 403 Blocks!
      L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', {
        attribution: '&copy; Esri & OpenStreetMap contributors',
        maxZoom: 19
      }).addTo(mapInstance);

      // Area Circle Highlight Kabupaten Ciamis
      L.circle(ciamisCenter, {
        color: '#10b981',
        fillColor: '#10b981',
        fillOpacity: 0.03,
        weight: 2,
        dashArray: '5, 8',
        radius: 20000
      }).addTo(mapInstance);

      markerLayerGroup = L.layerGroup().addTo(mapInstance);
      renderMapMarkers();
    }

    function renderMapMarkers() {
      if (!markerLayerGroup || !mapInstance) return;
      markerLayerGroup.clearLayers();

      const filtered = destinations.filter(p => {
        if (currentMapFilter === 'all') return true;
        if (currentMapFilter === 'curug') return (p.category === 'curug' || (p.nama && p.nama.toLowerCase().includes('curug')) || (p.nama && p.nama.toLowerCase().includes('sungai')));
        if (currentMapFilter === 'wisata-alam') return (p.category === 'wisata-alam' || p.category === 'alam');
        if (currentMapFilter === 'sejarah') return (p.category === 'sejarah' || (p.nama && p.nama.toLowerCase().includes('situs')));
        if (currentMapFilter === 'cafe-kuliner') return (p.category === 'cafe-kuliner' || p.category === 'cafe' || p.category === 'kuliner');
        if (currentMapFilter === 'spbu') return (p.category === 'spbu' || (p.nama && p.nama.toLowerCase().includes('spbu')));
        if (currentMapFilter === 'minimarket') return (p.category === 'minimarket' || (p.nama && p.nama.toLowerCase().includes('alfamart')) || (p.nama && p.nama.toLowerCase().includes('indomaret')));
        if (currentMapFilter === 'fasilitas') return (p.category === 'kesehatan' || p.category === 'keamanan' || p.category === 'transportasi' || p.category === 'olahraga-gor' || p.category === 'wisata-buatan');
        return p.category === currentMapFilter;
      });

      filtered.forEach(place => {
        const meta = getMetaForPlace(place);

        const customIcon = L.divIcon({
          className: '',
          html: `<div class="custom-pin" style="background:${meta.color}; width:32px; height:32px;">
                   <i class="ph-fill ${meta.icon}"></i>
                 </div>`,
          iconSize: [32, 32],
          iconAnchor: [16, 16],
          popupAnchor: [0, -16]
        });

        const lat = parseFloat(place.lat || place.latitude);
        const lng = parseFloat(place.lng || place.longitude);
        if (isNaN(lat) || isNaN(lng)) return;

        const marker = L.marker([lat, lng], { icon: customIcon }).addTo(markerLayerGroup);

        const img = place.image || place.foto || fallbackImage;
        const placeName = place.nama || place.name;
        const placeKec = place.kecamatan || 'Ciamis';
        const placePrice = place.price || place.harga || 'Gratis';
        const placeHours = place.hours || (place.jam_buka ? `${place.jam_buka} - ${place.jam_tutup}` : 'Setiap Hari');

        const popupContent = `
          <div style="font-family: 'Plus Jakarta Sans', sans-serif; width: 200px;">
            <img src="${img}" onerror="this.src='${fallbackImage}'" style="width:100%; height:92px; object-fit:cover; border-radius:10px; margin-bottom:6px;">
            <span style="font-size:9px; font-weight:800; color:${meta.color}; text-transform:uppercase;">${meta.label} • Kec. ${placeKec}</span>
            <b style="font-size:12px; display:block; color:#0f172a; margin:2px 0; line-height:1.3">${placeName}</b>
            <div style="font-size:10px; color:#64748b; margin-bottom:4px">⏰ ${placeHours}</div>
            ${place.wifi ? '<div style="font-size:10px; color:#0284c7; margin-bottom:4px">📶 WiFi Tersedia</div>' : ''}
            <span style="font-size:11px; font-weight:700; color:#10b981; display:block; margin-bottom:8px;">🎟️ ${placePrice}</span>
            <button onclick="openModal(${place.id})" style="background:#059669; color:white; border:none; padding:7px 10px; border-radius:8px; font-size:11px; font-weight:700; cursor:pointer; width:100%; display:flex; align-items:center; justify-content:center; gap:4px">
              Detail & Rute Maps →
            </button>
          </div>
        `;
        marker.bindPopup(popupContent);
      });
    }

    function filterMapMarkers(cat) {
      currentMapFilter = cat;
      document.querySelectorAll('.map-filter-btn').forEach(btn => {
        if (btn.dataset.mapcat === cat) {
          btn.className = 'map-filter-btn px-2.5 py-1.5 rounded-xl text-[11px] font-bold bg-slate-900 text-white whitespace-nowrap flex items-center gap-1 transition';
        } else {
          btn.className = 'map-filter-btn px-2.5 py-1.5 rounded-xl text-[11px] font-semibold bg-slate-100 text-slate-600 hover:bg-slate-200 whitespace-nowrap flex items-center gap-1 transition';
        }
      });
      renderMapMarkers();
    }

    // 3. Populate Dropdown Kecamatan
    function populateKecamatanOptions() {
      const select = document.getElementById('kecamatanSelect');
      const kecList = [...new Set(destinations.map(d => d.kecamatan).filter(Boolean))].sort();
      select.innerHTML = `<option value="all">Semua Kecamatan (${kecList.length})</option>` +
        kecList.map(k => `<option value="${k}">Kec. ${k}</option>`).join('');
      select.value = currentKecamatan;

      document.getElementById('statTotalKec').textContent = kecList.length;
    }

    // 4. Render Kartu Komponen Destinasi
    function createPlaceCard(item) {
      const isFav = favoriteIds.includes(item.id);
      const meta = getMetaForPlace(item);
      const img = item.image || item.foto || fallbackImage;
      const name = item.name || item.nama;
      const kec = item.kecamatan || 'Ciamis';
      const loc = item.location || item.alamat || `Kec. ${kec}, Kabupaten Ciamis`;
      const price = item.price || item.harga || 'Gratis';
      const rating = item.rating || 4.8;
      const desc = item.desc || item.deskripsi || '';
      const feats = Array.isArray(item.features) ? item.features : ['Akses Mudah', 'Fasilitas Nyaman'];

      return `
        <div class="bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition duration-300 flex flex-col justify-between">
          <div>
            <div class="relative h-44 w-full overflow-hidden bg-slate-200">
              <img src="${img}" onerror="this.src='${fallbackImage}'" alt="${name}" class="w-full h-full object-cover">
              
              <span class="absolute top-3 left-3 px-2.5 py-1 bg-white/95 backdrop-blur-md text-slate-800 text-[10px] font-extrabold rounded-full shadow-sm flex items-center gap-1">
                <span class="w-2 h-2 rounded-full" style="background:${meta.color}"></span>
                ${meta.label}
              </span>

              <button onclick="toggleFavorite(${item.id})" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 backdrop-blur flex items-center justify-center text-rose-500 shadow hover:scale-110 transition">
                <i class="${isFav ? 'ph-fill' : 'ph-bold'} ph-heart text-base"></i>
              </button>

              <div class="absolute bottom-3 left-3 px-2 py-0.5 bg-slate-900/75 backdrop-blur text-white text-[10px] font-semibold rounded-lg">
                ${price}
              </div>

              <div class="absolute bottom-3 right-3 px-2 py-0.5 bg-black/65 backdrop-blur text-white text-[11px] font-semibold rounded-lg flex items-center gap-1">
                <i class="ph-fill ph-star text-amber-400"></i> ${rating}
              </div>
            </div>

            <div class="p-4">
              <h4 class="font-bold text-slate-800 text-sm mb-1 leading-snug">${name}</h4>
              <p class="text-xs text-slate-400 flex items-center gap-1 mb-2.5">
                <i class="ph-bold ph-map-pin text-emerald-600 shrink-0"></i>
                <span class="truncate">${loc}</span>
              </p>
              
              <div class="flex flex-wrap gap-1 mb-2.5">
                ${feats.slice(0, 2).map(f => `
                  <span class="px-2 py-0.5 ${meta.badgeBg} text-[10px] font-semibold rounded-md">
                    ${f}
                  </span>
                `).join('')}
              </div>
              <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                ${desc}
              </p>
            </div>
          </div>
          
          <div class="p-4 pt-0">
            <button onclick="openModal(${item.id})" class="w-full py-2.5 rounded-xl border border-emerald-600 text-emerald-700 hover:bg-emerald-600 hover:text-white font-bold text-xs transition duration-200 flex items-center justify-center gap-1.5">
              Detail, Jam Buka & Rute <i class="ph-bold ph-arrow-right"></i>
            </button>
          </div>
        </div>
      `;
    }

    // 5. Render Grid Eksplor & Highlight Beranda
    function renderDestinations() {
      const grid = document.getElementById('destinationsGrid');
      const empty = document.getElementById('emptyState');
      const countLabel = document.getElementById('resultCount');

      const filtered = destinations.filter(item => {
        const cat = item.category || item.kategori_slug;
        const matchCategory = (currentCategory === 'all' || cat === currentCategory || 
                              (currentCategory === 'curug' && cat === 'curug') ||
                              (currentCategory === 'wisata-alam' && (cat === 'wisata-alam' || cat === 'alam')) ||
                              (currentCategory === 'sejarah' && cat === 'sejarah') ||
                              (currentCategory === 'cafe-kuliner' && (cat === 'cafe-kuliner' || cat === 'cafe' || cat === 'kuliner')) ||
                              (currentCategory === 'spbu' && cat === 'spbu') ||
                              (currentCategory === 'minimarket' && cat === 'minimarket'));

        const moods = Array.isArray(item.moods) ? item.moods : (typeof item.moods === 'string' ? item.moods.split(',').map(s=>s.trim()) : []);
        const matchMood = (currentMood === 'all' || moods.includes(currentMood));

        const kec = item.kecamatan || 'Ciamis';
        const matchKec = (currentKecamatan === 'all' || kec === currentKecamatan);

        const q = currentSearch.toLowerCase();
        const name = (item.name || item.nama || '').toLowerCase();
        const loc = (item.location || item.alamat || '').toLowerCase();
        const desc = (item.desc || item.deskripsi || '').toLowerCase();
        const matchSearch = !q || name.includes(q) || loc.includes(q) || kec.toLowerCase().includes(q) || desc.includes(q);

        return matchCategory && matchMood && matchKec && matchSearch;
      });

      countLabel.textContent = `Menampilkan ${filtered.length} dari ${destinations.length} destinasi & fasilitas di Ciamis`;
      document.getElementById('navTotalCount').textContent = destinations.length;
      document.getElementById('statTotalPlaces').textContent = destinations.length;

      if (filtered.length === 0) {
        grid.innerHTML = '';
        empty.classList.remove('hidden');
      } else {
        empty.classList.add('hidden');
        grid.innerHTML = filtered.map(item => createPlaceCard(item)).join('');
      }

      // Render 6 kartu highlight di Beranda
      const homeGrid = document.getElementById('homeHighlightGrid');
      if (homeGrid) {
        const highlights = destinations.slice(0, 6);
        homeGrid.innerHTML = highlights.map(item => createPlaceCard(item)).join('');
      }

      updateFavoriteUI();
    }

    // 6. Navigasi Tab
    function switchTab(targetTab) {
      document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
      const activeEl = document.getElementById(`tab-${targetTab}`);
      if (activeEl) activeEl.classList.remove('hidden');

      document.querySelectorAll('.desktop-tab-btn').forEach(btn => {
        if (btn.dataset.target === targetTab) {
          btn.classList.add('bg-white', 'text-emerald-700', 'shadow-sm');
          btn.classList.remove('text-slate-600');
        } else {
          btn.classList.remove('bg-white', 'text-emerald-700', 'shadow-sm');
          btn.classList.add('text-slate-600');
        }
      });

      document.querySelectorAll('.mobile-nav-btn').forEach(btn => {
        if (btn.dataset.target === targetTab) {
          btn.classList.remove('text-slate-400');
          btn.classList.add('text-emerald-600', 'font-bold');
        } else {
          btn.classList.remove('text-emerald-600', 'font-bold');
          btn.classList.add('text-slate-400');
        }
      });

      if (targetTab === 'favorit') renderFavorites();
      if (targetTab === 'beranda' && mapInstance) {
        setTimeout(() => mapInstance.invalidateSize(), 200);
      }
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function quickJumpCategory(cat) {
      resetAllFilters();
      setCategoryFilter(cat);
      switchTab('eksplor');
    }

    function quickJumpMood(mood) {
      resetAllFilters();
      setMoodFilter(mood);
      switchTab('eksplor');
    }

    // 7. Favorit Bookmark
    function toggleFavorite(id) {
      if (favoriteIds.includes(id)) {
        favoriteIds = favoriteIds.filter(favId => favId !== id);
      } else {
        favoriteIds.push(id);
      }
      localStorage.setItem('ciamisku_favorites', JSON.stringify(favoriteIds));
      renderDestinations();
      renderFavorites();
      updateFavoriteUI();
    }

    function updateFavoriteUI() {
      const count = favoriteIds.length;
      document.querySelectorAll('.fav-count').forEach(el => el.textContent = count);
      document.querySelectorAll('.fav-badge').forEach(el => {
        el.textContent = count;
        if (count > 0) el.classList.remove('hidden');
        else el.classList.add('hidden');
      });
    }

    function renderFavorites() {
      const favGrid = document.getElementById('favoritesGrid');
      const favEmpty = document.getElementById('favoriteEmptyState');
      const favItems = destinations.filter(d => favoriteIds.includes(d.id));

      if (favItems.length === 0) {
        favGrid.innerHTML = '';
        favEmpty.classList.remove('hidden');
      } else {
        favEmpty.classList.add('hidden');
        favGrid.innerHTML = favItems.map(item => createPlaceCard(item)).join('');
      }
    }

    // 8. Filters
    function setCategoryFilter(cat) {
      currentCategory = cat;
      document.querySelectorAll('.cat-card').forEach(btn => {
        if (btn.dataset.cat === cat) {
          btn.className = 'cat-card p-2.5 rounded-2xl bg-emerald-50/70 border border-emerald-500 text-emerald-800 font-bold flex flex-col items-center justify-center gap-1 shadow-sm transition';
        } else {
          btn.className = 'cat-card p-2.5 rounded-2xl bg-slate-50 border border-slate-200 text-slate-600 font-semibold flex flex-col items-center justify-center gap-1 shadow-sm transition';
        }
      });
      renderDestinations();
    }

    function setMoodFilter(mood) {
      currentMood = mood;
      document.querySelectorAll('.mood-btn').forEach(btn => {
        if (btn.dataset.mood === mood) {
          btn.className = 'mood-btn px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap border bg-slate-900 text-white border-slate-900 transition';
        } else {
          btn.className = 'mood-btn px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap border bg-slate-100 text-slate-600 border-slate-200 hover:border-emerald-500 transition';
        }
      });
      renderDestinations();
    }

    function handleKecamatanFilter() {
      currentKecamatan = document.getElementById('kecamatanSelect').value;
      renderDestinations();
    }

    function handleSearch() {
      currentSearch = document.getElementById('searchInput').value;
      renderDestinations();
    }

    function resetAllFilters() {
      currentSearch = '';
      currentKecamatan = 'all';
      document.getElementById('searchInput').value = '';
      document.getElementById('kecamatanSelect').value = 'all';
      setMoodFilter('all');
      setCategoryFilter('all');
    }

    // 9. Modal Detail
    function openModal(id) {
      const item = destinations.find(d => d.id === id);
      if (!item) return;

      const meta = getMetaForPlace(item);
      const modalImg = document.getElementById('modalImg');
      modalImg.src = item.image || item.foto || fallbackImage;
      modalImg.onerror = () => { modalImg.src = fallbackImage; };

      const name = item.name || item.nama;
      const kec = item.kecamatan || 'Ciamis';
      const loc = item.location || item.alamat || `Kec. ${kec}, Ciamis`;
      const price = item.price || item.harga || 'Gratis';
      const hours = item.hours || (item.jam_buka ? `${item.jam_buka} - ${item.jam_tutup} WIB` : 'Setiap Hari');
      const desc = item.desc || item.deskripsi || '';

      document.getElementById('modalCategory').textContent = `${meta.label} • Kec. ${kec}`;
      document.getElementById('modalTitle').textContent = name;
      document.getElementById('modalRating').textContent = item.rating || '4.8';
      document.getElementById('modalLocation').querySelector('span').textContent = loc;
      document.getElementById('modalPrice').textContent = price;
      document.getElementById('modalHours').textContent = hours;
      document.getElementById('modalDesc').textContent = desc;

      const feats = Array.isArray(item.features) ? item.features : ['Akses Mudah', 'Fasilitas Nyaman'];
      const featContainer = document.getElementById('modalFeatures');
      featContainer.innerHTML = feats.map(f => `
        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-800 text-xs font-semibold rounded-lg border border-emerald-100 flex items-center gap-1">
          <i class="ph-bold ph-check text-emerald-600"></i> ${f}
        </span>
      `).join('');

      const lat = item.lat || item.latitude;
      const lng = item.lng || item.longitude;
      document.getElementById('modalMapsLink').href = `https://www.google.com/maps?q=${lat},${lng}`;
      
      document.getElementById('modalFocusMapBtn').onclick = () => {
        closeModal();
        filterMapMarkers('all');
        switchTab('beranda');
        setTimeout(() => {
          mapInstance.flyTo([lat, lng], 15, { duration: 1.2 });
        }, 250);
      };

      const modal = document.getElementById('detailModal');
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    }

    function closeModal() {
      const modal = document.getElementById('detailModal');
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    }

    window.onclick = function(event) {
      if (event.target === document.getElementById('detailModal')) closeModal();
    };

    // Auto-init on page load
    window.addEventListener('DOMContentLoaded', () => {
      initCiamisMap();
      loadDataFromAPI();
    });
  </script>
</body>
</html>

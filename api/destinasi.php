<?php
require_once __DIR__ . '/../config.php';

$db = getDB();
$kategori_id = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$kategori_slug = isset($_GET['kategori_slug']) ? trim($_GET['kategori_slug']) : '';
$kecamatan = isset($_GET['kecamatan']) ? trim($_GET['kecamatan']) : '';
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "SELECT d.*, k.nama AS kategori_nama, k.slug AS kategori_slug, k.warna AS kategori_warna
        FROM destinasi d
        JOIN kategori k ON d.kategori_id = k.id
        WHERE d.status = 'aktif'";

$params = [];
$types = '';

if ($kategori_id > 0) {
    $sql .= " AND d.kategori_id = ?";
    $types .= 'i';
    $params[] = $kategori_id;
} elseif (!empty($kategori_slug) && $kategori_slug !== 'all') {
    $sql .= " AND k.slug = ?";
    $types .= 's';
    $params[] = $kategori_slug;
}

if (!empty($kecamatan) && $kecamatan !== 'all') {
    $sql .= " AND d.kecamatan = ?";
    $types .= 's';
    $params[] = $kecamatan;
}

if (!empty($search)) {
    $sql .= " AND (d.nama LIKE ? OR d.alamat LIKE ? OR d.deskripsi LIKE ? OR d.kecamatan LIKE ?)";
    $types .= 'ssss';
    $like = "%$search%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

$sql .= " ORDER BY d.id DESC";

$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$fallbackImage = "https://images.unsplash.com/photo-1470770841072-f978cf4d019e?w=700&auto=format&fit=crop";

$data = [];
while ($row = $result->fetch_assoc()) {
    $jam_formatted = '';
    if (!empty($row['jam_buka']) && !empty($row['jam_tutup'])) {
        $jam_formatted = substr($row['jam_buka'], 0, 5) . ' - ' . substr($row['jam_tutup'], 0, 5) . ' WIB';
    } elseif (!empty($row['hours_label'])) {
        $jam_formatted = $row['hours_label'];
    } else {
        $jam_formatted = 'Setiap Hari';
    }

    $feat_arr = [];
    if (!empty($row['features'])) {
        $feat_arr = array_filter(array_map('trim', explode(',', $row['features'])));
    }
    if (empty($feat_arr)) {
        if ($row['wifi']) $feat_arr[] = 'WiFi Kencang';
        $feat_arr[] = 'Akses Mudah';
        $feat_arr[] = 'Ramah Keluarga';
    }

    $mood_arr = [];
    if (!empty($row['moods'])) {
        $mood_arr = array_filter(array_map('trim', explode(',', $row['moods'])));
    }
    if (empty($mood_arr)) {
        $mood_arr = ['keluarga', 'transit'];
    }

    $img = !empty($row['foto']) ? $row['foto'] : $fallbackImage;

    $data[] = [
        'id'             => (int)$row['id'],
        'nama'           => $row['nama'],
        'name'           => $row['nama'], // alias for prototype compatibility
        'category'       => $row['kategori_slug'],
        'categoryName'   => $row['kategori_nama'],
        'kecamatan'      => !empty($row['kecamatan']) ? $row['kecamatan'] : 'Ciamis',
        'location'       => !empty($row['alamat']) ? $row['alamat'] : 'Kabupaten Ciamis',
        'deskripsi'      => $row['deskripsi'],
        'desc'           => $row['deskripsi'], // alias
        'alamat'         => $row['alamat'],
        'latitude'       => (float)$row['latitude'],
        'longitude'      => (float)$row['longitude'],
        'lat'            => (float)$row['latitude'], // alias
        'lng'            => (float)$row['longitude'], // alias
        'harga'          => !empty($row['harga']) ? $row['harga'] : 'Gratis',
        'price'          => !empty($row['harga']) ? $row['harga'] : 'Gratis', // alias
        'wifi'           => (bool)$row['wifi'],
        'jam_buka'       => $row['jam_buka'] ? substr($row['jam_buka'], 0, 5) : null,
        'jam_tutup'      => $row['jam_tutup'] ? substr($row['jam_tutup'], 0, 5) : null,
        'hours'          => $jam_formatted,
        'rating'         => !empty($row['rating']) ? (float)$row['rating'] : 4.8,
        'features'       => $feat_arr,
        'moods'          => $mood_arr,
        'image'          => $img,
        'foto'           => $img,
        'no_telepon'     => $row['no_telepon'],
        'kategori_id'    => (int)$row['kategori_id'],
        'kategori_nama'  => $row['kategori_nama'],
        'kategori_slug'  => $row['kategori_slug'],
        'kategori_warna' => $row['kategori_warna'],
        'ditambahkan_oleh'=> $row['ditambahkan_oleh'] ?? 'Admin',
    ];
}

jsonResponse(['status' => 'ok', 'data' => $data, 'total' => count($data)]);
?>

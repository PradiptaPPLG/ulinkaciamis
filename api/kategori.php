<?php
require_once __DIR__ . '/../config.php';

$db = getDB();
$result = $db->query("SELECT id, nama, slug, warna, urutan FROM kategori ORDER BY urutan ASC");
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'id'     => (int)$row['id'],
        'nama'   => $row['nama'],
        'slug'   => $row['slug'],
        'warna'  => $row['warna'],
        'urutan' => (int)$row['urutan'],
    ];
}

jsonResponse(['status' => 'ok', 'data' => $data]);
?>

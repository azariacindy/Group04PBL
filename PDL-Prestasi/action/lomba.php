<?php
include('../lib/Session.php');

$session = new Session();

// Cek apakah user sudah login
if ($session->get('is_login') !== true) {
    header('Location: login.php');
    exit();
}

include_once('../model/LombaModel.php');  // Ganti dengan lokasi file model lomba

$act = isset($_GET['act']) ? strtolower($_GET['act']) : '';

if ($act == 'load') {
    // Membuat objek LombaModel untuk mengambil data lomba
    $lomba = new LombaModel();  // Ganti dengan class model yang sesuai
    $data = $lomba->getData();  // Mengambil semua data lomba
    $result = [];
    $i = 1;

    // Mengambil data lomba dan menyiapkannya dalam format array untuk JSON
    while ($row = $data->fetch_assoc()) {
        $result['data'][] = [
            $i,
            $row['nama_lomba'],        // Ganti dengan kolom yang sesuai dari database
            $row['id_tingkat'],        // Ganti dengan kolom yang sesuai dari database
            $row['tanggal'],           // Ganti dengan kolom yang sesuai dari database
            $row['detail_lomba'],      // Ganti dengan kolom yang sesuai dari database
            $row['gambar'],            // Ganti dengan kolom yang sesuai untuk gambar
        ];
        $i++;
    }

    // Menampilkan hasil dalam format JSON
    echo json_encode($result);
    exit();
}
?>

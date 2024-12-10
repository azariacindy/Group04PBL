<?php
// Pastikan koneksi dan objek db sudah ada sebelumnya
include_once('../lib/Session.php');
include_once('../Model/daftarlombaModel.php');

// Fungsi untuk memperbarui status lomba
function updateStatusLomba($id_lomba, $status, $reason) {
    // Mendapatkan peran pengguna (admin atau bukan)
    $role = isset($_SESSION['role']) ? $_SESSION['role'] : '[user]'; // Misalnya 'admin' atau 'user'

    // Validasi status yang valid
    if ($status === 'rejected' && empty($reason)) {
        return json_encode([
            'status' => false,
            'message' => 'Alasan penolakan harus diisi jika statusnya ditolak.'
        ]);
    }

    // Admin bisa menyetujui atau menolak, user biasa hanya bisa 'in progress'
    if ($role == 'admin') {
        if ($status !== 'approved' && $status !== 'rejected') {
            return json_encode([
                'status' => false,
                'message' => 'Status hanya bisa diubah ke "approved" atau "rejected" untuk admin.'
            ]);
        }
    } else {
        if ($status !== 'in progress') {
            return json_encode([
                'status' => false,
                'message' => 'Status hanya bisa diubah ke "in progress" untuk user.'
            ]);
        }
    }

    // Update status lomba berdasarkan status yang diterima
    $sql = "UPDATE lomba SET status_input_lomba = ?, alasan_penolakan = ? WHERE id_lomba = ?";
    
    // Tentukan parameter untuk query berdasarkan status
    $params = [
        $status,
        ($status === 'approved') ? $reason : null,  // Set alasan penolakan hanya jika statusnya approved
        $id_lomba
    ];

    // Menjalankan query untuk memperbarui status lomba
    try {
        $db = new daftarlombaModel();  // Pastikan model sudah dibuat dengan benar
        $result = $db->updateData($id_lomba, $params);
        
        if ($result) {
            return json_encode([
                'status' => true,
                'message' => 'Status lomba berhasil diperbarui.'
            ]);
        } else {
            return json_encode([
                'status' => false,
                'message' => 'Gagal memperbarui status lomba.'
            ]);
        }
    } catch (Exception $e) {
        return json_encode([
            'status' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
}

// Proses request POST untuk mengubah status lomba
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'approve') {
    // Ambil data dari request AJAX
    $id_lomba = isset($_POST['id_lomba']) ? (int)$_POST['id_lomba'] : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $reason = isset($_POST['reason']) ? antiSqlInjection($_POST['reason']) : '';

    // Panggil fungsi untuk memperbarui status lomba
    echo updateStatusLomba($id_lomba, $status, $reason);
}

<?php
include('../lib/Session.php');
$session = new Session();
include_once('../Model/daftarlombaModel.php');
include_once('../lib/Secure.php');

$act = isset($_GET['act']) ? strtolower($_GET['act']) : '';

// Memeriksa peran pengguna
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '[user]'; // Defaultkan ke 'user' jika role tidak ditemukan
$id_user = isset($_SESSION['id_user']) ? (int)$_SESSION['id_user'] : 0;

if ($act == 'load') {
    
    // Inisialisasi model
    $daftarlomba = new daftarlombaModel();
    // Mengambil data lomba
    if ($role == 'admin') {
        $data = $daftarlomba->getData();
    }else{
        $data = $daftarlomba->getDataByIdUser($id_user);
    }
    $result = ['data' => []]; // Default: data kosong
    $i = 1;

    // Cek apakah ada data yang diambil
    if ($data !== false) {
    // Loop untuk memproses setiap data lomba
    while ($row = sqlsrv_fetch_array($data, SQLSRV_FETCH_ASSOC)) {
        // Menentukan status lomba berdasarkan status_input_lomba
        $status_text = '';
        if ($row['status_input_lomba'] === 'approved') {
            $status_text = '<span style="color: green;">Disetujui</span>';
        } elseif ($row['status_input_lomba'] === 'rejected') {
            $status_text = '<span style="color: red;">Ditolak: ' . '</span>';
        } else {
            $status_text = '<span style="color: orange;">Menunggu Persetujuan</span>';
        }

        // Tombol aksi untuk edit dan hapus
        $action_buttons = '
          <button class="btn btn-sm btn-warning" onclick="editData(' . htmlspecialchars(json_encode($row['id_lomba'])) . ')">
              <i class="fa fa-edit"></i> Edit
          </button>
          <button class="btn btn-sm btn-danger" onclick="deleteData(' . htmlspecialchars(json_encode($row['id_lomba'])) . ')">
              <i class="fa fa-trash"></i> Hapus
          </button>';

        // Tombol untuk mengubah status jika user adalah admin
        if ($role == 'admin') {
            $status_buttons = '
                <button class="btn btn-sm btn-success" onclick="updateStatus(' . htmlspecialchars(json_encode($row['id_lomba'])) . ', \'approved\')">
                    <i class="fa fa-check"></i> Disetujui
                </button>
                <button class="btn btn-sm btn-danger" onclick="updateStatus(' . htmlspecialchars(json_encode($row['id_lomba'])) . ', \'rejected\')">
                    <i class="fa fa-times"></i> Ditolak
                </button>';
        } else {
            $status_buttons = $status_text;
        }

        // Menambahkan data lomba ke array hasil tanpa menampilkan id_user atau nama_user
        $result['data'][] = [
            $i, // Nomor urut
            htmlspecialchars($row['nama_lomba'] ?? ''),
            htmlspecialchars($row['id_tingkat'] ?? ''),
            // Format tanggal 
            htmlspecialchars($row['tanggal'] instanceof DateTime ? $row['tanggal']->format('d M Y') : ($row['tanggal'] ?? '')),
            htmlspecialchars($row['detail_lomba'] ?? ''),
            '<img src="' . htmlspecialchars($row['gambar'] ?? '') . '" alt="Gambar Lomba" style="max-width: 100px; max-height: 100px;">',
           // Show action buttons
           $action_buttons,
           // Show status buttons only for admin
           $status_buttons,
           // Menampilkan status lomba
           $status_text,
        ];
        $i++;
    }
    }
    // Outputkan hasil dalam format JSON
    echo json_encode($result);
    exit();
}

if ($act == 'get') {
    $id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int)$_GET['id'] : 0;
    $daftarlomba = new daftarlombaModel();
    $data = $daftarlomba->getDataById($id);

    echo json_encode($data);
    exit;
}

if ($act == 'save') {
    if ($role === 'admin' || $role === 'mahasiswa' || $role === 'dosen') {
        $status_input_lomba = ($role === 'admin')
            ? 'approved'
            : 'in progress';

        $id_user = isset($_SESSION['id_user']) ? (int)antiSqlInjection($_SESSION['id_user']) : 0;

        $data = [
            'id_user' => $id_user,
            'nama_lomba' => antiSqlInjection($_POST['nama_lomba']),
            'id_tingkat' => (int)antiSqlInjection($_POST['id_tingkat']),
            'tanggal' => antiSqlInjection($_POST['tanggal']),
            'detail_lomba' => antiSqlInjection($_POST['detail_lomba']),
            'gambar' => antiSqlInjection($_POST['gambar']),
            'status_input_lomba' => $status_input_lomba
        ];

        try {
            $daftarlomba = new daftarlombaModel();
            $daftarlomba->insertData($data);

            echo json_encode([
                'status' => true,
                'message' => 'Data berhasil disimpan.'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Anda tidak memiliki izin untuk menyimpan data.'
        ]);
    }
}


if ($act == 'update') {
    $id = isset($_GET['id']) && ctype_digit($_GET['id']) ? (int)$_GET['id'] : 0;
    $daftarlomba = new daftarlombaModel();
    $row = $daftarlomba->getDataById($id);


    $data = [
        'nama_lomba' => antiSqlInjection($_POST['nama_lomba']),
        'id_tingkat' => (int)antiSqlInjection($_POST['id_tingkat']),
        'tanggal' => antiSqlInjection($_POST['tanggal']),
        'detail_lomba' => antiSqlInjection($_POST['detail_lomba']),
        'gambar' => antiSqlInjection($_POST['gambar'])
    ];

    $daftarlomba->updateData($id, $data);

    echo json_encode([
        'status' => true,
        'message' => 'Data daftar lomba berhasil diupdate.'
    ]);
    exit;
}

if ($act == 'delete') {
    $id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int)$_GET['id'] : 0;
    $daftarlomba = new daftarlombaModel();
    $row = $daftarlomba->getDataById($id);

    $daftarlomba->deleteData($id);
    echo json_encode([
        'status' => true,
        'message' => 'Data Daftar lomba berhasil dihapus.'
    ]);
    exit;
}

// update_status
if ($act == 'rejected') {
    $id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int)$_GET['id'] : 0;
    $status = isset($_GET['status_input_lomba']) ? $_GET['status_input_lomba'] : '';
    $reason = isset($_POST['reason']) ? antiSqlInjection($_POST['reason']) : '';

    // Validasi alasan penolakan
    if ($status == 'rejected' && empty($reason)) {
        echo json_encode(['status' => false, 'message' => 'Alasan penolakan harus diisi jika statusnya ditolak.']);
        exit;
    }

    // Memanggil fungsi updateStatus untuk memperbarui status
    $daftarlomba = new daftarlombaModel();
    $result = $daftarlomba->updateStatus($id, $status, $reason);

    echo json_encode([
        'status' => $result,
        'message' => $result ? 'Status lomba berhasil ditolak' : 'Gagal memperbarui status lomba.'
    ]);
    exit;
}



if ($act == 'update_status') {
    // Validasi input
    $id_lomba = isset($_POST['id_lomba']) ? (int)antiSqlInjection($_POST['id_lomba']) : 0;
    $status_input_lomba = isset($_POST['status_input_lomba']) ? antiSqlInjection($_POST['status_input_lomba']) : '';

    // Pastikan hanya admin yang bisa mengubah status
    if ($role !== 'admin') {
        echo json_encode([
            'status' => false,
            'message' => 'Anda tidak memiliki izin untuk mengubah status.'
        ]);
        exit;
    }

    try {
        // Membuat objek model dan memperbarui status lomba
        $daftarlomba = new daftarlombaModel();
        $daftarlomba->updateStatus($id_lomba, $status_input_lomba);

        echo json_encode([
            'status' => true,
            'message' => 'Status lomba berhasil diperbarui.'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}

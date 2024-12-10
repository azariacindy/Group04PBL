<?php
include('../lib/Session.php');
$session = new Session();
include_once('../Model/daftarlombaModel.php');
include_once('../lib/Secure.php');

$act = isset($_GET['act']) ? strtolower($_GET['act']) : '';

// Memeriksa peran pengguna
$role = isset($_SESSION['role']) ? $_SESSION['role'] : '[user]'; // Defaultkan ke 'user' jika role tidak ditemukan

if ($act == 'load') {
    // Inisialisasi model
    $lomba = new daftarlombaModel();
    // Mengambil data lomba
    $data = $lomba->getData();
    $result = [];
    $i = 1;

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
        
        // Menambahkan data lomba ke array hasil tanpa menampilkan id_user atau nama_user
        $result['data'][] = [
            $i, // Nomor urut
            htmlspecialchars($row['nama_lomba']), 
            htmlspecialchars($row['id_tingkat']),
            // Format tanggal 
            htmlspecialchars($row['tanggal'] instanceof DateTime ? $row['tanggal']->format('d M Y') : $row['tanggal']),
            htmlspecialchars($row['detail_lomba']),
            htmlspecialchars($row['gambar']),
           // Tombol untuk mengubah status jika user adalah admin
            ($role == 'admin') ? '
            <button class="btn btn-sm btn-success" onclick="updateStatus(' . htmlspecialchars(json_encode($row['id_lomba'])) . ', \'approved\')">
                <i class="fa fa-check"></i> Disetujui
            </button>
            <button class="btn btn-sm btn-danger" onclick="updateStatus(' . htmlspecialchars(json_encode($row['id_lomba'])) . ', \'rejected\')">
                <i class="fa fa-times"></i> Ditolak
            </button>
        ' : '',// Tombol untuk mengubah status jika user bukan admin
            // Tombol untuk edit dan hapus
            '<button class="btn btn-sm btn-warning" onclick="editData(' . htmlspecialchars(json_encode($row['id_lomba'])) . ')">
                <i class="fa fa-edit"></i> Edit
             </button>
             <button class="btn btn-sm btn-danger" onclick="deleteData(' . htmlspecialchars(json_encode($row['id_lomba'])) . ')">
                <i class="fa fa-trash"></i> Hapus
             </button>',
            // Menampilkan status lomba
            $status_text
        ];
        $i++;
    }

    // Outputkan hasil dalam format JSON
    echo json_encode($result);
    exit();
}

if ($act == 'get') {
    $id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int)$_GET['id'] : 0;
    $lomba = new daftarlombaModel();
    $data = $lomba->getDataById($id);

    echo json_encode($data);
    exit;
}

if ($act == 'save') {
    // Menentukan status lomba berdasarkan role
    $status_input_lomba = ($role === 'admin') 
        ? 'approved' // Jika admin, status otomatis "approved"
        : 'in progress';
    
    // Tentukan id_user berdasarkan role
    $id_user = ($role === 'admin') ? 1 : (isset($_POST['id_user']) ? (int)antiSqlInjection($_POST['id_user']) : 0);

    // Memeriksa apakah 'id_tingkat' ada dalam $_POST
    $data = [
        'id_user' => $id_user,  // Menambahkan id_user ke dalam data
        'nama_lomba' => isset($_POST['nama_lomba']) ? antiSqlInjection($_POST['nama_lomba']) : '',
        'id_tingkat' => isset($_POST['id_tingkat']) ? (int)antiSqlInjection($_POST['id_tingkat']) : 0,
        'tanggal' => isset($_POST['tanggal']) ? antiSqlInjection($_POST['tanggal']) : '',
        'detail_lomba' => isset($_POST['detail_lomba']) ? antiSqlInjection($_POST['detail_lomba']) : '',
        'gambar' => isset($_POST['gambar']) ? antiSqlInjection($_POST['gambar']) : '',
        'status_input_lomba' => $status_input_lomba
    ];

    try {
        // Membuat objek model dan memanggil insertData
        $lomba = new daftarlombaModel();
        $lomba->insertData($data);

        // Mengirimkan respons JSON jika data berhasil disimpan
        echo json_encode([
            'status' => true,
            'message' => 'Data berhasil disimpan.'
        ]);
    } catch (Exception $e) {
        // Menangani error dan mengirimkan respons JSON error
        echo json_encode([
            'status' => false,
            'message' => 'Terjadi kesalahan: ' . $e->getMessage()
        ]);
    }
    exit;
}

if ($act == 'update') {
    $id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int)$_GET['id'] : 0;
    $data = [
        'nama_lomba' => antiSqlInjection($_POST['nama_lomba']),
        'id_tingkat' => (int)antiSqlInjection($_POST['id_tingkat']),
        'tanggal' => antiSqlInjection($_POST['tanggal']),
        'detail_lomba' => antiSqlInjection($_POST['detail_lomba']),
        'gambar' => antiSqlInjection($_POST['gambar'])
    ];

    $daftarlomba = new daftarlombaModel();
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
    $daftarlomba->deleteData($id);

    echo json_encode([
        'status' => true,
        'message' => 'Data Daftar lomba berhasil dihapus.'
    ]);
    exit;
}
// update_status
if ($act == 'rejected') {
    if ($role !== 'admin') {
        echo json_encode([ 'status' => false, 'message' => 'Hanya admin yang dapat menolak lomba.' ]);
        exit;
    }

    $id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int)$_GET['id'] : 0;
    $status = isset($_GET['status_input_lomba']) ? $_GET['status_input_lomba'] : '';
    $reason = isset($_POST['reason']) ? antiSqlInjection($_POST['reason']) : '';

    // Validasi alasan penolakan
    if ($status == 'rejected' && empty($reason)) {
        echo json_encode([ 'status' => false, 'message' => 'Alasan penolakan harus diisi jika statusnya ditolak.' ]);
        exit;
    }

    // Memanggil fungsi updateStatus untuk memperbarui status
    $lomba = new daftarlombaModel();
    $result = $lomba->updateStatus($id, $status, $reason);

    echo json_encode([ 
        'status' => $result, 
        'message' => $result ? 'Status lomba berhasil ditolak' : 'Gagal memperbarui status lomba.' 
    ]);
    exit;
}

// Mengubah status lomba (approve)
if ($act == 'approved') {
    if ($role !== 'admin') {
        echo json_encode([ 'status' => false, 'message' => 'Hanya admin yang dapat menyetujui lomba.' ]);
        exit;
    }

    $id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int)$_GET['id'] : 0;
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $lomba = new daftarlombaModel();
    $result = $lomba->updateStatus($id, $status, null);

    echo json_encode([ 
        'status' => $result,
        'message' => $result ? 'Lomba berhasil disetujui.' : 'Gagal menyetujui lomba.'
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
        $lomba = new daftarlombaModel();
        $lomba->updateStatus($id_lomba, $status_input_lomba);

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



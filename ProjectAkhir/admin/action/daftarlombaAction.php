<?php
include('../../lib/Session.php');
$session = new Session();
include_once('../Model/daftarlombaModel.php');
include_once('../../lib/Secure.php');

$act = isset($_GET['act']) ? strtolower($_GET['act']) : '';

// Memeriksa peran pengguna
$role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user'; // Defaultkan ke 'user' jika role tidak ditemukan

if ($act == 'load') {
    $lomba = new daftarlombaModel();
    $data = $lomba->getData();
    $result = [];
    $i = 1; 

    while ($row = sqlsrv_fetch_array($data, SQLSRV_FETCH_ASSOC)) {
        $status_text = '';
        if ($row['status_input_lomba'] === 'approved') {
            $status_text = '<span style="color: green;">Disetujui</span>';
        } elseif ($row['status_input_lomba'] === 'rejected') {
            $status_text = '<span style="color: red;">Ditolak: ' . htmlspecialchars($row['alasan_penolakan']) . '</span>';
        } else {
            $status_text = '<span style="color: orange;">Menunggu Persetujuan</span>';
        }
        
        $result['data'][] = [
            $i, // Nomor urut
            htmlspecialchars($row['nama_lomba']), // Mengamankan output
            htmlspecialchars($row['id_tingkat']),
            htmlspecialchars($row['tanggal'] instanceof DateTime ? $row['tanggal']->format('Y-m-d') : $row['tanggal']), // Format tanggal
            htmlspecialchars($row['detail_lomba']),
            htmlspecialchars($row['gambar']),
            ($role == 'admin') ? '<button class="btn btn-sm btn-warning" onclick="approved(' . htmlspecialchars(json_encode($row['status_input_lomba'])) . ')">
                <i class="fa fa-check"></i>
             </button>' : '',
            '<button class="btn btn-sm btn-warning" onclick="editData(' . htmlspecialchars(json_encode($row['id_lomba'])) . ')">
                <i class="fa fa-edit"></i>
             </button>
             <button class="btn btn-sm btn-danger" onclick="deleteData(' . htmlspecialchars(json_encode($row['id_lomba'])) . ')">
                <i class="fa fa-trash"></i>
             </button>'
        ];
        $i++;
    }
    
    echo json_encode($result);
    exit;
}

if ($act == 'get') {
    $id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int)$_GET['id'] : 0;
    $lomba = new daftarlombaModel();
    $data = $lomba->getDataById($id);

    echo json_encode($data);
    exit;
}

if ($act == 'approve') {
    if ($role !== 'admin') {
        echo json_encode([
            'status' => false,
            'message' => 'Hanya admin yang dapat menyetujui lomba.'
        ]);
        exit;
    }

    $id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int)$_GET['id'] : 0;
    $lomba = new daftarlombaModel();

    $result = $lomba->updateStatus($id, 'approved', null);

    echo json_encode([
        'status' => $result,
        'message' => $result ? 'Lomba berhasil disetujui.' : 'Gagal menyetujui lomba.'
    ]);
    exit;
}

if ($act == 'save') {
    // Menentukan status lomba berdasarkan role
    $status_input_lomba = ($role === 'admin') ? 'approved' : 'pending';

    // Memeriksa apakah 'id_tingkat' ada dalam $_POST
    $data = [
        'nama_lomba' => isset($_POST['nama_lomba']) ? antiSqlInjection($_POST['nama_lomba']) : '',
        'id_tingkat' => isset($_POST['id_tingkat']) ? (int)antiSqlInjection($_POST['id_tingkat']) : 0,
        'tanggal' => isset($_POST['tanggal']) ? antiSqlInjection($_POST['tanggal']) : '',
        'detail_lomba' => isset($_POST['detail_lomba']) ? antiSqlInjection($_POST['detail_lomba']) : '',
        'gambar' => isset($_POST['gambar']) ? antiSqlInjection($_POST['gambar']) : '',
        'status_input_lomba' => $status_input_lomba
    ];

    try {
        $lomba = new daftarlombaModel();
        $lomba->insertData($data);
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

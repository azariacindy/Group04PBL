<?php
ini_set('display_errors', 1); 
error_reporting(E_ALL);

include('../lib/Session.php');
include_once('../model/PrestasiModel.php');
include_once('../lib/Secure.php');

$session = new Session();
if ($session->get('is_login') !== true) {
    header('Location: login.php');
    exit();
}

// Pastikan header ini ada agar respon JSON tidak tercampur dengan output lainnya
header('Content-Type: application/json');

// Mendapatkan aksi dari URL
$act = isset($_GET['act']) ? strtolower($_GET['act']) : '';

if ($act == 'load') {
    $prestasi = new PrestasiModel();
    $data = $prestasi->getData();
    $result = [];
    $i = 1;

    while ($row = $data->fetch_assoc()) {
        $result['data'][] = [
            $i,
            $row['id_prestasi'],
            $row['nim'],
            $row['nip'],
            $row['id_lomba'],
            $row['tanggal'],
            $row['detail_lomba'],
            $row['berkas'],
            $row['peringkat'],
            $row['status_lomba'],
            $row['status_validasi'],
            '<button class="btn btn-sm btn-warning" onclick="editData(' . $row['id_prestasi'] . ')"><i class="fa fa-edit"></i></button>
             <button class="btn btn-sm btn-danger" onclick="deleteData(' . $row['id_prestasi'] . ')"><i class="fa fa-trash"></i></button>'
        ];
        $i++;
    }

    echo json_encode($result);
    exit();
}

if ($act == 'get') {
    $id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? $_GET['id'] : 0;

    $prestasi = new PrestasiModel();
    $data = $prestasi->getDataById($id);
    
    if ($data) {
        echo json_encode($data);
    } else {
        echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan.']);
    }
    exit();
}

if ($act == 'save') {
    // Sanitasi input POST dan validasi
    $data = [
        'nim' => antiSqlInjection($_POST['nim']),
        'nip' => antiSqlInjection($_POST['nip']),
        'id_lomba' => antiSqlInjection($_POST['id_lomba']),
        'tanggal' => antiSqlInjection($_POST['tanggal']),
        'detail_lomba' => antiSqlInjection($_POST['detail_lomba']),
        'berkas' => isset($_FILES['berkas']) ? $_FILES['berkas']['name'] : '',
        'peringkat' => antiSqlInjection($_POST['peringkat']),
        'status_lomba' => antiSqlInjection($_POST['status_lomba']),
        'status_validasi' => antiSqlInjection($_POST['status_validasi'])
    ];

    // Pastikan file diupload dengan benar dan aman
    if ($_FILES['berkas']['name'] != '') {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $fileExt = pathinfo($_FILES['berkas']['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($fileExt), $allowedExtensions)) {
            echo json_encode(['status' => false, 'message' => 'Ekstensi file tidak diperbolehkan.']);
            exit();
        }

        $uploadPath = "../uploads/" . $_FILES['berkas']['name'];
        if (!move_uploaded_file($_FILES['berkas']['tmp_name'], $uploadPath)) {
            echo json_encode(['status' => false, 'message' => 'File gagal diupload.']);
            exit();
        }
    }

    $prestasi = new PrestasiModel();
    if ($prestasi->insertData($data)) {
        echo json_encode([
            'status' => true,
            'message' => 'Data berhasil disimpan.'
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Data gagal disimpan.'
        ]);
    }
    exit();
}

if ($act == 'update') {
    $id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? $_GET['id'] : 0;

    // Sanitasi dan validasi input
    $data = [
        'nim' => antiSqlInjection($_POST['nim']),
        'nip' => antiSqlInjection($_POST['nip']),
        'id_lomba' => antiSqlInjection($_POST['id_lomba']),
        'tanggal' => antiSqlInjection($_POST['tanggal']),
        'detail_lomba' => antiSqlInjection($_POST['detail_lomba']),
        'peringkat' => antiSqlInjection($_POST['peringkat']),
        'status_lomba' => antiSqlInjection($_POST['status_lomba']),
        'status_validasi' => antiSqlInjection($_POST['status_validasi'])
    ];

    // Update berkas jika ada file baru
    if ($_FILES['berkas']['name'] != '') {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
        $fileExt = pathinfo($_FILES['berkas']['name'], PATHINFO_EXTENSION);
        if (!in_array(strtolower($fileExt), $allowedExtensions)) {
            echo json_encode(['status' => false, 'message' => 'Ekstensi file tidak diperbolehkan.']);
            exit();
        }

        $uploadPath = "../uploads/" . $_FILES['berkas']['name'];
        if (move_uploaded_file($_FILES['berkas']['tmp_name'], $uploadPath)) {
            $data['berkas'] = $_FILES['berkas']['name']; // Update nama file yang diupload
        } else {
            echo json_encode(['status' => false, 'message' => 'File gagal diupload.']);
            exit();
        }
    }

    $prestasi = new PrestasiModel();
    if ($prestasi->updateData($id, $data)) {
        echo json_encode([
            'status' => true,
            'message' => 'Data Prestasi berhasil diperbarui.'
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Data Prestasi gagal diperbarui.'
        ]);
    }
    exit();
}

if ($act == 'delete') {
    $id = isset($_GET['id']) ? $_GET['id'] : 0;

    $prestasi = new PrestasiModel();
    if ($prestasi->deleteData($id)) {
        echo json_encode([
            'status' => true,
            'message' => 'Data Prestasi berhasil dihapus.'
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Data Prestasi gagal dihapus.'
        ]);
    }
    exit();
}

// Jika aksi tidak dikenali
echo json_encode([
    'status' => false,
    'message' => 'Aksi tidak dikenali.'
]);
exit();
?>

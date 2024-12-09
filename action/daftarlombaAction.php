<?php
include('../lib/Session.php');
include_once('../model/daftarlombaModel.php');
include_once('../lib/Secure.php');

$act = isset($_GET['act']) ? strtolower($_GET['act']) : '';

if ($act == 'load') {
    $lomba = new daftarlombaModel();
    $data = $lomba->getData();
    $result = [];
    $i = 1;

    while ($row = sqlsrv_fetch_array($data, SQLSRV_FETCH_ASSOC)) {
        $result['data'][] = [
            $i,
            htmlspecialchars($row['nama_lomba']),
            htmlspecialchars($row['tanggal']),
            htmlspecialchars($row['kategori']),
            htmlspecialchars($row['id_tingkat']),
            '<button class="btn btn-sm btn-warning" onclick="editData(' . $row['buku_id'] . ')"><i class="fa fa-edit"></i></button>
             <button class="btn btn-sm btn-danger" onclick="deleteData(' . $row['buku_id'] . ')"><i class="fa fa-trash"></i></button>'
        ];
        $i++;
    }

    echo json_encode($result);
}

if ($act == 'get') {
    $id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int)$_GET['id'] : 0;
    $buku = new daftarlombaModel();
    $data = $buku->getDataById($id);

    // if (empty($data['deskripsi'])) {
    //     $data['deskripsi'] = 'Tidak ada deskripsi';
    // }

    echo json_encode($data);
    exit;
}

if ($act == 'save') {
    $data = [
        'nama_lomba' => antiSqlInjection($_POST['nama_lomba']),
        'tanggal' => antiSqlInjection($_POST['tanggal']),
        'kategori' => antiSqlInjection($_POST['kategori']),
        'tingkat_id' => (int)antiSqlInjection($_POST['tingkat_id'])
    ];

    $buku = new daftarlombaModel();
    $buku->insertData($data);

    echo json_encode([
        'status' => true,
        'message' => 'Data berhasil disimpan.'
    ]);
    exit;
}

if ($act == 'update') {
    $id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int)$_GET['id'] : 0;
    $data = [
        'nama_lomba' => antiSqlInjection($_POST['nama_lomba']),
        'tanggal' => antiSqlInjection($_POST['tanggal']),
        'kategori' => antiSqlInjection($_POST['kategori']),
        'tingkat_id' => (int)antiSqlInjection($_POST['tingkat_id'])
    ];

    if (is_null($data['id_tingkat'])) {
        echo json_encode([
            'status' => false,
            'message' => 'Tingkat tidak boleh kosong.'
        ]);
        exit;
    }

    $buku = new daftarlombaModel();
    $buku->updateData($id, $data);

    echo json_encode([
        'status' => true,
        'message' => 'Data daftar lomba berhasil diupdate.'
    ]);
    exit;
}

if ($act == 'delete') {
    $id = (isset($_GET['id']) && ctype_digit($_GET['id'])) ? (int)$_GET['id'] : 0;
    $buku = new daftarlombaModel();
    $buku->deleteData($id);

    echo json_encode([
        'status' => true,
        'message' => 'Data Daftar lomba berhasil dihapus.'
    ]);
    exit;
}
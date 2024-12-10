<?php
include('../lib/Session.php');
$session = new Session();
include_once('../Model/prestasiModel.php');
include_once('../lib/Secure.php');

$act = isset($_GET['act']) ? strtolower($_GET['act']) : '';

// Konfigurasi direktori untuk upload file
$uploadDir = '../uploads/'; // Tentukan folder penyimpanan file
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true); // Membuat folder jika belum ada
}

if ($act == 'load') {
    $prestasi = new PrestasiModel();
    $data = $prestasi->getData(); // Mengambil semua data dari database menggunakan model

    $result = [];
    $i = 1;

    // Proses data untuk ditampilkan
    while ($row = sqlsrv_fetch_array($data, SQLSRV_FETCH_ASSOC)) {
        $result['data'][] = [
            $i, // Nomor urut
            htmlspecialchars($row['nim']), // NIM
            htmlspecialchars($row['nip']), // NIP
            htmlspecialchars($row['id_lomba']), // Nama Lomba
            htmlspecialchars($row['tanggal'] instanceof DateTime ? $row['tanggal']->format('d M Y') : $row['tanggal']), // Tanggal
            htmlspecialchars($row['detail_lomba']), // Detail Lomba
            htmlspecialchars($row['berkas']), // Link ke berkas
            htmlspecialchars($row['peringkat']), // Peringkat
            htmlspecialchars($row['status_lomba']), // Status Lomba
            '<button class="btn btn-sm btn-warning" onclick="editData(' . htmlspecialchars($row['id_prestasi']) . ')">Edit</button>
             <button class="btn btn-sm btn-danger" onclick="deleteData(' . htmlspecialchars($row['id_prestasi']) . ')">Hapus</button>' // Tombol aksi
        ];
        $i++;
    }

    // Output data dalam format JSON
    echo json_encode($result);
    exit;
}

if ($act == 'save') {
    $data = [
        'id_prestasi' => isset($_POST['id_prestasi']) ? antiSqlInjection($_POST['id_prestasi']) : '',
        'nim' => isset($_POST['nim']) ? antiSqlInjection($_POST['nim']) : '',
        'nip' => isset($_POST['nip']) ? antiSqlInjection($_POST['nip']) : '',
        'id_lomba' => isset($_POST['id_lomba']) ? (int)antiSqlInjection($_POST['id_lomba']) : 0,
        'tanggal' => isset($_POST['tanggal']) ? antiSqlInjection($_POST['tanggal']) : '',
        'detail_lomba' => isset($_POST['detail_lomba']) ? antiSqlInjection($_POST['detail_lomba']) : '',
        'berkas' => isset($_POST['berkas']) ? antiSqlInjection($_POST['berkas']) : '', // Simpan nama file ke database
        'peringkat' => isset($_POST['peringkat']) ? antiSqlInjection($_POST['peringkat']) : '',
        'status_lomba' => isset($_POST['status_lomba']) ? antiSqlInjection($_POST['status_lomba']) : '',
        'status_validasi' => isset($_POST['status_validasi']) ? antiSqlInjection($_POST['status_validasi']) : '',
    ];

    $prestasi = new PrestasiModel();

    try {
        $prestasi->insertData($data);
        echo json_encode(['status' => true, 'message' => 'Data berhasil disimpan.']);
    } catch (Exception $e) {
        echo json_encode(['status' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
    exit;
}

if ($act == 'update') {
    $id = isset($_POST['id_prestasi']) ? (int)antiSqlInjection($_POST['id_prestasi']) : 0;

    // Proses file upload (jika ada file baru)
    $fileName = isset($_POST['existing_berkas']) ? $_POST['existing_berkas'] : ''; // Nama file sebelumnya
    if (isset($_FILES['berkas']) && $_FILES['berkas']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['berkas']['tmp_name'];
        $originalFileName = $_FILES['berkas']['name'];
        $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);

        // Generate nama file unik
        $fileName = uniqid() . '.' . $fileExtension;
        $destPath = $uploadDir . $fileName;

        // Pindahkan file ke folder tujuan
        if (!move_uploaded_file($fileTmpPath, $destPath)) {
            echo json_encode(['status' => false, 'message' => 'Gagal mengupload file.']);
            exit;
        }
    }

    $data = [
        'nim' => antiSqlInjection($_POST['nim']),
        'nip' => antiSqlInjection($_POST['nip']),
        'id_lomba' => (int)antiSqlInjection($_POST['id_lomba']),
        'tanggal' => antiSqlInjection($_POST['tanggal']),
        'detail_lomba' => antiSqlInjection($_POST['detail_lomba']),
        'berkas' => $fileName, // Update nama file ke database
        'peringkat' => antiSqlInjection($_POST['peringkat']),
        'status_lomba' => antiSqlInjection($_POST['status_lomba']),
        'status_validasi' => antiSqlInjection($_POST['status_validasi']),
    ];

    $prestasi = new PrestasiModel();

    try {
        $prestasi->updateData($id, $data);
        echo json_encode(['status' => true, 'message' => 'Data berhasil diperbarui.']);
    } catch (Exception $e) {
        echo json_encode(['status' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
    }
    exit;
}
?>

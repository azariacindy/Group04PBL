<?php
// Turn off error reporting to prevent HTML errors
error_reporting(0);
ini_set('display_errors', 0);

session_start();
require_once(__DIR__ . '/../lib/Session.php');
require_once(__DIR__ . '/../Model/prestasiModel.php');

// Set JSON content type header for all AJAX responses
header('Content-Type: application/json');

// Error handler to catch all errors and return them as JSON
function handleError($errno, $errstr, $errfile, $errline) {
    echo json_encode([
        'status' => false,
        'message' => $errstr,
        'error_details' => [
            'file' => $errfile,
            'line' => $errline,
            'type' => $errno
        ]
    ]);
    exit;
}
set_error_handler('handleError');

// Function to handle file uploads
function handleFileUpload() {
    if (!isset($_FILES['berkas']) || $_FILES['berkas']['error'] === UPLOAD_ERR_NO_FILE) {
        throw new Exception('File berkas wajib diupload');
    }

    $file = $_FILES['berkas'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error saat upload file: ' . $file['error']);
    }

    // Validate file size (max 5MB)
    $maxSize = 5 * 1024 * 1024; // 5MB in bytes
    if ($file['size'] > $maxSize) {
        throw new Exception('Ukuran file terlalu besar. Maksimal 5MB');
    }

    // Validate file type
    $allowedTypes = ['application/pdf', 'image/jpeg', 'image/png'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception('Tipe file tidak diizinkan. Hanya PDF, JPEG, dan PNG yang diperbolehkan');
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFilename = uniqid('doc_') . '.' . $extension;
    
    // Create uploads directory if it doesn't exist
    $uploadDir = __DIR__ . '/../uploads/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception('Gagal membuat direktori upload');
        }
    }

    // Move file to uploads directory
    $destination = $uploadDir . $newFilename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new Exception('Gagal memindahkan file yang diupload');
    }

    return $newFilename;
}

// Catch any uncaught exceptions
set_exception_handler(function($e) {
    echo json_encode([
        'status' => false,
        'message' => $e->getMessage(),
        'error_details' => [
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]
    ]);
    exit;
});

// Clean any existing output
ob_clean();

try {
    $prestasi = new PrestasiModel();
    $session = new Session();

    if (!$session->get('is_login')) {
        throw new Exception('Sesi login telah berakhir');
    }

    $username = $session->get('user');
    $role = $session->get('role');
    $act = isset($_GET['act']) ? $_GET['act'] : '';

    if ($act == 'delete') {
        try {
            $id = isset($_GET['id']) ? $_GET['id'] : null;
            if (!$id) {
                throw new Exception("ID tidak valid");
            }

            // Ambil data prestasi
            $stmt = $prestasi->getDataById($id);
            if ($stmt === false) {
                throw new Exception("Gagal mengambil data prestasi");
            }

            $data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if (!$data) {
                throw new Exception("Data tidak ditemukan");
            }

            // Cek akses berdasarkan role
            if ($role != 'admin') {
                // Jika bukan admin, cek kepemilikan dan status validasi
                $nim = $prestasi->getNimFromUsername($username);
                if ($data['nim'] != $nim) {
                    throw new Exception("Anda tidak memiliki akses untuk menghapus data ini");
                }
                if ($data['status_validasi'] != 0) {
                    throw new Exception("Data yang sudah divalidasi tidak dapat dihapus");
                }
            }

            // Hapus file berkas jika ada
            if (!empty($data['berkas'])) {
                $filePath = __DIR__ . '/../uploads/' . $data['berkas'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Lakukan penghapusan
            $result = $prestasi->deleteData($id);
            if (!$result['status']) {
                throw new Exception($result['message']);
            }

            echo json_encode([
                'status' => true,
                'message' => 'Data berhasil dihapus'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    if ($act == 'load') {
        try {
            // Get data based on role
            if ($role == 'admin') {
                $stmt = $prestasi->getAllData();
            } else {
                // Get NIM for current user
                $nim = $prestasi->getNimFromUsername($username);
                if (!$nim) {
                    throw new Exception('NIM tidak ditemukan untuk user: ' . $username);
                }
                $stmt = $prestasi->getDataByNimUser($nim);
            }

            $data = [];
            $no = 1;
            
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                // Format tanggal
                if ($row['tanggal'] instanceof DateTime) {
                    $row['tanggal'] = $row['tanggal']->format('Y-m-d');
                }
                
                // Generate action buttons
                $buttons = '';
                if ($role == 'admin') {
                    // Admin bisa edit dan hapus semua data
                    $buttons = '<button onclick="editData(' . $row['id_prestasi'] . ')" class="btn btn-warning btn-sm mr-1"><i class="fas fa-edit"></i> Edit</button>';
                    $buttons .= '<button onclick="deleteData(' . $row['id_prestasi'] . ')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</button>';
                } else {
                    // Mahasiswa hanya bisa edit/hapus jika belum divalidasi (status_validasi = 0)
                    if ($row['status_validasi'] == 0) {
                        $buttons = '<button onclick="editData(' . $row['id_prestasi'] . ')" class="btn btn-warning btn-sm mr-1"><i class="fas fa-edit"></i> Edit</button>';
                        $buttons .= '<button onclick="deleteData(' . $row['id_prestasi'] . ')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</button>';
                    }
                }
                
                // Pastikan semua field yang diperlukan ada
                $row['no'] = $no++;
                $row['nama_mhs'] = isset($row['nama_mhs']) ? $row['nama_mhs'] : '';
                $row['nama_dosen'] = isset($row['nama_dosen']) ? $row['nama_dosen'] : '';
                $row['nama_lomba'] = isset($row['nama_lomba']) ? $row['nama_lomba'] : '';
                $row['nama_tingkat'] = isset($row['nama_tingkat']) ? $row['nama_tingkat'] : '';
                $row['detail_lomba'] = isset($row['detail_lomba']) ? $row['detail_lomba'] : '';
                $row['peringkat'] = isset($row['peringkat']) ? $row['peringkat'] : '';
                $row['status_lomba'] = isset($row['status_lomba']) ? $row['status_lomba'] : '';
                $row['status_validasi'] = isset($row['status_validasi']) ? (int)$row['status_validasi'] : 0;
                $row['alasan_validasi'] = isset($row['alasan_validasi']) ? $row['alasan_validasi'] : '';
                $row['action_buttons'] = $buttons;
                
                $data[] = $row;
            }
            
            echo json_encode([
                'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
                'recordsTotal' => count($data),
                'recordsFiltered' => count($data),
                'data' => $data
            ]);
        } catch (Exception $e) {
            error_log("Error in load action: " . $e->getMessage());
            echo json_encode([
                'error' => $e->getMessage(),
                'draw' => isset($_GET['draw']) ? intval($_GET['draw']) : 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }
        exit;
    }

    if ($act == 'get_alasan') {
        try {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                throw new Exception('ID prestasi tidak valid');
            }

            $data = $prestasi->getDataById($id);
            if (!$data) {
                throw new Exception('Data prestasi tidak ditemukan');
            }

            echo json_encode([
                'status' => true,
                'alasan' => $data['alasan'] ?? 'Tidak ada alasan yang tercatat'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    if ($act == 'get_nim') {
        if ($role == 'admin') {
            echo '';  // Admin tidak perlu NIM otomatis
            exit;
        }
        
        $nim = $session->get('nim');
        if (!$nim) {
            echo json_encode([
                'status' => false,
                'message' => 'NIM tidak ditemukan untuk user ini. Silakan hubungi admin.'
            ]);
            exit;
        }
        echo $nim;
        exit;
    }

    if ($act == 'get_dosen') {
        try {
            $stmt = $prestasi->getAllDosen();
            if ($stmt === false) {
                throw new Exception('Gagal mengambil data dosen');
            }
            
            $data = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                if ($row === false) {
                    throw new Exception('Error reading dosen data');
                }
                $data[] = $row;
            }
            
            echo json_encode([
                'status' => true,
                'message' => 'Data dosen berhasil diambil',
                'data' => $data
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    if ($act == 'get_lomba') {
        try {
            $stmt = $prestasi->getAllLomba();
            if ($stmt === false) {
                throw new Exception('Gagal mengambil data lomba');
            }
            
            $data = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                if ($row === false) {
                    throw new Exception('Error reading lomba data');
                }
                $data[] = $row;
            }
            
            echo json_encode([
                'status' => true,
                'message' => 'Data lomba berhasil diambil',
                'data' => $data
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    if ($act == 'save') {
        try {
            if ($role == 'admin') {
                // Admin bisa input untuk semua mahasiswa
                if (empty($_POST['nim'])) {
                    throw new Exception('NIM harus diisi');
                }
                $nim = $_POST['nim'];
            } else {
                // Debug session
                error_log("Session data: " . print_r($_SESSION, true));
                
                // Mahasiswa hanya bisa input untuk dirinya sendiri
                $nim = $session->get('nim');
                if (!$nim) {
                    // Coba ambil NIM dari username
                    $username = $session->get('user'); 
                    if ($username) {
                        $nim = $prestasi->getNimFromUsername($username);
                    }
                    
                    if (!$nim) {
                        throw new Exception('NIM tidak ditemukan untuk user ini. Username: ' . $username);
                    }
                }
            }

            $nip = $_POST['nip'];
            $id_lomba = $_POST['id_lomba'];
            $tanggal = $_POST['tanggal'];
            $detail_lomba = $_POST['detail_lomba'];
            $peringkat = $_POST['peringkat'];
            $status_lomba = $_POST['status_lomba'];
            
            // Handle file upload
            $fileName = handleFileUpload();
            
            $data = [
                'nim' => $nim,
                'nip' => $nip,
                'id_lomba' => $id_lomba,
                'tanggal' => $tanggal,
                'detail_lomba' => $detail_lomba,
                'berkas' => $fileName,
                'peringkat' => $peringkat,
                'status_lomba' => $status_lomba,
                'status_validasi' => 0  // Default belum divalidasi (0)
            ];

            $result = $prestasi->insertData($data);
            if (!$result['status']) {
                throw new Exception($result['message']);
            }

            echo json_encode([
                'status' => true,
                'message' => $result['message']
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    if ($act == 'get') {
        try {
            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                throw new Exception('ID prestasi tidak valid');
            }

            $stmt = $prestasi->getDataById($id);
            $data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            
            if (!$data) {
                throw new Exception('Data prestasi tidak ditemukan');
            }

            // Format tanggal jika ada
            if (isset($data['tanggal']) && $data['tanggal'] instanceof DateTime) {
                $data['tanggal'] = $data['tanggal']->format('Y-m-d');
            }

            echo json_encode([
                'status' => true,
                'message' => 'Data ditemukan',
                'data' => $data
            ]);
        } catch (Exception $e) {
            error_log("Error in get action: " . $e->getMessage());
            echo json_encode([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    if ($act == 'getAll') {
        try {
            $stmt = $prestasi->getData();
            $result = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                if (isset($row['tanggal']) && $row['tanggal'] instanceof DateTime) {
                    $row['tanggal'] = $row['tanggal']->format('Y-m-d');
                }
                $result[] = $row;
            }
            echo json_encode([
                'status' => true,
                'data' => $result
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    if ($act == 'update') {
        try {
            // Prevent admin from editing data
            if ($role == 'admin') {
                throw new Exception('Admin tidak diizinkan mengedit data prestasi');
            }

            $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
            if (!$id) {
                throw new Exception('ID prestasi tidak valid');
            }

            // Get current data
            $stmt = $prestasi->getDataById($id);
            if ($stmt === false) {
                throw new Exception('Gagal mengambil data prestasi');
            }

            $currentData = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if (!$currentData) {
                throw new Exception('Data prestasi tidak ditemukan');
            }

            // Check if data belongs to the user
            $nim = $prestasi->getNimFromUsername($username);
            if ($currentData['nim'] != $nim) {
                throw new Exception('Anda tidak memiliki akses untuk mengedit data ini');
            }

            // Check if data is already validated
            if ($currentData['status_validasi'] != 0) {
                throw new Exception('Data yang sudah divalidasi tidak dapat diedit');
            }

            // Mahasiswa bisa update semua field kecuali status_validasi
            $data = [
                'nip' => $_POST['nip'],
                'id_lomba' => $_POST['id_lomba'],
                'tanggal' => $_POST['tanggal'],
                'detail_lomba' => $_POST['detail_lomba'],
                'peringkat' => $_POST['peringkat'],
                'status_lomba' => $_POST['status_lomba']
            ];
            
            // Handle file upload jika ada file baru
            if (isset($_FILES['berkas']) && $_FILES['berkas']['size'] > 0) {
                // Delete old file if exists
                if (!empty($currentData['berkas'])) {
                    $oldFilePath = __DIR__ . '/../uploads/' . $currentData['berkas'];
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }
                $fileName = handleFileUpload();
                $data['berkas'] = $fileName;
            }

            $result = $prestasi->updateData($id, $data);
            if (!$result['status']) {
                throw new Exception($result['message']);
            }

            echo json_encode([
                'status' => true,
                'message' => $result['message']
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    if ($act == 'get_mahasiswa') {
        try {
            $stmt = $prestasi->getAllMahasiswa();
            $result = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $result[] = [
                    'nim' => $row['nim'],
                    'nama_mhs' => $row['nama_mhs']
                ];
            }
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }

    if ($act == 'validasi') {
        // Ensure clean output buffer
        ob_clean();
        header('Content-Type: application/json');
        
        try {
            // Validasi input
            $id_prestasi = isset($_POST['id_prestasi']) ? (int)$_POST['id_prestasi'] : 0;
            $status_validasi = isset($_POST['status_validasi']) ? $_POST['status_validasi'] : '';
            $alasan = isset($_POST['alasan']) ? $_POST['alasan'] : '';

            if (!$id_prestasi) {
                throw new Exception('ID prestasi tidak valid');
            }

            if (!in_array($status_validasi, ['0', '1', '2', '3'])) {
                throw new Exception('Status validasi tidak valid');
            }

            if ($status_validasi === '0' && empty($alasan)) {
                throw new Exception('Alasan penolakan wajib diisi');
            }

            // Update status validasi dan alasan
            $table = $prestasi->getTable(); // Get table name using getter method
            $sql = "UPDATE {$table} SET status_validasi = ?, alasan = ? WHERE id_prestasi = ?";
            $params = [$status_validasi, $alasan, $id_prestasi];
            
            $stmt = sqlsrv_query($prestasi->getConn(), $sql, $params);
            if ($stmt === false) {
                throw new Exception('Gagal memperbarui status validasi: ' . print_r(sqlsrv_errors(), true));
            }

            echo json_encode([
                'status' => true,
                'message' => 'Status validasi berhasil diperbarui'
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => $e->getMessage()
    ]);
}
?>

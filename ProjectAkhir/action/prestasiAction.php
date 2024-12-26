<?php
session_start();
require_once(__DIR__ . '/../lib/Session.php');
require_once(__DIR__ . '/../Model/prestasiModel.php');

$prestasi = new PrestasiModel();
$session = new Session();

if (!$session->get('is_login')) {
    header('Location: ../login.php');
    exit;
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
        $data = $prestasi->getDataById($id);
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

        // Lakukan penghapusan
        $result = $prestasi->deleteData($id);
        echo json_encode($result);
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

// Get NIM from username - will return in format 2001, 2002, etc.
$nim = $prestasi->getNimFromUsername($username);

// For debugging session
error_log("Full Session Data: " . print_r($_SESSION, true));
error_log("Username from session: " . $username);
error_log("Role from session: " . $role);
error_log("Is Login: " . ($session->get('is_login') ? 'true' : 'false'));

// For debugging
error_log("Username: " . $username);
error_log("NIM from lookup: " . $nim);
error_log("Role: " . $role);

// Konfigurasi direktori untuk upload file
$uploadDir = '../uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Fungsi untuk handle file upload
function handleFileUpload() {
    global $uploadDir;
    
    if (!isset($_FILES['berkas']) || $_FILES['berkas']['error'] == UPLOAD_ERR_NO_FILE) {
        return null;
    }

    $file = $_FILES['berkas'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error uploading file: ' . $file['error']);
    }

    $fileName = time() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        throw new Exception('Failed to move uploaded file');
    }

    return $fileName;
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
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if (!$id) {
            throw new Exception('ID prestasi tidak valid');
        }

        // Cek status validasi jika bukan admin
        if ($role != 'admin') {
            $stmt = $prestasi->getDataById($id);
            $currentData = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if (!$currentData) {
                throw new Exception('Data prestasi tidak ditemukan');
            }
            
            // Jika sudah divalidasi, mahasiswa tidak bisa edit
            if ($currentData['status_validasi'] != 0) {
                throw new Exception('Data prestasi yang sudah divalidasi tidak dapat diubah');
            }
            
            // Pastikan mahasiswa hanya bisa edit data miliknya
            if ($currentData['nim'] != $session->get('nim')) {
                throw new Exception('Anda tidak memiliki akses untuk mengubah data ini');
            }
        }

        // Jika admin, hanya bisa update status_validasi dan alasan_validasi
        if ($role == 'admin') {
            $status_validasi = isset($_POST['status_validasi']) ? (int)$_POST['status_validasi'] : 0;
            $alasan_validasi = isset($_POST['alasan_validasi']) ? $_POST['alasan_validasi'] : '';
            
            // Validasi status_validasi
            if (!in_array($status_validasi, [0, 1, 2, 3])) {
                throw new Exception('Status validasi tidak valid');
            }

            $data = [
                'status_validasi' => $status_validasi,
                'alasan_validasi' => $alasan_validasi
            ];
        } else {
            // Mahasiswa bisa update semua field kecuali status_validasi
            $nip = $_POST['nip'];
            $id_lomba = $_POST['id_lomba'];
            $tanggal = $_POST['tanggal'];
            $detail_lomba = $_POST['detail_lomba'];
            $peringkat = $_POST['peringkat'];
            $status_lomba = $_POST['status_lomba'];

            // Handle file upload jika ada file baru
            if (isset($_FILES['berkas']) && $_FILES['berkas']['size'] > 0) {
                $fileName = handleFileUpload();
                $data['berkas'] = $fileName;
            }

            $data = [
                'nip' => $nip,
                'id_lomba' => $id_lomba,
                'tanggal' => $tanggal,
                'detail_lomba' => $detail_lomba,
                'peringkat' => $peringkat,
                'status_lomba' => $status_lomba
            ];
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

if ($act == 'get_lomba') {
    try {
        $stmt = $prestasi->getAllLomba();
        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
        echo json_encode([
            'status' => true,
            'message' => 'Data lomba berhasil diambil',
            'data' => $data
        ]);
    } catch (Exception $e) {
        error_log("Error in get_lomba action: " . $e->getMessage());
        echo json_encode([
            'status' => false,
            'message' => $e->getMessage()
        ]);
    }
    exit;
}

if ($act == 'get_dosen') {
    try {
        $stmt = $prestasi->getAllDosen();
        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
        echo json_encode([
            'status' => true,
            'message' => 'Data dosen berhasil diambil',
            'data' => $data
        ]);
    } catch (Exception $e) {
        error_log("Error in get_dosen action: " . $e->getMessage());
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
?>

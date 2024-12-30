<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../lib/Session.php');
require_once(__DIR__ . '/../Model/prestasiModel.php');

$prestasi = new PrestasiModel();
$session = new Session();

// Check session
if (!$session->get('is_login')) {
    $response['status'] = false;
    $response['message'] = 'Anda harus login terlebih dahulu';
    echo json_encode($response);
    exit();
}

$role = $session->get('role');

// Set JSON content type header for all AJAX responses
header('Content-Type: application/json');

$act = isset($_GET['act']) ? $_GET['act'] : '';
$role = $session->get('role');

try {
    switch ($act) {
        case 'get_dosen':
            $data = $prestasi->getAllDosen();
            echo json_encode($data);
            break;

        case 'get_lomba':
            $data = $prestasi->getAllLomba();
            echo json_encode($data);
            break;

        case 'get_mahasiswa':
            $data = $prestasi->getAllMahasiswa();
            echo json_encode($data);
            break;

        case 'load':
            try {
                // Get data based on role
                if ($role == 'admin') {
                    $stmt = $prestasi->getAllData();
                } elseif ($role == 'dosen') {
                    // Get NIP for current user
                    $nip = $prestasi->getNipFromUsername($session->get('user'));
                    if (!$nip) {
                        throw new Exception('NIP tidak ditemukan untuk user: ' . $session->get('user'));
                    }
                    $stmt = $prestasi->getDataByNipUser($nip);
                } else {
                    // Get NIM for current user
                    $nim = $prestasi->getNimFromUsername($session->get('user'));
                    if (!$nim) {
                        throw new Exception('NIM tidak ditemukan untuk user: ' . $session->get('user'));
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
                    } elseif ($role == 'dosen') {
                        // Dosen bisa validasi jika status_validasi = 0
                        if ($row['status_validasi'] == 0) {
                            $buttons = '<button onclick="validasi(' . $row['id_prestasi'] . ')" class="btn btn-primary btn-sm"><i class="fas fa-check"></i> Validasi</button>';
                        }
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
                    $row['status_validasi'] = isset($row['status_validasi']) ? (int)$row['status_validasi'] : 0;
                    $row['alasan_validasi'] = isset($row['alasan_validasi']) ? $row['alasan_validasi'] : '';
                    $row['action'] = $buttons;
                    
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
            break;

        case 'save':
            try {
                // Get user ID from session
                $user_id = $session->get('id_user');
                
                // For admin, use the selected NIM
                // For students, get their own NIM
                if ($role === 'admin') {
                    if (empty($_POST['nim'])) {
                        throw new Exception('NIM harus dipilih');
                    }
                    $nim = $_POST['nim'];
                } else {
                    // Only students can submit their own prestasi
                    if ($role !== 'mahasiswa') {
                        throw new Exception('Unauthorized access');
                    }
                    $nim = $prestasi->getNimFromUsername($session->get('user'));
                    if (!$nim) {
                        throw new Exception('NIM tidak ditemukan');
                    }
                }

                // Handle custom lomba if needed
                if (isset($_POST['is_custom_lomba']) && $_POST['is_custom_lomba'] == '1') {
                    if (empty($_POST['custom_nama_lomba']) || empty($_POST['custom_tingkat'])) {
                        throw new Exception('Nama lomba dan tingkat harus diisi');
                    }
                    $_POST['id_lomba'] = $prestasi->createLomba($_POST['custom_nama_lomba'], $_POST['custom_tingkat']);
                }

                // Handle file upload
                if (isset($_FILES['berkas']) && $_FILES['berkas']['error'] == 0) {
                    $target_dir = "../uploads/prestasi/";
                    if (!file_exists($target_dir)) {
                        mkdir($target_dir, 0777, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['berkas']['name'], PATHINFO_EXTENSION));
                    $newFileName = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $newFileName;
                    
                    if (move_uploaded_file($_FILES['berkas']['tmp_name'], $target_file)) {
                        $_POST['berkas'] = $newFileName;
                    } else {
                        throw new Exception("Error uploading file.");
                    }
                }

                // Set the NIM in POST data
                $_POST['nim'] = $nim;
                
                // Set initial status
                $_POST['status_validasi'] = 0; // Pending validation
                if ($role === 'admin') {
                    $_POST['status_validasi'] = 1; // Auto-validate if admin submits
                }

                $result = $prestasi->save($_POST);
                if ($result) {
                    $response['status'] = true;
                    $response['message'] = 'Berhasil simpan prestasi';
                } else {
                    throw new Exception('Gagal menyimpan prestasi');
                }
            } catch (Exception $e) {
                $response['status'] = false;
                $response['message'] = $e->getMessage();
            }
            echo json_encode($response);
            break;

        case 'update':
            try {
                // Allow both admin and students to update, but with different permissions
                if ($role !== 'admin' && $role !== 'mahasiswa') {
                    throw new Exception('Unauthorized access');
                }

                // If admin, only allow updating status_validasi and alasan
                if ($role === 'admin') {
                    $allowedFields = ['status_validasi', 'alasan'];
                    $updateData = array_intersect_key($_POST, array_flip($allowedFields));
                } else {
                    // For students, don't allow updating status_validasi and alasan
                    $updateData = $_POST;
                    unset($updateData['status_validasi']);
                    unset($updateData['alasan']);
                }

                if ($prestasi->updateData($_POST['id_prestasi'], $updateData)) {
                    $response['status'] = true;
                    $response['message'] = 'Berhasil update prestasi';
                } else {
                    throw new Exception("Gagal update prestasi");
                }
            } catch (Exception $e) {
                $response['status'] = false;
                $response['message'] = $e->getMessage();
            }
            echo json_encode($response);
            break;

        case 'delete':
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
                    $nim = $prestasi->getNimFromUsername($session->get('user'));
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
            break;

        case 'get_alasan':
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
            break;

        case 'get_nim':
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
            break;

        case 'get':
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
            break;

        case 'getAll':
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
            break;

        case 'validasi':
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
            break;

        case 'get_tingkat_lomba':
            try {
                $id_lomba = isset($_GET['id_lomba']) ? $_GET['id_lomba'] : null;
                if (!$id_lomba) {
                    throw new Exception('ID Lomba tidak valid');
                }

                $data = $prestasi->getTingkatLomba($id_lomba);
                $response['status'] = true;
                $response['data'] = $data;
            } catch (Exception $e) {
                error_log("Error in get_tingkat_lomba: " . $e->getMessage());
                $response['status'] = false;
                $response['message'] = $e->getMessage();
            }
            echo json_encode($response);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => $e->getMessage()]);
}

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

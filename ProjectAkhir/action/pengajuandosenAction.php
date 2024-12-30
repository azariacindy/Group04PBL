<?php
require_once('../Model/pengajuanDosenModel.php');
require_once(__DIR__ . '/../lib/Session.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pengajuan = new PengajuanDosenModel();
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
$response = array('status' => false, 'message' => '');

try {
    switch ($act) {
        case 'load':
            try {
                // Get data based on role
                if ($role == 'admin') {
                    $stmt = $pengajuan->getData();
                } elseif ($role == 'dosen') {
                    $nip = $pengajuan->getNipFromUsername($session->get('user'));
                    if (!$nip) {
                        throw new Exception('NIP tidak ditemukan');
                    }
                    error_log("NIP for user " . $session->get('user') . ": " . $nip);
                    $stmt = $pengajuan->getDataByNip($nip);
                } else {
                    $nim = $pengajuan->getNimFromUsername($session->get('user'));
                    if (!$nim) {
                        throw new Exception('NIM tidak ditemukan');
                    }
                    error_log("NIM for user " . $session->get('user') . ": " . $nim);
                    $stmt = $pengajuan->getDataByNim($nim);
                }

                $data = [];
                $no = 1;
                
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    // Format tanggal
                    if ($row['tanggal'] instanceof DateTime) {
                        $row['tanggal'] = $row['tanggal']->format('d M y');
                    }
                    
                    // Generate action buttons based on role
                    $buttons = '';
                    if ($role == 'dosen') {
                        if ($row['status_validasi'] == 'Pending') {
                            $buttons = '<button onclick="validasi(' . $row['id_pengajuan'] . ')" class="btn btn-primary btn-sm mr-1">Validasi</button>';
                        }
                    }
                    
                    // Format status
                    $status = '';
                    switch($row['status_validasi']) {
                        case 'Pending':
                            $status = '<span class="badge badge-warning">Pending</span>';
                            break;
                        case 'Disetujui':
                            $status = '<span class="badge badge-success">Disetujui</span>';
                            break;
                        case 'Ditolak':
                            $status = '<span class="badge badge-danger">Ditolak</span>';
                            if (!empty($row['alasan'])) {
                                $status .= '<br><small class="text-muted">Alasan: ' . htmlspecialchars($row['alasan']) . '</small>';
                            }
                            break;
                    }
                    
                    $row['no'] = $no++;
                    $row['status'] = $status;
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
                if ($role !== 'mahasiswa') {
                    throw new Exception('Hanya mahasiswa yang dapat mengajukan dosen pembimbing');
                }

                $nim = $pengajuan->getNimFromUsername($session->get('user'));
                if (!$nim) {
                    throw new Exception('NIM tidak ditemukan');
                }

                // Check if student already has a pending request
                if ($pengajuan->checkExistingPengajuan($nim)) {
                    throw new Exception('Anda masih memiliki pengajuan yang belum divalidasi');
                }

                $_POST['nim'] = $nim;
                if ($pengajuan->save($_POST)) {
                    $response['status'] = true;
                    $response['message'] = 'Berhasil mengajukan dosen pembimbing';
                } else {
                    throw new Exception('Gagal mengajukan dosen pembimbing');
                }
            } catch (Exception $e) {
                $response['status'] = false;
                $response['message'] = $e->getMessage();
            }
            echo json_encode($response);
            break;

        case 'validasi':
            try {
                if ($role !== 'dosen') {
                    throw new Exception('Hanya dosen yang dapat memvalidasi pengajuan');
                }

                $id_pengajuan = isset($_POST['id_pengajuan']) ? $_POST['id_pengajuan'] : null;
                $status = isset($_POST['status']) ? $_POST['status'] : null;
                $alasan = isset($_POST['alasan']) ? $_POST['alasan'] : null;

                if (!$id_pengajuan || !$status) {
                    throw new Exception('Data tidak lengkap');
                }

                if ($pengajuan->updateStatus($id_pengajuan, $status, $alasan)) {
                    $response['status'] = true;
                    $response['message'] = 'Berhasil memvalidasi pengajuan';
                } else {
                    throw new Exception('Gagal memvalidasi pengajuan');
                }
            } catch (Exception $e) {
                $response['status'] = false;
                $response['message'] = $e->getMessage();
            }
            echo json_encode($response);
            break;

        case 'get_dosen':
            try {
                $sql = "SELECT nip, nama_dosen FROM dosen";
                $stmt = sqlsrv_query($pengajuan->getConnection(), $sql);
                
                if ($stmt === false) {
                    throw new Exception("Error loading dosen");
                }
                
                $data = array();
                while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                    $data[] = $row;
                }
                
                $response['status'] = true;
                $response['data'] = $data;
            } catch (Exception $e) {
                $response['status'] = false;
                $response['message'] = $e->getMessage();
            }
            echo json_encode($response);
            break;

        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    $response['status'] = false;
    $response['message'] = $e->getMessage();
    echo json_encode($response);
}
?>

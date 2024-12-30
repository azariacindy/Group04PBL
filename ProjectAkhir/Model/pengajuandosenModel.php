<?php
include_once(__DIR__ . '/Model.php');

class PengajuanDosenModel extends Model {
    protected $db;
    protected $driver;
    protected $table = '[pengajuan_dosen]';

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        include(__DIR__ . '/../lib/Connection.php');
        if (!$db) {
            die('Koneksi database gagal: ' . print_r(sqlsrv_errors(), true));
        }
        $this->db = $db;
        $this->driver = $use_driver;
    }

    public function getConnection() {
        return $this->db;
    }

    // Required abstract methods implementation
    public function insertData($data) {
        return $this->save($data);
    }

    public function getData() {
        try {
            $sql = "SELECT pd.*, m.nama_mhs, d.nama_dosen 
                    FROM {$this->table} pd
                    INNER JOIN [mahasiswa] m ON pd.nim = m.nim
                    INNER JOIN [dosen] d ON pd.nip = d.nip
                    ORDER BY pd.tanggal DESC";
            
            error_log("SQL Query: " . $sql); // Debug log
            
            $stmt = sqlsrv_query($this->db, $sql);
            
            if ($stmt === false) {
                $errors = sqlsrv_errors();
                error_log("SQL Error: " . print_r($errors, true));
                throw new Exception("Error fetching data: " . print_r($errors, true));
            }
            
            return $stmt;
        } catch (Exception $e) {
            error_log("Error in getData: " . $e->getMessage());
            throw $e;
        }
    }

    public function getDataById($id) {
        try {
            $sql = "SELECT pd.*, m.nama_mhs, d.nama_dosen 
                    FROM {$this->table} pd
                    JOIN mahasiswa m ON pd.nim = m.nim
                    JOIN dosen d ON pd.nip = d.nip
                    WHERE pd.id_pengajuan = ?";
            
            $params = array(intval($id));
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                throw new Exception("Error fetching data: " . print_r(sqlsrv_errors(), true));
            }
            
            return $stmt;
        } catch (Exception $e) {
            error_log("Error in getDataById: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateData($id, $data) {
        try {
            $sql = "UPDATE {$this->table} SET status_validasi = ?, alasan = ? WHERE id_pengajuan = ?";
            $params = array($data['status_validasi'], $data['alasan'], intval($id));
            
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                throw new Exception("Error updating data: " . print_r(sqlsrv_errors(), true));
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in updateData: " . $e->getMessage());
            throw $e;
        }
    }

    public function deleteData($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id_pengajuan = ?";
            $params = array(intval($id));
            
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                throw new Exception("Error deleting data: " . print_r(sqlsrv_errors(), true));
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in deleteData: " . $e->getMessage());
            throw $e;
        }
    }

    // Custom methods for this model
    public function save($data) {
        try {
            // Get NIM from session
            $nim = $this->getNimFromUsername($_SESSION['user']);
            if (!$nim) {
                throw new Exception('NIM tidak ditemukan');
            }

            // Set default values
            $tanggal = date('Y-m-d H:i:s');
            $status_validasi = 'Pending';

            $sql = "INSERT INTO {$this->table} (nim, nip, tanggal, status_validasi) VALUES (?, ?, ?, ?)";
            $params = array($nim, $data['nip'], $tanggal, $status_validasi);
            
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                throw new Exception("Error saving data: " . print_r(sqlsrv_errors(), true));
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in save: " . $e->getMessage());
            throw $e;
        }
    }

    public function getNimFromUsername($username) {
        try {
            error_log("Getting NIM for username: " . $username); // Debug log
            
            $sql = "SELECT m.nim FROM [mahasiswa] m 
                    JOIN [user] u ON m.id_user = u.id_user 
                    WHERE u.username = ?";
            $params = array($username);
            
            error_log("SQL Query: " . $sql); // Debug log
            error_log("Params: " . print_r($params, true)); // Debug log
            
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                $errors = sqlsrv_errors();
                error_log("SQL Error: " . print_r($errors, true)); // Debug log
                throw new Exception("Error getting NIM: " . print_r($errors, true));
            }
            
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            error_log("Query result: " . print_r($row, true)); // Debug log
            
            if (!$row) {
                error_log("No NIM found for username: " . $username); // Debug log
                throw new Exception("NIM tidak ditemukan untuk user: " . $username);
            }
            
            return $row['nim'];
        } catch (Exception $e) {
            error_log("Error in getNimFromUsername: " . $e->getMessage());
            throw $e;
        }
    }

    public function getNipFromUsername($username) {
        try {
            error_log("Getting NIP for username: " . $username); // Debug log
            
            $sql = "SELECT d.nip FROM [dosen] d 
                    JOIN [user] u ON d.id_user = u.id_user 
                    WHERE u.username = ?";
            $params = array($username);
            
            error_log("SQL Query: " . $sql); // Debug log
            error_log("Params: " . print_r($params, true)); // Debug log
            
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                $errors = sqlsrv_errors();
                error_log("SQL Error: " . print_r($errors, true)); // Debug log
                throw new Exception("Error getting NIP: " . print_r($errors, true));
            }
            
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            error_log("Query result: " . print_r($row, true)); // Debug log
            
            if (!$row) {
                error_log("No NIP found for username: " . $username); // Debug log
                throw new Exception("NIP tidak ditemukan untuk user: " . $username);
            }
            
            return $row['nip'];
        } catch (Exception $e) {
            error_log("Error in getNipFromUsername: " . $e->getMessage());
            throw $e;
        }
    }

    public function getDataByNim($nim) {
        try {
            $sql = "SELECT pd.*, m.nama_mhs, d.nama_dosen 
                    FROM {$this->table} pd
                    INNER JOIN [mahasiswa] m ON pd.nim = m.nim
                    INNER JOIN [dosen] d ON pd.nip = d.nip
                    WHERE pd.nim = ?
                    ORDER BY pd.tanggal DESC";
            
            error_log("SQL Query: " . $sql); // Debug log
            error_log("NIM: " . $nim); // Debug log
            
            $params = array($nim);
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                $errors = sqlsrv_errors();
                error_log("SQL Error: " . print_r($errors, true));
                throw new Exception("Error fetching data: " . print_r($errors, true));
            }
            
            return $stmt;
        } catch (Exception $e) {
            error_log("Error in getDataByNim: " . $e->getMessage());
            throw $e;
        }
    }

    public function getDataByNip($nip) {
        try {
            $sql = "SELECT pd.*, m.nama_mhs, d.nama_dosen, pd.tanggal, pd.status_validasi, pd.id_pengajuan 
                    FROM {$this->table} pd
                    INNER JOIN [mahasiswa] m ON pd.nim = m.nim
                    INNER JOIN [dosen] d ON pd.nip = d.nip
                    WHERE pd.nip = ?
                    ORDER BY pd.tanggal DESC";
            
            error_log("SQL Query: " . $sql); // Debug log
            error_log("NIP: " . $nip); // Debug log
            
            $params = array($nip);
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                $errors = sqlsrv_errors();
                error_log("SQL Error: " . print_r($errors, true));
                throw new Exception("Error fetching data: " . print_r($errors, true));
            }
            
            return $stmt;
        } catch (Exception $e) {
            error_log("Error in getDataByNip: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateStatus($id_pengajuan, $status, $alasan = null) {
        return $this->updateData($id_pengajuan, array(
            'status_validasi' => $status,
            'alasan' => $alasan
        ));
    }

    public function checkExistingPengajuan($nim) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                    WHERE nim = ? AND status_validasi = 'Pending'";
            
            $params = array(intval($nim));
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                throw new Exception("Error checking existing pengajuan: " . print_r(sqlsrv_errors(), true));
            }
            
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            return $row['count'] > 0;
        } catch (Exception $e) {
            error_log("Error in checkExistingPengajuan: " . $e->getMessage());
            throw $e;
        }
    }
}
?>

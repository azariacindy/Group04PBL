<?php
include_once(__DIR__ . '/Model.php');

class PrestasiModel extends Model
{
    protected $db;
    protected $driver;
    protected $table = '[prestasi]';

    public function __construct()
    {
        include(__DIR__ . '/../lib/Connection.php');
        if (!$db) {
            die('Koneksi database gagal: ' . print_r(sqlsrv_errors(), true));
        }
        $this->db = $db;
        $this->driver = $use_driver;
    }

    public function deleteData($id)
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id_prestasi = ?";
            $params = [$id];
            $stmt = sqlsrv_query($this->db, $sql, $params);
            if ($stmt === false) {
                throw new Exception('Gagal menghapus data: ' . print_r(sqlsrv_errors(), true));
            }
            return [
                'status' => true,
                'message' => 'Data berhasil dihapus'
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getNimByUsername($username) {
        $sql = "SELECT m.nim FROM [mahasiswa] m 
                JOIN [user] u ON m.id_user = u.id_user 
                WHERE u.username = ?";
        $params = [$username];
        $stmt = sqlsrv_query($this->db, $sql, $params);
        if ($stmt === false) {
            throw new Exception('Gagal mengambil data: ' . print_r(sqlsrv_errors(), true));
        }
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        return $row ? $row['nim'] : null;
    }

    public function getNimFromUsername($username) {
        if (empty($username)) {
            error_log("Username is empty");
            return null;
        }

        error_log("Getting NIM for username: " . $username);

        // First try database lookup
        $sql = "SELECT m.nim FROM [mahasiswa] m 
                JOIN [user] u ON m.id_user = u.id_user 
                WHERE u.username = ?";
        $params = [$username];
        
        error_log("Executing SQL: " . $sql);
        error_log("With params: " . print_r($params, true));
        
        $stmt = sqlsrv_query($this->db, $sql, $params);
        if ($stmt === false) {
            $errors = sqlsrv_errors();
            error_log("SQL Error: " . print_r($errors, true));
            throw new Exception("Database error: " . print_r($errors, true));
        }
        
        $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        error_log("Database result: " . print_r($row, true));
        if ($row && isset($row['nim'])) {
            return $row['nim'];
        }

        // If database lookup fails, try direct conversion
        if (strpos($username, 'mhs') === 0) {
            $num = substr($username, 3); // Get everything after 'mhs'
            if (is_numeric($num)) {
                $nim = '200' . $num; // Convert 1 to 2001, 2 to 2002, etc.
                error_log("Converted username {$username} to NIM: {$nim}");
                return $nim;
            }
        }

        error_log("Could not get NIM for username: " . $username);
        return null;
    }

    public function getConn() {
        return $this->db;
    }

    public function insertData($data)
    {
        try {
            // Get NIM from username if not provided
            if (empty($data['nim'])) {
                $username = isset($_SESSION['[user]']) ? $_SESSION['[user]'] : '';
                $data['nim'] = $this->getNimFromUsername($username);
                if (!$data['nim']) {
                    throw new Exception('NIM tidak ditemukan untuk user ini');
                }
            }

            $sql = "INSERT INTO {$this->table} (nim, nip, id_lomba, tanggal, detail_lomba, berkas, peringkat, status_lomba, status_validasi, alasan) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $data['nim'],
                $data['nip'],
                $data['id_lomba'],
                $data['tanggal'],
                $data['detail_lomba'],
                $data['berkas'],
                $data['peringkat'],
                $data['status_lomba'],
                $data['status_validasi'],
                $data['status_validasi'] == 0 ? $data['alasan'] : null
            ];
            $stmt = sqlsrv_query($this->db, $sql, $params);
            if ($stmt === false) {
                throw new Exception('Gagal menyimpan data: ' . print_r(sqlsrv_errors(), true));
            }
            return [
                'status' => true,
                'message' => 'Data prestasi berhasil disimpan'
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ];
        }
    }

    public function getData() {
        try {
            $query = "SELECT 
                        p.id_prestasi,
                        p.nim,
                        m.nama_mhs,
                        p.nip,
                        d.nama_dosen,
                        p.id_lomba,
                        l.nama_lomba,
                        t.nama_tingkat,
                        p.tanggal,
                        p.detail_lomba,
                        p.berkas,
                        p.peringkat,
                        p.status_lomba,
                        p.status_validasi,
                        p.alasan
                    FROM [prestasi] p 
                    LEFT JOIN [mahasiswa] m ON p.nim = m.nim 
                    LEFT JOIN [dosen] d ON p.nip = d.nip 
                    LEFT JOIN [lomba] l ON p.id_lomba = l.id_lomba 
                    LEFT JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat 
                    ORDER BY p.id_prestasi DESC";
            
            $stmt = sqlsrv_query($this->db, $query);
            if ($stmt === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }
            return $stmt;
        } catch (Exception $e) {
            throw new Exception('Gagal mengambil data: ' . $e->getMessage());
        }
    }

    public function getDataById($id) {
        try {
            $query = "SELECT 
                        p.id_prestasi,
                        p.nim,
                        m.nama_mhs,
                        p.nip,
                        d.nama_dosen,
                        p.id_lomba,
                        l.nama_lomba,
                        t.nama_tingkat,
                        p.tanggal,
                        p.detail_lomba,
                        p.berkas,
                        p.peringkat,
                        p.status_lomba,
                        p.status_validasi,
                        p.alasan
                    FROM [prestasi] p 
                    LEFT JOIN [mahasiswa] m ON p.nim = m.nim 
                    LEFT JOIN [dosen] d ON p.nip = d.nip 
                    LEFT JOIN [lomba] l ON p.id_lomba = l.id_lomba 
                    LEFT JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat 
                    WHERE p.id_prestasi = ?";
                    
            $stmt = sqlsrv_query($this->db, $query, [$id]);
            if ($stmt === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }
            return $stmt;
        } catch (Exception $e) {
            throw new Exception('Gagal mengambil data: ' . $e->getMessage());
        }
    }

    public function getDataByNimUser($nim)
    {
        try {
            // Convert string NIM to match database format
            $nim = trim($nim);
            
            $sql = "SELECT p.*, 
                    m.nama_mhs as nama_mhs, 
                    d.nama_dosen as nama_dosen, 
                    l.nama_lomba as nama_lomba, 
                    t.nama_tingkat as nama_tingkat,
                    p.alasan
                    FROM [prestasi] p
                    LEFT JOIN [mahasiswa] m ON p.nim = m.nim
                    LEFT JOIN [dosen] d ON p.nip = d.nip
                    LEFT JOIN [lomba] l ON p.id_lomba = l.id_lomba
                    LEFT JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat 
                    WHERE p.nim = ?
                    ORDER BY p.id_prestasi DESC";

            $params = [$nim];
            $stmt = sqlsrv_query($this->db, $sql, $params);
            if ($stmt === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }
            return $stmt;
        } catch (Exception $e) {
            throw new Exception('Gagal mengambil data: ' . $e->getMessage());
        }
    }

    public function updateData($id, $data) {
        try {
            $query = "UPDATE [prestasi] SET ";
            $params = [];
            
            foreach ($data as $key => $value) {
                if ($key !== 'id_prestasi') {
                    if ($key === 'alasan' && $data['status_validasi'] == 1) {
                        continue; // Skip alasan if status is valid
                    }
                    $query .= "$key = ?, ";
                    $params[] = $value;
                }
            }
            $query = rtrim($query, ", ");
            $query .= " WHERE id_prestasi = ?";
            $params[] = $id;

            $stmt = sqlsrv_query($this->db, $query, $params);
            if ($stmt === false) {
                throw new Exception('Gagal mengupdate data: ' . print_r(sqlsrv_errors(), true));
            }
            return [
                'status' => true,
                'message' => 'Data prestasi berhasil diupdate'
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => 'Gagal mengupdate data: ' . $e->getMessage()
            ];
        }
    }

    public function getAllDosen()
    {
        try {
            $sql = "SELECT nip, nama_dosen FROM [dosen]";
            $stmt = sqlsrv_query($this->db, $sql);
            if ($stmt === false) {
                throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
            }
            return $stmt;
        } catch (Exception $e) {
            throw new Exception("Gagal mengambil data: " . $e->getMessage());
        }
    }

    public function getAllLomba()
    {
        try {
            $sql = "SELECT l.id_lomba, l.nama_lomba, t.nama_tingkat 
                    FROM [lomba] l 
                    JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat";
            $stmt = sqlsrv_query($this->db, $sql);
            if ($stmt === false) {
                throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
            }
            return $stmt;
        } catch (Exception $e) {
            throw new Exception("Gagal mengambil data: " . $e->getMessage());
        }
    }

    public function getAllMahasiswa() {
        try {
            $query = "SELECT nim, nama_mhs FROM [mahasiswa] ORDER BY nama_mhs";
            $stmt = sqlsrv_query($this->db, $query);
            if ($stmt === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }
            return $stmt;
        } catch (Exception $e) {
            throw new Exception('Gagal mengambil data mahasiswa: ' . $e->getMessage());
        }
    }

    public function getAllData() {
        $sql = "SELECT p.*, 
                m.nama_mhs as nama_mhs, 
                d.nama_dosen as nama_dosen, 
                l.nama_lomba as nama_lomba, 
                t.nama_tingkat as nama_tingkat,
                p.alasan
                FROM [prestasi] p
                LEFT JOIN [mahasiswa] m ON p.nim = m.nim
                LEFT JOIN [dosen] d ON p.nip = d.nip
                LEFT JOIN [lomba] l ON p.id_lomba = l.id_lomba
                LEFT JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat
                ORDER BY p.id_prestasi DESC";
        
        try {
            $stmt = sqlsrv_query($this->db, $sql);
            if ($stmt === false) {
                throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
            }
            return $stmt;
        } catch (Exception $e) {
            error_log("Error in getAllData: " . $e->getMessage());
            throw $e;
        }
    }

    public function insertDataNew($data)
    {
        try {
            $sql = "INSERT INTO {$this->table} (nim, id_dosen, id_lomba, nama_lomba, juara, tingkat, tahun, status_validasi, alasan) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $data['nim'],
                $data['id_dosen'],
                $data['id_lomba'],
                $data['nama_lomba'],
                $data['juara'],
                $data['tingkat'],
                $data['tahun'],
                $data['status_validasi'],
                $data['status_validasi'] == 0 ? $data['alasan'] : null
            ];
            $stmt = sqlsrv_query($this->db, $sql, $params);
            if ($stmt === false) {
                throw new Exception('Gagal menyimpan data: ' . print_r(sqlsrv_errors(), true));
            }
            return [
                'status' => true,
                'message' => 'Data berhasil disimpan'
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getDataNew()
    {
        try {
            $query = "SELECT p.*, m.nama_mhs, d.nama_dosen, l.nama_lomba as kategori_lomba, t.nama_tingkat, p.alasan
                    FROM {$this->table} p
                    LEFT JOIN [mahasiswa] m ON p.nim = m.nim
                    LEFT JOIN [dosen] d ON p.id_dosen = d.nip
                    LEFT JOIN [lomba] l ON p.id_lomba = l.id_lomba
                    LEFT JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat 
                    ORDER BY p.id_prestasi DESC";
            
            $stmt = sqlsrv_query($this->db, $query);
            if ($stmt === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }

            $data = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $data[] = $row;
            }
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getDataByIdNew($id)
    {
        try {
            $query = "SELECT p.*, m.nama_mhs, d.nama_dosen, l.nama_lomba as kategori_lomba, t.nama_tingkat, p.alasan
                    FROM {$this->table} p
                    LEFT JOIN [mahasiswa] m ON p.nim = m.nim
                    LEFT JOIN [dosen] d ON p.id_dosen = d.nip
                    LEFT JOIN [lomba] l ON p.id_lomba = l.id_lomba
                    LEFT JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat 
                    WHERE p.id_prestasi = ?";
                    
            $stmt = sqlsrv_query($this->db, $query, [$id]);
            if ($stmt === false) {
                throw new Exception(print_r(sqlsrv_errors(), true));
            }
            return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    public function updateDataNew($id, $data)
    {
        try {
            $query = "UPDATE {$this->table} SET ";
            $params = [];
            
            foreach ($data as $key => $value) {
                if ($key !== 'id_prestasi') {
                    if ($key === 'alasan' && $data['status_validasi'] == 1) {
                        continue; // Skip alasan if status is valid
                    }
                    $query .= "{$key} = ?, ";
                    $params[] = $value;
                }
            }
            $query = rtrim($query, ", ");
            $query .= " WHERE id_prestasi = ?";
            $params[] = $id;

            $stmt = sqlsrv_query($this->db, $query, $params);
            if ($stmt === false) {
                throw new Exception('Gagal mengupdate data: ' . print_r(sqlsrv_errors(), true));
            }
            return [
                'status' => true,
                'message' => 'Data berhasil diupdate'
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getAllDosenNew()
    {
        try {
            $sql = "SELECT nip, nama_dosen FROM [dosen]";
            $stmt = sqlsrv_query($this->db, $sql);
            if ($stmt === false) {
                throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
            }
            $data = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $data[] = $row;
            }
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }

    public function getAllLombaNew()
    {
        try {
            $sql = "SELECT l.id_lomba, l.nama_lomba, t.nama_tingkat 
                    FROM [lomba] l 
                    JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat";
            $stmt = sqlsrv_query($this->db, $sql);
            if ($stmt === false) {
                throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
            }
            $data = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $data[] = $row;
            }
            return $data;
        } catch (Exception $e) {
            return [];
        }
    }
}
?>

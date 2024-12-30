<?php
include_once(__DIR__ . '/Model.php');

class PrestasiModel extends Model
{
    protected $db;
    protected $driver;
    protected $table = '[prestasi]';

    public function __construct()
    {
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

    public function getConn() {
        return $this->db;
    }

    public function getConnection() {
        return $this->db;
    }

    public function getTable() {
        return $this->table;
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
    
    public function countValidatedPrestasi() {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE status_validasi > 0";
            $stmt = sqlsrv_query($this->db, $sql);
            
            if ($stmt === false) {
                throw new Exception('Error executing query: ' . print_r(sqlsrv_errors(), true));
            }
            
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            return $row['total'];
            
        } catch (Exception $e) {
            return 0;
        }
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

    public function getNimByUserId($user_id) {
        try {
            $sql = "SELECT nim FROM mahasiswa WHERE id_user = ?";
            $params = array($user_id);
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                throw new Exception("Error getting user details: " . print_r(sqlsrv_errors(), true));
            }
            
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if (!$row) {
                throw new Exception("Student data not found. Please contact administrator.");
            }
            
            return $row['nim'];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function insertData($data)
    {
        try {
            // Get NIM from username if not provided
            if (empty($data['nim'])) {
                $username = isset($_SESSION['user']) ? $_SESSION['user'] : '';
                $data['nim'] = $this->getNimFromUsername($username);
                if (!$data['nim']) {
                    throw new Exception('NIM tidak ditemukan untuk user ini');
                }
            }

            // Set default values if not provided
            $data['status_validasi'] = $data['status_validasi'] ?? '-1'; // Default: belum divalidasi
            $data['alasan'] = $data['alasan'] ?? null;

            // Validasi status_validasi
            if (!in_array($data['status_validasi'], ['-1', '0', '1', '2', '3'])) {
                throw new Exception('Status validasi tidak valid. Harus salah satu dari: -1, 0, 1, 2, 3');
            }

            $sql = "INSERT INTO {$this->table} (nim, nip, id_lomba, tanggal, detail_lomba, berkas, peringkat, status_validasi, id_user) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                intval($data['nim']),
                intval($data['nip']),
                intval($data['id_lomba']),
                $data['tanggal'],
                $data['detail_lomba'],
                $data['berkas'],
                $data['peringkat'],
                $data['status_validasi'],
                $data['id_user']
            ];

            $stmt = sqlsrv_query($this->db, $sql, $params);
            if ($stmt === false) {
                throw new Exception('Gagal menyimpan data prestasi: ' . print_r(sqlsrv_errors(), true));
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
                        p.*,
                        m.nama_mhs,
                        d.nama_dosen,
                        l.nama_lomba,
                        l.detail_lomba as detail_kategori_lomba,
                        t.nama_tingkat,
                        t.id_tingkat
                    FROM [prestasi] p 
                    INNER JOIN [mahasiswa] m ON p.nim = m.nim 
                    INNER JOIN [dosen] d ON p.nip = d.nip 
                    INNER JOIN [lomba] l ON p.id_lomba = l.id_lomba 
                    INNER JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat 
                    ORDER BY p.id_prestasi DESC";
            
            $stmt = sqlsrv_query($this->db, $query);
            if ($stmt === false) {
                throw new Exception('Gagal mengambil data: ' . print_r(sqlsrv_errors(), true));
            }
            return $stmt;
        } catch (Exception $e) {
            throw new Exception('Gagal mengambil data: ' . $e->getMessage());
        }
    }

    public function getDataById($id) {
        try {
            $query = "SELECT 
                        p.*,
                        m.nama_mhs,
                        d.nama_dosen,
                        l.nama_lomba,
                        l.detail_lomba as detail_kategori_lomba,
                        t.nama_tingkat,
                        t.id_tingkat
                    FROM [prestasi] p 
                    INNER JOIN [mahasiswa] m ON p.nim = m.nim 
                    INNER JOIN [dosen] d ON p.nip = d.nip 
                    INNER JOIN [lomba] l ON p.id_lomba = l.id_lomba 
                    INNER JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat 
                    WHERE p.id_prestasi = ?";
                    
            $stmt = sqlsrv_query($this->db, $query, [$id]);
            if ($stmt === false) {
                throw new Exception('Gagal mengambil data: ' . print_r(sqlsrv_errors(), true));
            }
            return $stmt;
        } catch (Exception $e) {
            throw new Exception('Gagal mengambil data: ' . $e->getMessage());
        }
    }

    public function getDataByNimUser($nim)
    {
        try {
            $nim = trim($nim);
            
            $sql = "SELECT 
                    p.*,
                    m.nama_mhs,
                    d.nama_dosen,
                    l.nama_lomba,
                    l.detail_lomba as detail_kategori_lomba,
                    t.nama_tingkat,
                    t.id_tingkat
                    FROM [prestasi] p
                    INNER JOIN [mahasiswa] m ON p.nim = m.nim
                    INNER JOIN [dosen] d ON p.nip = d.nip
                    INNER JOIN [lomba] l ON p.id_lomba = l.id_lomba
                    INNER JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat
                    WHERE p.nim = ?
                    ORDER BY p.id_prestasi DESC";

            $params = [$nim];
            $stmt = sqlsrv_query($this->db, $sql, $params);
            if ($stmt === false) {
                throw new Exception('Gagal mengambil data: ' . print_r(sqlsrv_errors(), true));
            }
            return $stmt;
        } catch (Exception $e) {
            throw new Exception('Gagal mengambil data: ' . $e->getMessage());
        }
    }

    public function updateData($id, $data)
    {
        try {
            // First check if the lomba exists
            if (isset($data['id_lomba'])) {
                $check_sql = "SELECT id_lomba FROM lomba WHERE id_lomba = ?";
                $check_stmt = sqlsrv_query($this->db, $check_sql, array($data['id_lomba']));
                
                if ($check_stmt === false) {
                    throw new Exception("Error checking lomba: " . print_r(sqlsrv_errors(), true));
                }
                
                if (!sqlsrv_fetch_array($check_stmt)) {
                    throw new Exception("Lomba with ID " . $data['id_lomba'] . " does not exist.");
                }
            }

            // Handle custom lomba if needed
            if (isset($data['is_custom_lomba']) && $data['is_custom_lomba'] == '1') {
                $data['id_lomba'] = $this->createLomba($data['custom_nama_lomba'], $data['custom_tingkat']);
            }

            // Prepare update data
            $updateData = [];
            
            // Jika ini adalah validasi SKKM (hanya status_validasi dan alasan)
            if (isset($data['status_validasi'])) {
                // Validasi status_validasi sesuai constraint CK_status_validasi
                $status_validasi = strval($data['status_validasi']);
                if (!in_array($status_validasi, ['-1', '0', '1', '2', '3'])) {
                    throw new Exception('Status validasi tidak valid. Harus salah satu dari: -1, 0, 1, 2, 3');
                }
                $updateData['status_validasi'] = $status_validasi;
                
                if ($status_validasi === '0') {
                    if (empty($data['alasan'])) {
                        throw new Exception('Alasan penolakan harus diisi');
                    }
                    $updateData['alasan'] = $data['alasan'];
                    $updateData['peringkat'] = null; // Reset peringkat jika ditolak
                } else {
                    $updateData['alasan'] = null; // Reset alasan jika diterima
                    if (isset($data['peringkat'])) {
                        $updateData['peringkat'] = strval($data['peringkat']);
                    }
                }
            } 
            // Jika ini adalah update data prestasi biasa
            else {
                $allowedFields = ['nip', 'id_lomba', 'tanggal', 'detail_lomba', 'berkas', 'peringkat'];
                foreach ($allowedFields as $field) {
                    if (isset($data[$field])) {
                        // Convert numeric fields to int
                        if (in_array($field, ['nip', 'id_lomba'])) {
                            $updateData[$field] = intval($data[$field]);
                        }
                        // Convert string fields
                        else if ($field === 'peringkat') {
                            $updateData[$field] = strval($data[$field]);
                        }
                        // Other fields remain as is
                        else {
                            $updateData[$field] = $data[$field];
                        }
                    }
                }
            }

            // If no fields to update, return success
            if (empty($updateData)) {
                return [
                    'status' => true,
                    'message' => 'Tidak ada data yang diupdate'
                ];
            }

            // Build UPDATE query
            $sql = "UPDATE [prestasi] SET ";
            $params = [];
            foreach ($updateData as $key => $value) {
                $sql .= "[$key] = ?, ";
                $params[] = $value;
            }
            $sql = rtrim($sql, ", ") . " WHERE id_prestasi = ?";
            $params[] = intval($id); // Convert to int to match column type

            // Execute query
            $stmt = sqlsrv_query($this->db, $sql, $params);
            if ($stmt === false) {
                throw new Exception("Gagal update prestasi: " . print_r(sqlsrv_errors(), true));
            }

            return [
                'status' => true,
                'message' => isset($data['status_validasi']) ? 
                    'Status validasi berhasil diupdate' : 
                    'Data prestasi berhasil diupdate'
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function getAllDosen()
    {
        try {
            $sql = "SELECT nip, nama_dosen FROM [dosen] ORDER BY nama_dosen ASC";
            $stmt = sqlsrv_query($this->db, $sql);
            if ($stmt === false) {
                throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
            }
            
            $result = array();
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $result[] = $row;
            }
            return $result;
        } catch (Exception $e) {
            error_log("Error in getAllDosen: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAllLomba()
    {
        try {
            $sql = "SELECT l.id_lomba, l.nama_lomba, t.nama_tingkat 
                    FROM [lomba] l
                    LEFT JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat
                    ORDER BY l.nama_lomba ASC";
            
            $stmt = sqlsrv_query($this->db, $sql);
            if ($stmt === false) {
                throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
            }
            
            $result = array();
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $result[] = $row;
            }
            return $result;
        } catch (Exception $e) {
            error_log("Error in getAllLomba: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAllMahasiswa()
    {
        try {
            $sql = "SELECT nim, nama_mhs FROM [mahasiswa] ORDER BY nama_mhs ASC";
            $stmt = sqlsrv_query($this->db, $sql);
            if ($stmt === false) {
                throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
            }
            
            $result = array();
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $result[] = $row;
            }
            return $result;
        } catch (Exception $e) {
            error_log("Error in getAllMahasiswa: " . $e->getMessage());
            throw $e;
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
            $sql = "INSERT INTO {$this->table} (nim, nip, id_lomba, nama_lomba, juara, tingkat, tahun, status_validasi, alasan) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $data['nim'],
                $data['nip'],
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
                    LEFT JOIN [dosen] d ON p.nip = d.nip
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
                    LEFT JOIN [dosen] d ON p.nip = d.nip
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

    // Menggunakan query langsung untuk laporan prestasi
    public function getLaporanPrestasi($tanggal_mulai, $tanggal_akhir)
    {
        try {
            $sql = "SELECT p.*, m.nama_mhs, d.nama_dosen, l.nama_lomba, t.nama_tingkat
                    FROM [prestasi] p
                    LEFT JOIN [mahasiswa] m ON p.nim = m.nim
                    LEFT JOIN [dosen] d ON p.nip = d.nip
                    LEFT JOIN [lomba] l ON p.id_lomba = l.id_lomba
                    LEFT JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat
                    WHERE p.tanggal BETWEEN ? AND ?
                    ORDER BY p.tanggal DESC";
            
            $params = [$tanggal_mulai, $tanggal_akhir];
            $stmt = sqlsrv_query($this->db, $sql, $params);
            if ($stmt === false) {
                throw new Exception('Gagal mengambil laporan: ' . print_r(sqlsrv_errors(), true));
            }
            
            $result = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $result[] = $row;
            }
            return [
                'status' => true,
                'data' => $result
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Menggunakan query langsung untuk mendapatkan lomba aktif
    public function getLombaAktif()
    {
        try {
            $sql = "SELECT l.*, t.nama_tingkat
                    FROM [lomba] l
                    JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat
                    WHERE l.status = 'aktif'
                    ORDER BY l.nama_lomba";
            
            $stmt = sqlsrv_query($this->db, $sql);
            if ($stmt === false) {
                throw new Exception('Gagal mengambil data lomba aktif: ' . print_r(sqlsrv_errors(), true));
            }
            
            $result = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $result[] = $row;
            }
            return [
                'status' => true,
                'data' => $result
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Menggunakan query langsung untuk validasi prestasi
    public function validasiPrestasi($id_prestasi, $status_validasi, $peringkat = null)
    {
        try {
            // Siapkan data untuk update
            $data = [
                'status_validasi' => strval($status_validasi)
            ];

            // Jika status ditolak (0), tambahkan alasan
            if ($status_validasi === '0') {
                $data['alasan'] = $_POST['alasan'] ?? null;
                $data['peringkat'] = null; // Reset peringkat jika ditolak
            } else {
                $data['alasan'] = null; // Reset alasan jika diterima
                $data['peringkat'] = $peringkat ? strval($peringkat) : null;
            }

            // Update data menggunakan method updateData yang sudah ada
            return $this->updateData($id_prestasi, $data);
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function createLomba($nama_lomba, $tingkat)
    {
        try {
            // Get user ID from session
            if (!isset($_SESSION['id_user'])) {
                throw new Exception("User ID not found in session");
            }
            $id_user = $_SESSION['id_user'];

            // First check if lomba already exists
            $sql = "SELECT id_lomba FROM [lomba] WHERE nama_lomba = ? AND id_tingkat = ?";
            $stmt = sqlsrv_query($this->db, $sql, [$nama_lomba, $tingkat]);
            
            if ($stmt === false) {
                throw new Exception("Error checking existing lomba: " . print_r(sqlsrv_errors(), true));
            }
            
            if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                return $row['id_lomba']; // Return existing lomba ID
            }
            
            // Get current date for tanggal field
            $currentDate = date('Y-m-d');
            
            // If not exists, create new lomba
            $sql = "INSERT INTO [lomba] (nama_lomba, id_tingkat, id_user, tanggal, detail_lomba, status_input_lomba) 
                    VALUES (?, ?, ?, ?, ?, 'in progress')";
            
            $params = [
                $nama_lomba,
                $tingkat,
                $id_user,
                $currentDate,
                isset($_POST['detail_lomba']) ? $_POST['detail_lomba'] : null
            ];
            
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                throw new Exception("Error creating new lomba: " . print_r(sqlsrv_errors(), true));
            }
            
            // Get the newly created lomba ID
            $sql = "SELECT id_lomba FROM [lomba] WHERE nama_lomba = ? AND id_tingkat = ?";
            $stmt = sqlsrv_query($this->db, $sql, [$nama_lomba, $tingkat]);
            
            if ($stmt === false) {
                throw new Exception("Error getting new lomba ID: " . print_r(sqlsrv_errors(), true));
            }
            
            if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                return $row['id_lomba'];
            }
            
            throw new Exception("Failed to get new lomba ID");
        } catch (Exception $e) {
            error_log("Error in createLomba: " . $e->getMessage());
            throw $e;
        }
    }

    public function save($data)
    {
        try {
            // Validate required fields
            $required_fields = ['nim', 'nip', 'id_lomba', 'tanggal', 'peringkat', 'berkas'];
            foreach ($required_fields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Field $field is required");
                }
            }

            // Get user ID from session
            if (!isset($_SESSION['id_user'])) {
                throw new Exception("User ID not found in session");
            }
            $id_user = $_SESSION['id_user'];

            // Insert prestasi data
            $sql = "INSERT INTO {$this->table} (nim, nip, id_lomba, tanggal, detail_lomba, berkas, peringkat, status_validasi) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $data['nim'],
                $data['nip'],
                $data['id_lomba'],
                $data['tanggal'],
                isset($data['detail_lomba']) ? $data['detail_lomba'] : null,
                $data['berkas'],
                $data['peringkat'],
                isset($data['status_validasi']) ? $data['status_validasi'] : '0'
            ];
            
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                throw new Exception("Error saving prestasi: " . print_r(sqlsrv_errors(), true));
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error in save: " . $e->getMessage());
            throw $e;
        }
    }

    public function getTingkatLomba($id_lomba) {
        try {
            $sql = "SELECT l.id_tingkat, t.nama_tingkat 
                    FROM lomba l 
                    JOIN tingkat t ON l.id_tingkat = t.id_tingkat 
                    WHERE l.id_lomba = ?";
            $stmt = sqlsrv_query($this->db, $sql, array($id_lomba));
            
            if ($stmt === false) {
                throw new Exception("Error getting tingkat lomba: " . print_r(sqlsrv_errors(), true));
            }
            
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if (!$row) {
                throw new Exception('Lomba tidak ditemukan');
            }

            return $row;
        } catch (Exception $e) {
            error_log("Error in getTingkatLomba: " . $e->getMessage());
            throw $e;
        }
    }
    public function countPrestasiByUser($id_user) {
        try {
            // Get NIM first
            $nim = $this->getNimByUserId($id_user);
            
            // Count prestasi for this NIM
            $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE nim = ?";
            $params = array($nim);
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
            }
            
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            return $row['total'];
        } catch (Exception $e) {
            error_log($e->getMessage());
            return 0;
        }
    }

    public function getNipFromUsername($username) {
        try {
            $sql = "SELECT d.nip FROM [dosen] d 
                    JOIN [user] u ON d.id_user = u.id_user 
                    WHERE u.username = ?";
            $params = array($username);
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                throw new Exception("Error getting NIP: " . print_r(sqlsrv_errors(), true));
            }
            
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if (!$row) {
                throw new Exception("NIP tidak ditemukan untuk user: " . $username);
            }
            
            return $row['nip'];
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    public function getDataByNipUser($nip) {
        try {
            $nip = trim($nip);
            $sql = "SELECT 
                    p.*,
                    m.nama_mhs,
                    d.nama_dosen,
                    l.nama_lomba,
                    l.detail_lomba as detail_kategori_lomba,
                    t.nama_tingkat,
                    t.id_tingkat
                    FROM [prestasi] p
                    INNER JOIN [mahasiswa] m ON p.nim = m.nim
                    INNER JOIN [dosen] d ON p.nip = d.nip
                    INNER JOIN [lomba] l ON p.id_lomba = l.id_lomba
                    INNER JOIN [tingkat] t ON l.id_tingkat = t.id_tingkat
                    WHERE p.nip = ?
                    ORDER BY p.id_prestasi DESC";
    
            $params = [$nip];
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                throw new Exception('Gagal mengambil data: ' . print_r(sqlsrv_errors(), true));
            }
            
            return $stmt;
        } catch (Exception $e) {
            throw new Exception('Gagal mengambil data: ' . $e->getMessage());
        }
    }
    
    public function getMonthlyStats($year) {
        try {
            $sql = "SELECT 
                        MONTH(tanggal) as month,
                        COUNT(*) as count
                    FROM prestasi
                    WHERE YEAR(tanggal) = ?
                    GROUP BY MONTH(tanggal)
                    ORDER BY MONTH(tanggal)";
            
            $params = array($year);
            $stmt = sqlsrv_query($this->db, $sql, $params);
            
            if ($stmt === false) {
                throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
            }
            
            $results = array();
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $results[] = $row;
            }
            
            return $results;
        } catch (Exception $e) {
            throw new Exception("Failed to get monthly statistics: " . $e->getMessage());
        }
    }

    // ... rest of the code remains the same ...
}
?>

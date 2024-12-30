<?php
include_once(__DIR__ . '/Model.php');
include_once(__DIR__ . '/UserModel.php');

class daftarlombaModel extends Model
{
    protected $db;
    protected $table = 'lomba';
    protected $driver;
    protected $role;

    public function __construct()
    {
        require(__DIR__ . '/../lib/Connection.php');
        if (!isset($db) || $db === false) {
            throw new Exception('Database connection failed');
        }
        $this->db = $db;
        $this->driver = $use_driver;
        $this->role = isset($_SESSION['role']) ? $_SESSION['role'] : '[user]';
    }

    public function insertData($data)
    {
        // Tentukan id_user berdasarkan role
        $id_user = $data['id_user'];  // Set id_user menjadi 1 jika admin


        if ($this->driver == 'mysql') {
            // Tentukan status berdasarkan role
            $status_input_lomba = ($this->role == 'admin') ? 'approved' : 'in progress';


            // Insert data lomba dengan id_user
            $query = $this->db->prepare("INSERT INTO {$this->table} (id_user, nama_lomba, id_tingkat, tanggal, detail_lomba, status_input_lomba, gambar) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?)");
            $query->bind_param('issssss', $id_user, $data['nama_lomba'], $data['id_tingkat'], $data['tanggal'], $data['detail_lomba'], $status_input_lomba, $data['gambar']);
            $query->execute();
        } else {
            // Tentukan status berdasarkan role
            $status_input_lomba = ($this->role == 'admin') ? 'approved' : 'in progress';


            // Insert data lomba dengan id_user
            $sql = "INSERT INTO {$this->table} (id_user, nama_lomba, id_tingkat, tanggal, detail_lomba, status_input_lomba, gambar) 
               VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $id_user,
                $data['nama_lomba'],
                $data['id_tingkat'],
                $data['tanggal'],
                $data['detail_lomba'],
                $status_input_lomba,
                $data['gambar']
            ];
            sqlsrv_query($this->db, $sql, $params);
        }
    }


    public function getData()
    {
        if ($this->driver == 'mysql') {
            return $this->db->query("SELECT * FROM {$this->table}");
        } else {
            $sql = "SELECT * FROM {$this->table}";
            return sqlsrv_query($this->db, $sql);
        }
    }


    public function getDataById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id_lomba = ?";
        $params = [$id];
        $stmt = sqlsrv_query($this->db, $sql, $params);
        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }
    public function getDataByIdUser($id_user)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id_user = ?";
        $params = [$id_user];
        $stmt = sqlsrv_query($this->db, $sql, $params);


        if ($stmt === false) {
            throw new Exception('Error executing query: ' . print_r(sqlsrv_errors(), true));
        }


        return $stmt;
    }
    public function updateStatus($id_lomba, $status_input_lomba)
    {
        $query = "UPDATE lomba SET status_input_lomba = ? WHERE id_lomba = ?";
        $params = [$status_input_lomba, $id_lomba];


        // Eksekusi query
        $stmt = sqlsrv_query($this->db, $query, $params);


        if ($stmt === false) {
            throw new Exception('Gagal memperbarui status lomba.');
        }


        return true;
    }


    public function updateData($id, $data)
    {
            $sql = "UPDATE {$this->table} 
                    SET nama_lomba = ?, id_tingkat = ? , tanggal = ?, detail_lomba = ?, gambar = ?
                    WHERE id_lomba = ?";
            $params = [
                $data['nama_lomba'],
                $data['id_tingkat'],
                $data['tanggal'],
                $data['detail_lomba'],
                $data['gambar'],
                $id
            ];
            sqlsrv_query($this->db, $sql, $params);
    }


    public function deleteData($id)
    {
        if ($this->driver == 'mysql') {
            $query = $this->db->prepare("DELETE FROM {$this->table} WHERE id_lomba = ?");
            $query->bind_param('i', $id);
            $query->execute();
        } else {
            $sql = "DELETE FROM {$this->table} WHERE id_lomba = ?";
            $params = [$id];
            sqlsrv_query($this->db, $sql, $params);
        }
    }

    public function getApprovedLomba()
    {
        if ($this->driver == 'mysql') {
            $sql = "SELECT * FROM {$this->table} WHERE status_input_lomba = 'approved' ORDER BY tanggal DESC";
            return $this->db->query($sql);
        } else {
            $sql = "SELECT * FROM {$this->table} WHERE status_input_lomba = 'approved' ORDER BY tanggal DESC";
            return sqlsrv_query($this->db, $sql);
        }
    }
}

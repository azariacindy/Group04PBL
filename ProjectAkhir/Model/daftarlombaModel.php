<?php
include('../Model/UserModel.php');

class daftarlombaModel extends Model
{
    protected $db;
    protected $table = 'lomba';
    protected $driver;
    protected $role;

    public function __construct()
    {
        include_once('../lib/Connection.php');
        $this->db = $db;
        $this->driver = $use_driver; // pastikan $use_driver diatur di Connection.php (mysql/sqlsrv)
        $this->role = isset($_SESSION['role']) ? $_SESSION['role'] : '[user]'; // Mengambil role dari session
    }

    public function insertData($data)
    {
        // Tentukan id_user berdasarkan role
        $id_user = $data['id_user']; // Set id_user menjadi 1 jika admin

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

    public function getDataByUser($id_user)
    {
        // Hanya mengambil lomba yang dimiliki oleh user dengan id_user tertentu
        if ($this->driver == 'mysql') {
            $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_user = ?");
            $query->bind_param('i', $id_user);
            $query->execute();
            return $query->get_result();
        } else {
            $sql = "SELECT * FROM {$this->table} WHERE id_user = ?";
            $params = [$id_user];
            return sqlsrv_query($this->db, $sql, $params);
        }
    }

    public function getDataById($id)
    {
        if ($this->driver == 'mysql') {
            $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_lomba = ?");
            $query->bind_param('i', $id);
            $query->execute();
            return $query->get_result()->fetch_assoc();
        } else {
            $sql = "SELECT * FROM {$this->table} WHERE id_lomba = ?";
            $params = [$id];
            $stmt = sqlsrv_query($this->db, $sql, $params);
            return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        }
    }

    public function getDataByIdAndUser($id, $id_user)
    {
        // Mengambil lomba berdasarkan id dan id_user untuk validasi akses
        if ($this->driver == 'mysql') {
            $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_lomba = ? AND id_user = ?");
            $query->bind_param('ii', $id, $id_user);
            $query->execute();
            return $query->get_result()->fetch_assoc();
        } else {
            $sql = "SELECT * FROM {$this->table} WHERE id_lomba = ? AND id_user = ?";
            $params = [$id, $id_user];
            $stmt = sqlsrv_query($this->db, $sql, $params);
            return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        }
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
        if ($this->driver == 'mysql') {
            $query = $this->db->prepare("UPDATE {$this->table} 
                                         SET nama_lomba = ?, id_tingkat = ? , tanggal = ?, detail_lomba = ?, gambar = ?
                                         WHERE id_lomba = ? AND id_user = ?");
            $query->bind_param('sisssii', $data['nama_lomba'], $data['id_tingkat'], $data['tanggal'], $data['detail_lomba'], $data['gambar'], $id, $data['id_user']);
            $query->execute();
        } else {
            $sql = "UPDATE {$this->table} 
                    SET nama_lomba = ?, id_tingkat = ? , tanggal = ?, detail_lomba = ?, gambar = ?
                    WHERE id_lomba = ? AND id_user = ?";
            $params = [
                $data['nama_lomba'],
                $data['id_tingkat'],
                $data['tanggal'],
                $data['detail_lomba'],
                $data['gambar'],
                $id,
                $data['id_user']
            ];
            sqlsrv_query($this->db, $sql, $params);
        }
    }

    public function deleteData($id)
    {
        if ($this->driver == 'mysql') {
            $query = $this->db->prepare("DELETE FROM {$this->table} WHERE id_lomba = ? AND id_user = ?");
            $query->bind_param('ii', $id);
            $query->execute();
        } else {
            $sql = "DELETE FROM {$this->table} WHERE id_lomba = ? AND id_user = ?";
            $params = [$id];
            sqlsrv_query($this->db, $sql, $params);
        }
    }
}
?>

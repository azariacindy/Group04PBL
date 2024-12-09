<?php
include('../../Model.php');
class daftarlombaModel extends Model
{
    protected $db;
    protected $table = 'lomba';
    protected $driver;
    protected $role;

    public function __construct()
    {
        include_once('../../lib/Connection.php');
        $this->db = $db;
        $this->driver = $use_driver; // pastikan $use_driver diatur di Connection.php (mysql/sqlsrv)
        $this->role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user'; // Mengambil role dari session
    }

    public function insertData($data)
    {
        if ($this->driver == 'mysql') {
            // Tentukan status berdasarkan role
            $status_input_lomba = ($this->role == 'admin') ? 'approved' : 'pending';

            // Insert data lomba
            $query = $this->db->prepare("INSERT INTO {$this->table} (nama_lomba, id_tingkat, tanggal, detail_lomba, status_input_lomba, gambar) 
                                         VALUES (?, ?, ?, ?, ?, ?)");
            $query->bind_param('sissss', $data['nama_lomba'], $data['id_tingkat'], $data['tanggal'], $data['detail_lomba'], $status_input_lomba, $data['gambar']);
            $query->execute();
        } else {
            // Tentukan status berdasarkan role
            $status_input_lomba = ($this->role == 'admin') ? 'approved' : 'pending';

            // Insert data lomba
            $sql = "INSERT INTO {$this->table} (nama_lomba, id_tingkat, tanggal, detail_lomba, status_input_lomba, gambar) 
                   VALUES (?, ?, ?, ?, ?, ?)";
            $params = [
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

    public function updateStatus($id, $status, $alasan = null)
    {
        if ($this->driver == 'mysql') {
            $query = $this->db->prepare("UPDATE {$this->table} 
                                         SET status_input_lomba = ?, alasan_penolakan = ?
                                         WHERE id_lomba = ?");
            $query->bind_param('ssi', $status, $alasan, $id);
            return $query->execute();
        } else {
            $sql = "UPDATE {$this->table} 
                    SET status_input_lomba = ?, alasan_penolakan = ?
                    WHERE id_lomba = ?";
            $params = [$status, $alasan, $id];
            return sqlsrv_query($this->db, $sql, $params) !== false;
        }
    }

    public function updateData($id, $data)
    {
        if ($this->driver == 'mysql') {
            $query = $this->db->prepare("UPDATE {$this->table} 
                                         SET nama_lomba = ?, id_tingkat = ? , tanggal = ?, detail_lomba = ?, gambar = ?
                                         WHERE id_lomba = ?");
            $query->bind_param('sisssi', $data['nama_lomba'], $data['id_tingkat'], $data['tanggal'], $data['detail_lomba'], $data['gambar'], $id);
            $query->execute();
        } else {
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
}
?>

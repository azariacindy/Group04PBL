<?php
include('../Model/UserModel.php');
class PrestasiModel extends Model
{
    protected $db;
    protected $table = 'prestasi';
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
        if ($this->driver == 'mysql') {
            $query = $this->db->prepare("INSERT INTO {$this->table} (nim, nip, id_lomba, tanggal, detail_lomba, berkas, peringkat, status_lomba, status_validasi) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $query->bind_param(
                'ssissssss',
                $data['nim'],
                $data['nip'],
                $data['id_lomba'],
                $data['tanggal'],
                $data['detail_lomba'],
                $data['berkas'],
                $data['peringkat'],
                $data['status_lomba'],
                $data['status_validasi']
            );
            $query->execute();
        } else {
            $sql = "INSERT INTO {$this->table} (id_prestasi,nim, nip, id_lomba, tanggal, detail_lomba, berkas, peringkat, status_lomba, status_validasi) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $data['id_prestasi'],
                $data['nim'],
                $data['nip'],
                $data['id_lomba'],
                $data['tanggal'],
                $data['detail_lomba'],
                $data['berkas'],
                $data['peringkat'],
                $data['status_lomba'],
                $data['status_validasi']
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
            $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_prestasi = ?");
            $query->bind_param('i', $id);
            $query->execute();
            return $query->get_result()->fetch_assoc();
        } else {
            $sql = "SELECT * FROM {$this->table} WHERE id_prestasi = ?";
            $params = [$id];
            $stmt = sqlsrv_query($this->db, $sql, $params);
            return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        }
    }

    public function updateData($id, $data)
    {
        if ($this->driver == 'mysql') {
            $query = $this->db->prepare("UPDATE {$this->table} 
                                         SET nim = ?, nip = ?, id_lomba = ?, tanggal = ?, detail_lomba = ?, berkas = ?, peringkat = ?, status_lomba = ?, status_validasi = ?
                                         WHERE id_prestasi = ?");
            $query->bind_param(
                'ssissssssi',
                $data['nim'],
                $data['nip'],
                $data['id_lomba'],
                $data['tanggal'],
                $data['detail_lomba'],
                $data['berkas'],
                $data['peringkat'],
                $data['status_lomba'],
                $data['status_validasi'],
                $id
            );
            $query->execute();
        } else {
            $sql = "UPDATE {$this->table} 
                    SET nim = ?, nip = ?, id_lomba = ?, tanggal = ?, detail_lomba = ?, berkas = ?, peringkat = ?, status_lomba = ?, status_validasi = ?
                    WHERE id_prestasi = ?";
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
                $id
            ];
            sqlsrv_query($this->db, $sql, $params);
        }
    }

    public function deleteData($id)
    {
        if ($this->driver == 'mysql') {
            $query = $this->db->prepare("DELETE FROM {$this->table} WHERE id_prestasi = ?");
            $query->bind_param('i', $id);
            $query->execute();
        } else {
            $sql = "DELETE FROM {$this->table} WHERE id_prestasi = ?";
            $params = [$id];
            sqlsrv_query($this->db, $sql, $params);
        }
    }
}
?>

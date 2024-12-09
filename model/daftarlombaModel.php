<?php
include('Model.php');

class daftarlombaModel extends Model
{
    protected $db;
    protected $table = 'lomba';
    protected $driver;

    public function __construct()
    {
        include_once('../lib/Connection.php');
        $this->db = $db;
        $this->driver = $use_driver; // pastikan $use_driver diatur di Connection.php (mysql/sqlsrv)
    }

    public function insertData($data)
    {
        if ($this->driver == 'mysql') {
            $query = $this->db->prepare("INSERT INTO {$this->table} (nama_lomba, tanggal, Kategori, id_tingkat) 
                                         VALUES (?, ?, ?, ?)");
            $query->bind_param('ssisss', $data['nama_lomba'], $data['tanggal'], $data['kategori'], $data['id_tingkat']);
            $query->execute();
        } else {
            $sql = "INSERT INTO {$this->table} (nama_lomba, tanggal, Kategori, id_tingkat) 
                    VALUES (?, ?, ?, ?)";
            $params = [
                $data['nama_lomba'],
                $data['tanggal'],
                $data['kategori'],
                $data['id_tingkat']
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

    public function updateData($id, $data)
    {
        if ($this->driver == 'mysql') {
            $query = $this->db->prepare("UPDATE {$this->table} 
                                         SET nama_lomba = ?, tanggal = ?, kategori = ?, id_tingkat = ?
                                         WHERE id_lomba = ?");
            $query->bind_param('sssi', $data['nama_lomba'], $data['tanggal'], $data['kategori'], $data['id_tingkat'], $id);
            $query->execute();
        } else {
            $sql = "UPDATE {$this->table} 
                    SET nama_lomba = ?, tanggal = ?, kategori = ?, id_tingkat = ? 
                    WHERE id_lomba = ?";
            $params = [
                $data['nama_lomba'],
                $data['tanggal'],
                $data['kategori'],
                $data['id_tingkat'],
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
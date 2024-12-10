<?php
include('Model.php');
$session = new Session();
class UserModel extends Model
{
    protected $db;
    protected $table = '[user]';
    protected $driver;
    
    public function __construct()
    {
        include('../lib/Connection.php');
        $this->db = $db;
        $this->driver = $use_driver;
    }
    public function insertData($data)
    {
        if ($this->driver == 'mysql') {
            // prepare statement untuk query insert
            $query = $this->db->prepare("insert into {$this->table} (username, password,
            role) values(?,?,?)");
            // binding parameter ke query, "s" berarti string, "ss" berarti dua string
            $query->bind_param(
                'sss',
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role']
                
            );
            // eksekusi query untuk menyimpan ke database
            $query->execute();
        } else {
            // eksekusi query untuk menyimpan ke database
            sqlsrv_query($this->db, "insert into {$this->table} (username, password,
            role) values(?,?,?)", array(
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role']
            ));
        }
    }
    public function getData()
    {
        if ($this->driver == 'mysql') {
            // query untuk mengambil data dari tabel
            return $this->db->query("select * from {$this->table} ")->fetch_all(MYSQLI_ASSOC);
        } else {
            // query untuk mengambil data dari tabel
            $query = sqlsrv_query($this->db, "select * from {$this->table}");
            $data = [];
            while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
                $data[] = $row;
            }
            return $data;
        }
    }
    public function getDataById($id)
    {
        if ($this->driver == 'mysql') {
            // query untuk mengambil data berdasarkan id
            $query = $this->db->prepare("select * from {$this->table} where user_id = ?");
            // binding parameter ke query "i" berarti integer. Biar tidak kena SQL Injection
            $query->bind_param('i', $id);
            // eksekusi query
            $query->execute();
            // ambil hasil query
            return $query->get_result()->fetch_assoc();
        } else {
            // query untuk mengambil data berdasarkan id
            $query = sqlsrv_query($this->db, "select * from {$this->table} where user_id =
    ?", [$id]);
            // ambil hasil query
            return sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);
        }
    }
    public function updateData($id, $data)
    {
        if ($this->driver == 'mysql') {
            // query untuk update data
            $query = $this->db->prepare("update {$this->table} set username = ?,
            password = ?, role = ? where user_id = ?");
            // binding parameter ke query
            $query->bind_param(
                'sssi',
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'],
                $id
            );
            // eksekusi query
            $query->execute();
        } else {
            // query untuk update data
            sqlsrv_query($this->db, "update {$this->table} set username = ?, password
            = ?, role = ? where user_id = ?", [
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'],
                $id
            ]);
        }
    }
    public function deleteData($id)
    {
        if ($this->driver == 'mysql') {
            // query untuk delete data
            $query = $this->db->prepare("delete from {$this->table} where user_id = ?");
            // binding parameter ke query
            $query->bind_param('i', $id);
            // eksekusi query
            $query->execute();
        } else {
            // query untuk delete data
            sqlsrv_query($this->db, "delete from {$this->table} where user_id = ?", [$id]);
        }
    }

    public function getSingleDataByKeyword($column, $keyword)
{
    if ($this->driver == 'mysql') {
        // query untuk mengambil data berdasarkan kolom tertentu
        $query = $this->db->prepare("select * from {$this->table} where {$column} = ?");
        $query->bind_param('s', $keyword);
        $query->execute();
        return $query->get_result()->fetch_assoc();
    } else {
        // query untuk mengambil data berdasarkan kolom tertentu
        $query = sqlsrv_query($this->db, "select * from {$this->table} where {$column} = ?", [$keyword]);
        if ($query === false) {
            die(print_r(sqlsrv_errors(), true)); // Menampilkan error jika query gagal
        }
        $result = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);
        return $result ?: []; // Mengembalikan array kosong jika tidak ada hasil
    }
}
}
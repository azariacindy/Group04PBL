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
        if (!$db) {
            die('Koneksi database gagal: ' . print_r(sqlsrv_errors(), true));
        }
        $this->db = $db;
        $this->driver = $use_driver;
    }
    public function insertData($data)
    {
        // eksekusi query untuk menyimpan ke database
        sqlsrv_query($this->db, "insert into {$this->table} (username, password,
            role) values(?,?,?)", array(
            $data['username'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role']
        ));
    }
    public function getData()
    {
        
            // query untuk mengambil data dari tabel
            $query = sqlsrv_query($this->db, "select * from {$this->table}");
            $data = [];
            while ($row = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC)) {
                $data[] = $row;
            }
            return $data;
    }

    public function getDataById($id)
    {
            // query untuk mengambil data berdasarkan id
            $query = sqlsrv_query($this->db, "select * from {$this->table} where id_user =
    ?", [$id]);
            // ambil hasil query
            return sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);
    }

    public function updateData($id, $data)
    {
            // query untuk update data
            sqlsrv_query($this->db, "update {$this->table} set username = ?, password
            = ?, role = ? where id_user = ?", [
                $data['username'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'],
                $id
            ]);
    }
    public function deleteData($id)
    {
            // query untuk delete data
            sqlsrv_query($this->db, "delete from {$this->table} where id_user = ?", [$id]);
        
    }

    public function getSingleDataByKeyword($column, $keyword)
    {
        
            // query untuk mengambil data berdasarkan kolom tertentu
            $query = sqlsrv_query($this->db, "select * from {$this->table} where {$column} = ?", [$keyword]);
            if ($query === false) {
                die(print_r(sqlsrv_errors(), true)); // Menampilkan error jika query gagal
            }
            $result = sqlsrv_fetch_array($query, SQLSRV_FETCH_ASSOC);
            return $result ?: []; // Mengembalikan array kosong jika tidak ada hasil
    }

    
}

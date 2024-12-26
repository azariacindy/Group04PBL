<?php
include_once('Model.php');

class GlobalModel extends Model
{
    protected $table = '';
    protected $db;
    protected $driver;

    public function __construct()
    {
        include_once(__DIR__ . '/../lib/Connection.php');
        if (!$db) {
            die('Koneksi database gagal: ' . print_r(sqlsrv_errors(), true));
        }
        $this->db = $db;
        $this->driver = $use_driver;
    }

    public function insertData($id) {}
    public function getData() {}
    public function getDataById($id) {}
    public function updateData($id, $data) {}
    public function deleteData($id) {}
}
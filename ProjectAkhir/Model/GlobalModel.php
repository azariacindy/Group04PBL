<?php
include_once(__DIR__ . '/Model.php');

class GlobalModel extends Model
{
    protected $table = '';
    protected $db;
    protected $driver;

    public function __construct()
    {
        require(__DIR__ . '/../lib/Connection.php');
        if (!isset($db) || $db === false) {
            throw new Exception('Database connection failed');
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
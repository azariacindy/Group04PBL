<?php
require_once 'Database.php';

class CompetitionModel {
    private $db;
    private $conn;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    public function getAllCompetitions() {
        $query = "SELECT l.*, t.nama_tingkat 
                 FROM lomba l 
                 LEFT JOIN tingkat t ON l.id_tingkat = t.id_tingkat 
                 WHERE l.status_input_lomba = 'approved'  
                 ORDER BY l.tanggal DESC";
        
        $stmt = sqlsrv_query($this->conn, $query);
        
        if ($stmt === false) {
            throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
        }

        $results = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }

        return $results;
    }

    public function getUnvalidatedCompetitions() {
        $query = "SELECT l.*, t.nama_tingkat 
                 FROM lomba l 
                 LEFT JOIN tingkat t ON l.id_tingkat = t.id_tingkat 
                 WHERE l.status_input_lomba = 'in progress'
                 ORDER BY l.tanggal DESC";
        
        $stmt = sqlsrv_query($this->conn, $query);
        
        if ($stmt === false) {
            throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
        }

        $results = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }

        return $results;
    }

    public function addCompetition($nama_lomba, $tanggal, $detail_lomba, $gambar, $id_tingkat, $id_user) {
        $query = "INSERT INTO lomba (nama_lomba, tanggal, detail_lomba, gambar, id_tingkat, id_user, status_input_lomba) 
                 VALUES (?, ?, ?, ?, ?, ?, 'in progress')";
        $params = array($nama_lomba, $tanggal, $detail_lomba, $gambar, $id_tingkat, $id_user);
        
        $stmt = sqlsrv_query($this->conn, $query, $params);
        
        if ($stmt === false) {
            throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
        }

        return true;
    }

    public function validateCompetition($id_lomba) {
        $query = "UPDATE lomba SET status_input_lomba = 'approved' WHERE id_lomba = ?";
        $params = array($id_lomba);
        
        $stmt = sqlsrv_query($this->conn, $query, $params);
        
        if ($stmt === false) {
            throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
        }

        return true;
    }

    public function getCompetitionById($id_lomba) {
        $query = "SELECT l.*, t.nama_tingkat 
                 FROM lomba l 
                 LEFT JOIN tingkat t ON l.id_tingkat = t.id_tingkat 
                 WHERE l.id_lomba = ?";
        $params = array($id_lomba);
        
        $stmt = sqlsrv_query($this->conn, $query, $params);
        
        if ($stmt === false) {
            throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
        }

        return sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    }

    public function getTingkatLomba() {
        $query = "SELECT * FROM tingkat ORDER BY nama_tingkat";
        
        $stmt = sqlsrv_query($this->conn, $query);
        
        if ($stmt === false) {
            throw new Exception("Error executing query: " . print_r(sqlsrv_errors(), true));
        }

        $results = array();
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $results[] = $row;
        }

        return $results;
    }

    // Helper functions
    public static function formatDate($date) {
        if ($date instanceof DateTime) {
            return $date->format('d F Y');
        }
        return date('d F Y', strtotime($date));
    }

    public static function safeEcho($str) {
        return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
    }

    public static function getImageUrl($path) {
        if (empty($path)) {
            return self::getDefaultImagePath();
        }

        // Jika URL lengkap (dimulai dengan http atau https)
        if (preg_match('/^https?:\/\//', $path)) {
            return $path;
        }
        
        // Jika path relatif, periksa apakah file ada
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/group04PBL/ProjectAkhir/' . ltrim($path, '/');
        if (file_exists($fullPath)) {
            return $path;
        }

        return self::getDefaultImagePath();
    }

    public static function getDefaultImagePath() {
        $defaultPath = 'assets/image/default-competition.jpg';
        $fullPath = $_SERVER['DOCUMENT_ROOT'] . '/group04PBL/ProjectAkhir/' . $defaultPath;
        
        // Jika gambar default tidak ada, gunakan placeholder
        if (!file_exists($fullPath)) {
            return 'https://via.placeholder.com/800x400.jpg?text=No+Image+Available';
        }
        
        return $defaultPath;
    }
}
?>

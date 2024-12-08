<?php
include('Model.php');

class LombaModel extends Model {
    private $db;
    private $table = 'm_lomba';  // Ganti dengan nama tabel lomba di database

    public function __construct() {
        include_once('../lib/Connection.php');
        $this->db = $db;
        $this->db->set_charset('utf8');
    }

    public function insertData($data) {
        // Query untuk memasukkan data lomba
        $query = $this->db->prepare("INSERT INTO {$this->table} (nama_lomba, id_tingkat, tanggal, detail_lomba, gambar) VALUES (?, ?, ?, ?, ?)");

        // Binding parameter ke query
        $query->bind_param('sisss', $data['nama_lomba'], $data['id_tingkat'], $data['tanggal'], $data['detail_lomba'], $data['gambar']);

        // Eksekusi query
        $query->execute();
    }

    public function getData() {
        // Query untuk mengambil semua data lomba
        return $this->db->query("SELECT * FROM {$this->table}");
    }

    public function getDataById($id) {
        // Query untuk mengambil data lomba berdasarkan ID
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_lomba = ?");

        // Binding parameter ke query
        $query->bind_param('i', $id);

        // Eksekusi query
        $query->execute();

        // Ambil hasil query
        return $query->get_result()->fetch_assoc();
    }

    public function updateData($id, $data) {
        // Query untuk update data lomba
        $query = $this->db->prepare("UPDATE {$this->table} SET nama_lomba = ?, id_tingkat = ?, tanggal = ?, detail_lomba = ?, gambar = ? WHERE id_lomba = ?");

        // Binding parameter ke query
        $query->bind_param('sisssi', $data['nama_lomba'], $data['id_tingkat'], $data['tanggal'], $data['detail_lomba'], $data['gambar'], $id);

        // Eksekusi query
        $query->execute();
    }

    public function deleteData($id) {
        // Query untuk menghapus data lomba
        $query = $this->db->prepare("DELETE FROM {$this->table} WHERE id_lomba = ?");

        // Binding parameter ke query
        $query->bind_param('i', $id);

        // Eksekusi query
        $query->execute();
    }
}
?>

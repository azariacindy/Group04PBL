<?php
include('Model.php');

class PrestasiModel extends Model {
    private $db;
    private $table = 'prestasi';

    public function __construct() {
        include_once('../lib/Connection.php');
        $this->db = $db;
        $this->db->set_charset('utf8');
    }

    public function getData() {
        return $this->db->query("SELECT * FROM {$this->table}");
    }

    public function getDataById($id) {
        $query = $this->db->prepare("SELECT * FROM {$this->table} WHERE id_prestasi = ?");
        $query->bind_param('i', $id);
        $query->execute();
        return $query->get_result()->fetch_assoc();
    }

    public function insertData($data) {
        $query = $this->db->prepare("INSERT INTO {$this->table} (nim, nip, id_lomba, tanggal, detail_lomba, berkas, peringkat, status_lomba, status_validasi) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $query->bind_param('sssssssss', $data['nim'], $data['nip'], $data['id_lomba'], $data['tanggal'], $data['detail_lomba'], $data['berkas'], $data['peringkat'], $data['status_lomba'], $data['status_validasi']);
        if ($query->execute()) {
            echo json_encode(['status' => true, 'message' => 'Data berhasil disimpan']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Data gagal disimpan. Error: ' . $this->db->error]);
        }
    }

    public function updateData($id, $data) {
        $query = $this->db->prepare("UPDATE {$this->table} SET nim = ?, nip = ?, id_lomba = ?, tanggal = ?, detail_lomba = ?, berkas = ?, peringkat = ?, status_lomba = ?, status_validasi = ? WHERE id_prestasi = ?");
        $query->bind_param('sssssssssi', $data['nim'], $data['nip'], $data['id_lomba'], $data['tanggal'], $data['detail_lomba'], $data['berkas'], $data['peringkat'], $data['status_lomba'], $data['status_validasi'], $id);
        if ($query->execute()) {
            echo json_encode(['status' => true, 'message' => 'Data berhasil diperbarui']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Data gagal diperbarui. Error: ' . $this->db->error]);
        }
    }

    public function deleteData($id) {
        $query = $this->db->prepare("DELETE FROM {$this->table} WHERE id_prestasi = ?");
        $query->bind_param('i', $id);
        if ($query->execute()) {
            echo json_encode(['status' => true, 'message' => 'Data berhasil dihapus']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Data gagal dihapus. Error: ' . $this->db->error]);
        }
    }
}

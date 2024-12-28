<?php
include_once(__DIR__ . '/Model.php');

class NotifikasiModel extends Model
{
    protected $db;
    protected $driver;
    protected $table = '[notifikasi]';

    public function __construct()
    {
        include(__DIR__ . '/../lib/Connection.php');
        if (!$db) {
            die('Koneksi database gagal: ' . print_r(sqlsrv_errors(), true));
        }
        $this->db = $db;
        $this->driver = $use_driver;
    }

    // Menggunakan view untuk mendapatkan detail notifikasi
    public function getNotifikasiDetail($id_mahasiswa = null)
    {
        try {
            $sql = "SELECT * FROM vw_notifikasi_detail";
            if ($id_mahasiswa) {
                $sql .= " WHERE id_mahasiswa = ? ORDER BY tanggal DESC";
                $params = [$id_mahasiswa];
            } else {
                $sql .= " ORDER BY tanggal DESC";
                $params = [];
            }
            
            $stmt = sqlsrv_query($this->db, $sql, $params);
            if ($stmt === false) {
                throw new Exception('Gagal mengambil data notifikasi: ' . print_r(sqlsrv_errors(), true));
            }
            
            $result = [];
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                $result[] = $row;
            }
            return [
                'status' => true,
                'data' => $result
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Method untuk menandai notifikasi sudah dibaca
    public function markAsRead($id_notifikasi)
    {
        try {
            $sql = "UPDATE {$this->table} SET status = 'Read' WHERE id_notifikasi = ?";
            $params = [$id_notifikasi];
            
            $stmt = sqlsrv_query($this->db, $sql, $params);
            if ($stmt === false) {
                throw new Exception('Gagal memperbarui status notifikasi: ' . print_r(sqlsrv_errors(), true));
            }
            
            return [
                'status' => true,
                'message' => 'Notifikasi telah ditandai sebagai dibaca'
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    // Method untuk menghitung notifikasi yang belum dibaca
    public function getUnreadCount($id_mahasiswa)
    {
        try {
            $sql = "SELECT COUNT(*) as unread FROM {$this->table} WHERE id_mahasiswa = ? AND status = 'Unread'";
            $params = [$id_mahasiswa];
            
            $stmt = sqlsrv_query($this->db, $sql, $params);
            if ($stmt === false) {
                throw new Exception('Gagal menghitung notifikasi: ' . print_r(sqlsrv_errors(), true));
            }
            
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            return [
                'status' => true,
                'count' => $row['unread']
            ];
        } catch (Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
?>

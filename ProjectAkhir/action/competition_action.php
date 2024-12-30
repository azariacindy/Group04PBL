<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../Model/CompetitionModel.php';

$competitionModel = new CompetitionModel();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                // Handle file upload
                $targetDir = "../assets/image/competitions/";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $imageFile = $_FILES['gambar'];
                $imagePath = $targetDir . basename($imageFile['name']);
                
                if (move_uploaded_file($imageFile['tmp_name'], $imagePath)) {
                    $nama_lomba = $_POST['nama_lomba'];
                    $tanggal = $_POST['tanggal'];
                    $detail_lomba = $_POST['detail_lomba'];
                    $id_tingkat = $_POST['id_tingkat'];
                    $id_user = $_SESSION['user_id']; // Assuming user_id is stored in session
                    $dbImagePath = "assets/image/competitions/" . basename($imageFile['name']);

                    if ($competitionModel->addCompetition($nama_lomba, $tanggal, $detail_lomba, $dbImagePath, $id_tingkat, $id_user)) {
                        $_SESSION['success'] = "Kompetisi berhasil ditambahkan!";
                    } else {
                        $_SESSION['error'] = "Gagal menambahkan kompetisi.";
                    }
                } else {
                    $_SESSION['error'] = "Gagal mengupload gambar.";
                }
                break;
        }
    }
    header("Location: ../pages/competitions.php");
    exit();
}

// Function to get all competitions
function getCompetitions() {
    global $competitionModel;
    return $competitionModel->getAllCompetitions();
}

// Function to get competition levels
function getCompetitionLevels() {
    global $competitionModel;
    return $competitionModel->getTingkatLomba();
}
?>

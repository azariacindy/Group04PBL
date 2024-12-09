<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

// Fungsi untuk melihat daftar lomba
function viewCompetitions() {
    global $conn;
    $result = $conn->query("SELECT * FROM competitions");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Fungsi untuk melihat semua prestasi mahasiswa
function viewAchievements() {
    global $conn;
    $result = $conn->query("SELECT * FROM achievements");
    return $result->fetch_all(MYSQLI_ASSOC);
}

$competitions = viewCompetitions();
$achievements = viewAchievements();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome, <?php echo $_SESSION['username']; ?></h1>
    <h2>Daftar Lomba</h2>
    <ul>
        <?php foreach ($competitions as $competition): ?>
            <li><?php echo $competition['title']; ?> - <?php echo $competition['description']; ?></li>
        <?php endforeach; ?>
    </ul>

    <h2>Prestasi Mahasiswa</h2>
    <ul>
        <?php foreach ($achievements as $achievement): ?>
            <li><?php echo $achievement['student_name']; ?></li>
        <?php endforeach; ?>
    </ul>

    <a href="add_competition.php">Input Lomba Baru</a>
    <a href="view_achievements.php">Lihat Semua Prestasi Mahasiswa</a>
    <a href="become_supervisor.php">Menjadi Dosen Pembimbing</a>
    <form method="POST" action="logout.php">
        <button type="submit">Logout</button>
    </form>
</body>
</html>
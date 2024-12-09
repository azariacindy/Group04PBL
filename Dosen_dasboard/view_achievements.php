<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

// Fungsi untuk melihat semua prestasi mahasiswa
function viewAchievements() {
    global $conn;
    $result = $conn->query("SELECT * FROM achievements");
    return $result->fetch_all(MYSQLI_ASSOC);
}

$achievements = viewAchievements();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Achievements</title>
</head>
<body>
    <h1>Prestasi Mahasiswa</h1>
    <ul>
        <?php foreach ($achievements as $achievement): ?>
            <li><?php echo $achievement['student_name']; ?></li>
        <?php endforeach; ?>
    </ul>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
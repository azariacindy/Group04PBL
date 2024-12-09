<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_name = $_POST['student_name'];

    $stmt = $conn->prepare("INSERT INTO supervisors (user_id, student_name) VALUES (?, ?)");
    $stmt->bind_param("is", $_SESSION['user_id'], $student_name); // Pastikan user_id disimpan saat login
    if ($stmt->execute()) {
        $success = "Successfully became supervisor for " . $student_name;
    } else {
        $error = "Failed to become supervisor.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Become Supervisor</title>
</head>
<body>
    <h1>Menjadi Dosen Pembimbing</h1>
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="student_name" placeholder="Student Name" required>
        <button type="submit">Become Supervisor</button>
    </form>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO competitions (title, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $description);
    if ($stmt->execute()) {
        $success = "Competition added successfully!";
    } else {
        $error = "Failed to add competition.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Competition</title>
</head>
<body>
    <h1>Add Competition</h1>
    <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="title" placeholder="Competition Title" required>
        <textarea name="description" placeholder="Competition Description" required></textarea>
        <button type="submit">Add Competition</button>
    </form>
    <a href="dashboard.php">Back to Dashboard</a>
</body>
</html>
<?php
$host = 'localhost';
$db = 'your_database'; // Ganti dengan nama database Anda
$user = 'your_username'; // Ganti dengan username database Anda
$pass = 'your_password'; // Ganti dengan password database Anda

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
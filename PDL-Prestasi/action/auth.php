<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('../lib/Session.php');
include('../lib/Connection.php');

$session = new Session();

$act = isset($_GET['act']) ? strtolower($_GET['act']) : '';

if ($act == 'login') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Prepare query
    $query = $db->prepare('SELECT * FROM users WHERE username = ?');
    if (!$query) {
        die("Prepare failed: (" . $db->errno . ") " . $db->error);
    }

    $query->bind_param('s', $username);

    // Execute query
    if ($query->execute()) {
        $result = $query->get_result();
        $data = $result->fetch_assoc();

        // Verify password
        if ($data && password_verify($password, $data['password'])) {
            $session->set('is_login', true);
            $session->set('username', $data['username']);
            $session->set('name', $data['nama']);
            $session->set('level', $data['role']);
            $session->commit();

            header('Location: ../index.php', false);
        } else {
            $session->setFlash('status', false);
            $session->setFlash('message', 'Username dan password salah.');
            $session->commit();
            header('Location: ../login.php', false);
        }
    } else {
        die("Query execution failed: (" . $query->errno . ") " . $query->error);
    }
} else if ($act == 'logout') {
    $session->deleteAll();
    header('Location: ../login.php', false);
}
?>

<?php
include('../lib/Session.php');
include('../lib/Connection.php');
$session = new Session();
$act = isset($_GET['act']) ? strtolower($_GET['act']) : '';
if ($act == 'login') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    include('../model/UserModel.php');
    // digunakan untuk query user
    $user = new UserModel();
    $data = $user->getSingleDataByKeyword('username', $username);
    // jika password sesuai
    if (password_verify($password, $data['password'])) {
        $session->set('is_login', true);
        $session->set('username', $data['username']);
        $session->set('role', $data['role']);
        $session->commit();

        // // Redirect berdasarkan role
        // if ($data['role'] === 'Admin') {
        //     header('Location: ../admin/dashboard.php');
        //     exit;
        // } else if ($data['role'] === 'Mahasiswa') {
        //     header('Location: ../mahasiswa/dashboard.php');
        //     exit;
        // } else if ($data['role'] === 'Dosen') {
        //     header('Location: ../dosen/dashboard.php');
        //     exit;
        // } else {
        //     $session->deleteAll();
        //     $session->setFlash('status', false);
        //     $session->setFlash('message', 'Level pengguna tidak valid.');
        //     header('Location: ../login.php');
        //     exit;
        // }
        header('Location: ../index.php', false);
    } else {
        $session->setFlash('status', false);
        $session->setFlash('message', 'Username dan password salah.');
        $session->commit();
        header('Location: ../login.php', false);
    }
    
} else if ($act == 'logout') {
    $session->deleteAll();
    header('Location: ../login.php', false);
}
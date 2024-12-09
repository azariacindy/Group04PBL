<?php
include('lib/Session.php');
$session = new Session();
if ($session->get('is_login') !== true) {
  header('Location: login.php');
}
// Check if the user is logged in by verifying the session 'role'
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
  // Include necessary configuration and function files
  require 'lib/Connection.php';
  // Include header template
  require_once 'admin/index.php';

}else if (isset($_SESSION['role']) && $_SESSION['role'] === 'mahasiswa'){
  require 'lib/Connection.php';
  // Include header template
  require_once 'mahasiswa/index.php';
  
}



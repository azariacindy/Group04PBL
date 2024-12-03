<?php
include('lib/Session.php');
$session = new Session();
if ($session->get('is_login') !== true) {
  header('Location: login.php');
}
// Check if the user is logged in by verifying the session 'level'
if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin') {
  // Include necessary configuration and function files
  require 'lib/Connection.php';
  require 'fungsi/pesan_kilat.php';

  // Include header template
  include 'admin/index.php';
} else {
  // If user is not logged in, redirect to the login page
  header("Location: login.php");
}

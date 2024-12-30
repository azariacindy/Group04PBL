<?php
include('lib/Session.php');
$session = new Session();
if ($session->get('is_login') !== true) {
    header('Location: login.php');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Prestasi Polinema</title>
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="adminlte/plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="adminlte/dist/css/adminlte.min.css">
  <!-- DataTables -->
  <link rel="stylesheet" href="adminlte/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
  <link rel="stylesheet" href="adminlte/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
  <link rel="stylesheet" href="adminlte/plugins/datatables-buttons/css/buttons.bootstrap4.min.css">
  <!-- jQuery -->
  <script src="adminlte/plugins/jquery/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Bootstrap CSS -->
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Custom CSS -->
   <link rel="stylesheet" href="assets/css/dashboard-style.css">
   <link rel="stylesheet" href="assets/css/competition-style.css">
   <link rel="stylesheet" href="assets/css/user-management.css">
   
</head>

<body class="hold-transition sidebar-mini">
  <!-- Site wrapper -->
  <div class="wrapper">

    <!-- Navbar -->
    <?php include('layouts/header.php'); ?>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="index.php" class="brand-link">
        <img src="assets/image/JTI.png" alt="JTI Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">JTI Presma</span>
      </a>

      <!-- Sidebar -->
      <?php include('layouts/sidebar.php'); ?>
      <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
      <!-- Content Header (Page header) -->
      <?php
      $page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
      switch (strtolower($page)) {
        case 'dashboard':
          include('pages/dashboard.php');
          break;
        case 'daftarlomba':
          include('pages/daftarlomba.php');
          break;
        case 'input_prestasi':
          include('pages/input_prestasi.php');
          break;
        case 'competitions':
          include('pages/competitions.php');
          break;
        case 'pengajuan_dosen':
          include('pages/pengajuan_dosen.php');
          break;
        case 'manage_users':
          include('pages/manage_users.php');
          break;
    
      }
      ?>

      <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <?php include('layouts/footer.php'); ?>
    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
      <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
  </div>
  <!-- ./wrapper -->

  <!-- Bootstrap 4 -->
  <script src="adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <!-- jQuery Validation -->
  <script src="adminlte/plugins/jquery-validation/jquery.validate.min.js"></script>
  <script src="adminlte/plugins/jquery-validation/additional-methods.min.js"></script>
  <script src="adminlte/plugins/jquery-validation/localization/messages_id.min.js"></script>
  <!-- DataTables  & Plugins -->
  <script src="adminlte/plugins/datatables/jquery.dataTables.min.js"></script>
  <script src="adminlte/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="adminlte/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
  <script src="adminlte/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
  <script src="adminlte/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
  <script src="adminlte/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
  <script src="adminlte/plugins/jszip/jszip.min.js"></script>
  <script src="adminlte/plugins/pdfmake/pdfmake.min.js"></script>
  <script src="adminlte/plugins/pdfmake/vfs_fonts.js"></script>
  <script src="adminlte/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
  <script src="adminlte/plugins/datatables-buttons/js/buttons.print.min.js"></script>
  <script src="adminlte/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

  <!-- AdminLTE App -->
  <script src="adminlte/dist/js/adminlte.min.js"></script>
  <!-- AdminLTE for demo purposes
  <script src="adminlte/dist/js/demo.js"></script> -->
</body>

</html>
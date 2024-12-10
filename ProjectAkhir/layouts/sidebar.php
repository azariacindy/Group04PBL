<?php
    // Check if the user is logged in
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
} else {
    $username = ''; // Default value if not logged in
}
?>

<div class="sidebar"> 
    <!-- Sidebar user (optional) --> 
    <div class="user-panel mt-3 pb-3 mb-3 d-flex"> 
        <div class="image"> 
            <img src="adminlte/dist/img/user2-160x160.jpg" class="img-circle elevation-2" 
alt="User Image"> 
        </div> 
        <div class="info"> 
            <a href="/ProjectAkhir/admin/pages/profile.php" class="nav-link">Satriya Viar</a> 
        </div> 
    </div> 
 
    <!-- SidebarSearch Form --> 
    <div class="form-inline"> 
        <div class="input-group" data-widget="sidebar-search"> 
            <input class="form-control form-control-sidebar" type="search" 
placeholder="Search" aria-label="Search"> 
            <div class="input-group-append"><button class="btn btn-sidebar"><i class="fas 
fa-search fa-fw"></i></button></div> 
        </div> 
    </div> 

    <?php
    if ($_SESSION['role'] == 'admin') {
    ?>

    <!-- Sidebar Menu --> 
    <nav class="mt-2"> 
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" 
data-accordion="false"> 
        <!-- Add icons to the links using the .nav-icon class 
            with font-awesome or any other icon font library --> 
        <li class="nav-item"> 
            <a href="index.php" class="nav-link"> 
                <i class="nav-icon fas fa-tachometer-alt"></i> 
                <p>Dashboard</p> 
            </a> 
        </li> 
        <li class="nav-item border-bottom"> 
            <a href="index.php?page=daftarlomba" class="nav-link"> 
                <i class="nav-icon fas fa-flag"></i> 
                <p>Daftar Lomba</p> 
            </a> 
        </li> 
        <li class="nav-item border-bottom">
            <a href=""></a>
        </li>
        <li class="nav-item"> 
            <a href="../ProjectAkhir/action/auth.php?act=logout" class="nav-link"> 
                <i class="nav-icon fas fa-sign-out-alt"></i> 
                <p>Logout</p> 
            </a> 
        </li> 
    </ul> 
    </nav> 
    <!-- /.sidebar-menu --> 
</div>

<?php
    }
?>

<?php
    if ($_SESSION['role'] == 'mahasiswa') {
    ?>
    
    <!-- Sidebar Menu --> 
    <nav class="mt-2"> 
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" 
data-accordion="false"> 
        <!-- Add icons to the links using the .nav-icon class 
            with font-awesome or any other icon font library --> 
        <li class="nav-item"> 
            <a href="index.php" class="nav-link"> 
                <i class="nav-icon fas fa-tachometer-alt"></i> 
                <p>Dashboard</p> 
            </a> 
        </li> 
        <li class="nav-item border-item"> 
            <a href="index.php?page=daftarlomba" class="nav-link"> 
                <i class="nav-icon fas fa-flag"></i> 
                <p>Tambah Lomba</p> 
            </a> 
        </li> 
        <li class="nav-item border-bottom">
            <a href="index.php?page=input_prestasi" class="nav-link">
                <i class="nav-icon fas fa-medal"></i>
                <p>Input Prestasi</p>
            </a>
        </li>
        <li class="nav-item"> 
            <a href="../ProjectAkhir/action/auth.php?act=logout" class="nav-link"> 
                <i class="nav-icon fas fa-sign-out-alt"></i> 
                <p>Logout</p> 
            </a> 
        </li> 
    </ul> 
    </nav> 
    <!-- /.sidebar-menu --> 
</div>

<?php
    }
?>
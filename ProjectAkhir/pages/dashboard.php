<?php
include_once(__DIR__ . '/../lib/Session.php');

$session = new Session();

if ($session->get('is_login') !== true) {
    header('Location: login.php');
    exit;
}

$role = $session->get('role');
$username = $session->get('user');

include_once(__DIR__ . '/../Model/GlobalModel.php');
$global = new GlobalModel();

if ($role == 'admin') {
?>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Selamat Datang</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-body -->
            <div class="card-body">
                Selamat Datang <b><?php echo htmlspecialchars($username); ?></b>. Anda Login Sebagai <b>Admin</b>
            </div>
            <!-- /.card-footer-->
        </div>
        <!-- /.card -->
    </section>
<?php
} else if ($role == 'mahasiswa') {
?>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Blank Page</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Selamat Datang</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-body -->
            <div class="card-body">
                Selamat Datang <b><?php echo htmlspecialchars($username); ?></b>. Anda Login Sebagai <b>Mahasiswa</b>
            </div>
            <!-- /.card-footer-->
        </div>
        <!-- /.card -->
    </section>
<?php
} else if ($role == 'dosen') {
?>
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Blank Page</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Selamat Datang</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                    <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <!-- /.card-body -->
            <div class="card-body">
                Selamat Datang <b><?php echo htmlspecialchars($username); ?></b>. Anda Login Sebagai <b>Dosen</b>
            </div>
            <!-- /.card-footer-->
        </div>
        <!-- /.card -->
    </section>
<?php
}
?>
<?php
include('lib/Session.php');
$session = new Session();
if ($session->get('is_login') === true) {
    header('Location: index.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - POLINEMA</title>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/login-style.css">
    <!-- jQuery -->
    <script src="adminlte/plugins/jquery/jquery.min.js"></script>
    <!-- jQuery Validation -->
    <script src="adminlte/plugins/jquery-validation/jquery.validate.min.js"></script>
    <script src="adminlte/plugins/jquery-validation/additional-methods.min.js"></script>
    <script src="adminlte/plugins/jquery-validation/localization/messages_id.min.js"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <div class="illustration-container">
                <img src="assets/image/woman.png" alt="Student Illustration" class="illustration">
            </div>
            <h2>Informasi Perlombaan</h2>
            <p>Jelajahi kreativitasmu sebagai mahasiswa dengan mendaftar ragam perlombaan yang menarik</p>
        </div>
        <div class="login-right">
            <div class="login-form-container">
                <img src="assets/image/polinema.png" alt="Polinema Logo" class="logo">
                <h1>Selamat Datang!</h1>
                <p class="welcome-text">Anda telah memasuki portal sistem JTI untuk mendukung dokumentasi dan pencapaian prestasi mahasiswa secara optimal</p>
                
                <?php
                $status = $session->getFlash('status');
                if ($status === false) {
                    $message = $session->getFlash('message');
                    echo '<div class="alert alert-warning">' . $message .
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button></div>';
                }
                ?>

                <form action="../ProjectAkhir/action/auth.php?act=login" method="post" id="form-login">
                    <div class="form-group">
                        <input type="text" name="username" id="username" placeholder="Username/NIM" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" id="password" placeholder="Password" required>
                    </div>
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember" id="remember">
                            Remember me
                        </label>
                        <a href="#" class="forgot-password">Lupa password?</a>
                    </div>
                    <button type="submit" class="sign-in-btn">Sign In</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#form-login').validate({
                rules: {
                    username: {
                        required: true,
                        minlength: 3,
                        maxlength: 20
                    },
                    password: {
                        required: true,
                        minlength: 5,
                        maxlength: 255
                    }
                },
                errorElement: 'span',
                errorPlacement: function(error, element) {
                    error.addClass('invalid-feedback');
                    element.closest('.form-group').append(error);
                },
                highlight: function(element, errorClass, validClass) {
                    $(element).addClass('is-invalid');
                },
                unhighlight: function(element, errorClass, validClass) {
                    $(element).removeClass('is-invalid');
                }
            });
        });
    </script>
</body>
</html>
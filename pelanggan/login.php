<?php require "../config/db.php"; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Pelanggan - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<section class="checkout-section">
    <div class="login-box" style="margin:auto;">
        <h2>Login Pelanggan</h2>

        <?php show_flash(); ?>

        <form action="../proses/login-user.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>

            <button class="login-button">Login</button>
        </form>

        <p>Belum punya akun? <a href="register.php">Daftar</a></p>
    </div>
</section>

</body>
</html>
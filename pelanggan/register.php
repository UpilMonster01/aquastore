<?php require "../config/db.php"; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Pelanggan - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<section class="checkout-section">
    <div class="login-box" style="margin:auto;">
        <h2>Daftar Pelanggan</h2>

        <?php show_flash(); ?>

        <form action="../proses/register-user.php" method="POST">
            <input type="text" name="nama" placeholder="Nama lengkap" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="no_hp" placeholder="Nomor HP">
            <textarea name="alamat" placeholder="Alamat"></textarea>
            <input type="password" name="password" placeholder="Password" required>

            <button class="login-button">Daftar</button>
        </form>

        <p>Sudah punya akun? <a href="login.php">Login</a></p>
    </div>
</section>

</body>
</html>
<?php require "../config/db.php"; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Login Pelanggan - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <section class="auth-section">
        <div class="auth-card">
            <div class="auth-icon">🐟</div>

            <h1>Login AquaStore</h1>
            <p>Masuk untuk checkout lebih cepat dan melihat pesananmu.</p>

            <?php show_flash(); ?>

            <form action="../proses/login-user.php" method="POST">
                <input type="email" name="email" placeholder="Masukkan email" required>
                <input type="password" name="password" placeholder="Masukkan password" required>

                <button class="login-button full-button">
                    Login Sekarang
                </button>
            </form>

            <div class="auth-footer">
                Belum punya akun?
                <a href="register.php">Daftar di sini</a>
            </div>
        </div>
    </section>

</body>

</html>
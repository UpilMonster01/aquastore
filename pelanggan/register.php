<?php require "../config/db.php"; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Register Pelanggan - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>

    <section class="auth-section">
        <div class="auth-card">
            <div class="auth-icon">🐠</div>

            <h1>Daftar AquaStore</h1>
            <p>Buat akun untuk mulai belanja ikan dan perlengkapan.</p>

            <?php show_flash(); ?>

            <form action="../proses/register-user.php" method="POST">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="text" name="nama" placeholder="Nama lengkap" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="no_hp" placeholder="Nomor HP">
                <textarea name="alamat" placeholder="Alamat"></textarea>
                <input type="password" name="password" placeholder="Password" required>

                <button class="login-button full-button">
                    Daftar Sekarang
                </button>
            </form>

            <div class="auth-footer">
                Sudah punya akun?
                <a href="login.php">Login di sini</a>
            </div>
        </div>
    </section>

</body>

</html>
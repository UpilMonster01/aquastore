<?php
require "../config/db.php";

if (empty($_SESSION['user'])) {
    flash('error', 'Silakan login terlebih dahulu.');
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare("SELECT * FROM pelanggan WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    unset($_SESSION['user']);
    flash('error', 'Akun tidak ditemukan. Silakan login ulang.');
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Profil Saya - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=260">
</head>

<body>

    <?php include "../components/navbar.php"; ?>

    <section class="profile-section">
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?= e(strtoupper(substr($user['nama'], 0, 1))) ?>
                    </div>

                    <div>
                        <span>Akun Pelanggan</span>
                        <h1>Profil Saya</h1>
                        <p>Kelola data akun agar checkout lebih cepat dan mudah.</p>
                    </div>
                </div>

                <?php show_flash(); ?>

                <form action="../proses/update-profil.php" method="POST" class="profile-form">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= e($user['nama']) ?>" required>

                    <label>Email</label>
                    <input type="email" value="<?= e($user['email']) ?>" readonly>

                    <label>Nomor HP / WhatsApp</label>
                    <input type="text" name="no_hp" value="<?= e($user['no_hp'] ?? '') ?>"
                        placeholder="Contoh: 081234567890">

                    <label>Alamat</label>
                    <textarea name="alamat" placeholder="Alamat lengkap"><?= e($user['alamat'] ?? '') ?></textarea>

                    <label>Password Baru</label>
                    <input type="password" name="password" autocomplete="new-password" minlength="6"
                        placeholder="Kosongkan jika tidak ingin mengganti password">

                    <div class="profile-actions">
                        <a href="katalog.php" class="cancel-button">
                            Kembali
                        </a>

                        <button class="login-button full-button" type="submit">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            <div class="profile-side-card">
                <h3>Ringkasan Akun</h3>

                <div class="profile-info-item">
                    <span>Nama</span>
                    <b><?= e($user['nama']) ?></b>
                </div>

                <div class="profile-info-item">
                    <span>Email</span>
                    <b><?= e($user['email']) ?></b>
                </div>

                <div class="profile-info-item">
                    <span>No HP</span>
                    <b><?= e($user['no_hp'] ?: '-') ?></b>
                </div>

                <div class="profile-info-item">
                    <span>Terdaftar Sejak</span>
                    <b><?= date('d M Y', strtotime($user['created_at'])) ?></b>
                </div>

                <a href="pesanan-saya.php" class="hero-button profile-order-btn">
                    Lihat Pesanan Saya
                </a>
            </div>
        </div>
    </section>

    <script src="../assets/js/main.js"></script>
</body>

</html>
<?php
require "../config/db.php";

$id = (int) ($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM ikan WHERE id = ?");
$stmt->execute([$id]);
$ikan = $stmt->fetch();

if (!$ikan) {
    die("Ikan tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $j = max(1, (int) $_POST['jumlah']);

    if ($j > $ikan['stok']) {
        flash('error', 'Stok tidak cukup.');
    } else {
        $_SESSION['keranjang'][$id] = ($_SESSION['keranjang'][$id] ?? 0) + $j;
        flash('success', 'Ikan masuk keranjang.');
    }

    header("Location: detail.php?id=$id");
    exit;
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title><?= e($ikan['nama']) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <header class="topbar">
        <div class="brand">
            <div class="brand-icon">🐟</div>
            <div>
                <h2>AquaStore</h2>
                <small>Detail Ikan</small>
            </div>
        </div>
        <nav class="menu">
            <a href="../index.php">Beranda</a>
            <a href="katalog.php">Katalog</a>
            <a href="keranjang.php">Keranjang</a>
        </nav>
    </header>

    <section class="detail-section">
        <?php show_flash(); ?>

        <div class="detail-wrapper">
            <div class="detail-photo">
                <?php if ($ikan['foto']): ?>
                    <img src="../uploads/ikan/<?= e($ikan['foto']) ?>?v=<?= time() ?>">
                <?php else: ?>
                    <span><?= $ikan['kategori_sifat'] === 'Predator' ? '🦈' : '🐠' ?></span>
                <?php endif; ?>
            </div>

            <div class="detail-info">
                <span class="detail-badge"><?= e($ikan['kategori_air']) ?> • <?= e($ikan['kategori_sifat']) ?></span>
                <h1><?= e($ikan['nama']) ?></h1>
                <p><i><?= e($ikan['nama_latin']) ?></i></p>
                <h2><?= rupiah($ikan['harga']) ?></h2>
                <p><?= e($ikan['deskripsi']) ?></p>

                <div class="info-grid">
                    <div>
                        <b>Stok</b>
                        <span><?= e($ikan['stok']) ?></span>
                    </div>
                    <div>
                        <b>Ukuran</b>
                        <span><?= e($ikan['ukuran_cm']) ?> cm</span>
                    </div>
                    <div>
                        <b>Jenis</b>
                        <span><?= e($ikan['kategori_jenis']) ?></span>
                    </div>
                    <div>
                        <b>Perawatan</b>
                        <span><?= e($ikan['tingkat_perawatan']) ?></span>
                    </div>
                </div>

                <div class="care-box">
                    <h3>Tips Perawatan</h3>
                    <p>
                        <?= e($ikan['tips_perawatan'] ?: 'Belum ada tips perawatan khusus untuk ikan ini.') ?>
                    </p>
                </div>

                <form method="POST" class="cart-form">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                    <label>
                        Jumlah
                        <input type="number" name="jumlah" value="1" min="1" max="<?= e($ikan['stok']) ?>">
                    </label>
                    <button class="hero-button">Tambah ke Keranjang</button>
                </form>
            </div>
        </div>
    </section>
</body>

</html>
<?php
require "../config/db.php";

$where = ["1=1"];
$params = [];

foreach (['kategori_air', 'kategori_sifat', 'tingkat_perawatan', 'status'] as $f) {
    if (!empty($_GET[$f])) {
        $where[] = "$f = ?";
        $params[] = $_GET[$f];
    }
}

if (!empty($_GET['q'])) {
    $where[] = "nama LIKE ?";
    $params[] = "%" . $_GET['q'] . "%";
}

$order = "id DESC";

if (($_GET['sort'] ?? '') === 'termurah') {
    $order = "harga ASC";
} elseif (($_GET['sort'] ?? '') === 'termahal') {
    $order = "harga DESC";
} elseif (($_GET['sort'] ?? '') === 'az') {
    $order = "nama ASC";
}

$stmt = $pdo->prepare("SELECT * FROM ikan WHERE " . implode(' AND ', $where) . " ORDER BY $order");
$stmt->execute($params);
$data = $stmt->fetchAll();
$jumlahKeranjang = !empty($_SESSION['keranjang']) ? array_sum($_SESSION['keranjang']) : 0;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Katalog</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <header class="topbar">
        <div class="brand">
            <div class="brand-icon">🐟</div>
            <div>
                <h2>AquaStore</h2>
                <small>Katalog Ikan</small>
            </div>
        </div>
        <nav class="menu">
            <a href="../index.php">Beranda</a>
            <a href="katalog.php" class="active">Katalog</a>
            <a href="perawatan.php">Perlengkapan</a>
            <a href="cek-pesanan.php">Cek Pesanan</a>
        </nav>
        <a href="keranjang.php" class="cart">
            🛒
            <?php if ($jumlahKeranjang > 0): ?>
                <span><?= $jumlahKeranjang ?></span>
            <?php endif; ?>
        </a>
    </header>

    <section class="popular-section">
        <div class="section-title">
            <span>Katalog</span>
            <h2>Semua Ikan Hias</h2>
        </div>

        <form method="GET" class="filter-box">
            <input type="text" name="q" placeholder="Cari ikan..." value="<?= e($_GET['q'] ?? '') ?>">

            <select name="kategori_air">
                <option value="">Semua Air</option>
                <option value="Tawar" <?= ($_GET['kategori_air'] ?? '') === 'Tawar' ? 'selected' : '' ?>>Tawar</option>
                <option value="Laut" <?= ($_GET['kategori_air'] ?? '') === 'Laut' ? 'selected' : '' ?>>Laut</option>
                <option value="Payau" <?= ($_GET['kategori_air'] ?? '') === 'Payau' ? 'selected' : '' ?>>Payau</option>
            </select>

            <select name="kategori_sifat">
                <option value="">Semua Sifat</option>
                <option value="Predator" <?= ($_GET['kategori_sifat'] ?? '') === 'Predator' ? 'selected' : '' ?>>Predator</option>
                <option value="Non-Predator" <?= ($_GET['kategori_sifat'] ?? '') === 'Non-Predator' ? 'selected' : '' ?>>Non-Predator</option>
            </select>

            <select name="tingkat_perawatan">
                <option value="">Semua Perawatan</option>
                <option value="Mudah" <?= ($_GET['tingkat_perawatan'] ?? '') === 'Mudah' ? 'selected' : '' ?>>Mudah</option>
                <option value="Sedang" <?= ($_GET['tingkat_perawatan'] ?? '') === 'Sedang' ? 'selected' : '' ?>>Sedang</option>
                <option value="Sulit" <?= ($_GET['tingkat_perawatan'] ?? '') === 'Sulit' ? 'selected' : '' ?>>Sulit</option>
            </select>

            <select name="sort">
                <option value="">Terbaru</option>
                <option value="termurah" <?= ($_GET['sort'] ?? '') === 'termurah' ? 'selected' : '' ?>>Termurah</option>
                <option value="termahal" <?= ($_GET['sort'] ?? '') === 'termahal' ? 'selected' : '' ?>>Termahal</option>
                <option value="az" <?= ($_GET['sort'] ?? '') === 'az' ? 'selected' : '' ?>>A-Z</option>
            </select>

            <button>Filter</button>
        </form>

        <div class="fish-grid">
            <?php foreach ($data as $i): ?>
                <div class="fish-card">
                    <div class="fish-image">
                        <?php if ($i['foto']): ?>
                            <img src="../uploads/ikan/<?= e($i['foto']) ?>">
                        <?php else: ?>
                            <span><?= $i['kategori_sifat'] === 'Predator' ? '🦈' : '🐠' ?></span>
                        <?php endif; ?>
                    </div>

                    <h3><?= e($i['nama']) ?></h3>
                    <p><?= e($i['kategori_air']) ?> • <?= e($i['kategori_sifat']) ?></p>
                    <h4><?= rupiah($i['harga']) ?></h4>
                    <p>Stok: <?= e($i['stok']) ?></p>
                    <a href="detail.php?id=<?= $i['id'] ?>" class="mini-button">Detail</a>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</body>

</html>

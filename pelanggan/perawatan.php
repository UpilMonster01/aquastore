<?php
require "../config/db.php";

$jumlahKeranjang = !empty($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0;

$kategori = $_GET['kategori'] ?? '';
$cari = trim($_GET['cari'] ?? '');

$where = [];
$params = [];

if ($kategori !== '') {
    $where[] = "kategori = ?";
    $params[] = $kategori;
}

if ($cari !== '') {
    $where[] = "nama LIKE ?";
    $params[] = "%$cari%";
}

$sqlWhere = $where ? "WHERE " . implode(" AND ", $where) : "";

$stmt = $pdo->prepare("SELECT * FROM perlengkapan $sqlWhere ORDER BY id DESC");
$stmt->execute($params);
$data = $stmt->fetchAll();

$kategoriList = ['Pakan', 'Filter', 'Aerator', 'Heater', 'Obat', 'Lampu', 'Substrate', 'Dekorasi', 'Lainnya'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Perlengkapan Aquarium - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=120">
</head>

<body>

    <header class="topbar">
        <div class="brand">
            <div class="brand-icon">🐟</div>
            <div>
                <h2>AquaStore</h2>
                <small>Perlengkapan Aquarium</small>
            </div>
        </div>

        <nav class="menu">
            <a href="../index.php">Beranda</a>
            <a href="katalog.php">Katalog</a>
            <a href="perawatan.php" class="active">Perlengkapan</a>
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
            <span>AquaStore Equipment</span>
            <h2>Perlengkapan Aquarium</h2>
            <p>
                Temukan pakan, filter, aerator, obat, lampu, dan perlengkapan aquarium lainnya.
            </p>
        </div>

        <form method="GET" class="filter-box premium-filter">
            <input type="text" name="cari" placeholder="Cari perlengkapan..." value="<?= e($cari) ?>">

            <select name="kategori">
                <option value="">Semua Kategori</option>

                <?php foreach ($kategoriList as $k): ?>
                    <option value="<?= $k ?>" <?= $kategori === $k ? 'selected' : '' ?>>
                        <?= $k ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button>Filter</button>

            <a href="perawatan.php" class="reset-filter">
                Reset
            </a>
        </form>

        <div class="catalog-info">
            <h3><?= count($data) ?> perlengkapan ditemukan</h3>
            <p>Pilih kebutuhan aquarium sesuai jenis ikan dan ukuran tank kamu.</p>
        </div>

        <?php if (empty($data)): ?>

            <div class="empty-box">
                <h2>Data perlengkapan tidak ditemukan 🛠️</h2>
                <a href="perawatan.php" class="hero-button">Lihat Semua</a>
            </div>

        <?php else: ?>

            <div class="fish-grid">
                <?php foreach ($data as $p): ?>
                    <div class="fish-card equipment-product-card">
                        <div class="fish-image">
                            <?php if (!empty($p['foto'])): ?>
                                <img src="../uploads/perlengkapan/<?= e($p['foto']) ?>?v=<?= time() ?>" alt="<?= e($p['nama']) ?>">
                            <?php else: ?>
                                <span>🛠️</span>
                            <?php endif; ?>
                        </div>

                        <h3><?= e($p['nama']) ?></h3>

                        <p>
                            <?= e($p['kategori']) ?> • Stok <?= e($p['stok']) ?>
                        </p>

                        <h4><?= rupiah($p['harga']) ?></h4>

                        <form action="tambah-perlengkapan-keranjang.php" method="POST">

                            <input type="hidden" name="id" value="<?= $p['id'] ?>">

                            <button class="hero-button">
                                Tambah ke Keranjang
                            </button>

                        </form>

                        <p>
                            <?= e($p['deskripsi']) ?>
                        </p>

                        <span class="equipment-status <?= $p['status'] === 'Habis' ? 'habis' : '' ?>">
                            <?= e($p['status']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </section>

</body>

</html>
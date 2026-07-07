<?php
require "../config/db.php";

$q = trim($_GET['q'] ?? '');
$kategoriAir = trim($_GET['kategori_air'] ?? '');
$kategoriSifat = trim($_GET['kategori_sifat'] ?? '');
$kategoriJenis = trim($_GET['kategori_jenis'] ?? '');
$tingkatPerawatan = trim($_GET['tingkat_perawatan'] ?? '');
$status = trim($_GET['status'] ?? '');
$hargaMin = trim($_GET['harga_min'] ?? '');
$hargaMax = trim($_GET['harga_max'] ?? '');
$sort = trim($_GET['sort'] ?? '');

$where = ["1=1"];
$params = [];

if ($q !== '') {
    $where[] = "(
        nama LIKE ?
        OR nama_latin LIKE ?
        OR kategori_air LIKE ?
        OR kategori_sifat LIKE ?
        OR kategori_jenis LIKE ?
        OR tingkat_perawatan LIKE ?
        OR deskripsi LIKE ?
        OR tips_perawatan LIKE ?
        OR CAST(harga AS CHAR) LIKE ?
    )";

    $keyword = "%" . $q . "%";

    for ($i = 0; $i < 9; $i++) {
        $params[] = $keyword;
    }
}

if ($kategoriAir !== '') {
    $where[] = "kategori_air = ?";
    $params[] = $kategoriAir;
}

if ($kategoriSifat !== '') {
    $where[] = "kategori_sifat = ?";
    $params[] = $kategoriSifat;
}

if ($kategoriJenis !== '') {
    $where[] = "kategori_jenis = ?";
    $params[] = $kategoriJenis;
}

if ($tingkatPerawatan !== '') {
    $where[] = "tingkat_perawatan = ?";
    $params[] = $tingkatPerawatan;
}

if ($status !== '') {
    $where[] = "status = ?";
    $params[] = $status;
}

if ($hargaMin !== '' && is_numeric($hargaMin)) {
    $where[] = "harga >= ?";
    $params[] = (int) $hargaMin;
}

if ($hargaMax !== '' && is_numeric($hargaMax)) {
    $where[] = "harga <= ?";
    $params[] = (int) $hargaMax;
}

$order = "id DESC";

if ($sort === 'termurah') {
    $order = "harga ASC";
} elseif ($sort === 'termahal') {
    $order = "harga DESC";
} elseif ($sort === 'az') {
    $order = "nama ASC";
} elseif ($sort === 'za') {
    $order = "nama DESC";
} elseif ($sort === 'stok') {
    $order = "stok DESC";
}

$sql = "SELECT * FROM ikan WHERE " . implode(" AND ", $where) . " ORDER BY $order";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

$filterAktif = $q !== ''
    || $kategoriAir !== ''
    || $kategoriSifat !== ''
    || $kategoriJenis !== ''
    || $tingkatPerawatan !== ''
    || $status !== ''
    || $hargaMin !== ''
    || $hargaMax !== ''
    || $sort !== '';

function selected_filter($value, $current)
{
    return $value === $current ? 'selected' : '';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Katalog Ikan - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=410">
</head>

<body>
    <?php include "../components/navbar.php"; ?>

    <section class="popular-section">
        <div class="section-title">
            <span>Katalog AquaStore</span>
            <h2>Semua Ikan Hias</h2>
            <p>Cari ikan berdasarkan nama, jenis air, sifat, kategori, harga, dan tingkat perawatan.</p>
        </div>

        <div class="catalog-search-wrapper">
            <div class="search-hero-box">
                <h3>Temukan ikan yang paling cocok untuk aquarium kamu 🐠</h3>
                <p>
                    Contoh pencarian: <b>guppy</b>, <b>arwana</b>, <b>laut</b>, <b>predator</b>,
                    <b>hias</b>, <b>mudah</b>, atau rentang harga tertentu.
                </p>
            </div>

            <form method="GET" class="premium-search-form">
                <input
                    type="text"
                    name="q"
                    placeholder="Cari nama ikan, latin, kategori, sifat, deskripsi..."
                    value="<?= e($q) ?>"
                >

                <select name="kategori_air">
                    <option value="">Semua Air</option>
                    <option value="Tawar" <?= selected_filter('Tawar', $kategoriAir) ?>>Tawar</option>
                    <option value="Laut" <?= selected_filter('Laut', $kategoriAir) ?>>Laut</option>
                    <option value="Payau" <?= selected_filter('Payau', $kategoriAir) ?>>Payau</option>
                </select>

                <select name="kategori_sifat">
                    <option value="">Semua Sifat</option>
                    <option value="Predator" <?= selected_filter('Predator', $kategoriSifat) ?>>Predator</option>
                    <option value="Non-Predator" <?= selected_filter('Non-Predator', $kategoriSifat) ?>>Non-Predator</option>
                </select>

                <select name="kategori_jenis">
                    <option value="">Semua Jenis</option>
                    <option value="Hias" <?= selected_filter('Hias', $kategoriJenis) ?>>Hias</option>
                    <option value="Konsumsi" <?= selected_filter('Konsumsi', $kategoriJenis) ?>>Konsumsi</option>
                    <option value="Langka" <?= selected_filter('Langka', $kategoriJenis) ?>>Langka</option>
                </select>

                <select name="tingkat_perawatan">
                    <option value="">Semua Perawatan</option>
                    <option value="Mudah" <?= selected_filter('Mudah', $tingkatPerawatan) ?>>Mudah</option>
                    <option value="Sedang" <?= selected_filter('Sedang', $tingkatPerawatan) ?>>Sedang</option>
                    <option value="Sulit" <?= selected_filter('Sulit', $tingkatPerawatan) ?>>Sulit</option>
                </select>

                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="Tersedia" <?= selected_filter('Tersedia', $status) ?>>Tersedia</option>
                    <option value="Habis" <?= selected_filter('Habis', $status) ?>>Habis</option>
                    <option value="Pre-order" <?= selected_filter('Pre-order', $status) ?>>Pre-order</option>
                </select>

                <select name="sort">
                    <option value="">Terbaru</option>
                    <option value="termurah" <?= selected_filter('termurah', $sort) ?>>Harga Termurah</option>
                    <option value="termahal" <?= selected_filter('termahal', $sort) ?>>Harga Termahal</option>
                    <option value="az" <?= selected_filter('az', $sort) ?>>Nama A-Z</option>
                    <option value="za" <?= selected_filter('za', $sort) ?>>Nama Z-A</option>
                    <option value="stok" <?= selected_filter('stok', $sort) ?>>Stok Terbanyak</option>
                </select>

                <div class="price-filter-row">
                    <input
                        type="number"
                        name="harga_min"
                        placeholder="Harga minimum"
                        value="<?= e($hargaMin) ?>"
                        min="0"
                    >

                    <input
                        type="number"
                        name="harga_max"
                        placeholder="Harga maksimum"
                        value="<?= e($hargaMax) ?>"
                        min="0"
                    >
                </div>

                <div class="search-action-row">
                    <button type="submit" class="search-button">
                        Cari Ikan
                    </button>

                    <a href="katalog.php" class="reset-button">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="catalog-info">
            <div>
                <h3><?= count($data) ?> ikan ditemukan</h3>
                <p>
                    <?= $filterAktif ? 'Hasil pencarian berdasarkan filter yang kamu pilih.' : 'Menampilkan semua ikan terbaru di AquaStore.' ?>
                </p>
            </div>

            <?php if ($q !== ''): ?>
                <div class="active-keyword">
                    Keyword: <?= e($q) ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($data)): ?>
            <div class="empty-search-box">
                <h2>Ikan tidak ditemukan 🐟</h2>
                <p>Coba gunakan kata kunci lain atau hapus beberapa filter.</p>

                <a href="katalog.php" class="hero-button">
                    Lihat Semua Ikan
                </a>
            </div>
        <?php else: ?>
            <div class="fish-grid">
                <?php foreach ($data as $i): ?>
                    <?php
                    $statusClass = '';

                    if (($i['status'] ?? '') === 'Habis') {
                        $statusClass = 'habis';
                    } elseif (($i['status'] ?? '') === 'Pre-order') {
                        $statusClass = 'preorder';
                    }
                    ?>

                    <div class="fish-card">
                        <div class="fish-image">
                            <?php if (!empty($i['foto'])): ?>
                                <img src="../uploads/ikan/<?= e($i['foto']) ?>" alt="<?= e($i['nama']) ?>">
                            <?php else: ?>
                                <span><?= $i['kategori_sifat'] === 'Predator' ? '🦈' : '🐠' ?></span>
                            <?php endif; ?>
                        </div>

                        <h3><?= e($i['nama']) ?></h3>

                        <?php if (!empty($i['nama_latin'])): ?>
                            <p><i><?= e($i['nama_latin']) ?></i></p>
                        <?php endif; ?>

                        <div class="fish-badge-row">
                            <span class="fish-badge"><?= e($i['kategori_air']) ?></span>
                            <span class="fish-badge"><?= e($i['kategori_sifat']) ?></span>
                            <span class="fish-badge"><?= e($i['kategori_jenis']) ?></span>
                            <span class="fish-badge"><?= e($i['tingkat_perawatan']) ?></span>
                        </div>

                        <h4><?= rupiah($i['harga']) ?></h4>

                        <p>
                            Stok: <?= e($i['stok']) ?>
                            <?php if (!empty($i['ukuran_cm'])): ?>
                                • Ukuran: <?= e($i['ukuran_cm']) ?> cm
                            <?php endif; ?>
                        </p>

                        <span class="fish-status <?= e($statusClass) ?>">
                            <?= e($i['status']) ?>
                        </span>

                        <div class="catalog-action-row">
                            <a href="detail.php?id=<?= e($i['id']) ?>" class="mini-button">
                                Detail
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</body>

</html>
<?php
require "../config/db.php";

$q = trim($_GET['q'] ?? '');
$kategori = trim($_GET['kategori'] ?? '');
$status = trim($_GET['status'] ?? '');
$hargaMin = trim($_GET['harga_min'] ?? '');
$hargaMax = trim($_GET['harga_max'] ?? '');
$sort = trim($_GET['sort'] ?? '');

$where = ["1=1"];
$params = [];

if ($q !== '') {
    $where[] = "(
        nama LIKE ?
        OR kategori LIKE ?
        OR deskripsi LIKE ?
        OR CAST(harga AS CHAR) LIKE ?
    )";

    $keyword = "%" . $q . "%";

    for ($i = 0; $i < 4; $i++) {
        $params[] = $keyword;
    }
}

if ($kategori !== '') {
    $where[] = "kategori = ?";
    $params[] = $kategori;
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

$sql = "SELECT * FROM perlengkapan WHERE " . implode(" AND ", $where) . " ORDER BY $order";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();

$kategoriList = ['Pakan', 'Filter', 'Aerator', 'Heater', 'Obat', 'Lampu', 'Substrate', 'Dekorasi', 'Lainnya'];

$filterAktif = $q !== ''
    || $kategori !== ''
    || $status !== ''
    || $hargaMin !== ''
    || $hargaMax !== ''
    || $sort !== '';

function selected_equipment_filter($value, $current)
{
    return $value === $current ? 'selected' : '';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Perlengkapan Aquarium - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=410">
</head>

<body>

    <?php include "../components/navbar.php"; ?>

    <section class="popular-section">
        <div class="section-title">
            <span>AquaStore Equipment</span>
            <h2>Perlengkapan Aquarium</h2>
            <p>
                Cari pakan, filter, aerator, obat, lampu, dekorasi, dan perlengkapan aquarium lainnya.
            </p>
        </div>

        <div class="equipment-search-wrapper">
            <div class="equipment-hero-box">
                <h3>Cari perlengkapan aquarium lebih cepat 🛠️</h3>
                <p>
                    Contoh pencarian: <b>filter</b>, <b>aerator</b>, <b>pakan</b>, <b>lampu</b>,
                    <b>obat</b>, atau gunakan rentang harga.
                </p>
            </div>

            <form method="GET" class="equipment-search-form">
                <input
                    type="text"
                    name="q"
                    placeholder="Cari nama, kategori, deskripsi, atau harga..."
                    value="<?= e($q) ?>"
                >

                <select name="kategori">
                    <option value="">Semua Kategori</option>

                    <?php foreach ($kategoriList as $k): ?>
                        <option value="<?= e($k) ?>" <?= selected_equipment_filter($k, $kategori) ?>>
                            <?= e($k) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <select name="status">
                    <option value="">Semua Status</option>
                    <option value="Tersedia" <?= selected_equipment_filter('Tersedia', $status) ?>>Tersedia</option>
                    <option value="Habis" <?= selected_equipment_filter('Habis', $status) ?>>Habis</option>
                </select>

                <select name="sort">
                    <option value="">Terbaru</option>
                    <option value="termurah" <?= selected_equipment_filter('termurah', $sort) ?>>Harga Termurah</option>
                    <option value="termahal" <?= selected_equipment_filter('termahal', $sort) ?>>Harga Termahal</option>
                    <option value="az" <?= selected_equipment_filter('az', $sort) ?>>Nama A-Z</option>
                    <option value="za" <?= selected_equipment_filter('za', $sort) ?>>Nama Z-A</option>
                    <option value="stok" <?= selected_equipment_filter('stok', $sort) ?>>Stok Terbanyak</option>
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
                        Cari Perlengkapan
                    </button>

                    <a href="perawatan.php" class="reset-button">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="catalog-info">
            <div>
                <h3><?= count($data) ?> perlengkapan ditemukan</h3>
                <p>
                    <?= $filterAktif ? 'Hasil pencarian berdasarkan filter yang kamu pilih.' : 'Menampilkan semua perlengkapan terbaru di AquaStore.' ?>
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
                <h2>Perlengkapan tidak ditemukan 🛠️</h2>
                <p>Coba gunakan kata kunci lain atau hapus beberapa filter.</p>

                <a href="perawatan.php" class="hero-button">
                    Lihat Semua Perlengkapan
                </a>
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

                        <?php if (!empty($_SESSION['admin'])): ?>
                            <button type="button" class="admin-quick-edit-link" onclick="openModal('editPerlengkapanInline<?= (int) $p['id'] ?>')">
                                ✏️ Edit produk ini
                            </button>
                        <?php endif; ?>

                        <div class="equipment-badge-row">
                            <span class="equipment-badge"><?= e($p['kategori']) ?></span>
                            <span class="equipment-badge">Stok <?= e($p['stok']) ?></span>
                        </div>

                        <h4><?= rupiah($p['harga']) ?></h4>

                        <span class="equipment-status <?= $p['status'] === 'Habis' ? 'habis' : '' ?>">
                            <?= e($p['status']) ?>
                        </span>

                        <?php if ($p['status'] === 'Habis' || (int) $p['stok'] <= 0): ?>
                            <button class="hero-button product-action-button disabled-action-button">
                                Stok Habis
                            </button>
                        <?php else: ?>
                            <form action="tambah-perlengkapan-keranjang.php" method="POST" class="product-action-form">
                                <input type="hidden" name="id" value="<?= e($p['id']) ?>">

                                <button class="hero-button">
                                    Tambah ke Keranjang
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (!empty($p['deskripsi'])): ?>
                            <p class="product-description-text">
                                <?= e($p['deskripsi']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </section>

    <?php if (!empty($_SESSION['admin'])): ?>
        <!-- MODAL EDIT (inline di halaman publik per item, supaya admin
             tidak perlu pindah ke panel admin cuma buat edit produk ini) -->
        <?php foreach ($data as $p): ?>
            <div class="modal" id="editPerlengkapanInline<?= (int) $p['id'] ?>">
                <div class="modal-box">
                    <button class="close-btn" onclick="closeModal('editPerlengkapanInline<?= (int) $p['id'] ?>')">×</button>
                    <h2>Edit Perlengkapan</h2>

                    <form action="../proses/edit-perlengkapan.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                        <input type="hidden" name="kembali" value="<?= e($_SERVER['REQUEST_URI'] ?? '') ?>">

                        <?php
                        if (!defined('AQUASTORE_ADMIN_VIEW')) {
                            define('AQUASTORE_ADMIN_VIEW', true);
                        }
                        $item = $p;
                        include "../admin/form-perlengkapan.php";
                        ?>

                        <button class="login-button">Simpan</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <script src="../assets/js/main.js"></script>
</body>

</html>
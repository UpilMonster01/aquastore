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

    <style>
        .equipment-search-wrapper {
            width: min(1180px, 92%);
            margin: 0 auto 28px;
        }

        .equipment-hero-box {
            background: linear-gradient(135deg, #eff6ff, #f0fdf4);
            border: 1px solid rgba(14, 165, 233, .18);
            border-radius: 28px;
            padding: 24px;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .08);
            margin-bottom: 20px;
        }

        .equipment-hero-box h3 {
            margin: 0 0 8px;
            color: #0f172a;
            font-size: 24px;
        }

        .equipment-hero-box p {
            margin: 0;
            color: #475569;
            line-height: 1.6;
        }

        .equipment-search-form {
            background: #ffffff;
            border-radius: 24px;
            padding: 18px;
            box-shadow: 0 16px 45px rgba(15, 23, 42, .08);
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 12px;
            align-items: center;
        }

        .equipment-search-form input,
        .equipment-search-form select {
            width: 100%;
            border: 1px solid #dbe3ef;
            background: #f8fafc;
            border-radius: 15px;
            padding: 13px 14px;
            font-size: 14px;
            outline: none;
        }

        .equipment-search-form input:focus,
        .equipment-search-form select:focus {
            border-color: #1677ff;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(22, 119, 255, .10);
        }

        .price-filter-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            grid-column: span 2;
        }

        .search-action-row {
            display: flex;
            gap: 10px;
            grid-column: span 2;
        }

        .search-button,
        .reset-button {
            border: none;
            border-radius: 999px;
            padding: 13px 18px;
            font-weight: 900;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            display: inline-flex;
            justify-content: center;
            align-items: center;
        }

        .search-button {
            background: #1677ff;
            color: #ffffff;
            flex: 1;
        }

        .reset-button {
            background: #e2e8f0;
            color: #0f172a;
        }

        .catalog-info {
            width: min(1180px, 92%);
            margin: 0 auto 22px;
            background: #ffffff;
            border-radius: 22px;
            padding: 18px 22px;
            box-shadow: 0 12px 34px rgba(15, 23, 42, .06);
            display: flex;
            justify-content: space-between;
            gap: 16px;
            align-items: center;
        }

        .catalog-info h3 {
            margin: 0;
            color: #0f172a;
        }

        .catalog-info p {
            margin: 4px 0 0;
            color: #64748b;
        }

        .active-keyword {
            background: #eff6ff;
            color: #1d4ed8;
            padding: 8px 12px;
            border-radius: 999px;
            font-weight: 800;
            font-size: 13px;
        }

        .equipment-badge-row {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
            margin: 10px 0;
        }

        .equipment-badge {
            background: #f1f5f9;
            color: #334155;
            font-size: 12px;
            padding: 6px 9px;
            border-radius: 999px;
            font-weight: 800;
        }

        .equipment-status {
            display: inline-flex;
            margin-top: 8px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            background: #dcfce7;
            color: #15803d;
        }

        .equipment-status.habis {
            background: #fee2e2;
            color: #b91c1c;
        }

        .empty-search-box {
            width: min(720px, 92%);
            margin: 40px auto;
            text-align: center;
            background: #ffffff;
            border-radius: 26px;
            padding: 36px;
            box-shadow: 0 18px 48px rgba(15, 23, 42, .08);
        }

        .empty-search-box h2 {
            margin: 0 0 10px;
            color: #0f172a;
        }

        .empty-search-box p {
            color: #64748b;
            margin-bottom: 22px;
        }

        @media (max-width: 900px) {
            .equipment-search-form {
                grid-template-columns: 1fr 1fr;
            }

            .equipment-search-form input[name="q"] {
                grid-column: span 2;
            }

            .search-action-row,
            .price-filter-row {
                grid-column: span 2;
            }

            .catalog-info {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 560px) {
            .equipment-search-form {
                grid-template-columns: 1fr;
            }

            .equipment-search-form input[name="q"],
            .search-action-row,
            .price-filter-row {
                grid-column: span 1;
            }

            .price-filter-row {
                grid-template-columns: 1fr;
            }

            .search-action-row {
                flex-direction: column;
            }
        }
    </style>
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

                        <div class="equipment-badge-row">
                            <span class="equipment-badge"><?= e($p['kategori']) ?></span>
                            <span class="equipment-badge">Stok <?= e($p['stok']) ?></span>
                        </div>

                        <h4><?= rupiah($p['harga']) ?></h4>

                        <span class="equipment-status <?= $p['status'] === 'Habis' ? 'habis' : '' ?>">
                            <?= e($p['status']) ?>
                        </span>

                        <?php if ($p['status'] === 'Habis' || (int) $p['stok'] <= 0): ?>
                            <button class="hero-button" disabled style="opacity: .55; cursor: not-allowed; margin-top: 12px;">
                                Stok Habis
                            </button>
                        <?php else: ?>
                            <form action="tambah-perlengkapan-keranjang.php" method="POST" style="margin-top: 12px;">
                                <input type="hidden" name="id" value="<?= e($p['id']) ?>">

                                <button class="hero-button">
                                    Tambah ke Keranjang
                                </button>
                            </form>
                        <?php endif; ?>

                        <?php if (!empty($p['deskripsi'])): ?>
                            <p style="margin-top: 12px;">
                                <?= e($p['deskripsi']) ?>
                            </p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>
    </section>

</body>

</html>
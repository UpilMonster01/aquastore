<?php
require "../config/db.php";

if (empty($_SESSION['keranjang']) && empty($_SESSION['keranjang_perlengkapan'])) {
    flash('error', 'Keranjang masih kosong.');
    header("Location: katalog.php");
    exit;
}

$ikanItems = [];
$alatItems = [];
$subtotal = 0;

/* AMBIL IKAN */
if (!empty($_SESSION['keranjang'])) {
    $ids = array_keys($_SESSION['keranjang']);
    $in = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("SELECT * FROM ikan WHERE id IN ($in)");
    $stmt->execute($ids);
    $ikanItems = $stmt->fetchAll();

    foreach ($ikanItems as $i) {
        $jumlah = $_SESSION['keranjang'][$i['id']];

        if ($jumlah > $i['stok']) {
            flash('error', 'Stok ikan ' . $i['nama'] . ' tidak cukup.');
            header("Location: keranjang.php");
            exit;
        }

        $subtotal += $jumlah * $i['harga'];
    }
}

/* AMBIL PERLENGKAPAN */
if (!empty($_SESSION['keranjang_perlengkapan'])) {
    $ids = array_keys($_SESSION['keranjang_perlengkapan']);
    $in = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("SELECT * FROM perlengkapan WHERE id IN ($in)");
    $stmt->execute($ids);
    $alatItems = $stmt->fetchAll();

    foreach ($alatItems as $p) {
        $jumlah = $_SESSION['keranjang_perlengkapan'][$p['id']];

        if ($jumlah > $p['stok']) {
            flash('error', 'Stok perlengkapan ' . $p['nama'] . ' tidak cukup.');
            header("Location: keranjang.php");
            exit;
        }

        $subtotal += $jumlah * $p['harga'];
    }
}

/* PROSES CHECKOUT */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_pelanggan']);
    $hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);
    $pengiriman = $_POST['metode_pengiriman'];
    $bayar = $_POST['metode_bayar'];

    $ongkir = $pengiriman === 'Kurir' ? 15000 : 0;
    $total = $subtotal + $ongkir;
    $nomor = "AQS-" . date("Ymd") . "-" . rand(1000, 9999);

    try {
        $pdo->beginTransaction();

        $insert = $pdo->prepare("
            INSERT INTO pesanan
            (nomor_pesanan, nama_pelanggan, no_hp, alamat, metode_pengiriman, metode_bayar, total_harga, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')
        ");

        $insert->execute([
            $nomor,
            $nama,
            $hp,
            $alamat,
            $pengiriman,
            $bayar,
            $total
        ]);

        $pesananId = $pdo->lastInsertId();

        /* SIMPAN DETAIL IKAN */
        foreach ($ikanItems as $i) {
            $jumlah = $_SESSION['keranjang'][$i['id']];

            $detail = $pdo->prepare("
                INSERT INTO detail_pesanan
                (pesanan_id, ikan_id, jumlah, harga_satuan)
                VALUES (?, ?, ?, ?)
            ");

            $detail->execute([
                $pesananId,
                $i['id'],
                $jumlah,
                $i['harga']
            ]);

            $stok = $pdo->prepare("UPDATE ikan SET stok = stok - ? WHERE id = ?");
            $stok->execute([$jumlah, $i['id']]);
        }

        /* SIMPAN DETAIL PERLENGKAPAN */
        foreach ($alatItems as $p) {
            $jumlah = $_SESSION['keranjang_perlengkapan'][$p['id']];

            $detailAlat = $pdo->prepare("
                INSERT INTO detail_pesanan_perlengkapan
                (pesanan_id, perlengkapan_id, jumlah, harga_satuan)
                VALUES (?, ?, ?, ?)
            ");

            $detailAlat->execute([
                $pesananId,
                $p['id'],
                $jumlah,
                $p['harga']
            ]);

            $stokAlat = $pdo->prepare("UPDATE perlengkapan SET stok = stok - ? WHERE id = ?");
            $stokAlat->execute([$jumlah, $p['id']]);
        }

        $pdo->commit();

        unset($_SESSION['keranjang']);
        unset($_SESSION['keranjang_perlengkapan']);

        header("Location: checkout.php?sukses=" . urlencode($nomor));
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        flash('error', 'Checkout gagal.');
        header("Location: checkout.php");
        exit;
    }
}

if (!empty($_GET['sukses'])):
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout Berhasil</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<section class="popular-section">
    <div class="success-order">
        <h1>Pesanan Berhasil 🎉</h1>
        <p>Nomor pesanan kamu:</p>
        <h2><?= e($_GET['sukses']) ?></h2>

        <a href="cek-pesanan.php" class="hero-button">Cek Pesanan</a>
        <a href="katalog.php" class="mini-button">Kembali Belanja</a>
    </div>
</section>
</body>
</html>
<?php exit; endif; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Checkout - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=160">
</head>
<body>

<header class="topbar">
    <div class="brand">
        <div class="brand-icon">🐟</div>
        <div>
            <h2>AquaStore</h2>
            <small>Checkout</small>
        </div>
    </div>

    <nav class="menu">
        <a href="../index.php">Beranda</a>
        <a href="katalog.php">Katalog</a>
        <a href="perawatan.php">Perlengkapan</a>
        <a href="keranjang.php">Keranjang</a>
    </nav>
</header>

<section class="checkout-section">
    <div class="section-title">
        <span>Checkout</span>
        <h2>Lengkapi Pesanan</h2>
    </div>

    <?php show_flash(); ?>

    <div class="checkout-grid">
        <form method="POST" class="checkout-form">
            <input type="text" name="nama_pelanggan" placeholder="Nama lengkap" required>
            <input type="text" name="no_hp" placeholder="Nomor HP" required>
            <textarea name="alamat" placeholder="Alamat lengkap" required></textarea>

            <select name="metode_pengiriman" id="pengiriman" onchange="hitungTotal()">
                <option value="Ambil Sendiri">Ambil Sendiri - Gratis</option>
                <option value="Kurir">Kurir - Rp 15.000</option>
            </select>

            <select name="metode_bayar">
                <option>Transfer Bank</option>
                <option>COD</option>
                <option>QRIS</option>
            </select>

            <button class="login-button">Buat Pesanan</button>
        </form>

        <div class="summary-box">
            <h3>Ringkasan Pesanan</h3>

            <?php foreach ($ikanItems as $i): 
                $jumlah = $_SESSION['keranjang'][$i['id']];
            ?>
                <p>🐠 <?= e($i['nama']) ?> x <?= $jumlah ?> = <?= rupiah($jumlah * $i['harga']) ?></p>
            <?php endforeach; ?>

            <?php foreach ($alatItems as $p): 
                $jumlah = $_SESSION['keranjang_perlengkapan'][$p['id']];
            ?>
                <p>🛠️ <?= e($p['nama']) ?> x <?= $jumlah ?> = <?= rupiah($jumlah * $p['harga']) ?></p>
            <?php endforeach; ?>

            <hr>

            <p>Subtotal: <b id="subtotal" data-total="<?= $subtotal ?>"><?= rupiah($subtotal) ?></b></p>
            <p>Ongkir: <b id="ongkir">Rp 0</b></p>

            <h2>Total: <span id="grandTotal"><?= rupiah($subtotal) ?></span></h2>
        </div>
    </div>
</section>

<script src="../assets/js/main.js"></script>
</body>
</html>
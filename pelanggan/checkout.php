<?php
require "../config/db.php";

if (empty($_SESSION['keranjang'])) {
    flash('error', 'Keranjang kosong.');
    header("Location: katalog.php");
    exit;
}

$ids = array_keys($_SESSION['keranjang']);
$in = implode(',', array_fill(0, count($ids), '?'));
$stmt = $pdo->prepare("SELECT * FROM ikan WHERE id IN ($in)");
$stmt->execute($ids);
$items = $stmt->fetchAll();

$subtotal = 0;
foreach ($items as $i) {
    $j = $_SESSION['keranjang'][$i['id']];

    if ($j > $i['stok']) {
        flash('error', 'Stok tidak cukup.');
        header("Location: keranjang.php");
        exit;
    }

    $subtotal += $j * $i['harga'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $ongkir = $_POST['metode_pengiriman'] === 'Kurir' ? 15000 : 0;
    $total = $subtotal + $ongkir;
    $nomor = 'AQS-' . date('Ymd') . '-' . rand(1000, 9999);

    try {
        $pdo->beginTransaction();

        $ins = $pdo->prepare("INSERT INTO pesanan (nomor_pesanan,nama_pelanggan,no_hp,alamat,metode_pengiriman,metode_bayar,total_harga,status) VALUES (?,?,?,?,?,?,?,'Pending')");
        $ins->execute([
            $nomor,
            trim($_POST['nama_pelanggan']),
            trim($_POST['no_hp']),
            trim($_POST['alamat']),
            $_POST['metode_pengiriman'],
            $_POST['metode_bayar'],
            $total,
        ]);

        $pid = $pdo->lastInsertId();

        foreach ($items as $i) {
            $j = $_SESSION['keranjang'][$i['id']];
            $d = $pdo->prepare("INSERT INTO detail_pesanan (pesanan_id,ikan_id,jumlah,harga_satuan) VALUES (?,?,?,?)");
            $d->execute([$pid, $i['id'], $j, $i['harga']]);

            $u = $pdo->prepare("UPDATE ikan SET stok = stok - ? WHERE id = ?");
            $u->execute([$j, $i['id']]);
        }

        $pdo->commit();
        unset($_SESSION['keranjang']);

        header("Location: checkout.php?sukses=" . urlencode($nomor));
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        flash('error', 'Checkout gagal.');
    }
}

if (!empty($_GET['sukses'])):
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Sukses</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <section class="popular-section">
        <div class="success-order">
            <h1>Pesanan Berhasil 🎉</h1>
            <p>Nomor pesanan:</p>
            <h2><?= e($_GET['sukses']) ?></h2>
            <br>
            <a href="cek-pesanan.php" class="hero-button">Cek Pesanan</a>
        </div>
    </section>
</body>

</html>
<?php
    exit;
endif;
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Checkout</title>
    <link rel="stylesheet" href="../assets/css/style.css">
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
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <input type="text" name="nama_pelanggan" placeholder="Nama lengkap" required>
                <input type="text" name="no_hp" placeholder="No HP" required>
                <textarea name="alamat" placeholder="Alamat lengkap" required></textarea>
                <select name="metode_pengiriman" id="pengiriman" onchange="hitungTotal()">
                    <option value="Ambil Sendiri">Ambil Sendiri - Gratis</option>
                    <option value="Kurir">Kurir - Rp 15.000</option>
                </select>
                <select name="metode_bayar">
                    <option value="Transfer Bank">Transfer Bank</option>
                    <option value="COD">COD</option>
                    <option value="QRIS">QRIS</option>
                </select>
                <button class="login-button">Buat Pesanan</button>
            </form>

            <div class="summary-box">
                <h3>Ringkasan Pesanan</h3>

                <?php foreach ($items as $i):
                    $j = $_SESSION['keranjang'][$i['id']];
                ?>
                    <p><?= e($i['nama']) ?> x <?= $j ?> = <?= rupiah($j * $i['harga']) ?></p>
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

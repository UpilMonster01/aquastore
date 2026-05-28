<?php
require "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (isset($_POST['update'])) {
        foreach ($_POST['jumlah'] as $id => $j) {
            $_SESSION['keranjang'][(int)$id] = max(1, (int)$j);
        }
    }

    if (isset($_POST['hapus'])) {
        unset($_SESSION['keranjang'][(int)$_POST['id']]);
    }

    flash('success', 'Keranjang diperbarui.');
    header("Location: keranjang.php");
    exit;
}

$items = [];
$total = 0;

if (!empty($_SESSION['keranjang'])) {
    $ids = array_keys($_SESSION['keranjang']);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM ikan WHERE id IN ($in)");
    $stmt->execute($ids);
    $items = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Keranjang</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <header class="topbar">
        <div class="brand">
            <div class="brand-icon">🐟</div>
            <div>
                <h2>AquaStore</h2>
                <small>Keranjang</small>
            </div>
        </div>
        <nav class="menu">
            <a href="../index.php">Beranda</a>
            <a href="katalog.php">Katalog</a>
            <a href="checkout.php">Checkout</a>
        </nav>
    </header>

    <section class="popular-section">
        <div class="section-title">
            <span>Belanja</span>
            <h2>Keranjang Kamu</h2>
        </div>

        <?php show_flash(); ?>

        <?php if (empty($items)): ?>
            <div class="empty-box">
                <h2>Keranjang masih kosong 🐠</h2>
                <br>
                <a href="katalog.php" class="hero-button">Belanja Sekarang</a>
            </div>
        <?php else: ?>
            <form method="POST">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                <input type="hidden" name="id" id="hapusId">

                <div class="table-box">
                    <table>
                        <tr>
                            <th>Ikan</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                        <?php foreach ($items as $i): ?>
                            <?php
                                $j = $_SESSION['keranjang'][$i['id']];
                                if ($j > $i['stok']) {
                                    $j = $i['stok'];
                                }
                                $sub = $j * $i['harga'];
                                $total += $sub;
                            ?>
                            <tr>
                                <td><?= e($i['nama']) ?></td>
                                <td><?= rupiah($i['harga']) ?></td>
                                <td><?= e($i['stok']) ?></td>
                                <td>
                                    <input class="qty-input" type="number" name="jumlah[<?= $i['id'] ?>]" value="<?= $j ?>" min="1" max="<?= e($i['stok']) ?>">
                                </td>
                                <td><?= rupiah($sub) ?></td>
                                <td>
                                    <button class="delete-button" name="hapus" onclick="document.getElementById('hapusId').value='<?= $i['id'] ?>'">Hapus</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>

                <div class="cart-total">
                    <h2>Total: <?= rupiah($total) ?></h2>
                    <button name="update" class="mini-button">Update</button>
                    <a href="checkout.php" class="hero-button">Checkout</a>
                </div>
            </form>
        <?php endif; ?>
    </section>
</body>

</html>

<?php
require "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_check();

    if (isset($_POST['hapus_ikan'])) {
        $id = (int)$_POST['id'];
        unset($_SESSION['keranjang'][$id]);

        flash('success', 'Ikan dihapus dari keranjang.');
        header("Location: keranjang.php");
        exit;
    }

    if (isset($_POST['hapus_perlengkapan'])) {
        $id = (int)$_POST['id'];
        unset($_SESSION['keranjang_perlengkapan'][$id]);

        flash('success', 'Perlengkapan dihapus dari keranjang.');
        header("Location: keranjang.php");
        exit;
    }

    if (isset($_POST['update'])) {
        if (!empty($_POST['jumlah_ikan'])) {
            foreach ($_POST['jumlah_ikan'] as $id => $jumlah) {
                $_SESSION['keranjang'][(int)$id] = max(1, (int)$jumlah);
            }
        }

        if (!empty($_POST['jumlah_perlengkapan'])) {
            foreach ($_POST['jumlah_perlengkapan'] as $id => $jumlah) {
                $_SESSION['keranjang_perlengkapan'][(int)$id] = max(1, (int)$jumlah);
            }
        }

        flash('success', 'Keranjang diperbarui.');
        header("Location: keranjang.php");
        exit;
    }
}

$ikanItems = [];
$alatItems = [];
$total = 0;

if (!empty($_SESSION['keranjang'])) {
    $ids = array_keys($_SESSION['keranjang']);
    $in = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("SELECT * FROM ikan WHERE id IN ($in)");
    $stmt->execute($ids);
    $ikanItems = $stmt->fetchAll();
}

if (!empty($_SESSION['keranjang_perlengkapan'])) {
    $ids = array_keys($_SESSION['keranjang_perlengkapan']);
    $in = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("SELECT * FROM perlengkapan WHERE id IN ($in)");
    $stmt->execute($ids);
    $alatItems = $stmt->fetchAll();
}

$jumlahKeranjang =
    (!empty($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0) +
    (!empty($_SESSION['keranjang_perlengkapan']) ? count($_SESSION['keranjang_perlengkapan']) : 0);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Keranjang - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=150">
</head>
<body>

<?php include "../components/navbar.php"; ?>

<section class="checkout-section">

    <div class="section-title">
        <span>AquaStore Cart</span>
        <h2>Keranjang Belanja</h2>
    </div>

    <?php show_flash(); ?>

    <?php if (empty($ikanItems) && empty($alatItems)): ?>

        <div class="empty-box">
            <h2>Keranjang masih kosong 🐠</h2>
            <a href="katalog.php" class="hero-button">Belanja Ikan</a>
            <a href="perawatan.php" class="mini-button">Belanja Perlengkapan</a>
        </div>

    <?php else: ?>

    <form method="POST">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" id="hapusId">

        <?php if (!empty($ikanItems)): ?>
            <div class="admin-panel">
                <h2>🐠 Ikan Hias</h2>

                <div class="table-box">
                    <table>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>

                        <?php foreach ($ikanItems as $i): 
                            $jumlah = $_SESSION['keranjang'][$i['id']];
                            if ($jumlah > $i['stok']) {
                                $jumlah = $i['stok'];
                            }

                            $subtotal = $jumlah * $i['harga'];
                            $total += $subtotal;
                        ?>
                            <tr>
                                <td><?= e($i['nama']) ?></td>
                                <td><?= rupiah($i['harga']) ?></td>
                                <td><?= e($i['stok']) ?></td>
                                <td>
                                    <input 
                                        class="qty-input" 
                                        type="number" 
                                        name="jumlah_ikan[<?= $i['id'] ?>]" 
                                        value="<?= $jumlah ?>" 
                                        min="1" 
                                        max="<?= e($i['stok']) ?>"
                                    >
                                </td>
                                <td><?= rupiah($subtotal) ?></td>
                                <td>
                                    <button 
                                        class="delete-button" 
                                        name="hapus_ikan" 
                                        onclick="document.getElementById('hapusId').value='<?= $i['id'] ?>'"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    </table>
                </div>
            </div>
        <?php endif; ?>


        <?php if (!empty($alatItems)): ?>
            <div class="admin-panel">
                <h2>🛠️ Perlengkapan Aquarium</h2>

                <div class="table-box">
                    <table>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>

                        <?php foreach ($alatItems as $p): 
                            $jumlah = $_SESSION['keranjang_perlengkapan'][$p['id']];
                            if ($jumlah > $p['stok']) {
                                $jumlah = $p['stok'];
                            }

                            $subtotal = $jumlah * $p['harga'];
                            $total += $subtotal;
                        ?>
                            <tr>
                                <td><?= e($p['nama']) ?></td>
                                <td><?= rupiah($p['harga']) ?></td>
                                <td><?= e($p['stok']) ?></td>
                                <td>
                                    <input 
                                        class="qty-input" 
                                        type="number" 
                                        name="jumlah_perlengkapan[<?= $p['id'] ?>]" 
                                        value="<?= $jumlah ?>" 
                                        min="1" 
                                        max="<?= e($p['stok']) ?>"
                                    >
                                </td>
                                <td><?= rupiah($subtotal) ?></td>
                                <td>
                                    <button 
                                        class="delete-button" 
                                        name="hapus_perlengkapan" 
                                        onclick="document.getElementById('hapusId').value='<?= $p['id'] ?>'"
                                    >
                                        Hapus
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    </table>
                </div>
            </div>
        <?php endif; ?>

        <div class="cart-total">
            <h2>Total: <?= rupiah($total) ?></h2>

            <button name="update" class="mini-button">
                Update Keranjang
            </button>

            <a href="checkout.php" class="hero-button">
                Checkout
            </a>
        </div>

    </form>

    <?php endif; ?>

</section>

</body>
</html>
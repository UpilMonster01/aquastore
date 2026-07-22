<?php
require "../config/db.php";

$id = (int) ($_GET['id'] ?? 0);

$stmt = $pdo->prepare("SELECT * FROM ikan WHERE id = ?");
$stmt->execute([$id]);
$ikan = $stmt->fetch();

if (!$ikan) {
    die("Ikan tidak ditemukan.");
}

$jumlahKeranjang =
    (!empty($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0) +
    (!empty($_SESSION['keranjang_perlengkapan']) ? count($_SESSION['keranjang_perlengkapan']) : 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $j = max(1, (int) ($_POST['jumlah'] ?? 1));

    if ($ikan['stok'] <= 0) {
        flash('error', 'Stok ikan habis.');
    } elseif ($j > $ikan['stok']) {
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
    <title><?= e($ikan['nama']) ?> - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=55">
</head>

<?php include "../components/navbar.php"; ?>

    <section class="detail-section">
        <?php show_flash(); ?>

        <div class="detail-wrapper">
            <div class="detail-photo">
                <?php if (!empty($ikan['foto'])): ?>
                    <img src="../uploads/ikan/<?= e($ikan['foto']) ?>?v=<?= time() ?>">
                <?php else: ?>
                    <span><?= $ikan['kategori_sifat'] === 'Predator' ? '🦈' : '🐠' ?></span>
                <?php endif; ?>
            </div>

            <div class="detail-info">
                <span class="detail-badge">
                    <?= e($ikan['kategori_air']) ?> • <?= e($ikan['kategori_sifat']) ?>
                </span>

                <?php if (!empty($_SESSION['admin'])): ?>
                    <button type="button" class="admin-quick-edit-link" onclick="openModal('editIkanInline')">
                        ✏️ Edit produk ini
                    </button>
                <?php endif; ?>

                <h1><?= e($ikan['nama']) ?></h1>

                <p>
                    <i><?= e($ikan['nama_latin']) ?></i>
                </p>

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
                        <input 
                            type="number" 
                            name="jumlah" 
                            value="1" 
                            min="1" 
                            max="<?= e($ikan['stok']) ?>"
                            <?= $ikan['stok'] <= 0 ? 'disabled' : '' ?>
                        >
                    </label>

                    <button 
                        class="hero-button"
                        <?= $ikan['stok'] <= 0 ? 'disabled' : '' ?>
                    >
                        <?= $ikan['stok'] <= 0 ? 'Stok Habis' : 'Tambah ke Keranjang' ?>
                    </button>
                </form>

                <br>

                <a href="katalog.php" class="mini-button">
                    ← Kembali ke Katalog
                </a>
            </div>
        </div>
    </section>

    <?php if (!empty($_SESSION['admin'])): ?>
        <!-- MODAL EDIT (inline di halaman publik, supaya admin tidak perlu
             pindah ke panel admin cuma buat edit produk ini) -->
        <div class="modal" id="editIkanInline">
            <div class="modal-box">
                <button class="close-btn" onclick="closeModal('editIkanInline')">×</button>
                <h2>Edit Ikan</h2>

                <form action="../proses/edit-ikan.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int) $ikan['id'] ?>">
                    <input type="hidden" name="kembali" value="<?= e($_SERVER['REQUEST_URI'] ?? '') ?>">

                    <?php
                    define('AQUASTORE_ADMIN_VIEW', true);
                    $i = $ikan;
                    include "../admin/form-ikan.php";
                    ?>

                    <button class="login-button">Simpan Perubahan</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script src="../assets/js/main.js"></script>
</body>

</html>
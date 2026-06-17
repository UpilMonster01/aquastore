<?php
require "../config/db.php";

$pesanan = null;
$detailIkan = [];
$detailAlat = [];

if (!empty($_GET['nomor'])) {
    $nomor = trim($_GET['nomor']);

    $stmt = $pdo->prepare("SELECT * FROM pesanan WHERE nomor_pesanan = ?");
    $stmt->execute([$nomor]);
    $pesanan = $stmt->fetch();

    if ($pesanan) {
        $dIkan = $pdo->prepare("
            SELECT dp.*, i.nama, i.foto
            FROM detail_pesanan dp
            JOIN ikan i ON dp.ikan_id = i.id
            WHERE dp.pesanan_id = ?
        ");
        $dIkan->execute([$pesanan['id']]);
        $detailIkan = $dIkan->fetchAll();

        $dAlat = $pdo->prepare("
            SELECT dpp.*, p.nama, p.foto
            FROM detail_pesanan_perlengkapan dpp
            JOIN perlengkapan p ON dpp.perlengkapan_id = p.id
            WHERE dpp.pesanan_id = ?
        ");
        $dAlat->execute([$pesanan['id']]);
        $detailAlat = $dAlat->fetchAll();
    }
}

$jumlahKeranjang =
    (!empty($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0) +
    (!empty($_SESSION['keranjang_perlengkapan']) ? count($_SESSION['keranjang_perlengkapan']) : 0);

$statusList = ['Pending', 'Diproses', 'Dikirim', 'Selesai'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cek Pesanan - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=200">
</head>
<body>

<?php include "../components/navbar.php"; ?>

<section class="checkout-section tracking-section">
    <div class="section-title">
        <span>AquaStore Tracking</span>
        <h2>Cek Status Pesanan</h2>
        <p>Masukkan nomor pesanan untuk melihat status dan detail pembelian kamu.</p>
    </div>

    <form method="GET" class="tracking-form">
        <input 
            type="text" 
            name="nomor" 
            placeholder="Contoh: AQS-20260529-1234"
            value="<?= e($_GET['nomor'] ?? '') ?>"
            required
        >

        <button>
            🔍 Cek Pesanan
        </button>
    </form>

    <?php if (!empty($_GET['nomor']) && !$pesanan): ?>
        <div class="tracking-empty">
            <div>❌</div>
            <h2>Pesanan tidak ditemukan</h2>
            <p>Pastikan nomor pesanan sudah benar.</p>
        </div>
    <?php endif; ?>

    <?php if ($pesanan): ?>
        <div class="tracking-card">
            <div class="tracking-header">
                <div>
                    <span>Nomor Pesanan</span>
                    <h2><?= e($pesanan['nomor_pesanan']) ?></h2>
                </div>

                <div class="status-badge status-<?= strtolower($pesanan['status']) ?>">
                    <?= e($pesanan['status']) ?>
                </div>
            </div>

            <div class="tracking-timeline">
                <?php foreach ($statusList as $s): 
                    $active = array_search($s, $statusList) <= array_search($pesanan['status'], $statusList);
                ?>
                    <div class="timeline-step <?= $active ? 'active' : '' ?>">
                        <div class="dot"></div>
                        <span><?= $s ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="tracking-info-grid">
                <div>
                    <b>Nama Pelanggan</b>
                    <span><?= e($pesanan['nama_pelanggan']) ?></span>
                </div>

                <div>
                    <b>No HP</b>
                    <span><?= e($pesanan['no_hp']) ?></span>
                </div>

                <div>
                    <b>Pengiriman</b>
                    <span><?= e($pesanan['metode_pengiriman']) ?></span>
                </div>

                <div>
                    <b>Pembayaran</b>
                    <span><?= e($pesanan['metode_bayar']) ?></span>
                </div>

                <div class="wide">
                    <b>Alamat</b>
                    <span><?= e($pesanan['alamat']) ?></span>
                </div>

                <div>
                    <b>Total</b>
                    <span class="total-price"><?= rupiah($pesanan['total_harga']) ?></span>
                </div>
            </div>

            <?php if (!empty($detailIkan)): ?>
                <h3 class="tracking-subtitle">🐠 Ikan Hias</h3>

                <div class="tracking-items">
                    <?php foreach ($detailIkan as $item): ?>
                        <div class="tracking-item">
                            <div class="tracking-img">
                                <?php if (!empty($item['foto'])): ?>
                                    <img src="../uploads/ikan/<?= e($item['foto']) ?>?v=<?= time() ?>">
                                <?php else: ?>
                                    <span>🐠</span>
                                <?php endif; ?>
                            </div>

                            <div>
                                <h4><?= e($item['nama']) ?></h4>
                                <p>Jumlah: <?= e($item['jumlah']) ?></p>
                            </div>

                            <b><?= rupiah($item['harga_satuan']) ?></b>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($detailAlat)): ?>
                <h3 class="tracking-subtitle">🛠️ Perlengkapan</h3>

                <div class="tracking-items">
                    <?php foreach ($detailAlat as $item): ?>
                        <div class="tracking-item">
                            <div class="tracking-img">
                                <?php if (!empty($item['foto'])): ?>
                                    <img src="../uploads/perlengkapan/<?= e($item['foto']) ?>?v=<?= time() ?>">
                                <?php else: ?>
                                    <span>🛠️</span>
                                <?php endif; ?>
                            </div>

                            <div>
                                <h4><?= e($item['nama']) ?></h4>
                                <p>Jumlah: <?= e($item['jumlah']) ?></p>
                            </div>

                            <b><?= rupiah($item['harga_satuan']) ?></b>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

</body>
</html>
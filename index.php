<?php
require "config/db.php";

$ikanPopuler = $pdo->query("SELECT * FROM ikan ORDER BY id DESC LIMIT 6")->fetchAll();

$totalJenisIkan = (int) $pdo->query("SELECT COUNT(*) FROM ikan WHERE status = 'Tersedia'")->fetchColumn();
$totalPesananSelesai = (int) $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'Selesai'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>AquaStore</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php include "components/navbar.php"; ?>

<!-- ===== HERO ===== -->
<section class="ocean-hero">
    <div class="hero-content-ocean">
        <span class="hero-label">TOKO IKAN HIAS & PERLENGKAPAN</span>
        <h1>Bawa pulang<br>keindahan air jernih</h1>
        <p>Ikan hias sehat, perlengkapan aquascape lengkap, dan tips perawatan — semua dalam satu tempat.</p>
        <div class="hero-actions-ocean">
            <a class="hero-button" href="pelanggan/katalog.php">Lihat Katalog Ikan</a>
            <a class="outline-button" href="pelanggan/perawatan.php">Perlengkapan Aquascape</a>
        </div>
    </div>
    <div class="hero-fish-box">
        <img src="assets/img/hero.jpg">
    </div>
</section>

<!-- ===== STRIP KEPERCAYAAN ===== -->
<section class="trust-strip-wrap">
    <div class="trust-strip">
        <div>
            <b><?= $totalJenisIkan ?>+</b>
            <span>Jenis ikan tersedia</span>
        </div>
        <div>
            <b><?= $totalPesananSelesai ?>+</b>
            <span>Pesanan selesai dikirim</span>
        </div>
        <div>
            <b>Aman</b>
            <span>Dikemas untuk perjalanan jauh</span>
        </div>
        <div>
            <b>Gratis</b>
            <span>Konsultasi perawatan ikan</span>
        </div>
    </div>
</section>

<!-- ===== KATEGORI PILIHAN ===== -->
<section class="cat-section">
    <div class="section-title-home">
        <span>JELAJAHI</span>
        <h2>Kategori pilihan</h2>
    </div>
    <div class="cat-carousel">
        <a class="cat-item" href="pelanggan/katalog.php?kategori_air=Tawar">
            <span class="ic">🐟</span><b>Air Tawar</b>
        </a>
        <a class="cat-item" href="pelanggan/katalog.php?kategori_air=Laut">
            <span class="ic">🐠</span><b>Air Laut</b>
        </a>
        <a class="cat-item" href="pelanggan/katalog.php?kategori_sifat=Predator">
            <span class="ic">🦈</span><b>Predator</b>
        </a>
        <a class="cat-item" href="pelanggan/perawatan.php">
            <span class="ic">🌿</span><b>Perlengkapan</b>
        </a>
        <a class="cat-item" href="pelanggan/perawatan.php?kategori=Obat">
            <span class="ic">💊</span><b>Obat Ikan</b>
        </a>
        <a class="cat-item" href="pelanggan/katalog.php">
            <span class="ic">🔍</span><b>Semua Ikan</b>
        </a>
    </div>
</section>

<!-- ===== PRODUK UNGGULAN ===== -->
<section class="ocean-section">
    <div class="section-title-home">
        <span>TERBARU</span>
        <h2>Produk unggulan minggu ini</h2>
    </div>
    <div class="ocean-product-grid">
        <?php foreach ($ikanPopuler as $i): ?>
            <div class="ocean-product-card">
                <div class="product-image-ocean">
                    <?php if ($i['foto']): ?>
                        <img src="uploads/ikan/<?= e($i['foto']) ?>">
                    <?php else: ?>
                        🐠
                    <?php endif; ?>
                </div>
                <h3><?= e($i['nama']) ?></h3>
                <strong><?= rupiah($i['harga']) ?></strong>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- ===== CTA PENUTUP ===== -->
<section class="cta-section">
    <div class="cta-box">
        <h2>Siap membangun tangki impianmu?</h2>
        <p>Jelajahi ratusan ikan hias dan perlengkapan aquascape pilihan di AquaStore.</p>
        <a class="cta-button" href="pelanggan/katalog.php">Lihat katalog sekarang</a>
    </div>
</section>

<footer class="ocean-footer">AquaStore Premium Aquarium</footer>
</body>
</html>

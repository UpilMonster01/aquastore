<?php require "config/db.php";
$jumlahKeranjang = !empty($_SESSION['keranjang']) ? array_sum($_SESSION['keranjang']) : 0;
$ikanPopuler = $pdo->query("SELECT * FROM ikan ORDER BY id DESC LIMIT 4")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>AquaStore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
</head>

<body>
    <header class="topbar">
        <div class="brand">
            <div class="brand-icon">🐟</div>
            <div>
                <h2>AquaStore</h2><small>Everything for your aquarium</small>
            </div>
        </div>
        <nav class="menu"><a href="index.php" class="active">Beranda</a><a href="pelanggan/katalog.php">Katalog</a><a
                href="pelanggan/perawatan.php">Perawatan</a><a href="pelanggan/cek-pesanan.php">Cek Pesanan</a></nav><a
            href="pelanggan/keranjang.php"
            class="cart">🛒<?php if ($jumlahKeranjang > 0): ?><span><?= $jumlahKeranjang ?></span><?php endif; ?></a>
    </header>
    <section class="hero-home">
        <div class="hero-content">
            <h3>Selamat Datang di</h3>
            <h1>Aqua<span>Store</span></h1>
            <p>Tempat terbaik untuk menemukan ikan hias berkualitas, sehat, dan siap mempercantik akuarium Anda.</p>
            <div class="hero-benefits">
                <div><b>🐠</b><span>Ikan Sehat<br>Berkualitas</span></div>
                <div><b>🛡️</b><span>Aman<br>Terpercaya</span></div>
                <div><b>🚚</b><span>Pengiriman<br>Cepat Aman</span></div>
            </div><a href="pelanggan/katalog.php" class="hero-button">🐟 Lihat Katalog →</a>
            <div class="category-home"><a href="pelanggan/katalog.php?kategori_air=Tawar">🐟 Air Tawar</a><a
                    href="pelanggan/katalog.php?kategori_air=Laut">🌊 Air Laut</a><a
                    href="pelanggan/katalog.php?kategori_sifat=Predator">🦈 Predator</a><a
                    href="pelanggan/katalog.php?kategori_sifat=Non-Predator">🐠 Non-Predator</a></div>
        </div>
        <div class="glass-cards">
            <div class="glass-card">
                <div class="card-icon">🐠</div>
                <div>
                    <h3>Beragam Ikan</h3>
                    <p>Laut, tawar, payau, predator, dan non-predator.</p>
                </div>
            </div>
            <div class="glass-card">
                <div class="card-icon">💙</div>
                <div>
                    <h3>Perawatan Mudah</h3>
                    <p>Panduan praktis untuk pemula hingga profesional.</p>
                </div>
            </div>
            <div class="glass-card">
                <div class="card-icon">⭐</div>
                <div>
                    <h3>Kualitas Terjamin</h3>
                    <p>Ikan sehat, aktif, dan sudah diseleksi.</p>
                </div>
            </div>
            <div class="glass-card">
                <div class="card-icon">🚚</div>
                <div>
                    <h3>Pengiriman Aman</h3>
                    <p>Packing rapi dan aman sampai tujuan.</p>
                </div>
            </div>
        </div>
    </section>
    <section class="popular-section">
        <div class="section-title"><span>Koleksi Terbaru</span>
            <h2>Ikan Hias Populer</h2>
        </div>
        <div class="fish-grid"><?php foreach ($ikanPopuler as $i): ?>
                <div class="fish-card">
                    <div class="fish-image"><?php if ($i['foto']): ?><img
                                src="uploads/ikan/<?= e($i['foto']) ?>"><?php else: ?><span><?= $i['kategori_sifat'] === 'Predator' ? '🦈' : '🐠' ?></span><?php endif; ?>
                    </div>
                    <h3><?= e($i['nama']) ?></h3>
                    <p><?= e($i['kategori_air']) ?> • <?= e($i['kategori_sifat']) ?></p>
                    <h4><?= rupiah($i['harga']) ?></h4><a href="pelanggan/detail.php?id=<?= $i['id'] ?>"
                        class="mini-button">Detail</a>
                </div><?php endforeach; ?>
        </div>
    </section>
    <section class="about-section"><span>Tentang Kami</span>
        <h2>AquaStore Anime Aquarium Shop</h2>
        <p>AquaStore menyediakan ikan hias berkualitas dengan tampilan website modern, fresh, dan anime-style.</p>
    </section>
    <footer class="footer">
        <h2>AquaStore</h2>
        <p>Modern Aquarium Store © <?= date('Y') ?></p><button class="admin-secret"
            onclick="openLogin()">Admin</button>
    </footer>
    <div class="login-modal" id="loginModal">
        <form action="proses/login.php" method="POST" class="login-box"><button type="button" class="close-btn"
                onclick="closeLogin()">×</button>
            <h2>Login Admin</h2><?php show_flash(); ?><input type="hidden" name="csrf"
                value="<?= csrf_token(); ?>"><input type="text" name="username" placeholder="Username" required><input
                type="password" name="password" placeholder="Password" required><button class="login-button"
                type="submit">Masuk</button>
            <p>Default: admin / admin123</p>
        </form>
    </div>
    <script src="assets/js/main.js"></script>
</body>

</html>
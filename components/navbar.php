<header class="topbar">
    <div class="brand">
        <div class="brand-icon">🐟</div>
        <div>
            <h2>AquaStore</h2>
            <small>Toko Ikan Hias & Perlengkapan</small>
        </div>
    </div>

    <nav class="menu">
        <a href="/aquastore/index.php">Beranda</a>
        <a href="/aquastore/pelanggan/katalog.php">Katalog</a>
        <a href="/aquastore/pelanggan/perawatan.php">Perlengkapan</a>
        <a href="/aquastore/pelanggan/cek-pesanan.php">Cek Pesanan</a>

        <?php if (!empty($_SESSION['user'])): ?>
            <a href="/aquastore/pelanggan/logout.php">
                👤 <?= e($_SESSION['user']['nama']) ?>
            </a>

            <a href="/aquastore/pelanggan/logout.php">
                Logout
            </a>
        <?php else: ?>
            <a href="/aquastore/pelanggan/login.php">
                Login
            </a>

            <a href="/aquastore/pelanggan/register.php">
                Daftar
            </a>
        <?php endif; ?>
    </nav>

    <?php
    $jumlahKeranjang =
        (!empty($_SESSION['keranjang']) ? count($_SESSION['keranjang']) : 0) +
        (!empty($_SESSION['keranjang_perlengkapan']) ? count($_SESSION['keranjang_perlengkapan']) : 0);
    ?>

    <a href="/aquastore/pelanggan/keranjang.php" class="cart">
        🛒

        <?php if ($jumlahKeranjang > 0): ?>
            <span><?= $jumlahKeranjang ?></span>
        <?php endif; ?>
    </a>
</header>
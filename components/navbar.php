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
    </nav>

    <div class="header-actions">

        <?php if (!empty($_SESSION['user'])): ?>

            <div class="account-menu">
                <button class="account-pill" onclick="toggleAccountMenu()" type="button">
                    <span class="account-avatar">
                        <?= strtoupper(substr($_SESSION['user']['nama'], 0, 1)) ?>
                    </span>

                    <span class="account-name">
                        <?= e($_SESSION['user']['nama']) ?>
                    </span>

                    <span class="account-arrow">▾</span>
                </button>

                <div class="account-dropdown" id="accountDropdown">
                    <a href="/aquastore/pelanggan/profil.php">Profil Saya</a>
                    <a href="/aquastore/pelanggan/pesanan-saya.php">Pesanan Saya</a>
                    <a href="/aquastore/pelanggan/logout.php" class="danger-link">Logout</a>
                </div>
            </div>

        <?php else: ?>

            <button class="account-open-btn" onclick="openAuthDrawer('login')" type="button">
                <span>👤</span>
                <b>Akun</b>
            </button>

        <?php endif; ?>

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
    </div>
</header>

<?php if (empty($_SESSION['user'])): ?>

<div class="auth-overlay" id="authOverlay" onclick="closeAuthDrawer()"></div>

<aside class="auth-drawer" id="authDrawer">
    <button class="auth-close" onclick="closeAuthDrawer()" type="button">
        ×
    </button>

    <div class="auth-drawer-header">
        <div class="auth-logo">🐟</div>
        <h2>AquaStore Account</h2>
        <p>Masuk untuk checkout lebih cepat dan melihat status pesanan.</p>
    </div>

    <div class="auth-tabs">
        <button type="button" id="loginTab" class="active" onclick="showAuthTab('login')">
            Masuk
        </button>

        <button type="button" id="registerTab" onclick="showAuthTab('register')">
            Daftar
        </button>
    </div>

    <div class="auth-panel active" id="loginPanel">
        <form action="/aquastore/proses/login-user.php" method="POST">
            <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/aquastore/index.php') ?>">

            <label>Email</label>
            <input type="email" name="email" placeholder="email@example.com" required>

            <label>Password</label>
            <input type="password" name="password" placeholder="Password akun" required>

            <button class="auth-submit" type="submit">
                Masuk Sekarang
            </button>
        </form>

        <p class="auth-switch-text">
            Belum punya akun?
            <button type="button" onclick="showAuthTab('register')">Daftar di sini</button>
        </p>
    </div>

    <div class="auth-panel" id="registerPanel">
        <form action="/aquastore/proses/register-user.php" method="POST">
            <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? '/aquastore/index.php') ?>">

            <label>Nama Lengkap</label>
            <input type="text" name="nama" placeholder="Nama lengkap" required>

            <label>Email</label>
            <input type="email" name="email" placeholder="email@example.com" required>

            <label>No HP</label>
            <input type="text" name="no_hp" placeholder="Nomor WhatsApp">

            <label>Alamat</label>
            <textarea name="alamat" placeholder="Alamat lengkap"></textarea>

            <label>Password</label>
            <input type="password" name="password" placeholder="Minimal 6 karakter" required>

            <button class="auth-submit" type="submit">
                Buat Akun
            </button>
        </form>

        <p class="auth-switch-text">
            Sudah punya akun?
            <button type="button" onclick="showAuthTab('login')">Masuk di sini</button>
        </p>
    </div>
</aside>

<?php endif; ?>
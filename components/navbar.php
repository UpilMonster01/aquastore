<?php
$baseUrl = '/aquastore';
$currentFile = basename($_SERVER['PHP_SELF'] ?? 'index.php');

function nav_active($files)
{
    global $currentFile;

    if (!is_array($files)) {
        $files = [$files];
    }

    return in_array($currentFile, $files, true) ? 'active' : '';
}

$jumlahIkan = !empty($_SESSION['keranjang']) ? array_sum($_SESSION['keranjang']) : 0;
$jumlahPerlengkapan = !empty($_SESSION['keranjang_perlengkapan']) ? array_sum($_SESSION['keranjang_perlengkapan']) : 0;
$jumlahKeranjang = $jumlahIkan + $jumlahPerlengkapan;
?>

<header class="topbar">
    <div class="brand">
        <div class="brand-icon">🐟</div>
        <div>
            <h2>AquaStore</h2>
            <small>Toko Ikan Hias & Perlengkapan</small>
        </div>
    </div>

    <nav class="menu">
        <a href="<?= $baseUrl ?>/index.php" class="<?= nav_active('index.php') ?>">
            Beranda
        </a>

        <a href="<?= $baseUrl ?>/pelanggan/katalog.php" class="<?= nav_active(['katalog.php', 'detail.php']) ?>">
            Katalog
        </a>

        <a href="<?= $baseUrl ?>/pelanggan/perawatan.php" class="<?= nav_active('perawatan.php') ?>">
            Perlengkapan
        </a>

        <a href="<?= $baseUrl ?>/pelanggan/cek-pesanan.php" class="<?= nav_active('cek-pesanan.php') ?>">
            Cek Pesanan
        </a>
    </nav>

    <div class="header-actions">
        <?php if (!empty($_SESSION['user'])): ?>

            <div class="account-menu">
                <button class="account-pill" onclick="toggleAccountMenu(event)" type="button">
                    <span class="account-avatar">
                        <?= e(strtoupper(substr($_SESSION['user']['nama'] ?? 'U', 0, 1))) ?>
                    </span>

                    <span class="account-name">
                        <?= e($_SESSION['user']['nama'] ?? 'User') ?>
                    </span>

                    <span class="account-arrow">▾</span>
                </button>

                <div class="account-dropdown" id="accountDropdown">
                    <a href="<?= $baseUrl ?>/pelanggan/profil.php" class="<?= nav_active('profil.php') ?>">
                        Profil Saya
                    </a>

                    <a href="<?= $baseUrl ?>/pelanggan/pesanan-saya.php" class="<?= nav_active('pesanan-saya.php') ?>">
                        Pesanan Saya
                    </a>

                    <a href="<?= $baseUrl ?>/pelanggan/logout.php" class="danger-link">
                        Logout
                    </a>
                </div>
            </div>

        <?php else: ?>

            <button class="account-open-btn" onclick="openAuthDrawer('login')" type="button">
                <span>👤</span>
                <b>Akun</b>
            </button>

        <?php endif; ?>

        <a href="<?= $baseUrl ?>/pelanggan/keranjang.php" class="cart">
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
        <form action="<?= $baseUrl ?>/proses/login-user.php" method="POST">
            <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? $baseUrl . '/index.php') ?>">

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
            <button type="button" onclick="showAuthTab('register')">
                Daftar di sini
            </button>
        </p>
    </div>

    <div class="auth-panel" id="registerPanel">
        <form action="<?= $baseUrl ?>/proses/register-user.php" method="POST">
            <input type="hidden" name="redirect" value="<?= e($_SERVER['REQUEST_URI'] ?? $baseUrl . '/index.php') ?>">

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
            <button type="button" onclick="showAuthTab('login')">
                Masuk di sini
            </button>
        </p>
    </div>
</aside>

<?php endif; ?>

<script>
/* =========================
   FALLBACK AUTH + ACCOUNT MENU
   Supaya tombol Akun tetap jalan walau main.js tidak kebaca
========================= */

window.openAuthDrawer = function (tab = 'login') {
    const overlay = document.getElementById('authOverlay');
    const drawer = document.getElementById('authDrawer');

    if (overlay) {
        overlay.classList.add('show');
    }

    if (drawer) {
        drawer.classList.add('show');
    }

    document.body.classList.add('drawer-open');

    showAuthTab(tab);
};

window.closeAuthDrawer = function () {
    const overlay = document.getElementById('authOverlay');
    const drawer = document.getElementById('authDrawer');

    if (overlay) {
        overlay.classList.remove('show');
    }

    if (drawer) {
        drawer.classList.remove('show');
    }

    document.body.classList.remove('drawer-open');
};

window.showAuthTab = function (tab) {
    const loginTab = document.getElementById('loginTab');
    const registerTab = document.getElementById('registerTab');
    const loginPanel = document.getElementById('loginPanel');
    const registerPanel = document.getElementById('registerPanel');

    if (loginTab) loginTab.classList.remove('active');
    if (registerTab) registerTab.classList.remove('active');
    if (loginPanel) loginPanel.classList.remove('active');
    if (registerPanel) registerPanel.classList.remove('active');

    if (tab === 'register') {
        if (registerTab) registerTab.classList.add('active');
        if (registerPanel) registerPanel.classList.add('active');
    } else {
        if (loginTab) loginTab.classList.add('active');
        if (loginPanel) loginPanel.classList.add('active');
    }
};

window.toggleAccountMenu = function (event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    const dropdown = document.getElementById('accountDropdown');

    if (dropdown) {
        dropdown.classList.toggle('show');
    }
};

document.addEventListener('click', function (event) {
    const dropdown = document.getElementById('accountDropdown');
    const pill = document.querySelector('.account-pill');

    if (!dropdown || !pill) {
        return;
    }

    if (!pill.contains(event.target) && !dropdown.contains(event.target)) {
        dropdown.classList.remove('show');
    }
});
</script>
<?php
$currentAdminFile = basename($_SERVER['PHP_SELF'] ?? 'dashboard.php');

if (!function_exists('admin_menu_active')) {
    function admin_menu_active($files)
    {
        global $currentAdminFile;

        if (!is_array($files)) {
            $files = [$files];
        }

        return in_array($currentAdminFile, $files, true) ? 'admin-active-page' : '';
    }
}
?>

<aside class="admin-sidebar">
    <div class="side-brand">
        <div class="brand-icon">🐟</div>
        <h2>AquaStore</h2>
        <small>Admin Panel</small>
    </div>

    <a href="dashboard.php" class="<?= admin_menu_active('dashboard.php') ?>">📊 Dashboard</a>
    <a href="ikan.php" class="<?= admin_menu_active(['ikan.php', 'form-ikan.php']) ?>">🐠 Data Ikan</a>
    <a href="perlengkapan.php" class="<?= admin_menu_active(['perlengkapan.php', 'form-perlengkapan.php']) ?>">🛠️ Perlengkapan</a>
    <a href="pesanan.php" class="<?= admin_menu_active('pesanan.php') ?>">🛒 Pesanan</a>
    <a href="perawatan.php" class="<?= admin_menu_active('perawatan.php') ?>">💧 Perawatan</a>
    <a href="keuangan.php" class="<?= admin_menu_active('keuangan.php') ?>">💰 Keuangan</a>
    <a href="<?= e(url('index.php')) ?>">🏠 Website</a>
    <a href="<?= e(url('proses/logout.php')) ?>">🚪 Logout</a>
</aside>
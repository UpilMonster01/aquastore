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

<style>
.admin-sidebar a.admin-active-page {
    background: rgba(22, 119, 255, .12) !important;
    color: #1677ff !important;
    font-weight: 900 !important;
    border-radius: 14px;
    position: relative;
}

.admin-sidebar a.admin-active-page::before {
    content: "";
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 5px;
    height: 60%;
    border-radius: 999px;
    background: #1677ff;
}
</style>
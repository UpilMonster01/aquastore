<?php
$currentAdminFile = basename($_SERVER['PHP_SELF'] ?? 'dashboard.php');

if ($currentAdminFile === '' || $currentAdminFile === '.') {
    $currentAdminFile = basename(parse_url($_SERVER['REQUEST_URI'] ?? 'dashboard.php', PHP_URL_PATH) ?: 'dashboard.php');
}

if (!function_exists('admin_menu_active')) {
    function admin_menu_active($files)
    {
        global $currentAdminFile;

        if (!is_array($files)) {
            $files = [$files];
        }

        $current = strtolower($currentAdminFile ?? 'dashboard.php');
        $normalizedFiles = array_map(static function ($file) {
            return strtolower(trim($file));
        }, $files);

        return in_array($current, $normalizedFiles, true) ? 'admin-active-page' : '';
    }
}
?>

<aside class="admin-sidebar">
    <div class="side-brand">
        <?php if (file_exists(__DIR__ . '/../assets/img/logo.png')): ?>
            <img src="<?= e(url('../assets/img/logo.png')) ?>" alt="Logo AquaStore" class="brand-logo admin-brand-logo">
        <?php else: ?>
            <div class="brand-icon">🐟</div>
        <?php endif; ?>
        <div class="brand-text-wrap">
            <h2>AquaStore</h2>
            <small>Admin Panel</small>
        </div>
    </div>

    <a href="dashboard.php" class="<?= admin_menu_active('dashboard.php') ?>" <?= admin_menu_active('dashboard.php') ? 'aria-current="page"' : '' ?>>📊 Dashboard</a>
    <a href="ikan.php" class="<?= admin_menu_active(['ikan.php', 'form-ikan.php']) ?>" <?= admin_menu_active(['ikan.php', 'form-ikan.php']) ? 'aria-current="page"' : '' ?>>🐠 Data Ikan</a>
    <a href="perlengkapan.php" class="<?= admin_menu_active(['perlengkapan.php', 'form-perlengkapan.php']) ?>" <?= admin_menu_active(['perlengkapan.php', 'form-perlengkapan.php']) ? 'aria-current="page"' : '' ?>>🛠️ Perlengkapan</a>
    <a href="pesanan.php" class="<?= admin_menu_active('pesanan.php') ?>" <?= admin_menu_active('pesanan.php') ? 'aria-current="page"' : '' ?>>🛒 Pesanan</a>
    <a href="perawatan.php" class="<?= admin_menu_active('perawatan.php') ?>" <?= admin_menu_active('perawatan.php') ? 'aria-current="page"' : '' ?>>💧 Perawatan</a>
    <a href="keuangan.php" class="<?= admin_menu_active('keuangan.php') ?>" <?= admin_menu_active('keuangan.php') ? 'aria-current="page"' : '' ?>>💰 Keuangan</a>
    <a href="<?= e(url('index.php')) ?>">🏠 Website</a>
    <a href="<?= e(url('proses/logout.php')) ?>">🚪 Logout</a>
</aside>
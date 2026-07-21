<?php
// Bar ini hanya tampil kalau sesi ADMIN masih aktif (bukan sesi pelanggan
// biasa) saat sedang membuka halaman publik toko. Tujuannya supaya admin
// tidak salah kira dirinya ter-logout begitu klik "Website" dari sidebar.

if (empty($_SESSION['admin'])) {
    return;
}

$adminBarPendingCount = 0;

if (isset($pdo)) {
    $adminBarPendingCount = (int) $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'Pending'")->fetchColumn();
}
?>
<div class="admin-mode-bar">
    <div class="admin-mode-bar-inner">
        <span class="admin-mode-bar-label">
            🛠️ Mode Admin — masuk sebagai <b><?= e($_SESSION['admin']['nama'] ?? 'Admin') ?></b>
        </span>

        <div class="admin-mode-bar-actions">
            <?php if ($adminBarPendingCount > 0): ?>
                <a href="<?= e(url('admin/pesanan.php?status=Pending')) ?>" class="admin-mode-bar-badge">
                    <?= $adminBarPendingCount ?> pesanan pending
                </a>
            <?php endif; ?>

            <a href="<?= e(url('admin/dashboard.php')) ?>" class="admin-mode-bar-link">
                ← Kembali ke Dashboard
            </a>
        </div>
    </div>
</div>

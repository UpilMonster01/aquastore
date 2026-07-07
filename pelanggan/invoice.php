<?php
require "../config/db.php";

user_only();

$nomor = trim($_GET['nomor'] ?? '');

if ($nomor === '') {
    flash('error', 'Nomor pesanan tidak ditemukan.');
    redirect_to(url('pelanggan/pesanan-saya.php'));
}

$stmt = $pdo->prepare("
    SELECT *
    FROM pesanan
    WHERE nomor_pesanan = ?
    LIMIT 1
");
$stmt->execute([$nomor]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    flash('error', 'Pesanan tidak ditemukan.');
    redirect_to(url('pelanggan/pesanan-saya.php'));
}

$userId = (int) ($_SESSION['user']['id'] ?? 0);
$ownerId = (int) ($pesanan['pelanggan_id'] ?? 0);

if ($ownerId > 0 && $ownerId !== $userId) {
    flash('error', 'Kamu tidak memiliki akses ke invoice ini.');
    redirect_to(url('pelanggan/pesanan-saya.php'));
}

$stmtIkan = $pdo->prepare("
    SELECT 
        dp.*,
        i.nama AS nama_produk
    FROM detail_pesanan dp
    LEFT JOIN ikan i ON dp.ikan_id = i.id
    WHERE dp.pesanan_id = ?
");
$stmtIkan->execute([$pesanan['id']]);
$detailIkan = $stmtIkan->fetchAll();

$stmtPerlengkapan = $pdo->prepare("
    SELECT 
        dpp.*,
        p.nama AS nama_produk
    FROM detail_pesanan_perlengkapan dpp
    LEFT JOIN perlengkapan p ON dpp.perlengkapan_id = p.id
    WHERE dpp.pesanan_id = ?
");
$stmtPerlengkapan->execute([$pesanan['id']]);
$detailPerlengkapan = $stmtPerlengkapan->fetchAll();

$subtotal = 0;

foreach ($detailIkan as $item) {
    $subtotal += (int) $item['jumlah'] * (int) $item['harga_satuan'];
}

foreach ($detailPerlengkapan as $item) {
    $subtotal += (int) $item['jumlah'] * (int) $item['harga_satuan'];
}

$ongkir = (int) $pesanan['total_harga'] - $subtotal;

if ($ongkir < 0) {
    $ongkir = 0;
}

function tanggal_invoice($tanggal)
{
    if (!$tanggal) {
        return '-';
    }

    return date('d F Y H:i', strtotime($tanggal));
}

function payment_badge_invoice($status)
{
    $status = strtolower($status ?? 'belum bayar');

    if ($status === 'terverifikasi') {
        return 'badge-success';
    }

    if ($status === 'menunggu verifikasi') {
        return 'badge-warning';
    }

    if ($status === 'ditolak') {
        return 'badge-danger';
    }

    return 'badge-muted';
}

function order_badge_invoice($status)
{
    $status = strtolower($status ?? 'pending');

    if ($status === 'selesai') {
        return 'badge-success';
    }

    if ($status === 'dikirim') {
        return 'badge-primary';
    }

    if ($status === 'diproses') {
        return 'badge-warning';
    }

    return 'badge-muted';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice <?= e($pesanan['nomor_pesanan']) ?> - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=360">
</head>
<body class="invoice-page-body">

<?php include "../components/navbar.php"; ?>

<section class="invoice-page">
    <div class="invoice-toolbar">
        <a href="<?= e(url('pelanggan/pesanan-saya.php')) ?>" class="btn-back">
            ← Kembali
        </a>

        <button onclick="window.print()" class="btn-print">
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="invoice-card">
        <div class="invoice-header">
            <div class="invoice-brand">
                <div class="invoice-logo">🐟</div>

                <div>
                    <h1>AquaStore</h1>
                    <p>Toko Ikan Hias & Perlengkapan Aquarium</p>
                </div>
            </div>

            <div class="invoice-number">
                <span>Invoice</span>
                <h2><?= e($pesanan['nomor_pesanan']) ?></h2>

                <div>
                    <span class="badge <?= order_badge_invoice($pesanan['status']) ?>">
                        <?= e($pesanan['status']) ?>
                    </span>

                    <?php if ($pesanan['metode_bayar'] === 'COD'): ?>
                        <span class="badge badge-success">COD</span>
                    <?php else: ?>
                        <span class="badge <?= payment_badge_invoice($pesanan['status_pembayaran']) ?>">
                            <?= e($pesanan['status_pembayaran']) ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="invoice-grid">
            <div class="info-box">
                <h3>Data Pelanggan</h3>
                <p><b>Nama:</b> <?= e($pesanan['nama_pelanggan']) ?></p>
                <p><b>No HP:</b> <?= e($pesanan['no_hp']) ?></p>
                <p><b>Alamat:</b><br><?= nl2br(e($pesanan['alamat'])) ?></p>
            </div>

            <div class="info-box">
                <h3>Detail Pesanan</h3>
                <p><b>Tanggal:</b> <?= e(tanggal_invoice($pesanan['created_at'])) ?></p>
                <p><b>Pengiriman:</b> <?= e($pesanan['metode_pengiriman']) ?></p>
                <p><b>Pembayaran:</b> <?= e($pesanan['metode_bayar']) ?></p>
                <p><b>Status Pesanan:</b> <?= e($pesanan['status']) ?></p>
            </div>
        </div>

        <h3>Rincian Produk</h3>

        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Jumlah</th>
                    <th class="text-right">Subtotal</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($detailIkan as $item): ?>
                    <tr>
                        <td><?= e($item['nama_produk'] ?? 'Produk ikan dihapus') ?></td>
                        <td>Ikan</td>
                        <td class="text-right"><?= rupiah($item['harga_satuan']) ?></td>
                        <td class="text-right"><?= e($item['jumlah']) ?></td>
                        <td class="text-right">
                            <?= rupiah((int) $item['jumlah'] * (int) $item['harga_satuan']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php foreach ($detailPerlengkapan as $item): ?>
                    <tr>
                        <td><?= e($item['nama_produk'] ?? 'Produk perlengkapan dihapus') ?></td>
                        <td>Perlengkapan</td>
                        <td class="text-right"><?= rupiah($item['harga_satuan']) ?></td>
                        <td class="text-right"><?= e($item['jumlah']) ?></td>
                        <td class="text-right">
                            <?= rupiah((int) $item['jumlah'] * (int) $item['harga_satuan']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (!$detailIkan && !$detailPerlengkapan): ?>
                    <tr>
                        <td colspan="5">Tidak ada detail produk.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="invoice-summary">
            <div class="summary-row">
                <span>Subtotal</span>
                <b><?= rupiah($subtotal) ?></b>
            </div>

            <div class="summary-row">
                <span>Ongkir</span>
                <b><?= rupiah($ongkir) ?></b>
            </div>

            <div class="summary-row total">
                <span>Total</span>
                <b><?= rupiah($pesanan['total_harga']) ?></b>
            </div>
        </div>

        <div class="invoice-note">
            Terima kasih sudah berbelanja di AquaStore. Simpan invoice ini sebagai bukti transaksi.
            Untuk pembayaran Transfer Bank/QRIS, pesanan akan diproses setelah pembayaran diverifikasi admin.
        </div>

        <div class="invoice-footer">
            Invoice ini dibuat otomatis oleh sistem AquaStore.
        </div>
    </div>
</section>

</body>
</html>
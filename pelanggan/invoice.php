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

    <style>
        body {
            background: #f4f8fb;
        }

        .invoice-page {
            width: min(980px, 92%);
            margin: 40px auto;
        }

        .invoice-toolbar {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 18px;
        }

        .invoice-toolbar a,
        .invoice-toolbar button {
            border: none;
            padding: 11px 16px;
            border-radius: 999px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-back {
            background: #ffffff;
            color: #0f172a;
            box-shadow: 0 8px 20px rgba(15, 23, 42, .08);
        }

        .btn-print {
            background: #1677ff;
            color: #ffffff;
        }

        .invoice-card {
            background: #ffffff;
            border-radius: 28px;
            padding: 34px;
            box-shadow: 0 20px 55px rgba(15, 23, 42, .10);
        }

        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 24px;
            border-bottom: 2px dashed #e2e8f0;
            padding-bottom: 24px;
            margin-bottom: 24px;
        }

        .invoice-brand {
            display: flex;
            gap: 14px;
            align-items: center;
        }

        .invoice-logo {
            width: 58px;
            height: 58px;
            border-radius: 18px;
            background: linear-gradient(135deg, #1677ff, #22c55e);
            display: grid;
            place-items: center;
            color: white;
            font-size: 30px;
        }

        .invoice-brand h1 {
            margin: 0;
            color: #0f172a;
            font-size: 28px;
        }

        .invoice-brand p {
            margin: 4px 0 0;
            color: #64748b;
        }

        .invoice-number {
            text-align: right;
        }

        .invoice-number span {
            color: #64748b;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .invoice-number h2 {
            margin: 5px 0 8px;
            font-size: 22px;
            color: #0f172a;
        }

        .badge {
            display: inline-flex;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 900;
            margin-left: 6px;
        }

        .badge-success {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-warning {
            background: #fef3c7;
            color: #b45309;
        }

        .badge-danger {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge-muted {
            background: #e2e8f0;
            color: #475569;
        }

        .badge-primary {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .invoice-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
            margin-bottom: 26px;
        }

        .info-box {
            background: #f8fafc;
            border-radius: 20px;
            padding: 18px;
            border: 1px solid #e2e8f0;
        }

        .info-box h3 {
            margin: 0 0 12px;
            color: #0f172a;
        }

        .info-box p {
            margin: 6px 0;
            color: #334155;
            line-height: 1.5;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 18px;
            margin-top: 16px;
        }

        .invoice-table th {
            background: #0f172a;
            color: white;
            padding: 14px;
            font-size: 14px;
            text-align: left;
        }

        .invoice-table td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }

        .invoice-table tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .invoice-summary {
            width: min(420px, 100%);
            margin-left: auto;
            margin-top: 24px;
            background: #f8fafc;
            border-radius: 22px;
            padding: 20px;
            border: 1px solid #e2e8f0;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 9px 0;
            color: #334155;
        }

        .summary-row.total {
            border-top: 2px dashed #cbd5e1;
            margin-top: 8px;
            padding-top: 14px;
            font-size: 22px;
            font-weight: 900;
            color: #0f172a;
        }

        .invoice-note {
            margin-top: 26px;
            padding: 18px;
            border-radius: 20px;
            background: #eff6ff;
            color: #1e3a8a;
            line-height: 1.6;
        }

        .invoice-footer {
            text-align: center;
            color: #64748b;
            margin-top: 26px;
            font-size: 13px;
        }

        @media print {
            body {
                background: white;
            }

            .invoice-toolbar,
            .topbar {
                display: none !important;
            }

            .invoice-page {
                width: 100%;
                margin: 0;
            }

            .invoice-card {
                box-shadow: none;
                border-radius: 0;
            }
        }

        @media (max-width: 760px) {
            .invoice-header,
            .invoice-grid {
                grid-template-columns: 1fr;
                flex-direction: column;
            }

            .invoice-number {
                text-align: left;
            }

            .invoice-table {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>

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
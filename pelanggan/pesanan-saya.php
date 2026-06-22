<?php
require "../config/db.php";

if (empty($_SESSION['user'])) {
    flash('error', 'Silakan login terlebih dahulu.');
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user']['id'];

$stmt = $pdo->prepare("
    SELECT *
    FROM pesanan
    WHERE pelanggan_id = ?
    ORDER BY id DESC
");
$stmt->execute([$userId]);
$pesanan = $stmt->fetchAll();

function status_class_customer($status)
{
    $status = strtolower($status);

    if ($status === 'pending') return 'status-pending';
    if ($status === 'diproses') return 'status-diproses';
    if ($status === 'dikirim') return 'status-dikirim';
    if ($status === 'selesai') return 'status-selesai';

    return 'status-pending';
}

function status_pembayaran_class($status)
{
    $status = strtolower($status ?? 'belum bayar');

    if ($status === 'belum bayar') return 'payment-unpaid';
    if ($status === 'menunggu verifikasi') return 'payment-waiting';
    if ($status === 'terverifikasi') return 'payment-verified';
    if ($status === 'ditolak') return 'payment-rejected';

    return 'payment-unpaid';
}

function tanggal_pesanan($data)
{
    if (!empty($data['created_at'])) {
        return date('d M Y H:i', strtotime($data['created_at']));
    }

    return '-';
}

function ambil_detail_ikan($pdo, $pesananId)
{
    $stmt = $pdo->prepare("
        SELECT 
            dp.*,
            i.nama AS nama_ikan,
            i.foto AS foto_ikan
        FROM detail_pesanan dp
        LEFT JOIN ikan i ON dp.ikan_id = i.id
        WHERE dp.pesanan_id = ?
    ");
    $stmt->execute([$pesananId]);

    return $stmt->fetchAll();
}

function ambil_detail_perlengkapan($pdo, $pesananId)
{
    $stmt = $pdo->prepare("
        SELECT 
            dpp.*,
            p.nama AS nama_perlengkapan,
            p.foto AS foto_perlengkapan,
            p.kategori AS kategori_perlengkapan
        FROM detail_pesanan_perlengkapan dpp
        LEFT JOIN perlengkapan p ON dpp.perlengkapan_id = p.id
        WHERE dpp.pesanan_id = ?
    ");
    $stmt->execute([$pesananId]);

    return $stmt->fetchAll();
}

function is_image_file($file)
{
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan Saya - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=330">
</head>
<body>

<?php include "../components/navbar.php"; ?>

<section class="my-order-section">
    <div class="section-title">
        <span>Akun Pelanggan</span>
        <h2>Pesanan Saya</h2>
        <p>Lihat riwayat pesanan, status, pembayaran, dan detail produk yang pernah kamu beli.</p>
    </div>

    <?php show_flash(); ?>

    <?php if (!$pesanan): ?>
        <div class="empty-box">
            <h2>Belum Ada Pesanan</h2>
            <p>Kamu belum memiliki riwayat pesanan.</p>

            <a href="katalog.php" class="hero-button">
                Mulai Belanja
            </a>
        </div>
    <?php else: ?>

        <div class="my-order-container">
            <?php foreach ($pesanan as $p): ?>
                <?php
                $detailIkan = ambil_detail_ikan($pdo, $p['id']);
                $detailPerlengkapan = ambil_detail_perlengkapan($pdo, $p['id']);

                $statusPembayaran = $p['status_pembayaran'] ?? 'Belum Bayar';
                $buktiPembayaran = $p['bukti_pembayaran'] ?? '';
                $catatanPembayaran = $p['catatan_pembayaran'] ?? '';
                ?>

                <div class="my-order-card">
                    <div class="my-order-top">
                        <div>
                            <span>Nomor Pesanan</span>
                            <h3><?= e($p['nomor_pesanan']) ?></h3>
                        </div>

                        <div class="status-badge <?= status_class_customer($p['status']) ?>">
                            <?= e($p['status']) ?>
                        </div>
                    </div>

                    <div class="my-order-info">
                        <div>
                            <span>Nama</span>
                            <b><?= e($p['nama_pelanggan']) ?></b>
                        </div>

                        <div>
                            <span>No HP</span>
                            <b><?= e($p['no_hp']) ?></b>
                        </div>

                        <div>
                            <span>Tanggal</span>
                            <b><?= tanggal_pesanan($p) ?></b>
                        </div>

                        <div>
                            <span>Pengiriman</span>
                            <b><?= e($p['metode_pengiriman']) ?></b>
                        </div>

                        <div>
                            <span>Pembayaran</span>
                            <b><?= e($p['metode_bayar']) ?></b>
                        </div>

                        <div>
                            <span>Status Bayar</span>
                            <b class="payment-status <?= status_pembayaran_class($statusPembayaran) ?>">
                                <?= e($statusPembayaran) ?>
                            </b>
                        </div>

                        <div>
                            <span>Total</span>
                            <b class="order-price"><?= rupiah($p['total_harga']) ?></b>
                        </div>
                    </div>

                    <div class="my-order-address">
                        <span>Alamat</span>
                        <p><?= e($p['alamat']) ?></p>
                    </div>

                    <?php if ($p['metode_bayar'] !== 'COD'): ?>
                        <div class="payment-proof-box">
                            <div class="payment-proof-header">
                                <div>
                                    <span>Bukti Pembayaran</span>
                                    <h4>Upload Bukti Transfer / QRIS</h4>
                                </div>

                                <b class="payment-status <?= status_pembayaran_class($statusPembayaran) ?>">
                                    <?= e($statusPembayaran) ?>
                                </b>
                            </div>

                            <?php if (!empty($buktiPembayaran)): ?>
                                <div class="proof-current">
                                    <?php if (is_image_file($buktiPembayaran)): ?>
                                        <img
                                            src="../uploads/bukti/<?= e($buktiPembayaran) ?>"
                                            alt="Bukti pembayaran"
                                            class="proof-img"
                                        >
                                    <?php else: ?>
                                        <a
                                            href="../uploads/bukti/<?= e($buktiPembayaran) ?>"
                                            target="_blank"
                                            class="proof-file-link"
                                        >
                                            Lihat File Bukti Pembayaran
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($statusPembayaran === 'Ditolak' && !empty($catatanPembayaran)): ?>
                                <div class="payment-reject-note customer-note">
                                    <b>Alasan Penolakan:</b>
                                    <p><?= e($catatanPembayaran) ?></p>
                                </div>
                            <?php endif; ?>

                            <?php if ($statusPembayaran !== 'Terverifikasi'): ?>
                                <form
                                    action="../proses/upload-bukti.php"
                                    method="POST"
                                    enctype="multipart/form-data"
                                    class="proof-upload-form"
                                >
                                    <input type="hidden" name="pesanan_id" value="<?= $p['id'] ?>">

                                    <input
                                        type="file"
                                        name="bukti"
                                        accept="image/*,.pdf"
                                        required
                                    >

                                    <button class="hero-button" type="submit">
                                        <?= !empty($buktiPembayaran) ? 'Upload Ulang Bukti' : 'Upload Bukti' ?>
                                    </button>
                                </form>

                                <p class="proof-note">
                                    Format file: JPG, JPEG, PNG, WEBP, atau PDF. Maksimal 2 MB.
                                </p>
                            <?php else: ?>
                                <p class="proof-note success">
                                    Pembayaran sudah diverifikasi oleh admin.
                                </p>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="payment-proof-box cod-box">
                            <div class="payment-proof-header">
                                <div>
                                    <span>Pembayaran COD</span>
                                    <h4>Tidak Perlu Upload Bukti</h4>
                                </div>

                                <b class="payment-status payment-verified">
                                    COD
                                </b>
                            </div>

                            <p class="proof-note">
                                Pembayaran dilakukan saat pesanan diterima atau saat ambil sendiri di toko.
                            </p>
                        </div>
                    <?php endif; ?>

                    <details class="order-detail-box">
                        <summary>
                            Lihat Detail Produk
                        </summary>

                        <?php if ($detailIkan): ?>
                            <div class="order-detail-group">
                                <h4>🐠 Ikan Hias</h4>

                                <div class="order-product-list">
                                    <?php foreach ($detailIkan as $item): ?>
                                        <div class="order-product-item">
                                            <div class="order-product-img">
                                                <?php if (!empty($item['foto_ikan'])): ?>
                                                    <img src="../uploads/ikan/<?= e($item['foto_ikan']) ?>" alt="<?= e($item['nama_ikan']) ?>">
                                                <?php else: ?>
                                                    <span>🐠</span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="order-product-info">
                                                <h5><?= e($item['nama_ikan'] ?: 'Produk ikan tidak ditemukan') ?></h5>
                                                <p>
                                                    <?= $item['jumlah'] ?> x <?= rupiah($item['harga_satuan']) ?>
                                                </p>
                                            </div>

                                            <div class="order-product-price">
                                                <?= rupiah($item['jumlah'] * $item['harga_satuan']) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($detailPerlengkapan): ?>
                            <div class="order-detail-group">
                                <h4>🛠️ Perlengkapan Aquarium</h4>

                                <div class="order-product-list">
                                    <?php foreach ($detailPerlengkapan as $item): ?>
                                        <div class="order-product-item">
                                            <div class="order-product-img">
                                                <?php if (!empty($item['foto_perlengkapan'])): ?>
                                                    <img src="../uploads/perlengkapan/<?= e($item['foto_perlengkapan']) ?>" alt="<?= e($item['nama_perlengkapan']) ?>">
                                                <?php else: ?>
                                                    <span>🛠️</span>
                                                <?php endif; ?>
                                            </div>

                                            <div class="order-product-info">
                                                <h5><?= e($item['nama_perlengkapan'] ?: 'Produk perlengkapan tidak ditemukan') ?></h5>

                                                <?php if (!empty($item['kategori_perlengkapan'])): ?>
                                                    <small><?= e($item['kategori_perlengkapan']) ?></small>
                                                <?php endif; ?>

                                                <p>
                                                    <?= $item['jumlah'] ?> x <?= rupiah($item['harga_satuan']) ?>
                                                </p>
                                            </div>

                                            <div class="order-product-price">
                                                <?= rupiah($item['jumlah'] * $item['harga_satuan']) ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!$detailIkan && !$detailPerlengkapan): ?>
                            <div class="order-no-product">
                                Detail produk tidak ditemukan.
                            </div>
                        <?php endif; ?>

                        <div class="order-detail-total">
                            <span>Total Pembayaran</span>
                            <b><?= rupiah($p['total_harga']) ?></b>
                        </div>
                    </details>

                    <div class="my-order-actions">
                        <a
                            href="cek-pesanan.php?nomor=<?= urlencode($p['nomor_pesanan']) ?>"
                            class="mini-button"
                        >
                            Cek Status
                        </a>

                        <?php if ($p['status'] === 'Pending'): ?>
                            <span class="order-help-text">
                                Pesanan masih menunggu konfirmasi admin.
                            </span>
                        <?php elseif ($p['status'] === 'Diproses'): ?>
                            <span class="order-help-text">
                                Pesanan sedang diproses oleh admin.
                            </span>
                        <?php elseif ($p['status'] === 'Dikirim'): ?>
                            <span class="order-help-text">
                                Pesanan sedang dalam pengiriman.
                            </span>
                        <?php elseif ($p['status'] === 'Selesai'): ?>
                            <span class="order-help-text success">
                                Pesanan telah selesai.
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
</section>

<script src="../assets/js/main.js"></script>
</body>
</html>
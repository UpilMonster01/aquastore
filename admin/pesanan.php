<?php
require "../config/db.php";
admin_only();

$filterStatus = $_GET['status'] ?? '';

$allowedStatus = ['Pending', 'Diproses', 'Dikirim', 'Selesai'];
$allowedStatusPembayaran = ['Belum Bayar', 'Menunggu Verifikasi', 'Terverifikasi', 'Ditolak'];

if ($filterStatus !== '' && !in_array($filterStatus, $allowedStatus, true)) {
    $filterStatus = '';
}

if ($filterStatus !== '') {
    $stmt = $pdo->prepare("
        SELECT *
        FROM pesanan
        WHERE status = ?
        ORDER BY id DESC
    ");
    $stmt->execute([$filterStatus]);
} else {
    $stmt = $pdo->query("
        SELECT *
        FROM pesanan
        ORDER BY id DESC
    ");
}

$pesanan = $stmt->fetchAll();

function status_class_admin($status)
{
    $status = strtolower($status);

    if ($status === 'pending') return 'status-pending';
    if ($status === 'diproses') return 'status-diproses';
    if ($status === 'dikirim') return 'status-dikirim';
    if ($status === 'selesai') return 'status-selesai';

    return 'status-pending';
}

function status_pembayaran_class_admin($status)
{
    $status = strtolower($status ?? 'belum bayar');

    if ($status === 'belum bayar') return 'payment-unpaid';
    if ($status === 'menunggu verifikasi') return 'payment-waiting';
    if ($status === 'terverifikasi') return 'payment-verified';
    if ($status === 'ditolak') return 'payment-rejected';

    return 'payment-unpaid';
}

function tanggal_admin($row)
{
    if (isset($row['created_at']) && $row['created_at'] !== '') {
        return date('d M Y H:i', strtotime($row['created_at']));
    }

    return '-';
}

function detail_ikan_admin($pdo, $pesananId)
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

function detail_perlengkapan_admin($pdo, $pesananId)
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

function is_image_file_admin($file)
{
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true);
}

$totalPesanan = count($pesanan);

$totalPending = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'Pending'")->fetchColumn();
$totalDiproses = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'Diproses'")->fetchColumn();
$totalDikirim = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status = 'Dikirim'")->fetchColumn();

$totalMenungguVerifikasi = $pdo->query("
    SELECT COUNT(*) 
    FROM pesanan 
    WHERE status_pembayaran = 'Menunggu Verifikasi'
")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pesanan - Admin AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=330">
</head>
<body>

<div class="admin-layout">
    <?php include "sidebar.php"; ?>

    <main class="admin-content">
        <div class="admin-header">
            <div>
                <span>Manajemen Pesanan</span>
                <h1>Data Pesanan Customer</h1>
            </div>

            <b><?= $totalPesanan ?> Pesanan</b>
        </div>

        <?php show_flash(); ?>

        <div class="admin-stats admin-order-stats">
            <div class="dash-card">
                <span>⏳</span>
                <p>Pending</p>
                <h2><?= $totalPending ?></h2>
            </div>

            <div class="dash-card">
                <span>📦</span>
                <p>Diproses</p>
                <h2><?= $totalDiproses ?></h2>
            </div>

            <div class="dash-card">
                <span>🚚</span>
                <p>Dikirim</p>
                <h2><?= $totalDikirim ?></h2>
            </div>

            <div class="dash-card">
                <span>💳</span>
                <p>Menunggu Verifikasi</p>
                <h2><?= $totalMenungguVerifikasi ?></h2>
            </div>
        </div>

        <div class="admin-panel">
            <div class="admin-order-toolbar">
                <h2>Daftar Pesanan</h2>

                <form method="GET" class="admin-order-filter">
                    <select name="status" onchange="this.form.submit()">
                        <option value="">Semua Status</option>

                        <?php foreach ($allowedStatus as $status): ?>
                            <option value="<?= e($status) ?>" <?= $filterStatus === $status ? 'selected' : '' ?>>
                                <?= e($status) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <?php if (!$pesanan): ?>
                <div class="empty-box">
                    <h2>Belum Ada Pesanan</h2>
                    <p>Data pesanan customer belum tersedia.</p>
                </div>
            <?php else: ?>

                <div class="table-box">
                    <table>
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nomor Pesanan</th>
                                <th>Pelanggan</th>
                                <th>Pembayaran</th>
                                <th>Status Bayar</th>
                                <th>Total</th>
                                <th>Status Pesanan</th>
                                <th>Tanggal</th>
                                <th>Detail</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($pesanan as $index => $p): ?>
                                <?php
                                $statusPembayaran = $p['status_pembayaran'] ?? 'Belum Bayar';
                                ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>

                                    <td>
                                        <b><?= e($p['nomor_pesanan']) ?></b>
                                    </td>

                                    <td>
                                        <b><?= e($p['nama_pelanggan']) ?></b><br>
                                        <small><?= e($p['no_hp']) ?></small>
                                    </td>

                                    <td>
                                        <?= e($p['metode_bayar']) ?><br>
                                        <small><?= e($p['metode_pengiriman']) ?></small>
                                    </td>

                                    <td>
                                        <?php if ($p['metode_bayar'] === 'COD'): ?>
                                            <span class="payment-status payment-verified">COD</span>
                                        <?php else: ?>
                                            <span class="payment-status <?= status_pembayaran_class_admin($statusPembayaran) ?>">
                                                <?= e($statusPembayaran) ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <b><?= rupiah($p['total_harga']) ?></b>
                                    </td>

                                    <td>
                                        <form action="../proses/update-status.php" method="POST">
                                            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">

                                            <select
                                                name="status"
                                                class="status-select <?= status_class_admin($p['status']) ?>"
                                                onchange="this.form.submit()"
                                            >
                                                <?php foreach ($allowedStatus as $status): ?>
                                                    <option
                                                        value="<?= e($status) ?>"
                                                        <?= $p['status'] === $status ? 'selected' : '' ?>
                                                    >
                                                        <?= e($status) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>
                                    </td>

                                    <td><?= tanggal_admin($p) ?></td>

                                  <td>
                                   <button
                                    class="mini-button"
                                         onclick="openModal('detailPesanan<?= $p['id'] ?>')"
                                          >
                                               Lihat
                                                  </button>

                                                  <a href="invoice.php?nomor=<?= urlencode($p['nomor_pesanan']) ?>" class="mini-button order-action-link">
                                               Invoice
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </div>
    </main>
</div>

<?php foreach ($pesanan as $p): ?>
    <?php
    $detailIkan = detail_ikan_admin($pdo, $p['id']);
    $detailPerlengkapan = detail_perlengkapan_admin($pdo, $p['id']);

    $statusPembayaran = $p['status_pembayaran'] ?? 'Belum Bayar';
    $buktiPembayaran = $p['bukti_pembayaran'] ?? '';
    $catatanPembayaran = $p['catatan_pembayaran'] ?? '';
    ?>

    <div class="modal" id="detailPesanan<?= $p['id'] ?>">
        <div class="modal-box order-admin-modal">
            <button class="close-btn" onclick="closeModal('detailPesanan<?= $p['id'] ?>')">
                ×
            </button>

            <h2>Detail Pesanan</h2>

            <div class="admin-order-number">
                <?= e($p['nomor_pesanan']) ?>
            </div>

            <div class="admin-order-detail-grid">
                <div>
                    <span>Nama Pelanggan</span>
                    <b><?= e($p['nama_pelanggan']) ?></b>
                </div>

                <div>
                    <span>No HP</span>
                    <b><?= e($p['no_hp']) ?></b>
                </div>

                <div>
                    <span>Status Pesanan</span>
                    <b class="status-text <?= status_class_admin($p['status']) ?>">
                        <?= e($p['status']) ?>
                    </b>
                </div>

                <div>
                    <span>Status Pembayaran</span>

                    <?php if ($p['metode_bayar'] === 'COD'): ?>
                        <b class="payment-status payment-verified">COD</b>
                    <?php else: ?>
                        <b class="payment-status <?= status_pembayaran_class_admin($statusPembayaran) ?>">
                            <?= e($statusPembayaran) ?>
                        </b>
                    <?php endif; ?>
                </div>

                <div>
                    <span>Tanggal</span>
                    <b><?= tanggal_admin($p) ?></b>
                </div>

                <div>
                    <span>Pengiriman</span>
                    <b><?= e($p['metode_pengiriman']) ?></b>
                </div>

                <div>
                    <span>Pembayaran</span>
                    <b><?= e($p['metode_bayar']) ?></b>
                </div>

                <div class="wide">
                    <span>Alamat</span>
                    <b><?= e($p['alamat']) ?></b>
                </div>
            </div>

            <div class="admin-payment-proof-box">
                <div class="admin-payment-proof-header">
                    <div>
                        <span>Bukti Pembayaran</span>
                        <h3>Verifikasi Pembayaran</h3>
                    </div>

                    <?php if ($p['metode_bayar'] === 'COD'): ?>
                        <b class="payment-status payment-verified">COD</b>
                    <?php else: ?>
                        <b class="payment-status <?= status_pembayaran_class_admin($statusPembayaran) ?>">
                            <?= e($statusPembayaran) ?>
                        </b>
                    <?php endif; ?>
                </div>

                <?php if ($p['metode_bayar'] === 'COD'): ?>

                    <div class="admin-proof-empty">
                        Pesanan COD tidak memerlukan bukti pembayaran.
                    </div>

                <?php else: ?>

                    <?php if (!empty($buktiPembayaran)): ?>
                        <div class="admin-proof-preview">
                            <?php if (is_image_file_admin($buktiPembayaran)): ?>
                                <a href="../uploads/bukti/<?= e($buktiPembayaran) ?>" target="_blank">
                                    <img
                                        src="../uploads/bukti/<?= e($buktiPembayaran) ?>"
                                        alt="Bukti pembayaran"
                                        class="admin-proof-img"
                                    >
                                </a>
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
                    <?php else: ?>
                        <div class="admin-proof-empty">
                            Customer belum mengupload bukti pembayaran.
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($catatanPembayaran)): ?>
                        <div class="payment-reject-note">
                            <b>Catatan Admin:</b>
                            <p><?= e($catatanPembayaran) ?></p>
                        </div>
                    <?php endif; ?>

                    <form action="../proses/update-pembayaran.php" method="POST" class="admin-payment-form admin-payment-form-note">
                        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">

                        <select name="status_pembayaran" required>
                            <?php foreach ($allowedStatusPembayaran as $sp): ?>
                                <option
                                    value="<?= e($sp) ?>"
                                    <?= $statusPembayaran === $sp ? 'selected' : '' ?>
                                >
                                    <?= e($sp) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>

                        <textarea
                            name="catatan_pembayaran"
                            placeholder="Isi alasan jika pembayaran ditolak"
                        ><?= e($catatanPembayaran) ?></textarea>

                        <button class="hero-button" type="submit">
                            Simpan Status Pembayaran
                        </button>
                    </form>

                <?php endif; ?>
            </div>

            <?php if ($detailIkan): ?>
                <h3 class="admin-detail-title">🐠 Ikan Hias</h3>

                <div class="table-box small-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nama Ikan</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($detailIkan as $item): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($item['foto_ikan'])): ?>
                                            <img
                                                src="../uploads/ikan/<?= e($item['foto_ikan']) ?>"
                                                class="thumb"
                                                alt="<?= e($item['nama_ikan']) ?>"
                                            >
                                        <?php else: ?>
                                            <span class="emoji-table">🐠</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= e($item['nama_ikan'] ?: 'Produk ikan tidak ditemukan') ?></td>
                                    <td><?= $item['jumlah'] ?></td>
                                    <td><?= rupiah($item['harga_satuan']) ?></td>
                                    <td><?= rupiah($item['jumlah'] * $item['harga_satuan']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if ($detailPerlengkapan): ?>
                <h3 class="admin-detail-title">🛠️ Perlengkapan Aquarium</h3>

                <div class="table-box small-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>Nama Produk</th>
                                <th>Kategori</th>
                                <th>Jumlah</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($detailPerlengkapan as $item): ?>
                                <tr>
                                    <td>
                                        <?php if (!empty($item['foto_perlengkapan'])): ?>
                                            <img
                                                src="../uploads/perlengkapan/<?= e($item['foto_perlengkapan']) ?>"
                                                class="thumb"
                                                alt="<?= e($item['nama_perlengkapan']) ?>"
                                            >
                                        <?php else: ?>
                                            <span class="emoji-table">🛠️</span>
                                        <?php endif; ?>
                                    </td>

                                    <td><?= e($item['nama_perlengkapan'] ?: 'Produk tidak ditemukan') ?></td>
                                    <td><?= e($item['kategori_perlengkapan'] ?: '-') ?></td>
                                    <td><?= $item['jumlah'] ?></td>
                                    <td><?= rupiah($item['harga_satuan']) ?></td>
                                    <td><?= rupiah($item['jumlah'] * $item['harga_satuan']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

            <?php if (!$detailIkan && !$detailPerlengkapan): ?>
                <div class="order-no-product">
                    Detail produk tidak ditemukan.
                </div>
            <?php endif; ?>

            <div class="admin-order-total-box">
                <span>Total Pembayaran</span>
                <b><?= rupiah($p['total_harga']) ?></b>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<script src="../assets/js/main.js"></script>
</body>
</html>
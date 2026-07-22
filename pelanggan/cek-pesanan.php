<?php
require "../config/db.php";

$nomor = trim($_GET['nomor'] ?? '');
$pesanan = null;
$detailIkan = [];
$detailPerlengkapan = [];

if ($nomor !== '') {
    $stmt = $pdo->prepare("
        SELECT *
        FROM pesanan
        WHERE nomor_pesanan = ?
        LIMIT 1
    ");
    $stmt->execute([$nomor]);
    $pesanan = $stmt->fetch();

    if ($pesanan) {
        $stmtIkan = $pdo->prepare("
            SELECT 
                dp.*,
                i.nama AS nama_ikan,
                i.foto AS foto_ikan
            FROM detail_pesanan dp
            LEFT JOIN ikan i ON dp.ikan_id = i.id
            WHERE dp.pesanan_id = ?
        ");
        $stmtIkan->execute([$pesanan['id']]);
        $detailIkan = $stmtIkan->fetchAll();

        $stmtPerlengkapan = $pdo->prepare("
            SELECT 
                dpp.*,
                p.nama AS nama_perlengkapan,
                p.foto AS foto_perlengkapan,
                p.kategori AS kategori_perlengkapan
            FROM detail_pesanan_perlengkapan dpp
            LEFT JOIN perlengkapan p ON dpp.perlengkapan_id = p.id
            WHERE dpp.pesanan_id = ?
        ");
        $stmtPerlengkapan->execute([$pesanan['id']]);
        $detailPerlengkapan = $stmtPerlengkapan->fetchAll();
    }
}

function status_class_tracking($status)
{
    $status = strtolower($status);

    if ($status === 'pending') {
        return 'status-pending';
    }

    if ($status === 'diproses') {
        return 'status-diproses';
    }

    if ($status === 'dikirim') {
        return 'status-dikirim';
    }

    if ($status === 'selesai') {
        return 'status-selesai';
    }

    return 'status-pending';
}

function payment_status_class_tracking($status)
{
    $status = strtolower($status ?? 'belum bayar');

    if ($status === 'belum bayar') {
        return 'payment-unpaid';
    }

    if ($status === 'menunggu verifikasi') {
        return 'payment-waiting';
    }

    if ($status === 'terverifikasi') {
        return 'payment-verified';
    }

    if ($status === 'ditolak') {
        return 'payment-rejected';
    }

    return 'payment-unpaid';
}

function is_active_step($currentStatus, $step)
{
    $urutan = [
        'Pending' => 1,
        'Diproses' => 2,
        'Dikirim' => 3,
        'Selesai' => 4
    ];

    return ($urutan[$currentStatus] ?? 1) >= ($urutan[$step] ?? 1);
}

function is_image_tracking($file)
{
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true);
}

function tanggal_tracking($data)
{
    if (!empty($data['created_at'])) {
        return date('d M Y H:i', strtotime($data['created_at']));
    }

    return '-';
}

function status_desc_tracking($status)
{
    $deskripsi = [
        'Pending'  => 'Pesanan kamu sudah diterima dan sedang menunggu diproses toko.',
        'Diproses' => 'Toko sedang menyiapkan dan mengemas pesanan kamu.',
        'Dikirim'  => 'Pesanan sedang dalam perjalanan menuju alamat tujuan.',
        'Selesai'  => 'Pesanan telah selesai / sampai ke tujuan.',
    ];

    return $deskripsi[$status] ?? '';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Cek Pesanan - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=340">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
</head>

<body>

    <?php include "../components/navbar.php"; ?>

    <section class="checkout-section tracking-section">
        <div class="section-title">
            <span>Tracking Pesanan</span>
            <h2>Cek Status Pesanan</h2>
            <p>Masukkan nomor pesanan untuk melihat status pesanan, pembayaran, dan detail produk.</p>
        </div>

        <?php show_flash(); ?>

        <form method="GET" class="tracking-form">
            <input type="text" name="nomor" placeholder="Contoh: AQS-20260622-1234" value="<?= e($nomor) ?>" required>

            <button type="submit">
                Cek Pesanan
            </button>
        </form>

        <?php if ($nomor !== '' && !$pesanan): ?>
            <div class="tracking-empty">
                <div>🔎</div>
                <h2>Pesanan Tidak Ditemukan</h2>
                <p>Nomor pesanan yang kamu masukkan belum terdaftar.</p>
            </div>
        <?php endif; ?>

        <?php if ($pesanan): ?>
            <?php
            $statusPembayaran = $pesanan['status_pembayaran'] ?? 'Belum Bayar';
            $buktiPembayaran = $pesanan['bukti_pembayaran'] ?? '';
            $catatanPembayaran = $pesanan['catatan_pembayaran'] ?? '';
            ?>

            <div class="tracking-card">
                <div class="tracking-header">
                    <div>
                        <span>Nomor Pesanan</span>
                        <h2><?= e($pesanan['nomor_pesanan']) ?></h2>

                        <?php if (!empty($_SESSION['user'])): ?>
                            <a href="invoice.php?nomor=<?= urlencode($pesanan['nomor_pesanan']) ?>" class="mini-button tracking-invoice-link">
                                Lihat Invoice
                            </a>
                        <?php endif; ?>
                    </div>

                    <div class="status-badge <?= status_class_tracking($pesanan['status']) ?>">
                        <?= e($pesanan['status']) ?>
                    </div>
                </div>

                <div class="tracking-timeline">
                    <div class="timeline-step <?= is_active_step($pesanan['status'], 'Pending') ? 'active' : '' ?>">
                        <div class="dot"></div>
                        Pending
                    </div>

                    <div class="timeline-step <?= is_active_step($pesanan['status'], 'Diproses') ? 'active' : '' ?>">
                        <div class="dot"></div>
                        Diproses
                    </div>

                    <div class="timeline-step <?= is_active_step($pesanan['status'], 'Dikirim') ? 'active' : '' ?>">
                        <div class="dot"></div>
                        Dikirim
                    </div>

                    <div class="timeline-step <?= is_active_step($pesanan['status'], 'Selesai') ? 'active' : '' ?>">
                        <div class="dot"></div>
                        Selesai
                    </div>
                </div>

                <p class="tracking-status-desc">
                    <?php if ($pesanan['metode_pengiriman'] === 'Ambil Sendiri' && $pesanan['status'] === 'Dikirim'): ?>
                        Pesanan siap diambil di toko.
                    <?php else: ?>
                        <?= e(status_desc_tracking($pesanan['status'])) ?>
                    <?php endif; ?>
                </p>

                <div class="tracking-info-grid">
                    <div>
                        <b>Nama Pelanggan</b>
                        <span><?= e($pesanan['nama_pelanggan']) ?></span>
                    </div>

                    <div>
                        <b>No HP</b>
                        <span><?= e($pesanan['no_hp']) ?></span>
                    </div>

                    <div>
                        <b>Metode Pengiriman</b>
                        <span><?= e($pesanan['metode_pengiriman']) ?></span>
                    </div>

                    <div>
                        <b>Metode Pembayaran</b>
                        <span><?= e($pesanan['metode_bayar']) ?></span>
                    </div>

                    <div>
                        <b>Status Pembayaran</b>

                        <?php if ($pesanan['metode_bayar'] === 'COD'): ?>
                            <span class="payment-status payment-verified">COD</span>
                        <?php else: ?>
                            <span class="payment-status <?= payment_status_class_tracking($statusPembayaran) ?>">
                                <?= e($statusPembayaran) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div>
                        <b>Tanggal Pesanan</b>
                        <span><?= tanggal_tracking($pesanan) ?></span>
                    </div>

                    <div class="wide">
                        <b>Alamat</b>
                        <span><?= e($pesanan['alamat']) ?></span>
                    </div>

                    <div class="wide">
                        <b>Total Pembayaran</b>
                        <span class="total-price"><?= rupiah($pesanan['total_harga']) ?></span>
                    </div>
                </div>

                <?php if ($pesanan['metode_pengiriman'] === 'Kurir'): ?>
                    <div class="tracking-map-box">
                        <b>📍 Perkiraan Lokasi Tujuan</b>
                        <p class="tracking-map-note">
                            Peta di bawah ini menampilkan perkiraan lokasi berdasarkan alamat yang
                            dimasukkan saat checkout — bukan posisi kurir secara real-time, karena
                            AquaStore belum terhubung ke sistem pelacakan resmi jasa pengiriman.
                        </p>
                        <div id="trackingMap" data-alamat="<?= e($pesanan['alamat']) ?>"></div>
                    </div>
                <?php endif; ?>

                <div class="tracking-payment-box">
                    <div class="tracking-payment-header">
                        <div>
                            <span>Pembayaran</span>
                            <h3>Informasi Pembayaran</h3>
                        </div>

                        <?php if ($pesanan['metode_bayar'] === 'COD'): ?>
                            <b class="payment-status payment-verified">COD</b>
                        <?php else: ?>
                            <b class="payment-status <?= payment_status_class_tracking($statusPembayaran) ?>">
                                <?= e($statusPembayaran) ?>
                            </b>
                        <?php endif; ?>
                    </div>

                    <?php if ($pesanan['metode_bayar'] === 'COD'): ?>

                        <p class="tracking-payment-note">
                            Pesanan menggunakan metode COD. Pembayaran dilakukan saat pesanan diterima atau saat ambil sendiri
                            di toko.
                        </p>

                    <?php else: ?>

                        <?php if ($statusPembayaran === 'Belum Bayar'): ?>
                            <p class="tracking-payment-note">
                                Pembayaran belum dikirim. Silakan upload bukti pembayaran melalui halaman <b>Pesanan Saya</b>.
                            </p>

                        <?php elseif ($statusPembayaran === 'Menunggu Verifikasi'): ?>
                            <p class="tracking-payment-note">
                                Bukti pembayaran sudah dikirim dan sedang menunggu verifikasi admin.
                            </p>

                        <?php elseif ($statusPembayaran === 'Terverifikasi'): ?>
                            <p class="tracking-payment-note success">
                                Pembayaran sudah diverifikasi oleh admin.
                            </p>

                        <?php elseif ($statusPembayaran === 'Ditolak'): ?>
                            <p class="tracking-payment-note rejected">
                                Bukti pembayaran ditolak. Silakan upload ulang bukti pembayaran yang benar melalui halaman Pesanan
                                Saya.
                            </p>

                            <?php if (!empty($catatanPembayaran)): ?>
                                <div class="payment-reject-note customer-note">
                                    <b>Alasan Penolakan:</b>
                                    <p><?= e($catatanPembayaran) ?></p>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (!empty($buktiPembayaran)): ?>
                            <div class="tracking-proof-preview">
                                <?php if (is_image_tracking($buktiPembayaran)): ?>
                                    <a href="../uploads/bukti/<?= e($buktiPembayaran) ?>" target="_blank">
                                        <img src="../uploads/bukti/<?= e($buktiPembayaran) ?>" alt="Bukti pembayaran"
                                            class="tracking-proof-img">
                                    </a>
                                <?php else: ?>
                                    <a href="../uploads/bukti/<?= e($buktiPembayaran) ?>" target="_blank" class="proof-file-link">
                                        Lihat File Bukti Pembayaran
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($_SESSION['user']) && ($_SESSION['user']['id'] ?? null) == ($pesanan['pelanggan_id'] ?? null)): ?>
                            <a href="pesanan-saya.php" class="mini-button">
                                Upload / Kelola Bukti Pembayaran
                            </a>
                        <?php endif; ?>

                    <?php endif; ?>
                </div>

                <?php if ($detailIkan): ?>
                    <h3 class="tracking-subtitle">🐠 Ikan Hias</h3>

                    <div class="tracking-items">
                        <?php foreach ($detailIkan as $item): ?>
                            <div class="tracking-item">
                                <div class="tracking-img">
                                    <?php if (!empty($item['foto_ikan'])): ?>
                                        <img src="../uploads/ikan/<?= e($item['foto_ikan']) ?>" alt="<?= e($item['nama_ikan']) ?>">
                                    <?php else: ?>
                                        <span>🐠</span>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <h4><?= e($item['nama_ikan'] ?: 'Produk ikan tidak ditemukan') ?></h4>
                                    <p><?= $item['jumlah'] ?> x <?= rupiah($item['harga_satuan']) ?></p>
                                </div>

                                <b><?= rupiah($item['jumlah'] * $item['harga_satuan']) ?></b>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($detailPerlengkapan): ?>
                    <h3 class="tracking-subtitle">🛠️ Perlengkapan Aquarium</h3>

                    <div class="tracking-items">
                        <?php foreach ($detailPerlengkapan as $item): ?>
                            <div class="tracking-item">
                                <div class="tracking-img">
                                    <?php if (!empty($item['foto_perlengkapan'])): ?>
                                        <img src="../uploads/perlengkapan/<?= e($item['foto_perlengkapan']) ?>"
                                            alt="<?= e($item['nama_perlengkapan']) ?>">
                                    <?php else: ?>
                                        <span>🛠️</span>
                                    <?php endif; ?>
                                </div>

                                <div>
                                    <h4><?= e($item['nama_perlengkapan'] ?: 'Produk tidak ditemukan') ?></h4>

                                    <?php if (!empty($item['kategori_perlengkapan'])): ?>
                                        <p><?= e($item['kategori_perlengkapan']) ?></p>
                                    <?php endif; ?>

                                    <p><?= $item['jumlah'] ?> x <?= rupiah($item['harga_satuan']) ?></p>
                                </div>

                                <b><?= rupiah($item['jumlah'] * $item['harga_satuan']) ?></b>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (!$detailIkan && !$detailPerlengkapan): ?>
                    <div class="order-no-product">
                        Detail produk tidak ditemukan.
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </section>

    <script src="../assets/js/main.js"></script>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function () {
            var mapEl = document.getElementById('trackingMap');

            if (!mapEl) {
                return;
            }

            var alamat = mapEl.getAttribute('data-alamat') || '';

            mapEl.innerHTML = '<p class="tracking-map-loading">Memuat peta...</p>';

            fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(alamat))
                .then(function (res) { return res.json(); })
                .then(function (hasil) {
                    if (!hasil || !hasil.length) {
                        mapEl.innerHTML = '<p class="tracking-map-loading">Lokasi tidak dapat ditemukan di peta untuk alamat ini.</p>';
                        return;
                    }

                    var lat = parseFloat(hasil[0].lat);
                    var lon = parseFloat(hasil[0].lon);

                    mapEl.innerHTML = '';

                    var map = L.map(mapEl).setView([lat, lon], 14);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 18
                    }).addTo(map);

                    L.marker([lat, lon]).addTo(map)
                        .bindPopup('Perkiraan lokasi tujuan')
                        .openPopup();
                })
                .catch(function () {
                    mapEl.innerHTML = '<p class="tracking-map-loading">Gagal memuat peta. Coba muat ulang halaman.</p>';
                });
        })();
    </script>
</body>

</html>
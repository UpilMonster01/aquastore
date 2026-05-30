<?php
require "../config/db.php";
admin_only();

$status = $_GET['status'] ?? '';

if ($status !== '') {
    $stmt = $pdo->prepare("SELECT * FROM pesanan WHERE status = ? ORDER BY id DESC");
    $stmt->execute([$status]);
} else {
    $stmt = $pdo->query("SELECT * FROM pesanan ORDER BY id DESC");
}

$pesanan = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Pesanan</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=20">
</head>

<body>
    <div class="admin-layout">
        <?php include "sidebar.php"; ?>

        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <span>Admin</span>
                    <h1>Pesanan</h1>
                </div>
            </div>

            <?php show_flash(); ?>

            <div class="admin-panel">
                <form method="GET" class="admin-form">
                    <select name="status">
                        <option value="">Semua Status</option>

                        <?php foreach (['Pending', 'Diproses', 'Dikirim', 'Selesai'] as $s): ?>
                            <option value="<?= $s ?>" <?= $status === $s ? 'selected' : '' ?>>
                                <?= $s ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <button>Filter</button>
                </form>
            </div>

            <div class="admin-panel">
                <div class="table-box">
                    <table>
                        <tr>
                            <th>Nomor</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Detail</th>
                        </tr>

                        <?php foreach ($pesanan as $p): ?>
                            <tr>
                                <td><?= e($p['nomor_pesanan']) ?></td>

                                <td>
                                    <?= e($p['nama_pelanggan']) ?><br>
                                    <small><?= e($p['no_hp']) ?></small>
                                </td>

                                <td><?= rupiah($p['total_harga']) ?></td>

                                <td>
                                    <form action="../proses/update-status.php" method="POST">
                                        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">

                                        <select 
                                            name="status" 
                                            class="status-select status-<?= strtolower($p['status']) ?>"
                                            onchange="this.form.submit()"
                                        >
                                            <?php foreach (['Pending', 'Diproses', 'Dikirim', 'Selesai'] as $s): ?>
                                                <option value="<?= $s ?>" <?= $p['status'] === $s ? 'selected' : '' ?>>
                                                    <?= $s ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>

                                <td>
                                    <button class="mini-button" onclick="openModal('detail<?= $p['id'] ?>')">
                                        Lihat
                                    </button>
                                </td>
                            </tr>

                            <div class="modal" id="detail<?= $p['id'] ?>">
                                <div class="modal-box">
                                    <button class="close-btn" onclick="closeModal('detail<?= $p['id'] ?>')">×</button>

                                    <h2>Detail Pesanan</h2>

                                    <p><b>Nomor:</b> <?= e($p['nomor_pesanan']) ?></p>
                                    <p><b>Nama:</b> <?= e($p['nama_pelanggan']) ?></p>
                                    <p><b>No HP:</b> <?= e($p['no_hp']) ?></p>
                                    <p><b>Alamat:</b> <?= e($p['alamat']) ?></p>
                                    <p><b>Pengiriman:</b> <?= e($p['metode_pengiriman']) ?></p>
                                    <p><b>Pembayaran:</b> <?= e($p['metode_bayar']) ?></p>
                                    <p><b>Status:</b> <?= e($p['status']) ?></p>

                                    <br>

                                    <h3>🐠 Ikan Hias</h3>

                                    <div class="table-box">
                                        <table>
                                            <tr>
                                                <th>Ikan</th>
                                                <th>Jumlah</th>
                                                <th>Harga</th>
                                            </tr>

                                            <?php
                                            $d = $pdo->prepare("
                                                SELECT dp.*, i.nama 
                                                FROM detail_pesanan dp 
                                                JOIN ikan i ON dp.ikan_id = i.id 
                                                WHERE dp.pesanan_id = ?
                                            ");
                                            $d->execute([$p['id']]);
                                            $ikanDetail = $d->fetchAll();
                                            ?>

                                            <?php if ($ikanDetail): ?>
                                                <?php foreach ($ikanDetail as $item): ?>
                                                    <tr>
                                                        <td><?= e($item['nama']) ?></td>
                                                        <td><?= e($item['jumlah']) ?></td>
                                                        <td><?= rupiah($item['harga_satuan']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3">Tidak ada ikan.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </table>
                                    </div>

                                    <br>

                                    <h3>🛠️ Perlengkapan Aquarium</h3>

                                    <div class="table-box">
                                        <table>
                                            <tr>
                                                <th>Perlengkapan</th>
                                                <th>Jumlah</th>
                                                <th>Harga</th>
                                            </tr>

                                            <?php
                                            $alat = $pdo->prepare("
                                                SELECT dpp.*, p.nama
                                                FROM detail_pesanan_perlengkapan dpp
                                                JOIN perlengkapan p ON dpp.perlengkapan_id = p.id
                                                WHERE dpp.pesanan_id = ?
                                            ");
                                            $alat->execute([$p['id']]);
                                            $alatDetail = $alat->fetchAll();
                                            ?>

                                            <?php if ($alatDetail): ?>
                                                <?php foreach ($alatDetail as $item): ?>
                                                    <tr>
                                                        <td><?= e($item['nama']) ?></td>
                                                        <td><?= e($item['jumlah']) ?></td>
                                                        <td><?= rupiah($item['harga_satuan']) ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <tr>
                                                    <td colspan="3">Tidak ada perlengkapan.</td>
                                                </tr>
                                            <?php endif; ?>
                                        </table>
                                    </div>

                                    <br>
                                    <hr>
                                    <br>

                                    <h3>
                                        Total Pesanan:
                                        <?= rupiah($p['total_harga']) ?>
                                    </h3>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>

</html>
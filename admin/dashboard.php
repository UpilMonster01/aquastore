<?php
require "../config/db.php";
admin_only();

$totalIkan = $pdo->query("SELECT COUNT(*) FROM ikan")->fetchColumn() ?: 0;
$totalStokIkan = $pdo->query("SELECT SUM(stok) FROM ikan")->fetchColumn() ?: 0;

$totalPerlengkapan = $pdo->query("SELECT COUNT(*) FROM perlengkapan")->fetchColumn() ?: 0;
$totalStokPerlengkapan = $pdo->query("SELECT SUM(stok) FROM perlengkapan")->fetchColumn() ?: 0;

$totalPesanan = $pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn() ?: 0;
$pesananPending = $pdo->query("SELECT COUNT(*) FROM pesanan WHERE status='Pending'")->fetchColumn() ?: 0;

$pendapatan = $pdo->query("SELECT SUM(total_harga) FROM pesanan WHERE status='Selesai'")->fetchColumn() ?: 0;

$ikanTipis = $pdo->query("SELECT * FROM ikan WHERE stok < 5 ORDER BY stok ASC LIMIT 5")->fetchAll();

$alatTipis = $pdo->query("SELECT * FROM perlengkapan WHERE stok < 5 ORDER BY stok ASC LIMIT 5")->fetchAll();

$pesananBaru = $pdo->query("SELECT * FROM pesanan ORDER BY id DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=30">
</head>

<body>
    <div class="admin-layout">
        <?php include "sidebar.php"; ?>

        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <span>Admin Panel</span>
                    <h1>Dashboard AquaStore</h1>
                </div>

                <b><?= date('d M Y') ?></b>
            </div>

            <?php show_flash(); ?>

            <div class="admin-stats">
                <div class="dash-card">
                    <span>🐠</span>
                    <p>Total Ikan</p>
                    <h2><?= $totalIkan ?></h2>
                </div>

                <div class="dash-card">
                    <span>🛠️</span>
                    <p>Total Perlengkapan</p>
                    <h2><?= $totalPerlengkapan ?></h2>
                </div>

                <div class="dash-card">
                    <span>📦</span>
                    <p>Total Pesanan</p>
                    <h2><?= $totalPesanan ?></h2>
                </div>

                <div class="dash-card">
                    <span>💰</span>
                    <p>Pendapatan</p>
                    <h2><?= rupiah($pendapatan) ?></h2>
                </div>
            </div>

            <div class="admin-stats">
                <div class="dash-card">
                    <span>📊</span>
                    <p>Stok Ikan</p>
                    <h2><?= $totalStokIkan ?></h2>
                </div>

                <div class="dash-card">
                    <span>📚</span>
                    <p>Stok Perlengkapan</p>
                    <h2><?= $totalStokPerlengkapan ?></h2>
                </div>

                <div class="dash-card">
                    <span>⏳</span>
                    <p>Pesanan Pending</p>
                    <h2><?= $pesananPending ?></h2>
                </div>

                <div class="dash-card">
                    <span>🧾</span>
                    <p>Status Sistem</p>
                    <h2>Aktif</h2>
                </div>
            </div>

            <div class="admin-grid">
                <div class="admin-panel">
                    <h2>🐠 Stok Ikan Menipis</h2>

                    <div class="table-box">
                        <table>
                            <tr>
                                <th>Nama</th>
                                <th>Stok</th>
                                <th>Status</th>
                            </tr>

                            <?php if ($ikanTipis): ?>
                                <?php foreach ($ikanTipis as $i): ?>
                                    <tr>
                                        <td><?= e($i['nama']) ?></td>
                                        <td><b style="color:red;"><?= e($i['stok']) ?></b></td>
                                        <td><?= e($i['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">Stok ikan aman.</td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>

                <div class="admin-panel">
                    <h2>🛠️ Stok Perlengkapan Menipis</h2>

                    <div class="table-box">
                        <table>
                            <tr>
                                <th>Nama</th>
                                <th>Stok</th>
                                <th>Status</th>
                            </tr>

                            <?php if ($alatTipis): ?>
                                <?php foreach ($alatTipis as $a): ?>
                                    <tr>
                                        <td><?= e($a['nama']) ?></td>
                                        <td><b style="color:red;"><?= e($a['stok']) ?></b></td>
                                        <td><?= e($a['status']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="3">Stok perlengkapan aman.</td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>

            <div class="admin-panel">
                <h2>📦 Pesanan Terbaru</h2>

                <div class="table-box">
                    <table>
                        <tr>
                            <th>Nomor</th>
                            <th>Pelanggan</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>

                        <?php if ($pesananBaru): ?>
                            <?php foreach ($pesananBaru as $p): ?>
                                <tr>
                                    <td><?= e($p['nomor_pesanan']) ?></td>
                                    <td><?= e($p['nama_pelanggan']) ?></td>
                                    <td><?= rupiah($p['total_harga']) ?></td>
                                    <td><?= e($p['status']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">Belum ada pesanan.</td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
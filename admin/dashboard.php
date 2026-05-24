<?php require "../config/db.php";
admin_only();
$totalIkan = $pdo->query("SELECT COUNT(*) FROM ikan")->fetchColumn();
$totalStok = $pdo->query("SELECT SUM(stok) FROM ikan")->fetchColumn() ?: 0;
$totalPesanan = $pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn() ?: 0;
$pendapatan = $pdo->query("SELECT SUM(total_harga) FROM pesanan WHERE status='Selesai'")->fetchColumn() ?: 0;
$stokTipis = $pdo->query("SELECT * FROM ikan WHERE stok < 5 ORDER BY stok ASC LIMIT 5")->fetchAll();
$pesananBaru = $pdo->query("SELECT * FROM pesanan ORDER BY id DESC LIMIT 5")->fetchAll();
?><!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="admin-layout"><?php include "sidebar.php"; ?>
        <main class="admin-content">
            <div class="admin-header">
                <div><span>Selamat Datang</span>
                    <h1>Dashboard AquaStore</h1>
                </div><b><?= e($_SESSION['admin']['nama']) ?></b>
            </div><?php show_flash(); ?>
            <div class="admin-stats">
                <div class="dash-card"><span>🐠</span>
                    <p>Total Ikan</p>
                    <h2><?= $totalIkan ?></h2>
                </div>
                <div class="dash-card"><span>📦</span>
                    <p>Total Stok</p>
                    <h2><?= $totalStok ?></h2>
                </div>
                <div class="dash-card"><span>🛒</span>
                    <p>Total Pesanan</p>
                    <h2><?= $totalPesanan ?></h2>
                </div>
                <div class="dash-card"><span>💰</span>
                    <p>Pendapatan</p>
                    <h2><?= rupiah($pendapatan) ?></h2>
                </div>
            </div>
            <div class="admin-grid">
                <div class="admin-panel">
                    <h2>Stok Menipis</h2>
                    <div class="table-box">
                        <table>
                            <tr>
                                <th>Ikan</th>
                                <th>Stok</th>
                                <th>Status</th>
                            </tr><?php foreach ($stokTipis as $i): ?>
                                <tr>
                                    <td><?= e($i['nama']) ?></td>
                                    <td><?= e($i['stok']) ?></td>
                                    <td><?= e($i['status']) ?></td>
                                </tr><?php endforeach; ?>
                        </table>
                    </div>
                </div>
                <div class="admin-panel">
                    <h2>Pesanan Terbaru</h2>
                    <div class="table-box">
                        <table>
                            <tr>
                                <th>Nomor</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr><?php foreach ($pesananBaru as $p): ?>
                                <tr>
                                    <td><?= e($p['nomor_pesanan']) ?></td>
                                    <td><?= rupiah($p['total_harga']) ?></td>
                                    <td><?= e($p['status']) ?></td>
                                </tr><?php endforeach; ?>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
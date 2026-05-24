<?php require "../config/db.php";
admin_only();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['tambah'])) {
        $stmt = $pdo->prepare("INSERT INTO pengeluaran (kategori,keterangan,jumlah,tanggal) VALUES (?,?,?,?)");
        $stmt->execute([$_POST['kategori'], trim($_POST['keterangan']), (int) $_POST['jumlah'], $_POST['tanggal']]);
        flash('success', 'Pengeluaran ditambahkan.');
    }
    if (isset($_POST['hapus'])) {
        $stmt = $pdo->prepare("DELETE FROM pengeluaran WHERE id=?");
        $stmt->execute([(int) $_POST['id']]);
    }
    header("Location: keuangan.php");
    exit;
}
$pendapatan = $pdo->query("SELECT SUM(total_harga) FROM pesanan WHERE status='Selesai'")->fetchColumn() ?: 0;
$pengeluaran = $pdo->query("SELECT SUM(jumlah) FROM pengeluaran")->fetchColumn() ?: 0;
$data = $pdo->query("SELECT * FROM pengeluaran ORDER BY tanggal DESC")->fetchAll();
?><!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Keuangan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="admin-layout"><?php include "sidebar.php"; ?>
        <main class="admin-content">
            <div class="admin-header">
                <div><span>Admin</span>
                    <h1>Keuangan</h1>
                </div>
            </div><?php show_flash(); ?>
            <div class="admin-stats">
                <div class="dash-card"><span>💵</span>
                    <p>Pendapatan</p>
                    <h2><?= rupiah($pendapatan) ?></h2>
                </div>
                <div class="dash-card"><span>📉</span>
                    <p>Pengeluaran</p>
                    <h2><?= rupiah($pengeluaran) ?></h2>
                </div>
                <div class="dash-card"><span>📊</span>
                    <p>Bersih</p>
                    <h2><?= rupiah($pendapatan - $pengeluaran) ?></h2>
                </div>
            </div>
            <div class="admin-panel">
                <h2>Tambah Pengeluaran</h2>
                <form method="POST" class="admin-form"><input type="hidden" name="csrf"
                        value="<?= csrf_token() ?>"><select name="kategori">
                        <option>Pakan</option>
                        <option>Obat</option>
                        <option>Aksesori</option>
                        <option>Listrik</option>
                        <option>Lainnya</option>
                    </select><input type="text" name="keterangan" placeholder="Keterangan"><input type="number"
                        name="jumlah" placeholder="Jumlah" required><input type="date" name="tanggal" required><button
                        name="tambah">Simpan</button></form>
            </div>
            <div class="admin-panel">
                <h2>Riwayat</h2>
                <div class="table-box">
                    <table>
                        <tr>
                            <th>Tanggal</th>
                            <th>Kategori</th>
                            <th>Keterangan</th>
                            <th>Jumlah</th>
                            <th>Aksi</th>
                        </tr><?php foreach ($data as $r): ?>
                            <tr>
                                <td><?= e($r['tanggal']) ?></td>
                                <td><?= e($r['kategori']) ?></td>
                                <td><?= e($r['keterangan']) ?></td>
                                <td><?= rupiah($r['jumlah']) ?></td>
                                <td>
                                    <form method="POST"><input type="hidden" name="csrf" value="<?= csrf_token() ?>"><input
                                            type="hidden" name="id" value="<?= $r['id'] ?>"><button class="delete-button"
                                            name="hapus">Hapus</button></form>
                                </td>
                            </tr><?php endforeach; ?>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>
<?php require "../config/db.php";
admin_only();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (isset($_POST['tambah'])) {
        $stmt = $pdo->prepare("INSERT INTO perawatan (tank_id,nama_kegiatan,jadwal,status) VALUES (?,?,?,'Pending')");
        $stmt->execute([$_POST['tank_id'], trim($_POST['nama_kegiatan']), $_POST['jadwal']]);
        flash('success', 'Jadwal ditambahkan.');
    }
    if (isset($_POST['selesai'])) {
        $stmt = $pdo->prepare("UPDATE perawatan SET status='Selesai' WHERE id=?");
        $stmt->execute([(int) $_POST['id']]);
    }
    if (isset($_POST['hapus'])) {
        $stmt = $pdo->prepare("DELETE FROM perawatan WHERE id=?");
        $stmt->execute([(int) $_POST['id']]);
    }
    header("Location: perawatan.php");
    exit;
}
$tank = $pdo->query("SELECT * FROM tank")->fetchAll();
$data = $pdo->query("SELECT p.*,t.nama AS tank FROM perawatan p LEFT JOIN tank t ON p.tank_id=t.id ORDER BY p.jadwal DESC")->fetchAll();
?><!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Perawatan</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="admin-layout"><?php include "sidebar.php"; ?>
        <main class="admin-content">
            <div class="admin-header">
                <div><span>Admin</span>
                    <h1>Perawatan Tank</h1>
                </div>
            </div><?php show_flash(); ?>
            <div class="admin-panel">
                <h2>Tambah Jadwal</h2>
                <form method="POST" class="admin-form"><input type="hidden" name="csrf"
                        value="<?= csrf_token() ?>"><select name="tank_id"><?php foreach ($tank as $t): ?>
                            <option value="<?= $t['id'] ?>"><?= e($t['nama']) ?></option><?php endforeach; ?>
                    </select><input type="text" name="nama_kegiatan" placeholder="Ganti air 30%" required><input
                        type="date" name="jadwal" required><button name="tambah">Tambah</button></form>
            </div>
            <div class="admin-panel">
                <h2>Daftar Perawatan</h2>
                <div class="table-box">
                    <table>
                        <tr>
                            <th>Tank</th>
                            <th>Kegiatan</th>
                            <th>Jadwal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr><?php foreach ($data as $p): ?>
                            <tr>
                                <td><?= e($p['tank']) ?></td>
                                <td><?= e($p['nama_kegiatan']) ?></td>
                                <td><?= e($p['jadwal']) ?></td>
                                <td><?= e($p['status']) ?></td>
                                <td><?php if ($p['status'] === 'Pending'): ?>
                                        <form method="POST" style="display:inline"><input type="hidden" name="csrf"
                                                value="<?= csrf_token() ?>"><input type="hidden" name="id"
                                                value="<?= $p['id'] ?>"><button class="mini-button"
                                                name="selesai">Selesai</button></form><?php endif; ?>
                                    <form method="POST" style="display:inline"><input type="hidden" name="csrf"
                                            value="<?= csrf_token() ?>"><input type="hidden" name="id"
                                            value="<?= $p['id'] ?>"><button class="delete-button"
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
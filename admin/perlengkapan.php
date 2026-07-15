<?php
require "../config/db.php";
admin_only();

define('AQUASTORE_ADMIN_VIEW', true);

$data = $pdo->query("SELECT * FROM perlengkapan ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Perlengkapan - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=10">
</head>
<body>

<div class="admin-layout">
<?php include "sidebar.php"; ?>

<main class="admin-content">
    <div class="admin-header">
        <div>
            <span>Admin</span>
            <h1>Data Perlengkapan</h1>
        </div>
        <button class="mini-button" onclick="openModal('modalTambah')">+ Tambah</button>
    </div>

    <?php show_flash(); ?>

    <div class="table-box">
        <table>
            <tr>
                <th>Foto</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>

            <?php foreach($data as $p): ?>
            <tr>
                <td>
                    <?php if($p['foto']): ?>
                        <img src="../uploads/perlengkapan/<?= e($p['foto']) ?>?v=<?= time() ?>" class="thumb">
                    <?php else: ?>
                        <span class="emoji-table">🛠️</span>
                    <?php endif; ?>
                </td>
                <td><?= e($p['nama']) ?></td>
                <td><?= e($p['kategori']) ?></td>
                <td><?= rupiah($p['harga']) ?></td>
                <td><?= e($p['stok']) ?></td>
                <td><?= e($p['status']) ?></td>
                <td>
                    <button class="mini-button" onclick="openModal('edit<?= $p['id'] ?>')">Edit</button>

                    <form action="../proses/hapus-perlengkapan.php" method="POST" class="action-form-inline" onsubmit="return confirm('Hapus data ini?')">
                        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <button class="delete-button">Hapus</button>
                    </form>
                </td>
            </tr>

            <div class="modal" id="edit<?= $p['id'] ?>">
                <div class="modal-box">
                    <button class="close-btn" onclick="closeModal('edit<?= $p['id'] ?>')">×</button>
                    <h2>Edit Perlengkapan</h2>

                    <form action="../proses/edit-perlengkapan.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <?php $item = $p; include "form-perlengkapan.php"; ?>
                        <button class="login-button">Simpan</button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </table>
    </div>
</main>
</div>

<div class="modal" id="modalTambah">
    <div class="modal-box">
        <button class="close-btn" onclick="closeModal('modalTambah')">×</button>
        <h2>Tambah Perlengkapan</h2>

        <form action="../proses/tambah-perlengkapan.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <?php $item = []; include "form-perlengkapan.php"; ?>
            <button class="login-button">Tambah</button>
        </form>
    </div>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>
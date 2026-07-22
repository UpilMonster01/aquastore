<?php
require "../config/db.php";
admin_only();

define('AQUASTORE_ADMIN_VIEW', true);

$cari = trim($_GET['cari'] ?? '');
$filterKategori = trim($_GET['kategori'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');

$kategoriOptions = ['Pakan', 'Filter', 'Aerator', 'Heater', 'Obat', 'Lampu', 'Substrate', 'Dekorasi', 'Lainnya'];
$statusOptions = ['Tersedia', 'Habis'];

if (!in_array($filterKategori, $kategoriOptions, true)) {
    $filterKategori = '';
}

if (!in_array($filterStatus, $statusOptions, true)) {
    $filterStatus = '';
}

$where = [];
$params = [];

if ($cari !== '') {
    $where[] = "(nama LIKE ? OR deskripsi LIKE ?)";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
}

if ($filterKategori !== '') {
    $where[] = "kategori = ?";
    $params[] = $filterKategori;
}

if ($filterStatus !== '') {
    $where[] = "status = ?";
    $params[] = $filterStatus;
}

$sql = "SELECT * FROM perlengkapan";

if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll();
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

    <div class="admin-panel">
        <form method="GET" class="admin-form">
            <input type="text" name="cari" placeholder="Cari nama/deskripsi perlengkapan..." value="<?= e($cari) ?>">

            <select name="kategori">
                <option value="">Semua Kategori</option>
                <?php foreach ($kategoriOptions as $opt): ?>
                    <option value="<?= e($opt) ?>" <?= $filterKategori === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                <?php endforeach; ?>
            </select>

            <select name="status">
                <option value="">Semua Status</option>
                <?php foreach ($statusOptions as $opt): ?>
                    <option value="<?= e($opt) ?>" <?= $filterStatus === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                <?php endforeach; ?>
            </select>

            <button>Cari</button>

            <?php if ($cari !== '' || $filterKategori !== '' || $filterStatus !== ''): ?>
                <a href="perlengkapan.php" class="mini-button">Reset</a>
            <?php endif; ?>
        </form>
    </div>

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

            <?php if (!$data): ?>
                <tr>
                    <td colspan="7">Tidak ada perlengkapan yang cocok dengan pencarian/filter.</td>
                </tr>
            <?php endif; ?>

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
            <?php endforeach; ?>
        </table>
    </div>
</main>
</div>

<!-- MODAL EDIT (di luar <table>, sama seperti perbaikan di ikan.php) -->
<?php foreach ($data as $p): ?>
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
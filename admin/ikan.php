<?php
require "../config/db.php";

admin_only();

define('AQUASTORE_ADMIN_VIEW', true);

$cari = trim($_GET['cari'] ?? '');
$filterAir = trim($_GET['kategori_air'] ?? '');
$filterSifat = trim($_GET['kategori_sifat'] ?? '');
$filterJenis = trim($_GET['kategori_jenis'] ?? '');
$filterPerawatan = trim($_GET['tingkat_perawatan'] ?? '');
$filterStatus = trim($_GET['status'] ?? '');

$airOptions = ['Laut', 'Tawar', 'Payau'];
$sifatOptions = ['Predator', 'Non-Predator'];
$jenisOptions = ['Hias', 'Konsumsi', 'Langka'];
$perawatanOptions = ['Mudah', 'Sedang', 'Sulit'];
$statusOptions = ['Tersedia', 'Habis', 'Pre-order'];

if (!in_array($filterAir, $airOptions, true)) {
    $filterAir = '';
}

if (!in_array($filterSifat, $sifatOptions, true)) {
    $filterSifat = '';
}

if (!in_array($filterJenis, $jenisOptions, true)) {
    $filterJenis = '';
}

if (!in_array($filterPerawatan, $perawatanOptions, true)) {
    $filterPerawatan = '';
}

if (!in_array($filterStatus, $statusOptions, true)) {
    $filterStatus = '';
}

$where = [];
$params = [];

if ($cari !== '') {
    $where[] = "(nama LIKE ? OR nama_latin LIKE ? OR deskripsi LIKE ?)";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
    $params[] = "%$cari%";
}

if ($filterAir !== '') {
    $where[] = "kategori_air = ?";
    $params[] = $filterAir;
}

if ($filterSifat !== '') {
    $where[] = "kategori_sifat = ?";
    $params[] = $filterSifat;
}

if ($filterJenis !== '') {
    $where[] = "kategori_jenis = ?";
    $params[] = $filterJenis;
}

if ($filterPerawatan !== '') {
    $where[] = "tingkat_perawatan = ?";
    $params[] = $filterPerawatan;
}

if ($filterStatus !== '') {
    $where[] = "status = ?";
    $params[] = $filterStatus;
}

$sql = "SELECT * FROM ikan";

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
    <title>Data Ikan - AquaStore</title>

    <link rel="stylesheet" href="../assets/css/style.css?v=20">
</head>

<body>

    <div class="admin-layout"><?php include "sidebar.php"; ?>

        <!-- CONTENT -->

        <main class="admin-content">

            <div class="admin-header">

                <div>
                    <span>Admin</span>
                    <h1>Data Ikan</h1>
                </div>

                <button class="mini-button" onclick="openModal('modalTambah')">
                    + Tambah Ikan
                </button>

            </div>

            <?php show_flash(); ?>

            <!-- SEARCH & FILTER -->

            <div class="admin-panel">

                <form method="GET" class="admin-form">

                    <input type="text" name="cari" placeholder="Cari nama/nama latin/deskripsi..." value="<?= e($cari) ?>">

                    <select name="kategori_air">
                        <option value="">Semua Air</option>
                        <?php foreach ($airOptions as $opt): ?>
                            <option value="<?= e($opt) ?>" <?= $filterAir === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="kategori_sifat">
                        <option value="">Semua Sifat</option>
                        <?php foreach ($sifatOptions as $opt): ?>
                            <option value="<?= e($opt) ?>" <?= $filterSifat === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="kategori_jenis">
                        <option value="">Semua Jenis</option>
                        <?php foreach ($jenisOptions as $opt): ?>
                            <option value="<?= e($opt) ?>" <?= $filterJenis === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="tingkat_perawatan">
                        <option value="">Semua Perawatan</option>
                        <?php foreach ($perawatanOptions as $opt): ?>
                            <option value="<?= e($opt) ?>" <?= $filterPerawatan === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select name="status">
                        <option value="">Semua Status</option>
                        <?php foreach ($statusOptions as $opt): ?>
                            <option value="<?= e($opt) ?>" <?= $filterStatus === $opt ? 'selected' : '' ?>><?= e($opt) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <button>Cari</button>

                    <?php if ($cari !== '' || $filterAir !== '' || $filterSifat !== '' || $filterJenis !== '' || $filterPerawatan !== '' || $filterStatus !== ''): ?>
                        <a href="ikan.php" class="mini-button">Reset</a>
                    <?php endif; ?>

                </form>

            </div>

            <!-- TABLE -->

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
                            <td colspan="7">Tidak ada ikan yang cocok dengan pencarian/filter.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($data as $i): ?>

                        <tr>

                            <td>

                                <?php if (!empty($i['foto'])): ?>

                                    <img src="../uploads/ikan/<?= e($i['foto']) ?>?v=<?= time() ?>" class="thumb">

                                <?php else: ?>

                                    <div class="emoji-table">

                                        <?= $i['kategori_sifat'] == 'Predator'
                                            ? '🦈'
                                            : '🐠'
                                            ?>

                                    </div>

                                <?php endif; ?>

                            </td>

                            <td>

                                <b><?= e($i['nama']) ?></b>

                                <br>

                                <small>
                                    <?= e($i['nama_latin']) ?>
                                </small>

                            </td>

                            <td>

                                <?= e($i['kategori_air']) ?>

                                <br>

                                <small>
                                    <?= e($i['kategori_sifat']) ?>
                                </small>

                            </td>

                            <td>
                                <?= rupiah($i['harga']) ?>
                            </td>

                            <td>
                                <?= e($i['stok']) ?>
                            </td>

                            <td>
                                <?= e($i['status']) ?>
                            </td>

                            <td>

                                <button class="mini-button" onclick="openModal('edit<?= $i['id'] ?>')">
                                    Edit
                                </button>

                                <?php if (!empty($i['foto'])): ?>
                                    <form action="../proses/hapus-foto-ikan.php" method="POST" class="action-form-inline"
                                        onsubmit="return confirm('Hapus foto ikan ini?')">
                                        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
                                        <input type="hidden" name="id" value="<?= $i['id'] ?>">

                                        <button class="delete-button">
                                            Hapus Foto
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form action="../proses/hapus-ikan.php" method="POST" class="action-form-inline"
                                    onsubmit="return confirm('Hapus ikan ini?')">

                                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                                    <input type="hidden" name="id" value="<?= $i['id'] ?>">

                                    <button class="delete-button">
                                        Hapus
                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </table>

            </div>

        </main>

    </div>

    <!-- MODAL EDIT (di luar <table> — div di dalam <table> bukan anak yang
         valid, browser akan otomatis memindahkannya sehingga tampilan modal
         bisa rusak/tidak konsisten) -->

    <?php foreach ($data as $i): ?>

        <div class="modal" id="edit<?= $i['id'] ?>">

            <div class="modal-box">

                <button class="close-btn" onclick="closeModal('edit<?= $i['id'] ?>')">
                    ×
                </button>

                <h2>Edit Ikan</h2>

                <form action="../proses/edit-ikan.php" method="POST" enctype="multipart/form-data">

                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                    <input type="hidden" name="id" value="<?= $i['id'] ?>">

                    <?php include "form-ikan.php"; ?>

                    <button class="login-button">
                        Simpan Perubahan
                    </button>

                </form>

            </div>

        </div>

    <?php endforeach; ?>

    <!-- MODAL TAMBAH -->

    <div class="modal" id="modalTambah">

        <div class="modal-box">

            <button class="close-btn" onclick="closeModal('modalTambah')">
                ×
            </button>

            <h2>Tambah Ikan</h2>

            <?php $i = []; ?>

            <form action="../proses/tambah-ikan.php" method="POST" enctype="multipart/form-data">

                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                <?php include "form-ikan.php"; ?>

                <button class="login-button">
                    Tambah Ikan
                </button>

            </form>

        </div>

    </div>

    <script src="../assets/js/main.js"></script>

</body>

</html>
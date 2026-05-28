<?php
require "../config/db.php";

admin_only();

$cari = trim($_GET['cari'] ?? '');

if ($cari != '') {

    $stmt = $pdo->prepare("
        SELECT * FROM ikan
        WHERE 
            nama LIKE ?
            OR nama_latin LIKE ?
            OR kategori_air LIKE ?
            OR kategori_sifat LIKE ?
        ORDER BY id DESC
    ");

    $stmt->execute([
        "%$cari%",
        "%$cari%",
        "%$cari%",
        "%$cari%"
    ]);

} else {

    $stmt = $pdo->query("
        SELECT * FROM ikan
        ORDER BY id DESC
    ");
}

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

            <!-- SEARCH -->

            <div class="admin-panel">

                <form method="GET" class="admin-form">

                    <input type="text" name="cari" placeholder="Cari ikan..." value="<?= e($cari) ?>">

                    <button>Cari</button>

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

                                <form action="../proses/hapus-ikan.php" method="POST" style="display:inline-block;"
                                    onsubmit="return confirm('Hapus ikan ini?')">

                                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                                    <input type="hidden" name="id" value="<?= $i['id'] ?>">

                                    <button class="delete-button">
                                        Hapus
                                    </button>

                                </form>

                            </td>

                        </tr>

                        <!-- MODAL EDIT -->

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

                </table>

            </div>

        </main>

    </div>

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
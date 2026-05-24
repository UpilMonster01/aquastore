<?php
require "../config/db.php";
admin_only();

$id = (int)($_POST['id'] ?? 0);

$get = $pdo->prepare("SELECT foto FROM ikan WHERE id = ?");
$get->execute([$id]);
$ikan = $get->fetch();

if (!$ikan) {
    flash('error', 'Data ikan tidak ditemukan.');
    header("Location: ../admin/ikan.php");
    exit;
}

$foto = $ikan['foto'];
$uploadDir = __DIR__ . "/../uploads/ikan/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        flash('error', 'Format foto harus JPG, PNG, JPEG, atau WEBP.');
        header("Location: ../admin/ikan.php");
        exit;
    }

    if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
        flash('error', 'Ukuran foto maksimal 2MB.');
        header("Location: ../admin/ikan.php");
        exit;
    }

    $fotoBaru = uniqid('ikan_', true) . '.' . $ext;
    $targetPath = $uploadDir . $fotoBaru;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $targetPath)) {
        if (!empty($foto)) {
            $fotoLamaPath = $uploadDir . $foto;
            if (file_exists($fotoLamaPath)) {
                unlink($fotoLamaPath);
            }
        }

        $foto = $fotoBaru;
    } else {
        flash('error', 'Gagal upload foto baru.');
        header("Location: ../admin/ikan.php");
        exit;
    }
}

$update = $pdo->prepare("
    UPDATE ikan SET
        nama = ?,
        nama_latin = ?,
        kategori_air = ?,
        kategori_sifat = ?,
        kategori_jenis = ?,
        harga = ?,
        stok = ?,
        ukuran_cm = ?,
        tingkat_perawatan = ?,
        foto = ?,
        deskripsi = ?,
        status = ?
    WHERE id = ?
");

$update->execute([
    trim($_POST['nama'] ?? ''),
    trim($_POST['nama_latin'] ?? ''),
    $_POST['kategori_air'] ?? 'Tawar',
    $_POST['kategori_sifat'] ?? 'Non-Predator',
    $_POST['kategori_jenis'] ?? 'Hias',
    (int)($_POST['harga'] ?? 0),
    (int)($_POST['stok'] ?? 0),
    $_POST['ukuran_cm'] ?? 0,
    $_POST['tingkat_perawatan'] ?? 'Mudah',
    $foto,
    trim($_POST['deskripsi'] ?? ''),
    $_POST['status'] ?? 'Tersedia',
    $id
]);

flash('success', 'Data ikan berhasil diperbarui.');
header("Location: ../admin/ikan.php?v=" . time());
exit;

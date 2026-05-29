<?php
require "../config/db.php";
admin_only();

$id = (int)($_POST['id'] ?? 0);

$uploadDir = "../uploads/perlengkapan/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$get = $pdo->prepare("SELECT foto FROM perlengkapan WHERE id=?");
$get->execute([$id]);
$data = $get->fetch();

if (!$data) {
    flash('error', 'Data perlengkapan tidak ditemukan.');
    header("Location: ../admin/perlengkapan.php");
    exit;
}

$foto = $data['foto'];

if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowed) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
        $fotoBaru = uniqid('alat_', true) . "." . $ext;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fotoBaru)) {
            if (!empty($foto) && file_exists($uploadDir . $foto)) {
                unlink($uploadDir . $foto);
            }

            $foto = $fotoBaru;
        }
    }
}

$update = $pdo->prepare("
    UPDATE perlengkapan SET
        nama=?,
        kategori=?,
        harga=?,
        stok=?,
        foto=?,
        deskripsi=?,
        status=?
    WHERE id=?
");

$update->execute([
    trim($_POST['nama']),
    $_POST['kategori'],
    (int)$_POST['harga'],
    (int)$_POST['stok'],
    $foto,
    trim($_POST['deskripsi']),
    $_POST['status'],
    $id
]);

flash('success', 'Perlengkapan berhasil diperbarui.');
header("Location: ../admin/perlengkapan.php?v=" . time());
exit;
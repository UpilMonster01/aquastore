<?php
require "../config/db.php";
admin_only();

$uploadDir = "../uploads/perlengkapan/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$foto = "";

if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowed) && $_FILES['foto']['size'] <= 2 * 1024 * 1024) {
        $foto = uniqid('alat_', true) . "." . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $foto);
    }
}

$stmt = $pdo->prepare("
    INSERT INTO perlengkapan
    (nama, kategori, harga, stok, foto, deskripsi, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    trim($_POST['nama']),
    $_POST['kategori'],
    (int)$_POST['harga'],
    (int)$_POST['stok'],
    $foto,
    trim($_POST['deskripsi']),
    $_POST['status']
]);

flash('success', 'Perlengkapan berhasil ditambahkan.');
header("Location: ../admin/perlengkapan.php");
exit;
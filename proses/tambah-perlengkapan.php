<?php
require "../config/db.php";
admin_only();
csrf_check();

$uploadDir = "../uploads/perlengkapan/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$foto = "";

if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if ($_FILES['foto']['size'] <= 2 * 1024 * 1024 && class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['foto']['tmp_name']);

        if (isset($allowedMime[$mime]) && @getimagesize($_FILES['foto']['tmp_name']) !== false) {
            $ext = $allowedMime[$mime];
            $foto = uniqid('alat_', true) . "." . $ext;
            move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $foto);
        } else {
            flash('error', 'Format foto harus JPG, PNG, atau WEBP yang valid.');
            header("Location: ../admin/perlengkapan.php");
            exit;
        }
    }
}

$stmt = $pdo->prepare("
    INSERT INTO perlengkapan
    (nama, kategori, harga, stok, foto, deskripsi, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    trim($_POST['nama']),
    pilih_valid($_POST['kategori'] ?? '', ['Pakan', 'Filter', 'Aerator', 'Heater', 'Obat', 'Lampu', 'Substrate', 'Dekorasi', 'Lainnya'], 'Lainnya'),
    (int)$_POST['harga'],
    (int)$_POST['stok'],
    $foto,
    trim($_POST['deskripsi']),
    pilih_valid($_POST['status'] ?? '', ['Tersedia', 'Habis'], 'Tersedia')
]);

flash('success', 'Perlengkapan berhasil ditambahkan.');
header("Location: ../admin/perlengkapan.php");
exit;
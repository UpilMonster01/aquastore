<?php
require "../config/db.php";
admin_only();
csrf_check();

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
    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if ($_FILES['foto']['size'] > 2 * 1024 * 1024 || !class_exists('finfo')) {
        flash('error', 'Ukuran foto maksimal 2MB atau server tidak mendukung pemeriksaan file.');
        header("Location: ../admin/perlengkapan.php");
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['foto']['tmp_name']);

    if (!isset($allowedMime[$mime]) || @getimagesize($_FILES['foto']['tmp_name']) === false) {
        flash('error', 'Format foto harus JPG, PNG, atau WEBP yang valid.');
        header("Location: ../admin/perlengkapan.php");
        exit;
    }

    $ext = $allowedMime[$mime];
    $fotoBaru = uniqid('alat_', true) . "." . $ext;

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . $fotoBaru)) {
        if (!empty($foto) && file_exists($uploadDir . $foto)) {
            unlink($uploadDir . $foto);
        }

        $foto = $fotoBaru;
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
    pilih_valid($_POST['kategori'] ?? '', ['Pakan', 'Filter', 'Aerator', 'Heater', 'Obat', 'Lampu', 'Substrate', 'Dekorasi', 'Lainnya'], 'Lainnya'),
    (int)$_POST['harga'],
    (int)$_POST['stok'],
    $foto,
    trim($_POST['deskripsi']),
    pilih_valid($_POST['status'] ?? '', ['Tersedia', 'Habis'], 'Tersedia'),
    $id
]);

flash('success', 'Perlengkapan berhasil diperbarui.');

$kembali = safe_redirect_url($_POST['kembali'] ?? '', url('admin/perlengkapan.php?v=' . time()));
redirect_to($kembali);
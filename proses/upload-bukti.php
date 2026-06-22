<?php
require "../config/db.php";

if (empty($_SESSION['user'])) {
    flash('error', 'Silakan login terlebih dahulu.');
    header("Location: ../pelanggan/login.php");
    exit;
}

$userId = $_SESSION['user']['id'];
$pesananId = (int)($_POST['pesanan_id'] ?? 0);

if ($pesananId <= 0) {
    flash('error', 'Pesanan tidak valid.');
    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT *
    FROM pesanan
    WHERE id = ? AND pelanggan_id = ?
");
$stmt->execute([$pesananId, $userId]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    flash('error', 'Pesanan tidak ditemukan.');
    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

if ($pesanan['metode_bayar'] === 'COD') {
    flash('error', 'Pesanan COD tidak perlu upload bukti pembayaran.');
    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

if (empty($_FILES['bukti']['name'])) {
    flash('error', 'Silakan pilih file bukti pembayaran.');
    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

$file = $_FILES['bukti'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    flash('error', 'Upload gagal. Silakan coba lagi.');
    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

$maxSize = 2 * 1024 * 1024;

if ($file['size'] > $maxSize) {
    flash('error', 'Ukuran file maksimal 2 MB.');
    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

$allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'pdf'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExt, true)) {
    flash('error', 'Format file harus JPG, JPEG, PNG, WEBP, atau PDF.');
    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

$uploadDir = "../uploads/bukti/";

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$namaFile = "bukti_" . $pesananId . "_" . time() . "." . $ext;
$target = $uploadDir . $namaFile;

if (!move_uploaded_file($file['tmp_name'], $target)) {
    flash('error', 'File gagal disimpan.');
    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

if (!empty($pesanan['bukti_pembayaran'])) {
    $oldFile = $uploadDir . $pesanan['bukti_pembayaran'];

    if (file_exists($oldFile)) {
        unlink($oldFile);
    }
}

$update = $pdo->prepare("
    UPDATE pesanan
    SET bukti_pembayaran = ?,
        status_pembayaran = 'Menunggu Verifikasi',
        catatan_pembayaran = NULL
    WHERE id = ? AND pelanggan_id = ?
");

$update->execute([
    $namaFile,
    $pesananId,
    $userId
]);

flash('success', 'Bukti pembayaran berhasil diupload. Admin akan memverifikasi pembayaran kamu.');
header("Location: ../pelanggan/pesanan-saya.php");
exit;
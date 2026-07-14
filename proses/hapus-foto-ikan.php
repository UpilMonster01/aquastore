<?php
require "../config/db.php";
admin_only();
csrf_check();

$id = (int)($_POST['id'] ?? 0);

$stmt = $pdo->prepare("SELECT foto FROM ikan WHERE id = ?");
$stmt->execute([$id]);
$ikan = $stmt->fetch();

if (!$ikan) {
    flash('error', 'Data ikan tidak ditemukan.');
    header("Location: ../admin/ikan.php");
    exit;
}

if (!empty($ikan['foto'])) {
    $path = "../uploads/ikan/" . $ikan['foto'];

    if (file_exists($path)) {
        unlink($path);
    }
}

$update = $pdo->prepare("UPDATE ikan SET foto = '' WHERE id = ?");
$update->execute([$id]);

flash('success', 'Foto ikan berhasil dihapus.');
header("Location: ../admin/ikan.php?v=" . time());
exit;
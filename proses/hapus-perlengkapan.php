<?php
require "../config/db.php";
admin_only();
csrf_check();

$id = (int)($_POST['id'] ?? 0);

$get = $pdo->prepare("SELECT foto FROM perlengkapan WHERE id=?");
$get->execute([$id]);
$data = $get->fetch();

if ($data && !empty($data['foto'])) {
    $path = "../uploads/perlengkapan/" . $data['foto'];

    if (file_exists($path)) {
        unlink($path);
    }
}

$delete = $pdo->prepare("DELETE FROM perlengkapan WHERE id=?");
$delete->execute([$id]);

flash('success', 'Perlengkapan berhasil dihapus.');
header("Location: ../admin/perlengkapan.php");
exit;
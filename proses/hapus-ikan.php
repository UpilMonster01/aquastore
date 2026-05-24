<?php
require "../config/db.php";
admin_only();
csrf_check();
$id = (int) $_POST['id'];
try {
    $s = $pdo->prepare("SELECT foto FROM ikan WHERE id=?");
    $s->execute([$id]);
    $foto = $s->fetchColumn();
    if ($foto && file_exists("../uploads/ikan/" . $foto))
        unlink("../uploads/ikan/" . $foto);
    $d = $pdo->prepare("DELETE FROM ikan WHERE id=?");
    $d->execute([$id]);
    flash('success', 'Data ikan berhasil dihapus.');
} catch (Exception $e) {
    flash('error', 'Data ikan gagal dihapus.');
}
header("Location: ../admin/ikan.php");
exit;

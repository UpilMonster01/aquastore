<?php require "../config/db.php";
admin_only();
csrf_check();
$id = (int) $_POST['id'];
$status = $_POST['status'];
$allowed = ['Pending', 'Diproses', 'Dikirim', 'Selesai'];
if (!in_array($status, $allowed)) {
    flash('error', 'Status tidak valid.');
    header("Location: ../admin/pesanan.php");
    exit;
}
$stmt = $pdo->prepare("UPDATE pesanan SET status=? WHERE id=?");
$stmt->execute([$status, $id]);
flash('success', 'Status pesanan diperbarui.');
header("Location: ../admin/pesanan.php");
exit;

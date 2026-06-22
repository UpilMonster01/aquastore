<?php
require "../config/db.php";
admin_only();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/pesanan.php");
    exit;
}

csrf_check();

$id = (int)($_POST['id'] ?? 0);
$statusPembayaran = $_POST['status_pembayaran'] ?? '';
$catatanPembayaran = trim($_POST['catatan_pembayaran'] ?? '');

$allowedStatusPembayaran = [
    'Belum Bayar',
    'Menunggu Verifikasi',
    'Terverifikasi',
    'Ditolak'
];

if ($id <= 0 || !in_array($statusPembayaran, $allowedStatusPembayaran, true)) {
    flash('error', 'Data pembayaran tidak valid.');
    header("Location: ../admin/pesanan.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM pesanan WHERE id = ?");
$stmt->execute([$id]);
$pesanan = $stmt->fetch();

if (!$pesanan) {
    flash('error', 'Pesanan tidak ditemukan.');
    header("Location: ../admin/pesanan.php");
    exit;
}

if ($statusPembayaran === 'Ditolak' && $catatanPembayaran === '') {
    flash('error', 'Alasan penolakan wajib diisi jika pembayaran ditolak.');
    header("Location: ../admin/pesanan.php");
    exit;
}

if ($statusPembayaran !== 'Ditolak') {
    $catatanPembayaran = '';
}

try {
    $pdo->beginTransaction();

    $updateBayar = $pdo->prepare("
        UPDATE pesanan
        SET status_pembayaran = ?,
            catatan_pembayaran = ?
        WHERE id = ?
    ");

    $updateBayar->execute([
        $statusPembayaran,
        $catatanPembayaran,
        $id
    ]);

    if ($statusPembayaran === 'Terverifikasi' && $pesanan['status'] === 'Pending') {
        $updateStatus = $pdo->prepare("
            UPDATE pesanan
            SET status = 'Diproses'
            WHERE id = ?
        ");

        $updateStatus->execute([$id]);

        flash('success', 'Pembayaran terverifikasi. Status pesanan otomatis berubah menjadi Diproses.');
    } elseif ($statusPembayaran === 'Ditolak') {
        flash('success', 'Pembayaran ditolak dan alasan penolakan berhasil disimpan.');
    } else {
        flash('success', 'Status pembayaran berhasil diperbarui.');
    }

    $pdo->commit();

    header("Location: ../admin/pesanan.php");
    exit;

} catch (Exception $e) {
    $pdo->rollBack();

    flash('error', 'Status pembayaran gagal diperbarui.');
    header("Location: ../admin/pesanan.php");
    exit;
}
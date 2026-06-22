<?php
require "../config/db.php";

if (empty($_SESSION['user'])) {
    flash('error', 'Silakan login terlebih dahulu.');
    header("Location: ../pelanggan/login.php");
    exit;
}

$userId = $_SESSION['user']['id'];

$nama = trim($_POST['nama'] ?? '');
$no_hp = trim($_POST['no_hp'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($nama === '') {
    flash('error', 'Nama lengkap wajib diisi.');
    header("Location: ../pelanggan/profil.php");
    exit;
}

try {
    if ($password !== '') {
        if (strlen($password) < 6) {
            flash('error', 'Password minimal 6 karakter.');
            header("Location: ../pelanggan/profil.php");
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("
            UPDATE pelanggan
            SET nama = ?, no_hp = ?, alamat = ?, password = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $nama,
            $no_hp,
            $alamat,
            $hash,
            $userId
        ]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE pelanggan
            SET nama = ?, no_hp = ?, alamat = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $nama,
            $no_hp,
            $alamat,
            $userId
        ]);
    }

    $get = $pdo->prepare("SELECT * FROM pelanggan WHERE id = ?");
    $get->execute([$userId]);
    $user = $get->fetch();

    $_SESSION['user'] = [
        'id' => $user['id'],
        'nama' => $user['nama'],
        'email' => $user['email'],
        'no_hp' => $user['no_hp'],
        'alamat' => $user['alamat']
    ];

    flash('success', 'Profil berhasil diperbarui.');
    header("Location: ../pelanggan/profil.php");
    exit;

} catch (Exception $e) {
    flash('error', 'Profil gagal diperbarui.');
    header("Location: ../pelanggan/profil.php");
    exit;
}
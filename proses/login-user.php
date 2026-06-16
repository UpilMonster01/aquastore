<?php
require "../config/db.php";

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

$stmt = $pdo->prepare("SELECT * FROM pelanggan WHERE email = ?");
$stmt->execute([$email]);

$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user'] = [
        'id' => $user['id'],
        'nama' => $user['nama'],
        'email' => $user['email'],
        'no_hp' => $user['no_hp'],
        'alamat' => $user['alamat']
    ];

    flash('success', 'Login berhasil.');
    header("Location: ../index.php");
    exit;
}

flash('error', 'Email atau password salah.');
header("Location: ../pelanggan/login.php");
exit;
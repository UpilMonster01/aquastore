<?php
require "../config/db.php";

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$no_hp = trim($_POST['no_hp'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$password = trim($_POST['password'] ?? '');
$redirect = $_POST['redirect'] ?? '../index.php';

if ($nama === '' || $email === '' || $password === '') {
    flash('error', 'Nama, email, dan password wajib diisi.');
    header("Location: ../pelanggan/register.php");
    exit;
}

if (strlen($password) < 6) {
    flash('error', 'Password minimal 6 karakter.');
    header("Location: ../pelanggan/register.php");
    exit;
}

$cek = $pdo->prepare("SELECT id FROM pelanggan WHERE email = ?");
$cek->execute([$email]);

if ($cek->fetch()) {
    flash('error', 'Email sudah terdaftar.');
    header("Location: ../pelanggan/register.php");
    exit;
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare("
    INSERT INTO pelanggan
    (nama, email, password, no_hp, alamat)
    VALUES (?, ?, ?, ?, ?)
");

$stmt->execute([
    $nama,
    $email,
    $hash,
    $no_hp,
    $alamat
]);

$_SESSION['user'] = [
    'id' => $pdo->lastInsertId(),
    'nama' => $nama,
    'email' => $email,
    'no_hp' => $no_hp,
    'alamat' => $alamat
];

flash('success', 'Akun berhasil dibuat.');
header("Location: " . $redirect);
exit;
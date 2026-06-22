<?php
require "../config/db.php";

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$no_hp = trim($_POST['no_hp'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$password = trim($_POST['password'] ?? '');
$redirect = $_POST['redirect'] ?? '/aquastore/index.php';

if (
    empty($redirect) ||
    !is_string($redirect) ||
    strpos($redirect, '/aquastore/') !== 0
) {
    $redirect = '/aquastore/index.php';
}

function redirect_register_error($redirect)
{
    $separator = strpos($redirect, '?') !== false ? '&' : '?';
    header("Location: " . $redirect . $separator . "auth=register");
    exit;
}

if ($nama === '' || $email === '' || $password === '') {
    flash('error', 'Nama, email, dan password wajib diisi.');
    redirect_register_error($redirect);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flash('error', 'Format email tidak valid.');
    redirect_register_error($redirect);
}

if (strlen($password) < 6) {
    flash('error', 'Password minimal 6 karakter.');
    redirect_register_error($redirect);
}

try {
    $cek = $pdo->prepare("SELECT id FROM pelanggan WHERE email = ?");
    $cek->execute([$email]);

    if ($cek->fetch()) {
        flash('error', 'Email sudah terdaftar.');
        redirect_register_error($redirect);
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

} catch (Exception $e) {
    flash('error', 'Registrasi gagal. Silakan coba lagi.');
    redirect_register_error($redirect);
}
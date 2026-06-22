<?php
require "../config/db.php";

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$redirect = $_POST['redirect'] ?? '/aquastore/index.php';

if (
    empty($redirect) ||
    !is_string($redirect) ||
    strpos($redirect, '/aquastore/') !== 0
) {
    $redirect = '/aquastore/index.php';
}

function redirect_login_error($redirect)
{
    $separator = strpos($redirect, '?') !== false ? '&' : '?';
    header("Location: " . $redirect . $separator . "auth=login");
    exit;
}

if ($email === '' || $password === '') {
    flash('error', 'Email dan password wajib diisi.');
    redirect_login_error($redirect);
}

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
    header("Location: " . $redirect);
    exit;
}

flash('error', 'Email atau password salah.');
redirect_login_error($redirect);
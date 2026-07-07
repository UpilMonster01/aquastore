<?php
require "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(url('index.php'));
}

csrf_check();

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$redirect = safe_redirect_url($_POST['redirect'] ?? url('index.php'), url('index.php'));

function redirect_login_error($redirect)
{
    redirect_to(append_query($redirect, ['auth' => 'login']));
}

if ($email === '' || $password === '') {
    flash('error', 'Email dan password wajib diisi.');
    redirect_login_error($redirect);
}

$stmt = $pdo->prepare("SELECT * FROM pelanggan WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => $user['id'],
        'nama' => $user['nama'],
        'email' => $user['email'],
        'no_hp' => $user['no_hp'],
        'alamat' => $user['alamat']
    ];

    flash('success', 'Login berhasil.');
    redirect_to($redirect);
}

flash('error', 'Email atau password salah.');
redirect_login_error($redirect);
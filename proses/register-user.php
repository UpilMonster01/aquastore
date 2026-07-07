<?php
require "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(url('index.php'));
}

csrf_check();

$nama = trim($_POST['nama'] ?? '');
$email = trim($_POST['email'] ?? '');
$noHp = trim($_POST['no_hp'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');
$password = trim($_POST['password'] ?? '');
$redirect = safe_redirect_url($_POST['redirect'] ?? url('index.php'), url('index.php'));

function redirect_register_error($redirect)
{
    redirect_to(append_query($redirect, ['auth' => 'register']));
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
    $cek = $pdo->prepare("SELECT id FROM pelanggan WHERE email = ? LIMIT 1");
    $cek->execute([$email]);

    if ($cek->fetch()) {
        flash('error', 'Email sudah terdaftar.');
        redirect_register_error($redirect);
    }

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO pelanggan (nama, email, password, no_hp, alamat)
        VALUES (?, ?, ?, ?, ?)
    ");

    $stmt->execute([$nama, $email, $hash, $noHp, $alamat]);

    session_regenerate_id(true);

    $_SESSION['user'] = [
        'id' => $pdo->lastInsertId(),
        'nama' => $nama,
        'email' => $email,
        'no_hp' => $noHp,
        'alamat' => $alamat
    ];

    flash('success', 'Akun berhasil dibuat.');
    redirect_to($redirect);
} catch (Throwable $e) {
    flash('error', 'Registrasi gagal. Silakan coba lagi.');
    redirect_register_error($redirect);
}
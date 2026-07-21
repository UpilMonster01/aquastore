<?php
require "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(url('index.php'));
}

csrf_check();

// Kompatibilitas mundur: drawer navbar (pelanggan-only) masih mengirim
// field "email", sedangkan form login gabungan mengirim "identifier".
$identifier = trim($_POST['identifier'] ?? $_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$redirect = safe_redirect_url($_POST['redirect'] ?? url('index.php'), url('index.php'));

function redirect_login_error($redirect)
{
    redirect_to(append_query($redirect, ['auth' => 'login']));
}

if ($identifier === '' || $password === '') {
    flash('error', 'Email/username dan password wajib diisi.');
    redirect_login_error($redirect);
}

// Deteksi jenis akun dari format identifier, bukan dengan mencoba kedua
// tabel secara berurutan — supaya jumlah query & waktu respons konsisten
// (menghindari timing side-channel untuk enumerasi akun) dan rate limit
// bisa langsung dikenakan pada scope yang tepat.
$isEmail = strpos($identifier, '@') !== false;
$scope = $isEmail ? 'pelanggan' : 'admin';

if (!login_rate_limit_check($pdo, $scope)) {
    redirect_login_error($redirect);
}

if ($isEmail) {
    $stmt = $pdo->prepare("SELECT * FROM pelanggan WHERE email = ? LIMIT 1");
    $stmt->execute([$identifier]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        login_rate_limit_reset($pdo, 'pelanggan');
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

    flash('error', 'Email/username atau password salah.');
    login_rate_limit_record_fail($pdo, 'pelanggan');
    redirect_login_error($redirect);
}

// Jalur admin (identifier tanpa "@" dianggap username)
$stmt = $pdo->prepare("SELECT id, username, password, nama FROM admin WHERE username = ? LIMIT 1");
$stmt->execute([$identifier]);
$admin = $stmt->fetch();

if ($admin && password_verify($password, $admin['password'])) {
    login_rate_limit_reset($pdo, 'admin');
    session_regenerate_id(true);

    $_SESSION['admin'] = [
        'id'       => (int) $admin['id'],
        'nama'     => $admin['nama'],
        'username' => $admin['username']
    ];

    if (password_needs_rehash($admin['password'], PASSWORD_DEFAULT)) {
        $hashBaru = password_hash($password, PASSWORD_DEFAULT);
        $update = $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?");
        $update->execute([$hashBaru, $admin['id']]);
    }

    flash('success', 'Login berhasil.');
    redirect_to(url('admin/dashboard.php'));
}

flash('error', 'Email/username atau password salah.');
login_rate_limit_record_fail($pdo, 'admin');
redirect_login_error($redirect);
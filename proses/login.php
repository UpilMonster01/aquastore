<?php
require "../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../index.php");
    exit;
}

csrf_check();

if (!login_rate_limit_check($pdo, 'admin')) {
    header("Location: ../index.php");
    exit;
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    flash('error', 'Username dan password wajib diisi.');
    header("Location: ../index.php");
    exit;
}

$stmt = $pdo->prepare(
    "SELECT id, username, password, nama
     FROM admin
     WHERE username = ?
     LIMIT 1"
);

$stmt->execute([$username]);
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

        $update = $pdo->prepare(
            "UPDATE admin SET password = ? WHERE id = ?"
        );

        $update->execute([
            $hashBaru,
            $admin['id']
        ]);
    }

    header("Location: ../admin/dashboard.php");
    exit;
}

flash('error', 'Username atau password salah.');
login_rate_limit_record_fail($pdo, 'admin');

header("Location: ../index.php");
exit;
<?php
require "../config/db.php";

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

$stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
$stmt->execute([$username]);
$admin = $stmt->fetch();

if ($admin && $password === 'admin123') {
    $_SESSION['admin'] = [
        'id' => $admin['id'],
        'nama' => $admin['nama'],
        'username' => $admin['username']
    ];

    header("Location: ../admin/dashboard.php");
    exit;
}

flash('error', 'Username atau password salah.');
header("Location: ../index.php");
exit;
<?php
session_start();

$host = "localhost";
$dbname = "aquastore";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

function e($text)
{
    return htmlspecialchars($text ?? '', ENT_QUOTES, 'UTF-8');
}
function rupiah($angka)
{
    return "Rp " . number_format((int) $angka, 0, ',', '.');
}

function csrf_token()
{
    if (empty($_SESSION['csrf']))
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_check()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (empty($_POST['csrf']) || $_POST['csrf'] !== ($_SESSION['csrf'] ?? ''))
            die("CSRF token tidak valid.");
    }
}

function flash($type, $message)
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function show_flash()
{
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo "<div class='flash " . e($f['type']) . "'>" . e($f['message']) . "</div>";
        unset($_SESSION['flash']);
    }
}

function admin_only()
{
    if (empty($_SESSION['admin'])) {
        header("Location: ../index.php");
        exit;
    }
}

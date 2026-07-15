<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   DATABASE CONFIG
   Ubah bagian ini saat pindah hosting.
========================= */
$host = "localhost";
$dbname = "aquastore";
$user = "root";
$pass = "";

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Koneksi database gagal. Pastikan database aquastore sudah dibuat dan konfigurasi db.php benar.");
}

/* =========================
   BASE URL OTOMATIS
   Aman untuk localhost/aquastore atau domain hosting.
========================= */
$scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$basePath = str_replace('\\', '/', dirname($scriptName));

foreach (['/admin', '/pelanggan', '/proses', '/config', '/components'] as $folder) {
    if (substr($basePath, -strlen($folder)) === $folder) {
        $basePath = substr($basePath, 0, -strlen($folder));
        break;
    }
}

$basePath = rtrim($basePath, '/');

if ($basePath === '/' || $basePath === '.') {
    $basePath = '';
}

if (!defined('BASE_URL')) {
    define('BASE_URL', $basePath);
}

function url($path = '')
{
    $base = rtrim(BASE_URL, '/');
    $path = ltrim((string) $path, '/');

    if ($path === '') {
        return $base !== '' ? $base : '/';
    }

    return ($base !== '' ? $base : '') . '/' . $path;
}

function safe_redirect_url($redirect, $fallback = null)
{
    $fallback = $fallback ?: url('index.php');

    if (!is_string($redirect)) {
        return $fallback;
    }

    $redirect = trim($redirect);

    if ($redirect === '' || preg_match('/[\r\n]/', $redirect)) {
        return $fallback;
    }

    $parts = parse_url($redirect);

    if (!empty($parts['scheme']) || !empty($parts['host']) || strpos($redirect, '//') === 0) {
        return $fallback;
    }

    if ($redirect[0] !== '/') {
        return url($redirect);
    }

    $base = rtrim(BASE_URL, '/');

    if ($base === '') {
        return $redirect;
    }

    if ($redirect === $base || strpos($redirect, $base . '/') === 0) {
        return $redirect;
    }

    return $fallback;
}

function append_query($url, array $params)
{
    $separator = strpos($url, '?') !== false ? '&' : '?';
    return $url . $separator . http_build_query($params);
}

function redirect_to($url)
{
    header('Location: ' . $url);
    exit;
}

/* =========================
   HELPER UMUM
========================= */
function e($text)
{
    return htmlspecialchars((string) ($text ?? ''), ENT_QUOTES, 'UTF-8');
}

function rupiah($angka)
{
    return 'Rp ' . number_format((int) $angka, 0, ',', '.');
}

function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}

function csrf_check()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }

    $token = $_POST['csrf'] ?? '';
    $sessionToken = $_SESSION['csrf'] ?? '';

    if (!is_string($token) || !is_string($sessionToken) || !hash_equals($sessionToken, $token)) {
        die('CSRF token tidak valid. Silakan kembali dan ulangi proses.');
    }
}

function flash($type, $message)
{
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function show_flash()
{
    if (empty($_SESSION['flash'])) {
        return;
    }

    $flash = $_SESSION['flash'];
    echo "<div class='flash " . e($flash['type']) . "'>" . e($flash['message']) . "</div>";
    unset($_SESSION['flash']);
}

function admin_only()
{
    if (empty($_SESSION['admin'])) {
        redirect_to(url('index.php'));
    }
}

function user_only()
{
    if (empty($_SESSION['user'])) {
        redirect_to(url('pelanggan/login.php'));
    }
}

/* =========================
   RATE LIMIT LOGIN
   Mencegah brute-force dengan membatasi jumlah
   percobaan login gagal per IP.
========================= */
function login_rate_limit_check($pdo, $scope)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = $ip . '|' . $scope;

    $stmt = $pdo->prepare(
        "SELECT attempts, locked_until FROM login_attempts WHERE ip_scope = ? LIMIT 1"
    );
    $stmt->execute([$key]);
    $row = $stmt->fetch();

    if ($row && $row['locked_until'] && strtotime($row['locked_until']) > time()) {
        $sisaDetik = strtotime($row['locked_until']) - time();
        $sisaMenit = (int) ceil($sisaDetik / 60);

        flash(
            'error',
            "Terlalu banyak percobaan login gagal. Coba lagi dalam {$sisaMenit} menit."
        );

        return false;
    }

    return true;
}

function login_rate_limit_record_fail($pdo, $scope)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = $ip . '|' . $scope;

    $maxAttempts = 5;
    $lockMinutes = 5;

    $stmt = $pdo->prepare("SELECT attempts FROM login_attempts WHERE ip_scope = ? LIMIT 1");
    $stmt->execute([$key]);
    $row = $stmt->fetch();

    $attempts = $row ? ((int) $row['attempts'] + 1) : 1;
    $lockedUntil = null;

    if ($attempts >= $maxAttempts) {
        $lockedUntil = date('Y-m-d H:i:s', time() + ($lockMinutes * 60));
    }

    $upsert = $pdo->prepare("
        INSERT INTO login_attempts (ip_scope, attempts, last_attempt, locked_until)
        VALUES (?, ?, NOW(), ?)
        ON DUPLICATE KEY UPDATE
            attempts = VALUES(attempts),
            last_attempt = VALUES(last_attempt),
            locked_until = VALUES(locked_until)
    ");

    $upsert->execute([$key, $attempts, $lockedUntil]);
}

function login_rate_limit_reset($pdo, $scope)
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key = $ip . '|' . $scope;

    $del = $pdo->prepare("DELETE FROM login_attempts WHERE ip_scope = ?");
    $del->execute([$key]);
}

/**
 * Memastikan nilai yang dikirim dari form (mis. kategori,
 * status) benar-benar salah satu dari pilihan yang sah,
 * bukan sekadar apa pun yang dikirim lewat request.
 */
function pilih_valid($value, array $allowed, $default)
{
    return in_array($value, $allowed, true) ? $value : $default;
}
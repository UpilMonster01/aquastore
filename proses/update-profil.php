<?php
require "../config/db.php";

if (empty($_SESSION['user'])) {
    flash('error', 'Silakan login terlebih dahulu.');
    header("Location: ../pelanggan/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pelanggan/profil.php");
    exit;
}

csrf_check();

$userId = (int) ($_SESSION['user']['id'] ?? 0);

$nama = trim($_POST['nama'] ?? '');
$noHp = trim($_POST['no_hp'] ?? '');
$alamat = trim($_POST['alamat'] ?? '');

/*
 * Password tidak menggunakan trim().
 * Spasi dapat menjadi bagian dari password pengguna.
 */
$password = $_POST['password'] ?? '';

if ($userId <= 0) {
    unset($_SESSION['user']);

    flash('error', 'Sesi tidak valid. Silakan login kembali.');
    header("Location: ../pelanggan/login.php");
    exit;
}

if ($nama === '') {
    flash('error', 'Nama lengkap wajib diisi.');
    header("Location: ../pelanggan/profil.php");
    exit;
}

if (strlen($nama) > 100) {
    flash('error', 'Nama lengkap maksimal 100 karakter.');
    header("Location: ../pelanggan/profil.php");
    exit;
}

if (strlen($noHp) > 30) {
    flash('error', 'Nomor HP maksimal 30 karakter.');
    header("Location: ../pelanggan/profil.php");
    exit;
}

if ($password !== '' && strlen($password) < 6) {
    flash('error', 'Password baru minimal 6 karakter.');
    header("Location: ../pelanggan/profil.php");
    exit;
}

try {
    /*
     * Memastikan akun pelanggan masih tersedia.
     */
    $cek = $pdo->prepare("
        SELECT id
        FROM pelanggan
        WHERE id = ?
        LIMIT 1
    ");

    $cek->execute([$userId]);

    if (!$cek->fetch()) {
        unset($_SESSION['user']);

        flash('error', 'Akun tidak ditemukan. Silakan login kembali.');
        header("Location: ../pelanggan/login.php");
        exit;
    }

    $passwordDiubah = false;

    if ($password !== '') {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        if ($hash === false) {
            throw new RuntimeException(
                'Password gagal diproses.'
            );
        }

        $stmt = $pdo->prepare("
            UPDATE pelanggan
            SET
                nama = ?,
                no_hp = ?,
                alamat = ?,
                password = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $nama,
            $noHp !== '' ? $noHp : null,
            $alamat !== '' ? $alamat : null,
            $hash,
            $userId
        ]);

        $passwordDiubah = true;
    } else {
        $stmt = $pdo->prepare("
            UPDATE pelanggan
            SET
                nama = ?,
                no_hp = ?,
                alamat = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $nama,
            $noHp !== '' ? $noHp : null,
            $alamat !== '' ? $alamat : null,
            $userId
        ]);
    }

    $get = $pdo->prepare("
        SELECT
            id,
            nama,
            email,
            no_hp,
            alamat
        FROM pelanggan
        WHERE id = ?
        LIMIT 1
    ");

    $get->execute([$userId]);
    $user = $get->fetch();

    if (!$user) {
        throw new RuntimeException(
            'Data pelanggan tidak ditemukan.'
        );
    }

    /*
     * Mengganti session ID setelah perubahan password.
     */
    if ($passwordDiubah) {
        session_regenerate_id(true);
    }

    $_SESSION['user'] = [
        'id' => (int) $user['id'],
        'nama' => $user['nama'],
        'email' => $user['email'],
        'no_hp' => $user['no_hp'],
        'alamat' => $user['alamat']
    ];

    flash('success', 'Profil berhasil diperbarui.');

    header("Location: ../pelanggan/profil.php");
    exit;
} catch (Throwable $e) {
    flash(
        'error',
        'Profil gagal diperbarui. Silakan coba kembali.'
    );

    header("Location: ../pelanggan/profil.php");
    exit;
}
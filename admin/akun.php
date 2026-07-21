<?php
require "../config/db.php";
admin_only();

$adminId = (int) $_SESSION['admin']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (isset($_POST['ganti_password'])) {
        $lama = trim($_POST['password_lama'] ?? '');
        $baru = trim($_POST['password_baru'] ?? '');
        $konfirmasi = trim($_POST['password_konfirmasi'] ?? '');

        $stmt = $pdo->prepare("SELECT * FROM admin WHERE id = ? LIMIT 1");
        $stmt->execute([$adminId]);
        $adminSekarang = $stmt->fetch();

        if (!$adminSekarang || !password_verify($lama, $adminSekarang['password'])) {
            flash('error', 'Password lama tidak cocok.');
        } elseif (strlen($baru) < 6) {
            flash('error', 'Password baru minimal 6 karakter.');
        } elseif ($baru !== $konfirmasi) {
            flash('error', 'Konfirmasi password baru tidak cocok.');
        } else {
            $hashBaru = password_hash($baru, PASSWORD_DEFAULT);
            $update = $pdo->prepare("UPDATE admin SET password = ? WHERE id = ?");
            $update->execute([$hashBaru, $adminId]);
            flash('success', 'Password berhasil diganti.');
        }

        header("Location: akun.php");
        exit;
    }

    if (isset($_POST['tambah_admin'])) {
        $usernameBaru = trim($_POST['username_baru'] ?? '');
        $namaBaru = trim($_POST['nama_baru'] ?? '');
        $passwordBaru = trim($_POST['password_admin_baru'] ?? '');

        if ($usernameBaru === '' || $namaBaru === '' || $passwordBaru === '') {
            flash('error', 'Semua field admin baru wajib diisi.');
        } elseif (strpos($usernameBaru, '@') !== false) {
            // Username tidak boleh mengandung "@" karena sistem login
            // mendeteksi identifier ber-"@" sebagai email pelanggan.
            flash('error', 'Username admin tidak boleh mengandung karakter "@".');
        } elseif (strlen($passwordBaru) < 6) {
            flash('error', 'Password admin baru minimal 6 karakter.');
        } else {
            $cek = $pdo->prepare("SELECT id FROM admin WHERE username = ? LIMIT 1");
            $cek->execute([$usernameBaru]);

            if ($cek->fetch()) {
                flash('error', 'Username sudah dipakai admin lain.');
            } else {
                $hash = password_hash($passwordBaru, PASSWORD_DEFAULT);
                $insert = $pdo->prepare("INSERT INTO admin (username, password, nama) VALUES (?, ?, ?)");
                $insert->execute([$usernameBaru, $hash, $namaBaru]);
                flash('success', 'Admin baru berhasil ditambahkan.');
            }
        }

        header("Location: akun.php");
        exit;
    }

    if (isset($_POST['hapus_admin'])) {
        $idHapus = (int) ($_POST['id'] ?? 0);

        if ($idHapus === $adminId) {
            flash('error', 'Tidak bisa menghapus akun yang sedang kamu pakai sendiri.');
        } else {
            $totalAdmin = (int) $pdo->query("SELECT COUNT(*) FROM admin")->fetchColumn();

            if ($totalAdmin <= 1) {
                flash('error', 'Tidak bisa menghapus admin terakhir.');
            } else {
                $stmt = $pdo->prepare("DELETE FROM admin WHERE id = ?");
                $stmt->execute([$idHapus]);
                flash('success', 'Admin dihapus.');
            }
        }

        header("Location: akun.php");
        exit;
    }
}

$daftarAdmin = $pdo->query("SELECT id, username, nama, created_at FROM admin ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Akun Admin - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="admin-layout">
        <?php include "sidebar.php"; ?>

        <main class="admin-content">
            <div class="admin-header">
                <div>
                    <span>Admin</span>
                    <h1>Akun Admin</h1>
                </div>
            </div>

            <?php show_flash(); ?>

            <div class="admin-grid">
                <div class="admin-panel">
                    <h2>🔑 Ganti Password Saya</h2>

                    <form method="POST" class="admin-form">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                        <input type="password" name="password_lama" placeholder="Password lama" required autocomplete="current-password">
                        <input type="password" name="password_baru" placeholder="Password baru (min. 6 karakter)" required minlength="6" autocomplete="new-password">
                        <input type="password" name="password_konfirmasi" placeholder="Ulangi password baru" required minlength="6" autocomplete="new-password">

                        <button name="ganti_password">Simpan Password</button>
                    </form>
                </div>

                <div class="admin-panel">
                    <h2>➕ Tambah Admin Baru</h2>

                    <form method="POST" class="admin-form">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

                        <input type="text" name="nama_baru" placeholder="Nama lengkap" required>
                        <input type="text" name="username_baru" placeholder="Username (tanpa @)" required>
                        <input type="password" name="password_admin_baru" placeholder="Password (min. 6 karakter)" required minlength="6" autocomplete="new-password">

                        <button name="tambah_admin">Tambah Admin</button>
                    </form>
                </div>
            </div>

            <div class="admin-panel">
                <h2>👥 Daftar Admin</h2>

                <div class="table-box">
                    <table>
                        <tr>
                            <th>Username</th>
                            <th>Nama</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>

                        <?php foreach ($daftarAdmin as $a): ?>
                            <tr>
                                <td><b><?= e($a['username']) ?></b><?= (int) $a['id'] === $adminId ? ' <small>(kamu)</small>' : '' ?></td>
                                <td><?= e($a['nama']) ?></td>
                                <td><?= e(date('d M Y', strtotime($a['created_at']))) ?></td>
                                <td>
                                    <?php if ((int) $a['id'] !== $adminId): ?>
                                        <form method="POST" class="action-form-inline" onsubmit="return confirm('Hapus admin <?= e(addslashes($a['username'])) ?>?')">
                                            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $a['id'] ?>">
                                            <button class="delete-button" name="hapus_admin">Hapus</button>
                                        </form>
                                    <?php else: ?>
                                        <small>—</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>

</html>

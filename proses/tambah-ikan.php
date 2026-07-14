<?php
require "../config/db.php";
admin_only();
csrf_check();

function uploadFotoIkan()
{
    if (
        empty($_FILES['foto']['name']) ||
        $_FILES['foto']['error'] !== UPLOAD_ERR_OK
    ) {
        return '';
    }

    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    $ext = strtolower(
        pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION)
    );

    if (!in_array($ext, $allowed, true)) {
        flash(
            'error',
            'Format foto harus JPG, JPEG, PNG, atau WEBP.'
        );

        header("Location: ../admin/ikan.php");
        exit;
    }

    if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
        flash('error', 'Ukuran foto maksimal 2MB.');

        header("Location: ../admin/ikan.php");
        exit;
    }

    $uploadDir = __DIR__ . "/../uploads/ikan/";

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $namaFoto = uniqid('ikan_', true) . '.' . $ext;
    $targetFoto = $uploadDir . $namaFoto;

    if (move_uploaded_file(
        $_FILES['foto']['tmp_name'],
        $targetFoto
    )) {
        return $namaFoto;
    }

    flash('error', 'Gagal mengunggah foto ikan.');

    header("Location: ../admin/ikan.php");
    exit;
}

try {
    $foto = uploadFotoIkan();

    $stmt = $pdo->prepare("
        INSERT INTO ikan (
            nama,
            nama_latin,
            kategori_air,
            kategori_sifat,
            kategori_jenis,
            harga,
            stok,
            ukuran_cm,
            tingkat_perawatan,
            foto,
            deskripsi,
            tips_perawatan,
            status
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ");

    $stmt->execute([
        trim($_POST['nama'] ?? ''),
        trim($_POST['nama_latin'] ?? ''),
        $_POST['kategori_air'] ?? 'Tawar',
        $_POST['kategori_sifat'] ?? 'Non-Predator',
        $_POST['kategori_jenis'] ?? 'Hias',
        (int) ($_POST['harga'] ?? 0),
        (int) ($_POST['stok'] ?? 0),
        $_POST['ukuran_cm'] !== ''
            ? (float) $_POST['ukuran_cm']
            : null,
        $_POST['tingkat_perawatan'] ?? 'Mudah',
        $foto,
        trim($_POST['deskripsi'] ?? ''),
        trim($_POST['tips_perawatan'] ?? ''),
        $_POST['status'] ?? 'Tersedia'
    ]);

    flash('success', 'Data ikan berhasil ditambahkan.');
} catch (Throwable $e) {
    flash('error', 'Gagal menambahkan data ikan.');
}

header("Location: ../admin/ikan.php?v=" . time());
exit;
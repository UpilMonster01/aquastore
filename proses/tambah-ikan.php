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

    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!class_exists('finfo')) {
        flash('error', 'Fitur pemeriksaan file belum aktif pada server.');
        header("Location: ../admin/ikan.php");
        exit;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($_FILES['foto']['tmp_name']);

    if (!isset($allowedMime[$mime])) {
        flash(
            'error',
            'Format foto harus JPG, JPEG, PNG, atau WEBP.'
        );

        header("Location: ../admin/ikan.php");
        exit;
    }

    $ext = $allowedMime[$mime];

    if (@getimagesize($_FILES['foto']['tmp_name']) === false) {
        flash('error', 'File gambar tidak valid atau rusak.');
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
        pilih_valid($_POST['kategori_air'] ?? '', ['Laut', 'Tawar', 'Payau'], 'Tawar'),
        pilih_valid($_POST['kategori_sifat'] ?? '', ['Predator', 'Non-Predator'], 'Non-Predator'),
        pilih_valid($_POST['kategori_jenis'] ?? '', ['Hias', 'Konsumsi', 'Langka'], 'Hias'),
        (int) ($_POST['harga'] ?? 0),
        (int) ($_POST['stok'] ?? 0),
        $_POST['ukuran_cm'] !== ''
            ? (float) $_POST['ukuran_cm']
            : null,
        pilih_valid($_POST['tingkat_perawatan'] ?? '', ['Mudah', 'Sedang', 'Sulit'], 'Mudah'),
        $foto,
        trim($_POST['deskripsi'] ?? ''),
        trim($_POST['tips_perawatan'] ?? ''),
        pilih_valid($_POST['status'] ?? '', ['Tersedia', 'Habis', 'Pre-order'], 'Tersedia')
    ]);

    flash('success', 'Data ikan berhasil ditambahkan.');
} catch (Throwable $e) {
    flash('error', 'Gagal menambahkan data ikan.');
}

header("Location: ../admin/ikan.php?v=" . time());
exit;
<?php
require "../config/db.php";

if (empty($_SESSION['user'])) {
    flash('error', 'Silakan login terlebih dahulu.');
    header("Location: ../pelanggan/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

csrf_check();

$userId = (int) ($_SESSION['user']['id'] ?? 0);
$pesananId = (int) ($_POST['pesanan_id'] ?? 0);

if ($userId <= 0 || $pesananId <= 0) {
    flash('error', 'Pesanan tidak valid.');
    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

if (!isset($_FILES['bukti'])) {
    flash('error', 'Silakan pilih file bukti pembayaran.');
    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

$file = $_FILES['bukti'];

$pesanUpload = [
    UPLOAD_ERR_INI_SIZE =>
        'Ukuran file melebihi batas server.',

    UPLOAD_ERR_FORM_SIZE =>
        'Ukuran file melebihi batas formulir.',

    UPLOAD_ERR_PARTIAL =>
        'File hanya terunggah sebagian.',

    UPLOAD_ERR_NO_FILE =>
        'Silakan pilih file bukti pembayaran.',

    UPLOAD_ERR_NO_TMP_DIR =>
        'Folder sementara server tidak tersedia.',

    UPLOAD_ERR_CANT_WRITE =>
        'Server gagal menyimpan file.',

    UPLOAD_ERR_EXTENSION =>
        'Upload dihentikan oleh ekstensi PHP.'
];

$kodeError = $file['error'] ?? UPLOAD_ERR_NO_FILE;

if ($kodeError !== UPLOAD_ERR_OK) {
    flash(
        'error',
        $pesanUpload[$kodeError]
            ?? 'Upload gagal. Silakan coba kembali.'
    );

    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

$maxSize = 2 * 1024 * 1024;
$ukuranFile = (int) ($file['size'] ?? 0);

if ($ukuranFile <= 0 || $ukuranFile > $maxSize) {
    flash(
        'error',
        'Ukuran file harus lebih dari 0 dan maksimal 2 MB.'
    );

    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

if (
    empty($file['tmp_name']) ||
    !is_uploaded_file($file['tmp_name'])
) {
    flash('error', 'File upload tidak valid.');
    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

if (!class_exists('finfo')) {
    flash(
        'error',
        'Fitur pemeriksaan file belum aktif pada server.'
    );

    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

/*
 * Memeriksa tipe file berdasarkan isi asli file,
 * bukan hanya ekstensi nama file.
 */
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);

$allowedMime = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
    'application/pdf' => 'pdf',
    'application/x-pdf' => 'pdf'
];

if (!isset($allowedMime[$mime])) {
    flash(
        'error',
        'Format file harus JPG, PNG, WEBP, atau PDF yang valid.'
    );

    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

$ext = $allowedMime[$mime];

/*
 * Memastikan file gambar benar-benar dapat dibaca
 * sebagai gambar.
 */
if (strpos($mime, 'image/') === 0) {
    $infoGambar = @getimagesize($file['tmp_name']);

    if ($infoGambar === false) {
        flash(
            'error',
            'File gambar tidak valid atau rusak.'
        );

        header("Location: ../pelanggan/pesanan-saya.php");
        exit;
    }
}

/*
 * Memastikan file PDF memiliki signature PDF.
 */
if ($ext === 'pdf') {
    $handle = @fopen($file['tmp_name'], 'rb');
    $signature = $handle ? fread($handle, 5) : false;

    if ($handle) {
        fclose($handle);
    }

    if ($signature !== '%PDF-') {
        flash(
            'error',
            'File PDF tidak valid atau rusak.'
        );

        header("Location: ../pelanggan/pesanan-saya.php");
        exit;
    }
}

$uploadDir = __DIR__ . '/../uploads/bukti/';

if (
    !is_dir($uploadDir) &&
    !mkdir($uploadDir, 0755, true)
) {
    flash(
        'error',
        'Folder penyimpanan bukti tidak dapat dibuat.'
    );

    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}

/*
 * Nama file dibuat acak agar tidak bertabrakan
 * dan tidak menggunakan nama asli dari pengguna.
 */
$namaFile =
    'bukti_' .
    $pesananId .
    '_' .
    bin2hex(random_bytes(16)) .
    '.' .
    $ext;

$targetBaru = $uploadDir . $namaFile;

$fileBaruTersimpan = false;
$fileLama = '';

try {
    $pdo->beginTransaction();

    /*
     * Mengunci data pesanan selama proses upload
     * dan pembaruan database berlangsung.
     */
    $stmt = $pdo->prepare("
        SELECT
            id,
            metode_bayar,
            bukti_pembayaran,
            status_pembayaran
        FROM pesanan
        WHERE id = ?
          AND pelanggan_id = ?
        LIMIT 1
        FOR UPDATE
    ");

    $stmt->execute([
        $pesananId,
        $userId
    ]);

    $pesanan = $stmt->fetch();

    if (!$pesanan) {
        throw new DomainException(
            'Pesanan tidak ditemukan.'
        );
    }

    if ($pesanan['metode_bayar'] === 'COD') {
        throw new DomainException(
            'Pesanan COD tidak memerlukan bukti pembayaran.'
        );
    }

    if (
        $pesanan['status_pembayaran']
        === 'Terverifikasi'
    ) {
        throw new DomainException(
            'Pembayaran sudah diverifikasi dan bukti tidak dapat diganti.'
        );
    }

    if (
        !move_uploaded_file(
            $file['tmp_name'],
            $targetBaru
        )
    ) {
        throw new RuntimeException(
            'File gagal disimpan.'
        );
    }

    $fileBaruTersimpan = true;

    /*
     * basename() mencegah nama file lama mengarah
     * ke folder lain.
     */
    $fileLama = basename(
        (string) (
            $pesanan['bukti_pembayaran']
            ?? ''
        )
    );

    $update = $pdo->prepare("
        UPDATE pesanan
        SET
            bukti_pembayaran = ?,
            status_pembayaran =
                'Menunggu Verifikasi',
            catatan_pembayaran = NULL
        WHERE id = ?
          AND pelanggan_id = ?
          AND status_pembayaran
              <> 'Terverifikasi'
    ");

    $update->execute([
        $namaFile,
        $pesananId,
        $userId
    ]);

    if ($update->rowCount() !== 1) {
        throw new RuntimeException(
            'Data pembayaran gagal diperbarui.'
        );
    }

    $pdo->commit();

    /*
     * Bukti lama baru dihapus setelah perubahan
     * database benar-benar berhasil.
     */
    if (
        $fileLama !== '' &&
        $fileLama !== $namaFile
    ) {
        $targetLama = $uploadDir . $fileLama;

        if (is_file($targetLama)) {
            @unlink($targetLama);
        }
    }

    flash(
        'success',
        'Bukti pembayaran berhasil diunggah. Admin akan memverifikasinya.'
    );

    header("Location: ../pelanggan/pesanan-saya.php");
    exit;

} catch (DomainException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if (
        $fileBaruTersimpan &&
        is_file($targetBaru)
    ) {
        @unlink($targetBaru);
    }

    flash('error', $e->getMessage());

    header("Location: ../pelanggan/pesanan-saya.php");
    exit;

} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if (
        $fileBaruTersimpan &&
        is_file($targetBaru)
    ) {
        @unlink($targetBaru);
    }

    flash(
        'error',
        'Bukti pembayaran gagal diunggah. Silakan coba kembali.'
    );

    header("Location: ../pelanggan/pesanan-saya.php");
    exit;
}
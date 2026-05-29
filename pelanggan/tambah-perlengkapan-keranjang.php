<?php

require "../config/db.php";

$id = (int)($_POST['id'] ?? 0);

$get = $pdo->prepare("
    SELECT *
    FROM perlengkapan
    WHERE id=?
");

$get->execute([$id]);

$data = $get->fetch();

if(!$data){

    flash(
        'error',
        'Produk tidak ditemukan.'
    );

    header("Location: perawatan.php");
    exit;
}

$_SESSION['keranjang_perlengkapan'][$id] =
(
    $_SESSION['keranjang_perlengkapan'][$id]
    ??
    0
) + 1;

flash(
    'success',
    'Perlengkapan masuk keranjang.'
);

header("Location: perawatan.php");
exit;
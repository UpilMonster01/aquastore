<?php
require "../config/db.php";

if (empty($_SESSION['user'])) {
    flash('error', 'Silakan login terlebih dahulu sebelum checkout.');
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'] ?? [];

if (empty($_SESSION['keranjang']) && empty($_SESSION['keranjang_perlengkapan']) && empty($_GET['sukses'])) {
    flash('error', 'Keranjang masih kosong.');
    header("Location: katalog.php");
    exit;
}

$ikanItems = [];
$alatItems = [];
$subtotal = 0;

if (!empty($_SESSION['keranjang'])) {
    $ids = array_keys($_SESSION['keranjang']);
    $in = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("SELECT * FROM ikan WHERE id IN ($in)");
    $stmt->execute($ids);
    $ikanItems = $stmt->fetchAll();

    foreach ($ikanItems as $i) {
        $jumlah = $_SESSION['keranjang'][$i['id']];

        if ($jumlah > $i['stok']) {
            flash('error', 'Stok ikan ' . $i['nama'] . ' tidak cukup.');
            header("Location: keranjang.php");
            exit;
        }

        $subtotal += $jumlah * $i['harga'];
    }
}

if (!empty($_SESSION['keranjang_perlengkapan'])) {
    $ids = array_keys($_SESSION['keranjang_perlengkapan']);
    $in = implode(',', array_fill(0, count($ids), '?'));

    $stmt = $pdo->prepare("SELECT * FROM perlengkapan WHERE id IN ($in)");
    $stmt->execute($ids);
    $alatItems = $stmt->fetchAll();

    foreach ($alatItems as $p) {
        $jumlah = $_SESSION['keranjang_perlengkapan'][$p['id']];

        if ($jumlah > $p['stok']) {
            flash('error', 'Stok perlengkapan ' . $p['nama'] . ' tidak cukup.');
            header("Location: keranjang.php");
            exit;
        }

        $subtotal += $jumlah * $p['harga'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $nama = trim($_POST['nama_pelanggan'] ?? '');
    $hp = trim($_POST['no_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    $pengiriman = $_POST['metode_pengiriman'] ?? 'Ambil Sendiri';
    $bayar = $_POST['metode_bayar'] ?? 'COD';

    $allowedPengiriman = [
        'Ambil Sendiri',
        'Kurir'
    ];

    $allowedBayar = [
        'Transfer Bank',
        'COD',
        'QRIS'
    ];

    if (!in_array($pengiriman, $allowedPengiriman, true)) {
        $pengiriman = 'Ambil Sendiri';
    }

    if (!in_array($bayar, $allowedBayar, true)) {
        $bayar = 'COD';
    }

    if ($nama === '' || $hp === '' || $alamat === '') {
        flash('error', 'Semua data wajib diisi.');
        header("Location: checkout.php");
        exit;
    }

    if (strlen($alamat) < 8) {
        flash('error', 'Alamat terlalu pendek.');
        header("Location: checkout.php");
        exit;
    }

    /*
     * Bersihkan isi keranjang.
     * ID dan jumlah selalu diubah menjadi integer.
     */
    $keranjangIkan = [];

    foreach ($_SESSION['keranjang'] ?? [] as $id => $jumlah) {
        $id = (int) $id;
        $jumlah = (int) $jumlah;

        if ($id > 0 && $jumlah > 0) {
            $keranjangIkan[$id] = $jumlah;
        }
    }

    $keranjangPerlengkapan = [];

    foreach (
        $_SESSION['keranjang_perlengkapan'] ?? []
        as $id => $jumlah
    ) {
        $id = (int) $id;
        $jumlah = (int) $jumlah;

        if ($id > 0 && $jumlah > 0) {
            $keranjangPerlengkapan[$id] = $jumlah;
        }
    }

    if (
        empty($keranjangIkan) &&
        empty($keranjangPerlengkapan)
    ) {
        flash('error', 'Keranjang masih kosong.');
        header("Location: keranjang.php");
        exit;
    }

    try {
        $pdo->beginTransaction();

        $subtotalTransaksi = 0;
        $ikanTerkunci = [];
        $perlengkapanTerkunci = [];

        /*
         * Mengunci stok ikan sampai transaksi selesai.
         */
        if (!empty($keranjangIkan)) {
            $idsIkan = array_keys($keranjangIkan);

            $placeholderIkan = implode(
                ',',
                array_fill(0, count($idsIkan), '?')
            );

            $stmtIkan = $pdo->prepare("
                SELECT
                    id,
                    nama,
                    harga,
                    stok,
                    status
                FROM ikan
                WHERE id IN ($placeholderIkan)
                FOR UPDATE
            ");

            $stmtIkan->execute($idsIkan);

            foreach ($stmtIkan->fetchAll() as $ikan) {
                $ikanTerkunci[(int) $ikan['id']] = $ikan;
            }

            foreach ($keranjangIkan as $id => $jumlah) {
                if (!isset($ikanTerkunci[$id])) {
                    throw new DomainException(
                        'Salah satu ikan tidak ditemukan.'
                    );
                }

                $ikan = $ikanTerkunci[$id];

                if ($ikan['status'] === 'Habis') {
                    throw new DomainException(
                        'Ikan ' . $ikan['nama'] . ' sedang habis.'
                    );
                }

                if ($jumlah > (int) $ikan['stok']) {
                    throw new DomainException(
                        'Stok ikan ' . $ikan['nama'] .
                        ' tidak mencukupi. Stok tersedia: ' .
                        $ikan['stok'] . '.'
                    );
                }

                $subtotalTransaksi +=
                    $jumlah * (int) $ikan['harga'];
            }
        }

        /*
         * Mengunci stok perlengkapan sampai transaksi selesai.
         */
        if (!empty($keranjangPerlengkapan)) {
            $idsPerlengkapan = array_keys(
                $keranjangPerlengkapan
            );

            $placeholderPerlengkapan = implode(
                ',',
                array_fill(
                    0,
                    count($idsPerlengkapan),
                    '?'
                )
            );

            $stmtPerlengkapan = $pdo->prepare("
                SELECT
                    id,
                    nama,
                    harga,
                    stok,
                    status
                FROM perlengkapan
                WHERE id IN ($placeholderPerlengkapan)
                FOR UPDATE
            ");

            $stmtPerlengkapan->execute(
                $idsPerlengkapan
            );

            foreach (
                $stmtPerlengkapan->fetchAll()
                as $perlengkapan
            ) {
                $perlengkapanTerkunci[
                    (int) $perlengkapan['id']
                ] = $perlengkapan;
            }

            foreach (
                $keranjangPerlengkapan
                as $id => $jumlah
            ) {
                if (
                    !isset(
                    $perlengkapanTerkunci[$id]
                )
                ) {
                    throw new DomainException(
                        'Salah satu perlengkapan tidak ditemukan.'
                    );
                }

                $perlengkapan =
                    $perlengkapanTerkunci[$id];

                if (
                    $perlengkapan['status'] === 'Habis'
                ) {
                    throw new DomainException(
                        'Perlengkapan ' .
                        $perlengkapan['nama'] .
                        ' sedang habis.'
                    );
                }

                if (
                    $jumlah >
                    (int) $perlengkapan['stok']
                ) {
                    throw new DomainException(
                        'Stok perlengkapan ' .
                        $perlengkapan['nama'] .
                        ' tidak mencukupi. Stok tersedia: ' .
                        $perlengkapan['stok'] . '.'
                    );
                }

                $subtotalTransaksi +=
                    $jumlah *
                    (int) $perlengkapan['harga'];
            }
        }

        $ongkir =
            $pengiriman === 'Kurir'
            ? 15000
            : 0;

        $total = $subtotalTransaksi + $ongkir;

        /*
         * Nomor pesanan menggunakan random_bytes
         * agar kemungkinan nomor sama sangat kecil.
         */
        $nomor = 'AQS-' .
            date('Ymd') . '-' .
            strtoupper(bin2hex(random_bytes(3)));

        $pelangganId =
            (int) ($_SESSION['user']['id'] ?? 0);

        if ($pelangganId <= 0) {
            throw new DomainException(
                'Sesi pelanggan tidak valid. Silakan login kembali.'
            );
        }

        $insertPesanan = $pdo->prepare("
            INSERT INTO pesanan (
                pelanggan_id,
                nomor_pesanan,
                nama_pelanggan,
                no_hp,
                alamat,
                metode_pengiriman,
                metode_bayar,
                total_harga,
                status
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, 'Pending'
            )
        ");

        $insertPesanan->execute([
            $pelangganId,
            $nomor,
            $nama,
            $hp,
            $alamat,
            $pengiriman,
            $bayar,
            $total
        ]);

        $pesananId = (int) $pdo->lastInsertId();

        /*
         * Query detail dan pengurangan stok ikan.
         */
        $insertDetailIkan = $pdo->prepare("
            INSERT INTO detail_pesanan (
                pesanan_id,
                ikan_id,
                jumlah,
                harga_satuan
            ) VALUES (?, ?, ?, ?)
        ");

        $updateStokIkan = $pdo->prepare("
            UPDATE ikan
            SET
                stok = stok - ?,
                status = CASE
                    WHEN stok - ? <= 0
                    THEN 'Habis'
                    ELSE status
                END
            WHERE id = ?
              AND stok >= ?
        ");

        foreach ($keranjangIkan as $id => $jumlah) {
            $ikan = $ikanTerkunci[$id];

            $insertDetailIkan->execute([
                $pesananId,
                $id,
                $jumlah,
                $ikan['harga']
            ]);

            $updateStokIkan->execute([
                $jumlah,
                $jumlah,
                $id,
                $jumlah
            ]);

            if ($updateStokIkan->rowCount() !== 1) {
                throw new DomainException(
                    'Stok ikan ' . $ikan['nama'] .
                    ' berubah. Silakan ulangi checkout.'
                );
            }
        }

        /*
         * Query detail dan pengurangan stok perlengkapan.
         */
        $insertDetailPerlengkapan = $pdo->prepare("
            INSERT INTO detail_pesanan_perlengkapan (
                pesanan_id,
                perlengkapan_id,
                jumlah,
                harga_satuan
            ) VALUES (?, ?, ?, ?)
        ");

        $updateStokPerlengkapan = $pdo->prepare("
            UPDATE perlengkapan
            SET
                stok = stok - ?,
                status = CASE
                    WHEN stok - ? <= 0
                    THEN 'Habis'
                    ELSE status
                END
            WHERE id = ?
              AND stok >= ?
        ");

        foreach (
            $keranjangPerlengkapan
            as $id => $jumlah
        ) {
            $perlengkapan =
                $perlengkapanTerkunci[$id];

            $insertDetailPerlengkapan->execute([
                $pesananId,
                $id,
                $jumlah,
                $perlengkapan['harga']
            ]);

            $updateStokPerlengkapan->execute([
                $jumlah,
                $jumlah,
                $id,
                $jumlah
            ]);

            if (
                $updateStokPerlengkapan->rowCount()
                !== 1
            ) {
                throw new DomainException(
                    'Stok perlengkapan ' .
                    $perlengkapan['nama'] .
                    ' berubah. Silakan ulangi checkout.'
                );
            }
        }

        $pdo->commit();

        unset(
            $_SESSION['keranjang'],
            $_SESSION['keranjang_perlengkapan']
        );

        header(
            "Location: checkout.php?sukses=" .
            urlencode($nomor)
        );
        exit;

    } catch (DomainException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        flash('error', $e->getMessage());
        header("Location: keranjang.php");
        exit;

    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }

        flash(
            'error',
            'Checkout gagal diproses. Silakan coba kembali.'
        );

        header("Location: checkout.php");
        exit;
    }
}

if (!empty($_GET['sukses'])):
    $nomorSukses = $_GET['sukses'];

    $stmt = $pdo->prepare("SELECT * FROM pesanan WHERE nomor_pesanan = ?");
    $stmt->execute([$nomorSukses]);
    $pesananSukses = $stmt->fetch();
    ?>
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <title>Checkout Berhasil</title>
        <link rel="stylesheet" href="../assets/css/style.css?v=250">
    </head>

    <body>

        <section class="checkout-success-page">
            <div class="success-order premium-success">
                <div class="success-icon">🎉</div>

                <h1>Pesanan Berhasil</h1>

                <p>Simpan nomor pesanan ini untuk cek status:</p>

                <div class="order-number-box">
                    <?= e($nomorSukses) ?>
                </div>

                <?php if ($pesananSukses): ?>
                    <div class="payment-box">
                        <h3>Instruksi Pembayaran</h3>

                        <?php if ($pesananSukses['metode_bayar'] === 'Transfer Bank'): ?>

                            <p>Silakan transfer ke rekening berikut:</p>

                            <div class="payment-info">
                                <b>Bank BCA</b><br>
                                No. Rekening: 1234567890<br>
                                a.n. AquaStore
                            </div>

                            <p>
                                Setelah transfer, simpan bukti pembayaran dan hubungi admin.
                            </p>

                        <?php elseif ($pesananSukses['metode_bayar'] === 'QRIS'): ?>

                            <p>Scan QRIS berikut untuk melakukan pembayaran:</p>

                            <img src="../assets/img/qris.png" class="qris-img" alt="QRIS AquaStore">

                            <p>
                                Pastikan nominal pembayaran sesuai total pesanan.
                            </p>

                        <?php else: ?>

                            <p>
                                Pembayaran dilakukan saat pesanan diterima atau saat ambil sendiri di toko.
                            </p>

                        <?php endif; ?>

                        <div class="payment-total">
                            Total Pembayaran:
                            <b><?= rupiah($pesananSukses['total_harga']) ?></b>
                        </div>
                    </div>
                <?php endif; ?>

                <p class="success-note">
                    Pesanan kamu masuk dengan status <b>Pending</b>. Admin akan memproses pesanan secepatnya.
                </p>

                <div class="success-actions">
                    <a href="cek-pesanan.php?nomor=<?= urlencode($nomorSukses) ?>" class="hero-button">
                        Cek Pesanan
                    </a>

                    <a href="katalog.php" class="mini-button">
                        Belanja Lagi
                    </a>
                </div>
            </div>
        </section>

    </body>

    </html>
    <?php exit; endif; ?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Checkout - AquaStore</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=250">
</head>

<body>

    <?php include "../components/navbar.php"; ?>

    <section class="checkout-section">
        <div class="section-title">
            <span>AquaStore Checkout</span>
            <h2>Lengkapi Pesanan</h2>
            <p>Isi data dengan benar agar pesanan ikan dan perlengkapan bisa diproses.</p>
        </div>

        <?php show_flash(); ?>

        <div class="checkout-premium-grid">
            <form method="POST" class="checkout-form premium-checkout-form">
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <h3>Data Pelanggan</h3>

                <input type="text" name="nama_pelanggan" placeholder="Nama lengkap"
                    value="<?= e($user['nama'] ?? '') ?>" required>

                <input type="text" name="no_hp" placeholder="Nomor HP / WhatsApp" value="<?= e($user['no_hp'] ?? '') ?>"
                    required>

                <textarea name="alamat" placeholder="Alamat lengkap" required><?= e($user['alamat'] ?? '') ?></textarea>

                <h3>Pengiriman & Pembayaran</h3>

                <select name="metode_pengiriman" id="pengiriman" onchange="hitungTotal()">
                    <option value="Ambil Sendiri">Ambil Sendiri - Gratis</option>
                    <option value="Kurir">Kurir - Rp 15.000</option>
                </select>

                <select name="metode_bayar">
                    <option value="Transfer Bank">Transfer Bank</option>
                    <option value="COD">COD</option>
                    <option value="QRIS">QRIS</option>
                </select>

                <div class="checkout-warning">
                    Pastikan nomor HP aktif agar admin bisa menghubungi kamu.
                </div>

                <div class="checkout-actions">
                    <a href="#" class="cancel-button" onclick="konfirmasiBatal(event)">
                        ❌ Batal
                    </a>

                    <button type="submit" class="login-button full-button">
                        ✅ Buat Pesanan
                    </button>
                </div>
            </form>

            <div class="checkout-summary-premium">
                <h3>Ringkasan Pesanan</h3>

                <?php if ($ikanItems): ?>
                    <h4>🐠 Ikan Hias</h4>

                    <?php foreach ($ikanItems as $i):
                        $jumlah = $_SESSION['keranjang'][$i['id']];
                        ?>
                        <div class="checkout-item">
                            <div>
                                <b><?= e($i['nama']) ?></b>
                                <span>x <?= $jumlah ?></span>
                            </div>

                            <strong><?= rupiah($jumlah * $i['harga']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if ($alatItems): ?>
                    <h4>🛠️ Perlengkapan</h4>

                    <?php foreach ($alatItems as $p):
                        $jumlah = $_SESSION['keranjang_perlengkapan'][$p['id']];
                        ?>
                        <div class="checkout-item">
                            <div>
                                <b><?= e($p['nama']) ?></b>
                                <span>x <?= $jumlah ?></span>
                            </div>

                            <strong><?= rupiah($jumlah * $p['harga']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="checkout-line"></div>

                <div class="checkout-row">
                    <span>Subtotal</span>
                    <b id="subtotal" data-total="<?= $subtotal ?>">
                        <?= rupiah($subtotal) ?>
                    </b>
                </div>

                <div class="checkout-row">
                    <span>Ongkir</span>
                    <b id="ongkir">Rp 0</b>
                </div>

                <div class="checkout-total">
                    <span>Total</span>
                    <b id="grandTotal"><?= rupiah($subtotal) ?></b>
                </div>
            </div>
        </div>
    </section>

    <script src="../assets/js/main.js"></script>
</body>

</html>
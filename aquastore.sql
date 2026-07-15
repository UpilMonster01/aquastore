-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 07 Jul 2026 pada 06.11
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aquastore`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama`, `created_at`) VALUES
(1, 'admin', '$2y$10$.Olmw9lU.hMtS6lnN0KDFueu4To0n10RGY88INgeImqvdT5PVNydW', 'Admin AquaStore', '2026-05-23 10:36:38'),
(3, 'admin2', '$2y$12$Ron8wId6qbHk275BSqfideB/LKDA6ZJF7V72P.H4NWFhBqFGn18VW', 'Admin AquaStore 2', '2026-05-23 10:41:04');

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `ikan_id` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id`, `pesanan_id`, `ikan_id`, `jumlah`, `harga_satuan`) VALUES
(1, 1, 11, 1, 95000),
(2, 2, 11, 1, 95000),
(3, 3, 10, 1, 125000),
(4, 4, 11, 1, 95000),
(6, 6, 14, 1, 90);

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_pesanan_perlengkapan`
--

CREATE TABLE `detail_pesanan_perlengkapan` (
  `id` int(11) NOT NULL,
  `pesanan_id` int(11) NOT NULL,
  `perlengkapan_id` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `ikan`
--

CREATE TABLE `ikan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `nama_latin` varchar(100) DEFAULT NULL,
  `kategori_air` enum('Laut','Tawar','Payau') NOT NULL,
  `kategori_sifat` enum('Predator','Non-Predator') NOT NULL,
  `kategori_jenis` enum('Hias','Konsumsi','Langka') NOT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL,
  `ukuran_cm` decimal(5,2) DEFAULT NULL,
  `tingkat_perawatan` enum('Mudah','Sedang','Sulit') NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `tips_perawatan` text DEFAULT NULL,
  `status` enum('Tersedia','Habis','Pre-order') DEFAULT 'Tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ikan`
--

INSERT INTO `ikan` (`id`, `nama`, `nama_latin`, `kategori_air`, `kategori_sifat`, `kategori_jenis`, `harga`, `stok`, `ukuran_cm`, `tingkat_perawatan`, `foto`, `deskripsi`, `tips_perawatan`, `status`, `created_at`) VALUES
(1, 'Mandarin Fish', 'Synchiropus splendidus', 'Laut', 'Non-Predator', 'Langka', 500000, 2, 5.00, 'Sulit', '', 'Ikan laut langka.', NULL, 'Pre-order', '2026-05-23 10:36:38'),
(2, 'Arwana Super Red', 'Scleropages formosus', 'Tawar', 'Predator', 'Langka', 2500000, 3, 25.00, 'Sulit', 'ikan_6a12f0615af135.62391197.jpeg', 'Ikan predator premium.', NULL, 'Tersedia', '2026-05-23 10:36:38'),
(3, 'Oscar Tiger', 'Astronotus ocellatus', 'Tawar', 'Predator', 'Hias', 85000, 8, 12.00, 'Sedang', '', 'Oscar aktif dan kuat.', NULL, 'Tersedia', '2026-05-23 10:36:38'),
(4, 'Guppy Cobra', 'Poecilia reticulata', 'Tawar', 'Non-Predator', 'Hias', 15000, 30, 3.00, 'Mudah', '', 'Guppy warna cerah.', NULL, 'Tersedia', '2026-05-23 10:36:38'),
(5, 'Molly Balon', 'Poecilia sphenops', 'Payau', 'Non-Predator', 'Hias', 12000, 25, 4.00, 'Mudah', '', 'Molly mudah dirawat.', NULL, 'Tersedia', '2026-05-23 10:36:38'),
(6, 'Clownfish', 'Amphiprioninae', 'Laut', 'Non-Predator', 'Hias', 180000, 10, 6.00, 'Sedang', '', 'Ikan laut populer.', NULL, 'Tersedia', '2026-05-23 10:36:38'),
(7, 'Blue Tang', 'Paracanthurus hepatus', 'Laut', 'Non-Predator', 'Hias', 350000, 5, 8.00, 'Sulit', '', 'Ikan laut biru cantik.', NULL, 'Tersedia', '2026-05-23 10:36:38'),
(8, 'Louhan Cencu', 'Flowerhorn', 'Tawar', 'Predator', 'Hias', 450000, 4, 15.00, 'Sedang', '', 'Louhan jenong premium.', NULL, 'Tersedia', '2026-05-23 10:36:38'),
(9, 'Discus Red', 'Symphysodon', 'Tawar', 'Non-Predator', 'Hias', 275000, 6, 10.00, 'Sulit', '', 'Discus elegan.', NULL, 'Tersedia', '2026-05-23 10:36:38'),
(10, 'Koi Kohaku', 'Cyprinus rubrofuscus', 'Tawar', 'Non-Predator', 'Hias', 125000, 11, 18.00, 'Sedang', '', 'Koi cocok untuk kolam.', NULL, 'Tersedia', '2026-05-23 10:36:38'),
(11, 'Pacu Albino', 'Piaractus brachypomus', 'Tawar', 'Predator', 'Konsumsi', 95000, 4, 10.00, 'Sedang', 'ikan_6a180476a290e3.68972316.jpg', 'Pacu aktif dan besar.', NULL, 'Tersedia', '2026-05-23 10:36:38'),
(12, 'Mandarin Fish', 'Synchiropus splendidus', 'Laut', 'Non-Predator', 'Langka', 500000, 2, 5.00, 'Sulit', 'ikan_6a12efd3b2fae4.51256713.jpg', 'Ikan laut langka.', NULL, 'Pre-order', '2026-05-23 10:36:38'),
(14, 'tes', 'tes', 'Laut', 'Predator', 'Hias', 90, 0, 5.00, 'Mudah', '', 'test', 'test', 'Habis', '2026-06-30 00:17:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ikan_gambar`
--

CREATE TABLE `ikan_gambar` (
  `id` int(11) NOT NULL,
  `ikan_id` int(11) NOT NULL,
  `gambar` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `ikan_gambar`
--

INSERT INTO `ikan_gambar` (`id`, `ikan_id`, `gambar`, `created_at`) VALUES
(1, 12, 'gallery_6a17ff84252c3.jpg', '2026-05-28 08:40:36');

-- --------------------------------------------------------

--
-- Struktur dari tabel `log_kesehatan`
--

CREATE TABLE `log_kesehatan` (
  `id` int(11) NOT NULL,
  `ikan_id` int(11) DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `obat` varchar(100) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `login_attempts`
-- Menyimpan percobaan login gagal per IP untuk rate limiting
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `ip_scope` varchar(150) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 1,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp(),
  `locked_until` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(30) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pelanggan`
--

INSERT INTO `pelanggan` (`id`, `nama`, `email`, `password`, `no_hp`, `alamat`, `created_at`) VALUES
(1, 'Rudi Cahyadi', 'rudicahyadi900@gmail.com', '$2y$10$tJrhO1EqhPWO2u8xCdTjzOFSS4LvLKozIrpDHIkYacAZoq572XLRy', '082359437251', 'lomboktengan\r\nkr.bali', '2026-06-16 13:15:35'),
(2, 'udin', 'udin@gmail.com', '$2y$10$pCMPYiRZKa53mdMf.LUIoesVyNgyJH97ZxtHUB6QeBDPeIGXLUcHi', '0823594372', 'lomboktengan\r\nkr.bali', '2026-06-22 13:04:29'),
(3, 'Customer Test Update', 'customer@test.com', '$2y$10$n7MKgIa0bwZAH1MTu7QEweQHxFRxiJUzQoyS9tBWSFqIe.2TbXJ3W', '08999999999', 'Jalan Update No. 2', '2026-06-23 06:50:13');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pengeluaran`
--

CREATE TABLE `pengeluaran` (
  `id` int(11) NOT NULL,
  `kategori` enum('Pakan','Obat','Aksesori','Listrik','Lainnya') DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `tanggal` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pengeluaran`
--

INSERT INTO `pengeluaran` (`id`, `kategori`, `keterangan`, `jumlah`, `tanggal`, `created_at`) VALUES
(1, 'Pakan', 'Pakan ikan predator', 75000, '2026-05-23', '2026-05-23 10:36:39'),
(2, 'Obat', 'Obat anti jamur', 45000, '2026-05-23', '2026-05-23 10:36:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `perawatan`
--

CREATE TABLE `perawatan` (
  `id` int(11) NOT NULL,
  `tank_id` int(11) DEFAULT NULL,
  `nama_kegiatan` varchar(150) DEFAULT NULL,
  `jadwal` date DEFAULT NULL,
  `status` enum('Pending','Selesai') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `perawatan`
--

INSERT INTO `perawatan` (`id`, `tank_id`, `nama_kegiatan`, `jadwal`, `status`, `created_at`) VALUES
(1, 1, 'Ganti air 30%', '2026-05-23', 'Pending', '2026-05-23 10:36:39'),
(2, 2, 'Cek salinitas air laut', '2026-05-23', 'Pending', '2026-05-23 10:36:39'),
(3, 3, 'Bersihkan filter', '2026-05-23', 'Selesai', '2026-05-23 10:36:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `perlengkapan`
--

CREATE TABLE `perlengkapan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `kategori` enum('Pakan','Filter','Aerator','Heater','Obat','Lampu','Substrate','Dekorasi','Lainnya') NOT NULL,
  `harga` int(11) NOT NULL,
  `stok` int(11) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `status` enum('Tersedia','Habis') DEFAULT 'Tersedia',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `perlengkapan`
--

INSERT INTO `perlengkapan` (`id`, `nama`, `kategori`, `harga`, `stok`, `foto`, `deskripsi`, `status`, `created_at`) VALUES
(1, 'aerator', 'Aerator', 20000, 5, '', 'penghasil udara', 'Tersedia', '2026-05-29 09:57:39');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(11) NOT NULL,
  `pelanggan_id` int(11) DEFAULT NULL,
  `nomor_pesanan` varchar(50) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `no_hp` varchar(30) NOT NULL,
  `alamat` text NOT NULL,
  `metode_pengiriman` varchar(50) NOT NULL,
  `metode_bayar` varchar(50) NOT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `status_pembayaran` enum('Belum Bayar','Menunggu Verifikasi','Terverifikasi','Ditolak') DEFAULT 'Belum Bayar',
  `catatan_pembayaran` text DEFAULT NULL,
  `total_harga` int(11) NOT NULL,
  `status` enum('Pending','Diproses','Dikirim','Selesai') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pesanan`
--

INSERT INTO `pesanan` (`id`, `pelanggan_id`, `nomor_pesanan`, `nama_pelanggan`, `no_hp`, `alamat`, `metode_pengiriman`, `metode_bayar`, `bukti_pembayaran`, `status_pembayaran`, `catatan_pembayaran`, `total_harga`, `status`, `created_at`) VALUES
(1, NULL, 'AQS-20260530-2637', 'rudi', '082359437251', 'lomboktengan\r\nkr.bali', 'Kurir', 'QRIS', NULL, 'Belum Bayar', NULL, 110000, 'Selesai', '2026-05-30 08:59:38'),
(2, NULL, 'AQS-20260530-4211', 'Rudi Cahyadi', '082359437251', 'lomboktengan\r\nkr.bali', 'Kurir', 'Transfer Bank', NULL, 'Belum Bayar', NULL, 110000, 'Selesai', '2026-05-30 09:08:07'),
(3, NULL, 'AQS-20260530-2537', 'Rudi Cahyadi', '082359437251', 'lomboktengan\r\nkr.bali', 'Kurir', 'QRIS', NULL, 'Belum Bayar', NULL, 140000, 'Selesai', '2026-05-30 09:43:35'),
(4, 2, 'AQS-20260622-3138', 'udin', '082359437251', 'lomboktengan\r\nkr.bali', 'Ambil Sendiri', 'Transfer Bank', 'bukti_4_1782137689.png', 'Terverifikasi', NULL, 95000, 'Selesai', '2026-06-22 13:05:48'),
(6, 2, 'AQS-20260701-D248ED', 'udin', '0823594372', 'lomboktengan\r\nkr.bali', 'Ambil Sendiri', 'Transfer Bank', NULL, 'Belum Bayar', NULL, 90, 'Pending', '2026-07-01 00:04:11');

-- --------------------------------------------------------

--
-- Struktur dari tabel `tank`
--

CREATE TABLE `tank` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `ukuran_liter` int(11) DEFAULT NULL,
  `jenis_air` varchar(50) DEFAULT NULL,
  `suhu` varchar(50) DEFAULT NULL,
  `ph` varchar(50) DEFAULT NULL,
  `kapasitas` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `tank`
--

INSERT INTO `tank` (`id`, `nama`, `ukuran_liter`, `jenis_air`, `suhu`, `ph`, `kapasitas`, `created_at`) VALUES
(1, 'Tank Predator', 500, 'Tawar', '27-29 C', '6.5-7.5', 10, '2026-05-23 10:36:38'),
(2, 'Tank Laut Premium', 300, 'Laut', '25-27 C', '8.0-8.4', 15, '2026-05-23 10:36:38'),
(3, 'Tank Komunitas', 200, 'Tawar', '26-28 C', '6.8-7.4', 40, '2026-05-23 10:36:38');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `ikan_id` (`ikan_id`);

--
-- Indeks untuk tabel `detail_pesanan_perlengkapan`
--
ALTER TABLE `detail_pesanan_perlengkapan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `perlengkapan_id` (`perlengkapan_id`);

--
-- Indeks untuk tabel `ikan`
--
ALTER TABLE `ikan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `ikan_gambar`
--
ALTER TABLE `ikan_gambar`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ikan_id` (`ikan_id`);

--
-- Indeks untuk tabel `log_kesehatan`
--
ALTER TABLE `log_kesehatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ikan_id` (`ikan_id`);

--
-- Indeks untuk tabel `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ip_scope` (`ip_scope`);

--
-- Indeks untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `perawatan`
--
ALTER TABLE `perawatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tank_id` (`tank_id`);

--
-- Indeks untuk tabel `perlengkapan`
--
ALTER TABLE `perlengkapan`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nomor_pesanan` (`nomor_pesanan`);

--
-- Indeks untuk tabel `tank`
--
ALTER TABLE `tank`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `detail_pesanan_perlengkapan`
--
ALTER TABLE `detail_pesanan_perlengkapan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `ikan`
--
ALTER TABLE `ikan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT untuk tabel `ikan_gambar`
--
ALTER TABLE `ikan_gambar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `log_kesehatan`
--
ALTER TABLE `log_kesehatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `pengeluaran`
--
ALTER TABLE `pengeluaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT untuk tabel `perawatan`
--
ALTER TABLE `perawatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `perlengkapan`
--
ALTER TABLE `perlengkapan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `tank`
--
ALTER TABLE `tank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_ibfk_2` FOREIGN KEY (`ikan_id`) REFERENCES `ikan` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `detail_pesanan_perlengkapan`
--
ALTER TABLE `detail_pesanan_perlengkapan`
  ADD CONSTRAINT `detail_pesanan_perlengkapan_ibfk_1` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detail_pesanan_perlengkapan_ibfk_2` FOREIGN KEY (`perlengkapan_id`) REFERENCES `perlengkapan` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `ikan_gambar`
--
ALTER TABLE `ikan_gambar`
  ADD CONSTRAINT `ikan_gambar_ibfk_1` FOREIGN KEY (`ikan_id`) REFERENCES `ikan` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `log_kesehatan`
--
ALTER TABLE `log_kesehatan`
  ADD CONSTRAINT `log_kesehatan_ibfk_1` FOREIGN KEY (`ikan_id`) REFERENCES `ikan` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `perawatan`
--
ALTER TABLE `perawatan`
  ADD CONSTRAINT `perawatan_ibfk_1` FOREIGN KEY (`tank_id`) REFERENCES `tank` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

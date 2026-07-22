-- =========================================================
-- Migrasi: Tambah kolom lokasi presisi (lat, lng) dan
-- catatan/patokan alamat ke tabel pesanan.
--
-- Tujuan: saat checkout dengan metode "Kurir", pelanggan
-- memilih titik lokasi di peta (bukan cuma mengetik alamat
-- teks bebas) supaya pelacakan pesanan di halaman
-- "Cek Pesanan" bisa menampilkan lokasi yang akurat.
--
-- Pesanan LAMA (sebelum migrasi ini) akan punya lat/lng NULL
-- — halaman Cek Pesanan tetap jalan untuk pesanan lama,
-- cuma fallback ke perkiraan lokasi dari teks alamat seperti
-- sebelumnya.
--
-- Cara pakai: import file ini lewat phpMyAdmin/HeidiSQL ke
-- database aquastore yang SUDAH ada (jangan dipakai untuk
-- instalasi baru, pakai aquastore.sql untuk itu).
-- =========================================================

ALTER TABLE `pesanan`
  ADD COLUMN `lat` DECIMAL(10, 7) DEFAULT NULL AFTER `alamat`,
  ADD COLUMN `lng` DECIMAL(10, 7) DEFAULT NULL AFTER `lat`,
  ADD COLUMN `catatan_alamat` VARCHAR(255) DEFAULT NULL AFTER `lng`;

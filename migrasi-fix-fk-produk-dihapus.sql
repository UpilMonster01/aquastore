-- =========================================================
-- Migrasi: Perbaikan FK agar riwayat pesanan tidak ikut
-- terhapus saat admin menghapus data ikan/perlengkapan.
--
-- Sebelumnya: ON DELETE CASCADE
--   -> saat produk dihapus, baris detail_pesanan terkait
--      ikut terhapus otomatis (riwayat invoice hilang).
--
-- Sesudah: ON DELETE SET NULL
--   -> saat produk dihapus, kolom ikan_id/perlengkapan_id
--      di detail_pesanan cukup diisi NULL, baris riwayat
--      pesanan tetap ada (tampil "Produk ikan dihapus" dsb).
--
-- Cara pakai: import file ini lewat phpMyAdmin/HeidiSQL
-- ke database aquastore yang SUDAH ada (jangan dipakai
-- untuk instalasi baru, pakai aquastore.sql untuk itu).
-- =========================================================

-- 1) Hapus constraint FK lama
ALTER TABLE `detail_pesanan`
  DROP FOREIGN KEY `detail_pesanan_ibfk_2`;

ALTER TABLE `detail_pesanan_perlengkapan`
  DROP FOREIGN KEY `detail_pesanan_perlengkapan_ibfk_2`;

-- 2) Ubah kolom agar boleh NULL
ALTER TABLE `detail_pesanan`
  MODIFY `ikan_id` int(11) DEFAULT NULL;

ALTER TABLE `detail_pesanan_perlengkapan`
  MODIFY `perlengkapan_id` int(11) DEFAULT NULL;

-- 3) Tambahkan lagi FK dengan ON DELETE SET NULL
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `detail_pesanan_ibfk_2`
  FOREIGN KEY (`ikan_id`) REFERENCES `ikan` (`id`) ON DELETE SET NULL;

ALTER TABLE `detail_pesanan_perlengkapan`
  ADD CONSTRAINT `detail_pesanan_perlengkapan_ibfk_2`
  FOREIGN KEY (`perlengkapan_id`) REFERENCES `perlengkapan` (`id`) ON DELETE SET NULL;

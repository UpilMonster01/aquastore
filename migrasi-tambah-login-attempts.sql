-- =========================================================
-- Migrasi: Tambah tabel login_attempts untuk rate-limit
-- login (mencegah brute-force username/password).
--
-- Cara pakai: import file ini lewat phpMyAdmin/HeidiSQL
-- ke database aquastore yang SUDAH ada.
-- =========================================================

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_scope` varchar(150) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT 1,
  `last_attempt` timestamp NOT NULL DEFAULT current_timestamp(),
  `locked_until` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_scope` (`ip_scope`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

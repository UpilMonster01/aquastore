CREATE DATABASE IF NOT EXISTS aquastore;
USE aquastore;

SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS detail_pesanan_perlengkapan;
DROP TABLE IF EXISTS detail_pesanan;
DROP TABLE IF EXISTS pesanan;
DROP TABLE IF EXISTS pelanggan;
DROP TABLE IF EXISTS perlengkapan;
DROP TABLE IF EXISTS ikan;
DROP TABLE IF EXISTS tank;
DROP TABLE IF EXISTS perawatan;
DROP TABLE IF EXISTS pengeluaran;
DROP TABLE IF EXISTS admin;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================
-- TABEL ADMIN
-- =========================

CREATE TABLE admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) DEFAULT 'Administrator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admin (username, password, nama)
VALUES ('admin', 'admin123', 'Administrator AquaStore');

-- =========================
-- TABEL PELANGGAN
-- =========================

CREATE TABLE pelanggan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    no_hp VARCHAR(30),
    alamat TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABEL IKAN
-- =========================

CREATE TABLE ikan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    jenis VARCHAR(100) NOT NULL,
    harga INT NOT NULL DEFAULT 0,
    stok INT NOT NULL DEFAULT 0,
    ukuran VARCHAR(50),
    tingkat_perawatan VARCHAR(50),
    foto VARCHAR(255),
    deskripsi TEXT,
    tips_perawatan TEXT,
    status ENUM('Tersedia','Habis') DEFAULT 'Tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABEL PERLENGKAPAN
-- =========================

CREATE TABLE perlengkapan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kategori ENUM(
        'Pakan',
        'Filter',
        'Aerator',
        'Heater',
        'Obat',
        'Lampu',
        'Substrate',
        'Dekorasi',
        'Lainnya'
    ) NOT NULL,
    harga INT NOT NULL DEFAULT 0,
    stok INT NOT NULL DEFAULT 0,
    foto VARCHAR(255),
    deskripsi TEXT,
    status ENUM('Tersedia','Habis') DEFAULT 'Tersedia',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABEL PESANAN
-- =========================

CREATE TABLE pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pelanggan_id INT NULL,
    nomor_pesanan VARCHAR(50) NOT NULL UNIQUE,
    nama_pelanggan VARCHAR(100) NOT NULL,
    no_hp VARCHAR(30) NOT NULL,
    alamat TEXT NOT NULL,
    metode_pengiriman ENUM('Ambil Sendiri','Kurir') DEFAULT 'Ambil Sendiri',
    metode_bayar ENUM('Transfer Bank','COD','QRIS') DEFAULT 'COD',
    bukti_pembayaran VARCHAR(255) NULL,
    status_pembayaran ENUM(
        'Belum Bayar',
        'Menunggu Verifikasi',
        'Terverifikasi',
        'Ditolak'
    ) DEFAULT 'Belum Bayar',
    catatan_pembayaran TEXT NULL,
    total_harga INT NOT NULL DEFAULT 0,
    status ENUM('Pending','Diproses','Dikirim','Selesai') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pelanggan_id) REFERENCES pelanggan(id) ON DELETE SET NULL
);

-- =========================
-- DETAIL PESANAN IKAN
-- =========================

CREATE TABLE detail_pesanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    ikan_id INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    harga_satuan INT NOT NULL DEFAULT 0,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (ikan_id) REFERENCES ikan(id) ON DELETE CASCADE
);

-- =========================
-- DETAIL PESANAN PERLENGKAPAN
-- =========================

CREATE TABLE detail_pesanan_perlengkapan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pesanan_id INT NOT NULL,
    perlengkapan_id INT NOT NULL,
    jumlah INT NOT NULL DEFAULT 1,
    harga_satuan INT NOT NULL DEFAULT 0,
    FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
    FOREIGN KEY (perlengkapan_id) REFERENCES perlengkapan(id) ON DELETE CASCADE
);

-- =========================
-- TABEL TANK / AQUARIUM
-- =========================

CREATE TABLE tank (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    ukuran VARCHAR(100),
    kapasitas VARCHAR(100),
    harga INT DEFAULT 0,
    stok INT DEFAULT 0,
    foto VARCHAR(255),
    deskripsi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABEL PERAWATAN
-- =========================

CREATE TABLE perawatan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(150) NOT NULL,
    kategori VARCHAR(100),
    isi TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- TABEL PENGELUARAN
-- =========================

CREATE TABLE pengeluaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    kategori VARCHAR(100),
    keterangan TEXT,
    jumlah INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =========================
-- DATA CONTOH IKAN
-- =========================

INSERT INTO ikan
(nama, jenis, harga, stok, ukuran, tingkat_perawatan, foto, deskripsi, tips_perawatan, status)
VALUES
(
    'Cupang Halfmoon',
    'Cupang',
    35000,
    15,
    'Kecil',
    'Mudah',
    NULL,
    'Ikan cupang hias dengan sirip lebar dan warna menarik.',
    'Gunakan air bersih, ganti air secara rutin, dan beri pakan secukupnya.',
    'Tersedia'
),
(
    'Guppy Red Dragon',
    'Guppy',
    25000,
    20,
    'Kecil',
    'Mudah',
    NULL,
    'Ikan guppy dengan warna merah cerah dan aktif berenang.',
    'Cocok dipelihara berkelompok. Gunakan filter lembut dan pakan kecil.',
    'Tersedia'
),
(
    'Koi Kohaku',
    'Koi',
    150000,
    8,
    'Sedang',
    'Sedang',
    NULL,
    'Ikan koi dengan pola putih dan merah yang elegan.',
    'Butuh kolam luas, oksigen cukup, dan kualitas air stabil.',
    'Tersedia'
);

-- =========================
-- DATA CONTOH PERLENGKAPAN
-- =========================

INSERT INTO perlengkapan
(nama, kategori, harga, stok, foto, deskripsi, status)
VALUES
(
    'Pakan Ikan Premium',
    'Pakan',
    20000,
    30,
    NULL,
    'Pakan ikan hias untuk mendukung pertumbuhan dan warna ikan.',
    'Tersedia'
),
(
    'Filter Aquarium Mini',
    'Filter',
    45000,
    12,
    NULL,
    'Filter aquarium untuk menjaga air tetap bersih dan sehat.',
    'Tersedia'
),
(
    'Aerator Aquarium',
    'Aerator',
    55000,
    10,
    NULL,
    'Aerator untuk menambah oksigen dalam aquarium.',
    'Tersedia'
);

-- =========================
-- DATA CONTOH PERAWATAN
-- =========================

INSERT INTO perawatan (judul, kategori, isi)
VALUES
(
    'Cara Mengganti Air Aquarium',
    'Air',
    'Ganti sebagian air aquarium secara rutin, sekitar 20-30 persen, agar ikan tidak stres.'
),
(
    'Tips Memberi Pakan Ikan',
    'Pakan',
    'Berikan pakan secukupnya 1-2 kali sehari dan hindari pemberian pakan berlebihan.'
);

-- =========================
-- INDEX TAMBAHAN
-- =========================

CREATE INDEX idx_pesanan_pelanggan ON pesanan(pelanggan_id);
CREATE INDEX idx_pesanan_status ON pesanan(status);
CREATE INDEX idx_pesanan_status_pembayaran ON pesanan(status_pembayaran);
CREATE INDEX idx_detail_pesanan_id ON detail_pesanan(pesanan_id);
CREATE INDEX idx_detail_perlengkapan_pesanan_id ON detail_pesanan_perlengkapan(pesanan_id);
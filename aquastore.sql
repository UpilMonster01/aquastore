CREATE DATABASE IF NOT EXISTS aquastore;
USE aquastore;

CREATE TABLE admin (
 id INT AUTO_INCREMENT PRIMARY KEY,
 username VARCHAR(50) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 nama VARCHAR(100) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admin (username,password,nama) VALUES
('admin','$2y$10$e0MYzXyjpJS7Pd0RVvHwHeVh7iZqQ4ZrD8oMQMgBwldYyL4bNQ9Qa','Admin AquaStore');
-- login: admin / admin123

CREATE TABLE ikan (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nama VARCHAR(100) NOT NULL,
 nama_latin VARCHAR(100),
 kategori_air ENUM('Laut','Tawar','Payau') NOT NULL,
 kategori_sifat ENUM('Predator','Non-Predator') NOT NULL,
 kategori_jenis ENUM('Hias','Konsumsi','Langka') NOT NULL,
 harga INT NOT NULL,
 stok INT NOT NULL,
 ukuran_cm DECIMAL(5,2),
 tingkat_perawatan ENUM('Mudah','Sedang','Sulit') NOT NULL,
 foto VARCHAR(255),
 deskripsi TEXT,
 status ENUM('Tersedia','Habis','Pre-order') DEFAULT 'Tersedia',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE tank (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nama VARCHAR(100) NOT NULL,
 ukuran_liter INT,
 jenis_air VARCHAR(50),
 suhu VARCHAR(50),
 ph VARCHAR(50),
 kapasitas INT,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pesanan (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nomor_pesanan VARCHAR(50) NOT NULL UNIQUE,
 nama_pelanggan VARCHAR(100) NOT NULL,
 no_hp VARCHAR(30) NOT NULL,
 alamat TEXT NOT NULL,
 metode_pengiriman VARCHAR(50) NOT NULL,
 metode_bayar VARCHAR(50) NOT NULL,
 total_harga INT NOT NULL,
 status ENUM('Pending','Diproses','Dikirim','Selesai') DEFAULT 'Pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE detail_pesanan (
 id INT AUTO_INCREMENT PRIMARY KEY,
 pesanan_id INT NOT NULL,
 ikan_id INT NOT NULL,
 jumlah INT NOT NULL,
 harga_satuan INT NOT NULL,
 FOREIGN KEY (pesanan_id) REFERENCES pesanan(id) ON DELETE CASCADE,
 FOREIGN KEY (ikan_id) REFERENCES ikan(id) ON DELETE CASCADE
);

CREATE TABLE perawatan (
 id INT AUTO_INCREMENT PRIMARY KEY,
 tank_id INT,
 nama_kegiatan VARCHAR(150),
 jadwal DATE,
 status ENUM('Pending','Selesai') DEFAULT 'Pending',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE pengeluaran (
 id INT AUTO_INCREMENT PRIMARY KEY,
 kategori ENUM('Pakan','Obat','Aksesori','Listrik','Lainnya'),
 keterangan TEXT,
 jumlah INT,
 tanggal DATE,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO ikan (nama,nama_latin,kategori_air,kategori_sifat,kategori_jenis,harga,stok,ukuran_cm,tingkat_perawatan,foto,deskripsi,status) VALUES
('Cupang Halfmoon','Betta splendens','Tawar','Non-Predator','Hias',35000,20,5,'Mudah','','Cupang cantik cocok untuk pemula.','Tersedia'),
('Arwana Super Red','Scleropages formosus','Tawar','Predator','Langka',2500000,3,25,'Sulit','','Ikan predator premium.','Tersedia'),
('Oscar Tiger','Astronotus ocellatus','Tawar','Predator','Hias',85000,8,12,'Sedang','','Oscar aktif dan kuat.','Tersedia'),
('Guppy Cobra','Poecilia reticulata','Tawar','Non-Predator','Hias',15000,30,3,'Mudah','','Guppy warna cerah.','Tersedia'),
('Molly Balon','Poecilia sphenops','Payau','Non-Predator','Hias',12000,25,4,'Mudah','','Molly mudah dirawat.','Tersedia'),
('Clownfish','Amphiprioninae','Laut','Non-Predator','Hias',180000,10,6,'Sedang','','Ikan laut populer.','Tersedia'),
('Blue Tang','Paracanthurus hepatus','Laut','Non-Predator','Hias',350000,5,8,'Sulit','','Ikan laut biru cantik.','Tersedia'),
('Louhan Cencu','Flowerhorn','Tawar','Predator','Hias',450000,4,15,'Sedang','','Louhan jenong premium.','Tersedia'),
('Discus Red','Symphysodon','Tawar','Non-Predator','Hias',275000,6,10,'Sulit','','Discus elegan.','Tersedia'),
('Koi Kohaku','Cyprinus rubrofuscus','Tawar','Non-Predator','Hias',125000,12,18,'Sedang','','Koi cocok untuk kolam.','Tersedia'),
('Pacu Albino','Piaractus brachypomus','Tawar','Predator','Konsumsi',95000,7,10,'Sedang','','Pacu aktif dan besar.','Tersedia'),
('Mandarin Fish','Synchiropus splendidus','Laut','Non-Predator','Langka',500000,2,5,'Sulit','','Ikan laut langka.','Pre-order');

INSERT INTO tank (nama,ukuran_liter,jenis_air,suhu,ph,kapasitas) VALUES
('Tank Predator',500,'Tawar','27-29 C','6.5-7.5',10),
('Tank Laut Premium',300,'Laut','25-27 C','8.0-8.4',15),
('Tank Komunitas',200,'Tawar','26-28 C','6.8-7.4',40);

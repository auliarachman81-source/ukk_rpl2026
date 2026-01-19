-- Database: pengaduan_sekolah
CREATE DATABASE IF NOT EXISTS pengaduan_sekolah;
USE pengaduan_sekolah;

-- Tabel users (untuk admin dan siswa)
CREATE TABLE IF NOT EXISTS users (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin', 'siswa') NOT NULL,
    nis VARCHAR(20) NULL,
    kelas VARCHAR(20) NULL,
    email VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabel kategori pengaduan
CREATE TABLE IF NOT EXISTS kategori (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT NULL
);

-- Tabel pengaduan
CREATE TABLE IF NOT EXISTS pengaduan (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    kategori_id INT(11) NOT NULL,
    judul VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    lokasi VARCHAR(100) NOT NULL,
    foto VARCHAR(255) NULL,
    status ENUM('pending', 'proses', 'selesai', 'ditolak') DEFAULT 'pending',
    tanggal_lapor TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tanggal_selesai DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (kategori_id) REFERENCES kategori(id) ON DELETE CASCADE
);

-- Tabel feedback
CREATE TABLE IF NOT EXISTS feedback (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    pengaduan_id INT(11) NOT NULL,
    pesan TEXT NOT NULL,
    tanggal_feedback TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pengaduan_id) REFERENCES pengaduan(id) ON DELETE CASCADE
);

-- Insert data default kategori
INSERT INTO kategori (nama_kategori, deskripsi) VALUES
('Kerusakan Meja/Kursi', 'Laporan kerusakan meja atau kursi di kelas'),
('Kerusakan Toilet', 'Laporan kerusakan fasilitas toilet'),
('Kerusakan Lampu', 'Laporan kerusakan lampu di area sekolah'),
('Kerusakan AC/Kipas', 'Laporan kerusakan AC atau kipas angin'),
('Kerusakan Papan Tulis', 'Laporan kerusakan papan tulis'),
('Lainnya', 'Kategori pengaduan lainnya');

-- Insert data default admin
-- Username: aoel123
-- Password: aoel (plain text, tidak di-hash)
INSERT INTO users (username, password, nama_lengkap, role) VALUES
('aoel123', 'aoel', 'Administrator', 'admin');

-- Insert data default siswa untuk testing
-- Password: siswa123 (plain text, tidak di-hash)
INSERT INTO users (username, password, nama_lengkap, role, nis, kelas, email) VALUES
('siswa01', 'siswa123', 'Budi Santoso', 'siswa', '2026001', 'XII RPL 1', 'budi@example.com');

-- File generate_password.php tidak diperlukan karena password menggunakan plain text


-- Database: perpustakaan_persma

CREATE DATABASE IF NOT EXISTS perpustakaan_persma;
USE perpustakaan_persma;

-- Tabel Jabatan
CREATE TABLE jabatan (
    id_jabatan INT AUTO_INCREMENT PRIMARY KEY,
    nama_jabatan VARCHAR(50) NOT NULL
);

-- Tabel User
CREATE TABLE user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    jabatan_id INT,
    divisi VARCHAR(100),
    angkatan VARCHAR(10),
    no_wa VARCHAR(20),
    FOREIGN KEY (jabatan_id) REFERENCES jabatan(id_jabatan)
);

-- Tabel Buku
CREATE TABLE buku (
    id_buku INT AUTO_INCREMENT PRIMARY KEY,
    judul VARCHAR(200) NOT NULL,
    pengarang VARCHAR(100) NOT NULL,
    kategori VARCHAR(50),
    status ENUM('tersedia', 'dipinjam', 'rusak') DEFAULT 'tersedia'
);

-- Tabel Peminjaman
CREATE TABLE peminjaman (
    id_peminjaman INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT,
    id_buku INT,
    tanggal_pinjam DATE NOT NULL,
    estimasi_kembali DATE NOT NULL,
    status ENUM('aktif', 'dikembalikan', 'terlambat') DEFAULT 'aktif',
    FOREIGN KEY (id_user) REFERENCES user(id_user),
    FOREIGN KEY (id_buku) REFERENCES buku(id_buku)
);

-- Tabel Pengembalian
CREATE TABLE pengembalian (
    id_pengembalian INT AUTO_INCREMENT PRIMARY KEY,
    id_peminjaman INT,
    tanggal_kembali DATE NOT NULL,
    id_user_pengelola INT,
    FOREIGN KEY (id_peminjaman) REFERENCES peminjaman(id_peminjaman),
    FOREIGN KEY (id_user_pengelola) REFERENCES user(id_user)
);

-- Insert data jabatan
INSERT INTO jabatan (nama_jabatan) VALUES 
('Administrator'),
('Anggota');

-- Insert data user demo
INSERT INTO user (nama, username, password, jabatan_id, divisi, angkatan, no_wa) VALUES
('Administrator Persma', 'admin', 'admin123', 1, 'Pengurus Inti', '2022', '081234567890'),
('Anggota Persma', 'anggota', 'anggota123', 2, 'Anggota Biasa', '2023', '081234567891');

-- Insert data buku demo
INSERT INTO buku (judul, pengarang, kategori, status) VALUES
('Panduan Organisasi Mahasiswa', 'Dr. Ahmad Susanto', 'Manajemen', 'tersedia'),
('Kepemimpinan di Era Digital', 'Prof. Sari Melati', 'Leadership', 'dipinjam'),
('Komunikasi Efektif dalam Tim', 'Ir. Budi Hartono', 'Komunikasi', 'tersedia'),
('Manajemen Event dan Kegiatan', 'Dra. Lisa Permata', 'Event Management', 'tersedia'),
('Strategi Fundraising Organisasi', 'M. Rizki Pratama', 'Finance', 'dipinjam');

-- Insert data peminjaman demo
INSERT INTO peminjaman (id_user, id_buku, tanggal_pinjam, estimasi_kembali, status) VALUES
(2, 2, '2024-06-18', '2024-06-25', 'aktif'),
(2, 5, '2024-06-20', '2024-06-27', 'terlambat');

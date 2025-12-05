-- Database db_toko_outdoor
CREATE DATABASE IF NOT EXISTS db_toko_outdoor;
USE db_toko_outdoor;

-- Tabel User (role-based access)
CREATE TABLE IF NOT EXISTS user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'kasir', 'owner') NOT NULL DEFAULT 'kasir',
    nama_lengkap VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('aktif', 'nonaktif') DEFAULT 'aktif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Produk (data perlengkapan outdoor)
CREATE TABLE IF NOT EXISTS produk (
    id_produk INT AUTO_INCREMENT PRIMARY KEY,
    nama_produk VARCHAR(100) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    deskripsi TEXT,
    harga DECIMAL(10, 2) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    gambar VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('tersedia', 'habis', 'tidak_diproduksi') DEFAULT 'tersedia'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Transaksi (pembelian produk)
CREATE TABLE IF NOT EXISTS transaksi (
    id_transaksi INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    tanggal_transaksi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_harga DECIMAL(10, 2) NOT NULL,
    metode_pembayaran ENUM('tunai', 'kartu_kredit', 'transfer_bank') NOT NULL DEFAULT 'tunai',
    status ENUM('selesai', 'pending', 'dibatalkan') DEFAULT 'selesai',
    keterangan TEXT,
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabel Detail Transaksi (detail item dalam transaksi)
CREATE TABLE IF NOT EXISTS detail_transaksi (
    id_detail INT AUTO_INCREMENT PRIMARY KEY,
    id_transaksi INT NOT NULL,
    id_produk INT NOT NULL,
    jumlah INT NOT NULL,
    harga_satuan DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(id_transaksi) ON DELETE CASCADE,
    FOREIGN KEY (id_produk) REFERENCES produk(id_produk) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert data user default untuk testing
INSERT INTO user (username, password, role, nama_lengkap, email, phone) VALUES
('admin', '$2y$10$XPviGaN1z3F8046MURakcu7reT8Q4Vj3vYnTYS/erGBwbiMGAYw16', 'admin', 'Administrator', 'admin@toko-outdoor.com', '082123456789'),
('kasir', '$2y$10$t3zL1a1ZaGb9xq4eb2LVSulDPzIBDHiBAJXuhTgcyihOavjaKBxVS', 'kasir', 'Kasir', 'kasir@toko-outdoor.com', '082987654321'),
('owner', '$2y$10$aZNEhcG9OF1fshAP93wTkOnFP0fPL3rNPWso2NWhJ3K6iyF9nMlle', 'owner', 'Pemilik Toko', 'owner@toko-outdoor.com', '082111222333');

-- Catatan Password: semua user memiliki password 'password' yang sudah di-hash dengan bcrypt
-- Password hash di-generate menggunakan: password_hash('password', PASSWORD_BCRYPT)

-- Insert data produk sample
INSERT INTO produk (nama_produk, kategori, deskripsi, harga, stok) VALUES
('Tenda Camping 4 Orang', 'Tenda', 'Tenda berkualitas tinggi untuk 4 orang', 450000.00, 15),
('Sleeping Bag Premium', 'Sleeping Bag', 'Sleeping bag hangat untuk musim dingin', 350000.00, 20),
('Tas Gunung 50L', 'Tas', 'Tas gunung dengan kapasitas 50 liter', 550000.00, 10),
('Jaket Outdoor Waterproof', 'Pakaian', 'Jaket tahan air dan tahan angin', 450000.00, 25),
('Sepatu Hiking Profesional', 'Sepatu', 'Sepatu hiking dengan grip kuat', 650000.00, 12),
('Headlamp LED 5W', 'Penerangan', 'Lampu kepala LED dengan baterai tahan lama', 150000.00, 30),
('Nesting Cookware Set', 'Peralatan Masak', 'Set peralatan masak outdoor', 280000.00, 18),
('Kompas Digital', 'Navigasi', 'Kompas digital dengan akurasi tinggi', 120000.00, 22),
('Rope 50 Meter', 'Rope', 'Tali climbing berkualitas tinggi', 200000.00, 14),
('Camel Water Bottle 2L', 'Botol Air', 'Botol air dengan kapasitas 2 liter', 85000.00, 40);

-- Create Index untuk performa query
CREATE INDEX idx_user_role ON user(role);
CREATE INDEX idx_produk_kategori ON produk(kategori);
CREATE INDEX idx_produk_status ON produk(status);
CREATE INDEX idx_transaksi_user ON transaksi(id_user);
CREATE INDEX idx_transaksi_tanggal ON transaksi(tanggal_transaksi);
CREATE INDEX idx_detail_transaksi ON detail_transaksi(id_transaksi);

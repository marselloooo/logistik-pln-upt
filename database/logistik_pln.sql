-- =========================================================
-- DATABASE: logistik_pln
-- Sistem Informasi Monitoring Persediaan Material
-- Proyek Mahasiswa Magang - Divisi Logistik PLN UPT
-- (Data dummy/contoh, bukan sistem resmi PLN)
-- =========================================================

CREATE DATABASE IF NOT EXISTS logistik_pln;
USE logistik_pln;

-- ---------------------------------------------------------
-- Tabel users (untuk login)
-- ---------------------------------------------------------
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL
);

-- PENTING:
-- Akun demo (username: admin, password: admin123) TIDAK di-insert di sini,
-- karena password harus di-hash menggunakan fungsi password_hash() bawaan PHP
-- agar hasil hash selalu valid dan aman.
--
-- Cara membuat akun admin:
-- 1. Import file ini terlebih dahulu ke phpMyAdmin (buat tabel-tabelnya).
-- 2. Buka file config/setup_admin.php lewat browser, contoh:
--    http://localhost/LOGISTIK-PLN/config/setup_admin.php
-- 3. Akun admin (admin / admin123) akan otomatis dibuat.
-- 4. Setelah berhasil, file config/setup_admin.php sebaiknya dihapus/diberi nama lain
--    agar tidak bisa diakses ulang.

-- ---------------------------------------------------------
-- Tabel materials
-- ---------------------------------------------------------
CREATE TABLE materials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_material VARCHAR(20) NOT NULL UNIQUE,
    nama_material VARCHAR(150) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    minimum_stok INT NOT NULL DEFAULT 0,
    lokasi VARCHAR(50) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- Tabel suppliers
-- ---------------------------------------------------------
CREATE TABLE suppliers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_supplier VARCHAR(20) NOT NULL UNIQUE,
    nama_supplier VARCHAR(150) NOT NULL,
    alamat TEXT,
    telepon VARCHAR(20),
    email VARCHAR(100),
    pic VARCHAR(100)
);

-- ---------------------------------------------------------
-- Tabel stock_in (Barang Masuk)
-- ---------------------------------------------------------
CREATE TABLE stock_in (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_transaksi VARCHAR(30) NOT NULL UNIQUE,
    tanggal DATE NOT NULL,
    supplier_id INT NOT NULL,
    material_id INT NOT NULL,
    jumlah INT NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

-- ---------------------------------------------------------
-- Tabel stock_out (Barang Keluar)
-- ---------------------------------------------------------
CREATE TABLE stock_out (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nomor_transaksi VARCHAR(30) NOT NULL UNIQUE,
    tanggal DATE NOT NULL,
    material_id INT NOT NULL,
    jumlah INT NOT NULL,
    unit_peminta VARCHAR(100) NOT NULL,
    nama_peminta VARCHAR(100) NOT NULL,
    keterangan TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (material_id) REFERENCES materials(id)
);

-- =========================================================
-- DATA DUMMY / CONTOH
-- =========================================================

-- Supplier contoh
INSERT INTO suppliers (kode_supplier, nama_supplier, alamat, telepon, email, pic) VALUES
('SUP-001', 'CV Sumber Listrik Jaya', 'Jl. Industri No. 12, Palembang', '0711-123456', 'sumberlistrik@example.com', 'Budi Santoso'),
('SUP-002', 'PT Cahaya Elektrindo', 'Jl. Merdeka No. 45, Palembang', '0711-654321', 'cahayaelektrindo@example.com', 'Siti Aminah'),
('SUP-003', 'Toko Material Terang', 'Jl. Sudirman No. 8, Palembang', '0711-998877', 'materialterang@example.com', 'Andi Wijaya');

-- Material contoh
INSERT INTO materials (kode_material, nama_material, kategori, satuan, stok, minimum_stok, lokasi, keterangan) VALUES
('MT-001', 'Kabel NYM 2x1.5', 'Kabel', 'Meter', 500, 100, 'Gudang A', 'Kabel instalasi standar'),
('MT-002', 'MCB 10A', 'Komponen Listrik', 'Unit', 50, 10, 'Gudang A', 'Pengaman arus listrik'),
('MT-003', 'Lampu LED 20W', 'Lampu', 'Unit', 30, 10, 'Gudang B', 'Lampu hemat energi'),
('MT-004', 'Isolasi Kabel', 'Aksesoris', 'Roll', 8, 10, 'Gudang A', 'Isolasi listrik hitam'),
('MT-005', 'Fitting Lampu', 'Aksesoris', 'Unit', 0, 5, 'Gudang B', 'Dudukan lampu bulat');

-- Barang masuk contoh
INSERT INTO stock_in (nomor_transaksi, tanggal, supplier_id, material_id, jumlah, keterangan) VALUES
('IN-0001', '2026-07-01', 1, 1, 100, 'Pengiriman rutin bulanan'),
('IN-0002', '2026-07-05', 2, 2, 20, 'Restok komponen listrik'),
('IN-0003', '2026-07-10', 3, 3, 15, 'Pengadaan lampu baru');

-- Barang keluar contoh
INSERT INTO stock_out (nomor_transaksi, tanggal, material_id, jumlah, unit_peminta, nama_peminta, keterangan) VALUES
('OUT-0001', '2026-07-03', 1, 50, 'Unit Distribusi', 'Rahmat Hidayat', 'Kebutuhan perbaikan jaringan'),
('OUT-0002', '2026-07-08', 2, 5, 'Unit Transmisi', 'Dewi Lestari', 'Penggantian MCB rusak');

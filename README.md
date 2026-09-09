# Sistem Informasi Monitoring Persediaan Material
Proyek Mahasiswa Magang — Divisi Logistik PLN UPT
(Aplikasi ini adalah proyek magang mahasiswa, bukan sistem resmi PLN. Semua data adalah data dummy/contoh.)

## Teknologi
- HTML5, CSS3, JavaScript
- PHP Native (tanpa framework)
- MySQL
- Bootstrap 5 + Bootstrap Icons
- Chart.js (grafik dashboard)

## Cara Menjalankan (Windows + XAMPP/Laragon)

### 1. Salin folder proyek
Salin folder `LOGISTIK-PLN` ke dalam folder `htdocs` (XAMPP) atau `www` (Laragon), contoh:
```
C:\xampp\htdocs\LOGISTIK-PLN
```
atau
```
C:\laragon\www\LOGISTIK-PLN
```

### 2. Nyalakan Apache dan MySQL
Buka XAMPP Control Panel atau Laragon, lalu Start Apache dan MySQL.

### 3. Buat database
1. Buka phpMyAdmin: `http://localhost/phpmyadmin`
2. Buat database baru (atau biarkan, karena file SQL sudah membuat database otomatis)
3. Klik menu **Import**, pilih file `database/logistik_pln.sql`, lalu klik **Go**

Ini akan membuat database `logistik_pln` beserta seluruh tabel dan data contoh (material, supplier, transaksi).

### 4. Buat akun admin
Karena password harus di-hash dengan fungsi PHP (`password_hash`), buka file berikut lewat browser **satu kali saja**:
```
http://localhost/LOGISTIK-PLN/config/setup_admin.php
```
Halaman ini akan otomatis membuat akun:
- Username: `admin`
- Password: `admin123`

Setelah muncul pesan berhasil, sebaiknya file `config/setup_admin.php` dihapus atau diganti nama agar tidak bisa diakses ulang oleh orang lain.

### 5. Buka aplikasi
Buka browser dan akses:
```
http://localhost/LOGISTIK-PLN/
```
Login menggunakan akun admin di atas.

## Struktur Folder
```
LOGISTIK-PLN/
├── index.php              -> gerbang awal, redirect ke login/dashboard
├── login.php               -> halaman login
├── logout.php               -> proses logout
├── dashboard.php             -> dashboard utama
├── material/                -> CRUD Data Material
├── supplier/                -> CRUD Supplier
├── barang-masuk/             -> transaksi barang masuk (stok bertambah)
├── barang-keluar/             -> transaksi barang keluar (stok berkurang)
├── monitoring/               -> monitoring status stok (AMAN/MENIPIS/HABIS)
├── laporan/                 -> laporan persediaan, barang masuk, barang keluar
├── config/
│   ├── database.php          -> koneksi PDO ke MySQL
│   ├── session.php           -> proteksi login (wajib login untuk akses halaman)
│   └── setup_admin.php        -> setup akun admin pertama kali (hapus setelah dipakai)
├── includes/
│   ├── header.php            -> bagian atas HTML + navbar
│   ├── sidebar.php            -> menu sidebar
│   └── footer.php            -> bagian bawah HTML + script
├── assets/
│   ├── css/style.css         -> tampilan (warna biru, putih, abu-abu)
│   └── js/script.js          -> toggle sidebar HP + konfirmasi hapus
└── database/
    └── logistik_pln.sql       -> struktur tabel + data contoh
```

## Keamanan yang Diterapkan
- Password disimpan dalam bentuk **hash** (bcrypt via `password_hash()`), bukan teks biasa
- Seluruh query database menggunakan **prepared statement** (PDO) agar aman dari SQL Injection
- Setiap halaman (kecuali login) memakai `config/session.php` untuk memastikan pengguna sudah login
- Validasi input dilakukan di sisi server sebelum data disimpan
- Transaksi barang keluar divalidasi agar jumlah tidak melebihi stok yang tersedia

## Alur Stok
- **Barang Masuk** disimpan → stok material otomatis **bertambah**
- **Barang Keluar** disimpan → stok material otomatis **berkurang**
  - Jika jumlah keluar melebihi stok, sistem menampilkan pesan **"Stok tidak mencukupi."** dan transaksi tidak disimpan
- **Status stok** di halaman Monitoring dan Dashboard dihitung otomatis:
  - `stok = 0` → **HABIS**
  - `stok <= minimum_stok` → **MENIPIS**
  - `stok > minimum_stok` → **AMAN**

## Akun Demo
- Username: `admin`
- Password: `admin123`

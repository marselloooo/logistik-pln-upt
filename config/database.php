<?php
/**
 * File koneksi database
 * Menggunakan PDO agar aman dari SQL Injection (prepared statement)
 */

$host = "localhost";
$dbname = "logistik_pln";
$dbuser = "root";      // default XAMPP/Laragon
$dbpass = "";          // default XAMPP/Laragon (kosong)

try {
    $koneksi = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass);
    // Supaya error ditampilkan sebagai exception (memudahkan debugging)
    $koneksi->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}

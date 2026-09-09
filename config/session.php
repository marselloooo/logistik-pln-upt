<?php
/**
 * File pengecekan session login
 * Panggil file ini di setiap halaman yang butuh login (kecuali login.php)
 */

session_start();

// BASE_URL = folder utama aplikasi di localhost
// Sesuaikan jika nama folder di htdocs/www berbeda dari "LOGISTIK-PLN"
if (!defined('BASE_URL')) {
    define('BASE_URL', '/LOGISTIK-PLN');
}

// Jika belum login, tendang ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/login.php");
    exit;
}

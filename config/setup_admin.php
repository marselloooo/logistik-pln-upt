<?php
/**
 * File ini HANYA dipakai SATU KALI untuk membuat akun admin demo.
 * Setelah akun berhasil dibuat, sebaiknya file ini dihapus dari server.
 *
 * Cara pakai:
 * Buka lewat browser: http://localhost/LOGISTIK-PLN/config/setup_admin.php
 */

require_once 'database.php';

$username = "admin";
$password_asli = "admin123";
$nama = "Administrator";

// Hash password dengan fungsi bawaan PHP (bcrypt)
$password_hash = password_hash($password_asli, PASSWORD_DEFAULT);

try {
    // Cek apakah username sudah ada
    $cek = $koneksi->prepare("SELECT id FROM users WHERE username = :username");
    $cek->execute([':username' => $username]);

    if ($cek->rowCount() > 0) {
        echo "Akun admin sudah ada. Tidak perlu setup ulang.";
    } else {
        $stmt = $koneksi->prepare("INSERT INTO users (username, password, nama) VALUES (:username, :password, :nama)");
        $stmt->execute([
            ':username' => $username,
            ':password' => $password_hash,
            ':nama' => $nama
        ]);
        echo "Akun admin berhasil dibuat!<br>";
        echo "Username: admin<br>";
        echo "Password: admin123<br><br>";
        echo "<b>PENTING:</b> Hapus atau ganti nama file config/setup_admin.php sekarang juga.";
    }
} catch (PDOException $e) {
    echo "Terjadi kesalahan: " . $e->getMessage();
}

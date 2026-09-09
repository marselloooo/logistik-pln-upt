<?php
require_once '../config/session.php';
require_once '../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    try {
        $stmt = $koneksi->prepare("DELETE FROM suppliers WHERE id = :id");
        $stmt->execute([':id' => $id]);
        header("Location: index.php?notif=hapus");
        exit;
    } catch (PDOException $e) {
        // Jika supplier masih dipakai di data barang masuk, hapus akan gagal (foreign key)
        header("Location: index.php?notif=gagal_hapus");
        exit;
    }
}

header("Location: index.php");
exit;

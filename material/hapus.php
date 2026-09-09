<?php
require_once '../config/session.php';
require_once '../config/database.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // Prepared statement -> aman dari SQL Injection
    $stmt = $koneksi->prepare("DELETE FROM materials WHERE id = :id");
    $stmt->execute([':id' => $id]);
}

header("Location: index.php?notif=hapus");
exit;

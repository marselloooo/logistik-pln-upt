<?php
session_start();
// Hapus semua data session
$_SESSION = [];
session_destroy();

header("Location: login.php");
exit;

<?php
session_start();

// Jika sudah login -> ke dashboard, jika belum -> ke login
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;

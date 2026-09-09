<?php
session_start();
require_once 'config/database.php';

if (!defined('BASE_URL')) {
    define('BASE_URL', '/LOGISTIK-PLN');
}

// Jika sudah login, langsung arahkan ke dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/dashboard.php");
    exit;
}

$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $pesan_error = "Username dan password wajib diisi.";
    } else {
        // Prepared statement -> aman dari SQL Injection
        $stmt = $koneksi->prepare("SELECT id, username, password, nama FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama'] = $user['nama'];

            header("Location: " . BASE_URL . "/dashboard.php");
            exit;
        } else {
            $pesan_error = "Username atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SI Monitoring Persediaan Material</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<div class="login-page">
    <div class="login-card">
        <div class="login-logo">
            <i class="bi bi-box-seam-fill"></i>
            <h5>Monitoring Persediaan Material</h5>
            <p>Divisi Logistik PLN UPT</p>
        </div>

        <?php if ($pesan_error): ?>
            <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($pesan_error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="mb-3">
                <label class="form-label small">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required autofocus>
                </div>
            </div>
            <div class="mb-4">
                <label class="form-label small">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-box-arrow-in-right"></i> Masuk
            </button>
        </form>

        <div class="text-center mt-3">
            
        </div>
    </div>
</div>

</body>
</html>

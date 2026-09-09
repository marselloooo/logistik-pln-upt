<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($judul_halaman) ? $judul_halaman . ' - ' : ''; ?>SI Monitoring Persediaan Material</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- CSS Custom -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>

<!-- NAVBAR ATAS -->
<nav class="navbar navbar-expand navbar-top">
    <div class="container-fluid">
        <button class="btn btn-sm btn-toggle-sidebar d-md-none" id="btnToggleSidebar">
            <i class="bi bi-list"></i>
        </button>
        <span class="navbar-brand-text d-none d-md-inline">
            Sistem Informasi Monitoring Persediaan Material
        </span>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-muted small d-none d-sm-inline">
                <i class="bi bi-person-circle"></i>
                <?php echo htmlspecialchars($_SESSION['nama'] ?? 'Pengguna'); ?>
            </span>
            <a href="<?php echo BASE_URL; ?>/logout.php" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</nav>

<div class="app-wrapper">

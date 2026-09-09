<?php
// $halaman_aktif dikirim dari masing-masing halaman untuk menandai menu aktif
$halaman_aktif = $halaman_aktif ?? '';

function menuAktif($nama, $halaman_aktif) {
    return $nama === $halaman_aktif ? 'active' : '';
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <i class="bi bi-box-seam-fill"></i>
        <div>
            <div class="sidebar-logo-title">Monitoring Persediaan</div>
            <div class="sidebar-logo-sub">Divisi Logistik PLN UPT</div>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="<?php echo BASE_URL; ?>/dashboard.php" class="<?php echo menuAktif('dashboard', $halaman_aktif); ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>/material/index.php" class="<?php echo menuAktif('material', $halaman_aktif); ?>">
                <i class="bi bi-box-seam"></i> Data Material
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>/supplier/index.php" class="<?php echo menuAktif('supplier', $halaman_aktif); ?>">
                <i class="bi bi-truck"></i> Supplier
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>/barang-masuk/index.php" class="<?php echo menuAktif('barang-masuk', $halaman_aktif); ?>">
                <i class="bi bi-box-arrow-in-down"></i> Barang Masuk
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>/barang-keluar/index.php" class="<?php echo menuAktif('barang-keluar', $halaman_aktif); ?>">
                <i class="bi bi-box-arrow-up"></i> Barang Keluar
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>/monitoring/index.php" class="<?php echo menuAktif('monitoring', $halaman_aktif); ?>">
                <i class="bi bi-graph-up-arrow"></i> Monitoring Stok
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>/laporan/index.php" class="<?php echo menuAktif('laporan', $halaman_aktif); ?>">
                <i class="bi bi-file-earmark-text"></i> Laporan
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>/logout.php">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </li>
    </ul>
</aside>

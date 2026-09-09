<?php
require_once '../config/session.php';
require_once '../config/database.php';

$halaman_aktif = 'monitoring';
$judul_halaman = 'Monitoring Stok';

$cari = trim($_GET['cari'] ?? '');

$where = '';
$params = [];
if ($cari !== '') {
    $where = "WHERE kode_material LIKE :cari OR nama_material LIKE :cari";
    $params[':cari'] = '%' . $cari . '%';
}

$stmt = $koneksi->prepare("SELECT * FROM materials $where ORDER BY nama_material ASC");
$stmt->execute($params);
$daftarMaterial = $stmt->fetchAll(PDO::FETCH_ASSOC);

/**
 * Menentukan status stok berdasarkan aturan:
 * - stok = 0        -> HABIS
 * - stok <= minimum -> MENIPIS
 * - stok > minimum  -> AMAN
 */
function statusStok($stok, $minimum) {
    if ($stok == 0) {
        return ['label' => 'HABIS', 'kelas' => 'badge-habis'];
    } elseif ($stok <= $minimum) {
        return ['label' => 'MENIPIS', 'kelas' => 'badge-menipis'];
    } else {
        return ['label' => 'AMAN', 'kelas' => 'badge-aman'];
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="main-content">
    <h4 class="page-title">Monitoring Stok</h4>

    <div class="panel">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-8">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari kode atau nama material..." value="<?php echo htmlspecialchars($cari); ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-outline-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Material</th>
                        <th>Kategori</th>
                        <th>Lokasi</th>
                        <th>Stok</th>
                        <th>Minimum</th>
                        <th>Satuan</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($daftarMaterial) === 0): ?>
                        <tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data material.</td></tr>
                    <?php else: ?>
                        <?php foreach ($daftarMaterial as $m): ?>
                            <?php $status = statusStok($m['stok'], $m['minimum_stok']); ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['kode_material']); ?></td>
                                <td><?php echo htmlspecialchars($m['nama_material']); ?></td>
                                <td><?php echo htmlspecialchars($m['kategori']); ?></td>
                                <td><?php echo htmlspecialchars($m['lokasi']); ?></td>
                                <td><?php echo $m['stok']; ?></td>
                                <td><?php echo $m['minimum_stok']; ?></td>
                                <td><?php echo htmlspecialchars($m['satuan']); ?></td>
                                <td><span class="badge <?php echo $status['kelas']; ?>"><?php echo $status['label']; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

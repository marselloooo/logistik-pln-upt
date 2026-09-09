<?php
require_once '../config/session.php';
require_once '../config/database.php';

$halaman_aktif = 'supplier';
$judul_halaman = 'Supplier';

$cari = trim($_GET['cari'] ?? '');

$where = '';
$params = [];
if ($cari !== '') {
    $where = "WHERE kode_supplier LIKE :cari OR nama_supplier LIKE :cari";
    $params[':cari'] = '%' . $cari . '%';
}

$stmt = $koneksi->prepare("SELECT * FROM suppliers $where ORDER BY id DESC");
$stmt->execute($params);
$daftarSupplier = $stmt->fetchAll(PDO::FETCH_ASSOC);

$notifikasi = $_GET['notif'] ?? '';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h4 class="page-title mb-0">Supplier</h4>
        <a href="tambah.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Tambah Supplier
        </a>
    </div>

    <?php if ($notifikasi === 'tambah'): ?>
        <div class="alert alert-success py-2 small">Supplier berhasil ditambahkan.</div>
    <?php elseif ($notifikasi === 'edit'): ?>
        <div class="alert alert-success py-2 small">Supplier berhasil diperbarui.</div>
    <?php elseif ($notifikasi === 'hapus'): ?>
        <div class="alert alert-success py-2 small">Supplier berhasil dihapus.</div>
    <?php elseif ($notifikasi === 'gagal_hapus'): ?>
        <div class="alert alert-danger py-2 small">Supplier tidak dapat dihapus karena masih memiliki data transaksi Barang Masuk.</div>
    <?php endif; ?>

    <div class="panel">
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-8">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari kode atau nama supplier..." value="<?php echo htmlspecialchars($cari); ?>">
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
                        <th>Nama Supplier</th>
                        <th>Alamat</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th>PIC</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($daftarSupplier) === 0): ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada data supplier.</td></tr>
                    <?php else: ?>
                        <?php foreach ($daftarSupplier as $s): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($s['kode_supplier']); ?></td>
                                <td><?php echo htmlspecialchars($s['nama_supplier']); ?></td>
                                <td><?php echo htmlspecialchars($s['alamat']); ?></td>
                                <td><?php echo htmlspecialchars($s['telepon']); ?></td>
                                <td><?php echo htmlspecialchars($s['email']); ?></td>
                                <td><?php echo htmlspecialchars($s['pic']); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="#" onclick="konfirmasiHapus('hapus.php?id=<?php echo $s['id']; ?>', '<?php echo htmlspecialchars($s['nama_supplier'], ENT_QUOTES); ?>'); return false;" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

<?php
require_once '../config/session.php';
require_once '../config/database.php';

$halaman_aktif = 'material';
$judul_halaman = 'Data Material';

// ---------- SEARCH & FILTER ----------
$cari = trim($_GET['cari'] ?? '');
$filterKategori = trim($_GET['kategori'] ?? '');

// ---------- PAGINATION ----------
$halaman = isset($_GET['halaman']) ? max(1, (int)$_GET['halaman']) : 1;
$batasPerHalaman = 10;
$offset = ($halaman - 1) * $batasPerHalaman;

// ---------- BANGUN QUERY (dengan prepared statement) ----------
$where = [];
$params = [];

if ($cari !== '') {
    $where[] = "(kode_material LIKE :cari OR nama_material LIKE :cari)";
    $params[':cari'] = '%' . $cari . '%';
}
if ($filterKategori !== '') {
    $where[] = "kategori = :kategori";
    $params[':kategori'] = $filterKategori;
}
$sqlWhere = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// Hitung total data untuk pagination
$stmtHitung = $koneksi->prepare("SELECT COUNT(*) FROM materials $sqlWhere");
$stmtHitung->execute($params);
$totalData = $stmtHitung->fetchColumn();
$totalHalaman = max(1, ceil($totalData / $batasPerHalaman));

// Ambil data material
$sql = "SELECT * FROM materials $sqlWhere ORDER BY id DESC LIMIT :limit OFFSET :offset";
$stmt = $koneksi->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->bindValue(':limit', $batasPerHalaman, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$daftarMaterial = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil daftar kategori unik untuk dropdown filter
$daftarKategori = $koneksi->query("SELECT DISTINCT kategori FROM materials ORDER BY kategori")->fetchAll(PDO::FETCH_COLUMN);

// Pesan notifikasi dari proses tambah/edit/hapus
$notifikasi = $_GET['notif'] ?? '';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <h4 class="page-title mb-0">Data Material</h4>
        <a href="tambah.php" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-lg"></i> Tambah Material
        </a>
    </div>

    <?php if ($notifikasi === 'tambah'): ?>
        <div class="alert alert-success py-2 small">Material berhasil ditambahkan.</div>
    <?php elseif ($notifikasi === 'edit'): ?>
        <div class="alert alert-success py-2 small">Material berhasil diperbarui.</div>
    <?php elseif ($notifikasi === 'hapus'): ?>
        <div class="alert alert-success py-2 small">Material berhasil dihapus.</div>
    <?php endif; ?>

    <div class="panel">
        <!-- FORM SEARCH & FILTER -->
        <form method="GET" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari kode atau nama material..." value="<?php echo htmlspecialchars($cari); ?>">
            </div>
            <div class="col-md-4">
                <select name="kategori" class="form-select form-select-sm">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($daftarKategori as $kat): ?>
                        <option value="<?php echo htmlspecialchars($kat); ?>" <?php echo $filterKategori === $kat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($kat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                        <th>Satuan</th>
                        <th>Stok</th>
                        <th>Minimum</th>
                        <th>Lokasi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($daftarMaterial) === 0): ?>
                        <tr><td colspan="8" class="text-center text-muted py-3">Tidak ada data material.</td></tr>
                    <?php else: ?>
                        <?php foreach ($daftarMaterial as $m): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['kode_material']); ?></td>
                                <td><?php echo htmlspecialchars($m['nama_material']); ?></td>
                                <td><?php echo htmlspecialchars($m['kategori']); ?></td>
                                <td><?php echo htmlspecialchars($m['satuan']); ?></td>
                                <td><?php echo $m['stok']; ?></td>
                                <td><?php echo $m['minimum_stok']; ?></td>
                                <td><?php echo htmlspecialchars($m['lokasi']); ?></td>
                                <td>
                                    <a href="edit.php?id=<?php echo $m['id']; ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="#" onclick="konfirmasiHapus('hapus.php?id=<?php echo $m['id']; ?>', '<?php echo htmlspecialchars($m['nama_material'], ENT_QUOTES); ?>'); return false;" class="btn btn-sm btn-outline-danger" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- PAGINATION SEDERHANA -->
        <?php if ($totalHalaman > 1): ?>
            <nav>
                <ul class="pagination pagination-sm justify-content-end mb-0">
                    <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
                        <li class="page-item <?php echo $i === $halaman ? 'active' : ''; ?>">
                            <a class="page-link" href="?halaman=<?php echo $i; ?>&cari=<?php echo urlencode($cari); ?>&kategori=<?php echo urlencode($filterKategori); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

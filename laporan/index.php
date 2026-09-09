<?php
require_once '../config/session.php';
require_once '../config/database.php';

$halaman_aktif = 'laporan';
$judul_halaman = 'Laporan';

// ---------- AMBIL PARAMETER FILTER ----------
$jenisLaporan   = $_GET['jenis'] ?? 'persediaan'; // persediaan | masuk | keluar
$tanggalMulai   = $_GET['tanggal_mulai'] ?? '';
$tanggalAkhir   = $_GET['tanggal_akhir'] ?? '';
$materialFilter = (int)($_GET['material_id'] ?? 0);
$tampilkan      = isset($_GET['tampilkan']); // true jika tombol "Tampilkan" ditekan

// Daftar material untuk dropdown filter
$daftarMaterial = $koneksi->query("SELECT id, kode_material, nama_material FROM materials ORDER BY nama_material")->fetchAll(PDO::FETCH_ASSOC);

$dataLaporan = [];

if ($tampilkan) {
    if ($jenisLaporan === 'persediaan') {
        // Laporan Persediaan: tidak butuh filter tanggal, hanya filter material (opsional)
        $where = [];
        $params = [];
        if ($materialFilter > 0) {
            $where[] = "id = :material_id";
            $params[':material_id'] = $materialFilter;
        }
        $sqlWhere = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";
        $stmt = $koneksi->prepare("SELECT * FROM materials $sqlWhere ORDER BY nama_material");
        $stmt->execute($params);
        $dataLaporan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($jenisLaporan === 'masuk') {
        $where = [];
        $params = [];
        if ($tanggalMulai !== '') { $where[] = "si.tanggal >= :mulai"; $params[':mulai'] = $tanggalMulai; }
        if ($tanggalAkhir !== '') { $where[] = "si.tanggal <= :akhir"; $params[':akhir'] = $tanggalAkhir; }
        if ($materialFilter > 0) { $where[] = "si.material_id = :material_id"; $params[':material_id'] = $materialFilter; }
        $sqlWhere = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

        $stmt = $koneksi->prepare("
            SELECT si.nomor_transaksi, si.tanggal, si.jumlah, si.keterangan,
                   m.kode_material, m.nama_material, s.nama_supplier
            FROM stock_in si
            JOIN materials m ON m.id = si.material_id
            JOIN suppliers s ON s.id = si.supplier_id
            $sqlWhere
            ORDER BY si.tanggal DESC
        ");
        $stmt->execute($params);
        $dataLaporan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($jenisLaporan === 'keluar') {
        $where = [];
        $params = [];
        if ($tanggalMulai !== '') { $where[] = "so.tanggal >= :mulai"; $params[':mulai'] = $tanggalMulai; }
        if ($tanggalAkhir !== '') { $where[] = "so.tanggal <= :akhir"; $params[':akhir'] = $tanggalAkhir; }
        if ($materialFilter > 0) { $where[] = "so.material_id = :material_id"; $params[':material_id'] = $materialFilter; }
        $sqlWhere = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

        $stmt = $koneksi->prepare("
            SELECT so.nomor_transaksi, so.tanggal, so.jumlah, so.unit_peminta, so.nama_peminta,
                   m.kode_material, m.nama_material
            FROM stock_out so
            JOIN materials m ON m.id = so.material_id
            $sqlWhere
            ORDER BY so.tanggal DESC
        ");
        $stmt->execute($params);
        $dataLaporan = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="main-content">
    <h4 class="page-title">Laporan</h4>

    <!-- FORM FILTER (disembunyikan saat print) -->
    <div class="panel no-print">
        <div class="panel-title">Filter Laporan</div>
        <form method="GET" class="row g-3">
            <input type="hidden" name="tampilkan" value="1">

            <div class="col-md-3">
                <label class="form-label small">Jenis Laporan</label>
                <select name="jenis" class="form-select form-select-sm">
                    <option value="persediaan" <?php echo $jenisLaporan === 'persediaan' ? 'selected' : ''; ?>>Laporan Persediaan</option>
                    <option value="masuk" <?php echo $jenisLaporan === 'masuk' ? 'selected' : ''; ?>>Laporan Barang Masuk</option>
                    <option value="keluar" <?php echo $jenisLaporan === 'keluar' ? 'selected' : ''; ?>>Laporan Barang Keluar</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Tanggal Mulai</label>
                <input type="date" name="tanggal_mulai" class="form-control form-control-sm" value="<?php echo htmlspecialchars($tanggalMulai); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Tanggal Akhir</label>
                <input type="date" name="tanggal_akhir" class="form-control form-control-sm" value="<?php echo htmlspecialchars($tanggalAkhir); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small">Material</label>
                <select name="material_id" class="form-select form-select-sm">
                    <option value="0">Semua Material</option>
                    <?php foreach ($daftarMaterial as $m): ?>
                        <option value="<?php echo $m['id']; ?>" <?php echo $materialFilter === (int)$m['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($m['kode_material'] . ' - ' . $m['nama_material']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i> Tampilkan</button>
                <?php if ($tampilkan): ?>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()"><i class="bi bi-printer"></i> Print</button>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- HASIL LAPORAN -->
    <?php if ($tampilkan): ?>
        <div class="panel">
            <div class="text-center mb-3 d-none d-print-block">
                <h5 class="mb-0">SISTEM INFORMASI MONITORING PERSEDIAAN MATERIAL</h5>
                <p class="mb-0 small">Divisi Logistik PLN UPT</p>
                <hr>
            </div>

            <div class="panel-title">
                <?php
                    if ($jenisLaporan === 'persediaan') echo 'Laporan Persediaan';
                    elseif ($jenisLaporan === 'masuk') echo 'Laporan Barang Masuk';
                    else echo 'Laporan Barang Keluar';
                ?>
            </div>

            <div class="table-responsive">
                <?php if ($jenisLaporan === 'persediaan'): ?>
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Kode</th><th>Nama Material</th><th>Kategori</th><th>Stok</th><th>Minimum</th><th>Satuan</th><th>Lokasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($dataLaporan) === 0): ?>
                                <tr><td colspan="7" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                            <?php else: foreach ($dataLaporan as $d): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($d['kode_material']); ?></td>
                                    <td><?php echo htmlspecialchars($d['nama_material']); ?></td>
                                    <td><?php echo htmlspecialchars($d['kategori']); ?></td>
                                    <td><?php echo $d['stok']; ?></td>
                                    <td><?php echo $d['minimum_stok']; ?></td>
                                    <td><?php echo htmlspecialchars($d['satuan']); ?></td>
                                    <td><?php echo htmlspecialchars($d['lokasi']); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>

                <?php elseif ($jenisLaporan === 'masuk'): ?>
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>No. Transaksi</th><th>Tanggal</th><th>Material</th><th>Supplier</th><th>Jumlah</th><th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($dataLaporan) === 0): ?>
                                <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                            <?php else: foreach ($dataLaporan as $d): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($d['nomor_transaksi']); ?></td>
                                    <td><?php echo htmlspecialchars($d['tanggal']); ?></td>
                                    <td><?php echo htmlspecialchars($d['kode_material'] . ' - ' . $d['nama_material']); ?></td>
                                    <td><?php echo htmlspecialchars($d['nama_supplier']); ?></td>
                                    <td><?php echo $d['jumlah']; ?></td>
                                    <td><?php echo htmlspecialchars($d['keterangan']); ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>

                <?php else: ?>
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>No. Transaksi</th><th>Tanggal</th><th>Material</th><th>Unit Peminta</th><th>Peminta</th><th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($dataLaporan) === 0): ?>
                                <tr><td colspan="6" class="text-center text-muted py-3">Tidak ada data.</td></tr>
                            <?php else: foreach ($dataLaporan as $d): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($d['nomor_transaksi']); ?></td>
                                    <td><?php echo htmlspecialchars($d['tanggal']); ?></td>
                                    <td><?php echo htmlspecialchars($d['kode_material'] . ' - ' . $d['nama_material']); ?></td>
                                    <td><?php echo htmlspecialchars($d['unit_peminta']); ?></td>
                                    <td><?php echo htmlspecialchars($d['nama_peminta']); ?></td>
                                    <td><?php echo $d['jumlah']; ?></td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</main>

<style>
    @media print {
        .navbar-top, .sidebar, .no-print { display: none !important; }
        .main-content { padding: 0 !important; }
        body { background: #fff !important; }
    }
</style>

<?php require_once '../includes/footer.php'; ?>

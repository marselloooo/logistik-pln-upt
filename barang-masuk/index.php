<?php
require_once '../config/session.php';
require_once '../config/database.php';

$halaman_aktif = 'barang-masuk';
$judul_halaman = 'Barang Masuk';

$error = '';
$sukses = '';

// ---------- PROSES SIMPAN TRANSAKSI BARU ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_transaksi = trim($_POST['nomor_transaksi']);
    $tanggal          = trim($_POST['tanggal']);
    $supplier_id      = (int)$_POST['supplier_id'];
    $material_id      = (int)$_POST['material_id'];
    $jumlah           = (int)$_POST['jumlah'];
    $keterangan       = trim($_POST['keterangan']);

    if ($nomor_transaksi === '' || $tanggal === '' || $supplier_id <= 0 || $material_id <= 0 || $jumlah <= 0) {
        $error = "Semua field wajib diisi dengan benar dan jumlah harus lebih dari 0.";
    } else {
        // Cek nomor transaksi unik
        $cek = $koneksi->prepare("SELECT id FROM stock_in WHERE nomor_transaksi = :no");
        $cek->execute([':no' => $nomor_transaksi]);

        if ($cek->rowCount() > 0) {
            $error = "Nomor transaksi sudah pernah digunakan.";
        } else {
            // Gunakan transaction agar insert + update stok konsisten
            try {
                $koneksi->beginTransaction();

                // 1. Simpan transaksi barang masuk
                $stmt = $koneksi->prepare("INSERT INTO stock_in
                    (nomor_transaksi, tanggal, supplier_id, material_id, jumlah, keterangan)
                    VALUES (:nomor_transaksi, :tanggal, :supplier_id, :material_id, :jumlah, :keterangan)");
                $stmt->execute([
                    ':nomor_transaksi' => $nomor_transaksi,
                    ':tanggal'         => $tanggal,
                    ':supplier_id'     => $supplier_id,
                    ':material_id'     => $material_id,
                    ':jumlah'          => $jumlah,
                    ':keterangan'      => $keterangan
                ]);

                // 2. Tambah stok material (stok bertambah)
                $stmtUpdate = $koneksi->prepare("UPDATE materials SET stok = stok + :jumlah WHERE id = :id");
                $stmtUpdate->execute([':jumlah' => $jumlah, ':id' => $material_id]);

                $koneksi->commit();
                header("Location: index.php?notif=tambah");
                exit;
            } catch (PDOException $e) {
                $koneksi->rollBack();
                $error = "Terjadi kesalahan saat menyimpan transaksi.";
            }
        }
    }
}

// ---------- DATA UNTUK DROPDOWN ----------
$daftarSupplier = $koneksi->query("SELECT id, nama_supplier FROM suppliers ORDER BY nama_supplier")->fetchAll(PDO::FETCH_ASSOC);
$daftarMaterial = $koneksi->query("SELECT id, kode_material, nama_material, stok FROM materials ORDER BY nama_material")->fetchAll(PDO::FETCH_ASSOC);

// ---------- RIWAYAT TRANSAKSI BARANG MASUK ----------
$riwayat = $koneksi->query("
    SELECT si.nomor_transaksi, si.tanggal, si.jumlah, si.keterangan,
           m.kode_material, m.nama_material,
           s.nama_supplier
    FROM stock_in si
    JOIN materials m ON m.id = si.material_id
    JOIN suppliers s ON s.id = si.supplier_id
    ORDER BY si.id DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

$notifikasi = $_GET['notif'] ?? '';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="main-content">
    <h4 class="page-title">Barang Masuk</h4>

    <?php if ($notifikasi === 'tambah'): ?>
        <div class="alert alert-success py-2 small">Transaksi barang masuk berhasil disimpan. Stok material sudah diperbarui.</div>
    <?php endif; ?>

    <div class="row g-3">
        <!-- FORM INPUT -->
        <div class="col-lg-5">
            <div class="panel">
                <div class="panel-title">Input Transaksi Barang Masuk</div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small">Nomor Transaksi</label>
                        <input type="text" name="nomor_transaksi" class="form-control" placeholder="Contoh: IN-0004"
                               value="<?php echo htmlspecialchars($_POST['nomor_transaksi'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d')); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Supplier</label>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">-- Pilih Supplier --</option>
                            <?php foreach ($daftarSupplier as $s): ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['nama_supplier']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Material</label>
                        <select name="material_id" class="form-select" required>
                            <option value="">-- Pilih Material --</option>
                            <?php foreach ($daftarMaterial as $m): ?>
                                <option value="<?php echo $m['id']; ?>">
                                    <?php echo htmlspecialchars($m['kode_material'] . ' - ' . $m['nama_material'] . ' (stok: ' . $m['stok'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="2"></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-save"></i> Simpan Transaksi
                    </button>
                </form>
            </div>
        </div>

        <!-- RIWAYAT -->
        <div class="col-lg-7">
            <div class="panel">
                <div class="panel-title">Riwayat Barang Masuk</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No. Transaksi</th>
                                <th>Tanggal</th>
                                <th>Material</th>
                                <th>Supplier</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($riwayat) === 0): ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">Belum ada transaksi barang masuk.</td></tr>
                            <?php else: ?>
                                <?php foreach ($riwayat as $r): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r['nomor_transaksi']); ?></td>
                                        <td><?php echo htmlspecialchars($r['tanggal']); ?></td>
                                        <td><?php echo htmlspecialchars($r['kode_material'] . ' - ' . $r['nama_material']); ?></td>
                                        <td><?php echo htmlspecialchars($r['nama_supplier']); ?></td>
                                        <td>+<?php echo $r['jumlah']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

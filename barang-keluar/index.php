<?php
require_once '../config/session.php';
require_once '../config/database.php';

$halaman_aktif = 'barang-keluar';
$judul_halaman = 'Barang Keluar';

$error = '';

// ---------- PROSES SIMPAN TRANSAKSI BARU ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_transaksi = trim($_POST['nomor_transaksi']);
    $tanggal          = trim($_POST['tanggal']);
    $material_id      = (int)$_POST['material_id'];
    $jumlah           = (int)$_POST['jumlah'];
    $unit_peminta     = trim($_POST['unit_peminta']);
    $nama_peminta     = trim($_POST['nama_peminta']);
    $keterangan       = trim($_POST['keterangan']);

    if ($nomor_transaksi === '' || $tanggal === '' || $material_id <= 0 || $jumlah <= 0 || $unit_peminta === '' || $nama_peminta === '') {
        $error = "Semua field wajib diisi dengan benar dan jumlah harus lebih dari 0.";
    } else {
        // Cek nomor transaksi unik
        $cek = $koneksi->prepare("SELECT id FROM stock_out WHERE nomor_transaksi = :no");
        $cek->execute([':no' => $nomor_transaksi]);

        if ($cek->rowCount() > 0) {
            $error = "Nomor transaksi sudah pernah digunakan.";
        } else {
            // Cek stok material saat ini
            $stmtStok = $koneksi->prepare("SELECT stok FROM materials WHERE id = :id");
            $stmtStok->execute([':id' => $material_id]);
            $stokSekarang = $stmtStok->fetchColumn();

            if ($stokSekarang === false) {
                $error = "Material tidak ditemukan.";
            } elseif ($jumlah > $stokSekarang) {
                // ATURAN: jumlah barang keluar tidak boleh melebihi stok
                $error = "Stok tidak mencukupi.";
            } else {
                try {
                    $koneksi->beginTransaction();

                    // 1. Simpan transaksi barang keluar
                    $stmt = $koneksi->prepare("INSERT INTO stock_out
                        (nomor_transaksi, tanggal, material_id, jumlah, unit_peminta, nama_peminta, keterangan)
                        VALUES (:nomor_transaksi, :tanggal, :material_id, :jumlah, :unit_peminta, :nama_peminta, :keterangan)");
                    $stmt->execute([
                        ':nomor_transaksi' => $nomor_transaksi,
                        ':tanggal'         => $tanggal,
                        ':material_id'     => $material_id,
                        ':jumlah'          => $jumlah,
                        ':unit_peminta'    => $unit_peminta,
                        ':nama_peminta'    => $nama_peminta,
                        ':keterangan'      => $keterangan
                    ]);

                    // 2. Kurangi stok material (stok berkurang)
                    $stmtUpdate = $koneksi->prepare("UPDATE materials SET stok = stok - :jumlah WHERE id = :id");
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
}

// ---------- DATA UNTUK DROPDOWN ----------
$daftarMaterial = $koneksi->query("SELECT id, kode_material, nama_material, stok FROM materials ORDER BY nama_material")->fetchAll(PDO::FETCH_ASSOC);

// ---------- RIWAYAT TRANSAKSI BARANG KELUAR ----------
$riwayat = $koneksi->query("
    SELECT so.nomor_transaksi, so.tanggal, so.jumlah, so.unit_peminta, so.nama_peminta,
           m.kode_material, m.nama_material
    FROM stock_out so
    JOIN materials m ON m.id = so.material_id
    ORDER BY so.id DESC
    LIMIT 50
")->fetchAll(PDO::FETCH_ASSOC);

$notifikasi = $_GET['notif'] ?? '';

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="main-content">
    <h4 class="page-title">Barang Keluar</h4>

    <?php if ($notifikasi === 'tambah'): ?>
        <div class="alert alert-success py-2 small">Transaksi barang keluar berhasil disimpan. Stok material sudah diperbarui.</div>
    <?php endif; ?>

    <div class="row g-3">
        <!-- FORM INPUT -->
        <div class="col-lg-5">
            <div class="panel">
                <div class="panel-title">Input Transaksi Barang Keluar</div>

                <?php if ($error): ?>
                    <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small">Nomor Transaksi</label>
                        <input type="text" name="nomor_transaksi" class="form-control" placeholder="Contoh: OUT-0003"
                               value="<?php echo htmlspecialchars($_POST['nomor_transaksi'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['tanggal'] ?? date('Y-m-d')); ?>" required>
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
                        <label class="form-label small">Unit Peminta</label>
                        <input type="text" name="unit_peminta" class="form-control" placeholder="Contoh: Unit Distribusi"
                               value="<?php echo htmlspecialchars($_POST['unit_peminta'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Nama Peminta</label>
                        <input type="text" name="nama_peminta" class="form-control"
                               value="<?php echo htmlspecialchars($_POST['nama_peminta'] ?? ''); ?>" required>
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
                <div class="panel-title">Riwayat Barang Keluar</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>No. Transaksi</th>
                                <th>Tanggal</th>
                                <th>Material</th>
                                <th>Unit Peminta</th>
                                <th>Peminta</th>
                                <th>Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($riwayat) === 0): ?>
                                <tr><td colspan="6" class="text-center text-muted py-3">Belum ada transaksi barang keluar.</td></tr>
                            <?php else: ?>
                                <?php foreach ($riwayat as $r): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($r['nomor_transaksi']); ?></td>
                                        <td><?php echo htmlspecialchars($r['tanggal']); ?></td>
                                        <td><?php echo htmlspecialchars($r['kode_material'] . ' - ' . $r['nama_material']); ?></td>
                                        <td><?php echo htmlspecialchars($r['unit_peminta']); ?></td>
                                        <td><?php echo htmlspecialchars($r['nama_peminta']); ?></td>
                                        <td>-<?php echo $r['jumlah']; ?></td>
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

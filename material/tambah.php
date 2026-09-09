<?php
require_once '../config/session.php';
require_once '../config/database.php';

$halaman_aktif = 'material';
$judul_halaman = 'Tambah Material';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_material  = trim($_POST['kode_material']);
    $nama_material  = trim($_POST['nama_material']);
    $kategori       = trim($_POST['kategori']);
    $satuan         = trim($_POST['satuan']);
    $stok           = (int)$_POST['stok'];
    $minimum_stok   = (int)$_POST['minimum_stok'];
    $lokasi         = trim($_POST['lokasi']);
    $keterangan     = trim($_POST['keterangan']);

    if ($kode_material === '' || $nama_material === '' || $kategori === '' || $satuan === '' || $lokasi === '') {
        $error = "Semua field wajib diisi (kecuali keterangan).";
    } else {
        // Cek kode material harus unik
        $cek = $koneksi->prepare("SELECT id FROM materials WHERE kode_material = :kode");
        $cek->execute([':kode' => $kode_material]);

        if ($cek->rowCount() > 0) {
            $error = "Kode material sudah digunakan. Gunakan kode lain.";
        } else {
            $stmt = $koneksi->prepare("INSERT INTO materials
                (kode_material, nama_material, kategori, satuan, stok, minimum_stok, lokasi, keterangan)
                VALUES (:kode_material, :nama_material, :kategori, :satuan, :stok, :minimum_stok, :lokasi, :keterangan)");
            $stmt->execute([
                ':kode_material' => $kode_material,
                ':nama_material' => $nama_material,
                ':kategori'      => $kategori,
                ':satuan'        => $satuan,
                ':stok'          => $stok,
                ':minimum_stok'  => $minimum_stok,
                ':lokasi'        => $lokasi,
                ':keterangan'    => $keterangan
            ]);

            header("Location: index.php?notif=tambah");
            exit;
        }
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="main-content">
    <h4 class="page-title">Tambah Material</h4>

    <div class="panel" style="max-width: 700px;">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small">Kode Material</label>
                    <input type="text" name="kode_material" class="form-control" placeholder="Contoh: MT-006"
                           value="<?php echo htmlspecialchars($_POST['kode_material'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Nama Material</label>
                    <input type="text" name="nama_material" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['nama_material'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Kategori</label>
                    <input type="text" name="kategori" class="form-control" placeholder="Contoh: Kabel, Lampu, Komponen Listrik"
                           value="<?php echo htmlspecialchars($_POST['kategori'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Satuan</label>
                    <input type="text" name="satuan" class="form-control" placeholder="Contoh: Unit, Meter, Roll"
                           value="<?php echo htmlspecialchars($_POST['satuan'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Stok Awal</label>
                    <input type="number" name="stok" class="form-control" min="0"
                           value="<?php echo htmlspecialchars($_POST['stok'] ?? '0'); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Minimum Stok</label>
                    <input type="number" name="minimum_stok" class="form-control" min="0"
                           value="<?php echo htmlspecialchars($_POST['minimum_stok'] ?? '0'); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Lokasi</label>
                    <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Gudang A"
                           value="<?php echo htmlspecialchars($_POST['lokasi'] ?? ''); ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label small">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['keterangan'] ?? ''); ?></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan</button>
                <a href="index.php" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

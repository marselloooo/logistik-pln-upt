<?php
require_once '../config/session.php';
require_once '../config/database.php';

$halaman_aktif = 'material';
$judul_halaman = 'Edit Material';

$id = (int)($_GET['id'] ?? 0);

// Ambil data material yang akan diedit
$stmt = $koneksi->prepare("SELECT * FROM materials WHERE id = :id");
$stmt->execute([':id' => $id]);
$material = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$material) {
    header("Location: index.php");
    exit;
}

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
        // Cek kode material unik (kecuali milik data ini sendiri)
        $cek = $koneksi->prepare("SELECT id FROM materials WHERE kode_material = :kode AND id != :id");
        $cek->execute([':kode' => $kode_material, ':id' => $id]);

        if ($cek->rowCount() > 0) {
            $error = "Kode material sudah digunakan oleh material lain.";
        } else {
            $stmt = $koneksi->prepare("UPDATE materials SET
                kode_material = :kode_material,
                nama_material = :nama_material,
                kategori = :kategori,
                satuan = :satuan,
                stok = :stok,
                minimum_stok = :minimum_stok,
                lokasi = :lokasi,
                keterangan = :keterangan
                WHERE id = :id");
            $stmt->execute([
                ':kode_material' => $kode_material,
                ':nama_material' => $nama_material,
                ':kategori'      => $kategori,
                ':satuan'        => $satuan,
                ':stok'          => $stok,
                ':minimum_stok'  => $minimum_stok,
                ':lokasi'        => $lokasi,
                ':keterangan'    => $keterangan,
                ':id'            => $id
            ]);

            header("Location: index.php?notif=edit");
            exit;
        }
        // Perbarui variabel $material agar form tetap menampilkan input terbaru jika error
        $material = array_merge($material, $_POST);
    }
}

require_once '../includes/header.php';
require_once '../includes/sidebar.php';
?>

<main class="main-content">
    <h4 class="page-title">Edit Material</h4>

    <div class="panel" style="max-width: 700px;">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small">Kode Material</label>
                    <input type="text" name="kode_material" class="form-control"
                           value="<?php echo htmlspecialchars($material['kode_material']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Nama Material</label>
                    <input type="text" name="nama_material" class="form-control"
                           value="<?php echo htmlspecialchars($material['nama_material']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Kategori</label>
                    <input type="text" name="kategori" class="form-control"
                           value="<?php echo htmlspecialchars($material['kategori']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Satuan</label>
                    <input type="text" name="satuan" class="form-control"
                           value="<?php echo htmlspecialchars($material['satuan']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Stok</label>
                    <input type="number" name="stok" class="form-control" min="0"
                           value="<?php echo htmlspecialchars($material['stok']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Minimum Stok</label>
                    <input type="number" name="minimum_stok" class="form-control" min="0"
                           value="<?php echo htmlspecialchars($material['minimum_stok']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Lokasi</label>
                    <input type="text" name="lokasi" class="form-control"
                           value="<?php echo htmlspecialchars($material['lokasi']); ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label small">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="2"><?php echo htmlspecialchars($material['keterangan']); ?></textarea>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Simpan Perubahan</button>
                <a href="index.php" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</main>

<?php require_once '../includes/footer.php'; ?>

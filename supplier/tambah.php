<?php
require_once '../config/session.php';
require_once '../config/database.php';

$halaman_aktif = 'supplier';
$judul_halaman = 'Tambah Supplier';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode_supplier = trim($_POST['kode_supplier']);
    $nama_supplier = trim($_POST['nama_supplier']);
    $alamat        = trim($_POST['alamat']);
    $telepon       = trim($_POST['telepon']);
    $email         = trim($_POST['email']);
    $pic           = trim($_POST['pic']);

    if ($kode_supplier === '' || $nama_supplier === '') {
        $error = "Kode dan nama supplier wajib diisi.";
    } else {
        $cek = $koneksi->prepare("SELECT id FROM suppliers WHERE kode_supplier = :kode");
        $cek->execute([':kode' => $kode_supplier]);

        if ($cek->rowCount() > 0) {
            $error = "Kode supplier sudah digunakan.";
        } else {
            $stmt = $koneksi->prepare("INSERT INTO suppliers
                (kode_supplier, nama_supplier, alamat, telepon, email, pic)
                VALUES (:kode_supplier, :nama_supplier, :alamat, :telepon, :email, :pic)");
            $stmt->execute([
                ':kode_supplier' => $kode_supplier,
                ':nama_supplier' => $nama_supplier,
                ':alamat'        => $alamat,
                ':telepon'       => $telepon,
                ':email'         => $email,
                ':pic'           => $pic
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
    <h4 class="page-title">Tambah Supplier</h4>

    <div class="panel" style="max-width: 700px;">
        <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small">Kode Supplier</label>
                    <input type="text" name="kode_supplier" class="form-control" placeholder="Contoh: SUP-004"
                           value="<?php echo htmlspecialchars($_POST['kode_supplier'] ?? ''); ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Nama Supplier</label>
                    <input type="text" name="nama_supplier" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['nama_supplier'] ?? ''); ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label small">Alamat</label>
                    <textarea name="alamat" class="form-control" rows="2"><?php echo htmlspecialchars($_POST['alamat'] ?? ''); ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Telepon</label>
                    <input type="text" name="telepon" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['telepon'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Email</label>
                    <input type="email" name="email" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">PIC (Penanggung Jawab)</label>
                    <input type="text" name="pic" class="form-control"
                           value="<?php echo htmlspecialchars($_POST['pic'] ?? ''); ?>">
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

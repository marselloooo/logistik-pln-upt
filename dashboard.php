<?php
require_once 'config/session.php';   // proteksi login + BASE_URL
require_once 'config/database.php';

$halaman_aktif = 'dashboard';
$judul_halaman = 'Dashboard';

// ---------- AMBIL DATA UNTUK CARD STATISTIK ----------

// Total material (jumlah jenis material)
$totalMaterial = $koneksi->query("SELECT COUNT(*) FROM materials")->fetchColumn();

// Total stok (jumlah seluruh stok material)
$totalStok = $koneksi->query("SELECT COALESCE(SUM(stok),0) FROM materials")->fetchColumn();

// Total barang masuk (jumlah unit yang pernah masuk)
$totalMasuk = $koneksi->query("SELECT COALESCE(SUM(jumlah),0) FROM stock_in")->fetchColumn();

// Total barang keluar (jumlah unit yang pernah keluar)
$totalKeluar = $koneksi->query("SELECT COALESCE(SUM(jumlah),0) FROM stock_out")->fetchColumn();

// Jumlah material dengan stok menipis atau habis (stok <= minimum_stok)
$stokMenipis = $koneksi->query("SELECT COUNT(*) FROM materials WHERE stok <= minimum_stok")->fetchColumn();

// ---------- DATA GRAFIK: Barang Masuk vs Barang Keluar (6 bulan terakhir) ----------
$queryGrafik = "
    SELECT bulan, SUM(masuk) AS masuk, SUM(keluar) AS keluar FROM (
        SELECT DATE_FORMAT(tanggal, '%Y-%m') AS bulan, jumlah AS masuk, 0 AS keluar FROM stock_in
        UNION ALL
        SELECT DATE_FORMAT(tanggal, '%Y-%m') AS bulan, 0 AS masuk, jumlah AS keluar FROM stock_out
    ) AS gabungan
    GROUP BY bulan
    ORDER BY bulan ASC
    LIMIT 6
";
$grafikData = $koneksi->query($queryGrafik)->fetchAll(PDO::FETCH_ASSOC);

$labelBulan = [];
$dataMasuk = [];
$dataKeluar = [];
foreach ($grafikData as $baris) {
    $labelBulan[] = $baris['bulan'];
    $dataMasuk[] = (int)$baris['masuk'];
    $dataKeluar[] = (int)$baris['keluar'];
}

// ---------- DATA TABEL: Material dengan Stok Menipis ----------
$tabelMenipis = $koneksi->query("
    SELECT kode_material, nama_material, stok, minimum_stok
    FROM materials
    WHERE stok <= minimum_stok
    ORDER BY stok ASC
")->fetchAll(PDO::FETCH_ASSOC);

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<main class="main-content">
    <h4 class="page-title">Dashboard</h4>
    <p class="text-muted mb-4" style="margin-top:-10px;">Sistem Informasi Monitoring Persediaan &mdash; Divisi Logistik PLN UPT</p>

    <!-- CARD STATISTIK -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-4 col-lg">
            <div class="card-stat d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-soft"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="stat-value"><?php echo number_format($totalMaterial, 0, ',', '.'); ?></div>
                    <div class="stat-label">Total Material</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card-stat d-flex align-items-center gap-3">
                <div class="stat-icon"><i class="bi bi-stack"></i></div>
                <div>
                    <div class="stat-value"><?php echo number_format($totalStok, 0, ',', '.'); ?></div>
                    <div class="stat-label">Total Stok</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card-stat d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-soft"><i class="bi bi-box-arrow-in-down"></i></div>
                <div>
                    <div class="stat-value"><?php echo number_format($totalMasuk, 0, ',', '.'); ?></div>
                    <div class="stat-label">Barang Masuk</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card-stat d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-soft"><i class="bi bi-box-arrow-up"></i></div>
                <div>
                    <div class="stat-value"><?php echo number_format($totalKeluar, 0, ',', '.'); ?></div>
                    <div class="stat-label">Barang Keluar</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-lg">
            <div class="card-stat d-flex align-items-center gap-3">
                <div class="stat-icon bg-danger-soft"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <div class="stat-value"><?php echo number_format($stokMenipis, 0, ',', '.'); ?></div>
                    <div class="stat-label">Stok Menipis</div>
                </div>
            </div>
        </div>
    </div>

    <!-- GRAFIK -->
    <div class="panel">
        <div class="panel-title">Grafik Barang Masuk vs Barang Keluar</div>
        <?php if (count($labelBulan) > 0): ?>
            <canvas id="grafikStok" height="90"></canvas>
        <?php else: ?>
            <p class="text-muted small mb-0">Belum ada data transaksi untuk ditampilkan pada grafik.</p>
        <?php endif; ?>
    </div>

    <!-- TABEL STOK MENIPIS -->
    <div class="panel">
        <div class="panel-title">Material dengan Stok Menipis</div>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Material</th>
                        <th>Stok</th>
                        <th>Minimum</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($tabelMenipis) === 0): ?>
                        <tr><td colspan="5" class="text-center text-muted py-3">Semua stok material dalam kondisi aman.</td></tr>
                    <?php else: ?>
                        <?php foreach ($tabelMenipis as $m): ?>
                            <?php
                                if ($m['stok'] == 0) {
                                    $badge = '<span class="badge badge-habis">HABIS</span>';
                                } else {
                                    $badge = '<span class="badge badge-menipis">MENIPIS</span>';
                                }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($m['kode_material']); ?></td>
                                <td><?php echo htmlspecialchars($m['nama_material']); ?></td>
                                <td><?php echo $m['stok']; ?></td>
                                <td><?php echo $m['minimum_stok']; ?></td>
                                <td><?php echo $badge; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>

<?php if (count($labelBulan) > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('grafikStok');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($labelBulan); ?>,
            datasets: [
                {
                    label: 'Barang Masuk',
                    data: <?php echo json_encode($dataMasuk); ?>,
                    backgroundColor: '#1e5cb3'
                },
                {
                    label: 'Barang Keluar',
                    data: <?php echo json_encode($dataKeluar); ?>,
                    backgroundColor: '#e0a020'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true } }
        }
    });
</script>
<?php endif; ?>

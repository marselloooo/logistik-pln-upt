// =========================================================
// JS CUSTOM - Sistem Informasi Monitoring Persediaan Material
// =========================================================

// Toggle sidebar untuk tampilan HP
document.addEventListener('DOMContentLoaded', function () {
    var btnToggle = document.getElementById('btnToggleSidebar');
    var sidebar = document.getElementById('sidebar');

    if (btnToggle && sidebar) {
        btnToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });

        // Klik di luar sidebar akan menutup sidebar (khusus HP)
        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 767) {
                if (!sidebar.contains(e.target) && !btnToggle.contains(e.target)) {
                    sidebar.classList.remove('show');
                }
            }
        });
    }
});

// Konfirmasi sebelum menghapus data
function konfirmasiHapus(url, namaData) {
    if (confirm('Apakah Anda yakin ingin menghapus "' + namaData + '"?\nData yang dihapus tidak dapat dikembalikan.')) {
        window.location.href = url;
    }
}

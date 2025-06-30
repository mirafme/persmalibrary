<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/functions.php';
require_once '../../includes/header.php';

// Cek apakah user login sebagai Ketua
if (!isKetua($_SESSION['jabatan_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Database connection
$database = new Database();
$db = $database->getConnection();

// Hitung total keterlambatan
$queryTerlambat = "SELECT COUNT(*) as total FROM peminjaman WHERE estimasi_kembali < CURDATE() AND status = 'aktif'";
$stmtTerlambat = $db->prepare($queryTerlambat);
$stmtTerlambat->execute();
$jumlah_terlambat = $stmtTerlambat->fetch(PDO::FETCH_ASSOC)['total'];

// Data keterlambatan per divisi
$queryChart = "
    SELECT u.divisi, COUNT(*) as jumlah
    FROM peminjaman p
    JOIN user u ON p.id_user = u.id_user
    WHERE p.estimasi_kembali < CURDATE() AND p.status = 'aktif'
    GROUP BY u.divisi
";
$stmtChart = $db->prepare($queryChart);
$stmtChart->execute();
$divisi_data = $stmtChart->fetchAll(PDO::FETCH_ASSOC);

$labels = [];
$values = [];

foreach ($divisi_data as $row) {
    $labels[] = $row['divisi'];
    $values[] = $row['jumlah'];
}
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../includes/sidebar.php'; ?>

    <main class="flex-1 overflow-auto p-6">
        <h1 class="text-2xl font-bold mb-4">Laporan Keterlambatan - Ketua</h1>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold mb-2">Total Buku Terlambat Dikembalikan</h2>
            <p class="text-3xl text-red-600 font-bold">
                <?= $jumlah_terlambat; ?> buku
            </p>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Distribusi Keterlambatan per Divisi</h2>
            <canvas id="chartTerlambat" height="100"></canvas>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('chartTerlambat').getContext('2d');
const chartTerlambat = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels); ?>,
        datasets: [{
            label: 'Jumlah Terlambat',
            data: <?= json_encode($values); ?>,
            backgroundColor: 'rgba(255, 99, 132, 0.7)'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: (ctx) => ctx.raw + ' buku' } }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: { stepSize: 1 }
            }
        }
    }
});
</script>

<?php include '../../includes/footer.php'; ?>

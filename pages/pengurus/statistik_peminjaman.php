<?php
session_start();

if (!isset($_SESSION['jabatan_id']) || $_SESSION['jabatan_id'] != 5) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../../config/database.php';
$database = new Database();
$db = $database->getConnection();

// Statistik jumlah peminjaman total
$queryTotal = "SELECT COUNT(*) as total FROM peminjaman";
$stmtTotal = $db->prepare($queryTotal);
$stmtTotal->execute();
$total = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

// Statistik peminjaman aktif
$queryAktif = "SELECT COUNT(*) as aktif FROM peminjaman WHERE status = 'aktif'";
$stmtAktif = $db->prepare($queryAktif);
$stmtAktif->execute();
$aktif = $stmtAktif->fetch(PDO::FETCH_ASSOC)['aktif'];

// Statistik peminjaman selesai
$querySelesai = "SELECT COUNT(*) as selesai FROM peminjaman WHERE status = 'selesai'";
$stmtSelesai = $db->prepare($querySelesai);
$stmtSelesai->execute();
$selesai = $stmtSelesai->fetch(PDO::FETCH_ASSOC)['selesai'];

// Statistik berdasarkan bulan
$queryBulanan = "
    SELECT DATE_FORMAT(tanggal_pinjam, '%Y-%m') as bulan, COUNT(*) as jumlah
    FROM peminjaman
    GROUP BY bulan
    ORDER BY bulan ASC
";
$stmtBulanan = $db->prepare($queryBulanan);
$stmtBulanan->execute();
$dataBulanan = $stmtBulanan->fetchAll(PDO::FETCH_ASSOC);
$bulanLabels = array_column($dataBulanan, 'bulan');
$jumlahPinjam = array_column($dataBulanan, 'jumlah');
?>

<?php include '../../includes/header.php'; ?>
<div class="flex h-screen bg-gray-50">
    <?php include '../../includes/sidebar.php'; ?>

    <main class="flex-1 overflow-auto p-6">
        <h1 class="text-2xl font-bold mb-6">Statistik Peminjaman</h1>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white shadow rounded p-4">
                <p class="text-sm text-gray-600">Total Peminjaman</p>
                <p class="text-3xl font-bold text-blue-600"><?= $total ?></p>
            </div>
            <div class="bg-white shadow rounded p-4">
                <p class="text-sm text-gray-600">Aktif</p>
                <p class="text-3xl font-bold text-yellow-600"><?= $aktif ?></p>
            </div>
            <div class="bg-white shadow rounded p-4">
                <p class="text-sm text-gray-600">Selesai</p>
                <p class="text-3xl font-bold text-green-600"><?= $selesai ?></p>
            </div>
        </div>

        <div class="bg-white shadow rounded p-6">
            <h2 class="text-lg font-semibold mb-4">Grafik Peminjaman per Bulan</h2>
            <canvas id="peminjamanChart" height="100"></canvas>
        </div>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('peminjamanChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($bulanLabels) ?>,
            datasets: [{
                label: 'Jumlah Peminjaman',
                data: <?= json_encode($jumlahPinjam) ?>,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ctx.raw + ' buku'
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
<?php include '../../includes/footer.php'; ?>

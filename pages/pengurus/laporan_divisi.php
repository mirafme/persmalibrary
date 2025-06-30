<?php
$page_title = "Laporan Divisi - Perpustakaan Persma";
include '../../includes/header.php';
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

// Jumlah peminjaman per divisi
$queryDivisi = "SELECT u.divisi, COUNT(*) as total 
                FROM peminjaman p 
                JOIN user u ON p.id_user = u.id_user 
                WHERE u.jabatan_id = 7
                GROUP BY u.divisi";
$stmtDivisi = $db->prepare($queryDivisi);
$stmtDivisi->execute();
$dataDivisi = $stmtDivisi->fetchAll(PDO::FETCH_ASSOC);

// Peminjam terbanyak per divisi
$queryPeminjam = "SELECT u.nama, u.divisi, COUNT(*) as total 
                  FROM peminjaman p 
                  JOIN user u ON p.id_user = u.id_user 
                  WHERE u.jabatan_id = 7
                  GROUP BY u.nama, u.divisi
                  ORDER BY total DESC
                  LIMIT 10";
$stmtPeminjam = $db->prepare($queryPeminjam);
$stmtPeminjam->execute();
$dataPeminjam = $stmtPeminjam->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../includes/sidebar.php'; ?>

    <main class="flex-1 overflow-auto">
        <div class="p-6 space-y-6">
            <h1 class="text-3xl font-bold text-gray-900">Laporan Peminjaman Divisi</h1>
            <p class="text-gray-600">Visualisasi aktivitas membaca berdasarkan divisi dan anggota</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-10">
                <!-- Grafik Peminjaman per Divisi -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-bold mb-4">Statistik Peminjaman per Divisi</h2>
                    <canvas id="chartDivisi"></canvas>
                </div>

                <!-- Top 10 Peminjam -->
                <div class="bg-white p-6 rounded-lg shadow">
                    <h2 class="text-lg font-bold mb-4">Top 10 Peminjam Terbanyak</h2>
                    <ul class="list-disc pl-5 space-y-1">
                        <?php foreach ($dataPeminjam as $p): ?>
                            <li>
                                <strong><?= htmlspecialchars($p['nama']) ?></strong> 
                                (<?= htmlspecialchars($p['divisi']) ?>) - 
                                <?= $p['total'] ?> buku
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('chartDivisi').getContext('2d');
    const chartDivisi = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($dataDivisi, 'divisi')) ?>,
            datasets: [{
                label: 'Jumlah Peminjaman',
                data: <?= json_encode(array_column($dataDivisi, 'total')) ?>,
                backgroundColor: 'rgba(34, 197, 94, 0.6)', // warna hijau
                borderColor: 'rgba(34, 197, 94, 1)',
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>

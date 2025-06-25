
<?php
$page_title = "Riwayat Peminjaman - Perpustakaan Persma";
include '../../includes/header.php';
require_once '../../config/database.php';

// Cek apakah user sudah login dan bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['jabatan'] === 'Administrator') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$user_id = $_SESSION['user_id'];

// Get user's loan history
$query = "SELECT p.*, b.judul, b.pengarang, b.kategori, pen.tanggal_kembali,
          CASE 
              WHEN p.estimasi_kembali < CURDATE() AND p.status = 'aktif' THEN 'terlambat'
              ELSE p.status
          END as status_real,
          CASE 
              WHEN pen.tanggal_kembali IS NOT NULL THEN DATEDIFF(pen.tanggal_kembali, p.estimasi_kembali)
              WHEN p.estimasi_kembali < CURDATE() AND p.status = 'aktif' THEN DATEDIFF(CURDATE(), p.estimasi_kembali)
              ELSE 0
          END as days_late
          FROM peminjaman p
          JOIN buku b ON p.id_buku = b.id_buku
          LEFT JOIN pengembalian pen ON p.id_peminjaman = pen.id_peminjaman
          WHERE p.id_user = :user_id
          ORDER BY p.tanggal_pinjam DESC";
          
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistics
$total_loans = count($loans);
$active_loans = count(array_filter($loans, function($loan) { return $loan['status'] === 'aktif'; }));
$returned_loans = count(array_filter($loans, function($loan) { return $loan['status'] === 'dikembalikan'; }));
$overdue_loans = count(array_filter($loans, function($loan) { return $loan['status_real'] === 'terlambat'; }));
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../includes/sidebar.php'; ?>
    
    <main class="flex-1 overflow-auto">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Riwayat Peminjaman</h1>
                    <p class="text-gray-600 mt-1">Lihat histori peminjaman buku Anda</p>
                </div>
            </div>

            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Peminjaman</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo $total_loans; ?></p>
                        </div>
                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-book text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Sedang Dipinjam</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo $active_loans; ?></p>
                        </div>
                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Dikembalikan</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo $returned_loans; ?></p>
                        </div>
                        <div class="w-10 h-10 bg-gray-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Terlambat</p>
                            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo $overdue_loans; ?></p>
                        </div>
                        <div class="w-10 h-10 bg-red-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loan History -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Lengkap</h3>
                    
                    <?php if (count($loans) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buku</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Pinjam</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estimasi Kembali</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Kembali</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($loans as $loan): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="font-medium text-gray-900"><?php echo htmlspecialchars($loan['judul']); ?></div>
                                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($loan['pengarang']); ?></div>
                                                <div class="text-xs text-gray-400"><?php echo htmlspecialchars($loan['kategori']); ?></div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('d/m/Y', strtotime($loan['tanggal_pinjam'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo date('d/m/Y', strtotime($loan['estimasi_kembali'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php echo $loan['tanggal_kembali'] ? date('d/m/Y', strtotime($loan['tanggal_kembali'])) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                <?php 
                                                switch($loan['status_real']) {
                                                    case 'aktif': echo 'bg-green-100 text-green-800'; break;
                                                    case 'dikembalikan': echo 'bg-gray-100 text-gray-800'; break;
                                                    case 'terlambat': echo 'bg-red-100 text-red-800'; break;
                                                    default: echo 'bg-gray-100 text-gray-800';
                                                }
                                                ?>">
                                                <?php 
                                                switch($loan['status_real']) {
                                                    case 'aktif': echo 'Sedang Dipinjam'; break;
                                                    case 'dikembalikan': echo 'Dikembalikan'; break;
                                                    case 'terlambat': echo 'Terlambat'; break;
                                                    default: echo ucfirst($loan['status_real']);
                                                }
                                                ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <?php 
                                            if ($loan['days_late'] > 0) {
                                                echo "Terlambat " . $loan['days_late'] . " hari";
                                            } elseif ($loan['status'] === 'dikembalikan' && $loan['days_late'] < 0) {
                                                echo "Dikembalikan " . abs($loan['days_late']) . " hari lebih awal";
                                            } elseif ($loan['status'] === 'dikembalikan') {
                                                echo "Tepat waktu";
                                            } else {
                                                $days_left = ceil((strtotime($loan['estimasi_kembali']) - time()) / (60 * 60 * 24));
                                                if ($days_left > 0) {
                                                    echo "Sisa " . $days_left . " hari";
                                                } else {
                                                    echo "Sudah jatuh tempo";
                                                }
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-history text-gray-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada riwayat peminjaman</h3>
                            <p class="text-gray-500 mb-4">Mulai jelajahi koleksi buku kami dan pinjam buku pertama Anda!</p>
                            <a href="katalog.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                                Lihat Katalog Buku
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../../includes/footer.php'; ?>

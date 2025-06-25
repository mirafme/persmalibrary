<?php
$page_title = "Laporan Peminjaman - Perpustakaan Persma";
include '../../includes/header.php';
require_once '../../config/database.php';

// Cek apakah user sudah login dan merupakan admin
if (!isset($_SESSION['user_id']) || $_SESSION['jabatan'] !== 'Administrator') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Get filter parameters
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$query = "SELECT p.*, u.nama as nama_peminjam, u.divisi, u.angkatan, b.judul, b.pengarang, b.kategori,
                 CASE 
                     WHEN pg.id_pengembalian IS NOT NULL THEN 'dikembalikan'
                     WHEN p.estimasi_kembali < CURDATE() THEN 'terlambat'
                     ELSE 'aktif'
                 END as status_pinjam,
                 pg.tanggal_kembali
          FROM peminjaman p
          LEFT JOIN user u ON p.id_user = u.id_user
          LEFT JOIN buku b ON p.id_buku = b.id_buku
          LEFT JOIN pengembalian pg ON p.id_peminjaman = pg.id_peminjaman
          WHERE 1=1";

$params = [];

if (!empty($filter_status)) {
    if ($filter_status == 'aktif') {
        $query .= " AND pg.id_pengembalian IS NULL AND p.estimasi_kembali >= CURDATE()";
    } elseif ($filter_status == 'terlambat') {
        $query .= " AND pg.id_pengembalian IS NULL AND p.estimasi_kembali < CURDATE()";
    } elseif ($filter_status == 'dikembalikan') {
        $query .= " AND pg.id_pengembalian IS NOT NULL";
    }
}

if (!empty($filter_bulan)) {
    $query .= " AND MONTH(p.tanggal_pinjam) = :bulan";
    $params[':bulan'] = $filter_bulan;
}

if (!empty($filter_tahun)) {
    $query .= " AND YEAR(p.tanggal_pinjam) = :tahun";
    $params[':tahun'] = $filter_tahun;
}

if (!empty($search)) {
    $query .= " AND (u.nama LIKE :search OR b.judul LIKE :search OR u.divisi LIKE :search)";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY p.tanggal_pinjam DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$peminjaman = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get statistics
$stats_query = "SELECT 
                    COUNT(*) as total_peminjaman,
                    SUM(CASE WHEN pg.id_pengembalian IS NULL AND p.estimasi_kembali >= CURDATE() THEN 1 ELSE 0 END) as aktif,
                    SUM(CASE WHEN pg.id_pengembalian IS NULL AND p.estimasi_kembali < CURDATE() THEN 1 ELSE 0 END) as terlambat,
                    SUM(CASE WHEN pg.id_pengembalian IS NOT NULL THEN 1 ELSE 0 END) as dikembalikan
                FROM peminjaman p
                LEFT JOIN pengembalian pg ON p.id_peminjaman = pg.id_peminjaman";

if (!empty($filter_tahun)) {
    $stats_query .= " WHERE YEAR(p.tanggal_pinjam) = :tahun";
}

$stmt_stats = $db->prepare($stats_query);
if (!empty($filter_tahun)) {
    $stmt_stats->bindParam(':tahun', $filter_tahun);
}
$stmt_stats->execute();
$stats = $stmt_stats->fetch(PDO::FETCH_ASSOC);
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../includes/sidebar.php'; ?>
    
    <main class="flex-1 overflow-auto">
        <div class="p-6 space-y-6">
<!-- Header -->
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Laporan Peminjaman</h1>
        <p class="text-gray-600 mt-1">Laporan dan statistik peminjaman buku</p>
    </div>
    <a href="export_laporan_excel.php?status=<?php echo $filter_status; ?>&bulan=<?php echo $filter_bulan; ?>&tahun=<?php echo $filter_tahun; ?>&search=<?php echo urlencode($search); ?>" 
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
        <i class="fas fa-file-excel mr-2"></i>Export Excel
    </a>
</div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-blue-100 rounded-lg">
                            <i class="fas fa-book text-blue-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Total Peminjaman</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $stats['total_peminjaman']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-green-100 rounded-lg">
                            <i class="fas fa-clock text-green-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Aktif</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $stats['aktif']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-red-100 rounded-lg">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Terlambat</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $stats['terlambat']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <i class="fas fa-check-circle text-gray-600 text-xl"></i>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Dikembalikan</p>
                            <p class="text-2xl font-bold text-gray-900"><?php echo $stats['dikembalikan']; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow p-6 no-print">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Status</option>
                            <option value="aktif" <?php echo ($filter_status == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                            <option value="terlambat" <?php echo ($filter_status == 'terlambat') ? 'selected' : ''; ?>>Terlambat</option>
                            <option value="dikembalikan" <?php echo ($filter_status == 'dikembalikan') ? 'selected' : ''; ?>>Dikembalikan</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                        <select name="bulan" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Bulan</option>
                            <?php 
                            $bulan_names = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                            for ($i = 1; $i <= 12; $i++): 
                            ?>
                                <option value="<?php echo $i; ?>" <?php echo ($filter_bulan == $i) ? 'selected' : ''; ?>>
                                    <?php echo $bulan_names[$i]; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                        <select name="tahun" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php for ($year = date('Y'); $year >= 2020; $year--): ?>
                                <option value="<?php echo $year; ?>" <?php echo ($filter_tahun == $year) ? 'selected' : ''; ?>>
                                    <?php echo $year; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Nama/Buku/Divisi..." 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="laporan_peminjaman.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Report Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peminjam</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Buku</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Pinjam</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estimasi Kembali</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Kembali</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($peminjaman)): ?>
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-4"></i>
                                        <p>Tidak ada data peminjaman ditemukan</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($peminjaman as $index => $item): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm text-gray-900"><?php echo $index + 1; ?></td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['nama_peminjam']); ?></p>
                                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($item['divisi']); ?> - <?php echo htmlspecialchars($item['angkatan']); ?></p>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($item['judul']); ?></p>
                                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($item['pengarang']); ?></p>
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">
                                                <?php echo htmlspecialchars($item['kategori']); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo date('d/m/Y', strtotime($item['tanggal_pinjam'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php echo date('d/m/Y', strtotime($item['estimasi_kembali'])); ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <?php if ($item['tanggal_kembali']): ?>
                                            <?php echo date('d/m/Y', strtotime($item['tanggal_kembali'])); ?>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                            <?php 
                                            switch($item['status_pinjam']) {
                                                case 'aktif': echo 'bg-green-100 text-green-800'; break;
                                                case 'terlambat': echo 'bg-red-100 text-red-800'; break;
                                                case 'dikembalikan': echo 'bg-gray-100 text-gray-800'; break;
                                            }
                                            ?>">
                                            <?php echo ucfirst($item['status_pinjam']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .sidebar {
        display: none !important;
    }
    main {
        margin-left: 0 !important;
    }
    .bg-gray-50 {
        background-color: white !important;
    }
}
</style>

<?php include '../../includes/footer.php'; ?>
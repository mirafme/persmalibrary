<?php
$page_title = "Dashboard Admin - Perpustakaan Persma";
include '../../includes/header.php';
require_once '../../config/database.php';

// Cek apakah user sudah login dan adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['jabatan'] !== 'Administrator') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Hitung statistik
$query_total_buku = "SELECT COUNT(*) as total FROM buku WHERE aktif = TRUE";
$stmt_total_buku = $db->prepare($query_total_buku);
$stmt_total_buku->execute();
$total_buku = $stmt_total_buku->fetch(PDO::FETCH_ASSOC)['total'];

$query_tersedia = "SELECT COUNT(*) as total FROM buku WHERE status = 'tersedia' AND aktif = TRUE";

$stmt_tersedia = $db->prepare($query_tersedia);
$stmt_tersedia->execute();
$buku_tersedia = $stmt_tersedia->fetch(PDO::FETCH_ASSOC)['total'];

$query_dipinjam = "SELECT COUNT(*) as total FROM buku WHERE status = 'dipinjam' AND aktif = TRUE";
$stmt_dipinjam = $db->prepare($query_dipinjam);
$stmt_dipinjam->execute();
$buku_dipinjam = $stmt_dipinjam->fetch(PDO::FETCH_ASSOC)['total'];

$query_terlambat = "SELECT COUNT(*) as total FROM peminjaman WHERE estimasi_kembali < CURDATE() AND status = 'aktif'";
$stmt_terlambat = $db->prepare($query_terlambat);
$stmt_terlambat->execute();
$terlambat = $stmt_terlambat->fetch(PDO::FETCH_ASSOC)['total'];
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../includes/sidebar.php'; ?>
    
    <main class="flex-1 overflow-auto">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Dashboard Administrator</h1>
                    <p class="text-gray-600 mt-1">Selamat datang kembali! Kelola sistem perpustakaan Persma dengan mudah.</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Hari ini</p>
                    <p class="text-lg font-semibold text-gray-900">
                        <?php echo date('l, d F Y'); ?>
                    </p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Buku</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $total_buku; ?></p>
                            <p class="text-sm text-gray-500 mt-1">Koleksi buku digital</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-book text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Buku Tersedia</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $buku_tersedia; ?></p>
                            <p class="text-sm text-gray-500 mt-1">Siap dipinjam</p>
                        </div>
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-line text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Sedang Dipinjam</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $buku_dipinjam; ?></p>
                            <p class="text-sm text-gray-500 mt-1">Aktif peminjaman</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Terlambat</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $terlambat; ?></p>
                            <p class="text-sm text-gray-500 mt-1">Perlu tindak lanjut</p>
                        </div>
                        <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Activities -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-clock text-gray-600 mr-2"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Aktivitas Terkini</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Pantau aktivitas sistem perpustakaan secara real-time</p>
                        
                        <div class="space-y-4">
                            <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Buku dipinjam</p>
                                    <p class="text-sm text-gray-600">Kepemimpinan di Era Digital</p>
                                    <div class="flex items-center justify-between mt-1">
                                        <p class="text-xs text-gray-500">Anggota Persma</p>
                                        <p class="text-xs text-gray-400">2 jam lalu</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">Buku dikembalikan</p>
                                    <p class="text-sm text-gray-600">Panduan Organisasi Mahasiswa</p>
                                    <div class="flex items-center justify-between mt-1">
                                        <p class="text-xs text-gray-500">Budi Santoso</p>
                                        <p class="text-xs text-gray-400">5 jam lalu</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions (Tanpa Cetak Dashboard) -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Aksi Cepat</h3>
                        <p class="text-gray-600 mb-4">Akses fitur utama dengan sekali klik</p>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <a href="kelola_buku.php" class="p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors text-center">
                                <i class="fas fa-book text-green-600 text-2xl mb-2"></i>
                                <p class="text-sm font-medium text-green-800">Kelola Buku</p>
                            </a>
                            <a href="kelola_peminjaman.php" class="p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors text-center">
                                <i class="fas fa-users text-blue-600 text-2xl mb-2"></i>
                                <p class="text-sm font-medium text-blue-800">Kelola Peminjaman</p>
                            </a>
                            <a href="laporan_peminjaman.php" class="p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition-colors text-center">
                                <i class="fas fa-chart-bar text-purple-600 text-2xl mb-2"></i>
                                <p class="text-sm font-medium text-purple-800">Laporan Peminjaman</p>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../../includes/footer.php'; ?>

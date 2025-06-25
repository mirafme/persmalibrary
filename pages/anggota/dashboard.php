
<?php
$page_title = "Dashboard Anggota - Perpustakaan Persma";
include '../../includes/header.php';
require_once '../../config/database.php';

// Cek apakah user sudah login dan bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['jabatan'] === 'Administrator') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Hitung statistik user
$user_id = $_SESSION['user_id'];

// Total buku yang dipinjam user
$query = "SELECT COUNT(*) as total FROM peminjaman WHERE id_user = :user_id AND status = 'aktif'";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$aktif_pinjam = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Buku terlambat
$query = "SELECT COUNT(*) as total FROM peminjaman WHERE id_user = :user_id AND estimasi_kembali < CURDATE() AND status = 'aktif'";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$terlambat = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total buku tersedia
$query = "SELECT COUNT(*) as total FROM buku WHERE status = 'tersedia'";
$stmt = $db->prepare($query);
$stmt->execute();
$tersedia = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Get user's current loans
$query = "SELECT p.*, b.judul, b.pengarang, b.kategori,
          CASE 
              WHEN p.estimasi_kembali < CURDATE() AND p.status = 'aktif' THEN 'terlambat'
              ELSE p.status
          END as status_real
          FROM peminjaman p
          JOIN buku b ON p.id_buku = b.id_buku
          WHERE p.id_user = :user_id AND p.status = 'aktif'
          ORDER BY p.tanggal_pinjam DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$current_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent books
$query = "SELECT * FROM buku WHERE status = 'tersedia' ORDER BY id_buku DESC LIMIT 3";
$stmt = $db->prepare($query);
$stmt->execute();
$recent_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../includes/sidebar.php'; ?>
    
    <main class="flex-1 overflow-auto">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">
                        Selamat datang, <?php echo $_SESSION['nama']; ?>!
                    </h1>
                    <p class="text-gray-600 mt-1">
                        Jelajahi koleksi buku digital Persma dan kelola peminjaman Anda.
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Divisi: <?php echo $_SESSION['divisi']; ?></p>
                    <p class="text-sm text-gray-500">Hari ini: <?php echo date('l, d F Y'); ?></p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-l-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Sedang Dipinjam</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $aktif_pinjam; ?></p>
                            <p class="text-sm text-gray-500">Buku aktif</p>
                        </div>
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-book text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-l-orange-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Terlambat</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $terlambat; ?></p>
                            <p class="text-sm text-gray-500">Perlu dikembalikan</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6 border-l-4 border-l-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Tersedia</p>
                            <p class="text-3xl font-bold text-gray-900 mt-2"><?php echo $tersedia; ?></p>
                            <p class="text-sm text-gray-500">Buku dapat dipinjam</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-search text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- My Current Loans -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-history text-gray-600 mr-2"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Peminjaman Aktif Saya</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Buku yang sedang Anda pinjam</p>
                        
                        <?php if (count($current_loans) > 0): ?>
                            <div class="space-y-4">
                                <?php foreach ($current_loans as $loan): ?>
                                <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="w-12 h-16 bg-green-100 rounded flex items-center justify-center">
                                        <i class="fas fa-book text-green-600"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($loan['judul']); ?></p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($loan['pengarang']); ?></p>
                                        <div class="flex items-center justify-between mt-2">
                                            <span class="text-xs px-2 py-1 rounded-full <?php echo ($loan['status_real'] == 'terlambat') ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                                <?php echo ($loan['status_real'] == 'terlambat') ? 'Terlambat' : 'Aktif'; ?>
                                            </span>
                                            <p class="text-xs text-gray-500">
                                                Kembali: <?php echo date('d/m/Y', strtotime($loan['estimasi_kembali'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-book text-gray-400"></i>
                                </div>
                                <p class="text-gray-500">Belum ada buku yang dipinjam</p>
                                <a href="katalog.php" class="mt-3 inline-block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                                    Jelajahi Katalog
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Books -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <div class="flex items-center mb-4">
                            <i class="fas fa-star text-gray-600 mr-2"></i>
                            <h3 class="text-lg font-semibold text-gray-900">Buku Terbaru</h3>
                        </div>
                        <p class="text-gray-600 mb-4">Koleksi terbaru yang tersedia untuk dipinjam</p>
                        
                        <div class="space-y-4">
                            <?php foreach ($recent_books as $book): ?>
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                <div class="w-12 h-16 bg-blue-100 rounded flex items-center justify-center">
                                    <i class="fas fa-book text-blue-600"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($book['judul']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($book['pengarang']); ?></p>
                                    <p class="text-xs text-gray-500 mt-1"><?php echo htmlspecialchars($book['kategori']); ?></p>
                                    <span class="text-xs px-2 py-1 rounded-full mt-2 inline-block bg-green-100 text-green-800">
                                        Tersedia
                                    </span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Aksi Cepat</h3>
                    <p class="text-gray-600 mb-4">Akses fitur utama dengan mudah</p>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <a href="katalog.php" class="p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition-colors text-center">
                            <i class="fas fa-search text-green-600 text-2xl mb-2"></i>
                            <p class="text-sm font-medium text-green-800">Cari Buku</p>
                        </a>
                        <a href="katalog.php" class="p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition-colors text-center">
                            <i class="fas fa-book text-blue-600 text-2xl mb-2"></i>
                            <p class="text-sm font-medium text-blue-800">Katalog</p>
                        </a>
                        <a href="riwayat.php" class="p-4 bg-orange-50 hover:bg-orange-100 rounded-lg border border-orange-200 transition-colors text-center">
                            <i class="fas fa-history text-orange-600 text-2xl mb-2"></i>
                            <p class="text-sm font-medium text-orange-800">Riwayat</p>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../../includes/footer.php'; ?>

<?php
$page_title = "Katalog Buku - Perpustakaan Persma";
include '../../includes/header.php';
require_once '../../config/database.php';

// Cek apakah user sudah login dan bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['jabatan'] === 'Administrator') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle peminjaman buku
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'borrow') {
    $id_buku = $_POST['id_buku'];
    $user_id = $_SESSION['user_id'];
    
    // Cek apakah buku masih tersedia
    $query = "SELECT status FROM buku WHERE id_buku = :id_buku";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_buku', $id_buku);
    $stmt->execute();
    $book_status = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($book_status && $book_status['status'] == 'tersedia') {
        // Hitung estimasi kembali (+7 hari)
        $estimasi_kembali = date('Y-m-d', strtotime('+7 days'));
        
        // Insert peminjaman
        $query = "INSERT INTO peminjaman (id_user, id_buku, tanggal_pinjam, estimasi_kembali, status) 
                  VALUES (:id_user, :id_buku, CURDATE(), :estimasi_kembali, 'aktif')";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_user', $user_id);
        $stmt->bindParam(':id_buku', $id_buku);
        $stmt->bindParam(':estimasi_kembali', $estimasi_kembali);
        
        if ($stmt->execute()) {
            // Update status buku
            $query = "UPDATE buku SET status = 'dipinjam' WHERE id_buku = :id_buku";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':id_buku', $id_buku);
            $stmt->execute();
            
            $message = 'Buku berhasil dipinjam! Harap dikembalikan dalam 7 hari.';
        } else {
            $error = 'Gagal meminjam buku!';
        }
    } else {
        $error = 'Maaf, buku ini sudah tidak tersedia!';
    }
}

// Get search parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kategori = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Daftar kategori yang ditetapkan
$categories = [
    'Fiksi',
    'Non-Fiksi',
    'Pendidikan',
    'Teknologi/Komputer',
    'Agama',
    'Kesehatan',
    'Psikologi',
    'Hukum & Politik',
    'Ekonomi & Bisnis',
    'Anak-anak',
    'Seni & Desain',
    'Sastra'
];

// Build query
$query = "SELECT * FROM buku WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (judul LIKE :search OR pengarang LIKE :search)";
    $params[':search'] = "%$search%";
}

if (!empty($kategori)) {
    $query .= " AND kategori = :kategori";
    $params[':kategori'] = $kategori;
}

$query .= " ORDER BY judul ASC";

$stmt = $db->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../includes/sidebar.php'; ?>
    
    <main class="flex-1 overflow-auto">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Katalog Buku</h1>
                    <p class="text-gray-600 mt-1">Jelajahi koleksi buku digital UKM Persma</p>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Search and Filter -->
            <div class="bg-white rounded-lg shadow p-6">
                <form method="GET" class="flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-64">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Buku</label>
                        <input type="text" id="search" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>"
                               placeholder="Cari berdasarkan judul atau pengarang..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    
                    <div class="min-w-48">
                        <label for="kategori" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                        <select id="kategori" name="kategori" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Semua Kategori</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" 
                                        <?php echo ($kategori == $cat) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                    
                    <a href="katalog.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                        Reset
                    </a>
                </form>
            </div>

            <!-- Books Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <?php foreach ($books as $book): ?>
                <div class="bg-white rounded-lg shadow hover:shadow-lg transition-shadow">
                    <div class="p-6">
                        <div class="w-full h-32 bg-gray-100 rounded-lg flex items-center justify-center mb-4">
                            <i class="fas fa-book text-gray-400 text-3xl"></i>
                        </div>
                        
                        <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2"><?php echo htmlspecialchars($book['judul']); ?></h3>
                        <p class="text-sm text-gray-600 mb-1">Oleh: <?php echo htmlspecialchars($book['pengarang']); ?></p>
                        <p class="text-sm text-gray-500 mb-3">Kategori: <?php echo htmlspecialchars($book['kategori']); ?></p>
                        
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                <?php 
                                switch($book['status']) {
                                    case 'tersedia': echo 'bg-green-100 text-green-800'; break;
                                    case 'dipinjam': echo 'bg-orange-100 text-orange-800'; break;
                                    case 'rusak': echo 'bg-red-100 text-red-800'; break;
                                }
                                ?>">
                                <?php echo ucfirst($book['status']); ?>
                            </span>
                            
                            <?php if ($book['status'] == 'tersedia'): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Pinjam buku ini?')">
                                    <input type="hidden" name="action" value="borrow">
                                    <input type="hidden" name="id_buku" value="<?php echo $book['id_buku']; ?>">
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm">
                                        <i class="fas fa-plus mr-1"></i>Pinjam
                                    </button>
                                </form>
                            <?php else: ?>
                                <button disabled class="bg-gray-300 text-gray-500 px-3 py-1 rounded-md text-sm cursor-not-allowed">
                                    Tidak Tersedia
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (empty($books)): ?>
                <div class="text-center py-12">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-book text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada buku ditemukan</h3>
                    <p class="text-gray-500">Coba ubah kata kunci pencarian atau filter kategori.</p>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include '../../includes/footer.php'; ?>
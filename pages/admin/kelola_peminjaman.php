
<?php
$page_title = "Kelola Peminjaman - Perpustakaan Persma";
include '../../includes/header.php';
require_once '../../config/database.php';

// Cek apakah user sudah login dan adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['jabatan'] !== 'Administrator') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$message = '';
$error = '';

// Handle pengembalian buku
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'return') {
    $id_peminjaman = $_POST['id_peminjaman'];
    $id_buku = $_POST['id_buku'];
    
    // Insert pengembalian
    $query = "INSERT INTO pengembalian (id_peminjaman, tanggal_kembali, id_user_pengelola) VALUES (:id_peminjaman, CURDATE(), :id_user_pengelola)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_peminjaman', $id_peminjaman);
    $stmt->bindParam(':id_user_pengelola', $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        // Update status peminjaman
        $query = "UPDATE peminjaman SET status = 'dikembalikan' WHERE id_peminjaman = :id_peminjaman";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_peminjaman', $id_peminjaman);
        $stmt->execute();
        
        // Update status buku
        $query = "UPDATE buku SET status = 'tersedia' WHERE id_buku = :id_buku";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_buku', $id_buku);
        $stmt->execute();
        
        $message = 'Buku berhasil dikembalikan!';
    } else {
        $error = 'Gagal memproses pengembalian!';
    }
}

// Get active loans
$query = "SELECT p.*, u.nama as nama_peminjam, b.judul, b.pengarang, b.kategori,
          CASE 
              WHEN p.estimasi_kembali < CURDATE() AND p.status = 'aktif' THEN 'terlambat'
              ELSE p.status
          END as status_real
          FROM peminjaman p
          JOIN user u ON p.id_user = u.id_user
          JOIN buku b ON p.id_buku = b.id_buku
          WHERE p.status = 'aktif'
          ORDER BY p.tanggal_pinjam DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$active_loans = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get returned books
$query = "SELECT p.*, u.nama as nama_peminjaman, b.judul, b.pengarang, 
          u2.nama as nama_pengelola, pen.tanggal_kembali
          FROM peminjaman p
          JOIN user u ON p.id_user = u.id_user
          JOIN buku b ON p.id_buku = b.id_buku
          LEFT JOIN pengembalian pen ON p.id_peminjaman = pen.id_peminjaman
          LEFT JOIN user u2 ON pen.id_user_pengelola = u2.id_user
          WHERE p.status = 'dikembalikan'
          ORDER BY pen.tanggal_kembali DESC
          LIMIT 10";
$stmt = $db->prepare($query);
$stmt->execute();
$returned_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../includes/sidebar.php'; ?>
    
    <main class="flex-1 overflow-auto">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Kelola Peminjaman</h1>
                    <p class="text-gray-600 mt-1">Kelola peminjaman dan pengembalian buku</p>
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

            <!-- Active Loans -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Peminjaman Aktif</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peminjam</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buku</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pinjam</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estimasi Kembali</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($active_loans as $loan): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($loan['nama_peminjam']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($loan['judul']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($loan['pengarang']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                        <?php echo date('d/m/Y', strtotime($loan['tanggal_pinjam'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                        <?php echo date('d/m/Y', strtotime($loan['estimasi_kembali'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            <?php echo ($loan['status_real'] == 'terlambat') ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                                            <?php echo ($loan['status_real'] == 'terlambat') ? 'Terlambat' : 'Aktif'; ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Proses pengembalian buku ini?')">
                                            <input type="hidden" name="action" value="return">
                                            <input type="hidden" name="id_peminjaman" value="<?php echo $loan['id_peminjaman']; ?>">
                                            <input type="hidden" name="id_buku" value="<?php echo $loan['id_buku']; ?>">
                                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-sm">
                                                <i class="fas fa-check mr-1"></i>Terima Kembali
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recently Returned -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Buku Dikembalikan (10 Terbaru)</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Peminjam</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buku</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Kembali</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diproses Oleh</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($returned_books as $return): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($return['nama_peminjaman']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($return['judul']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($return['pengarang']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                        <?php echo date('d/m/Y', strtotime($return['tanggal_kembali'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                        <?php echo htmlspecialchars($return['nama_pengelola']); ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<?php include '../../includes/footer.php'; ?>


<?php
$page_title = "Kelola Buku - Perpustakaan Persma";
include '../../includes/header.php';
require_once '../../config/database.php';

// Cek apakah user sudah login dan adalah admin
if (!isset($_SESSION['user_id']) || $_SESSION['jabatan'] !== 'Administrator') {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// ✅ Tambahkan array kategori setelah koneksi database
$kategori_list = [
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

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $judul = $_POST['judul'];
                $pengarang = $_POST['pengarang'];
                $kategori = $_POST['kategori'];
                
                $query = "INSERT INTO buku (judul, pengarang, kategori, status) VALUES (:judul, :pengarang, :kategori, 'tersedia')";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':judul', $judul);
                $stmt->bindParam(':pengarang', $pengarang);
                $stmt->bindParam(':kategori', $kategori);
                
                if ($stmt->execute()) {
                    $message = 'Buku berhasil ditambahkan!';
                } else {
                    $error = 'Gagal menambahkan buku!';
                }
                break;
                
case 'edit':
    $id_buku = $_POST['id_buku'];
    $judul = $_POST['judul'];
    $pengarang = $_POST['pengarang'];
    $kategori = $_POST['kategori'];
    $status = $_POST['status'];

    // Jika status arsip maka aktif = 0, selain itu aktif = 1
    $aktif = ($status === 'arsip') ? 0 : 1;

    $query = "UPDATE buku 
              SET judul = :judul, pengarang = :pengarang, kategori = :kategori, status = :status, aktif = :aktif 
              WHERE id_buku = :id_buku";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':judul', $judul);
    $stmt->bindParam(':pengarang', $pengarang);
    $stmt->bindParam(':kategori', $kategori);
    $stmt->bindParam(':status', $status);
    $stmt->bindParam(':aktif', $aktif); // tambahkan ini
    $stmt->bindParam(':id_buku', $id_buku);

    if ($stmt->execute()) {
        $message = 'Buku berhasil diperbarui!';
    } else {
        $error = 'Gagal memperbarui buku!';
    }
    break;
                
case 'delete':
    $id_buku = $_POST['id_buku'];

    $query = "UPDATE buku SET status = 'arsip', aktif = 0 WHERE id_buku = :id_buku";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_buku', $id_buku);

    if ($stmt->execute()) {
        $message = 'Buku berhasil diarsipkan!';
    } else {
        $error = 'Gagal mengarsipkan buku!';
    }
    break;



        }
    }
}

// Get all books
// Get all books except archived
$query = "SELECT * FROM buku WHERE status != 'arsip' ORDER BY id_buku DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Get book for editing if ID is provided
$edit_book = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $query = "SELECT * FROM buku WHERE id_buku = :id_buku";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_buku', $edit_id);
    $stmt->execute();
    $edit_book = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../includes/sidebar.php'; ?>
    
    <main class="flex-1 overflow-auto">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Kelola Buku</h1>
                    <p class="text-gray-600 mt-1">Tambah, edit, dan hapus buku dalam koleksi perpustakaan</p>
                </div>
                <button onclick="showAddForm()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-plus mr-2"></i>Tambah Buku
                </button>
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

            <!-- Add/Edit Form -->
            <div id="bookForm" class="bg-white rounded-lg shadow p-6" style="display: <?php echo $edit_book ? 'block' : 'none'; ?>;">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    <?php echo $edit_book ? 'Edit Buku' : 'Tambah Buku Baru'; ?>
                </h3>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="<?php echo $edit_book ? 'edit' : 'add'; ?>">
                    <?php if ($edit_book): ?>
                        <input type="hidden" name="id_buku" value="<?php echo $edit_book['id_buku']; ?>">
                    <?php endif; ?>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="judul" class="block text-sm font-medium text-gray-700 mb-1">Judul Buku</label>
                            <input type="text" id="judul" name="judul" required 
                                   value="<?php echo $edit_book ? htmlspecialchars($edit_book['judul']) : ''; ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        
                        <div>
                            <label for="pengarang" class="block text-sm font-medium text-gray-700 mb-1">Pengarang</label>
                            <input type="text" id="pengarang" name="pengarang" required 
                                   value="<?php echo $edit_book ? htmlspecialchars($edit_book['pengarang']) : ''; ?>"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        </div>
                        
                        <div>
                            <label for="kategori" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
<select id="kategori" name="kategori" required 
        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
    <option value="">-- Pilih Kategori --</option>
    <?php foreach ($kategori_list as $kategori): ?>
        <option value="<?php echo $kategori; ?>" 
            <?php echo ($edit_book && $edit_book['kategori'] === $kategori) ? 'selected' : ''; ?>>
            <?php echo $kategori; ?>
        </option>
    <?php endforeach; ?>
</select>

                        </div>
                        
                        <?php if ($edit_book): ?>
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="status" name="status" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="tersedia" <?php echo ($edit_book['status'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                <option value="dipinjam" <?php echo ($edit_book['status'] == 'dipinjam') ? 'selected' : ''; ?>>Dipinjam</option>
                                <option value="rusak" <?php echo ($edit_book['status'] == 'rusak') ? 'selected' : ''; ?>>Rusak</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                            <?php echo $edit_book ? 'Update Buku' : 'Tambah Buku'; ?>
                        </button>
                        <button type="button" onclick="hideForm()" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                            Batal
                        </button>
                    </div>
                </form>
            </div>

            <!-- Books List -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Daftar Buku</h3>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengarang</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($books as $book): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($book['judul']); ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                        <?php echo htmlspecialchars($book['pengarang']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                                        <?php echo htmlspecialchars($book['kategori']); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            <?php 
                                            switch($book['status']) {
                                                case 'tersedia': echo 'bg-green-100 text-green-800'; break;
                                                case 'dipinjam': echo 'bg-orange-100 text-orange-800'; break;
                                                case 'rusak': echo 'bg-red-100 text-red-800'; break;
                                                default: echo 'bg-gray-100 text-gray-800';
                                            }
                                            ?>">
                                            <?php echo ucfirst($book['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="?edit=<?php echo $book['id_buku']; ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus buku ini?')">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id_buku" value="<?php echo $book['id_buku']; ?>">
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i> Hapus
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
        </div>
    </main>
</div>

<script>
function showAddForm() {
    document.getElementById('bookForm').style.display = 'block';
}

function hideForm() {
    document.getElementById('bookForm').style.display = 'none';
    window.location.href = 'kelola_buku.php';
}
</script>

<?php include '../../includes/footer.php'; ?>

<?php
$page_title = "Kelola Peminjaman Divisi - Perpustakaan Persma";
include '../../includes/header.php';
require_once '../../config/database.php';

if (!isset($_SESSION['jabatan_id']) || $_SESSION['jabatan_id'] != 4) {
    header("Location: ../auth/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Ambil filter
$filter_divisi = isset($_GET['divisi']) ? $_GET['divisi'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query utama
$query = "SELECT p.*, u.nama, u.divisi, u.angkatan, b.judul, b.pengarang, b.kategori,
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
          WHERE u.jabatan_id = 7";

$params = [];

if (!empty($filter_divisi)) {
    $query .= " AND u.divisi = :divisi";
    $params[':divisi'] = $filter_divisi;
}

if (!empty($filter_status)) {
    if ($filter_status == 'aktif') {
        $query .= " AND pg.id_pengembalian IS NULL AND p.estimasi_kembali >= CURDATE()";
    } elseif ($filter_status == 'terlambat') {
        $query .= " AND pg.id_pengembalian IS NULL AND p.estimasi_kembali < CURDATE()";
    } elseif ($filter_status == 'dikembalikan') {
        $query .= " AND pg.id_pengembalian IS NOT NULL";
    }
}

if (!empty($filter_tahun)) {
    $query .= " AND YEAR(p.tanggal_pinjam) = :tahun";
    $params[':tahun'] = $filter_tahun;
}

if (!empty($search)) {
    $query .= " AND (u.nama LIKE :search OR b.judul LIKE :search)";
    $params[':search'] = "%$search%";
}

$query .= " ORDER BY p.tanggal_pinjam DESC";

$stmt = $db->prepare($query);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val);
}
$stmt->execute();
$peminjaman = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../includes/sidebar.php'; ?>

    <main class="flex-1 overflow-auto">
        <div class="p-6 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Kelola Peminjaman Divisi</h1>
                    <p class="text-gray-600 mt-1">Peminjaman oleh anggota divisi</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Hari ini</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo date('l, d F Y'); ?></p>
                </div>
            </div>

            <!-- FILTER -->
<form method="GET" class="flex flex-wrap md:flex-nowrap gap-4 items-end">
    <!-- Filter Divisi -->
    <div class="flex-1 min-w-[150px]">
        <label class="block text-sm font-medium text-gray-700 mb-1">Divisi</label>
        <select name="divisi" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm shadow-sm">
            <option value="">-- Semua Divisi --</option>
            <?php
            $divisi_options = ['Konten', 'Litbang', 'PSDM', 'Marketing', 'Redaksi'];
            foreach ($divisi_options as $div) {
                echo '<option value="'.$div.'" '.($filter_divisi == $div ? 'selected' : '').'>'.$div.'</option>';
            }
            ?>
        </select>
    </div>
                <!-- Filter Status -->
 <!-- Filter Status -->
    <div class="flex-1 min-w-[150px]">
        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
        <select name="status" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm shadow-sm">
            <option value="">-- Semua --</option>
            <option value="aktif" <?= $filter_status == 'aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="terlambat" <?= $filter_status == 'terlambat' ? 'selected' : '' ?>>Terlambat</option>
            <option value="dikembalikan" <?= $filter_status == 'dikembalikan' ? 'selected' : '' ?>>Dikembalikan</option>
        </select>
    </div>

    <!-- Filter Tahun -->
    <div class="flex-1 min-w-[100px]">
        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
        <input type="number" name="tahun" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm shadow-sm" value="<?= htmlspecialchars($filter_tahun); ?>">
    </div>

    <!-- Search Bar -->
    <div class="flex-1 min-w-[200px]">
        <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
        <input type="text" name="search" class="w-full border border-gray-300 rounded-md px-4 py-2 text-sm shadow-sm" placeholder="Nama / Judul Buku..." value="<?= htmlspecialchars($search); ?>">
    </div>

    <!-- Search Button -->
    <div class="pt-6">
        <button type="submit" class="px-5 py-2 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 shadow-sm">
            Cari
        </button>
    </div>
</form>

            <!-- Table -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-green-100 text-gray-800">
                        <tr>
                            <th class="px-4 py-3 border-b">Nama</th>
                            <th class="px-4 py-3 border-b">Angkatan</th>
                            <th class="px-4 py-3 border-b">Judul Buku</th>
                            <th class="px-4 py-3 border-b">Pengarang</th>
                            <th class="px-4 py-3 border-b">Kategori</th>
                            <th class="px-4 py-3 border-b">Tanggal Pinjam</th>
                            <th class="px-4 py-3 border-b">Estimasi Kembali</th>
                            <th class="px-4 py-3 border-b">Tanggal Kembali</th>
                            <th class="px-4 py-3 border-b">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if (count($peminjaman) > 0): ?>
                            <?php foreach ($peminjaman as $row): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 border-b"><?= htmlspecialchars($row['nama']); ?></td>
                                    <td class="px-4 py-3 border-b"><?= htmlspecialchars($row['angkatan']); ?></td>
                                    <td class="px-4 py-3 border-b"><?= htmlspecialchars($row['judul']); ?></td>
                                    <td class="px-4 py-3 border-b"><?= htmlspecialchars($row['pengarang']); ?></td>
                                    <td class="px-4 py-3 border-b"><?= htmlspecialchars($row['kategori']); ?></td>
                                    <td class="px-4 py-3 border-b"><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                    <td class="px-4 py-3 border-b"><?= date('d/m/Y', strtotime($row['estimasi_kembali'])); ?></td>
                                    <td class="px-4 py-3 border-b">
                                        <?= $row['tanggal_kembali'] ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '<span class="text-gray-400">-</span>'; ?>
                                    </td>
                                    <td class="px-4 py-3 border-b">
                                        <span class="inline-block text-xs px-2 py-1 rounded-full font-semibold
                                            <?php
                                            switch ($row['status_pinjam']) {
                                                case 'aktif': echo 'bg-green-100 text-green-800'; break;
                                                case 'terlambat': echo 'bg-red-100 text-red-800'; break;
                                                case 'dikembalikan': echo 'bg-gray-100 text-gray-800'; break;
                                            }
                                            ?>">
                                            <?= ucfirst($row['status_pinjam']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center px-6 py-6 text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-2"></i><br>
                                    Tidak ada data peminjaman ditemukan.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<?php include '../../includes/footer.php'; ?>

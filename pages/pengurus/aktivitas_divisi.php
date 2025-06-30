<?php
$page_title = "Aktivitas Divisi - Perpustakaan Persma";
include '../../includes/header.php';
require_once '../../config/database.php';

// Cek hanya untuk pimpinan divisi
if (!isset($_SESSION['jabatan_id']) || $_SESSION['jabatan_id'] != 6) {
    header("Location: ../auth/login.php");
    exit();
}

// Pastikan session divisi tersedia
if (!isset($_SESSION['divisi'])) {
    echo "<div class='p-6 text-red-600 font-semibold'>Divisi tidak ditemukan. Silakan login ulang.</div>";
    include '../../includes/footer.php';
    exit();
}

// ✅ Konversi divisi ke lowercase dan hilangkan spasi tambahan
$divisi_pengurus = trim(strtolower($_SESSION['divisi']));

$database = new Database();
$db = $database->getConnection();


// Ambil aktivitas peminjaman anggota biasa (jabatan_id = 7) dari divisi yang sama
$query = "
    SELECT p.*, u.nama, u.angkatan, b.judul,
        CASE 
            WHEN pg.id_pengembalian IS NOT NULL THEN 'dikembalikan'
            WHEN p.estimasi_kembali < CURDATE() THEN 'terlambat'
            ELSE 'aktif'
        END AS status_pinjam
    FROM peminjaman p
    JOIN user u ON p.id_user = u.id_user
    JOIN buku b ON p.id_buku = b.id_buku
    LEFT JOIN pengembalian pg ON p.id_peminjaman = pg.id_peminjaman
    WHERE u.jabatan_id = 7 AND LOWER(TRIM(u.divisi)) = :divisi
    ORDER BY p.tanggal_pinjam DESC
";


$stmt = $db->prepare($query);
$stmt->bindParam(':divisi', $divisi_pengurus);
$stmt->execute();
$dataAktivitas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="flex h-screen bg-gray-50">
    <?php include '../../includes/sidebar.php'; ?>

    <main class="flex-1 overflow-auto">
        <div class="p-6 space-y-6">
            <!-- Judul -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold text-gray-900">Aktivitas Peminjaman Divisi Anda</h1>
                <p class="text-gray-600 mt-1">
                    Menampilkan aktivitas <strong>anggota</strong> divisi <strong><?= htmlspecialchars($divisi_pengurus); ?></strong>
                </p>
            </div>

            <!-- Tabel Aktivitas -->
            <div class="bg-white rounded-lg shadow overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-green-100 text-gray-800">
                        <tr>
                            <th class="px-4 py-3 border-b">Nama</th>
                            <th class="px-4 py-3 border-b">Angkatan</th>
                            <th class="px-4 py-3 border-b">Judul Buku</th>
                            <th class="px-4 py-3 border-b">Tanggal Pinjam</th>
                            <th class="px-4 py-3 border-b">Estimasi Kembali</th>
                            <th class="px-4 py-3 border-b">Status</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700">
                        <?php if (count($dataAktivitas) > 0): ?>
                            <?php foreach ($dataAktivitas as $row): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 border-b"><?= htmlspecialchars($row['nama']); ?></td>
                                    <td class="px-4 py-3 border-b"><?= htmlspecialchars($row['angkatan']); ?></td>
                                    <td class="px-4 py-3 border-b"><?= htmlspecialchars($row['judul']); ?></td>
                                    <td class="px-4 py-3 border-b"><?= date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                                    <td class="px-4 py-3 border-b"><?= date('d/m/Y', strtotime($row['estimasi_kembali'])); ?></td>
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
                                <td colspan="6" class="text-center px-6 py-6 text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-2"></i><br>
                                    Tidak ada aktivitas peminjaman ditemukan.
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

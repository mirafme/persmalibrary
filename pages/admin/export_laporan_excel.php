<?php
require_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$filter_status = $_GET['status'] ?? '';
$filter_bulan = $_GET['bulan'] ?? '';
$filter_tahun = $_GET['tahun'] ?? date('Y');
$search = $_GET['search'] ?? '';

// Query mirip laporan_peminjaman.php
$query = "SELECT u.nama, u.divisi, u.angkatan, u.no_wa, b.judul, p.tanggal_pinjam, pg.tanggal_kembali
          FROM peminjaman p
          LEFT JOIN user u ON p.id_user = u.id_user
          LEFT JOIN buku b ON p.id_buku = b.id_buku
          LEFT JOIN pengembalian pg ON p.id_peminjaman = pg.id_peminjaman
          WHERE 1=1";

$params = [];

if ($filter_status == 'aktif') {
    $query .= " AND pg.id_pengembalian IS NULL AND p.estimasi_kembali >= CURDATE()";
} elseif ($filter_status == 'terlambat') {
    $query .= " AND pg.id_pengembalian IS NULL AND p.estimasi_kembali < CURDATE()";
} elseif ($filter_status == 'dikembalikan') {
    $query .= " AND pg.id_pengembalian IS NOT NULL";
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
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Export headers
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Peminjaman_" . date("Ymd_His") . ".xls");

// Output Excel data
echo "<table border='1'>";
echo "<tr>
        <th>Nama</th>
        <th>Divisi</th>
        <th>Angkatan</th>
        <th>Nomor WA</th>
        <th>Judul Buku</th>
        <th>Tanggal Pinjam</th>
        <th>Tanggal Kembali</th>
      </tr>";

foreach ($data as $row) {
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
    echo "<td>" . htmlspecialchars($row['divisi']) . "</td>";
    echo "<td>" . htmlspecialchars($row['angkatan']) . "</td>";
    echo "<td>" . htmlspecialchars($row['no_wa']) . "</td>";
    echo "<td>" . htmlspecialchars($row['judul']) . "</td>";
    echo "<td>" . date('d/m/Y', strtotime($row['tanggal_pinjam'])) . "</td>";
    echo "<td>" . ($row['tanggal_kembali'] ? date('d/m/Y', strtotime($row['tanggal_kembali'])) : '-') . "</td>";
    echo "</tr>";
}
echo "</table>";
exit;
?>

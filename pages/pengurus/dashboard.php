<?php
session_start();
require_once '../../includes/functions.php';

// Cek login dan jabatan pengurus
if (!isset($_SESSION['jabatan_id']) || !isPengurus($_SESSION['jabatan_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// Arahkan ke file sesuai jabatan
switch ($_SESSION['jabatan_id']) {
    case 3: // Ketua
        header("Location: laporan_ketua.php");
        break;
    case 4: // Sekretaris
        header("Location: kelola_peminjaman_divisi.php");
        break;
    case 5: // Bendahara
        header("Location: statistik_peminjaman.php");
        break;
    case 6: // Pimpinan Divisi
        header("Location: aktivitas_divisi.php");
        break;
    default:
        header("Location: ../auth/login.php");
        break;
}
exit();

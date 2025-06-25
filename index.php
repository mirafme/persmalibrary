
<?php
session_start();

// Redirect ke login jika belum login
if (!isset($_SESSION['user_id'])) {
    header("Location: pages/auth/login.php");
    exit();
}

// Redirect ke dashboard sesuai role
$is_admin = isset($_SESSION['jabatan']) && $_SESSION['jabatan'] === 'Administrator';

if ($is_admin) {
    header("Location: pages/admin/dashboard.php");
} else {
    header("Location: pages/anggota/dashboard.php");
}
exit();
?>

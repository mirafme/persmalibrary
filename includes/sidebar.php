<?php
$current_page = basename($_SERVER['PHP_SELF']);
$is_admin = isset($_SESSION['jabatan']) && $_SESSION['jabatan'] === 'Administrator';
?>

<div class="w-64 bg-white shadow-lg h-screen flex flex-col">
    <!-- Header -->
    <div class="p-6 border-b border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-book-open text-white"></i>
            </div>
            <div>
                <h1 class="font-bold text-lg text-gray-900">Persma Library</h1>
                <p class="text-sm text-gray-500">Digital Book Management</p>
            </div>
        </div>
    </div>

    <!-- User Info -->
    <div class="p-4 border-b border-gray-200">
        <div class="flex items-center space-x-3">
            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <span class="text-green-600 font-semibold text-sm">
                    <?php echo strtoupper(substr($_SESSION['nama'], 0, 1)); ?>
                </span>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">
                    <?php echo $_SESSION['nama']; ?>
                </p>
                <p class="text-xs text-gray-500">
                    <?php echo $is_admin ? 'Administrator' : 'Anggota'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-2">
        <?php if ($is_admin): ?>
            <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-home w-4 h-4 mr-3"></i>
                Dashboard
            </a>
            <a href="kelola_buku.php" class="<?php echo ($current_page == 'kelola_buku.php') ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-book w-4 h-4 mr-3"></i>
                Kelola Buku
            </a>
            <a href="kelola_peminjaman.php" class="<?php echo ($current_page == 'kelola_peminjaman.php') ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-users w-4 h-4 mr-3"></i>
                Kelola Peminjaman
            </a>
            <a href="laporan_peminjaman.php" class="<?php echo ($current_page == 'laporan_peminjaman.php') ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-chart-bar w-4 h-4 mr-3"></i>
                Laporan Peminjaman
            </a>
        <?php else: ?>
            <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-home w-4 h-4 mr-3"></i>
                Dashboard
            </a>
            <a href="katalog.php" class="<?php echo ($current_page == 'katalog.php') ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-search w-4 h-4 mr-3"></i>
                Katalog Buku
            </a>
            <a href="riwayat.php" class="<?php echo ($current_page == 'riwayat.php') ? 'bg-green-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?> flex items-center px-4 py-2 rounded-lg transition-colors">
                <i class="fas fa-history w-4 h-4 mr-3"></i>
                Riwayat Pinjam
            </a>
        <?php endif; ?>
    </nav>

    <!-- Logout -->
    <div class="p-4 border-t border-gray-200">
        <a href="../auth/logout.php" class="w-full flex items-center px-4 py-2 text-red-600 hover:bg-red-50 hover:text-red-700 rounded-lg transition-colors">
            <i class="fas fa-sign-out-alt w-4 h-4 mr-3"></i>
            Keluar
        </a>
    </div>
</div>

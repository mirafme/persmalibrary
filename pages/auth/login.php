
<?php
$page_title = "Login - Perpustakaan Persma";
include '../../includes/header.php';
require_once '../../config/database.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    $is_admin = isset($_SESSION['jabatan']) && $_SESSION['jabatan'] === 'Administrator';
    header("Location: ../" . ($is_admin ? 'admin' : 'anggota') . "/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT u.*, j.nama_jabatan 
              FROM user u 
              JOIN jabatan j ON u.jabatan_id = j.id_jabatan 
              WHERE u.username = :username AND u.password = :password";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['jabatan'] = $user['nama_jabatan'];
        $_SESSION['divisi'] = $user['divisi'];
        
        $is_admin = $user['nama_jabatan'] === 'Administrator';
        header("Location: ../" . ($is_admin ? 'admin' : 'anggota') . "/dashboard.php");
        exit();
    } else {
        $error = 'Username atau password salah!';
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <div class="mx-auto h-12 w-12 bg-green-600 rounded-lg flex items-center justify-center">
                <i class="fas fa-book-open text-white text-xl"></i>
            </div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                Masuk ke Persma Library
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Sistem Informasi Perpustakaan Digital UKM Persma
            </p>
        </div>
        
        <form class="mt-8 space-y-6" method="POST">
            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="rounded-md shadow-sm -space-y-px">
                <div>
                    <label for="username" class="sr-only">Username</label>
                    <input id="username" name="username" type="text" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm" 
                           placeholder="Username">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" required 
                           class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm" 
                           placeholder="Password">
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <i class="fas fa-lock text-green-500 group-hover:text-green-400"></i>
                    </span>
                    Masuk
                </button>
            </div>
        </form>
        
        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300" />
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-gray-50 text-gray-500">Demo Login</span>
                </div>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-3">
                <div class="text-center p-2 bg-blue-50 rounded-md">
                    <p class="text-xs text-blue-600 font-medium">Admin</p>
                    <p class="text-xs text-blue-500">admin / admin123</p>
                </div>
                <div class="text-center p-2 bg-green-50 rounded-md">
                    <p class="text-xs text-green-600 font-medium">Anggota</p>
                    <p class="text-xs text-green-500">anggota / anggota123</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>

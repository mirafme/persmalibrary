
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
    
$query = "SELECT u.id_user, u.nama, u.username, u.divisi, u.angkatan, u.jabatan_id, j.nama_jabatan 
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
   $_SESSION['angkatan'] = $user['angkatan'];
$_SESSION['jabatan_id'] = $user['jabatan_id'];

if ($user['nama_jabatan'] === 'Administrator') {
    header("Location: ../admin/dashboard.php");
} elseif (in_array($user['jabatan_id'], [3,4,5,6])) {
    // Pengurus
    header("Location: ../pengurus/dashboard.php");
} else {
    // Anggota biasa
    header("Location: ../anggota/dashboard.php");
}
exit();

    } else {
        $error = 'Username atau password salah!';
    }
}
?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 px-4">
  <div class="max-w-md w-full bg-white p-8 rounded-lg shadow">
    
    <!-- LOGO -->
    <div class="w-14 h-14 mx-auto bg-green-600 rounded-lg flex items-center justify-center mb-4">
      <i class="fas fa-book-open text-white text-2xl"></i>
    </div>
    
    <!-- JUDUL -->
    <div class="text-center mb-6">
      <h1 class="text-3xl font-bold text-gray-900">Sistem Perpustakaan</h1>
      <p class="text-sm text-gray-600 mt-2">Masuk ke akun anda untuk melanjutkan</p>
    </div>

    <!-- FORM LOGIN -->
    <form class="space-y-5" method="POST">
      <?php if ($error): ?>
        <div class="bg-red-100 border border-red-300 text-red-700 px-4 py-3 rounded">
          <?php echo $error; ?>
        </div>
      <?php endif; ?>

      <div>
        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
        <input id="username" name="username" type="text" required
          class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm focus:ring-green-500 focus:border-green-500"
          placeholder="Masukan Username">
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
        <input id="password" name="password" type="password" required
          class="w-full px-4 py-2 border border-gray-300 rounded-md text-sm focus:ring-green-500 focus:border-green-500"
          placeholder="Masukan Password">
      </div>

      <button type="submit"
        class="w-full bg-green-600 text-white py-2 rounded-md font-semibold hover:bg-green-700 transition">
        Masuk
      </button>
    </form>
  </div>
</div>



<?php include '../../includes/footer.php'; ?>

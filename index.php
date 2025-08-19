<?php
session_start();

$currentPage = $_GET['page'] ?? 'home';

// halaman yang bisa diakses tanpa login
$publicPages = ['check_model', 'monitoring'];

// kalau halaman bukan public dan belum login → redirect login
if (!in_array($currentPage, $publicPages) && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// kalau monitoring langsung include (tanpa layout)
if ($currentPage === 'monitoring') {
    include 'monitoring_manual.php';
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dashboard - DIGITAL OM/IM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="flex h-screen bg-gray-100">

<?php if (isset($_SESSION['user_id'])): ?>
  <!-- SIDEBAR -->
  <aside class="w-64 bg-white shadow-md flex flex-col">
    <div class="px-6 py-5 border-b">
      <img src="assets/kyb.png" alt="Logo" class="w-28 mx-auto">
    </div>
    <nav class="flex-1 px-4 py-6 space-y-2">
      <a href="index.php?page=home" class="flex items-center px-3 py-2 rounded-md <?= $currentPage === 'home' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600' ?>">
        <i class="fa fa-pen-to-square px-2"></i> Input IM/OM
      </a>
      <a href="index.php?page=check_model" class="flex items-center px-3 py-2 rounded-md <?= $currentPage === 'check_model' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600' ?>">
        <i class="fa fa-magnifying-glass px-2"></i> Check Model
      </a>
      <a href="#" 
        onclick="openMonitoringModal(); return false;"
        class="flex items-center px-3 py-2 rounded-md <?= $currentPage === 'monitoring' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600' ?>">
        <i class="fa fa-chart-line px-2"></i> Monitoring OM/IM
      </a>
    </nav>
  </aside>
<?php endif; ?>

  <!-- MAIN CONTENT -->
  <div class="flex-1 flex flex-col">
    <?php if (isset($_SESSION['user_id'])): ?>
      <!-- Header -->
      <header class="flex justify-between items-center bg-white shadow px-6 py-5">
        <h1 class="text-lg font-semibold">
          Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
        </h1>
        <div class="relative">
          <button id="profileBtn" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100">
            <span class="font-medium text-gray-700"><?= htmlspecialchars($_SESSION['username']) ?></span>
            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
              <i class="fa fa-user text-gray-700"></i>
            </div>
          </button>
          <div id="profileMenu" class="hidden absolute right-0 mt-2 w-40 bg-white shadow-lg rounded-md border">
            <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
          </div>
        </div>
      </header>
    <?php endif; ?>

    <!-- Dynamic Page Content -->
    <main class="flex-1 p-6">
     <?php
        switch ($currentPage) {
          case 'check_model':
            include 'check_model.php';
            break;
          case 'manual':
            include 'manual_partial.php';
            break;
          case 'monitoring':
            include 'monitoring_manual.php';
            break;
          case 'workstations':
            include 'workstations.php';
            break;
          case 'sub_workstations':
            include 'sub_workstations.php';
            break;
          case 'detail_sub_workstations':
            include 'detail_sub_workstations.php';
            break;
          default:
            include 'dashboard_home.php';
        }
      ?>
    </main>
  </div>

  <?php if (isset($_SESSION['user_id'])): ?>
  <script>
    const profileBtn = document.getElementById('profileBtn');
    const profileMenu = document.getElementById('profileMenu');
    profileBtn.addEventListener('click', () => profileMenu.classList.toggle('hidden'));
    document.addEventListener('click', (e) => {
      if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
          profileMenu.classList.add('hidden');
      }
    });
  </script>
  <?php endif; ?>

  <!-- Modal Monitoring -->
  <div id="monitoringModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
      
      <!-- Tombol Close -->
      <button onclick="closeMonitoringModal()" class="absolute top-2 right-2 px-3 text-gray-500 hover:text-gray-700">
        ✕
      </button>

      <h3 class="text-lg font-bold text-gray-800 mb-4">⚠ Akses Monitoring ⚠</h3>

      <form id="monitoringForm" method="GET" action="index.php">
        <input type="hidden" name="page" value="monitoring">

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">NPK</label>
          <input type="text" name="npk" required
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-red-300">
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Mesin</label>
          <select name="machine" required
            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-red-300">
            <option value="">-- Pilih Mesin --</option>
            <option value="M001">Mesin 001</option>
            <option value="M002">Mesin 002</option>
            <option value="M003">Mesin 003</option>
          </select>
        </div>

        <div class="flex justify-end gap-2">
          <button type="button" onclick="closeMonitoringModal()" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
            Batal
          </button>
          <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
            OK
          </button>
        </div>
      </form>
    </div>
  </div>
  
  <script>
  function openMonitoringModal() {
      document.getElementById('monitoringModal').classList.remove('hidden');
      document.getElementById('monitoringModal').classList.add('flex');
  }
  function closeMonitoringModal() {
      document.getElementById('monitoringModal').classList.add('hidden');
      document.getElementById('monitoringModal').classList.remove('flex');
  }
  </script>

</body>
</html>

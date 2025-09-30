<?php
session_start();
require_once 'config.php';
require_once 'actions/check_monitoring.php';

$currentPage = $_GET['page'] ?? 'main_dashboard';

$publicPages = ['check_model', 'monitoring'];

if (!in_array($currentPage, $publicPages) && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ===== FILTER ROLE=====
if ($currentPage === 'home') {
    if (isset($_SESSION['dept']) && !in_array($_SESSION['dept'], ['QA', 'MIS'])) {
        // redirect sesuai dept
        header("Location: index.php?page=workstations&dept_id=" . $_SESSION['dept_id']);
        exit;
    }
}

// cek monitoring logic
$alert = null;
if ($currentPage === 'monitoring' && isset($_GET['npk'])) {
    $alert = checkMonitoringAccess($connUser, $connIMOM, $_GET['npk'], $_GET['machine'] ?? '');
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
      <img src="assets/kyb.png" alt="KYB Logo" class="w-28 mx-auto">
    </div>
    <nav class="flex-1 px-4 py-6 space-y-2">
      <a href="index.php?page=main_dashboard" 
        class="flex items-center px-3 py-2 rounded-md <?= $currentPage === 'main_dashboard' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600' ?>">
        <i class="fa fa-home px-2"></i> Dashboard
      </a>
      <a href="index.php?page=home" class="flex items-center px-3 py-2 rounded-md <?= $currentPage === 'home' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600' ?>">
        <i class="fa fa-pen-to-square px-2"></i> Input IM/OM
      </a>
      <a href="#"
        onclick="openMonitoringModal(); return false;"
        class="flex items-center px-3 py-2 rounded-md <?= $currentPage === 'monitoring' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600' ?>">
        <i class="fa fa-tv px-2"></i> Monitoring OM/IM
      </a>
      <?php if (in_array($_SESSION['dept'], ['QA','MIS'])): ?>
      <a href="index.php?page=system" 
        class="flex items-center px-3 py-2 rounded-md <?= $currentPage === 'system' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600' ?>">
        <i class="fa fa-gear px-2"></i> System
      </a>
    <?php endif; ?>
    </nav>
  </aside>
<?php endif; ?>

  <!-- MAIN CONTENT -->
  <div class="flex-1 flex flex-col">
    <?php if (isset($_SESSION['user_id'])): ?>
      <!-- Header -->
      <header class="flex justify-between items-center bg-red-600 shadow px-6 py-5">
        <h1 class="text-lg font-semibold text-white">
          Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
        </h1>
        <h1 class="text-white text-lg font-bold px-3 py-">
          DIGITAL OM/IM
        </h1>
        <div class="relative">
          <button id="profileBtn" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-red-800">
            <span class="font-medium text-white"><?= htmlspecialchars($_SESSION['username']) ?></span>
            <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
              <i class="fa fa-user text-gray-700"></i>
            </div>
          </button>
          <div id="profileMenu" class="hidden absolute right-0 mt-2 w-40 bg-white shadow-lg rounded-md border">
            <a href="actions/logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
          </div>
        </div>
      </header>
    <?php endif; ?>

    <!-- Dynamic Page Content -->
    <main class="flex-1 p-6">
      <?php
        switch ($currentPage) {
          case 'system':
            include 'system.php';
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
          case 'rules':  
            include 'rules.php';
            break;
          case 'main_dashboard':
            include 'main_dashboard.php';
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

<?php include 'partials/monitoring_modal.php'; ?>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if ($alert): ?>
<script>
Swal.fire({
    icon: "<?= $alert['type'] ?>",
    title: "<?= $alert['title'] ?>",
    text: "<?= $alert['message'] ?>",
    confirmButtonColor: "#d33"
}).then((result) => {
    <?php if (!empty($alert['redirect'])): ?>
        if (result.isConfirmed) {
            window.location.href = "<?= $alert['redirect'] ?>";
        }
    <?php endif; ?>
});
</script>
<?php endif; ?>

</body>
</html>

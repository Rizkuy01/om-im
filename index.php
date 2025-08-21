<?php
session_start();
require_once 'config.php';

$currentPage = $_GET['page'] ?? 'home';

// halaman yang bisa diakses tanpa login
$publicPages = ['check_model', 'monitoring'];

// kalau halaman bukan public dan belum login → redirect login
if (!in_array($currentPage, $publicPages) && !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// ======== CEK LOGIC MONITORING ========
$alert = null;
if ($currentPage === 'monitoring' && isset($_GET['npk'])) {
    $npk     = $_GET['npk'] ?? '';
    $machine = $_GET['machine'] ?? '';

    if ($npk) {
        // cek NPK di db lembur1.ct_users
        $stmt = $connUser->prepare("SELECT dept, full_name FROM ct_users WHERE npk = ?");
        $stmt->bind_param("s", $npk);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            $deptUser = strtolower(trim($user['dept']));

            // cek di db om_im.department
            $resDept   = $connIMOM->query("SELECT dept_name FROM department");
            $foundDept = null;
            while ($row = $resDept->fetch_assoc()) {
                if (strtolower(trim($row['dept_name'])) === $deptUser) {
                    $foundDept = $row['dept_name'];
                    break;
                }
            }

            if ($foundDept) {
                $alert = [
                    "type" => "success",
                    "title" => "Akses Diterima",
                    "message" => "Anda dari dept {$foundDept}. Mesin: {$machine}"
                ];
            } else {
                $alert = [
                    "type" => "error",
                    "title" => "Dept Tidak Valid",
                    "message" => "Dept '{$user['dept']}' tidak ada di om_im.department"
                ];
            }
        } else {
            $alert = [
                "type" => "error",
                "title" => "NPK Tidak Ditemukan",
                "message" => "NPK {$npk} tidak ada di database."
            ];
        }
    }
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
});
</script>
<?php endif; ?>

</body>
</html>

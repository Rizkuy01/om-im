<?php
require_once 'config.php';
require_once 'actions/check_monitoring.php';
session_start();

$error = '';
$alert = null;

// === LOGIN LOGIC ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['npk'], $_POST['password'], $_POST['captcha'])) {
    $npk      = trim($_POST['npk']);
    $password = trim($_POST['password']);
    $captcha  = trim($_POST['captcha']);

    // cek captcha
    if (!isset($_SESSION['captcha']) || strcasecmp($_SESSION['captcha'], $captcha) !== 0) {
        $error = 'Captcha salah!';
    } else {
        // get user
        $stmt = $connUser->prepare("SELECT * FROM ct_users WHERE npk = ? LIMIT 1");
        $stmt->bind_param("s", $npk);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['pwd'])) {
            // normalisasi
            $deptClean = ucwords(strtolower(trim($user['dept'])));

            // cek db om_im.department
            $stmtDept = $connIMOM->prepare("SELECT id, dept_name FROM department WHERE LOWER(dept_name) = LOWER(?) LIMIT 1");
            $stmtDept->bind_param("s", $deptClean);
            $stmtDept->execute();
            $resDept  = $stmtDept->get_result();
            $rowDept  = $resDept->fetch_assoc();
            $stmtDept->close();

            if ($rowDept) {
                $deptId   = $rowDept['id'];
                $deptName = $rowDept['dept_name'];

                $_SESSION['pending_user'] = [
                    'npk'      => $user['npk'],
                    'username' => $user['full_name'],
                    'dept'     => $deptName,
                    'dept_id'  => $deptId
                ];
                unset($_SESSION['captcha']);

                if ($deptName === 'QA' || $deptName === 'MIS' || stripos($deptName, 'Production') === 0) {
                  // generate OTP
                  $_SESSION['pending_user']['otp'] = rand(100000, 999999);

                  // mapping sessions
                  if (stripos($deptName, 'Production') === 0) {
                      $_SESSION['pending_user']['redirect_after_otp'] = "index.php?page=workstations&dept_id=" . $deptId;
                  } else {
                      $_SESSION['pending_user']['redirect_after_otp'] = "index.php?page=dashboard_home";
                  }

                  header("Location: verify_otp.php");
                  exit;
              } else {
                  $error = "Akses untuk departemen {$deptName} belum diatur.";
              }

            } else {
                $error = "Dept {$user['dept']} tidak ditemukan di database.";
            }
        } else {
            $error = 'NPK atau Password salah!';
        }
    }
}



// === MONITORING LOGIC ===
if (isset($_GET['page']) && $_GET['page'] === 'monitoring' && isset($_GET['npk'])) {
    $alert = checkMonitoringAccess($connUser, $connIMOM, $_GET['npk'], $_GET['machine'] ?? '');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - DIGITAL OM/IM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style> body { font-family: 'Inter', sans-serif; } </style>
</head>

<body class="relative min-h-screen flex items-center justify-center overflow-hidden">
  <!-- Background -->
  <div class="absolute inset-0 bg-cover bg-center brightness-50 z-[-1]" style="background-image: url('assets/bg.png');"></div>

  <div class="bg-white bg-opacity-80 p-10 rounded-xl shadow-lg max-w-md w-[90%] text-center z-10">
    <img src="assets/kyb.png" alt="KYB Logo" class="w-40 mx-auto mb-6">
    <h1 class="text-2xl font-bold text-black mb-1">LOGIN</h1>
    <h2 class="text-lg font-semibold text-black mb-8">DIGITAL OM/IM SYSTEM</h2>

    <!-- Error message -->
    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-sm">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- Form -->
    <form method="post">
      <input type="text" name="npk" placeholder="NPK" required autocomplete="off"
        class="w-full px-4 py-3 rounded-md border border-gray-300 text-base mb-4 focus:outline-none focus:ring-2 focus:ring-red-500">
      <input type="password" name="password" placeholder="Password" required
        class="w-full px-4 py-3 rounded-md border border-gray-300 text-base mb-4 focus:outline-none focus:ring-2 focus:ring-red-500">

      <!-- Captcha -->
      <div class="flex items-center justify-between mb-4">
        <img src="captcha.php" alt="Captcha" class="border rounded mr-3">
        <input type="text" name="captcha" placeholder="Masukkan kode Captcha" required
          class="flex-1 px-4 py-3 rounded-md border border-gray-300 text-base focus:outline-none focus:ring-2 focus:ring-red-500">
      </div>

      <button type="submit"
        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-md transition duration-300">
        LOGIN
      </button>
    </form>

    <!-- Tombol akses langsung -->
    <div class="grid grid-cols-2 gap-3 mt-3">
      <button type="button" onclick="window.location.href='index.php?page=check_model'"
        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-md transition duration-300">
        Check Model
      </button>
      <button type="button" onclick="openMonitoringModal()"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 rounded-md transition duration-300">
        Monitoring
      </button>
    </div>
  </div>

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

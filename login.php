<?php
require_once 'actions/login_handler.php';
require_once 'actions/check_monitoring.php';
$alert = null;

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
  <link rel="stylesheet" href="assets/css/tailwind.css">
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

    <!-- Separator -->
    <div class="flex items-center my-2">
      <div class="flex-grow border-t border-gray-500"></div>
      <span class="mx-2 text-gray-500 text-sm">atau</span>
      <div class="flex-grow border-t border-gray-500"></div>
    </div>

    <!-- Link Monitoring -->
    <p class="text-sm text-gray-700">
      <a href="javascript:void(0)" onclick="openMonitoringModal()" 
         class="text-gray-600 font-semibold hover:underline hover:text-red-600">
         Klik disini untuk akses Monitoring
      </a>
    </p>
  </div>

<?php include 'partials/monitoring_modal.php'; ?>
</body>
</html>

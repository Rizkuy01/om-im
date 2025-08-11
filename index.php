<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$currentPage = $_GET['page'] ?? 'home';
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

  <!-- Sidebar -->
  <aside class="w-64 bg-white shadow-md flex flex-col">
    <div class="px-6 py-5 border-b">
      <img src="assets/kyb.png" alt="Logo" class="w-28 mx-auto">
    </div>
    <nav class="flex-1 px-4 py-6 space-y-2">
      <a href="index.php?page=home" class="flex items-center px-3 py-2 rounded-md <?= $currentPage === 'home' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600' ?>">
        <i class="fa fa-gauge-high px-2"></i> Dashboard
      </a>
      <a href="index.php?page=check_model" class="flex items-center px-3 py-2 rounded-md <?= $currentPage === 'check_model' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600' ?>">
        <i class="fa fa-magnifying-glass px-2"></i> Check Model
      </a>
    </nav>
  </aside>

  <!-- Main Content -->
  <div class="flex-1 flex flex-col">
    <!-- Header -->
    <header class="flex justify-between items-center bg-white shadow px-6 py-5">
      <h1 class="text-lg font-semibold">Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></h1>
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

    <!-- Dynamic Page Content -->
    <main class="flex-1 p-6">
      <?php
        if ($currentPage === 'check_model') {
            include 'check_model.php';
        } else {
            include 'dashboard_home.php';
        }
      ?>
    </main>
  </div>

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
</body>
</html>

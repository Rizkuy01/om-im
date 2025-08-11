<?php
session_start();

// Proteksi login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Deteksi halaman aktif
$currentPage = basename($_SERVER['PHP_SELF']);
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
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>
<body class="flex h-screen bg-gray-100">

  <!-- Sidebar -->
  <aside class="w-64 bg-white shadow-md flex flex-col">
    <div class="px-6 py-5 border-b">
      <img src="assets/kyb.png" alt="Logo" class="w-28 mx-auto">
    </div>
    <nav class="flex-1 px-4 py-6 space-y-2">
      <a href="index.php" 
         class="flex items-center px-3 py-2 rounded-md 
         <?= $currentPage === 'index.php' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600' ?>">
        <span><i class="fa fa-gauge-high px-2"></i></span>
        Dashboard
      </a>
      <a href="check-model.php" 
         class="flex items-center px-3 py-2 rounded-md 
         <?= $currentPage === 'check-model.php' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600' ?>">
        <span><i class="fa fa-magnifying-glass px-2"></i></span>
        Check Model
      </a>
      <a href="menu2.php" 
         class="flex items-center px-3 py-2 rounded-md 
         <?= $currentPage === 'menu2.php' ? 'text-red-600 font-semibold' : 'text-gray-700 hover:text-red-600' ?>">
        <span><i class="fa fa-cogs px-2"></i></span>
        Menu 2
      </a>
    </nav>
  </aside>

  <!-- Main Content -->
  <div class="flex-1 flex flex-col">

    <!-- Header -->
    <header class="flex justify-between items-center bg-white shadow px-6 py-5">
      <h1 class="text-lg font-semibold">Welcome, <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></h1>
      
      <!-- Profile Dropdown -->
      <div class="relative">
        <!-- Button -->
        <button id="profileBtn" class="flex items-center gap-3 px-3 py-2 rounded hover:bg-gray-100">
          <span class="font-medium text-gray-700"><?= htmlspecialchars($_SESSION['username']) ?></span>
          <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center">
            <i class="fa fa-user text-gray-700"></i>
          </div>
        </button>
        <!-- Dropdown -->
        <div id="profileMenu" class="hidden absolute right-0 mt-2 w-40 bg-white shadow-lg rounded-md border">
          <a href="logout.php" class="block px-4 py-2 text-gray-700 hover:bg-gray-100">Logout</a>
        </div>
      </div>
    </header>

    <!-- Page Content -->
    <main class="flex-1 p-6">
    <!-- Judul di tengah -->
    <h2 class="text-3xl font-bold text-center mb-10 text-gray-800">2W / 4W</h2>

    <!-- Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Card 1 -->
        <div class="bg-white shadow-md hover:shadow-lg rounded-lg p-6 text-center transform hover:-translate-y-1 transition duration-300">
        <h3 class="text-xl font-semibold mb-4 text-gray-800">PRODUCTION A</h3>
        <a href="#" class="inline-block bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2 rounded-full transition duration-300">Open</a>
        </div>

        <!-- Card 2 -->
        <div class="bg-white shadow-md hover:shadow-lg rounded-lg p-6 text-center transform hover:-translate-y-1 transition duration-300">
        <h3 class="text-xl font-semibold mb-4 text-gray-800">PRODUCTION B</h3>
        <a href="#" class="inline-block bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2 rounded-full transition duration-300">Open</a>
        </div>

        <!-- Card 3 -->
        <div class="bg-white shadow-md hover:shadow-lg rounded-lg p-6 text-center transform hover:-translate-y-1 transition duration-300">
        <h3 class="text-xl font-semibold mb-4 text-gray-800">PRODUCTION C</h3>
        <a href="#" class="inline-block bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2 rounded-full transition duration-300">Open</a>
        </div>
    </div>
    </main>


  </div>

    <script>
        const profileBtn = document.getElementById('profileBtn');
        const profileMenu = document.getElementById('profileMenu');

        profileBtn.addEventListener('click', () => {
        profileMenu.classList.toggle('hidden');
        });

        // Tutup dropdown jika klik di luar
        document.addEventListener('click', (e) => {
        if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
            profileMenu.classList.add('hidden');
        }
        });
    </script>

</body>
</html>

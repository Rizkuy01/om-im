<?php
require 'config.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $npk = trim($_POST['npk'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $stmt = $connUser->prepare("
    SELECT * FROM ct_users 
    WHERE npk = ? 
      AND dept IN ('QA') 
    LIMIT 1
    ");

    $stmt->bind_param("s", $npk);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        if (password_verify($password, $user['pwd'])) {
            $_SESSION['user_id'] = $user['npk'];
            $_SESSION['username'] = $user['full_name'];
            $_SESSION['dept'] = $user['dept'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'NPK tidak ditemukan atau Anda bukan dari departemen QA!';
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - DIGITAL OM/IM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Inter', sans-serif;
    }
  </style>
</head>
<body class="relative min-h-screen flex items-center justify-center overflow-hidden">

  <!-- Background image -->
  <div class="absolute inset-0 bg-cover bg-center brightness-50 z-[-1]" style="background-image: url('assets/bg.png');"></div>

  <!-- Container -->
  <div class="bg-white bg-opacity-80 p-10 rounded-xl shadow-lg max-w-md w-[90%] text-center z-10">
    
    <!-- Logo -->
    <img src="assets/kyb.png" alt="KYB Logo" class="w-40 mx-auto mb-6">

    <!-- Heading -->
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
      <input 
        type="text" 
        name="npk" 
        placeholder="NPK" 
        required 
        autocomplete="off"
        class="w-full px-4 py-3 rounded-md border border-gray-300 text-base mb-4 focus:outline-none focus:ring-2 focus:ring-red-500"
      >
      <input 
        type="password" 
        name="password" 
        placeholder="Password" 
        required
        class="w-full px-4 py-3 rounded-md border border-gray-300 text-base mb-6 focus:outline-none focus:ring-2 focus:ring-red-500"
      >
      <button 
        type="submit"
        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-md transition duration-300"
      >
        LOGIN
      </button>
    </form>
  </div>

</body>
</html>

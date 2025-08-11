<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DIGITAL OM/IM</title>
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
    <h1 class="text-2xl font-bold text-black mb-1">DIGITAL</h1>
    <h2 class="text-lg font-bold text-black mb-8">INSPECTION AND OPERATION MANUAL</h2>

    <!-- Form -->
    <form id="manualForm" action="manual.php" method="get">
      <input 
        type="text" 
        name="id" 
        id="modelInput" 
        placeholder="Scan/Input Model Number (Model_no)" 
        required 
        autocomplete="off"
        class="w-full px-4 py-3 rounded-md border border-gray-300 text-base mb-6 focus:outline-none focus:ring-2 focus:ring-red-500"
      >
      <button 
        type="submit"
        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-md transition duration-300"
      >
        OPEN OM/IM
      </button>
    </form>
  </div>

  <!-- Script -->
  <script>
    document.getElementById('modelInput').addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        if (this.value.trim() !== '') {
          document.getElementById('manualForm').submit();
        }
      }
    });
  </script>
</body>
</html>

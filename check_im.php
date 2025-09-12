<?php
require_once 'config.php';
session_start();

$errorMessage = null;

// Ambil IM terbaru
$stmt = $connIMOM->prepare("SELECT * FROM data_im ORDER BY id DESC LIMIT 1");
$stmt->execute();
$lastIM = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$lastIM) {
    $errorMessage = "Belum ada data IM terbaru.";
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Check Model - Latest IM</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<?php if ($errorMessage): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Data Tidak Ada',
    text: <?= json_encode($errorMessage) ?>,
    confirmButtonColor: '#d33'
}).then(() => {
    window.location.href = "login.php";
});
</script>
<?php else: ?>

  <!-- HEADER -->
  <div class="bg-green-600 text-white px-6 py-4 shadow-md text-center">
    <h1 class="text-2xl font-bold">Check Model - IM Latest</h1>
    <p class="mt-1 text-sm">
      <strong>Part:</strong> <?= htmlspecialchars($lastIM['part_number']) ?>
    </p>
  </div>

  <!-- CONTENT -->
  <div class="flex-1 p-6">
    <div class="bg-white rounded-lg shadow-md p-4">
      <div class="flex justify-between items-center mb-3">
        <h2 class="text-lg font-semibold text-blue-700">
          IM File (Latest) - <span id="clockIM"></span>
        </h2>
        <a href="login.php"
           class="bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold px-4 py-2 rounded shadow">
           ← Kembali
        </a>
      </div>
      <iframe src="<?= htmlspecialchars($lastIM['path_name'].$lastIM['file_name']) ?>"
              class="w-full h-[90vh] border rounded" frameborder="0"></iframe>
    </div>
  </div>

  <script>
  function updateClock() {
      const now = new Date();
      document.getElementById("clockIM").textContent = now.toLocaleTimeString('id-ID');
  }
  setInterval(updateClock, 1000);
  updateClock();
  </script>

<?php endif; ?>
</body>
</html>

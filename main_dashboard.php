<?php
require_once 'config.php';

$dept    = $_SESSION['dept'] ?? '';
$dept_id = $_SESSION['dept_id'] ?? 0;
$npk     = $_SESSION['npk'] ?? '';
?>

<div class="bg-gray-50 shadow-md rounded-lg overflow-hidden">
  <!-- HEADER -->
  <div class="bg-red-600 text-white px-6 py-3">
    <h1 class="text-xl font-bold">Dashboard Utama</h1>
  </div>

  <!-- BODY -->
  <div class="p-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- TABEL -->
      <?php include __DIR__."/partials/table_lastest_file.php"; ?>

      <!-- CHART + LATEST FILES -->
      <div class="flex flex-col gap-6">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <?php include __DIR__."/partials/chart_distribution.php"; ?>
        <?php include __DIR__."/partials/button_lastest_file.php"; ?>
      </div>
    </div>
  </div>
</div>

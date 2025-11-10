<?php
  $modelNo = $_GET['model_no'] ?? 'MODEL NOT FOUND';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Checksheet</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/tailwind.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"/>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <style>
        /* Light Mode Overrides for DataTables */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
        background-color: white !important;
        color: #1f2937 !important;
        border: 1px solid #d1d5db !important;
        border-radius: 0.375rem !important;
        margin: 0 0.25rem;
        padding: 0.25rem 0.75rem;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background-color: #f3f4f6 !important;
        color: #111827 !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background-color: #B91C1C !important;
        color: white !important;
        border-color: #B91C1C !important;
        font-weight: bold;
        }

        /* Make background of table white */
        table.dataTable {
        background-color: white !important;
        }

        table.dataTable th,
        table.dataTable td {
        background-color: white !important;
        color: #111827 !important;
        }
    </style>
</head>

<body class="relative min-h-screen flex flex-col items-center justify-start py-10 bg-gray-100 overflow-auto">
    <!-- Header -->
    <div class="p-6 text-center w-[95%] max-w-5xl mb-6 z-10">
        <img src="assets/kyb.png" alt="KYB Logo" class="w-32 mx-auto mb-4">
        <h1 class="text-2xl font-bold text-black">PT. KAYABA INDONESIA</h1>
        <h2 class="text-lg font-semibold text-black">QUALITY ASSURANCE - 4W DEPT</h2>
    </div>

    <!-- No Model -->
    <div class="w-[95%] max-w-7xl flex items-center justify-between mb-4 z-10">
        <button onclick="window.location.href='index.php'" class="bg-red-600 hover:bg-red-500 text-white font-semibold px-4 py-2 rounded shadow flex items-center gap-2">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </button>
        <h2 class="text-xl font-semibold text-gray-800 text-center w-auto -ml-16">
            Model No: <?= htmlspecialchars($modelNo . 'IM') ?>
        </h2>
        <div class="w-28"></div>
    </div>


  <!-- Table Container -->
  <div class="bg-white bg-opacity-90 p-4 rounded-xl shadow-lg overflow-auto w-[95%] max-w-7xl z-10">
    <table id="checksheetTable" class="min-w-full table-auto border border-gray-400 text-sm text-center">
      <thead class="bg-gray-200 text-gray-800 font-semibold">
        <tr>
          <th class="border border-gray-400 px-2 py-1">Tanggal</th>
          <th class="border border-gray-400 px-2 py-1">Shift</th>
          <th class="border border-gray-400 px-2 py-1">Model</th>
          <th class="border border-gray-400 px-2 py-1">KYB No</th>
          <th class="border border-gray-400 px-2 py-1">P/Rod</th>
          <th class="border border-gray-400 px-2 py-1">Lot No</th>
          <th class="border border-gray-400 px-2 py-1">SPB No</th>
          <th class="border border-gray-400 px-2 py-1">Item 1</th>
          <th class="border border-gray-400 px-2 py-1">Item 2</th>
          <th class="border border-gray-400 px-2 py-1">Result</th>
        </tr>
      </thead>
      <tbody>
        <tr class="hover:bg-gray-100">
          <td class="border border-gray-400 px-2 py-1">2025-08-08</td>
          <td class="border border-gray-400 px-2 py-1">1</td>
          <td class="border border-gray-400 px-2 py-1">WLC-001</td>
          <td class="border border-gray-400 px-2 py-1">KYB-100</td>
          <td class="border border-gray-400 px-2 py-1">PR-01</td>
          <td class="border border-gray-400 px-2 py-1">LOT-01</td>
          <td class="border border-gray-400 px-2 py-1">SPB-01</td>
          <td class="border border-gray-400 px-2 py-1">OK</td>
          <td class="border border-gray-400 px-2 py-1">OK</td>
          <td class="border border-gray-400 px-2 py-1">PASS</td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- DataTables Init -->
  <script>
    $(document).ready(function () {
      $('#checksheetTable').DataTable({
        responsive: true,
        pageLength: 10
      });
    });
  </script>

</body>
</html>

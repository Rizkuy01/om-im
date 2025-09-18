<?php
require_once 'config.php';

$dept = $_SESSION['dept'] ?? '';
$dept_id = $_SESSION['dept_id'] ?? 0;
$npk  = $_SESSION['npk'] ?? '';

// --- Data untuk Pie Chart OM ---
$sqlOM = "
    SELECT d.dept_name, COUNT(o.id) AS total_om
    FROM data_om o
    JOIN sub_workstations s ON o.sub_workstation_id = s.id
    JOIN workstations w ON s.workstation_id = w.id
    JOIN department d ON w.dept_id = d.id
    GROUP BY d.dept_name
    ORDER BY total_om DESC
";
$resOM = $connIMOM->query($sqlOM);

$labelsOM = [];
$dataOM   = [];
if ($resOM) {
    while ($r = $resOM->fetch_assoc()) {
        $labelsOM[] = $r['dept_name'];
        $dataOM[]   = (int)$r['total_om'];
    }
}

// --- Data untuk Pie Chart IM ---
$sqlIM = "
    SELECT d.dept_name, COUNT(i.id) AS total_im
    FROM data_im i
    JOIN sub_workstations s ON i.sub_workstation_id = s.id
    JOIN workstations w ON s.workstation_id = w.id
    JOIN department d ON w.dept_id = d.id
    GROUP BY d.dept_name
    ORDER BY total_im DESC
";
$resIM = $connIMOM->query($sqlIM);

$labelsIM = [];
$dataIM   = [];
if ($resIM) {
    while ($r = $resIM->fetch_assoc()) {
        $labelsIM[] = $r['dept_name'];
        $dataIM[]   = (int)$r['total_im'];
    }
}
// --- Data untuk tabel (15 data terakhir) ---
$listQ = "
    SELECT t.type, t.process_id, t.part_number, t.id, s.name AS sub_name
    FROM (
        SELECT 'OM' AS type, process_id, part_number, id, sub_workstation_id
        FROM data_om
        UNION ALL
        SELECT 'IM' AS type, process_id, part_number, id, sub_workstation_id
        FROM data_im
    ) t
    LEFT JOIN sub_workstations s ON t.sub_workstation_id = s.id
    ORDER BY t.id DESC
    LIMIT 15
";
$listRes = $connIMOM->query($listQ);

// --- Ambil record terbaru OM ---
if (in_array($dept, ['QA','MIS'])) {
    $latestOM = $connIMOM->query("
        SELECT o.id, o.process_id, w.id AS ws_id
        FROM data_om o
        JOIN sub_workstations s ON o.sub_workstation_id = s.id
        JOIN workstations w ON s.workstation_id = w.id
        ORDER BY o.id DESC
        LIMIT 1
    ")->fetch_assoc();
} else {
    $latestOM = $connIMOM->query("
        SELECT o.id, o.process_id, w.id AS ws_id
        FROM data_om o
        JOIN sub_workstations s ON o.sub_workstation_id = s.id
        JOIN workstations w ON s.workstation_id = w.id
        WHERE w.dept_id = ".(int)$dept_id."
        ORDER BY o.id DESC
        LIMIT 1
    ")->fetch_assoc();
}

// --- Ambil record terbaru IM ---
if (in_array($dept, ['QA','MIS'])) {
    $latestIM = $connIMOM->query("
        SELECT i.id, i.process_id, w.id AS ws_id
        FROM data_im i
        JOIN sub_workstations s ON i.sub_workstation_id = s.id
        JOIN workstations w ON s.workstation_id = w.id
        ORDER BY i.id DESC
        LIMIT 1
    ")->fetch_assoc();
} else {
    $latestIM = $connIMOM->query("
        SELECT i.id, i.process_id, w.id AS ws_id
        FROM data_im i
        JOIN sub_workstations s ON i.sub_workstation_id = s.id
        JOIN workstations w ON s.workstation_id = w.id
        WHERE w.dept_id = ".(int)$dept_id."
        ORDER BY i.id DESC
        LIMIT 1
    ")->fetch_assoc();
}
?>

<!-- WRAPPER -->
<div class="bg-gray-50 shadow-md rounded-lg overflow-hidden">
  <!-- HEADER MERAH -->
  <div class="bg-red-600 text-white px-6 py-3">
    <h1 class="text-xl font-bold">Dashboard Utama</h1>
  </div>

  <!-- BODY -->
  <div class="p-6">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- TABEL -->
      <div class="bg-white rounded-lg shadow-inner p-4 overflow-hidden border-red-200 border">
        <h2 class="text-lg font-semibold mb-3 text-red-600">Latest OM/IM Files</h2>
        <div class="overflow-y-hidden h-80 relative" id="scrollContainer">
          <table class="min-w-full text-sm text-left border rounded">
            <thead class="bg-gray-200 sticky top-0">
              <tr>
                <th class="px-3 py-2">Type</th>
                <th class="px-3 py-2">Process</th>
                <th class="px-3 py-2">Part Number</th>
                <th class="px-3 py-2">Sub WS</th>
              </tr>
            </thead>
            <tbody id="omimTable">
              <?php if ($listRes && $listRes->num_rows > 0): ?>
                <?php while ($row = $listRes->fetch_assoc()): ?>
                  <?php
                    $procName = '-';
                    if (!empty($row['process_id'])) {
                      $p = $connIMOM->query("SELECT process_name FROM process WHERE id=".(int)$row['process_id'])->fetch_assoc();
                      $procName = $p['process_name'] ?? '-';
                    }
                  ?>
                  <tr class="border-b hover:bg-white">
                    <td class="px-3 py-2 font-semibold <?= $row['type']==='OM'?'text-green-600':'text-blue-600' ?>">
                      <?= htmlspecialchars($row['type']) ?>
                    </td>
                    <td class="px-3 py-2"><?= htmlspecialchars($procName) ?></td>
                    <td class="px-3 py-2"><?= htmlspecialchars($row['part_number']) ?></td>
                    <td class="px-3 py-2"><?= htmlspecialchars($row['sub_name'] ?? '-') ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="px-3 py-4 text-center text-gray-500">Tidak ada data</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- CHART + LATEST FILES -->
      <div class="flex flex-col gap-6">
        
        <!-- PIE CHART CARD (OM & IM sebelahan) -->
        <div class="bg-white rounded-lg shadow-inner p-4 border-red-200 border">
        <h2 class="text-lg font-semibold mb-4 text-red-600 text-center">
            Distribusi OM & IM per Departemen
        </h2>
        
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- OM -->
                <div class="flex flex-col items-center">
                    <h3 class="font-semibold mb-2 text-green-600">OM</h3>
                    <div class="w-56 h-56">
                        <canvas id="omPieChart"></canvas>
                    </div>
                </div>

                <!-- IM -->
                <div class="flex flex-col items-center">
                    <h3 class="font-semibold mb-2 text-blue-600">IM</h3>
                    <div class="w-56 h-56">
                        <canvas id="imPieChart"></canvas>
                    </div>
                </div>
            </div>
        </div>


        <!-- LATEST FILES CARD -->
        <div class="bg-white rounded-lg shadow-inner p-4 text-center border-red-200 border">
          <h3 class="font-semibold mb-3 text-red-600">Latest Files</h3>
          <div class="flex justify-center gap-4">
            <?php if ($latestOM): ?>
              <a href="monitoring_detail.php?npk=<?= urlencode($npk) ?>&machine=<?= $latestOM['ws_id'] ?>&process_id=<?= $latestOM['process_id'] ?>"
                 class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow">
                Latest OM
              </a>
            <?php endif; ?>
            <?php if ($latestIM): ?>
              <a href="monitoring_detail.php?npk=<?= urlencode($npk) ?>&machine=<?= $latestIM['ws_id'] ?>&process_id=<?= $latestIM['process_id'] ?>"
                 class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow">
                Latest IM
              </a>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctxOM = document.getElementById('omPieChart').getContext('2d');
new Chart(ctxOM, {
  type: 'pie',
  data: {
    labels: <?= json_encode($labelsOM) ?>,
    datasets: [{
      data: <?= json_encode($dataOM) ?>,
      backgroundColor: ['#ff1616','#116cff','#00ffaa','#ffa304','#8b5cf6','#ff198c','#14b8a6','#64748b']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' }
    }
  }
});

const ctxIM = document.getElementById('imPieChart').getContext('2d');
new Chart(ctxIM, {
  type: 'pie',
  data: {
    labels: <?= json_encode($labelsIM) ?>,
    datasets: [{
      data: <?= json_encode($dataIM) ?>,
      backgroundColor: ['#ff1616','#116cff','#00ffaa','#ffa304','#8b5cf6','#ff198c','#14b8a6','#64748b']
    }]
  },
  options: {
    responsive: true,
    plugins: {
      legend: { position: 'bottom' }
    }
  }
});

// auto scroll tabel
const scrollContainer = document.getElementById('scrollContainer');
function autoScroll() {
  if (scrollContainer.scrollTop + scrollContainer.clientHeight >= scrollContainer.scrollHeight) {
    fetch('api/get_file.php')
      .then(res => res.text())
      .then(html => {
        document.getElementById('omimTable').innerHTML = html;
        scrollContainer.scrollTop = 0;
      });
  } else {
    scrollContainer.scrollTop += 1;
  }
}
setInterval(autoScroll, 100);
</script>

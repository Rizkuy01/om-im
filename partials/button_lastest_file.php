<?php
// --- Ambil record terbaru OM ---
if (in_array($dept, ['QA','MIS'])) {
    $latestOM = $connIMOM->query("
        SELECT o.id, o.process_id, s.id AS sub_id
        FROM data_om o
        JOIN sub_workstations s ON o.sub_workstation_id = s.id
        ORDER BY o.id DESC
        LIMIT 1
    ")->fetch_assoc();
} else {
    $latestOM = $connIMOM->query("
        SELECT o.id, o.process_id, s.id AS sub_id
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
        SELECT i.id, i.process_id, s.id AS sub_id
        FROM data_im i
        JOIN sub_workstations s ON i.sub_workstation_id = s.id
        ORDER BY i.id DESC
        LIMIT 1
    ")->fetch_assoc();
} else {
    $latestIM = $connIMOM->query("
        SELECT i.id, i.process_id, s.id AS sub_id
        FROM data_im i
        JOIN sub_workstations s ON i.sub_workstation_id = s.id
        JOIN workstations w ON s.workstation_id = w.id
        WHERE w.dept_id = ".(int)$dept_id."
        ORDER BY i.id DESC
        LIMIT 1
    ")->fetch_assoc();
}
?>

<!-- LATEST FILES CARD -->
<!-- LATEST FILES CARD -->
<div class="bg-white rounded-lg shadow-inner p-4 text-center border-red-200 border">
  <h3 class="font-semibold mb-3 text-red-600">Latest Files</h3>
  <div class="flex justify-center gap-4">
    <?php if ($latestOM): ?>
      <a href="monitoring_detail.php?npk=<?= urlencode($npk) ?>&machine=<?= $latestOM['sub_id'] ?>&process_id=<?= $latestOM['process_id'] ?>&type=OM"
         class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded shadow">
        <i class="fa-solid fa-file"></i> OM
      </a>
    <?php else: ?>
      <div class="bg-red-100 text-red-700 px-4 py-2 rounded shadow text-sm font-semibold w-full">
        Departemen ini belum memiliki file OM
      </div>
    <?php endif; ?>

    <?php if ($latestIM): ?>
      <a href="monitoring_detail.php?npk=<?= urlencode($npk) ?>&machine=<?= $latestIM['sub_id'] ?>&process_id=<?= $latestIM['process_id'] ?>&type=IM"
         class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded shadow">
        <i class="fa-solid fa-file"></i> IM
      </a>
    <?php else: ?>
      <div class="bg-red-100 text-red-700 px-4 py-2 rounded shadow text-sm font-semibold w-full">
        Departemen ini belum memiliki file IM
      </div>
    <?php endif; ?>
  </div>
</div>


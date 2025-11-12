<?php
// --- Ambil record terbaru baik dari OM atau IM ---
if (in_array($dept, ['QA','MIS'])) {
    $latestFile = $connIMOM->query("
        SELECT f.process_id, s.id AS sub_id, p.process_name
        FROM (
            SELECT id, process_id, sub_workstation_id, 'OM' as source FROM data_om
            UNION ALL
            SELECT id, process_id, sub_workstation_id, 'IM' as source FROM data_im
        ) f
        JOIN sub_workstations s ON f.sub_workstation_id = s.id
        JOIN process p ON f.process_id = p.id
        ORDER BY f.id DESC
        LIMIT 1
    ")->fetch_assoc();
} else {
    $latestFile = $connIMOM->query("
        SELECT f.process_id, s.id AS sub_id, p.process_name
        FROM (
            SELECT o.id, o.process_id, o.sub_workstation_id, 'OM' as source 
            FROM data_om o
            JOIN sub_workstations s ON o.sub_workstation_id = s.id
            JOIN workstations w ON s.workstation_id = w.id
            WHERE w.dept_id = ".(int)$dept_id."
            UNION ALL
            SELECT i.id, i.process_id, i.sub_workstation_id, 'IM' as source 
            FROM data_im i
            JOIN sub_workstations s ON i.sub_workstation_id = s.id
            JOIN workstations w ON s.workstation_id = w.id
            WHERE w.dept_id = ".(int)$dept_id."
        ) f
        JOIN sub_workstations s ON f.sub_workstation_id = s.id
        JOIN process p ON f.process_id = p.id
        ORDER BY f.id DESC
        LIMIT 1
    ")->fetch_assoc();
}
?>

<!-- LATEST FILES CARD -->
<div class="bg-white rounded-lg shadow-inner p-4 text-center border-red-200 border">
  <h3 class="font-semibold mb-3 text-red-600">Latest File</h3>
  <div class="flex justify-center">
    <?php if ($latestFile): ?>
      <a href="monitoring_detail.php?npk=<?= urlencode($npk) ?>&machine=<?= $latestFile['sub_id'] ?>&process_id=<?= $latestFile['process_id'] ?>"
        class="relative bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded font-semibold w-11/12 text-center
                transition-all duration-300 shadow-md hover:shadow-[0_0_20px_rgba(244,64,54,0.7)]
                hover:animate-pulse">
        <i class="fa-solid fa-file"></i> <?= htmlspecialchars($latestFile['process_name'] ?: '(Tanpa Process)') ?>
      </a>
    <?php else: ?>
      <div class="bg-red-100 text-red-700 px-4 py-2 rounded shadow text-sm font-semibold w-full">
        Departemen ini belum memiliki file OM/IM
      </div>
    <?php endif; ?>
  </div>
</div>

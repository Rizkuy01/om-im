<?php
require_once 'config.php';

$workstation_id = isset($_GET['workstation_id']) ? (int) $_GET['workstation_id'] : 0;
$dept_id = $_GET['dept_id'] ?? null;

// Ambil nama workstation
$stmt = $connIMOM->prepare("SELECT name FROM workstations WHERE id = ?");
$stmt->bind_param("i", $workstation_id);
$stmt->execute();
$result = $stmt->get_result();
$workstation = $result->fetch_assoc();
$stmt->close();

// Ambil data sub_workstations
$stmt = $connIMOM->prepare("SELECT * FROM sub_workstations WHERE workstation_id = ?");
$stmt->bind_param("i", $workstation_id);
$stmt->execute();
$subs = $stmt->get_result();
?>

<h2 class="text-3xl font-bold text-gray-800 mb-6 text-center">
    <?= htmlspecialchars($workstation['name'] ?? 'Sub Workstations') ?>
</h2>

<!-- CARD VIEW -->
<div class="py-8 grid grid-cols-1 md:grid-cols-3 gap-6">
    <?php if ($subs->num_rows > 0): ?>
        <?php while ($row = $subs->fetch_assoc()): ?>
            <div class="flex items-center justify-between bg-gradient-to-br from-white to-gray-50 shadow-md hover:shadow-lg rounded-lg p-4 border border-gray-100 transform hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-center gap-3">
                    <div class="bg-red-100 text-red-600 p-3 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-industry text-lg"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">
                        <?= htmlspecialchars($row['name']) ?>
                    </h3>
                </div>
                <a href="index.php?page=sub_workstations_table&sub_ws_id=<?= $row['id'] ?>&workstation_id=<?= $workstation_id ?>&dept_id=<?= $dept_id ?>"
                   class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-full shadow-md transition-all duration-300">
                    Pilih
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="col-span-3 text-center text-gray-500">Belum ada sub-workstation untuk workstation ini.</p>
    <?php endif; ?>
</div>

<!-- Tombol kembali -->
<div class="flex justify-end mt-4">
    <a href="index.php?page=workstations&dept_id=<?= $dept_id ?>"
       class="bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold px-3 py-1 rounded shadow transition-all duration-300">
       ← Kembali
    </a>
</div>

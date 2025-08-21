<?php
require_once 'config.php';

$workstation_id = isset($_GET['workstation_id']) ? (int) $_GET['workstation_id'] : 0;
$dept_id        = $_GET['dept_id'] ?? null;

// Ambil nama department
$dept = $connIMOM->query("SELECT * FROM department WHERE id = $dept_id")->fetch_assoc();

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
$stmt->close();
?>

<!-- Container Utama -->
<div class="bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden max-w-7xl mx-auto">

    <!-- Header Merah + Breadcrumb -->
    <div class="bg-red-600 px-6 py-2 shadow-md border-b border-red-700">
        <nav class="flex items-center space-x-2 text-xs mb-2">
            <a href="index.php?page=dashboard_home" class="text-white hover:underline font-medium">Home</a>
            <span class="text-red-200">/</span>
            <a href="index.php?page=workstations&dept_id=<?= $dept_id ?>" class="text-white hover:underline font-medium">
                <?= htmlspecialchars($dept['dept_name'] ?? 'Departemen') ?>
            </a>
            <span class="text-red-200">/</span>
            <span class="text-white font-semibold"><?= htmlspecialchars($workstation['name'] ?? 'Workstation') ?></span>
        </nav>

        <h2 class="text-white text-xl font-bold tracking-wide">
            Data Mapping Sub Workstations
        </h2>
    </div>

    <!-- Isi Card Sub Workstations -->
    <div class="p-6 bg-gray-50">
        <?php if ($subs->num_rows > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($row = $subs->fetch_assoc()): ?>
                    <div class="flex flex-col justify-between bg-white shadow-sm rounded-lg p-4 border border-gray-100 transition-all duration-300 hover:shadow-[0_0_15px_rgba(220,38,38,0.6)]">
                        <!-- Nama Sub Workstation -->
                        <h3 class="text-base text-center font-semibold text-gray-800 mb-3">
                            <?= htmlspecialchars($row['name']) ?>
                        </h3>

                        <!-- Dua Button: IM & OM -->
                        <div class="flex gap-2">
                            <a href="index.php?page=detail_sub_workstations&type=IM&workstation_id=<?= $workstation_id ?>&dept_id=<?= $dept_id ?>&sub_id=<?= $row['id'] ?>" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-3 py-2 rounded shadow-md text-center transition-all duration-300">
                                IM
                            </a>
                            <a href="index.php?page=detail_sub_workstations&type=OM&workstation_id=<?= $workstation_id ?>&dept_id=<?= $dept_id ?>&sub_id=<?= $row['id'] ?>" class="flex-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium px-3 py-2 rounded shadow-md text-center transition-all duration-300">
                                OM
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-500">Belum ada sub workstation untuk workstation ini.</p>
        <?php endif; ?>

        <!-- Tombol kembali -->
        <div class="flex justify-end mt-6">
            <a href="index.php?page=workstations&dept_id=<?= $dept_id ?>" class="bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold px-4 py-2 rounded shadow transition-all duration-300">
                ← Kembali
            </a>
        </div>
    </div>
</div>

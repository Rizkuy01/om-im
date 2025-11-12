<?php
require_once 'config.php';

$dept_id = isset($_GET['dept_id']) ? intval($_GET['dept_id']) : 0;

$dept = $connIMOM->query("SELECT * FROM department WHERE id = $dept_id")->fetch_assoc();
$result = $connIMOM->query("SELECT * FROM workstations WHERE dept_id = $dept_id");

// logic back button
$backLink = "index.php?page=dashboard_home";
if (isset($_SESSION['dept']) && !in_array($_SESSION['dept'], ['QA','MIS'])) {
    $backLink = "index.php?page=workstations&dept_id=" . intval($_SESSION['dept_id']);
}

// cek apakah user production
$isProduction = (isset($_SESSION['dept']) && stripos($_SESSION['dept'], 'PRODUCTION') !== false);
?>

<!-- Container -->
<div class="bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden max-w-7xl mx-auto">
    <div class="bg-red-600 px-6 py-2 shadow-md border-b border-red-700">
        <!-- Breadcrumb -->
        <nav class="flex items-center space-x-2 text-xs mb-2">
            <a class="text-white hover:underline font-medium">Home</a>
            <span class="text-red-200">/</span>
            <span class="text-white font-medium"><?= htmlspecialchars($dept['dept_name'] ?? 'Departemen') ?></span>
            <span class="text-red-200">/</span>
            <span class="text-white font-semibold">Workstations</span>
        </nav>

        <!-- Title -->
        <h2 class="text-white text-xl font-bold tracking-wide">
            Data Mapping Workstations
        </h2>
    </div>

    <!-- Card Workstations -->
    <div class="p-6 bg-gray-50">
        <?php if ($result->num_rows > 0): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while($ws = $result->fetch_assoc()): ?>
                    <?php
                    // hitung jumlah sub_workstations
                    $subCount = $connIMOM->query("SELECT COUNT(*) as jml FROM sub_workstations WHERE workstation_id = {$ws['id']}")->fetch_assoc()['jml'];
                    $sub_id = null;
                    if ($subCount == 1) {
                        $sub = $connIMOM->query("SELECT id FROM sub_workstations WHERE workstation_id = {$ws['id']}")->fetch_assoc();
                        $sub_id = $sub['id'];
                    }

                    // tentukan link tujuan
                    if ($isProduction && $subCount == 1) {
                        $targetLink = "index.php?page=detail_sub_workstations&type=OM&workstation_id={$ws['id']}&dept_id={$dept_id}&sub_id={$sub_id}";
                    } else {
                        $targetLink = "index.php?page=sub_workstations&workstation_id={$ws['id']}&dept_id={$dept_id}";
                    }
                    ?>
                    
                    <div 
                        onclick="window.location.href='<?= $targetLink ?>'" 
                        class="cursor-pointer bg-white shadow-sm hover:shadow-md rounded-lg p-4 border border-gray-100 transition-all duration-300 hover:-translate-y-1 flex items-center justify-between">
                        
                        <!-- Workstation -->
                        <div class="flex items-center gap-3"> 
                            <div class="bg-blue-100 text-blue-600 p-3 rounded-full flex items-center justify-center"> 
                                <i class="fa-solid fa-cogs text-lg"></i> 
                            </div> 
                            <h3 class="text-lg font-semibold text-gray-800"> 
                                <?= htmlspecialchars($ws['name']) ?> 
                            </h3> 
                        </div>

                        <!-- Button -->
                        <span class="bg-red-600 text-white text-xs font-medium px-3 py-1.5 rounded-full shadow">
                            Pilih
                        </span>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-500">Belum ada workstation untuk departemen ini.</p>
        <?php endif; ?>

        <!-- Prev Button -->
        <div class="flex justify-end mt-6">
            <a href="<?= $backLink ?>" 
               class="bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold px-4 py-2 rounded shadow transition-all duration-300">
                ← Kembali
            </a>
        </div>
    </div>
</div>

<?php
require_once 'config.php';

$dept_id = isset($_GET['dept_id']) ? intval($_GET['dept_id']) : 0;

$dept = $connIMOM->query("SELECT * FROM department WHERE id = $dept_id")->fetch_assoc();
$result = $connIMOM->query("SELECT * FROM workstations WHERE dept_id = $dept_id");
?>

<h2 class="text-4xl text-center font-bold text-gray-800 tracking-wide">
    Work Center's
</h2>
<h4 class="text-lg text-center font-bold mb-6 text-gray-800 tracking-wide">
    <?= htmlspecialchars($dept['dept_name'] ?? 'Workstations') ?>
</h4>

<div class="py-8 grid grid-cols-1 md:grid-cols-3 gap-6">
    <?php if ($result->num_rows > 0): ?>
        <?php while($ws = $result->fetch_assoc()): ?>
            <div class="flex items-center justify-between bg-white shadow-md hover:shadow-lg rounded-lg p-4 border border-gray-100 transform hover:-translate-y-1 transition-all duration-300">
                
                <!-- Icon + Nama -->
                <div class="flex items-center gap-3">
                    <div class="bg-blue-100 text-blue-600 p-3 rounded-full flex items-center justify-center">
                        <i class="fa-solid fa-cogs text-lg"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">
                        <?= htmlspecialchars($ws['name']) ?>
                    </h3>
                </div>

                <!-- Tombol Pilih -->
                <a href="index.php?page=sub_workstations&workstation_id=<?= $ws['id'] ?>&dept_id=<?= $dept_id ?>" 
                   class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-full shadow-md transition-all duration-300">
                    Pilih
                </a>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="col-span-3 text-center text-gray-500">Belum ada workstation untuk departemen ini.</p>
    <?php endif; ?>

    <!-- Tombol kembali -->
    <div class="col-span-1 md:col-span-3 flex justify-end mt-4">
        <a href="index.php?page=dashboard_home" 
           class="bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold px-3 py-1 rounded shadow transition-all duration-300">
            ← Kembali
        </a>
    </div>
</div>

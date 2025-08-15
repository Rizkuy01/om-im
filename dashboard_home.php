<?php
require_once 'config.php';
$result = $connIMOM->query("SELECT * FROM department");
?>

<h2 class="text-4xl text-center font-bold mb-6 text-gray-800 tracking-wide">DEPARTEMENT</h2>

<div class="py-8 grid grid-cols-1 md:grid-cols-3 gap-6">
    <?php while($dept = $result->fetch_assoc()): ?>
        <div class="flex items-center justify-between bg-gradient-to-br from-white to-gray-50 shadow-md hover:shadow-lg rounded-lg p-4 border border-gray-100 transform hover:-translate-y-1 transition-all duration-300">
            
            <!-- Icon + Title -->
            <div class="flex items-center gap-3">
                <div class="bg-red-100 text-red-600 p-4 rounded-full flex items-center justify-center transition-colors duration-300">
                    <i class="fa-solid fa-warehouse"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 transition-colors duration-300">
                    <?= htmlspecialchars($dept['dept_name']) ?>
                </h3>
            </div>

            <!-- Button -->
            <a href="index.php?page=workstations&dept_id=<?= $dept['id'] ?>" 
               class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-4 py-2 rounded-full shadow-md transition-all duration-300">
               Lihat
            </a>
        </div>
    <?php endwhile; ?>
</div>

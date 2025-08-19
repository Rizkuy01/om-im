<?php
require_once 'config.php';
$result = $connIMOM->query("SELECT * FROM department");

// beberapa warna gradient buat beda2 card
$gradients = [
    "from-slate-200 to-slate-500 text-slate-600",
    "from-blue-200 to-blue-500 text-blue-600",
    "from-lime-200 to-lime-500 text-lime-600",
    "from-purple-200 to-purple-500 text-purple-600",
    "from-amber-200 to-amber-500 text-amber-600",
    "from-red-200 to-red-500 text-red-600",
];
?>

<h2 class="text-4xl text-center font-extrabold mb-10 text-gray-800 tracking-wide drop-shadow-sm">
  DEPARTEMENT
</h2>

<div class="py-8 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
    <?php 
    $i = 0;
    while($dept = $result->fetch_assoc()): 
        $gradient = $gradients[$i % count($gradients)];
        $i++;
    ?>
        <div class="group bg-white rounded-2xl shadow-md hover:shadow-xl transform hover:-translate-y-2 transition-all duration-300 p-6 flex flex-col items-center border border-gray-100 relative overflow-hidden">
            
            <!-- Decorative Circle -->
            <div class="absolute -top-10 -right-10 w-32 h-32 bg-gradient-to-br <?= $gradient ?> rounded-full opacity-20 group-hover:scale-110 transition-transform duration-500"></div>

            <!-- Icon -->
            <div class="relative z-10 mb-4 flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br <?= $gradient ?> shadow">
                <i class="fa-solid fa-warehouse text-2xl"></i>
            </div>

            <!-- Title -->
            <h3 class="relative z-10 text-xl font-bold text-gray-800 mb-4 text-center">
                <?= htmlspecialchars($dept['dept_name']) ?>
            </h3>

            <!-- Button -->
            <a href="index.php?page=workstations&dept_id=<?= $dept['id'] ?>" 
               class="relative z-10 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold px-6 py-2 rounded-full shadow-md transition-all duration-300">
               Lihat
            </a>
        </div>
    <?php endwhile; ?>
</div>

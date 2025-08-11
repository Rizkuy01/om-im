<?php
$dept = $_SESSION['dept'] ?? '';
?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-8">

    <?php if ($dept === 'QCE-2W' || $dept === 'QA'): ?>
        <!-- Card 1 -->
        <div class="bg-white shadow-md hover:shadow-lg rounded-lg p-6 text-center transform hover:-translate-y-1 transition duration-300">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">PRODUCTION A</h3>
            <a href="#" class="inline-block bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2 rounded-full transition duration-300">Open</a>
        </div>

        <!-- Card 2 -->
        <div class="bg-white shadow-md hover:shadow-lg rounded-lg p-6 text-center transform hover:-translate-y-1 transition duration-300">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">PRODUCTION B</h3>
            <a href="#" class="inline-block bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2 rounded-full transition duration-300">Open</a>
        </div>
    <?php endif; ?>

    <?php if ($dept === 'QCE-4W' || $dept === 'QA'): ?>
        <!-- Card 3 -->
        <div class="bg-white shadow-md hover:shadow-lg rounded-lg p-6 text-center transform hover:-translate-y-1 transition duration-300">
            <h3 class="text-xl font-semibold mb-4 text-gray-800">PRODUCTION C</h3>
            <a href="#" class="inline-block bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2 rounded-full transition duration-300">Open</a>
        </div>
    <?php endif; ?>

</div>

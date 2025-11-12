<?php
require_once 'config.php';

if (!in_array(strtoupper($_SESSION['dept'] ?? ''), ['QA','MIS'])) {
    header("Location: index.php");
    exit;
}
?>

<div class="bg-white rounded-lg shadow p-6">
    <!-- Judul -->
    <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">
        System Management
    </h1>

    <div class="space-y-4">
        <!-- Card Workstation -->
        <div onclick="openModal('workstationModal')" 
            class="cursor-pointer bg-white border border-red-200 rounded-lg shadow hover:shadow-lg 
                   flex flex-col items-center justify-center p-6 transition transform hover:scale-105 hover:bg-gray-100">
            <div class="w-14 h-14 flex items-center justify-center rounded-full bg-gradient-to-br from-green-400 to-green-600 text-white shadow">
                <i class="fa-solid fa-industry text-2xl"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-800 mt-3">Add Workstation</h2>
        </div>

        <!-- Card Sub Workstation -->
        <div onclick="openModal('subWsModal')" 
            class="cursor-pointer bg-white border border-red-200 rounded-lg shadow hover:shadow-lg 
                   flex flex-col items-center justify-center p-6 transition transform hover:scale-105 hover:bg-gray-100">
            <div class="w-14 h-14 flex items-center justify-center rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white shadow">
                <i class="fa-solid fa-gears text-2xl"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-800 mt-3">Add Sub Workstation</h2>
        </div>

        <!-- Card Process -->
        <div onclick="openModal('processModal')" 
            class="cursor-pointer bg-white border border-red-200 rounded-lg shadow hover:shadow-lg 
                   flex flex-col items-center justify-center p-6 transition transform hover:scale-105 hover:bg-gray-100">
            <div class="w-14 h-14 flex items-center justify-center rounded-full bg-gradient-to-br from-purple-400 to-purple-600 text-white shadow">
                <i class="fa-solid fa-diagram-project text-2xl"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-800 mt-3">Add Process</h2>
        </div>
    </div>
</div>

<?php 
include 'partials/add_workstation_modal.php';
include 'partials/add_subws_modal.php';
include 'partials/add_process_modal.php';
?>

<script>
function openModal(id) {
    document.getElementById(id).classList.remove('hidden');
    document.getElementById(id).classList.add('flex');
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
    document.getElementById(id).classList.remove('flex');
}
</script>

<?php
require_once 'config.php';

if (!in_array(strtoupper($_SESSION['dept'] ?? ''), ['QA','MIS'])) {
    header("Location: index.php");
    exit;
}
?>

<div class="p-6">
    <h1 class="text-2xl font-bold mb-6 text-gray-800">System Management</h1>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card Workstation -->
        <div class="bg-white p-6 rounded-lg shadow border">
            <h2 class="text-lg font-semibold mb-3">Add Workstation</h2>
            <button onclick="openModal('workstationModal')" 
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                + Add
            </button>
        </div>

        <!-- Card Sub Workstation -->
        <div class="bg-white p-6 rounded-lg shadow border">
            <h2 class="text-lg font-semibold mb-3">Add Sub Workstation</h2>
            <button onclick="openModal('subWsModal')" 
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                + Add
            </button>
        </div>

        <!-- Card Process -->
        <div class="bg-white p-6 rounded-lg shadow border">
            <h2 class="text-lg font-semibold mb-3">Add Process</h2>
            <button onclick="openModal('processModal')" 
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded shadow">
                + Add
            </button>
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

// Ajax cascade dropdown
function loadWorkstations(deptId, targetSelectId) {
    fetch('actions/get_workstations.php?dept_id=' + deptId)
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById(targetSelectId);
            select.innerHTML = '<option value="">-- Pilih Workstation --</option>';
            data.forEach(w => {
                select.innerHTML += `<option value="${w.id}">${w.name}</option>`;
            });
        });
}

function loadSubWorkstations(wsId, targetSelectId) {
    fetch('actions/get_subws.php?workstation_id=' + wsId)
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById(targetSelectId);
            select.innerHTML = '<option value="">-- Pilih Sub Workstation --</option>';
            data.forEach(s => {
                select.innerHTML += `<option value="${s.id}">${s.name}</option>`;
            });
        });
}
</script>

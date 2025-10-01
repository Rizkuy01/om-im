<?php
// Ambil list process\
$procStmt = $connIMOM->prepare("SELECT id, process_name FROM process WHERE sub_workstations_id = ?");
$procStmt->bind_param("i", $sub_id);
$procStmt->execute();
$processList = $procStmt->get_result();
$procStmt->close();
?>
<!-- Modal Tambah Rules -->
<div id="addRulesModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
  <div class="bg-white w-full max-w-lg rounded-lg shadow-lg p-6">
    <h2 class="text-lg font-bold mb-4">Tambah Rules</h2>
    <form action="actions/add_rules.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="sub_workstation_id" value="<?= $sub_id ?>">
        <input type="hidden" name="workstation_id" value="<?= $workstation_id ?>">
        <input type="hidden" name="dept_id" value="<?= $dept_id ?>">

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Nama Rules</label>
            <input type="text" name="rules_name" required class="w-full border rounded p-2">
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">Process</label>
            <select name="process_id" class="w-full border rounded p-2" required>
                <option value="">-- Pilih Process --</option>
                <?php
                $proc = $connIMOM->query("SELECT id, process_name FROM process WHERE sub_workstations_id = {$sub_id}");
                while ($p = $proc->fetch_assoc()): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['process_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium mb-1">File Rules</label>
            <input type="file" name="rules_file" accept=".pdf,.doc,.docx,.jpg,.png" required class="w-full border rounded p-2">
        </div>

        <div class="flex justify-end space-x-2">
            <button type="button" onclick="closeModal()" class="bg-gray-500 text-white px-4 py-2 rounded">Batal</button>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Simpan</button>
        </div>
    </form>
  </div>
</div>

<script>
function openModal() {
  document.getElementById('addRulesModal').classList.remove('hidden');
}
function closeModal() {
  document.getElementById('addRulesModal').classList.add('hidden');
}
</script>

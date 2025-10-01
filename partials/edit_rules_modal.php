<div id="editRulesModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    <h2 class="text-xl font-bold mb-4 text-gray-800">Edit Rules</h2>

    <form id="editRulesForm" method="post" action="actions/edit_rules.php" enctype="multipart/form-data">
      <input type="hidden" name="id" id="edit_id">
      <input type="hidden" name="sub_id" value="<?= $sub_id ?>">
      <input type="hidden" name="workstation_id" value="<?= $workstation_id ?>">
      <input type="hidden" name="dept_id" value="<?= $dept_id ?>">

      <!-- Nama Rules -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Rules</label>
        <input type="text" name="rules_name" id="edit_rules_name" required
               class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-red-500">
      </div>

      <!-- Pilih Process -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Process</label>
        <select name="process_id" id="edit_process_id" required
                class="w-full border px-3 py-2 rounded focus:outline-none focus:ring-2 focus:ring-red-500">
          <option value="">-- Pilih Process --</option>
          <?php
          $procRes = $connIMOM->query("SELECT id, process_name FROM process WHERE sub_workstations_id = $sub_id");
          while ($proc = $procRes->fetch_assoc()): ?>
            <option value="<?= $proc['id'] ?>"><?= htmlspecialchars($proc['process_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Upload File -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">File Rules (kosongkan jika tidak diganti)</label>
        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png"
               class="w-full text-sm text-gray-600 border rounded px-2 py-1">
      </div>

      <!-- Buttons -->
      <div class="flex justify-end gap-2">
        <button type="button" onclick="closeEditRulesModal()"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
          Batal
        </button>
        <button type="submit"
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded shadow">
          Update
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function openEditModal(id, rulesName, processId) {
  document.getElementById("edit_id").value = id;
  document.getElementById("edit_rules_name").value = rulesName;
  document.getElementById("edit_process_id").value = processId;

  document.getElementById("editRulesModal").classList.remove("hidden");
  document.getElementById("editRulesModal").classList.add("flex");
}

function closeEditRulesModal() {
  document.getElementById("editRulesModal").classList.add("hidden");
  document.getElementById("editRulesModal").classList.remove("flex");
}
</script>

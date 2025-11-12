<div id="subWsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <button onclick="closeModal('subWsModal')" class="absolute top-2 right-2 text-gray-500">✕</button>
        <h3 class="text-lg font-bold mb-4">Add Sub Workstation</h3>
        <form method="POST" action="actions/save_subws.php">
            <div class="mb-4">
                <label class="block text-sm font-medium">Departemen</label>
                <select name="dept_id" required class="w-full border rounded px-3 py-2" onchange="loadWorkstations(this.value,'wsSelectSub')">
                    <option value="">-- Pilih Departemen --</option>
                    <?php
                    $res = $connIMOM->query("SELECT id, dept_name FROM department ORDER BY dept_name");
                    while ($d = $res->fetch_assoc()): 
                        $deptName = strtoupper($d['dept_name']);
                        if (in_array($deptName, ['QA', 'MIS'])){
                            continue;
                        } ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['dept_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Workstation</label>
                <select id="wsSelectSub" name="workstation_id" required class="w-full border rounded px-3 py-2">
                    <option value="">-- Pilih Workstation --</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Nama Sub Workstation</label>
                <input type="text" name="name" required class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal('subWsModal')" class="bg-gray-400 text-white px-4 py-2 rounded">Batal</button>
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
function loadWorkstations(deptId, targetId) {
    const wsSelect = document.getElementById(targetId);
    wsSelect.innerHTML = "<option value=''>Loading...</option>";

    if (!deptId) {
        wsSelect.innerHTML = "<option value=''>-- Pilih Workstation --</option>";
        return;
    }

    fetch("actions/get_workstations.php?dept_id=" + deptId)
        .then(res => res.json())
        .then(data => {
            if (data.length > 0) {
                wsSelect.innerHTML = "<option value=''>-- Pilih Workstation --</option>";
                data.forEach(ws => {
                    wsSelect.innerHTML += `<option value="${ws.id}">${ws.name}</option>`;
                });
            } else {
                wsSelect.innerHTML = "<option value=''>Tidak ada workstation</option>";
            }
        })
        .catch(err => {
            console.error(err);
            wsSelect.innerHTML = "<option value=''>Gagal memuat</option>";
        });
}
</script>

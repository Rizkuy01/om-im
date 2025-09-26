<div id="processModal" class="hidden fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <button onclick="closeModal('processModal')" class="absolute top-2 right-2 text-gray-500">✕</button>
        <h3 class="text-lg font-bold mb-4">Add Process</h3>
        <form method="POST" action="actions/save_process.php">
            <div class="mb-4">
                <label class="block text-sm font-medium">Departemen</label>
                <select name="dept_id" required class="w-full border rounded px-3 py-2" onchange="loadWorkstations(this.value,'wsSelectProcess')">
                    <option value="">-- Pilih Departemen --</option>
                    <?php
                    $res = $connIMOM->query("SELECT id, dept_name FROM department ORDER BY dept_name");
                    while ($d = $res->fetch_assoc()): $deptName = strtoupper($d['dept_name']);
                        if (in_array($deptName, ['QA', 'MIS'])){
                            continue;
                        } ?>
                        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['dept_name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Workstation</label>
                <select id="wsSelectProcess" name="workstation_id" required class="w-full border rounded px-3 py-2"
                        onchange="loadSubWorkstations(this.value,'subWsSelectProcess')">
                    <option value="">-- Pilih Workstation --</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Sub Workstation</label>
                <select id="subWsSelectProcess" name="sub_workstation_id" required class="w-full border rounded px-3 py-2">
                    <option value="">-- Pilih Sub Workstation --</option>
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium">Nama Process</label>
                <input type="text" name="process_name" required class="w-full border rounded px-3 py-2">
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal('processModal')" class="bg-gray-400 text-white px-4 py-2 rounded">Batal</button>
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
            wsSelect.innerHTML = "<option value=''>-- Pilih Workstation --</option>";
            data.forEach(ws => {
                wsSelect.innerHTML += `<option value="${ws.id}">${ws.name}</option>`;
            });
        })
        .catch(err => {
            console.error(err);
            wsSelect.innerHTML = "<option value=''>Gagal memuat</option>";
        });
}

function loadSubWorkstations(wsId, targetId) {
    const subWsSelect = document.getElementById(targetId);
    subWsSelect.innerHTML = "<option value=''>Loading...</option>";

    if (!wsId) {
        subWsSelect.innerHTML = "<option value=''>-- Pilih Sub Workstation --</option>";
        return;
    }

    fetch("actions/get_subws.php?workstation_id=" + wsId)
        .then(res => res.json())
        .then(data => {
            subWsSelect.innerHTML = "<option value=''>-- Pilih Sub Workstation --</option>";
            data.forEach(sub => {
                subWsSelect.innerHTML += `<option value="${sub.id}">${sub.name}</option>`;
            });
        })
        .catch(err => {
            console.error(err);
            subWsSelect.innerHTML = "<option value=''>Gagal memuat</option>";
        });
}
</script>

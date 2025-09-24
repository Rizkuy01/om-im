<!-- Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <button onclick="closeModal()" class="absolute top-2 right-2 px-3 text-gray-500 hover:text-gray-700">
            ✕
        </button>
        <h3 class="text-lg font-bold text-gray-800 mb-4">
            Tambah Data <?= htmlspecialchars($subWs['name']) ?> - <?= $type ?>
        </h3>

        <form id="addDataForm" method="POST" action="actions/save_data.php" enctype="multipart/form-data">
            <input type="hidden" name="sub_workstation_id" value="<?= $sub_id ?>">
            <input type="hidden" name="workstation_id" value="<?= $workstation_id ?>">
            <input type="hidden" name="dept_id" value="<?= $dept_id ?>">
            <input type="hidden" name="type" value="<?= $type ?>">

            <!-- Process -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Process</label>
                <?php
                    $stmtProc = $connIMOM->prepare("SELECT id, process_name FROM process WHERE sub_workstations_id = ?");
                    $stmtProc->bind_param("i", $sub_id);
                    $stmtProc->execute();
                    $procResult = $stmtProc->get_result();
                    $stmtProc->close();
                ?>
                <?php if ($procResult->num_rows > 0): ?>
                    <select name="process_id" required
                        class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-red-300">
                        <option value="">-- Pilih Process --</option>
                        <?php while($p = $procResult->fetch_assoc()): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['process_name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                <?php else: ?>
                    <input type="hidden" name="process_id" value="">
                    <p class="text-gray-500 italic">(Tidak ada process)</p>
                <?php endif; ?>
            </div>

            <?php if ($type === 'IM'): ?>
            <!-- Part Number hanya untuk IM -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Part Number (Model)</label>
                <input type="text" name="part_number" id="part_number" required
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-red-300">
            </div>
            <?php endif; ?>

            <!-- Upload File -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload File (PDF/PNG/JPG)</label>
                <input type="file" name="uploaded_file" accept=".pdf,.png,.jpg,.jpeg" required
                    class="w-full text-sm border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-red-300">
            </div>

            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal()" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                    Batal
                </button>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                    Simpan
                </button>
            </div>
        </form>

<script>
document.getElementById('addDataForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    const partNumberField = document.getElementById('part_number');

    if (partNumberField) {
        const partNumber = partNumberField.value;
        const subWsId = form.sub_workstation_id.value;

        fetch('check_part_number.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `sub_workstation_id=${subWsId}&part_number=${encodeURIComponent(partNumber)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.exists) {
                Swal.fire({ icon: 'warning', title: 'Model sudah ada', text: 'Gunakan Edit/Replace' });
            } else {
                form.submit();
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal mengecek part number.' });
        });
    } else {
        // Kalau OM → langsung submit
        form.submit();
    }
});

</script>

            </div>
        </div>
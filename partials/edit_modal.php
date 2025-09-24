<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <!-- Close Button -->
        <button onclick="closeEditModal()" class="absolute top-2 right-2 px-3 text-gray-500 hover:text-gray-700">✕</button>

        <h3 class="text-lg font-bold text-gray-800 mb-4">Edit Data (<span id="editTypeLabel"><?= htmlspecialchars($type) ?></span>)</h3>

        <form id="editDataForm" method="POST" action="actions/update_data.php" enctype="multipart/form-data">
            <!-- hidden id record yang akan di-edit -->
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="sub_workstation_id" value="<?= (int)($sub_id ?? 0) ?>">
            <input type="hidden" name="workstation_id" value="<?= (int)($workstation_id ?? 0) ?>">
            <input type="hidden" name="dept_id" value="<?= (int)($dept_id ?? 0) ?>">
            <input type="hidden" name="type" id="edit_type" value="<?= htmlspecialchars($type) ?>">

            <!-- Part Number - hanya tampil kalo IM -->
            <div class="mb-4" id="edit_part_row" style="display:none;">
                <label class="block text-sm font-medium text-gray-700 mb-1">Part Number</label>
                <input type="text" name="part_number" id="edit_part_number" required
                    class="w-full px-3 py-2 border rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed" readonly>
            </div>

            <!-- Process -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Process</label>
                <?php
                // Ambil daftar process untuk sub workstation
                $procOptions = [];
                if (!empty($sub_id)) {
                    $stmtP = $connIMOM->prepare("SELECT id, process_name FROM process WHERE sub_workstations_id = ?");
                    $stmtP->bind_param("i", $sub_id);
                    $stmtP->execute();
                    $resP = $stmtP->get_result();
                    while ($r = $resP->fetch_assoc()) {
                        $procOptions[] = $r;
                    }
                    $stmtP->close();
                }
                ?>
                <?php if (count($procOptions) > 0): ?>
                <select name="process_id" id="edit_process_id"
                    class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-red-300">
                    <option value="">-- Pilih Process --</option>
                    <?php foreach ($procOptions as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= htmlspecialchars($p['process_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php else: ?>
                    <input type="hidden" name="process_id" id="edit_process_id" value="">
                    <p class="text-gray-500 italic">(Tidak ada process)</p>
                <?php endif; ?>
            </div>

            <!-- Upload File -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload File (PDF/PNG/JPG) (Opsional)</label>
                <input type="file" name="uploaded_file" accept=".pdf,.png,.jpg,.jpeg"
                    class="w-full text-sm border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-red-300">
                <p id="currentFile" class="text-xs text-gray-500 mt-2"></p>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeEditModal()" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
                    Batal
                </button>
                <button type="submit" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// AJAX
function openEditModal(id) {
    // reset
    document.getElementById('edit_id').value = '';
    document.getElementById('edit_part_number').value = '';
    document.getElementById('edit_process_id').value = '';
    document.getElementById('currentFile').textContent = '';

    // set id
    document.getElementById('edit_id').value = id;

    // type dari hidden
    const type = document.getElementById('edit_type').value || 'IM';
    document.getElementById('editTypeLabel').textContent = type;

    // toggle tampil part number row
    if (type === 'IM') {
        document.getElementById('edit_part_row').style.display = 'block';
    } else {
        document.getElementById('edit_part_row').style.display = 'none';
    }

    // fetch data record
    fetch(`actions/get_file.php?id=${encodeURIComponent(id)}&type=${encodeURIComponent(type)}`)
        .then(res => res.json())
        .then(data => {
            if (data.error) {
                Swal.fire({ icon: 'error', title: 'Error', text: data.error, confirmButtonColor:'#d33' });
                return;
            }
            // isi fields
            if (data.part_number) {
                document.getElementById('edit_part_number').value = data.part_number;
            }
            if (data.process_id) {
                const sel = document.getElementById('edit_process_id');
                if (sel) {
                    sel.value = data.process_id;
                }
            }
            if (data.file_name) {
                document.getElementById('currentFile').textContent = 'File saat ini: ' + data.file_name;
            }
            // show modal
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editModal').classList.add('flex');
        })
        .catch(err => {
            console.error(err);
            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal mengambil data', confirmButtonColor:'#d33' });
        });
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
}

// konfirmasi update
document.getElementById('editDataForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "File lama akan diganti jika Anda upload file baru.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, update',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6'
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
});
</script>

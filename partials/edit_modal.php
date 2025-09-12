<!-- partials/edit_modal.php -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <!-- Close Button -->
        <button onclick="closeEditModal()" class="absolute top-2 right-2 px-3 text-gray-500 hover:text-gray-700">
            ✕
        </button>

        <h3 class="text-lg font-bold text-gray-800 mb-4">Edit Data (<?= $type ?>)</h3>

        <form id="editDataForm" method="POST" action="actions/update_data.php" enctype="multipart/form-data">
            <!-- hidden id record yang akan di-edit -->
            <input type="hidden" name="id" id="edit_id">
            <input type="hidden" name="sub_workstation_id" value="<?= $sub_id ?>">
            <input type="hidden" name="workstation_id" value="<?= $workstation_id ?>">
            <input type="hidden" name="dept_id" value="<?= $dept_id ?>">
            <input type="hidden" name="type" value="<?= $type ?>">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Part Number</label>
                <input type="text" name="part_number" id="edit_part_number" required
                    class="w-full px-3 py-2 border rounded-lg bg-gray-100 text-gray-600 cursor-not-allowed" readonly>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload File (PDF/PNG/JPG) (Opsional)</label>
                <input type="file" name="uploaded_file" accept=".pdf,.png,.jpg,.jpeg"
                    class="w-full text-sm border px-3 py-2 rounded-lg focus:outline-none focus:ring focus:ring-red-300">
            </div>

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

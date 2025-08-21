

        <!-- Modal -->
        <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
                <button onclick="closeModal()" class="absolute top-2 right-2 px-3 text-gray-500 hover:text-gray-700">
                    ✕
                </button>
                <h3 class="text-lg font-bold text-gray-800 mb-4">Tambah Data Baru (<?= $type ?>)</h3>

                <form id="addDataForm" method="POST" action="save_data.php" enctype="multipart/form-data">
                    <input type="hidden" name="sub_workstation_id" value="<?= $sub_id ?>">
                    <input type="hidden" name="workstation_id" value="<?= $workstation_id ?>">
                    <input type="hidden" name="dept_id" value="<?= $dept_id ?>">
                    <input type="hidden" name="type" value="<?= $type ?>">

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Part Number</label>
                        <input type="text" name="part_number" id="part_number" required
                            class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-red-300">
                    </div>

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
    e.preventDefault(); // cegah submit langsung
    const form = this;
    const partNumber = document.getElementById('part_number').value;
    const subWsId = form.sub_workstation_id.value;

    fetch('check_part_number.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `sub_workstation_id=${subWsId}&part_number=${encodeURIComponent(partNumber)}`
    })
    .then(res => res.json())
    .then(data => {
        if (data.exists) {
            Swal.fire({
                icon: 'warning',
                title: 'Part Number sudah ada',
                text: 'Gunakan part number lain atau pilih fitur Edit/Replace.',
                confirmButtonColor: '#d33'
            });
        } else {
            form.submit(); // aman, lanjut submit
        }
    })
    .catch(err => {
        console.error(err);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Gagal mengecek part number, coba lagi.'
        });
    });
});
</script>

            </div>
        </div>
<?php
require_once 'config.php';

$workstation_id = (int) ($_GET['workstation_id'] ?? 0);
$dept_id        = $_GET['dept_id'] ?? null;
$sub_id         = (int) ($_GET['sub_id'] ?? 0);
$type           = strtoupper($_GET['type'] ?? 'IM'); // default IM

// Tentukan tabel berdasarkan type
$tableName = ($type === 'OM') ? 'data_om' : 'data_im';

// Ambil data sub workstation
$stmt = $connIMOM->prepare("SELECT * FROM sub_workstations WHERE id = ?");
$stmt->bind_param("i", $sub_id);
$stmt->execute();
$subWs = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Ambil data sesuai type
$stmt = $connIMOM->prepare("SELECT * FROM {$tableName} WHERE sub_workstation_id = ?");
$stmt->bind_param("i", $sub_id);
$stmt->execute();
$dataRows = $stmt->get_result();
$stmt->close();
?>

<h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
  <?= htmlspecialchars($subWs['name'] ?? 'Detail Sub Workstation') ?> - <?= $type ?>
</h2>

<!-- Add Button -->
<div class="flex justify-start mb-4">
    <button onclick="openModal()" 
        class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded shadow">
        + Tambah Data
    </button>
</div>

<!-- Modal -->
<div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <!-- Close Button -->
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
                <input type="text" name="part_number" required
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
    </div>
</div>

<script>
function openModal() {
    document.getElementById('addModal').classList.remove('hidden');
    document.getElementById('addModal').classList.add('flex');
}
function closeModal() {
    document.getElementById('addModal').classList.add('hidden');
    document.getElementById('addModal').classList.remove('flex');
}
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('addDataForm').addEventListener('submit', function(e) {
    e.preventDefault(); 
    
    const form = this;
    const formData = new FormData(form);

    fetch('check_part_number.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.exists) {
            Swal.fire({
                icon: 'warning',
                title: 'Part Number sudah ada',
                text: 'Apakah Anda ingin mengganti file lama dengan yang baru?',
                showCancelButton: true,
                confirmButtonText: 'Ya, ganti',
                cancelButtonText: 'Tidak',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) {
                    formData.append('replace', '1');
                    fetch('save_data.php', {
                        method: 'POST',
                        body: formData
                    }).then(() => {
                        window.location.reload();
                    });
                }
            });
        } else {
            fetch('save_data.php', {
                method: 'POST',
                body: formData
            }).then(() => {
                window.location.reload();
            });
        }
    });
});
</script>

<!-- TABLE VIEW -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<div class="overflow-x-auto bg-white shadow-md rounded-lg p-4">
    <table id="subWsTable" class="min-w-full text-sm text-left text-gray-700 border-collapse">
        <thead class="bg-red-600 text-gray-100 uppercase text-xs tracking-wider border-b">
            <tr>
                <th class="px-6 py-3 border">#</th>
                <th class="px-6 py-3 border">Part Number</th>
                <th class="px-6 py-3 border">File</th>
                <th class="px-6 py-3 border">File Name</th>
                <th class="px-6 py-3 border">Path File</th>
                <th class="px-6 py-3 border">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($dataRows->num_rows > 0): ?>
                <?php $no = 1; while ($row = $dataRows->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-3 border"><?= $no++ ?></td>
                        <td class="px-6 py-3 border"><?= htmlspecialchars($row['part_number']) ?></td>
                        <td class="px-6 py-3 border">
                            <a href="<?= htmlspecialchars($row['path_name'] . $row['file_name']) ?>" 
                               target="_blank" 
                               class="text-blue-600 hover:underline">
                                <?= htmlspecialchars($row['file_name']) ?>
                            </a>
                        </td>
                        <td class="px-6 py-3 border"><?= htmlspecialchars($row['file_name']) ?></td>
                        <td class="px-6 py-3 border"><?= htmlspecialchars($row['path_name']) ?></td>
                        <td class="px-6 py-3 border">
                            <a href="#"
                               class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium px-3 py-1 rounded-full shadow">
                               Edit
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                        Tidak ada data
                    </td>
                </tr>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        Swal.fire({
                            icon: 'info',
                            title: 'Tidak ada data',
                            text: 'Tidak ada data pada sub workstations ini',
                            confirmButtonColor: '#d33'
                        });
                    });
                </script>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- jQuery + DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<?php if ($dataRows->num_rows > 0): ?>
<script>
    $(document).ready(function() {
        $('#subWsTable').DataTable({
            pageLength: 5,
            lengthMenu: [5, 10, 20],
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                paginate: {
                    first: "Awal",
                    last: "Akhir",
                    next: "→",
                    previous: "←"
                }
            }
        });
    });
</script>
<?php endif; ?>

<!-- Tombol kembali -->
<div class="flex justify-end mt-4">
    <a href="index.php?page=sub_workstations&workstation_id=<?= $workstation_id ?>&dept_id=<?= $dept_id ?>"
       class="bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold px-3 py-1 rounded shadow">
       ← Kembali
    </a>
</div>

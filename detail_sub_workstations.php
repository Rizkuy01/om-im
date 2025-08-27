<?php
require_once 'config.php';

$workstation_id = (int) ($_GET['workstation_id'] ?? 0);
$dept_id        = $_GET['dept_id'] ?? null;
$sub_id         = (int) ($_GET['sub_id'] ?? 0);
$type           = strtoupper($_GET['type'] ?? 'IM');
$tableName = ($type === 'OM') ? 'data_om' : 'data_im';

// Ambil data departemen
$dept = $connIMOM->query("SELECT * FROM department WHERE id = $dept_id")->fetch_assoc();

// Ambil data workstation
$ws = $connIMOM->query("SELECT * FROM workstations WHERE id = $workstation_id")->fetch_assoc();

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

include 'partials/add_modal.php';
include 'partials/edit_modal.php';
?>

<!-- Container Utama -->
<div class="bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden max-w-7xl mx-auto">

    <!-- Header Merah + Breadcrumb -->
    <div class="bg-red-600 px-6 py-2 shadow-md border-b border-red-700">
        <nav class="flex items-center space-x-2 text-xs mb-2">
            <a href="index.php?page=dashboard_home" class="text-white hover:underline font-medium">Home</a>
            <span class="text-red-200">/</span>
            <a href="index.php?page=workstations&dept_id=<?= $dept_id ?>" class="text-white hover:underline font-medium">
                <?= htmlspecialchars($dept['dept_name'] ?? 'Departemen') ?>
            </a>
            <span class="text-red-200">/</span>
            <a href="index.php?page=sub_workstations&workstation_id=<?= $workstation_id ?>&dept_id=<?= $dept_id ?>" class="text-white hover:underline font-medium">
                <?= htmlspecialchars($ws['name'] ?? 'Workstation') ?>
            </a>
            <span class="text-red-200">/</span>
            <span class="text-white font-semibold">
                <?= htmlspecialchars($subWs['name'] ?? 'Detail Sub Workstation') ?> (<?= $type ?>)
            </span>
        </nav>

        <h2 class="text-white text-xl font-bold tracking-wide">
            Table Data <?= htmlspecialchars($subWs['name'] ?? '') ?> - <?= $type ?>
        </h2>
    </div>

    <!-- Isi Konten -->
    <div class="p-6 bg-gray-50">

        <!-- Add Button -->
        <div class="flex justify-start mb-4">
            <button onclick="openModal()" 
                class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded shadow">
                + Tambah Data
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto bg-white shadow-md rounded-lg p-4">
            <table id="subWsTable" class="min-w-full text-sm text-left text-gray-700 border-collapse">
                <thead class="bg-red-600 text-gray-100 uppercase text-xs tracking-wider border-b">
                    <tr>
                        <th class="px-6 py-3 border">#</th>
                        <th class="px-6 py-3 border">Part Number</th>
                        <th class="px-6 py-3 border">File</th>
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
                                    <a href="<?= htmlspecialchars($row['path_name'] . $row['file_name']) ?>" target="_blank" class="text-blue-600 hover:underline">
                                        <?= htmlspecialchars($row['file_name']) ?>
                                    </a>
                                </td>
                                <td class="px-6 py-3 border"><?= htmlspecialchars($row['path_name']) ?></td>
                                <td class="px-6 py-3 border text-center">
                                    <a href="javascript:void(0)" 
                                        onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['part_number'], ENT_QUOTES) ?>')" 
                                        class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium px-3 py-1 rounded-full shadow">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">
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


        <!-- Tombol kembali -->
        <div class="flex justify-end mt-6">
            <a href="index.php?page=sub_workstations&workstation_id=<?= $workstation_id ?>&dept_id=<?= $dept_id ?>"
               class="bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold px-4 py-2 rounded shadow">
               ← Kembali
            </a>
        </div>
    </div>
</div>

<!-- Script Modal -->
<script>
function openModal() {
    document.getElementById('addModal').classList.remove('hidden');
    document.getElementById('addModal').classList.add('flex');
}
function closeModal() {
    document.getElementById('addModal').classList.add('hidden');
    document.getElementById('addModal').classList.remove('flex');
}
function openEditModal(id, partNumber) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_part_number').value = partNumber;

    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
}
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
}

//  Alert
document.getElementById('editDataForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const form = this;
    Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data akan diubah sesuai input baru.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, ubah',
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

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php if (!empty($_SESSION['alert'])): ?>
<script>
Swal.fire({
    icon: "<?= $_SESSION['alert']['type'] ?>",
    title: "<?= $_SESSION['alert']['title'] ?>",
    text: "<?= $_SESSION['alert']['message'] ?>",
    confirmButtonColor: "#d33"
});
</script>
<?php unset($_SESSION['alert']); endif; ?>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
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

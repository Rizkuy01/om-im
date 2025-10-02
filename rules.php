<?php
require_once 'config.php';

$workstation_id = (int) ($_GET['workstation_id'] ?? 0);
$dept_id        = $_GET['dept_id'] ?? null;
$sub_id         = (int) ($_GET['sub_id'] ?? 0);

// Ambil data sub workstation
$stmt = $connIMOM->prepare("SELECT * FROM sub_workstations WHERE id = ?");
$stmt->bind_param("i", $sub_id);
$stmt->execute();
$subWs = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Ambil data rules
$stmt = $connIMOM->prepare("SELECT * FROM data_rules WHERE sub_workstation_id = ?");
$stmt->bind_param("i", $sub_id);
$stmt->execute();
$rules = $stmt->get_result();
$stmt->close();
?>

<div class="bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden max-w-7xl mx-auto">

    <!-- Header -->
    <div class="bg-red-600 px-6 py-2 shadow-md border-b border-red-700">
        <h2 class="text-white text-xl font-bold tracking-wide">
            Table Rules - <?= htmlspecialchars($subWs['name'] ?? '') ?>
        </h2>
    </div>

    <div class="p-6 bg-gray-50">
        <!-- Tombol Tambah -->
        <div class="flex justify-start mb-4">
            <button onclick="openModal()" 
                class="bg-green-600 hover:bg-green-700 text-white text-sm font-semibold px-4 py-2 rounded shadow">
                + Tambah Rules
            </button>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto bg-white shadow-md rounded-lg p-4">
            <table id="rulesTable" class="min-w-full text-sm text-left text-gray-700 border-collapse">
                <thead class="bg-red-600 text-gray-100 uppercase text-xs tracking-wider border-b">
                    <tr>
                        <th class="px-6 py-3 border">#</th>
                        <th class="px-6 py-3 border">Nama Rules</th>
                        <th class="px-6 py-3 border">Process</th>
                        <th class="px-6 py-3 border">File</th>
                        <th class="px-6 py-3 border">Path File</th>
                        <th class="px-6 py-3 border">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rules->num_rows > 0): ?>
                        <?php $no=1; while($row = $rules->fetch_assoc()): ?>
                            <?php
                                $procName = "(Tidak ada process)";
                                if (!empty($row['process_id'])) {
                                    $p = $connIMOM->query("SELECT process_name FROM process WHERE id = {$row['process_id']}")->fetch_assoc();
                                    $procName = $p['process_name'] ?? "Belum ada process";
                                }
                            ?>
                            <tr>
                                <td class="px-6 py-3 border"><?= $no++ ?></td>
                                <td class="px-6 py-3 border"><?= htmlspecialchars($row['rules_name']) ?></td>
                                <td class="px-6 py-3 border"><?= htmlspecialchars($procName) ?></td>
                                <td class="px-6 py-3 border">
                                    <a href="<?= htmlspecialchars($row['path_name'] . $row['file_name']) ?>" target="_blank" class="text-blue-600 hover:underline">
                                        <?= htmlspecialchars($row['file_name']) ?>
                                    </a>
                                </td>
                                <td class="px-6 py-3 border"><?= htmlspecialchars($row['path_name']) ?></td>
                                <td class="px-6 py-3 border text-center space-x-2">
                                    <a href="javascript:void(0)" 
                                    onclick="openEditModal(<?= $row['id'] ?>, '<?= htmlspecialchars($row['rules_name'],ENT_QUOTES) ?>', <?= (int)$row['process_id'] ?>)" 
                                    class="bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-medium px-3 py-1 rounded-full shadow">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    </a>

                                    <a href="actions/delete_rules.php?id=<?= $row['id'] ?>&sub_id=<?= $sub_id ?>&workstation_id=<?= $workstation_id ?>&dept_id=<?= $dept_id ?>" 
                                    onclick="return confirm('Yakin hapus rules ini?')" 
                                    class="bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-1 rounded-full shadow">
                                    <i class="fa-solid fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada rules
                            </td>
                        </tr>
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
<?php include 'partials/add_rules_modal.php'; ?>
<?php include 'partials/edit_rules_modal.php'; ?>

<!-- DataTables -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script>
    $(document).ready(function() {
        // Cek apakah tbody punya data selain row kosong
        let rowCount = $("#rulesTable tbody tr").length;
        let isEmpty = rowCount === 1 && $("#rulesTable tbody tr td").length === 1;

        if (!isEmpty) {
            $('#rulesTable').DataTable({
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
        }
    });
</script>


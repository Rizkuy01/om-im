<?php
$listQ = "
    SELECT t.type, t.process_id, t.part_number, t.id, s.name AS sub_name
    FROM (
        SELECT 'OM' AS type, process_id, part_number, id, sub_workstation_id FROM data_om
        UNION ALL
        SELECT 'IM' AS type, process_id, part_number, id, sub_workstation_id FROM data_im
    ) t
    LEFT JOIN sub_workstations s ON t.sub_workstation_id = s.id
    ORDER BY t.id DESC
    LIMIT 6
";
$listRes = $connIMOM->query($listQ);
?>

<div class="bg-white rounded-lg shadow-inner p-4 overflow-hidden border-red-200 border">
    <h2 class="text-lg font-semibold mb-3 text-red-600">Latest OM/IM Files</h2>
    <div class="overflow-y-auto h-80 relative" id="scrollContainer">
        <table class="min-w-full text-sm text-left border rounded">
            <thead class="bg-gray-200 sticky top-0">
                <tr>
                    <th class="px-3 py-2">Type</th>
                    <th class="px-3 py-2">Process</th>
                    <th class="px-3 py-2">Part Number</th>
                    <th class="px-3 py-2">Sub WS</th>
                </tr>
            </thead>
            <tbody id="omimTable">
                <?php if ($listRes && $listRes->num_rows > 0): ?>
                <?php while ($row = $listRes->fetch_assoc()): ?>
                    <?php
                    $procName = '-';
                    if (!empty($row['process_id'])) {
                        $p = $connIMOM->query("SELECT process_name FROM process WHERE id=".(int)$row['process_id'])->fetch_assoc();
                        $procName = $p['process_name'] ?? '-';
                    }
                    ?>
                    <tr class="border-b hover:bg-white">
                    <td class="px-3 py-2 font-semibold <?= $row['type']==='OM'?'text-green-600':'text-blue-600' ?>">
                        <?= htmlspecialchars($row['type']) ?>
                    </td>
                    <td class="px-3 py-2"><?= htmlspecialchars($procName) ?></td>
                    <td class="px-3 py-2"><?= htmlspecialchars($row['part_number']) ?></td>
                    <td class="px-3 py-2"><?= htmlspecialchars($row['sub_name'] ?? '-') ?></td>
                    </tr>
                <?php endwhile; ?>
                <?php else: ?>
                <tr>
                    <td colspan="4" class="px-3 py-4 text-center text-gray-500">Tidak ada data</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

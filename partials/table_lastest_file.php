<?php
$limit = 6;

$sql = "
SELECT type, file_id, process_id, process_name, sub_name, dept_name
FROM (
  SELECT
    'IM' AS type,
    di.id AS file_id,
    di.process_id,
    p.process_name,
    sw.name AS sub_name,
    d.dept_name
  FROM data_im di
  JOIN sub_workstations sw ON di.sub_workstation_id = sw.id
  JOIN workstations w ON sw.workstation_id = w.id
  JOIN department d ON w.dept_id = d.id
  LEFT JOIN process p ON di.process_id = p.id

  UNION ALL

  SELECT
    'OM' AS type,
    dox.id AS file_id,
    dox.process_id,
    p2.process_name,
    sw2.name AS sub_name,
    d2.dept_name
  FROM data_om dox
  JOIN sub_workstations sw2 ON dox.sub_workstation_id = sw2.id
  JOIN workstations w2 ON sw2.workstation_id = w2.id
  JOIN department d2 ON w2.dept_id = d2.id
  LEFT JOIN process p2 ON dox.process_id = p2.id
) AS combined
ORDER BY file_id DESC
LIMIT " . (int)$limit . "
";

$result = $connIMOM->query($sql);
if (!$result) {
    echo "<div class='p-4 bg-red-50 border rounded text-sm text-red-700'>Terjadi error query: " . htmlspecialchars($connIMOM->error) . "</div>";
    return;
}
?>

<div class="overflow-x-auto bg-white shadow-md rounded-lg p-4">
    <h2 class="text-lg font-bold mb-4 text-red-600">Latest OM/IM Files</h2>
    <table class="min-w-full text-sm text-left text-gray-700 border-collapse">
        <thead class="bg-red-600 text-gray-100 uppercase text-xs tracking-wider border-b">
            <tr>
                <th class="px-6 py-3 border">Type</th>
                <th class="px-6 py-3 border">Process</th>
                <th class="px-6 py-3 border">Line</th>
                <th class="px-6 py-3 border">Department</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td class="px-6 py-3 border font-semibold <?= $row['type'] === 'OM' ? 'text-green-600' : 'text-blue-600' ?>">
                            <?= htmlspecialchars($row['type']) ?>
                        </td>
                        <td class="px-6 py-3 border">
                            <?= htmlspecialchars($row['process_name'] ?: '-') ?>
                        </td>
                        <td class="px-6 py-3 border">
                            <?= htmlspecialchars($row['sub_name'] ?: '-') ?>
                        </td>
                        <td class="px-6 py-3 border">
                            <?= htmlspecialchars($row['dept_name'] ?: '-') ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">Tidak ada file terbaru</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

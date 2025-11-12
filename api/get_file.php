<?php
require_once '../config.php';

$query = "
    SELECT 'OM' AS type, process_id, part_number, id 
    FROM data_om
    UNION ALL
    SELECT 'IM' AS type, process_id, part_number, id 
    FROM data_im
    ORDER BY id DESC
    LIMIT 20
";
$result = $connIMOM->query($query);

while ($row = $result->fetch_assoc()) {
    $procName = '-';
    if ($row['process_id']) {
        $p = $connIMOM->query("SELECT process_name FROM process WHERE id=".(int)$row['process_id'])->fetch_assoc();
        $procName = $p['process_name'] ?? '-';
    }
    echo "<tr class='border-b hover:bg-gray-50'>";
    echo "<td class='px-3 py-2 font-semibold ".($row['type']==='OM'?'text-green-600':'text-blue-600')."'>".$row['type']."</td>";
    echo "<td class='px-3 py-2'>".htmlspecialchars($procName)."</td>";
    echo "<td class='px-3 py-2'>".htmlspecialchars($row['part_number'])."</td>";
    echo "</tr>";
}

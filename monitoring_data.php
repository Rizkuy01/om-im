<?php
require_once 'config.php';
$result = $connIM->query("SELECT * FROM im ORDER BY id DESC LIMIT 1");

if ($result->num_rows > 0) {
    echo '<table class="table-auto border-collapse w-full text-sm text-left text-gray-700">
            <thead>
                <tr class="bg-gray-100">
                    <th class="px-4 py-2 border">Line</th>
                    <th class="px-4 py-2 border">Type</th>
                    <th class="px-4 py-2 border">Machine</th>
                </tr>
            </thead>
            <tbody>';
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td class='px-4 py-2 border'>{$row['line']}</td>
                <td class='px-4 py-2 border'>{$row['type']}</td>
                <td class='px-4 py-2 border'>{$row['mesin']}</td>
              </tr>";
    }
    echo '</tbody></table>';
} else {
    echo '<p class="text-gray-500">Belum ada data.</p>';
}

// $result = $connIM->query("SELECT * FROM im ORDER BY id DESC LIMIT 1");

// if ($result->num_rows > 0) {
//     echo '<table class="table-auto border-collapse w-full text-sm text-left text-gray-700">
//             <thead>
//                 <tr class="bg-gray-100">
//                     <th class="px-4 py-2 border">Line</th>
//                     <th class="px-4 py-2 border">Type</th>
//                     <th class="px-4 py-2 border">Machine</th>
//                 </tr>
//             </thead>
//             <tbody>';
//     while ($row = $result->fetch_assoc()) {
//         echo "<tr>
//                 <td class='px-4 py-2 border'>{$row['line']}</td>
//                 <td class='px-4 py-2 border'>{$row['type']}</td>
//                 <td class='px-4 py-2 border'>{$row['mesin']}</td>
//               </tr>";
//     }
//     echo '</tbody></table>';
// } else {
//     echo '<p class="text-gray-500">Belum ada data.</p>';
// }

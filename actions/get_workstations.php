<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$dept_id = (int) ($_GET['dept_id'] ?? 0);
$data = [];

if ($dept_id > 0) {
    $stmt = $connIMOM->prepare("SELECT id, name FROM workstations WHERE dept_id = ? ORDER BY name ASC");
    $stmt->bind_param("i", $dept_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
}

echo json_encode($data);

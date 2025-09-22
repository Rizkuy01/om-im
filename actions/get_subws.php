<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$ws_id = (int) ($_GET['workstation_id'] ?? 0);
$data = [];

if ($ws_id > 0) {
    $stmt = $connIMOM->prepare("SELECT id, name FROM sub_workstations WHERE workstation_id = ? ORDER BY name ASC");
    $stmt->bind_param("i", $ws_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $stmt->close();
}

echo json_encode($data);

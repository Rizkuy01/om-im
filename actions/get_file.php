<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$type = strtoupper(trim($_GET['type'] ?? 'IM'));

if ($id <= 0) {
    echo json_encode(['error' => 'ID tidak valid']);
    exit;
}

if ($type === 'OM') {
    $stmt = $connIMOM->prepare("SELECT id, sub_workstation_id, process_id, file_name, path_name FROM data_om WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
} else {
    $stmt = $connIMOM->prepare("SELECT id, sub_workstation_id, process_id, part_number, file_name, path_name FROM data_im WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $id);
}
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(['error' => 'Data tidak ditemukan']);
    exit;
}

// return
echo json_encode($row);
exit;

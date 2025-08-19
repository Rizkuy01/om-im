<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sub_workstation_id = (int) ($_POST['sub_workstation_id'] ?? 0);
    $part_number        = trim($_POST['part_number'] ?? '');

    $stmt = $connIMOM->prepare("SELECT id FROM data_im WHERE sub_workstation_id=? AND part_number=?");
    $stmt->bind_param("is", $sub_workstation_id, $part_number);
    $stmt->execute();
    $exists = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    echo json_encode(['exists' => $exists]);
}

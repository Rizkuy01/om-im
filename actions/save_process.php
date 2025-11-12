<?php
require_once __DIR__ . '/../config.php';
session_start();

$dept = strtoupper(trim($_SESSION['dept'] ?? ''));
if (!in_array($dept, ['QA','MIS'])) {
    $_SESSION['alert'] = [
        "type" => "error",
        "title" => "Akses Ditolak",
        "message" => "Anda tidak memiliki akses ke menu ini."
    ];
    header("Location: ../system.php");
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dept_id          = (int) ($_POST['dept_id'] ?? 0);
    $workstation_id   = (int) ($_POST['workstation_id'] ?? 0);
    $sub_ws_id        = (int) ($_POST['sub_workstation_id'] ?? 0);
    $process_name     = trim($_POST['process_name'] ?? '');

    if ($dept_id > 0 && $workstation_id > 0 && $sub_ws_id > 0 && $process_name !== '') {
        $stmt = $connIMOM->prepare("INSERT INTO process (sub_workstations_id, process_name) VALUES (?, ?)");
        $stmt->bind_param("is", $sub_ws_id, $process_name);
        if ($stmt->execute()) {
            $_SESSION['alert'] = [
                "type" => "success",
                "title" => "Berhasil",
                "message" => "Process berhasil ditambahkan."
            ];
        } else {
            $_SESSION['alert'] = [
                "type" => "error",
                "title" => "Gagal",
                "message" => "Gagal menambahkan process."
            ];
        }
        $stmt->close();
    } else {
        $_SESSION['alert'] = [
            "type" => "error",
            "title" => "Data tidak lengkap",
            "message" => "Departemen, Workstation, Sub Workstation, dan nama Process wajib diisi."
        ];
    }
}

header("Location: ../system.php");
exit;

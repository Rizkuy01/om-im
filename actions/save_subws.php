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
    $dept_id        = (int) ($_POST['dept_id'] ?? 0);
    $workstation_id = (int) ($_POST['workstation_id'] ?? 0);
    $name           = trim($_POST['name'] ?? '');

    if ($dept_id > 0 && $workstation_id > 0 && $name !== '') {
        $stmt = $connIMOM->prepare("INSERT INTO sub_workstations (workstation_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $workstation_id, $name);
        if ($stmt->execute()) {
            $_SESSION['alert'] = [
                "type" => "success",
                "title" => "Berhasil",
                "message" => "Sub Workstation berhasil ditambahkan."
            ];
        } else {
            $_SESSION['alert'] = [
                "type" => "error",
                "title" => "Gagal",
                "message" => "Gagal menambahkan sub workstation."
            ];
        }
        $stmt->close();
    } else {
        $_SESSION['alert'] = [
            "type" => "error",
            "title" => "Data tidak lengkap",
            "message" => "Departemen, Workstation, dan nama Sub Workstation wajib diisi."
        ];
    }
}

header("Location: ../system.php");
exit;

<?php
require_once __DIR__ . '/../config.php';
session_start();

// 🔒 Cek role user
$dept = strtoupper(trim($_SESSION['dept'] ?? ''));
if (!in_array($dept, ['QA','MIS'])) {
    $_SESSION['alert'] = [
        "type" => "error",
        "title" => "Akses Ditolak",
        "message" => "Anda tidak memiliki akses untuk menambahkan workstation."
    ];
    header("Location: ../system.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dept_id = (int) ($_POST['dept_id'] ?? 0);
    $name    = trim($_POST['name'] ?? '');

    if ($dept_id > 0 && $name !== '') {
        $stmt = $connIMOM->prepare("INSERT INTO workstations (dept_id, name) VALUES (?, ?)");
        $stmt->bind_param("is", $dept_id, $name);
        if ($stmt->execute()) {
            $_SESSION['alert'] = [
                "type" => "success",
                "title" => "Berhasil",
                "message" => "Workstation berhasil ditambahkan."
            ];
        } else {
            $_SESSION['alert'] = [
                "type" => "error",
                "title" => "Gagal",
                "message" => "Gagal menambahkan workstation."
            ];
        }
        $stmt->close();
    } else {
        $_SESSION['alert'] = [
            "type" => "error",
            "title" => "Data tidak lengkap",
            "message" => "Departemen dan nama workstation wajib diisi."
        ];
    }
}

header("Location: ../system.php");
exit;

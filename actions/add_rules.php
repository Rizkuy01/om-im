<?php
require_once '../config.php';
session_start();

// Ambil data dari form
$rulesName = $_POST['rules_name'] ?? null;
$processId = $_POST['process_id'] ?? null;
$subWsId   = $_POST['sub_workstation_id'] ?? null;
$workstationId = $_POST['workstation_id'] ?? null;
$deptId    = $_POST['dept_id'] ?? null;

if (!$rulesName || !$processId || !$subWsId) {
    $_SESSION['alert'] = [
        "type" => "error",
        "title" => "Gagal",
        "message" => "Data tidak lengkap!"
    ];
    header("Location: ../index.php?page=rules&workstation_id={$workstationId}&dept_id={$deptId}&sub_id={$subWsId}");
    exit;
}

// Upload File
if (!empty($_FILES['rules_file']['name'])) {
    $uploadDir = "../uploads/rules/{$subWsId}/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = basename($_FILES['rules_file']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['rules_file']['tmp_name'], $targetFile)) {
        $pathName = "uploads/rules/{$subWsId}/";

        // Insert ke DB
        $stmt = $connIMOM->prepare("INSERT INTO data_rules (rules_name, process_id, sub_workstation_id, file_name, path_name) 
                                    VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiss", $rulesName, $processId, $subWsId, $fileName, $pathName);
        $stmt->execute();
        $stmt->close();

        $_SESSION['alert'] = [
            "type" => "success",
            "title" => "Berhasil",
            "message" => "Rules berhasil ditambahkan!"
        ];
    } else {
        $_SESSION['alert'] = [
            "type" => "error",
            "title" => "Upload Gagal",
            "message" => "File gagal diupload."
        ];
    }
} else {
    $_SESSION['alert'] = [
        "type" => "error",
        "title" => "Gagal",
        "message" => "Tidak ada file yang diupload."
    ];
}

// Redirect kembali
header("Location: ../index.php?page=rules&workstation_id={$workstationId}&dept_id={$deptId}&sub_id={$subWsId}");
exit;

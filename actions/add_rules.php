<?php
require_once '../config.php';
session_start();

// Ambil data dari form
$rulesName     = $_POST['rules_name'] ?? null;
$processId     = $_POST['process_id'] ?? null;
$subWsId       = $_POST['sub_workstation_id'] ?? null;
$workstationId = $_POST['workstation_id'] ?? null;
$deptId        = $_POST['dept_id'] ?? null;

if (!$rulesName || !$processId || !$subWsId) {
    $_SESSION['alert'] = [
        "type" => "error",
        "title" => "Gagal",
        "message" => "Data tidak lengkap!"
    ];
    header("Location: ../index.php?page=rules&workstation_id={$workstationId}&dept_id={$deptId}&sub_id={$subWsId}");
    exit;
}

// Ambil nama process
$stmt = $connIMOM->prepare("SELECT process_name FROM process WHERE id = ?");
$stmt->bind_param("i", $processId);
$stmt->execute();
$procRow = $stmt->get_result()->fetch_assoc();
$stmt->close();
$processName = $procRow['process_name'] ?? "process";

// Sanitasi nama file
function safeFileName($str) {
    return preg_replace("/[^A-Za-z0-9_\-]/", "_", $str);
}

if (!empty($_FILES['rules_file']['name'])) {
    $uploadDir = "../uploads/rules/{$subWsId}/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = pathinfo($_FILES['rules_file']['name'], PATHINFO_EXTENSION);
    $newFileName = safeFileName($processName) . "-" . safeFileName($rulesName) . "." . $ext;

    $targetFile = $uploadDir . $newFileName;

    if (move_uploaded_file($_FILES['rules_file']['tmp_name'], $targetFile)) {
        $pathName = "uploads/rules/{$subWsId}/";

        // Insert ke DB
        $stmt = $connIMOM->prepare("INSERT INTO data_rules (rules_name, process_id, sub_workstation_id, file_name, path_name) 
                                    VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("siiss", $rulesName, $processId, $subWsId, $newFileName, $pathName);
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

// Redirect
header("Location: ../index.php?page=rules&workstation_id={$workstationId}&dept_id={$deptId}&sub_id={$subWsId}");
exit;

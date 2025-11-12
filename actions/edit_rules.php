<?php
require_once '../config.php';
session_start();

$id           = $_POST['id'] ?? null;
$rulesName    = $_POST['rules_name'] ?? null;
$processId    = $_POST['process_id'] ?? null;
$subWsId      = $_POST['sub_id'] ?? null;
$workstationId= $_POST['workstation_id'] ?? null;
$deptId       = $_POST['dept_id'] ?? null;

if (!$id || !$rulesName || !$processId || !$subWsId) {
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

// Ambil data lama
$old = $connIMOM->query("SELECT file_name, path_name FROM data_rules WHERE id=$id")->fetch_assoc();

function safeFileName($str) {
    return preg_replace("/[^A-Za-z0-9_\-]/", "_", $str);
}

$newFileName = $old['file_name'];
$pathName = $old['path_name'];

if (!empty($_FILES['file']['name'])) {
    $uploadDir = "../uploads/rules/{$subWsId}/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $newFileName = safeFileName($processName) . "-" . safeFileName($rulesName) . "." . $ext;
    $targetFile = $uploadDir . $newFileName;

    move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
    $pathName = "uploads/rules/{$subWsId}/";
}

// Update DB
$stmt = $connIMOM->prepare("UPDATE data_rules SET rules_name=?, process_id=?, file_name=?, path_name=? WHERE id=?");
$stmt->bind_param("sissi", $rulesName, $processId, $newFileName, $pathName, $id);
$stmt->execute();
$stmt->close();

$_SESSION['alert'] = [
    "type" => "success",
    "title" => "Berhasil",
    "message" => "Rules berhasil diperbarui!"
];

// Redirect
header("Location: ../index.php?page=rules&workstation_id={$workstationId}&dept_id={$deptId}&sub_id={$subWsId}");
exit;

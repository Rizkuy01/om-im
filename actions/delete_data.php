<?php
require_once __DIR__ . '/../config.php';
session_start();

$id         = (int) ($_GET['id'] ?? 0);
$type       = strtoupper($_GET['type'] ?? 'IM');
$sub_id     = (int) ($_GET['sub_id'] ?? 0);
$work_id    = (int) ($_GET['workstation_id'] ?? 0);
$dept_id    = (int) ($_GET['dept_id'] ?? 0);

$tableName = ($type === 'OM') ? 'data_om' : 'data_im';
$redirect  = "../index.php?page=detail_sub_workstations&sub_id={$sub_id}&workstation_id={$work_id}&dept_id={$dept_id}&type={$type}";

if ($id > 0) {
    // ambil data lama
    $stmt = $connIMOM->prepare("SELECT file_name, path_name FROM {$tableName} WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $oldData = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($oldData) {
        // hapus file fisik
        $filePath = __DIR__ . '/../' . $oldData['path_name'] . $oldData['file_name'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // hapus dari database
        $del = $connIMOM->prepare("DELETE FROM {$tableName} WHERE id=?");
        $del->bind_param("i", $id);
        $del->execute();
        $del->close();

        $_SESSION['alert'] = [
            "type" => "success",
            "title" => "Berhasil",
            "message" => "Data berhasil dihapus."
        ];
    } else {
        $_SESSION['alert'] = [
            "type" => "error",
            "title" => "Gagal",
            "message" => "Data tidak ditemukan."
        ];
    }
} else {
    $_SESSION['alert'] = [
        "type" => "error",
        "title" => "Gagal",
        "message" => "ID tidak valid."
    ];
}

header("Location: $redirect");
exit;
?>

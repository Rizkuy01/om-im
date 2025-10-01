<?php
require_once '../config.php';
session_start();

$id     = (int) ($_GET['id'] ?? 0);
$subId  = (int) ($_GET['sub_id'] ?? 0);
$workstationId = (int) ($_GET['workstation_id'] ?? 0);
$deptId = (int) ($_GET['dept_id'] ?? 0);

if ($id > 0) {
    // Ambil file lama
    $stmt = $connIMOM->prepare("SELECT file_name, path_name FROM data_rules WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $old = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($old) {
        $filePath = "../" . $old['path_name'] . $old['file_name'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Hapus dari DB
        $stmt = $connIMOM->prepare("DELETE FROM data_rules WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['alert'] = ['type'=>'success','title'=>'Berhasil','message'=>'Rules berhasil dihapus.'];
    } else {
        $_SESSION['alert'] = ['type'=>'error','title'=>'Gagal','message'=>'Data tidak ditemukan.'];
    }
}

header("Location: ../index.php?page=rules&sub_id=$subId&workstation_id=$workstationId&dept_id=$deptId");
exit;

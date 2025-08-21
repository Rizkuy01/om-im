<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id               = (int) ($_POST['id'] ?? 0); // id data di table data_im/data_om
    $sub_workstation_id = (int) ($_POST['sub_workstation_id'] ?? 0);
    $workstation_id   = (int) ($_POST['workstation_id'] ?? 0);
    $dept_id          = (int) ($_POST['dept_id'] ?? 0);
    $part_number      = trim($_POST['part_number'] ?? '');
    $type             = strtoupper($_POST['type'] ?? 'IM');
    $uploaded         = $_FILES['uploaded_file'] ?? null;

    // tentukan tabel sesuai type
    $tableName = ($type === 'OM') ? 'data_om' : 'data_im';
    $suffix    = ($type === 'OM') ? '-OM' : '-IM';

    if ($id > 0 && $sub_workstation_id > 0 && $part_number !== '') {
        // ambil nama dept & sub workstation
        $deptQ = $connIMOM->prepare("SELECT dept_name FROM department WHERE id = ?");
        $deptQ->bind_param("i", $dept_id);
        $deptQ->execute();
        $deptName = $deptQ->get_result()->fetch_assoc()['dept_name'] ?? "Dept";
        $deptQ->close();

        $subQ = $connIMOM->prepare("SELECT name FROM sub_workstations WHERE id = ?");
        $subQ->bind_param("i", $sub_workstation_id);
        $subQ->execute();
        $subName = $subQ->get_result()->fetch_assoc()['name'] ?? "SubWS";
        $subQ->close();

        // buat folder Dept/SubWS/
        $basePath = "uploads/$deptName/$subName/";
        if (!is_dir($basePath)) {
            mkdir($basePath, 0777, true);
        }

        // jika ada file baru diupload
        if ($uploaded && $uploaded['error'] === UPLOAD_ERR_OK) {
            $file_ext = strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['pdf', 'png', 'jpg', 'jpeg'];

            if (!in_array($file_ext, $allowed_ext)) {
                die("Format file tidak diizinkan.");
            }

            $newFileName = $part_number . $suffix . "." . $file_ext;
            $targetFile  = $basePath . $newFileName;

            if (move_uploaded_file($uploaded['tmp_name'], $targetFile)) {
                // update dengan file baru
                $stmt = $connIMOM->prepare("UPDATE {$tableName} 
                                            SET part_number=?, file_name=?, path_name=? 
                                            WHERE id=?");
                $stmt->bind_param("sssi", $part_number, $newFileName, $basePath, $id);
                $stmt->execute();
                $stmt->close();
            } else {
                die("Gagal upload file.");
            }
        } else {
            // update hanya part_number tanpa ubah file
            $stmt = $connIMOM->prepare("UPDATE {$tableName} SET part_number=? WHERE id=?");
            $stmt->bind_param("si", $part_number, $id);
            $stmt->execute();
            $stmt->close();
        }

        header("Location: index.php?page=detail_sub_workstations&sub_id={$sub_workstation_id}&workstation_id={$workstation_id}&dept_id={$dept_id}&type={$type}");
        exit;
    } else {
        die("Data tidak lengkap!");
    }
}
?>

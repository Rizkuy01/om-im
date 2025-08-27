<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sub_workstation_id = (int) ($_POST['sub_workstation_id'] ?? 0);
    $workstation_id     = (int) ($_POST['workstation_id'] ?? 0);
    $dept_id            = (int) ($_POST['dept_id'] ?? 0);
    $part_number        = trim($_POST['part_number'] ?? '');
    $type               = strtoupper($_POST['type'] ?? 'IM');
    $uploaded           = $_FILES['uploaded_file'] ?? null;

    // tentukan tabel sesuai type
    $tableName = ($type === 'OM') ? 'data_om' : 'data_im';
    $suffix    = ($type === 'OM') ? '-OM' : '-IM';

    $redirect = "index.php?page=detail_sub_workstations&sub_id={$sub_workstation_id}&workstation_id={$workstation_id}&dept_id={$dept_id}&type={$type}";

    // validasi awal
    if ($sub_workstation_id <= 0 || $part_number === '' || !$uploaded) {
        $_SESSION['alert'] = [
            "type" => "error",
            "title" => "Data tidak lengkap",
            "message" => "Harap lengkapi semua data sebelum submit."
        ];
        header("Location: $redirect");
        exit;
    }

    $file_ext = strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['pdf', 'png', 'jpg', 'jpeg'];

    if (!in_array($file_ext, $allowed_ext)) {
        $_SESSION['alert'] = [
            "type" => "error",
            "title" => "Format tidak valid",
            "message" => "Hanya file PDF, PNG, JPG, atau JPEG yang diizinkan."
        ];
        header("Location: $redirect");
        exit;
    }

    // cek apakah part_number sudah ada di tabel sesuai type (IM atau OM)
    $stmt = $connIMOM->prepare("SELECT id FROM {$tableName} WHERE part_number = ?");
    $stmt->bind_param("s", $part_number);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

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

    // rename file jadi partnumber-IM/OM
    $newFileName = $part_number . $suffix . "." . $file_ext;
    $targetFile  = $basePath . $newFileName;

    // jika replace data lama
    if (isset($_POST['replace']) && $existing) {
        if (move_uploaded_file($uploaded['tmp_name'], $targetFile)) {
            $stmt = $connIMOM->prepare("UPDATE {$tableName} SET file_name=?, path_name=? WHERE part_number=?");
            $stmt->bind_param("sss", $newFileName, $basePath, $part_number);
            $stmt->execute();
            $stmt->close();

            $_SESSION['alert'] = [
                "type" => "success",
                "title" => "Berhasil",
                "message" => "File berhasil direplace."
            ];
        } else {
            $_SESSION['alert'] = [
                "type" => "error",
                "title" => "Upload gagal",
                "message" => "Tidak bisa menyimpan file yang diupload."
            ];
        }
        header("Location: $redirect");
        exit;
    }

    // kalau data baru
    if ($existing) {
        $_SESSION['alert'] = [
            "type" => "error",
            "title" => "Part Number sudah ada",
            "message" => "Gunakan fitur Edit/Replace untuk mengganti file lama."
        ];
        header("Location: $redirect");
        exit;
    }

    if (move_uploaded_file($uploaded['tmp_name'], $targetFile)) {
        $stmt = $connIMOM->prepare("INSERT INTO {$tableName} (sub_workstation_id, part_number, file_name, path_name) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $sub_workstation_id, $part_number, $newFileName, $basePath);
        $stmt->execute();
        $stmt->close();

        $_SESSION['alert'] = [
            "type" => "success",
            "title" => "Berhasil",
            "message" => "Data berhasil ditambahkan."
        ];
    } else {
        $_SESSION['alert'] = [
            "type" => "error",
            "title" => "Upload gagal",
            "message" => "Tidak bisa memindahkan file ke server."
        ];
    }

    header("Location: $redirect");
    exit;
}
?>

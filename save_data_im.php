<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sub_workstation_id = (int) ($_POST['sub_workstation_id'] ?? 0);
    $workstation_id     = (int) ($_POST['workstation_id'] ?? 0);
    $dept_id            = (int) ($_POST['dept_id'] ?? 0);

    $part_number = trim($_POST['part_number'] ?? '');

    // File Upload
    $uploadDir = "uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = $_FILES['uploaded_file']['name'];
    $fileTmp  = $_FILES['uploaded_file']['tmp_name'];
    $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validasi ekstensi
    $allowedExt = ['pdf','png','jpg','jpeg'];
    if (!in_array($fileExt, $allowedExt)) {
        die("Ekstensi file tidak diizinkan.");
    }

    // Generate nama unik
    $newFileName = uniqid() . "_" . basename($fileName);
    $uploadPath = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmp, $uploadPath)) {
        // Simpan ke database
        $stmt = $connIMOM->prepare("
            INSERT INTO data_im (sub_workstation_id, part_number, file_name, path_name) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("isss", $sub_workstation_id, $part_number, $fileName, $uploadPath);

        if ($stmt->execute()) {
            header("Location: index.php?page=detail_sub_workstation&sub_id={$sub_workstation_id}&workstation_id={$workstation_id}&dept_id={$dept_id}");
            exit;
        } else {
            echo "Gagal menyimpan ke database: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Gagal mengupload file.";
    }
} else {
    echo "Invalid request method!";
}

<?php
require_once __DIR__ . '/../config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sub_workstation_id = (int) ($_POST['sub_workstation_id'] ?? 0);
    $workstation_id     = (int) ($_POST['workstation_id'] ?? 0);
    $dept_id            = (int) ($_POST['dept_id'] ?? 0);
    $type               = strtoupper($_POST['type'] ?? 'IM');
    $process_id         = !empty($_POST['process_id']) ? (int) $_POST['process_id'] : null;
    $part_number        = ($type === 'IM') ? trim($_POST['part_number'] ?? '') : null;

    // Ambil semua file (multiple)
    $uploaded = $_FILES['uploaded_files'] ?? null;

    $tableName = ($type === 'OM') ? 'data_om' : 'data_im';
    $suffix    = ($type === 'OM') ? '-OM' : '-IM';
    $redirect  = "../index.php?page=detail_sub_workstations&sub_id={$sub_workstation_id}&workstation_id={$workstation_id}&dept_id={$dept_id}&type={$type}";

    // Validasi dasar
    if ($sub_workstation_id <= 0 || !$process_id || !$uploaded) {
        $_SESSION['alert'] = ["type" => "error", "title" => "Data tidak lengkap", "message" => "Harap lengkapi semua data."];
        header("Location: $redirect");
        exit;
    }

    if ($type === 'IM' && $part_number === '') {
        $_SESSION['alert'] = ["type" => "error", "title" => "Part Number wajib", "message" => "Part number harus diisi untuk IM."];
        header("Location: $redirect");
        exit;
    }

    // Ambil nama folder penyimpanan
    $deptName = $connIMOM->query("SELECT dept_name FROM department WHERE id=$dept_id")->fetch_assoc()['dept_name'] ?? "Dept";
    $subName  = $connIMOM->query("SELECT name FROM sub_workstations WHERE id=$sub_workstation_id")->fetch_assoc()['name'] ?? "SubWS";

    // Hindari spasi di folder (bikin path rusak di URL)
    $deptName = str_replace(' ', '_', $deptName);
    $subName  = str_replace(' ', '_', $subName);

    // Path server & database
    $basePath = __DIR__ . "/../uploads/$deptName/$subName/";
    $dbPath   = "/om-im/uploads/$deptName/$subName/";
    if (!is_dir($basePath)) mkdir($basePath, 0777, true);

    // Ambil nama process (untuk OM)
    $procName = "Process";
    if ($type === 'OM') {
        $procRow = $connIMOM->query("SELECT process_name FROM process WHERE id=$process_id")->fetch_assoc();
        $procName = $procRow['process_name'] ?? "Process";
    }

    // Proses semua file
    $totalFiles = count($uploaded['name']);
    $allowed_ext = ['pdf', 'png', 'jpg', 'jpeg'];
    $successCount = 0;

    for ($i = 0; $i < $totalFiles; $i++) {
        if ($uploaded['error'][$i] !== UPLOAD_ERR_OK) continue;

        $fileName = $uploaded['name'][$i];
        $tmpName  = $uploaded['tmp_name'][$i];
        $file_ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_ext)) continue;

        // Buat nama file baru
        if ($type === 'OM') {
            $fileBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', $procName) . "_" . ($i + 1);
        } else {
            $fileBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', $part_number) . "_" . ($i + 1);
        }

        $newFileName = $fileBase . $suffix . "." . $file_ext;
        $targetFile  = $basePath . $newFileName;

        // Jika nama file sudah ada, tambahkan angka unik
        $counter = 1;
        while (file_exists($targetFile)) {
            $newFileName = $fileBase . "_" . $counter . $suffix . "." . $file_ext;
            $targetFile = $basePath . $newFileName;
            $counter++;
        }

        // Simpan file
        if (move_uploaded_file($tmpName, $targetFile)) {
            if ($type === 'OM') {
                $stmt = $connIMOM->prepare("INSERT INTO data_om (sub_workstation_id, process_id, file_name, path_name) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("iiss", $sub_workstation_id, $process_id, $newFileName, $dbPath);
            } else {
                $stmt = $connIMOM->prepare("INSERT INTO data_im (sub_workstation_id, process_id, part_number, file_name, path_name) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisss", $sub_workstation_id, $process_id, $part_number, $newFileName, $dbPath);
            }
            $stmt->execute();
            $stmt->close();
            $successCount++;
        }
    }

    // Notifikasi hasil
    if ($successCount > 0) {
        $_SESSION['alert'] = ["type" => "success", "title" => "Berhasil", "message" => "$successCount file berhasil diupload."];
    } else {
        $_SESSION['alert'] = ["type" => "error", "title" => "Upload gagal", "message" => "Tidak ada file yang berhasil diupload."];
    }

    header("Location: $redirect");
    exit;
}
?>

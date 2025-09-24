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
    $uploaded           = $_FILES['uploaded_file'] ?? null;

    $tableName = ($type === 'OM') ? 'data_om' : 'data_im';
    $suffix    = ($type === 'OM') ? '-OM' : '-IM';
    $redirect  = "../index.php?page=detail_sub_workstations&sub_id={$sub_workstation_id}&workstation_id={$workstation_id}&dept_id={$dept_id}&type={$type}";

    // 🔎 Validasi dasar
    if ($sub_workstation_id <= 0 || !$process_id || !$uploaded) {
        $_SESSION['alert'] = ["type"=>"error","title"=>"Data tidak lengkap","message"=>"Harap lengkapi semua data."];
        header("Location: $redirect"); exit;
    }

    if ($type === 'IM' && $part_number === '') {
        $_SESSION['alert'] = ["type"=>"error","title"=>"Part Number wajib","message"=>"Part number harus diisi untuk IM."];
        header("Location: $redirect"); exit;
    }

    $file_ext = strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['pdf','png','jpg','jpeg'];
    if (!in_array($file_ext, $allowed_ext)) {
        $_SESSION['alert'] = ["type"=>"error","title"=>"Format salah","message"=>"Hanya PDF/PNG/JPG yang diizinkan."];
        header("Location: $redirect"); exit;
    }

    // 🔒 Untuk OM → hanya boleh 1 file per process
    if ($type === 'OM') {
        $stmt = $connIMOM->prepare("SELECT id FROM data_om WHERE sub_workstation_id=? AND process_id=? LIMIT 1");
        $stmt->bind_param("ii", $sub_workstation_id, $process_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $_SESSION['alert'] = ["type"=>"error","title"=>"Sudah ada file OM","message"=>"Hanya boleh 1 file OM per process."];
            $stmt->close();
            header("Location: $redirect"); exit;
        }
        $stmt->close();
    }

    // 🔎 Untuk IM → cek duplikat part number
    if ($type === 'IM') {
        $stmt = $connIMOM->prepare("SELECT id FROM data_im WHERE part_number=? AND sub_workstation_id=? AND process_id=?");
        $stmt->bind_param("sii", $part_number, $sub_workstation_id, $process_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $_SESSION['alert'] = ["type"=>"error","title"=>"Duplikat Model","message"=>"Part number sudah ada untuk process ini."];
            $stmt->close();
            header("Location: $redirect"); exit;
        }
        $stmt->close();
    }

    // 🔧 Ambil nama folder penyimpanan
    $deptName = $connIMOM->query("SELECT dept_name FROM department WHERE id=$dept_id")->fetch_assoc()['dept_name'] ?? "Dept";
    $subName  = $connIMOM->query("SELECT name FROM sub_workstations WHERE id=$sub_workstation_id")->fetch_assoc()['name'] ?? "SubWS";

    $basePath = __DIR__ . "/../uploads/$deptName/$subName/";
    $dbPath   = "uploads/$deptName/$subName/";
    if (!is_dir($basePath)) mkdir($basePath, 0777, true);

    // 🔧 Rename file
    if ($type === 'OM') {
        // Ambil nama process untuk rename
        $procRow = $connIMOM->query("SELECT process_name FROM process WHERE id=$process_id")->fetch_assoc();
        $procName = $procRow['process_name'] ?? "Process";
        $fileBase = preg_replace('/[^A-Za-z0-9_\-]/', '_', $procName); // amanin nama file
    } else {
        $fileBase = $part_number;
    }

    $newFileName = $fileBase . $suffix . "." . $file_ext;
    $targetFile  = $basePath . $newFileName;

    // ⬆️ Upload file
    if (move_uploaded_file($uploaded['tmp_name'], $targetFile)) {
        if ($type === 'OM') {
            $stmt = $connIMOM->prepare("INSERT INTO data_om (sub_workstation_id, process_id, file_name, path_name) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $sub_workstation_id, $process_id, $newFileName, $dbPath);
        } else {
            $stmt = $connIMOM->prepare("INSERT INTO data_im (sub_workstation_id, process_id, part_number, file_name, path_name) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $sub_workstation_id, $process_id, $part_number, $newFileName, $dbPath);
        }
        $stmt->execute();
        $stmt->close();

        $_SESSION['alert'] = ["type"=>"success","title"=>"Berhasil","message"=>"Data berhasil ditambahkan."];
    } else {
        $_SESSION['alert'] = ["type"=>"error","title"=>"Upload gagal","message"=>"File gagal disimpan ke server."];
    }

    header("Location: $redirect");
    exit;
}
?>

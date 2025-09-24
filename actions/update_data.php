<?php
require_once __DIR__ . '/../config.php';
session_start();

$errorMessage = null;
$redirectUrl  = "../index.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['alert'] = ["type"=>"error","title"=>"Method tidak valid","message"=>"Akses tidak diperbolehkan."];
    header("Location: ../index.php"); exit;
}

$id                 = (int) ($_POST['id'] ?? 0);
$sub_workstation_id = (int) ($_POST['sub_workstation_id'] ?? 0);
$workstation_id     = (int) ($_POST['workstation_id'] ?? 0);
$dept_id            = (int) ($_POST['dept_id'] ?? 0);
$type               = strtoupper(trim($_POST['type'] ?? 'IM'));
$process_id         = isset($_POST['process_id']) && $_POST['process_id'] !== '' ? (int)$_POST['process_id'] : null;
$part_number        = trim($_POST['part_number'] ?? '');
$uploaded           = $_FILES['uploaded_file'] ?? null;

$redirectUrl = "../index.php?page=detail_sub_workstations&sub_id={$sub_workstation_id}&workstation_id={$workstation_id}&dept_id={$dept_id}&type={$type}";

if ($id <= 0 || $sub_workstation_id <= 0) {
    $_SESSION['alert'] = ["type"=>"error","title"=>"Data tidak lengkap","message"=>"Record / Sub workstation tidak valid."];
    header("Location: $redirectUrl"); exit;
}

// fetch old data
if ($type === 'OM') {
    $stmtOld = $connIMOM->prepare("SELECT id, process_id, file_name, path_name FROM data_om WHERE id = ? LIMIT 1");
} else {
    $stmtOld = $connIMOM->prepare("SELECT id, process_id, part_number, file_name, path_name FROM data_im WHERE id = ? LIMIT 1");
}
$stmtOld->bind_param("i", $id);
$stmtOld->execute();
$oldData = $stmtOld->get_result()->fetch_assoc();
$stmtOld->close();

if (!$oldData) {
    $_SESSION['alert'] = ["type"=>"error","title"=>"Tidak ditemukan","message"=>"Record yang diedit tidak ditemukan."];
    header("Location: $redirectUrl"); exit;
}

// Validasi: OM harus punya process_id
if ($type === 'OM' && !$process_id) {
    $_SESSION['alert'] = ["type"=>"error","title"=>"Process wajib","message"=>"Pilih process untuk OM."];
    header("Location: $redirectUrl"); exit;
}

// Validasi: IM harus punya part_number
if ($type === 'IM' && $part_number === '') {
    $_SESSION['alert'] = ["type"=>"error","title"=>"Part Number wajib","message"=>"Part number tidak boleh kosong."];
    header("Location: $redirectUrl"); exit;
}

// cek duplikat untuk OM (hanya 1 OM per process per sub_ws)
if ($type === 'OM') {
    $stmtChk = $connIMOM->prepare("SELECT id FROM data_om WHERE sub_workstation_id = ? AND process_id = ? AND id <> ? LIMIT 1");
    $stmtChk->bind_param("iii", $sub_workstation_id, $process_id, $id);
    $stmtChk->execute();
    $exists = $stmtChk->get_result()->fetch_assoc();
    $stmtChk->close();
    if ($exists) {
        $_SESSION['alert'] = ["type"=>"error","title"=>"Duplikat OM","message"=>"Sudah ada file OM untuk process ini pada line yang sama."];
        header("Location: $redirectUrl"); exit;
    }
} else {
    // IM: cek duplikat part_number
    $stmtChk = $connIMOM->prepare("SELECT id FROM data_im WHERE part_number = ? AND sub_workstation_id = ? AND process_id = ? AND id <> ? LIMIT 1");
    $stmtChk->bind_param("siii", $part_number, $sub_workstation_id, $process_id, $id);
    $stmtChk->execute();
    $exists = $stmtChk->get_result()->fetch_assoc();
    $stmtChk->close();
    if ($exists) {
        $_SESSION['alert'] = ["type"=>"error","title"=>"Duplikat Model","message"=>"Part number sudah ada untuk process ini."];
        header("Location: $redirectUrl"); exit;
    }
}

// ambil nama folder (dept & sub)
$deptName = $connIMOM->prepare("SELECT dept_name FROM department WHERE id = ? LIMIT 1");
$deptName->bind_param("i", $dept_id);
$deptName->execute();
$deptNameRes = $deptName->get_result()->fetch_assoc();
$deptName->close();
$deptLabel = $deptNameRes['dept_name'] ?? 'Dept';

$subNameQ = $connIMOM->prepare("SELECT name FROM sub_workstations WHERE id = ? LIMIT 1");
$subNameQ->bind_param("i", $sub_workstation_id);
$subNameQ->execute();
$subNameRes = $subNameQ->get_result()->fetch_assoc();
$subNameQ->close();
$subLabel = $subNameRes['name'] ?? 'SubWS';

$basePath = __DIR__ . "/../uploads/{$deptLabel}/{$subLabel}/";
$dbPath   = "uploads/{$deptLabel}/{$subLabel}/";
if (!is_dir($basePath)) mkdir($basePath, 0777, true);

// jika upload file baru
if ($uploaded && isset($uploaded['tmp_name']) && is_uploaded_file($uploaded['tmp_name'])) {
    $file_ext = strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION));
    $allowed_ext = ['pdf','png','jpg','jpeg'];
    if (!in_array($file_ext, $allowed_ext)) {
        $_SESSION['alert'] = ["type"=>"error","title"=>"Format tidak valid","message"=>"Hanya PDF/PNG/JPG yang diizinkan."];
        header("Location: $redirectUrl"); exit;
    }

    // OM → ambil nama process untuk penamaan file
    $fileBase = '';
    if ($type === 'OM') {
    // OM → gunakan process_name
    $p = $connIMOM->query("SELECT process_name FROM process WHERE id = {$process_id}")->fetch_assoc();
    $procName = $p['process_name'] ?? "PROCESS";
    $fileBase = preg_replace('/[^\w\- \(\)]/u', '', $procName) . " - OM";
} else {
    // IM → gunakan part_number dari DB
    $pn = $oldData['part_number'] ?? 'IM_FILE';
    $fileBase = preg_replace('/[^\w\- \(\)]/u', '', $pn) . " - IM";
}
$newFileName = $fileBase . "." . $file_ext;

    $targetFile  = $basePath . $newFileName;

    // hapus file lama bila ada
    if (!empty($oldData['file_name']) && !empty($oldData['path_name'])) {
        $oldFileFull = __DIR__ . "/../" . $oldData['path_name'] . $oldData['file_name'];
        if (file_exists($oldFileFull) && is_file($oldFileFull)) {
            @unlink($oldFileFull);
        }
    }

    // pindahkan file baru
    if (!move_uploaded_file($uploaded['tmp_name'], $targetFile)) {
        $_SESSION['alert'] = ["type"=>"error","title"=>"Upload gagal","message"=>"Gagal memindahkan file ke server."];
        header("Location: $redirectUrl"); exit;
    }

    // Update DB
    if ($type === 'OM') {
        $stmtUp = $connIMOM->prepare("UPDATE data_om SET file_name = ?, path_name = ?, process_id = ? WHERE id = ?");
        $stmtUp->bind_param("ssii", $newFileName, $dbPath, $process_id, $id);
    } else {
        // Part Number read only
        $stmtUp = $connIMOM->prepare("UPDATE data_im SET file_name = ?, path_name = ?, process_id = ? WHERE id = ?");
        $stmtUp->bind_param("ssii", $newFileName, $dbPath, $process_id, $id);
    }
    $stmtUp->execute();
    $stmtUp->close();

    $_SESSION['alert'] = ["type"=>"success","title"=>"Berhasil","message"=>"File berhasil diperbarui."];
    header("Location: $redirectUrl"); exit;
}

// jika tidak upload file baru -> hanya update process_id
if ($process_id !== null && (int)$process_id !== (int)($oldData['process_id'] ?? 0)) {
    if ($type === 'OM') {
        // cek duplikat
        $stmtD = $connIMOM->prepare("SELECT id FROM data_om WHERE sub_workstation_id = ? AND process_id = ? AND id <> ? LIMIT 1");
        $stmtD->bind_param("iii", $sub_workstation_id, $process_id, $id);
        $stmtD->execute();
        $exists2 = $stmtD->get_result()->fetch_assoc();
        $stmtD->close();
        if ($exists2) {
            $_SESSION['alert'] = ["type"=>"error","title"=>"Duplikat OM","message"=>"Sudah ada file OM untuk process ini."];
            header("Location: $redirectUrl"); exit;
        }
        $stmtU = $connIMOM->prepare("UPDATE data_om SET process_id = ? WHERE id = ?");
        $stmtU->bind_param("ii", $process_id, $id);
        $stmtU->execute();
        $stmtU->close();
    } else {
        $stmtU = $connIMOM->prepare("UPDATE data_im SET process_id = ? WHERE id = ?");
        $stmtU->bind_param("ii", $process_id, $id);
        $stmtU->execute();
        $stmtU->close();
    }
    $_SESSION['alert'] = ["type"=>"success","title"=>"Berhasil","message"=>"Process berhasil diperbarui."];
    header("Location: $redirectUrl"); exit;
}

// Alert jika tidak ada perubahan apapun
$_SESSION['alert'] = ["type"=>"info","title"=>"Tidak ada perubahan","message"=>"Tidak ada perubahan yang disimpan."];
header("Location: $redirectUrl");
exit;

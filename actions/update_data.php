<?php
require_once __DIR__ . '/../config.php';
session_start();

$errorMessage = null;
$redirectUrl  = "../index.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id                 = (int) ($_POST['id'] ?? 0);
    $sub_workstation_id = (int) ($_POST['sub_workstation_id'] ?? 0);
    $workstation_id     = (int) ($_POST['workstation_id'] ?? 0);
    $dept_id            = (int) ($_POST['dept_id'] ?? 0);
    $part_number        = trim($_POST['part_number'] ?? '');
    $type               = strtoupper($_POST['type'] ?? 'IM');
    $uploaded           = $_FILES['uploaded_file'] ?? null;

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

        // Path fisik dan path DB
        $basePath = __DIR__ . "/../uploads/$deptName/$subName/";
        $dbPath   = "uploads/$deptName/$subName/";

        if (!is_dir($basePath)) {
            mkdir($basePath, 0777, true);
        }

        // ambil data lama untuk hapus file lama
        $oldQ = $connIMOM->prepare("SELECT file_name, path_name FROM {$tableName} WHERE id = ?");
        $oldQ->bind_param("i", $id);
        $oldQ->execute();
        $oldData = $oldQ->get_result()->fetch_assoc();
        $oldQ->close();

        if ($uploaded && $uploaded['error'] === UPLOAD_ERR_OK) {
            $file_ext = strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['pdf', 'png', 'jpg', 'jpeg'];

            if (!in_array($file_ext, $allowed_ext)) {
                $errorMessage = "Format file tidak diizinkan.";
            } else {
                $newFileName = $part_number . $suffix . "." . $file_ext;
                $targetFile  = $basePath . $newFileName;

                // hapus file lama (gunakan path lama dari DB)
                if (!empty($oldData['file_name'])) {
                    $oldFile = __DIR__ . "/../" . $oldData['path_name'] . $oldData['file_name'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }

                if (move_uploaded_file($uploaded['tmp_name'], $targetFile)) {
                    $stmt = $connIMOM->prepare("UPDATE {$tableName} 
                        SET file_name=?, path_name=?, part_number=? 
                        WHERE id=?");
                    $stmt->bind_param("sssi", $newFileName, $dbPath, $part_number, $id);
                    $stmt->execute();
                    $stmt->close();
                } else {
                    $errorMessage = "Gagal upload file baru.";
                }
            }
        } else {
            // update part number
            $stmt = $connIMOM->prepare("UPDATE {$tableName} SET part_number=? WHERE id=?");
            $stmt->bind_param("si", $part_number, $id);
            $stmt->execute();
            $stmt->close();
        }

        // redirect
        $redirectUrl = "../index.php?page=detail_sub_workstations&sub_id={$sub_workstation_id}&workstation_id={$workstation_id}&dept_id={$dept_id}&type={$type}";
    } else {
        $errorMessage = "Data tidak lengkap!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Error Upload</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<?php if ($errorMessage): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Upload Gagal',
    text: '<?= addslashes($errorMessage) ?>',
    confirmButtonColor: '#d33'
}).then(() => {
    window.location.href = "<?= $redirectUrl ?>";
});
</script>
<?php else: ?>
<script>
window.location.href = "<?= $redirectUrl ?>";
</script>
<?php endif; ?>
</body>
</html>

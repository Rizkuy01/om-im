<?php
function checkMonitoringAccess($connUser, $connIMOM, $npk, $machine) {
    // Ambil user dari lembur1.ct_users
    $stmt = $connUser->prepare("SELECT dept FROM ct_users WHERE npk = ? LIMIT 1");
    $stmt->bind_param("s", $npk);
    $stmt->execute();
    $resUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$resUser) {
        return [
            'type' => 'error',
            'title' => 'Akses Ditolak',
            'message' => "NPK {$npk} tidak ditemukan."
        ];  
    }

    $deptUser = ucwords(strtolower(trim($resUser['dept'])));

    // Normalisasi dept ke om_im.department
    $stmt = $connIMOM->prepare("SELECT id, dept_name FROM department WHERE LOWER(dept_name) = LOWER(?) LIMIT 1");
    $stmt->bind_param("s", $deptUser);
    $stmt->execute();
    $rowDept = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$rowDept) {
        return [
            'type' => 'error',
            'title' => 'Akses Ditolak',
            'message' => "Dept {$deptUser} tidak terdaftar di sistem OM/IM.",
            'redirect' => "login.php"
        ];
    }

    $deptName = $rowDept['dept_name'];

    // get sub_workstation by ID atau by NAME 
    $subWs = null;
    if (ctype_digit((string)$machine)) {
        $machineId = (int)$machine;
        $stmt = $connIMOM->prepare("SELECT id, name FROM sub_workstations WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $machineId);
    } else {
        $machineName = trim($machine);
        $stmt = $connIMOM->prepare("SELECT id, name FROM sub_workstations WHERE LOWER(name) = LOWER(?) LIMIT 1");
        $stmt->bind_param("s", $machineName);
    }
    $stmt->execute();
    $subWs = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$subWs) {
        return [
            'type' => 'error',
            'title' => 'Akses Ditolak',
            'message' => "Mesin ".htmlspecialchars($machine)." tidak ditemukan.",
            'redirect' => "index.php?page=workstations&dept_id=" . $rowDept['id']
        ];
    }

    $subWsId   = (int)$subWs['id'];
    $subWsName = $subWs['name'];

    // 4) Ambil OM terbaru (Production)
    $stmt = $connIMOM->prepare("SELECT id FROM data_om WHERE sub_workstation_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $subWsId);
    $stmt->execute();
    $lastOM = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // 5) Ambil IM terbaru (QA/MIS)
    $lastIM = null;
    if (in_array($deptName, ['QA','MIS'], true)) {
        $stmt = $connIMOM->prepare("SELECT id FROM data_im WHERE sub_workstation_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("i", $subWsId);
        $stmt->execute();
        $lastIM = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    if (!$lastOM && !$lastIM) {
        return [
            'type' => 'error',
            'title' => 'Data Kosong',
            'message' => "Belum ada file OM/IM terbaru untuk mesin {$subWsName}.",
            'redirect' => "index.php?page=workstations&dept_id=" . $rowDept['id']
        ];
    }

    //redirect ke monitoring_detail 
    return [
        'type'     => 'success',
        'title'    => 'Akses Diterima',
        'message'  => "Menampilkan file terbaru untuk mesin {$subWsName}.",
        'redirect' => "monitoring_detail.php?npk={$npk}&machine=" . urlencode($subWsName)
    ];
}

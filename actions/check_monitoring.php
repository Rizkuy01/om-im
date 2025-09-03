<?php
function checkMonitoringAccess($connUser, $connIMOM, $npk, $machineId) {
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

    // Cari dept di om_im.department
    $stmt = $connIMOM->prepare("SELECT id, dept_name FROM department WHERE LOWER(dept_name) = LOWER(?) LIMIT 1");
    $stmt->bind_param("s", $deptUser);
    $stmt->execute();
    $rowDept = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$rowDept) {
        return [
            'type' => 'error',
            'title' => 'Akses Ditolak',
            'message' => "Dept {$deptUser} tidak terdaftar di sistem OM/IM."
        ];
    }

    $deptId   = $rowDept['id'];
    $deptName = $rowDept['dept_name'];

    // ambil sub_workstation berdasarkan ID 
    $stmt = $connIMOM->prepare("SELECT id, name FROM sub_workstations WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $machineId);
    $stmt->execute();
    $rowSub = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if (!$rowSub) {
        return [
            'type' => 'error',
            'title' => 'Akses Ditolak',
            'message' => "Mesin dengan ID {$machineId} tidak ditemukan."
        ];
    }
    
    $subWsId   = $rowSub['id'];
    $subWsName = $rowSub['name'];

    // Ambil OM terbaru
    $stmt = $connIMOM->prepare("SELECT * FROM data_om WHERE sub_workstation_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $subWsId);
    $stmt->execute();
    $lastOM = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Ambil IM terbaru
    $lastIM = null;
    if (in_array($deptName, ['QA','MIS'])) {
        $stmt = $connIMOM->prepare("SELECT * FROM data_im WHERE sub_workstation_id = ? ORDER BY id DESC LIMIT 1");
        $stmt->bind_param("i", $subWsId);
        $stmt->execute();
        $lastIM = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    }

    if (!$lastOM && !$lastIM) {
        return [
            'type' => 'error',
            'title' => 'Data Kosong',
            'message' => "Belum ada file OM/IM terbaru untuk mesin {$subWsName}."
        ];
    }

    // redirect ke monitoring_detail.php
    return [
        'type' => 'success',
        'title' => 'Akses Diterima',
        'message' => "Menampilkan file terbaru untuk mesin {$subWsName}.",
        'redirect' => "monitoring_detail.php?npk={$npk}&machine=" . urlencode($subWsName)
    ];
}

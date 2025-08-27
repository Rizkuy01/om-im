<?php
// monitoring_check.php
function checkMonitoringAccess($connUser, $connIMOM, $npk, $machine) {
    $alert = null;

    if ($npk) {
        // cek NPK di db lembur1.ct_users
        $stmt = $connUser->prepare("SELECT dept, full_name FROM ct_users WHERE npk = ?");
        $stmt->bind_param("s", $npk);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user) {
            $deptUser = strtolower(trim($user['dept']));

            // cek di db om_im.department
            $resDept   = $connIMOM->query("SELECT dept_name FROM department");
            $foundDept = null;
            while ($row = $resDept->fetch_assoc()) {
                if (strtolower(trim($row['dept_name'])) === $deptUser) {
                    $foundDept = $row['dept_name'];
                    break;
                }
            }

            if ($foundDept) {
                $alert = [
                    "type" => "success",
                    "title" => "Akses Diterima",
                    "message" => "Monitoring mesin: {$machine}, Dept: {$foundDept}"
                ];
            } else {
                $alert = [
                    "type" => "error",
                    "title" => "Dept Tidak Valid",
                    "message" => "Dept {$user['dept']} tidak memiliki akses monitoring"
                ];
            }
        } else {
            $alert = [
                "type" => "error",
                "title" => "NPK Tidak Ditemukan",
                "message" => "NPK {$npk} tidak ada di database."
            ];
        }
    }

    return $alert;
}

<?php
require_once 'config.php';

header('Content-Type: application/json');

$npk = $_GET['npk'] ?? null;
$machines = [];

if ($npk) {
    // Ambil dept dari db lembur1
    $stmt = $connUser->prepare("SELECT dept FROM ct_users WHERE npk = ?");
    $stmt->bind_param("s", $npk);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user) {
        $dept = $user['dept'];

        //  dept_id.om_im, cocokkan data departemen
        $stmt = $connIMOM->prepare("SELECT id FROM department WHERE LOWER(dept_name) LIKE CONCAT('%', LOWER(?), '%') LIMIT 1");
        $stmt->bind_param("s", $dept);
        $stmt->execute();
        $deptResult = $stmt->get_result();
        $deptRow = $deptResult->fetch_assoc();
        $stmt->close();

        if ($deptRow) {
            $deptId = $deptRow['id'];

            // Ambil mesin (sub_workstations) sesuai dept_id
            $sql = "SELECT s.id, s.name 
                    FROM sub_workstations s
                    JOIN workstations w ON s.workstation_id = w.id
                    WHERE w.dept_id = ?";
            $stmt = $connIMOM->prepare($sql);
            $stmt->bind_param("i", $deptId);
            $stmt->execute();
            $subs = $stmt->get_result();

            while ($row = $subs->fetch_assoc()) {
                $machines[] = [
                    'id' => $row['id'],
                    'name' => $row['name']
                ];
            }
            $stmt->close();
        }
    }
}

echo json_encode(['machines' => $machines]);

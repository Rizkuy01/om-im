<?php
require_once 'config.php';
session_start();

$npk       = $_GET['npk'] ?? null;
$machine   = $_GET['machine'] ?? null;
$processId = isset($_GET['process_id']) ? (int)$_GET['process_id'] : null;
$type      = strtoupper(trim($_GET['type'] ?? '')); // 🔥 tambahkan

$errorMessage = null; 
$lastOM = $lastIM = null;
$procName = null;

if (!$npk || !$machine) {
    $errorMessage = "NPK dan mesin harus dipilih!";
} else {
    // --- Cek user dept
    $stmt = $connUser->prepare("SELECT dept FROM ct_users WHERE npk = ? LIMIT 1");
    $stmt->bind_param("s", $npk);
    $stmt->execute();
    $resUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$resUser) {
        $errorMessage = "NPK {$npk} tidak ditemukan.";
    } else {
        $deptUser = ucwords(strtolower(trim($resUser['dept'])));

        $stmt = $connIMOM->prepare("SELECT id, dept_name FROM department WHERE LOWER(dept_name) = LOWER(?) LIMIT 1");
        $stmt->bind_param("s", $deptUser);
        $stmt->execute();
        $rowDept = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$rowDept) {
            $errorMessage = "Departemen {$deptUser} tidak terdaftar di om_im.";
        } else {
            $deptId   = $rowDept['id'];
            $deptName = $rowDept['dept_name'];

            // --- Cari sub workstation
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
            $rowSub = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$rowSub) {
                $errorMessage = "Mesin {$machine} tidak ditemukan.";
            } else {
                $subWsId   = $rowSub['id'];
                $subWsName = $rowSub['name'];

                // --- Ambil nama process
                if ($processId) {
                    $stmt = $connIMOM->prepare("SELECT process_name FROM process WHERE id = ? AND sub_workstations_id = ?");
                    $stmt->bind_param("ii", $processId, $subWsId);
                    $stmt->execute();
                    $procRow = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    $procName = $procRow['process_name'] ?? null;
                }

                // 🔥 Tentukan ambil file sesuai type
                if ($type === 'OM') {
                    $sql = $processId
                        ? "SELECT * FROM data_om WHERE sub_workstation_id = ? AND process_id = ? ORDER BY id DESC LIMIT 1"
                        : "SELECT * FROM data_om WHERE sub_workstation_id = ? ORDER BY id DESC LIMIT 1";
                    $stmt = $connIMOM->prepare($sql);
                    if ($processId) $stmt->bind_param("ii", $subWsId, $processId);
                    else $stmt->bind_param("i", $subWsId);
                    $stmt->execute();
                    $lastOM = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                } elseif ($type === 'IM') {
                    $sql = $processId
                        ? "SELECT * FROM data_im WHERE sub_workstation_id = ? AND process_id = ? ORDER BY id DESC LIMIT 1"
                        : "SELECT * FROM data_im WHERE sub_workstation_id = ? ORDER BY id DESC LIMIT 1";
                    $stmt = $connIMOM->prepare($sql);
                    if ($processId) $stmt->bind_param("ii", $subWsId, $processId);
                    else $stmt->bind_param("i", $subWsId);
                    $stmt->execute();
                    $lastIM = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                } else {
                    // default ambil keduanya
                    $stmt = $connIMOM->prepare("SELECT * FROM data_om WHERE sub_workstation_id = ? ORDER BY id DESC LIMIT 1");
                    $stmt->bind_param("i", $subWsId);
                    $stmt->execute();
                    $lastOM = $stmt->get_result()->fetch_assoc();
                    $stmt->close();

                    $stmt = $processId
                        ? $connIMOM->prepare("SELECT * FROM data_im WHERE sub_workstation_id = ? AND process_id = ? ORDER BY id DESC LIMIT 1")
                        : $connIMOM->prepare("SELECT * FROM data_im WHERE sub_workstation_id = ? ORDER BY id DESC LIMIT 1");
                    if ($processId) $stmt->bind_param("ii", $subWsId, $processId);
                    else $stmt->bind_param("i", $subWsId);
                    $stmt->execute();
                    $lastIM = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                }

                if (!$lastOM && !$lastIM) {
                    $errorMessage = "Belum ada file OM/IM terbaru untuk mesin {$subWsName}" . ($procName ? " - Proses {$procName}" : "");
                }
            }
        }
    }
}
?>



<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Monitoring - Latest Files</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<?php if ($errorMessage): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Akses Ditolak',
    text: <?= json_encode($errorMessage) ?>,
    confirmButtonColor: '#d33'
}).then(() => {
    window.history.back();
});
</script>
<?php else: ?>

  <!-- HEADER -->
  <div class="bg-red-600 text-white px-6 py-4 shadow-md text-center">
    <h1 class="text-2xl font-bold">Monitoring Result</h1>
    <p class="mt-1 text-sm">
        <strong>NPK:</strong> <?= htmlspecialchars($npk) ?> | 
        <strong>Dept:</strong> <?= htmlspecialchars($deptName) ?> | 
        <strong>Mesin:</strong> <?= htmlspecialchars($subWsName) ?> |
        <?php if (!empty($lastOM)): ?>
            <strong>Part (OM):</strong> <?= htmlspecialchars($lastOM['part_number']) ?>
        <?php endif; ?>
        <?php if (!empty($procName)): ?>
            | <strong>Proses:</strong> <?= htmlspecialchars($procName) ?>
        <?php endif; ?>
    </p>
  </div>

  <!-- MAIN CONTENT -->
  <div class="flex-1 p-6 space-y-8">
      
      <?php if (!empty($lastOM)): ?>
      <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex justify-between items-center mb-3">
          <h2 class="text-lg font-semibold text-green-700">
            OM File (Latest) - <span id="clockOM"></span>
          </h2>
          <a href="index.php" 
             class="bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold px-4 py-2 rounded shadow">
             ← Kembali
          </a>
        </div>
        <iframe src="<?= htmlspecialchars($lastOM['path_name'].$lastOM['file_name']) ?>" 
                class="w-full h-[80vh] border rounded" frameborder="0"></iframe>
      </div>
      <?php endif; ?>

      <?php if (!empty($lastIM)): ?>
      <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex justify-between items-center mb-3">
          <h2 class="text-lg font-semibold text-blue-700">
            IM File (Latest) - <span id="clockIM"></span>
          </h2>
          <a href="index.php" 
             class="bg-gray-500 hover:bg-gray-600 text-white text-sm font-semibold px-4 py-2 rounded shadow">
             ← Kembali
          </a>
        </div>
        <iframe src="<?= htmlspecialchars($lastIM['path_name'].$lastIM['file_name']) ?>" 
                class="w-full h-[80vh] border rounded" frameborder="0"></iframe>
      </div>
      <?php endif; ?>

  </div>

  <script>
  function updateClocks() {
      const now = new Date();
      const timeString = now.toLocaleTimeString('id-ID', {
          hour: '2-digit',
          minute: '2-digit',
          second: '2-digit'
      });
      if (document.getElementById("clockOM")) {
          document.getElementById("clockOM").textContent = timeString;
      }
      if (document.getElementById("clockIM")) {
          document.getElementById("clockIM").textContent = timeString;
      }
  }
  setInterval(updateClocks, 1000);
  updateClocks();
  </script>

<?php endif; ?>

</body>
</html>

<?php
require_once 'config.php';
session_start();

$npk   = $_GET['npk'] ?? null;
$subId = $_GET['machine'] ?? null;

if (!$npk || !$subId) {
    die("NPK atau Line tidak valid.");
}

// Ambil sub workstation
$stmt = $connIMOM->prepare("SELECT * FROM sub_workstations WHERE id = ?");
$stmt->bind_param("i", $subId);
$stmt->execute();
$subWs = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Ambil daftar proses
$stmt = $connIMOM->prepare("SELECT * FROM process WHERE sub_workstations_id = ?");
$stmt->bind_param("i", $subId);
$stmt->execute();
$processes = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Pilih Process</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <div class="bg-red-600 text-white py-4 shadow-md text-center">
        <h1 class="text-2xl font-bold tracking-wide">
            Pilih Process - <?= htmlspecialchars($subWs['name']) ?>
        </h1>
        <p class="text-sm text-red-100">NPK: <?= htmlspecialchars($npk) ?></p>
    </div>

    <div class="flex-1 p-6">
        <!-- grid responsive-->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-4 max-w-6xl mx-auto">
            <?php if ($processes->num_rows > 0): ?>
                <?php while ($proc = $processes->fetch_assoc()): ?>
                    <?php
                    $procId = (int)$proc['id'];
                    $hasFile = false;

                    // cek data_om
                    $chk = $connIMOM->prepare("SELECT id FROM data_om WHERE sub_workstation_id = ? AND process_id = ? LIMIT 1");
                    $chk->bind_param("ii", $subId, $procId);
                    $chk->execute();
                    $resChk = $chk->get_result();
                    if ($resChk && $resChk->num_rows > 0) {
                        $hasFile = true;
                    }
                    $chk->close();

                    // jika belum ada di data_om, cek data_im
                    if (!$hasFile) {
                        $chk = $connIMOM->prepare("SELECT id FROM data_im WHERE sub_workstation_id = ? AND process_id = ? LIMIT 1");
                        $chk->bind_param("ii", $subId, $procId);
                        $chk->execute();
                        $resChk = $chk->get_result();
                        if ($resChk && $resChk->num_rows > 0) {
                            $hasFile = true;
                        }
                        $chk->close();
                    }

                    // link bila ada file
                    $link = $hasFile 
                        ? "monitoring_detail.php?npk=" . urlencode($npk) . "&machine=" . urlencode($subId) . "&process_id=" . $procId . "&type=OM"
                        : "javascript:void(0)";
                    ?>
                    <a href="<?= $link ?>"
                       <?= $hasFile ? '' : "onclick=\"showNoFileAlert('".htmlspecialchars(addslashes($proc['process_name']))."'); return false;\"" ?>
                       class="block rounded-lg p-4 text-left flex items-center justify-between transition duration-200
                              <?= $hasFile
                                   ? 'bg-white border border-red-200 shadow-sm hover:shadow-md hover:border-red-400'
                                   : 'bg-gray-50 border border-gray-200 hover:border-red-300' ?>">

                        <div class="flex flex-col">
                            <h2 class="text-sm font-semibold <?= $hasFile ? 'text-red-700' : 'text-gray-700' ?>">
                                <?= htmlspecialchars($proc['process_name']) ?>
                            </h2>
                            <p class="text-xs <?= $hasFile ? 'text-red-600' : 'text-gray-500' ?> mt-1">
                                Sub Workstation: <?= htmlspecialchars($subWs['name']) ?>
                            </p>
                        </div>

                        <div class="w-10 h-10 rounded-full flex-shrink-0 ml-4 flex items-center justify-center
                                    <?= $hasFile ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-400' ?>">
                            <i class="fa-solid <?= $hasFile ? 'fa-file' : 'fa-circle-xmark' ?> text-lg"></i>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full bg-gray-200 rounded-lg p-6 text-center text-gray-600">
                    Tidak ada process
                </div>
            <?php endif; ?>
        </div>

        <div class="mt-8 text-center">
            <a href="index.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow">
                ← Kembali
            </a>
        </div>
    </div>

    <script>
    function showNoFileAlert(processName) {
        Swal.fire({
            icon: 'warning',
            title: 'Belum Ada File',
            text: `Process "${processName}" belum memiliki file OM/IM.`,
            confirmButtonColor: '#d33'
        });
    }
    </script>
</body>
</html>

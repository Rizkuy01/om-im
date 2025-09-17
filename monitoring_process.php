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
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    <div class="bg-red-600 text-white py-4 shadow-md text-center">
        <h1 class="text-2xl font-bold tracking-wide">
            Pilih Process - <?= htmlspecialchars($subWs['name']) ?>
        </h1>
        <p class="text-sm text-red-100">NPK: <?= htmlspecialchars($npk) ?></p>
    </div>

    <div class="flex-1 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-w-4xl mx-auto">
            <?php if ($processes->num_rows > 0): ?>
                <?php while ($proc = $processes->fetch_assoc()): ?>
                    <a href="monitoring_detail.php?npk=<?= urlencode($npk) ?>&machine=<?= $subId ?>&process_id=<?= $proc['id'] ?>"
                        class="block bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:border-red-500 transition duration-200 p-4 text-left flex items-center justify-between group">
                        <div class="flex flex-col">
                            <h2 class="text-base font-semibold text-gray-800 group-hover:text-red-600">
                                <?= htmlspecialchars($proc['process_name']) ?>
                            </h2>
                            <p class="text-sm text-gray-500 mt-1">
                                Sub Workstation: <?= htmlspecialchars($subWs['name']) ?>
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-gray-200 rounded-full flex-shrink-0 ml-4 flex items-center justify-center">
                            <i class="fa-solid fa-wrench"></i>
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

</body>
</html>

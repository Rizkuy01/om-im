<?php
require_once 'config.php';
session_start();

$npk       = $_GET['npk'] ?? null;
$machine   = $_GET['machine'] ?? null;
$processId = isset($_GET['process_id']) ? (int)$_GET['process_id'] : null;

$errorMessage = null;
$deptName = $subWsName = $procName = null;
$files = [];

// Validasi awal
if (!$npk || !$machine || !$processId) {
    $errorMessage = "Parameter tidak lengkap!";
} else {
    // --- Ambil dept user
    $stmt = $connUser->prepare("SELECT dept FROM ct_users WHERE npk=? LIMIT 1");
    $stmt->bind_param("s", $npk);
    $stmt->execute();
    $resUser = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$resUser) {
        $errorMessage = "NPK {$npk} tidak ditemukan.";
    } else {
        $deptUser = ucwords(strtolower(trim($resUser['dept'])));
        $stmt = $connIMOM->prepare("SELECT id, dept_name FROM department WHERE LOWER(dept_name)=LOWER(?) LIMIT 1");
        $stmt->bind_param("s", $deptUser);
        $stmt->execute();
        $rowDept = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$rowDept) {
            $errorMessage = "Departemen {$deptUser} tidak terdaftar.";
        } else {
            $deptName = $rowDept['dept_name'];

            // --- Sub Workstation
            $stmt = $connIMOM->prepare("SELECT id, name FROM sub_workstations WHERE id=? LIMIT 1");
            $stmt->bind_param("i", $machine);
            $stmt->execute();
            $rowSub = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$rowSub) {
                $errorMessage = "Mesin {$machine} tidak ditemukan.";
            } else {
                $subWsName = $rowSub['name'];

                // --- Process
                $stmt = $connIMOM->prepare("SELECT process_name FROM process WHERE id=?");
                $stmt->bind_param("i", $processId);
                $stmt->execute();
                $procRow = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                $procName = $procRow['process_name'] ?? "-";

                // --- Ambil OM (1 file terbaru)
                $stmt = $connIMOM->prepare("SELECT file_name, path_name FROM data_om 
                                            WHERE sub_workstation_id=? AND process_id=? 
                                            ORDER BY id DESC LIMIT 1");
                $stmt->bind_param("ii", $machine, $processId);
                $stmt->execute();
                $omFile = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                if ($omFile) {
                    $files[] = [
                        "type" => "OM",
                        "src"  => $omFile['path_name'].$omFile['file_name']
                    ];
                }

                // --- Ambil semua IM
                $stmt = $connIMOM->prepare("SELECT part_number, file_name, path_name FROM data_im 
                                            WHERE sub_workstation_id=? AND process_id=? 
                                            ORDER BY id DESC");
                $stmt->bind_param("ii", $machine, $processId);
                $stmt->execute();
                $resIM = $stmt->get_result();
                while ($row = $resIM->fetch_assoc()) {
                    $files[] = [
                        "type" => "IM",
                        "part" => $row['part_number'],
                        "src"  => $row['path_name'].$row['file_name']
                    ];
                }
                $stmt->close();

                if (empty($files)) {
                    $errorMessage = "Belum ada file OM/IM untuk mesin {$subWsName} - Proses {$procName}";
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
  <title>Monitoring Detail</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<?php if ($errorMessage): ?>
<script>
Swal.fire({ icon:'error', title:'Akses Ditolak', text: <?= json_encode($errorMessage) ?> })
    .then(() => window.history.back());
</script>
<?php else: ?>
  <!-- Header -->
  <div class="bg-red-600 text-white px-6 py-4 shadow-md text-center">
    <h1 class="text-xl font-bold">Monitoring Result</h1>
    <p class="mt-1 text-sm">
        <strong>NPK:</strong> <?= htmlspecialchars($npk) ?> | 
        <strong>Dept:</strong> <?= htmlspecialchars($deptName) ?> | 
        <strong>Line:</strong> <?= htmlspecialchars($subWsName) ?> |
        <strong>Process:</strong> <?= htmlspecialchars($procName) ?>
    </p>
  </div>

  <!-- Carousel -->
  <div class="flex-1 flex items-center justify-center p-6">
    <div class="relative w-full max-w-5xl">
        <div id="slides" class="relative w-full h-[80vh] overflow-hidden border rounded shadow">
            <?php foreach($files as $i => $f): ?>
                <iframe src="<?= htmlspecialchars($f['src']) ?>"
                        class="slide absolute inset-0 w-full h-full <?= $i===0?'block':'hidden' ?>"
                        frameborder="0"></iframe>
            <?php endforeach; ?>
        </div>

        <!-- Controls -->
        <button onclick="prevSlide()" class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/50 text-white px-3 py-2 rounded-full">❮</button>
        <button onclick="nextSlide()" class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/50 text-white px-3 py-2 rounded-full">❯</button>
    </div>
  </div>

<script>
let current = 0;
const slides = document.querySelectorAll(".slide");

function showSlide(i){
  slides[current].classList.add("hidden");
  current = (i+slides.length)%slides.length;
  slides[current].classList.remove("hidden");
}

function nextSlide(){ showSlide(current+1); }
function prevSlide(){ showSlide(current-1); }
</script>
<?php endif; ?>
</body>
</html>

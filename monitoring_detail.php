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
    // Ambil dept user
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
                        "src"  => $omFile['path_name'].$omFile['file_name'],
                        "name" => $omFile['file_name']
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
                        "src"  => $row['path_name'].$row['file_name'],
                        "name" => $row['file_name']
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
  <style>
    .slide { display: none; }
    .slide.active { display: block; }
    .slide img {
      max-width: 100%;
      max-height: 70vh;
      object-fit: contain;
      margin: 0 auto;
    }
    .dot.active {
      background-color: #ef4444;
      transform: scale(1.2);
    }
  </style>
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
    <div class="relative w-full max-w-5xl bg-white rounded-lg shadow-lg p-4">
        <?php foreach($files as $i => $f): ?>
            <div class="slide <?= $i===0?'active':'' ?> text-center">
                <p class="font-semibold mb-2">
                    <?= $f['type']==='OM' ? '(OM)' : '(IM - '.htmlspecialchars($f['part']).')' ?>
                </p>
                <img src="<?= htmlspecialchars($f['src']) ?>" alt="File" class="mx-auto slide-img">
                <p class="text-xs text-gray-500 mt-2"><?= htmlspecialchars($f['name']) ?></p>
            </div>
        <?php endforeach; ?>

        <!-- Controls -->
        <button onclick="prevSlide()" class="absolute left-0 top-1/2 -translate-y-1/2 bg-gray-800 text-white w-10 h-10 flex items-center justify-center rounded-full shadow">❮</button>
        <button onclick="nextSlide()" class="absolute right-0 top-1/2 -translate-y-1/2 bg-gray-800 text-white w-10 h-10 flex items-center justify-center rounded-full shadow">❯</button>

        <!-- Fullscreen Button  -->
        <button onclick="openFullscreen()" 
                class="absolute top-2 right-2 bg-black/50 text-white px-3 py-2 rounded text-sm">
            ⛶ Fullscreen
        </button>

        <!-- Dots -->
        <div class="flex justify-center mt-4 space-x-2">
            <?php foreach($files as $i => $f): ?>
                <span onclick="showSlide(<?= $i ?>)" class="dot w-3 h-3 bg-gray-300 rounded-full cursor-pointer"></span>
            <?php endforeach; ?>
        </div>
    </div>
  </div>

<script>
let current = 0;
const slides = document.querySelectorAll(".slide");
const dots   = document.querySelectorAll(".dot");

function showSlide(i){
  slides[current].classList.remove("active");
  dots[current].classList.remove("active");

  current = (i+slides.length)%slides.length;

  slides[current].classList.add("active");
  dots[current].classList.add("active");
}

function nextSlide(){ showSlide(current+1); }
function prevSlide(){ showSlide(current-1); }

// Init
dots[current].classList.add("active");

// Fullscreen Function
function openFullscreen() {
    const activeSlide = slides[current];
    const img = activeSlide.querySelector(".slide-img");
    if (!img) return;

    if (img.requestFullscreen) {
        img.requestFullscreen();
    } else if (img.mozRequestFullScreen) {
        img.mozRequestFullScreen();
    } else if (img.webkitRequestFullscreen) { 
        img.webkitRequestFullscreen();
    } else if (img.msRequestFullscreen) { 
        img.msRequestFullscreen();
    }
}
</script>

<?php endif; ?>
</body>
</html>

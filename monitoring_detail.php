<?php
require_once 'config.php';
session_start();

$npk       = $_GET['npk'] ?? null;
$machine   = $_GET['machine'] ?? null;
$processId = isset($_GET['process_id']) ? (int)$_GET['process_id'] : null;
$selectedPart = $_GET['part'] ?? null;

$errorMessage = null;
$deptName = $subWsName = $procName = null;
$files = [];
$partNumbers = [];

// Validasi awal
if (!$npk || !$machine || !$processId) {
    $errorMessage = "Parameter tidak lengkap!";
} else {
    // GET dept user
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

            // Sub Workstation
            $stmt = $connIMOM->prepare("SELECT id, name FROM sub_workstations WHERE id=? LIMIT 1");
            $stmt->bind_param("i", $machine);
            $stmt->execute();
            $rowSub = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$rowSub) {
                $errorMessage = "Mesin {$machine} tidak ditemukan.";
            } else {
                $subWsName = $rowSub['name'];

                // Process
                $stmt = $connIMOM->prepare("SELECT process_name FROM process WHERE id=?");
                $stmt->bind_param("i", $processId);
                $stmt->execute();
                $procRow = $stmt->get_result()->fetch_assoc();
                $stmt->close();
                $procName = $procRow['process_name'] ?? "-";

                // --- GET daftar part_number IM untuk sidebar
                $stmt = $connIMOM->prepare("SELECT DISTINCT part_number FROM data_im WHERE sub_workstation_id=? AND process_id=? ORDER BY part_number ASC");
                $stmt->bind_param("ii", $machine, $processId);
                $stmt->execute();
                $resParts = $stmt->get_result();
                while ($r = $resParts->fetch_assoc()) {
                    $partNumbers[] = $r['part_number'];
                }
                $stmt->close();

                // --- GET OM (selalu ikut 1 terbaru)
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
                        "name" => $omFile['file_name'],
                        "label"=> "OM"
                    ];
                }

                // --- GET IM (jika ada filter pilih part → hanya tampilkan itu)
                if ($selectedPart) {
                    $stmt = $connIMOM->prepare("SELECT part_number, file_name, path_name FROM data_im 
                                                WHERE sub_workstation_id=? AND process_id=? AND part_number=? 
                                                ORDER BY id DESC");
                    $stmt->bind_param("iis", $machine, $processId, $selectedPart);
                } else {
                    $stmt = $connIMOM->prepare("SELECT part_number, file_name, path_name FROM data_im 
                                                WHERE sub_workstation_id=? AND process_id=? 
                                                ORDER BY id DESC");
                    $stmt->bind_param("ii", $machine, $processId);
                }
                $stmt->execute();
                $resIM = $stmt->get_result();
                while ($row = $resIM->fetch_assoc()) {
                    $files[] = [
                        "type" => "IM",
                        "part" => $row['part_number'],
                        "src"  => $row['path_name'].$row['file_name'],
                        "name" => $row['file_name'],
                        "label"=> "IM - ".$row['part_number']
                    ];
                }
                $stmt->close();

                // --- GET Rules (selalu ikut)
                $stmt = $connIMOM->prepare("SELECT rules_name, file_name, path_name FROM data_rules 
                                            WHERE sub_workstation_id=? AND process_id=? 
                                            ORDER BY id DESC");
                $stmt->bind_param("ii", $machine, $processId);
                $stmt->execute();
                $resRules = $stmt->get_result();
                while ($row = $resRules->fetch_assoc()) {
                    $files[] = [
                        "type" => "RULES",
                        "rules_name" => $row['rules_name'],
                        "src"  => $row['path_name'].$row['file_name'],
                        "name" => $row['file_name'],
                        "label"=> "Rules: ".$row['rules_name']
                    ];
                }
                $stmt->close();

                if (empty($files)) {
                    $errorMessage = "Belum ada file OM/IM/Rules untuk mesin {$subWsName} - Proses {$procName}";
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
        .slide img, .slide iframe {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
            margin: 0 auto;
        }
        .file-link {
            display: block;
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 500;
            color: #374151;
        }
        .file-link:hover { background-color: #f3f4f6; }
        .file-link.active { background-color: #ef4444; color: white; }
        .dot {
            width: 10px;
            height: 10px;
            background: #d1d5db;
            border-radius: 9999px;
            display: inline-block;
            cursor: pointer;
        }
        .dot.active { background: #ef4444; transform: scale(1.2); }
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
<div class="bg-red-600 text-white px-6 py-4 shadow-md flex justify-between items-center">
    <div class="text-center flex-1">
        <h1 class="text-xl font-bold">Monitoring Result</h1>
        <p class="mt-1 text-sm">
            <strong>Dept:</strong> <?= htmlspecialchars($deptName) ?> | 
            <strong>Line:</strong> <?= htmlspecialchars($subWsName) ?> |
            <strong>Process:</strong> <?= htmlspecialchars($procName) ?>
        </p>
    </div>
    <div class="ml-4">
        <a href="monitoring_process.php?npk=<?= urlencode($npk) ?>&machine=<?= urlencode($machine) ?>"
            class="bg-white text-red-600 font-semibold px-4 py-2 rounded shadow hover:bg-gray-100 transition">
            ← Kembali
        </a>
    </div>
</div>

<!-- Layout -->
<div class="flex flex-1">
    <!-- Sidebar Part Number-->
    <div class="w-64 bg-gray-100 p-4 border-r">
        <h2 class="font-bold mb-4 text-gray-700">Filter Model Part Number</h2>
        <ul class="space-y-2">
            <li>
                <a href="?npk=<?= urlencode($npk) ?>&machine=<?= urlencode($machine) ?>&process_id=<?= urlencode($processId) ?>" 
                   class="file-link <?= !$selectedPart?'active':'' ?>">All</a>
            </li>
            <?php foreach($partNumbers as $p): ?>
            <li>
                <a href="?npk=<?= urlencode($npk) ?>&machine=<?= urlencode($machine) ?>&process_id=<?= urlencode($processId) ?>&part=<?= urlencode($p) ?>" 
                   class="file-link <?= $selectedPart==$p?'active':'' ?>">
                   <?= htmlspecialchars($p) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- Carousel -->
    <div class="flex-1 flex flex-col items-center justify-center p-6">
        <div class="relative w-full max-w-5xl bg-white rounded-lg shadow-lg p-4">
            <?php foreach($files as $i => $f): ?>
            <div class="slide <?= $i===0?'active':'' ?> text-center">
                <p class="font-semibold mb-2"><?= htmlspecialchars($f['label']) ?></p>
                
                <?php if ($f['type']==='RULES' && preg_match('/\.(pdf)$/i',$f['name'])): ?>
                    <iframe src="<?= htmlspecialchars($f['src']) ?>" class="mx-auto w-full h-[70vh]"></iframe>
                <?php else: ?>
                    <img src="<?= htmlspecialchars($f['src']) ?>" alt="File" class="mx-auto slide-img">
                <?php endif; ?>

                <p class="text-xs text-gray-500 mt-2"><?= htmlspecialchars($f['name']) ?></p>
            </div>
            <?php endforeach; ?>

            <!-- Controls -->
            <button onclick="prevSlide()" class="absolute left-0 top-1/2 -translate-y-1/2 bg-gray-800/50 text-white w-10 h-10 ml-8 flex items-center justify-center rounded-full shadow">❮</button>
            <button onclick="nextSlide()" class="absolute right-0 top-1/2 -translate-y-1/2 bg-gray-800/50 text-white w-10 h-10 mr-8 flex items-center justify-center rounded-full shadow">❯</button>

            <!-- Fullscreen -->
            <button onclick="openFullscreen()" 
                    class="absolute top-2 right-2 bg-black/50 text-white px-3 py-2 rounded text-sm">
                ⛶ Fullscreen
            </button>
        </div>

        <!-- Dots indicator -->
        <div class="flex justify-center mt-4 space-x-2">
            <?php foreach($files as $i => $f): ?>
                <span onclick="showSlide(<?= $i ?>)" class="dot <?= $i===0?'active':'' ?>"></span>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
let current = 0;
const slides = document.querySelectorAll(".slide");
const dots   = document.querySelectorAll(".dot");
let fullscreenActive = false;
let fsContainer, fsContent;

// Buat container fullscreen sekali saja
function initFullscreenContainer() {
    fsContainer = document.createElement("div");
    fsContainer.id = "fullscreen-container";
    fsContainer.style.cssText = `
        background: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
    `;
    fsContent = document.createElement("div");
    fsContainer.appendChild(fsContent);
}

// Tampilkan slide
function showSlide(i) {
    slides[current].classList.remove("active");
    dots[current].classList.remove("active");

    current = (i + slides.length) % slides.length;

    slides[current].classList.add("active");
    dots[current].classList.add("active");

    // Update fullscreen jika aktif
    if (fullscreenActive) {
        updateFullscreenImage();
    }
}

function nextSlide(){ showSlide(current + 1); }
function prevSlide(){ showSlide(current - 1); }

// Inisialisasi dots
if (dots.length > 0) dots[current].classList.add("active");

// Buka fullscreen
function openFullscreen() {
    if (!fsContainer) initFullscreenContainer();

    fullscreenActive = true;
    updateFullscreenImage();

    // Masuk fullscreen (hanya sekali)
    if (!document.fullscreenElement) {
        document.body.appendChild(fsContainer);
        fsContainer.requestFullscreen();
    }
}

// Update isi fullscreen sesuai slide aktif
function updateFullscreenImage() {
    fsContent.innerHTML = ""; // kosongkan dulu
    const activeSlide = slides[current];
    const img = activeSlide.querySelector(".slide-img") || activeSlide.querySelector("iframe");

    if (!img) return;

    let clone;
    if (img.tagName === "IFRAME") {
        clone = document.createElement("iframe");
        clone.src = img.src;
        clone.style.width = "90%";
        clone.style.height = "90%";
    } else {
        clone = document.createElement("img");
        clone.src = img.src;
        clone.style.maxWidth = "100%";
        clone.style.maxHeight = "100%";
    }
    fsContent.appendChild(clone);
}

// Tutup fullscreen saat ESC
document.addEventListener("fullscreenchange", () => {
    if (!document.fullscreenElement) {
        fullscreenActive = false;
        if (fsContainer && fsContainer.parentNode) {
            fsContainer.remove();
        }
    }
});

// Navigasi keyboard saat fullscreen
document.addEventListener("keydown", (e) => {
    if (!fullscreenActive) return;

    if (e.key === "ArrowRight") {
        nextSlide();
    } else if (e.key === "ArrowLeft") {
        prevSlide();
    } else if (e.key === "Escape") {
        fullscreenActive = false;
    }
});
</script>
<?php endif; ?>
</body>
</html>

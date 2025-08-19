<?php
require_once 'config.php';

$modelName = 'Unknown Model';
$modelCode = '';
$line = '';
$modelFound = false;
$cleanId = '';
$id = '';

// Ambil data terbaru dari model_ff
$query = "SELECT Model_no, Model, Line 
            FROM master_model_ff 
            ORDER BY id DESC 
            LIMIT 1";
$result = $connData->query($query);

if ($row = $result->fetch_assoc()) {
    $id = $row['Model_no'];
    $cleanId = rtrim($id, '-');
    $modelCode = $row['Model'];
    $line = $row['Line']; // langsung ambil, tanpa pembatasan

    $baseDir = "manual_images/$line";
    $imgIMPath = $baseDir . "/" . $cleanId . "-IM.jpg";
    $imgOMPath = $baseDir . "/" . $cleanId . "-OM.jpg";

    if (file_exists($imgIMPath) && file_exists($imgOMPath)) {
        $modelFound = true;
        $modelName = $modelCode;
    } else {
        $modelName = "⚠ Manual file not found.";
    }
} else {
    $modelName = "⚠ No data found in database.";
}

$npk = $_GET['npk'] ?? null;
$machine = $_GET['machine'] ?? null;

if (!$npk || !$machine) {
    echo "<p class='text-red-600 font-bold text-center'>Akses tidak valid. Silakan pilih NPK & Mesin terlebih dahulu.</p>";
    exit;
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monitoring OM/IM Terbaru</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-gray-100 min-h-screen">
    
<!-- HEADER -->
<div class="bg-white shadow p-4 mb-4">
    <h1 class="text-xl font-bold text-gray-700 text-center">Monitoring OM/IM Terbaru</h1>
    <div id="realtimeClock" class="text-gray-500 text-sm font-medium text-center py-4"></div>

    <div class="flex flex-wrap justify-center gap-6 mb-4">
        <div>
            <label class="block text-xs font-bold uppercase text-gray-500">
                Model No
            </label>
            <input type="text" value="<?= htmlspecialchars($id ?: 'NOT FOUND') ?>" readonly
                class="bg-gray-100 px-3 py-2 rounded w-40 font-semibold text-gray-800 border border-gray-300" />
        </div>
        <div>
            <label class="block text-xs font-bold uppercase text-gray-500">
                Model Name
            </label>
            <input type="text" value="<?= htmlspecialchars($modelCode ?: 'NOT FOUND') ?>" readonly
                class="bg-gray-100 px-3 py-2 rounded w-40 font-semibold text-gray-800 border border-gray-300" />
        </div>
        <div>
            <label class="block text-xs font-bold uppercase text-gray-500">
                Line
            </label>
            <input type="text" value="<?= htmlspecialchars($line ?: 'NOT FOUND') ?>" readonly
                class="bg-gray-100 px-3 py-2 rounded w-40 font-semibold text-gray-800 border border-gray-300" />
        </div>
        <!-- BUTTON -->
            <div class="flex justify-center md:justify-end gap-3 flex-wrap">
                <button onclick="window.location.href='checksheet-im.php?model_no=<?= urlencode($id) ?>'" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold px-3 py-1 rounded shadow flex items-center gap-2">
                    <i class="fa-solid fa-file-lines"></i> IM
                </button>
                <button onclick="window.location.href='checksheet.php'" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold px-3 py-1 rounded shadow flex items-center gap-2">
                    <i class="fa-solid fa-file-lines"></i> OM
                </button>
                <button onclick="window.location.href='index.php'" class="bg-red-600 hover:bg-red-500 text-white font-semibold px-4 py-2 rounded shadow flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </button>
            </div>
    </div>
</div>

<!-- MAIN CONTENT -->
<div class="bg-black relative w-full h-[calc(100vh-330px)] overflow-hidden rounded-lg shadow-lg">
    <?php if (!$modelFound): ?>
        <div class="flex items-center justify-center h-full text-yellow-200 text-2xl font-bold p-6 text-center">
            <?= htmlspecialchars($modelName) ?>
        </div>
    <?php else: ?>
        <!-- Slide 1: IM -->
        <div class="slide absolute inset-0 opacity-100 transition-opacity duration-500 z-10">
            <img src="show_image.php?id=<?= urlencode($cleanId) ?>&line=<?= urlencode($line) ?>&type=IM"
                alt="IM Manual" class="w-full h-full object-contain bg-black" />
        </div>
        <!-- Slide 2: OM -->
        <div class="slide absolute inset-0 opacity-0 transition-opacity duration-500 z-0">
            <img src="show_image.php?id=<?= urlencode($cleanId) ?>&line=<?= urlencode($line) ?>&type=OM"
                alt="OM Manual" class="w-full h-full object-contain bg-black" />
        </div>

        <!-- Controls -->
        <div class="absolute top-1/2 left-0 right-0 flex justify-between px-6 transform -translate-y-1/2 z-20">
            <button onclick="prevSlide()" class="text-white text-3xl bg-white/20 hover:bg-white/30 p-3 rounded-full">
                ⟨
            </button>
            <button onclick="nextSlide()" class="text-white text-3xl bg-white/20 hover:bg-white/30 p-3 rounded-full">
                ⟩
            </button>
        </div>

        <!-- Indicator -->
        <div id="slide-indicator"
            class="absolute bottom-6 left-1/2 transform -translate-x-1/2 bg-white/20 text-white px-4 py-1 rounded-full text-sm z-20">
            1 / 2
        </div>
    <?php endif; ?>
</div>

<script>
function updateClock() {
    const now = new Date();
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit'
    };
    document.getElementById('realtimeClock').textContent = now.toLocaleDateString('id-ID', options);
}
setInterval(updateClock, 1000);
updateClock();

// --- slider existing ---
const slides = document.querySelectorAll('.slide');
const indicator = document.getElementById('slide-indicator');
let current = 0;

function showSlide(index) {
    slides.forEach((slide, i) => {
        slide.style.opacity = (i === index) ? '1' : '0';
        slide.style.zIndex = (i === index) ? '10' : '0';
    });
    if (indicator) {
        indicator.textContent = `${index + 1} / ${slides.length}`;
    }
}

function nextSlide() {
    current = (current + 1) % slides.length;
    showSlide(current);
}

function prevSlide() {
    current = (current - 1 + slides.length) % slides.length;
    showSlide(current);
}

document.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight') nextSlide();
    if (e.key === 'ArrowLeft') prevSlide();
});
</script>

</body>
</html>
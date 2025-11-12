<?php
require_once 'config.php';

$id = $_GET['id'] ?? '';
$allowedLines = ['RP-WLC', 'RP-OSC'];
$modelName = 'Unknown Model';
$modelCode = '';
$line = '';
$modelFound = false;

if ($id) {
    $cleanId = rtrim($id, '-');
    $stmt = $connData->prepare("SELECT Model, Line FROM master_model_ff WHERE Model_no = ? ORDER BY Rev DESC LIMIT 1");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->bind_result($modelCode, $lineFromDb);

    if ($stmt->fetch()) {
        $modelName = $modelCode;
        $line = in_array($lineFromDb, $allowedLines) ? $lineFromDb : 'RP-WLC';
        $baseDir = "manual_images/$line";
        $imgIMPath = $baseDir . "\\" . $cleanId . "-IM.jpg";
        $imgOMPath = $baseDir . "\\" . $cleanId . "-OM.jpg";

        if (file_exists($imgIMPath) && file_exists($imgOMPath)) {
            $modelFound = true;
        } else {
            $modelName = "⚠ Manual file not found. ⚠";
        }
    } else {
        $modelName = "⚠ Model not found in database. ⚠";
    }
    $stmt->close();
}
?>

<!-- Konten Manual -->
<div class="h-full overflow-auto">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="mb-4">
            <h2 class="text-xl font-bold">Digital OM/IM Viewer</h2>
        </div>

        <div class="flex flex-wrap justify-between py-4">
            <!-- FORM -->
            <div class="flex flex-wrap justify-center md:justify-start gap-6">
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500">
                    <i class="fa fa-barcode"></i> Model No
                    </label>
                    <input type="text" value="<?= htmlspecialchars($id ?: 'NOT FOUND') ?>" readonly class="bg-gray-100 px-3 py-2 rounded w-40 font-semibold text-gray-800 border border-gray-300" />
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500">
                    <i class="fa fa-file-pen"></i> Model Name
                    </label>
                    <input type="text" value="<?= htmlspecialchars($modelCode ?: 'NOT FOUND') ?>" readonly class="bg-gray-100 px-3 py-2 rounded w-40 font-semibold text-gray-800 border border-gray-300" />
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-500">
                    <i class="fa fa-hard-drive"></i> Line
                    </label>
                    <input type="text" value="<?= htmlspecialchars($line ?: 'NOT FOUND') ?>" readonly class="bg-gray-100 px-3 py-2 rounded w-40 font-semibold text-gray-800 border border-gray-300" />
                </div>
            </div>
            <!-- END FORM -->
            
            <!-- BUTTON -->
            <div class="flex justify-center md:justify-end gap-3 flex-wrap">
                <button onclick="window.location.href='checksheet-im.php?model_no=<?= urlencode($id) ?>'" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold px-4 py-2 rounded shadow flex items-center gap-2">
                    <i class="fa-solid fa-file-lines"></i> IM
                </button>
                <button onclick="window.location.href='checksheet.php'" class="bg-blue-600 hover:bg-blue-500 text-white font-semibold px-4 py-2 rounded shadow flex items-center gap-2">
                    <i class="fa-solid fa-file-lines"></i> OM
                </button>
                <button onclick="window.location.href='index.php'" class="bg-red-600 hover:bg-red-500 text-white font-semibold px-4 py-2 rounded shadow flex items-center gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </button>
            </div>
            <!-- END BUTTON -->
        </div>
        
        <main class="flex-grow relative"> 
                <?php if (!$modelFound): ?>
                <div class="flex items-center justify-center h-[calc(100vh-120px)] bg-black/80 text-yellow-200 text-2xl font-bold p-6 text-center">
                    <?= htmlspecialchars($modelName) ?>
                </div>
                <?php else: ?>
                <!-- SLIDER CONTAINER -->
                <div class="relative w-full h-[calc(100vh-120px)] bg-black overflow-hidden">
                    <!-- IM -->
                    <div class="slide absolute inset-0 opacity-100 transition-opacity duration-500 z-10">
                    <img src="show_image.php?id=<?= urlencode($cleanId) ?>&line=<?= urlencode($line) ?>&type=IM" alt="IM Manual" class="w-full h-full object-contain bg-black" />
                    </div>
                    <!-- OM -->
                    <div class="slide absolute inset-0 opacity-0 transition-opacity duration-500 z-0">
                    <img src="show_image.php?id=<?= urlencode($cleanId) ?>&line=<?= urlencode($line) ?>&type=OM" alt="OM Manual" class="w-full h-full object-contain bg-black" />
                    </div>

                    <!-- ARROW CONTROLS -->
                    <div class="absolute top-1/2 left-0 right-0 flex justify-between px-6 transform -translate-y-1/2 z-20">
                        <button onclick="prevSlide()" class="text-white text-3xl bg-white/20 hover:bg-white/30 p-3 rounded-full">
                            ⟨
                        </button>
                        <button onclick="nextSlide()" class="text-white text-3xl bg-white/20 hover:bg-white/30 p-3 rounded-full">
                            ⟩
                        </button>
                    </div>

                    <!-- INDICATOR -->
                    <div id="slide-indicator" class="absolute bottom-6 left-1/2 transform -translate-x-1/2 bg-white/20 text-white px-4 py-1 rounded-full text-sm z-20">
                        1 / 2
                    </div>
                </div>
                <?php endif; ?>
        </main>

    <script>
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
    </div>
</div>


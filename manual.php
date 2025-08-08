<?php
require_once 'config.php';

$id = $_GET['id'] ?? ''; // model_no dari URL
$allowedLines = ['RP-WLC', 'RP-OSC'];
$modelName = 'Unknown Model';
$modelCode = '';
// $line = 'RP-WLC';
$line = '';
$modelFound = false;

if ($id) {
    // Hapus strip terakhir jika ada agar tidak jadi double (--IM.jpg)
    $cleanId = rtrim($id, '-');

    // Ambil Model dan Line dari database berdasarkan Rev terbaru
    $stmt = $conn->prepare("
        SELECT Model, Line 
        FROM master_model_ff 
        WHERE Model_no = ? 
        ORDER BY Rev DESC 
        LIMIT 1
    ");

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
            $modelName = "Manual file not found.";
        }
    } else {
        $modelName = "Model not found in database.";
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($modelName) ?> - DIGITAL OM/IM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="css/googleapis.css" rel="stylesheet">
  <style>
    /* Styling... (potong jika tidak perlu diubah) */
    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      background: #f5f6fa;
      color: #2d3436;
    }

    .topbar {
      padding: 16px 30px;
      background: #ffffff;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 10px rgba(0,0,0,0.06);
      position: sticky;
      top: 0;
      z-index: 999;
    }

    .inputs {
      display: flex;
      gap: 24px;
      flex-wrap: wrap;
    }

    .input-group {
      display: flex;
      flex-direction: column;
    }

    .input-group label {
      font-size: 13px;
      font-weight: 700;
      text-transform: uppercase;
      margin-bottom: 4px;
      color: #636e72;
      letter-spacing: 0.5px;
    }

    .input-group input {
      padding: 10px 14px;
      border: 1px solid #dfe6e9;
      border-radius: 8px;
      background: #f1f2f6;
      font-weight: 600;
      color: #2d3436;
      min-width: 180px;
    }

    .buttons {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
    }

    .buttons button {
      padding: 10px 20px;
      font-weight: 600;
      background: #0984e3;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: 0.3s;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .buttons button:hover {
      background: #74b9ff;
    }

    .buttons .back {
      background: #d63031;
    }

    .buttons .back:hover {
      background: #c0392b;
    }

    .slider-container {
      width: 100%;
      height: calc(100vh - 120px);
      background: #000;
      position: relative;
      display: flex;
      justify-content: center;
      align-items: center;
      overflow: hidden;
    }

    .slide {
      display: none;
      position: absolute;
      width: 100%;
      height: 100%;
      transition: opacity 0.4s ease-in-out;
    }

    .slide img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      background-color: #000;
    }

    .slide.active {
      display: block;
    }

    .controls {
      position: absolute;
      top: 50%;
      width: 100%;
      display: flex;
      justify-content: space-between;
      transform: translateY(-50%);
      padding: 0 25px;
    }

    .controls button {
      background: rgba(255, 255, 255, 0.15);
      color: #fff;
      border: none;
      font-size: 28px;
      padding: 10px 18px;
      border-radius: 50%;
      cursor: pointer;
      transition: background 0.3s;
    }

    .controls button:hover {
      background: rgba(255, 255, 255, 0.3);
    }

    .center-message {
      color: #fab1a0;
      font-size: 26px;
      font-weight: 700;
      text-align: center;
      padding: 20px;
    }

    @media (max-width: 768px) {
      .inputs {
        flex-direction: column;
        gap: 12px;
      }

      .buttons {
        justify-content: center;
        gap: 10px;
      }

      .slider-container {
        height: 70vh;
      }

      .controls button {
        font-size: 22px;
        padding: 8px 14px;
      }
    }
  </style>
</head>
<body>
  <div class="topbar">
    <div class="inputs">
      <div class="input-group">
        <label>Model No | KYB Number</label>
        <input type="text" value="<?= htmlspecialchars($id ?: 'NOT FOUND') ?>" readonly>
      </div>
      <div class="input-group">
        <label>Model Name</label>
        <input type="text" value="<?= htmlspecialchars($modelCode ?: 'NOT FOUND') ?>" readonly>
      </div>
      <div class="input-group">
        <label>Line</label>
        <input type="text" value="<?= htmlspecialchars($line ?: 'NOT FOUND') ?>" readonly>
      </div>
    </div>

    <div class="buttons">
      <button onclick="window.location.href='checksheet.php'">CHECKSHEET IM</button>
      <button onclick="window.location.href='checksheet.php'">CHECKSHEET OM</button>
      <button class="back" onclick="window.location.href='index.php'">KEMBALI</button>
    </div>
  </div>

  <div class="slider-container">
    <?php if (!$modelFound): ?>
      <div class="center-message">⚠ <?= htmlspecialchars($modelName) ?></div>
    <?php else: ?>
      <!-- IM -->
      <div class="slide active">
        <img src="show_image.php?id=<?= urlencode($cleanId) ?>&line=<?= urlencode($line) ?>&type=IM" alt="IM Manual">
      </div>
      <!-- OM -->
      <div class="slide">
        <img src="show_image.php?id=<?= urlencode($cleanId) ?>&line=<?= urlencode($line) ?>&type=OM" alt="OM Manual">
      </div>

      <div class="controls">
        <button onclick="prevSlide()">⟨</button>
        <button onclick="nextSlide()">⟩</button>
      </div>
    <?php endif; ?>
  </div>

  <script>
    const slides = document.querySelectorAll('.slide');
    let current = 0;

    function showSlide(index) {
      slides.forEach((slide, i) => {
        slide.classList.toggle('active', i === index);
      });
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

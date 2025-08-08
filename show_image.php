<?php
$id = $_GET['id'] ?? '';
$line = basename($_GET['line'] ?? '');
$type = strtoupper($_GET['type'] ?? 'IM');

$allowedLines = ['RP-WLC', 'RP-OSC'];

if (!$id || !$line || !in_array($line, $allowedLines) || !in_array($type, ['IM', 'OM'])) {
    http_response_code(400);
    exit("Invalid request.");
}

// Buang strip di akhir jika ada
$cleanId = rtrim($id, '-');

$path = "manual_images/$line/{$cleanId}-$type.jpg";

if (!file_exists($path)) {
    http_response_code(404);
    exit("Manual not found.");
}

header("Content-Type: image/jpeg");
header("Content-Length: " . filesize($path));
readfile($path);

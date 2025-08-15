<?php
require_once 'config.php';

// Pastikan form dikirim via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $workstation_id = isset($_POST['workstation_id']) ? (int) $_POST['workstation_id'] : 0;
    $name           = trim($_POST['name'] ?? '');
    $col1           = trim($_POST['col1'] ?? '');
    $col2           = trim($_POST['col2'] ?? '');

    if ($workstation_id > 0 && $name !== '') {
        // Query insert
        $stmt = $connIMOM->prepare("INSERT INTO sub_workstations (workstation_id, name, col1, col2) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("isss", $workstation_id, $name, $col1, $col2);
            $stmt->execute();
            $stmt->close();

            // Redirect kembali ke halaman sub_workstations
            header("Location: index.php?page=sub_workstations&workstation_id={$workstation_id}");
            exit;
        } else {
            die("Gagal menyiapkan query: " . $connIMOM->error);
        }
    } else {
        die("Data tidak lengkap!");
    }
} else {
    die("Metode tidak valid!");
}

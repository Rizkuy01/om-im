<?php
// $host = '172.16.16.253';
// $user = 'nodered';
// $pass = 'BackEnd';
$host = 'localhost';
$user = 'root';
$pass = '';

$db   = 'model_ff';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>

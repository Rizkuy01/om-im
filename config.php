<?php
// $host = '172.16.16.253';
// $user = 'nodered';
// $pass = 'BackEnd';


// AUTHENTICATE
$host_userdb = 'localhost';
$user_userdb = 'root';
$pass_userdb = '';
$db_userdb   = 'lembur1';

$connUser = new mysqli($host_userdb, $user_userdb, $pass_userdb, $db_userdb);
if ($connUser->connect_error) {
    die("Koneksi ke DB user gagal: " . $connUser->connect_error);
}

// MODEL
$host_datadb = 'localhost';
$user_datadb = 'root';
$pass_datadb = '';
$db_datadb   = 'model_ff';

$connData = new mysqli($host_datadb, $user_datadb, $pass_datadb, $db_datadb);
if ($connData->connect_error) {
    die("Koneksi ke DB data gagal: " . $connData->connect_error);
}
?>

<?php
date_default_timezone_set('Asia/Jakarta');

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

// IM OM
$host_imom = 'localhost';
$user_imom = 'root';
$pass_imom = '';
$db_imom   = 'om_im';

$connIMOM = new mysqli($host_imom, $user_imom, $pass_imom, $db_imom);
if ($connIMOM->connect_error) {
    die("Koneksi ke DB OM IM gagal: " . $connIMOM->connect_error);
}

// ISD
$host_isd = 'localhost';
$user_isd = 'root';
$pass_isd = '';
$db_isd   = 'isd';

$connISD = new mysqli($host_isd, $user_isd, $pass_isd, $db_isd);
if ($connISD->connect_error) {
    die("Koneksi ke DB ISD gagal: " . $connISD->connect_error);
}
?>

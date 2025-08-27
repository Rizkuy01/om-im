<?php
// Password asli (plain text)
$password = "sinyalasu";

// Buat hash password dengan bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT);

// Tampilkan hash
echo "Password asli: " . $password . "<br>";
echo "Hash: " . $hash;

<?php
// Password asli (plain text)
$password = "abid123";

// Buat hash password dengan bcrypt
$hash = password_hash($password, PASSWORD_BCRYPT);

// Tampilkan hash
echo "Password asli: " . $password . "<br>";
echo "Hash: " . $hash;

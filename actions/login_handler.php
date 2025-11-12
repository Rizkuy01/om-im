<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/auth.php';
session_start();

$error = '';
$auth = new Auth($connUser, $connIMOM, $connISD);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $npk      = trim($_POST['npk']);
    $password = trim($_POST['password']);
    $captcha  = trim($_POST['captcha']);

    $result = $auth->login($npk, $password, $captcha);

    if (!empty($result['error'])) {
        $error = $result['error'];
    } elseif (!empty($result['redirect'])) {
        header("Location: " . $result['redirect']);
        exit;
    }
}

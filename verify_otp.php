<?php
session_start();
require_once 'config.php'; // ✅ koneksi $connIMOM

if (!isset($_SESSION['pending_user'])) {
    header("Location: login.php");
    exit;
}

$npk   = $_SESSION['pending_user']['npk'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    $inputOtp = trim($_POST['otp']);

    // ambil otp terbaru dari tabel
    $stmt = $connIMOM->prepare("
        SELECT kode_otp, expired_at 
        FROM otp 
        WHERE npk = ? 
        ORDER BY id DESC 
        LIMIT 1
    ");
    $stmt->bind_param("s", $npk);
    $stmt->execute();
    $rowOtp = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$rowOtp) {
        $error = "Kode OTP tidak ditemukan. Silakan login ulang.";
    } else {
        $dbOtp      = $rowOtp['kode_otp'];
        $expired_at = strtotime($rowOtp['expired_at']);
        $now        = time();

        if ($now > $expired_at) {
            $error = "Kode OTP sudah expired. Silakan login ulang.";
        } elseif ($inputOtp === $dbOtp) {
            // ✅ OTP benar
            $_SESSION['npk']      = $_SESSION['pending_user']['npk'];
            $_SESSION['user_id']  = $_SESSION['pending_user']['npk'];
            $_SESSION['username'] = $_SESSION['pending_user']['username'];
            $_SESSION['dept']     = $_SESSION['pending_user']['dept'];
            $_SESSION['dept_id']  = $_SESSION['pending_user']['dept_id'];

            $redirect = $_SESSION['pending_user']['redirect_after_otp'] ?? 'index.php?page=main_dashboard';
            unset($_SESSION['pending_user']);

            header("Location: " . $redirect);
            exit;
        } else {
            $error = "Kode OTP salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Verifikasi OTP</title>
  <link rel="stylesheet" href="assets/css/tailwind.css">
  <style>
    body { font-family: 'Inter', sans-serif; }
    .otp-input {
        width: 3rem;
        height: 3rem;
        text-align: center;
        font-size: 1.25rem;
        border: 1px solid #ccc;
        border-radius: 0.375rem;
        margin: 0 0.25rem;
    }
    .otp-input:focus {
        outline: none;
        border-color: #dc2626;
        box-shadow: 0 0 0 2px rgba(220,38,38,0.3);
    }
  </style>
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">

  <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md text-center">
    <img src="assets/kyb.png" alt="KYB Logo" class="w-32 mx-auto mb-2">
    <p class="text-sm text-gray-800 mb-6">PT KAYABA INDONESIA</p>

    <h1 class="text-xl font-bold text-gray-800 mb-4">VERIFIKASI KODE OTP</h1>
    <div class="bg-green-100 border border-green-300 text-green-800 text-sm rounded px-4 py-2 mb-4">
      Kode OTP telah dikirim ke nomor telepon Anda.
    </div>

    <p class="text-gray-600 text-sm mb-6">Silakan masukkan kode di bawah ini.</p>

    <?php if ($error): ?>
      <div class="bg-red-100 text-red-700 px-4 py-2 rounded mb-4 text-sm">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <!-- OTP Form -->
    <form method="post" id="otp-form">
      <div class="flex justify-center mb-6">
        <?php for ($i=0; $i<6; $i++): ?>
          <input type="text" maxlength="1" class="otp-input" inputmode="numeric" pattern="[0-9]*">
        <?php endfor; ?>
        <input type="hidden" name="otp" id="otp-hidden">
      </div>
      <button type="submit"
        class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 rounded-md transition duration-300">
        VERIFIKASI
      </button>
    </form>
  </div>

<script>
const inputs = document.querySelectorAll('.otp-input');
const otpHidden = document.getElementById('otp-hidden');
const form = document.getElementById('otp-form');

inputs.forEach((input, index) => {
  input.addEventListener('input', () => {
    if (input.value.length === 1 && index < inputs.length - 1) {
      inputs[index + 1].focus();
    }
    updateHidden();
  });
  input.addEventListener('keydown', (e) => {
    if (e.key === 'Backspace' && input.value === '' && index > 0) {
      inputs[index - 1].focus();
    }
  });
});

function updateHidden() {
  otpHidden.value = Array.from(inputs).map(i => i.value).join('');
}

form.addEventListener('submit', () => {
  updateHidden();
});
</script>
</body>
</html>

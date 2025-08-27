<?php
session_start();

if (!isset($_SESSION['pending_user'])) {
    header("Location: login.php");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['otp'])) {
    if ($_POST['otp'] === (string)$_SESSION['pending_user']['otp']) {
        // OTP benar → set session login penuh
        $_SESSION['user_id']   = $_SESSION['pending_user']['npk'];
        $_SESSION['username']  = $_SESSION['pending_user']['username'];
        $_SESSION['dept']      = $_SESSION['pending_user']['dept'];
        unset($_SESSION['pending_user']);

        header("Location: index.php");
        exit;
    } else {
        $error = 'OTP salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Verifikasi OTP</title>
  <script src="https://cdn.tailwindcss.com"></script>
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

    <!-- Info Testing -->
    <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 text-sm rounded px-4 py-2 mb-4">
      Untuk Testing: <strong>Kode OTP Anda adalah <?= $_SESSION['pending_user']['otp'] ?></strong>
    </div>

    <p class="text-green-600 font-medium mb-2">Kode OTP telah dikirim ke nomor telepon Anda.</p>
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

// otomatis pindah focus
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

// saat submit, gabungkan semua input ke hidden
form.addEventListener('submit', (e) => {
  updateHidden();
});
</script>
</body>
</html>

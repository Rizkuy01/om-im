<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>DIGITAL OM/IM</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="css/googleapis.css" rel="stylesheet">

  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Inter', sans-serif;
      background: #f1f2f6;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }

    .container {
      background: #ffffff;
      padding: 40px;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
      text-align: center;
      max-width: 500px;
      width: 90%;
    }

    h1 {
      font-size: 20px;
      margin-bottom: 10px;
      font-weight: 700;
      color: #000000ff;
    }

    h2 {
      font-size: 16px;
      margin-bottom: 30px;
      font-weight: 800;
      color: #ee0000ff;
    }

    input[type="text"] {
      padding: 12px;
      width: 100%;
      border-radius: 8px;
      border: 1px solid #ccc;
      margin-bottom: 20px;
      font-size: 16px;
    }

    .logo {
      width: 160px;
      margin-bottom: 20px;
    }

    button {
      padding: 12px 24px;
      background: #ee0000ff;
      color: white;
      border: none;
      border-radius: 8px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.3s ease;
    }

    button:hover {
      background: #b00202ff;
    }
  </style>
</head>
<body>

<div class="container">
  <img src="assets/kyb.png" alt="KYB Logo" class="logo">

  <h1>DIGITAL</h1>
  <h2>INSPECTION AND OPERATION MANUAL</h2>

  <form id="manualForm" action="manual.php" method="get">
    <input type="text" name="id" id="modelInput" placeholder="Scan/Input Model Number (Model_no)" required autocomplete="off">
    <br>
    <button type="submit">OPEN OM/IM</button>
  </form>
</div>

<script>
  document.getElementById('modelInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
      e.preventDefault(); // agar tidak double submit
      if (this.value.trim() !== '') {
        document.getElementById('manualForm').submit();
      }
    }
  });
</script>

</body>
</html>

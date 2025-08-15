<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monitoring IM Terbaru</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">

<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-bold mb-4">Monitoring IM Terbaru</h2>
    <div id="monitoringTable" class="overflow-x-auto"></div>
</div>

<script>
function loadMonitoringData() {
    fetch('monitoring_data.php')
        .then(res => res.text())
        .then(html => {
            document.getElementById('monitoringTable').innerHTML = html;
        })
        .catch(err => console.error('Gagal memuat data:', err));
    }
    loadMonitoringData();
    setInterval(loadMonitoringData, 5000);
</script>

</body>
</html>

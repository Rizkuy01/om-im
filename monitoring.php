<div class="bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-bold mb-4">Monitoring IM Terbaru</h2>
    <div id="monitoringTable" class="overflow-x-auto">
        <!-- Data akan dimuat lewat AJAX -->
    </div>
</div>

<script>
function loadMonitoringData() {
    fetch('monitoring_data.php')
        .then(res => res.text())
        .then(html => {
            document.getElementById('monitoringTable').innerHTML = html;
        })
        .catch(err => console.error(err));
}

// Load pertama kali
loadMonitoringData();

// Auto refresh setiap 5 detik
setInterval(loadMonitoringData, 5000);
</script>

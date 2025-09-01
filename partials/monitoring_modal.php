<!-- Modal Monitoring -->
<div id="monitoringModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
    
    <!-- Tombol Close -->
    <button onclick="closeMonitoringModal()" class="absolute top-2 right-2 px-3 text-gray-500 hover:text-gray-700">
      ✕
    </button>

    <h3 class="text-lg font-bold text-gray-800 mb-4">Akses Monitoring</h3>

    <form id="monitoringForm" method="GET" action="">
      <input type="hidden" name="page" value="monitoring">

      <!-- Input NPK -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">NPK</label>
        <input type="text" name="npk" required
          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-red-300">
      </div>

      <!-- Dropdown Mesin -->
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Mesin</label>
        <select name="machine" required
          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-red-300">
          <option value="">-- Pilih Mesin --</option>
        </select>
      </div>

      <!-- Tombol -->
      <div class="flex justify-end gap-2">
        <button type="button" onclick="closeMonitoringModal()" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded">
          Batal
        </button>
        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded">
          OK
        </button>
      </div>
    </form>
  </div>
</div>

<script>
// Buka & Tutup Modal
function openMonitoringModal() {
    document.getElementById('monitoringModal').classList.remove('hidden');
    document.getElementById('monitoringModal').classList.add('flex');
}
function closeMonitoringModal() {
    document.getElementById('monitoringModal').classList.add('hidden');
    document.getElementById('monitoringModal').classList.remove('flex');
}

// AJAX untuk ambil mesin berdasarkan NPK
document.addEventListener("DOMContentLoaded", () => {
  const npkInput = document.querySelector("input[name='npk']");
  const machineSelect = document.querySelector("select[name='machine']");

  npkInput.addEventListener("blur", () => {
    const npk = npkInput.value.trim();
    if (!npk) return;

    fetch("getMachines.php?npk=" + encodeURIComponent(npk))
      .then(res => res.json())
      .then(data => {
        machineSelect.innerHTML = '<option value="">-- Pilih Mesin --</option>';
        if (data.machines && data.machines.length > 0) {
          data.machines.forEach(m => {
            machineSelect.innerHTML += `<option value="${m.id}">${m.name}</option>`;
          });
        } else {
          machineSelect.innerHTML = '<option value="">(Tidak ada mesin)</option>';
        }
      })
      .catch(err => {
        console.error("Error:", err);
        machineSelect.innerHTML = '<option value="">(Gagal ambil data)</option>';
      });
  });
});
</script>

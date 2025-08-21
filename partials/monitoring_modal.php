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

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">NPK</label>
        <input type="text" name="npk" required
          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-red-300">
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Mesin</label>
        <select name="machine" required
          class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:ring-red-300">
          <option value="">-- Pilih Mesin --</option>
          <option value="M001">Mesin 001</option>
          <option value="M002">Mesin 002</option>
          <option value="M003">Mesin 003</option>
        </select>
      </div>

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
function openMonitoringModal() {
    document.getElementById('monitoringModal').classList.remove('hidden');
    document.getElementById('monitoringModal').classList.add('flex');
}
function closeMonitoringModal() {
    document.getElementById('monitoringModal').classList.add('hidden');
    document.getElementById('monitoringModal').classList.remove('flex');
}
</script>

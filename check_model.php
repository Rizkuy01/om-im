<div class="max-w-lg mx-auto bg-white shadow-md rounded-lg p-8">
    <!-- Judul -->
    <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
        Check Model Number
    </h2>

    <!-- Form -->
    <form id="manualForm" action="manual.php" method="get" class="space-y-4">
        <input 
            type="text" 
            name="id" 
            id="modelInput" 
            placeholder="Scan/Input Model Number (Model_no)" 
            required 
            autocomplete="off"
            class="w-full px-4 py-3 rounded-md border border-gray-300 text-base focus:outline-none focus:ring-2 focus:ring-red-500"
        >
        <button 
            type="submit"
            class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-6 rounded-md transition duration-300"
        >
            OPEN OM/IM
        </button>
    </form>
</div>

<script>
document.getElementById('modelInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        if (this.value.trim() !== '') {
            document.getElementById('manualForm').submit();
        }
    }
});
</script>

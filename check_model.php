<!-- Container Utama -->
<div class="bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden max-w-7xl mx-auto">

    <!-- Header Merah -->
    <div class="bg-red-600 px-6 py-4 shadow-md border-b border-red-700">
        <h2 class="text-white text-xl font-bold tracking-wide">
            Check Model Number
        </h2>
    </div>

    <!-- Isi Konten -->
    <div class="p-8 bg-gray-50 flex justify-center">
        <!-- Card Form -->
        <div class="bg-white shadow-md rounded-lg p-8 w-full max-w-lg">
            <h3 class="text-xl font-bold text-center text-gray-800 mb-6">
                Input / Scan Model Number
            </h3>

            <!-- FORM CHECK -->
            <form id="manualForm" action="index.php" method="get" class="space-y-4">
                <input type="hidden" name="page" value="manual">

                <input 
                    type="text" 
                    id="modelInput"
                    name="id" 
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
    </div>
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

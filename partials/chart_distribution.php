<?php
// query OM
$sqlOM = "
    SELECT d.dept_name, COUNT(o.id) AS total_om
    FROM data_om o
    JOIN sub_workstations s ON o.sub_workstation_id = s.id
    JOIN workstations w ON s.workstation_id = w.id
    JOIN department d ON w.dept_id = d.id
    GROUP BY d.dept_name
    ORDER BY total_om DESC
";
$resOM = $connIMOM->query($sqlOM);
$labelsOM = $dataOM = [];
while ($r = $resOM->fetch_assoc()) {
  $labelsOM[] = $r['dept_name'];
  $dataOM[]   = (int)$r['total_om'];
}

// query IM
$sqlIM = "
    SELECT d.dept_name, COUNT(i.id) AS total_im
    FROM data_im i
    JOIN sub_workstations s ON i.sub_workstation_id = s.id
    JOIN workstations w ON s.workstation_id = w.id
    JOIN department d ON w.dept_id = d.id
    GROUP BY d.dept_name
    ORDER BY total_im DESC
";
$resIM = $connIMOM->query($sqlIM);
$labelsIM = $dataIM = [];
while ($r = $resIM->fetch_assoc()) {
  $labelsIM[] = $r['dept_name'];
  $dataIM[]   = (int)$r['total_im'];
}
?>

<!-- Chart -->
<div class="bg-white rounded-lg shadow-inner p-4 border-red-200 border">
    <h2 class="text-lg font-semibold mb-4 text-red-600 text-center">
        Distribusi OM & IM per Departemen
    </h2>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="flex flex-col items-center">
            <h3 class="font-semibold mb-2 text-green-600">OM</h3>
            <div class="w-56 h-56">
                <canvas id="omPieChart"></canvas>
            </div>
        </div>
        <div class="flex flex-col items-center">
            <h3 class="font-semibold mb-2 text-blue-600">IM</h3>
            <div class="w-56 h-56">
                <canvas id="imPieChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Script Logic -->
<script>
function generateColors(count) {
    const colors = [];
    for (let i = 0; i < count; i++) {
        const r = Math.floor(Math.random() * 255);
        const g = Math.floor(Math.random() * 255);
        const b = Math.floor(Math.random() * 255);
        colors.push(`rgba(${r}, ${g}, ${b}, 0.7)`);
    }
    return colors;
}

const labelsOM = <?= json_encode($labelsOM) ?>;
const dataOM   = <?= json_encode($dataOM) ?>;
const labelsIM = <?= json_encode($labelsIM) ?>;
const dataIM   = <?= json_encode($dataIM) ?>;

new Chart(document.getElementById('omPieChart').getContext('2d'), {
    type: 'pie',
    data: {
        labels: labelsOM,
        datasets: [{
            data: dataOM,
            backgroundColor: generateColors(labelsOM.length)
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});

new Chart(document.getElementById('imPieChart').getContext('2d'), {
    type: 'pie',
    data: {
        labels: labelsIM,
        datasets: [{
            data: dataIM,
            backgroundColor: generateColors(labelsIM.length)
        }]
    },
    options: { plugins: { legend: { position: 'bottom' } } }
});
</script>

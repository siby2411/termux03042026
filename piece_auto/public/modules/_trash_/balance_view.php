<?php
require_once __DIR__ . '/../config/database.php';
include __DIR__ . '/../views/sidebar.php';
include __DIR__ . '/../views/topbar.php';

// Récupérer données pour Chart.js
$stmt = $conn->query("SELECT pc.classe, SUM(ec.montant) as total
                      FROM ECRITURES_COMPTABLES ec
                      JOIN PLAN_COMPTABLE_UEMOA pc ON ec.compte_debite_id = pc.compte_id
                      GROUP BY pc.classe ORDER BY pc.classe");
$classes = $totaux = [];
while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
    $classes[] = 'Classe ' . $row['classe'];
    $totaux[] = $row['total'];
}
?>
<div class="container mt-4">
    <h2>Balance Comptable</h2>
    <canvas id="balanceChart" height="100"></canvas>
    <a href="export_excel.php" class="btn btn-success mt-3">Exporter Excel</a>
</div>
<?php include __DIR__ . '/../views/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('balanceChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [<?php foreach($classes as $c){ echo "'$c',"; } ?>],
        datasets: [{
            label: 'Montants',
            data: [<?php foreach($totaux as $t){ echo "$t,"; } ?>],
            backgroundColor: 'rgba(54,162,235,0.7)'
        }]
    }
});
</script>

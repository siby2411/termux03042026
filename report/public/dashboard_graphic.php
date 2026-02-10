<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Top 8 comptes par solde (même logique que bilan)
$sql = "
SELECT p.compte_id, p.intitule_compte,
  COALESCE(SUM(CASE WHEN e.compte_debite_id = p.compte_id THEN e.montant ELSE 0 END),0) AS total_debit,
  COALESCE(SUM(CASE WHEN e.compte_credite_id = p.compte_id THEN e.montant ELSE 0 END),0) AS total_credit
FROM PLAN_COMPTABLE_UEMOA p
LEFT JOIN ECRITURES_COMPTABLES e ON p.compte_id IN (e.compte_debite_id, e.compte_credite_id)
GROUP BY p.compte_id, p.intitule_compte
ORDER BY ABS(COALESCE(SUM(CASE WHEN e.compte_debite_id = p.compte_id THEN e.montant ELSE 0 END),0) - COALESCE(SUM(CASE WHEN e.compte_credite_id = p.compte_id THEN e.montant ELSE 0 END),0)) DESC
LIMIT 10;
";
$top = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// Flux: monthly net bank movements (2200)
$sql2 = "
SELECT DATE_FORMAT(date_operation,'%Y-%m') AS month,
  SUM(CASE WHEN compte_debite_id = 2200 THEN montant ELSE 0 END) AS debit,
  SUM(CASE WHEN compte_credite_id = 2200 THEN montant ELSE 0 END) AS credit
FROM ECRITURES_COMPTABLES
GROUP BY DATE_FORMAT(date_operation,'%Y-%m')
ORDER BY month;
";
$flux = $pdo->query($sql2)->fetchAll(PDO::FETCH_ASSOC);

// Prepare data for JS
$labels = [];
$values = [];
foreach($top as $t) {
    $labels[] = $t['compte_id'] . ' - ' . $t['intitule_compte'];
    $sol = (float)$t['total_debit'] - (float)$t['total_credit'];
    $values[] = abs($sol);
}

$months = [];
$netflow = [];
foreach($flux as $f){
    $months[] = $f['month'];
    $netflow[] = (float)$f['debit'] - (float)$f['credit'];
}
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Dashboard graphique</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>body{background:#f4f6f9;padding:20px}</style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Dashboard graphique — Comptabilité</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Retour</a>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card p-3 shadow-sm">
                <h5>Top comptes (solde absolu)</h5>
                <canvas id="pieChart"></canvas>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card p-3 shadow-sm">
                <h5>Flux net bancaire (compte 2200)</h5>
                <canvas id="lineChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card mt-3 p-3">
        <h6>Détails</h6>
        <p class="small-muted">Top comptes listés et flux bancaires calculés à partir des écritures (compte 2200).</p>
    </div>
</div>

<script>
const pieLabels = <?php echo json_encode($labels); ?>;
const pieData = <?php echo json_encode($values); ?>;
const months = <?php echo json_encode($months); ?>;
const netflow = <?php echo json_encode($netflow); ?>;

new Chart(document.getElementById('pieChart'), {
    type: 'pie',
    data: {
        labels: pieLabels,
        datasets: [{ data: pieData }]
    },
    options: { responsive:true, plugins:{legend:{position:'right'}} }
});

new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: months,
        datasets: [{
            label: 'Flux net bancaire',
            data: netflow,
            tension: 0.3,
            fill: true
        }]
    },
    options: { responsive:true }
});
</script>
</body>
</html>


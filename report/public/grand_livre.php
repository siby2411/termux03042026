<?php
session_start();


require __DIR__ . '/../includes/db.php';
include "layout.php";



if(!isset($_SESSION['user_id'])){
    header("Location: login.php"); exit;
}

// Récupération des comptes et calcul Grand Livre
$stmt = $pdo->query("
SELECT pd.compte_id, pd.intitule_compte,
SUM(CASE WHEN e.compte_debite_id=pd.compte_id THEN e.montant ELSE 0 END) AS total_debit,
SUM(CASE WHEN e.compte_credite_id=pd.compte_id THEN e.montant ELSE 0 END) AS total_credit
FROM PLAN_COMPTABLE_UEMOA pd
LEFT JOIN ECRITURES_COMPTABLES e ON pd.compte_id IN (e.compte_debite_id, e.compte_credite_id)
GROUP BY pd.compte_id, pd.intitule_compte
ORDER BY pd.compte_id
");
$comptes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totaux pour graphique
$labels = [];
$totalsDebit = [];
$totalsCredit = [];
$solde = [];
foreach($comptes as $c){
    $labels[] = $c['intitule_compte'];
    $totalsDebit[] = (float)$c['total_debit'];
    $totalsCredit[] = (float)$c['total_credit'];
    $solde[] = (float)$c['total_debit'] - (float)$c['total_credit'];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Grand Livre - Dashboard Comptable</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
body {padding-top:70px;}
.sidebar {width:250px;position:fixed;top:0;bottom:0;background:#343a40;color:#fff;padding:20px;}
.sidebar a {color:#fff;text-decoration:none;display:block;margin:10px 0;}
.main {margin-left:270px;}
.table-hover tbody tr:hover {background-color:#f0f0f0;}
footer {background:#343a40;color:#fff;text-align:center;padding:15px;margin-top:30px;}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
<h4>Reporting Comptable</h4>
<a href="dashboard.php">Dashboard</a>
<a href="ecriture_add.php">Ajouter Écriture</a>
<a href="ecriture_list.php">Liste Écritures</a>
<a href="compte_uemoa_add.php">Comptes UEMOA</a>
<a href="grand_livre.php">Grand Livre</a>
<a href="balance.php">Balance</a>
<a href="excel_export.php">Export Excel</a>
<a href="logout.php" class="text-danger">Déconnexion</a>
</div>

<!-- Main content -->
<div class="main container">
<h2>Grand Livre Comptable</h2>

<!-- Tableau Grand Livre -->
<div class="table-responsive mt-4">
<table class="table table-striped table-bordered table-hover">
<thead class="table-dark">
<tr>
<th>ID</th>
<th>Intitulé du Compte</th>
<th>Débit</th>
<th>Crédit</th>
<th>Solde</th>
</tr>
</thead>
<tbody>
<?php foreach($comptes as $c): ?>
<tr>
<td><?= $c['compte_id'] ?></td>
<td><?= htmlspecialchars($c['intitule_compte']) ?></td>
<td><?= number_format($c['total_debit'],2,',',' ') ?></td>
<td><?= number_format($c['total_credit'],2,',',' ') ?></td>
<td><?= number_format($c['total_debit'] - $c['total_credit'],2,',',' ') ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- Graphique Solde par compte -->
<h3 class="mt-5">Visualisation graphique</h3>
<canvas id="grandLivreChart" height="100"></canvas>

<script>
const ctx = document.getElementById('grandLivreChart').getContext('2d');
const grandLivreChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [
            {label: 'Débit', data: <?= json_encode($totalsDebit) ?>, backgroundColor: 'rgba(54,162,235,0.7)'},
            {label: 'Crédit', data: <?= json_encode($totalsCredit) ?>, backgroundColor: 'rgba(255,99,132,0.7)'},
            {label: 'Solde', data: <?= json_encode($solde) ?>, backgroundColor: 'rgba(255,206,86,0.7)'}
        ]
    },
    options: {
        responsive: true,
        scales: {
            y: {beginAtZero:true}
        }
    }
});
</script>

<!-- Export PDF / Excel -->
<div class="mt-4">
<a href="excel_export.php" class="btn btn-success me-2"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
<a href="#" class="btn btn-primary"><i class="bi bi-file-earmark-pdf"></i> Export PDF</a>
</div>
</div>

<footer>
<p>&copy; <?= date('Y') ?> - Dashboard Comptable SynthèsePro</p>
</footer>

</body>
</html>


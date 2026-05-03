<?php
require_once 'auth.php';
require_once 'db_connect.php';
include('header.php');
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<h2><i class="fas fa-chart-pie"></i> États financiers - Holding Dieynaba</h2>

<?php
// Filtres
$date_debut = $_GET['date_debut'] ?? date('Y-m-01');
$date_fin = $_GET['date_fin'] ?? date('Y-m-t');
$sens_filter = $_GET['sens'] ?? 'all';

// Requêtes pour Paris → Dakar
$sql_paris_dakar = "SELECT o.*, c.numero_suivi, e1.nom as entite_origine_nom, e2.nom as entite_destination_nom 
                    FROM operations_financieres o 
                    JOIN colis c ON o.colis_id = c.id 
                    JOIN entites e1 ON o.entite_origine = e1.id 
                    JOIN entites e2 ON o.entite_destination = e2.id 
                    WHERE o.date_operation BETWEEN '$date_debut' AND '$date_fin 23:59:59'";
if ($sens_filter == 'paris_dakar') $sql_paris_dakar .= " AND c.sens = 'paris_dakar'";
if ($sens_filter == 'dakar_paris') $sql_paris_dakar .= " AND c.sens = 'dakar_paris'";

$operations = $pdo->query($sql_paris_dakar)->fetchAll();

// Statistiques par sens
$stats_paris_dakar = $pdo->query("SELECT 
    SUM(montant_total) as total,
    SUM(montant_expedition) as expedition,
    SUM(montant_douane) as douane,
    SUM(montant_taxe) as taxes,
    COUNT(*) as nb_colis
FROM operations_financieres o 
JOIN colis c ON o.colis_id = c.id 
WHERE c.sens = 'paris_dakar' AND o.date_operation BETWEEN '$date_debut' AND '$date_fin 23:59:59'")->fetch();

$stats_dakar_paris = $pdo->query("SELECT 
    SUM(montant_total) as total,
    SUM(montant_expedition) as expedition,
    SUM(montant_douane) as douane,
    SUM(montant_taxe) as taxes,
    COUNT(*) as nb_colis
FROM operations_financieres o 
JOIN colis c ON o.colis_id = c.id 
WHERE c.sens = 'dakar_paris' AND o.date_operation BETWEEN '$date_debut' AND '$date_fin 23:59:59'")->fetch();
?>

<!-- Filtres -->
<div class="card mb-4">
    <div class="card-header bg-info text-white">Filtrer par période et direction</div>
    <div class="card-body">
        <form method="get" class="row g-3">
            <div class="col-md-3">
                <label>Date début</label>
                <input type="date" name="date_debut" class="form-control" value="<?= $date_debut ?>">
            </div>
            <div class="col-md-3">
                <label>Date fin</label>
                <input type="date" name="date_fin" class="form-control" value="<?= $date_fin ?>">
            </div>
            <div class="col-md-3">
                <label>Direction</label>
                <select name="sens" class="form-select">
                    <option value="all">Tous</option>
                    <option value="paris_dakar" <?= $sens_filter == 'paris_dakar' ? 'selected' : '' ?>>Paris → Dakar</option>
                    <option value="dakar_paris" <?= $sens_filter == 'dakar_paris' ? 'selected' : '' ?>>Dakar → Paris</option>
                </select>
            </div>
            <div class="col-md-3">
                <label>&nbsp;</label>
                <button type="submit" class="btn btn-primary w-100">Filtrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Cartes récapitulatives -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-plane-departure"></i> Paris → Dakar
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">Colis expédiés : <strong><?= $stats_paris_dakar['nb_colis'] ?? 0 ?></strong></div>
                    <div class="col-6">Total encaissé : <strong class="text-success"><?= number_format($stats_paris_dakar['total'] ?? 0, 2) ?> €</strong></div>
                    <div class="col-6">Frais d'expédition : <?= number_format($stats_paris_dakar['expedition'] ?? 0, 2) ?> €</div>
                    <div class="col-6">Frais de douane : <?= number_format($stats_paris_dakar['douane'] ?? 0, 2) ?> €</div>
                    <div class="col-6">Taxes (TVA 20%) : <?= number_format($stats_paris_dakar['taxes'] ?? 0, 2) ?> €</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-success">
            <div class="card-header bg-success text-white">
                <i class="fas fa-plane-arrival"></i> Dakar → Paris
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">Colis expédiés : <strong><?= $stats_dakar_paris['nb_colis'] ?? 0 ?></strong></div>
                    <div class="col-6">Total encaissé : <strong class="text-success"><?= number_format($stats_dakar_paris['total'] ?? 0, 2) ?> €</strong></div>
                    <div class="col-6">Frais d'expédition : <?= number_format($stats_dakar_paris['expedition'] ?? 0, 2) ?> €</div>
                    <div class="col-6">Frais de douane : <?= number_format($stats_dakar_paris['douane'] ?? 0, 2) ?> €</div>
                    <div class="col-6">Taxes (TVA 18%) : <?= number_format($stats_dakar_paris['taxes'] ?? 0, 2) ?> €</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Graphiques -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Répartition des encaissements</div>
            <div class="card-body">
                <canvas id="revenusChart" height="250"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Évolution mensuelle</div>
            <div class="card-body">
                <canvas id="evolutionChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Liste détaillée des opérations -->
<div class="card">
    <div class="card-header bg-dark text-white">Détail des opérations financières</div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr><th>Date</th><th>N° Colis</th><th>Direction</th><th>Frais expédition</th><th>Douane</th><th>Taxes</th><th>Total</th><th>Statut</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($operations as $op): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($op['date_operation'])) ?></td>
                        <td><?= htmlspecialchars($op['numero_suivi']) ?></td>
                        <td><?= $op['entite_origine_nom'] ?> → <?= $op['entite_destination_nom'] ?></td>
                        <td><?= number_format($op['montant_expedition'], 2) ?> €</td>
                        <td><?= number_format($op['montant_douane'], 2) ?> €</td>
                        <td><?= number_format($op['montant_taxe'], 2) ?> €</td>
                        <td class="fw-bold"><?= number_format($op['montant_total'], 2) ?> €</td>
                        <td><span class="badge bg-success"><?= $op['statut_paiement'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Graphique des revenus
new Chart(document.getElementById("revenusChart"), {
    type: 'pie',
    data: {
        labels: ['Paris → Dakar', 'Dakar → Paris'],
        datasets: [{
            data: [<?= $stats_paris_dakar['total'] ?? 0 ?>, <?= $stats_dakar_paris['total'] ?? 0 ?>],
            backgroundColor: ['#007bff', '#28a745']
        }]
    }
});

// Graphique d'évolution mensuelle
<?php
$evolution = $pdo->query("SELECT DATE_FORMAT(date_operation, '%Y-%m') as mois, SUM(montant_total) as total, c.sens FROM operations_financieres o JOIN colis c ON o.colis_id = c.id WHERE date_operation BETWEEN '$date_debut' AND '$date_fin 23:59:59' GROUP BY mois, c.sens ORDER BY mois")->fetchAll();
$labels = []; $parisDakar = []; $dakarParis = [];
foreach ($evolution as $e) {
    if (!in_array($e['mois'], $labels)) $labels[] = $e['mois'];
    if ($e['sens'] == 'paris_dakar') $parisDakar[$e['mois']] = $e['total'];
    else $dakarParis[$e['mois']] = $e['total'];
}
?>
new Chart(document.getElementById("evolutionChart"), {
    type: 'line',
    data: {
        labels: <?= json_encode($labels) ?>,
        datasets: [
            { label: 'Paris → Dakar', data: <?= json_encode(array_values($parisDakar)) ?>, borderColor: '#007bff', fill: false },
            { label: 'Dakar → Paris', data: <?= json_encode(array_values($dakarParis)) ?>, borderColor: '#28a745', fill: false }
        ]
    }
});
</script>

<?php include('footer.php'); ?>

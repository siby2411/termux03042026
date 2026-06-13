<?php
session_start();
require_once '../../config/database.php';
$db = getPDO();

$date_debut = $_GET['date_debut'] ?? date('Y-m-01');
$date_fin = $_GET['date_fin'] ?? date('Y-m-t');

// Requête pour les données du tableau et du graphique
$sql = "SELECT s.name as service_nom, SUM(p.montant_paye) as total_recettes 
        FROM paiements p 
        JOIN consultations c ON p.consultation_id = c.id
        JOIN services s ON c.service_id = s.id
        WHERE p.date_paiement BETWEEN ? AND ? 
        GROUP BY s.name";

$stmt = $db->prepare($sql);
$stmt->execute([$date_debut, $date_fin]);
$rapports = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Préparation des données pour Chart.js
$labels = json_encode(array_column($rapports, 'service_nom'));
$values = json_encode(array_column($rapports, 'total_recettes'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques & Rapport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media print {
            .no-print { display: none; }
            .container { width: 100%; max-width: 100%; }
        }
        .chart-container { position: relative; height: 300px; width: 100%; }
    </style>
</head>
<body class="bg-light p-4">
<div class="container card p-4 shadow-sm">
    <div class="d-flex justify-content-between mb-4 no-print">
        <h4><i class="fas fa-chart-line"></i> État Financier et Analyses</h4>
        <div>
            <button onclick="window.print()" class="btn btn-danger"><i class="fas fa-file-pdf"></i> Imprimer PDF</button>
            <a href="/modules/admin/dashboard.php" class="btn btn-secondary">Retour</a>
        </div>
    </div>

    <form method="GET" class="row g-3 mb-4 no-print">
        <div class="col-md-4"><input type="date" name="date_debut" class="form-control" value="<?= $date_debut ?>"></div>
        <div class="col-md-4"><input type="date" name="date_fin" class="form-control" value="<?= $date_fin ?>"></div>
        <div class="col-md-4"><button type="submit" class="btn btn-primary w-100">Filtrer</button></div>
    </form>

    <div class="row">
        <div class="col-md-6">
            <table class="table table-bordered">
                <thead class="table-dark"><tr><th>Service</th><th>Recettes (FCFA)</th></tr></thead>
                <tbody>
                    <?php $total = 0; foreach($rapports as $r): $total += $r['total_recettes']; ?>
                    <tr><td><?= $r['service_nom'] ?></td><td><?= number_format($r['total_recettes'], 0, ',', ' ') ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot><tr><th>TOTAL</th><th><?= number_format($total, 0, ',', ' ') ?></th></tr></tfoot>
            </table>
        </div>
        <div class="col-md-6">
            <div class="chart-container">
                <canvas id="statChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('statChart').getContext('2d');
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?= $labels ?>,
            datasets: [{ data: <?= $values ?>, backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'] }]
        }
    });
</script>
</body>
</html>

<?php
include 'includes/db.php';
include 'includes/header.php';

// 1. Analyse du Top 5 des produits les plus vendus (en volume)
$topProducts = $pdo->query("
    SELECT p.designation, SUM(l.quantite) as total_vendu 
    FROM stock_logs l 
    JOIN produits p ON l.produit_id = p.id 
    WHERE l.type = 'sortie' 
    GROUP BY p.id 
    ORDER BY total_vendu DESC LIMIT 5
")->fetchAll();

// 2. Évolution du CA sur les 6 derniers mois
$caEvolution = $pdo->query("
    SELECT DATE_FORMAT(date_commande, '%M') as mois, SUM(total_ht) as total 
    FROM commandes 
    WHERE etat = 'facturee' 
    GROUP BY MONTH(date_commande) 
    ORDER BY date_commande ASC 
    LIMIT 6
")->fetchAll();

// Préparation des données pour JS
$labelsProducts = json_encode(array_column($topProducts, 'designation'));
$dataProducts = json_encode(array_column($topProducts, 'total_vendu'));

$labelsCA = json_encode(array_column($caEvolution, 'mois'));
$dataCA = json_encode(array_column($caEvolution, 'total'));
?>

<div class="container-fluid px-4">
    <h2 class="mt-3 mb-4"><i class="fas fa-chart-pie text-indigo me-2"></i>Business Intelligence & Analytics</h2>

    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Top 5 Produits (Volumes Sorties)</div>
                <div class="card-body">
                    <canvas id="chartProducts" height="250"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">Évolution du Chiffre d'Affaires (€)</div>
                <div class="card-body">
                    <canvas id="chartCA" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Graphique Produits
new Chart(document.getElementById('chartProducts'), {
    type: 'bar',
    data: {
        labels: <?= $labelsProducts ?>,
        datasets: [{
            label: 'Unités vendues',
            data: <?= $dataProducts ?>,
            backgroundColor: '#0d6efd'
        }]
    },
    options: { responsive: true, maintainAspectRatio: false }
});

// Graphique CA
new Chart(document.getElementById('chartCA'), {
    type: 'line',
    data: {
        labels: <?= $labelsCA ?>,
        datasets: [{
            label: 'CA Mensuel (€)',
            data: <?= $dataCA ?>,
            borderColor: '#198754',
            tension: 0.3,
            fill: true,
            backgroundColor: 'rgba(25, 135, 84, 0.1)'
        }]
    },
    options: { responsive: true, maintainAspectRatio: false }
});
</script>

<?php include 'includes/footer.php'; ?>

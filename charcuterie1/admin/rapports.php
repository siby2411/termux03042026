<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$pdo = getPDO();
$year = $_GET['year'] ?? date('Y');

// ==============================================
// 1. STATISTIQUES MENSUELLES
// ==============================================

// Utiliser les colonnes de charcuterie1
$ventes_mensuelles = $pdo->query("
    SELECT 
        MONTH(date_vente) as mois,
        SUM(total_ttc) as total_ventes,
        COUNT(*) as nb_ventes
    FROM ventes
    WHERE YEAR(date_vente) = $year
    GROUP BY MONTH(date_vente)
    ORDER BY mois
")->fetchAll();

$appro_mensuelles = $pdo->query("
    SELECT 
        MONTH(date_appro) as mois,
        SUM(total) as total_appro,
        COUNT(*) as nb_appro
    FROM approvisionnements
    WHERE YEAR(date_appro) = $year
    GROUP BY MONTH(date_appro)
    ORDER BY mois
")->fetchAll();

// Initialiser les tableaux
$mois_labels = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
$ventes_data = array_fill(1, 12, 0);
$appro_data = array_fill(1, 12, 0);
$benefice_data = array_fill(1, 12, 0);

foreach ($ventes_mensuelles as $v) {
    $ventes_data[$v['mois']] = (float)$v['total_ventes'];
}
foreach ($appro_mensuelles as $a) {
    $appro_data[$a['mois']] = (float)$a['total_appro'];
}

for ($i = 1; $i <= 12; $i++) {
    $benefice_data[$i] = $ventes_data[$i] - $appro_data[$i];
}

// ==============================================
// 2. STATISTIQUES GLOBALES
// ==============================================

$total_ventes = $pdo->query("SELECT SUM(total_ttc) FROM ventes WHERE YEAR(date_vente) = $year")->fetchColumn() ?: 0;
$total_appro = $pdo->query("SELECT SUM(total) FROM approvisionnements WHERE YEAR(date_appro) = $year")->fetchColumn() ?: 0;
$benefice_net = $total_ventes - $total_appro;
$marge_brute = $total_ventes > 0 ? round(($total_ventes - $total_appro) / $total_ventes * 100, 1) : 0;

// ==============================================
// 3. VENTES PAR CATÉGORIE
// ==============================================

$ventes_par_categorie = $pdo->query("
    SELECT 
        c.nom as categorie_nom,
        SUM(vl.total_ht) as total_ventes
    FROM ventes_lignes vl
    JOIN produits p ON vl.produit_id = p.id
    JOIN categories c ON p.categorie_id = c.id
    JOIN ventes v ON vl.vente_id = v.id
    WHERE YEAR(v.date_vente) = $year
    GROUP BY c.id
    ORDER BY total_ventes DESC
")->fetchAll();

// ==============================================
// 4. TOP 10 PRODUITS
// ==============================================

$top_produits = $pdo->query("
    SELECT 
        p.nom as produit_nom,
        c.nom as categorie_nom,
        SUM(vl.quantite) as quantite_vendue,
        COUNT(DISTINCT vl.vente_id) as nb_transactions,
        SUM(vl.total_ht) as total_ventes
    FROM ventes_lignes vl
    JOIN produits p ON vl.produit_id = p.id
    JOIN categories c ON p.categorie_id = c.id
    JOIN ventes v ON vl.vente_id = v.id
    WHERE YEAR(v.date_vente) = $year
    GROUP BY p.id
    ORDER BY total_ventes DESC
    LIMIT 10
")->fetchAll();

// ==============================================
// 5. TOP 5 CLIENTS
// ==============================================

$top_clients = $pdo->query("
    SELECT 
        c.nom as client_nom,
        COUNT(v.id) as nb_achats,
        SUM(v.total_ttc) as total_achats
    FROM ventes v
    JOIN clients c ON v.client_id = c.id
    WHERE YEAR(v.date_vente) = $year
    GROUP BY c.id
    ORDER BY total_achats DESC
    LIMIT 5
")->fetchAll();

// ==============================================
// 6. VALEUR DU STOCK
// ==============================================

$stock_prix_achat = $pdo->query("SELECT SUM(stock_actuel * prix_achat) FROM produits")->fetchColumn() ?: 0;
$stock_prix_vente = $pdo->query("SELECT SUM(stock_actuel * prix_vente) FROM produits")->fetchColumn() ?: 0;
$plus_value = $stock_prix_vente - $stock_prix_achat;

// ==============================================
// 7. VENTES RÉCENTES
// ==============================================

$ventes_recentes = $pdo->query("
    SELECT v.*, c.nom as client_nom 
    FROM ventes v 
    LEFT JOIN clients c ON v.client_id = c.id 
    ORDER BY v.date_vente DESC 
    LIMIT 10
")->fetchAll();

// ==============================================
// 8. DÉPENSES PAR CATÉGORIE
// ==============================================

$depenses_categorie = $pdo->query("
    SELECT categorie, SUM(montant) as total
    FROM depenses
    WHERE YEAR(date_depense) = $year
    GROUP BY categorie
    ORDER BY total DESC
")->fetchAll();
$total_depenses = array_sum(array_column($depenses_categorie, 'total'));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapports - OMEGA Charcuterie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card { transition: transform 0.2s; border-radius: 15px; overflow: hidden; cursor: pointer; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-value { font-size: 1.8rem; font-weight: bold; }
        .bg-ventes { background: linear-gradient(135deg, #2ecc71, #27ae60); }
        .bg-appro { background: linear-gradient(135deg, #e74c3c, #c0392b); }
        .bg-benefice { background: linear-gradient(135deg, #3498db, #2980b9); }
        .rank-1 { background: linear-gradient(135deg, #ffd700, #ffb700); color: #333; }
        .rank-2 { background: linear-gradient(135deg, #c0c0c0, #a0a0a0); }
        .rank-3 { background: linear-gradient(135deg, #cd7f32, #b87333); }
    </style>
</head>
<body>
    <?php require_once 'header.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2><i class="fas fa-chart-line me-2"></i>Rapports & Statistiques</h2>
                <p class="text-muted">Analyse complète de l'activité – Exercice <?= $year ?></p>
            </div>
            <div class="col text-end">
                <form method="get" class="d-inline">
                    <select name="year" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                        <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                        <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </form>
                <button onclick="window.print()" class="btn btn-secondary ms-2"><i class="fas fa-print me-1"></i>Imprimer</button>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4"><div class="card stat-card bg-ventes text-white"><div class="card-body"><div><h6>CA Annuel <?= $year ?></h6><div class="stat-value"><?= formatMoney($total_ventes) ?></div><small>Chiffre d'affaires</small></div><i class="fas fa-chart-line fa-3x opacity-50 float-end"></i></div></div></div>
            <div class="col-md-4"><div class="card stat-card bg-appro text-white"><div class="card-body"><div><h6>Coût des Achats</h6><div class="stat-value"><?= formatMoney($total_appro) ?></div><small>Approvisionnements</small></div><i class="fas fa-truck-loading fa-3x opacity-50 float-end"></i></div></div></div>
            <div class="col-md-4"><div class="card stat-card bg-benefice text-white"><div class="card-body"><div><h6>Bénéfice Net <?= $year ?></h6><div class="stat-value"><?= formatMoney($benefice_net) ?></div><small>Marge brute : <?= $marge_brute ?>%</small></div><i class="fas fa-chart-simple fa-3x opacity-50 float-end"></i></div></div></div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-calculator me-2"></i>Compte de Résultat – <?= $year ?></h5></div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><table class="table">  <tr><th>+ Chiffre d'Affaires</th><td class="text-end"><?= formatMoney($total_ventes) ?></td></tr><tr><th>− Coût d'Achats</th><td class="text-end"><?= formatMoney($total_appro) ?></td></tr><tr class="table-active"><th>= Marge Brute</th><td class="text-end fw-bold"><?= formatMoney($total_ventes - $total_appro) ?></td></tr><tr class="table-primary"><th>= Résultat Net</th><td class="text-end fw-bold"><?= formatMoney($benefice_net) ?></td></tr></table></div>
                    <div class="col-md-6 text-center"><div class="alert <?= $benefice_net >= 0 ? 'alert-success' : 'alert-danger' ?>"><i class="fas fa-<?= $benefice_net >= 0 ? 'check-circle' : 'exclamation-triangle' ?> fa-2x mb-2"></i><h3><?= $benefice_net >= 0 ? 'BÉNÉFICE' : 'DÉFICIT' ?></h3><h2><?= formatMoney(abs($benefice_net)) ?></h2></div></div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6"><div class="card"><div class="card-header"><h5>Évolution Mensuelle <?= $year ?></h5></div><div class="card-body"><canvas id="evolutionChart" height="300"></canvas></div></div></div>
            <div class="col-md-6"><div class="card"><div class="card-header"><h5>Ventes par Catégorie – <?= $year ?></h5></div><div class="card-body"><canvas id="categorieChart" height="300"></canvas></div></div></div>
        </div>
        
        <div class="row mb-4"><div class="col-md-12"><div class="card"><div class="card-header"><h5>Bénéfice Mensuel <?= $year ?></h5></div><div class="card-body"><canvas id="beneficeChart" height="300"></canvas></div></div></div></div>

        <div class="card mb-4">
            <div class="card-header"><h5><i class="fas fa-trophy me-2"></i>Top 10 Produits – <?= $year ?></h5></div>
            <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th>#</th><th>Produit</th><th>Catégorie</th><th class="text-end">Qté vendue</th><th class="text-end">CA (FCFA)</th><th class="text-end">%</th></tr></thead><tbody><?php $total_ca = array_sum(array_column($top_produits, 'total_ventes')); foreach ($top_produits as $i => $p): $pourcentage = $total_ca > 0 ? round($p['total_ventes'] / $total_ca * 100, 1) : 0; ?><tr><td class="table-rank"><span class="badge <?= $i == 0 ? 'rank-1' : ($i == 1 ? 'rank-2' : ($i == 2 ? 'rank-3' : 'bg-secondary')) ?>"><?= $i+1 ?></span></td><td><strong><?= escape($p['produit_nom']) ?></strong></td><td><?= escape($p['categorie_nom']) ?></td><td class="text-end"><?= number_format($p['quantite_vendue'], 0, ',', ' ') ?></td><td class="text-end fw-bold"><?= formatMoney($p['total_ventes']) ?></td><td class="text-end"><?= $pourcentage ?>%</td></tr><?php endforeach; ?></tbody></table></div></div>
        </div>

        <div class="row">
            <div class="col-md-6"><div class="card"><div class="card-header"><h5><i class="fas fa-users me-2"></i>Top 5 Clients – <?= $year ?></h5></div><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>#</th><th>Client</th><th class="text-end">Achats</th><th class="text-end">CA (FCFA)</th></tr></thead><tbody><?php foreach ($top_clients as $i => $c): ?><tr><td><span class="badge bg-<?= $i == 0 ? 'warning' : ($i == 1 ? 'secondary' : 'info') ?>"><?= $i+1 ?></span></td><td><strong><?= escape($c['client_nom']) ?></strong></td><td class="text-end"><?= $c['nb_achats'] ?></td><td class="text-end fw-bold"><?= formatMoney($c['total_achats']) ?></td></tr><?php endforeach; ?></tbody></table></div></div></div></div>
            <div class="col-md-6"><div class="card"><div class="card-header"><h5><i class="fas fa-warehouse me-2"></i>Valeur du Stock Actuel</h5></div><div class="card-body"><div class="row text-center"><div class="col-4"><div class="border rounded p-3"><small>Prix d'achat</small><h4><?= formatMoney($stock_prix_achat) ?></h4></div></div><div class="col-4"><div class="border rounded p-3"><small>Prix de vente</small><h4><?= formatMoney($stock_prix_vente) ?></h4></div></div><div class="col-4"><div class="border rounded p-3 bg-success text-white"><small>Plus-value</small><h4><?= formatMoney($plus_value) ?></h4></div></div></div></div></div></div>
        </div>
    </div>

    <script>
        const moisLabels = <?= json_encode($mois_labels) ?>;
        const ventesData = <?= json_encode(array_values($ventes_data)) ?>;
        const approData = <?= json_encode(array_values($appro_data)) ?>;
        new Chart(document.getElementById('evolutionChart'), { type: 'line', data: { labels: moisLabels, datasets: [{ label: 'Ventes (FCFA)', data: ventesData, borderColor: '#2ecc71', backgroundColor: 'rgba(46,204,113,0.1)', tension: 0.4, fill: true }, { label: 'Approvisionnements (FCFA)', data: approData, borderColor: '#e74c3c', backgroundColor: 'rgba(231,76,60,0.1)', tension: 0.4, fill: true }] }, options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() + ' FCFA' } } } } });

        const beneficeData = <?= json_encode(array_values($benefice_data)) ?>;
        new Chart(document.getElementById('beneficeChart'), { type: 'bar', data: { labels: moisLabels, datasets: [{ label: 'Bénéfice (FCFA)', data: beneficeData, backgroundColor: beneficeData.map(v => v >= 0 ? '#2ecc71' : '#e74c3c') }] }, options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true, ticks: { callback: v => v.toLocaleString() + ' FCFA' } } } } });

        const catLabels = <?= json_encode(array_column($ventes_par_categorie, 'categorie_nom')) ?>;
        const catData = <?= json_encode(array_column($ventes_par_categorie, 'total_ventes')) ?>;
        if (catLabels.length > 0) new Chart(document.getElementById('categorieChart'), { type: 'doughnut', data: { labels: catLabels, datasets: [{ data: catData, backgroundColor: ['#3498db', '#2ecc71', '#f39c12', '#e74c3c', '#9b59b6', '#1abc9c', '#e67e22'] }] }, options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } } });
        else document.getElementById('categorieChart').innerHTML = '<div class="text-center text-muted py-4">Aucune vente enregistrée</div>';
    </script>
</body>
</html>

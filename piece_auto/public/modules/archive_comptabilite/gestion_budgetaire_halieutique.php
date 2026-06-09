<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Gestion budgétaire halieutique – Sen-Pêche SA";
include 'inc_navbar.php';
require_once dirname(__DIR__) . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .section-title { background: #0d6efd; color: white; padding: 8px 15px; border-radius: 20px; display: inline-block; margin-bottom: 20px; }
        .budget-card { transition: 0.2s; border-left: 5px solid #0d6efd; margin-bottom: 20px; }
        .budget-card:hover { transform: translateX(5px); background-color: #f8f9fa; }
        .alert-ecart { border-left: 5px solid; }
        .ecart-favorable { border-left-color: #28a745; background-color: #e8f8f0; }
        .ecart-defavorable { border-left-color: #dc3545; background-color: #fee; }
        .table-analyse { font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-water"></i> Gestion budgétaire halieutique – Sen-Pêche SA</h2>
                    <p>Analyse des écarts : marge de manœuvre, répartition des charges, écarts par poste, analyse financière, causes</p>
                </div>
                <div class="card-body">

                    <!-- ==================== SECTION 1 : DONNÉES BUDGÉTAIRES ET RÉELLES ==================== -->
                    <h4 class="section-title"><i class="bi bi-table"></i> 1. Données budgétaires vs réelles (exercice N)</h4>
                    <?php
                    // Données budgétaires et réelles
                    $budget = [
                        'ca' => 200000,
                        'achats' => 144000,
                        'mod' => 20000,
                        'energie' => 10000,
                        'charges_fixes' => 30000,
                        'volume' => 100
                    ];
                    $reel = [
                        'ca' => 185000,
                        'achats' => 150000,
                        'mod' => 22000,
                        'energie' => 12000,
                        'charges_fixes' => 32000,
                        'volume' => 90
                    ];
                    
                    // Calculs des écarts
                    $ecart_ca = $reel['ca'] - $budget['ca'];
                    $ecart_achats = $reel['achats'] - $budget['achats'];
                    $ecart_mod = $reel['mod'] - $budget['mod'];
                    $ecart_energie = $reel['energie'] - $budget['energie'];
                    $ecart_charges_fixes = $reel['charges_fixes'] - $budget['charges_fixes'];
                    $ecart_volume = $reel['volume'] - $budget['volume'];
                    
                    $marge_brute_budget = $budget['ca'] - $budget['achats'];
                    $marge_brute_reel = $reel['ca'] - $reel['achats'];
                    $ecart_marge_brute = $marge_brute_reel - $marge_brute_budget;
                    ?>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Poste (K FCFA)</th><th>Budget</th><th>Réel</th><th>Écart</th><th>% écart</th></tr>
                            </thead>
                            <tbody>
                                <tr><td class="fw-bold">Chiffre d'affaires</td><td class="text-end"><?= number_format($budget['ca'], 0, ',', ' ') ?></td><td class="text-end"><?= number_format($reel['ca'], 0, ',', ' ') ?></td>
                                <td class="text-end <?= $ecart_ca >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($ecart_ca, 0, ',', ' ') ?></td>
                                <td class="text-end"><?= round($ecart_ca / $budget['ca'] * 100, 1) ?>%</td></tr>
                                <tr><td class="fw-bold">Achats</td><td class="text-end"><?= number_format($budget['achats'], 0, ',', ' ') ?></td><td class="text-end"><?= number_format($reel['achats'], 0, ',', ' ') ?></td>
                                <td class="text-end <?= $ecart_achats <= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($ecart_achats, 0, ',', ' ') ?></td>
                                <td class="text-end"><?= round($ecart_achats / $budget['achats'] * 100, 1) ?>%</td></tr>
                                <tr><td class="fw-bold">Main-d'œuvre directe</td><td class="text-end"><?= number_format($budget['mod'], 0, ',', ' ') ?></td><td class="text-end"><?= number_format($reel['mod'], 0, ',', ' ') ?></td>
                                <td class="text-end <?= $ecart_mod <= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($ecart_mod, 0, ',', ' ') ?></td>
                                <td class="text-end"><?= round($ecart_mod / $budget['mod'] * 100, 1) ?>%</td></tr>
                                <tr><td class="fw-bold">Énergie</td><td class="text-end"><?= number_format($budget['energie'], 0, ',', ' ') ?></td><td class="text-end"><?= number_format($reel['energie'], 0, ',', ' ') ?></td>
                                <td class="text-end <?= $ecart_energie <= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($ecart_energie, 0, ',', ' ') ?></td>
                                <td class="text-end"><?= round($ecart_energie / $budget['energie'] * 100, 1) ?>%</td></tr>
                                <tr><td class="fw-bold">Charges fixes</td><td class="text-end"><?= number_format($budget['charges_fixes'], 0, ',', ' ') ?></td><td class="text-end"><?= number_format($reel['charges_fixes'], 0, ',', ' ') ?></td>
                                <td class="text-end <?= $ecart_charges_fixes <= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($ecart_charges_fixes, 0, ',', ' ') ?></td>
                                <td class="text-end"><?= round($ecart_charges_fixes / $budget['charges_fixes'] * 100, 1) ?>%</td></tr>
                                <tr><td class="fw-bold">Volume (tonnes)</td><td class="text-end"><?= $budget['volume'] ?></td><td class="text-end"><?= $reel['volume'] ?></td>
                                <td class="text-end"><?= $ecart_volume ?></td><td class="text-end"><?= round($ecart_volume / $budget['volume'] * 100, 1) ?>%</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== SECTION 2 : ANALYSE DE LA MARGE DE MANŒUVRE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-arrows-angle-expand"></i> 2. Analyse de la marge de manœuvre disponible</h4>
                    <div class="alert alert-info">
                        <strong>📌 Objectif :</strong> Évaluer la capacité de l'entreprise à ajuster ses charges en fonction de l'activité réelle.
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card budget-card">
                                <div class="card-body">
                                    <h5>📊 Calcul de la marge de manœuvre</h5>
                                    <p><strong>Marge brute budgétée :</strong> <?= number_format($marge_brute_budget, 0, ',', ' ') ?> K FCFA<br>
                                    <strong>Marge brute réelle :</strong> <?= number_format($marge_brute_reel, 0, ',', ' ') ?> K FCFA<br>
                                    <strong>Écart sur marge brute :</strong> <span class="<?= $ecart_marge_brute >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($ecart_marge_brute, 0, ',', ' ') ?> K FCFA</span></p>
                                    <p><strong>Interprétation :</strong> L'entreprise dispose d'une marge de manœuvre de <strong><?= round(($marge_brute_reel / $budget['ca']) * 100, 1) ?>%</strong> sur le chiffre d'affaires.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card budget-card">
                                <div class="card-body">
                                    <h5>⚖️ Taux de flexibilité des charges</h5>
                                    <p><strong>Charges variables réelles :</strong> <?= number_format($reel['achats'] + $reel['mod'] + $reel['energie'], 0, ',', ' ') ?> K FCFA<br>
                                    <strong>Charges fixes réelles :</strong> <?= number_format($reel['charges_fixes'], 0, ',', ' ') ?> K FCFA<br>
                                    <strong>Taux de flexibilité :</strong> <?= round(($reel['achats'] + $reel['mod'] + $reel['energie']) / ($reel['ca']), 1) ?>%</p>
                                    <p class="text-muted">Plus le taux de flexibilité est élevé, plus l'entreprise peut s'adapter à une baisse d'activité.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 3 : ANALYSE DE LA RÉPARTITION DES CHARGES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-pie-chart"></i> 3. Analyse de la répartition des charges</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="chargesRepartitionChart" height="200"></canvas>
                        </div>
                        <div class="col-md-6">
                            <div class="alert alert-warning">
                                <strong>🔍 Observations :</strong><br>
                                - La part des achats a augmenté de <?= round(($reel['achats']/$reel['ca'] - $budget['achats']/$budget['ca']) * 100, 1) ?> points<br>
                                - La part de la main-d'œuvre a augmenté de <?= round(($reel['mod']/$reel['ca'] - $budget['mod']/$budget['ca']) * 100, 1) ?> points<br>
                                - Dégradation de la structure des coûts
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 4 : ANALYSE DES ÉCARTS PAR POSTES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-bar-chart"></i> 4. Analyse des écarts par postes budgétaires</h4>
                    <div class="row">
                        <div class="col-md-12">
                            <canvas id="ecartsChart" height="150"></canvas>
                        </div>
                    </div>
                    <div class="alert <?= $ecart_ca >= 0 ? 'ecart-favorable' : 'ecart-defavorable' ?> mt-3">
                        <strong>📌 Synthèse des écarts :</strong><br>
                        - CA : écart <?= $ecart_ca >= 0 ? 'favorable' : 'défavorable' ?> de <?= number_format(abs($ecart_ca), 0, ',', ' ') ?> K FCFA (<?= round($ecart_ca / $budget['ca'] * 100, 1) ?>%)<br>
                        - Achats : écart <?= $ecart_achats <= 0 ? 'favorable' : 'défavorable' ?> de <?= number_format(abs($ecart_achats), 0, ',', ' ') ?> K FCFA<br>
                        - Charges de personnel : écart <?= $ecart_mod <= 0 ? 'favorable' : 'défavorable' ?> de <?= number_format(abs($ecart_mod), 0, ',', ' ') ?> K FCFA
                    </div>

                    <!-- ==================== SECTION 5 : ANALYSE FINANCIÈRE DES ÉCARTS ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-calculator"></i> 5. Analyse financière des écarts</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-analyse">
                            <thead class="table-dark">
                                <tr><th>Indicateur</th><th>Budgété</th><th>Réel</th><th>Écart</th><th>Impact</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Marge commerciale (%)</td><td><?= round($marge_brute_budget / $budget['ca'] * 100, 1) ?>%</td>
                                <td><?= round($marge_brute_reel / $reel['ca'] * 100, 1) ?>%</td>
                                <td class="<?= ($marge_brute_reel / $reel['ca'] - $marge_brute_budget / $budget['ca']) >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= round(($marge_brute_reel / $reel['ca'] - $marge_brute_budget / $budget['ca']) * 100, 1) ?> pts
                                </td>
                                <td>Rentabilité commerciale</td></tr>
                                <tr><td>Ratio charges variables / CA</td><td><?= round(($budget['achats']+$budget['mod']+$budget['energie'])/$budget['ca']*100,1) ?>%</td>
                                <td><?= round(($reel['achats']+$reel['mod']+$reel['energie'])/$reel['ca']*100,1) ?>%</td>
                                <td class="text-danger">+<?= round(($reel['achats']+$reel['mod']+$reel['energie'])/$reel['ca']*100 - ($budget['achats']+$budget['mod']+$budget['energie'])/$budget['ca']*100, 1) ?> pts</td>
                                <td>Dégradation de la structure</td></tr>
                                <tr><td>Rentabilité opérationnelle</td><td><?= round(($marge_brute_budget - $budget['mod'] - $budget['energie'] - $budget['charges_fixes']) / $budget['ca'] * 100, 1) ?>%</td>
                                <td><?= round(($marge_brute_reel - $reel['mod'] - $reel['energie'] - $reel['charges_fixes']) / $reel['ca'] * 100, 1) ?>%</td>
                                <td class="<?= (($marge_brute_reel - $reel['mod'] - $reel['energie'] - $reel['charges_fixes']) / $reel['ca'] - ($marge_brute_budget - $budget['mod'] - $budget['energie'] - $budget['charges_fixes']) / $budget['ca']) >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= round((($marge_brute_reel - $reel['mod'] - $reel['energie'] - $reel['charges_fixes']) / $reel['ca'] - ($marge_brute_budget - $budget['mod'] - $budget['energie'] - $budget['charges_fixes']) / $budget['ca']) * 100, 1) ?> pts
                                </td>
                                <td>Performance globale</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ==================== SECTION 6 : ANALYSE DÉTAILLÉE DES CAUSES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-search"></i> 6. Analyse détaillée des causes des écarts</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">🔍 Causes identifiées</div>
                                <div class="card-body">
                                    <ul>
                                        <li><strong>Écart sur volume :</strong> baisse de <?= abs($ecart_volume) ?> tonnes (-<?= round($ecart_volume / $budget['volume'] * 100, 1) ?>%) → impact sur CA</li>
                                        <li><strong>Écart sur prix d'achat :</strong> hausse du coût d'approvisionnement (matières premières)</li>
                                        <li><strong>Écart sur coût de production :</strong> hausse des charges d'énergie et de main-d'œuvre</li>
                                        <li><strong>Écart sur charges fixes :</strong> dépassement de <?= number_format($ecart_charges_fixes, 0, ',', ' ') ?> K FCFA (investissements non prévus)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-light">
                                <div class="card-header">💡 Recommandations</div>
                                <div class="card-body">
                                    <ul>
                                        <li>Renégocier les prix d'achats auprès des pêcheurs</li>
                                        <li>Optimiser la consommation énergétique (chaîne du froid)</li>
                                        <li>Revoir la politique de prix de vente pour compenser la hausse des coûts</li>
                                        <li>Mettre en place un suivi budgétaire mensuel (contrôle de gestion)</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 7 : TABLEAU DE BORD DE PILOTAGE ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-speedometer2"></i> 7. Tableau de bord – Analyse des écarts</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Niveau d'analyse</th><th>Indicateur</th><th>Statut</th><th>Action corrective</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>Marge de manœuvre</td><td>Taux de flexibilité : <?= round(($reel['achats']+$reel['mod']+$reel['energie'])/$reel['ca']*100,1) ?>%</td><td class="text-warning">🟡 Moyen</td><td>Réduire les charges fixes</td></tr>
                                <tr><td>Répartition des charges</td><td>Part achats : <?= round($reel['achats']/$reel['ca']*100,1) ?>%</td><td class="text-danger">🔴 Élevée</td><td>Renégocier fournisseurs</td></tr>
                                <tr><td>Écarts par poste</td><td>Écart CA : <?= number_format(abs($ecart_ca),0,',',' ') ?> K FCFA</td><td class="text-danger">🔴 Défavorable</td><td>Stimuler les ventes</td></tr>
                                <tr><td>Analyse financière</td><td>Rentabilité opérationnelle : <?= round(($marge_brute_reel - $reel['mod'] - $reel['energie'] - $reel['charges_fixes']) / $reel['ca'] * 100, 1) ?>%</td><td class="text-warning">🟡 En baisse</td><td>Plan d'action Lean</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-success mt-4">
                        <i class="bi bi-check-circle-fill"></i> <strong>Conclusion :</strong> L'analyse des cinq approches révèle une dégradation de la performance due principalement à la hausse des coûts d'achat et à la baisse du volume d'activité. Des actions correctives sont à mettre en place pour le prochain exercice.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Graphique de répartition des charges (budget vs réel)
const repartitionCtx = document.getElementById('chargesRepartitionChart').getContext('2d');
new Chart(repartitionCtx, {
    type: 'bar',
    data: {
        labels: ['Achats', 'Main-d\'œuvre', 'Énergie', 'Charges fixes'],
        datasets: [
            { label: 'Budget', data: [<?= $budget['achats'] ?>, <?= $budget['mod'] ?>, <?= $budget['energie'] ?>, <?= $budget['charges_fixes'] ?>], backgroundColor: '#0d6efd' },
            { label: 'Réel', data: [<?= $reel['achats'] ?>, <?= $reel['mod'] ?>, <?= $reel['energie'] ?>, <?= $reel['charges_fixes'] ?>], backgroundColor: '#dc3545' }
        ]
    },
    options: { responsive: true, plugins: { tooltip: { callbacks: { label: (ctx) => `${ctx.dataset.label}: ${ctx.raw.toLocaleString()} K FCFA` } } } }
});

// Graphique des écarts
const ecartsCtx = document.getElementById('ecartsChart').getContext('2d');
new Chart(ecartsCtx, {
    type: 'bar',
    data: {
        labels: ['CA', 'Achats', 'MOD', 'Énergie', 'Charges fixes', 'Volume'],
        datasets: [{
            label: 'Écart (K FCFA)',
            data: [<?= $ecart_ca ?>, <?= $ecart_achats ?>, <?= $ecart_mod ?>, <?= $ecart_energie ?>, <?= $ecart_charges_fixes ?>, <?= $ecart_volume * 2000 ?>],
            backgroundColor: function(ctx) {
                return ctx.raw >= 0 ? '#28a745' : '#dc3545';
            }
        }]
    },
    options: { responsive: true, scales: { y: { title: { display: true, text: 'Écart (K FCFA)' } } } }
});
</script>
<?php include 'inc_footer.php'; ?>

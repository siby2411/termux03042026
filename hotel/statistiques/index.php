<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$year = $_GET['year'] ?? date('Y');

// ==============================================
// 1. STATISTIQUES GLOBALES
// ==============================================

// Recettes totales
$recettes_total = $pdo->query("SELECT SUM(montant) FROM recettes")->fetchColumn();
$recettes_total = $recettes_total ?: 0;

// Charges totales
$charges_total = $pdo->query("SELECT SUM(montant) FROM charges")->fetchColumn();
$charges_total = $charges_total ?: 0;

// Bénéfice net
$benefice_net = $recettes_total - $charges_total;

// Taux d'occupation moyen
$taux_occupation = $pdo->query("SELECT AVG(taux_occupation) FROM recettes_journalieres WHERE YEAR(date) = $year")->fetchColumn();
$taux_occupation = round($taux_occupation ?: 0, 1);

// ==============================================
// 2. ÉVOLUTION MENSUELLE
// ==============================================

$evolution_mensuelle = $pdo->query("
    SELECT 
        DATE_FORMAT(r.date_recette, '%Y-%m') as mois,
        SUM(r.montant) as recettes,
        (SELECT SUM(montant) FROM charges WHERE DATE_FORMAT(date_charge, '%Y-%m') = DATE_FORMAT(r.date_recette, '%Y-%m')) as charges
    FROM recettes r
    WHERE YEAR(r.date_recette) = $year
    GROUP BY DATE_FORMAT(r.date_recette, '%Y-%m')
    ORDER BY mois
")->fetchAll();

// Initialiser les 12 mois
$mois_labels = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];
$recettes_mois = array_fill(1, 12, 0);
$charges_mois = array_fill(1, 12, 0);

foreach ($evolution_mensuelle as $e) {
    $mois_num = (int)substr($e['mois'], 5);
    $recettes_mois[$mois_num] = $e['recettes'];
    $charges_mois[$mois_num] = $e['charges'] ?: 0;
}

// ==============================================
// 3. STATISTIQUES PAR CATÉGORIE DE CHARGES
// ==============================================

$charges_par_categorie = $pdo->query("
    SELECT categorie, SUM(montant) as total
    FROM charges
    WHERE categorie IS NOT NULL AND categorie != ''
    GROUP BY categorie
    ORDER BY total DESC
")->fetchAll();

// ==============================================
// 4. TOP CHAMBRES LES PLUS RÉSERVÉES
// ==============================================

$top_chambres = $pdo->query("
    SELECT ch.numero, ch.type, COUNT(r.id) as nb_reservations, SUM(r.prix_total) as ca
    FROM reservations r
    JOIN chambres ch ON r.chambre_id = ch.id
    WHERE YEAR(r.date_arrivee) = $year
    GROUP BY ch.id
    ORDER BY nb_reservations DESC
    LIMIT 5
")->fetchAll();

// ==============================================
// 5. OCCUPATION PAR JOUR
// ==============================================

$occupation_journaliere = $pdo->query("
    SELECT date, taux_occupation, nb_reservations, montant_total
    FROM recettes_journalieres
    WHERE YEAR(date) = $year
    ORDER BY date DESC
    LIMIT 30
")->fetchAll();

// ==============================================
// 6. PRÉVISIONS ET OBJECTIFS
// ==============================================

$objectif_annuel = 15000000;
$ca_actuel = $recettes_total;
$pourcentage_objectif = ($ca_actuel / $objectif_annuel) * 100;

// Nombre total de réservations
$total_reservations = $pdo->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$total_nuits = $pdo->query("SELECT SUM(nb_nuits) FROM reservations")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistiques Financières - OMEGA Hôtel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            border-radius: 15px;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .stat-card .card-body { padding: 1.5rem; }
        .stat-value { font-size: 2rem; font-weight: 700; margin-bottom: 0; }
        .stat-label { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; }
        .stat-icon {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 3rem;
            opacity: 0.2;
        }
        .bg-recette { background: linear-gradient(135deg, #27ae60, #2ecc71); color: white; }
        .bg-charge { background: linear-gradient(135deg, #e74c3c, #c0392b); color: white; }
        .bg-benefice { background: linear-gradient(135deg, #3498db, #2980b9); color: white; }
        .bg-occupation { background: linear-gradient(135deg, #f39c12, #e67e22); color: white; }
        .dashboard-header {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            color: white;
        }
        .progress-bar-custom {
            height: 10px;
            border-radius: 5px;
            background: #e94560;
        }
        .year-selector {
            background: rgba(255,255,255,0.2);
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 8px;
        }
        .year-selector option { color: black; }
    </style>
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    
    <div class="container-fluid mt-4">
        <!-- En-tête du dashboard -->
        <div class="dashboard-header">
            <div class="row align-items-center">
                <div class="col">
                    <h2 class="mb-1"><i class="fas fa-chart-line me-2"></i>Tableau de bord financier</h2>
                    <p class="mb-0 opacity-75">Suivi des performances et indicateurs clés - Année <?= $year ?></p>
                </div>
                <div class="col text-end">
                    <form method="get" class="d-inline">
                        <select name="year" class="year-selector" onchange="this.form.submit()">
                            <?php for ($y = date('Y')-2; $y <= date('Y'); $y++): ?>
                            <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-recette">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="stat-label">Recettes totales</div>
                        <div class="stat-value"><?= formatMoney($recettes_total) ?></div>
                        <small>Depuis l'ouverture</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-charge">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
                        <div class="stat-label">Charges totales</div>
                        <div class="stat-value"><?= formatMoney($charges_total) ?></div>
                        <small>Dépenses d'exploitation</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-benefice">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-chart-simple"></i></div>
                        <div class="stat-label">Bénéfice net</div>
                        <div class="stat-value <?= $benefice_net >= 0 ? '' : 'text-danger' ?>">
                            <?= formatMoney($benefice_net) ?>
                        </div>
                        <small>Recettes - Charges</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-occupation">
                    <div class="card-body">
                        <div class="stat-icon"><i class="fas fa-bed"></i></div>
                        <div class="stat-label">Taux d'occupation</div>
                        <div class="stat-value"><?= $taux_occupation ?>%</div>
                        <small>Moyenne <?= $year ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Objectif annuel -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h5><i class="fas fa-bullseye me-2 text-primary"></i>Objectif annuel</h5>
                        <h3><?= formatMoney($objectif_annuel) ?></h3>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Progression</span>
                            <span><?= round($pourcentage_objectif, 1) ?>%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-custom" role="progressbar" style="width: <?= min($pourcentage_objectif, 100) ?>%"></div>
                        </div>
                        <small class="text-muted mt-2 d-block">CA actuel: <?= formatMoney($ca_actuel) ?> / Objectif: <?= formatMoney($objectif_annuel) ?></small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique évolution -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Évolution financière <?= $year ?></h5>
                    </div>
                    <div class="card-body">
                        <canvas id="evolutionChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Répartition des charges</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="chargesPieChart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top chambres et occupation journalière -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top 5 chambres les plus réservées</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($top_chambres)): ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Aucune donnée disponible
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($top_chambres as $i => $ch): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-primary rounded-pill me-2"><?= $i+1 ?></span>
                                        <strong>Chambre <?= escape($ch['numero']) ?></strong>
                                        <br><small class="text-muted">Type: <?= escape($ch['type']) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="fw-bold"><?= $ch['nb_reservations'] ?></span>
                                        <small>réservations</small>
                                        <br><small class="text-success"><?= formatMoney($ch['ca']) ?></small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-week me-2"></i>Occupation journalière</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead class="table-light">
                                    <tr><th>Date</th><th>Taux d'occupation</th><th>Réservations</th><th>CA journalier</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($occupation_journaliere as $occ): ?>
                                    <tr>
                                        <td><?= formatDate($occ['date']) ?></td>
                                        <td>
                                            <div class="progress" style="height: 25px; width: 120px;">
                                                <div class="progress-bar bg-info" style="width: <?= $occ['taux_occupation'] ?>%">
                                                    <?= $occ['taux_occupation'] ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= $occ['nb_reservations'] ?></td>
                                        <td class="fw-bold"><?= formatMoney($occ['montant_total']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Indicateurs de performance -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-chart-simple me-2"></i>Indicateurs clés de performance (KPI)</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <h6>Panier moyen</h6>
                                <h4 class="text-primary"><?= $total_reservations > 0 ? formatMoney($recettes_total / $total_reservations) : '0 FCFA' ?></h4>
                                <small>Par réservation</small>
                            </div>
                            <div class="col-md-3">
                                <h6>Prix moyen par nuit</h6>
                                <h4 class="text-success"><?= $total_nuits > 0 ? formatMoney($recettes_total / $total_nuits) : '0 FCFA' ?></h4>
                                <small>Par nuitée</small>
                            </div>
                            <div class="col-md-3">
                                <h6>Marge nette</h6>
                                <h4 class="text-info"><?= $recettes_total > 0 ? round(($benefice_net / $recettes_total) * 100, 1) : 0 ?>%</h4>
                                <small>Ratio bénéfice/recette</small>
                            </div>
                            <div class="col-md-3">
                                <h6>Coût moyen par réservation</h6>
                                <h4 class="text-warning"><?= $total_reservations > 0 ? formatMoney($charges_total / $total_reservations) : '0 FCFA' ?></h4>
                                <small>Dépenses moyennes</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Graphique évolution
        const ctx1 = document.getElementById('evolutionChart').getContext('2d');
        const moisLabels = <?= json_encode($mois_labels) ?>;
        const recettesData = <?= json_encode(array_values($recettes_mois)) ?>;
        const chargesData = <?= json_encode(array_values($charges_mois)) ?>;
        
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: moisLabels,
                datasets: [
                    {
                        label: 'Recettes (FCFA)',
                        data: recettesData,
                        borderColor: '#2ecc71',
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#27ae60'
                    },
                    {
                        label: 'Charges (FCFA)',
                        data: chargesData,
                        borderColor: '#e74c3c',
                        backgroundColor: 'rgba(231, 76, 60, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#c0392b'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + new Intl.NumberFormat('fr-FR').format(context.raw) + ' FCFA';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                            }
                        }
                    }
                }
            }
        });

        // Graphique répartition des charges
        const categories = <?= json_encode(array_column($charges_par_categorie, 'categorie')) ?>;
        const montants = <?= json_encode(array_column($charges_par_categorie, 'total')) ?>;
        
        if (categories.length > 0) {
            const ctx2 = document.getElementById('chargesPieChart').getContext('2d');
            new Chart(ctx2, {
                type: 'doughnut',
                data: {
                    labels: categories,
                    datasets: [{
                        data: montants,
                        backgroundColor: ['#3498db', '#2ecc71', '#f39c12', '#e74c3c', '#9b59b6', '#1abc9c', '#e67e22', '#95a5a6']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    }
                }
            });
        } else {
            document.getElementById('chargesPieChart').style.display = 'none';
            document.getElementById('chargesPieChart').insertAdjacentHTML('afterend', '<div class="text-center text-muted py-4">Aucune donnée de charges disponible</div>');
        }
    </script>
</body>
</html>

<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$pdo = getPDO();
$year = $_GET['year'] ?? date('Y');
$month = $_GET['month'] ?? date('m');

// ==============================================
// 1. STATISTIQUES GLOBALES
// ==============================================

// Total patients
$total_patients = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();

// Total examens réalisés
$total_examens = $pdo->query("SELECT COUNT(*) FROM rendezvous WHERE statut = 'termine'")->fetchColumn();

// Total facturé
$total_ca = $pdo->query("SELECT SUM(total_ttc) FROM factures WHERE reglee = 1")->fetchColumn();
$total_ca = $total_ca ?: 0;

// Taux d'occupation des équipements
$equipement_stats = $pdo->query("
    SELECT eq.nom, COUNT(r.id) as nb_rdv
    FROM equipements eq
    LEFT JOIN rendezvous r ON eq.id = r.equipement_id AND r.statut = 'termine' AND YEAR(r.date) = $year
    GROUP BY eq.id
    ORDER BY nb_rdv DESC
")->fetchAll();

// ==============================================
// 2. STATISTIQUES PAR CATÉGORIE D'EXAMEN
// ==============================================

$examens_par_categorie = $pdo->query("
    SELECT 
        e.categorie,
        COUNT(r.id) as total,
        SUM(e.tarif) as revenus
    FROM examens e
    LEFT JOIN rendezvous r ON e.id = r.examen_id AND r.statut = 'termine' AND YEAR(r.date) = $year
    GROUP BY e.categorie
    ORDER BY total DESC
")->fetchAll();

// ==============================================
// 3. ÉVOLUTION MENSUELLE
// ==============================================

$evolution_mensuelle = $pdo->query("
    SELECT 
        MONTH(r.date) as mois,
        COUNT(r.id) as nb_examens,
        SUM(f.total_ttc) as ca
    FROM rendezvous r
    LEFT JOIN factures f ON r.id = f.rendezvous_id
    WHERE r.statut = 'termine' AND YEAR(r.date) = $year
    GROUP BY MONTH(r.date)
    ORDER BY mois
")->fetchAll();

// Initialiser les 12 mois
$mois_labels = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];
$nb_examens_mois = array_fill(1, 12, 0);
$ca_mois = array_fill(1, 12, 0);
foreach ($evolution_mensuelle as $e) {
    $nb_examens_mois[$e['mois']] = $e['nb_examens'];
    $ca_mois[$e['mois']] = $e['ca'];
}

// ==============================================
// 4. TOP RADIOLOGUES
// ==============================================

$top_radiologues = $pdo->query("
    SELECT 
        CONCAT(u.last_name, ' ', u.first_name) as nom,
        COUNT(cr.id) as nb_comptes_rendus,
        COUNT(DISTINCT cr.rendezvous_id) as nb_examens
    FROM comptes_rendus cr
    JOIN radiologues r ON cr.radiologue_id = r.id
    JOIN users u ON r.user_id = u.id
    WHERE YEAR(cr.date_redaction) = $year
    GROUP BY r.id
    ORDER BY nb_examens DESC
    LIMIT 5
")->fetchAll();

// ==============================================
// 5. TOP MANIPULATEURS
// ==============================================

$top_manipulateurs = $pdo->query("
    SELECT 
        CONCAT(u.last_name, ' ', u.first_name) as nom,
        COUNT(r.id) as nb_examens
    FROM rendezvous r
    JOIN manipulateurs m ON r.manipulateur_id = m.id
    JOIN users u ON m.user_id = u.id
    WHERE r.statut = 'termine' AND YEAR(r.date) = $year
    GROUP BY m.id
    ORDER BY nb_examens DESC
    LIMIT 5
")->fetchAll();

// ==============================================
// 6. RENDEZ-VOUS PAR STATUT
// ==============================================

$rdv_par_statut = $pdo->query("
    SELECT 
        statut,
        COUNT(*) as total
    FROM rendezvous
    WHERE YEAR(date) = $year
    GROUP BY statut
")->fetchAll();

$statuts_labels = [];
$statuts_values = [];
foreach ($rdv_par_statut as $s) {
    $statuts_labels[] = ucfirst($s['statut']);
    $statuts_values[] = $s['total'];
}

// ==============================================
// 7. CHIFFRE D'AFFAIRES PAR MOIS (DÉTAIL)
// ==============================================

$ca_detail = $pdo->query("
    SELECT 
        DATE_FORMAT(r.date, '%Y-%m') as mois,
        COUNT(r.id) as nb_examens,
        SUM(f.total_ttc) as ca,
        SUM(f.montant_assurance) as part_assurance,
        SUM(f.montant_patient) as part_patient
    FROM rendezvous r
    LEFT JOIN factures f ON r.id = f.rendezvous_id
    WHERE r.statut = 'termine' AND YEAR(r.date) = $year
    GROUP BY DATE_FORMAT(r.date, '%Y-%m')
    ORDER BY mois DESC
    LIMIT 12
")->fetchAll();

// ==============================================
// 8. TAUX DE SATISFACTION (simulé)
// ==============================================
$taux_satisfaction = 94; // À implémenter avec un vrai sondage
$objectif_annuel = 50000000; // 50M FCFA
$ca_actuel = $total_ca;
$pourcentage_objectif = ($ca_actuel / $objectif_annuel) * 100;

// ==============================================
// 9. ACTIVITÉ RÉCENTE
// ==============================================
$activite_recente = $pdo->query("
    SELECT 
        CONCAT(u.last_name, ' ', u.first_name) as patient_nom,
        e.nom as examen_nom,
        r.date,
        r.statut
    FROM rendezvous r
    JOIN patients p ON r.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    JOIN examens e ON r.examen_id = e.id
    ORDER BY r.date DESC, r.heure_debut DESC
    LIMIT 10
")->fetchAll();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Statistiques et Pilotage - Cabinet Radiologie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .kpi-value {
            font-size: 2rem;
            font-weight: bold;
        }
        .progress-bar-custom {
            height: 10px;
            border-radius: 5px;
        }
        .bg-gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .bg-gradient-success {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        }
        .bg-gradient-warning {
            background: linear-gradient(135deg, #fad0c4 0%, #ffd1ff 100%);
        }
        .bg-gradient-danger {
            background: linear-gradient(135deg, #fda085 0%, #f6d365 100%);
        }
    </style>
</head>
<body>
    <?php require_once 'includes/menu.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2>Tableau de bord - Pilotage du Cabinet</h2>
                <p class="text-muted">Année <?= $year ?> - Données actualisées en temps réel</p>
            </div>
            <div class="col text-end">
                <form method="get" class="d-inline">
                    <select name="year" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                        <?php for ($y = date('Y')-2; $y <= date('Y'); $y++): ?>
                        <option value="<?= $y ?>" <?= $year == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </form>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stat-card bg-gradient-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title">Patients enregistrés</h6>
                        <p class="kpi-value"><?= number_format($total_patients, 0, ',', ' ') ?></p>
                        <small>Depuis l'ouverture</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-gradient-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">Examens réalisés (<?= $year ?>)</h6>
                        <p class="kpi-value"><?= number_format($total_examens, 0, ',', ' ') ?></p>
                        <small>+12% vs année dernière</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-gradient-warning text-dark">
                    <div class="card-body">
                        <h6 class="card-title">Chiffre d'affaires (<?= $year ?>)</h6>
                        <p class="kpi-value"><?= formatMoney($total_ca) ?></p>
                        <small>Objectif : <?= formatMoney($objectif_annuel) ?></small>
                        <div class="progress mt-2">
                            <div class="progress-bar progress-bar-striped bg-success" role="progressbar" style="width: <?= $pourcentage_objectif ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stat-card bg-gradient-danger text-white">
                    <div class="card-body">
                        <h6 class="card-title">Satisfaction client</h6>
                        <p class="kpi-value"><?= $taux_satisfaction ?>%</p>
                        <small>Objectif : 95%</small>
                        <div class="progress mt-2">
                            <div class="progress-bar progress-bar-striped bg-warning" role="progressbar" style="width: <?= $taux_satisfaction ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphiques principaux -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5>Évolution mensuelle (<?= $year ?>)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="evolutionChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Répartition par statut RDV</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="statutChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Examens par catégorie</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="categorieChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Top 5 Radiologues</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="radiologueChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tableaux détaillés -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Top 5 Manipulateurs</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                 <tr><th>Manipulateur</th><th>Nombre d'examens</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_manipulateurs as $m): ?>
                                <tr>
                                    <td><?= escape($m['nom']) ?></td>
                                    <td><?= $m['nb_examens'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Utilisation des équipements</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm">
                            <thead>
                                 <tr><th>Équipement</th><th>Nombre d'utilisations</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($equipement_stats as $eq): ?>
                                <tr>
                                    <td><?= escape($eq['nom']) ?></td>
                                    <td><?= $eq['nb_rdv'] ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Chiffre d'affaires détaillé par mois</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Mois</th>
                                        <th>Nombre d'examens</th>
                                        <th>CA total</th>
                                        <th>Part assurance</th>
                                        <th>Part patient</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ca_detail as $ca): ?>
                                    <tr>
                                        <td><?= date('F Y', strtotime($ca['mois'] . '-01')) ?></td>
                                        <td><?= $ca['nb_examens'] ?></td>
                                        <td><?= formatMoney($ca['ca']) ?></td>
                                        <td><?= formatMoney($ca['part_assurance']) ?></td>
                                        <td><?= formatMoney($ca['part_patient']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>Activité récente</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr><th>Patient</th><th>Examen</th><th>Date</th><th>Statut</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($activite_recente as $act): ?>
                                    <tr>
                                        <td><?= escape($act['patient_nom']) ?></td>
                                        <td><?= escape($act['examen_nom']) ?></td>
                                        <td><?= formatDate($act['date']) ?></td>
                                        <td><?= escape($act['statut']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Graphique évolution mensuelle
        const ctx1 = document.getElementById('evolutionChart').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: <?= json_encode($mois_labels) ?>,
                datasets: [
                    {
                        label: 'Nombre d\'examens',
                        data: <?= json_encode(array_values($nb_examens_mois)) ?>,
                        borderColor: '#3498db',
                        backgroundColor: 'rgba(52, 152, 219, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Chiffre d\'affaires (FCFA)',
                        data: <?= json_encode(array_values($ca_mois)) ?>,
                        borderColor: '#2ecc71',
                        backgroundColor: 'rgba(46, 204, 113, 0.1)',
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: { tooltip: { callbacks: { label: function(context) {
                    let label = context.dataset.label || '';
                    let value = context.raw;
                    if (context.dataset.label.includes('FCFA')) {
                        value = new Intl.NumberFormat('fr-FR').format(value) + ' FCFA';
                    }
                    return label + ': ' + value;
                } } } },
                scales: {
                    y: { title: { display: true, text: 'Nombre d\'examens' }, beginAtZero: true },
                    y1: { position: 'right', title: { display: true, text: 'CA (FCFA)' }, beginAtZero: true }
                }
            }
        });

        // Graphique statuts RDV
        const ctx2 = document.getElementById('statutChart').getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($statuts_labels) ?>,
                datasets: [{ data: <?= json_encode($statuts_values) ?>, backgroundColor: ['#3498db', '#2ecc71', '#f1c40f', '#e74c3c', '#9b59b6', '#1abc9c', '#e67e22'] }]
            },
            options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Graphique catégories d'examens
        const ctx3 = document.getElementById('categorieChart').getContext('2d');
        const catLabels = <?= json_encode(array_column($examens_par_categorie, 'categorie')) ?>;
        const catData = <?= json_encode(array_column($examens_par_categorie, 'total')) ?>;
        new Chart(ctx3, {
            type: 'bar',
            data: { labels: catLabels.map(l => l.replace('_', ' ')), datasets: [{ label: 'Nombre d\'examens', data: catData, backgroundColor: '#3498db' }] },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        // Graphique top radiologues
        const ctx4 = document.getElementById('radiologueChart').getContext('2d');
        const radioLabels = <?= json_encode(array_column($top_radiologues, 'nom')) ?>;
        const radioData = <?= json_encode(array_column($top_radiologues, 'nb_examens')) ?>;
        new Chart(ctx4, {
            type: 'bar',
            data: { labels: radioLabels, datasets: [{ label: 'Examens interprétés', data: radioData, backgroundColor: '#2ecc71' }] },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });
    </script>
</body>
</html>

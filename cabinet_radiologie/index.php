<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$pdo = getPDO();

// Statistiques rapides pour le dashboard
$stats = [];
$stats['patients'] = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$stats['radiologues'] = $pdo->query("SELECT COUNT(*) FROM radiologues WHERE actif = 1")->fetchColumn();
$stats['examens'] = $pdo->query("SELECT COUNT(*) FROM examens WHERE actif = 1")->fetchColumn();
$stats['rdv_jour'] = $pdo->query("SELECT COUNT(*) FROM rendezvous WHERE date = CURDATE()")->fetchColumn();
$stats['rdv_semaine'] = $pdo->query("SELECT COUNT(*) FROM rendezvous WHERE date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)")->fetchColumn();
$stats['rdv_attente'] = $pdo->query("SELECT COUNT(*) FROM rendezvous WHERE statut IN ('programme','confirme') AND date >= CURDATE()")->fetchColumn();
$stats['factures_impayees'] = $pdo->query("SELECT COUNT(*) FROM factures WHERE reglee = 0")->fetchColumn();
$stats['ca_mois'] = $pdo->query("SELECT SUM(total_ttc) FROM factures WHERE reglee = 1")->fetchColumn();
$stats['ca_mois'] = $stats['ca_mois'] ?: 0;

// Top 5 examens les plus demandés
$top_examens = $pdo->query("
    SELECT e.nom, COUNT(r.id) as total
    FROM examens e
    LEFT JOIN rendezvous r ON e.id = r.examen_id AND r.statut = 'termine'
    GROUP BY e.id
    ORDER BY total DESC
    LIMIT 5
")->fetchAll();

// Rendez-vous du jour
$rdv_aujourdhui = $pdo->query("
    SELECT r.*, CONCAT(u.last_name, ' ', u.first_name) as patient_nom, e.nom as examen_nom
    FROM rendezvous r
    JOIN patients p ON r.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    JOIN examens e ON r.examen_id = e.id
    WHERE r.date = CURDATE()
    ORDER BY r.heure_debut
    LIMIT 5
")->fetchAll();

// Activité récente
$activite_recente = $pdo->query("
    SELECT CONCAT(u.last_name, ' ', u.first_name) as patient_nom, e.nom as examen_nom, r.date, r.statut
    FROM rendezvous r
    JOIN patients p ON r.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    JOIN examens e ON r.examen_id = e.id
    ORDER BY r.date DESC, r.heure_debut DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Cabinet Radiologie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .dashboard-card {
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            border-left: 4px solid;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card-primary { border-left-color: #3498db; }
        .card-success { border-left-color: #2ecc71; }
        .card-warning { border-left-color: #f1c40f; }
        .card-danger { border-left-color: #e74c3c; }
        .card-info { border-left-color: #1abc9c; }
        .kpi-value { font-size: 2rem; font-weight: bold; }
    </style>
</head>
<body>
    <?php require_once 'includes/menu.php'; ?>
    
    <div class="container-fluid mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2>Tableau de bord</h2>
                <p class="text-muted">Bienvenue, Dr. <?= escape($_SESSION['username']) ?> | <?= date('d/m/Y H:i') ?></p>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card card-primary">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Patients</h6>
                        <p class="kpi-value"><?= number_format($stats['patients'], 0, ',', ' ') ?></p>
                        <small>Total enregistrés</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card card-success">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Examens aujourd'hui</h6>
                        <p class="kpi-value"><?= $stats['rdv_jour'] ?></p>
                        <small>Rendez-vous programmés</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card card-warning">
                    <div class="card-body">
                        <h6 class="card-title text-muted">CA total</h6>
                        <p class="kpi-value"><?= formatMoney($stats['ca_mois']) ?></p>
                        <small>Chiffre d'affaires total</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card card-danger">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Factures impayées</h6>
                        <p class="kpi-value"><?= $stats['factures_impayees'] ?></p>
                        <small>À régulariser</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card dashboard-card card-info">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Radiologues actifs</h6>
                        <p class="kpi-value"><?= $stats['radiologues'] ?></p>
                        <small>Personnel médical</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card card-primary">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Rendez-vous cette semaine</h6>
                        <p class="kpi-value"><?= $stats['rdv_semaine'] ?></p>
                        <small>Programmés</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card dashboard-card card-warning">
                    <div class="card-body">
                        <h6 class="card-title text-muted">Examens en attente</h6>
                        <p class="kpi-value"><?= $stats['rdv_attente'] ?></p>
                        <small>À traiter</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top examens et RDV du jour -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Top 5 examens les plus demandés</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr><th>Examen</th><th>Nombre</th> </thead>
                            <tbody>
                                <?php foreach ($top_examens as $e): ?>
                                <tr><td><?= escape($e['nom']) ?></td><td><?= $e['total'] ?></td> </tr>
                                <?php endforeach; ?>
                            </tbody>
                         </table>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">Rendez-vous du jour</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($rdv_aujourdhui)): ?>
                            <p class="text-muted">Aucun rendez-vous pour aujourd'hui</p>
                        <?php else: ?>
                            <table class="table table-sm">
                                <thead> <tr><th>Heure</th><th>Patient</th><th>Examen</th></tr> </thead>
                                <tbody>
                                    <?php foreach ($rdv_aujourdhui as $r): ?>
                                    <tr>
                                        <td><?= substr($r['heure_debut'], 0, 5) ?></td>
                                        <td><?= escape($r['patient_nom']) ?></td>
                                        <td><?= escape($r['examen_nom']) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                             </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activité récente -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Activité récente</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
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
                        <a href="statistiques.php" class="btn btn-sm btn-primary">Voir toutes les statistiques →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

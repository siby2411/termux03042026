<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'caissier') {
    header('Location: /login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupérer les infos du caissier
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Statistiques du jour
$today = date('Y-m-d');
$stats = [
    'paiements_ajd' => $db->prepare("SELECT COUNT(*) FROM paiements WHERE caissier_id = ? AND DATE(date_paiement) = ?"),
    'montant_ajd' => $db->prepare("SELECT COALESCE(SUM(montant_total), 0) FROM paiements WHERE caissier_id = ? AND DATE(date_paiement) = ?"),
    'patients_ajd' => $db->prepare("SELECT COUNT(*) FROM patients WHERE created_by = ? AND DATE(created_at) = ?"),
    'total_paiements' => $db->prepare("SELECT COUNT(*) FROM paiements WHERE caissier_id = ?"),
    'total_montant' => $db->prepare("SELECT COALESCE(SUM(montant_total), 0) FROM paiements WHERE caissier_id = ?"),
    'total_patients' => $db->prepare("SELECT COUNT(*) FROM patients WHERE created_by = ?")
];

$stats['paiements_ajd']->execute([$_SESSION['user_id'], $today]);
$stats['montant_ajd']->execute([$_SESSION['user_id'], $today]);
$stats['patients_ajd']->execute([$_SESSION['user_id'], $today]);
$stats['total_paiements']->execute([$_SESSION['user_id']]);
$stats['total_montant']->execute([$_SESSION['user_id']]);
$stats['total_patients']->execute([$_SESSION['user_id']]);

// Derniers paiements
$paiements_recents = $db->prepare("
    SELECT p.*, pat.nom, pat.prenom, pat.code_patient_unique
    FROM paiements p
    JOIN patients pat ON p.patient_id = pat.id
    WHERE p.caissier_id = ?
    ORDER BY p.date_paiement DESC
    LIMIT 10
");
$paiements_recents->execute([$_SESSION['user_id']]);

// Derniers patients créés
$patients_recents = $db->prepare("
    SELECT * FROM patients 
    WHERE created_by = ?
    ORDER BY created_at DESC
    LIMIT 10
");
$patients_recents->execute([$_SESSION['user_id']]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Caissier - <?= $user['prenom'] ?> <?= $user['nom'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; }
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 15px;
        }
        .sidebar {
            background: white;
            height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
        }
        .sidebar-menu {
            list-style: none;
            padding: 0;
        }
        .sidebar-menu li {
            margin-bottom: 10px;
        }
        .sidebar-menu a {
            display: block;
            padding: 12px 15px;
            border-radius: 8px;
            color: #333;
            text-decoration: none;
            transition: all 0.2s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: #667eea;
            color: white;
        }
        .sidebar-menu i {
            margin-right: 10px;
            width: 20px;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid;
        }
        .stats-card.primary { border-left-color: #667eea; }
        .stats-card.success { border-left-color: #28a745; }
        .stats-card.warning { border-left-color: #ffc107; }
        .stats-card.info { border-left-color: #17a2b8; }
        .stats-number {
            font-size: 2.2em;
            font-weight: bold;
        }
        .action-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
            margin-bottom: 20px;
        }
        .action-card:hover {
            transform: translateY(-5px);
            border-color: #667eea;
            box-shadow: 0 10px 30px rgba(102,126,234,0.2);
        }
        .action-icon {
            font-size: 2.5em;
            color: #667eea;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-cash-register fa-3x mb-2" style="color: #667eea;"></i>
                        <h5>Centre Mamadou Diop</h5>
                        <small>Espace Caissier</small>
                        <hr>
                        <div class="mt-2">
                            <strong><?= $user['prenom'] ?> <?= $user['nom'] ?></strong>
                        </div>
                    </div>
                    <ul class="sidebar-menu">
                        <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                        <li><a href="ajout_patient.php"><i class="fas fa-user-plus"></i> Nouveau patient</a></li>
                        <li><a href="paiement_traitement.php"><i class="fas fa-credit-card"></i> Paiement traitement</a></li>
                        <li><a href="etat_journalier.php"><i class="fas fa-chart-bar"></i> État journalier</a></li>
                        <li><a href="/modules/medical/edition_dossier.php"><i class="fas fa-edit"></i> Édition dossier</a></li>
                        <li><a href="historique.php"><i class="fas fa-history"></i> Historique</a></li>
                        <li><a href="/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-10 p-4">
                <!-- En-tête -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard Caissier</h2>
                    <div class="text-muted">
                        <i class="fas fa-calendar"></i> <?= date('d/m/Y H:i') ?>
                    </div>
                </div>

                <!-- Statistiques globales -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stats-card primary">
                            <div class="stats-number"><?= $stats['total_patients']->fetchColumn() ?></div>
                            <div>Total patients créés</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card success">
                            <div class="stats-number"><?= $stats['total_paiements']->fetchColumn() ?></div>
                            <div>Total paiements</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card warning">
                            <div class="stats-number"><?= number_format($stats['total_montant']->fetchColumn(), 0, ',', ' ') ?> F</div>
                            <div>Total encaissé</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card info">
                            <div class="stats-number"><?= $stats['paiements_ajd']->fetchColumn() ?></div>
                            <div>Paiements aujourd'hui</div>
                        </div>
                    </div>
                </div>

                <!-- Actions rapides -->
                <h4 class="mb-3">Actions rapides</h4>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="action-card" onclick="window.location.href='ajout_patient.php'">
                            <div class="action-icon"><i class="fas fa-user-plus"></i></div>
                            <h6>Nouveau patient</h6>
                            <small>Enregistrer un patient</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="action-card" onclick="window.location.href='paiement_traitement.php'">
                            <div class="action-icon"><i class="fas fa-credit-card"></i></div>
                            <h6>Paiement traitement</h6>
                            <small>Encaisser un paiement</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="action-card" onclick="window.location.href='etat_journalier.php'">
                            <div class="action-icon"><i class="fas fa-chart-bar"></i></div>
                            <h6>État journalier</h6>
                            <small>Voir les stats du jour</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="action-card" onclick="window.location.href='/modules/medical/edition_dossier.php'">
                            <div class="action-icon"><i class="fas fa-edit"></i></div>
                            <h6>Édition dossier</h6>
                            <small>Consulter un dossier</small>
                        </div>
                    </div>
                </div>

                <!-- Statistiques du jour -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <i class="fas fa-calendar-day"></i> Aujourd'hui
                            </div>
                            <div class="card-body">
                                <p>Patients créés: <strong><?= $stats['patients_ajd']->fetchColumn() ?></strong></p>
                                <p>Paiements: <strong><?= $stats['paiements_ajd']->fetchColumn() ?></strong></p>
                                <p>Montant: <strong class="text-success"><?= number_format($stats['montant_ajd']->fetchColumn(), 0, ',', ' ') ?> F</strong></p>
                            </div>
                        </div>
                    </div>

                    <!-- Derniers paiements -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <i class="fas fa-clock"></i> Derniers paiements
                            </div>
                            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                <?php 
                                $paiements = $paiements_recents->fetchAll();
                                if (empty($paiements)): ?>
                                    <p class="text-muted">Aucun paiement</p>
                                <?php else: ?>
                                    <?php foreach ($paiements as $p): ?>
                                        <div class="border-bottom pb-2 mb-2">
                                            <small><?= date('H:i', strtotime($p['date_paiement'])) ?></small>
                                            <strong class="float-end text-success"><?= number_format($p['montant_total'], 0, ',', ' ') ?> F</strong>
                                            <div><?= $p['prenom'] ?> <?= $p['nom'] ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Derniers patients créés -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <i class="fas fa-users"></i> Derniers patients
                            </div>
                            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                <?php 
                                $patients = $patients_recents->fetchAll();
                                if (empty($patients)): ?>
                                    <p class="text-muted">Aucun patient créé</p>
                                <?php else: ?>
                                    <?php foreach ($patients as $p): ?>
                                        <div class="border-bottom pb-2 mb-2">
                                            <small><?= date('H:i', strtotime($p['created_at'])) ?></small>
                                            <div><?= $p['prenom'] ?> <?= $p['nom'] ?></div>
                                            <small class="text-muted"><?= $p['code_patient_unique'] ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

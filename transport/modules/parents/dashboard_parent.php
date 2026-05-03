<?php
// modules/parents/dashboard_parent.php
session_start();
require_once '../../config/database.php';

// Vérification connexion
if(!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'parent') {
    header('Location: login_parent.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupérer les élèves du parent
$query = "SELECT e.id_eleve, e.nom_eleve, e.prenom_eleve, e.classe, e.statut_inscription,
                 ec.nom_ecole, ec.horaire_matin, ec.horaire_soir,
                 b.immatriculation, b.photo_principale,
                 (SELECT statut_paiement FROM paiements WHERE id_eleve = e.id_eleve 
                  AND mois_periode = DATE_FORMAT(CURDATE(), '%Y-%m-01') LIMIT 1) as statut_paiement
          FROM eleves e
          JOIN ecoles ec ON e.id_ecole = ec.id_ecole
          LEFT JOIN affectations a ON e.id_eleve = a.id_eleve AND a.date_affectation = CURDATE()
          LEFT JOIN bus b ON a.id_bus = b.id_bus
          WHERE e.id_parent = :id_parent";
$stmt = $db->prepare($query);
$stmt->execute([':id_parent' => $_SESSION['user_id']]);
$enfants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Parent - OMEGA Transport</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #003366 0%, #006699 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            transition: transform 0.3s;
            margin-bottom: 20px;
        }
        .stat-card:hover { transform: translateY(-5px); }
        .child-card {
            border-left: 4px solid #ff9900;
            border-radius: 15px;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 3px 10px rgba(0,0,0,0.08);
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-paye { background: #4CAF50; color: white; }
        .status-impaye { background: #f44336; color: white; }
        .status-attente { background: #ff9800; color: white; }
        .quick-action {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s;
            cursor: pointer;
        }
        .quick-action:hover { background: #e9ecef; transform: scale(1.02); }
        .footer-dashboard {
            background: #001f3f;
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }
    </style>
</head>
<body>
<div class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2><i class="fas fa-bus"></i> OMEGA Transport</h2>
                <p class="mb-0">Bienvenue, <?php echo htmlspecialchars($_SESSION['user_name']); ?></p>
            </div>
            <div class="col-md-6 text-end">
                <a href="../../logout.php" class="btn btn-light">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Cartes statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card text-center">
                <i class="fas fa-child" style="font-size: 40px; color: #003366;"></i>
                <h3><?php echo count($enfants); ?></h3>
                <p>Enfant(s) inscrit(s)</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <i class="fas fa-school" style="font-size: 40px; color: #ff9900;"></i>
                <h3><?php 
                    $ecoles = array_unique(array_column($enfants, 'nom_ecole'));
                    echo count($ecoles);
                ?></h3>
                <p>École(s)</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card text-center">
                <i class="fas fa-credit-card" style="font-size: 40px; color: #4CAF50;"></i>
                <h3><?php 
                    $payes = array_filter($enfants, function($e) { 
                        return $e['statut_paiement'] === 'paye'; 
                    });
                    echo count($payes);
                ?></h3>
                <p>À jour des paiements</p>
            </div>
        </div>
    </div>
    
    <!-- Liste des enfants -->
    <div class="row">
        <div class="col-md-12">
            <h3><i class="fas fa-users"></i> Mes enfants</h3>
            <hr>
        </div>
    </div>
    
    <?php foreach($enfants as $enfant): ?>
    <div class="row">
        <div class="col-md-12">
            <div class="child-card">
                <div class="row p-3">
                    <div class="col-md-8">
                        <h4><?php echo htmlspecialchars($enfant['prenom_eleve'] . ' ' . $enfant['nom_eleve']); ?></h4>
                        <p class="mb-1">
                            <i class="fas fa-school"></i> <strong>École:</strong> <?php echo htmlspecialchars($enfant['nom_ecole']); ?><br>
                            <i class="fas fa-clock"></i> <strong>Horaires:</strong> <?php echo $enfant['horaire_matin']; ?> / <?php echo $enfant['horaire_soir']; ?><br>
                            <i class="fas fa-bus"></i> <strong>Bus:</strong> <?php echo $enfant['immatriculation'] ?? 'Non affecté'; ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="status-badge <?php 
                            echo $enfant['statut_paiement'] === 'paye' ? 'status-paye' : 
                                ($enfant['statut_paiement'] === 'impaye' ? 'status-impaye' : 'status-attente'); 
                        ?>">
                            <?php echo $enfant['statut_paiement'] === 'paye' ? '✓ PAYÉ' : 
                                ($enfant['statut_paiement'] === 'impaye' ? '⚠ IMPAYÉ' : '⏳ EN ATTENTE'); ?>
                        </span>
                        <div class="mt-3">
                            <a href="suivi_eleve.php?id=<?php echo $enfant['id_eleve']; ?>" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> Suivi
                            </a>
                            <a href="../paiements/etats_eleve.php?id=<?php echo $enfant['id_eleve']; ?>" class="btn btn-sm btn-success">
                                <i class="fas fa-credit-card"></i> Paiements
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <!-- Actions rapides -->
    <div class="row mt-4">
        <div class="col-md-12">
            <h3><i class="fas fa-bolt"></i> Actions rapides</h3>
            <hr>
        </div>
        <div class="col-md-4">
            <div class="quick-action" onclick="window.location.href='../paiements/historique.php'">
                <i class="fas fa-history" style="font-size: 30px; color: #003366;"></i>
                <h5>Historique paiements</h5>
                <small>Consultez vos transactions</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quick-action" onclick="window.location.href='../calendrier/vue_mensuelle.php'">
                <i class="fas fa-calendar-alt" style="font-size: 30px; color: #ff9900;"></i>
                <h5>Calendrier trajets</h5>
                <small>Voir les horaires</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="quick-action" onclick="window.location.href='../cartographie/map_interface.php'">
                <i class="fas fa-map-marked-alt" style="font-size: 30px; color: #4CAF50;"></i>
                <h5>Suivi en direct</h5>
                <small>Localisez le bus</small>
            </div>
        </div>
    </div>
</div>

<div class="footer-dashboard">
    <div class="container text-center">
        <p>&copy; 2025 OMEGA INFORMATIQUE CONSULTING - Transport scolaire sécurisé</p>
        <p><i class="fas fa-phone"></i> +221 78 123 45 67 | <i class="fas fa-envelope"></i> transport@omega-consulting.sn</p>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

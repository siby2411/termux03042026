<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'medecin') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupérer les infos du médecin
$stmt = $db->prepare("SELECT u.*, s.name as service_nom 
                      FROM users u
                      JOIN services s ON u.service_id = s.id
                      WHERE u.id = ?");
$stmt->execute([$_SESSION['user_id']]);
$medecin = $stmt->fetch();

// Récupérer les rendez-vous du jour
$rdv_jour = $db->prepare("
    SELECT rv.*, p.nom, p.prenom, p.code_patient_unique
    FROM rendez_vous rv
    JOIN patients p ON rv.patient_id = p.id
    WHERE rv.medecin_id = ? AND rv.date_rdv = CURDATE()
    ORDER BY rv.heure_rdv
");
$rdv_jour->execute([$_SESSION['user_id']]);
$liste_rdv = $rdv_jour->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Médecin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; }
        .navbar {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 15px;
        }
        .container { padding: 20px; }
        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .menu-card {
            text-align: center;
            padding: 30px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            border-color: #1e3c72;
        }
        .rdv-item {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 5px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <h3><i class="fas fa-stethoscope"></i> Dr. <?= htmlspecialchars($medecin['prenom'] . ' ' . $medecin['nom']) ?> - <?= htmlspecialchars($medecin['service_nom']) ?></h3>
            <a href="../auth/logout.php" class="btn btn-sm btn-light">Déconnexion</a>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="menu-card" onclick="window.location.href='nouveau_rdv.php'">
                    <i class="fas fa-user-plus fa-3x mb-3" style="color: #1e3c72;"></i>
                    <h5>Nouveau Patient</h5>
                    <p class="text-muted">Créer un dossier patient</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="menu-card" onclick="window.location.href='consultation.php'">
                    <i class="fas fa-stethoscope fa-3x mb-3" style="color: #1e3c72;"></i>
                    <h5>Consultation</h5>
                    <p class="text-muted">Prendre en charge un patient</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="menu-card" onclick="window.location.href='rdv.php'">
                    <i class="fas fa-calendar-alt fa-3x mb-3" style="color: #1e3c72;"></i>
                    <h5>Programmer RDV</h5>
                    <p class="text-muted">Planifier un rendez-vous</p>
                </div>
            </div>
        </div>
        
        <div class="card">
            <h5>Rendez-vous du jour (<?= date('d/m/Y') ?>)</h5>
            <hr>
            <?php if (empty($liste_rdv)): ?>
                <p class="text-muted">Aucun rendez-vous aujourd'hui</p>
            <?php else: ?>
                <?php foreach($liste_rdv as $rdv): ?>
                    <div class="rdv-item">
                        <strong><?= substr($rdv['heure_rdv'], 0, 5) ?> - <?= htmlspecialchars($rdv['prenom'] . ' ' . $rdv['nom']) ?></strong>
                        <br><small class="text-muted">Code: <?= htmlspecialchars($rdv['code_patient_unique']) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
                        <div class="action-card" onclick="window.location.href='../medical/recherche_medicale.php'">
                            <div class="action-icon"><i class="fas fa-search"></i></div>
                            <h6>Recherche Médicale</h6>
                            <small>Patients, RDV, consultations</small>
                        </div>

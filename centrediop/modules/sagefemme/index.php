<?php
session_start();
require_once '../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'sagefemme') {
    header('Location: ../auth/login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sage-femme - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); padding: 15px; color: white; }
        .container { padding: 20px; }
        .actions-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 30px;
        }
        .action-card {
            background: white; border-radius: 10px; padding: 20px; text-align: center;
            cursor: pointer; transition: all 0.3s; border: 2px solid transparent;
        }
        .action-card:hover {
            transform: translateY(-5px); border-color: #1e3c72; box-shadow: 0 10px 30px rgba(30, 60, 114, 0.15);
        }
        .action-icon { font-size: 40px; color: #1e3c72; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="navbar">
        <div class="container">
            <h3><i class="fas fa-female"></i> Sage-femme - <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h3>
            <a href="../auth/logout.php" class="btn btn-sm btn-light">Déconnexion</a>
        </div>
    </div>
    
    <div class="container">
        <div class="actions-grid">
            <div class="action-card" onclick="window.location.href='../medical/recherche_medicale.php'">
                <div class="action-icon"><i class="fas fa-search"></i></div>
                <h6>Recherche Médicale</h6>
                <small>Patients, RDV, consultations</small>
            </div>
            <div class="action-card" onclick="window.location.href='../consultation/liste.php'">
                <div class="action-icon"><i class="fas fa-stethoscope"></i></div>
                <h6>Consultations</h6>
                <small>Suivi des patientes</small>
            </div>
            <div class="action-card" onclick="window.location.href='../rendezvous/liste.php'">
                <div class="action-icon"><i class="fas fa-calendar-alt"></i></div>
                <h6>Rendez-vous</h6>
                <small>Planning maternité</small>
            </div>
        </div>
    </div>
</body>
</html>

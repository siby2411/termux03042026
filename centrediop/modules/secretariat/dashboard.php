<?php
session_start();
require_once '../../config/database.php';

// Correction : Autoriser 'secretariat' ET 'admin'
if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] !== 'secretariat' && $_SESSION['user_role'] !== 'admin')) {
    header('Location: /login.php');
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Récupérer les infos de l'utilisateur
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Statistiques
$stats = [
    'rdv_ajd' => $db->query("SELECT COUNT(*) FROM rendez_vous WHERE date_rdv = CURDATE()")->fetchColumn(),
    'patients_ajd' => $db->query("SELECT COUNT(*) FROM patients WHERE DATE(created_at) = CURDATE()")->fetchColumn(),
    'file_attente' => $db->query("SELECT COUNT(*) FROM file_attente WHERE statut = 'en_attente'")->fetchColumn()
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Secrétariat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; }
        .sidebar { background: white; height: 100vh; padding: 20px; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .stats-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 p-0">
                <div class="sidebar">
                    <div class="text-center mb-4">
                        <i class="fas fa-phone-alt fa-3x mb-2" style="color: #667eea;"></i>
                        <h5>Centre Mamadou Diop</h5>
                        <small>Secrétariat</small>
                    </div>
                    <?php include $_SERVER["DOCUMENT_ROOT"] . "/includes/sidebar.php"; ?>
                </div>
            </div>
            <div class="col-md-10 p-4">
                <h2>Secrétariat - <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
                <div class="row mt-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h3><?= $stats['rdv_ajd'] ?></h3>
                            <p>Rendez-vous aujourd'hui</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h3><?= $stats['patients_ajd'] ?></h3>
                            <p>Nouveaux patients</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <h3><?= $stats['file_attente'] ?></h3>
                            <p>En file d'attente</p>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle"></i> Accédez aux dossiers patients via le menu "Édition dossier".
                </div>
            </div>
        </div>
    </div>
</body>
</html>

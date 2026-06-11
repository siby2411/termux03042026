<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /login.php');
    exit();
}
$pdo = getPDO();
$stats = $pdo->query("SELECT (SELECT COUNT(*) FROM patients) as patients, (SELECT COUNT(*) FROM consultations) as consultations, (SELECT COUNT(*) FROM rendez_vous WHERE date_rdv >= CURDATE()) as rdv, (SELECT SUM(montant_total) FROM paiements) as recettes, (SELECT COUNT(*) FROM users WHERE role = 'medecin') as medecins, (SELECT COUNT(*) FROM users WHERE role = 'caissier') as caissiers, (SELECT COUNT(*) FROM services) as services, (SELECT COUNT(*) FROM materiel) as equipements, (SELECT COUNT(*) FROM file_attente WHERE statut = 'en_attente') as file_attente, (SELECT COUNT(*) FROM salles) as salles, (SELECT COUNT(*) FROM batiments) as batiments")->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Centre Mamadou Diop</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar { background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); min-height: 100vh; color: white; padding: 20px; }
        .sidebar a { color: rgba(255,255,255,0.8); text-decoration: none; padding: 8px 15px; display: block; font-size: 14px; border-radius: 4px; }
        .sidebar a:hover { background: rgba(255,255,255,0.2); color: white; }
        .sidebar-menu { list-style: none; padding: 0; }
        .kpi-card { background: #fff; border-radius: 8px; padding: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 15px; text-align: center; }
        .kpi-value { font-size: 22px; font-weight: bold; color: #1e3c72; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <div class="sidebar">
                <h5>Centre Mamadou Diop</h5>
                <small>Administrateur</small>
                <ul class="sidebar-menu mt-3">
                    <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="../medical/edition_dossier.php"><i class="fas fa-edit"></i> Édition dossier</a></li>
                    <li class="mt-3"><small>GESTION PATIENTS</small></li>
                    <li><a href="../patients/liste.php"><i class="fas fa-users"></i> Liste patients</a></li>
                    <li><a href="../patients/form.php"><i class="fas fa-user-plus"></i> Nouveau patient</a></li>
                    <li class="mt-3"><small>RENDEZ-VOUS</small></li>
                    <li><a href="../rendezvous/liste.php"><i class="fas fa-calendar-alt"></i> Tous les RDV</a></li>
                    <li class="mt-3"><small>CONSULTATIONS</small></li>
                    <li><a href="../consultation/liste.php"><i class="fas fa-stethoscope"></i> Consultations</a></li>
                    <li class="mt-3"><small>LOCALISATION & MATÉRIEL</small></li>
                    <li><a href="batiments.php"><i class="fas fa-building"></i> Bâtiments</a></li>
                    <li><a href="salles.php"><i class="fas fa-door-open"></i> Salles</a></li>
                    <li><a href="materiel.php"><i class="fas fa-tools"></i> Matériel</a></li>
                    <li class="mt-3"><small>ADMINISTRATION & RH</small></li>
                    <li><a href="personnel.php"><i class="fas fa-user-md"></i> Personnel</a></li>
                    <li><a href="personnel_form.php" class="text-warning"><i class="fas fa-user-plus"></i> Ajouter Personnel</a></li>
                    <li><a href="services.php"><i class="fas fa-hospital"></i> Services</a></li>
                    <li><a href="../pointage/index.php"><i class="fas fa-clock"></i> Pointage</a></li>
                    <li><a href="/logout.php" class="text-danger mt-3"><i class="fas fa-sign-out-alt"></i> Déconnexion</a></li>
                </ul>
            </div>
        </div>
        <div class="col-md-10 p-4">
            <h2>Dashboard Administrateur</h2>
            <div class="row">
                <div class="col-md-3"><div class="kpi-card"><div class="kpi-value"><?= $stats['patients'] ?></div>Patients</div></div>
                <div class="col-md-3"><div class="kpi-card"><div class="kpi-value"><?= $stats['medecins'] ?></div>Médecins</div></div>
                <div class="col-md-3"><div class="kpi-card"><div class="kpi-value"><?= number_format($stats['recettes'] ?? 0) ?> FCFA</div>Recettes</div></div>
                <div class="col-md-3"><div class="kpi-card"><div class="kpi-value"><?= $stats['file_attente'] ?></div>File d'attente</div></div>
            </div>
            <div class="alert alert-info">Bienvenue dans votre interface d'administration complète.</div>
        </div>
    </div>
</div>
</body>
</html>

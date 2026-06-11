<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
$pdo = getPDO();

// Récupération des statistiques
$stats = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM patients) as patients,
    (SELECT COUNT(*) FROM consultations) as consultations,
    (SELECT COUNT(*) FROM factures) as factures,
    (SELECT SUM(montant_total) FROM paiements) as recettes")->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0"><?php require_once '../../includes/sidebar.php'; ?></div>
        <div class="col-md-10 p-4">
            <h2 class="mb-4">Dashboard Administrateur</h2>
            
            <div class="mb-4">
                <a href="/modules/consultation/form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle Consultation</a>
                <a href="/modules/consultation/liste.php" class="btn btn-info text-white"><i class="fas fa-list"></i> Suivi des Consultations</a>
            </div>

            <div class="row">
                <div class="col-md-3"><div class="card p-3 shadow-sm border-0">Patients: <strong><?= $stats['patients'] ?></strong></div></div>
                <div class="col-md-3"><div class="card p-3 shadow-sm border-0">Consultations: <strong><?= $stats['consultations'] ?></strong></div></div>
                <div class="col-md-3"><div class="card p-3 shadow-sm border-0">Factures: <strong><?= $stats['factures'] ?></strong></div></div>
                <div class="col-md-3"><div class="card p-3 shadow-sm border-0">Recettes: <strong><?= number_format($stats['recettes'] ?? 0) ?> FCFA</strong></div></div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

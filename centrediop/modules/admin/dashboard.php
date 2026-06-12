<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
$pdo = getPDO();

$stats = $pdo->query("SELECT 
    (SELECT COUNT(*) FROM patients) as patients,
    (SELECT COUNT(*) FROM consultations WHERE statut = 'en_cours') as en_cours,
    (SELECT COUNT(*) FROM consultations WHERE statut = 'attente_paiement') as paiements_attente,
    (SELECT SUM(montant_paye) FROM paiements) as recettes")->fetch();
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
        <div class="col-md-10 p-4 bg-light min-vh-100">
            <h2 class="mb-4"><i class="fas fa-tachometer-alt"></i> Dashboard Administrateur</h2>

            <div class="row mb-4">
                <div class="col-md-12">
                    <a href="/modules/consultation/form.php" class="btn btn-primary"><i class="fas fa-plus"></i> Nouvelle Consultation</a>
                    <a href="/modules/secretariat/suivi.php" class="btn btn-info text-white"><i class="fas fa-compass"></i> Orientation</a>
                    <a href="/modules/caisse/index.php" class="btn btn-success"><i class="fas fa-cash-register"></i> Caisse</a>
                    <a href="/modules/stats/rapport_financier.php" class="btn btn-warning text-dark"><i class="fas fa-file-invoice-dollar"></i> Rapport Financier</a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm border-0 mb-3">
                        <small class="text-muted">Total Patients</small>
                        <div class="fs-4"><strong><?= $stats['patients'] ?></strong></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm border-0 mb-3">
                        <small class="text-muted">Consultations en cours</small>
                        <div class="fs-4"><strong><?= $stats['en_cours'] ?></strong></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm border-0 mb-3">
                        <small class="text-muted">Attente Paiement</small>
                        <div class="fs-4 text-warning"><strong><?= $stats['paiements_attente'] ?></strong></div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 shadow-sm border-0 mb-3">
                        <small class="text-muted">Recettes Totales</small>
                        <div class="fs-4"><strong><?= number_format($stats['recettes'] ?? 0, 0, ',', ' ') ?></strong> <small>FCFA</small></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

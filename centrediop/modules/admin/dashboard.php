<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
$pdo = getPDO();

$stats = $pdo->query("SELECT
    (SELECT COUNT(*) FROM patients) as patients,
    (SELECT COUNT(*) FROM consultations WHERE statut = 'en_cours') as en_cours,
    (SELECT COUNT(*) FROM consultations WHERE statut = 'attente_paiement') as paiements_attente,
    (SELECT SUM(montant_paye) FROM paiements) as recettes,
    (SELECT COUNT(*) FROM stock WHERE quantite <= seuil_alerte) as alertes_stock,
    (SELECT COUNT(*) FROM traitements WHERE DATE(date_prescription) = CURDATE()) as prescriptions_jour")->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>.card { transition: transform 0.2s; } .card:hover { transform: translateY(-5px); }</style>
</head>
<body class="bg-light">
<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0"><?php require_once '../../includes/sidebar.php'; ?></div>
        <div class="col-md-10 p-4">
            <h2 class="mb-4"><i class="fas fa-tachometer-alt"></i> Dashboard Administrateur</h2>

            <div class="card p-4 mb-4 shadow-sm border-0">
                <h5><i class="fas fa-bars"></i> Accès Rapides</h5>
                <div class="row mt-3 g-2">
                    <div class="col-md-12">
                        <a href="/modules/secretariat/dashboard.php" class="btn btn-primary"><i class="fas fa-user-nurse"></i> Secrétariat</a>
                        <a href="/modules/pharmacie/index.php" class="btn btn-warning"><i class="fas fa-pills"></i> Pharmacie</a>
                        <a href="/modules/statistiques/index.php" class="btn btn-dark"><i class="fas fa-chart-line"></i> Statistiques</a>
                        <a href="/modules/caisse/index.php" class="btn btn-success"><i class="fas fa-cash-register"></i> Caisse</a>
                        <a href="/modules/consultation/form.php" class="btn btn-info text-white"><i class="fas fa-plus"></i> Nouvelle Consultation</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3"><div class="card p-3 shadow-sm border-0 mb-3"><small class="text-muted">Total Patients</small><div class="fs-4"><strong><?= $stats['patients'] ?></strong></div></div></div>
                <div class="col-md-3"><div class="card p-3 shadow-sm border-0 mb-3"><small class="text-muted">En cours / Attente</small><div class="fs-4"><strong><?= $stats['en_cours'] ?> / <?= $stats['paiements_attente'] ?></strong></div></div></div>
                <div class="col-md-3"><div class="card p-3 shadow-sm border-0 mb-3"><small class="text-muted">Prescriptions du jour</small><div class="fs-4 text-success"><strong><?= $stats['prescriptions_jour'] ?></strong></div></div></div>
                <div class="col-md-3"><div class="card p-3 shadow-sm border-0 mb-3"><small class="text-muted">Alerte Stock Pharmacie</small><div class="fs-4 text-danger"><strong><?= $stats['alertes_stock'] ?></strong></div></div></div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card p-4 shadow-sm border-0">
                        <small class="text-muted">Recettes Totales</small>
                        <div class="fs-2 text-primary"><strong><?= number_format($stats['recettes'] ?? 0, 0, ',', ' ') ?> FCFA</strong></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card p-4 shadow-sm border-0">
                        <h5><i class="fas fa-file-invoice-dollar text-success"></i> État Financier Rapide</h5>
                        <div class="d-flex gap-2 mt-2">
                            <a href="/modules/statistiques/index.php?date_debut=<?= date('Y-m-d') ?>&date_fin=<?= date('Y-m-d') ?>" class="btn btn-outline-success btn-sm">Rapport du Jour</a>
                            <a href="/modules/statistiques/index.php?date_debut=<?= date('Y-m-01') ?>&date_fin=<?= date('Y-m-t') ?>" class="btn btn-outline-primary btn-sm">Rapport du Mois</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>

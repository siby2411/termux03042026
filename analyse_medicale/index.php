<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$pdo = getPDO();

// Récupérer les statistiques
$stats = [];
$stats['patients'] = $pdo->query("SELECT COUNT(*) FROM patients")->fetchColumn();
$stats['medecins'] = $pdo->query("SELECT COUNT(*) FROM medecins_prescripteurs")->fetchColumn();
$stats['analyses'] = $pdo->query("SELECT COUNT(*) FROM analyses WHERE actif = 1")->fetchColumn();
$stats['prelevements_attente'] = $pdo->query("SELECT COUNT(*) FROM prelevements WHERE statut = 'programme'")->fetchColumn();
$stats['analyses_attente'] = $pdo->query("SELECT COUNT(*) FROM analyses_realisees WHERE statut = 'en_attente'")->fetchColumn();
$stats['factures_impayees'] = $pdo->query("SELECT COUNT(*) FROM factures WHERE reglee = 0")->fetchColumn();
$stats['rendezvous_jour'] = $pdo->query("SELECT COUNT(*) FROM rendezvous WHERE date = CURDATE()")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord - Laboratoire Médical</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once 'includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Tableau de bord</h2>
        <div class="row mt-4">
            <div class="col-md-3"><div class="card text-white bg-primary mb-3"><div class="card-body"><h5>Patients</h5><p class="display-6"><?= $stats['patients'] ?></p></div></div></div>
            <div class="col-md-3"><div class="card text-white bg-success mb-3"><div class="card-body"><h5>Médecins</h5><p class="display-6"><?= $stats['medecins'] ?></p></div></div></div>
            <div class="col-md-3"><div class="card text-white bg-info mb-3"><div class="card-body"><h5>Analyses actives</h5><p class="display-6"><?= $stats['analyses'] ?></p></div></div></div>
            <div class="col-md-3"><div class="card text-white bg-warning mb-3"><div class="card-body"><h5>Prélèvements programmés</h5><p class="display-6"><?= $stats['prelevements_attente'] ?></p></div></div></div>
        </div>
        <div class="row">
            <div class="col-md-4"><div class="card text-white bg-danger mb-3"><div class="card-body"><h5>Analyses en attente</h5><p class="display-6"><?= $stats['analyses_attente'] ?></p></div></div></div>
            <div class="col-md-4"><div class="card text-white bg-secondary mb-3"><div class="card-body"><h5>Factures impayées</h5><p class="display-6"><?= $stats['factures_impayees'] ?></p></div></div></div>
            <div class="col-md-4"><div class="card text-white bg-dark mb-3"><div class="card-body"><h5>Rendez-vous aujourd'hui</h5><p class="display-6"><?= $stats['rendezvous_jour'] ?></p></div></div></div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

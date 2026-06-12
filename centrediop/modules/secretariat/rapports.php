<?php
session_start();
require_once '../../config/database.php';
require_once '../../includes/auth.php';
$db = getPDO();

$date_filtre = $_GET['date'] ?? date('Y-m-d');
$stats = $db->prepare("SELECT 
    (SELECT COUNT(*) FROM consultations WHERE DATE(date_consultation) = ?) as total_consultations,
    (SELECT COUNT(*) FROM traitements WHERE DATE(date_prescription) = ?) as total_prescriptions,
    (SELECT SUM(montant_paye) FROM paiements WHERE DATE(date_paiement) = ?) as total_recettes");
$stats->execute([$date_filtre, $date_filtre, $date_filtre]);
$data = $stats->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-light p-4">
<div class="card shadow-sm p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-chart-line text-primary"></i> Rapport Journalier : <?= $date_filtre ?></h4>
        <form method="GET" class="d-flex">
            <input type="date" name="date" class="form-control me-2" value="<?= $date_filtre ?>">
            <button class="btn btn-primary">Filtrer</button>
        </form>
    </div>
    
    <div class="row text-center">
        <div class="col-md-4">
            <div class="card bg-info text-white p-3"><h5>Consultations</h5><h3><?= $data['total_consultations'] ?? 0 ?></h3></div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white p-3"><h5>Prescriptions</h5><h3><?= $data['total_prescriptions'] ?? 0 ?></h3></div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark p-3"><h5>Recettes</h5><h3><?= number_format($data['total_recettes'] ?? 0, 0, ',', ' ') ?> FCFA</h3></div>
        </div>
    </div>
</div>
</body>
</html>

<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$stmt = $pdo->query("SELECT f.*, concat(u.last_name, ' ', u.first_name) as patient_nom
                     FROM factures f
                     JOIN patients p ON f.patient_id = p.id
                     JOIN users u ON p.user_id = u.id
                     ORDER BY f.date_emission DESC");
$factures = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des factures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">Laboratoire Médical</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="../patients/liste.php">Patients</a></li>
                    <li class="nav-item"><a class="nav-link" href="../analyses/liste.php">Analyses</a></li>
                    <li class="nav-item"><a class="nav-link" href="../prelevements/liste.php">Prélèvements</a></li>
                    <li class="nav-item"><a class="nav-link active" href="liste.php">Factures</a></li>
                </ul>
                <span class="navbar-text me-3"><?= escape($_SESSION['username']) ?></span>
                <a href="../logout.php" class="btn btn-outline-light">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Liste des factures</h2>
        <table class="table table-striped">
            <thead>
                <tr><th>N° facture</th><th>Patient</th><th>Date émission</th><th>Total TTC</th><th>Réglée</th><th>Date règlement</th></tr>
            </thead>
            <tbody>
                <?php foreach ($factures as $f): ?>
                <tr>
                    <td><?= escape($f['numero_facture']) ?></td>
                    <td><?= escape($f['patient_nom']) ?></td>
                    <td><?= formatDate($f['date_emission']) ?></td>
                    <td><?= formatMoney($f['total_ttc']) ?></td>
                    <td><?= $f['reglee'] ? 'Oui' : 'Non' ?></td>
                    <td><?= $f['date_reglement'] ? formatDate($f['date_reglement']) : '-' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

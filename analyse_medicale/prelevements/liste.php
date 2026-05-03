<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$stmt = $pdo->query("SELECT p.*, concat(u.last_name, ' ', u.first_name) as patient_nom
                     FROM prelevements p
                     JOIN patients pat ON p.patient_id = pat.id
                     JOIN users u ON pat.user_id = u.id
                     ORDER BY p.date_prelevement DESC");
$prelevements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des prélèvements</title>
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
                    <li class="nav-item"><a class="nav-link active" href="liste.php">Prélèvements</a></li>
                    <li class="nav-item"><a class="nav-link" href="../factures/liste.php">Factures</a></li>
                </ul>
                <span class="navbar-text me-3"><?= escape($_SESSION['username']) ?></span>
                <a href="../logout.php" class="btn btn-outline-light">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Liste des prélèvements</h2>
        <table class="table table-striped">
            <thead>
                <tr><th>Patient</th><th>Date prélèvement</th><th>Lieu</th><th>Statut</th><th>Code barre</th></tr>
            </thead>
            <tbody>
                <?php foreach ($prelevements as $p): ?>
                <tr>
                    <td><?= escape($p['patient_nom']) ?></td>
                    <td><?= formatDateTime($p['date_prelevement']) ?></td>
                    <td><?= escape($p['lieu_prelevement']) ?></td>
                    <td><?= escape($p['statut']) ?></td>
                    <td><?= escape($p['code_barre']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

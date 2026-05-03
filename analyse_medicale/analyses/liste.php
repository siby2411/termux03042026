<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$stmt = $pdo->query("SELECT a.*, c.nom as categorie_nom
                     FROM analyses a
                     LEFT JOIN categories_analyse c ON a.categorie_id = c.id
                     ORDER BY a.nom");
$analyses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des analyses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">Laboratoire Médical</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="../patients/liste.php">Patients</a></li>
                    <li class="nav-item"><a class="nav-link active" href="liste.php">Analyses</a></li>
                    <li class="nav-item"><a class="nav-link" href="../prelevements/liste.php">Prélèvements</a></li>
                    <li class="nav-item"><a class="nav-link" href="../factures/liste.php">Factures</a></li>
                </ul>
                <span class="navbar-text me-3"><?= escape($_SESSION['username']) ?></span>
                <a href="../logout.php" class="btn btn-outline-light">Déconnexion</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Liste des analyses</h2>
        <table class="table table-striped">
            <thead>
                <tr><th>Code</th><th>Nom</th><th>Catégorie</th><th>Type prélèvement</th><th>Prix</th><th>Statut</th></tr>
            </thead>
            <tbody>
                <?php foreach ($analyses as $a): ?>
                <tr>
                    <td><?= escape($a['code_analyse']) ?></td>
                    <td><?= escape($a['nom']) ?></td>
                    <td><?= escape($a['categorie_nom']) ?></td>
                    <td><?= escape($a['type_prelevement']) ?></td>
                    <td><?= formatMoney($a['prix']) ?></td>
                    <td><?= escape($a['statut']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

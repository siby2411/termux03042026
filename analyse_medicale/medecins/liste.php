<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$stmt = $pdo->query("SELECT * FROM medecins_prescripteurs ORDER BY nom, prenom");
$medecins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des médecins</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Liste des médecins prescripteurs</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouveau médecin</a>
        <table class="table table-striped">
            <thead>
                <tr><th>ID</th><th>Nom</th><th>Prénom</th><th>Spécialité</th><th>Téléphone</th><th>Hôpital</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($medecins as $m): ?>
                <tr>
                    <td><?= $m['id'] ?></td>
                    <td><?= escape($m['nom']) ?></td>
                    <td><?= escape($m['prenom']) ?></td>
                    <td><?= escape($m['specialite']) ?></td>
                    <td><?= escape($m['telephone']) ?></td>
                    <td><?= escape($m['hopital']) ?></td>
                    <td>
                        <a href="fiche.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-info">Voir</a>
                        <a href="modifier.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

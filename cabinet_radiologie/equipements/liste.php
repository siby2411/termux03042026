<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$equipements = $pdo->query("SELECT * FROM equipements ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Équipements</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Équipements</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouvel équipement</a>
        <table class="table table-striped">
            <thead><tr><th>Nom</th><th>Type</th><th>Marque</th><th>Modèle</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($equipements as $e): ?>
                <tr>
                    <td><?= escape($e['nom']) ?></td>
                    <td><?= escape($e['type']) ?></td>
                    <td><?= escape($e['marque']) ?></td>
                    <td><?= escape($e['modele']) ?></td>
                    <td><?= escape($e['statut']) ?></td>
                    <td>
                        <a href="fiche.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-info">Voir</a>
                        <a href="modifier.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $e['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

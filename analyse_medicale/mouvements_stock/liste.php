<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$mouvements = $pdo->query("SELECT m.*, r.nom as reactif_nom, u.first_name, u.last_name FROM mouvements_stock m JOIN reactifs r ON m.reactif_id = r.id LEFT JOIN users u ON m.utilisateur_id = u.id ORDER BY m.date DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mouvements de stock</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Mouvements de stock</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouveau mouvement</a>
        <table class="table table-striped">
            <thead> <tr><th>Réactif</th><th>Type</th><th>Quantité</th><th>Date</th><th>Utilisateur</th><th>Motif</th><th>Actions</th></tr> </thead>
            <tbody>
                <?php foreach ($mouvements as $m): ?>
                <tr>
                    <td><?= escape($m['reactif_nom']) ?></td>
                    <td><?= escape($m['type']) ?></td>
                    <td><?= $m['quantite'] ?></td>
                    <td><?= formatDateTime($m['date']) ?></td>
                    <td><?= escape($m['first_name'] . ' ' . $m['last_name']) ?></td>
                    <td><?= escape($m['motif']) ?></td>
                    <td>
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

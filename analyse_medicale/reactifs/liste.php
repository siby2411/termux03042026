<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$reactifs = $pdo->query("SELECT r.*, f.nom as fournisseur_nom FROM reactifs r LEFT JOIN fournisseurs f ON r.fournisseur_id = f.id ORDER BY r.nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réactifs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Réactifs</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouveau réactif</a>
        <table class="table table-striped">
            <thead> <tr><th>Code</th><th>Nom</th><th>Lot</th><th>Expiration</th><th>Stock actuel</th><th>Fournisseur</th><th>Actions</th></tr> </thead>
            <tbody>
                <?php foreach ($reactifs as $r): ?>
                <tr>
                    <td><?= escape($r['code_reactif']) ?></td>
                    <td><?= escape($r['nom']) ?></td>
                    <td><?= escape($r['lot']) ?></td>
                    <td><?= formatDate($r['date_expiration']) ?></td>
                    <td><?= $r['stock_actuel'] ?></td>
                    <td><?= escape($r['fournisseur_nom']) ?></td>
                    <td>
                        <a href="modifier.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

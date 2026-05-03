<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$fournisseurs = $pdo->query("SELECT * FROM fournisseurs ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fournisseurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Fournisseurs</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouveau fournisseur</a>
        <table class="table table-striped">
            <thead> <tr><th>Nom</th><th>Téléphone</th><th>Email</th><th>Contact</th><th>Actions</th></tr> </thead>
            <tbody>
                <?php foreach ($fournisseurs as $f): ?>
                <tr>
                    <td><?= escape($f['nom']) ?></td>
                    <td><?= escape($f['telephone']) ?></td>
                    <td><?= escape($f['email']) ?></td>
                    <td><?= escape($f['contact']) ?></td>
                    <td>
                        <a href="modifier.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

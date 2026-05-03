<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$stmt = $pdo->query("SELECT * FROM categories_analyse ORDER BY nom");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Catégories d'analyse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Catégories d'analyse</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouvelle catégorie</a>
        <table class="table table-striped">
            <thead><tr><th>ID</th><th>Nom</th><th>Description</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td><?= escape($c['nom']) ?></td>
                    <td><?= escape($c['description']) ?></td>
                    <td>
                        <a href="modifier.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

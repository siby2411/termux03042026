<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pdo = getPDO();
$data = $pdo->query("SELECT * FROM fournisseurs ORDER BY id DESC")->fetchAll();
$title = ucfirst("fournisseurs");
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title><?= $title ?></title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Liste des <?= $title ?></h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouveau</a>
        <table class="table table-striped">
            <thead><tr><th>ID</th><th>Nom</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($data as $item): ?>
                <tr><td><?= $item['id'] ?></td><td><?= escape($item['nom'] ?? $item['code'] ?? '') ?></td>
                <td><a href="modifier.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                <a href="supprimer.php?id=<?= $item['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$clients = $pdo->query("SELECT * FROM clients ORDER BY nom")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Clients</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Liste des clients</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouveau client</a>
        <table class="table table-striped">
            <thead><tr><th>Nom</th><th>Prénom</th><th>Email</th><th>Téléphone</th><th>Ville</th><th>Type</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($clients as $c): ?>
                <tr>
                    <td><?= escape($c['nom']) ?></td>
                    <td><?= escape($c['prenom']) ?></td>
                    <td><?= escape($c['email']) ?></td>
                    <td><?= escape($c['telephone']) ?></td>
                    <td><?= escape($c['ville']) ?></td>
                    <td><?= escape($c['type_client']) ?></td>
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

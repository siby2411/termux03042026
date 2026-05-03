<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$manipulateurs = $pdo->query("SELECT m.*, u.first_name, u.last_name, u.email, u.phone FROM manipulateurs m JOIN users u ON m.user_id = u.id ORDER BY u.last_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Manipulateurs</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Manipulateurs</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouveau manipulateur</a>
        <table class="table table-striped">
            <thead><tr><th>Nom</th><th>Qualification</th><th>Licence</th><th>Email</th><th>Téléphone</th><th>Actif</th><th>Actions</th> </thead>
            <tbody>
                <?php foreach ($manipulateurs as $m): ?>
                <tr>
                    <td><?= escape($m['last_name'] . ' ' . $m['first_name']) ?> \n
                    <td><?= escape($m['qualification']) ?> \n
                    <td><?= escape($m['numero_licence']) ?> \n
                    <td><?= escape($m['email']) ?> \n
                    <td><?= escape($m['phone']) ?> \n
                    <td><?= $m['actif'] ? 'Oui' : 'Non' ?> \n
                    <td>
                        <a href="modifier.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $m['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                     \n
                 \n
                <?php endforeach; ?>
            </tbody>
         \n
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

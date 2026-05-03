<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pdo = getPDO();
$radiologues = $pdo->query("SELECT r.*, u.first_name, u.last_name, u.email, u.phone FROM radiologues r JOIN users u ON r.user_id = u.id ORDER BY u.last_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Radiologues</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4"><h2>Radiologues</h2><a href="ajouter.php" class="btn btn-success mb-3">+ Nouveau radiologue</a>
    <table class="table table-striped"><thead><tr><th>Nom</th><th>Spécialité</th><th>Ordre</th><th>Email</th><th>Téléphone</th><th>Actif</th><th>Actions</th></tr></thead>
    <tbody><?php foreach ($radiologues as $r): ?>
    <tr><td><?= escape($r['last_name'] . ' ' . $r['first_name']) ?></td><td><?= escape($r['specialite']) ?></td><td><?= escape($r['numero_ordre']) ?></td><td><?= escape($r['email']) ?></td><td><?= escape($r['phone']) ?></td><td><?= $r['actif'] ? 'Oui' : 'Non' ?></td>
    <td><a href="modifier.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
        <a href="supprimer.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a></td></tr>
    <?php endforeach; ?></tbody></table></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

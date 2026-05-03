<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$stmt = $pdo->query("SELECT c.*, u.first_name, u.last_name FROM contrats_personnel c JOIN users u ON c.personnel_id = u.id ORDER BY u.last_name");
$contrats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Contrats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Contrats du personnel</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouveau contrat</a>
        <table class="table table-striped">
            <thead> <tr><th>Personnel</th><th>Type contrat</th><th>Date début</th><th>Date fin</th><th>Salaire base</th><th>Actif</th><th>Actions</th></tr> </thead>
            <tbody>
                <?php foreach ($contrats as $c): ?>
                <tr>
                    <td><?= escape($c['last_name'] . ' ' . $c['first_name']) ?></td>
                    <td><?= escape($c['type_contrat']) ?></td>
                    <td><?= formatDate($c['date_debut']) ?></td>
                    <td><?= $c['date_fin'] ? formatDate($c['date_fin']) : '-' ?></td>
                    <td><?= formatMoney($c['salaire_base']) ?></td>
                    <td><?= $c['actif'] ? 'Oui' : 'Non' ?></td>
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

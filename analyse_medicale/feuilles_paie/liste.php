<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$stmt = $pdo->query("SELECT fp.*, u.first_name, u.last_name 
                     FROM feuilles_paie fp 
                     JOIN users u ON fp.personnel_id = u.id 
                     ORDER BY fp.annee DESC, fp.mois DESC, u.last_name");
$feuilles = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Feuilles de paie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Feuilles de paie</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouvelle feuille de paie</a>
        <table class="table table-striped">
            <thead>
                <tr><th>Personnel</th><th>Mois/Année</th><th>Salaire base</th><th>Heures travaillées</th><th>Total brut</th><th>Total net</th><th>Statut</th><th>Actions</th>
            </thead>
            <tbody>
                <?php foreach ($feuilles as $f): ?>
                 <tr>
                     <td><?= escape($f['last_name'] . ' ' . $f['first_name']) ?></td>
                     <td><?= sprintf('%02d/%d', $f['mois'], $f['annee']) ?></td>
                     <td><?= formatMoney($f['salaire_base']) ?></td>
                     <td><?= $f['heures_travaillees'] ?></td>
                     <td><?= formatMoney($f['total_brut']) ?></td>
                     <td><?= formatMoney($f['total_net']) ?></td>
                     <td><?= escape($f['statut']) ?></td>
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

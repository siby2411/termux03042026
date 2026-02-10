<?php
session_start();
require __DIR__ . '/../includes/db.php';

$ecritures = $pdo->query("
    SELECT e.*, s.nom_societe, pd.intitule_compte AS compte_debite, pc.intitule_compte AS compte_credite
    FROM ECRITURES_COMPTABLES e
    JOIN SOCIETES s ON e.societe_id = s.societe_id
    JOIN PLAN_COMPTABLE_UEMOA pd ON e.compte_debite_id = pd.compte_id
    JOIN PLAN_COMPTABLE_UEMOA pc ON e.compte_credite_id = pc.compte_id
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Liste Écritures</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
<h3>Liste des écritures</h3>
<table class="table table-striped table-bordered">
<thead>
<tr>
<th>ID</th><th>Société</th><th>Date</th><th>Libellé</th><th>Débit</th><th>Crédit</th><th>Montant</th><th>Actions</th>
</tr>
</thead>
<tbody>
<?php foreach($ecritures as $e): ?>
<tr>
<td><?= $e['ecriture_id'] ?></td>
<td><?= htmlspecialchars($e['nom_societe']) ?></td>
<td><?= $e['date_operation'] ?></td>
<td><?= htmlspecialchars($e['libelle_operation']) ?></td>
<td><?= htmlspecialchars($e['compte_debite']) ?></td>
<td><?= htmlspecialchars($e['compte_credite']) ?></td>
<td><?= $e['montant'] ?></td>
<td>
<a href="ecriture_edit.php?id=<?= $e['ecriture_id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
<a href="ecriture_delete.php?id=<?= $e['ecriture_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
</body>
</html>


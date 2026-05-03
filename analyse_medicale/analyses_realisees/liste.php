<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$stmt = $pdo->query("SELECT ar.*, CONCAT(u.last_name, ' ', u.first_name) as patient_nom, a.nom as analyse_nom
                     FROM analyses_realisees ar
                     JOIN prelevements p ON ar.prelevement_id = p.id
                     JOIN patients pat ON p.patient_id = pat.id
                     JOIN users u ON pat.user_id = u.id
                     JOIN analyses a ON ar.analyse_id = a.id
                     ORDER BY ar.date_debut DESC");
$analyses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Analyses réalisées</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Analyses réalisées</h2>
        <table class="table table-striped">
            <thead><tr><th>Patient</th><th>Analyse</th><th>Date début</th><th>Date fin</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($analyses as $a): ?>
                <tr>
                    <td><?= escape($a['patient_nom']) ?></td>
                    <td><?= escape($a['analyse_nom']) ?></td>
                    <td><?= formatDateTime($a['date_debut']) ?></td>
                    <td><?= formatDateTime($a['date_fin']) ?></td>
                    <td><?= escape($a['statut']) ?></td>
                    <td>
                        <a href="details.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-info">Détails</a>
                        <?php if ($a['statut'] == 'en_attente'): ?>
                        <a href="valider.php?id=<?= $a['id'] ?>" class="btn btn-sm btn-success">Valider</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

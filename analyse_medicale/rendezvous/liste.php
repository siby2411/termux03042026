<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$stmt = $pdo->query("SELECT r.*, CONCAT(u.last_name, ' ', u.first_name) as patient_nom FROM rendezvous r JOIN patients p ON r.patient_id = p.id JOIN users u ON p.user_id = u.id ORDER BY r.date DESC, r.heure_debut");
$rdvs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rendez-vous</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Rendez-vous</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouveau rendez-vous</a>
        <table class="table table-striped">
            <thead><tr><th>Patient</th><th>Date</th><th>Heure début</th><th>Heure fin</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($rdvs as $r): ?>
                <tr>
                    <td><?= escape($r['patient_nom']) ?></td>
                    <td><?= formatDate($r['date']) ?></td>
                    <td><?= $r['heure_debut'] ?></td>
                    <td><?= $r['heure_fin'] ?></td>
                    <td><?= escape($r['statut']) ?></td>
                    <td>
                        <a href="modifier.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

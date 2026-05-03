<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$stmt = $pdo->query("
    SELECT cr.*, 
           CONCAT(u.last_name, ' ', u.first_name) as patient_nom,
           e.nom as examen_nom,
           CONCAT(ru.last_name, ' ', ru.first_name) as radiologue_nom,
           r.date as rdv_date
    FROM comptes_rendus cr
    JOIN rendezvous r ON cr.rendezvous_id = r.id
    JOIN patients p ON r.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    JOIN examens e ON r.examen_id = e.id
    JOIN radiologues rad ON cr.radiologue_id = rad.id
    JOIN users ru ON rad.user_id = ru.id
    ORDER BY cr.date_redaction DESC
");
$comptes_rendus = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Comptes rendus</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Comptes rendus d'examens</h2>
        <table class="table table-striped">
            <thead><tr><th>Patient</th><th>Examen</th><th>Radiologue</th><th>Date RDV</th><th>Date rédaction</th><th>Signé</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($comptes_rendus as $cr): ?>
                <tr>
                    <td><?= escape($cr['patient_nom']) ?></td>
                    <td><?= escape($cr['examen_nom']) ?></td>
                    <td><?= escape($cr['radiologue_nom']) ?></td>
                    <td><?= formatDate($cr['rdv_date']) ?></td>
                    <td><?= formatDateTime($cr['date_redaction']) ?></td>
                    <td><?= $cr['signe'] ? 'Oui' : 'Non' ?></td>
                    <td>
                        <a href="fiche.php?id=<?= $cr['id'] ?>" class="btn btn-sm btn-info">Voir</a>
                        <a href="modifier.php?id=<?= $cr['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <?php if (!$cr['signe']): ?>
                        <a href="signer.php?id=<?= $cr['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Signer ce compte rendu ?')">Signer</a>
                        <?php endif; ?>
                        <a href="supprimer.php?id=<?= $cr['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

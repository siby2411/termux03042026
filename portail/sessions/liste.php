<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

$pdo = getPDO();
$sessions = $pdo->query("SELECT s.*, 
    CASE WHEN s.type = 'client' THEN c.nom ELSE f.nom END as user_nom
    FROM sessions_portail s
    LEFT JOIN clients c ON s.client_id = c.id
    LEFT JOIN fournisseurs f ON s.fournisseur_id = f.id
    ORDER BY s.derniere_activite DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Sessions</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Sessions actives</h2>
        <table class="table table-striped">
            <thead><tr><th>Utilisateur</th><th>Type</th><th>Dernière activité</th><th>Création</th></tr></thead>
            <tbody>
                <?php foreach ($sessions as $s): ?>
                <tr><td><?= escape($s['user_nom']) ?></td><td><?= escape($s['type']) ?></td>
                <td><?= formatDateTime($s['derniere_activite']) ?></td>
                <td><?= formatDateTime($s['date_creation']) ?></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

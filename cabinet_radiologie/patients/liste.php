<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$patients = $pdo->query("SELECT p.*, u.first_name, u.last_name, u.phone FROM patients p JOIN users u ON p.user_id = u.id ORDER BY u.last_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Patients</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Liste des patients</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouveau patient</a>
        <table class="table table-striped">
            <thead><tr><th>Code</th><th>Nom</th><th>Date naissance</th><th>Téléphone</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($patients as $p): ?>
                <tr><td><?= escape($p['code_patient']) ?></td><td><?= escape($p['last_name'] . ' ' . $p['first_name']) ?></td><td><?= formatDate($p['date_naissance']) ?></td><td><?= escape($p['phone']) ?></td>
                <td><a href="fiche.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-info">Voir</a>
                    <a href="modifier.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                    <a href="supprimer.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a></td></tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

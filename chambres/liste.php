<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$chambres = $pdo->query("SELECT * FROM chambres ORDER BY numero")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Chambres - OMEGA Hôtel</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2><i class="fas fa-bed me-2"></i>Liste des chambres</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouvelle chambre</a>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead><tr><th>N°</th><th>Type</th><th>Prix/nuit</th><th>Capacité</th><th>Statut</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($chambres as $c): ?>
                    <tr>
                        <td><?= escape($c['numero']) ?></td>
                        <td><?= escape($c['type']) ?></td>
                        <td><?= formatMoney($c['prix_nuit']) ?></td>
                        <td><?= $c['capacite'] ?> pers.</td>
                        <td><span class="badge bg-<?= $c['statut'] == 'disponible' ? 'success' : ($c['statut'] == 'occupe' ? 'danger' : 'warning') ?>"><?= escape($c['statut']) ?></span></td>
                        <td>
                            <a href="modifier.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                            <a href="supprimer.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$stmt = $pdo->query("SELECT p.*, f.numero_facture, u.first_name, u.last_name 
                     FROM paiements p 
                     JOIN factures f ON p.facture_id = f.id
                     LEFT JOIN users u ON p.encaisse_par_id = u.id
                     ORDER BY p.date_paiement DESC");
$paiements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Paiements</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouveau paiement</a>
        <table class="table table-striped">
            <thead>
                <tr><th>Facture</th><th>Montant</th><th>Mode</th><th>Date</th><th>Référence</th><th>Encaissé par</th><th>Actions</th>
            </thead>
            <tbody>
                <?php foreach ($paiements as $p): ?>
                <tr>
                    <td><?= escape($p['numero_facture']) ?></td>
                    <td><?= formatMoney($p['montant']) ?></td>
                    <td><?= escape($p['mode']) ?></td>
                    <td><?= formatDate($p['date_paiement']) ?></td>
                    <td><?= escape($p['reference']) ?></td>
                    <td><?= $p['encaisse_par_id'] ? escape($p['first_name'] . ' ' . $p['last_name']) : '-' ?></td>
                    <td>
                        <a href="modifier.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

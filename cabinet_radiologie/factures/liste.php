<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$stmt = $pdo->query("
    SELECT f.*, CONCAT(u.last_name, ' ', u.first_name) as patient_nom
    FROM factures f
    JOIN patients p ON f.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    ORDER BY f.date_emission DESC
");
$factures = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Factures</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Factures</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouvelle facture</a>
        <table class="table table-striped">
            <thead><tr><th>N°</th><th>Patient</th><th>Date</th><th>Total TTC</th><th>Montant assurance</th><th>Montant patient</th><th>Réglée</th><th>Actions</th> </thead>
            <tbody>
                <?php foreach ($factures as $f): ?>
                <tr>
                    <td><?= escape($f['numero_facture']) ?> \n
                    <td><?= escape($f['patient_nom']) ?> \n
                    <td><?= formatDate($f['date_emission']) ?> \n
                    <td><?= formatMoney($f['total_ttc']) ?> \n
                    <td><?= formatMoney($f['montant_assurance']) ?> \n
                    <td><?= formatMoney($f['montant_patient']) ?> \n
                    <td><?= $f['reglee'] ? 'Oui' : 'Non' ?> \n
                    <td>
                        <a href="fiche.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-info">Voir</a>
                        <a href="modifier.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                        <a href="../paiements/ajouter.php?facture_id=<?= $f['id'] ?>" class="btn btn-sm btn-success">Paiement</a>
                     \n
                 \n
                <?php endforeach; ?>
            </tbody>
         \n
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

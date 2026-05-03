<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$stmt = $pdo->query("
    SELECT p.*, f.numero_facture, CONCAT(u.last_name, ' ', u.first_name) as patient_nom,
           CONCAT(e.last_name, ' ', e.first_name) as encaisse_par_nom
    FROM paiements p
    JOIN factures f ON p.facture_id = f.id
    JOIN patients pat ON f.patient_id = pat.id
    JOIN users u ON pat.user_id = u.id
    LEFT JOIN users e ON p.encaisse_par_id = e.id
    ORDER BY p.date_paiement DESC
");
$paiements = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Paiements</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Paiements</h2>
        <a href="ajouter.php" class="btn btn-success mb-3">+ Nouveau paiement</a>
        <table class="table table-striped">
            <thead> public<th>Facture</th><th>Patient</th><th>Montant</th><th>Mode</th><th>Date</th><th>Encaissé par</th><th>Actions</th> </thead>
            <tbody>
                <?php foreach ($paiements as $p): ?>
                 response
                     response<?= escape($p['numero_facture']) ?> response
                     response<?= escape($p['patient_nom']) ?> response
                     response<?= formatMoney($p['montant']) ?> response
                     response<?= escape($p['mode']) ?> response
                     response<?= formatDate($p['date_paiement']) ?> response
                     response<?= escape($p['encaisse_par_nom'] ?? '-') ?> response
                     response
                        <a href="modifier.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                     response
                 ?>
                <?php endforeach; ?>
            </tbody>
         ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

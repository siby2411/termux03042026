<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM equipements WHERE id = ?");
$stmt->execute([$id]);
$e = $stmt->fetch();

if (!$e) { die("Équipement introuvable."); }

$statuts = ['OPERATIONNEL' => 'Opérationnel', 'MAINTENANCE' => 'En maintenance', 'PANNE' => 'En panne', 'HS' => 'Hors service'];
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Fiche équipement</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Fiche équipement : <?= escape($e['nom']) ?></h2>
        <table class="table table-bordered">
            <tr><th>Nom</th><td><?= escape($e['nom']) ?></td></tr>
            <tr><th>Type</th><td><?= escape($e['type']) ?></td></tr>
            <tr><th>Marque</th><td><?= escape($e['marque']) ?></td></tr>
            <tr><th>Modèle</th><td><?= escape($e['modele']) ?></td></tr>
            <tr><th>Numéro série</th><td><?= escape($e['numero_serie']) ?></td></tr>
            <tr><th>Date acquisition</th><td><?= formatDate($e['date_acquisition']) ?></td></tr>
            <tr><th>Dernière maintenance</th><td><?= $e['date_derniere_maintenance'] ? formatDate($e['date_derniere_maintenance']) : '-' ?></td></tr>
            <tr><th>Prochaine maintenance</th><td><?= $e['prochaine_maintenance'] ? formatDate($e['prochaine_maintenance']) : '-' ?></td></tr>
            <tr><th>Statut</th><td><?= $statuts[$e['statut']] ?? $e['statut'] ?></td></tr>
            <tr><th>Notes</th><td><?= nl2br(escape($e['notes'])) ?></td></tr>
        </table>
        <a href="liste.php" class="btn btn-secondary">Retour</a>
        <a href="modifier.php?id=<?= $e['id'] ?>" class="btn btn-warning">Modifier</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

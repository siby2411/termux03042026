<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }

$pdo = getPDO();
$stmt = $pdo->prepare("
    SELECT r.*, 
           CONCAT(u.last_name, ' ', u.first_name) as patient_nom,
           p.code_patient,
           e.nom as examen_nom,
           CONCAT(ru.last_name, ' ', ru.first_name) as radiologue_nom,
           CONCAT(mu.last_name, ' ', mu.first_name) as manipulateur_nom,
           eq.nom as equipement_nom
    FROM rendezvous r
    JOIN patients p ON r.patient_id = p.id
    JOIN users u ON p.user_id = u.id
    JOIN examens e ON r.examen_id = e.id
    LEFT JOIN radiologues rad ON r.radiologue_id = rad.id
    LEFT JOIN users ru ON rad.user_id = ru.id
    JOIN manipulateurs man ON r.manipulateur_id = man.id
    JOIN users mu ON man.user_id = mu.id
    JOIN equipements eq ON r.equipement_id = eq.id
    WHERE r.id = ?
");
$stmt->execute([$id]);
$rdv = $stmt->fetch();

if (!$rdv) { die("Rendez-vous introuvable."); }
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Fiche rendez-vous</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Fiche rendez-vous</h2>
        <table class="table table-bordered">
            <tr><th>Patient</th><td><?= escape($rdv['patient_nom']) ?> (<?= escape($rdv['code_patient']) ?>)</td></tr>
            <tr><th>Examen</th><td><?= escape($rdv['examen_nom']) ?></td></tr>
            <tr><th>Radiologue</th><td><?= escape($rdv['radiologue_nom'] ?? '-') ?></td></tr>
            <tr><th>Manipulateur</th><td><?= escape($rdv['manipulateur_nom']) ?></td></tr>
            <tr><th>Équipement</th><td><?= escape($rdv['equipement_nom']) ?></td></tr>
            <tr><th>Date</th><td><?= formatDate($rdv['date']) ?></td></tr>
            <tr><th>Heure</th><td><?= substr($rdv['heure_debut'], 0, 5) . ' - ' . substr($rdv['heure_fin'], 0, 5) ?></td></tr>
            <tr><th>Statut</th><td><?= escape($rdv['statut']) ?></td></tr>
            <tr><th>Motif</th><td><?= escape($rdv['motif']) ?></td></tr>
            <tr><th>Notes</th><td><?= nl2br(escape($rdv['notes'])) ?></td></tr>
            <tr><th>Besoin spécifique</th><td><?= nl2br(escape($rdv['besoin_specifique'])) ?></td></tr>
        </table>
        <a href="liste.php" class="btn btn-secondary">Retour</a>
        <a href="modifier.php?id=<?= $rdv['id'] ?>" class="btn btn-warning">Modifier</a>
        <a href="../comptes_rendus/ajouter.php?rendezvous_id=<?= $rdv['id'] ?>" class="btn btn-primary">Créer compte rendu</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

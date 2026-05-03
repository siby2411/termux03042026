<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM examens WHERE id = ?");
$stmt->execute([$id]);
$e = $stmt->fetch();

if (!$e) { die("Examen introuvable."); }

$categories = [
    'IRM_CEREBRALE' => 'IRM Cérébrale', 'IRM_RACHIS' => 'IRM Rachis', 'IRM_ARTICULAIRE' => 'IRM Articulaire',
    'SCANNER_THORAX' => 'Scanner Thorax', 'SCANNER_ABDOMEN' => 'Scanner Abdomen', 'SCANNER_CRANE' => 'Scanner Crâne',
    'RADIO_THORAX' => 'Radiographie Thorax', 'RADIO_OS' => 'Radiographie Os',
    'MAMMOGRAPHIE' => 'Mammographie', 'ECHO_ABDOMEN' => 'Échographie Abdomen', 'ECHO_PELVIS' => 'Échographie Pelvis',
    'DENSITOMETRIE' => 'Densitométrie'
];
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Fiche examen</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Fiche examen : <?= escape($e['nom']) ?></h2>
        <table class="table table-bordered">
            <tr><th>Nom</th><td><?= escape($e['nom']) ?></td></tr>
            <tr><th>Catégorie</th><td><?= escape($categories[$e['categorie']] ?? $e['categorie']) ?></td></tr>
            <tr><th>Description</th><td><?= nl2br(escape($e['description'])) ?></td></tr>
            <tr><th>Durée estimée</th><td><?= $e['duree_estimee'] ?> minutes</td></tr>
            <tr><th>Tarif</th><td><?= formatMoney($e['tarif']) ?></td></tr>
            <tr><th>Préparation</th><td><?= nl2br(escape($e['preparation'])) ?></td></tr>
            <tr><th>Contre-indications</th><td><?= nl2br(escape($e['contre_indications'])) ?></td></tr>
            <tr><th>Actif</th><td><?= $e['actif'] ? 'Oui' : 'Non' ?></td></tr>
        </table>
        <a href="liste.php" class="btn btn-secondary">Retour</a>
        <a href="modifier.php?id=<?= $e['id'] ?>" class="btn btn-warning">Modifier</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) {
    header('Location: liste.php');
    exit;
}

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM medecins_prescripteurs WHERE id = ?");
$stmt->execute([$id]);
$medecin = $stmt->fetch();

if (!$medecin) {
    die("Médecin introuvable.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche médecin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Fiche médecin : Dr. <?= escape($medecin['prenom'] . ' ' . $medecin['nom']) ?></h2>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th>Nom</th><td><?= escape($medecin['nom']) ?></td></tr>
                    <tr><th>Prénom</th><td><?= escape($medecin['prenom']) ?></td></tr>
                    <tr><th>Spécialité</th><td><?= escape($medecin['specialite']) ?></td></tr>
                    <tr><th>Numéro d'ordre</th><td><?= escape($medecin['numero_ordre']) ?></td></tr>
                    <tr><th>Téléphone</th><td><?= escape($medecin['telephone']) ?></td></tr>
                    <tr><th>Email</th><td><?= escape($medecin['email']) ?></td></tr>
                    <tr><th>Hôpital</th><td><?= escape($medecin['hopital']) ?></td></tr>
                    <tr><th>Adresse</th><td><?= nl2br(escape($medecin['adresse'])) ?></td></tr>
                    <tr><th>Actif</th><td><?= $medecin['actif'] ? 'Oui' : 'Non' ?></td></tr>
                </table>
            </div>
        </div>
        <a href="liste.php" class="btn btn-secondary">Retour</a>
        <a href="modifier.php?id=<?= $id ?>" class="btn btn-warning">Modifier</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

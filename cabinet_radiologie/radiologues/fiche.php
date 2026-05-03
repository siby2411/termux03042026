<?php
session_start();
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
$stmt = $pdo->prepare("SELECT r.*, u.first_name, u.last_name, u.email, u.phone, u.adresse FROM radiologues r JOIN users u ON r.user_id = u.id WHERE r.id = ?");
$stmt->execute([$id]);
$r = $stmt->fetch();

if (!$r) {
    die("Radiologue introuvable.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche radiologue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Fiche radiologue : Dr. <?= escape($r['last_name'] . ' ' . $r['first_name']) ?></h2>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th>Nom complet</th><td><?= escape($r['last_name'] . ' ' . $r['first_name']) ?></td></tr>
                    <tr><th>Spécialité</th><td><?= escape($r['specialite']) ?></td></tr>
                    <tr><th>Numéro d'ordre</th><td><?= escape($r['numero_ordre']) ?></td></tr>
                    <tr><th>Email</th><td><?= escape($r['email']) ?></td></tr>
                    <tr><th>Téléphone</th><td><?= escape($r['phone']) ?></td></tr>
                    <tr><th>Adresse</th><td><?= nl2br(escape($r['adresse'])) ?></td></tr>
                    <tr><th>Actif</th><td><?= $r['actif'] ? 'Oui' : 'Non' ?></td></tr>
                </table>
            </div>
        </div>
        <a href="liste.php" class="btn btn-secondary">Retour</a>
        <a href="modifier.php?id=<?= $r['id'] ?>" class="btn btn-warning">Modifier</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

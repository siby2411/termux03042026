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
$stmt = $pdo->prepare("SELECT m.*, u.first_name, u.last_name, u.email, u.phone, u.adresse FROM manipulateurs m JOIN users u ON m.user_id = u.id WHERE m.id = ?");
$stmt->execute([$id]);
$m = $stmt->fetch();

if (!$m) {
    die("Manipulateur introuvable.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Fiche manipulateur</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Fiche manipulateur : <?= escape($m['last_name'] . ' ' . $m['first_name']) ?></h2>
        <div class="row">
            <div class="col-md-6">
                <table class="table table-bordered">
                    <tr><th>Nom complet</th><td><?= escape($m['last_name'] . ' ' . $m['first_name']) ?></td></tr>
                    <tr><th>Qualification</th><td><?= escape($m['qualification']) ?></td></tr>
                    <tr><th>Numéro de licence</th><td><?= escape($m['numero_licence']) ?></td></tr>
                    <tr><th>Email</th><td><?= escape($m['email']) ?></td></tr>
                    <tr><th>Téléphone</th><td><?= escape($m['phone']) ?></td></tr>
                    <tr><th>Adresse</th><td><?= nl2br(escape($m['adresse'])) ?></td></tr>
                    <tr><th>Actif</th><td><?= $m['actif'] ? 'Oui' : 'Non' ?></td></tr>
                </table>
            </div>
        </div>
        <a href="liste.php" class="btn btn-secondary">Retour</a>
        <a href="modifier.php?id=<?= $m['id'] ?>" class="btn btn-warning">Modifier</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

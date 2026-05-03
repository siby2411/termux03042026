<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = trim($_POST['numero'] ?? '');
    $type = $_POST['type'] ?? 'simple';
    $prix_nuit = (float)($_POST['prix_nuit'] ?? 0);
    $capacite = (int)($_POST['capacite'] ?? 1);
    $description = trim($_POST['description'] ?? '');
    $equipements = trim($_POST['equipements'] ?? '');
    $statut = $_POST['statut'] ?? 'disponible';

    if (!$numero || !$prix_nuit) { $error = "Numéro et prix obligatoires."; }
    else {
        try {
            $stmt = $pdo->prepare("INSERT INTO chambres (numero, type, prix_nuit, capacite, description, equipements, statut) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$numero, $type, $prix_nuit, $capacite, $description, $equipements, $statut]);
            $success = "Chambre ajoutée avec succès.";
        } catch (PDOException $e) { $error = "Erreur: " . $e->getMessage(); }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Ajouter chambre</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter une chambre</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post"><div class="row"><div class="col-md-4 mb-3"><label>Numéro *</label><input type="text" name="numero" class="form-control" required></div>
        <div class="col-md-4 mb-3"><label>Type</label><select name="type" class="form-control"><option value="simple">Simple</option><option value="double">Double</option><option value="suite">Suite</option><option value="presidentielle">Présidentielle</option></select></div>
        <div class="col-md-4 mb-3"><label>Prix par nuit (FCFA) *</label><input type="number" name="prix_nuit" class="form-control" step="1000" required></div>
        <div class="col-md-4 mb-3"><label>Capacité (personnes)</label><input type="number" name="capacite" class="form-control" value="1"></div>
        <div class="col-md-4 mb-3"><label>Statut</label><select name="statut" class="form-control"><option value="disponible">Disponible</option><option value="occupe">Occupé</option><option value="maintenance">Maintenance</option><option value="reserve">Réservé</option></select></div>
        <div class="col-12 mb-3"><label>Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
        <div class="col-12 mb-3"><label>Équipements</label><textarea name="equipements" class="form-control" rows="2"></textarea></div></div>
        <button type="submit" class="btn btn-primary">Enregistrer</button><a href="liste.php" class="btn btn-secondary">Annuler</a></form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

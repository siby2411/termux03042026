<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();
$pdo = getPDO();
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    if (!$nom) { $error = "Nom obligatoire."; }
    else {
        try {
            $pdo->prepare("INSERT INTO categories (nom) VALUES (?)")->execute([$nom]);
            $success = "Ajouté avec succès.";
        } catch (PDOException $e) { $error = "Erreur: " . $e->getMessage(); }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Ajouter</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post"><div class="mb-3"><label>Nom</label><input type="text" name="nom" class="form-control" required></div>
        <button type="submit" class="btn btn-primary">Enregistrer</button><a href="liste.php" class="btn btn-secondary">Annuler</a></form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

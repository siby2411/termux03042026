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
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');

    if (!$nom || !$email) { $error = "Nom et email obligatoires."; }
    else {
        try {
            $stmt = $pdo->prepare("INSERT INTO clients (nom, prenom, email, telephone, ville, adresse) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $telephone, $ville, $adresse]);
            $success = "Client ajouté avec succès.";
        } catch (PDOException $e) { $error = "Erreur: " . $e->getMessage(); }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Ajouter client</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter un client</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post"><div class="row"><div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="nom" class="form-control" required></div>
        <div class="col-md-6 mb-3"><label>Prénom</label><input type="text" name="prenom" class="form-control"></div>
        <div class="col-md-6 mb-3"><label>Email *</label><input type="email" name="email" class="form-control" required></div>
        <div class="col-md-6 mb-3"><label>Téléphone</label><input type="text" name="telephone" class="form-control"></div>
        <div class="col-md-6 mb-3"><label>Ville</label><input type="text" name="ville" class="form-control"></div>
        <div class="col-12 mb-3"><label>Adresse</label><textarea name="adresse" class="form-control" rows="2"></textarea></div></div>
        <button type="submit" class="btn btn-primary">Enregistrer</button><a href="liste.php" class="btn btn-secondary">Annuler</a></form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $specialite = trim($_POST['specialite'] ?? '');
    $numero_ordre = trim($_POST['numero_ordre'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $hopital = trim($_POST['hopital'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');
    $actif = isset($_POST['actif']) ? 1 : 0;

    if (!$nom || !$prenom || !$specialite || !$numero_ordre || !$telephone || !$hopital) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO medecins_prescripteurs (nom, prenom, specialite, numero_ordre, telephone, email, hopital, adresse, actif) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $specialite, $numero_ordre, $telephone, $email, $hopital, $adresse, $actif]);
            $success = "Médecin ajouté avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un médecin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter un médecin</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="nom" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Prénom *</label><input type="text" name="prenom" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Spécialité *</label><input type="text" name="specialite" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Numéro d'ordre *</label><input type="text" name="numero_ordre" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Téléphone *</label><input type="text" name="telephone" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Email</label><input type="email" name="email" class="form-control"></div>
                <div class="col-md-12 mb-3"><label>Hôpital *</label><input type="text" name="hopital" class="form-control" required></div>
                <div class="col-md-12 mb-3"><label>Adresse</label><textarea name="adresse" class="form-control" rows="2"></textarea></div>
                <div class="col-md-12 mb-3"><div class="form-check"><input type="checkbox" name="actif" class="form-check-input" checked> <label class="form-check-label">Actif</label></div></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

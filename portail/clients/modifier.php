<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM clients WHERE id = ?");
$stmt->execute([$id]);
$client = $stmt->fetch();
if (!$client) { die("Client introuvable."); }

$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type_client'] ?? 'particulier';
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $ville = trim($_POST['ville'] ?? '');
    $adresse = trim($_POST['adresse'] ?? '');

    if (!$nom || !$email) {
        $error = "Nom et email obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE clients SET type_client=?, nom=?, prenom=?, email=?, telephone=?, ville=?, adresse=? WHERE id=?");
            $stmt->execute([$type, $nom, $prenom, $email, $telephone, $ville, $adresse, $id]);
            $success = "Client modifié.";
        } catch (PDOException $e) { $error = "Erreur: " . $e->getMessage(); }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Modifier client</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Modifier client</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Type</label><select name="type_client" class="form-control"><option value="particulier" <?= $client['type_client']=='particulier' ? 'selected' : '' ?>>Particulier</option><option value="entreprise" <?= $client['type_client']=='entreprise' ? 'selected' : '' ?>>Entreprise</option></select></div>
                <div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="nom" value="<?= escape($client['nom']) ?>" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Prénom</label><input type="text" name="prenom" value="<?= escape($client['prenom']) ?>" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Email *</label><input type="email" name="email" value="<?= escape($client['email']) ?>" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Téléphone</label><input type="text" name="telephone" value="<?= escape($client['telephone']) ?>" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Ville</label><input type="text" name="ville" value="<?= escape($client['ville']) ?>" class="form-control"></div>
                <div class="col-12 mb-3"><label>Adresse</label><textarea name="adresse" class="form-control" rows="2"><?= escape($client['adresse']) ?></textarea></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

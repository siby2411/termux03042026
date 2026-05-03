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
    $telephone = trim($_POST['telephone'] ?? '');
    $poste = trim($_POST['poste'] ?? '');
    $salaire_base = (float)($_POST['salaire_base'] ?? 0);
    $date_embauche = $_POST['date_embauche'] ?? date('Y-m-d');

    if (!$nom || !$salaire_base) {
        $error = "Nom et salaire base obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO personnel (nom, prenom, telephone, poste, salaire_base, date_embauche) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $telephone, $poste, $salaire_base, $date_embauche]);
            $success = "Employé ajouté avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Ajouter employé</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter un employé</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="nom" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Prénom</label><input type="text" name="prenom" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Téléphone</label><input type="text" name="telephone" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Poste</label><input type="text" name="poste" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Salaire base (FCFA) *</label><input type="number" name="salaire_base" class="form-control" step="1000" required></div>
                <div class="col-md-6 mb-3"><label>Date d'embauche</label><input type="date" name="date_embauche" class="form-control" value="<?= date('Y-m-d') ?>"></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

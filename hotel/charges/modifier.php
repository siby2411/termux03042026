<?php
session_start();
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
$stmt = $pdo->prepare("SELECT * FROM charges WHERE id = ?");
$stmt->execute([$id]);
$charge = $stmt->fetch();

if (!$charge) {
    die("Charge introuvable.");
}

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $libelle = trim($_POST['libelle'] ?? '');
    $montant = (float)($_POST['montant'] ?? 0);
    $date_charge = $_POST['date_charge'] ?? '';
    $categorie = trim($_POST['categorie'] ?? '');
    $notes = trim($_POST['notes'] ?? '');

    if (!$libelle || !$montant) {
        $error = "Libellé et montant obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE charges SET libelle=?, montant=?, date_charge=?, categorie=?, notes=? WHERE id=?");
            $stmt->execute([$libelle, $montant, $date_charge, $categorie, $notes, $id]);
            $success = "Charge modifiée avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Modifier charge</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Modifier la charge</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Libellé *</label><input type="text" name="libelle" class="form-control" value="<?= escape($charge['libelle']) ?>" required></div>
                <div class="col-md-6 mb-3"><label>Montant (FCFA) *</label><input type="number" name="montant" class="form-control" step="1000" value="<?= $charge['montant'] ?>" required></div>
                <div class="col-md-6 mb-3"><label>Date</label><input type="date" name="date_charge" class="form-control" value="<?= $charge['date_charge'] ?>"></div>
                <div class="col-md-6 mb-3"><label>Catégorie</label><input type="text" name="categorie" class="form-control" value="<?= escape($charge['categorie']) ?>"></div>
                <div class="col-12 mb-3"><label>Notes</label><textarea name="notes" class="form-control" rows="2"><?= escape($charge['notes']) ?></textarea></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

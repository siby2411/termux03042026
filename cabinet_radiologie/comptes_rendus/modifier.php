<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$id = $_GET['id'] ?? 0;
if (!$id) { header('Location: liste.php'); exit; }

$pdo = getPDO();
$stmt = $pdo->prepare("SELECT * FROM comptes_rendus WHERE id = ?");
$stmt->execute([$id]);
$cr = $stmt->fetch();

if (!$cr) { die("Compte rendu introuvable."); }

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $indication = trim($_POST['indication'] ?? '');
    $technique = trim($_POST['technique'] ?? '');
    $comparaison = trim($_POST['comparaison'] ?? '');
    $resultats = trim($_POST['resultats'] ?? '');
    $conclusion = trim($_POST['conclusion'] ?? '');
    $recommandations = trim($_POST['recommandations'] ?? '');

    if (!$indication || !$technique || !$resultats || !$conclusion) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE comptes_rendus SET indication=?, technique=?, comparaison=?, resultats=?, conclusion=?, recommandations=? WHERE id=?");
            $stmt->execute([$indication, $technique, $comparaison, $resultats, $conclusion, $recommandations, $id]);
            $success = "Compte rendu modifié.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head><meta charset="UTF-8"><title>Modifier compte rendu</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Modifier compte rendu</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="mb-3"><label>Indication *</label><textarea name="indication" class="form-control" rows="2" required><?= escape($cr['indication']) ?></textarea></div>
            <div class="mb-3"><label>Technique *</label><textarea name="technique" class="form-control" rows="2" required><?= escape($cr['technique']) ?></textarea></div>
            <div class="mb-3"><label>Comparaison</label><textarea name="comparaison" class="form-control" rows="2"><?= escape($cr['comparaison']) ?></textarea></div>
            <div class="mb-3"><label>Résultats *</label><textarea name="resultats" class="form-control" rows="4" required><?= escape($cr['resultats']) ?></textarea></div>
            <div class="mb-3"><label>Conclusion *</label><textarea name="conclusion" class="form-control" rows="3" required><?= escape($cr['conclusion']) ?></textarea></div>
            <div class="mb-3"><label>Recommandations</label><textarea name="recommandations" class="form-control" rows="2"><?= escape($cr['recommandations']) ?></textarea></div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

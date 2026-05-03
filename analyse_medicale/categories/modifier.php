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
$stmt = $pdo->prepare("SELECT * FROM categories_analyse WHERE id = ?");
$stmt->execute([$id]);
$categorie = $stmt->fetch();

if (!$categorie) {
    die("Catégorie introuvable.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    if (!$nom) {
        $error = "Le nom est obligatoire.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE categories_analyse SET nom = ?, description = ? WHERE id = ?");
            $stmt->execute([$nom, $desc, $id]);
            $success = "Catégorie modifiée.";
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
    <title>Modifier catégorie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Modifier catégorie</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="mb-3"><label>Nom *</label><input type="text" name="nom" class="form-control" value="<?= escape($categorie['nom']) ?>" required></div>
            <div class="mb-3"><label>Description</label><textarea name="description" class="form-control" rows="3"><?= escape($categorie['description']) ?></textarea></div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

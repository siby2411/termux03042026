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
$stmt = $pdo->prepare("SELECT * FROM parametres_analyse WHERE id = ?");
$stmt->execute([$id]);
$param = $stmt->fetch();

if (!$param) {
    die("Paramètre introuvable.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $unite = trim($_POST['unite'] ?? '');
    $valeur_min = $_POST['valeur_min'] === '' ? null : $_POST['valeur_min'];
    $valeur_max = $_POST['valeur_max'] === '' ? null : $_POST['valeur_max'];
    $valeur_normale = trim($_POST['valeur_normale'] ?? '');
    $ordre = (int)($_POST['ordre'] ?? 0);

    if (!$nom) {
        $error = "Le nom est obligatoire.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE parametres_analyse SET nom = ?, unite = ?, valeur_min = ?, valeur_max = ?, valeur_normale = ?, ordre = ? WHERE id = ?");
            $stmt->execute([$nom, $unite, $valeur_min, $valeur_max, $valeur_normale, $ordre, $id]);
            $success = "Paramètre modifié.";
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
    <title>Modifier paramètre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Modifier paramètre</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="nom" class="form-control" value="<?= escape($param['nom']) ?>" required></div>
                <div class="col-md-6 mb-3"><label>Unité</label><input type="text" name="unite" class="form-control" value="<?= escape($param['unite']) ?>"></div>
                <div class="col-md-4 mb-3"><label>Valeur min</label><input type="number" step="any" name="valeur_min" class="form-control" value="<?= $param['valeur_min'] ?>"></div>
                <div class="col-md-4 mb-3"><label>Valeur max</label><input type="number" step="any" name="valeur_max" class="form-control" value="<?= $param['valeur_max'] ?>"></div>
                <div class="col-md-4 mb-3"><label>Valeur normale</label><input type="text" name="valeur_normale" class="form-control" value="<?= escape($param['valeur_normale']) ?>"></div>
                <div class="col-md-12 mb-3"><label>Ordre</label><input type="number" name="ordre" class="form-control" value="<?= $param['ordre'] ?>"></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php?analyse_id=<?= $param['analyse_id'] ?>" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

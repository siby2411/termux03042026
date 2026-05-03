<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$analyse_id = $_GET['analyse_id'] ?? 0;
if (!$analyse_id) {
    header('Location: liste.php');
    exit;
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
            $stmt = $pdo->prepare("INSERT INTO parametres_analyse (analyse_id, nom, unite, valeur_min, valeur_max, valeur_normale, ordre) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$analyse_id, $nom, $unite, $valeur_min, $valeur_max, $valeur_normale, $ordre]);
            $success = "Paramètre ajouté.";
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
    <title>Ajouter paramètre</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter un paramètre</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Nom *</label><input type="text" name="nom" class="form-control" required></div>
                <div class="col-md-6 mb-3"><label>Unité</label><input type="text" name="unite" class="form-control"></div>
                <div class="col-md-4 mb-3"><label>Valeur min</label><input type="number" step="any" name="valeur_min" class="form-control"></div>
                <div class="col-md-4 mb-3"><label>Valeur max</label><input type="number" step="any" name="valeur_max" class="form-control"></div>
                <div class="col-md-4 mb-3"><label>Valeur normale</label><input type="text" name="valeur_normale" class="form-control"></div>
                <div class="col-md-12 mb-3"><label>Ordre</label><input type="number" name="ordre" class="form-control" value="0"></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php?analyse_id=<?= $analyse_id ?>" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$analyse_id = $_GET['analyse_id'] ?? 0;

$analyses = $pdo->query("SELECT id, nom FROM analyses ORDER BY nom")->fetchAll();

if ($analyse_id) {
    $stmt = $pdo->prepare("SELECT p.*, a.nom as analyse_nom FROM parametres_analyse p JOIN analyses a ON p.analyse_id = a.id WHERE p.analyse_id = ? ORDER BY p.ordre");
    $stmt->execute([$analyse_id]);
    $parametres = $stmt->fetchAll();
} else {
    $parametres = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paramètres d'analyse</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Paramètres d'analyse</h2>
        <form method="get" class="row g-3 mb-4">
            <div class="col-auto"><label>Analyse :</label></div>
            <div class="col-auto"><select name="analyse_id" class="form-select" onchange="this.form.submit()">
                <option value="0">-- Choisir une analyse --</option>
                <?php foreach ($analyses as $a): ?>
                <option value="<?= $a['id'] ?>" <?= $analyse_id == $a['id'] ? 'selected' : '' ?>><?= escape($a['nom']) ?></option>
                <?php endforeach; ?>
            </select></div>
            <div class="col-auto"><a href="ajouter.php?analyse_id=<?= $analyse_id ?>" class="btn btn-success">+ Nouveau paramètre</a></div>
        </form>

        <?php if ($analyse_id && $parametres): ?>
        <table class="table table-striped">
            <thead><tr><th>Nom</th><th>Unité</th><th>Valeur min</th><th>Valeur max</th><th>Valeur normale</th><th>Ordre</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($parametres as $p): ?>
                <tr>
                    <td><?= escape($p['nom']) ?></td>
                    <td><?= escape($p['unite']) ?></td>
                    <td><?= $p['valeur_min'] ?></td>
                    <td><?= $p['valeur_max'] ?></td>
                    <td><?= escape($p['valeur_normale']) ?></td>
                    <td><?= $p['ordre'] ?></td>
                    <td>
                        <a href="modifier.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Modifier</a>
                        <a href="supprimer.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php elseif ($analyse_id): ?>
        <p>Aucun paramètre pour cette analyse.</p>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

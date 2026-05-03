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
$stmt = $pdo->prepare("SELECT ar.*, a.nom as analyse_nom FROM analyses_realisees ar JOIN analyses a ON ar.analyse_id = a.id WHERE ar.id = ?");
$stmt->execute([$id]);
$analyse = $stmt->fetch();

if (!$analyse) {
    die("Analyse non trouvée.");
}

// Récupérer les paramètres de cette analyse
$stmt = $pdo->prepare("SELECT * FROM parametres_analyse WHERE analyse_id = ? ORDER BY ordre");
$stmt->execute([$analyse['analyse_id']]);
$parametres = $stmt->fetchAll();

// Récupérer les résultats existants
$stmt = $pdo->prepare("SELECT * FROM resultats_analyse WHERE analyse_realisee_id = ?");
$stmt->execute([$id]);
$resultatsExistants = [];
while ($row = $stmt->fetch()) {
    $resultatsExistants[$row['parametre_id']] = $row;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        // Supprimer les anciens résultats
        $stmt = $pdo->prepare("DELETE FROM resultats_analyse WHERE analyse_realisee_id = ?");
        $stmt->execute([$id]);
        // Insérer les nouveaux
        $insert = $pdo->prepare("INSERT INTO resultats_analyse (analyse_realisee_id, parametre_id, valeur, interpretation, commentaire) VALUES (?, ?, ?, ?, ?)");
        foreach ($parametres as $param) {
            $valeur = $_POST["valeur_{$param['id']}"] ?? '';
            $interpretation = $_POST["interpretation_{$param['id']}"] ?? '';
            $commentaire = $_POST["commentaire_{$param['id']}"] ?? '';
            if ($valeur !== '') {
                $insert->execute([$id, $param['id'], $valeur, $interpretation, $commentaire]);
            }
        }
        // Mettre à jour le statut de l'analyse réalisée
        $pdo->prepare("UPDATE analyses_realisees SET statut = 'valide', date_fin = NOW() WHERE id = ?")->execute([$id]);
        $pdo->commit();
        $success = "Résultats enregistrés avec succès.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Erreur : " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Saisie des résultats</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Saisie des résultats pour <?= escape($analyse['analyse_nom']) ?></h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <table class="table table-bordered">
                <thead><tr><th>Paramètre</th><th>Valeur</th><th>Interprétation</th><th>Commentaire</th></tr></thead>
                <tbody>
                    <?php foreach ($parametres as $param): ?>
                    <?php $existing = $resultatsExistants[$param['id']] ?? null; ?>
                    <tr>
                        <td><?= escape($param['nom']) ?> (<?= escape($param['unite']) ?>)</td>
                        <td><input type="text" name="valeur_<?= $param['id'] ?>" class="form-control" value="<?= $existing ? escape($existing['valeur']) : '' ?>"></td>
                        <td>
                            <select name="interpretation_<?= $param['id'] ?>" class="form-select">
                                <option value="">--</option>
                                <option value="normal" <?= ($existing && $existing['interpretation']=='normal') ? 'selected' : '' ?>>Normal</option>
                                <option value="anormal" <?= ($existing && $existing['interpretation']=='anormal') ? 'selected' : '' ?>>Anormal</option>
                                <option value="critique" <?= ($existing && $existing['interpretation']=='critique') ? 'selected' : '' ?>>Critique</option>
                            </select>
                        </td>
                        <td><input type="text" name="commentaire_<?= $param['id'] ?>" class="form-control" value="<?= $existing ? escape($existing['commentaire']) : '' ?>"></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" class="btn btn-primary">Enregistrer les résultats</button>
            <a href="liste.php" class="btn btn-secondary">Retour</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

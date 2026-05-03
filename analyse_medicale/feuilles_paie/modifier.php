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
$stmt = $pdo->prepare("SELECT * FROM feuilles_paie WHERE id = ?");
$stmt->execute([$id]);
$feuille = $stmt->fetch();

if (!$feuille) {
    die("Feuille de paie introuvable.");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mois = (int)($_POST['mois'] ?? 0);
    $annee = (int)($_POST['annee'] ?? 0);
    $salaire_base = (float)($_POST['salaire_base'] ?? 0);
    $heures_travaillees = (float)($_POST['heures_travaillees'] ?? 0);
    $heures_sup = (float)($_POST['heures_sup'] ?? 0);
    $prime_presence = (float)($_POST['prime_presence'] ?? 0);
    $prime_performance = (float)($_POST['prime_performance'] ?? 0);
    $avantages = (float)($_POST['avantages'] ?? 0);
    $cotisations = (float)($_POST['cotisations'] ?? 0);
    $statut = $_POST['statut'] ?? 'brouillon';
    $date_paiement = $_POST['date_paiement'] ?? null;
    $reference_paiement = trim($_POST['reference_paiement'] ?? '');

    if (!$mois || !$annee || !$salaire_base) {
        $error = "Veuillez remplir les champs obligatoires.";
    } else {
        $total_brut = $salaire_base + ($heures_sup * 2000) + $prime_presence + $prime_performance + $avantages;
        $total_net = $total_brut - $cotisations;

        try {
            $stmt = $pdo->prepare("UPDATE feuilles_paie SET 
                mois = ?, annee = ?, salaire_base = ?, heures_travaillees = ?, heures_sup = ?,
                prime_presence = ?, prime_performance = ?, avantages = ?, total_brut = ?, cotisations = ?, total_net = ?,
                statut = ?, date_paiement = ?, reference_paiement = ? WHERE id = ?");
            $stmt->execute([$mois, $annee, $salaire_base, $heures_travaillees, $heures_sup,
                $prime_presence, $prime_performance, $avantages, $total_brut, $cotisations, $total_net,
                $statut, $date_paiement, $reference_paiement, $id]);
            $success = "Feuille de paie modifiée.";
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
    <title>Modifier feuille de paie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Modifier feuille de paie</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-3 mb-3"><label>Mois *</label><input type="number" name="mois" class="form-control" value="<?= $feuille['mois'] ?>" min="1" max="12" required></div>
                <div class="col-md-3 mb-3"><label>Année *</label><input type="number" name="annee" class="form-control" value="<?= $feuille['annee'] ?>" required></div>
                <div class="col-md-6 mb-3"><label>Salaire base *</label><input type="number" name="salaire_base" class="form-control" step="1000" value="<?= $feuille['salaire_base'] ?>" required></div>
                <div class="col-md-4 mb-3"><label>Heures travaillées</label><input type="number" name="heures_travaillees" class="form-control" step="0.5" value="<?= $feuille['heures_travaillees'] ?>"></div>
                <div class="col-md-4 mb-3"><label>Heures sup</label><input type="number" name="heures_sup" class="form-control" step="0.5" value="<?= $feuille['heures_sup'] ?>"></div>
                <div class="col-md-4 mb-3"><label>Prime présence</label><input type="number" name="prime_presence" class="form-control" step="1000" value="<?= $feuille['prime_presence'] ?>"></div>
                <div class="col-md-4 mb-3"><label>Prime performance</label><input type="number" name="prime_performance" class="form-control" step="1000" value="<?= $feuille['prime_performance'] ?>"></div>
                <div class="col-md-4 mb-3"><label>Avantages</label><input type="number" name="avantages" class="form-control" step="1000" value="<?= $feuille['avantages'] ?>"></div>
                <div class="col-md-4 mb-3"><label>Cotisations</label><input type="number" name="cotisations" class="form-control" step="1000" value="<?= $feuille['cotisations'] ?>"></div>
                <div class="col-md-6 mb-3"><label>Statut</label><select name="statut" class="form-control">
                    <option value="brouillon" <?= $feuille['statut']=='brouillon' ? 'selected' : '' ?>>Brouillon</option>
                    <option value="valide" <?= $feuille['statut']=='valide' ? 'selected' : '' ?>>Validé</option>
                    <option value="paye" <?= $feuille['statut']=='paye' ? 'selected' : '' ?>>Payé</option>
                </select></div>
                <div class="col-md-6 mb-3"><label>Date paiement</label><input type="date" name="date_paiement" class="form-control" value="<?= $feuille['date_paiement'] ?>"></div>
                <div class="col-md-12 mb-3"><label>Référence paiement</label><input type="text" name="reference_paiement" class="form-control" value="<?= escape($feuille['reference_paiement']) ?>"></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

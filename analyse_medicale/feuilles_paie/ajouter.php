<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $personnel_id = $_POST['personnel_id'] ?? 0;
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

    if (!$personnel_id || !$mois || !$annee || !$salaire_base) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $total_brut = $salaire_base + ($heures_sup * 2000) + $prime_presence + $prime_performance + $avantages;
        $total_net = $total_brut - $cotisations;

        try {
            $stmt = $pdo->prepare("INSERT INTO feuilles_paie 
                (personnel_id, mois, annee, salaire_base, heures_travaillees, heures_sup, 
                 prime_presence, prime_performance, avantages, total_brut, cotisations, total_net, 
                 statut, date_paiement, reference_paiement) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $personnel_id, $mois, $annee, $salaire_base, $heures_travaillees, $heures_sup,
                $prime_presence, $prime_performance, $avantages, $total_brut, $cotisations, $total_net,
                $statut, $date_paiement, $reference_paiement
            ]);
            $success = "Feuille de paie ajoutée.";
        } catch (PDOException $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

$personnel = $pdo->query("SELECT id, first_name, last_name FROM users WHERE role IN ('biologiste','technicien','secretaire') ORDER BY last_name")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter feuille de paie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2>Ajouter une feuille de paie</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post">
            <div class="row">
                <div class="col-md-6 mb-3"><label>Personnel *</label><select name="personnel_id" class="form-control" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($personnel as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= escape($p['last_name'] . ' ' . $p['first_name']) ?></option>
                    <?php endforeach; ?>
                </select></div>
                <div class="col-md-3 mb-3"><label>Mois *</label><select name="mois" class="form-control" required>
                    <?php for ($m=1; $m<=12; $m++): ?>
                    <option value="<?= $m ?>"><?= sprintf('%02d', $m) ?></option>
                    <?php endfor; ?>
                </select></div>
                <div class="col-md-3 mb-3"><label>Année *</label><select name="annee" class="form-control" required>
                    <?php for ($y=date('Y')-2; $y<=date('Y')+1; $y++): ?>
                    <option value="<?= $y ?>" <?= $y==date('Y') ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select></div>
                <div class="col-md-6 mb-3"><label>Salaire base (FCFA) *</label><input type="number" name="salaire_base" class="form-control" step="1000" required></div>
                <div class="col-md-3 mb-3"><label>Heures travaillées</label><input type="number" name="heures_travaillees" class="form-control" step="0.5" value="0"></div>
                <div class="col-md-3 mb-3"><label>Heures sup</label><input type="number" name="heures_sup" class="form-control" step="0.5" value="0"></div>
                <div class="col-md-4 mb-3"><label>Prime présence</label><input type="number" name="prime_presence" class="form-control" step="1000" value="0"></div>
                <div class="col-md-4 mb-3"><label>Prime performance</label><input type="number" name="prime_performance" class="form-control" step="1000" value="0"></div>
                <div class="col-md-4 mb-3"><label>Avantages</label><input type="number" name="avantages" class="form-control" step="1000" value="0"></div>
                <div class="col-md-6 mb-3"><label>Cotisations (FCFA)</label><input type="number" name="cotisations" class="form-control" step="1000" value="0"></div>
                <div class="col-md-6 mb-3"><label>Statut</label><select name="statut" class="form-control">
                    <option value="brouillon">Brouillon</option><option value="valide">Validé</option><option value="paye">Payé</option>
                </select></div>
                <div class="col-md-6 mb-3"><label>Date paiement</label><input type="date" name="date_paiement" class="form-control"></div>
                <div class="col-md-6 mb-3"><label>Référence paiement</label><input type="text" name="reference_paiement" class="form-control"></div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

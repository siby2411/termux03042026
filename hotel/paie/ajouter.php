<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$pdo = getPDO();
$error = $success = '';

$personnel = $pdo->query("SELECT id, nom, prenom, salaire_base FROM personnel ORDER BY nom")->fetchAll();
$mois_actuel = date('m');
$annee_actuel = date('Y');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $personnel_id = $_POST['personnel_id'] ?? 0;
    $mois = (int)($_POST['mois'] ?? 0);
    $annee = (int)($_POST['annee'] ?? 0);
    $salaire_brut = (float)($_POST['salaire_brut'] ?? 0);
    $prime = (float)($_POST['prime'] ?? 0);
    $deduction = (float)($_POST['deduction'] ?? 0);
    $paye = isset($_POST['paye']) ? 1 : 0;

    if (!$personnel_id || !$mois || !$annee || !$salaire_brut) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $salaire_net = $salaire_brut + $prime - $deduction;
        try {
            $stmt = $pdo->prepare("INSERT INTO paie (personnel_id, mois, annee, salaire_brut, prime, deduction, salaire_net, paye) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$personnel_id, $mois, $annee, $salaire_brut, $prime, $deduction, $salaire_net, $paye]);
            $success = "Fiche de paie ajoutée avec succès.";
        } catch (PDOException $e) {
            if ($e->errorInfo[1] == 1062) {
                $error = "Une paie existe déjà pour ce personnel pour cette période.";
            } else {
                $error = "Erreur: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter paie - OMEGA Hôtel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        function calculerNet() {
            const brut = parseFloat(document.getElementById('salaire_brut').value) || 0;
            const prime = parseFloat(document.getElementById('prime').value) || 0;
            const deduction = parseFloat(document.getElementById('deduction').value) || 0;
            const net = brut + prime - deduction;
            document.getElementById('salaire_net_preview').innerHTML = net.toLocaleString() + ' FCFA';
        }
    </script>
</head>
<body>
    <?php require_once '../includes/menu.php'; ?>
    <div class="container mt-4">
        <h2><i class="fas fa-plus me-2"></i>Ajouter une fiche de paie</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post" onchange="calculerNet()" onkeyup="calculerNet()">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Personnel *</label>
                    <select name="personnel_id" class="form-control" required>
                        <option value="">-- Sélectionner --</option>
                        <?php foreach ($personnel as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= escape($p['prenom'] . ' ' . $p['nom']) ?> (Salaire base: <?= formatMoney($p['salaire_base']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Mois *</label>
                    <select name="mois" class="form-control" required>
                        <?php for ($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= $m == $mois_actuel ? 'selected' : '' ?>><?= sprintf('%02d', $m) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Année *</label>
                    <select name="annee" class="form-control" required>
                        <?php for ($y=$annee_actuel-2; $y<=$annee_actuel+1; $y++): ?>
                        <option value="<?= $y ?>" <?= $y == $annee_actuel ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Salaire brut (FCFA) *</label>
                    <input type="number" name="salaire_brut" id="salaire_brut" class="form-control" step="1000" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Prime (FCFA)</label>
                    <input type="number" name="prime" id="prime" class="form-control" step="1000" value="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Déduction (FCFA)</label>
                    <input type="number" name="deduction" id="deduction" class="form-control" step="1000" value="0">
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="paye" class="form-check-input" id="paye">
                        <label class="form-check-label" for="paye">Déjà payé</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="alert alert-info">
                        <strong>Salaire net calculé :</strong>
                        <span id="salaire_net_preview">0 FCFA</span>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
            <a href="liste.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

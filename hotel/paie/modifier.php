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
$stmt = $pdo->prepare("SELECT p.*, per.nom, per.prenom FROM paie p JOIN personnel per ON p.personnel_id = per.id WHERE p.id = ?");
$stmt->execute([$id]);
$paie = $stmt->fetch();

if (!$paie) {
    die("Fiche de paie introuvable.");
}

$personnel = $pdo->query("SELECT id, nom, prenom, salaire_base FROM personnel ORDER BY nom")->fetchAll();
$error = $success = '';

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
            $stmt = $pdo->prepare("UPDATE paie SET personnel_id=?, mois=?, annee=?, salaire_brut=?, prime=?, deduction=?, salaire_net=?, paye=? WHERE id=?");
            $stmt->execute([$personnel_id, $mois, $annee, $salaire_brut, $prime, $deduction, $salaire_net, $paye, $id]);
            $success = "Fiche de paie modifiée avec succès.";
        } catch (PDOException $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier paie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <h2>Modifier fiche de paie</h2>
        <?php if ($error): ?><div class="alert alert-danger"><?= escape($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= escape($success) ?></div><?php endif; ?>
        <form method="post" onchange="calculerNet()" onkeyup="calculerNet()">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Personnel *</label>
                    <select name="personnel_id" class="form-control" required>
                        <?php foreach ($personnel as $p): ?>
                        <option value="<?= $p['id'] ?>" <?= $paie['personnel_id'] == $p['id'] ? 'selected' : '' ?>><?= escape($p['prenom'] . ' ' . $p['nom']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Mois *</label>
                    <select name="mois" class="form-control" required>
                        <?php for ($m=1; $m<=12; $m++): ?>
                        <option value="<?= $m ?>" <?= $paie['mois'] == $m ? 'selected' : '' ?>><?= sprintf('%02d', $m) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>Année *</label>
                    <select name="annee" class="form-control" required>
                        <?php for ($y=date('Y')-2; $y<=date('Y')+1; $y++): ?>
                        <option value="<?= $y ?>" <?= $paie['annee'] == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Salaire brut (FCFA) *</label>
                    <input type="number" name="salaire_brut" id="salaire_brut" class="form-control" value="<?= $paie['salaire_brut'] ?>" step="1000" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Prime (FCFA)</label>
                    <input type="number" name="prime" id="prime" class="form-control" value="<?= $paie['prime'] ?>" step="1000">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Déduction (FCFA)</label>
                    <input type="number" name="deduction" id="deduction" class="form-control" value="<?= $paie['deduction'] ?>" step="1000">
                </div>
                <div class="col-md-6 mb-3">
                    <div class="form-check mt-4">
                        <input type="checkbox" name="paye" class="form-check-input" id="paye" <?= $paie['paye'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="paye">Déjà payé</label>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="alert alert-info">
                        <strong>Salaire net calculé :</strong>
                        <span id="salaire_net_preview"><?= formatMoney($paie['salaire_net']) ?></span>
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

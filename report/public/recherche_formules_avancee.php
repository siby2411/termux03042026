<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Formules financières avancées";
include 'inc_navbar.php';

$formules = [
    'EBE' => ['nom'=>'Excédent Brut d\'Exploitation','formule'=>'Produits - Charges (hors dotations)','champs'=>['produits'=>'Produits (k€)','charges'=>'Charges (k€)'],'defauts'=>['produits'=>1200,'charges'=>800]],
    'CAF' => ['nom'=>'Capacité d\'Autofinancement','formule'=>'Résultat net + Dotations','champs'=>['rn'=>'Résultat net (k€)','dot'=>'Dotations (k€)'],'defauts'=>['rn'=>150,'dot'=>50]],
    'BFR' => ['nom'=>'Besoin en Fonds de Roulement','formule'=>'Stocks + Créances - Dettes','champs'=>['stocks'=>'Stocks (k€)','creances'=>'Créances (k€)','dettes'=>'Dettes (k€)'],'defauts'=>['stocks'=>200,'creances'=>300,'dettes'=>250]],
    'VAN' => [
        'nom'=>'Valeur Actuelle Nette',
        'formule'=>'Σ Flux/(1+t)^n - Investissement',
        'champs'=>['invest'=>'Investissement (k€)','taux'=>'Taux d\'actualisation (%)','flux'=>'Flux annuels (k€) séparés par des virgules'],
        'defauts'=>['invest'=>1000,'taux'=>8,'flux'=>'250,280,300'],
        'aide'=>'Exemple: 3 flux pour 3 années -> 250,280,300'
    ],
    'TRI' => [
        'nom'=>'Taux de Rendement Interne',
        'formule'=>'Taux tel que VAN=0',
        'champs'=>['invest'=>'Investissement (k€)','flux'=>'Flux annuels (k€) séparés par des virgules'],
        'defauts'=>['invest'=>1000,'flux'=>'250,280,300'],
        'aide'=>'Exemple: 250,280,300'
    ],
    'WACC' => ['nom'=>'Coût moyen pondéré du capital','formule'=>'(CP/Total)*Ke + (D/Total)*Kd*(1-IS)','champs'=>['cp'=>'Capitaux propres (k€)','dettes'=>'Dettes (k€)','ke'=>'Coût des CP (%)','kd'=>'Coût de la dette (%)','is'=>'Taux d\'IS (%)'],'defauts'=>['cp'=>800,'dettes'=>600,'ke'=>10,'kd'=>5,'is'=>25]]
];

$choix = $_GET['formule'] ?? 'VAN';
$data = $formules[$choix];
$res = null;
$interpretation = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($choix == 'EBE') $res = $_POST['produits'] - $_POST['charges'];
    if ($choix == 'CAF') $res = $_POST['rn'] + $_POST['dot'];
    if ($choix == 'BFR') $res = $_POST['stocks'] + $_POST['creances'] - $_POST['dettes'];
    if ($choix == 'VAN') {
        $flux = array_map('floatval', explode(',', str_replace(' ', '', $_POST['flux'])));
        if (count($flux) == 1 && $_POST['flux'] != '') {
            $error = "⚠️ Vous n'avez saisi qu'un seul flux. Pour une VAN réaliste, entrez plusieurs flux sur plusieurs années (ex: 250,280,300).";
        } else {
            $van = -$_POST['invest'];
            $t = $_POST['taux'] / 100;
            foreach ($flux as $i => $f) $van += $f / pow(1+$t, $i+1);
            $res = $van;
            $interpretation = $res > 0 ? "✅ Projet acceptable (VAN > 0)" : ($res < 0 ? "❌ Projet non rentable (VAN < 0)" : "⚖️ Équilibre");
        }
    }
    if ($choix == 'TRI') {
        $flux = array_map('floatval', explode(',', str_replace(' ', '', $_POST['flux'])));
        if (count($flux) == 1 && $_POST['flux'] != '') {
            $error = "⚠️ Veuillez saisir plusieurs flux séparés par des virgules (ex: 250,280,300).";
        } else {
            $guess = 0.1; $step = 0.05; $iter = 0; $van = 1;
            while (abs($van) > 0.01 && $iter < 50) {
                $van = -$_POST['invest'];
                foreach ($flux as $i => $f) $van += $f / pow(1+$guess, $i+1);
                if ($van > 0) $guess += $step;
                else $guess -= $step;
                $step /= 2;
                $iter++;
            }
            $res = $guess * 100;
            $interpretation = $res > 0 ? "💰 TRI calculé : " . number_format($res,2) . "%" : "TRI non trouvé";
        }
    }
    if ($choix == 'WACC') {
        $total = $_POST['cp'] + $_POST['dettes'];
        $res = ($_POST['cp']/$total)*($_POST['ke']/100) + ($_POST['dettes']/$total)*($_POST['kd']/100)*(1-$_POST['is']/100);
        $res = $res * 100;
        $interpretation = "Le coût moyen pondéré du capital est de " . number_format($res,2) . "%";
    }
}

$defauts = $data['defauts'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">📂 Formules</div>
                <div class="list-group list-group-flush">
                    <?php foreach ($formules as $k => $v): ?>
                    <a href="?formule=<?= urlencode($k) ?>" class="list-group-item list-group-item-action <?= $k == $choix ? 'active' : '' ?>">
                        <?= htmlspecialchars($v['nom']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4><i class="bi bi-calculator"></i> <?= htmlspecialchars($data['nom']) ?></h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Formule :</strong><br>
                        <code><?= htmlspecialchars($data['formule']) ?></code>
                        <?php if (isset($data['aide'])): ?>
                        <br><small class="text-muted">💡 <?= $data['aide'] ?></small>
                        <?php endif; ?>
                    </div>
                    <?php if ($error): ?>
                    <div class="alert alert-warning"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <?php foreach ($data['champs'] as $key => $label): ?>
                        <div class="mb-2">
                            <label class="form-label"><?= htmlspecialchars($label) ?></label>
                            <input type="text" name="<?= $key ?>" class="form-control" value="<?= htmlspecialchars($_POST[$key] ?? $defauts[$key] ?? '') ?>">
                        </div>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary">Calculer</button>
                    </form>
                    <?php if ($res !== null && !$error): ?>
                    <div class="alert alert-success mt-3">
                        <strong>Résultat :</strong> <?= number_format($res, 2) ?>
                        <?php if ($interpretation): ?>
                        <br><small><?= $interpretation ?></small>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>

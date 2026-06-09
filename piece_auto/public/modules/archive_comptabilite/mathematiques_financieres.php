<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Mathématiques financières (intérêts, annuités, emprunts, etc.)";
include 'inc_navbar.php';

$result = null;
$error = null;
$formule = $_GET['formule'] ?? 'capitalisation';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $calcul = $_POST['calcul'];
    switch ($calcul) {
        case 'capitalisation':
            $C0 = floatval($_POST['capital']);
            $t = floatval($_POST['taux']) / 100;
            $n = intval($_POST['annees']);
            $Cn = $C0 * pow(1 + $t, $n);
            $result = ["Valeur acquise" => number_format($Cn, 2) . " €"];
            break;
        case 'actualisation':
            $Cn = floatval($_POST['capital_futur']);
            $t = floatval($_POST['taux']) / 100;
            $n = intval($_POST['annees']);
            $C0 = $Cn / pow(1 + $t, $n);
            $result = ["Valeur actuelle" => number_format($C0, 2) . " €"];
            break;
        case 'annuite_constante':
            $emprunt = floatval($_POST['emprunt']);
            $t = floatval($_POST['taux']) / 100;
            $n = intval($_POST['annees']);
            $a = $emprunt * $t * pow(1+$t, $n) / (pow(1+$t, $n) - 1);
            $result = ["Annuité constante" => number_format($a, 2) . " €"];
            break;
        case 'amortissement_constant':
            $emprunt = floatval($_POST['emprunt']);
            $t = floatval($_POST['taux']) / 100;
            $n = intval($_POST['annees']);
            $amort = $emprunt / $n;
            $premiere_annuite = $amort + $emprunt * $t;
            $result = [
                "Amortissement annuel" => number_format($amort, 2) . " €",
                "Première annuité" => number_format($premiere_annuite, 2) . " €"
            ];
            break;
        case 'taux_equivalent':
            $t_nom = floatval($_POST['taux_nominal']) / 100;
            $p = intval($_POST['periodes']);
            $t_eq = pow(1 + $t_nom, 1/$p) - 1;
            $result = ["Taux équivalent périodique" => number_format($t_eq * 100, 4) . " %"];
            break;
        case 'taux_proportionnel':
            $t_annuel = floatval($_POST['taux_annuel']) / 100;
            $p = intval($_POST['periodes']);
            $t_prop = $t_annuel / $p;
            $result = ["Taux proportionnel périodique" => number_format($t_prop * 100, 4) . " %"];
            break;
        case 'duree_capitalisation':
            $C0 = floatval($_POST['capital_initial']);
            $Cn = floatval($_POST['capital_final']);
            $t = floatval($_POST['taux']) / 100;
            if ($C0 <= 0 || $Cn <= 0 || $t <= 0) $error = "Valeurs invalides";
            else {
                $n = log($Cn / $C0) / log(1 + $t);
                $result = ["Durée (années)" => number_format($n, 2)];
            }
            break;
        case 'taux_effectif_global':
            $t_nom = floatval($_POST['taux_nominal']) / 100;
            $p = intval($_POST['periodes']);
            $teg = pow(1 + $t_nom/$p, $p) - 1;
            $result = ["Taux effectif global (TEG)" => number_format($teg * 100, 4) . " %"];
            break;
        default:
            $error = "Formule non reconnue";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .formula-card { cursor: pointer; transition: 0.2s; }
        .formula-card:hover { transform: translateY(-5px); background-color: #f8f9fa; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">📐 Formules</div>
                <div class="list-group list-group-flush">
                    <a href="?formule=capitalisation" class="list-group-item list-group-item-action <?= $formule=='capitalisation'?'active':'' ?>">Capitalisation (Valeur acquise)</a>
                    <a href="?formule=actualisation" class="list-group-item list-group-item-action <?= $formule=='actualisation'?'active':'' ?>">Actualisation (Valeur actuelle)</a>
                    <a href="?formule=annuite_constante" class="list-group-item list-group-item-action <?= $formule=='annuite_constante'?'active':'' ?>">Annuité constante (emprunt)</a>
                    <a href="?formule=amortissement_constant" class="list-group-item list-group-item-action <?= $formule=='amortissement_constant'?'active':'' ?>">Amortissement constant</a>
                    <a href="?formule=taux_equivalent" class="list-group-item list-group-item-action <?= $formule=='taux_equivalent'?'active':'' ?>">Taux équivalent</a>
                    <a href="?formule=taux_proportionnel" class="list-group-item list-group-item-action <?= $formule=='taux_proportionnel'?'active':'' ?>">Taux proportionnel</a>
                    <a href="?formule=duree_capitalisation" class="list-group-item list-group-item-action <?= $formule=='duree_capitalisation'?'active':'' ?>">Durée de capitalisation</a>
                    <a href="?formule=taux_effectif_global" class="list-group-item list-group-item-action <?= $formule=='taux_effectif_global'?'active':'' ?>">TEG / TAEG</a>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3><i class="bi bi-calculator"></i> Mathématiques financières – <?= ucfirst(str_replace('_', ' ', $formule)) ?></h3>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>
                    <form method="post">
                        <input type="hidden" name="calcul" value="<?= $formule ?>">
                        <?php if ($formule == 'capitalisation'): ?>
                            <div class="mb-3"><label>Capital initial (€)</label><input type="number" step="any" name="capital" class="form-control" required></div>
                            <div class="mb-3"><label>Taux annuel (%)</label><input type="number" step="any" name="taux" class="form-control" required></div>
                            <div class="mb-3"><label>Nombre d'années</label><input type="number" step="1" name="annees" class="form-control" required></div>
                        <?php elseif ($formule == 'actualisation'): ?>
                            <div class="mb-3"><label>Capital futur (€)</label><input type="number" step="any" name="capital_futur" class="form-control" required></div>
                            <div class="mb-3"><label>Taux annuel (%)</label><input type="number" step="any" name="taux" class="form-control" required></div>
                            <div class="mb-3"><label>Nombre d'années</label><input type="number" step="1" name="annees" class="form-control" required></div>
                        <?php elseif ($formule == 'annuite_constante'): ?>
                            <div class="mb-3"><label>Montant emprunté (€)</label><input type="number" step="any" name="emprunt" class="form-control" required></div>
                            <div class="mb-3"><label>Taux annuel (%)</label><input type="number" step="any" name="taux" class="form-control" required></div>
                            <div class="mb-3"><label>Durée (années)</label><input type="number" step="1" name="annees" class="form-control" required></div>
                        <?php elseif ($formule == 'amortissement_constant'): ?>
                            <div class="mb-3"><label>Montant emprunté (€)</label><input type="number" step="any" name="emprunt" class="form-control" required></div>
                            <div class="mb-3"><label>Taux annuel (%)</label><input type="number" step="any" name="taux" class="form-control" required></div>
                            <div class="mb-3"><label>Durée (années)</label><input type="number" step="1" name="annees" class="form-control" required></div>
                        <?php elseif ($formule == 'taux_equivalent'): ?>
                            <div class="mb-3"><label>Taux nominal annuel (%)</label><input type="number" step="any" name="taux_nominal" class="form-control" required></div>
                            <div class="mb-3"><label>Nombre de périodes par an</label><input type="number" step="1" name="periodes" class="form-control" required></div>
                        <?php elseif ($formule == 'taux_proportionnel'): ?>
                            <div class="mb-3"><label>Taux annuel (%)</label><input type="number" step="any" name="taux_annuel" class="form-control" required></div>
                            <div class="mb-3"><label>Nombre de périodes par an</label><input type="number" step="1" name="periodes" class="form-control" required></div>
                        <?php elseif ($formule == 'duree_capitalisation'): ?>
                            <div class="mb-3"><label>Capital initial (€)</label><input type="number" step="any" name="capital_initial" class="form-control" required></div>
                            <div class="mb-3"><label>Capital final (€)</label><input type="number" step="any" name="capital_final" class="form-control" required></div>
                            <div class="mb-3"><label>Taux annuel (%)</label><input type="number" step="any" name="taux" class="form-control" required></div>
                        <?php elseif ($formule == 'taux_effectif_global'): ?>
                            <div class="mb-3"><label>Taux nominal annuel (%)</label><input type="number" step="any" name="taux_nominal" class="form-control" required></div>
                            <div class="mb-3"><label>Nombre de périodes de capitalisation par an</label><input type="number" step="1" name="periodes" class="form-control" required></div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">Calculer</button>
                    </form>
                    <?php if ($result): ?>
                        <div class="alert alert-success mt-4">
                            <?php foreach ($result as $label => $value): ?>
                                <strong><?= $label ?> :</strong> <?= $value ?><br>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>

<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$page_title = "Simulation de crédit / emprunt";
include 'inc_navbar.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .formula { background: #f8f9fa; padding: 10px; border-radius: 8px; font-family: monospace; }
        .table-amortissement { font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-bank"></i> Simulation de crédit / emprunt</h2>
                    <p>Calcul des mensualités, coût total, tableau d’amortissement</p>
                </div>
                <div class="card-body">

                    <div class="alert alert-info">
                        <strong>📘 Formule des mensualités constantes</strong><br>
                        Mensualité = Capital × (taux/12) / (1 - (1 + taux/12)<sup>-nombre de mois</sup>)
                    </div>

                    <form method="post">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Montant emprunté (€)</label>
                                <input type="number" name="capital" class="form-control" value="100000" step="1000" required>
                            </div>
                            <div class="col-md-3">
                                <label>Taux annuel (%)</label>
                                <input type="number" name="taux" class="form-control" value="4.5" step="0.1" required>
                            </div>
                            <div class="col-md-3">
                                <label>Durée (années)</label>
                                <input type="number" name="duree_ans" class="form-control" value="15" required>
                            </div>
                            <div class="col-md-3">
                                <label>&nbsp;</label><br>
                                <button type="submit" name="simuler" class="btn btn-primary">Simuler</button>
                            </div>
                        </div>
                    </form>

                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simuler'])) {
                        $capital = (float)$_POST['capital'];
                        $taux_annuel = (float)$_POST['taux'];
                        $duree_ans = (int)$_POST['duree_ans'];
                        $nb_mois = $duree_ans * 12;
                        $taux_mensuel = $taux_annuel / 100 / 12;

                        if ($taux_mensuel > 0) {
                            $mensualite = $capital * $taux_mensuel * pow(1 + $taux_mensuel, $nb_mois) / (pow(1 + $taux_mensuel, $nb_mois) - 1);
                        } else {
                            $mensualite = $capital / $nb_mois;
                        }
                        $cout_total = $mensualite * $nb_mois;
                        $interets_totaux = $cout_total - $capital;

                        echo <<<HTML
                        <div class="alert alert-success mt-4">
                            <strong>Résultats :</strong><br>
                            Mensualité : <strong>{$mensualite} €</strong><br>
                            Coût total du crédit : <strong>{$cout_total} €</strong><br>
                            Intérêts totaux : <strong>{$interets_totaux} €</strong>
                        </div>
                        <h5>Tableau d’amortissement (premières années)</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-amortissement">
                                <thead class="table-dark">
                                    <tr><th>Période</th><th>Capital restant dû</th><th>Intérêts</th><th>Amortissement</th><th>Mensualité</th></tr>
                                </thead>
                                <tbody>
HTML;
                        $capital_restant = $capital;
                        $max_lignes = min(24, $nb_mois);
                        for ($i = 1; $i <= $max_lignes; $i++) {
                            $interet = $capital_restant * $taux_mensuel;
                            $amortissement = $mensualite - $interet;
                            $capital_restant -= $amortissement;
                            echo "<tr>";
                            echo "<td>{$i}</td>";
                            echo "<td>" . number_format($capital_restant, 2, ',', ' ') . " €</td>";
                            echo "<td>" . number_format($interet, 2, ',', ' ') . " €</td>";
                            echo "<td>" . number_format($amortissement, 2, ',', ' ') . " €</td>";
                            echo "<td>" . number_format($mensualite, 2, ',', ' ') . " €</td>";
                            echo "</tr>";
                            if ($capital_restant <= 0) break;
                        }
                        if ($nb_mois > $max_lignes) {
                            echo "<tr><td colspan='5' class='text-center'>... suite du tableau disponible sur demande</td></tr>";
                        }
                        echo "</tbody></table></div>";
                    }
                    ?>

                    <hr>
                    <div class="alert alert-secondary">
                        <strong>🌐 Outils associés</strong><br>
                        <a href="budget_previsionnel_van_tri.php" class="btn btn-sm btn-outline-primary">Budget prévisionnel – VAN / TRI</a>
                        <a href="gestion_previsionnelle.php" class="btn btn-sm btn-outline-primary">Gestion prévisionnelle (loi normale)</a>
                        <a href="analyse_scenarios_avancee.php" class="btn btn-sm btn-outline-primary">Analyse de scénarios (VAN/TRI)</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>

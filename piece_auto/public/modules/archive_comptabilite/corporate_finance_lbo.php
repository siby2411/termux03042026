<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Corporate Finance – LBO & Modélisation financière";
include 'inc_navbar.php';
require_once dirname(__DIR__) . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .section-title { background: #0d6efd; color: white; padding: 8px 15px; border-radius: 20px; display: inline-block; margin-bottom: 20px; }
        .formula { background: #f8f9fa; padding: 10px; border-radius: 8px; font-family: monospace; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-bank"></i> Corporate Finance – LBO & Modélisation financière</h2>
                    <p>Leveraged Buy-Out, Cash Sweep, dette Senior/Mezzanine, TRI, covenants</p>
                </div>
                <div class="card-body">

                    <!-- ==================== SECTION 1 : THÉORIE LBO ==================== -->
                    <h4 class="section-title"><i class="bi bi-journal-bookmark-fill"></i> 1. Qu'est-ce qu'un LBO ?</h4>
                    <div class="alert alert-info">
                        <strong>Leveraged Buy-Out (LBO)</strong> – Acquisition d'une entreprise financée majoritairement par de la dette (levier financier). La dette est remboursée grâce aux flux de trésorerie générés par la cible.
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card card-term"><div class="card-body"><strong>Dette Senior</strong><br>Taux bas, prioritaire, maturité 5-7 ans.</div></div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-term"><div class="card-body"><strong>Dette Mezzanine</strong><br>Taux plus élevé, maturité 7-10 ans, parfois convertible.</div></div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-term"><div class="card-body"><strong>Equity (apport en capital)</strong><br>Fonds propres du sponsor, rémunéré en dernier.</div></div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 2 : SIMULATEUR LBO ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-calculator"></i> 2. Simulateur LBO – Cas ALFA</h4>
                    <form method="post" id="lboForm">
                        <div class="row">
                            <div class="col-md-3"><label>EBITDA initial (M€)</label><input type="number" name="ebitda" class="form-control" value="10" step="1" required></div>
                            <div class="col-md-3"><label>Multiple d'entrée (x EBITDA)</label><input type="number" name="multiple_entree" class="form-control" value="8" step="0.5" required></div>
                            <div class="col-md-3"><label>Croissance EBITDA (%)</label><input type="number" name="croissance" class="form-control" value="3" step="0.5" required></div>
                            <div class="col-md-3"><label>Multiple de sortie (x EBITDA)</label><input type="number" name="multiple_sortie" class="form-control" value="8" step="0.5" required></div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-3"><label>Dette Senior (M€)</label><input type="number" name="dette_senior" class="form-control" value="60" step="5" required></div>
                            <div class="col-md-3"><label>Taux Senior (%)</label><input type="number" name="taux_senior" class="form-control" value="5" step="0.5" required></div>
                            <div class="col-md-3"><label>Dette Mezzanine (M€)</label><input type="number" name="dette_mezz" class="form-control" value="5" step="5" required></div>
                            <div class="col-md-3"><label>Taux Mezzanine (%)</label><input type="number" name="taux_mezz" class="form-control" value="10" step="0.5" required></div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4"><label>Apport en capital (M€)</label><input type="number" name="equity" class="form-control" value="20" step="5" required></div>
                            <div class="col-md-4"><label>CAPEX annuel (M€)</label><input type="number" name="capex" class="form-control" value="2" step="0.5" required></div>
                            <div class="col-md-4"><label>Taux d'impôt (%)</label><input type="number" name="taux_is" class="form-control" value="25" step="1" required></div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-4"><label>Durée (années)</label><input type="number" name="duree" class="form-control" value="5" required></div>
                            <div class="col-md-4"><label>Stress test taux (+ bps)</label><input type="number" name="stress_taux" class="form-control" value="0" step="50"></div>
                            <div class="col-md-4"><label>Seuil covenant (ratio Dette/EBITDA)</label><input type="number" name="seuil_covenant" class="form-control" value="4.0" step="0.5"></div>
                        </div>
                        <button type="submit" name="simuler_lbo" class="btn btn-primary mt-3">Lancer la simulation LBO</button>
                    </form>

                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simuler_lbo'])) {
                        $ebitda_init = (float)$_POST['ebitda'];
                        $multiple_entree = (float)$_POST['multiple_entree'];
                        $croissance = (float)$_POST['croissance'] / 100;
                        $multiple_sortie = (float)$_POST['multiple_sortie'];
                        $dette_senior = (float)$_POST['dette_senior'];
                        $taux_senior = (float)$_POST['taux_senior'] / 100;
                        $dette_mezz = (float)$_POST['dette_mezz'];
                        $taux_mezz = (float)$_POST['taux_mezz'] / 100;
                        $equity = (float)$_POST['equity'];
                        $capex = (float)$_POST['capex'];
                        $taux_is = (float)$_POST['taux_is'] / 100;
                        $duree = (int)$_POST['duree'];
                        $stress_taux = (float)$_POST['stress_taux'] / 10000;
                        $seuil_covenant = (float)$_POST['seuil_covenant'];

                        // Prix d'acquisition
                        $prix_acquisition = $ebitda_init * $multiple_entree;
                        $capital_restant_senior = $dette_senior;
                        $capital_restant_mezz = $dette_mezz;
                        $capital_restant_total = $capital_restant_senior + $capital_restant_mezz;

                        $annees = [];
                        $ebitda = $ebitda_init;
                        $alertes = [];

                        for ($i = 1; $i <= $duree; $i++) {
                            // Calcul des intérêts
                            $interets_senior = $capital_restant_senior * ($taux_senior + $stress_taux);
                            $interets_mezz = $capital_restant_mezz * ($taux_mezz + $stress_taux);
                            $interets_totaux = $interets_senior + $interets_mezz;

                            // Cash flow disponible
                            $impots = ($ebitda - $interets_totaux - $capex) * $taux_is;
                            if ($impots < 0) $impots = 0;
                            $cash_flow_disponible = $ebitda - $capex - $impots - $interets_totaux;

                            // Cash Sweep : remboursement prioritaire de la dette Senior
                            $remboursement_senior = min($cash_flow_disponible, $capital_restant_senior);
                            $capital_restant_senior -= $remboursement_senior;
                            $cash_flow_disponible -= $remboursement_senior;

                            // Puis Mezzanine
                            $remboursement_mezz = min($cash_flow_disponible, $capital_restant_mezz);
                            $capital_restant_mezz -= $remboursement_mezz;

                            $capital_restant_total = $capital_restant_senior + $capital_restant_mezz;
                            $ratio_levier = ($ebitda > 0) ? $capital_restant_total / $ebitda : 0;

                            if ($ratio_levier > $seuil_covenant) {
                                $alertes[] = "⚠️ Alerte covenant année $i : ratio $ratio_levier > seuil $seuil_covenant";
                            }

                            $annees[] = [
                                'annee' => $i,
                                'ebitda' => $ebitda,
                                'interets' => $interets_totaux,
                                'cash_flow' => $cash_flow_disponible,
                                'capital_restant' => $capital_restant_total,
                                'ratio_levier' => $ratio_levier,
                                'remboursement_senior' => $remboursement_senior,
                                'remboursement_mezz' => $remboursement_mezz
                            ];

                            $ebitda *= (1 + $croissance);
                        }

                        // Valeur de sortie
                        $ebitda_sortie = $ebitda_init * pow(1 + $croissance, $duree);
                        $valeur_sortie = $ebitda_sortie * $multiple_sortie;
                        $dette_residuelle = $capital_restant_total;
                        $equity_sortie = $valeur_sortie - $dette_residuelle;
                        $tri = pow($equity_sortie / $equity, 1 / $duree) - 1;
                        $multiple_investissement = $equity_sortie / $equity;

                        echo <<<HTML
                        <div class="alert alert-success mt-4">
                            <strong>Résultats de la simulation LBO</strong><br>
                            Prix d'acquisition : {$prix_acquisition} M€<br>
                            Dette Senior initiale : {$dette_senior} M€<br>
                            Dette Mezzanine initiale : {$dette_mezz} M€<br>
                            Apport en capital (Equity) : {$equity} M€<br><br>
                            <strong>Valeur de sortie à l'année {$duree} : {$valeur_sortie} M€</strong><br>
                            Dette résiduelle : {$dette_residuelle} M€<br>
                            Equity récupéré : {$equity_sortie} M€<br>
                            <strong>TRI du sponsor : " . round($tri * 100, 2) . "%</strong><br>
                            <strong>Multiple de l'investissement : " . round($multiple_investissement, 2) . "x</strong>
                        </div>
HTML;

                        if (!empty($alertes)) {
                            echo '<div class="alert alert-warning">' . implode('<br>', $alertes) . '</div>';
                        }

                        echo '<h5 class="mt-4">Tableau de remboursement (Cash Sweep)</h5>';
                        echo '<div class="table-responsive"><table class="table table-bordered table-sm">';
                        echo '<thead class="table-dark"><tr><th>Année</th><th>EBITDA</th><th>Intérêts</th><th>Cash Flow dispo</th><th>Remboursement Senior</th><th>Remboursement Mezz</th><th>Capital restant</th><th>Ratio Dette/EBITDA</th></tr></thead><tbody>';
                        foreach ($annees as $a) {
                            echo "<tr>";
                            echo "<td>{$a['annee']}</td>";
                            echo "<td class='text-end'>" . round($a['ebitda'], 2) . "</td>";
                            echo "<td class='text-end'>" . round($a['interets'], 2) . "</td>";
                            echo "<td class='text-end'>" . round($a['cash_flow'], 2) . "</td>";
                            echo "<td class='text-end'>" . round($a['remboursement_senior'], 2) . "</td>";
                            echo "<td class='text-end'>" . round($a['remboursement_mezz'], 2) . "</td>";
                            echo "<td class='text-end'>" . round($a['capital_restant'], 2) . "</td>";
                            echo "<td class='text-end'>" . round($a['ratio_levier'], 2) . "</td>";
                            echo "</tr>";
                        }
                        echo '</tbody></table></div>';
                    }
                    ?>

                    <!-- ==================== SECTION 3 : MATRICE DE SENSIBILITÉ ==================== -->
                    <h4 class="section-title mt-5"><i class="bi bi-grid-3x3"></i> 3. Matrice de sensibilité – TRI vs multiple de sortie / croissance</h4>
                    <div class="alert alert-secondary">
                        <form method="post" id="sensibiliteForm">
                            <div class="row">
                                <div class="col-md-6"><label>Fourchette multiples de sortie (min,max,step)</label><input type="text" name="multiples" class="form-control" value="6,10,1"></div>
                                <div class="col-md-6"><label>Fourchette croissance EBITDA (min,max,step)</label><input type="text" name="croissances" class="form-control" value="0,6,1"></div>
                            </div>
                            <button type="submit" name="matrice_sensibilite" class="btn btn-info mt-2">Générer la matrice</button>
                        </form>
                    </div>

                    <?php
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['matrice_sensibilite'])) {
                        list($mult_min, $mult_max, $mult_step) = explode(',', $_POST['multiples']);
                        list($croiss_min, $croiss_max, $croiss_step) = explode(',', $_POST['croissances']);

                        echo '<div class="table-responsive mt-3"><table class="table table-bordered"><thead class="table-dark"><tr><th>Croissance / Multiple</th>';
                        for ($m = (float)$mult_min; $m <= (float)$mult_max; $m += (float)$mult_step) {
                            echo "<th>" . round($m, 1) . "x</th>";
                        }
                        echo "</tr></thead><tbody>";
                        for ($c = (float)$croiss_min; $c <= (float)$croiss_max; $c += (float)$croiss_step) {
                            echo "<tr><th>" . $c . "%</th>";
                            for ($m = (float)$mult_min; $m <= (float)$mult_max; $m += (float)$mult_step) {
                                $ebitda_sortie = $ebitda_init * pow(1 + $c / 100, $duree);
                                $valeur_sortie = $ebitda_sortie * $m;
                                $equity_sortie = $valeur_sortie - $capital_restant_total;
                                $tri = pow($equity_sortie / $equity, 1 / $duree) - 1;
                                $couleur = $tri > 0.15 ? 'success' : ($tri > 0.10 ? 'warning' : 'danger');
                                echo "<td class='bg-{$couleur} text-white text-center'>" . round($tri * 100, 1) . "%</td>";
                            }
                            echo "</tr>";
                        }
                        echo "</tbody></table></div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>

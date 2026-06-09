<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Gestion de portefeuille – Performance & risques";
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .section-title { background: #0d6efd; color: white; padding: 8px 15px; border-radius: 20px; display: inline-block; margin-bottom: 20px; }
        .portfolio-card { transition: 0.2s; border-left: 5px solid #0d6efd; margin-bottom: 15px; }
        .risk-low { background-color: #d4edda; }
        .risk-medium { background-color: #fff3cd; }
        .risk-high { background-color: #f8d7da; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-graph-up"></i> Gestion de portefeuille – Performance & risques</h2>
                    <p>Calcul de rendement, volatilité, ratio de Sharpe, stratégies d'investissement</p>
                </div>
                <div class="card-body">

                    <!-- ==================== SECTION 1 : PORTEFEUILLE VIRTUEL ==================== -->
                    <h4 class="section-title"><i class="bi bi-briefcase"></i> 1. Portefeuille virtuel</h4>
                    <div class="row">
                        <div class="col-md-12">
                            <form method="post" id="portfolioForm">
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label>Symbole action</label>
                                        <input type="text" name="symbole" class="form-control" placeholder="AAPL, MSFT, TSLA" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Quantité</label>
                                        <input type="number" name="quantite" class="form-control" value="10" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label>Prix d'achat (€)</label>
                                        <input type="number" step="0.01" name="prix_achat" class="form-control" required>
                                    </div>
                                    <div class="col-md-2">
                                        <label>Prix actuel (€)</label>
                                        <input type="number" step="0.01" name="prix_actuel" class="form-control" required>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" name="ajouter" class="btn btn-primary">Ajouter au portefeuille</button>
                                        <button type="submit" name="reset" class="btn btn-danger">Réinitialiser</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php
                    // Initialisation du portefeuille en session
                    if (!isset($_SESSION['portfolio'])) {
                        $_SESSION['portfolio'] = [];
                    }

                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        if (isset($_POST['ajouter'])) {
                            $_SESSION['portfolio'][] = [
                                'symbole' => $_POST['symbole'],
                                'quantite' => (int)$_POST['quantite'],
                                'prix_achat' => (float)$_POST['prix_achat'],
                                'prix_actuel' => (float)$_POST['prix_actuel']
                            ];
                        } elseif (isset($_POST['reset'])) {
                            $_SESSION['portfolio'] = [];
                        }
                    }

                    $valeur_totale = 0;
                    $cout_total = 0;
                    $rendements = [];

                    if (!empty($_SESSION['portfolio'])) {
                        echo '<div class="table-responsive mt-4"><table class="table table-bordered">';
                        echo '<thead class="table-dark"><tr><th>Symbole</th><th>Quantité</th><th>Prix achat</th><th>Prix actuel</th><th>Valeur achat</th><th>Valeur actuelle</th><th>Rendement (%)</th></tr></thead><tbody>';
                        foreach ($_SESSION['portfolio'] as $action) {
                            $valeur_achat = $action['quantite'] * $action['prix_achat'];
                            $valeur_actuelle = $action['quantite'] * $action['prix_actuel'];
                            $rendement = ($valeur_actuelle - $valeur_achat) / $valeur_achat * 100;
                            $valeur_totale += $valeur_actuelle;
                            $cout_total += $valeur_achat;
                            $rendements[] = $rendement / 100;
                            echo "<tr>";
                            echo "<td>{$action['symbole']}</td>";
                            echo "<td>{$action['quantite']}</td>";
                            echo "<td>" . number_format($action['prix_achat'], 2) . " €</td>";
                            echo "<td>" . number_format($action['prix_actuel'], 2) . " €</td>";
                            echo "<td>" . number_format($valeur_achat, 2) . " €</td>";
                            echo "<td>" . number_format($valeur_actuelle, 2) . " €</td>";
                            $rendement_class = $rendement >= 0 ? 'text-success' : 'text-danger';
                            echo "<td class='{$rendement_class} fw-bold'>" . number_format($rendement, 2) . "%</td>";
                            echo "</tr>";
                        }
                        echo '</tbody>';
                        echo "<tr class='table-info'><th colspan='4'>TOTAL</th><th>" . number_format($cout_total, 2) . " €</th><th>" . number_format($valeur_totale, 2) . " €</th><th>" . number_format(($valeur_totale - $cout_total) / $cout_total * 100, 2) . "%</th></tr>";
                        echo '</table></div>';

                        // Calcul des indicateurs
                        $rendement_portefeuille = ($valeur_totale - $cout_total) / $cout_total;
                        $nb_actifs = count($rendements);
                        $moyenne_rendements = array_sum($rendements) / $nb_actifs;
                        $variance = array_sum(array_map(function($r) use ($moyenne_rendements) { return pow($r - $moyenne_rendements, 2); }, $rendements)) / $nb_actifs;
                        $volatilite = sqrt($variance);
                        $taux_sans_risque = 0.02; // 2%
                        $sharpe_ratio = $volatilite > 0 ? ($rendement_portefeuille - $taux_sans_risque) / $volatilite : 0;

                        echo <<<HTML
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h5>Rendement total</h5>
                                        <h3>" . number_format($rendement_portefeuille * 100, 2) . "%</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h5>Volatilité (risque)</h5>
                                        <h3>" . number_format($volatilite * 100, 2) . "%</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-warning text-dark">
                                    <div class="card-body text-center">
                                        <h5>Ratio de Sharpe</h5>
                                        <h3>" . number_format($sharpe_ratio, 2) . "</h3>
                                        <small>Rendement ajusté du risque</small>
                                    </div>
                                </div>
                            </div>
                        </div>
HTML;
                    }
                    ?>

                    <!-- ==================== SECTION 2 : ANALYSE DES RISQUES ==================== -->
                    <h4 class="section-title mt-5"><i class="bi bi-shield-exclamation"></i> 2. Analyse des risques financiers</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card portfolio-card">
                                <div class="card-body">
                                    <h5>Risque de marché</h5>
                                    <p>Variation des prix des actifs. Mesuré par le beta (sensibilité au marché).</p>
                                    <span class="badge bg-primary">Beta > 1 : volatil</span>
                                    <span class="badge bg-secondary">Beta = 1 : neutre</span>
                                    <span class="badge bg-success">Beta < 1 : défensif</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card portfolio-card">
                                <div class="card-body">
                                    <h5>Risque de liquidité</h5>
                                    <p>Capacité à vendre rapidement sans perte significative.</p>
                                    <span class="badge bg-danger">Spread élevé = liquidité faible</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card portfolio-card">
                                <div class="card-body">
                                    <h5>Risque de change</h5>
                                    <p>Pour les actifs libellés en devises étrangères (USD, GBP, etc.).</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 3 : STRATÉGIES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-diagram-3"></i> 3. Stratégies usuelles</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-secondary text-white">Stratégie "Buy and Hold"</div>
                                <div class="card-body">Acheter et conserver sur le long terme. Moins de frais, capitalisation des dividendes.</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header bg-secondary text-white">Stratégie "Dollar Cost Averaging" (DCA)</div>
                                <div class="card-body">Investir des sommes fixes régulièrement, indépendamment du prix.</div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3">
                            <div class="card">
                                <div class="card-header bg-secondary text-white">Couverture (Hedging)</div>
                                <div class="card-body">Utilisation d'options ou de futures pour se protéger contre les baisses.</div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3">
                            <div class="card">
                                <div class="card-header bg-secondary text-white">Arbitrage</div>
                                <div class="card-body">Profiter des écarts de prix entre deux marchés ou deux actifs corrélés.</div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 4 : SIMULATION D'OPTIONS ==================== -->
                    <h4 class="section-title mt-5"><i class="bi bi-calculator"></i> 4. Simulateur d'options (Call / Put)</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <form method="post">
                                        <div class="mb-2"><label>Type d'option</label>
                                            <select name="option_type" class="form-control">
                                                <option value="call">Call (droit d'acheter)</option>
                                                <option value="put">Put (droit de vendre)</option>
                                            </select>
                                        </div>
                                        <div class="mb-2"><label>Prix du sous-jacent (S)</label><input type="number" name="s" class="form-control" value="100"></div>
                                        <div class="mb-2"><label>Prix d'exercice (K)</label><input type="number" name="k" class="form-control" value="105"></div>
                                        <div class="mb-2"><label>Taux sans risque (%)</label><input type="number" name="r" class="form-control" value="5"></div>
                                        <div class="mb-2"><label>Volatilité (%)</label><input type="number" name="sigma" class="form-control" value="20"></div>
                                        <div class="mb-2"><label>Temps jusqu'à échéance (années)</label><input type="number" name="t" class="form-control" value="1"></div>
                                        <button type="submit" name="calc_option" class="btn btn-primary">Calculer le prix de l'option</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php
                            if (isset($_POST['calc_option'])) {
                                $S = (float)$_POST['s'];
                                $K = (float)$_POST['k'];
                                $r = (float)$_POST['r'] / 100;
                                $sigma = (float)$_POST['sigma'] / 100;
                                $T = (float)$_POST['t'];
                                $type = $_POST['option_type'];

                                function normalCDF($x) { return (1.0 + erf($x / sqrt(2.0))) / 2.0; }

                                $d1 = (log($S / $K) + ($r + $sigma * $sigma / 2) * $T) / ($sigma * sqrt($T));
                                $d2 = $d1 - $sigma * sqrt($T);

                                if ($type == 'call') {
                                    $prix = $S * normalCDF($d1) - $K * exp(-$r * $T) * normalCDF($d2);
                                } else {
                                    $prix = $K * exp(-$r * $T) * normalCDF(-$d2) - $S * normalCDF(-$d1);
                                }

                                echo <<<HTML
                                <div class="card bg-light">
                                    <div class="card-header">Prix de l'option {$type}</div>
                                    <div class="card-body text-center">
                                        <h3>" . number_format($prix, 2) . " €</h3>
                                        <small>Prix d'exercice : {$K} € | Sous-jacent : {$S} €</small>
                                    </div>
                                </div>
HTML;
                            }
                            ?>
                        </div>
                    </div>

                    <!-- ==================== SECTION 5 : GRAPHIQUE ==================== -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <canvas id="portfolioChart" width="400" height="200"></canvas>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="bi bi-info-circle"></i> <strong>Conseil d'expert :</strong> Un portefeuille diversifié réduit le risque spécifique. Le ratio de Sharpe < 1 indique un rendement insuffisant par rapport au risque pris.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const ctx = document.getElementById('portfolioChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php 
                $labels = [];
                $values = [];
                foreach ($_SESSION['portfolio'] ?? [] as $action) {
                    $labels[] = $action['symbole'];
                    $values[] = ($action['quantite'] * $action['prix_actuel']);
                }
                echo json_encode($labels);
            ?>,
            datasets: [{
                label: 'Valeur actuelle (€)',
                data: <?php echo json_encode($values); ?>,
                backgroundColor: '#0d6efd'
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });
</script>
<?php include 'inc_footer.php'; ?>

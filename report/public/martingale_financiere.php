<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Martingale financière – Processus stochastiques & couverture IFRS 9";
include 'inc_navbar.php';
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
        .card-term { transition: 0.2s; border-left: 5px solid #0d6efd; margin-bottom: 15px; }
        .card-term:hover { transform: translateX(5px); background-color: #f8f9fa; }
        .formula { background: #f8f9fa; padding: 10px; border-radius: 8px; font-family: monospace; font-size: 0.9rem; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-graph-up"></i> Martingale financière – Processus stochastiques & couverture IFRS 9</h2>
                    <p>Modélisation, simulation Monte-Carlo, grecques, hedging, tests d'efficacité</p>
                </div>
                <div class="card-body">

                    <!-- ==================== SECTION 1 : FONDEMENTS THÉORIQUES ==================== -->
                    <h4 class="section-title"><i class="bi bi-math"></i> 1. Fondements théoriques – Processus stochastiques</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Mouvement Brownien Géométrique (MBG)</div>
                                    <div class="term-def">Base du modèle de Black-Scholes pour la modélisation des prix d'actifs.</div>
                                    <div class="formula mt-2">dS_t = μ S_t dt + σ S_t dW_t</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Théorème de Girsanov (changement de mesure)</div>
                                    <div class="term-def">Passage de la mesure historique à la mesure risque-neutre (ℚ) pour le pricing sans arbitrage.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Calcul d'Itô</div>
                                    <div class="term-def">Manipulation des intégrales stochastiques pour définir les dynamiques de prix.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Équation de Black-Scholes (EDP)</div>
                                    <div class="term-def">∂V/∂t + ½σ²S² ∂²V/∂S² + rS ∂V/∂S - rV = 0</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 2 : OUTILS NUMÉRIQUES ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-calculator"></i> 2. Outils numériques & méthodes</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Simulation de Monte-Carlo</div>
                                    <div class="term-def">Génération de milliers de trajectoires pour estimer la valeur des options et la VaR.</div>
                                    <div class="formula mt-2">S_T = S_0 × exp((r - σ²/2)T + σ√T × ε)</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Arbres binomiaux (Cox-Ross-Rubinstein)</div>
                                    <div class="term-def">Pour options américaines (exercice anticipé).</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Différences finies</div>
                                    <div class="term-def">Résolution des EDP lorsque les solutions fermées n'existent pas.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 3 : LES GRECQUES (SENSIBILITÉS) ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-activity"></i> 3. Les Grecques – Gestion des risques</h4>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Delta (Δ)</div>
                                    <div class="term-def">∂V/∂S – Sensibilité du prix au sous-jacent. Base de la couverture dynamique.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Gamma (Γ)</div>
                                    <div class="term-def">∂²V/∂S² – Risque de courbure (convexité).</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Vega (ν)</div>
                                    <div class="term-def">∂V/∂σ – Sensibilité à la volatilité (crucial pour le provisionnement).</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Theta (Θ)</div>
                                    <div class="term-def">∂V/∂t – Décroissance temporelle de l'option.</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Rho (ρ)</div>
                                    <div class="term-def">∂V/∂r – Sensibilité aux taux d'intérêt.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 4 : COMPTABILITÉ DE COUVERTURE IFRS 9 ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-file-earmark-check"></i> 4. Comptabilité de couverture (IFRS 9 / IAS 39)</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Dérivé hypothétique</div>
                                    <div class="term-def">Création d'un dérivé théorique miroir des conditions de l'élément couvert pour mesurer l'inefficacité.</div>
                                    <div class="formula mt-2">Inefficacité = |ΔFV<sub>réel</sub> - ΔFV<sub>hypothétique</sub>|</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Fair Value Hedge / Cash Flow Hedge</div>
                                    <div class="term-def">Couverture de juste valeur vs couverture de flux de trésorerie (impact en P&L vs OCI).</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Test d'efficacité prospective</div>
                                    <div class="term-def">Vérification ex-ante que la relation de couverture sera efficace (régression linéaire).</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title">Test d'efficacité rétrospective</div>
                                    <div class="term-def">Vérification ex-post sur une plage de 80%-125% (dollar offset method).</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 5 : SIMULATEUR MONTE-CARLO ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-sliders"></i> 5. Simulateur Monte-Carlo – Option Call européenne</h4>
                    <div class="card bg-light">
                        <div class="card-body">
                            <form method="post" id="monteCarloForm">
                                <div class="row">
                                    <div class="col-md-3"><label>Prix initial S0 (€)</label><input type="number" name="s0" class="form-control" value="100" required></div>
                                    <div class="col-md-3"><label>Prix d'exercice K (€)</label><input type="number" name="k" class="form-control" value="105" required></div>
                                    <div class="col-md-3"><label>Taux sans risque r (%)</label><input type="number" step="0.1" name="r" class="form-control" value="5" required></div>
                                    <div class="col-md-3"><label>Volatilité σ (%)</label><input type="number" step="1" name="sigma" class="form-control" value="20" required></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4"><label>Maturité T (années)</label><input type="number" step="0.1" name="t" class="form-control" value="1" required></div>
                                    <div class="col-md-4"><label>Nombre de simulations</label><input type="number" name="n_sim" class="form-control" value="10000" required></div>
                                    <div class="col-md-4"><label>Nombre de pas</label><input type="number" name="n_steps" class="form-control" value="252" required></div>
                                </div>
                                <button type="submit" name="simuler_mc" class="btn btn-primary mt-3">Lancer la simulation</button>
                            </form>

                            <?php
                            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simuler_mc'])) {
                                $S0 = (float)$_POST['s0'];
                                $K = (float)$_POST['k'];
                                $r = (float)$_POST['r'] / 100;
                                $sigma = (float)$_POST['sigma'] / 100;
                                $T = (float)$_POST['t'];
                                $nSim = min(10000, (int)$_POST['n_sim']);
                                $nSteps = (int)$_POST['n_steps'];

                                $dt = $T / $nSteps;
                                $mu = $r - 0.5 * $sigma * $sigma;
                                $sumPayoff = 0;

                                // Simulation simplifiée (calcul parallélisable)
                                for ($i = 0; $i < $nSim; $i++) {
                                    $S = $S0;
                                    for ($j = 0; $j < $nSteps; $j++) {
                                        $epsilon = $this->boxMuller();
                                        $S = $S * exp($mu * $dt + $sigma * sqrt($dt) * $epsilon);
                                    }
                                    $payoff = max($S - $K, 0);
                                    $sumPayoff += $payoff;
                                }
                                $prixOption = exp(-$r * $T) * ($sumPayoff / $nSim);

                                // Prix Black-Scholes théorique pour comparaison
                                $d1 = (log($S0 / $K) + ($r + $sigma * $sigma / 2) * $T) / ($sigma * sqrt($T));
                                $d2 = $d1 - $sigma * sqrt($T);
                                $bsPrice = $S0 * normalCDF($d1) - $K * exp(-$r * $T) * normalCDF($d2);

                                echo <<<HTML
                                <div class="alert alert-success mt-4">
                                    <strong>Résultats de la simulation Monte-Carlo :</strong><br>
                                    Prix de l'option Call européenne : <strong>{$prixOption} €</strong><br>
                                    Prix Black-Scholes théorique : <strong>{$bsPrice} €</strong><br>
                                    Écart : <strong>" . abs($prixOption - $bsPrice) . " €</strong><br>
                                    <small>Simulations : {$nSim} | Pas : {$nSteps}</small>
                                </div>
HTML;
                            }

                            function normalCDF($x) {
                                return (1.0 + erf($x / sqrt(2.0))) / 2.0;
                            }

                            function boxMuller() {
                                $u1 = mt_rand() / mt_getrandmax();
                                $u2 = mt_rand() / mt_getrandmax();
                                return sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);
                            }
                            ?>
                        </div>
                    </div>

                    <!-- ==================== SECTION 6 : ARCHITECTURE DONNÉES & AUDIT ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-database"></i> 6. Architecture de données & traçabilité (Audit)</h4>
                    <div class="alert alert-secondary">
                        <strong>Structure SQL recommandée pour les relations de couverture :</strong>
                        <pre class="bg-dark text-white p-2 rounded mt-2">
CREATE TABLE hedge_relations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    hedge_id VARCHAR(50) NOT NULL,
    hedge_type ENUM('Fair Value', 'Cash Flow') NOT NULL,
    hedged_item VARCHAR(255) NOT NULL,
    hedging_instrument VARCHAR(255) NOT NULL,
    inception_date DATE NOT NULL,
    maturity_date DATE NOT NULL,
    hedge_ratio DECIMAL(10,4) NOT NULL,
    status ENUM('Active', 'Discontinued', 'Expired') DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE hedge_effectiveness_tests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    hedge_id INT NOT NULL,
    test_date DATE NOT NULL,
    test_type ENUM('Prospective', 'Retrospective') NOT NULL,
    effectiveness_score DECIMAL(10,4) NOT NULL,
    is_effective BOOLEAN NOT NULL,
    model_version VARCHAR(50) NOT NULL,
    FOREIGN KEY (hedge_id) REFERENCES hedge_relations(id)
);

CREATE TABLE hedge_inefficiency (
    id INT PRIMARY KEY AUTO_INCREMENT,
    hedge_id INT NOT NULL,
    period DATE NOT NULL,
    real_fv_change DECIMAL(15,4),
    hypothetical_fv_change DECIMAL(15,4),
    inefficiency_amount DECIMAL(15,4),
    accounting_treatment ENUM('P&L', 'OCI') NOT NULL,
    FOREIGN KEY (hedge_id) REFERENCES hedge_relations(id)
);
                        </pre>
                    </div>

                    <!-- ==================== SECTION 7 : ROADMAP ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-map"></i> 7. Roadmap d'implémentation</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-dark">
                                <tr><th>Phase</th><th>Activité</th><th>Outil recommandé</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>I. Modélisation</td><td>Définition des processus de diffusion</td><td>Python (Jupyter) / R</td></tr>
                                <tr><td>II. Pricing</td><td>Moteur de simulation Monte-Carlo</td><td>C++ / QuantLib / Python</td></tr>
                                <tr><td>III. Risque</td><td>Calcul des VaR et Stress tests</td><td>SQL / Power BI / Tableau</td></tr>
                                <tr><td>IV. Audit</td><td>Réconciliation IFRS 9 / ERP</td><td>SAP TRM / Kyriba / Oracle</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="bi bi-info-circle"></i> <strong>Note d'expert :</strong> L'enjeu majeur n'est pas seulement le calcul du juste prix (fair value), mais la traçabilité des modèles. Toute simulation doit être réplicable et documentée pour satisfaire aux exigences des auditeurs externes et des régulateurs (BCE, AMF, ACPR).
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>

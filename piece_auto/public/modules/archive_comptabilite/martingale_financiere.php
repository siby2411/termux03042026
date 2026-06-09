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
        .tooltip-custom { cursor: help; border-bottom: 1px dotted #0d6efd; }
        .grecques-container { display: flex; flex-wrap: wrap; gap: 15px; margin-bottom: 20px; }
        .grecque-card { flex: 1; min-width: 180px; background: #f8f9fa; border-radius: 12px; padding: 15px; text-align: center; transition: 0.2s; }
        .grecque-card:hover { transform: translateY(-5px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .grecque-symbole { font-size: 2rem; font-weight: bold; color: #0d6efd; }
        .grecque-nom { font-weight: bold; margin: 10px 0 5px; }
        .grecque-def { font-size: 0.85rem; color: #555; }
        .grecque-formule { font-family: monospace; font-size: 0.75rem; background: #e9ecef; padding: 5px; border-radius: 6px; margin-top: 8px; }
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
                                    <div class="term-title tooltip-custom" title="Modèle mathématique décrivant l'évolution aléatoire des prix">Mouvement Brownien Géométrique (MBG) 📘</div>
                                    <div class="term-def">Base du modèle de Black-Scholes pour la modélisation des prix d'actifs.</div>
                                    <div class="formula mt-2">dS_t = μ S_t dt + σ S_t dW_t</div>
                                    <small>Où : S_t = prix à l'instant t, μ = tendance (drift), σ = volatilité, dW_t = bruit aléatoire (processus de Wiener).</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title tooltip-custom" title="Changement de probabilité pour éliminer le risque">Théorème de Girsanov (changement de mesure) 📘</div>
                                    <div class="term-def">Passage de la mesure historique à la mesure risque-neutre (ℚ) pour le pricing sans arbitrage.</div>
                                    <div class="formula mt-2">dW_t^ℚ = dW_t^ℙ + (μ - r)/σ dt</div>
                                    <small>Permet de valoriser les options comme si l'actif croissait au taux sans risque r.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title tooltip-custom" title="Règle de différentiation pour les processus aléatoires">Calcul d'Itô 📘</div>
                                    <div class="term-def">Manipulation des intégrales stochastiques pour définir les dynamiques de prix.</div>
                                    <div class="formula mt-2">df(S_t) = f'(S_t) dS_t + ½ f''(S_t) σ² S_t² dt</div>
                                    <small>Fondamental pour dériver l'équation de Black-Scholes.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title tooltip-custom" title="Équation aux dérivées partielles donnant le prix des options">Équation de Black-Scholes (EDP) 📘</div>
                                    <div class="term-def">∂V/∂t + ½σ²S² ∂²V/∂S² + rS ∂V/∂S - rV = 0</div>
                                    <small>Solution pour option Call européenne : C = S·N(d1) - K·e^(-rT)·N(d2)</small>
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
                                    <div class="term-title tooltip-custom" title="Méthode statistique de simulation aléatoire">Simulation de Monte-Carlo 🎲</div>
                                    <div class="term-def">Génération de milliers de trajectoires pour estimer la valeur des options et la VaR.</div>
                                    <div class="formula mt-2">S_T = S_0 × exp((r - σ²/2)T + σ√T × ε)</div>
                                    <small>ε ~ N(0,1) : variable aléatoire normale centrée réduite.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title tooltip-custom" title="Modèle discret pour options américaines">Arbres binomiaux (Cox-Ross-Rubinstein) 🌳</div>
                                    <div class="term-def">Pour options américaines (exercice anticipé).</div>
                                    <div class="formula mt-2">u = e^{σ√Δt}, d = 1/u, p = (e^{rΔt} - d)/(u - d)</div>
                                    <small>u = facteur de hausse, d = facteur de baisse, p = probabilité risque-neutre.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title tooltip-custom" title="Méthode de résolution numérique des EDP">Différences finies 📐</div>
                                    <div class="term-def">Résolution des EDP lorsque les solutions fermées n'existent pas.</div>
                                    <small>Discrétisation de l'équation de Black-Scholes sur une grille.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== SECTION 3 : LES GRECQUES (VERSION AMÉLIORÉE) ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-activity"></i> 3. Les Grecques – Gestion des risques</h4>
                    <div class="grecques-container">
                        <div class="grecque-card">
                            <div class="grecque-symbole">Δ (Delta)</div>
                            <div class="grecque-nom">Sensibilité au sous-jacent</div>
                            <div class="grecque-def">∂V/∂S – Variation du prix de l'option quand le sous-jacent varie de 1€.<br>Δ Call = N(d1) ∈ [0,1] ; Δ Put = N(d1)-1 ∈ [-1,0]</div>
                            <div class="grecque-formule">Formule : Δ = ∂V/∂S</div>
                            <small class="text-muted">Couverture : vendre Δ actions par option vendue.</small>
                        </div>
                        <div class="grecque-card">
                            <div class="grecque-symbole">Γ (Gamma)</div>
                            <div class="grecque-nom">Risque de courbure</div>
                            <div class="grecque-def">∂²V/∂S² – Variation du Delta quand le sous-jacent varie.<br>Maximale pour les options à la monnaie (ATM).</div>
                            <div class="grecque-formule">Formule : Γ = N'(d1)/(Sσ√T)</div>
                            <small class="text-muted">Plus élevé → risque de convexité important.</small>
                        </div>
                        <div class="grecque-card">
                            <div class="grecque-symbole">ν (Vega)</div>
                            <div class="grecque-nom">Sensibilité à la volatilité</div>
                            <div class="grecque-def">∂V/∂σ – Variation du prix de l'option quand la volatilité augmente de 1%.</div>
                            <div class="grecque-formule">Formule : ν = S√T·N'(d1)</div>
                            <small class="text-muted">Crucial pour le provisionnement IFRS 9.</small>
                        </div>
                        <div class="grecque-card">
                            <div class="grecque-symbole">Θ (Theta)</div>
                            <div class="grecque-nom">Décroissance temporelle</div>
                            <div class="grecque-def">∂V/∂t – Perte de valeur quotidienne de l'option (time decay).</div>
                            <div class="grecque-formule">Θ Call ≈ - (SσN'(d1))/(2√T) - rKe^{-rT}N(d2)</div>
                            <small class="text-muted">Négatif pour l'acheteur (perte chaque jour).</small>
                        </div>
                        <div class="grecque-card">
                            <div class="grecque-symbole">ρ (Rho)</div>
                            <div class="grecque-nom">Sensibilité aux taux</div>
                            <div class="grecque-def">∂V/∂r – Variation du prix de l'option quand les taux augmentent de 1%.</div>
                            <div class="grecque-formule">ρ Call = KTe^{-rT}N(d2)</div>
                            <small class="text-muted">Important pour options long terme (>1 an).</small>
                        </div>
                        <div class="grecque-card">
                            <div class="grecque-symbole">📊 Sharpe</div>
                            <div class="grecque-nom">Ratio rendement/risque</div>
                            <div class="grecque-def">(Rendement du portefeuille - Taux sans risque) / Volatilité</div>
                            <div class="grecque-formule">Sharpe = (Rp - Rf) / σp</div>
                            <small class="text-muted">Sharpe > 1 → bon compromis risque/rendement.</small>
                        </div>
                    </div>

                    <!-- ==================== SECTION 4 : SIMULATEUR MONTE-CARLO ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-sliders"></i> 4. Simulateur Monte-Carlo – Option Call européenne</h4>
                    <div class="card bg-light">
                        <div class="card-body">
                            <form method="post" id="monteCarloForm">
                                <div class="row">
                                    <div class="col-md-3"><label class="tooltip-custom" title="Prix actuel de l'actif sous-jacent">Prix initial S0 (€)</label><input type="number" name="s0" class="form-control" value="100" step="1" required></div>
                                    <div class="col-md-3"><label class="tooltip-custom" title="Prix auquel on peut acheter (Call) ou vendre (Put)">Prix d'exercice K (€)</label><input type="number" name="k" class="form-control" value="105" step="1" required></div>
                                    <div class="col-md-3"><label class="tooltip-custom" title="Taux des obligations d'État à 10 ans">Taux sans risque r (%)</label><input type="number" step="0.1" name="r" class="form-control" value="5" required></div>
                                    <div class="col-md-3"><label class="tooltip-custom" title="Mesure de l'incertitude du prix (écart-type annualisé)">Volatilité σ (%)</label><input type="number" step="1" name="sigma" class="form-control" value="20" required></div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-4"><label class="tooltip-custom" title="Temps jusqu'à l'échéance de l'option">Maturité T (années)</label><input type="number" step="0.1" name="t" class="form-control" value="1" required></div>
                                    <div class="col-md-4"><label class="tooltip-custom" title="Nombre de trajectoires simulées (plus = précision)">Nombre de simulations</label><input type="number" name="n_sim" class="form-control" value="10000" required></div>
                                    <div class="col-md-4"><label class="tooltip-custom" title="Nombre de pas de temps (discrétisation)">Nombre de pas</label><input type="number" name="n_steps" class="form-control" value="252" required></div>
                                </div>
                                <button type="submit" name="simuler_mc" class="btn btn-primary mt-3">Lancer la simulation</button>
                            </form>

                            <?php
                            function boxMuller() {
                                $u1 = mt_rand() / mt_getrandmax();
                                $u2 = mt_rand() / mt_getrandmax();
                                return sqrt(-2 * log($u1)) * cos(2 * M_PI * $u2);
                            }
                            
                            function normalCDF($x) {
                                return (1.0 + erf($x / sqrt(2.0))) / 2.0;
                            }
                            
                            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simuler_mc'])) {
                                $S0 = (float)$_POST['s0'];
                                $K = (float)$_POST['k'];
                                $r = (float)$_POST['r'] / 100;
                                $sigma = (float)$_POST['sigma'] / 100;
                                $T = (float)$_POST['t'];
                                $nSim = min(50000, (int)$_POST['n_sim']);
                                $nSteps = (int)$_POST['n_steps'];
                                
                                $dt = $T / $nSteps;
                                $mu = $r - 0.5 * $sigma * $sigma;
                                $sumPayoff = 0;
                                $trajectoires = [];
                                
                                for ($i = 0; $i < $nSim; $i++) {
                                    $S = $S0;
                                    for ($j = 0; $j < $nSteps; $j++) {
                                        $epsilon = boxMuller();
                                        $S = $S * exp($mu * $dt + $sigma * sqrt($dt) * $epsilon);
                                    }
                                    $payoff = max($S - $K, 0);
                                    $sumPayoff += $payoff;
                                    if ($i < 100) $trajectoires[] = $S;
                                }
                                $prixOption = exp(-$r * $T) * ($sumPayoff / $nSim);
                                $d1 = (log($S0 / $K) + ($r + $sigma * $sigma / 2) * $T) / ($sigma * sqrt($T));
                                $d2 = $d1 - $sigma * sqrt($T);
                                $bsPrice = $S0 * normalCDF($d1) - $K * exp(-$r * $T) * normalCDF($d2);
                                
                                $ecart_type = sqrt($sumPayoff / $nSim - pow($prixOption / exp(-$r * $T), 2));
                                $erreur_std = 1.96 * $ecart_type / sqrt($nSim);
                                $borne_inf = $prixOption - $erreur_std;
                                $borne_sup = $prixOption + $erreur_std;
                                
                                echo <<<HTML
                                <div class="alert alert-success mt-4">
                                    <strong>📊 Résultats de la simulation Monte-Carlo :</strong><br>
                                    <ul>
                                        <li><strong>Prix de l'option Call européenne</strong> : <span class="badge bg-primary fs-6">{$prixOption} €</span></li>
                                        <li><strong>Prix Black-Scholes théorique</strong> : {$bsPrice} €</li>
                                        <li><strong>Intervalle de confiance 95%</strong> : [{$borne_inf} € ; {$borne_sup} €]</li>
                                        <li><small>Simulations : {$nSim} | Pas : {$nSteps}</small></li>
                                    </ul>
                                    <p class="mt-2 text-muted">💡 <strong>Interprétation :</strong> Une option Call donne le droit d'acheter l'action à €{$K}. Avec un prix calculé de €{$prixOption}, il est rentable d'acheter cette option si vous pensez que l'action dépassera €" . round($K + $prixOption, 2) . " à échéance.</p>
                                </div>
                                <canvas id="histogramChart" width="400" height="200"></canvas>
                                <script>
                                    const histCtx = document.getElementById('histogramChart').getContext('2d');
                                    new Chart(histCtx, {
                                        type: 'bar',
                                        data: {
                                            labels: " . json_encode(range(1, min(20, count($trajectoires)))) . ",
                                            datasets: [{
                                                label: 'Prix finaux simulés (échantillon)',
                                                data: " . json_encode(array_slice($trajectoires, 0, 20)) . ",
                                                backgroundColor: '#0d6efd'
                                            }]
                                        },
                                        options: { responsive: true }
                                    });
                                </script>
HTML;
                            }
                            ?>
                        </div>
                    </div>

                    <!-- ==================== SECTION 5 : COMPTABILITÉ DE COUVERTURE IFRS 9 ==================== -->
                    <h4 class="section-title mt-4"><i class="bi bi-file-earmark-check"></i> 5. Comptabilité de couverture (IFRS 9 / IAS 39)</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title tooltip-custom" title="Dérivé théorique servant de benchmark">Dérivé hypothétique 🔄</div>
                                    <div class="term-def">Création d'un dérivé théorique miroir des conditions de l'élément couvert pour mesurer l'inefficacité.</div>
                                    <div class="formula mt-2">Inefficacité = |ΔFV<sub>réel</sub> - ΔFV<sub>hypothétique</sub>|</div>
                                    <small>Si inefficacité > 20% → la couverture n'est pas efficace selon IFRS 9.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title tooltip-custom" title="Types de couverture selon IFRS 9">Fair Value Hedge / Cash Flow Hedge 📋</div>
                                    <div class="term-def">Couverture de juste valeur (impact en P&L) vs couverture de flux de trésorerie (impact en OCI).</div>
                                    <small>Fair Value Hedge : protège contre variation de juste valeur. Cash Flow Hedge : protège contre variation des flux futurs.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title tooltip-custom" title="Test avant couverture">Test d'efficacité prospective 📈</div>
                                    <div class="term-def">Vérification ex-ante que la relation de couverture sera efficace (régression linéaire, R² > 0.8).</div>
                                    <small>Doit être documenté avant la mise en place de la couverture.</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card card-term">
                                <div class="card-body">
                                    <div class="term-title tooltip-custom" title="Test après couverture">Test d'efficacité rétrospective 📉</div>
                                    <div class="term-def">Vérification ex-post sur une plage de 80%-125% (dollar offset method).</div>
                                    <small>Le ratio de couverture doit être compris entre 0,8 et 1,25.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-4">
                        <i class="bi bi-info-circle"></i> <strong>Note d'expert :</strong> L'enjeu majeur n'est pas seulement le calcul du juste prix (fair value), mais la <strong>traçabilité des modèles</strong>. Toute simulation doit être réplicable et documentée pour satisfaire aux exigences des auditeurs externes et des régulateurs (BCE, AMF, ACPR).
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>

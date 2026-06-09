<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$page_title = "Analyse bilantaire – Lecture verticale / horizontale & Z-Score d'Altman";
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
        .alert-risk { border-left: 5px solid #dc3545; }
        .alert-warning { border-left: 5px solid #ffc107; }
        .alert-safe { border-left: 5px solid #28a745; }
        .ratio-card { background: #f8f9fa; padding: 15px; border-radius: 10px; margin-bottom: 15px; }
    </style>
</head>
<body>
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2><i class="bi bi-bar-chart-steps"></i> Analyse bilantaire – Lecture verticale, horizontale & Z-Score d'Altman</h2>
                    <p>Diagnostic structurel, analyse dynamique et prédiction de faillite</p>
                </div>
                <div class="card-body">

                    <!-- ==================== SECTION 1 : SAISIE DU BILAN ==================== -->
                    <h4 class="section-title"><i class="bi bi-pencil-square"></i> 1. Saisie des données bilantaires</h4>
                    <form method="post" id="bilanForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label>Année N</label>
                                <input type="number" name="annee_n" class="form-control" value="<?= date('Y') ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label>Année N-1</label>
                                <input type="number" name="annee_n1" class="form-control" value="<?= date('Y')-1 ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label>Actif immobilisé (k€)</label>
                                <input type="number" name="actif_immo" class="form-control" value="800" required>
                            </div>
                            <div class="col-md-3">
                                <label>Actif circulant (k€)</label>
                                <input type="number" name="actif_circ" class="form-control" value="1200" required>
                            </div>
                            <div class="col-md-3">
                                <label>Trésorerie actif (k€)</label>
                                <input type="number" name="treso_actif" class="form-control" value="80" required>
                            </div>
                            <div class="col-md-3">
                                <label>Capitaux propres (k€)</label>
                                <input type="number" name="cp" class="form-control" value="800" required>
                            </div>
                            <div class="col-md-3">
                                <label>Dettes financières (k€)</label>
                                <input type="number" name="dettes_fin" class="form-control" value="600" required>
                            </div>
                            <div class="col-md-3">
                                <label>Dettes fournisseurs (k€)</label>
                                <input type="number" name="dettes_fourn" class="form-control" value="400" required>
                            </div>
                            <div class="col-md-3">
                                <label>EBIT (Résultat opérationnel) (k€)</label>
                                <input type="number" name="ebit" class="form-control" value="350" required>
                            </div>
                            <div class="col-md-3">
                                <label>Chiffre d'affaires (k€)</label>
                                <input type="number" name="ca" class="form-control" value="2500" required>
                            </div>
                            <div class="col-md-3">
                                <label>Résultat non distribué cumulé (k€)</label>
                                <input type="number" name="resultat_cumule" class="form-control" value="200" required>
                            </div>
                            <div class="col-md-3">
                                <label>Valeur de marché des capitaux propres (k€)</label>
                                <input type="number" name="valeur_marche_cp" class="form-control" value="900" required>
                            </div>
                            <div class="col-md-12 mt-2">
                                <button type="submit" name="analyser_bilan" class="btn btn-primary">Analyser le bilan (vertical/horizontal)</button>
                                <button type="submit" name="calculer_zscore" class="btn btn-danger">Calculer le Z-Score d'Altman</button>
                                <button type="button" class="btn btn-info" onclick="simulerStress()">Simuler stress test (-10% CA)</button>
                            </div>
                        </div>
                    </form>

                    <?php
                    // ==================== TRAITEMENT DES DONNÉES ====================
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $annee_n = (int)$_POST['annee_n'];
                        $annee_n1 = (int)$_POST['annee_n1'];
                        $actif_immo = (float)$_POST['actif_immo'];
                        $actif_circ = (float)$_POST['actif_circ'];
                        $treso_actif = (float)$_POST['treso_actif'];
                        $cp = (float)$_POST['cp'];
                        $dettes_fin = (float)$_POST['dettes_fin'];
                        $dettes_fourn = (float)$_POST['dettes_fourn'];
                        $ebit = (float)$_POST['ebit'];
                        $ca = (float)$_POST['ca'];
                        $resultat_cumule = (float)$_POST['resultat_cumule'];
                        $valeur_marche_cp = (float)$_POST['valeur_marche_cp'];

                        $total_actif = $actif_immo + $actif_circ + $treso_actif;
                        $total_passif = $cp + $dettes_fin + $dettes_fourn;
                        
                        // Analyse verticale (année N)
                        $poids_cp = ($total_passif > 0) ? ($cp / $total_passif) * 100 : 0;
                        $poids_dettes_fin = ($total_passif > 0) ? ($dettes_fin / $total_passif) * 100 : 0;
                        $poids_dettes_fourn = ($total_passif > 0) ? ($dettes_fourn / $total_passif) * 100 : 0;
                        $poids_actif_immo = ($total_actif > 0) ? ($actif_immo / $total_actif) * 100 : 0;
                        $poids_actif_circ = ($total_actif > 0) ? ($actif_circ / $total_actif) * 100 : 0;
                        
                        // Simulation données N-1 (pour démonstration)
                        $ca_n1 = $ca * 0.9;
                        $cp_n1 = $cp * 0.85;
                        $ebit_n1 = $ebit * 0.8;
                        $total_actif_n1 = $total_actif * 0.85;
                        
                        // Analyse horizontale
                        $variation_ca = (($ca - $ca_n1) / $ca_n1) * 100;
                        $variation_cp = (($cp - $cp_n1) / $cp_n1) * 100;
                        $variation_ebit = (($ebit - $ebit_n1) / $ebit_n1) * 100;
                        $variation_actif = (($total_actif - $total_actif_n1) / $total_actif_n1) * 100;

                        if (isset($_POST['analyser_bilan'])) {
                            echo <<<HTML
                            <h4 class="mt-4 section-title">📊 2. Analyse verticale (structure au {$annee_n})</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card ratio-card">
                                        <h5>🔹 À l'actif</h5>
                                        <p><strong>Actif immobilisé</strong> : " . round($poids_actif_immo, 2) . "% du total actif</p>
                                        <p><strong>Actif circulant</strong> : " . round($poids_actif_circ, 2) . "% du total actif</p>
                                        <p class="text-muted">Commentaire : " . ($poids_actif_immo > 60 ? "Structure capitalistique lourde" : "Structure flexible") . "</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card ratio-card">
                                        <h5>🔹 Au passif</h5>
                                        <p><strong>Capitaux propres</strong> : " . round($poids_cp, 2) . "% (indépendance financière)</p>
                                        <p><strong>Dettes financières</strong> : " . round($poids_dettes_fin, 2) . "%</p>
                                        <p><strong>Dettes fournisseurs</strong> : " . round($poids_dettes_fourn, 2) . "%</p>
                                        <p class="text-muted">Commentaire : " . ($poids_cp > 50 ? "Très bonne autonomie" : ($poids_cp > 30 ? "Autonomie correcte" : "Dépendance financière élevée")) . "</p>
                                    </div>
                                </div>
                            </div>

                            <h4 class="mt-4 section-title">📈 3. Analyse horizontale (évolution {$annee_n1} → {$annee_n})</h4>
                            <div class="row">
                                <div class="col-md-3"><div class="card text-center p-2"><strong>Chiffre d'affaires</strong><br>" . round($variation_ca, 2) . "%</div></div>
                                <div class="col-md-3"><div class="card text-center p-2"><strong>Capitaux propres</strong><br>" . round($variation_cp, 2) . "%</div></div>
                                <div class="col-md-3"><div class="card text-center p-2"><strong>EBIT</strong><br>" . round($variation_ebit, 2) . "%</div></div>
                                <div class="col-md-3"><div class="card text-center p-2"><strong>Total actif</strong><br>" . round($variation_actif, 2) . "%</div></div>
                            </div>
HTML;
                        }

                        if (isset($_POST['calculer_zscore'])) {
                            // Calcul du Z-Score d'Altman
                            $fonds_roulement = $actif_circ - $dettes_fourn;
                            $dettes_totales = $dettes_fin + $dettes_fourn;
                            
                            $x1 = $total_actif > 0 ? $fonds_roulement / $total_actif : 0;
                            $x2 = $total_actif > 0 ? $resultat_cumule / $total_actif : 0;
                            $x3 = $total_actif > 0 ? $ebit / $total_actif : 0;
                            $x4 = $dettes_totales > 0 ? $valeur_marche_cp / $dettes_totales : 0;
                            $x5 = $total_actif > 0 ? $ca / $total_actif : 0;
                            
                            $zscore = (1.2 * $x1) + (1.4 * $x2) + (3.3 * $x3) + (0.6 * $x4) + (1.0 * $x5);
                            
                            if ($zscore > 2.99) {
                                $interpretation = "Zone Sûre (Safe Zone) – Risque de faillite très faible";
                                $classe = "alert-safe";
                            } elseif ($zscore > 1.81) {
                                $interpretation = "Zone Grise (Grey Zone) – Situation à surveiller";
                                $classe = "alert-warning";
                            } else {
                                $interpretation = "Zone de détresse (Distress Zone) – Risque élevé de défaillance";
                                $classe = "alert-risk";
                            }
                            
                            echo <<<HTML
                            <h4 class="mt-4 section-title">⚠️ 4. Z-Score d'Altman (Prédiction de faillite)</h4>
                            <div class="alert {$classe}">
                                <strong>Z-Score calculé : " . round($zscore, 3) . "</strong><br>
                                Interprétation : {$interpretation}<br>
                                <hr>
                                <small>X1 (Liquidité) : " . round($x1, 3) . " | X2 (Rentabilité cumulée) : " . round($x2, 3) . "<br>
                                X3 (Productivité actifs) : " . round($x3, 3) . " | X4 (Solvabilité) : " . round($x4, 3) . " | X5 (Rotation actifs) : " . round($x5, 3) . "</small>
                            </div>
HTML;
                            
                            // Sauvegarde dans historique (simulée)
                            $pdo->prepare("INSERT INTO historique_zscore (annee, zscore, interpretation, x1, x2, x3, x4, x5) VALUES (?, ?, ?, ?, ?, ?, ?, ?)")
                                ->execute([$annee_n, $zscore, $interpretation, $x1, $x2, $x3, $x4, $x5]);
                        }
                    }
                    ?>

                    <!-- ==================== SECTION 5 : HISTORIQUE ET GRAPHIQUE ==================== -->
                    <h4 class="mt-5 section-title"><i class="bi bi-graph-up"></i> 5. Évolution historique du Z-Score</h4>
                    <canvas id="zscoreChart" width="400" height="200"></canvas>

                    <div class="alert alert-info mt-4">
                        <i class="bi bi-info-circle"></i> <strong>Conseil d'expert :</strong> La lecture verticale seule est une "photographie" qui peut être trompeuse. L'analyse horizontale est indispensable pour valider la pertinence de la structure. Le Z-Score d'Altman permet une anticipation du risque de défaillance sur 2 ans.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Graphique d'évolution du Z-Score (données historiques)
    const ctx = document.getElementById('zscoreChart').getContext('2d');
    const labels = <?php 
        $stmt = $pdo->query("SELECT annee, zscore FROM historique_zscore ORDER BY annee ASC");
        $annees = []; $scores = [];
        while ($row = $stmt->fetch()) {
            $annees[] = $row['annee'];
            $scores[] = $row['zscore'];
        }
        echo json_encode($annees);
    ?>;
    const scores = <?php echo json_encode($scores); ?>;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Z-Score d\'Altman',
                data: scores,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                tooltip: { callbacks: { label: (ctx) => 'Z-Score: ' + ctx.raw.toFixed(3) } }
            },
            scales: { y: { title: { display: true, text: 'Z-Score' }, min: 0 } }
        }
    });
    
    function simulerStress() {
        alert("Module de stress test : simulation d'une baisse du CA de 10% impactant le Z-Score. (Fonctionnalité en cours de développement avancé)");
    }
</script>
<?php include 'inc_footer.php'; ?>

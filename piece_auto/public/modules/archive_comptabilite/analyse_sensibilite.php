<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Analyse de sensibilité - Méthode de Monte-Carlo";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];
$simulations = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $investissement = (float)$_POST['investissement'];
    $taux = (float)$_POST['taux_actualisation'];
    $duree = (int)$_POST['duree'];
    $nb_simulations = (int)$_POST['nb_simulations'];
    
    $ca_min = (float)$_POST['ca_min'];
    $ca_max = (float)$_POST['ca_max'];
    $charges_min = (float)$_POST['charges_min'];
    $charges_max = (float)$_POST['charges_max'];
    
    $vans = [];
    $tris = [];
    
    for($s = 0; $s < $nb_simulations; $s++) {
        $ca_simule = $ca_min + (($ca_max - $ca_min) * mt_rand() / mt_getrandmax());
        $charges_simule = $charges_min + (($charges_max - $charges_min) * mt_rand() / mt_getrandmax());
        $flux_annuel = $ca_simule - $charges_simule;
        
        $van = -$investissement;
        $tri_test = 0;
        
        for($i = 1; $i <= $duree; $i++) {
            $van += $flux_annuel / pow(1 + ($taux/100), $i);
        }
        
        // TRI simplifié
        for($t = 1; $t <= 50; $t++) {
            $van_test = -$investissement;
            for($i = 1; $i <= $duree; $i++) {
                $van_test += $flux_annuel / pow(1 + ($t/100), $i);
            }
            if($van_test <= 0) {
                $tri_test = $t - 1;
                break;
            }
        }
        
        $vans[] = $van;
        $tris[] = $tri_test;
        
        // Sauvegarde
        $stmt = $pdo->prepare("INSERT INTO SIMULATIONS_MONTE_CARLO (projet_id, iteration, van_simulee, tri_simule) VALUES (0, ?, ?, ?)");
        $stmt->execute([$s+1, $van, $tri_test]);
    }
    
    $van_moyenne = array_sum($vans) / $nb_simulations;
    $van_std = sqrt(array_sum(array_map(function($x) use ($van_moyenne) { return pow($x - $van_moyenne, 2); }, $vans)) / $nb_simulations);
    $tri_moyen = array_sum($tris) / $nb_simulations;
    
    $van_positives = count(array_filter($vans, function($v) { return $v > 0; }));
    $probabilite_succes = ($van_positives / $nb_simulations) * 100;
    
    $resultats = [
        'van_moyenne' => $van_moyenne,
        'van_std' => $van_std,
        'tri_moyen' => $tri_moyen,
        'probabilite_succes' => $probabilite_succes,
        'nb_simulations' => $nb_simulations
    ];
    
    $message = "✅ Simulation Monte-Carlo terminée - $nb_simulations itérations";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Analyse de sensibilité - Méthode Monte-Carlo</h5>
                <small>Simulation des risques sur CA et charges</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Paramètres de la simulation</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-4"><label>Investissement (F)</label><input type="number" name="investissement" class="form-control" value="10000000" step="1000000" required></div>
                                    <div class="col-md-4"><label>Taux actualisation (%)</label><input type="number" name="taux_actualisation" class="form-control" value="12" step="0.5" required></div>
                                    <div class="col-md-4"><label>Durée (ans)</label><input type="number" name="duree" class="form-control" value="5" required></div>
                                    <div class="col-md-4"><label>Nb simulations</label><input type="number" name="nb_simulations" class="form-control" value="1000" step="100" required></div>
                                    <div class="col-md-8"><label>CA annuel (min-max en F)</label>
                                        <div class="row"><div class="col-6"><input type="number" name="ca_min" class="form-control" value="3000000" required></div>
                                        <div class="col-6"><input type="number" name="ca_max" class="form-control" value="5000000" required></div></div>
                                    </div>
                                    <div class="col-md-8"><label>Charges annuelles (min-max en F)</label>
                                        <div class="row"><div class="col-6"><input type="number" name="charges_min" class="form-control" value="2000000" required></div>
                                        <div class="col-6"><input type="number" name="charges_max" class="form-control" value="3000000" required></div></div>
                                    </div>
                                    <div class="col-12"><button type="submit" class="btn-omega w-100">Lancer la simulation</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if(!empty($resultats)): ?>
                        <div class="card">
                            <div class="card-header bg-success text-white">📊 Résultats de la simulation</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="alert alert-info text-center">
                                            <h6>VAN moyenne</h6>
                                            <h4 class="text-info"><?= number_format($resultats['van_moyenne'], 0, ',', ' ') ?> F</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-warning text-center">
                                            <h6>Écart-type VAN</h6>
                                            <h4><?= number_format($resultats['van_std'], 0, ',', ' ') ?> F</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-success text-center">
                                            <h6>TRI moyen</h6>
                                            <h4><?= number_format($resultats['tri_moyen'], 2) ?>%</h4>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-danger text-center">
                                            <h6>Probabilité de succès</h6>
                                            <h4><?= number_format($resultats['probabilite_succes'], 2) ?>%</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="progress mt-3">
                                    <div class="progress-bar bg-success" style="width: <?= $resultats['probabilite_succes'] ?>%">
                                        <?= number_format($resultats['probabilite_succes'], 1) ?>%
                                    </div>
                                </div>
                                <div class="alert alert-secondary mt-3">
                                    <strong>📈 Interprétation :</strong><br>
                                    Basé sur <?= $resultats['nb_simulations'] ?> scénarios aléatoires.<br>
                                    <?php if($resultats['probabilite_succes'] > 70): ?>
                                        ✅ Projet très robuste aux variations du marché
                                    <?php elseif($resultats['probabilite_succes'] > 50): ?>
                                        ⚠️ Projet sensible - surveillance recommandée
                                    <?php else: ?>
                                        ❌ Projet risqué - reconsidérer la décision
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>

<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Investissement en avenir aléatoire";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$esperance = null;
$variance = null;
$ecart_type = null;

$projets = $pdo->query("SELECT * FROM PROJETS_INVESTISSEMENT ORDER BY code")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projet_id = (int)$_POST['projet_id'];
    $investissement = (float)$_POST['investissement'];
    $duree = (int)$_POST['duree'];
    $taux = (float)$_POST['taux_actualisation'];
    
    // Récupération des flux par scénario
    $flux_opt = array_sum([(float)$_POST['opt_annee1'], (float)$_POST['opt_annee2'], (float)$_POST['opt_annee3'], (float)$_POST['opt_annee4'], (float)$_POST['opt_annee5']]);
    $flux_real = array_sum([(float)$_POST['real_annee1'], (float)$_POST['real_annee2'], (float)$_POST['real_annee3'], (float)$_POST['real_annee4'], (float)$_POST['real_annee5']]);
    $flux_pess = array_sum([(float)$_POST['pess_annee1'], (float)$_POST['pess_annee2'], (float)$_POST['pess_annee3'], (float)$_POST['pess_annee4'], (float)$_POST['pess_annee5']]);
    
    $prob_opt = (float)$_POST['prob_opt'] / 100;
    $prob_real = (float)$_POST['prob_real'] / 100;
    $prob_pess = (float)$_POST['prob_pess'] / 100;
    
    // Calcul de l'espérance de VAN
    $van_opt = -$investissement;
    $van_real = -$investissement;
    $van_pess = -$investissement;
    
    for($i = 1; $i <= $duree; $i++) {
        $coef = pow(1 + $taux/100, $i);
        $van_opt += (float)$_POST["opt_annee$i"] / $coef;
        $van_real += (float)$_POST["real_annee$i"] / $coef;
        $van_pess += (float)$_POST["pess_annee$i"] / $coef;
    }
    
    $esperance = $van_opt * $prob_opt + $van_real * $prob_real + $van_pess * $prob_pess;
    
    // Calcul de la variance
    $variance = $prob_opt * pow($van_opt - $esperance, 2) + 
                $prob_real * pow($van_real - $esperance, 2) + 
                $prob_pess * pow($van_pess - $esperance, 2);
    $ecart_type = sqrt($variance);
    
    // Coefficient de variation (risque relatif)
    $coeff_variation = $esperance != 0 ? abs($ecart_type / $esperance) : 0;
    
    // Sauvegarde des scénarios
    $stmt = $pdo->prepare("INSERT INTO SCENARIOS_INVESTISSEMENT (projet_id, scenario, probabilite, flux_annee1, flux_annee2, flux_annee3, flux_annee4, flux_annee5) VALUES 
        (?, 'OPTIMISTE', ?, ?, ?, ?, ?, ?),
        (?, 'REALISTE', ?, ?, ?, ?, ?, ?),
        (?, 'PESSIMISTE', ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $projet_id, $prob_opt*100, $_POST['opt_annee1'], $_POST['opt_annee2'], $_POST['opt_annee3'], $_POST['opt_annee4'], $_POST['opt_annee5'],
        $projet_id, $prob_real*100, $_POST['real_annee1'], $_POST['real_annee2'], $_POST['real_annee3'], $_POST['real_annee4'], $_POST['real_annee5'],
        $projet_id, $prob_pess*100, $_POST['pess_annee1'], $_POST['pess_annee2'], $_POST['pess_annee3'], $_POST['pess_annee4'], $_POST['pess_annee5']
    ]);
    
    $message = "✅ Analyse de risque calculée";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Investissement en avenir aléatoire</h5>
                <small>Analyse espérance-variance - Scénarios optimiste, réaliste, pessimiste</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <strong>📊 Méthodologie :</strong><br>
                    • Espérance de VAN = Σ (VAN_scenario × Probabilité)<br>
                    • Variance = Σ Probabilité × (VAN_scenario - Espérance)²<br>
                    • Écart-type = √Variance<br>
                    • Coefficient de variation = Écart-type / |Espérance|
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <form method="POST" id="risqueForm" class="row g-3">
                            <div class="col-md-3"><label>Projet</label>
                                <select name="projet_id" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    <?php foreach($projets as $p): ?>
                                        <option value="<?= $p['id'] ?>"><?= $p['code'] ?> - <?= $p['libelle'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3"><label>Investissement (F)</label>
                                <input type="number" name="investissement" class="form-control" step="100000" required></div>
                            <div class="col-md-3"><label>Durée (ans)</label>
                                <input type="number" name="duree" class="form-control" value="5" required></div>
                            <div class="col-md-3"><label>Taux actualisation (%)</label>
                                <input type="number" name="taux_actualisation" class="form-control" value="12" step="0.5" required></div>
                            
                            <div class="col-md-12"><h6 class="mt-3">📈 Scénarios et probabilités</h6></div>
                            
                            <div class="col-md-4">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">Optimiste (Prob: <input type="number" name="prob_opt" class="form-control form-control-sm d-inline w-25" value="25">%)</div>
                                    <div class="card-body">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                        <div class="mb-1"><label>Année <?= $i ?></label><input type="number" name="opt_annee<?= $i ?>" class="form-control form-control-sm" step="100000" value="0"></div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">Réaliste (Prob: <input type="number" name="prob_real" class="form-control form-control-sm d-inline w-25" value="50">%)</div>
                                    <div class="card-body">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                        <div class="mb-1"><label>Année <?= $i ?></label><input type="number" name="real_annee<?= $i ?>" class="form-control form-control-sm" step="100000" value="0"></div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white">Pessimiste (Prob: <input type="number" name="prob_pess" class="form-control form-control-sm d-inline w-25" value="25">%)</div>
                                    <div class="card-body">
                                        <?php for($i=1; $i<=5; $i++): ?>
                                        <div class="mb-1"><label>Année <?= $i ?></label><input type="number" name="pess_annee<?= $i ?>" class="form-control form-control-sm" step="100000" value="0"></div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 text-center"><button type="submit" class="btn-omega mt-3">Analyser le risque</button></div>
                        </form>
                    </div>
                </div>

                <?php if($esperance !== null): ?>
                <div class="card mt-4">
                    <div class="card-header bg-secondary text-white">📊 Résultats de l'analyse espérance-variance</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="card text-center bg-info text-white">
                                    <div class="card-body">
                                        <h4><?= number_format($esperance, 0, ',', ' ') ?> F</h4>
                                        <small>Espérance de VAN</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center bg-warning text-dark">
                                    <div class="card-body">
                                        <h4><?= number_format($variance, 0, ',', ' ') ?> F²</h4>
                                        <small>Variance</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center bg-danger text-white">
                                    <div class="card-body">
                                        <h4><?= number_format($ecart_type, 0, ',', ' ') ?> F</h4>
                                        <small>Écart-type (risque)</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card text-center bg-success text-white">
                                    <div class="card-body">
                                        <h4><?= number_format($coeff_variation, 4) ?></h4>
                                        <small>Coefficient de variation</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert <?= $esperance > 0 ? 'alert-success' : 'alert-danger' ?> mt-3">
                            <?php if($esperance > 0): ?>
                                ✅ Projet rentable en espérance (VAN positive)
                            <?php else: ?>
                                ❌ Projet non rentable en espérance (VAN négative)
                            <?php endif; ?>
                            <?php if($coeff_variation < 0.5): ?>
                                Risque relatif faible
                            <?php elseif($coeff_variation < 1): ?>
                                Risque relatif modéré
                            <?php else: ?>
                                Risque relatif élevé
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>

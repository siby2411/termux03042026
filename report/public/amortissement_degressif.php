<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Amortissement Dégressif - Optimisation fiscale";
$page_icon = "graph-down";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats_lineaire = [];
$resultats_degressif = [];
$economie_impot = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valeur_brute = (float)$_POST['valeur_brute'];
    $duree = (int)$_POST['duree'];
    $taux_is = (float)$_POST['taux_is'];
    $coefficient = $duree <= 4 ? 1.5 : ($duree <= 6 ? 2 : 2.5);
    $taux_lineaire = 100 / $duree;
    $taux_degressif = $taux_lineaire * $coefficient;
    
    // Calcul linéaire
    $annuite_lineaire = $valeur_brute * $taux_lineaire / 100;
    for ($i = 1; $i <= $duree; $i++) {
        $resultats_lineaire[$i] = $annuite_lineaire;
    }
    
    // Calcul dégressif
    $vnc = $valeur_brute;
    for ($i = 1; $i <= $duree; $i++) {
        $annuite = $vnc * $taux_degressif / 100;
        if ($i == $duree) $annuite = $vnc;
        $resultats_degressif[$i] = $annuite;
        $vnc -= $annuite;
    }
    
    // Économie d'impôt
    for ($i = 1; $i <= $duree; $i++) {
        $difference = $resultats_degressif[$i] - $resultats_lineaire[$i];
        $economie_impot += $difference * ($taux_is / 100);
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-down"></i> Amortissement Dégressif - Optimisation fiscale</h5>
                <small>Comparaison linéaire vs dégressif et impact fiscal</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Explication technique :</strong><br>
                    • <strong>Amortissement linéaire</strong> : Annuité constante = Valeur brute × Taux / 100<br>
                    • <strong>Amortissement dégressif</strong> : Annuité décroissante = VNC × Taux dégressif / 100<br>
                    • <strong>Coefficient dégressif</strong> : 1.5 (3-4 ans), 2 (5-6 ans), 2.5 (7+ ans)<br>
                    • <strong>Avantage fiscal</strong> : Les premières années, l'annuité dégressive est plus élevée → économie d'IS
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">⚙️ Paramètres</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-12">
                                        <label>Valeur brute (FCFA)</label>
                                        <input type="number" name="valeur_brute" class="form-control" value="10000000" step="100000" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label>Durée (années)</label>
                                        <select name="duree" class="form-select" required>
                                            <option value="3">3 ans (coeff 1.5)</option>
                                            <option value="5" selected>5 ans (coeff 2)</option>
                                            <option value="10">10 ans (coeff 2.5)</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label>Taux IS (%)</label>
                                        <select name="taux_is" class="form-select" required>
                                            <option value="25" selected>25% (Sénégal)</option>
                                            <option value="30">30%</option>
                                            <option value="20">20%</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-omega w-100">Comparer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-success text-white">📊 Comparaison des méthodes</div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <thead class="table-dark">
                                            <tr><th>Année</th><th>Linéaire (F)</th><th>Dégressif (F)</th><th>Différence (F)</th><th>Économie IS (F)</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php if(!empty($resultats_lineaire)): 
                                                $cumul_economie = 0;
                                                for($i=1; $i<=$duree; $i++): 
                                                    $diff = $resultats_degressif[$i] - $resultats_lineaire[$i];
                                                    $economie = $diff * ($taux_is / 100);
                                                    $cumul_economie += $economie;
                                            ?>
                                            <tr>
                                                <td class="text-center"><?= $i ?></td>
                                                <td class="text-end"><?= number_format($resultats_lineaire[$i],0,',',' ') ?> F</td>
                                                <td class="text-end"><?= number_format($resultats_degressif[$i],0,',',' ') ?> F</td>
                                                <td class="text-end <?= $diff > 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($diff,0,',',' ') ?> F</td>
                                                <td class="text-end text-success"><?= number_format($economie,0,',',' ') ?> F</td>
                                            </tr>
                                            <?php endfor; ?>
                                            <tr class="table-secondary fw-bold">
                                                <td colspan="4" class="text-end">ÉCONOMIE D'IMPÔT TOTALE :</td>
                                                <td class="text-end text-success"><?= number_format($cumul_economie,0,',',' ') ?> F</td>
                                            </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-success mt-3">
                    <strong>💡 Analyse financière :</strong><br>
                    L'amortissement dégressif permet de déduire plus de charges les premières années, générant une économie d'impôt.<br>
                    <strong class="text-primary">À retenir :</strong> Cette méthode est avantageuse pour les entreprises dont les bénéfices sont élevés en début de vie de l'actif.
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>

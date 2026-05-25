<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Comparaison des méthodes de prévision";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$resultats_methodes = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nb_periodes = (int)$_POST['nb_periodes'];
    $donnees = [];
    for ($i = 1; $i <= $nb_periodes; $i++) {
        $donnees[] = ['periode' => $i, 'ventes' => (float)($_POST["vente_$i"] ?? 0)];
    }
    
    // Méthode 1: Moindres carrés
    $n = $nb_periodes;
    $sum_x = 0; $sum_y = 0; $sum_xy = 0; $sum_x2 = 0; $sum_y2 = 0;
    foreach ($donnees as $i => $d) {
        $x = $i + 1;
        $y = $d['ventes'];
        $sum_x += $x;
        $sum_y += $y;
        $sum_xy += $x * $y;
        $sum_x2 += $x * $x;
        $sum_y2 += $y * $y;
    }
    $a = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_x2 - $sum_x * $sum_x);
    $b = ($sum_y - $a * $sum_x) / $n;
    $previsions_mc = [];
    for ($i = 1; $i <= 3; $i++) {
        $x = $n + $i;
        $previsions_mc[] = round($a * $x + $b);
    }
    $numerateur = ($n * $sum_xy - $sum_x * $sum_y);
    $denominateur = sqrt(($n * $sum_x2 - $sum_x * $sum_x) * ($n * $sum_y2 - $sum_y * $sum_y));
    $precision_mc = $denominateur != 0 ? abs($numerateur / $denominateur) : 0;
    
    // Méthode 2: Exponentielle
    $sum_ln_y = 0; $sum_x_ln_y = 0;
    foreach ($donnees as $i => $d) {
        $x = $i + 1;
        $ln_y = log($d['ventes']);
        $sum_ln_y += $ln_y;
        $sum_x_ln_y += $x * $ln_y;
    }
    $b_exp = ($n * $sum_x_ln_y - $sum_x * $sum_ln_y) / ($n * $sum_x2 - $sum_x * $sum_x);
    $ln_a = ($sum_ln_y - $b_exp * $sum_x) / $n;
    $a_exp = exp($ln_a);
    $previsions_exp = [];
    for ($i = 1; $i <= 3; $i++) {
        $x = $n + $i;
        $previsions_exp[] = round($a_exp * exp($b_exp * $x));
    }
    
    // Méthode 3: Moyennes mobiles
    $fenetre = 3;
    $somme = 0;
    for ($i = $nb_periodes - $fenetre; $i < $nb_periodes; $i++) {
        $somme += $donnees[$i]['ventes'];
    }
    $moyenne = $somme / $fenetre;
    $previsions_mm = [$moyenne, $moyenne, $moyenne];
    
    // Méthode 4: Lissage exponentiel
    $alpha = 0.3;
    $lisse = $donnees[0]['ventes'];
    for ($i = 1; $i < $nb_periodes; $i++) {
        $lisse = $alpha * $donnees[$i]['ventes'] + (1 - $alpha) * $lisse;
    }
    $previsions_le = [$lisse, $lisse, $lisse];
    
    $resultats_methodes = [
        'moindres_carres' => ['previsions' => $previsions_mc, 'precision' => $precision_mc],
        'exponentielle' => ['previsions' => $previsions_exp, 'precision' => abs($b_exp)],
        'moyennes_mobiles' => ['previsions' => $previsions_mm, 'precision' => 0],
        'lissage_exponentiel' => ['previsions' => $previsions_le, 'precision' => 0]
    ];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Comparaison des méthodes de prévision</h5>
                <small>Moindres carrés | Exponentielle | Moyennes mobiles | Lissage exponentiel</small>
            </div>
            <div class="card-body">
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Données historiques</div>
                            <div class="card-body">
                                <form method="POST" id="dataForm">
                                    <div class="mb-3">
                                        <label>Nombre de périodes</label>
                                        <select name="nb_periodes" class="form-select" id="nbPeriodes" onchange="genererChamps()">
                                            <option value="4">4 périodes</option>
                                            <option value="5">5 périodes</option>
                                            <option value="6" selected>6 périodes</option>
                                            <option value="8">8 périodes</option>
                                        </select>
                                    </div>
                                    <div id="champsContainer">
                                        <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <div class="mb-2">
                                            <label>Période <?= $i ?> (milliers FCFA)</label>
                                            <input type="number" name="vente_<?= $i ?>" class="form-control" value="<?= 1000 + ($i-1)*150 ?>" step="50" required>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                    <button type="submit" class="btn-omega w-100 mt-3">Comparer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <?php if(!empty($resultats_methodes)): ?>
                        <div class="card">
                            <div class="card-header bg-success text-white">📈 Comparaison des prévisions</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-dark">
                                            <tr><th>Méthode</th><th>Période N+1</th><th>Période N+2</th><th>Période N+3</th><th>Fiabilité</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><strong>Moindres carrés</strong><br><small>Linéaire</small></td>
                                                <td class="text-end"><?= number_format($resultats_methodes['moindres_carres']['previsions'][0], 0, ',', ' ') ?> KF</td>
                                                <td class="text-end"><?= number_format($resultats_methodes['moindres_carres']['previsions'][1], 0, ',', ' ') ?> KF</td>
                                                <td class="text-end"><?= number_format($resultats_methodes['moindres_carres']['previsions'][2], 0, ',', ' ') ?> KF</td>
                                                <td class="text-center">R = <?= number_format($resultats_methodes['moindres_carres']['precision'], 4) ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Exponentielle</strong><br><small>Croissance</small></td>
                                                <td class="text-end"><?= number_format($resultats_methodes['exponentielle']['previsions'][0], 0, ',', ' ') ?> KF</td>
                                                <td class="text-end"><?= number_format($resultats_methodes['exponentielle']['previsions'][1], 0, ',', ' ') ?> KF</td>
                                                <td class="text-end"><?= number_format($resultats_methodes['exponentielle']['previsions'][2], 0, ',', ' ') ?> KF</td>
                                                <td class="text-center">Taux = <?= number_format($resultats_methodes['exponentielle']['precision'], 2) ?>%</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Moyennes mobiles</strong><br><small>MM3</small></td>
                                                <td class="text-end"><?= number_format($resultats_methodes['moyennes_mobiles']['previsions'][0], 0, ',', ' ') ?> KF</td>
                                                <td class="text-end"><?= number_format($resultats_methodes['moyennes_mobiles']['previsions'][1], 0, ',', ' ') ?> KF</td>
                                                <td class="text-end"><?= number_format($resultats_methodes['moyennes_mobiles']['previsions'][2], 0, ',', ' ') ?> KF</td>
                                                <td class="text-center">Lissage uniquement</td>
                                            </tr>
                                            <tr>
                                                <td><strong>Lissage exponentiel</strong><br><small>α=0.3</small></td>
                                                <td class="text-end"><?= number_format($resultats_methodes['lissage_exponentiel']['previsions'][0], 0, ',', ' ') ?> KF</td>
                                                <td class="text-end"><?= number_format($resultats_methodes['lissage_exponentiel']['previsions'][1], 0, ',', ' ') ?> KF</td>
                                                <td class="text-end"><?= number_format($resultats_methodes['lissage_exponentiel']['previsions'][2], 0, ',', ' ') ?> KF</td>
                                                <td class="text-center">α = 0.3</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="alert alert-info mt-3">
                                    <strong>📊 Synthèse et recommandation :</strong><br>
                                    <?php 
                                    $best_method = '';
                                    $best_value = 0;
                                    foreach($resultats_methodes as $key => $m) {
                                        if($m['precision'] > $best_value) {
                                            $best_value = $m['precision'];
                                            $best_method = $key;
                                        }
                                    }
                                    ?>
                                    La méthode <strong><?= strtoupper(str_replace('_', ' ', $best_method)) ?></strong> offre la meilleure fiabilité (R = <?= number_format($best_value, 4) ?>).
                                    <?php if($best_value >= 0.8): ?>
                                    ✅ Ce modèle est très fiable pour vos prévisions.
                                    <?php elseif($best_value >= 0.6): ?>
                                    ⚠️ Ce modèle est modérément fiable, affinez vos données.
                                    <?php else: ?>
                                    ❌ Aucun modèle n'est très fiable, vérifiez la cohérence des données.
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="card">
                            <div class="card-header bg-secondary text-white">📊 Comparaison des méthodes</div>
                            <div class="card-body text-center">
                                <i class="bi bi-bar-chart-steps fs-1 text-muted"></i>
                                <p class="mt-3">Saisissez vos données pour comparer les 4 méthodes de prévision</p>
                                <div class="alert alert-light">
                                    <strong>📈 Résultat attendu :</strong><br>
                                    Tableau comparatif avec prévisions sur 3 périodes
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

<script>
function genererChamps() {
    let nb = document.getElementById('nbPeriodes').value;
    let container = document.getElementById('champsContainer');
    let html = '';
    for(let i=1; i<=nb; i++) {
        let valeur = 1000 + (i-1) * 150;
        html += '<div class="mb-2">';
        html += '<label>Période ' + i + ' (milliers FCFA)</label>';
        html += '<input type="number" name="vente_' + i + '" class="form-control" value="' + valeur + '" step="50" required>';
        html += '</div>';
    }
    container.innerHTML = html;
}
</script>

<?php include 'inc_footer.php'; ?>

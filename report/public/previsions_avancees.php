<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Prévisions Avancées - Multi-méthodes";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];
$methode_active = $_POST['methode'] ?? 'MOINDRES_CARRES';

// Données par défaut
$donnees_par_defaut = [
    ['periode' => 1, 'ventes' => 1200],
    ['periode' => 2, 'ventes' => 1350],
    ['periode' => 3, 'ventes' => 1400],
    ['periode' => 4, 'ventes' => 1550],
    ['periode' => 5, 'ventes' => 1700],
    ['periode' => 6, 'ventes' => 1850],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'calculer') {
    $nb_periodes = (int)$_POST['nb_periodes'];
    $donnees = [];
    for ($i = 1; $i <= $nb_periodes; $i++) {
        $donnees[] = ['periode' => $i, 'ventes' => (float)($_POST["vente_$i"] ?? 0)];
    }
    
    $methode = $_POST['methode'];
    $previsions = [];
    $precision = 0;
    $equation = '';
    
    switch($methode) {
        case 'MOINDRES_CARRES':
            // Méthode des moindres carrés (linéaire)
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
            for ($i = 1; $i <= 3; $i++) {
                $x = $n + $i;
                $previsions[] = round($a * $x + $b);
            }
            $equation = "y = " . round($a, 2) . "x + " . round($b, 2);
            $numerateur = ($n * $sum_xy - $sum_x * $sum_y);
            $denominateur = sqrt(($n * $sum_x2 - $sum_x * $sum_x) * ($n * $sum_y2 - $sum_y * $sum_y));
            $precision = $denominateur != 0 ? abs($numerateur / $denominateur) : 0;
            break;
            
        case 'EXPONENTIEL':
            // Régression exponentielle (y = a * e^(bx))
            $n = $nb_periodes;
            $sum_x = 0; $sum_ln_y = 0; $sum_x_ln_y = 0; $sum_x2 = 0;
            foreach ($donnees as $i => $d) {
                $x = $i + 1;
                $ln_y = log($d['ventes']);
                $sum_x += $x;
                $sum_ln_y += $ln_y;
                $sum_x_ln_y += $x * $ln_y;
                $sum_x2 += $x * $x;
            }
            $b = ($n * $sum_x_ln_y - $sum_x * $sum_ln_y) / ($n * $sum_x2 - $sum_x * $sum_x);
            $ln_a = ($sum_ln_y - $b * $sum_x) / $n;
            $a = exp($ln_a);
            for ($i = 1; $i <= 3; $i++) {
                $x = $n + $i;
                $previsions[] = round($a * exp($b * $x));
            }
            $equation = "y = " . round($a, 2) . " * e^(" . round($b, 4) . "x)";
            $precision = abs($b) * 100;
            break;
            
        case 'MOYENNE_MOBILE':
            // Moyennes mobiles (3 périodes)
            $fenetre = 3;
            for ($i = $nb_periodes - $fenetre + 1; $i <= $nb_periodes; $i++) {
                $somme = 0;
                for ($j = $i - $fenetre; $j < $i; $j++) {
                    $somme += $donnees[$j]['ventes'];
                }
                $moyenne = $somme / $fenetre;
            }
            $derniere_moyenne = $moyenne;
            for ($i = 1; $i <= 3; $i++) {
                $previsions[] = round($derniere_moyenne);
            }
            $equation = "MM" . $fenetre . " = moyenne des 3 dernières périodes";
            $precision = 0;
            break;
            
        case 'LISSAGE_EXPONENTIEL':
            // Lissage exponentiel (α = 0.3)
            $alpha = 0.3;
            $lisse = $donnees[0]['ventes'];
            for ($i = 1; $i < $nb_periodes; $i++) {
                $lisse = $alpha * $donnees[$i]['ventes'] + (1 - $alpha) * $lisse;
            }
            for ($i = 1; $i <= 3; $i++) {
                $previsions[] = round($lisse);
            }
            $equation = "Lissage exponentiel (α = 0.3)";
            $precision = 0;
            break;
            
        case 'HOLT_WINTERS':
            // Holt-Winters (modèle à double lissage)
            $alpha = 0.3;
            $beta = 0.2;
            $niveau = $donnees[0]['ventes'];
            $tendance = ($donnees[$nb_periodes-1]['ventes'] - $donnees[0]['ventes']) / ($nb_periodes - 1);
            for ($i = 1; $i < $nb_periodes; $i++) {
                $niveau_prec = $niveau;
                $niveau = $alpha * $donnees[$i]['ventes'] + (1 - $alpha) * ($niveau + $tendance);
                $tendance = $beta * ($niveau - $niveau_prec) + (1 - $beta) * $tendance;
            }
            for ($i = 1; $i <= 3; $i++) {
                $previsions[] = round($niveau + $i * $tendance);
            }
            $equation = "Holt-Winters (α = 0.3, β = 0.2)";
            $precision = 0;
            break;
    }
    
    $resultats = [
        'donnees' => $donnees,
        'previsions' => $previsions,
        'equation' => $equation,
        'precision' => $precision,
        'methode' => $methode,
        'nb_periodes' => $nb_periodes
    ];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Prévisions Avancées - Multi-méthodes</h5>
                <small>Moindres carrés | Exponentielle | Moyennes mobiles | Lissage | Holt-Winters</small>
            </div>
            <div class="card-body">
                
                <div class="row">
                    <div class="col-md-5">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Données & Méthode</div>
                            <div class="card-body">
                                <form method="POST" id="dataForm">
                                    <input type="hidden" name="action" value="calculer">
                                    <div class="mb-3">
                                        <label>Méthode de prévision</label>
                                        <select name="methode" class="form-select" onchange="this.form.submit()" id="methodeSelect">
                                            <option value="MOINDRES_CARRES" <?= ($methode_active == 'MOINDRES_CARRES') ? 'selected' : '' ?>>📈 Moindres carrés (linéaire)</option>
                                            <option value="EXPONENTIEL" <?= ($methode_active == 'EXPONENTIEL') ? 'selected' : '' ?>>📊 Régression exponentielle</option>
                                            <option value="MOYENNE_MOBILE" <?= ($methode_active == 'MOYENNE_MOBILE') ? 'selected' : '' ?>>📉 Moyennes mobiles (3 périodes)</option>
                                            <option value="LISSAGE_EXPONENTIEL" <?= ($methode_active == 'LISSAGE_EXPONENTIEL') ? 'selected' : '' ?>>✨ Lissage exponentiel (α=0.3)</option>
                                            <option value="HOLT_WINTERS" <?= ($methode_active == 'HOLT_WINTERS') ? 'selected' : '' ?>>🔮 Holt-Winters (tendance)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label>Nombre de périodes</label>
                                        <select name="nb_periodes" class="form-select" id="nbPeriodes" onchange="genererChamps()">
                                            <option value="4">4 périodes</option>
                                            <option value="5">5 périodes</option>
                                            <option value="6" selected>6 périodes</option>
                                            <option value="8">8 périodes</option>
                                            <option value="12">12 périodes</option>
                                        </select>
                                    </div>
                                    <div id="champsContainer">
                                        <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <div class="mb-2">
                                            <label>Période <?= $i ?> (ventes en milliers FCFA)</label>
                                            <input type="number" name="vente_<?= $i ?>" class="form-control" value="<?= $donnees_par_defaut[$i-1]['ventes'] ?>" step="50" required>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                    <button type="submit" class="btn-omega w-100 mt-3">Calculer les prévisions</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-7">
                        <?php if(!empty($resultats)): ?>
                        <div class="card">
                            <div class="card-header bg-success text-white">📈 Résultats - <?= $resultats['methode'] ?></div>
                            <div class="card-body">
                                <div class="alert alert-primary">
                                    <strong>📐 Modèle :</strong><br>
                                    <code><?= $resultats['equation'] ?></code>
                                </div>
                                
                                <?php if($resultats['precision'] > 0): ?>
                                <div class="alert alert-info">
                                    <strong>📊 Précision du modèle :</strong><br>
                                    R = <?= number_format($resultats['precision'], 4) ?> (<?= $resultats['precision'] >= 0.8 ? '✅ Très fiable' : ($resultats['precision'] >= 0.6 ? '⚠️ Modérément fiable' : '❌ Peu fiable') ?>)
                                </div>
                                <?php endif; ?>
                                
                                <h6 class="mt-3">📈 Prévisions (milliers FCFA)</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-dark">
                                            <tr><th>Période</th><th>Prévision</th><th>FCFA</th><th>Variation</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $prec = 0;
                                            for($i=0; $i<3; $i++): 
                                                $variation = $i > 0 ? (($resultats['previsions'][$i] - $resultats['previsions'][$i-1]) / $resultats['previsions'][$i-1] * 100) : 0;
                                            ?>
                                            <tr>
                                                <td class="text-center">P<?= $resultats['nb_periodes'] + $i + 1 ?></td>
                                                <td class="text-end"><?= number_format($resultats['previsions'][$i], 0, ',', ' ') ?> milliers</td>
                                                <td class="text-end fw-bold text-primary"><?= number_format($resultats['previsions'][$i] * 1000, 0, ',', ' ') ?> FCFA</td>
                                                <td class="text-center <?= $variation >= 0 ? 'text-success' : 'text-danger' ?>"><?= $i > 0 ? ($variation >= 0 ? '+' : '') . number_format($variation, 1) . '%' : '-' ?></td>
                                            </tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <h6 class="mt-3">📊 Historique et tendance</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-dark">
                                            <tr><th>Période</th><th>Ventes réelles</th><th>Tendance</th><th>Écart</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            // Calcul de la tendance pour l'historique selon la méthode
                                            $tendances = [];
                                            if($resultats['methode'] == 'MOINDRES_CARRES') {
                                                $a = ($resultats['previsions'][0] - $resultats['previsions'][1]) / -1;
                                                $b = $resultats['donnees'][0]['ventes'] - $a;
                                                for($i=0; $i<$resultats['nb_periodes']; $i++) {
                                                    $tendances[] = round($a * ($i+1) + $b);
                                                }
                                            } elseif($resultats['methode'] == 'EXPONENTIEL') {
                                                // Extraction approximative
                                                for($i=0; $i<$resultats['nb_periodes']; $i++) {
                                                    $tendances[] = $resultats['donnees'][$i]['ventes'];
                                                }
                                            } else {
                                                $tendances = array_column($resultats['donnees'], 'ventes');
                                            }
                                            
                                            foreach($resultats['donnees'] as $i => $d): 
                                                $tendance = $tendances[$i] ?? $d['ventes'];
                                                $ecart = $d['ventes'] - $tendance;
                                            ?>
                                            <tr>
                                                <td class="text-center"><?= $d['periode'] ?> </td>
                                                <td class="text-end"><?= number_format($d['ventes'], 0, ',', ' ') ?> milliers</td>
                                                <td class="text-end"><?= number_format($tendance, 0, ',', ' ') ?> milliers</td>
                                                <td class="text-end <?= $ecart >= 0 ? 'text-success' : 'text-danger' ?>"><?= number_format($ecart, 0, ',', ' ') ?> milliers</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="alert alert-secondary mt-2">
                                    <strong>💡 Comparaison des méthodes :</strong><br>
                                    • <strong>Moindres carrés</strong> : croissance linéaire constante<br>
                                    • <strong>Exponentielle</strong> : croissance accélérée<br>
                                    • <strong>Moyennes mobiles</strong> : lissage des aléas<br>
                                    • <strong>Lissage exponentiel</strong> : pondération décroissante<br>
                                    • <strong>Holt-Winters</strong> : prend en compte la tendance
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="card">
                            <div class="card-header bg-secondary text-white">📊 Sélection d'une méthode</div>
                            <div class="card-body text-center">
                                <i class="bi bi-bar-chart-steps fs-1 text-muted"></i>
                                <p class="mt-3">Choisissez une méthode de prévision et saisissez vos données</p>
                                <div class="alert alert-light">
                                    <strong>📈 Guide des méthodes :</strong><br>
                                    • <strong>Moindres carrés</strong> : tendance linéaire stable<br>
                                    • <strong>Exponentielle</strong> : croissance ou décroissance rapide<br>
                                    • <strong>Moyennes mobiles</strong> : données avec fluctuations<br>
                                    • <strong>Lissage exponentiel</strong> : données récentes plus importantes<br>
                                    • <strong>Holt-Winters</strong> : série avec tendance et saisonnalité
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
        html += '<label>Période ' + i + ' (ventes en milliers FCFA)</label>';
        html += '<input type="number" name="vente_' + i + '" class="form-control" value="' + valeur + '" step="50" required>';
        html += '</div>';
    }
    container.innerHTML = html;
}
</script>

<?php include 'inc_footer.php'; ?>

<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Programmation Linéaire - Méthode des moindres carrés";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

// Données par défaut (exemple réel)
$donnees_par_defaut = [
    ['periode' => 1, 'ventes' => 1200],
    ['periode' => 2, 'ventes' => 1350],
    ['periode' => 3, 'ventes' => 1400],
    ['periode' => 4, 'ventes' => 1550],
    ['periode' => 5, 'ventes' => 1700],
    ['periode' => 6, 'ventes' => 1850],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nb_periodes = (int)$_POST['nb_periodes'];
    $donnees = [];
    
    for ($i = 1; $i <= $nb_periodes; $i++) {
        // Les données sont saisies en milliers
        $ventes = (float)($_POST["vente_$i"] ?? 0);
        $donnees[] = ['periode' => $i, 'ventes' => $ventes];
    }
    
    $n = $nb_periodes;
    $sum_x = 0; $sum_y = 0; $sum_xy = 0; $sum_x2 = 0; $sum_y2 = 0;
    
    for ($i = 0; $i < $n; $i++) {
        $x = $i + 1;
        $y = $donnees[$i]['ventes'];
        $sum_x += $x;
        $sum_y += $y;
        $sum_xy += $x * $y;
        $sum_x2 += $x * $x;
        $sum_y2 += $y * $y;
    }
    
    // Calcul de a et b
    $denominateur_a = ($n * $sum_x2 - $sum_x * $sum_x);
    if ($denominateur_a == 0) {
        $message = "⚠️ Impossible de calculer la tendance.";
        $resultats = [];
    } else {
        $a = ($n * $sum_xy - $sum_x * $sum_y) / $denominateur_a;
        $b = ($sum_y - $a * $sum_x) / $n;
        
        // Calcul du coefficient de corrélation R (formule correcte)
        $numerateur = ($n * $sum_xy - $sum_x * $sum_y);
        $denominateur = sqrt(($n * $sum_x2 - $sum_x * $sum_x) * ($n * $sum_y2 - $sum_y * $sum_y));
        
        if ($denominateur != 0) {
            $correlation = $numerateur / $denominateur;
        } else {
            $correlation = 0;
        }
        
        // R²
        $r2 = pow($correlation, 2);
        
        // Prévisions
        $previsions = [];
        for ($i = 1; $i <= 3; $i++) {
            $x = $n + $i;
            $previsions[] = round($a * $x + $b);
        }
        
        $resultats = [
            'a' => $a,
            'b' => $b,
            'equation' => "y = " . number_format($a, 2) . "x + " . number_format($b, 2),
            'correlation' => $correlation,
            'r2' => $r2,
            'previsions' => $previsions,
            'donnees' => $donnees,
            'nb_periodes' => $nb_periodes
        ];
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Programmation Linéaire - Moindres carrés</h5>
                <small>Prévisions par ajustement linéaire (données en milliers FCFA)</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-warning"><?= $message ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-5">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Données historiques</div>
                            <div class="card-body">
                                <form method="POST" id="dataForm">
                                    <div class="mb-3">
                                        <label>Nombre de périodes</label>
                                        <select name="nb_periodes" class="form-select" onchange="genererChamps()" id="nbPeriodes">
                                            <option value="4">4 périodes</option>
                                            <option value="5">5 périodes</option>
                                            <option value="6" selected>6 périodes</option>
                                            <option value="8">8 périodes</option>
                                        </select>
                                    </div>
                                    <div id="champsContainer">
                                        <?php for ($i = 1; $i <= 6; $i++): ?>
                                        <div class="mb-2">
                                            <label>Période <?= $i ?> (en milliers FCFA)</label>
                                            <input type="number" name="vente_<?= $i ?>" class="form-control" value="<?= $donnees_par_defaut[$i-1]['ventes'] ?>" step="50" required>
                                        </div>
                                        <?php endfor; ?>
                                    </div>
                                    <button type="submit" class="btn-omega w-100 mt-3">Calculer</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-7">
                        <?php if(!empty($resultats)): ?>
                        <div class="card">
                            <div class="card-header bg-success text-white">📈 Résultats</div>
                            <div class="card-body">
                                <div class="alert alert-primary">
                                    <strong>📐 Équation :</strong><br>
                                    <code><?= $resultats['equation'] ?></code>
                                    <p class="small">(y en milliers FCFA, x = période)</p>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card text-center <?= abs($resultats['correlation']) >= 0.8 ? 'bg-success text-white' : (abs($resultats['correlation']) >= 0.5 ? 'bg-warning text-dark' : 'bg-danger text-white') ?>">
                                            <div class="card-body">
                                                <h6>Coefficient R</h6>
                                                <h3><?= number_format($resultats['correlation'], 4) ?></h3>
                                                <small>
                                                    <?php 
                                                    $r = abs($resultats['correlation']);
                                                    if($r >= 0.9) echo '✅ Corrélation très forte';
                                                    elseif($r >= 0.7) echo '✅ Corrélation forte';
                                                    elseif($r >= 0.5) echo '⚠️ Corrélation modérée';
                                                    elseif($r >= 0.3) echo '⚠️ Corrélation faible';
                                                    else echo '❌ Corrélation très faible';
                                                    ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card text-center bg-info text-white">
                                            <div class="card-body">
                                                <h6>Coefficient R²</h6>
                                                <h3><?= number_format($resultats['r2'] * 100, 2) ?>%</h3>
                                                <small><?= $resultats['r2'] >= 0.7 ? 'Modèle fiable' : 'Modèle peu fiable' ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <h6 class="mt-3">📈 Prévisions (milliers FCFA)</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead class="table-dark">
                                            <tr><th>Période</th><th>Prévision</th><th>FCFA</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php for($i=0; $i<3; $i++): ?>
                                            <tr>
                                                <td class="text-center">P<?= $resultats['nb_periodes'] + $i + 1 ?></td>
                                                <td class="text-end"><?= number_format($resultats['previsions'][$i], 0, ',', ' ') ?> milliers FCA</th>
                                                <td class="text-end"><?= number_format($resultats['previsions'][$i] * 1000, 0, ',', ' ') ?> FCFA</th>
                                            </tr>
                                            <?php endfor; ?>
                                        </tbody>
                                    </table>
                                </div>
                                
                                <h6 class="mt-3">📊 Tableau des écarts</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead class="table-dark">
                                            <tr><th>Période</th><th>Réel</th><th>Tendance</th><th>Écart</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($resultats['donnees'] as $i => $d): 
                                                $tendance = round($resultats['a'] * ($i+1) + $resultats['b']);
                                            ?>
                                            <tr>
                                                <td class="text-center"><?= $d['periode'] ?> </td>
                                                <td class="text-end"><?= number_format($d['ventes'], 0, ',', ' ') ?> milliers</th>
                                                <td class="text-end"><?= number_format($tendance, 0, ',', ' ') ?> milliers</th>
                                                <td class="text-end <?= $d['ventes'] > $tendance ? 'text-success' : 'text-danger' ?>"><?= number_format($d['ventes'] - $tendance, 0, ',', ' ') ?> milliers</th>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="card">
                            <div class="card-header bg-secondary text-white">📊 Saisie des données</div>
                            <div class="card-body text-center">
                                <p>Saisissez les ventes historiques (en milliers FCFA).</p>
                                <div class="alert alert-light">Exemple : 1200 = 1 200 000 FCFA</div>
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

<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Calcul des Annuités - Amortissements";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

// Méthodes d'amortissement
$methodes = [
    'LINEAIRE' => 'Linéaire (constant)',
    'DEGRESSIF' => 'Dégressif (décroissant)',
    'MIXTE' => 'Mixte (Linéaire puis Dégressif)'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valeur_brute = (float)$_POST['valeur_brute'];
    $duree = (int)$_POST['duree'];
    $methode = $_POST['methode'];
    $taux_lineaire = 100 / $duree;
    
    if ($methode == 'LINEAIRE') {
        $annuite = $valeur_brute * $taux_lineaire / 100;
        for ($i = 1; $i <= $duree; $i++) {
            $resultats[$i] = [
                'annuite' => $annuite,
                'cumul' => $annuite * $i,
                'vnc' => $valeur_brute - ($annuite * $i)
            ];
        }
    } 
    elseif ($methode == 'DEGRESSIF') {
        $coefficient = $duree <= 4 ? 1.5 : ($duree <= 6 ? 2 : 2.5);
        $taux_degressif = $taux_lineaire * $coefficient;
        $vnc = $valeur_brute;
        for ($i = 1; $i <= $duree; $i++) {
            $annuite = $vnc * $taux_degressif / 100;
            if ($i == $duree) $annuite = $vnc;
            $resultats[$i] = [
                'annuite' => $annuite,
                'cumul' => $valeur_brute - ($vnc - $annuite),
                'vnc' => $vnc - $annuite
            ];
            $vnc -= $annuite;
        }
    }
    elseif ($methode == 'MIXTE') {
        // Première phase : dégressif pendant 2/3 de la durée
        $coefficient = $duree <= 4 ? 1.5 : ($duree <= 6 ? 2 : 2.5);
        $taux_degressif = $taux_lineaire * $coefficient;
        $vnc = $valeur_brute;
        $phase_degressif = floor($duree * 0.66);
        
        for ($i = 1; $i <= $phase_degressif; $i++) {
            $annuite = $vnc * $taux_degressif / 100;
            if ($i == $phase_degressif && $phase_degressif < $duree) {
                // Passage au linéaire
                $taux_restant = 100 / ($duree - $phase_degressif + 1);
                $annuite_lin = $vnc * $taux_restant / 100;
                $annuite = max($annuite, $annuite_lin);
            }
            $resultats[$i] = [
                'annuite' => $annuite,
                'cumul' => $valeur_brute - ($vnc - $annuite),
                'vnc' => $vnc - $annuite
            ];
            $vnc -= $annuite;
        }
        
        // Deuxième phase : linéaire
        $taux_restant = 100 / ($duree - $phase_degressif);
        for ($i = $phase_degressif + 1; $i <= $duree; $i++) {
            $annuite = $vnc * $taux_restant / 100;
            if ($i == $duree) $annuite = $vnc;
            $resultats[$i] = [
                'annuite' => $annuite,
                'cumul' => $valeur_brute - ($vnc - $annuite),
                'vnc' => $vnc - $annuite
            ];
            $vnc -= $annuite;
        }
    }
    
    $message = "✅ Calcul terminé - Méthode " . $methodes[$methode];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calculator"></i> Calcul des Annuités d'Amortissement</h5>
                <small>Méthodes linéaire, dégressive et mixte</small>
            </div>
            <div class="card-body">
                <?php if($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Paramètres</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-12">
                                        <label>Valeur brute (FCFA)</label>
                                        <input type="number" name="valeur_brute" class="form-control" value="10000000" step="100000" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label>Durée de vie (années)</label>
                                        <select name="duree" class="form-select" required>
                                            <option value="3">3 ans</option>
                                            <option value="4">4 ans</option>
                                            <option value="5" selected>5 ans</option>
                                            <option value="10">10 ans</option>
                                            <option value="15">15 ans</option>
                                            <option value="20">20 ans</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12">
                                        <label>Méthode d'amortissement</label>
                                        <select name="methode" class="form-select" required>
                                            <option value="LINEAIRE">Linéaire (constant)</option>
                                            <option value="DEGRESSIF">Dégressif (décroissant)</option>
                                            <option value="MIXTE">Mixte (Linéaire puis Dégressif)</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-omega w-100">Calculer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-8">
                        <div class="card bg-light">
                            <div class="card-header bg-success text-white">📊 Tableau d'amortissement</div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <thead class="table-dark">
                                            <tr><th>Année</th><th>Annuité (F)</th><th>Amort. cumulé (F)</th><th>VNC (F)</th><th>Taux (%)</th></tr>
                                        </thead>
                                        <tbody>
                                            <?php if(!empty($resultats)): 
                                                $taux = 100 / $duree;
                                                for($i=1; $i<=$duree; $i++): ?>
                                            <td>
                                                <td class="text-center"><?= $i ?></td>
                                                <td class="text-end"><?= number_format($resultats[$i]['annuite'],0,',',' ') ?> F</td>
                                                <td class="text-end"><?= number_format($resultats[$i]['cumul'],0,',',' ') ?> F</td>
                                                <td class="text-end fw-bold"><?= number_format($resultats[$i]['vnc'],0,',',' ') ?> F</td>
                                                <td class="text-center"><?= $methode == 'LINEAIRE' ? number_format($taux,1) : ($methode == 'DEGRESSIF' ? number_format($taux * 2,1) : 'variable') ?>%</td>
                                            </tr>
                                            <?php endfor; endif; ?>
                                        </tbody>
                                        <tfoot class="table-secondary">
                                            <tr><td colspan="4" class="text-end fw-bold">Valeur totale amortie :</td>
                                            <td class="text-end fw-bold"><?= number_format($valeur_brute,0,',',' ') ?> F</td>
                                            <tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cas pratiques -->
                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-info text-white">📋 Cas pratiques</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card text-center h-100">
                                            <div class="card-body">
                                                <i class="bi bi-laptop fs-1 text-primary"></i>
                                                <h6>Cas 1 : Matériel informatique</h6>
                                                <p class="small">Valeur : 5 000 000 F<br>Durée : 3 ans</p>
                                                <button class="btn btn-sm btn-outline-primary" onclick="chargerCas(5000000,3)">Charger</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-center h-100">
                                            <div class="card-body">
                                                <i class="bi bi-building fs-1 text-success"></i>
                                                <h6>Cas 2 : Local commercial</h6>
                                                <p class="small">Valeur : 50 000 000 F<br>Durée : 20 ans</p>
                                                <button class="btn btn-sm btn-outline-success" onclick="chargerCas(50000000,20)">Charger</button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card text-center h-100">
                                            <div class="card-body">
                                                <i class="bi bi-truck fs-1 text-warning"></i>
                                                <h6>Cas 3 : Véhicule utilitaire</h6>
                                                <p class="small">Valeur : 15 000 000 F<br>Durée : 5 ans</p>
                                                <button class="btn btn-sm btn-outline-warning" onclick="chargerCas(15000000,5)">Charger</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function chargerCas(valeur, duree) {
    document.querySelector('input[name="valeur_brute"]').value = valeur;
    document.querySelector('select[name="duree"]').value = duree;
    document.querySelector('form').submit();
}
</script>

<?php include 'inc_footer.php'; ?>

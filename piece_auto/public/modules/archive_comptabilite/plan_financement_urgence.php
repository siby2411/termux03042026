<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Plan de Financement - Plan de redressement";
$page_icon = "bank";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$plan = [];

// Récupération des données réelles
$ca = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799")->fetchColumn();
$achats = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699")->fetchColumn();
$charges_fixes = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id IN (613,615,628,631,641,651,652,653,671,681)")->fetchColumn();
$perte = $ca - $achats - $charges_fixes;
$capitaux_propres = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id = 101")->fetchColumn();
$dettes = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id = 401")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $augmentation_capital = (float)$_POST['augmentation_capital'];
    $reduction_charges = (float)$_POST['reduction_charges'];
    $hausse_ca = (float)$_POST['hausse_ca'];
    $emprunt = (float)$_POST['emprunt'];
    
    $ca_futur = $ca * (1 + $hausse_ca / 100);
    $charges_futures = $charges_fixes - $reduction_charges;
    $resultat_futur = $ca_futur - $achats - $charges_futures;
    
    $fonds_propres_futurs = $capitaux_propres + $augmentation_capital + $resultat_futur;
    $besoin_financement = max(0, -$resultat_futur);
    
    $plan = [
        'ca_futur' => $ca_futur,
        'charges_futures' => $charges_futures,
        'resultat_futur' => $resultat_futur,
        'besoin_financement' => $besoin_financement,
        'augmentation_capital' => $augmentation_capital,
        'emprunt' => $emprunt,
        'fonds_propres_futurs' => $fonds_propres_futurs,
        'hausse_ca' => $hausse_ca,
        'reduction_charges' => $reduction_charges
    ];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-danger text-white">
                <h5><i class="bi bi-bank"></i> Plan de Financement - Redressement d'Urgence</h5>
                <small>Perte actuelle : <?= number_format($perte, 0, ',', ' ') ?> FCFA</small>
            </div>
            <div class="card-body">
                
                <!-- Situation actuelle -->
                <div class="alert alert-danger">
                    <strong>🚨 DIAGNOSTIC FINANCIER</strong><br>
                    • Capitaux propres : <?= number_format($capitaux_propres, 0, ',', ' ') ?> FCFA<br>
                    • Dettes : <?= number_format($dettes, 0, ',', ' ') ?> FCFA<br>
                    • Perte de l'exercice : <?= number_format(abs($perte), 0, ',', ' ') ?> FCFA<br>
                    • <strong class="text-warning">Ratio capitaux propres/dettes : <?= number_format(($capitaux_propres / max($dettes,1)) * 100, 1) ?>%</strong>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">🔧 LEVIERS D'ACTION</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-6">
                                        <label>Augmentation de capital (FCFA)</label>
                                        <input type="number" name="augmentation_capital" class="form-control" value="5000000" step="1000000" required>
                                        <small class="text-muted">Renforcer les fonds propres</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Réduction des charges (%)</label>
                                        <input type="number" name="reduction_charges" class="form-control" value="20" step="5" required>
                                        <small class="text-muted">Cible : -20% sur charges fixes</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Hausse du CA (%)</label>
                                        <input type="number" name="hausse_ca" class="form-control" value="30" step="5" required>
                                        <small class="text-muted">Objectif de croissance</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Nouvel emprunt (FCFA)</label>
                                        <input type="number" name="emprunt" class="form-control" value="3000000" step="1000000" required>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-omega w-100">Élaborer le plan de redressement</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <?php if(!empty($plan)): ?>
                        <div class="card bg-success text-white">
                            <div class="card-header">📈 PLAN DE REDRESSEMENT</div>
                            <div class="card-body">
                                <table class="table table-bordered text-white">
                                    <tr><th>Indicateur</th><th>Avant</th><th>Après</th><th>Variation</th></tr>
                                    <tr><td>Chiffre d'affaires</td>
                                        <td class="text-end"><?= number_format($ca,0,',',' ') ?></td>
                                        <td class="text-end"><?= number_format($plan['ca_futur'],0,',',' ') ?></td>
                                        <td class="text-end text-success">+<?= number_format($plan['ca_futur'] - $ca,0,',',' ') ?></td>
                                    </tr>
                                    <tr><td>Charges fixes</td>
                                        <td class="text-end"><?= number_format($charges_fixes,0,',',' ') ?></td>
                                        <td class="text-end"><?= number_format($plan['charges_futures'],0,',',' ') ?></td>
                                        <td class="text-end text-success">-<?= number_format($charges_fixes - $plan['charges_futures'],0,',',' ') ?></td>
                                    </tr>
                                    <tr class="bg-warning text-dark">
                                        <td class="fw-bold">Résultat</td>
                                        <td class="text-end text-danger"><?= number_format($perte,0,',',' ') ?></td>
                                        <td class="text-end fw-bold"><?= number_format($plan['resultat_futur'],0,',',' ') ?></td>
                                        <td class="text-end"><?= $plan['resultat_futur'] >= 0 ? '✅ Bénéfice' : '⚠️ Perte réduite' ?></td>
                                    </tr>
                                </table>
                                <hr>
                                <h6>📊 BESOIN DE FINANCEMENT : <?= number_format($plan['besoin_financement'],0,',',' ') ?> FCFA</h6>
                                <div class="progress mt-2">
                                    <div class="progress-bar bg-success" style="width: <?= min(100, ($plan['augmentation_capital'] / max($plan['besoin_financement'],1)) * 100) ?>%">
                                        Apport capital : <?= number_format($plan['augmentation_capital'],0,',',' ') ?> F
                                    </div>
                                </div>
                                <div class="progress mt-1">
                                    <div class="progress-bar bg-info" style="width: <?= min(100, ($plan['emprunt'] / max($plan['besoin_financement'],1)) * 100) ?>%">
                                        Emprunt : <?= number_format($plan['emprunt'],0,',',' ') ?> F
                                    </div>
                                </div>
                                <div class="alert alert-light text-dark mt-3">
                                    <strong>💰 Nouveaux capitaux propres : <?= number_format($plan['fonds_propres_futurs'],0,',',' ') ?> FCFA</strong><br>
                                    <small>Ratio capitaux propres/dettes : <?= number_format(($plan['fonds_propres_futurs'] / max($dettes,1)) * 100, 1) ?>%</small>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Plan d'action -->
                <div class="card mt-4 border-warning">
                    <div class="card-header bg-warning text-dark">✅ PLAN D'ACTION PRIORITAIRE</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <i class="bi bi-graph-up fs-1 text-primary"></i>
                                        <h6>1. Augmenter le CA</h6>
                                        <p class="small">Objectif : +30%<br>Soit <?= number_format($ca * 0.3,0,',',' ') ?> FCFA</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <i class="bi bi-piggy-bank fs-1 text-success"></i>
                                        <h6>2. Réduire les charges</h6>
                                        <p class="small">Objectif : -20%<br>Soit <?= number_format($charges_fixes * 0.2,0,',',' ') ?> FCFA</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center h-100">
                                    <div class="card-body">
                                        <i class="bi bi-bank2 fs-1 text-danger"></i>
                                        <h6>3. Renforcer les fonds propres</h6>
                                        <p class="small">Augmentation de capital recommandée</p>
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
<?php include 'inc_footer.php'; ?>

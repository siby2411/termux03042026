<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Seuil de Rentabilité - Point Mort";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

// Récupération des données réelles
$ca = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799")->fetchColumn();
$achats = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699")->fetchColumn();
$charges_fixes = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id IN (613,615,628,631,641,651,652,653,671,681)")->fetchColumn();

// Calculs
$taux_marge_sur_cv = ($ca - $achats) / $ca * 100;
$seuil_rentabilite = ($charges_fixes / $taux_marge_sur_cv) * 100;
$point_mort_jours = ($seuil_rentabilite / $ca) * 360;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ca_souhaite = (float)$_POST['ca_souhaite'];
    $taux_marge_souhaite = (float)$_POST['taux_marge_souhaite'];
    $charges_fixes_souhaitees = (float)$_POST['charges_fixes_souhaitees'];
    
    $seuil_cible = ($charges_fixes_souhaitees / $taux_marge_souhaite) * 100;
    $resultat_cible = $ca_souhaite * ($taux_marge_souhaite / 100) - $charges_fixes_souhaitees;
    
    $resultats = [
        'seuil_cible' => $seuil_cible,
        'resultat_cible' => $resultat_cible,
        'ca_souhaite' => $ca_souhaite,
        'taux_marge_souhaite' => $taux_marge_souhaite,
        'charges_fixes_souhaitees' => $charges_fixes_souhaitees
    ];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calculator"></i> Seuil de Rentabilité - Point Mort</h5>
                <small>Déterminez le chiffre d'affaires minimum pour être rentable</small>
            </div>
            <div class="card-body">
                
                <!-- Situation actuelle -->
                <div class="alert alert-danger">
                    <strong>⚠️ SITUATION ACTUELLE CRITIQUE</strong><br>
                    Chiffre d'affaires : <?= number_format($ca, 0, ',', ' ') ?> FCFA<br>
                    Charges fixes : <?= number_format($charges_fixes, 0, ',', ' ') ?> FCFA<br>
                    Taux de marge sur coût variable : <?= number_format($taux_marge_sur_cv, 2) ?>%<br>
                    <strong class="text-danger">Perte actuelle : <?= number_format($ca - $achats - $charges_fixes, 0, ',', ' ') ?> FCFA</strong>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Situation actuelle</div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr><th>Indicateur</th><th>Valeur</th><th>Seuil cible</th><tr>
                                    <tr><td class="fw-bold">Seuil de rentabilité</td>
                                        <td class="text-end"><?= number_format($seuil_rentabilite, 0, ',', ' ') ?> F</td>
                                        <td class="text-end text-success"><?= number_format($ca * 1.3, 0, ',', ' ') ?> F</td>
                                    </tr>
                                    <tr><td class="fw-bold">Point mort (jours)</td>
                                        <td class="text-end"><?= round($point_mort_jours) ?> jours</td>
                                        <td class="text-end"><?= round($point_mort_jours * 0.7) ?> jours</td>
                                    </tr>
                                    <tr><td class="fw-bold">Marge de sécurité</td>
                                        <td class="text-end text-danger"><?= number_format($ca - $seuil_rentabilite, 0, ',', ' ') ?> F</td>
                                        <td class="text-end text-success">+ <?= number_format($ca * 0.3, 0, ',', ' ') ?> F</td>
                                    </tr>
                                </table>
                                <div class="progress mt-2">
                                    <div class="progress-bar bg-danger" style="width: <?= min(100, ($ca / $seuil_rentabilite) * 100) ?>%">
                                        <?= round(($ca / $seuil_rentabilite) * 100, 1) ?>% du seuil
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-success text-white">🎯 Simulation - Objectif de sortie de crise</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-6">
                                        <label>CA cible (FCFA)</label>
                                        <input type="number" name="ca_souhaite" class="form-control" value="<?= number_format($ca * 1.3, 0, '', '') ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Taux de marge (%)</label>
                                        <input type="number" name="taux_marge_souhaite" class="form-control" step="1" value="30" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Charges fixes cibles (FCFA)</label>
                                        <input type="number" name="charges_fixes_souhaitees" class="form-control" value="<?= number_format($charges_fixes * 0.7, 0, '', '') ?>" required>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn-omega w-100">Calculer le plan de sortie</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(!empty($resultats)): ?>
                <div class="card mt-4 border-success">
                    <div class="card-header bg-success text-white">📈 PLAN DE SORTIE DE CRISE</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-center bg-info text-white">
                                    <div class="card-body">
                                        <h5>Objectif CA</h5>
                                        <h3><?= number_format($resultats['ca_souhaite'], 0, ',', ' ') ?> F</h3>
                                        <small>+<?= round(($resultats['ca_souhaite'] / $ca - 1) * 100, 1) ?>% vs actuel</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center bg-warning text-dark">
                                    <div class="card-body">
                                        <h5>Réduction des charges fixes</h5>
                                        <h3><?= number_format($resultats['charges_fixes_souhaitees'], 0, ',', ' ') ?> F</h3>
                                        <small>-<?= round((1 - $resultats['charges_fixes_souhaitees'] / $charges_fixes) * 100, 1) ?>%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center bg-success text-white">
                                    <div class="card-body">
                                        <h5>Résultat net cible</h5>
                                        <h3><?= number_format($resultats['resultat_cible'], 0, ',', ' ') ?> F</h3>
                                        <small><?= $resultats['resultat_cible'] >= 0 ? '✅ Rentable' : '⚠️ Encore déficitaire' ?></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-secondary mt-3">
                            <strong>📋 Actions à mettre en œuvre :</strong>
                            <ul class="mt-2">
                                <li>📈 Augmenter le chiffre d'affaires de <?= round(($resultats['ca_souhaite'] / $ca - 1) * 100, 1) ?>% (<?= number_format($resultats['ca_souhaite'] - $ca, 0, ',', ' ') ?> FCFA)</li>
                                <li>💰 Réduire les charges fixes de <?= number_format($charges_fixes - $resultats['charges_fixes_souhaitees'], 0, ',', ' ') ?> FCFA</li>
                                <li>🎯 Atteindre un taux de marge de <?= $resultats['taux_marge_souhaite'] ?>% (actuel : <?= number_format($taux_marge_sur_cv, 1) ?>%)</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>

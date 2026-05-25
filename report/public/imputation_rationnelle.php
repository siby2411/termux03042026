<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Imputation rationnelle des charges - CAE";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $activite_reelle = (float)$_POST['activite_reelle'];
    $activite_normale = (float)$_POST['activite_normale'];
    $charges_fixes = (float)$_POST['charges_fixes'];
    $charges_variables = (float)$_POST['charges_variables'];
    
    $coefficient_imputation = $activite_reelle / $activite_normale;
    $charges_fixes_imputees = $charges_fixes * $coefficient_imputation;
    $ecart_activite = $charges_fixes - $charges_fixes_imputees;
    
    $total_charges_imputees = $charges_fixes_imputees + $charges_variables;
    
    $resultats = [
        'coeff' => $coefficient_imputation,
        'fixes_imputees' => $charges_fixes_imputees,
        'ecart' => $ecart_activite,
        'total' => $total_charges_imputees,
        'activite_reelle' => $activite_reelle,
        'activite_normale' => $activite_normale
    ];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calculator"></i> Imputation rationnelle des charges</h5>
                <small>Méthode de calcul des coûts en comptabilité analytique</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Définition :</strong> L'imputation rationnelle consiste à ajuster les charges fixes en fonction du niveau d'activité réel.<br>
                    <strong>Formule :</strong> Coefficient = Activité réelle / Activité normale
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Paramètres de calcul</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-6">
                                        <label>Activité réelle (heures/unités)</label>
                                        <input type="number" name="activite_reelle" class="form-control" step="100" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Activité normale (heures/unités)</label>
                                        <input type="number" name="activite_normale" class="form-control" step="100" value="1000" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Charges fixes (F)</label>
                                        <input type="number" name="charges_fixes" class="form-control" step="1000" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Charges variables (F)</label>
                                        <input type="number" name="charges_variables" class="form-control" step="1000" required>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn-omega">Calculer l'imputation</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if(!empty($resultats)): ?>
                        <div class="card">
                            <div class="card-header bg-success text-white">📊 Résultats</div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr><th>Indicateur</th><th>Valeur</th><th>Interprétation</th></tr>
                                    <tr><td>Coefficient d'imputation</td>
                                        <td class="text-end fw-bold"><?= number_format($resultats['coeff'], 2) ?></td>
                                        <td><?= $resultats['coeff'] >= 1 ? '✅ Activité normale atteinte' : '⚠️ Sous-activité' ?></td>
                                    </tr>
                                    <tr><td>Charges fixes imputées</td>
                                        <td class="text-end"><?= number_format($resultats['fixes_imputees'], 0, ',', ' ') ?> F</td>
                                        <td><?= number_format($resultats['fixes_imputees'] / max($resultats['activite_normale'],1), 0, ',', ' ') ?> F/unité</td>
                                    </tr>
                                    <tr><td>Écart d'activité</td>
                                        <td class="text-end <?= $resultats['ecart'] >= 0 ? 'text-danger' : 'text-success' ?>">
                                            <?= number_format(abs($resultats['ecart']), 0, ',', ' ') ?> F
                                        </td>
                                        <td><?= $resultats['ecart'] >= 0 ? 'Charges non imputées (sous-activité)' : 'Boni de suractivité' ?></td>
                                    </tr>
                                    <tr><td>Coût total imputé</td>
                                        <td class="text-end fw-bold text-primary"><?= number_format($resultats['total'], 0, ',', ' ') ?> F</td>
                                        <td><?= number_format($resultats['total'] / max($resultats['activite_reelle'],1), 0, ',', ' ') ?> F/unité produite</td>
                                    </tr>
                                </table>
                                
                                <div class="alert alert-secondary mt-3">
                                    <strong>💡 Interprétation :</strong><br>
                                    <?php if($resultats['coeff'] < 0.8): ?>
                                        ⚠️ Sous-activité sévère : <?= number_format((1 - $resultats['coeff']) * 100, 1) ?>% des charges fixes ne sont pas couvertes.
                                    <?php elseif($resultats['coeff'] < 1): ?>
                                        ⚠️ Sous-activité modérée : <?= number_format((1 - $resultats['coeff']) * 100, 1) ?>% des charges fixes à analyser.
                                    <?php else: ?>
                                        ✅ Activité normale ou en suractivité : boni de <?= number_format($resultats['ecart'], 0, ',', ' ') ?> F.
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Cas pratique -->
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">📋 CAS PRATIQUE - Atelier de production</div>
                    <div class="card-body">
                        <p><strong>Données :</strong></p>
                        <ul>
                            <li>Activité normale : 1 000 heures machine</li>
                            <li>Activité réelle : 750 heures machine</li>
                            <li>Charges fixes : 500 000 F (amortissements, location)</li>
                            <li>Charges variables : 300 000 F (matières, énergie)</li>
                        </ul>
                        <p><strong>Calculs :</strong></p>
                        <ul>
                            <li>Coefficient = 750 / 1 000 = 0,75</li>
                            <li>Charges fixes imputées = 500 000 × 0,75 = 375 000 F</li>
                            <li>Écart d'activité = 500 000 - 375 000 = 125 000 F (chômage)</li>
                            <li>Coût total imputé = 375 000 + 300 000 = 675 000 F</li>
                        </ul>
                        <div class="alert alert-success">
                            <strong>✅ Conclusion :</strong> Le coût de revient unitaire est de 675 000 / 750 = 900 F/heure.<br>
                            L'écart de 125 000 F représente le coût de la sous-activité.
                        </div>
                        <button class="btn btn-primary mt-2" onclick="remplirExemple()">Charger cet exemple</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function remplirExemple() {
    document.querySelector('input[name="activite_reelle"]').value = 750;
    document.querySelector('input[name="activite_normale"]').value = 1000;
    document.querySelector('input[name="charges_fixes"]').value = 500000;
    document.querySelector('input[name="charges_variables"]').value = 300000;
}
</script>

<?php include 'inc_footer.php'; ?>

<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Seuil de rentabilité - Analyse avancée";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$resultats = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ca = (float)$_POST['ca'];
    $charges_variables = (float)$_POST['charges_variables'];
    $charges_fixes = (float)$_POST['charges_fixes'];
    $prix_vente = (float)$_POST['prix_vente'];
    
    $marge_scv = $ca - $charges_variables;
    $taux_marge = $ca > 0 ? ($marge_scv / $ca) * 100 : 0;
    $seuil_rentabilite = $taux_marge > 0 ? ($charges_fixes / $taux_marge) * 100 : 0;
    $quantite_equilibre = $prix_vente > 0 ? $seuil_rentabilite / $prix_vente : 0;
    $point_mort_jours = $ca > 0 ? ($seuil_rentabilite / $ca) * 360 : 0;
    $marge_securite = $ca - $seuil_rentabilite;
    $indice_securite = $ca > 0 ? ($marge_securite / $ca) * 100 : 0;
    
    $resultats = [
        'ca' => $ca,
        'charges_variables' => $charges_variables,
        'charges_fixes' => $charges_fixes,
        'marge_scv' => $marge_scv,
        'taux_marge' => $taux_marge,
        'seuil_rentabilite' => $seuil_rentabilite,
        'quantite_equilibre' => $quantite_equilibre,
        'point_mort_jours' => $point_mort_jours,
        'marge_securite' => $marge_securite,
        'indice_securite' => $indice_securite
    ];
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calculator"></i> Seuil de rentabilité - Analyse avancée</h5>
                <small>Déterminez le point d'équilibre et la marge de sécurité</small>
            </div>
            <div class="card-body">
                
                <div class="row">
                    <div class="col-md-5">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Paramètres financiers</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-12">
                                        <label>Chiffre d'affaires (FCFA)</label>
                                        <input type="number" name="ca" class="form-control" value="50000000" step="1000000" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label>Charges variables (FCFA)</label>
                                        <input type="number" name="charges_variables" class="form-control" value="30000000" step="1000000" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label>Charges fixes (FCFA)</label>
                                        <input type="number" name="charges_fixes" class="form-control" value="15000000" step="1000000" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label>Prix de vente unitaire (FCFA)</label>
                                        <input type="number" name="prix_vente" class="form-control" value="5000" step="500" required>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-omega w-100">Calculer</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-7">
                        <?php if(!empty($resultats)): ?>
                        <div class="card">
                            <div class="card-header bg-success text-white">📈 Résultats de l'analyse</div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="alert alert-primary">
                                            <strong>📊 Marge sur coût variable</strong><br>
                                            <?= number_format($resultats['marge_scv'], 0, ',', ' ') ?> FCFA<br>
                                            <small>Taux : <?= number_format($resultats['taux_marge'], 2) ?>%</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-warning">
                                            <strong>⚖️ Seuil de rentabilité</strong><br>
                                            <?= number_format($resultats['seuil_rentabilite'], 0, ',', ' ') ?> FCFA<br>
                                            <small><?= number_format($resultats['quantite_equilibre'], 0, ',', ' ') ?> unités</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="alert alert-info">
                                            <strong>📅 Point mort</strong><br>
                                            <?= round($resultats['point_mort_jours']) ?> jours<br>
                                            <small>Soit le <?= date('d/m', mktime(0,0,0,1, round($resultats['point_mort_jours']))); ?></small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="alert alert-<?= $resultats['indice_securite'] >= 20 ? 'success' : ($resultats['indice_securite'] >= 10 ? 'warning' : 'danger') ?>">
                                            <strong>🛡️ Marge de sécurité</strong><br>
                                            <?= number_format($resultats['marge_securite'], 0, ',', ' ') ?> FCFA<br>
                                            <small>Indice : <?= number_format($resultats['indice_securite'], 1) ?>%</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="progress mt-3">
                                    <div class="progress-bar bg-success" style="width: <?= min(100, ($resultats['ca'] / $resultats['seuil_rentabilite']) * 100) ?>%">
                                        CA actuel
                                    </div>
                                    <div class="progress-bar bg-warning" style="width: <?= min(100, max(0, 100 - ($resultats['ca'] / $resultats['seuil_rentabilite']) * 100)) ?>%">
                                        Seuil
                                    </div>
                                </div>
                                
                                <div class="alert alert-secondary mt-3">
                                    <strong>💡 Analyse :</strong><br>
                                    <?php if($resultats['ca'] > $resultats['seuil_rentabilite']): ?>
                                    ✅ L'entreprise est au-dessus du seuil de rentabilité. Marge de sécurité : <?= number_format($resultats['indice_securite'], 1) ?>%<br>
                                    Le bénéfice actuel est de <?= number_format($resultats['ca'] - $resultats['charges_variables'] - $resultats['charges_fixes'], 0, ',', ' ') ?> FCFA
                                    <?php else: ?>
                                    ⚠️ L'entreprise est en dessous du seuil de rentabilité. Nécessité d'augmenter le CA de <?= number_format($resultats['seuil_rentabilite'] - $resultats['ca'], 0, ',', ' ') ?> FCFA
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="card">
                            <div class="card-header bg-secondary text-white">📊 Analyse du seuil de rentabilité</div>
                            <div class="card-body text-center">
                                <i class="bi bi-calculator-fill fs-1 text-muted"></i>
                                <p class="mt-3">Saisissez vos données pour calculer le seuil de rentabilité</p>
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

<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Modèle de Modigliani-Miller - Coût du capital";
$page_icon = "graph-up";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];
$exercice = $_GET['exercice'] ?? date('Y');

// Récupération des données financières
$capitaux_propres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 101 AND 199");
$capitaux_propres->execute([$exercice]);
$cp = $capitaux_propres->fetchColumn();

$dettes = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id IN (164, 165, 166)");
$dettes->execute([$exercice]);
$dettes = $dettes->fetchColumn();

$charges_financieres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id = 671");
$charges_financieres->execute([$exercice]);
$interets = $charges_financieres->fetchColumn();

$ebit = $pdo->prepare("
    SELECT COALESCE(SUM(CASE WHEN compte_credite_id BETWEEN 700 AND 799 THEN montant ELSE 0 END), 0) -
           COALESCE(SUM(CASE WHEN compte_debite_id BETWEEN 600 AND 699 THEN montant ELSE 0 END), 0)
    FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ?
");
$ebit->execute([$exercice]);
$ebit = $ebit->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taux_sans_risque = (float)$_POST['taux_sans_risque'];
    $prime_risque_economique = (float)$_POST['prime_risque_economique'];
    $prime_risque_financier = (float)$_POST['prime_risque_financier'];
    $taux_is = (float)$_POST['taux_is'];
    $dettes_saisie = (float)$_POST['dettes'];
    $cp_saisie = (float)$_POST['capitaux_propres'];
    
    // Calcul du coût des capitaux propres (MEDAF élargi)
    $cout_cp = $taux_sans_risque + $prime_risque_economique + $prime_risque_financier;
    
    // Coût de la dette (taux d'intérêt moyen)
    $cout_dette = $dettes_saisie > 0 ? ($interets / $dettes_saisie) * 100 : 5;
    $cout_dette_apres_is = $cout_dette * (1 - $taux_is / 100);
    
    // Ratio d'endettement
    $ratio_endettement = $cp_saisie > 0 ? $dettes_saisie / $cp_saisie : 0;
    
    // WACC sans dette (thèse Modigliani-Miller)
    $wacc_sans_dette = $cout_cp;
    
    // WACC avec dette (avec économie fiscale)
    $wacc_avec_dette = ($cp_saisie / ($cp_saisie + $dettes_saisie)) * $cout_cp + 
                       ($dettes_saisie / ($cp_saisie + $dettes_saisie)) * $cout_dette * (1 - $taux_is / 100);
    
    // Économie d'impôt liée à la déductibilité des intérêts
    $economie_fiscale = $interets * ($taux_is / 100);
    
    // Valeur de l'entreprise selon Modigliani-Miller
    $valeur_sans_dette = $ebit / ($cout_cp / 100);
    $valeur_avec_dette = $valeur_sans_dette + ($dettes_saisie * ($taux_is / 100));
    
    $resultats = [
        'cout_cp' => $cout_cp,
        'cout_dette' => $cout_dette,
        'cout_dette_apres_is' => $cout_dette_apres_is,
        'ratio_endettement' => $ratio_endettement,
        'wacc_sans_dette' => $wacc_sans_dette,
        'wacc_avec_dette' => $wacc_avec_dette,
        'economie_fiscale' => $economie_fiscale,
        'valeur_sans_dette' => $valeur_sans_dette,
        'valeur_avec_dette' => $valeur_avec_dette,
        'gain_endettement' => $valeur_avec_dette - $valeur_sans_dette
    ];
    
    // Sauvegarde
    $stmt = $pdo->prepare("INSERT INTO MODIGLIANI_MILLER (exercice, taux_sans_risque, prime_risque_economique, prime_risque_financier, cout_capitaux_propres, cout_dette, taux_is, ratio_endettement, wacc_sans_dette, wacc_avec_dette, economie_fiscale, valeur_entreprise_sans_dette, valeur_entreprise_avec_dette) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$exercice, $taux_sans_risque, $prime_risque_economique, $prime_risque_financier, $cout_cp, $cout_dette, $taux_is, $ratio_endettement, $wacc_sans_dette, $wacc_avec_dette, $economie_fiscale, $valeur_sans_dette, $valeur_avec_dette]);
    
    $message = "✅ Calcul Modigliani-Miller effectué";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-graph-up"></i> Modèle de Modigliani-Miller</h5>
                <small>Théorèmes sur la structure du capital et le coût moyen pondéré du capital (WACC)</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Données de base (<?= $exercice ?>)</div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr><th>Indicateur</th><th class="text-end">Valeur</th><th>Source</th></tr>
                                    <tr><td>EBIT (Résultat d'exploitation)</td><td class="text-end fw-bold"><?= number_format($ebit, 0, ',', ' ') ?> F</td><td>Comptabilité</td></tr>
                                    <tr><td>Capitaux propres</td><td class="text-end fw-bold"><?= number_format($cp, 0, ',', ' ') ?> F</td><td>Bilan</td></tr>
                                    <tr><td>Dettes financières</td><td class="text-end fw-bold"><?= number_format($dettes, 0, ',', ' ') ?> F</td><td>Bilan</td></tr>
                                    <tr><td>Charges financières</td><td class="text-end fw-bold"><?= number_format($interets, 0, ',', ' ') ?> F</td><td>Compte résultat</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">⚙️ Paramètres du modèle</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-6"><label>Taux sans risque (%)</label><input type="number" name="taux_sans_risque" class="form-control" value="3" step="0.5" required></div>
                                    <div class="col-md-6"><label>Prime risque économique (%)</label><input type="number" name="prime_risque_economique" class="form-control" value="6" step="0.5" required></div>
                                    <div class="col-md-6"><label>Prime risque financier (%)</label><input type="number" name="prime_risque_financier" class="form-control" value="2" step="0.5" required></div>
                                    <div class="col-md-6"><label>Taux IS (%)</label><input type="number" name="taux_is" class="form-control" value="25" step="1" required></div>
                                    <input type="hidden" name="dettes" value="<?= $dettes ?>">
                                    <input type="hidden" name="capitaux_propres" value="<?= $cp ?>">
                                    <div class="col-12"><button type="submit" class="btn-omega w-100">Calculer MM</button></div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(!empty($resultats)): ?>
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">📊 Résultats du modèle Modigliani-Miller</div>
                    <div class="card-body">
                        <!-- Théorème 1 -->
                        <div class="alert alert-info">
                            <strong>📌 Premier théorème (1958) :</strong> La valeur de l'entreprise ne dépend pas de sa structure financière.<br>
                            <strong>📌 Second théorème (1963) :</strong> La valeur de l'entreprise endettée = Valeur sans endettement + Économie d'impôt.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card text-center bg-primary text-white">
                                    <div class="card-body">
                                        <h6>Coût des capitaux propres</h6>
                                        <h3><?= number_format($resultats['cout_cp'], 2) ?>%</h3>
                                        <small>= <?= number_format($resultats['cout_cp'] - $resultats['cout_dette'], 2) ?>% - économie</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center bg-warning text-dark">
                                    <div class="card-body">
                                        <h6>Coût de la dette</h6>
                                        <h3><?= number_format($resultats['cout_dette'], 2) ?>%</h3>
                                        <small>Avant IS: <?= number_format($resultats['cout_dette'], 2) ?>%</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center bg-success text-white">
                                    <div class="card-body">
                                        <h6>Coût de la dette après IS</h6>
                                        <h3><?= number_format($resultats['cout_dette_apres_is'], 2) ?>%</h3>
                                        <small>Économie de <?= number_format($resultats['cout_dette'] - $resultats['cout_dette_apres_is'], 2) ?>%</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header bg-secondary text-white">💰 Structure du capital</div>
                                    <table class="table table-bordered mb-0">
                                        <tr>
                                            <td>Endettement / Capitaux propres</th>
                                            <td class="text-end fw-bold"><?= number_format($resultats['ratio_endettement'], 2) ?></td>
                                        </tr>
                                        <tr>
                                            <td>Poids des capitaux propres</th>
                                            <td class="text-end"><?= number_format(100 / (1 + $resultats['ratio_endettement']), 2) ?>%</td>
                                        </tr>
                                        <tr>
                                            <td>Poids de la dette</th>
                                            <td class="text-end"><?= number_format(100 - 100 / (1 + $resultats['ratio_endettement']), 2) ?>%</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header bg-secondary text-white">📉 WACC (Coût moyen pondéré du capital)</div>
                                    <table class="table table-bordered mb-0">
                                        <tr>
                                            <td>WACC sans endettement (entreprise non endettée)</th>
                                            <td class="text-end fw-bold"><?= number_format($resultats['wacc_sans_dette'], 2) ?>%</td>
                                        </tr>
                                        <tr>
                                            <td>WACC avec endettement (entreprise endettée)</th>
                                            <td class="text-end fw-bold text-success"><?= number_format($resultats['wacc_avec_dette'], 2) ?>%</td>
                                        </tr>
                                        <tr>
                                            :<Baisse du WACC</th>
                                            <td class="text-end text-success">- <?= number_format($resultats['wacc_sans_dette'] - $resultats['wacc_avec_dette'], 2) ?>%</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">💵 Économie d'impôt et valeur</div>
                                    <table class="table table-bordered mb-0">
                                        <tr>
                                            <td>Économie d'impôt annuelle</th>
                                            <td class="text-end fw-bold text-success">+ <?= number_format($resultats['economie_fiscale'], 0, ',', ' ') ?> F</td>
                                        </tr>
                                        <tr>
                                            <td>Valeur de l'entreprise sans dette</th>
                                            <td class="text-end"><?= number_format($resultats['valeur_sans_dette'], 0, ',', ' ') ?> F</td>
                                        </tr>
                                        <tr>
                                            <td>Valeur de l'entreprise avec dette</th>
                                            <td class="text-end fw-bold text-primary"><?= number_format($resultats['valeur_avec_dette'], 0, ',', ' ') ?> F</td>
                                        </tr>
                                        <tr>
                                            :<Gain lié à l'endettement</th>
                                            <td class="text-end text-success">+ <?= number_format($resultats['gain_endettement'], 0, ',', ' ') ?> F</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-header bg-secondary text-white">📈 Interprétation MM (Modigliani-Miller)</div>
                                    <div class="card-body">
                                        <?php if($resultats['wacc_avec_dette'] < $resultats['wacc_sans_dette']): ?>
                                            <div class="alert alert-success">
                                                <strong>✅ Effet de levier positif</strong><br>
                                                L'endettement réduit le WACC de <?= number_format($resultats['wacc_sans_dette'] - $resultats['wacc_avec_dette'], 2) ?>%<br>
                                                L'économie d'impôt (<?= number_format($resultats['economie_fiscale'], 0, ',', ' ') ?> F/an) compense le risque supplémentaire.
                                            </div>
                                        <?php else: ?>
                                            <div class="alert alert-warning">
                                                <strong>⚠️ Effet de levier neutre ou négatif</strong><br>
                                                Le coût du capital n'est pas optimisé.
                                            </div>
                                        <?php endif; ?>
                                        <div class="alert alert-secondary mt-2">
                                            <strong>🔑 Formules clés :</strong><br>
                                            WACC = (E/V) × Re + (D/V) × Rd × (1 - Tc)<br>
                                            VL = VU + Tc × D
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>

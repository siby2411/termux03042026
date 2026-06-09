<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Coût Moyen Pondéré du Capital (WACC)";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$message = '';
$resultats = [];
$exercice = $_GET['exercice'] ?? date('Y');

// Récupération des données financières de l'exercice
$capitaux_propres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 101 AND 199");
$capitaux_propres->execute([$exercice]);
$cp = $capitaux_propres->fetchColumn();

$dettes = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id IN (164, 165, 166, 401)");
$dettes->execute([$exercice]);
$dettes = $dettes->fetchColumn();

$charges_financieres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id = 671");
$charges_financieres->execute([$exercice]);
$interets = $charges_financieres->fetchColumn();

$total_financement = $cp + $dettes;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cout_cp = (float)$_POST['cout_capitaux_propres'];
    $taux_dette = (float)$_POST['taux_dette'];
    $taux_is = (float)$_POST['taux_is'];
    $cp_saisi = (float)$_POST['capitaux_propres'];
    $dettes_saisies = (float)$_POST['dettes'];
    
    $total = $cp_saisi + $dettes_saisies;
    $poids_cp = $total > 0 ? $cp_saisi / $total : 0;
    $poids_dette = $total > 0 ? $dettes_saisies / $total : 0;
    
    $cout_dette_apres_is = $taux_dette * (1 - $taux_is / 100);
    $wacc = ($poids_cp * $cout_cp) + ($poids_dette * $cout_dette_apres_is);
    
    $resultats = [
        'cp' => $cp_saisi,
        'dettes' => $dettes_saisies,
        'total' => $total,
        'poids_cp' => $poids_cp * 100,
        'poids_dette' => $poids_dette * 100,
        'cout_cp' => $cout_cp,
        'cout_dette_brut' => $taux_dette,
        'cout_dette_net' => $cout_dette_apres_is,
        'wacc' => $wacc
    ];
    
    // Sauvegarde
    $stmt = $pdo->prepare("INSERT INTO WACC_CALCULS (exercice, capitaux_propres, dettes_financieres, total_financement, cout_capitaux_propres, cout_dette_avant_is, taux_is, cout_dette_apres_is, wacc) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$exercice, $cp_saisi, $dettes_saisies, $total, $cout_cp, $taux_dette, $taux_is, $cout_dette_apres_is, $wacc]);
    
    $message = "✅ WACC calculé : " . number_format($wacc, 2) . "%";
}
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calculator"></i> Coût Moyen Pondéré du Capital (WACC)</h5>
                <small>Weighted Average Cost of Capital - Coût moyen des ressources financières</small>
            </div>
            <div class="card-body">
                <?php if($message): ?>
                    <div class="alert alert-success"><?= $message ?></div>
                <?php endif; ?>

                <div class="alert alert-info">
                    <strong>📖 Définition :</strong> Le WACC est le coût moyen des différentes sources de financement de l'entreprise (capitaux propres et dettes). Il représente le taux minimum de rentabilité exigé par les investisseurs.
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">📊 Structure financière (<?= $exercice ?>)</div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr><th>Source</th><th class="text-end">Montant</th><th class="text-end">Poids</th></tr>
                                    <tr><td>Capitaux propres</td><td class="text-end"><?= number_format($cp, 0, ',', ' ') ?> F</td>
                                        <td class="text-end"><?= $total_financement > 0 ? number_format($cp / $total_financement * 100, 1) : 0 ?>%</td>
                                    </tr>
                                    <tr><td>Dettes financières</td><td class="text-end"><?= number_format($dettes, 0, ',', ' ') ?> F</td>
                                        <td class="text-end"><?= $total_financement > 0 ? number_format($dettes / $total_financement * 100, 1) : 0 ?>%</td>
                                    </tr>
                                    <tr class="fw-bold"><td>TOTAL</td><td class="text-end"><?= number_format($total_financement, 0, ',', ' ') ?> F</td>
                                        <td class="text-end">100%</td>
                                    </tr>
                                </table>
                                <div class="progress mt-2">
                                    <div class="progress-bar bg-success" style="width: <?= $total_financement > 0 ? ($cp / $total_financement * 100) : 0 ?>%">CP</div>
                                    <div class="progress-bar bg-danger" style="width: <?= $total_financement > 0 ? ($dettes / $total_financement * 100) : 0 ?>%">Dettes</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-header bg-secondary text-white">⚙️ Paramètres de calcul</div>
                            <div class="card-body">
                                <form method="POST" class="row g-3">
                                    <div class="col-md-6">
                                        <label>Coût des capitaux propres (Re) %</label>
                                        <input type="number" name="cout_capitaux_propres" class="form-control" value="15" step="0.5" required>
                                        <small>Calculé via MEDAF ou taux d'actualisation</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Taux d'intérêt dette (Rd) %</label>
                                        <input type="number" name="taux_dette" class="form-control" value="6" step="0.5" required>
                                        <small>Taux moyen des emprunts</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Taux IS (%)</label>
                                        <input type="number" name="taux_is" class="form-control" value="25" step="1" required>
                                        <small>Impôt sur les sociétés</small>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Capitaux propres (F)</label>
                                        <input type="number" name="capitaux_propres" class="form-control" value="<?= $cp ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Dettes (F)</label>
                                        <input type="number" name="dettes" class="form-control" value="<?= $dettes ?>" required>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-omega w-100">Calculer le WACC</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if(!empty($resultats)): ?>
                <div class="card mt-4">
                    <div class="card-header bg-success text-white">📊 Résultat du calcul WACC</div>
                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>Formule :</strong> WACC = (E/V) × Re + (D/V) × Rd × (1 - Tc)
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-bordered">
                                    <tr><th>Élément</th><th>Formule</th><th>Valeur</th></tr>
                                    <tr><td>Poids capitaux propres</td><td>E/V</td><td class="text-end"><?= number_format($resultats['poids_cp'], 1) ?>%</td></tr>
                                    <tr><td>Poids de la dette</td><td>D/V</td><td class="text-end"><?= number_format($resultats['poids_dette'], 1) ?>%</td></tr>
                                    <tr><td>Coût capitaux propres</td><td>Re</td><td class="text-end"><?= number_format($resultats['cout_cp'], 2) ?>%</td></tr>
                                    <tr><td>Coût dette brut</td><td>Rd</td><td class="text-end"><?= number_format($resultats['cout_dette_brut'], 2) ?>%</td></tr>
                                    <tr><td>Coût dette net</td><td>Rd × (1 - IS)</td><td class="text-end"><?= number_format($resultats['cout_dette_net'], 2) ?>%</td></tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-primary text-white text-center">
                                    <div class="card-body">
                                        <h5>WACC (Coût moyen pondéré)</h5>
                                        <h2 class="display-4"><?= number_format($resultats['wacc'], 2) ?>%</h2>
                                        <p class="mb-0">Taux minimum de rentabilité exigé</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-secondary mt-3">
                            <strong>💡 Interprétation :</strong><br>
                            Un projet d'investissement est rentable si son TRI est <strong>supérieur à <?= number_format($resultats['wacc'], 2) ?>%</strong>.<br>
                            Actuellement, le coût moyen du capital de l'entreprise est de <strong><?= number_format($resultats['wacc'], 2) ?>%</strong>.
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>

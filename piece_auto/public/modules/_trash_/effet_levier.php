<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Effet de levier financier";
$page_icon = "arrow-up-down";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$exercice = $_GET['exercice'] ?? date('Y');

// Récupération des données financières
$resultat = $pdo->prepare("
    SELECT COALESCE(SUM(CASE WHEN compte_credite_id BETWEEN 700 AND 799 THEN montant ELSE 0 END), 0) -
           COALESCE(SUM(CASE WHEN compte_debite_id BETWEEN 600 AND 699 THEN montant ELSE 0 END), 0)
    FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ?
");
$resultat->execute([$exercice]);
$resultat_net = $resultat->fetchColumn();

$charges_financieres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id = 671");
$charges_financieres->execute([$exercice]);
$charges_financieres = $charges_financieres->fetchColumn();

$actif_total = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id BETWEEN 20 AND 59");
$actif_total->execute([$exercice]);
$actif_total = $actif_total->fetchColumn();

$capitaux_propres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 101 AND 199");
$capitaux_propres->execute([$exercice]);
$capitaux_propres = $capitaux_propres->fetchColumn();

$dettes = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id IN (164, 165, 166)");
$dettes->execute([$exercice]);
$dettes = $dettes->fetchColumn();

// Calcul de la rentabilité économique (ROE)
$rentabilite_economique = $actif_total > 0 ? ($resultat_net + $charges_financieres) / $actif_total * 100 : 0;

// Calcul de la rentabilité financière (ROA)
$rentabilite_financiere = $capitaux_propres > 0 ? $resultat_net / $capitaux_propres * 100 : 0;

// Taux d'intérêt moyen
$taux_interet = $dettes > 0 ? ($charges_financieres / $dettes) * 100 : 0;

// Effet de levier
$effet_leverage = $dettes > 0 ? ($rentabilite_economique - $taux_interet) * ($dettes / $capitaux_propres) : 0;

// Vérification
$rentabilite_financiere_calc = $rentabilite_economique + $effet_leverage;

// Interprétation
if ($effet_leverage > 0) {
    $interpretation_levier = "L'endettement est bénéfique : il amplifie la rentabilité des capitaux propres";
    $classe_levier = "success";
} elseif ($effet_leverage < 0) {
    $interpretation_levier = "L'endettement est défavorable : il réduit la rentabilité des capitaux propres";
    $classe_levier = "danger";
} else {
    $interpretation_levier = "L'endettement n'a pas d'effet sur la rentabilité";
    $classe_levier = "secondary";
}

// Sauvegarde
$stmt = $pdo->prepare("INSERT INTO EFFET_LEVIER (exercice, rentabilite_economique, rentabilite_financiere, taux_endettement, effet_leverage, interpretation) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->execute([$exercice, $rentabilite_economique, $rentabilite_financiere, $dettes / max($capitaux_propres, 1), $effet_leverage, $interpretation_levier]);
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-arrow-up-down"></i> Effet de levier financier</h5>
                <small>Impact de l'endettement sur la rentabilité</small>
            </div>
            <div class="card-body">
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card bg-info text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($rentabilite_economique, 2) ?>%</h4>
                                <small>Rentabilité économique (ROE)</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-primary text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($taux_interet, 2) ?>%</h4>
                                <small>Taux d'intérêt moyen</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white text-center">
                            <div class="card-body">
                                <h4><?= number_format($rentabilite_financiere, 2) ?>%</h4>
                                <small>Rentabilité financière (ROA)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formule de l'effet de levier -->
                <div class="card bg-light mb-4">
                    <div class="card-header bg-secondary text-white">📐 Formule de l'effet de levier</div>
                    <div class="card-body text-center">
                        <h5>ROA = ROE + (ROE - i) × D/CP</h5>
                        <p class="mt-2">
                            ROA = <?= number_format($rentabilite_financiere, 2) ?>% <br>
                            = <?= number_format($rentabilite_economique, 2) ?>% + (<?= number_format($rentabilite_economique - $taux_interet, 2) ?>%) × <?= number_format($dettes / max($capitaux_propres, 1), 2) ?>
                        </p>
                    </div>
                </div>

                <!-- Effet de levier -->
                <div class="alert alert-<?= $classe_levier ?> text-center">
                    <h4>Effet de levier : <strong><?= number_format($effet_leverage, 2) ?>%</strong></h4>
                    <p class="mb-0"><?= $interpretation_levier ?></p>
                </div>

                <!-- Tableau de sensibilité -->
                <h6 class="mt-4">📊 Analyse de sensibilité - Impact du taux d'endettement</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-dark">
                            <tr><th>Niveau d'endettement</th><th>Dettes / CP</th><th>Rentabilité économique</th><th>Rentabilité financière</th><th>Effet de levier</th></tr>
                        </thead>
                        <tbody>
                            <tr class="table-light"><td>Sans endettement</td><td class="text-center">0</td><td class="text-center"><?= number_format($rentabilite_economique, 2) ?>%</td><td class="text-center"><?= number_format($rentabilite_economique, 2) ?>%</td><td class="text-center">0%</td></tr>
                            <tr><td>Endettement actuel</td><td class="text-center"><?= number_format($dettes / max($capitaux_propres, 1), 2) ?></td><td class="text-center"><?= number_format($rentabilite_economique, 2) ?>%</td><td class="text-center"><?= number_format($rentabilite_financiere, 2) ?>%</td><td class="text-center"><?= number_format($effet_leverage, 2) ?>%</td></tr>
                            <tr class="bg-<?= $classe_levier ?>"><td>Endettement max recommandé</td><td class="text-center">2.0</td><td class="text-center"><?= number_format($rentabilite_economique, 2) ?>%</td><td class="text-center"><?= number_format($rentabilite_economique + ($rentabilite_economique - $taux_interet) * 2, 2) ?>%</td><td class="text-center"><?= number_format(($rentabilite_economique - $taux_interet) * 2, 2) ?>%</td></tr>
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-secondary mt-3">
                    <strong>💡 Interprétation :</strong><br>
                    • <strong>Effet de levier positif</strong> : L'endettement améliore la rentabilité des capitaux propres<br>
                    • <strong>Effet de levier nul</strong> : L'endettement n'influence pas la rentabilité<br>
                    • <strong>Effet de levier négatif</strong> : L'endettement détériore la rentabilité (risque de surendettement)
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>

<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Ratios de liquidité et solvabilité";
$page_icon = "calculator";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$exercice = $_GET['exercice'] ?? date('Y');

// Calcul des ratios
$actif_circulant = $pdo->prepare("
    SELECT COALESCE(SUM(CASE WHEN compte_debite_id BETWEEN 30 AND 49 THEN montant ELSE 0 END), 0)
    FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ?
");
$actif_circulant->execute([$exercice]);
$actif_circulant = $actif_circulant->fetchColumn();

$stocks = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id BETWEEN 30 AND 39");
$stocks->execute([$exercice]);
$stocks = $stocks->fetchColumn();

$passif_circulant = $pdo->prepare("
    SELECT COALESCE(SUM(CASE WHEN compte_credite_id BETWEEN 40 AND 49 THEN montant ELSE 0 END), 0)
    FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ?
");
$passif_circulant->execute([$exercice]);
$passif_circulant = $passif_circulant->fetchColumn();

$disponibilites = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id = 521");
$disponibilites->execute([$exercice]);
$disponibilites = $disponibilites->fetchColumn();

$capitaux_propres = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 101 AND 199");
$capitaux_propres->execute([$exercice]);
$capitaux_propres = $capitaux_propres->fetchColumn();

$dettes_total = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_credite_id BETWEEN 40 AND 49");
$dettes_total->execute([$exercice]);
$dettes_total = $dettes_total->fetchColumn();

// Calculs des ratios
$ratio_liquidite_generale = $passif_circulant > 0 ? $actif_circulant / $passif_circulant : 0;
$ratio_liquidite_reduite = $passif_circulant > 0 ? ($actif_circulant - $stocks) / $passif_circulant : 0;
$ratio_liquidite_immediate = $passif_circulant > 0 ? $disponibilites / $passif_circulant : 0;
$ratio_endettement = $capitaux_propres > 0 ? $dettes_total / $capitaux_propres : 0;
$ratio_autonomie = $capitaux_propres > 0 ? ($capitaux_propres / ($capitaux_propres + $dettes_total)) * 100 : 0;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-calculator"></i> Ratios de liquidité et solvabilité - Exercice <?= $exercice ?></h5>
                <small>Analyse de la santé financière</small>
            </div>
            <div class="card-body">
                
                <div class="row">
                    <!-- LIQUIDITÉ -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">💧 RATIOS DE LIQUIDITÉ</div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <td><strong>Liquidité générale</strong><br><small>Actif circulant / Passif circulant</small></td>
                                        <td class="text-center fw-bold"><?= number_format($ratio_liquidite_generale, 2) ?></td>
                                        <td><?= $ratio_liquidite_generale >= 1 ? '✅ Bonne couverture' : '⚠️ Risque de liquidité' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Liquidité réduite</strong><br><small>(Actif - Stocks) / Passif circulant</small></td>
                                        <td class="text-center fw-bold"><?= number_format($ratio_liquidite_reduite, 2) ?></td>
                                        <td><?= $ratio_liquidite_reduite >= 0.5 ? '✅ Suffisant' : '⚠️ Insuffisant' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Liquidité immédiate</strong><br><small>Disponibilités / Passif circulant</small></td>
                                        <td class="text-center fw-bold"><?= number_format($ratio_liquidite_immediate, 2) ?></td>
                                        <td><?= $ratio_liquidite_immediate >= 0.2 ? '✅ Bonne trésorerie' : '⚠️ Trésorerie faible' ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- SOLVABILITÉ -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">🏛️ RATIOS DE SOLVABILITÉ</div>
                            <div class="card-body">
                                <table class="table table-bordered">
                                    <tr>
                                        <td><strong>Endettement global</strong><br><small>Dettes / Capitaux propres</small></td>
                                        <td class="text-center fw-bold"><?= number_format($ratio_endettement, 2) ?></td>
                                        <td><?= $ratio_endettement <= 1 ? '✅ Bonne solvabilité' : '⚠️ Endettement élevé' ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Autonomie financière</strong><br><small>Capitaux propres / Total ressources</small></td>
                                        <td class="text-center fw-bold"><?= number_format($ratio_autonomie, 2) ?>%</td>
                                        <td><?= $ratio_autonomie >= 50 ? '✅ Bonne autonomie' : '⚠️ Dépendance financière' ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ÉQUILIBRE FINANCIER OPTIMUM -->
                <div class="alert alert-success mt-3">
                    <strong>⭐ ÉQUILIBRE FINANCIER OPTIMUM :</strong><br>
                    Une entreprise est en équilibre financier lorsque :<br>
                    <ul class="mt-2">
                        <li>FRNG > 0 (ressources stables > actif immobilisé)</li>
                        <li>BFR < FRNG (cycle d'exploitation bien financé)</li>
                        <li>Trésorerie Nette > 0 (disponibilités positives)</li>
                        <li>Ratio liquidité générale > 1</li>
                        <li>Ratio autonomie financière > 50%</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>

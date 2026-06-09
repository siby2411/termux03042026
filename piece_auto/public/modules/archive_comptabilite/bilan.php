<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Bilan Synthétique - SYSCOHADA";
$page_icon = "pie-chart";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$exercice = $_GET['exercice'] ?? date('Y');

// ---- ACTIF ----
$immobilisations_brutes = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=$exercice AND compte_debite_id IN (231,241,245,253)")->fetchColumn();
$amortissements = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=$exercice AND compte_credite_id IN (281,284,285,286)")->fetchColumn();
$immobilisations_nettes = $immobilisations_brutes - $amortissements;

$creances = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=$exercice AND compte_debite_id = 411")->fetchColumn();

$tresorerie = $pdo->query("SELECT COALESCE(SUM(CASE WHEN compte_debite_id=521 THEN montant ELSE 0 END),0) - COALESCE(SUM(CASE WHEN compte_credite_id=521 THEN montant ELSE 0 END),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=$exercice")->fetchColumn();

$total_actif = $immobilisations_nettes + $creances + $tresorerie;

// ---- PASSIF ----
$capital = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=$exercice AND compte_credite_id = 101")->fetchColumn();

$resultat = $pdo->query("SELECT COALESCE(SUM(CASE WHEN compte_credite_id BETWEEN 700 AND 799 THEN montant ELSE 0 END),0) - COALESCE(SUM(CASE WHEN compte_debite_id BETWEEN 600 AND 699 THEN montant ELSE 0 END),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=$exercice")->fetchColumn();

$dettes_fournisseurs = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=$exercice AND compte_credite_id = 401")->fetchColumn();
$dettes_salaires = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=$exercice AND compte_credite_id = 421")->fetchColumn();
$dettes_cnss = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=$exercice AND compte_credite_id = 431")->fetchColumn();
$dettes_ipres = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=$exercice AND compte_credite_id = 432")->fetchColumn();
$dettes_css = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=$exercice AND compte_credite_id = 433")->fetchColumn();
$tva_a_payer = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=$exercice AND compte_credite_id = 4451")->fetchColumn();

$total_dettes = $dettes_fournisseurs + $dettes_salaires + $dettes_cnss + $dettes_ipres + $dettes_css + $tva_a_payer;

$total_passif = $capital + $resultat + $total_dettes;

$ecart = $total_actif - $total_passif;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Bilan Synthétique (SYSCOHADA)</h5>
                <small>Arrêté au <?= date('d/m/Y') ?> - Exercice <?= $exercice ?></small>
            </div>
            <div class="card-body">
                <?php if(abs($ecart) > 1): ?>
                    <div class="alert alert-danger"><strong>⚠️ Écart détecté : <?= number_format(abs($ecart),0,',',' ') ?> FCFA</strong></div>
                <?php else: ?>
                    <div class="alert alert-success text-center">✅ Bilan équilibré : Actif = Passif = <?= number_format($total_actif,0,',',' ') ?> FCFA</div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">ACTIF (Emplois)</div>
                            <div class="card-body p-0">
                                <table class="table table-bordered mb-0">
                                    <tr class="bg-light"><td class="fw-bold">Immobilisations brutes</td><td class="text-end"><?= number_format($immobilisations_brutes,0,',',' ') ?> F</td></tr>
                                    <tr><td class="ps-3 text-danger">- Amortissements cumulés</td><td class="text-end text-danger">- <?= number_format($amortissements,0,',',' ') ?> F</td></tr>
                                    <tr class="bg-light"><td class="fw-bold ps-3">= Immobilisations nettes</td><td class="text-end fw-bold"><?= number_format($immobilisations_nettes,0,',',' ') ?> F</td></tr>
                                    <tr><td class="fw-bold">Créances clients</td><td class="text-end"><?= number_format($creances,0,',',' ') ?> F</td></tr>
                                    <tr><td class="fw-bold">Trésorerie</td><td class="text-end <?= $tresorerie >=0 ? 'text-success' : 'text-danger' ?>"><?= number_format($tresorerie,0,',',' ') ?> F</td></tr>
                                    <tr class="table-primary fw-bold"><td>TOTAL ACTIF</td><td class="text-end"><?= number_format($total_actif,0,',',' ') ?> F</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">PASSIF (Ressources)</div>
                            <div class="card-body p-0">
                                <table class="table table-bordered mb-0">
                                    <tr><td class="fw-bold">Capital social</td><td class="text-end"><?= number_format($capital,0,',',' ') ?> F</td></tr>
                                    <tr><td class="fw-bold <?= $resultat>=0 ? 'text-success' : 'text-danger' ?>">Résultat de l'exercice</td><td class="text-end"><?= number_format($resultat,0,',',' ') ?> F</td></tr>
                                    <tr class="bg-light"><td class="fw-bold">Dettes</td><td class="text-end"></td></tr>
                                    <tr><td class="ps-3">Fournisseurs</td><td class="text-end"><?= number_format($dettes_fournisseurs,0,',',' ') ?> F</td></tr>
                                    <tr><td class="ps-3">Salaires à payer</td><td class="text-end"><?= number_format($dettes_salaires,0,',',' ') ?> F</td></tr>
                                    <tr><td class="ps-3">CNSS à payer</td><td class="text-end"><?= number_format($dettes_cnss,0,',',' ') ?> F</td></tr>
                                    <tr><td class="ps-3">IPRES à payer</td><td class="text-end"><?= number_format($dettes_ipres,0,',',' ') ?> F</td></tr>
                                    <tr><td class="ps-3">CSS à payer</td><td class="text-end"><?= number_format($dettes_css,0,',',' ') ?> F</td></tr>
                                    <tr><td class="ps-3">TVA à payer</td><td class="text-end"><?= number_format($tva_a_payer,0,',',' ') ?> F</td></tr>
                                    <tr class="table-success fw-bold"><td>TOTAL PASSIF</td><td class="text-end"><?= number_format($total_passif,0,',',' ') ?> F</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="alert alert-info mt-3 text-center"><strong>🔑 ACTIF = PASSIF</strong><br><?= number_format($total_actif,0,',',' ') ?> F = <?= number_format($total_passif,0,',',' ') ?> F</div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>

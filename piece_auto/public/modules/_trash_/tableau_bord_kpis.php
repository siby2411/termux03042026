<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Tableau de bord - KPIs";
$page_icon = "speedometer2";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Calcul des KPIs financiers
$ca = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=2026 AND compte_credite_id BETWEEN 700 AND 799")->fetchColumn();
$charges = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=2026 AND compte_debite_id BETWEEN 600 AND 699")->fetchColumn();
$resultat = $ca - $charges;
$marge = $ca > 0 ? ($resultat / $ca) * 100 : 0;

// BFR simplifié
$stocks = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=2026 AND compte_debite_id BETWEEN 30 AND 39")->fetchColumn();
$creances = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=2026 AND compte_debite_id = 411")->fetchColumn();
$dettes_fourn = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture)=2026 AND compte_credite_id = 401")->fetchColumn();
$bfr = $stocks + $creances - $dettes_fourn;

// Trésorerie
$tresorerie = $pdo->query("SELECT COALESCE(SUM(CASE WHEN compte_debite_id=521 THEN montant ELSE 0 END),0) - COALESCE(SUM(CASE WHEN compte_credite_id=521 THEN montant ELSE 0 END),0) FROM ECRITURES_COMPTABLES")->fetchColumn();

// Ratios
$liquidite_generale = $dettes_fourn > 0 ? (($stocks + $creances) / $dettes_fourn) : 0;
$endettement = $ca > 0 ? ($dettes_fourn / $ca) * 100 : 0;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5>Tableau de bord - Indicateurs clés de performance</h5>
                <small>Période : 2026</small>
            </div>
            <div class="card-body">
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card text-center bg-success text-white">
                            <div class="card-body">
                                <h3><?= number_format($ca,0,',',' ') ?> F</h3>
                                <small>Chiffre d'affaires</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-info text-white">
                            <div class="card-body">
                                <h3><?= number_format($resultat,0,',',' ') ?> F</h3>
                                <small>Résultat net</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-warning text-dark">
                            <div class="card-body">
                                <h3><?= number_format($marge,1) ?>%</h3>
                                <small>Taux de marge</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-danger text-white">
                            <div class="card-body">
                                <h3><?= number_format($tresorerie,0,',',' ') ?> F</h3>
                                <small>Trésorerie nette</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-header">BFR</div>
                            <div class="card-body text-center">
                                <h4><?= number_format($bfr,0,',',' ') ?> F</h4>
                                <small><?= $bfr > 0 ? 'Besoin de financement' : 'Ressource dégagée' ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-header">Liquidité générale</div>
                            <div class="card-body text-center">
                                <h4><?= number_format($liquidite_generale,2) ?></h4>
                                <small><?= $liquidite_generale >= 1 ? '✓ Bonne couverture' : '⚠️ Risque' ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light">
                            <div class="card-header">Endettement / CA</div>
                            <div class="card-body text-center">
                                <h4><?= number_format($endettement,1) ?>%</h4>
                                <small><?= $endettement < 50 ? '✓ Maîtrisé' : '⚠️ Élevé' ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include 'inc_footer.php'; ?>

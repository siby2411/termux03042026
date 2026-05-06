<?php
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");
$page_title = "Tableau des Flux de Trésorerie";
$page_icon = "cash-stack";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Encaissements (comptes de produits 7)
$stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799");
$encaissements = $stmt->fetchColumn();

// Décaissements (comptes de charges 6)
$stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699");
$decaissements = $stmt->fetchColumn();

// Flux exploitation
$flux_exploitation = $encaissements - $decaissements;

// Investissements (comptes 2)
$stmt = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 200 AND 299");
$investissements = $stmt->fetchColumn();

// Solde banque (521)
$stmt = $pdo->query("
    SELECT COALESCE(SUM(CASE WHEN compte_debite_id = 521 THEN montant ELSE 0 END), 0) - 
           COALESCE(SUM(CASE WHEN compte_credite_id = 521 THEN montant ELSE 0 END), 0) 
    FROM ECRITURES_COMPTABLES
");
$tresorerie_finale = $stmt->fetchColumn();
?>
<div class="card">
    <div class="card-header bg-primary text-white">
        <h5><i class="bi bi-cash-stack"></i> Flux de Trésorerie - Exercice <?= date('Y') ?></h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <tr class="table-info">
                <td><strong>Encaissements d'exploitation</strong></td>
                <td class="text-end"><?= number_format($encaissements, 0, ',', ' ') ?> F</td>
            </tr>
            <tr class="table-warning">
                <td><strong>Décaissements d'exploitation</strong></td>
                <td class="text-end text-danger">- <?= number_format($decaissements, 0, ',', ' ') ?> F</td>
            </tr>
            <tr class="table-success">
                <td><strong>FLUX NET D'EXPLOITATION</strong></td>
                <td class="text-end fw-bold"><?= number_format($flux_exploitation, 0, ',', ' ') ?> F</td>
            </tr>
            <tr class="table-danger">
                <td><strong>Investissements (Immobilisations)</strong></td>
                <td class="text-end text-danger">- <?= number_format($investissements, 0, ',', ' ') ?> F</td>
            </tr>
            <tr class="table-primary">
                <td><strong>TRÉSORERIE FINALE</strong></td>
                <td class="text-end fw-bold"><?= number_format($tresorerie_finale, 0, ',', ' ') ?> F</td>
            </tr>
        </table>
    </div>
</div>
<?php include 'inc_footer.php'; ?>

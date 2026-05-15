<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Tableau de financement - Besoins et dégagements";
$page_icon = "arrow-left-right";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$exercice = $_GET['exercice'] ?? date('Y');

// Calcul des ressources (dégagements)
$capacite_autofinancement = $pdo->prepare("
    SELECT COALESCE(SUM(CASE WHEN compte_credite_id BETWEEN 700 AND 799 THEN montant ELSE 0 END), 0) -
           COALESCE(SUM(CASE WHEN compte_debite_id BETWEEN 600 AND 699 THEN montant ELSE 0 END), 0)
    FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ?
");
$capacite_autofinancement->execute([$exercice]);
$caf = $capacite_autofinancement->fetchColumn();

$dotations = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id = 681");
$dotations->execute([$exercice]);
$dotations = $dotations->fetchColumn();

$ressources = $caf + $dotations;

// Calcul des besoins (emplois)
$investissements = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id BETWEEN 20 AND 29");
$investissements->execute([$exercice]);
$investissements = $investissements->fetchColumn();

$remboursements = $pdo->prepare("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE YEAR(date_ecriture) = ? AND compte_debite_id IN (164, 165)");
$remboursements->execute([$exercice]);
$remboursements = $remboursements->fetchColumn();

$besoins = $investissements + $remboursements;

$variation_tresorerie = $ressources - $besoins;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-arrow-left-right"></i> Tableau de financement - Exercice <?= $exercice ?></h5>
                <small>Analyse des ressources et emplois (méthode besoins/dégagements)</small>
            </div>
            <div class="card-body">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">📈 RESSOURCES (Dégagements)</div>
                            <div class="card-body p-0">
                                <table class="table table-bordered mb-0">
                                    <tr><td>Capacité d'Autofinancement (CAF)</td><td class="text-end text-success">+ <?= number_format($caf, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Dotations aux amortissements</td><td class="text-end text-success">+ <?= number_format($dotations, 0, ',', ' ') ?> F</td></tr>
                                    <tr class="bg-success text-white fw-bold"><td>TOTAL DES RESSOURCES</td><td class="text-end"><?= number_format($ressources, 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-danger text-white">📉 EMPLOIS (Besoins)</div>
                            <div class="card-body p-0">
                                <table class="table table-bordered mb-0">
                                    <tr><td>Investissements (immobilisations)</td><td class="text-end text-danger">- <?= number_format($investissements, 0, ',', ' ') ?> F</td></tr>
                                    <tr><td>Remboursement d'emprunts</td><td class="text-end text-danger">- <?= number_format($remboursements, 0, ',', ' ') ?> F</td></tr>
                                    <tr class="bg-danger text-white fw-bold"><td>TOTAL DES EMPLOIS</td><td class="text-end"><?= number_format($besoins, 0, ',', ' ') ?> F</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-info text-white">💰 VARIATION DE TRÉSORERIE</div>
                            <div class="card-body text-center">
                                <h2 class="<?= $variation_tresorerie >= 0 ? 'text-success' : 'text-danger' ?>">
                                    <?= number_format($variation_tresorerie, 0, ',', ' ') ?> F
                                </h2>
                                <p>Ressources - Emplois = Variation de trésorerie</p>
                                <p class="small text-muted">Une variation positive indique une augmentation de la trésorerie</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lien avec FRNG/BFR/TN -->
                <div class="alert alert-secondary mt-3">
                    <strong>🔗 Liens entre indicateurs :</strong><br>
                    • <strong>FRNG</strong> = Fonds de Roulement Net Global (ressources stables - actif immobilisé)<br>
                    • <strong>BFR</strong> = Besoin en Fonds de Roulement (cycle d'exploitation)<br>
                    • <strong>TN</strong> = Trésorerie Nette (disponibilités réelles)<br>
                    • <strong>Relation</strong> : FRNG = BFR + TN
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>

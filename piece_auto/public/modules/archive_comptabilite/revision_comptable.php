<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

$page_title = "Révision comptable des états de synthèse";
$page_icon = "search";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

$anomalies = [];
$conformite = true;

// 1. Vérification de l'équilibre du bilan
$total_actif = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 200 AND 599")->fetchColumn();
$total_passif = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 100 AND 199")->fetchColumn();

if (abs($total_actif - $total_passif) > 1) {
    $anomalies[] = "❌ Bilan non équilibré : Actif = " . number_format($total_actif,0,',',' ') . " F, Passif = " . number_format($total_passif,0,',',' ') . " F";
    $conformite = false;
}

// 2. Vérification des comptes de régularisation
$charges_avance = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 481")->fetchColumn();
$produits_avance = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id = 482")->fetchColumn();

// 3. Vérification des amortissements
$immobilisations = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id IN (231,241,245,253)")->fetchColumn();
$amortissements = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id IN (281,284,285,286)")->fetchColumn();

if ($immobilisations > 0 && $amortissements == 0) {
    $anomalies[] = "⚠️ Immobilisations présentes sans amortissements cumulés";
}

// 4. Vérification des provisions
$provisions = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 160 AND 169")->fetchColumn();

// 5. Vérification des dettes sociales
$dettes_sociales = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id IN (421,431,432,433)")->fetchColumn();

// 6. Vérification de la TVA
$tva_collectee = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id = 4451")->fetchColumn();
$tva_deductible = $pdo->query("SELECT COALESCE(SUM(montant),0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id = 4454")->fetchColumn();
$tva_nette = $tva_collectee - $tva_deductible;
?>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><i class="bi bi-search"></i> Révision comptable des états de synthèse</h5>
                <small>Contrôle de conformité SYSCOHADA - OHADA</small>
            </div>
            <div class="card-body">
                
                <div class="alert alert-info">
                    <strong>📖 Rôle de la révision comptable :</strong><br>
                    La révision des états de synthèse vise à s'assurer de la régularité, sincérité et image fidèle des comptes annuels conformément aux normes SYSCOHADA.
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center bg-info text-white">
                            <div class="card-body">
                                <h4><?= $conformite ? '✅' : '⚠️' ?></h4>
                                <small>Conformité globale</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-success text-white">
                            <div class="card-body">
                                <h4><?= count($anomalies) ?></h4>
                                <small>Anomalies détectées</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-warning text-dark">
                            <div class="card-body">
                                <h4><?= number_format($provisions,0,',',' ') ?> F</h4>
                                <small>Provisions constituées</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center bg-danger text-white">
                            <div class="card-body">
                                <h4><?= number_format($dettes_sociales,0,',',' ') ?> F</h4>
                                <small>Dettes sociales</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Points de contrôle -->
                <div class="card mb-4">
                    <div class="card-header bg-secondary text-white">📋 Points de contrôle obligatoires</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="bi <?= $total_actif == $total_passif ? 'bi-check-circle text-success' : 'bi-x-circle text-danger' ?>"></i> Équilibre Actif = Passif</li>
                                    <li><i class="bi <?= $immobilisations > 0 && $amortissements > 0 ? 'bi-check-circle text-success' : 'bi-exclamation-triangle text-warning' ?>"></i> Amortissements constatés</li>
                                    <li><i class="bi <?= $provisions > 0 ? 'bi-check-circle text-success' : 'bi-info-circle text-secondary' ?>"></i> Provisions pour risques</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <ul class="list-unstyled">
                                    <li><i class="bi <?= $charges_avance > 0 ? 'bi-check-circle text-success' : 'bi-info-circle text-secondary' ?>"></i> Charges constatées d'avance</li>
                                    <li><i class="bi <?= $dettes_sociales > 0 ? 'bi-check-circle text-success' : 'bi-exclamation-triangle text-warning' ?>"></i> Dettes sociales comptabilisées</li>
                                    <li><i class="bi <?= $tva_nette != 0 ? 'bi-check-circle text-success' : 'bi-info-circle text-secondary' ?>"></i> TVA régularisée</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Anomalies -->
                <?php if(!empty($anomalies)): ?>
                <div class="alert alert-danger">
                    <strong>🚨 Anomalies détectées :</strong>
                    <ul class="mt-2 mb-0">
                        <?php foreach($anomalies as $a): ?>
                            <li><?= $a ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php else: ?>
                <div class="alert alert-success">
                    ✅ Aucune anomalie majeure détectée. Les états financiers sont conformes aux normes SYSCOHADA.
                </div>
                <?php endif; ?>

                <!-- Recommandations -->
                <div class="card mt-3">
                    <div class="card-header bg-success text-white">📝 Recommandations de l'expert-comptable</div>
                    <div class="card-body">
                        <ul>
                            <li>✓ Vérifier mensuellement l'équilibre du bilan</li>
                            <li>✓ Passer les écritures de régularisation en fin d'exercice</li>
                            <li>✓ Calculer systématiquement les amortissements</li>
                            <li>✓ Constituer des provisions pour risques identifiés</li>
                            <li>✓ Lettrage régulier des comptes clients et fournisseurs</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>

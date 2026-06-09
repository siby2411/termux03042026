<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "OMEGA CONSULTING ERP - Dashboard Expert";
$page_icon = "speedometer2";
require_once dirname(__DIR__) . '/config/config.php';
include 'inc_navbar.php';

// Indicateurs clés
$total_ca = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_credite_id BETWEEN 700 AND 799")->fetchColumn();
$total_charges = $pdo->query("SELECT COALESCE(SUM(montant), 0) FROM ECRITURES_COMPTABLES WHERE compte_debite_id BETWEEN 600 AND 699")->fetchColumn();
$tresorerie = $pdo->query("SELECT COALESCE(SUM(CASE WHEN compte_debite_id = 521 THEN montant ELSE 0 END), 0) - COALESCE(SUM(CASE WHEN compte_credite_id = 521 THEN montant ELSE 0 END), 0) FROM ECRITURES_COMPTABLES")->fetchColumn();
$nb_ecritures = $pdo->query("SELECT COUNT(*) FROM ECRITURES_COMPTABLES")->fetchColumn();
?>

<div class="row">
    <div class="col-md-12">
        <div class="alert alert-success">
            <h5><i class="bi bi-check-circle-fill"></i> Bienvenue sur OMEGA INFORMATIQUE CONSULTING ERP</h5>
            <p>Solution complète de gestion comptable et financière - Conforme SYSCOHADA UEMOA</p>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card-stats text-center p-3">
            <i class="bi bi-graph-up fs-2 text-primary"></i>
            <h3><?= number_format($total_ca, 0, ',', ' ') ?> F</h3>
            <small>Chiffre d'Affaires</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stats text-center p-3">
            <i class="bi bi-box-seam fs-2 text-danger"></i>
            <h3><?= number_format($total_charges, 0, ',', ' ') ?> F</h3>
            <small>Total Charges</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stats text-center p-3">
            <i class="bi bi-bank2 fs-2 text-success"></i>
            <h3 class="<?= $tresorerie >= 0 ? 'text-success' : 'text-danger' ?>">
                <?= number_format(abs($tresorerie), 0, ',', ' ') ?> F
            </h3>
            <small>Trésorerie (521)</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-stats text-center p-3">
            <i class="bi bi-journal-bookmark-fill fs-2 text-info"></i>
            <h3><?= $nb_ecritures ?></h3>
            <small>Écritures comptables</small>
        </div>
    </div>
</div>

<!-- Menus Navigation -->
<div class="row g-3">
    <div class="col-md-12">
        <h5 class="mb-3"><i class="bi bi-grid-3x3-gap-fill"></i> Modules de gestion financière</h5>
    </div>
    
    <!-- Comptabilité générale -->
    <div class="col-md-12">
        <div class="card bg-light mb-3">
            <div class="card-header bg-primary text-white">📚 Comptabilité générale</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2"><a href="ecriture.php" class="btn btn-outline-primary w-100 mb-2">📝 Écritures</a></div>
                    <div class="col-md-2"><a href="grand_livre.php" class="btn btn-outline-primary w-100 mb-2">📖 Grand Livre</a></div>
                    <div class="col-md-2"><a href="balance.php" class="btn btn-outline-primary w-100 mb-2">⚖️ Balance</a></div>
                    <div class="col-md-2"><a href="bilan.php" class="btn btn-outline-primary w-100 mb-2">📊 Bilan</a></div>
                    <div class="col-md-2"><a href="compte_resultat.php" class="btn btn-outline-primary w-100 mb-2">📈 Compte résultat</a></div>
                    <div class="col-md-2"><a href="sig.php" class="btn btn-outline-primary w-100 mb-2">📉 SIG</a></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gestion financière -->
    <div class="col-md-12">
        <div class="card bg-light mb-3">
            <div class="card-header bg-success text-white">💰 Gestion financière avancée</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><a href="ratios_financiers.php" class="btn btn-outline-success w-100 mb-2">📊 Ratios financiers</a></div>
                    <div class="col-md-3"><a href="variation_capitaux.php" class="btn btn-outline-success w-100 mb-2">📈 Variation capitaux</a></div>
                    <div class="col-md-3"><a href="previsions_budgetaires.php" class="btn btn-outline-success w-100 mb-2">📅 Prévisions budgétaires</a></div>
                    <div class="col-md-3"><a href="flux_tresorerie.php" class="btn btn-outline-success w-100 mb-2">💵 Flux trésorerie</a></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Gestion des actifs -->
    <div class="col-md-12">
        <div class="card bg-light mb-3">
            <div class="card-header bg-warning text-dark">🏗️ Gestion des actifs</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3"><a href="immobilisations.php" class="btn btn-outline-warning w-100 mb-2">🏢 Immobilisations</a></div>
                    <div class="col-md-3"><a href="amortissements_complet.php" class="btn btn-outline-warning w-100 mb-2">📉 Amortissements</a></div>
                    <div class="col-md-3"><a href="stock.php" class="btn btn-outline-warning w-100 mb-2">📦 Gestion stocks</a></div>
                    <div class="col-md-3"><a href="regularisations.php" class="btn btn-outline-warning w-100 mb-2">🔄 Régularisations</a></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Formation et documentation -->
    <div class="col-md-12">
        <div class="card bg-light mb-3">
            <div class="card-header bg-info text-white">🎓 Formation & Documentation</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4"><a href="didactiel/index.php" class="btn btn-outline-info w-100 mb-2">📚 Didacticiel complet</a></div>
                    <div class="col-md-4"><a href="manuel_formation.php" class="btn btn-outline-info w-100 mb-2">📖 Manuel formation</a></div>
                    <div class="col-md-4"><a href="guide_formation.php" class="btn btn-outline-info w-100 mb-2">🎯 Guide pratique</a></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'inc_footer.php'; ?>

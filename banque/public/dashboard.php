<?php
/**
 * PUBLIC/DASHBOARD.PHP
 * Tableau de bord principal pour la banque/mutuelle.
 * Affiche les indicateurs clés et tous les liens de navigation vers les modules CRUD et ALM.
 */

session_start();
require_once '../includes/db.php'; 
require_once '../includes/header.php'; 

// --- 1. Simulation de Données pour les Indicateurs (À remplacer par des requêtes réelles) ---
$clients_actifs = 452;
$comptes_actifs = 780;
$nouveaux_credits = 12; // Ce mois
$solde_total_caisse = 55000.00; 

// --- 2. Récupération des Ratios ALM (Simulée pour l'affichage initial) ---
// En production, vous feriez l'appel à la procédure ici comme :
/*
try {
    $conn->query("CALL CALCULER_RATIOS_ALM(@rli, @npl)");
    $result = $conn->query("SELECT @rli AS rli_value, @npl AS npl_value");
    $ratios = $result->fetch_assoc();
    $rli = $ratios['rli_value'];
    $npl = $ratios['npl_value'];
    while($conn->more_results()) { $conn->next_result(); } 
} catch (\Exception $e) {
    $rli = 0.00; $npl = 0.00;
}
*/
// Simulation pour que le dashboard fonctionne sans l'appel BD direct:
$rli = 1.67;
$npl = 33.33; // Taux élevé pour validation

// Logique pour la couleur des indicateurs
$rli_color = ($rli >= 1.20) ? 'success' : 'warning';
$npl_color = ($npl <= 5.00) ? 'success' : 'danger'; 
?>

<h1 class="mt-4"><i class="fas fa-chart-line me-2"></i> Tableau de Bord ALM & Gestion</h1>
<p class="text-muted">Vue complète de la santé financière et des outils de gestion des données.</p>

<hr>

## 📊 Santé Financière et Indicateurs Clés

<div class="row">
    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card shadow border-left-<?= $rli_color ?> h-100 py-2">
            <a href="<?php echo BASE_URL; ?>/comptabilite.php" class="card-body stretched-link text-decoration-none text-dark">
                <div class="text-xs font-weight-bold text-<?= $rli_color ?> text-uppercase mb-1">Ratio de Liquidité (RLI)</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($rli, 2) ?></div>
            </a>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card shadow border-left-<?= $npl_color ?> h-100 py-2">
            <a href="<?php echo BASE_URL; ?>/comptabilite.php" class="card-body stretched-link text-decoration-none text-dark">
                <div class="text-xs font-weight-bold text-<?= $npl_color ?> text-uppercase mb-1">Taux de Défaillance Brut (NPL)</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($npl, 2) ?> %</div>
            </a>
        </div>
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <div class="card shadow border-left-primary h-100 py-2">
            <a href="<?php echo BASE_URL; ?>/comptabilite.php" class="card-body stretched-link text-decoration-none text-dark">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Actifs Liquides (Trésorerie)</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($solde_total_caisse, 2, ',', ' ') ?> €</div>
            </a>
        </div>
    </div>
</div>

---

## 🛠️ Accès aux Modules de Gestion (CRUD)

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow border-left-secondary h-100 py-2">
            <div class="card-header bg-secondary text-white"><i class="fas fa-users me-2"></i> Gestion des Clients</div>
            <div class="card-body">
                <p>Clients actifs : <strong><?= number_format($clients_actifs) ?></strong></p>
                <a href="<?php echo BASE_URL; ?>/clients.php" class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-list"></i> Voir la Liste</a>
                <a href="<?php echo BASE_URL; ?>/clients.php?action=add" class="btn btn-sm btn-success"><i class="fas fa-user-plus"></i> Ajouter Nouveau</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow border-left-secondary h-100 py-2">
            <div class="card-header bg-secondary text-white"><i class="fas fa-wallet me-2"></i> Comptes & Épargne</div>
            <div class="card-body">
                <p>Comptes ouverts : <strong><?= number_format($comptes_actifs) ?></strong></p>
                <a href="<?php echo BASE_URL; ?>/epargne.php" class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-list"></i> Voir les Comptes</a>
                <a href="<?php echo BASE_URL; ?>/epargne.php?action=ouvrir" class="btn btn-sm btn-primary"><i class="fas fa-folder-open"></i> Ouvrir Compte</a>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow border-left-secondary h-100 py-2">
            <div class="card-header bg-secondary text-white"><i class="fas fa-hand-holding-usd me-2"></i> Gestion des Crédits</div>
            <div class="card-body">
                <p>Nouveaux crédits (mois) : <strong><?= $nouveaux_credits ?></strong></p>
                <a href="<?php echo BASE_URL; ?>/credits.php" class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-list"></i> Voir le Portefeuille</a>
                <a href="<?php echo BASE_URL; ?>/credits.php?action=add" class="btn btn-sm btn-warning"><i class="fas fa-plus"></i> Octroyer Crédit</a>
            </div>
        </div>
    </div>
</div>

---

## 🧠 Analyse et Reporting

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card shadow border-left-info h-100 py-2">
            <div class="card-header bg-info text-white"><i class="fas fa-calculator me-2"></i> Comptabilité & ALM (Vues)</div>
            <div class="card-body">
                <p>Accédez aux vues brutes pour le calcul des ratios (Trésorerie, Dépôts, Crédits).</p>
                <a href="<?php echo BASE_URL; ?>/comptabilite.php" class="btn btn-info"><i class="fas fa-eye me-2"></i> Vues de Diagnostic</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow border-left-dark h-100 py-2">
            <div class="card-header bg-dark text-white"><i class="fas fa-tachometer-alt me-2"></i> Administration & Reporting</div>
            <div class="card-body">
                <p>Tableau de bord complet avec l'évolution graphique des indicateurs (si implémenté).</p>
                <a href="<?php echo BASE_URL; ?>/administration.php" class="btn btn-dark"><i class="fas fa-chart-area me-2"></i> Rapport d'Évolution</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

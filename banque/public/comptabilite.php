<?php
/**
 * PUBLIC/COMPTABILITE.PHP
 * Module d'analyse financière et de gestion des risques (ALM).
 * Affiche les ratios clés (RLI, NPL).
 */

session_start();
require_once '../includes/db.php';
require_once '../includes/fonctions.php';

// --- CONTRÔLE D'ACCÈS (Restreint aux Admins et Comptables) ---
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !in_array($_SESSION['role'], ['Admin', 'Comptable'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$rli = 0.00;
$npl = 0.00;
$liquidite_totale = 0.00;

// --- 1. EXÉCUTION DE LA PROCÉDURE DE CALCUL DES RATIOS ---
try {
    // 1. Appel de la procédure pour calculer les ratios
    // NOTE: La procédure CALCULER_RATIOS_ALM doit exister dans la BDD.
    $conn->query("CALL CALCULER_RATIOS_ALM(@rli, @npl)");
    
    // 2. Récupérer les résultats
    $result = $conn->query("SELECT @rli AS rli_value, @npl AS npl_value");
    $ratios = $result->fetch_assoc();
    
    $rli = $ratios['rli_value'];
    $npl = $ratios['npl_value'];

    // Vider les tampons après CALL
    while($conn->more_results()) { $conn->next_result(); }
    
    // 3. Récupérer l'actif liquide total (Trésorerie) pour l'affichage
    $treso_result = $conn->query("SELECT Montant FROM TRESORERIE WHERE TresorerieID = 1");
    if ($treso_result && $treso_result->num_rows > 0) {
        $liquidite_totale = $treso_result->fetch_assoc()['Montant'];
    }

} catch (\Exception $e) {
    $message = "<div class='alert alert-danger'>Erreur lors du calcul des ratios : " . $e->getMessage() . "</div>";
}

// --- 2. DÉTERMINATION DES SEUILS ET DES COULEURS ---

// RLI : Seuil de sécurité généralement supérieur à 1.1 ou 1.2
if ($rli >= 1.20) {
    $rli_color = 'success';
    $rli_conseil = "Excellent. La mutuelle est très liquide.";
} elseif ($rli >= 1.00) {
    $rli_color = 'warning';
    $rli_conseil = "Marge de sécurité faible. Surveiller de près les sorties de fonds.";
} else {
    $rli_color = 'danger';
    $rli_conseil = "Risque de Liquidité. La mutuelle pourrait avoir du mal à honorer les retraits immédiats.";
}

// NPL : Taux de Défaillance (Non-Performing Loan). Seuil critique < 3-5%
if ($npl <= 3.00) {
    $npl_color = 'success';
    $npl_conseil = "Très bonne qualité du portefeuille de crédit.";
} elseif ($npl <= 5.00) {
    $npl_color = 'warning';
    $npl_conseil = "Niveau acceptable. Des efforts de recouvrement sont nécessaires.";
} else {
    $npl_color = 'danger';
    $npl_conseil = "Risque de Crédit Élevé. Revoir la politique de scoring ou intensifier le recouvrement.";
}


// --- 3. AFFICHAGE DE L'INTERFACE ---
require_once '../includes/header.php';
?>

<h1 class="mt-4"><i class="fas fa-chart-line me-2"></i> Tableau de Bord Financier & Risque (ALM)</h1>
<?= $message ?>

<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card shadow border-left-primary h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Actifs Liquides Disponibles (Trésorerie)
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($liquidite_totale, 2, ',', ' ') ?> €
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4 mb-4">
        <div class="card shadow border-left-<?= $rli_color ?> h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-<?= $rli_color ?> text-uppercase mb-1">
                            Ratio de Liquidité Immédiate (RLI)
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($rli, 2) ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-hand-holding-usd fa-2x text-gray-300"></i>
                    </div>
                </div>
                <small class="text-<?= $rli_color ?> mt-2 d-block"><?= $rli_conseil ?></small>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-4">
        <div class="card shadow border-left-<?= $npl_color ?> h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-<?= $npl_color ?> text-uppercase mb-1">
                            Taux de Défaillance Brut (NPL)
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?= number_format($npl, 2) ?> %
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
                <small class="text-<?= $npl_color ?> mt-2 d-block"><?= $npl_conseil ?></small>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header bg-dark text-white">
        <h5><i class="fas fa-calculator me-2"></i> Détail des Composantes du Calcul</h5>
    </div>
    <div class="card-body">
        <p>Les ratios sont calculés à la demande via la procédure stockée **`CALCULER_RATIOS_ALM`** pour refléter l'état actuel des comptes clients, crédits et de la trésorerie.</p>
        
        <h6>Formules utilisées :</h6>
        <ul>
            <li>**RLI (Ratio de Liquidité Immédiate)** : $\text{Actifs Liquides} / \text{Dépôts Exigibles}$</li>
            <li>**NPL (Non-Performing Loan)** : $(\text{Crédits en Souffrance} / \text{Total Portefeuille de Crédit}) \times 100$</li>
        </ul>
        
        <p class="text-muted">Pour des analyses plus poussées (ROE, ROA, GAP ALM), des procédures stockées supplémentaires et des tables de reporting complexes seraient nécessaires.</p>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

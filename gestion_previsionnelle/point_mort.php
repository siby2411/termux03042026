<?php
// /point_mort_analyse.php
$page_title = "Analyse du Seuil de Rentabilité (Point Mort)";
// Utilisation de la structure de connexion éprouvée
include_once __DIR__ . '/config/db.php'; 
include_once __DIR__ . '/includes/header.php'; // On suppose que le header contient le début du HTML et le style

$database = new Database();
$db = $database->getConnection();

// --- 1. HYPOTHÈSES DE TRAVAIL ANNUELLES ---
// Pour le Point Mort, on travaille généralement sur des bases annuelles ou mensuelles standard.
$frais_fixes_mensuels = 15000.00; 
$frais_fixes_annuels = $frais_fixes_mensuels * 12; // Charges fixes sur l'année

// Période d'analyse : Derniers 365 jours pour une meilleure moyenne annuelle
$date_fin = date('Y-m-d');
$date_debut = date('Y-m-d', strtotime('-365 days'));
$jours_analyse = 365;

$analyse = [
    'CA_Annuel' => 0.00,
    'MargeBrute_Annuelle' => 0.00,
    'TMCV' => 0.00, // Taux de Marge sur Coûts Variables
    'PointMort_CA' => 0.00,
    'MargeSecurite' => 0.00,
    'IndiceSecurite' => 0.00,
    'DatePM' => 'N/A'
];

try {
    // ----------------------------------------------------------------------
    // 2. CALCUL DES AGRÉGATS ANNUELS VIA V_Marge_Ventes
    // ----------------------------------------------------------------------
    $query_annuel = "
        SELECT 
            COALESCE(SUM(MontantCA), 0) AS CA_Total,
            COALESCE(SUM(MargeBrute), 0) AS MargeBrute_Total
        FROM V_Marge_Ventes
        WHERE DateCommande BETWEEN :date_debut AND :date_fin
    ";
    $stmt_annuel = $db->prepare($query_annuel);
    $stmt_annuel->bindParam(':date_debut', $date_debut);
    $stmt_annuel->bindParam(':date_fin', $date_fin);
    $stmt_annuel->execute();
    $result_annuel = $stmt_annuel->fetch(PDO::FETCH_ASSOC);

    $analyse['CA_Annuel'] = (float)$result_annuel['CA_Total'];
    $analyse['MargeBrute_Annuelle'] = (float)$result_annuel['MargeBrute_Total'];
    
    // --- 3. CALCUL DU TAUX DE MARGE SUR COÛTS VARIABLES (TMCV) ---
    if ($analyse['CA_Annuel'] > 0) {
        $analyse['TMCV'] = $analyse['MargeBrute_Annuelle'] / $analyse['CA_Annuel'];
    }
    
    // --- 4. CALCUL DU POINT MORT (SEUIL DE RENTABILITÉ) ---
    if ($analyse['TMCV'] > 0) {
        // Point Mort (SR) en CA : Charges Fixes / TMCV
        $analyse['PointMort_CA'] = $frais_fixes_annuels / $analyse['TMCV'];
    }
    
    // --- 5. CALCUL DES INDICATEURS DE SÉCURITÉ ---
    // Marge de Sécurité (MS)
    $analyse['MargeSecurite'] = $analyse['CA_Annuel'] - $analyse['PointMort_CA'];
    
    // Indice de Sécurité (IS)
    if ($analyse['CA_Annuel'] > 0) {
        $analyse['IndiceSecurite'] = ($analyse['MargeSecurite'] / $analyse['CA_Annuel']) * 100;
    }

    // --- 6. CALCUL DE LA DATE DU POINT MORT ---
    if ($analyse['PointMort_CA'] < $analyse['CA_Annuel'] && $analyse['CA_Annuel'] > 0) {
        // Taux de couverture journalier du PM
        $ca_journalier_moyen = $analyse['CA_Annuel'] / $jours_analyse;
        $jours_atteinte_pm = ceil($analyse['PointMort_CA'] / $ca_journalier_moyen);
        
        // Calculer la date (en supposant le début de l'activité au 1er janvier de la période)
        $date_pm_obj = new DateTime(date('Y-01-01'));
        $date_pm_obj->modify("+{$jours_atteinte_pm} days");
        $analyse['DatePM'] = $date_pm_obj->format('d F Y');
    }


} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Erreur de base de données pour l'analyse du Point Mort : " . $e->getMessage() . "</div>";
}
?>

<h1 class="mt-4 text-center"><i class="fas fa-chart-area me-2"></i> Analyse Stratégique : Seuil de Rentabilité (Point Mort)</h1>
<p class="text-muted text-center">Calculé sur la base de la performance des 365 derniers jours et des charges fixes annuelles estimées.</p>
<hr class="mb-5">

<div class="row mb-5">
    <h3 class="mb-4 text-primary"><i class="fas fa-cogs me-2"></i> Hypothèses Utilisées</h3>
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100 bg-light">
            <div class="card-body">
                <p class="fs-5 fw-bold text-primary">Charges Fixes Annuelles</p>
                <div class="display-5"><?= number_format($frais_fixes_annuels, 0, ',', ' ') ?> €</div>
                <p class="text-muted small">(<?= number_format($frais_fixes_mensuels, 0, ',', ' ') ?> € / mois)</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100 bg-light">
            <div class="card-body">
                <p class="fs-5 fw-bold text-primary">CA Annuel (Réalisé)</p>
                <div class="display-5"><?= number_format($analyse['CA_Annuel'], 0, ',', ' ') ?> €</div>
                <p class="text-muted small">(Basé sur les 365 derniers jours)</p>
            </div>
        </div>
    </div>
    <div class="col-lg-4 mb-4">
        <div class="card shadow-sm h-100 bg-light">
            <div class="card-body">
                <p class="fs-5 fw-bold text-primary">Taux de Marge sur Coûts Variables (TMCV)</p>
                <div class="display-5"><?= number_format($analyse['TMCV'] * 100, 1, ',', ' ') ?> %</div>
                <p class="text-muted small">(MB Annuelle / CA Annuel)</p>
            </div>
        </div>
    </div>
</div>

<hr>

<div class="row mt-5">
    <h3 class="mb-4 text-success"><i class="fas fa-trophy me-2"></i> Indicateurs de Rentabilité</h3>
    
    <div class="col-lg-6 mb-4">
        <div class="card shadow-lg h-100 border-success border-3">
            <div class="card-header bg-success text-white fw-bold">Seuil de Rentabilité (Point Mort)</div>
            <div class="card-body text-center">
                <div class="display-3 fw-bold">
                    <?= number_format($analyse['PointMort_CA'], 0, ',', ' ') ?> €
                </div>
                <p class="text-muted mt-3">CA minimum annuel à atteindre pour couvrir l'ensemble des charges (fixes et variables).</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card shadow-lg h-100 border-success border-3">
            <div class="card-header bg-success text-white fw-bold">Date de Point Mort</div>
            <div class="card-body text-center">
                <div class="display-3 fw-bold text-success">
                    <?= $analyse['DatePM'] ?>
                </div>
                <p class="text-muted mt-3">Date estimée à laquelle l'entreprise passe de la perte au profit (basé sur le CA journalier moyen).</p>
            </div>
        </div>
    </div>
</div>

<div class="row mt-5">
    <h3 class="mb-4 text-warning"><i class="fas fa-shield-alt me-2"></i> Marge et Indice de Sécurité</h3>
    
    <div class="col-lg-6 mb-4">
        <div class="card shadow-lg h-100 border-warning border-3">
            <div class="card-header bg-warning text-white fw-bold">Marge de Sécurité</div>
            <div class="card-body text-center">
                <div class="display-3 fw-bold <?= ($analyse['MargeSecurite'] >= 0) ? 'text-success' : 'text-danger'; ?>">
                    <?= number_format($analyse['MargeSecurite'], 0, ',', ' ') ?> €
                </div>
                <p class="text-muted mt-3">Montant maximum de CA que l'entreprise peut perdre avant d'atteindre le Seuil de Rentabilité.</p>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="card shadow-lg h-100 border-warning border-3">
            <div class="card-header bg-warning text-white fw-bold">Indice de Sécurité</div>
            <div class="card-body text-center">
                <div class="display-3 fw-bold <?= ($analyse['IndiceSecurite'] >= 20) ? 'text-success' : 'text-danger'; ?>">
                    <?= number_format($analyse['IndiceSecurite'], 1, ',', ' ') ?> %
                </div>
                <p class="text-muted mt-3">Pourcentage de CA qui peut être perdu avant d'atteindre le Seuil de Rentabilité (Indice > 20% est généralement bon).</p>
            </div>
        </div>
    </div>
</div>


<?php include_once __DIR__ . '/includes/footer.php'; ?>
